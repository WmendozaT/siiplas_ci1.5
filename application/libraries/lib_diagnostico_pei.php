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



    /*---- Detalle formulario N 1 - Poblacion Afiliada ----*/
    public function formulario_N1($get_form_distrital){
      $detalle_form1=$this->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $tabla='';
      $tabla.='
        <div class="viewport-container">
            <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/1/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
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

              <div style="font-weight: bold; margin-bottom: 10px;">2. Relevamiento de poblacion afiliada ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</div>
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
                  • Recolección de datos.
                  • Extraer información anual por cada categoría ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].').<br>
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
                      style="width: 100%; height: 150px; resize: none;"
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
          </script>
          <script>
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


    /*---- Detalle formulario N 1-1 - Poblacion Afiliada por Grupo etareo ----*/
    public function formulario_N1_1($get_form_distrital){
      $detalle_form1=$this->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $detalle_form1_etareo=$this->model_diagnosticopei->get_formulario_N1_etareo($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $tabla='';
      $tabla.='
      <style>
          .btn-disabled {
              background-color: #ccc !important;
              color: #666 !important;
              cursor: not-allowed !important;
              pointer-events: none; /* Bloquea el clic */
              border-color: #bbb !important;
          }
      </style>
        <div class="viewport-container">
            <div style="padding: 15px 0;" class="no-print">
                <a href="javascript:void(0);" 
                   id="btn-reporte"
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/2/".$get_form_distrital[0]['form_id']).'\');" 
                   class="btn-imprimir btn-disabled" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>
          <div class="page_horizontal_corto">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual1_1"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE LA POBLACIÓN PROTEGIDA POR GRUPOS ETAREOS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Relevamiento de poblacion afiliada por grupo etareo ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</div>
              <table>
                  <thead>
                    <tr style="text-align:center;">
                        <th rowspan="3" class="nro-col">GRUPO ETAREO</th>
                        <th colspan="3" style="text-align:center;">2021</th>
                        <th colspan="3" style="text-align:center;">2022</th>
                        <th colspan="3" style="text-align:center;">2023</th>
                        <th colspan="3" style="text-align:center;">2024</th>
                        <th colspan="3" style="text-align:center;">2025</th>
                    </tr>
                    <tr>
                        <th style="width:6%;">Nº Masculino</th><th style="width:6%;">Nº Femenino</th><th style="width:6%;">Total </th>
                        <th style="width:6%;">Nº Masculino</th><th style="width:6%;">Nº Femenino</th><th style="width:6%;">Total </th>
                        <th style="width:6%;">Nº Masculino</th><th style="width:6%;">Nº Femenino</th><th style="width:6%;">Total </th>
                        <th style="width:6%;">Nº Masculino</th><th style="width:6%;">Nº Femenino</th><th style="width:6%;">Total </th>
                        <th style="width:6%;">Nº Masculino</th><th style="width:6%;">Nº Femenino</th><th style="width:6%;">Total </th>
                    </tr>
                  </thead>
                  <tbody>';
                   foreach($detalle_form1_etareo as $row){
                    $tabla.='
                    <tr>
                      <td class="nro-label" style="font-size:15px;"><b>'.$row['grupo_etareo'].'</b></td>';
                      // Bucle para generar los 5 años (2021 al 2025)
                       for ($anio = 2021; $anio <= 2025; $anio++) {
                          $m = $row['m_'.$anio];
                          $f = $row['f_'.$anio];
                          $t = $row['t_'.$anio];

                          $tabla.='
                          <!-- Masculino -->
                          <td>
                              <input type="number" class="auto-save" min="0"
                                  value="'.$m.'" 
                                  data-form="'.$row['form_id'].'" 
                                  data-dist="'.$row['dist_id'].'" 
                                  data-eta="'.$row['eta_id'].'" 
                                  data-gestion="'.$anio.'" 
                                  data-campo="nro_masculino" 
                                  style="width:100%;">
                          </td>
                          <!-- Femenino -->
                          <td>
                              <input type="number" class="auto-save" min="0" 
                                  value="'.$f.'" 
                                  data-form="'.$row['form_id'].'" 
                                  data-dist="'.$row['dist_id'].'" 
                                  data-eta="'.$row['eta_id'].'" 
                                  data-gestion="'.$anio.'" 
                                  data-campo="nro_femenino" 
                                  style="width:100%;">
                          </td>
                          <!-- Total (Solo lectura) -->
                          <td>
                              <input type="number" class="form-control total-'.$anio.'-'.$row['eta_id'].'" 
                                  value="'.$t.'" 
                                  readonly 
                                  style="width:100%; background-color: #eee; font-weight: bold;">
                          </td>';
                      }
                            
                    $tabla.='</tr>';
                   }
                  $tabla.='
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align:left;">TOTALES POR GESTIÓN:</td>';
                        for ($anio = 2021; $anio <= 2025; $anio++) {
                            $tabla.='
                            <td colspan="2" style="text-align:center;">Gestión '.$anio.'</td>
                            <td style="text-align:center; background-color: #d9edf7;">
                                <span id="suma_total_'.$anio.'" style="font-size: 16px; color: #000;">0.00</span>
                            </td>';
                        }
                    $tabla.='
                    </tr>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                      <td style="text-align:left;">REFERENCIA FORM. 1</td>';
                      foreach($detalle_form1 as $f1) {
                          $tabla .= '
                          <td colspan="2" style="text-align:center; font-size:11px;">
                              Form. N° 1 Gestión: '.$f1['gestion'].'
                          </td>
                          <td style="text-align:center;">
                              <!-- ID corregido con el año -->
                              <span id="ref_f1_'.$f1['gestion'].'" style="font-size: 16px; color: #000;">'.number_format($f1['total_gestion'], 2, '.', ',').'</span>
                          </td>';
                      }
                  $tabla.='
                    </tr>
                    <tr style="background-color: #f2f2f2; font-weight: bold;">
                        <td style="text-align:left;">DIFERENCIA (F1 - ETÁREOS)</td>';
                        for ($anio = 2021; $anio <= 2025; $anio++) {
                            $tabla.='
                            <td colspan="2" style="text-align:center; font-size:11px; color: #555;">Faltante / Sobrante</td>
                            <td style="text-align:center;" id="celda_diff_'.$anio.'">
                                <span id="diff_'.$anio.'" style="font-size: 16px;">0.00</span>
                            </td>';
                        }
                    $tabla.='</tr>
                  </tbody>
              </table>

              <!-- Pie de página -->
              <div class="footer-nacional">
                  DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIIPLAS
              </div>
          </div>
          <hr>
        </div>
        <script>
          document.getElementById("fecha-actual1_1").innerText = new Date().toLocaleDateString();
        </script>

        <script>
          $(document).ready(function() {
              var timer = null;
              var base_url = "'.base_url().'";

              // --- FUNCIÓN PARA SUMAR TODAS LAS FILAS POR GESTIÓN ---
              function calcularTotalesPorGestion() {
                  for (var anio = 2021; anio <= 2025; anio++) {
                      var suma_gestion = 0;
                      // Sumamos todos los inputs de total de ese año específico
                      $("input[class*=\'total-" + anio + "-\']").each(function() {
                          suma_gestion += parseFloat($(this).val()) || 0;
                      });
                      // Actualizamos el span en la fila de abajo
                      $("#suma_total_" + anio).text(suma_gestion.toLocaleString(\'en-US\', {minimumFractionDigits: 2}));
                  }
              }

              // Ejecutar al cargar la página por primera vez
              calcularTotalesPorGestion();

              function verificarIncoherencias() {
                var errores = 0;
                var gestiones_vacias = 0;

                for (var anio = 2021; anio <= 2025; anio++) {
                    var total_etareo = parseFloat($("#suma_total_" + anio).text().replace(/,/g, "")) || 0;
                    var total_f1 = parseFloat($("#ref_f1_" + anio).text().replace(/,/g, "")) || 0;
                    
                    var diferencia = (total_f1 - total_etareo).toFixed(2);
                    var $span_diff = $("#diff_" + anio);
                    var $celda_diff = $("#celda_diff_" + anio);
                    
                    $span_diff.text(diferencia);

                    // CONDICIÓN 1: Verificar si hay diferencia con Form 1
                    if (Math.abs(diferencia) > 0.01) {
                        $celda_diff.css("background-color", "#f8d7da"); 
                        errores++;
                    } 
                    // CONDICIÓN 2: Verificar si el total es 0 (está vacío)
                    else if (total_etareo === 0) {
                        $celda_diff.css("background-color", "#fff3cd"); // Amarillo: Sin datos
                        $span_diff.text("Sin datos").css("color", "#856404");
                        gestiones_vacias++;
                    }
                    else {
                        $celda_diff.css("background-color", "#d4edda"); 
                        $span_diff.text("0.00 ✅").css("color", "#28a745");
                    }
                }

                var $btn = $("#btn-reporte");
                
                // Solo habilitar si NO hay errores Y al menos una gestión tiene DATOS (> 0)
                // Si quieres que las 5 gestiones tengan datos obligatoriamente, usa: (gestiones_vacias === 0)
                if (errores === 0 && gestiones_vacias < 5) {
                    $btn.removeClass("btn-disabled")
                        .attr("title", "Imprimir Formulario")
                        .css("pointer-events", "auto");
                } else {
                    $btn.addClass("btn-disabled")
                        .css("pointer-events", "none");
                        
                    if (gestiones_vacias === 5) {
                        $btn.attr("title", "No hay datos registrados para imprimir");
                    } else {
                        $btn.attr("title", "Existen diferencias con el Formulario N° 1");
                    }
                }
            }

            verificarIncoherencias();

              $(".auto-save").on("keyup change", function() {
                  var $input = $(this);
                  var $fila = $input.closest("tr");
                  var gestion = $input.data("gestion");
                  var eta_id  = $input.data("eta");

                  // 1. Recalcular total de la FILA (M + F)
                  var m = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_masculino\']").val()) || 0;
                  var f = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_femenino\']").val()) || 0;
                  var suma = (m + f).toFixed(2);
                  
                  $fila.find(".total-" + gestion + "-" + eta_id).val(suma);

                  // --- LLAMADA A LA SUMATORIA GLOBAL ---
                  calcularTotalesPorGestion();
                  verificarIncoherencias();

                  clearTimeout(timer);
                  timer = setTimeout(function() {
                      $.ajax({
                          url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form1_etareo",
                          type: "POST",
                          dataType: "json",
                          data: {
                              form_id: $input.data("form"),
                              dist_id: $input.data("dist"),
                              eta_id:  eta_id,
                              gestion: gestion,
                              campo:   $input.data("campo"),
                              valor:   $input.val()
                          },
                          success: function(resp) {
                              var $toast = $("#toast-notificacion");
                              if(resp.status == "success") {
                                  $input.css("background-color", "#d4edda");
                                  $toast.text(resp.msg).css({"background-color": "#28a745", "color": "white"}).fadeIn(400).delay(1000).fadeOut(400);
                              } else {
                                  $input.css("background-color", "#f8d7da").val("");
                                  
                                  // Recalcular totales si hubo error y se borró el valor
                                  var m2 = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_masculino\']").val()) || 0;
                                  var f2 = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_femenino\']").val()) || 0;
                                  $fila.find(".total-" + gestion + "-" + eta_id).val((m2 + f2).toFixed(2));
                                  
                                  calcularTotalesPorGestion(); // Actualizar sumatoria global de nuevo

                                  $toast.text("❌ " + resp.msg).css({"background-color": "#dc3545", "color": "white"}).fadeIn(400).delay(3000).fadeOut(400);
                              }
                              setTimeout(function(){ $input.css("background-color", ""); }, 1500);
                          },
                          error: function() {
                              $input.css("background-color", "#f8d7da").val("");
                              calcularTotalesPorGestion();
                              alert("Error de conexión");
                          }
                      });
                  }, 800); 
              });
          });
          </script>

          <script>
              $(document).ready(function() {
                var timer_perfil = null;
                var base_url = "'.base_url().'"; 

                // 2. EVENTO PARA INPUTS MANUALES
                $(".auto-save, .auto-save-infra").on("keyup change", function() {
                    var $el = $(this);

                    // VALIDACIÓN DE NEGATIVOS
                    if ($el.attr("type") === "number") {
                        if (parseFloat($el.val()) < 0) {
                            $el.val(0); // Forzamos a cero
                            return false; // Bloqueamos el guardado
                        }
                    }

                    clearTimeout(timer_perfil);
                    timer_perfil = setTimeout(function() {
                        // Aquí llamas a tu función de guardado
                        if(typeof ejecutarGuardado === "function") {
                            ejecutarGuardado($el);
                        } else {
                            // Si no tienes la función externa, el código AJAX iría aquí
                            console.log("Guardando dato...");
                        }
                    }, 800);
                });

                // 3. LIMPIEZA DE CEROS
                $(".auto-save[type=\'number\']").on("focus", function() {
                    if ($(this).val() == "0") $(this).val("");
                }).on("blur", function() {
                    if ($(this).val() === "") $(this).val("0");
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
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/3/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual2"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
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
          </script>
          <script>
            $(document).ready(function() {
                var timer_perfil = null;
                var base_url = "'.base_url().'"; 

                // 2. EVENTO PARA INPUTS MANUALES
                $(".auto-save, .auto-save-infra").on("keyup change", function() {
                    var $el = $(this);

                    // VALIDACIÓN DE NEGATIVOS
                    if ($el.attr("type") === "number") {
                        if (parseFloat($el.val()) < 0) {
                            $el.val(0); // Forzamos a cero
                            return false; // Bloqueamos el guardado
                        }
                    }

                    clearTimeout(timer_perfil);
                    timer_perfil = setTimeout(function() {
                        // Aquí llamas a tu función de guardado
                        if(typeof ejecutarGuardado === "function") {
                            ejecutarGuardado($el);
                        } else {
                            // Si no tienes la función externa, el código AJAX iría aquí
                            console.log("Guardando dato...");
                        }
                    }, 800);
                });

                // 3. LIMPIEZA DE CEROS
                $(".auto-save[type=\'number\']").on("focus", function() {
                    if ($(this).val() == "0") $(this).val("");
                }).on("blur", function() {
                    if ($(this).val() === "") $(this).val("0");
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
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/4/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>
          <div class="page_horizontal">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual3"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
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

        // LIMPIA LOS VALORES CEROS
        $(document).ready(function() {
            var timer_perfil = null;
            var base_url = "'.base_url().'"; 

            // 2. EVENTO PARA INPUTS MANUALES
            $(".auto-save, .auto-save-infra").on("keyup change", function() {
                var $el = $(this);

                // VALIDACIÓN DE NEGATIVOS
                if ($el.attr("type") === "number") {
                    if (parseFloat($el.val()) < 0) {
                        $el.val(0); // Forzamos a cero
                        return false; // Bloqueamos el guardado
                    }
                }

                clearTimeout(timer_perfil);
                timer_perfil = setTimeout(function() {
                    // Aquí llamas a tu función de guardado
                    if(typeof ejecutarGuardado === "function") {
                        ejecutarGuardado($el);
                    } else {
                        // Si no tienes la función externa, el código AJAX iría aquí
                        console.log("Guardando dato...");
                    }
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
                        <th rowspan="2" class="nro-col">N.- '.$tp.'</th>
                        <th colspan="2" style="text-align:center;">2021</th>
                        <th colspan="2" style="text-align:center;">2022</th>
                        <th colspan="2" style="text-align:center;">2023</th>
                        <th colspan="2" style="text-align:center;">2024</th>
                        <th colspan="2" style="text-align:center;">2025</th>
                    </tr>
                    <tr>
                        <th style="width:3%;">Nº casos</th><th style="width:17%;">Cod. CIE-10</th>
                        <th style="width:3%;">Nº casos</th><th style="width:17%;">Cod. CIE-10</th>
                        <th style="width:3%;">Nº casos</th><th style="width:17%;">Cod. CIE-10</th>
                        <th style="width:3%;">Nº casos</th><th style="width:17%;">Cod. CIE-10</th>
                        <th style="width:3%;">Nº casos</th><th style="width:17%;">Cod. CIE-10</th>
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
                                      style="width: 80%; font-size: 7.5pt; height: 24px; border-radius: 4px 0 0 4px; text-align:left;">
                              
                              <button type="button" class="btn btn-primary btn-xs" style="height: 24px; width: 20%;"
                                      onclick="abrirBuscador(\''.$anio.'\', \''.$row['nro'].'\', \''.$tp.'\')">
                                  <i class="fa fa-search"></i>
                              </button>

                              <input type="hidden" class="ce_id_input auto-save" id="id_' . $tp . '_' . $anio . '_' . $row['nro'] . '" 
                                     data-gestion="'.$anio.'" data-nro="'.$row['nro'].'"
                                     data-form="'.$row['form_id'].'" data-tp_perfil="'.$tp.'"
                                     data-col="ce_id" value="'.$val_ce_id.'">
                          </div>
                      </td>';
                  }
                            
                    $tabla.='</tr>';
                  }
                  $tabla.='
                  </tbody>
              </table>';

      return $tabla;
    }

      /// desactivado anterior completo
      public function tabla_form3tp_perfil2($dist_id,$tp){
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


    /*------- Detalle formulario N 4 Infraestructura -------*/
    public function formulario_N4($get_form_distrital){
      $detalle_form4_1er=$this->model_diagnosticopei->get_infraestructura_por_nivel($get_form_distrital[0]['dist_id'],'1'); /// 1er nivel
      $detalle_form4_2do=$this->model_diagnosticopei->get_infraestructura_por_nivel($get_form_distrital[0]['dist_id'],'2,3'); /// 2 y 3 nivel
      $detalle_form4_otros=$this->model_diagnosticopei->get_otros_infraestructura_por_nivel($get_form_distrital[0]['dist_id']); /// Otros Establecimientos
      
      $nro=count($detalle_form4_1er)+count($detalle_form4_2do);
      $page='page_horizontal_corto';
      if($nro>=13){
        $page='page_horizontal';
      }
      $tabla='';
      $tabla.='
      <div class="viewport-container">
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/5/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>
          <div class="'.$page.'">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual4"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE INFRAESTRUCTURA DE SALUD</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Objetivo</div>
              <div style="border: 1px solid #ccc; padding: 10px; font-size: 8.5pt; margin-bottom: 20px;">
                  Identificar, registrar y evaluar las condiciones de la infraestructura de los establecimientos de salud, para determinar su capacidad operativa y soporte a la demanda poblacional.
              </div>
              
              <div style="font-weight: bold; margin-bottom: 10px;">2. Matriz de inventario de establecimientos de PRIMER NIVEL (segun poa '.$get_form_distrital[0]['g_id_fin'].')</div>
                '.$this->tabla_form4Tp_infraestructura($detalle_form4_1er,1).'';

              if(count($detalle_form4_2do)!=0){
                $tabla.='
                <div style="font-weight: bold; margin-bottom: 10px;">3. Matriz de inventario de establecimientos de SEGUNDO Y TERCER NIVEL (segun poa '.$get_form_distrital[0]['g_id_fin'].')</div>
                '.$this->tabla_form4Tp_infraestructura($detalle_form4_2do,1).'';
              }
              
              $tabla.='
              <div style="font-weight: bold; margin-bottom: 10px;">Otros Establecimientos</div>
              <div style="padding-bottom: 10px;">
                  <button type="button" class="btn btn-success btn-sm" onclick="agregarNuevoEstablecimientoOtros('.$get_form_distrital[0]['form_id'].', '.$get_form_distrital[0]['g_id_fin'].');">
                      <i class="glyphicon glyphicon-plus"></i> Agregar otro Establecimiento
                  </button>
              </div>
                '.$this->tabla_form4Tp_infraestructura($detalle_form4_otros,0).'

              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="4">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
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
          document.getElementById("fecha-actual4").innerText = new Date().toLocaleDateString();
        </script>
       <script>
        $(document).ready(function() {
            var timer_infra = null;
            var base_url = "'.base_url().'"; 

            // 1. BLOQUEAR PEGADO (Corregido el conflicto de comillas)
            $(document).on("paste", ".auto-save-infra", function(e) {
                if ($(this).data("campo") === "ubicacion") {
                    e.preventDefault();
                    var $toast = $("#toast-notificacion");
                    $toast.text("⚠️ No se permite pegar texto en este campo")
                          .css({"background-color": "#dc3545", "color": "white", "display": "block"})
                          .fadeIn(400).delay(2000).fadeOut(400);
                    return false;
                }
            });

            // 2. EVENTO UNIFICADO PARA INPUTS
            $(".auto-save4, .auto-save-infra").on("keyup change", function() {
                var $el = $(this);
                var campo_actual = $el.data("campo"); // Definimos la variable correctamente

                // VALIDACIÓN DE NEGATIVOS
                if ($el.attr("type") === "number") {
                    if (parseFloat($el.val()) < 0) {
                        $el.val(0); 
                        return false; 
                    }
                }

                // VALIDACIÓN DE LONGITUD MÁXIMA
                if (campo_actual === "ubicacion") {
                    if ($el.val().length > 500) {
                        $el.val($el.val().substring(0, 500));
                        return false;
                    }
                }



                var valor = $el.val().trim();

                if (campo_actual === "tipo_situacion") {
                    // Transformación automática de códigos
                    if (valor === "1") {
                        $el.val("PROPIA");
                        valor = "PROPIA";
                    } else if (valor === "2") {
                        $el.val("ALQUILADA");
                        valor = "ALQUILADA";
                    }
                    
                    // Limitar a 100 caracteres si es una descripción (opción 3)
                    if (valor.length > 100) {
                        $el.val(valor.substring(0, 100));
                        valor = valor.substring(0, 100);
                    }
                }

                if (campo_actual === "serv_internet") {
                    // Transformación automática de códigos
                    if (valor === "1") {
                        $el.val("SI");
                        valor = "SI";
                    } else if (valor === "0") {
                        $el.val("NO");
                        valor = "NO";
                    }
                    
                    // Limitar a 100 caracteres si es una descripción (opción 3)
                    if (valor.length > 100) {
                        $el.val(valor.substring(0, 100));
                        valor = valor.substring(0, 100);
                    }
                }

                // INDICADOR VISUAL (Borde naranja)
                $el.css("border-color", "#ffc107");

                clearTimeout(timer_infra);
                timer_infra = setTimeout(function() {
                    $.ajax({
                        url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_infraestructura_form4",
                        type: "POST",
                        dataType: "json",
                        data: {
                            form_id: $el.data("form"),
                            act_id:  $el.data("act"),
                            gestion: $el.data("gestion"),
                            campo:   campo_actual,
                            valor:   valor // <--- Usamos la variable transformada
                        },
                        success: function(resp) {
                            var $toast = $("#toast-notificacion");
                            if(resp.status == "success") {
                                $el.css({"background-color": "#d4edda", "border-color": "#28a745"});
                                $toast.text(resp.msg).css({"background-color": "#28a745", "color": "white"}).fadeIn(400).delay(1000).fadeOut(400);
                            } else {
                                $el.css({"background-color": "#f8d7da", "border-color": "#dc3545"}).val("");
                                $toast.text("❌ " + resp.msg).css({"background-color": "#dc3545", "color": "white"}).fadeIn(400).delay(3000).fadeOut(400);
                            }
                            setTimeout(function(){ 
                                $el.css({"background-color": "", "border-color": ""}); 
                            }, 1500);
                        },
                        error: function() {
                            $el.css({"background-color": "#f8d7da", "border-color": "#dc3545"}).val("");
                            alert("Error de conexión");
                        }
                    });
                }, 800);
            });

            // 4. LIMPIEZA DE CEROS
            $(".auto-save4").on("focus", function() {
                if ($(this).val() == "0") $(this).val("");
            }).on("blur", function() {
                if ($(this).val() === "") $(this).val("0");
            });
        });
        </script>

        ///// Agregando nueva fila para la inscripcion de otros establecimientos
        <script>
        function agregarNuevoEstablecimientoOtros(form_id, gestion) {
            var base_url = "'.base_url().'";
            $.post(base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/nuevo_infra_otro", 
            { form_id: form_id, gestion: gestion }, 
            function(resp) {
                if(resp.status == "success") {
                    var nuevaFila = `
                    <tr id="fila_otro_${resp.id}" style="display:none; background-color: #fcf8e3;">
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="otro_establecimiento" style="text-transform: uppercase;"></td>
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="tipo_establecimiento" style="text-transform: uppercase;"></td>
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="nivel_establecimiento" style="text-transform: uppercase;"></td>
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="ubicacion" style="text-transform: uppercase;"></td>
                        <td><input type="number" class="auto-save5" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="nro_consultorios" min="0" value="0"></td>
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="serv_internet" placeholder="1 , 0"></td>
                        <td><input type="text" class="form-control auto-save-otros" data-id="${resp.id}" data-form="${form_id}" data-gestion="${gestion}" data-campo="tipo_situacion" placeholder="1, 2 o Detalle (3)"></td>
                        <!-- BOTÓN ELIMINAR AJUSTADO -->
                        <td style="text-align:center;">
                            <a href="javascript:void(0);" 
                               onclick="eliminarRegistroOtro(${resp.id});" 
                               class="btn btn-danger btn-xs" 
                               title="Eliminar Registro">
                               <i class="glyphicon glyphicon-trash"></i>
                            </a>
                        </td>
                    </tr>`;

                    $("#tabla_otros_body").append(nuevaFila);
                    $("#fila_otro_" + resp.id).fadeIn(600); // Efecto de entrada
                    $("#toast-notificacion").text("✅ Fila lista para registrar").fadeIn().delay(1000).fadeOut();
                }
            }, "json");
        }
        </script>

        //// guarda automaticamente los campos del establecimiento
        <script>
        $(document).ready(function() {
            var timer_otros = null;
            var base_url = "'.base_url().'";

            $(document).on("keyup change", ".auto-save-otros, .auto-save5", function() {
                var $el = $(this);
                var campo_actual = $el.data("campo"); // Definimos la variable campo
                var valor = $el.val().trim();

                // 1. VALIDACIÓN DE NEGATIVOS
                if ($el.attr("type") === "number") {
                    if (parseFloat($el.val()) < 0) {
                        $el.val(0);
                        return false;
                    }
                }

                // 2. VALIDACIÓN DE LONGITUD (UBICACIÓN)
                if (campo_actual === "ubicacion") {
                    if (valor.length > 500) {
                        $el.val(valor.substring(0, 500));
                    }
                }

                // 3. TRANSFORMACIÓN AUTOMÁTICA DE TIPO
                if (campo_actual === "tipo_establecimiento") {
                    var $fila = $el.closest("tr");
                    var $inputNivel = $fila.find(\'input[data-campo="nivel_establecimiento"]\');
                    var nivelVal = "";

                    // 1. Mapeo de valores
                    if (valor === "1") { $el.val("CIS"); nivelVal = "PRIMER NIVEL"; } 
                    else if (valor === "2") { $el.val("CIMFA"); nivelVal = "PRIMER NIVEL"; }
                    else if (valor === "3") { $el.val("PAISE"); nivelVal = "SEGUNDO NIVEL"; }
                    else if (valor === "4") { $el.val("HIS"); nivelVal = "SEGUNDO NIVEL"; }
                    else if (valor === "5") { $el.val("HAIG"); nivelVal = "TERCER NIVEL"; }

                    // 2. Si hay un nivel detectado, bloqueamos firmemente
                    if (nivelVal !== "") {
                        $inputNivel.val(nivelVal);
                        $inputNivel.prop("readonly", true).css({
                            "background-color": "#eeeeee", 
                            "cursor": "not-allowed",
                            "border": "1px solid #ccc"
                        });

                        // Disparar guardado del nivel con retraso para evitar colisión de timers
                        setTimeout(function() {
                            $inputNivel.trigger("change");
                        }, 1200); 
                    } 
                    // 3. Solo si el usuario BORRA el tipo, desbloqueamos el nivel
                    else if (valor === "") {
                        $inputNivel.val("");
                        $inputNivel.prop("readonly", false).css({
                            "background-color": "#ffffff", 
                            "cursor": "text",
                            "border": "1px solid #000"
                        });
                        
                        setTimeout(function() {
                            $inputNivel.trigger("change");
                        }, 1200);
                    }
                }


                // 3. TRANSFORMACIÓN AUTOMÁTICA DE CÓDIGOS (SITUACIÓN)
                if (campo_actual === "tipo_situacion") {
                    if (valor === "1") { $el.val("PROPIA"); } 
                    else if (valor === "2") { $el.val("ALQUILADA"); }
                    
                    if ($el.val().length > 100) {
                        $el.val($el.val().substring(0, 100));
                    }
                }

                // 4. TRANSFORMACIÓN AUTOMÁTICA (INTERNET)
                if (campo_actual === "serv_internet") {
                    if (valor === "1") { $el.val("SI"); } 
                    else if (valor === "0") { $el.val("NO"); }
                }

                // INDICADOR VISUAL
                $el.css("border-color", "#ffc107");

                // TEMPORIZADOR DE GUARDADO
                clearTimeout(timer_otros);
                timer_otros = setTimeout(function() {
                    $.ajax({
                        url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_infra_otros_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            id:      $el.data("id"),
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            campo:   campo_actual,
                            valor:   $el.val().toUpperCase() // Guardar siempre en MAYÚSCULAS
                        },
                        success: function(resp) {
                            if (resp.status == "success") {
                                $el.css({"background-color": "#d4edda", "border-color": "#28a745"});
                                $("#toast-notificacion").fadeIn(400).delay(1000).fadeOut(400);

                            } else {
                                $el.css("border-color", "#dc3545");
                            }
                            setTimeout(function(){ $el.css("border-color", ""); }, 1000);
                        },
                        error: function() {
                            $el.css("border-color", "#dc3545");
                        }
                    });
                }, 800);
            });

            // LIMPIEZA DE CEROS
            $(document).on("focus", ".auto-save5", function() {
                if ($(this).val() == "0") $(this).val("");
            }).on("blur", ".auto-save5", function() {
                if ($(this).val() === "") $(this).val("0");
            });
        });
        </script>

        <script>
          function eliminarRegistroOtro(id) {
              if (confirm("¿Está seguro de eliminar este registro? Esta acción no se puede deshacer.")) {
                  var base_url = "'.base_url().'";
                  
                  $.ajax({
                      url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/eliminar_infra_otro",
                      type: "POST",
                      dataType: "json",
                      data: { id: id },
                      success: function(resp) {
                          if (resp.status == "success") {
                              // Eliminamos la fila visualmente con un efecto
                              $("#fila_otro_" + id).fadeOut(400, function() {
                                  $(this).remove();
                              });
                              $("#toast-notificacion").text("✅ Registro eliminado").fadeIn().delay(1000).fadeOut();
                          } else {
                              alert("Error: No se pudo eliminar el registro.");
                          }
                      },
                      error: function() {
                          alert("Error de conexión con el servidor.");
                      }
                  });
              }
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

    //// lsiat de establecimientos alineados al poa
    public function tabla_form4Tp_infraestructura($detalle,$tp_infra){
      //// 1 : se encuentra en el poa
      //// 0 : nose encuentra en el poa
      $tabla='';
      $tabla.='
          <table>
            <thead>
              <tr>
                <th style="width:20%; text-align:center;">Establecimiento</th>';
                if($tp_infra==1){
                  $tabla.='<th style="width:10%; text-align:center;">Tipo</th>
                  <th style="width:10%; text-align:center;">Nivel</th>';
                }
                else{
                  $tabla.='<th style="width:10%; text-align:center;">Tipo<br>
                            <small style="font-weight:normal;"><b>(1=CIS, 2=CIMFA, 3=PAISE, 4=HIS, 5=HAIG)</b></small>
                          </th>
                          <th style="width:10%; text-align:center;">Nivel<br>
                            <small style="font-weight:normal;"><b>(1=PRIMER NIVEL, 2=SEGUNDO NIVEL, 3=TERCER NIVEL)</b></small>
                          </th>';
                }
                $tabla.='
                <th style="width:30%; text-align:center;">Ubicación</th>
                <th style="width:10%; text-align:center;">Nro. consultorios fisicos</th>
                <th style="width:10%; text-align:center;">Cuenta con Internet<br>
                  <small style="font-weight:normal;"><b>(1=SI, 0=NO)</b></small>
                </th>
                <th style="width:20%; text-align:center;">Situación Técnico Legal<br>
                  <small style="font-weight:normal;">(1=PROPIA, 2=ALQUILADA, o escriba detalle)</small>
                </th>';
                if($tp_infra==0){
                  $tabla.='<th style="width:5%; text-align:center;"></th>';
                }
                $tabla.='
              </tr>
            </thead>
          ';
          if($tp_infra==1){
            $tabla.='<tbody>';
            foreach($detalle as $row) {
            $tabla .= '<tr>
                <td style="text-align:left;">'.$row['act_descripcion'].'</td>
                <td>'.$row['tipo'].'</td>
                <td>'.$row['nivel'].'</td>
                <td>
                    <input type="text" class="form-control auto-save-infra" 
                        value="'.strtoupper($row['ubicacion']).'" 
                        maxlength="500" 
                        onpaste="return false;"
                        autocomplete="off"
                        data-form="'.$row['form_id'].'" 
                        data-act="'.$row['act_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="ubicacion"
                        style="text-transform: uppercase;" 
                        placeholder="MÁX. 500 CARACTERES">
                </td>
                <td>
                    <input type="number" class="auto-save4" min="0" 
                        value="'.$row['nro_consultorios'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-act="'.$row['act_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="nro_consultorios">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-infra" 
                        value="'.$row['serv_internet'].'" 
                        maxlength="10"
                        data-form="'.$row['form_id'].'" 
                        data-act="'.$row['act_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="serv_internet"
                        placeholder="1 , 0">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-infra" 
                        value="'.$row['tipo_situacion'].'" 
                        maxlength="100"
                        data-form="'.$row['form_id'].'" 
                        data-act="'.$row['act_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="tipo_situacion"
                        placeholder="1, 2 o Detalle (3)">
                </td>
            </tr>';
            }
            $tabla.='</tbody>';
          }
          else{
            $tabla.='<tbody id="tabla_otros_body">';
            foreach($detalle as $row) {
              $readonly = (!empty($row['nivel_establecimiento'])) ? 'readonly style="background-color: #eeeeee; cursor: not-allowed;"' : '';
            $tabla .= '<tr id="fila_otro_'.$row['infra_otro_id'].'">
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.strtoupper($row['otro_establecimiento']).'" 
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="otro_establecimiento"
                        style="text-transform: uppercase;">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.strtoupper($row['tipo_establecimiento']).'" 
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="tipo_establecimiento"
                        style="text-transform: uppercase";
                        placeholder="1, 2, 3, 4, 5">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.strtoupper($row['nivel_establecimiento']).'" 
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'"  
                        data-campo="nivel_establecimiento"
                        '.$readonly.'>
                </td>
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.strtoupper($row['ubicacion']).'" 
                        maxlength="500" 
                        onpaste="return false;"
                        autocomplete="off"
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'"   
                        data-campo="ubicacion"
                        style="text-transform: uppercase;" 
                        placeholder="MÁX. 500 CARACTERES">
                </td>
                <td>
                    <input type="number" class="auto-save5" min="0" 
                        value="'.$row['nro_consultorios'].'" 
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="nro_consultorios">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.$row['serv_internet'].'" 
                        maxlength="10"
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="serv_internet"
                        placeholder="1 , 0">
                </td>
                <td>
                    <input type="text" class="form-control auto-save-otros" 
                        value="'.$row['tipo_situacion'].'" 
                        maxlength="100"
                        data-id="'.$row['infra_otro_id'].'" 
                        data-form="'.$row['form_id'].'" 
                        data-gestion="'.$row['gestion_pei'].'" 
                        data-campo="tipo_situacion"
                        placeholder="1, 2 o Detalle (3)">
                </td>
                <td style="text-align:center;">
                    <a href="javascript:void(0);" 
                       onclick="eliminarRegistroOtro('.$row['infra_otro_id'].');" 
                       class="btn btn-danger btn-xs" 
                       title="Eliminar Registro">
                       <i class="glyphicon glyphicon-trash"></i>
                    </a>
                </td>
            </tr>';
            }
            $tabla.='</tbody>';
          }
            
            $tabla.='
          
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

                /* 2. LA HOJA (Tamaño Carta Largo) */
                .page_long { 
                    background-color: white; 
                    width: 8.5in; 
                    min-width: 8.5in; /* Evita que se encoja en celulares */
                    height: 18in; 
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
                    width: 20in; 
                    min-width: 20in; /* Mantiene el ancho horizontal en celulares con scroll */
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

                .page_horizontal_corto { 
                    background-color: white; 
                    /* Invertimos: Ancho ahora es 11 pulgadas y alto 8.5 */
                    width: 18in; 
                    min-width: 18in; /* Mantiene el ancho horizontal en celulares con scroll */
                    height: 15in; 
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


                  /* Estilo del Botón */
                  .btn-imprimir {
                      display: inline-block;
                      background-color: green;
                      color: white !important;
                      padding: 10px 20px;
                      text-decoration: none !important;
                      border-radius: 4px;
                      font-weight: bold;
                      font-family: Arial, sans-serif;
                      border: 1px solid #green;
                      transition: background 0.3s;
                  }
            </style>';
            return $tabla;
    }
}
?>