<!-- page content -->
<div class="right_col" role="main">
   <div class="">
      <div class="row">
         <div id="filtros" class="col-lg-3 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
               <div class="x_title">
                  <h2>Filtros</h2>
                  <ul class="nav navbar-right panel_toolbox">
                     <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                     </li>
                  </ul>
                  <div class="clearfix"></div>
               </div>
               <div class="x_content">

                  <?php  $current = 'style="border: 2px solid";'; ?>
                  <div class="row top_tiles">
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/activos") ?>">
                           <div <?= $filtro == 'activos' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-square-o"></i></div>
                              <div class="count"><?= $c_activos ?></div>
                              <h3>Activos</h3>
                           </div>
                        </a>
                     </div>

                      <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/detenidos") ?>">
                           <div <?= $filtro == 'detenidos' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-pause"></i></div>
                              <div class="count"><?= $c_detenidos ?></div>
                              <h3>Detenidos</h3>
                           </div>
                        </a>
                     </div>

                     
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/revision") ?>">
                           <div <?= $filtro == 'revision' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-list-ul"></i></div>
                              <div class="count"><?= $c_revision ?></div>
                              <h3>En Revision</h3>
                           </div>
                        </a>
                     </div>
                     
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/solucionados") ?>">
                           <div <?= $filtro == 'solucionados' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-check-square-o"></i></div>
                              <div class="count"><?= $c_solucionados ?></div>
                              <h3>Solucionados</h3>
                           </div>
                        </a>
                     </div>
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/cerrados") ?>">
                           <div <?= $filtro == 'cerrados' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-check-square"></i></div>
                              <div class="count"><?= $c_cerrados ?></div>
                              <h3>Cerrados</h3>
                           </div>
                        </a>
                     </div>
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/cancelados") ?>">
                           <div <?= $filtro == 'cancelados' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-close"></i></div>
                              <div class="count"><?= $c_cancelados ?></div>
                              <h3>Cancelados</h3>
                           </div>
                        </a>
                     </div>
                     <div class="animated flipInY col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <a href="<?= base_url($controlador . "/administrar/todos") ?>">
                           <div <?= $filtro == 'todos' ? $current : "" ?> class="tile-stats">
                              <div class="icon"><i class="fa fa-cubes"></i></div>
                              <div class="count"><?= $c_todos ?></div>
                              <h3>Todos</h3>
                           </div>
                        </a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
         <div style="transition: 2s;" id="tickets" class="col-lg-9 col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
               <div class="x_title">
                  <h2><a onclick="closeNav()" id="menu_toggle"><i class="fa fa-bars"></i></a> <?= str_replace('_', ' ', ucfirst($controlador)) . ": " . ucfirst($filtro) ?></h2>
                  <ul class="nav navbar-right panel_toolbox">
                     <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                     </li>
                     <li><a class="close-link"><i class="fa fa-close"></i></a>
                     </li>
                  </ul>
                  <div class="clearfix"></div>
                  <form method="POST" action=<?= base_url('tickets_IT/excel') ?> class="form-horizontal form-label-left" novalidate enctype="multipart/form-data">
                     <div class="col-md-12 col-sm-12 col-xs-12">
                        <!--  
                     
                        <p style="display: inline; margin-right: 10px; margin-left: 10px;">
                           ESTATUS: 
                        </p>
                        <select style="display: inline; width: 12%; margin-right: 10px;" class="select2_single form-control-xs" name="estatus">
                           <option value="TODOS">TODOS</option>
                           <option value="ABIERTO">ACTIVOS</option>
                           <option value="SOLUCIONADO">SOLUCIONADOS</option>
                           <option value="CERRADO">CERRADOS</option>
                           <option value="CANCELADOS">CANCELADOS</option>
                        </select>

                        -->
                        <input type="hidden" name="estatus" id="estatus" value="<?=$filtro?>">

                        <p style="display: inline; margin-right: 10px; margin-left: 10px;">
                           USUARIO: 
                        </p>
                        <select style="display: inline; width: 12%; margin-right: 10px;" class="select2_single form-control-xs" name="user" id="user" onchange="buscar();">
                           <option value=""></option>
                           <?php foreach ($usuarios as $elem) { ?>
                           <option value=<?= $elem->id ?>><?= $elem->user ?></option>
                           <?php } ?>
                        </select>   
                        <input id="fecha1" style="display: inline;" type="date" name="fecha1" required>
                        <input id="fecha2" style="display: inline;" type="date" name="fecha2" required>
                        <button type="button" class="btn btn-primary btn-xs" onclick="buscar();"><i class="fa fa-search" ></i> Buscar </button>
                        <button type="submit" class="btn btn-success btn-xs"><i class="fa fa-file-excel-o"></i> Exportar </button>
                     </div>
                  </form>
               </div>
