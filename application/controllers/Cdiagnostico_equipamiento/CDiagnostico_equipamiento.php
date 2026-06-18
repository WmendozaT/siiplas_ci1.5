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

            // Recolección y tipado de variables maestras de la Ficha Técnica
            $form_equip_id       = intval($post['form_equip_id']);
            $equip_id            = intval($post['equip_id']);
            $dist_id             = intval($post['dist_id']);
            $tp_registro         = intval($post['tp_registro']);
            
            // Mapeo condicional según tp_registro (1: Establecimiento, 2: Proyecto Inversión)
            $act_id              = ($tp_registro == 1) ? intval($post['act_id']) : 0;
            $nombre_inversion    = ($tp_registro == 2) ? trim(strtoupper($post['nombre_inversion'])) : '';

            $responsable         = trim(strtoupper($post['responsable']));
            $nombre_equipamiento = trim(strtoupper($post['nombre_equipamiento']));
            $servicio_unidad     = trim(strtoupper($post['servicio_unidad']));
            $ubicacion_physica   = trim(strtoupper($post['ubicacion_fisica']));
            $tp_compra           = intval($post['tp_compra']);
            $par_id              = intval($post['par_id']);
            //$tp_adecuacion       = intval($post['tp_adecuacion']);
            //$tp_firma            = intval($post['tp_firma']);
            
            $ade_infraestructura = trim(strtoupper($post['ade_infraestructura']));
            $ade_instalaciones   = trim(strtoupper($post['ade_instalaciones']));
            $observaciones       = trim(strtoupper($post['observaciones']));

            // Variables numéricas de control financiero
            $cantidad       = intval($post['cantidad']);
            $costo_unitario = floatval($post['costo_unitario']);
            $costo_total    = $cantidad * $costo_unitario; // Cálculo matemático forzado en backend

            // --------------------------------------------------------------------------
            // CAPA 2: SEGUNDO BLOQUEO DE INTEGRIDAD MATEMÁTICA EN EL SERVIDOR
            // --------------------------------------------------------------------------
            $suma_quinquenio = 0;
            for ($anio = 2026; $anio <= 2030; $anio++) {
                $suma_quinquenio += isset($post['gest' . $anio]) ? floatval($post['gest' . $anio]) : 0;
            }

            // Si un usuario malintencionado saltó el JS con montos descuadrados, el backend lo frena
            if (abs($suma_quinquenio - $costo_total) > 0.01) {
                $result = array(
                    'respuesta' => 'error',
                    'mensaje' => 'La sumatoria distribuida en las gestiones (' . number_format($suma_quinquenio, 2, ',', '.') . ' Bs.) no coincide con el Costo Total (' . number_format($costo_total, 2, ',', '.') . ' Bs.). Verifique los datos.'
                );
                $this->_retornar_json($result);
                return;
            }

            // --------------------------------------------------------------------------
            // CAPA 3: PROCESAMIENTO DE TRANSACCIÓN EN BASE DE DATOS (POSTGRESQL)
            // --------------------------------------------------------------------------
            // Iniciamos una transacción controlada en CodeIgniter
            $this->db->trans_begin();

            try {
                // Estructuramos el array de datos para la tabla formulario_diagnostico_equipamiento
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
                    //'tp_adecuacion'       => $tp_adecuacion,
                    //'tp_firma'            => $tp_firma,
                    'tp_adecuacion_infra' => $ade_infraestructura,
                    'tp_adecuacion_instalacion'   => $ade_instalaciones,
                    'observaciones'       => $observaciones,
                    'estado'              => 1 // 1: Activo
                );

                if ($form_equip_id == 0) {
                    // MODO A: Inserción de Nuevo Registro
                    $this->db->insert('formulario_diagnostico_equipamiento', $data_form);
                    // Capturamos el ID incremental generado por PostgreSQL
                    $form_equip_id = $this->db->insert_id();
                } else {
                    // MODO B: Modificación de Registro Existente
                    $this->db->where('form_equip_id', $form_equip_id);
                    $this->db->update('formulario_diagnostico_equipamiento', $data_form);

                    // Limpieza preventiva: Borramos la temporalidad anterior para evitar duplicados del quinquenio
                    $this->db->where('form_equip_id', $form_equip_id);
                    $this->db->delete('temporalidad_diagnostico_equipamiento');
                }

                // 4. BUCLE DE PERSISTENCIA: Registramos año por año la temporalidad (2026 a 2030)
                for ($anio = 2026; $anio <= 2030; $anio++) {
                    $monto_anio = isset($post['gest' . $anio]) ? floatval($post['gest' . $anio]) : 0;

                    // Para optimizar el espacio en el disco, solo guardamos los años que tengan presupuesto > 0
                    if ($monto_anio >= 0) {
                        $data_temp = array(
                            'form_equip_id' => $form_equip_id,
                            'g_id'          => $anio,
                            'prog_equi'     => $monto_anio
                        );
                        $this->db->insert('temporalidad_diagnostico_equipamiento', $data_temp);
                    }
                }

                // 5. EVALUACIÓN DEL FIN DE LA TRANSACCIÓN
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = array('respuesta' => 'error', 'mensaje' => 'Error de consistencia al escribir en las tablas relacionales.');
                } else {
                    $this->db->trans_commit();
                    $result = array('respuesta' => 'correcto', 'form_equip_id' => $form_equip_id);
                }

            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result = array('respuesta' => 'error', 'mensaje' => 'Excepción crítica en la base de datos: ' . $e->getMessage());
            }

            // Despachamos la respuesta JSON
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

}