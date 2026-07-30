<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Programacionpoa extends CI_Controller{
  public function __construct (){
      parent::__construct();
      $this->load->model('programacion/model_proyecto');
      $this->load->model('mantenimiento/model_entidad_tras');
      $this->load->model('mantenimiento/model_partidas');
      $this->load->model('mantenimiento/model_ptto_sigep');
      $this->load->model('modificacion/model_modrequerimiento');
      $this->load->model('programacion/insumos/minsumos');
      $this->load->model('programacion/insumos/model_insumo');
      $this->load->model('ejecucion/model_seguimientopoa');
      $this->load->model('programacion/model_faseetapa');
      $this->load->model('programacion/model_componente');
      $this->load->model('ejecucion/model_notificacion');
      $this->load->model('programacion/model_producto');
      $this->load->model('ejecucion/model_evaluacion');
    //  $this->load->model('mantenimiento/model_configuracion');
      $this->load->model('ejecucion/model_certificacion');
      $this->load->model('analisis_situacion/model_analisis_situacion');
      //$this->load->model('programacion/insumos/minsumos');
      $this->load->model('mestrategico/model_objetivoregion');
      $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
      $this->load->model('mantenimiento/model_estructura_org');
      $this->load->model('menu_modelo');
   
      $this->gestion = $this->session->userData('gestion');
      $this->adm = $this->session->userData('adm');
      $this->dist = $this->session->userData('dist');
      $this->tmes = $this->session->userData('trimestre');
      $this->fun_id = $this->session->userData('fun_id');
      $this->verif_mes=$this->session->userData('mes_actual');
      $this->resolucion=$this->session->userdata('rd_poa');
      $this->tp_adm = $this->session->userData('tp_adm');
      $this->mes = $this->mes_nombre();
      $this->conf_form4 = $this->session->userData('conf_form4');
      $this->conf_form5 = $this->session->userData('conf_form5');
      $this->conf_poa_estado = $this->session->userData('conf_poa_estado'); /// Ajuste POA 1: Inicial, 2 : Ajuste, 3 : aprobado
    }



  /*--- Modal Para Migrar Requerimientos x Componente 2027 ---*/
  public function modal_migracion_form5x_componente($componente){
    $tabla='';
    $tabla.='
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
                          <div style="color:blue;">CÓDIGO DE UNIDAD: <b style="font-size:14px;">'.$componente[0]['serv_cod'].' </b></div><br>
                          <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                      </div>

                      <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                      <form action="' . site_url('programacion/producto/valida_migracion_form5_consolidado') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                          <input name="com_id" value="'.$componente[0]['com_id'].'" type="hidden" > 
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

    return $tabla;
  }

    //// Modal de migracion requerimientos x Actividad
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
                            <form action="' . site_url('programacion/crequerimiento/valida_migracion_form5_x_actividad') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos_act" autocomplete="off" style="padding:0; background:transparent;">
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

                                <div id="mensaje_f5_act" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir_f5_act" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- 🛠️ COMPLETADO: Cierre simétrico y limpio del Pre-Loader animado institucional -->
                                <div id="loads_f5_act" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
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


    //// Modal lista de partidas asignados y programados por Unidad Organizacional
    public function modal_partidas_unidad_organizacional($proyecto){
        $tabla='';
        $partidas   = $this->model_insumo->lista_partidas_ppto_poa_asignado(intval($proyecto[0]['aper_id']), $g_id);
        $componente = $this->model_componente->lista_UnidadesResponsables($proyecto[0]['proy_id']);
        
        $tabla .= '
        <div style="margin-bottom: 12px; display: flex; gap: 6px; justify-content: flex-end; background: #f8fafc; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 4px;">
            <!-- Botón Imprimir -->
            <button type="button" class="btn btn-sm btn-default" onclick="imprimirTechosModal();" style="font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; padding: 5px 12px; font-size:11.5px; color:#334155; border-radius:3px;">
                <i class="fa fa-print text-primary" style="font-size:13px;"></i> Imprimir Cuadro
            </button>
            <!-- Botón Excel Masivo Directo -->
            <button type="button" class="btn btn-sm btn-default" onclick="exportarExcelModalDirecto();" style="font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; padding: 5px 12px; font-size:11.5px; color:#334155; border-radius:3px;">
                <i class="fa fa-file-excel-o text-success" style="font-size:13px;"></i> Descargar Excel
            </button>
        </div>';

        // ==========================================================================
        // 🚨 TU LÓGICA DE LISTADO ORIGINAL (MANTENIDA INTACTA AL 100%)
        // ==========================================================================
        $tabla.='
        <div id="area_impresion_techos_f5" class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px;">
            <table id="tabla_techos_reporte_cns" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11px; border-collapse: collapse;">
              <thead>
                <tr style="background: #334155; color: #ffffff; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; height: 38px; vertical-align: middle;">
                  <th style="text-align: left; padding-left: 10px; min-width: 240px; background: #1e293b;">PARTIDA CONTABLE</th>
                  <th style="text-align: right; padding-right: 10px; width: 12%; background: #1e3a8a;">PPTO. ASIGNADO</th>';
                  
                  // CABECERA: Bucle elástico de unidades responsables
                  foreach($componente as $rowc){
                    $tabla .= '<th style="text-align: right; padding-right: 8px; min-width: 95px;">' . $rowc['tipo_subactividad'] . '<br><small style="color:#94a3b8; font-size:8.5px; font-weight:normal;">' . substr(strtoupper($rowc['com_componente']), 0, 12) . '.</small></th>';
                  }
                  
              $tabla .= '
                  <th style="text-align: right; padding-right: 10px; width: 11%; background: #d97706;">TOTAL POA</th>
                  <th style="text-align: right; padding-right: 10px; width: 11%; background: #475569;">SALDO DISP.</th>
                </tr>
              </thead>
              <tbody>';
          foreach($partidas as $row){
            $saldo_item    = floatval($row['saldo_disponible']);
            $style_saldo = ($saldo_item < 0) ? 'background: #fef2f2; color: #dc2626; font-weight: bold;' : 'background: #f0f9ff; color: #2563eb; font-weight: bold;';

            $tabla.='
            <tr style="height: 30px; vertical-align: middle;">
              <td style="font-weight: bold; color: #1e293b; padding-left: 10px; background: #f8fafc; border-right: 2px solid #e2e8f0;" >'.$row['par_codigo'].' - '.$row['par_nombre'].'</td>
              <td style="text-align: right; padding-right: 10px; font-weight: 600; background: #f8fafc;">' . number_format($row['presupuesto_asignado_sigep'], 2, '.', ',') . '</td>';
              foreach($componente as $rowc){
                $get_partida=$this->model_insumo->get_partida_programado_x_uresponsable($rowc['com_id'],$row['par_id']);
                $tabla.='
                <td style="text-align: right; padding-right: 8px; font-weight: 500;">';
                if(count($get_partida)!=0){
                  $tabla.=number_format($get_partida[0]['ppto_poa'], 2, '.', ',');
                }
                else{
                  $tabla.='0.00';
                }
                $tabla.='
                </td>';
              }
            $tabla.='
            <td style="text-align: right; font-weight: bold; background: #fef3c7; color: #b45309; padding-right: 10px;">' . number_format($row['presupuesto_programado_poa'], 2, '.', ',') . '</td>
            <td style="text-align: right; padding-right: 10px; ' . $style_saldo . '">' . number_format($row['saldo_disponible'], 2, '.', ',') . '</td>
            </tr>';
          }
        $tabla.='
        </tbody>
        </table>
        </div>';

        return $tabla;
    }


    //// Modal lista de partidas programados por Unidad Responsable
    public function modal_partidas_programadas_unidad_responsable(){
        $tabla='';
        $tabla.='
        <div class="modal fade" id="modal_desglose_partidas_unidad" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.45);">
            <div class="modal-dialog modal-lg" style="width: 50% !important; max-width: 95%; margin: 25px auto;">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: none; overflow: hidden;">
                    
                    <!-- CABECERA DEL MODAL CORPORATIVA -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; margin:0;">
                            <i class="fa fa-folder-open text-primary"></i> Desglose de Presupuesto Programado por Partidas
                        </h4>
                        <!-- 🛠️ REPARADO: Añadido data-dismiss para que la "x" superior limpie los flujos y cierre la ventana -->
                        <button type="button" class="close" data-dismiss="modal" style="font-size: 20px; color: #475569; opacity: 0.8; border:none; background:none; cursor:pointer;">&times;</button>
                    </div>

                    <!-- CUERPO RECEPTOR DINÁMICO AJAX -->
                    <div class="modal-body" id="contenedor_desglose_dinamico_cns" style="padding: 20px 25px; background: #ffffff; max-height: calc(100vh - 165px); overflow-y: auto;">
                        <!-- Aquí el JS estampará el spinner y luego la matriz cruzada de saldos -->
                    </div>
                    
                    <!-- PIE DE VENTANA ACCIONES -->
                    <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 20px; text-align: right; margin:0;">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="font-weight: bold; font-size: 11.5px; padding: 6px 16px; border-radius: 3px; background:#64748b; color:#fff; border-color:#475569;">
                            <i class="fa fa-arrow-circle-left"></i> Cerrar Ventana
                        </button>
                    </div>

                </div>
            </div>
        </div>';

        return $tabla;
    }









  /// ----- APERTURAR NUEVO POA (UNIDAD)
  /*------------ FORMULACIÓN - ADICION - POA (2020) ----------*/
  public function formulacion_add_poa_adm(){
    $tabla='';
    $regionales=$this->model_proyecto->list_departamentos();
    $unidades=$this->model_estructura_org->list_unidades_apertura();
      $tabla.='
            <article class="col-sm-12">
            <div class="well">
              <form action="'.site_url("").'/programacion/proyecto/valida_poa_unidades'.'" id="form1" name="form1" class="smart-form" method="post">
                  <input type="hidden" name="tp" id="tp" value="1">
                  <header><b>FORMULACI&Oacute;N POA '.$this->gestion.'</b></header>
                  <input type="hidden" name="uni_id" id="uni_id" value="0">
                  <input type="hidden" name="prog" id="prog" value="0">
                  <input type="hidden" name="act" id="act" value="0">
                  <fieldset>          
                    <div class="row">
                      <section class="col col-3">
                        <label class="label">REGIONAL</label>
                        <select class="select2" id="reg_id" name="reg_id" title="SELECCIONE REGIONAL">
                        <option value="">SELECCIONE REGIONAL</option>';
                        foreach($regionales as $row){
                          if($row['dep_id']!=0){
                            $tabla.='<option value="'.$row['dep_id'].'">'.$row['dep_id'].'.- '.strtoupper($row['dep_departamento']).'</option>';
                          }
                        }
                        $tabla.='
                        </select>
                      </section>
                      
                    </div>
                    
                    <div id="uni" style="display:none;">
                      <hr><br>
                      <div class="row">
                        <section class="col col-3">
                          <label class="label">UNIDAD ORGANIZACIONAL</label>
                          <select class="select2" id="act_id" name="act_id" title="SELECCIONE UNIDAD ORGANIZACIONAL">
                          <option value="">SELECCIONE UNIDAD / ESTABLECIMIENTO</option>
                          </select>
                        </section>
                        <section class="col col-5">
                          <label class="label">VINCULACI&Oacute;N A OPERACIÓN REGIONAL</label>
                          <div id="oregional"></div>
                        </section>

                        <section class="col col-4">
                          <label class="label">UNIDADES RESPONSABLES DISPONIBLES</label>
                          <div id="servicios"></div>
                        </section>
                      </div>
                      <div class="row">
                        <div id="programa"></div>
                      </div>
                    </div>
                    

                  </fieldset>
                  <div id="programa"></div>
                  <div id="but" style="display:none;">
                    <footer>
                      <button type="button" name="subir_form1" id="subir_form1" class="btn btn-info">GUARDAR DATOS</button>
                      <a href="'.base_url().'index.php/admin/proy/list_proy" title="SALIR" class="btn btn-default">CANCELAR</a>
                    </footer>
                  </div>
              </form>
              </div>
            </article>';
    return $tabla;
  }

  /*------------ FORMULACIÓN - ADICION - POA (2020) ----------*/
    public function formulacion_add_poa(){
      $tabla='';
      $unidades=$this->model_estructura_org->list_unidades_apertura();
      $tabla.='
            <article class="col-sm-12">
            <div class="well">
              <form action="'.site_url("").'/programacion/proyecto/valida_poa_unidades'.'" id="form1" name="form1" class="smart-form" method="post">
                  <input type="hidden" name="tp" id="tp" value="1">
                  <header><b>FORMULACI&Oacute;N POA '.$this->gestion.'</b></header>
                  <input type="hidden" name="uni_id" id="uni_id" value="0">
                  <input type="hidden" name="prog" id="prog" value="0">
                  <input type="hidden" name="act" id="act" value="0">
                  <fieldset>          
                    <div class="row">
                      <section class="col col-3">
                        <label class="label">UNIDAD ORGANIZACIONAL</label>
                        <select class="form-control" id="act_id" name="act_id" title="SELECCIONE UNIDAD ORGANIZACIONAL">
                        <option value="">SELECCIONE UNIDAD ORGANIZACIONAL</option>';
                        foreach($unidades as $row){
                          if(count($this->model_proyecto->get_uni_apertura_programatica($row['act_id']))==0){
                            $tabla.='<option value="'.$row['act_id'].'">'.$row['act_cod'].'.- '.$row['tipo'].' '.$row['act_descripcion'].' - '.$row['abrev'].'</option>';
                          }
                        }
                        $tabla.='
                        </select>
                      </section>

                      <section class="col col-5">
                        <label class="label">VINCULACI&Oacute;N A OPERACIÓN REGIONAL</label>
                        <div id="oregional"></div>
                      </section>

                      <section class="col col-4">
                        <label class="label">UNIDADES DISPONIBLES</label>
                        <div id="servicios"></div>
                      </section>
                    </div>
                  </fieldset>
                  <div id="programa"></div>
                  <div id="but" style="display:none;">
                    <footer>
                      <button type="button" name="subir_form1" id="subir_form1" class="btn btn-info">GUARDAR DATOS</button>
                      <a href="'.base_url().'index.php/admin/proy/list_proy" title="SALIR" class="btn btn-default">CANCELAR</a>
                    </footer>
                  </div>
              </form>
              </div>
            </article>';

      return $tabla;
    }



  /// ----- EDITAR DATOS POA (UNIDAD)
     /*------------ FORMULACIÓN - UPDATE - POA (2020) ----------*/
    public function formulacion_update_poa($proyecto){
      $tabla='';
      $actividad= $this->model_estructura_org->get_actividad($proyecto[0]['act_id']); /// get actividad
      $oregionales=$this->model_objetivoregion->get_unidad_pregional_programado($proyecto[0]['act_id']); /// Objetivos Regionales
      $servicios=$this->model_estructura_org->list_establecimiento_servicio($actividad[0]['te_id']); /// Servicios Habilitados
      $fase = $this->model_faseetapa->get_id_fase($proyecto[0]['proy_id']);
      $tabla.='
      <article class="col-sm-12">
      <div class="well">
        <form action="'.site_url("").'/programacion/proyecto/valida_update_poa_unidades'.'" id="form1" name="form1" class="smart-form" method="post">
            <input type="hidden" name="base" value="'.base_url().'">
            <input type="hidden" name="tp" id="tp" value="1">
            <input type="hidden" name="proy_id" id="proy_id" value="'.$proyecto[0]['proy_id'].'">
            <input type="hidden" name="nro_ope" id="nro_ope" value="'.count($oregionales).'">
            <header><b>FORMULACI&Oacute;N POA '.$this->gestion.'</b></header>
            <fieldset>          
              <div class="row">
                <section class="col col-3">
                  <label class="label">UNIDAD / ESTABLECIMIENTO DE SALUD '.$proyecto[0]['act_id'].'</label>
                  <select class="select2" id="act_id" name="act_id" title="SELECCIONE UNIDAD / ESTABLECIMIENTO DE SALUD" disabled>
                  <option value="'.$actividad[0]['act_id'].'">'.$actividad[0]['act_cod'].'.- ('.$actividad[0]['tipo'].') '.$actividad[0]['act_descripcion'].'</option></select>
                </section>

                <section class="col col-5">
                  <label class="label">VINCULACI&Oacute;N A OPERACIONES '.$this->gestion.'</label>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th style="width:4%;"></th>
                        <th style="width:47.5%;">OPERACI&Oacute;N REGIONAL '.$this->gestion.'</th>
                        <th style="width:47.5%;">ACCI&Oacute;N DE CORTO PLAZO '.$this->gestion.'</th>
                        
                      </tr>
                    </thead>
                    <tbody>';
                    $cont = 0;
                    foreach($oregionales as $row){
                      $verif=$this->model_objetivoregion->get_proyecto_oregional($proyecto[0]['proy_id'],$row['por_id']);
                        $color='#f9eeee';
                        if($row['or_estado']!=0){
                          $color='#e2f9f6';
                        }
                      $cont++;
                      $tabla.='
                      <tr bgcolor='.$color.'>
                        <td style="width:4%;">';
                          if(count($verif)!=0){
                            $tabla.='<center><input type="checkbox" id="ope'.$cont.'" onclick="scheck'.$cont.'(this.checked,'.$row['por_id'].','.$proyecto[0]['proy_id'].');" title="OBJETIVO SELECCIONADO" checked/></center>';
                          }
                          else{
                            $tabla.='<center><input type="checkbox" id="ope'.$cont.'" onclick="scheck'.$cont.'(this.checked,'.$row['por_id'].','.$proyecto[0]['proy_id'].');" title="SELECCIONE OBJETIVO REGIONAL"/></center>';
                          }
                        $tabla.='
                        <td style="width:47.5%;"><b>'.$row['og_codigo'].'.'.$row['or_codigo'].'.</b>.- '.$row['or_objetivo'].'</td>
                        <td style="width:47.5%;"><b>'.$row['og_codigo'].'</b>.- '.$row['og_objetivo'].'</td>
                      </tr>';
                      ?>
                      <script>
                        function scheck<?php echo $cont;?>(estaChequeado,id,proy_id) {
                          valor=0;
                          titulo='DESACTIVAR OPERACIÓN REGIONAL';
                          if (estaChequeado == true) {
                            valor=1;
                            titulo='ACTIVAR OPERACIÓN REGIONAL';
                          }

                          alertify.confirm(titulo, function (a) {
                              if (a) {
                                  var url = "<?php echo site_url().'/programacion/proyecto/estado_oregional'?>";
                                  $.ajax({
                                      type: "post",
                                      url: url,
                                      data:{id:id,estado:valor,proy_id:proy_id},
                                      success: function (data) {
                                          window.location.reload(true);
                                      }
                                  });
                              } else {
                                  alertify.error("OPCI\u00D3N CANCELADA");
                              }
                          });
                        }
                      </script>
                      <?php
                    }
                    if($this->tp_adm==1){
                      $tabla.='
                      <tr>
                        <td><a href="javascript:deseleccionar_todo()" class="btn btn-default">Marcar ninguno</a></td>
                        <td><a href="javascript:seleccionar_todo()" class="btn btn-default">Marcar todos</a></td>
                        <td colspan=2></td>
                      </tr>';
                    }
                    $tabla.='
                    </tbody>
                  </table>
                </section>

                <section class="col col-4">
                  <label class="label">UNIDADES / SERVICIOS DISPONIBLES</label>
                  <table class="table table-bordered">
                    <thead>
                      <tr>
                        <th style="width:1%;">#</th>
                        <th style="width:4%;">C&Oacute;DIGO</th>
                        <th style="width:50%;">UNIDAD RESPONSABLE'.$this->gestion.'</th>
                        <th style="width:10%;"></th>
                        <th style="width:10%;">PROGRAMACIÓN POA</th>
                      </tr>
                    </thead>
                    <tbody>';
                    $cont = 0;
                    foreach($servicios as $row){
                      $veri_cs=$this->model_proyecto->verif_componente_servicio($fase[0]['id'],$row['serv_id']);
                      $cont++;
                      $tabla.='
                      <tr>
                        <td align=center>'.$cont.'</td>
                        <td>'.$row['serv_cod'].'</td>
                        <td>'.$row['serv_descripcion'].'</td>';
                          if($row['serv_id']!=0){
                            if(count($veri_cs)!=0){
                            $tabla.='
                            <td align="center">';
                              if(count($this->model_producto->lista_form4_x_unidadresponsable($veri_cs[0]['com_id']))==0){
                                $tabla.='<input type="checkbox" onclick="scheckk'.$cont.'(this.checked,'.$row['serv_id'].','.$fase[0]['id'].');" title="SERVICIO ACTIVADO" checked/>';
                              }
                            $tabla.='
                            </td>
                            <td align="center">
                              <a href="'.site_url("admin").'/prog/list_prod/'.$veri_cs[0]['com_id'].'" title="PROGRAMAR ACTIVIDADES" class="btn btn-default"><img src="'.base_url().'assets/ifinal/archivo.png" WIDTH="35" HEIGHT="35"/></a>
                            </td>';
                            }
                            else{
                              $tabla.='
                              <td>
                                <input type="checkbox" onclick="scheckk'.$cont.'(this.checked,'.$row['serv_id'].','.$fase[0]['id'].');" title="SELECCIONAR SERVICIO"/>
                              </td>
                              <td>
                              </td>';
                            }
                          }
                          $tabla.='
                        </td>
                      </tr>';
                      ?>
                      <script>
                        function scheckk<?php echo $cont;?>(estaChequeado,id,pfec_id) {
                          valor=0;
                          titulo='DESACTIVAR UNIDAD RESPONSABLE';
                          if (estaChequeado == true) {
                            valor=1;
                            titulo='ACTIVAR UNIDAD RESPONSABLE';
                          }

                          alertify.confirm(titulo, function (a) {
                              if (a) {
                                  var url = "<?php echo site_url().'/programacion/proyecto/estado_servicios'?>";
                                  $.ajax({
                                      type: "post",
                                      url: url,
                                      data:{id:id,estado:valor,pfec_id:pfec_id},
                                      success: function (data) {
                                          window.location.reload(true);
                                      }
                                  });
                              } else {
                                  alertify.error("OPCI\u00D3N CANCELADA");
                              }
                          });
                        }
                      </script>
                      <?php
                    }
                    $tabla.='
                    </tbody>
                  </table>
                </section>
              </div>
            </fieldset>
            <center><div class="alert alert-warning alert-block"><h1>'.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev'].'</h1></div></center>
        </form>
        </div>
      </article>';

      return $tabla;
    }



    /*------ GET POA -----*/
    public function mi_poa($proy_id){
      $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO
      $programas_bolsas=$this->model_proyecto->lista_programas_bolsas_distrital($proyecto[0]['dist_id']);
      $tabla='';
        $tabla.='
        <section class="col col-12">
          <input id="searchTerm" type="text" onkeyup="doSearch()" class="form-control" placeholder="BUSCADOR...." style="width:45%;"/><br>
        </section>
        <table class="table table-bordered" id="datos">
              <thead>
              <tr>
                <th>#</th>
                <th>UNIDAD RESPONSABLE </th>
                <th colspan=2><b>POA PROG. '.$proyecto[0]['aper_programa'].'</b><br>'.$proyecto[0]['proy_nombre'].'</th>';
                
                if(count($programas_bolsas)!=0){
                  foreach($programas_bolsas  as $row){
                    $tabla.='<th><b>POA PROG. '.$row['aper_programa'].'</b><br>'.$row['proy_nombre'].'</th>';
                  }
                }

                $tabla.='
              </tr>
              </thead>
              <tbody>';
              $nroc=0; $nro_ppto=0;
                $unidades=$this->model_componente->lista_UnidadesResponsables($proy_id);
                foreach($unidades as $pr){
                  if(count($this->model_producto->productos_nro($pr['com_id']))!=0){
                    $nroc++;
                    $tabla.=
                      '<tr>
                        <td>'.$nroc.'</td>
                        <td>'.$pr['serv_cod'].' '.$pr['tipo_subactividad'].' '.$pr['serv_descripcion'].'</td>
                        <td align=center>
                          <a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$pr['com_id'].'\');" class="btn btn-default" title="REPORTE FORMULARIO SPO N° 4 - ACTIVIDADES"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/><br><font size=1><b>FORM. N°4</b></font></a>
                        </td>
                        <td align=center>';
                          if(count($this->model_insumo->list_consolidado_partidas_uResponsable($pr['com_id']))!=0){
                            $tabla.='<a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form5_uresponsable/'.$pr['com_id'].'\');" class="btn btn-default" title="REPORTE FORMULARIO SPO N° 5 - REQUERIMIENTOS"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/><br><font size=1><b>FORM. N°5</b></font></a>';
                            $nro_ppto++;
                          } 
                        $tabla.='
                        </td>';

                        if(count($programas_bolsas)!=0){
                          foreach($programas_bolsas as $row){
                            $get_prog_bolsa=$this->model_producto->verif_programaBolsa_prog($row['aper_id'],$pr['com_id']); // Verifica la Actividad de la Unidad Responsable del Programa Bolsa

                            $tabla.='<td align=center>';
                            if(count($get_prog_bolsa)!=0){
                              $tabla.='<a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form5_uresponsable_programa_bolsa/'.$row['aper_id'].'/'.$pr['com_id'].'\');" class="btn btn-default" title="REPORTE FORMULARIO SPO N° 5 - REQUERIMIENTOS PROGRAMAS BOLSA"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/><br><font size=1><b>FORM. N°5</b></font></a>';
                            }
                            $tabla.='</td>';
                          }
                        }
                        $tabla.='
                      </tr>';
                  }
                  
                }
              $tabla.='</tbody>
              <tr bgcolor="#d6ecb3">
                      <td colspan=3 title="'.$proyecto[0]['aper_id'].'">('.$proyecto[0]['aper_id'].') <b>CONSOLIDADO TOTAL POA - PRESUPUESTO APROBADO TOTAL POR PARTIDAS </b></td> 
                      <td align=center><a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_ptto_consolidado_comparativo_programa/'.$proy_id.'\');"  title="CONSOLIDADO POA - PRESUPUESTO" class="btn btn-default" ><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30"/></a></td>
                      <td colspan='.count($programas_bolsas).'></td>
                    </tr>';
                  /*$partidas_asig=$this->model_ptto_sigep->partidas_proyecto($proyecto[0]['aper_id']);
                  if(count($partidas_asig)!=0){ //// POA APROBADO
                    $tabla.='
                    <tr bgcolor="#d6ecb3">
                      <td colspan=3 title="'.$proyecto[0]['aper_id'].'">('.$proyecto[0]['aper_id'].') <b>CONSOLIDADO TOTAL POA - PRESUPUESTO APROBADO TOTAL POR PARTIDAS </b></td> 
                      <td align=center><a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_ptto_consolidado_comparativo_programa/'.$proy_id.'\');"  title="CONSOLIDADO POA - PRESUPUESTO" class="btn btn-default" ><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30"/></a></td>
                      <td colspan='.count($programas_bolsas).'></td>
                    </tr>';
                  }*/
              $tabla.='
              
            </table>';

   

      return $tabla;
    }



    /*--- TIPO DE RESPONSABLE (Vigente) ---*/
    public function tp_resp(){
      $ddep = $this->model_proyecto->dep_dist($this->dist);
      if($this->adm==1){
        $titulo='<h1>RESPONSABLE : '.$this->session->userdata('funcionario').' -> <small>RESPONSABLE NACIONAL</h1>';
      }
      elseif($this->adm==2){
        $titulo='<h1>RESPONSABLE : '.$this->session->userdata('funcionario').' -> <small>RESPONSABLE '.strtoupper($ddep[0]['dist_distrital']).'</h1>';
      }

      return $titulo;
    }

    /*--- ESTILO ---*/
    public function estilo_tabla(){
      $tabla='';
      $tabla.='
        <style>
          .table1{
                display: inline-block;
                width:100%;
                max-width:1550px;
                overflow-x: scroll;
                }
          table{font-size: 10px;
                width: 100%;
                max-width:1550px;;
          overflow-x: scroll;
                }
                th{
                  padding: 1.4px;
                  text-align: center;
                  font-size: 10px;
                }
                #mdialTamanio{
                  width: 45% !important;
                }
                #mdialTamanio2{
                  width: 35% !important;
                }
          </style>';

      return $tabla;
    }

  ///// ============= FORMULARIO N° 4 

  /*--- ACTUALIZA CODIGO DE ACTIVIDAD (FORM 4) ----*/
