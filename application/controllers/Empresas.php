<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Empresas extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('empresas_model', 'Modelo');
        $this->db->db_debug = FALSE; // Desactiva los errores detallados de la base de datos
    }

    // Carga la vista principal del catálogo de empresas
    function index() {
        // $this->output->enable_profiler(TRUE); // Línea comentada para depuración
        // $datos['empresas'] = $this->Modelo->getEmpresas(); // Comentado, no se usa en la vista actual
        $this->load->view('header');
        $this->load->view('empresas/catalogo');
    }

    // Muestra el formulario para dar de alta una nueva empresa
    function alta() {
        $datos['paises'] = $this->Modelo->listadopaises(); // Lista de países para el formulario
        $this->load->view('header');
        $this->load->view('empresas/alta', $datos);
    }

    // Muestra los detalles de una empresa específica
    function ver($id) {
        $datos['empresa'] = $this->Modelo->getEmpresa($id); // Datos generales de la empresa
        $datos['proveedor'] = $this->Modelo->getProveedor($id); // Información si es proveedor
        $datos['contactos'] = $this->Modelo->getContactos($id); // Contactos asociados
        $datos['archivos'] = $this->Modelo->getArchivos($id); // Archivos relacionados
        $datos['requisitos'] = $this->Modelo->getRequisitos_empresa($id); // Requisitos pendientes
        $datos['paises'] = $this->Modelo->listadopaises(); // Nuevamente países
        $datos['documento'] = $this->Modelo->documento(); // Documentos de referencia

        $this->load->view('header');
        $this->load->view('empresas/ver', $datos);
    }

    function registrar() {
    $ACIERTOS = array(); 
    $ERRORES = array();

    // Recolección y limpieza de datos del formulario
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
        'foto' => 'default.png', // Imagen por defecto
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

    // Intento de inserción de nueva empresa
    if ($this->Modelo->crear_empresa($data)) {
        $acierto = array('titulo' => 'Agregar Empresa', 'detalle' => 'Se ha agregado Empresa con Éxito');
        array_push($ACIERTOS, $acierto);

        // Recupera el ID recién insertado
        $res = $this->Conexion->consultar('SELECT MAX(id) as id FROM empresas', TRUE);

        // Registra en bitácora
        $data = array(
            'id_empresa' => $res->id,
            'user' => $this->session->id,
            'estatus' => 'ALTA',
        );
        $this->Modelo->bitacoraEmpresas($data);

    } else {
        // En caso de error al insertar
        $error = array('titulo' => 'ERROR', 'detalle' => 'Error al agregar Empresa');
        array_push($ERRORES, $error);
    }

    // Se guardan mensajes en sesión
    $this->session->aciertos = $ACIERTOS;
    $this->session->errores = $ERRORES;

    // Redirige al catálogo de empresas
    redirect(base_url('empresas'));
}

    function editar() {
    $ACIERTOS = array(); 
    $ERRORES = array();
    
    $id = $this->input->post('id');

    // Recolección y limpieza de datos del formulario
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

    // Actualiza la empresa
    if ($this->Modelo->update($data)) {
        $acierto = array('titulo' => 'Editar Empresa', 'detalle' => 'Se ha editado Empresa con Éxito');
        array_push($ACIERTOS, $acierto);

        // Registro en bitácora
        $data = array(
            'id_empresa' => trim($id),
            'user' => $this->session->id,
            'estatus' => 'Editar Empresa',
        );
        $this->Modelo->bitacoraEmpresas($data);
    } else {
        // Error al actualizar
        $error = array('titulo' => 'ERROR', 'detalle' => 'Error al editar Empresa');
        array_push($ERRORES, $error);
    }

    // Almacena los mensajes en la sesión
    $this->session->aciertos = $ACIERTOS;
    $this->session->errores = $ERRORES;

    // Redirige a la vista de la empresa
    redirect(base_url('empresas/ver/' . $id));
}

    function paises() {
    // Carga la vista del catálogo de países
    $this->load->view('header');
    $this->load->view('empresas/catalogo_paises');
}

