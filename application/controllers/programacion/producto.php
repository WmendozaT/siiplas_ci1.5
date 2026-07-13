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

  /*------- LISTA DE FORM 4 (a optimizar)----------*/
    public function lista_productos($com_id){
      $data['componente'] = $this->model_componente->get_componente($com_id,$this->gestion);
      $data['stylo']=$this->programacionpoa->estilo_tabla_form4();

      if(count($data['componente'])!=0){
          $form4=$this->model_producto->lista_form4_x_unidadresponsable($com_id);

          $proy_id=$data['componente'][0]['proy_id'];
          $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
         
          if($data['proyecto'][0]['tp_id']==1){
            $list_oregional=$this->model_objetivoregion->get_unidad_pregional_programado($proy_id);
            $data['datos_proyecto']='<h2>'.$data['proyecto'][0]['proy_sisin'].' - '.$data['proyecto'][0]['proy_nombre'].'</h2>';
          }
          else{
            $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
            $data['datos_proyecto']='<h2>'.$data['proyecto'][0]['aper_programa'].' '.$data['proyecto'][0]['aper_proyecto'].' '.$data['proyecto'][0]['aper_actividad'].' - '.$data['proyecto'][0]['tipo'].' '.$data['proyecto'][0]['act_descripcion'].' - '.$data['proyecto'][0]['abrev'].'  / '.$data['componente'][0]['serv_cod'].' '.$data['componente'][0]['tipo_subactividad'].' '.$data['componente'][0]['serv_descripcion'].'</h2>';
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
                    <a href="#" data-toggle="modal" data-target="#modal_importar_ff" class="btn btn-default importar_ff" name="2" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO (FORM 5.CSV)</b>
                    </a>
                    <a href="#" data-toggle="modal" data-target="#modal_ver_form5" class="btn btn-default ver_requerimientos" name="'.$com_id.'" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>VER MIS REQUERIMIENTOS</b>
                    </a>
                    <a href="javascript:abreVentana(\''.site_url("").'/prog/rep_operacion_componente/'.$com_id.'\');" class="btn btn-primary" title="REPORTE FORM. 4"> <img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>REPORTE FORM N 4</a>
                    <a onclick="eliminar_form4_todos()" class="btn btn-danger"  title="Eliminar Actividades de la unidad (todos)"><img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 4 (TODOS)</a>
                    <a onclick="eliminar_requerimientos_servicio()" class="btn btn-danger"  title="Eliminar Requerimientos de la unidad (todos)"><img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>ELIMINAR FORM 5 (TODOS)</a>';
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
                      // Si el tipo de meta es Recurrente (1) o Trimestral (5), se queda 'disabled' y gris en $bg_meta
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
                                <i class="fa fa-upload text-primary"></i> Importar Consolidado Actividades
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
                                <img src="' . base_url('assets/img/img_migracion/migracion_form4_unidad.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                            </div>

                            <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                            <form action="' . site_url('programacion/componente/valida_migracion_form4_consolidado') . '" method="post" enctype="multipart/form-data" id="form_subir_sigep" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="proy_id" value="'.$data['componente'][0]['proy_id'].'" type="hidden" > 
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
            // ==========================================================================
            // RAMA B: ACTUALIZACIÓN DE CRONOGRAMA MENSUAL (prod_programado_mensual)
            // ==========================================================================
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


      /* public function guardar_campo_form4_en_caliente() {
        // Validamos que sea una petición asíncrona legítima de JQuery
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // 1. Captura y sanitización perimetral de variables del SIIPLAS
            $prod_id    = intval($this->input->post('prod_id'));
            $campo_raw  = trim($this->input->post('campo'));
            $valor_raw  = trim($this->input->post('valor'));
            $this->gestion = intval($this->gestion); // Gestión activa (ej: 2027)

            if ($prod_id <= 0 || empty($campo_raw)) {
                echo json_encode(array('status' => 'error', 'message' => 'Parámetros inconsistentes o ID de producto inválido.'));
                return;
            }

            // 2. DEDUCCIÓN DE CONTEXTO: ¿Es un campo maestro de texto o una celda mensual?
            // Si el campo empieza con la letra 'm' seguida de un número (ej: m1, m2... m12) es Temporalidad
            $tp = 0; 
            $mes_id = 0;
            if (preg_match('/^m([1-9]|1[0-2])$/', $campo_raw, $coincidencias)) {
                $tp = 1;          // Conmutador a modo Temporalidad Mensual
                $mes_id = intval($coincidencias[1]); // Extraemos el ID del mes (1 al 12)
            }

            // Inicialización limpia de variables de retorno para JQuery
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

                // Lista blanca de columnas autorizadas para mitigar inyecciones maliciosas
                $columnas_autorizadas = array('prod_cod', 'or_id', 'prod_producto', 'prod_resultado', 'uni_resp', 'prod_unidades', 'indi_id', 'prod_indicador', 'prod_fuente_verificacion', 'prod_meta', 'mt_id');
                if (!in_array($campo, $columnas_autorizadas)) {
                    echo json_encode(array('status' => 'error', 'message' => 'Columna no autorizada para persistencia.'));
                    return;
                }

                // Saneamiento de tipado según el DDL de PostgreSQL
                if ($campo == 'prod_cod' || $campo == 'or_id' || $campo == 'indi_id' || $campo == 'mt_id') {
                    $detalle = ($valor_raw === '') ? 0 : intval($valor_raw);
                } elseif ($campo == 'prod_meta' || $campo == 'uni_resp') {
                    $detalle = ($valor_raw === '') ? 0.00 : floatval($valor_raw);
                } else {
                    $detalle = strtoupper($this->security->xss_clean($valor_raw));
                }

                // Ejecutamos la actualización directa en tu tabla de productos
                $update_form4 = array(
                    $campo   => $detalle,
                    'num_ip' => $this->input->ip_address(),
                    'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                );
                
                $this->db->where('prod_id', $prod_id);
                $this->db->update('_productos', $update_form4);

                // Recuperamos el valor guardado para confirmación
                $producto_actualizado = $this->model_producto->get_producto_id($prod_id);
                $informacion = $producto_actualizado[0][$campo];

                // Conservamos tu regla de negocio: Si el indicador es de tipo absoluto (1), recalculamos meta
                if ($producto_actualizado[0]['indi_id'] == 1) {
                    $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $this->gestion);
                    if (!empty($suma_temp)) {
                        $sum_meta = round($suma_temp[0]['prog'], 2);
                    }
                } else {
                    $sum_meta = round($producto_actualizado[0]['prod_meta'], 2);
                }
            }
            // ==========================================================================
            // RAMA B: ACTUALIZACIÓN DE CRONOGRAMA MENSUAL (prod_programado_mensual)
            // ==========================================================================
            else { 
                $detalle = floatval($valor_raw);

                // --- Se conserva tu lógica estricta de borrado preventivo del mes ---
                $this->db->where('m_id', $mes_id);
                $this->db->where('prod_id', $prod_id);
                $this->db->delete('prod_programado_mensual');
                // --------------------------------------------------------------------

                if ($detalle != 0) {
                    // --- Inserción limpia del valor físico mensual en tu tabla real ---
                    $data_to_store = array( 
                        'prod_id' => $prod_id, 
                        'm_id'    => $mes_id, 
                        'pg_fis'  => $detalle,
                        'pg_fin'  => 0.00, // Inicializado por defecto
                        'g_id'    => $this->gestion, 
                    );
                    $this->db->insert('prod_programado_mensual', $data_to_store);
                }

                // Recuperamos la temporalidad registrada para feedback
                $temp_prod = $this->model_producto->get_mes_programado_form4($prod_id, $mes_id);
                if (!empty($temp_prod)) {
                    $informacion = $temp_prod[0]['pg_fis'];
                }

                // 🌟 LÓGICA CORE RESTABLECIDA: Re-cálculo instantáneo de Meta Anual si indi_id == 1
               // if ($producto[0]['indi_id'] == 1) {
                    $suma_temp = $this->model_producto->suma_programado_producto($prod_id, $this->gestion);
                    if (!empty($suma_temp)) {
                        $sum_meta = round($suma_temp[0]['prog'], 2);
                    }
                    
                    // Actualizamos la cabecera del maestro con la sumatoria física real del lote
                    $update_tempform4 = array(
                        'prod_meta' => $sum_meta
                    );
                    $this->db->where('prod_id', $prod_id);
                    $this->db->update('_productos', $update_tempform4);
                //} else {
                  //  $sum_meta = round($producto[0]['prod_meta'], 2);
               // }
            }

            // 3. RESPUESTA UNIFICADA: Mantiene tus índices originales 'respuesta', 'update_informacion' y 'update_meta'
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
    }*/


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
























//////

    /*---- GET DATOS PRODUCTO FORM 4 ----*/
/*    public function get_producto(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $prod_id = $this->security->xss_clean($post['prod_id']);
        $producto=$this->model_producto->get_producto_id($prod_id); /// Get producto
        $componente = $this->model_componente->get_componente($producto[0]['com_id'],$this->gestion);
        $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']);

        $temporalidad=$this->model_producto->producto_programado($prod_id,$this->gestion); /// Temporalidad
        
        $prioridad='';
        $prioridad.='<section class="col col-2">
                      <label class="label"><b style="color:blue">ACTIVIDAD CON PRIORIDAD ?</b></label>
                      <select class="form-control" id="priori" name="priori" title="ACTIVIDAD PRIORITARIO">';
                        if($producto[0]['prod_priori']==1){
                          $prioridad.='
                          <option value="1" selected>SI</option>
                          <option value="0">NO</option>';
                        }
                        else{
                          $prioridad.='
                          <option value="1">SI</option>
                          <option value="0" selected>NO</option>';
                        }
                      $prioridad.='      
                      </select>
                    </section>';


        $sum_temp=0;
        $sum=$this->model_producto->meta_prod_gest($prod_id);
        if(count($sum)!=0){
          $sum_temp=$sum[0]['meta_gest'];
        }

        if(count($temporalidad)!=0){
          for ($i=1; $i <=12 ; $i++) { 
            $this->prog_mes[$i]= $temporalidad[0][$this->temp[$i]];
          }
        }

        $uresponsable='';
        if($proyecto[0]['por_id']==1){
          $unidades=$this->model_producto->list_uresponsables_regional($proyecto[0]['dist_id']);
          $uresponsable.='
              <section class="col col-4">
                <label class="label"><b>UNIDAD RESPONSABLE</b></label>
                <select class="form-control" id="um_resp" name="um_resp" title="SELECCIONE UNIDAD RESPONSABLE">
                  <option value="">Seleccione Unidad Responsable</option>';
                  foreach($unidades as $row){
                    if($row['com_id']==$producto[0]['uni_resp']){
                      $uresponsable.='<option value="'.$row['com_id'].'" selected>'.$row['tipo'].' '.$row['actividad'].'-'.$row['abrev'].' -> '.$row['tipo_subactividad'].' '.$row['serv_descripcion'].'</option>';
                    }
                    else{
                      $uresponsable.='<option value="'.$row['com_id'].'" >'.$row['tipo'].' '.$row['actividad'].'-'.$row['abrev'].' -> '.$row['tipo_subactividad'].' '.$row['serv_descripcion'].'</option>';
                    }
                  }       
                $uresponsable.='
                </select>
              </section>';
        }
        else{
          $uresponsable.='
                <input type="text" name="um_resp" id="um_resp" value="0">
                <section class="col col-4">
                  <label class="label"><b>UNIDAD / SERVICIO RESPONSABLE</b></label>
                  <label class="textarea">
                    <i class="icon-append fa fa-tag"></i>
                    <textarea rows="2" name="munidad" id="munidad" title="REGISTRE UNIDAD RESPONSABLE">'.$producto[0]['prod_unidades'].'</textarea>
                  </label>
                </section>';
        }



        if(count($producto)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'producto'=>$producto,
            'uresponsable'=>$uresponsable,
            'temp'=>$this->prog_mes,
            'sum_temp'=>$sum_temp,
            'prioridad'=>$prioridad,
          );
        }
        else{
          $result = array(
            'respuesta' => 'error',
          );
        }

        echo json_encode($result);
      }else{
          show_404();
      }
    }*/



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


    /*--- ELIMINAR TOD@S LAS ACTIVIDADES REQUERIMIENTOS DE LA UNIDAD (2025) ---*/
    public function delete_form4_form5($com_id){
      $form4=$this->model_producto->lista_form4_x_unidadresponsable($com_id); /// form 4
      foreach($form4 as $rowp){
        $insumos=$this->model_insumo->lista_insumos_prod($rowp['prod_id']); /// form 5
        foreach ($insumos as $rowi) {
          
          /*--------- delete temporalidad --------*/
           $this->db->where('ins_id', $rowi['ins_id']);
           $this->db->delete('temporalidad_prog_insumo');

           $this->db->where('ins_id', $rowi['ins_id']);
           $this->db->delete('_insumoproducto');

          /*--------- delete form 5 --------*/
           $this->db->where('ins_id', $rowi['ins_id']);
           $this->db->delete('insumos');

        }

        /*------ delete form 4 -----*/
          $this->db->where('prod_id', $rowp['prod_id']);
          $this->db->delete('prod_programado_mensual');

        /*------ delete form 4 -----*/
          $this->db->where('prod_id', $rowp['prod_id']);
          $this->db->delete('_productos');
      }

        ///-----------------
        $this->session->set_flashdata('success','SE ELIMINO CORRECTAMENTE ');
        redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
    }


    /*--- ELIMINAR TOD@S LOS REQUERIMIENTOS DEL SERVICIO (SOLO REQUERIMIENTOS) (2025) ---*/
    public function delete_insumos_servicios($com_id){
    //  $productos = $this->model_producto->list_producto_programado($com_id,$this->gestion); // Lista de productos
      $productos=$this->model_producto->lista_form4_x_unidadresponsable($com_id);
      $nro=0;$nro_ins=0;
      //echo "eliminar productos";
      foreach($productos as $rowp){
        $insumos=$this->model_insumo->lista_insumos_prod($rowp['prod_id']);
        foreach ($insumos as $rowi) {
          /*--------- delete temporalidad --------*/
          $this->db->where('ins_id', $rowi['ins_id']);
          $this->db->delete('temporalidad_prog_insumo');

          $this->db->where('ins_id', $rowi['ins_id']);
          $this->db->delete('_insumoproducto');

          /*--------- delete Insumos --------*/
          $this->db->where('ins_id', $rowi['ins_id']);
          $this->db->delete('insumos');

          if(count($this->model_insumo->get_insumo_producto($rowi['ins_id']))==0){
            $nro_ins++;
          }
        }
      }

      $update_prod= array(
        'fun_id' => $this->fun_id,
        'prod_ppto' => 1
      );
      $this->db->where('com_id', $com_id);
      $this->db->update('_productos', $update_prod);


      $this->session->set_flashdata('success','SE ELIMINO CORRECTAMENTE '.$nro_ins.' REQUERIMIENTOS DE LA UNIDAD ');
      redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
    }






    /*--- ELIMINAR LISTA TOTAL DE REQUERIMEITNOS POR UNIDAD*/
    public function delete_list_requerimientos($aper_id){
      $insumos=$this->model_insumo->insumos_por_unidad($aper_id);
      $nro_ins=0;
      foreach ($insumos as $rowi) {
        /*--------- delete temporalidad --------*/
        $this->db->where('ins_id', $rowi['ins_id']);
        $this->db->delete('temporalidad_prog_insumo');

        $this->db->where('ins_id', $rowi['ins_id']);
        $this->db->delete('_insumoproducto');

        /*--------- delete Insumos --------*/
        $this->db->where('ins_id', $rowi['ins_id']);
        $this->db->delete('insumos');

        if(count($this->model_insumo->get_insumo_producto($rowi['ins_id']))==0){
          $nro_ins++;
        }
      }

      return $nro_ins;
    }

    /*----------------- LISTA OPERACIONES PI (2020) ------------------*/
    // public function operaciones_pi($proy_id,$com_id){
    //   $tabla ='';
    //   $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); 
    //   $fase = $this->model_faseetapa->get_id_fase($proy_id); //// recupera datos de la tabla fase activa
    //   $productos = $this->model_producto->list_operaciones_pi($com_id); // Lista de Operaciones
    //    $tabla .='<thead>
    //               <tr class="modo1">
    //                 <th style="width:1%; text-align=center"><b>#</b></th>
    //                 <th style="width:1%; text-align=center"><b>E/B</b></th>
    //                 <th style="width:2%;"><b>COD. OR.</b></th>
    //                 <th style="width:2%;"><b>COD. OPE.</b></th>
    //                 <th style="width:15%;"><b>OPERACI&Oacute;N</b></th>
    //                 <th style="width:15%;"><b>RESULTADO</b></th>
    //                 <th style="width:10%;"><b>TIP. IND.</b></th>
    //                 <th style="width:10%;"><b>INDICADOR</b></th>
    //                 <th style="width:1%;"><b>LINEA BASE '.($this->gestion-1).'</b></th>
    //                 <th style="width:1%;"><b>META</b></th>
    //                 <th style="width:4%;"><b>ENE.</b></th>
    //                 <th style="width:4%;"><b>FEB.</b></th>
    //                 <th style="width:4%;"><b>MAR.</b></th>
    //                 <th style="width:4%;"><b>ABR.</b></th>
    //                 <th style="width:4%;"><b>MAY.</b></th>
    //                 <th style="width:4%;"><b>JUN.</b></th>
    //                 <th style="width:4%;"><b>JUL.</b></th>
    //                 <th style="width:4%;"><b>AGO.</b></th>
    //                 <th style="width:4%;"><b>SEP.</b></th>
    //                 <th style="width:4%;"><b>OCT.</b></th>
    //                 <th style="width:4%;"><b>NOV.</b></th>
    //                 <th style="width:4%;"><b>DIC.</b></th>
    //                 <th style="width:10%;"><b>MEDIO DE VERIFICACI&Oacute;N</b></th>
    //               </tr>
    //             </thead>
    //             <tbody>';

    //     $cont = 0;
    //     foreach($productos as $rowp){
    //       $cont++;
    //       $tabla .='<tr class="modo1">';
    //         $tabla.='<td>'.$cont.'</td>';
            
    //         $tabla.='<td align="center">';
    //         $tabla.='<a href="'.site_url("admin").'/prog/mod_prod/'.$rowp['prod_id'].'" title="MODIFICAR OPERACI&Oacute;N" class="btn btn-default"><img src="'.base_url().'assets/ifinal/modificar.png" WIDTH="33" HEIGHT="34"/></a><br>
    //                  <a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default del_ff" title="ELIMINAR OPERACI&Oacute;N"  name="'.$rowp['prod_id'].'" ><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a><br>
    //                  <a href="'.site_url("").'/prog/requerimiento/'.$proy_id.'/'.$rowp['prod_id'].'" target="_blank" title="REQUERIMIENTOS DE LA ACTIVIDAD" class="btn btn-default"><img src="'.base_url().'assets/ifinal/insumo.png" WIDTH="33" HEIGHT="33"/></a><br>
    //                 </td>';
    //         $tabla.='<td style="width:2%;text-align=center"><b><font size=5 color=blue>'.$rowp['or_codigo'].'</font></b></td>';
    //         $tabla.='<td style="width:2%;text-align=center"><b><font size=5>'.$rowp['prod_cod'].'</font></b></td>';
    //         $tabla.='<td>'.$rowp['prod_producto'].'</td>';
    //         $tabla.='<td>'.$rowp['prod_resultado'].'</td>';
    //         $tabla.='<td>'.$rowp['indi_descripcion'].'</td>';
    //         $tabla.='<td>'.$rowp['prod_indicador'].'</td>';
    //         $tabla.='<td>'.$rowp['prod_linea_base'].'</td>';
    //         $tabla.='<td>'.$rowp['prod_meta'].'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['enero'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['febrero'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['marzo'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['abril'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['mayo'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['junio'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['julio'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['agosto'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['septiembre'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['octubre'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['noviembre'],2).'</td>';
    //         $tabla.='<td style="width:4%;" bgcolor="#e5fde5">'.round($rowp['diciembre'],2).'</td>';
    //         $tabla.='<td>'.$rowp['prod_fuente_verificacion'].'</td>';
    //       $tabla .='</tr>';
    //     }
    //     $tabla.='</tbody>';
    //   return $tabla;
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
       



  /*------------- COMBO OBJETIVO ESTRATEGICO -----------------*/
    // public function combo_acciones_estrategicos(){
    //   $salida = "";
    //   $id_pais = $_POST["elegido"];
    //   // construimos el combo de ciudades deacuerdo al pais seleccionado
    //   $combog = pg_query('select *
    //                       from _acciones_estrategicas
    //                       where obj_id='.$id_pais.' and acc_estado!=3
    //                       order by acc_id asc');
    //   $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE ACCI&Oacute;N ESTRATEGICA', 'cp1252', 'UTF-8') . "</option>";
    //   while ($sql_p = pg_fetch_row($combog)) {
    //       $salida .= "<option value='" . $sql_p[0] . "'>" .$sql_p[2].".- ".$sql_p[3] . "</option>";
    //   }
    //   echo $salida;
    // }



        /*------- GET ACCIONES ESTRATEGICAS -------*/
    // public function get_acciones_estrategicas(){
    //   if($this->input->is_ajax_request() && $this->input->post()){
    //     $post = $this->input->post();
    //     $obj_id = $this->security->xss_clean($post['obj_id']); /// Obj id

    //     $salida='';
    //     $combog = pg_query('select *
    //                       from _acciones_estrategicas
    //                       where obj_id='.$obj_id.' and acc_estado!=3
    //                       order by acc_id asc');
    //   $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE ACCI&Oacute;N ESTRATEGICA', 'cp1252', 'UTF-8') . "</option>";
    //   while ($sql_p = pg_fetch_row($combog)) {
    //       $salida .= "<option value='" . $sql_p[0] . "'>" .$sql_p[2].".- ".$sql_p[3] . "</option>";
    //   }


    //     $result = array(
    //         'respuesta' => 'correcto',
    //         'salida' => $salida,
    //       );
          
    //     echo json_encode($result);
    //   }else{
    //       show_404();
    //   }
    // }





  /*--- ACTUALIZA CODIGO DE ACTIVIDAD (FORM 4) ----*/
  // public function update_codigo($com_id){  
  //   $this->programacionpoa->update_codigo_actividad($com_id);
  //   redirect(site_url("").'/admin/prog/list_prod/'.$com_id);
  // }


  /*--- Verifica Codigo Operacion (vigente) ---*/ 
/*   function verif_codigo(){
     if($this->input->is_ajax_request()){
         $post = $this->input->post();

         $codigo= $this->security->xss_clean($post['codigo']); /// Codigo
         $com_id= $this->security->xss_clean($post['com_id']); /// Componente id

         $verif_com_ope=$this->model_producto->verif_componente_operacion($com_id,$codigo);
         if(count($verif_com_ope)!=0){
           echo "true"; ///// no existe un CI registrado
         }
         else{
           echo "false"; //// existe el CI ya registrado
         } 
     }else{
       show_404();
     }
   }
*/
  
   
 /*------ ELIMINA EL PRODUCTO Y SUS REQUERIMIENTOS ------*/
    function desactiva_producto(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $prod_id = $this->security->xss_clean($post['prod_id']); /// prod id
          $insumos = $this->model_producto->insumo_producto($prod_id); /// Insumo del producto

          foreach ($insumos as $rowi) {
            /*--------- delete temporalidad --------*/
            $this->db->where('ins_id', $rowi['ins_id']);
            $this->db->delete('temporalidad_prog_insumo');

            $this->db->where('prod_id', $prod_id);
            $this->db->where('ins_id', $rowi['ins_id']);
            $this->db->delete('_insumoproducto');

            /*--------- delete Insumos --------*/
            $this->db->where('ins_id', $rowi['ins_id']);
            $this->db->delete('insumos');
          }

          /*------ delete Productos -----*/
            $this->db->where('prod_id', $prod_id);
            $this->db->delete('prod_programado_mensual');

          /*------ delete Productos -----*/
            $this->db->where('prod_id', $prod_id);
            $this->db->delete('_productos');

          $prod=$this->model_producto->get_producto_id($prod_id);
          if(count($prod)==0){
            $result = array(
              'respuesta' => 'correcto'
            );
          }
          else{
            $result = array(
              'respuesta' => 'error'
            );
          }


       
          echo json_encode($result);
      } else {
          echo 'DATOS ERRONEOS';
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
    public function componente_operaciones($com_id){
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
    }




    /*--- MIGRACION DEL FORMULARIO N 4 (2020-2022) Y REQUERIMIENTOS (2025) ---*/
    // function importar_operaciones_requerimientos(){
    //   if ($this->input->post()) {
    //       $post = $this->input->post();
    //       $com_id = $this->security->xss_clean($post['com_id']); /// com id
    //       $componente = $this->model_componente->get_componente_pi($com_id);
    //       $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']);
    //       $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']);
    //       $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($fase[0]['proy_id']); /// Lista de Objetivos Regionales
    //       $tp = $this->security->xss_clean($post['tp']); /// tipo de migracion

    //       $tipo = $_FILES['archivo']['type'];
    //       $tamanio = $_FILES['archivo']['size'];
    //       $archivotmp = $_FILES['archivo']['tmp_name'];

    //       $filename = $_FILES["archivo"]["name"];
    //       $file_basename = substr($filename, 0, strripos($filename, '.'));
    //       $file_ext = substr($filename, strripos($filename, '.'));
    //       $allowed_file_types = array('.csv');

    //       if (in_array($file_ext, $allowed_file_types) && ($tamanio < 90000000)) {
    //         /*------------------- Migrando ---------------*/
    //         $lineas = file($archivotmp);
    //         $i=0;
    //         $nro=0;
    //         $guardado=0;
    //         $no_guardado=0;
  
    //         if($tp==1){  /// Formulario N 4
    //           foreach ($lineas as $linea_num => $linea){ 
    //             if($i != 0){
    //               $datos = explode(";",$linea);
    //               if(count($datos)==22){

    //                 $cod_og = intval(trim($datos[0])); // Codigo ACP
    //                 $cod_or = intval(trim($datos[1])); // Codigo Operacion
    //                 $cod_form4 = intval(trim($datos[2])); // Codigo Form 4
    //                 $descripcion = strval(utf8_encode(trim($datos[3]))); //// descripcion form4
    //                 $resultado = strval(utf8_encode(trim($datos[4]))); //// descripcion Resultado
    //                 $unidad = strval(utf8_encode(trim($datos[5]))); //// Unidad responsable
    //                 //$unidad = intval(trim($datos[5])); //// id Unidad responsable PRG Bolsas
    //                 $indicador = strval(utf8_encode(trim($datos[6]))); //// descripcion Indicador
    //                 $lbase = intval(trim($datos[7])); //// Linea Base
    //                 $meta = intval(trim($datos[8])); //// Meta
    //                 $mverificacion = strval(utf8_encode(trim($datos[21]))); //// Medio de verificacion

    //                 $or_id=0;
    //                 if(count($list_oregional)!=0){
    //                   $get_acc=$this->model_objetivoregion->get_alineacion_proyecto_oregional($fase[0]['proy_id'],$cod_og,$cod_or);
    //                   if(count($get_acc)!=0){
    //                     $or_id=$get_acc[0]['or_id'];
    //                   }
    //                 }

    //                 if(strlen($descripcion)!=0 & strlen($resultado)!=0){
    //                     $query=$this->db->query('set datestyle to DMY');
    //                     $data_to_store = array(
    //                       'com_id' => $com_id,
    //                       'prod_cod'=>$cod_form4,
    //                       'prod_producto' => strtoupper($descripcion),
    //                       'prod_resultado' => strtoupper($resultado),
    //                       'indi_id' => 1,
    //                       'prod_indicador' => strtoupper($indicador),
    //                       'prod_fuente_verificacion' => strtoupper($mverificacion), 
    //                       'prod_linea_base' => $lbase,
    //                       'prod_meta' => $meta,
    //                       'uni_resp' => 0, //// para prog bolsas
    //                       'prod_unidades' => $unidad,
    //                       'acc_id' => 0,
    //                       'prod_ppto' => 1,
    //                       'fecha' => date("d/m/Y H:i:s"),
    //                       'or_id'=>$or_id,
    //                       'fun_id' => $this->fun_id,
    //                       'num_ip' => $this->input->ip_address(), 
    //                       'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
    //                     );
    //                     $this->db->insert('_productos', $data_to_store);
    //                     $prod_id=$this->db->insert_id(); 


    //                     $var=9;
    //                     for ($i=1; $i <=12 ; $i++) {
    //                       $m[$i]=floatval(trim($datos[$var])); //// Mes i
    //                       if($m[$i]!=0){
    //                         if(strlen($m[$i])<=4){
    //                           $this->model_producto->add_prod_gest($prod_id,$this->gestion,$i,$m[$i]);
    //                         }
    //                       }
                          
    //                       $var++;
    //                     }

    //                     $producto=$this->model_producto->get_producto_id($prod_id);
    //                     if(count($producto)!=0){
    //                       $guardado++;
    //                     }
    //                     else{
    //                       $no_guardado++;
    //                     }
    //                 }

    //               }
    //             }
    //             $i++;
    //           }
              
    //           //// Actualizando Codigos
    //           $this->programacionpoa->update_codigo_actividad($com_id);
    //           $this->session->set_flashdata('success','SE REGISTRARON '.$guardado.' ACTIVIDADES');
    //         }
    //         else{ /// Requerimientos

    //         foreach ($lineas as $linea_num => $linea){
    //           if($i != 0){
    //             $datos = explode(";",$linea);
             
    //             if(count($datos)==20){
    //                 //echo count($datos).'<br>';
    //                 $prod_cod = intval(trim($datos[0])); //// Codigo Actividad
    //                 $cod_partida = intval(trim($datos[1])); //// Codigo partida
    //                 $par_id = $this->model_insumo->get_partida_codigo($cod_partida); //// DATOS DE LA FASE ACTIVA

    //                 $detalle = strval(utf8_encode(trim($datos[2]))); //// descripcion form5
    //                 $unidad = strval(utf8_encode(trim($datos[3]))); //// Unidad
    //                 $cantidad = intval(trim($datos[4])); //// Cantidad
    //                 $unitario = floatval(trim($datos[5])); //// Costo Unitario
                    
    //                 $p_total=($cantidad*$unitario);
    //                 $total = floatval(trim($datos[6])); //// Costo Total

    //                 $var=7; $sum_temp=0;
    //                 for ($i=1; $i <=12 ; $i++) {
    //                   $m[$i]=floatval(trim($datos[$var])); //// Mes i
    //                   if($m[$i]==''){
    //                     $m[$i]=0;
    //                   }
    //                   $var++;
    //                   $sum_temp=$sum_temp+$m[$i];
    //                 }

    //                 $observacion = strval(utf8_encode(trim($datos[19]))); //// Observacion
    //                 $verif_cod=$this->model_producto->verif_componente_operacion($com_id,$prod_cod);
    //                // echo count($verif_cod).'--'.count($par_id).'--'.$cod_partida.'--'.round($sum_temp,2).'=='.round($total,2)."<br>";

    //                 if(count($verif_cod)!=0 & count($par_id)!=0 & $cod_partida!=0 & round($sum_temp,2)==round($total,2) & round($p_total,2)==round($total,2)){ /// Verificando si existe Codigo de Actividad, par id, Codigo producto
    //                     $producto=$this->model_producto->get_producto_id($verif_cod[0]['prod_id']); /// Get producto
    //                     $guardado++;
    //                     //echo $guardado.'---'.$detalle.'<br>';
    //                     /*-------- INSERTAR DATOS REQUERIMIENTO ---------*/
    //                     $query=$this->db->query('set datestyle to DMY');
    //                     $data_to_store = array( 
    //                     'ins_codigo' => $this->session->userdata("name").'/REQ/'.$this->gestion, /// Codigo Insumo
    //                     'ins_fecha_requerimiento' => date('d/m/Y'), /// Fecha de Requerimiento
    //                     'ins_detalle' => strtoupper($detalle), /// Insumo Detalle
    //                     'ins_cant_requerida' => round($cantidad,0), /// Cantidad Requerida
    //                     'ins_costo_unitario' => $unitario, /// Costo Unitario
    //                     'ins_costo_total' => $total, /// Costo Total
    //                     'ins_unidad_medida' => $unidad, /// Unidad de Medida
    //                     'ins_gestion' => $this->gestion, /// Insumo gestion
    //                     'par_id' => $par_id[0]['par_id'], /// Partidas
    //                     'ins_tipo' => 1, /// Ins Tipo
    //                     'ins_observacion' => strtoupper($observacion), /// Observacion
    //                     'fun_id' => $this->fun_id, /// Funcionario
    //                     'aper_id' => $proyecto[0]['aper_id'], /// aper id
    //                     'com_id' => $producto[0]['com_id'], /// com id 
    //                     'form4_cod' => $producto[0]['prod_cod'], /// aper id
    //                     'num_ip' => $this->input->ip_address(), 
    //                     'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
    //                     );
    //                     $this->db->insert('insumos', $data_to_store); ///// Guardar en Tabla Insumos 
    //                     $ins_id=$this->db->insert_id();

    //                     /*--------------------------------------------------------*/
    //                       $data_to_store2 = array( ///// Tabla InsumoProducto
    //                         'prod_id' => $verif_cod[0]['prod_id'], /// prod id
    //                         'ins_id' => $ins_id, /// ins_id
    //                       );
    //                       $this->db->insert('_insumoproducto', $data_to_store2);
    //                     /*----------------------------------------------------------*/

    //                     for ($p=1; $p <=12 ; $p++) { 
    //                       if($m[$p]!=0 & is_numeric($unitario)){
    //                         $data_to_store4 = array(
    //                           'ins_id' => $ins_id, /// Id Insumo
    //                           'mes_id' => $p, /// Mes 
    //                           'ipm_fis' => $m[$p], /// Valor mes
    //                           'g_id' => $this->gestion, /// Gestion
    //                         );
    //                         $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
    //                       }
    //                     }
    //                 }
               
    //             } /// end dimension (20)
    //           } /// i!=0

    //           $i++;

    //         }

    //           $this->session->set_flashdata('success','SE REGISTRARON '.$guardado.' REQUERIMIENTOS');
    //         } /// end else

    //         redirect('admin/prog/list_prod/'.$com_id.'');
    //       }
    //       else{
    //         $this->session->set_flashdata('danger','SELECCIONE ARCHIVO ');
    //         redirect('admin/prog/list_prod/'.$com_id.'');
    //       }
    //   }
    //   else{
    //     echo "Error !!";
    //   }
    // }

    /*------ ACTUALIZA PRESUPUESTO EXISTENTE DE LAS OPERACIONES -------*/
    // public function update_ptto_operaciones($com_id){
    //   //$operaciones=$this->model_producto->list_producto_programado($com_id,$this->gestion);
    //   $operaciones=$this->model_producto->lista_form4_x_unidadresponsable($com_id);
    //   foreach($operaciones as $rowp){
    //     $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
    //     if(count($monto)==0){
    //       $update_act= array(
    //         'prod_ppto' => 0,
    //         'fun_id' => $this->fun_id
    //       );
    //       $this->db->where('prod_id', $rowp['prod_id']);
    //       $this->db->update('_productos', $update_act);
    //     }
    //   }
    // }    

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
    public function genera_menu($proy_id){
      $id_f = $this->model_faseetapa->get_id_fase($proy_id);
      $enlaces=$this->menu_modelo->get_Modulos_programacion(2);
      $tabla='';
      $tabla.='
          <nav>
            <ul>
                <li>
                    <a href='.site_url("admin").'/dashboard'.' title="MENU PRINCIPAL"><i class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
                </li>
                <li class="text-center">
                    <a href='.base_url().'index.php/admin/proy/mis_proyectos/1'.' title="PROGRAMACI&Oacute;N POA"> <span class="menu-item-parent">PROGRAMACI&Oacute;N POA</span></a>
                </li>';
                if(count($id_f)!=0){
                    for($i=0;$i<count($enlaces);$i++){ 
                        $tabla.='
                        <li>
                            <a href="#" >
                                <i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>
                            <ul >';
                            $submenu= $this->menu_modelo->get_Modulos_sub($enlaces[$i]['o_child']);
                            foreach($submenu as $row) {
                               $tabla.='<li><a href='.base_url($row['o_url'])."/".$id_f[0]['proy_id'].'>'.$row['o_titulo'].'</a></li>';
                            }
                        $tabla.='</ul>
                        </li>';
                    }
                }
            $tabla.='
            </ul>
          </nav>';

      return $tabla;
    }

}