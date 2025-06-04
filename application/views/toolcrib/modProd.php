<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Editar Producto</h2>
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
                        <form method="POST" action=<?= base_url('toolcrib/actualizarProducto') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Codigo <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="idp" class="form-control col-md-7 col-xs-12" name="idp" placeholder="" required="required" type="hidden" value=<?= $productos->idProducto ?>>
                                    <input style="text-transform: uppercase;" id="codigo" class="form-control col-md-7 col-xs-12" name="codigo" placeholder="" required="required" type="text" value=<?= $productos->codigo ?>>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Categoria <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="categoria" class="form-control col-md-7 col-xs-12" name="categoria" placeholder="" required="required" type="text" value=<?= $productos->categoria ?>>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Producto <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="producto" class="form-control col-md-7 col-xs-12" name="producto" placeholder="" required="required" type="text" value=<?= $productos->producto ?>>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Descripcion <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea style="text-transform: uppercase;" id="descripcion" class="form-control col-md-7 col-xs-12" name="descripcion" required="required" type="text" ><?= $productos->descripcion ?></textarea>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Proveedor <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="proveedor" value=<?= $productos->proveedor ?> class="form-control col-md-7 col-xs-12" name="proveedor" placeholder="" required="required" type="text">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Marca <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="marca" class="form-control col-md-7 col-xs-12" name="marca" placeholder="" required="required" type="text" value=<?= $productos->marca ?>>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Modelo <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="modelo" class="form-control col-md-7 col-xs-12" name="modelo" placeholder="" required="required" type="text" value=<?= $productos->modelo ?>>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Precio <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="precio" class="form-control col-md-7 col-xs-12" name="precio" placeholder="" required="required" type="text" value=<?= $productos->precio ?>>
                                </div>
                            </div>
                           
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Unidad de Medida</label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select required="required" class="select2_single form-control" name="um" id="um">
                                    <option value=<?= $productos->um ?>><?= $productos->um ?></option>    
                                    <option value="Piezas">Piezas</option>
                                    <option value="Kg">Kg</option>
                                    <option value="Paquete">Paquete</option>
                                    </select>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cantidad Minima <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="cantMin" class="form-control col-md-7 col-xs-12" name="cantMin" placeholder="" required="required" type="number" value=<?= $productos->cantMin ?>>
                                </div>
                            </div>
 
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cantidad Maxima <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input style="text-transform: uppercase;" id="cantMax" class="form-control col-md-7 col-xs-12" name="cantMax" placeholder="" required="required" type="number" value=<?= $productos->cantMax ?>>
                                </div>
                            </div>
                            
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Estatus</label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <?php if($productos->estatus!=0){?>
                                    <p>
                                        Activo
                                        <input type="checkbox" class="flat" name="estatus" value="1" checked />
                                    </p><?php
                                }else{?>
                                        <p>
                                        Activar
                                        <input type="checkbox" class="flat" name="estatus" value="1"/>
                                    </p>
                                    <?php
                                }?>
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Comentarios <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea style="text-transform: uppercase;" id="comentario" class="form-control col-md-7 col-xs-12" name="comentario" placeholder="" required="required"></textarea>
                                </div>
                            </div>

                            

                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <button id="send" type="submit" class="btn btn-success">Actualizar Producto</button>
                                </div>

                            </div>
                        </form>

                        <div class="x_content">
                                <h2>Comentarios</h2>
                            <div class="table-responsive">
                                <table id="tabla" class="table table-bordered">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">Tipo</th>
                                            <th class="column-title text-center">Usuario</th>
                                            
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

<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>



<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
 
</body>
</html>
