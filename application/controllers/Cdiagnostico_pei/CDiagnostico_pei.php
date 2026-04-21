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
     
      }else{
          redirect('/','refresh');
      }
    }

    /*------- View Principal Diagnostico PEI -------*/
    public function diagnostico_principal(){
        $data['titulo']='';
        if($this->tp_adm==1){ /// administrador Nacional
          $data['titulo']=$this->Seleccion_unidadEjecutora();
          $data['cuerpo']='<div id="contenedor_formulario"></div>';
        }
        else{
          if($this->conf_pei==1){
            $data['cuerpo']=$this->unidad_ejecutora_eleccionado($this->dist_id);
          }
          else{
            $data['cuerpo']='
            <div class="alert alert-block alert-danger">
                <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Acceso Restringido!</h4>
                <p>Usted no cuenta con los privilegios necesarios para el llenado del formulario en esta unidad ejecutora.</p>
            </div>';
          }
        }

      $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data); 

    }

    
    /*------- Selecciona Unidad Ejecutora -------*/
    public function Seleccion_unidadEjecutora(){
      $get_diagnostico=$this->model_diagnosticopei->get_diagnostico_activo();
      $UnidadEjecutora=$this->model_diagnosticopei->lista_UnidadEjecutora(); /// Lista Distritales
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
           
           $tabla = $this->unidad_ejecutora_eleccionado($get_diagnostico[0]['pei_id'],$dist_id);

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
    public function unidad_ejecutora_eleccionado($pei_id,$dist_id){
      $get_form_distrital=$this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id,$dist_id);

      $tabla='';
      $tabla.='
          <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              '.$this->style_form().'
              <div id="toast-notificacion" class="toast-msg">
                ¡Información guardada correctamente! ✓
              </div>
              <div class="well well-sm well-light">
              <h2>'.strtoupper($get_form_distrital[0]['dist_distrital']).'</h2>
                <div id="tabs">
                  <ul>
                    <li>
                      <a href="#tabs-a"><b>I.- POBLACIÓN AFILIADA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-b"><b>II.- EMPRESAS APORTANTES</b></a>
                    </li>
                    <li>
                      <a href="#tabs-c"><b>III.- PERFIL EPIDEMIOLOGICO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-d"><b>IV.- INFRAESTRUCTURA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-e"><b>V.- EQUIPO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-f"><b>VI.- RECURSOS HUMANOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-g"><b>VI.- COMPRA DE SERVICIOS</b></a>
                    </li>
                  </ul>
                  <div id="tabs-a">
                    <div class="row">
                      '.$this->formulario_N1($get_form_distrital).'
                    </div>
                  </div>

                  <div id="tabs-b">
                    <div class="row">
                     '.$this->formulario_N2($get_form_distrital).'
                    </div>
                  </div>
                  
                  <div id="tabs-c">
                    <div class="row">
                      c
                    </div>
                  </div>

                  <div id="tabs-d">
                    <div class="row">
                    d
                    </div>
                  </div>
                  
                  <div id="tabs-e">
                    <div class="row">
                     e
                    </div>
                  </div>

                  <div id="tabs-f">
                    <div class="row">
                         f
                    </div>
                  </div>

                  <div id="tabs-g">
                    <div class="row">
                         g
                    </div>
                  </div>

                </div>
              </div>
            </article>

            
            <script type="text/javascript">
              // DO NOT REMOVE : GLOBAL FUNCTIONS!
              $(document).ready(function() {
                pageSetUp();
                $("#menu").menu();
                $(".ui-dialog :button").blur();
                $("#tabs").tabs();
              })
            </script>
            <script>
              $(document).ready(function() {
                  var timeout = null;
                  var base_url = "'.base_url().'"; 

                  $(".observaciones-input").on("keyup", function() {
                      var $this = $(this); 
                      
                      // BUSCAMOS LOS VALORES RELATIVOS AL TEXTAREA ACTUAL
                      // Buscamos el contenedor padre y luego el input dentro de ese bloque
                      var contenedor = $this.closest("div").parent(); 
                      var form_id = contenedor.find(".form_id").val();
                      var nro_obs = contenedor.find(".nro_obs").val();
                      
                      var texto = $this.val();
                      var status = contenedor.find(".status"); // Cada uno tiene su propio status

                      if (!form_id || form_id == "0") {
                          status.show().text("⚠️ Error: No se detectó ID.").css("color", "red");
                          return;
                      }

                      status.stop(true, true).show().text("Escribiendo...").css("color", "blue");
                      
                      clearTimeout(timeout);

                      timeout = setTimeout(function() {
                          $.ajax({
                              url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_observacion",
                              type: "POST",
                              data: {
                                  form_id: form_id,
                                  nro: nro_obs, 
                                  observacion: texto
                              },
                              success: function(response) {

                                  status.text("Guardado ✓").css("color", "green").fadeOut(2000);
                                  $("#toast-notificacion").fadeIn(400).delay(2000).fadeOut(400);
                              },
                              error: function() {
                                  status.text("Error al guardar").css("color", "red");
                                  $("#toast-notificacion")
                                      .text("❌ Error al guardar")
                                      .css("background-color", "#dc3545")
                                      .fadeIn(400).delay(3000).fadeOut(400);
                              }
                          });
                      }, 800); 
                  });
              });
          </script>

          <script>
            document.getElementById("fecha-actual").innerText = new Date().toLocaleDateString();
            $(document).ready(function() {
                // ... tu código anterior de guardado automático ...

                // 1. Al entrar al input (Focus)
                $(".auto-save").on("focus", function() {
                    var valor = $(this).val();
                    // Si el valor es 0, lo dejamos vacío para que el usuario escriba directo
                    if (valor == "0") {
                        $(this).val("");
                    }
                });

                // 2. Al salir del input (Blur)
                $(".auto-save").on("blur", function() {
                    var valor = $(this).val();
                    // Si el usuario no escribió nada, le devolvemos el 0 por defecto
                    if (valor === "") {
                        $(this).val("0");
                    }
                });
            });
          </script>';


      return $tabla;
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



    /*------- formulario N 1 -------*/
    public function formulario_N1($get_form_distrital){
      $detalle_form1=$this->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $tabla='';
      $tabla.='
      <div class="viewport-container">
                    <br>
                    <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Formulario</button>
                    <div class="page">
                        <!-- Fecha de Impresión Automática -->
                        <div class="fecha-impresion">
                            Fecha: <span id="fecha-actual"></span>
                        </div>
                        <div class="header">
                            <p>CAJA NACIONAL DE SALUD</p>
                            <h1><b>DIAGNÓSTICO DE LA POBLACIÓN ASEGURADA</b></h1>
                        </div>

                        <div style="margin: 20px 0; font-weight: bold;">
                            Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
                        </div>

                        <div style="font-weight: bold; margin-bottom: 10px;">1. Objetivo del instrumento</div>
                        <div style="border: 1px solid #ccc; padding: 10px; font-size: 9pt; margin-bottom: 20px;">
                            Recopilar, validar y sistematizar información cuantitativa de la población afiliada (titulares y Beneficiarios) para analizar tendencias y cobertura y demanda potencial de la Caja Nacional de Salud.
                        </div>

                        <div style="font-weight: bold; margin-bottom: 10px;">2. Relevamiento de poblacion afiliada (2021 - 2025)</div>
                        <table>
                            <thead>
                                <tr>
                                  <th colspan="5" style="text-align:center; width: 40%;">Tipo de población afiliada</th>
                                </tr>
                                <tr>
                                    <th style="width: 10%;text-align:center;">Gestión</th>
                                    <th style="width: 25%;text-align:center;">Cotizantes Titulares</th>
                                    <th style="width: 25%;text-align:center;">Cotizantes Pasivos</th>
                                    <th style="width: 25%;text-align:center;">Beneficiarios</th>
                                    <th style="width: 10%;text-align:center;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>';
                             foreach($detalle_form1 as $row){
                                  $tabla.='
                                  <tr class="fila-dato">
                                      <td style="font-size: 11pt;">'.$row['gestion'].'</td>
                                      <td>
                                          <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_cot_tit" 
                                                 value="'.$row['titulares'].'">
                                      </td>
                                      <td>
                                      <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_cot_pas" 
                                                 value="'.$row['pasivos'].'">
                                      </td>
                                      <td>
                                      <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_cot_ben" 
                                                 value="'.$row['beneficiarios'].'">
                                      </td>
                                      <td class="total-row" style="font-weight:bold;">'.$row['total_gestion'].'</td>
                                  </tr>';
                             }
                            $tabla.='
                            </tbody>
                        </table>

                        <div style="border: 1px solid #000; padding: 15px; font-size: 8pt; margin-top: 20px;">
                            <strong>Recomendaciones:</strong><br>
                            • Extraer información anual por cada categoría (2021 - 2025).<br>
                            • Verificar consistencia entre fuentes oficiales.
                        </div>

                        <input type="hidden"  class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
                        <input type="hidden"  class="nro_obs" value="1">

                        <div style="margin-top: 30px;">
                            <strong>3. Observaciones adicionales</strong>
                            <textarea 
                                class="observaciones-input" 
                                name="obs" 
                                id="obs" 
                                data-nro="1"
                                onpaste="return false;" 
                                placeholder="Escriba aquí sus observaciones..."
                                style="width: 100%; height: 100px; resize: none;"
                            >'.strtoupper($get_form_distrital[0]['observacion1']).'</textarea>
                        </div>

                                <!-- Firma -->
                        <div class="firma-container">
                            <div class="linea-firma"></div>
                            <div class="firma-texto">'.$get_form_distrital[0]['tipo_firma'].'</div>
                        </div>

                        <!-- Pie de página -->
                        <div class="footer-nacional">
                            DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
                        </div>
                    </div>
                    <hr>
                  </div>
                  <script>
                    $(document).ready(function() {
                        var timer = null;
                        var base_url = "'.base_url().'"; 

                        $(".auto-save").on("keyup change", function() {
                            var $input = $(this);
                            var $fila = $input.closest("tr");
                            
                            // Recalcular total visualmente de inmediato
                            var t = parseFloat($fila.find("input[data-col=nro_cot_tit]").val()) || 0;
                            var p = parseFloat($fila.find("input[data-col=nro_cot_pas]").val()) || 0;
                            var b = parseFloat($fila.find("input[data-col=nro_cot_ben]").val()) || 0;
                            
                            $fila.find(".total-row").text(t + p + b);

                            clearTimeout(timer);

                            timer = setTimeout(function() {
                                $.ajax({
                                    url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form1",
                                    type: "POST",
                                    data: {
                                        form_id: $input.data("form"),
                                        gestion: $input.data("gestion"),
                                        columna: $input.data("col"),
                                        valor: $input.val()
                                    },
                                    success: function(resp) {
                                        
                                        $("#toast-notificacion").fadeIn(400).delay(2000).fadeOut(400);
                                    }
                                });
                            }, 800); 
                        });
                    });
                  </script>';


        return $tabla;

    }

    //// Guarda informacion de las tablas automaticamente
    public function guarda_detalle_automatica_form1() {
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $columna = $this->input->post('columna'); // Ej: nro_cot_tit
        $valor   = $this->input->post('valor');

        // Validar que la columna sea permitida (Seguridad)
        $columnas_permitidas = array('nro_cot_tit', 'nro_cot_pas', 'nro_cot_ben');
        if (!in_array($columna, $columnas_permitidas)) return;

          $this->db->where('form_id', $form_id);
          $this->db->where('g_id', $gestion);
          $existe = $this->db->get('formularion1_detalle')->num_rows();

          if ($existe > 0) {
              $this->db->where('form_id', $form_id);
              $this->db->where('g_id', $gestion);
              return $this->db->update('formularion1_detalle', array($columna => $valor));
          } else {
              return $this->db->insert('formularion1_detalle', array(
                  'form_id' => $form_id,
                  'g_id'    => $gestion,
                  $columna  => $valor
              ));
          }


        echo "ok";
    }



    public function formulario_N2($get_form_distrital){
      $detalle_form2=$this->model_diagnosticopei->get_formulario_N2($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $tabla='';
      $tabla.='
      <div class="viewport-container">
                    <br>
                    <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Formulario</button>
                    <div class="page">
                        <!-- Fecha de Impresión Automática -->
                        <div class="fecha-impresion">
                            Fecha: <span id="fecha-actual"></span>
                        </div>
                        <div class="header">
                            <p>CAJA NACIONAL DE SALUD</p>
                            <h1><b>DIAGNÓSTICO DE EMPRESAS</b></h1>
                        </div>

                        <div style="margin: 20px 0; font-weight: bold;">
                            Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
                        </div>


                        <div style="font-weight: bold; margin-bottom: 10px;">1. Objetivo del instrumento</div>
                        <div style="border: 1px solid #ccc; padding: 10px; font-size: 9pt; margin-bottom: 20px;">
                            Recolectar, validar y sistematizar información anual del número de empresas aportantes, permitiendo analizar su evolución, cobertura institucional y comportamiento contributivo.
                        </div>

                        <div style="font-weight: bold; margin-bottom: 10px;">2. Definición operativa</div>
                        <div style="border: 1px solid #ccc; padding: 10px; font-size: 9pt; margin-bottom: 20px;">
                            Empresa aportante: unidad económica registrada que realiza aportes al sistema en un periodo determinado, independientemente del número de trabajadores afiliados.
                        </div>
                        
                        <table>
                            <thead>
                                <tr>
                                    <th style="width: 10%;text-align:center;">Gestión</th>
                                    <th style="width: 25%;text-align:center;">N° de empresas registradas</th>
                                    <th style="width: 25%;text-align:center;">con aportes al dia</th>
                                    <th style="width: 25%;text-align:center;">en mora</th>
                                    <th style="width: 10%;text-align:center;">TOTAL</th>
                                </tr>
                            </thead>
                            <tbody>';
                             foreach($detalle_form2 as $row){
                                  $tabla.='
                                  <tr class="fila-dato">
                                      <td style="font-size: 11pt;">'.$row['gestion'].'</td>
                                      <td>
                                          <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_empresas_reg" 
                                                 value="'.$row['empresas'].'">
                                      </td>
                                      <td>
                                      <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_aportes_dia" 
                                                 value="'.$row['aportes'].'">
                                      </td>
                                      <td>
                                      <input type="number" 
                                                 class="auto-save" 
                                                 min="0"
                                                 onkeypress="return event.charCode >= 48"
                                                 data-form="'.$row['form_id'].'" 
                                                 data-gestion="'.$row['gestion'].'" 
                                                 data-col="nro_empresa_mora" 
                                                 value="'.$row['mora'].'">
                                      </td>
                                      <td class="total-row" style="font-weight:bold;">'.$row['total_gestion_empresas'].'</td>
                                  </tr>';
                             }
                            $tabla.='
                            </tbody>
                        </table>

                        <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
                        <input type="hidden" class="nro_obs" value="2">

                        <div style="margin-top: 30px;">
                            <strong>3. Observaciones adicionales</strong>
                            <textarea 
                                class="observaciones-input" 
                                name="obs" 
                                id="obs" 
                                data-nro="2"
                                onpaste="return false;" 
                                placeholder="Escriba aquí sus observaciones..."
                                style="width: 100%; height: 100px; resize: none;"
                            >'.strtoupper($get_form_distrital[0]['observacion2']).'</textarea>
                        </div>

                                <!-- Firma -->
                        <div class="firma-container">
                            <div class="linea-firma"></div>
                            <div class="firma-texto">'.$get_form_distrital[0]['tipo_firma'].'</div>
                        </div>

                        <!-- Pie de página -->
                        <div class="footer-nacional">
                            DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
                        </div>
                    </div>
                    <hr>
                  </div>
                  <script>
                    document.getElementById("fecha-actual").innerText = new Date().toLocaleDateString();
                  </script>
                  <script>
                    $(document).ready(function() {
                        var timer = null;
                        var base_url = "'.base_url().'"; 

                        $(".auto-save").on("keyup change", function() {
                            var $input = $(this);
                            var $fila = $input.closest("tr");
                            
                            // Recalcular total visualmente de inmediato
                            var t = parseFloat($fila.find("input[data-col=nro_empresas_reg]").val()) || 0;
                            var p = parseFloat($fila.find("input[data-col=nro_aportes_dia]").val()) || 0;
                            var b = parseFloat($fila.find("input[data-col=nro_empresa_mora]").val()) || 0;
                            
                            $fila.find(".total-row").text(t + p + b);

                            clearTimeout(timer);

                            timer = setTimeout(function() {
                                $.ajax({
                                    url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form2",
                                    type: "POST",
                                    data: {
                                        form_id: $input.data("form"),
                                        gestion: $input.data("gestion"),
                                        columna: $input.data("col"),
                                        valor: $input.val()
                                    },
                                    success: function(resp) {
                                        
                                        $("#toast-notificacion").fadeIn(400).delay(2000).fadeOut(400);
                                    }
                                });
                            }, 800); 
                        });
                    });
                  </script>';


        return $tabla;
    }


  public function style_form(){
  $tabla='          
          <style>
                .toast-msg {
                position: fixed;
                top: 20px;
                right: 20px;
                background-color: #28a745;
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                display: none; /* Oculto por defecto */
                z-index: 9999;
                font-weight: bold;
                border-left: 5px solid #1e7e34;
            }
            </style>
              <style>
                .btn-imprimir { background-color: #28a745; color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; margin-bottom: 20px; font-weight: bold; font-size: 16px; transition: 0.3s; }
                .btn-imprimir:hover { background-color: #218838; }
                
                .viewport-container {
                    background-color: #525659;
                    display: flex;
                    flex-direction: column;
                    align-items: center; /* Centra horizontalmente */
                   
                    padding: 0 !important; 
                    box-sizing: border-box;
                    /* Permite scroll si el contenido es más grande que la pantalla (celulares) */
                    overflow-x: auto; 
                    width: 100%;
                }

                /* 2. LA HOJA (Tamaño Carta Fijo) */
                .page { 
                    background-color: white; 
                    width: 8.5in; 
                    min-width: 8.5in; /* Evita que se encoja en celulares */
                    height: 11in; 
                    padding: 0.6in 0.7in; 
                    box-sizing: border-box; 
                    position: relative; 
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                    display: flex;
                    flex-direction: column;
                    border: 1px solid #ccc;
                }
                
                .fecha-impresion {
                    position: absolute;
                    top: 0.3in;
                    right: 0.7in;
                    font-size: 9pt;
                    color: #333;
                }
                
                .container {
                    background-color: white;
                    width: 900px;
                    padding: 40px;
                    border: 1px solid #ccc;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                }


                .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 15px; padding-bottom: 5px; }
                .header h1 { margin: 5px 0; font-size: 16pt; }

                /* TABLA EDITABLE */
                table { width: 100%; border-collapse: collapse; margin: 15px 0; font-size: 9pt; }
                th { background-color: #FFC000 !important; border: 1px solid #000; padding: 8px; }
                td { border: 1px solid #000; padding: 0; text-align: center; }
                input { width: 100%; border: none; padding: 8px; text-align: center; box-sizing: border-box; font-size: 10pt; color:blue; background: transparent; }
                .total-row { background-color: #FFFFCC !important; font-weight: bold; font-size: 11pt;}
                .total-cell { background-color: #FFFF00 !important; font-weight: bold; padding: 8px; }

                /* SECCIÓN DE FIRMA */
                .firma-container {
                    margin-top: 60px;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    width: 300px; /* Ajustado para la firma */
                }
                .linea-firma {
                    border-top: 1px dashed #000;
                    width: 70%;
                    margin-bottom: 5px;
                }
                .firma-texto {
                    font-weight: bold;
                    font-size: 8pt;
                    text-align: center;
                }

                /* PIE DE PÁGINA */
                .footer-nacional {
                    position: absolute;
                    bottom: 0.5in;
                    left: 0;
                    right: 0;
                    text-align: center;
                    font-weight: bold;
                    font-size: 7pt;
                }

                /* Estilo para el área de texto en pantalla */
                .observaciones-input {
                    width: 100%;
                    height: 120px;
                    margin-top: 10px;
                    padding: 10px;
                    border: 1px solid #ccc;
                    font-family: Arial, sans-serif;
                    font-size: 10pt;
                    resize: none; /* Evita que el usuario deforme la hoja */
                    box-sizing: border-box;
                    background-color: #fafafa;
              }

                /* REGLAS DE IMPRESIÓN */
                @media print {
                    body { background: none; padding: 0; }
                    body * { visibility: hidden; }
                    .page, .page * { visibility: visible; }
                    .page { 
                        position: absolute; 
                        left: 0; 
                        top: 0; 
                        width: 8.5in !important; 
                        height: 11in !important; 
                        box-shadow: none !important; 
                    }
                    .observaciones-input {
                        border: 1px solid #000 !important; /* Borde negro sólido para impresión */
                        background-color: transparent !important;
                        overflow: hidden; /* Oculta barras de scroll en el papel */
                    }
                    .btn-imprimir { display: none !important; }
                    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
                    @page { size: letter; margin: 0; }
                }
            </style>';
            return $tabla;
    }



}