<?php
/**
 * FirebaseFCM [ MODEL ]
 * Classe responsável pelo envio de notificações push via Firebase Cloud Messaging.
 * 
 * @copyright (c) Pizzaria Modelo
 */
class FirebaseFCM {

    /**
     * Envia uma notificação push para um token FCM específico.
     * 
     * @param string $token Token FCM do destinatário
     * @param string $titulo Título do push
     * @param string $mensagem Corpo da notificação
     * @param int $pedidoId ID do pedido para redirecionamento
     * @return boolean Sucesso ou falha no envio
     */
    public static function enviarPush($token, $titulo, $mensagem, $pedidoId = 0) {
        // Se a chave do servidor não estiver configurada ou for vazia, não prossegue
        if (!defined('FIREBASE_SERVER_KEY') || empty(FIREBASE_SERVER_KEY) || FIREBASE_SERVER_KEY === 'SUA_CHAVE_DO_SERVIDOR_AQUI') {
            file_put_contents('log_fcm.txt', "[" . date('Y-m-d H:i:s') . "] ERRO: FIREBASE_SERVER_KEY não está configurada no config.php\n", FILE_APPEND);
            return false;
        }

        if (empty($token)) {
            file_put_contents('log_fcm.txt', "[" . date('Y-m-d H:i:s') . "] AVISO: Tentativa de envio para token vazio no pedido #{$pedidoId}\n", FILE_APPEND);
            return false;
        }

        $url = 'https://fcm.googleapis.com/fcm/send';
        
        // Link de clique redireciona direto para a tela de acompanhamento do pedido
        $clickAction = HOME . '/sucesso?id=' . $pedidoId;
        $iconUrl = HOME . '/sheep_temas/site/assets/img/msFavicon.png';

        $payload = [
            'to' => $token,
            'notification' => [
                'title' => $titulo,
                'body' => $mensagem,
                'icon' => $iconUrl,
                'click_action' => $clickAction,
                'sound' => 'default'
            ],
            'data' => [
                'pedido_id' => $pedidoId,
                'origem' => 'pizzaria_modelo'
            ],
            'priority' => 'high'
        ];

        $headers = [
            'Authorization: key=' . FIREBASE_SERVER_KEY,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $response = json_decode($result, true);

        if ($httpCode === 200 && isset($response['success']) && $response['success'] > 0) {
            file_put_contents('log_fcm.txt', "[" . date('Y-m-d H:i:s') . "] SUCESSO: Push enviado com sucesso para pedido #{$pedidoId}. Resposta: " . $result . "\n", FILE_APPEND);
            return true;
        } else {
            file_put_contents('log_fcm.txt', "[" . date('Y-m-d H:i:s') . "] ERRO: Falha ao enviar push para pedido #{$pedidoId}. HTTP: {$httpCode}. Resposta: " . $result . "\n", FILE_APPEND);
            return false;
        }
    }
}
?>