function ajax_getEmpresas() {
    $texto = trim($this->input->post('texto'));
    $parametro = $this->input->post('parametro');
    $cliente = $this->input->post('cliente');
    $proveedor = $this->input->post('proveedor');

    // Consulta base: une empresas con proveedores
    $query = "SELECT E.* FROM empresas E JOIN proveedores P ON E.id = P.empresa WHERE 1 = 1";

    // Filtro según tipo (cliente o proveedor)
    if ($cliente != $proveedor) {
        if ($cliente == "1") {
            $query .= " AND E.cliente = '1'";
        } else {
            $query .= " AND E.proveedor = '1'";
        }
    }

    // Filtros de búsqueda por parámetro
    if (!empty($texto)) {
        if ($parametro == "nombre") {
            $query .= " AND (E.nombre LIKE '%$texto%' OR E.razon_social LIKE '%$texto%')";
        } else if ($parametro == "id") {
            $query .= " AND E.id = '$texto'";
        } else if ($parametro == "tag") {
            $query .= " AND P.tags LIKE '%$texto%'";
        }
    }

    // Ejecuta la consulta y retorna el resultado
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function ajax_getContactos() {
    // Obtiene el ID de la empresa desde el POST
    $id = $this->input->post('id');
    $where['empresa'] = $id;

    // Consulta los contactos asociados a la empresa
    $res = $this->Conexion->get('empresas_contactos', $where);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function ajax_getContacto() {
    // Obtiene el ID del contacto desde el POST
    $id = $this->input->post('id');

    // Consulta la información del contacto junto con el nombre de la planta si existe
    $res = $this->Conexion->consultar(
        "SELECT EC.*, IFNULL(Pl.nombre, 'NO DEFINIDO') AS Planta 
         FROM empresas_contactos EC 
         LEFT JOIN empresa_plantas Pl ON Pl.id = EC.planta 
         WHERE EC.id = $id", 
         TRUE
    );
    if ($res) {
        echo json_encode($res);
    }
}

function ajax_getArchivos() {
    // Obtiene el ID de la empresa y texto de búsqueda desde el POST
    $empresa = $this->input->post('empresa');
    $texto = $this->input->post('texto');

    // Consulta base para obtener los archivos de la empresa con información del usuario y tipo de documento
    $query = "SELECT A.*, CONCAT(U.nombre, ' ', U.paterno) AS User, D.tipo, D.clave 
              FROM empresas_archivos A 
              INNER JOIN usuarios U ON U.id = A.usuario  
              LEFT JOIN documentosEmpresas D ON D.id = A.id_documento 
              WHERE A.empresa = $empresa";

    // Filtro por texto si se proporciona
    if ($texto) {
        $query .= " AND (A.nombre LIKE '%$texto%' OR A.comentarios LIKE '%$texto%')";
    }

    // Ejecuta la consulta y responde con los resultados
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

    function ajax_getFacturaEjemplo() {
    // Obtiene el ID de la empresa desde POST
    $empresa = $this->input->post('empresa');

    // Consulta el nombre del archivo de factura de ejemplo
    $query = "SELECT factura_ejemplo FROM empresas WHERE id = $empresa";
    $res = $this->Conexion->consultar($query, TRUE);

    // Devuelve el nombre del archivo como respuesta
    echo $res->factura_ejemplo;
}

function ajax_setFacturaEjemplo() {
    // Obtiene datos desde POST
    $id = $this->input->post('empresa');
    $file = $this->input->post('file');

    // Si se recibió un archivo válido
    if ($file != "undefined") {
        $config['upload_path'] = 'data/empresas/ejemplo_facturas/';
        $config['allowed_types'] = 'pdf';
        $config['encrypt_name'] = TRUE;

        $this->load->library('upload', $config);

        // Intenta subir el archivo
        if ($this->upload->do_upload('file')) {
            $data['factura_ejemplo'] = $this->upload->data('file_name');

            // Guarda el nombre del archivo en la base de datos
            $this->Conexion->modificar('empresas', $data, null, array('id' => $id));
        }
    }
}

function ajax_deleteFacturaEjemplo() {
    // Obtiene ID de la empresa
    $id = $this->input->post('empresa');

    // Consulta el nombre del archivo actual
    $res = $this->Conexion->consultar("SELECT factura_ejemplo FROM empresas WHERE id = $id", TRUE);

    // Elimina el archivo del servidor
    unlink('data/empresas/ejemplo_facturas/' . $res->factura_ejemplo);

    // Limpia el campo en la base de datos
    $data['factura_ejemplo'] = "";
    $this->Conexion->modificar('empresas', $data, null, array('id' => $id));
}

    function editar_otros_datos() {
    // Inicializa arreglos para mensajes de éxito y error
    $ACIERTOS = array(); 
    $ERRORES = array();

    // Obtiene el ID de la empresa desde POST
    $id = $this->input->post('id');

    // Recoge los datos del formulario
    $data = array(
        'id' => $id,
        'horario_facturas' => trim($this->input->post('horario_facturas')),
        'ultimo_dia_facturas' => trim($this->input->post('ultimo_dia_facturas')),
        'requisitos_logisticos' => trim($this->input->post('requisitos_logisticos')),
        'requisitos_documento' => trim($this->input->post('requisitos_documento')),
        'comentarios' => trim($this->input->post('comentarios')),
        'dejar_factura' => $this->input->post('dejar_factura') == '1' ? '1' : '0',
    );

    // Intenta guardar los cambios
    if ($this->Modelo->update($data)) {
        array_push($ACIERTOS, array('titulo' => 'Editar Empresa', 'detalle' => 'Se ha editado Empresa con Éxito'));
    } else {
        array_push($ERRORES, array('titulo' => 'ERROR', 'detalle' => 'Error al editar Empresa'));
    }

    // Almacena mensajes y redirige
    $this->session->aciertos = $ACIERTOS;
    $this->session->errores = $ERRORES;
    redirect(base_url('empresas/ver/' . $id));
}

// CONTACTOS

function agregarContacto() {
    // Recoge datos del formulario
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

    // Inserta el nuevo contacto
    $res = $this->Modelo->insertContacto($data);

    // Registra en bitácora
    $r = $this->Conexion->consultar('SELECT * FROM empresas_contactos ORDER BY id DESC LIMIT 1', TRUE);
    $bitacora = array(
        'id_contacto' => $r->id,
        'id_empresa' => $r->empresa,
        'user' => $this->session->id,
        'estatus' => 'ALTA',
    );
    $this->Modelo->bitacoraContactosEmpresas($bitacora);

    // Devuelve el nuevo contacto en formato JSON
    if ($res) {
        $res = json_encode($this->Modelo->getContacto($res));
    } else {
        $res = "";
    }

    echo $res;
}

    // Edita los datos de un contacto existente
function editarContacto() {
    $id = $this->input->post('id');

    // Prepara los datos recibidos del formulario
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

    // Consulta la empresa del contacto para registrar en bitácora
    $r = $this->Conexion->consultar('SELECT * FROM empresas_contactos WHERE id = '.trim($id), TRUE);
    $dataBit = array(
        'id_contacto' => trim($id),
        'id_empresa' => $r->empresa,
        'user' => $this->session->id,
        'estatus' => 'Editar Contacto',
    );
    $this->Modelo->bitacoraContactosEmpresas($dataBit);

    // Actualiza el contacto y responde con el objeto actualizado
    if ($this->Modelo->updateContacto($data)) {
        $res = json_encode($this->Modelo->getContacto($id));
    } else {
        $res = "";
    }

    echo $res;
}

// Retorna un contacto en formato JSON
function getContacto_json(){
    $contact = $this->Modelo->getContacto($this->input->post('id'));
    if($contact){
        echo json_encode($contact);
    } else {
        echo "";
    }
}

// Elimina un contacto y retorna "1" si fue exitoso, o cadena vacía si falló
function deleteContacto_json(){
    if($this->Modelo->deleteContacto($this->input->post('id'))){
        echo "1";
    } else {
        echo ""; // jQuery interpreta como FALSE
    }
}

    // Sube o reemplaza la foto de una empresa
function subir_foto() {
    $id = $this->input->post('id_empresa');            // ID de la empresa
    $foto = $this->input->post('fotoActual');          // Nombre del archivo actual
    $file = $this->input->post('iptFoto');             // Archivo recibido

    if ($file != "undefined") {
        // Configuración para la carga del archivo
        $config['upload_path'] = 'data/empresas/fotos/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);

        // Si la carga del archivo fue exitosa
        if ($this->upload->do_upload('iptFoto')) {
            $data['id'] = $id;
            $data['foto'] = $this->upload->data('file_name');

            // Actualiza la foto en la base de datos
            if ($this->Modelo->update($data)) {
                // Elimina la foto anterior si no es la por defecto
                if ($foto != "default.png") {
                    unlink('data/empresas/fotos/' . $foto);
                }
                echo $data['foto'];
            }
        } else {
            echo "";
        }
    } else {
        // Si no se sube un archivo, se asigna la imagen por defecto
        $data['id'] = $id;
        $data['foto'] = 'default.png';

        if ($this->Modelo->update($data)) {
            if ($foto != "default.png") {
                unlink('data/empresas/fotos/' . $foto);
            }
            echo $data['foto'];
        }
    }
}

    // Sube un archivo relacionado a una empresa y lo registra en la base de datos
function subir_archivo() {
    $id = $this->input->post('id');                        // ID de la empresa
    $comentarios = $this->input->post('comentarios');      // Comentarios del archivo
    $documento = $this->input->post('documento');          // Tipo de documento

    // Crea el directorio de la empresa si no existe
    if (!is_dir('data/empresas/archivos/' . $id)) {
        mkdir('data/empresas/archivos/' . $id, 0777, TRUE);
    }

    // Configuración de subida
    $config['upload_path'] = 'data/empresas/archivos/' . $id;
    $config['allowed_types'] = '*';
    $this->load->library('upload', $config);

    // Si la carga del archivo fue exitosa
    if ($this->upload->do_upload('userfile')) {
        $data['empresa'] = $id;
        $data['usuario'] = $this->session->id;
        $data['nombre'] = $this->upload->data('file_name');
        $data['comentarios'] = $comentarios;
        $data['id_documento'] = $documento;

        $idFile = $this->Modelo->insertArchivo($data);

        if ($idFile) {
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
        // Se intenta generar un ícono aunque no haya nombre (puede generar warning si no hay archivo)
        $arreglo['icono'] = $this->aos_funciones->file_image($this->upload->data('file_name') ?? '');
        $arreglo['error'] = $this->upload->display_errors();
        echo json_encode($arreglo);
    }
}

    // Elimina un archivo físico y su registro en la base de datos
function deleteArchivo_json() {
    $id_file = $this->input->post('id');
    $id_empresa = $this->input->post('id_empresa');
    $nombre = $this->input->post('nombre_archivo');

    if ($this->Modelo->deleteArchivo($id_file)) {
        unlink('data/empresas/archivos/' . $id_empresa . '/' . $nombre);
        echo "1";
    } else {
        echo ""; // jQuery interpreta como FALSE (no 0)
    }
}

// Edita los metadatos de un archivo (comentarios y tipo de documento)
function editArchivo_json() {
    $data['id'] = $this->input->post('id');
    $data['comentarios'] = trim($this->input->post('comentarios'));
    $data['id_documento'] = $this->input->post('documento');

    if ($this->Modelo->updateArchivo($data)) {
        echo $data['comentarios'];
    } else {
        echo ""; // jQuery interpreta como FALSE (no 0)
    }
}

//////// REQUISITOS DE FACTURACIÓN //////////

// Carga la vista del catálogo de requisitos
function requisitos() {
    $data['requisitos'] = $this->Modelo->getRequisitos('');
    $this->load->view('header');
    $this->load->view('empresas/catalogo_requisitos', $data);
}

// Retorna los requisitos de facturación filtrados por tipo
function getRequisitos_json() {
    $tipo = $this->input->post('tipo');
    $requisitos = $this->Modelo->getRequisitos($tipo);

    if ($requisitos) {
        echo json_encode($requisitos->result());
    } else {
        echo "";
    }
}

      // Asocia un requisito existente a una empresa
function setRequisitos_json() {
    $data['empresa'] = $this->input->post('id_empresa');
    $data['requisito'] = $this->input->post('requisito');
    $data['tipo'] = $this->input->post('tipo');
    $data['detalles'] = $this->input->post('detalles');

    $id = $this->Modelo->setRequisitos($data);

    if ($id) {
        $res['id'] = $id;
        $res['requisito'] = $data['requisito'];
        $res['detalles'] = $data['detalles'];
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Elimina un requisito vinculado a una empresa
function deleteRequisito_json() {
    if ($this->Modelo->deleteRequisito($this->input->post('id'))) {
        echo "1";
    } else {
        echo ""; // jQuery interpreta como FALSE (no 0)
    }
}

// Agrega un nuevo requisito genérico al catálogo
function agregarRequisito_ajax() {
    $data = array(
        'requisito' => strtoupper(trim($this->input->post('requisito'))),
        'tipo' => strtoupper(trim($this->input->post('tipo'))),
        'detalle' => $this->input->post('detalle'),
    );

    $res = $this->Modelo->insertRequisito($data);

    if ($res) {
        $data['id'] = $res;
        echo json_encode($data);
    } else {
        echo "";
    }
}

    // Edita un requisito del catálogo general
function editarRequisito_ajax() {
    $data = array(
        'id' => strtoupper(trim($this->input->post('id'))),
        'requisito' => strtoupper(trim($this->input->post('requisito'))),
        'tipo' => strtoupper(trim($this->input->post('tipo'))),
        'detalle' => $this->input->post('detalle'),
    );

    $res = $this->Modelo->updateRequisito($data);

    if ($res) {
        echo json_encode($data);
    } else {
        echo "";
    }
}

// Elimina un requisito del catálogo general
function eliminarRequisito_ajax() {
    $data = array(
        'id' => trim($this->input->post('id')),
    );

    $res = $this->Modelo->deleteRequisitoCatalogo($data);

    if ($res) {
        echo "1";
    } else {
        echo "";
    }
}

// Actualiza la ubicación geográfica de la empresa (latitud, longitud y nivel de zoom)
function editarUbicacion_ajax() {
    $data = array(
        'id' => $this->input->post('id'),
        'lat' => $this->input->post('lat'),
        'lng' => $this->input->post('lng'),
        'zoom' => $this->input->post('zoom'),
    );

    $res = $this->Modelo->update($data);

    if ($res) {
        echo "1";
    } else {
        echo "";
    }
}

    // Actualiza el listado de documentos y código de impresión de una empresa
function ajax_setListadoDocumentos(){
    $where['id'] = $this->input->post('id');
    $data['documentos_facturacion'] = $this->input->post('documentos');
    $data['codigo_impresion'] = strtoupper($this->input->post('codigo'));

    $this->Conexion->modificar('empresas', $data, null, $where);
    echo "1";
}

// Registra o actualiza los datos del proveedor asociados a la empresa
function ajax_setProveedor(){
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

    $res = $this->Modelo->setProveedor($data);

    // Registrar en bitácora la modificación del proveedor
    $data = array(
        'id_empresa' => $this->input->post('id_empresa'),
        'user' => $this->session->id,
        'estatus' => 'Editar Proveedor',
    );
    $this->Modelo->bitacoraEmpresas($data);

    if ($res) {
        echo "1";
    } else {
        echo "";
    }
}
    
    // Devuelve clientes cuyo nombre coincida parcialmente con el texto ingresado
function ajax_getClientes(){
    $texto = $this->input->post('texto');
    $query = "SELECT E.* FROM empresas E WHERE E.nombre LIKE '%" . $texto . "%'";

    $res = $this->Modelo->consulta($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Devuelve proveedores cuyo nombre o tags coincidan parcialmente con el texto ingresado
function ajax_getProveedores(){
    $texto = $this->input->post('texto');
    $query = "SELECT E.* FROM empresas E INNER JOIN proveedores P ON E.id = P.empresa WHERE E.proveedor = 1 AND (P.tags LIKE '%," . $texto . ",%' OR E.nombre LIKE '%" . $texto . "%')";

    $res = $this->Modelo->consulta($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Devuelve los datos del proveedor a partir de su ID
function ajax_getProveedor(){
    $id = $this->input->post('id');
    $query = "SELECT P.*, E.nombre FROM proveedores P INNER JOIN empresas E ON E.id = P.empresa WHERE P.empresa = '" . $id . "'";

    $res = $this->Modelo->consulta($query, TRUE);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Devuelve la lista de ciudades registradas en empresas (sin duplicados)
function ajax_getCiudades(){
    $query = "SELECT DISTINCT ciudad FROM empresas";

    $res = $this->Modelo->consulta($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    // Guarda la información de cotización (moneda, IVA, etc.) de una empresa
function ajax_setInfoCotizaciones(){
    $datos = json_decode($this->input->post('datos'));
    $datos->moneda_cotizacion = json_encode($datos->moneda_cotizacion);
    $datos->iva_cotizacion = json_encode($datos->iva_cotizacion);

    $this->Conexion->modificar('empresas', $datos, null, array('id' => $datos->id));
    echo "1";
}

// Obtiene los contactos cotizables de una empresa específica
function ajax_getContactosCotizacion(){
    $empresa = $this->input->post('empresa');
    $res = $this->Conexion->consultar("SELECT EC.*, IFNULL(Pl.nombre, 'NO DEFINIDO') as Planta FROM empresas_contactos EC LEFT JOIN empresa_plantas Pl ON Pl.id = EC.planta WHERE EC.empresa = '$empresa' AND EC.cotizable = 1");
    echo json_encode($res);
}

// Crea o edita una planta asociada a la empresa
function ajax_setPlanta(){
    $planta = json_decode($this->input->post('planta'));
    if ($planta->id) {
        $this->Conexion->modificar('empresa_plantas', $planta, null, array('id' => $planta->id));
    } else {
        $this->Conexion->insertar('empresa_plantas', $planta);
    }
}

// Obtiene las plantas asociadas a una empresa
function ajax_getPlantas(){
    $empresa = $this->input->post('empresa');
    $query = "SELECT * FROM empresa_plantas WHERE empresa = $empresa";
    
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

// Elimina una planta por su ID
function ajax_deletePlanta(){
    $id = $this->input->post('id');
    $this->Conexion->eliminar('empresa_plantas', array('id' => $id));
}

    // Devuelve el listado de estados del país seleccionado en formato <option>
function estados() {
    $pais = $this->input->post('paisid');
    
    if ($pais) {
        $edo = $this->Modelo->getestados($pais);
        foreach ($edo as $fila) {
            echo '<option value="'. $fila->estadonombre .'">'. $fila->estadonombre .'</option>';
        }
    } else {
        echo '<option value="0">Estados</option>';
    }
}

// Obtiene la bitácora de cambios de una empresa específica
function ajax_getBitacoraEmpresas(){
    $ids = $this->input->post('id');      
    $query = "SELECT CONCAT(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus FROM bitacora_empresas b JOIN usuarios u ON b.user = u.id WHERE id_empresa = ". $ids;

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

// Obtiene la bitácora de cambios de contactos de empresa
function ajax_getBitacoraEmpresasContactos(){
    $ids = $this->input->post('id');      
    $query = "SELECT CONCAT(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus FROM bitacora_contactos_empresas b JOIN usuarios u ON b.user = u.id WHERE id_contacto = ". $ids;

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

// Devuelve lista de países según texto buscado, y si se desea solo los activos
function ajax_paises(){
    $texto = trim($this->input->post('texto'));
    $activo = $this->input->post('activo');
    $all = null;

    if ($activo == 0) {
        $all = " AND activo = 1";
    }

    $query = "SELECT * FROM pais WHERE 1 = 1 " . $all;
    if (!empty($texto)) {
        $query .= " AND paisnombre LIKE '%" . $texto . "%'";
    }

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    // Activa un país estableciendo el campo 'activo' en 1
function ajax_Activarpaises()
{
    $id = $this->input->post('id');
    $data['activo'] = '1';
    $this->Conexion->modificar('pais', $data, null, array('id' => $id));
    echo "1";
}

// Desactiva un país estableciendo el campo 'activo' en 0
function ajax_Desactivarpaises()
{
    $id = $this->input->post('id');
    $data['activo'] = '0';
    $this->Conexion->modificar('pais', $data, null, array('id' => $id));
    echo "1";
}

// Obtiene todos los estados pertenecientes a un país específico
function ajax_Estados()
{
    $id = $this->input->post('id');
    $query = "SELECT * FROM estado WHERE paisid = " . $id;

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Asigna un estado como predeterminado (defecto = 1) dentro de un país
function ajax_AsignarEstados()
{
    $id = $this->input->post('id');
    $idp = $this->input->post('idp');

    // Desactiva el estado predeterminado anterior del país
    $dato['defecto'] = '0';
    $this->Conexion->modificar('estado', $dato, null, array('paisid' => $idp));

    // Activa el nuevo estado predeterminado
    $data['defecto'] = '1';
    $this->Conexion->modificar('estado', $data, null, array('id' => $id));

    // Retorna el nuevo estado marcado como predeterminado
    $query = "SELECT * FROM estado WHERE defecto = 1 AND id = " . $id;
    $res = $this->Conexion->consultar($query, TRUE);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    // Retorna el estado marcado como defecto (predeterminado) para un país dado
function ajax_setEstados()
{
    $id = $this->input->post('id');

    $query = "SELECT * FROM estado WHERE defecto = 1 AND paisid = " . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    if ($res) {
        echo json_encode($res);
    } else {
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


$query = "SELECT c.id as cot, c.fecha,concat(uc.nombre, ' ', uc.paterno) usercot, c.tipo,P.entrega, c.estatus as estatus_cot, e.calle, e.numero, e.estado, e.pais, e.nombre, e.razon_social, e.ciudad, e.cliente, e.proveedor, (SELECT estatus from bitacora_empresas WHERE id_empresa = e.id AND estatus = 'ALTA') as estatus, (SELECT fecha from bitacora_empresas WHERE id_empresa = e.id AND estatus = 'ALTA') as fecha_alta, (SELECT concat(u.nombre, ' ', u.paterno) from bitacora_empresas be join usuarios u on u.id=be.user WHERE id_empresa = e.id AND estatus = 'ALTA') as usuario_alta FROM cotizaciones c JOIN empresas e on e.id=c.empresa JOIN proveedores P on e.id=P.empresa join usuarios uc on uc.id=c.responsable WHERE 1=1";

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
