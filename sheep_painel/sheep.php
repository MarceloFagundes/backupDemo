          <?php
// Pizzaria Modelo - Painel de Controle
require_once('sheep_topo.php');

            $sheep_base_painel = realpath(__DIR__ . '/sheep_sistema');
            $sheep_modulo = !empty($ms) ? trim((string) $ms) : 'sheep_painel';
            $sheep_caminho_painel = false;

            if (
                $sheep_base_painel !== false &&
                preg_match('/^[a-zA-Z0-9_\/-]+$/', $sheep_modulo) &&
                strpos($sheep_modulo, '..') === false
            ):
                $sheep_real = realpath($sheep_base_painel . DIRECTORY_SEPARATOR . $sheep_modulo . '.php');
                if ($sheep_real !== false && strpos($sheep_real, $sheep_base_painel . DIRECTORY_SEPARATOR) === 0):
                    $sheep_caminho_painel = $sheep_real;
                endif;
            endif;

            // Mostrar aviso de acesso negado para operadores redirecionados
            if (filter_input(INPUT_GET, 'acesso_negado', FILTER_VALIDATE_BOOLEAN)):
            ?>
            <div class="main-content">
              <div class="container" style="padding-top: 40px;">
                <div class="alert alert-warning alert-has-icon">
                  <div class="alert-icon"><i class="fa fa-lock"></i></div>
                  <div class="alert-body">
                    <div class="alert-title">Acesso Negado</div>
                    Você não tem permissão para acessar esta área. Entre em contato com o Administrador para solicitar acesso.
                  </div>
                </div>
              </div>
            </div>
            <?php
            elseif($sheep_caminho_painel && file_exists($sheep_caminho_painel)):
                include_once($sheep_caminho_painel);
            else:
                echo "Erro ao acessar a página /{$ms}.php!";
                unset($_SESSION['sheep_user']);
                header('Location: '.HOME);
            endif;

            require_once('sheep_rodape.php')
            ?>




  