<?php
$count=null;
switch ($filtro) {
   case 'activos':
      $count = $c_activos;

      break;
   case 'detenidos':
      $count = $c_detenidos;
      break;
   case 'solucionados':
      $count = $c_solucionados;
      break;
   case 'revision':
      $count = $c_revision;
      break;
   case 'cerrados':
      $count = $c_cerrados;
      break;
   case 'cancelados':
      $count = $c_cancelados;
      break;
   case 'todos':
      $count = $c_todos;
      break;
   default:
      // code...
      break;
}
?>
               <div class="x_content">
                  <div class="table-responsive">
                     <label id="lblCount" class="pull-right"><?=$count . ($count == 1 ? " Ticket" : " Ticket's");?>
                     </label>
                     <table id="tabla_tickets" class="table table-striped">
                        <thead>
                           <tr class="headings">
                              <th class="column-title">ID</th>
                              <th class="column-title">Fecha de Creación</th>
                              <th class="column-title">Usuario</th>
                              <th class="column-title">Tipo</th>
                              <th class="column-title">Titulo</th>
                              <th class="column-title">Estatus</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
                              if ($tickets) {
                                  $BTN_CLASS = 'btn btn-default';
                                  foreach ($tickets as $elem) {
                                      switch ($elem['estatus']) {
                              
                                          case 'ABIERTO':
                                              $BTN_CLASS = 'btn btn-primary';
                                              break;

                                          case 'EN REVISION':
                                              $BTN_CLASS = 'btn btn-warning';
                                              break;
                              
                                          case 'EN CURSO':
                                              $BTN_CLASS = 'btn btn-info';
                                              break;
                              
                                          case 'DETENIDO':
                                              $BTN_CLASS = 'btn btn-danger';
                                              break;
                              
                                          case 'CANCELADO':
                                              $BTN_CLASS = 'btn btn-default';
                                              break;
                              
                                          case 'SOLUCIONADO':
                                              $BTN_CLASS = 'btn btn-success';
                                              break;
                              
                                          case 'CERRADO':
                                              $BTN_CLASS = 'btn btn-dark';
                                              break;
                                      }
                                      ?>
                           <tr class="even pointer">
                              <td><?= substr($controlador, 8) . str_pad($elem['id'], 6, "0", STR_PAD_LEFT) ?></td>
                              <td>
                                 <?php $date = date_create($elem['fecha']); ?>
                                 <a><?= date_format($date, 'd/m/Y h:i A'); ?></a>
                              </td>
                              <td><?= $elem['User'] ?></td>
                              <td><?= $elem['tipo'] ?></td>
                              <td><?= $elem['titulo'] ?></td>
                              <td><a href=<?= base_url($controlador . "/ver/" . $elem['id']) ?>><button type="button" class=<?= "'" . $BTN_CLASS . "'" ?>><?= $elem['estatus'] ?></button></a></td>
                           </tr>
                           <?php
                              }
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
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js") ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js") ?>></script>
<!-- FastClick -->
<script src=<?= base_url("template/vendors/fastclick/lib/fastclick.js") ?>></script>
<!-- NProgress -->
<script src=<?= base_url("template/vendors/nprogress/nprogress.js") ?>></script>
<!-- bootstrap-progressbar -->
<script src=<?= base_url("template/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js") ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<script>
   <?php
      if (isset($this->session->errores)) {
          foreach ($this->session->errores as $error) {
              echo "new PNotify({ title: '" . $error['titulo'] . "', text: '" . $error['detalle'] . "', type: 'error', styling: 'bootstrap3' });";
          }
          $this->session->unset_userdata('errores');
      }
      if (isset($this->session->aciertos)) {
          foreach ($this->session->aciertos as $acierto) {
              echo "new PNotify({ title: '" . $acierto['titulo'] . "', text: '" . $acierto['detalle'] . "', type: 'success', styling: 'bootstrap3' });";
          }
          $this->session->unset_userdata('aciertos');
      }
      ?>
   
   
   function closeNav() {
     if(document.getElementById("filtros").style.display == "none")
     {
       document.getElementById("tickets").className = "col-lg-9 col-md-12 col-sm-12 col-xs-12";
       $('#filtros').delay( 2000 ).fadeIn('slow');
     }
     else {
       $('#filtros').fadeOut('slow',function(){
         document.getElementById("tickets").className = "col-lg-12 col-md-12 col-sm-12 col-xs-12";
       });
     }
   
   }

   
