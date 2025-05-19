<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Correos_facturacion {


    function solicitud($data) {
        $CI = & get_instance();
        $CI->load->library('email');

        $logo = base_url('template/images/logo.png');
        $url = base_url('facturas/ver_solicitud/') . $data->id;
        $idCompleto = str_pad($data->id, 6, "0", STR_PAD_LEFT);

        $mensaje = "
            <img width='400' src='$logo'><br>
            <h1><font face='Times'>SIGA-MAS</font></h1>
            <h2>Solicitud de Factura</h2>
            <p><b>ID:</b> $idCompleto</p>
            <p><b>Requisitor:</b> $data->User</p>
            <p><b>Cliente:</b> $data->Client</p>
            <p><b>Contacto:</b> $data->Contact</p>
            <p><b>Notas:</b> $data->notas</p>
            <br>
            <a href='$url' class='btn btn-primary'>Ver Solicitud</a>";

        $CI->email->from('tickets@masmetrologia.mx', 'Facturación SIGA-MAS');
        $CI->email->to($data->correos);

        $CI->email->subject('Solicitud de Factura');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function editar_solicitud($data) {
        $CI = & get_instance();
        $CI->load->library('email');
//echo var_dump($data);die();
        $logo = base_url('template/images/logo.png');
        $url = base_url('facturas/ver_solicitud/') . $data['id'];
        $idCompleto = str_pad( $data['id'], 6, "0", STR_PAD_LEFT);
        $estatus=$data['estatus_factura'];
        $comentarios=$data['comentario'];
        $client=$data['Client'];
        $Contact=$data['Contact'];
        $User=$data['User'];
        //$comentario = empty($data['comentario']) ? "<p><b>Nuevo Estatus:</b> $estatus</p>" : "<p>$comentarios</p>";

        $mensaje = "
            <img width='400' src='$logo'><br>
            <h1><font face='Times'>SIGA-MAS</font></h1>
            <h2>Solicitud de Factura</h2>
            <p><b>ID:</b> $idCompleto</p>
            <p><b>Requisitor:</b> $User</p>
            <p><b>Cliente:</b> $client</p>
            <p><b>Contacto:</b> $Contact</p>
            <br>
  
            <br>
            <a href='$url' class='btn btn-primary'>Ver Solicitud</a>";

        $CI->email->from('tickets@masmetrologia.mx', 'Facturación SIGA-MAS');
        $CI->email->to($data['correos']);

        $CI->email->subject('Solicitud de Factura');
        $CI->email->message($mensaje);

        $CI->email->send();
    }

    function enviarCorreo($datos) {
        $CI = & get_instance();

        $configMJET = Array(
            'charset' => 'utf-8',
            'smtp_host' => 'smtp.office365.com',
            'smtp_port' => '587',
            'smtp_user' => 'tickets@masmetrologia.com',
            'smtp_pass' => 'M-5Ghu3L(s',
            'mailtype' => 'html',
            'newline' => '\r\n',
            'crlf' => '\r\n',
            'protocol' => 'tls',
        );

        //$config['smtp_user'] = $CI->session->correo;
        //$config['smtp_pass'] = $CI->session->password_correo;
        
        $para = explode (",", $datos['para']);
        $cc = explode (",", $datos['cc']);
               
        $CI->load->library('email', $configMJET);

        $CI->email->from($CI->session->correo, 'Facturación SIGA-MAS');

        $CI->email->to($para);
        $CI->email->cc($cc);

        $CI->email->subject($datos['subject']);
        $CI->email->message($datos['body']);

        /*foreach ($datos['archivos'] as $i => $value) {
            $CI->email->attach($value, 'attachment', $datos['campos'][$i]);
        }*/

        $CI->email->send();
        echo $CI->email->print_debugger();
    }
    function archivos_facturacion($datos) {
//echo var_dump($datos['certificados']);die();
    
      $logo = base_url('template/images/logo.png');
    
      $pod=$datos['pod'];
      $para = explode (",", $datos['para']);
        $cc = explode (",", $datos['cc']);

      $CI = & get_instance();
      $CI->load->library('email');
      

        $CI->email->from('tickets@masmetrologia.mx', 'Soporte SIGA-MAS');


        $CI->email->to($para);
        $CI->email->cc($cc);

        $CI->email->subject($datos['subject']);
        $CI->email->message($datos['body']);
        
       foreach ($datos['archivos'] as $i => $value) {
        //echo substr($value, -13);die();
        if (substr($value, -3) == "xml") {
            $name =$datos['xmlname'];
            $CI->email->attach($value, $disposition='attachment', $newname = $name);
        }
        else if (substr($value, -13) == "f_factura.pdf") {
            $name =$datos['pdfname'];
            $CI->email->attach($value, $disposition='attachment', $newname = $name);
        }else if (substr($value, -18) == "f_orden_compra.pdf") {
            $name ="PO- ".$datos['po'].'.pdf';
            $CI->email->attach($value, $disposition='attachment', $newname = $name);
        }else {
    $CI->email->attach($value);
            

        }
        
            
        }
      foreach ($datos['certificados'] as $value) {
try {
    $documentoX=$value;
        if (is_numeric(substr($documentoX, 0, 6))) { // Valida que los 6 primeros caracteres sean numericos.
            $nDato = substr($documentoX, 0, 6);
            $nFolder = intval($nDato / 10000) * 10000; // Para definir la carpeta
           // $nArchivo ="http://192.168.6.13/QAPUBLIC/Certificados/" . $nFolder . "/" . $documentoX . ".pdf"; 
           $nArchivo = $_SERVER['DOCUMENT_ROOT']."/windowssync/". $documentoX . ".pdf"; 
        // echo $nArchivo;die();
            // Carpeta de produccion
            if (file_exists($nArchivo)) {
                $CI->email->attach($nArchivo);
            } else {
                echo "El archivo " . $documentoX . " no existe en el servidor"; // Cambiado de MsgBox a echo para PHP
               // return false;
            }

        }
    } catch (Exception $e) {
        echo $e->getMessage() . " Error: " . $e->getCode(); // Cambiado de MsgBox a echo para PHP
    }    
        }
       $CI->email->attach($pod);
       $CI->email->send();
    }
function test($datos) {
      
      $logo = base_url('template/images/logo.png');
      /*$accion=$datos['accion'];*/
      $remitentes='jcastaneda@masmetrologia.com';
      $cal=$datos['pod'];
      //$nombre=$datos['nombre'];

      $CI = & get_instance();
      $CI->load->library('email');
        $mensaje = <<<EOD

               <img width='400' src='$logo'><br>
               <h1><font face="Arial">SIGA-MAS</font></h1>
                <p><b>Evento:</b> </p>

EOD;

        $CI->email->from('tickets@masmetrologia.mx', 'Soporte SIGA-MAS');


        $CI->email->to($remitentes);

        $CI->email->subject('Evento de Seguimiento');
        $CI->email->message($mensaje);
        
       $CI->email->attach($cal);
       $CI->email->send();
    }


}
