<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Configuracion: Maneja vistas de configuración general
class Configuracion extends CI_Controller {

    // Constructor: Carga modelos necesarios
    function __construct() {
        parent::__construct();
        $this->load->model('conexion_model','Conexion');          // Modelo para conexión a BD
        $this->load->model('privilegios_model');                  // Modelo para privilegios y notificaciones
    }

    // Vista principal de configuración de compras
    function compras() {
        $this->load->view('header');                              // Carga el encabezado
        $this->load->view('configuracion/compra_opciones');       // Carga opciones de compras
    }

    // Vista principal de configuración de servicios
    function servicios() {
        $this->load->view('header');                              // Carga el encabezado
        $this->load->view('configuracion/servicios_opciones');    // Carga opciones de servicios
    }

    // Vista principal de configuración de requerimientos
    function requerimientos() {
        $this->load->view('header');                              // Carga el encabezado
        $this->load->view('configuracion/requerimientos_opciones'); // Carga opciones de requerimientos
    }

    // ====== SERVICIOS ======

    // Vista de claves y precios de servicios
    function claves_precio(){
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/servicios/claves_precio');// Vista de claves y precios
    }

    // Vista de magnitudes de servicios
    function magnitudes(){
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/servicios/magnitudes');  // Vista de magnitudes
    }

    // Vista para configuración de correo en servicios
    function correo(){
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/servicios/correo');      // Vista de correo
    }

    // ====== COMPRAS ======

    // Vista para dirección de envío
    function shipping_address() {
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/compras/shipping_address'); // Dirección de envío
    }

    // Vista para dirección de facturación
    function billing_address() {
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/compras/billing_address'); // Dirección de facturación
    }

    // Vista para opciones de pago
    function pagos() {
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/compras/pagos');         // Vista de pagos
    }

    // ====== NOTIFICACIONES ======

    // Vista de notificaciones con datos desde modelo de privilegios
    function notificaiones() {
        $datos['noti'] = $this->privilegios_model->getNotificaciones(); // Obtiene notificaciones
        $this->load->view('header');                               // Carga el encabezado
        $this->load->view('configuracion/notificaciones', $datos); // Vista con notificaciones
    }
}
    
    // Obtiene claves de precio desde la base de datos (por ID si se proporciona)
    function ajax_getClavesPrecio() {
    $id = 0;
        if(isset($_POST['id'])) {
            $id = $this->input->post('id');  // Recupera ID si está presente en POST
        }

        $query = "SELECT * from claves_precio";
        $row = $id > 0;

    // Si se especificó un ID, se agrega condición WHERE
        if($row) {
            $query .= " where id = $id";
        }

        $res = $this->Conexion->consultar($query, $row);

    // Devuelve resultado en JSON o "0" si falla
        if($res) {
         echo json_encode($res);
        } else {
         echo "0";
        }
}

// Actualiza valores de bajo y alto en la tabla claves_precio
function ajax_setClavesPrecio() {
    $id = $this->input->post('id');          // ID del registro a actualizar
    $bajo = $this->input->post('bajo');      // Valor bajo
    $alto = $this->input->post('alto');      // Valor alto

    $datos['bajo'] = $bajo;
    $datos['alto'] = $alto;
    $where['id'] = $id;

    $this->Conexion->modificar('claves_precio', $datos, null, $where); // Realiza la actualización
    echo "1"; // Éxito
}

// Obtiene registros de magnitudes desde la base de datos (opcionalmente por ID)
function ajax_getMagnitudes() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id');  // Recupera ID si está presente en POST
    }

    $query = "SELECT * from magnitudes";
    $row = $id > 0;

    // Si se especificó un ID, se agrega condición WHERE
    if($row) {
        $query .= " where id = $id";
    }

    $res = $this->Conexion->consultar($query, $row);

    // Devuelve resultado en JSON o "0" si falla
    if($res) {
        echo json_encode($res);
    } else {
        echo "0";
    }
}

