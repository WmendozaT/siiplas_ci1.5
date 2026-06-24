<?php
class CDiagnostico_equipamiento extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('mdiagnostico_equipamiento/model_diagnosticoequip');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->entidad = $this->session->userdata("entidad");
      $this->sistema = $this->session->userdata("sistema");
      $this->sistema_pie = $this->session->userdata("sistema_pie");
      $this->usuario = $this->session->userdata("usuario");
      $this->load->library('lib_diagnostico_equipamiento');
      }else{
          redirect('/','refresh');
      }
    }

    /// formulario principal
    public function diagnostico_principal() {
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $dist_id = $this->dist_id; // Asegúrate de que esta variable esté definida
        $distritales=$this->model_diagnosticoequip->lista_UnidadEjecutora();
        $titulo='';

        if($this->tp_adm==1){
        $titulo .='
         <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <input type="hidden" name="base" value="'.base_url().'">
                
                <!-- Contenedor Premium: Formato Tarjeta Flotante Minimalista -->
                <div style="background: #ffffff; border-radius: 8px; padding: 22px 24px; margin-bottom: 26px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid #edf2f7; position: relative;">
                    
                    <!-- Indicador Flotante Lateral de Categoría -->
                    <div style="position: absolute; left: 0; top: 24px; width: 4px; height: 26px; background: #2563eb; border-radius: 0 4px 4px 0;"></div>
                    
                    <!-- Sección de Encabezado: Título y Metadata del PEI -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #f8fafc; padding-bottom: 14px;">
                        <div>
                            <h2 style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 19px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.2px;">
                                Programación Quinquenal PEI '.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin'].'
                            </h2>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b; font-weight: 500;">
                                <i class="fa fa-calendar-check-o"></i> Planificación y Control de Requerimientos de Equipamiento Médico
                            </p>
                        </div>
                        <!-- Sello de Estado Institucional -->
                        <span style="background: #eff6ff; color: #2563eb; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 6px; letter-spacing: 0.3px; border: 1px solid #bfdbfe;">
                            <i class="fa fa-shield"></i> SIIPLAS ACTIVO
                        </span>
                    </div>
                    
                    <!-- Sección de Botonera: Minimalista y Sincronizada -->
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                        
                        <!-- Botón Registro Principal: Azul Cobalto Profesional -->
                        <button type="button" class="btn btn-sm" 
                                onclick="window.abrirModalNuevaEquipamiento();" 
                                style="font-weight: 600; background: #2563eb; color: #ffffff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 11px; letter-spacing: 0.3px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); transition: all 0.2s;">
                            <i class="fa fa-plus-circle" style="font-size:12px;"></i> NUEVO REQUERIMIENTO
                        </button>
                        
                        <!-- Botón PDF Consolidado: Fondo sutil Gris Ceniza -->
                        <a href="javascript:abreVentana_poa(\''.site_url("").'/Diagnostico_equip/rep_diagnostico_equipamiento/0\');" 
                           title="GENERAR REPORTE CONSOLIDADO" 
                           class="btn btn-sm" 
                           style="font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px; font-size: 11px;">
                            <i class="fa fa-file-pdf-o" style="color: #ef4444;"></i> CONSOLIDADO.PDF
                        </a>
                        
                        <!-- Grupo Desplegable Elegante: Reportes por Distritales -->
                        <div class="btn-group">
                            <button class="btn btn-sm" style="font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px 0 0 6px; font-size: 11px;">
                                <i class="fa fa-file-pdf-o" style="color: #ef4444;"></i> REPORTES DISTRITALES
                            </button>
                            <button class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 10px; border-radius: 0 6px 6px 0; border-left: none;">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" style="border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); border: 1px solid #e2e8f0; padding: 4px 0; font-size: 11.5px;">';
                            foreach($distritales as $d) {
                                $titulo.='
                                <li>
                                  <a href="javascript:abreVentana_poa(\''.site_url("").'/Diagnostico_equip/rep_diagnostico_equipamiento/'.$d['dist_id'].'\');" style="padding: 8px 16px; color: #334155;"><i class="fa fa-file-pdf-o" style="color:#ef4444; margin-right:6px;"></i> '.strtoupper($d['dist_distrital']).'</a>
                                </li>';
                            }
                          $titulo.='
                            </ul>
                        </div>
                        
                        <!-- 🌟 BOTÓN EXCEL CORREGIDO: Removido width:100% y padding exagerado, sincronizado al diseño base -->
                        <button type="button" id="btn_descargar_consolidado" class="btn btn-success btn-sm" 
                                style="font-weight: 600; color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 8px 16px; border-radius: 6px; font-size: 11px; letter-spacing: 0.3px; box-shadow: none;">
                            <i class="fa fa-file-excel-o"></i> EXPORTAR EN EXCEL
                        </button>
                        
                        <!-- 🌟 BOTÓN ADICIONAL: VOLVER / SALIR (Empujado a la derecha en la misma línea) -->
                        <a href="'.site_url("").'/admin/dashboard" 
                           title="VOLVER AL MENÚ ANTERIOR" 
                           class="btn btn-sm" 
                           style="font-weight: 600; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; padding: 7px 16px; border-radius: 6px; font-size: 11px; margin-left: auto;">
                            <i class="fa fa-arrow-left"></i> VOLVER
                        </a>
                    </div>
                </div>
            </article>';

            $titulo .= '
            <script type="text/javascript">
            // 🌟 TEMPORIZADOR DE SEGURIDAD: Blindar la existencia de jQuery antes de activar el evento
            var checkJQueryExcel = setInterval(function() {
                if (typeof $ !== "undefined") {
                    clearInterval(checkJQueryExcel); // Frenar la espera inmediata

                    $(document).on("click", "#btn_descargar_consolidado", function(e) {
                        e.preventDefault();

                        // 1. Generamos el Token único basado en la estampa de tiempo
                        var downloadToken = "token_" + new Date().getTime();
                        
                        // 2. Bloqueamos el botón y levantamos la capa de Loading
                        $("#btn_descargar_consolidado").prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i> Procesando...");
                        $("#loading_descarga_excel").fadeIn(200);
                        
                        // 3. Redireccionamos enviando el token limpio sin parámetros adicionales de distrital
                        // 🌟 AJUSTE: URL limpia apuntando directo al método del controlador
                        window.location.href = "' . site_url("Diagnostico_equip/exportar_consolidado_excel_equipamiento") . '?fileToken=" + downloadToken;
                        
                        // 4. Temporizador cíclico de auditoría de cookies
                        var checkDownloadTimer = setInterval(function() {
                            var cookieValue = getCookie("fileDownloadToken");
                            
                            if (cookieValue === downloadToken) {
                                clearInterval(checkDownloadTimer);
                                // Destruir cookie por seguridad institucional
                                document.cookie = "fileDownloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                                
                                // Restablecer el estado corporativo de la botonera
                                $("#loading_descarga_excel").fadeOut(300);
                                $("#btn_descargar_consolidado").prop("disabled", false).html("<i class=\'fa fa-file-excel-o\'></i> DESCARGAR CONSOLIDADO");
                            }
                        }, 400); 
                    });

                    function getCookie(name) {
                        var parts = document.cookie.split("; " + name + "=");
                        if (parts.length === 2) return parts.pop().split(";").shift();
                        return "";
                    }

                }
            }, 50);
            </script>';
        }
        else{
            $distrital=$this->model_diagnosticoequip->get_distrital($dist_id);
            $titulo .='
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <input type="hidden" name="base" value="'.base_url().'">
                
                <!-- Contenedor Premium: Formato Tarjeta Flotante Minimalista -->
                <div style="background: #ffffff; border-radius: 8px; padding: 22px 24px; margin-bottom: 26px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04); border: 1px solid #edf2f7; position: relative;">
                    
                    <!-- Indicador Flotante Lateral de Categoría -->
                    <div style="position: absolute; left: 0; top: 24px; width: 4px; height: 26px; background: #2563eb; border-radius: 0 4px 4px 0;"></div>
                    
                    <!-- Sección de Encabezado: Título y Metadata del PEI -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid #f8fafc; padding-bottom: 14px;">
                        <div>
                            <h2 style="font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; font-size: 19px; font-weight: 700; color: #1e293b; margin: 0; letter-spacing: -0.2px;">
                                Programación Quinquenal PEI '.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin'].'
                            </h2>
                            <p style="margin: 4px 0 0 0; font-size: 12px; color: #64748b; font-weight: 500;">
                                <i class="fa fa-calendar-check-o"></i> Planificación y Control de Requerimientos de Equipamiento Médico - '.strtoupper($distrital[0]['dist_distrital']).'
                            </p>
                        </div>
                        <!-- Sello de Estado Institucional -->
                        <span style="background: #eff6ff; color: #2563eb; font-size: 11px; font-weight: 600; padding: 5px 12px; border-radius: 6px; letter-spacing: 0.3px; border: 1px solid #bfdbfe;">
                            <i class="fa fa-shield"></i> SIIPLAS ACTIVO
                        </span>
                    </div>
                    
                    <!-- Sección de Botonera: Minimalista y Sincronizada -->
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                        
                        <!-- Botón Registro Principal: Azul Cobalto Profesional -->
                        <button type="button" class="btn btn-sm" 
                                onclick="window.abrirModalNuevaEquipamiento();" 
                                style="font-weight: 600; background: #2563eb; color: #ffffff; border: none; padding: 8px 16px; border-radius: 6px; font-size: 11px; letter-spacing: 0.3px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); transition: all 0.2s;">
                            <i class="fa fa-plus-circle" style="font-size:12px;"></i> NUEVO REQUERIMIENTO
                        </button>
                        
                        <!-- Botón PDF Consolidado: Fondo sutil Gris Ceniza -->
                        <a href="javascript:abreVentana_poa(\''.site_url("").'/Diagnostico_equip/rep_diagnostico_equipamiento/'.$this->dist_id.'\');" 
                           title="GENERAR REPORTE" 
                           class="btn btn-sm" 
                           style="font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 14px; border-radius: 6px; font-size: 11px;">
                            <i class="fa fa-file-pdf-o" style="color: #ef4444;"></i> Equipamiento.PDF
                        </a>
                        
                        <!-- 🌟 BOTÓN ADICIONAL: VOLVER / SALIR (Empujado a la derecha en la misma línea) -->
                        <a href="'.site_url("").'/admin/dashboard" 
                           title="VOLVER AL MENÚ ANTERIOR" 
                           class="btn btn-sm" 
                           style="font-weight: 600; color: #64748b; background: #ffffff; border: 1px solid #e2e8f0; padding: 7px 16px; border-radius: 6px; font-size: 11px; margin-left: auto;">
                            <i class="fa fa-arrow-left"></i> VOLVER
                        </a>
                    </div>
                </div>
            </article>';
        }

        $data['titulo']=$titulo;
        if(count($equipamiento)!=0){
            $data['listado']=$this->lib_diagnostico_equipamiento->listado_equipamiento($equipamiento);
        }
        else{
            echo "Error !!!";
        }

        $this->load->view('admin/diagnostico_equipamiento/View_diagnostico_equipamiento', $data);

    }



    ///// Guardar Registro de Equipamiento
    public function guardar_requerimiento_equipamiento() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // Sanitización estricta contra ataques de inyección XSS
            $post = $this->security->xss_clean($this->input->post());
            $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
            // Recolección y tipado de variables maestras con blindaje preventivo isset()
            $form_equip_id       = isset($post['form_equip_id']) ? intval($post['form_equip_id']) : 0;
            $equip_id            = isset($post['equip_id']) ? intval($post['equip_id']) : 0;
            $dist_id             = isset($post['dist_id']) ? intval($post['dist_id']) : 0;
            $tp_registro         = isset($post['tp_registro']) ? intval($post['tp_registro']) : 1;
            
            // Mapeo condicional reactivo según tp_registro (1: Establecimiento, 2: Proyecto Inversión)
            $act_id              = ($tp_registro == 1 && isset($post['act_id'])) ? intval($post['act_id']) : 0;
            $nombre_inversion    = ($tp_registro == 2 && isset($post['nombre_inversion'])) ? trim(strtoupper($post['nombre_inversion'])) : '';

            $responsable         = isset($post['responsable']) ? trim(strtoupper($post['responsable'])) : '';
            $nombre_equipamiento = isset($post['nombre_equipamiento']) ? trim(strtoupper($post['nombre_equipamiento'])) : '';
            $servicio_unidad     = isset($post['servicio_unidad']) ? trim(strtoupper($post['servicio_unidad'])) : '';
            $ubicacion_physica   = isset($post['ubicacion_fisica']) ? trim(strtoupper($post['ubicacion_fisica'])) : '';
            $tp_compra           = isset($post['tp_compra']) ? intval($post['tp_compra']) : 1;
            $par_id              = isset($post['par_id']) ? intval($post['par_id']) : 0;
            
            // Captura hermética de los textareas del Modal de Modificación
            $tp_adecuacion_infra = isset($post['ade_infraestructura']) ? trim(strtoupper($post['ade_infraestructura'])) : '';
            $tp_adecuacion_instalation   = isset($post['ade_instalaciones']) ? trim(strtoupper($post['ade_instalaciones'])) : '';
            $observaciones       = isset($post['observaciones']) ? trim(strtoupper($post['observaciones'])) : '';

            // Variables numéricas de control financiero (Soporta valores formateados con comas desde el JS)
            $cantidad       = isset($post['cantidad']) ? intval($post['cantidad']) : 0;
            $costo_unitario = isset($post['costo_unitario']) ? floatval(str_replace(',', '', $post['costo_unitario'])) : 0.00;
            $costo_total    = $cantidad * $costo_unitario; // Cálculo matemático forzado y blindado en el backend

            // --------------------------------------------------------------------------
            // CAPA 2: SEGUNDO BLOQUEO DE INTEGRIDAD MATEMÁTICA EN EL SERVIDOR
            // --------------------------------------------------------------------------
            $suma_quinquenio = 0;
            for ($anio = 2026; $anio <= 2030; $anio++) {
                $suma_quinquenio += isset($post['gest' . $anio]) ? floatval(str_replace(',', '', $post['gest' . $anio])) : 0;
            }

            // Si hay un descuadre contable, el servidor detiene el query y devuelve el aviso ajustado al JS
            if (abs($suma_quinquenio - $costo_total) > 0.01) {
                $result = array(
                    'status'  => 'error', // 🌟 Sincronizado con tu JavaScript
                    'message' => 'La sumatoria distribuida en el quinquenio (' . number_format($suma_quinquenio, 2, ',', '.') . ' Bs.) no coincide con el Costo Total (' . number_format($costo_total, 2, ',', '.') . ' Bs.). Verifique la grilla.'
                );
                $this->_retornar_json($result);
                return;
            }

            // --------------------------------------------------------------------------
            // CAPA 3: PROCESAMIENTO DE TRANSACCIÓN EN BASE DE DATOS (POSTGRESQL)
            // --------------------------------------------------------------------------
            $this->db->trans_begin();

            try {
                // Estructuramos la matriz sincronizada al 100% con tu base de datos física real
                $data_form = array(
                    'equip_id'            => $equip_id,
                    'dist_id'             => $dist_id,
                    'tp_registro'         => $tp_registro,
                    'act_id'              => $act_id,
                    'nombre_inversion'    => $nombre_inversion,
                    'responsable'         => $responsable,
                    'nombre_equipamiento' => $nombre_equipamiento,
                    'servicio_unidad'     => $servicio_unidad,
                    'ubicacion_fisica'    => $ubicacion_physica,
                    'tp_compra'           => $tp_compra,
                    'cantidad'            => $cantidad,
                    'costo_unitario'      => $costo_unitario,
                    'costo_total'         => $costo_total,
                    'par_id'              => $par_id,
                    'tp_adecuacion_infra'       => $tp_adecuacion_infra,
                    'tp_adecuacion_instalacion' => $tp_adecuacion_instalation,   
                    'observaciones'       => $observaciones,
                    'estado'              => 1 
                );

                // Conmutación automática transaccional
                if ($form_equip_id == 0) {
                    // MODO A: Inserción de Nuevo Registro (INSERT)
                    $this->db->insert('formulario_diagnostico_equipamiento', $data_form);
                    $form_equip_id = $this->db->insert_id();
                } else {
                    // MODO B: Modificación de Registro Existente (UPDATE)
                    $this->db->where('form_equip_id', $form_equip_id);
                    $this->db->update('formulario_diagnostico_equipamiento', $data_form);

                    // Limpieza preventiva: Purgamos la temporalidad antigua de este equipo para no dejar residuos del pasado
                    $this->db->where('form_equip_id', $form_equip_id);
                    $this->db->delete('temporalidad_diagnostico_equipamiento');
                }

                // 4. BUCLE DE PERSISTENCIA: Registramos año por año la temporalidad (2026 a 2030)
                for ($anio = $equipamiento[0]['g_id_inicio']; $anio <= $equipamiento[0]['g_id_fin']; $anio++) {
                    $monto_anio = isset($post['gest' . $anio]) ? floatval(str_replace(',', '', $post['gest' . $anio])) : 0;

                    if ($monto_anio >= 0) {
                        $data_temp = array(
                            'form_equip_id' => $form_equip_id,
                            'g_id'          => $anio,
                            'prog_equi'     => $monto_anio
                        );
                        $this->db->insert('temporalidad_diagnostico_equipamiento', $data_temp);
                    }
                }

                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = array(
                        'status'  => 'error', 
                        'message' => 'Error de consistencia al impactar las tablas relacionales de la CNS.'
                    );
                } else {
                    $this->db->trans_commit();
                    
                    // 🌟 RESPUESTA ULTRA-LIGERA: Solo el visto bueno al JS
                    $result = array(
                        'status'        => 'success',
                        'form_equip_id' => $form_equip_id
                    );
                }

            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result = array(
                    'status'  => 'error', 
                    'message' => 'Excepción crítica en la base de datos: ' . $e->getMessage()
                );
            }

            // Despachamos la respuesta JSON limpia hacia la vista
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit; // Detiene la ejecución para asegurar que no se envíe HTML residual

        } else {
            show_404();
        }
    }


   public function obtener_requerimiento_por_id() {
        if ($this->input->is_ajax_request()) {
         
            $form_equip_id = intval($this->input->post('form_equip_id'));

            // 1. Obtener la fila correspondiente de la Tabla Maestra
            $q_maestro = $this->db->query("
                SELECT * FROM public.formulario_diagnostico_equipamiento 
                WHERE form_equip_id = ? AND estado = 1
            ", array($form_equip_id));
            $maestro = $q_maestro->row_array();

            if ($maestro) {
                // 2. Obtener el Quinquenio de la Tabla Detalle asociado a la Ficha
                $q_detalle = $this->db->query("
                    SELECT g_id, prog_equi FROM public.temporalidad_diagnostico_equipamiento 
                    WHERE form_equip_id = ?
                    ORDER BY g_id ASC
                ", array($form_equip_id));
                $detalles = $q_detalle->result_array();

                $result = array(
                    'status'   => 'success',
                    'maestro'  => $maestro,
                    'detalles' => $detalles
                );
            } else {
                $result = array('status' => 'error', 'message' => 'El requerimiento especificado no existe o fue dado de baja.');
            }

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit;
        }
    }




    public function get_establecimientos_por_dist_json() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX
        if ($this->input->is_ajax_request()) {
            
            // Sanitización estricta de variables contra inyecciones XSS
            $dist_id = $this->security->xss_clean($this->input->post('dist_id'));
            $dist_id = intval($dist_id);

            $lista_json = array();

            if ($dist_id > 0) {
                // Consulta al Modelo pasándole la distrital del POST y la gestión de la sesión
                // NOTA TÉCNICA: Asegúrate de que $this->gestion esté bien cargada en el constructor
                $establecimientos = $this->model_diagnosticoequip->get_establecimientos_distrital($dist_id, $this->gestion);

                if (!empty($establecimientos)) {
                    foreach ($establecimientos as $est) {
                        // Formateamos la matriz con las llaves exactas que espera tu bucle $.each
                        $lista_json[] = array(
                            'act_id'          => intval($est['act_id']),
                            'establecimiento' => trim(strtoupper($est['tipo'] . ' ' . $est['act_descripcion']))
                        );
                    }
                }
            }

            // 🌟 EL BLINDAJE RAÍZ: Limpiamos cualquier Notice de PHP remanente en el búfer de salida
            if (ob_get_length()) {
                ob_clean();
            }

            // 2. Despacho seguro con cabeceras HTTP explícitas de CodeIgniter
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($lista_json));

        } else {
            show_404();
        }
    }


    //// eliminar Equipamiento 
     public function eliminar_requerimiento_logico() {
        // Validar estrictamente que sea una petición legítima por AJAX
        if ($this->input->is_ajax_request()) {
            // Recolección y tipado forzado del ID enviado por el JS
            $form_equip_id = intval($this->input->post('form_equip_id'));

            if ($form_equip_id > 0) {
                
                // Mapear la actualización: estado = 3 (Baja institucional en SIIPLAS)
                $this->db->where('form_equip_id', $form_equip_id);
                $operacion_exitosa = $this->db->update('public.formulario_diagnostico_equipamiento', array('estado' => 3));

                if ($operacion_exitosa) {
                    // Visto bueno ligero al JS para disparar el location.reload()
                    $result = array('status' => 'success');
                } else {
                    $result = array(
                        'status'  => 'error', 
                        'message' => 'Error interno: PostgreSQL rechazó la sentencia de actualización del estado.'
                    );
                }
            } else {
                $result = array(
                    'status'  => 'error', 
                    'message' => 'Error de consistencia: El identificador de registro enviado no es válido.'
                );
            }

            // Despacho de JSON nativo libre del error de función '_retornar_json' inexistente
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit; // Cortar flujos residuales de HTML
        } else {
            show_404();
        }
    }




    ///// Get Recupera listado de Adicionales
     public function get_formulario_adicionales_modal_html() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post('form_equip_id')) {
            
            // Sanitización estricta de variables contra inyecciones XSS
            $form_equip_id = $this->security->xss_clean($this->input->post('form_equip_id'));
            $form_equip_id = intval($form_equip_id);
            
            $dist_id = $this->security->xss_clean($this->input->post('dist_id'));
            $dist_id = intval($dist_id);

            // 2. Consulta al Modelo para traer el listado relacional de accesorios de este equipo
            $lista_adicionales = $this->model_diagnosticoequip->get_list_adcionales_x_equipo($form_equip_id);

            // 3. Maquetación del Formulario Superior de Inserción Rápida (Estilo Premium)
            $html = '
            <!-- Se asignó el ID id="form_interno_adicionales" para el mapeo estricto del JavaScript -->
            <form id="form_interno_adicionales" class="smart-form" style="background: #f8fafc; padding: 16px; border-radius: 6px; border: 1px dashed #bfdbfe; margin-bottom: 20px;">
                <input type="hidden" name="form_equip_id" value="' . $form_equip_id . '">
                <input type="hidden" name="dist_id" value="' . $dist_id . '">
                
                <div class="row">
                    <div class="col-md-3 form-group" style="margin-bottom:0;">
                        <label style="color:#1e293b; font-weight:bold; font-size:11px; display:block; margin-bottom:4px;">TIPO *</label>
                        <!-- Se ajustó name y el ID a tipo_adi para sincronización con el controlador de guardado -->
                        <select class="form-control" id="tipo_adi" name="tipo_adi" required style="font-weight:bold; color:#2563eb; height:32px;">
                            <option value="1">1.- ACCESORIO</option>        
                            <option value="2">2.- SOFTWARE</option>
                        </select>
                    </div>
                    <div class="col-md-6 form-group" style="margin-bottom:0;">
                        <label style="color:#1e293b; font-weight:bold; font-size:11px; display:block; margin-bottom:4px;">DESCRIPCIÓN DEL COMPONENTE / ACCESORIO ADICIONAL *</label>
                        <!-- Se ajustó name a descripcion_adicional para que coincida con el serialize() -->
                        <input type="text" class="form-control" name="descripcion_adicional" id="m_descripcion_adicional" required placeholder="EJ. BATERÍA RECARGABLE DE LITIO" style="text-transform: uppercase; font-weight:500; height:32px;">
                    </div>
                    
                    <div class="col-md-3 form-group" style="margin-bottom:0; padding-top: 19px;">
                        <!-- Se añadió el ID id="btn_sub_registrar" para controlar el Loading -->
                        <button type="submit" id="btn_sub_registrar" class="btn btn-primary btn-sm" style="background: #2563eb; border: none; font-weight: bold; width: 100%; height: 32px; border-radius: 4px; color:white; font-size:11px; box-shadow: 0 2px 6px rgba(37,99,235,0.15);">
                            <i class="fa fa-plus-circle"></i> AGREGAR ITEM
                        </button>
                    </div>
                </div>
            </form>';

            // 4. Maquetación de la Tabla Inferior para Enlistar los Registros Adicionales
            $html .= '
            <div style="background: #ffffff; padding: 15px; border: 1px solid #edf2f7; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                <header style="border-bottom: 1px solid #edf2f7; font-weight: bold; color: #1e293b; padding-bottom: 6px; margin-bottom: 10px; font-size:11.5px; background: transparent; letter-spacing:0.3px;">
                    <i class="fa fa-list" style="color:#2563eb;"></i> COMPONENTES COMPLEMENTARIOS REGISTRADOS
                </header>
                
                <div style="max-height: 250px; overflow-y: auto; border: 1px solid #f1f5f9; border-radius: 4px;">
                    <table class="table table-bordered table-striped" style="width: 100%; font-size: 11.5px; margin-bottom: 0; background:white; table-layout: fixed;">
                        <thead>
                            <tr style="background: #f1f5f9; color: #475569; height: 28px;">
                                <th style="width: 5%; text-align: center; vertical-align: middle; padding: 4px; font-weight:700;">#</th>
                                <th style="width: 10%; text-align: center; vertical-align: middle; padding: 4px; font-weight:700;">BORRAR</th>
                                <th style="width: 20%; vertical-align: middle; padding: 4px; font-weight:700; text-align:center;">TIPO</th>
                                <th style="width: 65%; vertical-align: middle; padding: 4px; font-weight:700; padding-left:8px;">DESCRIPCIÓN DEL ACCESORIO ADICIONAL</th>
                            </tr>
                        </thead>
                        <tbody id="m_body_tabla_adicionales">';
                        
                        $sub_nro = 0;
                        if (!empty($lista_adicionales)) {
                            foreach ($lista_adicionales as $item) {
                                $sub_nro++;
                                
                                // Generación de etiquetas estilizadas para el tipo
                                $badge_tipo = ($item['tipo_detalle_nombre'] == 'ACCESORIO' || $item['tp_equi_ad'] == 1) 
                                    ? '<span class="label" style="background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; font-weight:bold; padding:2px 6px; border-radius:4px;">ACCESORIO</span>' 
                                    : '<span class="label" style="background:#f5f3ff; color:#7c3aed; border:1px solid #ddd6fe; font-weight:bold; padding:2px 6px; border-radius:4px;">SOFTWARE</span>';

                                $html .= '
                                <tr style="height:32px; vertical-align: middle; border-bottom:1px solid #f1f5f9;">
                                    <td style="text-align: center; font-weight: bold; background: #f8fafc; color: #64748b; vertical-align: middle;">' . $sub_nro . '</td>
                                    
                                    <!-- 🌟 BOTÓN ELIMINAR REAL: Estilizado en Rojo con captura asíncrona de IDs -->
                                    <td style="text-align: center; vertical-align: middle; padding:0;">
                                        <button type="button" class="btn btn-danger btn-xs btn_eliminar_sub_item" 
                                                data-sub-id="' . $item['adi_equi_id'] . '" 
                                                data-form-id="' . $form_equip_id . '" 
                                                style="background:#dc2626; color:#ffffff; border:none; padding:3px 8px; border-radius:4px; font-size:10px;" 
                                                title="Remover este componente">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    
                                    <td style="text-align: center; vertical-align: middle; padding:0;">' . $badge_tipo . '</td>
                                    <td style="text-align: left; padding-left: 8px; text-transform: uppercase; font-weight:500; color:#1e293b; vertical-align: middle;">' . htmlspecialchars($item['detalle_equi_adi'], ENT_QUOTES, 'UTF-8') . '</td>
                                </tr>';
                            }
                        } else {
                            $html .= '
                            <tr>
                                <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic; padding: 25px 0; font-size:11px;">
                                    📋 Ningún accesorio adicional registrado todavía para este requerimiento.
                                </td>
                            </tr>';
                        }
                        
                        $html .= '
                        </tbody>
                    </table>
                </div>
            </div>';

            // 5. Limpieza del búfer de salida para erradicar cualquier Notice flotante de PHP
            if (ob_get_length()) {
                ob_clean();
            }

            // 6. Despacho y entrega del objeto JSON
            $result = array('respuesta' => 'correcto', 'html' => $html);
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } else {
            show_404();
        }
    }



    public function guardar_sub_item_adicional() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post('form_equip_id')) {
            
            // Cargar la librería de base de datos de forma explícita si no está en el autoload
            $this->load->database();

            // 2. Recolección, sanitización XSS y tipado forzado de variables del POST
            $form_equip_id    = intval($this->security->xss_clean($this->input->post('form_equip_id')));
            $tp_equi_adi      = intval($this->security->xss_clean($this->input->post('tipo_adi'))); // Mapeado desde <select name="tipo_adi">
            $detalle_equi_adi = $this->security->xss_clean($this->input->post('descripcion_adicional')); // Mapeado desde <input name="descripcion_adicional">
            
            // Forzar formateo a mayúsculas y remover espacios vacíos periféricos
            $detalle_equi_adi = trim(strtoupper($detalle_equi_adi));

            // 3. Validación de consistencia en el Servidor antes de impactar PostgreSQL
            if ($form_equip_id > 0 && !empty($detalle_equi_adi)) {
                
                // 🌟 MATRIZ SINCRONIZADA AL 100% CON TU TABLA REAL public.equipamiento_adicionales
                $data_insert = array(
                    'form_equip_id'    => $form_equip_id,
                    'tp_equi_adi'      => $tp_equi_adi,
                    'detalle_equi_adi' => $detalle_equi_adi
                );

                // 4. Inserción mediante Active Record clásico
                $insercion_exitosa = $this->db->insert('public.equipamiento_adicionales', $data_insert);

                if ($insercion_exitosa) {
                    $result = array('status' => 'success');
                } else {
                    $result = array(
                        'status'  => 'error', 
                        'message' => 'Error de base de datos: PostgreSQL rechazó la inserción en equipamiento_adicionales.'
                    );
                }
            } else {
                $result = array(
                    'status'  => 'error', 
                    'message' => 'Error de validación: La descripción del componente adicional no puede estar vacía.'
                );
            }

            // 5. Despacho y entrega del objeto JSON nativo libre de funciones obsoletas
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit; // Cortar cualquier ejecución o remanente HTML accidental del Framework
        } else {
            // Si intentan ingresar directo por URL sin ser AJAX, denegar el acceso
            show_404();
        }
    }

    /// eliminar registro de sub item adicional
    public function eliminar_sub_item_adicional() {
        // 1. Validar estrictamente que provenga de una llamada legítima de AJAX
        if ($this->input->is_ajax_request()) {
            
            // Cargar base de datos nativa (PostgreSQL)
            $this->load->database();
            
            // Recolección y tipado forzado del ID primario enviado por el JS
            $adi_equi_id = intval($this->input->post('adi_equi_id'));

            if ($adi_equi_id > 0) {
                
                // 2. Ejecutar DELETE físico sobre la tabla relacional mapeando tu PK
                $this->db->where('adi_equi_id', $adi_equi_id);
                $operacion_exitosa = $this->db->delete('public.equipamiento_adicionales');

                if ($operacion_exitosa) {
                    $result = array('status' => 'success');
                } else {
                    $result = array(
                        'status'  => 'error', 
                        'message' => 'Error relacional: PostgreSQL rechazó la eliminación física de la fila.'
                    );
                }
            } else {
                $result = array(
                    'status'  => 'error', 
                    'message' => 'Error de consistencia: El identificador de sub-registro no es válido.'
                );
            }

            // 3. Despacho y entrega de JSON plano nativo compatible con PHP 5.6
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
            exit; // Prevenir remanentes HTML
        } else {
            show_404();
        }
    }


    /// Reporte Formulario Diagnostico Pei Equipamiento
    public function reporte_formulario_equipamiento($dist_id){
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $data['reporte']= $this->rep_diagnostico_equipamiento($dist_id);
        $data['pie_rep']='dnp';
        $this->load->view('admin/diagnostico_equipamiento/View_report_form_diagequipamiento', $data);
        //echo $data['reporte'];
    }

    //// Detalle Reporte
    public function rep_diagnostico_equipamiento($dist_id) {
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        if($dist_id==0){
            $listado=$this->model_diagnosticoequip->get_consolidado_formulario_diagnostico_activo($equipamiento[0]['equip_id']); /// Consolidado
        }
        else{
            $listado=$this->model_diagnosticoequip->get_distrital_formulario_diagnostico_activo($equipamiento[0]['equip_id'],$dist_id); /// distrital
        }

        $tabla='';
       // $tabla = $this->style_report();
        $tabla .= ' 
        <style>
            /* Estilos globales obligatorios para html2pdf (Estandarización CNS) */
            p.bold { font-weight: bold; color: #1a237e; font-size: 11px; margin-bottom: 5px; text-transform: uppercase; }
            .box-container { border: 1px solid #b3b3b3; background: #fafafa; font-size: 10px; padding: 6px; margin-bottom: 12px; border-radius: 3px; }
            
            /* Configuración Maestra de Rejilla del Reporte */
            .tabla-datos { width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; }
            .tabla-datos th { 
                background: #404040; 
                color: #ffffff; 
                font-weight: bold; 
                font-size: 6.5px; 
                text-align: center; 
                vertical-align: middle; 
                border: 0.5px solid #ffffff;
                padding: 5px 2px;
            }
            .tabla-datos td { 
                font-size: 6.5px; 
                vertical-align: middle; 
                border: 0.5px solid #b3b3b3; 
                padding: 4px 3px; 
            }
            
            /* Clases de Control de Temporalidad y Redondeo */
            .celda-monto-activa { background: #f0fdf4; font-weight: bold; color: #16a34a; text-align: right; }
            .celda-monto-vacia { color: #94a3b8; text-align: right; }
        </style>
        <page orientation="landscape" backtop="28mm" backbottom="15mm" backleft="10mm" backright="10mm">
        '.$this->cabecera_report($equipamiento,$dist_id).'

        <p class="bold">1. Objetivo</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Recopilar Informacion sobre el equipamiento medico por Establecimiento de Salud de la Regional/Distrital
        </div>';
            
        $tabla.='
        <table class="tabla-datos">
        <thead>
            <tr>
                <th style="width: 2%;">#</th>
                <th style="width: 7%;">DISTRITAL</th>
                <th style="width: 8%;">RESPONSABLE</th>
                <th style="width: 8%;">ESTABLECIMIENTO / INVERSIÓN</th>
                <th style="width: 8%;">NOMBRE DEL EQUIPO</th>
                <th style="width: 8%;">SERVICIO / UNIDAD</th>
                <th style="width: 8%;">UBICACIÓN FÍSICA</th>
                <th style="width: 4%;">TIPO COMPRA</th>
                <th style="width: 4%;">PARTIDA</th>
                <th style="width: 3%;">CANT.</th>
                <th style="width: 5%;">COSTO UNIT.</th>
                <th style="width: 6%;">COSTO TOTAL</th>
                <th style="width: 4.5%;">2026</th>
                <th style="width: 4.5%;">2027</th>
                <th style="width: 4.5%;">2028</th>
                <th style="width: 4.5%;">2029</th>
                <th style="width: 4.5%;">2030</th>
                <th style="width: 7%;">OBSERVACIÓN</th>
            </tr>
        </thead>
        <tbody>';
        
        $nro = 0; 
        foreach ($listado as $row) {
            $nro++;
            
            $establecimiento_detallado = '';
            if ($row['tp_registro'] == 1) {
                $establecimiento_detallado = strtoupper($row['tipo_establecimiento'] . ' ' . $row['nombre_establecimiento']) . ' [' . strtoupper($row['abrev_establecimiento']) . ']';
            } else {
                $establecimiento_detallado = 'P.I. - ' . strtoupper($row['nombre_establecimiento']);
            }

            $tabla .= '
            <tr style="background: #ffffff;">
                <td style="text-align: center; font-weight: bold; background: #f8fafc; color: #94a3b8; height:2%;width: 2%;">' . $nro . '</td>
                <td style="text-align: left; font-weight: bold; color: #475569;width: 7%;">' . strtoupper($row['dist_distrital']) . '</td>
                <td style="text-align: left;width: 8%;">' . strtoupper($row['responsable']) . '</td>
                <td class="texto-bold-plomo" style="text-align: left;width: 8%;">' . $establecimiento_detallado . '</td>
                <td class="texto-bold-plomo" style="text-align: left;width: 8%;">' . strtoupper($row['nombre_equipamiento']) . '</td>
                <td style="text-align: left;width: 8%;">' . strtoupper($row['servicio_unidad']) . '</td>
                <td style="text-align: left;width: 8%;">' . strtoupper($row['ubicacion_fisica']) . '</td>
                <td style="text-align: left;width: 4%;">' . strtoupper($row['tp_compra_nombre']) . '</td>
                <td style="text-align: center; font-weight: bold;width: 4%;">' . $row['par_codigo'] . '</td>
                <td style="text-align: center; font-weight: bold; color: #334155;width: 3%;">' . intval($row['cantidad']) . '</td>
                <td style="text-align: right;width: 5%;">' . number_format($row['costo_unitario'], 2, '.', ',') . '</td>
                <td style="text-align: right; font-weight: bold; background: #f8fafc; color: #334155;width: 6%;">' . number_format($row['costo_total'], 2, '.', ',') . '</td>
                
                <!-- Distribución Anual en Plomo Ceniza con Resalte de Contraste Suave -->
                <td style="width: 4.5%;" class="' . ($row['g_2026'] > 0 ? 'celda-monto-activa' : 'celda-monto-vacia') . '">' . number_format($row['g_2026'], 2, '.', ',') . '</td>
                <td style="width: 4.5%;" class="' . ($row['g_2027'] > 0 ? 'celda-monto-activa' : 'celda-monto-vacia') . '">' . number_format($row['g_2027'], 2, '.', ',') . '</td>
                <td style="width: 4.5%;" class="' . ($row['g_2028'] > 0 ? 'celda-monto-activa' : 'celda-monto-vacia') . '">' . number_format($row['g_2028'], 2, '.', ',') . '</td>
                <td style="width: 4.5%;" class="' . ($row['g_2029'] > 0 ? 'celda-monto-activa' : 'celda-monto-vacia') . '">' . number_format($row['g_2029'], 2, '.', ',') . '</td>
                <td style="width: 4.5%;" class="' . ($row['g_2030'] > 0 ? 'celda-monto-activa' : 'celda-monto-vacia') . '">' . number_format($row['g_2030'], 2, '.', ',') . '</td>
                
                <td style="text-align: left;width: 7%;">' . htmlspecialchars(strtoupper($row['observaciones']), ENT_QUOTES, 'UTF-8') . '</td>
            </tr>';
        }

        if ($nro === 0) {
            $tabla .= '
            <tr style="background: #ffffff;">
                <td style="text-align: center; font-weight: bold; background: #f8fafc;">-</td>
                <td colspan="17" style="text-align: center; color: #94a3b8; font-style: italic; padding: 4px 0;">
                    No se identificaron requerimientos de registrados para la presente gestión distrital.
                </td>
            </tr>';
        }

        $tabla .= '
        </tbody>
    </table>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>FIRMA</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }





    /// cabecera reporte
    public function cabecera_report($equipamiento,$dist_id) {
        
        if($dist_id==0){
            $tit_distrital='CONSOLIDADO INSTITUCIONAL';
        }
        else{
            $distrital=$this->model_diagnosticoequip->get_distrital($dist_id);
            $tit_distrital=strtoupper($distrital[0]['dist_distrital']);
        }

        $tabla='';
        $tabla.='
        <page_header>
            <!-- Quitamos paddings en px que rompen el DOM del PDF. Usamos margin-lateral para alinear con la página -->
            <div style="padding-top: 25px; margin-left: 15mm; margin-right: 15mm; display: block;">
                
                <!-- Forzamos table-layout fixed para que respete estrictamente los porcentajes en Portrait y Landscape -->
                <table style="width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; table-layout: fixed;">
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: middle; font-size:9px;">
                            <b>DPTO. NAL. PLANIFICACIÓN</b>
                        </td>
                        
                        <td style="width: 60%; text-align: center; vertical-align: middle;">
                            <span style="font-size: 13px; font-weight: bold; color: #004640; letter-spacing: 0.5px;">
                                '.$this->entidad.'
                            </span>
                            <br>
                            <span style="font-size: 17px; font-weight: bold; color: #212121; line-height: 1.2;">
                                PROGRAMACIÓN QUINQUENAL PEI '.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin' ].'
                            </span>
                            <br>
                            <span style="font-size: 11px; font-weight: bold; color: #212121; line-height: 1.2;">
                                '.$tit_distrital.'
                            </span>
                        </td>
                        
                        <td style="width: 20%; text-align: right; vertical-align: middle; font-size: 8px; color: #424242; line-height: 1.3;">
                            PERIODO: <b style="color: #212121;">'.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin' ].'</b>
                            <br>
                            Fecha de Impresión: '.date('d/m/Y').'
                        </td>
                    </tr>
                </table>
                
                <!-- Barras Estéticas alineadas perfectamente con los datos -->
                <div style="width: 100%; height: 3px; background-color: #004640; margin-top: 12px; margin-bottom: 2px;"></div>
                <div style="width: 100%; height: 1px; background-color: #e0e0e0;"></div>
            </div>
        </page_header>
            <!-- ==================== PIE DE PÁGINA ESTÁTICO ==================== -->
        <page_footer>
            <!-- Usamos los mismos márgenes en milímetros que la cabecera y el contenido base -->
            <div style="margin-left: 15mm; margin-right: 15mm; padding-bottom: 15px; display: block;">
                
                <!-- Línea divisoria superior limpia -->
                <div style="width: 100%; height: 1px; background-color: #cccccc; margin-bottom: 6px;"></div>
                
                <!-- Tabla elástica con ancho total controlado al 100% -->
                <table style="width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; table-layout: fixed;">
                    <tr>
                        <!-- Zona Izquierda (50% proporcional) -->
                        <td style="width: 50%; text-align: left; vertical-align: middle; font-size: 8.5px; color: #666666; font-weight: 500;">
                            '.$this->sistema_pie.'
                        </td>
                        
                        <!-- Zona Derecha (50% proporcional) -->
                        <td style="width: 50%; text-align: right; vertical-align: middle; font-size: 8.5px; color: #424242; font-weight: bold;">
                         '.$this->usuario.' - Página [[page_cu]] de [[page_nb]]
                        </td>
                    </tr>
                </table>
            </div>
        </page_footer>';
        
        return $tabla;
    }



    //// Exportar en Excel
    public function exportar_consolidado_excel_equipamiento() {
        // Limpieza radical de búfer para evitar corrupción binaria del archivo .xls
        if (ob_get_length()) ob_clean(); 

        // 1. Inicialización e inyección de la librería PHPExcel incorporada
        $this->load->library('excel'); 
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("Consolidado Institucional Equipamiento")
                                     ->setCreator("SIIPLAS - CNS");

        // 2. DEFINICIÓN DE ESTILOS EJECUTIVOS GLOBALES CORPORATIVOS
        $styles = array(
            'header' => array(
                'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 10, 'name' => 'Arial'),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '1A237E')), // Azul Marino CNS
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'CCCCCC')))
            ),
            'subheader' => array(
                'font' => array('bold' => true, 'color' => array('rgb' => '1A237E'), 'size' => 9, 'name' => 'Arial'),
                'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'E8EAF6')), // Azul Claro
                'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'CCCCCC')))
            ),
            'data' => array(
                'font' => array('size' => 9, 'name' => 'Arial'),
                'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'E0E0E0')))
            )
        );

        // Carga obligatoria del modelo de datos de equipamiento
        $this->load->model('mdiagnostico_equipamiento/model_diagnosticoequip');

        // ==========================================================================
        // DELEGACIÓN DE PESTAÑAS AUTOMÁTICAS (Pasamos el objeto por referencia &$ )
        // ==========================================================================
        
        // PESTAÑA 1: Formulario General de Diagnóstico
        $this->_generar_equipamiento($objPHPExcel, $styles);

        // PESTAÑA 2: Temporalidad o Datos Adicionales Consolidados
        $this->_generar_adicionales($objPHPExcel, $styles);

        // ==========================================================================
        
        // === CAPTURA E INYECCIÓN DEL TOKEN DE DESCARGA PARA EL REINICIO DEL JS ===
        $file_token = $this->input->get('fileToken');
        if (!empty($file_token)) {
            // Inyectamos la cookie de forma pura. Es mandatorio que se declare ANTES de los headers del archivo
            header("Set-Cookie: fileDownloadToken=" . rawurlencode($file_token) . "; path=/");
        }

        // 3. PROCESAMIENTO DE DESCARGA FINAL DEL EXPEDIENTE BINARIO EXCEL5
        // ==========================================================================
        // 🌟 CORREGIDO: Se removió la condicional de $dist_id para evitar errores fatales de variable indefinida
        $filename = "Consolidado_Nacional_PEI_SIIPLAS";
        
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /// pestaña 1
    private function _generar_equipamiento(&$objPHPExcel, $styles) {
        // 1. Activar la primera pestaña y definir el título
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("EQUIPAMIENTO");

        // Habilitar las líneas de cuadrícula visibles en Excel por defecto
        $sheet->setShowGridlines(true);

        // 🌟 NUEVO ESTILO DE ALTO IMPACTO PARA LAS GESTIONES PLURIANUALES (VERDE ESMERALDA)
        $style_gestion_header = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 10, 'name' => 'Arial'),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '059669')), // Verde Esmeralda llamativo
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'CCCCCC')))
        );

        $style_gestion_data = array(
            'font' => array('bold' => true, 'color' => array('rgb' => '15803D'), 'size' => 9, 'name' => 'Arial'), // Texto verde oscuro resaltado
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => 'F0FDF4')), // Fondo verde menta pastel suave
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'CCCCCC')))
        );

        // 2. DEFINICIÓN REAL DE LAS COLUMNAS CON LAS NUEVAS ADICIONES (Total: Columnas de A hasta U)
        $columnas_headers = array(
            'A1' => 'NRO', 
            'B1' => 'DISTRITAL', 
            'C1' => 'RESPONSABLE', 
            'D1' => 'TIPO REGISTRO',
            'E1' => 'ESTABLECIMIENTO / PROYECTO INVERSIÓN', 
            'F1' => 'NOMBRE DEL EQUIPAMIENTO',
            'G1' => 'SERVICIO / UNIDAD DESTINO', 
            'H1' => 'UBICACIÓN FÍSICA', 
            'I1' => 'ADECUACIÓN INFRAESTRUCTURA', // 🌟 NUEVA COLUMNA I
            'J1' => 'ADECUACIÓN INSTALACIONES',   // 🌟 NUEVA COLUMNA J
            'K1' => 'TIPO COMPRA', 
            'L1' => 'PARTIDA', 
            'M1' => 'CANTIDAD', 
            'N1' => 'COSTO UNITARIO', 
            'O1' => 'COSTO TOTAL',
            'P1' => 'GESTIÓN 2026', // 🌟 GESTIONES DESPLAZADAS Y ESTILIZADAS
            'Q1' => 'GESTIÓN 2027', 
            'R1' => 'GESTIÓN 2028', 
            'S1' => 'GESTIÓN 2029', 
            'T1' => 'GESTIÓN 2030', 
            'U1' => 'OBSERVACIONES'
        );

        // Inyectar los textos de cabecera aplicando sus respectivos colores corporativos
        foreach ($columnas_headers as $celda => $texto) {
            $sheet->setCellValue($celda, $texto);
            
            // Si la celda corresponde a una gestión anual, le inyectamos el nuevo Verde Llamativo
            if (in_array($celda, array('P1', 'Q1', 'R1', 'S1', 'T1'))) {
                $sheet->getStyle($celda)->applyFromArray($style_gestion_header);
            } else {
                $sheet->getStyle($celda)->applyFromArray($styles['header']); // Azul marino para los demás
            }
        }

        // Fijar la altura de la fila de cabeceras para que respire visualmente
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 3. RECUPERACIÓN DE LOS REGISTROS DESDE EL MODELO NACIONAL SIIPLAS
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $listado = $this->model_diagnosticoequip->get_consolidado_formulario_diagnostico_activo($equipamiento[0]['equip_id']);

        // 4. BUCLE DE VOLCADO DE DATOS (Comienza en la Fila 2)
        $fila = 2;
        $nro = 0;

        foreach ($listado as $row) {
            $nro++;

            // Configuración lógica del origen (Establecimiento vs Inversión) sin etiquetas HTML
            $tipo_registro_txt = ($row['tp_registro'] == 1) ? 'ESTABLECIMIENTO REGULAR' : 'PROYECTO DE INVERSIÓN';
            
            if ($row['tp_registro'] == 1) {
                $origen_detalle = strtoupper($row['tipo_establecimiento'] . ' ' . $row['nombre_establecimiento'] . ' [' . $row['abrev_establecimiento'] . ']');
            } else {
                $origen_detalle = strtoupper($row['nombre_establecimiento']);
            }

            // Inyección de celdas de texto descriptivas
            $sheet->setCellValue('A' . $fila, $nro);
            $sheet->setCellValue('B' . $fila, strtoupper($row['dist_distrital']));
            $sheet->setCellValue('C' . $fila, strtoupper($row['responsable']));
            $sheet->setCellValue('D' . $fila, $tipo_registro_txt);
            $sheet->setCellValue('E' . $fila, $origen_detalle);
            $sheet->setCellValue('F' . $fila, strtoupper($row['nombre_equipamiento']));
            $sheet->setCellValue('G' . $fila, strtoupper($row['servicio_unidad']));
            $sheet->setCellValue('H' . $fila, strtoupper($row['ubicacion_fisica']));
            
            // 🌟 INYECCIÓN DE LAS DOS NUEVAS COLUMNAS ADAPTADAS A TU DDL DE POSTGRESQL
            $sheet->setCellValue('I' . $fila, strtoupper($row['tp_adecuacion_infra']));
            $sheet->setCellValue('J' . $fila, strtoupper($row['tp_adecuacion_instalacion']));
            
            // Soporte preventivo por si el índice viene nulo o vacío
            $tp_compra_txt = isset($row['tp_compra_nombre']) ? strtoupper($row['tp_compra_nombre']) : 'COMPRA';
            $sheet->setCellValue('K' . $fila, $tp_compra_txt);
            
            $par_codigo_txt = isset($row['par_codigo']) ? $row['par_codigo'] : '00000';
            $sheet->setCellValue('L' . $fila, $par_codigo_txt);

            // Inyección de Celdas Cuantitativas y Financieras con su respectivo tipo de dato
            $sheet->setCellValue('M' . $fila, intval($row['cantidad']));
            $sheet->setCellValue('N' . $fila, floatval($row['costo_unitario']));
            $sheet->setCellValue('O' . $fila, floatval($row['costo_total']));

            // Distribución Temporal Quinquenal Plurianual Relacional
            $sheet->setCellValue('P' . $fila, floatval($row['g_2026']));
            $sheet->setCellValue('Q' . $fila, floatval($row['g_2027']));
            $sheet->setCellValue('R' . $fila, floatval($row['g_2028']));
            $sheet->setCellValue('S' . $fila, floatval($row['g_2029']));
            $sheet->setCellValue('T' . $fila, floatval($row['g_2030']));
            
            // Saneamiento de observaciones abiertas
            $sheet->setCellValue('U' . $fila, trim($row['observaciones']));

            // 5. APLICACIÓN DE FORMATOS CONTABLES Y ESTILOS POR FILA
            // Formato para la columna de cantidad (Entero limpio)
            $sheet->getStyle('M' . $fila)->getNumberFormat()->setFormatCode('#,##0');

            // Formato contable de dos decimales para Costos y Distribuciones Quinquenales
            $columnas_moneda = array('N', 'O', 'P', 'Q', 'R', 'S', 'T');
            foreach ($columnas_moneda as $col) {
                $sheet->getStyle($col . $fila)->getNumberFormat()->setFormatCode('#,##0.00');
            }

            // Aplicar la cuadrícula fina general (estilo data) sobre toda la fila actual (De A hasta U)
            $sheet->getStyle('A' . $fila . ':U' . $fila)->applyFromArray($styles['data']);

            // 🌟 INYECTAR EL NUEVO ESTILO HIGHLIGHT LLAMATIVO EXCLUSIVO PARA LAS CASILLAS DEL QUINQUENIO
            $sheet->getStyle('P' . $fila . ':T' . $fila)->applyFromArray($style_gestion_data);

            // Alineaciones específicas por tipo de dato para orden institucional
            $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $fila . ':M' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            // Fijar la altura de cada fila de datos de manera uniforme
            $sheet->getRowDimension($fila)->setRowHeight(20);

            $fila++;
        }

        // 6. MOTOR DE AUTO-ANCHO DINÁMICO PARA LAS COLUMNAS EXTENDIDAS (A a U)
        foreach (range('A', 'U') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    private function _generar_adicionales(&$objPHPExcel, $styles) {
        // 1. Crear y activar formalmente la segunda pestaña del libro de trabajo
        $objPHPExcel->createSheet();
        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("ADICIONALES");

        // Habilitar las líneas de cuadrícula visibles por defecto en Excel
        $sheet->setShowGridlines(true);

        // Estilo complementario de alto impacto para la cabecera de componentes adicionales (Púrpura Corporativo)
        $style_adicionales_header = array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 10, 'name' => 'Arial'),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '059669')), // Verde Esmeralda llamativo
            'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER),
            'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => 'CCCCCC')))
        );

        // Estilo destacado de texto para las etiquetas de tipo de componente
        $style_badge_accesio = array('font' => array('bold' => true, 'color' => array('rgb' => '2563EB'))); // Azul Cobalto
        $style_badge_software = array('font' => array('bold' => true, 'color' => array('rgb' => '7C3AED'))); // Púrpura Oscuro

        // 2. DEFINICIÓN REAL DE LAS COLUMNAS DE LA GRILLA SECUNDARIA (FILA 1)
        $columnas_headers = array(
            'A1' => 'NRO',
            'B1' => 'DISTRITAL',
            'C1' => 'ESTABLECIMIENTO MÉDICO',
            'D1' => 'RESPONSABLE DEL SERVICIO',
            'E1' => 'EQUIPO PRINCIPAL ASOCIADO',
            'F1' => 'TIPO COMPONENTE',
            'G1' => 'DETALLE ESPECÍFICO DEL ACCESORIO / SOFTWARE ADICIONAL'
        );

        // Inyectar los textos de cabecera aplicando el nuevo color Púrpura de Categoría
        foreach ($columnas_headers as $celda => $texto) {
            $sheet->setCellValue($celda, $texto);
            $sheet->getStyle($celda)->applyFromArray($style_adicionales_header);
        }

        // Fijar la altura de la fila de cabeceras para que respire visualmente
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 3. RECUPERACIÓN DE LOS REGISTROS RELACIONALES DESDE TU MODELO
        // Usamos el ID de equipamiento activo que tienes en memoria global ($this->equip_id) o recuperándolo de tu objeto activo
        // Si no tienes el $equip_id global, puedes extraerlo del arreglo $equipamiento que cargaste en el paso anterior: $equipamiento['equip_id']
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();

        // Invocar directamente a tu consulta SQL personalizada
        $listado_adicionales = $this->model_diagnosticoequip->get_list_adcionales_consolidado($equipamiento[0]['equip_id']);

        // 4. BUCLE DE VOLCADO DE DATOS (Comienza en la Fila 2)
        $fila = 2;
        $nro = 0;

        if (!empty($listado_adicionales)) {
            foreach ($listado_adicionales as $row) {
                $nro++;

                // Formatear la cadena de texto del Establecimiento (Tipo + Descripción + Abrev)
                $establecimiento_txt = strtoupper($row['tipo'] . ' ' . $row['act_descripcion'] . ' [' . $row['abrev'] . ']');

                // Inyección de celdas descriptivas limpias (Sin etiquetas HTML residuales)
                $sheet->setCellValue('A' . $fila, $nro);
                $sheet->setCellValue('B' . $fila, strtoupper($row['dist_distrital']));
                $sheet->setCellValue('C' . $fila, $establecimiento_txt);
                $sheet->setCellValue('D' . $fila, strtoupper($row['responsable']));
                $sheet->setCellValue('E' . $fila, strtoupper($row['nombre_equipamiento']));
                $sheet->setCellValue('F' . $fila, strtoupper($row['tipo_detalle_nombre'])); // 'ACCESORIO' o 'SOFTWARE'
                $sheet->setCellValue('G' . $fila, strtoupper($row['detalle_equi_adi']));    // Descripción del accesorio

                // 5. APLICACIÓN DE FORMATOS Y ENFOQUES DE TEXTO POR FILA
                // Aplicar la cuadrícula fina general (estilo data) sobre toda la fila actual (De A hasta G)
                $sheet->getStyle('A' . $fila . ':G' . $fila)->applyFromArray($styles['data']);

                // Destacar con formato en negrita y color el Tipo de Componente para facilitar la auditoría
                if ($row['tp_equi_adi'] == 1) {
                    $sheet->getStyle('F' . $fila)->applyFromArray($style_badge_accesio);
                } else if ($row['tp_equi_adi'] == 2) {
                    $sheet->getStyle('F' . $fila)->applyFromArray($style_badge_software);
                }

                // Alineaciones específicas por tipo de dato para orden institucional
                $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F' . $fila)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                // Fijar la altura de cada fila de datos de manera uniforme
                $sheet->getRowDimension($fila)->setRowHeight(22);

                $fila++;
            }
        } else {
            // Hilera informativa estética por si el consolidado nacional no tiene accesorios registrados
            $sheet->setCellValue('A2', "No se tienen registrados componentes adicionales o complementarios en el SIIPLAS para este PEI.");
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2:G2')->applyFromArray($styles['data']);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('7F7F7F');
            $sheet->getRowDimension(2)->setRowHeight(25);
        }

        // 6. MOTOR DE AUTO-ANCHO DINÁMICO PARA LAS COLUMNAS (A a G)
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }
}