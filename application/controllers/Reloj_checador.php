<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Reloj_checador extends CI_Controller {

    function __construct() {
    parent::__construct();
    $this->load->model('reloj_model'); // Modelo que maneja la lógica del checador
}

function index() {
    // Vista principal del checador con valores predeterminados
    $data['usuario'] = 0;
    $data['idusuario'] = 0;
    $idusuario = $this->session->id;
    if (isset($idusuario)) {
        $data['idusuario'] = $idusuario;
    }
    $this->load->view('reloj_checador/checador', $data);
}

function checar() {
    // Vista del checador cuando ya se tiene un usuario en sesión
    $data['usuario'] = $this->session->no_empleado;
    $data['idusuario'] = $this->session->id;
    $this->load->view('reloj_checador/checador', $data);
}

function reporte() {
    // Vista del reporte semanal del checador
    $datos['semanaActual'] = $this->aos_funciones->no_semana(); // Obtiene número de semana actual
    $this->load->view('header');
    $this->load->view('reloj_checador/reporte', $datos);
}

function comprobar_usuario_ajax() {
    // Verifica si el número de empleado ingresado existe
    $no_empleado = strtoupper(trim($this->input->post('no_empleado')));
    $user = $this->reloj_model->getUsuario($no_empleado);
    if ($user) {
        echo json_encode($user);
    }
}

function checar_ajax() {
    // Registra una checada de entrada o salida
    $this->load->model('usuarios_model');
    $data['foto'] = $this->input->post('src');
    $data['usuario'] = $this->input->post('usuario');
    $data['tipo'] = $this->input->post('tipo');

    $res = $this->reloj_model->checarEntrada($data); // Guarda la checada en la base de datos
    if ($res) {
        $checada = $this->reloj_model->getEntrada($res); // Obtiene la hora registrada
        $this->usuarios_model->ultimaSesion($data['usuario']); // Actualiza última sesión del usuario
        $data['hora'] = $checada->hora;
        echo json_encode($data);
    } else {
        echo "";
    }
}

function checadas_ajax() {
    // Devuelve las checadas del usuario actual
    $idusuario = $this->input->post('idusuario');
    $res = $this->reloj_model->getChecadas($idusuario);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function reporte_ajax() {
    // Genera el reporte de checadas por semana/año
    $year = $this->input->post('year');
    $semana = $this->input->post('semana');

    $fecha = $this->aos_funciones->fecha_semana($year, $semana, $semana); // Devuelve fechas inicio/fin
    $data['inicio'] = $fecha[0];
    $data['final'] = $fecha[1];

    $res = $this->reloj_model->getReporte($data);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

}
