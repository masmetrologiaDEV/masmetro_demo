<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador para gestión de vehículos (autos)
class Autos extends CI_Controller {

    // Constructor: carga modelos y librerías necesarias
    function __construct() {
        parent::__construct();
        $this->load->model('autos_model','Modelo');          // Modelo principal de autos
        $this->load->model('privilegios_model');             // Modelo de privilegios
        $this->load->model('usuarios_model');                // Modelo de usuarios
        $this->load->model('descargas_model');               // Modelo para descargas
        $this->load->library('correos');                     // Librería de envío de correos
        $this->load->model('conexion_model', 'Conexion');    // Modelo de conexión a DB
    }

    // Muestra el catálogo principal de vehículos
    function index() {
        $datos['autos'] = $this->Modelo->getCatalogo();  // Obtiene listado de autos
        $datos['titulo'] = "Autos";                     // Título para la vista
        $this->load->view('header');                     // Carga el header
        $this->load->view('autos/catalogo',$datos);      // Carga la vista principal
    }

    // Muestra detalles de un vehículo específico
    function ver($idauto) {
        // Actualiza responsable si se envió el formulario
        $responsable = $this->input->post('responsable');
        if(isset($responsable)) {
            $this->Modelo->updateAuto($idauto, array('responsable' => $responsable));
        }
        
        // Obtiene datos del auto y usuarios disponibles
        $datos['auto'] = $this->Modelo->getAuto($idauto);
        $datos['responsable'] = $this->usuarios_model->getUsuario($datos['auto']->responsable);
        $datos['usuarios'] = $this->usuarios_model->getUsuarios();
        
        $this->load->view('header');
        $this->load->view('autos/ver_auto', $datos);
    }
    
    // Muestra vista de seguimiento GPS
    function gps() {
        $datos['autos'] = $this->Modelo->gpsAutos();  // Obtiene autos con GPS
        $datos['titulo'] = "GPS";                     // Título para la vista
        $this->load->view('header');
        $this->load->view('autos/gps',$datos);
    }

    // Muestra formulario para registrar GPS de un vehículo
    function alta_gps($idauto) {
        $datosCoche = $this->Modelo->getAuto($idauto);
        $datos['auto_foto'] = $datosCoche->foto;
        $datos['auto'] = $datosCoche->id;
        $datos['auto_marca'] = $datosCoche->marca;
        $datos['auto_combustible'] = $datosCoche->combustible;
        $datos['auto_placas'] = $datosCoche->placas;
        $datos['gps'] = $this->Modelo->getGPS($idauto); 
        $datos['titulo'] = "Autos";
        $this->load->view('header');
        $this->load->view('autos/alta_gps',$datos);
    }

    // Muestra formulario para editar vehículo
    function editar($idauto) {
        $datos['auto'] = $this->Modelo->getAutos($idauto);
        $datos['usuarios'] = $this->privilegios_model->listadoJefes();
        $this->load->view('header');
        $this->load->view('autos/editar',$datos);
    }

    // Muestra vista de uso de vehículos
    function uso_autos() {
        $this->load->view('header');
        $this->load->view('autos/uso_autos');
    }

    // Muestra formulario para registrar nuevo vehículo
    function alta_autos() {
        $datos['usuarios'] = $this->privilegios_model->listadoJefes();
        $this->load->view('header');
        $this->load->view('autos/alta_autos', $datos);
    }

    // Muestra revisiones de un vehículo específico
    function revisiones_ANT($auto) {
        $datos['auto'] = $auto;
        $datos['rev'] = $this->Modelo->getRevisiones($auto);
        $this->load->view('header');
        $this->load->view('autos/revisiones', $datos);
    }

    // Muestra opciones de revisiones
    function revisiones() {
        $this->load->view('header');
        $this->load->view('autos/revisiones_opciones');
    }

    // Muestra revisiones pendientes
    function revisiones_pendientes() {
        $datos['autos'] = $this->Modelo->getRevPendientes();
        $datos['titulo'] = "Revisiones Pendientes";
        $this->load->view('header');
        $this->load->view('autos/catalogo',$datos);
    }

    // Muestra revisiones programadas para hoy
    function revisiones_hoy() {
        $datos['autos'] = $this->Modelo->getRevHoy();
        $datos['titulo'] = "Revisiones de Hoy";
        $this->load->view('header');
        $this->load->view('autos/catalogo',$datos);
    }

    // Muestra próximos mantenimientos
    function proximos_mttos() {
        $head['url_actual'] = current_url();
        $datos['autos'] = $this->Modelo->getProxMttos();
        $datos['titulo'] = "Proximos Mantenimientos";
        $this->load->view('header', $head);
        $this->load->view('autos/proximos_mttos',$datos);
    }

