<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

class lib_diagnostico_pei extends CI_Controller{

    public function __construct (){
      parent::__construct();
      $this->load->model('diagnosticoPei/model_diagnosticopei');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");

    }



/*------- Detalle formulario N 1 -------*/
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
          </script>';
        return $tabla;
    }

    /*------- Detalle formulario N 2 -------*/
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
                  Fecha: <span id="fecha-actual2"></span>
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

              <!-- Pie de página -->
              <div class="footer-nacional">
                  DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
              </div>
          </div>
          <hr>
        </div>
        <script>
          document.getElementById("fecha-actual2").innerText = new Date().toLocaleDateString();
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
          </script>';
        return $tabla;
    }



    /*------- Detalle formulario N 2 -------*/
    public function formulario_N3($get_form_distrital){
      $tabla='';
      $tabla.='
      <div class="viewport-container">
          <br>
          <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Formulario</button>
          <div class="page_horizontal">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual3"></span>
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DEL PERFIL EPIDEMIOLOGICO</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Objetivo del instrumento</div>
              <div style="border: 1px solid #ccc; padding: 10px; font-size: 8.5pt; margin-bottom: 20px;">
                  Recolectar, organizar y analizar información epidemiológica de la población afiliada, identificando tendencias de morbilidad, mortalidad y factores de riesgo en el periodo '.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'.
              </div>
              

              <div style="font-weight: bold; margin-bottom: 10px;">2. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Externa)</div>
                '.$this->tabla_form3tp_perfil($get_form_distrital[0]['dist_id'],1).'

              <div style="font-weight: bold; margin-bottom: 10px;">3. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Hospitalaria)</div>
                '.$this->tabla_form3tp_perfil($get_form_distrital[0]['dist_id'],2).'

              <div style="font-weight: bold; margin-bottom: 10px;">4. Perfil de mortalidad (principales causas)</div>
                  '.$this->tabla_form3tp_perfil($get_form_distrital[0]['dist_id'],3).'
                  
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="3">

              <div style="margin-top: 30px;">
                  <strong>3. Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="3"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion3']).'</textarea>
              </div>

              <!-- Pie de página -->
              <div class="footer-nacional">
                  DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
              </div>
            </div>
          </div>
          <hr>
        </div>

        <script>
          document.getElementById("fecha-actual3").innerText = new Date().toLocaleDateString();
        </script>
        <script>
            var timer_perfil = null;
            var base_url = "' . base_url() . '";

            // 1. FUNCIÓN CENTRALIZADA DE GUARDADO
            function ejecutarGuardado($el) {
              //alert($el.data("form")+"-"+$el.data("gestion")+"-"+$el.data("nro")+"-"+$el.data("tp_perfil")+"-"+$el.data("col")+"-"+$el.val())
                $("#status").stop(true, true).show().text("Sincronizando...").css("color", "blue");

                $.ajax({
                    url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form3",
                    type: "POST",
                    data: {
                        form_id: $el.data("form"),
                        gestion: $el.data("gestion"),
                        nro_posicion: $el.data("nro"),
                        categoria: $el.data("tp_perfil"),
                        columna: $el.data("col"),
                        valor: $el.val()
                    },
                    success: function(resp) {
                        $("#status").text("Guardado ✓").css("color", "green").fadeOut(2000);
                        $("#toast-notificacion").fadeIn(400).delay(1500).fadeOut(400);
                    },
                    error: function() {
                        $("#status").text("Error de red").css("color", "red");
                    }
                });
            }

            $(document).ready(function() {
                // 2. EVENTO PARA INPUTS MANUALES (Nº Casos y Detalle Causa)
                $(".auto-save, .input-perfil").on("keyup change", function() {
                    var $el = $(this);

                    clearTimeout(timer_perfil);
                    timer_perfil = setTimeout(function() {
                        ejecutarGuardado($el);
                    }, 800);
                });

                // 3. LIMPIEZA DE CEROS
                $(".auto-save[type=\'number\']").on("focus", function() {
                    if ($(this).val() == "0") $(this).val("");
                }).on("blur", function() {
                    if ($(this).val() === "") $(this).val("0");
                });
            });

            // 4. LÓGICA DEL MODAL BUSCADOR
            var gestionActual, nroActual;

            function abrirBuscador(gestion, nro, tp) {
                gestionActual = gestion;
                nroActual = nro;
                tpActual = tp; 

                // Definimos los títulos según el tipo de perfil (tp)
                var titulo = "";
                switch(parseInt(tp)) {
                    case 1:
                        titulo = "BUSCADOR CIE-10 (MORBILIDAD: Consulta Externa)";
                        break;
                    case 2:
                        titulo = "BUSCADOR CIE-10 (MORBILIDAD: Hospitalaria)";
                        break;
                    case 3:
                        titulo = "BUSCADOR CIE-10 (MORTALIDAD)";
                        break;
                    default:
                        titulo = "BUSCADOR CIE-10";
                }

                // Actualizamos el título en el modal
                $("#modalBuscador .modal-title b").text(titulo);

                // Mostramos el modal
                $("#modalBuscador").modal("show");
                setTimeout(function(){ 
                    $("#txtBuscar").val("").focus(); 
                }, 500);
                $("#listaResultados").html("");
            }

            function filtrarCIE10() {
                var query = $("#txtBuscar").val();
                if(query.length < 2) return;

                $.get(base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/buscar_cie10_ajax?q=" + query, function(data) {
                    var resp = JSON.parse(data);
                    var html = "<div class=\'list-group\'>";
                    $.each(resp, function(i, item) {
                        html += "<a href=\'javascript:void(0)\' class=\'list-group-item\' onclick=\'seleccionarEnfermedad(\"" + item.id + "\", \"" + item.text + "\")\'>" + item.text + "</a>";
                    });
                    html += "</div>";
                    $("#listaResultados").html(html);
                });
            }

            function seleccionarEnfermedad(id, texto) {
                // 1. Identificamos los elementos
                var idCompuesto = "#id_" + tpActual + "_" + gestionActual + "_" + nroActual;
                var descCompuesto = "#desc_" + tpActual + "_" + gestionActual + "_" + nroActual;

                var inputId   = $(idCompuesto); 
                var inputDesc = $(descCompuesto); 
                
                // 2. Actualizamos el valor del input
                inputId.val(id);
                inputDesc.val(texto); 

                // 3. ACTUALIZACIÓN DEL TITLE: Esto permite ver la descripción completa al pasar el mouse
                inputDesc.attr("title", texto); 
                
                // 4. Actualizamos metadatos y guardamos
                inputId.attr("data-tp_perfil", tpActual);
                $("#modalBuscador").modal("hide");
                
                ejecutarGuardado(inputId);
            }
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
          </script>';
        return $tabla;
    }


    public function tabla_form3tp_perfil($dist_id,$tp){
      $detalle_form3=$this->model_diagnosticopei->get_formulario_N3($dist_id,$tp); /// listado de gestiones
      $cie10_list=$this->model_diagnosticopei->get_listado_cie10();
      $tabla='';
      $tabla.='
                <div class="modal fade" id="modalBuscador" tabindex="-1" role="dialog" style="z-index: 9999;">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header" style="background:#FFC000">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title"><b>BUSCADOR CIE-10 (TIPO: '.$tp.')</b></h4>
                            </div>
                            <div class="modal-body">
                                <input type="text" id="txtBuscar" class="form-control" placeholder="Escriba el código o enfermedad..." onkeyup="filtrarCIE10()" >
                                <hr>
                                <div id="listaResultados" style="max-height: 350px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <table>
                  <thead>
                    <tr style="text-align:center;">
                        <th rowspan="3" class="nro-col">N.- '.$tp.'</th>
                        <th colspan="3" style="text-align:center;">2021</th>
                        <th colspan="3" style="text-align:center;">2022</th>
                        <th colspan="3" style="text-align:center;">2023</th>
                        <th colspan="3" style="text-align:center;">2024</th>
                        <th colspan="3" style="text-align:center;">2025</th>
                    </tr>
                    <tr>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CIE-10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CIE-10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CIE-10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CIE-10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CIE-10</th><th style="width:10%;">10 primeras causas</th>
                    </tr>
                  </thead>
                <tbody>';
                   foreach($detalle_form3 as $row){
                    $tabla.='
                    <tr>
                      <td class="nro-label">'.$row['nro'].'</td>';
                      // Bucle para generar los 5 años (2021 al 2025)
                       for ($anio = 2021; $anio <= 2025; $anio++) {
                      $val_casos = $row['nro_casos_'.$anio];
                      $val_ce_id = $row['ce_id_'.$anio]; 
                      $val_causa = $row['causa_'.$anio];
                      $cod_cie   = $row['codigo_cie_'.$anio];

                      $tabla .= '
                      <!-- COLUMNA CASOS -->
                      <td>
                          <input type="number" class="auto-save" min="0"
                                 data-form="'.$row['form_id'].'" data-tp_perfil="'.$tp.'" 
                                 data-nro="'.$row['nro'].'" data-gestion="'.$anio.'" 
                                 data-col="nro_casos" value="'.$val_casos.'" 
                                 style="width:100%; text-align:center; border:none;">
                      </td>
                      
                      <!-- COLUMNA BUSCADOR (ID y CODIGO) -->
                      <td>
                          <div class="input-group" style="display: flex; width: 100%;">
                              <input type="text" 
                                      class="form-control input-sm" 
                                      id="desc_' . $tp . '_' . $anio . '_' . $row['nro'] . '"  
                                      value="' . $cod_cie . '" 
                                      readonly 
                                      title="' . $cod_cie . '" 
                                      style="width: 80%; font-size: 7.5pt; height: 24px; border-radius: 4px 0 0 4px;">
                              
                              <button type="button" class="btn btn-primary btn-xs" style="height: 24px; width: 20%;"
                                      onclick="abrirBuscador(\''.$anio.'\', \''.$row['nro'].'\', \''.$tp.'\')">
                                  <i class="fa fa-search"></i>
                              </button>

                              <input type="hidden" class="ce_id_input auto-save" id="id_' . $tp . '_' . $anio . '_' . $row['nro'] . '" 
                                     data-gestion="'.$anio.'" data-nro="'.$row['nro'].'"
                                     data-form="'.$row['form_id'].'" data-tp_perfil="'.$tp.'"
                                     data-col="ce_id" value="'.$val_ce_id.'">
                          </div>
                      </td>

                      <!-- COLUMNA DETALLE TEXTO -->
                      <td>
                          <input type="text" class="input-perfil auto-save" 
                                 data-gestion="'.$anio.'" data-nro="'.$row['nro'].'" 
                                 data-tp_perfil="'.$tp.'" data-form="'.$row['form_id'].'" 
                                 data-col="detalle_causa" value="'.$val_causa.'"
                                 style="width:100%; font-size: 8pt; border:none;">
                      </td>';
                  }
                            
                    $tabla.='</tr>';
                  }
                  $tabla.='
                  </tbody>
              </table>';

      return $tabla;
    }
    //// ------------------------- END FORM 3


    /*------- Detalle formulario N 2 -------*/
    public function formulario_N4($get_form_distrital){
      $tabla='';
      $tabla.='
      <div class="viewport-container">
          <br>
          <button class="btn-imprimir" onclick="window.print()">🖨️ Imprimir Formulario</button>
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual3"></span>
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE INFRAESTRUCTURA</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Objetivo</div>
              <div style="border: 1px solid #ccc; padding: 10px; font-size: 8.5pt; margin-bottom: 20px;">
                  Identificar, registrar y evaluar las condiciones de la infraestructura de los establecimientos de salud, para determinar su capacidad operativa y soporte a la demanda poblacional.
              </div>
              

              <div style="font-weight: bold; margin-bottom: 10px;">2. Matriz de inventario de establecimientos PRIMER NIVEL</div>
                '.$this->tabla_form4Tp_infraestructura($get_form_distrital[0]['dist_id'],1).'

              <div style="font-weight: bold; margin-bottom: 10px;">3. Matriz de inventario de establecimientos SEGUNDO Y TERCER NIVEL</div>
                '.$this->tabla_form4Tp_infraestructura($get_form_distrital[0]['dist_id'],2).'
                  
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="4">

              <div style="margin-top: 30px;">
                  <strong>3. Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="4"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion4']).'</textarea>
              </div>

              <!-- Pie de página -->
              <div class="footer-nacional">
                  DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
              </div>
            </div>
          </div>
          <hr>
        </div>


        
        <script>
          document.getElementById("fecha-actual3").innerText = new Date().toLocaleDateString();
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
          </script>';
        return $tabla;
    }

 public function tabla_form4Tp_infraestructura($dist_id,$tp){
     // $detalle_form3=$this->model_diagnosticopei->get_formulario_N3($dist_id,$tp); /// listado de gestiones
     // $cie10_list=$this->model_diagnosticopei->get_listado_cie10();
      $tabla='';
      $tabla.='
          <table>
            <thead>
              <tr>
                
                  <th style="width:30%;">Establecimiento</th>
                  <th style="width:15%;">Tipo</th>
                  <th style="width:25%;">Ubicación</th>
                  <th style="width:15%;">Nro. consultorios fisicos</th>
                  <th style="width:15%;">1. Propia<br>2. Alquilada<br>3. Otros (detalle)</th>
              </tr>
            </thead>
          <tbody>';
            
            $tabla.='
            </tbody>
        </table>';

      return $tabla;
    }


    /*------- Trabajando -------*/
    public function trabajando($get_form_distrital){
      $tabla='';
      $tabla.='TRABAJANDO';

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
                
                .page_horizontal { 
                    background-color: white; 
                    /* Invertimos: Ancho ahora es 11 pulgadas y alto 8.5 */
                    width: 22in; 
                    min-width: 22in; /* Mantiene el ancho horizontal en celulares con scroll */
                    height: 21in; 
                    padding: 0.4in 0.5in; /* Reducimos un poco el padding para ganar espacio */
                    box-sizing: border-box; 
                    position: relative; 
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                    display: flex;
                    flex-direction: column;
                    border: 1px solid #ccc;
                    overflow: hidden; /* Evita que el contenido "chorree" fuera de la hoja */
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
?>