</script>

<script>
  
</script>

<script type="text/javascript">
    function buscar() {
      var estatus='<?=$filtro?>';
      var controlador='<?=$controlador?>';
      var user=$('#user').val();
      var fecha1 =$('#fecha1').val();
      var fecha2 =$('#fecha2').val();
      var URL = base_url + "tickets_IT/buscar_tickets";
      $('#tabla_tickets tbody tr').remove();

      $.ajax({
         type : 'POST',
         url : URL,
         data : {estatus : estatus, user : user, fecha1 : fecha1, fecha2 : fecha2}, 
         success : function(result){
//console.log("Datos recibidos:", result);
            if (result) {
               var tab = $('#tabla_tickets tbody')[0];
               var rs = JSON.parse(result);
               $('#lblCount').text(rs.length + (rs.length == 1 ? " Ticket" : " Ticket's"));
                   $.each(rs, function(i, elem){

                     var BTN_CLASS =null;
//COMPLETAR switch
                     switch(elem.estatus){
                        case 'ABIERTO':
                              BTN_CLASS = 'btn btn-primary';
                              break;
                           
                        case 'EN REVISION':
                               BTN_CLASS = 'btn btn-warning';
                               break;
                              
                        case 'EN CURSO':
                              BTN_CLASS = 'btn btn-info';
                              break;
                              
                        case 'DETENIDO':
                              BTN_CLASS = 'btn btn-warning';
                              break;
                              
                        case 'CANCELADO':
                              BTN_CLASS = 'btn btn-default';
                              break;
                              
                        case 'SOLUCIONADO':
                              BTN_CLASS = 'btn btn-success';
                              break;
                              
                        case 'CERRADO':
                              BTN_CLASS = 'btn btn-dark';
                              break;      
                     }

                     var ren = tab.insertRow(tab.rows.length);

                     ren.insertCell().innerHTML = elem.id;
                     ren.insertCell().innerHTML = elem.fecha;
                     ren.insertCell().innerHTML = elem.User;
                     ren.insertCell().innerHTML = elem.tipo;
                     ren.insertCell().innerHTML = elem.titulo;
                     ren.insertCell().innerHTML = "<td><a href='" + base_url + "/" + controlador + "/ver/" + elem.id + "'><button type='button' class='" + BTN_CLASS + "'>" + elem.estatus + "</button></a></td>";
                   });               
            }else{
               new PNotify({ title: '¡Nada por aquí!', text: 'No se encontraron resultados', type: 'info', styling: 'bootstrap3' });
            }
         },
         error: function(data) {
            new PNotify({ title: 'ERROR', text: 'Error', type: 'error', styling: 'bootstrap3' });
            console.log(data);
         },

      });
   }
   

</script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
</body>
</html>