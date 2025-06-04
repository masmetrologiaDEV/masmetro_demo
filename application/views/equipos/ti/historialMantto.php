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
                        <span style=" text-align: left;font-weight: bold;" >Asignado a: <?= $equipo->nombre ?></span>
                        
                        
                              
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

            <div class="col-md-3 col-sm-3 col-xs-12">
              <div class="x_panel">
                  <div class="x_title">
                      <h2>Historial de Mantenimiento</h2>
                      <ul class="nav navbar-right panel_toolbox">
                          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                          </li>
                          <li><a class="close-link"><i class="fa fa-close"></i></a>
                          </li>
                      </ul>
                      <div class="clearfix"></div>
                      <button id="send" type="submit" class="btn btn-success"><a href=<?= base_url("equipos/ti"); ?>>Volver</a></button>
                  </div>
                  <div class="x_content">
                     <div class="table-responsive">
                                <table id="tabla" class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            <th class="column-title">Fecha de Mantenimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($manto) {
                                     foreach ($manto->result() as $elem) { ?>
                                        <tr>
                                            <td><a href=<?=base_url('equipos/hallazgos/'.$elem->idME);?> class="btn btn-warning"><?=$elem->fecha?></a></td>
                                            </tr>
                                    <?php  }
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
