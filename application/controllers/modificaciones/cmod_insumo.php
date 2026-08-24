<?php
class Cmod_insumo extends CI_Controller {  
    public $rol = array('1' => '3','2' => '6','3' => '4'); 
    public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
            $this->load->model('Users_model','',true);
            $this->load->library('pdf2');
            $this->load->model('menu_modelo');
            $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
            $this->load->model('programacion/model_proyecto');
            $this->load->model('modificacion/model_modificacion');
            $this->load->model('modificacion/model_modrequerimiento'); /// Gestion 2020
            $this->load->model('ejecucion/model_certificacion'); /// Gestion 2020
            $this->load->model('programacion/model_faseetapa');
            $this->load->model('programacion/model_producto');
            $this->load->model('programacion/model_componente');
            $this->load->model('programacion/insumos/minsumos');
            $this->load->model('mantenimiento/model_partidas');
            $this->load->model('mantenimiento/model_ptto_sigep');
            $this->load->library('security');
            $this->gestion = $this->session->userData('gestion'); /// Gestion
            $this->fun_id = $this->session->userData('fun_id'); /// Fun id
            $this->rol_id = $this->session->userData('rol_id'); /// Rol Id
            $this->tp_adm = $this->session->userData('tp_adm');
            $this->conf_mod_ope = $this->session->userData('conf_mod_ope');
            $this->conf_mod_req = $this->session->userData('conf_mod_req');
            $this->fecha_entrada = strtotime("20-09-2021 00:00:00");
            $this->load->library('modificacionpoa');
        }
        else{
          $this->session->sess_destroy();
          redirect('/','refresh');
        }
    }

    /*----- cite Modificacion POA x Unidad  ------*/
    public function cite_UnidadResponsable($proy_id){
      /// tp 0: Modificacion POA
      /// tp 1: Modificacion POA (Reversion de saldos)
      $data['menu']=$this->menu(3); //// genera menu
      $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
      $data['tp_mod']=0;
      $data['titulo_cite']='';

      if(count($data['proyecto'])!=0){
        $data['titulo']='<h1><small>'.$data['proyecto'][0]['aper_programa'].' '.$data['proyecto'][0]['aper_proyecto'].' '.$data['proyecto'][0]['aper_actividad'].' - '.$data['proyecto'][0]['tipo'].' '.$data['proyecto'][0]['proy_nombre'].' '.$data['proyecto'][0]['abrev'].'</small></h1>';
        $data['tabla']=$this->modificacionpoa->lista_unidades_responsables($data['proyecto']);
        $data['loading']=$this->modificacionpoa->loading('INGRESANDO A FORMULARIO DE MODIFICACIÓN POA');

        $this->load->view('admin/modificacion/requerimientos/cite_servicio', $data); 
      }
      else{
        redirect('mod/list_top');
      }
    }


    //// Limpiar ITEMS ELIMINAODS
    public function limpiar_insumos_eliminados($com_id){
      $componente=$this->model_componente->get_componente($com_id,$this->gestion);
      $requerimientos_del = $this->model_modrequerimiento->lista_requerimientos_eliminados($com_id);
      foreach ($requerimientos_del as $row) {
          $get_insumo_add=$this->model_modrequerimiento->get_insumo_adicionado_id($row['ins_id']); /// Add
          $get_insumo_mod=$this->model_modrequerimiento->get_insumo_modificado_id($row['ins_id']); /// Update

          if(count($get_insumo_add)==0 & count($get_insumo_mod)==0){
              /*-------- DELETE INSUMO PROGRAMADO --------*/  
                $this->db->where('ins_id', $row['ins_id']);
                $this->db->delete('temporalidad_prog_insumo');
              /*------------------------------------------*/
              /*-------- DELETE INSUMO --------*/
                $this->db->where('prod_id', $row['prod_id']);
                $this->db->where('ins_id', $row['ins_id']);
                $this->db->delete('_insumoproducto');
              /*--------------------------------*/
              /*-------- DELETE INSUMO  --------*/  
                $this->db->where('ins_id', $row['ins_id']);
                $this->db->delete('insumos');
              /*--------------------------------*/
          }
      }

      redirect(site_url("").'/mod/form5/'.$componente[0]['proy_id'].'');
    }


    //// get lista de partidas dependientes (nuevo registro)
    public function get_partidas_dependientes_nuevo(){
        // Validamos la legitimidad asíncrona de la solicitud (Evita accesos directos por URL)
        if($this->input->is_ajax_request() && $this->input->post()){
            
            $post    = $this->input->post();
            $par_id  = intval($this->security->xss_clean($post['par_id'])); // ID del Grupo Padre
            $cite_id = intval($this->security->xss_clean($post['cite_id'])); 
            $g_id    = intval($this->gestion); // Año contable activo de sesión

            if ($par_id <= 0 || $cite_id <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificadores contextuales numéricos vacíos.'));
                exit;
            }

            // Recuperamos los datos máster del CITE de modificación física
            $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); 
            
            if(empty($cite)){
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'El CITE de requerimientos no se encuentra registrado en el sistema.'));
                exit;
            }

            $aper_id = intval($cite[0]['aper_id']);
            $tipo_modificacion = intval($cite[0]['tipo_modificacion']);

            // 🌟 REPARADO CORE 2: Consultas masivas reestructuradas mediante Query Binding nativo seguro
            if($tipo_modificacion === 0){ /// CASO A: Partidas e Insumos Regulares del POA
                $sql = "SELECT pg.par_id, pg.partida AS par_codigo, p.par_nombre
                        FROM public.ptto_partidas_sigep pg
                        INNER JOIN public.partidas AS p ON p.par_id = pg.par_id
                        WHERE pg.aper_id = ? 
                          AND pg.estado != '3' 
                          AND pg.g_id = ? 
                          AND p.par_depende = ?
                        ORDER BY pg.partida ASC";
                $bind_params = array($aper_id, $g_id, $par_id);
            } 
            else { //// CASO B: Partidas y Saldos de Reversión Masiva
                $sql = "SELECT par.par_id, par.par_codigo, par.par_nombre
                        FROM public.lista_partidas_revertidas(?) pr
                        INNER JOIN public.partidas AS par ON par.par_id = pr.par_id
                        WHERE pr.aper_id = ? 
                          AND par.par_depende = ?
                        GROUP BY par.par_id, par.par_codigo, par.par_nombre, par.par_depende
                        ORDER BY par.par_codigo ASC";
                $bind_params = array($g_id, $aper_id, $par_id);
            }

            $query_dataset = $this->db->query($sql, $bind_params)->result_array();

            // 🌟 REPARADO CORE 3: Inicialización de la variable acumuladora para evitar Notice de PHP
            $salida = '<option value="">SELECCIONE PARTIDA DEPENDIENTE...</option>';
            
            foreach ($query_dataset as $row) {
                $salida .= '<option value="' . intval($row['par_id']) . '">' . htmlspecialchars($row['par_codigo'], ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars(strtoupper($row['par_nombre']), ENT_QUOTES, 'UTF-8') . '</option>';
            }

            // Ensamble del payload de respuesta atómico
            $result = array(
                'status'                => 'success',
                'respuesta'             => 'correcto', // Total compatibilidad con el response.respuesta == 'correcto' de tu JS
                'partidas_dependientes' => $salida
            );
  
            // Saneamiento definitivo de buffers: barremos remanentes para garantizar salida JSON pura
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode($result);
            exit; // Congela la ejecución física resguardando la integridad de la tubería de red

        } else {
            show_404();
        }
    }



    public function combo_partidas_hijos_asignados(){
        $salida = "";
        $id_pais = $_POST["elegido"]; /// codigo Partida
        $aper_id = $_POST["aper"]; /// aper id
        $tp=$_POST["tp"]; /// tp
        $id = $_POST["id"]; /// cite id | ins id

        if($tp==0){
          $cite=$this->model_modrequerimiento->get_cite_insumo($id); /// Datos cite
          $tipo_mod=$cite[0]['tipo_modificacion'];
        }
        else{
          $insumo= $this->model_insumo->get_requerimiento($id); /// Datos requerimientos productos
          $tipo_mod=$insumo[0]['ins_tipo_modificacion'];
        }
        

        if($tipo_mod==0){
          $combog = pg_query('
            select pg.par_id,pg.partida as par_codigo,p.par_nombre,p.par_depende,pg.importe
            from ptto_partidas_sigep pg
            Inner Join partidas as p On p.par_id=pg.par_id
            where pg.aper_id='.$aper_id.' and pg.estado!=\'3\' and pg.g_id='.$this->gestion.' and p.par_depende='.$id_pais.'
            order by pg.partida asc');
        }
        else{
          $combog = pg_query('
            select par.par_id,par.par_codigo,par.par_nombre,par.par_depende,SUM(pr.presupuesto_revertido) ppto_revertido
            from lista_partidas_revertidas('.$this->gestion.') pr
            Inner Join partidas as par On par.par_id=pr.par_id
            where pr.aper_id='.$aper_id.' and par.par_depende='.$id_pais.'
            group by par.par_id,par.par_codigo,par.par_nombre,par.par_depende
            order by par.par_codigo asc');
        }

        $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE PARTIDA', 'cp1252', 'UTF-8') . "</option>";
        while ($sql_p = pg_fetch_row($combog)) {
            $salida .= "<option value='" . $sql_p[0] . "'>" .$sql_p[1]." - ".$sql_p[2] . "</option>";
        }
        echo $salida;
    }



    /*------- Valida Cite Para Modificacion -------*/
    public function valida_cite_modificacion(){
      if ($this->input->post()) {
          $post = $this->input->post();
          $proy_id = $this->security->xss_clean($post['proy_id']); /// Proy id 
          //$cite = preg_replace('/[^\w\s]/u', '', htmlspecialchars($this->security->xss_clean($post['cite']), ENT_QUOTES, 'UTF-8')); /// Cite
          $cite=$this->security->xss_clean($post['cite']);
          $fecha = $this->security->xss_clean($post['fm']); /// Fecha
          $com_id = $this->security->xss_clean($post['com_id']); /// Com id
          $tp_mod = $this->security->xss_clean($post['tp_mod']); /// tipo mod
          $prod_id = $this->security->xss_clean($post['prod_id']); /// prod id
          $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id);

          if(count($proyecto)!=0){
            /*--- GUARDANDO CITE MODIFICADO (con estado inactivo) ---*/
            $data_to_store = array(
              'cite_nota' => strtoupper($cite),
              'cite_fecha' => $fecha,
              'com_id' => $com_id,
              'prod_id' => $prod_id, //// id de la actividad alienado a una unidad responsable en el caso de los programas Bolsas
              'tipo_modificacion' => $tp_mod,
              'fun_id' => $this->fun_id,
              'g_id' => $this->gestion,
              'num_ip' => $this->input->ip_address(), 
              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
            );
            $this->db->insert('cite_mod_requerimientos',$data_to_store);
            $cite_id=$this->db->insert_id();
            /*-------------------------------------------------------*/

            if(count($this->model_modrequerimiento->get_cite_insumo($cite_id))==1){
              redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
            }
            else{
              $this->session->set_flashdata('danger','ERROR AL REGISTRAR CITE');
              redirect(site_url("").'/mod/form5/'.$proy_id.'');
            }
          }
          else{ 
             $this->session->set_flashdata('danger','ERROR AL REGISTRAR CITE');
             redirect(site_url("").'/mod/form5/'.$proy_id.'');
          }

          
      } else {
          show_404();
      }
    }

    /*----- LISTA REQUERIMIENTOS 2027 ------*/
    public function mis_requerimientos($cite_id){
      $data['menu']=$this->menu(3); //// genera menu
      $data['cite'] = $this->model_modrequerimiento->get_cite_insumo($cite_id);

      if(count($data['cite'])!=0){
       // $proyecto = $this->model_proyecto->get_id_proyecto($data['cite'][0]['proy_id']); /// Proyecto de Inversion
        $data['cabecera']=$this->cabecera_formulario_mod5($data['cite']);
        $data['opciones']=$this->opciones_formulario_mod5($data['cite']);
        $data['style']=$this->style();
        //$data['loading_form']=$this->loagind_form();
        $data['loading']=$this->modificacionpoa->loading('ACTUALIZANDO LISTADO');

        $data['tabla']=$this->modificacionpoa->formN5_mod_lista_requerimientos_ConTemporalidad($data['cite']);  /// 2026
      
          /*if(count($this->model_modrequerimiento->lista_requerimientos($data['cite'][0]['com_id'],$data['cite'][0]['tipo_modificacion']))>250){
            
            $data['tabla']=$this->modificacionpoa->formN5_mod_lista_requerimientos_SinTemporalidad($data['cite']);  /// 2026 -> cargado rapido sin temporalidad
          }
          else{
            $data['tabla']=$this->modificacionpoa->formN5_mod_lista_requerimientos_ConTemporalidad($data['cite']);  /// 2026
          }*/

         // $data['tabla']=$this->modificacionpoa->modificar_requerimientos_auxiliar($data['cite']);  /// 2026 -> cargado rapido sin temporalidad
          $data['part_padres'] = $this->model_modificacion->list_part_padres_asig($data['cite'][0]['aper_id']);//partidas padres
          if($data['cite'][0]['tipo_modificacion']==1){
            $data['part_padres'] = $this->model_ptto_sigep->lista_partidas_padres_revertidos($data['cite'][0]['aper_id']);//partidas padres REVERTIDO
          }

          $data['lista']=$this->tipo_lista_ope_act($data['cite']); /// ALINEADO A ACTIVIDAD (FORM 4)
          $data['modal_migracion']=$this->modificacionpoa->modal_migracion_form5x_modpoa($cite_id);
          $this->load->view('admin/modificacion/requerimientos/list_requerimientos', $data);
         
      }
      else{
        redirect('mod/list_top');
      }

    }



  public function loagind_form(){
  $tabla='';
  $tabla.='
    <style>
    #loading_req {
    padding: 30px;
    text-align: center;
  }

  .loader-dots {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 8px;
      margin-bottom: 10px;
  }

  .dot {
      width: 12px;
      height: 12px;
      background-color: #3498db; /* Color principal */
      border-radius: 50%;
      animation: bounce 0.6s infinite alternate;
  }

  /* Retraso para el efecto de ola */
  .dot:nth-child(2) { animation-delay: 0.2s; }
  .dot:nth-child(3) { animation-delay: 0.4s; }

  @keyframes bounce {
      from { transform: translateY(0); opacity: 1; }
      to { transform: translateY(-15px); opacity: 0.3; }
  }

  .loading-text {
      font-family: "Arial", sans-serif;
      font-size: 12px;
      font-weight: bold;
      color: #777;
      text-transform: uppercase;
  }
    </style>
     <div id="loading_req" style="display:none;">
        <div class="loader-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        <div class="loading-text">OBTENIENDO INFORMACIÓN...</div>
    </div>';

  return $tabla;
  }



    /*-------- GET CUADRO COMPARATIVO ASIGNADO-POA 2026 --------*/
    public function get_cuadro_comparativo_ptto() {
        // Verificamos petición AJAX y POST
        if (!$this->input->is_ajax_request() || !$this->input->post()) {
            show_404();
            return;
        }

        $cite_id = $this->security->xss_clean($this->input->post('cite_id'));
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);

        // Validar que el CITE existe
        if (!empty($cite)) {
            $proy_id = $cite[0]['proy_id'];
            
            // Generar la URL del reporte
            $url_reporte = site_url("prog/reporte_ptto_consolidado_comparativo_programa/{$proy_id}");
            
            // Construir el iframe (puedes agregar estilos aquí mismo)
            $tabla = '<iframe src="'.$url_reporte.'" width="100%" height="800px" frameborder="0"></iframe>';

            $result = array(
                'respuesta' => 'correcto',
                'tabla' => $tabla,
            );
        } else {
            $result = array(
                'respuesta' => 'error',
                'mensaje' => 'No se encontraron datos para el CITE proporcionado.'
            );
        }

        echo json_encode($result);
    }



  /*----- CABECERA FORMULARIO ------*/
  public function cabecera_formulario_mod5($cite){
    $monto=$this->modificacionpoa->ppto($cite);
    $tabla='';
    $tabla.='
      <section id="widget-grid" class="well" title="'.$cite[0]['proy_id'].'">
          '.$this->modificacionpoa->titulo_cabecera($cite,1).'';
          $tabla.='
          <button type="button" 
                                class="btn btn-sm btn-exportar-excel-fila" 
                                onclick="exportarExcelConLoading(this, ' . $cite[0]['com_id'] . ')" 
                                title="EXPORTAR CONSOLIDADO DE LA UNIDAD EN EXCEL"
                                style="padding: 5px 11px; background: #16a34a; border: 1px solid #15803d; color: #ffffff; border-radius: 3px; display: inline-flex; align-items: center; justify-content: center; height: 35px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); cursor: pointer; font-family: Arial, sans-serif; font-weight: 600; font-size: 11px; transition: all 0.15s ease;"
                                onmouseover="this.style.background=\'#15803d\'; this.style.borderColor=\'#166534\';"
                                onmouseout="this.style.background=\'#16a34a\'; this.style.borderColor=\'#15803d\';">
                            <span class="txt-btn-excel-fila" style="display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa fa-file-excel-o" style="font-size: 12px; color: #ffffff;"></i> Extraer POA en Archivo Excel
                            </span>
                        </button>
      </section>';
    return $tabla;
  }

  /*----- OPCIONES FORMULARIO 2026 ------*/
  public function opciones_formulario_mod5($cite){
    $monto=$this->modificacionpoa->ppto($cite);
    $tabla='';

      $tabla.='
        <div class="well">';
          if($cite[0]['cite_activo']==1){
            if($cite[0]['cite_estado']==1){
              $tabla.='<button type="button" class="btn btn-success btn-sm btn-block" data-toggle="modal" data-target="#modal_cerrar" title="MODIFICACION CERRADA"><i class="fa fa-save"></i><b>&nbsp;MODIFICACI&Oacute;N CONCLUIDA</b></button><br>';
            }
            else{
              $tabla.='<button type="button" class="btn btn-warning btn-sm btn-block" data-toggle="modal" data-target="#modal_cerrar" title="CONCLUIR MODIFICACION"><i class="fa fa-save"></i><b>&nbsp;CERRAR MODIFICACIÓN</b></button><br>';
            }

            $tabla.='
            <a href="javascript:abreVentana(\''.site_url("").'/mod/rep_mod_financiera/'.$cite[0]['cite_id'].'\');" title="IMPRIMIR REPORTE DE MODIFICACION POA">
              <button class="btn btn-default btn-lg btn-block">
                <i class="fa fa-file-pdf-o"></i><b>&nbsp;IMPRIMIR MODIFICACIÓN POA</b>
              </button>
            </a><br>';
          }

          $tabla.='
          <button type="button" id="btsubmit" onclick="valida_eliminar()" class="btn btn-danger btn-sm btn-block">
            <i class="glyphicon glyphicon-trash"></i> &nbsp;ELIMINAR INSUMOS (SELECCIONADOS)
          </button>
        </div>';

    return $tabla;
  }


    /*--- Get Lista de Requerimientos por Actividad (form4)---*/
    public function get_items_x_form4(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $prod_id = $this->security->xss_clean($post['prod_id']);
        $form4=$this->model_producto->get_producto_id($prod_id); /// get form4
        $requerimientos=$this->model_insumo->lista_insumos_prod($prod_id); /// lista de requerimientos para Programas Bolsas
        $tabla='';
        $tabla.='
        <script src = "'.base_url().'mis_js/programacion/programacion/tablas.js"></script>

        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="jarviswidget jarviswidget-color-darken" >
            <header>
                <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                <h2 class="font-md"><strong></strong></h2>  
            </header>
          <div>
            <div class="widget-body no-padding">
              <table id="dt_basic" class="table table-bordered" style="width:100%;">
                <thead>
                  <tr style="text-align:center;">
                    <th>#</th>
                    <th>ACT.</th>
                    <th>PARTIDA</th>
                    <th style="text-align:center;">REQUERIMIENTO</th>
                    <th style="text-align:center;">UNIDAD</th>
                    <th style="text-align:center;">CANT.</th>
                    <th style="text-align:center;">PRECIO</th>
                    <th style="text-align:center;">PPTO. TOTAL</th>
                    <th style="text-align:center;">PPTO. CERT.</th>
                    <th style="text-align:center;">ENE.</th>
                    <th style="text-align:center;">FEB.</th>
                    <th style="text-align:center;">MAR.</th>
                    <th style="text-align:center;">ABR.</th>
                    <th style="text-align:center;">MAY.</th>
                    <th style="text-align:center;">JUN.</th>
                    <th style="text-align:center;">JUL.</th>
                    <th style="text-align:center;">AGO.</th>
                    <th style="text-align:center;">SEPT.</th>
                    <th style="text-align:center;">OCT.</th>
                    <th style="text-align:center;">NOV.</th>
                    <th style="text-align:center;">DIC.</th>
                    <th style="text-align:center;">OBSERVACION</th>
                  </tr>
                </thead>
                <tbody>';
                $nro=0;
                $suma_ppto=0;$suma_cert=0;
                foreach($requerimientos as $row){
                  $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
                  $suma_ppto=$suma_ppto+$row['ins_costo_total'];
                  $suma_cert=$suma_cert+$row['ins_monto_certificado'];
                  $nro++;
                  $color='';
                  if($row['ins_ejec_cpoa']==1){
                    $color='bgcolor="#FCE8E8"';
                  }
                  $tabla.='
                  <tr '.$color.'>
                    <td title="'.$row['ins_id'].'">'.$nro.'</td>
                    <td style="font-size:20px; text-align:center"><b>'.$row['prod_cod'].'</b></td>
                    <td style="font-size:20px; text-align:center"><b>'.$row['par_codigo'].'</b></td>
                    <td style="font-size:10.7px;">'.$row['ins_detalle'].'</td>
                    <td style="font-size:10.7px;">'.$row['ins_unidad_medida'].'</td>
                    <td style="font-size:10.7px; text-align:right;">'.round($row['ins_cant_requerida'],2).'</td>
                    <td style="font-size:10.7px; text-align:right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>
                    <td style="font-size:10.7px; text-align:right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>
                    <td style="font-size:12px; text-align:right; color:#FDFDFD;" bgcolor="green"><b>'.number_format($row['ins_monto_certificado'], 2, ',', '.').'</b></td>';
                    if(count($prog)!=0){
                      for ($i=1; $i <=12 ; $i++) { 
                        $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                      }
                    }
                    else{
                      for ($i=1; $i <=12 ; $i++) { 
                        $tabla .= '<td style="width: 4.5%; text-align: right;">0</td>';
                      }
                    }
                    
                    $tabla.='
                    <td style="font-size:10.7px;">'.$row['ins_observacion'].'</td>
                  </tr>';
                }
                $tabla.='
                <tbody>
                <tr>
                  <td colspan=7></td>
                  <td align=right><b>'.number_format($suma_ppto, 2, ',', '.').'</b></td>
                  <td align=right><b>'.number_format($suma_cert, 2, ',', '.').'</b></td>
                  <td colspan=13></td>
                </tr>
              </table>
            </div>
          </div>
          </div>
        </article>';



        $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$tabla,
        );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }


    /// ---- STYLE -----
    public function style(){
      $tabla='';

      $tabla.='   
      <style>
        table{font-size: 12px;
            width: 100%;
            max-width:1550px;;
            overflow-x: scroll;
        }
        th{
            padding: 1.4px;
            text-align: center;
            font-size: 11px;
        }
        #mdialTamanio{
            width: 80% !important;
        }
        #comparativo{
          width: 50% !important;
        }
        #dialog_subir{
          width: 40% !important;
        }
          input[type="checkbox"] {
          display:inline-block;
          width:25px;
          height:25px;
          margin:-1px 4px 0 0;
          vertical-align:middle;
          cursor:pointer;
        }
    </style>';

      return $tabla;
    }


    /*---- tipo lista : Operacion-Actividad ----*/
    public function tipo_lista_ope_act($cite){
      $form4=$this->model_producto->lista_productos($cite[0]['com_id']);
      $tabla='';
        $tabla.='
          <section class="col col-3">
            <label class="label"><b>ALINEACI&Oacute;N FORM 4 (ACTIVIDAD) '.$this->gestion.'</b> (prod id: '.$cite[0]['prod_id'].')</label>
            <label class="input">';
              if($cite[0]['por_id']==0){ /// Programas Normales
                $tabla.='
                <select class="form-control" id="dato_id" name="dato_id" title="SELECCIONE ACTIVIDAD">
                  <option value="">Seleccione Actividad</option>';
                  foreach($form4 as $row){ 
                    $tabla.='<option value="'.$row['prod_id'].'">'.$row['or_codigo'].'/'.$row['prod_cod'].'.- '.$row['prod_producto'].'</option>';
                  } 
                  $tabla.='      
                </select>';
              }
              else{ /// Programas Bolsas
                $tabla.='
                <select class="form-control" id="dato_id" name="dato_id" title="SELECCIONE ACTIVIDAD">';
                  foreach($form4 as $row){
                    if($cite[0]['prod_id']==$row['prod_id']){
                      $tabla.='<option value="'.$row['prod_id'].'" selected>'.$row['unidad_responsable'].')</option>';
                    }
                    else{
                      $tabla.='<option value="'.$row['prod_id'].'">'.$row['unidad_responsable'].')</option>';
                    }
                    
                  } 
                  $tabla.='      
                </select>';
              }
            $tabla.='
              
            </label>
          </section>';

      return $tabla;
    }




    /*--- VALIDA ADD REQUERIMIENTO (2026) ---*/
    public function valida_add_insumo(){
      if($this->input->post()) {
        $post = $this->input->post();
        $cite_id = $this->security->xss_clean($post['cite_id']); /// cite id
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// datos cite
        $tipo_modificacion=$cite[0]['tipo_modificacion'];

        $detalle = $this->security->xss_clean($post['ins_detalle']); /// detalle  
        $cantidad = $this->security->xss_clean($post['ins_cantidad']); /// cantidad
        $costo_unitario = $this->security->xss_clean($post['ins_costo_u']); /// costo unitario
        $costo_total = $this->security->xss_clean($post['costo']); /// costo Total
        $um_id = $this->security->xss_clean($post['ins_um']); /// Unidad de medida
        $partida = $this->security->xss_clean($post['partida_id']); /// partida id
        $observacion = $this->security->xss_clean($post['ins_observacion']); /// Observacion
        //$observacion = preg_replace('/[^\w\s]/u', '', $this->security->xss_clean($post['ins_observacion'])); /// observacion  
        $id = $this->security->xss_clean($post['dato_id']); /// Alineacion id Producto, Actividad
        $producto=$this->model_producto->get_producto_id($id); /// Get producto
       // $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);
        $proyecto = $this->model_proyecto->get_id_proyecto($cite[0]['proy_id']); /// DATOS DEL PROYECTO
        $umedida=$this->model_insumo->get_unidadmedida($um_id);

          $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
          'ins_codigo' => $this->session->userdata("name").'/REQ/'.$this->gestion, /// Codigo Insumo
          'ins_fecha_requerimiento' => date('d/m/Y'), /// Fecha de Requerimiento
          'ins_detalle' => strtoupper($detalle), /// Insumo Detalle
          'ins_cant_requerida' => round($cantidad,0), /// Cantidad Requerida
          'ins_costo_unitario' => $costo_unitario, /// Costo Unitario
          'ins_costo_total' => $costo_total, /// Costo Total
          'ins_unidad_medida' => $umedida[0]['um_descripcion'], /// Insumo Unidad de Medida
          'ins_gestion' => $this->gestion, /// Insumo gestion
          'par_id' => $partida, /// Partidas
          'ins_tipo' => 1, /// Ins Tipo
          'ins_observacion' => strtoupper($observacion), /// Observacion
          'ins_tipo_modificacion' => $tipo_modificacion, /// tipo modificacion
          'fun_id' => $this->fun_id, /// Funcionario
          'aper_id' => $proyecto[0]['aper_id'], /// aper id
          'com_id' => $producto[0]['com_id'], /// com id 
          'form4_cod' => $producto[0]['prod_cod'], /// aper id
          'ins_mod' => 2, /// mod
          'num_ip' => $this->input->ip_address(), 
          'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
          );
          $this->db->insert('insumos', $data_to_store); ///// Guardar en Tabla Insumos 
          $ins_id=$this->db->insert_id();

          /*-----------------------------------------------*/
            $data_to_store2 = array( ///// Tabla InsumoProducto
              'prod_id' => $id, /// prod id
              'ins_id' => $ins_id, /// ins_id
            );
            $this->db->insert('_insumoproducto', $data_to_store2);
            /*---------------------------------------------*/

          /*------------ PARA LA GESTION 2020 ---------*/
          for ($i=1; $i <=12 ; $i++) {
            $pfin=$this->security->xss_clean($post['m'.$i]);
            if($pfin!=0){
                if(count($this->model_certificacion->get_insumo_programado_mes($ins_id,$i))==0){
                  $data_to_store4 = array( 
                    'ins_id' => $ins_id, /// Id Insumo
                    'mes_id' => $i, /// Mes 
                    'ipm_fis' => $pfin, /// Valor mes
                    'g_id' => $this->gestion, /// Gestion 
                  );
                  $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                }
            }
          }
          /*------------------------------------------*/

          /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
          if($this->copia_insumo($cite_id,$ins_id,1)){ /// inserta historial reporte
            /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
              $this->update_activo_modificacion($cite_id);
              $this->session->set_flashdata('success','EL REQUERIMIENTO SE REGISTRO CORRECTAMENTE :)');
            /*--------------------------------------*/
          }
          else{
            $this->session->set_flashdata('danger','EL REQUERIMIENTO NOSE REGISTRO CORRECTAMENTE, VERIFIQUE DATOS :(');
          }

          redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
      }
      else{
        echo "Error en el Registro !!!";
      }
    }



    /*----- UPDATE ESTADO ACTIVO DE LA MODIFICACION ------*/
    function update_activo_modificacion($cite_id){
      $update_cite= array(
        'cite_activo' => 1,
        'tp_reporte' => 1, /// nuevo reporte
        'fun_id'=>$this->fun_id
      );
      $this->db->where('cite_id', $cite_id);
      $this->db->update('cite_mod_requerimientos', $this->security->xss_clean($update_cite));
    }


     /*======== VALIDA UPDATE REQUERIMIENTO (2027) ========*/
     public function valida_update_insumo(){
      if($this->input->post()) {
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']); /// Ins id
        $cite_id = $this->security->xss_clean($post['cite_id']); /// cite id


        // 1. VALIDACIÓN: Si el ID está vacío o no es numérico
        if (empty($ins_id) || !is_numeric($ins_id) || empty($cite_id)) {
            $this->session->set_flashdata('danger', 'ERROR: DATOS INCOMPLETOS PARA MODIFICAR EL REQUERIMIENTO');
            // Redirige al listado principal o a la página anterior
            redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
            return; // Detiene la ejecución
        }

        // 2. VALIDACIÓN DE EXISTENCIA: Verificar si los datos realmente existen en la BD
        $insumo = $this->model_insumo->get_requerimiento($ins_id);
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);

        if (empty($insumo) || empty($cite)) {
            $this->session->set_flashdata('danger', 'ERROR: EL REQUERIMIENTO NO EXISTE O FUE ELIMINADO');
            redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
            return;
        }


        if($insumo[0]['certificado_total']!=0){ /// Cuando ya esta certificado
          $detalle = $insumo[0]['ins_detalle']; /// detalle
        //  $costo_unitario = $insumo[0]['ins_costo_unitario']; /// costo unitario
          $unidad = $insumo[0]['ins_unidad_medida']; /// Unidad de medida
          $partida = $insumo[0]['par_id']; /// costo unitario
          $observacion = $insumo[0]['ins_observacion']; /// Observacion
        }
        else{ /// Aun no esta certificado
          $detalle = $this->security->xss_clean($post['detalle']); /// detalle
          $unidad = $this->security->xss_clean($post['umedida']); /// Unidad de medida
          $partida = $this->security->xss_clean($post['par_hijo']); /// costo unitario
          $partida = $this->security->xss_clean($post['par_hijo']); /// costo unitario
          $observacion = $this->security->xss_clean($post['observacion']); /// detalle
        }
        
        $cantidad = $this->security->xss_clean($post['cantidad']); /// cantidad
        $costo_unitario = $this->security->xss_clean($post['costou']); /// costo unitario /// temporal
        $costo_total = $this->security->xss_clean($post['costot']); /// costo Total
        $id = $this->security->xss_clean($post['id']); /// id : prod,act
        $producto=$this->model_producto->get_producto_id($id); /// Get producto


          if($this->registra_insumo_original($cite_id,$ins_id)){
            
            $update_ins= array(
              'ins_cant_requerida' => $cantidad,
              'ins_costo_unitario' => $costo_unitario,
              'ins_costo_total' => $costo_total,
              'ins_detalle' => $detalle,
              'par_id' => $partida, /// Partidas
              'ins_unidad_medida' => $unidad,
              'ins_observacion' => $observacion,
              'fun_id' => $this->fun_id,
              'com_id' => $cite[0]['com_id'], /// com id 
              'form4_cod' => $producto[0]['prod_cod'], /// aper id
              'ins_mod' => 2, /// mod
              'ins_estado'=> 2, /// mod
              'num_ip' => $this->input->ip_address(), 
              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
            );
            $this->db->where('ins_id', $ins_id);
            $this->db->update('insumos', $this->security->xss_clean($update_ins));

              for ($i=1; $i <=12 ; $i++) {
                if(count($this->model_certificacion->get_insumo_programado_certificado_mes($ins_id,$i))==0){
                  if(!is_null ($post['mm'.$i])){
                    $verif_mes=$this->model_modrequerimiento->get_mes_item($ins_id,$i);
                    if(count($verif_mes)!=0){
                      $pfin=$this->security->xss_clean($post['mm'.$i]);
                      if($pfin==0){
                        /*----------------- ELIMINA IFIN PROG MES---------------*/
                          $this->db->where('ins_id', $ins_id);
                          $this->db->where('mes_id', $i);
                          $this->db->delete('temporalidad_prog_insumo');
                        /*------------------------------------------------------*/
                      }
                      else{
                        /*----------------- UPDATE IFIN PROG MES---------------*/
                          $update_ifin = array(
                            'ipm_fis' => $pfin
                          );
                          $this->db->where('mes_id', $i);
                          $this->db->where('ins_id', $ins_id);
                          $this->db->update('temporalidad_prog_insumo', $update_ifin);
                        /*------------------------------------------------------*/
                      }

                    }
                    else{
                      $pfin=$this->security->xss_clean($post['mm'.$i]);
                      if($pfin!=0){
                          $data_to_store4 = array( 
                            'ins_id' => $ins_id, /// Id Insumo
                            'mes_id' => $i, /// Mes 
                            'g_id' => $this->gestion, /// gestion 
                            'ipm_fis' => $pfin, /// Valor mes
                            'g_id' => $this->gestion, /// Gestion 
                          );
                          $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                      }
                    }
                  }
                }
              }

              $update_proy = array(
                'prod_id' => $id,
              );
              $this->db->where('ins_id', $ins_id);
              $this->db->update('_insumoproducto', $update_proy);

              $this->copia_insumo($cite_id,$ins_id,2); /// historial de modificaciones para el reporte

              /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
              $this->update_activo_modificacion($cite_id);
              /*--------------------------------------*/

            $this->session->set_flashdata('success','EL REQUERIMIENTO SE MODIFICO CORRECTAMENTE :)');
            redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
        }
        else{
          echo "Error al Copiar Datos ...";
        }

      } else {
          show_404();
      }
    }

    /// Verifica Programacion de Temporalidad Inicial Total 2023
    public function valida_update_temporalidad_inicial_total_unidad($cite,$proyecto){

      if($proyecto[0]['tp_id']==1){ /// Solo Para Proyectos de Inversion
            $temporalidad_inicial=$this->model_insumo->temporalidad_inicial_total_unidad($proyecto[0]['proy_id']);

            if(count($temporalidad_inicial)==0){
              ///--- registrando temporalidad inicial
              $temporalidad_insumo=$this->model_insumo->list_temporalidad_programado_unidad($proyecto[0]['aper_id']);

              for ($i=1; $i <=12 ; $i++) { 
                $data_to_store = array( 
                  'proy_id' => $proyecto[0]['proy_id'],
                  'aper_id' => $proyecto[0]['aper_id'],
                  'mes_id' => $i,
                  'temp_fis' => $temporalidad_insumo[0]['mes'.$i],
                  'fun_id' => $this->fun_id,
                  //'cite_id' => $cite[0]['cite_id'],
                  );
                $this->db->insert('temporalidad_inicial_total_insumo', $data_to_store);  
              }
            }
      }

    }




    /*--- VALIDA DATOS DEL REQUERIMIENTO CERTIFICADO (2020) ---*/
    public function valida_update_insumo_cpoa(){
      if ($this->input->post()) {
          $post = $this->input->post();

          $ins_id = $this->security->xss_clean($post['ins_id']); /// ins_id
          $cpoaa_id = $this->security->xss_clean($post['cpoaa_id']); /// cpoaa_id de la anulacion

          $cert_editado=$this->model_certificacion->get_cert_poa_editado($cpoaa_id); /// Datos de la Certificacion Anulado
          $cpoa=$this->model_certificacion->get_datos_certificacion_poa($cert_editado[0]['cpoa_id']); /// Datos de la Certificacion POA
          $detalle_cert=$this->model_certificacion->get_certificado_poa_detalle($cpoa[0]['cpoa_id'],$ins_id); /// item certificado

          $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos requerimientos 
          if($this->registra_insumo_original($cert_editado[0]['cite_id'],$ins_id)){
              ///------ cambiando de estado de certificacion poa la temporalidad
              $get_list_temp_prog=$this->model_certificacion->get_list_cert_temporalidad_prog_insumo($detalle_cert[0]['cpoad_id']);

              foreach($get_list_temp_prog as $row){
                $datos_temp=$this->model_certificacion->get_id_insumo_programado_mes($row['tins_id']);
              //  $suma_cert=$suma_cert+$datos_temp[0]['ipm_fis'];

                $update_ins= array(
                  'ins_monto_certificado' => ($insumo[0]['ins_monto_certificado']-$datos_temp[0]['ipm_fis']),
                  'fun_id' => $this->fun_id
                );
                $this->db->where('ins_id', $ins_id);
                $this->db->update('insumos', $this->security->xss_clean($update_ins));

                /// Actualizando el estado de la temporalidad
                $update_temp = array(
                  'estado_cert' => 0
                );
                $this->db->where('tins_id', $row['tins_id']);
                $this->db->update('temporalidad_prog_insumo', $update_temp);
              }
              ///---------------------------------------------------------------

              /*-------- Elimina Los items certificados --------*/
              $this->db->where('cpoad_id', $detalle_cert[0]['cpoad_id']);
              $this->db->delete('cert_temporalidad_prog_insumo');
              /*------------------------------------------------*/

            if(count($this->model_certificacion->verif_insumo_certificado($ins_id))==1){
                $detalle = $this->security->xss_clean($post['detalle']); /// Detalle
                $unidad = $this->security->xss_clean($post['umedida']);  /// Unidad de Medida

                $update_ins= array(
                'ins_detalle' => $detalle,
                'ins_unidad_medida' => $unidad,
                'fun_id' => $this->fun_id,
                'ins_mod' => 2, /// mod
                'ins_estado'=> 2, /// mod
                'num_ip' => $this->input->ip_address(), 
                'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                );
              $this->db->where('ins_id', $ins_id);
              $this->db->update('insumos', $this->security->xss_clean($update_ins));
            }


            for ($i=1; $i <=12 ; $i++) {
                if(count($this->model_certificacion->get_insumo_programado_certificado_mes($ins_id,$i))==0){
                  if(!is_null ($post['mm'.$i])){
                    $verif_mes=$this->model_modrequerimiento->get_mes_item($ins_id,$i);
                    if(count($verif_mes)!=0){
                      $pfin=$this->security->xss_clean($post['mm'.$i]);
                      if($pfin==0){
                        /*----------------- ELIMINA IFIN PROG MES---------------*/
                          $this->db->where('ins_id', $ins_id);
                          $this->db->where('mes_id', $i);
                          $this->db->delete('temporalidad_prog_insumo');
                        /*------------------------------------------------------*/
                      }
                      else{
                        /*----------------- UPDATE IFIN PROG MES---------------*/
                          $update_ifin = array(
                            'ipm_fis' => $pfin
                          );
                          $this->db->where('mes_id', $i);
                          $this->db->where('ins_id', $ins_id);
                          $this->db->update('temporalidad_prog_insumo', $update_ifin);
                        /*------------------------------------------------------*/
                      }

                    }
                    else{
                      $pfin=$this->security->xss_clean($post['mm'.$i]);
                      if($pfin!=0){
                          $data_to_store4 = array( 
                            'ins_id' => $ins_id, /// Id Insumo
                            'mes_id' => $i, /// Mes 
                            'ipm_fis' => $pfin, /// Valor mes
                            'g_id' => $this->gestion, /// Gestion 
                          );
                          $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                      }
                    }
                  }
                }
              }


              $this->copia_insumo($cert_editado[0]['cite_id'],$ins_id,2); /// historial de modificaciones para el reporte

              /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
              $this->update_activo_modificacion($cert_editado[0]['cite_id']);
              /*--------------------------------------*/


              $this->session->set_flashdata('success','SE MODIFICO CORRECTAMENTE');
              redirect('cert/edit_certificacion/'.$cpoaa_id.'');

          }
          else{
            echo "Error al Copiar Datos ...";
          }

      } else {
          show_404();
      }
    }



    /*--- MIGRACION DE AJUSTE DE ITEMS CERTIFICACION POA  ---*/
    function importar_ajuste_cpoa(){
      if ($this->input->post()) {
        $post = $this->input->post();
          $cpoaa_id = $this->security->xss_clean($post['cpoaa_id']); /// cpoaa_id
          $cert_editado=$this->model_certificacion->get_cert_poa_editado($cpoaa_id); /// Datos de la Certificacion Anulado
          $cpoa=$this->model_certificacion->get_datos_certificacion_poa($cert_editado[0]['cpoa_id']); /// Datos de la Certificacion POA

          $tipo = $_FILES['archivo']['type'];
          $tamanio = $_FILES['archivo']['size'];
          $archivotmp = $_FILES['archivo']['tmp_name'];

          $filename = $_FILES["archivo"]["name"];
          $file_basename = substr($filename, 0, strripos($filename, '.'));
          $file_ext = substr($filename, strripos($filename, '.'));
          $allowed_file_types = array('.csv');

          if (in_array($file_ext, $allowed_file_types) && ($tamanio < 90000000)) {
            /*------------------- Migrando ---------------*/
            $lineas = file($archivotmp);
            $i=0;
            $nro=0;
            $guardado=0;
            $no_guardado=0;

            foreach ($lineas as $linea_num => $linea){
              if($i != 0){
                $datos = explode(";",$linea);
                if(count($datos)==19){
                  $ins_id = intval(trim($datos[0])); //// ins_id
                  $detalle = strval(utf8_encode(trim($datos[2]))); //// Detalle Requerimiento
                  $unidad = strval(utf8_encode(trim($datos[3]))); //// Unidad de Medida
                  
                  $detalle_cert=$this->model_certificacion->get_certificado_poa_detalle($cpoa[0]['cpoa_id'],$ins_id); /// item certificado

                    if(count($detalle_cert)!=0){
                      if($this->registra_insumo_original($cert_editado[0]['cite_id'],$ins_id)){
                          $guardado++;
                          //if(count($this->model_certificacion->verif_insumo_certificado($ins_id))==1){

                              $update_ins= array(
                              'ins_detalle' => $detalle,
                              'ins_unidad_medida' => $unidad,
                              'fun_id' => $this->fun_id,
                              'ins_mod' => 2, /// mod
                              'ins_estado'=> 2, /// mod
                              'num_ip' => $this->input->ip_address(), 
                              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                              );
                            $this->db->where('ins_id', $ins_id);
                            $this->db->update('insumos', $this->security->xss_clean($update_ins));
                        //  }

                            $this->copia_insumo($cert_editado[0]['cite_id'],$ins_id,2); /// historial de modificaciones para el reporte

                            /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
                            $this->update_activo_modificacion($cert_editado[0]['cite_id']);
                            /*--------------------------------------*/
                      }
                    }

                } /// end dimension (22)
              } /// i!=0

              $i++;

            }


            $this->session->set_flashdata('success','SE REGISTRARON '.$guardado.' REQUERIMIENTOS');
            redirect('cert/edit_certificacion/'.$cpoaa_id.'');
          }
          else{
            $this->session->set_flashdata('danger','SELECCIONE ARCHIVO ');
            redirect('prog/list_requerimiento/'.$cpoaa_id.'');
          }
      }
      else{
        echo "Error !!";
      }
    }



    /*------ ELIMINAR REQUERIMIENTO ------*/
    function delete_requerimiento(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
         $post = $this->input->post();
          $cite_id = $post['cite_id']; /// Cite Id
          $ins_id = $post['ins_id']; /// Insumo Id
          $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);
   
          if($this->copia_insumo($cite_id,$ins_id,3)){

            /*--- Update estado del Insumo ---*/
            $update_ins = array(
              'ins_estado' => 3, /// 3 : Eliminado
              'ins_mod' => 2, /// 2 : Modulo Modificaciones
              'aper_id' => 0, /// 2 : aper
              'com_id' => 0, /// 2 : com_id
              'form4_cod' => 0, /// 2 : cod. formulario n4
              'num_ip' => $this->input->ip_address(), 
              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
              'fun_id' => $this->fun_id
              );
            $this->db->where('ins_id', $ins_id);
            $this->db->update('insumos', $update_ins);
            /*------------------------------- -*/

              /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
                $this->update_activo_modificacion($cite_id);
              /*--------------------------------------*/

              $result = array(
                'respuesta' => 'correcto'
              );
          }
          else{
            $result = array(
              'respuesta' => 'error'
            );
          }

          echo json_encode($result);
      }
    }

    /*-- ELIMINAR VARIOS REQUERIMIENTOS SELECCIONADOS --*/
    public function delete_select_requerimientos(){
      if ($this->input->post()) {
        $post = $this->input->post();
        $cite_id = $this->security->xss_clean($post['cite_id']);
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);
        $si=0;$no=0;
        if (!empty($_POST["ins"]) && is_array($_POST["ins"]) ) {
          foreach ( array_keys($_POST["ins"]) as $como){
            if($this->copia_insumo($cite_id,$_POST["ins"][$como],3)){

            /*--- Update estado del Insumo ---*/
            $update_ins = array(
              'ins_estado' => 3, /// 3 : Eliminado
              'ins_mod' => 2, /// 2 : Modulo Modificaciones
              'aper_id' => 0, /// 2 : aper
              'com_id' => 0, /// 2 : com_id
              'form4_cod' => 0, /// 2 : cod. formulario n4
              'num_ip' => $this->input->ip_address(), 
              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
              'fun_id' => $this->fun_id
              );
            $this->db->where('ins_id', $_POST["ins"][$como]);
            $this->db->update('insumos', $update_ins);
            /*------------------------------- -*/

            /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
              $this->update_activo_modificacion($cite_id);
            /*--------------------------------------*/

              if(count($this->model_insumo->get_requerimiento($_POST["ins"][$como]))==0){
                $si++;
              }
              else{
                $no++;
              }
            }
            else{
              $no++;
            }
          }
        }

        $this->session->set_flashdata('success','SE ELIMINARON : '.$si.' REQUERIMIENTOS');
        redirect(site_url("").'/mod/list_requerimientos/'.$cite_id.'');
      }
      else{
        echo "Error !!!";
      }
    }


    /*------------- REPORTE MODIFICACION DE REQUERIMIENTOS -------------*/
    public function reporte_modificacion_financiera($cite_id){
    $data['cite'] = $this->model_modrequerimiento->get_cite_insumo($cite_id);
    
    if(count($data['cite']) != 0){ 
        $cabecera_modpoa = $this->modificacionpoa->cabecera_modpoa($data['cite'], 2);

        if($data['cite'][0]['tp_reporte'] == 0){ 
            $items_modificados = $this->modificacionpoa->items_modificados_form5($cite_id); 
        } else {
            $items_modificados = $this->modificacionpoa->items_modificados_form5_historial($cite_id, 1); 
        }
        
        $pie_mod = $this->modificacionpoa->pie_modpoa($data['cite'], $data['cite'][0]['cite_codigo']);
        $data['pie_rep'] = 'MOD_POA_FORM5_' . $data['cite'][0]['cite_nota'] . ' - ' . $data['cite'][0]['tipo_subactividad'] . ' ' . $data['cite'][0]['com_componente'] . ' - ' . $data['cite'][0]['proy_nombre'] . ' ' . $data['cite'][0]['abrev'] . '/' . $this->gestion;

        // Configuración de la página en orientación horizontal (paysage) como solicita tu etiqueta <page>
        $data['informacion'] = '
        <page orientation="paysage" backtop="73mm" backbottom="26mm" backleft="2.6mm" backright="2.6mm" pagegroup="new">
          <page_header>
              <br><div class="verde"></div>
              ' . $cabecera_modpoa . '
          </page_header>
          <page_footer>
              ' . $pie_mod . '
          </page_footer>
          ' . $items_modificados . '
        </page>';

        // 1. Capturamos el HTML estructurado de la vista en una variable
        $html_reporte = $this->load->view('admin/modificacion/moperaciones/reporte_modificacion_poa_form4', $data, true); 

        // 2. Limpieza radical del búfer de CodeIgniter para que Chrome no rechace el PDF
        if (ob_get_length()) ob_clean();

        // 3. Importación segura del motor conversor usando la ruta física del servidor
        require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
        
        try {
            // Inicializamos en orientación horizontal ('L' de Landscape / Paysage) para que coincida con tu diseño
            $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
            $html2pdf->pdf->SetDisplayMode('fullpage');
            $html2pdf->writeHTML($html_reporte);
            
            // 4. Enviamos el flujo binario limpio directo al visor de Chrome
            $html2pdf->Output($data['pie_rep'] . '.pdf', 'I');
        }
        catch(HTML2PDF_exception $e) {
            echo "Error al compilar el reporte: " . $e;
        }
        exit;
    } else {
        echo "Error !!! El CITE especificado no contiene registros.";
    }
  }



    //// CONSOLIDADO FORMULARIO N5 POR MESES
  public function consolidado_form5_mensual($proy_id, $mes) {
      // 1. Limpieza radical preliminar del búfer de salida para proteger el JSON/PDF
      if (ob_get_length()) ob_clean();

      $get_mes = $this->model_modrequerimiento->get_mes($mes);
      $cites_mod5 = $this->model_modrequerimiento->list_cites_requerimientos_proy_x_mes($proy_id, $mes);

      // Validación de cortesía: Si el mes no tiene modificaciones registradas, frenamos el proceso
      if (empty($cites_mod5)) {
          echo "<h3>⚠️ Información SIIPLAS: No se encontraron modificaciones financieras consolidadas para el mes seleccionado.</h3>";
          return;
      }

      $proyecto = $this->model_proyecto->get_UnidadOrganizacional($cites_mod5[0]['proy_id']); 
      
      // Configuración del pie de reporte unificado
      $data['pie_rep'] = $get_mes[0]['m_descripcion'] . ' ' . $this->gestion . ' - CONSOLIDADO_MOD_POA_FORM5 - ' . $proyecto[0]['tipo_adm'] . ' ' . $proyecto[0]['act_descripcion'] . ' ' . $proyecto[0]['abrev'];
      
      $tabla = '';
      // 2. BUCLE EVOLUTIVO: Acumula las páginas de todos los CITEs del mes en la variable $tabla
      foreach ($cites_mod5 as $row) {
          $cite = $this->model_modrequerimiento->get_cite_insumo($row['cite_id']);
          
          if (count($cite) != 0) {
              $cabecera_modpoa = $this->modificacionpoa->cabecera_modpoa($cite, 2);
              
              if ($cite[0]['tp_reporte'] == 0) { 
                  $items_modificados = $this->modificacionpoa->items_modificados_form5($row['cite_id']); 
              } else {
                  $items_modificados = $this->modificacionpoa->items_modificados_form5_historial($row['cite_id'], 1); 
              }

              $pie_mod = $this->modificacionpoa->pie_modpoa($cite, $row['cite_codigo']);
          
              $tabla .= '
              <page orientation="paysage" backtop="73mm" backbottom="30mm" backleft="2.6mm" backright="2.6mm" pagegroup="new">
                <page_header>
                    <br><div class="verde"></div>
                    ' . $cabecera_modpoa . '
                </page_header>
                <page_footer>
                    ' . $pie_mod . '
                </page_footer>
                ' . $items_modificados . '
              </page> ';
          }
      }

      $data['informacion'] = $tabla;

      // 3. CAPTURA ASÍNCRONA: Guardamos el HTML de la subvista reutilizando tu archivo limpio
      $html_reporte = $this->load->view('admin/modificacion/moperaciones/reporte_modificacion_poa_form4', $data, true); 

      // 4. BIODETECTOR: Vaciamos el búfer de CodeIgniter para que Chrome no lance el error de carga de PDF
      if (ob_get_length()) ob_clean();

      // 5. COMPILACIÓN DIRECTA DESDE LA RUTA FÍSICA FCPATH
      require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
      
      try {
          // Configuramos en Landscape ('L') y tamaño Carta (Letter) con codificación UTF-8
          $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
          $html2pdf->pdf->SetDisplayMode('fullpage');
          $html2pdf->writeHTML($html_reporte);
          
          // 6. ENVIAMOS EL ARCHIVO BINARIO COMPILADO DIRECTAMENTE A GOOGLE CHROME
          $html2pdf->Output($data['pie_rep'] . '.pdf', 'I');
      }
      catch(HTML2PDF_exception $e) {
          echo "Error al compilar el reporte mensual consolidado de la CNS: " . $e;
      }
      exit; // Forzamos el cierre estricto del proceso
  }




  /*------- LISTA DE REQUERIMIENTOS MODIFICADOS (2020) -------*/
  public function rep_requerimiento($cite_id){
    $tabla ='';
    $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); // Datos Cite
    $requerimientos_add = $this->model_modrequerimiento->list_requerimientos_adicionados($cite_id);
    
    if(count($requerimientos_add)!=0){
      $tabla.='<div style="font-size: 12px;font-family: Arial;">ITEMS AGREGADOS ('.count($requerimientos_add).')</div>';
      $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
      $tabla.='<thead>';
      $tabla.='<tr class="modo1" align="center">';
        $tabla.='<th style="width:1%;background-color: #1c7368; color: #FFFFFF">#</th>';
        $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>ACT.</th>';
        $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
        $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
        $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
        $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='<th style="width:7.5%;background-color: #1c7368; color: #FFFFFF">OBSERVACIONES</th>';
      $tabla.='</tr>';
      $tabla.='</thead>';
      $tabla.='<tbody>';
      $nro=0;
      $monto=0;
      foreach ($requerimientos_add as $row){
        $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
        $nro++;
        $tabla.='<tr class="modo1">';
          $tabla.='<td style="width: 1%; text-align: center;" style="height:11px;">'.$nro.'</td>';
          $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
          $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
          $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
          $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
          $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
          if(count($prog)!=0){
            for ($i=1; $i <=12 ; $i++) { 
              $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
            }
          }
          else{
            for ($i=1; $i <=12 ; $i++) { 
              $tabla.='<td style="width: 4.5%; text-align: right;" bgcolor=red>-</td>';
            }
          }
          $tabla.='<td style="width: 7.5%; text-align: left;">'.$row['ins_observacion'].'</td>';
        $tabla.='</tr>';
        $monto=$monto+$row['ins_costo_total'];
      }
      $tabla.='</tbody>
        <tr class="modo1">
          <td style="height:10px;" colspan=7></td>
          <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
          <td colspan=13></td>
        </tr>
      </table><br>';
    }
    

    $requerimientos_mod = $this->model_modrequerimiento->list_requerimientos_modificados($cite_id);
    if(count($requerimientos_mod)!=0){
      $tabla.='<div style="font-size: 12px;font-family: Arial;">ITEMS MODIFICADOS ('.count($requerimientos_mod).')</div>';
      $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
      $tabla.='<thead>';
      $tabla.='<tr class="modo1" style="text-align: center;">';
        $tabla.='<th style="width:1%;background-color: #1c7368; color: #FFFFFF">#</th>';
        $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>ACT.</th>';
        $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
        $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
        $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
        $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='<th style="width:7.5%;background-color: #1c7368; color: #FFFFFF">OBSERVACIONES</th>';
      $tabla.='</tr>';
      $tabla.='</thead>';
      $tabla.='<tbody>';
      $nro=0;
      $monto=0;
      foreach ($requerimientos_mod as $row){
        $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
        $nro++;
        $tabla.='<tr class="modo1">';
          $tabla.='<td style="width: 1%; text-align: center;" style="height:11px;">'.$nro.'</td>';
          $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
          $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
          $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
          $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
          $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
          if(count($prog)!=0){
            for ($i=1; $i <=12 ; $i++) { 
              $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
            }
          }
          else{
            for ($i=1; $i <=12 ; $i++) { 
              $tabla.='<td style="width: 4.5%; text-align: right;" border=red>-</td>';
            }
          }
          $tabla.='<td style="width: 7.5%; text-align: left;">'.$row['ins_observacion'].'</td>';
        $tabla.='</tr>';
        $monto=$monto+$row['ins_costo_total'];
      }
      $tabla.='</tbody>
        <tr class="modo1">
          <td style="height:10px;" colspan=7></td>
          <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
          <td colspan=13></td>
        </tr>
      </table><br>';
    }
    
    $requerimientos_del = $this->model_modrequerimiento->list_requerimientos_eliminados($cite_id);
    if(count($requerimientos_del)!=0){
      $tabla.='<div style="font-size: 12px;font-family: Arial;">ITEMS ELIMINADOS ('.count($requerimientos_del).')</div>';
      $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
      $tabla.='<thead>';
      $tabla.='<tr class="modo1" style="text-align: center;">';
        $tabla.='<th style="width:1.3%;background-color: #1c7368; color: #FFFFFF">#</th>';
        $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>OPE.</th>';
        $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
        $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
        $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
        $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
        $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='<th style="width:7.5%;background-color: #1c7368; color: #FFFFFF">OBSERVACIONES</th>';
      $tabla.='</tr>';
      $tabla.='</thead>';
      $tabla.='<tbody>';
      $nro=0;
      $monto=0;
      foreach ($requerimientos_del as $row){
        $nro++;
        $tabla.='<tr class="modo1">';
          $tabla.='<td style="width: 1.3%; text-align: center;" style="height:11px;">'.$nro.'</td>';
          $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
          $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
          $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
          $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
          $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
          $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
          for ($i=1; $i <=12 ; $i++) { 
            $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($row['mes'.$i], 2, ',', '.') . '</td>';
          }
        $tabla.='<td style="width: 7.5%; text-align: left;">'.$row['ins_observacion'].'</td>';
        $tabla.='</tr>';
        $monto=$monto+$row['ins_costo_total'];
      }
      $tabla.='</tbody>
        <tr class="modo1">
          <td style="height:10px;" colspan=7></td>
          <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
          <td colspan=13></td>
        </tr>
      </table><br>';
    }

    $tabla.='';
    $tabla.='<br>
    <div style="font-size: 8px;font-family: Arial;">
    En atención a requerimiento de su unidad, comunicamos a usted que se ha procedido a efectivizar la modificación solicitada, toda vez que:<br>

    &nbsp;&nbsp;&nbsp;a)&nbsp;&nbsp;No compromete u obstaculiza el cumplimiento de los objetivos previstos en la gestión fiscal.<br>
    &nbsp;&nbsp;&nbsp;b)&nbsp;&nbsp;No vulnera o contraviene disposiciones legales.<br>
    &nbsp;&nbsp;&nbsp;c)&nbsp;&nbsp;No genera obligaciones o deudas por las modificaciones efectuadas.<br>
    &nbsp;&nbsp;&nbsp;d)&nbsp;&nbsp;No compromete el pago de obligaciones previstas en el presupuesto.
    </div>';
    return $tabla;
  }


    /*------- LISTA DE REQUERIMIENTOS MODIFICADOS (UPDATE)(2020-2021-2022) -------*/
    //----------------------------- rep mod poa - vigente 2023
    public function rep_requerimiento_update($cite_id){
      $tabla ='';
      $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); // Datos Cite
      $requerimientos_add = $this->model_modrequerimiento->list_requerimientos_adicionados($cite_id);
      
      if(count($requerimientos_add)!=0){

        $tabla.='<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-darken">
                      <header>
                        <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                          <h2 class="font-md"><strong>ITEMS AGREGADOS ('.count($requerimientos_add).')</strong></h2>  
                      </header>
                    <div>
                    <div class="widget-body no-padding">';
        $tabla.='<table id="dt_basic1" class="table1 table-bordered" style="width:100%;" border="0.2">';
        $tabla.='<thead>';
        $tabla.='<tr class="modo1" align="center">';
          $tabla.='<th style="width:1.3%;background-color: #1c7368; color: #FFFFFF">#</th>';
          $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>ACT.</th>';
          $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
          $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
          $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
          $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='</tr>';
        $tabla.='</thead>';
        $tabla.='<tbody>';
        $nro=0;
        $monto=0;
        foreach ($requerimientos_add as $row){
          $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
          $nro++;
          $tabla.='<tr class="modo1">';
            $tabla.='<td style="width: 1.3%; text-align: center;" style="height:11px;">'.$nro.'</td>';
            $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
            $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
            $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
            $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
            $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
            if(count($prog)!=0){
              for ($i=1; $i <=12 ; $i++) { 
                $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
              }
            }
            else{
              for ($i=1; $i <=12 ; $i++) { 
                $tabla.='<td style="width: 4.5%; text-align: right;" bgcolor=red>-</td>';
              }
            }
          $tabla.='</tr>';
          $monto=$monto+$row['ins_costo_total'];
        }
        $tabla.='</tbody>
          <tr class="modo1">
            <td style="height:11px;" colspan=7></td>
            <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
            <td colspan=13></td>
          </tr>
        </table>
        </div>
      </div>
      </div>
      </article><br>';
      }
      

      $requerimientos_mod = $this->model_modrequerimiento->list_requerimientos_modificados($cite_id);
      if(count($requerimientos_mod)!=0){

        $tabla.='<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-darken">
                      <header>
                        <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                          <h2 class="font-md"><strong>ITEMS MODIFICADOS ('.count($requerimientos_mod).')</strong></h2>  
                      </header>
                    <div>
                    <div class="widget-body no-padding">';
        $tabla.='<table id="dt_basic" class="table table-bordered" style="width:100%;" border="0.2">';
        $tabla.='<thead>';
        $tabla.='<tr class="modo1" style="text-align: center;">';
          $tabla.='<th style="width:1.3%;background-color: #1c7368; color: #FFFFFF">#</th>';
          $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>ACT.</th>';
          $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
          $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
          $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
          $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='</tr>';
        $tabla.='</thead>';
        $tabla.='<tbody>';
        $nro=0;
        $monto=0;
        foreach ($requerimientos_mod as $row){
          $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
          $nro++;
          $tabla.='<tr class="modo1">';
            $tabla.='<td style="width: 1.3%; text-align: center; height:15px;" title='.$row['ins_id'].'>'.$nro.'</td>';
            $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
            $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
            $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
            $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
            $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
            if(count($prog)!=0){
              for ($i=1; $i <=12 ; $i++) { 
                $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
              }
            }
            else{
              for ($i=1; $i <=12 ; $i++) { 
                $tabla.='<td style="width: 4.5%; text-align: right;" bgcolor=red>-</td>';
              }
            }
          $tabla.='</tr>';
          $monto=$monto+$row['ins_costo_total'];
        }
        $tabla.='</tbody>
          <tr class="modo1">
            <td style="height:11px;" colspan=7></td>
            <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
            <td colspan=13></td>
          </tr>
        </table>
        </div>
      </div>
      </div>
      </article><br>';
      }
      
      $requerimientos_del = $this->model_modrequerimiento->list_requerimientos_eliminados($cite_id);
      if(count($requerimientos_del)!=0){

        $tabla.='<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <div class="jarviswidget jarviswidget-color-darken">
              <header>
                <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                  <h2 class="font-md"><strong>ITEMS ELIMINADOS ('.count($requerimientos_del).')</strong></h2>  
              </header>
            <div>
            <div class="widget-body no-padding">';
        $tabla.='<table id="dt_basic3" class="table1 table-bordered" style="width:100%;" border="0.2">';
        $tabla.='<thead>';
        $tabla.='<tr class="modo1" style="text-align: center;">';
          $tabla.='<th style="width:1.3%;background-color: #1c7368; color: #FFFFFF;height:45px;">#</th>';
          $tabla.='<th style="width:2.5%;background-color: #1c7368; color: #FFFFFF">COD.<br>ACT.</th>';
          $tabla.='<th style="width:3.5%;background-color: #1c7368; color: #FFFFFF">PARTIDA</th>';
          $tabla.='<th style="width:12%;background-color: #1c7368; color: #FFFFFF">DETALLE REQUERIMIENTO</th>';
          $tabla.='<th style="width:4%;background-color: #1c7368; color: #FFFFFF">UNIDAD<br>MEDIDA</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">CANTIDAD</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">UNITARIO</th>';
          $tabla.='<th style="width:6%;background-color: #1c7368; color: #FFFFFF">COSTO TOTAL</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ENE.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">FEB.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">ABR.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">MAY.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUN.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">JUL.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">AGO.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">SEPT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">OCT.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">NOV.</th>';
          $tabla.='<th style="width:4.5%;background-color: #1c7368; color: #FFFFFF">DIC.</th>';
        $tabla.='</tr>';
        $tabla.='</thead>';
        $tabla.='<tbody>';
        $nro=0;
        $monto=0;
        foreach ($requerimientos_del as $row){
          $nro++;
          $tabla.='<tr class="modo1">';
            $tabla.='<td style="width: 1.3%; text-align: center;height:8px;">'.$nro.'</td>';
            $tabla.='<td style="width: 2.5%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
            $tabla.='<td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>';
            $tabla.='<td style="width: 12%; text-align: left;">'.$row['ins_detalle'].'</td>';
            $tabla.='<td style="width: 4%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
            $tabla.='<td style="width: 4.5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
            $tabla.='<td style="width: 6%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
            for ($i=1; $i <=12 ; $i++) { 
              $tabla .= '<td style="width: 4.5%; text-align: right;">' . number_format($row['mes'.$i], 2, ',', '.') . '</td>';            
            }

          $tabla.='</tr>';
          $monto=$monto+$row['ins_costo_total'];
        }
        $tabla.='</tbody>
          <tr class="modo1">
            <td style="height:11px;" colspan=7></td>
            <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
            <td colspan=13></td>
          </tr>
        </table>
        </div>
      </div>
      </div>
      </article><br>';
      }

      return $tabla;
    }


    //// PARA MIGRACION DE REQUERIMIENTOS POR ARCHIVO EXCEL 2027
    public function valida_add_requerimientos() {
        ini_set('max_execution_time', 900); // 15 minutos máximos de procesamiento de CPU
        ini_set('memory_limit', '3072M'); 

      $this->load->library('excel'); // Carga el archivo que creamos arriba
      $cite_id = $this->input->post('cite_id');
      $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);

      // Validar que el CITE exista antes de seguir
      if (empty($cite)) {
        echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información del CITE. Verifique su sesión.')));
        return;
      }

      $archivo = $_FILES['archivo']['tmp_name'];
      $errores = array();
      $data_insertar = array();

      try {
          $archivoTipo = PHPExcel_IOFactory::identify($archivo);
          $lector = PHPExcel_IOFactory::createReader($archivoTipo);
          
          $phpExcel    = $lector->load($archivo);
          $hoja = $phpExcel->getSheet(0);
          $filasMax = $hoja->getHighestRow();

          $columnaMaxLetra = $hoja->getHighestDataColumn(); 
          $totalColumnas = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
          $limitePermitido = 20; // Columna T es la 20

          if ($totalColumnas != $limitePermitido) {
              echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. Solo se permiten $limitePermitido (hasta la 'T'). Por favor, elimine columnas sobrantes.")));
              return;
          }

          // --- 2. VALIDACIÓN FILA POR FILA ---
          for ($i = 2; $i <= $filasMax; $i++) {
              
              $cod_act       = trim($hoja->getCell('A' . $i)->getValue());
              $partida       = trim($hoja->getCell('B' . $i)->getValue());
              $requerimiento = trim($hoja->getCell('C' . $i)->getValue());
              $unidad_medida = trim($hoja->getCell('D' . $i)->getValue());
              
              $cantidad_raw  = $hoja->getCell('E' . $i)->getCalculatedValue();
              $precio_raw    = $hoja->getCell('F' . $i)->getCalculatedValue();
              $total_raw     = $hoja->getCell('G' . $i)->getCalculatedValue();
              $observacion   = trim($hoja->getCell('T' . $i)->getValue());

              if (empty($cod_act) && empty($partida) && empty($requerimiento) && (empty($total_raw) || floatval($total_raw) == 0)) {
                  // Alerta institucional con la instrucción didáctica de limpieza
                  $errores[] = "🚨 RECHAZO DE PLANILLA: Se detectó que la Fila N° $i está completamente vacía o contiene residuos de formato invisible de Excel. Por favor, abra su archivo Excel, seleccione la Fila $i completa (haciendo clic en el número de la fila a la izquierda), haga clic derecho y elija la opción 'Eliminar' para purgar la planilla antes de reintentar la subida.";
                  // Detiene el bucle por completo para no procesar hileras vacías inferiores
                  break; 
              }

              if ($cantidad_raw === NULL || trim($cantidad_raw) === '' || !is_numeric($cantidad_raw)) {
                  $errores[] = "Fila $i: La 'CANTIDAD' es obligatoria y debe ser numérica.";
              } else {
                  $cantidad_float = floatval($cantidad_raw);
                  if ($cantidad_float != floor($cantidad_float)) {
                      $errores[] = "Fila $i: Restricción contable -> La 'CANTIDAD' ($cantidad_raw) debe ser un número entero puro, sin decimales.";
                  }
              }
              $cantidad = intval($cantidad_raw);


              // 📋 REGLA 2: VALIDACIÓN DE PRECIO UNITARIO (Máximo 2 decimales)
                if ($precio_raw === NULL || trim($precio_raw) === '' || !is_numeric($precio_raw)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' es obligatorio y debe ser numérico.";
                } else {
                    $precio_float = floatval($precio_raw);
                    if (round($precio_float, 2) != $precio_float) {
                        $errores[] = "Fila $i: El 'PRECIO UNITARIO' ($precio_raw) excede el límite. Solo se aceptan hasta 2 decimales (Ej: 2500.00).";
                    }
                }
                $precio = round(floatval($precio_raw), 2);

                // 📋 REGLA 3: VALIDACIÓN DEL COSTO TOTAL MATEMÁTICO (Cantidad * Precio)
                $total_calculado = round(($cantidad * $precio), 2);
                $total_archivo   = round(floatval($total_raw), 2);

                if (abs($total_archivo - $total_calculado) > 0.05) {
                    $errores[] = "Fila $i: El 'PRECIO TOTAL' registrado ($total_raw) no coincide con la ecuación aritmética (Cantidad: $cantidad * Precio: $precio = $total_calculado).";
                }

                if (!empty($cod_act)) {
                  $prod_id=$cite[0]['prod_id'];

                } else {
                    $errores[] = "Fila $i: 'CODIGO DE ACTIVIDAD' es obligatoria.";
                }


                if (!empty($partida)) {
                    $par_id=0;
                    if (strlen($partida) == 5) {
                        $verif_partida_asignada=$this->model_ptto_sigep->get_ptto_sigep_pi($cite[0]['aper_id'],$partida);
                        if(count($verif_partida_asignada)!=0){
                          $par_id=$verif_partida_asignada[0]['par_id'];
                        }
                        else{
                          $errores[] = "Fila $i: Error en el registro de la 'PARTIDA' ($partida) No existe o no esta asignado.";
                        }
                    }
                    else{
                      $errores[] = "Fila $i: Error en el registro de la 'PARTIDA' ($partida).";
                    }
                } else {
                    $errores[] = "Fila $i: 'PARTIDA' es obligatoria.";
                }


                // 📋 REGLA 4: VALIDACIÓN MÁSTER Y RESOLUCIÓN DE FÓRMULAS EN LOS 12 MESES (H hasta la S)
                $suma_meses = 0;
                $columnas_meses = array('H' => 1,'I' => 2,'J' => 3,'K' => 4,'L' => 5,'M' => 6,'N' => 7,'O' => 8,'P' => 9,'Q' => 10,'R' => 11,'S' => 12);
                $meses_valores = array();

                foreach ($columnas_meses as $col => $mes_nro) {
                    // 🛠️ REPARADO: getCalculatedValue() resuelve la fórmula de Excel (ej: =SUMA(), =5000/12) y extrae el resultado numérico puro
                    $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                    $val_mes     = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);
                    
                    if (!is_numeric($val_mes)) {
                        $errores[] = "Fila $i: Valor o fórmula no numérica detectada en la columna del mes '$col'.";
                        break;
                    }
                    
                    $monto_mes = round(floatval($val_mes), 2);
                    $suma_meses += $monto_mes;
                    $meses_valores[$mes_nro] = $monto_mes; 
                }

                if (abs($suma_meses - $total_archivo) > 0.05) { 
                    $errores[] = "Fila $i: La suma de la distribución mensual ($suma_meses) no cuadra con el PRECIO TOTAL ($total_archivo) de la celda G.";
                }


                if (empty($errores)) {
                    $data_insertar[] = array(
                        'maestro' => array(
                            'ins_codigo'              => $this->session->userdata("name") . '/REQ/' . $this->gestion,
                            'ins_fecha_requerimiento' => date('Y-m-d'), 
                            'par_id'                  => $par_id,
                            'ins_detalle'             => strtoupper($this->security->xss_clean($requerimiento)),
                            'ins_unidad_medida'       => strtoupper($this->security->xss_clean($unidad_medida)),
                            'ins_cant_requerida'      => $cantidad,
                            'ins_costo_unitario'      => $precio,
                            'ins_costo_total'         => $total_archivo,
                            'ins_observacion'         => strtoupper($this->security->xss_clean($observacion)),
                            'ins_tipo_modificacion'   => $cite[0]['tipo_modificacion'],
                            'fun_id'                  => $this->fun_id,
                            'ins_gestion'             => $this->gestion,
                            'aper_id'                 => $cite[0]['aper_id'], 
                            'com_id'                  => $cite[0]['com_id'], 
                            'form4_cod'               => intval($cod_act), 
                            'ins_mod'                 => 2, // Conmutador de registro insertado
                            'num_ip'                  => $this->input->ip_address(), 
                            'nom_ip'                  => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                        ),

                        'meses' => $meses_valores // Array indexado del 1 al 12 resuelto por fórmulas
                    );
                }
             }   

             if (empty($errores) && count($data_insertar) > 0) {
                
                // Levantamos los muros de control transaccional para aislar fallas de presupuesto
                $this->db->trans_start(); 
                $filas_insertadas_conteo = 0;

                foreach ($data_insertar as $registro) {
                    
                    // 🛠️ REPARADO: Se inserta únicamente la estructura plana del sub-arreglo 'maestro'
                    $this->db->insert('insumos', $registro['maestro']);
                    
                    // Recuperamos el ID autogenerado asignado por la secuencia en Postgres
                    $ins_id = $this->db->insert_id();

                    /*-----------------------------------------------*/
                    // B. Registro de la alineación relacional en la tabla _insumoproducto
                    $data_to_store2 = array(
                        'prod_id' => $prod_id, // Variable física relacional obtenida en la validación
                        'ins_id'  => $ins_id
                    );
                    $this->db->insert('_insumoproducto', $data_to_store2);
                    /*---------------------------------------------*/
                    
                    /*------------ REGISTRO DE LA TEMPORALIDAD ---------*/
                    // 🛠️ REPARADO: Se recorre la colección real 'meses' usando $m_id para no pisar el iterador superior $i
                    for ($m_id = 1; $m_id <= 12; $m_id++) {
                        $pfin = isset($registro['meses'][$m_id]) ? $registro['meses'][$m_id] : 0;
                        
                        if ($pfin != 0) {
                            $data_to_store4 = array( 
                                'ins_id'  => $ins_id,          // Id Insumo maestro correlativo
                                'mes_id'  => $m_id,            // Mes dinámico (1 al 12)
                                'ipm_fis' => $pfin,            // Valor físico financiero del mes resuelto
                                'g_id'    => $this->gestion,   // Gestión POA activa de sesión
                            );
                            $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                        }
                    }
                    
                    $filas_insertadas_conteo++;
                    if($this->copia_insumo2($cite,$this->model_insumo->get_requerimiento($ins_id))){ /// inserta historial reporte
                      /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
                        $this->update_activo_modificacion($cite_id);
                      /*--------------------------------------*/
                    }
                }

                // Cerramos e indicamos a CodeIgniter que evalúe el estatus de las inserciones
                $this->db->trans_complete();

                // Si PostgreSQL detecta un desbordamiento numérico o violación de tope, aplica Rollback total
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array(
                        'status'    => 'error', 
                        'respuesta' => 'error', 
                        'mensaje'   => 'PostgreSQL rechazó las restricciones físicas o techos de los requerimientos. Matriz revertida de forma íntegra.'
                    ));
                    return;
                }

                // 🌟 ÉXITO ABSOLUTO: Despachamos el payload esperado por tu $.ajax en form4.js
                echo json_encode(array(
                    'status'           => 'success',
                    'respuesta'        => 'correcto',
                    'mensaje'          => '¡Matriz de requerimientos contables consolidados e inyectados en el sistema de forma exitosa!',
                    'filas_procesadas' => $filas_insertadas_conteo
                ));

            } else {
                // Si la colección de errores contiene advertencias estructurales, frena e informa al usuario
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'mensaje'   => 'Se detectaron observaciones de validación en la estructura o coincidencia de la plantilla.',
                    'errores'   => !empty($errores) ? $errores : array("No se encontraron registros consistentes para migrar.")
                ));
            }


      } catch (Exception $e) {
            // Captura forense de desbordamientos de memoria del motor de PHPExcel
            echo json_encode(array(
                'status'    => 'error', 
                'respuesta' => 'error', 
                'mensaje'   => 'Falla crítica del lector de planillas: ' . $e->getMessage()
            ));
        }
    }



    /*--- CERRAR MODIFICACION FIN (2027) A OPTIMIZAR ---*/
     public function cerrar_modificacion(){
      if($this->input->post()) {
        $post = $this->input->post();
        $cite_id = $this->security->xss_clean($post['cite_id']); /// Ins id
        $observacion = $this->security->xss_clean($post['observacion']); /// Observacion
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); // Datos Cite

        $verificando=$this->model_modrequerimiento->verif_modificaciones_distrital($cite[0]['dist_id']);
        if(count($verificando)==0){ // Creando campo para la distrital
          $data_to_store2 = array(
            'dist_id' => $cite[0]['dist_id'], /// dist_id
            'g_id' => $this->gestion, /// gestion
            'mod_ope' => 0, 
            'mod_req' => 0,
            );
          $this->db->insert('conf_modificaciones_distrital', $data_to_store2);
          $mod_id=$this->db->insert_id();
        }

        if($cite[0]['cite_estado']==0){ /// Pendiente, Insert Codigo
          $verificando=$this->model_modrequerimiento->verif_modificaciones_distrital($cite[0]['dist_id']);
          $nro_mod=$verificando[0]['mod_req']+1;
          $nro_cdep='';
          if($nro_mod<10){
            $nro_cdep='000';
          }
          elseif($nro_mod<100) {
            $nro_cdep='00';
          }
          elseif($nro_mod<1000){
            $nro_cdep='0';
          }

          /*--------------- Update cite ---------------*/
          $update_cite= array(
            'cite_codigo' => 'R_'.$cite[0]['adm'].'-'.$cite[0]['abrev'].'-'.$nro_cdep.''.$nro_mod,
            'cite_observacion' => strtoupper($observacion),
            'cite_estado' => 1,
            'fun_id'=>$this->fun_id
          );
          $this->db->where('cite_id', $cite_id);
          $this->db->update('cite_mod_requerimientos', $this->security->xss_clean($update_cite));
          /*------------------------------------------*/

          /*----- Update Configuracion mod distrital -----*/
          $update_conf= array(
            'mod_req' => $nro_mod
          );
          $this->db->where('mod_id', $verificando[0]['mod_id']);
          $this->db->update('conf_modificaciones_distrital', $this->security->xss_clean($update_conf));
          /*----------------------------------------------*/
        }
        else{ /// Cerrado, Update Observacion
          $update_cite= array(
            'cite_observacion' => strtoupper($observacion),
            'fun_id'=>$this->fun_id
          );
          $this->db->where('cite_id', $cite_id);
          $this->db->update('cite_mod_requerimientos', $this->security->xss_clean($update_cite));
        }

        /*----------- redireccionar -------*/
        $this->session->set_flashdata('success','SE CERRO CORRECTAMENTE LA MODIFICACIÓN FINANCIERA');
        redirect(site_url("").'/mod/ver_mod_poa/'.$cite_id.'');

      }
      else{
        echo "Error !!!";
      }
    }


    /*--- MODIFICAR CITE REQUERIMIENTO 2026 ---*/
    public function modificar_cite($cite_id){
      $data['cite'] = $this->model_modrequerimiento->get_cite_insumo($cite_id); // Datos Cite
      if(count($data['cite'])!=0){
        $data['menu']=$this->menu(3); //// genera menu
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($data['cite'][0]['proy_id']);
        $titulo=$this->modificacionpoa->titulo_cabecera($data['cite'],0); /// CABECERA
        $tp_cite='INGRESAR A MODIFICACI&Oacute;N';
        if($data['cite'][0]['cite_estado']==1){ 
          $tp_cite='INGRESAR A CITE : '.$data['cite'][0]['cite_codigo'];
        }

        $datos_historial_cite_modificado='';
        if($data['cite'][0]['tp_reporte']==1){
          if($this->tp_adm==1){
            $datos_historial_cite_modificado='
            <div align=right><a href="'.site_url("").'/mod/cite_historial_modf5/'.$data['cite'][0]['cite_id'].'" class="btn btn-success" TARGET="_blank" title="INGRESAR A CITE"><b> HISTORIAL DE MODIFICACIÓN </b></a></div>';
          }
          
          $items_modificados=$this->modificacionpoa->items_modificados_form5_historial($cite_id,0); /// listado de items modificados 2026 (historial)
        }
        else{
          $items_modificados=$this->rep_requerimiento_update($cite_id); /// listado de items modificados anterior gestion 2023
        }


        $tabla='';
        $tabla.='
        '.$this->modificacionpoa->loading('QUITANDO DEL REPORTE POA').'
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-7">
          <section id="widget-grid" class="well">
            <div title="'.$proyecto[0]['aper_id'].'">
              '.$titulo.'
            </div>
          </section>
        </article>
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-5">
          <div class="well">
            <div class="btn-group btn-group-justified">
              <a href="'.site_url("").'/mod/list_requerimientos/'.$data['cite'][0]['cite_id'].'" class="btn btn-success" TARGET="_blank" title="INGRESAR A CITE"><i class="fa fa-save"></i><b>'.$tp_cite.'</b></a>
              <a href="javascript:abreVentana(\''.site_url("").'/mod/rep_mod_financiera/'.$data['cite'][0]['cite_id'].'\');" class="btn btn-default" title="IMPRIMIR REPORTE DE MODIFICACION POA"><i class="fa fa-file-pdf-o"></i> <b>IMPRIMIR REPORTE</b></a>
              <a href="'.base_url().'index.php/mod/list_cites/'.$data['cite'][0]['proy_id'].'"  class="btn btn-default" title="SALIR"><i class="fa fa-caret-square-o-left"></i> <b>SALIR</b></a>
            </div>
          </div>
        </article>

        <article class="col-sm-12">
          <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-x" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
            <header>
              <span class="widget-icon"> <i class="fa fa-align-justify"></i> </span>
              <h2></h2>
            </header>
            <!-- widget div-->
            <div>
              <!-- widget edit box -->
              <div class="jarviswidget-editbox">
                <!-- This area used as dropdown edit box -->
              </div>
              <!-- end widget edit box -->
              <!-- widget content -->
              <div class="widget-body">
                <div class="row">
                '.$datos_historial_cite_modificado.'<br>
                '.$items_modificados.'
                </div>
              </div>
              <!-- end widget body-->
            </div>
            <!-- end widget content -->
          </div>
          <!-- end widget -->
        </article>  ';

        $data['vista']=$tabla;
        $this->load->view('admin/modificacion/requerimientos/update_cite', $data);
      }
      else{
        redirect(site_url("").'/mod/list_cites/'.$data['cite'][0]['proy_id'].'');
      }
    }


    /*--- VER MODIFICACION POA---*/
    public function ver_modificacion_poa($cite_id){
      $data['cite'] = $this->model_modrequerimiento->get_cite_insumo($cite_id); // Datos Cite
      if(count($data['cite'])!=0){
        $data['menu']=$this->menu(3); //// genera menu
        $data['proyecto'] = $this->model_proyecto->get_id_proyecto($data['cite'][0]['proy_id']);
        $data['titulo']=$this->modificacionpoa->titulo_cabecera($data['cite'],0); /// CABECERA
        //$data['datos_cite']=$this->modificacionpoa->datos_cite($data['cite']); /// DATOS CITE

        $this->load->view('admin/modificacion/requerimientos/ver_modificado_poa', $data);
      }
      else{
        redirect(site_url("").'/mod/list_cites/'.$data['cite'][0]['proy_id'].'');
      }
    }


    /* ======== FUNCIONES COMPLEMENTARIAS ======= */

    /*------- Quitar item de la modificacion poa del Cite 2026 ------*/
    function quitar_requerimiento_cite(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $id = $this->security->xss_clean($post['id']); /// insh_id 

          $update_mod = array(
            'historial_activo' => 0 /// item ocultado
          );
          $this->db->where('insh_id', $id);
          $this->db->update('insumos_historial', $update_mod);

          /*-------------------------------*/
          $result = array(
              'respuesta' => 'correcto'
            );
          /*-------------------------------*/

          echo json_encode($result);
      } else {
          echo 'DATOS ERRONEOS';
      }
    }



    /*---- Funcion Copia Insumo a Historial para reportes----*/
    public function copia_insumo2($cite,$insumo){
      //$insumo = $this->model_insumo->get_requerimiento($ins_id); //// DATOS DEL REQUERIMIENTO
      //$insumo = $this->minsumos->get_dato_insumo($ins_id); //// DATOS DEL REQUERIMIENTO
      
      if(count($insumo)!=0){
       // $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
       // $proyecto = $this->model_proyecto->get_id_proyecto($cite[0]['proy_id']); //// DATOS DEL PROYECTO

        //$ins_rel=$this->minsumos->relacion_ins_ope($ins_id);
        //$id=$ins_rel[0]['prod_id'];

        $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
            'ins_codigo' => $insumo[0]['ins_codigo'], /// Codigo Insumo
            'ins_fecha_requerimiento' => $insumo[0]['ins_fecha_requerimiento'], /// Fecha de Requerimiento
            'ins_detalle' => $insumo[0]['ins_detalle'], /// Insumo Detalle
            'ins_cant_requerida' => $insumo[0]['ins_cant_requerida'], /// Cantidad Requerida
            'ins_costo_unitario' => $insumo[0]['ins_costo_unitario'], /// Costo Unitario
            'ins_costo_total' => $insumo[0]['ins_costo_total'], /// Costo Total
            'ins_unidad_medida' => $insumo[0]['ins_unidad_medida'], /// Insumo Unidad de Medida
            'ins_tipo' => $insumo[0]['ins_tipo'], /// Ins Tipo
            'par_id' => $insumo[0]['par_id'], /// Partidas
            'ins_observacion' => $insumo[0]['ins_observacion'], /// Ins Observacion
            'ins_tipo_modificacion' => $insumo[0]['ins_tipo_modificacion'], /// mod por poa o reversion
            'fun_id' => $this->fun_id, /// Funcionario quien realizo la ACCION DE MODIFICACION
            'aper_id' => $cite[0]['aper_id'], /// aper id
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
            'id' => $insumo[0]['prod_id'], ///prod id
            'tipo_mod' => 1, ///tipo de modificacion 1:adicion, 2:modificacion, 3: eliminacion
            'cite_id' => $cite[0]['cite_id'], ///cite id
            'ins_id' => $insumo[0]['ins_id'], ///ins id
          );
          $this->db->insert('insumos_historial', $data_to_store); ///// Guardar en Tabla Insumos 
          $insh_id=$this->db->insert_id();

          for ($i=1; $i <=12 ; $i++) { 
            if($insumo[0]['mes'.$i]!=0){
               $data_to_store4 = array(
              'insh_id' => $insh_id, /// Insumo Id
              'mes_id' => $i, /// Mes
              'ipm_fis' => $insumo[0]['mes'.$i], /// Valor
              'g_id' => $this->gestion, /// gestion
              );
              $this->db->insert('temporalidad_prog_insumo_historial', $data_to_store4);
            }
          }

          return true;
      }
      else{
        return false;
      }
      
    }




    /*---- Funcion Copia Insumo a Historial para reportes----*/
    public function copia_insumo($cite_id,$ins_id,$tipo){
      $insumo = $this->model_insumo->get_requerimiento($ins_id); //// DATOS DEL REQUERIMIENTO
      //$insumo = $this->minsumos->get_dato_insumo($ins_id); //// DATOS DEL REQUERIMIENTO
      
      if(count($insumo)!=0){
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
        $proyecto = $this->model_proyecto->get_id_proyecto($cite[0]['proy_id']); //// DATOS DEL PROYECTO

        $ins_rel=$this->minsumos->relacion_ins_ope($ins_id);
        $id=$ins_rel[0]['prod_id'];

        $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
            'ins_codigo' => $insumo[0]['ins_codigo'], /// Codigo Insumo
            'ins_fecha_requerimiento' => $insumo[0]['ins_fecha_requerimiento'], /// Fecha de Requerimiento
            'ins_detalle' => $insumo[0]['ins_detalle'], /// Insumo Detalle
            'ins_cant_requerida' => $insumo[0]['ins_cant_requerida'], /// Cantidad Requerida
            'ins_costo_unitario' => $insumo[0]['ins_costo_unitario'], /// Costo Unitario
            'ins_costo_total' => $insumo[0]['ins_costo_total'], /// Costo Total
            'ins_unidad_medida' => $insumo[0]['ins_unidad_medida'], /// Insumo Unidad de Medida
            'ins_tipo' => $insumo[0]['ins_tipo'], /// Ins Tipo
            'par_id' => $insumo[0]['par_id'], /// Partidas
            'ins_observacion' => $insumo[0]['ins_observacion'], /// Ins Observacion
            'ins_tipo_modificacion' => $insumo[0]['ins_tipo_modificacion'], /// mod por poa o reversion
            'fun_id' => $this->fun_id, /// Funcionario quien realizo la ACCION DE MODIFICACION
            'aper_id' => $proyecto[0]['aper_id'], /// aper id
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
            'id' => $id, ///prod id
            'tipo_mod' => $tipo, ///tipo de modificacion 1:adicion, 2:modificacion, 3: eliminacion
            'cite_id' => $cite_id, ///cite id
            'ins_id' => $ins_id, ///ins id
          );
          $this->db->insert('insumos_historial', $data_to_store); ///// Guardar en Tabla Insumos 
          $insh_id=$this->db->insert_id();

          $prog=$this->model_insumo->lista_prog_fin($ins_id);
            foreach ($prog as $rowp) {
              $data_to_store4 = array(
              'insh_id' => $insh_id, /// Insumo Id
              'mes_id' => $rowp['mes_id'], /// Mes
              'ipm_fis' => $rowp['ipm_fis'], /// Valor
              'g_id' => $rowp['g_id'], /// gestion
              );
            $this->db->insert('temporalidad_prog_insumo_historial', $data_to_store4);
            $tinsh_id =$this->db->insert_id();
          }

          return true;
      }
      else{
        return false;
      }
      
    }


     /*---- Funcion Copia Insumo a Historial para reportes----*/
    public function registra_insumo_original($cite_id,$ins_id){
      $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
      $proyecto = $this->model_proyecto->get_id_proyecto($cite[0]['proy_id']); /// DATOS DEL PROYECTO

      $insumo = $this->model_insumo->get_requerimiento($ins_id); //// DATOS DEL REQUERIMIENTO
      
      if(count($insumo)!=0){
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
        $proyecto = $this->model_proyecto->get_id_proyecto($cite[0]['proy_id']); //// DATOS DEL PROYECTO

        $ins_rel=$this->minsumos->relacion_ins_ope($ins_id);
        $prod_id=$ins_rel[0]['prod_id'];

        $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
            'cite_id' => $cite_id, ///cite id
            'ins_id' => $insumo[0]['ins_id'], ///ins id
            'ins_codigo' => $insumo[0]['ins_codigo'], /// Codigo Insumo
            'ins_detalle' => $insumo[0]['ins_detalle'], /// Insumo Detalle
            'ins_cant_requerida' => $insumo[0]['ins_cant_requerida'], /// Cantidad Requerida
            'ins_costo_unitario' => $insumo[0]['ins_costo_unitario'], /// Costo Unitario
            'ins_costo_total' => $insumo[0]['ins_costo_total'], /// Costo Total
            'ins_unidad_medida' => $insumo[0]['ins_unidad_medida'], /// Insumo Unidad de Medida
            'par_id' => $insumo[0]['par_id'], /// Partidas
            'ins_estado' => $insumo[0]['ins_estado'], /// Estado
            'ins_gestion' => $insumo[0]['ins_gestion'], /// gestion
            'ins_observacion' => $insumo[0]['ins_observacion'], /// Ins Observacion
            'ins_mod' => $insumo[0]['ins_mod'], /// Ins mod
            'fun_id' => $insumo[0]['fun_id'], /// Funcionario
            'aper_id' => $proyecto[0]['aper_id'], /// aper id
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
            'prod_id' => $prod_id, ///prod id
          );
          $this->db->insert('insumo_original', $data_to_store); ///// Guardar en Tabla Insumos 
          $ins_id_inicial=$this->db->insert_id();

          $prog=$this->model_insumo->lista_prog_fin($ins_id);
          foreach ($prog as $rowp) {
            $update_ins= array(
              'm'.$rowp['mes_id'] => $rowp['ipm_fis']
            );
            $this->db->where('ins_id_inicial', $ins_id_inicial);
            $this->db->update('insumo_original', $this->security->xss_clean($update_ins));
          }
          return true;
      }
      else{
        return false;
      }
      
    }


    /*---- GET DATOS REQUERIMIENTO 2027 ----*/
    public function get_requerimiento(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']);
        $cite_id = $this->security->xss_clean($post['cite_id']);
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
        $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos Get requerimientos 
        $ppto_partida=$this->model_ptto_sigep->vista_get_seguimiento_partida_UOrganizacional($cite[0]['aper_id'],$insumo[0]['par_id']);


        if($insumo[0]['ins_tipo_modificacion']==0){ /// Poa Normal
          $saldo=$ppto_partida[0]['saldo_poa'];
          $partida_padres = $this->model_modificacion->list_part_padres_asig($cite[0]['aper_id']);// Lista partidas padres          
        }
        else{ /// Poa Revertido
          $saldo=$ppto_partida[0]['saldo_revertido'];
          $partida_padres = $this->model_ptto_sigep->lista_partidas_padres_revertidos($cite[0]['aper_id']);// Lista partidas padres REVERTIDO
        }

          /// ------ Partidas padres ------------
          $partidas='';
          $partidas.='
            <option value="">Seleccione Grupo Partida</option>';
            foreach($partida_padres as $row){
              if($row['par_codigo']==$insumo[0]['par_depende']){
                $partidas.='<option value="'.$row['par_codigo'].'" selected>'.$row['par_codigo'].' - '.$row['par_nombre'].'</option>';
              }
              else{
                $partidas.='<option value="'.$row['par_codigo'].'">'.$row['par_codigo'].' - '.$row['par_nombre'].'</option>';
              }
            };
          /// -------------------------------------                           

          $lista_partidas='';
          $lista_partidas.='';

          if($insumo[0]['ins_tipo_modificacion']==0){
            $lista_partidas_dependientes=$this->model_modrequerimiento->lista_partidas_dependientes($insumo[0]['aper_id'],$insumo[0]['par_depende']);
          }
          else{
            $lista_partidas_dependientes=$this->model_ptto_sigep->lista_partidas_dependientes_revertidos($insumo[0]['aper_id'],$insumo[0]['par_depende']);
          }

          foreach ($lista_partidas_dependientes as $row) {
            if($insumo[0]['par_id']==$row['par_id']){
              $lista_partidas.='<option value="'.$row['par_id'].'" selected>'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
            }
            else{
              $lista_partidas.='<option value="'.$row['par_id'].'">'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
            }
          }


          $lista_prod_act=$this->lista_form4_x_unidadresponsable($cite,$insumo); /// Lista de Actividades (Form 4)


          if(count($insumo)!=0){
            $result = array(
              'respuesta' => 'correcto',
              'insumo' => $insumo,
              'partidas'=> $partidas, /// partidas padres
              'lista_partidas'=> $lista_partidas, /// partidas dependientes
              'lista_prod_act'=> $lista_prod_act, /// alineacion a form 4
              'monto_saldo' => ($saldo+$insumo[0]['programado_total']),
              'saldo_dif' => $saldo,
            );
          }
          else{
            $result = array(
              'respuesta' => 'error',
            );
          }
          /// --------------------------------------
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*--- PARTIDAS DEPENDIENTES POA (MOD) ---*/
    function partidas_dependientes($insumo){
      $tabla='';
      $get_partida=$this->model_partidas->get_partida($insumo[0]['par_id']); /// datos de la partda

      if($insumo[0]['ins_tipo_modificacion']==0){
        $lista_partidas=$this->model_modrequerimiento->lista_partidas_dependientes($insumo[0]['aper_id'],$get_partida[0]['par_depende']);
      }
      else{
        $lista_partidas=$this->model_ptto_sigep->lista_partidas_dependientes_revertidos($insumo[0]['aper_id'],$get_partida[0]['par_depende']);
      }

      foreach ($lista_partidas as $row) {
        if($insumo[0]['par_id']==$row['par_id']){
          $tabla.='<option value="'.$row['par_id'].'" selected>'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
        }
        else{
          $tabla.='<option value="'.$row['par_id'].'">'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
        }
      }

      return $tabla;
    }



    /*--- LISTA DE FORMULARIO N° 4 (MOD) A REVISAR---*/
    function lista_form4_x_unidadresponsable($cite,$insumo){
      $tabla='';

        $form4=$this->model_producto->lista_productos($cite[0]['com_id']); /// listado sin temporalidad
        $tabla.='<option value="">Seleccione Alineacion a form N°4</option>';

        if($cite[0]['por_id']==0){ //// alineacion normal
          foreach($form4 as $row){
            if($row['prod_id']==$insumo[0]['prod_id']){
              $tabla.='<option value="'.$row['prod_id'].'" selected>'.$row['or_codigo'].'/'.$row['prod_cod'].'.- '.$row['prod_producto'].' - '.$row['prod_resultado'].'</option>';
            }
            else{
              $tabla.='<option value="'.$row['prod_id'].'">'.$row['or_codigo'].'/'.$row['prod_cod'].'.- '.$row['prod_producto'].' - '.$row['prod_resultado'].'</option>';
            }
          } 
        }
        else{
          foreach($form4 as $row){
            if($row['prod_id']==$insumo[0]['prod_id']){
              $tabla.='<option value="'.$row['prod_id'].'" selected>'.$row['unidad_responsable'].'</option>';
            }
            else{
              $tabla.='<option value="'.$row['prod_id'].'">'.$row['unidad_responsable'].'</option>';
            }
          } 
        }


      return $tabla;
    }


    /*---------- GET MONTO PARTIDA 2026 ------------*/
    public function get_monto_partida(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $par_id = $this->security->xss_clean($post['par_id']);
        $cite_id = $this->security->xss_clean($post['cite_id']);
        $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id); /// Datos Cite
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($cite[0]['proy_id']); /// Datos Unidad Organizacional
        $tp = $this->security->xss_clean($post['tp']); /// tp: 0 (form nuevo) , tp:1 (form modificacion)
        $id = $this->security->xss_clean($post['id']); /// cite id, ins_id

        $ppto_partida=$this->model_ptto_sigep->vista_get_seguimiento_partida_UOrganizacional($proyecto[0]['aper_id'],$par_id);
        if($cite[0]['tipo_modificacion']==0){ /// poa normal
          $saldo=$ppto_partida[0]['saldo_poa'];
        }
        else{ /// poa revertido
          $saldo=$ppto_partida[0]['saldo_revertido'];
        }

        $result = array(
          'respuesta' => 'correcto',
          'monto' => round($saldo,2),
          'datos' => $proyecto[0]['aper_id'].' <> '.$par_id,
        );
  
        echo json_encode($result);
      }else{
          show_404();
      }
    }



    /*------ ASIGNAR UNIDAD RESPONSABLE AL PROGRAMA 72 (BIENES Y SERVICIO) ---------*/
    function asignar_uresponsable(){
      if($this->input->is_ajax_request()){
          $this->form_validation->set_rules('com_id', 'id componente', 'required|trim');
          $post = $this->input->post();
          $com_id= $this->security->xss_clean($post['com_id']);
          $ins_id= $this->security->xss_clean($post['ins_id']);
           
          $update_insumo = array(
            'serv_id' => $com_id,
          );
          $this->db->where('ins_id', $ins_id);
          $this->db->update('insumos', $update_insumo);
              
      }else{
          show_404();
      }
    }



  //// ============ HISTORIAL DE MODIFICACION POA 
  public function historial_modificaciones_cite($cite_id){
    $historial_modificacion=$this->model_modrequerimiento->get_historial_modificacion_cite($cite_id);
    $tabla='';

    $tabla.='
     <style>
        table{font-size: 10px;
            width: 100%;
            max-width:1550px;;
            overflow-x: scroll;
        }
        th{
            padding: 1.4px;
            text-align: center;
            font-size: 10px;
        }
    </style>

    <table border="1" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
              <thead>
              <tr class="modo1" style="text-align: center;" bgcolor="#efefef">
                <th style="width:1%;height:20px;">#</th>
                <th style="width:2.1%;">COD.<br>ACT.</th>
                <th style="width:3.8%;">PARTIDA</th>
                <th style="width:25%;">DETALLE REQUERIMIENTO</th>
                <th style="width:4.6%;">UNIDAD MEDIDA</th>
                <th style="width:4%;">CANT.</th>
                <th style="width:4%;">PRECIO UNI.</th>
                <th style="width:4%;">COSTO TOTAL</th>
                <th style="width:4%;">ENE.</th>
                <th style="width:4%;">FEB.</th>
                <th style="width:4%;">MAR.</th>
                <th style="width:4%;">ABR.</th>
                <th style="width:4%;">MAY.</th>
                <th style="width:4%;">JUN.</th>
                <th style="width:4%;">JUL.</th>
                <th style="width:4%;">AGO.</th>
                <th style="width:4%;">SEPT.</th>
                <th style="width:4%;">OCT.</th>
                <th style="width:4%;">NOV.</th>
                <th style="width:4%;">DIC.</th>
                <th style="width:10%;">OBSERVACIÓN</th>
                <th style="width:15%;">ACCION</th>
                <th style="width:15%;">FECHA</th>
                <th style="width:25%;">RESPONSABLE</th>
              </tr>
              </thead>
              <tbody>';
              $nro=0;
              $monto=0;
              foreach ($historial_modificacion as $row){
                $prog = $this->model_modrequerimiento->list_temporalidad_insumo_historial($row['insh_id']);
                $accion='AGREGADO';
                if($row['tipo_mod']==2){
                  $accion='MODIFICADO';
                }
                elseif($row['tipo_mod']==3){
                  $accion='ELIMINADO';
                }
                $nro++;
                $tabla.='<tr class="modo1">
                  <td style="width: 1%;height:11px; text-align: center;font-size: 6px;">'.$nro.'</td>
                  <td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>
                  <td style="width: 3.8%; text-align: center;font-size: 12px;"><b>'.$row['par_codigo'].'</b></td>
                  <td style="width: 25%; text-align: left;">'.$row['ins_detalle'].'</td>
                  <td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>
                  <td style="width: 4%; text-align: right;">'.round($row['ins_cant_requerida'],2).'</td>
                  <td style="width: 4%; text-align: right;">'.round($row['ins_costo_unitario'],2).'</td>
                  <td style="width: 4%; text-align: right;">'.round($row['ins_costo_total'],2).'</td>';
                  if(count($prog)!=0){
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4%; text-align: right;">' . $prog[0]['mes'.$i] . '</td>';
                    }
                  }
                  else{
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4%; text-align: right;" bgcolor=red>-</td>';
                    }
                  }
                  $tabla.='<td style="width: 10%; text-align: left;">'.$row['ins_observacion'].'</td>';
                  $tabla.='<td style="width: 15%; text-align: left;" bgcolor="#bfebc7"><b>'.$accion.'</b></td>';
                  $tabla.='<td style="width: 15%; text-align: left;" bgcolor="#bfebc7"><b>'.date('d/m/Y',strtotime($row['fecha_creacion'])).'</b></td>';
                  $tabla.='<td style="width: 25%; text-align: left;" bgcolor="#bfebc7"><b>'.$row['fun_nombre'].' '.$row['fun_paterno'].' '.$row['fun_materno'].'</b></td>';
                  
                $tabla.='</tr>';
                $monto=$monto+$row['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:15px;" colspan=7></td>
                  <td style="text-align: right;">' . round($monto,2) . '</td>
                  <td colspan=16></td>
                </tr>
              </table>';
    echo $tabla;
  }









    /*---- GENERAR MENU -----*/
    function menu($mod){
      $enlaces=$this->menu_modelo->get_Modulos($mod);
      for($i=0;$i<count($enlaces);$i++) {
        $subenlaces[$enlaces[$i]['o_child']]=$this->menu_modelo->get_Enlaces($enlaces[$i]['o_child'], $this->session->userdata('user_name'));
      }

      $tabla ='';
      for($i=0;$i<count($enlaces);$i++){
          if(count($subenlaces[$enlaces[$i]['o_child']])>0){
              $tabla .='<li>';
                  $tabla .='<a href="#">';
                      $tabla .='<i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>';    
                      $tabla .='<ul>';    
                          foreach ($subenlaces[$enlaces[$i]['o_child']] as $item) {
                          $tabla .='<li><a href="'.base_url($item['o_url']).'">'.$item['o_titulo'].'</a></li>';
                      }
                      $tabla .='</ul>';
              $tabla .='</li>';
          }
      }
      return $tabla;
    }

    function mes_nombre(){
      $mes[1] = 'ENE.';
      $mes[2] = 'FEB.';
        $mes[3] = 'MAR.';
        $mes[4] = 'ABR.';
        $mes[5] = 'MAY.';
        $mes[6] = 'JUN.';
        $mes[7] = 'JUL.';
        $mes[8] = 'AGOS.';
        $mes[9] = 'SEPT.';
        $mes[10] = 'OCT.';
        $mes[11] = 'NOV.';
        $mes[12] = 'DIC.';
        return $mes;
    }
}