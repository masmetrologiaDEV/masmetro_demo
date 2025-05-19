<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Empresas extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('empresas_model','Modelo');
        $this->db->db_debug = FALSE;
    }

    function index() {
        //$this->output->enable_profiler(TRUE);
        //$datos['empresas'] = $this->Modelo->getEmpresas();
        $this->load->view('header');
        $this->load->view('empresas/catalogo');
    }

    function alta() {
        $datos['paises'] = $this->Modelo->listadopaises();
        $this->load->view('header');
        $this->load->view('empresas/alta',$datos);
    }

    function ver($id){
      $datos['empresa'] = $this->Modelo->getEmpresa($id);
      $datos['proveedor'] = $this->Modelo->getProveedor($id);
      $datos['contactos'] = $this->Modelo->getContactos($id);
      $datos['archivos'] = $this->Modelo->getArchivos($id);
      $datos['requisitos'] = $this->Modelo->getRequisitos_empresa($id);
      $datos['paises'] = $this->Modelo->listadopaises();
      $datos['documento'] = $this->Modelo->documento();
      
      $this->load->view('header');
      $this->load->view('empresas/ver', $datos);
    }

    function registrar() {
        $ACIERTOS = array(); $ERRORES = array();

        $data = array(
            'nombre' => trim($this->input->post('nombre')),
            'nombre_corto' => trim($this->input->post('nombre_corto')),
            'razon_social' => trim($this->input->post('razon_social')),
            'giro' => trim($this->input->post('giro')),
            'clasificacion' => trim($this->input->post('clasificacion')),
            'rfc' => trim($this->input->post('rfc')),
            'calle' => trim($this->input->post('calle')),
            'numero' => trim($this->input->post('numero')),
            'numero_interior' => trim($this->input->post('numero_interior')),
            'colonia' => trim($this->input->post('colonia')),
            'calles_aux' => trim($this->input->post('calles_aux')),
            'pais' => trim($this->input->post('pais')),
            'estado' => trim($this->input->post('estado')),
            'ciudad' => trim($this->input->post('ciudad')),
            'cp' => trim($this->input->post('cp')),
            'foto' => 'default.png',
            'ubicacion' => '',
            'cliente' => $this->input->post('cliente') != NULL ? '1' : '0',
            'proveedor' => $this->input->post('proveedor') != NULL ? '1' : '0',
            'credito_cliente' => '0',
            'credito_cliente_plazo' => '0',
            'documentos_facturacion' => '[]',
            'codigo_impresion' => '',
            'comentarios' => '',
            'moneda_cotizacion' => '[]',
            'iva_cotizacion' => '[]',
            'notas_cotizacion' => '',            
            'contacto_cotizacion' => '[]',
            'requisitos_logisticos' => '',
            'requisitos_documento' => '',
            'factura_ejemplo' => '',
            'dejar_factura' => 0,
            'prospecto' => $this->input->post('prospecto') != NULL ? '1' : '0',
        );

        if ($this->Modelo->crear_empresa($data)) {
            $acierto = array('titulo' => 'Agregar Empresa', 'detalle' => 'Se ha agregado Empresa con Éxito');
            array_push($ACIERTOS, $acierto);
            $res = $this->Conexion->consultar('SELECT MAX(id) as id FROM empresas', TRUE);
            $data = array(
                'id_empresa'=>$res->id,
                'user'=>$this->session->id,
                'estatus'=>'ALTA',
            );
            $this->Modelo->bitacoraEmpresas($data);

            //echo var_dump($res->id);die();
        } else {
            $error = array('titulo' => 'ERROR', 'detalle' => 'Error al agregar Empresa');
            array_push($ERRORES, $error);
        }
        $this->session->aciertos = $ACIERTOS;
        $this->session->errores = $ERRORES;
        redirect(base_url('empresas'));
    }

    function editar() {
        $ACIERTOS = array(); $ERRORES = array();
        $id = $this->input->post('id');
        $data = array(
            'id' => trim($id),
            'nombre' => trim($this->input->post('nombre')),
            'giro' => trim($this->input->post('giro')),
            'razon_social' => trim($this->input->post('razon_social')),
            'rfc' => trim($this->input->post('rfc')),
            'calle' => trim($this->input->post('calle')),
            'numero' => trim($this->input->post('numero')),
            'numero_interior' => trim($this->input->post('numero_interior')),
            'colonia' => trim($this->input->post('colonia')),
            'calles_aux' => trim($this->input->post('calles_aux')),
            'cp' => trim($this->input->post('cp')),
            'pais' => trim($this->input->post('pais')),
            'estado' => trim($this->input->post('estado')),
            'ciudad' => trim($this->input->post('ciudad')),
            'ubicacion' => '',
            'cliente' => $this->input->post('cliente') != NULL ? '1' : '0',
            'proveedor' => $this->input->post('proveedor') != NULL ? '1' : '0',
            'prospecto' => $this->input->post('prospecto') != NULL ? '1' : '0',
        );

        if ($this->Modelo->update($data)) {
            $acierto = array('titulo' => 'Editar Empresa', 'detalle' => 'Se ha editado Empresa con Éxito');
            array_push($ACIERTOS, $acierto);
            $data = array(
                'id_empresa'=>trim($id),
                'user'=>$this->session->id,
                'estatus'=>'Editar Empresa',
            );
            $this->Modelo->bitacoraEmpresas($data);
        } else {
            $error = array('titulo' => 'ERROR', 'detalle' => 'Error al editar Empresa');
            array_push($ERRORES, $error);
        }
        $this->session->aciertos = $ACIERTOS;
        $this->session->errores = $ERRORES;
        redirect(base_url('empresas/ver/' . $id ));
    }
    function paises() {
        //$this->output->enable_profiler(TRUE);
        //$datos['empresas'] = $this->Modelo->getEmpresas();
        $this->load->view('header');
        $this->load->view('empresas/catalogo_paises');
    }

    function ajax_getEmpresas(){
        //$this->output->enable_profiler(TRUE);
        $texto = $this->input->post('texto');
        $texto = trim($texto);
        $parametro = $this->input->post('parametro');
        $cliente = $this->input->post('cliente');
        $proveedor = $this->input->post('proveedor');

        $query = "SELECT E.* from empresas E JOIN proveedores P on E.id=P.empresa where 1=1 ";

        

        if($cliente != $proveedor)
        {
            if($cliente == "1")
            {
                $query .= " and E.cliente = '1'";
            }
            else
            {
                $query .= " and E.proveedor = '1'";
            }
        }

        if(!empty($texto))
        {
            if($parametro == "nombre")
            {
                $query .= " and (E.nombre like '%$texto%' or E.razon_social like '%$texto%')";
            }
            else if($parametro == "id")
            {
                $query .= " and E.id = '$texto'";
            }
            else if($parametro == "tag")
            {
                $query .= " and P.tags like '%$texto%'";
            }
        }


        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
        }
    }

    function ajax_getContactos(){
        $id = $this->input->post('id');
        $where['empresa'] = $id;

        $res = $this->Conexion->get('empresas_contactos', $where);
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function ajax_getContacto(){
        $id = $this->input->post('id');

        $res = $this->Conexion->consultar("SELECT EC.*, ifnull(Pl.nombre,'NO DEFINIDO') as Planta from empresas_contactos EC left join empresa_plantas Pl on Pl.id = EC.planta where EC.id = $id", TRUE);
        if($res)
        {
            echo json_encode($res);
        }
    }

    function ajax_getArchivos(){
        $empresa = $this->input->post('empresa');
        $texto = $this->input->post('texto');

        $query = "SELECT A.*, concat(U.nombre,' ',U.paterno) as User, D.tipo, D.clave from empresas_archivos A inner join usuarios U on U.id = A.usuario  left join documentosEmpresas D on D.id = A.id_documento where A.empresa = $empresa";

        if($texto){
            $query .= " and (A.nombre like '%$texto%' or A.comentarios like '%$texto%')";
        }

        $res = $this->Conexion->consultar($query);
        if($res){
            echo json_encode($res);
        }
    }

    function ajax_getFacturaEjemplo(){
        $empresa = $this->input->post('empresa');

        $query = "SELECT factura_ejemplo from empresas where id = $empresa";

        $res = $this->Conexion->consultar($query, TRUE);
        echo $res->factura_ejemplo;
    }

    function ajax_setFacturaEjemplo(){
        $id = $this->input->post('empresa');
        $file = $this->input->post('file');

        if($file != "undefined")
        {
            $config['upload_path'] = 'data/empresas/ejemplo_facturas/';
            $config['allowed_types'] = 'pdf';
            $config['encrypt_name'] = TRUE;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('file'))
            {
                $data['factura_ejemplo'] = $this->upload->data('file_name');
                $this->Conexion->modificar('empresas', $data, null, array('id' => $id));
            }
        }
    }

    function ajax_deleteFacturaEjemplo(){
        $id = $this->input->post('empresa');

        $res = $this->Conexion->consultar("SELECT factura_ejemplo from empresas where id = $id", TRUE);
        unlink('data/empresas/ejemplo_facturas/' . $res->factura_ejemplo);

        $data['factura_ejemplo'] = "";
        $this->Conexion->modificar('empresas', $data, null, array('id' => $id));
    }

    function editar_otros_datos() {
        $ACIERTOS = array(); $ERRORES = array();
        $id = $this->input->post('id');
        $data = array(
            'id' => $id,
            'horario_facturas' => trim($this->input->post('horario_facturas')),
            'ultimo_dia_facturas' => trim($this->input->post('ultimo_dia_facturas')),
            'requisitos_logisticos' => trim($this->input->post('requisitos_logisticos')),
            'requisitos_documento' => trim($this->input->post('requisitos_documento')),
            'comentarios' => trim($this->input->post('comentarios')),
            'dejar_factura' => $this->input->post('dejar_factura') == '1' ? '1' : '0',
        );

        if ($this->Modelo->update($data)) {
            $acierto = array('titulo' => 'Editar Empresa', 'detalle' => 'Se ha editado Empresa con Éxito');
            array_push($ACIERTOS, $acierto);
        } else {
            $error = array('titulo' => 'ERROR', 'detalle' => 'Error al editar Empresa');
            array_push($ERRORES, $error);
        }
        $this->session->aciertos = $ACIERTOS;
        $this->session->errores = $ERRORES;
        redirect(base_url('empresas/ver/' . $id ));
    }

    //CONTACTOS
    function agregarContacto() {
        $empresa = $this->input->post('empresa');
        $data = array(
            'empresa' => trim($empresa),
            'nombre' => trim($this->input->post('nombre')),
            'telefono' => trim($this->input->post('telefono')),
            'ext' => trim($this->input->post('ext')),
            'celular' => trim($this->input->post('celular')),
            'celular2' => trim($this->input->post('celular2')),
            'correo' => trim($this->input->post('correo')),
            'puesto' => trim($this->input->post('puesto')),
            'red_social' => trim($this->input->post('red_social')),
            'activo' => $this->input->post('activo'),
            'cotizable' => $this->input->post('cotizable'),
            'planta' => $this->input->post('planta'),
        );

        $res = $this->Modelo->insertContacto($data);
        $r = $this->Conexion->consultar('SELECT * FROM empresas_contactos ORDER BY id DESC LIMIT 1', TRUE);
            $data = array(
                'id_contacto'=>$r->id,
                'id_empresa'=>$r->empresa,
                'user'=>$this->session->id,
                'estatus'=>'ALTA',
            );
            $this->Modelo->bitacoraContactosEmpresas($data);
        if ($res) {
            $res = json_encode($this->Modelo->getContacto($res));

        }else {
            $res = "";
        }

        echo $res;
        //redirect(base_url('empresas/ver/' . $empresa . "#tab_content2" ));
    }

    function editarContacto() {
      $id = $this->input->post('id');
      $data = array(
          'id' => trim($id),
          'nombre' => trim($this->input->post('nombre')),
          'telefono' => trim($this->input->post('telefono')),
          'ext' => trim($this->input->post('ext')),
          'celular' => trim($this->input->post('celular')),
          'celular2' => trim($this->input->post('celular2')),
          'correo' => trim($this->input->post('correo')),
          'puesto' => trim($this->input->post('puesto')),
          'red_social' => trim($this->input->post('red_social')),
          'activo' => $this->input->post('activo'),
          'cotizable' => $this->input->post('cotizable'),
          'planta' => $this->input->post('planta'),
      );

    $r = $this->Conexion->consultar('SELECT * FROM empresas_contactos where id = '.trim($id), TRUE);
    $dataBit = array(
        'id_contacto'=>trim($id),
        'id_empresa'=>$r->empresa,
        'user'=>$this->session->id,
        'estatus'=>'Editar Contacto',
    );

    $this->Modelo->bitacoraContactosEmpresas($dataBit);

      if ($this->Modelo->updateContacto($data)) {
          $res = json_encode($this->Modelo->getContacto($id));
      }else {
          $res = "";
      }

      echo $res;
    }

    function getContacto_json(){
      $contact = $this->Modelo->getContacto($this->input->post('id'));
      if($contact){
          echo json_encode($contact);
      } else {
          echo "";
      }
    }

    function deleteContacto_json(){
      if($this->Modelo->deleteContacto($this->input->post('id'))){
          echo "1";
      } else {
          echo ""; //jQuery interpreta como FALSE (no 0)
      }
    }

    function subir_foto() {
        
        $id = $this->input->post('id_empresa');
        $foto = $this->input->post('fotoActual');
        $file = $this->input->post('iptFoto');

        if($file != "undefined")
        {
            $config['upload_path'] = 'data/empresas/fotos/';
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
    }

    function subir_archivo() {
        $id = $this->input->post('id');
        $comentarios = $this->input->post('comentarios');
        $documento = $this->input->post('documento');

        if (!is_dir('data/empresas/archivos/' . $id)) {
            mkdir('data/empresas/archivos/' . $id, 0777, TRUE);
        }

        $config['upload_path'] = 'data/empresas/archivos/' . $id;
        $config['allowed_types'] = '*';

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('userfile'))
        {
            $data['empresa'] = $id;
            $data['usuario'] = $this->session->id;
            $data['nombre'] = $this->upload->data('file_name');
            $data['comentarios'] = $comentarios;
            $data['id_documento'] = $documento;
            $idFile = $this->Modelo->insertArchivo($data);
            if($idFile)
            {
                $arreglo['id'] = $idFile;
                $arreglo['nombre'] = $data['nombre'];
                $arreglo['fecha'] = date('d/m/Y h:i A');
                $arreglo['icono'] = $this->aos_funciones->file_image($data['nombre']);
                $arreglo['error'] = '';
                echo json_encode($arreglo);
            }
        } else {
                $arreglo['id'] = '0';
                $arreglo['nombre'] = 'ERROR';
                $arreglo['fecha'] = 'ERROR';
                $arreglo['icono'] = $this->aos_funciones->file_image($data['nombre']);
                $arreglo['error'] = $this->upload->display_errors();
                echo json_encode($arreglo);
        }
    }

    function deleteArchivo_json(){
        $id_file = $this->input->post('id');
        $id_empresa = $this->input->post('id_empresa');
        $nombre = $this->input->post('nombre_archivo');
        if($this->Modelo->deleteArchivo($id_file)){
            unlink('data/empresas/archivos/' . $id_empresa . '/' . $nombre);
            echo "1";
        } else {
            echo ""; //jQuery interpreta como FALSE (no 0)
        }
      }

      function editArchivo_json(){
        $data['id'] = $this->input->post('id');
        $data['comentarios'] = trim($this->input->post('comentarios'));
        $data['id_documento'] = $this->input->post('documento');

        if($this->Modelo->updateArchivo($data)){
            echo $data['comentarios'];
        } else {
            echo ""; //jQuery interpreta como FALSE (no 0)
        }
      }

      //////// REQUISITOS DE FACTURACION ////////////

      function requisitos(){
        $data['requisitos'] = $this->Modelo->getRequisitos('');
        $this->load->view('header');
        $this->load->view('empresas/catalogo_requisitos', $data);
      }

      function getRequisitos_json(){
        $tipo = $this->input->post('tipo');
        $requisitos = $this->Modelo->getRequisitos($tipo);
        if($requisitos){
            echo json_encode($requisitos->result());
        } else {
            echo "";
        }
      }

      function setRequisitos_json(){
        $data['empresa'] = $this->input->post('id_empresa');
        $data['requisito'] = $this->input->post('requisito');
        $data['tipo'] = $this->input->post('tipo');
        $data['detalles'] = $this->input->post('detalles');
        $id = $this->Modelo->setRequisitos($data);
        if($id) {
            $res['id'] = $id;
            $res['requisito'] = $data['requisito'];
            $res['detalles'] = $data['detalles'];
            echo json_encode($res);
        } else {
            echo "";
        }
      }

      function deleteRequisito_json(){
        if($this->Modelo->deleteRequisito($this->input->post('id'))){
            echo "1";
        } else {
            echo ""; //jQuery interpreta como FALSE (no 0)
        }
      }

      function agregarRequisito_ajax() {
        $data = array(
            'requisito' => strtoupper(trim($this->input->post('requisito'))),
            'tipo' => strtoupper(trim($this->input->post('tipo'))),
            'detalle' => $this->input->post('detalle'),
        );

        $res = $this->Modelo->insertRequisito($data);
        if ($res) 
        {
            $data['id'] = $res;
            echo json_encode($data);
        }else {
            echo "";
        }
    }

    function editarRequisito_ajax() {
        $data = array(
            'id' => strtoupper(trim($this->input->post('id'))),
            'requisito' => strtoupper(trim($this->input->post('requisito'))),
            'tipo' => strtoupper(trim($this->input->post('tipo'))),
            'detalle' => $this->input->post('detalle'),
        );

        $res = $this->Modelo->updateRequisito($data);
        if ($res) 
        {
            echo json_encode($data);
        } else {
            echo "";
        }
    }

    function eliminarRequisito_ajax() {
        $data = array(
            'id' => trim($this->input->post('id')),
        );

        $res = $this->Modelo->deleteRequisitoCatalogo($data);
        if ($res) 
        {
            echo "1";
        } else {
            echo "";
        }
    }

    function editarUbicacion_ajax() {
        $data = array(
            'id' => $this->input->post('id'),
            'lat' => $this->input->post('lat'),
            'lng' => $this->input->post('lng'),
            'zoom' => $this->input->post('zoom'),
        );

        $res = $this->Modelo->update($data);
        if ($res) 
        {
            echo "1";
        } else {
            echo "";
        }
    }

    /* BORRAR function getLugaresEntrega_ajax(){
        $res = $this->Modelo->getLugaresEntrega();
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }*/

    /* BORRAR function setEntrega_ajax()
    {
        $data = array(
            'empresa' => $this->input->post('id_empresa'),
            'entrega' => $this->input->post('lugar'),
        );

        $res = $this->Modelo->setLugaresEntrega($data);

        if ($res) 
        {
            //$lugares = json_decode($this->input->post('lugares'));
            $lugares = $this->input->post('lugares');
            $lugares = json_decode($lugares, TRUE);
            array_push($lugares, $data['entrega']);
            echo json_encode($lugares);
            //array_push($lugares, $data['entrega']);
            //echo json_decode($lugares);
        } else {
            echo "";
        }
    }*/

    /*function getFormasCompra_ajax()
    {
        $res = $this->Modelo->getFormasCompra();
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function setFormasCompra_ajax()
    {
        $data = array(
            'empresa' => $this->input->post('id_empresa'),
            'forma' => $this->input->post('forma'),
        );

        $res = $this->Modelo->setFormasCompra($data);

        if ($res) 
        {
            $formas = $this->input->post('formas');
            $formas = json_decode($formas, TRUE);
            array_push($formas, $data['forma']);
            echo json_encode($formas);
        } else {
            echo "";
        }
    }

    function getMetodosPago_ajax(){
        $res = $this->Modelo->getMetodosPago();
        if($res)
        {
            echo json_encode($res);
        }
        else
        {
            echo "";
        }
    }

    function setMetodosPago_ajax()
    {
        $data = array(
            'empresa' => $this->input->post('id_empresa'),
            'forma' => $this->input->post('metodo'),
        );

        $res = $this->Modelo->setMetodosPago($data);

        if ($res) 
        {
            $metodos = $this->input->post('metodos');
            $metodos = json_decode($metodos, TRUE);
            array_push($metodos, $data['forma']);
            echo json_encode($metodos);
        } else {
            echo "";
        }
    }*/

    function ajax_setListadoDocumentos(){
        $where['id'] = $this->input->post('id');
        $data['documentos_facturacion'] = $this->input->post('documentos');
        $data['codigo_impresion'] = strtoupper($this->input->post('codigo'));

        $this->Conexion->modificar('empresas', $data, null, $where);
        echo "1";
    }

    function ajax_setProveedor(){
     //   $this->output->enable_profiler(TRUE);
        $data = array(
            'empresa' => $this->input->post('id_empresa'),
            'aprobado' => $this->input->post('aprobado'),
            'clasificacion_proveedor' => $this->input->post('clasificacion_proveedor'),
            'tipo' => $this->input->post('tipo'),
            'credito' => $this->input->post('credito'),
            'monto_credito' => $this->input->post('monto_credito'),
            'moneda_credito' => $this->input->post('moneda_credito'),
            'terminos_pago' => $this->input->post('terminos_pago'),
            'tags' => "," . $this->input->post('tags') . ",",
            'formas_pago' => $this->input->post('formas_pago'),
            'formas_compra' => $this->input->post('formas_compra'),
            'entrega' => $this->input->post('entrega'),
            'pasos_cotizacion' => $this->input->post('pasos_cotizacion'),
            'pasos_compra' => $this->input->post('pasos_compra'),
            'rma_requerido' => $this->input->post('rma_requerido'),
	        'valResico' => $this->input->post('valResico'),
            'persona' => $this->input->post('persona'),
        );
        //echo var_dump($data);die();

        $res = $this->Modelo->setProveedor($data);

            $data = array(
                'id_empresa'=>$this->input->post('id_empresa'),
                'user'=>$this->session->id,
                'estatus'=>'Editar Proveedor',
            );
            $this->Modelo->bitacoraEmpresas($data);

        if ($res) 
        {
            echo "1";
        } else {
            echo "";
        }
    }

    function ajax_getClientes(){
        $texto = $this->input->post('texto');
        $query = "SELECT E.* from empresas E where E.nombre like '%".$texto."%'";

        $res = $this->Modelo->consulta($query);
        if($res)
        {
            echo json_encode($res);
        }
        else {
            echo "";
        }
    }

    function ajax_getProveedores(){
        $texto = $this->input->post('texto');
        $query = "SELECT E.* from empresas E inner join proveedores P on E.id = P.empresa where E.proveedor = 1 and (P.tags like '%,".$texto.",%' or E.nombre like '%".$texto."%');";

        $res = $this->Modelo->consulta($query);
        if($res)
        {
            echo json_encode($res);
        }
        else {
            echo "";
        }
    }

    function ajax_getProveedor(){
        $id = $this->input->post('id');
        $query = "SELECT P.*, E.nombre from proveedores P inner join empresas E on E.id = P.empresa where P.empresa = '" . $id . "'";
        $res = $this->Modelo->consulta($query, TRUE);
        if($res)
        {
            echo json_encode($res);
        }
        else {
            echo "";
        }
    }

    function ajax_getCiudades(){
        $query = "SELECT distinct ciudad from empresas";
        $res = $this->Modelo->consulta($query);
        if($res)
        {
            echo json_encode($res);
        }
        else {
            echo "";
        }
    }

    function ajax_setInfoCotizaciones(){
        $datos = json_decode($this->input->post('datos'));
        $datos->moneda_cotizacion = json_encode($datos->moneda_cotizacion);
        $datos->iva_cotizacion = json_encode($datos->iva_cotizacion);

        $this->Conexion->modificar('empresas', $datos, null, array('id' => $datos->id));
        echo "1";
    }

    function ajax_getContactosCotizacion(){
        $empresa = $this->input->post('empresa');
        $res = $this->Conexion->consultar("SELECT EC.*, ifnull(Pl.nombre,'NO DEFINIDO') as Planta from empresas_contactos EC left join empresa_plantas Pl on Pl.id = EC.planta where EC.empresa = '$empresa' and EC.cotizable = 1");
        echo json_encode($res);
    }

    function ajax_setPlanta(){
        $planta = json_decode($this->input->post('planta'));
        if($planta->id){
            $this->Conexion->modificar('empresa_plantas', $planta, null, array('id' => $planta->id));
        }
        else{
            $this->Conexion->insertar('empresa_plantas', $planta);
        }
    }

    function ajax_getPlantas(){
        $empresa = $this->input->post('empresa');

        $query = "SELECT * from empresa_plantas where empresa = $empresa";
        
        $res = $this->Conexion->consultar($query);
        if($res){
            echo json_encode($res);
        }
    }

    function ajax_deletePlanta(){
        $id = $this->input->post('id');
        $this->Conexion->eliminar('empresa_plantas', array('id' => $id));

    }


