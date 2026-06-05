<?php
/**
 * Desenvolvido sob o padrão arquitetural do Sheep PHP Framework.
 */

class IfoodService extends Conexao {

    // Credenciais provisórias de homologação (carregadas dinamicamente do banco)
    private $clientId = null;
    private $clientSecret = null;
    private $merchantId = null;
    private $apiUrl = 'https://merchant-api.ifood.com.br';

    /**
     * Construtor da classe. Carrega as credenciais salvas no banco
     * ou usa chaves provisórias para testes.
     */
    public function __construct($clientId = null, $clientSecret = null, $merchantId = null) {
        if ($clientId !== null && $clientSecret !== null && $merchantId !== null) {
            $this->clientId = trim($clientId);
            $this->clientSecret = trim($clientSecret);
            $this->merchantId = trim($merchantId);
        } else {
            $this->loadCredentials();
        }
    }

    /**
     * Carrega as credenciais oficiais configuradas no banco de dados.
     */
    private function loadCredentials() {
        try {
            $ler = new Ler();
            
            $ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=client_id");
            $res = $ler->getResultado();
            if ($res && !empty($res[0]['valor'])) {
                $this->clientId = trim($res[0]['valor']);
            }

            $ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=client_secret");
            $res = $ler->getResultado();
            if ($res && !empty($res[0]['valor'])) {
                $this->clientSecret = trim($res[0]['valor']);
            }

            $ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=merchant_id");
            $res = $ler->getResultado();
            if ($res && !empty($res[0]['valor'])) {
                $this->merchantId = trim($res[0]['valor']);
            }
        } catch (Exception $e) {
            // Silencia caso as tabelas ainda estejam em migração
        }
    }

    /**
     * 1. Autenticação OAuth 2.0
     * Obtém o token de acesso que é válido por 6 horas.
     * Faz o cache do token no banco de dados para evitar múltiplas requisições de autenticação inúteis.
     */
    public function getAccessToken() {
        try {
            $ler = new Ler();
            $ler->Leitura('config_ifood', "WHERE chave = :chave LIMIT 1", "chave=access_token");
            $tokenSalvo = $ler->getResultado();

            if ($tokenSalvo && !empty($tokenSalvo[0]['valor'])) {
                $dadosToken = json_decode($tokenSalvo[0]['valor'], true);
                if (isset($dadosToken['expira_em']) && $dadosToken['expira_em'] > time()) {
                    return $dadosToken['access_token'];
                }
            }
        } catch (Exception $e) {
            // Caso a tabela ainda não exista, prossegue pedindo o token normalmente
        }

        // Solicita um novo token da API do iFood
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '/authentication/v1.0/oauth/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grantType' => 'client_credentials',
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // LOG DE DEBUG PARA DIAGNÓSTICO DA CONEXÃO
        $logPath = __DIR__ . '/../../log_ifood_debug.txt';
        $logMsg = "[" . date('Y-m-d H:i:s') . "] --- TESTE OAUTH ---" . PHP_EOL;
        $logMsg .= "Client ID: " . $this->clientId . PHP_EOL;
        $logMsg .= "Client Secret (Parcial): " . substr($this->clientSecret, 0, 8) . "..." . PHP_EOL;
        $logMsg .= "HTTP Code: " . $httpCode . PHP_EOL;
        $logMsg .= "cURL Error: " . $curlError . PHP_EOL;
        $logMsg .= "Response Body: " . $resposta . PHP_EOL;
        $logMsg .= "--------------------------------------" . PHP_EOL . PHP_EOL;
        @file_put_contents($logPath, $logMsg, FILE_APPEND);

        if ($httpCode === 200) {
            $dados = json_decode($resposta, true);
            
            // Calcula o timestamp de expiração (com margem de 5 minutos de segurança)
            $expiraEm = time() + $dados['expiresIn'] - 300; 
            
            $tokenCache = [
                'access_token' => $dados['accessToken'],
                'expira_em' => $expiraEm
            ];

            try {
                // Atualiza ou insere o token no banco de dados
                $atualizar = new Atualizar();
                $dadosUpdate = ['valor' => json_encode($tokenCache)];
                $atualizar->Atualizando('config_ifood', $dadosUpdate, "WHERE chave = :chave", "chave=access_token");
                
                if (!$atualizar->getResultado()) {
                    $criar = new Criar();
                    $criar->Criacao('config_ifood', [
                        'chave' => 'access_token',
                        'valor' => json_encode($tokenCache)
                    ]);
                }
            } catch (Exception $e) {
                // Ignora se o banco estiver temporariamente indisponível
            }

            return $dados['accessToken'];
        }

        // Em caso de falha de credenciais, retorna nulo para não quebrar a aplicação
        return null;
    }

