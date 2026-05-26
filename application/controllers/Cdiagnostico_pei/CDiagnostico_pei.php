<?php
class CDiagnostico_pei extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('diagnosticoPei/model_diagnosticopei');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->load->library('lib_diagnostico_pei');
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
        $pei = $this->model_diagnosticopei->get_diagnostico_activo();
        $dist_id = $this->dist_id; // Asegúrate de que esta variable esté definida
        $data['titulo']='';
        // 1. Verificación temprana (Early Return) para evitar anidación
        if (empty($pei)) {
            $data['cuerpo'] = $this->_mensaje_error("Solicitar que se habilite el formulario de diagnóstico PEI.");
            return $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data);
        }

        $pei_id = $pei[0]['pei_id'];
        $form_distrital = $this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id, $dist_id);

        if($this->tp_adm == 1){
          // Administrador Nacional
            $data['titulo'] = $this->Seleccion_unidadEjecutora();
            $data['cuerpo'] = '<div id="contenedor_formulario"></div>';
        }elseif ($this->conf_pei == 1) {
            // Usuario con permiso de llenado
            $data['cuerpo'] = $this->unidad_ejecutora_eleccionado($pei_id, $dist_id,0); /// regional
        } else { 
            // Acceso restringido por configuración
            $data['cuerpo'] = $this->_mensaje_error("Usted no cuenta con los privilegios necesarios para el llenado.");
        }


        $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data);
      
    }

    // Función auxiliar para no repetir código HTML de alertas
    private function _mensaje_error($mensaje) {
        return '
        <div class="alert alert-block alert-danger">
            <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Atención!</h4>
            <p>' . $mensaje . '</p>
        </div>';
    }

    /*------- Selecciona Unidad Ejecutora -------*/
    public function Seleccion_unidadEjecutora(){
      $get_diagnostico=$this->model_diagnosticopei->get_diagnostico_activo();
      $UnidadEjecutora=$this->model_diagnosticopei->lista_UnidadEjecutora(); 
      $tabla=''; 
      if(count($get_diagnostico)!=0){
        $tabla.='
          <article class="col-sm-12">
            <input name="base" type="hidden" value="'.base_url().'">
            <div class="well">
              <form class="smart-form">
                  <header>DIAGNOSTICO PEI ('.$get_diagnostico[0]['g_id_inicio'].' - '.$get_diagnostico[0]['g_id_fin'].')</header>
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
                          <a href="'.site_url("Diagnostico_pei/exportar_consolidado_excel/".$get_diagnostico[0]['pei_id']."/0").'" class="btn btn-success btn-sm" style="padding: 10px; width: 100%; text-align: center; color: white;">
                            <i class="fa fa-file-excel-o"></i> DESCARGAR CONSOLIDADO
                          </a>
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
                <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Sin PEI asignado!</h4>
                <p>Porfavor asigne datos del PEI .</p>
            </div>';
      }
      
      return $tabla;
    }


    /*--- GET LISTA DE UNIDAD EJECUTORA ----*/
    public function get_unidad_ejecutora(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            // Cambiado de 'dist_id' a 'id' para que coincida con el JS
            $dist_id = $this->security->xss_clean($post['id']); 
            $get_diagnostico=$this->model_diagnosticopei->get_diagnostico_activo();

            // Aquí puedes cargar una vista y pasarla a string
            if(count($this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($get_diagnostico[0]['pei_id'],$dist_id))==0){
                $data_to_store = array(
                 'pei_id' => $get_diagnostico[0]['pei_id'],
                 'dist_id' => $dist_id,
                );
                $this->db->insert('formulario_diagnostico_pei', $data_to_store);
            }
           
           $tabla = $this->unidad_ejecutora_eleccionado($get_diagnostico[0]['pei_id'],$dist_id,1);

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




    /*------- Listado de formularios -------*/
    public function unidad_ejecutora_eleccionado($pei_id,$dist_id,$tp_adm){
      $get_form_distrital=$this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id,$dist_id);

      $tabla='';
      $tabla.='
          <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            ' . $this->lib_diagnostico_pei->style_form() . '
            
            <div id="toast-notificacion" class="toast-msg">
              ¡Información guardada correctamente! ✓
            </div>
            
            <div class="well well-sm well-light">
                <!-- ==================== BARRA DE CABECERA CON ACCIONES ==================== -->
                <div class="row" style="margin-bottom: 15px; display: flex; align-items: center; border-bottom: 2px solid #3276b1; padding-bottom: 10px;">
                    <div class="col-xs-12 col-sm-6 col-md-7 col-lg-7">
                        <hr>
                        <h2 style="margin: 0; padding: 0; color: #212121; font-weight: bold;">
                            <i class="fa fa-hospital-o text-primary"></i> ' . strtoupper($get_form_distrital[0]['dist_distrital']) . '
                        </h2>
                        <small class="text-muted" style="font-size: 11px;">Módulo de Registro Diagnóstico Quinquenal (2021 - 2025)</small>
                    </div>';
                    $archivo_existente = (isset($get_form_distrital[0]['form_archivo_scanneado']) && !empty($get_form_distrital[0]['form_archivo_scanneado'])) ? trim($get_form_distrital[0]['form_archivo_scanneado']) : '';

                    $tabla .= '
                    <div class="col-xs-12 col-sm-6 col-md-5 col-lg-5 text-right" style="margin-top: 5px;">';
                        if($get_form_distrital[0]['form_opciones']==1 & $this->tp_adm==0){
                          $tabla.='
                          <button type="button" 
                                class="btn btn-primary btn-sm" 
                                onclick="abrirModalSubidaEscaneados()" 
                                style="font-weight: bold; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); margin-left: 5px;"
                                data-toggle="tooltip" 
                                title="Cargar al sistema el documento PDF final firmado y escaneado">
                            <i class="fa fa-upload"></i> Subir Escaneado (PDF)
                          </button>';
                        }
                      $tabla.='
                          <!-- NUEVO Botón 3: Ver Archivo Cargado (Oculto por defecto si el campo está vacío en DB) -->
                          <button type="button" 
                                  id="btn_ver_pdf_modal"
                                  class="btn btn-warning btn-sm" 
                                  onclick="verPdfEscaneadoModal()" 
                                  style="font-weight: bold; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); margin-left: 5px; ' . ($archivo_existente == '' ? 'display: none;' : '') . '"
                                  data-url="' . base_url() . 'escaneados_form_pei/' . $archivo_existente . '"
                                  data-toggle="tooltip" 
                                  title="Previsualizar el expediente digitalizado en la plataforma">
                              <i class="fa fa-eye"></i> Ver Archivo Digitalizado
                          </button>
                          <a href="' . base_url() . 'index.php/admin/dashboard" 
                           class="btn btn-danger btn-sm" 
                           style="font-weight: bold; border-radius: 4px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); margin-left: 5px;"
                           data-toggle="tooltip" 
                           title="Volver atrás al Dashboard de administración general">
                            <i class="fa fa-arrow-left"></i> Volver a menu
                        </a>
                    </div>';
                    
                    $tabla.='
                </div>

                <!-- ==================== CONTENEDOR DE PESTAÑAS (TABS) ==================== -->
                <div id="tabs" data-pei="' . $pei_id . '" data-dist="' . $dist_id . '">
                  <ul>
                    <li><a href="#tabs-a" data-url="poblacion_afiliada"><b>I.- POBLACIÓN AFILIADA</b></a></li>
                    <li><a href="#tabs-b" data-url="grupo_etareo"><b>I.I.- POBLACIÓN POR GRUPO ETAREO</b></a></li>
                    <li><a href="#tabs-c" data-url="empresas_aportantes"><b>II.- EMPRESAS APORTANTES</b></a></li>
                    <li><a href="#tabs-d" data-url="perfil_epidemiologico"><b>III.- PERFIL EPIDEMIOLOGICO</b></a></li>
                    <li><a href="#tabs-e" data-url="infraestructura"><b>IV.- INFRAESTRUCTURA</b></a></li>
                    <li><a href="#tabs-f" data-url="diagnostico_camas"><b>V.- DIAGNOSTICO CAMAS</b></a></li>
                    <li><a href="#tabs-g" data-url="equipo"><b>VI.- EQUIPO</b></a></li>
                    <li><a href="#tabs-h" data-url="recursos_humanos"><b>VII.- RECURSOS HUMANOS</b></a></li>
                    <li><a href="#tabs-i" data-url="compra_servicios"><b>VIII.- COMPRA DE SERVICIOS</b></a></li>
                    <li><a href="#tabs-j" data-url="presupuestos"><b>IX.- PRESUPUESTOS</b></a></li>
                    <li><a href="#tabs-k" data-url="reembolsos"><b>X.- REEMBOLSOS</b></a></li>
                    <li><a href="#tabs-j" data-url="ambulancias"><b>XI.- AMBULANCIAS</b></a></li>
                  </ul>
                  
                  <!-- Paneles de Contenido de las pestañas -->
                  <div id="tabs-a"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-b"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-c"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-d"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-e"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-f"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-g"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-h"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-i"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-j"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-k"><div class="row"><div class="contenido-ajax"></div></div></div>
                  <div id="tabs-j"><div class="row"><div class="contenido-ajax"></div></div></div>
                </div>
            </div>
        </article>

        <!-- ==================== MODAL DE SUBIDA DE ARCHIVOS ESCANEADOS ==================== -->
        <div class="modal fade" id="modal_subida_pdf" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="border-radius: 6px;">
                    <div class="modal-header" style="background-color: #3276b1; color: #fff; border-top-left-radius: 5px; border-top-right-radius: 5px;">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #fff; opacity: 0.8;">&times;</button>
                        <h4 class="modal-title" style="font-weight: bold;"><i class="fa fa-cloud-upload"></i> Subir Formularios Escaneados</h4>
                    </div>
                    <form id="form_subir_escaneado" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="pei_id" value="' . $pei_id . '">
                            <input type="hidden" name="dist_id" value="' . $dist_id . '">
                            <input type="hidden" name="abrev" value="' . $get_form_distrital[0]['abrev'] . '">
                            
                            <div class="alert alert-warning" style="font-size: 11.5px; line-height: 1.5; color: #8a6d3b; background-color: #fcf8e3; border-color: #faebcc; padding: 12px;">
                                <i class="fa fa-exclamation-triangle" style="font-size: 14px; margin-right: 5px;"></i> 
                                <b style="font-size: 12px; text-transform: uppercase;">¡REQUISITO OBLIGATORIO DE ENVÍO!</b>
                                <hr style="border-top: 1px solid #f7e1b5; margin-top: 5px; margin-bottom: 5px;">
                                <ul style="margin-left: 15px; padding-left: 0;">
                                    <li>Los <b>11 formularios</b> impresos del diagnóstico deben ser firmados por su Administrador Regional o Agente Distrital.</li>
                                    <li>Toda la documentación debe ser digitalizada **en un solo archivo unificado de formato PDF**.</li>
                                    <li>El sistema **NO** aceptará archivos separados por pestañas o imágenes sueltas.</li>
                                </ul>
                            </div>
                            
                            <div class="form-group" style="margin-top: 15px;">
                                <label style="font-weight: bold; color: #333;">Seleccionar archivo escaneado (PDF):</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-file-pdf-o text-danger"></i></span>
                                    <input type="file" name="archivo_pdf" id="archivo_pdf" class="form-control" accept="application/pdf" required>
                                </div>
                                <small class="text-muted">El tamaño máximo permitido es de 20MB.</small>
                            </div>
                            
                            <!-- Contenedor para mostrar barra de progreso de carga -->
                            <div class="progress progress-sm progress-striped active" id="progreso_carga_container" style="display: none; margin-top: 15px;">
                                <div class="progress-bar bg-color-darken" id="barra_progreso_pdf" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>
                        <div class="modal-footer" style="background-color: #fafafa;">
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="font-weight: bold;">Cancelar</button>
                            <button type="submit" class="btn btn-primary btn-sm" id="btn_confirmar_subida" style="font-weight: bold;"><i class="fa fa-check"></i> Cargar Archivo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';

        $tabla.='
        <!-- ==================== MODAL DE SUBIDA DE ARCHIVOS ESCANEADOS ==================== -->
          <div class="modal fade" id="modal_subida_pdf" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog">
                  <div class="modal-content" style="border-radius: 6px;">
                      <div class="modal-header" style="background-color: #3276b1; color: #fff; border-top-left-radius: 5px; border-top-right-radius: 5px;">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #fff; opacity: 0.8;">&times;</button>
                          <h4 class="modal-title" style="font-weight: bold;"><i class="fa fa-cloud-upload"></i> Subir Formularios Escaneados</h4>
                      </div>
                      <form id="form_subir_escaneado" enctype="multipart/form-data">
                          <div class="modal-body">
                              <input type="hidden" name="pei_id" value="' . $pei_id . '">
                              <input type="hidden" name="dist_id" value="' . $dist_id . '">

                              
                              <div class="alert alert-info" style="font-size: 11.5px; line-height: 1.4;">
                                  <i class="fa fa-info-circle"></i> <b>Instrucción:</b> Seleccione el archivo digitalizado único en formato <b>PDF</b> que contiene todos los reportes del diagnóstico firmados por el Administrador Regional o Agente Distrital.
                              </div>
                              
                              <div class="form-group" style="margin-top: 15px;">
                                  <label style="font-weight: bold; color: #333;">Seleccionar archivo escaneado (PDF):</label>
                                  <div class="input-group">
                                      <span class="input-group-addon"><i class="fa fa-file-pdf-o text-danger"></i></span>
                                      <input type="file" name="archivo_pdf" id="archivo_pdf" class="form-control" accept="application/pdf" required>
                                  </div>
                                  <small class="text-muted">El tamaño máximo permitido es de 20MB.</small>
                              </div>
                              
                              <!-- Contenedor para mostrar barra de progreso de carga -->
                              <div class="progress progress-sm progress-striped active" id="progreso_carga_container" style="display: none; margin-top: 15px;">
                                  <div class="progress-bar bg-color-darken" id="barra_progreso_pdf" role="progressbar" style="width: 0%"></div>
                              </div>
                          </div>
                          <div class="modal-footer" style="background-color: #fafafa;">
                              <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="font-weight: bold;">Cancelar</button>
                              <button type="submit" class="btn btn-primary btn-sm" id="btn_confirmar_subida" style="font-weight: bold;"><i class="fa fa-check"></i> Cargar Archivo</button>
                          </div>
                      </form>
                  </div>
              </div>
          </div>';

          $tabla .= '
          <!-- ==================== MODAL VISOR INTERACTIVO DE PDF CON ELIMINACIÓN ==================== -->
          <div class="modal fade" id="modal_visor_pdf" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-lg" style="width: 90%; max-width: 1200px;">
                  <div class="modal-content" style="border-radius: 6px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
                      
                      <div class="modal-header" style="background-color: #f57c00; color: #fff; border-top-left-radius: 5px; border-top-right-radius: 5px; padding: 10px 15px;">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color: #fff; opacity: 0.9;">&times;</button>
                          <h4 class="modal-title" style="font-weight: bold; font-size: 14px;">
                              <i class="fa fa-file-pdf-o"></i> VISOR DE FORMULARIOS ESCANEADOS PEI
                          </h4>
                      </div>
                      
                      <div class="modal-body" style="padding: 0; height: 600px; background-color: #525659; overflow: hidden;">
                          <iframe id="iframe_visor_pdf" src="" style="width: 100%; height: 100%; border: none; display: block;"></iframe>
                      </div>
                      
                      <div class="modal-footer" style="background-color: #fafafa; padding: 8px 15px; margin-top: 0; border-top: 1px solid #e5e5e5;">
                          <small class="text-muted pull-left" style="margin-top: 5px; text-align: left; width: 50%;">
                              <i class="fa fa-info-circle text-primary"></i> Use los controles internos del visor para aplicar Zoom o imprimir.
                          </small>
                          
                          <!-- NUEVO BOTÓN: Eliminar Reporte (Visible solo para administradores habilitados) -->
                          <button type="button" 
                                  id="btn_eliminar_pdf_server" 
                                  class="btn btn-danger btn-sm" 
                                  onclick="eliminarReporteEscaneado()"
                                  data-pei="' . $pei_id . '" 
                                  data-dist="' . $dist_id . '"
                                  style="font-weight: bold; border-radius: 4px; margin-right: 5px;">
                              <i class="fa fa-trash-o"></i> Eliminar Archivo 
                          </button>

                          <button type="button" class="btn btn-default btn-sm" data-dismiss="modal" style="font-weight: bold; border-radius: 4px;">Cerrar Visor</button>
                      </div>
                  </div>
              </div>
          </div>';


            if($tp_adm==1){
              $tabla.='
              <script type="text/javascript">
              $(document).ready(function() {
                  $("#tabs").tabs({
                      beforeActivate: function(event, ui) {
                          var panel = $(ui.newPanel);
                          var seccion = $(ui.newTab).find("a").attr("data-url");

                          // CAPTURAMOS LOS VALORES DEL DIV PADRE
                          var pei_id = $("#tabs").attr("data-pei");
                          var dist_id = $("#tabs").attr("data-dist");

                          // Si el panel no tiene contenido real, cargamos
                              panel.html("<div style=\'text-align:center; padding:50px;\'><i class=\'fa fa-spinner fa-spin\'></i> Cargando formulario ...</div>");
                              
                              $.post("'.base_url().'index.php/Cdiagnostico_pei/CDiagnostico_pei/cargar_formulario", { seccion: seccion,pei: pei_id,dist: dist_id }, function(data) {
                                  panel.html(data);

                              }).error(function() {
                                  panel.html("Error al cargar datos.");
                              });
                          
                      }
                  });

                  // Carga manual de la primera pestaña al cargar la página
                  var firstTab = $("#tabs ul li:first-child");
                  var firstPanel = $("#tabs-a");
                  $("#tabs").tabs("option", "beforeActivate")({}, {
                      newPanel: firstPanel,
                      newTab: firstTab
                  });
              });
            </script>

          
            <script type="text/javascript">
              // DO NOT REMOVE : GLOBAL FUNCTIONS!
              $(document).ready(function() {
                pageSetUp();
                $("#menu").menu();
                $(".ui-dialog :button").blur();
                $("#tabs").tabs();
              })
            </script>';
            }
            else{
              $tabla.='
              <script type="text/javascript">
                // Evento nativo del navegador para esperar el cascarón HTML
                document.addEventListener("DOMContentLoaded", function() {
                    
                    // Bucle de espera seguro para verificar la carga de librerías del footer
                    (function verificarLibrerias() {
                        if (window.jQuery && window.jQuery.ui) {
                            inicializarTabs(window.jQuery);
                        } else {
                            setTimeout(verificarLibrerias, 20);
                        }
                    })();

                    function inicializarTabs($) {
                        // 1. CONFIGURACIÓN ÚNICA DE LAS PESTAÑAS (TABS)
                        $("#tabs").tabs({
                            beforeActivate: function(event, ui) {
                                var panel = $(ui.newPanel);
                                var seccion = $(ui.newTab).find("a").attr("data-url");

                                var pei_id = $("#tabs").attr("data-pei");
                                var dist_id = $("#tabs").attr("data-dist");

                                panel.html("<div style=\'text-align:center; padding:50px;\'><i class=\'fa fa-spinner fa-spin\'></i> Cargando formulario ...</div>");
                                
                                $.ajax({
                                    url: "' . base_url() . 'index.php/Cdiagnostico_pei/CDiagnostico_pei/cargar_formulario",
                                    type: "POST",
                                    data: { seccion: seccion, pei: pei_id, dist: dist_id },
                                    success: function(data) {
                                        panel.html(data);
                                    },
                                    error: function() {
                                        panel.html("<div class=\'alert alert-danger\'>Error al conectar con el servidor. Reintente.</div>");
                                    }
                                });
                            }
                        });

                        // 2. CARGA INICIAL DE LA PRIMERA PESTAÑA AUTOMÁTICAMENTE
                        var firstTab = $("#tabs ul li:first-child");
                        var firstPanel = $("#tabs-a");
                        $("#tabs").tabs("option", "beforeActivate")({}, {
                            newPanel: firstPanel,
                            newTab: firstTab
                        });

                        // 3. LÓGICAS GLOBALES DE LA PLANTILLA INSTITUNCIAL
                        if (typeof pageSetUp === "function") { pageSetUp(); }
                        if (typeof $("#menu").menu === "function") { $("#menu").menu(); }
                        $(".ui-dialog :button").blur();
                    }
                });
              </script>';
            }

            //// Modal para subir Archivo digitalizado
            $tabla.='
           <script type="text/javascript">
            // --- 1. APERTURA DEL MODAL CON LIMPIEZA TOTAL ---
            function abrirModalSubidaEscaneados() {
                if($("#form_subir_escaneado").length > 0) {
                    $("#form_subir_escaneado")[0].reset();
                }
                $("#progreso_carga_container").hide();
                $("#barra_progreso_pdf").css("width", "0%");
                $("#modal_subida_pdf").modal("show");
            }

            // --- 2. NUEVO: FUNCIÓN PARA DESPLEGAR EL VISOR DE PDF EN CALIENTE ---
            function verPdfEscaneadoModal() {
                // Extraemos la URL en tiempo real que tiene inyectada el botón
                var url_archivo = $("#btn_ver_pdf_modal").attr("data-url");
                
                if(url_archivo && url_archivo !== "") {
                    // Se la pasamos al iframe visor y levantamos el modal interactivo
                    $("#iframe_visor_pdf").attr("src", url_archivo);
                    $("#modal_visor_pdf").modal("show");
                } else {
                    alert("⚠️ No se pudo recuperar la ruta del archivo digitalizado.");
                }
            }

            // --- 3. MANEJO INTERACTIVO DE EVENTOS DEL DOM ---
            document.addEventListener("DOMContentLoaded", function() {
                
                // Inicialización de tooltips de la cabecera
                if(typeof $ !== "undefined" && typeof $().tooltip === "function") {
                    $("[data-toggle=\'tooltip\']").tooltip();
                }

                // VALIDACIÓN PREVENTIVA DE TAMAÑO EN EL CLIENTE (Máximo 25MB)
                $(document).on("change", "#archivo_pdf", function() {
                    var archivo = this.files[0];
                    if (archivo) {
                        var tamanoMB = archivo.size / 1024 / 1024;
                        var limiteMaximo = 25; 

                        if (tamanoMB > limiteMaximo) {
                            alert("⚠️ El archivo seleccionado es demasiado pesado (" + tamanoMB.toFixed(2) + " MB).\nEl límite institucional máximo permitido para los 11 formularios unificados es de " + limiteMaximo + " MB.\n\nPor favor, optimice o reduzca la resolución del escaneo.");
                            $(this).val(""); 
                        }
                    }
                });

                // --- 4. PROCESAMIENTO Y ENVÍO AJAX ---
                $(document).on("submit", "#form_subir_escaneado", function(e) {
                    e.preventDefault();
                    
                    if ($("#archivo_pdf").val() == "") {
                        alert("⚠️ Por favor, seleccione un archivo PDF antes de confirmar la carga.");
                        return false;
                    }

                    var formData = new FormData(this);
                    
                    $("#btn_confirmar_subida").prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i> Subiendo...");
                    $("#progreso_carga_container").fadeIn(200);

                    $.ajax({
                        url: "' . base_url() . 'index.php/Cdiagnostico_pei/CDiagnostico_pei/guardar_pdf_escaneado",
                        type: "POST",
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        dataType: "json", 
                        xhr: function() {
                            var myXhr = $.ajaxSettings.xhr();
                            if(myXhr.upload){
                                myXhr.upload.addEventListener("progress", function(e){
                                    if(e.lengthComputable){
                                        var porcentaje = Math.round((e.loaded * 100) / e.total);
                                        $("#barra_progreso_pdf").css("width", porcentaje + "%");
                                    }
                                }, false);
                            }
                            return myXhr;
                        },
                        success: function(resp) {
                            var data = resp; 
                            
                            if (typeof resp === "string") {
                                try {
                                    data = JSON.parse(resp);
                                } catch (e) {
                                    console.error("Error crítico de análisis sintáctico:", resp);
                                    alert("❌ La respuesta del servidor está corrupta. Revise el log de CodeIgniter.");
                                    return;
                                }
                            }

                            if(data.status === "success") {
                                $("#modal_subida_pdf").modal("hide");
                                
                                // === ACTIVACIÓN EN CALIENTE DEL BOTÓN VER ESCANEADO ===
                                var nueva_ruta = "' . base_url() . 'escaneados_form_pei/" + data.nombre_archivo;
                                
                                // Inyectamos la URL real devuelta por el PHP y provocamos su aparición visual
                                $("#btn_ver_pdf_modal").attr("data-url", nueva_ruta).fadeIn(400);

                                // Feedback visual exitoso de la suite institucional
                                $("#toast-notificacion").text("✅ Documento consolidado cargado exitosamente.").fadeIn().delay(3000).fadeOut();
                            } else {
                                alert("⚠️ Restricción del Servidor: " + data.msg);
                                $("#progreso_carga_container").hide();
                            }
                        },
                        error: function(xhr) {
                            console.error("Respuesta cruda del servidor en error 500:", xhr.responseText);
                            alert("❌ Error crítico de comunicación de red. Verifique que el archivo no exceda la configuración post_max_size de PHP.");
                        },
                        complete: function() {
                            $("#btn_confirmar_subida").prop("disabled", false).html("<i class=\'fa fa-check\'></i> Cargar Archivo");
                        }
                    });
                });
            });
        </script>';

        $tabla.='
        <script type="text/javascript">
          // --- NUEVO: FUNCIÓN GLOBAL PARA ELIMINAR EL EXPEDIENTE DESDE EL CONTROLADOR ---
          function eliminarReporteEscaneado() {
              var $btn = $("#btn_eliminar_pdf_server");
              var pei_id = $btn.data("pei");
              var dist_id = $btn.data("dist");

              // Mensaje formal de confirmación institucional de la Caja Nacional de Salud
              var confirmacion = confirm("⚠️ ¿Está absolutamente seguro de eliminar el reporte escaneado de esta regional?\n\nEsta acción borrará el archivo físico del servidor de forma permanente y permitirá a la distrital realizar una nueva carga.");
              
              if (confirmacion) {
                  // Bloqueamos el botón para evitar doble clic accidental
                  $btn.prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i> Eliminando...");

                  $.ajax({
                      url: "' . base_url() . 'index.php/Cdiagnostico_pei/CDiagnostico_pei/eliminar_pdf_escaneado",
                      type: "POST",
                      data: { pei_id: pei_id, dist_id: dist_id },
                      dataType: "json",
                      success: function(resp) {
                          if (resp.status === "success") {
                              // 1. Cerramos el visor interactivo de PDF
                              $("#modal_visor_pdf").modal("hide");
                              
                              // 2. OCULTACIÓN EN CALIENTE: Desvanecemos el botón "Ver Escaneado" de la cabecera
                              $("#btn_ver_pdf_modal").fadeOut(300, function() {
                                  $(this).attr("data-url", ""); // Limpiamos la ruta para seguridad
                              });
                              
                              // 3. Disparamos tu toast de notificación verde de éxito
                              $("#toast-notificacion").text("🗑️ El archivo fue eliminado correctamente del servidor.").fadeIn().delay(3000).fadeOut();
                          } else {
                              alert("⚠️ Restricción del Servidor: " + resp.msg);
                          }
                      },
                      error: function() {
                          alert("❌ Error crítico de comunicación de red al intentar remover el archivo.");
                      },
                      complete: function() {
                          // Restablecemos el estado operativo del botón en el modal footer
                          $btn.prop("disabled", false).html("<i class=\'fa fa-trash-o\'></i> Eliminar Reporte");
                      }
                  });
              }
          }
      </script>';
      return $tabla;
    }
    
    //// subir archivo digitalizado ..
    public function guardar_pdf_escaneado() {
      // 1. Limpieza radical de salida para evitar que basuras rompan el JSON
      if (ob_get_length()) ob_clean();

      // 2. Seguridad: En CI 1.5 is_ajax_request() no existía nativamente, emulamos la validación
      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'Acceso directo no permitido al servidor.'));
          exit;
      }

      $pei_id  = $this->input->post('pei_id');
      $dist_id = $this->input->post('dist_id');
      $abrev = $this->input->post('abrev');

      if (empty($pei_id) || empty($dist_id) || empty($abrev)) {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'Faltan parámetros críticos de identificación (PEI/Distrital).'));
          exit;
      }

      // 3. Buscar el registro maestro del formulario
      $this->db->where(array('pei_id' => $pei_id, 'dist_id' => $dist_id));
      $form_row = $this->db->get('formulario_diagnostico_pei')->row();

      if (!$form_row) {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'No se encontró la configuración del formulario PEI para la distrital seleccionada.'));
          exit;
      }

      // 4. CONFIGURACIÓN DE LA LIBRERÍA DE SUBIDA (CodeIgniter 1.5)
      $path_upload = './escaneados_form_pei/';
      
      if (!is_dir($path_upload)) {
          mkdir($path_upload, 0777, true);
      }

      // Limpiamos la abreviatura recibida para evitar espacios o caracteres que rompan la ruta
      $abrev_limpia = trim(strtoupper($abrev));

      // CORRECCIÓN: Reemplazamos el segundo dist_id por la variable abrev_limpia
      $nombre_archivo_limpio = 'PEI_CONSOLIDADO_' . $dist_id . '_' . time();

      $config['upload_path']   = $path_upload;
      $config['allowed_types'] = 'pdf';
      $config['max_size']      = '25600'; // 25 MB
      $config['file_name']     = $nombre_archivo_limpio;
      $config['overwrite']     = TRUE;

      // --- ENFOQUE ESTRICTO PARA CODEIGNITER 1.5 ---
      // Cargamos la librería pasándole los datos directamente
      $this->load->library('upload', $config);
      
      // En CI 1.5, forzamos la referencia manual extrayendo el objeto desde la superinstancia
      $CI =& get_instance();
      $this->upload = $CI->upload;

      // Ejecutamos la subida apuntando al input del archivo HTML
      if (!$this->upload->do_upload('archivo_pdf')) {
          $error = $this->upload->display_errors('', '');
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => $error));
          exit;
      }

      // Extraer metadatos en CI 1.5 (devuelve un array asociativo del archivo subido)
      $upload_data = $this->upload->data();
      $nombre_final_pdf = $upload_data['file_name'];

      // 5. OPTIMIZACIÓN DE DISCO: Borrar el PDF anterior si existía uno
      if (!empty($form_row->form_archivo_scanneado)) {
          $archivo_antiguo = $path_upload . $form_row->form_archivo_scanneado;
          if (file_exists($archivo_antiguo) && is_file($archivo_antiguo)) {
              unlink($archivo_antiguo); 
          }
      }

      // 6. ACTUALIZAR ESTADOS EN LA TABLA MAESTRA
      $data_update = array(
          'form_archivo_scanneado' => $nombre_final_pdf,
          'form_subir_scanneado'   => 1 
      );

      $this->db->where('form_id', $form_row->form_id);
      $res = $this->db->update('formulario_diagnostico_pei', $data_update);

      // 7. RESPUESTA COMPATIBLE CON TU SCRIPT JAVASCRIPT
      if ($res) {
          $respuesta = array(
              'status' => 'success',
              'nombre_archivo' => $nombre_final_pdf
          );
      } else {
          if (file_exists($path_upload . $nombre_final_pdf)) {
              unlink($path_upload . $nombre_final_pdf);
          }
          $respuesta = array('status' => 'error', 'msg' => 'El archivo se cargó físicamente pero falló el registro en el sistema SIIPLAS.');
      }

      // CABECERAS ESTRICTAS DE RETORNO JSON
      header('Content-Type: application/json');
      echo json_encode($respuesta);
      exit; 
  }

    //// Eliminar ARchivo
    public function eliminar_pdf_escaneado() {
      // 1. Limpieza radical de búfer para evitar que trazas o warnings rompan el formato JSON
      if (ob_get_length()) ob_clean();

      // 2. Seguridad: En CodeIgniter 1.5 emulamos la validación de peticiones estrictamente AJAX
      if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'Acceso directo denegado al servidor.'));
          exit;
      }

      // Recepción de parámetros inyectados desde los atributos data- del botón
      $pei_id  = $this->input->post('pei_id');
      $dist_id = $this->input->post('dist_id');

      if (empty($pei_id) || empty($dist_id)) {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'Parámetros insuficientes para procesar la baja del documento.'));
          exit;
      }

      // 3. Buscar el registro maestro del formulario en la base de datos
      $this->db->where(array('pei_id' => $pei_id, 'dist_id' => $dist_id));
      $form_row = $this->db->get('formulario_diagnostico_pei')->row();

      if (!$form_row) {
          header('Content-Type: application/json');
          echo json_encode(array('status' => 'error', 'msg' => 'No se encontró el registro del formulario en el sistema SIIPLAS.'));
          exit;
      }

      // 4. ELIMINACIÓN FÍSICA EN DISCO (Optimización de Almacenamiento)
      $path_upload = './escaneados_form_pei/';
      
      if (!empty($form_row->form_archivo_scanneado)) {
          $archivo_fisico = $path_upload . $form_row->form_archivo_scanneado;
          
          // Verificación estricta de seguridad: que exista y sea un archivo válido antes de borrar
          if (file_exists($archivo_fisico) && is_file($archivo_fisico)) {
              unlink($archivo_fisico); // Remueve físicamente el PDF del disco del servidor
          }
      }

      // 5. REAPERTURAR ESTADOS EN LA TABLA MAESTRA
      $data_clear = array(
          'form_archivo_scanneado' => NULL, // Limpiamos la ruta para habilitar nuevas cargas
          'form_subir_scanneado'   => 0    // Restablecemos el estado a "Pendiente"
      );

      $this->db->where('form_id', $form_row->form_id);
      $res = $this->db->update('formulario_diagnostico_pei', $data_clear);

      // 6. ENVIAR RESPUESTA FORMAL PROCESABLE POR TU AJAX
      header('Content-Type: application/json');
      if ($res) {
          echo json_encode(array('status' => 'success'));
      } else {
          echo json_encode(array('status' => 'error', 'msg' => 'El archivo físico se removió con éxito, pero falló la actualización de estado en la base de datos del SIIPLAS.'));
      }
      exit; // Cortamos el hilo de ejecución para proteger la pureza del JSON
  }


  //// Cargar formulario view
  function cargar_formulario() {
      $seccion = $this->input->post('seccion');
      $pei_id  = $this->input->post('pei');
      $dist_id = $this->input->post('dist');
      
      // Importante: Aquí debes cargar tus datos necesarios
      $get_form_distrital=$this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id,$dist_id); 

      switch ($seccion) {
          case 'poblacion_afiliada':
              echo $this->lib_diagnostico_pei->formulario_N1($get_form_distrital);
              break;
          case 'grupo_etareo':
              echo $this->lib_diagnostico_pei->formulario_N1_1($get_form_distrital);
              break;
          case 'empresas_aportantes':
              echo $this->lib_diagnostico_pei->formulario_N2($get_form_distrital);
              break;
          case 'perfil_epidemiologico':
              echo $this->lib_diagnostico_pei->formulario_N3($get_form_distrital);
              break;
          case 'infraestructura':
              echo $this->lib_diagnostico_pei->formulario_N4($get_form_distrital);
              break;
          case 'diagnostico_camas':
              echo $this->lib_diagnostico_pei->formulario_N5($get_form_distrital);
              break;
          case 'equipo':
              echo $this->lib_diagnostico_pei->formulario_N6($get_form_distrital);
              break;
          case 'recursos_humanos':
              echo $this->lib_diagnostico_pei->formulario_N7($get_form_distrital);
              break;
          case 'compra_servicios':
              echo $this->lib_diagnostico_pei->formulario_N8($get_form_distrital);
              break;
          case 'presupuestos':
              echo $this->lib_diagnostico_pei->formulario_N9($get_form_distrital);
              break;
          case 'reembolsos':
              echo $this->lib_diagnostico_pei->formulario_N10($get_form_distrital);
              break;
          case 'ambulancias':
              echo $this->lib_diagnostico_pei->formulario_N11($get_form_distrital);
              break;
          // ... otros casos
          default:
              echo "Sección no válida";
              break;
      }

  }

  /// Reporte Formulario Diagnostico Pei
  public function reporte_formulario_pei($tp_rep,$dist_id){
    $get_formulario=$this->model_diagnosticopei->get_dist_formulario_diagnostico($dist_id);
     $data['reporte']= $this->lib_diagnosticopei_reporte->select_reporte_diagnostico_pei($tp_rep,$get_formulario);
     $data['pie_rep']='dnp';
     $this->load->view('admin/diagnostico_pei/View_report_form_diagpei', $data);
  }

  /// Exportar Diagnostico en Excel
  // Método Principal que se invoca desde el botón de la interfaz nacional
  public function exportar_consolidado_excel($tp_rep, $dist_id) {
    if (ob_get_length()) ob_clean(); // Limpieza radical de búfer para evitar corrupción

    $pei_id  = intval($tp_rep);
    $dist_id = intval($dist_id);

    // 1. Inicialización e inyección de la librería PHPExcel
    $this->load->library('excel'); 
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setTitle("Consolidado Institucional PEI")
                                 ->setCreator("SIIPLAS - CNS");

    // 2. DEFINICIÓN DE ESTILOS EJECUTIVOS GLOBALES
    $styles = array(
        'header' => array(
            'font' => array('bold' => true, 'color' => array('rgb' => 'FFFFFF'), 'size' => 10, 'name' => 'Arial'),
            'fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('rgb' => '1A237E')), // Azul Marino
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

    // Carga obligatoria del modelo de forma compartida
    $this->load->model('Cdiagnostico_pei/model_diagnosticopei');

    // ==========================================================================
    // DELEGACIÓN DE PESTAÑAS (Pasamos el objeto y estilos por referencia con &$ )
    // ==========================================================================
    
    // PESTAÑA 1: Población Afiliada
    $this->_generar_pestaña_poblacion($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 2: Grupos Etáreos
    $this->_generar_pestaña_etareos($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 3: empresas
    $this->_generar_pestaña_empresas($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 4: perfil
    $this->_generar_pestaña_perfil($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 5: infraestructura
    $this->_generar_pestaña_infraestructura($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 6: diagnosticos camas
    $this->_generar_pestaña_camas($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 7: diagnosticos equipamiento
    $this->_generar_pestaña_equipos($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 8: Recursos humanos
    $this->_generar_pestaña_rrhh($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 9: Compra de Servicios
    $this->_generar_pestaña_compra_servicios($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 10: Diagnostico de Ingresos y Gastos
    $this->_generar_pestaña_presupuestos($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 11: Reembolsos
    $this->_generar_pestaña_reembolsos($objPHPExcel, $dist_id, $styles);

    // PESTAÑA 12: Ambulancias
    $this->_generar_pestaña_ambulancias($objPHPExcel, $dist_id, $styles);

    // ==========================================================================
    // 3. PROCESAMIENTO DE DESCARGA FINAL DEL EXPEDIENTE
    // ==========================================================================
    $filename = ($dist_id > 0) ? "Consolidado_PEI_Regional_" . $dist_id : "Consolidado_Nacional_PEI";
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
    header('Cache-Control: max-age=0');
    
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}



  private function _generar_pestaña_poblacion(&$objPHPExcel, $dist_id, $styles) {
    $objPHPExcel->setActiveSheetIndex(0);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Población Afiliada');

    // 1. Títulos de la Fila 1
    $headers = array(
        'A1' => 'ID DIST', 'B1' => 'REGIONAL / DISTRITAL', 'C1' => 'ABREV', 
        'D1' => 'GESTIÓN', 'E1' => 'TITULARES', 'F1' => 'PASIVOS', 
        'G1' => 'BENEFICIARIOS', 'H1' => 'TOTAL PROTEGIDO'
    );
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']);
    }
    $sheet->getRowDimension(1)->setRowHeight(25);

    // 2. Extracción y volcado de registros
    $poblacion_data = $this->model_diagnosticopei->get_formulario_N1_consolidado($dist_id);

    $f = 2;
    foreach ($poblacion_data as $row) {
        $sheet->setCellValue('A' . $f, $row['dist_id']);
        $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('D' . $f, $row['gestion']);
        $sheet->setCellValue('E' . $f, intval($row['titulares']));
        $sheet->setCellValue('F' . $f, intval($row['pasivos']));
        $sheet->setCellValue('G' . $f, intval($row['beneficiarios']));
        $sheet->setCellValue('H' . $f, intval($row['total_gestion']));

        // Alineación y formatos numéricos contables
        $sheet->getStyle('A'.$f.':D'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E'.$f.':H'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E'.$f.':H'.$f)->getNumberFormat()->setFormatCode('#,##0');
        
        $sheet->getStyle('A'.$f.':H'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // Autoajuste automático de anchos
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_etareos(&$objPHPExcel, $dist_id, $styles) {
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(1);
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Grupos Etáreos');

    // 1. Cabecera Combinada Fila 1 y Fila 2
    $sheet->mergeCells('A1:A2')->setCellValue('A1', 'ID DIST');
    $sheet->mergeCells('B1:B2')->setCellValue('B1', 'REGIONAL / DISTRITAL');
    $sheet->mergeCells('C1:C2')->setCellValue('C1', 'ABREV');
    $sheet->mergeCells('D1:D2')->setCellValue('D1', 'GRUPO ETÁREO');

    // Combinación horizontal de bloques anuales estratégicos
    $sheet->mergeCells('E1:G1')->setCellValue('E1', 'GESTIÓN 2021');
    $sheet->mergeCells('H1:J1')->setCellValue('H1', 'GESTIÓN 2022');
    $sheet->mergeCells('K1:M1')->setCellValue('K1', 'GESTIÓN 2023');
    $sheet->mergeCells('N1:P1')->setCellValue('N1', 'GESTIÓN 2024');
    $sheet->mergeCells('Q1:S1')->setCellValue('Q1', 'GESTIÓN 2025');

    // Aplicar estilos a la Fila 1 de encabezados macro
    foreach (range('A', 'S') as $col) {
        $sheet->getStyle($col . '1')->applyFromArray($styles['header']);
    }
    $sheet->getRowDimension(1)->setRowHeight(22);

    // 2. Subcabeceras de desglose interno (Fila 2)
    $subHeaders = array(
        'E2'=>'MASC', 'F2'=>'FEM', 'G2'=>'TOTAL', 'H2'=>'MASC', 'I2'=>'FEM', 'J2'=>'TOTAL',
        'K2'=>'MASC', 'L2'=>'FEM', 'M2'=>'TOTAL', 'N2'=>'MASC', 'O2'=>'FEM', 'P2'=>'TOTAL',
        'Q2'=>'MASC', 'R2'=>'FEM', 'S2'=>'TOTAL'
    );
    foreach ($subHeaders as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['subheader']);
    }
    $sheet->getRowDimension(2)->setRowHeight(18);

    // 3. Extracción de datos etáreos desde el modelo
    $etareo_data = $this->model_diagnosticopei->get_formulario_N1_etareo_consolidado();

    $f = 3;
    foreach ($etareo_data as $row) {
        $sheet->setCellValue('A' . $f, $row['dist_id']);
        $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('D' . $f, trim($row['grupo_etareo']));

        $sheet->setCellValue('E' . $f, intval($row['m_2021']));
        $sheet->setCellValue('F' . $f, intval($row['f_2021']));
        $sheet->setCellValue('G' . $f, intval($row['t_2021']));

        $sheet->setCellValue('H' . $f, intval($row['m_2022']));
        $sheet->setCellValue('I' . $f, intval($row['f_2022']));
        $sheet->setCellValue('J' . $f, intval($row['t_2022']));

        $sheet->setCellValue('K' . $f, intval($row['m_2023']));
        $sheet->setCellValue('L' . $f, intval($row['f_2023']));
        $sheet->setCellValue('M' . $f, intval($row['t_2023']));

        $sheet->setCellValue('N' . $f, intval($row['m_2024']));
        $sheet->setCellValue('O' . $f, intval($row['f_2024']));
        $sheet->setCellValue('P' . $f, intval($row['t_2024']));

        $sheet->setCellValue('Q' . $f, intval($row['m_2025']));
        $sheet->setCellValue('R' . $f, intval($row['f_2025']));
        $sheet->setCellValue('S' . $f, intval($row['t_2025']));

        // Formateo y rejilla
        $sheet->getStyle('A'.$f.':D'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E'.$f.':S'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('E'.$f.':S'.$f)->getNumberFormat()->setFormatCode('#,##0');
        
        $sheet->getStyle('A'.$f.':S'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // Autoajuste dinámico de anchos de la pestaña 2
    foreach (range('A', 'S') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }

  
  private function _generar_pestaña_empresas(&$objPHPExcel, $dist_id, $styles) {
      // 1. Llamada limpia al modelo consolidado
      $empresas_data = $this->model_diagnosticopei->get_formulario_N2_consolidado();

      // 3. Inicialización física de la Tercera Hoja del libro Excel
      $objPHPExcel->createSheet();
      $objPHPExcel->setActiveSheetIndex(2); // Índice 2 representa la tercera pestaña
      $sheet = $objPHPExcel->getActiveSheet();
      $sheet->setTitle('Empresas Aportantes');

      // 4. Configuración y Estilizado de las Cabeceras (Fila 1) - Removida columna H
      $headers = array(
          'A1' => 'ID DIST', 
          'B1' => 'REGIONAL / DISTRITAL', 
          'C1' => 'ABREV', 
          'D1' => 'GESTIÓN', 
          'E1' => 'EMPRESAS REGISTRADAS', 
          'F1' => 'APORTES AL DÍA', 
          'G1' => 'EMPRESAS EN MORA'
      );
      
      foreach ($headers as $pos => $text) {
          $sheet->setCellValue($pos, $text);
          $sheet->getStyle($pos)->applyFromArray($styles['header']); 
      }
      $sheet->getRowDimension(1)->setRowHeight(25); 

      // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
      $f = 2;
      foreach ($empresas_data as $row) {
          $sheet->setCellValue('A' . $f, $row['dist_id']);
          $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
          $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
          $sheet->setCellValue('D' . $f, $row['gestion']);
          
          // Convertimos a tipos numéricos puros para habilitar fórmulas nativas
          $sheet->setCellValue('E' . $f, intval($row['empresas']));
          $sheet->setCellValue('F' . $f, intval($row['aportes']));
          $sheet->setCellValue('G' . $f, intval($row['mora']));

          // CORRECCIÓN 1: Alineación centrada para datos base (Columnas A a D)
          $sheet->getStyle('A'.$f.':D'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          
          // CORRECCIÓN 2: Alineación a la derecha para todas las columnas numéricas (E a G)
          $sheet->getStyle('E'.$f.':G'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
          
          // CORRECCIÓN 3: Formato de millares contables aplicado a todo el bloque numérico (E a G)
          $sheet->getStyle('E'.$f.':G'.$f)->getNumberFormat()->setFormatCode('#,##0');
          
          // CORRECCIÓN 4: Aplicar la cuadrícula de bordes a toda la fila de datos (A a G)
          $sheet->getStyle('A'.$f.':G'.$f)->applyFromArray($styles['data']);
          
          $sheet->getRowDimension($f)->setRowHeight(18);
          $f++;
      }

      // 6. CORRECCIÓN 5: Autoajuste dinámico de ancho limitado estrictamente de la A a la G
      foreach (range('A', 'G') as $col) {
          $sheet->getColumnDimension($col)->setAutoSize(true);
      }
  }


  private function _generar_pestaña_perfil(&$objPHPExcel, $dist_id, $styles) {
    $perfil_data = $this->model_diagnosticopei->get_formulario_N3_consolidado();

    // 3. Inicialización física de la Cuarta Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(3); 
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Perfil Epidemiológico');

    // 4. CONFIGURACIÓN DE FILA 1 (Cabeceras Superiores Combinadas Ajustadas de la A a la O)
    $sheet->mergeCells('A1:A2')->setCellValue('A1', 'ID DIST');
    $sheet->mergeCells('B1:B2')->setCellValue('B1', 'REGIONAL / DISTRITAL');
    $sheet->mergeCells('C1:C2')->setCellValue('C1', 'ABREV');
    $sheet->mergeCells('D1:D2')->setCellValue('D1', 'CATEGORÍA PERFIL');
    $sheet->mergeCells('E1:E2')->setCellValue('E1', 'Nº POSICIÓN');

    // REVISIÓN: Bloques anuales ahora abarcan solo 2 columnas (Casos y Código)
    $sheet->mergeCells('F1:G1')->setCellValue('F1', 'GESTIÓN 2021');
    $sheet->mergeCells('H1:I1')->setCellValue('H1', 'GESTIÓN 2022');
    $sheet->mergeCells('J1:K1')->setCellValue('J1', 'GESTIÓN 2023');
    $sheet->mergeCells('L1:M1')->setCellValue('L1', 'GESTIÓN 2024');
    $sheet->mergeCells('N1:O1')->setCellValue('N1', 'GESTIÓN 2025');

    // Aplicar estilos a la primera fila macro (De la A a la O)
    foreach (range('A', 'O') as $col) {
        $sheet->getStyle($col . '1')->applyFromArray($styles['header']);
    }
    $sheet->getRowDimension(1)->setRowHeight(24);

    // 5. CONFIGURACIÓN DE FILA 2 (Subcabeceras de Desglose - Sin Descripción de Causa)
    $subHeaders = array(
        'F2'=>'CASOS', 'G2'=>'CÓDIGO CIE-10',
        'H2'=>'CASOS', 'I2'=>'CÓDIGO CIE-10',
        'J2'=>'CASOS', 'K2'=>'CÓDIGO CIE-10',
        'L2'=>'CASOS', 'M2'=>'CÓDIGO CIE-10',
        'N2'=>'CASOS', 'O2'=>'CÓDIGO CIE-10'
    );
    foreach ($subHeaders as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['subheader']);
    }
    $sheet->getRowDimension(2)->setRowHeight(18);

    // 6. Volcado de Registros a las Celdas a partir de la Fila 3
    $f = 3;
    foreach ($perfil_data as $row) {
        $sheet->setCellValue('A' . $f, $row['dist_id']);
        $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('D' . $f, strtoupper($row['nombre_perfil']));
        $sheet->setCellValue('E' . $f, intval($row['tp_perfil']));

        // Datos Gestión 2021
        $sheet->setCellValue('F' . $f, intval($row['nro_casos_2021']));
        $sheet->setCellValue('G' . $f, strtoupper($row['codigo_cie_2021']));

        // Datos Gestión 2022
        $sheet->setCellValue('H' . $f, intval($row['nro_casos_2022']));
        $sheet->setCellValue('I' . $f, strtoupper($row['codigo_cie_2022']));

        // Datos Gestión 2023
        $sheet->setCellValue('J' . $f, intval($row['nro_casos_2023']));
        $sheet->setCellValue('K' . $f, strtoupper($row['codigo_cie_2023']));

        // Datos Gestión 2024
        $sheet->setCellValue('L' . $f, intval($row['nro_casos_2024']));
        $sheet->setCellValue('M' . $f, strtoupper($row['codigo_cie_2024']));

        // Datos Gestión 2025
        $sheet->setCellValue('N' . $f, intval($row['nro_casos_2025']));
        $sheet->setCellValue('O' . $f, strtoupper($row['codigo_cie_2025']));

        // Alineación centralizada para los metadatos de control (A-E)
        $sheet->getStyle('A'.$f.':E'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // REVISIÓN: Formateo numérico contable de millares aplicado solo a las celdas de conteo de Casos
        $columnas_casos = array('F', 'H', 'J', 'L', 'N');
        foreach ($columnas_casos as $c) {
            $sheet->getStyle($c . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($c . $f)->getNumberFormat()->setFormatCode('#,##0');
        }

        // REVISIÓN: Alineación a la izquierda para los códigos y descripciones CIE-10 (G, I, K, M, O)
        $columnas_cie = array('G', 'I', 'K', 'M', 'O');
        foreach ($columnas_cie as $c) {
            $sheet->getStyle($c . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        }

        // Aplicación masiva de bordes a la fila ajustada (De la A a la O)
        $sheet->getStyle('A'.$f.':O'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 7. REVISIÓN: Autoajuste dinámico limitado estrictamente desde la A hasta la O
    foreach (range('A', 'O') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_infraestructura(&$objPHPExcel, $dist_id, $styles) {
      $infra_data = $this->model_diagnosticopei->get_infraestructura_por_nivel_consolidado();

      // 3. Inicialización física de la Quinta Hoja del libro Excel
      $objPHPExcel->createSheet();
      $objPHPExcel->setActiveSheetIndex(4); // Índice 4 representa la quinta pestaña
      $sheet = $objPHPExcel->getActiveSheet();
      $sheet->setTitle('Infraestructura de Salud');

      // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plano de la A a la M)
      $headers = array(
          'A1' => 'ID DIST', 
          'B1' => 'REGIONAL / DISTRITAL', 
          'C1' => 'ABREV', 
          'D1' => 'GESTIÓN', 
          'E1' => 'CÓD. ACT', 
          'F1' => 'ESTABLECIMIENTO DE SALUD', 
          'G1' => 'TIPO', 
          'H1' => 'NIVEL', 
          'I1' => 'DIRECCIÓN / UBICACIÓN', 
          'J1' => 'Nº CONSULTORIOS', 
          'K1' => 'CONECTIVIDAD INTERNET', 
          'L1' => 'SITUACIÓN TÉCNICO LEGAL',
          'M1' => 'ORIGEN REGISTRO'
      );
      
      foreach ($headers as $pos => $text) {
          $sheet->setCellValue($pos, $text);
          $sheet->getStyle($pos)->applyFromArray($styles['header']); 
      }
      $sheet->getRowDimension(1)->setRowHeight(25); // Alto de fila corporativo

      // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
      $f = 2;
      foreach ($infra_data as $row) {
          $sheet->setCellValue('A' . $f, $row['dist_id']);
          $sheet->setCellValue('B' . $f, strtoupper($row['dist_distrital']));
          $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
          $sheet->setCellValue('D' . $f, $row['gestion_pei']);
          
          // Si es un centro manual no alineado, mostramos "---" en lugar de código 0
          $sheet->setCellValue('E' . $f, ($row['act_id'] > 0) ? $row['act_id'] : '---');
          $sheet->setCellValue('F' . $f, strtoupper($row['nombre_establecimiento']));
          $sheet->setCellValue('G' . $f, strtoupper($row['tipo_establecimiento']));
          
          // Formateo del nivel de atención
          $nivel_txt = (intval($row['nivel_establecimiento']) > 0) ? intval($row['nivel_establecimiento']) . '° NIVEL' : 'NO ASIGNADO';
          $sheet->setCellValue('H' . $f, $nivel_txt);
          
          $sheet->setCellValue('I' . $f, strtoupper($row['ubicacion']));
          $sheet->setCellValue('J' . $f, intval($row['nro_consultorios']));

          // === DECODIFICACIÓN INTERACTIVA DE CONECTIVIDAD INTERNET ===
          $internet_val = trim($row['serv_internet']);
          $internet_txt = 'SIN REGISTRO';
          if ($internet_val === '1' || strtolower($internet_val) === 'si') { $internet_txt = 'SÍ'; }
          elseif ($internet_val === '0' || strtolower($internet_val) === 'no') { $internet_txt = 'NO'; }
          $sheet->setCellValue('K' . $f, $internet_txt);

          // === DECODIFICACIÓN INTERACTIVA DE SITUACIÓN LEGAL ===
          $legal_val = trim($row['tipo_situacion']);
          $legal_txt = 'SIN REGISTRO';
          if ($legal_val === '1' || strtolower($legal_val) === 'propia') { $legal_txt = 'PROPIA (CNS)'; }
          elseif ($legal_val === '2' || strtolower($legal_val) === 'alquilado') { $legal_txt = 'ALQUILADO'; }
          elseif ($legal_val === '3' || strtolower($legal_val) === 'otros') { $legal_txt = 'COMODATO / OTROS'; }
          $sheet->setCellValue('L' . $f, $legal_txt);

          // Clasificación de la procedencia del registro
          $sheet->setCellValue('M' . $f, strtoupper($row['descripcion_infra']));

          // --- APLICACIÓN DE ALINEACIONES ESPECÍFICAS Y MAQUETACIÓN ---
          // Centrado para códigos, gestiones, niveles y conectividad (A-E, G-H, K-M)
          $sheet->getStyle('A'.$f.':E'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('G'.$f.':H'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $sheet->getStyle('K'.$f.':M'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          
          // Alineación a la derecha y formato de millares para número de consultorios (Columna J)
          $sheet->getStyle('J' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
          $sheet->getStyle('J' . $f)->getNumberFormat()->setFormatCode('#,##0');

          // Alineación a la izquierda con sangría para nombres largos y direcciones (F, I)
          $sheet->getStyle('F' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $sheet->getStyle('I' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

          // Aplicamos la cuadrícula de datos e incrementamos la fila
          $sheet->getStyle('A'.$f.':M'.$f)->applyFromArray($styles['data']);
          $sheet->getRowDimension($f)->setRowHeight(18);
          $f++;
      }

      // 6. Autoajuste dinámico de ancho elástico limitado de la A a la M (Evita el error ###)
      foreach (range('A', 'M') as $col) {
          $sheet->getColumnDimension($col)->setAutoSize(true);
      }
  }



  private function _generar_pestaña_camas(&$objPHPExcel, $dist_id, $styles) {
    $camas_data = $this->model_diagnosticopei->get_diagnostico_camas_consolidado();

    // 2. Inicialización física de la Sexta Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(5); // Índice 5 representa la sexta pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Diagnóstico de Camas');

    // 3. CONFIGURACIÓN DE FILA 1 (Encabezados Superiores Combinados Fijos)
    $sheet->mergeCells('A1:A2')->setCellValue('A1', 'ID DIST');
    $sheet->mergeCells('B1:B2')->setCellValue('B1', 'REGIONAL / DISTRITAL');
    $sheet->mergeCells('C1:C2')->setCellValue('C1', 'ABREV');
    $sheet->mergeCells('D1:D2')->setCellValue('D1', 'CÓD. ACT');
    $sheet->mergeCells('E1:E2')->setCellValue('E1', 'ESTABLECIMIENTO HOSPITALARIO');
    $sheet->mergeCells('F1:F2')->setCellValue('F1', 'NIVEL ATENCIÓN');

    // Combinación horizontal elástica de bloques anuales (4 celdas por año)
    $sheet->mergeCells('G1:J1')->setCellValue('G1', 'GESTIÓN 2021');
    $sheet->mergeCells('K1:N1')->setCellValue('K1', 'GESTIÓN 2022');
    $sheet->mergeCells('O1:R1')->setCellValue('O1', 'GESTIÓN 2023');
    $sheet->mergeCells('S1:V1')->setCellValue('S1', 'GESTIÓN 2024');
    $sheet->mergeCells('W1:Z1')->setCellValue('W1', 'GESTIÓN 2025');

    // Aplicar estilos a la primera fila macro (De la A a la Z)
    foreach (range('A', 'Z') as $col) {
        $sheet->getStyle($col . '1')->applyFromArray($styles['header']);
    }
    $sheet->getRowDimension(1)->setRowHeight(24);

    // 4. CONFIGURACIÓN DE FILA 2 (Subcabeceras Técnicas del Desglose Hospitalario)
    $subHeaders = array(
        'G2'=>'CAMAS', 'H2'=>'% OCUP.', 'I2'=>'EST. MEDIA', 'J2'=>'GIRO CAMA',
        'K2'=>'CAMAS', 'L2'=>'% OCUP.', 'M2'=>'EST. MEDIA', 'N2'=>'GIRO CAMA',
        'O2'=>'CAMAS', 'P2'=>'% OCUP.', 'Q2'=>'EST. MEDIA', 'R2'=>'GIRO CAMA',
        'S2'=>'CAMAS', 'T2'=>'% OCUP.', 'U2'=>'EST. MEDIA', 'V2'=>'GIRO CAMA',
        'W2'=>'CAMAS', 'X2'=>'% OCUP.', 'Y2'=>'EST. MEDIA', 'Z2'=>'GIRO CAMA'
    );
    foreach ($subHeaders as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['subheader']);
    }
    $sheet->getRowDimension(2)->setRowHeight(18);

    // 5. Volcado e Inyección de datos a partir de la Fila 3
    $f = 3;
    foreach ($camas_data as $row) {
        $sheet->setCellValue('A' . $f, $row['dist_id']);
        $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('D' . $f, $row['act_id']);
        $sheet->setCellValue('E' . $f, strtoupper($row['establecimiento'])); // Alineado a tu columna act_descripcion
        
        $nivel_txt = (intval($row['nivel']) > 0) ? intval($row['nivel']) . '° NIVEL' : 'NO ASIGNADO';
        $sheet->setCellValue('F' . $f, $nivel_txt);

        // --- MAPEO TRANSACCIONAL DE BLOQUES NUMÉRICOS POR GESTIÓN ---
        
        // Gestión 2021
        $sheet->setCellValue('G' . $f, intval($row['camas_2021']));
        $sheet->setCellValue('H' . $f, floatval($row['ocupacion_2021']));
        $sheet->setCellValue('I' . $f, floatval($row['estancia_2021']));
        $sheet->setCellValue('J' . $f, floatval($row['giro_2021']));

        // Gestión 2022
        $sheet->setCellValue('K' . $f, intval($row['camas_2022']));
        $sheet->setCellValue('L' . $f, floatval($row['ocupacion_2022']));
        $sheet->setCellValue('M' . $f, floatval($row['estancia_2022']));
        $sheet->setCellValue('N' . $f, floatval($row['giro_2022']));

        // Gestión 2023
        $sheet->setCellValue('O' . $f, intval($row['camas_2023']));
        $sheet->setCellValue('P' . $f, floatval($row['ocupacion_2023']));
        $sheet->setCellValue('Q' . $f, floatval($row['estancia_2023']));
        $sheet->setCellValue('R' . $f, floatval($row['giro_2023']));

        // Gestión 2024
        $sheet->setCellValue('S' . $f, intval($row['camas_2024']));
        $sheet->setCellValue('T' . $f, floatval($row['ocupacion_2024']));
        $sheet->setCellValue('U' . $f, floatval($row['estancia_2024']));
        $sheet->setCellValue('V' . $f, floatval($row['giro_2024']));

        // Gestión 2025
        $sheet->setCellValue('W' . $f, intval($row['camas_2025']));
        $sheet->setCellValue('X' . $f, floatval($row['ocupacion_2025']));
        $sheet->setCellValue('Y' . $f, floatval($row['estancia_2025']));
        $sheet->setCellValue('Z' . $f, floatval($row['giro_2025']));

        // Maquetación y alineaciones (A-F Centrado)
        $sheet->getStyle('A'.$f.':F'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Formateo y alineación a la derecha de todo el bloque analítico de datos (Columna G hasta la Z)
        $sheet->getStyle('G'.$f.':Z'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        
        // Configuración estructural de máscaras contables por bloque anual
        $columnas_año = array(
            array('int'=>'G', 'dec'=>array('H','I','J')), // 2021
            array('int'=>'K', 'dec'=>array('L','M','N')), // 2022
            array('int'=>'O', 'dec'=>array('P','Q','R')), // 2023
            array('int'=>'S', 'dec'=>array('T','U','V')), // 2024
            array('int'=>'W', 'dec'=>array('X','Y','Z'))  // 2025
        );
        
        foreach ($columnas_año as $bloque) {
            // Formato entero para número físico de Camas
            $sheet->getStyle($bloque['int'] . $f)->getNumberFormat()->setFormatCode('#,##0');
            // Formato flotante de dos decimales para indicadores de rendimiento clínico
            foreach ($bloque['dec'] as $col_dec) {
                $sheet->getStyle($col_dec . $f)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        // Aplicación estructural de bordes y altos
        $sheet->getStyle('A'.$f.':Z'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la Z
    foreach (range('A', 'Z') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_equipos(&$objPHPExcel, $dist_id, $styles) {
    // 1. Filtro dinámico si el administrador nacional selecciona una regional específica
    $equipos_data = $this->model_diagnosticopei->get_diagnostico_equipamiento_consolidado();

    // 3. Inicialización física de la Séptima Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(6); // Índice 6 representa la séptima pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Equipamiento Médico');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la K)
    $headers = array(
        'A1' => 'Nº',
        'B1' => 'ID DIST', 
        'C1' => 'REGIONAL / DISTRITAL', 
        'D1' => 'ABREV', 
        'E1' => 'GESTIÓN PEI',
        'F1' => 'CÓD. ACT',
        'G1' => 'ESTABLECIMIENTO DE SALUD',
        'H1' => 'SERVICIO / ÁREA',
        'I1' => 'DETALLE EQUIPO MAYOR',
        'J1' => 'PRECIO REFERENCIAL (Bs.)'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); // Alto de celda corporativo para títulos

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    foreach ($equipos_data as $row) {
        $sheet->setCellValue('A' . $f, intval($row['nro']));
        $sheet->setCellValue('B' . $f, $row['dist_id']);
        $sheet->setCellValue('C' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('D' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('E' . $f, $row['gestion_pei']);
        $sheet->setCellValue('F' . $f, $row['act_id']);
        $sheet->setCellValue('G' . $f, strtoupper($row['establecimiento_completo']));
        $sheet->setCellValue('H' . $f, strtoupper($row['servicio']));
        $sheet->setCellValue('I' . $f, strtoupper($row['detalle_equipo']));
        
        // Inyectamos como valor flotante nativo para permitir sumatorias en Excel
        $sheet->setCellValue('J' . $f, floatval($row['precio_referencial']));

        // --- APLICACIÓN DE ALINEACIONES ESPECÍFICAS Y MAQUETACIÓN ---
        // Centrado para números correlativos, códigos e identificadores fijos (A-B, D-F)
        $sheet->getStyle('A'.$f.':B'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$f.':F'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Alineación a la derecha con formato monetario para el Precio Referencial (Columna J)
        $sheet->getStyle('J' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('J' . $f)->getNumberFormat()->setFormatCode('#,##0.00');

        // Alineación a la izquierda con sangría para nombres de establecimientos, servicios y descripciones (C, G, H, I)
        $sheet->getStyle('C' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('I' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Aplicamos la cuadrícula de datos e incrementamos la fila
        $sheet->getStyle('A'.$f.':J'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la J (Evita el error ###)
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_rrhh(&$objPHPExcel, $dist_id, $styles) {
    $rrhh_data = $this->model_diagnosticopei->get_diagnostico_rrhh_consolidado();

    // 3. Inicialización física de la Octava Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(7); // Índice 7 representa la octava pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Recursos Humanos');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la W)
    $headers = array(
        'A1' => 'ID DIST', 'B1' => 'REGIONAL / DISTRITAL', 'C1' => 'ABREV', 'D1' => 'GESTIÓN',
        'E1' => 'CATEGORÍA RRHH', 'F1' => 'MÉDICOS', 'G1' => 'ODONTÓLOGOS', 'H1' => 'FARMACÉUTICOS',
        'I1' => 'LABORATORISTAS', 'J1' => 'OTROS PROFESIONALES', 'K1' => 'NUTRICIONISTAS', 'L1' => 'TRABAJO SOCIAL',
        'M1' => 'JEFE/SUPERV. ENFERMERÍA', 'N1' => 'LIC. GRAD. ENFERMERÍA', 'O1' => 'AUX. ENFERMERÍA', 'P1' => 'PERS. ADM. CENTRAL',
        'Q1' => 'PERS. ADM. SALUD', 'R1' => 'PERS. ADM. TÉCNICO', 'S1' => 'PERS. ADM. AUXILIAR', 'T1' => 'CHOFERES',
        'U1' => 'ARTESANOS', 'V1' => 'TRABAJADORES MANUALES', 'W1' => 'TOTAL PERSONAL'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); // Alto ejecutivo para cabeceras

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    foreach ($rrhh_data as $row) {
        $sheet->setCellValue('A' . $f, $row['dist_id']);
        $sheet->setCellValue('B' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('C' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('D' . $f, $row['gestion']);
        $sheet->setCellValue('E' . $f, strtoupper($row['categoria']));
        
        // Mapeo dinámico forzando enteros puros para habilitar sumas contables nativas
        $sheet->setCellValue('F' . $f, intval($row['nro_medicos']));
        $sheet->setCellValue('G' . $f, intval($row['nro_odontologos']));
        $sheet->setCellValue('H' . $f, intval($row['nro_farmaceuticos']));
        $sheet->setCellValue('I' . $f, intval($row['nro_laboratoristas']));
        $sheet->setCellValue('J' . $f, intval($row['nro_otros_prof']));
        $sheet->setCellValue('K' . $f, intval($row['nro_nutricionistas']));
        $sheet->setCellValue('L' . $f, intval($row['nro_trabajo_social']));
        $sheet->setCellValue('M' . $f, intval($row['nro_jefe_superv_enf']));
        $sheet->setCellValue('N' . $f, intval($row['nro_lic_grad_enf']));
        $sheet->setCellValue('O' . $f, intval($row['nro_aux_enf']));
        $sheet->setCellValue('P' . $f, intval($row['nro_pers_adm']));
        $sheet->setCellValue('Q' . $f, intval($row['nro_pers_adm_salud']));
        $sheet->setCellValue('R' . $f, intval($row['nro_pers_adm_tec']));
        $sheet->setCellValue('S' . $f, intval($row['nro_pers_adm_aux']));
        $sheet->setCellValue('T' . $f, intval($row['nro_pers_adm_chof']));
        $sheet->setCellValue('U' . $f, intval($row['nro_pers_adm_artesanos']));
        $sheet->setCellValue('V' . $f, intval($row['nro_pers_adm_trab_manual']));
        $sheet->setCellValue('W' . $f, intval($row['total']));

        // --- MAQUETACIÓN DE ALINEACIONES ---
        // Centrado para identificadores fijos, gestiones y categorías base (A-D)
        $sheet->getStyle('A'.$f.':D'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        
        // Alineación a la derecha y formato de millares para todas las columnas de personal (F-W)
        $sheet->getStyle('F'.$f.':W'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F'.$f.':W'.$f)->getNumberFormat()->setFormatCode('#,##0');

        // Aplicamos la cuadrícula de datos e incrementamos la fila operativa
        $sheet->getStyle('A'.$f.':W'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico (De la columna A hasta la W)
    foreach (range('A', 'W') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }



  private function _generar_pestaña_compra_servicios(&$objPHPExcel, $dist_id, $styles) {
    $compra_data = $this->model_diagnosticopei->get_diagnostico_compra_servicios_consolidado();

    // 3. Inicialización física de la Novena Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(8); // Índice 8 representa la novena pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Compra de Servicios');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la K)
    $headers = array(
        'A1' => 'Nº',
        'B1' => 'ID DIST', 
        'C1' => 'REGIONAL / DISTRITAL', 
        'D1' => 'ABREV', 
        'E1' => 'GESTIÓN',
        'F1' => 'POSICIÓN',
        'G1' => 'SERVICIO CONTRATADO',
        'H1' => 'Nº ATENCIONES',
        'I1' => 'COSTO TOTAL (Bs.)',
        'J1' => 'OBSERVACIONES'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); // Alto ejecutivo

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    $nro=0;
    foreach ($compra_data as $row) {
        $nro++;
        $sheet->setCellValue('A' . $f, intval($nro));
        $sheet->setCellValue('B' . $f, $row['dist_id']);
        $sheet->setCellValue('C' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('D' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('E' . $f, $row['gestion']);
        $sheet->setCellValue('F' . $f, intval($row['nro_fila']));
        $sheet->setCellValue('G' . $f, strtoupper($row['serv_contratado']));
        
        // Conversión a tipos de datos nativos numéricos para habilitar fórmulas
        $sheet->setCellValue('H' . $f, intval($row['nro_atenciones']));
        $sheet->setCellValue('I' . $f, floatval($row['costo_total']));
        $sheet->setCellValue('J' . $f, strtoupper($row['cservicios_observaciones']));

        // --- MAQUETACIÓN DE ALINEACIONES ---
        // Centrado para números correlativos, llaves, gestiones y posiciones (A-B, D-F)
        $sheet->getStyle('A'.$f.':B'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$f.':F'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Alineación a la derecha y máscaras contables para el bloque financiero (H, I)
        $sheet->getStyle('H' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H' . $f)->getNumberFormat()->setFormatCode('#,##0');
        
        $sheet->getStyle('I' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('I' . $f)->getNumberFormat()->setFormatCode('#,##0.00');

        // Alineación a la izquierda para campos de texto descriptivos (C, G, J)
        $sheet->getStyle('C' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('J' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Aplicamos la cuadrícula de datos e incrementamos la fila operativa
        $sheet->getStyle('A'.$f.':J'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la J
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }



  private function _generar_pestaña_presupuestos(&$objPHPExcel, $dist_id, $styles) {
    $presupuesto_data = $this->model_diagnosticopei->get_diagnostico_presupuestos_consolidado();

    // 3. Inicialización física de la Décima Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(9); // Índice 9 representa la décima pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Presupuestos');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la M)
    $headers = array(
        'A1' => 'Nº',
        'B1' => 'ID DIST', 
        'C1' => 'REGIONAL / DISTRITAL', 
        'D1' => 'ABREV', 
        'E1' => 'GESTIÓN',
        'F1' => 'ING. PROPIOS PROG.',
        'G1' => 'ING. PROPIOS EJEC.',
        'H1' => 'REC. FINANC. PROG.',
        'I1' => 'REC. FINANC. EJEC.',
        'J1' => 'TOTAL ING. EJEC.',
        'K1' => 'GASTOS PROG.',
        'L1' => 'GASTOS EJEC.',
        'M1' => 'RDO. DÉFICIT/SUPERÁVIT'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); // Alto ejecutivo para cabeceras

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    $nro=0;
    foreach ($presupuesto_data as $row) {
      $nro++;
        $sheet->setCellValue('A' . $f, intval($nro));
        $sheet->setCellValue('B' . $f, $row['dist_id']);
        $sheet->setCellValue('C' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('D' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('E' . $f, $row['gestion']);
        
        // Conversión estricta a flotantes nativos para permitir balances y sumas directas
        $sheet->setCellValue('F' . $f, floatval($row['ingresos_propios_programados']));
        $sheet->setCellValue('G' . $f, floatval($row['ingresos_propios_ejecutados']));
        $sheet->setCellValue('H' . $f, floatval($row['recursos_financieros_programados']));
        $sheet->setCellValue('I' . $f, floatval($row['recursos_financieros_ejecutados']));
        $sheet->setCellValue('J' . $f, floatval($row['total_ingresos_ejecutados']));
        $sheet->setCellValue('K' . $f, floatval($row['gastos_programados']));
        $sheet->setCellValue('L' . $f, floatval($row['gastos_ejecutados']));
        $sheet->setCellValue('M' . $f, floatval($row['deficit_superavit']));

        // --- MAQUETACIÓN DE ALINEACIONES ---
        // Centrado para números correlativos, llaves y gestiones (A-B, D-E)
        $sheet->getStyle('A'.$f.':B'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$f.':E'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Alineación a la izquierda para campos de texto (C)
        $sheet->getStyle('C' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Alineación a la derecha y formato monetario con dos decimales para todo el bloque presupuestario (F a M)
        $sheet->getStyle('F'.$f.':M'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F'.$f.':M'.$f)->getNumberFormat()->setFormatCode('#,##0.00');

        // Aplicamos la cuadrícula de datos e incrementamos la fila operativa
        $sheet->getStyle('A'.$f.':M'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la M
    foreach (range('A', 'M') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_reembolsos(&$objPHPExcel, $dist_id, $styles) {
    $reembolsos_data = $this->model_diagnosticopei->get_diagnostico_reembolsos_consolidado();

    // 3. Inicialización física de la Undécima Hoja del libro Excel
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(10); // Índice 10 representa la undécima pestaña
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Reembolsos');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la J)
    $headers = array(
        'A1' => 'Nº',
        'B1' => 'ID DIST', 
        'C1' => 'REGIONAL / DISTRITAL', 
        'D1' => 'ABREV', 
        'E1' => 'GESTIÓN',
        'F1' => 'REEMB. MEDICAMENTOS',
        'G1' => 'REEMB. LABORATORIO',
        'H1' => 'REEMB. IMAGENOLOGÍA',
        'I1' => 'REEMB. OTROS CONCEPTOS',
        'J1' => 'TOTAL REEMBOLSOS'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); // Alto de celda corporativo para títulos

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    $nro=0;
    foreach ($reembolsos_data as $row) {
      $nro++;
        $sheet->setCellValue('A' . $f, intval($nro));
        $sheet->setCellValue('B' . $f, $row['dist_id']);
        $sheet->setCellValue('C' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('D' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('E' . $f, $row['gestion']);
        
        // Conversión a flotantes numéricos puros para permitir sumatorias directas en Excel
        $sheet->setCellValue('F' . $f, floatval($row['reemb_concep_medicamentos']));
        $sheet->setCellValue('G' . $f, floatval($row['reemb_concep_laboratorio']));
        $sheet->setCellValue('H' . $f, floatval($row['reemb_concep_imagenologia']));
        $sheet->setCellValue('I' . $f, floatval($row['reemb_otros_conceptos']));
        $sheet->setCellValue('J' . $f, floatval($row['total_reembolsos']));

        // --- MAQUETACIÓN DE ALINEACIONES ---
        // Centrado para números correlativos, llaves y gestiones (A-B, D-E)
        $sheet->getStyle('A'.$f.':B'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$f.':E'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Alineación a la izquierda para campos de texto descriptivos (C)
        $sheet->getStyle('C' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Alineación a la derecha y formato de millares con dos decimales para el bloque financiero (F a J)
        $sheet->getStyle('F'.$f.':J'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('F'.$f.':J'.$f)->getNumberFormat()->setFormatCode('#,##0.00');

        // Aplicamos la cuadrícula de datos e incrementamos la fila operativa
        $sheet->getStyle('A'.$f.':J'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la J (Evita el error ###)
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }


  private function _generar_pestaña_ambulancias(&$objPHPExcel, $dist_id, $styles) {
    $ambulancias_data = $this->model_diagnosticopei->get_detalle_ambulancias_consolidado();

    // 3. Inicialización física de la Duodécima Hoja del libro Excel (Índice 11)
    $objPHPExcel->createSheet();
    $objPHPExcel->setActiveSheetIndex(11); 
    $sheet = $objPHPExcel->getActiveSheet();
    $sheet->setTitle('Inventario Ambulancias');

    // 4. Configuración y Estilizado de las Cabeceras (Fila 1 Plana de la A a la I)
    $headers = array(
        'A1' => 'Nº',
        'B1' => 'ID DIST', 
        'C1' => 'REGIONAL / DISTRITAL', 
        'D1' => 'ABREV', 
        'E1' => 'NRO. PLACA',
        'F1' => 'AÑO ADJUDICACIÓN',
        'G1' => 'ESTADO CONSERVACIÓN',
        'H1' => 'SITUACIÓN LEGAL',
        'I1' => 'ESTABLECIMIENTO ASIGNADO'
    );
    
    foreach ($headers as $pos => $text) {
        $sheet->setCellValue($pos, $text);
        $sheet->getStyle($pos)->applyFromArray($styles['header']); 
    }
    $sheet->getRowDimension(1)->setRowHeight(25); 

    // 5. Volcado e Inyección de datos a las celdas (A partir de la fila 2)
    $f = 2;
    $nro=0;
    foreach ($ambulancias_data as $row) {
      $nro++;
        $sheet->setCellValue('A' . $f, intval($nro));
        $sheet->setCellValue('B' . $f, $row['dist_id']);
        $sheet->setCellValue('C' . $f, strtoupper($row['regional']));
        $sheet->setCellValue('D' . $f, strtoupper($row['abreviatura']));
        $sheet->setCellValue('E' . $f, strtoupper($row['placa']));
        
        // El año de adjudicación viaja como entero numérico puro
        $sheet->setCellValue('F' . $f, ($row['anio_adjudicacion'] > 0) ? intval($row['anio_adjudicacion']) : '---');
        $sheet->setCellValue('G' . $f, strtoupper($row['estado_ambulancia']));
        $sheet->setCellValue('H' . $f, strtoupper($row['situacion_ambulancia']));
        $sheet->setCellValue('I' . $f, strtoupper($row['establecimiento']));

        // --- MAQUETACIÓN DE ALINEACIONES ---
        // Centrado para identificadores, correlativos, abreviaturas y placas (A-B, D-F)
        $sheet->getStyle('A'.$f.':B'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.$f.':F'.$f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        
        // Alineación a la izquierda para campos de texto descriptivos (C, G, H, I)
        $sheet->getStyle('C' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('G' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('H' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('I' . $f)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        // Aplicamos la cuadrícula de datos e incrementamos la fila operativa
        $sheet->getStyle('A'.$f.':I'.$f)->applyFromArray($styles['data']);
        $sheet->getRowDimension($f)->setRowHeight(18);
        $f++;
    }

    // 6. Autoajuste dinámico de ancho elástico limitado de la A a la I (Evita el error ###)
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
  }




  /// Buscador select CIe10
  public function buscar_cie10_ajax() {
      $search = $this->input->get('q'); // Palabra buscada
      $this->db->select('id, cod_3, descripcion');
      $this->db->from('tabla_cie10');
      $this->db->like('cod_3', $search);
      $this->db->or_like('descripcion', $search);
      $this->db->limit(20); // Limitamos a 20 resultados para que sea veloz
      $query = $this->db->get();

      $resultados = array();
      foreach ($query->result() as $row) {
          $resultados[] = array(
              'id'   => $row->id,
              'text' => $row->cod_3 . " - " . $row->descripcion
          );
      }
      echo json_encode($resultados);
  }


  //// Guarda Observacion al formulario
  function guarda_observacion() {
      $fid = $this->input->post('form_id');
      $nro = $this->input->post('nro');
      $txt = $this->input->post('observacion');

     // 1. Verificamos si ya existe un registro en la tabla de observaciones
     // Usamos el form_id como referencia
      $this->db->where('form_id', $fid);
      $this->db->where('obs_nro', $nro);
      $query = $this->db->get('form_observacion');

      $data = array(
          'form_id'       => $fid,
          'obs_nro'       => $nro, // Nota: Asegúrate de que obs_nro deba ser igual al form_id
          'obs_contenido' => $txt
      );

      if ($query->num_rows() > 0) {
          // 2. Si el registro YA EXISTE en la tabla, actualizamos
          $this->db->where('form_id', $fid);
          $this->db->where('obs_nro', $nro);
          $this->db->update('form_observacion', $data);
          echo "updated";
      } else {
          // 3. Si NO EXISTE, insertamos
          $this->db->insert('form_observacion', $data);
          echo "inserted";
      }
  }


    //// Guarda informacion de las tablas automaticamente form 1
public function guarda_detalle_automatica_form1() {
    // Seguridad para solo aceptar AJAX
    if (!$this->input->is_ajax_request()) return;

    $form_id = $this->input->post('form_id');
    $gestion = $this->input->post('gestion');
    $columna = $this->input->post('columna');
    $valor   = $this->input->post('valor');

    // Validación de columnas
    $columnas_permitidas = array('nro_cot_tit', 'nro_cot_pas', 'nro_cot_ben');
    if (!in_array($columna, $columnas_permitidas)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Columna no permitida'));
        return;
    }

    // Asegurar que el valor sea numérico y no pase de 999,999,999
    $valor = (is_numeric($valor) && $valor >= 0) ? substr($valor, 0, 9) : 0;

    $where = array('form_id' => $form_id, 'g_id' => $gestion);
    $this->db->where($where);
    $existe = $this->db->get('formularion1_detalle')->num_rows();

    if ($existe > 0) {
        $this->db->where($where);
        $res = $this->db->update('formularion1_detalle', array($columna => $valor));
    } else {
        $res = $this->db->insert('formularion1_detalle', array(
            'form_id' => $form_id,
            'g_id'    => $gestion,
            $columna  => $valor
        ));
    }

    // Devolvemos el JSON que el script espera
    echo json_encode(array('status' => $res ? 'success' : 'error'));
}


    //// Guarda informacion de las tablas automaticamente form 1 Grupo Etareo
    public function guarda_detalle_automatica_form1_etareo() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $form_id = $this->input->post('form_id');
        $eta_id  = $this->input->post('eta_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = ($this->input->post('valor') == '' || $this->input->post('valor') < 0) ? 0 : $this->input->post('valor');

        // Iniciar transacción para evitar duplicidad por peticiones rápidas
        $this->db->trans_start();

        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion, 'eta_id' => $eta_id));
        $fila = $this->db->get('formularion1_grupo_etareo')->row();

        if ($fila) {
            $nro_mas = ($campo == 'nro_masculino') ? $valor : $fila->nro_masculino;
            $nro_fem = ($campo == 'nro_femenino') ? $valor : $fila->nro_femenino;

            if ($nro_mas == 0 && $nro_fem == 0) {
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $this->db->delete('formularion1_grupo_etareo');
                $msg = '🗑️ Registro eliminado';
            } else {
                $data_update = array(
                    $campo => $valor,
                    'total_poblacion' => ($nro_mas + $nro_fem)
                );
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $this->db->update('formularion1_grupo_etareo', $data_update);
                $msg = '✅ Actualizado';
            }
        } else {
            if ($valor > 0) {
                $data_insert = array(
                    'form_id' => $form_id,
                    'g_id'    => $gestion,
                    'eta_id'  => $eta_id,
                    $campo    => $valor,
                    'total_poblacion' => $valor 
                );
                $this->db->insert('formularion1_grupo_etareo', $data_insert);
                $msg = '✅ Guardado';
            } else {
                $this->db->trans_complete();
                echo json_encode(array('status' => 'success', 'msg' => 'Nada que guardar'));
                return;
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'error', 'msg' => 'Error de concurrencia en la BD'));
        } else {
            echo json_encode(array('status' => 'success', 'msg' => $msg));
        }
    }
    
  //// Guarda informacion de las tablas automaticamente form2
public function guarda_detalle_automatica_form2() {
    $form_id = $this->input->post('form_id');
    $gestion = $this->input->post('gestion');
    $columna = $this->input->post('columna');
    $valor   = $this->input->post('valor');

    // 1. Columnas específicas para el Formulario 2
    $columnas_permitidas = array('nro_empresas_reg', 'nro_aportes_dia', 'nro_empresa_mora');
    if (!in_array($columna, $columnas_permitidas)) return;

    // 2. Definición del filtro
    $where = array('form_id' => $form_id, 'g_id' => $gestion);
    
    // 3. Verificamos existencia en la tabla del Formulario 2
    $existe = $this->db->get_where('formularion2_detalle', $where)->num_rows();

    if ($existe > 0) {
        // ACTUALIZACIÓN
        $this->db->where($where);
        $this->db->update('formularion2_detalle', array($columna => $valor));
    } else {
        // INSERCIÓN
        $this->db->insert('formularion2_detalle', array(
            'form_id' => $form_id,
            'g_id'    => $gestion,
            $columna  => $valor
        ));
    }

    echo "ok"; // Esto garantiza que el AJAX reciba el 'success' y no se quede cargando
}


  //// Guarda informacion de las tablas automaticamente form3
    public function guarda_detalle_automatica_form3() {
        // 1. Recibir datos por POST
        $form_id  = $this->input->post('form_id');
        $gestion  = $this->input->post('gestion');
        $posicion = $this->input->post('nro_posicion'); // 1 al 10
        $cat      = $this->input->post('categoria');    // tp perfil 1: Morbilidad, 2: Mortalidad
        $columna  = $this->input->post('columna');     // nro_casos, ce_id o detalle_causa
        $valor    = $this->input->post('valor');

        // 2. Validación básica de seguridad
        if (!$form_id || !$gestion || !$posicion) {
            echo "error_datos_incompletos";
            return;
        }

        // 3. Validar que la columna sea permitida para evitar inyecciones
        $columnas_permitidas = array('nro_casos', 'ce_id', 'detalle_causa', 'tipo_perfil_cat');
        if (!in_array($columna, $columnas_permitidas)) {
            echo "error_columna_no_permitida";
            return;
        }

        // 4. Preparar el array para el modelo
        $params = array(
            'form_id'    => (int)$form_id,
            'g_id'       => (int)$gestion,
            'posicion'   => (int)$posicion,
            'categoria'  => (int)$cat,
            'columna'    => $columna,
            'valor'      => $valor
        );

        // 5. Llamar al modelo para procesar el Upsert
        $resultado = $this->upsert_detalle_perfil($params);

        if ($resultado) {
            echo "success";
        } else {
            echo "error_en_db";
        }

    }

    /// update form3
    public function upsert_detalle_perfil($d) {
        // 1. BUSCAR EL ID DE LA TABLA MAESTRA (formularion3_detalle_perfil)
        // Necesitamos el det3_id para poder guardar en la tabla de detalles
        $this->db->where('form_id', $d['form_id']);
        $this->db->where('g_id', $d['g_id']);
        $query_master = $this->db->get('formularion3_detalle_perfil');

        if ($query_master->num_rows() > 0) {
            $master = $query_master->row();
            $det3_id = $master->det3_id;
        } else {
            // Si por alguna razón no existe el maestro para ese año, lo creamos
            $data_master = array(
                'form_id' => $d['form_id'],
                'g_id'    => $d['g_id'],
                'nro_causas' => 10
            );
            $this->db->insert('formularion3_detalle_perfil', $data_master);
            $det3_id = $this->db->insert_id();
        }

        // 2. BUSCAR SI YA EXISTE EL DETALLE ESPECÍFICO
        // Filtramos por el ID maestro, la posición (1-10) y la categoría (Morbilidad/Mortalidad)
        $this->db->where('det3_id', $det3_id);
        $this->db->where('tp_perfil', $d['posicion']);
        $this->db->where('tipo_perfil_cat', $d['categoria']);
        $query_detail = $this->db->get('detalle_form3_perfil');

        // Preparamos los datos a guardar
        $data_save = array(
            $d['columna'] => $d['valor'] // Ej: nro_casos => 50
        );

        if ($query_detail->num_rows() > 0) {
            // 3. ACTUALIZAR
            $this->db->where('det3_id', $det3_id);
            $this->db->where('tp_perfil', $d['posicion']);
            $this->db->where('tipo_perfil_cat', $d['categoria']);
            return $this->db->update('detalle_form3_perfil', $data_save);
        } else {
            // 4. INSERTAR
            $data_save['det3_id'] = $det3_id;
            $data_save['tp_perfil'] = $d['posicion'];
            $data_save['tipo_perfil_cat'] = $d['categoria'];
            return $this->db->insert('detalle_form3_perfil', $data_save);
        }
    }


    //// Validacion form 4 - Establecimientos inscritos en el poa
    public function guarda_detalle_infraestructura_form4() {
        if (!$this->input->is_ajax_request()) { show_404(); return; }

        $form_id = $this->input->post('form_id');
        $act_id  = $this->input->post('act_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = $this->input->post('valor');

        if ($campo == 'nro_consultorios') {
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // 1. Iniciar Transacción para evitar que dos procesos escriban al mismo tiempo
        $this->db->trans_start();

        // 2. Asegurar Cabecera
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();

        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            $this->db->insert('formularion4_detalle_infra', array(
                'form_id' => $form_id,
                'g_id' => $gestion,
                'form4_estado' => 1
            ));
            $det4_id = $this->db->insert_id();
        }

        // 3. VERIFICACIÓN CRÍTICA: ¿Existe ya este act_id para este det4_id?
        // Usamos select(1) para rapidez
        $this->db->where(array('det4_id' => $det4_id, 'act_id' => $act_id));
        $existe = $this->db->get('infraestructura_form4')->row();

        if ($existe) {
            // ACTUALIZACIÓN ESTRICTA por ID primario
            $this->db->where('infra_id', $existe->infra_id);
            $res = $this->db->update('infraestructura_form4', array($campo => $valor));
        } else {
            // INSERCIÓN: Solo si el valor no es vacío o cero (opcional, para no llenar basura)
            $data_insert = array(
                'det4_id'  => $det4_id,
                'act_id'   => $act_id,
                'tp_infra' => 1, 
                $campo     => $valor
            );
            $res = $this->db->insert('infraestructura_form4', $data_insert);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'error', 'msg' => '❌ Error de concurrencia'));
        } else {
            echo json_encode(array('status' => 'success', 'msg' => '✅ Guardado correctamente ..'));
        }
    }



    //// agregamos nueva fila para otros establecimientos
    public function nuevo_infra_otro() {
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');

        // 1. Asegurar det4_id (Cabecera)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();
        
        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            $this->db->insert('formularion4_detalle_infra', array('form_id' => $form_id, 'g_id' => $gestion, 'form4_estado' => 1));
            $det4_id = $this->db->insert_id();
        }

        // 2. Insertar registro en blanco en la tabla de Otros
        $res = $this->db->insert('infraestructura_otros_form4', array(
            'det4_id' => $det4_id,
            'tp_infra' => 0,
            'nro_consultorios' => 0
        ));
        $nuevo_id = $this->db->insert_id();

        echo json_encode(array('status' => $res ? 'success' : 'error', 'id' => $nuevo_id));
    }

    //// guarda informacion de otros establecimientos
    public function guarda_infra_otros_automatica() {
        // 1. Verificación de seguridad para peticiones AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Recepción de parámetros desde el JS
        $infra_otro_id = $this->input->post('id');      // ID primario de la tabla otros
        $form_id       = $this->input->post('form_id'); // ID del formulario diagnóstico
        $gestion       = $this->input->post('gestion'); // Año (ej. 2025)
        $campo         = $this->input->post('campo');   // Columna a modificar
        $valor         = $this->input->post('valor');   // Valor ingresado

        // 3. Validación de Negativos para campos numéricos
        if ($campo == 'nro_consultorios') {
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // 4. ASEGURAR CABECERA (formularion4_detalle_infra)
        // Buscamos si ya existe el vínculo con el diagnóstico pei
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();

        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            // Si no existe (caso de fila nueva), creamos la cabecera automáticamente
            $data_cabecera = array(
                'form_id'      => $form_id,
                'g_id'         => $gestion,
                'form4_estado' => 1
            );
            $this->db->insert('formularion4_detalle_infra', $data_cabecera);
            $det4_id = $this->db->insert_id();
        }

        // 5. ACTUALIZAR EL REGISTRO DE "OTROS"
        // Aseguramos que el det4_id esté actualizado por si se acaba de crear
        $data_update = array(
            'det4_id' => $det4_id,
            $campo    => $valor
        );

        $this->db->where('infra_otro_id', $infra_otro_id);
        $res = $this->db->update('infraestructura_otros_form4', $data_update);

        // 6. Respuesta JSON para el Script
        if ($res) {
            echo json_encode(array('status' => 'success', 'msg' => '✅ Informacion guardado correctamente ...'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => '❌ Error al actualizar registro'));
        }
    }

    public function eliminar_infra_otro() {
        if (!$this->input->is_ajax_request()) return;

        $id = $this->input->post('id');

        if ($id) {
            $this->db->where('infra_otro_id', $id);
            $res = $this->db->delete('infraestructura_otros_form4');
            
            echo json_encode(array('status' => $res ? 'success' : 'error'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'ID no válido'));
        }
    }


    ///// Guarda formulario 5
    public function guarda_produccion_cama_automatica() {
        // // 1. Verificación de seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // // 2. Recepción de parámetros
        $form_id = $this->input->post('form_id');
        $act_id  = $this->input->post('act_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = $this->input->post('valor');

        // // 3. Validación de datos en el servidor
        //  // VALIDACIÓN EN EL SERVIDOR
        if ($campo == 'ocupacion') {
            if (!is_numeric($valor) || $valor < 0) {
                $valor = 0;
            } elseif ($valor > 100) {
                $valor = 100; // Truncamos al máximo permitido
            }
        } else {
            // Para los otros campos numéricos (camas, estancia, giro)
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // // 4. ASEGURAR CABECERA (formularion5_produccion_cama)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion5_produccion_cama')->row();

        if ($cabecera) {
            $det5_id = $cabecera->det5_id;
        } else {
            // Si no existe para esa gestión, la creamos
            $this->db->insert('formularion5_produccion_cama', array(
                'form_id' => $form_id,
                'g_id'    => $gestion,
                'form5_estado' => 1
            ));
            $det5_id = $this->db->insert_id();
        }

        // // 5. GUARDADO DEL DETALLE (Upsert eficiente)
        // Intentamos actualizar primero
        $this->db->where(array('det5_id' => $det5_id, 'act_id' => $act_id));
        $this->db->update('detalle_form5_produccion_cama', array($campo => $valor));

        // Si no se afectó ninguna fila, es que el registro no existe
        if ($this->db->affected_rows() == 0) {
            // Verificamos si realmente no existe (por si el valor era el mismo)
            $this->db->where(array('det5_id' => $det5_id, 'act_id' => $act_id));
            $check = $this->db->get('detalle_form5_produccion_cama')->num_rows();

            if ($check == 0) {
                $data_insert = array(
                    'det5_id' => $det5_id,
                    'act_id'  => $act_id,
                    $campo    => $valor
                );
                $res = $this->db->insert('detalle_form5_produccion_cama', $data_insert);
            } else {
                $res = true; // El registro existía pero el valor enviado era igual al actual
            }
        } else {
            $res = true; // Update exitoso
        }

        // 6. Respuesta para el script
        echo json_encode(array('status' => $res ? 'success' : 'error'));
    }

    /// valida formulario 6 equipos
    public function crear_equipo_completo() {
        if (ob_get_length()) ob_clean(); // Limpieza de salida
        if (!$this->input->is_ajax_request()) { show_404(); return; }

        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $act_id  = $this->input->post('act_id');
        $servicio = $this->input->post('servicio');
        $detalle  = $this->input->post('detalle');
        $precio   = $this->input->post('precio');

        // Validación de seguridad
        $precio = (is_numeric($precio) && $precio >= 0) ? $precio : 0;

        // 1. Asegurar Cabecera
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion6_equipos')->row();
        $det6_id = ($cabecera) ? $cabecera->det6_id : 0;

        if (!$det6_id) {
            $this->db->insert('formularion6_equipos', array(
                'form_id' => $form_id,
                'g_id' => $gestion,
                'form6_estado' => 1
            ));
            $det6_id = $this->db->insert_id();
        }

        // 2. Insertar Detalle con nombre de columna corregido
        $data_detalle = array(
            'det6_id'           => $det6_id,
            'act_id'            => $act_id,
            'servicio'          => strtoupper(trim($servicio)),
            'detalle_equipo'    => strtoupper(trim($detalle)),
            'precio_referencial' => $precio // <--- SINCRONIZADO CON TU SQL
        );

        $res = $this->db->insert('detalle_form6_equipos', $data_detalle);
        $nuevo_id = $this->db->insert_id();

        echo json_encode(array(
            'status' => $res ? 'success' : 'error', 
            'id' => $nuevo_id
        ));
    }

    //// update equipo
    public function guarda_detalle_equipo_form6() {
        // 1. Limpieza de salida para evitar que espacios o errores previos rompan el JSON
        if (ob_get_length()) ob_clean();

        // 2. Verificación de seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 3. Recepción de parámetros (det6_form6_id, columna, valor)
        $id    = $this->input->post('id');
        $campo = $this->input->post('campo');
        $valor = $this->input->post('valor');

        // 4. Validación rápida por tipo de columna
        if ($campo == 'precio_referencial') {
            // Aseguramos que sea un número positivo
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        } else {
            // Para 'servicio' y 'detalle_equipo', limpiamos y pasamos a mayúsculas
            $valor = strtoupper(trim($valor));
        }

        // 5. UPDATE DIRECTO (Es más rápido que buscar con un IF/SELECT previo)
        $this->db->where('det6_form6_id', $id);
        $res = $this->db->update('detalle_form6_equipos', array($campo => $valor));

        // 6. Respuesta JSON veloz
        if ($res) {
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'Error de actualización'));
        }
    }

    public function eliminar_equipo_form6() {
        // 1. Verificación de seguridad para peticiones AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Limpieza de cualquier salida previa para evitar errores de JSON
        if (ob_get_length()) ob_clean();

        // 3. Recepción del ID del detalle
        $id = $this->input->post('id'); // det6_form6_id

        if ($id) {
            // 4. Ejecución del borrado
            $this->db->where('det6_form6_id', $id);
            $res = $this->db->delete('detalle_form6_equipos');

            if ($res) {
                // Éxito: el JS ejecutará el .fadeOut() de la fila
                echo json_encode(array('status' => 'success'));
            } else {
                echo json_encode(array('status' => 'error', 'msg' => 'No se pudo eliminar el registro de la base de datos.'));
            }
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'ID de registro no válido.'));
        }
    }
    /////////////////////////////////////////////////

    //// Guarda formulario Recursos Humanos
    public function guarda_rrhh_automatica() {
        // 1. Limpieza de salida para evitar errores de JSON
        if (ob_get_length()) ob_clean();

        // 2. Seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 3. Parámetros recibidos del JS
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $tp_rrhh = $this->input->post('tp_rrhh'); // 1:item, 2:contrato, 3:acefalia
        $campo   = $this->input->post('campo');
        $valor   = intval($this->input->post('valor'));

        // 4. ASEGURAR CABECERA (formularion7_rrhh)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion7_rrhh')->row();

        if ($cabecera) {
            $det7_id = $cabecera->det7_id;
        } else {
            // Crear cabecera si no existe para ese año
            $this->db->insert('formularion7_rrhh', array(
                'form_id' => $form_id,
                'g_id'    => $gestion,
                'form7_estado' => 1
            ));
            $det7_id = $this->db->insert_id();
        }

        // 5. GUARDADO DEL DETALLE (Upsert)
        // Intentamos actualizar la fila que coincida con la cabecera y el tipo (Item/Contr/Acef)
        $this->db->where(array('det7_id' => $det7_id, 'tp_rrhh_form' => $tp_rrhh));
        $this->db->update('detalle_form7_rrhh', array($campo => $valor));

        // Si no se afectó ninguna fila, el registro para ese tipo no existe, lo creamos
        if ($this->db->affected_rows() == 0) {
            // Doble verificación de existencia real
            $this->db->where(array('det7_id' => $det7_id, 'tp_rrhh_form' => $tp_rrhh));
            $check = $this->db->get('detalle_form7_rrhh')->num_rows();

            if ($check == 0) {
                $data_insert = array(
                    'det7_id'      => $det7_id,
                    'tp_rrhh_form' => $tp_rrhh,
                    $campo         => $valor
                );
                $res = $this->db->insert('detalle_form7_rrhh', $data_insert);
                $id_detalle = $this->db->insert_id();
            } else {
                $res = true; // El valor era el mismo, no hubo cambio
                $id_detalle = 0; // Obtener de la DB si fuera necesario
            }
        } else {
            $res = true;
        }

        // 6. RECALCULAR TOTAL DE LA FILA (Integridad de datos en DB)
        // Esto asegura que la columna 'total' de la tabla detalle esté siempre sincronizada
        if ($res) {
            $this->db->query("
                UPDATE detalle_form7_rrhh 
                SET total = (
                    nro_medicos + nro_odontologos + nro_farmaceuticos + nro_laboratoristas + 
                    nro_otros_prof + nro_nutricionistas + nro_trabajo_social + nro_jefe_superv_enf + 
                    nro_lic_grad_enf + nro_aux_enf + nro_pers_adm + nro_pers_adm_salud + 
                    nro_pers_adm_tec + nro_pers_adm_aux + nro_pers_adm_chof + 
                    nro_pers_adm_artesanos + nro_pers_adm_trab_manual
                ) 
                WHERE det7_id = $det7_id AND tp_rrhh_form = $tp_rrhh
            ");
        }

        // 7. Respuesta final
        echo json_encode(array('status' => $res ? 'success' : 'error'));
    }
    /////////////////////////////

    ///// Guarda formulario Compra de Servicios 
    public function guarda_servicios_automatica() {
      if (ob_get_length()) ob_clean(); // Limpieza para evitar errores de JSON
      if (!$this->input->is_ajax_request()) return;

      $form_id  = $this->input->post('form_id');
      $gestion  = $this->input->post('gestion');
      $nro_fila = $this->input->post('fila'); // El nro_fila (1, 2 o 3) es nuestra clave
      $campo    = $this->input->post('campo');
      $valor    = $this->input->post('valor');
      $id_sent  = $this->input->post('id'); // ID que viene del JS

      // 1. Asegurar cabecera (det8_id)
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cab = $this->db->get('formularion8_compra_servicios')->row();
      
      if ($cab) {
          $det8_id = $cab->det8_id;
      } else {
          $this->db->insert('formularion8_compra_servicios', array(
              'form_id' => $form_id, 
              'g_id' => $gestion,
              'form8_estado' => 1
          ));
          $det8_id = $this->db->insert_id();
      }

      // 2. LÓGICA ANTIDUPLICADOS: Buscar por "Casillero Fijo"
      // Buscamos si ya existe un registro vinculado a esta cabecera Y a esta posición (1, 2 o 3)
      $this->db->where(array(
          'det8_id' => $det8_id, 
          'nro_posicion' => $nro_fila // Usamos la fila como identificador único
      ));
      $registro_existente = $this->db->get('detalle_form8_compra_servicios')->row();

      if ($registro_existente) {
          // ACCIÓN: ACTUALIZAR (Si ya existe la fila 1, 2 o 3 para ese año)
          $this->db->where('det8_form8_id', $registro_existente->det8_form8_id);
          $res = $this->db->update('detalle_form8_compra_servicios', array($campo => $valor));
          $id_final = $registro_existente->det8_form8_id;
      } else {
          // ACCIÓN: INSERTAR (Es la primera vez que se toca este casillero)
          $data_ins = array(
              'det8_id' => $det8_id,
              'nro_posicion' => $nro_fila, // Guardamos la posición para futuras validaciones
              $campo => $valor
          );
          $res = $this->db->insert('detalle_form8_compra_servicios', $data_ins);
          $id_final = $this->db->insert_id();
      }

      // 3. Respuesta JSON con el ID real de la base de datos
      echo json_encode(array(
          'status' => $res ? 'success' : 'error', 
          'nuevo_id' => $id_final
      ));
  }

  //// guarda formulario Presupuesto
  public function guarda_presupuesto_automatica() {
      // 1. Limpieza de salida para evitar errores de JSON
      if (ob_get_length()) ob_clean();

      $id_det  = $this->input->post('id');
      $campo   = $this->input->post('campo');
      $valor   = $this->input->post('valor');
      $form_id = $this->input->post('form_id');
      $gestion = $this->input->post('gestion');

      // --- 1. ASEGURAR CABECERA (formularion9_presupuestos) ---
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cabecera = $this->db->get('formularion9_presupuestos')->row();

      if ($cabecera) {
          $det9_id = $cabecera->det9_id;
      } else {
          $this->db->insert('formularion9_presupuestos', array(
              'form_id' => $form_id, 
              'g_id' => $gestion,
              'form9_estado' => 1
          ));
          $det9_id = $this->db->insert_id();
      }

      // --- 2. LÓGICA UPSERT PARA EL DETALLE ---
      // Si el ID es 0, buscamos si ya existe una fila para este det9_id para no duplicar
      if (empty($id_det) || $id_det == 0) {
          $this->db->where('det9_id', $det9_id);
          $existe_det = $this->db->get('detalle_form9_presupuestos')->row();
          
          if ($existe_det) {
              $id_det = $existe_det->det9_form9_id;
          } else {
              // Si realmente es nuevo, insertamos
              $this->db->insert('detalle_form9_presupuestos', array(
                  'det9_id' => $det9_id,
                  $campo => $valor
              ));
              $id_det = $this->db->insert_id();
          }
      }

      // --- 3. ACTUALIZAR EL VALOR DEL CAMPO ACTUAL ---
      $this->db->where('det9_form9_id', $id_det);
      $res = $this->db->update('detalle_form9_presupuestos', array($campo => $valor));

      // --- 4. RECALCULO AUTOMÁTICO (Usando COALESCE para evitar errores con NULL) ---
      if ($res) {
          $sql = "UPDATE detalle_form9_presupuestos 
                  SET total_ingresos_ejecutados = (COALESCE(ingresos_propios_ejecutados,0) + COALESCE(recursos_financieros_ejecutados,0)),
                      gastos_programados        = (COALESCE(ingresos_propios_programados,0) + COALESCE(recursos_financieros_programados,0)),
                      deficit_superavit         = (COALESCE(ingresos_propios_ejecutados,0) + COALESCE(recursos_financieros_ejecutados,0)) - COALESCE(gastos_ejecutados,0)
                  WHERE det9_form9_id = " . intval($id_det);
          $this->db->query($sql);
      }

      // --- 5. RESPUESTA AL SCRIPT ---
      echo json_encode(array(
          'status' => $res ? 'success' : 'error', 
          'nuevo_id' => $id_det
      ));
  }

  /////
    public function guarda_reembolso_automatica() {
      // 1. Limpieza radical de salida
      if (ob_get_length()) ob_clean();

      $id_det  = $this->input->post('id');
      $campo   = $this->input->post('campo');
      $valor   = $this->input->post('valor');
      $form_id = $this->input->post('form_id');
      $gestion = $this->input->post('gestion');

      // Asegurar valor numérico
      $valor = (is_numeric($valor)) ? $valor : 0;

      // 2. Asegurar cabecera (formularion10_reembolsos)
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cab = $this->db->get('formularion10_reembolsos')->row();
      $det10_id = ($cab) ? $cab->det10_id : 0;

      if (!$det10_id) {
          $this->db->insert('formularion10_reembolsos', array('form_id' => $form_id, 'g_id' => $gestion, 'form10_estado' => 1));
          $det10_id = $this->db->insert_id();
      }

      // 3. Lógica Upsert para el Detalle (Tabla: detalle_form10_presupuestos)
      if (empty($id_det) || $id_det == 0) {
          $this->db->where('det10_id', $det10_id);
          $existe_det = $this->db->get('detalle_form10_presupuestos')->row();
          
          if ($existe_det) {
              $id_det = $existe_det->det10_form10_id;
          } else {
              $this->db->insert('detalle_form10_presupuestos', array('det10_id' => $det10_id, $campo => $valor));
              $id_det = $this->db->insert_id();
          }
      }

      // 4. Actualizar campo
      $this->db->where('det10_form10_id', $id_det);
      $res = $this->db->update('detalle_form10_presupuestos', array($campo => $valor));

      // 5. Recalculo del Total en Servidor (Evita descuadres)
      if ($res) {
          $sql = "UPDATE detalle_form10_presupuestos 
                  SET total_reembolsos = (
                      COALESCE(reemb_concep_medicamentos,0) + 
                      COALESCE(reemb_concep_laboratorio,0) + 
                      COALESCE(reemb_concep_imagenologia,0) + 
                      COALESCE(reemb_otros_conceptos,0)
                  )
                  WHERE det10_form10_id = " . intval($id_det);
          $this->db->query($sql);
      }

      echo json_encode(array('status' => $res ? 'success' : 'error', 'nuevo_id' => $id_det));
  }

    //// Guarda Detalle Ambulancia
    public function insertar_ambulancia_detalle() {
        // 1. Limpieza de búfer de salida para evitar que residuos o warnings rompan el formato JSON
        if (ob_get_length()) ob_clean();

        // 2. Seguridad: Validación manual de peticiones AJAX compatible con CodeIgniter 1.5
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'msg' => 'Acceso directo no permitido al servidor.'));
            exit;
        }

        // 3. Recepción y limpieza estricta de variables enviadas por el objeto $.ajax de JS
        $form_id              = intval($this->input->post('form_id'));
        $act_id               = intval($this->input->post('act_id'));
        $placa                = trim(strtoupper($this->input->post('placa')));
        $gestion              = intval($this->input->post('gestion'));
        $estado_ambulancia    = intval($this->input->post('estado_ambulancia'));
        $situacion_ambulancia = intval($this->input->post('situacion_ambulancia'));

        // 4. VALIDACIÓN DE CAMPOS MANDATORIOS EN EL SERVIDOR
        if (empty($form_id) || empty($act_id) || empty($placa) || empty($estado_ambulancia) || empty($situacion_ambulancia)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'msg' => 'Existen campos mandatorios incompletos.'));
            exit;
        }
        
        // Validación de máscara del formato boliviana por seguridad en el backend
        if (!preg_match('/^[0-9]{4}-[A-Z]{3}$/', $placa)) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'msg' => 'El número de placa enviado no cumple con la nomenclatura obligatoria XXXX-YYY.'));
            exit;
        }

        // 5. MECANISMO DE UPSERT PARA LA CABECERA (formularion11_ambulancias)
        // Verificamos si la regional ya cuenta con un registro maestro del Formulario 11
        $this->db->where('form_id', $form_id);
        $cabecera = $this->db->get('formularion11_ambulancias')->row();
        
        if ($cabecera) {
            // Si ya existe la cabecera, extraemos su llave primaria correlativa
            $det11_id = $cabecera->det11_id;
        } else {
            // Si es el primer vehículo de la distrital, creamos la cabecera en caliente
            $data_cabecera = array(
                'form_id' => $form_id,
                'form11_estado' => 1 // Flag activo de edición
            );
            $this->db->insert('formularion11_ambulancias', $data_cabecera);
            
            // Recuperamos el ID recién generado por la secuencia serial de PostgreSQL/MySQL
            $det11_id = $this->db->insert_id();
        }

        // 6. INSERCIÓN DEL REGISTRO DE DETALLE VINCULADO AL ESTABLECIMIENTO (act_id)
        $data_detalle = array(
            'det11_id'             => $det11_id,
            'placa'                => $placa,
            'gestion'              => $gestion, // Año de adjudicación/adquisición
            'estado_ambulancia'    => $estado_ambulancia,   // Almacena el código entero (1,2,3,4)
            'situacion_ambulancia' => $situacion_ambulancia, // Almacena el código entero (1,2,3,4)
            'act_id'               => $act_id
        );

        $insert_res = $this->db->insert('detalle_form11_ambulancias', $data_detalle);
        
        // Recuperamos el ID correlativo único del detalle recién guardado
        $det11_form11_id_generado = $this->db->insert_id();

        // 7. RESPUESTA JSON COMPATIBLE CON EL SCRIPT DE INYECCIÓN EN CALIENTE
        header('Content-Type: application/json');
        if ($insert_res && $det11_form11_id_generado > 0) {
            echo json_encode(array(
                'status' => 'success',
                'id'     => $det11_form11_id_generado // Es el id que el JS leerá como ${resp.id} para armar la fila
            ));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'El motor de la base de datos rechazó la fila debido a inconsistencias de indexación.'));
        }
        exit; // Detiene el hilo de CodeIgniter blindando la pureza del JSON
    }

    //// Eliminar registro ambulancia
    public function eliminar_ambulancia_detalle() {
        // 1. Limpieza estricta de salida para evitar que warnings de PHP arruinen el JSON
        if (ob_get_length()) ob_clean();

        // 2. Seguridad: Validación asíncrona manual compatible con tu versión CodeIgniter 1.5
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'msg' => 'Acceso directo denegado.'));
            exit;
        }

        // Capturamos el identificador correlativo único de la fila enviado por POST
        $id_detalle = intval($this->input->post('id_detalle'));

        if (empty($id_detalle) || $id_detalle <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'msg' => 'ID de registro inválido o ausente en el paquete de red.'));
            exit;
        }

        // 3. PROCESAMIENTO DE BAJA EN LA BASE DE DATOS
        $this->db->where('det11_form11_id', $id_detalle);
        $res_delete = $this->db->delete('detalle_form11_ambulancias');

        // 4. ENVÍO DE RESPUESTA ESTRUCTURADA AL JAVASCRIPT
        header('Content-Type: application/json');
        if ($res_delete) {
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'El motor de base de datos rechazó la eliminación por integridad referencial.'));
        }
        exit; // Detiene la ejecución protegiendo el parseo del Front-End
    }












}