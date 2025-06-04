<!-- page content -->
<?php
   $item=null;
   ?>
<style type="text/css">
   [type="file"] {
   height: 0;
   overflow: hidden;
   width: 0;
   }
   [type="file"] + label {
   background: #5cb85c;
   border: none;
   border-radius: 5px;
   color: #fff;
   cursor: pointer;
   display: inline-block;
   font-family: 'Rubik', sans-serif;
   font-size: inherit;
   font-weight: 500;
   margin-bottom: 1rem;
   outline: none;
   padding: 1rem 50px;
   position: relative;
   transition: all 0.3s;
   vertical-align: middle;
   }
</style>
<div class="right_col" role="main">
   <div class="">
      <div class="clearfix"></div>
      <div class="row">
         <div class="col-md-10 col-sm-10 col-xs-12">
            <div class="x_panel">
               <div class="x_title">
                  <h2>Orden de Trabajo:
                     <?= 'WO-' . str_pad($wo->WorkOrder_ID, 6, "0", STR_PAD_LEFT) ?>
                  </h2>
                  <?php
                     if ($this->session->privilegios['cerrar_wo'] && $wo->Status_Descripcion == "CANCELADA" || $wo->Status_Descripcion == "CONCLUIDA") {
                        
                     
                                       ?>
                  <button type='button'onclick="mdlCerrarWO(<?=$wo->WorkOrder_ID?>)" class='btn btn-dark btn-md pull-right solicitud' id="btnEnviar"><i class='fa fa-close'></i> Cerrar WO</button>
                  <?php
                     }
                     $estatus=null;
                     foreach ($wo_detail as $key){
                     $estatus=$key->estatus_item;
                     }
                     
                                                                          if ($wo->Status_Descripcion != "CANCELADA") {
                                                                             if ($wo->Status_Descripcion != "CONCLUIDA") {
                                                                              if ($wo->Status_Descripcion != "CERRADA") {
                                                                                   if ($estatus!= 'PROGRAMADA') {
                     
                     
                        ?>
                  <button type='button'onclick="mdlConcluir(<?=$wo->WorkOrder_ID?>)"  class='btn btn-success btn-md pull-right solicitud' id="btnEnviar"><i class='fa fa-send'></i> Concluir WO</button> 
                  <?php
                     }
                       if ($estatus== 'PROGRAMADA') {
                       
                       ?>
                  <button type='button' onclick="mdlCancelarWO(<?=$wo->WorkOrder_ID?>)" class='btn btn-danger btn-md pull-right solicitud' id="btnCancelarWO"><i class='fa fa-close'></i> Cancelar WO</button>  
                  <?php
                     }}}}
                        ?>
                  <div class="clearfix"></div>
               </div>
               <div class="x_content">
                  <div class="row">
                     <!-- C L I E N T E -->
                     <div class="col-md-4 col-sm-4 col-xs-4">
                        <div style="border: 0; margin-bottom: 0 px;" class="x_panel">
                           <div class="x_title">
                              <div class="clearfix"></div>
                           </div>
                           <div id="divCliente"  class="x_content">
                              <?php
                                 if ($wo->Status_Descripcion != "CANCELADA") {
                                    if ($wo->Status_Descripcion != "CONCLUIDA") {
                                     if ($wo->Status_Descripcion != "CERRADA") {
                                 ?>
                              <div class="row">
                                 <button type='button' onclick="mdlReprogramar(<?=$wo->WorkOrder_ID?>)" id='btnClientes' class='btn btn-primary btn-xs pull-right solicitud'><i class='fa fa-plus'></i> Reprogramar</button>
                                 <div class="col-md-4 col-sm-4 col-xs-8">
                                    <label>Fecha Programada:</label>
                                    <p id="lblRazonSocialCliente"><?=$wo->FProgramado?></p>
                                 </div>
                              </div>
                              <?php
                                 }}}
                                    ?>
                              <label>Cliente/Customer:</label>
                              <p><?=$wo->Empresa?></p>
                              <p><?=$wo->Direccion1?></p>
                              <p><?=$wo->Contacto?></p>
                              <p>Telefono: <?=$wo->TelefonoContacto?> Celular: <?=$wo->CelularContacto?></p>
                           </div>
                        </div>
                     </div>
                     <!-- D A T O S -->
                     <div class="col-md-4 col-sm-4 col-xs-12">
                        <div style="border: 0px;" class="x_panel">
                           <div class="x_title">
                              <div class="clearfix"></div>
                           </div>
                           <div class="x_content">
                              <div id="rowEstatus" style=" margin-top: 30px;" class="row">
                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                    <center>
                                       <label style="display: block">Estatus:</label>
                                       <?php
                                          switch ($wo->Status_Descripcion) {
                                             case 'PROGRAMADA':
                                             case 'REPROGRAMADA':
                                               $BTN_CLASS = 'btn btn-primary';
                                                   break;
                                             case 'CONCLUIDA':
                                               $BTN_CLASS = 'btn btn-success';
                                                   break;
                                             case 'CERRADA':
                                               $BTN_CLASS = 'btn btn-dark';
                                                   break;
                                             case 'CANCELADA':
                                               $BTN_CLASS = 'btn btn-danger';
                                                   break;
                                          
                                             
                                             default:
                                                // code...
                                                break;
                                          }
                                          
                                          ?>
                                       <button onclick="bitacoraEstatus()" id="btnEstatus" type="button" class=<?= "'" . $BTN_CLASS . "'" ?>><?=$wo->Status_Descripcion?></button>
                                       <br>
                                       <a target="_blank" href=<?= base_url("ordenes_trabajo/ver_wo_pdf/".$wo->WorkOrder_ID); ?>><button type='button' class='btn btn-warning btn-md solicitud' id="btnEnviar"><i class='fa fa-file'></i> Ver WO PDF</button>  </a>
                                    </center>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php
                        if ($wo->archivo) {
                        ?>
                     <div class="col-md-4 col-sm-4 col-xs-12">
                        <div style="border: 0px;" class="x_panel">
                           <div class="x_title">
                              <div class="clearfix"></div>
                           </div>
                           <div class="x_content">
                              <div id="rowEstatus" style=" margin-top: 30px;" class="row">
                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                    <center>
                                       <label class="pull-left" style="display: block">Evidencia:</label>
                                       <br>
                                       <?php
                                       if(!$this->aos_funciones->is_image($wo->nombre_archivo)){                            
                                       ?>
                                       <div class="col-md-20 col-sm-4 col-xs-12">
                                
                                <a href="<?= base_url('descargas/ordenes_trabajo/' . $wo->WorkOrder_ID) ?>">
                                  <img style="width: 80%; margin-bottom: 10px;" title="<?= $wo->nombre_archivo ?>" src="<?= $this->aos_funciones->file_image($wo->nombre_archivo) ?>" />
                                

                                </a>
                              </div>

<?php
                                      } else{                           
                                       ?>


                                       <div id="simple_gallery" class="box-content">
                                          <div class="col-md-20 col-sm-8 col-xs-12">
                                             <div class="image view view-first">
                                                <a id="single_image" href="<?= 'data:image/png;base64,' . base64_encode($wo->archivo) ?>">
                                                <img style="width: 100%; display: block;" src="<?= 'data:image/png;base64,' . base64_encode($wo->archivo) ?>" />
                                                </a>
                                             </div>
                                          </div>
                                          </div>
                                          <?php
                                       }
                                          ?>
                                    </center>
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                     <?php
                        }
                        ?>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="">
         <div class="clearfix"></div>
         <div class="row">
            <div class="col-md-10 col-sm-10 col-xs-12">
               <div class="x_panel">
                  <div class="x_content">
                     <div class="row">
                        <div class="row">
                           <!-- C O N C E P T O S   Y   C O M E N T A R I O S -->
                           <div class="col-md-12 col-sm-12 col-xs-12" >
                              <div class="row" >
                                 <div class="col-md-12 col-sm-12 col-xs-12">
                                    <div style="border: 0;" class="x_panel">
                                       <div class="x_title">
                                          <h3 style="display: inline;">Items RS</h3>
                                          <div class="clearfix"></div>
                                       </div>
                                       <div class="x_content">
                                          <div class="row">
                                             <h4 class="pull-right" id="lblItemsCount"></h4>
                                             <table id="tblConceptosRS" class="data table table-striped no-margin">
                                                <thead>
                                                   <tr>
                                                      <th>RS</th>
                                                      <th>Item</th>
                                                      <th>Descripción</th>
                                                      <th>Localizacion</th>
                                                      <th>Vencimiento</th>
                                                      <th>Calibrado</th>
                                                      <th>Tipo de Calibracion</th>
                                                      <th>Motivo</th>
                                                      <th>Estatus</th>
                                                      <th>Opciones</th>
                                                   </tr>
                                                </thead>
                                                <tbody>
                                                   <?php foreach ($wo_detail as $key){ ?>
                                                   <tr>
                                                      <td><?=$key->folio_id?></td>
                                                      <td><?=$key->Item_Id?></td>
                                                      <td><?=$key->CadenaDescripcion ?></td>
                                                      <td><?=$key->Localizacion ?></td>
                                                      <td><?=$key->vencimiento ?>
                                                         <br>
                                                         <?php
                                                            //echo $wo->Status_Descripcion;die();
                                                                          if ($wo->Status_Descripcion != "CANCELADA") {
                                                                             if ($wo->Status_Descripcion != "CONLCUIDA") {
                                                                              if ($wo->Status_Descripcion != "CERRADA") {
                                                                          ?>
                                                         <button type="button"class="btn btn-success btn-xs"  onclick="mdlFecha(<?=$key->id?>)"><i class="fa fa-"></i> Cambiar </button>
                                                         <?php
                                                            }}}
                                                            ?>
                                                      </td>
                                                      <td><?=$key->calibrado ?></td>
                                                      <td><?=$key->tipo_cal ?></td>
                                                      <td><?=$key->motivo ?></td>
                                                      <td><?=$key->estatus_item ?></td>
                                                      <td>
                                                         <?php
                                                            //echo $wo->Status_Descripcion;die();
                                                                          if ($wo->Status_Descripcion != "CANCELADA") {
                                                                             if ($wo->Status_Descripcion != "CONLCUIDA") {
                                                                              if ($wo->Status_Descripcion != "CERRADA") {
                                                                          
                                                                                if ($key->calibrado=="NO" || is_null($key->calibrado)) {
                                                                             ?>
                                                         <button type="button"class="btn btn-success btn-xs"  onclick="mdlRealizado(<?=$key->id?>)"><i class="fa fa-"></i> Realizada </button>
                                                         <?php
                                                            }
                                                            if ($key->calibrado=="SI" || is_null($key->calibrado)) { 
                                                            ?>
                                                         <button type="button"class="btn btn-danger btn-xs" onclick="mdlCancelar(<?=$key->id?>)"><i class="fa fa-"></i> No Realizada </button>
                                                         <?php
                                                            }
                                                            }}}
                                                            ?>
                                                      </td>
                                                   </tr>
                                                   <?php } ?>
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
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div id="rowComentarios" style="display: ;" class="row">
         <div class="col-md-10 col-sm-10 col-xs-12">
            <div style="border: 0;" class="x_panel">
               <?php
                  if ($wo->Status_Descripcion != "CERRADA") {
                  ?>
               <div class="x_title">
                  <h3 style="display: inline;">Comentarios</h3>
                  <button type='button' onclick="mdlComentario()" class='btn btn-primary btn-xs pull-right'><i class='fa fa-comments'></i> Agregar</button>
                  <div class="clearfix"></div>
               </div>
               <?php
                  }
                     ?>
               <div class="x_content">
                  <ul id='ulComments' class="list-unstyled msg_list">
                  <ul>
               </div>
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
<!-- MODAL RS -->
<div id="mdlRS" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="modal-title">Ingrese RS</h4>
         </div>
         <div class="modal-body">
            <form>
               <h3 style="display: none;" id="lblRS"></h3>
               <div class="input-group">
                  <input id="txtBuscarRS" type="text" class="form-control" placeholder="Buscar RS...">
                  <span class="input-group-btn">
                  <button onclick="buscarRS()" class="btn btn-default" type="button">Buscar</button>
                  </span>
               </div>
               <div id="divSelectTodo" style="display: none"><input id="iptTodo" type="checkbox" class="flat"> Seleccionar Todo</div>
               <table class="data table table-striped no-margin">
                  <thead>
                     <tr>
                        <th>Selecc.</th>
                        <th>RS</th>
                        <th>Item</th>
                        <th>Descripción</th>
                     </tr>
                  </thead>
                  <tbody>
                  </tbody>
               </table>
            </form>
         </div>
         <div class="modal-footer">
            <button data-dismiss="modal" style="display: inline;" type="button" class="btn btn-default pull-left"><i class="fa fa-close"></i> Cerrar</button>
            <button onclick="agregarRSItems()" type="button" class="btn btn-primary"><i class='fa fa-check'></i> Agregar</button>
         </div>
      </div>
   </div>
