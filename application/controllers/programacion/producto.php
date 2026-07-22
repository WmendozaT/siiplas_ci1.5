<?php
class Producto extends CI_Controller { 
  public function __construct (){ 
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_componente');
   
        $this->load->model('programacion/insumos/minsumos');
        $this->load->model('modificacion/model_modificacion');
        $this->load->model('mestrategico/model_mestrategico');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('ejecucion/model_ejecucion');
        $this->load->model('mantenimiento/model_estructura_org');
        $this->load->model('mestrategico/model_objetivoregion');
        $this->load->model('programacion/insumos/model_insumo');
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->rol = $this->session->userData('rol_id');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userdata("fun_id");
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->verif_mes=$this->session->userdata('mes_actual');
        $this->conf_form4 = $this->session->userData('conf_form4');
        $this->conf_form5 = $this->session->userData('conf_form5');
        $this->conf_poa_estado = $this->session->userData('conf_poa_estado'); /// Ajuste POA 1: Inicial, 2 : Ajuste, 3 : aprobado
        $this->load->library('programacionpoa');
      }else{
        $this->session->sess_destroy();
          redirect('/','refresh');
      }
    }

    //// Actualizar los codigos de Actividades
    public function update_codigo($form4) {
        $nro = 0;
        
        // Iniciamos un bloque de transacción rápida para acelerar los updates masivos en lote
        $this->db->trans_start();
        
        foreach($form4 as $rowp) {
            $nro++;
            $update_data = array(
                'prod_cod' => $nro,
                'fecha'    => date('Y-m-d H:i:s') // Marcador de auditoría
            );
            
            $this->db->where('prod_id', $rowp['prod_id']);
            $this->db->update('_productos', $update_data);
        }
        
        $this->db->trans_complete();
    }


  /*------- LISTA DE FORM 4 (a optimizar)----------*/
    public function lista_productos($com_id){
      $data['componente'] = $this->model_componente->get_componente($com_id,$this->gestion);
      $data['stylo']=$this->programacionpoa->estilo_tabla_form4();

      if (!empty($data['componente']) && count($data['componente']) != 0) {
          // A. Recuperamos la matriz cruda de actividades registradas actualmente
            $form4_crudo = $this->model_producto->lista_form4_x_unidadresponsable($com_id);
            
            // 🌟 MOTOR AUTOMÁTICO: Re-enumeramos el correlativo prod_cod (1, 2, 3...) en caliente
            if (!empty($form4_crudo) && count($form4_crudo) > 0) {
                $this->update_codigo($form4_crudo);
            }

            // B. 🛠️ REPARADO: Volvemos a consultar la lista ya re-ordenada para que la vista reciba el correlativo nuevo
            $form4 = $this->model_producto->lista_form4_x_unidadresponsable($com_id);
            
            // C. Extraemos el proy_id relacional utilizando el índice cero del framework
            $proy_id = intval($data['componente'][0]['proy_id']);
            $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
         
          if($data['proyecto'][0]['tp_id']==1){
            $list_oregional=$this->model_objetivoregion->get_unidad_pregional_programado($proy_id);
            $data['datos_proyecto']='<h2>'.$data['proyecto'][0]['proy_sisin'].' - '.$data['proyecto'][0]['proy_nombre'].'</h2>';
          }
          else{
            $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
            $data['datos_proyecto']='<h2>'.$data['proyecto'][0]['aper_programa'].' '.$data['proyecto'][0]['aper_proyecto'].' '.$data['proyecto'][0]['aper_actividad'].' - '.$data['proyecto'][0]['tipo'].' '.$data['proyecto'][0]['act_descripcion'].' - '.$data['proyecto'][0]['abrev'].'  / <b>'.$data['componente'][0]['serv_cod'].' </b>'.$data['componente'][0]['tipo_subactividad'].' '.$data['componente'][0]['serv_descripcion'].'</h2>';
          }

          $data['indi'] = $this->model_proyecto->indicador(); /// indicador
          $data['unidades']=$this->model_producto->list_uresponsables_regional_alineacion_prog_bolsas($data['proyecto'][0]['dist_id']);
          $data['metas'] = $this->model_producto->tp_metas(); /// tp metas
          $data['uni_resp']='';
          $data['alineacion']='';

          $data['alineacion']='
                  <section class="col col-4">
                    <label class="label"><b>ALINEACI&Oacute;N OPERACI&Oacute;N '.$this->gestion.'</b></label>
                      <select class="form-control" id="or_id" name="or_id" style="width:100%; font-size:13px; color:blue; background-color: #fafcd7;" title="SELECCIONE ALINEACION">
                        <option value="">SELECCIONE OPERACI&Oacute;N </option>';
                        foreach($list_oregional as $row){
                          $data['alineacion'].='<option value="'.$row['or_id'].'">'.$row['og_codigo'].'.|'.$row['or_codigo'].'. .- '.$row['or_objetivo'].'</option>';    
                        }
                      $data['alineacion'].='
                    </select>
                  </section>';

          if($data['proyecto'][0]['por_id']==1){
            $data['uni_resp'].='
            <section class="col col-3">
              <label class="label"><b>UNIDAD RESPONSABLE</b></label>
                <select class="form-control" id="uni_resp" name="uni_resp" title="SELECCIONE UNIDAD RESPONSABLE" style="width:100%; font-size:10.5px; color:blue; background-color: #d7fcfa;">
                  <option value="">Selec. Uni. Resp.</option>';
                  foreach($data['unidades'] as $row){
                    $data['uni_resp'].='<option value="'.$row['com_id'].'">'.$row['tipo'].' '.$row['proy_nombre'].'-'.$row['abrev'].' -> '.$row['tipo_subactividad'].' '.$row['com_componente'].'</option>';
                  }       
                  $data['uni_resp'].='
                </select>
              </section>';
          }
          else{
            $data['uni_resp']='
            <section class="col col-3">
              <label class="label"><b>UNIDAD RESPONSABLE</b></label>
              <label class="textarea">
                <i class="icon-append fa fa-tag"></i>
                <textarea rows="3" name="uni_resp" id="uni_resp" title="REGISTRE UNIDAD RESPONSABLE" style="width:100%; font-size:11px; color:blue; background-color: #e3fcf8;"></textarea>
              </label>
            </section>';
          }

          $data['titulo']='';
          //if($this->conf_poa_estado==1){ /// Ante proyecto
            $data['titulo'].='
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <input type="hidden" name="base" value="'.base_url().'">
              <div class="well">
                '.$data['datos_proyecto'].'
                  <a href="#" data-toggle="modal" data-target="#modal_nuevo_form" class="btn btn-default nuevo_form" title="NUEVO REGISTRO FORM N 4" >
                    <img src="'.base_url().'assets/Iconos/add.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>NUEVO REGISTRO (ACTIVIDAD)</b>
                  </a>

                  <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
                    <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO ACTIVIDADES.Xls </b>
                  </a>';

                  if(count($form4)!=0){
                    $data['titulo'].='
                    <a href="#" data-toggle="modal" data-target="#modal_importar_f5" class="btn btn-default importar_f5" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO REQUERIMIENTOS.Xls</b>
                    </a>
                    <a href="#" data-toggle="modal" data-target="#modal_ver_form5" class="btn btn-default ver_requerimientos" name="'.$com_id.'" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>VER MIS REQUERIMIENTOS</b>
                    </a>
                    <a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$com_id.'\');" class="btn btn-primary" title="REPORTE FORM. 4"> <img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>REPORTE FORM N 4</a>
                    <a onclick="eliminar_form4_todos()" class="btn btn-danger"  title="Eliminar Actividades de la unidad (todos)"><img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 4 (TODOS)</a>
                    <a onclick="eliminar_requerimientos_UnidadReponsable()" class="btn btn-danger"  title="Eliminar Solo Requerimientos de la unidad (todos)"><img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 5 (TODOS)</a>';
                  }
                  $data['titulo'].='
              </div>
            </article>';

            $tabla = '';
            $color_or = '';
            $cont = 0;

            foreach($form4 as $rowp){
                $disabled = '';
                  $style = 'none';
                  $bg_meta = '#f1f5f9'; // Gris inactivo por defecto para Absolutos y Relativos tipo 3

                  // 📋 REGLA SERVIDOR: Si el indicador es Relativo (2), evaluamos los subtipos de metas
                  if($rowp['indi_id'] == 2){
                      $style = 'block'; // Despliega el contenedor del combo de metas (#tp_met)
                      // 🛠️ AJUSTADO: El campo meta global se habilita UNICAMENTE si el tipo de meta es Mensualizado Manual (3)
                      if($rowp['mt_id'] == 3) {
                          $disabled = 'disabled'; 
                          $bg_meta = '#ffffff'; // Blanco activo para digitación libre
                      }
                  }
                $tabla .= '
                <tr id="fila_prod_'.$rowp['prod_id'].'">
                    <!-- Botón Requerimientos -->
                    <td style="width: 4%; text-align: center;" title="'.$rowp['prod_id'].'">
                        <center><a href="'.site_url("prog/requerimiento/".$rowp['prod_id']).'" target="_blank" title="REQUERIMIENTOS DE LA ACTIVIDAD" class="btn btn-default"><img src="'.base_url().'assets/ifinal/insumo.png" WIDTH="30" HEIGHT="30"/></a></center>
                    </td>
                    <!-- Botón Eliminar -->
                    <td style="width: 4%; text-align: center;">';
                        if(count($this->model_producto->insumo_producto($rowp['prod_id'])) == 0){
                            if($this->tp_adm == 1 || $this->conf_form4 == 1){
                              $tabla .= '<center><a name="del_prod'.$rowp['prod_id'].'" id="del_prod'.$rowp['prod_id'].'" onclick="delete_form4('.$rowp['prod_id'].');" class="btn btn-default" title="ELIMINAR ACTIVIDAD"><img src="' . base_url() . 'assets/ifinal/eliminar.png" WIDTH="30" HEIGHT="30"/></a></center>';
                            }
                        }
                    $tabla .= '
                    </td>
                    <!-- Código Actividad -->
                    <td style="width: 5%; text-align: center;" bgcolor="#eceaea" title="'.$rowp['prod_id'].'">
                        <input name="prod_cod'.$rowp['prod_id'].'" id="prod_cod'.$rowp['prod_id'].'" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_cod" type="text" style="width:100%; font-size:12px; color:blue; background-color: #d7fcfa; text-align:center;" value="'.round($rowp['prod_cod'],2).'" onkeypress="if (this.value.length < 10) { return numerosDecimales(event);}else{return false; }" onpaste="return false">
                    </td>
                    <!-- Alineación ACP / Operación -->
                    <td style="width: 10%; text-align: center;" title="'.$rowp['prod_id'].'">
                        <section class="col col-2" style="margin-bottom: 0; padding: 0;">
                            <select class="form-control auto-save-field" id="or_id'.$rowp['prod_id'].'" name="or_id'.$rowp['prod_id'].'" data-id="'.$rowp['prod_id'].'" data-campo="or_id" style="width:100%; font-size:12px; color:blue; background-color: #fafcd7;" title="SELECCIONE ALINEACION">
                                <option value="">SELECCIONE OPERACI&Oacute;N</option>';
                                foreach($list_oregional as $row){
                                    $selected = ($rowp['or_id'] == $row['or_id']) ? 'selected' : '';
                                    $tabla .= '<option value="'.$row['or_id'].'" '.$selected.'>'.$row['og_codigo'].'.|'.$row['or_codigo'].'. .- '.$row['or_objetivo'].'</option>';    
                                }
                            $tabla .= '
                            </select>
                        </section>
                    </td>
                    <!-- Detalle Actividad -->
                    <td style="width: 12%; text-align: left;">
                        <textarea rows="5" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_producto" name="prod_form4'.$rowp['prod_id'].'" id="prod_form4'.$rowp['prod_id'].'" style="width:100%; font-size:10px; color:blue;background-color: #d7fcfa;" title="DETALLE ACTIVIDAD">'.$rowp['prod_producto'].'</textarea>
                    </td>
                    <!-- Detalle Resultado -->
                    <td style="width: 12%; text-align: left;">
                        <textarea rows="5" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_resultado" style="width:100%; font-size:10px; color:blue;background-color: #d7fcfa;" name="prod_res'.$rowp['prod_id'].'" id="prod_res'.$rowp['prod_id'].'" title="DETALLE RESULTADO">'.$rowp['prod_resultado'].'</textarea>
                    </td>';
                    
                    // Contexto Relacional: Unidades Responsables (Bolsas vs Normales)
                    if($data['proyecto'][0]['por_id'] == 1){ 
                        $tabla .= '
                        <td style="width: 7%; text-align: left;">
                            <select class="form-control auto-save-field" id="u_resp'.$rowp['prod_id'].'" name="u_resp'.$rowp['prod_id'].'" data-id="'.$rowp['prod_id'].'" data-campo="uni_resp" title="SELECCIONE UNIDAD RESPONSABLE" style="width:100%; font-size:10px; color:blue; background-color: #d7fcfa;">
                                <option value="">Selec. Uni. Resp.</option>';
                                foreach($data['unidades'] as $row){
                                    $selected = ($rowp['uni_resp'] == $row['com_id']) ? 'selected' : '';
                                    $tabla .= '<option value="'.$row['com_id'].'" '.$selected.'>'.$row['tipo'].' '.$row['proy_nombre'].'-'.$row['abrev'].' -> '.$row['tipo_subactividad'].' '.$row['com_componente'].'</option>';
                                }       
                            $tabla .= '
                            </select>
                        </td>';
                    } else { 
                        $tabla .= '
                        <td style="width: 7%; text-align: left;">
                            <textarea rows="5" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_unidades" style="width:100%; font-size:10px; color:blue; background-color: #d7fcfa;" name="prod_uni'.$rowp['prod_id'].'" id="prod_uni'.$rowp['prod_id'].'" title="UNIDAD RESPONSABLE">'.$rowp['prod_unidades'].'</textarea>
                        </td>';
                    }
                    
                    // Configuración Indicador y Metas Contextuales
                    $tabla .= '
                    <td style="width: 5%; text-align: left;">
                        <select class="form-control auto-save-field-indicador" id="indi_id'.$rowp['prod_id'].'" name="indi_id'.$rowp['prod_id'].'" data-id="'.$rowp['prod_id'].'" data-campo="indi_id" style="width:100%; font-size:10px; color:blue; background-color: #d7fcfa;" title="SELECCIONE INDICADOR">
                            <option value="">SELECCIONE INDICADOR</option>';
                            foreach($data['indi'] as $row){
                                $selected = ($rowp['indi_id'] == $row['indi_id']) ? 'selected' : '';
                                $tabla .= '<option value="'.$row['indi_id'].'" '.$selected.'>'.$row['indi_descripcion'].'</option>';    
                            }
                        $tabla .= '
                        </select>

                        <div id="tp_met'.$rowp['prod_id'].'" style="display:'.$style.'; margin-top: 5px;" >
                            <select class="form-control auto-save-field" id="tp_met'.$rowp['prod_id'].'" name="tp_met'.$rowp['prod_id'].'" data-id="'.$rowp['prod_id'].'" data-campo="mt_id" style="width:100%; font-size:11px; color:blue; background-color: #e3fcf8;" title="SELECCIONE TIPO DE META">
                                <option value="">Seleccione Tipo de Meta</option>';
                                foreach($data['metas'] as $row){ 
                                    $selected = ($row['mt_id'] == $rowp['mt_id']) ? 'selected' : '';
                                    $tabla .= '<option value="'.$row['mt_id'].'" '.$selected.'>'.$row['mt_tipo'].'</option>';
                                }
                            $tabla .= '
                            </select>
                        </div>
                    </td>
                    
                    <!-- Detalle Indicador -->
                    <td style="width: 8%; text-align: left;">
                      <textarea rows="5" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_indicador" name="prod_indi'.$rowp['prod_id'].'" id="prod_indi'.$rowp['prod_id'].'" style="width:100%; font-size:10px;" title="DETALLE INDICADOR">'.$rowp['prod_indicador'].'</textarea>
                    </td>
                    <!-- Medio de Verificación -->
                    <td style="width: 8%; text-align: left;">
                        <textarea rows="5" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_fuente_verificacion" name="prod_mverif'.$rowp['prod_id'].'" id="prod_mverif'.$rowp['prod_id'].'" style="width:100%; font-size:10px;" title="DETALLE MEDIO DE VERIFICACION">'.$rowp['prod_fuente_verificacion'].'</textarea>
                    </td>
                    <!-- Meta Global de la Actividad -->
                    <td style="width: 5%; text-align: center;">
                        <input name="meta'.$rowp['prod_id'].'" id="meta'.$rowp['prod_id'].'" class="form-control auto-save-field" data-id="'.$rowp['prod_id'].'" data-campo="prod_meta" type="text" '.$disabled.' style="width:100%; font-size:11.5px; text-align:center" value="'.round($rowp['prod_meta'],2).'" onkeypress="if (this.value.length < 10) { return numerosDecimales(event);}else{return false; }" onpaste="return false">
                    </td>';
                    
                    // 🌟 BUCLE DINÁMICO DE TEMPORALIDAD OPTIMIZADO (Meses 1 al 12)
                    for ($m = 1; $m <= 12; $m++) {
                        $valor_mes = isset($rowp['m'.$m]) ? round($rowp['m'.$m], 2) : 0;
                        
                        // 📋 REGLA SERVIDOR: Evaluamos de forma elástica el bloqueo inicial del cronograma
                        $mes_disabled = '';
                        $bg_celda = '#e5fde5'; // Verde agua libre por defecto

                        // Si es Relativo (2) y el modo es Recurrente (1) o Trimestral (5), los meses nacen bloqueados en gris
                        if ($rowp['indi_id'] == 2 && ($rowp['mt_id'] == 1 || $rowp['mt_id'] == 5)) {
                            $mes_disabled = 'disabled';
                            $bg_celda = '#f1f5f9'; // Gris protegido institucional
                        }

                        $tabla .= '
                        <td style="width:5%; background-color: '.$bg_celda.';" align="center">
                            <input name="m'.$m.$rowp['prod_id'].'" 
                                   id="m'.$m.$rowp['prod_id'].'" 
                                   class="form-control auto-save-month" 
                                   data-prod="'.$rowp['prod_id'].'" 
                                   data-mes="'.$m.'" 
                                   type="text" 
                                   '.$mes_disabled.'
                                   style="width:100%; font-size:10px; color:blue; text-align:center; font-weight:bold; background-color: '.$bg_celda.';" 
                                   value="'.$valor_mes.'" 
                                   onkeypress="if (this.value.length < 6) { return numerosDecimales(event); } else { return false; }" 
                                   onpaste="return false">
                        </td>';
                    }
                    
                $tabla .= '</tr>';
            }


            $tabla .= '
            <div class="modal fade" id="modal_importar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
                <div class="modal-dialog" id="dialog_subirr">
                    <div class="modal-content" style="border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none; overflow: hidden;">
                        
                        <!-- CABECERA DEL COMPONENTE -->
                        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                            <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                <i class="fa fa-upload text-primary"></i> Importar Actividades
                            </h4>
                        </div>

                        <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                        <div class="modal-body" style="padding: 25px; background: #ffffff;">
                            
                            <!-- Título e Instrucción -->
                            <div class="text-center" style="margin-bottom: 20px;">
                                <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Excel (.xls, .xlsx)</h5>
                                <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                            </div>

                            <!-- Vista previa de columnas (Corregido: Concatenación nativa base_url) -->
                            <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                                <div style="color:blue;">CÓDIGO DE UNIDAD: <b style="font-size:14px;">'.$data['componente'][0]['serv_cod'].' </b></div><br>
                                <img src="' . base_url('assets/img/img_migracion/migracion_form4_unidad.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                            </div>

                            <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                            <form action="' . site_url('programacion/producto/valida_migracion_form4') . '" method="post" enctype="multipart/form-data" id="form_subir_actividades" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="com_id" value="'.$data['componente'][0]['com_id'].'" type="hidden" > 
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo" accept=".xlsx, .xls" name="archivo" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- Animación Pre-Loader de la Planilla -->
                                <div id="loads" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="modal_importar_f5" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
                <div class="modal-dialog" id="dialog_subirr">
                    <div class="modal-content" style="border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none; overflow: hidden;">
                        
                        <!-- CABECERA DEL COMPONENTE -->
                        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                            <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                <i class="fa fa-upload text-primary"></i> Importar Requerimientos GLOBAL
                            </h4>
                        </div>

                        <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                        <div class="modal-body" style="padding: 25px; background: #ffffff;">
                            
                            <!-- Título e Instrucción -->
                            <div class="text-center" style="margin-bottom: 20px;">
                                <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Requerimientos Global (.xls, .xlsx)</h5>
                                <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                            </div>

                            <!-- Vista previa de columnas (Corregido: Concatenación nativa base_url) -->
                            <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                                <div style="color:blue;">CÓDIGO DE UNIDAD: <b style="font-size:14px;">'.$data['componente'][0]['serv_cod'].' </b></div><br>
                                <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                            </div>

                            <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                            <form action="' . site_url('programacion/producto/valida_migracion_form5_consolidado') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="com_id" value="'.$data['componente'][0]['com_id'].'" type="hidden" > 
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo_f5" accept=".xlsx, .xls" name="archivo_f5" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje_f5" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir_f5" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- Animación Pre-Loader de la Planilla -->
                                <div id="loads_f5" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>';

            $data['tabla']=$tabla;
            $this->load->view('admin/programacion/producto/form_anteproyecto_form4', $data); /// Gasto Corriente
      }
      else{
        redirect('prog/list_serv/'.$com_id);
      }

    }


    //// guardar informacion de la Actividad
     public function guardar_campo_form4_en_caliente() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // 1. Captura y sanitización perimetral de variables del SIIPLAS
            $prod_id    = intval($this->input->post('prod_id'));
            $campo_raw  = trim($this->input->post('campo'));
            $valor_raw  = trim($this->input->post('valor'));
            $g_id       = intval($this->gestion); // Gestión activa (ej: 2026 / 2027)

            if ($prod_id <= 0 || empty($campo_raw)) {
                echo json_encode(array('status' => 'error', 'message' => 'Parámetros inconsistentes o ID de producto inválido.'));
                return;
            }

            // 2. DEDUCCIÓN DE CONTEXTO: ¿Es un campo maestro de texto o una celda mensual?
            $tp = 0; 
            $mes_id = 0;
            if (preg_match('/^m([1-9]|1[0-2])$/', $campo_raw, $coincidencias)) {
                $tp = 1;          
                $mes_id = intval($coincidencias[1]); 
            }

            $informacion = 0;
            $sum_meta = 0;

            // Recuperamos el estado del producto antes de operar
            $producto = $this->model_producto->get_producto_id($prod_id);
            if (empty($producto)) {
                echo json_encode(array('status' => 'error', 'message' => 'El producto/actividad no existe en PostgreSQL.'));
                return;
            }

            // ==========================================================================
            // RAMA A: ACTUALIZACIÓN DE CAMPOS MAESTROS DE LA ACTIVIDAD (_productos)
            // ==========================================================================
            if ($tp == 0) {
                
                // Mapeamos el nombre virtual que viene del JS al nombre físico real de tu tabla DDL
                $campo = $campo_raw;
                if ($campo_raw == 'prod_form4')  $campo = 'prod_producto';
                if ($campo_raw == 'prod_res')    $campo = 'prod_resultado';
                if ($campo_raw == 'prod_uni')    $campo = 'prod_unidades';
                if ($campo_raw == 'prod_indi')   $campo = 'prod_indicador';
                if ($campo_raw == 'prod_mverif') $campo = 'prod_fuente_verificacion';
                if ($campo_raw == 'meta')        $campo = 'prod_meta';

                $columnas_autorizadas = array('prod_cod', 'or_id', 'prod_producto', 'prod_resultado', 'uni_resp', 'prod_unidades', 'indi_id', 'prod_indicador', 'prod_fuente_verificacion', 'prod_meta', 'mt_id');
                if (!in_array($campo, $columnas_autorizadas)) {
                    echo json_encode(array('status' => 'error', 'message' => 'Columna no autorizada para persistencia.'));
                    return;
                }

                if ($campo == 'prod_cod' || $campo == 'or_id' || $campo == 'indi_id' || $campo == 'mt_id') {
                    $detalle = ($valor_raw === '') ? 0 : intval($valor_raw);
                } elseif ($campo == 'prod_meta' || $campo == 'uni_resp') {
                    $detalle = ($valor_raw === '') ? 0.00 : floatval($valor_raw);
                } else {
                    $detalle = strtoupper($this->security->xss_clean($valor_raw));
                }

                // 🌟 INICIO DE TRANSACCIÓN CONTROLADA PARA REPLICACIÓN EN CASCADA
                $this->db->trans_start();

                $update_form4 = array(
                    $campo   => $detalle,
                    'fecha'  => date('Y-m-d H:i:s'),
                    'num_ip' => $this->input->ip_address(),
                    'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                );
                
                $this->db->where('prod_id', $prod_id);
                $this->db->update('_productos', $update_form4);

                // 🌟 LÓGICA DE REPLICACIÓN AUTOMÁTICA EN CASILLAS MENSUALES
                if ($campo == 'prod_meta' && $producto[0]['indi_id'] == 2) {
                    $mt_id_actual = intval($producto[0]['mt_id']);
                    
                    if ($mt_id_actual == 1 || $mt_id_actual == 5) {
                        // Limpiamos el cronograma físico anterior para evitar duplicidad relacional
                        $this->db->where('prod_id', $prod_id);
                        $this->db->where('g_id', $g_id);
                        $this->db->delete('prod_programado_mensual');

                        // Recorremos los 12 meses para poblar según el mt_id
                        for ($m = 1; $m <= 12; $m++) {
                            $monto_mes = 0.00;
                            
                            if ($mt_id_actual == 1) {
                                $monto_mes = $detalle; // Recurrente: replica en los 12 meses
                            } elseif ($mt_id_actual == 5) {
                                // Trimestral: replica exclusivamente en Marzo(3), Junio(6), Septiembre(9) y Diciembre(12)
                                $monto_mes = ($m == 3 || $m == 6 || $m == 9 || $m == 12) ? $detalle : 0.00;
                            }

                            if ($monto_mes != 0) {
                                $this->db->insert('prod_programado_mensual', array(
                                    'prod_id' => $prod_id,
                                    'm_id'    => $m,
                                    'pg_fis'  => $monto_mes,
                                    'pg_fin'  => 0.00,
                                    'g_id'    => $g_id
                                ));
                            }
                        }
                    }
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'message' => 'PostgreSQL rechazó la consistencia de replicación de metas.'));
                    return;
                }

                $producto_actualizado = $this->model_producto->get_producto_id($prod_id);
                $informacion = $producto_actualizado[0][$campo];

                if ($producto_actualizado[0]['indi_id'] == 1) {
                    $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $g_id);
                    if (!empty($suma_temp)) {
                        $sum_meta = round($suma_temp[0]['prog'], 2);
                    }
                } else {
                    $sum_meta = round($producto_actualizado[0]['prod_meta'], 2);
                }
            }

            else { 
                $detalle = floatval($valor_raw);

                $this->db->trans_start();

                $this->db->where('m_id', $mes_id);
                $this->db->where('prod_id', $prod_id);
                $this->db->where('g_id', $g_id);
                $this->db->delete('prod_programado_mensual');

                if ($detalle != 0) {
                    $data_to_store = array( 
                        'prod_id' => $prod_id, 
                        'm_id'    => $mes_id, 
                        'pg_fis'  => $detalle,
                        'pg_fin'  => 0.00, 
                        'g_id'    => $g_id, 
                    );
                    $this->db->insert('prod_programado_mensual', $data_to_store);
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'message' => 'Error de consistencia mensual.'));
                    return;
                }

                $temp_prod = $this->model_producto->get_mes_programado_form4($prod_id, $mes_id);
                if (!empty($temp_prod)) {
                    $informacion = $temp_prod[0]['pg_fis'];
                }

                $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $g_id);
                if (!empty($suma_temp)) {
                    $sum_meta = round($suma_temp[0]['prog'], 2);
                }
                
                // Si el indicador es absoluto (1) o relativo mensualizado manual (3), se auto-actualiza la cabecera
                if ($producto[0]['indi_id'] == 1 || ($producto[0]['indi_id'] == 2 && $producto[0]['mt_id'] == 3)) {
                    $this->db->where('prod_id', $prod_id);
                    $this->db->update('_productos', array('prod_meta' => $sum_meta, 'fecha' => date('Y-m-d H:i:s')));
                } else {
                    $sum_meta = round($producto[0]['prod_meta'], 2);
                }
            }

            // 3. RESPUESTA UNIFICADA
            $result = array(
                'status'             => 'success',
                'respuesta'          => 'correcto',
                'update_informacion' => $informacion,
                'update_meta'        => $sum_meta
            );

            echo json_encode($result);

        } else {
            show_404();
        }
    }


    //// Valida Guardar Temporalidad form 4
    public function guardar_temporalidad_mes_en_caliente() {
        // Validamos que sea una petición asíncrona legítima de JQuery
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // Casteamos de forma estricta según el DDL de tu PostgreSQL
            $prod_id = intval($this->input->post('prod_id'));
            $m_id    = intval($this->input->post('m_id'));
            $pg_fis  = floatval($this->input->post('pg_fis'));
            $g_id    = intval($this->gestion); // Gestión POA activa de sesión (ej: 2027)

            if ($prod_id <= 0 || $m_id < 1 || $m_id > 12) {
                echo json_encode(array('status' => 'error', 'message' => 'Índices mensuales corruptos o fuera de rango (1-12).'));
                return;
            }

            // 1. Recuperamos el estado del producto antes de operar para verificar el tipo de indicador
            $producto = $this->model_producto->get_producto_id($prod_id);
            if (empty($producto)) {
                echo json_encode(array('status' => 'error', 'message' => 'La actividad no existe en la base de datos.'));
                return;
            }

            // 2. Transacción atómica: Limpieza preventiva e inserción relacional del mes (Tu lógica original)
            $this->db->trans_start();
            
            $this->db->where('m_id', $m_id);
            $this->db->where('prod_id', $prod_id);
            $this->db->delete('prod_programado_mensual');

            if ($pg_fis != 0) {
                $data_to_store = array( 
                    'prod_id' => $prod_id, 
                    'm_id'    => $m_id, 
                    'pg_fis'  => $pg_fis,
                    'pg_fin'  => 0.00, // Inicializado por defecto para presupuestos
                    'g_id'    => $g_id
                );
                $this->db->insert('prod_programado_mensual', $data_to_store);
            }

            $this->db->trans_complete();

            // Inicialización de variables de retorno para el éxito de JQuery
            $informacion = 0;
            $sum_meta = 0;

            if ($this->db->trans_status() !== FALSE) {
                // Recuperamos el valor guardado para confirmación visual de la celda
                $temp_prod = $this->model_producto->get_mes_programado_form4($prod_id, $m_id);
                if (!empty($temp_prod)) {
                    $informacion = $temp_prod[0]['pg_fis'];
                }

                // 🌟 LÓGICA DE SUMATORIA CORE: Si el indicador es Absoluto (1), recalculamos la meta anual en caliente
                if ($producto[0]['indi_id'] == 1 || ($producto[0]['indi_id'] == 2 && $producto[0]['mt_id'] == 3)) {
                    
                    $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $this->gestion);
                    if (!empty($suma_temp) && count($suma_temp) > 0) {
                        $sum_meta = round($suma_temp[0]['prog'], 2);
                    }

                    // Sincronizamos la meta calculada de inmediato en public._productos
                    $this->db->where('prod_id', $prod_id);
                    $this->db->update('_productos', array('prod_meta' => $sum_meta));
                } else {
                    // Si es relativo tipo 1 o 5, la meta global se mantiene fija e independiente
                    $sum_meta = round($producto[0]['prod_meta'], 2);
                }

                // Despachamos el JSON unificado con los índices esperados por tu JS para repintar la pantalla
                echo json_encode(array(
                    'status'             => 'success',
                    'respuesta'          => 'correcto',
                    'update_informacion' => $informacion,
                    'update_meta'        => $sum_meta // 🌟 Enviado al JS para inyectarse en #metaXXXX
                ));

            } else {
                echo json_encode(array('status' => 'error', 'message' => 'PostgreSQL rechazó la consistencia transaccional del mes.'));
            }

        } else {
            show_404();
        }
    }




    public function update_datos_form4_uresp(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $prod_id = intval($this->security->xss_clean($post['prod_id']));
        $id      = intval($this->security->xss_clean($post['id']));
        $tp      = intval($this->security->xss_clean($post['tp']));
        
        $informacion = 0;
        $sum_meta    = 0;
        $campo       = '';

        if($tp == 1){
            $campo = 'or_id';
        }
        elseif($tp == 2){
            $campo = 'uni_resp';
        }
        elseif($tp == 3){
            $campo = 'indi_id';
        }

        // 🌟 REPARACIÓN MAESTRA: Se construye un único pool de actualización integrado
        $update_prod = array(
            $campo   => $id,
            'num_ip' => $this->input->ip_address(),
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
        );

        if($tp == 3){ // Si estamos alterando el Tipo de Indicador abolsuto, relativo->acumulado (defecto)
              $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $this->gestion);
                if(!empty($suma_temp) && count($suma_temp) > 0){
                    $sum_meta = round($suma_temp[0]['prog'], 2);
                }
                
                // Unificamos las variables dentro del mismo arreglo para evitar dobles updates destructivos
                $update_prod['prod_meta'] = $sum_meta;
                $update_prod['mt_id']     = 3; // Resetea a mensualizado por norma CNS
        }

        // Impactamos de forma robusta la tabla física relacional en un solo viaje de base de datos
        $this->db->where('prod_id', $prod_id);
        $this->db->update('_productos', $update_prod);

        // Recuperamos el valor guardado para sincronización de pantalla
        $producto = $this->model_producto->get_producto_id($prod_id);
        if(!empty($producto)){
            $informacion = isset($producto[0][$campo]) ? $producto[0][$campo] : $id;
            $sum_meta    = round($producto[0]['prod_meta'], 2);
        }

        // 🌟 ENVIAMOS EL DIARIO DE DATOS COMPLETO AL CLIENTE JS
        $result = array(
          'respuesta'          => 'correcto',
          'update_informacion' => $informacion,
          'update_meta'        => $sum_meta,
          'indi_id'            => $id
        );

        echo json_encode($result);
      } else {
        show_404();
      }
    }

    /*==========================================================================*/
    /*--- MIGRACIÓN CNS: UPDATE TIPO DE META OPERATIVA (RECURRENTE / TRIM) ----*/
    /*==========================================================================*/
    public function update_datos_tpmeta(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        
        // 🌟 REPARACIÓN: Se casstea como FLOATVAL debido al DDL numeric(18,0) de tu Postgres
        $prod_id = floatval($this->security->xss_clean($post['prod_id']));
        $id      = intval($this->security->xss_clean($post['id'])); // mt_id (1, 3, 5)
        
        $producto = $this->model_producto->get_producto_id($prod_id); 
        $sum_meta = 0;

        if(!empty($producto)) {
            // Iniciamos bloque de transacción atómica para proteger PostgreSQL
            $this->db->trans_start();

            // 1. Actualizamos el tipo de meta en la tabla maestra
            $this->db->where('prod_id', $prod_id);
            $this->db->update('_productos', array(
                'mt_id'  => $id,
                'fecha'  => date('Y-m-d H:i:s'),
                'num_ip' => $this->input->ip_address(),
                'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
            ));

            // 2. DISTRIBUCIÓN AUTOMÁTICA DE REGISTROS EN LA BASE DE DATOS
            if ($id == 1 || $id == 5) {
                $sum_meta = 100; // Valor por defecto solicitado

                // Sincronizamos la meta del producto maestro
                $this->db->where('prod_id', $prod_id);
                $this->db->update('_productos', array('prod_meta' => $sum_meta));

                // Limpiamos el cronograma físico anual anterior de este producto
                $this->db->where('prod_id', $prod_id);
                $this->db->where('g_id', $this->gestion);
                $this->db->delete('prod_programado_mensual');

                // 🛠️ REPARADO: Se corrige la variable del iterador ($mes++) para evitar bucles infinitos
                for ($mes = 1; $mes <= 12; $mes++) {
                    $valor_fisico_mes = 0;
                    
                    if ($id == 1) {
                        $valor_fisico_mes = 100.00; // Recurrente clona en todos
                    } elseif ($id == 5) {
                        // Trimestral inyecta en Marzo(3), Junio(6), Septiembre(9) y Diciembre(12)
                        $valor_fisico_mes = ($mes == 3 || $mes == 6 || $mes == 9 || $mes == 12) ? 100.00 : 0.00;
                    }

                    if ($valor_fisico_mes > 0) {
                        $this->db->insert('prod_programado_mensual', array(
                            'prod_id' => $prod_id,
                            'm_id'    => $mes,
                            'pg_fis'  => $valor_fisico_mes,
                            'pg_fin'  => 0,
                            'g_id'    => $this->gestion
                        ));
                    }
                }
            } 
            elseif ($id == 3) {
                // Caso mensualizado manual: Resetea meta a cero para nueva digitación libre
                $this->db->where('prod_id', $prod_id);
                $this->db->update('_productos', array('prod_meta' => 0));
                $sum_meta = 0.;

                $this->db->where('prod_id', $prod_id);
                $this->db->where('g_id', $this->gestion);
                $this->db->delete('prod_programado_mensual');
            }

            $this->db->trans_complete();

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array('respuesta' => 'error', 'message' => 'PostgreSQL rechazó la consistencia en lote.'));
                return;
            }
            
            // ==========================================================================
            // 🌟 NUEVO AJUSTE: POBLADO EN CALIENTE DEL SUB-OBJETO 'producto' REQUERIDO POR TU JS
            // ==========================================================================
            $data_producto = array(
                'mt_id'     => $id,
                'prod_meta' => number_format($sum_meta, 2, '.', '')
            );

            // Estructuramos de forma matemática las claves m1 a m12 según las reglas POA
            for ($m = 1; $m <= 12; $m++) {
                $valor_mes_calculado = 0;
                if ($id == 1) {
                    $valor_mes_calculado = 100;
                } elseif ($id == 5) {
                    $valor_mes_calculado = ($m == 3 || $m == 6 || $m == 9 || $m == 12) ? 100 : 0;
                }
                
                // Formateamos con dos decimales limpios anti-Notice
                $data_producto['m' . $m] = number_format($valor_mes_calculado, 2, '.', '');
            }

            // Consolidamos la respuesta unificada final esperada por tu ajax.success
            $result = array(
              'respuesta'   => 'correcto',
              'update_meta' => number_format($sum_meta, 2, '.', ''),
              'producto'    => $data_producto // Enlazado de forma directa a response.producto
            );

        } else {
            // Mensaje de resguardo forense por si el ID numérico sigue rebotando en el modelo
            $result = array(
              'respuesta' => 'error',
              'message'   => 'Producto/Actividad inexistente. ID capturado: ' . $prod_id
            );
        }

        echo json_encode($result);
      } else {
        show_404();
      }
    }



      /// ==== MIGRACION EXCEL DE ACTIVIDADES - Formulario N° 4 / 2027
      public function valida_migracion_form4() {
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '512M');    // Aumentar memoria

        $this->load->library('excel'); 
        $com_id = $this->input->post('com_id');
        $get_unidad = $this->model_componente->get_componente($com_id, $this->gestion);
        
        // Carga de catálogo relacional de validación de los Objetivos Regionales
        $list_oregional = $this->model_objetivoregion->list_proyecto_oregional($get_unidad[0]['proy_id']);

        if (empty($get_unidad)) {
            echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información de la Unidad Organizacional. Verifique su sesión.')));
            return;
        }

        if (!isset($_FILES['archivo']) || empty($_FILES['archivo']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        $archivo = $_FILES['archivo']['tmp_name'];
        $errores = array();
        $data_insertar = array();

        try {
            $archivoTipo = PHPExcel_IOFactory::identify($archivo);
            $lector      = PHPExcel_IOFactory::createReader($archivoTipo);
            
            // OPTIMIZACIÓN DE MEMORIA: Ignoramos estilos gráficos pesados para no colapsar la RAM
            $lector->setReadDataOnly(true);
            
            $phpExcel    = $lector->load($archivo);
            $hoja        = $phpExcel->getSheet(0);
            $filasMax    = $hoja->getHighestRow();
            
            // --- 1. VALIDACIÓN DE ESTRUCTURA METRICA (Columnas Max V = 22) ---
            $columnaMaxLetra = $hoja->getHighestDataColumn(); 
            $totalColumnas   = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
            $limitePermitido = 22; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial estructurado exige exactamente $limitePermitido columnas (Hasta la 'V').")));
                return;
            }

            // --- 2. VALIDACIÓN FILA POR FILA ---
            for ($i = 2; $i <= $filasMax; $i++) {
                //$com_id = 0;
                $or_id  = 0;

                // Extraer valores básicos de la fila activa
                $cod_uresp          = trim($hoja->getCell('A' . $i)->getValue());
                $cod_acp            = trim($hoja->getCell('B' . $i)->getValue());
                $cod_ope            = trim($hoja->getCell('C' . $i)->getValue());
                $cod_act            = trim($hoja->getCell('D' . $i)->getValue());

                $actividad          = trim($hoja->getCell('E' . $i)->getValue());
                $resultado          = trim($hoja->getCell('F' . $i)->getValue());
                $unidad_responsable = trim($hoja->getCell('G' . $i)->getValue());
                $indicador          = trim($hoja->getCell('H' . $i)->getValue());
                $meta               = $hoja->getCell('I' . $i)->getValue();
                $medioverificacion  = trim($hoja->getCell('V' . $i)->getValue());

                // 🌟 BLINDAJE ANTIFALLA: Filtra y salta las filas vacías inferiores del Excel
                if (empty($cod_uresp) && empty($actividad) && (empty($meta) || floatval($meta) == 0)) {
                    continue;
                }

                if (!empty($cod_uresp)) {
                    if (strlen($cod_uresp) != 4) {
                        $errores[] = "Fila $i: El código de 'UNIDAD RESPONSABLE' ($cod_uresp) debe tener exactamente 4 caracteres.";
                    } 
                    else {
                      if($get_unidad[0]['serv_cod']!=$cod_uresp){
                        $errores[] = "Fila $i: Error en la 'UNIDAD RESPONSABLE' ($cod_uresp). debe exluir del archivo a migrar, ya que corresponde a otra Unidad Responsable.";
                      }
                    }
                } else {
                    $errores[] = "Fila $i: 'CODIGO DE UNIDAD RESPONSABLE' es obligatoria.";
                }

                // Verificando códigos ACP y Operación
                if (!empty($cod_acp) && is_numeric($cod_acp) && !empty($cod_ope) && is_numeric($cod_ope)) {
                    if (count($list_oregional) != 0) {
                        $get_acc = $this->model_objetivoregion->get_alineacion_proyecto_oregional($proy_id, $cod_acp, $cod_ope);
                        if (count($get_acc) != 0) {
                            $or_id = $get_acc[0]['or_id'];
                        } else {
                            $errores[] = "Fila $i: La combinación ACP ($cod_acp) y OPERACIÓN ($cod_ope) no guarda relación con los Objetivos Regionales.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: 'CODIGO ACP Y OPERACION' son obligatorios y deben ser numéricos.";
                }

                if (!is_numeric($meta) || floatval($meta) <= 0) {
                    $errores[] = "Fila $i: La 'META' debe ser un número válido mayor a cero.";
                }

                // Validación C: Cronograma Mensualizado Saneado (J al U)
                $suma_meses = 0;
                $columnas_meses = array('J','K','L','M','N','O','P','Q','R','S','T','U');
                $meses_valores = array();
                $m_index = 1;
                
                foreach ($columnas_meses as $col) {
                    $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                    $val_mes     = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);

                    if (!is_numeric($val_mes)) {
                        $errores[] = "Fila $i: Valor no numérico detectado en el mes de la columna '$col'.";
                        break;
                    }
                    $monto_mes = floatval($val_mes);
                    $suma_meses += $monto_mes;
                    $meses_valores[$m_index] = $monto_mes; // Guardamos en el array indexado temporal de la fila
                    $m_index++;
                }

                // Validación de Coincidencia Física Matemática
                if (abs($suma_meses - floatval($meta)) > 0.01) {
                    $errores[] = "Fila $i: La suma de los meses ($suma_meses) no coincide con la meta ($meta).";
                }

                if (empty($errores)) {
                    // 🌟 SOLUCIÓN RAÍZ: Agrupamos el Maestro y su Detalle mensual correspondiente en paralelo
                    $data_insertar[] = array(
                        'maestro' => array(
                            'com_id'                   => $com_id,
                            'prod_cod'                 => intval($cod_act),
                            'prod_producto'            => strtoupper($actividad),
                            'prod_resultado'           => strtoupper($resultado),
                            'indi_id'                  => 1,
                            'prod_indicador'           => strtoupper($indicador),
                            'prod_fuente_verificacion' => strtoupper($medioverificacion), 
                            'prod_linea_base'          => 0,
                            'prod_meta'                => floatval($meta),
                            'uni_resp'                 => 0, 
                            'prod_unidades'            => strtoupper($unidad_responsable),
                            'acc_id'                   => 0,
                            'prod_ppto'                => 1,
                            'fecha'                    => date("d/m/Y H:i:s"),
                            'or_id'                    => $or_id,
                            'fun_id'                   => intval($this->session->userdata('fun_id')),
                            'num_ip'                   => $this->input->ip_address(), 
                            'nom_ip'                   => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                        ),
                        'meses_lote' => $meses_valores // Queda amarrado en paralelo
                    );
                }
                
                if (count($errores) > 15) break; 
            } // Fin del bucle general FOR por fila

            if (ob_get_length()) ob_clean(); 
            header('Content-Type: application/json');

            if (empty($errores) && !empty($data_insertar)) {
                $this->db->trans_start(); // Iniciar transacción atómica en Postgres
                
                foreach ($data_insertar as $fila) {
                    // Inserción A: Registro maestro del Producto en tu tabla '_productos'
                    $this->db->insert('_productos', $fila['maestro']);
                    $prod_id = $this->db->insert_id(); // Capturamos el ID de la base de datos
                    
                    /*------------ REGISTRO DE LA TEMPORALIDAD EN TU TABLA REAL ---------*/
                    for ($m = 1; $m <= 12; $m++) {
                        $pfin = $this->security->xss_clean($fila['meses_lote'][$m]);
                        
                        if ($pfin != 0) {
                            $data_to_store4 = array( 
                                'prod_id' => $prod_id,
                                'm_id'    => $m, 
                                'pg_fis'  => $pfin, 
                                'g_id'    => intval($this->gestion), 
                            );
                            $this->db->insert('prod_programado_mensual', $data_to_store4);
                        }
                    }
                    /*-------------------------------------------------------------------*/
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'errors' => array('Error al insertar en la base de datos (Transacción fallida en Postgres).')));
                } else {
                    echo json_encode(array(
                        'status' => 'success', 
                        'msj'    => 'Importación finalizada con éxito.',
                        'conteo' => count($data_insertar) 
                    ));
                }
            } else {
                echo json_encode(array(
                    'status' => 'error', 
                    'errors' => !empty($errores) ? $errores : array('El archivo parece estar vacío o no tiene datos válidos para procesar.')
                ));
            }
            exit; 

        } catch (Exception $e) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'errors' => array('Excepción crítica de PHPExcel: ' . $e->getMessage())));
        }
    }



    /// ==== MIGRACION EXCEL DE REQUERIMIENTOS GLOBAL - Formulario N° 5 / 2027
      public function valida_migracion_form5_consolidado() {
        ini_set('max_execution_time', 900); // 15 minutos máximos de procesamiento de CPU
        ini_set('memory_limit', '3072M'); 
        $this->load->library('excel'); 
        $com_id = $this->input->post('com_id');
        $get_unidad = $this->model_componente->get_componente($com_id, $this->gestion);

        if (empty($get_unidad) || count($get_unidad) == 0) {
            echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información de la Unidad Organizacional. Verifique su sesión.')));
            return;
        }

        if (!isset($_FILES['archivo_f5']) || empty($_FILES['archivo_f5']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        $archivo = $_FILES['archivo_f5']['tmp_name'];
        $errores = array();
        $data_insertar = array();

        try {
            $archivoTipo = PHPExcel_IOFactory::identify($archivo);
            $lector      = PHPExcel_IOFactory::createReader($archivoTipo);
            
            // OPTIMIZACIÓN DE MEMORIA: Ignoramos estilos gráficos pesados para no colapsar la RAM
            $lector->setReadDataOnly(true);
            
            $phpExcel    = $lector->load($archivo);
            $hoja        = $phpExcel->getSheet(0);
            $filasMax    = $hoja->getHighestRow();
            
            // --- 1. VALIDACIÓN DE ESTRUCTURA METRICA (Columnas Max V = 22) ---
            $columnaMaxLetra = $hoja->getHighestDataColumn(); 
            $totalColumnas   = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
            $limitePermitido = 20; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial estructurado exige exactamente $limitePermitido columnas (Hasta la 'T').")));
                return;
            }

            // --- 2. VALIDACIÓN FILA POR FILA ---
           for ($i = 2; $i <= $filasMax; $i++) {
                $prod_id = 0;
                $par_id  = 0;
                
                // Extraer valores básicos de la fila activa según la imagen enviada
                $cod_act       = trim($hoja->getCell('A' . $i)->getValue());
                $partida       = trim($hoja->getCell('B' . $i)->getValue());
                $requerimiento = trim($hoja->getCell('C' . $i)->getValue());
                $unidad_medida = trim($hoja->getCell('D' . $i)->getValue());
                
                $cantidad_raw  = $hoja->getCell('E' . $i)->getCalculatedValue();
                $precio_raw    = $hoja->getCell('F' . $i)->getCalculatedValue();
                $total_raw     = $hoja->getCell('G' . $i)->getCalculatedValue();
                $observacion   = trim($hoja->getCell('T' . $i)->getValue());

                // ==========================================================================
                // 🛠️ AJUSTADO: TOLERANCIA CERO A FILAS VACÍAS O CON RESIDUOS DE FORMATO
                // ==========================================================================
                if (empty($cod_act) && empty($partida) && empty($requerimiento) && (empty($total_raw) || floatval($total_raw) == 0)) {
                    
                    // Alerta institucional con la instrucción didáctica de limpieza
                    $errores[] = "🚨 RECHAZO DE PLANILLA: Se detectó que la Fila N° $i está completamente vacía o contiene residuos de formato invisible de Excel. Por favor, abra su archivo Excel, seleccione la Fila $i completa (haciendo clic en el número de la fila a la izquierda), haga clic derecho y elija la opción 'Eliminar' para purgar la planilla antes de reintentar la subida.";
                    
                    // Detiene el bucle por completo para no procesar hileras vacías inferiores
                    break; 
                }

                // 📋 REGLA 1: VALIDACIÓN DE CANTIDAD ENTERA (Sin decimales)
                if ($cantidad_raw === NULL || trim($cantidad_raw) === '' || !is_numeric($cantidad_raw)) {
                    $errores[] = "Fila $i: La 'CANTIDAD' es obligatoria y debe ser numérica.";
                } else {
                    $cantidad_float = floatval($cantidad_raw);
                    if ($cantidad_float != floor($cantidad_float)) {
                        $errores[] = "Fila $i: Restricción contable -> La 'CANTIDAD' ($cantidad_raw) debe ser un número entero puro, sin decimales.";
                    }
                }
                $cantidad = intval($cantidad_raw);

                // 📋 REGLA 2: VALIDACIÓN DE PRECIO UNITARIO (Máximo 2 decimales)
                if ($precio_raw === NULL || trim($precio_raw) === '' || !is_numeric($precio_raw)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' es obligatorio y debe ser numérico.";
                } else {
                    $precio_float = floatval($precio_raw);
                    if (round($precio_float, 2) != $precio_float) {
                        $errores[] = "Fila $i: El 'PRECIO UNITARIO' ($precio_raw) excede el límite. Solo se aceptan hasta 2 decimales (Ej: 2500.00).";
                    }
                }
                $precio = round(floatval($precio_raw), 2);

                // 📋 REGLA 3: VALIDACIÓN DEL COSTO TOTAL MATEMÁTICO (Cantidad * Precio)
                $total_calculado = round(($cantidad * $precio), 2);
                $total_archivo   = round(floatval($total_raw), 2);

                if (abs($total_archivo - $total_calculado) > 0.05) {
                    $errores[] = "Fila $i: El 'PRECIO TOTAL' registrado ($total_raw) no coincide con la ecuación aritmética (Cantidad: $cantidad * Precio: $precio = $total_calculado).";
                }

                // Validación y alineación relacional con la actividad (Formulario N° 4)
                if (!empty($cod_act)) {
                    $get_form4 = $this->model_producto->verif_form4_vigente_para_alineacion($com_id, $cod_act);
                    
                    if (!empty($get_form4) && count($get_form4) == 1) {
                        $prod_id = $get_form4[0]['prod_id']; 
                    } else {
                        if (count($get_form4) > 1) {
                            $errores[] = "Fila $i: Alerta de Consistencia -> Existe más de una actividad registrada con el código ($cod_act) para esta Unidad Organizacional. Sanee sus códigos.";
                        } else {
                            $errores[] = "Fila $i: El CÓDIGO DE ACTIVIDAD ($cod_act) no corresponde a ninguna actividad vigente en el Formulario N° 4 para esta Unidad Organizacional.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: El 'CÓDIGO DE ACTIVIDAD' es obligatorio para enlazar físicamente el requerimiento.";
                }

                 // Validación de Partida
                if (!empty($partida)) {
                    if (strlen($partida) != 5) {
                        $errores[] = "Fila $i: La 'PARTIDA' ($partida) debe tener exactamente 5 caracteres.";
                    } else {
                        $get_partida = $this->model_partidas->dato_par_codigo($partida);
                        if (!empty($get_partida) && count($get_partida) == 1) {
                            $par_id = $get_partida[0]['par_id'];
                        } else {
                            $errores[] = "Fila $i: La partida contable ($partida) no existe en el clasificador de la base de datos.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: La 'PARTIDA' es obligatoria.";
                }

                // 📋 REGLA 4: VALIDACIÓN MÁSTER Y RESOLUCIÓN DE FÓRMULAS EN LOS 12 MESES (H hasta la S)
                $suma_meses = 0;
                $columnas_meses = array('H' => 1,'I' => 2,'J' => 3,'K' => 4,'L' => 5,'M' => 6,'N' => 7,'O' => 8,'P' => 9,'Q' => 10,'R' => 11,'S' => 12);
                $meses_valores = array();
                
                foreach ($columnas_meses as $col => $mes_nro) {
                    // 🛠️ REPARADO: getCalculatedValue() resuelve la fórmula de Excel (ej: =SUMA(), =5000/12) y extrae el resultado numérico puro
                    $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                    $val_mes     = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);
                    
                    if (!is_numeric($val_mes)) {
                        $errores[] = "Fila $i: Valor o fórmula no numérica detectada en la columna del mes '$col'.";
                        break;
                    }
                    
                    $monto_mes = round(floatval($val_mes), 2);
                    $suma_meses += $monto_mes;
                    $meses_valores[$mes_nro] = $monto_mes; 
                }

                // 📋 REGLA 5: COMPROBACIÓN DE COINCIDENCIA (Suma de meses == Costo Total)
                if (abs($suma_meses - $total_archivo) > 0.05) { 
                    $errores[] = "Fila $i: La suma de la distribución mensual ($suma_meses) no cuadra con el PRECIO TOTAL ($total_archivo) de la celda G.";
                }

                if (empty($errores)) {
                    $data_insertar[] = array(
                        'maestro' => array(
                            'ins_codigo'              => $this->session->userdata("name") . '/REQ/' . $this->gestion,
                            'ins_fecha_requerimiento' => date('Y-m-d'), 
                            'par_id'                  => $par_id,
                            'ins_detalle'             => strtoupper($this->security->xss_clean($requerimiento)),
                            'ins_unidad_medida'       => strtoupper($this->security->xss_clean($unidad_medida)),
                            'ins_cant_requerida'      => $cantidad,
                            'ins_costo_unitario'      => $precio,
                            'ins_costo_total'         => $total_archivo,
                            'ins_observacion'         => strtoupper($this->security->xss_clean($observacion)),
                            'fun_id'                  => $this->fun_id,
                            'aper_id'                 => $get_unidad[0]['aper_id'], 
                            'com_id'                  => $get_unidad[0]['com_id'], 
                            'form4_cod'               => intval($cod_act), 
                            'ins_mod'                 => 1, // Conmutador de registro insertado
                            'num_ip'                  => $this->input->ip_address(), 
                            'nom_ip'                  => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                        ),
                        'meses' => $meses_valores // Array indexado del 1 al 12 resuelto por fórmulas
                    );
                }
            }

            if (empty($errores) && count($data_insertar) > 0) {
                
                // Levantamos los muros de control transaccional para aislar fallas de presupuesto
                $this->db->trans_start(); 
                $filas_insertadas_conteo = 0;

                foreach ($data_insertar as $registro) {
                    
                    // 🛠️ REPARADO: Se inserta únicamente la estructura plana del sub-arreglo 'maestro'
                    $this->db->insert('insumos', $registro['maestro']);
                    
                    // Recuperamos el ID autogenerado asignado por la secuencia en Postgres
                    $ins_id = $this->db->insert_id();

                    /*-----------------------------------------------*/
                    // B. Registro de la alineación relacional en la tabla _insumoproducto
                    $data_to_store2 = array(
                        'prod_id' => $prod_id, // Variable física relacional obtenida en la validación
                        'ins_id'  => $ins_id
                    );
                    $this->db->insert('_insumoproducto', $data_to_store2);
                    /*---------------------------------------------*/
                    
                    /*------------ REGISTRO DE LA TEMPORALIDAD ---------*/
                    // 🛠️ REPARADO: Se recorre la colección real 'meses' usando $m_id para no pisar el iterador superior $i
                    for ($m_id = 1; $m_id <= 12; $m_id++) {
                        $pfin = isset($registro['meses'][$m_id]) ? $registro['meses'][$m_id] : 0;
                        
                        if ($pfin != 0) {
                            $data_to_store4 = array( 
                                'ins_id'  => $ins_id,          // Id Insumo maestro correlativo
                                'mes_id'  => $m_id,            // Mes dinámico (1 al 12)
                                'ipm_fis' => $pfin,            // Valor físico financiero del mes resuelto
                                'g_id'    => $this->gestion,   // Gestión POA activa de sesión
                            );
                            $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                        }
                    }
                    
                    $filas_insertadas_conteo++;
                }

                // Cerramos e indicamos a CodeIgniter que evalúe el estatus de las inserciones
                $this->db->trans_complete();

                // Si PostgreSQL detecta un desbordamiento numérico o violación de tope, aplica Rollback total
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array(
                        'status'    => 'error', 
                        'respuesta' => 'error', 
                        'mensaje'   => 'PostgreSQL rechazó las restricciones físicas o techos de los requerimientos. Matriz revertida de forma íntegra.'
                    ));
                    return;
                }

                // 🌟 ÉXITO ABSOLUTO: Despachamos el payload esperado por tu $.ajax en form4.js
                echo json_encode(array(
                    'status'           => 'success',
                    'respuesta'        => 'correcto',
                    'mensaje'          => '¡Matriz de requerimientos contables consolidados e inyectados en el sistema de forma exitosa!',
                    'filas_procesadas' => $filas_insertadas_conteo
                ));

            } else {
                // Si la colección de errores contiene advertencias estructurales, frena e informa al usuario
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'mensaje'   => 'Se detectaron observaciones de validación en la estructura o coincidencia de la plantilla.',
                    'errores'   => !empty($errores) ? $errores : array("No se encontraron registros consistentes para migrar.")
                ));
            }

        } catch (Exception $e) {
            // Captura forense de desbordamientos de memoria del motor de PHPExcel
            echo json_encode(array(
                'status'    => 'error', 
                'respuesta' => 'error', 
                'mensaje'   => 'Falla crítica del lector de planillas: ' . $e->getMessage()
            ));
        }
    }


  /*--------- VALIDA FORM 4 (REGISTRO nuevo) -----------*/
  public function valida_producto(){
    if ($this->input->server('REQUEST_METHOD') === 'POST'){
        $this->form_validation->set_rules('prod', 'Producto', 'required|trim');
        $this->form_validation->set_rules('tipo_i', 'Tipo de Indicador', 'required|trim');
        $componente = $this->model_componente->get_componente($this->input->post('com_id'),$this->gestion);
        $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']);
        $form4 = $this->model_producto->lista_productos($this->input->post('com_id'));
        $cod_nuv=0;
        if(count($form4)!=0){
          $cod_nuv=(count($form4)+1);
        }  

        if($proyecto[0]['por_id']==1){ /// BOLSA
          $campo='uni_resp';
        }
        else{
          $campo='prod_unidades';
        }

        if($this->input->post('tipo_i')==1){
          $tp_met=3;
        }
        else{
          $tp_met=$this->input->post('tp_met');
        }
    

        if ($this->form_validation->run()){
          /*-------- INSERT FORMULARIO N 4 ---------*/
          $data_to_store = array(
            'com_id' => $this->input->post('com_id'),
            'prod_cod' => $cod_nuv,
            'or_id' => strtoupper($this->input->post('or_id')),
            'prod_producto' => strtoupper($this->input->post('prod')),
            'prod_resultado' => strtoupper($this->input->post('resultado')),
            'indi_id' => $this->input->post('tipo_i'),
            'prod_indicador' => strtoupper($this->input->post('indicador')),
            'prod_fuente_verificacion' => strtoupper($this->input->post('verificacion')), 
            'prod_meta' => $this->input->post('meta'),
            $campo => strtoupper($this->input->post('uni_resp')),
            'mt_id' => $tp_met,
            'fecha' => date("d/m/Y H:i:s"),
            'fun_id' => $this->fun_id,
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
          );
          $this->db->insert('_productos', $data_to_store);
          $id_pr=$this->db->insert_id(); ////// id del producto
          /*------------------------------------------------------*/
          if($this->input->post('tipo_i')==1){
            for ($i=1; $i <=12 ; $i++) {
              if($this->input->post('m'.$i)!=0){
                $this->model_producto->add_prod_gest($id_pr,$this->gestion,$i,$this->input->post('m'.$i));
              }
            }
          }
          if($this->input->post('tipo_i')==2){
            if($tp_met==3){
              for ($i=1; $i <=12 ; $i++) {
                if($this->input->post('m'.$i)!=0){
                  $this->model_producto->add_prod_gest($id_pr,$this->gestion,$i,$this->input->post('m'.$i));
                }
              }
            }
            elseif($tp_met==1){
              for ($i=1; $i <=12 ; $i++) {
                $this->model_producto->add_prod_gest($id_pr,$this->gestion,$i,$this->input->post('meta'));
              }
            }
            elseif($tp_met==5){ /// trimestre recurrente
              for ($i=1; $i <=4 ; $i++) {
                $this->model_producto->add_prod_gest($id_pr,$this->gestion,($i*3),$this->input->post('meta'));
              }
            }
          }

          $producto=$this->model_producto->get_producto_id($id_pr);
          if(count($producto)==1){
            $this->session->set_flashdata('success','LA ACTIVIDAD SE REGISTRO CORRECTAMENTE :)');
          }
          else{
            $this->session->set_flashdata('danger','ERROR AL REGISTRAR ACTIVIDAD, VUELVA REGISTRAR :(');
          }

          redirect('admin/prog/list_prod/'.$this->input->post('com_id').'');
        }
        else{
          $this->session->set_flashdata('danger','ERROR AL REGISTRAR LA ACTIVIDAD :(');
          redirect('admin/prog/list_prod/'.$this->input->post('com_id').'');
        }
    }
    else{
      echo "<center><font color='red'>Error, Vuelva a registrar la Actividad !!!!</font></center>";
    }
  }

    /*---- GET VER REQUERIMIENTOS CARGADOS POR UNIDAD RESPONSABLE 2027 ----*/
    public function get_ver_requerimientos(){
        // Validamos que sea una petición asíncrona legítima de JQuery
        if($this->input->is_ajax_request() && $this->input->post()){
            // 🌟 REGLA 1: Blindaje extremo de recursos de hardware en el backend
            if (function_exists('ini_set')) {
                ini_set('max_execution_time', 300); // 5 minutos de tiempo máximo de ejecución
                ini_set('memory_limit', '512M');    // Búfer intermedio para listas densas de insumos
            }

            // 1. Captura y sanitización estricta de la llave foránea relacional
            $post   = $this->input->post();
            $com_id = intval($this->security->xss_clean($post['com_id'])); // 🛠️ REPARADO: Cast a integer

            if ($com_id <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador relacional del componente inválido o corrupto.'));
                return;
            }

            // 2. Consulta al modelo del catálogo del POA CNS
            $componente = $this->model_componente->get_componente($com_id, $this->gestion);
            
            if (empty($componente) || count($componente) == 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'La Unidad Organizacional solicitada no existe o no tiene vigencia en la presente gestión.'));
                return;
            }

            // 3. Limpieza preventiva del buffer del framework para liberar RAM antes de renderizar la matriz
            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            // 4. Invocamos tu subfunción armadora que genera el HTML de la rejilla de insumos
            $tabla = $this->vista_previa_requerimientos_uorganizacional($componente);

            // 5. Consolidamos el vector final despachando los índices exactos esperados por tu JS
            $result = array(
                'status'    => 'success',
                'respuesta' => 'correcto',
                'tabla'     => $tabla
            );
            echo json_encode($result);
        } else {
            // Protección perimetral contra accesos directos desde la barra de direcciones del explorador
            show_404();
        }
    }


    /// -- Vista previa de FORMULARIO N° 5 - requerimientos 2027
    public function vista_previa_requerimientos_uorganizacional($componente){
      $lista_insumos=$this->model_insumo->list_requerimientos_uresponsable($componente[0]['com_id']); /// Lista requerimientos
      $tabla='';
      $tabla.=' 
      <h2>'.strtoupper($componente[0]['tipo']).' '.strtoupper($componente[0]['proy_nombre']).' - '.strtoupper($componente[0]['abrev']).' / '.$componente[0]['serv_cod'].' .- '.$componente[0]['serv_descripcion'].'</h2>
      <hr>
        <section class="col col-6">
          <input id="searchTerm" type="text" onkeyup="doSearch()" class="form-control" placeholder="BUSCADOR...." style="width:45%;"/><br>
        </section>
            <table class="table table-bordered" id="datos">
              <thead>
              <tr style="font-size: 12px;" bgcolor="#eceaea" align=center>
                <th style="width:1%;height:15px;">#</th>
                <th style="width:2%;">COD.<br>ACT.</th> 
                <th style="width:4%;">PARTIDA</th>
                <th style="width:18%;">DETALLE REQUERIMIENTO</th>
                <th style="width:5%;">UNIDAD</th>
                <th style="width:4%;">CANTIDAD</th>
                <th style="width:5%;">UNITARIO</th>
                <th style="width:5%;">TOTAL</th>
                <th style="width:4%;">ENE.</th>
                <th style="width:4%;">FEB.</th>
                <th style="width:4%;">MAR.</th>
                <th style="width:4%;">ABR.</th>
                <th style="width:4%;">MAY.</th>
                <th style="width:4%;">JUN.</th>
                <th style="width:4%;">JUL.</th>
                <th style="width:4%;">AGO.</th>
                <th style="width:4%;">SEPT.</th>
                <th style="width:4%;">OCT.</th>
                <th style="width:4%;">NOV.</th>
                <th style="width:4%;">DIC.</th>
                <th style="width:8%;">OBSERVACI&Oacute;N</th>
              </tr>
              </thead>
              <tbody>';
              $cont = 0; $total=0;
              foreach ($lista_insumos as $row) {
              $cont++; $total=$total+$row['ins_costo_total'];
              $tabla.=
              '<tr style="font-size: 10.5px;" >
                  <td style="width: 1%; font-size: 6px; text-align: center;height:13px;">'.$cont.'</td>
                  <td style="width: 2%; text-align: center;font-size: 15px;"><b>'.$row['prod_cod'].'</b></td>
                  <td style="width: 4%; text-align: center; font-size: 15px;"><b>'.$row['par_codigo'].'</b></td>
                  <td style="width: 18%; text-align: left;">'.strtoupper($row['ins_detalle']).'</td>
                  <td style="width: 5%; text-align: left">'.strtoupper($row['ins_unidad_medida']).'</td>
                  <td style="width: 4%; text-align: right">'.round($row['ins_cant_requerida'],2).'</td>
                  <td style="width: 5%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>
                  <td style="width: 5%; text-align: right;font-size: 12px; background:yellow;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>'; 
                  for ($i=1; $i <=12 ; $i++) { 
                    $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['mes_'.$i], 2, ',', '.').'</td>';
                  }
              $tabla.='
                  <td style="width: 8%; text-align: left;">'.$row['ins_observacion'].'</td>
                  
              </tr>';
              }

          $tabla.='
              </tbody>
              <tr class="modo1" bgcolor="#eceaea">
                  <td colspan="7" style="height:10px;" ><b>TOTAL PROGRAMADO </b></td>
                  <td style="width: 4%; text-align: right; font-size: 15px;"><b>'.number_format($total, 2, ',', '.').'</b></td>
                  <td colspan="13"></td>
              </tr>
          </table><br>';
      return $tabla;
    }



    /*------ CAMBIA PRIORIDAD DE LA ACTIVIDAD---------*/
    function asignar_prioridad(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('prod_id', 'id producto', 'required|trim');
          $this->form_validation->set_message('required', 'El campo es es obligatorio');
        
          $post = $this->input->post();
          $prod_id= $this->security->xss_clean($post['prod_id']);
          $prioridad= $this->security->xss_clean($post['prioridad']);
           
          $update_prod = array(
            'prod_priori' => $prioridad,
          );
          $this->db->where('prod_id', $prod_id);
          $this->db->update('_productos', $update_prod);
              
      }else{
          show_404();
      }
    }


    /*--- ELIMINAR TOD@S LAS ACTIVIDADES y REQUERIMIENTOS DE LA UNIDAD responsable (2027) ---*/
     public function delete_form4($com_id) {
        // Validamos que sea una petición asíncrona legítima de JQuery
        if ($this->input->is_ajax_request()) {
            
            $com_id_clean = intval($com_id);

            if ($com_id_clean <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador del componente corrupto o vacío.'));
                return;
            }
            
            $verif=$this->model_componente->lista_Verif_items_cert_x_componente($com_id_clean);
            if ($com_id_clean <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador del componente corrupto o vacío.'));
                return;
            }

            // Ejecutamos tu consulta optimizada para cazar certificaciones activas en la gestión actual
            if (count($verif) != 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Error al eliminar actividades de la Unidad Responsable, existen Items Certificados..'));
                return;
            }

            // 1. Recuperamos la matriz completa de actividades asociadas a esta Unidad Organizacional
            $form4 = $this->model_producto->lista_form4_x_unidadresponsable($com_id_clean);
            
            if (empty($form4) || count($form4) == 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'La Unidad Responsable seleccionada ya se encuentra vacía. No existen registros para purgar.'));
                return;
            }

            // ==========================================================================
            // 🌟 INICIO DE COMPUERTA TRANSACCIONAL ATÓMICA DE MÁXIMA SEGURIDAD (POSTGRESQL)
            // ==========================================================================
            $this->db->trans_start();

            foreach ($form4 as $rowp) {
                $prod_id_actual = floatval($rowp['prod_id']); // numeric(18,0)

                // 🛠️ REPARADO: Se utiliza el modelo y función real certificada de tu proyecto 'insumo_producto'
                $insumos = $this->model_producto->insumo_producto($prod_id_actual); 
                
                if (!empty($insumos) && count($insumos) > 0) {
                    foreach ($insumos as $rowi) {
                        $ins_id_actual = intval($rowi['ins_id']);

                        // PASO A: Purgamos la temporalidad financiera mensual del insumo
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('temporalidad_prog_insumo');

                        // PASO B: Purgamos el nudo de enlace intermedio de la tabla cruzada _insumoproducto
                        $this->db->where('prod_id', $prod_id_actual);
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('_insumoproducto');

                        // PASO C: Eliminamos físicamente la cabecera del requerimiento en la tabla insumos
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('insumos');
                    }
                }

                // PASO D: Una vez limpio el Formulario 5, purgamos el cronograma físico mensualizado de la actividad (Form 4)
                $this->db->where('prod_id', $prod_id_actual);
                $this->db->delete('prod_programado_mensual');

                // PASO E: Purgamos la hilera maestra de la actividad de la tabla de productos
                $this->db->where('prod_id', $prod_id_actual);
                $this->db->delete('_productos');
            }

            // Sella las inserciones obligando a PostgreSQL a verificar la consistencia del lote entero
            $this->db->trans_complete();
            // ==========================================================================

            // 2. VERIFICACIÓN POST-TRANSACCIONAL DE COINCIDENCIA DE BASE DE DATOS
            if ($this->db->trans_status() !== FALSE) {
                
                $result = array(
                    'status'    => 'success',
                    'respuesta' => 'correcto',
                    'message'   => 'Se ha purgado de forma completa y absoluta la matriz física y financiera de la Unidad Responsable.'
                );
                
                $this->session->set_flashdata('success', 'SE ELIMINO CORRECTAMENTE EL FORMULARIO DE LA UNIDAD.');

            } else {
                $result = array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'message'   => 'PostgreSQL abortó el vaciado en lote debido a una violación de integridad relacional externa.'
                );
            }

            echo json_encode($result);

        } else {
            show_404();
        }
    }


    /*--- ELIMINAR TOD@S LOS REQUERIMIENTOS DEL COMPONENTE (SOLO REQUERIMIENTOS) (2027) ---*/
      public function delete_insumos_Unidad_Responsable($com_id) {
        // Validamos que sea una petición asíncrona legítima de JQuery
        if ($this->input->is_ajax_request()) {
            
            $com_id_clean = intval($com_id);
            $g_id = intval($this->gestion);

            if ($com_id_clean <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador del componente corrupto o vacío.'));
                return;
            }
            // ==========================================================================
            if ($com_id_clean <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador del componente corrupto o vacío.'));
                return;
            }
            // ==========================================================================

            // Ejecutamos tu consulta optimizada para cazar certificaciones activas en la gestión actual
            $verif=$this->model_componente->lista_Verif_items_cert_x_componente($com_id_clean);
            if (count($verif) != 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Error al eliminar Requerimientos, existen Items Certificados..'));
                return;
            }

            $productos = $this->model_producto->lista_form4_x_unidadresponsable($com_id_clean);
            $nro_ins = 0;

            if (empty($productos) || count($productos) == 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'La Unidad Responsable seleccionada no registra actividades vigentes.'));
                return;
            }

            // ==========================================================================
            // 🌟 INICIO DE COMPUERTA TRANSACCIONAL ATÓMICA DE MÁXIMA SEGURIDAD
            // ==========================================================================
            $this->db->trans_start();

            foreach ($productos as $rowp) {
                $prod_id_actual = floatval($rowp['prod_id']); // numeric(18,0)
                
                // Usamos la función interna de tu proyecto
                $insumos = $this->model_insumo->lista_insumos_prod($prod_id_actual);
                
                if (!empty($insumos) && count($insumos) > 0) {
                    foreach ($insumos as $rowi) {
                        $ins_id_actual = intval($rowi['ins_id']);

                        // PASO A: Purgamos la temporalidad financiera mensual del insumo
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('temporalidad_prog_insumo');

                        // PASO B: Purgamos el nudo relacional de la tabla intermedia cruzada
                        $this->db->where('prod_id', $prod_id_actual);
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('_insumoproducto');

                        // PASO C: Eliminamos físicamente el insumo
                        $this->db->where('ins_id', $ins_id_actual);
                        $this->db->delete('insumos');

                        $nro_ins++; // Acumulador de auditoría exitosa
                    }
                }

                // 🛠️ REPARACIÓN SOLUCIÓN GENERAL: Limpieza cruda complementaria fuera del bucle
                $this->db->where('prod_id', $prod_id_actual);
                $this->db->delete('_insumoproducto');

                // PASO D: Reseteamos la meta física anual a cero debido a la remoción de su presupuesto
                $this->db->where('prod_id', $prod_id_actual);
                $this->db->update('_productos', array(
                    'prod_ppto' => 1,
                    'fun_id'    => $this->fun_id
                ));
            }

            // Sella el lote completo de consultas forzando consistencia en Postgres
            $this->db->trans_complete();
            // ==========================================================================

            if ($this->db->trans_status() !== FALSE) {
                
                // 🛠️ REPARADO: Se quita el redirect síncrono y se despacha el JSON esperado por tu request.done
                echo json_encode(array(
                    'status'    => 'success',
                    'respuesta' => 'correcto',
                    'message'   => 'Se eliminaron correctamente ' . $nro_ins . ' requerimientos contables del Formulario N° 5.'
                ));
                
                $this->session->set_flashdata('success', 'SE ELIMINO CORRECTAMENTE ' . $nro_ins . ' REQUERIMIENTOS DE LA UNIDAD.');

            } else {
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'message'   => 'PostgreSQL rechazó la purga masiva de insumos debido a restricciones de consistencia interna.'
                ));
            }

        } else {
            show_404();
        }
    }


    // public function delete_insumos_Unidad_Responsable($com_id){

    //   $productos=$this->model_producto->lista_form4_x_unidadresponsable($com_id);
    //   $nro=0;$nro_ins=0;
    //   //echo "eliminar productos";
    //   foreach($productos as $rowp){
    //     $insumos=$this->model_insumo->lista_insumos_prod($rowp['prod_id']);
    //     foreach ($insumos as $rowi) {
    //       /*--------- delete temporalidad --------*/
    //       $this->db->where('ins_id', $rowi['ins_id']);
    //       $this->db->delete('temporalidad_prog_insumo');

    //       $this->db->where('ins_id', $rowi['ins_id']);
    //       $this->db->delete('_insumoproducto');

    //       /*--------- delete Insumos --------*/
    //       $this->db->where('ins_id', $rowi['ins_id']);
    //       $this->db->delete('insumos');

    //       if(count($this->model_insumo->get_insumo_producto($rowi['ins_id']))==0){
    //         $nro_ins++;
    //       }
    //     }
    //   }

    //   $update_prod= array(
    //     'fun_id' => $this->fun_id,
    //     'prod_ppto' => 1
    //   );
    //   $this->db->where('com_id', $com_id);
    //   $this->db->update('_productos', $update_prod);


    //   $this->session->set_flashdata('success','SE ELIMINO CORRECTAMENTE '.$nro_ins.' REQUERIMIENTOS DE LA UNIDAD ');
    //   redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
    // }


    /*--- ELIMINAR LISTA TOTAL DE REQUERIMEITNOS POR UNIDAD*/
    // public function delete_list_requerimientos($aper_id){
    //   $insumos=$this->model_insumo->insumos_por_unidad($aper_id);
    //   $nro_ins=0;
    //   foreach ($insumos as $rowi) {
    //     /*--------- delete temporalidad --------*/
    //     $this->db->where('ins_id', $rowi['ins_id']);
    //     $this->db->delete('temporalidad_prog_insumo');

    //     $this->db->where('ins_id', $rowi['ins_id']);
    //     $this->db->delete('_insumoproducto');

    //     /*--------- delete Insumos --------*/
    //     $this->db->where('ins_id', $rowi['ins_id']);
    //     $this->db->delete('insumos');

    //     if(count($this->model_insumo->get_insumo_producto($rowi['ins_id']))==0){
    //       $nro_ins++;
    //     }
    //   }

    //   return $nro_ins;
    // }


    /*----- ELIMINAR VARIOS OPERACIONES SELECCIONADOS -----*/
    public function delete_operaciones(){
      if ($this->input->post()) {
          $post = $this->input->post();
          $com_id = $this->security->xss_clean($post['com_id']); /// com id
          $componente = $this->model_componente->get_componente($com_id,$this->gestion);
         // $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); 

          $nro=0; $nro_ins=0;
          if (!empty($_POST["req"]) && is_array($_POST["req"]) ) {
          foreach ( array_keys($_POST["req"]) as $como){
            /*--------- delete Insumosproducto --------*/
            $insumos = $this->model_producto->insumo_producto($_POST["req"][$como]); /// Insumo del producto
            foreach ($insumos as $rowi) {
              /*--------- delete temporalidad --------*/
              $this->db->where('ins_id', $rowi['ins_id']);
              $this->db->delete('temporalidad_prog_insumo');

              $this->db->where('prod_id', $_POST["req"][$como]);
              $this->db->where('ins_id', $rowi['ins_id']);
              $this->db->delete('_insumoproducto');

              /*--------- delete Insumos --------*/
              $this->db->where('ins_id', $rowi['ins_id']);
              $this->db->delete('insumos');

              if(count($this->model_insumo->get_insumo_producto($rowi['ins_id']))==0){
                $nro_ins++;
              }
            }

            /*------ delete Productos -----*/
              $this->db->where('prod_id', $_POST["req"][$como]);
              $this->db->delete('prod_programado_mensual');

            /*------ delete Productos -----*/
              $this->db->where('prod_id', $_POST["req"][$como]);
              $this->db->delete('_productos');
            
            
            $prod=$this->model_producto->get_producto_id($_POST["req"][$como]);
            if(count($prod)==0){
              $nro++;
            }
            
          }

          $this->session->set_flashdata('success','SE ELIMINO CORRECTAMENTE '.$nro.' OPERACIONES SELECCIONADOS y '.$nro_ins.' REQUERIMIENTOS ');
          redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
        }
        else{
          $this->session->set_flashdata('danger','ERROR AL ELIMINAR OPERACIONES');
          redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
        }
      }
      else{
        echo "<font color=red><b>Error al Eliminar Operaciones</b></font>";
      }
    }
       

    /*------ ELIMINA EL PRODUCTO Y SUS REQUERIMIENTOS 2027 ------*/
    public function desactiva_producto(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          // 🛠️ AJUSTE 1: Cast numérico elástico compatible con DDL numeric(18,0)
          $prod_id = floatval($this->security->xss_clean($post['prod_id'])); 
          
           if ($prod_id <= 0) {
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador relacional corrupto.'));
                return;
            }

            // ==========================================================================
            // CANDADO DE AUDITORÍA: VERIFICACIÓN POR PRODUCTO SELECCIONADO
            // ==========================================================================
            $sql_verif_cert_individual = "
                SELECT COUNT(cert_temp.ctins_id) AS total_certificados
                FROM public.insumos i
                INNER JOIN public._insumoproducto ip ON i.ins_id = ip.ins_id
                INNER JOIN public.temporalidad_prog_insumo temp ON i.ins_id = temp.ins_id
                INNER JOIN public.cert_temporalidad_prog_insumo cert_temp ON temp.tins_id = cert_temp.tins_id
                WHERE ip.prod_id = ?
                  AND i.ins_estado != 3 
                  AND i.ins_gestion = ?
            ";
            
            $query_cert_ind = $this->db->query($sql_verif_cert_individual, array($prod_id, intval($this->gestion)));
            $res_cert_ind   = $query_cert_ind->row_array();

            if (!empty($res_cert_ind) && intval($res_cert_ind['total_certificados']) > 0) {
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'message'   => 'Restricción de Operación: No se puede eliminar esta actividad debido a que contiene ('.$res_cert_ind['total_certificados'].') requerimientos con CERTIFICACIÓN PRESUPUESTARIA VIGENTE.'
                ));
                return;
            }

          $insumos = $this->model_producto->insumo_producto($prod_id); 
          // 🌟 AJUSTE 2: Compuesta transaccional atómica para blindar PostgreSQL
          $this->db->trans_start();

          if (!empty($insumos) && count($insumos) > 0) {
              foreach ($insumos as $rowi) {
                $ins_id_actual = intval($rowi['ins_id']);

                /*--------- delete temporalidad --------*/
                $this->db->where('ins_id', $ins_id_actual);
                $this->db->delete('temporalidad_prog_insumo');

                /*--------- delete _insumoproducto --------*/
                $this->db->where('prod_id', $prod_id);
                $this->db->where('ins_id', $ins_id_actual);
                $this->db->delete('_insumoproducto');

                /*--------- delete Insumos --------*/
                $this->db->where('ins_id', $ins_id_actual);
                $this->db->delete('insumos');
              }
          }

          /*------ delete temporalidad programada mensual Form 4 -----*/
          $this->db->where('prod_id', $prod_id);
          $this->db->delete('prod_programado_mensual');

          /*------ delete Productos maestro -----*/
          $this->db->where('prod_id', $prod_id);
          $this->db->delete('_productos');

          // Cerramos y obligamos al framework a validar el éxito de las consultas
          $this->db->trans_complete();

          // 🌟 AJUSTE 3: Verificación de éxito robusta post-transacción
          if ($this->db->trans_status() !== FALSE) {
              
              $prod = $this->model_producto->get_producto_id($prod_id);
              
              // Verificación elástica tanto para arrays asociativos como multidimensionales
              if (empty($prod) || count($prod) == 0) {
                $result = array(
                  'respuesta' => 'correcto',
                  'status'    => 'success'
                );
              } else {
                $result = array(
                  'respuesta' => 'error',
                  'message'   => 'El registro maestro se resistió al purgado físico.'
                );
              }
          } else {
              $result = array(
                  'respuesta' => 'error',
                  'message'   => 'PostgreSQL rechazó la eliminación por restricciones de llave externa.'
              );
          }

          echo json_encode($result);
      } else {
          // Protección perimetral contra ingresos directos por URL
          show_404();
      }
    }


    /*------ REPORTE FORM4 ----*/
