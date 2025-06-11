<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador para pruebas de envío de correo
class Correo extends CI_Controller {

    // Constructor vacío
    function __construct() {
        parent::__construct();
    }

    // Prueba de envío de correo usando librería 'correos'
    function index() {
        $this->load->library('correos');                 // Carga librería personalizada
        $datos['titulo'] = 'Mantenimiento de Rutina';    // Título del ticket
        $datos['id'] = 'AT000023';                       // ID del ticket
        $datos['prefijo'] = 'AT';                        // Prefijo del ticket
        $datos['fecha'] = '17-Ene-2019';                 // Fecha de creación
        $datos['usuario'] = 'ALEJANDRO ORTIZ';           // Usuario responsable
        $this->correos->creacionTicket($datos);          // Envía correo de creación de ticket
    }

    // Prueba de múltiples configuraciones SMTP y envío con MailJet
    function prueba() {
        // Configuración para Gmail (no usada)
        $configGOOGLE = Array(
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => '465',
            'smtp_user' => 'aleksrocknlove@gmail.com',
            'smtp_pass' => 'Alekssdr14',
            'mailtype'  => 'html',
            'newline'   => '\r\n',
            'crlf'      => '\r\n',
            'protocol'  => 'smtp',
        );

        // Configuración para GoDaddy/masmetrologia (no usada)
        $configMAS = Array(
            'smtp_host' => 'smtpout.secureserver.net',
            'smtp_port' => '80',
            'smtp_user' => 'tickets@masmetrologia.mx',
            'smtp_pass' => 'Soporte2018@',
            'mailtype'  => 'html',
            'newline'   => '\r\n',
            'crlf'      => '\r\n',
            'protocol'  => 'smtp',
        );

        // Configuración para MailJet (usada en esta prueba)
        $configMJET = Array(
            'smtp_host' => 'in-v3.mailjet.com',
            'smtp_port' => '587',
            'smtp_user' => '1f55d1e5c5da7c2c10ee96e0a5d166af',
            'smtp_pass' => '0c68495c162a80a883412b1106045cb3',
            'mailtype'  => 'html',
            'newline'   => '\r\n',
            'crlf'      => '\r\n',
            'protocol'  => 'smtp',
        );

        $this->load->library('email', $configMJET); // Usa configuración de MailJet

        $this->email->from('aortiz@masmetrologia.com', 'PRUEBA MAIL-JET'); // Remitente
        $this->email->to('alejandro_ortiz426@hotmail.com');                // Destinatario

        $logo = base_url('template/images/logo.png');

        // Mensaje con logotipo (comentado)
        $mensaje = <<<EOD
        <a href='#'><img width='400' src='$logo'><br></a>
        <h1><font face="Arial">SIGA-MAS</font></h1>
EOD;

        $this->email->subject('SIGA MAILJET');
        //$this->email->message($mensaje); // Envío de mensaje con HTML
        $this->email->message("PRUEBA MAIL-JET"); // Mensaje de prueba (texto simple)
        $this->email->send();

        echo $this->email->print_debugger(); // Muestra resultado del envío
    }

    // Envío de correo con formato de ticket de servicio
    public function ticket($datos) {
        $logo = base_url('template/images/logo.png');   // Ruta del logotipo
        $titulo = $datos['titulo'];                     // Título del ticket

        // Cuerpo del correo en HTML
        $mensaje = <<<EOD
               <img width='500' src='$logo'>
               <h1>Ticket de Servicio</h1>
               <h3>Titulo: $titulo</h3>
EOD;

        $this->load->library('email');

        $this->email->from('alejandro_ortiz426@hotmail.com', 'Aleks Ortiz'); // Remitente
        $this->email->to('aortiz@masmetrologia.com');                         // Destinatario

        $this->email->subject('EXIaaaaaTO'); // Asunto del correo
        $this->email->message($mensaje);     // Cuerpo HTML

        $this->email->send(); // Enviar

        echo $this->email->print_debugger(); // Muestra resultado del envío
    }
}
