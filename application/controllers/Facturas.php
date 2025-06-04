<?php
require_once ('data/Merger/PDFMerger.php');
use PDFMerger\PDFMerger;

defined('BASEPATH') OR exit('No direct script access allowed');

class Facturas extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->library('correos_facturacion');
        $this->load->model('MLConexion_model', 'MLConexion');

    }

    function index(){
        $this->load->view('header');
        $this->load->view('facturas/catalogo_facturas');
    }

    function solicitudes(){
        $this->load->view('header');
        $this->load->view('facturas/catalogo_solicitudes');
    }

    function solicitud_factura(){
        $data["id"] = 0;

        $this->load->view('header');
        $this->load->view('facturas/solicitud_facturas', $data);
    }

    function editar_solicitud(){
        if(isset($_POST["id"]))
        {
            $id = $this->input->post('id');
        }
        else if ($id == 0){
            redirect(base_url('inicio'));
        }

        $data["id"] = $id;
        $data["editar"] = true;

        $this->load->view('header');
        $this->load->view('facturas/solicitud_facturas', $data);
    }

    function ver_solicitud($id = 0){

        if(isset($_POST["id"]))
        {
            $id = $this->input->post('id');

        }
        else if ($id == 0){
            redirect(base_url('inicio'));
        }

        $data["id"] = $id;

        $this->load->view('header');
        $this->load->view('facturas/solicitud_facturas', $data);
    }

    function documentos_globales(){
        $this->load->view('header');
        $this->load->view('facturas/documentos_globales');
    }

    function logistica(){
        $this->load->view('header');
        $this->load->view('facturas/logistica');
    }

    function programacion_recorrido(){
        $this->load->view('header');
        $this->load->view('facturas/programacion_recorrido');
    }

    /////////////////////////////

    function ajax_setSolicitud(){
        $solicitud = json_decode($this->input->post('solicitud'));
        $other = json_decode($this->input->post('other'));
        $rs_items = json_decode($this->input->post('rs_items'));
        //echo var_dump($solicitud);die();

        if(isset($_FILES['f_O']))
        {
            $solicitud->f_orden_compra = file_get_contents($_FILES['f_O']['tmp_name']);

        }
        if(isset($_FILES['f_R']))
        {
            $solicitud->f_remision = file_get_contents($_FILES['f_R']['tmp_name']);
        }
        $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');
        
        $res = false;
        if($solicitud->id == 0)
        {
            $solicitud->usuario = $this->session->id;
            $res = $this->Conexion->insertar('solicitudes_facturas', $solicitud, $funciones);
            $solicitud->id = $res;
        }
        else
        {
            $res = $this->Conexion->modificar('solicitudes_facturas', $solicitud, null, array('id' => $solicitud->id)) >= 0;
        }


        $this->load->model('MLConexion_model', 'MLConexion');
        foreach ($rs_items as $item) 
        {
            
            $borrar = $item->BORRAR;
            unset($item->BORRAR);
            if($item->id == 0){
                $rsitems=$this->MLConexion->consultar("SELECT rs.folio_id, rs.item_id,rs.Fec_CalibracionMT,s.DescripcionDeServicio, rs.HojadeDatos_Id FROM rsitems rs JOIN catalogo_servicios s on s.servicio_id=item_servicio_id WHERE rs.item_id=".$item->item_id, true);
                $item->servicio=$rsitems->DescripcionDeServicio;
                $item->HojadeDatos_Id=$rsitems->HojadeDatos_Id;
                $item->Fec_CalibracionMT=$rsitems->Fec_CalibracionMT;

                $item->id_factura = $solicitud->id;
                //echo var_dump($item);die();
                $this->Conexion->insertar('rsitems_facturas', $item);

                $this->MLConexion->modificar('rsitems', array('Solicitud_ID' => $item->id_factura), null, array('item_id' => $item->item_id));
            }
            else
            {
                if($borrar)
                {
                    $this->Conexion->eliminar('rsitems_facturas', array('id' => $item->id));
                    $this->MLConexion->modificar('rsitems', array('Solicitud_ID' => 0), null, array('item_id' => $item->item_id));
                }
                else
                {
                    $this->Conexion->modificar('rsitems_facturas', $item, null, array('id' => $item->id));
                }
            }
        }




        //ENVIAR CORREO
//        if(isset($_FILES['other'])){   
            $solicitud->User = $other->User;
            $solicitud->Client = $other->Client;
            $solicitud->Contact = $other->Contact;

            $correos = [];
            $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.responder_facturas = 1");
            foreach ($correos_a as $key => $value) {
                array_push($correos, $value->correo);
            }
            $solicitud->correos = array_merge(array($this->session->correo), $correos);

            $this->correos_facturacion->solicitud($solicitud);
  //      }


        if($res)
        {
            echo "1";
        }
    }

    function ajax_getSolicitudes(){
        $id = $this->input->post('id');
        $aceptadas = $this->input->post('aceptadas');
        $cliente = $this->input->post('cliente');
        $ejecutivo = $this->input->post('ejecutivo');
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');

        

        $query = "SELECT F.id, F.monto_factura, F.fecha, F.usuario, F.ejecutivo, F.cliente, F.contacto, F.reporte_servicio, F.orden_compra, F.forma_pago, F.pagada, F.urgente, F.conceptos, F.notas, F.estatus, F.documentos_requeridos, F.serie, F.folio, F.codigo_impresion, F.bitacora_estatus, E.nombre as Cliente, concat(U.nombre, ' ', U.paterno) as User from solicitudes_facturas F inner join empresas E on E.id = F.cliente inner join usuarios U on U.id = F.ejecutivo where 1 = 1";

        if($id)
        {
            $query .= " and F.id = '$id'";
        }
        else
        {
            if($texto)
            {
                $query .= " and F.$parametro = '$texto'";
            }
            if($aceptadas == 0)
            {
                $query .= " and (F.estatus != 'ACEPTADO' && F.estatus != 'CANCELADO')";
            }
            if($cliente && $cliente != 0)
            {
                $query .= " and F.cliente = '$cliente'";
            }
            if($ejecutivo && $ejecutivo != 0)
            {
                $query .= " and F.ejecutivo = '$ejecutivo'";
            }
            
        }
        
        if(isset($_POST['estatus']))
        {
            $estatus = $this->input->post('estatus');
            $query .= " and F.estatus = '$estatus'";
        }

        if(isset($_POST['logistica']))
        {
            $logistica = $this->input->post('logistica');
            $query .= " and F.logistica = '$logistica'";
        }

        $query .= " order by F.fecha desc";
//echo $query;die();
        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_editSolicitud(){

        $solicitud = json_decode($this->input->post('solicitud'));
        $other = json_decode($this->input->post('other'));
        //echo var_dump($solicitud);die();
        if(isset($_FILES['f_A']))
        {
            $solicitud->f_acuse = file_get_contents($_FILES['f_A']['tmp_name']);
        
        }
        if(isset($_FILES['f_F']))
        {
            $solicitud->f_factura = file_get_contents($_FILES['f_F']['tmp_name']);
            $solicitud->name_factura = $this->input->post('f_F_name');
            

        }
        if(isset($_FILES['f_X']))
        {
            $solicitud->f_xml = file_get_contents($_FILES['f_X']['tmp_name']);
            $solicitud->name_xml = $this->input->post('f_X_name');
            

        }

        $comentario = $this->input->post('comentario');

        $res = $this->Conexion->modificar('solicitudes_facturas', $solicitud, null, array('id' => $solicitud->id));
        if($solicitud->estatus_factura == "ACEPTADO")
        {
            
            $this->load->model('MLConexion_model', 'MLConexion');
            $this->MLConexion->comando("UPDATE rsitems set Factura = ifnull(Factura, $solicitud->folio) where Solicitud_ID = $solicitud->id;");
            
        }


        if($res > 0)
        {
            if(isset($_POST['comentario']) && !empty($comentario))
            {
                $this->Conexion->insertar('solicitudes_facturas_comentarios', array('solicitud' => $solicitud->id, 'usuario' => $this->session->id, 'comentario' => $comentario), array('fecha' => 'CURRENT_TIMESTAMP()'));
                $solicitud->comentario = $comentario;
                
            }

            $correos = [];
            $correos_a = $this->Conexion->consultar("SELECT U.correo from privilegios P inner join usuarios U on P.usuario = U.id where P.responder_facturas = 1");
            foreach ($correos_a as $key => $value) {
                array_push($correos, $value->correo);
            }
            $solicitud->correos = array_merge(array($this->session->correo), $correos);
            $solicitud->User = $other->User;
            $solicitud->Client = $other->Client;
            $solicitud->Contact = $other->Contact;
            $mail['id']=$solicitud->id;
            $mail['estatus_factura']=$solicitud->estatus_factura;
            $mail['comentario']=$solicitud->comentario;
            $mail['User']=$other->User;
            $mail['Client']=$other->Client;
            $mail['Contact']=$other->Contact;
            $mail['correos']=array_merge(array($this->session->correo), $correos);
            //echo var_dump($mail);die();

            
            $this->correos_facturacion->editar_solicitud($mail);
            echo "1";
        }
        else
        {
            echo "";
        }
    }

    function ajax_getReporteServicios(){

        $this->load->model('MLConexion_model', 'MLConexion');

        $texto = $this->input->post('texto');
        $rs = $this->input->post('rs');

        //$query = "SELECT item_id, folio_id, descripcion, Solicitud_ID, ifnull(Equipo_ID,'') as Equipo_ID,concat(' ID: ', Equipo_ID))) as CadenaDescripcion, ifnull(Fabricante,'') as Fabricante, ifnull(Modelo,'') as Modelo, ifnull(Serie,'') as Serie, Monto from rsitems where isnull(fechaCancelado)";
        $query = "SELECT item_id, folio_id, descripcion, concat(descripcion, if(isnull(Fabricante), '', concat(' ', Fabricante)), if(isnull(Modelo), '', concat(' ', Modelo)), if(isnull(Serie), '', concat(' Serie: ', Serie)), if(isnull(Equipo_ID), '', concat(' ID: ', Equipo_ID))) as CadenaDescripcion, Solicitud_ID, Monto from rsitems where 1=1 ";
        $query .= " and folio_id = '$rs'";
        if($texto)
        {
            $query .= " having CadenaDescripcion like '%$texto%'";
        }
//echo $query;die();
        $res = $this->MLConexion->Consultar($query);

        if($res){
            echo json_encode($res);
        }
    }

    function ajax_getRSItems(){
        $id_factura = $this->input->post('id_factura');
        $res = $this->Conexion->Consultar("SELECT * from rsitems_facturas where id_factura = $id_factura");
        if($res){
            echo json_encode($res);
        }
    }


    function ajax_setComentarios(){
        $comentario = json_decode($this->input->post('comentario'));
        $comentario->usuario = $this->session->id;
        $funciones = array('fecha' => 'CURRENT_TIMESTAMP()');
        

        $res = $this->Conexion->insertar('solicitudes_facturas_comentarios', $comentario, $funciones);
        if($res > 0)
        {
            echo "1";
        }
        else
        {
            echo "";
        }


    }

    function ajax_getComentarios(){
        $id = $this->input->post('id');

        $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from solicitudes_facturas_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

        if($id)
        {
            $query .= " and C.solicitud = '$id'";
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
//        ini_set('display_errors', 0);

//        $this->load->library('merger');
       // echo var_dump(base_url("application/libraries"));die();
//        require_once (base_url()."PDFMerger/PDFMerger.php');

//use PDFMerger\PDFMerger;
//$pdf = new PDFMerger;


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
        //echo $q;die();
        $res = $this->Conexion->consultar($q, TRUE);


        for ($i=0; $i < strlen($codigo); $i++) { 
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

            
            if($campo != null)
            {
                if(substr($campo, 0, 2 ) == "f_")
                {

                    $file = $res->$campo; 

                    $fichero = sys_get_temp_dir(). '/' . $campo . '.pdf';
                    file_put_contents($fichero, $file);
                   
                    $pdf->addPDF($fichero, 'all');
                }
                else
                {
                    $fichero = "data/empresas/documentos_globales/" . $campo . "_000001.pdf";
                    $pdf->addPDF($fichero, 'all');
                }
            }
        }
        

ob_end_clean();
       $pdf->merge('browser');
//        $pdfM->Output();
       
        
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
        
        $query = "SELECT E.id, E.nombre, count(S.id) as NumSol from solicitudes_facturas S inner join empresas E on E.id = S.cliente";

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

    function ajax_getEjecutivosSolicitudes(){
        $texto = $this->input->post('texto');
        
        $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Ejecutivo, count(S.id) as NumSol from solicitudes_facturas S inner join usuarios U on U.id = S.ejecutivo";

        if($texto)
        {
            $query .= " where concat(U.nombre, ' ', U.paterno) like '%$texto%'";
        }
        $query .= " group by U.id;";

        $res = $this->Conexion->consultar($query);

        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getDocumentosGlobales(){
        
        $query = "SELECT id, opinion_positiva, emision_sua from documentos_globales where id = 1";

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

    function ajax_filesExists($id){
        $this->load->helper('file');

        $id = str_pad($id, 6, "0", STR_PAD_LEFT);
        $acuse = read_file(base_url("data/empresas/documentos_facturacion/ACUSE_" . $id . ".pdf")) ? "1" : "0";
        $emision = read_file(base_url("data/empresas/documentos_facturacion/EMISION_" . $id . ".pdf")) ? "1" : "0";

        echo json_encode(array($acuse, $emision));
    }

    function ajax_readXML(){
        $dom = new DomDocument;
        $dom->preserveWhiteSpace = FALSE;
        $dom->loadXML(file_get_contents($_FILES['f_X']['tmp_name']));
      //$dom->loadXML(file_get_contents(base_url('data/1.xml')));
        $comp = $dom->getElementsByTagName('Comprobante');
        $ext = 1;
        $data = array();
        foreach ($comp[0]->attributes as $elem)
        {
            if($elem->name == "Serie" | $elem->name == "Folio" | $elem->name == "SubTotal")
            {
                $e = array($elem->name => $elem->value);
                array_push($data, $e);
            }


            if($elem->name == "Folio")
            {
                $folio = $elem->value;

                $res = $this->Conexion->consultar("SELECT count(*) as existe FROM solicitudes_facturas where folio = '$folio'", TRUE);
                $ext = $res->existe;
            }

            
        }

        if($ext == 0)
        {
            echo json_encode($data);
        }
        else
        {
            echo "0";
        }
        
    }

    function ajax_setDocumentoFacturacion(){
        $file = $this->input->post('file');
        $documento = $this->input->post('documento');
        $id = $this->input->post('empresa');
        $id = str_pad($id, 6, "0", STR_PAD_LEFT);
        
        if($file != "undefined")
        {
            $config['upload_path'] = 'data/empresas/documentos_facturacion/';
            $config['allowed_types'] = 'pdf';
            $config['overwrite'] = TRUE;
            $config['file_name'] = $documento . '_' . $id;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file'))
            {
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
                $data[$campo] = $this->upload->data('file_name');
                $this->Conexion->modificar('empresas', $data, null, $where);
                echo "1";
            } 
        }
    }

    function ajax_deleteDocumentoFacturacion(){
        $documento = $this->input->post('documento');
        $id = $this->input->post('empresa');
        $id = str_pad($id, 6, "0", STR_PAD_LEFT);
        unlink('data/empresas/documentos_facturacion/' . $documento . '_' . $id . '.pdf');

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
        $data[$campo] = "";
        $this->Conexion->modificar('empresas', $data, null, $where);
    }

    function ajax_setDocumentoGlobal(){
        $file = $this->input->post('file');
        $documento = $this->input->post('documento');
        $id = $this->input->post('empresa');
        $id = str_pad($id, 6, "0", STR_PAD_LEFT);
        
        if($file != "undefined")
        {
            $config['upload_path'] = 'data/empresas/documentos_globales/';
            $config['allowed_types'] = 'pdf';
            $config['overwrite'] = TRUE;
            $config['file_name'] = $documento . '_' . $id;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file'))
            {
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
                $data[$campo] = $this->upload->data('file_name');
                $this->Conexion->modificar('documentos_globales', $data, null, $where);
                echo "1";
            } 
        }
    }

    function ajax_deleteDocumentoGlobal(){
        $documento = $this->input->post('documento');
        $id = $this->input->post('empresa');
        $id = str_pad($id, 6, "0", STR_PAD_LEFT);
        unlink('data/empresas/documentos_globales/' . $documento . '_' . $id . '.pdf');

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
        $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);
        foreach ($campos as $value) {
            if(substr($value, 0, 2 ) == "f_")
            {
                $file = $res->$value;
                $fichero = sys_get_temp_dir(). '/' . $value . ($value == "f_xml" ? '.xml' : '.pdf');
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

        $datos['id'] = $id;
        $datos['para'] = $para;
        $datos['cc'] = $cc;
        $datos['subject'] = $subject;
        $datos['body'] = $body;
        $datos['campos'] = $campos;
        $datos['archivos'] = $archivos;
        $this->correos_facturacion->enviarCorreo($datos);

    }

    ////////////////////////////////// F A C T U R A S //////////////////////////////////

    function ajax_getFacturas(){
        $id = $this->input->post('id');

        $query = "SELECT F.id, F.fecha, F.usuario, F.cliente, F.contacto, F.reporte_servicio, F.orden_compra, F.forma_pago, F.pagada, F.conceptos, F.notas, F.estatus_factura, F.documentos_requeridos, F.serie, F.folio, F.codigo_impresion, (SELECT count(id) from recorrido_conceptos where id_concepto = F.id and tipo = 'FACTURA') as Recorridos, (SELECT count(id) from envios_factura where factura = F.id) as Envios, E.nombre as Cliente, concat(U.nombre, ' ', U.paterno) as User, U.correo, ifnull(EC.correo, 'N/A') as CorreoContacto from solicitudes_facturas F inner join empresas E on E.id = F.cliente inner join usuarios U on U.id = F.usuario left join empresas_contactos EC on EC.id = F.contacto";
        $query .= " where F.folio > 0 and F.estatus = 'ACEPTADO'";

        if($id)
        {
            $query .= " and F.id = '$id'";
        }
        
        if(isset($_POST['estatus']))
        {
            $estatus = $this->input->post('estatus');
            $query .= " and F.estatus = '$estatus'";
        }

        $res = $this->Conexion->consultar($query, $id);
        if($res)
        {
            echo json_encode($res);
        }
    }


    ////////////////////////////////// L O G I S T I C A //////////////////////////////////
    function ajax_getMensajeros(){
        $query = "SELECT U.id, U.nombre, U.paterno, U.materno, U.no_empleado, U.puesto, U.correo, U.ultima_sesion, U.departamento, U.activo, U.jefe_directo, U.autorizador_compras, U.autorizador_compras_venta, concat(U.nombre, ' ', U.paterno) as User, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as CompleteName from usuarios U inner join privilegios P on P.usuario = U.id where U.activo = '1'";// having Name like '%$texto%'";
        $query .= " and P.mensajero = '1'";
        $query .= " order by User";
        
        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }

    }

    function ajax_setRecorrido(){
        $mensajero = $this->input->post('mensajero');
        $fecha = $this->input->post('fecha');
        $recorrido = json_decode($this->input->post('recorrido'));

        $data['mensajero'] = $mensajero;
        $data['fecha_recorrido'] = $fecha;
        $recorrido_id = $this->Conexion->insertar('recorridos', $data);

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

        $query = "SELECT RF.*, R.mensajero, R.fecha_recorrido, (SELECT count(RC.id) from recorrido_comentarios RC where RC.recorrido_factura = RF.id) as Comentarios, (SELECT count(RF2.id) from recorrido_facturas RF2 where RF2.recorrido = RF.recorrido and RF2.estatus = 'EN RECORRIDO') as Pendientes, (SELECT E.nombre from solicitudes_facturas F inner join empresas E on E.id = F.cliente where F.id = RF.factura) as Cliente, ifnull(concat(M.nombre, ' ', M.paterno), 'N/A') as Mensajero from recorrido_facturas RF inner join recorridos R on R.id = RF.recorrido left join usuarios M on M.id = R.mensajero where 1 = 1";

        if(isset($_POST['factura']))
        {
            $query .= " and RF.factura = $factura";
        }

        if($pendientes == "1")
        {
            $query .= " having Pendientes > 0";
        }

        $query .= " order by R.fecha_recorrido, R.id, RF.id asc";

        $res = $this->Conexion->consultar($query);

        echo json_encode($res);
    }

    function ajax_updateRecorrido(){
        $recorrido = json_decode($this->input->post('recorrido'));
        $recolecta = $this->input->post('recolecta');
        $comentario = $this->input->post('comentario');
        
        $this->Conexion->modificar('recorrido_facturas', $recorrido, null, array('id' => $recorrido->id));

        if(substr($recorrido->estatus, 0, 2) == "NO")
        {
            $estat = "PENDIENTE " . $recorrido->accion;
            $this->Conexion->modificar('solicitudes_facturas', array('estatus' => $estat), null, array('id' => $recorrido->factura));
        }
        else
        {
            $estat = $recolecta == "1" ? "PENDIENTE RECOLECTA" : "CERRADO";
            $this->Conexion->modificar('solicitudes_facturas', array('estatus' => $estat), null, array('id' => $recorrido->factura));
        }

        if($comentario)
        {
            $color = substr($recorrido->estatus, 0, 2) == "NO" ? "red" : "green";
            $comentario = '<font color=' . $color . '><b>' . $recorrido->estatus . ':</b></font> ' . $comentario;

            $data_com['recorrido_factura'] = $recorrido->id;
            $data_com['usuario'] = $this->session->id;
            $data_com['comentario'] = $comentario;
            $func_com['fecha'] = "CURRENT_TIMESTAMP()";
            $this->Conexion->insertar('recorrido_comentarios', $data_com, $func_com);
        }
        
        
    }

    function ajax_getComentariosRecorrido(){
        $id = $this->input->post('id');

        $query = "SELECT C.*, concat(U.nombre, ' ', U.paterno) as User from recorrido_comentarios C inner join usuarios U on U.id = C.usuario where 1 = 1";

        if($id)
        {
            $query .= " and C.recorrido_factura = '$id'";
        }
        $query .= " order by C.fecha";

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }


    }
    function ver_POD($id){
        //$query = "SELECT sf.id,sf.folio, sf.reporte_servicio, sf.fecha, sf.reporte_servicio, sf.orden_compra, concat(u.nombre, ' ',u.paterno) as responsable, e.razon_social, ec.nombre as contacto, ec.correo, concat(e.calle, ' ',e.numero, ' CP ',e.cp) as direccion,e.ciudad, e.estado, e.rfc,e.colonia FROM `solicitudes_facturas` sf JOIN usuarios u on u.id = sf.ejecutivo join empresas e on e.id = sf.cliente join empresas_contactos ec on ec.id = sf.cliente WHERE sf.id= $id";
//echo $query;
        $query = "SELECT sf.id, sf.folio,sf.serie, sf.reporte_servicio, sf.fecha, sf.orden_compra, concat(u.nombre, ' ', u.paterno) as responsable, e.razon_social, concat(e.calle, ' ',e.numero, ' CP ',e.cp) as direccion,e.ciudad, e.estado, e.rfc,e.colonia, ec.nombre as contacto, ec.correo FROM solicitudes_facturas sf join usuarios u on sf.ejecutivo = u.id JOIN empresas e on sf.cliente = e.id JOIN empresas_contactos ec on sf.contacto = ec.id WHERE sf.id =$id";
        $factura = $this->Conexion->consultar($query, TRUE);

  //      $query2 = "SELECT * FROM rsitems_facturas WHERE id_factura =".$id;
$query2 = "SELECT rs.descripcion, rs.Equipo_ID, rs.Fec_CalibracionMT, s.DescripcionDeServicio, concat(rs.descripcion,if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)) ) as CadenaDescripcion from rsitems rs JOIN catalogo_servicios s on s.servicio_id = rs.item_servicio_id WHERE rs.Solicitud_ID =".$id." and rs.Factura = '".$factura->folio."'";

        $rs = $this->MLConexion->consultar($query2);
        $total=0;
        foreach($rs as $row){
            $total++;
        }
        ini_set('display_errors', 0);
        $this->load->library('pdfview');

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

        $pdf->SetFont('helvetica', '', 8);

        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 10);
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
$pdf->Output(sys_get_temp_dir().'/'.$name, 'I');
    }
function ajax_enviarCorreoPOD(){
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
        $res = $this->Conexion->consultar("SELECT SF.* from solicitudes_facturas SF where SF.id = $id", TRUE);
        $pdfname=$res->name_factura;
        $xmlname=$res->name_xml;
         //echo var_dump(file_put_contents($_FILES[$res->f_factura]['name']));die();
        foreach ($campos as $value) {

            if(substr($value, 0, 2 ) == "f_")
            {
                $file = $res->$value;

                $fichero = sys_get_temp_dir(). '/' . $value . ($value == "f_xml" ? '.xml' : '.pdf');
                $name =$value == "f_xml" ? $res->name_xml : $res->name_factura;
               // echo var_dump($fichero);die();
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

        $query = "SELECT sf.id, sf.folio,sf.serie, sf.reporte_servicio, sf.fecha, sf.orden_compra, concat(u.nombre, ' ', u.paterno) as responsable, e.razon_social, concat(e.calle, ' ',e.numero, ' CP ',e.cp) as direccion,e.ciudad, e.estado, e.rfc,e.colonia, ec.nombre as contacto, ec.correo FROM solicitudes_facturas sf join usuarios u on sf.ejecutivo = u.id JOIN empresas e on sf.cliente = e.id JOIN empresas_contactos ec on sf.contacto = ec.id WHERE sf.id =$id";

        $factura = $this->Conexion->consultar($query, TRUE);

        $query2 = "SELECT rs.descripcion, rs.Equipo_ID, rs.documento_id, rs.Fec_CalibracionMT, s.DescripcionDeServicio, concat(rs.descripcion,if(isnull(rs.Fabricante), '', concat(' ', rs.Fabricante)), if(isnull(rs.Modelo), '', concat(' ', rs.Modelo)), if(isnull(rs.Serie), '', concat(' Serie: ', rs.Serie)) ) as CadenaDescripcion from rsitems rs JOIN catalogo_servicios s on s.servicio_id = rs.item_servicio_id WHERE rs.Solicitud_ID =".$id." and rs.Factura = '".$factura->folio."'";
        //echo $query2;die();
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

        $pdf->SetFont('helvetica', '', 8);

        $pdf->AddPage();
        $pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('helvetica', '', 10);
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
//echo var_dump($certificados);die();
        $this->correos_facturacion->archivos_facturacion($datos);

    }
    public function validar_archivos(){
        $id=$this->input->post('ID');
        $factura = $this->Conexion->consultar("SELECT id, folio  from solicitudes_facturas where id= ".$id, true);

        $query = " select item_id, documento_id,Fec_CalibracionMT from rsitems where factura = ".$factura->folio." and Solicitud_ID";
        //echo $query;die()
        $rs = $this->MLConexion->consultar($query);
        
        if($rs)
        {
            echo json_encode($rs);
            //echo $query;
        }
        else{
            echo "";
        }
    }



}
