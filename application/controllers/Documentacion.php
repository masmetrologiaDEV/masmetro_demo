<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Documentacion extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('conexion_model', 'Conexion'); // Carga el modelo de conexión general
    }

    // Muestra la vista del catálogo de manuales
    function manuales() {
        $this->load->view('header');
        $this->load->view('documentacion/catalogo');
    }

    // Muestra la vista del mapa del sitio
    function mapa_sitio() {
        $this->load->view('header');
        $this->load->view('documentacion/mapa_sitio');
    }
}
