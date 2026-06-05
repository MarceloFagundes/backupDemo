<?php
/**
 * Migração: Criar tabela bairros_entrega e adicionar colunas na tabela pedidos
 * Suba este arquivo e acesse-o via navegador: http://seusite.com.br/migrate_bairros.php
 */
require_once __DIR__ . '/sheep_core/config.php';

try {
    // Usamos SHEEP_HOST que o config.php vai definir automaticamente baseando-se em onde está rodando (local ou online)
    $pdo = new PDO("mysql:host=" . SHEEP_HOST . ";dbname=" . SHEEP_BD . ";charset=utf8", SHEEP_USER, SHEEP_SENHA);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Criar tabela bairros_entrega
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `bairros_entrega` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `nome_bairro` VARCHAR(150) NOT NULL,
            `taxa` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
            `criado_em` DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
    echo "✅ Tabela 'bairros_entrega' criada (ou já existia).\n";

    // 2. Adicionar coluna taxa_entrega em pedidos (se não existir)
    $check = $pdo->query("SHOW COLUMNS FROM `pedidos` LIKE 'taxa_entrega'");
    if ($check->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `pedidos` ADD `taxa_entrega` DECIMAL(10,2) DEFAULT 0.00 AFTER `valor_total`");
        echo "✅ Coluna 'taxa_entrega' adicionada à tabela 'pedidos'.\n";
    } else {
        echo "⚠️ Coluna 'taxa_entrega' já existe em 'pedidos'.\n";
    }

    // 3. Adicionar coluna bairro em pedidos (se não existir)
    $check2 = $pdo->query("SHOW COLUMNS FROM `pedidos` LIKE 'bairro'");
    if ($check2->rowCount() == 0) {
        $pdo->exec("ALTER TABLE `pedidos` ADD `bairro` VARCHAR(150) NULL DEFAULT NULL AFTER `taxa_entrega`");
        echo "✅ Coluna 'bairro' adicionada à tabela 'pedidos'.\n";
    } else {
        echo "⚠️ Coluna 'bairro' já existe em 'pedidos'.\n";
    }

    echo "\n🎉 Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    echo "❌ Erro na migração: " . $e->getMessage() . "\n";
}
