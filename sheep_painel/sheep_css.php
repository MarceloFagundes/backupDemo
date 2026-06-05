<?php
// Detect if we are on the PDV dashboard (no 'm' parameter in URL)
$modulo_ativo = filter_input(INPUT_GET, 'm', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
?>

<!-- Icons & Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/bundles/font-awesome/css/font-awesome.min.css">

<!-- Premium CSS loaded on all pages -->
<link rel="stylesheet" href="assets/css/pdv_clean.css?v=<?= time(); ?>">

<?php if (!empty($modulo_ativo)): ?>
  <!-- Additional modules (Select2, Summernote) -->
  <link rel="stylesheet" href="assets/bundles/select2/dist/css/select2.min.css">
<?php endif; ?>