/*  public function update_codigo_actividad($com_id){  
    $productos = $this->model_producto->lista_form4_x_unidadresponsable($com_id,$this->gestion); // Lista de productos
    $nro=0;
    foreach($productos as $row){
      $nro++;
      $update_prod= array(
        'prod_cod' => $nro,
        'fun_id' => $this->fun_id
      );
      $this->db->where('prod_id', $row['prod_id']);
      $this->db->update('_productos', $update_prod);
    }
  }*/

    /*--- BOTON REPORTE SEGUIMIENTO POA (MES VIGENTE)---*/
/*    function button_form4($nro,$com_id){
      $tabla='';
      if($this->conf_form4==1 || $this->fun_id==401 || $this->fun_id==399 || $this->fun_id==583 || $this->fun_id==600){
      //if($this->tp_adm==1 || $this->conf_form4==1){
        $tabla.=' <a href="#" data-toggle="modal" data-target="#modal_nuevo_form" class="btn btn-default nuevo_form" title="NUEVO REGISTRO FORM N 4" >
                    <img src="'.base_url().'assets/Iconos/add.png" WIDTH="20" HEIGHT="20"/>&nbsp;NUEVO REGISTRO
                  </a>
                  
                  <a href="#" data-toggle="modal" data-target="#modal_importar_ff" class="btn btn-default importar_ff" name="1" title="MODIFICAR REGISTRO" >
                    <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;SUBIR NUEVAS ACTIVIDADES.CSV
                  </a>';

        
      }
      if($this->conf_form5==1 || $this->fun_id==401 || $this->fun_id==399 || $this->fun_id==583 || $this->fun_id==600){
     // if($this->tp_adm==1 || $this->conf_form5==1){ 
        if($nro!=0){ 
          $tabla.=' <a href="#" data-toggle="modal" data-target="#modal_importar_ff" class="btn btn-default importar_ff" name="2" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;SUBIR REQUERIMIENTOS (GLOBAL)
                    </a>
                    <a href="#" data-toggle="modal" data-target="#modal_ver_form5" class="btn btn-default ver_requerimientos" name="'.$com_id.'" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
                      <img src="'.base_url().'assets/Iconos/text_list_bullets.png" WIDTH="30" HEIGHT="20"/>&nbsp;VER MIS REQUERIMIENTOS
                    </a>';
        }
      }

      $tabla.='<br><br>';
      
      return $tabla;
    }*/

    /*--- LISTA DE OBJETIVO REGIONAL (GASTO CORRIENTE )-----*/
    public function lista_oregional($proy_id){
      $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
      $tabla='';
      if(count($list_oregional)==1){
        $tabla.=' <section class="col col-3">
                    <label class="label"><b>OPERACIÓN REGIONAL '.$list_oregional[0]['or_id'].'</b></label>
                    <label class="input">
                      <i class="icon-append fa fa-tag"></i>
                      <input type="hidden" name="or_id" id="or_id" value="'.$list_oregional[0]['or_id'].'">
                      <input type="text" value="'.$list_oregional[0]['or_codigo'].'.- '.$list_oregional[0]['or_objetivo'].'" disabled>
                    </label>
                  </section>'; 
      }
      else{
          $tabla.='<section class="col col-6">
                  <label class="label"><b>ALINEACIÓN OPERACIÓN REGIONAL '.$this->gestion.'</b></label>
                    <select class="form-control" id="or_id" name="or_id" title="SELECCIONE">
                      <option value="">SELECCIONE ALINEACIÓN OPERACIÓN</option>';
                      foreach($list_oregional as $row){ 
                        $tabla.='<option value="'.$row['or_id'].'">('.$row['aper_programa'].' '.$row['aper_proyecto'].' '.$row['aper_actividad'].') / '.$row['og_codigo'].'.|'.$row['or_codigo'].'. .- '.$row['or_objetivo'].'</option>';    
                      }
                    $tabla.='
                  </select>
                </section>'; 
      }
         
      return $tabla;
    }

    /*---- LISTA DE OBJETIVO REGIONAL (PROYECTO DE INVERSION)-----*/
    public function lista_oregional_pi($proy_id){
      $list_oregional= $this->model_objetivoregion->get_unidad_pregional_programado($proy_id);
      $tabla='';
      if(count($list_oregional)==1){
        $tabla.=' <section class="col col-6">
                    <label class="label"><b>OPERACIÓN REGIONAL '.$list_oregional[0]['or_id'].'</b></label>
                    <label class="input">
                      <i class="icon-append fa fa-tag"></i>
                      <input type="hidden" name="or_id" id="or_id" value="'.$list_oregional[0]['or_id'].'">
                      <input type="text" value="'.$list_oregional[0]['og_codigo'].'.'.$list_oregional[0]['or_codigo'].'. .- '.$list_oregional[0]['or_objetivo'].'" disabled>
                    </label>
                  </section>'; 
      }
      else{
          $tabla.='<section class="col col-6">
                  <label class="label"><b>ALIENACIÓN OPERACIÓN REGIONAL '.$this->gestion.'</b></label>
                    <select class="form-control" id="or_id" name="or_id" title="SELECCIONE">
                      <option value="0">SELECCIONE ALINEACIÓN OPERACIÓN</option>';
                      foreach($list_oregional as $row){ 
                        $tabla.='<option value="'.$row['or_id'].'">'.$row['og_codigo'].'.|'.$row['or_codigo'].'. .- '.$row['or_objetivo'].'</option>';    
                      }
                    $tabla.='
                  </select>
                </section>'; 
      }
         
      return $tabla;
    }

    /*----------- VERIFICA LA ALINEACION DE OBJETIVO REGIONAL -----*/
    public function verif_oregional($proy_id){
      $proyecto=$this->model_proyecto->get_id_proyecto($proy_id);
      $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
/*      if($proyecto[0]['tp_id']==1){
        $list_oregional=$this->model_objetivoregion->get_unidad_pregional_programado($proy_id);
      }
      else{
        $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($proy_id); /// PROYECTO
        $list_oregional=$this->model_objetivoregion->get_unidad_pregional_programado($proyecto[0]['act_id']); /// Objetivos Regionales
      }*/
      
      $tabla='';
      $nro=0;
      if(count($list_oregional)!=0){
        foreach($list_oregional as $row){
          $nro++;
          $tabla.='<h1 title='.$row['or_id'].'>'.$nro.' .- ('.$row['aper_programa'].' '.$row['aper_proyecto'].' '.$row['aper_actividad'].') / OPERACIÓN REGIONAL : <small> <b>'.$row['og_codigo'].'.|'.$row['or_codigo'].'.</b>.- '.$row['or_objetivo'].'</small></h1>';
        }
      }
      else{
        $tabla.='<h1><small><font color=red>NO ALINEADO A NINGUNA OPERACIÓN REGIONAL</font></small></h1>';
      }
      
      return $tabla;
    }




    /*--- ESTILO FORM 4---*/
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


