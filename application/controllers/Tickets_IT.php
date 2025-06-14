<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_IT extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('unit_test');
        $this->load->model('tickets_IT_model', 'Modelo');
        $this->load->model('conexion_model', 'Conexion');
        $this->load->library('correos');
        $this->load->library('AOS_funciones');
    }

    function reporte() {
        $this->load->view('header');
        $this->load->view('tickets/reporte_it');
    }

    function reporte_usuarios_ajax() {
        $datos[0] = $this->input->post('inicio');
        $datos[1] = $this->input->post('final');

        $res = $this->Modelo->getReporteUsuarios($datos);
        if($res)
        {
            echo json_encode($res);
        }
        else{ echo ""; }
    }

    function reporte_tipo_ajax() {
        $datos[0] = $this->input->post('inicio');
        $datos[1] = $this->input->post('final');

        $res = $this->Modelo->getReporteTipo($datos);
        if($res)
        {
            echo json_encode($res);
        }
        else{ echo ""; }
    }

    function reporte_tickets_ajax() {
        $datos['TS.fecha >='] = $this->input->post('inicio');
        $datos['TS.fecha <='] = $this->input->post('final');

        $res = $this->Modelo->getReporteTickets($datos);
        if($res)
        {
            echo json_encode($res);
        }
        else{ echo ""; }
    }

    function generar() {
        //$this->output->enable_profiler(TRUE);
        $this->load->view('header');
        $this->load->view('generar_ticket_sistemas');
    }

    function administrar($estatus) {
        $this->load->model('privilegios_model');
	$count = $this->Modelo->getTicketsCount();
	$datos['usuarios'] = $this->privilegios_model->listadoJefes();
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
        $this->load->view('header');
        $this->load->view('tickets_sistemas', $datos);
    }

    function registrar() {
      

        $data = array(
            'usuario' => $this->session->id,
            'tipo' => $this->input->post('opCategoria'),
            'titulo' => $this->input->post('titulo'),
            'descripcion' => $this->input->post('descripcion'),
            'estatus' => 'ABIERTO',
            'cierre' => '0',
        );
        $last_id = $this->Modelo->crear_ticket($data);

        $datosCorreo['id'] = $last_id;
        $datosCorreo['prefijo'] = substr($this->router->fetch_class(), 8);
        $datosCorreo['titulo'] = $data['titulo'];
        $datosCorreo['fecha'] = date('d/m/Y h:i A');
        $datosCorreo['usuario'] = $this->session->nombre;
        $datosCorreo['correo'] = $this->session->correo;
        $this->correos->creacionTicket($datosCorreo);
        redirect(base_url('tickets_IT/archivos/') . $last_id);
    }

    function archivos($id_ticket) {
        $datos['id_ticket'] = $id_ticket;
        $datos['controlador'] = 'tickets_IT';
        $this->load->view('header');
        $this->load->view('subir_archivos', $datos);
    }

    public function ver($id) {
        //$this->output->enable_profiler(TRUE);
        $Renglon = $this->Modelo->verTicket($id);
        $datos['ticket'] = $Renglon->row(); // 1 SOLO RENGLON
        $datos['comentarios'] = $this->Modelo->verTicket_comentarios($id);
        $datos['comentarios_fotos'] = $this->Modelo->verTicket_comentarios_fotos($id);
        $datos['archivos'] = $this->Modelo->verTicketArchivos($id);
        $datos['controlador'] = $this->router->fetch_class();
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
        $this->Modelo->agregar_comentario($data);
        redirect(base_url('tickets_IT/ver/' . $idTicket));
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

            case '7':
                $Stat = 'EN REVISION';
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

        redirect(base_url('tickets_IT/ver/' . $idTicket));
    }

    function subir_archivos() {
      $idTicket = $this->input->post('id_ticket');
      for ($i=0; $i < count($_FILES['file']['tmp_name']) ; $i++) {
        $datos = array('ticket' => $idTicket, 'nombre' => $_FILES['file']['name'][$i], 'archivo' => file_get_contents($_FILES['file']['tmp_name'][$i]));
        if(!$this->Modelo->subir_archivos($datos))
        {
          trigger_error("Error al subir archivo", E_USER_ERROR);
        }
      }
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

    function ajax_cerrarTicket(){
        $id = $this->input->post('id');
        $comentario = $this->input->post('comentario');
        $t->calificacion = $this->input->post('calificacion');
        $t->estatus = "CERRADO";


        $this->Conexion->modificar('tickets_sistemas', $t, null, array('id' => $id));

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

    function ajax_TicketsSimultaneos(){
        $categoria = $this->input->post("categoria");

        $res = $this->Conexion->consultar("SELECT TS.*, concat(U.nombre, ' ', U.paterno) as User from tickets_sistemas TS inner join usuarios U on U.id = TS.usuario where TS.tipo = '$categoria' and TS.estatus != 'CERRADO' and TS.estatus != 'CANCELADO' ");
        if($res)
        {
            echo json_encode($res);
        }
    }

function excel(){
        $estatus= $this->input->post('estatus');

        $user= $this->input->post('user');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';

        ///agregar comentarios y fecha del comentario en un solo renglon
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



        
        //agregar filtros
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

            //$query .=" and t.estatus = '$esta'";
        }
        if (!empty($user)) {
            $query .=" and t.usuario = '$user'";
        }
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
        }
        
        $query .= " GROUP BY t.id";
        $query .=" ORDER BY t.id";
        //echo $query;die();
        $result= $this->Conexion->consultar($query);

         $salida='';

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

if (!$result || !is_array($result)) {
    echo "No se encontraron resultados o hubo un error en la consulta.";
    return;
}


        foreach($result as $row){

            //lineas reemplazadas por nl2br($row->Comentarios)

            // <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->fecha_comentario.'</td>
            // <td style="color: #444;  border: 1px solid black; border-collapse: collapse">'.$row->Comentario.'</td>

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

                $salida .= '</tbody>
                </table>';

        $timestamp = date('m/d/Y', time());
       
        $filename='Tickets_'.$timestamp.'.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary'); 
        echo $salida;
    }





    function reporte_it()
    {
        $estatus = $this->input->post('estatus');
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';

        $query = "SELECT t.*, concat(u.nombre, ' ',u.paterno) as User from tickets_sistemas t JOIN usuarios u on t.usuario = u.id where 1=1";

        if($estatus != 'TODO')
        {
            $query .= " and t.estatus = '$estatus'";
        }
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
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
        }
        $query .= " order by t.id desc";

        $res= $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
        }
    }
    function excel_IT(){
        $estatus= $this->input->post('opEstatus');
        $texto= $this->input->post('txtBusqueda');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $parametro = $this->input->post('rbBusqueda');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';
        
        $query = "SELECT t.id as No_Ticket,t.fecha as Fecha_Ticket,t.tipo as Tipo,t.titulo as Titulo,t.descripcion as Descripcion,t.estatus as Estatus, tc.fecha as fecha_comentario,tc.comentario as Comentario, concat(u.nombre,' ', u.paterno) as Cerador_Ticket FROM tickets_sistemas t JOIN tickets_sistemas_comentarios tc on t.id=tc.ticket JOIN usuarios u on t.usuario = u.id where 1=1 ";

         if($estatus != 'TODO')
        {
            $query .= " and t.estatus = '$estatus'";
        }
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
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" and t.fecha BETWEEN '".$f1."' AND '".$f2."'";
        }
             
        $query .= " order by t.id desc";
        $result= $this->Conexion->consultar($query);

         $salida='';

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
       
        $filename='Tickets_'.$timestamp.'.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary'); 
        echo $salida;
    }




    function buscar_tickets(){
    $estatus = $this->input->post('estatus');
    $user = $this->input->post('user');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    $f1 = (!empty($fecha1)) ? $fecha1 : null;
    $f2 = (!empty($fecha2)) ? $fecha2 : null;

    $res = $this->Modelo->getTickets($estatus, $user, $f1, $f2);
//echo var_dump($res);die();
    echo json_encode($res ?: []);
}
public function prueba_buscar_tickets()
{
    // Simulamos entradas POST
    $_POST['estatus'] = 'activos';
    //$_POST['user'] = '1';
    /*$_POST['fecha1'] = '2024-01-01';
    $_POST['fecha2'] = '2024-12-31';*/

    // Obtenemos las variables como en la función original
    $estatus = $this->input->post('estatus');
    $user = $this->input->post('user');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    $f1 = (!empty($fecha1)) ? $fecha1 : null;
    $f2 = (!empty($fecha2)) ? $fecha2 : null;

    // Llamamos el modelo directamente
    $res = $this->Modelo->getTickets($estatus, $user, $f1, $f2);
    //echo var_dump($res);die();

    // Verificamos que $res sea un array (o el tipo que esperes)
    echo $this->unit->run(is_array($res), TRUE, 'getTickets devuelve un arreglo');

    // Si hay resultados, verificamos su estructura (opcional)
    if (!empty($res)) {
        echo $this->unit->run(isset($res[0]['id']), TRUE, 'El ticket tiene ID');
        echo $this->unit->run(isset($res[0]['estatus']), TRUE, 'El ticket tiene estatus');
    } else {
        echo $this->unit->run(true, true, 'No hay tickets, pero respuesta válida');
    }
}
}
