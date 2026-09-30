<?php if (!defined('BASEPATH')) exit('No direct script access');

class Lib_seguimientopoa {

    private $CI;
    public $dist_id;
    public $dep_id;
    public $gestion;
    public $fun_id;
    public $conf_pei;
    public $tp_adm;

    public function __construct() {
        // Obtenemos la instancia de CodeIgniter
        $this->CI =& get_instance();
        // Cargamos el modelo usando la instancia
        $this->CI->load->model('programacion/model_proyecto');
        $this->CI->load->model('programacion/model_producto');
        $this->CI->load->model('programacion/model_componente');
        $this->CI->load->model('mevaluacion_poa/model_evaluacionpoa');
        $this->CI->load->model('mantenimiento/model_configuracion');

        $this->dist_id  = $this->CI->session->userdata('dist');
        $this->dep_id   = $this->CI->session->userdata('dep_id');
        $this->gestion  = $this->CI->session->userdata('gestion');
        $this->fun_id   = $this->CI->session->userdata('fun_id');
        $this->tmes = $this->CI->session->userdata('trimestre');
        $this->tp_adm   = $this->CI->session->userdata("tp_adm");
        $this->entidad   = $this->CI->session->userdata("entidad");
        $this->sistema   = $this->CI->session->userdata("sistema");
        $this->sistema_pie   = $this->CI->session->userdata("sistema_pie");
        $this->usuario   = $this->CI->session->userdata("usuario");
        $this->mes_sistema   = $this->CI->session->userdata("mes");
    }


    /// Modal Para ver la ejecucion de la Actividad llevar a Libreria
    public function modal_seguimiento_x_form4() {
      $tabla='';
      $tabla.='
      <div class="modal fade" id="modalDetalleActividadAjax" tabindex="-1" role="dialog" aria-hidden="true" >
          <div class="modal-dialog modal-lg" style="width:90%;">
              <div class="modal-content">
                  <div class="modal-header" style="background: #1e3a8a; color: #fff;">
                      <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:1;">&times;</button>
                      <h4 class="modal-title" style="font-weight: bold;">
                          <i class="fa fa-database"></i> Detalle de Actividad y Seguimiento en BD
                      </h4>
                  </div>
                  <div class="modal-body" style="padding: 20px;">
                      <div id="detalle"></div>
                  </div>
                  <div class="modal-footer" style="background: #f8fafc;">
                      <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold;">Cerrar Ventana</button>
                  </div>
              </div>
          </div>
      </div>';
      return $tabla;
    }

    /*-- FORMULARIOS POA ACTUALIZADOS FORM 4, FORM 5 --*/
    public function formularios_poa($com_id){
      $tabla='';
      $meses = $this->CI->model_configuracion->get_mes();
      $tabla.='
            <div class="btn-group" >
              <a class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_cascade.png" WIDTH="19" HEIGHT="18"/>&nbsp;&nbsp;FORMULARIOS POA GESTIÓN - '.$this->gestion.'&nbsp;&nbsp;&nbsp;&nbsp;</a>
              <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" ><span class="caret"></span></a>
              <ul class="dropdown-menu">
                <li>
                  <a href="javascript:abreVentana(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$com_id.'\');" >FORMULARIO N°4 (ACTIVIDADES)</a>
                </li>
                <li>
                  <a href="javascript:abreVentana(\''.site_url("").'/prog/reporte_form5_uresponsable/'.$com_id.'\');">FORMULARIO N°5 (REQUERIMIENTOS)</a>
                </li>
                <hr>';
                  foreach($meses as $rowm){
                  if($rowm['m_id']<=$this->mes_sistema){
                    $tabla.='
                    <li>
                      <a href="javascript:abreVentana(\''.site_url("").'/seguimiento_poa/reporte_seguimientopoa_mensual/'.$com_id.'/'.$rowm['m_id'].'\');">REPORTE SEGUIMIENTO POA - '.$rowm['m_descripcion'].'</a>
                    </li>';
                  }                     
                }
                $tabla.='
                <hr>';
                  for ($i=1; $i <=$this->tmes; $i++) { 
                    $trimestre=$this->CI->model_evaluacionpoa->get_trimestre($i); /// Datos del Trimestre
                    $tabla.='
                    <li>
                      <a href="javascript:abreVentana(\''.site_url("").'/seg/ver_reporte_evaluacionpoa/'.$com_id.'/'.$i.'\');" >REP. EVAL. POA - '.$trimestre[0]['trm_descripcion'].'</a>
                    </li>';
                  }
                
                $tabla.='
              </ul>
            </div>';

      return $tabla;
    }

    ///// Get Listado de Actividades para impresion
    public function list_form4_seguimiento_x_unidadResp($com_id){

    }