/*    public function reporte_operacion_componente($com_id){
      $data['componente'] = $this->model_componente->get_componente_pi($com_id); //// DATOS COMPONENTE
      if(count($data['componente'])!=0){
        redirect(site_url("").'/prog/reporte_form4/'.$com_id.''); /// Reporte Form4 2021
      }
      else{
        echo "Error !!!";
      }
    }*/


    /*------ Para reporte poa 2019-2020 -----*/
/*    public function reporte_poa_2020($com_id){
      $data['componente'] = $this->model_componente->get_componente_pi($com_id); //// DATOS COMPONENTE
      $data['mes'] = $this->mes_nombre();
      $data['fase']=$this->model_faseetapa->get_fase($data['componente'][0]['pfec_id']); /// DATOS FASE
      $data['proyecto'] = $this->model_proyecto->get_id_proyecto($data['fase'][0]['proy_id']); //// DATOS PROYECTO

      $data['cabecera']=$this->cabecera_2020($com_id,1); /// Cabecera
      if($this->gestion==2019){ /// GESTION 2019
        if($data['proyecto'][0]['tp_id']==1){
          $data['operaciones']=$this->componente_operacion_pi_nuevo($com_id);
        }
        else{
          $data['operaciones']=$this->componente_operacion_nuevo($com_id);
        }
        
        $this->load->view('admin/programacion/producto/reporte_productos', $data);
      }
      else{ /// Para la gestion 2020
        if($data['proyecto'][0]['tp_id']==4){
          $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($data['fase'][0]['proy_id']);
        }

        $data['operaciones']=$this->componente_operaciones($com_id); /// Reporte Gasto Corriente, Proyecto de Inversion 2020
        $this->load->view('admin/programacion/producto/reporte_productos2020', $data);
      }
    }*/


    /*----- TITULO SERVICIO OPERACION (2020 - Operaciones) tp:1 (pdf), 2:(Excel) -----*/
