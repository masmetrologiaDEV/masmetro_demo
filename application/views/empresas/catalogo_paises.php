<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Paises</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>

                        <div class="x_content">
                            <div class="row">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <input id="txtBuscar" style="display: inline;" type="text">
                                    <p style="margin-left: 10px; display: inline;">
                                    Ver Inactivos:
                                    <input type="checkbox" class="flat cbTipo" id="activo" value="activo" />
                                    </p>
                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_content">
                            <div class="table-responsive">
                                <table id="tabla" class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">Pais</th>
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

  <div id="mdlEstados" class="modal fade bs-example-modal-md" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Estados</h4>
            </div>
            <div class="modal-body">
                <form>
                    <div>
                        <ul class="list-inline widget_tally">
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left;" id="estadoAsignado"></span>
                            </p>
                          </li>
                        </ul>
                      </div>
                <select id="estados"></select>
                <button type="button" data-dismiss="modal" class="btn btn-primary btn-sm" onclick="asignar()"><i class="fa fa-pencil"></i> Asignar</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-default btn-sm"><i class="fa fa-close"></i> Cerrar</button>
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
<!-- /footer content -->
</div>
</div>

<!-- jQuery -->
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>

<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- JS File -->
<script src=<?= base_url("application/views/empresas/js/catalogo_paises.js"); ?>></script>

<script>
    $(document).ready(function(){
        load();
    });
</script>
</body>
</html>
