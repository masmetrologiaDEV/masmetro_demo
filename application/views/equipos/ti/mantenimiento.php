<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">

           <div class="col-md-3 col-sm-3 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
                        <h2>Equipo</h2>
                        <ul class="nav navbar-right panel_toolbox">
                            <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                            </li>
                            <li><a class="close-link"><i class="fa fa-close"></i></a>
                            </li>
                        </ul>
                        <div class="clearfix"></div>
                    </div>
                    <div class="x_content">
                         <div style="text-align: center; overflow: hidden; margin: 10px;">
                        <span style="width: 50%; float: left; text-align: left;font-weight: bold;" >Asignado a:</span>
                        <span style="width: 50%; float: left; text-align: left;"><?= $equipo->nombre ?></span>
                        
                              
                      </div>

                      <div style="text-align: center; overflow: hidden; margin: 10px;">
                        <img width="100%" src="<?= base_url("data/equipos/ti/fotos/".$equipo->foto);?>">
                      </div>
                     
<?php
$array = json_decode( $equipo->campos );
foreach ( $array as $titulo => $col ) {?>
                      <div>
                        <ul class="list-inline widget_tally">
                          <li>
                            <p>
                              <span style="width: 40%; float: left; text-align: left;font-weight: bold;" ><?= $titulo ?></span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $col ?></span>
                            </p>
                          </li>
                          
                      </div>
 <?php
                }?>
                    </div>
                   
                </div>
                
            </div>

            <div class="col-md-9 col-sm-9 col-xs-12">
              <div class="x_panel">
                  <div class="x_title">
                      <h2>Mantenimiento</h2>
                      <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                      </ul>
                      <div class="clearfix"></div>
                      <button id="send" type="submit" class="btn btn-success"><a href=<?= base_url("equipos/revisiones"); ?>>Volver</a></button>
                  </div>
                  <div class="x_content">
                  <form method="POST" action=<?= base_url('equipos/registrarMantto') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                    <input type="hidden" name="equipo" value="<?= $equipo->id ?>">
                        <center>
                             <?php if($equipo->tipo=="Celular") {
                                ?>
                          <div class="item form-group">
                                
                                  <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label style="min-width: 25%;">Case</label>
                                        <input type="checkbox" name="case" value="1"/>
                                        <label style="min-width: 25%;">Vidrio Templado</label>
                                        <input type="checkbox" name="vidTem" value="1" />
                                      
                                    </div>
                              
                                
                              </div>
                        </center>
                        <center>
                             

                              <div class="item form-group">
                                
                                  <div class="col-md-6 col-sm-6 col-xs-12">
                                      
                                        <label style="min-width: 25%;">Cargador USB</label>
                                        <input type="checkbox" name="usb" value="1"/>
                                        <label style="min-width: 25%;">Manos Libres</label>
                                        <input type="checkbox" name="ml" value="1" />

                                      
                                      <input style="display: none; margin-bottom: 10px;" id="com_liqPaDel" class="form-control col-md-7 col-xs-12" name="com_liqPaDel" placeholder="Comentarios" type="text">
                                  </div>
                                
                              </div>


                              <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Condicion de Bateria <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input id="name" class="col-xs-5" name="bateria" placeholder="" required="required" type="number">
                                </div>

                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Consumo de Datos <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input id="name" class="col-xs-5" name="datos" placeholder="" required="required" type="number">
                                </div>
                                
                            </div>
<?php
}elseif ($equipo->tipo=="Laptop" || $equipo->tipo=="Desktop") {
  // code...
?><div class="item form-group">
                                
                                  <div class="col-md-6 col-sm-6 col-xs-12">
                                        <label style="min-width: 25%;">Disco</label>
                                        <input type="checkbox" name="disco" value="1"/>
                                        <label style="min-width: 25%;">CPU</label>
                                        <input type="checkbox" name="cpu" value="1" />
                                      
                                    </div>
                              
                                
                              </div>
                        </center>
                        <center>
                             

                              <div class="item form-group">
                                
                                  <div class="col-md-6 col-sm-6 col-xs-12">
                                      
                                        <label style="min-width: 25%;">Teclado/Mouse</label>
                                        <input type="checkbox" name="tecMouse" value="1"/>
                                        <label style="min-width: 25%;">Abanicos</label>
                                        <input type="checkbox" name="abanicos" value="1" />

                                      
                                      <input style="display: none; margin-bottom: 10px;" id="com_liqPaDel" class="form-control col-md-7 col-xs-12" name="com_liqPaDel" placeholder="Comentarios" type="text">
                                  </div>
                                
                              </div>
                            <?php }?>
			    <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Fotos <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input id="name" class="col-xs-5" name="foto[]"  type="file" accept="image/png, image/jpeg" multiple>
                                </div>
                                
                            </div>

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Comentarios <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <textarea style="text-transform: uppercase;" id="comentario" class="col-xs-5" name="comentarios" placeholder="" required="required"></textarea>
                                </div>
                            </div>



                              </center>




                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <button id="send" type="submit" class="btn btn-success">Registrar Mantenimiento</button>
                                </div>

                            </div>
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
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- FancyBOX -->
<script src=<?= base_url("template/vendors/fancybox/dist/jquery.fancybox.min.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
</body>
</html>
