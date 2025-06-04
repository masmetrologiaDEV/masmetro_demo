<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Reporte de Tickets Autos</h2>
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
                                <form method="POST" action=<?= base_url('tickets_AT/excel_AT') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data" onkeypress="return anular(event)">
                                <p style="display: inline;">
                                    Ticket:
                                    <input type="radio" class="flat" name="rbBusqueda" id="rbFolio" value="folio" checked />
                                    Usuario:
                                    <input type="radio" class="flat" name="rbBusqueda" id="rbUsuario" value="usuario" />
                                </p>

                                <input id="txtBusqueda" name="txtBusqueda" style="display: inline;" type="text">


                                <p style="display: inline; margin-right: 10px;">
                                    Estatus: 
                                </p>

                                <select onchange="buscar_AT()" style="display: inline; width: 12%; margin-right: 10px;" required="required" class="select2_single form-control" id="opEstatus" name="opEstatus">
                                    <option value="TODO" >TODO</option>
                                    <option value="ABIERTO" >ABIERTO</option>
                                    <option value="EN CURSO" >EN CURSO</option>
                                    <option value="DETENIDO" >DETENIDO</option>
                                    <option value="CANCELADO" >CANCELADO</option>                                 
                                    <option value="SOLUCIONADO" >SOLUCIONADO</option>                                  
                                    <option value="CERRADO" >CERRADO</option>                                   
                                </select>    
                                <input id="fecha1" style="display: inline;" type="date" name="fecha1">
                                <input id="fecha2" style="display: inline;" type="date" name="fecha2">
                                <button onclick="buscar_AT()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_content">
                    <div class="table-responsive">
                        <button type="submit" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> Exportar </button>
                        </form>
                        <label id="lblCount" class="pull-right"></label>
                        <table id="tabla" class="table table-striped">
                            <thead>
                                <tr class="headings">
                                    <th class="column-title">#</th>
                                    <th class="column-title">Fecha Creación</th>
                                    <th class="column-title">Usuario</th>
                                    <th class="column-title">Tipo</th>
                                    <th class="column-title">Titulo</th>
                                    <th class="column-title">Estatus</th>
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
<script src=<?= base_url("application/views/tickets/js/reporte.js"); ?>></script>
<script>

    $(function(){
        load();

    });
function load(){
        buscar_AT();
    }
    
</script>
</body>
</html>
