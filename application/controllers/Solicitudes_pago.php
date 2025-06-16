<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Solicitudes_pago extends CI_Controller {

    function __construct() {
    parent::__construct();
    $this->load->library('correos');
    $this->load->model('pago_model');
    $this->load->model('descargas_model');
}

function generar_pago() {
    $this->load->view('header');
    $this->load->view('solicitudes_pago/generar_pago');
}

function catalogo_solicitudes() {
    $this->load->view('header');
    $this->load->view('solicitudes_pago/catalogo_solicitudes');
}

function construccion_pago($idtemp) {
    // Consulta los datos temporales de un pago junto con información de proveedor, orden de compra y empresa
    $query = "SELECT pt.*, po.proveedor, po.total, po.estatus as estatusPO, pr.monto_credito, pr.moneda_credito, pr.terminos_pago, e.nombre from pago_temp pt join ordenes_compra po on po.id=pt.po JOIN proveedores pr on po.proveedor = pr.empresa join empresas e on e.id = pr.empresa WHERE pt.idtemp ='$idtemp'";
    
    $res = $this->Conexion->consultar($query);
    $data['idTemp'] = $idtemp;
    $data['pago'] = $res;

    $this->load->view('header');
    $this->load->view('solicitudes_pago/construccion_pago', $data);
}

function ver_pago($id) {
    $data['id'] = $id;

    // Obtiene comentarios, fotos y contacto relacionados al pago
    $data['comentarios'] = $this->pago_model->verPago_comentarios($id);
    $data['comentarios_fotos'] = $this->pago_model->verPago_comentarios_fotos($id);
    $data['contacto'] = $this->pago_model->contactos_pago($id);

    // Información general del pago
    $query = "SELECT s.*, p.monto_credito, p.empresa as idEmpresa, p.moneda_credito, p.terminos_pago, e.nombre, concat(u.nombre, ' ', u.paterno) as user from solicitudes_pago s JOIN proveedores p on s.idprov = p.empresa JOIN empresas e on p.empresa = e.id join usuarios u on s.idus = u.id WHERE s.id ='$id'";
    
    // Detalle de órdenes de compra vinculadas al pago
    $q = "SELECT ps.*, concat(u.nombre, ' ',u.paterno) as requisitor, po.tipo, po.total, po.estatus, concat(ua.nombre, ' ',ua.paterno) as aprobador FROM `Po_solicitudes` ps join ordenes_compra po on po.id = ps.idPO join usuarios u on u.id=po.usuario JOIN usuarios ua on ua.id=po.aprobador WHERE idPago ='$id'";

    $data['pago'] = $this->Conexion->consultar($query, TRUE);
    $data['pos'] = $this->Conexion->consultar($q);

    $this->load->view('header');
    $this->load->view('solicitudes_pago/editar_pago', $data);
}

function ajax_getPoPagos() {
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $id_proveedor = $this->input->post('id_proveedor');
    $tipo = $this->input->post('tipo');
    $moneda = $this->input->post('moneda');

    // Consulta órdenes de compra pendientes de pago con filtros dinámicos
    $query = "SELECT PO.id, E.id as IdProv, E.nombre as Prov, PO.tipo, PO.total, PO.moneda, PO.estatus_pago from ordenes_compra PO inner join empresas E on PO.proveedor = E.id where 1=1";

    if($id_proveedor > 0) {
        $query .= " and E.id = '$id_proveedor' and PO.moneda = '$moneda' and PO.tipo = '$tipo'";
    }

    $query .= " and PO.estatus_pago = 'PENDIENTE'";

    if(!empty($texto)) {
        if($parametro == "folio") {
            $query .= " and PO.id = '$texto'";
        }
        if($parametro == "proveedor") {
            $query .= " having Prov like '%$texto%'";
        }
    }

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function ajax_setTempPago() {
    $idtemp = uniqid();

    // Decodifica las órdenes de compra seleccionadas y las inserta en tabla temporal
    $po = json_decode($this->input->post('pos'));
    foreach($po as $elem) {
        $data['idtemp'] = $idtemp;
        $data['us'] = $this->session->id;
        $data['po'] = $elem;
        $this->Conexion->insertar('pago_temp', $data, null);
    }
    echo $idtemp;
}

function ajax_getTempPago() {
    $pos = json_decode($this->input->post('pos'));

    // Consulta productos relacionados a las órdenes seleccionadas
    $query = "SELECT PR.*, ifnull(JSON_UNQUOTE(PR.atributos->'$.modelo'),'') as Modelo, ifnull(JSON_UNQUOTE(PR.atributos->'$.marca'),'') as Marca, ifnull(JSON_UNQUOTE(PR.atributos->'$.serie'),'') as Serie, QRP.costos, QRP.factor, P.entrega from prs PR inner join qr_proveedores QRP on PR.qr_proveedor = QRP.id inner join proveedores P on P.empresa = QRP.empresa where 1 != 1";
    foreach ($pos as $elem) {
        $query .= " or PR.id ='$elem'";
    }

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function generarSolicitud($id) {
    $total = null;

    // Consulta la orden de compra relacionada al ID temporal
    $query = "SELECT pt.*, po.proveedor, po.total FROM pago_temp pt JOIN ordenes_compra po on pt.po = po.id where pt.idtemp ='$id'";
    $r = $this->Conexion->consultar($query, TRUE);
    $res = $this->Conexion->consultar($query);

    foreach ($res as $elem) {
        $total += $elem->total;
    }

    // Inserta la solicitud de pago con su información base
    $data['idus'] = $this->session->id;
    $data['idprov'] = $r->proveedor;
    $data['estatus'] = 'PENDIENTE';
    $data['estatus_factura'] = 'PENDIENTE';
    $data['estatus_complemento'] = 'PENDIENTE';
    $data['total'] = $total;

    $idPago = $this->Conexion->insertar('solicitudes_pago', $data, null);

    // Relaciona las órdenes con la solicitud de pago
    foreach ($res as $elem) {
        $pago['idPago'] = $idPago;
        $pago['idPO'] = $elem->po;
        $this->Conexion->insertar('Po_solicitudes', $pago, null);

        $dato['estatus_pago'] = "SOLICITADA";
        $where['id'] = $elem->po;
        $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
    }

    redirect(base_url('solicitudes_pago/ver_pago/' . $idPago));
}

    function agregarComentarioPago() {
    // Inserta un comentario asociado a una solicitud de pago
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
    // Guarda el archivo PDF de la prefactura en la solicitud de pago
    $id = $this->input->post('id');
    $datos['prefactura'] = file_get_contents($_FILES['file']['tmp_name']);
    $this->pago_model->updateSolicitudPago($id, $datos);
}

function uploadFactura() {
    // Guarda el archivo PDF de la factura en la solicitud de pago y registra fecha
    $date = date('Y-m-d h:i:s');
    $id = $this->input->post('id');
    $datos['factura'] = file_get_contents($_FILES['file']['tmp_name']);
    $datos['fecha_factura'] = $date;
    $this->pago_model->updateSolicitudPago($id, $datos);

    $query = "SELECT factura, xml from solicitudes_pago where id=" . $id;
    $res = $this->Conexion->consultar($query, TRUE);
}

function uploadXML() {
    // Guarda el archivo XML en la solicitud de pago y verifica si debe cambiar estatus
    $date = date('Y-m-d H:i:s');
    $id = $this->input->post('id');
    $datos['xml'] = file_get_contents($_FILES['file']['tmp_name']);
    $this->pago_model->updateSolicitudPago($id, $datos);

    $query = "SELECT factura, xml from solicitudes_pago where id=" . $id;
    $res = $this->Conexion->consultar($query, TRUE);
}

    function uploadComprobante() {
    // Carga el archivo de comprobante de pago, marca la solicitud como PAGADA y envía correo al solicitante
    $date = date('Y-m-d H:i:s');
    $id = $this->input->post('id');
    $datos['comprobante_pago'] = file_get_contents($_FILES['file']['tmp_name']);
    $datos['estatus'] = 'PAGADO';
    $datos['fecha_comprobante'] = $date;

    $this->pago_model->updateSolicitudPago($id, $datos);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $mail['date'] = $date;
    $this->correos->correos_solictudes_pagada($mail);

    // Actualiza estatus de pago de las órdenes asociadas
    $po = $this->Conexion->consultar('select * from Po_solicitudes where idPago = ' . $id);
    foreach ($po as $elem) {
        $dato['estatus_pago'] = 'APROBADO';
        $where['id'] = $elem->idPO;
        $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
    }
}

function uploadComplemento() {
    // Carga el archivo de complemento de pago y notifica a los usuarios con permiso de responder pagos
    $date = date('Y-m-d H:i:s');
    $id = $this->input->post('id');
    $datos['complemento'] = file_get_contents($_FILES['file']['tmp_name']);
    $datos['fecha_factura'] = $date;
    $this->pago_model->updateSolicitudPago($id, $datos);

    $query = 'SELECT u.correo from usuarios u join privilegios p on p.usuario = u.id where p.responderPago=1 and u.activo=1';
    $res = $this->Conexion->consultar($query);
    $correo = [];
    foreach ($res as $elem) {
        array_push($correo, $elem->correo);
    }

    $mail['id'] = $id;
    $mail['correo'] = $correo;
    $this->correos->correos_solictudes_complemento($mail);
}

function getprePDF($id) {
    // Devuelve el archivo PDF de la prefactura directamente al navegador
    $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
    $file = $row->prefactura;

    header('Content-type: application/pdf');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');

    echo $file;
}

function getPDF($id) {
    // Devuelve el archivo PDF de la factura directamente al navegador
    $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
    $file = $row->factura;

    header('Content-type: application/pdf');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');

    echo $file;
}

    function getXML($id) {
    // Devuelve el archivo XML como si fuera PDF para visualización directa en navegador
    $row = $this->descargas_model->getFile($id, 'solicitudes_pago');
    $file = $row->xml;

    header('Content-type: application/pdf');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');

    echo $file;
}

function solicitarPago() {
    // Actualiza el tipo de pago y factura de la solicitud, y redirige a la vista del pago
    $id = $this->input->post('id');
    $pago = $this->input->post('pago');
    $tipoFactura = $this->input->post('tipoFactura');

    $data['tipo_pago'] = $pago;
    $data['tipo_factura'] = $tipoFactura;
    $where['id'] = $id;

    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
    redirect(base_url('solicitudes_pago/ver_pago/' . $id));
}

function ajax_getContactos() {
    // Devuelve los contactos activos de una empresa en formato JSON
    $id = $this->input->post('id');
    $query = 'SELECT * from empresas_contactos where activo = 1 and empresa = ' . $id;
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function seleccionarContacto() {
    // Inserta la relación de un contacto con una solicitud de pago
    $id = $this->input->post('id');
    $idContacto = $this->input->post('idContacto');
    $contacto = $this->input->post('contacto');

    $data['idpago'] = $id;
    $data['idcontacto'] = $idContacto;
    $data['tipo'] = $contacto;

    $this->Conexion->insertar('contactos_pago', $data, null);
}

    function enviarSolicitud() {
    // Cambia estatus de la solicitud a 'SOLICITADO' y notifica a usuarios con permiso de responder pagos
    $id = $this->input->post('id');

    $data['estatus'] = 'SOLICITADO';
    $data['estatus_factura'] = 'EN REVISION';
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join privilegios p on p.usuario = u.id where p.responderPago=1 and u.activo=1';
    $res = $this->Conexion->consultar($query);
    $correo = [];
    foreach ($res as $elem) {
        array_push($correo, $elem->correo);
    }

    $mail['id'] = $id;
    $mail['correo'] = $correo;

    $this->correos->correos_solictudes($mail);
}

function aceptarSolicitud() {
    // Cambia estatus a 'APROBADA', registra aprobador y fecha, y notifica al usuario que solicitó el pago
    $date = date('Y-m-d h:i:s');
    $id = $this->input->post('id');

    $po = $this->Conexion->consultar('select * from Po_solicitudes where idPago = ' . $id);

    foreach ($po as $elem) {
        $dato['estatus_pago'] = 'APROBADO';
        $where['id'] = $elem->idPO;
        $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
    }

    $data['estatus'] = 'APROBADA';
    $data['estatus_factura'] = 'ACEPTADA';
    $data['aprobador_factura'] = $this->session->id;
    $data['fecha_aprobacion_factura'] = $date;
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;

    $this->correos->correos_solictudes_aceptada($mail);
}

    function programar_pago() {
    // Cambia el estatus del pago a "PROGRAMADA" y notifica al usuario responsable
    $id = $this->input->post('id');
    $date = $this->input->post('date');
    $po = $this->Conexion->consultar('select * from Po_solicitudes where idPago = ' . $id);

    foreach ($po as $elem) {
        $dato['estatus_pago'] = 'PROGRAMADO';
        $where['id'] = $elem->idPO;
        $this->Conexion->modificar('ordenes_compra', $dato, null, $where);
    }

    $data['estatus'] = 'PROGRAMADA';
    $data['fecha_programada_pago'] = $date;
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $mail['date'] = $date;
    $this->correos->correos_solictudes_programada($mail);
}

function solicitar_complemento() {
    // Cambia el estatus del complemento a "SOLICITADO"
    $id = $this->input->post('id');
    $data['estatus_complemento'] = 'SOLICITADO';
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);
}

function aceptar_complemento() {
    // Cambia el estatus del complemento a "ACEPTADO" y notifica al usuario
    $id = $this->input->post('id');
    $data['estatus_complemento'] = 'ACEPTADO';
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $mail['date'] = date('Y-m-d H:i:s'); // Se añadió para evitar variable indefinida
    $this->correos->correos_solictudes_complemento_aceptado($mail);
}

function cancelar_pago() {
    // Cambia el estatus del pago a "CANCELADO" y notifica al usuario
    $id = $this->input->post('id');
    $data['estaus'] = 'CANCELADO'; // Nota: probable typo, debería ser 'estatus'
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $this->correos->correos_solictudes_cancelado($mail);
}

function rechazar_factura() {
    // Cambia el estatus de la factura a "RECHAZADA" y notifica al usuario
    $id = $this->input->post('id');
    $data['estatus_factura'] = 'RECHAZADA';
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $this->correos->correos_solictudes_rechazar_factura($mail);
}

    function rechazar_complemento() {
    // Cambia el estatus del complemento a "RECHAZADO" y notifica al usuario correspondiente
    $id = $this->input->post('id');
    $data['estatus_complemento'] = 'RECHAZADO';
    $where['id'] = $id;
    $this->Conexion->modificar('solicitudes_pago', $data, null, $where);

    $query = 'SELECT u.correo from usuarios u join solicitudes_pago p on p.idus = u.id WHERE p.id = ' . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $mail['id'] = $id;
    $mail['correo'] = $res->correo;
    $this->correos->correos_solictudes_rechazar_complemento($mail);
}

function ajax_getSolicitudes() {
    // Devuelve lista de solicitudes de pago con filtros opcionales por estatus, folio, usuario o proveedor
    $query = "SELECT e.nombre, concat(u.nombre, ' ', u.paterno) as requisitor, p.id, p.fecha_creacion, p.estatus from solicitudes_pago p JOIN proveedores pr on pr.empresa=p.idprov JOIN empresas e on e.id = pr.empresa JOIN usuarios u on u.id=p.idus where 1 = 1";

    if (isset($_POST['estatus'])) {
        $estatus = $this->input->post('estatus');
        if ($estatus != "TODO") {
            $query .= " and p.estatus = '$estatus'";
        }
    }

    if (isset($_POST['texto'])) {
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        if (!empty($texto)) {
            if ($parametro == "folio") {
                $query .= " and p.id = '$texto'";
            }
            if ($parametro == "usuario") {
                $query .= " having requisitor like '%$texto%'";
            }
            if ($parametro == "proveedor") {
                $query .= " having nombre like '%$texto%'";
            }
        }
    }

    $query .= " order by p.id desc";
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function validarContacto() {
    // Valida si ya existe un contacto relacionado al pago y tipo dado
    $id = $this->input->post('id');
    $idContacto = $this->input->post('idContacto');
    $contacto = $this->input->post('contacto');

    $query = "SELECT * from contactos_pago where tipo ='$contacto' and idpago = " . $id . " and idcontacto = " . $idContacto;
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}
}