    // Muestra formulario para registrar revisión
    function registrar_revision($auto) {
        $head['url_actual'] = current_url();
        $datos['iu'] = uniqid();
        $datos['auto'] = $auto;
        $res = $this->Modelo->getMotorPlacasKm($auto);
        $datos['combustible'] = $res->combustible;
        $datos['placas'] = $res->placas;
        $datos['kilometraje'] = $res->kilometraje;
        $this->load->view('header', $head);
        $this->load->view('autos/registrar_revision',$datos);
    }

    // Muestra checklist de revisión en PDF
    function ver_checklist($id) {
        $res = $this->Modelo->getChecklist($id);
        $pdf = $res->checklist;
        $datos['data'] = $pdf;
        $this->load->view('pdf', $datos);
    }

    // Muestra foto de hallazgo específico
    function hallazgo_foto($id_foto) {
        $photo = $this->Modelo->getHallazgoFoto($id_foto);
        if($photo) {
            header("Content-type: image/png");
            echo $photo->foto;
        } else {
            echo "ERROR";
        }
    }

    // Muestra hallazgos de una revisión específica
    function hallazgos($id_rev) {
        // Datos de la revisión
        $datosRevision = $this->Modelo->getRevision($id_rev);
        $auto = $datosRevision->auto;
        $datos['rev_id'] = $datosRevision->id;
        $datos['rev_fecha'] = $datosRevision->fecha;
        $datos['rev_User'] = $datosRevision->User;
        $datos['rev_kilometraje'] = $datosRevision->kilometraje;
        $datos['rev_combustible'] = $datosRevision->combustible;
        $datos['rev_placas'] = $datosRevision->placas;
        $datos['rev_vencimiento_poliza'] = $datosRevision->vencimiento_poliza;
        $datos['rev_ecologico'] = $datosRevision->vencimiento_ecologico;

        // Datos del vehículo
        $datosCoche = $this->Modelo->getAuto($auto);
        $datos['auto_foto'] = $datosCoche->foto;
        $datos['auto_marca'] = $datosCoche->marca;
        $datos['auto_combustible'] = $datosCoche->combustible;
        $datos['auto_placas'] = $datosCoche->placas;

        // Hallazgos encontrados
        $datos['carroceria'] = $this->Modelo->getHallazgosCarroceria($id_rev);
        $datos['otros'] = $this->Modelo->getHallazgosOtros($id_rev);
        
        $this->load->view('header');
        $this->load->view('autos/hallazgos', $datos);
    }

    // Guarda fotos temporales de hallazgos
    function temp_photos() {
        $datos['iu'] = $this->input->post('iu');
        $datos['tipo'] = 'REVISION';
        $datos['usuario'] = $this->session->id;
        $datos['objeto'] = $this->input->post('auto');
        $datos['texto'] = $this->input->post('texto');
        $fotoB64 = $this->input->post('archivo');
        $datos['archivo'] = base64_decode($fotoB64);
        
        $res = $this->Modelo->saveTempPhotos($datos);
        if($res > 0) {
            $respuesta = array('id' => $res, 'file' => $fotoB64);
            echo json_encode($respuesta);
        }
    }

    // Elimina fotos temporales
    function delete_temp_photos() {
        $id_temp = $this->input->post('id');
        $res = $this->Modelo->deleteTempPhotos($id_temp);
        if($res) {
            echo "1";
        }
    }

