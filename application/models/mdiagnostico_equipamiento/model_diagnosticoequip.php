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

    /*--------- Get Formulario Habilitado para el diagnostico CONSOLIDADO----------*/
    public function get_consolidado_formulario_diagnostico_activo($equip_id){
        $sql = '
        SELECT 
            -- 1. CAMPOS DE LA FICHA MAESTRA DEL FORMULARIO
            form.form_equip_id,
            form.equip_id,
            form.dist_id,
            d.dist_distrital,
            d.abrev,
            d.da,
            d.ue,
            form.responsable,
            form.nombre_equipamiento,
            form.servicio_unidad,
            form.ubicacion_fisica,
            form.cantidad,
            form.costo_unitario,
            form.costo_total,
            form.par_id,
            form.tp_adecuacion,
            form.tp_adecuacion_infra, -- REPARADO: Nombre de columna real según tu DDL
            form.tp_adecuacion_instalacion,   -- REPARADO: Nombre de columna real según tu DDL
            form.tp_firma,
            form.observaciones,
            form.estado AS estado_formulario,
            form.nombre_inversion,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO REGISTRO (1: Establecimiento, 2: Proyecto Inversión)
            form.tp_registro,
            CASE 
                WHEN form.tp_registro = 1 THEN \'ESTABLECIMIENTO DE SALUD\'
                WHEN form.tp_registro = 2 THEN \'PROYECTO DE INVERSIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_registro_nombre,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO COMPRA (1: Nuevo, 2: Reposición)
            form.tp_compra,
            CASE 
                WHEN form.tp_compra = 1 THEN \'NUEVO\'
                WHEN form.tp_compra = 2 THEN \'REPOSICIÓN\'
                WHEN form.tp_compra = 3 THEN \'ADECUACIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_compra_nombre,

            -- 2. DATOS SANEADOS DEL ESTABLECIMIENTO DE SALUD
            COALESCE(est.act_id, 0) AS act_id,
            COALESCE(est.tipo, \'\') AS tipo_establecimiento,
            -- Si es Proyecto de Inversión, jala el texto libre; si es Establecimiento, jala la descripción de la CNS
            CASE 
                WHEN form.tp_registro = 2 THEN form.nombre_inversion
                ELSE COALESCE(est.act_descripcion, \'---\')
            END AS nombre_establecimiento,
            COALESCE(est.abrev, \'\') AS abrev_establecimiento,

            -- 3. CLASIFICADOR DE PARTIDAS MAPADO AL VECTOR DE LA BASE DE DATOS (par_id)
            par.par_codigo,
            par.par_nombre,

            -- 4. CRONOGRAMA QUINQUENAL PLANO (Desde tu Vista Optimizada)
            COALESCE(v_temp.g_2026, 0::numeric) AS g_2026,
            COALESCE(v_temp.g_2027, 0::numeric) AS g_2027,
            COALESCE(v_temp.g_2028, 0::numeric) AS g_2028,
            COALESCE(v_temp.g_2029, 0::numeric) AS g_2029,
            COALESCE(v_temp.g_2030, 0::numeric) AS g_2030,
            COALESCE(v_temp.total_quinquenal, 0::numeric) AS total_quinquenal

        FROM public.formulario_diagnostico_equipamiento form

        -- NEXO A: Enlace estricto con el Clasificador de Partidas
        INNER JOIN public.partidas par 
            ON par.par_id = form.par_id

        INNER JOIN public._distritales d 
            ON d.dist_id = form.dist_id

        -- NEXO B: Enlace con Establecimientos controlando la gestión y el tipo de registro
        LEFT JOIN public.vlista_establecimientos_salud est 
            ON form.tp_registro = 1 
           AND form.act_id > 0 
           AND est.act_id = form.act_id 
           AND est.aper_gestion = ' . intval($this->gestion) . '

        -- NEXO C: Acople elástico de la temporalidad distribuida (2026 - 2030)
        LEFT JOIN public.v_temporalidad_diagnostico_equipamiento_quinquenal v_temp 
            ON v_temp.form_equip_id = form.form_equip_id

        -- FILTROS DE CONTROL DE ENTORNO
        WHERE form.estado != 3 
          AND form.equip_id = ' . intval($equip_id) . ' 
        ORDER BY form.dist_id,form.tp_firma ASC, form.form_equip_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


     /*--------- Get Formulario Habilitado para el diagnostico por DISTRITAL----------*/
    public function get_distrital_formulario_diagnostico_activo($equip_id, $dist_id){
        $sql = '
        SELECT 
            -- 1. CAMPOS DE LA FICHA MAESTRA DEL FORMULARIO
            form.form_equip_id,
            form.equip_id,
            form.dist_id,
            d.dist_distrital,
            d.abrev,
            d.da,
            d.ue,
            form.responsable,
            form.nombre_equipamiento,
            form.servicio_unidad,
            form.ubicacion_fisica,
            form.cantidad,
            form.costo_unitario,
            form.costo_total,
            form.par_id,
            form.tp_adecuacion,
            form.tp_adecuacion_infra, -- REPARADO: Nombre de columna real según tu DDL
            form.tp_adecuacion_instalacion,   -- REPARADO: Nombre de columna real según tu DDL
            form.tp_firma,
            form.observaciones,
            form.estado AS estado_formulario,
            form.nombre_inversion,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO REGISTRO (1: Establecimiento, 2: Proyecto Inversión)
            form.tp_registro,
            CASE 
                WHEN form.tp_registro = 1 THEN \'ESTABLECIMIENTO DE SALUD\'
                WHEN form.tp_registro = 2 THEN \'PROYECTO DE INVERSIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_registro_nombre,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO COMPRA (1: Nuevo, 2: Reposición)
            form.tp_compra,
            CASE 
                WHEN form.tp_compra = 1 THEN \'NUEVO\'
                WHEN form.tp_compra = 2 THEN \'REPOSICIÓN\'
                WHEN form.tp_compra = 3 THEN \'ADECUACIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_compra_nombre,

            -- 2. DATOS SANEADOS DEL ESTABLECIMIENTO DE SALUD
            COALESCE(est.act_id, 0) AS act_id,
            COALESCE(est.tipo, \'\') AS tipo_establecimiento,
            -- Si es Proyecto de Inversión, jala el texto libre; si es Establecimiento, jala la descripción de la CNS
            CASE 
                WHEN form.tp_registro = 2 THEN form.nombre_inversion
                ELSE COALESCE(est.act_descripcion, \'---\')
            END AS nombre_establecimiento,
            COALESCE(est.abrev, \'\') AS abrev_establecimiento,

            -- 3. CLASIFICADOR DE PARTIDAS MAPADO AL VECTOR DE LA BASE DE DATOS (par_id)
            par.par_codigo,
            par.par_nombre,

            -- 4. CRONOGRAMA QUINQUENAL PLANO (Desde tu Vista Optimizada)
            COALESCE(v_temp.g_2026, 0::numeric) AS g_2026,
            COALESCE(v_temp.g_2027, 0::numeric) AS g_2027,
            COALESCE(v_temp.g_2028, 0::numeric) AS g_2028,
            COALESCE(v_temp.g_2029, 0::numeric) AS g_2029,
            COALESCE(v_temp.g_2030, 0::numeric) AS g_2030,
            COALESCE(v_temp.total_quinquenal, 0::numeric) AS total_quinquenal

        FROM public.formulario_diagnostico_equipamiento form

        -- NEXO A: Enlace estricto con el Clasificador de Partidas
        INNER JOIN public.partidas par 
            ON par.par_id = form.par_id

        INNER JOIN public._distritales d 
            ON d.dist_id = form.dist_id

        -- NEXO B: Enlace con Establecimientos controlando la gestión y el tipo de registro
        LEFT JOIN public.vlista_establecimientos_salud est 
            ON form.tp_registro = 1 
           AND form.act_id > 0 
           AND est.act_id = form.act_id 
           AND est.aper_gestion = ' . intval($this->gestion) . '

        -- NEXO C: Acople elástico de la temporalidad distribuida (2026 - 2030)
        LEFT JOIN public.v_temporalidad_diagnostico_equipamiento_quinquenal v_temp 
            ON v_temp.form_equip_id = form.form_equip_id

        -- FILTROS DE CONTROL DE ENTORNO
        WHERE form.estado != 3 
          AND form.dist_id = ' . intval($dist_id) . ' 
          AND form.equip_id = ' . intval($equip_id) . ' 
        ORDER BY form.tp_firma ASC, form.form_equip_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- Get Formulario Habilitado para el diagnostico por Distrital----------*/
    public function get_formulario_equipamiento_by_id($form_equip_id){
        $sql = '
        SELECT 
            -- 1. CAMPOS DE LA FICHA MAESTRA DEL FORMULARIO
            form.form_equip_id,
            form.equip_id,
            form.dist_id,
            d.dist_distrital,
            d.abrev,
            d.da,
            d.ue,
            form.responsable,
            form.nombre_equipamiento,
            form.servicio_unidad,
            form.ubicacion_fisica,
            form.cantidad,
            form.costo_unitario,
            form.costo_total,
            form.par_id,
            form.tp_adecuacion,
            form.tp_adecuacion_infra, -- REPARADO: Nombre de columna real según tu DDL
            form.tp_adecuacion_instalacion,   -- REPARADO: Nombre de columna real según tu DDL
            form.tp_firma,
            form.observaciones,
            form.estado AS estado_formulario,
            form.nombre_inversion,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO REGISTRO (1: Establecimiento, 2: Proyecto Inversión)
            form.tp_registro,
            CASE 
                WHEN form.tp_registro = 1 THEN \'ESTABLECIMIENTO DE SALUD\'
                WHEN form.tp_registro = 2 THEN \'PROYECTO DE INVERSIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_registro_nombre,

            -- 🔄 DECODIFICACIÓN EN CALIENTE: TIPO COMPRA (1: Nuevo, 2: Reposición)
            form.tp_compra,
            CASE 
                WHEN form.tp_compra = 1 THEN \'NUEVO\'
                WHEN form.tp_compra = 2 THEN \'REPOSICIÓN\'
                ELSE \'NO DEFINIDO\'
            END AS tp_compra_nombre,

            -- 2. DATOS SANEADOS DEL ESTABLECIMIENTO DE SALUD
            COALESCE(est.act_id, 0) AS act_id,
            COALESCE(est.tipo, \'\') AS tipo_establecimiento,
            -- Si es Proyecto de Inversión, jala el texto libre; si es Establecimiento, jala la descripción de la CNS
            CASE 
                WHEN form.tp_registro = 2 THEN form.nombre_inversion
                ELSE COALESCE(est.act_descripcion, \'---\')
            END AS nombre_establecimiento,
            COALESCE(est.abrev, \'\') AS abrev_establecimiento,

            -- 3. CLASIFICADOR DE PARTIDAS MAPADO AL VECTOR DE LA BASE DE DATOS (par_id)
            par.par_codigo,
            par.par_nombre,

            -- 4. CRONOGRAMA QUINQUENAL PLANO (Desde tu Vista Optimizada)
            COALESCE(v_temp.g_2026, 0::numeric) AS g_2026,
            COALESCE(v_temp.g_2027, 0::numeric) AS g_2027,
            COALESCE(v_temp.g_2028, 0::numeric) AS g_2028,
            COALESCE(v_temp.g_2029, 0::numeric) AS g_2029,
            COALESCE(v_temp.g_2030, 0::numeric) AS g_2030,
            COALESCE(v_temp.total_quinquenal, 0::numeric) AS total_quinquenal

        FROM public.formulario_diagnostico_equipamiento form

        -- NEXO A: Enlace estricto con el Clasificador de Partidas
        INNER JOIN public.partidas par 
            ON par.par_id = form.par_id

        INNER JOIN public._distritales d 
            ON d.dist_id = form.dist_id

        -- NEXO B: Enlace con Establecimientos controlando la gestión y el tipo de registro
        LEFT JOIN public.vlista_establecimientos_salud est 
            ON form.tp_registro = 1 
           AND form.act_id > 0 
           AND est.act_id = form.act_id 
           AND est.aper_gestion = ' . intval($this->gestion) . '

        -- NEXO C: Acople elástico de la temporalidad distribuida (2026 - 2030)
        LEFT JOIN public.v_temporalidad_diagnostico_equipamiento_quinquenal v_temp 
            ON v_temp.form_equip_id = form.form_equip_id

        -- FILTROS DE CONTROL DE ENTORNO
        WHERE form.estado != 3 
          AND form.form_equip_id = ' . intval($form_equip_id) . '  
        ORDER BY form.tp_firma ASC, form.form_equip_id ASC;';

        $query = $this->db->query($sql);
        return $query->row_array();
    }

    public function get_temporalidad_equipamiento_by_id($form_equip_id) {
        // Selecciona la gestión y el valor programado de la tabla de temporalidad
        $sql = 'SELECT g_id, prog_equi 
                FROM public.temporalidad_diagnostico_equipamiento 
                WHERE form_equip_id = ' . intval($form_equip_id) . ' 
                ORDER BY g_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array(); // Retorna la matriz de años (2026 a 2030)
    }


    //// Get lista de adicionales por equipo
    public function get_list_adcionales_x_equipo($form_equip_id) {
        // Selecciona la gestión y el valor programado de la tabla de temporalidad
        $sql = 'SELECT 
                adi_equi_id,
                form_equip_id,
                tp_equi_adi,
                CASE 
                    WHEN tp_equi_adi = 1 THEN \'ACCESORIO\'
                    WHEN tp_equi_adi = 2 THEN \'SOFTWARE\'
                    ELSE \'NO DEFINIDO\'
                END as tipo_detalle_nombre,
                detalle_equi_adi
            FROM public.equipamiento_adicionales
            WHERE form_equip_id = '.$form_equip_id.'
            ORDER BY adi_equi_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array(); // Retorna la matriz de años (2026 a 2030)
    }


    //// Get lista de adicionales por equipo
    public function get_list_adcionales_consolidado($equip_id) {
        // Selecciona la gestión y el valor programado de la tabla de temporalidad
        $sql = 'SELECT form.equip_id,form.form_equip_id,form.dist_id,d.dist_distrital,act.tipo,act.act_descripcion,d.abrev,form.responsable,form.nombre_equipamiento,
                ea.tp_equi_adi,
                                CASE 
                                    WHEN ea.tp_equi_adi = 1 THEN \'ACCESORIO\'
                                    WHEN ea.tp_equi_adi = 2 THEN \'SOFTWARE\'
                                    ELSE \'NO DEFINIDO\'
                                END as tipo_detalle_nombre,
                                ea.detalle_equi_adi
                                
                FROM formulario_diagnostico_equipamiento form
                Inner Join _distritales d On d.dist_id=form.dist_id
                Inner Join vlista_establecimientos_salud act On act.act_id=form.act_id and act.aper_gestion = '.$this->gestion.'
                Inner Join equipamiento_adicionales ea On ea.form_equip_id=form.form_equip_id
                where form.equip_id='.$equip_id.' 
                ORDER BY form.dist_id,form.form_equip_id,ea.adi_equi_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array(); // Retorna la matriz de años (2026 a 2030)
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
