<?php
session_start();
require_once('../../sheep_core/config.php');
// Verificação de autenticação: apenas usuários logados com nível Administrador
if (empty($_SESSION['sheep_user']) || !in_array(($_SESSION['sheep_user']['nivel'] ?? 'C'), ['M'], true)) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'erro' => 'Acesso negado.']);
    exit;
}


$response = ['sucesso' => false, 'erro' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status_loja'] ?? '';

    if (in_array($status, ['aberta', 'fechada'])) {
        $atualizar = new Atualizar();
        $dados = ['status_loja' => $status];
        
        $atualizar->Atualizando('configuracoes', $dados, "WHERE id = :id", "id=1");

        if ($atualizar->getResultado()) {
            $response['sucesso'] = true;
            $response['status'] = $status;
        } else {
            $response['erro'] = 'Erro ao atualizar o status no banco de dados.';
        }
    } else {
        $response['erro'] = 'Status inválido.';
    }
} else {
    $response['erro'] = 'Método não permitido.';
}

header('Content-Type: application/json');
echo json_encode($response);
?>
