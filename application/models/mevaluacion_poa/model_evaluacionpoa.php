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
    public function list_formN4_para_evaluacion_UnidadResponsable_trimestre($com_id, $trimestre) {
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

    /*----------- GET FORM 4 + PROG + EJEC ------*/
    public function list_formN4_para_evaluacion_UnidadResponsable_anual($com_id){
        $sql = 'SELECT *
                from vista_formN4_para_evaluacionPoa_x_UniResponsable p
                left JOIN vista_temporalidad_form4_ejecutado_uresp AS ejec ON ejec.prod_id = p.prod_id
                where p.com_id='.$com_id.'
                ORDER BY p.prod_id, p.prod_cod ASC';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----------- GET FORM 4 + PROG + EJEC ------*/
    public function get_form4_seguimiento_poa($prod_id){
        $sql = 'SELECT *
                from vista_formN4_para_evaluacionPoa_x_UniResponsable p
                left JOIN vista_temporalidad_form4_ejecutado_uresp AS ejec ON ejec.prod_id = p.prod_id
                where p.prod_id='.$prod_id.'';

        $query = $this->db->query($sql);
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

    /*----- TRIMESTRE VIGENTE 2027-------*/
    public function trimestre(){
        $sql = 'SELECT *
                from trimestre_mes
                where trm_id='.$this->tmes.' and estado!=\'0\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----------- GET TRIMESTRE VIGENTE ------*/
    public function get_trimestre($trm_id){
        $sql = 'SELECT *
                from trimestre_mes
                where trm_id='.$trm_id.' and estado!=\'0\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

  //// Para los Cuadros de Evaluacion POA
    // 1. Obtener la sumatoria agrupada de metas PROGRAMADAS hasta el trimestre seleccionado
    public function suma_programados_acumulados($com_id, $mes_inicio, $mes_final) {
        $sql = "SELECT COUNT(DISTINCT prod.prod_id) AS total_operaciones, 
                       SUM(pprog.pg_fis) AS suma_programado
                FROM _productos AS prod
                INNER JOIN prod_programado_mensual AS pprog ON pprog.prod_id = prod.prod_id
                WHERE prod.com_id = ? 
                  AND prod.estado != '3' 
                  AND pprog.g_id = ? 
                  AND pprog.m_id >= ?
                  AND pprog.m_id <= ? 
                  AND pprog.pg_fis != '0'";
                  
        $query = $this->db->query($sql, array($com_id, $this->gestion, $mes_inicio, $mes_final));
        return $query->row_array();
    }

    // 2. Obtener la sumatoria agrupada de metas EJECUTADAS reales hasta el trimestre seleccionado
    public function suma_ejecutados_acumulados($com_id, $mes_inicio, $mes_final) {
        $sql = "SELECT SUM(pejec.pejec_fis) AS suma_evaluado
                FROM _productos AS prod
                INNER JOIN prod_ejecutado_mensual AS pejec ON pejec.prod_id = prod.prod_id
                WHERE prod.com_id = ? 
                  AND pejec.g_id = ? 
                  AND pejec.m_id >= ?
                  AND pejec.m_id <= ? 
                  AND pejec.pejec_fis != '0'";
                  
        $query = $this->db->query($sql, array($com_id, $this->gestion, $mes_inicio, $mes_final));
        return $query->row_array();
    }

    // 3. ✨ NUEVA CONSULTA CRÍTICA: Trae los 3 estados del Pastel agrupados de una sola vez
    public function obtener_estados_pastel_acumulado($com_id, $trimestre_max) {
        $sql = "SELECT 
                    SUM(CASE WHEN pt.tp_eval = 1 THEN 1 ELSE 0 END) AS cumplidos,
                    SUM(CASE WHEN pt.tp_eval = 2 THEN 1 ELSE 0 END) AS en_proceso,
                    SUM(CASE WHEN pt.tp_eval = 3 THEN 1 ELSE 0 END) AS no_cumplidos
                FROM _productos AS p
                INNER JOIN _productos_trimestral AS pt ON p.prod_id = pt.prod_id
                WHERE p.com_id = ? 
                  AND pt.testado != '3' 
                  AND pt.trm_id <= ?"; // Trae la acumulación histórica de trimestres
                  
        $query = $this->db->query($sql, array($com_id, $trimestre_max));
        return $query->row_array();
    }

}
