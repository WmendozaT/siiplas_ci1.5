<?php
class Model_modfisica extends CI_Model{
    var $gestion;
    public function __construct(){
        $this->load->database();
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_actividad');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');
        $this->gestion = $this->session->userData('gestion');
        $this->fun_id = $this->session->userData('fun_id');
        $this->rol = $this->session->userData('rol_id');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
    }


    /*---- GET DATOS CITE (FISICO)----*/
    function get_cite_fis($cite_id){
             $sql = '
                SELECT 
                    ci.cite_id, ci.com_id, ci.cite_codigo, ci.cite_nota, ci.cite_fecha, ci.tp_reporte,
                    ci.cite_estado, ci.cite_activo, ci.tipo_modificacion, ci.fun_id,
                    f.fun_nombre, f.fun_paterno, f.fun_materno,f.fun_cargo,
                    c.com_componente,
                    tpsa.tipo_subactividad,
                    poa.*
                FROM cite_mod_fisica ci
                INNER JOIN funcionario f ON ci.fun_id = f.fun_id
                INNER JOIN _componentes c ON ci.com_id = c.com_id
                INNER JOIN tipo_subactividad tpsa ON c.tp_sact = tpsa.tp_sact
                INNER JOIN fnlista_poa_nacional('.$this->gestion.') poa ON c.pfec_id = poa.pfec_id
                -- Eliminado: INNER JOIN servicios_actividad sa (No se usan sus columnas)
                WHERE ci.cite_id = '.$cite_id.'';


   /*     $sql = 'select *
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join servicios_actividad as sa On sa.serv_id=c.serv_id
                Inner Join tipo_subactividad as tpsa On tpsa.tp_sact=c.tp_sact
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                Inner Join aperturaproyectos as ap On ap.proy_id=p.proy_id
                Inner Join aperturaprogramatica as apg On apg.aper_id=ap.aper_id
                Inner Join _departamentos as d On d.dep_id=p.dep_id
                Inner Join _distritales as ds On ds.dist_id=p.dist_id
                Inner Join unidad_actividad as ua On ua.act_id=p.act_id
                Inner Join v_tp_establecimiento as te On te.te_id=ua.te_id
                where ci.cite_id='.$cite_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and apg.aper_gestion='.$this->gestion.'';*/
        $query = $this->db->query($sql);
        return $query->result_array();
    }



    /*--- LISTA MODIFICADOS ITEMS 2023---*/
    public function list_form4_historial_modificados($cite_id,$tipo_mod){
        ///ih.historial_activo : 0 (no se muestra)
        ///ih.historial_activo : 1 (se muestra)

// /*        if($tipo_mod==2){
//             $sql = 'SELECT DISTINCT
//                 ae.acc_codigo,
//                 og.og_codigo,
//                 ore.or_codigo,
//                 ph.prodh_producto,
//                 ph.prodh_indicador,
//                 ph.prodh_linea_base,
//                 ph.prodh_meta,
//                 ph.prod_fuente_verificacion,
//                 ph.prod_resultado,
//                 ph.prod_cod,
//                 ph.prod_id,
//                 ph.prodh_unidades,
//                 ph.huni_resp,
//                 ph.indi_id,
//                 ph.acc_id,
//                 ph.mt_id,
//                 ph.or_id,
//                 prog.*
//             FROM _producto_historial ph
//             INNER JOIN objetivos_regionales ore ON ore.or_id = ph.or_id
//             INNER JOIN vista_temporalidad_form4_programado_uresp prog ON ph.prod_id = prog.prod_id
//             INNER JOIN indicador i ON i.indi_id = ph.indi_id
//             -- Nota: Si opm tiene varios registros por objetivo, esto causa duplicados
//             INNER JOIN objetivo_programado_mensual opm ON ore.pog_id = opm.pog_id
//             INNER JOIN objetivo_gestion og ON og.og_id = opm.og_id
//             INNER JOIN _acciones_estrategicas ae ON ae.acc_id = og.acc_id
//             -- WHERE con paso de parámetros seguro (Bindings ?)
//             WHERE ph.cite_id = '.$cite_id.' 
//               AND ph.tipo_mod = '.$tipo_mod.'
//               AND ph.historial_activo != 0
//             ORDER BY ph.prod_cod ASC';

// /*select ae.acc_codigo,og.og_codigo,ore.or_codigo,ph.prodh_producto,ph.indi_id,ph.prodh_indicador,ph.prodh_linea_base,ph.prodh_meta,ph.prod_fuente_verificacion,ph.prod_resultado,ph.acc_id,ph.prod_cod,ph.mt_id,ph.or_id,ph.prod_id,ph.prodh_unidades,ph.huni_resp
//                 from _producto_historial ph
//                 Inner Join indicador as i On i.indi_id=ph.indi_id
//                 Inner Join objetivos_regionales as ore On ore.or_id=ph.or_id

//                 Inner Join objetivo_programado_mensual as opm On ore.pog_id=opm.pog_id
//                 Inner Join objetivo_gestion as og On og.og_id=opm.og_id
//                 Inner Join _acciones_estrategicas as ae On ae.acc_id=og.acc_id
//                 Inner Join _objetivos_estrategicos as oe On oe.obj_id=ae.obj_id
//                 where ph.cite_id='.$cite_id.' and ph.tipo_mod='.$tipo_mod.' and ph.historial_activo!=\'0\'
//                 group by ae.acc_codigo,og.og_codigo,ore.or_codigo,ph.prodh_producto,ph.indi_id,ph.prodh_indicador,ph.prodh_linea_base,ph.prodh_meta,ph.prod_fuente_verificacion,ph.prod_resultado,ph.acc_id,ph.prod_cod,ph.mt_id,ph.or_id,ph.prod_id,ph.prodh_unidades,ph.huni_resp
//                 order by ph.prod_cod asc';*/



//         }
//         else{
//             $sql = 'select *,ph.indi_id
//                 from _producto_historial ph
//                 Inner Join indicador as i On i.indi_id=ph.indi_id
//                 Inner Join objetivos_regionales as ore On ore.or_id=ph.or_id

//                 Inner Join objetivo_programado_mensual as opm On ore.pog_id=opm.pog_id
//                 Inner Join objetivo_gestion as og On og.og_id=opm.og_id
//                 Inner Join _acciones_estrategicas as ae On ae.acc_id=og.acc_id
//                 Inner Join _objetivos_estrategicos as oe On oe.obj_id=ae.obj_id
//                 where ph.cite_id='.$cite_id.' and ph.tipo_mod='.$tipo_mod.' and ph.historial_activo!=\'0\'
//                 order by ph.prodh_id, ph.prod_cod asc';
//         }*/


        $sql = 'SELECT DISTINCT
                ae.acc_codigo,
                og.og_codigo,
                ore.or_codigo,
                ph.prodh_producto,
                ph.prodh_indicador,
                ph.prodh_linea_base,
                ph.prodh_meta,
                ph.prod_fuente_verificacion,
                ph.prod_resultado,
                ph.prod_cod,
                ph.prod_id,
                ph.prodh_unidades,
                ph.huni_resp,
                ph.indi_id,
                ph.acc_id,
                ph.mt_id,
                ph.or_id,
                prog.*
            FROM _producto_historial ph
            Inner Join indicador as i On i.indi_id=ph.indi_id
            Inner Join objetivos_regionales as ore On ore.or_id=ph.or_id
            INNER JOIN vista_temporalidad_form4_programado_uresp prog ON ph.prod_id = prog.prod_id
            -- Nota: Si opm tiene varios registros por objetivo, esto causa duplicados
            INNER JOIN objetivo_programado_mensual opm ON ore.pog_id = opm.pog_id
            INNER JOIN objetivo_gestion og ON og.og_id = opm.og_id
            INNER JOIN _acciones_estrategicas ae ON ae.acc_id = og.acc_id
            -- WHERE con paso de parámetros seguro (Bindings ?)
            WHERE ph.cite_id = '.$cite_id.'
              AND ph.tipo_mod = '.$tipo_mod.'
              AND ph.historial_activo != 0
            ORDER BY ph.prod_cod ASC';

        $query = $this->db->query($sql);
        return $query->result_array();
    }




    /*----- Lista de Operaciones - Nuevos -------*/
