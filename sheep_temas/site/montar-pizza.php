<?php
require_once('header.php');

// Initialize data access
$sheep = new Ler();
?>

<!-- ESTILOS EXCLUSIVOS DA PÁGINA DE MONTAGEM DE PIZZA -->
<style>
.builder-secao {
    padding: 3rem 9%;
    background: #f8f9fa;
}

.aviso-loja-fechada-pagina {
    max-width: 1180px;
    margin: 2.5rem auto 0;
    padding: 1.8rem 2.2rem;
    border: 1px solid #f3c7c0;
    border-left: 6px solid #e74c3c;
    border-radius: 0.8rem;
    background: #fff5f3;
    color: #7f1d1d;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    box-shadow: 0 0.6rem 1.4rem rgba(231, 76, 60, 0.08);
}

.aviso-loja-fechada-pagina strong {
    font-size: 1.7rem;
    color: #c0392b;
}

.aviso-loja-fechada-pagina span {
    font-size: 1.35rem;
    line-height: 1.45;
}

.builder-container {
    display: flex;
    gap: 3rem;
    align-items: flex-start;
}

.builder-passos {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2.5rem;
}

.builder-card {
    background: #fff;
    border-radius: 1rem;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
    overflow: hidden;
    transition: transform 0.3s ease;
}

.builder-card:hover {
    transform: translateY(-2px);
}

.builder-card-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.8rem 2.5rem;
    background: #fafafa;
    border-bottom: 1px solid #eee;
}

.builder-card-header .numero {
    width: 3rem;
    height: 3rem;
    border-radius: 50%;
    background: var(--red, red);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: bold;
}

.builder-card-header h2 {
    font-size: 2rem;
    color: #130f40;
    margin: 0;
}

.builder-card-body {
    padding: 2.5rem;
}

/* Tamanhos Grid */
.sizes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
    gap: 2rem;
}

.size-option input[type="radio"],
.border-option input[type="radio"],
.drink-option input[type="radio"] {
    display: none;
}

.size-content, .border-content, .drink-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    border: 2px solid #ddd;
    border-radius: 1rem;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.size-option input[type="radio"]:checked + .size-content,
.border-option input[type="radio"]:checked + .border-content,
.drink-option input[type="radio"]:checked + .drink-content {
    border-color: var(--red, red);
    background: rgba(255, 0, 0, 0.02);
    box-shadow: 0 0.5rem 1rem rgba(255, 0, 0, 0.05);
}

.icon-pizza {
    font-size: 3rem;
    color: var(--red, red);
    margin-bottom: 1rem;
    transition: transform 0.3s ease;
}

.size-option:hover .icon-pizza {
    transform: scale(1.15) rotate(15deg);
}

.size-s .fa-pizza-slice { font-size: 2.2rem; }
.size-m .fa-pizza-slice { font-size: 2.8rem; }
.size-l .fa-pizza-slice { font-size: 3.5rem; }
.size-xl .fa-pizza-slice { font-size: 4.2rem; }

.size-content h3, .border-content h3, .drink-content h3 {
    font-size: 1.8rem;
    color: #130f40;
    margin-bottom: 0.5rem;
}

.size-content p, .border-content p, .drink-content p {
    font-size: 1.3rem;
    color: #666;
    margin-bottom: 1rem;
}

.preco-tag {
    font-size: 1.6rem;
    font-weight: bold;
    color: #27ae60;
    background: #e8f8f0;
    padding: 0.4rem 1.2rem;
    border-radius: 5rem;
}

/* Sabores Grid */
.flavors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(28rem, 1fr));
    gap: 2rem;
}

.flavor-option {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 1rem;
    align-items: center;
    background: #fff;
    transition: all 0.3s ease;
}

.flavor-option.selected {
    border-color: var(--red, red);
    background: rgba(255, 0, 0, 0.01);
}

.flavor-option img {
    width: 8rem;
    height: 8rem;
    object-fit: cover;
    border-radius: 50%;
}

.flavor-details {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.flavor-details h3 {
    font-size: 1.6rem;
    color: #130f40;
    margin-bottom: 0.3rem;
}

.flavor-details p {
    font-size: 1.2rem;
    color: #777;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.btn-select-flavor {
    align-self: flex-start;
    padding: 0.5rem 1.5rem;
    border: 1px solid #ddd;
    border-radius: 5rem;
    background: #fff;
    color: #333;
    font-size: 1.3rem;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.btn-select-flavor:hover {
    border-color: var(--red, red);
    color: var(--red, red);
}

.flavor-option.selected .btn-select-flavor {
    background: var(--red, red);
    color: #fff;
    border-color: var(--red, red);
}

/* Bordas Grid */
.borders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
    gap: 2rem;
}

/* Extras List */
.extras-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(28rem, 1fr));
    gap: 2rem;
}

