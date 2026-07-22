<?php
class crequerimiento extends CI_Controller { 
  public function __construct (){ 
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('ejecucion/model_ejecucion');
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


    /*------- LISTA DE FORM 5 (a optimizar)----------*/
    public function list_requerimientos($prod_id_activo){
      $tabla='';
      $data['stylo'] = $this->programacionpoa->estilo_tabla_form5();
      $get_producto = $this->model_producto->get_producto_id($prod_id_activo);
      if (!empty($get_producto) && count($get_producto) > 0) {
        $get_componente = $this->model_componente->get_componente($get_producto[0]['com_id'], $this->gestion);
        $data['titulo']=$this->cabecera($get_producto, $get_componente); //// Cabecera
        $data['part_padres'] = $this->model_partidas->lista_padres(); // Partidas padres (Agrupadores)
        $data['part_hijos']  = $this->model_partidas->lista_partidas(); // Partidas hijos (Sub-ítems)






         // Recuperamos la colección base de requerimientos vinculados (Form 5)
        $lista_insumos = $this->model_insumo->lista_insumos_prod($prod_id_activo);
        
        $tabla = '';
        $total = 0;
        
        // Totales verticales acumuladores para el pie de la grilla contable
        $total_meses = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0);

        // Inputs hidden limpios de anclaje para el motor form5.js
        $tabla .= '<input type="hidden" name="prod_id" id="prod_id" value="' . $prod_id_activo . '">';
        $tabla .= '<input type="hidden" name="base" id="base_url_siiplas" value="' . base_url() . '">';
        
        // Tabla con diseño vectorial responsivo de SmartAdmin
        $tabla .= '
        <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #e2e8f0; border-radius: 4px;">
        <table id="dt_basic" class="table table-striped table-bordered table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11px; border-collapse: collapse;">
            <thead>
              <tr style="background: #475569; color: #ffffff; text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px;">
                <th style="text-align: center; vertical-align: middle; width: 3%; padding: 8px;">#</th>
                <th style="text-align: center; vertical-align: middle; width: 4%; padding: 8px;">ACCIONES</th>
                <th style="text-align: center; vertical-align: middle; width: 5%; padding: 8px;">PARTIDA</th>
                <th style="text-align: left; vertical-align: middle; width: 15%; padding: 8px;">DETALLE REQUERIMIENTO</th>
                <th style="text-align: left; vertical-align: middle; width: 6%; padding: 8px;">UNIDAD MEDIDA</th>
                <th style="text-align: center; vertical-align: middle; width: 4%; padding: 8px;">CANT.</th>
                <th style="text-align: right; vertical-align: middle; width: 6%; padding: 8px;">COSTO UNIT.</th>
                <th style="text-align: right; vertical-align: middle; width: 7%; padding: 8px;">COSTO TOTAL</th>
                
                <!-- Cabeceras Mensuales en Verde Agua Institucional CNS -->
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">ENE</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">FEB</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">MAR</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">ABR</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">MAY</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">JUN</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">JUL</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">AGO</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">SEP</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">OCT</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">NOV</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">DIC</th>
                
                <th style="text-align: left; vertical-align: middle; width: 10%; padding: 8px;">OBSERVACIONES</th>
              </tr>
            </thead>
            <tbody>';
            
            $cont = 0;
            foreach ($lista_insumos as $row) {
                $cont++;
                $ins_id_actual = intval($row['ins_id']);
                
                // Verificación de blindaje de Certificación Presupuestaria SIGEP
                $ins_cert = (floatval($row['ins_monto_certificado']) > 0) ? 1 : 0;
                
                // Fila con sombreado de alerta suave si el ítem está bloqueado por presupuesto
                $tr_style = ($ins_cert == 1) ? 'style="background: #fef2f2;"' : '';

                $tabla .= '<tr ' . $tr_style . ' title="ID Insumo: ' . $ins_id_actual . '">';
                
                // COLUMNA 1: Correlativo numérico rígido de control lineal
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #64748b; padding: 6px; vertical-align: middle;">' . $cont . '</td>';
                
                // COLUMNA 2: Rejilla unificada elástica de Acciones/Frenos de Auditoría
                $tabla .= '<td style="text-align: center; padding: 6px; vertical-align: middle;">';
                if ($this->tp_adm == 1 || $this->conf_form5 == 1) {
                    if ($ins_cert == 0) {
                        $tabla .= '
                        <div style="display: flex; gap: 4px; justify-content: center;">
                            <a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-xs btn-default mod_ff" name="' . $ins_id_actual . '" title="MODIFICAR REQUERIMIENTO PRESUPUESTARIO" style="padding: 2px 5px;"><i class="fa fa-pencil text-warning" style="font-size:14px;"></i></a>
                            <a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-xs btn-default del_ff" name="' . $ins_id_actual . '" title="ELIMINAR REQUERIMIENTO" style="padding: 2px 5px;"><i class="fa fa-trash text-danger" style="font-size:14px;"></i></a>
                        </div>';
                    } else {
                        $tabla .= '<span class="label label-danger" style="font-size: 9px; padding: 2px 5px; font-weight: bold; background-color: #ef4444;"><i class="fa fa-lock"></i> CERTIFICADO</span>';
                    }
                } else {
                    $tabla .= '<i class="fa fa-eye text-muted" title="Solo lectura"></i>';
                }
                $tabla .= '</td>';

                // Columnas descriptivas base del clasificador nacional
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e293b; padding: 6px; vertical-align: middle;">' . $row['par_codigo'] . '</td>';
                $tabla .= '<td style="text-align: left; padding: 6px; color: #334155; vertical-align: middle;">' . strtoupper($row['ins_detalle']) . '</td>';
                $tabla .= '<td style="text-align: left; padding: 6px; color: #475569; vertical-align: middle;">' . strtoupper($row['ins_unidad_medida']) . '</td>';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e293b; padding: 6px; vertical-align: middle;">' . intval($row['ins_cant_requerida']) . '</td>';
                $tabla .= '<td style="text-align: right; padding: 6px; vertical-align: middle;">' . number_format($row['ins_costo_unitario'], 2, '.', ',') . '</td>';
                $tabla .= '<td style="text-align: right; font-weight: bold; background: #f8fafc; color: #0f172a; padding: 6px; vertical-align: middle;">' . number_format($row['ins_costo_total'], 2, '.', ',') . '</td>';

                // ==========================================================================
                // 🛠️ REPARADO: RESOLUCIÓN DINÁMICA DE LA PROGRAMACIÓN MENSUAL DE LA BASE DE DATOS
                // ==========================================================================
                // Recuperamos el desglose real (mes 1 al 12) vinculando el ins_id
                /*$programacion_meses = $this->model_insumo->get_programacion_meses_insumo($ins_id_actual, intval($this->gestion));
                
                for ($m = 1; $m <= 12; $m++) {
                    $monto_mes_real = isset($programacion_meses[$m]) ? floatval($programacion_meses[$m]) : 0.00;
                    
                    // Estilos cromáticos con resalte si la celda contiene dinero programado
                    $style_celda_mes = ($monto_mes_real > 0) ? 'style="text-align: right; background: #f0fdf4; color: #16a34a; font-weight: bold; padding: 6px; vertical-align: middle;"' : 'style="text-align: right; color: #cbd5e1; padding: 6px; vertical-align: middle;"';
                    
                    $tabla .= '<td ' . $style_celda_mes . '>' . ($monto_mes_real > 0 ? number_format($monto_mes_real, 2, '.', ',') : '0.00') . '</td>';
                    
                    // Acumulamos el subtotal de la columna vertical para el resumen inferior
                    $total_meses[$m] += $monto_mes_real;
                }*/
                // ==========================================================================

                $tabla .= '<td style="text-align: left; color: #64748b; font-style: italic; padding: 6px; vertical-align: middle;">' . htmlspecialchars(strtoupper($row['ins_observacion']), ENT_QUOTES, 'UTF-8') . '</td>';
                $tabla .= '</tr>';
                
                $total += floatval($row['ins_costo_total']);
            }

            $tabla.='
            </tbody>
              <tr class="modo1">
                <td colspan="6"> TOTAL </td>
                <td><font color="blue" size=1>'.number_format($total, 2, ',', '.') .'</font></td>
                <td colspan="15"></td>
              </tr>
          </table>';
        $tabla .= $this->modal_migracion_form5x_actividad($get_producto, $get_componente); /// modal de migracion
        $data['tabla']=$tabla;
        $this->load->view('admin/programacion/requerimiento/form_anteproyecto_form5', $data); /// Gasto Corriente
      }
      else{
        show_error("🚨 Error SIIPLAS: La Actividad física solicitada no existe en PostgreSQL o fue purgada del Formulario N° 4.");
      }
    }


