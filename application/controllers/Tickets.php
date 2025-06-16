<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends CI_Controller {

   function __construct() {
    parent::__construct();
    // Cargar modelos necesarios
    $this->load->model('tickets_model');
    $this->load->model('conexion_model', 'Conexion');
    // Cargar biblioteca para envíos de correos de tickets
    $this->load->library('correos_tickets');
}

public function index() {
    // Vista principal para generar tickets
    $datos['modulo'] = 'generar';
    $this->load->view('header');
    $this->load->view('tickets', $datos);
}

public function administrar() {
    // Vista para administrar tickets activos
    $datos['modulo'] = 'administrar/activos';
    $this->load->view('header');
    $this->load->view('tickets', $datos);
}

public function mis_tickets() {
    // Vista para mostrar tickets asignados al usuario logueado
    $datos['tickets'] = $this->tickets_model->getMis_tickets($this->session->id);
    $this->load->view('header');
    $this->load->view('tickets/mis_tickets', $datos);
}

public function reportes() {
    // Vista para reportes de tickets
    $datos['modulo'] = 'reporte';
    $this->load->view('header');
    $this->load->view('tickets', $datos);
}

public function cafeteria() {
    // Vista para módulo cafetería
    $datos['modulo'] = 'cafeteria';
    $this->load->view('header');
    $this->load->view('tickets', $datos);
}

function dashboard(){
    // Vista del dashboard de tickets
    $this->load->view('header');
    $this->load->view('tickets/dashboard');
}

function camaras(){
    // Vista para cámaras relacionadas a tickets
    $this->load->view('header');
    $this->load->view('tickets/camaras');
}

    ///////// F U N C I O N E S  A J A X ///////

function ajax_getTicketsSolucionados() {
    // Obtener datos del POST
    $user = $this->input->post('usuario');
    $tipo = $this->input->post('tipo');

    // Determinar tabla y campo usuario según el tipo
    $tabla = null;
    $usuario = 'usuario';

    if ($tipo == 'IT') {
        $tabla = 'tickets_sistemas';
    } else if ($tipo == 'AT') {
        $tabla = 'tickets_autos';
    } else if ($tipo == 'ED') {
        $tabla = 'tickets_edificio';
    } else if ($tipo == 'cafeteria') {
        $tabla = 'comentarios_cafeteria';
        $usuario = 'id_user';
    }

    // Consultar el conteo de tickets solucionados del usuario
    $res = $this->Conexion->consultar("SELECT count(*) as Conteo FROM $tabla where $usuario = '$user' and estatus = 'SOLUCIONADO'", TRUE);
    if ($res) {
        echo json_encode($res);
    }
}

function ajax_sendTicketNotification() {
    // Consulta tickets pendientes por tipo y estatus (ABIERTO o EN CURSO)
    $query = "SELECT 'IT' as tipo, T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO'"
        . " union "
        . "SELECT 'IT' as tipo, T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO'"
        . " union "
        . "SELECT 'AT', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_autos T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO'"
        . " union "
        . "SELECT 'AT', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_autos T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO'"
        . " union "
        . "SELECT 'ED', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_edificio T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO'"
        . " union "
        . "SELECT 'ED', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_edificio T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO' order by usuario";

    $arr = array();
    $res = $this->Conexion->consultar($query);

    // Agrupar tickets por usuario para enviar resumen por correo
    if ($res) {
        foreach ($res as $key => $value) {
            if (!isset($arr[$value->usuario])) {
                $arr[$value->usuario]['nombre'] = $value->User;
                $arr[$value->usuario]['correo'] = $value->correo;
                $arr[$value->usuario]['tickets'] = array();
            }
            array_push($arr[$value->usuario]['tickets'], $value->tipo . str_pad($value->id, 6, "0", STR_PAD_LEFT));
        }
    }

    // Enviar notificaciones por correo de los tickets pendientes
    $this->correos_tickets->ticketsPendientes($arr);
}

function ajax_getTicktesIT() {
    // Obtener el período desde POST
    $periodo_it = $this->input->post('periodo_it');
    $periodo = null;

    // Si no es "TODO", filtramos por fecha
    if ($periodo_it != 'TODO') {
        $periodo = ' and fecha like "%' . $periodo_it . '%"';
    }

    // Consulta de resumen por estatus en tickets_sistemas
    $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "ABIERTO"' . $periodo . ') as Abiertos, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "ABIERTO"' . $periodo . ') as ultAbiertos, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "EN CURSO"' . $periodo . ') as Curso, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "EN CURSO"' . $periodo . ') as ultCurso, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "EN REVISION"' . $periodo . ') as Revision, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "EN REVISION"' . $periodo . ') as ultRevision, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "DETENIDO"' . $periodo . ') as Detenido, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "DETENIDO"' . $periodo . ') as ultDetenido, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "SOLUCIONADO"' . $periodo . ') as Solucionado, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "SOLUCIONADO"' . $periodo . ') as ultSolucionado, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "CERRADO"' . $periodo . ') as Cerrado, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "CERRADO"' . $periodo . ') as ultCerrado, 
        (SELECT count(*) FROM tickets_sistemas where estatus = "CANCELADO"' . $periodo . ') as Cancelado, 
        (SELECT max(fecha) FROM tickets_sistemas where estatus = "CANCELADO"' . $periodo . ') as ultCancelado 
        FROM tickets_sistemas where 1=1 ' . $periodo;

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

