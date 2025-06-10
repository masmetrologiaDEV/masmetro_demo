<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Cafeteria para gestionar incidencias relacionadas con el área de cafetería
class Cafeteria extends CI_Controller {

    // Constructor: carga modelos y librerías necesarias
    function __construct() {
        parent::__construct();
        $this->load->model('cafeteria_model','Modelo');   // Modelo principal para cafetería
        $this->load->library('correos');                  // Librería para enviar correos
        $this->load->library('AOS_funciones');            // Librería adicional (propietaria)
    }

    // Carga la vista principal para generar tickets de cafetería
    public function generar() {
        $this->load->view('header');                      // Encabezado
        $this->load->view('cafeteria/cafeteria');         // Vista principal del formulario
        // $this->load->view('test/chat');                // Vista de prueba (comentada)
    }

    // Registra un nuevo ticket
    function registrar() {
        $data = array(
            'id_user' => $this->session->id,                                      // Usuario que registra
            'tipo' => $this->input->post('opCategoria'),                          // Categoría seleccionada
            'titulo' => $this->input->post('titulo'),                             // Título del ticket
            'descripcion' => $this->input->post('descripcion'),                   // Descripción del problema
            'estatus' => 'ABIERTO',                                               // Estado inicial
            'cierre' => '0',                                                      // Aún no cerrado
            'fecha_incidencia' => $this->input->post('fecha_incidencia'),         // Fecha de la incidencia
        );

        $last_id = $this->Modelo->crear_comentario($data);                         // Guarda el ticket y obtiene ID

        // Datos para notificación por correo
        $datosCorreo['id'] = $last_id;
        $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
        $datosCorreo['titulo'] = $data['titulo'];
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $this->session->nombre;
        $datosCorreo['correo'] = $this->session->correo;
        $datosCorreo['categoria'] = $this->input->post('opCategoria');
        $datosCorreo['fecha_incidencia'] = $this->input->post('fecha_incidencia');

        // $this->correos->comentarios_cafeteria($datosCorreo);  // Envío de correo (comentado)

        redirect(base_url('cafeteria/archivos/') . $last_id);  // Redirecciona a la carga de archivos
    }

    // Carga vista para subir archivos relacionados a un ticket
    function archivos($id_ticket) {
        $datos['id_ticket'] = $id_ticket;
        // $datos['controlador'] = 'tickets_IT';                // Referencia a otro controlador (comentado)
        $this->load->view('header');
        $this->load->view('cafeteria/subir_archivos', $datos);
    }

    // Procesa subida de archivos al ticket
    function subir_archivos() {
        $idTicket = $this->input->post('id_ticket');

        for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
            $datos = array(
                'id_comentario' => $idTicket,
                'nombre' => $_FILES['file']['name'][$i],
                'archivo' => file_get_contents($_FILES['file']['tmp_name'][$i])
            );

            if (!$this->Modelo->subir_archivos($datos)) {
                trigger_error("Error al subir archivo", E_USER_ERROR);
            }
        }
    }

    // Muestra detalles del ticket, comentarios y archivos
    public function ver($id) {
        // $this->output->enable_profiler(TRUE);                // Habilita profiler (comentado)
        $Renglon = $this->Modelo->verTicket($id);
        $datos['ticket'] = $Renglon->row();                    // Obtiene un solo renglón
        $datos['comentarios'] = $this->Modelo->verTicket_comentarios($id);         // Comentarios
        $datos['comentarios_fotos'] = $this->Modelo->verTicket_comentarios_fotos($id); // Fotos
        $datos['archivos'] = $this->Modelo->verTicketArchivos($id);               // Archivos adjuntos
        // $datos['controlador'] = $this->router->fetch_class();                   // Controlador actual (comentado)
        $this->load->view('header');
        $this->load->view('cafeteria/ver', $datos);
    }

    // Muestra una imagen adjunta a un comentario
    function ver_foto($id) {
        $photo = $this->Modelo->getFoto($id);
        if ($photo) {
            header("Content-type: image/png");
            echo $photo->archivo;
        } else {
            echo "ERROR";
        }
    }

    // Agrega un comentario a un ticket existente
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

    // Cambia el estatus del ticket (ABIERTO, EN CURSO, etc.)
    public function estatus($idTicket, $estatus) {
        $Res = $this->Modelo->getUsuarioTicket($idTicket);

        switch ($estatus) {
            case '1': $Stat = 'ABIERTO'; break;
            case '2': $Stat = 'EN CURSO'; break;
            case '3': $Stat = 'DETENIDO'; break;
            case '4': $Stat = 'CANCELADO'; break;
            case '5': $Stat = 'SOLUCIONADO'; break;
            case '6': $Stat = 'CERRADO'; break;
            default:
                redirect(base_url('inicio')); exit(); break;
        }

        $correo = $Res->correo;
        $usuario = $Res->User;
        $data = array('estatus' => $Stat);
        $this->Modelo->update($idTicket, $data);
        redirect(base_url('cafeteria/ver/' . $idTicket));
    }

    // Muestra los tickets creados por el usuario actual
    public function mis_tickets() {
        $datos['tickets'] = $this->Modelo->getMis_tickets($this->session->id);
        $this->load->view('header');
        $this->load->view('cafeteria/mis_tickets', $datos);
    }

    // Muestra todos los tickets disponibles para el usuario administrador
    public function administrar() {
        $datos['tickets'] = $this->Modelo->get_tickets($this->session->id);
        $this->load->view('header');
        $this->load->view('cafeteria/tickets', $datos);
    }

    // Cierra un ticket desde una petición AJAX
    function ajax_cerrarTicket() {
        $id = $this->input->post('id');
        $comentario = $this->input->post('comentario');

        $t->calificacion = $this->input->post('calificacion'); // Calificación del servicio
        $t->estatus = "CERRADO";                               // Marca como cerrado

        $this->Conexion->modificar('comentarios_cafeteria', $t, null, array('id' => $id)); // Actualiza en BD

        if (!empty($comentario)) {
            $data = array(
                'ticket' => $id,
                'usuario' => $this->session->id,
                'comentario' => $comentario
            );
            $this->Modelo->agregar_comentario($data); // Agrega comentario final
        }
    }
}
