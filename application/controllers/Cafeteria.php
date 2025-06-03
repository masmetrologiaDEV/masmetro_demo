<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cafeteria extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('cafeteria_model','Modelo');
        $this->load->library('correos');
         $this->load->library('AOS_funciones');
    }

    public function generar() {
        $this->load->view('header');
        $this->load->view('cafeteria/cafeteria');
        //$this->load->view('test/chat');
    }
    function registrar() {
        $data = array(
            'id_user' => $this->session->id,
            'tipo' => $this->input->post('opCategoria'),
            'titulo' => $this->input->post('titulo'),
            'descripcion' => $this->input->post('descripcion'),
            'estatus' => 'ABIERTO',
            'cierre' => '0',
            'fecha_incidencia' => $this->input->post('fecha_incidencia'),
        );
        $last_id = $this->Modelo->crear_comentario($data);

        $datosCorreo['id'] = $last_id;
        $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
        $datosCorreo['titulo'] = $data['titulo'];
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $this->session->nombre;
        $datosCorreo['correo'] = $this->session->correo;
        $datosCorreo['categoria'] = $this->input->post('opCategoria');
        $datosCorreo['fecha_incidencia'] = $this->input->post('fecha_incidencia');
       // $this->correos->comentarios_cafeteria($datosCorreo);
        redirect(base_url('cafeteria/archivos/') . $last_id);
    }
    function archivos($id_ticket) {
        $datos['id_ticket'] = $id_ticket;
       // $datos['controlador'] = 'tickets_IT';
        $this->load->view('header');
        $this->load->view('cafeteria/subir_archivos', $datos);
    }
    function subir_archivos() {
      $idTicket = $this->input->post('id_ticket');
      for ($i=0; $i < count($_FILES['file']['tmp_name']) ; $i++) {
        $datos = array('id_comentario' => $idTicket, 'nombre' => $_FILES['file']['name'][$i], 'archivo' => file_get_contents($_FILES['file']['tmp_name'][$i]));
        if(!$this->Modelo->subir_archivos($datos))
        {
          trigger_error("Error al subir archivo", E_USER_ERROR);
        }
      }
    }
     public function ver($id) {
        //$this->output->enable_profiler(TRUE);
        $Renglon = $this->Modelo->verTicket($id);
        $datos['ticket'] = $Renglon->row(); // 1 SOLO RENGLON
        $datos['comentarios'] = $this->Modelo->verTicket_comentarios($id);
        $datos['comentarios_fotos'] = $this->Modelo->verTicket_comentarios_fotos($id);
        $datos['archivos'] = $this->Modelo->verTicketArchivos($id);
        //$datos['controlador'] = $this->router->fetch_class();
        $this->load->view('header');
        $this->load->view('cafeteria/ver', $datos);
    }
    function ver_foto($id){
      $photo = $this->Modelo->getFoto($id);
      if($photo)
      {
        header("Content-type: image/png");
        echo $photo->archivo;
      }
      else {
        echo "ERROR";
      }
    }
    public function agregarComentario() {
        $idTicket = $this->input->post('idticket');
        $data = array(
            'id_comentario' => $idTicket,
            'usuario' => $this->session->id,
            'comentario' => $this->input->post('comentario'),
        );
        $this->Modelo->agregar_comentario($data);
        redirect(base_url('cafeteria/ver/' . $idTicket));
    }
    public function estatus($idTicket, $estatus) {
         $Res = $this->Modelo->getUsuarioTicket($idTicket);

        switch ($estatus) {

            case '1':
                $Stat = 'ABIERTO';
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '2':
                $Stat = 'EN CURSO';
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '3':
                $Stat = 'DETENIDO';
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '4':
                $Stat = 'CANCELADO';
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '5':
                $Stat = 'SOLUCIONADO';
               
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '6':
                $Stat = 'CERRADO';
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            default:
                redirect(base_url('inicio'));
                exit();
                break;
        }

        $data = array(
            'estatus' => $Stat,
        );

        $this->Modelo->update($idTicket, $data);

     /*   if (isset($correo)) {
            $datosCorreo['id'] = $idTicket;
            $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
            $datosCorreo['fecha'] = date('d/m/Y h:i A');
            $datosCorreo['usuario'] = $usuario;
            $datosCorreo['tecnico'] = $this->session->nombre;
            $datosCorreo['estatus'] = $Stat;
            $datosCorreo['correo'] = $correo;
            $this->correos->ticketSolucionado($datosCorreo);
        }*/

        redirect(base_url('cafeteria/ver/' . $idTicket));
    }
    public function mis_tickets() {
        $datos['tickets'] = $this->Modelo->getMis_tickets($this->session->id);
        $this->load->view('header');
        $this->load->view('cafeteria/mis_tickets', $datos);
    }
    public function administrar() {
        $datos['tickets'] = $this->Modelo->get_tickets($this->session->id);
        $this->load->view('header');
        $this->load->view('cafeteria/tickets', $datos);
    }
    function ajax_cerrarTicket(){
        $id = $this->input->post('id');
        $comentario = $this->input->post('comentario');
        $t->calificacion = $this->input->post('calificacion');
        $t->estatus = "CERRADO";


        $this->Conexion->modificar('comentarios_cafeteria', $t, null, array('id' => $id));

        if(!empty($comentario))
        {
            $data = array(
                'ticket' => $id,
                'usuario' => $this->session->id,
                'comentario' => $comentario
            );
            $this->Modelo->agregar_comentario($data);
        }

    }



}
