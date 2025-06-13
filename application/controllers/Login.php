<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('usuarios_model');
        $this->load->library('correos_tickets');
    }

    function index() {
        date_default_timezone_set('America/Chihuahua');
        $data['url_actual'] = base_url('inicio');

        // Si existe una URL previa en sesión, se conserva para redireccionar luego
        if(isset($this->session->url_actual)) {
            $data['url_actual'] = $this->session->url_actual;
        }

        // Destruye la sesión existente
        $this->session->sess_destroy();

        // Carga vista de login
        $this->load->view('login', $data);
    }

    function autenticar() {
        $url_actual = $this->input->post('url_actual');
        $usuario = $this->input->post('user');
        $pass = $this->input->post('pass');

        // Intenta autenticar al usuario con los datos proporcionados
        $res = $this->usuarios_model->autenticar($usuario, $pass);

        if ($res) {
            // Datos del usuario autenticado
            $row = $res->row();

            // Guardar datos importantes en la sesión
            $this->session->nombre = $row->User;
            $this->session->id = $row->id;
            $this->session->no_empleado = $row->no_empleado;
            $this->session->password = $row->password;
            $this->session->vencimiento_password = $row->vencimiento_password;
            $this->session->password_correo = $row->password_correo;
            $this->session->correo = $row->correo;
            $this->session->puesto = $row->puesto;
            $this->session->activo = $row->activo;
            $this->session->foto = $row->foto;
            $this->session->chats = '{ "chatbox0" : "CHAT"}';
            $this->session->departamento = $row->departamento;

            // Se asignan privilegios del usuario
            $this->session->privilegios = $this->usuarios_model->getPrivilegios($this->session->id);

            // Se actualiza última sesión
            $this->usuarios_model->ultimaSesion($row->id);

            // Se valida si hay tickets abiertos o en curso del usuario
            $us = $this->Conexion->consultar("SELECT max(ultima_sesion) as us from usuarios", true);
            $fecha = date("y-m-d", strtotime($us->us));
            $today = date("y-m-d", strtotime("today"));

            $query = "SELECT 'IT' as tipo, T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO' and usuario = $row->id"
                . " union "
                . "SELECT 'IT' as tipo, T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO' and usuario = $row->id"
                . " union "
                . "SELECT 'AT', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_autos T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO' and usuario = $row->id"
                . " union "
                . "SELECT 'AT', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_autos T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO' and usuario = $row->id"
                . " union "
                . "SELECT 'ED', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_edificio T inner join usuarios U on U.id = T.usuario where T.estatus = 'ABIERTO' and usuario = $row->id"
                . " union "
                . "SELECT 'ED', T.id, T.usuario, concat(U.nombre,' ',U.paterno) as User, U.correo, T.estatus from tickets_edificio T inner join usuarios U on U.id = T.usuario where T.estatus = 'EN CURSO' and usuario = $row->id";

            $arr = array();
            $res = $this->Conexion->consultar($query);
            if($res) {
                foreach ($res as $key => $value) {
                    if(!isset($arr[$value->usuario])) {
                        $arr[$value->usuario]['nombre'] = $value->User;
                        $arr[$value->usuario]['correo'] = $value->correo;
                        $arr[$value->usuario]['tickets'] = array();
                    }
                    array_push($arr[$value->usuario]['tickets'], $value->tipo . str_pad($value->id, 6, "0", STR_PAD_LEFT));
                }
            }

            // Posible envío de correo (actualmente desactivado)
            // $this->correos_tickets->ticketsPendientes($arr);

            // Redirige a la URL original
            redirect($url_actual);

        } else {
            // Autenticación fallida, se guarda mensaje de error en sesión
            $ERRORES = array();
            $error = array('titulo' => 'ERROR', 'detalle' => 'Usuario y/o Contraseña incorrectas');
            array_push($ERRORES, $error);
            $this->session->errores = $ERRORES;

            // Se conserva URL actual para reintentar
            $this->session->url_actual = $url_actual;

            // Redirige de nuevo a login
            redirect(base_url('login'));
        }
    }

    public function cerrar_sesion() {
        // Destruye la sesión y redirige a login
        $this->session->sess_destroy();
        redirect(base_url('login'));
    }

}
