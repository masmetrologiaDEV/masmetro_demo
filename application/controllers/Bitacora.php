<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Bitacora para gestionar registros de software
class Bitacora extends CI_Controller {

    // Constructor del controlador
    function __construct() {
        parent::__construct();
    }

    // Página principal: carga la vista de la bitácora de software
    public function index() {
        $this->load->view('header');                   // Carga el encabezado común
        $this->load->view('bitacora/software');        // Carga la vista específica de bitácora de software
    }

    // Obtiene los registros de la bitácora (vía Ajax)
    function ajax_getLog() {
        $res = $this->Conexion->consultar("SELECT * from bitacora_software"); // Consulta todos los registros
        echo json_encode($res);                                                // Devuelve los datos en formato JSON
    }
}
