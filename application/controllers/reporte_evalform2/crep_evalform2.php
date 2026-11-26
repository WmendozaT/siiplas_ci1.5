<?php
/*controlador para evaluacion ACP GESTION 2022*/
class Crep_evalform2 extends CI_Controller {  
    public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->model('Users_model','',true);
        $this->load->model('menu_modelo');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');

        $this->load->model('reporte_eval/model_evalunidad'); /// Model Evaluacion Unidad
        $this->load->model('reporte_eval/model_evalinstitucional'); /// Model Evaluacion Institucional
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('ejecucion/model_certificacion');

        $this->load->model('mestrategico/model_objetivogestion');
        $this->load->model('mestrategico/model_objetivoregion');

        $this->pcion = $this->session->userData('pcion');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->trimestre = $this->model_evaluacion->trimestre();
        $this->fun_id = $this->session->userData('fun_id');
        $this->tr_id = $this->session->userData('tr_id'); /// Trimestre Eficacia
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->mes = $this->mes_nombre();
        $this->load->library('eval_oregional');
      }
      else{
          redirect('/','refresh');
      }
    }

  /// MENU EVALUACIÓN POA FORM2
  public function menu_eval_form2(){
    $data['menu']=$this->menu(7); //// genera menu
    $regionales=$this->model_proyecto->list_departamentos();
    $trimestre=$this->model_evaluacion->trimestre(); /// Datos del Trimestre
    $tabla='';
    $tabla.='
      <input name="base" type="hidden" value="'.base_url().'">
      <input name="gestion" type="hidden" value="'.$this->gestion.'">
      <article class="col-sm-12">
        <div class="well">
          <form class="smart-form">
            <header><b>CONSOLIDADO EVALUACI&Oacute;N OPERACIONES - '.$trimestre[0]['trm_descripcion'].' / '.$this->gestion.'</b></header>
            <fieldset>          
              <div class="row">
                <section class="col col-3">
                  <label class="label"><b>DIRECCIÓN ADMINISTRATIVA</b></label>
                  <select class="form-control" id="d_id" name="d_id" title="SELECCIONE REGIONAL">
                  <option value="">Seleccione Regional ....</option>
                  <option value="0">0.- INSTITUCIONAL C.N.S.</option>';
                  foreach($regionales as $row){
                    if($row['dep_id']!=0){
                      $tabla.='<option value="'.$row['dep_id'].'">'.$row['dep_id'].'.- '.strtoupper($row['dep_departamento']).'</option>';
                    }
                  }
                  $tabla.='
                  </select>
                </section>
              </div>
            </fieldset>
          </form>
        </div>
      </article>';

    $data['regional']=$tabla;
    $data['da']=$this->model_proyecto->list_departamentos();
    $tabla='';
    $tabla.='<div class="well">
              <div id="lista_consolidado">
                <div class="jumbotron">
                  <h1>Cuadro Evaluaci&oacute;n Operaciones '.$this->gestion.'</h1>
                    <p>
                      Reporte consolidado de evaluaci&oacute;n de Operaciones (formulario N° 2) al '.$this->trimestre[0]['trm_descripcion'].' de '.$this->gestion.' a nivel Institucional, Regional.
                    </p>
                </div>
              </div>
            </div>';

    $data['titulo_modulo']=$tabla;
    $data['modal_form4_priorizados']=$this->eval_oregional->modal_form4_priorizados();
    $this->load->view('admin/reportes_cns/repevaluacion_form2/rep_menu', $data);
  }



  /*-------- GET CUADRO EVALUACION FORM 2 INSTITUCIONAL --------*/
  public function get_cuadro_evaluacion_formulario2_institucional() {
    if ($this->input->is_ajax_request() && $this->input->post()) {
        $regionales = $this->db->query("SELECT * FROM public.f_cumplimiento_form2_regionales(?)", array($this->gestion))->result_array();
        $trm_indice = $this->tmes; // Trimestre vigente (1, 2, 3 o 4)

        $siglas_dep = array(
            1 => 'CH', 2 => 'LPZ', 3 => 'CBB', 4 => 'OR', 5 => 'POT',
            6 => 'TJA', 7 => 'SCZ', 8 => 'BE', 9 => 'PA', 10 => 'OFN'
        );

        $categorias   = array();
        $serie_trm    = array(); // Barras: Cumplimiento Trimestral Vigente
        $serie_ges    = array(); // Línea: Cumplimiento Acumulado Gestión Vigente
        
        $filas_tabla  = '';
        $total_ops    = 0;
        $sumas        = array('t1'=>0, 't2'=>0, 't3'=>0, 't4'=>0, 'g1'=>0, 'g2'=>0, 'g3'=>0, 'g4'=>0);
        $conteo_reg   = 0;

        foreach ($regionales as $row) {
            $id    = intval($row['dep_id']);
            $sigla = isset($siglas_dep[$id]) ? $siglas_dep[$id] : substr(strtoupper($row['dep_departamento']), 0, 3);
            $ops   = intval($row['total_operaciones']);

            $t1 = floatval($row['prom_trm1']); $t2 = floatval($row['prom_trm2']);
            $t3 = floatval($row['prom_trm3']); $t4 = floatval($row['prom_trm4']);
            $g1 = floatval($row['prom_ges1']); $g2 = floatval($row['prom_ges2']);
            $g3 = floatval($row['prom_ges3']); $g4 = floatval($row['prom_ges4']);

            // Datos para el Gráfico Combinado (Trimestre activo vs Gestión acumulada)
            $categorias[] = $sigla . ' (' . strtoupper($row['dep_departamento']) . ')';
            $serie_trm[]  = floatval($row['prom_trm' . $trm_indice]);
            $serie_ges[]  = floatval($row['prom_ges' . $trm_indice]);

            // Acumuladores para fila de Promedio Nacional
            $total_ops += $ops;
            if ($ops > 0) {
                $sumas['t1'] += $t1; $sumas['t2'] += $t2; $sumas['t3'] += $t3; $sumas['t4'] += $t4;
                $sumas['g1'] += $g1; $sumas['g2'] += $g2; $sumas['g3'] += $g3; $sumas['g4'] += $g4;
                $conteo_reg++;
            }

            // Fila de la tabla con resaltado sutil en la columna del trimestre activo
            $filas_tabla .= '
            <tr>
                <td class="text-center"><b>' . $id . '</b></td>
                <td><b>' . strtoupper($row['dep_departamento']) . '</b></td>
                <td class="text-center">' . $ops . '</td>
                
                <td class="text-right" style="' . ($trm_indice == 1 ? 'background:#e0f2fe; font-weight:bold;' : '') . '">' . number_format($t1, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 2 ? 'background:#e0f2fe; font-weight:bold;' : '') . '">' . number_format($t2, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 3 ? 'background:#e0f2fe; font-weight:bold;' : '') . '">' . number_format($t3, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 4 ? 'background:#e0f2fe; font-weight:bold;' : '') . '">' . number_format($t4, 1) . '%</td>
                
                <td class="text-right" style="' . ($trm_indice == 1 ? 'background:#dcfce7; font-weight:bold;' : '') . '">' . number_format($g1, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 2 ? 'background:#dcfce7; font-weight:bold;' : '') . '">' . number_format($g2, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 3 ? 'background:#dcfce7; font-weight:bold;' : '') . '">' . number_format($g3, 1) . '%</td>
                <td class="text-right" style="' . ($trm_indice == 4 ? 'background:#dcfce7; font-weight:bold;' : '') . '">' . number_format($g4, 1) . '%</td>
            </tr>';
        }

        // Calcular fila de Promedio Institucional
        $div = ($conteo_reg > 0) ? $conteo_reg : 1;
        $fila_total = '
        <tr style="background-color: #f1f5f9; font-weight: bold; color: #0f172a; border-top: 2px solid #94a3b8;">
            <td colspan="2" class="text-right">PROMEDIO :</td>
            <td class="text-center">' . $total_ops . '</td>
            <td class="text-right">' . number_format($sumas['t1'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['t2'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['t3'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['t4'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['g1'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['g2'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['g3'] / $div, 1) . '%</td>
            <td class="text-right">' . number_format($sumas['g4'] / $div, 1) . '%</td>
        </tr>';

        $tabla = '
        <div class="jarviswidget well" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 12px;">
                    <h2 style="margin: 0; font-weight: bold; color: #1e3a8a;">Consolidado Institucional Formulario N° 2 - Gestión ' . $this->gestion . '</h2>
                    
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <a href="javascript:void(0);" 
                           class="btn btn-lg btn-default" 
                           onclick="nivel_cumplimiento_form2_institucional_total_o_priorizados(0); return false;" 
                           title="NIVEL DE CUMPLIMIENTO FORM N° 2 (TOTAL)">
                            <img src="'.base_url().'assets/Iconos/chart_bar.png" width="18" height="18"/>
                            <span style="font-size: 12px; color:#1e5e56; border-color:#1e5e56">CUMP. (Form. N° 2) Total</span>
                        </a>
                        <a href="javascript:void(0);" 
                           class="btn btn-lg btn-default" 
                           onclick="nivel_cumplimiento_form2_institucional_total_o_priorizados(1); return false;" 
                           title="NIVEL DE CUMPLIMIENTO FORM N° 2 (PRIORIZADOS)">
                            <img src="'.base_url().'assets/Iconos/chart_bar.png" width="18" height="18"/>
                            <span style="font-size: 12px; color:#1e5e56; border-color:#1e5e56">CUMP. (Form. N° 2) Priorizados</span>
                        </a>';
                        if($this->tp_adm==1){
                            $tabla.='<!-- Botón 4: Reporte Consolidado -->
                          <button type="button" id="btn_exportar_excel" class="btn btn-lg btn-primary" onclick="exportarExcelInstitucional(0, this);" style="font-size: 12px;background-color: #15803d; border: none; font-weight: bold; padding: 7px 15px;">
                            <i class="fa fa-file-excel-o"></i> EXPORTAR EXCEL
                          </button>';
                          } 
                        $tabla.='

                        <!-- Botón 2: Exportar a PDF -->
                        <button type="button" class="btn btn-lg btn-success" onclick="imprimirConsolidadoInstitucional();" style="font-size: 12px;background-color: #1e3a8a; border: none; font-weight: bold; padding: 7px 15px;">
                            <i class="fa fa-print"></i> EXPORTAR REPORTE PDF
                        </button>
                    </div>
                </div>

                <!-- 1. GRÁFICO COMBINADO (COLUMNAS + LÍNEA) -->
                <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; background: #fff; margin-bottom: 18px;">
                    <div id="grafico_combo_institucional" style="width: 100%; height: 420px;"></div>
                </div>

                <!-- 2. TABLA DETALLADA POR TRIMESTRE Y ANUAL -->
                <div class="table-responsive" style="border: 1px solid #cbd5e1; border-radius: 6px; background: #fff;">
                    <table id="tabla_detalle_institucional" class="table table-bordered table-striped table-hover" style="width: 100%; font-size: 11px; margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; background: #1e3a8a; color: #fff; width: 4%;">#</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; background: #1e3a8a; color: #fff; width: 22%;">REGIONAL / DEPARTAMENTO</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; background: #1e3a8a; color: #fff; width: 10%;">TOTAL OPE.</th>
                                <th colspan="4" class="text-center" style="background: #0284c7; color: #fff;">(%) CUMPLIMIENTO TRIMESTRAL</th>
                                <th colspan="4" class="text-center" style="background: #1c7368; color: #fff;">(%) CUMPLIMIENTO ACUMULADO GESTIÓN</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="background: #e0f2fe; color: #0369a1;">I TRM</th>
                                <th class="text-center" style="background: #e0f2fe; color: #0369a1;">II TRM</th>
                                <th class="text-center" style="background: #e0f2fe; color: #0369a1;">III TRM</th>
                                <th class="text-center" style="background: #e0f2fe; color: #0369a1;">IV TRM</th>
                                <th class="text-center" style="background: #dcfce7; color: #15803d;">I TRM</th>
                                <th class="text-center" style="background: #dcfce7; color: #15803d;">II TRM</th>
                                <th class="text-center" style="background: #dcfce7; color: #15803d;">III TRM</th>
                                <th class="text-center" style="background: #dcfce7; color: #15803d;">IV TRM</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $filas_tabla . '
                        </tbody>
                        <tfoot>
                            ' . $fila_total . '
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>';

        echo json_encode(array(
            'respuesta'  => 'correcto',
            'gestion'    => $this->gestion,
            'trimestre'  => $trm_indice,
            'categorias' => $categorias,
            'serie_trm'  => $serie_trm,
            'serie_ges'  => $serie_ges,
            'tabla'      => $tabla
        ));
    } else {
        show_404();
    }
  }



  /*-------- GET CUADRO EVALUACION FORM 2 REGIONAL (Consolidado por Regional) 2027 (Modulo : Reportes POA)--------*/
  public function get_cuadro_evaluacion_formulario2_regional(){
    if($this->input->is_ajax_request() && $this->input->post()){
      $post = $this->input->post();
      $dep_id = $this->security->xss_clean($post['dep_id']); // dep id, 0: Nacional
      $regional=$this->model_proyecto->get_departamento($dep_id);
      $form2 = $this->model_objetivoregion->list_form2_regional_x_regional($dep_id); /// form 2 por regional
      $trimestre    = $this->model_evaluacion->trimestre();

          // 1. INICIALIZAR VARIABLES FALTANTES
          $nombre_regional = !empty($regional) ? strtoupper($regional[0]['dep_departamento']) : 'REGIONAL';
          $desc_trimestre  = !empty($trimestre) ? strtoupper($trimestre[0]['trm_descripcion']) : 'TRIMESTRE';
          $trm_indice      = $this->tmes; // Trimestre activo (1, 2, 3 o 4)

          $categorias        = array();
          $valores_trimestre = array();
          $valores_gestion   = array();

          $suma_trm_porcentajes  = 0;
          $total_trm_operaciones = 0;

          $suma_porcentajes  = 0;
          $total_operaciones = 0;

          foreach ($form2 as $row) {
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
                  $suma_trm_porcentajes += $val_trm_tope;
                  $total_trm_operaciones++;

                  $suma_porcentajes += $val_ges_tope;
                  $total_operaciones++;
          }

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


      $tabla='';
      $tabla .= '
        <div class="jarviswidget well" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
            <header>
                <div id="cabecera" style="display: none">cabecera</div>
            </header>
            <div>
                <h2 style="margin-top: 0; font-weight: bold; color: #1e3a8a;">Evaluación del Formulario N° 2 (Operaciones) - '.$nombre_regional.'</h2>
                <ul id="myTab1" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#sA" data-toggle="tab"><b>(%) Cumplimiento de Operaciones</b></a>
                    </li>
                    <li>
                        <a href="#sC" data-toggle="tab"><b>Detalle de Cumplimiento</b></a>
                    </li>
                </ul>

                <div id="myTabContent1" class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="sA">
                      <!-- BARRA DE HERRAMIENTAS CON 3 BOTONES DE IMPRESIÓN -->
                      <div class="well well-sm" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 15px; margin-bottom: 15px; display: flex; justify-content: flex-end; align-items: center; flex-wrap: wrap; gap: 10px;">
                          <span style="margin-right: auto; font-weight: bold; color: #334155; font-size: 12px;">
                              <i class="fa fa-file-pdf-o" style="color: #dc2626;"></i> OPCIONES DE EXPORTACIÓN:
                          </span>

                          <!-- Botón 1: Gráfico Trimestral -->
                          <button type="button" class="btn btn-info btn-sm" onclick="imprimirGraficoIndividual(\'grafico_trimestral\', \'calificacion_trimestral\', \'Trimestral\');" style="background-color: #0284c7; border: none; font-weight: bold; padding: 6px 12px;">
                              <i class="fa fa-bar-chart"></i> EXPORTAR GRÁF. TRIMESTRAL.Pdf
                          </button>

                          <!-- Botón 2: Gráfico Anual / Gestión -->
                          <button type="button" class="btn btn-success btn-sm" onclick="imprimirGraficoIndividual(\'grafico1\', \'calificacion\', \'Gestion\');" style="background-color: #1c7368; border: none; font-weight: bold; padding: 6px 12px;">
                              <i class="fa fa-bar-chart"></i> EXPORTAR GRÁF. GESTIÓN.Pdf
                          </button>

                          <!-- Botón 3: Reporte Consolidado -->
                          <a href="javascript:abreVentana(\''.site_url("").'/rep_eval_oregional/'.$dep_id.'\');" class="btn btn-primary btn-sm" style="background-color: #1e3a8a; border: none; font-weight: bold; padding: 6px 14px;">
                              <i class="fa fa-print"></i> REPORTE EVALUACIÓN (FORM N° 2)
                          </a>';

                          if($this->tp_adm==1){
                            $tabla.='<!-- Botón 4: Reporte Consolidado -->
                          <button type="button" id="btn_exportar_excel" class="btn btn-primary btn-sm" onclick="exportarExcelInstitucional('.$dep_id.', this);" style="background-color: #15803d; border: none; font-weight: bold; padding: 7px 15px;">
                            <i class="fa fa-file-excel-o"></i> EXPORTAR EXCEL
                          </button>';
                          } 
                      $tabla.='    
                      </div>

                      <div class="row">
                          <!-- COLUMNA 1: TRIMESTRAL -->
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="calificacion_trimestral" class="text-center" style="padding: 10px; border-radius: 5px; color: #fff; margin-bottom: 10px; background-color: '.$color_banner_trm.';">
                                  <div style="font-size: 26px; font-weight: bold; line-height: 1.1;">'.$promedio_trm.'%</div>
                                  <div style="font-size: 11px; font-weight: bold; letter-spacing: 1px;">'.$calificacion_trm.'</div>
                              </div>
                              <div style="border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; background: #fff;">
                                  <div id="grafico_trimestral" style="width: 100%; min-height: 550px;"></div>
                              </div>
                          </article>

                          <!-- COLUMNA 2: ANUAL / GESTIÓN -->
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="calificacion" class="text-center" style="padding: 10px; border-radius: 5px; color: #fff; margin-bottom: 10px; background-color: '.$color_banner.';">
                                  <div style="font-size: 26px; font-weight: bold; line-height: 1.1;">'.$promedio_global.'%</div>
                                  <div style="font-size: 11px; font-weight: bold; letter-spacing: 1px;">'.$calificacion.'</div>
                              </div>
                              <div style="border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; background: #fff;">
                                  <div id="grafico1" style="width: 100%; min-height: 550px;"></div>
                              </div>
                          </article>
                      </div>
                  </div>

                    
                    <div class="tab-pane fade" id="sC">
                        <div class="row">
                            '.$this->get_lista_acp_regional_con_operaciones($dep_id).'
                        </div>
                    </div>
                </div>
            </div>
        </div>';


        // 4. RESPUESTA JSON UNIFICADA
          $respuesta = array(
            'respuesta'         => 'correcto',
            'regional'          => $nombre_regional,
            'trimestre'         => $desc_trimestre,

            'promedio_trm'      => $promedio_trm,
            'calificacion_trm'  => $calificacion_trm,
            'color_banner_trm'  => $color_banner_trm,

            'promedio_global'   => $promedio_global,
            'calificacion'      => $calificacion,
            'color_banner'      => $color_banner,

            'categorias'        => $categorias,
            'valores_trimestre' => $valores_trimestre, /// cumplimiento al trimestre
            'valores_gestion'   => $valores_gestion, /// cumplimiento a la gestion
            
            'tabla'             => $tabla,
            'gestion'           => $this->gestion
          );

      echo json_encode($respuesta);
    }else{
        show_404();
    }
  }



  /*---- FUNCION GET NIVEL DE CUMPLIMIENTO DE OPERACIONES X ACP REGIONAL (GRAFICO)---*/
  public function ver_datos_avance_oregional_acp(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $post   = $this->input->post();
          $og_id  = $this->security->xss_clean($post['og_id']);
          $dep_id = $this->security->xss_clean($post['dep_id']);

          $regional     = $this->model_proyecto->get_departamento($dep_id);
          $acp_regional = $this->model_objetivogestion->get_objetivosgestion($og_id);
          $form2        = $this->model_objetivoregion->list_form2_regional_x_form1($og_id, $dep_id);
          $trimestre    = $this->model_evaluacion->trimestre();

          // 1. INICIALIZAR VARIABLES FALTANTES
          $nombre_regional = !empty($regional) ? strtoupper($regional[0]['dep_departamento']) : 'REGIONAL';
          $desc_trimestre  = !empty($trimestre) ? strtoupper($trimestre[0]['trm_descripcion']) : 'TRIMESTRE';
          $trm_indice      = $this->tmes; // Trimestre activo (1, 2, 3 o 4)

          $categorias        = array();
          $valores_trimestre = array();
          $valores_gestion   = array();

          $suma_trm_porcentajes  = 0;
          $total_trm_operaciones = 0;

          $suma_porcentajes  = 0;
          $total_operaciones = 0;

          $nro = 0;
          $filas_tabla = '';
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
                  $suma_trm_porcentajes += $val_trm_tope;
                  $total_trm_operaciones++;

                  $suma_porcentajes += $val_ges_tope;
                  $total_operaciones++;

              $filas_tabla .= '
              <tr>
                  <td class="text-center">'.$nro.'</td>
                  <td class="text-center"><b>'.$row['og_codigo'].'</b></td>
                  <td class="text-center"><b>'.$row['or_codigo'].'</b></td>
                  <td>'.$row['or_objetivo'].'</td>
                  <td class="text-right" style="background-color: #f0f9ff;"><b>'.$porc_trm.' %</b></td>
                  <td class="text-right" style="background-color: #f0fdf4;"><b>'.$porc_ges.' %</b></td>
              </tr>';
          }

          // 3. CALCULAR PROMEDIO GLOBAL Y CALIFICACIÓN
          $promedio_trm = ($total_trm_operaciones > 0) ? round(($suma_trm_porcentajes / $total_trm_operaciones), 1) : 0;
          $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

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
          // 2. CONSTRUIR CONTENEDORES DE GRÁFICOS Y TABLA
          $tabla = '
          <div class="row">
            <div id="tit_acp" class="alert alert-info" style="background-color: #f4f6f6; border-left: 5px solid #1c7368; color: #1e293b; padding: 10px 15px; margin-bottom: 15px;">
              <b>ACP. '.$acp_regional[0]['og_codigo'].' .- </b>'.$acp_regional[0]['og_objetivo'].'
            </div>
            <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div id="banner_calificacion_acp_trm" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner_trm.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp_trm">'.$promedio_trm.'%</div>
                    <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp_trm">'.$calificacion_trm.'</div>
                </div>
                <div id="container1" style="width: 100%; min-height: 300px;"></div>
            </article>

            <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div id="banner_calificacion_acp" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp">'.$promedio_global.'%</div>
                    <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp">'.$calificacion.'</div>
                </div>
                <div id="container2" style="width: 100%; min-height: 300px;"></div>
            </article>
          </div>
          <hr>
          <div class="table-responsive">
              <table class="table table-bordered" style="width:100%; font-size: 11px;" id="datos_acp">
                  <thead>
                      <tr style="background-color: #1c7368; color: #000;">
                          <th style="width:3%; text-align: center;">#</th>
                          <th style="width:7%; text-align: center;">COD. ACP</th>
                          <th style="width:7%; text-align: center;">COD. OPE</th>
                          <th style="width:53%; text-align: center;">OPERACI&Oacute;N</th>
                          <th style="width:15%; text-align: center;">(%) CUMP. AL TRIMESTRE</th>
                          <th style="width:15%; text-align: center;">(%) CUMP. A LA GESTI&Oacute;N</th>
                      </tr>
                  </thead>
                  <tbody>
                    '.$filas_tabla.'
                </tbody>
              </table>
          </div>';
        

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

  
  /*---- LISTA DE ACP REGIONALES CON OPERACIONES POR REGIONAL 2027 (Modulo : Reportes POA)---*/
  public function get_lista_acp_regional_con_operaciones($dep_id){
    $tabla='';
    $acp_regional=$this->model_objetivogestion->lista_acp_x_regional($dep_id); 
    $tabla.='
    <article class="col-sm-12 col-md-12 col-lg-2">
    </article>
    <article class="col-sm-12 col-md-12 col-lg-8">
        <div class="jarviswidget well transparent" id="wid-id-9" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
          <header>
            <span class="widget-icon"> <i class="fa fa-comments"></i> </span>
            <h2>Accordions </h2>
          </header>
          <div>
            <div class="jarviswidget-editbox">
            </div>
            <div class="widget-body">
              <div align=right>';

              //$tabla.='  <a href="javascript:abreVentana(\''.site_url("").'/rep_eval_oregional/'.$dep_id.'\');" title="REPORTE EVALUACIÓN META REGIONAL" class="btn btn-lg btn-default" style="font-size: 12px; color:#1e5e56; border-color:#1e5e56"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/> &nbsp;<b>IMPRIMIR DETALLE OPERACIONES POR REGIONAL (Form N° 2)</b></a>';
              $tabla.='  <a href="javascript:abreVentana(\''.site_url("").'/rep_oregional/'.$dep_id.'\');" title="REPORTE EVALUACIÓN META REGIONAL" class="btn btn-lg btn-default" style="font-size: 12px; color:#1e5e56; border-color:#1e5e56"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/> &nbsp;<b>IMPRIMIR DETALLE OPERACIONES POR REGIONAL (Form N° 2)</b></a>
              </div>
              <hr>
              <div class="panel-group smart-accordion-default" id="accordion">';
                $nro_acp=0;
                foreach($acp_regional as $row){
                  $form2=$this->model_objetivoregion->list_form2_regional_x_form1($row['og_id'],$dep_id);
                  $nro_acp++;
                  $collapse='class="collapsed"';
                  $in='';
                  if($nro_acp==1){
                    $collapse='';
                    $in='in';
                  }

                  $tabla.='
                  <div class="panel panel-default">
                    <div class="panel-heading">
                      <h4 class="panel-title"><a data-toggle="collapse" data-parent="#accordion" href="#'.$nro_acp.'" '.$collapse.'> <i class="fa fa-lg fa-angle-down pull-right"></i> <i class="fa fa-lg fa-angle-up pull-right"></i> <b>A.C.P. '.$row['og_codigo'].'</b> .- '.$row['og_objetivo'].'</a></h4>
                    </div>
                    <div id="'.$nro_acp.'" class="panel-collapse collapse '.$in.'">
                      <div class="panel-body no-padding">
                        <br>
                          <table style="width:100%">
                            <tr>
                              <td>
                                <div style="font-size: 15px;font-family: Arial" align=left><b>DETALLE (%) CUMPLIMIENTO DE OPERACIONES DE LA ACP. '.$row['og_codigo'].' .- </b>'.$row['og_objetivo'].'</div>
                              </td>
                              <td>
                                  <a href="javascript:void(0);" 
                                     class="btn btn-lg btn-default" 
                                     onclick="nivel_cumplimiento_rep_form2_regional('.$row['og_id'].','.$dep_id.'); return false;" 
                                     title="NIVEL DE CUMPLIMIENTO ACCION DE CORTO PLAZO - REGIONAL">
                                      <img src="'.base_url().'assets/Iconos/chart_bar.png" width="35" height="35"/><br>
                                      <span style="font-size: 10px; font-weight: bold;">GRAF. CUMPLIMIENTO OPERACIONES</span>
                                  </a>
                              </td>
                            </tr>
                          </table>
                          <br>
                          <table class="table table-bordered" style="width:100%">
                            <thead>
                              <tr>
                                <th style="width:1%;text-align:center">#</th>
                                <th style="width:30%;text-align:center">OPERACIÓN '.$this->gestion.'</th>
                                <th style="width:5%;text-align:center">META</th>
                                <th style="width:10%;text-align:center">(%) CUMPLIMIENTO TRIMESTRAL</th>
                                <th style="width:10%;text-align:center">(%) CUMPLIMIENTO GESTIÓN</th>
                                <th style="width:5%;text-align:center">VER ALINEACION</th>
                              </tr>
                            </thead>
                              <tbody>';
                              $nro_form2=0;
                              foreach($form2 as $reg){
                                $base_url = base_url();
                                $label_priorizado='';
                                if($reg['or_priorizado']==1){
                                  $img_path = $base_url . 'assets/ifinal/';
                                  $label_priorizado = "<br><img src='{$img_path}ok.png' WIDTH='25' HEIGHT='25'/><br><font size=1 color=green><b>PRIORIZADO</b></font>";
                                }

                                $nro_form2++;
                                $tabla.='
                                <tr >
                                  <td style="font-size: 10px;font-family: Arial;text-align:center">'.$label_priorizado.'<br>'.$nro_form2.'</td>
                                  <td style="font-size: 11px;font-family: Arial;"><b>'.$reg['og_codigo'].'.'.$reg['or_codigo'].'.- </b>'.$reg['or_objetivo'].'</td>
                                  <td style="font-size: 10px;font-family: Arial;text-align:right">'.round($reg['or_meta'],2).'</td>
                                  <td style="font-size: 12px;font-family: Arial;text-align:right;" bgcolor="#e6fdfb"><b>'.round($reg['porc_cump_'.$this->tmes],2).' %</b></td>
                                  <td style="font-size: 10px;font-family: Arial;text-align:right">'.round($reg['trm_porc_ejec'.$this->tmes],2).' %</td>
                                  <td align=center>
                                    <a href="#" data-toggle="modal" data-target="#modal_act_priorizados" style="font-size: 10px;" class="btn btn-lg btn-default" name="'.$reg['or_id'].'"  onclick="ver_actividades_priorizados('.$reg['or_id'].','.$dep_id.');" title="VER MIS ACTIVIDADES PRIORIZADOS">ACT. PRIORIZADOS</a>
                                  </td>
                                </tr>';
                              }
                            $tabla.='
                            </tbody>
                          </table>
                        <hr>
                      </div>
                    </div>
                  </div>';
                }
              
              $tabla.='
              </div>
            </div>
          </div>
        </div>
      </article>';

    return $tabla;
  }




  /*---- GRAFICO CONSOLIDADO de OPERACIONES (barras) INSTITUCIONAL 2027 total|Priorizados (Modulo : Reporte POA) --------*/
   public function get_grafico_cumplimiento_form2_institucional_total_o_priorizados() {
    if ($this->input->is_ajax_request() && $this->input->post()) {
        $tp             = intval($this->input->post('tp'));
        $tmes           = $this->tmes;
        $trimestre      = $this->model_evaluacion->get_trimestre($tmes);
        $trm_indice     = ($tmes >= 1 && $tmes <= 4) ? $tmes : 1;
        $desc_trimestre = !empty($trimestre) ? strtoupper($trimestre[0]['trm_descripcion']) : 'TRIMESTRE ' . $trm_indice;

        // 1. Llamar al modelo según sea Total (0) o Priorizado (1)
        if ($tp == 0) {
            $form2 = $this->model_objetivoregion->consolidado_formN2_og_or_institucional();
        } else {
            $form2 = $this->model_objetivoregion->consolidado_formN2_og_or_institucional_priorizado();
        }

        $categorias        = array();
        $valores_trimestre = array();
        $valores_gestion   = array();

        $suma_trm_porcentajes  = 0;
        $total_trm_operaciones = 0;
        $suma_porcentajes      = 0;
        $total_operaciones     = 0;

        // 2. Primero armamos únicamente las filas de la tabla y calculamos los acumuladores
        $filas_tabla = '';
        $nro = 0;
        foreach ($form2 as $row) {
            $nro++;
            $cod_ope   = 'OPE ' . $row['og_codigo'] . '.' . $row['or_codigo'];
            $campo_trm = 'cump_trm' . $trm_indice;
            $campo_ges = 'cump_anual' . $trm_indice;

            $porc_trm = isset($row[$campo_trm]) ? round(floatval($row[$campo_trm]), 2) : 0;
            $porc_ges = isset($row[$campo_ges]) ? round(floatval($row[$campo_ges]), 2) : 0;

            $val_trm_tope = ($porc_trm > 100) ? 100 : $porc_trm;
            $val_ges_tope = ($porc_ges > 100) ? 100 : $porc_ges;

            // Color barra TRIMESTRAL
            $color_trm = '#ef4444'; // Rojo (0% a 50%)
            if ($porc_trm > 50 && $porc_trm <= 80) {
                $color_trm = '#f59e0b'; // Amarillo (51% a 80%)
            } elseif ($porc_trm > 80) {
                $color_trm = '#10b981'; // Verde (> 80%)
            }

            // Color barra ANUAL / GESTIÓN
            $color_ges = '#ef4444'; // Rojo (0% a 50%)
            if ($porc_ges > 50 && $porc_ges <= 80) {
                $color_ges = '#f59e0b'; // Amarillo (51% a 80%)
            } elseif ($porc_ges > 80) {
                $color_ges = '#10b981'; // Verde (> 80%)
            }

            $categorias[] = $cod_ope;

            $valores_trimestre[] = array(
                'y'           => $val_trm_tope,
                'color'       => $color_trm,
                'borderColor' => null,
                'borderWidth' => 0
            );

            $valores_gestion[] = array(
                'y'           => $val_ges_tope,
                'color'       => $color_ges,
                'borderColor' => null,
                'borderWidth' => 0
            );

            $suma_trm_porcentajes += $val_trm_tope;
            $total_trm_operaciones++;

            $suma_porcentajes += $val_ges_tope;
            $total_operaciones++;

            $filas_tabla .= '
            <tr>
                <td class="text-center">'.$nro.'</td>
                <td class="text-center"><b>'.$row['og_codigo'].'</b></td>
                <td class="text-center"><b>'.$row['or_codigo'].'</b></td>
                <td>'.$row['or_objetivo'].'</td>
                <td class="text-right" style="background-color: #f0f9ff;"><b>'.round($porc_trm, 2).' %</b></td>
                <td class="text-right" style="background-color: #f0fdf4;"><b>'.round($porc_ges, 2).' %</b></td>
            </tr>';
        }

        // 3. Calcular promedios globales y colores de los banners
        $promedio_trm    = ($total_trm_operaciones > 0) ? round(($suma_trm_porcentajes / $total_trm_operaciones), 1) : 0;
        $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

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

        // 4. AHORA que todas las variables existen, armamos el HTML completo con banners + gráficos + tabla
        $tabla = '
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div id="banner_calificacion_acp_trm" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner_trm.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp_trm">'.$promedio_trm.'%</div>
                    <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp_trm">CALIFICACIÓN TRIMESTRAL: '.$calificacion_trm.'</div>
                </div>
                <div id="container1" style="width: 100%; min-height: 300px;"></div>
            </article>

            <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                <div id="banner_calificacion_acp" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp">'.$promedio_global.'%</div>
                    <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp">CALIFICACIÓN ANUAL: '.$calificacion.'</div>
                </div>
                <div id="container2" style="width: 100%; min-height: 300px;"></div>
            </article>
        </div>
        <hr>
        <div class="table-responsive">
            <table class="table table-bordered" border="0.2" style="width:100%;" id="datos">
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
                <tbody>
                    '.$filas_tabla.'
                </tbody>
            </table>
        </div>';

        // 5. Devolver respuesta JSON limpia
        $respuesta = array(
            'status'            => 'success',
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







  //// Exportar Detalle Formulario N° 2 Nacional/Distrital
  public function exportar_form2($dep_id = 0) {
      // 1. Limpieza de parámetro y ampliación de recursos
      $dep_id = intval($this->security->xss_clean($dep_id));
      @set_time_limit(1200);
      ini_set('memory_limit', '2048M');

      // 2. Extracción de datos según ámbito (0 = Institucional, >0 = Regional)
      if ($dep_id == 0) {
          $form2 = $this->model_objetivoregion->list_form2_regional_nacional();
          $tit   = 'Institucional';
      } else {
          $form2    = $this->model_objetivoregion->list_form2_regional_x_regional($dep_id);
          $regional = $this->model_proyecto->get_departamento($dep_id);
          $tit      = !empty($regional) ? str_replace(' ', '_', strtoupper($regional['dep_departamento'])) : 'Regional_' . $dep_id;
      }

      if (!empty($form2)) {
          $nombre_archivo = "Consolidado_Formulario_N2_" . $this->gestion . "_" . $tit . ".xls";

          // 3. Limpieza de buffers
          while (ob_get_level() > 0) {
              ob_end_clean();
          }
          ob_start();

          // 4. Cabeceras HTTP para descarga en Excel
          header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
          header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
          header("Cache-Control: max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
          header("Pragma: public");
          header("Expires: 0");

          // 5. BOM UTF-8 y despacho de la tabla corregida
          echo "\xEF\xBB\xBF";
          echo $this->stream_lista_excel_operaciones($form2);
          
          ob_end_flush();
          exit;
      } else {
          echo "<center><br><h3>🚨 Error de Consistencia: No se registran datos disponibles del Formulario N° 2 (" . $tit . ") para la gestión " . $this->gestion . ".</h3></center>";
      }
  }



    //// matriz formulario para exportar N| 2 2027
    private function stream_lista_excel_operaciones($form2) {
      $tabla = '
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      <table border="1" cellpadding="4" cellspacing="0">
          <thead>
              <tr style="background-color: #1a237e; color: #ffffff; font-weight: bold; height: 35px; font-family: Arial, sans-serif; font-size: 9px; text-transform: uppercase;">
                  <th style="background-color: #1a237e; color: #ffffff;">REG.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">REGIONAL</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRIORI.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. ACP.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. OPE.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">OBJETIVO REGIONAL</th>
                  <th style="background-color: #1a237e; color: #ffffff;">RESULTADO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">INDICADOR</th>
                  <th style="background-color: #1a237e; color: #ffffff;">MEDIO DE VERIFICACION</th>
                  <th style="background-color: #1a237e; color: #ffffff;">UNIDADES ALINEADAS</th>
                  <th style="background-color: #1a237e; color: #ffffff;">META</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TIPO INDICADOR</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TIPO OPERACIÓN</th>
                  <th style="background-color: #1a237e; color: #ffffff;">NRO. ALI.</th>

                  <th style="background-color: #1a237e; color: #ffffff;">PROG. 1</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PROG. 2</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PROG. 3</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PROG. 4</th>

                  <th style="background-color: #1a237e; color: #ffffff;">EJEC. 1</th>
                  <th style="background-color: #1a237e; color: #ffffff;">EJEC. 2</th>
                  <th style="background-color: #1a237e; color: #ffffff;">EJEC. 3</th>
                  <th style="background-color: #1a237e; color: #ffffff;">EJEC. 4</th>

                  <th style="background-color: #1a237e; color: #ffffff;">(%) TRM. 1</th>
                  <th style="background-color: #1a237e; color: #ffffff;">(%) TRM. 2</th>
                  <th style="background-color: #1a237e; color: #ffffff;">(%) TRM. 3</th>
                  <th style="background-color: #1a237e; color: #ffffff;">(%) TRM. 4</th>

                  <th style="background-color: #0284c7; color: #ffffff;">(%) PROG. ANUAL 1</th>
                  <th style="background-color: #0284c7; color: #ffffff;">(%) PROG. ANUAL 2</th>
                  <th style="background-color: #0284c7; color: #ffffff;">(%) PROG. ANUAL 3</th>
                  <th style="background-color: #0284c7; color: #ffffff;">(%) PROG. ANUAL 4</th>

                  <th style="background-color: #1c7368; color: #ffffff;">(%) CUMP. ANUAL 1</th>
                  <th style="background-color: #1c7368; color: #ffffff;">(%) CUMP. ANUAL 2</th>
                  <th style="background-color: #1c7368; color: #ffffff;">(%) CUMP. ANUAL 3</th>
                  <th style="background-color: #1c7368; color: #ffffff;">(%) CUMP. ANUAL 4</th>
              </tr>
          </thead>
          <tbody style="font-family: Arial, sans-serif; font-size: 8.5px;">';

      foreach ($form2 as $row) {
          $uresponsables = $this->model_objetivoregion->get_urep_oregional($row['or_id']);
          
          $priori = (isset($row['or_priorizado']) && intval($row['or_priorizado']) === 1) ? 'SÍ' : 'NO';
          $tp_ope = (intval($row['or_tp']) === 1) ? 'INVERSIÓN' : 'GASTO CORRIENTE';

          $color='';
          if($row['nro']==0){
            $color='red';
          }

          // Respaldo de nombres de columna según el modelo/función SQL que alimente $form2
          $indicador = !empty($row['or_indicador']) ? $row['or_indicador'] : (!empty($row['prod_indicador']) ? $row['prod_indicador'] : 'S/R');
          $tp_indi   = !empty($row['indi_descripcion']) ? $row['indi_descripcion'] : (!empty($row['indi_abreviacion']) ? $row['indi_abreviacion'] : 'S/R');

          // Concatenación limpia de unidades responsables
          $lista_ue = array();
          if (!empty($uresponsables)) {
              foreach ($uresponsables as $row_ue) {
                  $lista_ue[] = trim($row_ue['tipo'] . ' ' . $row_ue['proy_nombre'] . ' ' . $row_ue['abrev']);
              }
          }
          $uresp_txt = !empty($lista_ue) ? implode(' - ', $lista_ue) : 'S/R';

          $tabla .= '<tr>';
          $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . str_pad($row['dep_id'], 2, '0', STR_PAD_LEFT) . '</td>';
          $tabla .= '<td><b>' . strtoupper(htmlspecialchars(!empty($row['dep_departamento']) ? $row['dep_departamento'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</b></td>';
          $tabla .= '<td style="text-align: center; font-weight: bold;">' . $priori . '</td>';
          
          // Códigos ACP y OPE corregidos (antes apuntaban a dist_cod)
          $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;"><b>' . (!empty($row['og_codigo']) ? $row['og_codigo'] : '0') . '</b></td>';
          $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;"><b>' . (!empty($row['or_codigo']) ? $row['or_codigo'] : '0') . '</b></td>';
          
          $tabla .= '<td>' . strtoupper(htmlspecialchars(!empty($row['or_objetivo']) ? $row['or_objetivo'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</td>';
          $tabla .= '<td>' . strtoupper(htmlspecialchars(!empty($row['or_resultado']) ? $row['or_resultado'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</td>';
          $tabla .= '<td>' . strtoupper(htmlspecialchars($indicador, ENT_QUOTES, 'UTF-8')) . '</td>';
          $tabla .= '<td>' . strtoupper(htmlspecialchars(!empty($row['or_verificacion']) ? $row['or_verificacion'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</td>';
          $tabla .= '<td style="text-align: justify;">' . strtoupper(htmlspecialchars($uresp_txt, ENT_QUOTES, 'UTF-8')) . '</td>';

          $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right;">' . round((isset($row['or_meta']) ? floatval($row['or_meta']) : 0),2) . '</td>';
          $tabla .= '<td>' . strtoupper(htmlspecialchars($tp_indi, ENT_QUOTES, 'UTF-8')) . '</td>';
          $tabla .= '<td style="text-align: center; font-weight: bold;">' . $tp_ope . '</td>';
          $tabla .= '<td style="vnd.ms-excel.numberformat:0; text-align: center;">' . round((isset($row['nro']) ? intval($row['nro']) : 0),2) . '</td>';

          // PROG. 1 a 4
          for ($i = 1; $i <= 4; $i++) { 
              $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right; background-color:'.$color.'">' . round((isset($row['trm'.$i]) ? floatval($row['trm'.$i]) : 0),2) . '</td>';
          }

          // EJEC. 1 a 4
          for ($i = 1; $i <= 4; $i++) { 
              $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right; background-color:'.$color.'">' . round((isset($row['e_trm'.$i]) ? floatval($row['e_trm'.$i]) : 0),2) . '</td>';
          }

          // (%) TRM. 1 a 4
          for ($i = 1; $i <= 4; $i++) { 
              $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right; background-color:'.$color.'"><b>' . round((isset($row['porc_cump_'.$i]) ? floatval($row['porc_cump_'.$i]) : 0),2) . '</b></td>';
          }

          // (%) PROG. ANUAL 1 a 4 (Alineación exacta con las cabeceras)
          for ($i = 1; $i <= 4; $i++) { 
              $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right; background-color: #f0f9ff;">' . round((isset($row['trm_porc'.$i]) ? floatval($row['trm_porc'.$i]) : 0),2) . '</td>';
          }

          // (%) CUMP. ANUAL 1 a 4
          for ($i = 1; $i <= 4; $i++) { 
              $tabla .= '<td style="vnd.ms-excel.numberformat:0.00; text-align: right; background-color: #f0fdf4;"><b>' . round((isset($row['trm_porc_ejec'.$i]) ? floatval($row['trm_porc_ejec'.$i]) : 0),2) . '</b></td>';
          }

          $tabla .= '</tr>';
      }

      $tabla .= '
          </tbody>
      </table>';

      return $tabla;
  }



  /*=== GENERAR MENU ===*/
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

  /*------ NOMBRE MES -------*/
  public function mes_nombre(){
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