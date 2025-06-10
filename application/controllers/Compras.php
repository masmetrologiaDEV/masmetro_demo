<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Compras para gestionar requisiciones, QR, y flujos relacionados
class Compras extends CI_Controller {

    // Constructor: carga modelos, librerías y helpers necesarios
    function __construct() {
        parent::__construct();
        $this->load->model('compras_model','Modelo');            // Modelo principal de compras
        $this->load->model('MLConexion_model', 'MLConexion');    // Modelo para conexión con ML
        $this->load->model('descargas_model');                   // Modelo para descargas
        $this->load->model('conexion_model', 'Conexion');        // Modelo de conexión general

        $this->load->helper('download');                         // Helper para descargas
        $this->load->library('correos');                         // Librería de correos (general)
        $this->load->library('correos_pr');                      // Librería de correos para PR
        $this->load->library('AOS_funciones');                   // Funciones auxiliares AOS
    }

    // Carga vista para generar una nueva QR
    function generar_qr() {
        $data['intervalo'] = $this->Modelo->intervalos();       // Obtiene intervalos disponibles
        $this->load->view('header');
        $this->load->view('compras/generar_qr', $data);         // Vista para generación de QR
    }

    // Muestra los detalles de un QR específico
    function ver_qr($id) {
        $data['comentarios'] = $this->Modelo->verQr_comentarios($id);          // Comentarios del QR
        $data['comentarios_fotos'] = $this->Modelo->verQr_comentarios_fotos($id); // Fotos relacionadas
        $data['conceptos'] = $this->Modelo->conceptos();                       // Catálogo de conceptos
        $data['qr'] = $this->Modelo->getDetalleQR($id);                        // Detalle del QR

        // Consulta la fecha del rechazo si aplica
        $fecha_rechazo = $this->Conexion->consultar(
            "SELECT b.fecha FROM requisiciones_cotizacion qr 
             JOIN bitacora_qrs b ON qr.id = b.qr 
             WHERE b.estatus = 'RECHAZADO' AND b.qr = " . $id, true
        );
        $data['fecha_rechazo'] = $fecha_rechazo;

        // Define estilo del botón según estatus del QR
        switch ($data['qr']->estatus) {
            case 'ABIERTO':
                $data['btn_estatus'] = "btn-primary";
                break;
            case 'LIBERADO':
            case 'COMPRA APROBADA':
                $data['btn_estatus'] = "btn-success";
                break;
            case 'COTIZANDO':
                $data['btn_estatus'] = "btn-warning";
                break;
            case 'CANCELADO':
                $data['btn_estatus'] = "btn-default";
                break;
            case 'RECHAZADO':
            case 'COMPRA RECHAZADA':
                $data['btn_estatus'] = "btn-danger";
                break;
            default:
                break;
        }

        $this->load->view('header');
        $this->load->view('compras/ver_qr', $data);             // Vista para ver QR
    }

    // Carga la vista para editar un QR específico
    function editar_qr($id) {
        $data['qr'] = $this->Modelo->getDetalleQR($id);         // Datos del QR a editar
        $data['intervalo'] = $this->Modelo->intervalos();       // Intervalos disponibles
        // $this->load->view('debug', $data); // Para depuración (comentado)
        $this->load->view('header');
        $this->load->view('compras/editar_qr', $data);
    }

    // Carga la vista para clonar un QR específico
    function clonar_qr($id) {
        $data['qr'] = $this->Modelo->getDetalleQR($id);         // Datos del QR original
        $data['intervalo'] = $this->Modelo->intervalos();       // Intervalos disponibles
        $this->load->view('header');
        $this->load->view('compras/clonar_qr', $data);
    }

    // Muestra listado de requisiciones filtradas por estatus o ID
    function requisiciones($estatus = 'TODO', $id = "") {
        $data['estatus'] = strtoupper($estatus);                        // Estatus seleccionado
        $data['qr'] = $id;                                              // ID del QR (si aplica)
        $data['asignado'] = $this->Modelo->usuariosCompras();          // Usuarios del área de compras
        $data['otros_aprobadores'] = $data['estatus'] == 'TODO' ? '' : 'unchecked'; // Filtro de vista

        $this->load->view('header');
        $this->load->view('compras/catalogo_qr', $data);               // Vista del catálogo
    }

    // Muestra la vista con las requisiciones del usuario actual
    function mis_qrs() {
        $this->load->view('header');
        $this->load->view('compras/mis_qrs');
    }

        // Genera una nueva QR con datos recibidos vía POST (JSON + atributos)
    function ajax_generarQR() {
        $_info = json_decode($this->input->post('info'));       // Información general en formato JSON
        $_atributos = $this->input->post('atributos');          // Atributos específicos

        $unidad = "";
        $clave_unidad = "";
        $intervalo = null;

        // Si hay intervalo definido, se asigna
        if (!empty($_info->intervalo)) {
            $intervalo = $_info->intervalo;
        }

        // Si el tipo es 'PRODUCTO', se extraen unidad y clave, y se ignora el intervalo
        if ($_info->tipo == 'PRODUCTO') {
            $unidad = $_info->unidad;
            $clave_unidad = $_info->clave_unidad;
            $intervalo = null;
        }

        // Preparación de datos para guardar la QR
        $info = array(
            'usuario' => $this->session->id,
            'archivo' => "0",
            'nombre_archivo' => "",
            'tipo' => $_info->tipo,
            'subtipo' => $_info->subtipo,
            'cantidad' => $_info->cantidad,
            'cantidad_aprobada' => 0,
            'unidad' => $unidad,
            'clave_unidad' => $clave_unidad,
            'descripcion' => $_info->descripcion,
            'prioridad' => $_info->prioridad,
            'lugar_entrega' => $_info->lugar_entrega,
            'comentarios' => $_info->comentarios,
            'critico' => $_info->critico,
            'destino' => $_info->destino,
            'atributos' => $_atributos,
            'notificaciones' => $_info->notificaciones,
            'estatus' => 'ABIERTO',
            'especificos' => $_info->especificos,
            'intervalo' => $intervalo
        );

        // Si es tipo PRODUCTO y tiene tipo de calibración, se añade
        if ($_info->tipo == 'PRODUCTO' && isset($_info->tipocalibracion)) {
            $info['tipocalibracion'] = $_info->tipocalibracion;
        }

        $res = $this->Modelo->generarQR($info); // Se crea la QR en la base de datos

        // Registra movimiento en bitácora de QR
        $bitacoraQR = array(
            'qr' => intval($res),
            'user' => $this->session->id,
            'estatus' => 'ABIERTO'
        );
        $this->Modelo->estatusQR($bitacoraQR);

        // Si la creación fue exitosa
        if ($res) {
            $datos = array(
                'id' => $res,
                'fecha' => date('d/m/Y h:i A'),
                'usuario' => $this->session->nombre,
                'cantidad' => $_info->cantidad,
                'unidad' => $unidad,
                'descripcion' => $_info->descripcion,
                'atributos' => $_atributos,
                'prioridad' => $_info->prioridad,
                'comentarios' => $_info->comentarios,
                'correos' => array_merge(
                    array($this->session->correo),
                    $this->Modelo->getCorreosQR()
                )
            );

            // Solo se envía correo si la prioridad no es NORMAL
            if ($_info->prioridad != 'NORMAL') {
                $this->correos->creacionQR($datos);
            }

            echo $res; // Devuelve el ID de la nueva QR
        }
    }

    // Prueba: imprime lista de correos configurados para QR
    function test() {
        print_r($this->Modelo->getCorreosQR());
    }

    // Edita una QR existente con nueva información recibida vía POST
    function ajax_editarQR() {
        $_info = json_decode($this->input->post('info'));       // Datos principales
        $_atributos = $this->input->post('atributos');          // Atributos editados

        $unidad = "";
        $clave_unidad = "";
        $intervalo = null;
        $especificos = null;

        // Registra edición en bitácora
        $bitacoraQR = array(
            'qr' => intval($_info->id),
            'user' => $this->session->id,
            'estatus' => 'EDICION'
        );
        $this->Modelo->estatusQR($bitacoraQR);

        // Asigna intervalo si existe
        if (!empty($_info->intervalo)) {
            $intervalo = $_info->intervalo;
        }

        // Asigna específicos si existen
        if (!empty($_info->especificos)) {
            $especificos = $_info->especificos;
        }

        // Si el tipo es PRODUCTO, se ajustan los valores y se ignora el intervalo
        if ($_info->tipo == 'PRODUCTO') {
            $unidad = $_info->unidad;
            $clave_unidad = $_info->clave_unidad;
            $intervalo = null;
        }

        // Datos a actualizar en la QR
        $info = array(
            'id' => $_info->id,
            'usuario' => $this->session->id,
            'tipo' => $_info->tipo,
            'subtipo' => $_info->subtipo,
            'cantidad' => $_info->cantidad,
            'unidad' => $unidad,
            'clave_unidad' => $clave_unidad,
            'descripcion' => $_info->descripcion,
            'prioridad' => $_info->prioridad,
            'lugar_entrega' => $_info->lugar_entrega,
            'comentarios' => $_info->comentarios,
            'critico' => $_info->critico,
            'destino' => $_info->destino,
            'atributos' => $_atributos,
            'notificaciones' => $_info->notificaciones,
            'estatus' => 'ABIERTO',
            'especificos' => $especificos,
            'intervalo' => $intervalo
        );

        // Si tiene tipo de calibración y es producto, lo añade
        if ($_info->tipo == 'PRODUCTO' && isset($_info->tipocalibracion)) {
            $info['tipocalibracion'] = $_info->tipocalibracion;
        }

        // Si se escribió un comentario durante la edición, lo guarda
        if ($_info->coments) {
            $coments = array(
                'comentario' => $_info->coments,
                'usuario' => $this->session->id,
                'qr' => $_info->id
            );
            $this->Modelo->agregar_comentario($coments);
        }

        $res = $this->Modelo->editarQR($info); // Ejecuta la actualización

        // Si fue exitosa la edición
        if ($res) {
            $datos = array(
                'id' => $_info->id,
                'fecha' => date('d/m/Y h:i A'),
                'usuario' => $this->session->nombre,
                'cantidad' => $_info->cantidad,
                'unidad' => $unidad,
                'descripcion' => $_info->descripcion,
                'atributos' => $_atributos,
                'prioridad' => $_info->prioridad,
                'comentarios' => $_info->comentarios,
                'correos' => array_merge(
                    array($this->session->correo),
                    $this->Modelo->getCorreosQR()
                )
            );

            // Solo si la prioridad no es NORMAL se notifica por correo
            if ($_info->prioridad != 'NORMAL') {
                $this->correos->edicionQR($datos);
            }

            echo "1"; // Confirma éxito
        }
    }

