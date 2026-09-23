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
        $data['stylo'] = $this->estilo_tabla_form4(); 
        $data['titulo']=$this->titulo($componente);

        $form4_crudo=$this->model_producto->lista_productos($com_id);
        //$form4 = $this->model_producto->lista_form4_x_unidadresponsable($com_id);
          if($componente[0]['por_id']==1){
            $this->update_uresponsable($form4_crudo);
          }

        $form4 = $this->model_evaluacionpoa->list_formN4_para_evaluacion_UnidadResponsable($com_id,$this->tmes);
        $data['tabla'] = $this->formulario($form4);
        $this->load->view('admin/evaluacion/evaluacion_form4/form_evaluacion_form4', $data);
    }


    /// formulario de SEguimiento / Evaluacion
    function formulario($form4){
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
                <th class="hasinput" style="width:1%; text-align: center;"></th>
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
                <th style="width:1%; text-align: center;">COD.<br>OPE.</th>
                <th style="width:1.5%; text-align: center;">COD.<br> ACT.</th>
                <th style="width:7%; text-align: center;">ACTIVIDAD</th>
                <th style="width:4%; text-align: center;">UNIDAD RESPONSABLE</th>
                <th style="width:4%; text-align: center;">MEDIO DE VERIFICACIÓN</th>
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
          $tp_indi='';
          if($rowp['indi_id']==2){
            $tp_indi='%';
          }
          $prod_id = intval($rowp['prod_id']);
          
          $tabla .= '
          <tr id="fila_prod_'.$prod_id.'" style="vertical-align: middle;">
              <td style="text-align: center; font-weight: bold; vertical-align: middle; font-size:15px;"><b>'.round($rowp['og_codigo'],2).'</b></td>
              <td style="width: 5%; text-align: center; font-size:15px; vertical-align: middle;" bgcolor="#eceaea" title="'.$prod_id.'">
                  <b>'.round($rowp['prod_cod'],2).'</b>
              </td>
              <td style="width: 15%; text-align: left; font-size:9.5px;vertical-align: middle;">'.$rowp['prod_producto'].'</td>
              <td style="width: 10%; text-align: left; font-size:9.5px;vertical-align: middle;">'.$rowp['prod_unidades'].'</td>
              <td style="width: 10%; text-align: left; font-size:9.5px;vertical-align: middle;">'.$rowp['prod_fuente_verificacion'].'</td>
              <td style="width: 5%; text-align: right; font-weight: bold; color: #1e3a8a; padding-right:8px;vertical-align: middle; font-size:15px;">'.round($rowp['prod_meta'], 2).' '.$tp_indi.'</td>';
              
              for ($m = $mes_inicio; $m <= $mes_fin; $m++) {
                  $v_prog   = floatval($rowp['mes'.$m]);
                  $mes_ejec=0;$mverificacion='';$prob_presentados='';$acciones=''; 
                  
                  // Determinar si hay ID de seguimiento existente para pasar al botón eliminar
                  $id_seguimiento = 0; 
                  
                  $ejec=$this->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id,$m); 
                  if(count($ejec)!=0){ 
                    $id_seguimiento = isset($ejec[0]['pe_id']) ? intval($ejec[0]['pe_id']) : 0;
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
                                      <textarea style="font-size: 11px; height: 80px; padding: 2px; resize: vertical;" id="mverif_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$mverificacion.'</textarea>
                                    </label>
                                  </td>
                                  <td style="padding: 2px; width:32%; vertical-align: middle;">
                                    <label class="textarea">
                                      <textarea style="font-size: 11px; height: 80px; padding: 2px; resize: vertical;" id="prob_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$prob_presentados.'</textarea>
                                    </label>
                                  </td>
                                  <td style="padding: 2px; width:32%; vertical-align: middle;">
                                    <label class="textarea">
                                      <textarea style="font-size: 11px; height: 80px; padding: 2px; resize: vertical;" id="acc_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>'.$acciones.'</textarea>
                                    </label>
                                  </td>
                                  <td style="text-align: center; vertical-align: middle; padding: 4px; width: 5%;">
                                    <!-- 💾 Botón Guardar -->
                                    <button type="button" class="btn btn-success btn-xs" title="Guardar Mes '.$m.'" onclick="guardarSeguimiento('.$prod_id.', '.$m.')" style="margin-bottom: 5px; width: 100%; padding: 3px 2px;" '.$es_deshabilitado.'>
                                        <i class="fa fa-save"></i> Guardar
                                    </button>
                                    
                                    <!-- 🗑️ Botón Eliminar (Visible solo si ya existe un registro guardado en BD) -->
                                    <button type="button" class="btn btn-danger btn-xs" title="Eliminar Mes '.$m.'" onclick="eliminarSeguimiento('.$prod_id.', '.$m.', '.$id_seguimiento.')" style="width: 100%; padding: 3px 2px; '.($id_seguimiento == 0 ? 'display:none;' : '').'" id="btn_del_'.$prod_id.'_'.$m.'" '.$es_deshabilitado.'>
                                        <i class="fa fa-trash-o"></i> Quitar
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

        return $tabla;
    }


    public function guardar_seguimiento() {
      // 1. Capturar de forma segura las variables enviadas por el POST de jQuery
      $prod_id = intval($this->input->post('prod_id'));
      $mes     = intval($this->input->post('mes'));
      $ejecutado = floatval($this->input->post('ejecutado'));
      
      // Sanitizar cadenas de texto quitando espacios innecesarios
      $verificacion = trim($this->input->post('verificacion'));
      $problemas    = trim($this->input->post('problemas'));
      $acciones     = trim($this->input->post('acciones'));

      // 2. Validación de seguridad del lado del Servidor (Duplica la seguridad del JS)
      if ($ejecutado == 0 && (empty($problemas) || empty($acciones))) {
          $respuesta = array(
              'status'  => 'error',
              'message' => 'Los campos problemas y acciones son obligatorios cuando la ejecución es cero.'
          );
          echo json_encode($respuesta);
          return;
      }

      // // 3. Evaluar si ya existe un registro previo para este producto, mes y gestión
      // // Nota: Reutiliza el método que ya tienes en tu modelo
      // $existe_ejec = $this->model_evaluacionpoa->get_seguimiento_poa_mes($prod_id, $mes);
      // $existe_no_ejec = $this->model_evaluacionpoa->get_seguimiento_poa_mes_noejec($prod_id, $mes);

      // $id_seguimiento = 0;

      // // Supongamos que decides guardar todo en la tabla correspondiente:
      // if ($ejecutado > 0) {
      //     // Lógica para 'prod_ejecutado_mensual'
      //     // Si existe haces UPDATE, si no haces INSERT.
      //     // $id_seguimiento = $this->model_evaluacionpoa->guardar_ejecutado($prod_id, $mes, $ejecutado, $verificacion, $problemas, $acciones);
          
      //     // *Simulación de ID insertado/actualizado para el ejemplo*
      //     $id_seguimiento = (count($existe_ejec) > 0) ? intval($existe_ejec[0]['pe_id']) : 999; 
      // } else {
      //     // Lógica para 'prod_no_ejecutado_mensual' (Ejecución cero con justificación)
      //     // Si existe haces UPDATE, si no haces INSERT.
      //     // $id_seguimiento = $this->model_evaluacionpoa->guardar_no_ejecutado($prod_id, $mes, $verificacion, $problemas, $acciones);
          
      //     // *Simulación de ID insertado/actualizado para el ejemplo*
      //     $id_seguimiento = (count($existe_no_ejec) > 0) ? intval($existe_no_ejec[0]['ne_id']) : 888;
      // }

      // 4. Retornar la respuesta JSON esperada por tu script JS
      $respuesta = array(
          'status'         => 'success',
          'id_seguimiento' => 1
      );

      echo json_encode($respuesta);
      return;
  }



    //// Verifica tipo de formulario Seguimiento o Evaluacion (a implementar)
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