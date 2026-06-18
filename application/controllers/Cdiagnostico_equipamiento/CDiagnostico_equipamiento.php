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
$tabla.=$this->js_validacion();
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

           
           $tabla = $this->unidad_ejecutora_seleccionado($get_diagnostico[0]['equip_id'],$dist_id,1); //// get listado de la distrital

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
                    // ESCUCHA TRANSACCIONAL: VALIDACIÓN ABSOLUTA DE TODOS LOS CAMPOS
                    // ==========================================================================
                    $(document).on(\'submit\', \'#form_nuevo\', function(e) {
                        e.preventDefault();

                        // --------------------------------------------------------------------------
                        // CAPA 1: VALIDACIÓN MANUAL DE OBLIGATORIEDAD INDIVIDUAL
                        // --------------------------------------------------------------------------
                        var tp_registro = $(\'#tp_registro\').val();
                        
                        // 1. Validar Sección Dinámica (Establecimiento o Proyecto)
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

                        // 2. Validar Datos de Identificación Fijos
                        if ($(\'#responsable\').val().trim() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Ingrese el Nombre del Responsable / Solicitante.");
                            $(\'#responsable\').focus();
                            return false;
                        }

                        // 3. Validar Especificaciones Técnicas del Equipo
                        if ($(\'#nombre_equipamiento\').val().trim() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Ingrese el Nombre del Equipamiento Médico.");
                            $(\'#nombre_equipamiento\').focus();
                            return false;
                        }
                        if ($(\'#servicio_unidad\').val().trim() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Ingrese el Servicio / Unidad Destino.");
                            $(\'#servicio_unidad\').focus();
                            return false;
                        }
                        if ($(\'#ubicacion_fisica\').val().trim() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Ingrese la Ubicación Física Exacta.");
                            $(\'#ubicacion_fisica\').focus();
                            return false;
                        }
                        if ($(\'#tp_compra\').val() === "") {
                            alertify.error("⚠️ Campo Obligatorio: Seleccione el Tipo de Compra.");
                            $(\'#tp_compra\').focus();
                            return false;
                        }

                        // 4. Validar Matriz Financiera Básica
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

                                    // 🚨 SOLUCIÓN AL ERROR DE LA SOMBRA NEGRA (BACKDROP PERSISTENTE)
                                    // Eliminamos de forma manual el fondo oscuro remanente en el DOM de SmartAdmin
                                    $(\'.modal-backdrop\').remove();
                                    // Removemos la clase de congelamiento del body para restaurar los clics de la grilla
                                    $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');

                                    // 2. REINICIO DE VALORES: Vacía el formulario para el siguiente registro
                                    if ($(\'#form_nuevo\').length > 0) {
                                        $(\'#form_nuevo\')[0].reset();
                                    }
                                    
                                    // Restablecemos manualmente campos ocultos y numéricos que no limpia el reset común
                                    $("#form_equip_id").val("0");
                                    $("#cantidad").val("0");
                                    $("#costo_unitario").val("0.00");
                                    $("#costo_total").val("0.00");
                                    $("#total_prog").val("0.00");
                                    $(".prog-anio").val("0");
                                    $("#tp_registro").val("1").trigger("change"); // Muestra sección establecimientos
                                    $("#alerta_descuadre_poa").remove(); // Quita alertas de error previas

                                    // 3. REFRESCO AUTOMÁTICO EN CALIENTE DE LA TABLA SIN RECARGAR LA PÁGINA
                                    $("#dist_id").trigger("change");

                                } else {
                                    if (typeof alertify !== "undefined") {
                                        alertify.error("❌ Restricción: " + data.mensaje);
                                    }
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


        $tabla .= '
        <script type="text/javascript">
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // ==========================================================================
                    // ESCUCHA 1: AL CLIC EN MODIFICAR -> Invoca data por ID y la inyecta al Modal
                    // ==========================================================================
                    $(document).on(\'click\', \'.btn_modificar_equip\', function() {
                        var form_equip_id = $(this).attr(\'data-id\');
                        var dist_id = $(\'#dist_id\').val();
                        var $body_modal = $(\'#modal_nuevo_equipamiento .modal-body\');

                        $.ajax({
                            url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_formulario_modal_html",
                            type: \'POST\',
                            data: { form_equip_id: form_equip_id, dist_id: dist_id },
                            dataType: \'json\',
                            beforeSend: function() {
                                $body_modal.html(\'<div class="text-center" style="padding:40px 0;"><i class="fa fa-gear fa-spin fa-3x text-warning"></i><h5><b>Recuperando especificaciones técnicas...</b></h5></div>\');
                            },
                            success: function(data) {
                                if (data.respuesta === "correcto") {
                                    $body_modal.html(data.html);
                                    
                                    // Forzamos el gatillado del tipo de registro para conmutar las vistas del modal
                                    $(\'#tp_registro\').trigger(\'change\');
                                }
                            }
                        });
                    });

                    // ==========================================================================
                    // ESCUCHA 2: AL CLIC EN ELIMINAR -> Confirmación con Alertify y baja lógica por AJAX
                    // ==========================================================================
                    $(document).on(\'click\', \'.btn_eliminar_equip\', function() {
                        var form_equip_id = $(this).attr(\'data-id\');

                        alertify.confirm("⚠️ ALERTA DE SEGURIDAD CNS", "¿Está absolutamente seguro de dar de baja este requerimiento de equipamiento del SIIPLAS? Esta acción alterará el consolidado plurianbal.", function() {
                            
                            $.ajax({
                                url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/eliminar_requerimiento_equipamiento",
                                type: \'POST\',
                                data: { form_equip_id: form_equip_id },
                                dataType: \'json\',
                                success: function(data) {
                                    if (data.respuesta === "correcto") {
                                        alertify.success("🗑️ Éxito: Registro eliminado correctamente.");
                                        // Refresca la tabla automáticamente sin parpadeos
                                        $("#dist_id").trigger("change");
                                    } else {
                                        alertify.error("❌ Error: " + data.mensaje);
                                    }
                                }
                            });
                        }, function() {
                            alertify.error("Operación cancelada.");
                        });
                    });

                }
            });
        </script>';
        return $tabla;
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
                                                            data-toggle="modal" 
                                                            data-target="#modal_nuevo_equipamiento" 
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
         $tabla.=$this->js_validacion();
       
        return $tabla;
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