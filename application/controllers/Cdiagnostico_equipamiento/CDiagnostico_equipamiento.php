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
      $this->load->library('lib_diagnosticopei_reporte');
        // Si CI no creó la propiedad, la asignamos nosotros a mano
        if (!isset($this->lib_diagnosticopei_reporte)) {
            $CI =& get_instance();
            $this->lib_diagnosticopei_reporte = $CI->lib_diagnosticopei_reporte;
        }
        
      }else{
          redirect('/','refresh');
      }
    }

    /// formulario principal
    public function diagnostico_principal() {
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $dist_id = $this->dist_id; // Asegúrate de que esta variable esté definida
        $data['titulo']='';
        // 1. Verificación temprana (Early Return) para evitar anidación
        if (empty($equipamiento)) {
            $data['cuerpo'] = $this->_mensaje_error("Solicitar que se habilite el formulario de diagnóstico PEI.");
            return $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data);
        }

        $equip_id = $equipamiento[0]['equip_id'];

        if($this->tp_adm == 1){
          // Administrador Nacional
            $data['titulo'] = $this->Seleccion_unidadEjecutora();
            $data['cuerpo'] = '<div id="contenedor_formulario"></div>';
        }elseif ($this->conf_pei == 1) {
            // Usuario con permiso de llenado
            $data['cuerpo'] = $this->unidad_ejecutora_seleccionado($equip_id, $dist_id,0); /// regional
        } else { 
            // Acceso restringido por configuración
            $data['cuerpo'] = $this->_mensaje_error("Usted no cuenta con los privilegios necesarios para el llenado.");
        }


        $this->load->view('admin/diagnostico_equipamiento/View_diagnostico_equipamiento', $data);
      //echo $this->unidad_ejecutora_seleccionado(1,1,1);
    }



    /*------- Selecciona Unidad Ejecutora -------*/
    public function Seleccion_unidadEjecutora(){
      $get_diagnostico=$this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
      $UnidadEjecutora=$this->model_diagnosticoequip->lista_UnidadEjecutora(); 
      $tabla=''; 
      if(count($get_diagnostico)!=0){
        $tabla.='
          <article class="col-sm-12">
            <input name="base" type="hidden" value="'.base_url().'">
            <div class="well">
              <form class="smart-form">
                  <header>DIAGNOSTICO EQUIPAMIENTO ('.$get_diagnostico[0]['g_id_inicio'].' - '.$get_diagnostico[0]['g_id_fin'].')</header>
                  <fieldset>          
                    <div class="row">
                      <section class="col col-3">
                          <label class="label">Seleccione Unidad Ejecutora</label>
                            <select class="form-control" id="dist_id" name="dist_id" title="SELECCIONE">
                            <option value="0">Seleccione ..</option>';
                            foreach($UnidadEjecutora as $row){
                              $tabla.='<option value="'.$row['dist_id'].'">'.$row['dist_id'].'.- '.strtoupper($row['dist_distrital']).'</option>';
                            }
                            $tabla.='
                            </select>
                      </section>

                      <!-- BOTÓN PARA DESCARGAR CONSOLIDADO -->
                        <section class="col col-3">
                            <label class="label">&nbsp;</label>
                            <button type="button" id="btn_descargar_consolidado" class="btn btn-success btn-sm" style="padding: 10px; width: 100%; text-align: center; color: white; font-weight: bold; border: none; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                              <i class="fa fa-file-excel-o"></i> DESCARGAR CONSOLIDADO
                            </button>
                        </section>
                    </div>
                  </fieldset>
              </form>
              </div>
          </article>';
      }
      else{
        $tabla.='
        <div class="alert alert-block alert-danger">
                <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Error !!!</h4>
                <p>Porfavor asigne datos del PEI .</p>
            </div>';
      }
      
      $tabla .= '
            <script type="text/javascript">
                document.addEventListener("DOMContentLoaded", function() {
                    
                    $(document).on("click", "#btn_descargar_consolidado", function(e) {
                        e.preventDefault();
                        
                        var dist_id = $("#consolidado_dist_id").val();
                        var pei_id = "' . $get_diagnostico[0]['equip_id'] . '";
                        
                        // 1. Generamos un Token único basado en la estampa de tiempo
                        var downloadToken = "token_" + new Date().getTime();
                        
                        // 2. Bloqueamos la interfaz levantando el Loading de SmartAdmin
                        $("#btn_descargar_consolidado").prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i> Procesando...");
                        $("#loading_descarga_excel").fadeIn(200);
                        
                        // 3. Redireccionamos la ventana enviando el token como parámetro GET adicional
                        window.location.href = "' . site_url("Diagnostico_pei/exportar_consolidado_excel") . '/" + pei_id + "/" + dist_id + "?fileToken=" + downloadToken;
                        
                        // 4. Temporizador cíclico de auditoría de cookies
                        var checkDownloadTimer = setInterval(function() {
                            // Buscamos si la cookie con el token ya fue depositada por el servidor
                            var cookieValue = getCookie("fileDownloadToken");
                            
                            if (cookieValue === downloadToken) {
                                // Descarga finalizada: Limpiamos el temporizador y destruimos la cookie por seguridad
                                clearInterval(checkDownloadTimer);
                                document.cookie = "fileDownloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                                
                                // Restablecemos el estado operativo de la interfaz visual
                                $("#loading_descarga_excel").fadeOut(300);
                                $("#btn_descargar_consolidado").prop("disabled", false).html("<i class=\'fa fa-file-excel-o\'></i> DESCARGAR CONSOLIDADO");
                            }
                        }, 300); // Evalúa el DOM cada 300 milisegundos de forma transparente
                    });

                    // Función auxiliar clásica nativa para leer cookies del navegador
                    function getCookie(name) {
                        var parts = document.cookie.split("; " + name + "=");
                        if (parts.length === 2) return parts.pop().split(";").shift();
                        return "";
                    }
                });
            </script>';
            $tabla.=$this->lib_diagnostico_equipamiento->js_validacion();
      return $tabla;
    }








    // Función auxiliar para no repetir código HTML de alertas
    private function _mensaje_error($mensaje) {
        return '
        <div class="alert alert-block alert-danger">
            <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Atención!</h4>
            <p>' . $mensaje . '</p>
        </div>';
    }


    /*--- GET LISTA DE UNIDAD EJECUTORA ----*/
    public function get_unidad_ejecutora(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            // Cambiado de 'dist_id' a 'id' para que coincida con el JS
            $dist_id = $this->security->xss_clean($post['id']); 
            $get_diagnostico=$this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();

           
           $tabla = $this->lib_diagnostico_equipamiento->unidad_ejecutora_seleccionado($get_diagnostico[0]['equip_id'],$dist_id,1); //// get listado de la distrital

            $result = array(
                'respuesta' => 'correcto',
                'tabla' => $tabla,
            );
            
            // Indicamos al navegador que es un JSON
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($result));
        } else {
            show_404();
        }
    }



    //// guardar registro de equipamiento
     public function guardar_requerimiento_equipamiento() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // Sanitización estricta contra ataques de inyección XSS
            $post = $this->security->xss_clean($this->input->post());

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
            $ade_infraestructura = isset($post['ade_infraestructura']) ? trim(strtoupper($post['ade_infraestructura'])) : '';
            $ade_instalaciones   = isset($post['ade_instalaciones']) ? trim(strtoupper($post['ade_instalaciones'])) : '';
            $observaciones       = isset($post['observaciones']) ? trim(strtoupper($post['observaciones'])) : '';

            // Variables numéricas de control financiero
            $cantidad       = isset($post['cantidad']) ? intval($post['cantidad']) : 0;
            $costo_unitario = isset($post['costo_unitario']) ? floatval($post['costo_unitario']) : 0.00;
            $costo_total    = $cantidad * $costo_unitario; // Cálculo matemático forzado y blindado en el backend

            // --------------------------------------------------------------------------
            // CAPA 2: SEGUNDO BLOQUEO DE INTEGRIDAD MATEMÁTICA EN EL SERVIDOR
            // --------------------------------------------------------------------------
            $suma_quinquenio = 0;
            for ($anio = 2026; $anio <= 2030; $anio++) {
                $suma_quinquenio += isset($post['gest' . $anio]) ? floatval($post['gest' . $anio]) : 0;
            }

            // Si hay un descuadre contable, el servidor detiene el query y devuelve el aviso sin romper el JSON
            if (abs($suma_quinquenio - $costo_total) > 0.01) {
                $result = array(
                    'respuesta' => 'error',
                    'mensaje' => 'La sumatoria distribuida en el quinquenio (' . number_format($suma_quinquenio, 2, ',', '.') . ' Bs.) no coincide con el Costo Total (' . number_format($costo_total, 2, ',', '.') . ' Bs.). Verifique la grilla.'
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
                    'tp_adecuacion_infra' => $ade_infraestructura,
                    'tp_adecuacion_instalacion'   => $ade_instalaciones,
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
                for ($anio = 2026; $anio <= 2030; $anio++) {
                    $monto_anio = isset($post['gest' . $anio]) ? floatval($post['gest' . $anio]) : 0;

                    if ($monto_anio >= 0) {
                        $data_temp = array(
                            'form_equip_id' => $form_equip_id,
                            'g_id'          => $anio,
                            'prog_equi'     => $monto_anio
                        );
                        $this->db->insert('temporalidad_diagnostico_equipamiento', $data_temp);
                    }
                }

                // 5. EVALUACIÓN Y CIERRE DE TRANSACCIÓN
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = array('respuesta' => 'error', 'mensaje' => 'Error de consistencia al impacter las tablas relacionales de la CNS.');
                } else {
                    $this->db->trans_commit();
                    $result = array('respuesta' => 'correcto', 'form_equip_id' => $form_equip_id);
                }

            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result = array('respuesta' => 'error', 'mensaje' => 'Excepción crítica en la base de datos: ' . $e->getMessage());
            }

            // Despachamos la respuesta JSON limpia por tu método nativo de salida
            $this->_retornar_json($result);

        } else {
            show_404();
        }
    }

    /*--- FUNCIÓN INTERNA DE SALIDA JSON ---*/
    private function _retornar_json($resultado) {
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($resultado));
    }
    /// funcion para exportar
    public function exportar_consolidado_excel_equipamiento($tp_rep, $dist_id) {
        $tabla='No disponible ...';
    }




    /*======= CONTROLADOR: GENERADOR ASÍNCRONO DEL FORMULARIO EN MODAL =======*/
     public function get_formulario_modal_html() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX y por POST
        if ($this->input->is_ajax_request() && $this->input->post()) {
            $post = $this->input->post();
            
            // Sanitización estricta de variables contra inyecciones XSS
            $form_equip_id = $this->security->xss_clean($post['form_equip_id']);
            $dist_id       = $this->security->xss_clean($post['dist_id']);
            
            // Extracción del Diagnóstico Maestro activo para capturar el equip_id global plurianual
            $get_diagnostico = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
            $equip_id    = !empty($get_diagnostico) ? $get_diagnostico[0]['equip_id'] : 0;
            $g_id_inicio = !empty($get_diagnostico) ? $get_diagnostico[0]['g_id_inicio'] : 2026;
            $g_id_fin    = !empty($get_diagnostico) ? $get_diagnostico[0]['g_id_fin'] : 2030;

            // 2. Estructura de valores vacíos iniciales por defecto para un "Nuevo Registro" (ID = 0)
            $row = array(
                'form_equip_id'       => 0,
                'tp_registro'         => 1, // 1: Establecimiento por defecto
                'act_id'              => '',
                'nombre_inversion'    => '',
                'responsable'         => '',
                'nombre_equipamiento' => '',
                'servicio_unidad'     => '',
                'ubicacion_fisica'    => '',
                'tp_compra'           => 1, // 1: Nuevo por defecto
                'cantidad'            => 0,
                'costo_unitario'      => 0.00,
                'costo_total'         => 0.00,
                'par_id'              => '',
                'tp_adecuacion'       => 0,
                'tp_firma'            => 0,
                'ade_infraestructura' => '',
                'ade_instalaciones'   => '',
                'observaciones'       => ''
            );

            // Inicialización de la matriz de temporalidad para las 5 gestiones (2026-2030)
            $prog = array('g2026' => 0, 'g2027' => 0, 'g2028' => 0, 'g2029' => 0, 'g2030' => 0);

            // 3. MODO MODIFICACIÓN: Si el ID > 0, extraemos la información real de la base de datos
            if ($form_equip_id > 0) {
                // 🌟 REPARADO: Al usar row_array() ya no buscamos el índice, leemos directo
                $registro = $this->model_diagnosticoequip->get_formulario_equipamiento_by_id($form_equip_id);
                if (!empty($registro)) {
                    $row = $registro; // Mapeamos el registro físico unidimensional
                }
                
                // Recuperamos la distribución de las 5 gestiones desde tu modelo de temporalidad
                $temporalidad = $this->model_diagnosticoequip->get_temporalidad_equipamiento_by_id($form_equip_id);
                if (!empty($temporalidad)) {
                    foreach ($temporalidad as $t) {
                        $prog['g' . $t['g_id']] = $t['prog_equi'];
                    }
                }
            }

            // 4. Extracción de Catálogos Relacionales filtrando por la Distrital y la Gestión activa de la CNS
            $establecimientos = $this->model_diagnosticoequip->get_establecimientos_distrital($dist_id, $this->gestion);

            // Vector Estático de Partidas Presupuestarias vinculadas a sus IDs llave primarias reales (149, 173, etc.)
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

            // 5. MAQUETACIÓN DEL ESQUELETO SMART-FORM UTILIZANDO CLASES CONTEXTUALES EXCLUSIVAS
            $html = '
            <form class="smart-form form_transaccional_equipamiento" method="post" autocomplete="off" style="padding:0; background:transparent;">
                <input type="hidden" name="form_equip_id" value="' . $row['form_equip_id'] . '">
                <input type="hidden" name="equip_id" value="' . $equip_id . '">
                <input type="hidden" name="dist_id" class="modal_dist_id" value="' . $dist_id . '">
                
                <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                    <b>I. IDENTIFICACIÓN INSTITUCIONAL DE ORIGEN</b>
                </header>
                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                    <div class="row">
                        <section class="col col-4">
                            <label class="label"><b>TIPO REGISTRO *</b></label>
                            <label class="select">
                                <select class="form_tp_registro" name="tp_registro" style="font-weight: bold; color: #0d47a1;" required>
                                    <option value="1" ' . ($row['tp_registro'] == 1 ? 'selected' : '') . '>1.- ESTABLECIMIENTO DE SALUD</option>
                                    <option value="2" ' . ($row['tp_registro'] == 2 ? 'selected' : '') . '>2.- PROYECTO DE INVERSIÓN</option>
                                </select><i></i>
                            </label>
                        </section>
                    </div>
                    
                    <div class="row">
                        <!-- SECCIÓN DINÁMICA A: SELECTOR DE CENTROS DE SALUD -->
                        <div class="div_est" style="' . ($row['tp_registro'] == 1 ? '' : 'display:none;') . '">
                            <section class="col col-6">
                                <label class="label"><b>ESTABLECIMIENTO DE SALUD VINCULADO *</b></label>
                                <label class="select">
                                    <select class="form_act_id" name="act_id">
                                        <option value="">Seleccione Centro de Salud...</option>';
                                        foreach($establecimientos as $est){
                                            $selected_est = ($row['act_id'] == $est['act_id']) ? 'selected' : '';
                                            $html .= '<option value="'.$est['act_id'].'" '.$selected_est.'>'.strtoupper($est['tipo'].' '.$est['act_descripcion']).'</option>';
                                        }
                                        $html .= '
                                    </select><i></i>
                                </label>
                            </section>
                        </div>
                        
                        <!-- SECCIÓN DINÁMICA B: TEXTO LIBRE PROYECTO INVERSIÓN -->
                        <div class="div_inv" style="' . ($row['tp_registro'] == 2 ? '' : 'display:none;') . '">
                            <section class="col col-6">
                                <label class="label"><b>NOMBRE DEL PROYECTO DE INVERSIÓN *</b></label>
                                <label class="textarea"><i class="icon-append fa fa-folder-open"></i>
                                    <textarea rows="2" class="form_nombre_inversion" name="nombre_inversion" placeholder="Escriba el nombre oficial del proyecto de inversión...">' . htmlspecialchars($row['nombre_inversion'], ENT_QUOTES, 'UTF-8') . '</textarea>
                                </label>
                            </section>
                        </div>
                        
                        <section class="col col-6">
                            <label class="label"><b>NOMBRE DEL RESPONSABLE / SOLICITANTE *</b></label>
                            <label class="textarea"><i class="icon-append fa fa-user"></i>
                                <textarea rows="2" class="form_responsable" name="responsable" required placeholder="Ej. Dr. Carlos Murillo - Jefe del Servicio de Quirófano">' . htmlspecialchars($row['responsable'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>

                <header style="border-bottom: 2px solid #1a237e; color: #1a237e; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                    <b>II. ESPECIFICACIONES TÉCNICAS DEL EQUIPO</b>
                </header>
                <fieldset style="background:transparent; padding:0; margin-bottom:10px;">
                    <div class="row">
                        <section class="col col-4">
                            <label class="label"><b>NOMBRE DEL EQUIPAMIENTO MÉDICO *</b></label>
                            <label class="textarea"><i class="icon-append fa fa-tag"></i>
                                <textarea rows="2" class="form_nombre_equipamiento" name="nombre_equipamiento" required placeholder="Ej. MONITOR MULTIPARAMÉTRICO DE 5 PARÁMETROS">' . htmlspecialchars($row['nombre_equipamiento'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                        <section class="col col-3">
                            <label class="label"><b>SERVICIO / UNIDAD *</b></label>
                            <label class="textarea"><i class="icon-append fa fa-hospital-o"></i>
                               <textarea rows="2" class="form_servicio_unidad" name="servicio_unidad" required placeholder="Ej. UNIDAD DE TERAPIA INTENSIVA CORONARIA">' . htmlspecialchars($row['servicio_unidad'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                        <section class="col col-3">
                            <label class="label"><b>UBICACIÓN FÍSICA EXACTA *</b></label>
                            <label class="textarea"><i class="icon-append fa fa-map-marker"></i>
                                <textarea rows="2" class="form_ubicacion_fisica" name="ubicacion_fisica" required placeholder="Ej. Bloque Central - Tercer Piso - Sala de Recuperación">' . htmlspecialchars($row['ubicacion_fisica'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                        <section class="col col-2">
                            <label class="label"><b>TIPO DE COMPRA *</b></label>
                            <label class="select">
                                <select class="form_tp_compra" name="tp_compra" required>
                                    <option value="1" ' . ($row['tp_compra'] == 1 ? 'selected' : '') . '>NUEVO</option>        
                                    <option value="2" ' . ($row['tp_compra'] == 2 ? 'selected' : '') . '>REPOSICIÓN</option>        
                                </select><i></i>
                            </label>
                        </section>
                    </div>
                </fieldset>

                <header style="border-bottom: 2px solid #2e7d32; color: #1b5e20; font-weight: bold; font-size: 11.5px; padding-bottom:4px; margin-bottom:12px;">
                    <b>III. CRONOGRAMA DE DISTRIBUCIÓN QUINQUENAL Y MATRIZ FINANCIERA (Bs.)</b>
                </header>
                <fieldset style="background:transparent; padding:0;">
                    <div class="row">
                        <section class="col col-3">
                            <label class="label"><b>PARTIDA PRESUPUESTARIA *</b></label>
                            <label class="select">
                                <select class="form_par_id" name="par_id" required title="SELECCIONE GRUPO DE PARTIDA">
                                    <option value="">Seleccione Partida...</option>';
                                    foreach ($partidas_gastos as $id_partida => $descripcion_partida) {
                                        $selected_par = ($row['par_id'] == $id_partida) ? 'selected' : '';
                                        $html .= '<option value="' . $id_partida . '" ' . $selected_par . '>' . $descripcion_partida . '</option>';
                                    }
                                    $html .= '
                                </select><i></i>
                            </label>
                        </section>

                        <section class="col col-3">
                            <label class="label"><b>CANTIDAD TOTAL *</b></label>
                            <label class="input"><i class="icon-append fa fa-calculator"></i>
                                <input type="text" class="form_cantidad" name="cantidad" value="' . $row['cantidad'] . '" required title="REGISTRAR CANTIDAD TOTAL">
                            </label>
                        </section>

                        <section class="col col-3">
                            <label class="label"><b>COSTO UNITARIO (Bs.) *</b></label>
                            <label class="input"><i class="icon-append fa fa-money"></i>
                                <input type="text" class="form_costo_unitario" name="costo_unitario" value="' . $row['costo_unitario'] . '" required title="REGISTRAR COSTO UNITARIO">
                            </label>
                        </section>

                        <section class="col col-3">
                            <label class="label"><b>COSTO TOTAL CONSOLIDADO</b></label>
                            <label class="input" style="background: #f4f4f4;">
                                <i class="icon-append fa fa-lock"></i>
                                <input type="text" class="form_costo_total" name="costo_total" value="' . number_format($row['costo_total'], 2, '.', '') . '" readonly style="font-weight: bold; color: #0d47a1; background: #f4f4f4;" title="COSTO TOTAL AUTOMÁTICO">
                            </label>
                        </section>
                    </div>

                    <div class="row row_temporalidad_quinquenal" style="background: #f1f8e9; padding: 12px 0 2px 0; border: 1px dashed #2e7d32; border-radius: 4px; margin: 5px 0 10px 0;">
                        <section class="col col-2" style="margin-left: 2%;">
                            <label class="label" style="color:#1b5e20;"><b>TOTAL DISTRIBUIDO</b></label>
                            <label class="input">
                                <i class="icon-append fa fa-check-circle"></i>
                                <input type="text" class="form_total_prog" id="total_prog" value="0.00" readonly style="font-weight: bold; color: #2e7d32; background: #eaebd8;" title="SUMATORIA DE PLANIFICACIÓN ANUAL">
                            </label>
                        </section>';
                        
                        // Renderizado dinámico paramétrico de los 5 años (2026 a 2030)
                        for ($i = $g_id_inicio; $i <= $g_id_fin; $i++) { 
                            $html .= '
                            <section class="col col-2">
                                <label class="label" style="color:#1b5e20;"><b>GESTIÓN ' . $i . '</b></label>
                                <label class="input">
                                    <i class="icon-append fa fa-calendar"></i>
                                    <input type="text" class="prog-anio" name="gest' . $i . '" value="' . $prog['g' . $i] . '">
                                </label>
                            </section>';
                        }

                        $html .= '
                    </div>
                    
                    <div class="row">
                        <section class="col col-4">
                            <label class="label"><b>ADECUACIÓN DE INFRAESTRUCTURA</b></label>
                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                <textarea rows="2" name="ade_infraestructura" placeholder="Describa adecuaciones físicas de albañilería...">' . htmlspecialchars($row['tp_adecuacion_infra'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                        <section class="col col-4">
                            <label class="label"><b>ADECUACIÓN DE INSTALACIÓN</b></label>
                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                <textarea rows="2" name="ade_instalaciones" placeholder="Describa adecuaciones eléctricas, sanitarias o de gases medicinales...">' . htmlspecialchars($row['tp_adecuacion_instalacion'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                        <section class="col col-4">
                            <label class="label"><b>OBSERVACIONES / JUSTIFICACIÓN TÉCNICA</b></label>
                            <label class="textarea"><i class="icon-append fa fa-comment-o"></i>
                                <textarea rows="2" name="observaciones" placeholder="Detalles de justificación...">' . htmlspecialchars($row['observaciones'], ENT_QUOTES, 'UTF-8') . '</textarea>
                            </label>
                        </section>
                    </div>
                </fieldset>
                
                <footer>
                    <button type="submit" class="btn btn-primary btn_guardar_requerimiento_pluri" style="background: #1a237e; border-color: #0d47a1; font-weight: bold;">
                        <i class="fa fa-save"></i> GUARDAR REQUERIMIENTO
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold;">
                        CANCELAR
                    </button>
                    <center style="margin-top:10px;">
                        <div class="loada_spinner" style="display: none;">
                            <i class="fa fa-refresh fa-spin fa-2x text-primary"></i>
                            <br><small class="text-muted">Procesando registros en el servidor...</small>
                        </div>
                    </center>
                </footer>
            </form>';

            // 6. Cierre del Flujo y Retorno de Respuesta formateada en un JSON robusto
            $result = array('respuesta' => 'correcto', 'html' => $html);
            $this->output->set_content_type('application/json')->set_output(json_encode($result));
        } else {
            show_404();
        }
    }


     public function eliminar_requerimiento_equipamiento() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // Sanitización estricta de variables contra inyecciones XSS
            $post = $this->security->xss_clean($this->input->post());
            $form_equip_id = isset($post['form_equip_id']) ? intval($post['form_equip_id']) : 0;

            if ($form_equip_id <= 0) {
                $result = array('respuesta' => 'error', 'mensaje' => 'Identificador de registro inválido o inexistente.');
                $this->_retornar_json($result);
                return;
            }

            // 2. Protocolo de Transacción en Bloque (PostgreSQL)
            $this->db->trans_begin();

            try {
                // Paso A: Cambiamos el estado a 3 (Eliminado / Dado de baja) en la tabla principal
                $data_baja = array('estado' => 3);
                $this->db->where('form_equip_id', $form_equip_id);
                $this->db->update('formulario_diagnostico_equipamiento', $data_baja);

                // Paso B: Purgamos físicamente las filas del quinquenio para liberar espacio en disco
                $this->db->where('form_equip_id', $form_equip_id);
                $this->db->delete('temporalidad_diagnostico_equipamiento');

                // 3. Evaluación del estado de la transacción
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = array('respuesta' => 'error', 'mensaje' => 'Error interno al intentar procesar la baja en las tablas relacionales.');
                } else {
                    $this->db->trans_commit();
                    $result = array('respuesta' => 'correcto');
                }

            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result = array('respuesta' => 'error', 'mensaje' => 'Excepción crítica en la base de datos: ' . $e->getMessage());
            }

            // Despachamos la respuesta en un JSON legítimo interpretado por tu script
            $this->_retornar_json($result);

        } else {
            show_404();
        }
    }
}