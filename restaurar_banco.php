<?php
require_once('sheep_core/config.php');

echo "<h2>Restaurando Banco de Dados...</h2>";

try {
    // Acessa a conexão PDO interna da classe Conexao
    $pdo = Conexao::getConn();
    
    // Força UTF-8 para garantir que a importação não corrompa os acentos
    $pdo->exec("SET NAMES 'utf8'");
    
    // Lê o arquivo SQL
    $sqlFile = 'banco_exportado.sql';
    if (!file_exists($sqlFile)) {
        die("Erro: Arquivo $sqlFile não encontrado no servidor.");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // O arquivo SQL já é exportado nativamente em UTF-8 pelo MariaDB, 
    // então apenas executamos as instruções diretamente usando a conexão PDO que já está com SET NAMES utf8
    $pdo->exec($sql);
    
    echo "<p style='color:green; font-weight:bold;'>Banco de dados restaurado e corrigido para UTF-8 com sucesso!</p>";
    echo "<p>Todos os acentos das pizzas, produtos e categorias foram atualizados.</p>";
    echo "<p><strong>Por segurança, exclua este arquivo (restaurar_banco.php) e o banco_exportado.sql do servidor agora.</strong></p>";
    
} catch (PDOException $e) {
    echo "<p style='color:red;'>Erro ao importar: " . $e->getMessage() . "</p>";
}
?>
