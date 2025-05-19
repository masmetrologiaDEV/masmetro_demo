<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('tickets_model');
        $this->load->model('conexion_model', 'Conexion');
        $this->load->library('correos_tickets');
    }

    public function index() {
        $datos['modulo'] = 'generar';
        $this->load->view('header');
        $this->load->view('tickets', $datos);
    }

    public function administrar() {
        $datos['modulo'] = 'administrar/activos';
        $this->load->view('header');
        $this->load->view('tickets', $datos);
    }

    public function mis_tickets() {
        $datos['tickets'] = $this->tickets_model->getMis_tickets($this->session->id);
        $this->load->view('header');
        $this->load->view('tickets/mis_tickets', $datos);
    }

    public function reportes() {
        $datos['modulo'] = 'reporte';
        $this->load->view('header');
        $this->load->view('tickets', $datos);
    }
    public function cafeteria() {
        $datos['modulo'] = 'cafeteria';
        $this->load->view('header');
        $this->load->view('tickets', $datos);
    }
function dashboard(){
        $this->load->view('header');
        $this->load->view('tickets/dashboard');
    }
function camaras(){
        $this->load->view('header');
        $this->load->view('tickets/camaras');
    }



    /////// F U N C I O N E S  A J A X ///////

    function ajax_getTicketsSolucionados(){
        $user = $this->input->post('usuario');
        $res = $this->Conexion->consultar("SELECT count(*) as Conteo FROM tickets_sistemas where usuario = '$user' and estatus = 'SOLUCIONADO'", TRUE);
        if($res)
        {
            echo json_encode($res);   
        }
    }

    function ajax_sendTicketNotification(){
/*        $query = "SELECT 'IT' as tipo, T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.estatus = 'SOLUCIONADO'"
        . " union "
        . "SELECT 'AT', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_autos T inner join usuarios U on U.id = T.usuario where T.estatus = 'SOLUCIONADO'"
        . " union "
        . "SELECT 'ED', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_edificio T inner join usuarios U on U.id = T.usuario where T.estatus = 'SOLUCIONADO' order by usuario";*/

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
        if($res)
        {
            foreach ($res as $key => $value) {
                if(!isset($arr[$value->usuario]))
                {
                    $arr[$value->usuario]['nombre'] = $value->User;
                    $arr[$value->usuario]['correo'] = $value->correo;
                    $arr[$value->usuario]['tickets'] = array();
                }
                array_push($arr[$value->usuario]['tickets'], $value->tipo . str_pad($value->id, 6, "0", STR_PAD_LEFT));
            }
        }

        $this->correos_tickets->ticketsPendientes($arr);

        

    }
function ajax_getTicktesIT(){
        $query = 'SELECT count(*) as Total, (SELECT count(*) FROM tickets_sistemas where estatus = "ABIERTO") as Abiertos, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "ABIERTO") as ultAbiertos, 
                    (SELECT count(*) FROM tickets_sistemas where estatus = "EN CURSO") as Curso, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "EN CURSO") as ultCurso, 
		    (SELECT count(*) FROM tickets_sistemas where estatus = "EN REVISION") as Revision, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "EN REVISION") as ultRevision,
                    (SELECT count(*) FROM tickets_sistemas where estatus = "DETENIDO") as Detenido, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "DETENIDO") as ultDetenido, 
                    (SELECT count(*) FROM tickets_sistemas where estatus = "SOLUCIONADO") as Solucionado, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "SOLUCIONADO") as ultSolucionado, 
                    (SELECT count(*) FROM tickets_sistemas where estatus = "CERRADO") as Cerrado, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "CERRADO") as ultCerrado, 
                    (SELECT count(*) FROM tickets_sistemas where estatus = "CANCELADO") as Cancelado, 
                    (SELECT max(fecha) FROM tickets_sistemas where estatus = "CANCELADO") as ultCancelado FROM tickets_sistemas';
        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }
     function ajax_getTicktesAutos(){
        $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM tickets_autos where estatus = "ABIERTO") as Abiertos, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "ABIERTO") as ultAbiertos, 
        (SELECT count(*) FROM tickets_autos where estatus = "EN CURSO") as Curso, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "EN CURSO") as ultCurso, 
        (SELECT count(*) FROM tickets_autos where estatus = "DETENIDO") as Detenido, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "DETENIDO") as ultDetenido,
        (SELECT count(*) FROM tickets_autos where estatus = "SOLUCIONADO") as Solucionado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "SOLUCIONADO") as ultSolucionado, 
        (SELECT count(*) FROM tickets_autos where estatus = "CERRADO") as Cerrado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CERRADO") as ultCerrado, 
        (SELECT count(*) FROM tickets_autos where estatus = "CANCELADO") as Cancelado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CANCELADO") as ultCancelado FROM tickets_autos';
        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }
    function ajax_getTicktesEdificio(){
        $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM tickets_edificio where estatus = "ABIERTO") as Abiertos, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "ABIERTO") as ultAbiertos, 
        (SELECT count(*) FROM tickets_edificio where estatus = "EN CURSO") as Curso, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "EN CURSO") as ultCurso, 
        (SELECT count(*) FROM tickets_edificio where estatus = "DETENIDO") as Detenido, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "DETENIDO") as ultDetenido, 
        (SELECT count(*) FROM tickets_edificio where estatus = "SOLUCIONADO") as Solucionado, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "SOLUCIONADO") as ultSolucionado, 
        (SELECT count(*) FROM tickets_edificio where estatus = "CERRADO") as Cerrado, 
        (SELECT max(fecha) FROM tickets_autos where estatus = "CERRADO") as ultCerrado, 
        (SELECT count(*) FROM tickets_edificio where estatus = "CANCELADO") as Cancelado, 
        (SELECT max(fecha) FROM tickets_edificio where estatus = "CANCELADO") as ultCancelado FROM tickets_edificio';
        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }
    function ajax_getTicktesCafeteria(){
        $query = 'SELECT count(*) as Total, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "ABIERTO") as Abiertos, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "ABIERTO") as ultAbiertos, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "EN CURSO") as Curso, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "EN CURSO") as ultCurso, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "DETENIDO") as Detenido, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "DETENIDO") as ultDetenido, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "SOLUCIONADO") as Solucionado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "SOLUCIONADO") as ultSolucionado, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "CERRADO") as Cerrado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "CERRADO") as ultCerrado, 
        (SELECT count(*) FROM comentarios_cafeteria where estatus = "CANCELADO") as Cancelado, 
        (SELECT max(fecha) FROM comentarios_cafeteria where estatus = "CANCELADO") as ultCancelado FROM comentarios_cafeteria';
        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }
function ajax_getCamaras(){
         $res = $this->Conexion->consultar("SELECT * FROM camaras");
        if($res)
        {
            echo json_encode($res);   
        }

    }
    function registrar_camara()
    {
        $data = json_decode($this->input->post('data'));
        $this->Conexion->insertar('camaras', $data);
        
    }

}