/*    public function operaciones_adicionados($cite_id){
        $sql = '
            select p.prod_id,p.com_id,p.prod_producto,p.prod_ppto,p.indi_id,p.prod_indicador,p.prod_linea_base, p.prod_meta,p.prod_fuente_verificacion,p.prod_unidades,p.prod_ponderacion,p.estado,p.prod_mod,
                p.prod_resultado,p.acc_id,p.prod_cod, p.prod_observacion,p.mt_id,p.or_id,i.indi_descripcion,
                ore.or_id,ore.or_codigo,og.og_id,og.og_codigo, ae.acc_id,ae.acc_codigo,oe.obj_codigo
                from _producto_add pa
                Inner Join _productos as p On pa.prod_id=p.prod_id
                Inner Join indicador as i On i.indi_id=p.indi_id
                Inner Join objetivos_regionales as ore On ore.or_id=p.or_id

                Inner Join objetivo_programado_mensual as opm On ore.pog_id=opm.pog_id
                Inner Join objetivo_gestion as og On og.og_id=opm.og_id
                Inner Join _acciones_estrategicas as ae On ae.acc_id=og.acc_id
                Inner Join _objetivos_estrategicos as oe On oe.obj_id=ae.obj_id
                
            where pa.cite_id='.$cite_id.'
            order by pa.proda_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/

    /*----- Lista de Operaciones - Modificados -------*/