        // Filtra y devuelve QRs según múltiples criterios recibidos vía POST (prioridad, tipo, estatus, fechas, texto, etc.)
    function ajax_getQRs() {
        $prioridad = json_decode($this->input->post('prioridad'));
        $tipo = json_decode($this->input->post('tipo'));

        $estatus = $this->input->post('estatus');
        $texto = $this->input->post('texto');
        $parametro = $this->input->post('parametro');
        $usuario = $this->input->post('usuario');
        $asignado = $this->input->post('asignado');
        $archivo = $this->input->post('archivo');
        $fecha1 = $this->input->post('fecha1');
        $fecha2 = $this->input->post('fecha2');

        $f1 = strval($fecha1) . ' 00:00:00';
        $f2 = strval($fecha2) . ' 23:59:59';

        // Consulta base con JOIN para obtener nombre del usuario
        $query = "SELECT R.id, R.fecha, R.usuario, R.prioridad, R.tipo, R.subtipo, R.cantidad, 
                         R.cantidad_aprobada, R.unidad, R.clave_unidad, R.descripcion, R.atributos, 
                         R.critico, R.destino, R.lugar_entrega, R.comentarios, R.estatus, R.asignado, 
                         CONCAT(U.nombre, ' ', U.paterno) AS User 
                  FROM requisiciones_cotizacion R 
                  LEFT JOIN usuarios U ON R.usuario = U.id 
                  WHERE 1 = 1";

        // Filtros opcionales según campos recibidos
        if ($estatus != 'TODO') {
            $query .= " AND R.estatus = '$estatus'";
        }

        if (!empty($asignado)) {
            $query .= " AND R.asignado = '$asignado'";
        }

        if ($usuario == '1') {
            $idUser = $this->session->id;
            $query .= " AND R.usuario = '$idUser'";
        }

        if (count($prioridad) > 0) {
            $query .= " AND (1 = 0";
            foreach ($prioridad as $value) {
                $query .= " OR R.prioridad = '$value'";
            }
            $query .= ")";
        }

        if (isset($tipo) && count($tipo) > 0) {
            $query .= " AND (1 = 0";
            foreach ($tipo as $value) {
                $query .= " OR R.tipo = '$value'";
            }
            $query .= ")";
        }

        // Control de acceso según privilegios para consumo interno o venta
        if ($this->session->privilegios['crear_qr_interno'] != $this->session->privilegios['crear_qr_venta']) {
            if ($this->session->privilegios['editar_qr'] == "0" && $this->session->privilegios['liberar_qr'] == "0") {
                if ($this->session->privilegios['crear_qr_interno'] == "1") {
                    $query .= " AND R.destino = 'CONSUMO INTERNO'";
                } else {
                    $query .= " AND R.destino = 'VENTA'";
                }
            }
        }

        // Filtro por texto libre según el parámetro seleccionado
        if (!empty($texto)) {
            switch ($parametro) {
                case 'folio':
                    $query .= " AND R.id = '$texto'";
                    break;
                case 'usuario':
                    $query .= " AND CONCAT(U.nombre, ' ', U.paterno) LIKE '%$texto%'";
                    break;
                case 'contenido':
                    $query .= " AND (R.descripcion LIKE '%$texto%' 
                                 OR UPPER(R.atributos->'$.marca') LIKE UPPER('%$texto%') 
                                 OR UPPER(R.atributos->'$.modelo') LIKE UPPER('%$texto%'))";
                    break;
            }
        }

        // Filtro por rango de fechas
        if (!empty($fecha1) && !empty($fecha2)) {
            $query .= " AND R.fecha BETWEEN '$f1' AND '$f2'";
        }

        // Si no es consulta de archivo histórico
        if ($archivo != 1) {
            $query .= " AND R.fecha > '2021-01-01 00:00:00'";
        }

        $query .= " ORDER BY R.fecha DESC";

        // Ejecuta la consulta
        $res = $this->Modelo->consulta($query);
        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

    // Devuelve todas las QRs creadas por un usuario específico
    function ajax_getMisQRs() {
        $user = $this->input->post('usuario');
        $res = $this->Modelo->getMisQrs($user);

        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

    // Devuelve los comentarios de una QR específica
    function ajax_getQRComentarios() {
        $qr = $this->input->post('qr');
        $res = $this->Conexion->consultar(
            "SELECT C.*, CONCAT(U.nombre, ' ', U.paterno, ' ', U.materno) AS User 
             FROM qr_comentarios C 
             INNER JOIN usuarios U ON U.id = C.usuario 
             WHERE C.qr = $qr"
        );

        if ($res) {
            echo json_encode($res);
        }
    }

    // Devuelve el detalle completo de una QR específica
    function ajax_getDetalleQR() {
        $id = $this->input->post('idQR');
        $res = $this->Modelo->getDetalleQR($id);

        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

    // Devuelve usuarios con privilegios específicos para recibir notificaciones de QR
    function ajax_getUsuariosQRNotificaciones() {
        $privilegio = $this->input->post('privilegio');
        $id = $this->input->post('id');

        // Consulta base
        $query = "SELECT U.id, CONCAT(U.nombre, ' ', U.paterno) AS Nombre, P.puesto AS Puesto, U.correo 
                  FROM usuarios U 
                  INNER JOIN puestos P ON U.puesto = P.id 
                  INNER JOIN privilegios PR ON PR.usuario = U.id 
                  WHERE U.activo = 1";

        // Filtro por ID o privilegio específico
        if ($id) {
            $query .= " AND U.id = '$id'";
        } else {
            $query .= " AND PR.$privilegio = 1";
        }

        $query .= " ORDER BY Nombre ASC";

        $res = $this->Conexion->consultar($query, $id);
        if ($res) {
            echo json_encode($res);
        }
    }

        // Devuelve los proveedores asignados a una QR específica
    function ajax_getProveedoresAsignados() {
        $idQR = $this->input->post('idQR');

        // Consulta con información de empresa, propuesta y asignador
        $query = "SELECT E.id, QP.id as idQP, P.entrega, E.nombre, QP.monto, QP.total, QP.moneda, 
                         QP.tiempo_entrega, QP.dias_habiles, QP.comentarios, QP.nominado, 
                         QP.seleccionado, QP.nombre_archivo, QP.vencimiento, QP.fechaAsignacion, 
                         CONCAT(U.nombre, ' ', U.paterno) AS Asignador 
                  FROM qr_proveedores QP 
                  INNER JOIN empresas E ON E.id = QP.empresa 
                  INNER JOIN proveedores P ON P.empresa = E.id 
                  LEFT JOIN usuarios U ON U.id = QP.asignador 
                  WHERE QP.qr = '$idQR'";

        $res = $this->Modelo->consulta($query);
        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

    // Devuelve las propuestas enviadas por proveedores para una QR específica
    function ajax_getPropuestas() {
        $idQR = $this->input->post('idQR');

        $query = "SELECT E.id, QP.id as idQP, P.entrega, P.rma_requerido, E.nombre, QP.monto, QP.total, 
                         QP.moneda, QP.tiempo_entrega, QP.dias_habiles, QP.comentarios, QP.nominado, 
                         QP.seleccionado, QP.nombre_archivo, QP.vencimiento, QR.cantidad, QR.descripcion, 
                         QR.tipo, QR.subtipo, IFNULL(JSON_UNQUOTE(atributos->'$.serie'), '') AS Serie, 
                         QR.nombre_archivoEjemplo, QR.id as QR 
                  FROM qr_proveedores QP 
                  INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                  INNER JOIN empresas E ON E.id = QP.empresa 
                  INNER JOIN proveedores P ON P.empresa = E.id 
                  WHERE QP.qr = '$idQR'";

        $res = $this->Modelo->consulta($query);
        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

    // Asigna un proveedor a una QR (registro inicial con valores por defecto)
    function ajax_setProveedor() {
        $data['qr'] = $this->input->post('idQR');
        date_default_timezone_set('America/Chihuahua');
        $data['fechaAsignacion'] = date('Y-m-d h:i:s');

        $data['empresa'] = $this->input->post('idProv');
        $data['total'] = "0";
        $data['dias_habiles'] = "1";
        $data['nominado'] = "0";
        $data['seleccionado'] = "0";
        $data['comentarios'] = "";
        $data['factor'] = "0";
        $data['asignador'] = $this->session->id;

        $res = $this->Modelo->setProveedor($data);
        if ($res) {
            echo "1";
        }
    }

    // Marca un proveedor como el seleccionado para una QR
    function ajax_setProveedorSugerido() {
        $qr = $this->input->post('qr');
        $qr_prov = $this->input->post('qr_prov');

        // Primero desmarca todos los proveedores de esa QR
        $query = "UPDATE qr_proveedores SET seleccionado = '0' WHERE qr = '$qr'";
        $this->Modelo->update($query);

        // Luego marca como seleccionado al proveedor indicado
        $query2 = "UPDATE qr_proveedores SET seleccionado = '1' WHERE id = '$qr_prov'";
        $this->Modelo->update($query2);

        echo "1";
    }

    // Elimina un proveedor asignado a una QR
    function ajax_eliminarProveedor() {
        $data['qr'] = $this->input->post('qr');
        $data['empresa'] = $this->input->post('empresa');

        $res = $this->Modelo->deleteProveedor($data);
        if ($res) {
            echo "1";
        }
    }

    // Guarda información actualizada de todos los proveedores de una QR
    function guardarProveedores() {
        $datos = json_decode($this->input->post('datos'), TRUE);

        // Se actualiza cada proveedor uno por uno
        foreach ($datos as $value) {
            $this->Modelo->updateProveedor($value);
        }

        $id = $datos[0]["id"];

        // Se actualiza el campo de máximo vencimiento en la tabla de requisiciones
        $this->Conexion->comando(
            "UPDATE requisiciones_cotizacion 
             SET maximo_vencimiento = (
                 SELECT MAX(vencimiento) 
                 FROM qr_proveedores 
                 WHERE qr = (
                     SELECT qr 
                     FROM qr_proveedores 
                     WHERE id = $id
                 )
             )"
        );

        echo "1";
    }

    // Sugiere proveedores según coincidencia en palabras clave (tags)
    function proveedoresSugeridos() {
        $tags = $this->input->post('tags');
        $arreglo = explode(" ", trim($tags));
        $arreglo = array_diff($arreglo, array("")); // Elimina vacíos

        // Consulta proveedores con coincidencias en tags
        $query = "SELECT E.id, E.nombre 
                  FROM empresas E 
                  INNER JOIN proveedores P ON P.empresa = E.id 
                  WHERE E.proveedor = 1 AND (1 != 1";

        foreach ($arreglo as $value) {
            $query .= " OR tags LIKE '%," . $value . ",%'";
        }

        $query .= ")";

        $res = $this->Modelo->consulta($query);
        if ($res) {
            echo json_encode($res);
        } else {
            echo "";
        }
    }

          // Sugiere proveedores según coincidencia con la marca en atributos
    function proveedoresSugeridosMarca() {
        $tags = $this->input->post('tags');

        $query = "SELECT E.id, E.nombre, IFNULL((
                      SELECT COUNT(QP.id) 
                      FROM qr_proveedores QP 
                      INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                      WHERE QP.monto > 0 
                        AND QP.empresa = E.id 
                        AND UPPER(QR.atributos->'$.marca') = UPPER('\"$tags\"')
                  ), 0) AS QtyQr 
                  FROM empresas E 
                  INNER JOIN proveedores P ON P.empresa = E.id 
                  WHERE E.proveedor = 1 
                    AND (1 != 1 OR tags LIKE '%,$tags,%') 
                    AND !ISNULL(P.entrega) 
                    AND JSON_LENGTH(P.tipo) > 0 
                    AND JSON_LENGTH(P.formas_pago) > 0";

        $query .= " UNION ";

        $query .= "SELECT E.id, E.nombre, IFNULL((
                      SELECT COUNT(QP.id) 
                      FROM qr_proveedores QP 
                      INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                      WHERE QP.monto > 0 
                        AND QP.empresa = E.id 
                        AND UPPER(QR.atributos->'$.marca') = UPPER('\"$tags\"')
                  ), 0) AS QtyQr 
                  FROM qr_proveedores QP 
                  INNER JOIN requisiciones_cotizacion QR ON QP.qr = QR.id 
                  INNER JOIN empresas E ON E.id = QP.empresa 
                  WHERE E.proveedor = 1 
                    AND QP.monto > 0 
                    AND UPPER(QR.atributos->'$.marca') = UPPER('\"$tags\"')";

        $res = $this->Modelo->consulta($query);
        echo $res ? json_encode($res) : "";
    }

    // Sugiere proveedores según coincidencia con modelo en atributos
    function proveedoresSugeridosModelo() {
        $tags = $this->input->post('tags');

        $query = "SELECT E.id, E.nombre, IFNULL((
                      SELECT COUNT(QP.id) 
                      FROM qr_proveedores QP 
                      INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                      WHERE QP.monto > 0 
                        AND QP.empresa = E.id 
                        AND UPPER(QR.atributos->'$.modelo') = UPPER('\"$tags\"')
                  ), 0) AS QtyQr 
                  FROM qr_proveedores QP 
                  INNER JOIN requisiciones_cotizacion QR ON QP.qr = QR.id 
                  INNER JOIN empresas E ON E.id = QP.empresa 
                  WHERE E.proveedor = 1 
                    AND QP.monto > 0 
                    AND UPPER(QR.atributos->'$.modelo') = UPPER('\"$tags\"')";

