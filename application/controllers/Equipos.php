<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipos extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('tickets_IT_model','Modelo');
        $this->load->model('privilegios_model');
    }

    // Vista principal del catálogo de equipos TI
    public function ti() {
        $datos['usuarios'] = $this->privilegios_model->listadoJefes();
        $this->load->view('header');
        $this->load->view('equipos/ti/catalogo', $datos);
    }

    // Muestra historial de un equipo específico
    function historial($idequipo){
        $datosequipo = $this->Modelo->getEquipo($idequipo); // No usado en esta vista, podrías revisar si es necesario
        $datos['equipo'] = $this->Modelo->historial($idequipo);
        $this->load->view('header');
        $this->load->view('equipos/ti/historial', $datos);
    }

    // Vista general de revisiones de equipos
    function revisiones() {
        $datos['equipo'] = $this->Modelo->Equipos();
        $this->load->view('header');
        $this->load->view('equipos/ti/revisiones', $datos);
    }

    // Muestra la vista para realizar mantenimiento a un equipo específico
    function mantenimiento($idE) {
        $datos['equipo'] = $this->Modelo->getEquipos($idE);
        $this->load->view('header');
        $this->load->view('equipos/ti/mantenimiento', $datos);
    }

    // Muestra el historial de mantenimiento de un equipo
    function historialMantto($idE) {
        $datos['equipo'] = $this->Modelo->getEquipos($idE);
        $datos['manto'] = $this->Modelo->historialMantto($idE);
        $this->load->view('header');
        $this->load->view('equipos/ti/historialMantto', $datos);
    }

    // Vista de hallazgos para un mantenimiento específico
    public function hallazgos($iM) {
        $id = $datos['manto'] = $this->Modelo->Mantto($iM);
        $a = $id->idEquipo;
        $datos['equipo'] = $this->Modelo->getEquipos($a);
        $datos['fotos'] = $this->Modelo->fotosMantto($iM);
        $this->load->view('header');
        $this->load->view('equipos/ti/hallazgos', $datos);
    }

    function ajax_setEquiposTI(){
    $equipo = json_decode($this->input->post('equipo'));
    $file = $this->input->post('iptFoto');

    // Manejo de imagen anterior y subida de nueva imagen
    if($equipo->foto != "default.png") {
        unlink('data/equipos/ti/fotos/' . $equipo->foto);
    } else if($file == "undefined") {
        $equipo->foto = 'default.png';
    } else {
        $config['upload_path'] = 'data/equipos/ti/fotos/';
        $config['allowed_types'] = 'gif|jpg|png';
        $config['encrypt_name'] = TRUE;
        $this->load->library('upload', $config);

        if ($this->upload->do_upload('iptFoto')) {
            $equipo->foto = $this->upload->data('file_name');
        }
    }

    // Tiempos de alta/asignación automáticos
    $funciones = array('fecha_alta' => 'CURRENT_TIMESTAMP()', 'fecha_asignacion' => 'CURRENT_TIMESTAMP()');

    if($equipo->id == 0) {
        // Inserta nuevo equipo
        $res = $this->Conexion->insertar('equipos_it', $equipo, $funciones);
    } else {
        // Guarda histórico de asignación
        $query = 'INSERT INTO `equiposHis` (`idEqH`, `idequipo`, `idus`, `fecha`) VALUES (NULL, '.$equipo->id.', '.$equipo->asignado.', CURRENT_TIMESTAMP);';
        $this->Conexion->comando($query);

        // Actualiza equipo existente
        $this->Conexion->modificar('equipos_it', $equipo, $funciones, array('id' => $equipo->id));
    }
}

    function ajax_getEquiposTI(){
    $texto = $this->input->post('texto');
    $tipo = $this->input->post('tipo');
    $inactivo = $this->input->post('inactivo');
    $asignado = $this->input->post('asignado');

    // Consulta base con unión para obtener el nombre del usuario asignado
    $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado ";

    // Si se recibe un ID específico, se filtra directamente por él
    if(isset($_POST['id'])){
        $id = $this->input->post('id');
        $query .= " where E.id = $id";
    }
    // Equipos inactivos por tipo
    else if ($inactivo == 1 && !empty($tipo)) {
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 0 and E.tipo like '".$tipo."'";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Equipos inactivos por texto (buscando por ID)
    else if ($inactivo == 1 && !empty($texto)) {
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 0 and E.id =  '".$texto."'";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Todos los equipos inactivos
    else if ($inactivo == 1) {
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 0";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Búsqueda por campo JSON: No_Inventario_Interno o Serie (en caso de celulares)
    else if(!empty($texto)){
        $campo = '$.No_Inventario_Interno';
        if ($tipo == 'Celular') {
            $campo = '$.Serie';
        }
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo=1 and  JSON_EXTRACT(campos, '".$campo."') = '".$texto."'";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Filtro por tipo
    else if(!empty($tipo)){
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo=1 and E.tipo like '".$tipo."'";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Filtro por usuario asignado
    else if(!empty($asignado)){
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo=1 and E.asignado = '".$asignado."'";
        if(isset($_POST['id'])){
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
    }
    // Por defecto, mostrar todos los equipos activos
    else{
        $query .= " where E.activo=1";
    }

    // Ejecutar la consulta y retornar resultados en formato JSON
    $res = $this->Conexion->consultar($query, isset($_POST['id']));
    echo json_encode($res);
}

    function getEquiposTI(){     
    $tipo = $this->input->post('tipo');
    $texto = $this->input->post('texto');

    // Consulta base con nombre del asignado y fecha de última revisión (Ultrev)
    $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado, ifnull((SELECT max(fecha) from manttoEquipos where idEquipo=E.id),'') as Ultrev FROM equipos_it E LEFT JOIN usuarios U ON U.id = E.asignado where E.activo = 1";

    // Si se filtra por tipo, se construye la consulta según ese parámetro
    if(!empty($tipo)){
        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado,  ifnull((SELECT max(fecha) from manttoEquipos where idEquipo=E.id),'') as Ultrev from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 1 and E.tipo like '".$tipo."'";
    }

    // Se filtra por equipos cuya última revisión fue hace al menos 2 meses
    $query .= " HAVING `Ultrev` < last_day(now()) + interval 1 day - interval 2 month";

    if(!empty($texto)){
        $campo = '$.No_Inventario_Interno';
        if ($tipo == 'Celular') {
            $campo = '$.Serie';
        }
        $query .= " and  JSON_EXTRACT(campos, '".$campo."') = '".$texto."'";
    }

    $res = $this->Conexion->consultar($query);
    if($res){
        echo json_encode($res);
    } else {
        echo "";
    }
}

    function equipos(){
    $texto = $this->input->post('texto');
    $texto = trim($texto);
    $inactivo = $this->input->post('inactivo');

    // Consulta base
    $query = "SELECT * from equipos_it";

    // Filtrado por estado de actividad
    if($activo!="1"){
        $query .="where activo=1";
    } elseif($activo=="1"){
        $query .="where activo=0";
    }

    $res = $this->Conexion->consultar($query);
    if($res)
    {
        echo json_encode($res);
    }
    else{
        echo "";
    }
}

    function registrarMantto(){

    $equipo = $this->input->post('equipo');
    $id = null;
    $fototmp = $_FILES['foto'];
    $fSize = intval(implode(".", $fototmp['size']));
    $countfoto = count($fototmp['name']);

    // Datos de mantenimiento recopilados del formulario
    $datos = array(
        'idEquipo' => $this->input->post('equipo'),
        'usMantto' => $this->session->id,
        'case' => $this->input->post('case'),
        'vidTem' => $this->input->post('vidTem'),
        'usb' => $this->input->post('usb'),
        'manosL' => $this->input->post('ml'),
        'bateria' => $this->input->post('bateria'),
        'datos' => $this->input->post('datos'),
        'comentarios' => $this->input->post('comentarios'),
        'disco' => $this->input->post('disco'),
        'cpu' => $this->input->post('cpu'),
        'tecMouse' => $this->input->post('tecMouse'),
        'abanicos' => $this->input->post('abanicos'),
    );

    $this->Modelo->registrarMantto($datos);
    echo var_dump($datos); 

    // Obtener el ID del mantenimiento recién registrado
    $query = "SELECT MAX(idME) as id FROM manttoEquipos WHERE idEquipo= '".$equipo."'";
    $res = $this->Conexion->consultar($query);
    foreach($res as $elem){
        $id = $elem->id;
    }

    // Guardar fotos si se cargaron
    if($fSize != 0){            
        for ($i = 0; $i < $countfoto; $i++) { 
            $fotofile = file_get_contents($fototmp['tmp_name'][$i]);   
            $data = array(   
                'idMantto' => $id,
                'fotos' => $fotofile,
            );
            $id_inserted = $this->Modelo->registrar($data);
        }   
    }

    redirect(base_url('equipos/revisiones'));  
}

    function generador() {
    // Consulta registros del generador junto con el nombre del usuario asociado
    $datos['data'] = $this->Conexion->consultar("SELECT g.*, concat(u.nombre, ' ', u.paterno) as name FROM generador g join usuarios u on u.id=g.usuario ORDER BY fecha DESC");
    $this->load->view('header');
    $this->load->view('equipos/ti/generador', $datos);
}

function registrar_generador() {
    // Se obtiene la imagen del formulario como archivo binario
    $foto = file_get_contents($_FILES['foto']['tmp_name']);
    
    // Datos recopilados del formulario de registro
    $data = array(
        'fecha_inicio' => $this->input->post('fecha_inicio'),
        'fecha_final' => $this->input->post('fecha_final'),
        'duracion' => $this->input->post('duracion'),
        'foto' => $foto,
        'porcentaje' => $this->input->post('porcentaje') . "% capacidad",
        'amperaje' => $this->input->post('amperaje'),
        'usuario' => $this->session->id,
        'consumo' => $this->input->post('consumo') . " kW",
        'motor' => $this->input->post('motor'),
        'arranques' => $this->input->post('arranques'),
    );
    
    $this->Conexion->insertar('generador', $data);
    redirect(base_url('equipos/generador'));
}

function uploadFoto() {
    // Actualiza la foto para un registro de generador específico
    $id = $this->input->post('id');
    $datos['foto'] = file_get_contents($_FILES['file']['tmp_name']);
    $this->Conexion->modificar('generador', $datos, null, array('id' => $id));
}
}
