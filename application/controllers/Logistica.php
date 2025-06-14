<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Logistica extends CI_Controller {

    function __construct() {
    parent::__construct();

    // Carga la librería personalizada para envío de correos de logística
    $this->load->library('correos_logistica');

    // Carga el modelo de conexión a base de datos para logística
    $this->load->model('MLConexion_model', 'MLConexion');
}

function index() {
    // Vista principal de opciones de logística
    $this->load->view('header');
    $this->load->view('logistica/logistica_opciones');
}

function documentacion() {
    // Vista para el módulo de documentación logística
    $this->load->view('header');
    $this->load->view('logistica/documentacion');
}

function equipos() {
    // Vista del módulo de equipos logísticos
    $this->load->view('header');
    $this->load->view('logistica/equipos');
}

function programacion_recorridos() {
    // Vista para programar recorridos logísticos
    $this->load->view('header');
    $this->load->view('logistica/programacion_recorridos');
}

function recorridos() {
    // Vista para ver y gestionar recorridos
    $this->load->view('header');
    $this->load->view('logistica/recorridos');
}

function dashboard() {
    // Vista del panel de control logístico
    $this->load->view('header');
    $this->load->view('logistica/dashboard');
}

function firmar() {
    // Muestra formulario o vista para firmar documentos/logística
    $data['data'] = $this->input->post();
    $this->load->view('logistica/firmar', $data);
}

function formato_requisitos() {
    // Decodifica lista de IDs de empresas enviada vía POST
    $empresas = json_decode($this->input->post('empresas'));
    $data = [];

    // Consulta datos de cada empresa y los agrega a un arreglo
    foreach ($empresas as $key => $value) {
        $res = $this->Conexion->consultar("SELECT * from empresas where id = $value", TRUE);

        $empresa = new stdClass;
        $empresa->nombre = $res->nombre;
        $empresa->requisitos_logisticos = $res->requisitos_logisticos;
        $empresa->requisitos_documento = $res->requisitos_documento;
        array_push($data, $empresa);
    }

    // Se ocultan errores PHP
    ini_set('display_errors', 0);

    // Carga librería para generar PDF
    $this->load->library('pdfview');
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Configuración del documento PDF
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AleksOrtiz');
    $pdf->SetTitle('Masmetrologia');
    $pdf->SetSubject('Formato Requisitos');
    $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', '                         Requisitos de Empresa');
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
    $pdf->SetFont('times', 'B', 15);
    $pdf->AddPage();
    $pdf->SetFillColor(255, 255, 255);

    // Escribe la información de cada empresa en el PDF
    foreach ($data as $key => $empresa) {
        $pdf->SetFont('times', 'B', 16);
        $pdf->Write(0, $empresa->nombre, '', 0, 'L', true, 0, false, false, 0);

        $pdf->SetFont('times', 'B', 10);
        $pdf->Write(0, 'Requisitos Logísticos:', '', 0, 'L', true, 0, false, false, 0);
        $pdf->SetFont('times', '', 10);
        $pdf->Write(0, $empresa->requisitos_logisticos, '', 0, 'L', true, 0, false, false, 0);
        $pdf->ln();

        $pdf->SetFont('times', 'B', 10);
        $pdf->Write(0, 'Requisitos de Documento:', '', 0, 'L', true, 0, false, false, 0);
        $pdf->SetFont('times', '', 10);
        $pdf->Write(0, $empresa->requisitos_documento, '', 0, 'L', true, 0, false, false, 0);
        $pdf->ln();
        $pdf->ln();
    }

    // Muestra el PDF en pantalla
    $pdf->Output('Requisitos.pdf', 'I');
}

    ////////////////////////////////// A J A X //////////////////////////////////
    function ajax_getSolicitudes(){

    // Consulta base: solicitudes de factura con empresa, usuario y conteo de recorridos
    $query = "SELECT F.id, F.fecha, F.fecha_retorno, F.usuario, F.cliente, F.contacto, F.reporte_servicio, F.orden_compra, F.forma_pago, F.pagada, F.conceptos, F.notas, F.estatus_factura, F.documentos_requeridos, F.serie, F.folio, F.codigo_impresion, E.nombre as Cliente, concat(U.nombre, ' ', U.paterno) as User, ";
    $query .= "(SELECT count(id) from recorrido_conceptos where id_concepto = F.id and tipo = 'FACTURA') as Recorridos, (SELECT count(id) from recorrido_conceptos where id_concepto = F.id and tipo = 'FACTURA' and cerrado = 1) as RecorridosCerrados from solicitudes_facturas F inner join empresas E on E.id = F.cliente inner join usuarios U on U.id = F.usuario where 1 = 1";

    if(isset($_POST['estatus_factura']))
    {
        $estatus = $this->input->post('estatus_factura');

        // Filtra solicitudes por estatus de entrega si corresponde
        if($estatus == "ENTREGA")
        {
            $query .= " and (F.estatus_factura = 'RECIBIDO EN LOGISTICA' or F.estatus_factura = 'NO ENTREGADA' or F.estatus_factura = 'ENTREGA RECHAZADA') having Recorridos = RecorridosCerrados";
        }
        // Filtra solicitudes por estatus de recolección si corresponde
        else if($estatus == "RECOLECTA")
        {
            $query .= " and (F.estatus_factura = 'DEJADA CON CLIENTE' or F.estatus_factura = 'NO RECOLECTADA' or F.estatus_factura = 'RECOLECTA RECHAZADA') having Recorridos = RecorridosCerrados";
            $query .= " order by F.fecha_retorno asc";
        }
        // Filtra por otro estatus específico
        else
        {
            $query .= " and F.estatus_factura = '$estatus'";
        }
    }

    // Ejecuta la consulta y devuelve resultados en JSON
    $res = $this->Conexion->consultar($query);
    if($res)
    {
        echo json_encode($res);
    }
}


    ////////////////////////////////// L O G I S T I C A //////////////////////////////////
   function ajax_getMLEquipos() {
    // Consulta equipos en proceso de entrega desde la vista v_items_entregando
    $res = $this->MLConexion->consultar("SELECT *, ifnull(Item,'') as Equipo_ID, ifnull(fabricante,'') as Fabricante, ifnull(modelo,'') as Modelo, ifnull(serie,'') as Serie from v_items_entregando");
    if($res) {
        echo json_encode($res);
    }
}

function ajax_getMensajeros() {
    // Consulta usuarios activos, potencialmente filtrando solo mensajeros
    $query = "SELECT U.id, U.nombre, U.paterno, U.materno, U.no_empleado, U.puesto, U.correo, U.ultima_sesion, U.departamento, U.activo, U.jefe_directo, U.autorizador_compras, U.autorizador_compras_venta, concat(U.nombre, ' ', U.paterno) as User, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as CompleteName from usuarios U inner join privilegios P on P.usuario = U.id where U.activo = '1'";

    if(isset($_POST['mensajeros']) && $_POST['mensajeros'] == "1") {
        $query .= " and P.mensajero = '1'";
    }

    $query .= " order by User";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function ajax_setRecorrido() {
    // Obtiene datos del formulario
    $mensajero = $this->input->post('mensajero');
    $fecha = $this->input->post('fecha');
    $recorrido = json_decode($this->input->post('recorrido'));

    // Crea nuevo recorrido principal
    $data['mensajero'] = $mensajero;
    $data['fecha_recorrido'] = $fecha;
    $data['estatus'] = 'ASIGNADO A MENSAJERO';
    $recorrido_id = $this->Conexion->insertar('recorridos', $data);

    // Inserta cada concepto dentro del recorrido
    foreach ($recorrido as $value) {
        $data2['recorrido'] = $recorrido_id;
        $data2['tipo'] = $value[0];
        $data2['id_concepto'] = $value[1];
        $data2['rs'] = $value[2];
        $data2['cliente'] = $value[3];
        $data2['descripcion'] = $value[4];
        $data2['accion'] = $value[5];
        $data2['estatus'] = "ASIGNADO A MENSAJERO";
        $data2['discrepancia'] = "0";
        $data2['cerrado'] = "0";
        $data2['reporte'] = "0";

        $this->Conexion->insertar('recorrido_conceptos', $data2);

        // Actualiza estatus en la tabla solicitudes_facturas (si corresponde)
        $this->Conexion->modificar('solicitudes_facturas', array('estatus_factura' => $data2['estatus']), null, array('id' => $data2['factura']));
    }
}

    function ajax_getRecorridos() {
    $pendientes = $this->input->post('pendientes');
    $factura = $this->input->post('factura');

    // Consulta de recorridos asociados a facturas con datos de usuario, cliente y estado
    $query = "SELECT RF.*, SF.folio, SF.cliente, R.mensajero, R.fecha_recorrido, R.estatus as Estatus_recorrido, ifnull(RR.id,'N/A') as Reporte, E.nombre as Cliente,";
    $query .= "(SELECT count(RF2.id) from recorrido_facturas RF2 where RF2.recorrido = RF.recorrido and (RF2.estatus = 'ASIGNADO A MENSAJERO' or RF2.estatus = 'EN RECORRIDO')) as Pendientes,";
    $query .= "ifnull(concat(M.nombre, ' ', M.paterno), 'N/A') as Mensajero";
    $query .= " from recorrido_facturas RF";
    $query .= " inner join recorridos R on R.id = RF.recorrido";
    $query .= " left join recorrido_reporte RR on RR.id = RF.reporte";
    $query .= " inner join solicitudes_facturas SF on SF.id = RF.factura";
    $query .= " inner join empresas E on E.id = SF.cliente";
    $query .= " left join usuarios M on M.id = R.mensajero where 1 = 1";

    // Filtro por factura específica
    if(isset($_POST['factura'])) {
        $query .= " and RF.factura = $factura";
    }

    // Filtro por recorridos pendientes
    if($pendientes == "1") {
        $query .= " and (R.estatus = 'ACEPTADO' or R.estatus = 'ASIGNADO A MENSAJERO')";
    }

    $query .= " order by R.fecha_recorrido, R.id, SF.cliente, RF.accion, SF.folio, RF.id asc";

    $res = $this->Conexion->consultar($query);

    // Devuelve resultados como JSON
    echo json_encode($res);
}

function ajax_getEnvios() {
    $factura = $this->input->post('factura');

    // Consulta de envíos asociados a una factura específica
    $query = "SELECT EF.*, concat(U.nombre, ' ', U.paterno) as User from envios_factura EF inner join usuarios U on U.id = EF.usuario where EF.factura = $factura";

    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res);
    }
}

    function ajax_setEstatusEnvioCorreo() {
    // Actualiza el estatus de un envío en la tabla envios_factura
    $where['id'] = $this->input->post('id_envio');
    $data['estatus'] = $this->input->post('estatus');

    $this->Conexion->modificar('envios_factura', $data, null, $where);
}

