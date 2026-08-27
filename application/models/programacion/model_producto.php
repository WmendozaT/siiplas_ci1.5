<?php
class model_producto extends CI_Model {
    public function __construct(){
        $this->load->database();
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
        $this->rol = $this->session->userData('rol_id'); /// rol->1 administrador, rol->3 TUE, rol->4 POA
        $this->adm = $this->session->userData('adm'); /// adm->1 Nacional, adm->2 Regional
        $this->dist = $this->session->userData('dist'); /// dist-> id de la distrital
        $this->dist_tp = $this->session->userData('dist_tp'); /// dist_tp->1 Regional, dist_tp->0 Distritales
    }

/*    function temporalidad_form4(){
        $sql = 'select *
                from vista_productos_temporalizacion_programado_dictamen
                where g_id='.$this->gestion.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/

    /*----- LISTA DE FORMULARIO 4 (2022) para el SEguimiento POA (A optimizar)-----*/
    // function list_operaciones_subactividad($com_id){
    //     $sql = '
    //     select 
    //         p.prod_id,
    //         p.com_id,
    //         p.prod_cod,
    //         p.prod_producto,
    //         p.indi_id,
    //         mt.mt_id,
    //         p.prod_indicador,
    //         p.prod_linea_base,
    //         p.prod_meta,
    //         p.prod_fuente_verificacion,
    //         p.prod_unidades,
    //         p.prod_resultado,
    //         p.acc_id,
    //         p.prod_priori,
    //         p.prod_priori,
    //         p.uni_resp,
    //         ore.or_id,
    //         ore.or_codigo,
    //         ore.or_objetivo,
    //         ore.or_indicador,
    //         ore.or_producto,
    //         ore.or_resultado,
    //         ore.or_verificacion,
    //         mt.mt_tipo,
    //         mt.mt_descripcion
        
    //       from _productos as p
    //       Inner Join objetivos_regionales as ore On ore.or_id=p.or_id
    //       Inner Join indicador as tp On p.indi_id=tp.indi_id
    //       Inner Join meta_relativo as mt On mt.mt_id=p.mt_id
    //       where p.estado!=\'3\' and p.com_id='.$com_id.'
    //       ORDER BY p.prod_cod asc'; 

    //     $query = $this->db->query($sql);
    //     return $query->result_array();
    // }


