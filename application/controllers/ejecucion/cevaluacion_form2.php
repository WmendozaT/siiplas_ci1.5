<?php
class Cevaluacion_form2 extends CI_Controller {
  public $rol = array('1' => '3','2' => '4');
  public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_faseetapa');
      
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('ejecucion/model_ejecucion');
        $this->load->model('mestrategico/model_mestrategico');
        $this->load->model('mestrategico/model_objetivogestion');
        $this->load->model('mestrategico/model_objetivoregion');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('programacion/insumos/model_insumo');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dep_id = $this->session->userData('dep_id');
        $this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->fun_id = $this->session->userData('fun_id');
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->conf_estado = $this->session->userData('conf_estado'); /// conf estado Gestion (1: activo, 0: no activo)
        $this->fecha_plazo_actualizacion = strtotime(date('2022-04-30'));
        $this->load->library('eval_oregional');

        }else{
          $this->session->sess_destroy();
          redirect('/','refresh');
        }
    }

    //// Modulo : Evaluacion del POA
    /*-- Menu Regional 2027 FORMULARIO N° 2 --*/
    public function menu_regional(){
      $data['menu']=$this->eval_oregional->menu(4); //// genera menu
      $data['titulo']=$this->eval_oregional->titulo();
      if($this->tp_adm==1){
        $data['tabla']=$this->eval_oregional->regionales();
      }
      else{
        $data['tabla']=$this->eval_oregional->ver_relacion_ogestion($this->dep_id);
      }

      $data['modal_form4_priorizados']=$this->eval_oregional->modal_form4_priorizados(); //// modal actividades priorizados x operacion 
      $this->load->view('admin/evaluacion/evaluacion_form2/menu_regionales', $data);
      //echo $this->eval_oregional->ver_relacion_ogestion(1);
    }


    /*---- FUNCION GET LISTA DE OPERACIONES POR REGIONAL 2027 (Modulo : Evaluacion POA) --------*/
    public function get_lista_operaciones_x_regionales() {
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $dep_id = intval($this->input->post('dep_id')); // Conversión segura a entero

          if ($dep_id > 0) {
              $result = array(
                  'respuesta' => 'correcto',
                  'tabla'     => $this->eval_oregional->ver_relacion_ogestion($dep_id)
              );
          } else {
              $result = array(
                  'respuesta' => 'error',
                  'tabla'     => ''
              );
          }

          echo json_encode($result);
      } else {
          show_404();
      }
    }

  /*---- FUNCION GET LISTA DE ACTIVIDADES PRIORIZADOS 2027--------*/
  public function ver_actividades_priorizados(){
    if($this->input->is_ajax_request() && $this->input->post()){
      $post = $this->input->post();
      $or_id = $this->security->xss_clean($post['or_id']); /// or id
      $dep_id = $this->security->xss_clean($post['dep_id']); /// Regional
      $detalle_oregional=$this->model_objetivoregion->get_objetivosregional($or_id); /// Objetivo Regional
      $regional=$this->model_proyecto->get_departamento($dep_id);
      $meta='';
      if($detalle_oregional[0]['indi_id']==2){
        $meta='%';
      }

      $titulo='
      <b style="font-family:Verdana;font-size: 16px;">
        OBJ. REGIONAL ('.strtoupper($regional[0]['dep_departamento']).'): '.$detalle_oregional[0]['or_codigo'].' '.$detalle_oregional[0]['or_objetivo'].'<br>
        META '.$this->gestion.' : '.round($detalle_oregional[0]['or_meta'],2).' '.$meta.'
      </b>';

      $boton_imprimir='
        <hr>
          <div align=right>
            <a href="javascript:abreVentana(\''.site_url("").'/rep_list_form4_priori_oregional/'.$or_id.'\');" title="IMPRIMIR ACP DISTRIBUCION REGIONAL" class="btn btn-default">
              <img src="'.base_url().'assets/Iconos/printer_empty.png" WIDTH="20" HEIGHT="20"/>&nbsp;&nbsp;IMPRIMIR ACTIVIDADES PRIORITARIOS
            </a>
          </div>
        </hr>';


        $listado=$this->eval_oregional->get_mis_form4_priorizados_x_oregional($detalle_oregional); /// lista de actividades 
        if($detalle_oregional[0]['or_tp']==1){
          $listado=$this->eval_oregional->get_mis_Inversion_priorizados_x_oregional($detalle_oregional[0]['or_id']); //// lista inversion
        }

        $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$listado,
          'titulo'=>$titulo,
          'imprimir_act_priori'=>$boton_imprimir,
        );

      echo json_encode($result);
    }else{
        show_404();
    }
  }


  //// Reporte Formulario N° 2 2027
  public function reporte_form2($dep_id){
    // 1. Ampliación y control de recursos de hardware en el servidor
      ini_set('memory_limit', '2048M'); 
      set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
      
      // Limpieza preliminar del búfer de salida para proteger el binario del PDF
      if (ob_get_length()) ob_clean();
          $regional=$this->model_proyecto->get_departamento($dep_id);
          if(count($regional)!=0){
            $pie_report='REPORTE_FORM2_'.$regional[0]['dep_departamento'].'_'.$this->gestion;
            $listado=$this->eval_oregional->lista_form2_reporte_x_regional($dep_id);
            $cabecera=$this->eval_oregional->cabecera_form2($regional,0);
            $pie=$this->eval_oregional->pie_form2($regional);
            
            $data['lista'] = '
              <page orientation="landscape" backtop="50mm" backbottom="26mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$cabecera.'
                </page_header>
                
                <!-- Pie de Página Fijo en la Base de la Hoja -->
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$pie.'
                    </div>
                </page_footer>
                    '.$listado.'
              </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4_consolidado', $data, true); 
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
              $html2pdf->Output($pie_report. '.pdf', 'I');
          }
          catch(HTML2PDF_exception $e) {
              echo "Error al compilar el reporte: " . $e;
          }
          exit;
        }
        else{
          echo "Error !!!";
      }
  }

    ///// Reporte Evaluacion Formulario N2
    public function reporte_evaluacion_form2($dep_id){
      // 1. Ampliación y control de recursos de hardware en el servidor
      ini_set('memory_limit', '2048M'); 
      set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
      
      // Limpieza preliminar del búfer de salida para proteger el binario del PDF
      if (ob_get_length()) ob_clean();
          $regional=$this->model_proyecto->get_departamento($dep_id);
          if(count($regional)!=0){
            $pie_report='EVALUACION_FORM2_'.$regional[0]['dep_departamento'].'_'.$this->gestion;
            $listado=$this->eval_oregional->lista_form2_evaluacion_reporte_x_regional($dep_id);
            $cabecera=$this->eval_oregional->cabecera_form2($regional,1);
            $pie=$this->eval_oregional->pie_form2($regional);
            
            $data['lista'] = '
              <page orientation="landscape" backtop="50mm" backbottom="26mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$cabecera.'
                </page_header>
                
                <!-- Pie de Página Fijo en la Base de la Hoja -->
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$pie.'
                    </div>
                </page_footer>
                    '.$listado.'
              </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4_consolidado', $data, true); 
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
              $html2pdf->Output($pie_report. '.pdf', 'I');
          }
          catch(HTML2PDF_exception $e) {
              echo "Error al compilar el reporte: " . $e;
          }
          exit;
      }
      else{
        echo "Error !!!";
      }
    }


    /*---- GRAFICO CONSOLIDADO de OPERACIONES (barras) POR REGIONAL 2027 (Modulo : Evaluacion POA) --------*/
    public function get_grafico_cumplimiento_regional() {
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $dep_id = intval($this->input->post('dep_id'));
          $tmes   = $this->tmes;
          $trimestre    = $this->model_evaluacion->get_trimestre($tmes);
          // Asegurar que el índice trimestral esté entre 1 y 4
          $trm_indice = ($tmes >= 1 && $tmes <= 4) ? $tmes : 1;

          // 1. Obtener datos de la regional
          $departamento = $this->model_proyecto->get_departamento($dep_id);
          $nombre_regional = !empty($departamento) ? strtoupper($departamento[0]['dep_departamento']) : 'REGIONAL';
          $desc_trimestre  = !empty($trimestre) ? strtoupper($trimestre[0]['trm_descripcion']) : 'TRIMESTRE';


          // 2. Llamar a tu consulta SQL optimizada
          // Cambia $this->model_evaluacionpoa por el nombre exacto con el que cargas ese modelo
          $form2 = $this->model_objetivoregion->list_form2_regional_x_regional($dep_id);

          $categorias        = array();
          $valores_trimestre = array();
          $valores_gestion   = array();

          $suma_trm_porcentajes  = 0;
          $total_trm_operaciones = 0;

          $suma_porcentajes  = 0;
          $total_operaciones = 0;

          $tabla='';
          $tabla.='
          <div class="row">
              <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                  <div id="container1" style="width: 100%; height: 480px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px;"></div>
              </article>
              <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                  <div id="container2" style="width: 100%; height: 480px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 5px;"></div>
              </article>
          </div>
          <hr>
          <div class="table-responsive">
                <table class="table table-bordered" border=0.2 style="width:100%;" id="datos">
                  <thead>
                  <tr style="font-size: 11px;">
                    <th style="width:1%;height:10px;color:#FFF; text-align: center" bgcolor="#1c7368">#</th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. ACP.</b></th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. OPE.</b></th>
                    <th style="width:11%;color:#FFF; text-align: center" bgcolor="#1c7368">OPERACI&Oacute;N</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. AL TRIMESTRE</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. A LA GESTIÓN</th>
                  </tr>
                  </thead>
                  <tbody>';
                  $nro=0;
                  foreach ($form2 as $row) {
                      $nro++;
                      // Etiqueta para el eje Y de Highcharts
                      $cod_ope = 'OPE ' . $row['og_codigo'] . '.' . $row['or_codigo'];
                      
                      // 1. Capturar metas programadas (Trimestral y Anual/Gestión)
                      $meta_trm = isset($row['trm' . $trm_indice]) ? floatval($row['trm' . $trm_indice]) : 0;
                      $meta_ges = isset($row['trm4']) ? floatval($row['trm4']) : 0; // Meta acumulada al 4to trimestre (Gestión)

                      // Porcentaje Trimestral (porc_cump_1..4) y Gestión (trm_porc_ejec1..4)
                      $campo_trm = 'porc_cump_' . $trm_indice;
                      $campo_ges = 'trm_porc_ejec' . $trm_indice;

                      $porc_trm = isset($row[$campo_trm]) ? round(floatval($row[$campo_trm]), 2) : 0;
                      $porc_ges = isset($row[$campo_ges]) ? round(floatval($row[$campo_ges]), 2) : 0;

                      // Tope visual en barras de 100%
                      $val_trm_tope = ($porc_trm > 100) ? 100 : $porc_trm;
                      $val_ges_tope = ($porc_ges > 100) ? 100 : $porc_ges;


                      // 3. Evaluar color de la barra TRIMESTRAL
                      if ($meta_trm == 0) {
                          $color_trm = '#ffffff'; // Blanco (Sin programación)
                          $borde_trm = '#94a3b8'; // Borde gris para que sea visible sobre fondo blanco
                          $ancho_borde_trm = 1;
                      } else {
                          $borde_trm = null;
                          $ancho_borde_trm = 0;
                          $color_trm = '#ef4444'; // Rojo (<= 50%)
                          if ($porc_trm > 50 && $porc_trm <= 80) {
                              $color_trm = '#f59e0b'; // Amarillo
                          } elseif ($porc_trm > 80) {
                              $color_trm = '#10b981'; // Verde
                          }
                      }

                      // 4. Evaluar color de la barra ANUAL / GESTIÓN
                      if ($meta_ges == 0) {
                          $color_ges = '#ffffff'; // Blanco (Sin programación)
                          $borde_ges = '#94a3b8';
                          $ancho_borde_ges = 1;
                      } else {
                          $borde_ges = null;
                          $ancho_borde_ges = 0;
                          $color_ges = '#ef4444'; // Rojo (<= 50%)
                          if ($porc_ges > 50 && $porc_ges <= 80) {
                              $color_ges = '#f59e0b'; // Amarillo
                          } elseif ($porc_ges > 80) {
                              $color_ges = '#10b981'; // Verde
                          }
                      }

                      $categorias[] = $cod_ope;

                      // 5. Enviar objetos con valor y color hacia Highcharts
                      $valores_trimestre[] = array(
                          'y'           => $val_trm_tope,
                          'color'       => $color_trm,
                          'borderColor' => $borde_trm,
                          'borderWidth' => $ancho_borde_trm
                      );

                      $valores_gestion[] = array(
                          'y'           => $val_ges_tope,
                          'color'       => $color_ges,
                          'borderColor' => $borde_ges,
                          'borderWidth' => $ancho_borde_ges
                      );

                      // 6. Acumuladores para los promedios (solo suma si tiene programación > 0, o quita el if si promedias todo)
                      //if ($meta_trm > 0) {
                          $suma_trm_porcentajes += $val_trm_tope;
                          $total_trm_operaciones++;
                      //}

                      //if ($meta_ges > 0) {
                          $suma_porcentajes += $val_ges_tope;
                          $total_operaciones++;
                      //}
                      $tabla.='
                      <tr>
                        <td>'.$nro.'</td>
                        <td><b>'.$row['og_codigo'].'</b></td>
                        <td><b>'.$row['or_codigo'].'</b></td>
                        <td>'.$row['or_objetivo'].'</td>
                        <td>'.round($row['porc_cump_'.$this->tmes],2).' %</b></td>
                        <td>'.round($row['trm_porc_ejec'.$this->tmes],2).' %</b></td>
                      </tr>';
                    }
                  $tabla.='
                  </tbody>
                </table>
              </div>';

          // 3. Calcular porcentaje global de la regional (Calificacion Anual)
          $promedio_trm = ($total_trm_operaciones > 0) ? round(($suma_trm_porcentajes / $total_trm_operaciones), 1) : 0;
          $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

           //// Prmedio Trimestre
          if ($promedio_trm <= 50) {
              $calificacion_trm = 'INSATISFACTORIO';
              $color_banner_trm = '#ef4444';
          } elseif ($promedio_trm <= 75) {
              $calificacion_trm = 'REGULAR';
              $color_banner_trm = '#f59e0b';
          } elseif ($promedio_trm <= 90) {
              $calificacion_trm = 'BUENO';
              $color_banner_trm = '#0284c7';
          } else {
              $calificacion_trm = 'ÓPTIMO';
              $color_banner_trm = '#16a34a';
          }

          //// Promedio Anual
          if ($promedio_global <= 50) {
              $calificacion = 'INSATISFACTORIO';
              $color_banner = '#ef4444';
          } elseif ($promedio_global <= 75) {
              $calificacion = 'REGULAR';
              $color_banner = '#f59e0b';
          } elseif ($promedio_global <= 90) {
              $calificacion = 'BUENO';
              $color_banner = '#0284c7';
          } else {
              $calificacion = 'ÓPTIMO';
              $color_banner = '#16a34a';
          }

          // 4. RESPUESTA JSON UNIFICADA
          $respuesta = array(
              'status'            => 'success',
              'regional'          => $nombre_regional,
              'trimestre'         => $desc_trimestre,
              'promedio_trm'      => $promedio_trm,
              'calificacion_trm'  => $calificacion_trm,
              'color_banner_trm'  => $color_banner_trm,

              'promedio_global'   => $promedio_global,
              'calificacion'      => $calificacion,
              'color_banner'      => $color_banner,
              'categorias'        => $categorias,
              'valores_trimestre' => $valores_trimestre,
              'valores_gestion'   => $valores_gestion,
              'lista'             => $tabla,
              'gestion'           => $this->gestion
          );

          echo json_encode($respuesta);
      } else {
          show_404();
      }
  }


    /*----- CUADRO EVALUACION OPERACIONES REGIONALES (GRAFICO FORM 2)  a eliminar ----*/
    public function cuadro_evaluacion_grafico_form2($dep_id){
      $data['regional']=$this->model_proyecto->get_departamento($dep_id);
      $data['trimestre']=$this->model_evaluacion->trimestre();
      $data['nro']=count($this->model_objetivoregion->list_form2_regional_x_regional($dep_id));
      $data['eval']=$this->eval_oregional->matriz_cumplimiento_operaciones_regional($dep_id); /// matriz form 2  
      $data['cabecera']=$this->eval_oregional->cabecera_reporte_grafico($data['regional']);
      $data['calificacion']=$this->eval_oregional->calificacion_total_form2_regional($dep_id,1);

      
      $tabla='';
      $tabla.='<div style="font-family: Arial;">DETALLE DE OPERACIONES REGIONALES '.$this->gestion.'</div>
              <table>';
                for ($i=0; $i <$data['nro'] ; $i++) { 
                  $tabla.='<tr><td style="font-family: Arial;font-size: 11px;height: 1%;">OPE. '.$data['eval'][$i][0].'.'.$data['eval'][$i][1].'.- '.$data['eval'][$i][2].' - <b>'.$data['eval'][$i][4].' %</b></td></tr>';
                }
                $tabla.='
              </table>
              <hr>';
      $data['tabla_detalle']=$tabla;
//echo $tabla;
      $this->load->view('admin/evaluacion/evaluacion_form2/reporte_grafico_meta_form2', $data);

    }

}