    // Procesa el registro de una revisión completa
    function registrarRev() {
        $ACIERTOS = array(); 
        $ERRORES = array();
        $VARIABLES = array();

        // Datos básicos de la revisión
        $descripcion = $this->input->post('texto');
        $usuario = $this->session->nombre;
        $AUTO = $this->input->post('auto');
        $IU = $this->input->post('iu');

        // Datos del vehículo
        $KILOMETRAJE = $this->input->post('kilometraje');
        $COMBUSTIBLE = $this->input->post('combustible');
        $PLACAS = $this->input->post('placas');
        $timePoliza = strtotime($this->input->post('vencimientoPoliza'));
        $VENCIMIENTO_POLIZA = date("Y-m-d", $timePoliza);
        $timeEco = strtotime($this->input->post('vencimientoEcologico'));
        $VENCIMIENTO_ECOLOGICO = date("Y-m-d", $timeEco);

        // Recolección de variables de revisión
        $VARIABLES['Kilometraje'] = array('Kilometraje' => $this->input->post('kilometraje'), 'comentario' => $this->input->post('com_kilometraje'));
        $VARIABLES['Aceite del motor'] = array('Aceite del motor' => $this->input->post('aceiteMotor'), 'comentario' => $this->input->post('com_aceiteMotor'));
        $VARIABLES['Condiciones del aceite'] = array('Condiciones del aceite' => $this->input->post('condicionesAceite'), 'comentario' => $this->input->post('com_condicionesAceite'));
        $VARIABLES['Limpia parabrisas delantero'] = array('Limpia parabrisas delantero' => $this->input->post('llpbDelantero'), 'comentario' => $this->input->post('com_llpbDelantero'));
        $VARIABLES['Limpia parabrisas trasero'] = array('Limpia parabrisas trasero' => $this->input->post('llpbTrasero'), 'comentario' => $this->input->post('com_llpbTrasero'));
        $VARIABLES['Deposito de refrigerante'] = array('Deposito de refrigerante' => $this->input->post('refrigeranteDeposito'), 'comentario' => $this->input->post('com_refrigeranteDeposito'));
        $VARIABLES['Radiador'] = array('Radiador' => $this->input->post('refrigeranteRadiador'), 'comentario' => $this->input->post('com_refrigeranteRadiador'));
        $VARIABLES['Liquido de frenos'] = array('Liquido de frenos' => $this->input->post('liquidoFrenos'), 'comentario' => $this->input->post('com_liquidoFrenos'));
        $VARIABLES['Direccion hidraulica'] = array('Direccion hidraulica' => $this->input->post('direccionH'), 'comentario' => $this->input->post('com_direccionH'));
        $VARIABLES['Direccional izquierda'] = array('Direccional izquierda' => $this->input->post('direccionalIzq'), 'comentario' => $this->input->post('com_direccionalIzq'));
        $VARIABLES['Direccional derecha'] = array('Direccional derecha' => $this->input->post('direccionalDer'), 'comentario' => $this->input->post('com_direccionalDer'));
        $VARIABLES['Lampara izquierda'] = array('Lampara izquierda' => $this->input->post('lamparaIzq'), 'comentario' => $this->input->post('com_lamparaIzq'));
        $VARIABLES['Lampara derecha'] = array('Lampara derecha' => $this->input->post('lamparaDer'), 'comentario' => $this->input->post('com_lamparaDer'));
        $VARIABLES['Lampara trasera izquierda'] = array('Lampara trasera izquierda' => $this->input->post('traseraIzq'), 'comentario' => $this->input->post('com_traseraIzq'));
        $VARIABLES['Lampara trasera derecha'] = array('Lampara trasera derecha' => $this->input->post('traseraDer'), 'comentario' => $this->input->post('com_traseraDer'));
        $VARIABLES['Lampara de emergencia'] = array('Lampara de emergencia' => $this->input->post('emergencia'), 'comentario' => $this->input->post('com_emergencia'));
        $VARIABLES['Reversa'] = array('Reversa' => $this->input->post('reversa'), 'comentario' => $this->input->post('com_reversa'));
        $VARIABLES['Tapiceria'] = array('Tapiceria' => $this->input->post('tapiceria'), 'comentario' => $this->input->post('com_tapiceria'));
        $VARIABLES['Controles'] = array('Controles' => $this->input->post('controles'), 'comentario' => $this->input->post('com_controles'));
        $VARIABLES['Kit de herramientas'] = array('Kit de herramientas' => $this->input->post('kitHerramientas'), 'comentario' => $this->input->post('com_kitHerramientas'));
        $VARIABLES['Tapetes'] = array('Tapetes' => $this->input->post('tapetes'), 'comentario' => $this->input->post('com_tapetes'));
        $VARIABLES['Placa delantera'] = array('Placa delantera' => $this->input->post('placaDelantera'), 'comentario' => $this->input->post('com_placaDelantera'));
        $VARIABLES['Placa trasera'] = array('Placa trasera' => $this->input->post('placaTrasera'), 'comentario' => $this->input->post('com_placaTrasera'));
        $VARIABLES['Tarjeta de combustible'] = array('Tarjeta de combustible' => $this->input->post('tarjetaCombustible'), 'comentario' => $this->input->post('com_tarjetaCombustible'));
        $VARIABLES['Tarjeta de circulacion'] = array('Tarjeta de circulacion' => $this->input->post('tarjetaCirculacion'), 'comentario' => $this->input->post('com_tarjetaCirculacion'));
        $VARIABLES['Poliza'] = array('Poliza' => $this->input->post('poliza'), 'comentario' => $this->input->post('com_poliza'));

        // Identificación de fallas
        $FALLAS = array();
        foreach ($VARIABLES as $key => $value) {
            if($value[$key] == "NG") {
                $FALLAS[$key] = $value['comentario'];
            }
        }

        // Guarda en base de datos
        $datos = array(
            'auto' => $AUTO, 
            'usuario' => $this->session->id, 
            'kilometraje' => $KILOMETRAJE, 
            'combustible' => $COMBUSTIBLE, 
            'placas' => $PLACAS, 
            'vencimiento_poliza' => $VENCIMIENTO_POLIZA, 
            'vencimiento_ecologico' => $VENCIMIENTO_ECOLOGICO
        );
        
        $db_response = $this->Modelo->saveChecklist($datos);
        
        if($db_response['EXITO']) {
            // Actualiza datos del vehículo
            $AutoData = array(
                'kilometraje' => $KILOMETRAJE, 
                'vencimiento_poliza' => $VENCIMIENTO_POLIZA, 
                'vencimiento_ecologico' => $VENCIMIENTO_ECOLOGICO
            );
            $this->Modelo->updateAuto($AUTO, $AutoData);

            // Registra hallazgos si existen
            if(isset($descripcion)) {
                $arreglo = array('revision' => $db_response['ID'], 'iu' => $IU);
                $this->Modelo->saveHallazgosCarroceria($arreglo);
            }

            // Registra fallas encontradas
            if(isset($FALLAS)) {
                foreach ($FALLAS as $key => $value) {
                    $arreglo = array('revision' => $db_response['ID'], 'tipo' => $key, 'descripcion' => $value);
                    $this->Modelo->saveHallazgos($arreglo);
                }

                $acierto = array('titulo' => 'Registro de Revisión', 'detalle' => 'Se ha registrado Revisión con Éxito');
                array_push($ACIERTOS, $acierto);
            }

            // Notifica si está próximo el mantenimiento
            $prox = $this->Modelo->getMotorPlacasKm($AUTO);
            if(($prox->kilometraje + 200) >= $prox->proximo_mtto) {
                $Auto = $this->Modelo->getAuto($AUTO);
                $Resp = $this->Modelo->getResponsable($Auto->responsable);

                $data['auto'] = $Auto->fabricante . ' ' . $Auto->marca . ' ' . $Auto->modelo;
                $data['placas'] = $Auto->placas;
                $data['serie'] = $Auto->serie;
                $data['kmActual'] = number_format($KILOMETRAJE);
                $data['proxMtto'] = number_format($Auto->proximo_mtto);
                
                if($Resp) {
                    $data['correoResponsable'] = $Resp->correo;
                    $this->correos->proxMtto200($data);
                }
            }
        } else {
            $error = array('titulo' => 'ERROR', 'detalle' => 'Error al registrar revisión');
            array_push($ERRORES, $error);
        }

        $this->session->aciertos = $ACIERTOS;
        $this->session->errores = $ERRORES;
        redirect(base_url('inicio'));
    }

