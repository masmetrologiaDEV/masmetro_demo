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

            <div class="col-md-9 col-sm-9 col-xs-12">
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
                          <?php if($equipo->tipo=="Celular") {
                                ?>
                                <table id="tabla" class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            
                                            <th class="column-title">Case</th>
                                            <th class="column-title">Vidrio Templado</th>
                                            <th class="column-title">Cargador USB</th>
                                            <th class="column-title">Manos Libres</th>
                                            <th class="column-title">Condicion de Bateria</th>
                                            <th class="column-title">Consumo de Datos</th>
                                            <th class="column-title">Comentarios</th>
                                            <th class="column-title">Usuario Mantenimiento</th>
                                            <th class="column-title">Fecha de Mantenimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($manto) {
                                     foreach ($manto->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td><?= $elem->case == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->vidTem == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->usb == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->manosL == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->bateria."%"?></td>
                                                <td><?= $elem->datos."%"?></td>
                                                <td><?= $elem->comentarios?></td>
                                                <td><?= $elem->nombre?></td>
                                                <td><?= $elem->fecha?></td>
                                            </tr>
                                    <?php  }
                                      }
                                    ?>
                                    </tbody>
                                </table>
                                <?php
}elseif ($equipo->tipo=="Laptop" || $equipo->tipo=="Desktop") {
  // code...
?>
                             <table id="tabla" class="table table-striped">
                                    <thead>
                                        <tr class="headings">
                                            
                                            <th class="column-title">Disco</th>
                                            <th class="column-title">CPU</th>
                                            <th class="column-title">Teclado/Mouse</th>
                                            <th class="column-title">Abanicos</th>
                                            <th class="column-title">Comentarios</th>
                                            <th class="column-title">Usuario Mantenimiento</th>
                                            <th class="column-title">Fecha de Mantenimiento</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                    <?php
                                    if($manto) {
                                     foreach ($manto->result() as $elem) { ?>

                                            <tr class="even pointer">
                                                <td><?= $elem->disco == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->cpu == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->tecMouse == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->abanicos == "1" ? "SI" : "NO" ?></td>
                                                <td><?= $elem->comentarios?></td>
                                                <td><?= $elem->nombre?></td>
                                                <td><?= $elem->fecha?></td>
                                            </tr>
                                    <?php  }
                                      }
                                    ?>
                                    </tbody>
                                </table>
<?php }?>
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
