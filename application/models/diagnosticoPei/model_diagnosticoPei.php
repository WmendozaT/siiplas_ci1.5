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
                from Diagnostico_pei
                where estado=1';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get Formulario Habilitado para el diagnostico ----------*/
    public function get_distrital_formulario_diagnostico_activo($pei_id,$dist_id){
        $sql = '
                SELECT ds.*,pei.*,form.*,obs.*
                from Diagnostico_pei pei
                Inner join formulario_diagnostico_pei form On form.pei_id=pei.pei_id
                left join form_observacion obs On obs.form_id=form.form_id
                Inner join _distritales ds On ds.dist_id=form.dist_id 
                where pei.estado=1 and pei.pei_id='.$pei_id.' and form.dist_id='.$dist_id.'';

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
