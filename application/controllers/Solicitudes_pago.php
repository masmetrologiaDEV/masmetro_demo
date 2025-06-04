<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Solicitudes_pago extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('correos');
        $this->load->model('pago_model');
        $this->load->model('descargas_model');
        //$this->load->library('AOS_funciones');
    }

    function generar_pago(){
        $this->load->view('header');
        $this->load->view('solicitudes_pago/generar_pago');
    }

    function catalogo_solicitudes(){
        $this->load->view('header');
        $this->load->view('solicitudes_pago/catalogo_solicitudes');
    }

    function construccion_pago($idtemp){
        
       $query = "SELECT pt.*, po.proveedor, po.total, po.estatus as estatusPO, pr.monto_credito, pr.moneda_credito, pr.terminos_pago, e.nombre from pago_temp pt join ordenes_compra po on po.id=pt.po JOIN proveedores pr on po.proveedor = pr.empresa join empresas e on e.id = pr.empresa WHERE pt.idtemp ='$idtemp'";

        $res = $this->Conexion->consultar($query);
        $data['idTemp'] =$idtemp;
        $data['pago'] = $res;
        $this->load->view('header');
        $this->load->view('solicitudes_pago/construccion_pago', $data);    
    }

    function ver_pago($id){
        $data['id'] = $id;
        $data['comentarios'] = $this->pago_model->verPago_comentarios($id);
        $data['comentarios_fotos'] = $this->pago_model->verPago_comentarios_fotos($id);
        $data['contacto'] = $this->pago_model->contactos_pago($id);
        
       $query = "SELECT s.*, p.monto_credito, p.empresa as idEmpresa, p.moneda_credito, p.terminos_pago, e.nombre, concat(u.nombre, ' ', u.paterno) as user from solicitudes_pago s JOIN proveedores p on s.idprov = p.empresa JOIN empresas e on p.empresa = e.id join usuarios u on s.idus = u.id WHERE s.id ='$id'";
       $q="SELECT ps.*, concat(u.nombre, ' ',u.paterno) as requisitor, po.tipo, po.total, po.estatus, concat(ua.nombre, ' ',ua.paterno) as aprobador FROM `Po_solicitudes` ps join ordenes_compra po on po.id = ps.idPO join usuarios u on u.id=po.usuario JOIN usuarios ua on ua.id=po.aprobador WHERE idPago ='$id'";
        $data['pago'] = $this->Conexion->consultar($query, TRUE);
        $data['pos'] = $this->Conexion->consultar($q);

        $this->load->view('header');
        $this->load->view('solicitudes_pago/editar_pago', $data);
    }

    function ajax_getPoPagos(){
    
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        
        $id_proveedor = $this->input->post('id_proveedor');
        $tipo = $this->input->post('tipo');

        $moneda = $this->input->post('moneda');

        $query = "SELECT PO.id, E.id as IdProv, E.nombre as Prov, PO.tipo, PO.total, PO.moneda, PO.estatus_pago from ordenes_compra PO  inner join empresas E on PO.proveedor = E.id where 1=1 ";

        if($id_proveedor > 0)
        {
            $query .= " and E.id = '$id_proveedor' and PO.moneda = '$moneda' and PO.tipo = '$tipo'";
        }

        $query .= " and PO.estatus_pago = 'PENDIENTE'";

        if(!empty($texto))
        {
            if($parametro == "folio")
            {
                $query .= " and PO.id = '$texto'";
            }
            if($parametro == "proveedor")
            {
                $query .= " having Prov like '%$texto%'";
            }
            
        }
      //  echo $query;die();

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function ajax_setTempPago(){
        $idtemp= uniqid();

        $po = json_decode($this->input->post('pos'));
        foreach($po as $elem){
            $data['idtemp']= $idtemp;
            $data['us']= $this->session->id;
            $data['po']= $elem;
            $this->Conexion->insertar('pago_temp', $data, null);
        }
        echo $idtemp;
    }

     function ajax_getTempPago(){
        $pos = json_decode($this->input->post('pos'));  

        $query = "SELECT PR.*, ifnull(JSON_UNQUOTE(PR.atributos->'$.modelo'),'') as Modelo, ifnull(JSON_UNQUOTE(PR.atributos->'$.marca'),'') as Marca, ifnull(JSON_UNQUOTE(PR.atributos->'$.serie'),'') as Serie, QRP.costos, QRP.factor, P.entrega from prs PR inner join qr_proveedores QRP on PR.qr_proveedor = QRP.id inner join proveedores P on P.empresa = QRP.empresa where 1 != 1";
        foreach ($pos as $elem) {
            $query .= " or PR.id ='$elem'";
        }

        $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        } 
    }

    function generarSolicitud($id){
        $total=null;
        $query= "SELECT pt.*, po.proveedor, po.total FROM pago_temp pt JOIN ordenes_compra po on pt.po = po.id where pt.idtemp ='$id'";
        $r = $this->Conexion->consultar($query, TRUE);
        $res = $this->Conexion->consultar($query);
        foreach ($res as $elem) {
            $total+=$elem->total;
        }
        $data['idus'] = $this->session->id;
        $data['idprov'] = $r->proveedor;
        $data['estatus'] ='PENDIENTE';
        $data['estatus_factura'] ='PENDIENTE';
        $data['estatus_complemento'] ='PENDIENTE';
        $data['total'] = $total;

        $idPago=$this->Conexion->insertar('solicitudes_pago', $data, null);

        foreach ($res as $elem) {
            $pago['idPago']= $idPago;
            $pago['idPO']= $elem->po;
            $this->Conexion->insertar('Po_solicitudes', $pago, null);
            $dato['estatus_pago'] = "SOLICITADA";
            $where['id'] = $elem->po;
            $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
        }
        redirect(base_url('solicitudes_pago/ver_pago/'.$idPago));
    }

    function agregarComentarioPago() {
        $id = $this->input->post('id');
        $comentario = $this->input->post('comentario');


        $data = array(
            'idpago' => $id,
            'usuario' => $this->session->id,
            'comentario' => $comentario,
        );

        $funciones['fecha'] = 'current_timestamp()';
        
        $this->Conexion->insertar('pago_comentarios', $data, $funciones);

        redirect(base_url('solicitudes_pago/ver_pago/' . $id));
    }
    function uploadPreFactura() {
      $id=$this->input->post('id');
      $datos['prefactura'] = file_get_contents($_FILES['file']['tmp_name']);
      $this->pago_model->updateSolicitudPago($id,$datos);
    }
    function uploadFactura() {
      $date = date('Y-m-d h:i:s');
      $id=$this->input->post('id');
      $datos['factura'] = file_get_contents($_FILES['file']['tmp_name']);
      $datos['fecha_factura']=$date;
      $this->pago_model->updateSolicitudPago($id,$datos);  
      $query="SELECT factura, xml from solicitudes_pago where id=".$id;
      $res =$this->Conexion->consultar($query, TRUE);
      /*if($res->factura != null && $res->xml !=null) {
       // $data['estatus'] = 'EN REVISION';
        $data['estatus_factura'] = 'EN REVISION';
        $data['fecha_factura'] = $date;;
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
      }*/
    }
    function uploadXML() {
      $date = date('Y-m-d H:i:s');
      $id=$this->input->post('id');
      $datos['xml'] = file_get_contents($_FILES['file']['tmp_name']);
      $this->pago_model->updateSolicitudPago($id,$datos);
      $query="SELECT factura, xml from solicitudes_pago where id=".$id;
      $res =$this->Conexion->consultar($query, TRUE);
      /*if($res->factura != null && $res->xml !=null) {
        //$data['estatus'] = 'EN REVISION';
        $data['estatus_factura'] = 'EN REVISION';
        $data['fecha_factura'] = $date;
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
      }*/
    }
    function uploadComprobante() {
      $date = date('Y-m-d H:i:s');
      $id=$this->input->post('id');
      $datos['comprobante_pago'] = file_get_contents($_FILES['file']['tmp_name']);
      $datos['estatus']='PAGADO';
      $datos['fecha_comprobante']=$date;
      $this->pago_model->updateSolicitudPago($id,$datos);
      $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
      $res = $this->Conexion->consultar($query, TRUE);
      $mail['id']=$id;
      $mail['correo']=$res->correo;
      $mail['date']=$date;
      $this->correos->correos_solictudes_pagada($mail); 

      $po=$this->Conexion->consultar('select * from Po_solicitudes where idPago = '.$id);

        foreach($po as $elem){
            $dato['estatus_pago']='APROBADO';
            $where['id']=$elem->idPO;
            $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
        }
    }
    function uploadComplemento() {
      $date = date('Y-m-d H:i:s');
      $id=$this->input->post('id');
      $datos['complemento'] = file_get_contents($_FILES['file']['tmp_name']);
      $datos['fecha_factura']=$date;
      $this->pago_model->updateSolicitudPago($id,$datos);
      $query = 'SELECT u.correo from usuarios u join privilegios p on p.usuario = u.id where p.responderPago=1 and u.activo=1';
        $res = $this->Conexion->consultar($query);
        $correo = [];
        foreach($res as $elem){
        array_push($correo, $elem->correo);    
        }
        $mail['id']=$id;
        $mail['correo']=$correo;
        $this->correos->correos_solictudes_complemento($mail);  

    }
    function getprePDF($id){
        $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
        $file = $row->prefactura;
        //$nombre = $row->nombre_archivo;1

        //$file = 'dummy.pdf';
        header('Content-type: application/pdf');
        //header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');

        echo $file;

    }
    function getPDF($id){
        $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
        $file = $row->factura;
        //$nombre = $row->nombre_archivo;1

        //$file = 'dummy.pdf';
        header('Content-type: application/pdf');
        //header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');

        echo $file;

    }
    function getXML($id){
        $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
        $file = $row->xml;
        //$nombre = $row->nombre_archivo;1

        //$file = 'dummy.pdf';
        header('Content-type: application/pdf');
        //header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Transfer-Encoding: binary');
        //header('Content-Length: ' . filesize($file));
        header('Accept-Ranges: bytes');

        echo $file;

        //force_download($nombre, $file);
    }

    function solicitarPago(){
        $id = $this->input->post('id');
        $pago = $this->input->post('pago');
        $tipoFactura = $this->input->post('tipoFactura');

       /* $query="SELECT * from solicitudes_pago where id = ".$id;
        $r = $this->Conexion->consultar($query, TRUE);
        if ($r->prefactura == null || $r->xml == null) {
            echo 0;
        }else if($r->factura == null  || $r->xml == null) {
            echo 02;
        }else{*/
        $data['tipo_pago'] = $pago;
        $data['tipo_factura'] = $tipoFactura;
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        redirect(base_url('solicitudes_pago/ver_pago/' . $id));
      //  }



    }

    function ajax_getContactos(){
        $id = $this->input->post('id');
        $query = 'SELECT * from empresas_contactos where activo = 1 and empresa = '.$id;
        $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function seleccionarContacto(){
        $id = $this->input->post('id');
        $idContacto = $this->input->post('idContacto');
        $contacto = $this->input->post('contacto');
        $data['idpago'] = $id;
        $data['idcontacto'] = $idContacto;
        $data['tipo'] = $contacto;
        $this->Conexion->insertar('contactos_pago', $data, null);
    }
    function enviarSolicitud()
    {
        $id=$this->input->post('id');

        $data['estatus'] = 'SOLICITADO';
        $data['estatus_factura'] = 'EN REVISION';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

        $query = 'SELECT u.correo from usuarios u join privilegios p on p.usuario = u.id where p.responderPago=1 and u.activo=1';
        $res = $this->Conexion->consultar($query);
        $correo = [];
        foreach($res as $elem){
        array_push($correo, $elem->correo);    
        }
        $mail['id']=$id;
        $mail['correo']=$correo;
        $this->correos->correos_solictudes($mail);        

    }
    function aceptarSolicitud()
    {
        $date = date('Y-m-d h:i:s');

        $id=$this->input->post('id');
        $po=$this->Conexion->consultar('select * from Po_solicitudes where idPago = '.$id);

        foreach($po as $elem){
            $dato['estatus_pago']='APROBADO';
            $where['id']=$elem->idPO;
            $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
        }

        $data['estatus'] = 'APROBADA';
        $data['estatus_factura'] = 'ACEPTADA';
        $data['aprobador_factura']=$this->session->id;
        $data['fecha_aprobacion_factura']=$date;
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

        //$query = 'SELECT u.correo from usuarios u join privilegios p on p.usuario = u.id where p.responderPago=1 and u.activo=1';
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        /*echo $res->correo;die();
        $correo = [];
        foreach($res as $elem){
        array_push($correo, $elem->correo);    
        }*/
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $this->correos->correos_solictudes_aceptada($mail); 
    }

    function programar_pago(){
        $id=$this->input->post('id');
        $date=$this->input->post('date');
        $po=$this->Conexion->consultar('select * from Po_solicitudes where idPago = '.$id);

        foreach($po as $elem){
            $dato['estatus_pago']='PROGRAMADO';
            $where['id']=$elem->idPO;
            $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
        }

        $data['estatus'] = 'PROGRAMADA';
        $data['fecha_programada_pago']=$date;
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $mail['date']=$date;
        $this->correos->correos_solictudes_programada($mail); 
    }

    function solicitar_complemento(){
        $id=$this->input->post('id');
        $data['estatus_complemento']='SOLICITADO';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
    }
    function aceptar_complemento(){
        $id=$this->input->post('id');
        $data['estatus_complemento']='ACEPTADO';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $mail['date']=$date;
        $this->correos->correos_solictudes_complemento_aceptado($mail); 
    }
    function cancelar_pago(){
        $id=$this->input->post('id');
        $data['estaus']='CANCELADO';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $this->correos->correos_solictudes_cancelado($mail); 
    }
    function rechazar_factura(){
        $id=$this->input->post('id');
        $data['estatus_factura']='RECHAZADA';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $this->correos->correos_solictudes_rechazar_factura($mail); 
    }
    function rechazar_complemento(){
        $id=$this->input->post('id');
        $data['estatus_complemento']='RECHAZADO';
        $where['id']=$id;
        $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
        $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = '.$id;
        $res = $this->Conexion->consultar($query, TRUE);
        
        $mail['id']=$id;
        $mail['correo']=$res->correo;
        $this->correos->correos_solictudes_rechazar_complemento($mail); 
    }
    function ajax_getSolicitudes(){
        $query = "SELECT e.nombre, concat(u.nombre, ' ', u.paterno) as requisitor, p.id, p.fecha_creacion, p.estatus from solicitudes_pago p JOIN proveedores pr on pr.empresa=p.idprov JOIN empresas e on e.id = pr.empresa JOIN usuarios u on u.id=p.idus where 1 = 1 ";

        if(isset($_POST['estatus'])){
            $estatus = $this->input->post('estatus');
            
           
            if($estatus != "TODO")
            {
                $query .= " and p.estatus = '$estatus'";

            }
        }       

        if(isset($_POST['texto'])){
            $texto = $this->input->post('texto');
            $parametro = $this->input->post('parametro');
            if(!empty($texto))
            {
                if($parametro == "folio")
                {
                    $query .= " and p.id = '$texto'";
                }
                if($parametro == "usuario")
                {
                    $query .= " having requisitor like '%$texto%'";
                }
                if($parametro == "proveedor")
                {
                    $query .= " having nombre like '%$texto%'";
                }
            }
        }

        $query .= " order by p.id desc";
        $res = $this->Conexion->consultar($query);
        //echo ($res);die();

        if($res){
            echo json_encode($res);

        }
    }
    function validarContacto(){
        $id = $this->input->post('id');
        $idContacto = $this->input->post('idContacto');
        $contacto = $this->input->post('contacto');

        $query = "SELECT * from contactos_pago where tipo ='$contacto' and idpago =".$id." and idcontacto = ".$idContacto;
        //echo $query;die();
        $res = $this->Conexion->consultar($query);
         if($res){
            echo json_encode($res);

        }
    }
    

}



