<?php
require_once ('data/Merger/PDFMerger.php');
use PDFMerger\PDFMerger;

defined('BASEPATH') OR exit('No direct script access allowed');

class ordenes_trabajo extends CI_Controller {

    function __construct() {
    parent::__construct();

    // Carga bibliotecas necesarias para órdenes de trabajo
    $this->load->library('correos_facturacion');
    $this->load->model('MLConexion_model', 'MLConexion');
    $this->load->library('correos');
    $this->load->library('AOS_funciones');
    $this->load->library('ciqrcode');
}

// Vista principal del catálogo de órdenes de trabajo
function index(){
    $this->load->view('header');
    $this->load->view('ordenes_trabajo/catalogo');
}

// Vista del catálogo de órdenes con listado de técnicos activos
function catalogo_wo(){
    $data['tecnicos']= $this->MLConexion->consultar("SELECT * from catalogo_tecnicos where activo = '-1'");
    $this->load->view('header');
    $this->load->view('ordenes_trabajo/catalogo_wo', $data);
}

// Carga la vista para crear una nueva orden de trabajo
function crear_orden(){
    $this->load->view('header');
    $this->load->view('ordenes_trabajo/work_orders');
}

// Carga la vista para editar una solicitud de facturación
function editar_solicitud(){
    if(isset($_POST["id"])) {
        $id = $this->input->post('id');
    } else if ($id == 0){
        redirect(base_url('inicio'));
    }

    $data["id"] = $id;
    $data["editar"] = true;

    $this->load->view('header');
    $this->load->view('facturas/solicitud_facturas', $data);
}

// Visualiza el detalle de una orden de trabajo específica
function ver_wo($id = 0){
    if(isset($_POST["id"])) {
        $id = $this->input->post('id');
    } else if ($id == 0){
        redirect(base_url('inicio'));
    }

    $data["id"] = $id;

    // Consulta de detalles de la orden y descripción compuesta del equipo
    $query = "SELECT wo.*, wd.*, rs.folio_id, rs.Localizacion, ei.Status_Descripcion as estatus_item, ew.Status_Descripcion as estatus_wo, concat(rs.descripcion, if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)), if(isnull(rs.Equipo_ID), '', concat(' ID: ', rs.Equipo_ID))) as CadenaDescripcion from WO_Master wo JOIN WO_Detail wd on wo.WorkOrder_ID=wd.WorkOrder_ID JOIN rsitems rs ON rs.item_id=wd.Item_Id JOIN tblStatusWO ei on ei.Status_id = wd.Item_Status JOIN tblStatusWO ew on ew.Status_ID=wo.WorkOrder_Status where wo.WorkOrder_ID = ".$id;
    $data['wo_detail']=$this->MLConexion->consultar($query);

    // Consulta de información general de la orden y datos del cliente
    $query2 = "SELECT wo.*, wd.*, we.Status_Descripcion, rh.Empresa, rh.Direccion1, rh.Contacto, rh.TelefonoContacto, rh.CelularContacto from WO_Master wo left JOIN WO_Detail wd on wo.WorkOrder_ID=wd.WorkOrder_ID JOIN tblStatusWO we on we.Status_ID=wo.WorkOrder_Status join rsheaders rh on rh.folio_id=wo.rs where wo.WorkOrder_ID= ".$id;
    $data['wo']=$this->MLConexion->consultar($query2, TRUE);

    $this->load->view('header');
    $this->load->view('ordenes_trabajo/ver_wo', $data);
}

    function ajax_setWO(){
    $wo = json_decode($this->input->post('wo'));
    $rs_items = json_decode($this->input->post('rs_items'));

    // Establece el estatus inicial de la orden de trabajo
    $wo->WorkOrder_Status = 1;
    $wo->correo_us = $this->session->correo;

    $res = false;

    // Inserta la orden de trabajo en WO_Master
    $res = $this->MLConexion->insertar('WO_Master', $wo);

    // Por cada ítem de RS asociado, inserta en WO_Detail y actualiza rsitems
    foreach ($rs_items as $key) {
        $wo_detail['WorkOrder_ID'] = $res;
        $wo_detail['Item_Id'] = $key->item_id;
        $wo_detail['Item_Status'] = 1;

        $this->MLConexion->insertar('WO_Detail', $wo_detail);
        $this->MLConexion->comando("UPDATE rsitems SET FechaInWO = CURRENT_TIMESTAMP(), WOrder_ID ='".$res."' WHERE item_id = '".$key->item_id."'");
    }

    // Prepara y envía correo de notificación de creación de WO
    $mail['usuario'] = $this->session->nombre;
    $mail['wo'] = $res;
    $correo = [];
    $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.cerrar_wo = 1 and U.activo = 1");
    foreach ($correos_a as $key => $value) {
        array_push($correo, $value->correo);
    }
    $mail['correos'] = array_merge(array($this->session->correo), $correo);
    $this->correos->crear_wo($mail);

    // Genera código QR con enlace para visualizar la orden de trabajo
    $query = "SELECT * FROM WO_Master WHERE WorkOrder_ID=(SELECT MAX(WorkOrder_ID) FROM WO_Master) and correo_us='".$this->session->correo."'";
    $res = $this->MLConexion->consultar($query, TRUE);
    $t = 'https://validacion.masmetrologia.mx/ordenes_trabajo/ordenes_trabajo/ver_wo/'.$res->WorkOrder_ID;
    $qr = 'WO_'.$res->WorkOrder_ID.".png";
    $file = 'data/wo/'.$qr;
    QRcode::png($t, $file, 'L', 10, 2);
    $this->MLConexion->comando("UPDATE WO_Master SET qr =  '".$qr."' WHERE WorkOrder_ID=".$res->WorkOrder_ID);

    // Respuesta al frontend si la creación fue exitosa
    if($res) {
        echo "1";
    }
}
    
    function ajax_getSolicitudes(){
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $cliente = $this->input->post('cliente');
    $estatus = $this->input->post('estatus');
    $tipo = $this->input->post('tipo');
    $cerradas = $this->input->post('cerradas');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    $f1 = strval($fecha1).' 00:00:00';
    $f2 = strval($fecha2).' 23:59:59';

    // Consulta inicial: todas las órdenes de trabajo del usuario en sesión
    $query = "select wo.WorkOrder_ID, wo.rs, wo.correo_us, wo.FCreacion, wo.FProgramado, wo.FRetroalimentacion, wo.WorkOrder_Status, rs.`Nombre Corto` as Empresa, C.Cust_ID as empresa, e.Status_Descripcion from WO_Master wo join tblStatusWO e on e.Status_ID=wo.WorkOrder_Status JOIN rsheaders rs on rs.folio_id =wo.rs join catalogo_clientes C on rs.Cust_ID = C.Cust_ID where correo_us ='".$this->session->correo."'";

    // Filtro según si se desea ver solo abiertas o cerradas
    if($cerradas == "0") {
        $query .= " and (wo.WorkOrder_Status != '4')";
    } else {
        $query .= " and (wo.WorkOrder_Status = '4')";
    }

    // Filtro por número de folio
    if(!empty($texto)) {
        $query .= " and rs.folio_id = ".$texto;
    }

    // Filtro por cliente
    if(!empty($cliente) && $cliente != 0) {
        $query .= " and C.Cust_ID  = '$cliente'";
    }

    // Filtro por estatus
    if(!empty($estatus) && $estatus != 'TODO') {
        $query .= " and wo.WorkOrder_Status = '$estatus'";
    }

    // Filtro por rango de fechas programadas
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " and wo.FProgramado BETWEEN '".$f1."' AND '".$f2."'";
    }

    // Ordena los resultados por fecha programada más reciente
    $query .= " order by FProgramado desc";

    // Ejecuta la consulta y devuelve los resultados en formato JSON
    $res = $this->MLConexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