/*    public function operaciones_modificados($cite_id){
        $sql = '
             select p.prod_id,p.com_id,p.prod_producto,p.prod_ppto,p.indi_id,p.prod_indicador,p.prod_linea_base, p.prod_meta,p.prod_fuente_verificacion,p.prod_unidades,p.prod_ponderacion,p.estado,p.prod_mod,
                p.prod_resultado,p.acc_id,p.prod_cod, p.prod_observacion,p.mt_id,p.or_id,i.indi_descripcion,
                ore.or_id,ore.or_codigo,og.og_id,og.og_codigo, ae.acc_id,ae.acc_codigo,oe.obj_codigo
                from _producto_modificado pm
                Inner Join _productos as p On pm.prod_id=p.prod_id
                Inner Join indicador as i On i.indi_id=p.indi_id
                Inner Join objetivos_regionales as ore On ore.or_id=p.or_id

                Inner Join objetivo_programado_mensual as opm On ore.pog_id=opm.pog_id
                Inner Join objetivo_gestion as og On og.og_id=opm.og_id
                Inner Join _acciones_estrategicas as ae On ae.acc_id=og.acc_id
                Inner Join _objetivos_estrategicos as oe On oe.obj_id=ae.obj_id
                where pm.cite_id='.$cite_id.' and p.estado!=\'3\'

                group by p.prod_id,p.com_id,p.prod_producto,p.prod_ppto,p.indi_id,p.prod_indicador,p.prod_linea_base, p.prod_meta,p.prod_fuente_verificacion,p.prod_unidades,p.prod_ponderacion,p.estado,p.prod_mod,
                p.prod_resultado,p.acc_id,p.prod_cod, p.prod_observacion,p.mt_id,p.or_id,i.indi_descripcion,
                ore.or_id,ore.or_codigo,og.og_id,og.og_codigo, ae.acc_id,ae.acc_codigo,oe.obj_codigo';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/

    /*----- Lista de Operaciones - eliminados -------*/