    /// TITULO VISTA
    public function titulo($componente){
        $trimestre=$this->CI->model_evaluacionpoa->trimestre();
        $tabla='';
        $tabla.='
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <input type="hidden" name="base" value="'.base_url().'">
      
              <div class="well">
                <h2>'.$componente[0]['aper_programa'].' '.$componente[0]['aper_proyecto'].' '.$componente[0]['aper_actividad'].' - '.$componente[0]['tipo'].' '.$componente[0]['act_descripcion'].' - '.$componente[0]['abrev'].'  / <b>'.$componente[0]['serv_cod'].' </b>'.$componente[0]['tipo_subactividad'].' '.$componente[0]['serv_descripcion'].'</h2>
                <h1><small>TRIMESTRE VIGENTE : </small> '.$trimestre[0]['trm_descripcion'].'</h1>

                '.$this->formularios_poa($componente[0]['com_id']).'

                    <a href="#" class="btn btn-default" onclick="cargarCuadrosEvaluacion(this, '.$componente[0]['com_id'].')" title="GENERAR CUADROS DE EVALUACION POA">
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>GENERAR CUADROS DE EVALUACIÓN</b>
                    </a>
              </div>
            </article>';
    
        return $tabla;
    }


    /// Modal Para ver los graficos de Cumplimiento Al POa
    public function modal_evaluacion_poa_x_UnidadResponsable() {
      $tabla='';
      $tabla .= '
      <div class="modal fade" id="modal_graficos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <!-- 🌟 OPTIMIZACIÓN DE ANCHO: Se expande al 95% de la pantalla con un tope máximo ideal de 1400px -->
        <div class="modal-dialog" style="width: 95%; max-width: 1400px; margin: 20px auto;">
            <div class="modal-content" style="border-radius: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.3);">
                <div class="modal-header" style="background: #1e3a8a; color: #fff; padding: 15px 20px;">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:1;">&times;</button>
                    <h4 class="modal-title" style="font-weight: bold;">
                        <i class="fa fa-bar-chart-o"></i> Cuadros y Gráficos de Evaluación POA
                    </h4>
                </div>
                
                <!-- 🌟 ALTURA AMPLIADA: max-height subido a 700px con scroll interno para dar un respiro visual a los canvas de 350px -->
                <div class="modal-body print-area-graficos" style="padding: 25px; max-height: 700px; overflow-y: auto;vertical-align: middle;">
                    <div class="row">
                        <div class="col-md-6 col-sm-12 text-center" style="vertical-align: middle; padding-left: 10px;font-size: 14px;">
                            <h5 style="vertical-align: middle; padding-left: 10px;font-size: 14px;">
                                 <b>CUMPLIMIENTO_POA</b>
                            </h5>
                            <div style="position: relative; height:350px; width:100%; padding: 0 10px;">
                                <canvas id="grafico_pastel_cumplimiento"></canvas>
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-sm-12 text-center" style="margin-bottom: 25px;vertical-align: middle;">
                            <h5 style="vertical-align: middle;font-weight: bold; color: #334155; margin-bottom: 15px; font-size: 14px;">
                                <b>CUMPLIMIENTO_POA_TRIMESTRAL.</b>
                            </h5>
                            <div style="position: relative; height:350px; width:100%; padding: 0 10px;">
                                <canvas id="grafico_barras_temporalidad"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-12" id="detalles" style="padding: 0 10px;">
                            <!-- 🌟 AQUÍ JQUERY INYECTARÁ LA TABLA DINÁMICA DE FORMA AUTOMÁTICA -->
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer" style="background: #f8fafc; padding: 15px 20px;">
                    <button type="button" class="btn btn-primary" onclick="exportarPDF();" style="background: #0284c7; border: none; font-weight: bold; color:#fff; padding: 6px 16px;">
                        <i class="fa fa-file-pdf-o"></i> Exportar a PDF
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold; padding: 6px 16px;">Cerrar Ventana</button>
                </div>
            </div>
        </div>
      </div>';


      return $tabla;
    }


