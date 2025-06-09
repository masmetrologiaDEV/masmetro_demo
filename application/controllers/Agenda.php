<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Agenda extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('agenda_model','Modelo');
        $this->load->model('tickets_IT_model', 'ITModelo');
        $this->load->library('correos');
        $this->load->model('conexion_model', 'Conexion');
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
    $reunion = $_POST['reunion'];
   

    // Datos comunes del evento
    $datos = array(
        'usuario' => $this->session->id,
        'titulo' => $_POST['titulo'],
        'inicia' => $_POST['inicia'],
        'termina' => $_POST['termina'],
        'descripcion' => $_POST['descripcion'],
        'sala' => $_POST['sala'],
    );

    // Si es una reunión, se agregan datos extra y se crea ticket
    if ($reunion == 1) {
        
        $datos['equipo'] = 1;
        $datos['correos'] = $_POST['tags_1'];

        echo $this->Modelo->insertEvent($datos);

        // Crear ticket
        $data = array(
            'usuario' => $this->session->id,
            'tipo' => 'Reunion',
            'titulo' => $_POST['titulo'] . ' -- Fecha/Hora: ' . $_POST['inicia'] . ' hasta ' . $_POST['termina'],
            'descripcion' => $_POST['descripcion'] . ' -- Fecha/Hora: ' . $_POST['inicia'] . ' hasta ' . $_POST['termina'] . ' agregar correos: ' . $_POST['tags_1'],
            'estatus' => 'ABIERTO',
            'cierre' => '0',
        );

        $last_id = $this->ITModelo->crear_ticket($data);

        $datosCorreo = array(
            'id' => $last_id,
            'prefijo' => 'IT',
            'titulo' => $data['titulo'],
            'fecha' => date('d/m/Y h:i A'),
            'usuario' => $this->session->nombre,
            'correo' => $this->session->correo,
        );

        $this->correos->creacionTicket($datosCorreo);

        redirect(base_url('tickets_IT/archivos/') . $last_id);
    } else {
        // Evento normal sin ticket
        echo $this->Modelo->insertEvent($datos);
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
    function validacion()
    {
        $inicia=$this->input->post('inicia');
        $termina=$this->input->post('termina');
        $sala=$this->input->post('sala');
//cambiar la consulta
    
        $res = $this->Conexion->consultar("SELECT * FROM `agenda` WHERE inicia <= '".$termina."' AND termina >= '".$inicia."' and sala=".$sala);

      if($res)
      {
        echo json_encode($res);
      
    }
}


}
