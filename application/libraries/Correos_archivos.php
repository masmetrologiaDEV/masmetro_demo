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
    $pdf = $datos['pdf'];
    $nombre = $datos['nombre'];
    $pdf_nombre = $datos['pdf_nombre'];
    

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
     $CI->email->attach($pdf, 'attachment', $pdf_nombre, 'application/pdf');

    // Enviar correo y verificar errores
    if (!$CI->email->send()) {
        // Mostrar errores si el envío falla
        echo "Error al enviar el correo:<br>";
        echo $CI->email->print_debugger(['headers', 'subject', 'body']);
    } else {
        echo "Correo enviado correctamente a: " . implode(', ', $remitentes);
    }
}

function Solicitar_Autorización($datos) {

    $asunto=$datos['asunto'];
    $pdf = $datos['pdf'];
    $pdf_nombre = $datos['pdf_nombre'];
    $firma=$datos['firma'];
    $firma_base64 = base64_encode(file_get_contents($firma));
    $cuerpo = $datos['body'];
    $contacto= $datos['contacto']; 
    $responsable= $datos['responsable'];
    $creador= $datos['creador'];
    $correo_cc= $datos['correo_cc'];
    
    $contacto = isset($datos['contacto']) ? $datos['contacto'] : null;
    $correo_cc = isset($datos['correo_cc']) ? $datos['correo_cc'] : null;
    
    // Filtrar remitentes válidos (no nulos ni vacíos)
    $remitentes = array_filter([$contacto, $responsable, $creador]);


    $CI = &get_instance();
    $CI->load->library('email');

    $mensaje = <<<EOD
<html>
<head>
    <meta charset="UTF-8">
    <title>$asunto</title>
</head>
<body style="style="background: white;font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div>
            $cuerpo
        

        <!-- Redes Sociales -->
        <div>
             <a href="https://masmetrologia.com/" target="_blank">
                    <img src="data:image/png;base64,$firma_base64" style="width: 400px; display: block; margin: auto;" alt="Firma">
                </a><br>
            <a href="https://www.facebook.com/masmetrologia" target="_blank">
                <img src="https://i.imgur.com/UYxrXXu.png" alt="Facebook">
            </a>
            <a href="https://www.instagram.com/masmetrologia" target="_blank">
                <img src="https://i.imgur.com/C8S10iF.png" alt="Instagram">
            </a>
            <a href="https://www.linkedin.com/company/masmetrologia/about/" target="_blank">
                <img src="https://i.imgur.com/lQhkAdl.png" alt="LinkedIn">
            </a>
            <a href="https://www.youtube.com/@masmetrologia" target="_blank">
                <img src="https://i.imgur.com/2S2TtOh.png" alt="YouTube">
            </a>
            <a href="https://maps.app.goo.gl/dgWmWTQrPGkLaC5p6" target="_blank">
                <img src="https://i.imgur.com/gGg2lEB.png" alt="Google Maps">
            </a>
        </div>

        <p style="font-size: 12px; color: #777; margin-top: 20px;">
            © 2025 Mas Metrología - Todos los derechos reservados.
        </p>
    </div>
</body>
</html>
EOD;

    $CI->email->from('tickets@masmetrologia.com', 'Soporte SIGA-MAS');
    $CI->email->to($remitentes); // Enviar a múltiples destinatarios
    $CI->email->cc($correo_cc); // Enviar a múltiples destinatarios

    $CI->email->subject($asunto);
    $CI->email->message($mensaje);

    // Adjuntar archivo
     $CI->email->attach($pdf, 'attachment', $pdf_nombre, 'application/pdf');

    // Enviar correo y verificar errores
    if (!$CI->email->send()) {
        // Mostrar errores si el envío falla
        echo "Error al enviar el correo:<br>";
        echo $CI->email->print_debugger(['headers', 'subject', 'body']);
    } else {
        echo "Correo enviado correctamente a: " . implode(', ', $remitentes);
        echo "Ruta de la firma: " . $firma;

    }
}


}
