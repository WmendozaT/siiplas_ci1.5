<?php
class Cevaluacion_form4 extends CI_Controller {
    public $rol = array('1' => '3','2' => '4');
    public function __construct (){
        parent::__construct();
        // 1. Corregido el operador AND lógico (&&)
        if($this->session->userdata('fun_id') != null && $this->session->userdata('fun_estado') != 3){

            $this->load->model('programacion/model_proyecto');
            $this->load->model('programacion/model_producto');
            $this->load->model('programacion/model_componente');
            $this->load->model('mevaluacion_poa/model_evaluacionpoa');
            $this->load->model('mantenimiento/model_configuracion');
            $this->load->model('menu_modelo');
            
            $this->gestion = $this->session->userData('gestion');
            $this->adm = $this->session->userData('adm');
            $this->rol = $this->session->userData('rol_id');
            $this->dist_id = $this->session->userData('dist');
            $this->dep_id = $this->session->userData('dep_id');
            $this->dist_tp = $this->session->userData('dist_tp');
            $this->tmes = $this->session->userData('trimestre');
            //$this->mes = $this->mes_nombre();
            $this->fun_id = $this->session->userData('fun_id');
            $this->tp_adm = $this->session->userData('tp_adm');
            $this->resolucion=$this->session->userdata('rd_poa');
            $this->com_id=$this->session->userdata('com_id');
            $this->mes_sistema=$this->session->userData('mes'); /// mes sistema
            $this->verif_mes=$this->session->userdata('mes_actual');
            $this->load->library('lib_seguimientopoa');
            
        } else {
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }


    /*----- Vista lista POA para evaluacion poa 2027 ------*/
      public function lista_poa_seguimientoPoa(){
      $data['menu']=$this->menu(4);
      $tabla='';
    
      //$tabla .= $this->programacionpoa->tp_resp();
      $tabla .= '
      <input name="base" type="hidden" value="'.base_url().'">
      
      <div id="tabs" style="border: none; background: transparent;">
        <!-- 📌 MENÚ DE PESTAÑAS PRINCIPALES (TABS) -->
        <ul style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; padding: 0; border-radius: 4px 4px 0 0;">
            <li style="margin-bottom: -2px;">
                <a href="#tabs-c" style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11.5px; color: #1e293b; text-transform: uppercase; padding: 10px 16px;"><i class="fa fa-folder-open text-primary"></i> Gasto Corriente</a>
            </li>
            <li style="margin-bottom: -2px;">
                <a href="#tabs-a" style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11.5px; color: #1e293b; text-transform: uppercase; padding: 10px 16px;"><i class="fa fa-university text-success"></i> Proyectos de Inversión Pública</a>
            </li>
        </ul>

        <div id="tabs-c" style="padding: 15px 0 0 0; background: transparent;">
            <div class="row">
                <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-darken" style="margin-bottom: 15px;">
                        <header style="background: #334155; color: #ffffff; height: 38px; display: flex; align-items: center; padding: 0 10px; border-radius: 4px 4px 0 0;">
                            <span class="widget-icon" style="margin-right: 8px;"> <i class="fa fa-arrows-v text-muted"></i> </span>
                            <h2 class="font-md" style="margin: 0; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px;"><strong>Gasto Corriente - Gestión Regular</strong></h2>  
                        </header>
                        <div>
                            <div class="widget-body no-padding" style="background: #ffffff; padding: 15px !important; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 4px 4px;">
                                <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px;">
                                    <table id="dt_basic3" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; min-width: 1500px; font-size: 11px; border-collapse: collapse;">
                                        <thead>
                                            <tr style="height: 42px; background: #475569; color: #ffffff; text-transform: uppercase; font-size: 9.5px; letter-spacing: 0.3px;">
                                              <th style="width:1%; text-align: center; vertical-align: middle;">#</th>
                                              <th style="width:5%; text-align: center; vertical-align: middle;"></th>
                                              <th style="width:5%; text-align: center; vertical-align: middle;">FORMULARIO EVALUACIÓN</th>
                                              <th style="width:5%; text-align: center; vertical-align: middle;">GENERAR CUADRO EVALUACIÓN POA</th>
                                              <th style="width:5%; text-align: center; vertical-align: middle;">FORMULARIO EVALUACIÓN .PDF</th>
                                              <th style="width:10%; text-align: center; vertical-align: middle;">CATEGORIA PROGRAMÁTICA '.$this->gestion.'</th>
                                              <th style="width:20%; text-align: center; vertical-align: middle;">GASTO CORRIENTE DESCRIPCIÓN</th>
                                              <th style="width:8%; text-align: center; vertical-align: middle;">DISTRITAL</th>
                                              <th style="width:9%; text-align: center; vertical-align: middle; background-color: #1e3a8a;">PPTO. ASIGNADO</th>
                                              <th style="width:9%; text-align: center; vertical-align: middle; background-color: #d97706;">PPTO. POA</th>
                                              <th style="width:9%; text-align: center; vertical-align: middle;">SALDO REMANENTE</th>
                                              <th style="width:4%; text-align: center; vertical-align: middle;">ESTADO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            '.$this->list_unidades_es(4).'
                                        </tbody>
                                    </table>
                                </div> <!-- Fin .table-responsive -->

                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div id="tabs-a" style="padding: 15px 0 0 0; background: transparent;">
            <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div class="jarviswidget jarviswidget-color-darken" >
                  <header>
                    <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                      <h2 class="font-md"><strong>PROYECTOS DE INVERSI&Oacute;N PUBLICA </strong></h2>  
                  </header>
                  <div>
                    <div class="widget-body no-padding">
                          <table id="dt_basic" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; min-width: 1500px; font-size: 11px; border-collapse: collapse;">
                            <thead>
                              <tr style="height: 42px; background: #475569; color: #ffffff; text-transform: uppercase; font-size: 9.5px; letter-spacing: 0.3px;">
                                <th style="width:1%;"></th>
                                <th style="width:10%;"title="REPORTE POA">REPORTE POA</th>
                                <th style="width:10%;" title="REPORTE POA APROBADO">EJECUCION POA '.$this->gestion.'</th>
                                <th style="width:5%;" title="ERROR EN EL POA"></th>
                                <th style="width:10%;" title="APERTURA PROGRAM&Aacute;TICA">CATEGORIA PROGRAM&Aacute;TICA</th>
                                <th style="width:25%;" title="NOMBRE DEL PROYECTO DE INVERSI&Oacute;N">PROYECTO DE INVERSIÓN</th>
                                <th style="width:10%;" title="C&Oacute;DIGO SISIN">C&Oacute;DIGO_SISIN</th>
                                <th style="width:15%;" title="UNIDAD ADMINISTRATIVA">UNIDAD_ADMINISTRATIVA</th>
                                <th style="width:15%;" title="UNIDAD EJECUTORA">UNIDAD_EJECUTORA</th>
                                <th style="width:20%;" title="FASE - ETAPA DE LA OPERACI&Oacute;N">FASE_ETAPA</th>
                              </tr>
                            </thead>
                            <tbody>
                            '.$this->list_pinversion(4).'
                            </tbody>
                          </table>
                    </div>
                  </div>
                </div>
              </article>
            </div>
        </div>
      </div>


        <div class="modal fade" id="modal_nuevo_ff" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true" data-backdrop="static">
          <!-- Ancho controlado y centrado estético -->
          <div class="modal-dialog" id="mdialTamanio" style="width: 50% !important; max-width: 900px; margin: 30px auto;">
              <div class="modal-content" style="border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.25); border: none; overflow: hidden;">
                  
                  <!-- 🏛️ Cabecera Superior Estilizada -->
                  <div class="modal-header" style="background: #1e3a8a; color: #ffffff; padding: 15px 20px; border-bottom: 1px solid #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
                      <h4 class="modal-title" style="font-weight: bold; margin: 0; font-size: 16px; font-family: sans-serif;">
                          <i class="fa fa-folder-open"></i> EVALUACIÓN OPERATIVA TRIMESTRAL
                      </h4>
                      <!-- Botón de Salir Moderno -->
                      <button type="button" class="close" data-dismiss="modal" id="amcl" title="SALIR" style="color: #ffffff; opacity: 0.8; font-size: 14px; font-weight: bold; background: rgba(255,255,255,0.15); border: none; padding: 5px 12px; border-radius: 4px; transition: all 0.2s;">
                          <span aria-hidden="true">&times; Cerrar</span>
                      </button>
                  </div>
                  
                  <!-- 📝 Cuerpo del Modal -->
                  <div class="modal-body" style="padding: 25px; background: #ffffff;">
                      
                      <!-- Alerta de Gestión Estilo Banner Plano Corporativo -->
                      <div class="alert alert-info" style="background-color: #e0f2fe; border-color: #bae6fd; color: #0369a1; border-radius: 6px; padding: 12px; margin-bottom: 20px; font-weight: bold; font-size: 15px; text-align: center; letter-spacing: 0.5px; font-family: sans-serif;">
                          <i class="fa fa-calendar"></i> MI POA — GESTIÓN '.$this->gestion.'
                      </div>
                  
                      <!-- Bloque Estructural del Establecimiento / Proyecto Seleccionado -->
                      <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 15px; margin-bottom: 20px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);">
                          <table style="width:100%; border-collapse: collapse;">
                              <tr>
                                  <!-- Título o Datos del Establecimiento -->
                                  <td style="width: 85%; vertical-align: middle; padding-right: 15px;">
                                      <div id="titulo" style="font-size: 13px; color: #334155; line-height: 1.4;"></div> 
                                  </td>
                                  <!-- Carátula o Icono del Establecimiento -->
                                  <td style="width: 15%; text-align: center; vertical-align: middle; border-left: 1px dashed #cbd5e1; padding-left: 10px;">
                                      <div id="caratula"></div> 
                                  </td>
                              </tr>
                          </table>
                      </div>

                      <!-- 🌟 CONTENEDOR DINÁMICO DONDE JQUERY INYECTA LAS UNIDADES RESPONSABLES -->
                      <div class="row" style="margin: 0;">
                          <div id="content1" style="width: 100%; min-height: 100px;">
                              <!-- El preloader o la tabla asíncrona se renderizarán limpiamente aquí -->
                          </div>
                      </div>
                      
                  </div>
              </div>
          </div>
      </div>';
      $tabla.=$this->lib_seguimientopoa->modal_evaluacion_poa_x_UnidadResponsable(); /// Graficos MD2
      $data['inf_eval']='';
      if($this->lib_seguimientopoa->verif_eval()==true){
        $data['inf_eval']='
        <div class="well" style="background:#D8FAD2;text-align:center;">
          <h2>PROCESO DE EVALUACION POA - <b>'.$this->model_evaluacionpoa->trimestre()[0]['trm_descripcion'].'</b></h2>
        </div>';
      }
      $data['listado']=$tabla;
      $this->load->view('admin/evaluacion/evaluacion_form4/list_poa_evaluacion', $data);
    }


