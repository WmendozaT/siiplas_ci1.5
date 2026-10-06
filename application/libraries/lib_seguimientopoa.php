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


    /// formulario de Evaluacion x Unidad Responsable
    function formulario_evaluacion_UnidadResponsable($componente){
    $form4 = $this->CI->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_trimestre($componente[0]['com_id'],$this->tmes); //// listado de actividades por trimestre programados
    $tabla='';
    $trimestre = $this->tmes; // Puedes parametrizarlo dinámicamente según tu vista ($this->input->post('trimestre'))
    $mes_inicio = (($trimestre - 1) * 3) + 1;
    $mes_fin    = $trimestre * 3;

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
          $valor_pendiente='green';
          if($rowp['saldo_acumulado_trm'.$this->tmes]>0){
            $valor_pendiente='red';
          }

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
              <br><div id="valor_pendiente'.$rowp['prod_id'].'"><div style="font-size:10px; color:'.$valor_pendiente.'"><b>pendiente : '.round($rowp['saldo_acumulado_trm'.$this->tmes], 2).' '.$tp_indi.'</b></div></div>
              <br><div id="valor_cumplimiento'.$rowp['prod_id'].'" <div style="font-size:10px;"><b>'.$rowp['cumplimiento_trm'.$this->tmes].'</b></div></div>
              </td>
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;" title="'.$prod_id.'"><b>'.round($rowp['or_codigo'],2).'</b></td>
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
                  
                  $ejec=$this->CI->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id,$m); 
                  if(count($ejec)!=0){ 
                    $id_seguimiento = isset($ejec[0]['peg_id']) ? intval($ejec[0]['peg_id']) : 0;
                    $mes_ejec=round($ejec[0]['pejec_fis'],2);
                    $mverificacion=$ejec[0]['medio_verificacion'];
                    $prob_presentados=$ejec[0]['observacion'];
                    $acciones=$ejec[0]['acciones'];
                  } 
                  else{
                    $no_ejec=$this->CI->model_evaluacionpoa->get_seguimiento_poa_mes_noejec($prod_id,$m);
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

        $tabla.=$this->modal_seguimiento_x_form4(); /// MD1
        $tabla.=$this->modal_evaluacion_poa_x_UnidadResponsable(); /// Graficos MD2
        return $tabla;
    }


    /// formulario de SEguimiento Mensual x Unidad Responsable
    function formulario_seguimiento_UnidadResponsable($componente){
    $form4 = $this->CI->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable_trimestre($componente[0]['com_id'],$this->tmes); //// listado de actividades por trimestre programados
    $tabla='';
    $trimestre = $this->tmes; // Puedes parametrizarlo dinámicamente según tu vista ($this->input->post('trimestre'))
    $mes_inicio = (($trimestre - 1) * 3) + 1;
    $mes_fin    = $trimestre * 3;
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
                <th></th>
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
                <th style="width:3%; text-align: center;">META</th>
                <th class="col-mes-'.$this->verif_mes.'" style="width:33%; text-align: center; vertical-align: middle; background: #334155; color: #ffffff; overflow: hidden; white-space: nowrap;">
                      <span class="txt-nombre-mes-'.$m.'">'.$nombres_meses[$this->verif_mes].'</span>
                  </th>
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
          $valor_pendiente='green';
          if($rowp['saldo_acumulado_trm'.$this->tmes]>0){
            $valor_pendiente='red';
          }

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
              <br><div id="valor_pendiente'.$rowp['prod_id'].'"><div style="font-size:10px; color:'.$valor_pendiente.'"><b>pendiente : '.round($rowp['saldo_acumulado_trm'.$this->tmes], 2).' '.$tp_indi.'</b></div></div>
              <br><div id="valor_cumplimiento'.$rowp['prod_id'].'" <div style="font-size:10px;"><b>'.$rowp['cumplimiento_trm'.$this->tmes].'</b></div></div>
              </td>
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;" title="'.$prod_id.'"><b>'.round($rowp['or_codigo'],2).'</b></td>
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
                  
                  $ejec=$this->CI->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id,$m); 
                  if(count($ejec)!=0){ 
                    $id_seguimiento = isset($ejec[0]['peg_id']) ? intval($ejec[0]['peg_id']) : 0;
                    $mes_ejec=round($ejec[0]['pejec_fis'],2);
                    $mverificacion=$ejec[0]['medio_verificacion'];
                    $prob_presentados=$ejec[0]['observacion'];
                    $acciones=$ejec[0]['acciones'];
                  } 
                  else{
                    $no_ejec=$this->CI->model_evaluacionpoa->get_seguimiento_poa_mes_noejec($prod_id,$m);
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

        $tabla.=$this->modal_seguimiento_x_form4(); /// MD1
        $tabla.=$this->modal_evaluacion_poa_x_UnidadResponsable(); /// Graficos MD2
        return $tabla;
    }


    /// Verificando el tipo de formulario
    public function verif_eval() {
      $dia_actual=ltrim(date("d"), "0");
      $mes_actual=ltrim(date("m"), "0");
      $fecha_actual = date('Y-m-d');
      $get_fecha_evaluacion=$this->CI->model_configuracion->get_datos_fecha_evaluacion($this->gestion);
      if(count($get_fecha_evaluacion)!=0){
          $configuracion=$this->CI->model_configuracion->get_configuracion_session();
          $date_actual = strtotime($fecha_actual); //// fecha Actual
          $date_inicio = strtotime($configuracion[0]['eval_inicio']); /// Fecha Inicio
          $date_final = strtotime($configuracion[0]['eval_fin']); /// Fecha Final
          if (($date_actual >= $date_inicio) && ($date_actual <= $date_final)){ /// ingresa al formulario de Evaluacion
            return true;
          }
          else{
            return false;
          }
      }
    }


    /// actualizando evaluacion poa
    public function migracion_evaluacion_poa_UnidadResponsable($com_id) {
      $poa_prog          = 0;
      $poa_cumplidos     = 0;
      $poa_proceso       = 0;
      $poa_no_cumplidos  = 0;
      // Bucle para consolidar el histórico acumulado hasta el trimestre seleccionado
      for ($i = 1; $i <= $this->tmes; $i++) { 
          $form4 = $this->CI->model_evaluacionpoa->consolidado_list_formN4_para_evaluacion_UnidadResponsable_trimestre($com_id, $i); 

          // Suma acumulativa incremental por trimestre
          $poa_prog          += intval($form4['poa_prog']);
          $poa_cumplidos     += intval($form4['poa_cumplidos']);
          $poa_proceso       += intval($form4['poa_proceso']);
          $poa_no_cumplidos  += intval($form4['poa_no_cumplidos']);

          // Calcular porcentajes con precisión decimal para este corte trimestral
          $pct_cumplimiento        = 0.00;
          $pct_proceso            = 0.00;
          $pct_no_cumplimiento    = 0.00;
          $pct_no_cumplimiento_tot = 0.00;

          if ($poa_prog > 0) {
              $pct_cumplimiento     = round(($poa_cumplidos * 100) / $poa_prog, 2);
              $pct_proceso         = round(($poa_proceso * 100) / $poa_prog, 2);
              $pct_no_cumplimiento = round(($poa_no_cumplidos * 100) / $poa_prog, 2);
              $pct_no_cumplimiento_tot = round((($poa_proceso + $poa_no_cumplidos) * 100) / $poa_prog, 2);
          }

          // 🌟 Limpiar registros previos consolidados en este corte para evitar duplicaciones
          $this->db->where('com_id', $com_id);
          $this->db->where('trm_id', $i);
          $this->db->where('g_id', $this->gestion);
          $this->db->delete('detalle_evaluacion_poa_trimestral');

          // Insertar registro consolidado limpio
          $data_insert = array(
              'dep_id'                           => intval($componente_data[0]['dep_id']),
              'dist_id'                          => intval($componente_data[0]['dist_id']),
              'proy_id'                          => floatval($componente_data[0]['proy_id']),
              'com_id'                           => $com_id,
              'g_id'                             => intval($this->gestion),
              'trm_id'                           => intval($i),
              'poa_prog'                         => $poa_prog,
              'poa_cumplidos'                    => $poa_cumplidos,
              'poa_proceso'                      => $poa_proceso,
              'poa_no_cumplidos'                 => $poa_no_cumplidos,
              'porcentaje_cumplimiento'          => $pct_cumplimiento,
              'porcentaje_proceso'               => $pct_proceso,
              'porcentaje_no_cumplimiento'       => $pct_no_cumplimiento,
              'porcentaje_no_cumplimiento_total' => $pct_no_cumplimiento_tot
          );

          $this->db->insert('detalle_evaluacion_poa_trimestral', $data_insert);
      }

    }


















    /// Modal Para ver la ejecucion de la Actividad llevar a Libreria MD1
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
                      <a href="javascript:abreVentana(\''.site_url("").'/eval/reporte_seg_eval_poa/'.$com_id.'/'.$rowm['m_id'].'\');">REPORTE SEGUIMIENTO POA - '.$rowm['m_descripcion'].'</a>
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

                    <a href="#" class="btn btn-success" onclick="cargarCuadrosEvaluacion(this, '.$componente[0]['com_id'].',0)" title="GENERAR CUADROS DE EVALUACION POA">
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>GENERAR CUADROS DE EVALUACIÓN POA</b>
                    </a>
                    <a href="' . site_url("seg/seguimiento_poa") . '" 
                       title="VOLVER AL MENÚ ANTERIOR" 
                       class="btn btn-default" 
                       style="font-weight: bold; color: #475569; border-color: #cbd5e1; background: #ffffff; padding: 6px 12px; display: inline-block;">
                        <i class="fa fa-arrow-left"></i> VOLVER
                    </a>
              </div>
            </article>';
    
        return $tabla;
    }


    /// Modal Para ver los graficos de Cumplimiento Al POa MD2
    public function modal_evaluacion_poa_x_UnidadResponsable() {
      $tabla='';
      $tabla .= '
      <div class="modal fade" id="modal_graficos" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <!-- 🌟 CORREGIDO: Altura elástica (height: auto) y eliminación de los topes exagerados de 2000px -->
        <div class="modal-dialog" style="width: 95%; max-width: 1500px; margin: 20px auto; height: auto;">
            <div class="modal-content" style="border-radius: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); height: auto;">
                <div class="modal-header" style="background: #1e3a8a; color: #fff; padding: 15px 20px;">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:#fff; opacity:1;">&times;</button>
                    <h4 class="modal-title" style="font-weight: bold;">
                        <i class="fa fa-bar-chart-o"></i> Cuadros y Gráficos de Evaluación POA
                    </h4>
                </div>
                
                <!-- 🌟 OPTIMIZACIÓN DE VISUALIZACIÓN: max-height ampliado a 75vh (adaptable a laptops y monitores) con scroll interno -->
                <div class="modal-body print-area-graficos" style="padding: 25px; max-height: 75vh; min-height: 400px; overflow-y: auto; clear: both;">

                    <!-- Contenedor del semáforo de calificación ocupando todo el ancho superior -->
                    <div class="row" style="margin-bottom: 20px; padding: 0 10px;">
                        <div id="calificacion" style="width: 100%;"></div>
                    </div>

                    <div class="row" style="display: flex; flex-wrap: wrap; align-items: center;">
                        <!-- Gráfico de Torta -->
                        <div class="col-md-6 col-sm-12 text-center" style="margin-bottom: 25px;">
                            <h5 style="font-weight: bold; color: #334155; margin-bottom: 15px; font-size: 14px;">
                                 <b>(%)_CUMPLIMIENTO_POA</b>
                            </h5>
                            <div style="position: relative; height:350px; width:100%; padding: 0 10px;">
                                <canvas id="grafico_pastel_cumplimiento"></canvas>
                            </div>
                        </div>
                        
                        <!-- Gráfico de Línea de Regresión -->
                        <div class="col-md-6 col-sm-12 text-center" style="margin-bottom: 25px;">
                            <h5 style="font-weight: bold; color: #334155; margin-bottom: 15px; font-size: 14px;">
                                <b>CUMPLIMIENTO_TRIMESTRAL_(TENDENCIA)</b>
                            </h5>
                            <div style="position: relative; height:350px; width:100%; padding: 0 10px;">
                                <canvas id="grafico_barras_temporalidad"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Matriz Consolidada Inferior -->
                    <div class="row" style="margin-top: 25px;">
                        <div class="col-md-12" id="detalles" style="padding: 0 10px; margin-bottom: 10px;">
                            <!-- 🌟 AQUÍ JQUERY INYECTARÁ LA TABLA DINÁMICA DE FORMA AUTOMÁTICA -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8fafc; padding: 15px 20px;">
                    
                    <button type="button"><div id="btn_reporte"></div></button>
                    <button type="button" class="btn btn-primary" onclick="exportarPDF();" style="background: #0284c7; border: none; font-weight: bold; color:#fff; padding: 6px 16px;">
                        <i class="fa fa-file-pdf-o"></i> Exportar a Cuadros a PDF
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold; padding: 6px 16px;">Cerrar Ventana</button>
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