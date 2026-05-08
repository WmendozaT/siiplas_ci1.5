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


    /*--- Detalle formulario N1 - Poblacion afiliada ---*/
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


    /*--- Detalle formulario N1 - Poblacion afiliada CONSOLIDADO---*/
    public function get_formulario_N1_consolidado(){
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
            dist.dist_id,
            dist.dist_distrital AS regional,
            dist.abrev AS abreviatura,
            g.anio AS gestion,
            COALESCE(d.nro_cot_tit, 0) AS titulares,
            COALESCE(d.nro_cot_pas, 0) AS pasivos,
            COALESCE(d.nro_cot_ben, 0) AS beneficiarios,
            (COALESCE(d.nro_cot_tit, 0) + COALESCE(d.nro_cot_pas, 0) + COALESCE(d.nro_cot_ben, 0)) AS total_gestion
        FROM public._distritales dist
        CROSS JOIN gestiones g
        LEFT JOIN formulario_diagnostico_pei f ON f.dist_id = dist.dist_id AND f.pei_id = g.pei_id
        LEFT JOIN formularion1_detalle d ON d.form_id = f.form_id AND d.g_id = g.anio
        WHERE dist.dist_estado = 1 
          AND dist.dist_id > 0  -- <--- EXCLUIMOS EL ID 0 AQUÍ
        ORDER BY dist.dist_id, g.anio ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N1 - Poblacion afiliada por grupo etareo---*/
    public function get_formulario_N1_etareo($dist_id){
        $sql = 'WITH rango_pei AS (
            SELECT pei_id, g_id_inicio, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        universo AS (
            SELECT eta_id, grupo_etareo 
            FROM tabla_grupo_etareo 
            WHERE estado = 1
        )
        SELECT 
            f.form_id,
            f.dist_id, 
            u.eta_id,
            u.grupo_etareo,
            -- GESTIÓN 2021
            SUM(CASE WHEN det.g_id = 2021 THEN det.nro_masculino ELSE 0 END) as m_2021,
            SUM(CASE WHEN det.g_id = 2021 THEN det.nro_femenino ELSE 0 END) as f_2021,
            SUM(CASE WHEN det.g_id = 2021 THEN det.total_poblacion ELSE 0 END) as t_2021,
            
            -- GESTIÓN 2022
            SUM(CASE WHEN det.g_id = 2022 THEN det.nro_masculino ELSE 0 END) as m_2022,
            SUM(CASE WHEN det.g_id = 2022 THEN det.nro_femenino ELSE 0 END) as f_2022,
            SUM(CASE WHEN det.g_id = 2022 THEN det.total_poblacion ELSE 0 END) as t_2022,
            
            -- GESTIÓN 2023
            SUM(CASE WHEN det.g_id = 2023 THEN det.nro_masculino ELSE 0 END) as m_2023,
            SUM(CASE WHEN det.g_id = 2023 THEN det.nro_femenino ELSE 0 END) as f_2023,
            SUM(CASE WHEN det.g_id = 2023 THEN det.total_poblacion ELSE 0 END) as t_2023,
            
            -- GESTIÓN 2024
            SUM(CASE WHEN det.g_id = 2024 THEN det.nro_masculino ELSE 0 END) as m_2024,
            SUM(CASE WHEN det.g_id = 2024 THEN det.nro_femenino ELSE 0 END) as f_2024,
            SUM(CASE WHEN det.g_id = 2024 THEN det.total_poblacion ELSE 0 END) as t_2024,
            
            -- GESTIÓN 2025
            SUM(CASE WHEN det.g_id = 2025 THEN det.nro_masculino ELSE 0 END) as m_2025,
            SUM(CASE WHEN det.g_id = 2025 THEN det.nro_femenino ELSE 0 END) as f_2025,
            SUM(CASE WHEN det.g_id = 2025 THEN det.total_poblacion ELSE 0 END) as t_2025
        FROM universo u
        CROSS JOIN rango_pei r 
        LEFT JOIN formulario_diagnostico_pei f ON (
            f.pei_id = r.pei_id 
            AND f.dist_id ='.$dist_id.' -- Reemplazar por $dist_id
        )
        LEFT JOIN formularion1_grupo_etareo det ON (
            det.form_id = f.form_id 
            AND det.eta_id = u.eta_id
        )
        GROUP BY f.form_id, u.eta_id, u.grupo_etareo
        ORDER BY f.dist_id,u.eta_id ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N1 - Poblacion afiliada por grupo etareo---*/
    public function get_formulario_N1_etareo_consolidado(){
        $sql = 'WITH rango_pei AS (
                    SELECT pei_id, g_id_inicio, g_id_fin 
                    FROM Diagnostico_pei 
                    WHERE estado = 1 
                    LIMIT 1
                ),
                universo_etareo AS (
                    SELECT eta_id, grupo_etareo 
                    FROM tabla_grupo_etareo 
                    WHERE estado = 1
                ),
                distritales AS (
                    -- Filtramos el universo de distritales reales
                    SELECT dist_id, dist_distrital, abrev 
                    FROM public._distritales 
                    WHERE dist_estado = 1 AND dist_id > 0
                )
                SELECT 
                    d.dist_id,
                    d.dist_distrital AS regional,
                    d.abrev AS abreviatura,
                    u.eta_id,
                    u.grupo_etareo,
                    -- GESTIÓN 2021
                    SUM(CASE WHEN det.g_id = 2021 THEN det.nro_masculino ELSE 0 END) as m_2021,
                    SUM(CASE WHEN det.g_id = 2021 THEN det.nro_femenino ELSE 0 END) as f_2021,
                    SUM(CASE WHEN det.g_id = 2021 THEN det.total_poblacion ELSE 0 END) as t_2021,
                    
                    -- GESTIÓN 2022
                    SUM(CASE WHEN det.g_id = 2022 THEN det.nro_masculino ELSE 0 END) as m_2022,
                    SUM(CASE WHEN det.g_id = 2022 THEN det.nro_femenino ELSE 0 END) as f_2022,
                    SUM(CASE WHEN det.g_id = 2022 THEN det.total_poblacion ELSE 0 END) as t_2022,
                    
                    -- GESTIÓN 2023
                    SUM(CASE WHEN det.g_id = 2023 THEN det.nro_masculino ELSE 0 END) as m_2023,
                    SUM(CASE WHEN det.g_id = 2023 THEN det.nro_femenino ELSE 0 END) as f_2023,
                    SUM(CASE WHEN det.g_id = 2023 THEN det.total_poblacion ELSE 0 END) as t_2023,
                    
                    -- GESTIÓN 2024
                    SUM(CASE WHEN det.g_id = 2024 THEN det.nro_masculino ELSE 0 END) as m_2024,
                    SUM(CASE WHEN det.g_id = 2024 THEN det.nro_femenino ELSE 0 END) as f_2024,
                    SUM(CASE WHEN det.g_id = 2024 THEN det.total_poblacion ELSE 0 END) as t_2024,
                    
                    -- GESTIÓN 2025
                    SUM(CASE WHEN det.g_id = 2025 THEN det.nro_masculino ELSE 0 END) as m_2025,
                    SUM(CASE WHEN det.g_id = 2025 THEN det.nro_femenino ELSE 0 END) as f_2025,
                    SUM(CASE WHEN det.g_id = 2025 THEN det.total_poblacion ELSE 0 END) as t_2025
                FROM universo_etareo u
                CROSS JOIN distritales d -- Crea la matriz Grupo x Regional
                CROSS JOIN rango_pei r 
                LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = d.dist_id)
                LEFT JOIN formularion1_grupo_etareo det ON (det.form_id = f.form_id AND det.eta_id = u.eta_id)
                GROUP BY d.dist_id, d.dist_distrital, d.abrev, u.eta_id, u.grupo_etareo
                ORDER BY d.dist_id, u.eta_id ASC;';

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
                    (COALESCE(d.nro_aportes_dia, 0) + COALESCE(d.nro_empresa_mora, 0)) AS total_gestion_empresas
                FROM formulario_diagnostico_pei f
                -- Cambiamos CROSS JOIN por INNER JOIN para poder usar ON
                INNER JOIN gestiones g ON f.pei_id = g.pei_id 
                LEFT JOIN formularion2_detalle d ON d.form_id = f.form_id AND d.g_id = g.anio
                WHERE f.dist_id = '.$dist_id.'
                ORDER BY g.anio ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N2 Consolidado---*/
    public function get_formulario_N2_consolidado(){
        $sql = 'WITH rango_pei AS (
                    SELECT pei_id, g_id_inicio, g_id_fin 
                    FROM Diagnostico_pei 
                    WHERE estado = 1 
                    LIMIT 1
                ),
                gestiones AS (
                    SELECT pei_id, generate_series(g_id_inicio, g_id_fin) AS anio
                    FROM rango_pei
                ),
                distritales AS (
                    -- Filtramos el universo de distritales reales excluyendo ID 0
                    SELECT dist_id, dist_distrital, abrev 
                    FROM public._distritales 
                    WHERE dist_estado = 1 AND dist_id > 0
                )
                SELECT 
                    d.dist_id,
                    d.dist_distrital AS regional,
                    d.abrev AS abreviatura,
                    g.anio AS gestion,
                    f.form_id,
                    COALESCE(det.nro_empresas_reg, 0) AS empresas,
                    COALESCE(det.nro_aportes_dia, 0) AS aportes,
                    COALESCE(det.nro_empresa_mora, 0) AS mora,
                    (COALESCE(det.nro_aportes_dia, 0) + COALESCE(det.nro_empresa_mora, 0)) AS total_gestion_empresas
                FROM distritales d
                CROSS JOIN gestiones g
                CROSS JOIN rango_pei r
                LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = d.dist_id)
                LEFT JOIN formularion2_detalle det ON (det.form_id = f.form_id AND det.g_id = g.anio)
                ORDER BY d.dist_id, g.anio ASC;';

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    /*--- Detalle formulario N3 ---*/
    public function get_formulario_N3($dist_id,$tipo_perfil_cat){
        $sql = "
            WITH rango_pei AS (
                SELECT pei_id, g_id_inicio, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
            ),
            diez_filas AS (
                SELECT generate_series(1, 10) AS nro 
            )
            SELECT 
                df.nro,
                f.form_id,   -- Agregado
                f.dist_id,   -- Agregado
                -- Gestión 2021
                COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN dp.nro_casos END), 0) AS nro_casos_2021,
                COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN dp.ce_id END), 0) AS ce_id_2021,
                COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN cie21.cod_3 || ' - ' || cie21.descripcion END), '') AS codigo_cie_2021,
                MAX(CASE WHEN mp.g_id = 2021 THEN dp.detalle_causa END) AS causa_2021,
                
                -- Gestión 2022
                COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN dp.nro_casos END), 0) AS nro_casos_2022,
                COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN dp.ce_id END), 0) AS ce_id_2022,
                COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN cie22.cod_3 || ' - ' || cie22.descripcion END), '') AS codigo_cie_2022,
                MAX(CASE WHEN mp.g_id = 2022 THEN dp.detalle_causa END) AS causa_2022,

                -- Gestión 2023
                COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN dp.nro_casos END), 0) AS nro_casos_2023,
                COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN dp.ce_id END), 0) AS ce_id_2023,
                COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN cie23.cod_3 || ' - ' || cie23.descripcion END), '') AS codigo_cie_2023,
                MAX(CASE WHEN mp.g_id = 2023 THEN dp.detalle_causa END) AS causa_2023,

                -- Gestión 2024
                COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN dp.nro_casos END), 0) AS nro_casos_2024,
                COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN dp.ce_id END), 0) AS ce_id_2024,
                COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN cie24.cod_3 || ' - ' || cie24.descripcion END), '') AS codigo_cie_2024,
                MAX(CASE WHEN mp.g_id = 2024 THEN dp.detalle_causa END) AS causa_2024,

                -- Gestión 2025
                COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN dp.nro_casos END), 0) AS nro_casos_2025,
                COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN dp.ce_id END), 0) AS ce_id_2025,
                COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN cie25.cod_3 || ' - ' || cie25.descripcion END), '') AS codigo_cie_2025,
                MAX(CASE WHEN mp.g_id = 2025 THEN dp.detalle_causa END) AS causa_2025
                
            FROM diez_filas df
            CROSS JOIN formulario_diagnostico_pei f
            LEFT JOIN formularion3_detalle_perfil mp ON mp.form_id = f.form_id
            LEFT JOIN detalle_form3_perfil dp ON dp.det3_id = mp.det3_id 
                 AND dp.tp_perfil = df.nro 
                 AND dp.tipo_perfil_cat = " . (int)$tipo_perfil_cat . "
            LEFT JOIN public.tabla_cie10 cie21 ON cie21.id = dp.ce_id AND mp.g_id = 2021
            LEFT JOIN public.tabla_cie10 cie22 ON cie22.id = dp.ce_id AND mp.g_id = 2022
            LEFT JOIN public.tabla_cie10 cie23 ON cie23.id = dp.ce_id AND mp.g_id = 2023
            LEFT JOIN public.tabla_cie10 cie24 ON cie24.id = dp.ce_id AND mp.g_id = 2024
            LEFT JOIN public.tabla_cie10 cie25 ON cie25.id = dp.ce_id AND mp.g_id = 2025
            WHERE f.dist_id = " . (int)$dist_id . "
            GROUP BY df.nro, f.form_id, f.dist_id -- Actualizado para incluir form y dist
            ORDER BY df.nro ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N3 - Consolidado---*/
    public function get_formulario_N3_consolidado(){
        $sql = "
            WITH rango_pei AS (
            SELECT pei_id, g_id_inicio, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        diez_filas AS (
            SELECT generate_series(1, 10) AS nro 
        ),
        categorias_perfil AS (
            -- Generamos los 3 tipos de perfil para la matriz
            SELECT generate_series(1, 3) AS cat_id
        ),
        distritales AS (
            -- Universo de distritales reales excluyendo ID 0
            SELECT dist_id, dist_distrital, abrev 
            FROM public._distritales 
            WHERE dist_estado = 1 AND dist_id > 0
        )
        SELECT 
            d.dist_id,
            d.dist_distrital AS regional,
            d.abrev AS abreviatura,
            cp.cat_id AS tipo_perfil_cat, -- 1, 2 o 3
            CASE 
                WHEN cp.cat_id = 1 THEN 'MORBILIDAD'
                WHEN cp.cat_id = 2 THEN 'MORTALIDAD'
                WHEN cp.cat_id = 3 THEN 'CONSULTA EXTERNA'
            END AS nombre_perfil,
            df.nro AS tp_perfil, -- Posición 1 al 10
            f.form_id,
            
            -- Gestión 2021
            COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN dp.nro_casos END), 0) AS nro_casos_2021,
            COALESCE(MAX(CASE WHEN mp.g_id = 2021 THEN cie21.cod_3 || ' - ' || cie21.descripcion END), '') AS codigo_cie_2021,
            MAX(CASE WHEN mp.g_id = 2021 THEN dp.detalle_causa END) AS causa_2021,
            
            -- Gestión 2022
            COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN dp.nro_casos END), 0) AS nro_casos_2022,
            COALESCE(MAX(CASE WHEN mp.g_id = 2022 THEN cie22.cod_3 || ' - ' || cie22.descripcion END), '') AS codigo_cie_2022,
            MAX(CASE WHEN mp.g_id = 2022 THEN dp.detalle_causa END) AS causa_2022,

            -- Gestión 2023
            COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN dp.nro_casos END), 0) AS nro_casos_2023,
            COALESCE(MAX(CASE WHEN mp.g_id = 2023 THEN cie23.cod_3 || ' - ' || cie23.descripcion END), '') AS codigo_cie_2023,
            MAX(CASE WHEN mp.g_id = 2023 THEN dp.detalle_causa END) AS causa_2023,

            -- Gestión 2024
            COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN dp.nro_casos END), 0) AS nro_casos_2024,
            COALESCE(MAX(CASE WHEN mp.g_id = 2024 THEN cie24.cod_3 || ' - ' || cie24.descripcion END), '') AS codigo_cie_2024,
            MAX(CASE WHEN mp.g_id = 2024 THEN dp.detalle_causa END) AS causa_2024,

            -- Gestión 2025
            COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN dp.nro_casos END), 0) AS nro_casos_2025,
            COALESCE(MAX(CASE WHEN mp.g_id = 2025 THEN cie25.cod_3 || ' - ' || cie25.descripcion END), '') AS codigo_cie_2025,
            MAX(CASE WHEN mp.g_id = 2025 THEN dp.detalle_causa END) AS causa_2025
            
        FROM distritales d
        CROSS JOIN diez_filas df
        CROSS JOIN categorias_perfil cp -- Multiplicamos por los 3 tipos de perfil
        CROSS JOIN rango_pei r
        LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = d.dist_id)
        LEFT JOIN formularion3_detalle_perfil mp ON mp.form_id = f.form_id
        LEFT JOIN detalle_form3_perfil dp ON dp.det3_id = mp.det3_id 
             AND dp.tp_perfil = df.nro 
             AND dp.tipo_perfil_cat = cp.cat_id -- Unimos dinámicamente por categoría
        LEFT JOIN public.tabla_cie10 cie21 ON cie21.id = dp.ce_id AND mp.g_id = 2021
        LEFT JOIN public.tabla_cie10 cie22 ON cie22.id = dp.ce_id AND mp.g_id = 2022
        LEFT JOIN public.tabla_cie10 cie23 ON cie23.id = dp.ce_id AND mp.g_id = 2023
        LEFT JOIN public.tabla_cie10 cie24 ON cie24.id = dp.ce_id AND mp.g_id = 2024
        LEFT JOIN public.tabla_cie10 cie25 ON cie25.id = dp.ce_id AND mp.g_id = 2025

        GROUP BY d.dist_id, d.dist_distrital, d.abrev, cp.cat_id, df.nro, f.form_id
        ORDER BY d.dist_id ASC, cp.cat_id ASC, df.nro ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------- Lista de cie10 ----------*/
    public function get_listado_cie10(){
        $sql = 'SELECT *
                from tabla_cie10
                order by id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N5 Infraestructura en establecimientos inscritos en el poa---*/
    public function get_infraestructura_por_nivel($dist_id,$tn_ids){
    $sql = "
            WITH rango_pei AS (
                SELECT pei_id, g_id_fin 
                FROM Diagnostico_pei 
                WHERE estado = 1 
                LIMIT 1
            ),
            establecimientos AS (
                SELECT v.act_id, v.act_descripcion, v.tipo, v.establecimiento, v.tn_id, v.nivel
                FROM vlista_establecimientos_salud v
                INNER JOIN rango_pei r ON v.aper_gestion = r.g_id_fin
                WHERE v.dist_id = $dist_id 
                  AND v.tn_id IN ($tn_ids) -- Aquí usamos IN para permitir múltiples IDs
            )
            SELECT 
                r.g_id_fin AS gestion_pei,
                f.form_id,
                f.dist_id,
                est.*,
                COALESCE(inf.tp_infra, 1) AS tp_infra,
                COALESCE(inf.ubicacion, '') AS ubicacion,
                COALESCE(inf.nro_consultorios, 0) AS nro_consultorios,
                COALESCE(inf.tipo_situacion, '') AS tipo_situacion,
                COALESCE(inf.serv_internet, '') AS serv_internet,
                inf.infra_id
            FROM establecimientos est
            CROSS JOIN rango_pei r
            LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = $dist_id)
            LEFT JOIN formularion4_detalle_infra det4 ON (det4.form_id = f.form_id AND det4.g_id = r.g_id_fin)
            LEFT JOIN infraestructura_form4 inf ON (inf.det4_id = det4.det4_id AND inf.act_id = est.act_id)
            ORDER BY est.nivel ASC, est.act_descripcion ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N5 Infraestructura que no estan establecimientos inscritos en el poa---*/
    public function get_otros_infraestructura_por_nivel($dist_id){
    $sql = "
            WITH rango_pei AS (
                -- Obtenemos el PEI activo y su gestión final
                SELECT pei_id, g_id_fin 
                FROM Diagnostico_pei 
                WHERE estado = 1 
                LIMIT 1
            )
            SELECT 
                r.g_id_fin AS gestion_pei,
                f.form_id,
                f.dist_id,
                inf.infra_otro_id,
                inf.otro_establecimiento, -- El nombre que el usuario escribe manualmente
                inf.tipo_establecimiento,
                inf.nivel_establecimiento,
                inf.ubicacion,
                inf.nro_consultorios,
                inf.tipo_situacion,
                inf.serv_internet,
                COALESCE(inf.tp_infra, 0) AS tp_infra -- 0 para otros (no alineados)
            FROM rango_pei r
            INNER JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id =$dist_id)
            INNER JOIN formularion4_detalle_infra det4 ON (det4.form_id = f.form_id AND det4.g_id = r.g_id_fin)
            INNER JOIN infraestructura_otros_form4 inf ON (inf.det4_id = det4.det4_id)
            ORDER BY inf.infra_otro_id ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--- Detalle formulario N5 Infraestructura Consolidado Institucional---*/
    public function get_infraestructura_por_nivel_consolidado(){
    $sql = "
            WITH rango_pei AS (
            SELECT pei_id, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
        ),
        distritales AS (
            SELECT dist_id, dist_distrital, abrev FROM public._distritales WHERE dist_estado = 1 AND dist_id > 0
        )
        -- PARTE 1: ESTABLECIMIENTOS OFICIALES (ALINEADOS)
        SELECT 
            d.dist_id,
            d.dist_distrital,
            d.abrev AS abreviatura,
            r.g_id_fin AS gestion_pei,
            v.act_id,
            v.act_descripcion AS nombre_establecimiento,
            v.tipo AS tipo_establecimiento,
            v.nivel AS nivel_establecimiento,
            COALESCE(inf.ubicacion, '') AS ubicacion,
            COALESCE(inf.nro_consultorios, 0) AS nro_consultorios,
            COALESCE(inf.serv_internet, '') AS serv_internet,
            COALESCE(inf.tipo_situacion, '') AS tipo_situacion,
            1 AS tp_infra,
            'SEGÚN POA' AS descripcion_infra
        FROM vlista_establecimientos_salud v
        INNER JOIN rango_pei r ON v.aper_gestion = r.g_id_fin
        INNER JOIN distritales d ON d.dist_id = v.dist_id
        LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = v.dist_id)
        LEFT JOIN formularion4_detalle_infra det4 ON (det4.form_id = f.form_id AND det4.g_id = r.g_id_fin)
        LEFT JOIN infraestructura_form4 inf ON (inf.det4_id = det4.det4_id AND inf.act_id = v.act_id)

        UNION ALL

        -- PARTE 2: OTROS ESTABLECIMIENTOS (NO ALINEADOS)
        SELECT 
            d.dist_id,
            d.dist_distrital,
            d.abrev AS abreviatura,
            r.g_id_fin AS gestion_pei,
            0 AS act_id, -- No tienen act_id oficial
            inf_o.otro_establecimiento AS nombre_establecimiento,
            inf_o.tipo_establecimiento,
            inf_o.nivel_establecimiento,
            inf_o.ubicacion,
            COALESCE(inf_o.nro_consultorios, 0) AS nro_consultorios,
            inf_o.serv_internet,
            inf_o.tipo_situacion,
            0 AS tp_infra,
            'OTROS ESTABLECIMIENTOS' AS descripcion_infra
        FROM infraestructura_otros_form4 inf_o
        INNER JOIN formularion4_detalle_infra det4 ON det4.det4_id = inf_o.det4_id
        INNER JOIN formulario_diagnostico_pei f ON f.form_id = det4.form_id
        INNER JOIN distritales d ON d.dist_id = f.dist_id
        CROSS JOIN rango_pei r
        WHERE det4.g_id = r.g_id_fin

        ORDER BY dist_id ASC, tp_infra DESC, nivel_establecimiento ASC, nombre_establecimiento ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N6 Diagnostico Camas (Establecimientos de 2 y 3 nivel)---*/
    public function get_diagnostico_camas($dist_id){
    $sql = "
            WITH rango_pei AS (
        -- Obtenemos el PEI activo y el rango de años
        SELECT pei_id, g_id_inicio, g_id_fin 
        FROM Diagnostico_pei 
        WHERE estado = 1 
        LIMIT 1
    ),
    establecimientos AS (
        -- Universo de establecimientos de 2do y 3er nivel de la distrital
        SELECT v.act_id, v.act_descripcion, v.tipo, v.nivel, v.dist_id
        FROM vlista_establecimientos_salud v
        INNER JOIN rango_pei r ON v.aper_gestion = r.g_id_fin
        WHERE v.dist_id = $dist_id
          AND v.tn_id IN (2,3)
    )
    SELECT 
        f.form_id,
        f.dist_id,
        est.act_id,
        est.act_descripcion,
        est.tipo,
        est.nivel,
        
            -- Gestión 2021
        COALESCE(MAX(CASE WHEN det5.g_id = 2021 THEN d5.nro_camas END), 0) AS camas_2021,
        COALESCE(MAX(CASE WHEN det5.g_id = 2021 THEN d5.ocupacion END), 0) AS ocupacion_2021,
        COALESCE(MAX(CASE WHEN det5.g_id = 2021 THEN d5.nro_estancia_media END), 0) AS estancia_2021,
        COALESCE(MAX(CASE WHEN det5.g_id = 2021 THEN d5.nro_giro_cama END), 0) AS giro_2021,
        
        -- Gestión 2022
        COALESCE(MAX(CASE WHEN det5.g_id = 2022 THEN d5.nro_camas END), 0) AS camas_2022,
        COALESCE(MAX(CASE WHEN det5.g_id = 2022 THEN d5.ocupacion END), 0) AS ocupacion_2022,
        COALESCE(MAX(CASE WHEN det5.g_id = 2022 THEN d5.nro_estancia_media END), 0) AS estancia_2022,
        COALESCE(MAX(CASE WHEN det5.g_id = 2022 THEN d5.nro_giro_cama END), 0) AS giro_2022,

        -- Gestión 2023
        COALESCE(MAX(CASE WHEN det5.g_id = 2023 THEN d5.nro_camas END), 0) AS camas_2023,
        COALESCE(MAX(CASE WHEN det5.g_id = 2023 THEN d5.ocupacion END), 0) AS ocupacion_2023,
        COALESCE(MAX(CASE WHEN det5.g_id = 2023 THEN d5.nro_estancia_media END), 0) AS estancia_2023,
        COALESCE(MAX(CASE WHEN det5.g_id = 2023 THEN d5.nro_giro_cama END), 0) AS giro_2023,

        -- Gestión 2024
        COALESCE(MAX(CASE WHEN det5.g_id = 2024 THEN d5.nro_camas END), 0) AS camas_2024,
        COALESCE(MAX(CASE WHEN det5.g_id = 2024 THEN d5.ocupacion END), 0) AS ocupacion_2024,
        COALESCE(MAX(CASE WHEN det5.g_id = 2024 THEN d5.nro_estancia_media END), 0) AS estancia_2024,
        COALESCE(MAX(CASE WHEN det5.g_id = 2024 THEN d5.nro_giro_cama END), 0) AS giro_2024,

        -- Gestión 2025
        COALESCE(MAX(CASE WHEN det5.g_id = 2025 THEN d5.nro_camas END), 0) AS camas_2025,
        COALESCE(MAX(CASE WHEN det5.g_id = 2025 THEN d5.ocupacion END), 0) AS ocupacion_2025,
        COALESCE(MAX(CASE WHEN det5.g_id = 2025 THEN d5.nro_estancia_media END), 0) AS estancia_2025,
        COALESCE(MAX(CASE WHEN det5.g_id = 2025 THEN d5.nro_giro_cama END), 0) AS giro_2025

    FROM establecimientos est
    CROSS JOIN rango_pei r
    LEFT JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = est.dist_id)
    LEFT JOIN formularion5_produccion_cama det5 ON (det5.form_id = f.form_id)
    LEFT JOIN detalle_form5_produccion_cama d5 ON (d5.det5_id = det5.det5_id AND d5.act_id = est.act_id)
    GROUP BY est.act_id, est.act_descripcion, est.tipo, est.nivel, f.form_id, f.dist_id
    ORDER BY est.nivel ASC, est.act_descripcion ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N7 Diagnostico Equipamiento (Establecimientos)---*/
    public function get_diagnostico_equipamiento($dist_id){
    $sql = "
            WITH rango_pei AS (
            -- Obtenemos el PEI activo y su gestión final (ej. 2025)
            SELECT pei_id, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        )
        SELECT 
            r.g_id_fin AS gestion_pei,
            f.form_id,
            f.dist_id,
            inf.det6_form6_id,
            inf.act_id, -- ID del establecimiento alineado
            est.tipo,
            est.act_descripcion,
            inf.servicio,
            inf.detalle_equipo,
            inf.precio_referencial
        FROM rango_pei r
        INNER JOIN formulario_diagnostico_pei f ON (f.pei_id = r.pei_id AND f.dist_id = $dist_id)
        -- Unimos con la cabecera del formulario 6 filtrando por la gestión final
        INNER JOIN formularion6_equipos det6 ON (det6.form_id = f.form_id AND det6.g_id = r.g_id_fin)
        -- Unimos con el detalle técnico de los equipos
        INNER JOIN detalle_form6_equipos inf ON (inf.det6_id = det6.det6_id)
        INNER JOIN vlista_establecimientos_salud est ON (est.act_id = inf.act_id) and (est.aper_gestion=r.g_id_fin)
        ORDER BY inf.det6_form6_id ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }




    /*--- Detalle formulario N8 Diagnostico Recursos Humanos---*/
    public function get_diagnostico_rrhh($dist_id){
    $sql = "
            WITH rango AS (
                SELECT pei_id, g_id_inicio, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
            ),
            categorias AS (
                SELECT 1 as id, 'PERSONAL DE ÍTEM' as nombre
                UNION ALL SELECT 2, 'PERSONAL DE CONTRATO'
                UNION ALL SELECT 3, 'ACEFALÍAS'
            )
            SELECT 
                c.id as tp_rrhh_form,
                c.nombre as categoria,
                f.dist_id,
                f.form_id,
                g.gestion,
                COALESCE(d.nro_medicos, 0) as nro_medicos,
                COALESCE(d.nro_odontologos, 0) as nro_odontologos,
                COALESCE(d.nro_farmaceuticos, 0) as nro_farmaceuticos,
                COALESCE(d.nro_laboratoristas, 0) as nro_laboratoristas,
                COALESCE(d.nro_otros_prof, 0) as nro_otros_prof,
                COALESCE(d.nro_nutricionistas, 0) as nro_nutricionistas,
                COALESCE(d.nro_trabajo_social, 0) as nro_trabajo_social,
                COALESCE(d.nro_jefe_superv_enf, 0) as nro_jefe_superv_enf,
                COALESCE(d.nro_lic_grad_enf, 0) as nro_lic_grad_enf,
                COALESCE(d.nro_aux_enf, 0) as nro_aux_enf,
                COALESCE(d.nro_pers_adm, 0) as nro_pers_adm,
                COALESCE(d.nro_pers_adm_salud, 0) as nro_pers_adm_salud,
                COALESCE(d.nro_pers_adm_tec, 0) as nro_pers_adm_tec,
                COALESCE(d.nro_pers_adm_aux, 0) as nro_pers_adm_aux,
                COALESCE(d.nro_pers_adm_chof, 0) as nro_pers_adm_chof,
                COALESCE(d.nro_pers_adm_artesanos, 0) as nro_pers_adm_artesanos,
                COALESCE(d.nro_pers_adm_trab_manual, 0) as nro_pers_adm_trab_manual,
                COALESCE(d.total, 0) as total
            FROM categorias c
            CROSS JOIN rango r
            -- Movimos el generate_series afuera para que reconozca los campos de 'r'
            CROSS JOIN LATERAL generate_series(r.g_id_inicio, r.g_id_fin) as g(gestion)
            LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = r.pei_id AND f.dist_id = $dist_id
            LEFT JOIN formularion7_rrhh h ON h.form_id = f.form_id AND h.g_id = g.gestion
            LEFT JOIN detalle_form7_rrhh d ON d.det7_id = h.det7_id AND d.tp_rrhh_form = c.id
            ORDER BY g.gestion ASC, c.id ASC;";

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
