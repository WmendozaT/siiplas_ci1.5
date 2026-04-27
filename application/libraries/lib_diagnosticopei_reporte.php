<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

class lib_diagnosticopei_reporte {

    private $CI;

    public function __construct() {
        // Obtenemos la instancia de CI para usar modelos, sesiones, etc.
        $this->CI =& get_instance();
        
        // Cargamos recursos usando la instancia $this->CI
        $this->CI->load->model('diagnosticoPei/model_diagnosticopei');

        // Asignamos variables desde la sesión
        $this->dist_id  = $this->CI->session->userdata('dist');
        $this->dep_id   = $this->CI->session->userdata('dep_id');
        $this->gestion  = $this->CI->session->userdata('gestion');
        $this->fun_id   = $this->CI->session->userdata('fun_id');
        $this->conf_pei = $this->CI->session->userdata('conf_pei');
        $this->tp_adm   = $this->CI->session->userdata("tp_adm");
    }

    public function reporte_diagnostico_pei() {
        return 'Hola mundo desde la librería corregida';
    }
}