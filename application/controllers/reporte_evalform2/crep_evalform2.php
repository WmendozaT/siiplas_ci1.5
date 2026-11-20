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
    //$data['modal_grafico_x_form2']=$this->eval_oregional->modal_graficos_x_form2();
    $this->load->view('admin/reportes_cns/repevaluacion_form2/rep_menu', $data);
  }



  /*-------- GET CUADRO EVALUACION FORM 2 INSTITUCIONAL --------*/
  public function get_cuadro_evaluacion_formulario2_institucional(){
    if($this->input->is_ajax_request() && $this->input->post()){
      $post = $this->input->post();
      $dep_id = $this->security->xss_clean($post['dep_id']); // dep id, 0: Nacional
        
      $titulo='<b>ACUMULADO INSTITUCIONAL</b>';
      
      $nro=count($this->model_proyecto->list_departamentos()); /// nro de regionales
      $matriz=$this->matriz_eval_form2(); /// Matriz
      $cabecera=$this->cabecera_reporte_grafico(); /// Cabecera Grafico
      $tabla_vista=$this->tabla_eval_form2($matriz,$nro,0); /// Tabla Vista
      $tabla_impresion=$this->tabla_eval_form2($matriz,$nro,1); /// Tabla Impresion

      //-------
      $matriz_form2_regresion=$this->tabla_trimestral_acumulado_institucional();
      $tabla_vista_acumulado=$this->get_tabla_cumplimiento_form2_priorizados_institucional(0);
      $tabla_vista_acumulado_impresion=$this->get_tabla_cumplimiento_form2_priorizados_institucional(1);

      //------
      $matriz3=$this->eval_oregional->matriz_cumplimiento_operaciones_institucional(); /// Matriz detalle operaciones institucional a la Gestion
      $matriz4=$this->eval_oregional->matriz_cumplimiento_operaciones_institucional_al_trimestre(); /// Matriz detalle operaciones institucional al Trimestre
      /// ---------------------------------------------------------------------------------------------
      $lista='';
      $lista.=' <div style="font-family: Arial;">DETALLE DE CUMPLIMIENTO DE OPERACIONES - '.$this->gestion.'</div>
                <ul>';
                  for ($i=0; $i <count($this->eval_oregional->matriz_cumplimiento_operaciones_institucional()); $i++) { 
                    $lista.='<li style="font-family: Arial;font-size: 10px;height: 1%;">OPE. '.$matriz3[$i][0].'.'.$matriz3[$i][1].' - <b>'.$matriz3[$i][4].' %</b></li>';
                  }
                  $lista.='
                </ul>
                <hr>';
      $lista_operaciones=$lista;
      //style="display: none"
      /// ----------------------------------------------------------------------------------------------

      $tabla='';
      $tabla='
      <div class="jarviswidget well" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
        <header>
          <div id="cabecera" style="display: none">'.$cabecera.'</div>
          <div id="calificacion"></div>
        </header>
        <div>
          <h2>Evaluación del Formulario N° 2 (Operaciones) - '.$titulo.'</h2>
            <div class="jarviswidget-editbox">
            </div>
            <div class="widget-body">
                <ul id="myTab1" class="nav nav-tabs bordered">
                  <li class="active">
                      <a href="#s1" data-toggle="tab"> (%) Cumplimiento de Operaciones por Regional</a>
                  </li>
                  <li>
                      <a href="#s2" data-toggle="tab"> Detalle de Cumplimiento</a>
                  </li>
                  <li>
                      <a href="#s3" data-toggle="tab"> Detalle por Operación</a>
                  </li>
                </ul>

                <div id="myTabContent1" class="tab-content padding-10">
                  <div class="tab-pane fade in active" id="s1">
                    <div align=center>
                      <div id="graf_detalle1">
                        <div id="grafico1" style="width: 950px; height: 550px; margin: 2 auto"></div>
                      </div>
                      '.$tabla_vista.' 
                    </div>
                    <div id="tabla_impresion_detalle1" style="display: none">
                     '.$tabla_impresion.'
                    </div>
                    <div align="right">
                      <button  onClick="imprimir_grafico1()" class="btn btn-default"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="30" HEIGHT="30"/></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                  </div>
                  
                  <div class="tab-pane fade" id="s2">
                    <div class="rows" align=center>
                      <div id="graf_detalle2">
                        <div id="grafico2" style="width: 900px; height: 550px; margin: 2 auto"></div>
                      </div>
                      '.$tabla_vista_acumulado.'
                    </div>
                    <div id="tabla_impresion_detalle2" style="display: none">
                     '.$tabla_vista_acumulado_impresion.'
                    </div>
                    <div align="right">
                      <button  onClick="imprimir_grafico2()" class="btn btn-default"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="30" HEIGHT="30"/></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                  </div>

                  <div class="tab-pane fade" id="s3">
                    <div class="rows" align=center>
                      <article class="col-sm-12 col-md-12 col-lg-6">
                      <div id="graf_detalle3">
                        <div id="grafico_trimestre" style="width: 900px; height: 800px; margin: 2 auto"></div>
                      </div>
                      </article>
                      <article class="col-sm-12 col-md-12 col-lg-6">
                        <div id="grafico3" style="width: 900px; height: 800px; margin: 2 auto"></div>
                      </article>
                      
                    </div>
                    <div id="tabla_impresion_detalle3" style="display: none">
                     '.$lista_operaciones.'
                    </div>
                    <div align="right">
                      <button  onClick="imprimir_grafico3()" class="btn btn-default"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="30" HEIGHT="30"/></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    </div>
                  </div>

                </div>

            </div>
          </div>
      </div>';

      $result = array(
        'respuesta' => 'correcto',
        'trimestre'=>$this->model_evaluacion->trimestre(),
        'gestion'=>$this->gestion,
        'titulo'=>$titulo,
        'tabla'=>$tabla,
        'nro'=>$nro,
        'matriz'=>$matriz,
        'matriz_regresion'=>$matriz_form2_regresion,
        
        'nro_form2'=>count($this->eval_oregional->matriz_cumplimiento_operaciones_institucional()),
        'matriz_form2'=>$matriz3, /// detalle form2 institucional a la gestion

        'nro_form2_trimestre'=>count($this->eval_oregional->matriz_cumplimiento_operaciones_institucional_al_trimestre()),
        'matriz_form2_trimestre'=>$matriz4, /// detalle form2 institucional al trimestre        

      );

      echo json_encode($result);
    }else{
        show_404();
    }
  }


  /*-------- GET CUADRO EVALUACION FORM 2 REGIONAL --------*/
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
              // Porcentaje Trimestral (porc_cump_1..4) y Gestión (trm_porc_ejec1..4)
              $campo_trm = 'porc_cump_' . $trm_indice;
              $campo_ges = 'trm_porc_ejec' . $trm_indice;

              $porc_trm = isset($row[$campo_trm]) ? round(floatval($row[$campo_trm]), 2) : 0;
              $porc_ges = isset($row[$campo_ges]) ? round(floatval($row[$campo_ges]), 2) : 0;

              $categorias[] = $cod_ope;

              // Tope visual en barras de 100%
              $valores_trimestre[] = ($porc_trm > 100) ? 100 : $porc_trm;
              $valores_gestion[]   = ($porc_ges > 100) ? 100 : $porc_ges;

              // El promedio global toma el cumplimiento trimestral
              $suma_trm_porcentajes += ($porc_trm > 100) ? 100 : $porc_trm; 
              $total_trm_operaciones++;

              // El promedio global toma el cumplimiento de gestión 
              $suma_porcentajes += ($porc_ges > 100) ? 100 : $porc_ges; 
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
      $tabla = '
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
                              <i class="fa fa-bar-chart"></i> IMPRIMIR GRÁFICO TRIMESTRAL
                          </button>

                          <!-- Botón 2: Gráfico Anual / Gestión -->
                          <button type="button" class="btn btn-success btn-sm" onclick="imprimirGraficoIndividual(\'grafico1\', \'calificacion\', \'Gestion\');" style="background-color: #1c7368; border: none; font-weight: bold; padding: 6px 12px;">
                              <i class="fa fa-bar-chart"></i> IMPRIMIR GRÁFICO GESTIÓN
                          </button>

                          <!-- Botón 3: Reporte Consolidado -->
                          <a href="javascript:abreVentana(\''.site_url("").'/rep_eval_oregional/'.$dep_id.'\');" class="btn btn-primary btn-sm" style="background-color: #1e3a8a; border: none; font-weight: bold; padding: 6px 14px;">
                              <i class="fa fa-print"></i> REPORTE EVALUACIÓN (FORM N° 2)
                          </a>
                      </div>

                      <div class="row">
                          <!-- COLUMNA 1: TRIMESTRAL -->
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="calificacion_trimestral" class="text-center" style="padding: 10px; border-radius: 5px; color: #fff; margin-bottom: 10px; background-color: '.$color_banner_trm.';">
                                  <div style="font-size: 26px; font-weight: bold; line-height: 1.1;">'.$promedio_trm.'%</div>
                                  <div style="font-size: 11px; font-weight: bold; letter-spacing: 1px;">CALIFICACIÓN TRIMESTRAL: '.$calificacion_trm.'</div>
                              </div>
                              <div style="border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; background: #fff;">
                                  <div id="grafico_trimestral" style="width: 100%; min-height: 550px;"></div>
                              </div>
                          </article>

                          <!-- COLUMNA 2: ANUAL / GESTIÓN -->
                          <article class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                              <div id="calificacion" class="text-center" style="padding: 10px; border-radius: 5px; color: #fff; margin-bottom: 10px; background-color: '.$color_banner.';">
                                  <div style="font-size: 26px; font-weight: bold; line-height: 1.1;">'.$promedio_global.'%</div>
                                  <div style="font-size: 11px; font-weight: bold; letter-spacing: 1px;">CALIFICACIÓN GESTIÓN: '.$calificacion.'</div>
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
          $suma_porcentajes  = 0;
          $total_operaciones = 0;

          // 2. CONSTRUIR CONTENEDORES DE GRÁFICOS Y TABLA
          $tabla = '
          <div id="tit_acp" class="alert alert-info" style="background-color: #f4f6f6; border-left: 5px solid #1c7368; color: #1e293b; padding: 10px 15px; margin-bottom: 15px;">
              <b>ACP. '.$acp_regional[0]['og_codigo'].' .- </b>'.$acp_regional[0]['og_objetivo'].'
          </div>
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
                  <tbody>';

          $nro = 0;
          foreach ($form2 as $row) {
              $nro++;
              // Etiqueta para el eje Y de Highcharts
              $cod_ope = 'OPE ' . $row['og_codigo'] . '.' . $row['or_codigo'];

              // Porcentaje Trimestral (porc_cump_1..4) y Gestión (trm_porc_ejec1..4)
              $campo_trm = 'porc_cump_' . $trm_indice;
              $campo_ges = 'trm_porc_ejec' . $trm_indice;

              $porc_trm = isset($row[$campo_trm]) ? round(floatval($row[$campo_trm]), 2) : 0;
              $porc_ges = isset($row[$campo_ges]) ? round(floatval($row[$campo_ges]), 2) : 0;

              // Tope visual en barras de 100%
              $valores_trimestre[] = ($porc_trm > 100) ? 100 : $porc_trm;
              $valores_gestion[]   = ($porc_ges > 100) ? 100 : $porc_ges;
              $categorias[]        = $cod_ope;

              // El promedio global toma el cumplimiento de gestión (o cambia a $porc_trm si evalúas por trimestre)
              $suma_porcentajes += ($porc_ges > 100) ? 100 : $porc_ges;
              $total_operaciones++;

              $tabla .= '
              <tr>
                  <td class="text-center">'.$nro.'</td>
                  <td class="text-center"><b>'.$row['og_codigo'].'</b></td>
                  <td class="text-center"><b>'.$row['or_codigo'].'</b></td>
                  <td>'.$row['or_objetivo'].'</td>
                  <td class="text-right" style="background-color: #f0f9ff;"><b>'.$porc_trm.' %</b></td>
                  <td class="text-right" style="background-color: #f0fdf4;"><b>'.$porc_ges.' %</b></td>
              </tr>';
          }

          $tabla .= '
                  </tbody>
              </table>
          </div>';

          // 3. CALCULAR PROMEDIO GLOBAL Y CALIFICACIÓN
          $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

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

  



  /*---- MATRIZ DETALLE DE CUMPLIMIENTO DE OPERACIONES POR ACP REGIONAL ---*/
  // public function matriz_cumplimiento_operaciones_acp_regional($og_id,$dep_id){
  //   $matriz='';
  //   $lista_form2=$this->model_objetivoregion->list_oregional_regional_graf2($og_id,$dep_id);

  //   $nro=0;
  //   foreach($lista_form2 as $row){
  //     $calificacion=$this->eval_oregional->calificacion_trimestral_acumulado_x_oregional($row['or_id'],$this->tmes,$row['or_tp'],$row['tp_meta']);
  //     $matriz[$nro][0]=$row['og_codigo'];
  //     $matriz[$nro][1]=$row['or_codigo'];
  //     $matriz[$nro][2]=$row['or_objetivo'];
  //     $matriz[$nro][3]=round($row['or_meta'],2);
  //     $matriz[$nro][4]=round($calificacion[3],2); /// cumplimiento al trimestre
  //     $matriz[$nro][5]=round($calificacion[4],2); /// cumplimiento a la gestion
  //     $matriz[$nro][6]=$row['or_id']; /// or_id
  //     $nro++;
  //   }
    
  //   return $matriz;
  // }


  /*---- LISTA DE ACP REGIONALES CON OPERACIONES POR REGIONAL trabajando 2027---*/
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
                                     onclick="nivel_cumplimiento_acp_regional('.$row['og_id'].','.$dep_id.'); return false;" 
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
                                $nro_form2++;
                                $tabla.='
                                <tr>
                                  <td style="font-size: 10px;font-family: Arial;text-align:center">'.$nro_form2.'</td>
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









  /*-- GENERA TABLA PARA EVALUACION TRIMESTRAL INSTITUCIONAL --*/
 //  public function tabla_trimestral_acumulado_institucional(){

 //    for ($i=0; $i <=4 ; $i++) { 
 //      $matriz[1][$i]=0;
 //      $matriz[2][$i]=0;
 //      $matriz[3][$i]=0;
 //      $matriz[4][$i]=0;
 //      $matriz[5][$i]=0;
 //      $matriz[6][$i]=0;
 //    }

 //    for ($i=1; $i <=4 ; $i++) { 
 //      $valor=$this->calificacion_trimestral_acumulado_institucional($i);
 //      $matriz[1][$i]=$valor[1];  /// prog
 //      $matriz[2][$i]=$valor[2];  /// ejec
 //      $matriz[3][$i]=$valor[3];  /// % cumplimiento trimestral
 //      $matriz[4][$i]=(100-$valor[3]);  /// % no cumplido
 //    }

 //    $total=$matriz[1][4];

 //    if($total!=0){
 //      for ($i=1; $i <=4 ; $i++) { 
 //        $matriz[5][$i]=round((($matriz[1][$i]/$total)*100),2);  /// % Programado con respecto al total acumulado
 //        $matriz[6][$i]=round((($matriz[2][$i]/$total)*100),2);  /// % Ejecutado con respecto al total acumulado
 //      }
 //    }
 //    else{
 //      for ($i=1; $i <=4 ; $i++) { 
 //        $matriz[5][$i]=0;  /// % Programado con respecto al total acumulado
 //        $matriz[6][$i]=0;  /// % Ejecutado con respecto al total acumulado
 //      }
 //    }

 //    return $matriz;
 //  }


 // /*-- CALIFICACION TRIMESTRAL INSTITUCIONAL --*/
 //  public function calificacion_trimestral_acumulado_institucional($trimestre){
 //    $valor = array( '1' => '0','2' => '0','3' => '0','4' => '0');

 //    if(count($this->model_objetivoregion->get_suma_total_prog_form2_institucional())!=0){
 //      $suma_total_prog=0; $suma_prog=0; $suma_ejec=0;
 //      //// Suma total programado por operacion
 //      $prog_total=$this->model_objetivoregion->get_suma_total_prog_form2_institucional();
 //      if(count($prog_total)!=0){
 //        $suma_total_prog=$prog_total[0]['programado_total'];
 //      }
 //      ///-----

 //      for ($i=1; $i <=$trimestre; $i++) {
 //        $get_trm=$this->model_objetivoregion->get_suma_trimestre_prog_form2_institucional($i); /// Temporalidad Programado
 //        $get_trm_ejec=$this->model_objetivoregion->get_suma_trimestre_ejec_form2_institucional($i); /// Temporalidad Ejecutado

 //        if(count($get_trm)!=0){
 //          $suma_prog=$suma_prog+$get_trm[0]['prog']; 
 //        }

 //        if(count($get_trm_ejec)!=0){
 //          $suma_ejec=$suma_ejec+$get_trm_ejec[0]['ejec'];
 //        }

 //        $ejecucion=0;
 //        if($suma_ejec!=0){
 //          $ejecucion=round((($suma_ejec/$suma_prog)*100),2);
 //        }

 //        $cumplimiento_gestion=0;
 //        if($suma_total_prog!=0){
 //          $cumplimiento_gestion=round((($suma_ejec/$suma_total_prog)*100),2);
 //        }
 //      }


 //      $valor[1]=$suma_prog; /// Programado Acumulado al trimestre
 //      $valor[2]=$suma_ejec; /// Ejecutado Acumulado al trimestre
 //      $valor[3]=$ejecucion; /// Cumplimiento al trimestre
 //      $valor[4]=$cumplimiento_gestion; /// Cumplimiento a la Gestion
 //    }

 //    return $valor; 
 //  }


  /*-- VISTA DE TABLA CUMPLIMUENTO PARA IMPRESION (ACUMULADO) --*/
  public function get_tabla_cumplimiento_form2_priorizados_institucional($tp_rep){
    /// tp_rep=0 normal
    /// tp_rep=1 Reporte

    $valor=$this->tabla_trimestral_acumulado_institucional();
    $tabla='';

    if($tp_rep==0){ /// VISTA NORMAL

      $tabla.='
      <center>
        <table class="table table-bordered" border=0.2 style="width:60%;">
          <thead>
            <tr align=center>
              <th style="width:20%; height:30px; text-align:center">I TRIMESTRE</th>
              <th style="width:20%;text-align:center">II TRIMESTRE</th>
              <th style="width:20%;text-align:center">III TRIMESTRE</th>
              <th style="width:20%;text-align:center">IV TRIMESTRE</th>
            </tr>
          </thead>
          <tbody>
            <tr>';
            for ($i=1; $i <=4 ; $i++) {
              $tabla.='
              <td style="width:6%;" align=center>
                <table class="table table-bordered" border=0.2 style="width:80%;">
                  <tr>
                    <td style="width:50%;"><b>(%) PROG.</b></td>
                    <td style="width:50%;font-size: 12px; color:blue" align=right><b>'.$valor[5][$i].'%</b></td>
                  </tr>
                  <tr>
                    <td><b>(%) CUMP.</b></td>
                    <td style="font-size: 12px; color:blue" align=right><b>'.$valor[6][$i].'%</b></td>
                  </tr>
                </table>
              </td>';
            }
            $tabla.='
            </tr>  
          </tbody>
        </table>
      </center>';
    }
    else{ /// VISTA PARA REPORTES
      $tabla.='
      <table class="change_order_items" border=1 style="width:100%;">
        <thead>
          <tr align=center>
            <th style="width:10%; text-align:center"></th>
            <th style="width:23%; height:20px; text-align:center">I TRIMESTRE</th>
            <th style="width:23%;text-align:center">II TRIMESTRE</th>
            <th style="width:23%;text-align:center">III TRIMESTRE</th>
            <th style="width:23%;text-align:center">IV TRIMESTRE</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="width:10%;"><b>(%) PROG.</b></td>';
            for ($i=1; $i <=4 ; $i++) {
              $tabla.='
              <td style="width:20%; height:20px; color:blue" align=right><b>'.$valor[5][$i].'%</b></td>';
            }
          $tabla.='
          </tr> 
          <tr>
            <td style="width:10%;"><b>(%) CUMP.</b></td>';
            for ($i=1; $i <=4 ; $i++) {
              $tabla.='
              <td style="width:20%; height:20px; color:blue" align=right><b>'.$valor[6][$i].'%</b></td>';
            }
          $tabla.='
          </tr>  
        </tbody>
      </table>';
    }

    return $tabla;
  }



  /// TABLA  EVALUACION DE FORMULARIO 2
  public function tabla_eval_form2($matriz,$nro,$tp_rep){
    $tabla='';
    // tp_rep : 0 normal
    // tp_rep : 1 impresion
    $tyle='class="table table-bordered" border=0.2 style="width:80%;"';
    if($tp_rep==1){
      $tyle='class="change_order_items" border=1 style="width:100%;"';
    }

    $tabla.='
      <center>
        <table '.$tyle.'>
          <thead>
            <tr align=center>
              <th style="width:9.09%;"></th>';
              for ($i=0; $i<$nro; $i++) { 
                $tabla.='<th style="width:9.09%;"><center>'.$matriz[$i][2].'</center></th>';
              }
              $tabla.='
              </tr>
            </thead>
          <tbody>
            <tr>
              <td style="height:20px;"><b>(%) CUMPLIMIENTO</b></td>';
              for ($i=0; $i<$nro; $i++) { 
                $tabla.='<td style="width:9.09%;" align=right><b>'.$matriz[$i][3].' %</b></td>';
              }
              $tabla.='
            </tr>
          </tbody>
        </table>
      </center>';

    return $tabla;
  }


  /// MATRIZ EVALUACION DE FORMULARIO 2
  public function matriz_eval_form2(){
    $regionales=$this->model_proyecto->list_departamentos();
    

    $nro=0;
    foreach($regionales as $reg){
        
        /// -----------------------------------------------------------------------------------------
        $lista_ogestion=$this->model_objetivogestion->get_list_ogestion_por_regional($reg['dep_id']);
        //$lista_ogestion=$this->model_objetivogestion->get_list_ogestion_por_regional_priorizado($reg['dep_id']);
        $nro_prog=count($lista_ogestion);
        $suma_cumplimiento_trimestral=0;
        $suma_cumplimiento_gestion=0;
        
        foreach($lista_ogestion as $row){
          $calificacion=$this->eval_oregional->calificacion_trimestral_acumulado_x_oregional($row['or_id'],$this->tmes,$row['or_tp'],$row['tp_meta']);
          $suma_cumplimiento_trimestral=$suma_cumplimiento_trimestral+$calificacion[3];
          $suma_cumplimiento_gestion=$suma_cumplimiento_gestion+$calificacion[4];
        }

         $cumplimiento=0;
         if($nro_prog!=0){
            $cumplimiento= round(($suma_cumplimiento_gestion/$nro_prog),2); 
         }

        /// -----------------------------------------------------------------------------------------

      $mat[$nro][1]=$reg['dep_id'];
      $mat[$nro][2]=strtoupper($reg['dep_sigla']);
      $mat[$nro][3]=$cumplimiento; /// % cumplimiento
      $nro++;
    }

    return $mat;
  }


 /*---- CABECERA REPORTE OPERACIONES POR REGIONALES (GRAFICO)----*/
  function cabecera_reporte_grafico(){
    $tabla='';

    $tabla.='
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
        <tr style="border: solid 0px;">              
            <td style="width:70%;height: 2%">
                <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                    <tr style="font-size: 15px;font-family: Arial;">
                        <td style="width:45%;height: 20%;">&nbsp;&nbsp;<b>'.$this->session->userData('entidad').'</b></td>
                    </tr>
                    <tr>
                        <td style="width:50%;height: 20%;font-size: 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</td>
                    </tr>
                </table>
            </td>
            <td style="width:30%; height: 2%; font-size: 8px;text-align:right;">
              '.date("d").' de '.$this->mes[ltrim(date("m"), "0")]. " de " . date("Y").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </td>
        </tr>
      </table>
      <hr>
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
          <tr style="border: solid 0px black; text-align: center;">
              <td style="width:10%; text-align:center;">
              </td>
              <td style="width:80%; height: 5%">
                <table align="center" border="0" style="width:100%;">
                  <tr style="font-size: 23px;font-family: Arial;">
                    <td style="height: 32%; text-align:center"><b>PLAN OPERATIVO ANUAL - GESTI&Oacute;N '.$this->gestion.'</b></td>
                  </tr>
                  <tr style="font-size: 20px;font-family: Arial;">
                    <td style="height: 5%; text-align:center">EVALUACIÓN DE OPERACIONES</td>
                  </tr>
                </table>
              </td>
              <td style="width:10%; text-align:center;">
              </td>
          </tr>
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