<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// Controlador Clientes para gestionar datos de empresas y contactos
class Clientes extends CI_Controller {

    // Constructor: carga modelo de conexión a base de datos
    function __construct() {
        parent::__construct();
        $this->load->model('conexion_model', 'Conexion'); // Modelo de conexión a la base de datos
    }

    // Obtiene clientes desde la tabla 'empresas' (filtrados por ID o nombre) vía Ajax
    function ajax_getClientes() {
        $id = $this->input->post('id');          // ID del cliente (opcional)
        $nombre = $this->input->post('nombre');  // Nombre del cliente (opcional)

        // Consulta base para obtener clientes
        $query = "SELECT id, nombre, razon_social, rfc, calle, numero, numero_interior, colonia, 
                  credito_cliente, credito_cliente_plazo, horario_facturas, 
                  ultimo_dia_facturas, documentos_facturacion, codigo_impresion 
                  FROM empresas 
                  WHERE cliente = 1";

        // Filtro por ID si se proporciona
        if ($id) {
            $query .= " AND id = '$id'";
        } else {
            // Filtro por nombre si se proporciona
            if ($nombre) {
                $query .= " AND nombre LIKE '%$nombre%'";
            }
        }

        $res = $this->Conexion->consultar($query, $id); // Ejecuta consulta
        if ($res) {
            echo json_encode($res);                    // Devuelve resultados en JSON
        } else {
            echo "";                                   // Si no hay resultados, devuelve vacío
        }
    }

    // Obtiene contactos de empresas (por ID o por empresa) vía Ajax
    function ajax_getContactos() {
        $id = $this->input->post('id');                // ID del contacto (opcional)
        $empresa = $this->input->post('id_cliente');   // ID de la empresa (opcional)

        // Consulta base para obtener contactos activos
        $query = "SELECT * FROM empresas_contactos WHERE activo = 1";

        // Filtro por ID si se proporciona
        if ($id) {
            $query .= " AND id = '$id'";
        } else {
            // Filtro por empresa si se proporciona
            if ($empresa) {
                $query .= " AND empresa = '$empresa'";
            }
        }

        $res = $this->Conexion->consultar($query, $id); // Ejecuta consulta
        if ($res) {
            echo json_encode($res);                     // Devuelve resultados en JSON
        }
    }
}