// Obtiene textos de correo activos, con nombre del usuario asociado (opcionalmente por ID)
function ajax_getTexto_correo() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id');  // Recupera ID si está presente en POST
    }

    // Consulta que une texto_correo con usuarios y filtra solo activos
    $query = "SELECT tc.*, concat(u.nombre, ' ', u.paterno) as us 
              FROM texto_correo tc 
              JOIN usuarios u on u.id = tc.id_us 
              WHERE tc.activo = 1";

    $row = $id > 0;

    // Si se especificó un ID, se agrega condición adicional
    if($row) {
        $query .= " and tc.id = $id";
    }

    $res = $this->Conexion->consultar($query, $row);

    // Devuelve resultado en JSON o "0" si falla
    if($res) {
        echo json_encode($res);
    } else {
        echo "0";
    }
}
// Inserta o actualiza una magnitud
function ajax_setMagnitudes() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID del registro (si aplica)
    }

    $magnitud = $this->input->post('magnitud'); // Nombre de la magnitud
    $prefijo = $this->input->post('prefijo');   // Prefijo asociado

    $datos['magnitud'] = $magnitud;
    $datos['prefijo'] = $prefijo;

    $res = FALSE;

    // Si el ID es 0, se inserta nuevo registro
    if($id == 0) {
        $res = $this->Conexion->insertar('magnitudes', $datos) > 0;
    } else {
        // Si ya existe, se actualiza el registro
        $where['id'] = $id;
        $res = $this->Conexion->modificar('magnitudes', $datos, null, $where) >= 0;
    }

    if($res) {
        echo "1"; // Éxito
    }
}

// Elimina una magnitud por ID
function ajax_deleteMagnitudes() {
    $id = $this->input->post('id');     // ID del registro a eliminar
    $where['id'] = $id;

    // Elimina y devuelve "1" si tiene éxito
    if($this->Conexion->eliminar('magnitudes', $where)) {
        echo "1";
    }
}

// Verifica si un prefijo ya existe en la tabla magnitudes
function ajax_prefijoExists() {
    $prefijo = $this->input->post('prefijo'); // Prefijo a verificar

    $query = "SELECT count(M.prefijo) as Qty 
              FROM magnitudes M 
              WHERE 1 = 1 AND prefijo = '$prefijo'";

    $res = $this->Conexion->consultar($query, TRUE);

    // Devuelve cantidad de coincidencias o vacío si falla
    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Obtiene direcciones de envío (shipping_address), por ID si se especifica
function ajax_getShippingAddresses() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID del registro si aplica
    }

    $query = "SELECT * from shipping_address";
    $row = $id > 0;

    // Si hay ID, se agrega condición WHERE
    if($row) {
        $query .= " where id = $id";
    }

    $res = $this->Conexion->consultar($query, $row);

    // Devuelve resultados en JSON o "0" si no hay resultados
    if($res) {
        echo json_encode($res);
    } else {
        echo "0";
    }
}

// Inserta o actualiza una dirección de envío (shipping_address)
function ajax_setShippingAddresses() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID si se va a actualizar
    }

    $nombre = $this->input->post('nombre');       // Nombre del destinatario
    $direccion = $this->input->post('direccion'); // Dirección de envío
    $pais = $this->input->post('pais');           // País

    $datos['nombre'] = $nombre;
    $datos['direccion'] = $direccion;
    $datos['pais'] = $pais;

    $res = FALSE;

    // Inserta nuevo registro si ID es 0
    if($id == 0) {
        $datos['default'] = '0'; // No es default por defecto
        $res = $this->Conexion->insertar('shipping_address', $datos) > 0;
    } else {
        // Actualiza dirección existente
        $where['id'] = $id;
        $res = $this->Conexion->modificar('shipping_address', $datos, null, $where) >= 0;
    }

    if($res) {
        echo "1"; // Éxito
    }
}

// Elimina una dirección de envío por ID
function ajax_deleteShippingAddresses() {
    $id = $this->input->post('id');     // ID de la dirección a eliminar
    $where['id'] = $id;

    // Elimina el registro y responde "1" si tuvo éxito
    if($this->Conexion->eliminar('shipping_address', $where)) {
        echo "1";
    }
}

