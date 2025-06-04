<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Agenda extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('agenda_model','Modelo');
        $this->load->model('tickets_IT_model', 'ITModelo');
        $this->load->library('correos');

    }

    public function index() {
        $this->load->view('header');
        $this->load->view('agenda/calendario');
        //$this->load->view('test/chat');
    }

    function getEventos()
    {
        $data = $this->Modelo->getEventos();
        echo json_encode($data);
    }

    function crearEvento()
    {
        $datos['usuario'] = $this->session->id;
        $datos['titulo'] = $_POST['titulo'];
        $datos['inicia'] = $_POST['inicia'];
        $datos['termina'] = $_POST['termina'];
        $datos['descripcion'] = $_POST['descripcion'];
        echo $this->Modelo->insertEvent($datos);
	$reunion = $_POST['reunion'];
        if ($reunion == 1) {
            $data = array(
            'usuario' => $this->session->id,
            'tipo' =>'Reunion',
            'titulo' => $_POST['titulo'].'   Fecha/Hora: '.$_POST['inicia'],
            'descripcion' => $_POST['descripcion'].'   Fecha/Hora: '.$_POST['inicia'],
            'estatus' => 'ABIERTO',
            'cierre' => '0',
        );
        $last_id = $this->ITModelo->crear_ticket($data);

        $datosCorreo['id'] = $last_id;
        $datosCorreo['prefijo'] = 'IT';
        $datosCorreo['titulo'] = $data['titulo'];
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $this->session->nombre;
        $datosCorreo['correo'] = $this->session->correo;
        //echo var_dump($datosCorreo);die();
        $this->correos->creacionTicket($datosCorreo);
        redirect(base_url('tickets_IT/archivos/') . $last_id);
	}
    }

    function borrarEvento()
    {
        $id = $_POST['id'];
        if($this->Modelo->deleteEvent($id) > 0)
        {
          echo "1";
        }
        else {
          echo "0";
        }
    }


}
