
<?php
// Inicia a sessão se ainda não foi iniciada (embora o index.php já faça isso)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['carrinho'])){
    $_SESSION['carrinho'] = array();
}

$sheep = new Ler();
$sheep->Leitura('configuracoes', "WHERE id = '1'");
$status_loja = 'aberta';
if($sheep->getResultado()){
    $status_loja = $sheep->getResultado()[0]['status_loja'] ?? 'aberta';
}

// Adicionar item
if(isset($_GET['add'])){
    if($status_loja == 'fechada'){
        echo "<script>alert('Desculpe, a loja está fechada no momento!'); window.location.href='".HOME."/loja';</script>";
        exit;
    }
    
    $id = $_GET['add'];
    
    // Processo idêntico ao da pizza montada: ler do banco UMA vez e salvar na sessão
    $sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
    $res = $sheep->getResultado();
    
    if($res){
        $p = $res[0];
        $preco = $p['preco_promocional'] ? $p['preco_promocional'] : $p['preco'];
        if(!isset($_SESSION['produtos_normais'])) {
            $_SESSION['produtos_normais'] = array();
        }
        $_SESSION['produtos_normais'][$id] = [
            'nome' => $p['nome'],
            'preco' => $preco,
            'imagem' => $p['imagem']
        ];
    }

    if(!isset($_SESSION['carrinho'][$id])){
        $_SESSION['carrinho'][$id] = 1;
    } else {
        $_SESSION['carrinho'][$id]++;
    }
    session_write_close();
    header("Location: " . HOME . "/carrinho");
    exit;
}

// Remover item
if(isset($_GET['remove'])){
    $id = $_GET['remove']; // Agora pode ser string (ex: custom_123)
    if(isset($_SESSION['carrinho'][$id])){
        unset($_SESSION['carrinho'][$id]);
        if (strpos((string)$id, 'custom_') === 0) {
            unset($_SESSION['custom_pizzas'][$id]);
        }
    }
    session_write_close();
    header("Location: " . HOME . "/carrinho");
    exit;
}

// Limpar carrinho
if(isset($_GET['limpar'])){
    unset($_SESSION['carrinho']);
    session_write_close();
    header("Location: " . HOME . "/carrinho");
    exit;
}

require_once('header.php');
?>

<div class="topo-pagina">
	<h1>Seu Carrinho</h1>
	<p><a href="<?= HOME ?>" title="">Inicio >> </a>Carrinho</p>
</div>

