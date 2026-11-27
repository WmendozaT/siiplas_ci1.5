<?php
/*controlador para evaluacion ACP GESTION 2022*/
class Crep_evalform1 extends CI_Controller {  
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
            $this->load->library('lib_form1');
        }
        else{
            redirect('/','refresh');
        }
    }

  /// MENU EVALUACIÓN POA FORM 1
  public function menu_eval_acp(){
    $data['menu']=$this->menu(7); //// genera menu
    $tp_acp=1;
    $tabla='';
      if($tp_acp==0){
        $tit='';
        $form1 = $this->model_objetivogestion->lista_alineacion_Form1_a_Form2_total_institucional(); /// form 1 -> operaciones total
      }
      else{
        $tit='(priorizados)';
        $form1 = $this->model_objetivogestion->lista_alineacion_Form1_a_Form2_priorizados_total_institucional(); /// form 1 -> operaciones priorizados
      }
      
      
      $trimestre    = $this->model_evaluacion->trimestre();
      $trm_indice      = $this->tmes; // Trimestre activo (1, 2, 3 o 4)

          $categorias        = array();
          $valores_trimestre = array();
          $valores_gestion   = array();

          $suma_trm_porcentajes  = 0;
          $total_trm_operaciones = 0;

          $suma_porcentajes  = 0;
          $total_operaciones = 0;

        $filas_tabla = '';
        $nro = 0;
        foreach ($form1 as $row) {
            $nro++;
            $cod_ope   = 'ACP ' . $row['og_codigo'];
            $campo_trm = 'cump_trm' . $trm_indice;
            $campo_ges = 'cump_anual' . $trm_indice;

            $porc_trm = isset($row[$campo_trm]) ? round(floatval($row[$campo_trm]), 2) : 0;
            $porc_ges = isset($row[$campo_ges]) ? round(floatval($row[$campo_ges]), 2) : 0;

            $val_trm_tope = ($porc_trm > 100) ? 100 : $porc_trm;
            $val_ges_tope = ($porc_ges > 100) ? 100 : $porc_ges;

            // Color barra TRIMESTRAL
            $color_trm = '#ef4444'; // Rojo (0% a 50%)
            $semaforo_trm='<img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:40%;">';
            if ($porc_trm > 50 && $porc_trm <= 80) {
                $color_trm = '#f59e0b'; // Amarillo (51% a 80%)
                $semaforo_trm='<img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:40%;">';
            } elseif ($porc_trm > 80) {
                $color_trm = '#10b981'; // Verde (> 80%)
                $semaforo_trm='<img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:40%;">';
            }

            // Color barra ANUAL / GESTIÓN
            $color_ges = '#ef4444'; // Rojo (0% a 50%)
            $semaforo_ges='<img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:40%;">';
            if ($porc_ges > 50 && $porc_ges <= 80) {
                $color_ges = '#f59e0b'; // Amarillo (51% a 80%)
                $semaforo_ges='<img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:40%;">';
            } elseif ($porc_ges > 80) {
                $color_ges = '#10b981'; // Verde (> 80%)
                $semaforo_ges='<img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:40%;">';
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
              <td class="text-center" style="font-size:18px;"><b> ACP. - '.$row['og_codigo'].'</b></td>
              <td><b>'.$row['og_objetivo'].'</b></td>
              <td>'.$row['og_producto'].'</td>
              <td class="text-right" style="background-color: #f0f9ff;"><b>'.round($porc_trm, 2).' %</b></td>
              <td class="text-center">'.$semaforo_trm.'</td>
              <td class="text-right" style="background-color: #f0fdf4;"><b>'.round($porc_ges, 2).' %</b></td>
              <td class="text-center">'.$semaforo_ges.'</td>
            </tr>';
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
                <h2 style="margin-top: 0; font-weight: bold; color: #1e3a8a;">Evaluación del Formulario N° 1 (Acción de Corto Plazo) INSTITUCIONAL</h2>
                <h3 style="margin-top: 0; font-weight: bold; color: #000000;">Periodo de Evaluación : '.$trimestre[0]['trm_descripcion'].' / '.$this->gestion.'</h3>
                <ul id="myTab1" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#sA" data-toggle="tab"><b>(%) Cumplimiento Form. N° 1 '.$tit.'</b></a>
                    </li>
                </ul>

                <div id="myTabContent1" class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="sA"> 
                      
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
                          <a href="javascript:abreVentana(\''.site_url("").'/rep_eval_form1/'.$tp_acp.'\');" class="btn btn-primary btn-sm" style="background-color: #1e3a8a; border: none; font-weight: bold; padding: 6px 14px;">
                              <i class="fa fa-print"></i> REPORTE EVALUACIÓN (FORM N° 1)
                          </a>';

                          if($this->tp_adm==1){
                            $tabla.='<!-- Botón 4: Reporte Consolidado -->
                          <button type="button" id="btn_exportar_excel" class="btn btn-primary btn-sm" onclick="exportarExcelInstitucional_form1();" style="background-color: #15803d; border: none; font-weight: bold; padding: 7px 15px;">
                            <i class="fa fa-file-excel-o"></i> EXPORTAR EXCEL
                          </button>';
                          } 
                      $tabla.='    
                      </div>

                      <div class="row">
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
                          <table class="table table-bordered" border="0.2" style="width:100%;" id="datos">
                              <thead>
                                  <tr style="font-size: 11px;">
                                      <th style="width:1%;height:10px;color:#FFF; text-align: center" bgcolor="#1c7368">#</th>
                                      <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. ACP.</b></th>
                                      <th style="width:15%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>ACCIÓN DE CORTO PLAZO</b></th>
                                      <th style="width:11%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>PRODUCTO</b></th>
                                      <th style="width:5%;color:#FFF; text-align: center" bgcolor="#1c7368">(%) CUMP. AL TRIMESTRE</th>
                                      <th style="width:3%;color:#FFF; text-align: center" bgcolor="#1c7368"></th>
                                      <th style="width:5%;color:#FFF; text-align: center" bgcolor="#1c7368">(%) CUMP. A LA GESTIÓN</th>
                                      <th style="width:3%;color:#FFF; text-align: center" bgcolor="#1c7368"></th>
                                  </tr>
                              </thead>
                              <tbody>
                                  '.$filas_tabla.'
                              </tbody>
                          </table>
                      </div>
                  </div>
                </div>
            </div>
        </div>';
         // 2. Pasar el HTML y los datos JSON hacia la vista
        $desc_trimestre = !empty($trimestre) ? strtoupper($trimestre[0]['trm_descripcion']) : 'TRIMESTRE ' . $trm_indice;

        $data['tabla']             = $tabla;
        $data['gestion']           = $this->gestion;
        $data['desc_trimestre']    = $desc_trimestre;
        $data['categorias_json']   = json_encode($categorias);
        $data['valores_trm_json']  = json_encode($valores_trimestre);
        $data['valores_ges_json']  = json_encode($valores_gestion);

    $this->load->view('admin/reportes_cns/repevaluacion_form1/rep_menu', $data);
  }


  ////// Reporte de Evaluacion Formulario N° 1
  // public function reporte_evaluacion_form1($tp_acp){
  //   //$matriz_trimestral=$this->matriz_cumplimiento_form1_institucional(0); /// trimestral
  //   //$matriz_gestion=$this->matriz_cumplimiento_form1_institucional(1); /// acumulado Gestion
  //   $data['cabecera']=$this->lib_form1->cabecera_acp();
  //   $data['detalle']=$this->detalle_cumplimiento_form1_institucional($tp_acp);
  //   $data['pie']='
  //   <hr>
  //   <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%; font-size:8px;" align="center">
  //     <tr>
  //       <td style="width: 33%; height:18px;text-align: left">
  //         POA - '.$this->session->userdata('gestion').". ".$this->session->userdata('rd_poa').'
  //       </td>
  //       <td style="width: 33%; text-align: center">
  //         '.$this->session->userdata('sistema').'
  //       </td>
  //       <td style="width: 33%; text-align: right">
  //         '.$this->session->userdata('funcionario').' - pag. [[page_cu]]/[[page_nb]]
  //       </td>
  //     </tr>
  //   </table>';

  //  // $this->load->view('admin/reportes_cns/repevaluacion_form1/reporte_eval_form1', $data);
  // }



    public function reporte_evaluacion_form1($tp_acp){
      // 1. Ampliación y control de recursos de hardware en el servidor
      ini_set('memory_limit', '2048M'); 
      set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
      
      // Limpieza preliminar del búfer de salida para proteger el binario del PDF
      if (ob_get_length()) ob_clean();
            $pie_report='EVALUACION_FORM1_INSTITUCIONAL_'.$this->gestion;
            $listado=$this->detalle_cumplimiento_form1_institucional($tp_acp);;
            $cabecera=$this->lib_form1->cabecera_acp();

            $pie='
            <hr>
            <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%; font-size:8px;" align="center">
              <tr>
                <td style="width: 33%; height:18px;text-align: left">
                  POA - '.$this->session->userdata('gestion').". ".$this->session->userdata('rd_poa').'
                </td>
                <td style="width: 33%; text-align: center">
                  '.$this->session->userdata('sistema').'
                </td>
                <td style="width: 33%; text-align: right">
                  '.$this->session->userdata('funcionario').' - pag. [[page_cu]]/[[page_nb]]
                </td>
              </tr>
            </table>';

            
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

  // //// Detalle Ejecucion form 1 
  public function detalle_cumplimiento_form1_institucional($tp_acp){
      if($tp_acp==0){
        $tit='';
        $form1 = $this->model_objetivogestion->lista_alineacion_Form1_a_Form2_total_institucional(); /// form 1 -> operaciones total
      }
      else{
        $tit='(priorizados)';
        $form1 = $this->model_objetivogestion->lista_alineacion_Form1_a_Form2_priorizados_total_institucional(); /// form 1 -> operaciones priorizados
      }

    $tabla='';
    $tabla.='
    <table cellpadding="0" cellspacing="0" class="tabla" border="0" style="width:100%;" align=center>
          <tr>
            <td style="width:96%; font-size:12px;">
              <b>CUMPLIMIENTO DE ACCIONES DE CORTO PLAZO AL '.$this->trimestre[0]['trm_descripcion'].'</b>
            </td>
          </tr>
        </table>
        <br>
        <table cellpadding="0" cellspacing="0" class="tabla" border="0.2" style="width:100%;" align=center>
          <thead>
            <tr style="font-size: 6.7px;" bgcolor="#eceaea">
              <th style="width:2%; text-align:center;height:20px;">#</th>
              <th style="width:5%; text-align:center;">COD. ACP.</th>
              <th style="width:33%; text-align:center;">DETALLE A.C.P. INSTITUCIONAL</th>
              <th style="width:33%; text-align:center;">PRODUCTO</th>
              <th style="width:8%; text-align:center;">(%) CUMP. TRIM.</th>
              <th style="width:5%; text-align:center;"></th>
              <th style="width:8%; text-align:center;">(%) CUMP. GESTION</th>
              <th style="width:5%; text-align:center;"></th>
            </tr>
          </thead>
          <tbody>';
          $nro=0;
          foreach($form1 as $row){
            $semaforo_trm='<img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:50%;">';
            if ($row['cump_trm'.$this->tmes]> 50 && $row['cump_trm'.$this->tmes] <= 80) {
                $semaforo_trm='<img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:50%;">';
            } elseif ($row['cump_trm'.$this->tmes] > 80) {
                $semaforo_trm='<img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:50%;">';
            }

            $semaforo_ges='<img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:50%;">';
            if ($row['cump_anual'.$this->tmes] > 50 && $row['cump_anual'.$this->tmes] <= 80) {
                $semaforo_ges='<img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:50%;">';
            } elseif ($row['cump_anual'.$this->tmes] > 80) {
                $semaforo_ges='<img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:50%;">';
            }

            $nro++;
            $tabla.='
            <tr>
              <td style="width:2%; font-size:8px; text-align:center;">'.$nro.'</td>
              <td style="width:5%; font-size:10px; text-align:center;"><b>ACP. - '.$row['og_codigo'].'</b></td>
              <td style="width:33%; font-size:9px;">'.$row['og_objetivo'].'</td>
              <td style="width:33%; font-size:9px;">'.$row['og_producto'].'</td>
              <td style="width:8%; font-size:13px; text-align:right;"><b>'.round($row['cump_trm'.$this->tmes],2).'%</b></td>
              <td style="width:5%; font-size:10px; text-align:center;">'.$semaforo_trm.'</td>
              <td style="width:8%; font-size:13px; text-align:right;"><b>'.round($row['cump_anual'.$this->tmes],2).'%</b></td>
              <td style="width:5%; font-size:10px; text-align:center;">'.$semaforo_ges.'</td>
            </tr>';
          }
        $tabla.='
        </tbody>
      </table>
<br>
<table cellpadding="0" cellspacing="0" class="tabla" border=0 style="width:50%;" align=left>
      <tbody>
        <tr>
          <td colspan=2>CUMPLIMIENTO TRIMESTRAL ACUMULADO</td>
        </tr>
        <tr>
          <td colspan=2><hr></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/blanco.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td style="width:30%;">BLANCO:<b> Sin programación al Trimestre</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/rojo1.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td style="width:30%;">ROJO:<b> Entre 0% y 50%</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/amarillo.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td>AMARILLO:<b> Entre 51% y 80%</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/verde.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td>VERDE:<b> Entre 81% y 100%</b></td>
        </tr>
        <tr>
          <td colspan=2><hr></td>
        </tr>
      </tbody>
    </table>
      ';
    
    return $tabla;
  }




 //// Exportar Detalle Formulario N° 1 Nacional/Distrital
  public function exportar_form1() {
      // 1. Limpieza de parámetro y ampliación de recursos
      $dep_id = intval($this->security->xss_clean($dep_id));
      @set_time_limit(1200);
      ini_set('memory_limit', '2048M');

      // 2. Extracción de datos según ámbito (0 = Institucional, >0 = Regional)
      // if ($dep_id == 0) {
      //     $form2 = $this->model_objetivoregion->list_form2_regional_nacional();
      //     $tit   = 'Institucional';
      // } else {
      //     $form2    = $this->model_objetivoregion->list_form2_regional_x_regional($dep_id);
      //     $regional = $this->model_proyecto->get_departamento($dep_id);
      //     $tit      = !empty($regional) ? str_replace(' ', '_', strtoupper($regional['dep_departamento'])) : 'Regional_' . $dep_id;
      // }

      if (!empty($form2)) {
          $nombre_archivo = "Consolidado_Formulario_N1_" . $this->gestion . "_" . $tit . ".xls";

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
          //echo $this->stream_lista_excel_operaciones($form2);
          
          ob_end_flush();
          exit;
      } else {
          echo "<center><br><h3>🚨 Error de Consistencia: No se registran datos disponibles del Formulario N° 2 (" . $tit . ") para la gestión " . $this->gestion . ".</h3></center>";
      }
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


  /*======= GENERAR MENU ==========*/
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
  
}