/// ===== FORMULARIO N5

    /*------- TIPO AJUSTE POA --------*/
/*    public function titulo_ajuste($proyecto,$componente){
      $tabla='';
      $tabla.='
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="well">
            <h2>'.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.$proyecto[0]['tipo'].' '.$proyecto[0]['proy_nombre'].' '.$proyecto[0]['abrev'].' / '.$componente[0]['serv_cod'].'.- '.$componente[0]['serv_descripcion'].'</h2>
            <a role="menuitem" tabindex="-1" href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default" title="NUEVO REGISTRO">
              <img src="'.base_url().'assets/Iconos/add.png" WIDTH="20" HEIGHT="20"/>&nbsp;NUEVO REGISTRO (FORM. N 5)
            </a>
            <a href="#" data-toggle="modal" data-target="#modal_importar_ff" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
              <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;SUBIR REQUERIMIENTOS.CSV 
            </a>
            <a href="#" data-toggle="modal" data-target="#modal_comparativo" name="'.$proyecto[0]['proy_id'].'" id="'.$proyecto[0]['aper_programa'].''.$proyecto[0]['aper_proyecto'].''.$proyecto[0]['aper_actividad'].' - '.$proyecto[0]['tipo'].' '.$proyecto[0]['proy_nombre'].' '.$proyecto[0]['abrev'].'" class="btn btn-default comparativo" title="MOSTRAR CUADRO COMPARATIVO PRESUPUESTARIA ASIGANDO-POA">
              <i class="fa fa-clipboard"></i> <b>COMPARATIVO PPTO.</b>
            </a>
            <a class="btn btn-danger" id="btsubmit" onclick="valida_eliminar()" title="ELIMINAR REQUERIMIENTOS SELECCIONADOS">
              <i class="glyphicon glyphicon-trash"></i> ELIMINAR INSUMOS (SELECCIONADOS)
            </a>
          </div>
        </article>';

      return $tabla;
    }*/ 


    /*--- DISTRIBUCION FINANCIERA ---*/