        $res = $this->Modelo->consulta($query);
        echo $res ? json_encode($res) : "";
    }

    // Obtiene información detallada de un proveedor asignado a una QR
    function ajax_getProveedor() {
        $id = $this->input->post('id');

        $query = "SELECT QP.id, QP.qr, QP.empresa, QP.costos, QP.monto, QP.moneda, QP.total, 
                         QP.tiempo_entrega, QP.dias_habiles, QP.comentarios, QP.nominado, 
                         QP.seleccionado, QP.vencimiento, QP.nombre_archivo, E.nombre, 
                         P.entrega, QR.nombre_archivoEjemplo 
                  FROM qr_proveedores QP 
                  INNER JOIN empresas E ON E.id = QP.empresa 
                  INNER JOIN proveedores P ON P.empresa = E.id 
                  JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                  WHERE QP.id = '$id'";

        $res = $this->Modelo->consulta($query, TRUE);
        echo $res ? json_encode($res) : "";
    }

    // Obtiene lista de proveedores filtrando por texto y condiciones necesarias
    function ajax_getProveedores() {
        $texto = $this->input->post('texto');

        $query = "SELECT E.* 
                  FROM empresas E 
                  INNER JOIN proveedores P ON E.id = P.empresa 
                  WHERE E.proveedor = 1 
                    AND (P.tags LIKE '%,$texto,%' OR E.nombre LIKE '%$texto%') 
                    AND !ISNULL(P.entrega) 
                    AND JSON_LENGTH(P.tipo) > 0 
                    AND JSON_LENGTH(P.formas_pago) > 0";

        $res = $this->Modelo->consulta($query);
        echo $res ? json_encode($res) : "";
    }

    // Cambia el estatus de una QR, registra en bitácora y envía correo si aplica
    function ajax_setEstatusQR() {
        $estatus = $this->input->post('estatus');
        $idqr = $this->input->post('idqr');
        $liberador = $this->session->id;

        if ($estatus == "LIBERADO") {
            $query = "UPDATE requisiciones_cotizacion 
                      SET estatus = '$estatus', 
                          fecha_liberacion = CURRENT_TIMESTAMP(), 
                          liberador = $liberador 
                      WHERE id = '$idqr'";
        } else {
            $query = "UPDATE requisiciones_cotizacion 
                      SET estatus = '$estatus' 
                      WHERE id = '$idqr'";
        }

        $this->Modelo->update($query);

        // Registra cambio en bitácora
        $bitacoraQR = array(
            'qr' => intval($idqr),
            'user' => $this->session->id,
            'estatus' => $estatus
        );
        $this->Modelo->estatusQR($bitacoraQR);

        // Si fue exitoso, enviar correo si es necesario
        if (in_array($estatus, ["LIBERADO", "COMPRA APROBADA"])) {
            $qr = $this->Modelo->getDetalleQR($idqr);
            $datos = array(
                'id' => $idqr,
                'fecha' => date('d/m/Y h:i A'),
                'usuario' => $qr->User,
                'cliente' => $qr->Client,
                'prioridad' => $qr->prioridad,
                'unidad' => $qr->unidad,
                'cantidad' => $qr->cantidad,
                'descripcion' => $qr->descripcion,
                'atributos' => $qr->atributos,
                'comentarios' => $qr->comentarios,
                'correos' => array($qr->correo),
                'estatus' => $estatus
            );

            // Añade notificados adicionales
            $Notificar = json_decode($qr->notificaciones);
            $query = "SELECT U.correo FROM usuarios U WHERE 1 != 1";
            foreach ($Notificar as $value) {
                $query .= " OR U.id = $value";
            }
            $res = $this->Conexion->consultar($query);
            foreach ($res as $value) {
                array_push($datos['correos'], $value->correo);
            }

            $this->correos->liberarQR($datos);
        }

        echo "1";
    }

    // Cambia el estatus de una QR e incluye un comentario con notificación por correo
    function ajax_setEstatusMsjQR() {
        $idqr = $this->input->post('idqr');
        $estatus = $this->input->post('estatus');
        $comentario_original = $this->input->post('comentario');
        $comentario = "<b><font color='red'>$estatus:</font></b> " . $comentario_original;
        $tags = $this->input->post('txtTags');
        $correos = explode(",", $tags);

        $query = "UPDATE requisiciones_cotizacion SET estatus = '$estatus' WHERE id = '$idqr'";
        $this->Modelo->update($query);

        // Bitácora
        $bitacoraQR = array(
            'qr' => intval($idqr),
            'user' => $this->session->id,
            'estatus' => $estatus
        );
        $this->Modelo->estatusQR($bitacoraQR);

        // Guarda el comentario del cambio de estatus
        $data = array(
            'qr' => $idqr,
            'usuario' => $this->session->id,
            'comentario' => $comentario
        );
        $this->Modelo->agregar_comentario($data);

        $qr = $this->Modelo->getDetalleQR($idqr);
        $datos = array(
            'id' => $idqr,
            'fecha' => date('d/m/Y h:i A'),
            'usuario' => $qr->User,
            'cliente' => $qr->Client,
            'prioridad' => $qr->prioridad,
            'unidad' => $qr->unidad,
            'cantidad' => $qr->cantidad,
            'descripcion' => $qr->descripcion,
            'atributos' => $qr->atributos,
            'comentarios' => $comentario_original,
            'correos' => array($qr->correo)
        );

        // Envía correo según tipo de estatus
        if ($estatus == "RECHAZADO" || $estatus == "COMPRA RECHAZADA") {
            $this->correos->rechazoQR($datos);
        } else if ($estatus == "LIBERADO") {
            $this->correos->liberarQR($datos);
        }

        // Si se agregaron correos adicionales manualmente
        if (count($correos) > 0) {
            $datos2 = array(
                'id' => $idqr,
                'comentario' => $comentario,
                'correos' => $correos
            );
            $this->correos->comentarioQR($datos2);
        }

        echo $comentario;
    }

        // Marca uno o varios proveedores como nominados para una QR
    function ajax_setProveedoresNominados() {
        $res = TRUE;
        $qr_proveedor = json_decode($this->input->post('qr_proveedores')); // IDs de proveedores a nominar
        $qr = $this->input->post('qr'); // ID de la QR

        // Primero se desmarcan todos
        $this->Modelo->update("UPDATE qr_proveedores SET nominado = 0 WHERE qr = $qr");

        // Luego se marcan los seleccionados
        foreach ($qr_proveedor as $value) {
            if (!$this->Modelo->update("UPDATE qr_proveedores SET nominado = 1 WHERE id = $value")) {
                $res = FALSE;
            }
        }

        echo $res ? "1" : "";
    }

    // Sube archivo PDF asociado a una QR (como archivo principal)
    function ajax_subirArchivoQR() {
        $datos['id'] = $this->input->post('qr');
        $datos['archivo'] = file_get_contents($_FILES['file']['tmp_name']);
        $datos['nombre_archivo'] = str_pad($datos['id'], 6, "0", STR_PAD_LEFT) . ".pdf";

        if (!$this->Modelo->setQRFile($datos)) {
            trigger_error("Error al subir archivo", E_USER_ERROR);
        } else {
            echo "1";
        }
    }

    // Elimina el archivo PDF asociado a una QR
    function ajax_borrarArchivoQR() {
        $datos['id'] = $this->input->post('qr');
        $datos['archivo'] = "";
        $datos['nombre_archivo'] = "";

        if (!$this->Modelo->setQRFile($datos)) {
            trigger_error("Error al borrar archivo", E_USER_ERROR);
        } else {
            echo "1";
        }
    }

    // Sube archivo de evidencia (como propuesta de proveedor)
    function ajax_subirEvidencia() {
        $datos['id'] = $this->input->post('qr_prov');
        $datos['archivo'] = file_get_contents($_FILES['file']['tmp_name']);
        $datos['nombre_archivo'] = $_FILES['file']['name'];

        if (!$this->Modelo->updateProveedor($datos)) {
            trigger_error("Error al subir evidencia", E_USER_ERROR);
        } else {
            echo $datos['nombre_archivo'];
        }
    }

    // Elimina archivo de evidencia cargado por proveedor
    function ajax_eliminarEvidencia() {
        $datos['id'] = $this->input->post('qr_prov');
        $datos['archivo'] = null;
        $datos['nombre_archivo'] = null;

        if (!$this->Modelo->updateProveedor($datos)) {
            trigger_error("Error al eliminar evidencia", E_USER_ERROR);
        } else {
            echo "1";
        }
    }

    // Muestra un archivo de evidencia en el navegador (inline PDF)
    function getEvidencia($qr_prov) {
        $row = $this->descargas_model->getFile($qr_prov, 'qr_proveedores');
        $file = $row->archivo;
        $nombre = $row->nombre_archivo;

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');

        echo $file;
    }

    // Muestra el archivo principal PDF de una QR
    function getQrFile($qr) {
        $row = $this->descargas_model->getFile($qr, 'requisiciones_cotizacion');
        $file = $row->archivo;
        $nombre = $row->nombre_archivo;

        header('Content-type: application/pdf');
        header('Content-Disposition: inline; filename="' . $nombre . '"');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');

        echo $file;
    }

    // Agrega un comentario a una QR y notifica a usuarios relacionados
    function agregarComentario() {
        $idQr = $this->input->post('idQr');
        $comentario = $this->input->post('comentario');
        $tags = $this->input->post('txtTags');
        $correos = explode(",", $tags);

        // Obtiene correos del solicitante y del asignado
        $query = "SELECT ur.correo AS correoReq, ua.correo AS correoA 
                  FROM requisiciones_cotizacion qr 
                  JOIN usuarios ur ON ur.id = qr.usuario 
                  LEFT JOIN usuarios ua ON ua.id = qr.asignado 
                  WHERE qr.id = $idQr";

        $res = $this->Modelo->consulta($query, TRUE);
        $mails = explode(',', $res->correoReq . ',' . $res->correoA);

        // Enviar correo principal de comentario
        $correoData = array(
            'id' => $idQr,
            'comentario' => $comentario,
            'correos' => $mails,
            'usuario' => $this->session->nombre
        );
        $this->correos->comentarioQR($correoData);

        // Guarda comentario en base de datos
        $data = array(
            'qr' => $idQr,
            'usuario' => $this->session->id,
            'comentario' => $comentario
        );
        $this->Modelo->agregar_comentario($data);

        // Enviar notificaciones a correos adicionales si existen
        if (count($correos) > 0) {
            $datos = array(
                'id' => $idQr,
                'comentario' => $comentario,
                'correos' => $correos,
                'usuario' => $this->session->nombre
            );
            $this->correos->comentarioQR($datos);
        }

        redirect(base_url('compras/ver_qr/' . $idQr));
    }


        // Obtiene resumen de propuestas por marca para un proveedor específico
    function ajax_getResumenMarca() {
        $prov = $this->input->post('prov');
        $marca = $this->input->post('marca');

        $query = "SELECT QP.id AS idQP, QP.qr, QR.descripcion, QP.total, QP.moneda, 
                         QP.tiempo_entrega, QP.dias_habiles, QP.nombre_archivo, QP.vencimiento 
                  FROM qr_proveedores QP 
                  INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                  WHERE QP.monto > 0 
                    AND QP.empresa = '$prov' 
                    AND UPPER(QR.atributos->'$.marca') = UPPER('\"$marca\"')";

        $res = $this->Modelo->consulta($query);
        echo $res ? json_encode($res) : "";
    }

    // Obtiene resumen de propuestas por modelo para un proveedor específico
    function ajax_getResumenModelo() {
        $prov = $this->input->post('prov');
        $modelo = $this->input->post('modelo');

        $query = "SELECT QP.id AS idQP, QP.qr, QR.descripcion, QP.total, QP.moneda, 
                         QP.tiempo_entrega, QP.dias_habiles, QP.nombre_archivo, QP.vencimiento 
                  FROM qr_proveedores QP 
                  INNER JOIN requisiciones_cotizacion QR ON QR.id = QP.qr 
                  WHERE QP.monto > 0 
                    AND QP.empresa = '$prov' 
                    AND UPPER(QR.atributos->'$.modelo') = UPPER('\"$modelo\"')";

        $res = $this->Modelo->consulta($query);
        echo $res ? json_encode($res) : "";
    }

    // Consulta PRs activas relacionadas a una QR específica
    function ajax_getPRS_QR() {
        $idQR = $this->input->post('id_qr');

        $query = "SELECT PR.id, PR.fecha, PR.estatus, CONCAT(U.nombre, ' ', U.paterno) AS User 
                  FROM prs PR 
                  INNER JOIN usuarios U ON PR.usuario = U.id 
                  WHERE (PR.estatus != 'CANCELADO' AND PR.estatus != 'CERRADO') 
                    AND PR.qr = $idQR";

        $res = $this->Conexion->consultar($query);
        echo $res ? json_encode($res) : "";
    }

    // Carga el dashboard general de compras
    function dashboard() {
        $data['asignado'] = $this->Modelo->usuariosCompras(); // Usuarios del área de compras
        $this->load->view('header');
        $this->load->view('compras/dashboard', $data);
    }

    // Genera reporte resumen de QRs activas para el dashboard
    function ajax_getReporteQR() {
        $asignado = $this->input->post('asignado');
        $us = ($asignado != 'TODO') ? " AND asignado = $asignado" : "";

        $query = "SELECT 
                    COUNT(*) AS Total,
                    (SELECT COUNT(*) FROM requisiciones_cotizacion WHERE estatus = 'ABIERTO' AND fecha > '2021-01-01 00:00:00' $us) AS Abiertos,
                    (SELECT MIN(fecha) FROM requisiciones_cotizacion WHERE estatus = 'ABIERTO' AND fecha > '2021-01-01 00:00:00' $us) AS ultAbiertos,
                    (SELECT COUNT(*) FROM requisiciones_cotizacion WHERE estatus = 'RECHAZADO' AND fecha > '2021-01-01 00:00:00' $us) AS Rechazados,
                    (SELECT MIN(fecha) FROM requisiciones_cotizacion WHERE estatus = 'RECHAZADO' AND fecha > '2021-01-01 00:00:00' $us) AS ultRechazados,
                    (SELECT COUNT(*) FROM requisiciones_cotizacion WHERE estatus = 'COTIZANDO' AND fecha > '2021-01-01 00:00:00' $us) AS Cotizando,
                    (SELECT MIN(fecha) FROM requisiciones_cotizacion WHERE estatus = 'COTIZANDO' AND fecha > '2021-01-01 00:00:00' $us) AS ultCotizando
                  FROM requisiciones_cotizacion 
                  WHERE fecha > '2021-01-01 00:00:00' $us";

        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }

    // Genera reporte resumen de PRs activas para el dashboard
    function ajax_getReportePR() {
        $query = "SELECT 
                    COUNT(*) AS Total,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'PENDIENTE') AS Pendientes,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'PENDIENTE') AS ultPendientes,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'APROBADO') AS Aprobados,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'APROBADO') AS ultAprobados,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'RECHAZADO') AS Rechazados,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'RECHAZADO') AS ultRechazados,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'EN SELECCION') AS Seleccion,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'EN SELECCION') AS ultSeleccion,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'PO AUTORIZADA') AS PO_Autorizada,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'PO AUTORIZADA') AS ultPO_Autorizada,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'EN PO') AS En_PO,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'EN PO') AS ultEn_PO,
                    (SELECT COUNT(*) FROM prs WHERE estatus = 'POR RECIBIR') AS Por_Recibir,
                    (SELECT MIN(fecha) FROM prs WHERE estatus = 'POR RECIBIR') AS ultPor_Recibir
                  FROM prs";

        $res = $this->Conexion->consultar($query, TRUE);
        echo json_encode($res);
    }

    // Reporte de órdenes de compra por estatus