</div>
<!-- /footer content -->
</div>
</div>
<div id="mdlComentario" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Rechazar Item</h4>
         </div>
         <div class="modal-body">
            <form>
               <label>Comentario</label>
               <textarea style="height: 60px; resize: none;" id="txtComentarios" class="form-control"></textarea>
            </form>
         </div>
         <div class="modal-footer">
            <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
            <button id="btnComentario" type="button" onclick="agregarComentario(<?=$wo->WorkOrder_ID?>)" class="btn btn-primary btn-sm"><i class='fa fa-times-comment'></i> Agregar</button>
         </div>
      </div>
   </div>
</div>
<div id="mdlCancelar" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Liberar Item</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/rechazar_item') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <label>Motivo</label>
               <select style="display: inline; margin-right: 10px;" required="required" class="selectpicker" id="motivo" name="motivo">
                  <option value='Equipo no encontrado'>Equipo no encontrado</option>
                  <option value='Equipo no funciona'>Equipo no funciona</option>
                  <option value='ID equivocado'>ID equivocado</option>
                  <option value='Calibracion vigente'>Calibracion vigente</option>
                  <option value='Falta Orden de compra'>Falta Orden de compra</option>
               </select>
               <label>Comentario</label>
               <input type="hidden" name="item" id="item">
               <textarea  id="txtrechazar" name="txtrechazar" class="form-control" required rows="5" cols="55"></textarea>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-danger btn-sm"><i class='fa fa-close'></i> Liberar</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlReprogramar" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Reprogramar WO</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/reprogramar') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <div class="col-md-8 col-sm-8 col-xs-12">
                  <label style="display: block;">Fecha Programada</label>
                  <input type="text" class="form-control pull-right" id="txtFechaAccion" name="txtFechaAccion" value="<?= $wo->FProgramado?>?">
               </div>
               <div class="col-md-8 col-sm-8 col-xs-12">
                  <label style="display: block;">Comentarios</label>
                  <textarea  id="txtrechazar" name="txtreprogramar" class="form-control" required rows="5" cols="55"></textarea>
               </div>
               <input type="hidden" name="itemR" id="itemR">
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-success btn-sm"><i class='fa fa-close'></i> Reprogramar</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlRealizado" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Liberar Item</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/realizado') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <div class="col-md-12 col-sm-12 col-xs-12">
                  <label>Tipo de Calibracion</label>
                  <input type="hidden" name="itemOk" id="itemOk">
                  <select style="display: inline; margin-right: 10px;" required="required" onclick="text_realizado()" class="selectpicker" id="tipo_cal" name="tipo_cal">
                     <option value='Normal'>Normal</option>
                     <option value='Limitada'>Limitada</option>
                     <option value='Fuera de cal.'>Fuera de cal.</option>
                  </select>
               </div>
               <div class="col-md-12 col-sm-12 col-xs-12" id="textarea" style="display: none;">
                  <label style="display: block;">Comentarios</label>
                  <textarea  id="txtrealizado" name="txtrealizado" class="form-control"  rows="5" cols="55"></textarea>
               </div>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-success btn-sm"><i class='fa fa-check'></i> Realizado</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlFecha" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Fecha Vencimiento</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/fecha_vencimiento') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <label>Si el equipo tiene etiqueta de calibracion, ingrese la fecha del vencimiento de la misma.</label>
               <input type="hidden" name="itemFecha" id="itemFecha">
               <input type="date" name="fecha" id="fecha" required>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-success btn-sm"><i class='fa fa-close'></i> Aceptar</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlConcluir" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Concluir WO</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/conlcuir_wo') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <input type="hidden" name="itemConcluir" id="itemConcluir">
               <div class="col-md-12 col-sm-12 col-xs-12">
                  <label style="display: block;">Comentarios</label>
                  <textarea  id="txtconcluir" name="txtconcluir" class="form-control" required rows="5" cols="55"></textarea>
               </div>
               <div class="col-md-12 col-sm-12 col-xs-12">
                  <br>
                  <input type="file" id="file" name="foto" required  accept="application/pdf, image/*"/>
                  <label for="file"/>Subir Archivo</label>
               </div>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-success btn-sm"><i class='fa fa-check'></i> Concluir</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlCancelarwo" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Cancelar WO</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/cancelar_wo') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <input type="hidden" name="itemCancelarWO" id="itemCancelarWO">
               <div class="col-md-12 col-sm-12 col-xs-12">
                  <label style="display: block;">Comentarios</label>
                  <textarea  id="txtcancelar" name="txtcancelar" class="form-control" required rows="5" cols="55"></textarea>
               </div>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-danger btn-sm"><i class='fa fa-close'></i> Cancelar</button>
         </div>
         </form>
      </div>
   </div>