function ajax_getTicktesAutos() {
    // Obtener el período desde POST
    $periodo_at = $this->input->post('periodo_at');
    $periodo = null;

    // Filtro por fecha
    if ($periodo_at != 'TODO') {
        $periodo = ' and fecha like "%' . $periodo_at . '%"';
    }

    // Consulta de resumen por estatus en tickets_autos
    $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM tickets_autos where estatus = "ABIERTO"' . $periodo . ') as Abiertos, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "ABIERTO"' . $periodo . ') as ultAbiertos, 
        (SELECT count(*) FROM tickets_autos where estatus = "EN CURSO"' . $periodo . ') as Curso, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "EN CURSO"' . $periodo . ') as ultCurso, 
        (SELECT count(*) FROM tickets_autos where estatus = "DETENIDO"' . $periodo . ') as Detenido, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "DETENIDO"' . $periodo . ') as ultDetenido, 
        (SELECT count(*) FROM tickets_autos where estatus = "SOLUCIONADO"' . $periodo . ') as Solucionado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "SOLUCIONADO"' . $periodo . ') as ultSolucionado, 
        (SELECT count(*) FROM tickets_autos where estatus = "CERRADO"' . $periodo . ') as Cerrado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CERRADO"' . $periodo . ') as ultCerrado, 
        (SELECT count(*) FROM tickets_autos where estatus = "CANCELADO"' . $periodo . ') as Cancelado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CANCELADO"' . $periodo . ') as ultCancelado 
        FROM tickets_autos where 1=1 ' . $periodo;

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

    // Función para obtener estadísticas de tickets de mantenimiento de edificio
function ajax_getTicktesEdificio() {
    // Obtener el periodo enviado por POST
    $periodo_ed = $this->input->post('periodo_ed');
    $periodo = null;

    // Si se seleccionó un periodo específico, se agrega condición para filtrar por fecha
    if ($periodo_ed != 'TODO') {
        $periodo = ' and fecha like "%' . $periodo_ed . '%"';
    }

    // Consulta SQL para obtener el total y las cantidades por estatus, además de la última fecha por cada estatus
    $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM tickets_edificio where estatus = "ABIERTO"' . $periodo . ') as Abiertos, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "ABIERTO"' . $periodo . ') as ultAbiertos, 
        (SELECT count(*) FROM tickets_edificio where estatus = "EN CURSO"' . $periodo . ') as Curso, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "EN CURSO"' . $periodo . ') as ultCurso, 
        (SELECT count(*) FROM tickets_edificio where estatus = "DETENIDO"' . $periodo . ') as Detenido, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "DETENIDO"' . $periodo . ') as ultDetenido, 
        (SELECT count(*) FROM tickets_edificio where estatus = "SOLUCIONADO"' . $periodo . ') as Solucionado, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "SOLUCIONADO"' . $periodo . ') as ultSolucionado, 
        (SELECT count(*) FROM tickets_edificio where estatus = "CERRADO"' . $periodo . ') as Cerrado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CERRADO"' . $periodo . ') as ultCerrado, -- bug: consulta desde otra tabla
        (SELECT count(*) FROM tickets_edificio where estatus = "CANCELADO"' . $periodo . ') as Cancelado, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "CANCELADO"' . $periodo . ') as ultCancelado 
        FROM tickets_edificio where 1=1 ' . $periodo;

    // Ejecuta la consulta y devuelve el resultado en formato JSON
    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

// Función para obtener estadísticas de comentarios o tickets de cafetería
function ajax_getTicktesCafeteria() {
    $periodo_ca = $this->input->post('periodo_ca');
    $periodo = null;

    // Filtro por periodo si se indicó alguno
    if ($periodo_ca != 'TODO') {
        $periodo = ' and fecha like "%' . $periodo_ca . '%"';
    }

    // Consulta SQL similar a las demás, adaptada para comentarios_cafeteria
    $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "ABIERTO"' . $periodo . ') as Abiertos, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "ABIERTO"' . $periodo . ') as ultAbiertos, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "EN CURSO"' . $periodo . ') as Curso, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "EN CURSO"' . $periodo . ') as ultCurso, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "DETENIDO"' . $periodo . ') as Detenido, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "DETENIDO"' . $periodo . ') as ultDetenido, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "SOLUCIONADO"' . $periodo . ') as Solucionado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "SOLUCIONADO"' . $periodo . ') as ultSolucionado, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "CERRADO"' . $periodo . ') as Cerrado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "CERRADO"' . $periodo . ') as ultCerrado, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "CANCELADO"' . $periodo . ') as Cancelado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "CANCELADO"' . $periodo . ') as ultCancelado 
        FROM comentarios_cafeteria where 1=1' . $periodo;

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

// Función para obtener la lista de cámaras registradas
function ajax_getCamaras() {
    $res = $this->Conexion->consultar("SELECT * FROM camaras");
    if ($res) {
        echo json_encode($res);
    }
}

// Función para registrar una nueva cámara
function registrar_camara() {
    $data = json_decode($this->input->post('data')); // decodifica los datos JSON enviados
    $this->Conexion->insertar('camaras', $data);     // inserta en la tabla 'camaras'
}


}
