<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Agenda para manejar eventos y reuniones
class Agenda extends CI_Controller {

    // Constructor: carga modelos y librerías necesarias
    function __construct() {
        parent::__construct();
        $this->load->model('agenda_model','Modelo');          // Modelo para agenda
        $this->load->model('tickets_IT_model', 'ITModelo');   // Modelo para tickets IT
        $this->load->library('correos');                      // Librería para envío de correos
        $this->load->model('conexion_model', 'Conexion');     // Modelo para conexiones DB
    }

    // Página principal: muestra el calendario
    public function index() {
        $this->load->view('header');                     // Carga el header
        $this->load->view('agenda/calendario');          // Carga la vista del calendario
    }

    // Obtiene eventos para mostrarlos en el calendario
    function getEventos()
    {
        $data = $this->Modelo->getEventos();  // Obtiene eventos del modelo
        echo json_encode($data);              // Devuelve datos en formato JSON
    }

    // Crea un nuevo evento o reunión
    function crearEvento()
    {
        $reunion = $_POST['reunion'];  // Determina si es reunión (1) o evento normal
        
        // Datos básicos del evento
        $datos = array(
            'usuario' => $this->session->id,       // ID del usuario de sesión
            'titulo' => $_POST['titulo'],          // Título del evento
            'inicia' => $_POST['inicia'],          // Fecha/hora de inicio
            'termina' => $_POST['termina'],        // Fecha/hora de fin
            'descripcion' => $_POST['descripcion'],// Descripción
            'sala' => $_POST['sala'],              // Sala asignada
        );

        // Si es una reunión (valor 1)
        if ($reunion == 1) {
            
            $datos['equipo'] = 1;                  // Marca como reunión de equipo
            $datos['correos'] = $_POST['tags_1'];  // Correos de invitados

            echo $this->Modelo->insertEvent($datos); // Inserta el evento

            // Crea un ticket asociado a la reunión
            $data = array(
                'usuario' => $this->session->id,
                'tipo' => 'Reunion',               // Tipo de ticket
                'titulo' => $_POST['titulo'] . ' -- Fecha/Hora: ' . $_POST['inicia'] . ' hasta ' . $_POST['termina'],
                'descripcion' => $_POST['descripcion'] . ' -- Fecha/Hora: ' . $_POST['inicia'] . ' hasta ' . $_POST['termina'] . ' agregar correos: ' . $_POST['tags_1'],
                'estatus' => 'ABIERTO',            // Estado inicial
                'cierre' => '0',                  // No cerrado
            );

            $last_id = $this->ITModelo->crear_ticket($data); // Crea ticket y obtiene ID

            // Prepara datos para correo de notificación
            $datosCorreo = array(
                'id' => $last_id,
                'prefijo' => 'IT',
                'titulo' => $data['titulo'],
                'fecha' => date('d/m/Y h:i A'),
                'usuario' => $this->session->nombre,
                'correo' => $this->session->correo,
            );

            $this->correos->creacionTicket($datosCorreo); // Envía correo

            // Redirecciona a la página de archivos del ticket
            redirect(base_url('tickets_IT/archivos/') . $last_id);
        } else {
            // Si no es reunión, solo inserta el evento normal
            echo $this->Modelo->insertEvent($datos);
        }
    }

    // Elimina un evento
    function borrarEvento()
    {
        $id = $_POST['id'];  // ID del evento a borrar
        
        // Intenta borrar y devuelve resultado (1=éxito, 0=fallo)
        if($this->Modelo->deleteEvent($id) > 0)
        {
          echo "1";
        }
        else {
          echo "0";
        }
    }
    
    // Valida disponibilidad de sala en un horario específico
    function validacion()
    {
        $inicia=$this->input->post('inicia');  // Hora de inicio
        $termina=$this->input->post('termina'); // Hora de fin
        $sala=$this->input->post('sala');      // Sala a verificar
        
        // Consulta eventos que se solapen con el horario solicitado
        $res = $this->Conexion->consultar("SELECT * FROM `agenda` WHERE inicia <= '".$termina."' AND termina >= '".$inicia."' and sala=".$sala);

      if($res)
      {
        echo json_encode($res);  // Devuelve eventos conflictivos
      }
    }
}