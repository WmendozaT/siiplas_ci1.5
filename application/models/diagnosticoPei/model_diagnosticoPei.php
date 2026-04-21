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
        $sql = '
                SELECT 
                    ds.*, 
                    pei.*, 
                    form.*, 
                    COALESCE(obs1.obs_id, 0) AS verif_obs1, -- Cambia null por 0
                    obs1.obs_id,obs1.obs_nro as obs_nro1,obs1.obs_contenido as observacion1,
                    COALESCE(obs2.obs_id, 0) AS verif_obs2, -- Cambia null por 0
                    obs2.obs_id,obs2.obs_nro as obs_nro2,obs2.obs_contenido as observacion2,
                    COALESCE(obs3.obs_id, 0) AS verif_obs3, -- Cambia null por 0
                    obs3.obs_id,obs3.obs_nro as obs_nro3,obs3.obs_contenido as observacion3,
                    COALESCE(obs4.obs_id, 0) AS verif_obs4, -- Cambia null por 0
                    obs4.obs_id,obs4.obs_nro as obs_nro4,obs4.obs_contenido as observacion4,
                    COALESCE(obs5.obs_id, 0) AS verif_obs5, -- Cambia null por 0
                    obs5.obs_id,obs5.obs_nro as obs_nro5,obs5.obs_contenido as observacion5,
                    COALESCE(obs6.obs_id, 0) AS verif_obs6, -- Cambia null por 0
                    obs6.obs_id,obs6.obs_nro as obs_nro6,obs6.obs_contenido as observacion6,
                    COALESCE(obs7.obs_id, 0) AS verif_obs7, -- Cambia null por 0
                    obs7.obs_id,obs7.obs_nro as obs_nro7,obs7.obs_contenido as observacion7
                FROM diagnostico_pei pei
                INNER JOIN formulario_diagnostico_pei form ON form.pei_id = pei.pei_id
                LEFT JOIN form_observacion obs1 ON obs1.form_id = form.form_id and obs1.obs_nro=1
                LEFT JOIN form_observacion obs2 ON obs2.form_id = form.form_id and obs2.obs_nro=2
                LEFT JOIN form_observacion obs3 ON obs3.form_id = form.form_id and obs3.obs_nro=3
                LEFT JOIN form_observacion obs4 ON obs4.form_id = form.form_id and obs4.obs_nro=4
                LEFT JOIN form_observacion obs5 ON obs5.form_id = form.form_id and obs5.obs_nro=5
                LEFT JOIN form_observacion obs6 ON obs6.form_id = form.form_id and obs6.obs_nro=6
                LEFT JOIN form_observacion obs7 ON obs7.form_id = form.form_id and obs7.obs_nro=7
                INNER JOIN _distritales ds ON ds.dist_id = form.dist_id 
                WHERE pei.estado = 1 
                  AND pei.pei_id = '.$pei_id.' 
                  AND form.dist_id = '.$dist_id.'';

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
