<!-- page content -->
<div class="right_col" role="main">
   <div class="">
      <div class="clearfix"></div>
      <div class="row">
         <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
               <div class="x_title">
                  <h2>Orden de Trabajo</h2>
                  <button type='button' onclick="enviarSolicitud()" class='btn btn-success btn-md pull-right solicitud' id="btnEnviar"><i class='fa fa-send'></i> Crear WO</button>                     
                  <div class="clearfix"></div>
               </div>
               <div style="margin-top: 10px;" class="row">
                  <div class="modal-body">
                     <form>
                        <div class="col-md-8 col-sm-8 col-xs-12">
                           <label style="display: block;">Fecha Programada</label>
                           <input type="text" class="form-control pull-right" id="txtFechaAccion">
                        </div>
                     </form>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="">
         <div class="clearfix"></div>
         <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
               <div class="x_panel">
                  <div class="x_content">
                     <div class="row">
                        <div class="row">
                           <!-- C O N C E P T O S   Y   C O M E N T A R I O S -->
                           <div class="col-md-12 col-sm-12 col-xs-12" >
                              <div class="row" >
                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div style="border: 0;" class="x_panel">
                                       <div class="x_title">
                                          <h3 style="display: inline;">Items RS</h3>
                                          <button type='button' onclick="mdlRS()" class='btn btn-primary btn-xs pull-right solicitud'><i class='fa fa-plus'></i> Agregar</button>
                                          <div class="clearfix"></div>
                                       </div>
                                       <div class="x_content">
                                          <div class="row">
                                             <h4 class="pull-right" id="lblItemsCount"></h4>
                                             <table id="tblConceptosRS" class="data table table-striped no-margin">
                                                <thead>
                                                   <tr>
                                                      <th>#</th>
                                                      <th>RS</th>
                                                      <th>Item</th>
                                                      <th>Descripción</th>
                                                      <th>Opciones</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                             </table>
                                          </div>
                                       </div>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- /page content -->
<!-- footer content -->
<footer>
   <div class="pull-right">
      Equipo de Desarrollo | MAS Metrología
   </div>
   <div class="clearfix"></div>
</footer>
<!-- MODAL RS -->
<div id="mdlRS" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="modal-title">Ingrese RS</h4>
         </div>
         <div class="modal-body">
            <form>
               <h3 style="display: none;" id="lblRS"></h3>
               <div class="input-group">
                  <input id="txtBuscarRS" type="text" class="form-control" placeholder="Buscar RS...">
                  <span class="input-group-btn">
                  <button onclick="buscarRS()" class="btn btn-default" type="button">Buscar</button>
                  </span>
               </div>
               <div id="divSelectTodo" style="display: none"><input id="iptTodo" type="checkbox" class="flat"> Seleccionar Todo</div>
               <table class="data table table-striped no-margin">
                  <thead>
                     <tr>
                        <th>Selecc.</th>
                        <th>RS</th>
                        <th>Item</th>
                        <th>Descripción</th>
                     </tr>
                  </thead>
                  <tbody>
                  </tbody>
               </table>
            </form>
         </div>
         <div style="border: 0;" class="x_panel">
               <div class="x_title">
                  <h3 style="display: inline;">Notas</h3>
                  <div class="clearfix"></div>
               </div>
               <div class="x_content">
                  <p id="rsNotas"></p>
               </div>
            </div>
         <div class="modal-footer">
            <button data-dismiss="modal" style="display: inline;" type="button" class="btn btn-default pull-left"><i class="fa fa-close"></i> Cerrar</button>
            <button onclick="agregarRSItems()" type="button" class="btn btn-primary"><i class='fa fa-check'></i> Agregar</button>
         </div>
      </div>
   </div>
</div>
<!-- /footer content -->
</div>
</div>
<!-- jQuery -->
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- formatCurrency -->
<script src=<?= base_url("template/vendors/formatCurrency/jquery.formatCurrency-1.4.0.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<!-- bootstrap-wysiwyg -->
<script src=<?=base_url("template/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"); ?>></script>
<script src=<?=base_url("template/vendors/jquery.hotkeys/jquery.hotkeys.js"); ?>></script>
<script src=<?=base_url("template/vendors/google-code-prettify/src/prettify.js"); ?>></script>
<!-- jQuery Tags Input -->
<script src=<?= base_url("template/vendors/jquery.tagsinput/src/jquery.tagsinput.js") ?>></script>
<!-- jquery.redirect -->
<script src=<?= base_url("template/vendors/jquery.redirect/jquery.redirect.js"); ?>></script>
<!-- CUSTOM JS FILE -->
<script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>
<!-- JS FILE -->
<script src=<?= base_url("application/views/ordenes_trabajo/js/work_orders.js"); ?>></script>
<script src=<?= base_url("template/vendors/bootstrap-daterangepicker/daterangepicker.js") ?>></script>
<script>
   var ID = '<?= $this->session->id ?>';
   // var RES = '<?= $this->session->privilegios['responder_facturas'] ?>';
   var EDIT = '<?= isset($editar) ? '1' : '0' ?>';
   
   $(function(){
       load();
   });
   
   
</script>
</body>
</html>