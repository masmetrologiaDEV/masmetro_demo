<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Toolcrib extends CI_Controller {
    function __construct() {
    parent::__construct();
    $this->load->library('correos_po');
    $this->load->model('Tool_model');
}

function tool_crib() {
    $this->load->model('tool_model');
    $this->load->model('privilegios_model');

    // Obtiene productos solicitados para el módulo ToolCrib
    $datos['productos'] = $this->tool_model->ProdPedidos();

    // Lista de usuarios con rol de jefe para asignaciones
    $datos['usuarios'] = $this->privilegios_model->listadoJefes();

    // Información de venta temporal (posible carrito o pre-pedido)
    $datos['venta'] = $this->tool_model->ventatemp();

    $this->load->view('header');
    $this->load->view('toolcrib/toolcrib', $datos);
}

function pedidos() {
    $this->load->model('tool_model');

    // Obtiene pedidos registrados en el sistema
    $datos['pedido'] = $this->tool_model->pedidos();

    $this->load->view('header');
    $this->load->view('toolcrib/pedido', $datos);
}

function producto_nuevo() {
    $this->load->model('tool_model');
    $this->load->view('header');
    $this->load->view('toolcrib/productoNuevo');
}

function inventario() {
    $this->load->model('tool_model');

    // Consulta todos los productos registrados en inventario
    $data['productos'] = $this->tool_model->productos();

    $this->load->view('header');
    $this->load->view('toolcrib/inventario', $data);
}

function movimientos() {
    $this->load->model('tool_model');

    // Historial de movimientos de productos (entradas/salidas)
    $data['movimientos'] = $this->tool_model->movimientos();
    $this->load->view('header');
    $this->load->view('toolcrib/movimientos', $data);
}

    function verProducto($idp) {
    $this->load->model('tool_model');

    // Datos principales del producto seleccionado
    $datosProd = $this->tool_model->getProds($idp);
    $data['codigo'] = $datosProd->codigo;
    $data['producto'] = $datosProd->producto;

    // Detalles adicionales: movimientos, cantidad actual y ubicación
    $data['productos'] = $this->tool_model->getProd($idp);
    $data['movimientos'] = $this->tool_model->movimientosProd($idp);
    $data['qty'] = $this->tool_model->prodCant($idp);
    $data['ubicacion'] = $this->tool_model->ubicaciones($idp);

    $this->load->view('header');
    $this->load->view('toolcrib/ver_Prod', $data);
}

function cancelarProducto($idp) {
    $this->load->model('tool_model');

    // Cancela un producto del inventario por ID
    $this->tool_model->cancelarProducto($idp);

    // Redirige al listado principal
    redirect(base_url('toolcrib/tool_crib'));
}

function modificarProd($idp) {
    $this->load->model('tool_model');

    // Obtiene datos actuales del producto
    $datosProd = $this->tool_model->getProds($idp);
    $data['codigo'] = $datosProd->codigo;
    $data['producto'] = $datosProd->producto;

    // Detalles para modificación: historial, ubicación
    $data['productos'] = $this->tool_model->getProd($idp);
    $data['movimientos'] = $this->tool_model->movimientosProdCom($idp);
    $data['ubicacion'] = $this->tool_model->ubicaciones($idp);

    $this->load->view('header');
    $this->load->view('toolcrib/modProd', $data);
}

function EditarPedido($idp) {
    $this->load->model('tool_model');

    // Obtiene detalle del pedido a editar
    $datosProd = $this->tool_model->getDetalleVent($idp);
    $data['producto'] = $datosProd->idProd;
    $data['producto'] = $datosProd->cantidad;
    $idProd = $datosProd->idProd;

    // Carga detalle del pedido y ubicaciones disponibles para ajuste
    $data['productos'] = $this->tool_model->DetalleVent($idp);
    $data['ubi'] = $this->tool_model->ubicacionAjuste($idProd);

    $this->load->view('header');
    $this->load->view('toolcrib/editarPedido', $data);
}

    function EditarProd($idp) {
    $this->load->model('tool_model');
    
    $datosub = $this->tool_model->ubicacion($idp);
    
    // Obtiene datos del producto y ubicaciones para edición
    $data['productos'] = $this->tool_model->getProd($idp);
    $data['ubicaciones'] = $this->tool_model->locacion($idp);
    
    $this->load->view('header');
    $this->load->view('toolcrib/editar_Prod', $data);
}

function verPedido($idVenta) {
    $this->load->model('tool_model');
    $this->load->model('privilegios_model');
    
    // Prepara datos para visualización detallada de pedido
    $data['privilegios'] = $this->privilegios_model->listadoPuestos();
    $venta = $this->tool_model->getventaDet($idVenta);
    $data['idVenta'] = $venta->idToolCrib;
    $data['pedido'] = $this->tool_model->getPedido($idVenta);
    
    $this->load->view('header');
    $this->load->view('toolcrib/verPedido', $data);
}

function aprobarPedido($idVenta) {
    $this->load->model('tool_model');
    
    // Recupera información necesaria para aprobación de pedido
    $venta = $this->tool_model->getventaDet($idVenta);
    $data['idVenta'] = $venta->idToolCrib;
    $data['estatus'] = $venta->estatus;
    $data['pedido'] = $this->tool_model->getPedido($idVenta);
    
    $this->load->view('header');
    $this->load->view('toolcrib/aprobar', $data);
}

    function reporte() {
    $this->load->model('tool_model');
    // Genera reporte de pedidos
    $data['pedidos'] = $this->tool_model->reporte();
    $this->load->view('header');
    $this->load->view('toolcrib/reporte', $data);
}

function registrarVenta() {
    $this->load->model('tool_model');
    
    // Prepara y registra venta temporal
    $datos = array(
        'idUs' => $this->session->id,
        'idProd' => $this->input->post('producto'),
        'cantidad' => $this->input->post('cantidad'),
    );
    
    $res = $this->tool_model->registrarVentaTemp($datos);
    redirect(base_url('toolcrib/tool_crib'));
}

function registrarProducto() {
    $this->load->model('tool_model');
    
    // Registra nuevo producto con datos del formulario
    $data = array(
        'codigo' => $this->input->post('codigo'),
        'categoria' => $this->input->post('categoria'),
        'producto' => $this->input->post('producto'),
        'descripcion' => $this->input->post('descripcion'),
        'proveedor' => $this->input->post('proveedor'),
        'marca' => $this->input->post('marca'),
        'modelo' => $this->input->post('modelo'),
        'precio' => $this->input->post('precio'),
        'um' => $this->input->post('um'),
        'cantMax' => $this->input->post('cantMax'),
        'cantMin' => $this->input->post('cantMin'),
        'estatus' => '1',
    );
    
    $id_inserted = $this->tool_model->registrarProducto($data);
    redirect(base_url('toolcrib/producto_nuevo'));
}

    function actualizarProducto() {
    // Actualiza producto y registra movimiento de modificación
    $idp = $this->input->post('idp');
    $check = $this->input->post('estatus');
    
    // Determina estatus basado en checkbox
    $estatus = ($check == 1) ? '1' : '0';
    
    // Prepara datos de actualización
    $data = array(
        'codigo' => $this->input->post('codigo'),
        'categoria' => $this->input->post('categoria'),
        'producto' => $this->input->post('producto'),
        'descripcion' => $this->input->post('descripcion'),
        'proveedor' => $this->input->post('proveedor'),
        'marca' => $this->input->post('marca'),
        'modelo' => $this->input->post('modelo'),
        'precio' => $this->input->post('precio'),
        'um' => $this->input->post('um'),
        'cantMax' => $this->input->post('cantMax'),
        'cantMin' => $this->input->post('cantMin'),
        'estatus' => $estatus,
    );
    
    $this->load->model('tool_model');
    $this->tool_model->updateProd($idp, $data);
    
    // Registra movimiento de modificación
    $mov = array(
        'idProd' => $idp,
        'idus' => $this->session->id,
        'cantidad' => '0',
        'local' => 'N/A',
        'tipo' => 'MODIFICACION',
        'comentario' => $this->input->post('comentario'),
        'fecha' => date('Y-m-d'),
    );
    $this->tool_model->registrarMov($mov);

    redirect(base_url('toolcrib/inventario'));
}

    function editarProducto() {
    $this->load->model('tool_model');
    $this->load->model('conexion_model', 'Conexion');
    $idp = $this->input->post('idProducto');
    $qty = $this->input->post('cantidad');
    $ubicacion = $this->input->post('ubicacion');
    $stock = $this->input->post('stock');
    $qtyF = $stock + $qty;

    // Verifica si la ubicación ya está registrada para este producto
    $query = "SELECT * from ubiProds where ubicacion ='".$ubicacion."' and idProd='".$idp."'";
    $result = $this->db->query($query)->result_array();
    
    if ($result) {
        // Muestra alerta si la ubicación ya existe
        echo '<script type="text/javascript">'; 
        echo 'alert("Ubicacion ya ha sido registraad");'; 
        echo 'window.location = "verProducto/'.$idp.'"';
        echo '</script>';
    } else {
        // Registra nueva ubicación para el producto
        $data = array(
            'idProd' => $this->input->post('idProducto'),
            'ubicacion' => $this->input->post('ubicacion'),
            'cantidad' => $this->input->post('cantidad'),
        );
        $this->tool_model->registrarubicacion($data);

        // Registra movimiento de entrada
        $mov = array(
            'idProd' => $idp,
            'idUs' => $this->session->id,
            'cantidad' => $qty,
            'local' => $ubicacion,
            'tipo' => 'ENTRADA',
            'comentario' => $this->input->post('comentario'),
            'fecha' => date('Y-m-d'),
        );
        $this->tool_model->registrarMov($mov);

        redirect(base_url('toolcrib/verProducto/'.$idp));
    }
}

function updateProducto() {
    $this->load->model('tool_model');
    $idp = $this->input->post('idProducto');
    $stock = $this->input->post('stock');
    $qty = $this->input->post('cantidad');
    $qtyF = $stock + $qty;
    $ubi = $this->input->post('ubicacion');

    // Actualiza cantidad en ubicación existente
    $data = array(
        'cantidad' => $qtyF,
    );
    $id_inserted = $this->tool_model->updateProducto($idp, $ubi, $data); 
    // Registra movimiento de entrada
    $mov = array(
        'idProd' => $idp,
        'idUs' => $this->session->id,
        'cantidad' => $qty,
        'local' => $ubi,
        'tipo' => 'ENTRADA',
        'comentario' => $this->input->post('comentario'),
        'fecha' => date('Y-m-d'),
    );
    $this->tool_model->registrarMov($mov);

    redirect(base_url('toolcrib/verProducto/'.$idp));
}

    function eliminarProducto() {
    // Elimina producto solo si su cantidad es cero
    $idp = trim($this->input->post('idProducto'));
    $datosP = $this->tool_model->getProds($idp);
    
    $qty = $data['cantidad'] = $datosP->cantidad;
    
    // Verifica si el producto tiene cantidad cero
    if ($qty == 0) {
        $data = array(
            'idProducto' => $idp,
        );

        $res = $this->tool_model->deleteProducto($data);
        
        // Retorna respuesta para AJAX
        if ($res) {
            echo "1";
        } else {
            echo "";
        }
    }
}

     function registrarPedido() {
    $this->load->model('tool_model');
    $this->load->model('privilegios_model');
    $this->load->library('correos');
    
    // Maneja registro de pedido con diferentes flujos de aprobación
    $op = $this->input->post('apro');
    $apro = $this->input->post('aprobador');
    $fecha = date("Y/m/d");
    
    // Usuario con privilegios de autorización
    if ($this->session->privilegios['autorizarTC']) {
        $data = array(
            'idUs' => $this->session->id,
            'aprobador' => $this->session->id,
            'estatus' => "APROBADO",
            'fecha' => $fecha,
        );
        $id_inserted = $this->tool_model->registrarDetVenta($data);
    } else {
        // Usuario sin privilegios: requiere aprobación externa
        $consulta = 'SELECT u.autorizadorTC, a.correo from usuarios u JOIN usuarios a on u.autorizadorTC=a.id WHERE u.id='.$this->session->id.'';
        $r = $this->Conexion->consultar($consulta, TRUE);
        
        $data = array(
            'idUs' => $this->session->id,
            'aprobador' => $r->autorizadorTC,
            'estatus' => "PENDIENTE",
            'fecha' => $fecha,
        );
        $id_inserted = $this->tool_model->registrarDetVenta($data);
        
        // Envía correo de notificación
        $correo = array(
            'nombre' => $this->session->nombre,
            'mail' => $r->correo,
            'fecha' => $fecha,
            'tool' => $id_inserted,
        );
        $this->correos->registrar_tool($correo);
    }
    
    // Recupera ID del pedido recién creado
    $consulta = 'SELECT * from VentToolCrib WHERE idToolCrib =(SELECT MAX(idToolCrib) from VentToolCrib WHERE idUs ='.$this->session->id.');';
    $resV = $this->Conexion->consultar($consulta);
    foreach ($resV as $valV) {
        $idToolCrib = $valV->idToolCrib;
    }
    
    // Transfiere items temporales al pedido permanente
    $query = 'SELECT * from VentTCTemp where idUs ='.$this->session->id;
    $res = $this->Conexion->consultar($query);
    
    foreach ($res as $val) {
        $data = array(
            'idVenta' => $idToolCrib,
            'idUs' => $val->idUs,
            'idProd' => $val->idProd,
            'cantidad' => $val->cantidad,
            'estatus' => 'PENDIENTE'
        );
        $id_inserted = $this->tool_model->registrarVentaDet($data);
        $this->tool_model->delVentTemp();
    }
    
    redirect(base_url('toolcrib/tool_crib'));
}

    function entregarPedido() {
    $this->load->library('correos');
    $this->load->model('tool_model');
    
    // Proceso de entrega de pedido con notificación por correo
    $idVenta = $this->input->post('idVenta');
    $prod = $this->input->post('prod');
    $cant = $this->input->post('cant');
    $idVD = $this->input->post('idVenta');
    $idP = $this->input->post('idProd');

    $venta = $this->tool_model->getventaDet($idVenta);
    $p = $data['idVenta'] = $venta->idToolCrib;
    $idUs = $data['idVenta'] = $venta->idUs;
    $correo = $this->tool_model->correoJefe($idUs, $p);
    $correoJefe = $data['correo'] = $correo->correo;
    
    $pedido = $this->tool_model->getPedido($idVenta);
    
    $mail['pedido'] = $p;
    $mail['pedidos'] = $this->tool_model->getPedido($idVenta);
    $mail['correo'] = $correoJefe;

    // Envía notificación por correo electrónico
    $this->correos->entregarPedido($mail);

    // Actualiza estado del pedido a ENTREGADO
    $data = array('estatus' => 'ENTREGADO');
    $res = $this->tool_model->updateVenta($idVenta, $data);

    // Retorna respuesta JSON y redirecciona
    if ($res) {
        echo json_encode($data);
    } else {
        echo "";
    }
    redirect(base_url('toolcrib/pedidos'));
}

    function excel() {
    // Genera reporte de inventario en formato Excel
    $txt = $this->input->post('txtBuscar');
    $opc = $this->input->post('rbBusqueda');

    $query = 'SELECT p.idProducto as CODIGO, p.producto as PODUCTO, p.descripcion AS DESCRIPCION, p.proveedor as PROVEEDOR, p.marca AS MARCA, p.modelo AS MODELO, p.precio AS PRECIO, p.um AS UNIDAD_DE_MEDIDA,u.ubicacion as LOCAL, u.cantidad AS STOCK FROM productos p JOIN ubiProds u on p.idProducto=u.idProd';

    // Construye consulta con filtros dinámicos
    if (!empty($txt)) {
        if ($opc == "prod") {
            $query .= " where producto like '$txt'";
        } else if ($opc == "marca") {
            $query .= " where marca like '$txt'";
        } else if ($opc == "prov") {
            $query .= " where proveedor like '$txt'";
        }
    }
    $result = $this->db->query($query)->result_array();

    // Prepara y descarga archivo Excel
    $timestamp = date('m/d/Y', time());
    $filename = 'inventario_'.$timestamp.'.xls';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $isPrintHeader = false;

    // Imprime filas de datos
    foreach ($result as $row) {
        if (!$isPrintHeader) {
            echo implode("\t", array_keys($row)). "\n";
            $isPrintHeader = true;
        }
        echo implode("\t", array_values($row)). "\n";
    }

    exit;
}  

    function getProds() {
    // Obtiene productos filtrados por criterios de búsqueda
    $texto = $this->input->post('texto');
    $texto = trim($texto);
    $parametro = $this->input->post('parametro');

    $query = "SELECT * from productos";

    // Construye consulta con filtros dinámicos
    if (!empty($texto)) {
        if ($parametro == "prod") {
            $query .= " where producto like '$texto'";
        } else if ($parametro == "marca") {
            $query .= " where marca like '$texto'";
        } else if ($parametro == "prov") {
            $query .= " where proveedor like '$texto'";
        } else if ($parametro == "modelo") {
            $query .= " where modelo like '$texto'";
        }
    }

    // Ejecuta consulta y devuelve resultados en JSON
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function productos() {
    // Obtiene productos con stock total agrupado
    $texto = $this->input->post('texto');
    $texto = trim($texto);
    $parametro = $this->input->post('parametro');

    // Consulta base con suma de cantidades por ubicación
    $query = "SELECT p.*, SUM(u.cantidad) as cantidad FROM productos p JOIN ubiProds u ON p.idProducto=u.idProd ";
    
    // Aplica filtros de búsqueda si existen
    if (!empty($texto)) {
        if ($parametro == "prod") {
            $query .= " where producto like '$texto' ";
        } else if ($parametro == "codigo") {
            $query .= " where codigo like '$texto' ";
        }
    }
    $query .= " GROUP BY p.idProducto";

    // Ejecuta consulta y devuelve resultados en JSON
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function getRep() {
    // Genera reporte de ventas con múltiples criterios de filtrado
    $texto = $this->input->post('texto');
    $texto = trim($texto);
    $parametro = $this->input->post('parametro');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');

    // Consulta base con joins entre tablas
    $query = "SELECT dv.*, v.fecha, p.marca, p.proveedor, p.producto, p.precio, concat(u.nombre, ' ', u.paterno) as nombre, u.no_empleado FROM detalleVentTC dv JOIN usuarios u ON dv.idUs=u.id JOIN VentToolCrib v ON dv.idVenta=v.idToolCrib JOIN productos p ON dv.idProd=p.idProducto ";
    
    // Aplica filtros según parámetros seleccionados
    if ($parametro == "date") {
        $query .= " where v.fecha BETWEEN '".$fecha1."' and '".$fecha2."'";
    }
    if (!empty($texto)) {
        if ($parametro == "prod") {
            $query .= " where p.producto like '$texto'";
        } else if ($parametro == "marca") {
            $query .= " where p.marca like '$texto'";
        } else if ($parametro == "prov") {
            $query .= " where p.proveedor like '$texto'";
        } else if ($parametro == "user") {
            $query .= " where u.nombre like '$texto'";
        }
    }

    // Ejecuta consulta y devuelve resultados en JSON
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}
    function mov()
    {
       /* $texto = $this->input->post('texto');
        $texto = trim($texto);
        $parametro = $this->input->post('parametro');*/
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');
        $f1=strval($fecha1);
        $f2=strval($fecha2);


        $query = "SELECT m.*, concat(u.nombre, ' ', u.paterno) as nombre, p.producto FROM movimientosTool m JOIN usuarios u ON u.id=m.idus JOIN productos p ON p.idProducto=m.idProd ";
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .="where m.fecha BETWEEN '".$f1."' AND '".$f2."' ";
        }
        $query .=" ORDER BY m.fecha DESC";

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
    function excelRep() {
    // Genera reporte de ventas en formato Excel con múltiples filtros
    $texto = $this->input->post('txtBuscar');
    $parametro = $this->input->post('rbBusqueda');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = strval($fecha1);
    $f2 = strval($fecha2);

    // Consulta base para reporte de ventas detallado
    $query = "SELECT dv.*, v.fecha, p.marca, p.proveedor, p.producto, p.precio, concat(u.nombre, ' ', u.paterno) as nombre, u.no_empleado FROM detalleVentTC dv JOIN usuarios u ON dv.idUs=u.id JOIN VentToolCrib v ON dv.idVenta=v.idToolCrib JOIN productos p ON dv.idProd=p.idProducto ";
    
    // Aplica filtros según parámetros seleccionados
    if (!empty($texto)) {
        if ($parametro == "prod") {
            $query .= " where p.producto like '$texto'";
        } else if ($parametro == "marca") {
            $query .= " where p.marca like '$texto'";
        } else if ($parametro == "prov") {
            $query .= " where p.proveedor like '$texto'";
        } else if ($parametro == "user") {
            $query .= " where u.nombre like '$texto'";
        }
    } else if ($parametro = "date") {
        $query .= " where v.fecha BETWEEN '$f1' and '$f2'";
    }

    // Prepara y descarga archivo Excel
    $result = $this->db->query($query)->result_array();
    $timestamp = date('m/d/Y', time());
    $filename = 'Reporte_'.$timestamp.'.xls';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    
    $isPrintHeader = false;

    // Imprime filas de datos
    foreach ($result as $row) {
        if (!$isPrintHeader) {
            echo implode("\t", array_keys($row)). "\n";
            $isPrintHeader = true;
        }
        echo implode("\t", array_values($row)). "\n";
    }

    exit;
}

    function ajuesteInventario() {
    $this->load->model('tool_model');
    
    // Realiza ajustes de inventario por entrega de productos
    $idV = $this->input->post('idV');
    $idDV = $this->input->post('idDV');
    $idProd = $this->input->post('idProd');
    $producto = $this->input->post('producto');
    $ubi = $this->input->post('ubicacion');
    $qty = $this->input->post('cantidad');
    
    // Obtiene stock actual en ubicación
    $datos = $this->tool_model->getUbi($idProd, $ubi);
    $cant = $datos->cantidad;
    $qtyF = $cant - $qty;

    // Verifica disponibilidad suficiente
    if ($qty > $cant) {
        echo '<script type="text/javascript">'; 
        echo 'alert("No hay suficientes productos en la local, elija otra local o modifique la cantidad a entregar");'; 
        echo 'window.history.back();';
        echo '</script>';
    } else {
        // Actualiza detalle de venta a ENTREGADO
        $data = array(
            'cantidad' => $qty,
            'estatus' => 'ENTREGADO'  
        );
        $this->tool_model->ajusteDetalle($idDV, $data);

        // Actualiza stock en ubicación
        $data = array('cantidad' => $qtyF);
        $this->tool_model->ajuste($idProd, $ubi, $data);

        // Registra movimiento de salida
        $mov = array(
            'idProd' => $idProd,
            'idUs' => $this->session->id,
            'cantidad' => $qty,
            'local' => $ubi,
            'tipo' => 'SALIDA',
            'comentario' => $this->input->post('comentario'),
            'fecha' => date('Y-m-d'),
        );
        $this->tool_model->registrarMov($mov);

        redirect(base_url('toolcrib/verPedido/'.$idV));
    }
}

    function aprobPedido() {
    $this->load->model('tool_model');
    
    // Aprueba un pedido cambiando su estado a APROBADO
    $idVenta = $this->input->post('idVenta');
    $prod = $this->input->post('prod');
    $cant = $this->input->post('cant');
    $idVD = $this->input->post('idVenta');
    $idP = $this->input->post('idProd');

    // Prepara datos de actualización
    $data = array(
        'estatus' => 'APROBADO'
    );
    $res = $this->tool_model->aprobar($idVenta, $data);

    // Retorna respuesta JSON y redirecciona
    if ($res) {
        echo json_encode($data);
    } else {
        echo "";
    }
    redirect(base_url('inicio'));
}

    function excelMov() {
    // Genera reporte de movimientos en formato Excel filtrado por fechas
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = strval($fecha1);
    $f2 = strval($fecha2);

    // Consulta base para movimientos de inventario
    $query = "SELECT m.tipo, concat(u.nombre, ' ', u.paterno) as nombre, p.producto, m.local, m.cantidad, m.fecha, m.comentario FROM movimientosTool m JOIN usuarios u ON u.id=m.idus JOIN productos p ON p.idProducto=m.idProd ";
    
    // Aplica filtro de rango de fechas si está presente
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " where m.fecha BETWEEN '$f1' and '$f2'";
    }
    
    // Prepara y descarga archivo Excel
    $result = $this->db->query($query)->result_array();
    $timestamp = date('m/d/Y', time());
    $filename = 'movimientos_'.$timestamp.'.xls';
    header("Content-type: application/vnd-ms-xls; name='excel'");
    header('Content-Disposition: attachment; filename='.$filename);
    
    $isPrintHeader = false;

    // Imprime filas de datos con retorno de carro
    foreach ($result as $row) {
        if (!$isPrintHeader) {
            echo implode("\t", array_keys($row)). "\r";
            $isPrintHeader = true;
        }
        echo implode("\t", array_values($row)). "\r";
    }

    exit;
}

function autorizadores() {
    $this->load->model('conexion_model', 'Conexion');
    
    // Obtiene lista de usuarios autorizadores activos
    $query = "SELECT U.id, concat(U.nombre,' ',U.paterno,' ',U.materno) as Name from usuarios U inner join privilegios P on P.usuario = U.id where U.activo = 1 and P.autorizarTC = 1";
    
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}
}
