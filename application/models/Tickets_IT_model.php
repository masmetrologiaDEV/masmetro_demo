<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tickets_IT_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

    function getTickets($estatus, $user = null, $f1 = null, $f2 = null) {
    // Definimos los estatus válidos
    $estatus_validos = ['activos', 'detenidos', 'revision', 'solucionados', 'cerrados', 'cancelados'];

    // Validamos el estatus; si no es válido, retornamos arreglo vacío
    if (!in_array($estatus, $estatus_validos)) {
        return false;
    }

    $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.usuario');
    $this->db->from('tickets_sistemas TS');
    $this->db->join('usuarios U', 'TS.usuario = U.id', 'LEFT');

    // Aplicamos filtro por estatus
    if ($estatus == 'activos') {
        $this->db->group_start();
        $this->db->where('TS.estatus', 'ABIERTO');
        // $this->db->or_where('TS.estatus', 'DETENIDO'); // comentado como en original
        $this->db->or_where('TS.estatus', 'EN CURSO');
        $this->db->group_end();
        // $this->db->or_where('TS.estatus', 'EN REVISION'); // comentado como en original
    } elseif ($estatus == 'detenidos') {
        $this->db->where('TS.estatus', 'DETENIDO');
    } elseif ($estatus == 'revision') {
        $this->db->where('TS.estatus', 'EN REVISION');
    } elseif ($estatus == 'solucionados') {
        $this->db->where('TS.estatus', 'SOLUCIONADO');
    } elseif ($estatus == 'cerrados') {
        $this->db->where('TS.estatus', 'CERRADO');
    } elseif ($estatus == 'cancelados') {
        $this->db->where('TS.estatus', 'CANCELADO');
    }

    // Filtrado por usuario si se recibe parámetro
    if ($user) {
        $this->db->where('TS.usuario', $user);
    }

    // Filtrado por fechas si se reciben parámetros válidos
    if (!empty($f1) && !empty($f2)) {
        $this->db->where('TS.fecha >=', $f1);
        $this->db->where('TS.fecha <=', $f2);
    }

    // Ordenar por fecha descendente
    $this->db->order_by('TS.fecha', 'DESC');
    $query = $this->db->get();

    if ($query->num_rows() > 0) {
        return $query->result_array();
    } else {
        return false;
    }
}


    function getReporteUsuarios($datos){
        $query = $this->db->query("SELECT T.usuario, concat(U.nombre, ' ', U.paterno) as User ,count(T.usuario) as conteo from tickets_sistemas T inner join usuarios U on U.id = T.usuario where T.fecha between '" . $datos[0] . "' and '" . $datos[1] . "' group by T.usuario order by conteo desc limit 5;");
        if($query->num_rows() > 0)
        {
            return $query->result();
        }
        else
        {
            return FALSE;
        }
    }
    
    function getReporteTipo($datos){
        $query = $this->db->query("SELECT T.tipo, count(T.tipo) as conteo from tickets_sistemas T where T.fecha between '" . $datos[0] . "' and '" . $datos[1] . "' group by T.tipo order by conteo desc;");
        if($query->num_rows() > 0)
        {
            return $query->result();
        }
        else
        {
            return FALSE;
        }
    }

    function getReporteTickets($datos){
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.usuario');
        $this->db->from('tickets_sistemas TS');
        $this->db->join('usuarios U', 'TS.usuario = U.id', 'LEFT');
        $this->db->where($datos);
        $this->db->order_by('TS.fecha', 'DESC');
        
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return false;
        }
    }

    function getTicketsCount(){
      $comando = "count(*) as todos";
    //  $comando .=", (SELECT count(*) from tickets_sistemas where (estatus = 'ABIERTO' or estatus = 'DETENIDO' or estatus = 'EN CURSO' or estatus = 'EN REVISION')) as activos";

      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'ABIERTO' or estatus = 'EN CURSO')) as activos";
      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'DETENIDO')) as detenidos";


      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'EN REVISION')) as revision";
      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'SOLUCIONADO')) as solucionados";
      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'CERRADO')) as cerrados";
      $comando .= ", (SELECT count(*) from tickets_sistemas where (estatus = 'CANCELADO')) as cancelados";

      $this->db->select($comando);
      $this->db->from('tickets_sistemas TS');
      $query = $this->db->get();
      return $query->row();
    }

    public function getMis_tickets() {
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.usuario');
        $this->db->from('tickets_sistemas TS');
        $this->db->join('usuarios U', 'TS.usuario = U.id', 'LEFT');
        $this->db->where('usuario', $this->session->id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function crear_ticket($datos) {
        $this->db->set('fecha', 'current_timestamp()', FALSE);
        $this->db->insert('tickets_sistemas', $datos);
        return $this->db->insert_id();
    }

    ///////////////////////// VER TICKET DE SERVICIO IT /////////////////////////
    public function verTicket($idTicket) {
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, U.correo, ifnull((SELECT concat(nombre," ",paterno) from usuarios where id = TS.cierre), "0") as Cierre, TS.fecha_cierre, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.usuario, TS.calificacion');
        $this->db->from('tickets_sistemas TS');
        $this->db->join('usuarios U', 'TS.usuario = U.id', 'LEFT');
        $this->db->where('TS.id', $idTicket);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function verTicket_comentarios($idTicket) {
        $this->db->select('TC.fecha, ifnull(concat(U.nombre," ",U.paterno), "N/A") as User, TC.usuario, TC.comentario');
        $this->db->from('tickets_sistemas_comentarios TC');
        $this->db->join('usuarios U', 'TC.usuario = U.id', 'LEFT');
        $this->db->where('TC.ticket', $idTicket);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function verTicket_comentarios_fotos($idTicket) {
        $this->db->select('TC.usuario, U.foto');
        $this->db->from('tickets_sistemas_comentarios TC');
        $this->db->join('usuarios U', 'TC.usuario = U.id', 'LEFT');
        $this->db->where('TC.ticket', $idTicket);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function agregar_comentario($datos) {
        $this->db->set('fecha', 'current_timestamp()', FALSE);
        $this->db->insert('tickets_sistemas_comentarios', $datos);
    }

    public function update($id, $datos) {
        if($datos['estatus'] == 'SOLUCIONADO')
        {
            $this->db->set('cierre',$this->session->id);
            $this->db->set('fecha_cierre','CURRENT_TIMESTAMP()',FALSE);
        }
        $this->db->where('id', $id);
        $this->db->update('tickets_sistemas', $datos);
    }

    public function subir_archivos($datos) {
        return $this->db->insert('tickets_sistemas_archivos', $datos);
    }

    function verTicketArchivos($id_ticket) {
        $this->db->where('ticket', $id_ticket);
        $query = $this->db->get('tickets_sistemas_archivos');

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    function getUsuarioTicket($idTicket){
        $this->db->select('concat(U.nombre," ",U.paterno) as User, U.correo');
        $this->db->from('tickets_sistemas TS');
        $this->db->join('usuarios U', 'TS.usuario = U.id', 'LEFT');
        $this->db->where('TS.id', $idTicket);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }

    function getFoto($id){
      $this->db->select('archivo');
      $this->db->where('id', $id);
      $res = $this->db->get('tickets_sistemas_archivos');
      if($res->num_rows() > 0)
      {
          return $res->row();
      }
      else
      {
          return false;
      }
    }
 function Equipos()
    {
        $this->db->select("E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado, ifnull((SELECT max(fecha) from manttoEquipos where idEquipo=E.id),'') as Ultrev");
        $this->db->from('equipos_it E');
        $this->db->join('usuarios U', 'U.id = E.asignado', 'LEFT');
//        $this->db->join('manttoEquipos m', 'm.idEquipo = E.id', 'LEFT');
	$this->db->where('E.activo', '1');
        $this->db->having('Ultrev < last_day(now()) + interval 1 day - interval 2 month');
        $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
/*
    function Equipos()
    {
        $this->db->select("E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado");
        $this->db->from('equipos_it E');
        $this->db->join('usuarios U', 'U.id = E.asignado');
        $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res;


        }
        else
        {
            return false;
        }
    }*/
    function getEquipos($idE)
    {
        $this->db->select("e.*, concat(u.nombre, ' ', u.paterno) as nombre");
        $this->db->from('equipos_it e');
        $this->db->join('usuarios u', 'u.id = e.asignado', 'LEFT');

        $this->db->where("e.id", $idE);
        $res = $this->db->get();
        
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function getEquipo($idequipo)
    {
        $this->db->where('idequipo',$idequipo);
        $res = $this->db->get('equiposHis');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function historialMantto($ide){
        $this->db->select("m.*, concat(u.nombre, ' ', u.paterno) as nombre");
        $this->db->from('manttoEquipos m');
        $this->db->join('usuarios u', 'm.usMantto=u.id');
        $this->db->where('m.idEquipo ', $ide);
        $this->db->order_by('m.fecha', 'DESC');
         $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res;


        }
        else
        {
            return false;
        }
    }
    function Mantto($ide){
        $this->db->select("m.*, concat(u.nombre, ' ', u.paterno) as nombre");
        $this->db->from('manttoEquipos m');
        $this->db->join('usuarios u', 'm.usMantto=u.id');
        $this->db->where('m.idME ', $ide);
        //$this->db->order_by('R.fecha', 'DESC');
         $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res->row();


        }
        else
        {
            return false;
        }
    }
    function historial($idequipo){
        $this->db->select('h.*, concat(u.nombre," ", u.paterno) as usuario, e.tipo');
        $this->db->from('equiposHis h');
        $this->db->join('usuarios u', 'h.idus=u.id');
        $this->db->join('equipos_it e', 'h.idequipo=e.id');
        $this->db->where('h.idequipo', $idequipo);
        //$this->db->order_by('R.fecha', 'DESC');
         $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res;


        }
        else
        {
            return false;
        }
    }
     public function registrarMantto($datos) {
     
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('manttoEquipos', $datos)){
          return $this->db->insert_id();
         
        }
        else {
          return FALSE;
      }
        
    }
    function registrar($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('fotosMantto', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
        }
    }
    function fotosMantto($idM){
        $this->db->select('*');
        $this->db->from('fotosMantto');
        $this->db->where('idMantto', $idM);
        //$this->db->order_by('R.fecha', 'DESC');
         $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res;


        }
        else
        {
            return false;
        }
    }
    function getEquiposCount(){
      $comando = "count(*) as todos,";
      $comando .="(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Laptop') as laptop,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Desktop')as desktop,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Monitor') as monitor,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Impresora') as impresora,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Bateria') as bateria,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Router') as router,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Switch') as switches,";
      $comando .= "(SELECT COUNT(*) FROM manttoEquipos m JOIN equipos_it e on e.id=m.idEquipo WHERE e.tipo='Celular') as celular";

      $this->db->select($comando);
      $this->db->from('manttoEquipos');
      $query = $this->db->get();
      return $query->row();
    }



}