/*    function distribucion_financiera($insumo){
      $prog=$this->model_insumo->list_temporalidad_insumo($insumo[0]['ins_id']); /// Temporalidad Requerimiento 2020
        for ($i=0; $i <=12 ; $i++) { 
          if($i==0){
            $titulo[$i]='programado_total';  
          }
          else{
            $titulo[$i]='mes'.$i.''; 
          }

          $temporalidad[$i]=0;
        }

        if(count($prog)!=0){
          for ($i=0; $i <=12 ; $i++) { 
            $temporalidad[$i]= round($prog[0][$titulo[$i]],2);
          }
        }

      return $temporalidad;
    }*/

    /*--- PARTIDAS DEPENDIENTES ---*/
    function partidas_dependientes($insumo){
      $tabla='';
      $get_partida=$this->model_partidas->get_partida($insumo[0]['par_id']); /// datos de la partda
      $lista_partidas=$this->model_partidas->lista_par_hijos($get_partida[0]['par_depende']);
      foreach ($lista_partidas as $row) {
        if($insumo[0]['par_id']==$row['par_id']){
          $tabla.='<option value="'.$row['par_id'].'" selected>'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
        }
        else{
          $tabla.='<option value="'.$row['par_id'].'">'.$row['par_codigo'].'.- '.$row['par_nombre'].'</option>';
        }
      }

      return $tabla;
    }

    /*--- LISTA DE UNIDADES DE MEDIDA ---*/
    function unidades_medida($insumo){
      $tabla='';
      $lista_umedida=$this->model_insumo->lista_umedida($insumo[0]['par_id']); /// Lista de Unidades de medida

      foreach ($lista_umedida as $row) {
        if($insumo[0]['ins_unidad_medida']==$row['um_descripcion']){
          $tabla.='<option value="'.$row['um_id'].'" selected>'.$row['um_descripcion'].'</option>';
        }
        else{
          $tabla.='<option value="'.$row['um_id'].'">'.$row['um_descripcion'].'</option>';
        }
      }

      return $tabla;
    }

        /*--- LISTA DE PRODUCTOS, ACTIVIDADES (MOD) ---*/
    function list_prod_actividad($com_id,$insumo){
      $tabla='';

        $operaciones=$this->model_producto->lista_form4_x_unidadresponsable($com_id);
        $tabla.='<option value="">Seleccione Actividad</option>';
        foreach($operaciones as $row){
          if($row['prod_id']==$insumo[0]['prod_id']){
            $tabla.='<option value="'.$row['prod_id'].'" selected>ACT. '.$row['prod_cod'].'.- '.$row['prod_producto'].'</option>';
          }
          else{
            $tabla.='<option value="'.$row['prod_id'].'">ACT. '.$row['prod_cod'].'.- '.$row['prod_producto'].'</option>';
          }
        } 

      return $tabla;
    }
    

    /*--- BOTON ESTADO FORM 5 2027 ---*/
