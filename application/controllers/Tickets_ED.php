<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_ED extends CI_Controller {

    function __construct() {
    parent::__construct();
    $this->load->model('tickets_ED_model','Modelo'); // Modelo principal de tickets para el módulo de Edificio
    $this->load->model('conexion_model', 'Conexion'); // Modelo para conexión a base de datos personalizada
    $this->load->library('correos'); // Librería personalizada para envío de correos
    $this->load->library('AOS_funciones'); // Librería adicional para funciones AOS
}

public function generar() {
    // Carga la vista para generar un nuevo ticket de edificio
    $this->load->view('header');
    $this->load->view('generar_ticket_edificio');
}

function administrar($estatus) {
    // Obtiene conteo de tickets por estatus
    $count = $this->Modelo->getTicketsCount();
    $datos['c_activos'] = $count->activos;
    $datos['c_solucionados'] = $count->solucionados;
    $datos['c_cerrados'] = $count->cerrados;
    $datos['c_cancelados'] = $count->cancelados;
    $datos['c_revision'] = $count->revision;
    $datos['c_todos'] = $count->todos;
    $datos['c_detenidos'] = $count->detenidos;

    $datos['filtro'] = $estatus; // Filtro actual aplicado
    $datos['tickets'] = $this->Modelo->getTickets($estatus); // Tickets filtrados por estatus
    $datos['controlador'] = 'tickets_ED'; // Nombre del controlador para identificar módulo
    $this->load->view('header');
    $this->load->view('tickets_sistemas', $datos); // Vista compartida de tickets
}

public function registrar() {
    // Datos del formulario para crear nuevo ticket
    $data = array(
        'usuario' => $this->session->id,
        'tipo' => $this->input->post('tipo'),
        'titulo' => $this->input->post('titulo'),
        'descripcion' => $this->input->post('descripcion'),
        'estatus' => 'ABIERTO',
        'cierre' => '0',
    );
    
    $last_id = $this->Modelo->crear_ticket($data); // Guarda ticket en BD y obtiene ID

    // Preparación de datos para enviar correo de confirmación
    $datosCorreo['id'] = $last_id;
    $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
    $datosCorreo['titulo'] = $data['titulo'];
    $datosCorreo['fecha'] = date('d/m/Y h:i A');
    $datosCorreo['usuario'] = $this->session->nombre;
    $datosCorreo['correo'] = $this->session->correo;
    $this->correos->creacionTicket($datosCorreo); // Envía correo

    redirect(base_url('tickets_ED/archivos/') . $last_id); // Redirige a subir archivos
}

function archivos($id_ticket) {
    // Carga vista para subir archivos al ticket
    $datos['id_ticket'] = $id_ticket;
    $datos['controlador'] = 'tickets_ED';
    $this->load->view('header');
    $this->load->view('subir_archivos', $datos);
}

public function ver($id) {
    // Consulta información completa de un ticket
    $Renglon = $this->Modelo->verTicket($id);
    $datos['ticket'] = $Renglon->row(); // Ticket individual
    $datos['comentarios'] = $this->Modelo->verTicket_comentarios($id); // Comentarios del ticket
    $datos['comentarios_fotos'] = $this->Modelo->verTicket_comentarios_fotos($id); // Comentarios con fotos
    $datos['archivos'] = $this->Modelo->verTicketArchivos($id); // Archivos adjuntos al ticket
    $datos['controlador'] = $this->router->fetch_class(); // Nombre del controlador actual
    $this->load->view('header');
    $this->load->view('ver_ticket', $datos); // Carga vista para ver el ticket completo
}

    public function agregarComentario() {
    // Registra un nuevo comentario para el ticket correspondiente
    $idTicket = $this->input->post('idticket');
    $data = array(
        'ticket' => $idTicket,
        'usuario' => $this->session->id,
        'comentario' => $this->input->post('comentario'),
    );
    $this->Modelo->agregar_comentario($data); // Inserta comentario en base de datos
    redirect(base_url('tickets_ED/ver/' . $idTicket)); // Redirige a vista del ticket
}

public function estatus($idTicket, $estatus) {
    // Cambia el estatus de un ticket dependiendo del parámetro recibido
    switch ($estatus) {

        case '1':
            $Stat = 'ABIERTO';
            break;

        case '2':
            $Stat = 'EN CURSO';
            break;

        case '3':
            $Stat = 'DETENIDO';
            break;

        case '4':
            $Stat = 'CANCELADO';
            break;

        case '5':
            $Stat = 'SOLUCIONADO';
            $Res = $this->Modelo->getUsuarioTicket($idTicket); // Se obtiene información del usuario dueño del ticket
            $correo = $Res->correo;
            $usuario = $Res->User;
            break;

        case '6':
            $Stat = 'CERRADO';
            break;

        default:
            redirect(base_url('inicio')); // Redirección si el estatus no es válido
            exit();
            break;
    }

    $data = array(
        'estatus' => $Stat,
    );

    $this->Modelo->update($idTicket, $data); // Actualiza el estatus del ticket en BD

    // Si se ha definido $correo (solo cuando es SOLUCIONADO), se envía correo al usuario
    if (isset($correo)) {
        $datosCorreo['id'] = $idTicket;
        $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $usuario;
        $datosCorreo['tecnico'] = $this->session->nombre;
        $datosCorreo['correo'] = $correo;
        $this->correos->ticketSolucionado($datosCorreo); // Envío de correo de solución
    }

    redirect(base_url('tickets_ED/ver/' . $idTicket)); // Redirige a vista del ticket
}

function subir_archivos() {
    // Sube uno o más archivos al ticket
    $idTicket = $this->input->post('id_ticket');
    for ($i=0; $i < count($_FILES['file']['tmp_name']) ; $i++) {
        $datos = array('ticket' => $idTicket, 'nombre' => $_FILES['file']['name'][$i], 'archivo' => file_get_contents($_FILES['file']['tmp_name'][$i]));
        if(!$this->Modelo->subir_archivos($datos)) {
            trigger_error("Error al subir archivo", E_USER_ERROR); // Muestra error si la carga falla
        }
    }
}

    function ver_foto($id) {
    // Muestra la imagen almacenada en la base de datos
    $photo = $this->Modelo->getFoto($id);
    if($photo) {
        header("Content-type: image/png"); // Define el tipo de contenido
        echo $photo->archivo; // Imprime el contenido de la imagen
    } else {
        echo "ERROR"; // Mensaje de error si no se encuentra la foto
    }
}

function ajax_cerrarTicket() {
    // Cierra un ticket vía Ajax y registra un comentario si se proporciona
    $id = $this->input->post('id');
    $comentario = $this->input->post('comentario');
    $t->calificacion = $this->input->post('calificacion');
    $t->estatus = "CERRADO";

    $this->Conexion->modificar('tickets_edificio', $t, null, array('id' => $id)); // Actualiza ticket

    if(!empty($comentario)) {
        $data = array(
            'ticket' => $id,
            'usuario' => $this->session->id,
            'comentario' => $comentario
        );
        $this->Modelo->agregar_comentario($data); // Agrega comentario si se escribió
    }
}

function reporte() {
    // Muestra la vista del reporte general
    $this->load->view('header');
    $this->load->view('tickets/reporte_ed');
}

function reporte_ed() {
    // Genera reporte en base a filtros recibidos por POST y responde en JSON
    $estatus = $this->input->post('estatus');
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1=strval($fecha1).' 00:00:00';
    $f2=strval($fecha2).' 23:59:59';

    $query = "SELECT t.*, concat(u.nombre, ' ',u.paterno) as User from tickets_edificio t JOIN usuarios u on t.usuario = u.id where 1=1";

    if($estatus != 'TODO') {
        $query .= " and t.estatus = '$estatus'";
    }

    if(!empty($texto)) {
        if($parametro == "folio") {
            $query .= " and t.id = '$texto'";
        }
        if($parametro == "usuario") {
            $query .= " and concat(u.nombre, ' ', u.paterno) like '%$texto%'";
        }
    }

    if (!empty($fecha1) && !empty($fecha2)) {
        $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
    }

    $query .= " order by t.id desc";

    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res); // Devuelve resultado en formato JSON
    } else {
        echo ""; // Vacío si no hay resultados
    }
}

    function excel_ED() {
    // Recibe los filtros del formulario
    $estatus = $this->input->post('opEstatus');
    $texto = $this->input->post('txtBusqueda');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $parametro = $this->input->post('rbBusqueda');
    $f1 = strval($fecha1) . ' 00:00:00';
    $f2 = strval($fecha2) . ' 23:59:59';

    // Construcción de la consulta SQL
    $query = "SELECT t.id as No_Ticket, t.fecha as Fecha_Ticket, t.tipo as Tipo, t.titulo as Titulo, t.descripcion as Descripcion, t.estatus as Estatus, tc.fecha as fecha_comentario, tc.comentario as Comentario, concat(u.nombre,' ', u.paterno) as Cerador_Ticket FROM tickets_edificio t JOIN tickets_edificio_comentarios tc on t.id=tc.ticket JOIN usuarios u on t.usuario = u.id WHERE 1=1";

    if ($estatus != 'TODO') {
        $query .= " and t.estatus = '$estatus'";
    }

    if (!empty($texto)) {
        if ($parametro == "folio") {
            $query .= " and t.id = '$texto'";
        }
        if ($parametro == "usuario") {
            $query .= " and concat(u.nombre, ' ', u.paterno) like '%$texto%'";
        }
    }

    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " and t.fecha BETWEEN '$f1' AND '$f2'";
    }

    $query .= " order by t.id desc";

    $result = $this->Conexion->consultar($query); // Consulta ejecutada

    // Preparación de la tabla en HTML para exportar como Excel
    $salida = '';
    $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                            <thead> 
                                <tr>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">No_Ticket</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha_Ticket</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Tipo</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Titulo</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Descripcion</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Estatus</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha_Comentario</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Comentario</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Creador Ticket</th>
                                </tr>
                            </thead>
                            <tbody>';
        foreach($result as $row){
            
            $salida .='
                        <tr>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->No_Ticket.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Fecha_Ticket.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Tipo.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Titulo.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Descripcion.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Estatus.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->fecha_comentario.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Comentario.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Cerador_Ticket.'</td>
                        </tr>';
             }

                $salida .= '</tbody>
                </table>';

        $timestamp = date('m/d/Y', time());
    $filename = 'Tickets_' . $timestamp . '.xls';

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary');

    echo $salida; // Se imprime la tabla como Excel
    }
}
