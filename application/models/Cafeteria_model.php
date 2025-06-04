<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cafeteria_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }
    public function crear_comentario($datos) {
        $this->db->set('fecha', 'current_timestamp()', FALSE);
        $this->db->insert('comentarios_cafeteria', $datos);
        return $this->db->insert_id();
    }
    public function subir_archivos($datos) {
        return $this->db->insert('cafeteria_archivos', $datos);
    }
    public function verTicket($idTicket) {
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, U.correo, ifnull((SELECT concat(nombre," ",paterno) from usuarios where id = TS.cierre), "0") as Cierre, TS.fecha_cierre, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.id_user, TS.fecha_incidencia');
        $this->db->from('comentarios_cafeteria TS');
        $this->db->join('usuarios U', 'TS.id_user = U.id', 'LEFT');
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
        $this->db->from('cafeteria_comentarios TC');
        $this->db->join('usuarios U', 'TC.usuario = U.id', 'LEFT');
        $this->db->where('TC.id_comentario', $idTicket);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }
    public function verTicket_comentarios_fotos($idTicket) {
        $this->db->select('TC.usuario, U.foto');
        $this->db->from('cafeteria_comentarios TC');
        $this->db->join('usuarios U', 'TC.usuario = U.id', 'LEFT');
        $this->db->where('TC.id_comentario', $idTicket);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }
    function verTicketArchivos($id_ticket) {
        $this->db->where('id_comentario', $id_ticket);
        $query = $this->db->get('cafeteria_archivos');

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }
    function getFoto($id){
      $this->db->select('archivo');
      $this->db->where('id', $id);
      $res = $this->db->get('cafeteria_archivos');
      if($res->num_rows() > 0)
      {
          return $res->row();
      }
      else
      {
          return false;
      }
    }
    public function agregar_comentario($datos) {
        $this->db->set('fecha', 'current_timestamp()', FALSE);
        $this->db->insert('cafeteria_comentarios', $datos);
    }
    function getUsuarioTicket($idTicket){
        $this->db->select('concat(U.nombre," ",U.paterno) as User, U.correo');
        $this->db->from('comentarios_cafeteria TS');
        $this->db->join('usuarios U', 'TS.id_user = U.id', 'LEFT');
        $this->db->where('TS.id', $idTicket);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->row();
        } else {
            return null;
        }
    }
    public function update($id, $datos) {
        if($datos['estatus'] == 'SOLUCIONADO')
        {
            $this->db->set('cierre',$this->session->id);
            $this->db->set('fecha_cierre','CURRENT_TIMESTAMP()',FALSE);
        }
        $this->db->where('id', $id);
        $this->db->update('comentarios_cafeteria', $datos);
    }
    public function comentarios() {
        $this->db->select("c.*, concat(u.nombre, ' ', u.paterno) as user");
        $this->db->from('comentarios_cafeteria c');
        $this->db->join('usuarios u', 'c.id_user = u.id');
        $where = "(c.estatus= 'ABIERTO' OR c.estatus = 'EN CURSO') and c.fecha BETWEEN now() - INTERVAL 2 day AND now()";
        $this->db->where($where);
//        $this->db->where('c.estatus', 'ABIERTO');

        //$this->db->where('TC.id_comentario', $idTicket);
        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }

    public function getMis_tickets() {
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.id_user');
        $this->db->from('comentarios_cafeteria TS');
        $this->db->join('usuarios U', 'TS.id_user = U.id', 'LEFT');
        $this->db->where('id_user', $this->session->id);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }
    public function get_tickets() {
        $this->db->select('TS.id, TS.fecha, ifnull(concat(U.nombre," ",U.paterno),"N/A") as User, TS.tipo, TS.titulo, TS.descripcion, TS.estatus, TS.id_user');
        $this->db->from('comentarios_cafeteria TS');
        $this->db->join('usuarios U', 'TS.id_user = U.id', 'LEFT');
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return false;
        }
    }


}

?>