<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cotizaciones extends CI_Controller {
public $idsub=null;

// Constructor de la clase
function __construct() {
    parent::__construct();
    $this->load->library('correos_cotizaciones');
    $this->load->model('privilegios_model');
}

// Vista principal del catálogo de cotizaciones
function index($estatus = 'TODO', $user = "") {
    $us = null;
    $data['estatus'] = strtoupper($estatus); // Convierte el estatus a mayúsculas
    $data['user'] = strtoupper($user);       // Convierte el nombre de usuario a mayúsculas
    $this->load->model('inicio_model');
    $this->load->view('header');
    $this->load->view('cotizaciones/catalogo', $data);
}

// Carga el formulario para crear una nueva cotización
function crear_cotizacion() {
    $usd = $this->aos_funciones->getUSD(); // Obtiene tipos de cambio USD
    $data["id"] = 0;
    $data['USD'] = $usd[0];
    $data['USD_ACT'] = $usd[1];
    $data['COPY'] = 0;

    if (isset($_POST["id"])) {
        $id = $this->input->post('id');
        $rev = $this->input->post('rev');
        $data['COPY'] = $id . '-' . $rev; // Marca como copia de una cotización existente
    }

    $this->load->view('header');
    $this->load->view('cotizaciones/generar', $data);
}

// Carga una cotización específica en modo visualización/edición
function ver_cotizacion($id = 0) {
    $this->load->model('usuarios_model');
    $data['sub'] = $this->usuarios_model->userCots(); // Obtiene cotizaciones del usuario
    $data['COPY'] = 0;
    $usd = $this->aos_funciones->getUSD();
    $data['USD'] = $usd[0];
    $data['USD_ACT'] = $usd[1];

    if (isset($_POST["id"])) {
        $id = $this->input->post('id'); // Prioriza el ID enviado por POST
    } else if ($id == 0) {
        redirect(base_url('inicio')); // Redirige si no hay ID válido
    }

    $data["id"] = $id;

    $this->load->view('header');
    $this->load->view('cotizaciones/generar', $data);
}

// Muestra el dashboard de cotizaciones
function dashboard() {
    $this->load->model('usuarios_model');
    $datos['sub'] = $this->usuarios_model->userCots(); // Carga cotizaciones del usuario
    $this->load->view('header');
    $this->load->view('cotizaciones/dashboard', $datos);
}

    function cotizacion_pdf($param, $modo = 'I') {
    // Separa el parámetro compuesto (id-rev)
    $param = explode('-', $param);
    $id = $param[0];
    $rev = $param[1];

    // Construcción del query SQL para obtener los datos de la cotización y conceptos
    $query = "SELECT 
                (SELECT ifnull(max(CC2.revision), 0) 
                 FROM cotizaciones_conceptos CC2 
                 WHERE CC2.cotizacion = $id) as UltRev, 
                C.fecha, C.moneda, C.tipo, C.impuesto_factor, C.impuesto_nombre, 
                C.aprobador, C.estatus, E.razon_social, E.calle, E.numero, 
                E.numero_interior, E.colonia, E.ciudad, E.estado, E.pais, E.rfc, 
                EC.nombre, EC.telefono, EC.correo, 
                (if(E.credito_cliente = 1, concat(E.credito_cliente_plazo, ' Días'), 'Contado')) as Credito, 
                concat(R.nombre, ' ', R.paterno) as Resp, R.correo as RespCorreo, 
                CC.*, C.planta, 
                ifnull(EP.nombre, 'N/A') as PlantaNombre, 
                ifnull(EP.calle, 'N/A') as PlantaCalle, 
                ifnull(EP.colonia, 'N/A') as PlantaColonia, 
                ifnull(EP.ciudad, 'N/A') as PlantaCiudad, 
                ifnull(EP.estado, 'N/A') as PlantaEstado
              FROM cotizaciones_conceptos CC
              INNER JOIN cotizaciones C ON C.id = CC.cotizacion
              LEFT JOIN empresa_plantas EP ON EP.id = C.planta
              INNER JOIN empresas E ON E.id = C.empresa
              INNER JOIN empresas_contactos EC ON EC.id = C.contactos->'$[0]'
              INNER JOIN usuarios R ON R.id = C.responsable
              WHERE CC.cotizacion = $id AND CC.revision = $rev";

    // Ejecuta la consulta
    $res = $this->Conexion->consultar($query);

    // Inicialización de variables utilizadas en el PDF
    $conceptos = [];
    $APROB = -1;
    $ESTATUS = "";
    $SERVICIOS = new stdClass;
    $SUBTOTAL = 0;
    $IMPUESTO = 0;
    $TOTAL = 0;

    foreach ($res as $i => $elem) {
        // Determina si la revisión es obsoleta
        $OBS = $elem->UltRev != $rev ? " OBSOLETA" : "";

        // Captura información general de la cotización
        $APROB = $elem->aprobador;
        $ESTATUS = $elem->estatus;
        $COT = 'COT-' . str_pad($elem->cotizacion, 6, "0", STR_PAD_LEFT) . " Rev: " . $rev . $OBS;
        $FECHA = date_format(date_create($elem->fecha), 'd/m/Y h:i A');
        $CLIENTE = $elem->razon_social;
        $DOMICILIO = $elem->calle . ' ' . $elem->numero . ' ' . $elem->numero_interior;
        $COLONIA = $elem->colonia;
        $RFC = $elem->rfc;

        // Datos de contacto
        $CONTACTO = $elem->nombre;
        $TELEFONO = $elem->telefono;
        $CORREO = $elem->correo;
        $UBICACION = $elem->ciudad . ', ' . $elem->estado . ', ' . $elem->pais;

        // Responsable de la cotización
        $RESPONSABLE = $elem->Resp;
        $RESPONSABLE_CORREO = $elem->RespCorreo;

        // Planta asociada
        $ID_PLANTA = $elem->planta;
        $NOMBRE_PLANTA = $elem->PlantaNombre;
        $CALLE_PLANTA = $elem->PlantaCalle;
        $COLONIA_PLANTA = $elem->PlantaColonia;
        $CIUDAD_PLANTA = $elem->PlantaCiudad;
        $ESTADO_PLANTA = $elem->PlantaEstado;

        // Construcción del objeto concepto
        $concepto = new stdClass;
        $concepto->cantidad = $elem->cantidad;
        $concepto->atributos = json_decode($elem->atributos);
        $concepto->descripcion = $elem->descripcion;
        $concepto->servicios = json_decode($elem->servicios);

        // Acumula servicios únicos
        foreach ($concepto->servicios as $key => $value) {
            $cod = $value[1];
            if ($cod != "N/A") {
                $SERVICIOS->$cod = $value[2];
            }
        }

        // Campos adicionales del concepto
        $concepto->comentarios = $elem->comentarios;
        $concepto->tiempo_entrega = $elem->tiempo_entrega;
        $concepto->sitio = $elem->sitio;
        $concepto->precio_unitario = $elem->precio_unitario;
        $concepto->importe = floatval($elem->precio_unitario) * floatval($elem->cantidad);

        // Agrega el concepto al arreglo
        array_push($conceptos, $concepto);

        // Cálculos de totales
        $IMPUESTO_NOMBRE = "IVA / TAX [" . ($elem->impuesto_factor) * 100 . "%]";
        $MONEDA = $elem->moneda;
        $CREDITO = $elem->Credito;

        $SUBTOTAL += $concepto->importe;
        $IMPUESTO += $concepto->importe * ($elem->impuesto_factor);
        $TOTAL += $concepto->importe * ($elem->impuesto_factor + 1);

                // Inicializa notas y margen por tipo de servicio
        $NOTAS = "TERMINOS Y CONDICIONES:";

        switch ($elem->tipo) {
            case 'CALIBRACION':
                $Margin = 195;
                $NOTAS .= ''
                . "\n1.- El servicio se programa con la orden de compra correspondiente. Si su orden de compra incluye más de un equipo se requiere que los mismos se calibren dentro de un periodo de 30 días máximo."
                . "\n2.- La Cotización es válida por 120 días a partir de la fecha de elaboración."
                . "\n3.- El servicio de calibración descrito se ofrece usando los métodos y procedimientos internos del laboratorio. Si existen requisitos específicos del cliente, se iniciará nuevamente el proceso de cotización."
                . "\n4.- El costo del servicio es aplicable aun cuando su equipo no pase la calibración o no responda al proceso de ajuste. En ese caso, se entregará un reporte detallado."
                . "\n5.- No hay garantía de que el equipo mantendrá las tolerancias especificadas durante el intervalo de calibración."
                . "\n6.- Identificación y frecuencia de calibración son asignadas por el cliente."
                . "\n7.- El tiempo de entrega depende de la programación y empieza tras la aprobación y recepción física del instrumento."
                . "\n8.- Si el cliente no proporciona regla de decisión, nuestros certificados no consideran la incertidumbre salvo que se indique lo contrario."
                . "\nESTE SERVICIO CONSTA DE:"
                . "\n1.- Revisión y limpieza externa del equipo."
                . "\n2.- Calibración del equipo.";
                break;

            case 'ESTUDIO DIMENSIONAL':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- Se requiere orden de compra para iniciar el servicio. Una vez recibida, se confirma la fecha de entrega."
                . "\n2.- Generar la orden de compra con base en esta cotización y sus políticas internas."
                . "\n3.- Cotización válida por 120 días desde la fecha de elaboración."
                . "\n4.- Se requieren planos legibles."
                . "\n5.- Puede ser necesario seccionar piezas; proveer cantidad suficiente.";
                break;

            case 'RENTA':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- Se requiere orden de compra para iniciar el servicio."
                . "\n2.- Generar la orden de compra conforme a esta cotización y políticas internas."
                . "\n3.- Cotización válida por 30 días desde la fecha de elaboración."
                . "\nESTE SERVICIO CONSTA DE:"
                . "\n1.- Entrega del equipo en planta."
                . "\n2.- Instrucción sobre el uso correcto del equipo."
                . "\n3.- Instalación en sitio (solo básculas y contadores).";
                break;

            case 'REPARACION':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- Se requiere orden de compra para iniciar el servicio."
                . "\n2.- Generar la orden de compra conforme a esta cotización y políticas internas."
                . "\n3.- Cotización válida por 15 días desde la fecha de elaboración.";
                break;

            case 'VENTA':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- Generar la orden de compra conforme a esta cotización y políticas internas."
                . "\n2.- Cotización válida por 30 días desde la fecha de elaboración."
                . "\n3.- Pedido no cancelable."
                . "\n4.- Disponibilidad sujeta a existencias si no hay orden de compra."
                . "\n5.- Se requiere orden de compra por escrito para entrega."
                . "\n6.- Al recibir la orden, se confirma la fecha de entrega.";
                break;

            case 'SOPORTE':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- Se requiere orden de compra para iniciar el servicio."
                . "\n2.- Generar la orden de compra conforme a esta cotización y políticas internas."
                . "\n3.- Cotización válida por 30 días desde la fecha de elaboración.";
                break;

            case 'CALIBRACION EXTERNA':
                $Margin = 205;
                $NOTAS .= ''
                . "\n1.- El servicio se programa con orden de compra y equipo disponible para Metrología Aplicada."
                . "\n2.- Cotización válida por 30 días desde la fecha de elaboración."
                . "\n3.- Si el cliente solicita otro proveedor, se iniciará nuevo proceso de cotización."
                . "\n4.- Costo aplicable incluso si el equipo no pasa calibración."
                . "\n5.- No hay garantía de estabilidad en tolerancias por factores externos."
                . "\n6.- Tiempos de entrega pueden cambiar, se notificará con anticipación."
                . "\n7.- El tiempo empieza a correr desde la aprobación y recepción del equipo."
                . "\nESTE SERVICIO CONSTA DE:"
                . "\n1.- Revisión y limpieza externa del equipo."
                . "\n2.- Calibración del equipo.";
                break;

            case 'MAPEO':
                $Margin = 215;
                $NOTAS .= ''
                . "\n1.- El servicio se programa con orden de compra."
                . "\n2.- El equipo debe estar disponible en planta para el servicio."
                . "\n3.- La cotización cubre el tiempo estimado. Excesos se cotizan por separado."
                . "\n4.- Cambios en requerimientos se cotizan y requieren autorización."
                . "\n5.- Cotización válida por 60 días desde su elaboración.";
                break;

            case 'LISTA PRECIOS':
                $Margin = 210;
                $NOTAS .= ''
                . "\n1.- Se requiere orden de compra para programar el servicio."
                . "\n2.- Calibración según procedimientos internos. Requerimientos especiales implican nueva cotización."
                . "\n3.- Costo aplicable aunque el equipo no pase calibración."
                . "\n4.- No se garantiza estabilidad del equipo debido a factores externos."
                . "\n5.- Vigencia establecida en contrato o acuerdo correspondiente."
                . "\nESTE SERVICIO CONSTA DE:"
                . "\n1.- Revisión y limpieza externa del equipo."
                . "\n2.- Calibración del equipo.";
                break;
        }
    }

        // Oculta errores para evitar que interfieran con la salida del PDF
    ini_set('display_errors', 0);

    // Carga la librería personalizada para generar PDF
    $this->load->library('pdfview');

    // Selección de plantilla según estatus de la cotización
    if ($ESTATUS == "CANCELADA") {
        $pdf = new pdfview_CANCEL(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    } else {
        if ($APROB > 0) {
            $pdf = new pdfview(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        } else {
            $pdf = new pdfview_NA(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        }
    }

    // Configuración general del PDF
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('AleksOrtiz');
    $pdf->SetTitle('Masmetrologia');
    $pdf->SetSubject('Formato Cotización');

    // Texto de cabecera personalizado
    $spc = "            ";
    $head = "Metrologia Aplicada y Servicios, S. de R.L. de C.V. $spc Cotización / $COT";
    $txt = "Av. Ramón Rayón #1520 Int-9                                                           Fecha: " . $FECHA;
    $txt .= "\nCol. Rio Bravo                                                                                    Ejecutivo: " . $RESPONSABLE;
    $txt .= "\nCd. Juárez, Chih. C.P. 32550                                                                 Correo: " . $RESPONSABLE_CORREO;
    $txt .= "\nRFC: MAS080825EE7";

    $pdf->SetHeaderData(PDF_HEADER_LOGO_ORIGINAL, '40', $head, $txt);

    // Fuentes y márgenes
    $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 9));
    $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    $pdf->SetMargins(8, PDF_MARGIN_TOP, 8); // Márgenes modificados manualmente
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // Carga idioma si existe
    if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
        require_once(dirname(__FILE__) . '/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // Fuente por defecto y nueva página
    $pdf->SetFont('times', '', 8);
    $pdf->AddPage();

    // Construcción de la tabla de cabecera con datos del cliente
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

    // Si existe planta asociada, incluir sus datos
    if ($ID_PLANTA > 0) {
        $tbl .= <<<EOD
            <b>Planta/Plant:</b><br>
            $NOMBRE_PLANTA<br>
            $CALLE_PLANTA<br>
            $COLONIA_PLANTA<br>
            $CIUDAD_PLANTA $ESTADO_PLANTA<br><br>
EOD;
    }

    // Sección de contacto
    $tbl .= <<<EOD
                <b>Contacto / Contact:</b><br>
                $CONTACTO<br>
                $TELEFONO<br>
                $CORREO<br>
            </td>
        </tr>
    </table>
EOD;

    // Imprime la tabla de cabecera
    $pdf->writeHTML($tbl, false, false, false, false, '');

    // Ancho de columnas para tabla de conceptos
    $w = array(8, 125, 12, 24, 24);

    // Encabezado de tabla de conceptos
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->SetTextColor(255); // Blanco
    $pdf->SetTextColor(0);   // Negro

    // Nombres de columnas
    $pdf->Cell($w[0], 6, "#", 1, 0, 'C');
    $pdf->Cell($w[1], 6, "Descripción", 1, 0, 'C');
    $pdf->Cell($w[2], 6, "Cant.", 1, 0, 'C');
    $pdf->Cell($w[3], 6, "Precio Unit.", 1, 0, 'C');
    $pdf->Cell($w[4], 6, "Importe", 1, 1, 'C');

    // Fuente base para los conceptos
    $pdf->SetFont('', '', 7);

    // Inicializa índice de conceptos
    $i = 0;

    // Altura por renglón base para celdas
    $RenSpace = 2.8;

    foreach ($conceptos as $indice => $concepto) {
        $pdf->SetFont('', '', 7);

        // Si el concepto tiene atributo "otro" (CABEZAL o SEPARADOR)
        if (array_key_exists("otro", $concepto->atributos)) {
            // Espacio en blanco para separación visual
            for ($j = 0; $j < 5; $j++) {
                $pdf->writeHTMLCell($w[$j], 0, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
            }

            $startX = $pdf->GetX();
            $startY = $pdf->GetY();
            $cellcount = [];

            if ($concepto->atributos->otro == 'CABEZAL') {
                $pdf->SetFont('', 'B', 8);
                $pdf->MultiCell($w[0], '', '', 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[1], '', $concepto->descripcion, 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[2], '', '', 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[3], '', '', 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[4], '', '', 'LR', 'C', 0, 1);

                $h = (max($cellcount)) * $RenSpace;
                $pdf->SetXY($startX, $startY);
                for ($j = 0; $j < 5; $j++) {
                    $pdf->writeHTMLCell($w[$j], $h, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
                }
                $pdf->SetFont('', '', 7);
            } elseif ($concepto->atributos->otro == 'SEPARADOR') {
                $pdf->MultiCell($w[0], '', '===', 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[1], '', str_repeat('=', 84), 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[2], '', '=====', 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[3], '', str_repeat('=', 12), 'LR', 'C', 0, 0);
                $pdf->MultiCell($w[4], '', str_repeat('=', 12), 'LR', 'C', 0, 1);

                $h = (max($cellcount)) * $RenSpace;
                $pdf->SetXY($startX, $startY);
                for ($j = 0; $j < 5; $j++) {
                    $pdf->writeHTMLCell($w[$j], $h, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
                }
            }
        } else {
            // Concepto estándar
            $i++;

            // Espacio inicial
            for ($j = 0; $j < 5; $j++) {
                $pdf->writeHTMLCell($w[$j], 0, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
            }

            // Sitio y tiempo de entrega
            $dicSitios = ['OS' => 'En Planta', 'LAB' => 'LAB', 'EXT' => 'Prov. Externo'];
            $sitio = $concepto->sitio != "N/A" ? ", Donde: " . $dicSitios[$concepto->sitio] : "";
            $tEntrega = $concepto->tiempo_entrega > 0
                ? "T. Entrega: {$concepto->tiempo_entrega} Día" . ($concepto->tiempo_entrega > 1 ? "s" : "") . $sitio
                : "T. Entrega: N/A" . $sitio;

            // Si tiene servicios: mostrarlos con descripción extendida
            if (count($concepto->servicios) > 0) {
                $codigos = "";
                foreach ($concepto->servicios as $value) {
                    $codigos .= ($value[1] == "N/A" ? $value[2] : $value[1]) . ", ";
                }

                $startX = $pdf->GetX();
                $startY = $pdf->GetY();
                $cellcount = [];
                $cellcount[] = $pdf->MultiCell($w[0], '', $indice + 1, 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[1], '', "Servicios: $codigos   $tEntrega", 0, 'L', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[2], '', number_format($concepto->cantidad, 0), 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[3], '', "$" . number_format($concepto->precio_unitario, 2), 0, 'R', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[4], '', "$" . number_format($concepto->importe, 2), 0, 'R', 0, 1);

                $h = max($cellcount) * $RenSpace;
                $pdf->SetXY($startX, $startY);
                for ($j = 0; $j < 5; $j++) {
                    $pdf->writeHTMLCell($w[$j], $h, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
                }
            }

            // Datos del equipo desde los atributos
            $att = "";
            foreach (['ID', 'Marca', 'Modelo', 'Serie'] as $key) {
                if (isset($concepto->atributos->$key)) {
                    $att .= "$key: {$concepto->atributos->$key}, ";
                }
            }

            foreach ($concepto->atributos as $key => $value) {
                if (!in_array($key, ['ID', 'Marca', 'Modelo', 'Serie'])) {
                    $att .= "$key: $value, ";
                }
            }

            $att = rtrim($att, ', ');

            $startX = $pdf->GetX();
            $startY = $pdf->GetY();
            $cellcount = [];
            $cellcount[] = $pdf->MultiCell($w[0], '', (count($concepto->servicios) > 0 ? "" : $indice + 1), 0, 'C', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[1], '', $concepto->descripcion . (count($concepto->servicios) > 0 ? " $att" : "   $tEntrega"), 0, 'L', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[2], '', (count($concepto->servicios) > 0 ? "" : $concepto->cantidad), 0, 'C', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[3], '', (count($concepto->servicios) > 0 ? "" : "$" . number_format($concepto->precio_unitario, 2)), 0, 'R', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[4], '', (count($concepto->servicios) > 0 ? "" : "$" . number_format($concepto->importe, 2)), 0, 'R', 0, 1);

            $h = max($cellcount) * $RenSpace;
            $pdf->SetXY($startX, $startY);
            for ($j = 0; $j < 5; $j++) {
                $pdf->writeHTMLCell($w[$j], $h, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
            }

            // Comentarios del concepto
            if (!empty($concepto->comentarios)) {
                $startX = $pdf->GetX();
                $startY = $pdf->GetY();
                $cellcount = [];
                $cellcount[] = $pdf->MultiCell($w[0], '', "", 0, 'C', 0, 0);
                $cellcount[] = $pdf->MultiCell($w[1], '', "** " . $concepto->comentarios, 0, 'L', 0, 0);
                for ($j = 2; $j < 5; $j++) {
                    $cellcount[] = $pdf->MultiCell($w[$j], '', "", 0, 'R', 0, 0);
                }

                $h = max($cellcount) * $RenSpace;
                $pdf->SetXY($startX, $startY);
                for ($j = 0; $j < 5; $j++) {
                    $pdf->writeHTMLCell($w[$j], $h, '', '', '', "LR", ($j == 4), 0, true, 'J', false);
                }
            }

            // Salto de página si se rebasa el límite vertical
            if ($pdf->GetY() > 260) {
                for ($j = 0; $j < 5; $j++) {
                    $pdf->writeHTMLCell($w[$j], '', '', '', '', "LRB", ($j == 4), 0, true, 'J', false);
                }

                $pdf->AddPage();
                $pdf->SetFont('times', '', 8);
                $pdf->writeHTML($tbl, false, false, false, false, '');

                if ($i < count($conceptos)) {
                    $pdf->SetFont('helvetica', 'B', 9);
                    $pdf->SetTextColor(0);
                    $pdf->Cell($w[0], 6, "#", 1, 0, 'C');
                    $pdf->Cell($w[1], 6, "Descripción", 1, 0, 'C');
                    $pdf->Cell($w[2], 6, "Cant.", 1, 0, 'C');
                    $pdf->Cell($w[3], 6, "Precio Unit.", 1, 0, 'C');
                    $pdf->Cell($w[4], 6, "Importe", 1, 1, 'C');

                    for ($j = 0; $j < 5; $j++) {
                        $pdf->writeHTMLCell($w[$j], '', '', '', '', "LRT", ($j == 4), 0, true, 'J', false);
                    }
                }
            }
        }
    }

            // DESCRIPCIÓN DE CÓDIGOS DE SERVICIO (solo si existen servicios únicos)
    if (count((array)$SERVICIOS) > 0) {
        // Línea separadora visual
        $pdf->MultiCell($w[0], '', '===', 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[1], '', str_repeat('=', 84), 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[2], '', '=====', 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[3], '', str_repeat('=', 12), 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[4], '', str_repeat('=', 12), 'LR', 'C', 0, 1);

        // Encabezado de sección
        $pdf->SetFont('', 'B', 7);
        $pdf->MultiCell($w[0], '', '', 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[1], '', 'Descripción de Servicios:', 0, 'L', 0, 0);
        $pdf->MultiCell($w[2], '', '', 'LR', 'R', 0, 0);
        $pdf->MultiCell($w[3], '', '', 'LR', 'R', 0, 0);
        $pdf->MultiCell($w[4], '', '', 'LR', 'R', 0, 1);

        // Cuerpo de descripción de servicios
        $pdf->SetFont('', '', 7);
        foreach ($SERVICIOS as $codigo => $servicio) {
            $startX = $pdf->GetX();
            $startY = $pdf->GetY();
            $cellcount = [];

            // Calcula número de líneas necesarias por celda
            $cellcount[] = $pdf->MultiCell($w[0], '', '', 0, 'C', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[1], '', "$codigo: $servicio", 0, 'L', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[2], '', '', 0, 'R', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[3], '', '', 0, 'R', 0, 0);
            $cellcount[] = $pdf->MultiCell($w[4], '', '', 0, 'R', 0, 0);

            // Regresa cursor y escribe celdas con altura unificada
            $pdf->SetXY($startX, $startY);
            $h = (max($cellcount) + 1) * $RenSpace;
            $pdf->MultiCell($w[0], $h, '', 'LR', 'C', 0, 0);
            $pdf->MultiCell($w[1], $h, '', 'LR', 'L', 0, 0);
            $pdf->MultiCell($w[2], $h, '', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[3], $h, '', 'LR', 'R', 0, 0);
            $pdf->MultiCell($w[4], $h, '', 'LR', 'R', 0, 1);
        }
    }

           // Rellena hasta el margen para separar los totales
    $startY = $pdf->GetY();
    for ($startY; $startY < $Margin; $startY += 6) {
        $pdf->MultiCell($w[0], 6, '----', 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[1], 6, str_repeat('-', 120), 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[2], 6, '-------', 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[3], 6, str_repeat('-', 19), 'LR', 'C', 0, 0);
        $pdf->MultiCell($w[4], 6, str_repeat('-', 19), 'LR', 'C', 0, 0);
        $pdf->Ln();
    }

    // Notas de servicio
    $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, $NOTAS, 'T', 'M', 0, 0);

    // Subtotal
    $pdf->SetFont('', '', 8);
    $pdf->MultiCell($w[3], 4, 'Sub-Total', 'T', 'C', 0, 0);
    $pdf->MultiCell($w[4], 4, "$" . number_format($SUBTOTAL, 2), 1, 'R', 0, 0);
    $pdf->Ln();

    // Impuesto
    $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'L', 0, 0);
    $pdf->MultiCell($w[3], 4, $IMPUESTO_NOMBRE, 0, 'C', 0, 0);
    $pdf->MultiCell($w[4], 4, "$" . number_format($IMPUESTO, 2), 1, 'R', 0, 0);
    $pdf->Ln();

    // Total
    $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'M', 0, 0);
    $pdf->MultiCell($w[3], 4, 'Total', 0, 'C', 0, 0);
    $pdf->MultiCell($w[4], 4, "$" . number_format($TOTAL, 2), 1, 'R', 0, 0);
    $pdf->Ln();

    // Moneda
    $pdf->MultiCell($w[0] + $w[1] + $w[2], 6, '', 0, 'M', 0, 0);
    $pdf->MultiCell($w[3] + $w[4], 6, '* Moneda / Currency: ' . $MONEDA, 0, 'C', 0, 0);
    $pdf->Ln();

    // Espacios de separación
    $pdf->MultiCell(180, 6, '', 0, 'M', 0, 0);
    $pdf->Ln();
    $pdf->MultiCell(180, 6, '', 0, 'M', 0, 0);
    $pdf->Ln();
    $pdf->Ln();

    // Salida del PDF: como string ('S') o mostrar en navegador ('I')
    if ($modo === 'S') {
        return $pdf->Output('', 'S'); // Devuelve como string (por ejemplo, para adjuntar a correo)
    } else {
        $cot = str_pad($id, 6, '0', STR_PAD_LEFT) . '_Rev_' . $rev;
        $pdf->Output("COT-$cot.pdf", 'I'); // Muestra en el navegador
    }
}

function ajax_setCotizacion() {
    $id_cotizacion = null;
    $id_cotizacion_nueva = null;

    // Decodifica los objetos JSON recibidos por POST
    $cotizacion = json_decode($this->input->post("cotizacion"));
    $conceptos = json_decode($this->input->post("conceptos"));

    // Si se copia desde una cotización existente, guarda el ID de origen
    if (isset($cotizacion->copiar_desde)) {
        $id_cotizacion = $cotizacion->copiar_desde;
    }

    // Si es una nueva cotización
    if ($cotizacion->id == 0) {
        $cotizacion->usuario = $this->session->id;

        // Inserta la nueva cotización con timestamp automático
        $cotizacion->id = $this->Conexion->insertar(
            'cotizaciones',
            $cotizacion,
            array('fecha' => 'CURRENT_TIMESTAMP()')
        );
        $id_cotizacion_nueva = $cotizacion->id;

        // Verifica si la empresa es un prospecto para marcarlo
        $res = $this->Conexion->consultar(
            'SELECT prospecto FROM empresas WHERE id = ' . $cotizacion->empresa,
            TRUE
        );

        if ($res->prospecto == '1') {
            $prospectoCot['prospectoEmp'] = '1';
            $this->Conexion->modificar(
                'cotizaciones',
                $prospectoCot,
                null,
                array('id' => $cotizacion->id)
            );
        }

        } else {
        // M O D I F I C A C I Ó N   D E   C O T I Z A C I Ó N
        $func = null;

        // Si se aprueba, se registra aprobador y fecha
        if ($cotizacion->estatus == 'AUTORIZADA') {
            $cotizacion->aprobador = $this->session->id;
            $func['fecha_aprobacion'] = 'CURRENT_TIMESTAMP()';
        }

        // Si se confirma, se registra la fecha de confirmación
        if ($cotizacion->estatus == 'CONFIRMADA') {
            $func['fecha_confirmacion'] = 'CURRENT_TIMESTAMP()';
        }

        // Si es aprobado total, actualiza prospecto de la empresa y cancela seguimientos
        if ($cotizacion->estatus == 'APROBADO TOTAL') {
            $res = $this->Conexion->consultar(
                'SELECT prospecto FROM empresas WHERE id = ' . $cotizacion->empresa,
                TRUE
            );

            if ($res->prospecto == '1') {
                $empresa['prospecto'] = '0';
                $this->Conexion->modificar(
                    'empresas',
                    $empresa,
                    null,
                    array('id' => $cotizacion->empresa)
                );
            }

            $seg = $this->Conexion->consultar(
                "SELECT ca.*, u.correo 
                 FROM cot_acciones ca 
                 JOIN usuarios u ON u.id = ca.usuario 
                 WHERE estatus = 'PENDIENTE' AND idCot = " . $cotizacion->id
            );

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

        // Actualiza los datos de la cotización
        $this->Conexion->modificar(
            'cotizaciones',
            $cotizacion,
            $func,
            array('id' => $cotizacion->id)
        );

        // Si la cotización fue cancelada, cancela seguimientos relacionados
        if ($cotizacion->estatus == 'CANCELADA') {
            $seg = $this->Conexion->consultar(
                "SELECT ca.*, u.correo 
                 FROM cot_acciones ca 
                 JOIN usuarios u ON u.id = ca.usuario 
                 WHERE estatus = 'PENDIENTE' AND idCot = " . $cotizacion->id
            );

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
               // E N V Í O   D E   C O R R E O S

        // Si se envió un comentario desde el formulario
        if (isset($_POST['comentarios']) && $_POST['comentarios']) {
            $datos['comentarios'] = $this->input->post('comentarios');
        }

        // Si está pendiente de autorización, notifica al autorizador
        if ($cotizacion->estatus == 'PENDIENTE AUTORIZACION') {
            $res = $this->Conexion->consultar(
                "SELECT 
                    E.nombre AS NombreCliente, 
                    EC.nombre AS NombreContacto, 
                    CONCAT(R.nombre, ' ', R.paterno) AS Responsable, 
                    A.correo 
                 FROM cotizaciones C 
                 INNER JOIN usuarios R ON R.id = C.responsable 
                 INNER JOIN usuarios A ON R.autorizador_cotizacion = A.id 
                 INNER JOIN empresas E ON E.id = C.empresa 
                 INNER JOIN empresas_contactos EC ON EC.id = C.contactos->'$[0]' 
                 WHERE C.id = $cotizacion->id",
                TRUE
            );

            $datos['id'] = $cotizacion->id;
            $datos['correos'] = $res->correo;
            $datos['nombreCliente'] = $res->NombreCliente;
            $datos['nombreContacto'] = $res->NombreContacto;
            $datos['nombreResponsable'] = $res->Responsable;

            if (isset($cotizacion->enviar_autorizar) && $cotizacion->enviar_autorizar == 1) {
                // Lógica adicional si se requiere enviar autorización explícitamente (actualmente vacía)
            }

            // Envío de solicitud de aprobación
            $this->correos_cotizaciones->solicitarAprobacion($datos);
        }

        // Si fue rechazada, notificar al responsable
        if ($cotizacion->estatus == 'RECHAZADA') {
            $res = $this->Conexion->consultar(
                "SELECT 
                    E.nombre AS NombreCliente, 
                    EC.nombre AS NombreContacto, 
                    CONCAT(R.nombre, ' ', R.paterno) AS Responsable, 
                    R.correo 
                 FROM cotizaciones C 
                 INNER JOIN usuarios R ON R.id = C.responsable 
                 INNER JOIN empresas E ON E.id = C.empresa 
                 INNER JOIN empresas_contactos EC ON EC.id = C.contactos->'$[0]' 
                 WHERE C.id = $cotizacion->id",
                TRUE
            );

            $datos['id'] = $cotizacion->id;
            $datos['correos'] = $res->correo;
            $datos['nombreCliente'] = $res->NombreCliente;
            $datos['nombreContacto'] = $res->NombreContacto;
            $datos['nombreResponsable'] = $res->Responsable;

            $this->correos_cotizaciones->rechazoCotizacion($datos);
        }

        // Si fue autorizada, notificar también al responsable
        if ($cotizacion->estatus == 'AUTORIZADA') {
            $res = $this->Conexion->consultar(
                "SELECT 
                    E.nombre AS NombreCliente, 
                    EC.nombre AS NombreContacto, 
                    CONCAT(R.nombre, ' ', R.paterno) AS Responsable, 
                    R.correo, 
                    correo_cc 
                 FROM cotizaciones C 
                 INNER JOIN usuarios R ON R.id = C.responsable 
                 INNER JOIN empresas E ON E.id = C.empresa 
                 INNER JOIN empresas_contactos EC ON EC.id = C.contactos->'$[0]' 
                 WHERE C.id = $cotizacion->id",
                TRUE
            );

            $datos['id'] = $cotizacion->id;
            $datos['correos'] = $res->correo;
            $datos['nombreCliente'] = $res->NombreCliente;
            $datos['nombreContacto'] = $res->NombreContacto;
            $datos['nombreResponsable'] = $res->Responsable;

            $this->correos_cotizaciones->aprobacionCotizacion($datos);
        }
    }

        // Procesamiento de los conceptos asociados a la cotización
        foreach ($conceptos as $key => $elem) {
            $elem->cotizacion = $cotizacion->id;

            // Inserta nuevo concepto
            if ($elem->id == 0) {
                $this->Conexion->insertar('cotizaciones_conceptos', $elem);
            }

            // Elimina concepto si el ID es negativo (marcado para eliminar)
            if ($elem->id < 0) {
                $this->Conexion->eliminar('cotizaciones_conceptos', array('id' => (intval($elem->id) * -1)));
            }

            // Actualiza o re-inserta concepto si ya existía
            else if ($elem->id != 0) {
                $cot = $this->Conexion->consultar(
                    "SELECT cotizacion FROM cotizaciones_conceptos WHERE cotizacion = '$elem->cotizacion'",
                    TRUE
                );

                if ($cot) {
                    $id_cotizacion = $cot;
                    $this->Conexion->modificar('cotizaciones_conceptos', $elem, null, array('id' => $elem->id));
                } else {
                    unset($elem->id); // Elimina el ID para forzar una inserción nueva
                    $this->Conexion->insertar('cotizaciones_conceptos', $elem);
                }
            }
        }

    // Si no hay estatus (es decir, la cotización aún no ha sido enviada formalmente)
    if (!isset($cotizacion->estatus)) {
        if ($id_cotizacion) {
            // Comentario en la nueva cotización indicando de cuál se copió
            $comentarios = array(
                'cotizacion' => $id_cotizacion_nueva,
                'usuario' => $this->session->id,
                'comentario' => 'Esta cotización es copia de: #' . $id_cotizacion,
            );
            $this->Conexion->insertar('cotizaciones_comentarios', $comentarios, array('fecha' => 'CURRENT_TIMESTAMP()'));

            // Comentario en la cotización original indicando que fue copiada
            $comentarios2 = array(
                'cotizacion' => $id_cotizacion,
                'usuario' => $this->session->id,
                'comentario' => 'Esta cotización fue copiada para la cotización: #' . $id_cotizacion_nueva,
            );
            $this->Conexion->insertar('cotizaciones_comentarios', $comentarios2, array('fecha' => 'CURRENT_TIMESTAMP()'));
        }
    }

    // Si se envió un comentario general en POST, agregarlo como comentario nuevo
    if (isset($_POST['comentarios']) && $_POST['comentarios']) {
        $comentario = new stdClass;
        $comentario->cotizacion = $cotizacion->id;
        $comentario->usuario = $this->session->id;
        $comentario->comentario = $this->input->post('comentarios');

        $this->Conexion->insertar('cotizaciones_comentarios', $comentario, array('fecha' => 'CURRENT_TIMESTAMP()'));
    }

    // Devuelve el ID de la cotización como respuesta (por ejemplo, para JS)
    echo $cotizacion->id;
}


    function ajax_getCotizaciones() {
    // Limpia la caché de la vista de catálogo
    $this->output->delete_cache('cotizaciones/catalogo');

    // Obtiene los filtros enviados por POST
    $id = $this->input->post('id');
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $cliente = $this->input->post('cliente');
    $estatus = $this->input->post('estatus');
    $tipo = $this->input->post('tipo');
    $cerradas = $this->input->post('cerradas');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Formateo de fechas para rango de búsqueda
    $f1 = strval($fecha1) . ' 00:00:00';
    $f2 = strval($fecha2) . ' 23:59:59';

    // Filtro de fecha por defecto (último año)
    $Limitante_Fecha = " AND (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR))";

    // Bandera para agrupar resultados
    $group = TRUE;

    // Query base
    $query = "SELECT C.*, IFNULL(MAX(CC.revision), 0) AS UltRev, E.nombre AS Cliente, EC.nombre AS Contacto, CONCAT(R.nombre, ' ', R.paterno) AS Responsable, E.prospecto FROM cotizaciones C LEFT JOIN empresas E ON E.id = C.empresa LEFT JOIN empresas_contactos EC ON EC.id = C.contactos->'$[0]' LEFT JOIN usuarios R Oid = C.responsable LEFT JOIN cotizaciones_conceptos CC ON C.id = CC.cotizacion WHERE 1 = 1
    ";

        // Si se busca por ID específico
    if ($id) {
        $query .= " AND C.id = '$id'";
    } else {
        // Filtro por cliente seleccionado
        if (!empty($cliente) && $cliente != 0) {
            $query .= " AND C.empresa = '$cliente'";
        }

        // Filtro por tipo de cotización
        if (!empty($tipo) && $tipo != "TODOS") {
            $query .= " AND C.tipo = '$tipo'";
        }

        // Filtro por estatus
        if (!empty($estatus) && $estatus != 'TODOS') {
            $query .= " AND C.estatus = '$estatus'";
        } else {
            // Si no se muestran cerradas
            if ($cerradas == "0") {
                $query .= " AND (C.estatus != 'CERRADO TOTAL' AND C.estatus != 'CERRADO PARCIAL' AND C.estatus != 'CANCELADA')";
            }
        }

        // Filtro por texto (folio, ID, marca, etc.)
        if (!empty($texto)) {
            if ($parametro == "folio") {
                $query .= " AND C.id = '$texto'";
            }
            if ($parametro == "id") {
                $query .= " AND UPPER(CC.atributos->'$.ID') LIKE '%" . strtoupper($texto) . "%'";
            }
            if ($parametro == "marca") {
                $query .= " AND UPPER(CC.atributos->'$.Marca') LIKE '%" . strtoupper($texto) . "%'";
            }
            if ($parametro == "serie") {
                $query .= " AND UPPER(CC.atributos->'$.Serie') LIKE '%" . strtoupper($texto) . "%'";
            }
            if ($parametro == "modelo") {
                $query .= " AND UPPER(CC.atributos->'$.Modelo') LIKE '%" . strtoupper($texto) . "%'";
            }
            if ($parametro == "contenido") {
                $query .= " AND CC.descripcion LIKE '%$texto%'";
            }

            // Si el filtro es por responsable, requiere agrupamiento especial
            if ($parametro == "responsable") {
                $query .= $Limitante_Fecha;
                $group = FALSE;
                $query .= " GROUP BY C.id";
                $query .= " HAVING Responsable LIKE '%$texto%' ";
            }
        }
    }

    // Filtro adicional por rango de fechas (si ambos campos están presentes)
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " AND C.fecha BETWEEN '$f1' AND '$f2'";
    }

    // Aplica agrupamiento si está habilitado
    if ($group) {
        $query .= $Limitante_Fecha;
        $query .= " GROUP BY C.id";
    }

    // Orden descendente por ID
    $query .= " ORDER BY C.id DESC";

    // Ejecuta la consulta
    $res = $this->Conexion->consultar($query, $id);

    // Devuelve los resultados en formato JSON
    if ($res) {
        echo json_encode($res);
    }
}

    function ajax_getClientesCotizaciones() {
    $texto = $this->input->post('texto');

    // Consulta que devuelve clientes con número de cotizaciones
    $query = "SELECT E.id, E.nombre, COUNT(C.id) AS NumCot FROM cotizaciones C INNER JOIN empresas E ON E.id = C.empresa
    ";

    // Filtro por nombre de cliente si se proporcionó texto
    if ($texto) {
        $query .= " WHERE E.nombre LIKE '%$texto%'";
    }

    $query .= " GROUP BY E.id";
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

   function ajax_getCotizacionConceptos() {
    $coti = $this->input->post('cotizacion');
    $rev = $this->input->post('revision');

    // Consulta conceptos de una cotización (opcionalmente por revisión)
    $query = "SELECT CC.* FROM cotizaciones_conceptos CC WHERE cotizacion = $coti";

    // Si se envió la revisión como parámetro, agregarla al filtro
    if (isset($_POST["revision"])) {
        $query .= " AND revision = $rev";
    }

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_setRevision() {
    $cotizacion = json_decode($this->input->post("cotizacion"));
    $conceptos = json_decode($this->input->post("conceptos"));
    $id = $cotizacion->id;

    // Elimina el ID de la cotización (solo se usará como referencia, no se modificará)
    unset($cotizacion->id);

    // Obtiene el siguiente número de revisión
    $rv = $this->Conexion->consultar(
        "SELECT (MAX(CC.revision) + 1) AS Rev 
         FROM cotizaciones_conceptos CC 
         WHERE CC.cotizacion = $id", 
        TRUE
    );

    // Inserta todos los conceptos con la nueva revisión
    foreach ($conceptos as $key => $elem) {
        $elem->cotizacion = $id;
        $elem->revision = $rv->Rev;
        $this->Conexion->insertar('cotizaciones_conceptos', $elem);
    }

    // Actualiza la cabecera de la cotización (excepto el ID)
    $this->Conexion->modificar('cotizaciones', $cotizacion, null, array('id' => $id));

    echo "1";
}

function ajax_setComentarios() {
    $comentario = json_decode($this->input->post('comentario'));

    // Asigna al comentario el usuario actual y la fecha actual
    $comentario->usuario = $this->session->id;
    $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');

    // Inserta el comentario
    $res = $this->Conexion->insertar('cotizaciones_comentarios', $comentario, $funciones);

    if ($res > 0) {
        echo "1";
    }
}

function ajax_getComentarios() {
    $id = $this->input->post('id');

    // Consulta los comentarios de una cotización y el nombre del usuario que los hizo
    $query = "SELECT C.*, CONCAT(U.nombre, ' ', U.paterno) AS User FROM cotizaciones_comentarios C INNER JOIN usuarios U ON U.id = C.usuario WHERE 1 = 1
    ";

    // Filtra por ID de cotización si se proporciona
    if ($id) {
        $query .= " AND C.cotizacion = '$id'";
    }

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getSAData() {
    // Permite acceso desde cualquier origen (CORS)
    header("Access-Control-Allow-Origin: *");

    // Obtiene el ID del equipo desde POST
    $id_equipo = $this->input->post('id_equipo');

    // Ejecuta un archivo externo .exe pasando el ID como parámetro
    $res = shell_exec("C:/xampp/htdocs/sa_reader/sa_reader.exe \"$id_equipo\"");
    echo $res;
}

function ajax_getClientes() {
    $id = $this->input->post('id');
    $nombre = $this->input->post('nombre');

    // Consulta empresas que sean clientes y tengan moneda de cotización
    $query = "SELECT E.* FROM empresas E WHERE E.cliente = 1 AND JSON_LENGTH(E.moneda_cotizacion) > 0
    ";

    // Filtro por ID si se proporciona
    if ($id) {
        $query .= " AND E.id = '$id'";
    } else {
        // Filtro por nombre parcial si se proporciona
        if ($nombre) {
            $query .= " AND E.nombre LIKE '%$nombre%'";
        }
    }

    $res = $this->Conexion->consultar($query, $id);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getPlanta() {
    $id = $this->input->post('id');

    // Consulta la planta de la empresa por ID
    $query = "SELECT * FROM empresa_plantas WHERE id = $id";

    $res = $this->Conexion->consultar($query, TRUE);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getbuscarAutores() {
    $id = $this->input->post('id');

    // Consulta usuarios activos con privilegio para administrar cotizaciones
    $query = "SELECT U.id, CONCAT(U.nombre, ' ', U.paterno) AS Nombre, P.puesto AS Puesto, U.correo FROM usuarios U INNER JOIN puestos P ON U.puesto = P.id INNER JOIN privilegios PR ON PR.usuario = U.id WHERE U.activo = 1 AND PR.administrar_cotizaciones = 1";

    // Filtro por ID si se proporciona
    if ($id) {
        $query .= " AND U.id = '$id'";
    }

    $res = $this->Conexion->consultar($query, $id);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getContactos() {
    $id = $this->input->post('id');
    $empresa = $this->input->post('id_cliente');

    // Consulta contactos activos y cotizables
    $query = "SELECT * FROM empresas_contactos WHERE activo = 1 AND cotizable = 1";

    // Filtro por ID si se proporciona
    if ($id) {
        $query .= " AND id = '$id'";
    } else {
        // Filtro por empresa si se proporciona
        if ($empresa) {
            $query .= " AND empresa = '$empresa'";
        }
    }

    $res = $this->Conexion->consultar($query, $id);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getNombresUsuarios() {
    $ids = $this->input->post('ids');

    // Convierte el array en sintaxis SQL válida para IN
    $ids = str_replace('[', '(', $ids);
    $ids = str_replace(']', ')', $ids);

    // Consulta nombres de los usuarios dados sus IDs
    $query = "SELECT id, CONCAT(nombre, ' ', paterno) AS User FROM usuarios WHERE id IN $ids";

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getServicio() {
    $codigo = $this->input->post('codigo');

    // Consulta servicio por código y su precio
    $query = "SELECT S.id, S.codigo, S.sitio, S.descripcion AS DescripcionServicio, CP.alto_a AS Precio FROM servicios S INNER JOIN claves_precio CP ON S.clave_precio = CP.id WHERE S.codigo = '$codigo'";

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getServicioMarMod() {
    $fabricante = $this->input->post('fabricante');
    $modelo = $this->input->post('modelo');

    // Consulta requerimiento catalogado por marca y modelo
    $query = "SELECT R.id, R.descripcion, R.servicio FROM requerimientos R WHERE R.catalogado = '1' AND UPPER(TRIM(R.fabricante)) = '$fabricante' AND UPPER(TRIM(R.modelo)) = '$modelo' LIMIT 1";

    $res = $this->Conexion->consultar($query, TRUE);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_enviarCorreo() {
    // Carga la librería encargada del envío de correos con archivos adjuntos
    $this->load->library('correos_archivos');

    // Decodifica la cotización recibida por POST
    $cotizacion = json_decode($this->input->post("cotizacion"));      

    $id_cot = $cotizacion->id;
    $param = $cotizacion->id . "-" . $cotizacion->UltRev;

    // Genera el PDF de la cotización
    $pdf_content = $this->cotizacion_pdf($param, 'S');

    // Consulta los correos del contacto, responsable, creador y copia
    $res = $this->Conexion->consultar("SELECT ec.correo AS contacto, r.correo AS responsable, u.correo AS creador, c.correo_cc FROM cotizaciones c JOIN empresas_contactos ec ON JSON_CONTAINS(c.contactos, CAST(ec.id AS JSON), '$') JOIN usuarios r ON c.responsable = r.id JOIN usuarios u ON c.usuario = u.id WHERE c.id = $id_cot",TRUE );

    // Consulta el cuerpo del correo activo
    $body = $this->Conexion->consultar("SELECT * FROM texto_correo WHERE activo = 1", TRUE);

    // Obtiene la firma del usuario en sesión
    $firma = $this->Conexion->consultar("SELECT foto_firma FROM usuarios WHERE id = " . $this->session->id, TRUE);

    // Guarda la firma en un archivo temporal
    $nombre_archivo = 'firma_' . $this->session->id . '.png';
    $ruta_temporal = sys_get_temp_dir() . '/' . $nombre_archivo;
    file_put_contents($ruta_temporal, $firma->foto_firma);

    // Construye los datos del correo
    $datos['asunto'] = "Cotización Masmetrologia – #" . $id_cot;
    $datos['pdf'] = $pdf_content;
    $datos['pdf_nombre'] = "Cotización - #" . $param . ".pdf";
    $datos['body'] = $body->texto;
    $datos['firma'] = $ruta_temporal;
    $datos['firma_nombre'] = $nombre_archivo;
    $datos['contacto'] = $res->contacto;
    $datos['responsable'] = $res->responsable;
    $datos['creador'] = $res->creador;
    $datos['correo_cc'] = $res->correo_cc;

    // Envía el correo y actualiza estatus si es AUTORIZADA
    if ($this->correos_archivos->Solicitar_Autorización($datos)) {
        if ($datos['estatus'] == "AUTORIZADA") {
            $this->Conexion->modificar(
                'cotizaciones',
                array(
                    'estatus' => 'ENVIADA',
                    'bitacora_estatus' => $cotizacion->bitacora_estatus
                ),
                null,
                array('id' => $datos['id'])
            );
        }
        echo "1";
    }
}

// Carga la vista del calendario de cotizaciones con los datos del subusuario actual
function calendario() {
    $this->load->model('usuarios_model');
    $datos['sub'] = $this->usuarios_model->userCots();
    
    $this->load->view('header');
    $this->load->view('cotizaciones/calendario', $datos);
}

// Obtiene las acciones programadas para mostrarlas en el calendario
function ajax_getAcciones_calendar() {
    $idsub = $this->input->get('idSub'); 

    $query = "SELECT A.*, concat('Responsable: ',U.nombre, ' ', U.paterno,'\r\n','Cotizacion: ', A.idCot ) as title, A.fecha_limite as start, A.fecha_limite + interval 1 hour as end, concat(U.nombre, ' ', U.paterno) as User,";
    $query .= " if(estatus = 'CANCELADA', 'gray', if(estatus = 'PENDIENTE' and current_timestamp() > A.fecha_limite, 'red', if(estatus = 'PENDIENTE' and current_timestamp() <= A.fecha_limite, '#f0ad4e', if(estatus = 'REALIZADA' and A.fecha_realizada > A.fecha_limite, '#76A874', if(estatus = 'REALIZADA' and A.fecha_realizada <= A.fecha_limite, 'green', ''))))) as color";
    $query .= " from cot_acciones A inner join usuarios U on U.id = A.usuario";

    if (!empty($idsub)) {
        $query .= " where U.id=" . $idsub;
    }

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

// Registra una nueva acción de seguimiento para una cotización y envía un evento de calendario por correo
function ajax_setAccion() {
    $this->load->library('correos_archivos');

    $accion = json_decode($this->input->post('data'));
    $rev = $this->input->post('rev');
    $responsable = $this->input->post('responsable');
    $enviar_contacto = $this->input->post('enviar_contacto');

    $idUser = null;
    $contacto = null;

    $param = $accion->idCot . "-" . $rev;
    $pdf_content = $this->cotizacion_pdf($param, 'S');

    if (empty($responsable)) {
        $accion->usuario = $this->session->id;
        $idUser = $this->session->id;
    } else {
        $accion->usuario = $responsable;
        $idUser = $responsable;
    }

    $accion->estatus = "PENDIENTE";
    $func['fecha_creacion'] = 'CURRENT_TIMESTAMP()';

    $id = $this->Conexion->insertar('cot_acciones', $accion, $func);

    $query = "SELECT concat(nombre , '', paterno) as name, correo from usuarios where id = " . $idUser;
    $res = $this->Conexion->consultar($query, TRUE);

    if ($enviar_contacto == 1) {
        $contacto = $this->Conexion->consultar(
            "SELECT ec.correo 
             FROM cotizaciones c 
             JOIN empresas_contactos ec 
             ON JSON_CONTAINS(c.contactos, CAST(ec.id AS JSON), '$') 
             WHERE c.id = $accion->idCot", 
             TRUE
        );
    }

    // Generar contenido para evento de calendario
    $fecha = strtotime($accion->fecha_limite);
    $name = "Cotización : " . $accion->idCot;
    $start = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $end = date('Ymd', $fecha) . 'T' . date('His', $fecha);
    $description = $accion->accion;

    $ical_content = "BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//LearnPHP.co//NONSGML {$res->name}//EN
METHOD:REQUEST
BEGIN:VEVENT
ORGANIZER;CN=" . $this->session->nombre . ":tickets@masmetrologia.com
UID:" . date('Ymd') . 'T' . date('His') . rand() . "-learnphp.co
DTSTAMP:" . date('Ymd') . 'T' . date('His') . "
DTSTART:{$start}
DTEND:{$end}
SUMMARY:{$name}
DESCRIPTION:{$description}
END:VEVENT
END:VCALENDAR";

    // Datos del correo
    $datos['nombre'] = "Seguimiento-" . $accion->idCot . ".ics";
    $datos['cuerpo'] = "Esto es un evento de seguimiento para la cotización - " . $accion->idCot;
    $datos['cal'] = $ical_content;
    $datos['correo'] = $res->correo;
    $datos['accion'] = $description;
    $datos['contacto'] = $contacto->correo;
    $datos['pdf'] = $pdf_content;
    $datos['pdf_nombre'] = "Cotización-" . $param . ".pdf";
    $datos['correoResponsable'] = $this->session->correo;

    $this->correos_archivos->evento_cotizaciones($datos);

    if ($id > 0) {
        echo $id;
    }
}

// Obtiene las acciones asociadas a una cotización específica
function ajax_getAcciones() {
    $po = $this->input->post('idCot');

    $res = $this->Conexion->consultar(
        "SELECT A.*, concat(U.nombre, ' ', U.paterno) as User 
         FROM cot_acciones A 
         INNER JOIN usuarios U ON U.id = A.usuario 
         WHERE A.idCot != 0 AND A.idCot = $po"
    );

    if ($res) {
        echo json_encode($res);
    }
}

// Marca una acción como realizada, registrando la fecha actual
function ajax_setAccionRealizada() {
    $accion = json_decode($this->input->post('data'));
    $func['fecha_realizada'] = 'CURRENT_TIMESTAMP()';

    $id = $this->Conexion->modificar(
        'cot_acciones',
        $accion,
        $func,
        array('id' => $accion->id)
    );
}

// Actualiza los datos de una acción existente
function ajax_updateAccion() {
    $accion = json_decode($this->input->post('data'));

    $id = $this->Conexion->modificar('cot_acciones',$accion,null,array('id' => $accion->id)
    );
}

// Inserta un comentario asociado a una acción
function ajax_setAccionComentario() {
    $comment = json_decode($this->input->post('data'));
    $comment->usuario = $this->session->id;

    $func['fecha'] = 'CURRENT_TIMESTAMP()';

    $id = $this->Conexion->insertar('cot_acciones_comentarios', $comment, $func);

    if ($id > 0) {
        echo $id;
    }
}

// Obtiene los comentarios asociados a una acción específica
function ajax_getAccionComentarios() {
    $accion = $this->input->post('accion');

    $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as User FROM cot_acciones_comentarios C INNER JOIN usuarios U ON U.id = C.usuario WHERE C.accion = " . $accion;

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function exportar() {
    $parametro = $this->input->post('rbBusqueda');
    $texto = $this->input->post('txtBusqueda');
    $cliente = $this->input->post('txtClienteId');
    $tipoCot = $this->input->post('opTipoCotizacion');
    $estatus = $this->input->post('opEstatus');
    $cerradas = $this->input->post('cbCerradasCanceladas');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = strval($fecha1) . ' 00:00:00';
    $f2 = strval($fecha2) . ' 23:59:59';
    $query2 = "";

    if (empty($cerradas)) {
        $close = " and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA')";
    } else {
        $close = "";
    }

    $sep = '",';
    $coma = "'";
    $url = base_url('cotizaciones/ver_cotizacion/');
    $con = $coma . '=HYPERLINK("' . $url . '' . $coma;

    $query = "SELECT C.id as Cotizacion, C.fecha as Fecha, C.tipo as Servicio, C.estatus as Estatus, EC.nombre as Contacto, EC.telefono as Telefono, EC.celular as Celular, EC.correo as Correo, E.nombre as Cliente, concat(R.nombre, ' ', R.paterno) as Responsable, (SELECT fecha_creacion from cot_acciones WHERE idCot= C.id ORDER BY id DESC  limit 1) as Fecha_Seguimiento, (SELECT accion from cot_acciones WHERE idCot= C.id ORDER BY id DESC limit 1) as Accion_Seguimiento, C.prospectoEmp from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->'$[0]' left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 " . $close . "and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ";

    if (!empty($cliente) && $cliente != 0) {
        $query .= " and C.empresa = '$cliente'";
    }
    if (!empty($tipoCot) && $tipoCot != "TODOS") {
        $query .= " and C.tipo = '$tipoCot'";
    }
    if (!empty($estatus) && $estatus != 'TODOS') {
        $query .= " and C.estatus = '$estatus'";
    }

    if (!empty($texto)) {
        if ($parametro == "folio") {
            $query .= " and C.id = '$texto'";
        }
        if ($parametro == "id") {
            $query .= " and UPPER(CC.atributos->'$.ID') like '%" . strtoupper($texto) . "%'";
        }
        if ($parametro == "marca") {
            $query .= " and UPPER(CC.atributos->'$.Marca') like '%" . strtoupper($texto) . "%'";
        }
        if ($parametro == "serie") {
            $query .= " and UPPER(CC.atributos->'$.Serie') like '%" . strtoupper($texto) . "%'";
        }
        if ($parametro == "modelo") {
            $query .= " and UPPER(CC.atributos->'$.Modelo') like '%" . strtoupper($texto) . "%'";
        }
        if ($parametro == "contenido") {
            $query .= " and CC.descripcion like '%$texto%'";
        }
        if ($parametro == "responsable") {
            $query2 = " having Responsable like '%$texto%' ";
        }
    }

    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " and C.fecha BETWEEN '" . $f1 . "' AND '" . $f2 . "'";
    }

    $query .= ' group by C.id ' . $query2 . ' order by C.fecha desc';

    $result = $this->Conexion->consultar($query);

    $salida = '';
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

    foreach ($result as $row) {
        $prospecto = ($row->prospectoEmp == 1) ? 'Si' : 'No';
        $salida .= '
                    <tr>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Cotizacion . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Fecha . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Servicio . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Estatus . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Contacto . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Telefono . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Celular . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Correo . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Cliente . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $prospecto . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Responsable . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->Fecha_Seguimiento . '</td>
                        <td style="color: $444; border: 1px solid black; border-collapse: collapse">' . $row->Accion_Seguimiento . '</td>
                    </tr>';
    }

    $salida .= '</tbody></table>';

    $timestamp = date('m/d/Y', time());
    $filename = 'Cotizaciones' . $timestamp . '.xls';

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary');
    echo $salida;
}

function ajax_getDashboard(){
    $sub = $this->input->post('idSub');

    $query = "SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, ' ', R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and (C.estatus != 'CERRADO TOTAL' and C.estatus != 'CERRADO PARCIAL' and C.estatus != 'CANCELADA') and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ";

    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = ".$this->session->id;
    }

    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%".$sub."%'";
    }

    $query .= " group by C.id order by C.fecha desc";

    $res = $this->Conexion->consultar($query);
    $res = count($res);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardCreadas(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones con estatus "CREADA" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "CREADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios de sesión
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = ".$this->session->id;
    }

    // Filtro por responsable si se indica un subusuario específico
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%".$sub."%'";
    }

    $query .= ' group by C.id order by C.fecha desc';

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardRechazadas(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones RECHAZADAS del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "RECHAZADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios del usuario en sesión
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si aplica
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupación y orden de resultados
    $query .= ' group by C.id order by C.fecha desc';

    // Depuración: se muestra la consulta y se detiene la ejecución
    echo $query; die();

    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardPendientes(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones en estado "PENDIENTE AUTORIZACION" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "PENDIENTE AUTORIZACION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según los privilegios del usuario en sesión
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro adicional si se seleccionó un subusuario específico
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupación por cotización y orden descendente por fecha
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecución de la consulta
    $res = $this->Conexion->consultar($query);

    // Si hay resultados, se regresan en formato JSON
    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardRevision(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones en estado "EN REVISION" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "EN REVISION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios del usuario en sesión
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si se especifica uno
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupamiento y ordenamiento
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecución de la consulta
    $res = $this->Conexion->consultar($query);

    // Devolver resultados en formato JSON
    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardAutorizadas(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones con estatus "AUTORIZADA" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "AUTORIZADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios del usuario en sesión
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si se especifica uno
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupamiento y ordenamiento
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecución de la consulta
    $res = $this->Conexion->consultar($query);

    // Devolver resultados en formato JSON si hay datos
    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardConfirmadas(){
    $sub = $this->input->post('idSub');

    // Consulta principal para cotizaciones con estatus "CONFIRMADA"
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "CONFIRMADA" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro por privilegios del usuario
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si se indicó alguno
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupamiento y orden por fecha descendente
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecutar consulta y retornar resultado si existe
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}
 
function ajax_getDashboardAprobacion(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones con estatus "EN APROBACION" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "EN APROBACION" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios del usuario
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si corresponde
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupamiento y orden final
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecutar consulta y retornar resultado
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardApParcial(){
    $sub = $this->input->post('idSub');

    // Consulta para obtener cotizaciones con estatus "APROBADO PARCIAL" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "APROBADO PARCIAL" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro según privilegios del usuario
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si corresponde
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupamiento y orden final
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecutar consulta y retornar resultado
    $res = $this->Conexion->consultar($query);

    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getDashboardApTotal(){
    $sub = $this->input->post('idSub');

    // Consulta base: obtiene cotizaciones con estatus "APROBADO TOTAL" del último año
    $query = 'SELECT C.*, ifnull(max(CC.revision), 0) as UltRev, E.nombre as Cliente, EC.nombre as Contacto, concat(R.nombre, " ", R.paterno) as Responsable from cotizaciones C left join empresas E on E.id = C.empresa left join empresas_contactos EC on EC.id = C.contactos->"$[0]" left join usuarios R on R.id = C.responsable inner join cotizaciones_conceptos CC on C.id = CC.cotizacion where 1 = 1 and C.estatus = "APROBADO TOTAL" and (C.fecha > (CURRENT_DATE() - INTERVAL 1 YEAR)) ';

    // Filtro por privilegios del usuario
    if ($this->session->privilegios['cotDashboard']) {
        $query .= " ";
    } elseif ($this->session->privilegios['generar_cotizaciones']) {
        $query .= " and C.usuario = " . $this->session->id;
    }

    // Filtro por subusuario si se especifica
    if ($sub != 'TODOS') {
        $query .= " and R.nombre like '%" . $sub . "%'";
    }

    // Agrupar por ID de cotización y ordenar por fecha descendente
    $query .= ' group by C.id order by C.fecha desc';

    // Ejecutar la consulta
    $res = $this->Conexion->consultar($query);

    // Si hay resultados, devolverlos como JSON
    if ($res) {
        echo json_encode($res);
    }
}

function excel()
{
    // Consulta todos los conceptos de la cotización con ID 16747
    $query = "SELECT CC.* from cotizaciones_conceptos CC where cotizacion = 16747";
    $result = $this->Conexion->consultar($query);

    $salida = '';

    // Encabezado de la tabla con estilos en línea
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
    $d = 2;

    // Cuerpo de la tabla con los datos obtenidos
    foreach ($result as $row) {
        $salida .= '
                    <tr>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->cantidad . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->descripcion . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->servicios . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->comentarios . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->sitio . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->tiempo_entrega . '</td>
                        <td style="color: $444;  border: 1px solid black; border-collapse: collapse">' . $row->precio_unitario . '</td>
                    </tr>';
        $d = $d + 1;
    }

    $salida .= '</tbody>
                </table>';

    // Encabezados para forzar la descarga como archivo Excel
    $timestamp = date('m/d/Y', time());
    $filename = 'QR_' . $timestamp . '.xls';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary'); 

    // Salida final del archivo
    echo $salida;
}

function buscarQrs()
{
    // Obtiene el valor ingresado para búsqueda del QR
    $qr = $this->input->post('txtBuscarQr');        

    // Consulta el ID y estatus de la requisición que coincide con el QR
    $query = "SELECT id, estatus FROM requisiciones_cotizacion where id = " . $qr;
    $res = $this->Conexion->consultar($query);

    // Devuelve los resultados en formato JSON si existen
    if ($res)
    {
        echo json_encode($res);    
    }
}

function ajax_setQr()
{
    // Recibe los datos enviados por POST
    $qr = $this->input->post('qr');
    $id_cotizacion = $this->input->post('id_cotizacion');        

    // Crea el arreglo de datos a actualizar
    $data = array('id_cotizacion' => $id_cotizacion);

    // Actualiza la tabla requisiciones_cotizacion con el ID de cotización
    $res = $this->Conexion->modificar('requisiciones_cotizacion', $data, null, array('id' => $qr));  

    // Devuelve 1 si la operación fue exitosa
    if ($res)
    {
        echo 1;    
    } 
}

function ajax_getQrs()
{
    // Recibe el ID de cotización desde POST
    $id_cotizacion = $this->input->post('id_cotizacion');        

    // Consulta los QRs asociados a la cotización
    $query = "SELECT id FROM requisiciones_cotizacion where id_cotizacion = " . $id_cotizacion;
    $res = $this->Conexion->consultar($query);

    // Devuelve los IDs en formato JSON si existen resultados
    if ($res)
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
