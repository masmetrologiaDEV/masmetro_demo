<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_IT extends CI_Controller {

    function __construct() {
    parent::__construct();
    $this->load->library('unit_test'); // Carga biblioteca para pruebas unitarias
    $this->load->model('tickets_IT_model', 'Modelo'); // Modelo principal para reportes
    $this->load->model('conexion_model', 'Conexion'); // Modelo para gestión de conexión
    $this->load->library('correos'); // Biblioteca para envío de correos
    $this->load->library('AOS_funciones'); // Biblioteca de funciones personalizadas
}

function reporte() {
    // Carga vistas de encabezado y vista de reporte IT
    $this->load->view('header');
    $this->load->view('tickets/reporte_it');
}

function reporte_usuarios_ajax() {
    // Recibe fechas desde el formulario por POST
    $datos[0] = $this->input->post('inicio');
    $datos[1] = $this->input->post('final');

    $res = $this->Modelo->getReporteUsuarios($datos);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function reporte_tipo_ajax() {
    // Recibe fechas desde el formulario por POST
    $datos[0] = $this->input->post('inicio');
    $datos[1] = $this->input->post('final');

    $res = $this->Modelo->getReporteTipo($datos);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function reporte_tickets_ajax() {
    // Recibe fechas desde el formulario por POST y las asigna con formato clave-valor
    $datos['TS.fecha >='] = $this->input->post('inicio');
    $datos['TS.fecha <='] = $this->input->post('final');

    $res = $this->Modelo->getReporteTickets($datos);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function generar() {
    // Carga vistas para generación de ticket del área de sistemas
    $this->load->view('header');
    $this->load->view('generar_ticket_sistemas');
}

    function administrar($estatus) {
    $this->load->model('privilegios_model');
    $count = $this->Modelo->getTicketsCount();

    $datos['usuarios'] = $this->privilegios_model->listadoJefes();

    // Conteo de tickets por estado
    $datos['c_activos'] = $count->activos;
    $datos['c_detenidos'] = $count->detenidos;
    $datos['c_solucionados'] = $count->solucionados;
    $datos['c_cerrados'] = $count->cerrados;
    $datos['c_cancelados'] = $count->cancelados;
    $datos['c_revision'] = $count->revision;
    $datos['c_todos'] = $count->todos;

    $datos['filtro'] = $estatus;
    $datos['tickets'] = $this->Modelo->getTickets($estatus);
    $datos['controlador'] = 'tickets_IT';

    // Carga las vistas con datos para administrar tickets
    $this->load->view('header');
    $this->load->view('tickets_sistemas', $datos);
}

function registrar() {
    // Datos para crear un nuevo ticket
    $data = array(
        'usuario' => $this->session->id,
        'tipo' => $this->input->post('opCategoria'),
        'titulo' => $this->input->post('titulo'),
        'descripcion' => $this->input->post('descripcion'),
        'estatus' => 'ABIERTO',
        'cierre' => '0',
    );

    $last_id = $this->Modelo->crear_ticket($data);

    // Preparar datos para enviar correo de notificación
    $datosCorreo['id'] = $last_id;
    $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
    $datosCorreo['titulo'] = $data['titulo'];
    $datosCorreo['fecha'] = date('d/m/Y h:i A');
    $datosCorreo['usuario'] = $this->session->nombre;
    $datosCorreo['correo'] = $this->session->correo;

    $this->correos->creacionTicket($datosCorreo);

    // Redireccionar para subir archivos al ticket creado
    redirect(base_url('tickets_IT/archivos/') . $last_id);
}

function archivos($id_ticket) {
    $datos['id_ticket'] = $id_ticket;
    $datos['controlador'] = 'tickets_IT';

    // Carga vista para subir archivos al ticket
    $this->load->view('header');
    $this->load->view('subir_archivos', $datos);
}

public function ver($id) {
    // Obtiene datos completos del ticket seleccionado
    $Renglon = $this->Modelo->verTicket($id);
    $datos['ticket'] = $Renglon->row(); // Solo un renglón del ticket
    $datos['comentarios'] = $this->Modelo->verTicket_comentarios($id);
    $datos['comentarios_fotos'] = $this->Modelo->verTicket_comentarios_fotos($id);
    $datos['archivos'] = $this->Modelo->verTicketArchivos($id);
    $datos['controlador'] = $this->router->fetch_class();

    // Carga vistas para mostrar el ticket y sus detalles
    $this->load->view('header');
    $this->load->view('ver_ticket', $datos);
}

public function agregarComentario() {
    $idTicket = $this->input->post('idticket');

    $data = array(
        'ticket' => $idTicket,
        'usuario' => $this->session->id,
        'comentario' => $this->input->post('comentario'),
    );

    // Agrega comentario y redirecciona a la vista del ticket
    $this->Modelo->agregar_comentario($data);
    redirect(base_url('tickets_IT/ver/' . $idTicket));
}

    public function estatus($idTicket, $estatus) {
    // Obtiene datos del usuario asociado al ticket
    $Res = $this->Modelo->getUsuarioTicket($idTicket);

    // Mapea el estatus recibido a su texto y obtiene correo y usuario
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

        case '7':
            $Stat = 'EN REVISION';
            $correo = $Res->correo;
            $usuario = $Res->User;
            break;

        default:
            // Si estatus no válido, redirige al inicio
            redirect(base_url('inicio'));
            exit();
    }

    // Actualiza el estatus del ticket
    $data = array(
        'estatus' => $Stat,
    );
    $this->Modelo->update($idTicket, $data);

    // Si se tiene correo, envía notificación del cambio de estatus
    if (isset($correo)) {
        $datosCorreo['id'] = $idTicket;
        $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $usuario;
        $datosCorreo['tecnico'] = $this->session->nombre;
        $datosCorreo['estatus'] = $Stat;
        $datosCorreo['correo'] = $correo;

        $this->correos->ticketSolucionado($datosCorreo);
    }

    // Redirige a la vista del ticket actualizado
    redirect(base_url('tickets_IT/ver/' . $idTicket));
}

function subir_archivos() {
    $idTicket = $this->input->post('id_ticket');

    // Procesa múltiples archivos enviados
    for ($i = 0; $i < count($_FILES['file']['tmp_name']); $i++) {
        $datos = array(
            'ticket' => $idTicket,
            'nombre' => $_FILES['file']['name'][$i],
            'archivo' => file_get_contents($_FILES['file']['tmp_name'][$i])
        );

        // Intenta subir archivo, lanza error si falla
        if (!$this->Modelo->subir_archivos($datos)) {
            trigger_error("Error al subir archivo", E_USER_ERROR);
        }
    }
}

    function ver_foto($id) {
    // Obtiene la foto del ticket por ID
    $photo = $this->Modelo->getFoto($id);

    if ($photo) {
        // Envía encabezado de imagen PNG y muestra el contenido binario
        header("Content-type: image/png");
        echo $photo->archivo;
    } else {
        echo "ERROR";
    }
}

function ajax_cerrarTicket() {
    $id = $this->input->post('id');
    $comentario = $this->input->post('comentario');

    // Construye objeto con nueva calificación y estatus cerrado
    $t->calificacion = $this->input->post('calificacion');
    $t->estatus = "CERRADO";

    // Actualiza tabla tickets_sistemas para el ticket específico
    $this->Conexion->modificar('tickets_sistemas', $t, null, array('id' => $id));

    // Si hay comentario, se agrega como comentario del ticket
    if (!empty($comentario)) {
        $data = array(
            'ticket' => $id,
            'usuario' => $this->session->id,
            'comentario' => $comentario
        );
        $this->Modelo->agregar_comentario($data);
    }
}

function ajax_TicketsSimultaneos() {
    $categoria = $this->input->post("categoria");

    // Consulta tickets activos de la categoría que no están cerrados ni cancelados
    $res = $this->Conexion->consultar(
        "SELECT TS.*, concat(U.nombre, ' ', U.paterno) as User
        FROM tickets_sistemas TS
        INNER JOIN usuarios U ON U.id = TS.usuario
        WHERE TS.tipo = '$categoria'
        AND TS.estatus != 'CERRADO'
        AND TS.estatus != 'CANCELADO'"
    );

    if ($res) {
        // Devuelve resultado en formato JSON
        echo json_encode($res);
    }
}

    function excel(){
    // Obtener filtros enviados por POST
    $estatus= $this->input->post('estatus');
    $user= $this->input->post('user');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Preparar fechas para filtro en formato datetime
    $f1=strval($fecha1).' 00:00:00';
    $f2=strval($fecha2).' 23:59:59';

    // Construcción base del query con joins para traer info del ticket, comentarios y usuarios involucrados
    $query = " SELECT t.id as No_Ticket, t.fecha as Fecha_Ticket, t.tipo as Tipo, t.titulo as Titulo, t.descripcion as Descripcion, t.estatus as Estatus, t.fecha_cierre,
CONCAT(s.nombre, ' ', s.paterno) AS solucionador,
GROUP_CONCAT(
    CONCAT(DATE_FORMAT(tc.fecha, '%d/%m/%Y %H:%i'), ' - ', tc.comentario)
    SEPARATOR '\n'
) AS Comentarios,
CONCAT(u.nombre, ' ', u.paterno) AS Creador_Ticket
FROM tickets_sistemas t
    LEFT JOIN tickets_sistemas_comentarios tc ON t.id = tc.ticket
    LEFT JOIN usuarios s ON t.cierre = s.id
    JOIN usuarios u ON t.usuario = u.id
    WHERE 1=1 ";

    // Filtros por estatus, agregando condiciones específicas para cada caso
    if ($estatus) {
        if ($estatus=='activos') {
        $query .=" and (t.estatus = 'EN CURSO' OR t.estatus = 'ABIERTO')";
        }else if ($estatus=='revision') {
            $query .=" and t.estatus = 'EN REVISION'";
        }
        else if ($estatus=='solucionados') {
            $query .=" and t.estatus = 'SOLUCIONADO'";
        }
        else if ($estatus=='cerrados') {
            $query .=" and t.estatus = 'CERRADO'";
        }
        else if ($estatus=='cancelados') {
            $query .=" and t.estatus = 'CANCELADO'";
        }
        else if ($estatus=='detenidos') {
            $query .=" and t.estatus = 'DETENIDO'";
        } else if ($estatus=='todos') {
             $query .= " and t.estatus IN ('EN CURSO', 'ABIERTO', 'EN REVISION', 'SOLUCIONADO', 'CERRADO', 'CANCELADO', 'DETENIDO')";
        }
    }

    // Filtro por usuario creador del ticket
    if (!empty($user)) {
        $query .=" and t.usuario = '$user'";
    }

    // Filtro por rango de fechas
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
    }

    // Agrupar resultados por id de ticket para evitar duplicados por comentarios múltiples
    $query .= " GROUP BY t.id";

    // Ordenar resultados por id ascendente
    $query .=" ORDER BY t.id";

    // Ejecutar consulta
    $result= $this->Conexion->consultar($query);

    // Inicializar salida HTML para tabla Excel
    $salida='';

    // Cabecera de tabla con estilos inline para borde y fondo
    $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                    <thead> 
                        <tr>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">No_Ticket</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha_Ticket</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Tipo</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Titulo</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Descripcion</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Estatus</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha Cierre</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Solucionado</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Comentario</th>
                            <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Creador Ticket</th>
                        </tr>
                    </thead>
                    <tbody>';

    // Verificar que la consulta devolvió resultados válidos
    if (!$result || !is_array($result)) {
        echo "No se encontraron resultados o hubo un error en la consulta.";
        return;
    }

    // Recorrer cada fila para armar el contenido de la tabla
    foreach($result as $row){

        $salida .='
                    <tr>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->No_Ticket.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Fecha_Ticket.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Tipo.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Titulo.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Descripcion.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Estatus.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->fecha_cierre.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->solucionador.'</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">' . nl2br($row->Comentarios) . '</td>
                        <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Creador_Ticket.'</td>
                    </tr>';
    }

    // Cierre de tabla HTML
    $salida .= '</tbody>
            </table>';

    // Nombre del archivo Excel con fecha actual
    $timestamp = date('m/d/Y', time());
    $filename='Tickets_'.$timestamp.'.xls';

    // Headers para forzar descarga como archivo Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary'); 

    // Enviar contenido al navegador
    echo $salida;
}

    function reporte_it()
{
    // Recibir datos enviados vía POST
    $estatus = $this->input->post('estatus');
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Preparar fechas con horas para filtro entre rangos
    $f1=strval($fecha1).' 00:00:00';
    $f2=strval($fecha2).' 23:59:59';

    // Query base para obtener tickets con nombre completo del usuario que los creó
    $query = "SELECT t.*, concat(u.nombre, ' ',u.paterno) as User from tickets_sistemas t JOIN usuarios u on t.usuario = u.id where 1=1";

    // Filtrar por estatus si no es TODO (todos)
    if($estatus != 'TODO')
    {
        $query .= " and t.estatus = '$estatus'";
    }

    // Filtros de búsqueda según texto y parámetro (folio o usuario)
    if(!empty($texto))
    {
        if($parametro == "folio")
        {
            $query .= " and t.id = '$texto'";
        }
        if($parametro == "usuario")
        {
            $query .= " and concat(u.nombre, ' ', u.paterno) like '%$texto%'";
        }
    }

    // Filtro por rango de fechas, si se especifican ambas fechas
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
    }

    // Ordenar resultados de más recientes a más antiguos
    $query .= " order by t.id desc";

    // Ejecutar consulta
    $res= $this->Conexion->consultar($query);

    // Devolver resultados en formato JSON si existen, sino devolver cadena vacía
    if($res)
    {
        echo json_encode($res);
    }
    else{
        echo "";
    }
}

    function excel_IT(){
    // Obtener datos enviados vía POST
    $estatus= $this->input->post('opEstatus');
    $texto= $this->input->post('txtBusqueda');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $parametro = $this->input->post('rbBusqueda');
    $f1=strval($fecha1).' 00:00:00';
    $f2=strval($fecha2).' 23:59:59';
    
    // Consulta para obtener tickets con comentarios y creador
    $query = "SELECT t.id as No_Ticket,t.fecha as Fecha_Ticket,t.tipo as Tipo,t.titulo as Titulo,t.descripcion as Descripcion,t.estatus as Estatus, tc.fecha as fecha_comentario,tc.comentario as Comentario, concat(u.nombre,' ', u.paterno) as Cerador_Ticket FROM tickets_sistemas t JOIN tickets_sistemas_comentarios tc on t.id=tc.ticket JOIN usuarios u on t.usuario = u.id where 1=1 ";

    // Filtro por estatus, excepto cuando es TODO
    if($estatus != 'TODO')
    {
        $query .= " and t.estatus = '$estatus'";
    }
    // Filtros por texto y parámetro (folio o usuario)
    if(!empty($texto))
    {
        if($parametro == "folio")
        {
            $query .= " and t.id = '$texto'";
        }
        if($parametro == "usuario")
        {
            $query .= " and concat(u.nombre, ' ', u.paterno) like '%$texto%'";
        }
    }
    // Filtro por rango de fechas
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
    }
         
    // Ordenar resultados de forma descendente por ID
    $query .= " order by t.id desc";

    // Ejecutar consulta en base de datos
    $result= $this->Conexion->consultar($query);

    // Inicializar variable para salida HTML de tabla
    $salida='';

    // Crear encabezados de tabla con estilos para Excel
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

    // Recorrer cada resultado y agregar fila a tabla
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

    // Preparar nombre de archivo con fecha actual
    $timestamp = date('m/d/Y', time());
    $filename='Tickets_'.$timestamp.'.xls';

    // Encabezados para descarga del archivo Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary'); 

    // Mostrar contenido para descarga
    echo $salida;
}

    function buscar_tickets(){
    // Obtener datos enviados vía POST
    $estatus = $this->input->post('estatus');
    $user = $this->input->post('user');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Asignar fechas solo si no están vacías
    $f1 = (!empty($fecha1)) ? $fecha1 : null;
    $f2 = (!empty($fecha2)) ? $fecha2 : null;

    // Llamar al modelo para obtener tickets según filtros
    $res = $this->Modelo->getTickets($estatus, $user, $f1, $f2);

    // Retornar resultado en formato JSON (arreglo vacío si no hay resultados)
    echo json_encode($res ?: []);
}

public function prueba_buscar_tickets()
{
    // Simulación de datos POST para pruebas unitarias
    $_POST['estatus'] = 'activos';

    // Obtener variables simuladas desde POST
    $estatus = $this->input->post('estatus');
    $user = $this->input->post('user');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Asignar fechas solo si no están vacías
    $f1 = (!empty($fecha1)) ? $fecha1 : null;
    $f2 = (!empty($fecha2)) ? $fecha2 : null;

    // Ejecutar función del modelo
    $res = $this->Modelo->getTickets($estatus, $user, $f1, $f2);

    // Validar que la respuesta sea un arreglo
    echo $this->unit->run(is_array($res), TRUE, 'getTickets devuelve un arreglo');

    // Validar estructura si hay resultados
    if (!empty($res)) {
        echo $this->unit->run(isset($res[0]['id']), TRUE, 'El ticket tiene ID');
        echo $this->unit->run(isset($res[0]['estatus']), TRUE, 'El ticket tiene estatus');
    } else {
        // No hay tickets pero la respuesta es válida
        echo $this->unit->run(true, true, 'No hay tickets, pero respuesta válida');
    }
}

}
