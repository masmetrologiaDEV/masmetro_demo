<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>


        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Texto de correo</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>

                    <div class="x_content">
                    <div class="table-responsive">
                        <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-xs"><i class='fa fa-plus'></i> Agregar</button>
                        <table id="tabla" class="table table-striped">
                            <thead>
                                <tr class="headings">
                                    <th style="width: 7%;" class="column-title">#</th>
                                    <th class="column-title">Texto</th>
                                    <th class="column-title">Fecha</th>
                                    <th class="column-title">Usuario</th>
                                    <th class="column-title">Activo</th>
                                    <th class="column-title">Opciones</th>
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
<!-- /page content -->



<div id="mdl" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="mdlTitulo">Texto Correo</h4>
            </div>
            <div class="modal-body">
            <form>
            <div class="row">
                  <label class="col-md-4 col-sm-4 col-xs-12" for="txtTexto">Comentarios</label>
                  <div class="col-md-6 col-sm-6 col-xs-12">
                      <textarea style="width: 324px; height: 198px;" id="txtTexto" name="txtTexto"></textarea>
                  </div>
              </div>
<br>
                <div class="row">
                    
                       
                            <label class="col-md-4 col-sm-4 col-xs-12" for="cbActivo">Activo</label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="checkbox" class="flat cbTipo" id="cbActivo" value="1" name="cbActivo" checked/>
                        </div>
                    
                </div>
            </form>
            </div>
            <div class="modal-footer">
                <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default pull-left"><i class="fa fa-close"></i> Cancelar</button>
                <button id="btnEditar" type="button" onclick="modificar(this)" class="btn btn-warning"><i class='fa fa-pencil'></i> Editar</button>
                <button id="btnAgregar" type="button" onclick="agregar()" class="btn btn-primary"><i class='fa fa-plus'></i> Agregar</button>
            </div>
            

        </div>
    </div>
</div>



<!-- footer content -->
<footer>
    <div class="pull-right">
        Equipo de Desarrollo | MAS Metrología
    </div>
    <div class="clearfix"></div>
</footer>
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
<!-- JS FILE -->
<script src=<?= base_url("application/views/configuracion/servicios/js/correo.js"); ?>></script>
<script>

    $(function(){
        load();
    });
    
</script>
</body>
</html>
