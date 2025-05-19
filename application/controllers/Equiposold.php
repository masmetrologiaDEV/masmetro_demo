<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipos extends CI_Controller {

    function __construct() {
        parent::__construct();
    }

    public function ti() {
        $this->load->view('header');
        $this->load->view('equipos/ti/catalogo');
    }


function historial($idequipo){
        $this->load->model('tickets_model');
        $datosequipo=$this->tickets_model->getEquipos($idequipo);
        $datos['equipo'] = $this->tickets_model->historial($idequipo);
        $this->load->view('header');
        $this->load->view('equipos/ti/historial',$datos);

    }


    function ajax_setEquiposTI(){
        $equipo = json_decode($this->input->post('equipo'));
        $file = $this->input->post('iptFoto');



        if($equipo->foto != "default.png")
        {
            unlink('data/equipos/ti/fotos/' . $equipo->foto);
        }
        
        if($file == "undefined")
        {
            $equipo->foto = 'default.png';
        } 
        else 
        {
            $config['upload_path'] = 'data/equipos/ti/fotos/';
            $config['allowed_types'] = 'gif|jpg|png';
            $config['encrypt_name'] = TRUE;
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('iptFoto'))
            {
                $equipo->foto = $this->upload->data('file_name');
            }
        }

        $funciones = array('fecha_alta' => 'CURRENT_TIMESTAMP()', 'fecha_asignacion' => 'CURRENT_TIMESTAMP()');
        if($equipo->id == 0)
        {
            $res = $this->Conexion->insertar('equipos_it', $equipo, $funciones);
            $equipo->id = $res;
        }
        else
        {
	    $query='INSERT INTO `equiposHis` (`idEqH`, `idequipo`, `idus`, `fecha`) VALUES (NULL, '.$equipo->id.', '.$equipo->asignado.', CURRENT_TIMESTAMP);';
            $this->Conexion->comando($query);
            $this->Conexion->modificar('equipos_it', $equipo, null, array('id' => $equipo->id));
            
            
        }
    }

    
    function ajax_getEquiposTI(){
	
	$texto = $this->input->post('texto');
        $tipo=$this->input->post('tipo');
        $inactivo = $this->input->post('inactivo');

$query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado";


       
         if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " where E.id = $id";

        }

        else if ($inactivo==1) {
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 0";

            if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " and E.id = $id";

        }

        }else if(!empty($texto)){
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.id = '".$texto."'";

               if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " and E.id = $id";

        }



        }else if(!empty($tipo)){
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.tipo like '".$tipo."'";

               if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " and E.id = $id";

        }




        }






        $res = $this->Conexion->consultar($query, isset($_POST['id']));
        echo json_encode($res);







//        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado";


/*        if ($inactivo==1) {
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.activo = 0";
        }if(!empty($texto)){
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.id = '".$texto."'";
        }if(!empty($tipo)){
            $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where E.tipo like '".$tipo."'";
        }
	if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }
        

        $query = "SELECT E.*, ifnull(concat(U.nombre, ' ', U.paterno, ' ', U.materno), 'N/A') as Asignado from equipos_it E left join usuarios U on U.id = E.asignado where 1 = 1";

        if(isset($_POST['id']))
        {
            $id = $this->input->post('id');
            $query .= " and E.id = $id";
        }



        $res = $this->Conexion->consultar($query,  isset($_POST['id']));
        echo json_encode($res);*/
    }



}