// Establece una dirección como predeterminada para un país específico
function ajax_setDefaultShippingAddresses() {
    $where['pais'] = $this->input->post('pais');    // País al que pertenece la dirección
    $datos['default'] = '0';
    
    // Primero quita el estatus "default" de todas las direcciones del país
    $this->Conexion->modificar('shipping_address', $datos, null, $where);
    
    // Luego asigna como default la dirección indicada por ID
    $where['id'] = $this->input->post('id');
    $datos['default'] = '1';
    $this->Conexion->modificar('shipping_address', $datos, null, $where);

    echo "1"; // Confirmación de éxito
}

// Obtiene direcciones de facturación (billing_address), por ID si se especifica
function ajax_getBillingAddresses() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID si se desea un registro específico
    }

    $query = "SELECT * from billing_address";
    $row = $id > 0;

    // Si se especificó ID, agrega condición WHERE
    if($row) {
        $query .= " where id = $id";
    }

    $res = $this->Conexion->consultar($query, $row);

    // Devuelve resultado en JSON o "0" si no hay datos
    if($res) {
        echo json_encode($res);
    } else {
        echo "0";
    }
}

// Inserta o actualiza una dirección de facturación (billing_address)
function ajax_setBillingAddresses() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID si es una actualización
    }

    $nombre = $this->input->post('nombre');         // Nombre del cliente
    $direccion = $this->input->post('direccion');   // Dirección fiscal

    $datos['nombre'] = $nombre;
    $datos['direccion'] = $direccion;

    $res = FALSE;

    // Si no hay ID, se inserta nuevo registro
    if($id == 0) {
        $datos['default'] = '0'; // No es default por defecto
        $res = $this->Conexion->insertar('billing_address', $datos) > 0;
    } else {
        // Actualiza registro existente
        $where['id'] = $id;
        $res = $this->Conexion->modificar('billing_address', $datos, null, $where) >= 0;
    }

    if($res) {
        echo "1"; // Éxito
    }
}

    // Elimina una dirección de facturación por ID
function ajax_deleteBillingAddresses() {
    $id = $this->input->post('id');     // ID del registro a eliminar
    $where['id'] = $id;

    // Ejecuta la eliminación y devuelve "1" si tiene éxito
    if($this->Conexion->eliminar('billing_address', $where)) {
        echo "1";
    }
}

// Establece una dirección de facturación como predeterminada
function ajax_setDefaultBillingAddresses() {
    $where['id'] = $this->input->post('id'); // ID de la dirección que será default

    $datos['default'] = '0';

    // Pone todas las direcciones como no predeterminadas
    $this->Conexion->modificar('billing_address', $datos, null, null);

    // Establece como predeterminada la dirección especificada
    $datos['default'] = '1';
    $this->Conexion->modificar('billing_address', $datos, null, $where);

    echo "1"; // Éxito
}

// Obtiene métodos de pago (empresa_metodos_pago), por ID si se proporciona
function ajax_getPagos() {
    $id = 0;
    if(isset($_POST['id'])) {
        $id = $this->input->post('id'); // ID del método de pago a obtener
    }

    $query = "SELECT * from empresa_metodos_pago";
    $row = $id > 0;

    // Aplica filtro si se especificó un ID
    if($row) {
        $query .= " where id = $id";
    }

    $res = $this->Conexion->consultar($query, $row);

    // Devuelve los datos en JSON o "0" si no hay resultados
    if($res) {
        echo json_encode($res);
    } else {
        echo "0";
    }
}

// Inserta o actualiza un método de pago
function ajax_setPagos() {
    $metodo = json_decode($this->input->post('metodo_pago'));  // Decodifica objeto desde JSON
    $metodo->fecha_vencimiento = date("Y-m-d", strtotime($metodo->fecha_vencimiento)); // Formatea fecha

    $res = FALSE;

    // Inserta nuevo método si el ID es 0
    if($metodo->id == 0) {
        $res = $this->Conexion->insertar('empresa_metodos_pago', $metodo) > 0;
    } else {
        // Actualiza método existente
        $where['id'] = $metodo->id;
        $res = $this->Conexion->modificar('empresa_metodos_pago', $metodo, null, $where) >= 0;
    }

    if($res) {
        echo "1"; // Éxito
    }
}

    // Elimina un método de pago por ID
