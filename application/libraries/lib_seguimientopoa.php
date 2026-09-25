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
<div class="modal fade" id="modal_graficos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: #1e3a8a; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:1;">&times;</button>
                <h4 class="modal-title" style="font-weight: bold;">
                    <i class="fa fa-bar-chart-o"></i> Cuadros y Gráficos de Evaluación POA
                </h4>
            </div>
            
            <div class="modal-body print-area-graficos" style="padding: 20px; max-height: 550px; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6 col-sm-12 text-center" style="margin-bottom: 25px;">
                        <h5 style="font-weight: bold; color: #334155; margin-bottom: 15px;">
                            <i class="fa fa-pie-chart"></i> Cumplimiento Físico Global de Actividades (%)
                        </h5>
                        <div style="position: relative; height:220px; width:100%;">
                            <canvas id="grafico_pastel_cumplimiento"></canvas>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-center" style="margin-bottom: 25px;">
                        <h5 style="font-weight: bold; color: #334155; margin-bottom: 15px;">
                            <i class="fa fa-bar-chart"></i> Eficacia Mensual vs Programada (Unidades)
                        </h5>
                        <div style="position: relative; height:220px; width:100%;">
                            <canvas id="grafico_barras_temporalidad"></canvas>
                        </div>
                    </div>
                </div>
                        <div class="row" style="margin-top: 15px;">
                    <div class="col-md-12">
                        <table class="table table-bordered table-striped" style="width: 100%; font-size: 12px;">
                            <thead>
                                <tr style="background: #475569; color: #fff;">
                                    <th>Criterio de Evaluación</th>
                                    <th style="text-align: center; width: 20%;">Total Programado</th>
                                    <th style="text-align: center; width: 20%;">Total Ejecutado</th>
                                    <th style="text-align: center; width: 20%;">% Eficacia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>Actividades POA de la Unidad Responsable</strong></td>
                                    <td style="text-align: center; font-weight: bold; color: #0284c7;" id="lbl_total_prog">0.00</td>
                                    <td style="text-align: center; font-weight: bold; color: #16a34a;" id="lbl_total_ejec">0.00</td>
                                    <td style="text-align: center; font-weight: bold; font-size: 14px;" id="lbl_total_porcentaje">0%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
          <div class="modal-footer" style="background: #f8fafc;">
                    <button type="button" class="btn btn-primary" onclick="window.print();" style="background: #0284c7; border: none; font-weight: bold; color:#fff;">
                        <i class="fa fa-print"></i> Imprimir Cuadros
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold;">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>';

      return $tabla;
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