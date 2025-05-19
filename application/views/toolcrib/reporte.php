<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Reporte</h2>                            
                            <ul class="nav navbar-right panel_toolbox">

                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>

                                </li>

                            </ul><form method="POST" action=<?= base_url('toolcrib/excelRep') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                    <p style="display: inline;">
                                        Fechas: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="date" value="date"/> 

                                        <input id="fecha1" style="display: inline;" type="date" name="fecha1">
                                        <input id="fecha2" style="display: inline;" type="date" name="fecha2">   
                                        Producto: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="prod" value="prod"/>
                                        Marca: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="marca" value="marca"/>
                                        Proveedor: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="prov" value="prov"/>
                                        Usuario: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="user" value="user"/>                                    

                                    </p>

                                    <input id="txtBuscar" style="display: inline;" type="text" name="txtBuscar">



                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>
                                    <button type="submit" class="btn btn-warning"><i class="fa fa-pencil"></i> Exportar </button>

                                </div></form>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">

                        <!--<button onclick="agregar()" type="button" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> Agregar Requisito </button>-->
                            <div class="table-responsive">


                                <table id="tabla" class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">Pedido</th>
                                            <th class="column-title">Proveedor</th>
                                            <th class="column-title">Marca</th>
                                            <th class="column-title">Producto</th>
                                            <th class="column-title">Cantidad</th>
                                            <th class="column-title">Precio unitario</th>
                                            <th class="column-title">No. Empleado</th>
                                            <th class="column-title">Usuario</th>
                                            <th class="column-title">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($pedidos) { $i = 1;
                                     foreach ($pedidos->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td><?= $elem->idVenta ?></td>
                                                <td><?= $elem->proveedor ?></td>
                                                <td><?= $elem->marca ?></td>
                                                <td><?= $elem->producto ?></td>
                                                <td><?= $elem->cantidad ?></td>
                                                <td><?= "$ ".$elem->precio ?></td>
                                                <td><?= $elem->no_empleado ?></td>
                                                <td><?= $elem->nombre ?></td>
                                                <td><?= $elem->fecha ?></td>
                                                
                                            </tr>
                                    <?php $i++; }
                                      }
                                    ?>
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


<div id="modal" class="modal fade" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title">Editar Producto</h4>
        </div>
        <form id="formContacto" class="form-horizontal form-label-left">
        <div class="modal-body">
        <input type="hidden" id="id" name="id"/>
        <input type="hidden" id="ren" name="ren"/>
        <div class="item form-group">
        <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Producto</label>
        <div class="col-md-6 col-sm-6 col-xs-12">
            <input maxlength="150" style="text-transform: uppercase;" id="NombreProducto" class="form-control col-md-7 col-xs-12" name="NombreProducto" placeholder="" required="required" type="text">
        </div>
        </div>
        
        <div class="item form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="tipo">Cantidad</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input maxlength="150" style="text-transform: uppercase;" id="Cantidad" class="form-control col-md-7 col-xs-12" name="Cantidad" placeholder="" required="required" type="number">                
            </div>
        </div>

        <br>
       


        </div>
        <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
        <button id="btnModalAgregar" onclick="registrar()" type="button" class="btn btn-primary" data-dismiss="modal">Agregar</button>
        <button id="btnModalEditar" onclick="editar()" type="button" class="btn btn-warning" data-dismiss="modal">Editar</button>
        </div>
        </form>
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
<!-- icheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<script>
    function buscar(){
        if (!$('input[name="rbBusqueda"]').is(':checked')) {
        alert('Seleccione campo a buscar');
        }else{
    var URL = base_url + "toolcrib/getRep";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBuscar").val();
    var fecha1 = $("#fecha1").val();
    var fecha2 = $("#fecha2").val();
    alert(fecha1+fecha2);


    $.ajax({
        type: "POST",
        url: URL,
        data: { texto : texto, parametro : parametro, fecha1 : fecha1, fecha2 : fecha2 },
        success: function(result) {
            //alert(result);
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = elem.idVenta;
                    ren.insertCell(1).innerHTML = elem.proveedor;
                    ren.insertCell(2).innerHTML = elem.marca;
                    ren.insertCell(3).innerHTML = elem.producto;
                    ren.insertCell(4).innerHTML = elem.cantidad;
                    ren.insertCell(5).innerHTML = "$ "+elem.precio;
                    ren.insertCell(6).innerHTML = elem.no_empleado;
                    ren.insertCell(7).innerHTML = elem.nombre;
                    ren.insertCell(8).innerHTML = elem.fecha;
                    

                    

                    
                });
            }
            else
            {
                new PNotify({ title: '¡Nada por aquí!', text: 'No se encontraron resultados', type: 'info', styling: 'bootstrap3' });
            }
          },
        error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
        },
      });
    }
}






<?php
if (isset($this->session->errores)) {
    foreach ($this->session->errores as $error) {
        echo "new PNotify({ title: '" . $error['titulo'] . "', text: '" . $error['detalle'] . "', type: 'error', styling: 'bootstrap3' });";
    }
    $this->session->unset_userdata('errores');
}
if (isset($this->session->aciertos)) {
    foreach ($this->session->aciertos as $acierto) {
        echo "new PNotify({ title: '" . $acierto['titulo'] . "', text: '" . $acierto['detalle'] . "', type: 'success', styling: 'bootstrap3' });";
    }
    $this->session->unset_userdata('aciertos');
}
?>
</script>
</body>
</html>
