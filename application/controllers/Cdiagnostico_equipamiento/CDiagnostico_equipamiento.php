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
    public function unidad_ejecutora_seleccionado($equip_id,$dist_id,$tp_adm){
        $get_form_distrital=$this->model_diagnosticoequip->get_distrital_formulario_diagnostico_activo($equip_id,$dist_id);
        
        $tabla='
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
            <div class="well well-light" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px; min-height: 550px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <header style="border-bottom: 2px solid #2e7d32; padding-bottom: 8px; margin-bottom: 15px; font-weight: bold; color: #1b5e20; text-transform: uppercase; font-size: 11.5px; letter-spacing: 0.3px;">
                    <i class="fa fa-list-ul"></i> Listado de Registros Solicitados
                </header>
                
                <!-- Botón de Alta Rápida de Equipamiento -->
                <div style="margin-bottom: 15px;">
                    <button type="button" id="btn_nuevo_registro" class="btn btn-warning btn-sm" style="font-weight: bold; width: 100%; text-transform: uppercase; border: none; height: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); background: #e67e22;">
                        <i class="fa fa-plus"></i> + Adicionar Nuevo Registro
                    </button>
                </div>
                
                <!-- CONTENEDOR ESPECÍFICO PARA LA TABLA AJAX -->
                <div id="contenedor_lista_ajax" style="max-height: 440px; overflow-y: auto;">
                    <table>
                        <thead>
                        <tr>
                            <th>DISTRITAL</th>
                            <th>NOMBRE DEL RESPONSABLE</th>
                            <th>NOMBRE DEL ESTABLECIMIENTO</th>
                            <th>NOMBRE DEL EQUIPAMIENTO MEDICO</th>
                            <th>SERVICIO / UNIDAD</th>
                            <th>UBICACION</th>
                            <th>TIPO DE COMPRA</th>
                            <th>MODIFICAR</th>
                            <th>ELIMINAR</th>
                        </tr>
                        </thead>
                        <tbody>';
                        foreach($get_form_distrital as $row){
                            $tabla.='
                            <tr>
                                <td>'.$row['nombre_distrital'].'</td>
                                <td>'.$row['responsable'].'</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><button> MODIFICAR</button></td>
                                <td><button> ELIMINAR</button></td>
                            </tr>';
                        }
                        $tabla.='
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-7 col-md-7 col-lg-7">
            <div class="well well-light" style="padding: 15px; background: #fff; border: 1px solid #ddd; border-radius: 4px; min-height: 550px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);" id="contenedor_formulario_ajax">
                <div id="formulario">

                    <!-- Pizarra Neutra por defecto -->
                    <div style="text-align: center; color: #999; padding-top: 170px;">
                        <i class="fa fa-object-group" style="font-size: 46px; margin-bottom: 15px; color: #e0e0e0;"></i>
                        <h5 style="font-weight: bold; color: #444; font-size: 13px; margin: 0 0 5px 0;">Ficha de Equipamiento</h5>
                        <p style="font-size: 11px; color: #888; max-width: 320px; margin: 0 auto; line-height: 1.4;">
                            Seleccione un equipo de la izquierda para modificar o haga clic en el botón naranja para abrir el formulario de alta.
                        </p>
                    </div>
                
                </div>
            </div>
        </div>';

        $tabla .= '
            function cargar_formulario_derecho_ajax(form_equip_id, dist_id) {
                // REVISIÓN SIIPLAS: Escapamos las comillas simples para evitar errores de sintaxis en PHP
                var $target_formulario = $(\'#formulario\');
                
                $.ajax({
                    url: base + "index.php/Cdiagnostico_equipamiento/CDiagnostico_equipamiento/get_formulario_equipamiento",
                    type: \'POST\',
                    data: { form_equip_id: form_equip_id, dist_id: dist_id },
                    dataType: \'json\',
                    beforeSend: function() {
                        $target_formulario.html(
                            \'<div class="text-center" style="padding-top:140px;">\' +
                            \'   <i class="fa fa-gear fa-spin fa-3x text-warning"></i>\' +
                            \'   <h5 style="margin-top:15px; font-weight:bold; color:#666;">Sincronizando campos con la base de datos...</h5>\' +
                            \'</div>\'
                        );
                    },
                    success: function(data) {
                        if(data.respuesta == \'correcto\') {
                            // Reemplazamos la pizarra por el formulario HTML con una animación suave
                            $target_formulario.hide().html(data.html).fadeIn(300);
                        }
                    },
                    error: function() {
                        $target_formulario.html(\'<div class="alert alert-danger">Error al estructurar el formulario.</div>\');
                    }
                });
            }';
        return $tabla;
    }


    /// funcion para exportar
    public function exportar_consolidado_excel_equipamiento($tp_rep, $dist_id) {
        $tabla='No disponible ...';
    }

}