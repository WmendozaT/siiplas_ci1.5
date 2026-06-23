<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

class lib_diagnostico_equipamiento extends CI_Controller{

    public function __construct (){
      parent::__construct();
      $this->load->model('mdiagnostico_equipamiento/model_diagnosticoequip');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->entidad   = $this->session->userdata("entidad");
      $this->sistema   = $this->session->userdata("sistema");
      $this->sistema_pie   = $this->session->userdata("sistema_pie");
      $this->usuario   = $this->session->userdata("usuario");
      $this->direccion   = $this->session->userdata("direccion");

    }



    /*------- Listado de formularios -------*/
  public function unidad_ejecutora_seleccionado($equip_id, $dist_id, $tp_adm){
    $get_diagnostico=$this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
    $get_form_distrital = $this->model_diagnosticoequip->get_distrital_formulario_diagnostico_activo($equip_id, $dist_id);
    $establecimientos=$this->model_diagnosticoequip->get_establecimientos_distrital($dist_id,$this->gestion);
    $partidas_gastos = array(
        "149" => "39400 - INSTRUMENTAL MENOR MÉDICO QUIRÚRGICO",
        "173" => "43110 - EQUIPO DE OFICINA Y MUEBLES",
        "174" => "43120 - EQUIPO DE COMPUTACIÓN",
        "175" => "43200 - MAQUINARIA Y EQUIPO DE PRODUCCIÓN",
        "179" => "43330 - MAQUINARIA Y EQUIPO DE TRANSPORTE",
        "181" => "43400 - EQUIPO MÉDICO Y DE LABORATORIO",
        "182" => "43500 - EQUIPO DE COMUNICACIÓN",
        "183" => "43600 - EQUIPO EDUCACIONAL Y RECREATIVO"
    );

    $tabla = '';
    $tabla .= '
    <input name="base" type="hidden" value="'.base_url().'">
    <section id="widget-grid" class="" style="margin-top: 5px;">
        <div class="row">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                
                <!-- 1. ACCIÓN MAESTRA PLURIANUAL: Disparador del Formulario por Modal -->
                <div style="margin-bottom: 15px; text-align: left;">
                    <button type="button" class="btn btn-success btn-sm font-md" data-toggle="modal" data-target="#modal_nuevo_equipamiento" style="font-weight: bold; background: #e67e22; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.15); letter-spacing:0.3px; padding: 6px 15px;">
                        <i class="fa fa-plus-circle"></i> + REGISTRAR NUEVO REQUERIMIENTO DE EQUIPAMIENTO
                    </button>
                </div>

                <!-- 2. JARVISWIDGET ESTILO PREMIUM CORPORATIVO (CNS) -->
                <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-diagnostico-equip" data-widget-editbutton="false" data-widget-colorbutton="false" data-widget-deletebutton="false">
                    <header style="background: #1a237e; color: #ffffff;">
                        <span class="widget-icon"> <i class="fa fa-table" style="color: #ffffff;"></i> </span>
                        <h2 class="font-md" style="font-weight: bold; text-transform: uppercase;"> Matriz General de Requerimientos de Inversión Fija Quinquenal </h2>  
                    </header>
                    
                    <div>
                        <div class="widget-body no-padding" style="background: #ffffff;">
                            <div class="table-responsive" style="width: 100%; overflow-x: auto;">
                                <table id="datatable_fixed_column" class="table table-bordered table-striped table-hover" style="width:100%; font-size: 10.5px; margin-bottom: 0; white-space: nowrap; vertical-align: middle;" border="1">
                                    <thead>
                                        <!-- FILA CABECERA A: BUSCADORES EN CALIENTE POR COLUMNA -->
                                        <tr style="background-color: #f5f5f5;">
                                            <th>#</th> <!-- # -->
                                            <th></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px;" placeholder="🔍 Responsable"/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px;" placeholder="🔍 Establ."/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px; font-weight:bold; color:#0d47a1;" placeholder="🔍 Equipo Médico"/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px;" placeholder="🔍 Servicio"/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px;" placeholder="🔍 Ubicación"/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px; text-align:center;" placeholder="🔍 Compra"/></th>
                                            <th></th> <!-- Cantidad -->
                                            <th></th> <!-- Costo Unit -->
                                            <th></th> <!-- Costo Total -->
                                            <!-- Columnas de Temporalidad Plurianual (No requieren inputs individuales) -->
                                            <th></th><th></th><th></th><th></th><th></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px; text-align:center;" placeholder="🔍 Partida"/></th>
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px;" placeholder="🔍 Observación"/></th>
                                        </tr>                          
                                        
                                        <!-- FILA CABECERA B: TÍTULOS TÉCNICOS OFICIALES DE LA PLANILLA -->
                                        <tr >
                                            <th style="width:1%; text-align:center; vertical-align: middle;">#</th>
                                            <th style="width:5%; text-align:center; vertical-align: middle;"></th>
                                            <th style="width:7%; text-align:center; vertical-align: middle;">RESPONSABLE</th>
                                            <th style="width:8%; text-align:center; vertical-align: middle;">ESTABLECIMIENTO / INVERSIÓN</th>
                                            <th style="width:12%; text-align:center; vertical-align: middle;">NOMBRE DEL EQUIPAMIENTO</th>
                                            <th style="width:8%; text-align:center; vertical-align: middle;">SERVICIO/UNIDAD</th>
                                            <th style="width:8%; text-align:center; vertical-align: middle;">UBICACIÓN FÍSICA</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle;">TIPO COMPRA</th>
                                            <th style="width:3%; text-align:center; vertical-align: middle;">CANT.</th>
                                            <th style="width:6%; text-align:center; vertical-align: middle;">COSTO UNITARIO (Bs.)</th>
                                            <th style="width:6%; text-align:center; vertical-align: middle;">COSTO TOTAL (Bs.)</th>
                                            <!-- Temporalidad Quinquenal de Asignación Física -->
                                            <th style="width:4%; text-align:center; vertical-align: middle; background:#2e7d32;">2026</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle; background:#2e7d32;">2027</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle; background:#2e7d32;">2028</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle; background:#2e7d32;">2029</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle; background:#2e7d32;">2030</th>
                                            <th style="width:4%; text-align:center; vertical-align: middle;">PARTIDA</th>
                                            <th style="width:10%; text-align:center; vertical-align: middle;">OBSERVACIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bdi_equipamiento">';
                                        $nro = 0;
                                        foreach($get_form_distrital as $row){
                                            $nro++;
                                            
                                            // Configuración semántica del nombre del establecimiento según el Tipo de Registro
                                            $establecimiento_detallado = '';
                                            if ($row['tp_registro'] == 1) {
                                                $establecimiento_detallado = '<span class="text-primary" style="font-weight:600;">' . strtoupper($row['tipo_establecimiento'] . ' ' . $row['nombre_establecimiento']) . '</span> <small class="text-muted">[' . strtoupper($row['abrev_establecimiento']) . ']</small>';
                                            } else {
                                                $establecimiento_detallado = '<span class="label label-primary" style="font-size:9px; padding:2px 4px;">PROY. INVERSIÓN</span> <br><small style="white-space:normal; font-weight:600; color:#444;">' . strtoupper($row['nombre_establecimiento']) . '</small>';
                                            }

                                            $tabla .= '
                                            <tr style="height:32px; vertical-align: middle;">
                                                <!-- Columnas Base -->
                                                <td style="text-align:center; font-weight:bold; background:#fafafa; vertical-align: middle;">' . $nro . '</td>
                                                
                                                <!-- 🛠️ BOTONERA DE ACCIÓN ASÍNCRONA (MODAL / ELIMINAR) -->
                                                <td style="text-align:center; vertical-align: middle; white-space: nowrap; padding: 4px;">
                                                    <!-- Botón Modificar: Abre el mismo modal inyectando data-id -->
                                                    <button type="button" class="btn btn-warning btn-xs btn_modificar_equip" 
                                                            data-id="' . $row['form_equip_id'] . '" 
                                                            data-distrital="' . $row['dist_id'] . '" 
                                                            style="background: #e67e22; border: none; font-weight: bold; padding: 2px 6px;" 
                                                            title="Modificar especificaciones de este requerimiento">
                                                        <i class="fa fa-pencil"></i> MODIFICAR
                                                    </button>
                                                    
                                                    <!-- Botón Eliminar: Registra el data-id para disparar confirmación AJAX -->
                                                    <button type="button" class="btn btn-danger btn-xs btn_eliminar_equip" 
                                                            data-id="' . $row['form_equip_id'] . '" 
                                                            style="font-weight: bold; padding: 2px 6px; margin-left:2px;" 
                                                            title="Dar de baja este registro del SIIPLAS">
                                                        <i class="fa fa-trash-o"></i> ELIMINAR
                                                    </button>
                                                </td>
                                                
                                                <td style="vertical-align: middle; white-space:normal; min-width:110px; padding-left:4px;">' . strtoupper($row['responsable']) . '</td>
                                                <td style="vertical-align: middle; white-space:normal; min-width:120px; padding-left:4px;">' . strtoupper($row['nombre_establecimiento']) . '</td>
                                                <td style="vertical-align: middle; padding-left:4px;">' . $establecimiento_detallado . '</td>
                                                <td style="vertical-align: middle; font-weight:600; color:#0d47a1; white-space:normal; min-width:150px; padding-left:4px;">' . strtoupper($row['nombre_equipamiento']) . '</td>
                                                <td style="vertical-align: middle; white-space:normal; min-width:110px; padding-left:4px; color:#555;">' . strtoupper($row['ubicacion_fisica']) . '</td>
                                                <td style="text-align:center; vertical-align: middle;"><span class="badge bg-color-blue text-white" style="font-size:8.5px; padding:2px 4px;">' . strtoupper($row['tp_compra_nombre']) . '</span></td>
                                                
                                                <!-- Celdas Numéricas con Formato Contable -->
                                                <td style="text-align:center; vertical-align: middle; font-weight:bold;">' . intval($row['cantidad']) . '</td>
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px;">' . number_format($row['costo_unitario'], 2, '.', ',') . '</td>
                                                <td style="text-align:right; vertical-align: middle; font-weight:bold; color:#1565c0; padding-right:4px;">' . number_format($row['costo_total'], 2, '.', ',') . '</td>
                                                
                                                <!-- Distribución Temporal Plurianual -->
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2026'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2026'], 2, '.', ',') . '</td>
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2027'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2027'], 2, '.', ',') . '</td>
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2028'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2028'], 2, '.', ',') . '</td>
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2029'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2029'], 2, '.', ',') . '</td>
                                                <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2030'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2030'], 2, '.', ',') . '</td>
                                                
                                                <td style="text-align:center; vertical-align: middle; font-weight:600;">' . $row['par_codigo'] . '</td>
                                                <td style="vertical-align: middle; white-space:normal; min-width:140px; padding:4px; line-height:1.2; color:#666;">' . htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') . '</td>
                                            </tr>';
                                        }
                                        $tabla .= '</tbody>
                                </table>
                               </div>
                            </div>
                           </div>
                          </div>
                        </article>
                    </div>
        </div>
        </section>

        <style>
            #mdialTamanio { width: 85% !important; max-width: 1200px; }
            .smart-form .select select { height: 32px; padding: 5px 8px; }
            .smart-form .textarea textarea { resize: none; }
        </style>
        <div class="modal fade" id="modal_nuevo_equipamiento" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog" id="mdialTamanio">
        <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            
            <!-- CABECERA DEL MODAL -->
            <div class="modal-header" style="background: #1a237e; color: white; padding: 10px 15px;">
                <button type="button" class="close" data-dismiss="modal" id="amcl" title="SALIR FORMULARIO" style="color: white; opacity: 0.8; font-size: 14px;">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i> SALIR FORMULARIO</span>
                </button>
                <h4 class="modal-title" style="font-weight: bold; font-size: 13px; text-transform: uppercase; color: white;">
                    <i class="fa fa-edit"></i> REGISTRO PLURIANUAL DE EQUIPAMIENTO MÉDICO E INDUSTRIAL
                </h4>
            </div>
            
            <!-- CUERPO DEL MODAL -->
            <div class="modal-body" style="padding: 15px; background: #fafafa;">
                <form id="form_nuevo" name="form_nuevo" class="smart-form" method="post" autocomplete="off" style="padding:0; background:transparent;">
                    <!-- Campos de control transaccional -->
                    <input type="hidden" name="form_equip_id" id="form_equip_id" value="0">
                    <input type="hidden" name="equip_id" value="' . $equip_id . '">
                    <input type="hidden" name="dist_id" id="modal_dist_id" value="' . $dist_id . '">
                    
                    <!-- BLOQUE 1: IDENTIFICACIÓN -->
                    <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                        <b>I. IDENTIFICACIÓN INSTITUCIONAL DE ORIGEN</b>
                    </header>
                    <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                        <div class="row">
                            <section class="col col-4">
                                <label class="label"><b>TIPO REGISTRO *</b></label>
                                <label class="select">
                                    <select id="tp_registro" name="tp_registro" style="font-weight: bold; color: #0d47a1;" required>
                                        <option value="1">1.- ESTABLECIMIENTO DE SALUD</option>
                                        <option value="2">2.- PROYECTO DE INVERSIÓN</option>
                                    </select><i></i>
                                </label>
                            </section>
                        </div>
                        
                        <div class="row">
                            <!-- SECCIÓN DINÁMICA: ESTABLECIMIENTO -->
                            <div id="est">
                                <section class="col col-6">
                                    <label class="label"><b>ESTABLECIMIENTO DE SALUD VINCULADO *</b></label>
                                    <label class="select">
                                        <select id="act_id" name="act_id">
                                            <option value="">Seleccione Centro de Salud...</option>';
                                            foreach($establecimientos as $est){
                                                $tabla .= '<option value="'.$est['act_id'].'">'.strtoupper($est['tipo'].' '.$est['act_descripcion']).'</option>';
                                            }
                                            $tabla .= '
                                        </select><i></i>
                                    </label>
                                </section>
                            </div>
                            
                            <!-- SECCIÓN DINÁMICA: PROYECTO INVERSIÓN -->
                            <div id="inv" style="display:none;">
                                <section class="col col-6">
                                    <label class="label"><b>NOMBRE DEL PROYECTO DE INVERSIÓN *</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-folder-open"></i>
                                        <textarea rows="2" name="nombre_inversion" id="nombre_inversion" placeholder="Escriba el nombre oficial del proyecto de inversión..."></textarea>
                                    </label>
                                </section>
                            </div>
                            
                            <section class="col col-6">
                                <label class="label"><b>NOMBRE DEL RESPONSABLE / SOLICITANTE</b></label>
                                <label class="textarea"><i class="icon-append fa fa-user"></i>
                                    <textarea rows="2" name="responsable" id="responsable" placeholder="Ej. Dr. Carlos Murillo - Jefe del Servicio de Quirófano"></textarea>
                                </label>
                            </section>
                        </div>
                    </fieldset>

                    <!-- BLOQUE 2: DETALLE DEL EQUIPO -->
                    <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                        <b>II. ESPECIFICACIONES TÉCNICAS DEL EQUIPO</b>
                    </header>
                        <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                            <div class="row">
                                <section class="col col-4">
                                    <label class="label"><b>NOMBRE DEL EQUIPAMIENTO MÉDICO *</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-tag"></i>
                                        <textarea rows="2" name="nombre_equipamiento" id="nombre_equipamiento" required placeholder="Ej. MONITOR MULTIPARAMÉTRICO DE 5 PARÁMETROS"></textarea>
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>SERVICIO / UNIDAD</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-hospital-o"></i>
                                        <textarea rows="2" name="servicio_unidad" id="servicio_unidad" placeholder="Ej. UNIDAD DE TERAPIA INTENSIVA CORONARIA"></textarea>
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>UBICACIÓN FÍSICA EXACTA</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-map-marker"></i>
                                        <textarea rows="2" name="ubicacion_fisica" id="ubicacion_fisica" placeholder="Ej. Bloque Central - Tercer Piso - Sala de Recuperación"></textarea>
                                    </label>
                                </section>
                                <section class="col col-2">
                                    <label class="label"><b>TIPO DE COMPRA</b></label>
                                    <label class="select">
                                        <select id="tp_compra" name="tp_compra">
                                            <option value="1">NUEVO</option>        
                                            <option value="2">REPOSICIÓN</option>       
                                        </select><i></i>
                                    </label>
                                </section>
                            </div>
                        </fieldset>

                        <!-- BLOQUE 3: COTIZACIÓN REFERENCIAL Y CRONOGRAMA QUINQUENAL -->
                        <header style="border-bottom: 2px solid #2e7d32; color: #1b5e20; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                            <b>III. CRONOGRAMA DE DISTRIBUCIÓN QUINQUENAL Y MATRIZ FINANCIERA (Bs.)</b>
                        </header>
                        <fieldset style="background:transparent; padding:0;">
                            <div class="row">
                                        <section class="col col-4">
                                            <label class="label"><b>PARTIDA PRESUPUESTARIA *</b></label>
                                            <label class="select">
                                                <select id="par_id" name="par_id" required title="SELECCIONE GRUPO DE PARTIDA">
                                                    <option value="">Seleccione Partida...</option>';
                                                    // Recorremos el vector asociativo inyectando el ID de la base de datos en el value
                                                    foreach ($partidas_gastos as $id_partida => $descripcion_partida) {
                                                        $selected = (isset($row['par_id']) && $row['par_id'] == $id_partida) ? 'selected' : '';
                                                        $tabla .= '<option value="' . $id_partida . '" ' . $selected . '>' . $descripcion_partida . '</option>';
                                                    }
                                                    $tabla .= '
                                                </select><i></i>
                                            </label>
                                        </section>

                                        <section class="col col-2">
                                            <label class="label"><b>CANTIDAD TOTAL *</b></label>
                                            <label class="input">
                                                <i class="icon-append fa fa-calculator"></i>
                                                <input type="text" name="cantidad" id="cantidad" value="0" required title="REGISTRAR CANTIDAD TOTAL">
                                            </label>
                                        </section>

                                        <section class="col col-3">
                                            <label class="label"><b>COSTO UNITARIO (Bs.) *</b></label>
                                            <label class="input">
                                                <i class="icon-append fa fa-money"></i>
                                                <input type="text" name="costo_unitario" id="costo_unitario" value="0.00" required title="REGISTRAR COSTO UNITARIO">
                                            </label>
                                        </section>

                                        <section class="col col-3">
                                            <label class="label"><b>COSTO TOTAL CONSOLIDADO</b></label>
                                            <label class="input" style="background: #f4f4f4;">
                                                <i class="icon-append fa fa-lock"></i>
                                                <input type="text" name="costo_total" id="costo_total" value="0.00" readonly style="font-weight: bold; color: #0d47a1; background: #f4f4f4;" title="COSTO TOTAL AUTOMÁTICO">
                                            </label>
                                        </section>
                                    </div>

                                    <!-- SECCIÓN: DISTRIBUCIÓN DE TEMPORALIDAD QUINQUENAL DINÁMICA -->
                                    <header style="border-bottom: 1px dashed #2e7d32; color: #1b5e20; font-weight: bold; font-size: 11px; padding-bottom:3px; margin-top:15px; margin-bottom:10px;">
                                        <b>DISTRIBUCIÓN FINANCIERA POR GESTIÓN (PLAN PLURIANUAL)</b>
                                    </header>
                                    
                                    <div class="row" style="background: #f1f8e9; padding: 12px 0 2px 0; border: 1px dashed #2e7d32; border-radius: 4px; margin: 5px 0 10px 0;">
                                        <section class="col col-2" style="margin-left: 2%;">
                                            <label class="label" style="color:#1b5e20;"><b>TOTAL DISTRIBUIDO</b></label>
                                            <label class="input">
                                                <i class="icon-append fa fa-check-circle"></i>
                                                <input type="text" name="total_prog" id="total_prog" value="0.00" readonly style="font-weight: bold; color: #2e7d32; background: #eaebd8;" title="SUMATORIA DE PLANIFICACIÓN ANUAL">
                                            </label>
                                        </section>';
                                        
                                        // Bucle evolutivo parametrizado según tu diagnóstico activo (2026 a 2030)
                                        for ($i = $get_diagnostico[0]['g_id_inicio']; $i <= $get_diagnostico[0]['g_id_fin']; $i++) { 
                                            $tabla .= '
                                            <section class="col col-2">
                                                <label class="label" style="color:#1b5e20;"><b>GESTIÓN ' . $i . '</b></label>
                                                <label class="input">
                                                    <i class="icon-append fa fa-calendar"></i>
                                                    <!-- REVISIÓN CRÍTICA: Se remueven eventos inline y se fija la clase prog-anio con n -->
                                                    <input type="text" class="prog-anio" name="gest' . $i . '" id="gest' . $i . '" value="0">
                                                </label>
                                            </section>';
                                        }

                                        $tabla .= '
                                    </div>
                                    
                                    <div class="row">
                                        <section class="col col-4">
                                            <label class="label"><b>ADECUACIÓN DE INFRAESTRUCTURA</b></label>
                                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                                <textarea rows="2" name="ade_infraestructura" id="ade_infraestructura" placeholder="..."></textarea>
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label"><b>ADECUACIÓN DE INSTALACION</b></label>
                                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                                <textarea rows="2" name="ade_instalaciones" id="ade_instalaciones" placeholder="..."></textarea>
                                            </label>
                                        </section>
                                        <section class="col col-4">
                                            <label class="label"><b>OBSERVACIONES / JUSTIFICACIÓN TÉCNICA</b></label>
                                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                                <textarea rows="2" name="observaciones" id="observaciones" placeholder="Detalles de justificación..."></textarea>
                                            </label>
                                        </section>
                                    </div>
                                </fieldset>
                                
                                <!-- BOTONERA DE CONTROL INTEGRADA AL MODAL DE SMARTADMIN -->
                                <footer>
                                    <!-- REVISIÓN DE INTEGRIDAD: El botón inicia visible por defecto pero responde al fadeOut/fadeIn del script -->
                                    <button type="submit" id="btn_guardar_requerimiento" class="btn btn-primary" style="background: #1a237e; border-color: #0d47a1; font-weight: bold;">
                                        <i class="fa fa-save"></i> GUARDAR REQUERIMIENTO
                                    </button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal" id="btn_cerrar_modal" style="font-weight: bold;">
                                        CANCELAR
                                    </button>
                                    <center style="margin-top:10px;">
                                        <div id="loada" style="display: none;">
                                            <i class="fa fa-refresh fa-spin fa-2x text-primary"></i>
                                            <br><small class="text-muted">Procesando registros en el servidor...</small>
                                        </div>
                                    </center>
                                </footer>
                        </form>
                    </div>
                </div>
            </div>
        </div>';

        $tabla.='
        <div class="modal fade" id="modal_modificar_equipamiento" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalModificarTitle" aria-hidden="true">
            <div class="modal-dialog" style="width: 85% !important; max-width: 1200px;">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); border: 1px solid #1a237e;">
                    
                    <!-- CABECERA DEL MODAL DE MODIFICACIÓN (ESTILO PREMIUM EN AZUL MARINO) -->
                    <div class="modal-header" style="background: #1a237e; color: white; padding: 10px 15px;">
                        <button type="button" class="close" data-dismiss="modal" title="SALIR FORMULARIO" style="color: white; opacity: 0.9; font-size: 14px; margin-top: 2px;">
                            <span aria-hidden="true"><i class="fa fa-times-circle"></i> Salir Edición</span>
                        </button>
                        <h4 class="modal-title" style="font-weight: bold; font-size: 13px; text-transform: uppercase; color: white; letter-spacing: 0.5px;">
                            <i class="fa fa-pencil-square-o"></i> SIIPLAS v2.0: Modificar Requerimiento de Inversión Quinquenal
                        </h4>
                    </div>
                    
                    <!-- CONTENEDOR DINÁMICO RECEPTOR DE LA RESPUESTA AJAX -->
                    <div class="modal-body" style="padding: 15px; background: #fafafa; min-height: 250px;">
                        <!-- 🌟 AQUÍ EL SCRIPT JAVASCRIPT INYECTARÁ EL CONTENIDO DE data.html EN CALIENTE -->
                        <div class="text-center" style="padding: 60px 0;">
                            <i class="fa fa-gear fa-spin fa-3x text-warning" style="margin-bottom:15px;"></i>
                            <h5 style="font-weight:bold; color:#555; margin:0;">SINCRONIZANDO INFORMACION</h5>
                            <small class="text-muted">Extrayendo registros y temporalidades de la base de datos...</small>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>';
         $tabla.=$this->js_validacion();
       
        return $tabla;
    }


        //// validacion js
    public function js_validacion(){
        $tabla='';
        $tabla .= '
        <script type="text/javascript">
            // REVISIÓN DE INTEGRIDAD SIIPLAS: Espera la carga de la librería central JQuery
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // ==========================================================================
                    // 1. CONMUTACIÓN REACTIVA DE CAMPOS (Establecimiento vs Proyecto)
                    // ==========================================================================
                    $(document).on(\'change\', \'#tp_registro\', function() {
                        var tipo = $(this).val();
                        var $div_est = $(\'#est\');
                        var $div_inv = $(\'#inv\');
                        var $select_act = $(\'#act_id\');
                        var $text_inv = $(\'#nombre_inversion\');

                        if (tipo == 1 || tipo == 0) {
                            $div_inv.hide();
                            $text_inv.val(\'\').removeAttr(\'required\');
                            $div_est.fadeIn(200);
                            $select_act.attr(\'required\', \'required\');
                        } 
                        else if (tipo == 2) {
                            $div_est.hide();
                            $select_act.val(\'\').removeAttr(\'required\');
                            $div_inv.fadeIn(200);
                            $text_inv.attr(\'required\', \'required\');
                        }
                    });

                    $(\'#act_id\').attr(\'required\', \'required\');

                    // ==========================================================================
                    // 2. CALCULO ARITMÉTICO EN CALIENTE: Cantidad * Costo Unitario
                    // ==========================================================================
                    function procesar_calculo_matriz_financiera() {
                        // Obtenemos los valores limpiando cualquier residuo no numérico
                        var cantidad   = parseFloat($(\'#cantidad\').val()) || 0;
                        var costo_unit = parseFloat($(\'#costo_unitario\').val()) || 0;

                        // Operación aritmética directa en memoria RAM
                        var costo_total = cantidad * costo_unit;
                        
                        // Inyectamos el resultado formateado a dos decimales contables en el campo bloqueado
                        $(\'#costo_total\').val(costo_total.toFixed(2));
                        
                        // Forzamos la verificación de cuadre contra la temporalidad de los 5 años
                        verificar_cuadre_financiero_quinquenal();
                    }

                    // ==========================================================================
                    // 3. ENTRADA SANITIZADA CRÍTICA: Bloqueo de letras/comas para montos y gestiones
                    // ==========================================================================
                    // REVISIÓN: Añadidos #cantidad y #costo_unitario al selector dinámico
                    $(document).on(\'input\', \'.prog-anio, #costo_unitario, #cantidad\', function() {
                        var $input = $(this);
                        
                        // Paso A: Convertimos comas en puntos decimales legibles por el motor en caliente
                        var valor_sanitizado = $input.val().replace(\',\', \'.\');
                        
                        // Paso B: Removemos de forma agresiva cualquier carácter que no sea número o punto
                        valor_sanitizado = valor_sanitizado.replace(/[^0-9.]/g, \'\');
                        
                        // Paso C: Impedimos la inyección de múltiples puntos decimales que corrompan el tipo
                        var partes = valor_sanitizado.split(\'.\');
                        if (partes.length > 2) {
                            valor_sanitizado = partes[0] + \'.\' + partes.slice(1).join(\'\');
                        }
                        
                        // Devolvemos el valor limpio al campo de texto de forma invisible para el operador
                        $input.val(valor_sanitizado);
                        
                        // Si la edición viene de los campos maestros, recalculamos el costo total consolidado
                        if ($input.attr(\'id\') === \'cantidad\' || $input.attr(\'id\') === \'costo_unitario\') {
                            procesar_calculo_matriz_financiera();
                        } else {
                            // Si viene de los años (gestiones), calculamos la sumatoria quinquenal
                            calcular_sumatoria_quinquenal();
                        }
                    });

                    // ==========================================================================
                    // --- SUB-FUNCIONES CORE DE CONTROL ARITMÉTICO ---
                    // ==========================================================================
                    function calcular_sumatoria_quinquenal() {
                        var suma = 0;
                        $(\'.prog-anio\').each(function() {
                            var valor_casilla = parseFloat($(this).val()) || 0;
                            suma += valor_casilla;
                        });
                        
                        $(\'#total_prog\').val(suma.toFixed(2));
                        verificar_cuadre_financiero_quinquenal();
                    }

                    function verificar_cuadre_financiero_quinquenal() {
                        var costo_total  = parseFloat($(\'#costo_total\').val()) || 0;
                        var total_prog   = parseFloat($(\'#total_prog\').val()) || 0;
                        var $btn_guardar = $(\'#btn_guardar_requerimiento\'); 
                        
                        $(\'#alerta_descuadre_poa\').remove();

                        // Margen de tolerancia contable por redondeo de coma flotante
                        if (Math.abs(total_prog - costo_total) > 0.01) {
                            $btn_guardar.fadeOut(150); // Oculta el botón guardar inyectando alerta
                            
                            var plantilla_error = `
                            <div id="alerta_descuadre_poa" class="alert alert-danger" style="margin: 10px 0 0 0; padding: 6px 12px; font-size: 11px; font-weight: bold; border-radius: 4px; width: 100%;">
                                <i class="fa fa-times-circle"></i> Restricción Financiera CNS: El Total Distribuido Quinquenal (${total_prog.toFixed(2)} Bs.) no iguala al Costo Total Consolidado (${costo_total.toFixed(2)} Bs.). El botón de guardado permanecerá bloqueado.
                            </div>`;
                            
                            $(\'#total_prog\').closest(\'.row\').after(plantilla_error);
                        } else {
                            // Habilita el botón únicamente si los montos cuadran perfectamente
                            $btn_guardar.fadeIn(150);
                        }
                    }

                } else {
                    console.error("SIIPLAS Error: No se pudo iniciar el validador de expresiones de entrada.");
                }
            });
        </script>';

        $tabla .= '
        <script type="text/javascript">
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // ==========================================================================
                    // 🌟 FUNCIÓN AUXILIAR DE ACTUALIZACIÓN ASÍNCRONA DIRECTA (MULTI-ROL)
                    // ==========================================================================
                    function forzar_refresco_grilla_siiplas(dist_id_origen) {
                        var $contenedor_lista = $(\'#contenedor_lista_ajax\');
                        
                        // Si por maquetación de la vista el ID receptor general es el contenedor del formulario
                        if($(\'#contenedor_formulario\').length > 0 && $contenedor_lista.length === 0) {
                            $contenedor_lista = $(\'#contenedor_formulario\');
                        }

                        if (!dist_id_origen || dist_id_origen == "0") {
                            return false;
                        }

                        $.ajax({
                            url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_unidad_ejecutora",
                            type: \'POST\',
                            dataType: \'json\',
                            data: { id: dist_id_origen },
                            beforeSend: function() {
                                $contenedor_lista.html(
                                    \'<div class="text-center" style="padding: 40px 0;">\' +
                                    \'   <i class="fa fa-refresh fa-spin fa-2x text-primary"></i>\' +
                                    \'   <p class="text-muted" style="margin-top:10px; font-size:11px;">Sincronizando grilla de requerimientos...</p>\' +
                                    \'</div>\'
                                );
                            },
                            success: function(data) {
                                if(data.respuesta == \'correcto\') {
                                    // Inyecta el listado o las filas actualizadas sin recargar la página
                                    $contenedor_lista.hide().html(data.tabla).fadeIn(300);
                                }
                            }
                        });
                    }

                    // ==========================================================================
                    // ESCUCHA TRANSACCIONAL: VALIDACIÓN ABSOLUTA DE TODOS LOS CAMPOS
                    // ==========================================================================
                    $(document).on(\'submit\', \'#form_nuevo\', function(e) {
                        e.preventDefault();

                        // --------------------------------------------------------------------------
                        // CAPA 1: VALIDACIÓN MANUAL DE OBLIGATORIEDAD INDIVIDUAL
                        // --------------------------------------------------------------------------
                        var tp_registro = $(\'#tp_registro\').val();
                        
                        if (tp_registro == "1") {
                            if ($(\'#act_id\').val() === "" || $(\'#act_id\').val() === "0") {
                                alertify.error("⚠️ Campo Obligatorio: Seleccione el Establecimiento de Salud Vinculado.");
                                $(\'#act_id\').focus();
                                return false;
                            }
                        } else if (tp_registro == "2") {
                            if ($(\'#nombre_inversion\').val().trim() === "") {
                                alertify.error("⚠️ Campo Obligatorio: Ingrese el Nombre del Proyecto de Inversión.");
                                $(\'#nombre_inversion\').focus();
                                return false;
                            }
                        }

                        if ($(\'#par_id\').val() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Seleccione la Partida Presupuestaria.");
                            $(\'#par_id\').focus();
                            return false;
                        }

                        var cantidad = parseInt($(\'#cantidad\').val()) || 0;
                        var costo_unit = parseFloat($(\'#costo_unitario\').val()) || 0;

                        if (cantidad <= 0) {
                            alertify.error("⚠️ Restricción: La Cantidad Total debe ser un número mayor a cero.");
                            $(\'#cantidad\').focus();
                            return false;
                        }
                        if (costo_unit <= 0) {
                            alertify.error("⚠️ Restricción: El Costo Unitario debe ser un monto mayor a cero.");
                            $(\'#costo_unitario\').focus();
                            return false;
                        }

                        // --------------------------------------------------------------------------
                        // CAPA 2: VALIDACIÓN DE INTEGRIDAD FINANCIERA (CUADRE QUINQUENAL)
                        // --------------------------------------------------------------------------
                        var costo_total  = parseFloat($(\'#costo_total\').val()) || 0;
                        var suma_quinquenio = 0;
                        $(\'.prog-anio\').each(function() {
                            suma_quinquenio += parseFloat($(this).val()) || 0;
                        });

                        if (Math.abs(suma_quinquenio - costo_total) > 0.01) {
                            alertify.error("🚨 Error Financiero: La suma de las 5 gestiones (" + suma_quinquenio.toFixed(2) + " Bs.) debe igualar al Costo Total Consolidado (" + costo_total.toFixed(2) + " Bs.).");
                            return false;
                        }

                        // --------------------------------------------------------------------------
                        // CAPA 3: PROCESAMIENTO DE ENVÍO SEGURO POR AJAX
                        // --------------------------------------------------------------------------
                        var formulario_data = $(this).serialize();
                        var $btn_ejecutar = $(\'#btn_guardar_requerimiento\');
                        var $loader_gif = $(\'#loada\');

                        // 🌟 CAPTURA MULTI-ROL ANTES DEL ENVÍO: Detectamos qué distrital se procesa
                        // Si está como admin lee el select combo, si está como responsable lee la entrada oculta del form
                        var id_distrital_origen = $(\'#dist_id\').val() || $(this).find(\'input[name="dist_id"]\').val() || 0;

                        $.ajax({
                            url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/guardar_requerimiento_equipamiento",
                            type: "POST",
                            dataType: "json",
                            data: formulario_data,
                            beforeSend: function() {
                                $btn_ejecutar.attr("disabled", "disabled").hide();
                                $loader_gif.fadeIn(150);
                            },
                            success: function(data) {
                                $loader_gif.hide();
                                $btn_ejecutar.removeAttr("disabled").show();

                                if (data.respuesta === "correcto") {
                                    if (typeof alertify !== "undefined") {
                                        alertify.success("✅ Éxito: Registro de equipamiento consolidado en el SIIPLAS.");
                                    }
                                    
                                    // 1. CIERRE DEL MODAL FLOTANTE
                                    $("#modal_nuevo_equipamiento").modal("hide");

                                    // SOLUCIÓN AL ERROR DE LA SOMBRA NEGRA (BACKDROP PERSISTENTE)
                                    $(\'.modal-backdrop\').remove();
                                    $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');

                                    // 2. REINICIO DE VALORES: Vacía el formulario para el siguiente registro
                                    if ($(\'#form_nuevo\').length > 0) {
                                        $(\'#form_nuevo\')[0].reset();
                                    }
                                    
                                    $("#form_equip_id").val("0");
                                    $("#cantidad").val("0");
                                    $("#costo_unitario").val("0.00");
                                    $("#costo_total").val("0.00");
                                    $("#total_prog").val("0.00");
                                    $(".prog-anio").val("0");
                                    $("#tp_registro").val("1").trigger("change"); 
                                    $("#alerta_descuadre_poa").remove(); 

                                    // 3. 🌟 REFRESCO AUTOMÁTICO EN CALIENTE MULTI-ROL ADAPTADO
                                    var combo_select_admin = $(\'#dist_id\').val();
                                    
                                    if (combo_select_admin !== undefined && combo_select_admin !== "0" && combo_select_admin !== "") {
                                        // ROL ADMINISTRADOR: Dispara el trigger normal del combo presente en su vista
                                        $("#dist_id").trigger("change");
                                    } else {
                                        // ROL RESPONSABLE: Ejecuta de forma directa la subfunción pasando el ID oculto capturado
                                        forzar_refresco_grilla_siiplas(id_distrital_origen);
                                    }

                                } else {
                                    if (typeof alertify !== "undefined") {
                                        alertify.error("❌ Restricción: " + data.mensaje);
                                    }
                                }
                            },
                            error: function() {
                                $loader_gif.hide();
                                $btn_ejecutar.removeAttr("disabled").show();
                                if (typeof alertify !== "undefined") {
                                    alertify.error("❌ Error Crítico: Falla de comunicación con el servidor.");
                                }
                            }
                        });
                    });

                }
            });
        </script>';

        $tabla .= '
        <script type="text/javascript">
            // REVISIÓN DE INTEGRIDAD SIIPLAS: Espera de manera nativa la disponibilidad de JQuery en el DOM
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // ==========================================================================
                    // ESCUCHA ASÍNCRONA: RECUPERACIÓN MULTI-ROL EN EL MODAL DE EDICIÓN
                    // ==========================================================================
                    $(document).on(\'click\', \'.btn_modificar_equip\', function(e) {
                        e.preventDefault(); // Detiene cualquier comportamiento nativo de redirección

                        // 1. CAPTURA SEGURO MULTI-ROL:
                        var form_equip_id = $(this).attr(\'data-id\');
                        
                        // 🌟 El blindaje: Si no existe el combo #dist_id (Admin), lee el data-distrital del botón (Responsable)
                        var dist_id = $(\'#dist_id\').val() || $(this).attr(\'data-distrital\') || 0;
                        
                        // Elementos de referencia del modal de modificación independiente
                        var $modal = $(\'#modal_modificar_equipamiento\');
                        var $body_modal = $modal.find(\'.modal-body\');

                        // Detenemos la petición si ambos selectores fallan por completo
                        if(dist_id == 0 || dist_id == "") {
                            if (typeof alertify !== "undefined") {
                                alertify.error("⚠️ Restricción: No se pudo determinar el código distrital de origen.");
                            }
                            return false;
                        }

                        $.ajax({
                            url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_formulario_modal_html",
                            type: \'POST\',
                            dataType: \'json\',
                            data: { 
                                form_equip_id: form_equip_id, 
                                dist_id: dist_id 
                            },
                            beforeSend: function() {
                                // LIMPIEZA PREVENTIVA: Forzamos el spinner de carga antes de levantar la ventana
                                $body_modal.html(
                                    \'<div class="text-center" style="padding: 60px 0;">\' +
                                    \'   <i class="fa fa-gear fa-spin fa-3x text-warning" style="margin-bottom:15px;"></i>\' +
                                    \'   <h5 style="font-weight:bold; color:#555; margin:0;">SINCRONIZANDO INFORMACIÓN</h5>\' +
                                    \'   <small class="text-muted">Extrayendo registros y temporalidades de la base de datos...</small>\' +
                                    \'</div>\'
                                );
                                
                                // DESPLIEGUE CONTROLADO: Mostramos la ventana flotante de modificación
                                $modal.modal(\'show\');
                            },
                            success: function(data) {
                                if (data.respuesta === "correcto") {
                                    // INYECCIÓN DE LA ENTRADA RECUPERADA DESDE EL CONTROLADOR
                                    $body_modal.hide().html(data.html).fadeIn(250);
                                    
                                    // Gatillamos la conmutación de campos dinámicos (Establecimiento vs Inversión)
                                    $modal.find(\'.form_tp_registro\').trigger(\'change\');
                                    
                                    // PRE-CÁLCULO QUINQUENAL: Sumamos y poblamos el total programado inicial
                                    var suma_inicial_poa = 0;
                                    $modal.find(\'.prog-anio\').each(function() {
                                        suma_inicial_poa += parseFloat($(this).val()) || 0;
                                    });
                                    $modal.find(\'.form_total_prog\').val(suma_inicial_poa.toFixed(2));
                                    
                                } else {
                                    if (typeof alertify !== "undefined") {
                                        alertify.error("❌ Error operativo: El servidor denegó la recuperación de la ficha.");
                                    }
                                    $modal.modal(\'hide\');
                                }
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                if (typeof alertify !== "undefined") {
                                    alertify.error("❌ Error Crítico: Falla en la comunicación con el servidor local.");
                                }
                                $modal.modal(\'hide\');
                            }
                        });
                    });

                }
            });
        </script>';

      ///// VALIDADOR PARA MODIFICAR REGISTRO
      $tabla .= '
      <script type="text/javascript">
          // REVISIÓN DE INTEGRIDAD SIIPLAS: Espera nativamente la carga de la librería central JQuery
          window.addEventListener("load", function() {
              if (typeof $ !== "undefined") {

                  // ==========================================================================
                  // 1. CONMUTACIÓN REACTIVA DE CAMPOS (Establecimiento de Salud vs Proyecto)
                  // ==========================================================================
                  $(document).on(\'change\', \'.form_tp_registro\', function() {
                      var $modal = $(this).closest(\'.modal\'); 
                      var tipo = $(this).val();
                      
                      var $div_est = $modal.find(\'.div_est\');
                      var $div_inv = $modal.find(\'.div_inv\');
                      var $select_act = $modal.find(\'.form_act_id\');
                      var $text_inv = $modal.find(\'.form_nombre_inversion\');

                      if (tipo == 1 || tipo == 0) {
                          $div_inv.hide();
                          $text_inv.val(\'\').removeAttr(\'required\');
                          $div_est.fadeIn(200);
                          $select_act.attr(\'required\', \'required\');
                      } 
                      else if (tipo == 2) {
                          $div_est.hide();
                          $select_act.val(\'\').removeAttr(\'required\');
                          $div_inv.fadeIn(200);
                          $text_inv.attr(\'required\', \'required\');
                      }
                  });

                  // ==========================================================================
                  // 2. CALCULO ARITMÉTICO EN CALIENTE: Cantidad * Costo Unitario Aislado
                  // ==========================================================================
                  $(document).on(\'input change\', \'.form_cantidad, .form_costo_unitario\', function() {
                      var $modal = $(this).closest(\'.modal\');
                      
                      var cantidad = parseInt($modal.find(\'.form_cantidad\').val()) || 0;
                      var costo_unit = $modal.find(\'.form_costo_unitario\').val().replace(\',\', \'.\');
                      costo_unit = parseFloat(costo_unit) || 0;

                      var costo_total = cantidad * costo_unit;
                      $modal.find(\'.form_costo_total\').val(costo_total.toFixed(2));
                      
                      verificar_cuadre_financiero_contextual($modal);
                  });

                  // ==========================================================================
                  // 3. SANITIZACIÓN CRÍTICA Y SUMATORIA DE GESTIONES QUINQUENALES
                  // ==========================================================================
                  $(document).on(\'input\', \'.prog-anio\', function() {
                      var $modal = $(this).closest(\'.modal\');
                      var valor_sanitizado = $(this).val().replace(\',\', \'.\').replace(/[^0-9.]/g, \'\');
                      
                      var partes = valor_sanitizado.split(\'.\');
                      if (partes.length > 2) {
                          valor_sanitizado = partes[0] + \'.\' + partes.slice(1).join(\'\');
                      }
                      $(this).val(valor_sanitizado);

                      var suma = 0;
                      $modal.find(\'.prog-anio\').each(function() {
                          suma += parseFloat($(this).val()) || 0;
                      });
                      
                      $modal.find(\'.form_total_prog\').val(suma.toFixed(2));
                      verificar_cuadre_financiero_contextual($modal);
                  });

                  // ==========================================================================
                  // 4. VERIFICACIÓN DE INTEGRIDAD FINANCIERA Y BLOQUEO DE BOTÓN EN CALIENTE
                  // ==========================================================================
                  function verificar_cuadre_financiero_contextual($modal) {
                      var costo_total  = parseFloat($modal.find(\'.form_costo_total\').val()) || 0;
                      var total_prog   = parseFloat($modal.find(\'.form_total_prog\').val()) || 0;
                      var $btn_guardar = $modal.find(\'.btn_guardar_requerimiento_pluri\'); 
                      
                      $modal.find(\'.alerta_descuadre_poa\').remove();

                      if (Math.abs(total_prog - costo_total) > 0.01) {
                          $btn_guardar.fadeOut(150);
                          
                          var plantilla_error = `
                          <div id="alerta_descuadre_poa" class="alerta_descuadre_poa alert alert-danger" style="margin: 10px 0 0 0; padding: 6px 12px; font-size: 11px; font-weight: bold; border-radius: 4px; width: 100%;">
                              <i class="fa fa-times-circle"></i> Restricción Financiera CNS: El Total Distribuido (${total_prog.toFixed(2)} Bs.) no iguala al Costo Total Consolidado (${costo_total.toFixed(2)} Bs.). El botón de guardado permanecerá bloqueado.
                          </div>`;
                          
                          $modal.find(\'.form_total_prog\').closest(\'.row\').after(plantilla_error);
                      } else {
                          $btn_guardar.fadeIn(150);
                      }
                  }

                  // ==========================================================================
                  // 5. CAPA TRANSACCIONAL: VALIDACIÓN ABSOLUTA AL HACER SUBMIT (GUARDAR)
                  // ==========================================================================
                  $(document).on(\'submit\', \'.form_transaccional_equipamiento\', function(e) {
                      e.preventDefault();
                      
                      var $form = $(this);
                      var $modal = $form.closest(\'.modal\');
                      var tp_registro = $modal.find(\'.form_tp_registro\').val();
                      
                      // --- VALIDACIÓN MANUAL DE OBLIGATORIEDAD CAMPO POR CAMPO ---
                      if (tp_registro == "1") {
                          if ($modal.find(\'.form_act_id\').val() === "" || $modal.find(\'.form_act_id\').val() === "0") {
                              alertify.error("⚠️ Campo Obligatorio: Seleccione el Establecimiento de Salud Vinculado.");
                              $modal.find(\'.form_act_id\').focus();
                              return false;
                          }
                      } else if (tp_registro == "2") {
                          if ($modal.find(\'.form_nombre_inversion\').val().trim() === "") {
                              alertify.error("⚠️ Campo Obligatorio: Ingrese el Nombre del Proyecto de Inversión.");
                              $modal.find(\'.form_nombre_inversion\').focus();
                              return false;
                          }
                      }

                    
                      if ($modal.find(\'.form_tp_compra\').val() === "" || $modal.find(\'.form_tp_compra\').val() === "0") {
                          alertify.error("⚠️ Campo Obligatorio: Seleccione el Tipo de Compra.");
                          $modal.find(\'.form_tp_compra\').focus();
                          return false;
                      }
                      if ($modal.find(\'.form_par_id\').val() === "") {
                          alertify.error("⚠️ Campo Obligatorio: Seleccione la Partida Presupuestaria.");
                          $modal.find(\'.form_par_id\').focus();
                          return false;
                      }

                      var cantidad = parseInt($modal.find(\'.form_cantidad\').val()) || 0;
                      var costo_unit = parseFloat($modal.find(\'.form_costo_unitario\').val()) || 0;

                      if (cantidad <= 0) {
                          alertify.error("⚠️ Restricción: La Cantidad Total debe ser un número mayor a cero.");
                          $modal.find(\'.form_cantidad\').focus();
                          return false;
                      }
                      if (costo_unit <= 0) {
                          alertify.error("⚠️ Restricción: El Costo Unitario debe ser un monto mayor a cero.");
                          $modal.find(\'.form_costo_unitario\').focus();
                          return false;
                      }

                      var costo_total_maestro = parseFloat($modal.find(\'.form_costo_total\').val()) || 0;
                      
                      var suma_quinquenio_real = 0;
                      $modal.find(\'.prog-anio\').each(function() {
                          suma_quinquenio_real += parseFloat($(this).val()) || 0;
                      });

                      // Convertimos ambos montos a texto de 2 decimales para una comparación contable pura
                      var total_formateado_check = costo_total_maestro.toFixed(2);
                      var suma_formateada_check  = suma_quinquenio_real.toFixed(2);

                      if (suma_formateada_check !== total_formateado_check) {
                          if (typeof alertify !== "undefined") {
                              alertify.error("🚨 Restricción POA: La sumatoria programada en el quinquenio (" + suma_formateada_check + " Bs.) debe ser exactamente igual al Costo Total Consolidado (" + total_formateado_check + " Bs.).");
                          } else {
                              alert("⚠️ Error: La distribución quinquenal no coincide con el Costo Total.");
                          }
                          
                          $modal.find(\'.prog-anio\').first().focus();
                          return false; // Bloquea el envío al controlador
                      }

                      // 🌟 AJUSTE MULTI-ROL A: Capturamos la distrital activa antes de enviar el AJAX
                      // Si es admin la lee del combo superior, si es responsable la lee del input oculto del formulario modal
                      var dist_id_activo = $(\'#dist_id\').val() || $form.find(\'.modal_dist_id\').val() || 0;

                      // --- ENVÍO SEGURO POR AJAX DINÁMICO ---
                      var url_destino = base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/guardar_requerimiento_equipamiento";
                      var $btn_ejecutar = $modal.find(\'.btn_guardar_requerimiento_pluri\');
                      var $loader_gif = $modal.find(\'.loada_spinner\');

                      $.ajax({
                          url: url_destino,
                          type: "POST",
                          dataType: "json",
                          data: $form.serialize(),
                          beforeSend: function() {
                              $btn_ejecutar.attr("disabled", "disabled").hide();
                              $loader_gif.fadeIn(150);
                          },
                          success: function(data) {
                              $loader_gif.hide();
                              $btn_ejecutar.removeAttr("disabled").show();

                              if (data.respuesta === "correcto") {
                                  alertify.success("✅ Éxito: Registro consolidado correctamente en el SIIPLAS.");
                                  
                                  $modal.modal("hide");
                                  $(\'.modal-backdrop\').remove();
                                  $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');

                                  // 🌟 AJUSTE MULTI-ROL B: Refresco inteligente de la tabla izquierda sin recargar
                                  var combo_select_admin = $(\'#dist_id\').val();
                                  
                                  if (combo_select_admin !== undefined && combo_select_admin !== "0" && combo_select_admin !== "") {
                                      // ROL ADMINISTRADOR: Dispara el trigger normal del combo select de su vista
                                      $("#dist_id").trigger("change");
                                  } else {
                                      // ROL RESPONSABLE: Ejecuta de forma directa la subfunción pasando el ID oculto capturado
                                      if (typeof forzar_refresco_grilla_siiplas === "function") {
                                          forzar_refresco_grilla_siiplas(dist_id_activo);
                                      } else {
                                          $("#dist_id").trigger("change");
                                      }
                                  }
                              } else {
                                  alertify.error("❌ Restricción: " + data.mensaje);
                              }
                          },
                          error: function() {
                              $loader_gif.hide();
                              $btn_ejecutar.removeAttr("disabled").show();
                              alertify.error("❌ Error Crítico: Falla de comunicación con el servidor.");
                          }
                      });
                  });

              }
          });
      </script>';

      ///// ELIMINAR REGISTRO
     $tabla .= '
    <script type="text/javascript">
        window.addEventListener("load", function() {
            if (typeof $ !== "undefined") {

                // ==========================================================================
                // ESCUCHA CORREGIDA: ELIMINACIÓN / BAJA LÓGICA CON COMPATIBILIDAD ALERTIFY
                // ==========================================================================
                $(document).on(\'click\', \'.btn_eliminar_equip\', function(e) {
                    e.preventDefault();
                    
                    var form_equip_id = $(this).attr(\'data-id\');
                    
                    if(!form_equip_id || form_equip_id == 0) {
                        if (typeof alertify !== "undefined") {
                            alertify.error("⚠️ Error: Identificador de registro no válido.");
                        }
                        return false;
                    }

                    // 🛠️ REPARACIÓN DE SINTAXIS PARA COMPATIBILIDAD GLOBAL DE ALERTIFY
                    if (typeof alertify !== "undefined" && typeof alertify.confirm === "function") {
                        
                        alertify.confirm(
                            "¿Está absolutamente seguro de dar de baja este requerimiento de equipamiento? Esta acción eliminará el registro y sus temporalidades (2026-2030) del consolidado distrital.", 
                            function (e) {
                                if (e) {
                                    // MODO EXCLUSIVO: Si el usuario presionó "Aceptar" (true)
                                    $.ajax({
                                        url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/eliminar_requerimiento_equipamiento",
                                        type: \'POST\',
                                        dataType: \'json\',
                                        data: { form_equip_id: form_equip_id },
                                        success: function(data) {
                                            if (data.respuesta === "correcto") {
                                                alertify.success("🗑️ Éxito: El requerimiento fue dado de baja correctamente.");
                                                // Sincroniza la tabla izquierda en caliente sin recargar la página
                                                $("#dist_id").trigger("change");
                                            } else {
                                                alertify.error("❌ Restricción: " + data.mensaje);
                                            }
                                        },
                                        error: function() {
                                            alertify.error("❌ Error Crítico: El servidor no procesó la baja.");
                                        }
                                    });
                                } else {
                                    // Si el usuario presionó "Cancelar" (false)
                                    alertify.error("Operación de baja cancelada.");
                                }
                            }
                        );

                    } else {
                        // Capa de seguridad por si Alertify sufre caídas de carga en el cliente
                        if (window.confirm("¿Está seguro de dar de baja este requerimiento de equipamiento plurianual?")) {
                            $.ajax({
                                url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/eliminar_requerimiento_equipamiento",
                                type: \'POST\',
                                dataType: \'json\',
                                data: { form_equip_id: form_equip_id },
                                success: function(data) {
                                    if (data.respuesta === "correcto") {
                                        $("#dist_id").trigger("change");
                                    }
                                }
                            });
                        }
                    }
                });

            }
        });
    </script>';
        return $tabla;
    }


}
?>