.extra-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.5rem;
    border: 1px solid #eee;
    border-radius: 1rem;
    background: #fff;
    transition: all 0.3s ease;
}

.extra-item:hover {
    border-color: #ccc;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.03);
}

.icon-extra {
    width: 5rem;
    height: 5rem;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
}

.extra-product-image {
    width: 6.4rem;
    height: 6.4rem;
    border-radius: 0.8rem;
    object-fit: contain;
    background: #fff;
    border: 1px solid #f0f0f0;
    padding: 0.4rem;
}

.extra-info {
    flex: 1;
}


.extra-info h3 {
    font-size: 1.5rem;
    color: #130f40;
    margin-bottom: 0.3rem;
}

.extra-qty {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.btn-qty {
    width: 3.2rem;
    height: 3.2rem;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: #f9f9f9;
    color: #333;
    font-size: 1.6rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-qty:hover {
    background: var(--red, red);
    color: #fff;
    border-color: var(--red, red);
}

.extra-qty input {
    width: 3rem;
    text-align: center;
    font-size: 1.6rem;
    font-weight: bold;
    border: none;
    background: transparent;
    color: #130f40;
}

/* Sidebar Resumo */
.builder-resumo-sidebar {
    width: 32rem;
    position: sticky;
    top: 10rem;
}

.resumo-fixado {
    background: #fff;
    border-radius: 1rem;
    padding: 2.5rem;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.05);
    border: 1px solid #eee;
}

.resumo-fixado h2 {
    font-size: 2.2rem;
    color: #130f40;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f5f5f5;
}

/* Pizza Preview visual */
.pizza-preview-container {
    display: flex;
    justify-content: center;
    margin-bottom: 2.5rem;
}

.pizza-preview-base {
    width: 22rem;
    height: 22rem;
    border-radius: 50%;
    background: #e0e0e0;
    border: 6px solid #d35400;
    position: relative;
    overflow: hidden;
    box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
}

.slice-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-size: 105%; /* Perfeito para o padding normalizado de 2.5% */
    background-position: center center;
    background-repeat: no-repeat;
    transition: all 0.3s ease;
}

/* Split effects for pizza flavors preview */
.pizza-preview-base.one-flavor .slice-overlay {
    width: 100%;
    height: 100%;
    clip-path: none;
}

.pizza-preview-base.two-flavors #preview-sabor-1 {
    clip-path: polygon(0 0, 50% 0, 50% 100%, 0 100%);
}

.pizza-preview-base.two-flavors #preview-sabor-2 {
    clip-path: polygon(50% 0, 100% 0, 100% 100%, 50% 100%);
}

.pizza-preview-base.three-flavors #preview-sabor-1 {
    clip-path: polygon(50% 50%, 0 0, 100% 0);
}

.pizza-preview-base.three-flavors #preview-sabor-2 {
    clip-path: polygon(50% 50%, 100% 0, 100% 100%, 50% 100%);
}

.pizza-preview-base.three-flavors #preview-sabor-3 {
    clip-path: polygon(50% 50%, 50% 100%, 0 100%, 0 0);
}

.resumo-detalhes {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    margin-bottom: 2rem;
}

.resumo-linha {
    display: flex;
    justify-content: space-between;
    font-size: 1.4rem;
}

.resumo-linha .label, .resumo-sabores-list .label {
    color: #777;
    font-weight: 500;
}

.resumo-linha .valor {
    color: #130f40;
    font-weight: 600;
}

.resumo-sabores-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    font-size: 1.4rem;
    border-top: 1px solid #f5f5f5;
    padding-top: 1.2rem;
}