    /*--- VERIF ALINEACION DEL REQUERIMIENTO A LA ACTIVIDAD 2026 ---*/
    public function verif_form4_vigente_para_alineacion($com_id,$prod_cod){ 
        $sql = 'SELECT *
                from _productos p
                where p.com_id='.$com_id.' and p.prod_cod='.$prod_cod.' and p.estado!=3'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- LISTA DE FORM 4 ELIMINADOS PARA LIMPIAR EN LA BS -----*/
    function list_form4_eliminados_gestion($proy_id){
        $sql = 'select c.*,prod.*,apg.aper_gestion
                from _proyectofaseetapacomponente pfe
                Inner Join aperturaprogramatica as apg On apg.aper_id=pfe.aper_id
                Inner Join _componentes as c On c.pfec_id=pfe.pfec_id
                Inner Join _productos as prod On c.com_id=prod.com_id
                where pfe.proy_id='.$proy_id.' and apg.aper_gestion='.$this->gestion.''; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- LISTA DE UNIDADES RESPONSABLES REGIONAL PARA alinear a programas BOLSA -----*/
    function list_uresponsables_regional_alineacion_prog_bolsas($dist_id){
            
        $sql = "
            SELECT 
            poa.aper_id, 
            poa.proy_id,
            poa.tipo,  
            poa.aper_programa, 
            poa.proy_nombre, 
            poa.abrev, 
            c.com_id, 
            -- Lógica para intercambiar el Tipo si el programa es 742 o 751
            CASE 
                WHEN poa.aper_programa IN ('742', '751') THEN poa.tipo 
                ELSE tpsa.tipo_subactividad 
            END AS tipo_subactividad,
            -- Lógica para intercambiar el Componente si el programa es 742 o 751
            CASE 
                WHEN poa.aper_programa IN ('742', '751') THEN poa.proy_nombre 
                ELSE c.com_componente 
            END AS com_componente
        FROM fnlista_poa_nacional(".$this->gestion.") poa
        INNER JOIN _componentes c ON c.pfec_id = poa.pfec_id
        INNER JOIN servicios_actividad sa ON sa.serv_id = c.serv_id
        INNER JOIN tipo_subactividad tpsa ON tpsa.tp_sact = c.tp_sact
        WHERE poa.dist_id = ".$dist_id."
          AND poa.tp_id = 4 
          AND c.estado != 3 
          AND poa.aper_programa NOT IN ('771', '98', '99', '720')
        ORDER BY poa.aper_programa, poa.aper_proyecto, poa.aper_actividad ASC;"; 
                

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- GET UNIDAD RESP POR ACTIVIDAD (PROG 770) -----*/
/*    function get_uni_resp_prog770($com_id,$uni_resp){
        $sql = '
        select *
                from _productos
                where com_id='.$com_id.' and uni_resp='.$uni_resp.' and estado!=\'3\''; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }*/


    /*----- GET UNIDAD RESPONSABLE POR ACTIVIDAD (PROG BOLSA) -----*/
    function verif_get_uni_resp_programaBolsa($com_id){
        $sql = '
            select prod.*,apg.*,prog.*
            from _productos prod
            Inner Join _componentes as c On prod.com_id=c.com_id
            Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
            Inner Join aperturaprogramatica as apg On apg.aper_id=pfe.aper_id
                
            Inner Join vista_productos_temporalizacion_programado_dictamen as prog On prog.prod_id=prod.prod_id
            where prod.uni_resp='.$com_id.' and prog.g_id='.$this->gestion.'
            order by apg.aper_programa, prod.prod_cod asc'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    
    /*----- GET UNIDAD RESPONSABLE POR programa (PROG BOLSA) 2026 -----*/
    function verif_programaBolsa_prog($aper_id,$com_id){
        $sql = '
            SELECT *
            from _productos prod
            Inner Join _insumoproducto as ins On ins.prod_id=prod.prod_id
            Inner Join _componentes as c On prod.com_id=c.com_id
            Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
            where pfe.aper_id='.$aper_id.' and prod.uni_resp='.$com_id.''; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- GET LISTA DE ACTIVIDADES ALINEADO A LA UNIDAD RESPONSABLE DE LOS PROGRAMAS BOLSA 2023 (REVISAR)-----*/
    function get_lista_form4_uniresp_prog_bolsas($com_id){
        $sql = 'select apg.aper_id,p.proy_id,apg.aper_gestion,apg.aper_programa,apg.aper_proyecto,apg.aper_actividad,apg.aper_descripcion,prod.com_id,prod.prod_id,prod.prod_cod,prod.prod_producto, prod.prod_indicador, prod.prod_meta,prod.prod_fuente_verificacion,prod.uni_resp
                from _productos prod
                Inner Join _componentes as c On prod.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join aperturaprogramatica as apg On apg.aper_id=pfe.aper_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id

                where prod.uni_resp='.$com_id.' and apg.aper_gestion='.$this->gestion.' and c.estado!=\'3\' and prod.estado!=\'3\' and pfe.pfec_estado!=\'3\'
                order by apg.aper_programa, apg.aper_proyecto, apg.aper_actividad asc'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- GET LISTA DE ACTIVIDADES PROGRAMA NORMAL + prog Bolsa x UNI ORGANIZACIONAL (consolidado para reporte)-----*/
    function get_lista_form4_uOrganizacional_consolidado($proy_id){
        $sql = "
              SELECT *
              from vista_consolidado_actividades_mas_bolsa_ordenado_form4
              where proy_id=$proy_id
              ORDER BY serv_cod, aper_programa,prod_cod ASC;"; 
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- GET LISTA DE ACTIVIDADES PROGRAMA NORMAL + prog Bolsa x UNI RESP (consolidado para resporte)-----*/
    function get_lista_form4_uresp_consolidado($com_id){
        $sql = "
        SELECT 
        -- Lógica de Apertura Programática
        COALESCE(apg_bolsa.aper_programa, apg_normal.aper_programa) AS aper_programa,
        
        -- Datos del Producto
        c.com_componente,p.prod_id, p.com_id, p.prod_priori, p.prod_producto, p.prod_ppto, 
        p.indi_id, p.prod_indicador, p.prod_linea_base, p.prod_meta, p.prod_fuente_verificacion, 
        
        -- Lógica de Unidades Responsables (Bolsas)
        CASE 
            WHEN p.uni_resp = ".$com_id." THEN CONCAT(uresp.proy_nombre, '.', uresp.abrev, ' - ', uresp.tipo_subactividad, ' ', uresp.com_componente)
            ELSE p.prod_unidades 
        END AS prod_unidades,

        p.estado, p.prod_mod, p.prod_resultado, 
        p.prod_cod, p.uni_resp, p.prod_observacion, p.mt_id,p.or_id, 
        
        -- Datos de Indicador y Objetivos
        i.indi_descripcion, i.indi_abreviacion,
        ore.or_codigo, -- Eliminamos ore.or_id porque ya está p.or_id arriba
        og.og_id, og.og_codigo, 
        
        -- Temporalidad (Meses)
        prog.mes1 AS m1, prog.mes2 AS m2, prog.mes3 AS m3, prog.mes4 AS m4, 
        prog.mes5 AS m5, prog.mes6 AS m6, prog.mes7 AS m7, prog.mes8 AS m8, 
        prog.mes9 AS m9, prog.mes10 AS m10, prog.mes11 AS m11, prog.mes12 AS m12, 
        prog.g_id,prog.total_anual
        FROM _productos p
        left JOIN indicador i ON i.indi_id = p.indi_id
        left JOIN objetivos_regionales ore ON ore.or_id = p.or_id
        left JOIN (
            SELECT DISTINCT pog_id, og_id FROM objetivo_programado_mensual
        ) opm ON ore.pog_id = opm.pog_id
        LEFT JOIN objetivo_gestion og ON og.og_id = opm.og_id
        LEFT JOIN vista_temporalidad_form4_programado_uresp prog ON prog.prod_id = p.prod_id

        LEFT JOIN aperturaprogramatica apg_normal ON apg_normal.aper_id = og.aper_id
        LEFT JOIN _componentes c ON p.com_id = c.com_id
        LEFT JOIN _proyectofaseetapacomponente pfe ON pfe.pfec_id = c.pfec_id
        LEFT JOIN aperturaprogramatica apg_bolsa ON apg_bolsa.aper_id = pfe.aper_id
        LEFT JOIN vista_ver_uresp_proyecto uresp ON uresp.com_id = p.uni_resp
        WHERE p.estado != 3 
      AND (
          p.com_id = ".$com_id."  -- Caso Normal
          OR 
          (p.uni_resp = ".$com_id."  AND prog.g_id = ".$this->gestion.") -- Caso Bolsas
      )
    ORDER BY p.uni_resp,p.prod_id,p.prod_cod ASC;"; 
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }



    /*----- GET LISTA DE ACTIVIDADES PROGRAMA BIENES Y SERVICIOS Y FORTALECIMIENTO -----*/
    function get_lista_form4_uresp_consolidado_programa_bolsas($com_id){
        $sql = "
        SELECT 
            COALESCE(apg_bolsa.aper_programa, apg_normal.aper_programa) AS aper_programa,
            c.com_componente, p.prod_id, p.com_id, p.prod_priori, p.prod_producto, p.prod_ppto, 
            p.indi_id, p.prod_indicador, p.prod_linea_base, p.prod_meta, p.prod_fuente_verificacion, 
            
            -- CORRECCIÓN: Si existe relación con la vista de unidad responsable, mostramos el detalle concatenado
            CASE 
                WHEN p.uni_resp = 0 THEN ''
                WHEN uresp.com_id IS NOT NULL THEN 
                    CONCAT(uresp.proy_nombre, '.', uresp.abrev, ' - ', uresp.tipo_subactividad, ' ', uresp.com_componente)
                ELSE 
                    p.prod_unidades 
            END AS prod_unidades,
                    
            p.estado, p.prod_mod, p.prod_resultado, 
            p.prod_cod, p.uni_resp, p.prod_observacion, p.mt_id, p.or_id, 
            i.indi_descripcion, i.indi_abreviacion,
            ore.or_codigo, og.og_id, og.og_codigo, 
            
            prog.mes1 AS m1, prog.mes2 AS m2, prog.mes3 AS m3, prog.mes4 AS m4, 
            prog.mes5 AS m5, prog.mes6 AS m6, prog.mes7 AS m7, prog.mes8 AS m8, 
            prog.mes9 AS m9, prog.mes10 AS m10, prog.mes11 AS m11, prog.mes12 AS m12, 
            prog.g_id, prog.total_anual
        FROM _productos p
        LEFT JOIN indicador i ON i.indi_id = p.indi_id
        LEFT JOIN objetivos_regionales ore ON ore.or_id = p.or_id
        INNER JOIN (
            SELECT DISTINCT pog_id, og_id FROM objetivo_programado_mensual
        ) opm ON ore.pog_id = opm.pog_id
        LEFT JOIN objetivo_gestion og ON og.og_id = opm.og_id
        LEFT JOIN vista_temporalidad_form4_programado_uresp prog ON prog.prod_id = p.prod_id

        LEFT JOIN aperturaprogramatica apg_normal ON apg_normal.aper_id = og.aper_id
        LEFT JOIN _componentes c ON p.com_id = c.com_id
        LEFT JOIN _proyectofaseetapacomponente pfe ON pfe.pfec_id = c.pfec_id
        LEFT JOIN aperturaprogramatica apg_bolsa ON apg_bolsa.aper_id = pfe.aper_id

        -- Join clave: relacionamos la unidad responsable del producto con la vista de proyectos
        LEFT JOIN vista_ver_uresp_proyecto uresp ON uresp.com_id = p.uni_resp

        WHERE p.com_id = ".$com_id." 
          AND p.estado != 3
        ORDER BY p.prod_cod,p.prod_id ASC;"; 
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }






    /// Migracion de temporalidad form 4 
/*    function list_temporalidad_total_form4(){
        $sql = 'select *
                from prod_programado_mensual'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }*/



    /*----- RELACION INSUMO PRODUCTO (VIGENTE) 2027 -----*/
    function insumo_producto($prod_id){
        $sql = 'SELECT ip.ins_id
                from _insumoproducto ip
                Inner Join insumos as i On i.ins_id=ip.ins_id
                where ip.prod_id='.$prod_id.' and i.ins_estado!=3 and i.ins_gestion='.$this->gestion.' 
                group by ip.ins_id'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- ULTIMO PRODUCTO (2021-2022) ----------*/
/*    function ult_operacion($com_id){
        $sql = 'select p.*
                from _productos as p
                Inner Join vista_productos_temporalizacion_programado_dictamen as prog On prog.prod_id=p.prod_id
                where p."com_id"='.$com_id.' and p.estado!=\'3\' and prog.g_id='.$this->gestion.'
                ORDER BY p.prod_cod desc LIMIT 1'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/

    //// lista de productos sin Temporalidad
    function lista_productos($com_id){
       $sql = 'SELECT p.*, ore.*, tp.*, mt.*,
            COALESCE(
                CASE 
                    WHEN uresp.com_id IS NOT NULL THEN CONCAT(ore.or_codigo, \'/\',p.prod_cod, \' .- \',p.prod_producto, \'-> ( \',uresp.tipo_subactividad, \' \', uresp.com_componente, \' - \', uresp.proy_nombre, \' \', uresp.abrev, \' ) \')
                    ELSE \'0\'
                END, 
                \'0\'
            ) AS unidad_responsable
        FROM public._productos AS p
        left JOIN public.objetivos_regionales AS ore ON ore.or_id = p.or_id
        left JOIN public.indicador AS tp ON p.indi_id = tp.indi_id
        INNER JOIN public.meta_relativo AS mt ON mt.mt_id = p.mt_id
        LEFT JOIN public.vista_ver_uresp_proyecto AS uresp ON uresp.com_id = p.uni_resp
        WHERE p.com_id = ' . intval($com_id) . ' AND p.estado != 3
        ORDER BY p.prod_id, p.prod_cod ASC'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*========== META GESTION ACTUAL PRODUCTO ==========*/
/*    public function meta_prod_gest($id_prod){
        $sql = 'SELECT SUM(pg_fis) as meta_gest
            from prod_programado_mensual
            where prod_id='.$id_prod.' AND g_id='.$this->session->userdata("gestion").''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/
    /*====================================================*/

    /* ----- GET FORM 4 2027 -----*/
    function get_producto_id($id_prod){
        $sql = "
        SELECT 
            p.prod_id, p.com_id, p.prod_cod,p.prod_producto, p.indi_id,p.prod_indicador,p.prod_linea_base, p.prod_meta, 
            p.prod_fuente_verificacion, p.prod_unidades, p.prod_resultado, p.prod_priori, p.or_id,
            p.uni_resp,
            CONCAT(uresp.proy_nombre, '.', uresp.abrev, ' - ', uresp.tipo_subactividad, ' ', uresp.com_componente) unidad_responsable,
            c.com_componente,
            tp.indi_descripcion,
            p.mt_id,
            mr.mt_tipo, mr.mt_descripcion,
            COALESCE(prog.mes1, 0.00) AS m1, 
            COALESCE(prog.mes2, 0.00) AS m2, 
            COALESCE(prog.mes3, 0.00) AS m3, 
            COALESCE(prog.mes4, 0.00) AS m4, 
            COALESCE(prog.mes5, 0.00) AS m5, 
            COALESCE(prog.mes6, 0.00) AS m6, 
            COALESCE(prog.mes7, 0.00) AS m7, 
            COALESCE(prog.mes8, 0.00) AS m8, 
            COALESCE(prog.mes9, 0.00) AS m9, 
            COALESCE(prog.mes10, 0.00) AS m10, 
            COALESCE(prog.mes11, 0.00) AS m11, 
            COALESCE(prog.mes12, 0.00) AS m12,
            prog.g_id,
            COALESCE(prog.total_anual, 0.00) AS total_anual, 
            poa.*
        FROM _productos p
        INNER JOIN indicador tp ON p.indi_id = tp.indi_id
        INNER JOIN meta_relativo mr ON p.mt_id = mr.mt_id
        INNER JOIN _componentes c ON p.com_id = c.com_id
        LEFT JOIN vista_ver_uresp_proyecto uresp ON uresp.com_id = p.uni_resp
        LEFT JOIN vista_temporalidad_form4_programado_uresp prog ON p.prod_id = prog.prod_id
        INNER JOIN fn_lista_poa_nacional($this->gestion) poa ON c.pfec_id = poa.pfec_id
        WHERE p.prod_id = $id_prod 
          AND p.estado != 3;"; 
        $query = $this->db->query($sql);
        return $query->result_array();

    }
    /*=====================================================*/



    /*=== LISTA DE ACTIVIDADES (NORMAL) + TEMPORALIDAD ===*/
    function lista_form4_x_unidadresponsable($com_id){
        $sql = '
            SELECT  p.prod_id, 
                    p.com_id, 
                    p.prod_priori, 
                    p.prod_producto, 
                    p.prod_ppto, 
                    p.indi_id, 
                    p.prod_indicador, 
                    p.prod_linea_base, 
                    
                    -- 🌟 REPARADO: Si p.prod_meta es NULL en la tabla base, fuerza un 0.00
                    COALESCE(p.prod_meta, 0.00) AS prod_meta, 
                    
                    p.prod_fuente_verificacion,
                    p.prod_unidades,
                    p.estado, 
                    p.prod_mod, 
                    p.prod_resultado, 
                    p.prod_cod, 
                    p.uni_resp, 
                    p.prod_observacion, 
                    p.mt_id,
                    p.or_id, 
                    i.indi_descripcion, 
                    i.indi_abreviacion,
                    ore.or_codigo, 
                    og.og_id, 
                    og.og_codigo, 
                    
                    -- 🌟 REPARADO: Control de nulos perimetral para los 12 meses de la subvista
                    COALESCE(prog.mes1, 0.00) AS m1, 
                    COALESCE(prog.mes2, 0.00) AS m2, 
                    COALESCE(prog.mes3, 0.00) AS m3, 
                    COALESCE(prog.mes4, 0.00) AS m4, 
                    COALESCE(prog.mes5, 0.00) AS m5, 
                    COALESCE(prog.mes6, 0.00) AS m6, 
                    COALESCE(prog.mes7, 0.00) AS m7, 
                    COALESCE(prog.mes8, 0.00) AS m8, 
                    COALESCE(prog.mes9, 0.00) AS m9, 
                    COALESCE(prog.mes10, 0.00) AS m10, 
                    COALESCE(prog.mes11, 0.00) AS m11, 
                    COALESCE(prog.mes12, 0.00) AS m12, 
                    
                    prog.g_id,
                    
                    -- 🌟 REPARADO: Si el total acumulado anual viene vacío, lo fuerza a 0.00
                    COALESCE(prog.total_anual, 0.00) AS total_anual
                    
            FROM public._productos p
            INNER JOIN public.indicador i ON i.indi_id = p.indi_id
            LEFT JOIN public.objetivos_regionales ore ON ore.or_id = p.or_id
            LEFT JOIN (
                SELECT DISTINCT pog_id, og_id FROM public.objetivo_programado_mensual
            ) opm ON ore.pog_id = opm.pog_id
            LEFT JOIN public.objetivo_gestion og ON og.og_id = opm.og_id
            LEFT JOIN public.vista_temporalidad_form4_programado_uresp prog ON prog.prod_id = p.prod_id

            WHERE p.com_id = '.$com_id.' 
              AND p.estado != 3
              
            ORDER BY p.prod_cod ASC;'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*=== LISTA DE ACTIVIDADES (NORMAL) + TEMPORALIDAD 2027 ===*/
    function lista_form4_institucional_completo(){
        $sql = 'SELECT poa.*
                from get_formulario4_consolidado_nacional('.$this->gestion.') poa
                ORDER BY poa.dep_id,poa.dist_id,poa.aper_programa,poa.aper_proyecto,poa.aper_actividad,poa.proy_id,poa.com_id,poa.prod_id,poa.prod_cod asc'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /// lista POA Formulario N4 completo
    function lista_form4_x_regional_completo($dep_id,$tp_id){
        $sql = 'SELECT poa.*
                from get_formulario4_consolidado_nacional('.$this->gestion.') poa
                where poa.dep_id='.$dep_id.' and poa.tp_id='.$tp_id.'
                ORDER BY poa.dep_id,poa.dist_id,poa.aper_programa,poa.aper_proyecto,poa.aper_actividad,poa.proy_id,poa.com_id,poa.prod_id,poa.prod_cod asc'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function lista_form4_x_distrital_completo($dep_id,$tp_id){
        $sql = 'SELECT poa.*
                from get_formulario4_consolidado_nacional('.$this->gestion.') poa
                where dist_id='.$dist_id.' and tp_id='.$dist_id.'
                ORDER BY poa.dist_id,poa.aper_programa,poa.aper_proyecto,poa.aper_actividad,poa.proy_id,poa.com_id,poa.prod_id,poa.prod_cod asc'; 

        $query = $this->db->query($sql);
        return $query->result_array();
    }
    /*=========================================


    /*------- SUMA TOTAL EVALUADO -------*/
    function suma_total_evaluado($prod_id){
        $sql = 'select 
                SUM(pejec_fis) as suma_total
                from prod_ejecutado_mensual
                where prod_id='.$prod_id.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    public function prod_prog_mensual($id_pr,$gest){
        $this->db->from('prod_programado_mensual');
        $this->db->where('prod_id', $id_pr);
        $this->db->where('g_id', $gest);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function nro_prod_prog_mensual($id_pr,$gest){
        $this->db->from('prod_programado_mensual');
        $this->db->where('prod_id', $id_pr);
        $this->db->where('g_id', $gest);
        $query = $this->db->get();
        return $query->num_rows();
    } 
    /*==========================================================*/
    /*================= LISTA DE PRODUCTOS EJECUTADO GESTION  ==============*/
    public function prod_ejec_mensual($id_pr,$gest){
        $this->db->from('prod_ejecutado_mensual');
        $this->db->where('prod_id', $id_pr);
        $this->db->where('g_id', $gest);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function nro_prod_ejec_mensual($id_pr,$gest){
        $this->db->from('prod_ejecutado_mensual');
        $this->db->where('prod_id', $id_pr);
        $this->db->where('g_id', $gest);
        $query = $this->db->get();
        return $query->num_rows();
    }

    /*==========================================================*/

    /*============ LISTA DE PRODUCTOSGESTION ANUAL =====================*/
    function list_prodgest_anual($id_prod){
        $sql = 'SELECT *
            from prod_programado_mensual
            where prod_id='.$id_prod.' and g_id='.$this->session->userdata("gestion").''; 
        $query = $this->db->query($sql);
        return $query->result_array();

    }
    /*===================================================*/
    /*======= AGREGAR PRODUCTO  PROGRAMADO GESTION =====*/
    function add_prod_gest($id_prod,$gestion,$m_id,$valor){
        $data = array(
            'prod_id' => $id_prod,
            'm_id' => $m_id,
            'pg_fis' => $valor,
            'g_id' => $gestion,
        );
        $this->db->insert('prod_programado_mensual',$data);
    }
    /*==================================================*/

    /*--------- VERIF MES EVALUADO-FORM 4 ---------*/
    public function verif_ope_evaluado_mes($prod_id,$mes_id){
        $sql = 'select *
                from prod_ejecutado_mensual
                where g_id='.$this->gestion.' and prod_id='.$prod_id.' and m_id='.$mes_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*===== AGREGAR PRODUCTO  EJECUTADO GESTION (Cumplido-En proceso) =====*/
    function add_prod_ejec_gest($id_prod,$gestion,$m_id,$ejecutado,$mverificacion,$observacion,$acciones){
        $data = array(
            'prod_id' => $id_prod,
            'm_id' => $m_id,
            'pejec_fis' => $ejecutado,
            'g_id' => $gestion,
            'fun_id' => $this->fun_id,
            'medio_verificacion' => strtoupper($mverificacion),
            'observacion' => strtoupper($observacion),
            'acciones' => strtoupper($acciones),
        );
        $this->db->insert('prod_ejecutado_mensual',$data);
    }

    /*---------- Adiciona operaciones no cumplidas ---------*/
    function add_no_ejec_prod($prod_id,$mes_id,$mverificacion,$observacion,$acciones){
        $data = array(
            'prod_id' => $prod_id,
            'm_id' => $mes_id,
            'g_id' => $this->gestion,
            'medio_verificacion' => strtoupper($mverificacion),
            'observacion' => strtoupper($observacion),
            'acciones' => strtoupper($acciones),
        );
        $this->db->insert('prod_no_ejecutado_mensual',$data);
    }
    /*====================================================================*/
    /*=========== BORRA DATOS DE PRODUCTO PROGRAMADO GESTION =============*/
    public function delete_prod_gest($id_prod){ 
        $this->db->where('prod_id', $id_prod);
        $this->db->delete('prod_programado_mensual'); 
    }
    /*=====================================================================*/
    /*======= BORRA DATOS DE PRODUCTO PROGRAMADO GESTION ========*/
    public function delete_prod_ejec_gest($id_prod,$gest){ 
        $this->db->where('prod_id', $id_prod);
        $this->db->where('g_id', $gest);
        $this->db->delete('prod_ejecutado_mensual'); 

        $this->db->where('prod_id', $id_prod);
        $this->db->where('g_id', $gest);
        $this->db->delete('prod_ejecutado_mensual_relativo'); 
    }
    /*==========================================================*/

    /*======= NRO DE PRODUCTOS ===========*/
    public function productos_nro($id_c){
        $this->db->from('_productos');
        $this->db->where('com_id', $id_c);
        $query = $this->db->get();
        return $query->num_rows();
    }
    /*====================================*/    
    /*===== BORRA DATOS PRODUCTOS ========*/
    public function delete_producto_p($id_p){ 

        $this->db->where('prod_id', $id_p);
        $this->db->delete('prod_programado_mensual');
    }

    public function delete_producto_e($id_p){ 

        $this->db->where('prod_id', $id_p);
        $this->db->delete('prod_ejecutado_mensual');

        $this->db->where('prod_id', $id_p);
        $this->db->delete('_productos');
    }

    public function delete_producto($id_p){ 

        $this->db->where('prod_id', $id_p);
        $this->db->delete('_productos');
    }
    /*=============================================*/

    /*----------- GET PRODUCTO PROGRAMADO ---------*/
    public function programado_producto($prod_id){ 
        $sql = 'select *
                from prod_programado_mensual
                where prod_id='.$prod_id.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ GET PRODUCTO PROGRAMADO MES-------------------*/
    public function get_mes_programado_form4($prod_id,$mes_id){ 
        $sql = 'select *
                from prod_programado_mensual
                where prod_id='.$prod_id.' and m_id='.$mes_id.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ SUMA PRODUCTO PROGRAMADO -------------------*/
    public function suma_programado_producto($prod_id,$gestion){ 
        $sql = 'select prod_id,SUM(pg_fis) as prog
                from prod_programado_mensual
                where prod_id='.$prod_id.' and g_id='.$gestion.'
                GROUP BY prod_id'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*------ VERIF ALINEACION DEL REQUERIMIENTO A LA ACTIVIDAD anterior-----*/
    public function verif_componente_operacion($com_id,$prod_cod){ 
        $sql = 'select *
                from _productos p
                where p.com_id='.$com_id.' and p.prod_cod='.$prod_cod.' and p.estado!=\'3\''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*---- LISTA DE METAS - RELATIVO ----*/
    public function tp_metas(){
        $sql = 'select *
                from meta_relativo
                where estado!=\'0\'
                order by mt_id asc'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- SUMA PRODUCTO PROGRAMADO AL TRIMESTRE ACTUAL ----*/
    public function suma_prog_trimestre($prod_id,$fmes){ 
        $sql = 'select prod_id,sum(pg_fis) meta
                from prod_programado_mensual
                where prod_id='.$prod_id.' and (m_id>\'0\' and m_id<='.$fmes.')
                group by prod_id'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- SUMA TEMPORALIDAD EJECUTADO AL MES ACTUAL ----*/
    public function suma_ejec_trimestre($prod_id,$fmes){ 
        $sql = 'select prod_id,sum(pejec_fis) meta
                from prod_ejecutado_mensual
                where prod_id='.$prod_id.' and (m_id>\'0\' and m_id<='.$fmes.')
                group by prod_id'; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /// =============== PARA ACTUALIZAR LOS REQUERIMIENTOS DE PROYECTOS DE INVERSION A ISNUMOSPRODUCTO
    /*----- LISTA PRODUCTOS CON ID PROYECTO ----*/
    public function list_productos_proyecto($proy_id){ 
        $sql = 'select *
                from vista_producto
                where proy_id='.$proy_id.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    // ------ Lista Insumos por Productos - Proyectos de Inversion 
    public function lista_insumos_por_producto($prod_id){
        $sql = 'select *
                from _actividades a
                Inner Join _insumoactividad as ia On ia.act_id=a.act_id
                Inner Join insumos as i On i.ins_id=ia.ins_id
                where a.prod_id='.$prod_id.' and a.estado!=\'3\' and i.ins_estado!=\'3\' and i.aper_id!=\'0\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }
    /// ======================================


    //// VIGENTE PARA EL FORMULARIO DE SEGUIMIENTO POA (A OPTIMIZAR EN LA NUEVA VERSION)
    function producto_programado($prod_id,$gestion){
        $sql = 'select *
                from vista_productos_temporalizacion_programado_dictamen
                where prod_id='.$prod_id.' and g_id='.$gestion.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    function producto_ejecutado($prod_id,$gestion){
        $sql = 'select *
                from vista_productos_temporalizacion_ejecutado_dictamen
                where prod_id='.$prod_id.' and g_id='.$gestion.''; 
        $query = $this->db->query($sql);
        return $query->result_array();
    }


}
?>  
