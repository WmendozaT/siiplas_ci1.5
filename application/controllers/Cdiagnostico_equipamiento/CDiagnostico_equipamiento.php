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
            $data['cuerpo'] = $this->unidad_ejecutora_eleccionado($pei_id, $dist_id,0); /// regional
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

    /*------- Listado de formularios -------*/
public function unidad_ejecutora_seleccionado($equip_id, $dist_id, $tp_adm){
    $get_form_distrital = $this->model_diagnosticoequip->get_distrital_formulario_diagnostico_activo($equip_id, $dist_id);
    $tabla = '';
    
    $tabla .= '
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
                                            <th></th> <!-- # -->
                                            <th class="hasinput"><input type="text" class="form-control col-search-input-equip input-xs" style="font-size:9px; padding:2px; text-align:center;" placeholder="🔍 Distrito"/></th>
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
                                        <tr style="background-color: #1a237e; color: #ffffff; height: 35px;">
                                            <th style="width:1%; text-align:center; vertical-align: middle;">#</th>
                                            <th style="width:5%; text-align:center; vertical-align: middle;">DISTRITAL</th>
                                            <th style="width:7%; text-align:center; vertical-align: middle;">RESPONSABLE / SOLICITANTE</th>
                                            <th style="width:8%; text-align:center; vertical-align: middle;">ESTABLECIMIENTO DE SALUD</th>
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
                                            <th style="width:10%; text-align:center; vertical-align: middle;">OBSERVACIONES / JUSTIFICACIÓN</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bdi_equipamiento">';
                                    foreach($get_form_distrital as $row){
                                        $tabla.='
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>';
                                    }
                                    $tabla.='
                                    </tbody>
                                </table>
                               </div>
                            </div>
                           </div>
                          </div>
                        </article>
                    </div>
        </div>
        </section>

        <div class="modal fade" id="modal_nuevo_equipamiento" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg" style="width: 75%; max-width: 950px;">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);">
                    <div class="modal-header" style="background: #1a237e; color: white; padding: 12px 15px;">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="color:white; opacity:0.8;">&times;</button>
                        <h4 class="modal-title" style="font-weight: bold; font-size: 13px; text-transform: uppercase;">
                            <i class="fa fa-plus-circle"></i> Formulario de Planificación y Alta de Equipamiento Médico
                        </h4>
                    </div>
                    <div class="modal-body" style="padding: 15px; background: #fafafa;">
                         <form class="smart-form" id="form_guardar_equipamiento_modal" autocomplete="off">
            <input type="hidden" name="equip_id" value="' . $equip_id . '">
            <input type="hidden" name="dist_id" value="' . $dist_id . '">
            
            <!-- PESTAÑAS DE ENLACE NATIVAS -->
            <ul class="nav nav-tabs" style="margin-bottom:15px; font-size:11px; font-weight:bold;">
                <li class="active"><a href="#modal-tab-general" data-toggle="tab"><i class="fa fa-info-circle"></i> 1. Ficha del Activo</a></li>
                <li><a href="#modal-tab-quinquenio" data-toggle="tab"><i class="fa fa-calendar"></i> 2. Cronograma (2026 - 2030)</a></li>
            </ul>
            
            <div class="tab-content">
                <!-- CONTENEDOR TABS 1: DETALLES GENERALES -->
                <div class="tab-pane active" id="modal-tab-general">
                    <fieldset style="background:transparent; padding:0;">
                        <div class="row">
                            <section class="col col-6">
                                <label class="label"><b>Nombre del Equipamiento Médico *</b></label>
                                <label class="input"><i class="icon-append fa fa-tag"></i>
                                    <input type="text" name="nombre_equipamiento" value="" required placeholder="Ej. Equipo de Ultrasonido Diagnóstico">
                                </label>
                            </section>
                            <section class="col col-6">
                                <label class="label"><b>Nombre del Responsable / Solicitante</b></label>
                                <label class="input"><i class="icon-append fa fa-user"></i>
                                    <input type="text" name="responsable" value="" placeholder="Ej. Dr. Carlos Murillo">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-4">
                                <label class="label"><b>Servicio / Unidad Destino</b></label>
                                <label class="input"><i class="icon-append fa fa-hospital-o"></i>
                                    <input type="text" name="servicio_unidad" value="" placeholder="Ej. Neonatología">
                                </label>
                            </section>
                            <section class="col col-4">
                                <label class="label"><b>Ubicación Física</b></label>
                                <label class="input"><i class="icon-append fa fa-map-marker"></i>
                                    <input type="text" name="ubicacion_fisica" value="" placeholder="Ej. Pabellón de Maternidad - Piso 1">
                                </label>
                            </section>
                            <section class="col col-4">
                                <label class="label"><b>Partida de Gasto (par_id)</b></label>
                                <label class="input"><i class="icon-append fa fa-folder-open-o"></i>
                                    <input type="number" name="par_id" value="" placeholder="Ej. 43110">
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-3">
                                <label class="label"><b>Cantidad Total *</b></label>
                                <label class="input"><i class="icon-append fa fa-calculator"></i>
                                    <input type="number" name="cantidad" id="m_cantidad" value="" min="1" required style="text-align:center;">
                                </label>
                            </section>
                            <section class="col col-3">
                                <label class="label"><b>Costo Unitario (Bs.) *</b></label>
                                <label class="input"><i class="icon-append fa fa-money"></i>
                                    <input type="text" name="costo_unitario" id="m_costo_unit" value="" required style="text-align:right;">
                                </label>
                            </section>
                            <section class="col col-3">
                                <label class="label"><b>Tipo de Compra</b></label>
                                <label class="select">
                                    <select name="tp_compra">
                                        <option value="1">REPOSICIÓN</option>
                                        <option value="2">COMPRA NUEVA</option>
                                        <option value="3">ADECUACIÓN</option>
                                    </select><i></i>
                                </label>
                            </section>
                            <section class="col col-3">
                                <label class="label"><b>Requiere Adecuación</b></label>
                                <label class="select">
                                    <select name="tp_adecuacion">
                                        <option value="0">0.- NO REQUIERE</option>
                                        <option value="1">1.- REQUIERE TRABAJOS</option>
                                    </select><i></i>
                                </label>
                            </section>
                        </div>
                        <div class="row">
                            <section class="col col-12" style="width:100%;">
                                <label class="label"><b>Observaciones / Justificaciones</b></label>
                                <label class="textarea">
                                    <textarea name="observaciones" rows="2" placeholder="Describa justificaciones del requerimiento..."></textarea>
                                </label>
                            </section>
                        </div>
                    </fieldset>
                </div>
                </form>
                    </div>
                </div>
            </div>
        </div>';
        return $tabla;
    }


    /// funcion para exportar
    public function exportar_consolidado_excel_equipamiento($tp_rep, $dist_id) {
        $tabla='No disponible ...';
    }

}