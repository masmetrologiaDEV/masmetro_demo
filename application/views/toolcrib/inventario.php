<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Catalogo de Productos</h2>
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                           <form method="POST" action=<?= base_url('toolcrib/excel') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                                <div class="col-md-12 col-sm-12 col-xs-12">
                                    <p style="display: inline;">
                                        Producto: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="prod" value="prod"  />
                                        Marca: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="marca" value="marca"/>
                                        Modelo: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="modelo" value="modelo"/>
                                        Proveedor: 
                                        <input type="radio" class="flat" name="rbBusqueda" id="prov" value="prov"/>
                                    
                                    </p>

                                    <input id="txtBuscar" style="display: inline;" type="text" name="txtBuscar">


                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>
                                    <a href=<?= base_url("toolcrib/producto_nuevo"); ?>><button id="send" type="button" class="btn btn-primary">Agregar Nuevo Producto</button></a>

                                </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="x_content">
                        <!--<button onclick="agregar()" type="button" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> Agregar Requisito </button>-->
                            <div class="table-responsive">
                                <table id="tabla" class="table table-bordered">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Codigo</th>
                                            <th class="column-title text-center">Producto</th>
                                            <th class="column-title text-center">Descripcion</th>
                                            <th class="column-title text-center">Proveedor</th>
                                            <th class="column-title text-center">Marca</th>
                                            <th class="column-title text-center">Modelo</th>
                                            
                                            <th class="column-title text-center">Precio</th>
                                            <th class="column-title text-center">Unidad de Medida</th>
                                            
                                            
                                            
                                            <th class="column-title text-center">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($productos) { $i = 1;
                                     foreach ($productos->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td  class="text-center"><?= $elem->codigo ?></td>
                                                <td  class="text-center"><?= $elem->producto ?></td>
                                                <td style="width: 500px;"><?= $elem->descripcion ?></td>
                                                <td  class="text-center"><?= $elem->proveedor ?></td>
                                                <td  class="text-center"><?= $elem->marca ?></td>
                                                <td  class="text-center"><?= $elem->modelo ?></td>
                                                
                                                <td  class="text-center"><?= "$ ".$elem->precio ?></td>
                                                <td  class="text-center"><?= $elem->um ?></td>
                                                
                                                
                                                
                                                
                                                <td class="text-center">
                                                <a href=<?= base_url("toolcrib/verProducto/".$elem->idProducto); ?>><button type="button"class="btn btn-success btn-xs"><i class="fa fa-eye"></i> Existencias </button></a>
                                                <a href=<?= base_url("toolcrib/modificarProd/".$elem->idProducto); ?>><button type="button"class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> Editar </button></a>
                                                
                                                </td>
                                            </tr>
                                    <?php $i++; }
                                      }
                                    ?>
                                    </tbody>

                                </table>

                            </div>


                                    <!--<a href=<?= base_url("toolcrib/excel"); ?>>--><button  type="submit" class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> Exportar </button>
                        </div></form> 
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
        }

    else {
        var URL = base_url + "toolcrib/getProds";
    $('#tabla tbody tr').remove();
    
    var parametro = $("input[name=rbBusqueda]:checked").val();
    var texto = $("#txtBuscar").val();
        


    $.ajax({
        type: "POST",
        url: URL,
        data: { texto : texto, parametro : parametro },
        success: function(result) {
            //alert(result);
            if(result)
            {
                var tab = $('#tabla tbody')[0];
                var rs = JSON.parse(result);
                $.each(rs, function(i, elem){
                    var ren = tab.insertRow(tab.rows.length);
                    ren.insertCell(0).innerHTML = elem.codigo;
                    ren.insertCell(1).innerHTML = elem.producto;
                    ren.insertCell(2).innerHTML = "<td style='width: 500px;'>"+elem.descripcion+"</td>";
                    ren.insertCell(3).innerHTML = elem.proveedor;
                    ren.insertCell(4).innerHTML = elem.marca;
                    ren.insertCell(5).innerHTML = elem.modelo;
                    ren.insertCell(6).innerHTML = "$ "+elem.precio;
                    ren.insertCell(7).innerHTML = elem.um;
                    ren.insertCell(8).innerHTML = "<a href="+base_url+"toolcrib/verProducto/"+elem.idProducto+"><button type='button' class='btn btn-success btn-xs'><i class='fa fa-eye'></i> Existencias </button></a><a href="+base_url+"toolcrib/modificarProd/"+elem.idProducto+"><button type='button' class='btn btn-warning btn-xs'><i class='fa fa-pencil'></i> Editar </button></a>";

                    

                    
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


function limpiar(){
    $('#NombreProducto').val();
    //$('#detalle').iCheck('uncheck');
    $('#btnModalAgregar').show();
    $('#btnModalEditar').hide();
}

function agregar(){
    limpiar();
    $('#modal').modal();
}



function prepareEdit(btn){
    $('#btnModalAgregar').hide();
    $('#btnModalEditar').show();
    var tab = $('#tabla')[0];
    var ren = btn.parentNode.parentNode.rowIndex;
    $('#idProducto').val(btn.value);
    $('#ren').val(ren);
    $('#NombreProducto').val(tab.rows[ren].cells[1].innerText);
    $('#Cantidad').val(tab.rows[ren].cells[2].innerText);
    
    $('#modal').modal();
}

function editar(){
    var idProducto =  $('#idProducto').val();
    var ren = $('#ren').val();
    var NombreProducto = $('#NombreProducto').val();
    var Cantidad = $('#Cantidad').val();




    $.ajax({
          type: "POST",
          url: '<?= base_url('toolcrib/editarProducto') ?>',
          data: { 'idProducto' : idProducto, 'NombreProducto' : NombreProducto, 'Cantidad' : Cantidad },
          success: function(result){
            if(result){

              var res = JSON.parse(result);
              var tab = $('#tabla')[0];
              tab.rows[ren].cells[1].innerText = res.NombreProducto;
              tab.rows[ren].cells[2].innerText = res.Cantidad;
              //tab.rows[ren].cells[3].innerText = res.Cantidad;

              new PNotify({ title: 'Requisito', text: 'Se ha editado Requisito con Éxito', type: 'success', styling: 'bootstrap3' });



            } else {
              new PNotify({ title: 'ERROR', text: 'Error al editar ', type: 'error', styling: 'bootstrap3' });
            }
          },
          error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error al editar Requisito', type: 'error', styling: 'bootstrap3' });
            console.log(data);
          },
        });
}

function eliminarRequisito(btn){
    var ren = btn.parentNode.parentNode.rowIndex;
    var idProducto =  btn.value;
    if(confirm('¿Desea eliminar requisito?')){
        $.ajax({
            type: "POST",
            url: '<?= base_url('toolcrib/eliminarProducto') ?>',
            data: { 'idProducto' : idProducto },
            success: function(result){
            if(result){
                var tab = $('#tabla')[0];
                tab.deleteRow(ren);

                new PNotify({ title: 'Requisito', text: 'Se ha eliminado Requisito con Éxito', type: 'success', styling: 'bootstrap3' });
            }else if(!result){
                var tab = $('#tabla')[0];
                //tab.deleteRow(ren);

                alert("Error al eliminar, aun hay producto en innvetario");

            } 
            else {
                new PNotify({ title: 'ERROR', text: 'Error al eliminar Requisito', type: 'error', styling: 'bootstrap3' });
            }
            },
            error: function(data){
            new PNotify({ title: 'ERROR', text: 'Error al eliminar Requisito', type: 'error', styling: 'bootstrap3' });
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
 <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('input[type=text]').forEach( node => node.addEventListener('keypress', e => {
        if(e.keyCode == 13) {
          e.preventDefault();
        }
      }))
    });
  </script>
</body>
</html>
