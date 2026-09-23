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
            
            $this->load->library('seguimientopoa');
            
        } else {
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }

    /*----- Vista de Seguimiento y Evaluacion 2027 ------*/
    public function formulario_seguimiento_poa($com_id){
        $componente = $this->model_componente->get_componente($com_id,$this->gestion);
        // 3. Ahora el objeto $this->programacionpoa existirá correctamente y sin conflictos
        $data['stylo'] = $this->estilo_tabla_form4(); 
        $data['titulo']=$this->titulo($componente);

        $form4 = $this->model_producto->lista_form4_x_unidadresponsable($com_id);
        $data['tabla'] = $this->formulario($form4);
        $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
    }


    /// formulario de SEguimiento / Evaluacion
    function formulario($form4){
        $tabla='';
 // Identificamos los meses que corresponden al trimestre seleccionado (Ej: Trimestre 1)
    $trimestre = 3; // Puedes parametrizarlo dinámicamente según tu vista ($this->input->post('trimestre'))
    $mes_inicio = (($trimestre - 1) * 3) + 1;
    $mes_fin    = $trimestre * 3;

    // Arreglo de nombres de meses para las etiquetas superiores de la subtabla
    $nombres_meses = array(1=>'ENERO', 2=>'FEBRERO', 3=>'MARZO', 4=>'ABRIL', 5=>'MAYO', 6=>'JUNIO', 7=>'JULIO', 8=>'AGOSTO', 9=>'SEPTIEMBRE', 10=>'OCTUBRE', 11=>'NOVIEMBRE', 12=>'DICIEMBRE');

    foreach($form4 as $rowp){
        $prod_id = intval($rowp['prod_id']);
        
        $tabla .= '
        <tr id="fila_prod_'.$prod_id.'" style="vertical-align: middle; font-size:9px;">
            <td style="text-align: center; font-weight: bold;">'.round($rowp['prod_cod'],2).'</td>
            <td style="width: 5%; text-align: center; font-size:11px;" bgcolor="#eceaea" title="'.$prod_id.'">
                <b>'.round($rowp['prod_cod'],2).'</b>
            </td>
            <td style="width: 15%; text-align: left; font-size:9.5px;">'.$rowp['prod_producto'].'</td>
            <td style="width: 10%; text-align: left; font-size:9.5px;">'.$rowp['prod_unidades'].'</td>
            <td style="width: 10%; text-align: left; font-size:9.5px;">'.$rowp['prod_fuente_verificacion'].'</td>
            <td style="width: 5%; text-align: right; font-weight: bold; color: #1e3a8a; padding-right:8px;">'.number_format($rowp['prod_meta'], 2, ',', '.').'</td>';
            
            // 🌟 BUCLE DINÁMICO DE EVALUACIÓN TRIMESTRAL EN CALIENTE
            for ($m = $mes_inicio; $m <= $mes_fin; $m++) {
                
                // Extraemos los valores ya registrados previamente en tu BD si existieran
                // (Debes adecuar estas variables según los índices que traiga tu consulta de evaluación mensual)
                $v_prog   = floatval($rowp['m'.$m]); 
                $v_ejec   = isset($rowp['m'.$m.'_ejec']) ? floatval($rowp['m'.$m.'_ejec']) : ''; 
                $v_mverif = isset($rowp['m'.$m.'_mverif']) ? $rowp['m'.$m.'_mverif'] : '';
                $v_prob   = isset($rowp['m'.$m.'_prob']) ? $rowp['m'.$m.'_prob'] : '';
                $v_acc    = isset($rowp['m'.$m.'_acciones']) ? $rowp['m'.$m.'_acciones'] : '';

                $tabla .= '
                <td style="width: 18%; background: #f8fafc; padding: 4px; border: 1px solid #cbd5e1;">
                   <table class="table-bordered" style="width:100%;">
                          <thead>
                            <tr style="background: #475569; color: #ffffff; height:24px;">
                              <th colspan="5" style="text-align: center; padding: 2px; font-size: 10px; background: #334155;">'.$nombres_meses[$m].'</th>
                            </tr>
                            <tr style="background: #64748b; color: #ffffff; height:22px; font-size: 9px;">
                              <th style="width:15%; text-align: center; padding:2px;">PROG.</th>
                              <th style="width:20%; text-align: center; padding:2px;">EJEC.</th>
                              <th style="width:25%; text-align: center; padding:2px;">MEDIO VERIF.</th>
                              <th style="width:20%; text-align: center; padding:2px;">PROBLEMAS</th>
                              <th style="width:20%; text-align: center; padding:2px;">ACCIONES</th>
                            </tr>
                          </thead>
                          <tbody>
                            <tr style="background: #ffffff; font-size:9px;">
                              <!-- Meta programada estática de la BD -->
                              <td style="text-align: right; font-weight: bold; color: #16a34a; vertical-align: middle; padding: 2px;">'.number_format($v_prog, 1, ',', '.').'</td>
                              
                              <!-- INPUTS CON IDENTIFICADORES ÚNICOS Y EVENTO ONCHANGE AUTOMÁTICO -->
                              <td style="padding: 2px;">
                                  <input type="number" 
                                         step="0.1" 
                                         class="form-control" 
                                         style="text-align: right; padding: 2px; height: 22px; font-size: 10.5px; font-weight: bold;" 
                                         id="ejec_'.$prod_id.'_'.$m.'" 
                                         value="'.$v_ejec.'" 
                                         onchange="guardarEvaluacionEnCaliente('.$prod_id.', '.$m.', \'ejecuc\');">
                              </td>
                              <td style="padding: 2px;">
                                  <textarea class="form-control" style="font-size: 9.5px; height: 50px; padding: 2px; resize: vertical;" id="mverif_'.$prod_id.'_'.$m.'" onchange="guardarEvaluacionEnCaliente('.$prod_id.', '.$m.', \'mverif\');">'.$v_mverif.'</textarea>
                              </td>
                              <td style="padding: 2px;">
                                  <textarea class="form-control" style="font-size: 9.5px; height: 50px; padding: 2px; resize: vertical;" id="prob_'.$prod_id.'_'.$m.'" onchange="guardarEvaluacionEnCaliente('.$prod_id.', '.$m.', \'proble\');">'.$v_prob.'</textarea>
                              </td>
                              <td style="padding: 2px;">
                                  <textarea class="form-control" style="font-size: 9.5px; height: 50px; padding: 2px; resize: vertical;" id="acc_'.$prod_id.'_'.$m.'" onchange="guardarEvaluacionEnCaliente('.$prod_id.', '.$m.', \'accion\');">'.$v_acc.'</textarea>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                </td>';
            }
            
        $tabla .= '</tr>';
    }
        

        return $tabla;
    }


    //// Verifica tipo de formulario Seguimiento o Evaluacion
    function verif_btn_evaluacionpoa(){
      $tabla='';

      $dia_actual=ltrim(date("d"), "0");
      $mes_actual=ltrim(date("m"), "0");

      $fecha_actual = date('Y-m-d');

      $get_fecha_evaluacion=$this->model_configuracion->get_datos_fecha_evaluacion($this->gestion);
      if(count($get_fecha_evaluacion)!=0){
          $configuracion=$this->model_configuracion->get_configuracion_session();
          $date_actual = strtotime($fecha_actual); //// fecha Actual
          $date_inicio = strtotime($configuracion[0]['eval_inicio']); /// Fecha Inicio
          $date_final = strtotime($configuracion[0]['eval_fin']); /// Fecha Final
          $date_trimestre = $configuracion[0]['conf_mes_otro']; /// Trimestre
          $meses=$this->model_configuracion->list_mes_trimestre($date_trimestre);

            if (($date_actual >= $date_inicio) && ($date_actual <= $date_final)|| $this->tp_adm==1){
              if(count($this->model_configuracion->get_responsables_evaluacion($this->fun_id))!=0){

              $tabla.='

              <section class="col col-3">
               <b style="color:blue;"> MESES A EVALUAR: </b>
                <select class="form-control" id="mes_id" name="mes_id" title="SELECCIONE MES A EVALUAR">
                  <option value="0" selected>Seleccione mes para Evaluacion POA ...</option>';
                foreach($meses as $row){
                  //if($this->verif_mes[1]<=$row['m_id']){
                    if($row['m_id']==$this->verif_mes[1]){ 
                      $tabla.='<option value="'.$row['m_id'].'" selected>'.$row['m_descripcion'].'</option>';
                    }
                    else{ 
                      $tabla.='<option value="'.$row['m_id'].'" >'.$row['m_descripcion'].'</option>';
                    } 
                  //}                     
                }
               $tabla.='
                </select>
              </section>';
            }
          }
      }

      return $tabla;
    }




























    /// Titulo
    public function titulo($componente){
        $tabla='';
        $tabla.='<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <input type="hidden" name="base" value="'.base_url().'">
            '.$this->tmes.' -> '.$this->mes_sistema.'
              <div class="well">
                <h2>'.$componente[0]['aper_programa'].' '.$componente[0]['aper_proyecto'].' '.$componente[0]['aper_actividad'].' - '.$componente[0]['tipo'].' '.$componente[0]['act_descripcion'].' - '.$componente[0]['abrev'].'  / <b>'.$componente[0]['serv_cod'].' </b>'.$componente[0]['tipo_subactividad'].' '.$componente[0]['serv_descripcion'].'</h2>
                  <a href="#" data-toggle="modal" data-target="#modal_nuevo_form" class="btn btn-default nuevo_form" title="NUEVO REGISTRO FORM N 4" >
                    <img src="'.base_url().'assets/Iconos/add.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>NUEVO REGISTRO (ACTIVIDAD)</b>
                  </a>

                  <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
                    <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO ACTIVIDADES.Xls </b>
                  </a>

                 
                    <a href="#" data-toggle="modal" data-target="#modal_importar_f5" class="btn btn-default importar_f5" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO REQUERIMIENTOS.Xls</b>
                    </a>
                    <a href="#" data-toggle="modal" data-target="#modal_ver_form5" class="btn btn-default ver_requerimientos" name="'.$componente[0]['com_id'].'" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>VER MIS REQUERIMIENTOS</b>
                    </a>
                    <a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$componente[0]['com_id'].'\');" class="btn btn-primary" title="REPORTE FORM. 4"> <img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>REPORTE FORM N 4</a>
                    <a onclick="eliminar_form4_todos(this)" class="btn btn-danger" title="Eliminar Actividades de la unidad (todos)">
                        <img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 4 (TODOS)</b>
                    </a>
                    <a onclick="eliminar_requerimientos_UnidadReponsable(this)" class="btn btn-danger" title="Eliminar Solo Requerimientos de la unidad (todos)">
                        <img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 5 (TODOS)</b>
                    </a>
              </div>
            </article>';
    
        return $tabla;
    }

    /// Estilo Formulario
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
}