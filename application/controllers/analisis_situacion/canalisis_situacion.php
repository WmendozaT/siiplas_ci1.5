<?php
class Canalisis_situacion extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('menu_modelo');
      $this->load->model('Users_model','',true);
      $this->load->model('programacion/model_proyecto');
      $this->load->model('reportes/mreporte_operaciones/mrep_operaciones');
      $this->load->model('mantenimiento/model_estructura_org');
      $this->load->model('analisis_situacion/model_analisis_situacion');
      $this->load->model('ejecucion/model_ejecucion');
      $this->gestion = $this->session->userData('gestion');
      $this->adm = $this->session->userData('adm'); // 1: Adm. Nacional, 2: Regional
      $this->dist = $this->session->userData('dist');
      $this->rol = $this->session->userData('rol_id');
      $this->dist_tp = $this->session->userData('dist_tp');
      $this->fun_id = $this->session->userdata("fun_id");
      $this->tp_adm = $this->session->userdata("tp_adm"); // 1: Privilegios, 0: sin Privilegios
      $this->mes=$this->mes_nombre();
      $this->load->library('programacionpoa');
      $this->load->library('lib_reportespoa');
        // Si CI no creó la propiedad, la asignamos nosotros a mano
        if (!isset($this->lib_reportespoa)) {
            $CI =& get_instance();
            $this->lib_reportespoa = $CI->lib_reportespoa;
        }
      }else{
          redirect('/','refresh');
      }
    }

    /*------- Tipo de Responsable -------*/
    public function tp_resp(){
      $ddep = $this->model_proyecto->dep_dist($this->dist);
      if($this->adm==1){
        $titulo='<b>RESPONSABLE :</b> NACIONAL';
      }
      elseif($this->adm==2){
        $titulo='<b>RESPONSABLE :</b> '.strtoupper($ddep[0]['dist_distrital']);
      }

      return $titulo;
    }

    /*------ Lista de fodas -----*/
    public function lista_foda($proy_id){
      $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id);
      if(count($data['proyecto'])!=0){
        $data['menu']=$this->menu();
        $data['fodas']=$this->fodas($proy_id);
        $this->load->view('admin/programacion/foda/list_fodas', $data);
      }
      else{
        redirect('admin/dashboard');
      }
    }

    /*---------- FODAS -----------*/
    public function fodas($proy_id){
      $problemas = $this->model_analisis_situacion->list_analisis_problemas($proy_id); /// Problemas
      $tabla ='';
      $tabla .='
            <article class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
              <div class="jarviswidget jarviswidget-color-darken" >
                    <header>
                      <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                      <h2 class="font-md"></h2>  
                    </header>
                <div>
                  
                  <div class="widget-body no-padding">
                    <table id="dt_basic" class="table table table-bordered" width="100%">
                      <thead>
                        <tr>
                          <th style="width:1%;">NRO</th>
                          <th style="width:3%;"></th>
                          <th style="width:40%;">PROBLEMAS</th>
                          <th style="width:3%;"></th>
                          <th style="width:56%;">CAUSAS</th>
                        </tr>
                      </thead>
                      <tbody>';
                      $nro=0;
                      foreach($problemas as $row){
                        $nro++;
                        $tabla.='<tr>';
                          $tabla.='<td>'.$nro.'</td>';
                          $tabla.='<td>
                            <a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default mod_ff" title="MODIFICAR PROBLEMAS" name="'.$row['prob_id'].'">
                              <img src="'.base_url().'assets/ifinal/modificar.png" width="35" height="35"/>
                            </a><br>
                            <a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default del_ff" title="ELIMINAR PROBLEMAS" name="'.$row['prob_id'].'">
                              <img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/>
                            </a>
                          </td>';
                          $tabla.='<td>'.$row['problema'].'</td>';
                          $tabla.='<td>
                            <a href="#" data-toggle="modal" data-target="#modal_nuevo_cff" class="btn btn-default nuevo_cff" title="REGISTRAR CAUSAS-ACCIONES" name="'.$row['prob_id'].'">
                              <img src="'.base_url().'assets/Iconos/add.png" width="35" height="35"/><br>REGISTRAR<br>CAUSAS-ACCIONES
                            </a>
                          </td>';
                          $tabla.='<td>';
                            $causas=$this->model_analisis_situacion->lista_causas_acciones($row['prob_id']);
                            if(count($causas)!=0){
                                $tabla.='
                                <table class="table table-bordered" border=1>
                                  <thead>
                                    <tr>
                                      <th>#</th>
                                      <th></th>
                                      <th>CAUSAS DE LOS PROBLEMAS</th>
                                      <th>ACCIONES RECOMENDADAS</th>
                                      <th></th>
                                    </tr>
                                  </thead>
                                  <tbody>';
                                  $nro_c=0;
                                  foreach($causas as $rowc){
                                    $nro_c++;
                                    $tabla.='<tr>
                                              <td>'.$nro_c.'</td>
                                              <td>
                                                <a href="#" data-toggle="modal" data-target="#modal_mod_cff" class="btn btn-xs mod_cff" title="MODIFICAR CAUSAS/SOLUCIONES" name="'.$rowc['ca_id'].'">
                                                  <img src="'.base_url().'assets/ifinal/modificar.png" width="35" height="35"/>
                                                </a>
                                              </td>
                                              <td>'.$rowc['causas'].'</td>
                                              <td>'.$rowc['acciones'].'</td>
                                              <td><a href="#" data-toggle="modal" data-target="#modal_del_cff" class="btn btn-xs del_cff" title="ELIMINAR CAUSAS/SOLUCIONES" name="'.$rowc['ca_id'].'"><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a></td>
                                            </tr>';
                                  }
                                $tabla.='
                                  </tbody>
                                </table>';
                            }
                            else{
                              $tabla.='SIN REGISTRO';
                            }
                          $tabla.='</td>';
                        $tabla.='</tr>';
                      }
                      $tabla.='
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </article>';

      return $tabla;

    }


    /*------- VALIDA PROBLEMAS (FODA) -------*/
    public function valida_problema(){
      if($this->input->post()) {
        $post = $this->input->post();
        $tp = $this->security->xss_clean($post['tp']); /// Ins id
        $proy_id = $this->security->xss_clean($post['proy_id']); /// Proyecto id
        
        if($tp==1){
          $problema = $this->security->xss_clean($post['problema']); /// problema
          $data_to_store = array( 
            'proy_id' => $proy_id,
            'problema' => strtoupper($problema),
            'fun_id' => $this->fun_id,
          );
          $this->db->insert('analisis_situacion_problemas', $this->security->xss_clean($data_to_store));
        }
        else{
          $prob_id = $this->security->xss_clean($post['prob_id']); /// prob id
          $problema = $this->security->xss_clean($post['mproblema']); /// problema
          $update_ins= array(
            'problema' => strtoupper($problema),
            'estado' => 2,
            'fun_id' => $this->fun_id
          );
          $this->db->where('prob_id', $prob_id);
          $this->db->update('analisis_situacion_problemas', $update_ins);
        }

        $this->session->set_flashdata('success','RE REGISTRO CORRECTAMENTE)');
        redirect(site_url("").'/as/list_foda/'.$proy_id.'');

      } else {
          show_404();
      }
    }

    /*------- Get Problema -------*/
    public function get_problema(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $prob_id = $this->security->xss_clean($post['prob_id']);
        $problema= $this->model_analisis_situacion->get_problema($prob_id);

        if(count($problema)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'problema' => $problema,
          );
        }
        else{
          $result = array(
              'respuesta' => 'error',
          );
        }
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*----- ELIMINA PROBLEMAS -----*/
    function delete_problemas(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $prob_id = $this->security->xss_clean($post['prob_id']);

          $this->db->where('prob_id', $prob_id);
          $this->db->delete('analisis_causas_acciones');

          $this->db->where('prob_id', $prob_id);
          $this->db->delete('analisis_situacion_problemas');

          $problemas= $this->model_analisis_situacion->get_problema($prob_id);
          if(count($problemas)==0){
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

      } else {
          echo 'DATOS ERRONEOS';
      }
    }

    /*------- VALIDA CAUSAS/ACCIONES (FODA) -------*/
    public function valida_causas(){
      if($this->input->post()) {
        $post = $this->input->post();
        $tp = $this->security->xss_clean($post['tp']); /// Ins id
        $proy_id = $this->security->xss_clean($post['proy_id']); /// Proyecto id
        $prob_id = $this->security->xss_clean($post['prob_cid']); /// Problema id
        
        if($tp==1){
          $causas = $this->security->xss_clean($post['causas']); /// causas
          $acciones = $this->security->xss_clean($post['acciones']); /// acciones
          $data_to_store = array( 
            'prob_id' => $prob_id,
            'causas' => strtoupper($causas),
            'acciones' => strtoupper($acciones),
            'fun_id' => $this->fun_id,
          );
          $this->db->insert('analisis_causas_acciones', $data_to_store);
        }
        else{
          $ca_id = $this->security->xss_clean($post['ca_id']); /// ca_id
          $causas = $this->security->xss_clean($post['mcausas']); /// causas
          $acciones = $this->security->xss_clean($post['macciones']); /// acciones
          $update_ca= array(
            'causas' => strtoupper($causas),
            'acciones' => strtoupper($acciones),
            'estado' => 2,
            'fun_id' => $this->fun_id
          );
          $this->db->where('ca_id', $ca_id);
          $this->db->update('analisis_causas_acciones', $update_ca);
        }
        
        $this->session->set_flashdata('success','RE REGISTRO CORRECTAMENTE)');
        redirect(site_url("").'/as/list_foda/'.$proy_id.'');

      } else {
          show_404();
      }
    }

    /*------- Get Problema -------*/
    public function get_causas(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $ca_id = $this->security->xss_clean($post['ca_id']);
        $causas= $this->model_analisis_situacion->get_causas_acciones($ca_id);

        if(count($causas)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'causas' => $causas,
          );
        }
        else{
          $result = array(
              'respuesta' => 'error',
          );
        }
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*----- ELIMINA ACCIONES - CAUSAS -----*/
    function delete_causas_acciones(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $ca_id = $this->security->xss_clean($post['ca_id']);

          $this->db->where('ca_id', $ca_id);
          $this->db->delete('analisis_causas_acciones');

          $causas= $this->model_analisis_situacion->get_causas_acciones($ca_id);
          if(count($causas)==0){
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

      } else {
          echo 'DATOS ERRONEOS';
      }
    }



    /*----- Reporte Lista de Problemas (FODA) 2026 -----*/
    public function reporte_form3_Unidad_organizacional($proy_id){
      $unidad_organizacional = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
    
        if(count($unidad_organizacional) != 0){ 
          $cabecera = $this->programacionpoa->cabecera($unidad_organizacional,3);
          $items = $this->reporte_datos_foda($proy_id);
          $pie_mod = $this->programacionpoa->pie_foda();;
          $data['pie_rep'] = 'FORM_N3' . $unidad_organizacional[0]['tipo'] . ' ' . $unidad_organizacional[0]['act_descripcion'] . ' ' . $unidad_organizacional[0]['abrev'] . '/' . $this->gestion;

          // Configuración de la página en orientación horizontal (paysage) como solicita tu etiqueta <page>
          $data['informacion'] = '
          <page orientation="portrait" backtop="57mm" backbottom="50mm" backleft="4mm" backright="4mm" pagegroup="new">
            <!-- Cabecera Institucional Inalterada -->
            <page_header>
                <br><div class="verde"></div>
                ' . $cabecera . '
            </page_header>
            
            <!-- Pie de Página Fijo en la Base de la Hoja -->
            <page_footer>
                <div style="width: 100%; display: block;">
                    ' . $pie_mod . '
                </div>
            </page_footer>
            ' . $items . '
          </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/foda/reporte_foda', $data, true); 

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
          echo "Error !!! ";
      }

    }

    /*------ LISTA DE FODA - REPORTE -----*/
    public function reporte_datos_foda($proy_id){
        // 1. Recuperamos el pool de problemas autorizados del proyecto
        $problemas = $this->model_analisis_situacion->list_analisis_problemas_reporte($proy_id); 
        
        $tabla = '';
        $tabla .= '
        <style>
            /* Replicación de Gobernanza Visual de la Cabecera en el Cuerpo */
            .cns-tbl-foda-report { 
                width: 100%; 
                border-collapse: collapse; 
                table-layout: fixed; /* Forzado absoluto para no desbordar los márgenes */
                font-family: helvetica, arial, sans-serif;
                margin-top: 5px;
            }
            
            /* Encabezados alineados a los títulos técnicos del Formulario */
            .cns-tbl-foda-report th { 
                background: #475569; 
                color: #ffffff; 
                font-weight: bold; 
                font-size: 7px; 
                text-align: center; 
                vertical-align: middle; 
                border: 0.5px solid #64748b; 
                padding: 5px 3px;
                text-transform: uppercase;
                letter-spacing: 0.2px;
            }
            
            /* Celdas de datos con filetes plomo claro ultra finos (.cns-tbl-ident) */
            .cns-tbl-foda-report td { 
                font-size: 6.5px; 
                vertical-align: top; /* Lectura descendente prolija para textos largos */
                border: 0.5px solid #cbd5e1; 
                padding: 5px 6px; 
                color: #334155;
                line-height: 1.3;
            }
            
            /* Columna incremental idéntica a .cns-lbl */
            .cns-col-nro-foda { 
                text-align: center; 
                font-weight: bold; 
                background: #f1f5f9; 
                color: #475569; 
                vertical-align: middle !important;
            }
            
            .cns-txt-vacio {
                color: #94a3b8;
                font-style: italic;
                font-size: 6.5px;
            }
        </style>

        <!-- 🌟 ENCAPSULAMIENTO HORIZONTAL EN CUADRÍCULA ESTRICTA -->
        <table class="cns-tbl-foda-report">
            <thead>
                <tr>
                    <th style="width: 4%; height: 8px;">#</th>
                    <th style="width: 33.5%;">PROBLEMAS IDENTIFICADOS</th>
                    <th style="width: 31%;">CAUSAS DE LOS PROBLEMAS</th>
                    <th style="width: 31%;">ACCIONES RECOMENDADAS (SOLUCIONES)</th>
                </tr>
            </thead>
            <tbody>';
            
            $nro = 0;
            if(!empty($problemas)) {
                foreach($problemas as $row){
                    $nro++;
                    // Extracción asíncrona de la subtabla relacional (Causas - Acciones)
                    $causas = $this->model_analisis_situacion->lista_causas_acciones($row['prob_id']);
                    // Formateo seguro para evitar Notice de strings vacíos en PHP 5.6
                    $txt_problema = !empty($row['problema']) ? trim(strtoupper($this->security->xss_clean($row['problema']))) : '';
                    
                    // 🌟 CONTROL DE INTEGRIDAD: Evaluamos si registra causas indexadas
                    if(!empty($causas) && count($causas) > 0) {
                        
                        $txt_causas  = '';
                        $txt_acciones = '';
                        $sub_item = 0;
                        
                        // Si hay múltiples causas por problema, las listamos limpiamente con viñetas numéricas
                        foreach($causas as $rowc) {
                            $sub_item++;
                            $c_desc = !empty($rowc['causas']) ? trim(strtoupper($this->security->xss_clean($rowc['causas']))) : '';
                            $a_desc = !empty($rowc['acciones']) ? trim(strtoupper($this->security->xss_clean($rowc['acciones']))) : '';
                            
                            $prefix = (count($causas) > 1) ? $sub_item . ". " : "";
                            
                            $txt_causas   .= $prefix . $c_desc . "<br>";
                            $txt_acciones .= $prefix . $a_desc . "<br>";
                        }
                    } else {
                        // Hilera de protección por si el analista dejó el problema sin causas registradas
                        $txt_causas  = '<span class="cns-txt-vacio">📋 SIN REGISTRO DE CAUSAS</span>';
                        $txt_acciones = '<span class="cns-txt-vacio">📋 SIN ACCIONES RECOMENDADAS</span>';
                    }

                    $tabla .= '
                    <tr>
                        <td class="cns-col-nro-foda" style="height: 10px;width: 4%;">' . $nro . '</td>
                        <td style="text-align: justify; font-weight: 500;width: 33.5%;">' . $txt_problema . '</td>
                        <td style="text-align: justify;width: 31%;">' . $txt_causas . '</td>
                        <td style="text-align: justify; font-weight: 600; color: #1e3a8a;width: 31%;">' . $txt_acciones . '</td>
                    </tr>';
                }
            } else {
                // Alerta formal por si toda la matriz FODA de la distrital viene vacía
                $tabla .= '
                <tr>
                    <td class="cns-col-nro-foda">-</td>
                    <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px 0;">
                        No se identificaron problemas operacionales o análisis de situación registrados para la presente gestión.
                    </td>
                </tr>';
            }
            
            $tabla .= '
            </tbody>
        </table>';

        return $tabla;
    }


    /*------ NOMBRE MES -------*/
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

    /*------ MENU ------*/
    function menu(){
      $enlaces=$this->menu_modelo->get_Modulos(2);
      for($i=0;$i<count($enlaces);$i++){
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
   
}