function ajax_getReportePO() {
    $requisitor = $this->input->post('requisitor');
    $us = ($requisitor != 'TODO') ? " and usuario = $requisitor" : "";

    $query = 'SELECT count(*) as Total, ';

    // Subconsultas para contar y obtener la fecha más antigua de cada estatus
    $estados = [
        'EN PROCESO' => 'EnProceso',
        'PENDIENTE AUTORIZACION' => 'PendienteAutorizacion',
        'AUTORIZADA' => 'Autorizada',
        'RECHAZADA' => 'Rechazada',
        'ORDENADA' => 'Ordenada',
        'RECIBIDA' => 'Recibida',
        'RECIBIDA PARCIAL' => 'Parcial',
        'RECIBIDA TOTAL' => 'RecibidaTotal',
        'LISTA PARA CERRAR' => 'Lista'
    ];

    foreach ($estados as $estatus => $alias) {
        $query .= "(SELECT count(*) FROM ordenes_compra WHERE estatus = \"$estatus\" AND publish = 1 AND fecha > \"2022-01-01 00:00:00\"$us) AS $alias, ";
        $query .= "(SELECT MIN(fecha) FROM ordenes_compra WHERE estatus = \"$estatus\" AND publish = 1 AND fecha > \"2022-01-01 00:00:00\"$us) AS ult$alias, ";
    }

    // Remueve la última coma
    $query = rtrim($query, ', ');

    // Consulta principal
    $query .= " FROM ordenes_compra WHERE publish = 1 AND fecha > \"2022-01-01 00:00:00\"$us";

    $res = $this->Conexion->consultar($query, TRUE);
    echo json_encode($res);
}

// Carga vista para solicitudes de compra (PR)
function solicitudes_compra($estatus = 'TODO') {
    $estatus = strtoupper($estatus);
    $data['estatus'] = str_replace('_', ' ', $estatus);
    $data['otros_aprobadores'] = ($data['estatus'] == 'TODO') ? '' : 'checked';

    $this->load->view('header');
    $this->load->view('compras/catalogo_pr', $data);
}

