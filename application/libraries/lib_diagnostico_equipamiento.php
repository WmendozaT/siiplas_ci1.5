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



    public function listado_equipamiento($equipamiento){
        $distritales=$this->model_diagnosticoequip->lista_UnidadEjecutora();

        //  $establecimientos=$this->model_diagnosticoequip->get_establecimientos_distrital($dist_id,$this->gestion);
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
        $tabla='';
            if($this->tp_adm==1){
                $listado=$this->model_diagnosticoequip->get_consolidado_formulario_diagnostico_activo($equipamiento[0]['equip_id']);
            }
            else{
                $listado=$this->model_diagnosticoequip->get_distrital_formulario_diagnostico_activo($equipamiento[0]['equip_id'],$this->dist_id);
            }

        $tabla .= '
        <input name="base" type="hidden" value="'.base_url().'">
        <tbody id="bdi_equipamiento">';
            $nro = 0;
            foreach($listado as $row){
                $nro++;
                
                // Configuración semántica del nombre del establecimiento según el Tipo de Registro
                $establecimiento_detallado = '';
                if ($row['tp_registro'] == 1) {
                    $establecimiento_detallado = '<span style="color: #2563eb; font-weight: 700; font-size: 11.5px;">' . strtoupper($row['tipo_establecimiento'] . ' ' . $row['nombre_establecimiento']) . '</span><br><small style="color: #64748b; font-weight: 600; letter-spacing:0.3px;">[' . strtoupper($row['abrev_establecimiento']) . ']</small>';
                } else {
                    $establecimiento_detallado = '<span class="label" style="background: #eff6ff; color: #2563eb; font-size: 9px; padding: 2px 5px; font-weight: 700; border: 1px solid #bfdbfe; border-radius: 4px; letter-spacing: 0.3px;">PROY. INVERSIÓN</span><br><small style="white-space: normal; font-weight: 700; color: #334155; display: inline-block; margin-top: 3px;">' . strtoupper($row['nombre_establecimiento']) . '</small>';
                }

                $tabla .= '
                <tr style="height: 38px; border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;">
                    <!-- Columnas Base -->
                    <td style="text-align: center; font-weight: 700; background: #f8fafc; color: #64748b; vertical-align: middle; font-size: 11px; border-right: 1px solid #edf2f7;">' . $nro . '</td>
                    
                    <!-- 🛠️ BOTONERA DE ACCIÓN ASÍNCRONA (MODAL / ELIMINAR) -->
                    <td style="text-align: center; vertical-align: middle; white-space: nowrap; padding: 6px; background: #ffffff; border-right: 1px solid #edf2f7;">
                        <!-- Botón Editar: Azul Cobalto Profesional con Sombra Sutil -->
                        <button type="button" class="btn btn-default btn-xs btn_modificar_equip" 
                                data-id="' . $row['form_equip_id'] . '" 
                                data-distrital="' . $row['dist_id'] . '" 
                                style="background: #2563eb; color: #ffffff; border: none; font-weight: 600; padding: 5px 12px; border-radius: 6px; font-size: 10px; letter-spacing: 0.3px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);" 
                                title="Editar especificaciones técnicas de este requerimiento">
                            <i class="fa fa-edit"></i> EDITAR
                        </button>
                        
                        <!-- Botón Baja: Rojo Institucional de Alerta Controlada -->
                        <button type="button" class="btn btn-danger btn-xs btn_eliminar_equip" 
                                data-id="' . $row['form_equip_id'] . '" 
                                style="background: #dc2626; color: #ffffff; border: none; font-weight: 600; padding: 5px 12px; margin-left: 4px; border-radius: 6px; font-size: 10px; letter-spacing: 0.3px; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.15);" 
                                title="Dar de baja este registro del sistema">
                            <i class="fa fa-trash"></i> ELIMINAR
                        </button>
                    </td>
                    <td>
                        <button type="button" class="btn btn-default btn-xs btn_modificar_adcionales" 
                                data-id="' . $row['form_equip_id'] . '" 
                                data-distrital="' . $row['dist_id'] . '" 
                                style="background: #1c7368; color: #ffffff; border: none; font-weight: 600; padding: 5px 12px; border-radius: 6px; font-size: 10px; letter-spacing: 0.3px; box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);" 
                                title="registrar adicionales">
                            <i class="fa fa-edit"></i> ADICIONALES
                        </button>
                    </td>
                    
                    <td style="vertical-align: middle; white-space: normal; min-width: 110px; padding: 6px; font-size: 11.5px; font-weight: 600; color: #475569;">' . strtoupper($row['dist_distrital']) . '</td>
                    <td style="vertical-align: middle; white-space: normal; min-width: 110px; padding: 6px; font-size: 11.5px; color: #334155;">' . strtoupper($row['responsable']) . '</td>
                    <td style="vertical-align: middle; white-space: normal; min-width: 120px; padding: 6px; line-height: 1.3;">' . $establecimiento_detallado . '</td>
                    <td style="vertical-align: middle; padding: 6px; font-size: 11.5px; font-weight: 500; color: #1e293b;">' . strtoupper($row['nombre_equipamiento']) . '</td>
                    <td style="vertical-align: middle; font-weight: 700; color: #1e3a8a; white-space: normal; min-width: 150px; padding: 6px; font-size: 11.5px;">' . strtoupper($row['servicio_unidad']) . '</td>
                    <td style="vertical-align: middle; white-space: normal; min-width: 110px; padding: 6px; font-size: 11px; color: #64748b; font-weight: 500;">' . strtoupper($row['ubicacion_fisica']) . '</td>
                    <td style="vertical-align: middle; padding: 6px; font-size: 11px; font-weight: 600; color: #475569;">' . strtoupper($row['tp_compra_nombre']) . '</td>
                    
                    <!-- Celdas Numéricas con Formato Contable -->
                    <td style="text-align: center; vertical-align: middle; font-weight: 700; font-size: 11.5px; color: #475569; background: #f8fafc;">' . $row['par_codigo'] . '</td>
                    <td style="text-align: center; vertical-align: middle; font-weight: 700; font-size: 12px; color: #0f172a;">' . intval($row['cantidad']) . '</td>
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; font-weight: 500; color: #334155;">' . number_format($row['costo_unitario'], 2, '.', ',') . '</td>
                    <td style="text-align: right; vertical-align: middle; font-weight: 700; color: #2563eb; padding-right: 8px; font-size: 12px; background: #f0f5ff;">' . number_format($row['costo_total'], 2, '.', ',') . '</td>
                    
                    <!-- Distribución Temporal Plurianual -->
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; ' . ($row['g_2026'] > 0 ? 'background: #f0fdf4; font-weight: 700; color: #16a34a;' : 'color: #94a3b8; font-weight: 400;') . '">' . number_format($row['g_2026'], 2, '.', ',') . '</td>
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; ' . ($row['g_2027'] > 0 ? 'background: #f0fdf4; font-weight: 700; color: #16a34a;' : 'color: #94a3b8; font-weight: 400;') . '">' . number_format($row['g_2027'], 2, '.', ',') . '</td>
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; ' . ($row['g_2028'] > 0 ? 'background: #f0fdf4; font-weight: 700; color: #16a34a;' : 'color: #94a3b8; font-weight: 400;') . '">' . number_format($row['g_2028'], 2, '.', ',') . '</td>
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; ' . ($row['g_2029'] > 0 ? 'background: #f0fdf4; font-weight: 700; color: #16a34a;' : 'color: #94a3b8; font-weight: 400;') . '">' . number_format($row['g_2029'], 2, '.', ',') . '</td>
                    <td style="text-align: right; vertical-align: middle; padding-right: 8px; font-size: 11.5px; ' . ($row['g_2030'] > 0 ? 'background: #f0fdf4; font-weight: 700; color: #16a34a;' : 'color: #94a3b8; font-weight: 400;') . '">' . number_format($row['g_2030'], 2, '.', ',') . '</td>
                    
                    <td style="vertical-align: middle; white-space: normal; min-width: 140px; padding: 6px; line-height: 1.3; color: #64748b; font-size: 11px; font-weight: 500;">' . htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
            }
            $tabla .= '</tbody>';

            //// Modal Adicionales
            $tabla.='
            <div class="modal fade" id="modal_adicionales_equipamiento" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" style="width: 55% !important; max-width: 750px; margin: 50px auto;">
                    <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); border: 1px solid #2563eb;">
                        
                        <!-- CABECERA DEL MODAL ADICIONAL (ESTILO AZUL CORPORATIVO) -->
                        <div class="modal-header" style="background: #2563eb; color: white; padding: 10px 15px;">
                            <button type="button" class="close" data-dismiss="modal" title="CERRAR VENTANA" style="color: white; opacity: 0.9; font-size: 14px; margin-top: 2px;">
                                <span aria-hidden="true"><i class="fa fa-times-circle"></i> Cerrar</span>
                            </button>
                            <h4 class="modal-title" style="font-weight: bold; font-size: 13px; text-transform: uppercase; color: white; letter-spacing: 0.5px;">
                                <i class="fa fa-plus-square"></i> SIIPLAS v2.0: Componentes y Accesorios Adicionales
                            </h4>
                        </div>
                        
                        <!-- CONTENEDOR RECEPTOR DEL FORMULARIO Y TABLA INTERNA -->
                        <div class="modal-body" style="padding: 15px; background: #fafafa; min-height: 200px;">
                            <!-- El motor JQuery inyectará aquí el sub-formulario y el listado en tiempo real -->
                        </div>
                        
                        <div class="modal-footer" style="padding: 8px 15px; background: #f5f5f5;">
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold; font-size: 12px;">FINALIZAR Y SALIR</button>
                        </div>
                        
                    </div>
                </div>
            </div>';
            

            ////// Modal Registro
            $tabla.='
                <style>
                    #mdialTamanio{
                      width: 75% !important;
                    }
                    .modal-backdrop.in {
                        filter: alpha(opacity=85) !important;
                        opacity: 0.85 !important;
                        background-color: #000000 !important;
                    }
                </style>
                <div class="modal fade" id="modal_nuevo_equipamiento" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                  <div class="modal-dialog modal-lg" role="document" id="mdialTamanio">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Formulario de Registro Equipamiento</h5>
                        <!-- Botón X de cerrar con comportamiento nativo de limpieza -->
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <form id="form_nuevo_equip" name="form_nuevo_equip" class="smart-form" method="post" >
                            <input type="hidden" name="form_equip_id" id="m_form_equip_id" value="0">
                            <input type="hidden" name="equip_id" value="' . $equipamiento[0]['equip_id'] . '">
                          <div class="row" style="margin-bottom: 15px;">';
                              if($this->tp_adm == 1){ /// MODO: ADMINISTRADOR NACIONAL (SELECCIÓN MANDATORIA)
                                $tabla .= '
                                <div class="col-md-5 form-group">
                                    <label for="rol">Seleccione Distrital</label>
                                    <select id="m_dist_id" name="dist_id" class="form-control" onchange="cargar_establecimientos_por_distrital_modal(this.value)" style="font-weight:bold; color:#1a237e;" required>
                                        <option value="">Seleccione la distrital...</option>';
                                        foreach($distritales as $d) {
                                            $tabla .= '<option value="'.$d['dist_id'].'">'.strtoupper($d['dist_distrital']).'</option>';
                                        }
                                        $tabla .= '
                                    </select>
                                </div>';
                            } 
                            else { /// MODO: RESPONSABLE LOCAL (ESTABLECIDO POR SESIÓN AUTOMÁTICA)
                                $tabla .= '<input type="hidden" name="dist_id" id="m_dist_id" value="' . $this->dist_id . '">';
                            }                            
                            $tabla .= '
                          </div>
                          <div id="campos_detalle_equipamiento" style="' . ($this->tp_adm == 1 ? 'display:none;' : '') . '">
                                <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                                    <b>II. ESPECIFICACIONES TÉCNICAS DEL BIEN</b>
                                </header>
                                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                                <div class="row" style="margin-bottom: 15px;">
                                    <!-- Columna de tamaño 2 -->
                                    <div class="col-md-4 form-group">
                                      <label for="rol">Tipo de Registro</label>
                                        <select id="m_tp_registro" name="tp_registro" class="form-control" style="font-weight: bold; color: #0d47a1;" onchange="conmutar_tipo_origen_modal(this.value)" required>
                                            <option value="1">1.- Establecimiento</option>
                                            <option value="2">2.- Inversión</option>
                                        </select>
                                    </div>
                                    
                           
                                    <div id="m_sec_establecimiento">
                                        <div class="col-md-4 form-group">
                                        <label for="rol">Establecimiento: *</label>
                                            <select id="m_act_id" name="act_id" class="form-control">
                                                <option value="">Seleccione Centro de Salud...</option>';
                                                // Si es responsable, cargamos inicialmente sus centros médicos por defecto
                                                if($this->tp_adm != 1) {
                                                     $establecimientos=$this->model_diagnosticoequip->get_establecimientos_distrital($this->dist_id,$this->gestion);  
                                                    foreach($establecimientos as $est) {
                                                        $tabla .= '<option value="'.$est['act_id'].'">'.strtoupper($est['tipo'].' '.$est['act_descripcion']).'</option>';
                                                    }
                                                }
                                                $tabla .= '
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div id="m_sec_inversion" style="display:none;">
                                        <div class="col-md-4 form-group">
                                          <label for="email">Inversión: *</label>
                                          <textarea rows="2" class="form-control" name="nombre_inversion" id="m_nombre_inversion" placeholder="Escriba el nombre oficial del proyecto de inversión..."></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4 form-group">
                                      <label for="notas">Responsable: *</label>
                                      <textarea rows="3" class="form-control" name="responsable" id="m_responsable" required placeholder="Ej. Dr. Carlos Murillo - Jefe del Servicio de Quirófano"></textarea>
                                    </div>

                                </div>
                                </fieldset>

                                <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                                    <b>II. ESPECIFICACIONES TÉCNICAS</b>
                                </header>
                                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                                    <div class="row" style="margin-bottom: 15px;">
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Nombre del Equipo *</label>
                                          <textarea rows="3" class="form-control" name="nombre_equipamiento" id="m_nombre_equipamiento" required placeholder="Ej. MONITOR MULTIPARAMÉTRICO DE 5 PARÁMETROS"></textarea>
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Servicio / Unidad Destino: *</label>
                                          <textarea rows="3" class="form-control" name="servicio_unidad" id="m_servicio_unidad" required placeholder="Ej. UNIDAD DE TERAPIA INTENSIVA CORONARIA"></textarea>
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Ubicación Física Exacta: *</label>
                                          <textarea rows="3" class="form-control" name="ubicacion_fisica" id="m_ubicacion_fisica" required placeholder="Ej. Bloque Central - Tercer Piso - Sala de Recuperación"></textarea>
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="rol">Tipo de Compra: *</label>
                                          <select class="form-control" id="rol" name="rol" required>
                                            <option value="1">REPOSICIÓN</option>        
                                            <option value="2">COMPRA NUEVA</option>        
                                            <option value="3">ADECUACIÓN</option> 
                                          </select>
                                        </div>
                                    </div>
                                </fieldset>

                                <header style="border-bottom: 2px solid #2e7d32; color: #1b5e20; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                                    <b>III. MATRIZ FINANCIERA Y TOTALIZACIÓN DE GESTIONES (Bs.)</b>
                                </header>
                                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                                    <div class="row" style="margin-bottom: 15px;">
                                        <div class="col-md-3 form-group">
                                          <label for="rol">Partida Presupuestaria: *</label>
                                          <select id="m_par_id" name="par_id" class="form-control" required>
                                            <option value="">Seleccione Partida...</option>';
                                            foreach($partidas_gastos as $id_p => $desc_p) {
                                                $tabla .= '<option value="' . $id_p . '">' . $desc_p . '</option>';
                                            }
                                            $tabla .= '
                                          </select>
                                        </div>

                                        <div class="col-md-3 form-group">
                                          <label for="notas">Cantidad Total: *</label>
                                          <input type="text" name="cantidad" id="m_cantidad" class="form-control" value="0" required style="text-align:right; font-weight:bold;">
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Costo Unitario: *</label>
                                          <input type="text" name="costo_unitario" id="m_costo_unitario" class="form-control" value="0.00" required style="text-align:right; font-weight:bold;">
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Costo Total Consolidado (Bs.):</label>
                                          <input type="text" id="m_costo_total" value="0.00" class="form-control" readonly style="text-align:right; font-weight:bold; color:#0d47a1; background:#f4f4f4;">
                                        </div>
                                        
                                    </div>

                                    <div class="row" style="background: #f1f8e9; padding: 12px 10px 2px 10px; border: 1px dashed #2e7d32; border-radius: 4px; margin: 5px 0 10px 0;">
                                        <div class="col-md-2 form-group">
                                          <label for="notas">Total</label>
                                          <input type="text" id="m_total_prog" value="0.00" class="form-control" readonly style="font-weight: bold; color: #2e7d32; background: #eaebd8;">
                                        </div>';
                                        
                                        for ($i = $equipamiento[0]['g_id_inicio']; $i <= $equipamiento[0]['g_id_fin']; $i++) { 
                                            $tabla .= '
                                            <div class="col-md-2 form-group">
                                              <label for="notas"><b>GESTIÓN ' . $i . ' *</b></label>
                                              <!-- Se unificó la clase a class="form-control m-prog-anio" -->
                                              <input type="text" class="form-control m-prog-anio" name="gest' . $i . '" id="m_gest' . $i . '" value="0" style="text-align:right; font-weight:bold; color:#2e7d32;">
                                            </div>';
                                        }

                                        $tabla .= '
                                    </div>
                                    </fieldset>

                                    <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                                        <div class="row" style="margin-bottom: 15px;">
                                            <div class="col-md-4 form-group">
                                              <label for="notas">Adecuación de Infraestructura: *</label>
                                              <textarea rows="3" class="form-control" name="ade_infraestructura" id="m_ade_infraestructura" ></textarea>
                                            </div>
                                            <div class="col-md-4 form-group">
                                              <label for="notas">Adecuación de Instalación: *</label>
                                              <textarea rows="3" class="form-control" name="ade_instalaciones" id="m_ade_instalaciones" ></textarea>
                                            </div>
                                            <div class="col-md-4 form-group">
                                              <label for="notas">Observaciones / Justificaciones:: *</label>
                                              <textarea rows="3" class="form-control" name="observaciones" id="m_observaciones" required ></textarea>
                                            </div>
                                        </div>

                                    </fieldset>
                            </div>
                            <div style="text-align: right; border-top: 1px solid #ddd; padding-top: 12px; margin-top: 15px; display: flex; justify-content: flex-end; gap: 5px;">
                                <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:bold;">CANCELAR</button>
                                <button type="submit" class="btn btn-primary" id="btnGuardarEquipamiento" style="' . ($this->tp_adm == 1 ? 'display:none;' : '') . ' background:#1a237e; border-color:#0d47a1; font-weight:bold;">
                                    <i class="fa fa-save"></i> GUARDAR Y REGISTRAR
                                </button>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>';

        $tabla .= '
        <script type="text/javascript">
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // DESTRUCTOR DE CACHÉ PARA EVITAR SOLAPAMIENTOS AL CERRAR
                    $(\'#modal_adicionales_equipamiento\').on(\'hide.bs.modal\', function () {
                        $(this).find(\'.modal-body\').html(\'\');
                        $(\'.modal-backdrop\').remove();
                        $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');
                    });

                    // ==========================================================================
                    // 🌟 EXCLUSIVO: CAPTURA DE CLIC Y APERTURA DE MODAL ASÍNCRONO DE ADICIONALES
                    // ==========================================================================
                    $(document).on(\'click\', \'.btn_modificar_adcionales\', function(e) {
                        e.preventDefault(); // Detiene comportamientos de redirección nativos
                        
                        // Captura segura de variables contextuales multi-rol
                        var form_equip_id = $(this).attr(\'data-id\');
                        var dist_id = $(\'#dist_id\').val() || $(this).attr(\'data-distrital\') || 0;
                        
                        var $modal = $(\'#modal_adicionales_equipamiento\');
                        var $body_modal = $modal.find(\'.modal-body\');

                        if(!form_equip_id || form_equip_id == "0") {
                            if (typeof alertify !== "undefined") {
                                alertify.error("⚠️ Error: Identificador de requerimiento no válido.");
                            }
                            return false;
                        }

                        $.ajax({
                            url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_formulario_adicionales_modal_html",
                            type: \'POST\',
                            dataType: \'json\',
                            data: { 
                                form_equip_id: form_equip_id,
                                dist_id: dist_id
                            },
                            beforeSend: function() {
                                // 1. LIMPIEZA DEL CONTENEDOR: Inyectamos el loader oficial de SmartAdmin
                                $body_modal.html(
                                    \'<div class="text-center" style="padding: 40px 0;">\' +
                                    \'   <i class="fa fa-refresh fa-spin fa-2x text-primary" style="margin-bottom:10px;"></i>\' +
                                    \'   <h5 style="font-weight:bold; color:#444; margin:0 0 5px 0;">SINCRONIZANDO ADICIONALES</h5>\' +
                                    \'   <small class="text-muted">Cargando catálogo secundario de la CNS...</small>\' +
                                    \'</div>\'
                                );
                                
                                // 2. APERTURA CONTROLADA: Desplegamos la ventana flotante en la pantalla
                                $modal.modal(\'show\');
                            },
                            success: function(data) {
                                if (data.respuesta === "correcto") {
                                    // 3. INYECCIÓN FLUIDA: Pintamos la respuesta HTML dentro del cuerpo del modal
                                    $body_modal.html(data.html);
                                } else {
                                    if (typeof alertify !== "undefined") {
                                        alertify.error("❌ Error operativo: El servidor denegó la carga del panel.");
                                    }
                                    $modal.modal(\'hide\');
                                }
                            },
                            error: function() {
                                if (typeof alertify !== "undefined") {
                                    alertify.error("❌ Error Crítico: Falla de comunicación con el servidor local.");
                                }
                                $modal.modal(\'hide\');
                            }
                        });
                    });

                }
            });
        </script>';

        
      $tabla .= '
        <script type="text/javascript">
        window.addEventListener("load", function() {
            if (typeof $ !== "undefined") {
                var ES_ADMINISTRADOR = ' . ($this->tp_adm == 1 ? 'true' : 'false') . ';

                window.abrirModalNuevaEquipamiento = function() {
                    var $form = $("#form_nuevo_equip");
                    if ($form.length > 0) {
                        $form[0].reset(); // 🌟 El [0] soluciona el error fatal
                    }

                    var formElement = document.getElementById(\'form_nuevo_equip\');
                    if(formElement) {
                        formElement.reset();
                    }

                    $(\'#m_form_equip_id\').val(\'0\');
                    $(\'#m_tp_registro\').val(\'1\');
                    $(\'#m_act_id\').val(\'\');
                    $(\'#m_nombre_inversion\').val(\'\');
                    $(\'#m_cantidad\').val(\'0\');
                    $(\'#m_costo_unitario\').val(\'0.00\');
                    $(\'#m_costo_total\').val(\'0.00\');
                    $(\'#m_total_prog\').val(\'0.00\');
                    $(\'.m-prog-anio\').val(\'0\');
                    $(\'#alerta_descuadre_modal\').remove();

                    $(\'#m_sec_inversion\').hide();
                    $(\'#m_sec_establecimiento\').show();

                    if (ES_ADMINISTRADOR) {
                        $(\'#m_dist_id\').val(\'\');
                        $(\'#campos_detalle_equipamiento\').hide(); // La Parte 2 nace oculta para el Admin
                        $(\'#btnGuardarEquipamiento\').hide();      // El botón guardar nace oculto
                    } else {
                        // El Responsable Local ingresa directo a la ficha técnica
                        $(\'#campos_detalle_equipamiento\').show();
                        $(\'#btnGuardarEquipamiento\').show();
                    }

                    $(\'#modal_nuevo_equipamiento\').modal(\'show\');
                };

                // ==========================================================================
                // 2. PETICIÓN AJAX: CARGA EN CALIENTE Y DESPLIEGUE AUTOMÁTICO DE LA PARTE 2
                // ==========================================================================
                window.cargar_establecimientos_por_distrital_modal = function(dist_id) {
                    var $combo_est = $(\'#m_act_id\');
                    
                    if (dist_id === "" || dist_id == "0") {
                        $(\'#campos_detalle_equipamiento\').hide();
                        $(\'#btnGuardarEquipamiento\').hide();
                        return false;
                    }

                    $.ajax({
                        url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_establecimientos_por_dist_json",
                        type: \'POST\',
                        dataType: \'json\',
                        data: { dist_id: dist_id },
                        beforeSend: function() {
                            $combo_est.html(\'<option value="">Cargando centros autorizados...</option>\');
                            $(\'#campos_detalle_equipamiento\').hide();
                            $(\'#btnGuardarEquipamiento\').hide();
                        },
                        success: function(data) {
                            $combo_est.html(\'<option value="">Seleccione Centro de Salud...</option>\');
                            
                            if (data && data.length > 0) {
                                $.each(data, function(i, item) {
                                    $combo_est.append(\'<option value="\' + item.act_id + \'">\' + item.establecimiento + \'</option>\');
                                });
                            }
                            
                            // 🌟 EL AJUSTE RAÍZ: Al elegir Distrital, desplazamos la ficha técnica inmediatamente
                            $(\'#campos_detalle_equipamiento\').fadeIn(300);
                            
                            // El botón de guardado sigue bajo la supervisión estricta del Paso 1
                            evaluar_despliegue_secuencial_formulario();
                        },
                        error: function() {
                            alertify.error("❌ Error de red: No se pudo sincronizar el catálogo médico.");
                        }
                    });
                };

                // ==========================================================================
                // 3. CONMUTACIÓN INTERNA PASO 2: (Establecimiento vs Proyecto)
                // ==========================================================================
                window.conmutar_tipo_origen_modal = function(tipo) {
                    $(\'#m_act_id\').val(\'\');
                    $(\'#m_nombre_inversion\').val(\'\');

                    if (tipo == 1) {
                        $(\'#m_sec_inversion\').hide();
                        $(\'#m_sec_establecimiento\').fadeIn(150);
                    } else {
                        $(\'#m_sec_establecimiento\').hide();
                        $(\'#m_sec_inversion\').fadeIn(150);
                    }

                    evaluar_despliegue_secuencial_formulario();
                };

                // Escuchas en tiempo real para liberar el botón Guardar cuando se complete el origen
                $(document).on(\'change\', \'#m_act_id\', function() {
                    evaluar_despliegue_secuencial_formulario();
                });
                
                $(document).on(\'input change\', \'#m_nombre_inversion\', function() {
                    evaluar_despliegue_secuencial_formulario();
                });

                // ==========================================================================
                // 4. MOTOR DE CONTROL DE BOTONERA: LIBERA EL BOTÓN SÓLO SI SE ELIGIÓ ORIGEN
                // ==========================================================================
                function evaluar_despliegue_secuencial_formulario() {
                    if (!ES_ADMINISTRADOR) {
                        return false;
                    }

                    var dist_id = $(\'#m_dist_id\').val() || "";
                    var tp_registro = $(\'#m_tp_registro\').val();
                    
                    var establecimiento_val = $(\'#m_act_id\').val() || "";
                    var proyecto_val = $(\'#m_nombre_inversion\').val() ? $(\'#m_nombre_inversion\').val().trim() : "";

                    var origen_valido = false;

                    if (dist_id !== "" && dist_id !== "0") {
                        if (tp_registro == "1" && establecimiento_val !== "") {
                            origen_valido = true; // Completó Distrital + Hospital
                        } 
                        else if (tp_registro == "2" && proyecto_val !== "") {
                            origen_valido = true; // Completó Distrital + Texto del Proyecto
                        }
                    }

                    // El botón de guardado responde de manera estricta al cumplimiento del origen
                    if (origen_valido) {
                        $(\'#btnGuardarEquipamiento\').fadeIn(200);
                    } else {
                        $(\'#btnGuardarEquipamiento\').hide();
                    }
                }

                // ==========================================================================
                // 🌟 NUEVO MOTOR: TRATAMIENTO ERGONÓMICO DE VALORES EN CERO (FOCUS / BLUR)
                // ==========================================================================
                $(document).on(\'focus\', \'#m_cantidad, #m_costo_unitario, .m-prog-anio\', function() {
                    var val = $(this).val().trim();
                    if (val === "0" || val === "0.00") {
                        $(this).val("");
                    }
                });

                $(document).on(\'blur\', \'#m_cantidad, .m-prog-anio\', function() {
                    if ($(this).val().trim() === "") {
                        $(this).val("0");
                    }
                    calcular_matriz_financiera_modal();
                });

                $(document).on(\'blur\', \'#m_costo_unitario\', function() {
                    if ($(this).val().trim() === "") {
                        $(this).val("0.00");
                    }
                    calcular_matriz_financiera_modal();
                });

                // ==========================================================================
                // 5. MOTOR ARITMÉTICO CENTRALIZADO AJUSTADO (MATRIZ FINANCIERA)
                // ==========================================================================
                $(document).on(\'input\', \'.m-prog-anio, #m_costo_unitario, #m_cantidad\', function() {
                    var $input = $(this);
                    var limpio = $input.val().replace(\',\', \'.\').replace(/[^0-9.]/g, \'\');
                    
                    var partes = limpio.split(\'.\');
                    if (partes.length > 2) { limpio = partes[0] + \'.\' + partes.slice(1).join(\'\'); }
                    $input.val(limpio);

                    calcular_matriz_financiera_modal();
                });

                 window.calcular_matriz_financiera_modal = function() { 
                    var cant = parseInt($(\'#m_cantidad\').val()) || 0;
                    var unit = parseFloat($(\'#m_costo_unitario\').val()) || 0;
                    
                    // Cálculo automático del Costo Total
                    var costo_total = cant * unit;
                    $(\'#m_costo_total\').val(costo_total.toFixed(2));

                    // Sumatoria en caliente de los inputs del bucle anual
                    var suma_gestiones = 0;
                    $(\'.m-prog-anio\').each(function() {
                        var valor_anio = parseFloat($(this).val()) || 0;
                        suma_gestiones += valor_anio;
                    });

                    // Mostrar la suma total consolidada de las gestiones
                    $(\'#m_total_prog\').val(suma_gestiones.toFixed(2));

                    // Indicador visual de descuadre
                    if (Math.abs(costo_total - suma_gestiones) > 0.01) {
                        // 1. Cambiar estilos a color rojo pastel (Alerta)
                        $(\'#m_total_prog\').css({\'background\': \'#ffebee\', \'color\': \'#c62828\', \'border\': \'1px solid #e57373\'});
                        
                        // 2. Inyectar etiqueta de advertencia bajo el input si no existía ya
                        if ($(\'#alerta_descuadre_modal\').length === 0) {
                            $(\'#m_total_prog\').closest(\'.form-group\').append(
                                \'<small id="alerta_descuadre_modal" style="color:#c62828; font-weight:bold; display:block; margin-top:4px;"><i class="fa fa-warning"></i> Distribución descuadrada</small>\'
                            );
                        }

                        // 3. 🚫 ERROR PRESUPUESTARIO: Ocultar el botón de guardar inmediatamente
                        $(\'#btnGuardarEquipamiento\').hide();

                    } else {
                        // 1. Restaurar los estilos originales en verde cuando cuadre perfectamente
                        $(\'#m_total_prog\').css({\'background\': \'#eaebd8\', \'color\': \'#2e7d32\', \'border\': \'1px solid #a5d6a7\'});
                        
                        // 2. Eliminar el mensaje de advertencia del DOM de inmediato
                        $(\'#alerta_descuadre_modal\').remove();

                        // 3. 🌟 AJUSTE RAÍZ INTERNO: Detectar si es Edición o Registro Nuevo
                        var id_actual = parseInt($(\'#m_form_equip_id\').val()) || 0;

                        if (id_actual > 0) {
                            // 🚀 MODO EDICIÓN: Si la sumatoria cuadra, liberamos el botón inmediatamente
                            // Esto evita el bloqueo asíncrono mientras cargan los combos de hospitales
                            $(\'#btnGuardarEquipamiento\').fadeIn(150);
                        } else {
                            // 🆕 MODO REGISTRO NUEVO: Mantiene el flujo restrictivo original
                            if (ES_ADMINISTRADOR) {
                                var dist_id = $(\'#m_dist_id\').val() || "";
                                var tp_registro = $(\'#m_tp_registro\').val();
                                var establecimiento_val = $(\'#m_act_id\').val() || "";
                                var proyecto_val = $(\'#m_nombre_inversion\').val() ? $(\'#m_nombre_inversion\').val().trim() : "";

                                var origen_valido = false;
                                if (dist_id !== "" && dist_id !== "0") {
                                    if (tp_registro == "1" && establecimiento_val !== "") { origen_valido = true; } 
                                    else if (tp_registro == "2" && proyecto_val !== "") { origen_valido = true; }
                                }

                                if (origen_valido) {
                                    $(\'#btnGuardarEquipamiento\').fadeIn(150);
                                } else {
                                    $(\'#btnGuardarEquipamiento\').hide();
                                }
                            } else {
                                // Si es un Responsable Local, el origen ya es válido por sesión, muestra directo
                                $(\'#btnGuardarEquipamiento\').fadeIn(150);
                            }
                        }
                    }
                }; // 🌟 IMPORTANTE: Se cierra con }; por ser asignación de vari


                // ==========================================================================
                // MOTOR DE VALIDACIÓN AL ENVIAR EL FORMULARIO
                // ==========================================================================
                $(\'#form_nuevo_equip\').on(\'submit\', function(e) {
                e.preventDefault(); // Detener el envío tradicional obligatorio para AJAX

                // 1. Forzar sincronización financiera en caliente
                calcular_matriz_financiera_modal();

                var c_total = parseFloat($(\'#m_costo_total\').val()) || 0;
                var p_total = parseFloat($(\'#m_total_prog\').val()) || 0;

                // 2. Validación estricta del presupuesto anual
                if (Math.abs(c_total - p_total) > 0.01) {
                    alertify.error("❌ La distribución en las gestiones está descuadrada con el Costo Total.");
                    return false;
                }

                // 3. Validaciones de campos requeridos obligatorios de texto y selectores
                var dist_id = $(\'#m_dist_id\').val() || "";
                var tp_registro = $(\'#m_tp_registro\').val();
                var responsable = $(\'#m_responsable\').val() ? $(\'#m_responsable\').val().trim() : "";
                var nombre_equip = $(\'#m_nombre_equipamiento\').val() ? $(\'#m_nombre_equipamiento\').val().trim() : "";
                var servicio = $(\'#m_servicio_unidad\').val() ? $(\'#m_servicio_unidad\').val().trim() : "";

                if (dist_id === "" || dist_id === "0") { alertify.error("⚠️ Debe seleccionar una Distrital."); $(\'#m_dist_id\').focus(); return false; }
                
                if (tp_registro === "1") {
                    var act_id = $(\'#m_act_id\').val() || "";
                    if (act_id === "") { alertify.error("⚠️ Seleccione el Centro de Salud."); $(\'#m_act_id\').focus(); return false; }
                } else {
                    var inversion = $(\'#m_nombre_inversion\').val() ? $(\'#m_nombre_inversion\').val().trim() : "";
                    if (inversion === "") { alertify.error("⚠️ Ingrese el nombre oficial del proyecto de inversión."); $(\'#m_nombre_inversion\').focus(); return false; }
                }

                if (responsable === "") { alertify.error("⚠️ Ingrese el responsable del servicio."); $(\'#m_responsable\').focus(); return false; }
                if (nombre_equip === "") { alertify.error("⚠️ Ingrese el nombre del equipo."); $(\'#m_nombre_equipamiento\').focus(); return false; }
                if (servicio === "") { alertify.error("⚠️ Defina el Servicio o Unidad de destino."); $(\'#m_servicio_unidad\').focus(); return false; }

                // 4. ACTIVAR CAPA DE LOADING (PRE-SEND AJAX)
                var $btnGuardar = $(\'#btnGuardarEquipamiento\');
                var textoOriginalBtn = $btnGuardar.html(); // Guardar el texto original (ej: "Guardar Datos")

                // Serializar todos los inputs del formulario
                var datosFormulario = $(this).serialize();

                $.ajax({
                    url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/guardar_requerimiento_equipamiento",
                    type: \'POST\',
                    dataType: \'json\',
                    data: datosFormulario,
                    beforeSend: function() {
                        $btnGuardar.prop(\'disabled\', true).html(\'<i class="fa fa-refresh fa-spin"></i> Guardando en SIIPLAS...\');
                        alertify.log("⏳ Procesando registro presupuestario, por favor espere...");
                    },
                    success: function(response) {
                        if (response && response.status === "success") {
                            // 1. Notificación institucional de guardado correcto
                            alertify.success("✔️ ¡Registro consolidado con éxito! Recargando vista...");
                            
                            // 2. Ocultar el modal estático inmediatamente
                            $(\'#modal_nuevo_equipamiento\').modal(\'hide\');

                            // 3. 🌟 RECARGA AUTOMÁTICA DE LA PÁGINA (Ajuste Raíz)
                            // Espera 1000 milisegundos (1 segundo) para que el usuario aprecie el mensaje de éxito
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            // Si el controlador reporta un problema lógico, liberamos el botón para corregir
                            $btnGuardar.prop(\'disabled\', false).html(textoOriginalBtn);
                            alertify.error("❌ Error de consistencia: " + (response.message || "No se pudo procesar."));
                        }
                    },
                    error: function(xhr, status, errorThrown) {
                        $btnGuardar.prop(\'disabled\', false).html(textoOriginalBtn);
                        alertify.error("❌ Error interno " + xhr.status + ": El servidor PHP falló al procesar.");
                        
                        if(xhr.responseText) {
                            alert("DETALLE DEL ERROR 500 PHP DEBUGLOG:\n\n" + xhr.responseText.substring(0, 400) + "...\n\n(Revise la consola F12 para ver el reporte completo)");
                        }
                    }
                });

            }); // 🌟 ESTA LLAVE CIERRA EL EVENTO SUBMIT DEL FORMULARIO
            } // Cierra: if (typeof $ !== "undefined")
        }); // Cierra: window.addEventListener("load", function() { ... })
        </script>';


        //// Get modificar Registro
       $tabla .= '
        <script type="text/javascript">
        var checkJQueryModificar = setInterval(function() {
            if (typeof $ !== "undefined") {
                clearInterval(checkJQueryModificar); // Detener la espera de inmediato

                // ==========================================================================
                // 🛠️ SCRIPT INDEPENDIENTE: EVENTO MODIFICAR REQUERIMIENTO (EDICIÓN)
                // ==========================================================================
                $("#bdi_equipamiento").on("click", ".btn_modificar_equip", function(e) {
                    e.preventDefault();
                    
                    // 1. Extraer los identificadores de la fila seleccionada
                    var form_equip_id = $(this).attr("data-id");
                    var dist_id = $(this).attr("data-distrital");

                    // 2. Llamada asíncrona para recuperar los datos Maestro-Detalle de PostgreSQL
                    $.ajax({
                        url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/obtener_requerimiento_por_id",
                        type: "POST",
                        dataType: "json",
                        data: { form_equip_id: form_equip_id },
                        beforeSend: function() {
                            alertify.log("⏳ Recuperando especificaciones técnicas del registro...");
                        },
                        success: function(response) {
                            if (response && response.status === "success") {
                                var maestro = response.maestro;
                                var detalles = response.detalles;

                                // 3. Forzar inicialización limpia del modal llamando a tu función base
                                window.abrirModalNuevaEquipamiento();

                                // 4. Inyectar el ID real (Conmuta el backend a modo UPDATE)
                                $("#m_form_equip_id").val(maestro.form_equip_id);
                                $("#m_dist_id").val(maestro.dist_id);
                                $("#m_tp_registro").val(maestro.tp_registro);

                                // 5. Poblar cajas de texto y campos técnicos descriptivos
                                $("#m_responsable").val(maestro.responsable);
                                $("#m_nombre_equipamiento").val(maestro.nombre_equipamiento);
                                $("#m_servicio_unidad").val(maestro.servicio_unidad);
                                $("#m_ubicacion_fisica").val(maestro.ubicacion_fisica);
                                $("#m_tp_compra").val(maestro.tp_compra);
                                $("#m_par_id").val(maestro.par_id);
                                $("#m_observaciones").val(maestro.observaciones);
                                $("#m_ade_infraestructura").val(maestro.tp_adecuacion_infra);
                                $("#m_ade_instalaciones").val(maestro.tp_adecuacion_instalacion);

                                // 6. Poblar datos cuantitativos y financieros consolidados
                                $("#m_cantidad").val(maestro.cantidad);
                                $("#m_costo_unitario").val(maestro.costo_unitario);
                                $("#m_costo_total").val(maestro.costo_total);

                                // 7. Conmutación lógica y visual de la procedencia (Establecimiento / Inversión)
                                if (maestro.tp_registro == "1") {
                                    if ('.$this->tp_adm.'==1) {
                                        window.cargar_establecimientos_por_distrital_modal(maestro.dist_id);
                                        setTimeout(function() { $("#m_act_id").val(maestro.act_id); }, 450);
                                    } else {
                                        $("#m_act_id").val(maestro.act_id);
                                    }
                                    $("#m_sec_establecimiento").show();
                                    $("#m_sec_inversion").hide();
                                } else {
                                    $("#m_nombre_inversion").val(maestro.nombre_inversion);
                                    $("#m_sec_establecimiento").hide();
                                    $("#m_sec_inversion").show();
                                }

                                // 8. 📊 DISTRIBUCIÓN AUTOMÁTICA DEL QUINQUENIO RELACIONAL
                                $(".m-prog-anio").val("0");
                                if (detalles && detalles.length > 0) {
                                    $.each(detalles, function(i, item) {
                                        $("#m_gest" + item.g_id).val(item.prog_equi);
                                    });
                                }

                                // 9. Forzar la ejecución de las matemáticas y abrir el bloque técnico
                                window.calcular_matriz_financiera_modal();
                                $("#campos_detalle_equipamiento").show();
                                $("#btnGuardarEquipamiento").fadeIn(200);

                                alertify.success("✔️ Especificaciones técnicas cargadas correctamente.");
                            } else {
                                alertify.error("❌ Error: " + (response.message || "No se pudo leer el registro."));
                            }
                        },
                        error: function(xhr) {
                            alertify.error("❌ Error crítico " + xhr.status + ": No hay respuesta del módulo central.");
                        }
                    });
                });

            } // Cierra el check de jQuery
        }, 50); // Intenta compilar cada 50ms hasta que jQuery esté listo
        </script>';


      $tabla .= '
        <script type="text/javascript">
        // 🌟 TEMPORIZADOR DE SEGURIDAD: Asegura la existencia de jQuery ($) antes de inyectar el listener de baja
        var checkJQueryEliminar = setInterval(function() {
            if (typeof $ !== "undefined") {
                clearInterval(checkJQueryEliminar); // Detener la espera de inmediato al detectar la librería

                // ==========================================================================
                // 🗑️ SCRIPT INDEPENDIENTE: EVENTO ELIMINAR REQUERIMIENTO (BAJA LÓGICA)
                // ==========================================================================
                $("#bdi_equipamiento").on("click", ".btn_eliminar_equip", function(e) {
                    e.preventDefault();
                    
                    // 1. Capturar el botón presionado y su ID primario de PostgreSQL
                    var $btnEliminar = $(this);
                    var form_equip_id = $btnEliminar.attr("data-id");
                    var textoOriginalBtn = $btnEliminar.html(); // Guardar el icono actual

                    // 2. 🌟 AJUSTE RAÍZ: Alerta adaptada a la sintaxis de la versión clásica de Alertify
                    alertify.confirm("⚠️ ADVERTENCIA DE SEGURIDAD (SIIPLAS): ¿Está completamente seguro de dar de baja este requerimiento ? Esta acción modificará los consolidados quinquenales.", function(confirmacion) {
                        
                        // Si confirmacion es true, significa que el usuario hizo clic en "Aceptar"
                        if (confirmacion) {
                            
                            // CASO AFIRMATIVO: Procesar la baja por AJAX
                            $.ajax({
                                url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/eliminar_requerimiento_logico",
                                type: "POST",
                                dataType: "json",
                                data: { form_equip_id: form_equip_id },
                                beforeSend: function() {
                                    // ACTIVAR LOADING: Deshabilitar botón y mutar a spinner en la misma celda de la tabla
                                    $btnEliminar.prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i>");
                                    alertify.log("⏳ Aplicando baja lógica en el servidor de la CNS...");
                                },
                                success: function(response) {
                                    if (response && response.status === "success") {
                                        // Notificar éxito e iniciar reset de la vista
                                        alertify.success("✔️ Registro eliminado con éxito. Sincronizando grilla...");
                                        
                                        setTimeout(function() {
                                            location.reload(); // Recarga limpia del Layout general
                                        }, 1000);
                                    } else {
                                        // Restaurar botón si el backend detecta un problema de negocio lógica
                                        $btnEliminar.prop("disabled", false).html(textoOriginalBtn);
                                        alertify.error("❌ Error: " + (response.message || "No se pudo procesar la baja."));
                                    }
                                },
                                error: function(xhr) {
                                    // Restaurar estado físico del botón ante una falla de red (Error 500/404)
                                    $btnEliminar.prop("disabled", false).html(textoOriginalBtn);
                                    alertify.error("❌ Error de red " + xhr.status + ": El servidor de datos no respondió.");
                                }
                            });

                        } else {
                            // CASO NEGATIVO: El usuario hizo clic en "Cancelar"
                            alertify.log("Operación de eliminación cancelada por el usuario.");
                        }
                    });
                });

            } // Cierra el check de jQuery
        }, 50); // Intenta compilar cada 50ms hasta que jQuery esté listo en memoria
        </script>
        ';


        return $tabla;
    }






































}
?>