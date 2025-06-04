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
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /*$sala = $_POST['sala'];
    $inicia = $_POST['inicia'];
    $termina = $_POST['termina'];*/
/*
echo '<pre>';
print_r($_POST['sala']);
print_r($_POST['inicia']);
print_r($_POST['termina']);
echo '</pre>';
die();*/

/*
    $query = "SELECT * FROM agenda WHERE sala = ? AND (
        (inicia < ? AND termina > ?) OR
        (inicia >= ? AND inicia < ?)
    )";
    $res = $this->db->query($query, [$sala, $termina, $inicia, $inicia, $termina])->result();

    if (!empty($res)) {
        echo "<script>alert('La sala ya está ocupada en ese horario'); window.history.back();</script>";
        exit;
    }*/

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//agregar sala
           if ($reunion == 1) {
            $datos = array(
            'usuario' => $this->session->id, 
            'titulo' =>$_POST['titulo'] , 
            'inicia' => $_POST['inicia'], 
            'termina' => $_POST['termina'], 
            'sala' => $_POST['sala'], 

            'descripcion' => $_POST['descripcion'], 
            'equipo' => 1, 
            'correos' => $_POST['tags_1'], 
        );


        echo $this->Modelo->insertEvent($datos);

            $data = array(
            'usuario' => $this->session->id,
            'tipo' =>'Reunion',
            'titulo' => $_POST['titulo'].' -- Fecha/Hora: '.$_POST['inicia'].' hasta '.$_POST['termina'],
            'descripcion' => $_POST['descripcion'].' -- Fecha/Hora: '.$_POST['inicia'].' hasta '.$_POST['termina'].' agregrar correos: '.$_POST['tags_1'],
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
            
        }else{
            $datos = array(
            'usuario' => $this->session->id, 
            'titulo' =>$_POST['titulo'] , 
            'inicia' => $_POST['inicia'], 
            'termina' => $_POST['termina'], 
            'descripcion' => $_POST['descripcion'], 
        );
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