    // Obtiene lista de vehículos para AJAX
    function ajax_getAutos() {
        $res = $this->Conexion->consultar("SELECT id, marca, placas from autos order by marca");
        if($res) {
            echo json_encode($res);
        }
    }

    // Obtiene eventos de vehículos para calendario
    function ajax_getEventos() {
        $res = $this->Conexion->consultar("SELECT BA.id, BA.inicio as start, BA.final as end, BA.usuario, BA.usuarios, BA.auto, concat(A.placas, ' - ', A.marca) as title, BA.destino, BA.visita, BA.rsi, BA.comentarios, BA.equipo, BA.color from bitacora_autos BA inner join autos A on A.id = BA.auto");
        if($res) {
            echo json_encode($res);
        }
    }

    // Crea evento para vehículo (AJAX)
    function ajax_crearEvento() {
        $data = json_decode($this->input->post('data'));
        $data->usuario = $this->session->id;
        $f = array('fecha' => 'CURRENT_TIMESTAMP()');
        $res = $this->Conexion->insertar('bitacora_autos', $data, $f);
        echo $res;
    }

    // Elimina evento de vehículo (AJAX)
    function ajax_borrarEvento() {
        $where['id'] = $this->input->post('id');
        if($this->Conexion->eliminar('bitacora_autos', $where)) {
            echo "1";
        }
    }

    // Muestra foto de vehículo
    function photo($id) {
        $res = $this->Conexion->consultar("SELECT ifnull(A.foto, '') as Photo from autos A where A.id = $id", TRUE);
        header("Content-type: image/png");
        echo $res->Photo;
    }
  