/*    public function cabecera_2020($com_id,$tp){
      $obj_est=$this->model_producto->list_oestrategico($com_id); /// Objetivos Estrategicos
      $componente = $this->model_componente->get_componente_pi($com_id); //// DATOS COMPONENTE
      $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']); /// DATOS FASE
      //$proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']); //// DATOS PROYECTO
      $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($fase[0]['proy_id']);

      $tabla='';
      $tabla.='<table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                    <tr>
                      <td colspan="2" style="width:100%; height: 1.2%; font-size: 14pt;"><b>'.$this->session->userdata('entidad').'</b></td>
                    </tr>
                    <tr style="font-size: 8pt;">
                      <td style="width:10%; height: 1.2%"><b>DIR. ADM.</b></td>
                      <td style="width:90%;">: '.strtoupper($proyecto[0]['dep_departamento']).'</td>
                    </tr>
                    <tr style="font-size: 8pt;">
                      <td style="width:10%; height: 1.2%"><b>UNI. EJEC.</b></td>';
                      if($tp==1){  // pdf
                        $tabla.='<td style="width:90%;">: '.strtoupper($proyecto[0]['dist_distrital']).'</td>';
                      }
                      else{ // excel
                        $tabla.='<td style="width:90%;">: '.mb_convert_encoding(strtoupper($proyecto[0]['dist_distrital']), 'cp1252', 'UTF-8').'</td>';
                      }
                      $tabla.='
                    </tr>
                    <tr style="font-size: 8pt;">';
                      if($this->gestion!=2020){
                        $tabla.='<td style="height: 1.2%"><b>';
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='PROY. INV. ';
                          }
                          else{
                            $tabla.='ACTIVIDAD ';
                          }
                        $tabla.='</b></td>';
                        $tabla.='<td>: '.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.strtoupper($proyecto[0]['proy_nombre']).'</td>';
                      }
                      else{
                        $tabla.='<td style="height: 1.2%"><b>';
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='PROY. INV. ';
                          }
                          else{
                            $tabla.=''.$proyecto[0]['tipo_adm'].' ';
                          }
                        $tabla.='</b></td>';
                        if($tp==1){ /// pdf
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='<td>: '.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.strtoupper($proyecto[0]['proy_nombre']).'</td>';
                          }
                          else{
                            $tabla.='<td>: '.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' '.$proyecto[0]['tipo'].' - '.strtoupper($proyecto[0]['act_descripcion']).'-'.$proyecto[0]['abrev'].'</td>';
                          }
                        }
                        else{ /// Excel
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='<td>: '.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.mb_convert_encoding(strtoupper($proyecto[0]['proy_nombre']), 'cp1252', 'UTF-8').'</td>';
                          }
                          else{
                            $tabla.='<td>: '.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' '.$proyecto[0]['tipo'].' - '.mb_convert_encoding(strtoupper($proyecto[0]['act_descripcion']), 'cp1252', 'UTF-8').'-'.$proyecto[0]['abrev'].'</td>';
                          }
                        }    
                    }
                    $tabla.='
                    </tr>
                    <tr style="font-size: 8pt;">';
                      if($this->gestion!=2020){
                        $tabla.='<td style="height: 1.2%"><b>';
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='COMPONENTE ';
                          }
                          else{
                            $tabla.='SUB ACTIVIDAD ';
                          }
                        $tabla.='</b></td>';
                      }
                      else{
                        $tabla.='<td style="height: 1.2%"><b>';
                          if($proyecto[0]['tp_id']==1){
                            $tabla.='COMPONENTE ';
                          }
                          else{
                            $tabla.='SERVICIO ';
                          }
                        $tabla.='</b></td>';
                      }
                      if($tp==1){ // pdf
                        $tabla.='<td>: '.strtoupper($componente[0]['com_componente']).'</td>';
                      }
                      else{ // excel
                        $tabla.='<td>: '.mb_convert_encoding(strtoupper($componente[0]['com_componente']), 'cp1252', 'UTF-8').'</td>';
                      }
                      $tabla.='
                    </tr>
                </table>';
      return $tabla;
    }*/


    /*----- SERVICIO ACTIVIDAD (2020 - Operaciones, Proyectos de Inversion) - REPORTE ----*/
   /* public function componente_operaciones($com_id){
      $obj_est=$this->model_producto->list_oestrategico($com_id); /// Objetivos Estrategicos
      $componente = $this->model_componente->get_componente_pi($com_id); //// DATOS COMPONENTE
      $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']); /// DATOS FASE
      $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']); //// DATOS PROYECTO
      $mes = $this->mes_nombre();
      
      $tabla='';
      
      if($proyecto[0]['tp_id']==1){ /// Proyectos de Inversion
        $tabla.='<table cellpadding="0" cellspacing="0" class="tabla" border=0.2 style="width:100%;" align=center>
                <thead>
                  <tr style="font-size: 7px;" bgcolor=#1c7368 align=center>
                    <th style="width:1%;height:15px;color:#FFF;">#</th>
                    <th style="width:2%;color:#FFF;">COD.<br>ACE.</th>
                    <th style="width:2%;color:#FFF;">COD.<br>ACP.</th>
                    <th style="width:2%;color:#FFF;">COD.<br>OPE.</th>
                    <th style="width:2%; color:#FFF;">COD.<br>ACT.</th> 
                    <th style="width:11%; color:#FFF;">ACTIVIDAD</th>
                    <th style="width:11%; color:#FFF;">RESULTADO</th>
                    <th style="width:11%; color:#FFF;">INDICADOR</th>
                    <th style="width:2%; color:#FFF;">LB.</th>
                    <th style="width:2.5%; color:#FFF;">META</th>
                    <th style="width:2.5%; color:#FFF;">ENE.</th>
                    <th style="width:2.5%; color:#FFF;">FEB.</th>
                    <th style="width:2.5%; color:#FFF;">MAR.</th>
                    <th style="width:2.5%; color:#FFF;">ABR.</th>
                    <th style="width:2.5%; color:#FFF;">MAY.</th>
                    <th style="width:2.5%; color:#FFF;">JUN.</th>
                    <th style="width:2.5%; color:#FFF;">JUL.</th>
                    <th style="width:2.5%; color:#FFF;">AGO.</th>
                    <th style="width:2.5%; color:#FFF;">SEPT.</th>
                    <th style="width:2.5%; color:#FFF;">OCT.</th>
                    <th style="width:2.5%; color:#FFF;">NOV.</th>
                    <th style="width:2.5%; color:#FFF;">DIC.</th>
                    <th style="width:8.5%; color:#FFF;">VERIFICACI&Oacute;N</th> 
                    <th style="width:5%; color:#FFF;">PPTO.</th>   
                  </tr>
                </thead>
                <tbody>';
                $operaciones=$this->model_producto->list_operaciones_pi($com_id);  /// 2020
                $nro=0;
                foreach($operaciones as $rowp){
                  $nro++;
                  $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
                  $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
                  $tp='';
                  if($rowp['indi_id']==2){
                    $tp='%';
                  }

                  $color_or='';
                  if($rowp['or_id']==0){
                    $color_or='#fbd5d5';
                  }

                  $ptto=number_format(0, 2, '.', ',');
                  if(count($monto)!=0){
                    $ptto="<b>".number_format($monto[0]['total'], 2, ',', '.')."</b>";
                  }


                  $tabla.='
                  <tr>
                    <td style="height:12px;">'.$nro.'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['acc_codigo'].'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['og_codigo'].'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['or_codigo'].'</td>
                    <td style="width: 2%; text-align: center; font-size: 8px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
                    <td style="width: 11%; text-align: left;">'.$rowp['prod_producto'].'</td>
                    <td style="width: 11%; text-align: left;">'.$rowp['prod_resultado'].'</td>
                    <td style="width:11%; text-align: left;">'.$rowp['prod_indicador'].'</td>
                    <td style="width:2%; text-align: center;">'.round($rowp['prod_linea_base'],2).'</td>
                    <td style="width:2.5%; text-align: center;" bgcolor="#eceaea">'.round($rowp['prod_meta'],2).'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['enero'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['febrero'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['marzo'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['abril'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['mayo'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['junio'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['julio'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['agosto'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['septiembre'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['octubre'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['noviembre'],2).''.$tp.'</td>
                    <td style="width:2.5%;" align=center>'.round($rowp['diciembre'],2).''.$tp.'</td>
                    <td style="width:8.5%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                    <td style="width: 5%; text-align: right;">'.$ptto.'</td>
                  </tr>';            
                }
          $tabla.='
                </tbody>
              </table>';

      }
      else{ //// Gasto Corriente

         $tabla.='<table cellpadding="0" cellspacing="0" class="tabla" border=0.2 style="width:100%;" align=center>
                <thead>
                 <tr style="font-size: 7px;" bgcolor=#1c7368 align=center>
                    <th style="width:1%;height:15px;color:#FFF;">#</th>
                    <th style="width:2%;color:#FFF;">COD.<br>ACE.</th>
                    <th style="width:2%;color:#FFF;">COD.<br>ACP.</th>
                    <th style="width:2%;color:#FFF;">COD.<br>OPE.</th>
                    <th style="width:2%;color:#FFF;">COD.<br>ACT.</th> 
                    <th style="width:10%;color:#FFF;">ACTIVIDAD</th>
                    <th style="width:9.5%;color:#FFF;">RESULTADO</th>
                    <th style="width:7%;color:#FFF;">UNIDAD RESPONSABLE</th>
                    <th style="width:9%;color:#FFF;">INDICADOR</th>
                    <th style="width:2%;color:#FFF;">LB.</th>
                    <th style="width:3%;color:#FFF;">META</th>
                    <th style="width:3%;color:#FFF;">ENE.</th>
                    <th style="width:3%;color:#FFF;">FEB.</th>
                    <th style="width:3%;color:#FFF;">MAR.</th>
                    <th style="width:3%;color:#FFF;">ABR.</th>
                    <th style="width:3%;color:#FFF;">MAY.</th>
                    <th style="width:3%;color:#FFF;">JUN.</th>
                    <th style="width:3%;color:#FFF;">JUL.</th>
                    <th style="width:3%;color:#FFF;">AGO.</th>
                    <th style="width:3%;color:#FFF;">SEPT.</th>
                    <th style="width:3%;color:#FFF;">OCT.</th>
                    <th style="width:3%;color:#FFF;">NOV.</th>
                    <th style="width:3%;color:#FFF;">DIC.</th>
                    <th style="width:9%;color:#FFF;">VERIFICACI&Oacute;N</th> 
                    <th style="width:5%;color:#FFF;">PPTO.</th>   
                </tr>    
               
                </thead>
                <tbody>';
                $nro=0;
                $operaciones=$this->model_producto->lista_form4_x_unidadresponsable($com_id);
                
                foreach($operaciones as $rowp){
                  $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
                  $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
                  $programado=$this->model_producto->producto_programado($rowp['prod_id'],$this->gestion);
                  $color=''; $tp='';
                  if($rowp['indi_id']==1){
                    if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
                      $color='#fbd5d5';
                    }
                  }
                  elseif ($rowp['indi_id']==2) {
                    $tp='%';
                    if($rowp['mt_id']==3){
                      if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
                        $color='#fbd5d5';
                      }
                    }
                  }

                  $ptto=number_format(0, 2, '.', ',');
                  if(count($monto)!=0){
                    $ptto="<b>".number_format($monto[0]['total'], 2, ',', '.')."</b>";
                  }

                  $color_or='';
                  if($rowp['or_id']==0){
                    $color_or='#fbd5d5';
                  }

                  $nro++;
                  $tabla.=
                  '<tr style="font-size: 6.5px;" bgcolor="'.$color.'">
                    <td style="height:12px;" bgcolor='.$color_or.'>'.$nro.'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['acc_codigo'].'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['og_codigo'].'</td>
                    <td style="width: 2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['or_codigo'].'</td>
                    <td style="width: 2%; text-align: center; font-size: 8px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
                    <td style="width: 10%; text-align: left;font-size: 7px;">'.$rowp['prod_producto'].'</td>
                    <td style="width: 9.5%; text-align: left;">'.$rowp['prod_resultado'].'</td>
                    <td style="width: 7%; text-align: left;">'.strtoupper($rowp['prod_unidades']).'</td>
                    <td style="width: 9%; text-align: left;">'.$rowp['prod_indicador'].'</td>
                    <td style="width: 2%; text-align: center;">'.round($rowp['prod_linea_base'],2).'</td>
                    <td style="width: 3%; text-align: center;" bgcolor="#eceaea">'.round($rowp['prod_meta'],2).'</td>';

                    if(count($programado)!=0){
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['enero'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['febrero'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['marzo'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['abril'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['mayo'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['junio'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['julio'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['agosto'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['septiembre'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['octubre'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['noviembre'],2).''.$tp.'</td>';
                      $tabla.='<td style="width:3%;" bgcolor="#e5fde5" align=center>'.round($programado[0]['diciembre'],2).''.$tp.'</td>';
                    }
                    else{
                      for ($i=1; $i <=12 ; $i++) { 
                        $tabla.='<td bgcolor="#f5cace" align=center>0.00</td>';
                      }
                    }

                    $tabla.='
                    <td style="width: 9%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                    <td style="width: 5%; text-align: right;">'.$ptto.'</td>
                  </tr>';

                }
                $tabla.='
                </tbody>
              </table>';
      }
      return $tabla;
    }*/
  

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
    