    //// Cabecera titulo
    public function cabecera($producto_row, $componente){
      $componente_row = $componente[0]; // Hilera única activa del componente
      $producto_row = $producto_row[0]; // Hilera única activa de la consulta
      $tit = '<small>PROYECTO : </small>' . $componente_row['aper_programa'] . ' ' . $componente_row['proy_sisin'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['proy_nombre'];
      /*--------- Caso Gasto Corriente (Apertura tipo 4) ----------*/
      if (intval($componente_row['tp_id']) == 4) {
          $tit = '<h2>' . $componente_row['aper_programa'] . ' ' . $componente_row['aper_proyecto'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['tipo'] . ' ' . $componente_row['act_descripcion'] . ' - ' . $componente_row['abrev'] . '  / <b>' . $componente_row['serv_cod'] . ' </b>' . $componente_row['tipo_subactividad'] . ' ' . $componente_row['serv_descripcion'] . '</h2>';
      }

      $data['datos'] = '<h1>' . $tit . '</h1>
                       <h1><small>ACTIVIDAD : </small>COD - ' . round($producto_row['prod_cod'], 2) . '. ' . $producto_row['prod_producto'] . '</h1>';
      $data['prog_especial'] = '';
      
      // 4. 🛠️ REPARADO: Validación elástica de la Unidad Responsable para proyectos de arrastre Bolsa
      if (intval($componente_row['por_id']) == 1) {
          $uni_resp_id = intval($producto_row['uni_resp']);
          
          // Inicializamos la alerta roja restrictiva institucional
          $data['prog_especial'] = '<h1><font color="red"><b>🚨 RESTRICCIÓN: DEBE SELECCIONAR UNIDAD RESPONSABLE EN LA GRILA MAESTRA ANTES DE ASIGNAR INSUMOS V5 !!!!!</b></font></h1>';
          
          if ($uni_resp_id > 0) {
              $unidad = $this->model_componente->get_componente($uni_resp_id, $this->gestion);
              
              if (!empty($unidad) && count($unidad) > 0) {
                  $data['prog_especial'] = '<h1><font color="blue">UNIDAD RESPONSABLE : <b>' . $unidad[0]['tipo_subactividad'] . ' ' . $unidad[0]['serv_descripcion'] . '</b></font></h1>';
              }
          }
      }

      $tabla='';
      $tabla.='
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <input type="hidden" name="base" value="'.base_url().'">
        <div class="well">
          '.$data['datos'].'
          '.$data['prog_especial'].'';
          
          if($this->tp_adm==1 || $this->conf_form5==1){
            $tabla.='
            <a href="#" data-toggle="modal" data-target="#modal_importar_f5" class="btn btn-default importar_f5" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
              <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO REQUERIMIENTOS.Xls</b>
            </a>';
          }
          $tabla.='
        </div>
      </article>';

      return $tabla;
    }

    //// Modal de migracion requerimientos
    public function modal_migracion_form5x_actividad($producto, $componente){
        $prog_especial = '';
        $bloquear_formulario = false;
            
        // 🛠️ REPARADO: Evaluación elástica de la matriz de la Unidad Organizacional
        if (!empty($componente) && isset($componente[0]['por_id']) && intval($componente[0]['por_id']) == 1) {
            $uni_resp_id = intval($producto[0]['uni_resp']);
            
            // Mensaje corporativo SmartAdmin de bloqueo elástico si uni_resp es 0
            $prog_especial = '
                <div class="alert alert-danger text-center" style="margin-bottom: 20px; border-left: 5px solid #ef4444; background: #fef2f2; color: #991b1b; padding: 12px; font-size:12px; font-weight:bold;">
                    <i class="fa fa-exclamation-triangle fa-2x" style="margin-bottom:5px; display:block;"></i>
                    🚨 RESTRICCIÓN DE FORMULACIÓN: DEBE SELECCIONAR LA UNIDAD RESPONSABLE EN LA GRILLA PRINCIPAL (FORM 4) ANTES DE PODER CARGAR O ASIGNAR INSUMOS.
                </div>';
            
            $bloquear_formulario = true; // Gatilla el conmutador de bloqueo de red
            
            if ($uni_resp_id > 0) {
                $unidad = $this->model_componente->get_componente($uni_resp_id, $this->gestion);
                
                if (!empty($unidad) && count($unidad) > 0) {
                    // Si ya tiene asignación, muestra un banner azul informativo limpio
                    $prog_especial = '
                        <div class="alert alert-info" style="margin-bottom: 20px; border-left: 5px solid #3b82f6; background: #eff6ff; color: #1e3a8a; padding: 10px; font-size:11.5px; font-weight:600;">
                            <i class="fa fa-info-circle"></i> UNIDAD RESPONSABLE VINCULADA: ' . strtoupper($unidad[0]['tipo_subactividad'] . ' ' . $unidad[0]['serv_descripcion']) . '
                        </div>';
                    $bloquear_formulario = false; // Libera el formulario
                }
            }
        }

        $tabla = '';
        // Inyectamos el fondo carbón oscuro y el desenfoque elástico de SmartAdmin
        $tabla .= '
        <div class="modal fade" id="modal_importar_f5" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
            <div class="modal-dialog" id="dialog_subirr">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); border: none; overflow: hidden;">
                    
                    <!-- CABECERA DEL COMPONENTE -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                            <i class="fa fa-upload text-primary"></i> Importar Requerimientos x Actividad
                        </h4>
                    </div>

                    <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                    <div class="modal-body" style="padding: 25px; background: #ffffff;">
                        
                        <!-- 🌟 INYECCIÓN DEL CANDADO DE AUDITORÍA: Alerta o Banner Informativo -->
                        ' . $prog_especial . '

                        <!-- Título e Instrucción -->
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Requerimientos x Actividad (.xls, .xlsx)</h5>
                            <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                        </div>

                        <!-- Vista previa de columnas -->
                        <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                            <div style="color:blue; font-weight:bold; font-size:11px;">CÓD. ACT.: <span style="color:#334155; font-size:11.5px;">' . round($producto[0]['prod_cod'], 2) . '.- ' . strtoupper($producto[0]['prod_producto']) . '</span></div><br>
                            <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                        </div>';

                        // 📋 REGLA DE BLOQUEO: Ocultamos el formulario si la unidad obligatoria no fue seleccionada
                        if ($bloquear_formulario === false) {
                            $tabla .= '
                            <!-- Formulario de persistencia binaria -->
                            <!-- 🛠️ REPARADO: Sincronizada la URL de acción exacta hacia el controlador unificado de productos -->
                            <form action="' . site_url('programacion/crequerimiento/valida_migracion_form5_x_actividad') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="prod_id" value="' . $producto[0]['prod_id'] . '" type="hidden" > 
                                <input type="hidden" name="base" value="'.base_url().'">
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

                                <!-- 🛠️ COMPLETADO: Cierre simétrico y limpio del Pre-Loader animado institucional -->
                                <div id="loads_f5" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>';
                        } else {
                            // Si está bloqueado por auditoría, inyectamos un botón deshabilitado inactivo de advertencia
                            $tabla .= '
                            <button type="button" class="btn btn-default btn-block" disabled style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; text-transform: uppercase; background:#f1f5f9; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed;">
                                <i class="fa fa-lock"></i> Importación bloqueada por Auditoría
                            </button>';
                        }
                        
                    $tabla .= '
                    </div>
                </div>
            </div>
        </div>';

        return $tabla;
    }

    //// Valida Migracion de Requerimientos por Actividad
    public function valida_migracion_form5_x_actividad() {
        if (function_exists('ini_set')) {
            ini_set('max_execution_time', 900); 
            ini_set('memory_limit', '3072M'); 
        }
        if (function_exists('set_time_limit')) { @set_time_limit(900); }
        if (function_exists('gc_enable')) { gc_enable(); }

        $this->load->library('excel'); 
        $prod_id = $this->input->post('prod_id');
        $get_form4 = $this->model_producto->get_producto_id($prod_id);

        if (empty($get_form4) || count($get_form4) == 0) {
            echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información de la Actividad. Verifique su sesión.')));
            return;
        }

        $get_unidad=$this->model_componente->get_componente($get_form4[0]['com_id'],$this->gestion);
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

                if (empty($cod_act) && empty($partida) && empty($requerimiento) && (empty($total_raw) || floatval($total_raw) == 0)) {
                    $errores[] = "🚨 RECHAZO DE PLANILLA: Se detectó que la Fila N° $i está completamente vacía o contiene residuos de formato invisible de Excel. Por favor, abra su archivo Excel, seleccione la Fila $i completa (haciendo clic en el número de la fila a la izquierda), haga clic derecho y elija la opción 'Eliminar' para purgar la planilla antes de reintentar la subida.";
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
                // 📋 🛠️ REPARADO REGLA 2: VALIDACIÓN ESTRICTA DE PRECIO UNITARIO (MÁXIMO 2 DECIMALES)
                if ($precio_raw === NULL || trim($precio_raw) === '' || !is_numeric($precio_raw)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' es obligatorio y debe ser numérico.";
                } else {
                    $precio_float = floatval($precio_raw);
                    
                    // Condicional contable: Multiplicamos por 100 y comparamos contra su entero truncado para cazar un 3er decimal (Ej: 2.345 * 100 = 234.5 != 234)
                    if (floor($precio_float * 100) != ($precio_float * 100)) {
                        // Tolerancia por ruido flotante residual de memoria PHP (0.00001)
                        if (abs(($precio_float * 100) - floor($precio_float * 100)) > 0.00001 && abs(($precio_float * 100) - ceil($precio_float * 100)) > 0.00001) {
                            $errores[] = "Fila $i: Restricción Contable -> El 'PRECIO UNITARIO' ($precio_raw) excede el límite permitido de la CNS. Solo se aceptan hasta 2 decimales puros (Ej: 3.45).";
                        }
                    }
                }
                $precio = round(floatval($precio_raw), 2);
                // 📋 REGLA 3: VALIDACIÓN DEL COSTO TOTAL MATEMÁTICO (Cantidad * Precio)
                $total_calculado = round(($cantidad * $precio), 2);
                $total_archivo   = round(floatval($total_raw), 2);

                if (abs($total_archivo - $total_calculado) > 0.05) {
                    $errores[] = "Fila $i: El 'PRECIO TOTAL' registrado ($total_raw) no coincide con la ecuación aritmética (Cantidad: $cantidad * Precio: $precio = $total_calculado).";
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
                          'form4_cod'               => $get_form4[0]['prod_cod'], 
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

    //// Get Requerimiento
    public function get_requerimiento(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']);
        $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos requerimientos productos
        $producto=$this->model_producto->get_producto_id($insumo[0]['prod_id']); /// Get producto
        $componente = $this->model_componente->get_componente($producto[0]['com_id'],$this->gestion); /// Get Componente
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($componente[0]['proy_id']); ////// DATOS DEL PROYECTO

        $monto_asig=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($proyecto[0]['aper_id'],1);
        $monto_prog=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($proyecto[0]['aper_id'],2);
        

        $m_asig=0;$m_prog=0;
        if(count($monto_asig)!=0){
          $m_asig=$monto_asig[0]['monto'];
        }
        if(count($monto_prog)!=0){
          $m_prog=$monto_prog[0]['monto'];
        }

        $saldo=($m_asig-$m_prog);
        
        $par_padre=$this->model_partidas->get_partida_padre($insumo[0]['par_depende']); /// lista de partidas padres
        $lista_partidas=$this->programacionpoa->partidas_dependientes($insumo); /// Lista de Insumos dependientes
        $temporalidad=$this->programacionpoa->distribucion_financiera($insumo); /// Distribucion Financiera
        $lista_umedida=$this->programacionpoa->unidades_medida($insumo); /// Lista de Unidad de medida

        if(count($insumo)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'insumo' => $insumo,
            'monto_saldo' => $saldo+$insumo[0]['ins_costo_total'],
            'lista_partidas'=> $lista_partidas,
            'lista_umedida'=> $lista_umedida,
            'ppdre' => $par_padre,
            'prog' => $temporalidad,
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
    }











}