    function ajax_getWo(){
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $cliente = $this->input->post('cliente');
    $estatus = $this->input->post('estatus');
    $tecnico = $this->input->post('tecnico');
    $tipo = $this->input->post('tipo');
    $cerradas = $this->input->post('cerradas');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    $f1 = strval($fecha1).' 00:00:00';
    $f2 = strval($fecha2).' 23:59:59';

    // Consulta principal de órdenes de trabajo (WO), unida con estatus, cliente, técnico y encabezado RS
    $query = "select wo.WorkOrder_ID, wo.rs, wo.correo_us, wo.FCreacion, wo.FProgramado, wo.FRetroalimentacion, wo.WorkOrder_Status, rs.`Nombre Corto` as Empresa, e.Status_Descripcion, C.Cust_ID as empresa, ct.Nombre from WO_Master wo join tblStatusWO e on e.Status_ID=wo.WorkOrder_Status JOIN rsheaders rs on rs.folio_id =wo.rs join catalogo_tecnicos ct on ct.email_tecnico=wo.correo_us join catalogo_clientes C on rs.Cust_ID = C.Cust_ID where 1=1";

    // Filtra por estado: abierta o cerrada
    if($cerradas == "0") {
        $query .= " and (wo.WorkOrder_Status != '4')";
    } else {
        $query .= " and (wo.WorkOrder_Status = '4')";
    }

    // Filtra por número de folio si se especifica
    if(!empty($texto)) {
        $query .= " and rs.folio_id = ".$texto;
    }

    // Filtra por cliente si se especifica
    if(!empty($cliente) && $cliente != 0) {
        $query .= " and C.Cust_ID  = '$cliente'";
    }

    // Filtra por estatus de la orden si se especifica
    if(!empty($estatus) && $estatus != 'TODO') {
        $query .= " and wo.WorkOrder_Status = '$estatus'";
    }

    // Filtra por técnico asignado si se especifica
    if(!empty($tecnico) && $tecnico != 'TODO') {
        $query .= " and wo.correo_us = '$tecnico'";
    }

    // Filtra por fechas si ambos extremos están definidos
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " and wo.FProgramado BETWEEN '".$f1."' AND '".$f2."'";
    }

    // Ordena por fecha programada descendente
    $query .= " order by FProgramado desc";

    // Ejecuta consulta y responde con los resultados en formato JSON
    $res = $this->MLConexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

    function realizado(){
    $id = $this->input->post('itemOk');
    $tipo_cal = $this->input->post('tipo_cal');
    $txtrealizado = $this->input->post('txtrealizado');

    // Marca el ítem como realizado (estatus 5), guarda tipo de calibración y marca como calibrado
    $this->MLConexion->comando("UPDATE WO_Detail set Item_Status = 5, tipo_cal = '".$tipo_cal."', calibrado = 'SI' where id = ".$id);

    // Obtiene detalles de la orden de trabajo para redireccionar
    $wo = $this->MLConexion->consultar("SELECT * from WO_Detail where id=".$id, true);

    // Inserta comentario indicando que el ítem fue realizado
    $res = $this->MLConexion->consultar("SELECT * from WO_Detail where id=".$id, true);
    $datos['id_wo'] = $res->WorkOrder_ID;
    $datos['comentario'] = "Item Realizado #".$res->Item_Id." = ".$txtrealizado;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Redirecciona a la vista de la orden de trabajo correspondiente
    redirect(base_url('ordenes_trabajo/ver_wo/'.$wo->WorkOrder_ID));
}

function rechazar_item(){
    $id = $this->input->post('item');
    $txtrechazar = $this->input->post('txtrechazar');
    $motivo = $this->input->post('motivo');

    // Marca el ítem como no realizado (estatus 6), guarda motivo, desmarca calibración y limpia vencimiento
    $this->MLConexion->comando("UPDATE WO_Detail set Item_Status = 6, motivo = '".$motivo."', calibrado = 'NO', vencimiento = null, tipo_cal=null where id = ".$id);

    // Obtiene detalles del ítem rechazado para registrar comentario y limpieza en RS
    $res = $this->MLConexion->consultar("SELECT * from WO_Detail where id=".$id, true);
    $datos['id_wo'] = $res->WorkOrder_ID;
    $datos['comentario'] = "Item No Realizado #".$res->Item_Id." = ".$txtrechazar;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Limpia asignación del ítem en RS (se libera del WO)
    $this->MLConexion->comando("UPDATE rsitems set FechaInWO = null, WOrder_ID = null where item_id = ".$res->Item_Id);

    // Redirecciona nuevamente a la orden de trabajo para revisión
    redirect(base_url('ordenes_trabajo/ver_wo/'.$res->WorkOrder_ID));
}

    function fecha_vencimiento(){
    $id = $this->input->post('itemFecha');
    $vencimiento = $this->input->post('fecha');

    // Actualiza la fecha de vencimiento del ítem en la orden de trabajo
    $this->MLConexion->comando("UPDATE WO_Detail set vencimiento ='".$vencimiento."' where id = ".$id);

    // Consulta los datos del ítem para redirigir correctamente
    $wo = $this->MLConexion->consultar("SELECT * from WO_Detail where id=".$id, true);

    // Redirecciona a la vista correspondiente de la orden de trabajo
    redirect(base_url('ordenes_trabajo/ver_wo/'.$wo->WorkOrder_ID));
}

function reprogramar(){
    $id = $this->input->post('itemR');
    $txtreprogramar = "REPROGRAMAR WO: ".$this->input->post('txtreprogramar');
    $fecha = $this->input->post('txtFechaAccion');

    // Reprograma la orden de trabajo con nueva fecha y cambia estatus a 2
    $this->MLConexion->comando("UPDATE WO_Master set FProgramado = '".$fecha."', WorkOrder_Status = 2 where WorkOrder_ID = ".$id);

    // Inserta comentario informando la reprogramación
    $datos['id_wo'] = $id;
    $datos['comentario'] = $txtreprogramar;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Redirecciona a la vista de la orden reprogramada
    redirect(base_url('ordenes_trabajo/ver_wo/'.$id));
}

    function conlcuir_wo()
{
    $id = $this->input->post('itemConcluir');
    $txtconcluir = $this->input->post('txtconcluir');
    $foto = file_get_contents($_FILES['foto']['tmp_name']);
    $name = $_FILES['foto']['name'];

    // Registra el comentario de conclusión de WO
    $datos['id_wo'] = $id;
    $datos['comentario'] = "CONCLUIR WO: " . $txtconcluir;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Actualiza el estatus de la orden de trabajo a 3 (Concluida) y guarda la foto
    $this->load->library('correos');
    $data = array(
        'WorkOrder_Status' => 3,
        'archivo' => $foto,
        'nombre_archivo' => $name,
    );
    $where['WorkOrder_ID'] = $id;
    $this->MLConexion->modificar('WO_Master', $data, null, $where);

    // Marca la fecha de conclusión en los ítems que no fueron rechazados
    $rs_items = $this->MLConexion->consultar("SELECT * from WO_Detail where WorkOrder_ID=" . $id);
    foreach ($rs_items as $key) {
        if ($key->Item_Status != 6) {
            $this->MLConexion->comando("UPDATE rsitems SET FechaConcluidaWO = CURRENT_TIMESTAMP() WHERE item_id = '" . $key->Item_Id . "'");
        }
    }

    // Recupera correos del usuario actual y su jefe
    $res = $this->Conexion->consultar("SELECT u.id, u.correo, cj.correo as correo_jefe from usuarios u JOIN usuarios cj on cj.id=u.jefe_directo WHERE u.id=" . $this->session->id, true);

    // Prepara datos del correo de notificación de conclusión
    $mail['usuario'] = $this->session->nombre;
    $mail['items'] = $this->MLConexion->consultar("SELECT wo.*, e.Status_Descripcion FROM WO_Detail wo JOIN tblStatusWO e on e.Status_ID=wo.Item_Status where wo.WorkOrder_ID = " . $id);
    $mail['wo'] = $id;

    // Obtiene correos de los usuarios con permiso para cerrar WO
    $correo = [];
    $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.cerrar_wo = 1 and U.activo = 1");
    foreach ($correos_a as $key => $value) {
        array_push($correo, $value->correo);
    }

    // Agrega correo del usuario actual a la lista de destinatarios
    $mail['correos'] = array_merge(array($this->session->correo), $correo);

    // Envía el correo
    $this->correos->concluir_wo($mail);

    // Redirige a la vista de la orden concluida
    redirect(base_url('ordenes_trabajo/ver_wo/' . $id));
}

