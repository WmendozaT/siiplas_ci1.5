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
                COALESCE(obs7.obs_id, 0) AS verif_obs7, obs7.obs_id, obs7.obs_nro as obs_nro7, obs7.obs_contenido as observacion7,
                COALESCE(obs8.obs_id, 0) AS verif_obs8, obs8.obs_id, obs8.obs_nro as obs_nro8, obs8.obs_contenido as observacion8,
                COALESCE(obs9.obs_id, 0) AS verif_obs9, obs9.obs_id, obs9.obs_nro as obs_nro9, obs9.obs_contenido as observacion9,
                COALESCE(obs10.obs_id, 0) AS verif_obs10, obs10.obs_id, obs10.obs_nro as obs_nro10, obs10.obs_contenido as observacion10,
                COALESCE(obs11.obs_id, 0) AS verif_obs11, obs11.obs_id, obs11.obs_nro as obs_nro11, obs11.obs_contenido as observacion11
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
            LEFT JOIN form_observacion obs8 ON obs8.form_id = form.form_id AND obs8.obs_nro = 8
            LEFT JOIN form_observacion obs9 ON obs9.form_id = form.form_id AND obs9.obs_nro = 9
            LEFT JOIN form_observacion obs10 ON obs10.form_id = form.form_id AND obs10.obs_nro = 10
            LEFT JOIN form_observacion obs11 ON obs11.form_id = form.form_id AND obs11.obs_nro = 11
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
          AND dist.dist_id > 0 AND dist.dist_id <> 22  -- <--- EXCLUIMOS EL ID 0 AQUÍ
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
                    WHERE dist_estado = 1 AND dist_id > 0 AND dist_id <> 22
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
                    WHERE dist_estado = 1 AND dist_id > 0 AND dist_id <> 22
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
            WHERE dist_estado = 1 AND dist_id > 0 AND dist_id <> 22
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
            SELECT dist_id, dist_distrital, abrev FROM public._distritales WHERE dist_estado = 1 AND dist_id > 0 AND dist_id <> 22
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


    /*--- Detalle formulario N9 Diagnostico Compra de Servicios ---*/
    public function get_diagnostico_compra_servicios($dist_id){
    $sql = "
            WITH rango AS (
                SELECT pei_id, g_id_inicio, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
            ),
            matriz AS (
                -- Generamos la base fija de 3 filas por año
                SELECT g.gestion, s.nro_fila, r.pei_id
                FROM rango r
                CROSS JOIN LATERAL generate_series(r.g_id_inicio, r.g_id_fin) AS g(gestion)
                CROSS JOIN LATERAL generate_series(1, 3) AS s(nro_fila)
            ),
            datos_registrados AS (
                -- Obtenemos los datos reales tal cual están en la tabla
                SELECT 
                    h.g_id,
                    d.*
                FROM formularion8_compra_servicios h
                INNER JOIN detalle_form8_compra_servicios d ON d.det8_id = h.det8_id
                INNER JOIN formulario_diagnostico_pei f ON f.form_id = h.form_id
                WHERE f.dist_id = $dist_id
            )
            SELECT 
                f.dist_id,
                m.gestion,
                m.nro_fila, -- Muestra siempre 1, 2, 3
                f.form_id,
                dr.det8_form8_id,
                COALESCE(dr.serv_contratado, '') as serv_contratado,
                COALESCE(dr.nro_atenciones, 0) as nro_atenciones,
                COALESCE(dr.costo_total, 0) as costo_total,
                COALESCE(dr.cservicios_observaciones, '') as cservicios_observaciones
            FROM matriz m
            LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = $dist_id
            -- UNIÓN CLAVE: Unimos la matriz con la tabla real usando gestión Y nro_posicion
            LEFT JOIN datos_registrados dr ON dr.g_id = m.gestion 
                                           AND dr.nro_posicion = m.nro_fila
            ORDER BY m.gestion ASC, m.nro_fila ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N9 Diagnostico Compra de Servicios ---*/
    public function get_diagnostico_compra_servicios_consolidado(){
    $sql = "
            WITH rango AS (
                -- Obtenemos el PEI activo
                SELECT pei_id, g_id_inicio, g_id_fin FROM Diagnostico_pei WHERE estado = 1 LIMIT 1
            ),
            distritales_activas AS (
                -- Universo de distritales (excluyendo dist_id 0 y el ID 22)
                SELECT dist_id, dist_distrital, abrev 
                FROM public._distritales 
                WHERE dist_estado = 1 
                  AND dist_id > 0 
                  AND dist_id <> 22
            ),
            matriz_base AS (
                -- Generamos la estructura completa: Distritales x Años x 3 Filas fijas
                SELECT 
                    d.dist_id, 
                    d.dist_distrital, 
                    d.abrev, 
                    g.gestion, 
                    s.nro_fila, 
                    r.pei_id
                FROM distritales_activas d
                CROSS JOIN rango r
                CROSS JOIN LATERAL generate_series(r.g_id_inicio, r.g_id_fin) AS g(gestion)
                CROSS JOIN LATERAL generate_series(1, 3) AS s(nro_fila)
            ),
            datos_registrados AS (
                -- Obtenemos los datos reales existentes sin usar ROW_NUMBER
                SELECT 
                    f.dist_id,
                    h.g_id,
                    d.*
                FROM formularion8_compra_servicios h
                INNER JOIN detalle_form8_compra_servicios d ON d.det8_id = h.det8_id
                INNER JOIN formulario_diagnostico_pei f ON f.form_id = h.form_id
                WHERE f.dist_id <> 22
            )
            SELECT 
                m.dist_id,
                m.dist_distrital as regional,
                m.abrev as abreviatura,
                m.gestion,
                m.nro_fila, -- Este es el casillero 1, 2 o 3 de la matriz
                f.form_id,
                dr.det8_form8_id,
                COALESCE(dr.serv_contratado, '') as serv_contratado,
                COALESCE(dr.nro_atenciones, 0) as nro_atenciones,
                COALESCE(dr.costo_total, 0) as costo_total,
                COALESCE(dr.cservicios_observaciones, '') as cservicios_observaciones
            FROM matriz_base m
            -- Unimos con el formulario para verificar vinculación
            LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = m.dist_id
            -- UNIÓN CLAVE: Cruce por regional, gestión y posición exacta (nro_posicion)
            LEFT JOIN datos_registrados dr ON dr.dist_id = m.dist_id 
                                           AND dr.g_id = m.gestion 
                                           AND dr.nro_posicion = m.nro_fila
            ORDER BY m.dist_id ASC, m.gestion ASC, m.nro_fila ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N10 Diagnostico de Presupuestos ---*/
    public function get_diagnostico_presupuestos($dist_id){
    $sql = "
           WITH pei_activo AS (
            -- 1. Obtenemos el rango de años del PEI vigente
            SELECT pei_id, g_id_inicio, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        matriz_gestiones AS (
            -- 2. Generamos las filas para cada año del periodo
            SELECT p.pei_id, g.anio
            FROM pei_activo p
            CROSS JOIN LATERAL generate_series(p.g_id_inicio, p.g_id_fin) AS g(anio)
        )
        SELECT 
            m.anio AS gestion,
            f.form_id,
            f.dist_id,
            det.det9_form9_id,
            -- Campos de Ingresos
            COALESCE(det.ingresos_propios_programados, 0) AS ingresos_propios_programados,
            COALESCE(det.ingresos_propios_ejecutados, 0) AS ingresos_propios_ejecutados,
            COALESCE(det.recursos_financieros_programados, 0) AS recursos_financieros_programados,
            COALESCE(det.recursos_financieros_ejecutados, 0) AS recursos_financieros_ejecutados,
            COALESCE(det.total_ingresos_ejecutados, 0) AS total_ingresos_ejecutados,
            -- Campos de Gastos
            COALESCE(det.gastos_programados, 0) AS gastos_programados,
            COALESCE(det.gastos_ejecutados, 0) AS gastos_ejecutados,
            -- Resultado (Déficit/Superávit)
            COALESCE(det.deficit_superavit, 0) AS deficit_superavit
        FROM matriz_gestiones m
        -- Unimos con el formulario de la distrital específica
        LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = $dist_id
        -- Unimos con la cabecera del presupuesto anual
        LEFT JOIN formularion9_presupuestos h ON h.form_id = f.form_id AND h.g_id = m.anio
        -- Unimos con el detalle técnico de valores
        LEFT JOIN detalle_form9_presupuestos det ON det.det9_id = h.det9_id
        ORDER BY m.anio ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N10 Diagnostico de Presupuestos Consolidado---*/
    public function get_diagnostico_presupuestos_consolidado(){
    $sql = "
           WITH pei_activo AS (
                -- 1. Obtenemos el PEI vigente
                SELECT pei_id, g_id_inicio, g_id_fin 
                FROM Diagnostico_pei 
                WHERE estado = 1 
                LIMIT 1
            ),
            distritales_universo AS (
                -- 2. Universo de regionales activas (Excluyendo dist_id 22 y 0)
                SELECT dist_id, dist_distrital, abrev 
                FROM public._distritales 
                WHERE dist_estado = 1 
                  AND dist_id > 0 
                  AND dist_id <> 22
            ),
            matriz_base AS (
                -- 3. Generamos la matriz completa: Distritales x Años del PEI
                SELECT d.dist_id, d.dist_distrital, d.abrev, g.anio, p.pei_id
                FROM distritales_universo d
                CROSS JOIN pei_activo p
                CROSS JOIN LATERAL generate_series(p.g_id_inicio, p.g_id_fin) AS g(anio)
            )
            SELECT 
                m.dist_id,
                m.dist_distrital AS regional,
                m.abrev AS abreviatura,
                m.anio AS gestion,
                f.form_id,
                det.det9_form9_id,
                -- Campos de Ingresos
                COALESCE(det.ingresos_propios_programados, 0) AS ingresos_propios_programados,
                COALESCE(det.ingresos_propios_ejecutados, 0) AS ingresos_propios_ejecutados,
                COALESCE(det.recursos_financieros_programados, 0) AS recursos_financieros_programados,
                COALESCE(det.recursos_financieros_ejecutados, 0) AS recursos_financieros_ejecutados,
                COALESCE(det.total_ingresos_ejecutados, 0) AS total_ingresos_ejecutados,
                -- Campos de Gastos
                COALESCE(det.gastos_programados, 0) AS gastos_programados,
                COALESCE(det.gastos_ejecutados, 0) AS gastos_ejecutados,
                -- Resultado (Déficit/Superávit)
                COALESCE(det.deficit_superavit, 0) AS deficit_superavit
            FROM matriz_base m
            -- Unimos con el formulario de cada distrital
            LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = m.dist_id
            -- Unimos con cabecera de presupuesto
            LEFT JOIN formularion9_presupuestos h ON h.form_id = f.form_id AND h.g_id = m.anio
            -- Unimos con detalle de valores
            LEFT JOIN detalle_form9_presupuestos det ON det.det9_id = h.det9_id
            ORDER BY m.dist_id ASC, m.anio ASC";

        $query = $this->db->query($sql);
        return $query->result_array();
    }



    /*--- Detalle formulario N11 Diagnostico de Reembolsos ---*/
    public function get_diagnostico_reembolsos($dist_id){
    $sql = "
           WITH pei_activo AS (
            -- 1. Obtenemos el periodo del PEI vigente
            SELECT pei_id, g_id_inicio, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        matriz_gestiones AS (
            -- 2. Generamos las filas para cada año (2021 a 2025)
            SELECT p.pei_id, g.anio
            FROM pei_activo p
            CROSS JOIN LATERAL generate_series(p.g_id_inicio, p.g_id_fin) AS g(anio)
        )
        SELECT 
            m.anio AS gestion,
            f.form_id,
            f.dist_id,
            det.det10_form10_id,
            -- Conceptos de Reembolso
            COALESCE(det.reemb_concep_medicamentos, 0) AS reemb_concep_medicamentos,
            COALESCE(det.reemb_concep_laboratorio, 0) AS reemb_concep_laboratorio,
            COALESCE(det.reemb_concep_imagenologia, 0) AS reemb_concep_imagenologia,
            COALESCE(det.reemb_otros_conceptos, 0) AS reemb_otros_conceptos,
            -- Total Calculado
            COALESCE(det.total_reembolsos, 0) AS total_reembolsos
        FROM matriz_gestiones m
        -- Unimos con el formulario de la regional específica ($dist_id)
        LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = $dist_id
        -- Unimos con la cabecera de Reembolsos anual
        LEFT JOIN formularion10_reembolsos h ON h.form_id = f.form_id AND h.g_id = m.anio
        -- Unimos con el detalle de montos
        LEFT JOIN detalle_form10_presupuestos det ON det.det10_id = h.det10_id
        ORDER BY m.anio ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N11 Diagnostico de Reembolsos Consolidado---*/
    public function get_diagnostico_reembolsos_consolidado(){
    $sql = "
        WITH pei_activo AS (
            -- 1. Obtenemos el periodo del PEI vigente
            SELECT pei_id, g_id_inicio, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        distritales_universo AS (
            -- 2. Universo de regionales activas (Excluyendo dist_id 0 y dist_id 22)
            SELECT dist_id, dist_distrital, abrev 
            FROM public._distritales 
            WHERE dist_estado = 1 
              AND dist_id > 0 
              AND dist_id <> 22
        ),
        matriz_base AS (
            -- 3. Generamos la matriz completa: Distritales x Años del PEI
            SELECT d.dist_id, d.dist_distrital, d.abrev, g.anio, p.pei_id
            FROM distritales_universo d
            CROSS JOIN pei_activo p
            CROSS JOIN LATERAL generate_series(p.g_id_inicio, p.g_id_fin) AS g(anio)
        )
        SELECT 
            m.dist_id,
            m.dist_distrital AS regional,
            m.abrev AS abreviatura,
            m.anio AS gestion,
            f.form_id,
            det.det10_form10_id,
            -- Conceptos de Reembolso
            COALESCE(det.reemb_concep_medicamentos, 0) AS reemb_concep_medicamentos,
            COALESCE(det.reemb_concep_laboratorio, 0) AS reemb_concep_laboratorio,
            COALESCE(det.reemb_concep_imagenologia, 0) AS reemb_concep_imagenologia,
            COALESCE(det.reemb_otros_conceptos, 0) AS reemb_otros_conceptos,
            -- Total Calculado
            COALESCE(det.total_reembolsos, 0) AS total_reembolsos
        FROM matriz_base m
        -- Unimos con el formulario de cada regional
        LEFT JOIN formulario_diagnostico_pei f ON f.pei_id = m.pei_id AND f.dist_id = m.dist_id
        -- Unimos con la cabecera de Reembolsos anual
        LEFT JOIN formularion10_reembolsos h ON h.form_id = f.form_id AND h.g_id = m.anio
        -- Unimos con el detalle de montos
        LEFT JOIN detalle_form10_presupuestos det ON det.det10_id = h.det10_id
        ORDER BY m.dist_id ASC, m.anio ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- Detalle formulario N12 Detalle de Ambulancias ---*/
    public function get_detalle_ambulancias($dist_id){
    $sql = "
        WITH rango_pei AS (
            -- 1. Obtenemos el PEI activo para amarrar la vigencia del formulario
            SELECT pei_id, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        formulario_actual AS (
            -- 2. Identificamos el formulario maestro de la distrital y recuperamos su dist_id y form_id
            SELECT form_id, dist_id 
            FROM formulario_diagnostico_pei 
            WHERE dist_id = $dist_id
              AND pei_id = (SELECT pei_id FROM rango_pei)
        ),
        establecimientos_maestros AS (
            -- 3. Traemos el catálogo de nombres oficiales de tus centros médicos
            SELECT v.act_id, CONCAT(v.tipo, ' ', v.act_descripcion) AS nombre_establecimiento
            FROM public.vlista_establecimientos_salud v
            WHERE v.dist_id = $dist_id
              AND v.aper_gestion = (SELECT g_id_fin FROM rango_pei)
        )
        SELECT 
            -- Genera el número correlativo (1, 2, 3, 4...) de tu primera columna de la imagen
            ROW_NUMBER() OVER(ORDER BY det.det11_form11_id ASC) AS nro,
            
            det.det11_form11_id,
            det.det11_id,
            
            -- AJUSTE CRÍTICO: Extraemos el dist_id directamente del Formulario de Diagnóstico Maestro
            f_act.dist_id,
            cab.form_id,
            
            COALESCE(det.placa, '---') AS placa,
            COALESCE(det.gestion, 0) AS anio_adjudicacion, 
            
            -- === DECODIFICACIÓN NATURAL DEL ESTADO (Campos Integer) ===
            CASE det.estado_ambulancia
                WHEN 1 THEN 'EXCELENTE'
                WHEN 2 THEN 'BUENO'
                WHEN 3 THEN 'REGULAR'
                WHEN 4 THEN 'MALO'
                ELSE 'SIN REGISTRO'
            END AS estado_ambulancia,
            
            -- === DECODIFICACIÓN NATURAL DE LA SITUACIÓN (Campos Integer) ===
            CASE det.situacion_ambulancia
                WHEN 1 THEN 'ACTIVO'
                WHEN 2 THEN 'BAJA TEMPORAL'
                WHEN 3 THEN 'BAJA DEFINITIVA'
                WHEN 4 THEN 'RETENIDO'
                ELSE 'SIN REGISTRO'
            END AS situacion_ambulancia,
            
            -- === ALINEACIÓN DEL ESTABLECIMIENTO A LA DERECHA ===
            COALESCE(est.nombre_establecimiento, 'SIN ASIGNACIÓN') AS establecimiento
            
        FROM formularion11_ambulancias cab
        -- CROSS JOIN: Vinculamos los datos recuperados del formulario de la distrital seleccionada
        CROSS JOIN formulario_actual f_act
        -- INNER JOIN: Filtra solo las ambulancias vinculadas a esta cabecera distrital
        INNER JOIN detalle_form11_ambulancias det ON det.det11_id = cab.det11_id
        -- LEFT JOIN: Cruce elástico 1 a muchos alineado por el act_id
        LEFT JOIN establecimientos_maestros est ON est.act_id = det.act_id
        WHERE cab.form_id = f_act.form_id
        ORDER BY det.det11_form11_id ASC;";

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--- Detalle formulario N12 Detalle de Ambulancias Consolidado---*/
    public function get_detalle_ambulancias_consolidado(){
    $sql = "
            WITH pei_activo AS (
            -- 1. Obtenemos el periodo del PEI vigente (2026 - 2030)
            SELECT pei_id, g_id_fin 
            FROM Diagnostico_pei 
            WHERE estado = 1 
            LIMIT 1
        ),
        distritales_universo AS (
            -- 2. Universo de regionales activas (Excluyendo dist_id 0 y dist_id 22)
            SELECT dist_id, dist_distrital, abrev 
            FROM public._distritales 
            WHERE dist_estado = 1 
              AND dist_id > 0 
              AND dist_id <> 22
        ),
        establecimientos_maestros AS (
            -- 3. Catálogo nacional de nombres oficiales de centros médicos
            SELECT v.dist_id, v.act_id, CONCAT(v.tipo, ' ', v.act_descripcion) AS nombre_establecimiento
            FROM public.vlista_establecimientos_salud v
            WHERE v.aper_gestion = (SELECT g_id_fin FROM pei_activo)
        )
        SELECT 
            -- Genera el número correlativo nacional único (1, 2, 3, 4...) para la grilla plana consolidada
            ROW_NUMBER() OVER(ORDER BY f.dist_id ASC, det.det11_form11_id ASC) AS nro,
            
            -- EXTRACCIÓN MAESTRA DEL DIST_ID DEL FORMULARIO DIAGNÓSTICO
            f.dist_id,
            d.dist_distrital AS regional,
            d.abrev AS abreviatura,
            f.form_id,
            
            cab.det11_id,
            det.det11_form11_id,
            COALESCE(det.placa, '---') AS placa,
            COALESCE(det.gestion, 0) AS anio_adjudicacion, 
            
            -- === DECODIFICACIÓN NATURAL DEL ESTADO (Campos Integer) ===
            CASE det.estado_ambulancia
                WHEN 1 THEN 'EXCELENTE'
                WHEN 2 THEN 'BUENO'
                WHEN 3 THEN 'REGULAR'
                WHEN 4 THEN 'MALO'
                ELSE 'SIN REGISTRO'
            END AS estado_ambulancia,
            
            -- === DECODIFICACIÓN NATURAL DE LA SITUACIÓN (Campos Integer) ===
            CASE det.situacion_ambulancia
                WHEN 1 THEN 'ACTIVO'
                WHEN 2 THEN 'BAJA TEMPORAL'
                WHEN 3 THEN 'BAJA DEFINITIVA'
                WHEN 4 THEN 'RETENIDO'
                ELSE 'SIN REGISTRO'
            END AS situacion_ambulancia,
            
            -- === ALINEACIÓN DEL ESTABLECIMIENTO A LA DERECHA ===
            COALESCE(est.nombre_establecimiento, 'SIN ASIGNACIÓN') AS establecimiento,
            COALESCE(cab.form11_estado, 0) AS form11_estado
            
        FROM distritales_universo d
        -- INNER JOIN: Garantiza que la distrital cuente con su formulario de diagnóstico PEI habilitado
        INNER JOIN formulario_diagnostico_pei f ON f.dist_id = d.dist_id AND f.pei_id = (SELECT pei_id FROM pei_activo)
        -- LEFT JOIN: Mapea la cabecera unificada del Formulario 11
        LEFT JOIN formularion11_ambulancias cab ON cab.form_id = f.form_id
        -- LEFT JOIN (1 a Muchos): Desglosa de forma elástica todas las unidades vehiculares registradas
        LEFT JOIN detalle_form11_ambulancias det ON det.det11_id = cab.det11_id
        -- LEFT JOIN: Cruce contra el catálogo de nombres médicos vinculando estrictamente f.dist_id
        LEFT JOIN establecimientos_maestros est ON est.act_id = det.act_id AND est.dist_id = f.dist_id
        ORDER BY f.dist_id ASC, det.det11_form11_id ASC;";

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