function ajax_deletePagos() {
    $id = $this->input->post('id');      // ID del método a eliminar
    $where['id'] = $id;

    if($this->Conexion->eliminar('empresa_metodos_pago', $where)) {
        echo "1"; // Éxito
    }
}

//////////// R E Q U E R I M I E N T O S ////////////

// Vista del evaluador predeterminado de requerimientos
function evaluador_requerimiento() {
    $this->load->view('header');                                           // Carga el header
    $this->load->view('configuracion/requerimientos/evaluador');          // Vista del evaluador
}

// Obtiene el evaluador predeterminado desde la configuración
function ajax_getEvaluador() {
    $query = "SELECT evaluador_default FROM requerimientos_config WHERE id = 1";
    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res); // Devuelve resultado en formato JSON
}

// Establece un nuevo evaluador predeterminado
function ajax_setEvaluador() {
    $datos['evaluador_default'] = $this->input->post('id'); // ID del nuevo evaluador

    // Actualiza el campo evaluador_default donde id = 1
    $res = $this->Conexion->modificar('requerimientos_config', $datos, null, array('id' => 1)) >= 0;

    if($res) {
        echo "1"; // Éxito
    }
}

// Guarda o actualiza preferencias de notificaciones del usuario
function guardarNot() {
    $query = "SELECT * FROM notificaciones WHERE idus = " . $this->session->id;
    $res = $this->Conexion->consultar($query);

    if (empty($res)) {
        // Si no existen preferencias, se insertan
        $not = array(
            'idUs' => $this->session->id,
            'qr' => $this->input->post('qr'),
            'tickets' => $this->input->post('tickets'),
            'pr' => $this->input->post('pr'),
            'po' => $this->input->post('po'),
            'cotizaciones' => $this->input->post('cot'),
            'facturas' => $this->input->post('fact'),
            'agenda' => $this->input->post('agenda'),
            'tool' => $this->input->post('tool'),
        );
        $this->privilegios_model->guardarNot($not);
    } else {
        // Si ya existen, se actualizan
        $not = array(
            'qr' => $this->input->post('qr'),
            'tickets' => $this->input->post('tickets'),
            'pr' => $this->input->post('pr'),
            'po' => $this->input->post('po'),
            'cotizaciones' => $this->input->post('cot'),
            'facturas' => $this->input->post('fact'),
            'agenda' => $this->input->post('agenda'),
            'tool' => $this->input->post('tool'),
        );
        $this->privilegios_model->updateguardarNot($not);
    }

    // Redirige a la vista de notificaciones
    redirect(base_url('configuracion/notificaiones'));
}

// Inserta o actualiza un texto de correo
function ajax_setTexto_correo() {
    $id = 0;
    if($this->input->post('id')) {
        $id = $this->input->post('id'); // ID del texto a modificar
    }

    $datos['texto'] = $this->input->post('texto');      // Contenido del texto
    $datos['activo'] = $this->input->post('activo');    // Estado (activo/inactivo)
    $datos['id_us'] = $this->session->id;               // ID del usuario que lo modifica

    $res = FALSE;

    if($id == 0) {
        // Inserta nuevo texto
        $res = $this->Conexion->insertar('texto_correo', $datos) > 0;
    } else {
        // Actualiza texto existente
        $where['id'] = $id;
        $res = $this->Conexion->modificar('texto_correo', $datos, null, $where) >= 0;
    }

    if($res) {
        echo "1"; // Éxito
    }
}

// "Elimina" un texto de correo marcándolo como inactivo
function ajax_deleteTexto() {
    $id = $this->input->post('id');      // ID del texto a desactivar
    $where['id'] = $id;
    $datos['activo'] = 0;                // Lo marca como inactivo

    if($this->Conexion->modificar('texto_correo', $datos, null, $where)) {
        echo "1"; // Éxito
    }
}