    function cancelar_wo()
{
    $id = $this->input->post('itemCancelarWO');
    $txtcancelar = $this->input->post('txtcancelar');

    // Registra el comentario de cancelación de la WO
    $datos['id_wo'] = $id;
    $datos['comentario'] = "CANCELAR WO: " . $txtcancelar;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Carga librería para enviar correos
    $this->load->library('correos');

    // Actualiza el estatus de la orden a 7 (cancelada)
    $this->MLConexion->comando("UPDATE WO_Master set WorkOrder_Status = 7 where WorkOrder_ID = " . $id);

    // Recorre los detalles de la WO para actualizar cada ítem como cancelado y limpiar su vínculo con rsitems
    $res = $this->MLConexion->consultar("SELECT * from WO_Detail where WorkOrder_ID = " . $id);
    foreach ($res as $key) {
        $this->MLConexion->comando("UPDATE WO_Detail set Item_Status = 6 where id = " . $key->id);
        $this->MLConexion->comando("UPDATE rsitems set FechaInWO = null, WOrder_ID = null where item_id = " . $key->Item_Id);
    }

    // Prepara información para enviar correo de notificación de cancelación
    $mail['usuario'] = $this->session->nombre;
    $mail['wo'] = $id;
    $correo = [];
    $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.cerrar_wo = 1 and U.activo = 1");
    foreach ($correos_a as $key => $value) {
        array_push($correo, $value->correo);
    }
    $mail['correos'] = array_merge(array($this->session->correo), $correo);
    $this->correos->cancelar_wo($mail);

    // Redirige al detalle de la WO cancelada
    redirect(base_url('ordenes_trabajo/ver_wo/' . $id));
}

    function cerrar_wo()
{
    $this->load->library('correos');
    $foto = null;
    $id = $this->input->post('itemCerrar');
    $txtcerrar = "CERRAR WO: " . $this->input->post('txtcerrar');
    $mail = $this->input->post('mail');

    // Registra el comentario de cierre en la bitácora de la WO
    $datos['id_wo'] = $id;
    $datos['comentario'] = "CERRAR WO: " . $txtcerrar;
    $datos['mail_us'] = $this->session->correo;
    $this->MLConexion->insertar('comentarios_wo', $datos);

    // Cambia el estatus de la WO a 'CERRADA' (estatus 4)
    $this->MLConexion->comando("UPDATE WO_Master set WorkOrder_Status = 4 where WorkOrder_ID = " . $id);

    // Si se debe enviar correo, prepara y envía con archivo adjunto
    if ($mail == 1) {
        $rs = $this->MLConexion->consultar("SELECT rs, archivo, nombre_archivo from WO_Master where WorkOrder_ID = " . $id, true);
        $mail = $this->MLConexion->consultar("SELECT email from rsheaders where folio_id = " . $rs->rs, true);

        $ext = strrchr($rs->nombre_archivo, '.');
        $fichero = sys_get_temp_dir() . '/' . $rs->rs . $ext;
        $foto = file_put_contents($fichero, $rs->archivo);

        $datos['correo'] = $mail->email;
        $datos['correo_us'] = $this->session->correo;
        $datos['foto'] = $fichero;
        $datos['usuario'] = $this->session->nombre;
        $datos['wo'] = $id;

        $this->correos->cerrar_wo($datos);
    }

    // Redirige a la vista de la WO correspondiente
    redirect(base_url('ordenes_trabajo/ver_wo/' . $id));
}

    function ajax_editSolicitud(){
    $solicitud = json_decode($this->input->post('solicitud'));
    $other = json_decode($this->input->post('other'));

    // Verifica si se adjuntó el archivo de acuse y lo guarda como binario
    if(isset($_FILES['f_A'])) {
        $solicitud->f_acuse = file_get_contents($_FILES['f_A']['tmp_name']);
    }

    // Verifica si se adjuntó el archivo de factura (PDF) y guarda su contenido y nombre
    if(isset($_FILES['f_F'])) {
        $solicitud->f_factura = file_get_contents($_FILES['f_F']['tmp_name']);
        $solicitud->name_factura = $this->input->post('f_F_name');
    }

    // Verifica si se adjuntó el archivo XML y guarda su contenido y nombre
    if(isset($_FILES['f_X'])) {
        $solicitud->f_xml = file_get_contents($_FILES['f_X']['tmp_name']);
        $solicitud->name_xml = $this->input->post('f_X_name');
    }

    $comentario = $this->input->post('comentario');

    // Actualiza la solicitud de factura en la base de datos
    $res = $this->Conexion->modificar('solicitudes_facturas', $solicitud, null, array('id' => $solicitud->id));

    // Si la factura fue aceptada, actualiza el campo Factura en rsitems si está nulo
    if($solicitud->estatus_factura == "ACEPTADO") {
        $this->load->model('MLConexion_model', 'MLConexion');
        $this->MLConexion->comando("UPDATE rsitems set Factura = ifnull(Factura, $solicitud->folio) where Solicitud_ID = $solicitud->id;");
    }

    if($res > 0) {
        // Si se agregó un comentario, lo guarda en la tabla de comentarios con marca de tiempo
        if(isset($_POST['comentario']) && !empty($comentario)) {
            $this->Conexion->insertar('solicitudes_facturas_comentarios', array('solicitud' => $solicitud->id, 'usuario' => $this->session->id, 'comentario' => $comentario), array('fecha' => 'CURRENT_TIMESTAMP()'));
            $solicitud->comentario = $comentario;
        }

        // Obtiene los correos de usuarios con permiso para responder facturas
        $correos = [];
        $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.responder_facturas = 1");
        foreach ($correos_a as $key => $value) {
            array_push($correos, $value->correo);
        }

        // Prepara los datos para el correo de notificación
        $solicitud->correos = array_merge(array($this->session->correo), $correos);
        $solicitud->User = $other->User;
        $solicitud->Client = $other->Client;
        $solicitud->Contact = $other->Contact;

        $mail['id'] = $solicitud->id;
        $mail['estatus_factura'] = $solicitud->estatus_factura;
        $mail['comentario'] = $solicitud->comentario;
        $mail['User'] = $other->User;
        $mail['Client'] = $other->Client;
        $mail['Contact'] = $other->Contact;
        $mail['correos'] = array_merge(array($this->session->correo), $correos);

        // Envía correo notificando la edición de la solicitud
        $this->correos_facturacion->editar_solicitud($mail);
        echo "1";
    } else {
        echo "";
    }
}

    function ajax_getReporteServicios(){
    $this->load->model('MLConexion_model', 'MLConexion');

    $texto = $this->input->post('texto');
    $rs = $this->input->post('rs');

    // Consulta para obtener todos los ítems de un RS (con condiciones)
    $query = "SELECT rsitems.folio_id, rsitems.descripcion, rsn.Notas, concat(rsitems.descripcion, if(isnull(rsitems.Fabricante), '', concat(' ', rsitems.Fabricante)), if(isnull(rsitems.Modelo), '', concat(' ', rsitems.Modelo)), if(isnull(rsitems.Serie), '', concat(' Serie: ', rsitems.Serie)), if(isnull(rsitems.Equipo_ID), '', concat(' ID: ', rsitems.Equipo_ID))) as CadenaDescripcion, rsitems.item_id, rsitems.fechaCancelado, rsitems.inWork, rsitems.tecnico_id, rsitems.fechaActEQ, rsitems.WOrder_ID, catalogo_tecnicos.email_tecnico FROM rsitems INNER JOIN catalogo_tecnicos ON rsitems.tecnico_id = catalogo_tecnicos.Tecnico_Id join rsheadersnotas rsn on rsn.Folio_ID=rsitems.folio_id WHERE (((rsitems.folio_id)='".$rs."') AND ((rsitems.fechaCancelado) Is Null) AND ((rsitems.inWork)=0) AND ((rsitems.fechaActEQ) Is Null) AND ((rsitems.WOrder_ID) Is Null) AND ((catalogo_tecnicos.email_tecnico)= '".$this->session->correo."'))";

    // Si se proporciona un texto, se aplica un filtro adicional sobre la cadena descriptiva
    if($texto) {
        $query .= " having CadenaDescripcion like '%$texto%'";
    }

    $res = $this->MLConexion->Consultar($query);

    // Devuelve los resultados en formato JSON si existen
    if($res){
        echo json_encode($res);
    }
}

function ajax_getRSItems(){
    $id_factura = $this->input->post('id_factura');

    // Consulta para obtener los ítems asociados a una factura específica
    $res = $this->Conexion->Consultar("SELECT * from rsitems_facturas where id_factura = $id_factura");

    // Devuelve los resultados en formato JSON si existen
    if($res){
        echo json_encode($res);
    }
}