<section class="carrinho-pagina">
    <div class="box-container">
        <?php
        $total = 0;
        if(!empty($_SESSION['carrinho'])):
            foreach($_SESSION['carrinho'] as $id => $qtd):
                if (strpos((string)$id, 'custom_') === 0) {
                    // É uma pizza montada
                    $customPizza = isset($_SESSION['custom_pizzas'][$id]) ? $_SESSION['custom_pizzas'][$id] : null;
                    $nome = $customPizza ? $customPizza['nome'] : "Pizza Personalizada";
                    $detalhes = $customPizza ? $customPizza['detalhes'] : "Montada no site";
                    $preco = $customPizza ? $customPizza['preco'] : 59.90;
                    $imagem = $customPizza ? $customPizza['imagem'] : "pizza-1.png";
                    
                    $subtotal = $preco * $qtd;
                    $total += $subtotal;
                    ?>
                    <div class="box">
                        <img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="Pizza Montada" loading="lazy" decoding="async">
                        <div class="content">
                            <h3><?= $nome ?></h3>
                            <p style="font-size: 1.3rem; color: #666; margin-bottom: 0.5rem;"><em><?= $detalhes ?></em></p>
                            <span>Preço: R$ <?= number_format($preco, 2, ',', '.') ?></span>
                            <p>Quantidade: <?= $qtd ?></p>
                            <p>Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
                        </div>
                        <a href="<?= HOME ?>/carrinho?remove=<?= $id ?>" class="fas fa-trash"></a>
                    </div>
                    <?php
                } else {
                    // Produto normal (processo repetido da pizza montada)
                    $produtoNormal = isset($_SESSION['produtos_normais'][$id]) ? $_SESSION['produtos_normais'][$id] : null;
                    
                    if($produtoNormal) {
                        $nome = $produtoNormal['nome'];
                        $preco = $produtoNormal['preco'];
                        $imagem = $produtoNormal['imagem'];
                    } else {
                        // Fallback defensivo caso a sessão não tenha o produto salvo
                        $sheep->Leitura('produtos', "WHERE id = :id", "id={$id}");
                        $produto = $sheep->getResultado();
                        $nome = "Pizza Tradicional";
                        $preco = 49.90;
                        $imagem = "pizza-1.png";
                        
                        if($produto){
                            $produto = $produto[0];
                            $nome = $produto['nome'];
                            $preco = $produto['preco_promocional'] ? $produto['preco_promocional'] : $produto['preco'];
                            $imagem = $produto['imagem'];
                        }
                    }
                    
                    $subtotal = $preco * $qtd;
                    $total += $subtotal;
        ?>
            <div class="box">
                <img src="<?= mondiniTemaImagemUrl('loja/' . $imagem) ?>" alt="<?= $nome ?>" loading="lazy" decoding="async">
                <div class="content">
                    <h3><?= $nome ?></h3>
                    <span>Preço: R$ <?= number_format($preco, 2, ',', '.') ?></span>
                    <p>Quantidade: <?= $qtd ?></p>
                    <p>Subtotal: R$ <?= number_format($subtotal, 2, ',', '.') ?></p>
                </div>
                <a href="<?= HOME ?>/carrinho?remove=<?= $id ?>" class="fas fa-trash"></a>
            </div>
        <?php 
                } // Fim do if custom
            endforeach;
        ?>
        
        <div class="total-carrinho">
            <h3>Total: R$ <?= number_format($total, 2, ',', '.') ?></h3>
            <div class="botoes">
                <a href="<?= HOME ?>/loja" class="btn">Continuar Comprando</a>
                <?php if($status_loja == 'fechada'): ?>
                    <button class="btn" style="background: #95a5a6; cursor: not-allowed; opacity: 0.8;" disabled>Loja Fechada</button>
                <?php else: ?>
                    <a href="<?= HOME ?>/finalizar" class="btn">Finalizar Pedido</a>
                <?php endif; ?>
                <a href="<?= HOME ?>/carrinho?limpar=true" class="btn" style="background: var(--red);">Limpar Carrinho</a>
            </div>
        </div>

        <?php else: ?>
            <div class="carrinho-vazio">
                <h2>Seu carrinho está vazio!</h2>
                <a href="<?= HOME ?>/loja" class="btn">Ir para a Loja</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
require_once('footer.php');
?>

<style>
.carrinho-pagina {
    padding: 2rem 9%;
}

.carrinho-pagina .box-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.carrinho-pagina .box {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 2rem;
    background: #fff;
    border-radius: .5rem;
    box-shadow: var(--box-shadow);
    position: relative;
}

.carrinho-pagina .box img {
    height: 10rem;
}

.carrinho-pagina .box .content h3 {
    font-size: 2rem;
    color: var(--black);
    padding-bottom: .5rem;
}

.carrinho-pagina .box .content span {
    font-size: 1.5rem;
    color: var(--light-color);
}

.carrinho-pagina .box .content p {
    font-size: 1.5rem;
    color: var(--light-color);
    padding-top: .5rem;
}

.carrinho-pagina .box .fa-trash {
    position: absolute;
    top: 2rem;
    right: 2rem;
    font-size: 2rem;
    color: var(--red);
    cursor: pointer;
}

.carrinho-pagina .box .fa-trash:hover {
    color: var(--black);
}

.total-carrinho {
    padding: 2rem;
    background: #fff;
    border-radius: .5rem;
    box-shadow: var(--box-shadow);
    text-align: center;
}

.total-carrinho h3 {
    font-size: 2.5rem;
    color: var(--black);
    margin-bottom: 2rem;
}

.total-carrinho .botoes {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.carrinho-vazio {
    text-align: center;
    padding: 5rem 2rem;
}

.carrinho-vazio h2 {
    font-size: 3rem;
    color: var(--black);
    margin-bottom: 2rem;
}
</style>
