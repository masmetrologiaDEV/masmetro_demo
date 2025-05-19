<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Correos_archivos {

    function evento_cotizaciones($datos) {

        
    $logo = base_url('template/images/logo.png');
    $accion = $datos['accion'];
    $cuerpo = $datos['cuerpo'];
    $responsable = $datos['correo'];
    $correoResponsable = $datos['correoResponsable'];
    $contacto = isset($datos['contacto']) ? $datos['contacto'] : null;
    
    // Filtrar remitentes válidos (no nulos ni vacíos)
    $remitentes = array_filter([$contacto, $responsable, $correoResponsable]);

    $cal = $datos['cal'];
    $nombre = $datos['nombre'];

    $CI = &get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD
        <img width='400' src='$logo'><br>
        <h1><font face="Arial">SIGA-MAS</font></h1>
        <p><b>$cuerpo</b> </p>
        <p><b>Acción de seguimiento:</b> $accion</p>
EOD;

    $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');
    $CI->email->to($remitentes); // Enviar a múltiples destinatarios

    $CI->email->subject('Evento de Seguimiento');
    $CI->email->message($mensaje);

    // Adjuntar archivo
     $CI->email->attach($cal, $disposition='attachment', $newname = $nombre, $mime='text/calendar');

    // Enviar correo y verificar errores
    if (!$CI->email->send()) {
        // Mostrar errores si el envío falla
        echo "Error al enviar el correo:<br>";
        echo $CI->email->print_debugger(['headers', 'subject', 'body']);
    } else {
        echo "Correo enviado correctamente a: " . implode(', ', $remitentes);
    }
}


}