/*    public function operaciones_eliminados($cite_id){
        $sql = '
             select p.prod_id,p.com_id,p.prod_producto,p.prod_ppto,p.indi_id,p.prod_indicador,p.prod_linea_base, p.prod_meta,p.prod_fuente_verificacion,p.prod_unidades,p.prod_ponderacion,p.estado,p.prod_mod,
                p.prod_resultado,p.acc_id,p.prod_cod, p.prod_observacion,p.mt_id,p.or_id,i.indi_descripcion,
                ore.or_id,ore.or_codigo,og.og_id,og.og_codigo, ae.acc_id,ae.acc_codigo,oe.obj_codigo
                from _producto_delete pd
                 Inner Join _productos as p On pd.prod_id=p.prod_id
                 Inner Join indicador as i On i.indi_id=p.indi_id
                 Inner Join objetivos_regionales as ore On ore.or_id=p.or_id

                 Inner Join objetivo_programado_mensual as opm On ore.pog_id=opm.pog_id
                 Inner Join objetivo_gestion as og On og.og_id=opm.og_id
                 Inner Join _acciones_estrategicas as ae On ae.acc_id=og.acc_id
                 Inner Join _objetivos_estrategicos as oe On oe.obj_id=ae.obj_id
                where pd.cite_id='.$cite_id.'
                order by dlte_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }*/

    /*==== LISTA DE CITES GENERADOS =====*/

    /*---- Get cite Proyecto - Operaciones ----*/
    function list_cites_modpoa_form4($proy_id){
        $sql = 'SELECT *
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                where p.proy_id='.$proy_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and ci.cite_estado!=\'3\'
                order by ci.cite_id asc';

        /*if($this->gestion>2021){
            $sql = 'select *
                from lista_modificacion_form4('.$proy_id.','.$this->gestion.')
                where cite_activo=\'1\'';
        }
        else{
            $sql = 'select *
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                where p.proy_id='.$proy_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and ci.cite_estado!=\'3\'
                order by ci.cite_id asc';
        }*/
        

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*---- Get lista cite Generados a nivel de Regionales ----*/
    public function list_cites_modfis_regionales($dep_id,$mes_id){
        if($mes_id==0){ //// Nacional
            $sql = '
                select *
                FROM vlista_cite_modificacion_fisica
                WHERE dep_id='.$dep_id.' and g_id='.$this->gestion.'';
        }
        else{ //// Regional
            $sql = '
                select *
                FROM vlista_cite_modificacion_fisica
                WHERE dep_id='.$dep_id.' and mes='.$mes_id.' and g_id='.$this->gestion.'';
        }
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }



    //////// REPORTE CONSOLIDADO DE MODIFICACIONES 
    ////// PRODUCTOS (ACTIVIDADES)
    
    /*--- Lista de Items Generados por regional y mes (Productos) ---*/
    public function list_cites_generados_productos($dep_id,$mes_id){
        $sql = 'select ci.cite_id,c.com_id,extract(day from (ci.fecha_creacion))as dia, extract(month from (ci.fecha_creacion))as mes, extract(years from (ci.fecha_creacion))as gestion, ci.g_id,p.proy_id,p.dep_id,p.dist_id
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                where p.dep_id='.$dep_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and ci.cite_estado!=\'3\' and extract(month from (ci.fecha_creacion))='.$mes_id.' and ci.g_id='.$this->gestion.'
                order by ci.cite_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--- Lista de Items Generados por distrital y mes (Productos) ---*/
    public function list_cites_generados_operaciones_distrital($dist_id,$mes_id,$tp_id){
        $sql = 'select ci.cite_id,c.com_id,extract(day from (ci.fecha_creacion))as dia, extract(month from (ci.fecha_creacion))as mes, extract(years from (ci.fecha_creacion))as gestion, ci.g_id,p.proy_id,p.dep_id,p.dist_id
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                where p.dist_id='.$dist_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and ci.cite_estado!=\'3\' and extract(month from (ci.fecha_creacion))='.$mes_id.' and ci.g_id='.$this->gestion.' and p.tp_id='.$tp_id.'
                order by ci.cite_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--- Lista de Items Generados por regional y mes (Productos) ---*/
    public function list_cites_generados_operaciones_regional($dep_id,$mes_id,$tp_id){
        $sql = 'select ci.cite_id,c.com_id,extract(day from (ci.fecha_creacion))as dia, extract(month from (ci.fecha_creacion))as mes, extract(years from (ci.fecha_creacion))as gestion, ci.g_id,p.proy_id,p.dep_id,p.dist_id
                from cite_mod_fisica ci
                Inner Join funcionario as f On ci.fun_id=f.fun_id
                Inner Join _componentes as c On ci.com_id=c.com_id
                Inner Join _proyectofaseetapacomponente as pfe On pfe.pfec_id=c.pfec_id
                Inner Join _proyectos as p On p.proy_id=pfe.proy_id
                where p.dep_id='.$dep_id.' and pfe.pfec_estado=\'1\' and pfe.estado!=\'3\' and ci.cite_estado!=\'3\' and extract(month from (ci.fecha_creacion))='.$mes_id.' and ci.g_id='.$this->gestion.' and p.tp_id='.$tp_id.'
                order by ci.cite_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--- tipos de acciones - requerimientos ---*/
    public function numero_de_modificaciones_productos($cite_id,$tp_accion){
        //// 1 : Adicion
        //// 2 : Modificacion
        //// 3 : Eliminacion

        if($tp_accion==1){
            $sql = '
            select pa.cite_id
            from _producto_add pa
            Inner Join _productos as p On pa.prod_id=p.prod_id                
            where pa.cite_id='.$cite_id.' and pa.estado!=\'3\'
            group by pa.cite_id';
        }
        elseif ($tp_accion==2) {
           $sql = '
            select pm.cite_id
            from _producto_modificado pm
            Inner Join _productos as p On pm.prod_id=p.prod_id               
            where pm.cite_id='.$cite_id.' and p.estado!=\'3\' and pm.estado!=\'3\'
            group by pm.cite_id'; 
        }
        elseif ($tp_accion==3) {
            $sql = '
            select pd.cite_id
            from _producto_delete pd
            Inner Join _productos as p On pd.prod_id=p.prod_id               
            where pd.cite_id='.$cite_id.' and p.estado!=\'3\' and pd.estado!=\'3\'
            group by pd.cite_id'; 
        }
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}