    /*---- Lista de Unidades / Establecimientos de Salud (llevar a libreria) -----*/
    public function list_unidades_es($proy_estado){
      $unidades=$this->model_proyecto->list_unidades(4,$proy_estado);
      $tabla='';
      $nro=0;
        foreach($unidades as $row){
        $nro++;
        $tabla.='
          <tr style="height:35px;">
            <td title="'.$row['proy_id'].'"><center>'.$nro.'</center></td>
            <td></td>
            <td style="text-align:center;">';
            if($row['ta_id']==2){
              $componente=$this->model_componente->proyecto_componente($row['proy_id']);
              $url_destino = site_url("formulario_seguimiento_poa/".$componente[0]['com_id']);
                $tabla .= '
                  <a href="#"  onclick="cargarFormularioEvaluacion(event, \''.$url_destino.'\', \''.addslashes($row['tipo'].' '.$row['proy_nombre'].' '.$row['abrev']).'\')" class="btn btn-primary btn-block" style="font-size:10px;background: #16a34a; border: none; font-weight: bold; padding: 5px 0;" title="Ingresar a evaluar actividades">
                    <i class="fa fa-pencil-square-o"></i> EVALUAR POA '.$this->verif_mes[2].' / '.$this->gestion.'
                  </a>';
            }
            else{
              $tabla.='
              <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-primary enlace" name="'.$row['proy_id'].'" id=" '.$row['tipo'].' '.strtoupper($row['proy_nombre']).' - '.$row['abrev'].'" style="font-size:10px;">
                <i class="glyphicon glyphicon-list"></i> <b>EVALUAR POA</b>
              </a>';
            }
            $tabla.='
            </td>
            <td>
              <a href="#" class="btn btn-default" onclick="cargarCuadrosEvaluacion(this, '.$row['proy_id'].',1)" title="GENERAR CUADROS DE EVALUACION POA">
                <img src="'.base_url().'assets/Iconos/chart_bar.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>CUADRO EVALUACIÓN POA</b>
              </a>
            </td>
            <td></td>
            <td><center>'.$row['aper_programa'].''.$row['aper_proyecto'].''.$row['aper_actividad'].'</center></td>
            <td>'.$row['tipo'].' '.$row['act_descripcion'].' - '.$row['abrev'].'</td>
            <td style="text-align:left;">'.strtoupper($row['dist_distrital']).'</td>
            <td style="text-align:right;">'.number_format($row['ppto_asignado'], 2, ',', '.').'</td>
            <td style="text-align:right;">'.number_format($row['ppto_poa'], 2, ',', '.').'</td>
            <td style="text-align:right;">'.number_format($row['ppto_saldo'], 2, ',', '.').'</td>
            <td><b>'.$row['estado_poa'].'</b></td>
          </tr>';
        }

      return $tabla;
    }


 /*---- Lista de Proyectos de Inversion (llevar a libreria) -----*/
    public function list_pinversion($proy_estado){
      $tabla='';
      $proyectos=$this->model_proyecto->list_unidades(1,$proy_estado);
      $nro=0;
        foreach($proyectos as $row){
          $componentes = $this->model_componente->lista_UnidadesResponsables_con_actividades($row['proy_id']);
          $nro++;
          $tabla.='
          <tr style="height:35px;">
            <td title='.$row['proy_id'].'><center>'.$nro.'</center></td>
            <td>';
                foreach($componentes as $rowc){
                    $url_destino = site_url("formulario_seguimiento_poa/".$rowc['com_id']);
                    $tabla .= '
                    <a href="#" onclick="cargarFormularioEvaluacion(event, \''.$url_destino.'\', \''.addslashes($proyectos[0]['proy_nombre']).'\')" class="btn btn-xs btn-success btn-block" style="background: #16a34a; border: none; font-weight: bold; padding: 5px 0;" title="Ingresar a evaluar actividades">
                      <i class="fa fa-pencil-square-o"></i> FORMULARIO POA '.$this->verif_mes[2].' / '.$this->gestion.'
                    </a>';
                }
                $tabla.='
            </td>
            <td></td>
            <td></td>
            <td><center>'.$row['aper_programa'].''.$row['proy_sisin'].''.$row['aper_actividad'].'</center></td>
            <td>'.$row['proy_nombre'].'</td>
            <td>'.$row['proy_sisin'].'</td>
            <td>'.$row['dep_cod'].' '.strtoupper($row['dep_departamento']).'</td>
            <td>'.$row['dist_cod'].' '.strtoupper($row['dist_distrital']).'</td>
            <td title='.$row['pfec_id'].'>'.strtoupper($row['pfec_descripcion']).'</td>        
          </tr>';
        }
      return $tabla;
    }















    /*----- GET Evaluar Unidad Responsable -----*/
    public function get_evaluar_unidadresponsable(){
    // Verificación nativa de peticiones asíncronas
    if($this->input->is_ajax_request() && $this->input->post()){
        $proy_id = intval($this->input->post('proy_id'));

        // Obtener el HTML optimizado
        $tabla = $this->lista_UnidadesResponsables_para_evaluacion($proy_id);
        
        $result = array(
            'respuesta'  => 'correcto',
            'tabla'      => $tabla,
            'evaluacion' => '' // Se inicializa vacía para evitar el error de "Undefined variable"
        );
        
        echo json_encode($result);
        return;
    } else {
        show_404();
    }
}

/*------ GET UNIDADES RESPONSABLES (OPTIMIZADO SIN CONSULTAS EN BUCLE) -----*/
public function lista_UnidadesResponsables_para_evaluacion($proy_id){
    $tabla = ' 
    <table class="table table-bordered table-striped" style="width:100%; font-family:sans-serif; font-size:12px;">
      <thead>
        <tr style="background: #334155; color: #ffffff; height: 32px;">
          <th style="width:8%; text-align: center; vertical-align: middle;">COD.</th>
          <th style="width:72%; vertical-align: middle; padding-left: 10px;">UNIDAD RESPONSABLE A EVALUAR</th>
          <th style="width:30%; text-align: center; vertical-align: middle;">ACCIONES</th>
        </tr>
      </thead>
      <tbody>';

    $componentes = $this->model_componente->lista_UnidadesResponsables_con_actividades($proy_id);
    
    if (empty($componentes)) {
        $tabla .= '<tr><td colspan="3" style="text-align:center; color:#94a3b8; padding: 15px;">La unidad organizacional seleccionada no registra actividades POA vigentes para evaluación.</td></tr>';
    } else {
        foreach($componentes as $rowc){
            $url_destino = site_url("formulario_seguimiento_poa/".$rowc['com_id']);
            
            $tabla .= '
            <tr style="height: 35px; vertical-align: middle;">
              <td style="text-align: center; font-weight: bold; color: #1e293b; vertical-align: middle;">'.$rowc['serv_cod'].'</td>
              <td style="vertical-align: middle; padding-left: 10px; color: #334155;"><b>'.$rowc['tipo_subactividad'].' '.$rowc['com_componente'].'</b></td>
              <td style="text-align: center; vertical-align: middle; padding: 4px;">
                <!-- 🌟 AJUSTE AQUÍ: href cambiado por # y evento onclick para activar el Loading de pantalla completa -->
                <a href="#" onclick="cargarFormularioEvaluacion(event, \''.$url_destino.'\', \''.addslashes($rowc['com_componente']).'\')" class="btn btn-xs btn-success btn-block" style="background: #16a34a; border: none; font-weight: bold; padding: 5px 0;" title="Ingresar a evaluar actividades">
                    <i class="fa fa-pencil-square-o"></i> FORMULARIO POA
                </a>
              </td>
            </tr>';
        }
    }
    
    $tabla .= '</tbody></table>';
    return $tabla;
}














/////////////////////////////////////

  

