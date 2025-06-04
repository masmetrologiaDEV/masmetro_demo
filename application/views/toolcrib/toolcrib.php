<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Realizar Pedido</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                        <form method="POST" action=<?= base_url('toolcrib/registrarVenta') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                          <div class="col-md-12 col-sm-12 col-xs-12">
                                    <p style="display: inline;">
                                        
                                        Código :
                                        <input type="radio" class="flat" name="rbBusqueda" id="codigo" value="codigo" checked />
                                        Producto : 
                                        <input type="radio" class="flat" name="rbBusqueda" id="prod" value="prod"/>
                                    
                                    </p>


                                    <input id="txtBuscar" style="display: inline;" type="text" name="txtBuscar">


                                    <button id="btnBuscar" onclick="buscar()" style="display: inline;" class="btn btn-success" type="button"><i class="fa fa-search"></i> Buscar</button>

                                </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="x_content">
                        <!--<button onclick="agregar()" type="button" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> Agregar Requisito </button>-->
                            <div class="table-responsive">
                                <table id="tabla" class="table table-bordered" >
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Codigo</th>
                                            <th class="column-title text-center">Producto</th>
                                            <th class="column-title text-center">Descripcion</th>
                                            
                                            <th class="column-title text-center">Marca</th>
                                            <th class="column-title text-center">Modelo</th>
                                            <th class="column-title text-center">Stock</th>
                                            <th class="column-title text-center">Precio</th>
                                            
                                            
                                            
                                            
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
                                                <td  class="text-center"><?= $elem->marca ?></td>
                                                <td  class="text-center"><?= $elem->modelo ?></td>
                                                <td  class="text-center"><?= $elem->cantidad ?></td>
                                                
                                                <td  class="text-center"><?= "$ ".$elem->precio ?></td>
                                                
                                                
                                                
                                                
                                                <td class="text-center">
                                                    <form method="POST" action=<?= base_url('toolcrib/registrarVenta') ?>>
                                                        <input id='cantidad'  type='number' name='cantidad' min='0' max='10' class='border' required><input id='producto' style='display: inline;' type='hidden' name='producto' value="<?= $elem->idProducto ?>">
                                                        <button type='submit'class='btn btn-warning btn-xs'><i class='fa fa-pencil'></i> Agregar </button>
                                                    </form>
                                               
                                                
                                                </td>
                                            </tr>
                                    <?php $i++; }
                                      }
                                    ?>
                                   
                                    </tbody>

                                </table>

                            </div>

                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                   <!-- <button id="send" type="submit" class="btn btn-success">Agregar producto</button>-->
                                </div>

                            </div>
                        </form>
                        
                        

                        
                            
                              
                            
                        






                    </div>

                    <div class="x_content">
                        <!--<button onclick="agregar()" type="button" class="btn btn-primary btn-xs"><i class="fa fa-plus"></i> Agregar Requisito </button>-->
                            <div class="table-responsive">
                                  <?php

                                    if($venta) { 
                                    //echo var_dump($venta->result());die();

                                        ?>
                                <table  class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Codigo</th>
                                            <th class="column-title text-center">Producto</th>
                                            <th class="column-title text-center">Cantidad</th>
                                            <th class="column-title text-center">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php

                                    
                                     foreach ($venta->result() as $elem) { 


                                        ?>

                                            <tr class="even pointer">
                                                
                                                <td class="text-center"><?= $elem->codigo ?></td>
                                                <td class="text-center"><?= $elem->producto ?></td>
                                                <td class="text-center"><?= $elem->cantidad ?></td>
                                                <td class="text-center">
                                                <a href=<?= base_url("toolcrib/cancelarProducto/".$elem->idvt); ?>><button type="button"class="btn btn-danger btn-xs"><i class="fa fa-eye"></i> Eliminar </button></a>                                                
                                                </td>
                                               
                                            </tr>
                                    <?php }
                                      
                                    ?>
                                    </tbody>

                                </table><form method="POST" action=<?= base_url('toolcrib/registrarPedido') ?>>
                                
                               
                                 <?php }
                                      
                                    ?>
                                    <button id="send" type="submit" class="btn btn-primary">Realizar pedido</button>
                                    

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
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js") ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js") ?>></script>
<!-- FastClick -->
<script src=<?= base_url("template/vendors/fastclick/lib/fastclick.js") ?>></script>
<!-- NProgress -->
<script src=<?= base_url("template/vendors/nprogress/nprogress.js") ?>></script>
<!-- validator -->
<script src=<?= base_url("template/vendors/validator/validator.js") ?>></script>
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>


<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>

<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>



<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
  <script>

/*    $(function(){
      load();
    });*/


        </script>



        <script>

function buscar(){
    var URL = base_url + "toolcrib/productos";
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
                    ren.insertCell(2).innerHTML = elem.descripcion;
                    ren.insertCell(3).innerHTML = elem.marca;
                    ren.insertCell(4).innerHTML = elem.modelo;
                    ren.insertCell(5).innerHTML = elem.cantidad;
                    ren.insertCell(6).innerHTML = "$ "+elem.precio;
                    ren.insertCell(7).innerHTML = "<form method='POST' action="+base_url+"toolcrib/registrarVenta><input id='cantidad'  type='number' name='cantidad' min='0' max='10' class='border' required><input id='producto' style='display: inline;' type='hidden' name='producto' value="+elem.idProducto+"><button type='submit'class='btn btn-warning btn-xs'><i class='fa fa-pencil'></i> Agregar </button></form>";

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

</script>
<script type="text/javascript">
    function compra()
{
   var cantidad = $("#cantidad").val();
   var producto = $("#producto").val();
   alert(cantidad);

    $.ajax({
        type: "POST",
        url: '<?= base_url('toolcrib/registrarVenta') ?>',
        data: { cantidad : cantidad, producto : producto},
        error: function(data){
          alert("Errorri");
          console.log(data);
        },
      });
    location.reload();
  
}
</script>
</body>
</html>