/*    public function reporte_formulario4($com_id){
        $componente=$this->model_componente->get_componente($com_id,$this->gestion);
        if(count($componente)!=0){
            $proyecto = $this->model_proyecto->get_id_proyecto($componente[0]['proy_id']); //// DATOS PROYECTO
            $data['pie_rep']=$proyecto[0]['proy_nombre'].'-'.$componente[0]['serv_descripcion'];
            if($proyecto[0]['tp_id']==4){
                $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($componente[0]['proy_id']); /// PROYECTO
                $data['pie_rep']=$componente[0]['serv_descripcion'].'-'.$proyecto[0]['abrev'];
            }

            $data['cabecera']=$this->programacionpoa->cabecera($proyecto[0]['tp_id'],4,$proyecto,$com_id);
            $data['operaciones']=$this->programacionpoa->operaciones_form4($componente,$proyecto); /// Reporte Gasto Corriente, Proyecto de Inversion 2022
           
            $data['pie']=$this->programacionpoa->pie_form($proyecto);
            $this->load->view('admin/programacion/reportes/reporte_form4', $data);
        }
        else{
            echo "Error !!!";
        }
    }*/



   /*------- VALIDA ADICIONAR PRODUCTO POR MODIFICACION -------*/
     public function valida_add_producto(){
      if ($this->input->server('REQUEST_METHOD') === 'POST'){
          $this->form_validation->set_rules('proy_id', 'Proyecto Id', 'required|trim');
          $this->form_validation->set_rules('cite_id', 'Cite Id', 'required|trim');
          $this->form_validation->set_rules('com_id', 'Componente Id', 'required|trim');
          $this->form_validation->set_rules('prod', 'Producto', 'required|trim');

          $fase = $this->model_faseetapa->get_id_fase($this->input->post('proy_id'));
          for ($i=1; $i <=12 ; $i++) { 
            $m[$i]='m'.$i;
          }
          if ($this->form_validation->run()){

              /*---------------- Actualiza Ponderacion de productos -------------*/
                $list_prod=$this->model_producto->list_prod($this->input->post('com_id'));
                if(count($list_prod)!=0){
                  $pond=10;
                }
                else{
                 $pond=100; 
                }
                $ponderacion=$this->model_producto->suma_ponderacion($this->input->post('com_id'));
                foreach ($list_prod as $row) {
                  $update_prod = array(
                    'prod_ponderacion' => round((($row['prod_ponderacion']/100)*90),2), /// Ponderacion
                    'fun_id' => $this->session->userdata("fun_id")
                    );
                  $this->db->where('prod_id', $row['prod_id']);
                  $this->db->update('_productos', $update_prod);
                }
                /*-----------------------------------------------------------------*/

            /*------------------ Adiciona Producto -------------------*/
            $data_to_store = array(
              'com_id' => $this->input->post('com_id'),
              'prod_producto' => strtoupper($this->input->post('prod')),
              'indi_id' => $this->input->post('tipo_i'),
              'prod_indicador' => strtoupper($this->input->post('indicador')),
              'prod_formula' => strtoupper($this->input->post('formula')),
              'prod_linea_base' => $this->input->post('lb'),
              'prod_meta' => $this->input->post('met'),
              'prod_fuente_verificacion' => strtoupper($this->input->post('verificacion')), 
              'prod_supuestos' => strtoupper($this->input->post('supuestos')),
              'pt_id' => $this->input->post('p_t'),
              'prod_ponderacion' => $pond,
              'prod_total_casos' => strtoupper($this->input->post('c_a')),
              'prod_casos_favorables' => strtoupper($this->input->post('c_b')),
              'prod_denominador' => $this->input->post('den'),
              'fun_id' => $this->session->userdata("fun_id"),
              'prod_mod' => 2,
            );
              $this->db->insert('_productos', $data_to_store); 
            /*-------------------------------------------------------*/
            $prod_id=$this->db->insert_id();

            $gestion=$fase[0]['pfec_fecha_inicio'];
            if ( !empty($_POST["m1"]) && is_array($_POST["m1"]) ){
                foreach ( array_keys($_POST["m1"]) as $como ){
                  
                  for ($i=1; $i <=12 ; $i++) { 
                      if($_POST[$m[$i]][$como]!=0 || $_POST[$m[$i]][$como]!=''){
                        $this->model_producto->add_prod_gest($prod_id,$gestion,$i,$_POST[$m[$i]][$como]);
                      }
                  }
                $gestion++;        
              }
            }

            /*--------------------- iNSERT AUDI ADICIONAR PRODUCTOS -------------*/
              $data_to_store2 = array(
                'prod_id' => $prod_id, /// prod_id
                'ope_id' => $this->input->post('cite_id'), /// cite_id
                'num_ip' => $this->input->ip_address(), 
                'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
                'fun_id' => $this->session->userdata("fun_id"),
                );
              $this->db->insert('_producto_add', $data_to_store2);
              $proda_id=$this->db->insert_id();

              if(count($this->model_modificacion->get_add_producto($proda_id))==1){
                $this->session->set_flashdata('success','EL PRODUCTO SE AGREGO CORRECTAMENTE');
                redirect(site_url("admin").'/mod/proyecto_mod/'.$this->input->post('cite_id').'/'.$this->input->post('proy_id'));
              }
              else{
                $this->session->set_flashdata('danger','NO SE GUARDO CORRECTAMENTE, VERIFIQUE DATOS');
                redirect(site_url("admin").'/mod/proyecto_mod/'.$this->input->post('cite_id').'/'.$this->input->post('proy_id'));
              }
          }
          else{
            redirect('admin/mod/add_producto/'.$this->input->post('cite_id').'/'.$this->input->post('proy_id')."/".$this->input->post('com_id').'/error');
          }
      }
   }

    /*--------------- GENERA MENU -------------*/
    // public function genera_menu($proy_id){
    //   $id_f = $this->model_faseetapa->get_id_fase($proy_id);
    //   $enlaces=$this->menu_modelo->get_Modulos_programacion(2);
    //   $tabla='';
    //   $tabla.='
    //       <nav>
    //         <ul>
    //             <li>
    //                 <a href='.site_url("admin").'/dashboard'.' title="MENU PRINCIPAL"><i class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
    //             </li>
    //             <li class="text-center">
    //                 <a href='.base_url().'index.php/admin/proy/mis_proyectos/1'.' title="PROGRAMACI&Oacute;N POA"> <span class="menu-item-parent">PROGRAMACI&Oacute;N POA</span></a>
    //             </li>';
    //             if(count($id_f)!=0){
    //                 for($i=0;$i<count($enlaces);$i++){ 
    //                     $tabla.='
    //                     <li>
    //                         <a href="#" >
    //                             <i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>
    //                         <ul >';
    //                         $submenu= $this->menu_modelo->get_Modulos_sub($enlaces[$i]['o_child']);
    //                         foreach($submenu as $row) {
    //                            $tabla.='<li><a href='.base_url($row['o_url'])."/".$id_f[0]['proy_id'].'>'.$row['o_titulo'].'</a></li>';
    //                         }
    //                     $tabla.='</ul>
    //                     </li>';
    //                 }
    //             }
    //         $tabla.='
    //         </ul>
    //       </nav>';

    //   return $tabla;
    // }

}