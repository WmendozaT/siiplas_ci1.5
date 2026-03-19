<?php
class Model_modificacion extends CI_Model{
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

    /*----- Tabla Temporal operaciones ---*/
    public function list_temporal(){
        $sql = ' select *
                 from temporal_operaciones
                 order by proy_id,prod_id,com_id asc';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    //lista de organismo financiador
    /*========== BORRA DATOS DEL OBJETIVO PROGRAMADO GESTION ================*/
    public function delete_og_prog($o_id){ 
        $this->db->where('o_id', $o_id);
        $this->db->delete('ogestion_prog_mes'); 
    }
    /*=======================================================================*/
    /*=============== AGREGAR OBJETIVO GESTION  PROGRAMADO ==================*/
    public function add_og_prog($o_id,$m_id,$opm_fis)
    {
        $data = array(
            'o_id' => $o_id,
            'mes_id' => $m_id,
            'opm_fis' => $opm_fis,
        );
        $this->db->insert('ogestion_prog_mes',$data);
    }
    /*========================================================================*/
    /*=================== GET PRODUCTO TERMINAL ==============================*/
    public function get_pterminal($pt_id){
        $this->db->SELECT('p.*,i.*,f.fun_nombre,f.fun_paterno,f.fun_materno,(SELECT get_unidad(f.uni_id)) AS unidad,i.indi_abreviacion');
        $this->db->FROM('_productoterminal p');
        $this->db->JOIN('funcionario f', 'p.fun_id = f.fun_id','LEFT');
        $this->db->JOIN('indicador i', 'i.indi_id = p.indi_id','LEFT');
        $this->db->WHERE('p.pt_id', $pt_id);
        $query = $this->db->get();
        return $query->result_array();
    }

    /*============ LISTA DE PROGRAMADOS OBJETIVOS DE GESTION  ===============*/
    public function pt_prog_mensual($pt_id){
        $this->db->from('pt_prog_mes');
        $this->db->where('pt_id', $pt_id);
        $query = $this->db->get();
        return $query->result_array();
    }
    /*=======================================================================*/

    /*================= BORRA DATOS DEL PRODUCTO TERMINAL ===================*/
    public function delete_pt_prog($pt_id){ 
        $this->db->where('pt_id', $pt_id);
        $this->db->delete('pt_prog_mes'); 
    }
    /*======================================================================*/
    /*================ AGREGAR OBJETIVO GESTION  PROGRAMADO ================*/
    public function add_pt_prog($pt_id,$m_id,$ppm_fis){
        $data = array(
            'pt_id' => $pt_id,
            'mes_id' => $m_id,
            'ppm_fis' => $ppm_fis,
        );
        $this->db->insert('pt_prog_mes',$data);
    }
    /*=====================================================================*/

    /*------------------ get modificaciones ----------*/
    public function get_modificaciones($mod_id){
        $sql = 'select *
                from mod_operacion
                where mod_id='.$mod_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------------------- Suma Monto Programado insumo Producto --------------------------*/
    public function suma_monto_requerimientos_producto($prod_id){
        $sql = 'select sum(programado_total) as monto_programado
                from vproy_insumo_producto_programado
                where prod_id='.$prod_id.' and g_id='.$this->gestion.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------------------- Suma Monto Programado insumo Componente --------------------------*/
    public function suma_monto_requerimientos_componente($com_id){
        $sql = 'select sum(programado_total) as monto_programado
                from vproy_insumo_componente_programado
                where com_id='.$com_id.' and g_id='.$this->gestion.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Verif Get Insumo Modificado -------------*/
    function verif_get_insumo_modificado($ins_id){
        $sql = 'select *
            from _insumo_modificado 
            where ins_id='.$ins_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }
    
    /*------------------ Get Insumo Modificado -------------*/
    function get_insumo_modificado($insm_id){
        $sql = 'select *
            from _insumo_modificado 
            where insm_id='.$insm_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*-------- Get insumo programado certificado 2023 (VIGENTE) --------*/
    function get_iprog_cert($ipm_id){
        $sql = 'select *
                from cert_ifin_prog_mes cp
                where cp.ipm_id='.$ipm_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get insumo programado 2023 (VIGENTE) -------------*/
    function get_iprog($ifin_id,$mes_id){
        $sql = 'select *
                from ifin_prog_mes
                where ifin_id='.$ifin_id.' and mes_id='.$mes_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get insumo Eliminado -------------*/
    function get_delete_insumo($dlte_id){
        $sql = 'select *
                from _insumo_delete
                where dlte_id='.$dlte_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get insumo Cite (VIGENTE) 2023-------------*/
    function get_cite_insumo($insc_id){
        $sql = 'select *
                from _insumo_mod_cite im
                Inner Join funcionario as f On im.fun_id=f.fun_id
                where im.insc_id='.$insc_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- Get insumo adicionado -------*/
    function get_add_insumo($insa_id){
        $sql = 'select *
                from _insumo_add
                where insa_id='.$insa_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*---- Get cite Proyecto - Requerimientos ----*/
    function list_cites_requerimientos_proy($proy_id){
        $sql = 'select *
                from _insumo_mod_cite im
                Inner Join _proyectos as p On p.proy_id=im.proy_id
                Inner Join funcionario as f On f.fun_id=im.fun_id
                Inner Join _proyectofaseetapacomponente as pf On pf.proy_id=p.proy_id
                where im.insc_estado!=\'0\' and im.proy_id='.$proy_id.' and pf.pfec_estado=\'1\' and pf.estado!=\'3\' and im.g_id='.$this->gestion.'
                order by im.insc_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------- Cites para modificarlos --------------------------*/
    public function cite_add($insc_id){
        $sql = 'select *
                from _insumo_mod_cite i
                Inner Join _insumo_add as ia On ia.insc_id=i.insc_id
                where i.insc_id='.$insc_id.' and ia.estado!=\'3\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    public function ins_del($insc_id){
        $sql = 'select *
                from _insumo_mod_cite i
                Inner Join _insumo_delete as id On id.insc_id=i.insc_id
                where i.insc_id='.$insc_id.' and id.estado!=\'3\'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*------------------ Get Producto adicionado -------------*/
    function get_add_producto($proda_id){
        $sql = 'select *
                from _producto_add
                where proda_id='.$proda_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get Producto Eliminado -------------*/
    function get_delete_producto($dlte_id){
        $sql = 'select *
                from _producto_delete
                where dlte_id='.$dlte_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get Actividad Eliminado -------------*/
    function get_delete_actividad($dlte_id){
        $sql = 'select *
                from _actividad_delete
                where dlte_id='.$dlte_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get Actividad adicionado -------------*/
    function get_add_actividad($acta_id){
        $sql = 'select *
                from _actividad_add
                where acta_id='.$acta_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get Producto adicionado -------------*/
    function list_add_actividad($ope_id){
        $sql = 'select *
                from _actividad_add aa
                Inner Join vista_actividad as a On a.act_id=aa.act_id
                where aa.ope_id='.$ope_id.' and aa.estado!=\'3\'
                order by acta_id asc';
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*------------------ relacion act ins -----------------*/
    function rel_ins_act($ins_id){
        $sql = 'select *
                from _insumoactividad
                where ins_id='.$ins_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*------------------ relacion ins prod -------------*/
    function rel_ins_prod($ins_id){
        $sql = 'select *
                from _insumoproducto
                where ins_id='.$ins_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ relacion ins prod - recupera componente  -------------*/
    function proceso_rel_ins_prod($ins_id){
        $sql = 'select c.*
                from insumos i
                Inner Join _insumoproducto as ip On ip.ins_id=i.ins_id
                Inner Join _productos as p On p.prod_id=ip.prod_id
                Inner Join _componentes as c On c.com_id=p.com_id
                where ip.ins_id='.$ins_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ relacion ins com -------------*/
    function rel_ins_com($ins_id){
        $sql = 'select *
                from insumocomponente
                where ins_id='.$ins_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ relacion ins com -------------*/
    function proceso_rel_ins_com($ins_id){
        $sql = 'select c.*
                from insumos i
                Inner Join insumocomponente as ic On ic.ins_id=i.ins_id
                Inner Join _componentes as c On c.com_id=ic.com_id
                where ic.ins_id='.$ins_id.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*======== MOD OPERACIONES ======*/
    function cite_add_operacion($ope_id){
        $sql = 'select *
                from _ope_mod_cite o
                Inner Join _producto_add as pa On pa.ope_id=o.ope_id
                where o.ope_id='.$ope_id.' and pa.estado!=\'3\'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function cite_mod_operacion($ope_id){
        $sql = 'select *
                from _ope_mod_cite o
                Inner Join _producto_modificado as pm On pm.ope_id=o.ope_id
                where o.ope_id='.$ope_id.' and pm.estado!=\'3\'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    function cite_del_operacion($ope_id){
        $sql = 'select *
                from _ope_mod_cite o
                Inner Join _producto_delete as pd On pd.ope_id=o.ope_id
                where o.ope_id='.$ope_id.' and pd.estado!=\'3\'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    /*===============================*/

    /*------------------ Actividades Modificados -------------------*/
    public function actividades_modificados($ope_id){
        $sql = 'select a.act_id,a.act_actividad,tp.indi_descripcion,a.act_indicador,a.act_formula,a.act_linea_base,a.act_meta,a.act_fuente_verificacion,a.act_ponderacion
        from _actividad_modificado am
        Inner Join _actividades as a On am.act_id=a.act_id
        Inner Join indicador as tp On a.indi_id=tp.indi_id
        where am.ope_id='.$ope_id.' and am.estado!=\'3\'
        group by a.act_id,a.act_actividad,tp.indi_descripcion,a.act_indicador,a.act_formula,a.act_linea_base,a.act_meta,a.act_fuente_verificacion,a.act_ponderacion';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Actividades Eliminados -------------------*/
    public function actividades_eliminados($ope_id){
        $sql = 'select *
                from _actividad_delete ad
                Inner Join vista_actividad as a On a.act_id=ad.act_id
                where ad.ope_id='.$ope_id.' and ad.estado!=\'3\'
                order by ad.dlte_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Fases Modificados -------------------*/
    public function fasecomponente_modificados($ope_id){
        $sql = 'select *
                from _fase_modificado fm
                inner Join _proyectofaseetapacomponente as pf On pf.pfec_id=fm.pfec_id
                where fm.ope_id='.$ope_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Lista de Meses 2023 (VIGENTE)-------------------*/
    public function list_meses(){
        $sql = 'select *
                from mes
                order by m_id asc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*------------------ Get Mes -------------------*/
    public function get_mes($mes_id){
        $sql = 'select *
                from mes
                where m_id='.$mes_id.'';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*--------------------------- Reporte de Modificaciones ------------------------*/
    /*--Operaciones Onacional--*/
    public function modificaciones_operaciones_onacional($dep_id,$fecha1,$fecha2){
        $sql = '
            select *
            from vlista_modificaciones_operaciones
            where fecha BETWEEN \''.$fecha1.'\' AND \''.$fecha2.'\' and dep_id='.$dep_id.' and g_id='.$this->gestion.'
            order by ope_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*-- Numero de Modificaciones Operaciones Regionales --*/
    public function modificaciones_operaciones_regionales($dep_id,$mes_id){
        if($mes_id==0){
            $sql = '
                select *
                from vlista_modificaciones_operaciones
                where dep_id='.$dep_id.' and g_id='.$this->gestion.'';
        }
        else{
            $sql = '
                select *
                from vlista_modificaciones_operaciones
                where dep_id='.$dep_id.' and mes='.$mes_id.' and g_id='.$this->gestion.'';
        }
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--Requerimientos onacional--*/
    public function modificaciones_requerimientos_onacional($dep_id,$fecha1,$fecha2){
        $sql = '
            select * 
            from vlista_modificaciones_requerimiento 
            where fecha BETWEEN \''.$fecha1.'\' AND \''.$fecha2.'\' and dep_id='.$dep_id.' and g_id='.$this->gestion.'
            order by insc_id';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }
    /*--Requerimientos regionales--*/
    public function modificaciones_requerimientos_regionales($dep_id,$mes_id){
        if($mes_id==0){
            $sql = '
                select *
                from vlista_modificaciones_requerimiento
                where dep_id='.$dep_id.' and g_id='.$this->gestion.'';
        }
        else{
            $sql = '
                select *
                from vlista_modificaciones_requerimiento
                where dep_id='.$dep_id.' and mes='.$mes_id.' and g_id='.$this->gestion.'';
        }
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

   /*----- Modificaciones Presupuesto Operacion -------*/
    public function mod_presupuesto($cite,$pfec_id){
        $sql = '
            select fm.pttom_id,fm.ope_id,fm.estado,fm.pfec_ptto_fase as ini, ((pfec.pfec_ptto_fase)-(fm.pfec_ptto_fase)) as mod, pfec.pfec_ptto_fase as total, fm.tp_mod
            from _ptto_fase_modificado fm
            Inner Join _proyectofaseetapacomponente as pfec On pfec.pfec_id=fm.pfec_id
            where fm.pfec_id='.$pfec_id.' and ope_id='.$cite.' and fm.estado!=\'3\'
            order by pttom_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- VERIF NRO DE MOD REQUERIMIENTOS (2019) ---------*/
    public function verif_mod_req($dep_id){
        $sql = 'select *
                from mod_req_regionales
                where dep_id='.$dep_id.' and g_id='.$this->gestion.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*--------- VERIF NRO DE MOD OPERACIONES (2019) ---------*/
    public function verif_mod_ope($dep_id){
        $sql = 'select *
                from mod_ope_regionales
                where dep_id='.$dep_id.' and g_id='.$this->gestion.'';
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- Lista de Actividades - Modificados -------*/
    public function act_modificados($cite_id){
        $sql = '
            select *
            from vactividades_modificados am
            Inner Join _productos as p On p.prod_id=am.prod_id
            where am.ope_id='.$cite_id.' and am.estado!=\'3\'
            order by am.actm_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- Lista de Actividades - Eliminados -------*/
    public function act_eliminados($cite_id){
        $sql = '
            select *
            from vactividades_eliminados ae
            Inner Join _productos as p On p.prod_id=ae.prod_id
            where ae.ope_id='.$cite_id.' and ae.estado!=\'3\'
            order by ae.dlte_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*----- Lista de Partidas Padres Asignados (2023) vigente -----*/
    public function list_part_padres_asig($aper_id){
        $sql = '
            select par.par_id,par.par_codigo,par.par_nombre
            from partidas par 
            Inner Join 
            (
            select pg.sp_id, pg.par_id,p.par_depende,pg.aper_id
            from ptto_partidas_sigep pg
            Inner Join partidas as p On p.par_id=pg.par_id
            where pg.estado!=3 and pg.g_id='.$this->gestion.'
            order by pg.partida
            ) as sig On sig.par_depende=par.par_codigo
            where par.par_depende=\'0\' and par.par_id!=\'0\' and sig.aper_id='.$aper_id.'

            group by par.par_id,par.par_codigo,par.par_nombre
            order by par.par_id asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- Lista de Partidas Dependientes (2019) -----*/
    public function list_part_hijos_asig($aper_id){
        $sql = '
            select pg.par_id,pg.partida as par_codigo,p.par_nombre,p.par_depende,pg.importe
            from ptto_partidas_sigep pg
            Inner Join partidas as p On p.par_id=pg.par_id
            where pg.aper_id='.$aper_id.' and pg.estado!=\'3\' and pg.g_id='.$this->gestion.'
            order by pg.partida asc';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*-- lista de insumos que fueron certificados por producto --*/
    public function list_cert_poa_producto($prod_id){
        $sql = '
            select *
            from _productos p
            Inner Join _insumoproducto as iprod On iprod.prod_id=p.prod_id
            Inner Join insumos as i On i.ins_id=iprod.ins_id
            Inner Join certificacionpoadetalle as cpoa On cpoa.ins_id=i.ins_id
            where p.prod_id='.$prod_id.' and i.ins_estado!=\'3\'';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*==== TECHOS PRESUPUESTARIOS*/
    /*----- Lista de Cites Techo 2023 (VIGENTE) -------*/
    public function list_cites_techo($proy_id){
        $sql = '
            select *
            from lista_modificacion_techos_unidad('.$proy_id.','.$this->gestion.')
            order by cppto_id desc';

        $query = $this->db->query($sql);
        return $query->result_array();
    }


    /*==== REVERTIR EDICIONES */
    /*----- get requerimiento modificado -------*/
    public function get_datos_insumo_modificado($insm_id){
        $sql = '
            select *
            from insumo_modificado 
            where insm_id='.$insm_id.'';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }

    /*----- lista de requerimiento modificado -------*/
    public function lista_insumo_modificado($ins_id){
        $sql = '
            select *
            from insumo_modificado 
            where ins_id='.$ins_id.'';
        
        $query = $this->db->query($sql);
        return $query->result_array();
    }
}
