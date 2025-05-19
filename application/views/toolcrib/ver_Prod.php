<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Inventario</h2>
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
                        <form method="POST" action=<?= base_url('toolcrib/editarProducto') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                            <h3>Agregar Nueva Ubicacion</h3>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Codigo <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                     <label class="form-control col-md-7 col-xs-12"><?= $productos->codigo?></label>

                                     <input type="hidden" name="idProducto" value=<?= $productos->idProducto ?>>
                                     

                                </div>
                            </div>
                             <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Stock <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                     <label class="form-control col-md-7 col-xs-12"><?= $qty->cantidad?></label>
                                </div>
                            </div>
                           

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Producto <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input  style="text-transform: uppercase;" id="producto" class="form-control col-md-7 col-xs-12" name="NombreProducto" value=<?= $productos->producto?> required="required" type="text">
                                </div>
                            </div>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Localizacion <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                     <input  style="text-transform: uppercase;" id="ubicacion" class="form-control col-md-7 col-xs-12" name="ubicacion" required="required">

                                </div>
                            </div>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cantidad <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input  style="text-transform: uppercase;" id="cantidad" class="form-control col-md-7 col-xs-12" name="cantidad" required="required" type="number">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Comentarios <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea style="text-transform: uppercase;" id="comentario" class="form-control col-md-7 col-xs-12" name="comentario" placeholder="" required="required"></textarea>
                                </div>
                            </div>
                            

                      
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <button id="send" type="submit" class="btn btn-success "> Agregar</button><br><br>
                                </div>
                            </div>
                                  <div class="ln_solid"></div>
                                     </form>

                             <div class="table-responsive">
                                <table id="tabla" class="table table-bordered">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Codigo</th>
                                            <th class="column-title text-center">Producto</th>
                                            <th class="column-title text-center">Local</th>
                                            <th class="column-title text-center">Cantidad</th>
                                           
                                            <th class="column-title text-center">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($ubicacion) { $i = 1;
                                     foreach ($ubicacion->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td class="text-center"><?= $elem->codigo ?></td>
                                                <td class="text-center"><?= $elem->producto ?></td>
                                                <td class="text-center"><?= $elem->ubicacion ?></td>
                                                <td class="text-center"><?= $elem->cantidad ?></td>
                                                <td class="text-center">
                                                <a href=<?= base_url("toolcrib/editarProd/".$elem->idUbi); ?>><button type="button"class="btn btn-warning btn-xs"><i class="fa fa-pencil"></i> Ver Producto </button></a>
                                                </td>
                                            </tr>
                                    <?php $i++; }
                                      }
                                    ?>
                                    </tbody>

                                </table>

                            </div>
                             <div class="x_content">
                                <h2>Movimientos</h2>
                            <div class="table-responsive">
                                <table id="tabla" class="table table-bordered">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Tipo</th>
                                            <th class="column-title text-center">Usuario</th>
                                            <th class="column-title text-center">Producto</th>
                                            <th class="column-title text-center">Local</th>
                                            <th class="column-title text-center">Cantidad</th>
                                            <th class="column-title text-center">Comentarios</th>
                                            <th class="column-title text-center">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($movimientos) { $i = 1;
                                     foreach ($movimientos->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td  class="text-center"><?= $elem->tipo ?></td>
                                                <td  class="text-center"><?= $elem->nombre ?></td>
                                                <td class="text-center"><?= $elem->producto ?></td>
                                                <td  class="text-center"><?= $elem->local ?></td>
                                                <td  class="text-center"><?= $elem->cantidad ?></td>
                                                <td  class="text-center"><?= $elem->comentario ?></td>
                                                <td  class="text-center"><?= $elem->fecha ?></td>
                                                
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

<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("application/views/autos/js/alta_autos.js"); ?>></script>

<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>



<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
  <script>

    $(function(){
      load();
    });


        </script>
</body>
</html>
