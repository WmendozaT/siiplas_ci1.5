<?php
class Cevaluacion_form4 extends CI_Controller {
    
    public $rol = array('1' => '3','2' => '4');
    
    public function __construct (){
        parent::__construct();
        
        // 1. Corregido el operador AND lógico (&&)
        if($this->session->userdata('fun_id') != null && $this->session->userdata('fun_estado') != 3){

            $this->load->model('programacion/model_proyecto');
            $this->load->model('programacion/model_producto');
            $this->load->model('programacion/model_componente');
            
            $this->com_id = $this->session->userdata('com_id');
            $this->mes_sistema = $this->session->userdata('mes'); 
            $this->verif_mes = $this->session->userdata('mes_actual');
            
            $this->load->library('seguimientopoa');
            
            // 2. Cargamos la librería TODO EN MINÚSCULAS para que coincida con el objeto
            $this->load->library('programacionpoa');
            // Si CI no creó la propiedad, la asignamos nosotros a mano
            if (!isset($this->programacionpoa)) {
                $CI =& get_instance();
                $this->programacionpoa = $CI->programacionpoa;
            } 
            
        } else {
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }

    /*----- formulario de Seguimiento y Evaluacion 2027 ------*/
    public function formulario_seguimiento_poa($com_id){
        // 3. Ahora el objeto $this->programacionpoa existirá correctamente y sin conflictos
        $data['stylo'] = $this->programacionpoa->estilo_tabla_form4(); 
        
        $data['formulario'] = 'Hola mundo';
        $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
    }


        /*------ NOMBRE MES -------*/
    function mes_nombre(){
        $mes[1] = 'ENE.';
        $mes[2] = 'FEB.';
        $mes[3] = 'MAR.';
        $mes[4] = 'ABR.';
        $mes[5] = 'MAY.';
        $mes[6] = 'JUN.';
        $mes[7] = 'JUL.';
        $mes[8] = 'AGOS.';
        $mes[9] = 'SEPT.';
        $mes[10] = 'OCT.';
        $mes[11] = 'NOV.';
        $mes[12] = 'DIC.';
        return $mes;
    }
}