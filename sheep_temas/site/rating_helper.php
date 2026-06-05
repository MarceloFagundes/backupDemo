<?php
// Gera nota baseada no id do produto (4.0 a 5.0, sempre igual pro mesmo produto)
function sheep_nota($id) {
    $notas = [4.0, 4.5, 4.0, 5.0, 4.5, 4.0, 4.5, 5.0, 4.0, 4.5];
    return $notas[$id % count($notas)];
}
function sheep_estrelas($id) {
    $nota = sheep_nota($id);
    $html = '<div class="estrelas">';
    $cheias = floor($nota);
    $meia   = ($nota - $cheias) >= 0.5;
    for ($i = 0; $i < $cheias; $i++)  $html .= '<i class="fa fa-star"></i>';
    if ($meia)                         $html .= '<i class="fa fa-star-half-alt"></i>';
    $html .= '</div>';
    return $html;
}
?>