</div>
<div id="mdlCerrarWO" class="modal fade bs-example-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-sm">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 id='mdlComentarioTitle' class="modal-title">Cerrar WO</h4>
         </div>
         <div class="modal-body">
            <form method="POST" action=<?= base_url('ordenes_trabajo/cerrar_wo') ?> class="form-horizontal form-label-left"  enctype="multipart/form-data">
               <input type="hidden" name="itemCerrar" id="itemCerrar">
               <div class="col-md-12 col-sm-12 col-xs-12">
                  <label style="display: block;">Comentarios</label>
                  <textarea  id="txtcerrar" name="txtcerrar" class="form-control" required rows="5" cols="55"></textarea>
                  <p style="margin-top: 8px">
                     Enviar email al contacto:
                     <input type="checkbox" id="mail" name="mail" value="1" class="flat"/>
                  </p>
               </div>
         </div>
         <div class="modal-footer">
         <button id="btnCancelar" type="button" data-dismiss="modal" class="btn btn-default btn-sm pull-left"><i class="fa fa-close"></i> Cancelar</button>
         <button id="btnComentario" type="submit" class="btn btn-dark btn-sm"><i class='fa fa-close'></i> Cerrar</button>
         </div>
         </form>
      </div>
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
<!-- iCheck -->
<script src=<?= base_url("template/vendors/iCheck/icheck.min.js"); ?>></script>
<!-- formatCurrency -->
<script src=<?= base_url("template/vendors/formatCurrency/jquery.formatCurrency-1.4.0.js"); ?>></script>
<!-- Custom Theme Scripts -->
<script src=<?= base_url("template/build/js/custom.js"); ?>></script>
<!-- bootstrap-wysiwyg -->
<script src=<?=base_url("template/vendors/bootstrap-wysiwyg/js/bootstrap-wysiwyg.min.js"); ?>></script>
<script src=<?=base_url("template/vendors/jquery.hotkeys/jquery.hotkeys.js"); ?>></script>
<script src=<?=base_url("template/vendors/google-code-prettify/src/prettify.js"); ?>></script>
<!-- jQuery Tags Input -->
<script src=<?= base_url("template/vendors/jquery.tagsinput/src/jquery.tagsinput.js") ?>></script>
<!-- jquery.redirect -->
<script src=<?= base_url("template/vendors/jquery.redirect/jquery.redirect.js"); ?>></script>
<!-- CUSTOM JS FILE -->
<script src=<?=base_url("template/js/custom/funciones.js"); ?>></script>
<!-- JS FILE -->
<script src=<?= base_url("application/views/ordenes_trabajo/js/work_orders.js"); ?>></script>
<script src=<?= base_url("template/vendors/bootstrap-daterangepicker/daterangepicker.js") ?>></script>
<!-- FancyBOX -->
<script src=<?= base_url("template/vendors/fancybox/dist/jquery.fancybox.min.js"); ?>></script>
<script>
   var UID = '<?= $this->session->id ?>';
   // var RES = '<?= $this->session->privilegios['responder_facturas'] ?>';
   var EDIT = '<?= isset($editar) ? '1' : '0' ?>';
    var ID = '<?= $wo->WorkOrder_ID?>';
   
   $(function(){
       load();
   });
   
   
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
   
   $(document).ready(function() {                       
    $("#tipo_cal").change(function() {
    tipo = $('#tipo_cal').val();
    var x = document.getElementById("textarea");
    if (tipo == 'Normal') {
        $('#textarea').hide();
   
    }else{
         $('#textarea').show();
   
    }
    
    });
   });
   
   
   
</script>
</body>
</html>
