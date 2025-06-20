<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Recursos extends CI_Controller {

    function __construct() {
    parent::__construct();
    // La siguiente línea fue eliminada porque estaba comentada e inactiva
    // $this->load->library('aos_funciones');
}

// Carga la vista para asignación de recursos, incluyendo la tasa USD y su fecha de actualización
function asignar_recursos(){
    $usd = $this->aos_funciones->getUSD();
    $data['usd'] = $usd[0];
    $data['usd_actualizacion'] = $usd[1];

    $this->load->view('header');
    $this->load->view('recursos/asignar_recursos', $data);
}

// Carga la vista para mostrar la forma de pago de una orden específica
function ver_forma_pago(){
    if(isset($_POST["id"])){
        $id = $this->input->post('id');
    } else {
        redirect(base_url('inicio'));
    }

    $data["id"] = $id;

    $this->load->view('header');
    $this->load->view('recursos/ver_forma_pago', $data);
}

// Devuelve por AJAX la lista de órdenes de compra relacionadas a recursos pendientes de pago
function ajax_getPO_recursos(){
    $metodo = 0; $date_sort = 0;

    if(isset($_POST['metodo'])) {
        $metodo = $this->input->post('metodo');
    }

    if(isset($_POST['date_sort'])) {
        $date_sort = $this->input->post('date_sort');
    }

    // Se consultan únicamente órdenes con estatus válidos y que aún no han sido marcadas como "PAGADO"
    $query = "SELECT PO.*, concat(U.nombre, ' ', U.paterno) as User from ordenes_compra PO inner join usuarios U on U.id = PO.usuario where 1 = 1";
    $query .= " and PO.recurso != 'PAGADO'";
    $query .= " and (PO.estatus = 'AUTORIZADA' or PO.estatus = 'ORDENADA' or PO.estatus = 'RECIBIDA' or PO.estatus = 'CERRADA')";

    if($metodo != 0){
        $query .= " and PO.metodo_pago = $metodo";
    }

    if($date_sort != 0){
        $query .= " order by PO.fecha_cobro desc";
    }

    $res = $this->Conexion->consultar($query);

    if($res){
        echo json_encode($res);
    } else {
        echo "";
    }
}

    // Consulta los métodos de pago activos junto con su saldo disponible (ingresos - egresos)
function ajax_getMetodosPago(){
    $query = "SELECT MP.*, (SELECT (ifnull(sum(MPM.monto), 0)) as Ingreso from empresa_metodos_pago_movimientos MPM where MPM.metodo = MP.id) - (ifnull(sum(PO.total * PO.tipo_cambio), 0)) as Saldo from empresa_metodos_pago MP left join ordenes_compra PO on PO.metodo_pago = MP.id and PO.recurso != 'PENDIENTE' and (PO.estatus != 'RECHAZADA' and PO.estatus != 'CANCELADA') where MP.activo = '1' group by MP.id";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Marca una orden de compra como provisionada, registra la fecha y dispara una notificación por bajo saldo
function ajax_setPO(){
    $this->load->library('recursos_funciones');
    $po = json_decode($this->input->post('PO'));

    $res = $this->Conexion->modificar('ordenes_compra', $po, array('fecha_provision' => 'CURRENT_TIMESTAMP()'), array('id' => $po->id));
    if($res > 0){
        $this->recursos_funciones->NotificacionMinimo($po->metodo_pago);
        echo "1";
    }
}

// Inserta un nuevo fondeo (abono) a un método de pago y actualiza su estado de fondeo pendiente
function ajax_setFondeo(){
    $registro = json_decode($this->input->post('registro'));
    $registro->usuario = $this->session->id;

    $res = $this->Conexion->insertar('empresa_metodos_pago_movimientos', $registro, array('fecha' => 'CURRENT_TIMESTAMP()'));
    if($res > 0){
        $this->Conexion->modificar('empresa_metodos_pago', array('fondear' => 0), null, array('id' => $registro->metodo));
        echo "1";
    }
}

// Obtiene todos los movimientos de un método de pago: fondeos (abonos) y pagos (ordenes de compra)
function ajax_getMovimientos(){
    $id = $this->input->post('id');

    $query = "SELECT EMM.id, EMM.monto, '1' as tipo_cambio, EMM.fecha, 'ABONO' as TipoM, EM.nombre, EM.tipo from empresa_metodos_pago_movimientos EMM inner join empresa_metodos_pago EM on EMM.metodo = EM.id where EMM.metodo = $id";
    $query .= " UNION ";
    $query .= "SELECT PO.id, PO.total, PO.tipo_cambio, PO.fecha_provision, PO.recurso, M.nombre, M.tipo from ordenes_compra PO inner join empresa_metodos_pago M on PO.metodo_pago = M.id where PO.metodo_pago = $id and PO.recurso != 'PENDIENTE' and (PO.estatus != 'RECHAZADA' and PO.estatus != 'CANCELADA') order by fecha";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    } else {
        echo "";
    }
}

}