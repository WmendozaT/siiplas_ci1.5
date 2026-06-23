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

        $tabla.='
        <input name="base" type="hidden" value="'.base_url().'">
        <tbody id="bdi_equipamiento">';
            $nro = 0;
            foreach($listado as $row){
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
                    
                    <td style="vertical-align: middle; white-space:normal; min-width:110px; padding-left:4px;">' . strtoupper($row['dist_distrital']) . '</td>
                    <td style="vertical-align: middle; white-space:normal; min-width:110px; padding-left:4px;">' . strtoupper($row['responsable']) . '</td>
                    <td style="vertical-align: middle; white-space:normal; min-width:120px; padding-left:4px;">' . $establecimiento_detallado . '</td>
                    <td style="vertical-align: middle; padding-left:4px;">' . strtoupper($row['nombre_equipamiento']) . '</td>
                    <td style="vertical-align: middle; font-weight:600; color:#0d47a1; white-space:normal; min-width:150px; padding-left:4px;">' . strtoupper($row['servicio_unidad']) . '</td>
                    <td style="vertical-align: middle; white-space:normal; min-width:110px; padding-left:4px; color:#555;">' . strtoupper($row['ubicacion_fisica']) . '</td>
                    <td style="vertical-align: middle;">' . strtoupper($row['tp_compra_nombre']) . '</td>
                    
                    <!-- Celdas Numéricas con Formato Contable -->
                    <td style="text-align:center; vertical-align: middle; font-weight:600; font-size:12px;">' . $row['par_codigo'] . '</td>
                    <td style="text-align:center; vertical-align: middle; font-weight:bold;">' . intval($row['cantidad']) . '</td>
                    <td style="text-align:right; vertical-align: middle; padding-right:4px;">' . number_format($row['costo_unitario'], 2, '.', ',') . '</td>
                    <td style="text-align:right; vertical-align: middle; font-weight:bold; color:#1565c0; padding-right:4px;">' . number_format($row['costo_total'], 2, '.', ',') . '</td>
                    
                    <!-- Distribución Temporal Plurianual -->
                    <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2026'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2026'], 2, '.', ',') . '</td>
                    <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2027'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2027'], 2, '.', ',') . '</td>
                    <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2028'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2028'], 2, '.', ',') . '</td>
                    <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2029'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2029'], 2, '.', ',') . '</td>
                    <td style="text-align:right; vertical-align: middle; padding-right:4px; ' . ($row['g_2030'] > 0 ? 'background:#e8f5e9; font-weight:bold; color:#2e7d32;' : 'color:#ccc;') . '">' . number_format($row['g_2030'], 2, '.', ',') . '</td>
                    <td style="vertical-align: middle; white-space:normal; min-width:140px; padding:4px; line-height:1.2; color:#666;">' . htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') . '</td>
                </tr>';
            }
            $tabla .= '</tbody>';



                $tabla.='
                <div class="modal fade" id="modal_nuevo_equipamiento" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document" id="mdialTamanio">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="modalLabel">Formulario de Registro Equipamiento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        
                        <!-- Formulario apuntando al controlador de CI 1.5 -->
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
                                        <label for="rol">Establecimiento</label>
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
                                          <label for="email">Inversión</label>
                                          <textarea rows="2" class="form-control" name="nombre_inversion" id="m_nombre_inversion" placeholder="Escriba el nombre oficial del proyecto de inversión..."></textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4 form-group">
                                      <label for="notas">Responsable</label>
                                      <textarea rows="2" class="form-control" name="responsable" id="m_responsable" required placeholder="Ej. Dr. Carlos Murillo - Jefe del Servicio de Quirófano"></textarea>
                                    </div>

                                </div>
                                </fieldset>

                                <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                                    <b>II. ESPECIFICACIONES TÉCNICAS DEL BIEN</b>
                                </header>
                                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                                    <div class="row" style="margin-bottom: 15px;">
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Nombre del Equipo *</label>
                                          <textarea rows="2" class="form-control" name="nombre_equipamiento" id="m_nombre_equipamiento" required placeholder="Ej. MONITOR MULTIPARAMÉTRICO DE 5 PARÁMETROS"></textarea>
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Servicio / Unidad Destino: *</label>
                                          <textarea rows="2" class="form-control" name="servicio_unidad" id="m_servicio_unidad" required placeholder="Ej. UNIDAD DE TERAPIA INTENSIVA CORONARIA"></textarea>
                                        </div>
                                        <div class="col-md-3 form-group">
                                          <label for="notas">Ubicación Física Exacta: *</label>
                                          <textarea rows="2" class="form-control" name="ubicacion_fisica" id="m_ubicacion_fisica" required placeholder="Ej. Bloque Central - Tercer Piso - Sala de Recuperación"></textarea>
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





                                 
                                  <div class="row" style="margin-bottom: 15px;">
                                    
                                    <!-- Columna de tamaño 4 con elemento SELECT -->
                                    <div class="col-md-4 form-group">
                                      <label for="rol">Rol de Usuario</label>
                                      <select class="form-control" id="rol" name="rol" required>
                                        <option value="">Seleccione un rol...</option>
                                        <option value="1">Administrador</option>
                                        <option value="2">Editor</option>
                                        <option value="3">Usuario Estándar</option>
                                      </select>
                                    </div>

                                    <!-- Columna de tamaño 4 para Teléfono -->
                                    <div class="col-md-4 form-group">
                                      <label for="telefono">Teléfono</label>
                                      <input type="text" class="form-control" id="telefono" name="telefono" />
                                    </div>

                                    <!-- Columna de tamaño 4 para Ciudad -->
                                    <div class="col-md-4 form-group">
                                      <label for="ciudad">Ciudad</label>
                                      <input type="text" class="form-control" id="ciudad" name="ciudad" />
                                    </div>

                                  </div>

                                  <!-- FILA 3: Observaciones ocupando todo el ancho disponible -->
                                  <div class="row">
                                    <div class="col-md-12 form-group">
                                      <label for="notas">Notas u Observaciones</label>
                                      <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                                    </div>
                                  </div>

                                  <!-- Botones de Acción del Modal -->
                                  <div class="modal-footer" style="padding: 15px 0 0 0; margin-top: 15px;">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Registrar Información</button>
                                  </div>

                        </div>
                        </form>

                      </div>
                    </div>
                  </div>
                </div>';






