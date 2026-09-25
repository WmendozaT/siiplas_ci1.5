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
            
            //$this->load->library('seguimientopoa');
            $this->load->library('lib_seguimientopoa');
            
        } else {
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }

    /// Actualizando Unidad Responsable
    public function update_uresponsable($form4) {
      foreach($form4 as $rowp) {
        $info='';
        if($rowp['uni_resp']!=0){
          $info=$rowp['unidad_asignado_bolsa'];
        }
        $update_data = array(
          'prod_unidades' => $info
        );
        
        $this->db->where('prod_id', $rowp['prod_id']);
        $this->db->update('_productos', $update_data);
      }
        
      $this->db->trans_complete();
    }

    /*----- Vista de Seguimiento y Evaluacion 2027 ------*/
    public function formulario_seguimiento_poa($com_id){
        $componente = $this->model_componente->get_componente($com_id,$this->gestion);
        // 3. Ahora el objeto $this->programacionpoa existirá correctamente y sin conflictos
        $data['stylo'] = $this->lib_seguimientopoa->estilo_tabla_form4(); 
        $data['titulo']=$this->lib_seguimientopoa->titulo($componente);

        $form4_crudo=$this->model_producto->lista_productos($com_id);
        //$form4 = $this->model_producto->lista_form4_x_unidadresponsable($com_id);
          if($componente[0]['por_id']==1){
            $this->update_uresponsable($form4_crudo);
          }

        
        $data['tabla'] = $this->formulario($componente);
        $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
    }


    /// formulario de SEguimiento / Evaluacion
    function formulario($componente){
    $form4 = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_trimestre($componente[0]['com_id'],$this->tmes); //// listado de actividades por trimestre programados
    $tabla='';
    $trimestre = $this->tmes; // Puedes parametrizarlo dinámicamente según tu vista ($this->input->post('trimestre'))
    $mes_inicio = (($trimestre - 1) * 3) + 1;
    $mes_fin    = $trimestre * 3;

    // Arreglo de nombres de meses para las etiquetas superiores de la subtabla
    $nombres_meses = array(1=>'ENERO', 2=>'FEBRERO', 3=>'MARZO', 4=>'ABRIL', 5=>'MAYO', 6=>'JUNIO', 7=>'JULIO', 8=>'AGOSTO', 9=>'SEPTIEMBRE', 10=>'OCTUBRE', 11=>'NOVIEMBRE', 12=>'DICIEMBRE');
    $tabla.='
    <input type="hidden" name="base" value="'.base_url().'">
    <table id="datatable_fixed_column" class="table table-bordered" style="width: 130%; table-layout: fixed;">
        <thead>
            <tr style="vertical-align: middle;">
                <th class="hasinput" style="width:1.5%; text-align: center;"></th>
                <th style="width:4%; text-align: center;"></th>
                <th class="hasinput" style="width:1.5%; text-align: center;"></th>
                <th class="hasinput" style="width:1.5%; text-align: center;">
                    <input type="text" class="form-control" placeholder="COD. ACT."/>
                </th>
                <th class="hasinput" style="width:7%; text-align: center;">
                    <input type="text" class="form-control" placeholder="ACTIVIDAD"/>
                </th>
                <th class="hasinput" style="width:4%; text-align: center;">
                    <input type="text" class="form-control" placeholder="UNIDAD RESPONSABLE"/>
                </th>
                <th class="hasinput" style="width:4%; text-align: center;">
                    <input type="text" class="form-control" placeholder="MEDIO DE VERIFICACION"/>
                </th>
                <th class="hasinput" style="width:3%; text-align: center;">
                    <input type="text" class="form-control" placeholder="META"/>
                </th>
                <!-- 🌟 BOTONES DINÁMICOS DE OCULTAR/MOSTRAR EN LA FILA DE FILTROS -->';
                for ($m = $mes_inicio; $m <= $mes_fin; $m++) {
                  $tabla.='
                  <th class="hasinput col-mes-'.$m.'" style="width:33%; text-align: center; padding: 4px; overflow: hidden; white-space: nowrap;">
                      <button type="button" class="btn btn-xs btn-default btn-block" id="btn_toggle_'.$m.'" onclick="toggleColumnaMes('.$m.')" style="background: #475569; color: #ffffff; border: none; font-weight: bold; padding: 4px; font-size: 11px;">
                          <i class="fa fa-eye-slash"></i> Ocultar
                      </button>
                  </th>';
                }
                $tabla.='
            </tr>                          
            <tr>
                <th style="width:1.5%; text-align: center;"></th>
                <th style="width:4%; text-align: center;">
                  <button type="button" 
                      class="btn btn-default btn-xs" 
                      title="Ver detalle seguimiento por Unidad Operativa" 
                      onclick="abrirModalDetalle_UresponsableConAjax('.$componente[0]['com_id'].')" 
                      style="padding: 6px 9px;vertical-align: middle;">
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="20" HEIGHT="20"/>&nbsp;&nbsp;<b>VER</b>
                  </button>
                </th>
                <th style="width:1.5%; text-align: center;" title="CÓDIGO OPERACIÓN">COD.<br>OPE.</th>
                <th style="width:1.5%; text-align: center;" title="CÓDIGO ACTIVIDAD">COD.<br> ACT.</th>
                <th style="width:7%; text-align: center;" title="DETALLE ACTIVIDAD">ACTIVIDAD</th>
                <th style="width:4%; text-align: center;" title="UNIDAD RESPONSABLE">UNIDAD RESPONSABLE</th>
                <th style="width:4%; text-align: center;" title="FUENTE VERIFICACION">MEDIO DE VERIFICACIÓN</th>
                <th style="width:3%; text-align: center;">META</th>';
                for ($m = $mes_inicio; $m <= $mes_fin; $m++) {
                  // 🌟 SEGUNDA FILA DE CABECERA (Nombres de los meses)
                  $tabla.='
                  <th class="col-mes-'.$m.'" style="width:33%; text-align: center; vertical-align: middle; background: #334155; color: #ffffff; overflow: hidden; white-space: nowrap;">
                      <span class="txt-nombre-mes-'.$m.'">'.$nombres_meses[$m].'</span>
                  </th>';
                }
                $tabla.='
            </tr>
        </thead>
        <tbody>';

        foreach($form4 as $rowp){
          $priori='';
          if($rowp['prod_priori']==1){
            $priori='<img src="'.base_url().'assets/ifinal/ok.png" WIDTH="20" HEIGHT="25"/ title="ACTIVIDAD PRIORIZADA AL CUMPLIMIENTO DEL POA">';
          }
          $tp_indi='';
          if($rowp['indi_id']==2){
            $tp_indi='%';
          }
          $prod_id = intval($rowp['prod_id']);
          
          $tabla .= '
          <tr id="fila_prod_'.$prod_id.'" style="vertical-align: middle;">
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;" title="'.$prod_id.'"></td>
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;">
               <button type="button" 
                      class="btn btn-info btn-xs" 
                      title="Ver detalle completo de la Actividad" 
                      onclick="abrirModalDetalleConAjax('.$prod_id.')" 
                      style="padding: 3px 6px;">
                  <i class="fa fa-search"></i> Detalle
              </button>
              </td>
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;" title="'.$prod_id.'"><b>'.round($rowp['og_codigo'],2).'</b></td>
              <td style="width: 5%; text-align: center; font-size:15px; vertical-align: middle;" bgcolor="#eceaea" ">
                  <b>'.round($rowp['prod_cod'],2).'</b><br>'.$priori.'
              </td>
              <td style="width: 15%; text-align: left; font-size:9.5px;vertical-align: middle;">'.strtoupper($rowp['prod_producto']).'</td>
              <td style="width: 10%; text-align: left; font-size:9.5px;vertical-align: middle;">'.strtoupper($rowp['prod_unidades']).'</td>
              <td style="width: 10%; text-align: left; font-size:9.5px;vertical-align: middle;">'.strtoupper($rowp['prod_fuente_verificacion']).'</td>
              <td style="width: 5%; text-align: right; font-weight: bold; color: #1e3a8a; padding-right:8px;vertical-align: middle; font-size:15px;">'.round($rowp['prod_meta'], 2).' '.$tp_indi.'</td>';
              
              for ($m = $mes_inicio; $m <= $mes_fin; $m++) {
                  $v_prog   = floatval($rowp['mes'.$m]);
                  $mes_ejec=0;$mverificacion='';$prob_presentados='';$acciones=''; 
                  
                  // Determinar si hay ID de seguimiento existente para pasar al botón eliminar
                  $id_seguimiento = 0; 
                  
                  $ejec=$this->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id,$m); 
                  if(count($ejec)!=0){ 
                    $id_seguimiento = isset($ejec[0]['peg_id']) ? intval($ejec[0]['peg_id']) : 0;
                    $mes_ejec=round($ejec[0]['pejec_fis'],2);
                    $mverificacion=$ejec[0]['medio_verificacion'];
                    $prob_presentados=$ejec[0]['observacion'];
                    $acciones=$ejec[0]['acciones'];
                  } 
                  else{
                    $no_ejec=$this->model_evaluacionpoa->get_seguimiento_poa_mes_noejec($prod_id,$m);
                    if(count($no_ejec)!=0){
                      $id_seguimiento = isset($no_ejec[0]['ne_id']) ? intval($no_ejec[0]['ne_id']) : 0;
                      $mes_ejec=0;
                      $mverificacion=$no_ejec[0]['medio_verificacion'];
                      $prob_presentados=$no_ejec[0]['observacion'];
                      $acciones=$no_ejec[0]['acciones'];
                    }
                  }
                  
                  // 🌟 NUEVA LÓGICA DE CONTROL VISUAL CON ALERTAS DE COLOR SEMAFÓRICAS
                  $es_deshabilitado = ($v_prog == 0) ? 'disabled' : '';
                  
                  if ($v_prog == 0) {
                      $color_fondo_celda = '#f8fafc'; // Gris sutil para meses sin programar
                  } else {
                      if ($mes_ejec == 0) {
                          $color_fondo_celda = '#fef08a'; // Amarillo suave (Pendiente de registrar)
                      } else {
                          $color_fondo_celda = '#bbf7d0'; // Verde suave (Ya cuenta con ejecución)
                      }
                  }
                  
                  $tabla .= '
                  <td class="col-mes-'.$m.'" style="width: 10%; background: '.$color_fondo_celda.'; padding: 4px; border: 1px solid #cbd5e1;" id="reg'.$m.'" name="prod'.$prod_id.'">
                    <div class="wrapper-mes-'.$m.'">
                      <div class="smart-form">
                       <table class="table table-bordered" style="width:100%; margin-bottom:0;">
                              <thead>
                                <tr style="background: #64748b; color: #ffffff; height:22px; font-size: 10px;">
                                  <th style="width:5%; text-align: center; padding:2px;">PROG.</th>
                                  <th style="width:1%; text-align: center; padding:2px;">EJEC.</th>
                                  <th style="width:32%; text-align: center; padding:2px;">MEDIO VERIF.</th>
                                  <th style="width:32%; text-align: center; padding:2px;">PROBLEMAS</th>
                                  <th style="width:32%; text-align: center; padding:2px;">ACCIONES</th>
                                  <th style="width:5%; text-align: center; padding:2px;">OPCIONES</th>
                                </tr>
                              </thead>
                              <tbody>
                                <tr style="background: #ffffff; height:100px;">
                                  <td style="width:5%; text-align: center; font-weight: bold; color: #16a34a; vertical-align: middle; padding: 2px; font-size:15px;">'.round($v_prog, 2).' '.$tp_indi.'</td>
                                  
                                  <td style="padding: 2px; width:1%; vertical-align: middle;">
                                      <input type="number" step="0.1" class="form-control" style="text-align: right; padding: 2px; height: 30px; font-size: 13.5px; font-weight: bold;" id="ejec_'.$prod_id.'_'.$m.'" value="'.$mes_ejec.'" '.$es_deshabilitado.'>
                                  </td>
                                  <td style="padding: 2px; width:32%; vertical-align: middle;">
                                    <label class="textarea">
                                      <textarea style="font-size: 11px; height: 100px; padding: 2px; resize: vertical;" id="mverif_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$mverificacion.'</textarea>
                                    </label>
                                  </td>
                                  <td style="padding: 2px; width:32%; vertical-align: middle;">
                                    <label class="textarea">
                                      <textarea style="font-size: 11px; height: 100px; padding: 2px; resize: vertical;" id="prob_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$prob_presentados.'</textarea>
                                    </label>
                                  </td>
                                  <td style="padding: 2px; width:32%; vertical-align: middle;">
                                    <label class="textarea">
                                      <textarea style="font-size: 11px; height: 100px; padding: 2px; resize: vertical;" id="acc_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$acciones.'</textarea>
                                    </label>
                                  </td>
                                  <td style="text-align: center; vertical-align: middle; padding: 4px; width: 5%;">
                                      <!-- 💾 Botón Guardar (Solo Icono para optimizar el 5% de ancho) -->
                                      <button type="button" 
                                              class="btn btn-success btn-xs" 
                                              title="Guardar Registro - Mes '.$m.'" 
                                              onclick="guardarSeguimiento('.$prod_id.', '.$m.')" 
                                              style="margin-bottom: 5px; width: 100%; padding: 4px 2px;" 
                                              '.$es_deshabilitado.'>
                                          <i class="fa fa-save"></i>
                                      </button>
                                      
                                      <!-- 🗑️ Botón Eliminar (Se oculta por completo si id es 0 O si la celda está deshabilitada) -->
                                      <button type="button" 
                                              class="btn btn-danger btn-xs" 
                                              title="Eliminar Registro - Mes '.$m.'" 
                                              onclick="eliminarSeguimiento('.$prod_id.', '.$m.', '.$id_seguimiento.')" 
                                              style="width: 100%; padding: 4px 2px; '.($id_seguimiento == 0 || $v_prog == 0 ? 'display:none;' : '').'" 
                                              id="btn_del_'.$prod_id.'_'.$m.'">
                                          <i class="fa fa-trash-o"></i>
                                      </button>
                                  </td>
                                </tr>
                              </tbody>
                            </table>
                          </div>
                      </div>
                  </td>';
              }
          $tabla .= '</tr>';
        }

        $tabla.='
        </tbody>
        </table>';

        $tabla.=$this->lib_seguimientopoa->modal_seguimiento_x_form4();
        $tabla.=$this->lib_seguimientopoa->modal_evaluacion_poa_x_UnidadResponsable();
        return $tabla;
    }









    //// Get Obtiene Seguimiento por Actividad
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
                  '.$get_form4[0]['og_codigo'].'.'.$get_form4[0]['prod_cod'].'
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


    //// Get Obtiene Seguimiento de Actividades por Unidad Responsable
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
          <td style="white-space: normal; line-height: 1.4; text-align: center; font-weight: bold; color: #1e293b; vertical-align: middle; font-size:14px; background: #f8fafc;">
            '.$rowp['og_codigo'].'.'.$rowp['prod_cod'].'
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

    /// Guardar Registro Mensual para Seguimiento o Evaluacion POA
    public function guardar_seguimiento() {
    // 1. Capturar de forma segura las variables enviadas por el POST de jQuery
    $prod_id   = intval($this->input->post('prod_id'));
    $mes       = intval($this->input->post('mes'));
    $ejecutado = floatval($this->input->post('ejecutado'));
    
    // Sanitizar cadenas de texto quitando espacios innecesarios
    $verificacion = trim($this->input->post('verificacion'));
    $problemas    = trim($this->input->post('problemas'));
    $acciones     = trim($this->input->post('acciones'));

    // 2. Validación de seguridad del lado del Servidor (Duplica la seguridad del JS)
    if ($ejecutado == 0 && (empty($problemas) || empty($acciones))) {
        $respuesta = array(
            'status'  => 'error',
            'message' => 'Los campos problemas y acciones son obligatorios cuando la ejecución es cero o está vacía.'
        );
        echo json_encode($respuesta);
        return;
    }

    // 3. Limpiar registros previos en ambas tablas para evitar duplicidad de estados
    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_ejecutado_mensual');

    $this->db->where('prod_id', $prod_id);
    $this->db->where('m_id', $mes);
    $this->db->where('g_id', $this->gestion);
    $this->db->delete('prod_no_ejecutado_mensual');

    // Inicializamos la variable para capturar el ID de inserción
    $id_seguimiento = 0;
    
    $producto = $this->model_producto->get_producto_id($prod_id);

    // 4. Inserción según el valor de ejecución
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
        // 🌟 CAPTURA DEL ID AUTOINCREMENTAL DE LA TABLA EJECUTADO
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
            // 🌟 CAPTURA DEL ID AUTOINCREMENTAL DE LA TABLA NO EJECUTADO
            $id_seguimiento = intval($this->db->insert_id());
        }

        // 5. Retornar la respuesta JSON correcta esperada por el AJAX
        $respuesta = array(
            'status'         => 'success',
            'id_seguimiento' => $id_seguimiento
        );

        echo json_encode($respuesta);
        return;
    }


    //// eliminar Registro
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
      // Buscamos por la llave primaria si vino en el request, u ocupamos el par prod_id/m_id
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

      // 5. Retornar respuesta de éxito en formato JSON para el frontend
      $respuesta = array(
          'status'  => 'success',
          'message' => 'El registro fue eliminado correctamente de la base de datos.'
      );

      echo json_encode($respuesta);
      return;
  }




  public function obtener_graficos_cumplimiento() {
    // 1. Capturar el identificador del componente de forma segura
    $com_id = intval($this->input->post('com_id'));

    if ($com_id == 0) {
        $respuesta = array(
            'status'  => 'error',
            'message' => 'Identificador de componente no válido.'
        );
        echo json_encode($respuesta);
        return;
    }

    // 2. Obtener la lista de productos asociados al componente anual para sumar su programación
    // Usamos el mismo método optimizado anual que ya tienes en tu modelo
    $productos = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_anual($com_id);
    
    $total_programado = 0;
    $array_prod_ids = array();

    // Sumamos aritméticamente las metas programadas de los 12 meses de todos los productos del componente
    foreach ($productos as $rowp) {
        $array_prod_ids[] = intval($rowp['prod_id']);
        
        for ($m = 1; $m <= 12; $m++) {
            $total_programado += floatval($rowp['mes' . $m]);
        }
    }

 $total_ejecutado = 0;

    // 3. Si el componente tiene productos, consultamos las ejecuciones reales en la BD
    if (!empty($array_prod_ids)) {
        $prod_ids_string = implode(',', $array_prod_ids);
        $g_id = intval($this->gestion);

        // Consultamos la sumatoria física real acumulada en la tabla de ejecutados
        $sql_ejec = "SELECT SUM(pejec_fis) AS total_real 
                     FROM prod_ejecutado_mensual 
                     WHERE prod_id IN ($prod_ids_string) 
                       AND g_id = $g_id";
        
        $query_ejec = $this->db->query($sql_ejec);
        $res_ejec = $query_ejec->row_array();
        
        if (isset($res_ejec['total_real'])) {
            $total_ejecutado = floatval($res_ejec['total_real']);
        }
    }

    // 4. Calcular el porcentaje de eficacia física global del componente
    $porcentaje_eficacia = 0;
    if ($total_programado > 0) {
        // Regla de tres simple: (Ejecutado * 100) / Programado
        $porcentaje_eficacia = ($total_ejecutado * 100) / $total_programado;
    // Control de salvaguarda por si la ejecución supera el 100% de la meta por reajustes
        if ($porcentaje_eficacia > 100) {
            $porcentaje_eficacia = 100;
        }
    }

    // 5. Estructurar el paquete de datos formateado para el frontend
    $datos_consolidados = array(
        'total_programado'    => number_format($total_programado, 2, '.', ''),
        'total_ejecutado'     => number_format($total_ejecutado, 2, '.', ''),
        'porcentaje_eficacia' => round($porcentaje_eficacia, 2)
    );

    $respuesta = array(
        'status' => 'success',
        'datos'  => $datos_consolidados
    );

    // 6. Retornar el JSON limpio sin código HTML huérfano
    echo json_encode($respuesta);
    return;
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