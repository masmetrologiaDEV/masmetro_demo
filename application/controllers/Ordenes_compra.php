<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Ordenes_compra extends CI_Controller {
    function __construct() {
    parent::__construct();
    $this->load->library('correos_po');
    $this->load->model('compras_model');
}

function catalogo_po($estatus = 'TODO') {
    $estatus = strtoupper($estatus);
    $data['estatus'] = str_replace('_', ' ', $estatus);

    $this->load->view('header');
    $this->load->view('ordenes_compra/catalogo_po', $data);
}

function generar_po() {
    $this->load->view('header');
    $this->load->view('ordenes_compra/generar_po');
}

function construccion_po($idtemp) {
    $query = "SELECT OCT.*, JSON_UNQUOTE(OCT.prs) as PRS, JSON_UNQUOTE(OCT.prs_rechazados) as PRS_R, E.nombre FROM ordenes_compra_temp OCT INNER JOIN empresas E ON OCT.proveedor = E.id WHERE OCT.idtemp = '$idtemp'";

    $res = $this->Conexion->consultar($query, TRUE);
    if ($res) {
        $data['id_prov'] = $res->proveedor;
        $data['moneda'] = $res->moneda;
        $data['tipo'] = $res->tipo;
        $data['prs'] = $res->PRS;
        $data['prs_rechazadas'] = $res->PRS_R;
        $data['idtemp'] = $idtemp;

        $this->load->view('header');
        $this->load->view('ordenes_compra/construccion_po', $data);    
    }
}

    function editar_po($id){
    $data['id'] = $id;

    // Carga la vista de edición para la orden de compra con el ID proporcionado
    $this->load->view('header');
    $this->load->view('ordenes_compra/editar_po', $data);
}

function modificar_po($id){
    $data['id'] = $id;

    // Obtiene el último estatus registrado de la orden de compra en la bitácora
    $query = "SELECT estatus from bitacora_po where po='$id' ORDER BY idBitPO DESC LIMIT 1";
    $res = $this->Conexion->consultar($query, TRUE);

    // Si no está en edición, lo cambia a 'EDICION' y registra el cambio en la bitácora
    if($res->estatus != 'EDICION'){
        $estatus = 'EDICION';
        $datos['id'] = $id;
        $datos['estatus'] = $estatus;
        $this->compras_model->updateEstatusEditar($datos); // Actualiza el estatus en la orden
        $dato['po'] = $id;
        $dato['user'] = $this->session->id;
        $dato['estatus'] = $estatus;
        $this->compras_model->estatusPO($dato); // Registra el cambio en la bitácora
    }

    // Carga la vista de edición específica para modificar una orden existente
    $this->load->view('header');
    $this->load->view('ordenes_compra/editarPO', $data);
}

function ver_po($id){
    $data['id'] = $id;

    // Obtiene los comentarios generales relacionados con la PO
    $data['comentarios'] = $this->compras_model->verPo_comentarios($id);

    // Obtiene el historial de rastreo o seguimiento de la PO
    $data['rastreo'] = $this->compras_model->verPo_rastreo($id);

    // Obtiene comentarios que contienen imágenes adjuntas
    $data['comentarios_fotos'] = $this->compras_model->verPo_comentarios_fotos($id);

    // Obtiene imágenes relacionadas con el rastreo de la PO
    $data['rastreo_fotos'] = $this->compras_model->verPo_rastreo_fotos($id);

    // Carga las vistas necesarias para visualizar la orden de compra
    $this->load->view('header');
    $this->load->view('ordenes_compra/ver_po', $data); 
}


    function po_pdf($id){
        $query="SELECT PO.id, PO.fecha, PO.shipping_address, PO.billing_address, PO.conceptos, PO.subtotal, PO.descuento, PO.impuesto, PO.impuesto_nombre, PO.total, PO.retencion, PO.moneda, PO.rma, PO.fecha_aprobacion, concat(U.nombre, ' ', U.paterno) as User, U.correo as UserMail, concat(UA.nombre, ' ', UA.paterno) as UserA, E.razon_social, E.calle, E.numero, E.numero_interior, E.colonia, E.ciudad, E.estado, E.pais, E.rfc, EC.nombre, EC.telefono, EC.correo FROM ordenes_compra PO inner join empresas E on PO.proveedor = E.id inner join usuarios U on U.id = PO.usuario inner join usuarios UA on UA.id = PO.aprobador inner join empresas_contactos EC on PO.contacto = EC.id where PO.id='$id'";
        // Consulta toda la información de la orden de compra, proveedor, aprobador y contacto

        $res = $this->Conexion->consultar($query, TRUE);

        $conceptos = json_decode($res->conceptos);
        // Decodifica los conceptos incluidos en la orden (formato JSON)

        $PO = 'PO-' . str_pad($res->id, 6, "0", STR_PAD_LEFT);
        $FECHA = date_format(date_create($res->fecha), 'd/m/Y h:i A');
        // Formatea la fecha de creación de la orden

        $PROVEEDOR = $res->razon_social;
        $RMA = $res->rma;
        $DOMICILIO = $res->calle . ' ' . $res->numero . ' ' . $res->numero_interior;
        $COLONIA = $res->colonia;
        $RFC = $res->rfc;

        $REQUISITOR = $res->User;
        $REQUISITOR_CORREO = $res->UserMail;

        $APROBADOR = $res->UserA;
        $FECHA_APROBACION = date_format(date_create($res->fecha_aprobacion), 'd/m/Y h:i A'); // d/m/Y h:i A

        $CONTACTO = $res->nombre;
        $TELEFONO = $res->telefono;
        $CORREO = $res->correo;
        $UBICACION = $res->ciudad . ', ' . $res->estado . ', ' . $res->pais;

        $BILLING_A = $res->billing_address;
        $SHIPPING_A = $res->shipping_address;

        $SUBTOTAL = $res->subtotal;
        $DESCUENTO = $res->descuento;
        $IMPUESTO = $res->impuesto;
        $IMPUESTO_NOMBRE = $res->impuesto_nombre;
        $TOTAL = $res->total;
        $MONEDA = $res->moneda;
        $RETENCION = $res->retencion;
        
        
        ini_set('display_errors', 0);
        $this->load->library('pdfview');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AleksOrtiz');
        $pdf->SetTitle('Masmetrologia');
        $pdf->SetSubject('Formato PO');
        
        $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', '                                                       Orden de Compra / ' . $PO, "                                                             Fecha: " . $FECHA . " \n                                                             Comprador: ". $REQUISITOR ." \n                                                             Correo: " . $REQUISITOR_CORREO);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 10));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
        $pdf->SetFont('times', 'B', 15);
        $pdf->AddPage();        

        $pdf->SetFillColor(255, 255, 255);

        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 0, "Proveedor/Supplier:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $RMA ? "RMA:" : "", 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->SetFont('times', '', 10);
        $pdf->MultiCell(90, 6, $PROVEEDOR, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $RMA ? $RMA : "", 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        

        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 0, "Dirección / Address:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, "Contacto / Contact:", 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->SetFont('times', '', 10);

        $pdf->MultiCell(90, 0, $DOMICILIO, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $CONTACTO, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $COLONIA, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $TELEFONO, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $UBICACION, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 0, $CORREO, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        
        $pdf->MultiCell(90, 6, $RFC, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        
        $pdf->SetFont('times', 'B', 12);
        $pdf->MultiCell(90, 0, "Facturar a / Billing Address:", 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 6, "Enviar a / Shipping Address:", 0, 'L', 1, 1, '', '', true, 0, false, true, 0);
        $pdf->SetFont('times', '', 10);

        $pdf->MultiCell(90, 20, $BILLING_A, 0, 'L', 1, 0, '', '', true, 0, false, true, 0);
        $pdf->MultiCell(90, 20, $SHIPPING_A, 0, 'L', 1, 1, '', '', true, 0, false, true, 0);

        $pdf->MultiCell(180, 0, "", 0, 'L', 1, 1, '', '', true, 0, false, true, 0);

        $pdf->SetFont('times', 'B', 12);
        $pdf->SetFillColor(1, 59, 117);
        $pdf->SetTextColor(255);

        $pdf->SetTextColor(0);
        $pdf->Cell(15, 6, "Cant.", 1, 0, 'C', 0);
        $pdf->Cell(115, 6, "Descripción", 1, 0, 'C', 0);
        $pdf->Cell(25, 6, "Precio Unit.", 1, 0, 'C', 0);
        $pdf->Cell(25, 6, "Importe", 1, 0, 'C', 0);
        $pdf->Ln();

        $w = array(15, 115 , 25, 25);
        $pdf->SetFont('', '', 10);
        $pdf->MultiCell($w[0], 3,'','LR','C',0,0);
        $pdf->MultiCell($w[1], 3,'','LR','L',0,0);
        $pdf->MultiCell($w[2], 3,'','LR','R',0,0);
        $pdf->MultiCell($w[3], 3,'','LR','R',0,0);
        $pdf->Ln();
        $i = 0;

        // Itera por cada concepto para mostrar cantidad, descripción, precio unitario e importe
        foreach($conceptos as $indice => $concepto) {
            $cellcount = array();
            $startX = $pdf->GetX();
            $startY = $pdf->GetY();


            $pu = $concepto[2] / $concepto[0];
            $cellcount[] = $pdf->MultiCell($w[0], 6, $concepto[0], 0, 'C', 0, 0);
            if (empty($concepto[3])) {
            $cellcount[] = $pdf->MultiCell($w[1], 6, $concepto[1], 0, 'L', 0, 0);
            }else{
            $cellcount[] = $pdf->MultiCell($w[1], 6, $concepto[1]."\n Iva Retenido: ".$concepto[3]." - Retencion: "."$".number_format(floatval($concepto[4]),2)  , 0, 'L', 0, 0);    
            }
            
            
            $cellcount[] = $pdf->MultiCell($w[2], 6, "$" . number_format($pu, 2), 0, 'R', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[3], 6, "$" . number_format($concepto[2], 2), 0, 'R', 0, 0); // VER AQUI

            $pdf->SetXY($startX, $startY);

            $maxnocells = max($cellcount);

            $pdf->MultiCell($w[0], $maxnocells * 6, '', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], $maxnocells * 6, '', 'LR', 'L', 0, 0);
            $pdf->MultiCell($w[2], $maxnocells * 6, '', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[3], $maxnocells * 6, '', 'LR', 'R', 0, 0);
            $pdf->Ln();
            
            // Si el concepto incluye IVA retenido o retención, agrega línea adicional con detalles
            if(isset($concepto[3]) && !empty($concepto[3]))
            {
                $cellcount = array();
                $startX = $pdf->GetX();
                $startY = $pdf->GetY();

                $cellcount[] = $pdf->MultiCell($w[0], 6, "", 0, 'C', 0, 0);

                $pdf->SetXY($startX, $startY);

                $maxnocells = max($cellcount);

                $pdf->MultiCell($w[0], $maxnocells * 6,'','LR','C',0,0);
                $pdf->MultiCell($w[1], $maxnocells * 6,'','LR','L',0,0);
                $pdf->MultiCell($w[2], $maxnocells * 6,'','LR','R',0,0);
                $pdf->MultiCell($w[3], $maxnocells * 6,'','LR','R',0,0);
                $pdf->Ln();
            }
            $i++;

            if($startY > 212)
            // Si el contenido se acerca al final de la hoja, crea una nueva página

            {
                $pdf->MultiCell(180, 6,'','T','C',0,0);
                $pdf->AddPage();
            }
        }
        

        for ($startY; $startY < 200; $startY = $startY + 6) { 
            $pdf->MultiCell($w[0], 6, '---', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], 6, '   -------------------------------------------------------------------------------------------', 'LR', 'L', 0, 0);
            $pdf->MultiCell($w[2], 6, '   ---------------   ', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[3], 6, '   ---------------   ', 'LR', 'R', 0, 0);
            $pdf->Ln();
        }

        // Muestra el resumen de importes: subtotal, descuento, impuestos, retención y total
        $pdf->MultiCell(130, 6, '* Moneda / Currency: ' . $MONEDA, 'T', 'M', 0 , 0);
        $pdf->MultiCell($w[2], 6, 'Sub-Total', 'T', 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[3], 6, "$" . number_format($SUBTOTAL, 2), 1, 'R', 0, 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell(130, 6, '* If you have any questions regarding this Purchase Order please contact the buyer.', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[2], 6, 'Descuento', 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[3], 6, "$" . number_format($DESCUENTO, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell(130, 6, '* Any changes or exceptions must be authorized by the Purchasing Department.', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[2], 6, "IVA / TAX", 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[3], 6, "$" . number_format($IMPUESTO, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell(130, 6, '* Elaborate one invoice for each Purchase Order.', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[2], 6, 'Retencion', 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[3], 6, "$" . number_format($RETENCION, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell(130, 6, "* Favor de contactar al Comprador si usted tiene alguna duda acerca de esta Orden de  Compra.", 0, 'M', 0 , 0);
        $pdf->MultiCell($w[2], 6, 'Total', 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[3], 6, "$" . number_format($TOTAL, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();
        $pdf->Ln();
        
        $pdf->MultiCell(180, 6, '* Cualquier cambio o excepcion debera ser autorizado por el Departamento de Compras.', 0, 'M', 0 , 0);
        $pdf->Ln();

        $pdf->MultiCell(180, 6, '* Elaborar una factura por cada orden de compra.', 0, 'M', 0 , 0);
        $pdf->Ln();
        $pdf->Ln();

        $pdf->MultiCell(180, 6, 'Aprobado por: ' . $APROBADOR . ' (' . $FECHA_APROBACION . ')', 0, 'M', 0 , 0);
        $pdf->Ln();

        // Genera y envía el PDF al navegador
        $pdf->Output($PO . '.pdf', 'I');
    }

    function menu_retroceder(){
    // Carga la vista del menú de opciones para retroceder procesos
    $this->load->view('header');
    $this->load->view('ordenes_compra/retroceder_opciones');
}

function retroceder_qr(){
    // Verifica si el usuario tiene privilegios para retroceder QR
    if($this->session->privilegios['retroceder_qr'])
    {
        // Si tiene privilegios, carga la vista correspondiente
        $this->load->view('header');
        $this->load->view('ordenes_compra/retroceder_qr');
    }
    else
    {
        // Si no tiene privilegios, redirige al inicio
        redirect(base_url('inicio'));
    }
}

function recibir_pr(){
    // Verifica si el usuario tiene privilegios para retroceder QR o PO
    if($this->session->privilegios['retroceder_qr'] || $this->session->privilegios['retroceder_po'])
    {
        // Carga la vista para recibir PR
        $this->load->view('header');
        $this->load->view('ordenes_compra/recibir_pr');
    }
    else
    {
        // Redirige al inicio si no tiene los privilegios requeridos
        redirect(base_url('inicio'));
    }
}

function retroceder_po(){
    // Verifica si el usuario tiene privilegios para retroceder PO
    if($this->session->privilegios['retroceder_po'])
    {
        // Carga la vista correspondiente
        $this->load->view('header');
        $this->load->view('ordenes_compra/retroceder_po');
    }
    else
    {
        // Si no tiene privilegios, redirige al inicio
        redirect(base_url('inicio'));
    }
}

    function historial(){
    // Carga la vista para mostrar el historial general de empresas proveedoras
    $this->load->view('header');
    $this->load->view('ordenes_compra/empresas_historial');
}

function historial_po(){
    // Recibe el ID del proveedor enviado por POST
    $data['id_proveedor'] = $this->input->post("id");

    // Carga la vista del historial de órdenes de compra del proveedor
    $this->load->view('header');
    $this->load->view('ordenes_compra/historial_po', $data);
}

function calendario_seguimiento(){
    // Obtiene la lista de usuarios del área de compras
    $data['usuario'] = $this->compras_model->usuariosCompras();

    // Carga la vista con el calendario de seguimiento
    $this->load->view('header');
    $this->load->view('ordenes_compra/calendario_seguimiento', $data);
}

    function ajax_getPRs(){
    // Se obtienen parámetros enviados por POST
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $id_proveedor = $this->input->post('id_proveedor');
    $tipo = $this->input->post('tipo');
    $moneda = $this->input->post('moneda');

    // Consulta principal para obtener PRs aprobados relacionados con un proveedor
    $query = "SELECT PR.id, E.id as IdProv, E.nombre as Prov, PR.cantidad, PR.tipo, ifnull(JSON_UNQUOTE(PR.atributos->'$.modelo'),'N/A') as Modelo, PR.descripcion, PR.importe, PR.moneda, PR.estatus from prs PR inner join qr_proveedores QR_p on PR.qr_proveedor = QR_p.id inner join empresas E on QR_p.empresa = E.id where 1=1 ";

    // Filtra por proveedor, moneda y tipo si se especifica un proveedor
    if($id_proveedor > 0)
    {
        $query .= " and E.id = '$id_proveedor' and PR.moneda = '$moneda' and PR.tipo = '$tipo'";
    }

    $query .= " and PR.estatus = 'APROBADO'";

    // Filtra según el tipo de búsqueda: por folio, proveedor o contenido
    if(!empty($texto))
    {
        if($parametro == "folio")
        {
            $query .= " and PR.id = '$texto'";
        }
        if($parametro == "proveedor")
        {
            $query .= " having Prov like '%$texto%'";
        }
        if($parametro == "contenido")
        {
            $query .= " and (PR.descripcion like '%$texto%' or UPPER(PR.atributos->'$.marca') like UPPER('%$texto%') or UPPER(PR.atributos->'$.modelo') like UPPER('%$texto%') )";
        }
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

    function ajax_setTempPO(){
    $pr_aprob = []; // Lista de PRs aprobadas
    $pr_rech = [];  // Lista de PRs rechazadas

    // Se obtienen datos del formulario
    $prs = json_decode($this->input->post('prs'));
    $proveedor = $this->input->post('id_prov');
    $moneda = $this->input->post('moneda');
    $tipo = $this->input->post('tipo');

    // Se construye la consulta con los IDs de PRs recibidos
    $query = "SELECT * from prs where 1 != 1";
    foreach ($prs as $elem) {
        $query .= " or id ='$elem'";
    }

    $res = $this->Conexion->consultar($query);
    foreach ($res as $elem) {
        if($elem->estatus == "APROBADO")
        {
            // Se actualiza el estatus de la PR a "EN SELECCION"
            $data['estatus'] = "EN SELECCION";
            $where['id'] = $elem->id;
            $this->Conexion->modificar('prs', $data, null, $where);

            // Se registra el cambio en la bitácora
            $bitacora['pr']=intval($elem->id);
            $bitacora['user']=$this->session->id;
            $bitacora['estatus']="EN SELECCION";
            $this->compras_model->estatusPR($bitacora);

            array_push($pr_aprob, $elem->id); // Se añade a la lista de aprobadas
        }
        else
        {
            array_push($pr_rech, $elem->id); // Se añade a la lista de rechazadas
        }
    }

    // Se genera un registro temporal para construir la PO
    $tempid = uniqid();
    $data2['idtemp'] = $tempid;
    $data2['usuario'] = $this->session->id;
    $data2['proveedor'] = $proveedor;
    $data2['moneda'] = $moneda;
    $data2['tipo'] = $tipo;
    $data2['prs'] = json_encode($pr_aprob);
    $data2['prs_rechazados'] = json_encode($pr_rech);
    $funciones['fecha'] = 'CURRENT_TIMESTAMP()';

    $this->Conexion->insertar('ordenes_compra_temp', $data2, $funciones);

    echo $tempid; // Se devuelve el ID temporal generado
}

    function ajax_getTempPO(){
    $prs = json_decode($this->input->post('prs'));  // Se obtienen los IDs de PRs seleccionadas

    // Se construye la consulta para recuperar los datos de cada PR
    $query = "SELECT PR.*, ifnull(JSON_UNQUOTE(PR.atributos->'$.modelo'),'') as Modelo, ifnull(JSON_UNQUOTE(PR.atributos->'$.marca'),'') as Marca, ifnull(JSON_UNQUOTE(PR.atributos->'$.serie'),'') as Serie, QRP.costos, QRP.factor, P.entrega from prs PR inner join qr_proveedores QRP on PR.qr_proveedor = QRP.id inner join proveedores P on P.empresa = QRP.empresa where 1 != 1";
    foreach ($prs as $elem) {
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

function ajax_saveTempPO(){
    $idtemp = $this->input->post('idtemp'); // ID del registro temporal
    $prs_costos = $this->input->post('prs_costos'); // Costos asignados por PR

    // Se guardan los costos en la tabla temporal
    $data['prs_costos'] = $prs_costos;
    $where['idtemp'] = $idtemp;
    $this->Conexion->modificar('ordenes_compra_temp', $data, null, $where);
    echo "1";
}

function ajax_getPosContruccion(){
    $user = $this->session->id; // ID del usuario en sesión

    // Se recuperan todas las órdenes en construcción del usuario
    $query = "SELECT CT.*, E.nombre as Proveedor FROM ordenes_compra_temp CT inner join empresas E on E.id = CT.proveedor where CT.usuario = '$user'";
    $res = $this->Conexion->consultar($query);

    if($res)
    {
        echo json_encode($res);
    }
}


function ajax_generarPO(){
    $this->load->model('compras_model');
    ini_set('display_errors', 0);

    $moneda = $this->input->post('moneda');
    $tipo = $this->input->post('tipo');
    $usd = 1;

    // Si la moneda es USD, se obtiene el tipo de cambio actual
    if($moneda == "USD")
    {
        $usd = $this->aos_funciones->getUSD()[0];
    }

    // Se decodifican datos recibidos vía POST
    $datos = json_decode($this->input->post('datos'), TRUE);
    $idtemp = $this->input->post('idtemp');
    $prs = $this->input->post('prs');
    $id_prov = $this->input->post('id_prov');  
    $prioridad = 'NORMAL';
    $entrega = $this->input->post('entrega');

    // Datos base para la orden de compra
    $data['usuario'] = $this->session->id;
    $data['prioridad'] = $prioridad;
    $data['proveedor'] = $id_prov;
    $data['tipo'] = $tipo;
    $data['contacto'] = 0;
    $data['prs'] = $prs;
    $data['moneda'] = $moneda;
    $data['estatus'] = 'EN PROCESO';
    $data['billing_address'] = '';
    $data['shipping_address'] = '';
    $data['entrega'] = $entrega;
    $data['metodo_pago'] = 0;
    $data['conceptos'] = '[]';
    $data['subtotal'] = 0;
    $data['descuento'] = '0';
    $data['impuesto'] = '0';
    $data['impuesto_nombre'] = 'Exento (0.00%)';
    $data['impuesto_factor'] = '0.00';
    $data['total'] = 0;
    $data['recurso'] = 'PENDIENTE';
    $data['rma'] = '';
    $data['numero_confirmacion'] = '';
    $data['tipo_cambio'] = $usd;
    $data['retencion'] = 0;
    $data['ivaRet'] = 0;
    $data['publish'] = 1;

    $functions['fecha'] = 'CURRENT_TIMESTAMP()';

    // Inserta la orden de compra principal
    $po_id = $this->Conexion->insertar('ordenes_compra', $data, $functions);
    
    // Registra el estatus de la PO en la bitácora
    $dato['po'] = intval($po_id);
    $dato['user'] = $this->session->id;
    $dato['estatus'] = 'EN PROCESO';
    $this->compras_model->estatusPO($dato);

    // Crea registro vacío para evidencias de la PO y elimina temporal
    $this->Conexion->insertar('ordenes_compra_evidencias', array('po' => $po_id));
    $this->Conexion->eliminar('ordenes_compra_temp', array('idtemp' => $idtemp));
    
    // Se insertan los conceptos seleccionados como parte de la PO
    foreach ($datos as $key => $value) {
        $data2['usuario'] = $this->session->id;
        $data2['po'] = $po_id;
        $data2['pr'] = $value[0];
        $data2['cantidad'] = $value[1];
        $data2['precio_unitario'] = $value[2];
        $data2['importe'] = $value[3];
        $data2['costos'] = json_encode($value[4]);

        $this->Conexion->insertar('ordenes_compra_conceptos', $data2, null);
        $this->Conexion->modificar('prs', array('estatus' => 'EN PO'), null, array('id' => $value[0]));
        
        // Registra el cambio de estatus del PR en la bitácora
        $bitacora['pr'] = intval($value[0]);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = "EN PO";
        $this->compras_model->estatusPR($bitacora);
    }

    echo $po_id;
}

    function ajax_agregarAPO(){
    $datos = json_decode($this->input->post('datos'), TRUE);
    $id_po = $this->input->post('id_po');
    $idtemp = $this->input->post('idtemp');

    // Verifica que la PO esté en estatus válido para agregar conceptos
    $res = $this->Conexion->consultar("SELECT estatus from ordenes_compra where id='$id_po'", TRUE);

    if($res->estatus == 'EN PROCESO')
    {   
        foreach ($datos as $key => $value) {
            // Inserta cada concepto adicional en la PO existente
            $data2['usuario'] = $this->session->id;
            $data2['po'] = $id_po;
            $data2['pr'] = $value[0];
            $data2['cantidad'] = $value[1];
            $data2['precio_unitario'] = $value[2];
            $data2['importe'] = $value[3];
            $data2['costos'] = json_encode($value[4]);

            $this->Conexion->insertar('ordenes_compra_conceptos', $data2, null);

            // Actualiza el estatus del PR a "EN PO"
            $this->Conexion->modificar('prs', array('estatus' => 'EN PO'), null, array('id' => $value[0]));
        }

        // Elimina el registro temporal ya usado
        $this->Conexion->eliminar('ordenes_compra_temp', array('idtemp' => $idtemp));

        echo "1";
    }
    else
    {
        echo "";
    }
}

function ajax_getShippingAddress(){
    // Devuelve las direcciones de envío registradas
    $res = $this->Conexion->get('shipping_address');
    if($res)
    {
        echo json_encode($res);
    }
    else{
        echo "";
    }
}


   function ajax_getBillingAddress(){
    // Obtiene todas las direcciones de facturación desde la base de datos
    $res = $this->Conexion->get('billing_address');
    if($res)
    {
        echo json_encode($res);
    }
    else{
        echo "";
    }
}

    /*
        /////////////////////////////  FUNCIONES DE PO  /////////////////////////////
    */

    function ajax_getPOs(){
    $archivo = $this->input->post('archivo');

    // Consulta principal para obtener órdenes de compra junto con información de usuario, proveedor y contacto
    $query = "SELECT PO.*, concat(U.nombre, ' ', U.paterno) as User, E.nombre as Prov, ifnull(EC.nombre,'NO DEFINIDO') as Contact from ordenes_compra PO left join usuarios U on U.id = PO.usuario left join empresas E on E.id = PO.proveedor left join empresas_contactos EC on EC.id = PO.contacto where 1 = 1";

    // Filtro por proveedor (si se envía desde el frontend)
    if(isset($_POST['proveedor'])){
        $proveedor = $this->input->post('proveedor');
        $query .= " and PO.proveedor = '$proveedor'";
    }

    // Filtro por moneda
    if(isset($_POST['moneda'])){
        $moneda = $this->input->post('moneda');
        $query .= " and PO.moneda = '$moneda'";
    }

    // Filtro por tipo de orden
    if(isset($_POST['tipo'])){
        $tipo = $this->input->post('tipo');
        $query .= " and PO.tipo = '$tipo'";
    }

    // Filtro por estatus
    if(isset($_POST['estatus'])){
        $estatus = $this->input->post('estatus');

        // Caso especial si el estatus es "RECIBIDA TOTAL", se incluye también "RECIBIDA"
        if ($estatus == 'RECIBIDA TOTAL') {
            $estatus = $estatus."') OR (PO.estatus = 'RECIBIDA";
        }

        if($estatus == "TODO") {
            // Cuando se requiere todo excepto vacíos
            $query .= " and (PO.estatus != ' ')";
        } else {
            // Estatus específico
            $query .= " and (PO.estatus = '$estatus')";

            // Filtro por proveedor (duplicado por lógica interna de algunas vistas)
            if(isset($_POST['proveedor'])){
                $proveedor = $this->input->post('proveedor');
                $query .= " and PO.proveedor = '$proveedor'";
            }

            // Filtro por prioridad (pueden ser múltiples)
            if(isset($_POST['prioridad'])){
                $prioridad = json_decode($this->input->post('prioridad'));
                if(count($prioridad) > 0) {
                    $query .= " and ( 1 = 0 ";
                    foreach ($prioridad as $key => $value) {
                        $query .= " or PO.prioridad = '$value'";
                    }
                    $query .= " )";
                }
            }

            // Solo órdenes del último año
            $query .= " and (PO.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ";
        }
    }

    // Búsqueda general por texto, con diferentes parámetros
    if(isset($_POST['texto'])){
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        if(!empty($texto)) {
            if($parametro == "folio") {
                $query .= " and PO.id = '$texto'";
            }
            if($parametro == "usuario") {
                $query .= " having User like '%$texto%'";
            }
            if($parametro == "proveedor") {
                $query .= " having Prov like '%$texto%'";
            }
            if($parametro == "contenido") {
                $query .= " and UPPER(conceptos) like '%".strtoupper($texto)."%'";
            }
        }
    }

    // Solo órdenes publicadas
    $query .= " and PO.publish =1 ";

    // Condicional para mostrar solo órdenes recientes si no se solicita archivo completo
    if ($archivo != 1) {
        $query .= " AND PO.fecha > '2022-01-01 00:00:00' ";
    }

    // Ordena las órdenes por ID descendente (más recientes primero)
    $query .= " order by PO.id desc";

    // Ejecuta la consulta y responde con JSON si hay resultados
    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
}

    function ajax_getPO(){
    $id = $this->input->post('id');
    $revision = $this->input->post('revision');

    // Construcción del subquery para obtener los conceptos de la última revisión o revisión específica
    if (is_null($revision)) {
        $queryrev = "(SELECT conceptos from po_revisiones WHERE po='$id' ORDER BY id DESC LIMIT 1) as concepto";
    } else if (!is_null($revision)) {
        $queryrev = "(SELECT conceptos from po_revisiones WHERE po='$id' and rev='$revision' ORDER BY id DESC LIMIT 1) as concepto";
    }

    // Consulta principal para obtener la orden de compra, incluyendo usuario, aprobador, proveedor, contacto, método de pago y conceptos de revisión
    $query = "SELECT PO.*, concat(U.nombre, ' ', U.paterno) as User, U.correo UserMail, concat(UA.nombre, ' ', UA.paterno) as UserA, P.valResico, E.nombre as Prov, P.rma_requerido, ifnull(EC.nombre,'NO DEFINIDO') as Contact, EC.puesto, EC.correo, ifnull(MP.nombre,'NO DEFINIDO') as MetodoPago, ifnull(MP.tipo,'NO DEFINIDO') as TipoMetodoPago,".$queryrev.", (SELECT rev from po_revisiones WHERE po='$id' ORDER BY id DESC LIMIT 1) as UltRev from ordenes_compra PO left join usuarios UA on UA.id = PO.aprobador inner join usuarios U on U.id = PO.usuario inner join empresas E on E.id = PO.proveedor inner join proveedores P on P.empresa = E.id left join empresas_contactos EC on EC.id = PO.contacto left join empresa_metodos_pago MP on MP.id = PO.metodo_pago where 1 = 1";

    // Filtro específico por ID de la orden de compra
    $query .= " and PO.id = '$id'";

    // Ejecuta la consulta y devuelve el resultado en formato JSON
    $res = $this->Conexion->consultar($query, TRUE);
    if($res){
        echo json_encode($res);
    }
    else {
        echo "";
    }
}

function ajax_getPRsPO(){
    $po = $this->input->post('id');

    // Consulta para obtener los PRs ligados a una orden de compra, con su información de usuario, tipo, descripción, etc.
    $query = "SELECT OCC.pr, concat(U.nombre, ' ', U.paterno) as User, PR.tipo, PR.subtipo, PR.cantidad, PR.descripcion, PR.estatus, PR.recibida from ordenes_compra_conceptos OCC inner join prs PR on PR.id = OCC.pr inner join usuarios U on U.id = PR.usuario where OCC.po = '$po'";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
    else {
        echo "";
    }
}

    function ajax_getConceptosPO(){
    $po = $this->input->post('id');

    // Consulta para obtener todos los conceptos (líneas de detalle) de una orden de compra específica
    $query = "SELECT OCC.* from ordenes_compra_conceptos OCC where OCC.po = '$po'";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
    else {
        echo "";
    }
}

function ajax_getConceptosPO_fromPRS(){
    $po = $this->input->post('id');
    $prs = $this->input->post('prs');
    $prs_act = $this->input->post('prs_actuales');

    // Se actualiza el campo 'prs' de la orden de compra con los PRs actuales seleccionados
    $this->Conexion->modificar('ordenes_compra', array('prs' => $prs_act), null, array('id' => $po));

    $prs = json_decode($prs);

    // Consulta para obtener los conceptos de la orden de compra que coinciden con los PRs seleccionados
    $query = "SELECT OCC.* from ordenes_compra_conceptos OCC where OCC.po = '$po' and ( 1 != 1";
    foreach ($prs as $value) {
        $query .= " or OCC.pr = '$value'";
    }
    $query .= ")";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
    else {
        echo "";
    }
}

function ajax_solicitarAprobacionPO(){
    $this->load->library('recursos_funciones');

    $id = $this->input->post('id');
    $shipping_a = $this->input->post('shipping_a');
    $billing_a = $this->input->post('billing_a');
    $contacto = $this->input->post('contacto');
    $prioridad = 'NORMAL';
    $metodo_pago = $this->input->post('metodo_pago');
    $rma = $this->input->post('rma');
    $conceptos = $this->input->post('conceptos');
    $estatus = $this->input->post('estatus');

    $subtotal = $this->input->post('subtotal');
    $descuento = $this->input->post('descuento');
    $impuesto = $this->input->post('impuesto');
    $impuesto_nombre = $this->input->post('impuesto_nombre');
    $impuesto_factor = $this->input->post('impuesto_factor');
    $total = $this->input->post('total');

    $retencion = $this->input->post('retencion');
    $ivaRet = $this->input->post('ivaRet');

    // Datos adicionales para notificación por correo
    $nombreProveedor = $this->input->post('nombre_proveedor');
    $nombreContacto = $this->input->post('nombre_contacto');

    $recurso = $this->input->post('recurso');
    $fecha_cobro = $this->input->post('fecha_cobro');

    // Se arma el arreglo con los datos actualizados de la orden de compra
    $data['shipping_address'] = $shipping_a;
    $data['billing_address'] = $billing_a;
    $data['contacto'] = $contacto;
    $data['conceptos'] = $conceptos;
    $data['metodo_pago'] = $metodo_pago;
    $data['rma'] = $rma;
    $data['estatus'] = $estatus;
    $data['subtotal'] = $subtotal;
    $data['descuento'] = $descuento;
    $data['impuesto'] = $impuesto;
    $data['impuesto_nombre'] = $impuesto_nombre;
    $data['impuesto_factor'] = $impuesto_factor;
    $data['total'] = $total;
    $data['retencion'] = $retencion;
    $data['ivaRet'] = $ivaRet;

    // Se registra estatus de la orden en la bitácora
    $bitacora['po'] = intval($id);
    $bitacora['user'] = $this->session->id;
    $bitacora['estatus'] = $estatus;
    $this->compras_model->estatusPO($bitacora);

    // Se guarda la revisión de la orden
    $rev['po'] = intval($id);
    $rev['rev'] = 0;
    $rev['user'] = $this->session->id;
    $rev['conceptos'] = $this->input->post('conceptos');
    $this->compras_model->revisionPO($rev);

    $func = null;
    // Si el recurso ya no está pendiente, se registra la fecha de provisión
    if($recurso != "PENDIENTE"){
        $func = array('fecha_provision' => 'CURRENT_TIMESTAMP()');
    }

    $data['recurso'] = $recurso;
    $data['fecha_cobro'] = date("Y-m-d", strtotime($fecha_cobro));
    $data['publish'] = 1;

    $where['id'] = $id;
    // Actualiza la orden de compra con los nuevos datos
    $this->Conexion->modificar('ordenes_compra', $data, $func, $where);

    // Envía notificación si el método de pago activa alertas
    $this->recursos_funciones->NotificacionMinimo($metodo_pago);

    // Prepara datos para enviar correo de creación de orden de compra
    $datos['id'] = $id;
    $datos['fecha'] = date('d/m/Y h:i A');
    $datos['usuario'] = $this->session->nombre;
    $datos['prioridad'] = $prioridad;
    $datos['proveedor'] = $nombreProveedor;
    $datos['contacto'] = $nombreContacto;
    $datos['total'] = $total;

    // Se consulta la lista de correos a notificar
    $correos = [];
    $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.recibirNotPO = 1 and U.activo = 1");
    foreach ($correos_a as $key => $value) {
        array_push($correos, $value->correo);
    }

    $datos['correos'] = array_merge(array($this->session->correo), $correos);
    $this->correos_po->creacionPO($datos);

    echo $id;
}

    function ajax_cancelarPO(){
    $id = $this->input->post('id');

    // Marca la orden de compra como cancelada y la oculta (publish = 0)
    $this->Conexion->comando("UPDATE ordenes_compra set estatus='CANCELADA', publish = 0 where id=$id");

    // Obtiene todos los PR relacionados con esa orden de compra
    $res = $this->Conexion->consultar("SELECT distinct pr from ordenes_compra_conceptos where po = $id");

    // Prepara la consulta para restaurar el estatus de esos PR a 'APROBADO'
    $query2 = "UPDATE prs set estatus='APROBADO' where 1 != 1";
    foreach ($res as $elem) {
        $query2 .= " or id = $elem->pr";

        // Guarda el cambio de estatus en la bitácora
        $bitacora['pr'] = intval($elem->pr);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = "CANCELAR PO #".$po_id; 
        $this->compras_model->estatusPR($bitacora);
    }

    // Ejecuta el update en la tabla de PRs
    $this->Conexion->comando($query2);

    echo "1";
}

function ajax_cancelarTempPO(){
    $idtemp = $this->input->post('idtemp');

    // Consulta los PRs asociados a la orden temporal
    $res = $this->Conexion->consultar("SELECT prs from ordenes_compra_temp where idtemp = '$idtemp'", TRUE);
    $PRS = json_decode($res->prs);

    // Prepara el update para regresar esos PRs a 'APROBADO'
    $query2 = "UPDATE prs set estatus='APROBADO' where 1 != 1";
    foreach ($PRS as $elem) {
        $query2 .= " or id = $elem";

        // Registra el cambio en la bitácora
        $bitacora['pr'] = intval($elem);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = "CANCELAR SELECCION PO";
        $this->compras_model->estatusPR($bitacora);
    }

    // Actualiza los PRs y elimina el registro temporal
    $this->Conexion->comando($query2);
    $this->Conexion->eliminar('ordenes_compra_temp', array('idtemp' => $idtemp));

    echo "1";
}

    function ajax_getMetodos(){
    // Obtiene todos los métodos de pago disponibles para las empresas
    $res = $this->Conexion->get('empresa_metodos_pago', null);
    if($res){
        echo json_encode($res);
    }
}

function agregarComentarioPO() {
    $id = $this->input->post('id');
    $comentario = $this->input->post('comentario');
    $tags = $this->input->post('txtTags');
    $correos = explode(",", $tags); // Convierte la lista de correos separados por coma en arreglo

    // Prepara el comentario a insertar en la tabla
    $data = array(
        'po' => $id,
        'usuario' => $this->session->id,
        'comentario' => $comentario,
    );

    // Fecha automática de registro
    $funciones['fecha'] = 'current_timestamp()';
    
    // Inserta el comentario en la base de datos
    $this->Conexion->insertar('po_comentarios', $data, $funciones);

    if(count($correos) > 0)
    {
        $datos['id'] = $id;
        $datos['comentario'] = $comentario;
        $datos['correos'] = $correos;

        // Posible envío de notificación por correo (actualmente comentado)
        //$this->correos_pr->comentarioPR($datos); VER AQUI
    }

    redirect(base_url('ordenes_compra/ver_po/' . $id));
}

    function ajax_setEstatusMsjPO(){
    $PO = json_decode($this->input->post('PO'));
    $id = $this->input->post('id');
    $estatus = $this->input->post('estatus');

    // Construye el comentario destacando el nuevo estatus
    $comentario_original = $this->input->post('comentario');
    $comentario = "<b><font color='red'>$estatus:</font></b> " . $comentario_original;

    $tags = $this->input->post('txtTags');
    $correos = explode(",", $tags); // Convierte los correos en arreglo

    // Actualiza el estatus de la orden de compra
    $query = "UPDATE ordenes_compra set estatus='" . $estatus . "' where id='" . $id . "'";
    $res = $this->Conexion->comando($query);

    if($res){
        // Inserta el comentario relacionado al cambio de estatus
        $data = array(
            'po' => $id,
            'usuario' => $this->session->id,
            'comentario' => $comentario,
        );
        $funciones['fecha'] = 'current_timestamp()';
        $this->Conexion->insertar('po_comentarios', $data, $funciones);

        // Prepara los datos del PO para el correo
        $PO->comentario = $comentario;
        $PO->UserA = $this->session->nombre;

        // Envía correo de rechazo o estatus por medio de la librería correos_po
        $this->correos_po->rechazarPO($PO);

        echo $comentario;
    }
    else{
        echo "";
    }
}

function ajax_poSetEstatus(){
    $this->load->model('compras_model');

    $PO = json_decode($this->input->post('PO'));
    $id = $this->input->post('id');
    $estatus = $this->input->post('estatus');
    $prs = json_decode($this->input->post('prs'));

    $prioridad = $PO->prioridad;
    $nombreProveedor = $PO->Prov;
    $nombreContacto = $PO->Contact;
    $total = $PO->total;

    // Si el nuevo estatus es AUTORIZADA
    if($estatus == 'AUTORIZADA')
    {
        $data['aprobador'] = $this->session->id;
        $funt['fecha_aprobacion'] = 'CURRENT_TIMESTAMP()';

        $stat_pr = 'PO AUTORIZADA';
        $query = "UPDATE prs set estatus='$stat_pr' where 1 != 1";

        // Bitácora de cambio de estatus de la PO
        $bitacora['po'] = intval($id);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = $stat_pr;
        $this->compras_model->estatusPO($bitacora);

        // Bitácora de cada PR vinculada y actualización en la tabla
        foreach ($prs as $value) {
            $query .= " or id ='$value'";
            $bitacoraPR['pr'] = $value;
            $bitacoraPR['user'] = $this->session->id;
            $bitacoraPR['estatus'] = $stat_pr;
            $this->compras_model->estatusPR($bitacoraPR);
        }

        $this->Conexion->comando($query);

        $PO->UserA = $this->session->nombre;
        $this->correos_po->aprobarPO($PO);
    }

    // Si se cancela la PO
    if($estatus == 'CANCELADA')
    {
        $stat_pr = 'CANCELADO';
        $query = "UPDATE prs set estatus='$stat_pr' where 1 != 1";

        $bitacora['po'] = intval($id);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = $stat_pr;
        $this->compras_model->estatusPO($bitacora);

        foreach ($prs as $value) {
            $query .= " or id ='$value'";
        }

        $this->Conexion->comando($query);

        $PO->UserA = $this->session->nombre;
        $this->correos_po->cancelarPO($PO);
    }

    // Si la PO pasa a estatus ORDENADA
    elseif($estatus == 'ORDENADA'){
        $where['id'] = $id;
        $data['estatus'] = $estatus;
        $this->Conexion->modificar('ordenes_compra', $data, $funt, $where);

        $bitacora['po'] = intval($id);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = $estatus;
        $this->compras_model->estatusPO($bitacora);

        $PO->UserA = $this->session->nombre;
        $this->correos_po->ordenarPO($PO);
    }

    // Cualquier otro estatus (por ejemplo CERRADA)
    else{
        $bitacora['po'] = intval($id);
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = $estatus;
        $this->compras_model->estatusPO($bitacora);

        if ($estatus == 'CERRADA') {
            $PO->UserA = $this->session->nombre;
            $this->correos_po->cerrarPO($PO);
        }
    }

    // Actualiza el estatus de la PO y lo marca como pendiente de pago
    $where['id'] = $id;
    $data['estatus'] = $estatus;
    $data['estatus_pago'] = 'PENDIENTE';
    $this->Conexion->modificar('ordenes_compra', $data, $funt, $where);

    echo "1";
}

    function ajax_subirEvidencia(){
    $campo = $this->input->post('campo');
    $id = $this->input->post('po');

    $where['po'] = $id;
    $funciones['fecha' . $campo] = 'CURRENT_TIMESTAMP()'; // Registra la fecha del archivo subido
    $datos['usuario' . $campo] = $this->session->id; // Guarda el usuario que subió el archivo
    $datos['archivo' . $campo] = file_get_contents($_FILES['file']['tmp_name']); // Guarda el contenido binario del archivo
    $datos['nombre' . $campo] = $this->input->post('nombre') . ".pdf"; // Asigna nombre al archivo (termina en .pdf)

    // Si la modificación de la evidencia fue exitosa
    if ($this->Conexion->modificar('ordenes_compra_evidencias', $datos, $funciones, $where) > 0)
    {
        // Consulta para verificar si ya están ambas evidencias y si el estatus es 'RECIBIDA TOTAL'
        $qry = "SELECT e.*, po.estatus FROM ordenes_compra_evidencias e join ordenes_compra po on e.po = po.id WHERE po = " . $id;
        $res = $this->Conexion->consultar($qry, TRUE);

        // Si ambas evidencias están presentes y el estatus es 'RECIBIDA TOTAL'
        if (!is_null($res->archivo1) && !is_null($res->archivo2) && $res->estatus == 'RECIBIDA TOTAL') {
            $this->Conexion->comando("UPDATE ordenes_compra set estatus = 'LISTA PARA CERRAR' where id = '$id'");
            $bitacora['po'] = intval($id);
            $bitacora['user'] = $this->session->id;
            $bitacora['estatus'] = 'LISTA PARA CERRAR';
            $this->compras_model->estatusPO($bitacora);
        }
        // Alternativa: si el archivo nombre1 inicia con "comp_fact" y el estatus es 'RECIBIDA TOTAL'
        elseif (substr($res->nombre1, 0, 9) == 'comp_fact' && $res->estatus == 'RECIBIDA TOTAL') {
            $this->Conexion->comando("UPDATE ordenes_compra set estatus = 'LISTA PARA CERRAR' where id = '$id'");
            $bitacora['po'] = intval($id);
            $bitacora['user'] = $this->session->id;
            $bitacora['estatus'] = 'LISTA PARA CERRAR';
            $this->compras_model->estatusPO($bitacora);
        }

        echo "1";
    }
    else {
        // En caso de fallo en la subida del archivo
        trigger_error("Error al subir archivo", E_USER_ERROR);
    }

    // Redirige de vuelta a la vista de la PO
    redirect(base_url('/ordenes_compra/ver_po/' . $id));
}

    function ajax_eliminarEvidencia(){
    $po = $this->input->post('po'); // ID de la orden de compra
    $campo = $this->input->post('campo'); // Campo a eliminar (1 o 2)

    $where['po'] = $po;
    $datos['fecha' . $campo] = NULL; // Elimina la fecha del archivo
    $datos['usuario' . $campo] = NULL; // Elimina el ID del usuario que subió
    $datos['archivo' . $campo] = NULL; // Elimina el contenido binario del archivo
    $datos['nombre' . $campo] = NULL; // Elimina el nombre del archivo

    // Limpia los datos del archivo correspondiente en la base
    $this->Conexion->modificar('ordenes_compra_evidencias', $datos, NULL, $where);
    echo "1";
}

function ajax_getEvidenciaInfo(){
    $po = $this->input->post('po');

    // Obtiene la información de evidencias 1 y 2 y sus respectivos usuarios
    $query = "SELECT E.fecha1, E.fecha2, E.nombre1, E.nombre2, E.usuario1, E.usuario2, concat(U1.nombre, ' ', U1.paterno) as User1, concat(U2.nombre, ' ', U2.paterno) as User2 from ordenes_compra_evidencias E left join usuarios U1 on E.usuario1 = U1.id left join usuarios U2 on U2.id = E.usuario2 where E.po = '$po'";

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res); // Devuelve información para mostrarla en la vista
}

function ajax_getPRsAgregadas(){
    $id = $this->input->post('id');

    // Selecciona los PRs ya agregados a la orden de compra
    $query = "SELECT OCC.pr from ordenes_compra_conceptos OCC where OCC.po = '$id'";
    $res = $this->Conexion->consultar($query);

    $prs = array();
    foreach ($res as $row) {
        array_push($prs, $row->pr); // Almacena solo los IDs de PRs
    }

    echo json_encode($prs); // Devuelve un array simple de PRs
}

    function ajax_enviarPO(){
    $body = $this->input->post('body'); // Contenido del correo a enviar
    $para = $this->input->post('para'); // Dirección de correo del proveedor
    $po = $this->input->post('po'); // ID de la orden de compra

    $datos['id'] = $po; // Se asigna el ID de la PO al array de datos
    $datos['correo_proveedor'] = $para; // Se asigna el correo del proveedor
    $datos['body'] = $body; // Se asigna el cuerpo del mensaje

    // Se llama al método que envía el correo al proveedor con la PO
    $this->correos_po->enviarPO_proveedor($datos);
}

    function ajax_recibirPO(){
    $this->load->model('compras_model');
    $id = $this->input->post('id'); // ID de la orden de compra
    $prs = json_decode($this->input->post('prs')); // Lista de PRs asociadas
    $todos = $this->input->post('todos'); // Indicador de si se reciben todas

    // Se obtiene el correo del usuario que generó la PO
    $q="SELECT u.correo from ordenes_compra po JOIN usuarios u on u.id=po.usuario WHERE po.id =".$id;
    $mail=$this->Conexion->consultar($q, TRUE);

    if(boolval($todos)) // Si se reciben todos los productos de la PO
    {
        // Se marca la PO como 'RECIBIDA TOTAL'
        $this->Conexion->comando("UPDATE ordenes_compra set estatus = 'RECIBIDA TOTAL' where id = '$id'");
        $bitacora['po']=intval($id);
        $bitacora['user']=$this->session->id;
        $bitacora['estatus']='RECIBIDA TOTAL';
        $this->compras_model->estatusPO($bitacora);

        // Se registra cada PR como 'POR RECIBIR' en su bitácora
        foreach ($prs as $value) {
            $bitacoraPR['pr']=$value;
            $bitacoraPR['user']=$this->session->id;
            $bitacoraPR['estatus']='POR RECIBIR';
            $this->compras_model->estatusPR($bitacoraPR);
        }
    }

    // Se verifica si ya existen ambas evidencias cargadas
    $qry="SELECT * FROM ordenes_compra_evidencias WHERE po =".$id;
    $res=$this->Conexion->consultar($qry, TRUE);
    
    // Si ambas evidencias están presentes o el nombre1 inicia con 'comp_fact', se marca como 'LISTA PARA CERRAR'
    if (!is_null($res->archivo1) && !is_null($res->archivo2)) {
        $this->Conexion->comando("UPDATE ordenes_compra set estatus = 'LISTA PARA CERRAR' where id = '$id'");
        $bitacora['po']=intval($id);
        $bitacora['user']=$this->session->id;
        $bitacora['estatus']='LISTA PARA CERRAR';
        $this->compras_model->estatusPO($bitacora);
    } elseif (substr($res->nombre1,0, 9) == 'comp_fact') {
        $this->Conexion->comando("UPDATE ordenes_compra set estatus = 'LISTA PARA CERRAR' where id = '$id'");
        $bitacora['po']=intval($id);
        $bitacora['user']=$this->session->id;
        $bitacora['estatus']='LISTA PARA CERRAR';
        $this->compras_model->estatusPO($bitacora);
    }

    // Se actualiza el estatus de las PRs y se registra la fecha/hora de recepción
    $query = "UPDATE prs set estatus = 'POR RECIBIR', recibido = CURRENT_TIMESTAMP() where 1 != 1";
    foreach ($prs as $value) {
        $query .= " or id = '$value'";
    }

    // Se prepara el objeto para enviar notificación por correo
    $PO->id=$id;
    $PO->correo= $mail->correo;
    $PO->recibidor=$this->session->nombre;
    $this->correos_po->recibirPO($PO);

    // Se ejecuta la actualización final de los PRs
    if($this->Conexion->comando($query))
    {
        echo "1";
    }
}

    function evidencia(){
    $po = $this->input->post('po'); // ID de la orden de compra
    $f = $this->input->post('file'); // Número de campo de evidencia (1 o 2)

    // Se obtiene el nombre y contenido binario del archivo correspondiente
    $res = $this->Conexion->consultar("SELECT nombre$f as Nombre, archivo$f as Archivo from ordenes_compra_evidencias where po = '$po'", TRUE);
    $nombre = $res->Nombre;
    $file = $res->Archivo;    
    
    // Encabezados para forzar descarga de archivo PDF
    header('Content-Description: File Transfer');
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename='.$file);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    // Se envía el archivo al navegador
    echo $file;
}

function ajax_retrocederQR(){
    $id = $this->input->post('id'); // ID de la cotización (QR)
    $comentarios = $this->input->post('comentarios'); // Comentario del usuario

    // Se restablece el estatus y campos de liberación de la cotización
    $data['estatus'] = "ABIERTO";
    $data['fecha_liberacion'] = null;
    $data['liberador'] = null;

    $this->Conexion->modificar('requisiciones_cotizacion', $data, null, array('id' => $id));

    // Se registra el comentario en la bitácora con marca de 'RETROCEDIDO'
    $data2['qr'] = $id;
    $data2['usuario'] = $this->session->id;
    $data2['comentario'] = "<b><font color=blue>RETROCEDIDO:</font></b> " . $comentarios;
    $this->Conexion->insertar('qr_comentarios', $data2, array('fecha' => 'CURRENT_TIMESTAMP()'));
}

    function ajax_retrocederPO(){
    $id = $this->input->post('id'); // ID de la orden de compra (PO)
    $comentarios = $this->input->post('comentarios'); // Comentario del usuario
    $prs = json_decode($this->input->post('prs')); // PRs asociadas a la PO

    // Se revierte la PO a estado "EN PROCESO", eliminando aprobador y fechas
    $data['estatus'] = "EN PROCESO";
    $data['fecha_provision'] = null;
    $data['fecha_aprobacion'] = null;
    $data['aprobador'] = null;
    $this->Conexion->modificar('ordenes_compra', $data, null, array('id' => $id));

    // Se agrega un comentario indicando el retroceso
    $data2['po'] = $id;
    $data2['usuario'] = $this->session->id;
    $data2['comentario'] = "<b><font color=blue>RETROCEDIDO:</font></b> " . $comentarios;
    $this->Conexion->insertar('po_comentarios', $data2, array('fecha' => 'CURRENT_TIMESTAMP()'));

    // Se actualiza cada PR relacionada a estatus "EN PO"
    foreach ($prs as $value) {
        $data3['estatus'] = "EN PO";
        $this->Conexion->modificar('prs', $data3, null, array('id' => $value));
    }
}

function ajax_getRecibirPRs(){
    // Se consultan todas las PRs pendientes de recibir, junto con el nombre del usuario
    $query = "SELECT PR.id, PR.fecha, PR.usuario, PR.prioridad, PR.tipo, PR.subtipo, PR.cantidad, PR.unidad, PR.clave_unidad, PR.descripcion, PR.atributos, PR.critico, PR.destino, PR.lugar_entrega, PR.comentarios, PR.estatus, concat(U.nombre, ' ', U.paterno) as User";
    $query .= " from prs PR left join usuarios U on PR.usuario = U.id where 1 = 1 and PR.estatus = 'POR RECIBIR' order by PR.fecha desc";

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
    else{
        echo "";
    }
}

    function requisitores(){
    // Carga la vista principal del catálogo de requisitores
    $this->load->view('header');
    $this->load->view('ordenes_compra/catalogo_requisitores');
}

function ajax_getRequisitores(){
    // Consulta a todos los usuarios activos que pueden crear QR internos o de venta
    $res = $this->Conexion->Consultar("SELECT U.id, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as Requisitor, P.crear_qr_interno as QRI, P.crear_qr_venta as QRV from usuarios U inner join privilegios P on P.usuario = U.id where (P.crear_qr_interno = 1 or P.crear_qr_venta = 1) and U.activo = 1");
    if($res){
        echo json_encode($res);
    }
}

function ajax_getAprobadores(){
    // Obtiene todos los usuarios con privilegio para aprobar PRs, incluyendo cuántos requisitores tienen a su cargo
    $res = $this->Conexion->Consultar("SELECT U.id, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as Aprobador, (SELECT count(R.id) from usuarios R inner join privilegios PP on PP.usuario = R.id where (R.autorizador_compras = U.id and PP.crear_qr_interno = 1) or (R.autorizador_compras_venta = U.id and PP.crear_qr_venta = 1) and R.activo = 1) as RequisitoresACargo from usuarios U inner join privilegios P on P.usuario = U.id where P.aprobar_pr = 1 and U.activo = 1");
    if($res){
        echo json_encode($res);
    }
}

function ajax_getRequisitoresACargo(){
    // Devuelve los requisitores a cargo de un aprobador específico
    $aprobador = $this->input->post("aprobador");
    $res = $this->Conexion->Consultar("SELECT U.id, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as Requisitor from usuarios U inner join privilegios P on P.usuario = U.id where (U.autorizador_compras = '$aprobador' and P.crear_qr_interno = 1) or (U.autorizador_compras_venta = '$aprobador' and P.crear_qr_venta = 1) and U.activo = 1");
    if($res){
        echo json_encode($res);
    }
}

    ////////////// H I S T O R I A L //////////////

function ajax_getEmpresasHistorial(){
    // Obtiene empresas con al menos una orden de compra asociada, filtrando por nombre si se proporciona
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $query = "SELECT E.id, E.foto, E.nombre, E.razon_social, E.cliente, E.proveedor, E.calle, E.numero, E.colonia, (SELECT count(*) from ordenes_compra where proveedor = E.id) as CountPO from empresas E where 1 = 1";
    if($texto){
        if($parametro == "nombre"){
            $query .= " and E.nombre like '%$texto%'";
        }
    }
    $query .= " having CountPO > 0 order by E.nombre";
    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
}

////////////// A C C I O N E S //////////////

function ajax_getAcciones(){
    // Obtiene acciones registradas para una orden de compra específica (PO)
    $po = $this->input->post('po');
    $res = $this->Conexion->consultar("SELECT A.*, concat(U.nombre, ' ', U.paterno) as User from po_acciones A inner join usuarios U on U.id = A.usuario where A.po = $po");
    if($res){
        echo json_encode($res);
    }
}

function ajax_getAcciones_calendar(){
    // Obtiene acciones del calendario, coloreadas según su estatus y fecha de cumplimiento
    $idsub = $this->input->get('idSub');
    $query = "SELECT A.*, concat('Responsable: ',U.nombre, ' ', U.paterno,' ','PO: ', A.po) as title, A.fecha_limite as start, A.fecha_limite + interval 1 hour as end, concat(U.nombre, ' ', U.paterno) as User,";
    $query .= " if(estatus = 'CANCELADA', 'gray', if(estatus = 'PENDIENTE' and current_timestamp() > A.fecha_limite, 'red', if(estatus = 'PENDIENTE' and current_timestamp() <= A.fecha_limite, '#f0ad4e', if(estatus = 'REALIZADA' and A.fecha_realizada > A.fecha_limite, '#76A874', if(estatus = 'REALIZADA' and A.fecha_realizada <= A.fecha_limite, 'green', ''))))) as color";
    $query .= " from po_acciones A inner join usuarios U on U.id = A.usuario";
    if (!empty($idsub)){
        $query .=" where U.id=".$idsub;
    }
    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    }
}

    function ajax_setAccion(){
    // Carga las librerías necesarias para enviar correos
    $this->load->library('correos_archivos');
    $this->load->library('correos');

    // Decodifica los datos enviados desde el frontend
    $accion = json_decode($this->input->post('data'));

    // Asigna el usuario actual y estatus por defecto
    $accion->usuario = $this->session->id;
    $accion->estatus = "PENDIENTE";

    // Dirección de correo electrónico del responsable
    $correo = $this->input->post('correo');

    // Define la fecha de creación automática
    $func['fecha_creacion'] = 'CURRENT_TIMESTAMP()';

    // Inserta la acción en la base de datos y guarda el ID generado
    $id = $this->Conexion->insertar('po_acciones', $accion, $func);

    // Formateo de datos para crear el archivo .ics del evento de calendario
    $fecha = strtotime($accion->fecha_limite);
    $name = "PO : ".$accion->po;
    $start = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $end = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $description = $accion->accion;

    // Contenido del archivo ICS (evento de calendario)
    $ical_content = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//LearnPHP.co//NONSGML {$name}//EN
METHOD:REQUEST
BEGIN:VEVENT
ORGANIZER;CN=".$this->session->nombre.":tickets@masmetrologia.com
UID:".date('Ymd').'T'.date('His').rand()."-learnphp.co
DTSTAMP:".date('Ymd').'T'.date('His')."
DTSTART:{$start}
DTEND:{$end}
SUMMARY:{$name}
DESCRIPTION:{$description}
END:VEVENT
END:VCALENDAR";

    // Datos para enviar el correo con el archivo ICS como adjunto
    $datos['nombre'] = "Seguimiento PO-".$accion->po.".ics";
    $datos['cuerpo'] = "Esto es un evento de seguimiento para la orden de compra - ".$accion->po;
    $datos['cal'] = $ical_content;
    $datos['correo'] = $this->session->correo;
    $datos['accion'] = $description;
    $datos['contacto'] = $correo;
    $datos['correoResponsable'] = $this->session->correo;

    // Envía el evento por correo
    $this->correos_archivos->evento_cotizaciones($datos);

    // Retorna el ID si la acción fue registrada correctamente
    if($id > 0){
        echo $id;
    }
}

    function eventoICS($data)
{
    // Consulta los datos de la acción específica por su ID
    $query = "SELECT * from po_acciones where id = ".$data;
    $res = $this->Conexion->consultar($query, TRUE);

    // Habilita la visualización de errores para depuración
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // Extrae y formatea la fecha para el archivo ICS
    $fecha = strtotime($res->fecha_limite);
    $name = "PO : ".$res->po;
    $start = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $end = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $description = $res->accion;

    // Genera un slug simplificado del nombre (aunque no se usa)
    $slug = strtolower(str_replace(array(' ', "'", '.'), array('_', '', ''), $name));

    // Generación del contenido del archivo ICS para el evento
    echo "BEGIN:VCALENDAR\n";
    echo "VERSION:2.0\n";
    echo "PRODID:-//LearnPHP.co//NONSGML {$name}//EN\n";
    echo "METHOD:REQUEST\n";
    echo "BEGIN:VEVENT\n";
    echo "ORGANIZER;CN=".$this->session->nombre.":tickets@masmetrologia.com\n";
    echo "UID:".date('Ymd').'T'.date('His')."-".rand()."-learnphp.co\n";
    echo "DTSTAMP:".date('Ymd').'T'.date('His')."\n";
    echo "DTSTART:{$start}\n";
    echo "DTEND:{$end}\n";
    echo "SUMMARY:{$name}\n";
    echo "DESCRIPTION: {$description}\n";
    echo "END:VEVENT\n";
    echo "END:VCALENDAR\n";
    // Cabeceras para forzar la descarga como archivo de calendario
    header("Content-type: text/calendar; charset=utf-8");
    header("Content-Disposition: attachment; filename=Seguimiento PO - ".$res->po.".ics");
}


    function ajax_updateAccion(){
    // Decodifica los datos enviados desde el frontend
    $accion = json_decode($this->input->post('data'));

    // Actualiza la acción en la base de datos según su ID
    $id = $this->Conexion->modificar('po_acciones', $accion, null, array('id' => $accion->id));
}


    function ajax_setAccionRealizada(){
    // Decodifica los datos recibidos
    $accion = json_decode($this->input->post('data'));

    // Marca la acción como realizada con fecha actual
    $func['fecha_realizada'] = 'CURRENT_TIMESTAMP()';

    // Actualiza en base de datos el estatus y fecha de realización
    $id = $this->Conexion->modificar('po_acciones', $accion, $func, array('id' => $accion->id));
}

    function ajax_setAccionComentario(){
    // Recibe un comentario en formato JSON y decodifica
    $comment = json_decode($this->input->post('data'));

    // Asigna el ID del usuario actual desde la sesión
    $comment->usuario = $this->session->id;
    $func['fecha'] = 'CURRENT_TIMESTAMP()';
    $id = $this->Conexion->insertar('po_acciones_comentarios', $comment, $func);

    if($id > 0){
        echo $id;
    }
}


    function ajax_getAccionComentarios(){
        // Obtiene el ID de la acción a consultar
        $accion = $this->input->post('accion');
        // Consulta los comentarios asociados a esa acción, incluyendo nombre del usuario
        $res = $this->Conexion->consultar("SELECT C.*, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as User from po_acciones_comentarios C inner join usuarios U on U.id = C.usuario where C.accion = $accion");
        if($res)
        {
            echo json_encode($res);    
        }
    }

    function ajax_setNoConfirmacion(){
        // Recoge datos del número de confirmación desde POST

        $data['id'] = $this->input->post('po');
        $data['numero_confirmacion'] = $this->input->post('numero');
        
        $this->Conexion->modificar('ordenes_compra', $data, null, array('id' => $data['id']));
    }

    function exportarPO()
{
    // Consulta para obtener las órdenes de compra del último año, con datos del requisitor, proveedor y contacto
    $query = 'SELECT PO.id as PO, PO.fecha as Fecha,concat(U.nombre, " ", U.paterno) as Requisitor, E.nombre as Proveedor, ifnull(EC.nombre,"NO DEFINIDO") as Contacto, PO.entrega as Entrega,PO.estatus as Estatus from ordenes_compra PO inner join usuarios U on U.id = PO.usuario inner join empresas E on E.id = PO.proveedor left join empresas_contactos EC on EC.id = PO.contacto where 1 = 1 and (PO.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) order by PO.id desc';

    // Ejecuta la consulta y convierte el resultado en arreglo
    $result = $this->db->query($query)->result_array();

    // Genera un timestamp para el nombre del archivo
    $timestamp = date('m/d/Y', time());

    // Define el nombre del archivo Excel
    $filename = 'PO_' . $timestamp . '.xls';

    // Encabezados para exportación como archivo Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");

    $isPrintHeader = false;

    // Recorre los resultados para imprimir encabezado y datos en formato tabulado
    foreach ($result as $row) {
        if (!$isPrintHeader) {
            echo implode("\t", array_keys($row)) . "\n"; // Encabezados
            $isPrintHeader = true;
        }
        echo implode("\t", array_values($row)) . "\n"; // Datos por fila
    }

    exit; // Finaliza la ejecución y descarga el archivo
}

    function ajax_getNombresUsuarios(){
    $ids = $this->input->post('POAc'); // Se obtiene el ID de la orden de compra desde el POST (variable 'POAc')

    $query = "SELECT concat(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus from bitacora_po b JOIN usuarios u on b.user=u.id where po=". $ids; // Consulta para obtener nombre del usuario, fecha y estatus de la bitácora de la PO

    $res = $this->Conexion->consultar($query); // Ejecuta la consulta
    if($res){ echo json_encode($res); } // Si hay resultados, los convierte a JSON y los devuelve
}


    function agregarRastreo() {
    $id = $this->input->post('id'); // ID de la orden de compra
    $tracking = $this->input->post('txtTracking'); // Número de rastreo ingresado
    $links = $this->input->post('txtLinks'); // Link(s) de seguimiento

    $data = array('po' => $id, 'usuario' => $this->session->id, 'trackNum' => $tracking, 'enlace' => $links); // Datos a insertar en la tabla de rastreo
    $funciones['fecha'] = 'current_timestamp()'; // Función SQL para registrar fecha actual

    $this->Conexion->insertar('po_rastreos', $data, $funciones); // Inserta el rastreo en la tabla correspondiente

    redirect(base_url('ordenes_compra/ver_po/' . $id)); // Redirige al detalle de la orden de compra
}

    function ajax_generarPOTemp(){
    $this->load->model('compras_model');
    ini_set('display_errors', 0); // Desactiva la visualización de errores en pantalla

    $moneda = $this->input->post('moneda'); // Moneda seleccionada (MXN o USD)
    $tipo = $this->input->post('tipo'); // Tipo de orden (compra/servicio/etc.)
    $usd = 1; // Valor por defecto del tipo de cambio

    if($moneda == "USD"){ $usd = $this->aos_funciones->getUSD()[0]; } // Si es USD, se obtiene tipo de cambio actual

    $datos = json_decode($this->input->post('datos'), TRUE); // Conceptos en formato JSON (arreglo)
    $idtemp = $this->input->post('idtemp'); // ID temporal (no se usa aquí, pero puede usarse para limpieza posterior)
    $prs = $this->input->post('prs'); // PRs asociadas a la orden
    $id_prov = $this->input->post('id_prov'); // ID del proveedor
    $prioridad = 'NORMAL'; // Prioridad fija por defecto
    $entrega = $this->input->post('entrega'); // Fecha estimada de entrega

    // Datos base para crear la PO temporal
    $data['usuario'] = $this->session->id;
    $data['prioridad'] = $prioridad;
    $data['proveedor'] = $id_prov;
    $data['tipo'] = $tipo;
    $data['contacto'] = 0;
    $data['prs'] = $prs;
    $data['moneda'] = $moneda;
    $data['estatus'] = 'EN PROCESO';
    $data['billing_address'] = '';
    $data['shipping_address'] = '';
    $data['entrega'] = $entrega;
    $data['metodo_pago'] = 0;
    $data['conceptos'] = '[]';
    $data['subtotal'] = 0;
    $data['descuento'] = '0';
    $data['impuesto'] = '0';
    $data['impuesto_nombre'] = 'Exento (0.00%)';
    $data['impuesto_factor'] = '0.00';
    $data['total'] = 0;
    $data['recurso'] = 'PENDIENTE';
    $data['rma'] = '';
    $data['numero_confirmacion'] = '';
    $data['tipo_cambio'] = $usd;
    $data['retencion'] = 0;
    $data['ivaRet'] = 0;

    $functions['fecha'] = 'CURRENT_TIMESTAMP()'; // Fecha de creación actual

    $po_id = $this->Conexion->insertar('po_temp', $data, $functions); // Inserta PO temporal en base de datos

    // Bitácora para la nueva PO generada
    $dato['po']=intval($po_id);
    $dato['user']=$this->session->id;
    $dato['estatus']='EN PROCESO';
    $this->compras_model->estatusPO($dato);

    $this->Conexion->insertar('ordenes_compra_evidencias', array('po' => $po_id)); // Prepara tabla de evidencias para la PO

    // Recorre cada concepto recibido y lo registra en la base
    foreach ($datos as $key => $value) {
        $data2['usuario'] = $this->session->id;
        $data2['po'] = $po_id;
        $data2['pr'] = $value[0]; // ID de PR
        $data2['cantidad'] = $value[1]; // Cantidad solicitada
        $data2['precio_unitario'] = $value[2]; // Precio unitario
        $data2['importe'] = $value[3]; // Total por concepto
        $data2['costos'] = json_encode($value[4]); // Costos asociados codificados en JSON

        $this->Conexion->insertar('ordenes_compra_conceptos', $data2, null); // Inserta cada concepto en la tabla correspondiente
        $this->Conexion->modificar('prs', array('estatus' => 'EN PO'), null, array('id' => $value[0])); // Actualiza PR a estado EN PO

        $bitacora['pr']=intval($value[0]);
        $bitacora['user']=$this->session->id;
        $bitacora['estatus']="EN PO #".$po_id;
        $this->compras_model->estatusPR($bitacora); // Registra movimiento en bitácora PR
    }

    echo $po_id; // Devuelve ID de la nueva PO temporal generada
}


function guardarPO(){
        $this->load->library('recursos_funciones');
        
        // ID de la orden de compra que se va a actualizar
        $id = $this->input->post('id');

        $est =$this->compras_model->getLastSt($id);
        $estatus = strval($est->estatus);
        $shipping_a = $this->input->post('shipping_a');
        $billing_a = $this->input->post('billing_a');
        $contacto = $this->input->post('contacto');
        $prioridad = 'NORMAL'; 
        $metodo_pago = $this->input->post('metodo_pago');
        $rma = $this->input->post('rma');
        $conceptos = $this->input->post('conceptos');

        $subtotal = $this->input->post('subtotal');
        $descuento = $this->input->post('descuento');
        $impuesto = $this->input->post('impuesto');
        $impuesto_nombre = $this->input->post('impuesto_nombre');
        $impuesto_factor = $this->input->post('impuesto_factor');
        $total = $this->input->post('total');

        $retencion = $this->input->post('retencion');
        $ivaRet = $this->input->post('ivaRet');
        ///////////
        $nombreProveedor = $this->input->post('nombre_proveedor');
        $nombreContacto = $this->input->post('nombre_contacto');

        $recurso = $this->input->post('recurso');
        $fecha_cobro = $this->input->post('fecha_cobro');

        $data['shipping_address'] = $shipping_a;
        $data['billing_address'] = $billing_a;
        $data['contacto'] = $contacto;
        $data['conceptos'] = $conceptos;
        $data['metodo_pago'] = $metodo_pago;
        $data['rma'] = $rma;
        $data['estatus'] = strval($estatus);

        $data['subtotal'] = $subtotal;
        $data['descuento'] = $descuento;
        $data['impuesto'] = $impuesto;
        $data['impuesto_nombre'] = $impuesto_nombre;
        $data['impuesto_factor'] = $impuesto_factor;
        $data['total'] = $total;

        $data['retencion'] = $retencion;
        $data['ivaRet'] = $ivaRet;

        // Registra el cambio de estatus en la bitácora de la PO
        $bitacora['po']=intval($id);
        $bitacora['user']=$this->session->id;
        $bitacora['estatus']=strval($estatus);
        $this->compras_model->estatusPO($bitacora);

        // Se obtiene la última revisión de la PO para registrar una nueva
        $res = $this->Conexion->consultar("SELECT rev from po_revisiones where po = ".intval($id)." ORDER by id DESC LIMIT 1");
        $elem =null;
        foreach($res as $elem){
            $r= $elem->rev;
        }
        $revision=intval($r)+1;
        $rev['po']=intval($id);
        $rev['rev']=$revision;
        $rev['user']=$this->session->id;
        $rev['conceptos']=$this->input->post('conceptos');
        $this->compras_model->revisionPO($rev);


        $func = null;
        if($recurso != "PENDIENTE"){
            $func = array('fecha_provision' => 'CURRENT_TIMESTAMP()');
        }
        $data['recurso'] = $recurso;
        $data['fecha_cobro'] = date("Y-m-d", strtotime($fecha_cobro));
        $data['publish'] = 1;

        $where['id'] = $id;
        $this->Conexion->modificar('ordenes_compra', $data, $func, $where);
        $this->recursos_funciones->NotificacionMinimo($metodo_pago);

        // Se prepara la cadena de costos para ser guardada correctamente
        $conc=$this->input->post('concep');
        $concTemp=str_replace("[", "",$conc);
        $concTemp=str_replace("]", "",$concTemp);
        $concTemp=str_replace("", "",$concTemp);
        $concTemp=stripslashes($concTemp);
        $conc ="{".$concTemp."}";

        $dataC['costos'] = $conc;
        $whereC['po'] = $id;
        $funcC = null;

        // Se guarda la información actualizada de la PO en la base de datos
        $this->Conexion->modificar('ordenes_compra_conceptos', $dataC, $funcC, $whereC);
        
        $datos['id'] = $id;
        $datos['fecha'] = date('d/m/Y h:i A');
        $datos['usuario'] = $this->session->nombre;
        $datos['prioridad'] = $prioridad;
        $datos['proveedor'] = $nombreProveedor;
        $datos['contacto'] = $nombreContacto;
        $datos['total'] = $total;

        $correos = [];

        // Se obtienen los correos de usuarios con privilegio para recibir notificaciones de PO
        $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.recibirNotPO = 1");
        foreach ($correos_a as $key => $value) {
            array_push($correos, $value->correo);
        }
        $datos['correos'] = array_merge(array($this->session->correo), $correos);
        
        // Se envía la notificación por correo de creación/modificación de la PO
        $this->correos_po->creacionPO($datos);
        echo $id;
    }
    
    function getArchivosPrs(){
    $prs = $this->input->post('prs');
    $pr = explode(',', $prs);

    foreach($pr as $elem){
        // Se limpia el valor de la PR eliminando corchetes
        $prF = str_replace("[", "", $elem);
        $prF = str_replace("]", "", $prF);

        // Se construye la consulta para obtener el archivo de la PR
        $query = "SELECT q.archivo, p.id from prs p JOIN qr_proveedores q on q.qr = p.qr WHERE p.id = " . $prF;

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            // Se devuelve la información en formato JSON
            echo json_encode($res);
        }
    }
}

    function entregaParcial(){
    $pr = $this->input->post('pr');
    $qty = $this->input->post('qty');
    $po = $this->input->post('po');

    // Se obtiene la información actual de la PR
    $query = 'select * from prs where id=' . $pr;
    $res = $this->Conexion->consultar($query, TRUE);

    // Se calcula la cantidad total recibida con lo nuevo
    $cant = $qty + intval($res->recibida);

    // Se valida que no se exceda la cantidad solicitada y que la cantidad ingresada no sea 0
    if ($cant <= intval($res->cantidad) && $qty != 0) {
        // Se actualiza la PR como recibida parcialmente
        $data['recibida'] = $cant;
        $data['estatus'] = 'PARCIAL';
        $where['id'] = $pr;
        $res = $this->Conexion->modificar('prs', $data, null, $where);

        // Se marca la PO como recibida parcialmente
        $dataPo['estatus'] = 'RECIBIDA PARCIAL';
        $wherePo['id'] = $po;
        $res = $this->Conexion->modificar('ordenes_compra', $dataPo, null, $wherePo);

        // Se guarda bitácora para PR
        $bitacora['pr'] = $pr;
        $bitacora['user'] = $this->session->id;
        $bitacora['estatus'] = "PARCIAL : " . $qty;
        $this->compras_model->estatusPR($bitacora);

        // Se guarda bitácora para PO
        $bitacoraPo['po'] = $po;
        $bitacoraPo['user'] = $this->session->id;
        $bitacoraPo['estatus'] = "RECIBIDA PARCIAL";
        $this->compras_model->estatusPO($bitacoraPo);

        if ($res) {
            echo '1';
        }
    }
}
}


