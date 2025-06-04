        <!-- page content -->
        <div class="right_col" role="main">
          <div class="">

            <div class="clearfix"></div>

            <div class="row">
              <div class="col-md-12">
                <div class="x_panel">
                  <div class="x_title">
                    <h2>Reservaciones Sala de Juntas</h2>
                    <ul class="nav navbar-right panel_toolbox">
                      <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                      </li>
                      <li><a class="close-link"><i class="fa fa-close"></i></a>
                      </li>
                    </ul>
                    <div class="clearfix"></div>
                  </div>
                  <div class="x_content">

                    <div id='calendar'></div>

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

    <!-- calendar modal -->
    <div id="modalNew" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="modalNewTitle">Agendar Sala de Juntas</h4>
          </div>
          <div class="modal-body">
            <div id="testmodal" style="padding: 5px 20px;">
              <form id="antoform" class="form-horizontal calender" role="form">


		<!--AQUI-->
		<div class="form-group">
                    <label class="col-sm-3 control-label">Asunto:</label>
                      <div class='col-sm-9 input-group'>
                        <input id="asunto" type='text' class="form-control" />
                      </div>
                </div>
<!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  -->
<!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  -->

 <div class="form-group">
  <label for="sala" class="col-sm-3 control-label">Sala:</label>
  <div class="col-sm-9 input-group">
    <select name="sala" id="sala" class="form-control" required>
      <option value="1">Sala 1</option>
      <option value="2">Sala 2</option>
    </select>
  </div>
</div>

<!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  -->
<!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  --><!--  --> <!--  --> <!--  -->               
                <div class="form-group">
                    <label class="col-sm-3 control-label">Inicia:</label>
                    <div class='col-sm-9 input-group date' id='timeInicia'>
                        <input id="inicia" type='text' class="form-control" />
                        <span class="input-group-addon">
                           <span class="glyphicon glyphicon-time"></span>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Termina:</label>
                    <div class='col-sm-9 input-group date' id='timeTermina'>
                        <input id="termina" type='text' class="form-control"/>
                        <span class="input-group-addon">
                           <span class="glyphicon glyphicon-time"></span>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Notas:</label>
                    <div class='col-sm-9 input-group date'>
                        <textarea id="notas" class="form-control" ></textarea>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-7 control-label">Se necesita equipo para reunion:</label>
                      <div class='col-sm-3 input-group'>
                        <input type="checkbox"  id="cbreunion" value="1" name="cbreunion"/>
                      </div>
                </div>

                <div id="correos" class="form-group" style="display: none;">
                    <label class="col-sm-3 control-label">Agregar correos:</label>
                      <div class='col-sm-9 input-group'>
                        <input id="tags_1" type="text" class="form-control prov" />  
                      </div>
                </div>

              </form>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default antoclose" data-dismiss="modal">Cerrar</button>
            <button type="button" onclick="validar()" data-dismiss="modal" class="btn btn-primary">Agendar</button>
          </div>
        </div>
      </div>
    </div>
    <div id="modalView" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">

          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            <h4 class="modal-title" id="modalViewTitle"></h4>
          </div>
          <div class="modal-body" style="height: 100%;" >
            <form>
            <div class="form-group" >
              <center style="font-size: 20px;">
                <label style="font-size: 15px;" class="col-sm-12 control-label" id="modalViewDate"></label>
                <label class="col-sm-12 control-label" id="modalViewUsuario">Usuario</label>
<!-- vista de la sala ocupada -->
                <label class="col-sm-12 control-label" id="modalViewSala">Sala</label>

                <label class="col-sm-12 control-label" id="modalViewInicia">Inicia</label>
                <label class="col-sm-12 control-label" id="modalViewTermina">Termina</label>
                <label class="col-sm-12 control-label" id="modalViewNotes">Notas</label>
              </center>
            </div>
          </form>
          </div>
          <div class="modal-footer">
            <button id="modalCancel" onclick="borrarEvento()" type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
            <button type="button" class="btn btn-default antoclose2" data-dismiss="modal">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <!-- /calendar modal -->

    <!-- jQuery -->
    <script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
    <!-- Bootstrap -->
    <script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
    <!-- FastClick -->
    <script src=<?= base_url("template/vendors/fastclick/lib/fastclick.js"); ?>></script>
    <!-- NProgress -->
    <script src=<?= base_url("template/vendors/nprogress/nprogress.js"); ?>></script>
    <!-- FullCalendar -->
    <script src=<?= base_url("template/vendors/moment/min/moment.min.js"); ?>></script>
    <script src=<?= base_url("template/vendors/fullcalendar/dist/fullcalendar.min.js"); ?>></script>
    <script src=<?= base_url("template/vendors/fullcalendar/dist/locale-all.js"); ?>></script>
    <script src=<?= base_url("template/vendors/jquery.tagsinput/src/jquery.tagsinput.js") ?>></script>


    <!-- bootstrap-daterangepicker -->
    <script src=<?= base_url("template/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js") ?>></script>

    <!-- CUSTOM JS FILE -->
    <script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>

    <!-- Custom Theme Scripts -->
    <script src=<?= base_url("template/build/js/custom.js"); ?>></script>
    
  


<script>
  var session_id = "<?= $this->session->id ?>";
  var session_name = "<?= $this->session->nombre ?>";
</script>

<script src="<?= base_url("application/views/agenda/js/calendario.js") ?>"></script>


  </body>
</html>