// Vista detallada de una PR
function ver_pr($id) {
    $data['comentarios'] = $this->Modelo->verPr_comentarios($id);
    $data['comentarios_fotos'] = $this->Modelo->verPr_comentarios_fotos($id);
    $data['surtidorPR'] = $this->Modelo->surtidorPR($id);
    $data['atributos'] = $this->Modelo->atributosPr($id);
    $data['pr'] = $this->Modelo->getPR($id);

    // Determina color de botón según estatus
    switch ($data['pr']->estatus) {
        case 'APROBADO':
        case 'PO AUTORIZADA':
            $data['btn_estatus'] = "btn-success"; break;
        case 'PENDIENTE':
        case 'EN SELECCION':
        case 'POR RECIBIR':
            $data['btn_estatus'] = "btn-warning"; break;
        case 'EN PO':
        case 'CERRADO':
            $data['btn_estatus'] = "btn-primary"; break;
        case 'RECHAZADO':
            $data['btn_estatus'] = "btn-danger"; break;
        case 'PROCESADO':
        case 'CANCELADO':
            $data['btn_estatus'] = "btn-default"; break;
        default:
            $data['btn_estatus'] = "btn-warning"; break;
    }

    $this->load->view('header');
    $this->load->view('compras/ver_pr', $data);
}

// Vista de PRs del usuario actual
function mis_prs() {
    $this->load->view('header');
    $this->load->view('compras/mis_prs');
}

// Genera una nueva PR desde una QR
function ajax_generarPR() {
    $this->load->model('compras_model');

    $qrt = $this->input->post('qr');
    $qty = $this->input->post('qty');
    $precio = $this->input->post('precio');
    $descripcion = $this->input->post('descripcion');
    $serie = $this->input->post('serie');
    $qr_prov = $this->input->post('qr_prov');
    $item = $this->input->post('item');
    $id = $this->input->post('id');

    $qr = $this->Modelo->getDetalleQR($qrt);
    $qr_prov = $this->Modelo->getQRProv($qr_prov);

    // Modifica atributos si existe clave 'serie'
    $att = json_decode($qr->atributos, TRUE);
    if (array_key_exists('serie', $att)) {
        $att['serie'] = $serie ?: 'N/A';
        if ($id) $att['id'] = $id;
        if ($item) $att['item'] = $item;
    }

    // Datos para la nueva PR
    $datos = [
        'qr' => $qr->id,
        'qr_proveedor' => $qr_prov->id,
        'usuario' => $this->session->id,
        'prioridad' => $qr->prioridad,
        'tipo' => $qr->tipo,
        'subtipo' => $qr->subtipo,
        'cantidad' => $qty,
        'precio_unitario' => $qr_prov->monto,
        'importe' => $qr_prov->monto * $qty,
        'moneda' => $qr_prov->moneda,
        'unidad' => $qr->unidad,
        'clave_unidad' => $qr->clave_unidad,
        'descripcion' => $descripcion,
        'atributos' => json_encode($att),
        'critico' => $qr->critico,
        'destino' => $qr->destino,
        'lugar_entrega' => $qr->lugar_entrega,
        'comentarios' => "",
        'estatus' => "PENDIENTE"
    ];

    $funciones['fecha'] = 'CURRENT_TIMESTAMP()';
    $res = $this->Conexion->insertar('prs', $datos, $funciones);

    // Guarda bitácora de estatus inicial
    $this->compras_model->estatusPR([
        'pr' => intval($res),
        'user' => $this->session->id,
        'estatus' => 'PENDIENTE'
    ]);

    // Si es SERVICIO, transfiere atributos temporales
    if ($qr->tipo == "SERVICIO" || ($qr->tipo == "PRODUCTO" && $qr->destino == "VENTA")) {
        $result = $this->Conexion->consultar("SELECT * FROM qr_atributos_temp WHERE idQr = $qrt");
        if ($result) {
            foreach ($result as $elem) {
                $atributosPR = [
                    'idPr' => intval($res),
                    'item' => $elem->item,
                    'asignado' => $elem->asignado
                ];
                if ($qr->tipo == "SERVICIO") {
                    $atributosPR['equipo'] = $elem->equipo;
                    $atributosPR['serie'] = $elem->serie;
                    $atributosPR['modelo'] = $elem->modelo;
                    $atributosPR['fabricante'] = $elem->fabricante;
                }
                $this->Conexion->insertar('pr_atributos', $atributosPR);
            }
            $this->Conexion->eliminar('qr_atributos_temp', ['idQr' => $qrt]);
        }
    }

    // Enviar correo si se generó correctamente
    if ($res > 0) {
        $correoData = [
            'id' => $res,
            'fecha' => date('d/m/Y h:i A'),
            'usuario' => $this->session->nombre,
            'cantidad' => $qty,
            'unidad' => $qr->unidad,
            'descripcion' => $qr->descripcion,
            'atributos' => $qr->atributos,
            'prioridad' => $qr->prioridad,
            'comentarios' => "",
            'correos' => array_merge([$this->session->correo], $this->Modelo->getAprobadorPR($this->session->id, $qr->destino))
        ];
        $this->correos_pr->creacionPR($correoData);
        echo $res;
    }
}

    // Edita cantidad e importe de una PR, reinicia estatus a 'PENDIENTE'
function ajax_editarPR() {
    $id = $this->input->post('id');
    $qty = $this->input->post('qty');

    $query = "UPDATE prs SET cantidad = $qty, importe = precio_unitario * cantidad, estatus = 'PENDIENTE' WHERE id = $id";
    $this->Conexion->comando($query);

    echo "1";
}

// Recupera lista de PRs filtradas por múltiples criterios
function ajax_getPRs() {
    $curUser = $this->session->id;
    $misprs = $this->input->post('misprs');
    $prioridad = json_decode($this->input->post('prioridad'));
    $estatus = $this->input->post('estatus');
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = $fecha1 . ' 00:00:00';
    $f2 = $fecha2 . ' 23:59:59';
    $tipo = json_decode($this->input->post('tipo'));
    $stock = $this->input->post('stock');
    $archivo = $this->input->post('archivo');

    $query = "SELECT 
        PR.id, PR.fecha, PR.usuario, PR.prioridad, PR.tipo, PR.subtipo,
        PR.cantidad, PR.unidad, PR.clave_unidad, PR.descripcion, PR.atributos,
        PR.critico, PR.destino, PR.lugar_entrega, PR.comentarios, PR.estatus,
        CONCAT(U.nombre, ' ', U.paterno) AS User,
        U.autorizador_compras, U.autorizador_compras_venta, PR.stock,
        IFNULL((
            SELECT OCC.po 
            FROM ordenes_compra_conceptos OCC
            INNER JOIN ordenes_compra OC ON OCC.po = OC.id
            WHERE OCC.pr = PR.id AND OC.estatus != 'CANCELADA' LIMIT 1
        ), 0) AS POActual
        FROM prs PR
        LEFT JOIN usuarios U ON PR.usuario = U.id
        WHERE 1 = 1";

    if ($estatus != 'TODO') {
        $query .= " AND PR.estatus = '$estatus'";
    }

    if (count($prioridad) > 0) {
        $query .= " AND (0";
        foreach ($prioridad as $value) {
            $query .= " OR PR.prioridad = '$value'";
        }
        $query .= ")";
    }

    if ($misprs == "1") {
        $query .= " AND IF(PR.destino = 'VENTA', U.autorizador_compras_venta = $curUser, U.autorizador_compras = $curUser)";
    }

    if ($stock == "1") {
        $query .= " AND PR.stock = 1";
    }

    if (!empty($texto)) {
        switch ($parametro) {
            case "folio":
                $query .= " AND PR.id = '$texto'";
                break;
            case "usuario":
                $query .= " HAVING User LIKE '%$texto%'";
                break;
            case "contenido":
                $query .= " AND (
                    PR.descripcion LIKE '%$texto%' OR
                    UPPER(PR.atributos->'$.marca') LIKE UPPER('%$texto%') OR
                    UPPER(PR.atributos->'$.modelo') LIKE UPPER('%$texto%')
                )";
                break;
            case "productos":
                $query .= " AND PR.tipo LIKE '%$texto%'";
                break;
            case "servicios":
                $query .= " AND PR.subtipo LIKE '%$texto%'";
                break;
        }
    }

    if (isset($tipo) && count($tipo) > 0) {
        $query .= " AND (0";
        foreach ($tipo as $value) {
            $query .= " OR PR.tipo = '$value'";
        }
        $query .= ")";
    }

    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " AND PR.fecha BETWEEN '$f1' AND '$f2'";
    }

    if (true) {
        if ($archivo != 1) {
            $query .= " AND PR.fecha > '2022-01-01 00:00:00'";
        }
        $query .= " ORDER BY PR.fecha DESC";
    }

    $res = $this->Modelo->consulta($query);

    if ($res) {
        echo json_encode($res);
    }
}

