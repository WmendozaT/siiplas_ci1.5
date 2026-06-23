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
      $this->load->library('lib_diagnostico_equipamiento');
     // $this->load->library('lib_diagnosticopei_reporte');
        // Si CI no creó la propiedad, la asignamos nosotros a mano
        // if (!isset($this->lib_diagnosticopei_reporte)) {
        //     $CI =& get_instance();
        //     $this->lib_diagnosticopei_reporte = $CI->lib_diagnosticopei_reporte;
        // }
        
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
                            Información Quinquenal PEI '.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin'].'
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
              <div class="well">
                <h2>FORMULARIO DE INFORMACIÓN QUINCENAL PEI '.$equipamiento[0]['g_id_inicio'].' - '.$equipamiento[0]['g_id_fin'].'</h2><br>
                <h2>'.strtoupper($distrital[0]['dist_distrital']).'</h2>
                <button type="button" class="btn btn-success btn-sm font-md" 
                        onclick="window.abrirModalNuevaEquipamiento();" 
                        style="font-weight: bold; background: #e67e22; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.15); letter-spacing:0.3px; padding: 6px 15px;">
                    <i class="fa fa-plus-circle"></i> + REQUERIMIENTO DE EQUIPAMIENTO
                </button>
                <a href="'.site_url("").'/me/exportar_alineacion_ope_acp/" title="EXPORTAR EN EXCEL" class="btn btn-default">
                  <img src="'.base_url().'assets/Iconos/printer_empty.png" WIDTH="20" HEIGHT="20"/>&nbsp;EXPORTAR ALINEACION EN EXCEL
                </a>
                
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



    /// Reporte Formulario Diagnostico Pei Equipamiento
    public function reporte_formulario_equipamiento($dist_id){
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $data['reporte']= 'Reporte ...';
        $data['pie_rep']='dnp';
        $this->load->view('admin/diagnostico_equipamiento/View_report_form_diagequipamiento', $data);
    }

    //// Detalle Reporte
    public function rep_diagnostico_equipamiento($dist_id) {
        //$listado_ambulancias=$this->CI->model_diagnosticopei->get_detalle_ambulancias($get_form_distrital[0]['dist_id']);

        $tabla='';
       // $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="portrait" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report($dist_id).'

        <p class="bold">1. Objetivo</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Inventario General del Parque Automotor de Ambulancias por Establecimiento de Salud de la Regional/Distrital
        </div>';
            
            $tabla.='
            <table class="tabla-datos" style="font-size: 8px; width: 100%; border-collapse: collapse; table-layout: fixed;" border="1">
                <thead>
                    <tr style="background: #e8eaf6; color: #1a237e; font-weight: bold; height: 25px;">
                        <th style="width:3%; text-align:center; vertical-align: middle; font-size: 8.5px; padding: 5px 0;">#</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">PLACA</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">AÑO ADJUDICACIÓN</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">ESTADO</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">SITUACIÓN</th>
                        <th style="width:25%; text-align:center; vertical-align: middle; font-size: 8.5px;">ESTABLECIMIENTO</th>
                    </tr>
                </thead>
                <tbody>';
                
                // Contador correlativo plano independiente
               /* $nro = 0; 
                
                foreach ($listado_ambulancias as $row) {
                    $nro++;
                    
                    // Formateamos las cadenas para asegurar consistencia contable en mayúsculas
                    $placa_rep       = !empty($row['placa']) ? strtoupper(trim($row['placa'])) : '---';
                    $gestion_rep     = ($row['anio_adjudicacion'] > 0) ? intval($row['anio_adjudicacion']) : '---';
                    $estado_rep      = !empty($row['estado_ambulancia']) ? strtoupper(trim($row['estado_ambulancia'])) : 'SIN REGISTRO';
                    $situacion_rep   = !empty($row['situacion_ambulancia']) ? strtoupper(trim($row['situacion_ambulancia'])) : 'SIN REGISTRO';
                    $establecimiento = !empty($row['establecimiento']) ? strtoupper(trim($row['establecimiento'])) : 'SIN ASIGNACIÓN';

                    $tabla .= '
                    <tr style="height: 22px;">
                        <!-- Número correlativo automático de la grilla -->
                        <td style="text-align:center; vertical-align: middle; font-weight: bold; background:#f9f9f9;">' . $nro . '</td>
                        
                        <!-- Datos técnicos del parque automotor sanitario -->
                        <td style="text-align:center; vertical-align: middle; font-weight: bold; color: #0d47a1;">' . $placa_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-size:8px;">' . $gestion_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-weight: 500;font-size:8px;">' . $estado_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-weight: 500;font-size:8px;">' . $situacion_rep . '</td>
                        
                        <!-- Alineación del Centro de Salud a la derecha con padding de resguardo -->
                        <td style="text-align:left; vertical-align: middle; font-weight: bold; color: #1a237e; padding-left: 5px;font-size:8px;">' . $establecimiento . '</td>
                    </tr>';
                }

                // CONTROL DE REJILLA VACÍA: Si no hay registros inyectados, dibuja una fila informativa para mantener la estética
                if ($nro === 0) {
                    $tabla .= '
                    <tr style="height: 30px;">
                        <td style="text-align:center; vertical-align: middle; color:#777; font-weight:bold;">-</td>
                        <td colspan="5" style="text-align:center; vertical-align: middle; color:#999; font-style:italic; font-size:9px;">
                            <i class="fa fa-info-circle"></i> No se encontraron unidades de transporte sanitario registradas en el inventario oficial de esta regional.
                        </td>
                    </tr>';
                }*/

            $tabla .= '
                </tbody>
            </table>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>firma</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }



        /// cabecera reporte
    public function cabecera_report($dist_id) {
        $tabla='';
        $tabla.='
        <page_header>
            <!-- Quitamos paddings en px que rompen el DOM del PDF. Usamos margin-lateral para alinear con la página -->
            <div style="padding-top: 25px; margin-left: 15mm; margin-right: 15mm; display: block;">
                
                <!-- Forzamos table-layout fixed para que respete estrictamente los porcentajes en Portrait y Landscape -->
                <table style="width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; table-layout: fixed;">
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: middle; font-size:9px;">
                            <b>FORMULARIO PEI N° </b>
                        </td>
                        
                        <td style="width: 60%; text-align: center; vertical-align: middle;">
                            <span style="font-size: 13px; font-weight: bold; color: #004640; letter-spacing: 0.5px;">
                                entidad
                            </span>
                            <br>
                            <span style="font-size: 17px; font-weight: bold; color: #212121; line-height: 1.2;">
                                titulo
                            </span>
                            <br>
                            <span style="font-size: 11px; font-weight: bold; color: #212121; line-height: 1.2;">
                                dist id
                            </span>
                        </td>
                        
                        <td style="width: 20%; text-align: right; vertical-align: middle; font-size: 8px; color: #424242; line-height: 1.3;">
                            PERIODO: <b style="color: #212121;"></b>
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
                            
                        </td>
                        
                        <!-- Zona Derecha (50% proporcional) -->
                        <td style="width: 50%; text-align: right; vertical-align: middle; font-size: 8.5px; color: #424242; font-weight: bold;">
                         - Página [[page_cu]] de [[page_nb]]
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
        $listado = $this->model_diagnosticoequip->get_consolidado_formulario_diagnostico_activo($equipamiento);

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

    private function _generar_adicionales(&$objPHPExcel,$styles) {
        // Crear la segunda pestaña de forma explícita antes de seleccionarla
        $objPHPExcel->createSheet(); 
        $objPHPExcel->setActiveSheetIndex(1);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle("ADICIONALES CONSOLIDADOS");
        // Aquí pones el bucle de la segunda consulta SQL de temporalidad
    }
}