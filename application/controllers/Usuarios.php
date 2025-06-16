<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Controller {

    function __construct() {
    parent::__construct();
    $this->load->model('usuarios_model');
}

    function index() {
    // Carga vista principal de catálogo de usuarios
    $this->load->view('header');
    $this->load->view('usuarios/catalogo');
}

    function puestos($idPuesto) {
    // Obtiene usuarios por puesto específico
    $datos['usuarios'] = $this->usuarios_model->getUsuarios_Puesto($idPuesto);

    $this->load->view('header');
    $this->load->view('usuarios/catalogo', $datos);
}

    function ver($id = 0) {
    $this->load->model('privilegios_model');
    
    // Maneja ID de usuario desde POST si está presente
    if (isset($_POST['id'])) {
        $id = $this->input->post('id');
    }
    
    // Obtiene datos de usuario y sus privilegios
    $datos['usuario'] = $this->usuarios_model->getUsuario($id);
    $datos['privilegio'] = $this->privilegios_model->getPrivilegios($id);
    
    $this->load->view('header');
    $this->load->view('usuarios/ver', $datos);
}

    function alta() {
    $this->load->model('privilegios_model');
    
    // Prepara datos para formulario de alta de usuario
    $datos['puestos'] = $this->privilegios_model->listadoPuestos();
    
    $this->load->view('header');
    $this->load->view('usuarios/alta_usuario', $datos);
}

   function registrar() {
    $this->load->model('privilegios_model');
    $ACIERTOS = array();
    $ERRORES = array();

    // Prepara datos para nuevo usuario con valores normalizados
    $data = array(
        'no_empleado' => trim(strtoupper($this->input->post('no_empleado'))),
        'nombre' => trim(strtoupper($this->input->post('nombre'))),
        'paterno' => trim(strtoupper($this->input->post('paterno'))),
        'materno' => trim(strtoupper($this->input->post('materno'))),
        'departamento' => trim(strtoupper($this->input->post('departamento'))),
        'puesto' => $this->input->post('puesto'),
        'correo' => $this->input->post('correo'),
        'password' => sha1(trim(strtoupper($this->input->post('no_empleado')))),
        'activo' => '1',
        'foto' => file_get_contents(base_url('template/images/avatar.png')),
        'password_correo' => '',
    );

    // Registra nuevo usuario y maneja resultado
    $id_inserted = $this->usuarios_model->crear_usuario($data);

    if ($id_inserted) {
        // Asigna privilegios básicos al nuevo usuario
        $this->privilegios_model->agregarPrivilegio(array('usuario' => $id_inserted));
        $acierto = array('titulo' => 'Agregar Usuario', 'detalle' => 'Se ha agregado Usuario con Éxito');
        array_push($ACIERTOS, $acierto);
    } else {
        $error = array('titulo' => 'ERROR', 'detalle' => 'Error al agregar Usuario');
        array_push($ERRORES, $error);
    }
    
    // Almacena mensajes en sesión y redirecciona
    $this->session->aciertos = $ACIERTOS;
    $this->session->errores = $ERRORES;
    redirect(base_url('usuarios'));
}

   function subirfoto() {
    // Procesa imagen en base64 y actualiza foto de perfil
    $foto = $this->input->post('foto');
    $foto = str_replace('data:image/png;base64,', '', $foto);
    $foto = base64_decode($foto);

    $data = array(
        'foto' => $foto,
    );
    
    // Actualiza foto en base de datos y sesión
    if ($this->usuarios_model->updateFoto($data)) {
        $this->session->foto = $foto;
    }
    redirect(base_url('inicio'));
}

    function foto() {
    // Carga vista para subir nueva foto de perfil
    $this->load->view('header');
    $this->load->view('subir_foto');
}

    function modificar_contrasena() {
        $OLD = sha1($this->input->post('oldpass'));
        $NEW = sha1($this->input->post('newpass'));
        $NEW1 = sha1($this->input->post('newpass1'));
        $ACIERTOS = array();
        $ERRORES = array();

        $PSW_CORRECTO = $OLD == $this->session->password;
        $PSW_COINCIDEN = $NEW == $NEW1;

        //VALIDACION
        if (!$PSW_CORRECTO) {
            $error = array('titulo' => 'Contraseña Incorrecta', 'detalle' => 'La contraseña antigüa es incorrecta');
            array_push($ERRORES, $error);
        }
        if (!$PSW_COINCIDEN) {
            $error2 = array('titulo' => 'Contraseñas NO Coinciden', 'detalle' => 'No coinciden contraseñas');
            array_push($ERRORES, $error2);
        }

        //VALIDACION
        if ($PSW_CORRECTO && $PSW_COINCIDEN) {
            $this->db->set('password', $NEW);
            $this->db->where('id', $this->session->id);
            if ($this->db->update('usuarios')) {
                $this->session->password = $NEW;
                $acierto = array('titulo' => 'Contraseña Modificada', 'detalle' => 'Se ha modificado su contraseña con Éxito');
                array_push($ACIERTOS, $acierto);
            } else {
                $error3 = array('titulo' => 'ERROR', 'detalle' => 'Error al modificar contraseña');
                array_push($ERRORES, $error3);
            }
        }

        $this->session->aciertos = $ACIERTOS;
        $this->session->errores = $ERRORES;
        redirect(base_url('inicio'));
    }
    /// A J A X  F U N C T I O N S //

    function ajax_getUsuarios() {
    // Construye consulta base para obtener usuarios
    $query = "SELECT U.id, U.nombre, U.paterno, U.materno, U.no_empleado, U.puesto as id_puesto, U.correo, U.ultima_sesion, U.departamento, U.activo, U.jefe_directo, U.autorizador_compras, U.autorizador_compras_venta, concat(U.nombre, ' ', U.paterno) as User, concat(U.nombre, ' ', U.paterno, ' ', U.materno) as CompleteName,U.password_correo, P.puesto from usuarios U inner join puestos P on P.id = U.puesto where 1 = 1";
    
    // Filtro por estado activo
    if (isset($_POST['activo'])) {
        $activo = $this->input->post('activo');
        if ($activo == "0") {
            $query .= " and U.activo = '1'";
        }
    } else {
        $query .= " and U.activo = '1'";
    }
    
    // Filtros por texto y parámetro
    if (isset($_POST['texto']) && $_POST['texto']) {
        $texto = $this->input->post('texto');

        if (isset($_POST['parametro'])) {
            $parametro = $this->input->post('parametro');
            
            if ($parametro == "nombre") {
                $query .= " having User like '%$texto%'";
            } else if ($parametro == "id") {
                $query .= " and U.id = '$texto'";
            } else if ($parametro == "no_empleado") {
                $query .= " and U.no_empleado = '$texto'";
            } else if ($parametro == "correo") {
                $query .= " and U.correo like '%$texto%'";
            }
        } else {
            $query .= " and U.activo = 1 having User like '%$texto%' or P.puesto like '%$texto%'";
        }
    }

    $query .= " order by User";
    
    $res = $this->Conexion->consultar($query);
    if ($res) {
        echo json_encode($res);
    }
}

    function ajax_guardarDatos() {
    // Actualiza datos de usuario desde solicitud AJAX
    $usuario = json_decode($this->input->post('usuario'));
    echo $this->Conexion->modificar('usuarios', $usuario, null, array('id' => $usuario->id));
}

    function ajax_getPuestos() {
    // Obtiene todos los puestos disponibles
    $query = "SELECT * from puestos where 1 = 1";
    $res = $this->Conexion->consultar($query);
    echo json_encode($res);
}

// FUNCIONES DE RETORNO RÁPIDO //
    function name($id) {
    // Devuelve nombre completo de usuario por ID
    $res = $this->Conexion->consultar("SELECT ifnull(concat(U.nombre, ' ', U.paterno), '') as User from usuarios U where U.id = $id", TRUE);
    echo $res->User;
}

    function photo($id) {
    // Devuelve foto de perfil en formato PNG
    $res = $this->Conexion->consultar("SELECT ifnull(U.foto, '') as Photo from usuarios U where U.id = $id", TRUE);
    header("Content-type: image/png");
    echo $res->Photo;
}

    function uploadFoto() {
    // Actualiza foto de firma desde archivo subido
    $id = $this->input->post('id');
    $datos['foto_firma'] = file_get_contents($_FILES['file']['tmp_name']);
    $this->usuarios_model->updateFirma($id, $datos);
}

    function get_foto($id) {
    // Obtiene foto de usuario por ID (posible error en implementación)
    $photo = $this->Conexion->consultar("SELECT ifnull(U.foto, '') as Photo from usuarios U where U.id = $id", TRUE);
    if ($photo) {
        header("Content-type: image/png");
        echo $photo->file;  // Nota: debería ser $photo->Photo
    } else {
        echo "ERROR";
    }
}

}