function ajax_setEnviosComentarios() {
    // Inserta un comentario asociado a un envío
    $data['envio'] = $this->input->post('id_envio');
    $data['comentario'] = $this->input->post('comentario');
    $data['usuario'] = $this->session->id;
    $func['fecha'] = 'CURRENT_TIMESTAMP()';

    $this->Conexion->insertar('envios_factura_comentarios', $data, $func);
}

function ajax_getEnviosComentarios() {
    // Consulta comentarios relacionados a un envío
    $id_envio = $this->input->post('id_envio');

    $query = "SELECT EFC.*, concat(U.nombre, ' ', U.paterno) as User from envios_factura_comentarios EFC inner join usuarios U on U.id = EFC.usuario where EFC.envio = $id_envio";

    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res);
    }
}

function ajax_getFacturasRecorrido() {
    // Consulta facturas que forman parte de un recorrido específico
    $id = $this->input->post('id');

    $query = "SELECT RF.*, SF.folio from recorrido_facturas RF inner join solicitudes_facturas SF on SF.id = RF.factura where RF.recorrido = $id";

    $res = $this->Conexion->consultar($query);

    echo json_encode($res);
}

    function ajax_aceptarRecorrido() {
    // Cambia estatus del recorrido a "ACEPTADO"
    $id = $this->input->post('recorrido');
    $data["estatus"] = "ACEPTADO";

    $res = $this->Conexion->modificar('recorridos', $data, null, array('id' => $id));

    // Actualiza estatus de recorrido_facturas y solicitudes_facturas a "EN RECORRIDO"
    $this->Conexion->comando("UPDATE solicitudes_facturas SF, recorrido_facturas RF set SF.estatus_factura = 'EN RECORRIDO', RF.estatus = 'EN RECORRIDO' where SF.id = RF.factura and RF.recorrido = $id");

    if($res > 0) {
        echo "1";
    }
}

