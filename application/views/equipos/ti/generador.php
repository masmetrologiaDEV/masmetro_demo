<!-- page content -->
<div class="right_col" role="main">
   <div class="">
      <div class="clearfix"></div>
      <div class="row">
         <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
               <div class="x_title">
                  <h2>Generador</h2>
                  <ul class="nav navbar-right panel_toolbox">
                     <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a></li>
                     <li><a class="close-link"><i class="fa fa-close"></i></a></li>
                  </ul>
                  <div class="clearfix"></div>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
               <div class="x_content">
                  <div class="table-responsive">
                     <button onclick="agregar()" class="btn btn-primary btn-xs">
                     <i class="fa fa-plus"></i> Agregar 
                     </button>
                     <label id="lblCount" class="pull-right"></label>
                     <table id="tabla" class="table table-striped">
                        <thead>
                           <tr class="headings ">
                              <th class="column-title text-center">Fecha Inicio</th>
                              <th class="column-title text-center">Fecha Terminado</th>
                              <th class="column-title text-center">Duracion</th>
                              <th class="column-title text-center">Porcentaje Carga</th>
                              <th class="column-title text-center">Amperaje</th>
                              <th class="column-title text-center">Consumo</th>
                              <th class="column-title text-center">T. Funcion Motor</th>
                              <th class="column-title text-center">No. Arranques</th>
                              <th class="column-title text-center">Usuario</th>
                              <th class="column-title text-center">Foto</th>
                              <th class="column-title text-center">Fecha Registro</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
                              if ($data) {
                                  foreach ($data as $elem) {
                                      ?>
                           <tr class="even pointer text-center">
                              <td ><?= $elem->fecha_inicio ?></td>
                              <td ><?= $elem->fecha_final ?></td>
                              <td ><?= $elem->duracion?></td>
                              <td ><?= $elem->porcentaje ?></td>
                              <td><?= $elem->amperaje ?></td>
                              <td><?= $elem->consumo ?></td>
                              <td><?= $elem->motor ?></td>
                              <td><?= $elem->arranques ?></td>
                              <td ><?= $elem->name ?></td>
                              <td id="simple_gallery">
                                 <?php if ($elem->foto) {?>
                                 <div class="image view view-first">
                                    <a id="single_image" href="<?= 'data:image/png;base64,' . base64_encode($elem->foto) ?>">
                                    <span>Ver Foto</span>
                                    </a>
                                    <label class="btn btn-success btn-xs">
                                    <input accept="image/png, image/gif, image/jpeg" type="file" class="sr-only imgAuto"
                                       data-id="<?= $elem->id ?>" onchange="uploadFoto(this);">
                                    <i class="fa fa-file"></i> Cambiar Foto
                                    </label>
                                 </div>
                                 <?php 
                                    } ?>
                              </td>
                              <td><?= $elem->fecha ?></td>
                           </tr>
                           <?php }
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
<!-- Modal -->
<div id="formModal" class="modal fade" role="dialog">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Agregar Registro</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action="<?= base_url('equipos/registrar_generador') ?>" class="form-horizontal form-label-left" enctype="multipart/form-data">
               <div class="form-group">
                  <label for="fecha_inicio">Fecha Inicio</label>
                  <input type="datetime-local" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
               </div>
               <div class="form-group">
                  <label for="fecha_final">Fecha Final</label>
                  <input type="datetime-local" class="form-control" id="fecha_final" name="fecha_final" required>
               </div>
               <div class="form-group">
                  <label for="duracion">Duración</label>
                  <input type="text" class="form-control" id="duracion" name="duracion" required readonly>
               </div>
               <div class="form-group">
                  <label for="foto">Foto</label>
                  <input type="file" class="form-control" id="foto" name="foto" accept="image/*">
               </div>
	       <div class="form-group">
                  <label for="consumo">Consumo (kW)</label>
                  <input type="number" class="form-control" id="consumo" name="consumo" required min="0" max="100" step="0.1">
               </div>
               <div class="form-group">
                  <label for="porcentaje">Porcentaje</label>
                  <input type="text" class="form-control" id="porcentaje" name="porcentaje" required" readonly>
               </div>
               <div class="form-group">
                  <label for="amperaje">Amperaje</label>
                  <input type="text" class="form-control" id="amperaje" name="amperaje" required>
               </div>
               <div class="form-group">
                  <label for="amperaje">T. Funcion Motor</label>
                  <input type="text" class="form-control" id="motor" name="motor" required>
               </div>
               <div class="form-group">
                  <label for="amperaje">No. Arranques</label>
                  <input type="text" class="form-control" id="arranques" name="arranques" required>
               </div>
               <button type="submit" class="btn btn-primary">Guardar</button>
            </form>
         </div>
      </div>
   </div>
</div>
<!-- footer content -->
<footer>
   <div class="pull-right">
      Equipo de Desarrollo | MAS Metrología
   </div>
   <div class="clearfix"></div>
</footer>
<!-- /footer content -->
<!-- jQuery -->
<script src=<?= base_url("template/vendors/jquery/dist/jquery.min.js"); ?>></script>
<!-- Bootstrap -->
<script src=<?= base_url("template/vendors/bootstrap/dist/js/bootstrap.min.js"); ?>></script>
<!-- PNotify -->
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.buttons.js"); ?>></script>
<script src=<?= base_url("template/vendors/pnotify/dist/pnotify.nonblock.js"); ?>></script>
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- formatCurrency -->
<script src=<?= base_url("template/vendors/formatCurrency/jquery.formatCurrency-1.4.0.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<script src=<?= base_url("template/vendors/fancybox/dist/jquery.fancybox.min.js"); ?>></script>
<script>
   $(document).ready(function () {
   
      /* This is basic - uses default settings */
   
      $("a#single_image").fancybox();
   
      /* Using custom settings */
   
      $("a#inline").fancybox({
          'hideOnContentClick': true
      });
   
      /* Apply fancybox to multiple items */
   
      $("a.group").fancybox({
          'transitionIn': 'elastic',
          'transitionOut': 'elastic',
          'speedIn': 600,
          'speedOut': 200,
          'overlayShow': false
      });
   });
   function agregar(){ 
   $('#formModal').modal();
   }
   document.addEventListener("DOMContentLoaded", function () {
      const fechaInicio = document.getElementById("fecha_inicio");
      const fechaFinal = document.getElementById("fecha_final");
      const duracion = document.getElementById("duracion");
      const porcentaje = document.getElementById("porcentaje");
      const consumo = document.getElementById("consumo");
   
      function calcularDuracion() {
         if (fechaInicio.value && fechaFinal.value) {
            const inicio = new Date(fechaInicio.value);
            const final = new Date(fechaFinal.value);
   
            if (final >= inicio) {
               let diferencia = (final - inicio) / 1000; // Diferencia en segundos
               let horas = Math.floor(diferencia / 3600);
               let minutos = Math.floor((diferencia % 3600) / 60);
               let segundos = Math.floor(diferencia % 60);
   
               let strHoras = horas.toString().padStart(2, '0');
               let strMinutos = minutos.toString().padStart(2, '0');
               let strSegundos = segundos.toString().padStart(2, '0');
   
               if (horas > 0) {
                  duracion.value = `${strHoras}:${strMinutos}:${strSegundos} horas`;
               } else {
                  duracion.value = `${strMinutos}:${strSegundos} minutos`;
               }
            } else {
               duracion.value = "Fecha inválida";
            }
         }
      }
   
      function calcularConsumo() {
         let consumoVal = parseFloat(consumo.value);
         if (!isNaN(consumoVal) && consumoVal >= 0 && consumoVal <= 100) {
            let porcentajeKW = (consumoVal / 80) * 100; // Capacidad de 80 kW
            porcentaje.value = porcentajeKW.toFixed(2);
         } else {
            consumo.value = "";
         }
      }
   
      fechaInicio.addEventListener("change", calcularDuracion);
      fechaFinal.addEventListener("change", calcularDuracion);
      consumo.addEventListener("input", calcularConsumo);
   });
   
   function uploadFoto(input) {
    var file = input.files[0]; // Obtener el archivo del input
    var id = input.getAttribute("data-id"); // Obtener el ID del equipo desde data-id
   
    if (!file || !id) {
        alert("Error: No se seleccionó una imagen o no se encontró el ID.");
        return;
    }
   
    var URL = base_url + 'equipos/uploadFoto';
    var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("id", id);
   
    var ajax = new XMLHttpRequest();
    ajax.open("POST", URL, true);
    ajax.onload = function () {
        if (ajax.status === 200) {
            alert("Foto subida correctamente.");
            window.location.reload();
        } else {
            alert("Error al subir la foto.");
        }
    };
    ajax.send(formdata);
   }
   
</script>
</body>
</html>
