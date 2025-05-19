<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_ED extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('tickets_ED_model','Modelo');
        $this->load->model('conexion_model', 'Conexion');
        $this->load->library('correos');
        $this->load->library('AOS_funciones');
    }

    public function generar() {
        $this->load->view('header');
        $this->load->view('generar_ticket_edificio');
    }

    function administrar($estatus) {
      $count = $this->Modelo->getTicketsCount();
      $datos['c_activos'] = $count->activos;
      $datos['c_solucionados'] = $count->solucionados;
      $datos['c_cerrados'] = $count->cerrados;
      $datos['c_cancelados'] = $count->cancelados;
      $datos['c_revision'] = $count->revision;
      $datos['c_todos'] = $count->todos;

      $datos['filtro'] = $estatus;
      $datos['tickets'] = $this->Modelo->getTickets($estatus);
      $datos['controlador'] = 'tickets_ED';
      $this->load->view('header');
      $this->load->view('tickets_sistemas', $datos);
    }

    public function registrar() {
        $data = array(
            'usuario' => $this->session->id,
            'tipo' => $this->input->post('tipo'),
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

        redirect(base_url('tickets_ED/archivos/') . $last_id);
    }

    function archivos($id_ticket) {
        $datos['id_ticket'] = $id_ticket;
        $datos['controlador'] = 'tickets_ED';
        $this->load->view('header');
        $this->load->view('subir_archivos', $datos);
    }

    public function ver($id) {
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
        redirect(base_url('tickets_ED/ver/' . $idTicket));
    }

    public function estatus($idTicket, $estatus) {
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
                $Res = $this->Modelo->getUsuarioTicket($idTicket);
                $correo = $Res->correo;
                $usuario = $Res->User;
                break;

            case '6':
                $Stat = 'CERRADO';
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
            $datosCorreo['correo'] = $correo;
            $this->correos->ticketSolucionado($datosCorreo);
        }

        redirect(base_url('tickets_ED/ver/' . $idTicket));
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


        $this->Conexion->modificar('tickets_edificio', $t, null, array('id' => $id));

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
    function reporte() {
        $this->load->view('header');
        $this->load->view('tickets/reporte_ed');
    }
    function reporte_ed()
    {
        $estatus = $this->input->post('estatus');
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';

        $query = "SELECT t.*, concat(u.nombre, ' ',u.paterno) as User from tickets_edificio t JOIN usuarios u on t.usuario = u.id where 1=1";

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
    function excel_ED(){
        $estatus= $this->input->post('opEstatus');
        $texto= $this->input->post('txtBusqueda');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $parametro = $this->input->post('rbBusqueda');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';
        
        $query = "SELECT t.id as No_Ticket,t.fecha as Fecha_Ticket,t.tipo as Tipo,t.titulo as Titulo,t.descripcion as Descripcion,t.estatus as Estatus, tc.fecha as fecha_comentario,tc.comentario as Comentario, concat(u.nombre,' ', u.paterno) as Cerador_Ticket FROM tickets_edificio t JOIN tickets_edificio_comentarios tc on t.id=tc.ticket JOIN usuarios u on t.usuario = u.id where 1=1 ";

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
        echo $query;die();
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



}