    /*----- Vista de Seguimiento y Evaluacion 2027 ------*/
    public function formulario_seguimiento_poa($com_id){
        $componente = $this->model_componente->get_componente($com_id,$this->gestion);
        $data['stylo'] = $this->lib_seguimientopoa->estilo_tabla_form4(); 
        $data['titulo']=$this->lib_seguimientopoa->titulo($componente);
        $form4_crudo=$this->model_producto->lista_productos($com_id);

          // if($componente[0]['tp_id']==1){ //// Inversion
          //   redirect('form_ejec_pinversion/'.$com_id);
          // }
          // else{ ////// Gasto Corriente
          //   if($this->lib_seguimientopoa->verif_eval()){
          //     $data['tabla'] = $this->lib_seguimientopoa->formulario_evaluacion_UnidadResponsable($componente); /// formulario de Evaluacion poa
          //   }
          //   else{
          //     $data['tabla'] = 'Formulario de Seguimiento'; //// Formulario de seguimiento Mensual
          //   }
          //   $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
          // }
        $data['tabla'] = $this->lib_seguimientopoa->formulario_evaluacion_UnidadResponsable($componente); /// formulario de Evaluacion poa
        $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
    }












    //// Get Obtiene Seguimiento x Actividad
    public function obtener_detalle_seguimiento_x_actividad() {
      $prod_id = intval($this->input->post('prod_id'));
      $get_form4 = $this->model_evaluacionpoa->get_form4_seguimiento_poa($prod_id);
      
      if (count($get_form4) == 0) {
          echo json_encode(array('status' => 'error', 'message' => 'Actividad no encontrada.'));
          return;
      }
      
      $trimestre  = $this->tmes; // Puedes cambiarlo por: intval($this->input->post('trimestre')) si viaja por POST
      $mes_inicio = (($trimestre - 1) * 3) + 1;
      $mes_fin    = $trimestre * 3;

      $tp_indi = ($get_form4[0]['indi_id'] == 2) ? '%' : '';

      // 2. DISEÑO MODERNO: Estructura de cabeceras de la tabla con colores profesionales
      $tabla = '
      <div class="table-responsive">
          <table class="table table-bordered table-condensed" style="width: 100%; margin-bottom: 0; font-family: sans-serif; table-layout: fixed;">
            <thead>
              <tr style="font-size: 11px; background: #334155; color: #ffffff;">
                <th style="width: 3%; text-align:center; vertical-align: middle;">COD.</th>
                <th style="width: 15%; text-align:center; vertical-align: middle;">ACTIVIDAD</th>
                <th style="width: 10%; text-align:center; vertical-align: middle;">UNIDAD RESPONSABLE</th>
                <th style="width: 10%; text-align:center; vertical-align: middle;">
                 <th style="width: 3%; text-align:center; vertical-align: middle;">META</th>
                
                <!-- Cabeceras de meses simplificadas -->
                <th style="width: 9%; text-align:center; vertical-align: middle;">ENE</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">FEB</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">MAR</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">ABR</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">MAY</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">JUN</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">JUL</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">AGO</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">SEP</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">OCT</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">NOV</th>
                <th style="width: 9%; text-align:center; vertical-align: middle;">DIC</th>
              </tr>
            </thead>
            <tbody>
              <!-- FILA 1: DATOS GENERALES Y METAS DE CADA MES -->
              <tr>
                <td style="white-space: normal; line-height: 1.4; text-align: center; font-weight: bold; color: #1e293b; vertical-align: middle; font-size:11.5px; background: #f8fafc;">
                  '.$get_form4[0]['or_codigo'].'.'.$get_form4[0]['prod_cod'].'
                </td>
                <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
                  '.strtoupper($get_form4[0]['prod_producto']).'
                </td>
                <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
                  '.strtoupper($get_form4[0]['prod_unidades']).'
                </td>
                <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
                  '.strtoupper($get_form4[0]['prod_fuente_verificacion']).'
                </td>
                           <td style="white-space: normal; text-align: center; font-weight: bold; color: #1e3a8a; vertical-align: middle; font-size:13px; background: #f1f5f9;">
                  '.round($get_form4[0]['prod_meta'], 2).' '.$tp_indi.'
                </td>';
                
                // Renderizar Metas de Programado vs Ejecutado de corrido
                for ($i = 1; $i <= 12; $i++) {
                  // Alerta visual de color celeste claro si pertenece al trimestre actual evaluado
                  $color = ($i >= $mes_inicio && $i <= $mes_fin) ? '#e0f2fe' : '#ffffff';
                  
                  $tabla .= '
                  <td style="white-space: normal; padding: 4px; vertical-align: middle; background: '.$color.'; font-size: 11.5px;">
                    <div style="border-bottom: 1px dashed #cbd5e1; padding-bottom: 2px; margin-bottom: 2px;">
                      <span style="color:#64748b; font-weight:bold;">P:</span> 
                      <span style="font-weight:bold; float:right; color:#0284c7;">'.round($get_form4[0]['mes'.$i], 2).$tp_indi.'</span>
                      <div style="clear:both;"></div>
                    </div>
                    <div>
                      <span style="color:#64748b; font-weight:bold;">E:</span> 
                      <span style="font-weight:bold; float:right; color:#16a34a;">'.round($get_form4[0]['e_mes'.$i], 2).$tp_indi.'</span>
                      <div style="clear:both;"></div>
                    </div>
                  </td>';
                }
                
              $tabla .= '
              </tr>
            <!-- FILA 2: DETALLES EXTRA, JUSTIFICACIONES Y MEDIOS DE VERIFICACIÓN -->
              <tr style="background: #ffffff;">
                <td colspan="5" style="background: #f1f5f9; text-align: right; font-weight: bold; font-size: 10px; color: #475569; vertical-align: middle; padding-right: 8px;">
                  <i class="fa fa-paperclip"></i> REGISTRO MENSUAL:
                </td>';
                
                // Renderizar los bloques informativos extrayéndolos del Caché en memoria RAM de PHP
                for ($i = 1; $i <= 12; $i++) {
                  $color = ($i >= $mes_inicio && $i <= $mes_fin) ? '#e0f2fe' : '#ffffff';
                  $info = '<span style="color: #94a3b8; font-style: italic; font-size:10px;">Sin datos</span>';

                  $ejec=$this->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id,$i);
                  if(count($ejec)!=0){
                    $info='
                                  <div style="font-size: 10px; line-height: 1.3; color: #16a34a; text-align: justify;">
                                      <strong style="color:#155724;"><i class="fa fa-check-circle"></i> MV:</strong> '.$ejec[0]['medio_verificacion'].'
                                  </div>';
                  }
                  else{
                    $no_ejec=$this->model_evaluacionpoa->get_seguimiento_poa_mes_noejec($prod_id,$i);
                    if(count($no_ejec)!=0){
                      $info = '<div style="font-size: 10px; line-height: 1.3; color: #b45309; text-align: justify; background: #fef9c3; padding: 3px; border-radius:3px; border: 1px solid #fef08a;">
                                      <strong style="color:#795203;"><i class="fa fa-times-circle"></i> JUSTIFICADO:</strong><br>
                                      <b style="color:#475569;">MV:</b> '.strtoupper($no_ejec[0]['medio_verificacion']).'<br>
                                      <b style="color:#475569;">Obs:</b> '.strtoupper($no_ejec[0]['observacion']).'<br>
                                      <b style="color:#475569;">Acc:</b> '.strtoupper($no_ejec[0]['acciones']).'
                                </div>';
                    }
                  }

                $tabla .= '
                  <td style="white-space: normal; padding: 4px; vertical-align: top; background: '.$color.'; border: 1px solid #cbd5e1;">
                    ' . $info . '
                  </td>';
                }
                
              $tabla .= '
              </tr>
            </tbody>
          </table>
      </div>';

      $respuesta = array(
          'status'    => 'success',
          'actividad' => $tabla
      );

      echo json_encode($respuesta);
      return;
  }


    //// Get Obtiene Listado de trimestres por UnidadResponsable para su ajuste
