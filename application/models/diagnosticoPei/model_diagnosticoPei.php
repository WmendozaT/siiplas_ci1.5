<?php
class model_diagnosticoPei extends CI_Model {

    public function __construct(){
        $this->load->database();
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
       
    }
    

    /*--------- Get diagnostico vigente ----------*/
    public function get_diagnostico_activo(){
        $sql = '
                SELECT *
                from diagnostico_pei
                where estado=1';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get Formulario Habilitado para el diagnostico por Distrital----------*/
    public function get_distrital_formulario_diagnostico_activo($pei_id,$dist_id){
        $sql = "
            SELECT 
                ds.*, 
                CASE 
                    WHEN ds.dist_tp = 1 THEN 'FIRMA ADMINISTRADOR REGIONAL'
                    WHEN ds.dist_tp = 0 THEN 'FIRMA AGENTE DISTRITAL'
                    ELSE 'RESPONSABLE'
                END AS tipo_firma,
                pei.*, 
                form.*, 
                COALESCE(obs1.obs_id, 0) AS verif_obs1, obs1.obs_id, obs1.obs_nro as obs_nro1, obs1.obs_contenido as observacion1,
                COALESCE(obs2.obs_id, 0) AS verif_obs2, obs2.obs_id, obs2.obs_nro as obs_nro2, obs2.obs_contenido as observacion2,
                COALESCE(obs3.obs_id, 0) AS verif_obs3, obs3.obs_id, obs3.obs_nro as obs_nro3, obs3.obs_contenido as observacion3,
                COALESCE(obs4.obs_id, 0) AS verif_obs4, obs4.obs_id, obs4.obs_nro as obs_nro4, obs4.obs_contenido as observacion4,
                COALESCE(obs5.obs_id, 0) AS verif_obs5, obs5.obs_id, obs5.obs_nro as obs_nro5, obs5.obs_contenido as observacion5,
                COALESCE(obs6.obs_id, 0) AS verif_obs6, obs6.obs_id, obs6.obs_nro as obs_nro6, obs6.obs_contenido as observacion6,
                COALESCE(obs7.obs_id, 0) AS verif_obs7, obs7.obs_id, obs7.obs_nro as obs_nro7, obs7.obs_contenido as observacion7
            FROM diagnostico_pei pei
            INNER JOIN formulario_diagnostico_pei form ON form.pei_id = pei.pei_id
            INNER JOIN _distritales ds ON ds.dist_id = form.dist_id 
            LEFT JOIN form_observacion obs1 ON obs1.form_id = form.form_id AND obs1.obs_nro = 1
            LEFT JOIN form_observacion obs2 ON obs2.form_id = form.form_id AND obs2.obs_nro = 2
            LEFT JOIN form_observacion obs3 ON obs3.form_id = form.form_id AND obs3.obs_nro = 3
            LEFT JOIN form_observacion obs4 ON obs4.form_id = form.form_id AND obs4.obs_nro = 4
            LEFT JOIN form_observacion obs5 ON obs5.form_id = form.form_id AND obs5.obs_nro = 5
            LEFT JOIN form_observacion obs6 ON obs6.form_id = form.form_id AND obs6.obs_nro = 6
            LEFT JOIN form_observacion obs7 ON obs7.form_id = form.form_id AND obs7.obs_nro = 7
            WHERE pei.estado = 1 
              AND pei.pei_id = ". (int)$pei_id ." 
              AND form.dist_id = ". (int)$dist_id;

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N1 ---*/
    public function get_formulario_N1($dist_id){
        $sql = 'WITH rango_pei AS (
                    SELECT pei_id, g_id_inicio, g_id_fin 
                    FROM Diagnostico_pei 
                    WHERE estado = 1 
                    LIMIT 1
                ),
                gestiones AS (
                    SELECT pei_id, generate_series(g_id_inicio, g_id_fin) AS anio
                    FROM rango_pei
                )
                SELECT 
                    f.form_id,
                    f.dist_id,
                    g.anio AS gestion,
                    COALESCE(d.nro_cot_tit, 0) AS titulares,
                    COALESCE(d.nro_cot_pas, 0) AS pasivos,
                    COALESCE(d.nro_cot_ben, 0) AS beneficiarios,
                    (COALESCE(d.nro_cot_tit, 0) + COALESCE(d.nro_cot_pas, 0) + COALESCE(d.nro_cot_ben, 0)) AS total_gestion
                FROM formulario_diagnostico_pei f
                -- Cambiamos CROSS JOIN por INNER JOIN para poder usar ON
                INNER JOIN gestiones g ON f.pei_id = g.pei_id 
                LEFT JOIN formularion1_detalle d ON d.form_id = f.form_id AND d.g_id = g.anio
                WHERE f.dist_id = '.$dist_id.'
                ORDER BY g.anio ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--- Detalle formulario N2 ---*/
    public function get_formulario_N2($dist_id){
        $sql = 'WITH rango_pei AS (
                    SELECT pei_id, g_id_inicio, g_id_fin 
                    FROM Diagnostico_pei 
                    WHERE estado = 1 
                    LIMIT 1
                ),
                gestiones AS (
                    SELECT pei_id, generate_series(g_id_inicio, g_id_fin) AS anio
                    FROM rango_pei
                )
                SELECT 
                    f.form_id,
                    f.dist_id,
                    g.anio AS gestion,
                    COALESCE(d.nro_empresas_reg, 0) AS empresas,
                    COALESCE(d.nro_aportes_dia, 0) AS aportes,
                    COALESCE(d.nro_empresa_mora, 0) AS mora,
                    (COALESCE(d.nro_empresas_reg, 0) + COALESCE(d.nro_aportes_dia, 0) + COALESCE(d.nro_empresa_mora, 0)) AS total_gestion_empresas
                FROM formulario_diagnostico_pei f
                -- Cambiamos CROSS JOIN por INNER JOIN para poder usar ON
                INNER JOIN gestiones g ON f.pei_id = g.pei_id 
                LEFT JOIN formularion2_detalle d ON d.form_id = f.form_id AND d.g_id = g.anio
                WHERE f.dist_id = '.$dist_id.'
                ORDER BY g.anio ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N3 ---*/
    public function get_formulario_N3($dist_id,$tipo_perfil_cat){
        $sql = '
        WITH rango_pei AS (
            SELECT pei_id, g_id_inicio, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
        ),
        diez_filas AS (
            SELECT generate_series(1, 10) AS nro 
        )
        SELECT 
            df.nro,
            -- Gestión 2021
            COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN dp.nro_casos END), 0) AS casos_2021,
            MAX(CASE WHEN mp.g_id = 2021 THEN dp.codigo_ce END) AS codigo_2021,
            MAX(CASE WHEN mp.g_id = 2021 THEN dp.detalle_causa END) AS causa_2021,
            
            
            -- Gestión 2022
            COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN dp.nro_casos END), 0) AS casos_2022,
            MAX(CASE WHEN mp.g_id = 2022 THEN dp.codigo_ce END) AS codigo_2022,
            MAX(CASE WHEN mp.g_id = 2022 THEN dp.detalle_causa END) AS causa_2022,
            

            -- Gestión 2023
            COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN dp.nro_casos END), 0) AS casos_2023,
            MAX(CASE WHEN mp.g_id = 2023 THEN dp.codigo_ce END) AS codigo_2023,
            MAX(CASE WHEN mp.g_id = 2023 THEN dp.detalle_causa END) AS causa_2023,
            

            -- Gestión 2024
            COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN dp.nro_casos END), 0) AS casos_2024,
            MAX(CASE WHEN mp.g_id = 2024 THEN dp.codigo_ce END) AS codigo_2024,
            MAX(CASE WHEN mp.g_id = 2024 THEN dp.detalle_causa END) AS causa_2024,
            

            -- Gestión 2025
            COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN dp.nro_casos END), 0) AS casos_2025,
            MAX(CASE WHEN mp.g_id = 2025 THEN dp.codigo_ce END) AS codigo_2025,
            MAX(CASE WHEN mp.g_id = 2025 THEN dp.detalle_causa END) AS causa_2025
            

        FROM diez_filas df
        CROSS JOIN formulario_diagnostico_pei f
        LEFT JOIN formularion3_detalle_perfil mp ON mp.form_id = f.form_id
        LEFT JOIN detalle_form3_perfil dp ON dp.det3_id = mp.det3_id 
             AND dp.tp_perfil = df.nro 
             AND dp.tipo_perfil_cat = 1
        WHERE f.dist_id = 1
        GROUP BY df.nro
        ORDER BY df.nro ASC';

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


    /*--------- Detalle General  ----------*/
    // public function general_diagnostico_pei(){
    //     $sql = 'WITH rango AS (
    //             SELECT g_id_inicio, g_id_fin 
    //             FROM public.Diagnostico_pei 
    //             WHERE estado = 1 
    //             LIMIT 1
    //         ),
    //         gestiones AS (
    //             SELECT generate_series(r.g_id_inicio, r.g_id_fin) AS anio
    //             FROM rango r
    //         )
    //         SELECT 
    //             di.dist_id,
    //             di.dist_distrital AS regional_distrital,
    //             g.anio AS gestion,
    //             COALESCE(df.det_from1_nro_coti_titular, 0) AS form1_titulares,
    //             COALESCE(df.det_form1_nro_coti_pasivo, 0) AS form1_pasivos,
    //             COALESCE(df.det_form1_nro_coti_benef, 0) AS form1_beneficiarios,
    //             -- Suma horizontal por fila (titulares + pasivos + beneficiarios)
    //             (COALESCE(df.det_from1_nro_coti_titular, 0) + 
    //              COALESCE(df.det_form1_nro_coti_pasivo, 0) + 
    //              COALESCE(df.det_form1_nro_coti_benef, 0)) AS total_form1_gestion,
    //             f1.form1_observacion
    //         FROM public._distritales di
    //         CROSS JOIN gestiones g
    //         LEFT JOIN formN1_diagnostico_pei f1 ON di.dist_id = f1.dist_id
    //         LEFT JOIN detalle_form1 df ON f1.form1_id = df.form1_id 
    //              AND df.det_form1_g_id = g.anio
    //         WHERE di.dist_estado = 1 AND di.dist_id NOT IN (0, 22)
    //         ORDER BY di.dist_id ASC, g.anio ASC;';

    //     $query = $this->db->query($sql);
    //     return $query->result_array();
    // }
}