$tabla .= '
<style>
    /* Estilos de Gobernanza y Limpieza de Grid SmartAdmin */
    #mdialTamanio { width: 90% !important; max-width: 1200px; margin: 30px auto; }
    .smart-form fieldset { padding: 15px 20px 5px 20px !important; background: transparent; }
    .smart-form .label { font-weight: bold; color: #1a237e; font-size: 11px; letter-spacing: 0.3px; margin-bottom: 5px; text-transform: uppercase; }
    
    /* Forzado de Tamaños Homogéneos para Evitar Colapsos Visuales */
    .smart-form .input input, .smart-form .select select, .smart-form .textarea textarea { 
        font-size: 12px !important; 
        height: 32px !important; 
        padding: 6px 10px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    .smart-form .textarea textarea { height: 54px !important; resize: none !important; }
    
    /* Encabezados de Bloques */
    .smart-header-seccion {
        border-bottom: 2px solid #1a237e; 
        color: #1a237e; 
        font-weight: bold; 
        font-size: 11.5px; 
        padding-bottom: 4px; 
        margin: 15px 20px 5px 20px;
    }
    .smart-header-finanzas {
        border-bottom: 2px solid #2e7d32; 
        color: #1b5e20; 
        font-weight: bold; 
        font-size: 11.5px; 
        padding-bottom: 4px; 
        margin: 15px 20px 5px 20px;
    }
</style>

<div class="modal fade" id="modal_nuevo_equipamiento2" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog" id="mdialTamanio">
        <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
            
            <!-- CABECERA DEL MODAL -->
            <div class="modal-header" style="background: #1a237e; color: white; padding: 10px 15px;">
                <button type="button" class="close" data-dismiss="modal" title="SALIR FORMULARIO" style="color: white; opacity: 0.8; font-size: 14px;">
                    <span aria-hidden="true"><i class="fa fa-times-circle"></i> SALIR FORMULARIO</span>
                </button>
                <h4 class="modal-title" style="font-weight: bold; font-size: 13px; text-transform: uppercase; color: white;">
                    <i class="fa fa-edit"></i> REGISTRO PLURIANUAL DE EQUIPAMIENTO MÉDICO E INDUSTRIAL
                </h4>
            </div>
            
            <!-- CUERPO DEL MODAL -->
            <div class="modal-body" style="padding: 15px; background: #fafafa;">
                <form id="form_nuevo_equip" name="form_nuevo_equip" class="smart-form" method="post" >
                    
                    <!-- Campos de control transaccional blindados con m_ -->
                    <input type="hidden" name="form_equip_id" id="m_form_equip_id" value="0">
                    <input type="hidden" name="equip_id" value="' . $equipamiento[0]['equip_id'] . '">

                    <!-- ========================================================================== -->
                    <!-- PARTE 1: COMPUERTA DE GOBERNANZA GEOGRÁFICA (SECUENCIAL POR ROL)           -->
                    <!-- ========================================================================== -->
                    <fieldset >
                        <div class="row">';
                            
                            if($this->tp_adm == 1){ /// MODO: ADMINISTRADOR NACIONAL (SELECCIÓN MANDATORIA)
                                $tabla .= '
                                <section class="col col-6">
                                    <label class="label"><b>1. Seleccione Unidad Ejecutora / Distrital: *</b></label>
                                    <label class="select">
                                        <select id="m_dist_id" name="dist_id" class="form-control" onchange="cargar_establecimientos_por_distrital_modal(this.value)" style="font-weight:bold; color:#1a237e;" required>
                                            <option value="">Seleccione la distrital...</option>';
                                            foreach($distritales as $d) {
                                                $tabla .= '<option value="'.$d['dist_id'].'">'.strtoupper($d['dist_distrital']).'</option>';
                                            }
                                            $tabla .= '
                                        </select><i></i>
                                    </label>
                                </section>';
                            } 
                            else { /// MODO: RESPONSABLE LOCAL (ESTABLECIDO POR SESIÓN AUTOMÁTICA)
                                $tabla .= '<input type="hidden" name="dist_id" id="m_dist_id" value="' . $this->dist_id . '">';
                            }
                            
                            $tabla .= '
                        </div>
                    </fieldset>

                    <!-- ========================================================================== -->
                    <!-- PARTE 2: ESPECIFICACIONES TÉCNICAS (OCULTAS POR DEFECTO PARA EL ADMIN)     -->
                    <!-- ========================================================================== -->
                    <!-- Nota: Si es Responsable ($this->tp_adm != 1), la sección nace visible directamente -->
                    <div id="campos_detalle_equipamiento" style="' . ($this->tp_adm == 1 ? 'display:none;' : '') . ' border-top: 2px dashed #1a237e; padding-top: 15px; margin-top: 10px;">
                        
                        <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                            <b>I. IDENTIFICACIÓN INSTITUCIONAL DE ORIGEN</b>
                        </header>
                        
                        <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                            <div class="row">
                                <section class="col col-4">
                                    <label class="label"><b>Tipo de Registro: *</b></label>
                                    <label class="select">
                                        <select id="m_tp_registro" name="tp_registro" class="form-control" style="font-weight: bold; color: #0d47a1;" onchange="conmutar_tipo_origen_modal(this.value)" required>
                                            <option value="1">1.- ESTABLECIMIENTO DE SALUD</option>
                                            <option value="2">2.- PROYECTO DE INVERSIÓN</option>
                                        </select><i></i>
                                    </label>
                                </section>
                            </div>
                            
                            <div class="row">
                                <!-- SECCIÓN DINÁMICA A: SELECTOR DE CENTROS MÉDICOS -->
                                <div id="m_sec_establecimiento">
                                    <section class="col col-6">
                                        <label class="label"><b>Establecimiento de Salud Vinculado: *</b></label>
                                        <label class="select">
                                            <select id="m_act_id" name="act_id">
                                                <option value="">Seleccione Centro de Salud...</option>';
                                                // Si es responsable, cargamos inicialmente sus centros médicos por defecto
                                                if($this->tp_adm != 1) {
                                                     $establecimientos=$this->model_diagnosticoequip->get_establecimientos_distrital($this->dist_id,$this->gestion);  
                                                    foreach($establecimientos as $est) {
                                                        $tabla .= '<option value="'.$est['act_id'].'">'.strtoupper($est['tipo'].' '.$est['act_descripcion']).'</option>';
                                                    }
                                                }
                                                $tabla .= '
                                            </select><i></i>
                                        </label>
                                    </section>
                                </div>
                                
                                <!-- SECCIÓN DINÁMICA B: TEXTO LIBRE PROYECTO INVERSIÓN -->
                                <div id="m_sec_inversion" style="display:none;">
                                    <section class="col col-6">
                                        <label class="label"><b>Nombre del Proyecto de Inversión: *</b></label>
                                        <label class="textarea"><i class="icon-append fa fa-folder-open"></i>
                                            <textarea rows="2" name="nombre_inversion" id="m_nombre_inversion" placeholder="Escriba el nombre oficial del proyecto de inversión..."></textarea>
                                        </label>
                                    </section>
                                </div>

                                <section class="col col-6">
                                    <label class="label"><b>Nombre del Responsable / Solicitante: *</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-user"></i>
                                        <textarea rows="2" name="responsable" id="m_responsable" required placeholder="Ej. Dr. Carlos Murillo - Jefe del Servicio de Quirófano"></textarea>
                                    </label>
                                </section>
                            </div>
                        </fieldset>

                        <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                            <b>II. ESPECIFICACIONES TÉCNICAS DEL BIEN</b>
                        </header>
                        <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                            <div class="row">
                                <section class="col col-4">
                                    <label class="label"><b>Nombre del Equipamiento Médico: *</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-tag"></i>
                                        <textarea rows="2" name="nombre_equipamiento" id="m_nombre_equipamiento" required placeholder="Ej. MONITOR MULTIPARAMÉTRICO DE 5 PARÁMETROS"></textarea>
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>Servicio / Unidad Destino: *</b></label><label class="textarea"><i class="icon-append fa fa-hospital-o"></i>
                                        <textarea rows="2" name="servicio_unidad" id="m_servicio_unidad" required placeholder="Ej. UNIDAD DE TERAPIA INTENSIVA CORONARIA"></textarea>
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>Ubicación Física Exacta: *</b></label>
                                    <label class="textarea"><i class="icon-append fa fa-map-marker"></i>
                                        <textarea rows="2" name="ubicacion_fisica" id="m_ubicacion_fisica" required placeholder="Ej. Bloque Central - Tercer Piso - Sala de Recuperación"></textarea>
                                    </label>
                                </section>
                                <section class="col col-2">
                                    <label class="label"><b>Tipo de Compra: *</b></label>
                                    <label class="select">
                                        <select id="m_tp_compra" name="tp_compra" required>
                                            <option value="1">REPOSICIÓN</option>        
                                            <option value="2">COMPRA NUEVA</option>        
                                            <option value="3">ADECUACIÓN</option>        
                                        </select><i></i>
                                    </label>
                                </section>
                            </div>
                        </fieldset>

                        <header style="border-bottom: 2px solid #2e7d32; color: #1b5e20; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px; background:transparent;">
                            <b>III. MATRIZ FINANCIERA Y TOTALIZACIÓN DE GESTIONES (Bs.)</b>
                        </header>
                        <fieldset style="background:transparent; padding:0;">
                            <div class="row">
                                <section class="col col-3">
                                    <label class="label"><b>Partida Presupuestaria: *</b></label>
                                    <label class="select">
                                        <select id="m_par_id" name="par_id" required>
                                            <option value="">Seleccione Partida...</option>';
                                            foreach($partidas_gastos as $id_p => $desc_p) {
                                                $tabla .= '<option value="' . $id_p . '">' . $desc_p . '</option>';
                                            }
                                            $tabla .= '
                                        </select><i></i>
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>Cantidad Total: *</b></label>
                                    <label class="input"><i class="icon-append fa fa-calculator"></i>
                                        <input type="text" name="cantidad" id="m_cantidad" value="0" required style="text-align:center; font-weight:bold;">
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>Costo Unitario Referencial: *</b></label>
                                    <label class="input"><i class="icon-append fa fa-money"></i>
                                        <input type="text" name="costo_unitario" id="m_costo_unitario" value="0.00" required style="text-align:right; font-weight:bold;">
                                    </label>
                                </section>
                                <section class="col col-3">
                                    <label class="label"><b>Costo Total Consolidado (Bs.):</b></label>
                                    <label class="input" style="background: #f4f4f4;">
                                        <i class="icon-append fa fa-lock"></i>
                                        <input type="text" id="m_costo_total" value="0.00" readonly style="text-align:right; font-weight:bold; color:#0d47a1; background:#f4f4f4;">
                                    </label>
                                </section>
                            </div>

                            <div class="row" style="background: #f1f8e9; padding: 12px 10px 2px 10px; border: 1px dashed #2e7d32; border-radius: 4px; margin: 5px 0 10px 0;">
                                <section class="col col-2">
                                    <label class="label" style="color:#1b5e20;"><b>TOTAL PLAN</b></label>
                                    <label class="input">
                                        <i class="icon-append fa fa-check-circle"></i>
                                        <input type="text" id="m_total_prog" value="0.00" readonly style="font-weight: bold; color: #2e7d32; background: #eaebd8;">
                                    </label>
                                </section>';
                                
                                for ($i = $equipamiento[0]['g_id_inicio']; $i <= $equipamiento[0]['g_id_fin']; $i++) { 
                                    $tabla .= '
                                    <section class="col col-2">
                                        <label class="label" style="color:#1b5e20;"><b>GESTIÓN ' . $i . ' *</b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-calendar"></i>
                                            <input type="text" class="m-prog-anio" name="gest' . $i . '" id="m_gest' . $i . '" value="0" style="text-align:center; font-weight:bold; color:#2e7d32;">
                                        </label>
                                    </section>';
                                }

                                $tabla .= '
                            </div>
                            
                            <div class="row">
                                <section class="col col-4"><label class="label"><b>Adecuación de Infraestructura:</b></label><label class="textarea"><textarea rows="2" name="ade_infraestructura" id="m_ade_infraestructura"></textarea></label></section>
                                <section class="col col-4"><label class="label"><b>Adecuación de Instalación:</b></label><label class="textarea"><textarea rows="2" name="ade_instalaciones" id="m_ade_instalaciones"></textarea></label></section>
                                <section class="col col-4"><label class="label"><b>Observaciones / Justificaciones:</b></label><label class="textarea"><textarea rows="2" name="observaciones" id="m_observaciones"></textarea></label></section>
                            </div>
                        </fieldset>
                    </div>
                    
                    <!-- BOTONERA DE CONTROL FINAL -->
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
       

        
       $tabla .= '
<script type="text/javascript">
    window.addEventListener("load", function() {
        if (typeof $ !== "undefined") {

            // Bandera de control de rol inyectada desde PHP
            var ES_ADMINISTRADOR = ' . ($this->tp_adm == 1 ? 'true' : 'false') . ';

            // ==========================================================================
            // 1. DISPARADOR DE ALTAS: REINICIO RADICAL DE MEMORIA Y VISIBILIDADES
            // ==========================================================================
            window.abrirModalNuevaAmbulancia = function() {
                var $form = $(\'#form_nuevo_equip\');
                if($form.length > 0) {
                    $form.reset();
                }

                // Reseteo de variables numéricas y ocultamiento preventivo de seguridad
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
                        // Esto permite al Admin interactuar con los combos internos sin bloqueos de caché
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
            // 5. MOTOR ARITMÉTICO CENTRALIZADO (MATRIZ FINANCIERA)
            // ==========================================================================
            $(document).on(\'input\', \'.m-prog-anio, #m_costo_unitario, #m_cantidad\', function() {
                var $input = $(this);
                var limpio = $input.val().replace(\',\', \'.\').replace(/[^0-9.]/g, \'\');
                
                var partes = limpio.split(\'.\');
                if (partes.length > 2) { limpio = partes[0] + \'.\' + partes.slice(1).join(\'\'); }
                $input.val(limpio);

                var cant = parseInt($(\'#m_cantidad\').val()) || 0;
                var unit = parseFloat($(\'#m_costo_unitario\').val()) || 0;
                var total = cant * unit;
                $(\'#m_costo_total\').val(total.toFixed(2));

                var suma = 0;
                $(\'.m-prog-anio\').each(function() {
                    suma += parseFloat($(this).val()) || 0;
                });
                $(\'#m_total_prog\').val(suma.toFixed(2));

                $(\'#alerta_descuadre_modal\').remove();
                if (suma.toFixed(2) !== total.toFixed(2)) {
                    var aviso = \'<div id="alerta_descuadre_modal" class="alert alert-danger" style="margin:10px 0 0 0; padding:6px; font-size:11px; font-weight:bold; width:100%;"><i class="fa fa-times-circle"></i> Restricción Financiera: La sumatoria de las gestiones (\' + suma.toFixed(2) + \' Bs.) debe igualar al Costo Total Consolidado (\' + total.toFixed(2) + \' Bs.).</div>\';
                    $(\'#m_total_prog\').closest(\'.row\').after(aviso);
                }
            });

        }
    });
</script>';



       //  $tabla.=$this->js_validacion();

        return $tabla;
    }






































}
?>