public function obtener_detalle_seguimiento_x_unidadResponsable_para_ajustar() {
    $com_id = intval($this->input->post('com_id'));
    $componente = $this->model_componente->get_componente($com_id, $this->gestion);
    
    if (count($componente) == 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Unidad Responsable no encontrada.'));
        return;
    }

    $listado_trimestre = $this->model_evaluacionpoa->lista_consolidado_evaluacion_trimestral_Unidadresponsable($com_id);
    
    $tabla = '
    <div class="table-responsive">
        <table class="table table-bordered table-condensed" style="width: 100%; margin-bottom: 0; font-family: sans-serif; table-layout: fixed;">
          <thead>
            <tr style="font-size: 11px; background: #334155; color: #ffffff; height: 32px;">
              <th style="width: 20%; text-align:center; vertical-align: middle;">TRIMESTRE</th>
              <th style="width: 15%; text-align:center; vertical-align: middle;">NRO. PROGRAMADOS</th>
              <th style="width: 15%; text-align:center; vertical-align: middle;">NRO. CUMPLIDOS</th>
              <th style="width: 15%; text-align:center; vertical-align: middle;">NRO. PROCESO</th>
              <th style="width: 15%; text-align:center; vertical-align: middle;">NRO. NO CUMPLIDOS</th>
              <th style="width: 10%; text-align:center; vertical-align: middle;">(%) CUMP.</th>
              <th style="width: 10%; text-align:center; vertical-align: middle;">(%) PROC.</th>
              <th style="width: 10%; text-align:center; vertical-align: middle;">(%) N.CUMP.</th>
            </tr>
          </thead>
          <tbody>';
          
          foreach($listado_trimestre as $row){
            // 🌟 OPTIMIZACIÓN: Estilo en línea compacto para los inputs (Evita deformaciones en el modal)
            $style_input = 'style="height: 26px; padding: 2px 5px; font-size: 12px; font-weight: bold; text-align: center; border-radius: 4px;"';
            
            $tabla .= '
            <tr style="height: 35px; vertical-align: middle;">
              <td style="vertical-align: middle; font-weight: bold; padding-left: 8px; color: #1e293b;">'.strtoupper($row['trimestre']).'</td>
              <td style="vertical-align: middle; padding: 4px;"><input type="number" min="0" class="form-control" value="'.$row['poa_prog'].'" '.$style_input.' onchange="guardarAjusteAutomatico('.$row['eval_id'].', \'poa_prog\', this.value)"></td>
              <td style="vertical-align: middle; padding: 4px;"><input type="number" min="0" class="form-control" value="'.$row['poa_cumplidos'].'" '.$style_input.' onchange="guardarAjusteAutomatico('.$row['eval_id'].', \'poa_cumplidos\', this.value)"></td>
              <td style="vertical-align: middle; padding: 4px;"><input type="number" min="0" class="form-control" value="'.$row['poa_proceso'].'" '.$style_input.' onchange="guardarAjusteAutomatico('.$row['eval_id'].', \'poa_proceso\', this.value)"></td>
              <td style="vertical-align: middle; padding: 4px;"><input type="number" min="0" class="form-control" value="'.$row['poa_no_cumplidos'].'" '.$style_input.' onchange="guardarAjusteAutomatico('.$row['eval_id'].', \'poa_no_cumplidos\', this.value)"></td>
              
              <!-- Celdas receptoras de recálculos de porcentajes asíncronos -->
              <td style="vertical-align: middle; text-align:center; font-weight: bold; color: #16a34a;" id="pct_cump_'.$row['eval_id'].'">'.number_format($row['porcentaje_cumplimiento'], 2).'%</td>
              <td style="vertical-align: middle; text-align:center; font-weight: bold; color: #ca8a04;" id="pct_proc_'.$row['eval_id'].'">'.number_format($row['porcentaje_proceso'], 2).'%</td>
              <td style="vertical-align: middle; text-align:center; font-weight: bold; color: #dc2626;" id="pct_ncump_'.$row['eval_id'].'">'.number_format($row['porcentaje_no_cumplimiento'], 2).'%</td>
            </tr>';
          }
          
          $tabla .= '
          </tbody>
        </table>
    </div>';

    $respuesta = array(
        'status'    => 'success',
        'actividad' => $tabla
    );

    echo json_encode($respuesta);
    return;
}


  //// Guardar el ajuste de los campoa del trimestre si corresponde
  public function guardar_ajuste_campo_consolidado() {
    // 1. Validar que la petición sea estrictamente AJAX
    if (!$this->input->is_ajax_request()) {
        show_404();
        return;
    }

    // 2. Capturar y asegurar los parámetros del POST
    $eval_id = intval($this->input->post('eval_id'));
    $campo   = trim($this->input->post('campo'));
    $valor   = intval($this->input->post('valor'));

    // Lista blanca para evitar inyección de columnas maliciosas
    $campos_permitidos = array('poa_prog', 'poa_cumplidos', 'poa_proceso', 'poa_no_cumplidos');

    if ($eval_id == 0 || !in_array($campo, $campos_permitidos) || $valor < 0) {
        echo json_encode(array('status' => 'error', 'message' => 'Parámetros o valores de ajuste no válidos.'));
        return;
    }

    // 3. Actualizar el campo modificado en la base de datos
    $update = array($campo => $valor);
    $this->db->where('eval_id', $eval_id);
    $this->db->update('detalle_evaluacion_poa_trimestral', $this->security->xss_clean($update));

    // 4. 🌟 OBTENER DATOS ACTUALIZADOS: Consultamos la fila completa tras la edición
    $this->db->where('eval_id', $eval_id);
    $query = $this->db->get('detalle_evaluacion_poa_trimestral');
    $registro_actualizado = $query->row_array(); // Ahora sí contiene la información real

    if (empty($registro_actualizado)) {
        echo json_encode(array('status' => 'error', 'message' => 'No se pudo recuperar el registro consolidado para recalcular los porcentajes.'));
        return;
    }

    // Asignamos las variables con los datos de la fila de la base de datos
    $poa_prog         = intval($registro_actualizado['poa_prog']);
    $poa_cumplidos    = intval($registro_actualizado['poa_cumplidos']);
    $poa_proceso      = intval($registro_actualizado['poa_proceso']);
    $poa_no_cumplidos = intval($registro_actualizado['poa_no_cumplidos']);

    // Inicializar porcentajes con precisión decimal para este corte trimestral
    $pct_cumplimiento        = 0.00;
    $pct_proceso            = 0.00;
    $pct_no_cumplimiento    = 0.00;
    $pct_no_cumplimiento_tot = 0.00;

    // 5. 🧮 CALCULO MATEMÁTICO DE PORCENTAJES (Protegiendo la división entre cero)
    if ($poa_prog > 0) {
        $pct_cumplimiento        = round(($poa_cumplidos * 100) / $poa_prog, 2);
        $pct_proceso            = round(($poa_proceso * 100) / $poa_prog, 2);
        $pct_no_cumplimiento    = round(($poa_no_cumplidos * 100) / $poa_prog, 2);
        $pct_no_cumplimiento_tot = round((($poa_proceso + $poa_no_cumplidos) * 100) / $poa_prog, 2);
    }

    // 6. PERSISTENCIA: Guardamos los nuevos porcentajes calculados en la base de datos
    $data_porcentajes = array(
        'porcentaje_cumplimiento'          => $pct_cumplimiento,
        'porcentaje_proceso'               => $pct_proceso,
        'porcentaje_no_cumplimiento'       => $pct_no_cumplimiento,
        'porcentaje_no_cumplimiento_total' => $pct_no_cumplimiento_tot
    );

    $this->db->where('eval_id', $eval_id);
    $this->db->update('detalle_evaluacion_poa_trimestral', $data_porcentajes);

    // 7. Retornar los porcentajes ya listos al frontend
    $respuesta = array(
        'status'                    => 'success',
        'nuevo_pct_cumplimiento'    => number_format($pct_cumplimiento, 2, '.', ''),
        'nuevo_pct_proceso'         => number_format($pct_proceso, 2, '.', ''),
        'nuevo_pct_no_cumplimiento' => number_format($pct_no_cumplimiento, 2, '.', '')
    );

    echo json_encode($respuesta);
    return;
  }





    //// Get Obtiene (lista) Seguimiento de Actividades x Unidad Responsable
    public function obtener_detalle_seguimiento_de_actividad_x_UnidadResponsable() {
      $com_id = intval($this->input->post('com_id'));
      $componente = $this->model_componente->get_componente($com_id,$this->gestion);
      $form4 = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_anual($com_id);
      
      if (count($form4) == 0) {
          echo json_encode(array('status' => 'error', 'message' => 'Listado no encontrada.'));
          return;
      }
      
      $trimestre  = $this->tmes; // Puedes cambiarlo por: intval($this->input->post('trimestre')) si viaja por POST
      $mes_inicio = (($trimestre - 1) * 3) + 1;
      $mes_fin    = $trimestre * 3;

      $tabla = '
        <script type="text/javascript">
          jQuery(document).ready(function() {
              // Escuchar el evento de escritura en el input del buscador
              jQuery("#buscar_actividad_poa").on("keyup", function() {
                  var valorBusqueda = jQuery(this).val().toLowerCase();

                  // Filtrar cada fila que tenga la clase "fila-actividad"
                  jQuery("#tabla_seguimiento_poa_principal .fila-actividad").each(function() {
                      var textoFila = jQuery(this).text().toLowerCase();

                      // Si el texto de la fila contiene lo buscado, la muestra; si no, la oculta
                      if (textoFila.indexOf(valorBusqueda) > -1) {
                          jQuery(this).show();
                      } else {
                          jQuery(this).hide();
                      }
                  });
              });
          });
      </script>

  <!-- 🌟 Añadida clase identificadora al título para rescatarlo en el CSS de impresión -->
  <h2 class="titulo-reporte-print">'.$componente[0]['aper_programa'].' '.$componente[0]['aper_proyecto'].' '.$componente[0]['aper_actividad'].' - '.$componente[0]['tipo'].' '.$componente[0]['act_descripcion'].' - '.$componente[0]['abrev'].'  / <b>'.$componente[0]['serv_cod'].' </b>'.$componente[0]['tipo_subactividad'].' '.$componente[0]['serv_descripcion'].'</h2>

  <div class="row noprint" style="margin-bottom: 15px;">
    <div class="col-md-6 col-sm-8 col-xs-12">
        <div class="input-group">
            <span class="input-group-addon" style="background: #334155; color: #fff; border: 1px solid #334155;"><i class="fa fa-search"></i></span>
            <input type="text" id="buscar_actividad_poa" class="form-control" placeholder="Buscar por código, actividad o unidad..." style="height: 34px; font-weight: bold;">
        </div>
    </div>
    <div class="col-md-6 col-sm-4 col-xs-12 text-right">
        <!-- 🖨️ Botón que dispara la ventana de impresión nativa -->
        <a class="btn btn-primary" href="javascript:abreVentana(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$componente[0]['com_id'].'\');" disabled="true" style="background: #0284c7; border: none; height: 34px; font-weight: bold; padding: 0 15px;"><i class="fa fa-print"></i> Imprimir Reporte</a>
    </div>
  </div>

  <div class="table-responsive">
    <table id="tabla_seguimiento_poa_principal" class="table table-bordered table-condensed" style="width: 100%; margin-bottom: 0; font-family: sans-serif; table-layout: fixed;">
      <thead>
        <thead>
        <tr style="font-size: 11px; background: #334155; color: #ffffff;">
          <th style="width: 5%; text-align:center; vertical-align: middle;">COD.</th>
          <th style="width: 15%; text-align:center; vertical-align: middle;">ACTIVIDAD</th>
          <th style="width: 12%; text-align:center; vertical-align: middle;">UNIDAD RESPONSABLE</th>
          <th style="width: 12%; text-align:center; vertical-align: middle;">MEDIO DE VERIFICACIÓN</th> 
          <th style="width: 5%; text-align:center; vertical-align: middle;">META</th>
          
          <th style="width: 6%; text-align:center; vertical-align: middle;">ENE</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">FEB</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">MAR</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">ABR</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">MAY</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">JUN</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">JUL</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">AGO</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">SEP</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">OCT</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">NOV</th>
          <th style="width: 6%; text-align:center; vertical-align: middle;">DIC</th>
        </tr>
      </thead>
      <tbody>';
      foreach($form4 as $rowp){
        $tp_indi = ($rowp['indi_id'] == 2) ? '%' : '';
        
        $tabla.='
        <tr class="fila-actividad">
          <td style="white-space: normal; line-height: 1.4; text-align: center; font-weight: bold; color: #1e293b; vertical-align: middle; font-size:14px; background: #f8fafc;" title="'.$rowp['prod_id'].'">
            '.$rowp['or_codigo'].'.'.$rowp['prod_cod'].'
          </td>
          <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
            '.strtoupper($rowp['prod_producto']).'
          </td>
          <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
            '.strtoupper($rowp['prod_unidades']).'
          </td>
          <td style="white-space: normal; line-height: 1.4; text-align: justify; font-size: 11px; color: #334155; vertical-align: middle;">
            '.strtoupper($rowp['prod_fuente_verificacion']).'
          </td>
          <td style="white-space: normal; text-align: center; font-weight: bold; color: #1e3a8a; vertical-align: middle; font-size:13px; background: #f1f5f9;">
            '.round($rowp['prod_meta'], 2).' '.$tp_indi.'
          </td>';
          
          for ($i = 1; $i <= 12; $i++) {
            $color = ($i >= $mes_inicio && $i <= $mes_fin) ? '#e0f2fe' : '#ffffff';
            $tabla .= '
            <td style="white-space: normal; padding: 4px; vertical-align: middle; background: '.$color.'; font-size: 11.5px;">
              <div style="border-bottom: 1px dashed #cbd5e1; padding-bottom: 2px; margin-bottom: 2px;">
                <span style="color:#64748b; font-weight:bold;">P:</span> 
                <span style="font-weight:bold; float:right; color:#0284c7;">'.round($rowp['mes'.$i], 2).$tp_indi.'</span>
                <div style="clear:both;"></div>
              </div>
              <div>
                <span style="color:#64748b; font-weight:bold;">E:</span> 
                <span style="font-weight:bold; float:right; color:#16a34a;">'.round($rowp['e_mes'.$i], 2).$tp_indi.'</span>
                <div style="clear:both;"></div>
              </div>
            </td>';
          }
          
        // 🌟 CORREGIDO: Cerramos la variable concatenada e inyectamos el tag tr de forma estricta
        $tabla .= '</tr>';
      }
      $tabla .= '
          </tbody>
        </table>
      </div>';

      $respuesta = array(
          'status'    => 'success',
          'actividad' => $tabla
      );

      echo json_encode($respuesta);
      return;
  }

  /// Guardar Registro Mensual para Seguimiento o Evaluacion POA x Actividad
  public function guardar_seguimiento() {
    // 1. Capturar de forma segura las variables enviadas por el POST de jQuery
    $prod_id   = intval($this->input->post('prod_id'));
    $mes       = intval($this->input->post('mes'));
    $ejecutado = floatval($this->input->post('ejecutado'));
    
    // Sanitizar cadenas de texto quitando espacios innecesarios
    $verificacion = trim($this->input->post('verificacion'));
    $problemas    = trim($this->input->post('problemas'));
    $acciones     = trim($this->input->post('acciones'));

    $trimestre_row = $this->model_evaluacionpoa->get_trimestre($this->tmes);
    $trm_activo = isset($trimestre_row[0]['trm_id']) ? intval($trimestre_row[0]['trm_id']) : 1;

    // 2. Validación de seguridad básica: Evitar ceros sin justificación
    if ($ejecutado == 0 && (empty($problemas) || empty($acciones))) {
        $respuesta = array(
            'status'  => 'error',
            'message' => 'Los campos problemas y acciones son obligatorios cuando la ejecución es cero o está vacía.'
        );
        echo json_encode($respuesta);
        return;
    }

    // 🚀 3. VALIDACIÓN MATEMÁTICA CALIBRADA (Control de Sobreejecución Trimestral)
    // Recuperamos los datos del producto vigentes en la vista
    $eval = $this->model_evaluacionpoa->get_form4_seguimiento_poa($prod_id);
    
    if (!empty($eval)) {
        // Determinamos los límites del trimestre en evaluación
        $mes_inicio = (($trm_activo - 1) * 3) + 1;
        $mes_fin    = $trm_activo * 3;
        
        // Sumamos lo programado en el trimestre actual + el saldo arrastrado de trimestres anteriores
        $programado_trimestre = floatval($eval[0]['prog_trm' . $trm_activo]);
        $saldo_arrastre_previo = 0.00;
        
        // El arrastre acumulado real proviene estrictamente del saldo del trimestre anterior inmediato (si no es el T1)
        if ($trm_activo > 1) {
            $saldo_arrastre_previo = floatval($eval[0]['saldo_acumulado_trm' . ($trm_activo - 1)]);
        }
        
        // Meta total disponible para gastar en este periodo de 3 meses
        $meta_total_disponible = $programado_trimestre + $saldo_arrastre_previo;

        // Consultamos la base de datos para ver cuánto se ha gastado en los OTROS meses del mismo trimestre
        $sql_otros_meses = "SELECT SUM(pejec_fis) AS total_otros 
                            FROM prod_ejecutado_mensual 
                            WHERE prod_id = ? 
                              AND m_id BETWEEN ? AND ? 
                              AND m_id != ? 
                              AND g_id = ?";
        $query_otros = $this->db->query($sql_otros_meses, array($prod_id, $mes_inicio, $mes_fin, $mes, $this->gestion));
        $res_otros = $query_otros->row_array();
        $ejecutado_otros_meses = isset($res_otros['total_otros']) ? floatval($res_otros['total_otros']) : 0.00;

        // El tope físico permitido para este mes específico es la meta total menos lo que ya consumieron los otros meses
        $limite_maximo_mes = $meta_total_disponible - $ejecutado_otros_meses;

        if ($ejecutado > $limite_maximo_mes) {
            $respuesta = array(
                'status'  => 'error',
                'message' => 'El valor registrado (' . $ejecutado . ') supera el límite disponible para este mes (' . number_format($limite_maximo_mes, 2) . ') en el ' . $trimestre_row[0]['trm_descripcion'] . '. Revise la distribución de la ejecución mensual.'
            );
            echo json_encode($respuesta);
            return;
        }
    }

    // 4. Limpiar registros previos en ambas tablas para evitar duplicidad de estados
    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_ejecutado_mensual');

    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_no_ejecutado_mensual');

    $id_seguimiento = 0;

    // 5. Inserción según el valor físico de ejecución
    if ($ejecutado > 0) {
        $data = array(
            'prod_id'            => $prod_id,
            'm_id'               => $mes,
            'pejec_fis'          => $ejecutado,
            'g_id'               => $this->gestion,
            'fun_id'             => $this->fun_id,
            'medio_verificacion' => strtoupper($verificacion),
            'observacion'        => strtoupper($problemas),
            'acciones'           => strtoupper($acciones),
        );
        $this->db->insert('prod_ejecutado_mensual', $data);
        $id_seguimiento = intval($this->db->insert_id());
    } 
    else {
        $data = array(
            'prod_id'            => $prod_id,
            'm_id'               => $mes,
            'g_id'               => $this->gestion,
            'medio_verificacion' => strtoupper($verificacion),
            'observacion'        => strtoupper($problemas),
            'acciones'           => strtoupper($acciones),
        );
        $this->db->insert('prod_no_ejecutado_mensual', $data);
        $id_seguimiento = intval($this->db->insert_id());
    }

    // 🚀 6. RE-EVALUACIÓN EN CALIENTE: Recuperamos los nuevos saldos actualizados desde la vista PostgreSQL
    $eval_actualizado = $this->model_evaluacionpoa->get_form4_seguimiento_poa($prod_id);
    
    $saldo_actual = isset($eval_actualizado[0]['saldo_acumulado_trm' . $this->tmes]) ? floatval($eval_actualizado[0]['saldo_acumulado_trm' . $this->tmes]) : 0.00;
    $cumplimiento_txt = isset($eval_actualizado[0]['cumplimiento_trm' . $this->tmes]) ? $eval_actualizado[0]['cumplimiento_trm' . $this->tmes] : 'PENDIENTE';

    // Semáforo dinámico para el letrero de pendiente que retorna al HTML
    $color_pendiente = ($saldo_actual > 0) ? '#dc2626' : '#16a34a'; // Rojo si debe saldo, Verde si está en 0 o sobrepasado

    $valor_pendiente = '<div style="font-size:10px; color:' . $color_pendiente . '; font-weight:bold;">' .
                            'Pendiente: ' . round($saldo_actual, 2) . 
                       '</div>';

    // 7. Retornar el paquete JSON asíncrono limpio hacia el JavaScript
    $respuesta = array(
        'status'           => 'success',
        'v_pendiente_act'  => $valor_pendiente,
        'calificacion_act' => $cumplimiento_txt,
        'id_seguimiento'   => $id_seguimiento
    );

    echo json_encode($respuesta);
    return;
}



    //// eliminar Registro x Actividad
