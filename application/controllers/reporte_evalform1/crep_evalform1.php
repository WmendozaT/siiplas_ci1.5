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
    $tabla='';
      $form1 = $this->model_objetivogestion->lista_alineacion_Form1_a_Form2_total_institucional(); /// form 1 -> operaciones priorizados
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
              <td class="text-center" style="font-size:15px;"><b>'.$row['og_codigo'].'</b></td>
              <td><b>'.$row['og_objetivo'].'</b></td>
              <td>'.$row['og_producto'].'</td>
              <td class="text-right" style="background-color: #f0f9ff;"><b>'.round($porc_trm, 2).' %</b></td>
              <td class="text-right" style="background-color: #f0fdf4;"><b>'.round($porc_ges, 2).' %</b></td>
              <td class="text-center"></td>
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
                <ul id="myTab1" class="nav nav-tabs bordered">
                    <li class="active">
                        <a href="#sA" data-toggle="tab"><b>(%) Cumplimiento de Operaciones</b></a>
                    </li>
                </ul>

                <div id="myTabContent1" class="tab-content padding-10">
                    <div class="tab-pane fade in active" id="sA"> 
                      <div class="row">
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="banner_calificacion_acp_trm" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner_trm.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                  <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp_trm">'.$promedio_trm.'%</div>
                                  <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp_trm">TRIMESTRAL: '.$calificacion_trm.'</div>
                              </div>
                              <div id="container1" style="width: 100%; min-height: 300px;"></div>
                          </article>
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="banner_calificacion_acp" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: '.$color_banner.'; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                  <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_acp">'.$promedio_global.'%</div>
                                  <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_acp">ANUAL: '.$calificacion.'</div>
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
                                      <th style="width:5%;color:#FFF; text-align: center" bgcolor="#1c7368">(%) CUMP. A LA GESTIÓN</th>
                                      <th style="width:2%;color:#FFF; text-align: center" bgcolor="#1c7368"></th>
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


  //  //// Reporte de Evaluacion Formulario N° 1
  // public function reporte_evaluacion_form1(){
  //   $matriz_trimestral=$this->matriz_cumplimiento_form1_institucional(0); /// trimestral
  //   $matriz_gestion=$this->matriz_cumplimiento_form1_institucional(1); /// acumulado Gestion
  //   $data['cabecera']=$this->acortoplazo->cabecera_acp();
  //   $data['detalle']=$this->detalle_cumplimiento_form1_institucional($matriz_trimestral,$matriz_gestion,1);
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

  //   $this->load->view('admin/reportes_cns/repevaluacion_form1/reporte_eval_form1', $data);
  // }


  // //// Grafico Cumplimiento Institucional
  // public function grafico_cumplimiento_institucional($titulo,$cumplimiento,$grafico,$subtitulo,$color){
  //   $tabla='      
  //   <script src="'.base_url().'assets/js/libs/jquery-2.0.2.min.js"></script>
  //   <script src="'.base_url().'assets/js/libs/jquery-ui-1.10.3.min.js"></script>
  //   <script src="'.base_url().'assets/highcharts/js/highcharts.js"></script>
  //   <script>
  //   $(function() {
  //   Highcharts.chart("'.$grafico.'", {
  //         chart: {
  //             type: "bar",
  //              backgroundColor: "#f7f7f7",
  //              spacing: [40, 20, 15, 45],
  //              style: {
  //              fontFamily: "Segoe UI, Arial, sans-serif"
  //           }
  //         },
  //         title: {
  //             text: "<b>EVALUACIÓN A.C.P. INSTITUCIONAL</b>",
  //             align: "center",
  //             style: {
  //               color: "#1e293b",
  //               fontSize: "20px",
  //               fontWeight: 600
  //             },
  //             margin: 30
  //         },
  //         subtitle: {
  //             text: "<b>'.$subtitulo.'</b>",
  //             align: "center"
  //         },
  //         xAxis: {
  //             categories: '.json_encode($titulo).',
  //             title: {
  //                 text: null
  //             },
  //             gridLineWidth: 1,
  //             lineWidth: 0
  //         },
  //         yAxis: {
  //             min: 0,
  //             title: {
  //                 text: "'.$subtitulo.'",
  //                 align: "high"
  //             },
  //             labels: {
  //                 overflow: "justify"
  //             },
  //             gridLineWidth: 0
  //         },
  //         tooltip: {
  //             valueSuffix: " %"
  //         },
  //         plotOptions: {
  //             bar: {
  //                 borderRadius: "50%",
  //                 dataLabels: {
  //                     enabled: true,
  //                     format: "{point.y:.1f}%",
  //                     color: "#1e293b"
  //                 },
  //                 groupPadding: 0.1
  //             }
  //         },
  //         legend: {
  //             layout: "vertical",
  //             align: "right",
  //             verticalAlign: "top",
  //             x: -40,
  //             y: 80,
  //             floating: true,
  //             borderWidth: 1,
  //             backgroundColor:
  //                 Highcharts.defaultOptions.legend.backgroundColor || "#FFFFFF",
  //             shadow: true
  //         },
  //         credits: {
  //             enabled: false
  //         },
  //         series: [{
  //             name: "(%) CUMPLIMIENTO A.C.P.",
  //             color: "'.$color.'",
  //             data: '.json_encode($cumplimiento).'
  //         }]
  //     });
  //   });
  //   </script>';

  //   return $tabla;
  // }






  // //// Calificacion Cumplimiento ACP Institucional
  // public function calificacion_form1_institucional($tp_rep,$tp){
  //   $lista_acp=$this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2();

  //    $eficacia=0;
  //    foreach($lista_acp as $row){
  //     $cumplimiento=$this->get_cumplimiento_acp($row['og_id'],$tp);
  //       $eficacia=$eficacia+$cumplimiento[1]; /// cumplimiento trimestral/Acumulado
  //    }

  //    if(count($lista_acp)!=0){
  //     $eficacia=round(($eficacia/count($lista_acp)),2);
  //    }


  //   $tp='danger';
  //   $titulo='ERROR EN LOS VALORES';
  //   if($eficacia<=50){$tp='danger';$titulo='CUMPLIMIENTO INSTITUCIONAL : '.$eficacia.'% -> INSATISFACTORIO (0% - 50%)';} /// Insatisfactorio - Rojo
  //   if($eficacia > 50 & $eficacia <= 75){$tp='warning';$titulo='CUMPLIMIENTO INSTITUCIONAL : '.$eficacia.'% -> REGULAR (51% - 75%)';} /// Regular - Amarillo
  //   if($eficacia > 75 & $eficacia <= 99){$tp='info';$titulo='CUMPLIMIENTO INSTITUCIONAL : '.$eficacia.'% -> BUENO (76% - 99%)';} /// Bueno - Azul
  //   if($eficacia > 99 & $eficacia <= 101){$tp='success';$titulo='CUMPLIMIENTO INSTITUCIONAL : '.$eficacia.'% -> OPTIMO (100%)';} /// Optimo - verde

  //   $tabla='<h5 class="alert alert-'.$tp.'" style="font-family: Arial;" align="center"><b>'.$titulo.'</b></h5>';

  //   return $tabla;
  // }



  // //// Semaforo de cumplimiento para las acciones de corto plazo
  // public function semaforo_cumplimiento_acp($valor,$tp){
  //   /// tp: 0 (vista), 1 (reporte)
  //   $tabla='';


  //   if($tp==0){
  //     if($valor > 0 & $valor <= 50){$tabla.='<img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:40%;">';} /// Insatisfactorio - Rojo
  //     if($valor > 50 & $valor <= 75){$tabla.='<img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:40%;">';} /// Regular - Amarillo
  //     if($valor > 75 & $valor <= 99){$tabla.='<img src="'.base_url('assets/ifinal/celeste.JPG').'" style="width:40%;">';} /// Bueno - Azul
  //     if($valor > 99 & $valor <= 101){$tabla.='<img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:40%;">';} /// Optimo - verde
  //   }
  //   else{
  //   if($valor > 0 & $valor <= 50){$tabla.='<img src="'.getcwd().'/assets/ifinal/rojo1.JPG" style="width:50%;">';} /// Insatisfactorio - Rojo
  //   if($valor > 50 & $valor <= 75){$tabla.='<img src="'.getcwd().'/assets/ifinal/amarillo.JPG" style="width:50%;">';} /// Regular - Amarillo
  //   if($valor > 75 & $valor <= 99){$tabla.='<img src="'.getcwd().'/assets/ifinal/celeste.JPG" style="width:50%;">';} /// Bueno - Azul
  //   if($valor > 99 & $valor <= 101){$tabla.='<img src="'.getcwd().'/assets/ifinal/verde.JPG" style="width:50%;">';} /// Optimo - verde
  //   }

  //   return $tabla;
  // }


  // //// Detalle Ejecucion ACP Institucional nuevo
  // public function detalle_cumplimiento_form1_institucional($matriz,$matriz_gestion,$tp_rep){
  //   /// tp_rep : 0 (vista)
  //   /// tp_rep : 1 (impresion)
  //   $tabla='';

  //   if($tp_rep==0){
  //   $tabla.='
  //     <center>
  //       <table class="table table-bordered" style="width:93%;">
  //         <thead>
  //           <tr style="text-align:center;">
  //             <th style="text-align:center;"></th>
  //             <th style="text-align:center;">ACCIÓN DE CORTO PLAZO</th>
  //             <th style="text-align:center;">INDICADOR</th>
  //             <th style="text-align:center;">META</th>
  //             <th style="text-align:center;">EJECUCIÓN</th>
  //             <th style="text-align:center;">(%) CUMPLIMIENTO GESTIÓN</th>
  //             <th style="text-align:center;"></th>
  //           </tr>
  //         </thead>
  //         <tbody>';
  //         $suma_trimestral=0;$suma_acumulado=0;
  //         for ($i=0; $i < count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2()); $i++) { 
  //           $tabla.='
  //           <tr>
  //             <th style="width:5%; font-size:18px;"><b>'.$matriz[$i][1].'</b></th>
  //             <td style="width:30%;">'.$matriz[$i][2].'</td>
  //             <td style="width:30%;">'.$matriz[$i][4].'</td>
  //             <td style="width:5%;font-size:15px; text-align:right;">'.round($matriz[$i][5],2).'</td>
  //             <td style="width:5%;font-size:15px; text-align:right;">'.round($matriz_gestion[$i][6],2).'</td>
  //             <td style="width:5%; color:#14665c; font-size:15px; text-align:right;"><b>'.$matriz_gestion[$i][3].' %</b></td>
  //             <td style="width:5%; text-align:center;">'.$this->semaforo_cumplimiento_acp($matriz_gestion[$i][3],0).'</td>
              
  //           </tr>';
  //         $suma_trimestral=$suma_trimestral+$matriz[$i][3];
  //         $suma_acumulado=$suma_acumulado+$matriz_gestion[$i][3];
  //         }
  //         $tabla.='
  //         </tbody>
  //         <tr>
  //           <td style="font-size:15px;text-align:right;" colspan=3><b>TOTAL CUMPLIMIENTO</b></td>
  //           <td></td>
  //           <td></td>
  //           <td style="font-size:16px; text-align:right;"><b>'.round(($suma_acumulado/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //           <td></td>
  //       </tr>
  //       </table>
     
  //       <table style="width:40%;" border=0>
  //         <tr>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:18%;"><br> <b>INSATISFACTORIO (0% - 50%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:20%;"><br> <b>REGULAR (51% - 75%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/celeste.JPG').'" style="width:20%;"><br> <b>BUENO (76% - 99%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:20%;"><br> <b>OPTIMO (100%)</b>
  //           </td>
  //         </tr>
  //       </table>
  //     </center>';
  //   }
  //   else{
  //     $tabla.='
    
  //         <style>
  //           table{font-size: 9px;
  //             font-family: Arial;
  //           }
  //           th{
  //             padding: 1.4px;
  //             text-align: center;
  //             font-size: 10px;
  //           }
  //         </style>
  //       <table cellpadding="0" cellspacing="0" class="tabla" border="0" style="width:100%;" align=center>
  //         <tr>
  //           <td style="width:96%; font-size:12px;">
  //             <b>CUMPLIMIENTO DE ACCIONES DE CORTO PLAZO AL '.$this->trimestre[0]['trm_descripcion'].'</b>
  //           </td>
  //         </tr>
  //       </table>
  //       <br>
  //       <table cellpadding="0" cellspacing="0" class="tabla" border="0.2" style="width:100%;" align=center>
  //         <thead>
  //           <tr>
  //             <th style="width:5%;">COD.</th>
  //             <th style="width:35%;">DETALLE A.C.P. INSTITUCIONAL</th>
  //             <th style="width:35%;">INDICADOR</th>
  //             <th style="width:8%;">(%) CUMP. TRIM.</th>
  //             <th style="width:5%;"></th>
  //             <th style="width:8%;">(%) CUMP. GESTION</th>
  //           </tr>
  //         </thead>
  //         <tbody>';
  //         $suma_trimestral=0;$suma_acumulado=0;
  //       for ($i=0; $i < count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2()); $i++) { 
  //         $tabla.='
  //         <tr>
  //           <td style="font-size:9px;width:5%; height:20px;text-align:center;">'.$matriz[$i][1].'</td>
  //           <td style="font-size:9px;width:35%;">'.$matriz[$i][2].'</td>
  //           <td style="font-size:9px;width:35%;">'.$matriz[$i][2].'</td>
  //           <td style="font-size:12px;width:8%;text-align:right"><b>'.$matriz[$i][3].' %</b></td>
  //           <th style="width:5%;">'.$this->semaforo_cumplimiento_acp($matriz[$i][3],1).'</th>
  //           <td style="font-size:12px;width:8%;text-align:right"><b>'.$matriz_gestion[$i][3].' %</b></td>
  //         </tr>';
  //         $suma_trimestral=$suma_trimestral+$matriz[$i][3];
  //         $suma_acumulado=$suma_acumulado+$matriz_gestion[$i][3];
  //       }
  //       $tabla.='
  //         </tbody>
  //         <tr>
  //           <td style="font-size:12px;height:25px;text-align:right;" colspan=3><b> CUMPLIMIENTO INSTITUCIONAL</b></td>
  //           <td style="font-size:12px; text-align:right"><b>'.round(($suma_trimestral/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //           <th style="width:5%;"></th>
  //           <td style="font-size:12px; text-align:right"><b>'.round(($suma_acumulado/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //         </tr>
  //       </table>
  //       <br><br>
  //         <table cellpadding="0" cellspacing="0" class="tabla" border=0 style="width:70%;" align=left>
  //           <tbody>
  //             <tr>
  //               <td colspan=2>PARAMETROS DE CUMPLIMIENTO '.$this->gestion.' </td>
  //             </tr>
  //             <tr>
  //               <td colspan=2><hr></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/rojo1.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td style="width:30%;">ROJO: INSATISFACTORIO<b> entre (0 y 50)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/amarillo.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td style="width:30%;">AMARILLO: REGULAR<b> entre (51 y 75)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/celeste.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td>CELESTE: BUENO<b> entre (76 y 99)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/verde.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td>VERDE: ÓPTIMO<b> 100%</b></td>
  //             </tr>
  //             <tr>
  //               <td colspan=2><hr></td>
  //             </tr>
  //           </tbody>
  //         </table>
  //     ';
  //   }

  //   return $tabla;
  // }



  // //// Detalle Ejecucion ACP Institucional anterior
  // public function detalle_cumplimiento_form1_institucional2($matriz,$matriz_gestion,$tp_rep){
  //   /// tp_rep : 0 (vista)
  //   /// tp_rep : 1 (impresion)
  //   $tabla='';

  //   if($tp_rep==0){
  //   $tabla.='
  //     <center>
  //       <table class="table table-bordered" style="width:93%;">
  //         <thead>
  //           <tr style="text-align:center;">
  //             <th style="text-align:center;"></th>
  //             <th style="text-align:center;">ACCIÓN DE CORTO PLAZO</th>
  //             <th style="text-align:center;">INDICADOR</th>
  //             <th style="text-align:center;">(%) CUMPLIMIENTO TRIMESTRAL</th>
  //             <th style="text-align:center;"></th>
  //             <th style="text-align:center;">(%) CUMPLIMIENTO GESTIÓN</th>
  //             <th style="text-align:center;"></th>
  //             <th style="text-align:center;">META</th>
  //             <th style="text-align:center;">EJECUTADO TRIM.</th>
  //             <th style="text-align:center;">EJECUTADO ANUAL.</th>
  //           </tr>
  //         </thead>
  //         <tbody>';
  //         $suma_trimestral=0;$suma_acumulado=0;
  //         for ($i=0; $i < count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2()); $i++) { 
  //           $tabla.='
  //           <tr>
  //             <th style="width:5%; font-size:18px;"><b>'.$matriz[$i][1].'</b></th>
  //             <td style="width:40%;">'.$matriz[$i][2].'</td>
  //             <td style="width:25%;">'.$matriz[$i][4].'</td>
  //             <td style="width:5%; color:#0fbba2; font-size:15px; text-align:right;"><b>'.$matriz[$i][3].' %</b></td>
  //             <td style="width:5%; text-align:center;">'.$this->semaforo_cumplimiento_acp($matriz[$i][3],0).'</td>
  //             <td style="width:5%; color:#14665c; font-size:15px; text-align:right;"><b>'.$matriz_gestion[$i][3].' %</b></td>
  //             <td style="width:5%; text-align:center;">'.$this->semaforo_cumplimiento_acp($matriz_gestion[$i][3],0).'</td>
  //             <td>'.$matriz[$i][5].'</td>
  //             <td>'.$matriz[$i][6].'</td>
  //             <td>'.$matriz_gestion[$i][6].'</td>
  //           </tr>';
  //         $suma_trimestral=$suma_trimestral+$matriz[$i][3];
  //         $suma_acumulado=$suma_acumulado+$matriz_gestion[$i][3];
  //         }
  //         $tabla.='
  //         </tbody>
  //         <tr>
  //           <td style="font-size:15px;text-align:right;" colspan=3><b>TOTAL CUMPLIMIENTO</b></td>
  //           <td style="font-size:16px; text-align:right"><b>'.round(($suma_trimestral/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //           <td></td>
  //           <td style="font-size:16px; text-align:right;"><b>'.round(($suma_acumulado/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //           <td></td>
  //           <td></td>
  //           <td></td>
  //           <td></td>
  //       </tr>
  //       </table>
     
  //       <table style="width:40%;" border=0>
  //         <tr>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/rojo1.JPG').'" style="width:18%;"><br> <b>INSATISFACTORIO (0% - 50%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/amarillo.JPG').'" style="width:20%;"><br> <b>REGULAR (51% - 75%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/celeste.JPG').'" style="width:20%;"><br> <b>BUENO (76% - 99%)</b>
  //           </td>
  //           <td style="width:10%;" align=center>
  //             <img src="'.base_url('assets/ifinal/verde.JPG').'" style="width:20%;"><br> <b>OPTIMO (100%)</b>
  //           </td>
  //         </tr>
  //       </table>
  //     </center>';
  //   }
  //   else{
  //     $tabla.='
    
  //         <style>
  //           table{font-size: 9px;
  //             font-family: Arial;
  //           }
  //           th{
  //             padding: 1.4px;
  //             text-align: center;
  //             font-size: 10px;
  //           }
  //         </style>
  //       <table cellpadding="0" cellspacing="0" class="tabla" border="0" style="width:100%;" align=center>
  //         <tr>
  //           <td style="width:96%; font-size:12px;">
  //             <b>CUMPLIMIENTO DE ACCIONES DE CORTO PLAZO AL '.$this->trimestre[0]['trm_descripcion'].'</b>
  //           </td>
  //         </tr>
  //       </table>
  //       <br>
  //       <table cellpadding="0" cellspacing="0" class="tabla" border="0.2" style="width:100%;" align=center>
  //         <thead>
  //           <tr>
  //             <th style="width:5%;">COD.</th>
  //             <th style="width:35%;">DETALLE A.C.P. INSTITUCIONAL</th>
  //             <th style="width:35%;">INDICADOR</th>
  //             <th style="width:8%;">(%) CUMP. TRIM.</th>
  //             <th style="width:5%;"></th>
  //             <th style="width:8%;">(%) CUMP. GESTION</th>
  //           </tr>
  //         </thead>
  //         <tbody>';
  //         $suma_trimestral=0;$suma_acumulado=0;
  //       for ($i=0; $i < count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2()); $i++) { 
  //         $tabla.='
  //         <tr>
  //           <td style="font-size:9px;width:5%; height:20px;text-align:center;">'.$matriz[$i][1].'</td>
  //           <td style="font-size:9px;width:35%;">'.$matriz[$i][2].'</td>
  //           <td style="font-size:9px;width:35%;">'.$matriz[$i][2].'</td>
  //           <td style="font-size:12px;width:8%;text-align:right"><b>'.$matriz[$i][3].' %</b></td>
  //           <th style="width:5%;">'.$this->semaforo_cumplimiento_acp($matriz[$i][3],1).'</th>
  //           <td style="font-size:12px;width:8%;text-align:right"><b>'.$matriz_gestion[$i][3].' %</b></td>
  //         </tr>';
  //         $suma_trimestral=$suma_trimestral+$matriz[$i][3];
  //         $suma_acumulado=$suma_acumulado+$matriz_gestion[$i][3];
  //       }
  //       $tabla.='
  //         </tbody>
  //         <tr>
  //           <td style="font-size:12px;height:25px;text-align:right;" colspan=3><b> CUMPLIMIENTO INSTITUCIONAL</b></td>
  //           <td style="font-size:12px; text-align:right"><b>'.round(($suma_trimestral/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //           <th style="width:5%;"></th>
  //           <td style="font-size:12px; text-align:right"><b>'.round(($suma_acumulado/count($this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2())),2).' %</b></td>
  //         </tr>
  //       </table>
  //       <br><br>
  //         <table cellpadding="0" cellspacing="0" class="tabla" border=0 style="width:70%;" align=left>
  //           <tbody>
  //             <tr>
  //               <td colspan=2>PARAMETROS DE CUMPLIMIENTO '.$this->gestion.' </td>
  //             </tr>
  //             <tr>
  //               <td colspan=2><hr></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/rojo1.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td style="width:30%;">ROJO: INSATISFACTORIO<b> entre (0 y 50)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/amarillo.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td style="width:30%;">AMARILLO: REGULAR<b> entre (51 y 75)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/celeste.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td>CELESTE: BUENO<b> entre (76 y 99)%</b></td>
  //             </tr>
  //             <tr>
  //               <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/verde.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
  //               <td>VERDE: ÓPTIMO<b> 100%</b></td>
  //             </tr>
  //             <tr>
  //               <td colspan=2><hr></td>
  //             </tr>
  //           </tbody>
  //         </table>
  //     ';
  //   }

  //   return $tabla;
  // }





  // //// Matriz lista de cumplimiento de Form 1 Institucional 
  // public function matriz_cumplimiento_form1_institucional($tp){
  //   $lista_acp=$this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2();
  //   $matriz = array(); // O simplemente: $matriz = [];
  //    for ($i=0; $i <count($lista_acp); $i++) { 
  //     for ($j=0; $j <5 ; $j++) { 
  //       $matriz[$i][$j]=0;
  //     } 
  //    }

  //    //// acumulado
  //    $nro=0;
  //    foreach($lista_acp as $row){
  //     $cumplimiento=$this->get_cumplimiento_acp($row['og_id'],$tp);



  //       $matriz[$nro][0]=$row['og_id']; /// cod OG
  //       $matriz[$nro][1]='<b>ACP.- '.$row['og_codigo'].'</b>'; /// cod OG
  //       $matriz[$nro][2]=$row['og_objetivo']; /// detalle OG
  //       $matriz[$nro][3]=$cumplimiento[1]; /// cumplimiento trimestral/Acumulado %
  //       $matriz[$nro][4]=$row['og_indicador']; /// indicador del acp
  //       if($row['og_id']==72){
  //         $matriz[$nro][5]=$row['og_meta']; /// Meta programado a la gestion
  //       }
  //       else{
  //         $matriz[$nro][5]=$row['programado_total']; /// Meta programado a la gestion
  //       }
        
  //       $matriz[$nro][6]=$cumplimiento[2]; /// ejecucion acumulado
  //     $nro++;
  //    }

  //    return $matriz;
  // }

  // /// funcion para devolver el cumplimiento por ACP
  // public function get_cumplimiento_acp($og_id,$tp){
  //   /// tp : 0 (trimestre)
  //   /// tp : 1 (Gestion)

  //     $list_form2_alineado_a_acp=$this->model_objetivogestion->get_list_form2_x_ogestion_trimestral($og_id,$this->tmes);
  //   if($tp==1){
  //     $list_form2_alineado_a_acp=$this->model_objetivogestion->get_list_form2_x_ogestion($og_id);
  //   }

  //   $matriz[1]=0;$matriz[2]=0;
  //   $cumplimiento_acp=0;$ejecutado_abs=0;
  //   foreach($list_form2_alineado_a_acp as $f2){
  //       $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional_al_trimestre($f2['og_codigo'],$f2['or_codigo'],$this->tmes); /// Temporalidad Ejecutado trimestre
  //     if($tp==1){ 
  //       $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional($f2['og_codigo'],$f2['or_codigo']); /// Temporalidad Ejecutado Gestion
  //     }

  //     $ejec_form2_institucional=0;
  //     if(count($get_trm_ejec)!=0){
  //       $ejec_form2_institucional=$get_trm_ejec[0]['ejecutado'];

  //     } 

  //     $ejecutado=0; // %
  //     if($f2['programado_total']!=0){
  //       $ejecutado=round((($ejec_form2_institucional/$f2['programado_total'])*100),2); /// %

  //     }

  //     $cumplimiento_acp=$cumplimiento_acp+$ejecutado; /// %
  //     $ejecutado_abs=$ejecutado_abs+$ejec_form2_institucional; /// valor abs
  //   }
  //   ///-----------------------
    
  //   if(count($list_form2_alineado_a_acp)!=0){
  //     $cumplimiento_acp=round(($cumplimiento_acp/count($list_form2_alineado_a_acp)),2);
  //   }
    
  //   if($og_id==72){
  //     $cumplimiento_acp=8.33;
  //     $ejecutado_abs=1;
  //   }

  //   $matriz[1]=$cumplimiento_acp;
  //   $matriz[2]=$ejecutado_abs;

  //   return $matriz;
  // }




  // //// AYUDA MEMORIA DEL CUMPLIMIENTO DE LAS ACCIONES
  // public function matriz_cumplimiento_form1_institucional2(){
  //   $lista_acp=$this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2();
  //    for ($i=0; $i <count($lista_acp); $i++) { 
  //     for ($j=0; $j <4 ; $j++) { 
  //       $matriz[$i][$j]=0;
  //     } 
  //    }

  //    //// acumulado

  //    foreach($lista_acp as $row){
  //     $form2=$this->model_objetivogestion->get_list_form2_x_ogestion($row['og_id']);

  //     echo 'ACP'.$row['og_codigo'].' - ('.$row['og_id'].')<br>';
  //     ///-----------------------
  //     $cumplimiento=0;
  //     foreach($form2 as $f2){
  //       $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional($f2['og_codigo'],$f2['or_codigo']); /// Temporalidad Ejecutado
  //       $ejec_form2_institucional=0;
  //       if(count($get_trm_ejec)!=0){
  //         $ejec_form2_institucional=$get_trm_ejec[0]['ejecutado'];
  //       } 

  //       $ejecutado=0;
  //       if($f2['programado_total']!=0){
  //         $ejecutado=round((($ejec_form2_institucional/$f2['programado_total'])*100),2);
  //       }

  //       $cumplimiento=$cumplimiento+$ejecutado;

  //       echo ' ---- OPE'.$f2['og_codigo'].' - '.$f2['or_codigo'].' -> '.$f2['programado_total'].' ----> '.$ejecutado.'%<br>';
  //     }
  //     ///-----------------------
      
  //     if(count($form2)!=0){
  //       $cumplimiento=round(($cumplimiento/count($form2)),2);
  //     }
  //     echo "--------> ".$cumplimiento." %<br>";
  //    }

  //    echo "===========<br>";

  //    //// trimestral
  //    //$lista_acp=$this->model_objetivogestion->get_list_acp_institucional_alineados_a_form2_trimestral($this->tmes);
  //    foreach($lista_acp as $row){
  //     $form2=$this->model_objetivogestion->get_list_form2_x_ogestion_trimestral($row['og_id'],$this->tmes);

  //     echo 'ACP'.$row['og_codigo'].' - ('.$row['og_id'].')<br>';
  //     ///-----------------------
  //     foreach($form2 as $f2){
  //       $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional_al_trimestre($f2['og_codigo'],$f2['or_codigo'],$this->tmes); /// Temporalidad Ejecutado
  //       $ejec_form2_institucional=0;
  //       if(count($get_trm_ejec)!=0){
  //         $ejec_form2_institucional=$get_trm_ejec[0]['ejecutado'];
  //       } 

  //       $ejecutado=0;
  //       if($f2['programado_total']!=0){
  //         $ejecutado=round((($ejec_form2_institucional/$f2['programado_total'])*100),2);
  //       }

  //       echo ' ---- OPE'.$f2['og_codigo'].' - '.$f2['or_codigo'].' -> '.$f2['programado_total'].' ----> '.$ejecutado.'%<br>';
  //     }
  //     ///-----------------------

  //    }
  //    //return $matriz;
  // }

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