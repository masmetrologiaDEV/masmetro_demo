<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Tool_model extends CI_Model {

    function __construct() {
        parent::__construct();
        $this->load->database();
    }

         function listadoProductos(){
        //$this->db->select('P.id, P.puesto, (SELECT count(*) from usuarios where puesto=P.id ) as Usuarios');
        $this->db->select('*');
        $this->db->from('productos');
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result->result();
        } else {
            return false;
        }
    }
    function listadoUsuarios(){
        //$this->db->select('P.id, P.puesto, (SELECT count(*) from usuarios where puesto=P.id ) as Usuarios');
        $this->db->select('id, no_empleado as numero, concat(nombre," ", paterno) as usuario');
        $this->db->from('usuarios');
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result->result();
        } else {
            return false;
        }
    }

    public function registrarVenta($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('VentToolCrib', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
      }
    }

    public function registrarVentaTemp($datos) {
    //$this->db->set('idUs', $this->session->id, FALSE);
      if($this->db->insert('VentTCTemp', $datos))
      {
        return $this->db->insert_id();
      }
      else
      {
        return 0;
      }
        
    }


    public function registrarProducto($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('productos', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
      }
    }

    public function registrarubicacion($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('ubiProds', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
      }
    }

    public function productos(){
        $query = $this->db->get('productos');
        if ($query->num_rows() > 0) {
            return $query;
        } else {
            return FALSE;
        }/*

        $this->db->select(' p.*, SUM(u.cantidad) as cantidad');
        $this->db->from('productos p');
        $this->db->join('ubiProds u', 'p.idProducto=u.idProd');
        $this->db->group_by('p.idProducto');
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }*/
    }
    public function ProdPedidos(){
        

        $this->db->select(' p.*, SUM(u.cantidad) as cantidad');
        $this->db->from('productos p');
        $this->db->join('ubiProds u', 'p.idProducto=u.idProd');
        $this->db->group_by('p.idProducto');
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }
    public function prodCant($idp){
        

        $this->db->select('SUM(u.cantidad) as cantidad');
        $this->db->from('productos p');
        $this->db->join('ubiProds u', 'p.idProducto=u.idProd');
        $this->db->where('p.idProducto',$idp);
        
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result->row();
        } else {
            return false;
        }
    }
    public function ubicaciones($idp){
        $this->db->select('u.*, p.*');        
        
        $this->db->join('productos p', 'p.idProducto=u.idProd');
        $this->db->where('idProd',$idp);
        $res = $this->db->get('ubiProds u');
         if($res->num_rows() > 0)
        {
            return $res;
        }
        else
        {
            return false;
        }
    }
    public function ubicacionAjuste($idp){
        $this->db->select('u.*, p.*');        
        
        $this->db->join('productos p', 'p.idProducto=u.idProd');
        $this->db->where('cantidad >',0);
        $this->db->where('idProd',$idp);
        $res = $this->db->get('ubiProds u');
         if($res->num_rows() > 0)
        {
            return $res->result();
        }
        else
        {
            return false;
        }
    }

     public function locacion($idUbi){
        $this->db->select('u.*, p.*');        
        
        $this->db->join('productos p', 'p.idProducto=u.idProd');
        $this->db->where('idUbi',$idUbi);
        $res = $this->db->get('ubiProds u');
         if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function ubicacion($idp)
    {
        $this->db->where('idProd',$idp);
        $res = $this->db->get('ubiProds');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    

    function updateProducto($idp,$ubi,$data){
        $this->db->where('idProd', $idp);
        $this->db->where('ubicacion', $ubi);
        $this->db->update('ubiProds', $data);
    }



    function getProds($idp)
    {
        $this->db->where('idProducto',$idp);
        $res = $this->db->get('productos');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function getDetalleVent($idp)
    {
        $this->db->where('idDVTC',$idp);
        $res = $this->db->get('detalleVentTC');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function DetalleVent($idp)
    {
        $this->db->select('d.*, p.producto');
        $this->db->join('productos p', 'd.idProD = p.idProducto');
        $this->db->where('idDVTC',$idp);
        $res = $this->db->get('detalleVentTC d');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function getProd($idp)
    {
        $this->db->where('idProducto',$idp);
        $res = $this->db->get('productos');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function deleteProducto($data){
        $this->db->where('idProducto', $data['idProducto']);
        $this->db->delete('productos', $data);
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    /* function ventatemp(){
        //$this->db->select('P.id, P.puesto, (SELECT count(*) from usuarios where puesto=P.id ) as Usuarios');
        $this->db->select('v.*, p.producto');
        $this->db->from('VentTCTemp v');
        $this->db->join('productos p', 'p.idProducto=v.idProd');
        $this->db->where('v.idUs', $this->session->id);
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result->result();
        } else {
            return false;
        }
    }*/
    function ventatemp()
    {
        $this->db->select('v.*, p.*');
        $this->db->from('VentTCTemp v');
        $this->db->join('productos p', 'p.idProducto=v.idProd');
        $this->db->where('v.idUs', $this->session->id);
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
    function getVenta(){

         $this->db->select('*');
        $this->db->from('VentTCTemp');
        
        $this->db->where('idUs', $this->session->id);
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



    public function registrarVentaDet($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('detalleVentTC', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
      }
    }
    function delVentTemp(){
        $this->db->where('idUs', $this->session->id);
        $this->db->delete('VentTCTemp');
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    public function registrarDetVenta($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('VentToolCrib', $datos)){
          return $this->db->insert_id();
         // echo var_dump($datos);die();
        }
        else {
          return FALSE;
      }
    }
     

    function pedidos()
    {
        $this->db->select('vt.*, concat(u.nombre," ",u.paterno) as nombre, u.no_empleado');
        $this->db->from('VentToolCrib vt');
        $this->db->join('usuarios u', 'vt.idUs = u.id');
        $this->db->where('vt.estatus !=', 'ENTREGADO');
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


    function getventaDet($idToolCrib)
    {
        $this->db->where('idToolCrib',$idToolCrib);
        $res = $this->db->get('VentToolCrib');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }

    function getPedido($idVenta)
    {
        $this->db->select('dv.*, p.codigo,p.producto,p.descripcion, concat(u.nombre," ", u.paterno) as nombre, (dv.cantidad*p.precio) total');
        $this->db->from('detalleVentTC dv');
        $this->db->join('productos p', 'dv.idProd = p.idProducto');
        $this->db->join('usuarios u', 'dv.idUs=u.id');
        $this->db->where('dv.idVenta', $idVenta);
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
    function getPedidoCorreo($idVenta)
    {
        $this->db->select('dv.*, p.producto, concat(u.nombre," ", u.paterno) as nombre, (dv.cantidad*p.precio) total');
        $this->db->from('detalleVentTC dv');
        $this->db->join('productos p', 'dv.idProd = p.idProducto');
        $this->db->join('usuarios u', 'dv.idUs=u.id');
        $this->db->where('dv.idVenta', $idVenta);
        $res = $this->db->get();
        if($res->num_rows() > 0)
        {
            return $res->result();
        }
        else
        {
            return false;
        }
    }

    function updateProd($idp,$data){
        $this->db->where('idProducto', $idp);
        $this->db->update('productos', $data);
    }
    function updateVenta($idVenta,$data){
        $this->db->where('idToolCrib', $idVenta);
        $this->db->update('VentToolCrib', $data);
    }
    function updateVentaDet($idDVTC,$data){
        $this->db->where('idDVTC', $idDVTC);
        $this->db->update('detalleVentTC', $data);
    }
     function reporte()
    {
        $this->db->select('dv.*, v.fecha, p.marca, p.proveedor, p.producto, p.precio, concat(u.nombre, " ", u.paterno) as nombre, u.no_empleado');
        $this->db->from('detalleVentTC dv');
        $this->db->join('usuarios u', 'dv.idUs=u.id');
        $this->db->join('VentToolCrib v', 'dv.idVenta=v.idToolCrib');
        $this->db->join('productos p', 'dv.idProd=p.idProducto');
        
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
    function ajuste($idProd,$ubi,$data){
        $this->db->where('idProd', $idProd);
        $this->db->where('idUbi', $ubi);
        $this->db->update('ubiProds', $data);
    }
    function ajusteVenta($idDVTC,$data){
        $this->db->where('idDVTC', $idDVTC);
        $this->db->update('detalleVentTC', $data);
    }

    function getUbi($idProd,$ubi)
    {
        $this->db->where('idProd',$idProd);
        $this->db->where('idUbi ',$ubi);
        $res = $this->db->get('ubiProds');
        if($res->num_rows() > 0)
        {
            return $res->row();
        }
        else
        {
            return false;
        }
    }
    function ajusteDetalle($idDV, $data){
        $this->db->where('idDVTC', $idDV);
        $this->db->update('detalleVentTC', $data);
    }





function pedidosPendientes(){
        $this->db->select('u.no_empleado, concat(u.nombre," ",u.paterno) as nombre, v.idToolCrib, v.estatus');
        $this->db->from('usuarios u');
        $this->db->join('VentToolCrib v', 'u.id = v.idus');
        $this->db->where('v.aprobador', $this->session->id);
        $this->db->where('v.estatus', 'PENDIENTE');       
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
    function correoJefe($idUs,$idp){
        $this->db->select('u.no_empleado, concat(u.nombre, " ", u.paterno) as nombre,u.jefe_directo, v.idToolCrib, v.estatus, (SELECT c.correo from usuarios c WHERE c.id=v.aprobador) as correo');
        $this->db->from('usuarios u');
        $this->db->join('VentToolCrib v', 'u.id = v.idUs');
        $this->db->where('u.id', $idUs);
        $this->db->where('v.idToolCrib', $idp);   
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

    function aprobar($idToolCrib,$data){
        $this->db->where('idToolCrib', $idToolCrib);
        $this->db->update('VentToolCrib', $data);
    }

    public function registrarMov($datos) {
        $this->db->db_debug = FALSE;
    
        if($this->db->insert('movimientosTool', $datos)){
          return $this->db->insert_id();
         
        }
        else {
          return FALSE;
      }
    }
    public function movimientos(){
        $this->db->select('m.*, concat(u.nombre," ",u.paterno) as nombre, p.producto');
        $this->db->from('movimientosTool m');
        $this->db->join('usuarios u', 'u.id=m.idus');
        $this->db->join('productos p', 'p.idProducto=m.idProd');
        $this->db->where('tipo !=','MODIFICACION');
        $this->db->order_by('m.fecha desc');

        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }
    public function movimientosProd($idProd){
        $this->db->select('m.*, concat(u.nombre," ",u.paterno) as nombre, p.producto');
        $this->db->from('movimientosTool m');
        $this->db->join('usuarios u', 'u.id=m.idus');
        $this->db->join('productos p', 'p.idProducto=m.idProd');
        $this->db->where('idProd', $idProd);
        $this->db->order_by('m.fecha desc');
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public function movimientosProdCom($idProd){
        $this->db->select('m.*, concat(u.nombre," ",u.paterno) as nombre, p.producto');
        $this->db->from('movimientosTool m');
        $this->db->join('usuarios u', 'u.id=m.idus');
        $this->db->join('productos p', 'p.idProducto=m.idProd');
        $this->db->where('idProd', $idProd);
        $this->db->where('tipo', 'MODIFICACION');
        $this->db->order_by('m.fecha desc');
        
        $result = $this->db->get();
        if ($result->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }

     function cancelarProducto($idp){
        $this->db->where('idvt', $idp);
        $this->db->delete('VentTCTemp');
        if ($this->db->affected_rows() > 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    



















}