<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>


        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Configuracion de Notificaciones</h2>
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
                        <table class="table table-striped">
                            <thead>
                                <tr class="headings">
                                    <th style="width: 5%;" class="column-title">Seleccion</th>
                                    <th style="width: 45%;" class="column-title">Tipo de Notificacion</th>                                    
                                </tr>
                                
                            </thead>
                            <tbody>
                                <form method="POST" action=<?= base_url('configuracion/guardarNot') ?>>
                                <tr>
                                    <td><input type="checkbox" name="qr" value="1"  <?= $noti->qr == '1' ? 'checked' : '' ?>></td>
                                    <td>Qr'S</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="tickets" value="1" <?= $noti->tickets == '1' ? 'checked' : '' ?>></td>
                                    <td>Tickets Pendientes</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="pr" value="1" <?= $noti->pr == '1' ? 'checked' : '' ?>></td>
                                    <td>PR'S</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="po" value="1" <?= $noti->po == '1' ? 'checked' : '' ?>></td>
                                    <td>PO'S</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="cot" value="1" <?= $noti->cotizaciones == '1' ? 'checked' : '' ?>></td>
                                    <td>Cotizaciones</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="fact" value="1" <?= $noti->facturas == '1' ? 'checked' : '' ?>></td>
                                    <td>Facturas</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="agenda" value="1" <?= $noti->agenda == '1' ? 'checked' : '' ?>></td>
                                    <td>Agenda</td>                                    
                                </tr>
                                <tr>
                                    <td><input type="checkbox" name="tool" value="1" <?= $noti->tool == '1' ? 'checked' : '' ?>></td>
                                    <td>Tool Crib</td>                                    
                                </tr>

                                
                            </tbody>
                            
                        </table>
                        <button type='submit'class='btn btn-warning btn-xs'><i class='fa fa-pencil'></i> Guardar </button>
                        </form>
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
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<!-- bootstrap-daterangepicker -->
<script src=<?= base_url("template/vendors/moment/min/moment.min.js") ?>></script>
<script src=<?= base_url("template/vendors/bootstrap-daterangepicker/daterangepicker.js") ?>></script>
<!-- CUSTOM JS FILE -->
<script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>
<!-- JS FILE -->
<script src=<?= base_url("application/views/configuracion/compras/js/pagos.js"); ?>></script>
<script>
    var priv_gestion = '<?= $this->session->privilegios['gestionar_recursos'] ?>';

    $(function(){
        load();
    });
    
</script>
</body>
</html>
