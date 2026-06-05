<?php
/**
 * CLASSE FIDELIDADE & CASHBACK SERVICE - PIZZARIA
 * Gerencia o acúmulo de pontos e saldos de cashback ("Clube Fidelidade").
 */
class FidelidadeService extends Conexao {

    public function __construct() {
        $this->verificarEInstalarTabelas();
    }

    /**
     * Verifica e instala as tabelas necessárias de forma autossuficiente (Self-healing).
     */
    private function verificarEInstalarTabelas() {
        try {
            $pdo = parent::getCanectar();
            
            // 1. Tabela de carteira de fidelidade (saldo de cashback e pontos)
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `fidelidade_carteira` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `usuario_id` INT NOT NULL UNIQUE,
                    `saldo_cashback` DECIMAL(10,2) DEFAULT 0.00,
                    `pontos_acumulados` INT DEFAULT 0,
                    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `atualizado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // 2. Tabela de histórico de transações de fidelidade
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS `fidelidade_historico` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `usuario_id` INT NOT NULL,
                    `pedido_id` INT NULL,
                    `tipo` VARCHAR(30) NOT NULL, -- 'ganho_fidelidade', 'uso_cashback', 'expiracao_cashback'
                    `valor_cashback` DECIMAL(10,2) DEFAULT 0.00,
                    `quantidade_pontos` INT DEFAULT 0,
                    `descricao` VARCHAR(255) NOT NULL,
                    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `data_expiracao` DATETIME NULL DEFAULT NULL,
                    `expirado` TINYINT(1) DEFAULT 0
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // 3. Autocorreção / Migração: Adicionar colunas se tabelas já existirem
            
            // Tabela fidelidade_historico: data_expiracao
            $check = $pdo->query("SHOW COLUMNS FROM `fidelidade_historico` LIKE 'data_expiracao'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `fidelidade_historico` ADD `data_expiracao` DATETIME NULL DEFAULT NULL AFTER `criado_em`");
            }

            // Tabela fidelidade_historico: expirado
            $check = $pdo->query("SHOW COLUMNS FROM `fidelidade_historico` LIKE 'expirado'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `fidelidade_historico` ADD `expirado` TINYINT(1) DEFAULT 0 AFTER `data_expiracao`");
            }

            // Adicionar índice para performance
            try {
                $pdo->exec("ALTER TABLE `fidelidade_historico` ADD INDEX `idx_fidelidade_expiracao` (`usuario_id`, `tipo`, `expirado`, `data_expiracao`)");
            } catch (Exception $eIndex) {
                // Se o índice já existir, ignora o erro
            }

            // Tabela configuracoes: porcentagem_cashback
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'porcentagem_cashback'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `porcentagem_cashback` DECIMAL(5,2) DEFAULT 5.00");
            }

            // Tabela configuracoes: tipo_validade_cashback
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'tipo_validade_cashback'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `tipo_validade_cashback` VARCHAR(20) DEFAULT 'dias'");
            }

            // Tabela configuracoes: dias_validade_cashback
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'dias_validade_cashback'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `dias_validade_cashback` INT(11) DEFAULT 30");
            }

            // Tabela configuracoes: data_validade_cashback
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'data_validade_cashback'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `data_validade_cashback` DATE DEFAULT NULL");
            }

            // Tabela configuracoes: mp_public_key
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'mp_public_key'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `mp_public_key` VARCHAR(255) NULL DEFAULT NULL");
            }

            // Tabela configuracoes: mp_access_token
            $check = $pdo->query("SHOW COLUMNS FROM `configuracoes` LIKE 'mp_access_token'");
            if ($check->rowCount() == 0) {
                $pdo->exec("ALTER TABLE `configuracoes` ADD `mp_access_token` VARCHAR(255) NULL DEFAULT NULL");
            }

        } catch (Exception $e) {
            // Ignora se o banco de dados falhar temporariamente
        }
    }

    /**
     * Retorna a carteira de fidelidade do usuário (ou cria uma caso não exista).
     */
    public function getCarteira($usuarioId) {
        if (!$usuarioId) return null;
        
        $pdo = parent::getCanectar();
        
        // Executa a expiração dinâmica de saldos vencidos antes de retornar
        $this->atualizarExpiracoes($usuarioId);
        
        $stmt = $pdo->prepare("SELECT * FROM `fidelidade_carteira` WHERE `usuario_id` = ? LIMIT 1");
        $stmt->execute([$usuarioId]);
        $carteira = $stmt->fetch();
        
        if (!$carteira) {
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO `fidelidade_carteira` (`usuario_id`, `saldo_cashback`, `pontos_acumulados`) VALUES (?, 0.00, 0)");
                $stmtInsert->execute([$usuarioId]);
                
                return [
                    'usuario_id' => $usuarioId,
                    'saldo_cashback' => 0.00,
                    'pontos_acumulados' => 0
                ];
            } catch (Exception $e) {
                $stmt->execute([$usuarioId]);
                return $stmt->fetch();
            }
        }
        
        $carteira['saldo_cashback'] = (float)$carteira['saldo_cashback'];
        $carteira['pontos_acumulados'] = (int)$carteira['pontos_acumulados'];
        
        return $carteira;
    }

    /**
     * Registra os ganhos de cashback e pontos após confirmação de pagamento.
     */
    public function registrarGanho($usuarioId, $pedidoId, $valorTotalPedido) {
        if (!$usuarioId || !$pedidoId || $valorTotalPedido <= 0) return false;
        
        $pdo = parent::getCanectar();
        
        // Evita duplicidade de ganho no histórico
        $stmtCheck = $pdo->prepare("SELECT `id` FROM `fidelidade_historico` WHERE `pedido_id` = ? AND `tipo` = 'ganho_fidelidade' LIMIT 1");
        $stmtCheck->execute([$pedidoId]);
        if ($stmtCheck->fetch()) {
            return false;
        }

        // Busca as configurações de cashback e validade
        $stmtConfig = $pdo->prepare("SELECT porcentagem_cashback, tipo_validade_cashback, dias_validade_cashback, data_validade_cashback FROM `configuracoes` WHERE `id` = 1 LIMIT 1");
        $stmtConfig->execute();
        $config = $stmtConfig->fetch();
        
        $taxaCashback = ($config && isset($config['porcentagem_cashback'])) ? (float)$config['porcentagem_cashback'] / 100 : 0.05;
        $tipoValidade = ($config && isset($config['tipo_validade_cashback'])) ? $config['tipo_validade_cashback'] : 'dias';
        $diasValidade = ($config && isset($config['dias_validade_cashback'])) ? (int)$config['dias_validade_cashback'] : 30;
        $dataValidade = ($config && isset($config['data_validade_cashback'])) ? $config['data_validade_cashback'] : null;

        // Regras: Cashback dinâmico e 1 ponto por cada 1 Real gasto
        $cashbackGanho = round($valorTotalPedido * $taxaCashback, 2);
        $pontosGanhos = (int)floor($valorTotalPedido);

        if ($cashbackGanho <= 0 && $pontosGanhos <= 0) return false;

        // Calcular a data de expiração
        $dataExpiracao = null;
        if ($tipoValidade === 'dias' && $diasValidade > 0) {
            $dataExpiracao = date('Y-m-d H:i:s', strtotime("+{$diasValidade} days"));
        } elseif ($tipoValidade === 'data' && !empty($dataValidade)) {
            $dataExpiracao = $dataValidade . ' 23:59:59';
        }

        // Garante a existência da carteira
        $this->getCarteira($usuarioId);

        $pdo->beginTransaction();
        try {
            // Atualiza saldos da carteira
            $stmtUpdate = $pdo->prepare("UPDATE `fidelidade_carteira` SET `saldo_cashback` = `saldo_cashback` + ?, `pontos_acumulados` = `pontos_acumulados` + ? WHERE `usuario_id` = ?");
            $stmtUpdate->execute([$cashbackGanho, $pontosGanhos, $usuarioId]);

            // Insere log no histórico com data de expiração
            $stmtLog = $pdo->prepare("INSERT INTO `fidelidade_historico` (`usuario_id`, `pedido_id`, `tipo`, `valor_cashback`, `quantidade_pontos`, `descricao`, `data_expiracao`, `expirado`) VALUES (?, ?, 'ganho_fidelidade', ?, ?, ?, ?, 0)");
            $descricao = "Acúmulo de Cashback (+R$ " . number_format($cashbackGanho, 2, ',', '.') . ") e Pontos (+{$pontosGanhos}) no Pedido #{$pedidoId}";
            $stmtLog->execute([$usuarioId, $pedidoId, $cashbackGanho, $pontosGanhos, $descricao, $dataExpiracao]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Deduz o saldo de cashback usado como desconto na finalização do pedido.
     */
    public function registrarDeducao($usuarioId, $pedidoId, $valorDesconto) {
        if (!$usuarioId || !$pedidoId || $valorDesconto <= 0) return false;
        
        $pdo = parent::getCanectar();
        $carteira = $this->getCarteira($usuarioId);

        if (!$carteira || $carteira['saldo_cashback'] < $valorDesconto) return false;

        $pdo->beginTransaction();
        try {
            // Deduz o saldo da carteira
            $stmtUpdate = $pdo->prepare("UPDATE `fidelidade_carteira` SET `saldo_cashback` = `saldo_cashback` - ? WHERE `usuario_id` = ?");
            $stmtUpdate->execute([$valorDesconto, $usuarioId]);

            // Insere log negativo no histórico
            $stmtLog = $pdo->prepare("INSERT INTO `fidelidade_historico` (`usuario_id`, `pedido_id`, `tipo`, `valor_cashback`, `quantidade_pontos`, `descricao`) VALUES (?, ?, 'uso_cashback', ?, 0, ?)");
            $descricao = "Desconto de Cashback aplicado no Pedido #{$pedidoId}";
            $stmtLog->execute([$usuarioId, $pedidoId, -$valorDesconto, $descricao]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }

    /**
     * Expira cashback e pontos que passaram da validade de forma segura e transacional (lazy cleanup).
     */
    public function atualizarExpiracoes($usuarioId) {
        if (!$usuarioId) return;
        
        $pdo = parent::getCanectar();
        
        // 1. Buscar transações de ganho de fidelidade expiradas que ainda não foram marcadas como processadas
        $stmt = $pdo->prepare("
            SELECT id, valor_cashback, quantidade_pontos, descricao 
            FROM `fidelidade_historico` 
            WHERE `usuario_id` = ? 
              AND `tipo` = 'ganho_fidelidade' 
              AND `expirado` = 0 
              AND `data_expiracao` IS NOT NULL 
              AND `data_expiracao` < NOW()
        ");
        $stmt->execute([$usuarioId]);
        $expirados = $stmt->fetchAll();
        
        if (empty($expirados)) return;
        
        // Calcular o total de cashback e pontos expirados
        $totalCashbackExpirado = 0.00;
        $totalPontosExpirados = 0;
        $idsExpirados = [];
        
        foreach ($expirados as $exp) {
            $totalCashbackExpirado += (float)$exp['valor_cashback'];
            $totalPontosExpirados += (int)$exp['quantidade_pontos'];
            $idsExpirados[] = (int)$exp['id'];
        }
        
        if ($totalCashbackExpirado <= 0 && $totalPontosExpirados <= 0) return;
        
        // Buscar saldo atual da carteira para garantir que não fique negativo
        $stmtCarteira = $pdo->prepare("SELECT saldo_cashback, pontos_acumulados FROM `fidelidade_carteira` WHERE `usuario_id` = ? LIMIT 1");
        $stmtCarteira->execute([$usuarioId]);
        $carteira = $stmtCarteira->fetch();
        
        if (!$carteira) return;
        
        $saldoAtual = (float)$carteira['saldo_cashback'];
        $pontosAtuais = (int)$carteira['pontos_acumulados'];
        
        // O valor a deduzir não pode ultrapassar o saldo atual (evita saldo negativo)
        $deducaoCashback = min($saldoAtual, $totalCashbackExpirado);
        $deducaoPontos = min($pontosAtuais, $totalPontosExpirados);
        
        $pdo->beginTransaction();
        try {
            // Deduzir da carteira
            $stmtUpdate = $pdo->prepare("UPDATE `fidelidade_carteira` SET `saldo_cashback` = `saldo_cashback` - ?, `pontos_acumulados` = `pontos_acumulados` - ? WHERE `usuario_id` = ?");
            $stmtUpdate->execute([$deducaoCashback, $deducaoPontos, $usuarioId]);
            
            // Gravar log de expiração
            $stmtLog = $pdo->prepare("INSERT INTO `fidelidade_historico` (`usuario_id`, `tipo`, `valor_cashback`, `quantidade_pontos`, `descricao`, `expirado`) VALUES (?, 'expiracao_cashback', ?, ?, ?, 1)");
            $descricao = "Expiração de Cashback (-R$ " . number_format($deducaoCashback, 2, ',', '.') . ") e Pontos (-{$deducaoPontos}) expirados";
            $stmtLog->execute([$usuarioId, -$deducaoCashback, -$deducaoPontos, $descricao]);
            
            // Marcar os registros originais como expirados (para não processar de novo)
            $placeholders = implode(',', array_fill(0, count($idsExpirados), '?'));
            $stmtMark = $pdo->prepare("UPDATE `fidelidade_historico` SET `expirado` = 1 WHERE `id` IN ($placeholders)");
            $stmtMark->execute($idsExpirados);
            
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    }

    /**
     * Retorna a lista de lançamentos de cashback ativos (não expirados) e suas respectivas datas de validade.
     */
    public function getSaldosValidos($usuarioId) {
        if (!$usuarioId) return [];
        
        $pdo = parent::getCanectar();
        
        // Garante que o saldo está atualizado antes de listar
        $this->atualizarExpiracoes($usuarioId);
        
        $stmt = $pdo->prepare("
            SELECT valor_cashback, quantidade_pontos, criado_em, data_expiracao 
            FROM `fidelidade_historico` 
            WHERE `usuario_id` = ? 
              AND `tipo` = 'ganho_fidelidade' 
              AND `expirado` = 0 
              AND `data_expiracao` IS NOT NULL 
              AND `data_expiracao` >= NOW() 
            ORDER BY `data_expiracao` ASC
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }
}
?>