    function ajax_setComentarios(){
    // Decodifica el comentario recibido vía POST en formato JSON
    $comentario = json_decode($this->input->post('comentario'));
        $comentario->mail_us = $this->session->correo;
    $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');
    $res = $this->MLConexion->insertar('comentarios_wo', $comentario, $funciones);

    // Devuelve "1" si la operación fue exitosa, vacío si falló
    if($res > 0) {
        echo "1";
    } else {
        echo "";
    }
}

function ajax_getComentarios(){
    $id = $this->input->post('id');

    // Consulta los comentarios de la orden de trabajo junto al nombre del técnico
    $query = "SELECT cm.*, ct.nombre FROM comentarios_wo cm JOIN catalogo_tecnicos ct on cm.mail_us=ct.email_tecnico where 1 = 1";

    // Si se recibe un ID de orden de trabajo, se filtra por él
    if($id) {
        $query .= " and cm.id_wo = '$id'";
    }

    $res = $this->MLConexion->consultar($query);

    // Devuelve los resultados en formato JSON si existen, vacío en caso contrario
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function ajax_getRequisitores(){
        $id = $this->input->post('id');

        $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Nombre, P.puesto as Puesto from usuarios U inner join puestos P on U.puesto = P.id inner join privilegios PR on PR.usuario = U.id where U.activo = 1 and PR.solicitar_facturas = 1";

        if($id)
        {
            $query .= " and U.id = '$id'";
        }

        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function ajax_getVFPData(){
        $modelo = $this->input->post('modelo');
        $res = shell_exec("C:/xampp/htdocs/MASMetrologia/vfp_reader/vfp_reader.exe \"$modelo\"");
        echo $res;
    }

    function archivo_impresion(){
    $pdf = new PDFMerger;
    ob_start();
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    /* ...
    Resto del código que genera el PDF
     ... */
    /* Limpiamos la salida del búfer y lo desactivamos */
     ob_end_clean();
        $id = $this->input->post('id');
        $codigo = $this->input->post('codigo');
        
                         ob_start();
    error_reporting(E_ALL & ~E_NOTICE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    /* ...
    Resto del código que genera el PDF
    ... */
    /* Limpiamos la salida del búfer y lo desactivamos */
  
    $pdf = new PDFMerger();
    $q="SELECT SF.* from solicitudes_facturas SF where SF.id = $id";
    $res = $this->Conexion->consultar($q, TRUE);

        for ($i=0; $i < strlen($codigo); $i++) { 
        // Determina el tipo de documento según el carácter leído

            switch (strtoupper($codigo[$i])) {
                
                case 'F':
                    $campo = 'f_factura';
                    break;
    
                case 'R':
                    $campo = 'f_remision';
                    break;
    
                case 'O':
                    $campo = 'f_orden_compra';
                    break;
    
                case 'A':
                    $campo = 'f_acuse';
                    break;

                case 'P':
                    $campo = 'OPINION';
                    break;

                case 'S':
                    $campo = 'EMISI ON';
                    break;

                default:
                    $campo = null;
                    break;
            }

        // Si se reconoció un campo válido
        if($campo != null) {

        // Si es un archivo binario guardado en base de datos (prefijo "f_")
        if(substr($campo, 0, 2 ) == "f_") {

            $file = $res->$campo; // Recupera archivo desde el objeto
            $fichero = sys_get_temp_dir(). '/' . $campo . '.pdf'; // Ruta temporal
            file_put_contents($fichero, $file); // Escribe archivo temporal
            $pdf->addPDF($fichero, 'all'); // Agrega PDF generado al combinador

        } else {

            // Si es archivo precargado del sistema de archivos
            $fichero = "data/empresas/documentos_globales/" . $campo . "_000001.pdf";
            $pdf->addPDF($fichero, 'all'); // Agrega PDF externo
        }
    }
}

    ob_end_clean();
       $pdf->merge('browser');       
    }

    function ajax_getClientes(){
        
        $query = "SELECT C.id, C.nombre, C.razon_social, C.foto, C.opinion_positiva, C.emision_sua from empresas C where C.cliente = 1";

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

    function ajax_getClientesSolicitudes(){
    $texto = $this->input->post('texto');

    // Consulta para obtener clientes y cantidad de solicitudes asociadas
    $query = "SELECT E.id, E.nombre, count(S.id) as NumSol from solicitudes_facturas S inner join empresas E on E.id = S.cliente";

    // Aplica filtro si se ingresó texto de búsqueda
    if($texto)
    {
        $query .= " where E.nombre like '%$texto%'";
    }

    // Agrupa por ID de empresa para contar solicitudes
    $query .= " group by E.id;";

    $res = $this->Conexion->consultar($query);

    if($res)
    {
        echo json_encode($res); // Devuelve los resultados en formato JSON
    }
}

function ajax_getEjecutivosSolicitudes(){
    $texto = $this->input->post('texto');

    // Consulta para obtener ejecutivos y cantidad de solicitudes que gestionaron
    $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Ejecutivo, count(S.id) as NumSol from solicitudes_facturas S inner join usuarios U on U.id = S.ejecutivo";

    // Aplica filtro por nombre si se proporcionó
    if($texto)
    {
        $query .= " where concat(U.nombre, ' ', U.paterno) like '%$texto%'";
    }

    // Agrupa por ID de usuario para contar solicitudes
    $query .= " group by U.id;";

    $res = $this->Conexion->consultar($query);

    if($res)
    {
        echo json_encode($res); // Devuelve los resultados en formato JSON
    }
}

    function ajax_getDocumentosGlobales(){
    // Consulta los documentos globales registrados (solo uno, con id = 1)
    $query = "SELECT id, opinion_positiva, emision_sua from documentos_globales where id = 1";

    $res = $this->Conexion->consultar($query);
    if($res)
    {
        echo json_encode($res); // Devuelve los resultados en formato JSON
    }
    else
    {
        echo ""; // Respuesta vacía si no hay datos
    }
}

function ajax_filesExists($id){
    $this->load->helper('file'); // Carga helper para funciones de archivos

    // Rellena con ceros a la izquierda para obtener un ID de 6 dígitos
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Verifica si existen los archivos PDF de acuse y emisión para la empresa
    $acuse = read_file(base_url("data/empresas/documentos_facturacion/ACUSE_" . $id . ".pdf")) ? "1" : "0";
    $emision = read_file(base_url("data/empresas/documentos_facturacion/EMISION_" . $id . ".pdf")) ? "1" : "0";

    // Devuelve un arreglo indicando si cada archivo existe ("1") o no ("0")
    echo json_encode(array($acuse, $emision));
}

    function ajax_readXML(){
    // Crea una instancia del objeto DOMDocument para leer el XML
    $dom = new DomDocument;
    $dom->preserveWhiteSpace = FALSE;

    // Carga el contenido del archivo XML subido
    $dom->loadXML(file_get_contents($_FILES['f_X']['tmp_name']));

    // Obtiene el nodo <Comprobante> del XML
    $comp = $dom->getElementsByTagName('Comprobante');
    $ext = 1; // Bandera para validar si el folio ya existe
    $data = array(); // Arreglo para guardar los datos extraídos del XML

    // Recorre los atributos del nodo <Comprobante>
    foreach ($comp[0]->attributes as $elem)
    {
        // Si el atributo es Serie, Folio o SubTotal, lo guarda en $data
        if($elem->name == "Serie" | $elem->name == "Folio" | $elem->name == "SubTotal")
        {
            $e = array($elem->name => $elem->value);
            array_push($data, $e);
        }

        // Si el atributo es Folio, verifica si ya existe una solicitud con ese folio
        if($elem->name == "Folio")
        {
            $folio = $elem->value;
            $res = $this->Conexion->consultar("SELECT count(*) as existe FROM solicitudes_facturas where folio = '$folio'", TRUE);
            $ext = $res->existe;
        }
    }

    // Si el folio no existe en la base de datos, devuelve los datos del XML
    if($ext == 0)
    {
        echo json_encode($data);
    }
    else
    {
        // Si el folio ya existe, retorna "0" indicando duplicado
        echo "0";
    }
}

    function ajax_setDocumentoFacturacion(){
    $file = $this->input->post('file');
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');
    $id = str_pad($id, 6, "0", STR_PAD_LEFT); // Completa el ID a 6 dígitos con ceros a la izquierda

    if($file != "undefined")
    {
        // Configura la carga del archivo PDF
        $config['upload_path'] = 'data/empresas/documentos_facturacion/';
        $config['allowed_types'] = 'pdf';
        $config['overwrite'] = TRUE;
        $config['file_name'] = $documento . '_' . $id;

        $this->load->library('upload', $config);

        // Intenta subir el archivo
        if ($this->upload->do_upload('file'))
        {
            $where['id'] = $id;

            // Asigna el campo correspondiente según el tipo de documento
            switch($documento)
            {
                case "EMISION":
                    $campo = "emision_sua";
                    break;
                case "OPINION":
                    $campo = "opinion_positiva";
                    break;
            }

            // Guarda el nombre del archivo en la base de datos
            $data[$campo] = $this->upload->data('file_name');
            $this->Conexion->modificar('empresas', $data, null, $where);
            echo "1"; // Indica éxito al frontend
        }
    }
}

    function ajax_deleteDocumentoFacturacion(){
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');
    $id = str_pad($id, 6, "0", STR_PAD_LEFT); // Asegura que el ID tenga 6 dígitos

    // Elimina físicamente el archivo PDF correspondiente
    unlink('data/empresas/documentos_facturacion/' . $documento . '_' . $id . '.pdf');

    // Determina el campo que se debe actualizar como vacío
    $where['id'] = $id;
    switch($documento)
    {
        case "EMISION":
            $campo = "emision_sua";
            break;
        case "OPINION":
            $campo = "opinion_positiva";
            break;
    }

    // Actualiza el campo a vacío en la base de datos
    $data[$campo] = "";
    $this->Conexion->modificar('empresas', $data, null, $where);
}

function ajax_setDocumentoGlobal(){
    $file = $this->input->post('file');
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');
    $id = str_pad($id, 6, "0", STR_PAD_LEFT); // Asegura que el ID tenga 6 dígitos

    if($file != "undefined")
    {
        // Configuración de la subida del archivo
        $config['upload_path'] = 'data/empresas/documentos_globales/';
        $config['allowed_types'] = 'pdf';
        $config['overwrite'] = TRUE;
        $config['file_name'] = $documento . '_' . $id;

        $this->load->library('upload', $config);

        // Intenta subir el archivo
        if ($this->upload->do_upload('file'))
        {
            $where['id'] = $id;

            // Determina el campo que se debe actualizar
            switch($documento)
            {
                case "EMISION":
                    $campo = "emision_sua";
                    break;
                case "OPINION":
                    $campo = "opinion_positiva";
                    break;
            }

            // Guarda el nombre del archivo en la base de datos
            $data[$campo] = $this->upload->data('file_name');
            $this->Conexion->modificar('documentos_globales', $data, null, $where);
            echo "1"; // Notifica éxito al frontend
        }
    }
}

    function ajax_deleteDocumentoGlobal(){
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');
    $id = str_pad($id, 6, "0", STR_PAD_LEFT); // Asegura que el ID tenga 6 dígitos con ceros a la izquierda

    // Elimina físicamente el archivo PDF correspondiente del sistema de archivos
    unlink('data/empresas/documentos_globales/' . $documento . '_' . $id . '.pdf');

    // Determina el campo correspondiente que se debe actualizar como vacío en la base de datos
    $where['id'] = $id;
    switch($documento)
    {
        case "EMISION":
            $campo = "emision_sua";
            break;
        case "OPINION":
            $campo = "opinion_positiva";
            break;
    }

    // Limpia el campo en la base de datos que hacía referencia al archivo eliminado
    $data[$campo] = "";
    $this->Conexion->modificar('documentos_globales', $data, null, $where);
}

    function ajax_enviarCorreo(){
    $id = $this->input->post('id');
    $body = $this->input->post('body');
    $subject = $this->input->post('subject');
    $para = $this->input->post('para');
    $cc = $this->input->post('cc');
    
    $campos = json_decode($this->input->post('campos'));

    $archivos = [];

    // Obtiene los datos de la solicitud de factura por ID
    $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);

    foreach ($campos as $value) {
        // Si el campo es un archivo binario de la tabla (f_pdf, f_xml, etc.)
        if(substr($value, 0, 2 ) == "f_") {
            $file = $res->$value;
            $fichero = sys_get_temp_dir(). '/' . $value . ($value == "f_xml" ? '.xml' : '.pdf');
            file_put_contents($fichero, $file); // Guarda el archivo temporalmente
        } else {
            // Para documentos globales con nombres codificados
            switch ($value) {
                case 'opinion_positiva':
                    $value = 'OPINION';
                    break;
                case 'emision_sua':
                    $value = 'EMISION';
                    break;
            }
            $fichero = "data/empresas/documentos_globales/" . $value . "_000001.pdf";
        }

        array_push($archivos, $fichero); // Añade el archivo al array de adjuntos
    }

    // Prepara el array con todos los datos del correo
    $datos['id'] = $id;
    $datos['para'] = $para;
    $datos['cc'] = $cc;
    $datos['subject'] = $subject;
    $datos['body'] = $body;
    $datos['campos'] = $campos;
    $datos['archivos'] = $archivos;

    // Llama a la librería de envío de correos
    $this->correos_facturacion->enviarCorreo($datos);
}

    ////////////////////////////////// F A C T U R A S //////////////////////////////////
function ajax_getFacturas(){
    $id = $this->input->post('id');

    // Consulta principal para obtener detalles de las facturas aceptadas
    $query = "SELECT F.id, F.fecha, F.usuario, F.cliente, F.contacto, F.reporte_servicio, F.orden_compra, F.forma_pago, F.pagada, F.conceptos, F.notas, F.estatus_factura, F.documentos_requeridos, F.serie, F.folio, F.codigo_impresion, (SELECT count(id) from recorrido_conceptos where id_concepto = F.id and tipo = 'FACTURA') as Recorridos, (SELECT count(id) from envios_factura where factura = F.id) as Envios, E.nombre as Cliente, concat(U.nombre, ' ', U.paterno) as User, U.correo, ifnull(EC.correo, 'N/A') as CorreoContacto from solicitudes_facturas F inner join empresas E on E.id = F.cliente inner join usuarios U on U.id = F.usuario left join empresas_contactos EC on EC.id = F.contacto";
    $query .= " where F.folio > 0 and F.estatus = 'ACEPTADO'";

    // Filtra por ID si se proporciona
    if($id) {
        $query .= " and F.id = '$id'";
    }

    // Filtro adicional opcional por estatus
    if(isset($_POST['estatus'])) {
        $estatus = $this->input->post('estatus');
        $query .= " and F.estatus = '$estatus'";
    }

    $res = $this->Conexion->consultar($query, $id);
    if($res) {
        echo json_encode($res);
    }
}


////////////////////////////////// L O G I S T I C A //////////////////////////////////
function ajax_getMensajeros(){
    // Consulta para obtener todos los usuarios con privilegio de mensajero activos
    $query = "SELECT U.id, U.nombre, U.paterno, U.materno, U.no_empleado, U.puesto, U.correo, U.ultima_sesion, U.departamento, U.activo, U.jefe_directo, U.autorizador_compras, U.autorizador_compras_venta, concat(U.nombre, ' ', U.paterno) as User, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as CompleteName from usuarios U inner join privilegios P on P.usuario = U.id where U.activo = '1'";
    $query .= " and P.mensajero = '1'";
    $query .= " order by User";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

    function ajax_setRecorrido(){
    $mensajero = $this->input->post('mensajero');
    $fecha = $this->input->post('fecha');
    $recorrido = json_decode($this->input->post('recorrido'));

    // Inserta cabecera del recorrido
    $data['mensajero'] = $mensajero;
    $data['fecha_recorrido'] = $fecha;
    $recorrido_id = $this->Conexion->insertar('recorridos', $data);

    // Por cada factura en el recorrido, crea detalle y actualiza su estatus
    foreach ($recorrido as $value) {
        $data2['recorrido'] = $recorrido_id;
        $data2['factura'] = $value[1];
        $data2['accion'] = $value[0];
        $data2['estatus'] = "EN RECORRIDO";

        $this->Conexion->insertar('recorrido_conceptos', $data2);
        $this->Conexion->modificar('solicitudes_facturas', array('estatus' => $data2['estatus']), null, array('id' => $data2['factura']));
    }
}

function ajax_getRecorridos(){
    $pendientes = $this->input->post('pendientes');
    $factura = $this->input->post('factura');

    // Consulta de todos los recorridos y sus detalles, con subconsultas para comentarios y pendientes
    $query = "SELECT RF.*, R.mensajero, R.fecha_recorrido, (SELECT count(RC.id) from recorrido_comentarios RC where RC.recorrido_factura = RF.id) as Comentarios, (SELECT count(RF2.id) from recorrido_facturas RF2 where RF2.recorrido = RF.recorrido and RF2.estatus = 'EN RECORRIDO') as Pendientes, (SELECT E.nombre from solicitudes_facturas F inner join empresas E on E.id = F.cliente where F.id = RF.factura) as Cliente, ifnull(concat(M.nombre, ' ', M.paterno), 'N/A') as Mensajero from recorrido_facturas RF inner join recorridos R on R.id = RF.recorrido left join usuarios M on M.id = R.mensajero where 1 = 1";

    // Filtra por factura específica si se envía
    if(isset($_POST['factura'])) {
        $query .= " and RF.factura = $factura";
    }

    // Filtra para mostrar solo recorridos con facturas pendientes
    if($pendientes == "1") {
        $query .= " having Pendientes > 0";
    }

    // Ordena resultados por fecha de recorrido, ID del recorrido y ID de la factura dentro del recorrido
    $query .= " order by R.fecha_recorrido, R.id, RF.id asc";

    $res = $this->Conexion->consultar($query);
    echo json_encode($res);
}

    function ajax_updateRecorrido(){
    $recorrido = json_decode($this->input->post('recorrido')); // Decodifica los datos del recorrido
    $recolecta = $this->input->post('recolecta'); // Indica si el estatus final es recolección
    $comentario = $this->input->post('comentario'); // Comentario del usuario

    // Actualiza los datos del recorrido en la tabla correspondiente
    $this->Conexion->modificar('recorrido_facturas', $recorrido, null, array('id' => $recorrido->id));

    // Si el estatus inicia con "NO", se considera como pendiente
    if(substr($recorrido->estatus, 0, 2) == "NO") {
        $estat = "PENDIENTE " . $recorrido->accion; // Marca como pendiente la acción (ej. ENTREGA o RECOLECCIÓN)
        $this->Conexion->modificar('solicitudes_facturas', array('estatus' => $estat), null, array('id' => $recorrido->factura));
    } else {
        // Si recolecta está activa, el nuevo estatus es "PENDIENTE RECOLECTA", si no, es "CERRADO"
        $estat = $recolecta == "1" ? "PENDIENTE RECOLECTA" : "CERRADO";
        $this->Conexion->modificar('solicitudes_facturas', array('estatus' => $estat), null, array('id' => $recorrido->factura));
    }

    // Si se ingresó un comentario, se guarda en la tabla de comentarios del recorrido
    if($comentario) {
        // Define color del comentario: rojo para negativos, verde para positivos
        $color = substr($recorrido->estatus, 0, 2) == "NO" ? "red" : "green";
        $comentario = '<font color=' . $color . '><b>' . $recorrido->estatus . ':</b></font> ' . $comentario;

        $data_com['recorrido_factura'] = $recorrido->id;
        $data_com['usuario'] = $this->session->id;
        $data_com['comentario'] = $comentario;
        $func_com['fecha'] = "CURRENT_TIMESTAMP()";

        // Inserta el comentario en la base de datos
        $this->Conexion->insertar('recorrido_comentarios', $data_com, $func_com);
    }
}

    function ajax_getComentariosRecorrido(){
    // Se recibe el ID del recorrido de la factura mediante POST
    $id = $this->input->post('id');

    // Consulta los comentarios asociados al recorrido de factura, junto con el nombre del usuario que los hizo
    $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from recorrido_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

    // Si se proporcionó un ID específico, se filtra por ese recorrido
    if($id) {
        $query .= " and C.recorrido_factura = '$id'";
    }

    // Ordena los resultados cronológicamente por fecha
    $query .= " order by C.fecha";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

    function ver_POD($id){
        //Se obtiene la información principal de la solicitud
        $query = "SELECT sf.id, sf.folio,sf.serie, sf.reporte_servicio, sf.fecha, sf.orden_compra, concat(u.nombre, ' ', u.paterno) as responsable, e.razon_social, concat(e.calle, ' ',e.numero, ' CP ',e.cp) as direccion,e.ciudad, e.estado, e.rfc,e.colonia, ec.nombre as contacto, ec.correo FROM solicitudes_facturas sf join usuarios u on sf.ejecutivo = u.id JOIN empresas e on sf.cliente = e.id JOIN empresas_contactos ec on sf.contacto = ec.id WHERE sf.id =$id";
        $factura = $this->Conexion->consultar($query, TRUE);

        //Se consultan los equipos relacionados a la factura para mostrarlos en el documento
        $query2 = "SELECT rs.descripcion, rs.Equipo_ID, rs.Fec_CalibracionMT, s.DescripcionDeServicio, concat(rs.descripcion, if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)) ) as CadenaDescripcion from rsitems rs JOIN catalogo_servicios s on s.servicio_id = rs.item_servicio_id WHERE rs.Solicitud_ID =".$id." and rs.Factura = '".$factura->folio."'";
        $rs = $this->MLConexion->consultar($query2);
        $total=0;
        foreach($rs as $row){
            $total++;
        }
        ini_set('display_errors', 0);
        $this->load->library('pdfview');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       

        $f=$factura->serie . "-".$factura->folio; 
        //Configura el encabezado con folio, responsable y fecha    
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AleksOrtiz');
        $pdf->SetTitle('Masmetrologia');
        $pdf->SetSubject('Formato Cotización');
        $spc = "           ";
        $head = "$spc           Prueba de Entrega      $spc Folio: $id / Factura: $f";
        $txt = "                             Proof of delivery                       Responsable: " . $factura->responsable;
        $txt .= "\n                                                                                Fec. de elaboración:: " . $factura->fecha;
       
        $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', $head, $txt);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('helvetica', '', 8);

        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 10);

        //Construccion de la tabla HTML con los datos del cliente, dirección, contacto y total de equipos
        $tbl = <<<EOD
        <br>
            <table border="0">
                <tr>
                    <td>
                        <b>Cliente/Customer:</b><br>
                        $factura->razon_social<br>
                        $factura->direccion<br>
                        $factura->colonia<br>
                        $factura->ciudad, $factura->estado<br>
                        RFC: $factura->rfc<br>
                        
                    </td>
                    <td>
EOD;


        $tbl .= <<<EOD
        <b>Orden de Compra: </b>
        $factura->orden_compra<br>
        <b>Contacto / Contact: </b><br>
        $factura->contacto<br>
        $factura->correo<br>
        <br>        
        Total de Equipo(s):  $total<br>
        </td>
        </tr>
    </table>
EOD;

        $pdf->writeHTML($tbl, false, false, false, false, '');
        $w = array(8, 125, 12, 24, 24);


        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(255);
        $pdf->Ln();$pdf->Ln();
        $w = array(20, 20, 125, 20);

        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln();
        $tabla_items='';

            $tabla_items .= '<table style=" ">
                            <thead> 
                                <tr>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Servicio</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Equipo ID</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 55%;">Descripcion</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Realizado</th>
                                </tr>
                            </thead>
                            <tbody>';
         foreach($rs as $row){
            $date=date_create($row->Fec_CalibracionMT);
            $tabla_items .='
                        <tr>
                            <td style="border-bottom: 1px solid #000; text-align: center; width: 15%; tr:nth-child:background: #F8F8F8;">'.$row->DescripcionDeServicio .'</td>
                            <td style="border-bottom: 1px solid #000; text-align: center; width: 15%; tr:nth-child:background: #F8F8F8;">'.$row->Equipo_ID.'</td>
                            <td style="border-bottom: 1px solid #000; text-align:center ; width: 55%; tr:nth-child:background: #F8F8F8;">'.$row->CadenaDescripcion.'</td>
                            <td style="border-bottom: 1px solid #000; text-align: center;width: 15%; tr:nth-child:background: #F8F8F8;">'.date_format($date,'d/M/Y').'</td>
                        </tr>';
             }
             $tabla_items .= '</tbody>
                </table>
                <br> 
                <br> 
                <br> 
                <br> 
                <br> 
                <br> ';

        $pdf->writeHTML($tabla_items, true, false, false, false, '');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(150, 8,"Recibí el servicio a los equipos arriba listados", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->writeHTML("_________________________", true, false, false, false, '');
        $pdf->MultiCell(150, 8,"Firma de recibido", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->Ln();
        $name = "POD ".$id." - PO ".$factura->orden_compra.'.pdf';
  
        $pdf->Ln();
        
        //El PDF generado se envía al navegador para su visualización.
        $pdf->Output(sys_get_temp_dir().'/'.$name, 'I');
    }

function ajax_enviarCorreoPOD(){
        //Recepción de parámetros del formulario
        $id = $this->input->post('id');
        $body = $this->input->post('body');
        $subject = $this->input->post('subject');
        $para = $this->input->post('para');
        $cc = $this->input->post('cc');
        $certificados=[];
        $pdfname=null;
        $xmlname =null;
        $campos = json_decode($this->input->post('campos'));
        $archivos = [];
        $name_files = [];
        //Consulta principal de la solicitud de factura
        $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);
        $pdfname=$res->name_factura;
        $xmlname=$res->name_xml;
        foreach ($campos as $value) {

            if(substr($value, 0, 2 ) == "f_")
            {
                $file = $res->$value;

                $fichero = sys_get_temp_dir(). '/' . $value . ($value == "f_xml" ? '.xml' : '.pdf');
                $name =$value == "f_xml" ? $res->name_xml : $res->name_factura;
                 file_put_contents($fichero, $file);
            }
            else
            {
                switch ($value) {
                    case 'opinion_positiva':
                        $value = 'OPINION';
                        break;
                    
                    case 'emision_sua':
                        $value = 'EMISION';
                        break;
                }
                $fichero = "data/empresas/documentos_globales/" . $value . "_000001.pdf";
            }

            array_push($archivos, $fichero);
            
        }
        //Consulta de datos de cliente y factura
        $query = "SELECT sf.id, sf.folio,sf.serie, sf.reporte_servicio, sf.fecha, sf.orden_compra, concat(u.nombre, ' ', u.paterno) as responsable, e.razon_social, concat(e.calle, ' ',e.numero, ' CP ',e.cp) as direccion,e.ciudad, e.estado, e.rfc,e.colonia, ec.nombre as contacto, ec.correo FROM solicitudes_facturas sf join usuarios u on sf.ejecutivo = u.id JOIN empresas e on sf.cliente = e.id JOIN empresas_contactos ec on sf.contacto = ec.id WHERE sf.id =$id";
        $factura = $this->Conexion->consultar($query, TRUE);

        //Consulta de equipos incluidos en la factura
        $query2 = "SELECT rs.descripcion, rs.Equipo_ID, rs.documento_id, rs.Fec_CalibracionMT, s.DescripcionDeServicio, concat(if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)) ) as CadenaDescripcion from rsitems rs JOIN catalogo_servicios s on s.servicio_id = rs.item_servicio_id WHERE rs.Solicitud_ID =".$id." and rs.Factura = '".$factura->folio."'";
         $rs = $this->MLConexion->consultar($query2);
         $total=0;
        foreach($rs as $row){
            $total++;
        }

        foreach ($rs as $key) {
        array_push($certificados, $key->documento_id);
    }

        ini_set('display_errors', 0);
        $this->load->library('pdfview');

        //Inicialización y configuración del PDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
       


        $f=$factura->serie . "-".$factura->folio; 
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AleksOrtiz');
        $pdf->SetTitle('Masmetrologia');
        $pdf->SetSubject('Formato Cotización');
        $spc = "           ";
        $head = "$spc           Prueba de Entrega      $spc Folio: $id / Factura: $f";
        $txt = "                             Proof of delivery                       Responsable: " . $factura->responsable;
        $txt .= "\n                                                                                Fec. de elaboración:: " . $factura->fecha;
       

        $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', $head, $txt);


        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('helvetica', '', 8);

        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 10);
        
        //Construcción del contenido del PDF
        $tbl = <<<EOD
        <br>
            <table border="0">
                <tr>
                    <td>
                        <b>Cliente/Client:</b><br>
                        $factura->razon_social<br>
                        $factura->direccion<br>
                        $factura->colonia<br>
                        $factura->ciudad, $factura->estado<br>
                        $factura->rfc<br>
                        
                    </td>
                    <td>
EOD;


        $tbl .= <<<EOD
        <b>Orden de Compra: </b>
        $factura->orden_compra<br>
        <b>Contacto / Contact: </b><br>
        $factura->contacto<br>
        $factura->correo<br>
        <br>        
        Total de Equipo(s):  $total<br>
        </td>
        </tr>
    </table>
EOD;

        $pdf->writeHTML($tbl, false, false, false, false, '');
        $w = array(8, 125, 12, 24, 24);


        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(255);
        $pdf->Ln();$pdf->Ln();
        $w = array(20, 20, 125, 20);

        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln();
        $tabla_items='';

            $tabla_items .= '<table style=" ">
                            <thead> 
                                <tr>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Servicio</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Equipo ID</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 55%;">Descripcion</th>
                                    <th style="border-bottom: 1px solid #000; text-align: center; font-weight: bold; width: 15%;">Realizado</th>
                                </tr>
                            </thead>
                            <tbody>';
         foreach($rs as $row){
            $date=date_create($row->Fec_CalibracionMT);
            $tabla_items .='
                         <tr>
                            <td style="border-bottom: 1px solid #000; text-align: center; width: 15%; tr:nth-child:background: #F8F8F8;">'.$row->DescripcionDeServicio .'</td>
                            <td style="border-bottom: 1px solid #000; text-align: center; width: 15%; tr:nth-child:background: #F8F8F8;">'.$row->Equipo_ID.'</td>
                            <td style="border-bottom: 1px solid #000; text-align:center ; width: 55%; tr:nth-child:background: #F8F8F8;">'.$row->CadenaDescripcion.'</td>
                            <td style="border-bottom: 1px solid #000; text-align: center;width: 15%; tr:nth-child:background: #F8F8F8;">'.date_format($date,'d/M/Y').'</td>
                        </tr>';
             }
             $tabla_items .= '</tbody>
                </table>
                <br> 
                <br> 
                <br> 
                <br> 
                <br> 
                <br> ';
        $pdf->writeHTML($tabla_items, true, false, false, false, '');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(150, 8,"Recibí el servicio a los equipos arriba listados", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->writeHTML("_________________________", true, false, false, false, '');
        $pdf->MultiCell(150, 8,"Firma de recibido", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->Ln();
        $name = "POD ".$id." - PO ".$factura->orden_compra.'.pdf';
  
        $pdf->Ln();
        //Generación del PDF
        $pdf->Output(sys_get_temp_dir().'/'.$name, 'F');
        $datos['pod']=sys_get_temp_dir().'/'.$name;

        $datos['id'] = $id;
        $datos['para'] = $para;
        $datos['cc'] = $cc;
        $datos['subject'] = "POD ".$id." - PO ".$factura->orden_compra;
        $datos['body'] = $body;
        $datos['campos'] = $campos;
        $datos['archivos'] = $archivos;
        $datos['certificados'] = $certificados;
        $datos['pdfname']=$pdfname;
        $datos['xmlname']=$xmlname;
        $datos['po']=$factura->orden_compra;
        $this->correos_facturacion->archivos_facturacion($datos);
    }

    public function validar_archivos(){
        // Obtener el ID de la solicitud enviado por POST
        $id=$this->input->post('ID');
        $factura = $this->Conexion->consultar("SELECT id, folio  from solicitudes_facturas where id= ".$id, true);

        $query = " select item_id, documento_id,Fec_CalibracionMT from rsitems where factura = ".$factura->folio." and Solicitud_ID";
        $rs = $this->MLConexion->consultar($query);
        
        if($rs)
        {
            echo json_encode($rs);
        }
        else{
            // Si no se encontraron resultados, regresar una cadena vacía
            echo "";
        }
    }
    function ver_wo_pdf($id)
    {
        $query = "SELECT wo.*, wd.*, rs.folio_id, rs.Localizacion, ei.Status_Descripcion as estatus_item, ew.Status_Descripcion as estatus_wo, concat(rs.descripcion, if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)), if(isnull(rs.Equipo_ID), '', concat(' ID: ', rs.Equipo_ID))) as CadenaDescripcion from WO_Master wo JOIN WO_Detail wd on wo.WorkOrder_ID=wd.WorkOrder_ID JOIN rsitems rs ON rs.item_id=wd.Item_Id JOIN tblStatusWO ei on ei.Status_id = wd.Item_Status JOIN tblStatusWO ew on ew.Status_ID=wo.WorkOrder_Status where wo.WorkOrder_ID = ".$id;
        $wo_detail=$this->MLConexion->consultar($query);
        $query2 = "SELECT wo.*, wd.*,rsn.Notas, we.Status_Descripcion, ct.nombre, rs.folio_id, rh.Empresa, rh.Direccion1, rh.Contacto, rh.TelefonoContacto, rh.CelularContacto from WO_Master wo JOIN WO_Detail wd on wo.WorkOrder_ID=wd.WorkOrder_ID JOIN tblStatusWO we on we.Status_ID=wo.WorkOrder_Status JOIN catalogo_tecnicos ct on ct.email_tecnico=wo.correo_us join rsitems rs on rs.item_id =wd.Item_Id join rsheaders rh on rh.folio_id=rs.folio_id join rsheadersnotas rsn on wo.rs=rsn.Folio_ID where wo.WorkOrder_ID=   ".$id;
        
        // Obtener información general de la orden de trabajo: empresa, contacto, técnico responsable y notas.
        $wo=$this->MLConexion->consultar($query2, TRUE);
        $comentario="SELECT cm.*, ct.nombre FROM comentarios_wo cm JOIN catalogo_tecnicos ct on cm.mail_us=ct.email_tecnico where 1 = 1 and cm.id_wo =".$id;
        
        // Obtener comentarios registrados por el técnico para esta orden de trabajo.
        $cm=$this->MLConexion->consultar($comentario);

        $qr =  '<img height="100" width="100" src="'. base_url('data/wo/'.$wo->qr).'"/>';

        ini_set('display_errors', 0);
        $this->load->library('pdfview');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AleksOrtiz');
        $pdf->SetTitle('Masmetrologia');
        $pdf->SetSubject('Formato Cotización');
        $spc = "                                         ";
        $head = "$spc                              Orden De Servicio      $spc Folio: $id";
        $txt = "$spc$spc Work Order                  $spc    Responsable: " . $wo->nombre;
        $txt .= "\n       $spc    $spc                                                                        Fec. de Programada: " . $wo->FProgramado;
        $txt .= "\n         $spc  $spc                                                                        RS: " . $wo->folio_id;
       

        $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', $head, $txt);


        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('helvetica', '', 8);

        $pdf->AddPage('L', array('format' => 'A4'));

        $pdf->SetFillColor(255, 255, 255);
        $pdf->SetFont('helvetica', '', 10);
        
        //Construcción del contenido del PDF
         $tbl = <<<EOD
        <br>
            <table>
               <thead>
                  <tr>
                     <th>
                           <b>Cliente/Customer:</b><br>
                           $wo->Empresa<br>
                           $wo->Direccion1<br>
                           $wo->Contacto<br>
                           Telefono: $wo->TelefonoContacto Celular: $wo->CelularContacto<br>
                     </th>
                      <th>
                     </th>

                     <th>
                     $qr
                     </th>
                  </tr>
               </thead>
            </table>
  

EOD;

        $pdf->writeHTML($tbl, true, true, true, true, '');
        
        $w = array(8, 125, 12, 24, 24);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(255);
        $pdf->Ln();$pdf->Ln();
        $w = array(20, 20, 125, 20);
        $pdf->SetTextColor(0);
        $pdf->SetFont('helvetica', '', 10);
            $pdf->Ln();
        $tabla_items='';

            $tabla_items .= '<table >
                            <thead> 
                                <tr>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold; width: 10%;" >RS</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold; width: 10%;" >Item</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 20%;" >Descripcion</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Loc.</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Venc.</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Cal.</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Tipo</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Motivo</th>
                                                      <th style="border: 1px solid #000; text-align: center; font-weight: bold;width: 10%;" >Estatus</th>
                              </tr>
                            </thead>
                            <tbody>';
         foreach ($wo_detail as $key){
           
            $tabla_items .='
                        <tr>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->folio_id.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->Item_Id.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 20%;">'.$key->CadenaDescripcion.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->Localizacion.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->vencimiento .'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->calibrado.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->tipo_cal.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->motivo.'</td>
                            <td style="border: 1px solid #000; text-align: center; tr:nth-child:background: #F8F8F8; width: 10%;">'.$key->estatus_item.'</td>
                        </tr>';
             }
             $tabla_items .= '</tbody>
                </table>
                <br> 
                <br> 
                <br> ';

$pdf->SetFont('helvetica', '', 9);

        $pdf->writeHTML($tabla_items, true, false, false, false, '');
        $pdf->Ln();
        $pdf->SetFont('helvetica', '', 10);
        $pdf->writeHTML("_____________________________________", true, false, false, false, '');
        $pdf->MultiCell(150, 8,"Nombre y firma de quien recibe el servicio.", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->Ln();
        $pdf->Ln();
             $lista = "";
             $lista .="<h2>Comentarios:</h2>";
             foreach ($cm as $ul) {
                 $lista.="
                 <li>
                    
                    <span>
                                ".$ul->nombre."<small> ".$ul->fecha."</span>
                    </span>

                    <span >".$ul->comentario."</span>
                    <br>
                    
                    </li>";
             }
             
    

$pdf->writeHTML($lista, true, false, false, false, '');

$pdf->Ln();
             $notas = "";
             $notas .="<h3>Notas:</h3>";
             $notas.="                    
                    <span>
                                ".$wo->Notas."</span>
                    </span>";
        $pdf->writeHTML($notas, true, false, false, false, '');
        $name = "WO".$id.'.pdf';

  
        $pdf->Ln();
        //Generación del PDF
        $pdf->Output(sys_get_temp_dir().'/'.$name, 'I');
    }

    function ajax_getClientesCotizaciones(){
    $texto = $this->input->post('texto');

    // Consulta clientes con nombre corto válido, y si se proporciona texto, se filtra por coincidencia parcial
    $query = "SELECT Cust_ID, `Nombre Corto` as nombre FROM catalogo_clientes where `Nombre Corto` is not null and `Nombre Corto` != '.'";

    if($texto)
    {
        $query .= " and `Nombre Corto` like '%$texto%'";
    }

    $res = $this->MLConexion->consultar($query);

    if($res)
    {
        echo json_encode($res);
    }
}

function ver_foto($id){
    // Recupera y muestra la foto asociada a la orden de trabajo como imagen PNG
    $photo = $this->MLConexion->consultar('select * from WO_Master where WorkOrder_ID = '.$id);
    if($photo)
    {
        header("Content-type: image/png");
        echo $photo->foto;
    }
    else {
        echo "ERROR";
    }
}
}
