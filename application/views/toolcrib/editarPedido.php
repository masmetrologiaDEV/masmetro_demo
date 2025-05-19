<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="row">
            <div class="col-md-12 col-sm-12 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Pedido</h2>
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
                        <form method="POST" action=<?= base_url('toolcrib/ajuesteInventario') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                            <h3>Ajuste de Inventario</h3>

                            
                           

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Producto <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input  name="idV" value=<?= $productos->idVenta?> required="required" type="hidden">
                                    <input  name="idDV" value=<?= $productos->idDVTC?> required="required" type="hidden">
                                    <input  name="idProd" value=<?= $productos->idProd?> required="required" type="hidden">
                                    <input  style="text-transform: uppercase;" id="producto" class="form-control col-md-7 col-xs-12" name="producto" value=<?= $productos->producto?> required="required" type="text" readonly>
                                </div>
                            </div>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Ubicacion <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select required="required" class="select2_single form-control" name="ubicacion">
                                        <?php foreach ($ubi as $elem) { ?>
                                            <option value=<?= $elem->idUbi ?>><?= $elem->ubicacion." - ". $elem->cantidad ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Cantidad <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input  style="text-transform: uppercase;" id="cantidad" class="form-control col-md-7 col-xs-12" name="cantidad" required="required" type="number" value=<?= $productos->cantidad?> >
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
                                    <button id="send" type="submit" class="btn btn-success "> Entrega</button><br><br>
                                </div>
                            </div>
                                  <div class="ln_solid"></div>
                            

                        </form>
                        


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
