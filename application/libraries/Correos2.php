<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Correos {

    function creacionTicket($datos) {

        $id = $datos['id'];
        $prefijo = $datos['prefijo'];
        $titulo = $datos['titulo'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        $correo = $datos['correo'];

        $logo = base_url('template/images/logo.png');
        $url = base_url('tickets_' . $prefijo . '/ver/') . $id;
        $idCompleto = $prefijo . str_pad($id, 6, "0", STR_PAD_LEFT);

        if($prefijo == "IT")
        {
            $remitentes = array('tickets@masmetrologia.com','jcastaneda@masmetrologia.com', 'crodriguez@masmetrologia.com', 'jc@masmetrologia.com', $correo);
        }
        else if ($prefijo == "AT")
        {
            $remitentes = array('gsolano@masmetrologia.com', $correo);
        }
        else if ($prefijo == "ED")
        {
            $remitentes = array('gsolano@masmetrologia.com', $correo);
        }
        else
        {
            exit();
        }

        $CI = & get_instance();
        $CI->load->library('email');


        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Se ha creado Ticket de Servicio $prefijo</h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Titulo:</b> $titulo</p>
               <a href='$url' class='btn btn-primary'>Ver Ticket</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Ticket De Servicio');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function ticketSolucionado($datos) {
        $id = $datos['id'];
        $prefijo = $datos['prefijo'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        $correo = $datos['correo'];
        $tecnico = $datos['tecnico'];
        $estatus = $datos['estatus'];

        $logo = base_url('template/images/logo.png');
        $url = base_url('tickets_' . $prefijo . '/ver/') . $id;
        $idCompleto = $prefijo . str_pad($id, 6, "0", STR_PAD_LEFT);

        $CI = & get_instance();
        $CI->load->library('email');

        if($prefijo == "IT")
        {
            if ($estatus == 'ABIERTO') {
                $remitentes = array('crodriguez@masmetrologia.com', 'jc@masmetrologia.com', 'jcastaneda@masmetrologia.com', $correo);
            }else{
                $remitentes = array($correo);
            }
            //$remitentes = array('tickets@masmetrologia.com', 'crodriguez@masmetrologia.com', 'jc@masmetrologia.com', 'jcastaneda@masmetrologia.com', $correo);
             
        }
        else if ($prefijo == "AT")
        {
            $remitentes = array('gsolano@masmetrologia.com', $correo);
        }
        else if ($prefijo == "ED")
        {
            $remitentes = array('gsolano@masmetrologia.com', $correo);
        }
        else
        {
            exit();
        }


        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Ticket de Servicio $prefijo</h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Cambio de estatus de Ticket de Servicio</b></p>
               <p><b>$estatus por:</b> $tecnico</p>
               <a href='$url' class='btn btn-primary'>Ver Ticket</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');


        $CI->email->to($remitentes);

        $CI->email->subject('Ticket de Servicio');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function creacionQR($datos) {

        $id = $datos['id'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        //$cliente = $datos['cliente'];
        $prioridad = $datos['prioridad'];
        $unidad = $datos['unidad'];
        $cantidad = $datos['cantidad'];
        $descripcion = $datos['descripcion'];
        $atributos = json_decode($datos['atributos'], TRUE);
        $correos = $datos['correos'];
        $comentarios = $datos['comentarios'];


        $logo = base_url('template/images/logo.png');
        $url = base_url('compras/ver_qr/') . $id;
        $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

        $remitentes = array('tickets@masmetrologia.com');

        $remitentes = array_merge($remitentes, $correos);
        

        $CI = & get_instance();
        $CI->load->library('email');

        $att = "";
        foreach ($atributos as $key => $value) {
            $att .= "<p><b>" . ucfirst($key) . ":</b> $value</p>";
        }

        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Se ha creado QR</h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Prioridad:</b> $prioridad</p>
               <p><b>Unidad:</b> $unidad</p>
               <p><b>Cantidad:</b> $cantidad</p>
               <p><b>Descripción:</b> $descripcion</p>

               $att

               <br>
               <p><b>Notas: </b> $comentarios</p>
               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Requisición de Cotización (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function edicionQR($datos) {

        $id = $datos['id'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        //$cliente = $datos['cliente'];
        $prioridad = $datos['prioridad'];
        $unidad = $datos['unidad'];
        $cantidad = $datos['cantidad'];
        $descripcion = $datos['descripcion'];
        $atributos = json_decode($datos['atributos'], TRUE);
        $correos = $datos['correos'];
        $comentarios = $datos['comentarios'];


        $logo = base_url('template/images/logo.png');
        $url = base_url('compras/ver_qr/') . $id;
        $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

        $remitentes = array('tickets@masmetrologia.com');

        $remitentes = array_merge($remitentes, $correos);
        

        $CI = & get_instance();
        $CI->load->library('email');

        $att = "";
        foreach ($atributos as $key => $value) {
            $att .= "<p><b>" . ucfirst($key) . ":</b> $value</p>";
        }

        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Se ha editado QR</h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Prioridad:</b> $prioridad</p>
               <p><b>Unidad:</b> $unidad</p>
               <p><b>Cantidad:</b> $cantidad</p>
               <p><b>Descripción:</b> $descripcion</p>

               $att

               <br>
               <p><b>Notas: </b> $comentarios</p>
               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Requisición de Cotización (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function comentarioQR($datos) {

        $id = $datos['id'];
        $comentario = $datos['comentario'];
        $correos = $datos['correos'];
        $us= $datos['us'];
//        echo var_dump($datos);die();

        $logo = base_url('template/images/logo.png');
        $url = base_url('compras/ver_qr/') . $id;
        $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

        $remitentes = array('tickets@masmetrologia.com');

        $remitentes = array_merge($remitentes, $correos);
        

        $CI = & get_instance();
        $CI->load->library('email');



        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Nuevo comentario de QR</h2>
               <p><b>ID:</b> $idCompleto</p>

               <br>
               <p><b>Comentario de $us: </b> $comentario</p>
               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Nuevo Comentario (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function liberarQR($datos) {

        $id = $datos['id'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        //$cliente = $datos['cliente'];
        $prioridad = $datos['prioridad'];
        $unidad = $datos['unidad'];
        $cantidad = $datos['cantidad'];
        $descripcion = $datos['descripcion'];
        $atributos = json_decode($datos['atributos'], TRUE);
        $correos = $datos['correos'];
        $estatus = $datos['estatus'];
        $comentarios = $datos['comentarios'];

        if($estatus == "LIBERADO")
        {
            $leyendaEstatus = "Se ha liberado QR";
        }
        else if($estatus == "COMPRA APROBADA")
        {
            $leyendaEstatus = "Se ha Aprobado Compra";
        }


        $logo = base_url('template/images/logo.png');
        $url = base_url('compras/ver_qr/') . $id;
        $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

        $remitentes = $correos;
        

        $CI = & get_instance();
        $CI->load->library('email');

        $att = "";
        foreach ($atributos as $key => $value) {
            $att .= "<p><b>" . ucfirst($key) . ":</b> $value</p>";
        }

        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2><font color='green'>$leyendaEstatus</font></h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Prioridad:</b> $prioridad</p>
               <p><b>Unidad:</b> $unidad</p>
               <p><b>Cantidad:</b> $cantidad</p>
               <p><b>Descripción:</b> $descripcion</p>

               $att

               <br>
               <p><b>Notas: </b> $comentarios</p>
               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Requisición de Cotización (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function rechazoQR($datos) {

        $id = $datos['id'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        $cliente = $datos['cliente'];
        $prioridad = $datos['prioridad'];
        $unidad = $datos['unidad'];
        $cantidad = $datos['cantidad'];
        $descripcion = $datos['descripcion'];
        $atributos = json_decode($datos['atributos'], TRUE);
        $correos = $datos['correos'];
        $comentarios = $datos['comentarios'];


        $logo = base_url('template/images/logo.png');
        $url = base_url('compras/ver_qr/') . $id;
        $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

        $remitentes = $correos;
        

        $CI = & get_instance();
        $CI->load->library('email');


        $att = "";
        foreach ($atributos as $key => $value) {
            $att .= "<p><b>" . ucfirst($key) . ":</b> $value</p>";
        }

        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2><font color='red'>Se ha rechazado QR</font></h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Cliente:</b> $cliente</p>
               <p><b>Prioridad:</b> $prioridad</p>
               <p><b>Unidad:</b> $unidad</p>
               <p><b>Cantidad:</b> $cantidad</p>
               <p><b>Descripción:</b> $descripcion</p>
               
               $att

               <br>
               <p><b>Motivos de Rechazo: </b> $comentarios</p>
               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Requisición de Cotización (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function proxMtto200($datos){
      $logo = base_url('template/images/logo.png');

      $auto = $datos['auto'];
      $placas = $datos['placas'];
      $serie = $datos['serie'];
      $kmActual = $datos['kmActual'];
      $proxMtto = $datos['proxMtto'];
      $correo = $datos['correoResponsable'];
      $url = base_url('autos/proximos_mttos');


      $CI = & get_instance();
      $CI->load->library('email');

      $remitentes = array('tickets@masmetrologia.com', $correo);


      $mensaje = <<<EOD

             <img width='400' src='$logo'><br>
             <h1><font face="Arial">SIGA-MAS</font></h1>
             <h2>Notificación de Proximo Mantenimiento</h2>
             <p><b>Auto:</b> $auto</p>
             <p><b>Placas:</b> $placas</p>
             <p><b>Serie:</b> $serie</p>
             <p><b>Kilometraje Actual:</b> $kmActual KM</p>
             <p><b>Proximo Mantenimiento:</b> $proxMtto KM</p>
             <a href='$url' class='btn btn-primary'>Ver Proximos Mantenimientos</a>
EOD;

      $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');


      $CI->email->to($remitentes);

      $CI->email->subject('Proximo Mantenimiento');
      $CI->email->message($mensaje);

      $CI->email->send();
    }

 function entregarPedido($datos){
      $logo = base_url('template/images/logo.png');

      $mail = $datos['pedido'];
      
      $pedidos=$datos['pedidos'];

      
      $correo = $datos['correo'];

      $url = base_url('toolcrib/verPedido/'.$mail);


      $CI = & get_instance();
      $CI->load->library('email');
      

      $remitentes = array('tickets@masmetrologia.com', $correo);
      $m="   <table>
                                    <thead>
                                        <tr class='headings'>
                                            <th class='column-title text-center'>Codigo</th>
                                            <th class='column-title text-center'>Descripción</th>
                                            
                                            <th class='column-title text-center'>Cantidad</th>
                                            <th class='column-title text-center'>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                                     $suma=null;
                                     foreach ($pedidos->result() as $elem) { 
                                        


                                            $m.= "<tr class='headings'><td style='text-align:center; padding:0 40px 0 40px;'>".$elem->codigo."</td>";
                                            $m.="<td style='text-align:center; padding:0 40px 0 40px;' >".$elem->descripcion."</td>";
                                             
                                              $m.= "<td style='text-align:center; padding:0 40px 0 40px;'>".$elem->cantidad."</td>";
                                               $m.= "<td style='text-align:right; padding:0 40px 0 40px;'>$".number_format($elem->total)."</td></tr>";
                                                
                                                $suma +=$elem->total;
                                                
                                           }///echo var_dump($elem->nombre);die();
                                               $m.= "</tbody></table> <p>TOTAL: $".number_format($suma)."</p><script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>";
//echo var_dump($m);die();
                                            
      $mensaje = <<<EOD

             <img width='400' src='$logo'><br>
             <h1><font face="Arial">SIGA-MAS</font></h1>
             <h2>Entrega Pedido Tool Crib</h2>
             <h3>El usuario  $elem->nombre  ha recibido:</h3>
             
             <p><b></p>
             <div>$m</div>  
             <script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script>   
             </script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>                    
             
EOD;

      $CI->email->from('tickets@masmetrologia.com', 'Tool Crib');


      $CI->email->to($remitentes);

      $CI->email->subject('Pedido Tool Crib');
      $CI->email->message($mensaje);

      $CI->email->send();
    }

function asignarQR($datos)
{
    $id = $datos['qr'];
    $correos = $datos['correo'];

    $logo = base_url('template/images/logo.png');
    $url = base_url('compras/ver_qr/') . $id;
    $idCompleto = "QR" . str_pad($id, 6, "0", STR_PAD_LEFT);

    $remitentes = array('tickets@masmetrologia.com', $correos);
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Se te ha asignado el QR</h2>
               <p><b>QR: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver QR</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Asignacion de Requisición de Cotización (QR)');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Solicitud de Pago</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_aceptada($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Solicitud de Pago ACEPTADA</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_programada($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    $date = $datos['date'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Solicitud de Pago PROGRAMADA</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>
               <p><b>Fecha de pago: </b> $date</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_complemento($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Revision de Complemento</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision de complemento');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_pagada($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Solicitud de Pago PAGADA</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_complemento_aceptado($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Complemento de Pago Aceptado</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Se ha enviado Solicitud de Pago para revision');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_cancelado($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Solicitud de Pago Cancelada</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Solicitud de Pago Cancelada');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_rechazar_factura($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Factura Rechazada</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Solicitud de Pago Cancelada');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function correos_solictudes_rechazar_complemento($datos)
{
    $id = $datos['id'];
    $correos = $datos['correo'];
    //echo var_dump($datos);die();

    $logo = base_url('template/images/logo.png');
    $url = base_url('solicitudes_pago/ver_pago/') . $id;
    $idCompleto = "# " . str_pad($id, 6, "0", STR_PAD_LEFT);

    
    
    $CI = & get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Complemento Rechazado</h2>
               <p><b>Solicitud de pago: </b> $idCompleto</p>

               <a href='$url' class='btn btn-primary'>Ver Solicitud de Pago</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($datos['correo']);

        $CI->email->subject('Solicitud de Pago Cancelada');
        $CI->email->message($mensaje);

        $CI->email->send();
    
}
function comentarios_cafeteria($datos) {

        $id = $datos['id'];
        $prefijo = $datos['prefijo'];
        $titulo = $datos['titulo'];
        $fecha = $datos['fecha'];
        $usuario = $datos['usuario'];
        $correo = $datos['correo'];
	$categoria = $datos['categoria'];
        $fecha_incidencia = $datos['fecha_incidencia'];

        $logo = base_url('template/images/logo.png');
        $url = base_url('cafeteria/ver/') . $id;
        $idCompleto = $prefijo . str_pad($id, 6, "0", STR_PAD_LEFT);

        
            $remitentes = array('cfelix@masmetrologia.com','gsolano@masmetrologia.com', 'jc@masmetrologia.com', $correo);
       

        $CI = & get_instance();
        $CI->load->library('email');


        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
               <h2>Se ha creado Nuevo Comentario de Cafeteria</h2>
               <p><b>ID:</b> $idCompleto</p>
               <p><b>Usuario:</b> $usuario</p>
               <p><b>Titulo:</b> $titulo</p>
	       <p><b>Categoria:</b> $categoria</p>
               <p><b>Fecha de Incidencia:</b> $fecha_incidencia</p>
               <a href='$url' class='btn btn-primary'>Ver Comentario</a>
EOD;

        $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');

        $CI->email->to($remitentes);

        $CI->email->subject('Cafeteria');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

function concluir_wo($datos){

          $logo = base_url('template/images/logo.png');

      $mail = $datos['wo'];
      
      $items=$datos['items'];
      $usuario=$datos['usuario'];

      
     // $correo = $datos['correo'];
      //$us_correo = $datos['us_correo'];
      $correos = $datos['correos'];
//echo var_dump($correos);die();
      $url = base_url('ordenes_trabajo/ver_wo/'.$mail);


      $CI = & get_instance();
      $CI->load->library('email');
    
                                            
      $mensaje = <<<EOD

             <img width='400' src='$logo'><br>
             <h1><font face="Arial">SIGA-MAS</font></h1>
             <h2>Work Orders</h2>
             <h3>El usuario: $usuario ha concluido WO#: $mail</h3>
             <a href='$url' class='btn btn-primary'>Ver WO</a>
             <br><br><br>

              
             <script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script>   
             </script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>                    
             
EOD;

      $CI->email->from('tickets@masmetrologia.com', 'Work Oders');


      $CI->email->to($correos);

      $CI->email->subject('Work Orders');
      $CI->email->message($mensaje);

      $CI->email->send();

    }
    function cancelar_wo($datos){

          $logo = base_url('template/images/logo.png');

      $mail = $datos['wo'];
      
      $items=$datos['items'];
      $usuario=$datos['usuario'];

      
      $correo = $datos['correo'];
      $us_correo = $datos['us_correo'];

      $url = base_url('ordenes_trabajo/ver_wo/'.$mail);


      $CI = & get_instance();
      $CI->load->library('email');
      

      $remitentes = array('tickets@masmetrologia.com','jcastaneda@masmetrologia.com', $us_correo, $correo);
      $m="   <table>
                                    <thead>
                                        <tr class='headings'>
                                            <th class='column-title text-center'>Item</th>
                                            <th class='column-title text-center'>estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>";

                                     
                                     foreach ($items as $elem) { 
                                        


                                            $m.= "<tr class='headings'><td style='text-align:center; padding:0 40px 0 40px;'>".$elem->Item_Id."</td>";
                                            $m.="<td style='text-align:center; padding:0 40px 0 40px;' >".$elem->Status_Descripcion."</td>";
                                             
                                                
                                            
                                                
                                           }"</p><script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>";
//echo var_dump($m);die();
                                            
      $mensaje = <<<EOD

             <img width='400' src='$logo'><br>
             <h1><font face="Arial">SIGA-MAS</font></h1>
             <h2>Work Orders</h2>
             <h3>El usuario: $usuario ha cancelado WO#: $mail</h3>
             <a href='$url' class='btn btn-primary'>Ver WO</a>
             <br><br><br>

             <p><b></p>
             <div>$m</div>  
             <script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script>   
             </script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>                    
             
EOD;

      $CI->email->from('tickets@masmetrologia.com', 'Work Oders');


      $CI->email->to($remitentes);

      $CI->email->subject('Work Orders');
      $CI->email->message($mensaje);

      $CI->email->send();

    }

    function cerrar_wo($datos){

          $logo = base_url('template/images/logo.png');

      
      $correos = $datos['correo'];
      $correo_us = $datos['correo_us'];
      $foto=$datos['foto'];
     // $url = base_url('ordenes_trabajo/ver_wo/'.$mail);
//echo var_dump($correo_us);die();
      $remitentes = array($correo_us, $correos);

      $CI = & get_instance();
      $CI->load->library('email');
    
                                            
      $mensaje = <<<EOD

             <img width='400' src='$logo'><br>
             <h1><font face="Arial">SIGA-MAS</font></h1>
             <h2>Work Orders</h2>
             Comprobante de orden e trabajo.
             
             <br><br><br>

              
             <script src='template/vendors/bootstrap/dist/js/bootstrap.min.js'></script>   
             </script> <script src='template/build/js/custom.js'></script>

<script src=template/vendors/iCheck/icheck.min.js></script>

<script src=template/vendors/pnotify/dist/pnotify.js'</script>
<script src='template/vendors/pnotify/dist/pnotify.buttons.js'></script>
<script src='template/vendors/pnotify/dist/pnotify.nonblock.js'></script>                    
             
EOD;

      $CI->email->from('tickets@masmetrologia.com', 'Work Oders');


      $CI->email->to($remitentes);

      $CI->email->subject('Work Orders');
      $CI->email->message($mensaje);
$CI->email->attach($foto);
      $CI->email->send();

    }



}