      //// Genera Matriz de cumplimiento 
      public function tabla_regresion_lineal_servicio($com_id, $trm_id) {
           $nombres_trimestres = array(
                0 => '',
                1 => 'I Trimestre',
                2 => 'II Trimestre',
                3 => 'III Trimestre',
                4 => 'IV Trimestre'
            );

            // 2. Inicializar la estructura de la matriz final
            $tr = array();
            $tr['labels'] = array();
            $tr['programadas'] = array();
            $tr['cumplidas'] = array();
            $tr['no_cumplidas'] = array();
            $tr['en_proceso'] = array();
            $tr['eficacia_porcentaje'] = array();

            // Variable para acumular la programación de forma lineal/secuencial
            $sum_total_prog = 0;

            // 🔥 CORRECCIÓN: El bucle inicia en 1 para evitar consultas con Trimestre 0
            for ($i = 0; $i <= $trm_id; $i++) {
                $valor = $this->obtiene_datos_evaluacion($com_id, $i, 1);

                // Acumulación aritmética progresiva
                $sum_total_prog = $sum_total_prog + intval($valor[1]); 
                
                $prog         = $sum_total_prog; 
                $cumplidas    = intval($valor['cumplidos']);
                $no_cumplidas = intval($valor['no_cumplidos']);
                $en_proceso   = intval($valor['en_proceso']);

                // Calcular porcentaje de eficacia
                $eficacia = 0;
                if ($prog > 0) {
                    $eficacia = round((($cumplidas / $prog) * 100), 2);
                }

                // 4. Llenar la matriz clásica
                $tr[1][$i] = $nombres_trimestres[$i];
                $tr[2][$i] = $prog;
                $tr[3][$i] = $cumplidas;
                $tr[4][$i] = $no_cumplidas;
                $tr[5][$i] = $eficacia;
                $tr[6][$i] = round(100 - $eficacia, 2);
                $tr[7][$i] = $en_proceso;
                $tr[8][$i] = $prog > 0 ? round(($en_proceso / $prog) * 100, 2) : 0;

                // 🌟 5. Estructura directa para Chart.js
                $tr['labels'][]      = $nombres_trimestres[$i];
                $tr['programadas'][] = $prog;
                $tr['cumplidas'][]   = $cumplidas;
                $tr['no_cumplidas'][]= $no_cumplidas;
                $tr['en_proceso'][]  = $en_proceso;
                $tr['eficacia_porcentaje'][] = $eficacia;
        }

        return $tr;
    }
    

    public function obtiene_datos_evaluacion($com_id, $trimestre, $tipo_evaluacion) {
      // Definimos el tope del mes según el trimestre de manera matemática
      // Trimestre 1 = Mes 3, Trimestre 2 = Mes 6, Trimestre 3 = Mes 9, Trimestre 4 = Mes 12
      $mes_inicio = (($trimestre - 1) * 3) + 1;
      $mes_final = intval($trimestre) * 3;

      // 1. Ejecutar las 3 consultas directas (Sin bucles)
      $prog_data  = $this->CI->model_evaluacionpoa->suma_programados_acumulados($com_id, $mes_inicio, $mes_final);
      $ejec_data  = $this->CI->model_evaluacionpoa->suma_ejecutados_acumulados($com_id, $mes_inicio, $mes_final);
      $pastel_data = $this->CI->model_evaluacionpoa->obtener_estados_pastel_acumulado($com_id, $trimestre);

      // 2. Extraer y formatear datos de manera segura
      $nro_ope_eval     = isset($prog_data['total_operaciones']) ? intval($prog_data['total_operaciones']) : 0;
      $total_programado = isset($prog_data['suma_programado']) ? floatval($prog_data['suma_programado']) : 0;
      $total_ejecutado  = isset($ejec_data['suma_evaluado']) ? floatval($ejec_data['suma_evaluado']) : 0;

      // 3. Mapear los 3 estados para el gráfico de pastel
      $cumplidas    = isset($pastel_data['cumplidos']) ? intval($pastel_data['cumplidos']) : 0;
      $no_cumplidas = isset($pastel_data['no_cumplidos']) ? intval($pastel_data['no_cumplidos']) : 0;
      $en_proceso   = isset($pastel_data['en_proceso']) ? intval($pastel_data['en_proceso']) : 0;

      // 4. Retornar el vector estructurado con las nuevas posiciones limpias
      $vtrimestre[1] = $nro_ope_eval;     // Nro de operaciones evaluadas
      $vtrimestre[2] = $cumplidas;        // [Mantenido por compatibilidad heredada]
      $vtrimestre[3] = $total_programado; // Suma física programada
      $vtrimestre[4] = $total_ejecutado;  // Suma física ejecutada
      
      // 🌟 Nuevos índices específicos para el gráfico de pastel de 3 valores
      $vtrimestre['cumplidos']    = $cumplidas;
      $vtrimestre['no_cumplidos'] = $no_cumplidas;
      $vtrimestre['en_proceso']   = $en_proceso;

      return $vtrimestre;
    }




    /// Estilo Formulario LLEVAR A LIBRERIA
    public function estilo_tabla_form4(){
      $tabla='';
      $tabla.='
      <style type="text/css">
        aside{background: #05678B;}
        #mdialTamanio{
            width: 90% !important;
        }
        #mdialTamanio2{
            width: 50% !important;
        }
        #mdialTamanio3{
            width: 95% !important;
        }
        #dialog_subirr { width: 45%;}
        table{font-size: 10px;
              width: 100%;
              max-width:1550px;;
              overflow-x: scroll;
              }
        input[type="checkbox"] {
          display:inline-block;
          width:28px;
          height:28px;
          margin:-1px 4px 0 0;
          vertical-align:middle;
          cursor:pointer;
        }
        th {font-size: 10px; }

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



}