//Funcion PAISES

function estados() {
        $pais = $this->input->post('paisid');
        
        if($pais){
            //$this->load->Modelo;
            $edo = $this->Modelo->getestados($pais);

            //echo '<option value="0">Estados</option>';
            foreach($edo as $fila){
                 


                echo '<option value="'. $fila->estadonombre .'">'. $fila->estadonombre .'</option>';

            }
        }  else {
            echo '<option value="0">Estados</option>';
        }
    }

    function ajax_getBitacoraEmpresas(){
        $ids = $this->input->post('id');      
        $query = "SELECT concat(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus from bitacora_empresas b JOIN usuarios u on b.user=u.id where id_empresa =". $ids;
        //echo var_dump($query);die();

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }
    function ajax_getBitacoraEmpresasContactos(){
        $ids = $this->input->post('id');      
        $query = "SELECT concat(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus from bitacora_contactos_empresas b JOIN usuarios u on b.user=u.id where id_contacto =". $ids;
        //echo var_dump($query);die();

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
        }
    }
    function ajax_paises(){
        //$this->output->enable_profiler(TRUE);
        $texto = $this->input->post('texto');
        $texto = trim($texto);
        $activo = $this->input->post('activo');
        $all=null;
        
        if ($activo ==0) {
             $all = " and activo=1";
         }
        $query = "SELECT * from pais where 1=1 ".$all;
        if (!empty($texto)) {
            $query .=" and paisnombre like '%".$texto."%'";
        }
        
        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
        }
    }

    function ajax_Activarpaises()
    {
        $id=$this->input->post('id');
        $data['activo']='1';
        $this->Conexion->modificar('pais', $data, null, array('id' => $id));
        echo "1";
    }
    
    function ajax_Desactivarpaises()
    {
        $id=$this->input->post('id');
        $data['activo']='0';
        $this->Conexion->modificar('pais', $data, null, array('id' => $id));
        echo "1";
    }

    function ajax_Estados()
    {
        $id=$this->input->post('id');

        $query = "SELECT * from estado where paisid= ".$id;                   

        $res = $this->Conexion->consultar($query);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
        }
    }
    function ajax_AsignarEstados()
    {
        $id=$this->input->post('id');
        $idp=$this->input->post('idp');

        $dato['defecto']='0';
        $this->Conexion->modificar('estado', $dato, null, array('paisid' => $idp));

        $data['defecto']='1';
        $this->Conexion->modificar('estado', $data, null, array('id' => $id));

        $query = "SELECT * from estado where defecto = 1 and id= ".$id;                   

        $res = $this->Conexion->consultar($query, TRUE);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
        }
        
    }
    function ajax_setEstados()
    {
        $id=$this->input->post('id');

        $query = "SELECT * from estado where defecto = 1 and paisid= ".$id;                   

        $res = $this->Conexion->consultar($query, TRUE);
        if($res)
        {
            echo json_encode($res);
            //echo $query;
        }
        else{
            echo "";
      }
  }
  function excel_empresas(){
        $texto = $this->input->post('texto');
        $texto = trim($texto);
        $parametro = $this->input->post('rbBusqueda');
        $cliente = $this->input->post('cliente');
        $proveedor = $this->input->post('proveedor');
        $c=0;
        $p=0;
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1).' 00:00:00';
        $f2=strval($fecha2).' 23:59:59' ;
        if ($cliente == 'cliente') {
                $c=1;
            }
            if ($proveedor == 'proveedor') {
                $p=1;
            }

