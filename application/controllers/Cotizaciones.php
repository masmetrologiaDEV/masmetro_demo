<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizaciones extends CI_Controller {
public $idsub=null;

    function __construct() {
        parent::__construct();
        $this->load->library('correos_cotizaciones');
	$this->load->model('privilegios_model');
    }

/*    function index(){
	$this->output->delete_cache('cotizaciones/catalogo');
        $this->load->view('header');
        $this->load->view('cotizaciones/catalogo');
    }*/
function index($estatus = 'TODO', $user=""){
	$us=null;
        $data['estatus'] = strtoupper($estatus);
	    $data['user'] = strtoupper($user);
        $this->load->model('inicio_model');
        $this->load->view('header');
        $this->load->view('cotizaciones/catalogo', $data);
    }

    function crear_cotizacion(){
        $usd = $this->aos_funciones->getUSD();
        $data["id"] = 0;
        $data['USD'] = $usd[0];
        $data['USD_ACT'] = $usd[1];
        $data['COPY'] = 0;
        
        if(isset($_POST["id"]))
        {
            $id = $this->input->post('id');
            $rev = $this->input->post('rev');
            $data['COPY'] = $id . '-' . $rev;
        }

        $this->load->view('header');
        $this->load->view('cotizaciones/generar', $data);
    }

    function ver_cotizacion($id = 0){
        $this->load->model('usuarios_model');
        $data['sub']=$this->usuarios_model->userCots();
        $data['COPY'] = 0;
        $usd = $this->aos_funciones->getUSD();
        $data['USD'] = $usd[0];
        $data['USD_ACT'] = $usd[1];

        if(isset($_POST["id"]))
        {
            $id = $this->input->post('id');
        }
        else if ($id == 0){
            redirect(base_url('inicio'));
        }

        $data["id"] = $id;

        $this->load->view('header');
        $this->load->view('cotizaciones/generar', $data);
    }

    function dashboard(){
	$this->load->model('usuarios_model');
        $datos['sub']=$this->usuarios_model->userCots();
        $this->load->view('header');
        $this->load->view('cotizaciones/dashboard',$datos);
    }

    function cotizacion_pdf($param, $modo='I'){
        $param = explode('-', $param);
        $id = $param[0];
        $rev = $param[1];

        $query = "SELECT (SELECT ifnull(max(CC2.revision), 0) from cotizaciones_conceptos CC2 where CC2.cotizacion = $id) as UltRev, C.fecha, C.moneda, C.tipo, C.impuesto_factor,";
        $query .= " C.impuesto_nombre, C.aprobador, C.estatus, E.razon_social, E.calle, E.numero, E.numero_interior, E.colonia, E.ciudad, E.estado, E.pais, E.rfc, EC.nombre,";
        $query .= " EC.telefono, EC.correo, (if(E.credito_cliente = 1, concat(E.credito_cliente_plazo, ' Días'), 'Contado')) as Credito, concat(R.nombre, ' ', R.paterno) as Resp,";
        $query .= " R.correo as RespCorreo, CC.*, C.planta, ifnull(EP.nombre, 'N/A') as PlantaNombre, ifnull(EP.calle, 'N/A') as PlantaCalle, ifnull(EP.colonia, 'N/A') as PlantaColonia, ifnull(EP.ciudad, 'N/A') as PlantaCiudad, ifnull(EP.estado, 'N/A') as PlantaEstado";
        $query .= " FROM cotizaciones_conceptos CC inner join cotizaciones C on C.id = CC.cotizacion";
        $query .= " left join empresa_plantas EP on EP.id = C.planta";
        $query .= " inner join empresas E on E.id = C.empresa inner join empresas_contactos EC on EC.id = C.contactos->'$[0]'";
        $query .= " inner join usuarios R on R.id = C.responsable where CC.cotizacion = $id and CC.revision = $rev";

        $res = $this->Conexion->consultar($query);


        $conceptos = [];
        $APROB = -1;
        $ESTATUS = "";
        $SERVICIOS = new stdClass;
        $SUBTOTAL = 0;
        $IMPUESTO = 0;
        $TOTAL = 0;



        foreach ($res as $i => $elem) {
            $OBS = $elem->UltRev != $rev ? " OBSOLETA" : "";
            $APROB = $elem->aprobador;
            $ESTATUS = $elem->estatus;
            $COT = 'COT-' . str_pad($elem->cotizacion, 6, "0", STR_PAD_LEFT) . " Rev: " . $rev . $OBS;
            $FECHA = date_format(date_create($elem->fecha), 'd/m/Y h:i A');
            $CLIENTE = $elem->razon_social;
            $DOMICILIO = $elem->calle . ' ' . $elem->numero . ' ' . $elem->numero_interior;
            $COLONIA = $elem->colonia;
            $RFC = $elem->rfc;

            
            $CONTACTO = $elem->nombre;
            $TELEFONO = $elem->telefono;
            $CORREO = $elem->correo;
            $UBICACION = $elem->ciudad . ', ' . $elem->estado . ', ' . $elem->pais;

            $RESPONSABLE = $elem->Resp;
            $RESPONSABLE_CORREO = $elem->RespCorreo;

            $ID_PLANTA = $elem->planta;
            $NOMBRE_PLANTA = $elem->PlantaNombre;
            $CALLE_PLANTA = $elem->PlantaCalle;
            $COLONIA_PLANTA = $elem->PlantaColonia;
            $CIUDAD_PLANTA = $elem->PlantaCiudad;
            $ESTADO_PLANTA = $elem->PlantaEstado;



            ////////////////////////////////////////////////////////////////////////////////////
            $concepto = new stdClass;
            $concepto->cantidad = $elem->cantidad;

            $concepto->atributos = json_decode($elem->atributos);

            $concepto->descripcion = $elem->descripcion;
            $concepto->servicios = json_decode($elem->servicios);

            foreach ($concepto->servicios as $key => $value) {
                $cod = $value[1];
                if($cod != "N/A")
                {
                    $SERVICIOS->$cod = $value[2];
                }
            }

            $concepto->comentarios = $elem->comentarios;
            $concepto->tiempo_entrega = $elem->tiempo_entrega;
            $concepto->sitio = $elem->sitio;
            $concepto->precio_unitario = $elem->precio_unitario;
            $concepto->importe = floatval($elem->precio_unitario) * floatval($elem->cantidad);
            array_push($conceptos, $concepto);

            ////////////////////////////////////////////////////////////////////////////////////
            
            $IMPUESTO_NOMBRE = "IVA / TAX [" . ($elem->impuesto_factor)*100 . "%]";
            $MONEDA = $elem->moneda;
            $CREDITO = $elem->Credito;

            $SUBTOTAL += $concepto->importe;
            $IMPUESTO += $concepto->importe * ($elem->impuesto_factor);
            $TOTAL += $concepto->importe * ($elem->impuesto_factor + 1);

            $NOTAS = "TERMINOS Y CONDICIONES:";

            switch ($elem->tipo) {
                case 'CALIBRACION':
                    $Margin = 195;
                    $NOTAS .= ''
                    . "\n1.- El servicio se programa con la orden de compra correspondiente. Si su orden de compra incluye mas de un equipo se requiere que los mismos se calibren dentro de un periodo de 30 días máximo."
                    . "\n2.- La Cotización es válida por 120 días a partir de la fecha de elaboración."
                    . "\n3.- El servicio de calibración descrito se ofrece usando los métodos y procedimientos internos del laboratorio, si existen requisitos específicos del cliente, se iniciará nuevamente el proceso de cotización."
                    . "\n4.- El costo del servicio es aplicable aun cuando su equipo no pase la calibración o no responda al proceso de ajuste, en cuyo caso, un reporte con información detallada de la falla presentada por su equipo le será entregado."
                    . "\n5.- No hay garantía de que el equipo mantendrá las tolerancias especificadas a lo largo del intervalo de la calibración, esto debido a deriva, medio ambiente, manejo y otras situaciones fuera de nuestro control."
                    . "\n6.- Identificación y frecuencia de calibración son asignadas por el cliente, asegurarse de proporcionarlas con su aprobación para agilizar la emisión de los documentos del servicio."
                    . "\n7.- El tiempo de entrega está sujeto a programación y transcurre a partir de la aprobación y de la recepción física del instrumento."
		    . "\n8.- Cuando el cliente no proporciona regla de decisión los criterios de aceptación y rechazo en nuestros certificados no consideran la incertidumbre salvo que se indique lo contrario en el propio certificado."
                    . "\nESTE SERVICIO CONSTA DE:"
                    . "\n1.- Revisión del Equipo y sus funciones generales / Limpieza externa del instrumento."
                    . "\n2.- Calibración del Equipo.";
                    break;
                
                case 'ESTUDIO DIMENSIONAL':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- Para programar e iniciar el servicio se requiere de la orden de compra correspondiente,\nUNA VEZ RECIBIDA SE LE CONFIRMA FECHA DE ENTREGA DE RESULTADOS."
                    . "\n2.- Favor de generar la orden de compra de acuerdo a la presente cotización y sus requisitos internos."
                    . "\n3.- La Cotización es válida por 120 días a partir de la fecha de elaboración."
                    . "\n4.- Se requieren planos legibles."
                    . "\n5.- La realización del servicio puede requerir seccionar piezas, provea la cantidad de piezas necesarias.";
                    break;

                case 'RENTA':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- Para programar e iniciar el servicio se requiere de la orden de compra correspondiente."
                    . "\n2.- Favor de generar la orden de compra de acuerdo a la presente cotización y sus requisitos internos."
                    . "\n3.- La Cotización es válida por 30 días a partir de la fecha de elaboración."
                    . "\nESTE SERVICIO CONSTA DE:"
                    . "\n1.- El equipo se entrega en su planta el día que usted lo requiera."
                    . "\n2.- Se instruirá a su personal sobre la operación y uso correcto del equipo."
                    . "\n3.- Se instalará el equipo donde usted lo disponga (solo básculas y contadores de componentes).";
                    break;

                case 'REPARACION':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- Para programar e iniciar el servicio se requiere de la orden de compra correspondiente."
                    . "\n2.- Favor de generar la orden de compra de acuerdo a la presente cotización y sus requisitos internos."
                    . "\n3.- La Cotización es válida por 15 días a partir de la fecha de elaboración.";
                    break;

                case 'VENTA':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- Favor de generar la orden de compra de acuerdo a la presente cotización y sus requisitos internos."
                    . "\n2.- La Cotización es válida por 30 días a partir de la fecha de elaboración."
                    . "\n3.- Pedido No Cancelable."
                    . "\n4.- Equipo disponible salvo previa venta si no se tiene confirmado por la orden de compra."
                    . "\n5.- Se requiere una orden de compra por escrito para realizar la entrega del equipo."
                    . "\n6.- Al recibir su Orden de Compra se le confirmara la fecha de entrega actualizada.";
                    break;

                case 'SOPORTE':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- Para programar e iniciar el servicio se requiere de la orden de compra correspondiente."
                    . "\n2.- Favor de generar la orden de compra de acuerdo a la presente cotización y sus requisitos internos."
                    . "\n3.- La Cotización es válida por 30 días a partir de la fecha de elaboración.";
                    break;

                case 'CALIBRACION EXTERNA':
                    $Margin = 205;
                    $NOTAS .= ''
                    . "\n1.- El servicio se programa con la orden de compra correspondiente y el equipo a disposición de Metrología Aplicada y Servicios. Si su orden de compra incluye más de un equipo se requiere que los mismos se calibren dentro de un periodo de 30 días máximo."
                    . "\n2.- La Cotización es válida por 30 días a partir de la fecha de elaboración."
                    . "\n3.- El servicio de calibración descrito se ofrece con el proveedor especificado en la cotización, si el cliente desea que su equipo sea enviado a otro proveedor distinto al especificado, se iniciara nuevamente el proceso de cotización."
                    . "\n4.- El costo del servicio es aplicable aun cuando su equipo no pase la calibración o no responda al proceso de ajuste, en cuyo caso, un reporte con información detallada de la falla presentada por su equipo le será entregado."
                    . "\n5.- No hay garantía de que el equipo mantendrá las tolerancias especificadas a lo largo del intervalo de la calibración, esto debido a deriva, medio ambiente, manejo y otras situaciones fuera de nuestro control."
                    . "\n6.- Tiempos de entrega sujetos a cambio, cualquier cambio en la programación se notificará con anticipación."
                    . "\n7.- El tiempo de entrega está sujeto a programación y transcurre a partir de la aprobación y de la recepción física del instrumento."
                    . "\nESTE SERVICIO CONSTA DE:"
                    . "\n1.- Revisión del Equipo y sus funciones generales / Limpieza externa del instrumento."
                    . "\n2.- Calibración del Equipo";
                    break;

                case 'MAPEO':
                    $Margin = 215;
                    $NOTAS .= ''
                    . "\n1.- El servicio se programa con la orden de compra correspondiente."
                    . "\n2.- Se requiere que el equipo sea puesto a disposición de Metrología Aplicada y Servicios a nuestra llegada a su planta para la realizacion del servicio."
                    . "\n3.- La cotizacion es por el servicio a ser realizado en el tiempo establecido en la cotizacion, en caso de requerir mas tiempo por causas ajenas a nosotros, se le cotizara aparte."
                    . "\n4.- En caso de cambio en el requerimiento o puntos adicionales, se le presentara la propuesta complementaria para su autorizacion."
                    . "\n5.- La Cotización es válida por 60 días a partir de la fecha de elaboración.";
                    break;

                case 'LISTA PRECIOS':
                    $Margin = 210;
                    $NOTAS .= ''
                    . "\n1.- El servicio se programa con la orden de compra correspondiente."
                    . "\n2.- El servicio de calibración descrito se ofrece usando los métodos y procedimientos internos del laboratorio, si existen requisitos específicos del cliente, se iniciara nuevamente el proceso de cotización."
                    . "\n3.- El costo del servicio es aplicable aun cuando su equipo no pase la calibración o no responda al proceso de ajuste, en cuyo caso, un reporte con información detallada de la falla presentada por su equipo le será entregado."
                    . "\n4.- No hay garantía de que el equipo mantendrá las tolerancias especificadas a lo largo del intervalo de la calibración, esto debido a deriva, medio ambiente, manejo y otras situaciones fuera de nuestro control."
                    . "\n5.- La vigencia de la presente cotización se establecerá en el contrato o acuerdo correspondiente."
                    . "\nESTE SERVICIO CONSTA DE:"
                    . "\n1.- Revisión del Equipo y sus funciones generales / Limpieza externa del instrumento."
                    . "\n2.- Calibración del Equipo";
                    break;
            }
        }


        ini_set('display_errors', 0);
        $this->load->library('pdfview');

        if($ESTATUS == "CANCELADA")
        {
            $pdf = new pdfview_CANCEL(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        }
        else
        {
            if($APROB > 0)
            {
                $pdf = new pdfview(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            }
            else{
                $pdf = new pdfview_NA(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            }
        }
        

        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('AleksOrtiz');
        $pdf->SetTitle('Masmetrologia');
        $pdf->SetSubject('Formato Cotización');
        
        //$pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', '                                                       Cotización / ' . $COT, "                                                             Fecha: " . $FECHA . " \n                                                             Ejecutivo: ". $RESPONSABLE ." \n                                                             Correo: " . $RESPONSABLE_CORREO);
        //$pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', 'Cotización / ' . $COT, "Fecha: " . $FECHA . " \n Ejecutivo: ". $RESPONSABLE ." \n Correo: " . $RESPONSABLE_CORREO);

        $spc = "            ";
        $head = "Metrologia Aplicada y Servicios, S. de R.L. de C.V. $spc Cotización / $COT";
        $txt = "Av. Ramón Rayón #1520 Int-9                                                           Fecha: " . $FECHA;
        $txt .= "\nCol. Rio Bravo                                                                                    Ejecutivo: " . $RESPONSABLE;
        $txt .= "\nCd. Juárez, Chih. C.P. 32550                                                                 Correo: " . $RESPONSABLE_CORREO;
        $txt .= "\nRFC: MAS080825EE7";

        $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', $head, $txt);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        //$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(8, PDF_MARGIN_TOP, 8);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        $pdf->SetFont('times', '', 8);
        $pdf->AddPage();

        $tbl = <<<EOD
            <table border="0">
                <tr>
                    <td>
                        <b>Cliente/Client:</b><br>
                        $CLIENTE<br><br>
                        <b>Dirección / Address:</b><br>
                        $DOMICILIO<br>
                        $COLONIA<br>
                        $UBICACION<br>
                        RFC: $RFC<br>
                    </td>
                    <td>
EOD;

if($ID_PLANTA > 0)
{
        $tbl .= <<<EOD
        <b>Planta/Plant:</b><br>
        $NOMBRE_PLANTA<br>
        $CALLE_PLANTA<br>
        $COLONIA_PLANTA<br>
        $CIUDAD_PLANTA $ESTADO_PLANTA<br><br>
EOD;
}

        $tbl .= <<<EOD
                <b>Contacto / Contact:</b><br>
                $CONTACTO<br>
                $TELEFONO<br>
                $CORREO<br>
            </td>
        </tr>
    </table>
EOD;

        $pdf->writeHTML($tbl, false, false, false, false, '');
        $w = array(8, 125, 12, 24, 24);


        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(255);
        $w = array(8, 125, 12, 24, 24);

        $pdf->SetTextColor(0);

        $pdf->Cell($w[0], 6, "#", 1, 0, 'C');
        $pdf->Cell($w[1], 6, "Descripción", 1, 0, 'C');
        $pdf->Cell($w[2], 6, "Cant.", 1, 0, 'C');
        $pdf->Cell($w[3], 6, "Precio Unit.", 1, 0, 'C');
        $pdf->Cell($w[4], 6, "Importe", 1, 1, 'C');

        $pdf->SetFont('', '', 7);
        $i = 0;

        

        $RenSpace = 2.8;

        foreach($conceptos as $indice => $concepto)
        {
            $pdf->SetFont('', '', 7);


            if(array_key_exists("otro", $concepto->atributos))
            {
                //ESPACIO ANTES DEL PRIMER CONCEPTO
                $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);

                $startX = $pdf->GetX();
                $startY = $pdf->GetY();
                $cellcount = array();

                if($concepto->atributos->otro == 'CABEZAL')
                {
                    $pdf->SetFont('', 'B', 8);
                    $pdf->MultiCell($w[0], '', '', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[1], '', $concepto->descripcion, 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[2], '', '', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[3], '', '', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[4], '', '', 'LR', 'C', 0, 1);
                    
                    $h = (max($cellcount)) * $RenSpace;
                    $pdf->SetXY($startX, $startY);
                    $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);
                    $pdf->SetFont('', '', 7);
                }
                else if($concepto->atributos->otro == 'SEPARADOR')
                {
                    
                    $pdf->MultiCell($w[0], '', '===', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[1], '', '====================================================================================', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[2], '', '=====', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[3], '', '============', 'LR', 'C', 0, 0);
                    $pdf->MultiCell($w[4], '', '============', 'LR', 'C', 0, 1);
                    
                    $h = (max($cellcount)) * $RenSpace;
                    $pdf->SetXY($startX, $startY);
                    $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);
                }

            }
            else
            {
                $i++;

                //ESPACIO ANTES DEL PRIMER CONCEPTO
                $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);

                ////// TIEMPO DE ENTREGA //////
                $dicSitios = array('OS' => 'En Planta', 'LAB' => 'LAB', 'EXT' => 'Prov. Externo');
                $sitio = $concepto->sitio != "N/A" ? (", Donde: " . $dicSitios[$concepto->sitio]) : "";
                $tEntrega = $concepto->tiempo_entrega > 0 ? ("T. Entrega: " . $concepto->tiempo_entrega . " Día" . ($concepto->tiempo_entrega > 1 ? "s" : "") . $sitio) : ("T. Entrega: N/A" . $sitio);

                ////// SERVICIOS //////
                if(count($concepto->servicios) > 0){
                    $codigos = " ";
                    foreach ($concepto->servicios as $value) {
                        $value[1] = $value[1] == "N/A" ? $value[2] : $value[1];
                        $codigos .= $value[1] . ", ";
                    }

                    $startX = $pdf->GetX();
                    $startY = $pdf->GetY();
                    $cellcount = array();
                    $cellcount[] = $pdf->MultiCell($w[0], '', $indice + 1, 0, 'C', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[1], '', "Servicios: " . $codigos . "   " . $tEntrega, 0, 'L', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[2], '', number_format($concepto->cantidad, 0), 0, 'C', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[3], '', ("$" . number_format($concepto->precio_unitario, 2)), 0, 'R', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[4], '', ("$" . number_format($concepto->importe, 2)), 0, 'R', 0, 1);

                    $h = (max($cellcount)) * $RenSpace;
                    $pdf->SetXY($startX, $startY);
                    $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);
                }

                ////// EQUIPO //////
                $att = "";
                if(array_key_exists("ID", $concepto->atributos)){
                    $att .= "ID: " . $concepto->atributos->ID . ", ";
                }
                if(array_key_exists("Marca", $concepto->atributos)){
                    $att .= "Marca: " . $concepto->atributos->Marca . ", ";
                }
                if(array_key_exists("Modelo", $concepto->atributos)){
                    $att .= "Mod: " . $concepto->atributos->Modelo . ", ";
                }
                if(array_key_exists("Serie", $concepto->atributos)){
                    $att .= "S/N: " . $concepto->atributos->Serie . ", ";
                }

                foreach ($concepto->atributos as $key => $value) {
                    if($key != "ID" && $key != "Marca" && $key != "Modelo" && $key != "Serie")
                    {
                        $att .= $key . ": " . $value . ", ";
                    }
                }
                $att = rtrim($att, ', ');

                $startX = $pdf->GetX();
                $startY = $pdf->GetY();
                $cellcount = array();
                $cellcount[] = $pdf->MultiCell($w[0], '', count($concepto->servicios) > 0 ? "" : $indice + 1, 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[1], '', $concepto->descripcion . (count($concepto->servicios) > 0 ? (" " . $att) : ("   " . $tEntrega)), 0, 'L', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[2], '', count($concepto->servicios) > 0 ? "" : $concepto->cantidad, 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[3], '', count($concepto->servicios) > 0 ? "" :  ("$" . number_format($concepto->precio_unitario, 2)), 0, 'R', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[4], '', count($concepto->servicios) > 0 ? "" :  ("$" . number_format($concepto->importe, 2)), 0, 'R', 0, 1);
                
                $h = (max($cellcount)) * $RenSpace;
                $pdf->SetXY($startX, $startY);
                $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);

                ////// COMENTARIOS //////
                if(!empty($concepto->comentarios)){
                    
                    $startX = $pdf->GetX();
                    $startY = $pdf->GetY();
                    $cellcount = array();
                    $cellcount[] = $pdf->MultiCell($w[0], '', "", 0, 'C', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[1], '', "** " . $concepto->comentarios, 0, 'L', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[2], '', "", 0, 'R', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[3], '', "", 0, 'R', 0, 0);
                    $cellcount[] = $pdf->MultiCell($w[4], '', "", 0, 'R', 0, 0);
                    
                    $h = (max($cellcount)) * $RenSpace;
                    $pdf->SetXY($startX, $startY);
                    $pdf->writeHTMLCell($w[0], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[1], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[2], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[3], $h, '', '', '', "LR", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[4], $h, '', '', '', "LR", 1, 0, true, 'J', false);
                }
            
                


                if($pdf->GetY() > (260))
                {
                    $pdf->writeHTMLCell($w[0], '', '', '', '', "LRB", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[1], '', '', '', '', "LRB", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[2], '', '', '', '', "LRB", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[3], '', '', '', '', "LRB", 0, 0, true, 'J', false);
                    $pdf->writeHTMLCell($w[4], '', '', '', '', "LRB", 1, 0, true, 'J', false);

                    $pdf->AddPage();

                    $pdf->SetFont('times', '', 8);
                    $pdf->writeHTML($tbl, false, false, false, false, '');

                    if($i < count($conceptos))
                    {
                        $pdf->SetFont('helvetica', 'B', 9);
                        $pdf->SetTextColor(0);
                        $pdf->Cell($w[0], 6, "#", 1, 0, 'C');
                        $pdf->Cell($w[1], 6, "Descripción", 1, 0, 'C');
                        $pdf->Cell($w[2], 6, "Cant.", 1, 0, 'C');
                        $pdf->Cell($w[3], 6, "Precio Unit.", 1, 0, 'C');
                        $pdf->Cell($w[4], 6, "Importe", 1, 1, 'C');

                        $pdf->writeHTMLCell($w[0], '', '', '', '', "LRT", 0, 0, true, 'J', false);
                        $pdf->writeHTMLCell($w[1], '', '', '', '', "LRT", 0, 0, true, 'J', false);
                        $pdf->writeHTMLCell($w[2], '', '', '', '', "LRT", 0, 0, true, 'J', false);
                        $pdf->writeHTMLCell($w[3], '', '', '', '', "LRT", 0, 0, true, 'J', false);
                        $pdf->writeHTMLCell($w[4], '', '', '', '', "LRT", 1, 0, true, 'J', false);
                    }
                }
            }
        }







        //DESCRIPCION DE CODIGOS DE SERVICIO
        if(count((array)$SERVICIOS) > 0)
        {
            
            $pdf->MultiCell($w[0], '', '===', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], '', '====================================================================================', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[2], '', '=====', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[3], '', '============', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[4], '', '============', 'LR', 'C', 0, 1);

            
            $pdf->SetFont('', 'B', 7);
            $pdf->MultiCell($w[0], '', '', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], '', 'Descripción de Servicios:', 0, 'L', 0, 0);
            $pdf->MultiCell($w[2], '', '', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[3], '', '', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[4], '', '', 'LR', 'R', 0, 1);
            

            $pdf->SetFont('', '', 7);
            foreach($SERVICIOS as $codigo => $servicio) 
            {
                $startX = $pdf->GetX();
                $startY = $pdf->GetY();

                $cellcount = array();
                $cellcount[] = $pdf->MultiCell($w[0], '', '', 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[1], '', $codigo . ": " . $servicio, 0, 'L', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[2], '', '', 0, 'R', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[3], '', '', 0, 'R', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[4], '', '', 0, 'R', 0, 0);
                
                $pdf->SetXY($startX, $startY);
                $h = (max($cellcount) + 1) * $RenSpace;
                $pdf->MultiCell($w[0], $h,'','LR','C',0,0);
                $pdf->MultiCell($w[1], $h,'','LR','L',0,0);
                $pdf->MultiCell($w[2], $h,'','LR','R',0,0);
                $pdf->MultiCell($w[3], $h,'','LR','R',0,0);
                $pdf->MultiCell($w[4], $h,'','LR','R',0,1);
            }
        }
        


        $startY = $pdf->GetY();
        for ($startY; $startY < $Margin; $startY = $startY + 6) {
            $pdf->MultiCell($w[0], 6, '----', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], 6, '-----------------------------------------------------------------------------------------------------------------------------------------------', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[2], 6, '-------', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[3], 6, '-------------------', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[4], 6, '-------------------', 'LR', 'C', 0, 0);
            $pdf->Ln();
        }

        $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, $NOTAS, 'T', 'M', 0 , 0);
        $pdf->SetFont('', '', 8);
        $pdf->MultiCell($w[3], 4, 'Sub-Total', 'T', 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[4], 4, "$" . number_format($SUBTOTAL, 2), 1, 'R', 0, 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        
        $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'L', 0 , 0);
        $pdf->MultiCell($w[3], 4, $IMPUESTO_NOMBRE, 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[4], 4, "$" . number_format($IMPUESTO, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[3], 4, 'Total', 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->MultiCell($w[4], 4, '$' . number_format($TOTAL, 2), 1, 'R', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[3] + $w[4], 6, '* Moneda / Currency: ' . $MONEDA, 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        //$pdf->MultiCell($w[3], 6, $MONEDA, 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();

        $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'M', 0 , 0);
        $pdf->MultiCell($w[3] + $w[4], 6, '', 0, 'C', 0 , 0, '', '', TRUE, 0, FALSE, TRUE, 0, 'M');
        $pdf->Ln();
        
        $pdf->MultiCell(180, 6, '', 0, 'M', 0 , 0);
        $pdf->Ln();

        $pdf->MultiCell(180, 6, '', 0, 'M', 0 , 0);
        $pdf->Ln();
        $pdf->Ln();

         if ($modo === 'S') {
                return $pdf->Output('', 'S'); // Devuelve el PDF como string para adjuntarlo
            } else {
                $cot= str_pad($id, 6, '0', STR_PAD_LEFT).'_Rev_'.$rev;
                $pdf->Output("COT-$cot.pdf", 'I'); // Muestra el PDF en el navegador
            }

    }

 

    function ajax_setCotizacion(){
        $id_cotizacion=null;
        $id_cotizacion_nueva=null;
        $cotizacion = json_decode($this->input->post("cotizacion"));
        //echo var_dump($cotizacion->correo_cc);die();
        $conceptos = json_decode($this->input->post("conceptos"));
        if (isset($cotizacion->copiar_desde)) {
            $id_cotizacion=$cotizacion->copiar_desde;
        }
        


        if($cotizacion->id == 0)
        {
            $cotizacion->usuario = $this->session->id;
            $cotizacion->id = $this->Conexion->insertar('cotizaciones', $cotizacion, array('fecha' => 'CURRENT_TIMESTAMP()'));
            $id_cotizacion_nueva=$cotizacion->id;
            
            $res= $this->Conexion->consultar('SELECT prospecto from empresas where id = '.$cotizacion->empresa, TRUE);
            if ($res->prospecto=='1') {
                $prospectoCot['prospectoEmp'] = '1';
                //echo var_dump($res);die();
            $this->Conexion->modificar('cotizaciones', $prospectoCot, null, array('id' => $cotizacion->id));    
            }
        }
        else
        {
            
            // M O D I F I C A C I O N    D E    D A T O S
            $func = null;
            if($cotizacion->estatus == 'AUTORIZADA')
            {
                $cotizacion->aprobador = $this->session->id;
                $func['fecha_aprobacion'] = 'CURRENT_TIMESTAMP()';
            }
            if($cotizacion->estatus == 'CONFIRMADA')
            {
                $func['fecha_confirmacion'] = 'CURRENT_TIMESTAMP()';
            }
            if ($cotizacion->estatus=='APROBADO TOTAL') {
                $res= $this->Conexion->consultar('SELECT prospecto from empresas where id = '.$cotizacion->empresa, TRUE);
            if ($res->prospecto=='1') {
                $empresa['prospecto'] = '0';

            $this->Conexion->modificar('empresas', $empresa, null, array('id' => $cotizacion->empresa));    
            }
            $seg = $this->Conexion->consultar("SELECT ca.*, u.correo from cot_acciones ca join usuarios u on u.id=ca.usuario where estatus ='PENDIENTE' and idCot = ".$cotizacion->id);
            if ($seg) {
                foreach ($seg as $value) {
                    $seguimiento['estatus'] = 'CANCELADA';
                    $this->Conexion->modificar('cot_acciones', $seguimiento, null, array('id' => $value->id));
                    $datos['id'] = $value->idCot;
                    $datos['fecha_limite'] = $value->fecha_limite;
                    $datos['accion'] = $value->accion;
                    $datos['correos'] = $value->correo;
                    $datos['correo_cancelar'] = $this->session->correo;
                
                $this->correos_cotizaciones->cancelarSeguimiento($datos);

                }
            }
            }
            //echo  var_dump($cotizacion);die();
            $this->Conexion->modificar('cotizaciones', $cotizacion, $func, array('id' => $cotizacion->id));

            if ($cotizacion->estatus=='CANCELADA') {
                $seg = $this->Conexion->consultar("SELECT ca.*, u.correo from cot_acciones ca join usuarios u on u.id=ca.usuario where estatus ='PENDIENTE' and idCot = ".$cotizacion->id);
                    if ($seg) {
                        foreach ($seg as $value) {
                            $seguimiento['estatus'] = 'CANCELADA';
                            $this->Conexion->modificar('cot_acciones', $seguimiento, null, array('id' => $value->id));
                            $datos['id'] = $value->idCot;
                            $datos['fecha_limite'] = $value->fecha_limite;
                            $datos['accion'] = $value->accion;
                            $datos['correos'] = $value->correo;
                            $datos['correo_cancelar'] = $this->session->correo;
                        
                        $this->correos_cotizaciones->cancelarSeguimiento($datos);

                        }
                    }
            }


            
            // E N V I O    D E    C O R R E O S
            if(isset($_POST['comentarios']) && $_POST['comentarios'])
            {
                $datos['comentarios'] = $this->input->post('comentarios');
            }

            if($cotizacion->estatus == 'PENDIENTE AUTORIZACION')
            {
                
                $res = $this->Conexion->consultar("SELECT E.nombre as NombreCliente, EC.nombre as NombreContacto, concat(R.nombre, ' ', R.paterno) as Responsable, A.correo from cotizaciones C inner join usuarios R on R.id = C.responsable inner join usuarios A on R.autorizador_cotizacion = A.id inner join empresas E on E.id = C.empresa inner join empresas_contactos EC on EC.id = C.contactos->'$[0]' where C.id = $cotizacion->id", TRUE);
                $datos['id'] = $cotizacion->id;
                $datos['correos'] = $res->correo;
                $datos['nombreCliente'] = $res->NombreCliente;
                $datos['nombreContacto'] = $res->NombreContacto;
                $datos['nombreResponsable'] = $res->Responsable;

                if (isset($cotizacion->enviar_autorizar) && $cotizacion->enviar_autorizar==1) {
                    
                }

                
                $this->correos_cotizaciones->solicitarAprobacion($datos);
            }
            if($cotizacion->estatus == 'RECHAZADA')
            {

                $res = $this->Conexion->consultar("SELECT E.nombre as NombreCliente, EC.nombre as NombreContacto, concat(R.nombre, ' ', R.paterno) as Responsable, R.correo from cotizaciones C inner join usuarios R on R.id = C.responsable inner join empresas E on E.id = C.empresa inner join empresas_contactos EC on EC.id = C.contactos->'$[0]' where C.id = $cotizacion->id", TRUE);
                $datos['id'] = $cotizacion->id;
                $datos['correos'] = $res->correo;
                $datos['nombreCliente'] = $res->NombreCliente;
                $datos['nombreContacto'] = $res->NombreContacto;
                $datos['nombreResponsable'] = $res->Responsable;

                $this->correos_cotizaciones->rechazoCotizacion($datos);
            }
            if($cotizacion->estatus == 'AUTORIZADA')
            {
                
                $res = $this->Conexion->consultar("SELECT E.nombre as NombreCliente, EC.nombre as NombreContacto, concat(R.nombre, ' ', R.paterno) as Responsable, R.correo, correo_cc from cotizaciones C inner join usuarios R on R.id = C.responsable inner join empresas E on E.id = C.empresa inner join empresas_contactos EC on EC.id = C.contactos->'$[0]' where C.id = $cotizacion->id", TRUE);
                $datos['id'] = $cotizacion->id;
                $datos['correos'] = $res->correo;
                $datos['nombreCliente'] = $res->NombreCliente;
                $datos['nombreContacto'] = $res->NombreContacto;
               
                $datos['nombreResponsable'] = $res->Responsable;


                $this->correos_cotizaciones->aprobacionCotizacion($datos);
            }
        }

        foreach ($conceptos as $key => $elem) {
            
            $elem->cotizacion = $cotizacion->id;

            if($elem->id == 0)
            {
                $this->Conexion->insertar('cotizaciones_conceptos', $elem);
            }
            if($elem->id < 0)
            {
                $this->Conexion->eliminar('cotizaciones_conceptos', array('id' => (intval($elem->id) * -1)));
            }
            else if ($elem->id != 0)
            {
                $cot=$this->Conexion->consultar("SELECT cotizacion from cotizaciones_conceptos where cotizacion='$elem->cotizacion'", TRUE);
                if ($cot) {
                    $id_cotizacion=$cot;
                  $this->Conexion->modificar('cotizaciones_conceptos', $elem, null, array('id' => $elem->id));  
                  //echo 1;die();
                }else{
                   // $id_cotizacion=$cotizacion->id;
                    unset($elem->id);
                    $this->Conexion->insertar('cotizaciones_conceptos', $elem);
                   // echo 2;die();
                }
            }
        }
if (!isset($cotizacion->estatus)) {
        if ($id_cotizacion) {
            $comentarios  = array(
                'cotizacion' => $id_cotizacion_nueva, 
                'usuario' => $this->session->id,
                'comentario' => 'Esta cotizacion es copia de: #'.$id_cotizacion,
            );
            
            $this->Conexion->insertar('cotizaciones_comentarios', $comentarios, array('fecha' => 'CURRENT_TIMESTAMP()'));
            $comentarios2  = array(
                'cotizacion' => $id_cotizacion, 
                'usuario' => $this->session->id,
                'comentario' => 'Esta cotizacion fue copiada para la cotizacion: #'.$id_cotizacion_nueva,
            );
            
            $this->Conexion->insertar('cotizaciones_comentarios', $comentarios2, array('fecha' => 'CURRENT_TIMESTAMP()'));
        }
}
        

        if(isset($_POST['comentarios']) && $_POST['comentarios'])
        {
            $comentario = new stdClass;
            $comentario->cotizacion = $cotizacion->id;
            $comentario->usuario = $this->session->id;
            $comentario->comentario = $this->input->post('comentarios');
            
            $this->Conexion->insertar('cotizaciones_comentarios', $comentario, array('fecha' => 'CURRENT_TIMESTAMP()'));
        }
        
        echo $cotizacion->id;
    }

    function ajax_getCotizaciones(){
        $this->output->delete_cache('cotizaciones/catalogo');

        $id = $this->input->post('id');
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        $cliente = $this->input->post('cliente');
        $estatus = $this->input->post('estatus');
        $tipo = $this->input->post('tipo');
        $cerradas = $this->input->post('cerradas');
	    $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';

        $Limitante_Fecha = " and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR))";

        $group = TRUE;

$query="SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, ' ', R.paterno) as Responsable, E.prospecto from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->'$[0]' left join usuarios R on R.id = C.responsable left join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1";
        if($id)
        {
            $query .= " and C.id = '$id'";
        }
        else
        {
            if(!empty($cliente) && $cliente != 0)
            {
                $query .= " and C.empresa = '$cliente'";
            }
            if(!empty($tipo) && $tipo != "TODOS")
            {
                $query .= " and C.tipo = '$tipo'";
            }
            if(!empty($estatus) && $estatus != 'TODOS')
            {
                $query .= " and C.estatus = '$estatus'";
            }
            else
            {
                if($cerradas == "0")
                {
                    $query .= " and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA')";
                }
            }

            if(!empty($texto))
            {
                if($parametro == "folio")
                {
                    $query .= " and C.id = '$texto'";
                }
                if($parametro == "id")
                {
                    $query .= " and UPPER(CC.atributos->'$.ID') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "marca")
                {
                    $query .= " and UPPER(CC.atributos->'$.Marca') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "serie")
                {
                    $query .= " and UPPER(CC.atributos->'$.Serie') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "modelo")
                {
                    $query .= " and UPPER(CC.atributos->'$.Modelo') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "contenido")
                {
                    $query .= " and CC.descripcion like '%$texto%'";
                }
                if($parametro == "responsable")
                {
		    $query .= $Limitante_Fecha;

                    $group = FALSE;
                    $query .= " group by C.id";
                    $query .= " having Responsable like '%$texto%' ";

                }
            }
        }

    if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" and C.fecha BETWEEN '".$f1."' AND '".$f2."' ";
        }

        if($group)
        {
            $query .= $Limitante_Fecha;

        $query .= " group by C.id ";
        }
        $query .= " order by C.id desc";

        $res = $this->Conexion->consultar($query, $id);

        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getClientesCotizaciones(){
        $texto = $this->input->post('texto');
        
        $query = "SELECT E.id, E.nombre, count(C.id) as NumCot from cotizaciones C inner join empresas E on E.id = C.empresa";

        if($texto)
        {
            $query .= " where E.nombre like '%$texto%'";
        }
        $query .= " group by E.id;";

        $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getCotizacionConceptos(){
        $coti = $this->input->post('cotizacion');
        $rev = $this->input->post('revision');
        
        $query = "SELECT CC.* from cotizaciones_conceptos CC where cotizacion = $coti";

        if(isset($_POST["revision"]))
        {
            $query .= " and revision = $rev";
        }

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_setRevision(){
        $cotizacion = json_decode($this->input->post("cotizacion"));
        $conceptos = json_decode($this->input->post("conceptos"));
        $id = $cotizacion->id;
        unset($cotizacion->id);

        $rv = $this->Conexion->consultar("SELECT (max(CC.revision) + 1) as Rev from cotizaciones_conceptos CC where CC.cotizacion = $id", TRUE);
        
        foreach ($conceptos as $key => $elem) {
            $elem->cotizacion = $id;
            $elem->revision = $rv->Rev;
            $this->Conexion->insertar('cotizaciones_conceptos', $elem);
        }
        $this->Conexion->modificar('cotizaciones', $cotizacion, null, array('id' => $id));

        echo "1";
    }

    function ajax_setComentarios(){
        $comentario = json_decode($this->input->post('comentario'));
        $comentario->usuario = $this->session->id;
        $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');
        

        $res = $this->Conexion->insertar('cotizaciones_comentarios', $comentario, $funciones);
        if($res > 0)
        {
            echo "1";
        }
    }

    function ajax_getComentarios(){
        $id = $this->input->post('id');

        $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from cotizaciones_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

        if($id)
        {
            $query .= " and C.cotizacion = '$id'";
        }

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getSAData(){
        header("Access-Control-Allow-Origin: *");
        $id_equipo = $this->input->post('id_equipo');
        $res = shell_exec("C:/xampp/htdocs/sa_reader/sa_reader.exe \"$id_equipo\"");
        echo $res;
    }


    function ajax_getClientes(){
        $id = $this->input->post('id');
        $nombre = $this->input->post('nombre');

        $query = "SELECT E.* from empresas E where E.cliente = 1 and JSON_LENGTH(E.moneda_cotizacion) > 0";

        if($id)
        {
            $query .= " and E.id = '$id'";
        }
        else
        {
            if($nombre)
            {
                $query .= " and E.nombre like '%$nombre%'";
            }
        }


        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getPlanta(){
        $id = $this->input->post('id');

        $query = "SELECT * from empresa_plantas where id = $id";
        
        $res = $this->Conexion->consultar($query, TRUE);
        if($res){
            echo json_encode($res);
        }
    }

    function ajax_getbuscarAutores(){
        $id = $this->input->post('id');

        $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Nombre, P.puesto as Puesto, U.correo from usuarios U inner join puestos P on U.puesto = P.id inner join privilegios PR on PR.usuario = U.id where U.activo = 1 and PR.administrar_cotizaciones = 1";

        if($id)
        {
            $query .= " and U.id = '$id'";
        }

        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
    }
    
    function ajax_getContactos(){
        $id = $this->input->post('id');
        $empresa = $this->input->post('id_cliente');

        $query = "SELECT * from empresas_contactos where activo = 1 and cotizable = 1";

        if($id)
        {
            $query .= " and id = '$id'";
        }
        else
        {
            if($empresa)
            {
                $query .= " and empresa = '$empresa'";
            }
        }

        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getNombresUsuarios(){
        $ids = $this->input->post('ids');
        $ids = str_replace('[', '(', $ids);
        $ids = str_replace(']', ')', $ids);

        $query = "SELECT id, concat(nombre, ' ', paterno) as User from usuarios where id in $ids";

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getServicio(){
        $codigo = $this->input->post('codigo');

        $query = "SELECT S.id, S.codigo, S.sitio, S.descripcion as DescripcionServicio, CP.alto_a as Precio from servicios S inner join claves_precio CP on S.clave_precio = CP.id where S.codigo = '$codigo'";

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getServicioMarMod(){ //SERVICIOS RELACIONADOS A REQUERIMIENTO POR MARCA/MODELO
        $fabricante = $this->input->post('fabricante');
        $modelo = $this->input->post('modelo');

        //$query = "SELECT S.*, S.descripcion as DescripcionServicio, CP.alto_a as Precio, R.descripcion, R.id as IdReq from requerimientos R inner join servicios S on S.id = R.servicio inner join claves_precio CP on S.clave_precio = CP.id where R.catalogado = '1' and upper(trim(R.fabricante)) = '$fabricante' and upper(trim(R.modelo)) = '$modelo' group by S.id limit 1";
        $query = "SELECT R.id, R.descripcion, R.servicio from requerimientos R where R.catalogado = '1' and upper(trim(R.fabricante)) = '$fabricante' and upper(trim(R.modelo)) = '$modelo' limit 1";

        $res = $this->Conexion->consultar($query, TRUE);

        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_enviarCorreo(){
        $this->load->library('correos_archivos');
        $cotizacion = json_decode($this->input->post("cotizacion"));      
        

        $id_cot = $cotizacion->id;
        $param = $cotizacion->id."-".$cotizacion->UltRev;
        $pdf_content = $this->cotizacion_pdf($param, 'S');
        
        $res = $this->Conexion->consultar("SELECT ec.correo as contacto, r.correo as responsable, u.correo as creador, c.correo_cc FROM cotizaciones c JOIN empresas_contactos ec ON JSON_CONTAINS(c.contactos, CAST(ec.id AS JSON), '$') JOIN usuarios r on c.responsable=r.id JOIN usuarios u on c.usuario=u.id WHERE c.id = $id_cot", TRUE);
        
        $body=$this->Conexion->consultar("SELECT *  FROM texto_correo WHERE activo = 1", true);
        
        $firma=$this->Conexion->consultar("SELECT foto_firma  FROM usuarios WHERE id= ".$this->session->id, true);
        
        $nombre_archivo = 'firma_' . $this->session->id . '.png'; // Ajusta la extensión según el formato real
        
        $ruta_temporal = sys_get_temp_dir() . '/' . $nombre_archivo;
        
        // Guardar el archivo en la ubicación temporal
        file_put_contents($ruta_temporal, $firma->foto_firma);
        

        $datos['asunto']="Cotización Masmetrologia – #".$id_cot;
        $datos['pdf']=$pdf_content;
        $datos['pdf_nombre']="Cotización - #".$param.".pdf";
        $datos['body']=$body->texto;
        $datos['firma']=$ruta_temporal;
        $datos['firma_nombre']=$nombre_archivo;
        $datos['contacto']=$res->contacto;
        $datos['responsable']=$res->responsable;
        $datos['creador']=$res->creador;
        $datos['correo_cc']=$res->correo_cc;
      
        //$this->correos_archivos->Solicitar_Autorización($datos);
        if ($this->correos_archivos->Solicitar_Autorización($datos)) {
            if($datos['estatus'] == "AUTORIZADA")
            {
                $this->Conexion->modificar('cotizaciones', array('estatus' => 'ENVIADA', 'bitacora_estatus' => $cotizacion->bitacora_estatus), null, array('id' => $datos['id']));
            }
            echo "1";
        }

    
        /*if(substr($this->correos_archivos->Solicitar_Autorización($datos), 0, 5) == "<pre>")
        {
            if($datos['estatus'] == "AUTORIZADA")
            {
                $this->Conexion->modificar('cotizaciones', array('estatus' => 'ENVIADA', 'bitacora_estatus' => $cotizacion->bitacora_estatus), null, array('id' => $datos['id']));
            }
            echo "1";
        }*/

    }
function calendario(){
        $this->load->model('usuarios_model');
        $datos['sub']=$this->usuarios_model->userCots();


        $this->load->view('header');
        $this->load->view('cotizaciones/calendario', $datos);
    }
 function ajax_getAcciones_calendar(){
        //$usuario = $this->input->post('usuario');
        $idsub=$this->input->get('idSub'); 

        $query = "SELECT A.*, concat('Responsable: ',U.nombre, ' ', U.paterno,'\r\n','Cotizacion: ', A.idCot ) as title, A.fecha_limite as start, A.fecha_limite + interval 1 hour as end, concat(U.nombre, ' ', U.paterno) as User,";
        $query .= " if(estatus = 'CANCELADA', 'gray', if(estatus = 'PENDIENTE' and current_timestamp() > A.fecha_limite, 'red', if(estatus = 'PENDIENTE' and current_timestamp() <= A.fecha_limite, '#f0ad4e', if(estatus = 'REALIZADA' and A.fecha_realizada > A.fecha_limite, '#76A874', if(estatus = 'REALIZADA' and A.fecha_realizada <= A.fecha_limite, 'green', ''))))) as color";
        $query .= " from cot_acciones A inner join usuarios U on U.id = A.usuario";
        //echo var_dump($query);die();
        // where U.id=".$this->session->id;
        if (!empty($idsub)) {
            $query .=" where U.id=".$idsub;
            // code...
        }


        $res = $this->Conexion->consultar($query);
        
        if($res)
        {
            echo json_encode($res);
        }
    }
 function ajax_setAccion(){
            $this->load->library('correos_archivos');

           

        $accion = json_decode($this->input->post('data'));
        $rev=$this->input->post('rev');
        $responsable=$this->input->post('responsable');
        $enviar_contacto=$this->input->post('enviar_contacto');
        $idUser=null;
        $contacto=null;
        $param = $accion->idCot."-".$rev;
        $pdf_content = $this->cotizacion_pdf($param, 'S');
        if (empty($responsable)) {
            $accion->usuario = $this->session->id; 
            $idUser=$this->session->id;
        }else{
            $accion->usuario = $responsable; 
            $idUser=$responsable;
        }
        $accion->estatus = "PENDIENTE";
        //echo var_dump($accion);die();
        $func['fecha_creacion'] = 'CURRENT_TIMESTAMP()';

        $id = $this->Conexion->insertar('cot_acciones', $accion, $func);
        $query = "SELECT concat(nombre , '',paterno) as name, correo from usuarios where id=".$idUser ;
        $res = $this->Conexion->consultar($query, TRUE);
        if ($enviar_contacto==1) {
            $contacto = $this->Conexion->consultar("SELECT ec.correo FROM cotizaciones c JOIN empresas_contactos ec ON JSON_CONTAINS(c.contactos, CAST(ec.id AS JSON), '$') WHERE c.id= $accion->idCot", TRUE);
        }

$fecha = strtotime($accion->fecha_limite);
$name = "Cotización : ".$accion->idCot;
$start = date('Ymd', $fecha) . 'T' . date('His', $fecha);
$end = date('Ymd', $fecha) . 'T' . date('His', $fecha);
$description = $accion->accion;

        $ical_content = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//LearnPHP.co//NONSGML {$res->name}//EN
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

        $datos['nombre']="Seguimiento-".$accion->idCot.".ics";
        $datos['cuerpo']="Esto es un evento de seguimiento para la cotización - ".$accion->idCot;
        $datos['cal']=$ical_content;
        $datos['correo']=$res->correo;
        $datos['accion']=$description;
        $datos['contacto']=$contacto->correo;
        $datos['pdf']=$pdf_content;
        $datos['pdf_nombre']="Cotización-".$param.".pdf";
        $datos['correoResponsable']=$this->session->correo;
        $this->correos_archivos->evento_cotizaciones($datos);
        if($id > 0)
        {
            echo $id;
        }
    }
     function ajax_getAcciones(){
        $po = $this->input->post('idCot');

        $res = $this->Conexion->consultar("SELECT A.*, concat(U.nombre, ' ', U.paterno) as User from cot_acciones A inner join usuarios U on U.id = A.usuario where  A.idCot !=0 and A.idCot = $po");
        if($res){
            echo json_encode($res);
        }
    }
    function ajax_setAccionRealizada(){
        $accion = json_decode($this->input->post('data'));
        $func['fecha_realizada'] = 'CURRENT_TIMESTAMP()';

        $id = $this->Conexion->modificar('cot_acciones', $accion, $func, array('id' => $accion->id));
    }
     function ajax_updateAccion(){
        $accion = json_decode($this->input->post('data'));

        $id = $this->Conexion->modificar('cot_acciones', $accion, null, array('id' => $accion->id));
    }
    function ajax_setAccionComentario(){
        $comment = json_decode($this->input->post('data'));
        $comment->usuario = $this->session->id;

        $func['fecha'] = 'CURRENT_TIMESTAMP()';

        $id = $this->Conexion->insertar('cot_acciones_comentarios', $comment, $func);
        if($id > 0)
        {
            echo $id;
        }
    }
     function ajax_getAccionComentarios(){
        $accion = $this->input->post('accion');
        $query="SELECT C.*, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as User from cot_acciones_comentarios C inner join usuarios U on U.id = C.usuario where C.accion =".$accion;
        //echo var_dump($query); die();
        $res = $this->Conexion->consultar($query);
        
        if($res)
        {
            echo json_encode($res);    
        }
    }
function exportar()
    {
        $parametro=$this->input->post('rbBusqueda');
        $texto=$this->input->post('txtBusqueda');
        $cliente=$this->input->post('txtClienteId');
        $tipoCot=$this->input->post('opTipoCotizacion');
        $estatus=$this->input->post('opEstatus');
        $cerradas=$this->input->post('cbCerradasCanceladas');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59';
        $query2 ="";
	
	if(empty($cerradas))
         {
           $close =" and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA')";
         }else{
           $close = "";
            }
        
        $sep='",';
        $coma= "'";
        $url=base_url('cotizaciones/ver_cotizacion/');

        $con=$coma.'=HYPERLINK("'.$url.''.$coma;

        $query = "SELECT C.id as Cotizacion, C.fecha as Fecha, C.tipo as Servicio,C.estatus as Estatus, EC.nombre as Contacto, EC.telefono as Telefono, EC.celular as Celular, EC.correo as Correo, E.nombre as Cliente,concat(R.nombre, ' ', R.paterno) as Responsable , (SELECT fecha_creacion from cot_acciones WHERE idCot= C.id ORDER BY id DESC  limit 1) as Fecha_Seguimiento, (SELECT accion from cot_acciones WHERE idCot= C.id ORDER BY id DESC limit 1) as Accion_Seguimiento, C.prospectoEmp from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->'$[0]' left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 ".$close."and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ";

         if(!empty($cliente) && $cliente != 0)
            {
                $query .= " and C.empresa = '$cliente'";
            }
            if(!empty($tipoCot) && $tipoCot != "TODOS")
            {
                $query .= " and C.tipo = '$tipoCot'";
            }
            if(!empty($estatus) && $estatus != 'TODOS')
            {
                $query .= " and C.estatus = '$estatus'";
            }
            /*else
            {
                if($cerradas == "0")
                {
                    $query .= " and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA')";
                }
            }*/

            if(!empty($texto))
            {
                if($parametro == "folio")
                {
                    $query .= " and C.id = '$texto'";
                }
                if($parametro == "id")
                {
                    $query .= " and UPPER(CC.atributos->'$.ID') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "marca")
                {
                    $query .= " and UPPER(CC.atributos->'$.Marca') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "serie")
                {
                    $query .= " and UPPER(CC.atributos->'$.Serie') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "modelo")
                {
                    $query .= " and UPPER(CC.atributos->'$.Modelo') like '%" . strtoupper($texto) . "%'";
                }
                if($parametro == "contenido")
                {
                    $query .= " and CC.descripcion like '%$texto%'";
                }
                if($parametro == "responsable")
                {
                   
                  // $query .= $Limitante_Fecha;

                    
                    $query2 = " having Responsable like '%$texto%' ";
                }
            }
	    if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" and C.fecha BETWEEN '".$f1."' AND '".$f2."'";
            }
            $query .=' group by C.id '.$query2.' order by C.fecha desc';
           //echo $query;die();
            
            $result= $this->Conexion->consultar($query);


            $salida='';

            $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                            <thead> 
                                <tr>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Cotizacion</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Servicio</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Estatus</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Contacto</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Telefono</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Celular</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Correo</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Cliente</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Prospecto</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Responsable</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha Seguimiento</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Accion Seguimiento</th>
                                </tr>
                            </thead>
                            <tbody>';
        foreach($result as $row){
            $prospecto='No';
            if ($row->prospectoEmp == 1) {
                $prospecto='Si';
            }
            $salida .='
                        <tr>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Cotizacion.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Fecha.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Servicio.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Estatus.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Contacto.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Telefono.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Celular.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Correo.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Cliente.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$prospecto.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Responsable.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->Fecha_Seguimiento.'</td>
                            <td style="color: $444; border: 1px solid black; border-collapse: collapse">'.$row->Accion_Seguimiento.'</td>
                        </tr>';
             }

                $salida .= '</tbody>
                </table>';

        $timestamp = date('m/d/Y', time());
       
        $filename='Cotizaciones'.$timestamp.'.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary'); 
        echo $salida;
        
    } 
function ajax_getDashboard(){
        $sub=$this->input->post('idSub');
        //echo var_dump($sub);die();

        $query="SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, ' ', R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA') and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ";
	
	if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }

        $query .=" group by C.id order by C.fecha desc";

        //echo $query;die();

        $res = $this->Conexion->consultar($query);
        
        $res = count($res);
        if($res)
        {
            echo json_encode($res);
            //$res
             
        }
    }

    function ajax_getDashboardCreadas(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "CREADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }        
if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';



        
         $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function ajax_getDashboardRechazadas(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "RECHAZADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
echo $query;die();

         $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function ajax_getDashboardPendientes(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "PENDIENTE AUTORIZACION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
         $res = $this->Conexion->consultar($query);



        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function ajax_getDashboardRevision(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "EN REVISION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
         $res = $this->Conexion->consultar($query);



        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function ajax_getDashboardAutorizadas(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "AUTORIZADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
         $res = $this->Conexion->consultar($query);



        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function ajax_getDashboardConfirmadas(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "CONFIRMADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';
if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
        
        
        $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
             
        }
    }
    
    function ajax_getDashboardAprobacion(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "EN APROBACION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';
         $res = $this->Conexion->consultar($query);



        if($res)
        {
            echo json_encode($res);
             
        }
    }
function ajax_getDashboardApParcial(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "APROBADO PARCIAL" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';



        
         $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
             
        }
    }
    function ajax_getDashboardApTotal(){
        $sub=$this->input->post('idSub');
        $query='SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "APROBADO TOTAL" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

if ($this->session->privilegios['cotDashboard']) {
            $query .=" ";
        }elseif ($this->session->privilegios['generar_cotizaciones']) {
            $query .=" and C.usuario = ".$this->session->id;
        }

        if ($sub!='TODOS') {
            $query .=" and R.nombre like '%".$sub."%'";
        }
        $query .=' group by C.id order by C.fecha desc';



        
         $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
             
        }
    }

    function excel()
    {
        
        $query = "SELECT CC.* from cotizaciones_conceptos CC where cotizacion = 16747";
$result= $this->Conexion->consultar($query);

        $salida='';

            $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                            <thead> 
                                <tr>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Cantidad</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Concepto</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Servicio</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Comentarios</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Servicio a Realizarse</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">T. Entrega </th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Precio Unitario</th>
                                </tr>
                            </thead>
                            <tbody>';
                            $d=2;
        foreach($result as $row){
            $salida .='
                        <tr>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->cantidad.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->descripcion.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->servicios.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->comentarios.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->sitio.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->tiempo_entrega.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->precio_unitario.'</td>
                            
                        </tr>';
                        $d=$d+1;
             }
                $salida .= '</tbody>
                </table>';

        $timestamp = date('m/d/Y', time());
       
        $filename='QR_'.$timestamp.'.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary'); 
        echo $salida;



        
    }
    function buscarQrs()
    {
        $qr=$this->input->post('txtBuscarQr');        
        $query = "SELECT id, estatus FROM requisiciones_cotizacion where id = ".$qr;
        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
             
        }
    }
    function ajax_setQr()
    {
        $qr=$this->input->post('qr');
        $id_cotizacion=$this->input->post('id_cotizacion');        
        $data = array('id_cotizacion' => $id_cotizacion, );
        $res=$this->Conexion->modificar('requisiciones_cotizacion', $data, null, array('id' => $qr));  

        if($res)
        {
            echo 1;
             
        } 

    }

    function ajax_getQrs()
    {
        $id_cotizacion=$this->input->post('id_cotizacion');        
        $query = "SELECT id FROM requisiciones_cotizacion where id_cotizacion = ".$id_cotizacion;
        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
             
        }
    }
    function ajax_getReporteCotizaciones()
    {
         $requisitor = $this->input->post('asignado');
        $us=null;
        $usF=null;
        if ($requisitor != 'TODO') {
            $us =' and C1.usuario ='.$requisitor;
            $usF =' and C.usuario ='.$requisitor;
            
        }
         $query="SELECT 
    COUNT(DISTINCT C.id) AS Total,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'CREADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalCreadas,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'CREADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaCreadas,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'RECHAZADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalRechazadas,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'RECHAZADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaRechazadas,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'PENDIENTE AUTORIZACION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalPendienteAutorizacion,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'PENDIENTE AUTORIZACION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaPendienteAutorizacion,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'EN REVISION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalEnRevision,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'EN REVISION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaEnRevision,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'AUTORIZADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalAutorizadas,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'AUTORIZADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaAutorizadas,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'CONFIRMADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalConfirmada,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'CONFIRMADA'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaConfirmada,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'EN APROBACION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalEnAprobacion,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'EN APROBACION'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaEnAprobacion,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'APROBADO TOTAL'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalAprobadoTotal,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'APROBADO TOTAL'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaAprobadoTotal,
    (SELECT COUNT(DISTINCT C1.id) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'APROBADO PARCIAL'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS TotalAprobadoParcial,
    (SELECT MIN(C1.fecha) 
     FROM cotizaciones C1
     WHERE C1.estatus = 'APROBADO PARCIAL'".$us."
       AND C1.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
       AND C1.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) AS UltFechaAprobadoParcial
FROM cotizaciones C
WHERE C.estatus NOT IN ('CERRADO TOTAL', 'CERRADO PARCIAL', 'CANCELADA')
  AND C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)".$usF;
  $res = $this->Conexion->consultar($query, TRUE);
  //echo $query;die
        echo json_encode($res);
    
     
    }
    






}
