<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Descargas extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('descargas_model', 'Modelo'); // Modelo principal para descargas
        $this->load->helper('download'); // Helper para forzar descargas
        $this->load->model('MLConexion_model', 'MLConexion'); // Modelo para conexión con WO_Master
    }

    public function index() {
        // Sin implementación; puede utilizarse como landing para el controlador si se requiere
    }

    // Descarga archivos adjuntos a tickets de autos
    function tickets_AT($id) {
        $row = $this->Modelo->getFile($id, 'tickets_autos_archivos');
        $file = $row->archivo;
        $nombre = $row->nombre;
        force_download($nombre, $file);
    }

    // Descarga archivos adjuntos a tickets de edificio
    function tickets_ED($id) {
        $row = $this->Modelo->getFile($id, 'tickets_edificio_archivos');
        $file = $row->archivo;
        $nombre = $row->nombre;
        force_download($nombre, $file);
    }

    // Descarga archivos adjuntos a tickets de sistemas
    function tickets_IT($id) {
        $row = $this->Modelo->getFile($id, 'tickets_sistemas_archivos');
        $file = $row->archivo;
        $nombre = $row->nombre;
        force_download($nombre, $file);
    }

    // Descarga archivos adjuntos a órdenes de trabajo (WO_Master)
    function ordenes_trabajo($id) {
        $row = $this->MLConexion->consultar("SELECT * from WO_Master where WorkOrder_ID=".$id, true);
        $file = $row->archivo;
        $nombre = $row->nombre_archivo;
        force_download($nombre, $file);
    }
}
