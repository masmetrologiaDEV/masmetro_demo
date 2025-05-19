<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Alta Gps</h2>

                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                                <button id="send" type="submit" class="btn btn-success btn-xs"><a href=<?= base_url("autos/gps"); ?>>Volver</a></button>
                        </div>



                        <div class="x_content" >

<div class="col-md-3 col-sm-3 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Automóvil</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i></i></a>
                            </li>
                            <li><a class="close-link"><i></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">

                      <div style="text-align: center; overflow: hidden; margin: 10px;">
                        <img width="100%" src="<?= 'data:image/bmp;base64,' . base64_encode($auto_foto); ?>">
                      </div>

                      <div>
                        <ul class="list-inline widget_tally">
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Marca:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto_marca ?></span>
                            </p>
                          </li>
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Combustible:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto_combustible ?></span>
                            </p>
                          </li>
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Placas:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto_placas ?></span>
                            </p>
                          </li>
                        </ul>
                      </div>

                    </div>
                </div>
            </div>



                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title text-center">GPS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                      <td>
                                      <form method="POST" action=<?= base_url('autos/registrarGPS') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                                        <input style="text-transform: uppercase;" value="<?= $auto ?>" class="form-control col-md-7 col-xs-12" name="idAuto" placeholder="" required="required" type="hidden">

                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Marca <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12">

                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12" name="marca" required="required" type="text" value="<?=$gps ? $gps->marca:""?>" />
                                          </div>
                                      </div>

                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">IMEI <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12">
                                              <input style="text-transform: uppercase;" id="name" class="form-control col-md-7 col-xs-12" name="imei" placeholder="" required="required" type="text" value="<?= $gps ? $gps->imei:''?>">
                                          </div>
                                      </div>

                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Modelo <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12">
                                              <input style="text-transform: uppercase;" id="name" class="form-control col-md-7 col-xs-12" name="modelo" placeholder="" required="required" type="text" value="<?= $gps ?$gps->modelo:''?>" >
                                          </div>
                                      </div>

                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">CHIP <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12">
                                              <input style="text-transform: uppercase;" id="name" class="form-control col-md-7 col-xs-12" name="chip" placeholder="" required="required" type="text" value="<?= $gps ?$gps->chip:''?>" >
                                          </div>
                                      </div>

                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Numero de CHIP <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12">
                                              <input style="text-transform: uppercase;" id="name" class="form-control col-md-7 col-xs-12" name="nchip" placeholder="" required="required" type="text" value="<?= $gps ?$gps->telefono:''?>" >
                                          </div>
                                      </div>
                                      
                                      <div class="form-group">
                                          <div class="col-md-6 col-md-offset-3">
                                              <button id="send" type="submit" class="btn btn-success btn-sm">Registrar</button>
                                          </div>
                                      </div>
                                        
                                      </form>
                                      </td>
                                    </tr>
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


</body>
</html>
