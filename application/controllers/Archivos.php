<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador para manejo de archivos (PDF, documentos y descargas)
class Archivos extends CI_Controller {

    // Constructor: carga el modelo de conexión a base de datos
    function __construct() {
        parent::__construct();
        $this->load->model('conexion_model', 'Conexion');
    }
    
    // Muestra un archivo PDF en línea desde la base de datos
    function pdf() {
        // Verifica que existan los parámetros requeridos
        if(isset($_POST['tabla']) && isset($_POST['id'])) {
            $tabla = $this->input->post("tabla");  // Tabla donde está almacenado
            $id = $this->input->post("id");        // ID del registro
            
            // Obtiene el archivo y su nombre desde la base de datos
            $res = $this->Conexion->consultar("SELECT nombre, archivo from $tabla where id = $id", TRUE);
            $nombre = $res->nombre;  // Nombre original del archivo
            $file = $res->archivo;    // Contenido binario del PDF
            
            // Configura headers para visualización en navegador
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $nombre . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            echo $file;  // Imprime el contenido del PDF
        } else {
            // Redirecciona si faltan parámetros
            redirect(base_url('inicio'));
        }
    }

    // Muestra un documento genérico (similar a pdf() pero con campo personalizable)
    function documento() {
        // Verifica parámetros requeridos (incluyendo el campo específico)
        if(isset($_POST['tabla']) && isset($_POST['id']) && isset($_POST['campo'])) {
            $id = $this->input->post("id");     // ID del registro
            $tabla = $this->input->post("tabla"); // Tabla de origen
            $campo = $this->input->post("campo"); // Campo que contiene el archivo
            
            // Obtiene el archivo desde la base de datos
            $res = $this->Conexion->consultar("SELECT $campo from $tabla where id = $id", TRUE);            
            $file = $res->$campo;  // Contenido binario del documento
            
            // Configura headers para visualización en navegador
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $campo . '"');
            header('Content-Transfer-Encoding: binary');
            header('Accept-Ranges: bytes');

            echo $file;  // Imprime el contenido del documento
        } else {
            // Redirecciona si faltan parámetros
            redirect(base_url('inicio'));
        }
    }

    // Descarga un archivo XML desde la base de datos
    function descarga() {
        // Verifica parámetros requeridos
        if(isset($_POST['tabla']) && isset($_POST['id']) && isset($_POST['campo'])) {
            $id = $this->input->post("id");     // ID del registro
            $tabla = $this->input->post("tabla"); // Tabla de origen
            $campo = $this->input->post("campo"); // Campo que contiene el XML
            
            // Obtiene el archivo XML desde la base de datos
            $res = $this->Conexion->consultar("SELECT $campo from $tabla where id = $id", TRUE);            
            $file = $res->$campo;  // Contenido del XML
            
            // Configura headers para descarga
            header('Content-type: text/xml');
            echo $file;  // Imprime el contenido XML
        } else {
            // Redirecciona si faltan parámetros
            redirect(base_url('inicio'));
        }
    }
}