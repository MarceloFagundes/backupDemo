 <!-- INICIO DO MODAL SALVO COM SUCESSO .COM.BR - opacity: 0.5;-->
 <div class="modal fade" id="sucesso" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#12C06A; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2>Salvo com sucesso!</h2>
                      <img  src="assets/img/webtec_sucesso.gif" style="width:50%; height: auto;">
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>
        
        
        <div class="modal fade" id="versaoSheep" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#12C06A; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">SHEEP FRAMEWORK PHP <?= SHEEP_VERSAO ?></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2><?= SHEEP_VERSAO ?></h2>
                     <?= sheep ?>
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>
        
        
 <!-- INICIO DO MODAL ERRO COM SUCESSO .COM.BR - opacity: 0.5;-->
        <div class="modal fade" id="erro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#FF4957; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2>Ocorreu um erro!</h2>
                      <img  src="assets/img/webtec_erro_1.gif" style="width:50%; height: auto;">
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>
        
  
        
 <!-- INICIO DO MODAL ERRO TEM CONTEUDO .COM.BR - opacity: 0.5;-->
        <div class="modal fade" id="sheep_firewall" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#FF4957; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2>SHEEP FIREWALL - MULTIPLAS TENTATIVAS EM FORMULÁRIOS</h2>
                      <img  src="assets/img/webtec_erro_1.gif" style="width:50%; height: auto;">
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>

 <!-- INICIO DO MODAL ERRO COM SUCESSO .COM.BR - opacity: 0.5;-->
  <div class="modal fade" id="erroTemConteudo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#FF4957; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2>Já Existe Um Conteudo Relacionado a Este Cadastro!</h2>
                      <img  src="assets/img/webtec_erro_1.gif" style="width:50%; height: auto;">
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>
        
        
 <!-- INICIO DO MODAL ERRO COM SUCESSO .COM.BR - opacity: 0.5;-->
        <div class="modal fade" id="delete" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
          aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#FF4957; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel"></h5>
               
              </div>
                  <div class="modal-body" style="justify-content: center!important;">
                      <center>
                          <h2>Excluído com sucesso!</h2>
                      <img  src="assets/img/webtec_erro_1.gif" style="width:50%; height: auto;">
                      </center>
                      <button type="button" style="float:right;" class="btn btn-danger" data-dismiss="modal">x</button>
              </div>
              
            </div>
          </div>
        </div>

        <!-- INICIO DO MODAL PERMISSAO NEGADA -->
        <div class="modal fade" id="permissao_negada" tabindex="-1" role="dialog" aria-hidden="true" >
          <div class="modal-dialog" role="document" >
              <div class="modal-content" style="background:#FF8C00; color:#fff;">
              <div class="modal-header">
                  <h5 class="modal-title">Aviso de Demonstração</h5>
              </div>
                  <div class="modal-body" style="text-align: center; padding-bottom: 30px;">
                          <h3>Você não tem permissão para fazer isso.</h3>
                          <p style="font-size: 0.95rem; margin-top: 10px;">Por motivos de segurança, ações de exclusão estão desativadas no ambiente de demonstração.</p>
                      <button type="button" style="margin-top:20px;" class="btn btn-light" data-dismiss="modal">Entendi</button>
              </div>
            </div>
          </div>
        </div>
        <!-- FIM DO MODAL PERMISSAO NEGADA -->