/*        $query = "SELECT E.*, P.entrega from empresas E JOIN proveedores P on E.id=P.empresa where 1=1 ";

        if($c != $p)
        {
            if($cliente == "cliente")
            {
                $query .= " and E.cliente = '1'";
            }
            else if ($proveedor == "proveedor")
            {
                $query .= " and E.proveedor = '1'";
            }
        }

        if(!empty($texto))
        {
            if($parametro == "nombre")
            {
                $query .= " and (E.nombre like '%$texto%' or E.razon_social like '%$texto%')";
            }
            else if($parametro == "id")
            {
                $query .= " and E.id = '$texto'";
            }
            else if($parametro == "tag")
            {
                $query .= " and P.tags like '%$texto%'";
            }
        }
        $query .= "  ORDER BY E.nombre ASC";*/
$query = "SELECT c.id as cot, c.fecha,concat(uc.nombre, ' ', uc.paterno) usercot, c.tipo,P.entrega, c.estatus as estatus_cot, e.calle, e.numero, e.estado, e.pais, e.nombre, e.razon_social, e.ciudad, e.cliente, e.proveedor, (SELECT estatus from bitacora_empresas WHERE id_empresa = e.id AND estatus = 'ALTA') as estatus, (SELECT fecha from bitacora_empresas WHERE id_empresa = e.id AND estatus = 'ALTA') as fecha_alta, (SELECT concat(u.nombre, ' ', u.paterno) from bitacora_empresas be join usuarios u on u.id=be.user WHERE id_empresa = e.id AND estatus = 'ALTA') as usuario_alta FROM cotizaciones c JOIN empresas e on e.id=c.empresa JOIN proveedores P on e.id=P.empresa join usuarios uc on uc.id=c.responsable WHERE 1=1";

        //"SELECT E.*, P.entrega from empresas E JOIN proveedores P on E.id=P.empresa where 1=1 ";

        if($c != $p)
        {
            if($cliente == "cliente")
            {
                $query .= " and e.cliente = '1'";
            }
            else if ($proveedor == "proveedor")
            {
                $query .= " and e.proveedor = '1'";
            }
        }

        if(!empty($texto))
        {
            if($parametro == "nombre")
            {
                $query .= " and (e.nombre like '%$texto%' or e.razon_social like '%$texto%')";
            }
            else if($parametro == "id")
            {
                $query .= " and e.id = '$texto'";
            }
            else if($parametro == "tag")
            {
                $query .= " and P.tags like '%$texto%'";
            }
        }
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .=" HAVING fecha_alta BETWEEN '".$f1."' AND '".$f2."' ";
        }
        $query .= "  ORDER BY e.nombre ASC";
        
        $result= $this->Conexion->consultar($query);

        $salida='';

            $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                            <thead> 
                                <tr>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Nombre</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Razon Social</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Calle</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Ciduad</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Estado</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Pais</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Cliente</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Proveedor</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Entrega</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha Alta</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Usuario Alta</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Cotizacion</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Fecha</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Estatus</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Responsable</th>
                                    <th style="background-color: #F3F1F1; color: black;  border: 1px solid black; border-collapse: collapse">Tipo</th>
	                         </tr>
                            </thead>
                            <tbody>';
        foreach($result as $row){
            $ECLIENTE="";
            $EPROVEEDOR="";
            if ($row->cliente == 1) {
                $ECLIENTE="CLIENTE";
            }
            if ($row->proveedor == 1) {
                $EPROVEEDOR="PROVEEDOR";
            }

            $salida .='

                        <tr>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->nombre.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->razon_social.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->calle." ".$row->numero.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->ciudad.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->estado.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->pais.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$ECLIENTE.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$EPROVEEDOR.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->entrega.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->fecha_alta.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->usuario_alta.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->cot.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->fecha.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->estatus_cot.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->usercot.'</td>
                            <td style="color: $444;  border: 1px solid black; border-collapse: collapse">'.$row->tipo.'</td>
                        </tr>';
             }

                $salida .= '</tbody>
                </table>';

        $timestamp = date('m/d/Y', time());
       
        $filename='Empresas_'.$timestamp.'.xls';
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Content-Transfer-Encoding: binary'); 
        echo $salida;
        
    }

}
