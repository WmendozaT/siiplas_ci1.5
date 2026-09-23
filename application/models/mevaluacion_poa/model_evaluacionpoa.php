<?php
///// model a2027
class Model_evaluacionpoa extends CI_Model{
    public function __construct(){
        $this->load->database();
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
        $this->rol = $this->session->userData('rol_id'); /// rol->1 administrador, rol->3 TUE, rol->4 POA
        $this->adm = $this->session->userData('adm'); /// adm->1 Nacional, adm->2 Regional
        $this->dist = $this->session->userData('dist'); /// dist-> id de la distrital
        $this->dist_tp = $this->session->userData('dist_tp'); /// dist_tp->1 Regional, dist_tp->0 Distritales
        $this->tmes = $this->session->userData('trimestre');
    }
    

    /*------- Lista formN4 para Evaluacion POA --------*/
    public function list_formN4_para_evaluacion_UnidadResponsable($com_id, $trimestre) {
        // 1. Validar el trimestre (si no es 1, 2 o 3, por defecto es 4)
        $t = in_array($trimestre, array(1, 2, 3, 4)) ? intval($trimestre) : 4;

        // 2. Construir dinámicamente los nombres de las columnas
        $prog_trimestre  = 'prog_trimestre' . $t;
        $saldo_trimestre = 'saldo_acumulado_trimestre' . $t;

        // 3. Crear el molde SQL usando el signo de interrogación '?' para bindings
        $sql = "SELECT *
                FROM vista_formN4_para_evaluacionPoa_x_UniResponsable
                WHERE com_id = ? 
                  AND (" . $prog_trimestre . " != 0 OR " . $saldo_trimestre . " != 0)
                  AND estado != 3 
                ORDER BY prod_id, prod_cod ASC";

        // 4. En CI 1.5 pasas los parámetros del WHERE como un arreglo en el segundo argumento
        $query = $this->db->query($sql, array(intval($com_id)));
        
        return $query->result_array();
    }

    /*-- GET SEGUIMIENTO (EJECUTADO) POA MENSUAL (Cumplidas, En proceso) 2027 --*/
    public function get_seguimiento_poa_mes($prod_id,$mes_id){
        $sql = 'SELECT *
                from prod_ejecutado_mensual
                where prod_id='.$prod_id.' and m_id='.$mes_id.' and g_id='.$this->gestion.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*-- GET SEGUIMIENTO POA MENSUAL (No cumplido) 2027 --*/
    public function get_seguimiento_poa_mes_noejec($prod_id,$mes_id){
        $sql = 'SELECT *
                from prod_no_ejecutado_mensual
                where prod_id='.$prod_id.' and m_id='.$mes_id.' and g_id='.$this->gestion.'
                order by ne_id desc  LIMIT 1';

        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