public function eliminar_seguimiento() {
    // 1. Capturar de manera segura las variables enviadas por el método POST de jQuery
    $id_seguimiento = intval($this->input->post('id_seguimiento'));
    $prod_id        = intval($this->input->post('prod_id'));
    $mes            = intval($this->input->post('mes'));

    // 2. Validación de seguridad básica
    if ($prod_id == 0 || $mes == 0) {
        $respuesta = array(
            'status'  => 'error',
            'message' => 'Parámetros insuficientes para procesar la eliminación.'
        );
        echo json_encode($respuesta);
        return;
    }

    // 3. Ejecutar el borrado físico en la tabla de ejecutados
    if ($id_seguimiento > 0) {
        $this->db->where('peg_id', $id_seguimiento);
    }
    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_ejecutado_mensual');

    // 4. Ejecutar el borrado físico en la tabla de no ejecutados (justificaciones)
    if ($id_seguimiento > 0) {
        $this->db->where('ne_id', $id_seguimiento);
    }
    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_no_ejecutado_mensual');

    // 🌟 5. RE-EVALUACIÓN EN CALIENTE: Definir el trimestre activo para el recálculo cronológico
    $trimestre_row = $this->model_evaluacionpoa->get_trimestre($this->tmes);
    //$trm_activo = isset($trimestre_row['trm_id']) ? intval($trimestre_row['trm_id']) : 1;

    // Recuperamos los nuevos saldos actualizados desde la vista PostgreSQL
    // Removidos los índices [0] debido al uso nativo de row_array() en el modelo
    $eval_actualizado = $this->model_evaluacionpoa->get_form4_seguimiento_poa($prod_id);
    
    $saldo_actual = isset($eval_actualizado[0]['saldo_acumulado_trm' . $this->tmes]) ? floatval($eval_actualizado[0]['saldo_acumulado_trm' . $this->tmes]) : 0.00;
    $cumplimiento_txt = isset($eval_actualizado[0]['cumplimiento_trm' . $this->tmes]) ? $eval_actualizado[0]['cumplimiento_trm' . $this->tmes] : 'PENDIENTE';

    // Semáforo dinámico para el letrero de pendiente que retorna al HTML
    $color_pendiente = ($saldo_actual > 0) ? '#dc2626' : '#16a34a'; // Rojo si debe saldo, Verde si está en 0 o sobrepasado

    $valor_pendiente = '<div style="font-size:10px; color:' . $color_pendiente . '; font-weight:bold;">' .
                            'Pendiente: ' . round($saldo_actual, 2) . 
                       '</div>';

    $respuesta = array(
        'status'           => 'success',
        'v_pendiente_act'  => $valor_pendiente,
        'calificacion_act' => $cumplimiento_txt,
        'message'          => 'El registro fue eliminado correctamente de la base de datos.'
    );

    echo json_encode($respuesta);
    return;
}




  //// Obtener cuadros de Evaluacion (Graficos) x Unidad Responsable, Unidad Organizacional
  public function obtener_graficos_cumplimiento() {
      // 1. Capturar el identificador del componente de forma segura
      $tp_nivel = intval($this->input->post('tp_nivel'));
      if($tp_nivel==0){ /// Unidad Reponsable (Componente)
        $id = intval($this->input->post('id'));
        $Unidad_data = $this->model_componente->get_componente($id, $this->gestion); /// UnidadReponsable
      }
      else{ /// Unidad Organizacional (Proyecto)
        $id = intval($this->input->post('id'));
        $Unidad_data = $this->model_proyecto->get_UnidadOrganizacional($id); /// UnidadOrganizacional
      }
      
      if ($id == 0 || empty($Unidad_data)) {
          $respuesta = array(
              'status'  => 'error',
              'message' => 'Identificador de UnidadReponsable / Unidad Organizacional no válido o no encontrado.'
          );
          echo json_encode($respuesta);
          return;
      }

      // Inicializamos variables acumuladoras fuera del bucle (para actualizar informacion de la evaluacion)
      if($tp_nivel==0){
        if($this->lib_seguimientopoa->verif_eval()==true || $tp_adm==1){ /// se actualiza mientras este vigente el plazo
          $this->lib_seguimientopoa->migracion_evaluacion_poa_UnidadResponsable($id,$Unidad_data);
        }
        
         // Obtener listado consolidado para la subtabla e inyección del gráfico de regresión
        $lista_evaluacion_uresponsable = $this->model_evaluacionpoa->lista_consolidado_evaluacion_trimestral_Unidadresponsable($id); 
        $nombre_unidad = $Unidad_data[0]['tipo'].' '.$Unidad_data[0]['proy_nombre'].' '.$Unidad_data[0]['abrev'].' / '.$Unidad_data[0]['tipo_subactividad'].' '.$Unidad_data[0]['com_componente'];
        $btn_reporte = '
        <a href="javascript:abreVentana(\''.site_url('eval/reporte_eval_poa/'.$id.'/'.$this->tmes).'\' );" class="btn btn-success" style="background: #16a34a; border: none; font-weight: bold; color:#fff; padding: 6px 16px; margin-right: 5px;">
          <i class="fa fa-file-pdf-o"></i> Generar Formulario de Evaluación POA PDF
        </a>';
      }
      else{
        if($this->lib_seguimientopoa->verif_eval()==true || $tp_adm==1){ /// se actualiza mientras este vigente el plazo
          $unidades_responsables=$this->model_componente->lista_UnidadesResponsables($id);
          foreach ($unidades_responsables as $row) {
            $this->lib_seguimientopoa->migracion_evaluacion_poa_UnidadResponsable($row['com_id'],$Unidad_data);
          }
        }
        // Obtener listado consolidado para la subtabla e inyección del gráfico de regresión
        $lista_evaluacion_uresponsable = $this->model_evaluacionpoa->lista_consolidado_evaluacion_trimestral_UnidadOrganizacional($id); 
        $nombre_unidad = $Unidad_data[0]['tipo'].' '.$Unidad_data[0]['proy_nombre'].' '.$Unidad_data[0]['abrev'];
        $btn_reporte = '';
      }

      $trimestre_row=$this->model_evaluacionpoa->get_trimestre($this->tmes);
      $tabla = '
        <div class="table-responsive">
          <table class="table table-bordered table-striped" style="width: 100%; font-size: 11.5px; margin-top: 15px; font-family: sans-serif;">
              <thead>
                <tr style="background: #475569; color: #ffffff; height: 32px;">
                  <th style="vertical-align: middle; width: 22%; text-align:left; padding-left: 10px;">DETALLE DE EVALUACIÓN</th>';
                  // 🌟 CORRECTO: Generar los trimestres de forma HORIZONTAL como columnas de la cabecera
                  foreach ($lista_evaluacion_uresponsable as $fila) {
                      $tabla .= '<th style="vertical-align: middle; text-align:center;">' . strtoupper($fila['trimestre']) . '</th>';
                  }
                  
                $tabla .= '
                </tr>
              </thead>
            <tbody>';

            // Fila 1: Programados
            $tabla .= '<tr><td style="padding: 6px; padding-left: 10px;"><strong>Total Programado</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #0284c7; '.$bg.'">' . $fila['poa_prog'] . '</td>';
            }
            $tabla .= '</tr>';

            // Fila 2: Cumplidos
            $tabla .= '<tr><td style="padding: 6px; padding-left: 10px;"><strong>Actividades Cumplidas</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #16a34a; '.$bg.'">' . $fila['poa_cumplidos'] . '</td>';
            }
            $tabla .= '</tr>';

            // Fila 3: En Proceso
            $tabla .= '<tr><td style="padding: 6px; padding-left: 10px;"><strong>Actividades En Proceso</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #f59e0b; '.$bg.'">' . $fila['poa_proceso'] . '</td>';
            }
            $tabla .= '</tr>';

            // Fila 4: No Cumplidos
            $tabla .= '<tr><td style="padding: 6px; padding-left: 10px;"><strong>Actividades No Cumplidas</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #ef4444; '.$bg.'">' . $fila['poa_no_cumplidos'] . '</td>';
            }
            $tabla .= '</tr>';

            // Fila 5: Porcentaje Cumplimiento
            $tabla .= '<tr style="background: #f8fafc;"><td style="padding: 6px; padding-left: 10px;"><strong>(%) Cumplimiento</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e3a8a; '.$bg.'">' . $fila['porcentaje_cumplimiento'] . '%</td>';
            }
            $tabla .= '</tr>';

            // Fila 6: Porcentaje No Cumplimiento
            $tabla .= '<tr style="background: #f8fafc;"><td style="padding: 6px; padding-left: 10px;"><strong>(%) No Cumplimiento</strong></td>';
            foreach ($lista_evaluacion_uresponsable as $fila) {
                $bg = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4;' : '';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #475569; '.$bg.'">' . $fila['porcentaje_no_cumplimiento_total'] . '%</td>';
            }
            $tabla .= '</tr>';

            // Fila 7: calificacion
            $tabla .= '<tr style="background: #f1f5f9;">
                        <td style="padding: 6px; padding-left: 10px; vertical-align: middle;"></td>';
                        
                        foreach ($lista_evaluacion_uresponsable as $fila) {
                            // Resaltado de la columna del trimestre activo
                            $bg_columna = ($fila['trm_id'] == $this->tmes) ? 'background-color: #d3f5f4 !important;' : '';
                            
                            // Mapeo dinámico de colores hexadecimales basado en el alias de tu base de datos
                            $color_badge = '#64748b'; // Color gris por defecto (SIN RANGO)
                            
                            switch ($fila['color_semaforo']) {
                                case 'success':
                                    $color_badge = '#10b981'; // Verde (Óptimo)
                                    break;
                                case 'info':
                                    $color_badge = '#0284c7'; // Azul (Bueno)
                                    break;
                                case 'warning':
                                    $color_badge = '#f59e0b'; // Naranja/Amarillo (Regular)
                                    break;
                                case 'danger':
                                    $color_badge = '#ef4444'; // Rojo (Insatisfactorio)
                                    break;
                            }

                            $tabla .= '
                            <td style="text-align: center; vertical-align: middle; ' . $bg_columna . ' padding: 5px;">
                                <!-- 🌟 Insignia/Badge estilizada con el color exacto del semáforo -->
                                <span style="display: inline-block; padding: 4px 10px; background-color: ' . $color_badge . '; color: #ffffff; font-weight: bold; font-size: 10px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.15); width: 90%; text-transform: uppercase; white-space: normal; line-height: 1.2;">
                                    ' . $fila['parametro'] . '
                                </span>
                            </td>';
                        }
                    $tabla .= '</tr>';

        $tabla .= '
      </tbody>
    </table>

  </div>';


      // Obtener los datos consolidados específicos del trimestre actual seleccionado para el pastel
      if($tp_nivel==0){
        $datos_trimestre_actual = $this->model_evaluacionpoa->get_lista_consolidado_evaluacion_trimestral($id, $this->tmes); /// componente
      }
      else{
        $datos_trimestre_actual = $this->model_evaluacionpoa->get_lista_consolidado_evaluacion_trimestral_UnidadOrganizacional($id, $this->tmes); /// Proyecto
        $lista_UniResponsables =$this->model_evaluacionpoa->get_lista_consolidado_evaluacion_trimestral_de_UnidadResponsable_x_UnidadOrganizacional($id,$this->tmes);
        $tabla.='
          DETALLE CUMPLIMIENTO TRIMESTRAL POR UNIDAD RESPONSABLE: 
          <table class="table table-bordered table-striped" style="width: 100%; font-size: 11.5px; margin-top: 15px; font-family: sans-serif;">
            <thead>
              <tr style="background: #475569; color: #ffffff; height: 32px;">
                <th style="vertical-align: middle; width: 2%; text-align:center; padding-left: 10px;">#</th>
                <th style="vertical-align: middle; width: 20%; text-align:center; padding-left: 10px;">UNIDAD RESPONSABLE</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">N° PROG.</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">N° CUMP.</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">N° PROC.</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">N° NO CUMP.</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">(%) CUMP.</th>
                <th style="vertical-align: middle; width: 8%; text-align:center; padding-left: 10px;">(%) NO CUMP.</th>
                <th style="vertical-align: middle; width: 10%; text-align:center; padding-left: 10px;"></th>
              </tr>
            </thead>
            <tbody>';
            $nro=0;
            foreach ($lista_UniResponsables as $fila) {
              $nro++;
              $tabla.='
              <tr>
                <td style="text-align: center; font-weight: bold; ">'.$nro.'</td>
                <td style="text-align: left; font-weight: bold; ">' . $fila['tipo_subactividad'] . ' ' . $fila['com_componente'] . '</td>
                <td style="text-align: right; font-weight: bold; ">' . $fila['poa_prog'] . ' </td>
                <td style="text-align: right; font-weight: bold; ">' . $fila['poa_cumplidos'] . '</td>
                <td style="text-align: right; font-weight: bold; ">' . $fila['poa_proceso'] . '</td>
                <td style="text-align: right; font-weight: bold; ">' . $fila['poa_no_cumplidos'] . '</td>
                <td style="text-align: right; font-weight: bold; color: #16a34a;"><b>' . $fila['porcentaje_cumplimiento'] . '%</b></td>
                <td style="text-align: right; font-weight: bold; color: #ef4444;"><b>' . $fila['porcentaje_no_cumplimiento_total'] . '%</b></td>
                <td style="text-align: center; font-weight: bold; ">
                  <button type="button" class="btn btn-'.$fila['color_semaforo'].'" style="font-size:9px;">' . $fila['parametro'] . '</button>
                </td>
              </tr>';
            }
            $tabla.='
            </tbody>
          </table>';
      }
      

      $color_real = '#64748b'; // Gris por defecto
      if (!empty($datos_trimestre_actual)) {
          // ⚠️ Corrección del Typo de tu consulta SQL anterior ('color_semarofo')
          $alias_color = isset($datos_trimestre_actual[0]['color_semarofo']) ? $datos_trimestre_actual[0]['color_semarofo'] : $datos_trimestre_actual[0]['color_semaforo'];
          
          switch ($alias_color) {
              case 'success': $color_real = '#10b981'; break; // Verde
              case 'info':    $color_real = '#0284c7'; break; // Azul
              case 'warning': $color_real = '#f59e0b'; break; // Amarillo/Naranja
              case 'danger':  $color_real = '#ef4444'; break; // Rojo
          }
      }

      // Armamos el banner/div del semáforo con un diseño de cabecera limpio
      $calificacion = '
          <div style="background-color: ' . $color_real . '; color: #ffffff; font-weight: bold; font-size: 13px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 10px 15px; margin-bottom: 20px; text-align: center; font-family: sans-serif; text-transform: uppercase; letter-spacing: 0.5px;">
              <i class="fa fa-flag"></i> CUMPLIMIENTO POA ' . $trimestre_row[0]['trm_descripcion'] . ' / ' . $this->gestion . ' &nbsp;&rarr;&nbsp; ' . number_format($datos_trimestre_actual[0]['porcentaje_cumplimiento'], 2) . '% (' . $datos_trimestre_actual[0]['parametro'] . ')
          </div>';

      $act_cumplidas    = isset($datos_trimestre_actual[0]['porcentaje_cumplimiento']) ? floatval($datos_trimestre_actual[0]['porcentaje_cumplimiento']) : 0;
      $act_no_cumplidas = isset($datos_trimestre_actual[0]['porcentaje_no_cumplimiento']) ? floatval($datos_trimestre_actual[0]['porcentaje_no_cumplimiento']) : 0;
      $act_en_proceso   = isset($datos_trimestre_actual[0]['porcentaje_proceso']) ? floatval($datos_trimestre_actual[0]['porcentaje_proceso']) : 0;


      // Inicializamos los arreglos partiendo desde el punto cero (0) como en tu imagen
      $labels_regresion = array('0', 'I Trimestre', 'II Trimestre', 'III Trimestre', 'IV Trimestre');
      $prog_regresion   = array(0);
      $ejec_regresion   = array(0);

      // Recorremos el consolidado histórico para llenar los puntos del gráfico
      foreach ($lista_evaluacion_uresponsable as $fila) {
          $prog_regresion[] = intval($fila['poa_prog']);
          $ejec_regresion[] = intval($fila['poa_cumplidos']);
      }

      // Lo agregamos al paquete JSON de respuesta
      $respuesta = array(
          'status' => 'success',
          'datos'  => array(
              'calificacion'         => $calificacion,
              'reporte'         => $btn_reporte,
              'trimestre'         => $trimestre_row[0]['trm_descripcion'].' / '.$this->gestion,
              'UnidadResponsable' => $nombre_unidad,

              'tabla_detalle'    => $tabla,
              'act_cumplidas'    => $act_cumplidas,
              'act_no_cumplidas' => $act_no_cumplidas,
              'act_en_proceso'   => $act_en_proceso,
              
              // 🌟 NUEVOS DATOS PARA EL GRÁFICO DE REGRESIÓN/TENDENCIA
              'regresion_labels' => $labels_regresion,
              'regresion_prog'   => $prog_regresion,
              'regresion_ejec'   => $ejec_regresion
          )
      );

      echo json_encode($respuesta);
      return;
  }




    ///// Reporte Evaluacion POA 2027
    public function reporte_formulario_evaluacion_poa($com_id,$mes_trm){
        // 1. Ampliación y control de recursos de hardware en el servidor
        ini_set('memory_limit', '2048M'); 
        set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
        
        // Limpieza preliminar del búfer de salida para proteger el binario del PDF
        if (ob_get_length()) ob_clean();
        $componente=$this->model_componente->get_componente($com_id,$this->gestion); /// GET COMP -> PROY -> APER
        if(count($componente)!=0){
            $pie_report=$mes_trm.'_Evaluacion_'.$componente[0]['tipo_subactividad'].' '.$componente[0]['com_componente'];
            $form4 = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_trimestre($componente[0]['com_id'],$mes_trm); //// listado de actividades por trimestre programados
            $tabla = '';
            $tabla.='
            <table cellpadding="0" cellspacing="0" class="tabla" border=0.05 style="width:100%;">
                  <thead>
                      <tr style="font-size: 7px; background: #4F4D4D; color: #ffffff; height:50px;">
                        <th style="width: 1%; height:18px; text-align:center; vertical-align: middle;">#</th>
                        <th style="width: 3.5%; text-align:center; vertical-align: middle;">CODIGO</th>
                        <th style="width: 12%; text-align:center; vertical-align: middle;">ACTIVIDAD</th>
                        <th style="width: 11%; text-align:center; vertical-align: middle;">RESPONSABLE</th>
                        <th style="width: 11%; text-align:center; vertical-align: middle;">MEDIO DE VERIFICACIÓN</th>
                        <th style="width: 5%; text-align:center; vertical-align: middle;">META '.$this->gestion.'</th>
                        <th style="width: 5%; text-align:center; vertical-align: middle;">META PROG.</th>
                        <th style="width: 5%; text-align:center; vertical-align: middle;">META EJEC.</th>

                        <th style="width: 15.5%; text-align:center; vertical-align: middle;">MEDIO DE VERIFICACIÓN (presentado en evaluación)</th>
                        <th style="width: 11.5%; text-align:center; vertical-align: middle;">PROBLEMAS PRESENTADOS</th>
                        <th style="width: 11%; text-align:center; vertical-align: middle;">ACCIONES REALIZADAS</th>
                        <th style="width: 7%; text-align:center; vertical-align: middle;">ESTADO</th>
                      </tr>
                  </thead>
                  <tbody>';
                  $nro=0;
                  foreach($form4 as $rowp){
                    $tp_indi = ($rowp['indi_id'] == 2) ? '%' : '';
                    $nro++;
                    $tabla.='
                    <tr style="font-size: 7px;">
                      <td style="width: 1%; text-align:center;">'.$nro.'</td>
                      <td style="width: 3.5%; text-align:center; font-size:11px;">
                        <b>'.$rowp['or_codigo'].'.'.$rowp['prod_cod'].'</b>
                      </td>
                      <td style="width: 12%;">
                        '.strtoupper($rowp['prod_producto']).'
                      </td>
                      <td style="width: 11%;">
                        '.strtoupper($rowp['prod_unidades']).'
                      </td>
                      <td style="width: 12%;">
                        '.strtoupper($rowp['prod_fuente_verificacion']).'
                      </td>
                      <td style="width: 5%; text-align:center; font-size:11px;">
                        '.round($rowp['prod_meta'], 2).' '.$tp_indi.'
                      </td>
                      <td style="width: 5%; text-align:center; font-size:11px;">
                        <b>'.round($rowp['prog_trm'.$mes_trm], 2).' '.$tp_indi.'</b>
                      </td>
                      <td style="width: 5%; text-align:center; font-size:11px;">
                        <b>'.round($rowp['ejec_trm'.$mes_trm], 2).' '.$tp_indi.'</b>
                      </td>
                      <td style="width: 15.5%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],1,$mes_trm).'</td>
                      <td style="width: 11.5%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],2,$mes_trm).'</td>
                      <td style="width: 11%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],3,$mes_trm).'</td>
                      <td style="width: 7%; text-align:center;"><b>'.$rowp['cumplimiento_trm'.$mes_trm].'</b></td>';
                    $tabla .= '</tr>';
                  }
                  $tabla.='
                  </tbody>
            </table>';
    
            $data['lista'] = '
              <page orientation="landscape" backtop="60mm" backbottom="25mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$this->lib_seguimientopoa->cabecera_reporte($componente,$mes_trm).'
                </page_header>
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$this->lib_seguimientopoa->pie_evaluacionpoa().'
                    </div>
                </page_footer>
                  <span style="font-size: 7px; font-weight: bold; color: #0f172a; display: block; text-transform: uppercase; letter-spacing: 0.3px;"><b>Detalle de Actividades Evaluadas : </b></span>
                    '.$tabla.'
              </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/evaluacion/evaluacion_form4/reporte_eval_poa', $data, true); 
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


    ///// Reporte Seguimiento Mensual POA
    // public function reporte_formulario_seguimiento_poa($com_id,$mes){
    //     // 1. Ampliación y control de recursos de hardware en el servidor
    //     ini_set('memory_limit', '2048M'); 
    //     set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
        
    //     // Limpieza preliminar del búfer de salida para proteger el binario del PDF
    //     if (ob_get_length()) ob_clean();
    //     $componente=$this->model_componente->get_componente($com_id,$this->gestion); /// GET COMP -> PROY -> APER
    //     if(count($componente)!=0){
    //         $pie_report=$mes_trm.'_Evaluacion_'.$componente[0]['tipo_subactividad'].' '.$componente[0]['com_componente'];
    //         $form4 = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_trimestre($componente[0]['com_id'],$mes_trm); //// listado de actividades por trimestre programados
    //         $tabla = '';
    //         $tabla.='
    //         <table cellpadding="0" cellspacing="0" class="tabla" border=0.05 style="width:100%;">
    //               <thead>
    //                   <tr style="font-size: 7px; background: #4F4D4D; color: #ffffff; height:50px;">
    //                     <th style="width: 1%; height:18px; text-align:center; vertical-align: middle;">#</th>
    //                     <th style="width: 3.5%; text-align:center; vertical-align: middle;">CODIGO</th>
    //                     <th style="width: 12%; text-align:center; vertical-align: middle;">ACTIVIDAD</th>
    //                     <th style="width: 11%; text-align:center; vertical-align: middle;">RESPONSABLE</th>
    //                     <th style="width: 11%; text-align:center; vertical-align: middle;">MEDIO DE VERIFICACIÓN</th>
    //                     <th style="width: 5%; text-align:center; vertical-align: middle;">META '.$this->gestion.'</th>
    //                     <th style="width: 5%; text-align:center; vertical-align: middle;">META PROG.</th>
    //                     <th style="width: 5%; text-align:center; vertical-align: middle;">META EJEC.</th>

    //                     <th style="width: 15.5%; text-align:center; vertical-align: middle;">MEDIO DE VERIFICACIÓN (presentado en evaluación)</th>
    //                     <th style="width: 11.5%; text-align:center; vertical-align: middle;">PROBLEMAS PRESENTADOS</th>
    //                     <th style="width: 11%; text-align:center; vertical-align: middle;">ACCIONES REALIZADAS</th>
    //                     <th style="width: 7%; text-align:center; vertical-align: middle;">ESTADO</th>
    //                   </tr>
    //               </thead>
    //               <tbody>';
    //               $nro=0;
    //               foreach($form4 as $rowp){
    //                 $tp_indi = ($rowp['indi_id'] == 2) ? '%' : '';
    //                 $nro++;
    //                 $tabla.='
    //                 <tr style="font-size: 7px;">
    //                   <td style="width: 1%; text-align:center;">'.$nro.'</td>
    //                   <td style="width: 3.5%; text-align:center; font-size:11px;">
    //                     <b>'.$rowp['or_codigo'].'.'.$rowp['prod_cod'].'</b>
    //                   </td>
    //                   <td style="width: 12%;">
    //                     '.strtoupper($rowp['prod_producto']).'
    //                   </td>
    //                   <td style="width: 11%;">
    //                     '.strtoupper($rowp['prod_unidades']).'
    //                   </td>
    //                   <td style="width: 12%;">
    //                     '.strtoupper($rowp['prod_fuente_verificacion']).'
    //                   </td>
    //                   <td style="width: 5%; text-align:center; font-size:11px;">
    //                     '.round($rowp['prod_meta'], 2).' '.$tp_indi.'
    //                   </td>
    //                   <td style="width: 5%; text-align:center; font-size:11px;">
    //                     <b>'.round($rowp['prog_trm'.$mes_trm], 2).' '.$tp_indi.'</b>
    //                   </td>
    //                   <td style="width: 5%; text-align:center; font-size:11px;">
    //                     <b>'.round($rowp['ejec_trm'.$mes_trm], 2).' '.$tp_indi.'</b>
    //                   </td>
    //                   <td style="width: 15.5%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],1,$mes_trm).'</td>
    //                   <td style="width: 11.5%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],2,$mes_trm).'</td>
    //                   <td style="width: 11%; font-size:6.5px;">'.$this->lib_seguimientopoa->report_verif_medios_verificacion($rowp['prod_id'],3,$mes_trm).'</td>
    //                   <td style="width: 7%; text-align:center;"><b>'.$rowp['cumplimiento_trm'.$mes_trm].'</b></td>';
    //                 $tabla .= '</tr>';
    //               }
    //               $tabla.='
    //               </tbody>
    //         </table>';
    
    //         $data['lista'] = '
    //           <page orientation="landscape" backtop="60mm" backbottom="5mm" backleft="4mm" backright="4mm" pagegroup="new">
    //             <!-- Cabecera Institucional Inalterada -->
    //             <page_header>
    //                 <br><div class="verde"></div>
    //                 '.$this->lib_seguimientopoa->cabecera_reporte($componente,$mes_trm).'
    //             </page_header>
    //             <page_footer>
    //                 <div style="width: 100%; display: block;">
    //                 '.$this->lib_seguimientopoa->pie_evaluacionpoa().'
    //                 </div>
    //             </page_footer>
    //               <span style="font-size: 7px; font-weight: bold; color: #0f172a; display: block; text-transform: uppercase; letter-spacing: 0.3px;"><b>Detalle de Actividades Evaluadas : </b></span>
    //                 '.$tabla.'
    //           </page>';

    //       // 1. Capturamos el HTML estructurado de la vista en una variable
    //       $html_reporte = $this->load->view('admin/evaluacion/evaluacion_form4/reporte_eval_poa', $data, true); 
    //       // 2. Limpieza radical del búfer de CodeIgniter para que Chrome no rechace el PDF
    //       if (ob_get_length()) ob_clean();
    //       // 3. Importación segura del motor conversor usando la ruta física del servidor
    //       require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
    //       try {
    //           // Inicializamos en orientación horizontal ('L' de Landscape / Paysage) para que coincida con tu diseño
    //           $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
    //           $html2pdf->pdf->SetDisplayMode('fullpage');
    //           $html2pdf->writeHTML($html_reporte);
              
    //           // 4. Enviamos el flujo binario limpio directo al visor de Chrome
    //           $html2pdf->Output($pie_report. '.pdf', 'I');
    //       }
    //       catch(HTML2PDF_exception $e) {
    //           echo "Error al compilar el reporte: " . $e;
    //       }
    //       exit;
    //     }
    //     else{
    //         echo "Error !!!";
    //     }
    // }
  

    function menu($mod){
        $enlaces=$this->menu_modelo->get_Modulos($mod);
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