    /**
     * 2. Executa requisições na API do iFood
     */
    private function request($endpoint, $method = 'GET', $body = null) {
        $token = $this->getAccessToken();
        
        if (!$token) {
            return ['code' => 401, 'body' => 'Não autorizado'];
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ];

        if ($body) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $resposta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // LOG DE DEBUG PARA DIAGNÓSTICO DE REQUISIÇÕES DA API
        $logPath = __DIR__ . '/../../log_ifood_debug.txt';
        $logMsg = "[" . date('Y-m-d H:i:s') . "] --- REQUISIÇÃO API ---" . PHP_EOL;
        $logMsg .= "Endpoint: " . $endpoint . PHP_EOL;
        $logMsg .= "Method: " . $method . PHP_EOL;
        $logMsg .= "HTTP Code: " . $httpCode . PHP_EOL;
        $logMsg .= "cURL Error: " . $curlError . PHP_EOL;
        $logMsg .= "Response Body: " . $resposta . PHP_EOL;
        $logMsg .= "--------------------------------------" . PHP_EOL . PHP_EOL;
        @file_put_contents($logPath, $logMsg, FILE_APPEND);

        return [
            'code' => $httpCode,
            'body' => json_decode($resposta, true)
        ];
    }

    /**
     * 3. Busca eventos da fila do iFood (Polling)
     */
    public function getEvents() {
        $res = $this->request('/order/v1.0/events:polling');
        if ($res['code'] === 200 || $res['code'] === 204) {
            return $res['body'] ?? [];
        }
        return [];
    }

    /**
     * 4. Envia a confirmação de recebimento do evento (Acknowledgment)
     * Isso impede que o iFood envie o mesmo evento novamente na próxima busca.
     */
    public function acknowledgeEvents(array $eventIds) {
        $body = [];
        foreach ($eventIds as $id) {
            $body[] = ['id' => $id];
        }
        $res = $this->request('/order/v1.0/events/acknowledgment', 'POST', $body);
        return $res['code'] === 202;
    }

    /**
     * 5. Obtém os detalhes completos de um pedido específico
     */
    public function getOrderDetails($orderId) {
        $res = $this->request("/order/v1.0/orders/{$orderId}");
        if ($res['code'] === 200) {
            return $res['body'];
        }
        return null;
    }

    /**
     * 6. Atualiza o status do pedido no iFood para "Confirmado" (Aceito na Cozinha)
     */
    public function confirmOrder($orderId) {
        $res = $this->request("/order/v1.0/orders/{$orderId}/confirm", 'POST');
        return $res['code'] === 202;
    }

    /**
     * 7. Inicia a Preparação do Pedido
     */
    public function startPreparation($orderId) {
        $res = $this->request("/order/v1.0/orders/{$orderId}/startPreparation", 'POST');
        return $res['code'] === 202;
    }

    /**
     * 8. Despacha o pedido (Saiu para entrega)
     */
    public function dispatchOrder($orderId) {
        $res = $this->request("/order/v1.0/orders/{$orderId}/dispatch", 'POST');
        return $res['code'] === 202;
    }

    /**
     * 9. Solicita Cancelamento de Pedido
     * Requer um código de motivo e descrição amigável.
     */
    public function requestCancellation($orderId, $reasonCode = '501', $details = 'Problemas operacionais na cozinha.') {
        $body = [
            'reasonCode' => $reasonCode,
            'details' => $details
        ];
        $res = $this->request("/order/v1.0/orders/{$orderId}/requestCancellation", 'POST', $body);
        return $res['code'] === 202;
    }
}
?>