function ajax_rechazarRecorrido() {
    // Cambia estatus del recorrido a "RECHAZADO POR MENSAJERO"
    $id = $this->input->post('recorrido');
    $acc = $this->input->post('accion');
    $data["estatus"] = "RECHAZADO POR MENSAJERO";

    $res = $this->Conexion->modificar('recorridos', $data, null, array('id' => $id));

    // Actualiza estatus de recorrido_facturas y solicitudes_facturas a "{ACCION} RECHAZADA"
    $this->Conexion->comando("UPDATE solicitudes_facturas SF, recorrido_facturas RF set SF.estatus_factura = '$acc RECHAZADA', RF.estatus = 'RECHAZADO POR MENSAJERO' where SF.id = RF.factura and RF.recorrido = $id");

    if($res > 0) {
        echo "1";
    }
}

function ajax_pendienteCierreRecorrido() {
    // Marca recorrido como "PENDIENTE CIERRE"
    $id = $this->input->post('recorrido');
    $data["estatus"] = "PENDIENTE CIERRE";

    $res = $this->Conexion->modificar('recorridos', $data, null, array('id' => $id));

    if($res > 0) {
        echo "1";
    }
}

function ajax_getRecorridosPendienteCierre() {
    // Obtiene recorridos que están pendientes por cerrar
    $res = $this->Conexion->consultar('SELECT R.*, CONCAT(M.nombre, " ", M.paterno) as Mensajero from recorridos R inner join usuarios M on M.id = R.mensajero where R.estatus = "PENDIENTE CIERRE"');
    if($res) {
        echo json_encode($res);
    }
}


    function guardarFirma() {
    ///////////////// F I R M A  J P G /////////////////
    
    // Se obtiene la firma en base64 desde POST
    $file = $this->input->post('firma');

    // Decodifica la imagen base64 a binario
    $image = base64_decode(str_replace('data:image/png;base64,', '', $file));

    // Define el nombre del archivo y la ruta de guardado
    $image_name = 'HOLA';
    $filename = $image_name . '.' . 'jpg';
    $path = "data/logistica/firmas/" . $filename;

    // Guarda la imagen en disco
    file_put_contents($path, $image);

    // Retorna el contenido recibido
    echo $file;
    return;

        if($file != "undefined")
        {
            $config['upload_path'] = 'data/logistica/firmas/';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['encrypt_name'] = TRUE;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('iptFoto'))
            {
                $data['id'] = $id;
                $data['foto'] = $this->upload->data('file_name');
                if($this->Modelo->update($data)){
                    if($foto != "default.png"){
                        unlink('data/empresas/fotos/' . $foto );
                    }
                    echo $data['foto'];
                }
            } else {
                echo "";
            }
        } else {
            $data['id'] = $id;
            $data['foto'] = 'default.png';
            if($this->Modelo->update($data)){
                if($foto != "default.png"){
                    unlink('data/empresas/fotos/' . $foto );
                }
                echo $data['foto'];
            }
        }
        ///////////////////////////////////////////////////////////////
    }

    function ajax_updateRecorrido() {
    $recorrido = json_decode($this->input->post('recorrido'));
    $facturas = json_decode($this->input->post('facturas'));
    
    $recolecta = $this->input->post('recolecta');
    $comentario = $this->input->post('comentario');
    $fecha = $this->input->post('fecha');

    // Si la acción fue completada y se requiere recolección, cambia estatus y fecha
    if($recorrido->estatus == "ENTREGADA") {
        if($recolecta == "1" && $recorrido->accion == "ENTREGA") {
            $recorrido->estatus = "DEJADA CON CLIENTE";
            $data["fecha_retorno"] = $fecha;
        } else {
            $recorrido->estatus = "RETORNADA AUTORIZADA";
        }
    }

    // Se genera reporte del recorrido
    $recorrido_reporte['recorrido'] = $recorrido->id;
    $recorrido_reporte['cliente'] = $recorrido->cliente;
    $recorrido_reporte['contacto'] = $recorrido->contacto;
    $recorrido_reporte['accion'] = $recorrido->accion;
    $recorrido_reporte['resultado'] = $recorrido->estatus;
    $recorrido_reporte['firma'] = isset($_POST['firma']) ? 1 : 0;

    $RR = $this->Conexion->insertar('recorrido_reporte', $recorrido_reporte, array('fecha' => 'CURRENT_TIMESTAMP()'));

    // Se registran facturas del recorrido y se actualiza su estatus
    $folios = "";
    foreach ($facturas as $value) {
        $folios .= $value->folio . ", ";
        $recorrido_facturas['recorrido'] = $recorrido->id;
        $recorrido_facturas['factura'] = $value->factura;
        $recorrido_facturas['accion'] = $recorrido->accion;
        $recorrido_facturas['estatus'] = $recorrido->estatus;
        $recorrido_facturas['reporte'] = $RR;
        
        if($value->nueva == "1") {
            $this->Conexion->insertar('recorrido_facturas', $recorrido_facturas);
        } else {
            $this->Conexion->modificar('recorrido_facturas', $recorrido_facturas, null, array('id' => $value->id));
        }

        $data['estatus_factura'] = $recorrido->estatus;
        $this->Conexion->modificar('solicitudes_facturas', $data, null, array('id' => $value->factura));
    }

    // Si hay comentario, se registra con color según el estatus
    if($comentario) {
        $color = substr($recorrido->estatus, 0, 2) == "NO" ? "red" : "green";
        $comentario = '<font color=' . $color . '><b>' . $recorrido->estatus . ':</b></font> ' . $comentario;

        $data_com['reporte'] = $RR;
        $data_com['usuario'] = $this->session->id;
        $data_com['comentario'] = $comentario;
        $func_com['fecha'] = "CURRENT_TIMESTAMP()";
        $this->Conexion->insertar('recorrido_comentarios', $data_com, $func_com);
    }

    // Si se capturó firma, se guarda como imagen y se envía correo si aplica
    if(isset($_POST['firma'])) {
        $file = $this->input->post('firma');
        $image = base64_decode(str_replace('data:image/png;base64,', '', $file));
        $image_name = $RR . '.jpg';
        $path = "data/logistica/firmas/" . $image_name;
        file_put_contents($path, $image);

        // Enviar correo de acuse si se proporcionó correo de contacto
        if($recorrido->CorreoContacto) {
            $data = new stdClass;
            $data->facturas = rtrim($folios, ', ');
            $data->contacto = $recorrido->NombreContacto;
            $data->correo = $recorrido->CorreoContacto;
            $data->comentarios = $comentario;
            $data->firma = $image_name;
            $data->fecha_retorno = $fecha;
            $this->correos_logistica->acuse($data);
        }
    }
}
    function ajax_getComentariosRecorrido() {
    $id = $this->input->post('id');

    // Consulta comentarios del recorrido con nombre del usuario
    $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from recorrido_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

    if($id) {
        $query .= " and C.reporte = '$id'";
    }

    $query .= " order by C.fecha";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function ajax_getReporte() {
    $idReporte = $this->input->post('id');

    // Consulta detalles del reporte de recorrido con información de factura, cliente, contacto y requisitor
    $query = "SELECT RF.id, RR.fecha, RF.discrepancia, RR.accion, RR.resultado, SF.folio, RF.estatus, E.nombre as Cliente, ifnull(EC.nombre, 'N/A') as Contacto, RR.firma, concat(U.nombre, ' ', U.paterno) as Requisitor FROM recorrido_facturas RF";
    $query .= " inner join solicitudes_facturas SF on SF.id = RF.factura";
    $query .= " inner join usuarios U on U.id = SF.usuario";
    $query .= " inner join recorrido_reporte RR on RR.id = RF.reporte";
    $query .= " inner join empresas E on E.id = RR.cliente";
    $query .= " left join empresas_contactos EC on EC.id = RR.contacto";
    $query .= " where RF.reporte = $idReporte";

    $res = $this->Conexion->Consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function ajax_aceptarCierreRecorrido() {
    // Marca recorrido como cerrado y actualiza sus conceptos
    $recorrido = json_decode($this->input->post('recorrido'));

    $this->Conexion->modificar('recorridos', $recorrido, null, array('id' => $recorrido->id));
    $this->Conexion->modificar('recorrido_facturas', array('cerrado' => 1), null, array('recorrido' => $recorrido->id));
}

function ajax_rechazarCierreRecorrido() {
    // Marca recorrido como cerrado pero identifica discrepancias en facturas específicas
    $recorrido = json_decode($this->input->post('recorrido'));
    $facturas = json_decode($this->input->post('facturas'));

    $this->Conexion->modificar('recorridos', $recorrido, null, array('id' => $recorrido->id));
    $this->Conexion->modificar('recorrido_facturas', array('cerrado' => 1), null, array('recorrido' => $recorrido->id));

    foreach ($facturas as $id_factura) {
        $this->Conexion->comando("UPDATE recorrido_facturas RF, solicitudes_facturas SF set RF.discrepancia = 1, RF.estatus = concat('NO ', RF.accion, 'DA'), SF.estatus_factura = concat('NO ', RF.accion, 'DA') where SF.id = RF.factura and RF.id = $id_factura");
    }
}

function ajax_getEmpresasRecorrido() {
    // Consulta empresas involucradas en un recorrido
    $recorrido = $this->input->post('recorrido');
    $query = "SELECT E.id, E.nombre from empresas E inner join solicitudes_facturas SF on SF.cliente = E.id inner join recorrido_facturas RF on RF.factura = SF.id where RF.recorrido = $recorrido order by E.nombre";

    $res = $this->Conexion->consultar($query);
    echo json_encode($res);
}

    function ajax_getDashboard() {
    // Consulta resumen de estatus y fechas mínimas por categoría para el dashboard logístico
    $query = 'SELECT count(*) as Total, (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "ENVIADA LOGISTICA") as D1, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "ENVIADA LOGISTICA") as ult1,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "RECIBIDO EN LOGISTICA") as D2, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "RECIBIDO EN LOGISTICA") as ult2,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "RECHAZADO EN LOGISTICA") as D3, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "RECHAZADO EN LOGISTICA") as ult3,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "ASIGNADO A MENSAJERO") as D4, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "ASIGNADO A MENSAJERO") as ult4,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "ENTREGA RECHAZADA") as D5, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "ENTREGA RECHAZADA") as ult5,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "RECOLECTA RECHAZADA") as D6, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "RECOLECTA RECHAZADA") as ult6,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "EN RECORRIDO") as D7, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "EN RECORRIDO") as ult7,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "DEJADA CON CLIENTE") as D8, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "DEJADA CON CLIENTE") as ult8,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "NO ENTREGADA") as D9, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "NO ENTREGADA") as ult9,';
    $query .= ' (SELECT count(*) FROM solicitudes_facturas where estatus_factura = "NO RECOLECTADA") as D10, (SELECT min(fecha) FROM solicitudes_facturas where estatus_factura = "NO RECOLECTADA") as ult10';
    $query .= ' FROM solicitudes_facturas;';

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

function ajax_setSolicitud() {
    $solicitud = json_decode($this->input->post('solicitud'));
    $other = json_decode($this->input->post('other'));

    // Adjunta archivos binarios si existen
    if(isset($_FILES['f_O'])) {
        $solicitud->f_orden_compra = file_get_contents($_FILES['f_O']['tmp_name']);
    }
    if(isset($_FILES['f_R'])) {
        $solicitud->f_remision = file_get_contents($_FILES['f_R']['tmp_name']);
    }

    $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');
    $res = false;

    // Inserta nueva solicitud o actualiza una existente
    if($solicitud->id == 0) {
        $res = $this->Conexion->insertar('solicitudes_facturas', $solicitud, $funciones);
        $solicitud->id = $res;
    } else {
        $res = $this->Conexion->modificar('solicitudes_facturas', $solicitud, null, array('id' => $solicitud->id)) >= 0;
    }

    // Si se cargó archivo "other", se notifica vía correo
    if(isset($_FILES['other'])) {
        $solicitud->User = $other->User;
        $solicitud->Client = $other->Client;
        $solicitud->Contact = $other->Contact;

        $correos = [];
        $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.responder_facturas = 1");

        foreach ($correos_a as $key => $value) {
            array_push($correos, $value->correo);
        }

        $solicitud->correos = array_merge(array($this->session->correo), $correos);

        // Envía correo de notificación
        $this->correos_facturacion->solicitud($solicitud);
    }

    if($res) {
        echo "1";
    }
}
    
    function ajax_editSolicitud() {
    $solicitud = json_decode($this->input->post('solicitud'));
    $other = json_decode($this->input->post('other'));

    // Se cargan archivos si se enviaron en el formulario
    if(isset($_FILES['f_A'])) {
        $solicitud->f_acuse = file_get_contents($_FILES['f_A']['tmp_name']);
    }
    if(isset($_FILES['f_F'])) {
        $solicitud->f_factura = file_get_contents($_FILES['f_F']['tmp_name']);
    }
    if(isset($_FILES['f_X'])) {
        $solicitud->f_xml = file_get_contents($_FILES['f_X']['tmp_name']);
    }

    $comentario = $this->input->post('comentario');

    // Actualiza la solicitud
    $res = $this->Conexion->modificar('solicitudes_facturas', $solicitud, null, array('id' => $solicitud->id));
    if($res > 0) {
        // Si se envió comentario, lo guarda
        if(isset($_POST['comentario']) && !empty($comentario)) {
            $this->Conexion->insertar('solicitudes_facturas_comentarios', array('solicitud' => $solicitud->id, 'usuario' => $this->session->id, 'comentario' => $comentario), array('fecha' => 'CURRENT_TIMESTAMP()'));
            $solicitud->comentario = $comentario;
        }

        // Prepara correos de notificación
        $correos = [];
        $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.responder_facturas = 1");
        foreach ($correos_a as $key => $value) {
            array_push($correos, $value->correo);
        }

        $solicitud->correos = array_merge(array($this->session->correo), $correos);
        $solicitud->User = $other->User;
        $solicitud->Client = $other->Client;
        $solicitud->Contact = $other->Contact;

        // Envía correo de modificación de solicitud
        $this->correos_facturacion->editar_solicitud($solicitud);
        echo "1";
    } else {
        echo "";
    }
}

function ajax_setComentarios() {
    $comentario = json_decode($this->input->post('comentario'));

    // Asigna el usuario de sesión al comentario y registra la fecha actual
    $comentario->usuario = $this->session->id;
    $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');

    // Inserta el comentario en la base de datos
    $res = $this->Conexion->insertar('solicitudes_facturas_comentarios', $comentario, $funciones);
    if($res > 0) {
        echo "1";
    }
}

    function ajax_getComentarios() {
    $id = $this->input->post('id');

    // Consulta comentarios asociados a una solicitud específica
    $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from solicitudes_facturas_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

    if($id) {
        $query .= " and C.solicitud = '$id'";
    }

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function ajax_getRequisitores() {
    $id = $this->input->post('id');

    // Consulta de usuarios con privilegio de solicitar facturas
    $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Nombre, P.puesto as Puesto from usuarios U inner join puestos P on U.puesto = P.id inner join privilegios PR on PR.usuario = U.id where U.activo = 1 and PR.solicitar_facturas = 1";

    if($id) {
        $query .= " and U.id = '$id'";
    }

    $res = $this->Conexion->consultar($query, $id);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function ajax_getVFPData() {
    // Ejecuta lector de datos VFP externo pasando el modelo como argumento
    $modelo = $this->input->post('modelo');
    $res = shell_exec("C:/xampp/htdocs/MASMetrologia/vfp_reader/vfp_reader.exe \"$modelo\"");
    echo $res;
}

    function archivo_impresion() {
    // Desactiva errores en pantalla y carga librería de combinación de PDFs
    ini_set('display_errors', 0);
    $this->load->library('pdfmerge');

    $id = $this->input->post('id');
    $codigo = $this->input->post('codigo');

    $pdf = new PDFMerger();

    // Consulta solicitud de factura por ID
    $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);

    // Itera cada letra del código recibido para determinar qué documentos incluir
    for ($i = 0; $i < strlen($codigo); $i++) {
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
                $campo = 'EMISION';
                break;
            default:
                $campo = null;
                break;
        }

        if($campo != null) {
            if(substr($campo, 0, 2 ) == "f_") {
                // Documentos almacenados como BLOB en BD (factura, remisión, etc.)
                $file = $res->$campo;
                $fichero = sys_get_temp_dir() . '/' . $campo . '.pdf';
                file_put_contents($fichero, $file);
                $pdf->addPDF($fichero, 'all');
            } else {
                // Documentos externos por código fijo (OPINION, EMISION)
                $fichero = "data/empresas/documentos_globales/" . $campo . "_000001.pdf";
                $pdf->addPDF($fichero, 'all');
            }
        }
    }

    // Combina y muestra los PDF en el navegador
    $pdf->merge('browser');
}

    function ajax_getClientes() {
    // Consulta clientes registrados como empresas con rol de cliente
    $query = "SELECT C.id, C.nombre, C.razon_social, C.foto, C.opinion_positiva, C.emision_sua from empresas C where C.cliente = 1";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function ajax_getDocumentosGlobales() {
    // Consulta los documentos globales configurados para la empresa
    $query = "SELECT id, opinion_positiva, emision_sua from documentos_globales where id = 1";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}
    
    function ajax_filesExists($id) {
    $this->load->helper('file');

    // Rellenar el ID con ceros a la izquierda (ej. 1 => 000001)
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Verifica si existen los archivos PDF de ACUSE y EMISIÓN para la empresa
    $acuse = read_file(base_url("data/empresas/documentos_facturacion/ACUSE_" . $id . ".pdf")) ? "1" : "0";
    $emision = read_file(base_url("data/empresas/documentos_facturacion/EMISION_" . $id . ".pdf")) ? "1" : "0";

    echo json_encode(array($acuse, $emision));
}

function ajax_readXML() {
    $dom = new DomDocument;
    $dom->preserveWhiteSpace = FALSE;

    // Cargar el XML desde archivo temporal subido
    $dom->loadXML(file_get_contents($_FILES['f_X']['tmp_name']));

    $comp = $dom->getElementsByTagName('Comprobante');
    $data = array();

    // Obtener atributos Serie y Folio del nodo <Comprobante>
    foreach ($comp[0]->attributes as $elem) {
        if($elem->name == "Serie" | $elem->name == "Folio") {
            $e = array($elem->name => $elem->value);
            array_push($data, $e);
        }
    }

    echo json_encode($data);
}

function ajax_setDocumentoFacturacion() {
    $file = $this->input->post('file');
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');

    // Formatear ID a 6 dígitos
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Verificar que se haya subido un archivo
    if($file != "undefined") {
        $config['upload_path'] = 'data/empresas/documentos_facturacion/';
        $config['allowed_types'] = 'pdf';
        $config['overwrite'] = TRUE;
        $config['file_name'] = $documento . '_' . $id;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('file')) {
            $where['id'] = $id;

            // Determinar campo a actualizar según tipo de documento
            switch($documento) {
                case "EMISION":
                    $campo = "emision_sua";
                    break;
                case "OPINION":
                    $campo = "opinion_positiva";
                    break;
            }

            $data[$campo] = $this->upload->data('file_name');
            $this->Conexion->modificar('empresas', $data, null, $where);
            echo "1";
        }
    }
}

    function ajax_deleteDocumentoFacturacion() {
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');

    // Formatear ID a 6 dígitos (rellenando con ceros a la izquierda)
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Eliminar archivo físico del documento
    unlink('data/empresas/documentos_facturacion/' . $documento . '_' . $id . '.pdf');

    // Actualizar campo correspondiente en base de datos
    $where['id'] = $id;
    switch($documento) {
        case "EMISION":
            $campo = "emision_sua";
            break;
        case "OPINION":
            $campo = "opinion_positiva";
            break;
    }

    $data[$campo] = "";
    $this->Conexion->modificar('empresas', $data, null, $where);
}

function ajax_setDocumentoGlobal() {
    $file = $this->input->post('file');
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');

    // Formatear ID a 6 dígitos (rellenando con ceros a la izquierda)
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Validar que se haya recibido un archivo
    if($file != "undefined") {
        $config['upload_path'] = 'data/empresas/documentos_globales/';
        $config['allowed_types'] = 'pdf';
        $config['overwrite'] = TRUE;
        $config['file_name'] = $documento . '_' . $id;

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('file')) {
            $where['id'] = $id;

            // Determinar campo correspondiente a actualizar
            switch($documento) {
                case "EMISION":
                    $campo = "emision_sua";
                    break;
                case "OPINION":
                    $campo = "opinion_positiva";
                    break;
            }

            $data[$campo] = $this->upload->data('file_name');
            $this->Conexion->modificar('documentos_globales', $data, null, $where);
            echo "1";
        }
    }
}

    function ajax_deleteDocumentoGlobal() {
    $documento = $this->input->post('documento');
    $id = $this->input->post('empresa');
    
    // Formatear ID a 6 dígitos con ceros a la izquierda
    $id = str_pad($id, 6, "0", STR_PAD_LEFT);

    // Eliminar archivo físico
    unlink('data/empresas/documentos_globales/' . $documento . '_' . $id . '.pdf');

    // Determinar campo a limpiar
    $where['id'] = $id;
    switch($documento) {
        case "EMISION":
            $campo = "emision_sua";
            break;
        case "OPINION":
            $campo = "opinion_positiva";
            break;
    }

    $data[$campo] = "";
    $this->Conexion->modificar('documentos_globales', $data, null, $where);
}

function ajax_enviarCorreo() {
    $id = $this->input->post('id');
    $body = $this->input->post('body');
    $para = $this->input->post('para');
    $cc = $this->input->post('cc');

    // Archivos a enviar
    $campos = ['f_xml', 'f_factura'];
    $archivos = [];

    $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);

    foreach ($campos as $value) {
        if(substr($value, 0, 2 ) == "f_") {
            $file = $res->$value;
            $fichero = sys_get_temp_dir(). '/' . $value . ($value == "f_xml" ? '.xml' : '.pdf');
            file_put_contents($fichero, $file);
        } else {
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

    // Armar datos y enviar
    $datos['id'] = $id;
    $datos['para'] = $para;
    $datos['cc'] = $cc;
    $datos['body'] = $body;
    $datos['campos'] = $campos;
    $datos['archivos'] = $archivos;

    $this->correos_logistica->enviarCorreoFactura($datos);
}

    function ajax_enviarCorreoLogistica() {
    $id = $this->input->post('id');
    $destinatario = $this->input->post('destinatario');
    $mensaje = $this->input->post('mensaje');

    $data['factura'] = $id;
    $data['destino'] = $destinatario;
    $data['usuario'] = $this->session->id;
    $data['estatus'] = "ENVIADA";
    $data['mensaje'] = $mensaje;

    $func['fecha'] = 'CURRENT_TIMESTAMP()';
    $recorrido_id = $this->Conexion->insertar('envios_factura', $data, $func);
}

////////////////////////////////// F A C T U R A S //////////////////////////////////

function ajax_getFacturas() {
    $id = $this->input->post('id');

    $query = "SELECT F.id, F.fecha, F.usuario, F.cliente, F.contacto, F.reporte_servicio, F.orden_compra, F.forma_pago, F.pagada, F.conceptos, F.notas, F.estatus_factura, F.documentos_requeridos, F.serie, F.folio, F.codigo_impresion,";
    $query .= " (SELECT count(id) from recorrido_conceptos where factura = F.id) as Recorridos,";
    $query .= " E.nombre as Cliente, concat(U.nombre, ' ', U.paterno) as User";
    $query .= " from solicitudes_facturas F";
    $query .= " inner join empresas E on E.id = F.cliente";
    $query .= " inner join usuarios U on U.id = F.usuario";
    $query .= " where F.folio > 0 and F.estatus = 'ACEPTADO'";

    if ($id) {
        $query .= " and F.id = '$id'";
    }

    if (isset($_POST['estatus'])) {
        $estatus = $this->input->post('estatus');
        $query .= " and F.estatus = '$estatus'";
    }

    if (isset($_POST['estatus_factura'])) {
        $estatus_factura = $this->input->post('estatus_factura');
        $query .= " and F.estatus_factura = '$estatus_factura'";
    }

    if (isset($_POST['cliente'])) {
        $cliente = $this->input->post('cliente');
        $query .= " and F.cliente = '$cliente'";
    }

    $res = $this->Conexion->consultar($query, $id);

    if ($res) {
        echo json_encode($res);
    }
}


    

}
