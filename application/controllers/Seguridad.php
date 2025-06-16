<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Seguridad extends CI_Controller {

    function __construct() {
    parent::__construct();
}

function vencimiento_password() {
    // Vista para advertencia de vencimiento de contraseña
    $this->load->view('seguridad/password');
}

function recuperacion_password() {
    // Vista para recuperación de contraseña mediante correo
    $this->load->view('seguridad/recuperacion_password');
}

function reiniciar_password($link) {
    // Validación del enlace de recuperación de contraseña y reinicio de sesión temporal
    date_default_timezone_set('America/Chihuahua');
    $res = $this->Conexion->consultar("SELECT R.vencimiento, U.id, concat(U.nombre, ' ', U.paterno) as Name, U.activo, U.password from recuperacion_password R inner join usuarios U on U.id = R.usuario where R.link = '$link'", TRUE);

    $today = date("y-m-d H:i", strtotime("now"));
    $fecha = date("y-m-d H:i", strtotime($res->vencimiento));

    if ($today > $fecha) {
        echo "LIGA VENCIDA"; // El enlace ya expiró
    } else {
        // Se establece sesión temporal para permitir el cambio de contraseña
        $this->session->id = $res->id;
        $this->session->activo = $res->activo;
        $this->session->nombre = $res->Name;
        $this->session->password = $res->password;
        $this->session->vencimiento_password = date("y-m-d H:i", strtotime("last month"));

        redirect(base_url('inicio'));
    }
}

function ajax_changePass() {
    // Cambia la contraseña del usuario si es distinta de la anterior
    $pass = sha1($this->input->post('password'));
    if (strtoupper($this->session->password) != strtoupper($pass)) {
        $this->session->sess_destroy(); // Cierra sesión para evitar que continúe con sesión vieja
        echo $this->Conexion->modificar('usuarios', array('password' => $pass), array('vencimiento_password' => 'CURRENT_TIMESTAMP() + INTERVAL 30 day'), array('id' => $this->session->id));
    }
}

    function ajax_recoverPass() {
    // Proceso de recuperación de contraseña: valida usuario, genera link único, guarda y envía correo
    $noempleado = $this->input->post('noempleado');
    $correo = $this->input->post('correo');

    $res = $this->Conexion->consultar("SELECT id, correo, concat(nombre, ' ', paterno) as Name from usuarios where activo = 1 and no_empleado='$noempleado' and correo = '$correo' limit 1", TRUE);
    if ($res) {
        $data['usuario'] = $res->id;
        $data['link'] = uniqid();
        $func['fecha'] = "CURRENT_TIMESTAMP()";
        $func['vencimiento'] = "CURRENT_TIMESTAMP() + interval 6 hour";
        $this->Conexion->insertar('recuperacion_password', $data, $func);

        $this->load->library('email');
        $logo = base_url('template/images/logo.png');
        $url = base_url('seguridad/reiniciar_password/') . $data['link'];

        $mensaje = "
            <img width='400' src='$logo'><br>
            <h1><font face='Times'>SIGA-MAS</font></h1>
            <h2>Recuperación de Contraseña</h2>
            <p>Ingresa a la liga debajo para reestablecer tu contraseña</p>
            <br>
            <a href='$url' class='btn btn-primary'>Reestablecer contraseña</a>";

        $this->email->from('tickets@masmetrologia.mx', 'Soporte SIGA-MAS');
        $this->email->to($res->correo);
        $this->email->subject('Recuperación de Contraseña');
        $this->email->message($mensaje);
        $this->email->send();

        echo json_encode($res);
    }
}

function ajax_md5() {
    // Devuelve el hash MD5 del texto recibido por POST
    echo md5($this->input->post('text'));
}

function md5($texto) {
    // Devuelve el hash MD5 del texto recibido por parámetro
    echo md5($texto);
}

}
