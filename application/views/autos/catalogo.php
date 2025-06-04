<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2><?= $titulo ?></h2>
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
                                    <p style="display: inline;">
                                        Placas: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="placas" value="placas"  />
                                        Serie: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="serie" value="serie" />
                                    </p>

                                    <input id="txtBuscar" style="display: inline;" type="text">

                                    <p style="margin-left: 10px; display: inline;">
                                        Autos inactivos:
                                        <input type="checkbox" onclick="buscar()" class="flat" id="cbActivo" value="1"  />
                                    </p>

                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>

                                </div>

                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped" id="tblAutos">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">No.</th>
                                            <th class="column-title">Foto</th>
                                            <th class="column-title">Automóvil</th>
                                            <th class="column-title">Serie</th>
                                            <th class="column-title">Placas</th>
                                            <th class="column-title">Responsable</th>
                                            <th class="column-title">Ultima Revisión</th>
                                            <th class="column-title">Editar</th>
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
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- jquery.redirect -->
<script src=<?= base_url("template/vendors/jquery.redirect/jquery.redirect.js"); ?>></script>

<script src=<?= base_url("application/views/autos/js/catalogo.js"); ?>></script>
<script>
  $(document).ready(function(){
        load();
    });
</script>

</body>
</html>