/*    public function button_form5($com_id){
      $tabla = '';
      
      // Filtro perimetral elástico: Solo Administradores Nacionales o Configuraciones Regionales activas
      if($this->tp_adm == 1 || $this->conf_form5 == 1){
        $tabla .= ' 
          <a href="#" data-toggle="modal" data-target="#modal_importar_f5" class="btn btn-default importar_f5" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
            <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO REQUERIMIENTOS.Xls</b>
          </a>';
      }

      // 🛠️ REPARADO: Corrección de rutas, nombres y cierre limpio de etiquetas HTML 
      $tabla .= ' 
        <a href="javascript:abreVentana_poa(\''.site_url("prog/reporte_form5_uresponsable/".$com_id).'\');" class="btn btn-primary" title="IMPRIMIR REPORTE CONSOLIDADO DE INSUMOS" style="font-weight:bold; margin-right:5px;"> 
            <img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/>&nbsp;IMPRIMIR FORM N° 5
        </a>
        
        <!-- 🛠️ REPARADO: Apunta al disparador específico de limpieza financiera de la Unidad sin borrar el Form 4 -->
        <a onclick="eliminar_requerimientos_UnidadReponsable();" class="btn btn-danger" title="ELIMINAR TODOS LOS REQUERIMIENTOS E INSUMOS DE ESTA UNIDAD" style="font-weight:bold;">
            <img src="'.base_url().'assets/Iconos/application_delete.png" WIDTH="20" HEIGHT="20"/>&nbsp;ELIMINAR REQUERIMIENTOS (TODOS)
        </a>

        <a href="'.site_url("admin/proy/list_proy").'" class="btn btn-default" title="SALIR A MENU PRINCIPAL" style="font-weight:bold; background-color: #475569; color: #ffffff; border-color: #475569; transition: all 0.2s ease;">
            <i class="fa fa-arrow-circle-left" style="font-size:14px; margin-right:4px;"></i> SALIR
        </a>';
        
      $tabla .= '<br><br>';
      
      return $tabla;
    }*/

    /*--- ESTILO FORM 5---*/
    public function estilo_tabla_form5(){
      $tabla='';
      $tabla.='
      <style>
      aside{background: #05678B;}
      .table1{
            display: inline-block;
            width:100%;
            max-width:1550px;
            overflow-x: scroll;
            }
      table{font-size: 10px;
            width: 100%;
            max-width:1550px;;
      overflow-x: scroll;
            }
            th{
              padding: 1.4px;
              text-align: center;
              font-size: 10px;
            }
            #mdialTamanio{
          width: 80% !important;
        }
        #mdialTamanio2{
          width: 55% !important;
        }
        #dialog_subirr{
          width: 45% !important;
        }
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





  //// ======== CABECERA Y PIE PARA LOS REPORTES POA 2024
  //// Cabecera Reporte form 3, 4 y 5
    public function stylo_cabecera() {
    $tabla='
    <style>
        /* 🌟 MAQUETACIÓN RAÍZ DE CONTROL DE MÁRGENES (Sincronizado al 100% con tu imagen) */
        .cns-contenedor-ajuste-pagina {
            padding-left: 3mm;
            padding-right: 3mm;
            width: 97%;
        }
        
        .cns-cabecera-master { 
            font-family: helvetica, arial, sans-serif; 
            color: #1e293b; 
            width: 100%; 
            border-collapse: collapse; 
            table-layout: fixed; /* Forzado geométrico de celdas */
        }
        
        .cns-txt-title { font-size: 12px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .cns-txt-sub { font-size: 7.5px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.2px; }
        
        /* Línea Verde Institucional Sincronizada */
        .cns-linea-verde { 
            height: 2px; 
            background: #000000; 
            width: 100%; 
            margin-top: 3px;
            margin-bottom: 5px; 
        }
        
        /* Bloque Maestro de Datos POA */
        .cns-tbl-ident { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 10px 0 5px 0; 
            table-layout: fixed; 
        }
        .cns-tbl-ident td { padding: 4px 6px; font-size: 7.5px; border: 0.9px solid #C2C2C2; vertical-align: middle; color: #000000; }
        .cns-tbl-ident td.cns-lbl { background: #EBEBEB; font-weight: bold; color: #000000; width: 22%; height:1.3%; text-transform: uppercase; }
        .cns-tbl-ident td.cns-val { background: #ffffff; font-weight: 600; color: #000000; width: 78%; }
    </style>';

    return $tabla;
    }

    //// Cabecera Reporte POA
    public function cabecera($datos_poa, $tp_rep) {
    /// datos_poa: informacion desde componente -> proy -> apertura
    /// tp_rep : 3 (Foda), 4 (Actividades), 5 (requerimientos), 0 (consolidado ppto)
    $comp = '';
    $titulo_rep = '';
    $titulo_form = '';
    
    if ($tp_rep == 0) {
        if ($datos_poa[0]['aper_proy_estado'] == 1) {
            $titulo_rep  = 'CONSOLIDADO POA PRESUPUESTO';
            $titulo_form = 'PPTO. ANTEPROYECTO';
        } else {
            $titulo_rep  = 'CONSOLIDADO POA PRESUPUESTO - APROBADO';
            $titulo_form = 'PPTO. APROBADO - POA';
        }
    } 
    elseif ($tp_rep == 3) {
        $titulo_rep  = 'ANALISIS DE PROBLEMAS Y CAUSAS';
        $titulo_form = 'FORMULARIO SPO N° 3';
        $comp        = '';
    } 
    else {
        $estado = '';
        if ($datos_poa[0]['aper_proy_estado'] == 1) {
            $estado = '(ANTEPROYECTO)';
        } 

        if ($tp_rep == 4) {
            $titulo_rep  = 'ACTIVIDADES ' . $estado;
            $titulo_form = 'FORMULARIO SPO N° 4';
        } elseif ($tp_rep == 5) {
            $titulo_rep  = 'REQUERIMIENTOS ' . $estado;
            $titulo_form = 'FORMULARIO SPO N° 5';
        }

        $comp = '
        <tr>
            <td class="cns-lbl">UNIDAD RESPONSABLE</td>
            <td class="cns-val">' . $datos_poa[0]['serv_cod'] . ' ' . $datos_poa[0]['tipo_subactividad'] . ' ' . strtoupper($datos_poa[0]['serv_descripcion']) . '</td>
        </tr>';
    }

    $tabla = '';
    $tabla .= ''.$this->stylo_cabecera().'
    <div class="cns-contenedor-ajuste-pagina">
        <table class="cns-cabecera-master">
            <tr>
                <td style="width: 65%; text-align: left; vertical-align: bottom; padding-bottom: 2px; font-size:8px;">
                    <span class="cns-txt-title">&nbsp;&nbsp;' . strtoupper($this->session->userdata('entidad')) . '</span><br>
                    <span class="cns-txt-sub">DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</span>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: bottom; font-size: 8px; color: #475569; font-family: courier; font-weight: bold; padding-bottom: 2px;">
                    ' . date("d") . ' de ' . $this->mes[ltrim(date("m"), "0")] . ' de ' . date("Y") . '
                </td>
            </tr>
        </table>

        <!-- 🌟 REPARADO: La línea verde ahora se confina de forma exacta a los 3mm laterales -->
        <div class="cns-linea-verde"></div>

        <!-- 2. NÚCLEO DE TÍTULOS Y CÓDIGO QR -->
        <table class="cns-cabecera-master" style="margin-top: 5px;">
            <tr>
                <td style="width: 15%; text-align: center; vertical-align: middle;">';
                if ($datos_poa[0]['proy_estado'] == 4 && $this->gestion > 2025) {
                    $tabla .= '<qrcode value="' . $this->session->userdata('rd_poa') . '" style="border: none; width: 11mm; color: #475569;"></qrcode><br>
                               <span style="font-size: 5.5px; font-weight: bold; color: #475569; display: block; margin-top: 2px;">POA APROBADO</span>';
                }
                $tabla .= '
                </td>
                <td style="width: 70%; text-align: center; vertical-align: middle; padding: 0 5px;">
                    <span style="font-size: 17px; font-weight: bold; color: #0f172a; display: block; text-transform: uppercase; letter-spacing: 0.3px;">PLAN OPERATIVO ANUAL GESTIÓN - ' . $this->gestion . '</span><br>
                    <span style="font-size: 13px; font-weight: 500; color: #475569; display: block; margin-top: 3px; letter-spacing: 0.2px;">' . strtoupper($titulo_rep) . '</span>
                </td>
                <td style="width: 15%; text-align: right; vertical-align: middle;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 4px 5px; background: #000000; color: #ffffff; font-weight: bold; font-size: 8px; text-align: center; border: 0.5px solid #475569; text-transform: uppercase;">
                                ' . strtoupper($titulo_form) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 3. CUADRO FORMAL DE IDENTIFICACIÓN RELACIONAL POA -->
        <table class="cns-tbl-ident">
            <tr>
                <td class="cns-lbl">REGIONAL / DEPARTAMENTO</td>
                <td class="cns-val">' . $datos_poa[0]['dep_cod'] . ' ' . strtoupper($datos_poa[0]['dep_departamento']) . '</td>
            </tr>
            <tr>
                <td class="cns-lbl">UNIDAD EJECUTORA</td>
                <td class="cns-val">' . $datos_poa[0]['dist_cod'] . ' ' . strtoupper($datos_poa[0]['dist_distrital']) . '</td>
            </tr>
            <tr>';
            if ($datos_poa[0]['tp_id'] == 4) {
                $tabla .= '
                <td class="cns-lbl">CAT. PROGRAMÁTICA ' . $this->gestion . '</td>
                <td class="cns-val">' . $datos_poa[0]['aper_programa'] . ' ' . $datos_poa[0]['aper_proyecto'] . ' ' . $datos_poa[0]['aper_actividad'] . ' - ' . $datos_poa[0]['tipo'] . ' ' . strtoupper($datos_poa[0]['proy_nombre']) . ' [' . strtoupper($datos_poa[0]['abrev']) . ']</td>';
            } else {
                $tabla .= '
                <td class="cns-lbl">PROYECTO</td>
                <td class="cns-val">' . $datos_poa[0]['aper_programa'] . ' ' . $datos_poa[0]['proy_sisin'] . ' ' . $datos_poa[0]['aper_actividad'] . ' - ' . strtoupper($datos_poa[0]['proy_nombre']) . '</td>';
            }
            $tabla .= '
            </tr>
            ' . $comp . '
        </table>
    </div>';
    
    return $tabla;
  }



  //// Cabecera Reporte BOLSA
  public function cabecera_bolsa($datos_prog_bolsa,$datos_uniresp){
    $titulo_rep = '';
    $titulo_form = '';
    $estado = '';
        if ($datos_poa[0]['aper_proy_estado'] == 1) {
            $estado = '(ANTEPROYECTO)';
        } 
        $titulo_rep  = 'REQUERIMIENTOS ' . $estado;
        $titulo_form = 'FORMULARIO SPO N° 5';

    $tabla='';
    $tabla .= ''.$this->stylo_cabecera().'
    <div class="cns-contenedor-ajuste-pagina">
        <table class="cns-cabecera-master">
            <tr>
                <td style="width: 65%; text-align: left; vertical-align: bottom; padding-bottom: 2px; font-size:8px;">
                    <span class="cns-txt-title">&nbsp;&nbsp;' . strtoupper($this->session->userdata('entidad')) . '</span><br>
                    <span class="cns-txt-sub">DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</span>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: bottom; font-size: 8px; color: #475569; font-family: courier; font-weight: bold; padding-bottom: 2px;">
                    ' . date("d") . ' de ' . $this->mes[ltrim(date("m"), "0")] . ' de ' . date("Y") . '
                </td>
            </tr>
        </table>

        <!-- 🌟 REPARADO: La línea verde ahora se confina de forma exacta a los 3mm laterales -->
        <div class="cns-linea-verde"></div>

        <!-- 2. NÚCLEO DE TÍTULOS Y CÓDIGO QR -->
        <table class="cns-cabecera-master" style="margin-top: 5px;">
            <tr>
                <td style="width: 15%; text-align: center; vertical-align: middle;">';
                if ($datos_prog_bolsa[0]['proy_estado'] == 4 && $this->gestion > 2025) {
                    $tabla .= '<qrcode value="' . $this->session->userdata('rd_poa') . '" style="border: none; width: 11mm; color: #475569;"></qrcode><br>
                               <span style="font-size: 5.5px; font-weight: bold; color: #475569; display: block; margin-top: 2px;">POA APROBADO</span>';
                }
                $tabla .= '
                </td>
                <td style="width: 70%; text-align: center; vertical-align: middle; padding: 0 5px;">
                    <span style="font-size: 17px; font-weight: bold; color: #0f172a; display: block; text-transform: uppercase; letter-spacing: 0.3px;">PLAN OPERATIVO ANUAL GESTIÓN - ' . $this->gestion . '</span><br>
                    <span style="font-size: 13px; font-weight: 500; color: #475569; display: block; margin-top: 3px; letter-spacing: 0.2px;">' . strtoupper($titulo_rep) . '</span>
                </td>
                <td style="width: 15%; text-align: right; vertical-align: middle;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding: 4px 5px; background: #000000; color: #ffffff; font-weight: bold; font-size: 8px; text-align: center; border: 0.5px solid #475569; text-transform: uppercase;">
                                ' . strtoupper($titulo_form) . '
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- 3. CUADRO FORMAL DE IDENTIFICACIÓN RELACIONAL POA -->
        <table class="cns-tbl-ident">
            <tr>
                <td class="cns-lbl">REGIONAL / DEPARTAMENTO</td>
                <td class="cns-val">'.$datos_prog_bolsa[0]['dep_cod'].' '.strtoupper ($datos_prog_bolsa[0]['dep_departamento']).'</td>
            </tr>
            <tr>
                <td class="cns-lbl">UNIDAD EJECUTORA</td>
                <td class="cns-val">'.$datos_prog_bolsa[0]['dist_cod'].' '.strtoupper ($datos_prog_bolsa[0]['dist_distrital']).'</td>
            </tr>
          
            <tr>
                <td class="cns-lbl">CAT. PROGRAMÁTICA ' . $this->gestion . '</td>
                <td class="cns-val"><b>'.$datos_prog_bolsa[0]['aper_programa'].''.$datos_prog_bolsa[0]['aper_proyecto'].''.$datos_prog_bolsa[0]['aper_actividad'].' - '.strtoupper ($datos_prog_bolsa[0]['proy_nombre']).' '.$datos_prog_bolsa[0]['abrev'].'</b></td>
            </tr>
            <tr>
                <td class="cns-lbl">UNIDAD RESPONSABLE</td>
                <td class="cns-val"><b>'.$datos_uniresp[0]['serv_cod'].' '.$datos_uniresp[0]['tipo_subactividad'].' '.$datos_uniresp[0]['serv_descripcion'].'</b></td>
            </tr>
        
        </table>
    </div>';


    return $tabla;
  }

/*------ PIE FODA - REPORTE -----*/
public function pie_foda(){
    $tabla = '';
    $tabla .= '    
      <!-- 🌟 SE CONSERVA TU LÓGICA DE DISTRIBUCIÓN: Estructura original al 97% de ancho horizontal -->
      <table border="0" cellpadding="0" cellspacing="0" style="width: 97%; border-collapse: collapse; font-family: helvetica, arial, sans-serif;" align="center">
        <tr>
          <!-- 1. BLOQUE ELABORADO POR (50% DEL ANCHO DE LA HOJA) -->
          <td style="width: 50%; padding-right: 8px; vertical-align: top;">
              <!-- Réplica exacta del esqueleto de la tabla maestra .cns-tbl-ident -->
              <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 0.7px solid #cbd5e1; background: #ffffff;">
                  <tr>
                      <!-- Cabecera de caja formal idéntica a .cns-lbl (Fondo plomo claro, letra plomo oscuro) -->
                      <td style="width: 100%; height: 8px; font-size: 7.5px; font-weight: bold; background: #EBEBEB; color: #000000; padding: 4px 6px; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 0.7px solid #cbd5e1; text-align: left;">
                          ELABORADO POR:
                      </td>
                  </tr>
                  <tr>
                      <!-- Espacio de rúbrica idéntico a .cns-val (Despejado con línea de guía fina) -->
                      <td align="center" style="height: 40px; vertical-align: bottom; padding-bottom: 6px; background: #ffffff;">
                          <span style="font-size: 6.5px; font-weight: 600; color: #000000; text-transform: uppercase; letter-spacing: 0.2px;">Firma y Sello Aclaratorio</span>
                      </td>
                  </tr>
              </table>
          </td>
          
          <!-- 2. BLOQUE APROBADO POR (50% DEL ANCHO DE LA HOJA) -->
          <td style="width: 50%; padding-left: 8px; vertical-align: top;">
              <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse; border: 0.7px solid #cbd5e1; background: #ffffff;">
                  <tr>
                      <!-- Cabecera de caja formal idéntica a .cns-lbl -->
                      <td style="width: 100%; height: 8px; font-size: 7.5px; font-weight: bold; background: #EBEBEB; color: #000000; padding: 4px 6px; text-transform: uppercase; letter-spacing: 0.3px; border-bottom: 0.7px solid #cbd5e1; text-align: left;">
                          APROBADO POR:
                      </td>
                  </tr>
                  <tr>
                      <!-- Espacio de rúbrica idéntico a .cns-val (Despejado con línea de guía fina) -->
                      <td align="center" style="height: 40px; vertical-align: bottom; padding-bottom: 6px; background: #ffffff;">
                          <span style="font-size: 6.5px; font-weight: 600; color: #000000; text-transform: uppercase; letter-spacing: 0.2px;">Firma y Sello Aclaratorio</span>
                      </td>
                  </tr>
              </table>
          </td>
        </tr>
        
        <tr>
          <td colspan="2" style="height: 9px;"></td>
        </tr>
        
        <!-- FILA DE METADATOS DE SEGURIDAD IDÉNTICA Y SINCRO AL RAS DE LA CABECERA -->
        <tr style="font-size: 7.5px; color: #475569; font-weight: bold;">
          <td style="text-align: left; vertical-align: middle; text-transform: uppercase; color: #64748b; font-family: courier; font-size: 7px; letter-spacing: 0.2px;">
            ' . $this->security->xss_clean($this->session->userdata('sistema')) . '
          </td>
          <!-- Inmunización tipográfica obligatoria a Courier para evitar quiebres en TCPDF -->
          <td style="width: 20%; text-align: right; vertical-align: middle; font-family: courier; font-size: 7.5px; color: #334155;">
                pág. [[page_cu]]/[[page_nb]]
          </td>
        </tr>
        
        <tr>
            <td colspan="2" style="height: 5px;"></td>
        </tr>
      </table>';

    return $tabla;
  }


  /*------ PIE FORM - REPORTE -----*/
  public function pie_form($proyecto){
    $firma1='REPRESENTANTE DE AREA REGIONAL';$firma2='SERVICIOS GENERALES / JEFATURA MEDICA';$firma3='ADMINISTRADOR REGIONAL';
    if($proyecto[0]['dist_id']==22){ /// Oficina Nacional
      $firma1='JEFATURA DE UNIDAD O AREA';$firma2='JEFATURA DE DEPARTAMENTO';$firma3='GERENCIA DE AREA';
    }

    $tabla='';
    $tabla .= '
      <!-- 🌟 SE CONSERVA TU LÓGICA ORIGINAL: Estructura al 98% centrada con 3 columnas simétricas -->
      <table border="0" cellpadding="0" cellspacing="0" style="width:98%; border-collapse: collapse; font-family: helvetica, arial, sans-serif;" align="center">
          <tr>
            <!-- 1. BLOQUE JEFATURA DE UNIDAD O ÁREA (33%) -->
            <td style="width: 33%; padding-right: 5px; vertical-align: top;">
                <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse: collapse; border: 0.5px solid #cbd5e1; background: #ffffff;">
                    <tr>
                        <td style="width:100%; height:9px; font-size: 6.5px; font-weight: bold; background: #EBEBEB; color: #000000; padding: 4px 3px; text-align: center; border-bottom: 0.5px solid #cbd5e1; text-transform: uppercase; line-height: 1.2; vertical-align: middle;">
                            <b>'.$firma1.'</b>
                        </td>
                    </tr>
                    <tr>
                      <td align="center" style="font-size: 6.5px; font-weight: bold; height: 50px; vertical-align: bottom; padding-bottom: 4px; color: #000000;">
                          <b>FIRMA y SELLO</b>
                      </td>
                    </tr>
                </table>
            </td>
            
            <!-- 2. BLOQUE JEFATURA DE DEPARTAMENTOS (34% Ajustado por simetría de margen) -->
            <td style="width: 34%; padding-right: 5px; vertical-align: top;">
                <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse: collapse; border: 0.5px solid #cbd5e1; background: #ffffff;">
                    <tr>
                      <td style="width:100%; height:9px; font-size: 6.5px; font-weight: bold; background: #EBEBEB; color: #000000; padding: 4px 3px; text-align: center; border-bottom: 0.5px solid #cbd5e1; text-transform: uppercase; line-height: 1.2; vertical-align: middle;">
                          <b>'.$firma2.'</b>
                      </td>
                    </tr>
                    <tr>
                      <td align="center" style="font-size: 6.5px; font-weight: bold; height: 50px; vertical-align: bottom; padding-bottom: 4px; color: #000000;">
                          <b>FIRMA y SELLO</b>
                      </td>
                    </tr>
                </table>
            </td>
            
            <!-- 3. BLOQUE GERENCIA GENERAL (33%) -->
            <td style="width: 33%; vertical-align: top;">
                <table cellpadding="0" cellspacing="0" style="width:100%; border-collapse: collapse; border: 0.5px solid #cbd5e1; background: #ffffff;">
                    <tr>
                      <td style="width:100%; height:9px; font-size: 6.5px; font-weight: bold; background: #EBEBEB; color: #000000; padding: 4px 3px; text-align: center; border-bottom: 0.5px solid #cbd5e1; text-transform: uppercase; line-height: 1.2; vertical-align: middle;">
                          <b>'.$firma3.'</b>
                      </td>
                    </tr>
                    <tr>
                      <td align="center" style="font-size: 6.5px; font-weight: bold; height: 50px; vertical-align: bottom; padding-bottom: 4px; color: #000000;">
                          <b>FIRMA y SELLO</b>
                      </td>
                    </tr>
                </table>
            </td>
          </tr>


          <!-- 🌟 SE CONSERVA TU LÓGICA DE CONDICIONALES DE SESIÓN Y METADATOS -->
          <tr style="font-size: 6.5px; color: #475569; font-weight: bold;">
            <td style="width: 33%; text-align: left; height:20px; vertical-align: middle; text-transform: uppercase;font-family: courier;">';
              if($proyecto[0]['aper_proy_estado']==1){
                $tabla.='POA - '.$this->session->userdata('gestion');
              }
              else{
                $tabla.='<span style="color: #1e293b;">POA - '.$this->session->userdata('gestion').' '.strtoupper($this->session->userdata('rd_poa')).'</span>';
              } 
            $tabla.='
            </td>
            <td style="width: 34%; text-align: center; vertical-align: middle; text-transform: uppercase; color: #64748b; font-family: courier;">
              '.$this->session->userdata('sistema').'
            </td>
            <!-- 🛠️ INMUNIZADO: Se fuerza el uso de Courier para evitar quiebres en TCPDF -->
            <td style="width: 33%; text-align: right; vertical-align: middle; font-family: courier;">
                pag. [[page_cu]]/[[page_nb]]
            </td>
          </tr>
      </table>';

    return $tabla;
  }


  //// Caratula POA 2022 (GASTO CORRIENTE)
  public function caratula_poa_gacorriente($proyecto){
    $tabla='';
    $tabla.='
        <page orientation="portrait" backtop="50mm" backbottom="10mm" backleft="5mm" backright="5mm" pagegroup="new">
            <page_header>
                <br><div class="verde"></div>
                 
                  <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                    <tr style="width: 100%; border: solid 0px black; text-align: center; font-size: 8pt; font-style: oblique;">
                      <td width=20%; text-align:center;"">
                      </td>
                      <td width=60%; align=left>
                        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                          <tr>
                            <td style="width:100%; height: 1.2%; font-size: 35px; font-family: Arial;" align="center"><b>'.$this->session->userdata('entidad').'</b></td>
                          </tr>
                          <tr>
                            <td style="width:100%; height: 1.2%; font-size: 30px; font-family: Arial;" align="center">'.strtoupper($proyecto[0]['dep_departamento']).'</td>
                          </tr>
                          <tr>
                            <td style="width:100%; height: 1.2%; font-size: 25px; font-family: Arial;" align="center">'.strtoupper($proyecto[0]['dist_distrital']).'</td>
                          </tr>
                        </table>
                      </td>
                      <td width=20%; align=left style="font-size: 8px;">
                      </td>
                    </tr>
                  </table>
                  <hr style="border:2px;">
                
            </page_header>
            <page_footer>
            <hr>
            <div style="width:100%; height: 1.2%; font-size: 9px; font-family: Arial;">&nbsp;&nbsp;&nbsp;<b>SISTEMA DE PLANIFICACIÓN DE SALUD - SIIPLAS @Wmendoza7</b><br><br></div>
            </page_footer>
                <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                  <tr>
                    <td style="width:100%; height: 50%; font-size: 18pt; text-align:center">';
                     if($proyecto[0]['img']!=''){
                          $tabla.='<img src="'.getcwd().'/fotos/'.$proyecto[0]['img'].'" class="img-responsive" style="width:70%; height:90%;" align=center />';
                      }
                      else{
                          $tabla.='<img src="'.getcwd().'/fotos/simagen.jpg" class="img-responsive" style="width:50%; height:60%;"/>';
                      }
                      $tabla.='
                      <br>
                    </td>
                  </tr>
                  <tr>
                    <td style="width:100%; height: 1.2%; font-size: 12pt;" align="center">
                      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                        <tr>
                          <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 60px;" align="center"><b>POA '.$this->gestion.'</b></td>
                        </tr>';
                          if($proyecto[0]['tn_id']!=0){ 
                              $tabla.='
                              <tr>
                                  <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 26px;" align="center"><br>'.$proyecto[0]['tipo_adm'].'</td>
                              </tr>';
                          }
                        $tabla.='
                        <tr>
                          <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 26px;" align="center"><b>'.$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev'].'</b></td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table><br>';
                 if($proyecto[0]['tn_id']==0){
                    $UnidadesResponsables=$this->model_componente->lista_UnidadesResponsables($proyecto[0]['proy_id']);
                     $tabla.="
                      <table border=0 style='width:100%;' align=center>
                        <tr>
                          <td style='width:100%;'>
                            <hr style='border:2px;'>
                            <ul>";
                            $cont=0;
                              foreach($UnidadesResponsables as $row){
                                if(count($this->model_producto->productos_nro($row['com_id']))!=0){
                                  $tabla.="<li style='font-family: Arial;height: 12px; font-size: 12.2px; text-align:justify'>".$row['tipo_subactividad'].' '.$row['serv_descripcion']."</li>";
                                }
                              }
                    $tabla.="</ul>
                            <hr style='border:2px;'>
                          </td>
                        </tr>
                      </table>";
                  }
        $tabla.='
        </page>';

      return $tabla;
  }


  //// Caratula POA 2022 (GASTO CORRIENTE)
  public function caratula_poa_pinversion($proyecto){
    $imagen=$this->model_proyecto->get_img_ficha_tecnica($proyecto[0]['proy_id']);
    $tabla='';
    $tabla.='
        <page orientation="portrait" backtop="50mm" backbottom="10mm" backleft="5mm" backright="5mm" pagegroup="new">
            <page_header>
                <br><div class="verde"></div>
                    <table class="page_header" border="0">
                      <tr>
                        <td style="width: 100%; text-align: left">
                          <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:99.5%;">
                              <tr style="width: 100%; border: solid 0px black; text-align: center; font-size: 8pt; font-style: oblique;">
                                <td width=20%; text-align:center;"">
                                </td>
                                <td width=60%; align=left>
                                  <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                                    <tr>
                                      <td style="width:100%; height: 1.2%; font-size: 25pt; font-family: Arial;" align="center"><b>'.$this->session->userdata('entidad').'</b></td>
                                    </tr>
                                    <tr>
                                      <td style="width:100%; height: 1.2%; font-size: 20pt; font-family: Arial;" align="center">'.strtoupper($proyecto[0]['dep_departamento']).'</td>
                                    </tr>
                                    <tr>
                                      <td style="width:100%; height: 1.2%; font-size: 15pt; font-family: Arial;" align="center">'.strtoupper($proyecto[0]['dist_distrital']).'</td>
                                    </tr>
                                  </table>
                                </td>
                                <td width=20%; align=left style="font-size: 8px;">
                                </td>
                              </tr>
                          </table>
                        </td>
                      </tr>
                  </table><br>
                  <div align="center"></div>
            </page_header>
            <page_footer>
            <hr>
            <div style="width:100%; height: 1.2%; font-size: 9px; font-family: Arial;">&nbsp;&nbsp;&nbsp;<b>SISTEMA DE PLANIFICACIÓN DE SALUD - SIIPLAS @Wmendoza7</b><br><br></div>
            </page_footer>
              <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                <tr>
                  <td style="width:100%; height: 50%; font-size: 18pt;" align="center">';
                    if(count($imagen)!=0){
                      if($imagen[0]['tp']==1){
                        $tabla.='<img src="'.getcwd().'/fotos_proyectos/'.$imagen[0]['imagen'].'" class="img-responsive" style="width:75%; height:90%;"/><br>';
                      }
                      else{
                        $tabla.='<img src="'.getcwd().'/fotos/209-6b01a.JPG" class="img-responsive" style="width:50%; height:100%;"/><br>';
                      }
                    }
                    else{
                      $tabla.='<img src="'.getcwd().'/fotos/209-6b01a.JPG" class="img-responsive" style="width:50%; height:100%;"/><br>';
                    }
                  $tabla.='
                    
                    <br>
                  </td>
                </tr>
                <tr>
                  <td style="width:100%; height: 1.2%; font-size: 12pt;" align="center">
                      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                        <tr>
                          <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 55px;" align="center"><b>POA '.$this->gestion.'</b></td>
                        </tr>
                        <tr>
                          <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 35px;" align="center">PROYECTO DE INVERSI&Oacute;N</td>
                        </tr>
                        <tr>
                          <td style="font-family: Arial; width:100%; height: 1.2px; font-size: 25px;" align="center"><b><br>'.$proyecto[0]['proy_sisin'].' - '.$proyecto[0]['proy_nombre'].'</b></td>
                        </tr>
                      </table>
                  </td>
                </tr>
              </table>';
                
        $tabla.='
        </page>';

      return $tabla;
  }

   /*------ LISTA DE FODA - REPORTE 2027 -----*/
    public function reporte_datos_foda($proy_id){
        // 1. Recuperamos el pool de problemas autorizados del proyecto
        $problemas = $this->model_analisis_situacion->list_analisis_problemas_reporte($proy_id); 
        $tabla = '';
        $tabla .= '
          <table cellpadding="0" cellspacing="0" class="tabla" border=0.05 style="width:100%;">
            <thead>
              <tr style="font-size: 6px;" bgcolor="#eceaea" align=center>
                <th style="width: 2%;height:18px;">#</th>
                <th style="width: 33.5%;">PROBLEMAS IDENTIFICADOS</th>
                <th style="width: 32%;">CAUSAS DE LOS PROBLEMAS</th>
                <th style="width: 32%;">ACCIONES RECOMENDADAS (SOLUCIONES)</th>
              </tr>
            </thead>
            <tbody>';
            
            $nro = 0;
            if(!empty($problemas)) {
                foreach($problemas as $row){
                    $nro++;
                    // Extracción asíncrona de la subtabla relacional (Causas - Acciones)
                    $causas = $this->model_analisis_situacion->lista_causas_acciones($row['prob_id']);
                    // Formateo seguro para evitar Notice de strings vacíos en PHP 5.6
                    $txt_problema = !empty($row['problema']) ? trim(strtoupper($this->security->xss_clean($row['problema']))) : '';
                    
                    // 🌟 CONTROL DE INTEGRIDAD: Evaluamos si registra causas indexadas
                    if(!empty($causas) && count($causas) > 0) {
                        
                        $txt_causas  = '';
                        $txt_acciones = '';
                        $sub_item = 0;
                        
                        // Si hay múltiples causas por problema, las listamos limpiamente con viñetas numéricas
                        foreach($causas as $rowc) {
                            $sub_item++;
                            $c_desc = !empty($rowc['causas']) ? trim(strtoupper($this->security->xss_clean($rowc['causas']))) : '';
                            $a_desc = !empty($rowc['acciones']) ? trim(strtoupper($this->security->xss_clean($rowc['acciones']))) : '';
                            
                            $prefix = (count($causas) > 1) ? $sub_item . ". " : "";
                            
                            $txt_causas   .= $prefix . $c_desc . "<br>";
                            $txt_acciones .= $prefix . $a_desc . "<br>";
                        }
                    } else {
                        // Hilera de protección por si el analista dejó el problema sin causas registradas
                        $txt_causas  = '<span >📋 SIN REGISTRO DE CAUSAS</span>';
                        $txt_acciones = '<span >📋 SIN ACCIONES RECOMENDADAS</span>';
                    }

                    $tabla .= '
                    <tr>
                        <td style="height: 15px;width: 2%; text-align:center;"><b>' . $nro . '</b></td>
                        <td style="text-align: justify; font-weight: 500;width: 33.5%;">' . $txt_problema . '</td>
                        <td style="text-align: justify;width: 32%;">' . $txt_causas . '</td>
                        <td style="text-align: justify; font-weight: 600; color: #1e3a8a;width: 32%;">' . $txt_acciones . '</td>
                    </tr>';
                }
            } else {
                // Alerta formal por si toda la matriz FODA de la distrital viene vacía
                $tabla .= '
                <tr>
                    <td class="cns-col-nro-foda">-</td>
                    <td colspan="3" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px 0;">
                        No se identificaron problemas operacionales o análisis de situación registrados para la presente gestión.
                    </td>
                </tr>';
            }
            
            $tabla .= '
            </tbody>
        </table>';

        return $tabla;
    }

  /*----- REPORTE FORMULARIO 4 (2027 - Gasto Corriente) ----*/
  public function rep_formulario_N4_Uresponsable($componente){
    if($componente[0]['por_id']==0){
      $lista_form4_uresp=$this->model_producto->get_lista_form4_uresp_consolidado($componente[0]['com_id']); //// lista form4 + bolsas
    }
    else{
      $lista_form4_uresp=$this->model_producto->get_lista_form4_uresp_consolidado_programa_bolsas($componente[0]['com_id']); //// lista form4 de bolsas
    }
    
    $tabla='';
    $tabla .= '
        <div style="font-size: 8px; font-weight: bold; color: #1e293b; text-transform: uppercase; font-family: helvetica;">DETALLE :</div>
      <table cellpadding="0" cellspacing="0" class="tabla" border=0.05 style="width:100%;">
        <thead>
          <tr style="font-size: 6px;" bgcolor="#eceaea" align=center>
            <th style="width:2.5%;height:18px;">PROG.</th>
            <th style="width: 2.5%;">COD.<br>ACP.</th>
            <th style="width: 2.5%;">COD.<br>OPE.</th>
            <th style="width: 2.5%;">COD.<br>ACT.</th> 
            <th style="width: 15%;">ACTIVIDAD</th>
            <th style="width: 12.5%;">RESULTADO</th>
            <th style="width: 10%;">UNIDAD RESPONSABLE</th>
            <th style="width: 12%;">INDICADOR</th>
            <th style="width: 3.5%;">META</th>
            <th style="width: 2.3%;">ENE.</th>
            <th style="width: 2.3%;">FEB.</th>
            <th style="width: 2.3%;">MAR.</th>
            <th style="width: 2.3%;">ABR.</th>
            <th style="width: 2.3%;">MAY.</th>
            <th style="width: 2.3%;">JUN.</th>
            <th style="width: 2.3%;">JUL.</th>
            <th style="width: 2.3%;">AGO.</th>
            <th style="width: 2.3%;">SEPT.</th>
            <th style="width: 2.3%;">OCT.</th>
            <th style="width: 2.3%;">NOV.</th>
            <th style="width: 2.3%;">DIC.</th>
            <th style="width: 8.7%;">MEDIO DE VERIFICACIÓN</th> 
          </tr>
        </thead>
        <tbody>';

        $nro = 0;
        if (!empty($lista_form4_uresp)) {
            foreach ($lista_form4_uresp as $rowp) {
                $color = ''; 
                $tp = ''; 
                $color_uni = '';
                
                if ($rowp['indi_id'] == 1) {
                    if ($rowp['total_anual'] != $rowp['prod_meta']) {
                        $color = 'background-color: #fef2f2;'; 
                    }
                } elseif ($rowp['indi_id'] == 2) {
                    $tp = '%';
                    if ($rowp['mt_id'] == 3) {
                        if ($rowp['total_anual'] != $rowp['prod_meta']) {
                            $color = 'background-color: #fef2f2;';
                        }
                    }
                }

                $color_or = '';
                if ($rowp['or_id'] == 0) {
                    $color_or = 'background-color: #ffeeec;'; 
                }

                if ($rowp['uni_resp'] != 0) {
                    $color_uni = 'background-color: #fefce8;'; 
                }

                $estilo_fila = !empty($color) ? $color : '';

                $nro++;
                $tabla .= '
                <tr style="' . $estilo_fila . ' height: 15px;">
                  <td style="width: 2.5%; font-size: 8px; text-align:center; height: 14px;' . $color_uni . '"><b>'.$rowp['aper_programa'].'</b></td>
                  <td style="width: 2.5%; font-size: 8px; text-align:center;' . $color_uni . '"><b>' . $rowp['og_codigo'] . '</b></td>
                  <td style="width: 2.5%; font-size: 8px; text-align:center;' . $color_uni . '"><b>' . $rowp['or_codigo'] . '</b></td>
                  <td style="width: 2.5%; text-align: center; font-size: 8px; font-weight: 600;' . $color_uni . '"><b>' . $rowp['prod_cod'] . '</b></td>
                  
                  <td style="width: 15%; text-align: justify; font-size: 6.5px; !important;' . $color_uni . '">' . trim(strtoupper($this->security->xss_clean($rowp['prod_producto']))) . '</td>
                  <td style="width: 12.5%; text-align: justify;font-size: 6.5px;' . $color_uni . '">' . trim(strtoupper($this->security->xss_clean($rowp['prod_resultado']))) . '</td>
                  <td style="width: 10%; text-align: left; font-weight: bold; font-size: 6.5px; !important;' . $color_uni . '">' . trim(strtoupper($this->security->xss_clean($rowp['prod_unidades']))) . '</td>
                  <td style="width: 12%; font-size: 6.5px; text-align: justify;' . $color_uni . '">' . trim(strtoupper($this->security->xss_clean($rowp['prod_indicador']))) . '</td>
                  
                  <td style="width: 3.5%;font-size: 6.5px; text-align:right;' . $color_uni . '"><b>' . round($rowp['prod_meta'], 2) . $tp . '</b></td>';
                  
                  // 🔄 AJUSTADO: Columnas mensuales con porcentajes idénticos a la cabecera (2.3%)
                  for ($i = 1; $i <= 12; $i++) { 
                      $tabla .= '<td style="width: 2.3%; font-size: 6.5px; text-align: center;' . $color_uni . '">' . round($rowp['m' . $i], 2) . $tp . '</td>';
                  }

                  $tabla .= '
                  <td style="width: 8.7%; font-size: 6px; text-align: left;' . $color_uni . '">' . trim(strtoupper($this->security->xss_clean($rowp['prod_fuente_verificacion']))) . '</td>
                </tr>';
            }
        } else {
            $tabla .= '
            <tr>
                <td colspan="22" style="text-align: center; color: #94a3b8; font-style: italic; padding: 15px 0; font-size: 7.5px;">
                    📋 Ninguna actividad operativa o requerimiento físico programado para esta unidad organizativa en la gestión 2027.
                </td>
            </tr>';
        }

        $tabla .= '
        </tbody>
      </table>';
    return $tabla;
  }

    /// reporte Formulario N° 5 - Requerimientos 2027
    public function rep_formulario_N5_Uresponsable($lista_insumos){
      $tabla='';
      $tabla.='
            <div style="font-size: 8px; font-weight: bold; color: #1e293b; text-transform: uppercase; font-family: helvetica;">DETALLE :</div>
          <table cellpadding="0" cellspacing="0" class="tabla" border=0.05 style="width:100%;">
              <thead>
              <tr style="font-size: 6px;" bgcolor="#eceaea" align=center>
                <th style="width:1%;height:18px;">#</th>
                <th style="width:2%;">COD.<br>ACT.</th> 
                <th style="width:4%;">PARTIDA</th>
                <th style="width:15%;">DETALLE REQUERIMIENTO</th>
                <th style="width:3%;">UNIDAD</th>
                <th style="width:2.5%;">CANT.</th>
                <th style="width:5%;">PRECIO</th>
                <th style="width:5%;">TOTAL</th>
                <th style="width:5%;">TOTAL CERT.</th>
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
                <th style="width:7%;">OBSERVACI&Oacute;N</th>
              </tr>
              </thead>
              <tbody>';
              $cont = 0; $total=0; $total_cert=0; 
              foreach ($lista_insumos as $row) {
              $cont++;
              $total=$total+$row['ins_costo_total'];
              $total_cert=$total_cert+$row['ins_monto_certificado'];
              $color='';
              $tabla.=
              '<tr style="font-size: 6.5px;" >
                  <td style="width: 1%; font-size: 4.5px; text-align: center;height:14px;">'.$cont.'</td>
                  <td style="width: 2%; text-align: center; font-size: 9px;"><b>'.$row['prod_cod'].'</b></td>
                  <td style="width: 4%; text-align: center;font-size: 8px;"><b>'.$row['par_codigo'].'</b></td>
                  <td style="width: 15%; text-align: left;font-size: 6.6px;">'.strtoupper($row['ins_detalle']).'</td>
                  <td style="width: 5%; text-align: left;font-size: 6.6px;">'.strtoupper($row['ins_unidad_medida']).'</td>
                  <td style="width: 2.5%; text-align: right;font-size: 6.6px;">'.round($row['ins_cant_requerida'],2).'</td>
                  <td style="width: 5%; text-align: right;font-size: 6.6px;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>
                  <td style="width: 5%; text-align: right;font-size: 6.6px;"><b>'.number_format($row['ins_costo_total'], 2, ',', '.').'</b></td>
                  <td style="width: 5%; text-align: right;font-size: 6.6px;" bgcolor="#ecfbf9"><b>'.number_format($row['ins_monto_certificado'], 2, ',', '.').'</b></td>';
                  for ($i=1; $i <=12 ; $i++) { 
                    $tabla.='<td style="width: 4%; text-align: right;font-size: 6.6px;">'.number_format($row['mes_'.$i], 2, ',', '.').'</td>';
                  }
              $tabla.='
                  <td style="width: 7%; text-align: left;font-size: 6px;">'.$row['ins_observacion'].'</td>
                  
              </tr>';
              }

          $tabla.='
              </tbody>
              <tr class="modo1" bgcolor="#eceaea">
                  <td colspan="7" style="height:10px;" ><b>TOTAL PROGRAMADO </b></td>
                  <td style="width: 4%; text-align: right; font-size: 6px;"><b>'.number_format($total, 2, ',', '.').'</b></td>
                  <td style="width: 4%; text-align: right; font-size: 6px;"><b>'.number_format($total_cert, 2, ',', '.').'</b></td>
                  <td colspan="13"></td>
              </tr>
          </table><br>';
      return $tabla;
    }




  /*-------- MENU -----*/
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
?>