    // Registra o actualiza GPS de vehículo
    function registrarGPS() {
        $auto = intval($this->input->post('idAuto'));
        $res = $this->Conexion->consultar("SELECT * from gpsAutos where idAuto = ".$auto);

        $data = array(
            'idAuto' => $auto,
            'marca' => $this->input->post('marca'),
            'imei' => $this->input->post('imei'),
            'modelo' => $this->input->post('modelo'),
            'chip' => $this->input->post('chip'),       
            'telefono' => $this->input->post('nchip'),       
        );       
        
        if($res) {
            $id_inserted = $this->Modelo->updateGps($auto, $data);
        } else {
            $id_inserted = $this->Modelo->registrar_GPS($data);
        }
        
        redirect(base_url('autos/alta_gps/'.$auto));
    }

    // Registra nuevo vehículo
    function registrar() {
        $foto = file_get_contents($_FILES['foto']['tmp_name']);
        $poliza = file_get_contents($_FILES['poliza']['tmp_name']);
        
        $data = array(
            'serie' => $this->input->post('serie'),
            'fabricante' => $this->input->post('fabricante'),
            'marca' => $this->input->post('marca'),
            'modelo' => $this->input->post('modelo'),
            'combustible' => $this->input->post('combustible'),
            'kilometraje' => $this->input->post('kilometraje'),
            'placas' => $this->input->post('placas'),
            'no_poliza' => $poliza,
            'vencimiento_poliza' => $this->input->post('vencimiento_poliza'),
            'vencimiento_ecologico' => $this->input->post('vencimiento_ecologico'),
            'tarjeta_combustible' => $this->input->post('tarjeta_combustible'),
            'foto' => $foto,
            'responsable' => $this->input->post('responsable'),
        );

        $id_inserted = $this->Modelo->registrar_auto($data);
        redirect(base_url('autos'));
    }

    // Actualiza datos de vehículo
    function updateAuto() {
        $idauto = $this->input->post('idAuto');
        $data = array(
            'serie' => $this->input->post('serie'),
            'fabricante' => $this->input->post('fabricante'),
            'marca' => $this->input->post('marca'),
            'modelo' => $this->input->post('modelo'),
            'placas' => $this->input->post('placas'),
            'vencimiento_ecologico' => $this->input->post('vencimiento_ecologico'),
            'tarjeta_combustible' => $this->input->post('tarjeta_combustible'),
            'vencimiento_poliza' => $this->input->post('vencimiento_poliza'),
            'activo' => $this->input->post('activo'),   
            'responsable' => $this->input->post('responsable'),       
        );
       
        $this->Modelo->updateAuto($idauto, $data);
        redirect(base_url('autos/'));
    }

    // Obtiene vehículos filtrados (AJAX)
    function ajaxgetAutos() {
        $activo = $this->input->post('activo');
        $parametro = $this->input->post('parametro');
        $texto = $this->input->post('texto');
        
        $query = "SELECT A.id, A.fabricante, A.marca, A.serie, A.placas, A.responsable, A.modelo, A.activo,ifnull(concat(U.nombre, ' ', U.paterno), 'N/A') as Responsable, (SELECT max(fecha) from autos_revisiones where auto=A.id) as Ultrev, (SELECT max(id) from autos_revisiones where auto=A.id) as IdUltrev FROM autos A LEFT JOIN usuarios U ON A.responsable = U.id where 1=1 ";
        
        if($activo == "0") {
            $query .= " and A.activo = '1'";
        } else {
            $query .= " and A.activo = '0'"; 
        }

        if(!empty($texto)) {
            if($parametro == 'placas') {
                $query .=" and A.placas = '". $texto ."'";
            } elseif($parametro == 'serie') {
                $query .= " and A.serie ='". $texto ."'";
            }
        }
        
        $res = $this->Conexion->consultar($query);
        if($res) {
            echo json_encode($res);
        }
    }

    // Muestra archivo de póliza
    function getFile($id) {
        $row = $this->descargas_model->getFile($id, 'autos');
        $file = $row->no_poliza;
        header('Content-type: application/pdf');
        header('Content-Transfer-Encoding: binary');
        header('Accept-Ranges: bytes');
        echo $file;
    }

    // Sube foto de vehículo
    function uploadFoto() {
        $id = $this->input->post('id');
        $datos['foto'] = file_get_contents($_FILES['file']['tmp_name']);
        $this->Modelo->updateAuto($id,$datos);
    }

    // Sube póliza de vehículo
    function uploadPoliza() {
        $id = $this->input->post('id');
        $datos['no_poliza'] = file_get_contents($_FILES['file']['tmp_name']);
        if(!$this->Modelo->updateAuto($id,$datos)) {
            trigger_error("Error al subir archivo", E_USER_ERROR);
        }
    }
}