<?php
class model_diagnosticoequip extends CI_Model {

    public function __construct(){
        $this->load->database();
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
       
    }
    

    /*--------- Get diagnostico vigente ----------*/
    public function get_diagnostico_equipamiento_activo(){
        $sql = '
                SELECT *
                from diagnostico_equipamiento
                where estado=1';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get Formulario diganostico ----------*/
    public function get_formulario_diagnostico($form_id){
        $sql = '
                SELECT *
                from formulario_diagnostico_pei
                where form_id='.$form_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- Get Formulario diganostico x distrital ----------*/
    public function get_dist_formulario_diagnostico($dist_id){
        $sql = '
                SELECT *
                from formulario_diagnostico_pei
                where dist_id='.$dist_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get Formulario Habilitado para el diagnostico por Distrital----------*/
    public function get_distrital_formulario_diagnostico_activo($equip_id,$dist_id){
        $sql = '
        SELECT 
            -- Datos del Diagnóstico Quinquenal Maestro
            equip.equip_id,
            equip.g_id_inicio,
            equip.g_id_fin,
            equip.estado AS estado_diagnostico,
            
            -- Datos Geográficos / Distrital de la CNS
            d.dep_id,
            d.dist_id,
            d.dist_distrital AS nombre_distrital,
            d.abrev AS abrev_distrital,
            d.da,
            d.ue,
            
            -- Datos del Establecimiento de Salud Vinculado
            est.act_id,
            est.tipo AS tipo_establecimiento,
            est.act_descripcion AS nombre_establecimiento,
            
            -- Ficha del Formulario de Requerimiento de Equipamiento
            form.form_equip_id,
            form.responsable,
            form.nombre_equipamiento,
            form.servicio_unidad,
            form.ubicacion_fisica,
            form.tp_compra,
            form.cantidad,
            form.costo_unitario,
            form.costo_total,
            form.par_id,
            par.par_codigo,
            par.par_nombre,
            form.tp_adecuacion,
            form.tp_firma, -- Nueva variable de gobernanza integrada
            form.observaciones,
            form.estado AS estado_formulario,
            
            -- Cronograma Temporal Quinquenal (Pivoteado de 2026 a 2030)
            COALESCE(v_temp.g_2026, 0::numeric) AS g_2026,
            COALESCE(v_temp.g_2027, 0::numeric) AS g_2027,
            COALESCE(v_temp.g_2028, 0::numeric) AS g_2028,
            COALESCE(v_temp.g_2029, 0::numeric) AS g_2029,
            COALESCE(v_temp.g_2030, 0::numeric) AS g_2030,
            COALESCE(v_temp.total_quinquenal, 0::numeric) AS total_quinquenal

        FROM public.diagnostico_equipamiento equip
        -- NEXO 1: Conecta la maestría con las fichas de equipamiento cargadas
        INNER JOIN public.formulario_diagnostico_equipamiento form 
            ON form.equip_id = equip.equip_id

        -- NEXO 2: Sincroniza la distrital correspondiente
        INNER JOIN public._distritales d 
            ON d.dist_id = form.dist_id

        INNER JOIN public.partidas par 
            ON par.par_id = form.par_id

        -- NEXO 3: CORRECCIÓN - Conecta el establecimiento de salud al formulario del requerimiento
        INNER JOIN public.vlista_establecimientos_salud est 
            ON est.act_id = form.act_id

        -- NEXO 4: CORRECCIÓN ALIAS - Acopla de forma elástica el cronograma quinquenal con ceros estables
        LEFT JOIN public.v_temporalidad_diagnostico_equipamiento_quinquenal v_temp 
            ON v_temp.form_equip_id = form.form_equip_id

        -- CONDICIONANTES Y ORDENAMIENTO DE RENDIMIENTO
        WHERE equip.estado = 1  -- Diagnósticos habilitados / activos
          AND form.estado != 3  -- Excluye registros eliminados o dados de baja en el SIIPLAS
          AND form.dist_id= '.$dist_id.'
          AND equip.equip_id='.$equip_id.'
        ORDER BY 
            d.dep_id ASC, 
            d.dist_id ASC, 
            form.tp_firma ASC, 
            form.form_equip_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- Lista de Unidades Ejecutoras ----------*/
    public function lista_UnidadEjecutora(){
        $sql = 'SELECT *
                FROM _distritales
                WHERE dist_estado = 1 
                  AND dist_id NOT IN (0, 22) -- Más legible y eficiente que múltiples !=
                ORDER BY dist_id ASC, dep_id ASC';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get Distrital ----------*/
    public function get_distrital($dist_id){
        $sql = 'SELECT *
                from _distritales
                where dist_id='.$dist_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- Get Establecimientos x distrital ----------*/
    public function get_establecimientos_distrital($dist_id,$gestion){
        $sql = 'SELECT *
                from vlista_establecimientos_salud
                where dist_id='.$dist_id.' and aper_gestion='.$gestion.'
                order by tn_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