// Obtiene PRs del usuario actual con filtros avanzados
function ajax_getMisPRs() {
    $user = $this->session->id;

    $prioridad = json_decode($this->input->post('prioridad'));
    $estatus = $this->input->post('estatus');
    $texto = $this->input->post('texto');
    $parametro = $this->input->post('parametro');
    $misprs = $this->input->post('misprs');

    $query = "SELECT 
        PR.id, PR.fecha, PR.usuario, 
        CONCAT(U.nombre, ' ', U.paterno, ' ', U.materno) AS User,
        PR.prioridad, PR.tipo, PR.subtipo, PR.cantidad, PR.unidad, PR.clave_unidad,
        PR.descripcion, PR.atributos, PR.critico, PR.destino, PR.lugar_entrega, 
        PR.comentarios, PR.estatus,
        IFNULL((
            SELECT OCC.po 
            FROM ordenes_compra_conceptos OCC
            INNER JOIN ordenes_compra OC ON OCC.po = OC.id
            WHERE OCC.pr = PR.id AND OC.estatus != 'CANCELADA' LIMIT 1
        ), 0) AS POActual
        FROM prs PR
        INNER JOIN usuarios U ON U.id = PR.usuario
        WHERE 1 = 1";

    if ($misprs == "1") {
        $query .= " AND PR.usuario = $user";
    }

    if ($estatus != 'TODO') {
        $query .= " AND PR.estatus = '$estatus'";
    }

    if (count($prioridad) > 0) {
        $query .= " AND (0";
        foreach ($prioridad as $value) {
            $query .= " OR PR.prioridad = '$value'";
        }
        $query .= ")";
    }

    if (!empty($texto)) {
        if ($parametro == "folio") {
            $query .= " AND PR.id = '$texto'";
        }
        if ($parametro == "contenido") {
            $query .= " AND (
                PR.descripcion LIKE '%$texto%' OR 
                UPPER(PR.atributos->'$.marca') LIKE UPPER('%$texto%') OR 
                UPPER(PR.atributos->'$.modelo') LIKE UPPER('%$texto%')
            )";
        }
    }

    $query .= " ORDER BY PR.fecha DESC";

    $res = $this->Modelo->consulta($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Obtiene información detallada de una PR específica
function ajax_getPR() {
    $id = $this->input->post('id');

    $query = "SELECT PR.*, CONCAT(U.nombre, ' ', U.paterno) AS User, U.correo 
              FROM prs PR 
              INNER JOIN usuarios U ON U.id = PR.usuario 
              WHERE PR.id = $id";

    $res = $this->Conexion->consultar($query, TRUE);

    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

// Obtiene detalles del proveedor relacionado a una PR
function ajax_getProveedorPR() {
    $id = $this->input->post('id');

    $query = "SELECT 
        E.id, QP.id AS idQP, P.entrega, E.nombre, 
        QP.monto, QP.total, QP.moneda, QP.tiempo_entrega, 
        QP.dias_habiles, QP.comentarios, QP.nominado, QP.seleccionado, 
        QP.nombre_archivo, QP.vencimiento 
        FROM qr_proveedores QP 
        INNER JOIN empresas E ON E.id = QP.empresa 
        INNER JOIN proveedores P ON P.empresa = E.id 
        WHERE QP.id = '$id'";

    $res = $this->Modelo->consulta($query);
    if ($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}
    // Cambia el estatus de una PR y envía correo si aplica
function ajax_setEstatusPR() {
    $this->load->model('compras_model');

    $estatus = $this->input->post('estatus');
    $id = $this->input->post('id');
    $aprobador = $this->session->id;

    // Bitácora del cambio de estatus (no se almacena en esta función, pero se construye)
    $bitacora = [$estatus, date("Y-m-d h:i:sa"), $aprobador];
    $estatusBit = json_encode([$bitacora]);

    $query = "UPDATE prs SET estatus = '$estatus' WHERE id = '$id'";
    $data['pr'] = $id;
    $data['user'] = $aprobador;
    $data['estatus'] = $estatus;

    // Si fue aprobado, agrega fecha y aprobador
    if ($estatus == "APROBADO") {
        $query = "UPDATE prs SET estatus = '$estatus', fecha_aprobacion = CURRENT_TIMESTAMP(), aprobador = $aprobador WHERE id = '$id'";
        $this->compras_model->estatusPR($data);
    }

    // Si fue cerrado, agrega fecha de entrega
    if ($estatus == "CERRADO") {
        $query = "UPDATE prs SET estatus = '$estatus', entregado = CURRENT_TIMESTAMP() WHERE id = '$id'";
        if ($this->Modelo->update($query)) {
            echo "1";
        }
        exit();
    }

    // Ejecuta la actualización de estatus
    $res = $this->Modelo->update($query);

    if ($res) {
        $pr = $this->Modelo->getPR($id);

        $datos['id'] = $id;
        $datos['fecha'] = date('d/m/Y h:i A');
        $datos['usuario'] = $pr->User;
        $datos['prioridad'] = $pr->prioridad;
        $datos['unidad'] = $pr->unidad;
        $datos['cantidad'] = $pr->cantidad;
        $datos['descripcion'] = $pr->descripcion;
        $datos['atributos'] = $pr->atributos;
        $datos['comentarios'] = $pr->comentarios;
        $datos['estatus'] = $estatus;

        // Si fue aprobado, notifica por correo
        if ($estatus == "APROBADO") {
            $datos['correos'] = array_merge([$qr->correo], $this->Modelo->getCorreosQR());
            $this->correos_pr->liberarPR($datos);
        }

        echo "1";
    } else {
        echo "";
    }
}

// Agrega comentario a una PR y notifica a los correos relacionados
function agregarComentarioPR() {
    $id = $this->input->post('id');
    $comentario = $this->input->post('comentario');
    $tags = $this->input->post('txtTags');
    $correos = explode(",", $tags);

    // Correos del solicitante y del asignado a la QR relacionada
    $query = "SELECT u.correo AS correoReq, ua.correo AS correoA  
              FROM prs pr 
              JOIN usuarios u ON u.id = pr.usuario 
              JOIN requisiciones_cotizacion qr ON qr.id = pr.qr 
              JOIN usuarios ua ON qr.asignado = ua.id 
              WHERE pr.id = $id";
    $res = $this->Modelo->consulta($query, TRUE);

    $mails = explode(',', $res->correoReq . ',' . $res->correoA);

    // Datos para enviar notificación de comentario
    $data['id'] = $id;
    $data['comentario'] = $comentario;
    $data['correos'] = $mails;
    $data['nombre'] = $this->session->nombre;

    $this->correos_pr->comentarioPR($data);

    // Guarda el comentario en la base de datos
    $insert = [
        'pr' => $id,
        'usuario' => $this->session->id,
        'comentario' => $comentario
    ];
    $this->Modelo->agregar_comentarioPR($insert);

    // Envía comentario a correos marcados con etiquetas (si hay)
    if (count($correos) > 0) {
        $datos['id'] = $id;
        $datos['comentario'] = $comentario;
        $datos['correos'] = $correos;
        $this->correos_pr->comentarioPR($datos);
    }

    redirect(base_url('compras/ver_pr/' . $id));
}

   // Cambia estatus de PR con comentario incluido y notificación por correo
function ajax_setEstatusMsjPR() {
    $this->load->model('compras_model');

    $id = $this->input->post('id');
    $estatus = $this->input->post('estatus');
    $comentario_original = $this->input->post('comentario');
    $comentario = "<b><font color='red'>$estatus:</font></b> " . $comentario_original;
    $tags = $this->input->post('txtTags');
    $correos = explode(",", $tags);

    // Actualiza estatus en base de datos
    $query = "UPDATE prs SET estatus = '$estatus' WHERE id = '$id'";
    $data['pr'] = $id;
    $data['user'] = $this->session->id;
    $data['estatus'] = $estatus;
    $this->compras_model->estatusPR($data);

    $res = $this->Modelo->update($query);
    if ($res) {
        // Registra comentario con el estatus como prefijo
        $data = [
            'pr' => $id,
            'usuario' => $this->session->id,
            'comentario' => $comentario,
        ];
        $this->Modelo->agregar_comentarioPR($data);

        $pr = $this->Modelo->getPR($id);

        $datos['id'] = $id;
        $datos['fecha'] = date('d/m/Y h:i A');
        $datos['usuario'] = $pr->User;
        $datos['prioridad'] = $pr->prioridad;
        $datos['unidad'] = $pr->unidad;
        $datos['cantidad'] = $pr->cantidad;
        $datos['descripcion'] = $pr->descripcion;
        $datos['atributos'] = $pr->atributos;
        $datos['comentarios'] = $pr->comentarios;
        $datos['estatus'] = $estatus;

        // Enviar correo solo si fue rechazado
        if ($estatus == "RECHAZADO") {
            $datos['correos'] = [$pr->correo];
            $this->correos_pr->rechazoPR($datos);
        }

        // Correos personalizados vía etiquetas
        if (count($correos) > 0) {
            $datos2['id'] = $id;
            $datos2['comentario'] = $comentario;
            $datos2['correos'] = $correos;
            $this->correos_pr->comentarioPR($datos2);
        }

        echo $comentario;
    } else {
        echo "";
    }
}

// Retorna lista de usuarios con permiso para aprobar PRs
function ajax_getLiberadoresCompra() {
    $query = "SELECT U.id, CONCAT(U.nombre, ' ', U.paterno, ' ', U.materno) AS Name 
              FROM usuarios U 
              INNER JOIN privilegios P ON P.usuario = U.id 
              WHERE U.activo = 1 AND P.aprobar_pr = 1";

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

// Retorna lista de usuarios con permiso para aprobar cotizaciones
function ajax_getLiberadoresCotizacion() {
    $query = "SELECT U.id, CONCAT(U.nombre, ' ', U.paterno, ' ', U.materno) AS Name 
              FROM usuarios U 
              INNER JOIN privilegios P ON P.usuario = U.id 
              WHERE U.activo = 1 AND P.aprobar_cotizacion = 1";

    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}
    function exportarQR() {
    // Recolección de filtros desde POST
    $parametro = $this->input->post('rbBusqueda');
    $texto = $this->input->post('txtBusqueda');
    $estatus = $this->input->post('opEstatus');
    $usuario = $this->input->post('cbMisQrs');
    $cbNormal = $this->input->post('cbNormal');
    $cbUrgente = $this->input->post('cbUrgente');
    $cbInfoUrgente = $this->input->post('cbInfoUrgente');
    $cbProducto = $this->input->post('cbProducto');
    $cbServicio = $this->input->post('cbServicio');
    $asignado = $this->input->post('opAsignado');
    $archivo = $this->input->post('cbArchivo');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = $fecha1 . ' 00:00:00';
    $f2 = $fecha2 . ' 23:59:59';

    // Consulta base
    $query = "SELECT R.id, R.fecha, R.usuario, R.prioridad, R.tipo, R.subtipo, R.cantidad, R.cantidad_aprobada, R.unidad, R.clave_unidad, R.descripcion, R.atributos, R.critico, R.destino, R.lugar_entrega, R.comentarios, R.estatus, R.asignado, CONCAT(U.nombre, ' ', U.paterno) as User, R.fecha_liberacion
              FROM requisiciones_cotizacion R
              LEFT JOIN usuarios U ON R.usuario = U.id
              WHERE 1 = 1";

    // Filtros aplicados
    if ($estatus != 'TODO') {
        $query .= " AND R.estatus = '$estatus'";
    }
    if (!empty($asignado)) {
        $query .= " AND R.asignado = '$asignado'";
    }
    if ($usuario == 'NORMAL') {
        $idUser = $this->session->id;
        $query .= " AND R.usuario = '$idUser'";
    }

    // Prioridades
    $query .= " AND (1 = 0";
    if (!empty($cbNormal)) $query .= " OR R.prioridad = '$cbNormal'";
    if (!empty($cbUrgente)) $query .= " OR R.prioridad = '$cbUrgente'";
    if (!empty($cbInfoUrgente)) $query .= " OR R.prioridad = '$cbInfoUrgente'";
    $query .= ")";

    // Tipo de producto o servicio
    $query .= " AND (1 = 0";
    if (!empty($cbProducto)) $query .= " OR R.tipo = '$cbProducto'";
    if (!empty($cbServicio)) $query .= " OR R.tipo = '$cbServicio'";
    $query .= ")";

    // Filtro por privilegios internos o de venta
    if ($this->session->privilegios['crear_qr_interno'] != $this->session->privilegios['crear_qr_venta']) {
        if ($this->session->privilegios['editar_qr'] == "0" && $this->session->privilegios['liberar_qr'] == "0") {
            if ($this->session->privilegios['crear_qr_interno'] == "1") {
                $query .= " AND R.destino = 'CONSUMO INTERNO'";
            } else {
                $query .= " AND R.destino = 'VENTA'";
            }
        }
    }

    // Búsqueda por texto
    if (!empty($texto)) {
        if ($parametro == "folio") {
            $query .= " AND R.id = '$texto'";
        }
        if ($parametro == "usuario") {
            $query .= " AND CONCAT(U.nombre, ' ', U.paterno) LIKE '%$texto%'";
        }
        if ($parametro == "contenido") {
            $query .= " AND (R.descripcion LIKE '%$texto%' 
                            OR UPPER(R.atributos->'$.marca') LIKE UPPER('%$texto%') 
                            OR UPPER(R.atributos->'$.modelo') LIKE UPPER('%$texto%'))";
        }
    }

    // Rango de fechas
    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " AND R.fecha BETWEEN '$f1' AND '$f2'";
    }

    // Solo QRs del último año
    $query .= " AND R.maximo_vencimiento > (CURRENT_DATE() - INTERVAL 1 YEAR)";

    // Filtrar si no es exportación completa
    if ($archivo != "1") {
        $query .= " AND R.fecha > '2021-01-01 00:00:00'";
    }

    $query .= " ORDER BY R.fecha DESC";

    $result = $this->Conexion->consultar($query);

    // Construcción de tabla para exportar
    $salida = '<table style="border: 1px solid black; border-collapse: collapse;">
        <thead>
            <tr>
                <th style="background-color: #F3F1F1; border: 1px solid black;">QR</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Fecha</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Requisitor</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Prioridad</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Tipo</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Subtipo</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Cantidad</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Estatus</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Fecha Liberado</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Días Transcurridos</th>
                <th style="background-color: #F3F1F1; border: 1px solid black;">Descripción</th>
            </tr>
        </thead>
        <tbody>';

    $d = 2;
    foreach ($result as $row) {
        $salida .= '<tr>
            <td style="border: 1px solid black;">' . $row->id . '</td>
            <td style="border: 1px solid black;">' . $row->fecha . '</td>
            <td style="border: 1px solid black;">' . $row->User . '</td>
            <td style="border: 1px solid black;">' . $row->prioridad . '</td>
            <td style="border: 1px solid black;">' . $row->tipo . '</td>
            <td style="border: 1px solid black;">' . $row->subtipo . '</td>
            <td style="border: 1px solid black;">' . $row->cantidad . '</td>
            <td style="border: 1px solid black;">' . $row->estatus . '</td>
            <td style="border: 1px solid black;">' . $row->fecha_liberacion . '</td>
            <td style="border: 1px solid black;">=SI(ESBLANCO($I' . $d . '),"",DIAS($I' . $d . ',$B' . $d . '))</td>
            <td style="border: 1px solid black;">' . $row->descripcion . '</td>
        </tr>';
        $d++;
    }

    $salida .= '</tbody></table>';

    // Preparar headers para descarga como archivo Excel
    $timestamp = date('m-d-Y');
    $filename = 'QR_' . $timestamp . '.xls';
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header("Content-Transfer-Encoding: binary");

    echo $salida;
}

   function exportarPR()
{
    // Entrada de parámetros desde POST
    $opc = $this->input->post('rbBusqueda');
    $texto = $this->input->post('txtBusqueda');
    $estatus = $this->input->post('opEstatus');
    $fecha1 = $this->input->post('fecha1');
    $fecha2 = $this->input->post('fecha2');
    $f1 = strval($fecha1) . ' 00:00:00';
    $f2 = strval($fecha2) . ' 23:59:59';
    $stock = $this->input->post('stock');
    $archivo = $this->input->post('cbArchivo');

    // Consulta base
    $query = "SELECT 
                PR.id as PR,
                PR.fecha as Fecha,
                concat(U.nombre, ' ', U.paterno) as Requisitor,
                PR.prioridad as Prioridad,
                PR.tipo as Tipo,
                PR.subtipo as Subtipo,
                PR.cantidad as Cantidad,
                PR.estatus as Estatus,
                ifnull((
                    SELECT OCC.po 
                    FROM ordenes_compra_conceptos OCC 
                    INNER JOIN ordenes_compra OC ON OCC.po = OC.id 
                    WHERE OCC.pr = PR.id AND OC.estatus != 'CANCELADA' 
                    LIMIT 1
                ), 0) as PO,
                concat('$ ', format(PR.importe, 2)) as MONTO,
                PR.moneda as Moneda,
                (
                    SELECT P.entrega 
                    FROM qr_proveedores QP 
                    INNER JOIN empresas E ON E.id = QP.empresa 
                    INNER JOIN proveedores P ON P.empresa = E.id 
                    WHERE QP.id = PR.qr_proveedor
                ) as Lugar,
                (
                    SELECT E.nombre 
                    FROM qr_proveedores QP 
                    INNER JOIN empresas E ON E.id = QP.empresa 
                    INNER JOIN proveedores P ON P.empresa = E.id 
                    WHERE QP.id = PR.qr_proveedor
                ) as Proveedor,
                PR.descripcion as Descripcion
            FROM prs PR 
            LEFT JOIN usuarios U ON PR.usuario = U.id 
            WHERE 1 = 1";

    // Filtros dinámicos
    if ($estatus != 'TODO') {
        $query .= " AND PR.estatus = '$estatus'";
    }

    if (!empty($texto)) {
        if ($opc == "folio") {
            $query .= " AND PR.id = '$texto'";
        }
        if ($opc == "usuario") {
            $query .= " HAVING Requisitor LIKE '%$texto%'";
        }
        if ($opc == "contenido") {
            $query .= " AND (PR.descripcion LIKE '%$texto%' 
                        OR UPPER(PR.atributos->'$.marca') LIKE UPPER('%$texto%') 
                        OR UPPER(PR.atributos->'$.modelo') LIKE UPPER('%$texto%'))";
        }
    }

    if (!empty($fecha1) && !empty($fecha2)) {
        $query .= " AND PR.fecha BETWEEN '$fecha1' AND '$fecha2'";
    }

    if (!empty($stock)) {
        $query .= " AND PR.stock = 1";
    }

    if ($archivo != "1") {
        $query .= " AND PR.fecha > '2021-01-01 00:00:00'";
    }

    $query .= " ORDER BY PR.fecha DESC";

    // Ejecutar consulta
    $result = $this->Conexion->consultar($query);

    // Construcción de tabla HTML para exportar a Excel
    $salida = '';
    $salida .= '<table style="border: 1px solid black; border-collapse: collapse;">
                    <thead> 
                        <tr>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">PR</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Fecha</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Requisitor</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Prioridad</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Tipo</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Subtipo</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Cantidad</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Estatus</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">PO</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Monto</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Moneda</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Lugar de entrega</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Proveedor</th>
                            <th style="background-color: #F3F1F1; border: 1px solid black;">Descripcion</th>
                        </tr>
                    </thead>
                    <tbody>';

    foreach ($result as $row) {
        $salida .= '
            <tr>
                <td style="border: 1px solid black;">' . $row->PR . '</td>
                <td style="border: 1px solid black;">' . $row->Fecha . '</td>
                <td style="border: 1px solid black;">' . $row->Requisitor . '</td>
                <td style="border: 1px solid black;">' . $row->Prioridad . '</td>
                <td style="border: 1px solid black;">' . $row->Tipo . '</td>
                <td style="border: 1px solid black;">' . $row->Subtipo . '</td>
                <td style="border: 1px solid black;">' . $row->Cantidad . '</td>
                <td style="border: 1px solid black;">' . $row->Estatus . '</td>
                <td style="border: 1px solid black;">' . $row->PO . '</td>
                <td style="border: 1px solid black;">' . $row->MONTO . '</td>
                <td style="border: 1px solid black;">' . $row->Moneda . '</td>
                <td style="border: 1px solid black;">' . $row->Lugar . '</td>
                <td style="border: 1px solid black;">' . $row->Proveedor . '</td>
                <td style="border: 1px solid black;">' . $row->Descripcion . '</td>
            </tr>';
    }

    $salida .= '</tbody></table>';

    // Preparar headers para descargar el archivo como Excel
    $timestamp = date('m/d/Y', time());
    $filename = 'PR_' . $timestamp . '.xls';

    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header("Pragma: no-cache");
    header("Expires: 0");
    header('Content-Transfer-Encoding: binary');

    echo $salida;
}

    function AllPR(){
    // Obtiene el ID de la PR desde POST
    $id = $this->input->post('CURRENT_PR');

    // Consulta toda la información de la PR específica
    $query = "SELECT * from prs where id = ".$id;

    // Ejecuta la consulta
    $res = $this->Conexion->consultar($query, $id);

    // Si hay resultado, lo devuelve como JSON
    if($res) {
        echo json_encode($res);
    }
}

function ajax_getNombresUsuarios(){
    // Obtiene el ID de la PR desde POST
    $ids = $this->input->post('CURRENT_PR');

    // Consulta la bitácora de estatus para esa PR y quién los realizó
    $query = "SELECT concat(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus 
              from bitacora_prs b 
              JOIN usuarios u on b.user = u.id 
              where pr = ".$ids;

    // Ejecuta la consulta y devuelve JSON si hay resultados
    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function bitacoraQR(){
    // Obtiene el ID del QR desde POST
    $ids = $this->input->post('id');

    // Consulta la bitácora de estatus para ese QR y quién los realizó
    $query = "SELECT concat(u.nombre, ' ', u.paterno) as user, b.fecha, b.estatus 
              from bitacora_qrs b 
              JOIN usuarios u on b.user = u.id 
              where qr = ".$ids;

    // Ejecuta la consulta y devuelve JSON si hay resultados
    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

public function ajax_surtirPR()
{
    // Obtiene el ID de la PR desde POST
    $pr = $this->input->post('id');

    // Establece zona horaria y obtiene fecha/hora actual
    date_default_timezone_set('America/Chihuahua');
    $date = date('Y-m-d h:i:s');

    // Prepara los datos para actualizar la PR
    $data['estatus'] = "POR RECIBIR";
    $data['stock'] = 1;
    $data['surtidorStock'] = $this->session->id;
    $data['fechaSurtido'] = $date;

    $where['id'] = intval($pr);

    // Ejecuta la actualización en la tabla `prs`
    $res = $this->Conexion->modificar('prs', $data, null, $where);

    // Registra en bitácora el evento "SURTIDO DE STOCK"
    $bitacoraPR['pr'] = intval($pr);
    $bitacoraPR['user'] = $this->session->id;
    $bitacoraPR['estatus'] = 'SURTIDO DE STOCK';
    $this->Modelo->estatusPR($bitacoraPR);

    // Registra en bitácora el nuevo estatus "POR RECIBIR"
    $bitacoraPR['pr'] = intval($pr);
    $bitacoraPR['user'] = $this->session->id;
    $bitacoraPR['estatus'] = 'POR RECIBIR';
    $this->Modelo->estatusPR($bitacoraPR);

    // Devuelve 1 si la actualización fue exitosa
    if ($res) {
        echo 1;
    }
}

    function ajax_getUsuariosQR(){
    // Obtener usuarios activos del departamento de COMPRAS con privilegios de editar QR
    $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Nombre, P.puesto as Puesto, U.correo, PR.editar_qr 
              FROM usuarios U 
              INNER JOIN puestos P ON U.puesto = P.id 
              INNER JOIN privilegios PR ON PR.usuario = U.id 
              WHERE U.activo = 1 AND U.departamento='COMPRAS'";

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function asignarUsuariosQR(){
    // Obtener datos desde POST
    $qr = $this->input->post('qr');
    $id = $this->input->post('id');

    // Consultar correo del usuario a asignar
    $mail = "SELECT correo FROM usuarios WHERE id =".$id;
    $correo = $this->Conexion->consultar($mail);

    // Enviar correo de asignación
    foreach($correo as $elem){     
        $dato['qr'] = $qr;
        $dato['correo'] = $elem->correo;
        $this->correos->asignarQR($dato);    
    }

    // Guardar asignación en base de datos
    date_default_timezone_set('America/Chihuahua');
    $date = date('Y-m-d h:i:s');

    $data['asignado'] = intval($id);
    $data['asignador'] = $this->session->id;
    $data['fechaAsignacion'] = $date;
    $where['id'] = intval($qr);
    $this->Conexion->modificar('requisiciones_cotizacion', $data, null, $where);   

    // Registrar bitácora
    $bitacoraQR['qr'] = intval($qr);
    $bitacoraQR['user'] = $this->session->id;
    $bitacoraQR['estatus'] = 'Asignacion por: ' . $this->session->nombre;
    $this->Modelo->estatusQR($bitacoraQR);

    // Consultar y devolver información completa de asignación
    $query = "SELECT U.id, concat(U.nombre, ' ', U.paterno) as Nombre, P.puesto as Puesto, U.correo, 
                     qr.id as QR, qr.asignado, fechaAsignacion, concat(A.nombre, ' ', A.paterno) as Asignador 
              FROM usuarios U 
              INNER JOIN puestos P ON U.puesto = P.id 
              JOIN requisiciones_cotizacion qr ON qr.asignado = U.id 
              JOIN usuarios A ON A.id = qr.asignador 
              WHERE qr.id = ".$qr;

    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function buscarML(){
    // Recibe parámetros por POST
    $item = $this->input->post('item');
    $destino = $this->input->post('destino');
    $tipo = $this->input->post('tipo');

    // Consulta los items disponibles según tipo/destino
    if ($destino == 'VENTA' && $tipo == 'PRODUCTO') {
        $query = "SELECT rs.Item_id, rs.Equipo_ID, rs.serie , rs.tecnico_id, rs.fechaActEQ, 
                         rs.Modelo, rs.Fabricante, t.Nombre 
                  FROM rsitems rs 
                  JOIN catalogo_tecnicos t ON rs.tecnico_id = t.Tecnico_Id 
                  WHERE item_id = '".$item."' AND Equipo_id IS NULL AND fechaACtEQ IS NULL";
    } else {
        $query = "SELECT rs.Item_id, rs.Equipo_ID, rs.serie , rs.tecnico_id, rs.fechaActEQ, 
                         rs.Modelo, rs.Fabricante, t.Nombre 
                  FROM rsitems rs 
                  JOIN catalogo_tecnicos t ON rs.tecnico_id = t.Tecnico_Id 
                  WHERE item_id = '".$item."' AND Equipo_id IS NOT NULL AND fechaACtEQ IS NULL";
    }

    // Ejecutar consulta
    $res = $this->MLConexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function agregarAtributos(){
    // Recibe atributos e ID de QR por POST
    $att = $this->input->post('ATT');
    $qr = $this->input->post('qr');

    // Inserta los atributos recibidos en la tabla temporal
    foreach($att as $elem){
        $datos['idQr'] = intval($qr);
        $datos['item'] = $elem['Item_id'];
        $datos['equipo'] = $elem['Equipo_ID'];
        $datos['serie'] = $elem['serie'];
        $datos['modelo'] = $elem['Modelo'];
        $datos['fabricante'] = $elem['Fabricante'];
        $datos['asignado'] = $elem['Nombre'];
        $this->Conexion->insertar('qr_atributos_temp', $datos, $funciones = null);
    }

    // Consultar todos los atributos agregados para ese QR
    $query = "SELECT * FROM qr_atributos_temp WHERE idQr = ".$qr;
    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

function cargarItems(){
    // Recibe ID de QR por POST
    $qr = $this->input->post('qr');

    // Consulta todos los items temporales relacionados a ese QR
    $query = "SELECT * FROM qr_atributos_temp WHERE idQr = ".$qr;
    $res = $this->Conexion->consultar($query);
    if($res) {
        echo json_encode($res);
    }
}

    function eliminarAtributos(){
    // Elimina un atributo temporal del QR y devuelve los atributos restantes
    $id = $this->input->post('id');
    $qr = $this->input->post('qr');
    $where['id'] = $id;

    $this->Conexion->eliminar('qr_atributos_temp', $where);

    $query = "SELECT * FROM qr_atributos_temp WHERE idQr = ".$qr;
    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res);
    } 
}

function asignarCompras(){
    // Asigna un técnico fijo (id 34) a un item en rsitems (ML), y lo etiqueta como 'Compras' en atributos temporales
    $item = $this->input->post('item');
    $qr = $this->input->post('qr');

    $data['tecnico_id'] = '34';
    $where['Item_id'] = $item;
    $this->MLConexion->modificar('rsitems', $data, null, $where);

    $dato['asignado'] = 'Compras';
    $donde['item'] = $item;
    $this->Conexion->modificar('qr_atributos_temp', $dato, null, $donde);

    $query = "SELECT * FROM qr_atributos_temp WHERE idQr = ".$qr;
    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res);
    }
}

function validarAtributos(){
    // Verifica si un item ya está registrado en un QR específico
    $item = $this->input->post('item');
    $qr = $this->input->post('qr');
    $query = "SELECT * FROM qr_atributos_temp WHERE item = ".$item." AND idQr = ".$qr;
    $res = $this->Conexion->consultar($query);

    if($res) {
        echo json_encode($res);
    }
}

function getItem(){
    // Obtiene detalles de un atributo PR específico
    $item = $this->input->post('item');
    $query = "SELECT * FROM pr_atributos WHERE id = ".$item;
    $res = $this->Conexion->consultar($query, TRUE);

    if($res) {
        echo json_encode($res);
    }
}

function ValidarItem(){
    // Verifica si un item ya está ligado a una PR activa (no cancelada)
    $item = $this->input->post('item');

    $query = "SELECT pa.item, pa.idPr, pr.estatus 
              FROM pr_atributos pa 
              JOIN prs pr ON pa.idPr = pr.id 
              WHERE pa.item = '".$item."' AND pr.estatus != 'CANCELADO'";
    $res = $this->Conexion->consultar($query, TRUE);

    if($res) {
        echo json_encode($res);
    } 
}

function getFileEx(){
    // Obtiene el nombre y contenido binario del archivo de ejemplo asociado a un QR
    $id = $this->input->post('qr');

    $query = "SELECT nombre_archivoEjemplo, archivoEjemplo 
              FROM requisiciones_cotizacion 
              WHERE id = ".$id;
    
    $res = $this->Modelo->consulta($query, TRUE);

    if($res) {
        echo json_encode($res);
    } else {
        echo "";
    }
}

function getFileExample($qr){
    // Despliega un archivo PDF ejemplo en el navegador sin forzar descarga
    $row = $this->descargas_model->getFile($qr, 'requisiciones_cotizacion');
    $file = $row->archivoEjemplo;
    $nombre = $row->nombre_archivoEjemplo;

    header('Content-type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nombre . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    echo $file;
}

   function ajax_subirArchivoQRClon() {
    // Clona el archivo PDF de un QR y lo asocia al QR más reciente
    $id = $this->input->post('qr');

    // Obtener archivo original del QR base
    $query = "SELECT nombre_archivo, archivo FROM requisiciones_cotizacion WHERE id = " . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    $datos['archivo'] = $res->archivo;

    // Obtener el último QR generado (para usar su ID como nuevo archivo)
    $query = "SELECT * FROM requisiciones_cotizacion ORDER BY id DESC LIMIT 1";
    $r = $this->Conexion->consultar($query, TRUE);

    $datos['id'] = $r->id;
    $datos['nombre_archivo'] = str_pad($datos['id'], 6, "0", STR_PAD_LEFT) . ".pdf";

    if (!$this->Modelo->setQRFile($datos)) {
        trigger_error("Error al subir archivo", E_USER_ERROR);
    } else {
        echo "1";
    }
}

function checkFile() {
    // Verifica si el QR tiene un archivo cargado
    $id = $this->input->post('qr');
    $query = "SELECT nombre_archivo FROM requisiciones_cotizacion WHERE id = " . $id;
    $res = $this->Modelo->consulta($query, TRUE);

    if ($res->nombre_archivo) {
        echo 1;
    } else {
        echo 0;
    }
}

function uploadFileEx() {
    // Sube archivo de ejemplo (binario) a la requisición
    $datos['id'] = $this->input->post('qr');
    $datos['archivoEjemplo'] = file_get_contents($_FILES['file']['tmp_name']);
    $datos['nombre_archivoEjemplo'] = $_FILES['file']['name'];

    if (!$this->Modelo->archivo_ejemplo($datos)) {
        trigger_error("Error al subir archivo", E_USER_ERROR);
    } else {
        echo $datos['nombre_archivoEjemplo'];
    }
}

function eliminarArchivoEx() {
    // Elimina el archivo de ejemplo asociado a una requisición
    $datos['id'] = $this->input->post('qr');
    $datos['archivoejemplo'] = null;
    $datos['nombre_archivoejemplo'] = null;

    if (!$this->Modelo->archivo_ejemplo($datos)) {
        trigger_error("Error al eliminar archivo", E_USER_ERROR);
    } else {
        echo "1";
    }
}

function checkDestino() {
    // Devuelve el destino asignado a una requisición QR
    $id = $this->input->post('qr');
    $query = "SELECT destino FROM requisiciones_cotizacion WHERE id = " . $id;
    $res = $this->Conexion->consultar($query, TRUE);

    if ($res) {
        echo json_encode($res);
    } else {
        echo "1";
    }
}
}