.resumo-sabores-list ul {
    list-style: none;
    padding-left: 0;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.resumo-sabores-list li {
    font-size: 1.3rem;
    color: #130f40;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.resumo-sabores-list li::before {
    content: "•";
    color: var(--red, red);
    font-size: 1.8rem;
}

.resumo-sabores-list li.nenhum-sabor {
    color: #999;
    font-style: italic;
    font-weight: normal;
}

.resumo-sabores-list li.nenhum-sabor::before {
    display: none;
}

.total-box {
    border-top: 2px dashed #eee;
    padding-top: 1.8rem;
    margin-bottom: 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.total-box span {
    font-size: 1.5rem;
    color: #666;
    font-weight: 500;
}

.total-box h3 {
    font-size: 2.8rem;
    color: #27ae60;
    margin: 0;
}

.btn-add-carrinho {
    width: 100%;
    padding: 1.5rem;
    background: var(--red, red);
    color: #fff;
    border-color: var(--red, red);
    font-size: 1.6rem;
    font-weight: bold;
    border-radius: 5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-add-carrinho:hover:not(:disabled) {
    background: #c0392b;
    border-color: #c0392b;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.btn-add-carrinho:disabled {
    background: #ccc;
    border-color: #ccc;
    color: #666;
    cursor: not-allowed;
}

.alerta-flavors {
    font-size: 1.2rem;
    color: #e74c3c;
    text-align: center;
    margin-top: 1rem;
    font-weight: bold;
}

@media (max-width: 991px) {
    .builder-container {
        flex-direction: column;
    }
    
    .builder-resumo-sidebar {
        width: 100%;
        position: static;
    }

    .aviso-loja-fechada-pagina {
        margin: 2rem 1.5rem 0;
        align-items: flex-start;
        flex-direction: column;
    }
}
</style>

<?php
// Buscar os sabores (pizzas) cadastrados no banco de dados
$sheep->Leitura('produtos', "WHERE id NOT BETWEEN 5 AND 12 ORDER BY nome ASC");
$saboresDB = $sheep->getResultado();

$sheep->Leitura('produtos', "WHERE categoria = 'bebida' ORDER BY nome ASC");
$bebidasDB = $sheep->getResultado();

$sheep->Leitura('produtos', "WHERE categoria = 'adicional' ORDER BY nome ASC");
$extrasDB = $sheep->getResultado();

$bebidasFallback = [
    ['id' => 'coca-cola-2l', 'nome' => 'Coca-Cola 2L', 'preco' => 11.90, 'imagem' => '', 'icon' => 'fa-wine-bottle', 'color' => '#c0392b'],
    ['id' => 'guarana-antarctica-2l', 'nome' => 'Guaraná Antarctica 2L', 'preco' => 9.90, 'imagem' => '', 'icon' => 'fa-wine-bottle', 'color' => '#27ae60'],
    ['id' => 'coca-cola-lata', 'nome' => 'Coca-Cola Lata', 'preco' => 5.90, 'imagem' => '', 'icon' => 'fa-prescription-bottle', 'color' => '#e74c3c'],
    ['id' => 'fanta-laranja-lata', 'nome' => 'Fanta Laranja Lata', 'preco' => 5.90, 'imagem' => '', 'icon' => 'fa-prescription-bottle', 'color' => '#f39c12']
];

$listaBebidas = $bebidasDB ?: $bebidasFallback;
$listaExtras = $extrasDB ?: [];

$mapaBebidas = [];
foreach ($listaBebidas as $bebidaItem) {
    $mapaBebidas[(string)$bebidaItem['id']] = $bebidaItem;
}

$mapaExtras = [];
foreach ($listaExtras as $extraItem) {
    $mapaExtras[(string)$extraItem['id']] = $extraItem;
}

if (!function_exists('mondiniProdutoImagemUrl')) {
    function mondiniProdutoImagemUrl($imagem)
    {
        $imagem = trim((string)$imagem);
        if ($imagem === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $imagem)) {
            return $imagem;
        }

        if (strpos($imagem, '/') !== false || strpos($imagem, '\\') !== false) {
            $relativa = ltrim(str_replace('\\', '/', $imagem), '/');
            if (strpos($relativa, 'assets/img/') === 0) {
                $relativa = substr($relativa, strlen('assets/img/'));
            }
            return function_exists('mondiniTemaImagemUrl') ? mondiniTemaImagemUrl($relativa) : CAMINHO_TEMAS . '/assets/img/' . $relativa;
        }

        foreach (['loja', 'bebidas', 'extras'] as $pasta) {
            if (is_file(__DIR__ . '/assets/img/' . $pasta . '/' . $imagem)) {
                return function_exists('mondiniTemaImagemUrl') ? mondiniTemaImagemUrl($pasta . '/' . $imagem) : CAMINHO_TEMAS . '/assets/img/' . $pasta . '/' . $imagem;
            }
        }

        return function_exists('mondiniTemaImagemUrl') ? mondiniTemaImagemUrl('loja/' . $imagem) : CAMINHO_TEMAS . '/assets/img/loja/' . $imagem;
    }
}

// Processar a adição ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_custom'])) {
    if (($status_loja ?? 'aberta') == 'fechada') {
        header("Location: " . HOME . "/montar-pizza?loja_fechada=true");
        exit;
    }

    $tamanho = strip_tags($_POST['tamanho']);
    $saboresSelecionados = isset($_POST['sabores']) && is_array($_POST['sabores']) ? array_map('strip_tags', $_POST['sabores']) : [];
    $borda = strip_tags($_POST['borda']);
    
    // Definir preços base por tamanho
    $precoBase = 59.90;
    if ($tamanho === 'Broto') $precoBase = 39.90;
    elseif ($tamanho === 'Média') $precoBase = 49.90;
    elseif ($tamanho === 'Grande') $precoBase = 59.90;
    elseif ($tamanho === 'Gigante') $precoBase = 74.90;

    // Calcular acréscimo da borda
    $precoBorda = 0.00;
    if ($borda === 'Catupiry') $precoBorda = 8.90;
    elseif ($borda === 'Cheddar') $precoBorda = 9.90;
    elseif ($borda === 'Chocolate') $precoBorda = 11.90;

    $itensSelecionados = 'Nenhuma';
    $precoItens = 0.00;
    $itensArr = [];

    // Processar Bebidas
    if (isset($_POST['bebidas']) && is_array($_POST['bebidas'])) {
        foreach ($_POST['bebidas'] as $itemId => $qty) {
            $itemId = strip_tags((string)$itemId);
            $qty = (int)$qty;
            if ($qty > 0 && isset($mapaBebidas[$itemId])) {
                $preco = (float)$mapaBebidas[$itemId]['preco'];
                $nomeItem = strip_tags($mapaBebidas[$itemId]['nome']);
                $precoItens += ($preco * $qty);
                $itensArr[] = "{$qty}x {$nomeItem}";
            }
        }
    }

    // Processar Extras
    if (isset($_POST['extras']) && is_array($_POST['extras'])) {
        foreach ($_POST['extras'] as $itemId => $qty) {
            $itemId = strip_tags((string)$itemId);
            $qty = (int)$qty;
            if ($qty > 0 && isset($mapaExtras[$itemId])) {
                $preco = (float)$mapaExtras[$itemId]['preco'];
                $nomeItem = strip_tags($mapaExtras[$itemId]['nome']);
                $precoItens += ($preco * $qty);
                $itensArr[] = "{$qty}x {$nomeItem}";
            }
        }
    }

    if (count($itensArr) > 0) {
        $itensSelecionados = implode(' + ', $itensArr);
    }

    $precoTotal = $precoBase + $precoBorda + $precoItens;

    // Criar descrição amigável
    $saboresTexto = implode(' + ', $saboresSelecionados);
    if (empty($saboresTexto)) {
        $saboresTexto = "Muçarela Tradicional";
    }
    
    $detalhes = "Tamanho: {$tamanho} | Sabores: {$saboresTexto} | Borda: {$borda}";
    if ($itensSelecionados !== 'Nenhuma') {
        $detalhes .= " | Itens: {$itensSelecionados}";
    }

    // Gerar ID exclusivo para este item personalizado
    $customId = 'custom_' . uniqid();

    // Salvar nas variáveis de sessão
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    if (!isset($_SESSION['custom_pizzas'])) {
        $_SESSION['custom_pizzas'] = [];
    }

    $_SESSION['carrinho'][$customId] = 1; // Quantidade inicial
    $_SESSION['custom_pizzas'][$customId] = [
        'nome' => "Pizza {$tamanho} Personalizada",
        'detalhes' => $detalhes,
        'preco' => $precoTotal,
        'imagem' => 'pizza-1.png' // Imagem genérica para pizza personalizada
    ];

    session_write_close();
    header("Location: " . HOME . "/carrinho");
    exit;
}
?>

<div class="topo-pagina">
    <h1>Monte sua Pizza</h1>
    <p><a href="<?= HOME ?>/index" title="Voltar ao início">Inicio >> </a>Personalizar</p>
</div>

<?php if(($status_loja ?? 'aberta') == 'fechada'): ?>
<div class="aviso-loja-fechada-pagina" role="alert">
    <strong><i class="fa fa-lock"></i> Loja fechada no momento</strong>
    <span>Voce pode montar e consultar os valores, mas nao sera possivel adicionar pedidos ao carrinho agora.</span>
</div>
<?php endif; ?>

<section class="builder-secao">
    <form action="" method="post" id="pizzaBuilderForm" class="builder-container">
        
        <!-- LADO ESQUERDO: PASSOS DA CUSTOMIZAÇÃO -->
        <div class="builder-passos">
            
            <!-- PASSO 1: TAMANHO -->
            <div class="builder-card active" id="passo-tamanho">
                <div class="builder-card-header">
                    <span class="numero">1</span>
                    <h2>Escolha o Tamanho</h2>
                </div>
                <div class="builder-card-body sizes-grid">
                    <label class="size-option">
                        <input type="radio" name="tamanho" value="Broto" data-price="39.90" data-flavors="1" onclick="atualizarTamanho('Broto', 1, 39.90)">
                        <div class="size-content">
                            <span class="icon-pizza size-s"><i class="fas fa-pizza-slice"></i></span>
                            <h3>Broto</h3>
                            <p>4 Fatias • 1 Sabor</p>
                            <span class="preco-tag">R$ 39,90</span>
                        </div>
                    </label>

                    <label class="size-option">
                        <input type="radio" name="tamanho" value="Média" data-price="49.90" data-flavors="2" onclick="atualizarTamanho('Média', 2, 49.90)">
                        <div class="size-content">
                            <span class="icon-pizza size-m"><i class="fas fa-pizza-slice"></i></span>
                            <h3>Média</h3>
                            <p>6 Fatias • Até 2 Sabores</p>
                            <span class="preco-tag">R$ 49,90</span>
                        </div>
                    </label>

                    <label class="size-option">
                        <input type="radio" name="tamanho" value="Grande" data-price="59.90" data-flavors="2" checked onclick="atualizarTamanho('Grande', 2, 59.90)">
                        <div class="size-content">
                            <span class="icon-pizza size-l"><i class="fas fa-pizza-slice"></i></span>
                            <h3>Grande</h3>
                            <p>8 Fatias • Até 2 Sabores</p>
                            <span class="preco-tag">R$ 59,90</span>
                        </div>
                    </label>

                    <label class="size-option">
                        <input type="radio" name="tamanho" value="Gigante" data-price="74.90" data-flavors="3" onclick="atualizarTamanho('Gigante', 3, 74.90)">
                        <div class="size-content">
                            <span class="icon-pizza size-xl"><i class="fas fa-pizza-slice"></i></span>
                            <h3>Gigante</h3>
                            <p>12 Fatias • Até 3 Sabores</p>
                            <span class="preco-tag">R$ 74,90</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- PASSO 2: SABORES -->
            <div class="builder-card" id="passo-sabores">
                <div class="builder-card-header">
                    <span class="numero">2</span>
                    <h2>Escolha os Sabores (<span id="info-limite">Até 2 sabores</span>)</h2>
                </div>
                <div class="builder-card-body flavors-grid">
                    <?php if ($saboresDB): foreach ($saboresDB as $sabor): ?>
                        <div class="flavor-option" id="sabor-card-<?= $sabor['id'] ?>">
                            <img src="<?= mondiniProdutoImagemUrl($sabor['imagem']) ?>" alt="<?= $sabor['nome'] ?>" loading="lazy" decoding="async">
                            <div class="flavor-details">
                                <h3><?= htmlspecialchars($sabor['nome'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <p><?= htmlspecialchars($sabor['descricao'], ENT_QUOTES, 'UTF-8') ?></p>
                                <button type="button" class="btn-select-flavor" onclick="toggleSabor('<?= addslashes($sabor['nome']) ?>', <?= $sabor['id'] ?>, '<?= mondiniProdutoImagemUrl($sabor['imagem']) ?>')">Selecionar</button>
                                <input type="checkbox" name="sabores[]" value="<?= $sabor['nome'] ?>" id="check-sabor-<?= $sabor['id'] ?>" style="display:none;">
                            </div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>

            <!-- PASSO 3: BORDAS -->
            <div class="builder-card" id="passo-borda">
                <div class="builder-card-header">
                    <span class="numero">3</span>
                    <h2>Escolha a Borda</h2>
                </div>
                <div class="builder-card-body borders-grid">
                    <label class="border-option">
                        <input type="radio" name="borda" value="Sem Borda" checked data-price="0.00" onclick="atualizarBorda('Sem Borda', 0.00)">
                        <div class="border-content">
                            <h3>Sem Borda</h3>
                            <p>Tradicional</p>
                            <span class="preco-tag">Grátis</span>
                        </div>
                    </label>

                    <label class="border-option">
                        <input type="radio" name="borda" value="Catupiry" data-price="8.90" onclick="atualizarBorda('Catupiry', 8.90)">
                        <div class="border-content">
                            <h3>Catupiry</h3>
                            <p>Catupiry Original</p>
                            <span class="preco-tag">+ R$ 8,90</span>
                        </div>
                    </label>

                    <label class="border-option">
                        <input type="radio" name="borda" value="Cheddar" data-price="9.90" onclick="atualizarBorda('Cheddar', 9.90)">
                        <div class="border-content">
                            <h3>Cheddar</h3>
                            <p>Cheddar Cremoso</p>
                            <span class="preco-tag">+ R$ 9,90</span>
                        </div>
                    </label>

                    <label class="border-option">
                        <input type="radio" name="borda" value="Chocolate" data-price="11.90" onclick="atualizarBorda('Chocolate', 11.90)">
                        <div class="border-content">
                            <h3>Chocolate</h3>
                            <p>Chocolate ao Leite</p>
                            <span class="preco-tag">+ R$ 11,90</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- PASSO 4: BEBIDAS -->
            <div class="builder-card" id="passo-bebidas">
                <div class="builder-card-header">
                    <span class="numero">4</span>
                    <h2>Escolha suas Bebidas</h2>
                </div>
                <div class="builder-card-body extras-grid">
                    
                    <?php foreach($listaBebidas as $bebidaItem):
                        $safeId = 'bebida-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$bebidaItem['id']);
                        $bebidaNome = htmlspecialchars($bebidaItem['nome'], ENT_QUOTES, 'UTF-8');
                        $bebidaImagem = mondiniProdutoImagemUrl($bebidaItem['imagem'] ?? '');
                        $bebidaIcon = $bebidaItem['icon'] ?? 'fa-wine-bottle';
                        $bebidaColor = $bebidaItem['color'] ?? '#c0392b';
                    ?>
                    <div class="extra-item">
                        <?php if ($bebidaImagem): ?>
                            <img class="extra-product-image" src="<?= $bebidaImagem ?>" alt="<?= $bebidaNome ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png';">
                        <?php else: ?>
                            <span class="icon-extra"><i class="fas <?= $bebidaIcon ?>" style="color: <?= $bebidaColor ?>;"></i></span>
                        <?php endif; ?>
                        <div class="extra-info">
                            <h3><?= $bebidaNome ?></h3>
                            <span class="preco-tag">+ R$ <?= number_format($bebidaItem['preco'], 2, ',', '.') ?></span>
                        </div>
                        <div class="extra-qty">
                            <button type="button" class="btn-qty" onclick="mudarExtra('<?= addslashes($bebidaItem['nome']) ?>', '<?= $safeId ?>', <?= $bebidaItem['preco'] ?>, -1)"><i class="fas fa-minus"></i></button>
                            <input type="text" name="bebidas[<?= $bebidaItem['id'] ?>]" id="qty-<?= $safeId ?>" value="0" readonly>
                            <button type="button" class="btn-qty" onclick="mudarExtra('<?= addslashes($bebidaItem['nome']) ?>', '<?= $safeId ?>', <?= $bebidaItem['preco'] ?>, 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>

            <?php if (!empty($listaExtras)): ?>
            <!-- PASSO 5: EXTRAS -->
            <div class="builder-card" id="passo-extras">
                <div class="builder-card-header">
                    <span class="numero">5</span>
                    <h2>Deseja adicionar Extras?</h2>
                </div>
                <div class="builder-card-body extras-grid">
                    
                    <?php foreach($listaExtras as $extra):
                        $safeId = 'extra-' . preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$extra['id']);
                        $extraNome = htmlspecialchars($extra['nome'], ENT_QUOTES, 'UTF-8');
                        $extraImagem = mondiniProdutoImagemUrl($extra['imagem'] ?? '');
                        $extraIcon = $extra['icon'] ?? 'fa-cookie';
                        $extraColor = $extra['color'] ?? '#d35400';
                    ?>
                    <div class="extra-item">
                        <?php if ($extraImagem): ?>
                            <img class="extra-product-image" src="<?= $extraImagem ?>" alt="<?= $extraNome ?>" loading="lazy" decoding="async" onerror="this.onerror=null;this.src='<?= HOME ?>/sheep_painel/assets/img/sem-imagem.png';">
                        <?php else: ?>
                            <span class="icon-extra"><i class="fas <?= $extraIcon ?>" style="color: <?= $extraColor ?>;"></i></span>
                        <?php endif; ?>
                        <div class="extra-info">
                            <h3><?= $extraNome ?></h3>
                            <span class="preco-tag">+ R$ <?= number_format($extra['preco'], 2, ',', '.') ?></span>
                        </div>
                        <div class="extra-qty">
                            <button type="button" class="btn-qty" onclick="mudarExtra('<?= addslashes($extra['nome']) ?>', '<?= $safeId ?>', <?= $extra['preco'] ?>, -1)"><i class="fas fa-minus"></i></button>
                            <input type="text" name="extras[<?= $extra['id'] ?>]" id="qty-<?= $safeId ?>" value="0" readonly>
                            <button type="button" class="btn-qty" onclick="mudarExtra('<?= addslashes($extra['nome']) ?>', '<?= $safeId ?>', <?= $extra['preco'] ?>, 1)"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                    <?php endforeach; ?>

                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- LADO DIREITO: LIVE PREVIEW & RESUMO DO PEDIDO -->
        <div class="builder-resumo-sidebar">
            <div class="resumo-fixado">
                <h2>Sua Pizza</h2>
                
                <!-- Pizza visual representation (circle split preview) -->
                <div class="pizza-preview-container">
                    <div class="pizza-preview-base" id="pizzaPreviewImg">
                        <div class="slice-overlay" id="preview-sabor-1" style="background-image: url('<?= mondiniProdutoImagemUrl('pizza-1.png') ?>');"></div>
                        <div class="slice-overlay" id="preview-sabor-2" style="background-image: url('<?= mondiniProdutoImagemUrl('pizza-2.png') ?>'); display: none;"></div>
                        <div class="slice-overlay" id="preview-sabor-3" style="background-image: url('<?= mondiniProdutoImagemUrl('pizza-3.png') ?>'); display: none;"></div>
                    </div>
                </div>

                <div class="resumo-detalhes">
                    <div class="resumo-linha">
                        <span class="label">Tamanho:</span>
                        <span class="valor" id="resumo-tamanho">Grande (8 fatias)</span>
                    </div>
                    <div class="resumo-linha">
                        <span class="label">Borda:</span>
                        <span class="valor" id="resumo-borda">Sem Borda</span>
                    </div>
                    <div class="resumo-linha">
                        <span class="label"><?= !empty($listaExtras) ? 'Extras/Bebidas:' : 'Bebidas:' ?></span>
                        <div class="valor" id="resumo-bebida" style="text-align: right; line-height: 1.5;">Nenhum</div>
                    </div>
                    <div class="resumo-sabores-list">
                        <span class="label">Sabores Selecionados:</span>
                        <ul id="resumo-sabores">
                            <li class="nenhum-sabor">Nenhum sabor selecionado</li>
                        </ul>
                    </div>
                </div>

                <div class="total-box">
                    <span>Total a Pagar</span>
                    <h3 id="resumo-preco-total">R$ 59,90</h3>
                </div>

                <?php if($status_loja == 'fechada'): ?>
                    <button type="button" class="btn btn-add-carrinho" id="btnSubmitBuilder" style="background: #95a5a6; cursor: not-allowed;" disabled>
                        <i class="fa fa-lock"></i> Loja Fechada
                    </button>
                    <script>const isLojaFechada = true;</script>
                <?php else: ?>
                    <button type="submit" name="adicionar_custom" class="btn btn-add-carrinho" id="btnSubmitBuilder" disabled>
                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                    </button>
                    <script>const isLojaFechada = false;</script>
                    <p class="alerta-flavors" id="alerta-flavors-msg">Selecione pelo menos 1 sabor!</p>
                <?php endif; ?>
            </div>
        </div>

    </form>
</section>



<!-- CLIENT-SIDE LOGIC FOR DYNAMIC PRICE SUMMATION AND SELECTIONS -->
<script>
let currentSize = 'Grande';
let limitFlavors = 2;
let selectedFlavors = [];
let selectedImages = [];

// Preços dinâmicos das seleções
let priceBase = 59.90;
let priceBorda = 0.00;
let priceBebida = 0.00;

// Imagem base genérica
const previewImagePadrao = '<?= mondiniProdutoImagemUrl('pizza-1.png') ?>';

function atualizarTamanho(nome, limite, preco) {
    currentSize = nome;
    limitFlavors = limite;
    priceBase = parseFloat(preco);
    
    // Atualizar UI
    document.getElementById('resumo-tamanho').innerText = nome + " (" + (nome === 'Broto' ? '4' : (nome === 'Média' ? '6' : (nome === 'Grande' ? '8' : '12'))) + " fatias)";
    document.getElementById('info-limite').innerText = "Até " + limite + " sabor" + (limite > 1 ? "es" : "");
    
    // Resetar sabores se excederem o novo limite
    if (selectedFlavors.length > limitFlavors) {
        // Desmarcar todos os excessos
        for (let i = selectedFlavors.length - 1; i >= limitFlavors; i--) {
            let flavorName = selectedFlavors[i];
            // Encontrar checkbox e desmarcar
            let checkboxes = document.getElementsByName('sabores[]');
            for (let chk of checkboxes) {
                if (chk.value === flavorName) {
                    chk.checked = false;
                    chk.closest('.flavor-option').classList.remove('selected');
                }
            }
            selectedFlavors.pop();
            selectedImages.pop();
        }
    }
    
    atualizarResumoSabores();
    calcularTotal();
}

function atualizarBorda(nome, preco) {
    priceBorda = parseFloat(preco);
    document.getElementById('resumo-borda').innerText = nome + (parseFloat(preco) > 0 ? ' (+ R$ ' + preco.toFixed(2).replace('.', ',') + ')' : '');
    calcularTotal();
}

let selectedExtrasObj = {};

function mudarExtra(nome, idSafe, preco, delta) {
    let input = document.getElementById('qty-' + idSafe);
    
    if(!selectedExtrasObj[nome]) {
        selectedExtrasObj[nome] = { qty: 0, price: preco };
    }
    
    let currentQty = selectedExtrasObj[nome].qty;
    let newQty = currentQty + delta;
    if(newQty < 0) newQty = 0;
    
    selectedExtrasObj[nome].qty = newQty;
    input.value = newQty;
    
    atualizarResumoExtras();
}

function atualizarResumoExtras() {
    priceBebida = 0.00; // Reproveitando a variável para todos os extras
    const list = document.getElementById('resumo-bebida');
    list.innerHTML = "";
    
    let extrasHtml = "";
    for(let nome in selectedExtrasObj) {
        let item = selectedExtrasObj[nome];
        if(item.qty > 0) {
            let sub = item.qty * item.price;
            priceBebida += sub;
            extrasHtml += `<div style="font-size: 1.2rem; color: #555;">${item.qty}x ${nome} <span style="color:#27ae60;">(+R$ ${sub.toFixed(2).replace('.',',')})</span></div>`;
        }
    }
    
    if(extrasHtml === "") {
        list.innerHTML = "Nenhum";
    } else {
        list.innerHTML = extrasHtml;
    }
    
    calcularTotal();
}

function toggleSabor(nome, id, imageUrl) {
    const chk = document.getElementById('check-sabor-' + id);
    const card = document.getElementById('sabor-card-' + id);
    
    if (selectedFlavors.includes(nome)) {
        // Desmarcar
        let idx = selectedFlavors.indexOf(nome);
        selectedFlavors.splice(idx, 1);
        selectedImages.splice(idx, 1);
        chk.checked = false;
        card.classList.remove('selected');
    } else {
        // Verificar limite
        if (selectedFlavors.length >= limitFlavors) {
            alert("Você atingiu o limite de " + limitFlavors + " sabor(es) para o tamanho " + currentSize + "! Desmarque um sabor para selecionar outro.");
            return;
        }
        // Marcar
        selectedFlavors.push(nome);
        selectedImages.push(imageUrl);
        chk.checked = true;
        card.classList.add('selected');
    }
    
    atualizarResumoSabores();
    calcularTotal();
}

function atualizarResumoSabores() {
    const list = document.getElementById('resumo-sabores');
    list.innerHTML = "";
    
    if (selectedFlavors.length === 0) {
        list.innerHTML = `<li class="nenhum-sabor">Nenhum sabor selecionado</li>`;
        
        // Habilitar/Desabilitar submit e alerta
        if(document.getElementById('btnSubmitBuilder')) document.getElementById('btnSubmitBuilder').disabled = true;
        if(document.getElementById('alerta-flavors-msg')) document.getElementById('alerta-flavors-msg').style.display = 'block';
    } else {
        selectedFlavors.forEach(sabor => {
            list.innerHTML += `<li>${sabor}</li>`;
        });
        
        if(!isLojaFechada && document.getElementById('btnSubmitBuilder')) document.getElementById('btnSubmitBuilder').disabled = false;
        if(document.getElementById('alerta-flavors-msg')) document.getElementById('alerta-flavors-msg').style.display = 'none';
    }
    
    // Atualizar preview visual da pizza dividida
    const previewContainer = document.getElementById('pizzaPreviewImg');
    
    // Remover classes anteriores
    previewContainer.className = "pizza-preview-base";
    
    const div1 = document.getElementById('preview-sabor-1');
    const div2 = document.getElementById('preview-sabor-2');
    const div3 = document.getElementById('preview-sabor-3');
    
    div1.style.display = 'none';
    div2.style.display = 'none';
    div3.style.display = 'none';
    
    if (selectedFlavors.length === 1) {
        previewContainer.classList.add('one-flavor');
        div1.style.display = 'block';
        div1.style.backgroundImage = `url('${selectedImages[0]}')`;
        div1.style.filter = "none";
    } else if (selectedFlavors.length === 2) {
        previewContainer.classList.add('two-flavors');
        div1.style.display = 'block';
        div2.style.display = 'block';
        div1.style.backgroundImage = `url('${selectedImages[0]}')`;
        div2.style.backgroundImage = `url('${selectedImages[1]}')`;
        div1.style.filter = "none";
        div2.style.filter = "none";
    } else if (selectedFlavors.length === 3) {
        previewContainer.classList.add('three-flavors');
        div1.style.display = 'block';
        div2.style.display = 'block';
        div3.style.display = 'block';
        div1.style.backgroundImage = `url('${selectedImages[0]}')`;
        div2.style.backgroundImage = `url('${selectedImages[1]}')`;
        div3.style.backgroundImage = `url('${selectedImages[2]}')`;
        div1.style.filter = "none";
        div2.style.filter = "none";
        div3.style.filter = "none";
    } else {
        // Sem sabores, apenas base cinza
        previewContainer.className = "pizza-preview-base";
    }
}

function calcularTotal() {
    let total = priceBase + priceBorda + priceBebida;
    document.getElementById('resumo-preco-total').innerText = "R$ " + total.toFixed(2).replace('.', ',');
}

// Inicializar na primeira carga
window.addEventListener('DOMContentLoaded', () => {
    // Definir inicial do checked (Grande)
    atualizarTamanho('Grande', 2, 59.90);
});
</script>

<?php if (isset($_GET['sabor_id']) && is_numeric($_GET['sabor_id'])): 
    $saborId = (int)$_GET['sabor_id'];
    $sheep->Leitura('produtos', "WHERE id = :id", "id={$saborId}");
    $saborAuto = $sheep->getResultado();
    if ($saborAuto): 
        $saborObj = $saborAuto[0];
?>
<script>
window.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => {
        // Seleciona a pizza que veio da URL
        toggleSabor('<?= addslashes($saborObj['nome']) ?>', <?= $saborObj['id'] ?>, '<?= mondiniProdutoImagemUrl($saborObj['imagem']) ?>');
        
        // Rola a página suavemente para o painel de montagem
        const passoTamanho = document.getElementById('passo-tamanho');
        if (passoTamanho) {
            passoTamanho.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 200);
});
</script>
<?php 
    endif; 
endif; 
?>

<?php
require_once('footer.php');
?>
