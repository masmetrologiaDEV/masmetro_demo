<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Camaras de Seguridad</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
<button onclick="agregar()" style="display: inline;" class="btn btn-primary btn-xs" type="button"><i class="fa fa-plus"></i> Agregar</button>
                    <div class="x_content">

                        <div class="table-responsive">
                            <table class="table table-striped" id="camaras">
                                <thead>
                                    <tr class="headings">
                                        <th class="column-title">ID</th>
                                        <th class="column-title">Grabadora</th>
                                        <th class="column-title">Ubicacion</th>
                                        <th class="column-title">Marca</th>
                                        <th class="column-title">Modelo</th>
                                        <th class="column-title">Serie</th>
                                        <th class="column-title">Codigo</th>
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


        <div id="mdlAgregar" class="modal fade bs-example-modal-md" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-md">
            <div class="modal-content">

              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Agregar Camara</h4>
              </div>
              <div class="modal-body">
                <form >
                    <label>Grabadora</Label>
                    <input id="grabadora"name="grabadora" class="form-control" type="text">
                    <label style="margin-top:5px;">Ubicacion</Label>
                    <input id="ubicacion"name="ubicacion" class="form-control" type="text">
                    <label style="margin-top:5px;">Marca</Label>
                    <input id="marca"name="marca" class="form-control" type="text">
                    <label style="margin-top:5px;">Modelo</Label>
                    <input id="modelo"name="modelo" class="form-control" type="text">
                    <label style="margin-top:5px;">Serie</Label>
                    <input id="serie"name="serie" class="form-control" type="text">
                    <label style="margin-top:5px;">Codigo</Label>
                    <input id="codigo" name="codigo" class="form-control" type="text">
                </form>
              </div>
              <div class="modal-footer">
                <button type="button"class="btn btn-default pull-left" data-dismiss="modal">Cancelar</button>
                <button id="btnAgregarPlanta" type="button" onclick="agregarCamara()" class="btn btn-primary"><i class="fa fa-plus"></i> Agregar</button>
                <!--<button id="btnEditarPlanta" type="button" onclick="editarPlanta()" class="btn btn-warning"><i class="fa fa-pencil"></i> Editar</button>-->
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
<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js") ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js") ?>></script>
<!-- FastClick -->
<script src=<?= base_url("template/vendors/fastclick/lib/fastclick.js") ?>></script>
<!-- NProgress -->
<script src=<?= base_url("template/vendors/nprogress/nprogress.js") ?>></script>
<!-- bootstrap-progressbar -->
<script src=<?= base_url("template/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js") ?>></script>

<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<script src=<?= base_url("application/views/tickets/js/camara.js"); ?>></script>

<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<script type="text/javascript">
     $(document).ready(function(){
        load();
    });
</script>
</body>
</html>
