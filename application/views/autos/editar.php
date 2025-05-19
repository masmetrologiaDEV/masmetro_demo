<!-- page content -->
<div class="right_col" role="main">
    <div class="">

        <div class="clearfix"></div>

        <div class="row">
            <div class="x_content">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            <h2>Editar Auto</h2>

                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                               
                        </div>



                        <div class="x_content" >

<div class="col-md-3 col-sm-3 col-xs-12">
                <div class="x_panel">
                    <div class="x_title">
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

                        <img width="100%" src="<?= 'data:image/bmp;base64,' . base64_encode($auto->foto); ?>">  
                        <label class="btn btn-default btn-sm" for="imgAuto">
                                <input accept="image/png, image/gif, image/jpeg" target="_blank" onchange="uploadFoto();" type="file" class="sr-only" id="imgAuto" name="imgAuto">
                                            <i class="fa fa-file"></i> Cambiar foto
                              </label>
                        <!--<label class="btn btn-default btn-sm" for="file">
                        <input type=file target="_blank" type="file" class="sr-only" id="file" name="file">
                        <i class="fa fa-file"></i> Seleccionar Archivo
                        </label>
                        <input type="button" id="btn_uploadfile" value="Aceptar" onclick="uploadFile();" class="btn btn-default btn-sm">    -->                 
                      </div>

                      <div>
                        <ul class="list-inline widget_tally">
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Marca:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto->marca ?></span>
                            </p>
                          </li>
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Combustible:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto->combustible ?></span>
                            </p>
                          </li>
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >Placas:</span>
                              <span style="width: 50%; float: left; text-align: left;"><?= $auto->placas ?></span>
                            </p>
                          </li>
                          <li>
                            <p>
                              <span style="width: 50%; float: left; text-align: left" >No Poliza:</span>
                              <span style="width: 50%; float: left; text-align: left;"><a target='_blank' href="<?= base_url('autos/getFile/'.$auto->id) ?>"><img height='25px' src="<?= base_url('template/images/files/pdf.png')?>"></a>
                              <label class="btn btn-default btn-sm" for="poliza">
                                <input accept="application/pdf" target="_blank" onchange="uploadPoliza();" type="file" class="sr-only" id="poliza" name="poliza">
                                            <i class="fa fa-file"></i> Subir Poliza
                              </label>
                            </span>
                              
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
                                            <th class="column-title text-center">Datos del Auto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                      <td>
                                      <form method="POST" action=<?= base_url('autos/updateAuto') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                                        <input style="text-transform: uppercase;" value="<?= $auto->id ?>" class="form-control col-md-7 col-xs-12" name="idAuto" placeholder="" required="required" type="hidden">

                                      <div class="item form-group ">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Serie <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="serie" required="required" type="text" value="<?=$auto ? $auto->serie:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Fabricante <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="fabricante" required="required" type="text" value="<?=$auto ? $auto->fabricante:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Marca <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="marca" required="required" type="text" value="<?=$auto ? $auto->marca:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Modelo <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="modelo" required="required" type="text" value="<?=$auto ? $auto->modelo:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Placas <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="placas" required="required" type="text" value="<?=$auto ? $auto->placas:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Vencimiento de poliza <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="vencimiento_poliza" required="required" type="date" value="<?=$auto ? $auto->vencimiento_poliza:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Vencimiento Ecologico <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="vencimiento_ecologico" required="required" type="date" value="<?=$auto ? $auto->vencimiento_ecologico:""?>" />
                                          </div>
                                      </div>
                                      <div class="item form-group">
                                          <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Tarjeta Combustible <span class="required">*</span>
                                          </label>
                                          <div class="col-md-6 col-sm-6 col-xs-12 ">
                                              <input style="text-transform: uppercase;" class="form-control col-md-7 col-xs-12 text-center" name="tarjeta_combustible" required="required" type="text" value="<?=$auto ? $auto->tarjeta_combustible:""?>" />
                                          </div>
                                      </div>
                                       <div class="item form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Responsable <span class="required">*</span></label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <select required="required" class="select2_single form-control text-center" name="responsable">
                                                <option value=<?= $auto->responsable ?>><?= $auto->User ?></option>
                                                <?php foreach ($usuarios as $elem) { ?>
                                                    <option value=<?= $elem->id ?>><?= $elem->user ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="item form-group">
                                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Activo</label>
                                        <div class="col-md-6 col-sm-6 col-xs-12">
                                            <input type="checkbox" class="flat" name="activo" value="1" <?= $auto->activo == '1' ? 'checked' : '' ?>/>
                                        </div>
                                    </div>

                                      <div class="form-group text-center">
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
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>

<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>

<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<script src=<?= base_url("template/vendors/formatCurrency/jquery.formatCurrency-1.4.0.js"); ?>></script>
<script src=<?= base_url("template/vendors/moment/min/moment.min.js") ?>></script>
<script src=<?= base_url("template/vendors/bootstrap-daterangepicker/daterangepicker.js") ?>></script>
<script src=<?= base_url("template/vendors/jquery.tagsinput/src/jquery.tagsinput.js") ?>></script>
<script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>




<script type="text/javascript">
  var AUTO = '<?= $auto->id?>';

  function uploadFoto(){
    var files = document.getElementById("imgAuto").files;
    var file = files[0];
    var URL = base_url + 'autos/uploadFoto';
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("id", AUTO);
    var ajax = new XMLHttpRequest();
    ajax.open("POST", URL);
    ajax.send(formdata);
    ajax.onload = function(){
      window.location.reload();
    }
  }  
  function uploadPoliza(){
    var files = document.getElementById("poliza").files;
    var file = files[0];
    var URL = base_url + 'autos/uploadPoliza';
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("id", AUTO);
    var ajax = new XMLHttpRequest();
    ajax.open("POST", URL);
    ajax.send(formdata);
    ajax.onload = function(){
      window.location.reload();
    }
  }

 
</script>
</body>
</html>
