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
      $this->entidad   = $this->session->userdata("entidad");
      $this->sistema   = $this->session->userdata("sistema");
      $this->sistema_pie   = $this->session->userdata("sistema_pie");
      $this->usuario   = $this->session->userdata("usuario");
      $this->direccion   = $this->session->userdata("direccion");

    }



    /*---- Detalle formulario N 1 - Poblacion Afiliada ----*/
    public function formulario_N1($get_form_distrital){
      $detalle_form1=$this->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
      $tabla='';
      $tabla.='
        <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/1/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
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
              <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
                  <thead>
                      <tr style="background: #fdfdfd; color: #666; font-size: 11px;">
                          <th style="width: 20%; text-align:center; padding: 10px;">GESTIÓN</th>
                          <th style="width: 20%; text-align:center;">COTIZANTES TITULARES</th>
                          <th style="width: 20%; text-align:center;">COTIZANTES PASIVOS</th>
                          <th style="width: 20%; text-align:center;">BENEFICIARIOS</th>
                          <th style="width: 220%; text-align:center; background: #f5f5f5;">TOTAL GESTIÓN</th>
                      </tr>
                  </thead>
                  <tbody>';

                  foreach($detalle_form1 as $row){
                      $tabla.='
                      <tr class="fila-dato">
                          <td style="font-size: 13px; text-align: center; vertical-align: middle; background: #f9f9f9;">
                              <b>'.$row['gestion'].'</b>
                          </td>
                          <td style="padding: 8px;">
                              <input type="number" 
                                     class="form-control auto-save limpiar-cero" 
                                     style="text-align: right; font-weight: 500; border-radius: 4px;"
                                     min="0"
                                     max="999999999"
                                     oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
                                     onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 9"
                                     data-form="'.$row['form_id'].'" 
                                     data-gestion="'.$row['gestion'].'" 
                                     data-col="nro_cot_tit" 
                                     value="'.$row['titulares'].'">
                          </td>
                          <td style="padding: 8px;">
                              <input type="number" 
                                     class="form-control auto-save limpiar-cero" 
                                     style="text-align: right; font-weight: 500; border-radius: 4px;"
                                     min="0"
                                     max="999999999"
                                     oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
                                     onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 9"
                                     data-form="'.$row['form_id'].'" 
                                     data-gestion="'.$row['gestion'].'" 
                                     data-col="nro_cot_pas" 
                                     value="'.$row['pasivos'].'">
                          </td>
                          <td style="padding: 8px;">
                              <input type="number" 
                                     class="form-control auto-save limpiar-cero" 
                                     style="text-align: right; font-weight: 500; border-radius: 4px;"
                                     min="0"
                                     max="999999999"
                                     oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
                                     onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 9"
                                     data-form="'.$row['form_id'].'" 
                                     data-gestion="'.$row['gestion'].'" 
                                     data-col="nro_cot_ben" 
                                     value="'.$row['beneficiarios'].'">
                          </td>
                          <td class="total-gestion-val" style="font-weight:bold; font-size: 14px; text-align: right; vertical-align: middle; background-color: #f5f5f5; color: #333; padding-right: 15px;">
                              '.number_format($row['total_gestion'], 0, '.', ',').'
                          </td>
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
            var base_url = "'.base_url().'"; 

            // --- A. LIMPIEZA DE CEROS AL ENTRAR ---
            $(document).on("focus", ".limpiar-cero", function() {
                var $el = $(this);
                if (parseInt($el.val()) === 0) $el.val("");
                $el.select();
            });

            $(document).on("blur", ".limpiar-cero", function() {
                var $el = $(this);
                if ($el.val() === "" || $el.val() === null) $el.val("0");
            });

            // --- B. PROCESO DE GUARDADO Y CÁLCULO ---
            $(".auto-save").on("keyup change", function() {
                var $input = $(this);
                var $fila = $input.closest("tr");
                
                // 1. Validación de 9 dígitos
                if ($input.val().length > 9) {
                    $input.val($input.val().slice(0, 9));
                }

                // 2. Cálculo de Totales Visuales
                var t = parseInt($fila.find("input[data-col=nro_cot_tit]").val()) || 0;
                var p = parseInt($fila.find("input[data-col=nro_cot_pas]").val()) || 0;
                var b = parseInt($fila.find("input[data-col=nro_cot_ben]").val()) || 0;
                var suma = t + p + b;
                
                // Formateo con comas (estilo 1,250,000)
                $fila.find(".total-gestion-val").text(suma.toLocaleString("en-US"));

                // 3. Temporizador independiente
                clearTimeout($input.data("h_timer"));

                var t_id = setTimeout(function() {
                    $input.css("border-color", "#ffc107"); // Naranja: Procesando

                    $.ajax({
                        url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form1",
                        type: "POST",
                        dataType: "json", // Esperamos JSON del controlador
                        data: {
                            form_id: $input.data("form"),
                            gestion: $input.data("gestion"),
                            columna: $input.data("col"),
                            valor: $input.val()
                        },
                        success: function(resp) {
                            if(resp.status === "success") {
                                $input.css("border-color", "green");
                                $("#toast-notificacion").text("✅ Guardado").stop(true,true).fadeIn(200).delay(800).fadeOut(200);
                            } else {
                                $input.css("border-color", "red");
                            }
                            setTimeout(function(){ $input.css("border-color", ""); }, 1500);
                        },
                        error: function() {
                            $input.css("border-color", "red");
                        }
                    });
                }, 600); 

                $input.data("h_timer", t_id);
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
         ';
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
        <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
            <div style="padding: 15px 0;" class="no-print">
                <a href="javascript:void(0);" 
                   id="btn-reporte"
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/2/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir btn-disabled" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page_horizontal_corto">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual1_1"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
                  <h1><b>DIAGNÓSTICO DE LA POBLACIÓN PROTEGIDA POR GRUPOS ETAREOS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Relevamiento de poblacion afiliada por grupo etareo ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</div>
              <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
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
                      <td class="nro-label" style="font-size:12px; width:5%;"><b>'.$row['grupo_etareo'].'</b></td>';
                      
                      for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $m = round($row['m_'.$anio],2);
                          $f = round($row['f_'.$anio],2);
                          $t = round($row['t_'.$anio],2);

                          $tabla.='
                          <!-- Masculino -->
                          <td>
                              <input type="number" class="form-control auto-save" 
                                  min="0" 
                                  max="9999"
                                  style="text-align: right;"
                                  oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5);"
                                  onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 5"
                                  value="'.$m.'" 
                                  data-form="'.$row['form_id'].'" 
                                  data-dist="'.$row['dist_id'].'" 
                                  data-eta="'.$row['eta_id'].'" 
                                  data-gestion="'.$anio.'" 
                                  data-campo="nro_masculino" 
                                  style="width:55px; text-align:right;">
                          </td>
                          <!-- Femenino -->
                          <td>
                              <input type="number" class="form-control auto-save" 
                                  min="0" 
                                  max="9999"
                                  style="text-align: right;"
                                  oninput="if(this.value.length > 5) this.value = this.value.slice(0, 5);"
                                  onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 5"
                                  value="'.$f.'" 
                                  data-form="'.$row['form_id'].'" 
                                  data-dist="'.$row['dist_id'].'" 
                                  data-eta="'.$row['eta_id'].'" 
                                  data-gestion="'.$anio.'" 
                                  data-campo="nro_femenino" 
                                  style="width:55px; text-align:right;">
                          </td>
                          <!-- Total Fila (Calculado por JS) -->

                          <td style="background-color: #f2f2f2;">
                              <input type="text" class="form-control total-fila-etareo total-'.$anio.'-'.$row['eta_id'].'" 
                                  value="'.$t.'" 
                                  readonly 
                                  style="width:60px; border:none; background:transparent; text-align:right; font-weight:bold;">
                          </td>';
                      }
                    $tabla.='</tr>';
                }
                  $tabla.='
                    <tr style="background: #eee; font-weight: bold; border-top: 2px solid #ccc;">
                        <td style="text-align:left;">TOTALES POR GESTIÓN:</td>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                            $tabla.='
                            <td colspan="2" style="text-align:center;">Gestión '.$anio.'</td>
                            <td style="text-align:right; background-color: #d9edf7;">
                                <span id="suma_total_'.$anio.'" style="font-size: 13px; color: #000;">0.00</span>
                            </td>';
                        }
                    $tabla.='
                    </tr>
                    <tr style="background: #fafafa; font-size: 10px;">
                      <td style="text-align:left;">REFERENCIA FORM. 1</td>';
                      foreach($detalle_form1 as $f1) {
                          $tabla .= '
                          <td colspan="2" style="text-align:center; font-size:11px;">
                              Form. N° 1 Gestión: '.$f1['gestion'].'
                          </td>
                          <td style="text-align:right;">
                              <!-- ID corregido con el año -->
                              <span id="ref_f1_'.$f1['gestion'].'" style="font-size: 13px; color: #000;">'.number_format($f1['total_gestion'], 2, '.', ',').'</span>
                          </td>';
                      }
                  $tabla.='
                    </tr>
                    <tr style="background: #fff;">
                        <td style="text-align:left;">DIFERENCIA (F1 - ETÁREOS)</td>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                            $tabla.='
                            <td colspan="2" style="text-align:center; font-size:11px; color: #555;">Faltante / Sobrante</td>
                            <td style="text-align:right;" id="celda_diff_'.$anio.'">
                                <span id="diff_'.$anio.'" style="font-size: 13px;">0.00</span>
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
            var base_url = "'.base_url().'";

            // --- FUNCIÓN PARA SUMAR TODAS LAS FILAS POR GESTIÓN ---
            function calcularTotalesPorGestion() {
                for (var anio = 2021; anio <= 2025; anio++) {
                    var suma_gestion = 0;
                    $("input[class*=\'total-" + anio + "-\']").each(function() {
                        suma_gestion += parseFloat($(this).val()) || 0;
                    });
                    $("#suma_total_" + anio).text(suma_gestion.toLocaleString(\'en-US\', {minimumFractionDigits: 2}));
                }
            }

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
                    if (Math.abs(diferencia) > 0.01) {
                        $celda_diff.css("background-color", "#f8d7da"); 
                        errores++;
                    } else if (total_etareo === 0) {
                        $celda_diff.css("background-color", "#fff3cd");
                        $span_diff.text("Sin datos").css("color", "#856404");
                        gestiones_vacias++;
                    } else {
                        $celda_diff.css("background-color", "#d4edda"); 
                        $span_diff.text("0.00 ✅").css("color", "#28a745");
                    }
                }

                var $btn = $("#btn-reporte");
                if (errores === 0 && gestiones_vacias < 5) {
                    $btn.removeClass("btn-disabled").attr("title", "Imprimir").css("pointer-events", "auto");
                } else {
                    $btn.addClass("btn-disabled").css("pointer-events", "none");
                }
            }

            // Inicializar
            calcularTotalesPorGestion();
            verificarIncoherencias();

            $(".auto-save").on("keyup change", function() {
                var $input = $(this);
                var $fila = $input.closest("tr");
                var gestion = $input.data("gestion");
                var eta_id  = $input.data("eta");

                // 1. Recalcular total de la FILA (M + F) inmediatamente
                var m = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_masculino\']").val()) || 0;
                var f = parseFloat($fila.find("input[data-gestion=\'"+gestion+"\'][data-campo=\'nro_femenino\']").val()) || 0;
                $fila.find(".total-" + gestion + "-" + eta_id).val((m + f).toFixed(2));

                calcularTotalesPorGestion();
                verificarIncoherencias();

                // --- TEMPORIZADOR INDEPENDIENTE POR CELDA ---
                clearTimeout($input.data("h_timer"));

                var t_id = setTimeout(function() {
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
                            if(resp.status == "success") {
                                $input.css("background-color", "#d4edda");
                                $("#toast-notificacion").text(resp.msg).fadeIn(200).delay(800).fadeOut(200);
                            } else {
                                $input.css("background-color", "#f8d7da").val("0");
                                // Forzar recálculo por error
                                $fila.find(".total-" + gestion + "-" + eta_id).val("0.00");
                                calcularTotalesPorGestion();
                                verificarIncoherencias();
                            }
                            setTimeout(function(){ $input.css("background-color", ""); }, 1000);
                        }
                    });
                }, 400); // Bajamos a 400ms para mayor fluidez

                $input.data("h_timer", t_id);
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
        <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/3/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual2"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
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
              
              <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
                <thead>
                    <tr style="background: #fdfdfd; color: #666; font-size: 11px;">
                        <th style="width: 15%; text-align:center; padding: 10px;">GESTIÓN</th>
                        <th style="width: 25%; text-align:center; background: #f0f4f3;">N° EMPRESAS REG. (TOTAL)</th>
                        <th style="width: 30%; text-align:center;">CON APORTES AL DÍA</th>
                        <th style="width: 30%; text-align:center;">EN MORA</th>
                    </tr>
                </thead>
                <tbody>';

                foreach($detalle_form2 as $row){
                    $tabla.='
                    <tr class="fila-dato">
                        <td style="font-size: 13px; text-align: center; vertical-align: middle; background: #f9f9f9;">
                            <b>'.$row['gestion'].'</b>
                        </td>
                        <td style="padding: 8px; background: #f0f4f3;">
                            <input type="number" 
                                   class="form-control auto-save nro_empresas_reg"
                                   style="text-align: right; background-color: #e0f2f1; font-weight: bold; border: 1px solid #b2dfdb;"
                                   readonly
                                   data-form="'.$row['form_id'].'" 
                                   data-gestion="'.$row['gestion'].'" 
                                   data-col="nro_empresas_reg" 
                                   value="'.$row['empresas'].'">
                        </td>
                        <td style="padding: 8px;">
                            <input type="number" 
                                   class="form-control auto-save limpiar-cero" 
                                   style="text-align: right; font-weight: 500;"
                                   min="0"
                                   max="999999999"
                                   oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
                                   onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 9"
                                   data-form="'.$row['form_id'].'" 
                                   data-gestion="'.$row['gestion'].'" 
                                   data-col="nro_aportes_dia" 
                                   value="'.$row['aportes'].'">
                        </td>
                        <td style="padding: 8px;">
                            <input type="number" 
                                   class="form-control auto-save limpiar-cero" 
                                   style="text-align: right; font-weight: 500;"
                                   min="0"
                                   max="999999999"
                                   oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9);"
                                   onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 9"
                                   data-form="'.$row['form_id'].'" 
                                   data-gestion="'.$row['gestion'].'" 
                                   data-col="nro_empresa_mora" 
                                   value="'.$row['mora'].'">
                        </td>
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
            var base_url = "'.base_url().'";

            $(".auto-save").on("keyup change", function() {
                var $input = $(this);
                var $fila = $input.closest("tr");
                var columna = $input.data("col");
                
                // 1. LÓGICA DE SUMA (Solo si se tocan los campos de aportes o mora)
                if (columna === "nro_aportes_dia" || columna === "nro_empresa_mora") {
                    var a = parseFloat($fila.find("input[data-col=nro_aportes_dia]").val()) || 0;
                    var m = parseFloat($fila.find("input[data-col=nro_empresa_mora]").val()) || 0;
                    var total = a + m;

                    // Buscamos el input readonly por su clase específica
                    var $inputTotal = $fila.find(".nro_empresas_reg");
                    
                    if ($inputTotal.val() != total) { // Solo si el valor cambió
                        $inputTotal.val(total); 

                        // Disparamos el guardado automático del total
                        clearTimeout($inputTotal.data("h_timer"));
                        var t_id_total = setTimeout(function() {
                            ejecutarGuardado($inputTotal);
                        }, 800); // Un poco más de tiempo para el total
                        $inputTotal.data("h_timer", t_id_total);
                    }
                }

                // 2. GUARDADO DEL CAMPO QUE SE ESTÁ ESCRIBIENDO
                $input.css("border", "1px solid #ffc107");
                clearTimeout($input.data("h_timer"));

                var t_id = setTimeout(function() {
                    // Evitamos guardar el total aquí si es readonly para que no se duplique la petición
                    if (!$input.prop("readonly")) {
                        ejecutarGuardado($input);
                    }
                }, 400);

                $input.data("h_timer", t_id);
            });

            // FUNCIÓN ÚNICA DE AJAX
            function ejecutarGuardado($el) {
                $.ajax({
                    url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_automatica_form2",
                    type: "POST",
                    data: {
                        form_id: $el.data("form"),
                        gestion: $el.data("gestion"),
                        columna: $el.data("col"),
                        valor:   $el.val()
                    },
                    success: function(resp) {
                        $el.css("border", "1px solid #28a745");
                        $("#toast-notificacion").stop(true, true).fadeIn(200).delay(800).fadeOut(200);
                        setTimeout(function(){ $el.css("border", ""); }, 1000);
                    },
                    error: function() {
                        $el.css("border", "2px solid #dc3545");
                    }
                });
            }
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
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/4/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page_horizontal">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual3"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
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
                '.$this->tabla_form3tp_perfil($get_form_distrital,1).'<br>

              <div style="font-weight: bold; margin-bottom: 10px;">3. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Hospitalaria)</div>
                '.$this->tabla_form3tp_perfil($get_form_distrital,2).'<br>

              <div style="font-weight: bold; margin-bottom: 10px;">4. Perfil de mortalidad (principales causas)</div>
                  '.$this->tabla_form3tp_perfil($get_form_distrital,3).'
                  
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
        $(document).ready(function() {
            // Inicializa todos los elementos con data-toggle="tooltip" de la matriz
            $("[data-toggle=\'tooltip\']").tooltip({
                container: "body",
                trigger: "hover" // El mensaje solo aparece al posicionar el mouse encima
            });

            // Opcional: Cerrar el tooltip si el usuario hace clic en el botón de búsqueda
            $(document).on("click", ".btn-primary", function() {
                $(this).tooltip("hide");
            });
        });
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

                var titulo = "";
                // Usamos parseInt para asegurar la comparación numérica
                switch(parseInt(tp)) {
                    case 1:
                        titulo = "BUSCADOR CIE-10 (MORBILIDAD: Consulta Externa)<br><small>Fila: "+nro+" - Gestión: "+gestion+"</small>";
                        break;
                    case 2:
                        titulo = "BUSCADOR CIE-10 (MORBILIDAD: Hospitalaria)<br><small>Fila: "+nro+" - Gestión: "+gestion+"</small>";
                        break;
                    case 3:
                        titulo = "BUSCADOR CIE-10 (MORTALIDAD)<br><small>Fila: "+nro+" - Gestión: "+gestion+"</small>";
                        break;
                    default:
                        titulo = "BUSCADOR CIE-10";
                }

                // IMPORTANTE: Usar .html() para que procese el <br> o <small>
                $("#modalBuscador .modal-title b").html(titulo);

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


    public function tabla_form3tp_perfil($get_form_distrital,$tp){
      $detalle_form3=$this->model_diagnosticopei->get_formulario_N3($get_form_distrital[0]['dist_id'],$tp); /// listado de gestiones
      $cie10_list=$this->model_diagnosticopei->get_listado_cie10();
      $tabla='';
      $tabla.='
      <style>
          /* Estilo Premium para el fondo del Tooltip */
          .tooltip-inner {
              background-color: #1a237e !important; /* Azul Marino Institucional */
              color: #ffffff !important;
              font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
              font-size: 11px !important;
              font-weight: bold;
              padding: 6px 10px;
              border-radius: 4px;
              box-shadow: 0 4px 8px rgba(0,0,0,0.2);
              max-width: 300px;
          }
          /* Estilo para la pequeña flecha del Tooltip */
          .tooltip.top .tooltip-arrow {
              border-top-color: #1a237e !important;
          }
      </style>
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
      <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
        <thead>
          <tr style="text-align:center;">
              <th rowspan="2" class="nro-col">N.- '.$tp.'</th>
              <th colspan="2" style="text-align:center; font-size:12.5px;">2021</th>
              <th colspan="2" style="text-align:center; font-size:12.5px;">2022</th>
              <th colspan="2" style="text-align:center; font-size:12.5px;">2023</th>
              <th colspan="2" style="text-align:center; font-size:12.5px;">2024</th>
              <th colspan="2" style="text-align:center; font-size:12.5px;">2025</th>
          </tr>
          <tr>
              <th style="width:6%;text-align:center;">Nº casos</th><th style="width:14%;text-align:center;">Código CIE-10</th>
              <th style="width:6%;text-align:center;">Nº casos</th><th style="width:14%;text-align:center;">Código CIE-10</th>
              <th style="width:6%;text-align:center;">Nº casos</th><th style="width:14%;text-align:center;">Código CIE-10</th>
              <th style="width:6%;text-align:center;">Nº casos</th><th style="width:14%;text-align:center;">Código CIE-10</th>
              <th style="width:6%;text-align:center;">Nº casos</th><th style="width:14%;text-align:center;">Código CIE-10</th>
          </tr>
        </thead>
      <tbody>';
         foreach($detalle_form3 as $row){
          $tabla.='
          <tr>
            <td class="nro-label">'.$row['nro'].'</td>';
            
             for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
            $val_casos = $row['nro_casos_'.$anio];
            $val_ce_id = $row['ce_id_'.$anio]; 
            $val_causa = !empty($row['causa_'.$anio]) ? strtoupper(trim($row['causa_'.$anio])) : 'SIN CAUSA SELECCIONADA';
            $cod_cie   = !empty($row['codigo_cie_'.$anio]) ? strtoupper(trim($row['codigo_cie_'.$anio])) : '';

            // Texto limpio para el Tooltip estilizado
            $texto_tooltip = ($cod_cie != '') ? "CÓDIGO: ".$cod_cie." | ENFERMEDAD: ".$val_causa : "NINGÚN CÓDIGO SELECCIONADO";

            $tabla .= '
            <!-- COLUMNA CASOS -->
            <td>
                <input type="number" class="form-control auto-save"
                       min="0" max="999999"
                       oninput="if(this.value.length > 7) this.value = this.value.slice(0, 7);"
                       onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 7"
                       data-form="'.$row['form_id'].'" data-tp_perfil="'.$tp.'" 
                       data-nro="'.$row['nro'].'" data-gestion="'.$anio.'" 
                       data-col="nro_casos" value="'.$val_casos.'" 
                       style="width:100%; text-align:right;">
            </td>
            
            <!-- COLUMNA BUSCADOR CON TOOLTIPS ESTILIZADOS -->
            <td>
                <div class="input-group" style="display: flex; width: 100%;">
                    <!-- Input CIE-10 con Tooltip -->
                    <input type="text" 
                            class="form-control input-sm input-cie10-tooltip" 
                            id="desc_' . $tp . '_' . $anio . '_' . $row['nro'] . '"  
                            value="' . $cod_cie . '" 
                            readonly 
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="' . $texto_tooltip . '" 
                            style="width: 80%; font-size: 7.5pt; height: 25px; border-radius: 4px 0 0 4px; text-align:left; cursor: help; font-weight: bold; color: #1a237e;">
                    
                    <!-- Botón de Búsqueda con Tooltip -->
                    <button type="button" class="btn btn-primary btn-xs" 
                            style="height: 25px; width: 20%;"
                            data-toggle="tooltip" 
                            data-placement="top" 
                            title="SELECCIONE CÓDIGO CIE - 10"
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
    //// ------------------------- END FORM 3


    /*------- Detalle formulario N 4 Infraestructura -------*/
    public function formulario_N4($get_form_distrital){
      $detalle_form4_1er=$this->model_diagnosticopei->get_infraestructura_por_nivel($get_form_distrital[0]['dist_id'],'1'); /// 1er nivel
      $detalle_form4_2do=$this->model_diagnosticopei->get_infraestructura_por_nivel($get_form_distrital[0]['dist_id'],'2,3'); /// 2 y 3 nivel
      $detalle_form4_otros=$this->model_diagnosticopei->get_otros_infraestructura_por_nivel($get_form_distrital[0]['dist_id']); /// Otros Establecimientos
      
       $nro=count($detalle_form4_1er)+count($detalle_form4_2do);
       $page='page';
       if($nro>=5){
         $page='page_long';
       }
      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/5/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="'.$page.'">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual4"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
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
                <br>
                <div style="font-weight: bold; margin-bottom: 10px;">3. Matriz de inventario de establecimientos de SEGUNDO Y TERCER NIVEL (segun poa '.$get_form_distrital[0]['g_id_fin'].')</div>
                '.$this->tabla_form4Tp_infraestructura($detalle_form4_2do,1).'';
              }
              
              $tabla.='
                <br>
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
            var base_url = "'.base_url().'";

            // --- 1. EVENTO PRINCIPAL: GUARDADO INDEPENDIENTE Y TRANSFORMACIONES ---
            $(document).on("keyup change", ".auto-save-otros, .auto-save5", function(e) {
                var $el = $(this);
                var campo_actual = $el.data("campo");
                var valor = $el.val().trim();

                // VALIDACIÓN DE NEGATIVOS
                if ($el.attr("type") === "number" && parseFloat($el.val()) < 0) {
                    $el.val(0);
                    return false;
                }

                // TRANSFORMACIÓN AUTOMÁTICA DE TIPO Y NIVEL
                if (campo_actual === "tipo_establecimiento") {
                    var $fila = $el.closest("tr");
                    var $inputNivel = $fila.find(\'input[data-campo="nivel_establecimiento"]\');
                    var nivelVal = "";

                    if (valor === "1") { $el.val("CIS"); nivelVal = "PRIMER NIVEL"; } 
                    else if (valor === "2") { $el.val("CIMFA"); nivelVal = "PRIMER NIVEL"; }
                    else if (valor === "3") { $el.val("PAISE"); nivelVal = "SEGUNDO NIVEL"; }
                    else if (valor === "4") { $el.val("HIS"); nivelVal = "SEGUNDO NIVEL"; }
                    else if (valor === "5") { $el.val("HAIG"); nivelVal = "TERCER NIVEL"; }

                    if (nivelVal !== "" && $inputNivel.val() !== nivelVal) {
                        $inputNivel.val(nivelVal).prop("readonly", true).css({
                            "background-color": "#eeeeee", "cursor": "not-allowed"
                        }).trigger("change"); 
                    } 
                    else if (valor === "" && $inputNivel.val() !== "") {
                        $inputNivel.val("").prop("readonly", false).css({
                            "background-color": "#ffffff", "cursor": "text"
                        }).trigger("change");
                    }
                }

                // TRANSFORMACIONES RÁPIDAS (SITUACIÓN E INTERNET)
                if (campo_actual === "tipo_situacion") {
                    if (valor === "1") $el.val("PROPIA");
                    else if (valor === "2") $el.val("ALQUILADA");
                }
                if (campo_actual === "serv_internet") {
                    if (valor === "1") $el.val("SI");
                    else if (valor === "0") $el.val("NO");
                }

                // GESTIÓN DE TIMER INDEPENDIENTE POR INPUT
                clearTimeout($el.data("h_timer"));
                if (e.type === "keyup") $el.css("border-color", "#ffc107"); // Naranja al escribir

                var t_id = setTimeout(function() {
                    $.ajax({
                        url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_infra_otros_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            id:      $el.data("id"),
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            campo:   campo_actual,
                            valor:   $el.val().toUpperCase()
                        },
                        success: function(resp) {
                            if (resp.status == "success") {
                                $el.css({"border-color": "#28a745"}); // Verde éxito
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(600).fadeOut(100);
                            }
                            setTimeout(function(){ $el.css("border-color", ""); }, 800);
                        }
                    });
                }, 350); 

                $el.data("h_timer", t_id);
            });

            // --- 2. SALTO INTELIGENTE CON ENTER (OMITE READONLY) ---
            $(document).on("keypress", ".auto-save-otros, .auto-save5", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var $inputs = $(\'.auto-save-otros, .auto-save5\');
                    var $editables = $inputs.filter(function() {
                        return !$(this).prop(\'readonly\') && $(this).is(\':visible\');
                    });
                    var index = $editables.index(this);
                    if (index > -1 && index + 1 < $editables.length) {
                        $editables.eq(index + 1).focus().select();
                    } else {
                        // Si es el último, intentamos crear una fila nueva automáticamente
                        if(typeof agregarNuevoEstablecimientoOtros === "function") {
                            agregarNuevoEstablecimientoOtros($(this).data("form"), $(this).data("gestion"));
                        }
                    }
                }
            });

            // --- 3. LIMPIEZA DE CEROS AL ENTRAR (FOCUS) ---
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

    //// Lista de establecimientos alineados al poa
    public function tabla_form4Tp_infraestructura($detalle,$tp_infra){
      //// 1 : se encuentra en el poa
      //// 0 : nose encuentra en el poa
      $tabla='';
      if($tp_infra==1){
        $tabla .= '
          <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
              <thead>
                  <tr style="background: #fdfdfd; color: #000000;">
                      <th style="width:15%; text-align:center; padding: 10px;">Establecimiento</th>
                      <th style="width:30%; text-align:center;">Ubicacion</th>
                      <th style="width:10%; text-align:center;">Nro. Consultorios</th>
                      <th style="width:10%; text-align:center;">Cuenta con Internet? <br>(1: si - 0: no)</th>
                      <th style="width:15%; text-align:center;">Situación <br>(1: PROPIA - 2: ALQUILADA - 3: OTROS)</th>
                  </tr>
              </thead>
              <tbody>';
              foreach($detalle as $row) {
                  $tabla .= '
                  <tr class="fila-dato">
                      <td style="text-align:left; vertical-align: middle; background: #f9f9f9;"><b>'.$row['tipo'].' '.$row['act_descripcion'].'</b></td>
                      <td>
                          <input type="text" class="form-control auto-save-infra limpiar-cero" 
                              value="'.strtoupper($row['ubicacion']).'" 
                              maxlength="500" data-form="'.$row['form_id'].'" data-act="'.$row['act_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="ubicacion"
                              style="text-transform: uppercase; font-size: 10px;" placeholder="MÁX. 500 CARACT.">
                      </td>
                      <td>
                          <input type="number" class="form-control auto-save4 limpiar-cero"
                              style="text-align: right; font-weight: bold;" min="0" max="999999"
                              value="'.$row['nro_consultorios'].'" data-form="'.$row['form_id'].'" data-act="'.$row['act_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="nro_consultorios">
                      </td>
                      <td>
                          <input type="text" class="form-control auto-save-infra limpiar-cero" 
                              value="'.$row['serv_internet'].'" data-form="'.$row['form_id'].'" data-act="'.$row['act_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="serv_internet"
                              style="text-align:center;" placeholder="1/0">
                      </td>
                      <td>
                          <input type="text" class="form-control auto-save-infra" 
                              value="'.$row['tipo_situacion'].'" data-form="'.$row['form_id'].'" data-act="'.$row['act_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="tipo_situacion"
                              style="font-size: 10px;" placeholder="Detalle">
                      </td>
                  </tr>';
                  }
                  $tabla.='
                </tbody>';
      }
      else{
        $tabla .= '
          <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border: none;">
              <thead>
                  <tr style="background: #fdfdfd; color: #000000;">
                      <th style="width:15%; text-align:center; padding: 10px;">Establecimiento</th>
                      <th style="width:12%; text-align:center;">Tipo <br><small>(1 a 5)</small></th>
                      <th style="width:12%; text-align:center;">Nivel <br><small>(Auto)</small></th>
                      <th style="width:30%; text-align:center;">Ubicacion</th>
                      <th style="width:10%; text-align:center;">Nro. Consultorios</th>
                      <th style="width:10%; text-align:center;">Cuenta con Internet? <br>(1: si - 0: no)</th>
                      <th style="width:15%; text-align:center;">Situación <br>(1: PROPIA - 2: ALQUILADA - 3: OTROS)</th>
                      <th style="width:5%; text-align:center;"></th>
                  </tr>
              </thead>
              <tbody id="tabla_otros_body">';
              foreach($detalle as $row) {
                $readonly = (!empty($row['nivel_establecimiento'])) ? 'readonly style="background-color: #eeeeee; cursor: not-allowed;"' : '';
                $tabla .= '
                <tr id="fila_otro_'.$row['infra_otro_id'].'" class="fila-dato">
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.strtoupper($row['otro_establecimiento']).'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="otro_establecimiento"
                            style="text-transform: uppercase; font-weight: bold;">
                    </td>
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.strtoupper($row['tipo_establecimiento']).'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="tipo_establecimiento"
                            style="text-transform: uppercase; text-align:center;" placeholder="1-5">
                    </td>
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.strtoupper($row['nivel_establecimiento']).'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="nivel_establecimiento"
                            '.$readonly.'>
                    </td>
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.strtoupper($row['ubicacion']).'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="ubicacion"
                            style="text-transform: uppercase; font-size: 10px;">
                    </td>
                    <td>
                        <input type="number" class="form-control auto-save5 limpiar-cero" 
                            value="'.$row['nro_consultorios'].'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="nro_consultorios"
                            style="text-align:right; font-weight:bold;">
                    </td>
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.$row['serv_internet'].'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="serv_internet"
                            style="text-align:center;">
                    </td>
                    <td>
                        <input type="text" class="form-control auto-save-otros" 
                            value="'.$row['tipo_situacion'].'" 
                            data-id="'.$row['infra_otro_id'].'" data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion_pei'].'" data-campo="tipo_situacion">
                    </td>
                    <td style="text-align:center; vertical-align: middle;">
                        <a href="javascript:void(0);" 
                           onclick="eliminarRegistroOtro('.$row['infra_otro_id'].');" 
                           class="btn btn-danger btn-xs" 
                           title="Eliminar Registro">
                           <i class="glyphicon glyphicon-trash"></i>
                        </a>
                    </td>
                </tr>';
            }
            $tabla.='
            </tbody>';
      }
      $tabla.='
      </table>';

      return $tabla;
    }


    /*------- Detalle formulario N 5 - Diagnostico camas -------*/
    public function formulario_N5($get_form_distrital){
      $establecimientos=$this->model_diagnosticopei->get_diagnostico_camas($get_form_distrital[0]['dist_id']);
      $nro_est=count($establecimientos);
      $tabla='';
      if($nro_est==0){
        $tabla.='Sin Formulario !!!';
      }
      else{
      $tabla.='
        <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/6/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual5"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>'.$this->entidad.'</p>
                  <h1><b>DIAGNÓSTICO CAMAS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Matriz de gestion de camas Hospitalarias (II Y III nivel)</div>';
             
             if ($nro_est > 1) {
                  // CASO: Múltiples establecimientos (USAR ACORDEÓN)
                  $tabla .= '<div class="panel-group smart-accordion-default" id="accordion-produccion">';
              }

              foreach ($establecimientos as $row) {
                  $collapse_id = "hosp_" . $row['act_id'];

                  if ($nro_est > 1) {
                      // Estructura de Cabecera para Acordeón
                      $tabla .= '
                      <div class="panel panel-default">
                          <div class="panel-heading" style="background-color: #f2f2f2;">
                              <h4 class="panel-title">
                                  <a data-toggle="collapse" data-parent="#accordion-produccion" href="#' . $collapse_id . '" class="collapsed" style="text-decoration:none; display: block; width: 100%;"> 
                                      <i class="fa fa-lg fa-angle-down pull-right"></i> 
                                      <b>' . strtoupper($row['act_descripcion']) . '</b> <small>(' . $row['tipo'] . ')</small>
                                  </a>
                              </h4>
                          </div>
                          <div id="' . $collapse_id . '" class="panel-collapse collapse">';
                  } else {
                      // Estructura de Título simple para un solo establecimiento
                      $tabla .= '
                      <div class="well well-sm" style="background-color: #f2f2f2; border: 1px solid #ccc;">
                          <h4 style="margin:0;"><b>' . strtoupper($row['act_descripcion']) . '</b> <small>(' . $row['tipo'] . ')</small></h4>
                      </div>
                      <div class="bloque-unico">';
                  }

                  // Contenido de la Tabla (Común para ambos casos)
                  $tabla .= '
                      <div class="panel-body no-padding">
                          <table class="table table-bordered table-condensed" style="width:100%; margin-bottom:0;">
                              <thead>
                                  <tr style="text-align:center; background-color: #fafafa; font-size: 11px; color: #666;">
                                      <th style="width:10%;">GESTIÓN</th>
                                      <th style="width:20%;">NRO. DE CAMAS</th>
                                      <th style="width:20%;">% DE OCUPACIÓN</th>
                                      <th style="width:25%;">ESTANCIA MEDIA</th>
                                      <th style="width:25%;">GIRO CAMA</th>
                                  </tr>
                              </thead>
                              <tbody>';

                  for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                      $tabla .= '
                      <tr>
                        <td style="text-align:center; vertical-align:middle; font-weight:bold; background-color: #f9f9f9;">' . $anio . '</td>
                        
                        <!-- Nro de Camas -->
                        <td>
                            <input type="number" class="form-control auto-save-cama limpiar-cero" 
                                value="' . $row['camas_'.$anio] . '" 
                                data-form="' . $row['form_id'] . '" data-act="' . $row['act_id'] . '" data-gestion="' . $anio . '" data-campo="nro_camas" 
                                style="text-align:right;">
                        </td>
                        
                        <!-- % Ocupación con estilo de grupo -->
                        <td>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control auto-save-cama limpiar-cero" 
                                    value="' . round($row['ocupacion_'.$anio],2) . '" 
                                    data-form="' . $row['form_id'] . '" data-act="' . $row['act_id'] . '" data-gestion="' . $anio . '" data-campo="ocupacion" 
                                    style="text-align:right;">
                                <span class="input-group-addon" style="padding: 4px 7px; font-weight: bold;">%</span>
                            </div>
                        </td>
                        
                        <!-- Estancia Media -->
                        <td>
                            <input type="number" class="form-control auto-save-cama limpiar-cero" 
                                value="' . $row['estancia_'.$anio] . '" 
                                data-form="' . $row['form_id'] . '" data-act="' . $row['act_id'] . '" data-gestion="' . $anio . '" data-campo="nro_estancia_media" 
                                style="text-align:right;">
                        </td>
                        
                        <!-- Giro Cama -->
                        <td>
                            <input type="number" class="form-control auto-save-cama limpiar-cero" 
                                value="' . $row['giro_'.$anio] . '"
                                data-form="' . $row['form_id'] . '" data-act="' . $row['act_id'] . '" data-gestion="' . $anio . '" data-campo="nro_giro_cama" 
                                style="text-align:right;">
                        </td>
                    </tr>';
                  }

                  $tabla .= '</tbody></table></div></div>'; // Cierra panel-body y collapse/bloque-unico

                  if ($nro_est > 1) {
                      $tabla .= '</div>'; // Cierra panel-default
                  }
              }

              if ($nro_est > 1) {
                  $tabla .= '</div>'; // Cierra el accordion-produccion
              }
                   
         $tabla.='   
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="5">

              <div style="margin-top: 30px;">
                  <strong>* Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="5"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion5']).'</textarea>
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
          document.getElementById("fecha-actual5").innerText = new Date().toLocaleDateString();
        </script>
        <script>
        $(document).ready(function() {
            var base_url = "'.base_url().'";

            // --- 1. GESTIÓN DE CEROS (FOCUS / BLUR) ---
            $(document).on("focus", ".limpiar-cero", function() {
                var $el = $(this);
                if (parseFloat($el.val()) === 0) {
                    $el.val("");
                }
                $el.select();
            });

            $(document).on("blur", ".limpiar-cero", function() {
                var $el = $(this);
                if ($el.val() === "" || $el.val() === null) {
                    $el.val("0");
                }
            });

            // --- 2. GUARDADO AUTOMÁTICO E INDEPENDIENTE ---
            $(document).on("keyup change", ".auto-save-cama", function(e) {
                var $el = $(this);
                var campo = $el.data("campo");
                var valor = $el.val();

                // VALIDACIÓN CRÍTICA DE OCUPACIÓN
                if (campo === "ocupacion" && valor > 100) {
                    clearTimeout($el.data("h_timer")); // DETENEMOS el guardado automático
                    
                    $el.val(100); // Forzamos el tope visualmente
                    $el.css({"border-color": "#dc3545", "background-color": "#fff5f5"}); // Rojo
                    
                    alert("⚠️ Error: El porcentaje de ocupación no puede sobrepasar el 100%. Se ha reajustado al valor máximo.");
                    
                    // Opcional: disparamos el guardado con el valor corregido (100)
                    valor = 100; 
                }

                // Validación de Negativos
                if (parseFloat(valor) < 0) { $el.val(0); return false; }

                // Validación de Porcentaje Máximo (100%)
                if (campo === "ocupacion" && parseFloat(valor) > 100) {
                    $el.val(100);
                    valor = 100;
                }

                // Timer independiente por cada input para evitar colisiones
                clearTimeout($el.data("h_timer"));
                
                // Efecto visual: amarillo mientras espera guardado
                if (e.type === "keyup") $el.css("border-color", "#ffc107");

                var t_id = setTimeout(function() {
                    $.ajax({
                        url: base_url + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_produccion_cama_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            form_id: $el.data("form"),
                            act_id:  $el.data("act"),
                            gestion: $el.data("gestion"),
                            campo:   campo,
                            valor:   valor
                        },
                        success: function(resp) {
                            if (resp.status == "success") {
                                $el.css({"border-color": "#28a745", "background-color": "#fafffa"});
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(600).fadeOut(100);
                            }
                            setTimeout(function(){ 
                                $el.css({"border-color": "", "background-color": ""}); 
                            }, 800);
                        }
                    });
                }, 450); // 450ms de espera tras dejar de escribir

                $el.data("h_timer", t_id);
            });

            // --- 3. SALTO INTELIGENTE CON TECLA ENTER ---
            $(document).on("keypress", ".auto-save-cama", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var $el = $(this);
                    var $inputs = $(".auto-save-cama");
                    var index = $inputs.index(this);

                    if (index > -1 && index + 1 < $inputs.length) {
                        var $next = $inputs.eq(index + 1);
                        
                        // Si el siguiente está en un acordeón cerrado, lo abrimos
                        var $collapse = $next.closest(".panel-collapse");
                        if ($collapse.length > 0 && !$collapse.hasClass("in")) {
                            $next.closest(".panel").find(".enlace-acordeon").trigger("click");
                            
                            // Esperamos a la animación para dar foco
                            setTimeout(function() {
                                $next.focus().select();
                            }, 400);
                        } else {
                            $next.focus().select();
                        }
                    }
                }
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
      }
        return $tabla;
    }


  /*------- Detalle formulario N 6 Equipos -------*/
    public function formulario_N6($get_form_distrital){
      $listado_equipamiento=$this->model_diagnosticopei->get_diagnostico_equipamiento($get_form_distrital[0]['dist_id']);
      $establecimientos=$this->model_diagnosticopei->get_establecimientos_distrital($get_form_distrital[0]['dist_id'],$get_form_distrital[0]['g_id_fin']);
      $tabla='';
      $tabla.='
      <div class="modal fade" id="modal_nuevo_equipo" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background:#3276b1; color:white;">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"><b>REGISTRAR REQUERIMIENTO DE EQUIPO</b></h4>
                </div>
                <div class="modal-body">
                    <!-- PARTE 1: SELECCIÓN -->
                    <div class="form-group">
                        <label><b>1. Seleccione el Establecimiento:</b></label>
                        <select class="form-control" id="m_act_id" style="width:100%" onchange="mostrarCamposEquipo(this.value)">
                            <option value="">Seleccione...</option>';
                            foreach($establecimientos as $e){
                                $tabla .= '<option value="'.$e['act_id'].'" data-nombre="'.$e['tipo'].' '.strtoupper($e['act_descripcion']).'">'.$e['tipo'].' '.strtoupper($e['act_descripcion']).'</option>';
                            }
                        $tabla .= '</select>
                    </div>

                    <!-- PARTE 2: DETALLES (Ocultos por defecto) -->
                    <div id="campos_detalle_equipo" style="display:none; border-top: 1px solid #eee; padding-top: 15px;">
                        <div class="form-group">
                            <label><b>2. Servicio / Área:</b></label>
                            <input type="text" id="m_servicio" class="form-control" style="text-transform:uppercase" placeholder="EJ. RAYOS X">
                        </div>
                        <div class="form-group">
                            <label><b>3. Detalle de Equipo Mayor:</b></label>
                            <textarea id="m_detalle" class="form-control" rows="3" style="text-transform:uppercase" placeholder="ESPECIFICACIONES TÉCNICAS"></textarea>
                        </div>
                        <div class="form-group">
                            <label><b>4. Precio Referencial (Bs.):</b></label>
                            <input type="number" id="m_precio" class="form-control" 
                               min="0" 
                               max="999999999"
                               oninput="if(this.value.length > 9) this.value = this.value.slice(0, 9); if(this.value < 0) this.value = 0;"
                               onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                               value="0" step="0.01" style="text-align:right;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">CANCELAR</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEquipo" style="display:none;" onclick="guardarEquipoCompleto()">
                        <i class="fa fa-save"></i> GUARDAR Y AGREGAR
                    </button>
                </div>
            </div>
        </div>
    </div>


      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/7/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual4"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE EQUIPAMIENTO MAYOR</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Identificación del establecimiento</div>
              <div style="border: 1px solid #ccc; padding: 10px; font-size: 8.5pt; margin-bottom: 20px;">
                  Detalle el equipo medico mayor requerido para el funcionamiento operativo de su regional / Distrital (ej. tomógrafo, resonador, equipo de rayos X, ventiladores, etc.)
              </div>
              
              <div style="margin-bottom: 15px;">
                  <button type="button" class="btn btn-primary btn-sm" onclick="nuevoEquipoModal();">
                      <i class="fa fa-plus"></i> AGREGAR REQUERIMIENTO DE EQUIPO
                  </button>
              </div>';

              $tabla .= '
              <style>
                .table textarea {
                    padding: 4px 8px;
                    line-height: 1.2;
                    border-radius: 4px;
                }
                .table td {
                    vertical-align: middle !important;
                }
              </style>
              <table class="table table-bordered table-hover" style="width: 100%; font-size: 11px;">
                  <thead>
                      <tr style="background: #fdfdfd; color: #666;">
                          <th style="width:20%; text-align:center;">Establecimiento</th>
                          <th style="width:20%; text-align:center;">Servicio / Área</th>
                          <th style="width:35%; text-align:center;">Detalle de Equipo Mayor</th>
                          <th style="width:15%; text-align:center;">Precio Referencial (Bs.)</th>
                          <th style="width:5%; text-align:center;"></th>
                      </tr>
                  </thead>
                  <tbody id="tabla_equipos_body">';
                  foreach ($listado_equipamiento as $row) {
                  $tabla .= '
                  <tr id="fila_eq_'.$row['det6_form6_id'].'">
                      <td style="vertical-align:middle;"><b>' . $row['tipo'] . ' ' . $row['act_descripcion'] . '</b></td>
                      <td>
                          <textarea class="form-control auto-save-eq" 
                                    rows="3" 
                                    data-id="'.$row['det6_form6_id'].'" 
                                    data-campo="servicio" 
                                    style="text-transform:uppercase; resize:vertical; min-width: 100%;">'.strtoupper($row['servicio']).'</textarea>
                      </td>
                      <td>
                          <textarea class="form-control auto-save-eq" 
                                    rows="3" 
                                    data-id="'.$row['det6_form6_id'].'" 
                                    data-campo="detalle_equipo" 
                                    style="text-transform:uppercase; resize:vertical; min-width: 100%;">'.strtoupper($row['detalle_equipo']).'</textarea>
                      </td>
                      <td style="vertical-align:middle;">
                          <input type="number" class="form-control auto-save-eq limpiar-cero" 
                              value="'.$row['precio_referencial'].'" 
                              min="0"
                              data-id="'.$row['det6_form6_id'].'" 
                              data-campo="precio_referencial" 
                              style="text-align:right; font-weight:bold;">
                      </td>
                      <td style="text-align:center; vertical-align:middle;">
                          <a href="javascript:void(0);" 
                                       onclick="eliminarEquipo('.$row['det6_form6_id'].')" 
                                       class="btn btn-danger btn-xs" 
                                       title="Eliminar Registro">
                                       <i class="glyphicon glyphicon-trash"></i>
                          </a>
                      </td>
                  </tr>';
              }
              $tabla .= '</tbody></table>

              

              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="6">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="6"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion6']).'</textarea>
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
            // Usamos una variable global para evitar errores de redeclaración
            var BASE_URL = "'.base_url().'";

            // 1. Abrir modal y resetear campos
            function nuevoEquipoModal() {
                $("#m_act_id").val("").trigger("change");
                $("#m_servicio, #m_detalle").val("");
                $("#m_precio").val("0");
                $("#campos_detalle_equipo, #btnGuardarEquipo").hide();
                $("#modal_nuevo_equipo").modal("show");
            }

            // 2. Mostrar campos al seleccionar establecimiento
            function mostrarCamposEquipo(val) {
                if(val !== "") {
                    $("#campos_detalle_equipo, #btnGuardarEquipo").fadeIn();
                } else {
                    $("#campos_detalle_equipo, #btnGuardarEquipo").fadeOut();
                }
            }

            // 3. Guardar desde el Modal e insertar fila dinámicamente
            function guardarEquipoCompleto() {
                var act_id = $("#m_act_id").val();
                var act_nombre = $("#m_act_id option:selected").data("nombre");
                var servicio = $("#m_servicio").val().trim().toUpperCase();
                var detalle = $("#m_detalle").val().trim().toUpperCase();
                var precio = $("#m_precio").val();

                if(servicio == "" || detalle == "") { 
                    alert("Por favor complete los campos obligatorios."); 
                    return; 
                }

                $.ajax({
                    url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/crear_equipo_completo",
                    type: "POST",
                    dataType: "json",
                    data: {
                        act_id: act_id,
                        servicio: servicio,
                        detalle: detalle,
                        precio: precio,
                        form_id: "'.$get_form_distrital[0]['form_id'].'",
                        gestion: "'.$get_form_distrital[0]['g_id_fin'].'"
                    },
                    success: function(resp) {
                        if(resp.status == "success") {
                            var nuevaFila = `
                            <tr id="fila_eq_${resp.id}" style="display:none; background-color: #fafffa;">
                                <td style="vertical-align:middle;"><b>${act_nombre}</b></td>
                                <td>
                                    <textarea class="form-control auto-save-eq" rows="2" data-id="${resp.id}" data-campo="servicio" style="text-transform:uppercase; resize:vertical;">${servicio}</textarea>
                                </td>
                                <td>
                                    <textarea class="form-control auto-save-eq" rows="2" data-id="${resp.id}" data-campo="detalle_equipo" style="text-transform:uppercase; resize:vertical;">${detalle}</textarea>
                                </td>
                                <td>
                                    <input type="number" class="form-control auto-save-eq limpiar-cero" value="${precio}" data-id="${resp.id}" data-campo="precio_referencial" style="text-align:right;">
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <a href="javascript:void(0);" 
                                       onclick="eliminarEquipo(${resp.id})" 
                                       class="btn btn-danger btn-xs" 
                                       title="Eliminar Registro">
                                       <i class="glyphicon glyphicon-trash"></i>
                                    </a>
                                </td>
                            </tr>`;

                            $("#tabla_equipos_body").append(nuevaFila);
                            $("#fila_eq_" + resp.id).fadeIn(800);
                            $("html, body").animate({ scrollTop: $(document).height() }, 1000);
                            
                            $("#modal_nuevo_equipo").modal("hide");
                            $("#toast-notificacion").text("✅ Registro añadido").fadeIn().delay(2000).fadeOut();
                        }
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert("Error al crear el equipo. Verifique la consola.");
                    }
                });
            }

            // 4. Guardado Automático para edición directa en la tabla
            $(document).on("keyup change", ".auto-save-eq", function() {
                var $el = $(this);
                var campo = $el.data("campo");
                var valor = $el.val();

                if (campo === "precio_referencial") {
                    if (parseFloat(valor) < 0 || valor === "") {
                        valor = 0; $el.val(0);
                    }
                }

                clearTimeout($el.data("h_timer"));
                $el.css("border-color", "#ffc107");

                var t_id = setTimeout(function() {
                    $.ajax({
                        url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_detalle_equipo_form6",
                        type: "POST",
                        dataType: "json",
                        data: {
                            id: $el.data("id"),
                            campo: campo,
                            valor: (campo === "precio_referencial") ? valor : valor.toUpperCase()
                        },
                        success: function(resp) {
                            if (resp.status == "success") {
                                $el.css({"border-color": "#28a745"});
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(600).fadeOut(100);
                            } else {
                                $el.css("border-color", "#dc3545");
                            }
                            setTimeout(function(){ $el.css("border-color", ""); }, 1000);
                        },
                        error: function(xhr) {
                            $el.css("border-color", "#dc3545");
                        }
                    });
                }, 500);

                $el.data("h_timer", t_id);
            });

            // 5. Gestión de Ceros
            $(document).on("focus", ".limpiar-cero", function() {
                if (parseFloat($(this).val()) === 0) $(this).val("");
            }).on("blur", ".limpiar-cero", function() {
                if ($(this).val() === "" || $(this).val() === null) $(this).val("0");
            });

            // 6. Eliminar Registro
            function eliminarEquipo(id) {
                if (confirm("¿Está seguro de eliminar este requerimiento?")) {
                    $.post(BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/eliminar_equipo_form6", { id: id }, function(resp) {
                        if(resp.status == "success") {
                            $("#fila_eq_" + id).fadeOut(400, function() { $(this).remove(); });
                            $("#toast-notificacion").text("✅ Registro eliminado").fadeIn().delay(1000).fadeOut();
                        }
                    }, "json");
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



    /*------- Detalle formulario N 7 Recursos Humanos -------*/
    public function formulario_N7($get_form_distrital){
      $detalle_rrhh=$this->model_diagnosticopei->get_diagnostico_rrhh($get_form_distrital[0]['dist_id']);
      $profesiones = array(
          'nro_medicos' => 'MEDICOS',
          'nro_odontologos' => 'ODONTOLOGOS',
          'nro_farmaceuticos' => 'FARMACEUTICOS',
          'nro_laboratoristas' => 'LABORATORISTAS',
          'nro_otros_prof' => 'OTROS PROFESIONALES',
          'nro_nutricionistas' => 'NUTRICIONISTAS',
          'nro_trabajo_social' => 'TRABAJO SOCIAL',
          'nro_jefe_superv_enf' => 'JEF. SUPERV. ENFERMERÍA',
          'nro_lic_grad_enf' => 'LIC. EN ENFERMERÍA',
          'nro_aux_enf' => 'AUXILIARES DE ENFERMERÍA',
          'nro_pers_adm' => 'PERSONAL ADM. (ÍTEM)',
          'nro_pers_adm_salud' => 'PERSONAL ADM. SALUD',
          'nro_pers_adm_tec' => 'PERS. ADM. TÉCNICO',
          'nro_pers_adm_aux' => 'PERS. ADM. AUXILIAR',
          'nro_pers_adm_chof' => 'CHÓFERES',
          'nro_pers_adm_artesanos' => 'ARTESANOS',
          'nro_pers_adm_trab_manual' => 'TRAB. MANUALES'
      );

      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/8/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page_horizontal_corto">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual8"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE RECURSOS HUMANOS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Cuadro del Personal por Items, Contrato, Acefalias</div>
               <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 10px; border: none;">
                  <thead>
                      <tr style="background: #f5f5f5; color: #333;">
                          <th rowspan="2" style="width: 10%;vertical-align: middle; text-align:center; min-width: 180px; border-top:none;">CATEGORÍA / PROFESIÓN</th>';
                          
                          // Cabecera de Años con alternancia de colores
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $bgColor = ($anio % 2 == 0) ? '#fce4ec' : '#ffffff';
                              $tabla .= '<th colspan="3" style="text-align:center; background: '.$bgColor.'; border-bottom: 2px solid #E91E63; font-size: 15px;">GESTIÓN '.$anio.'</th>';
                          }
                      
                      $tabla .= '
                      </tr>
                      <tr style="background: #fafafa; font-size: 9px; color: #666;">';
                          // Sub-cabecera de Tipos
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $tabla .= '<th style="text-align:center; width:50px; font-size:12px;">ITEMS</th>';
                              $tabla .= '<th style="text-align:center; width:50px; font-size:12px;">CONTR.</th>';
                              $tabla .= '<th style="text-align:center; width:50px; background: #fff3f3; font-size:12px;">ACEF.</th>';
                          }
                      $tabla .= '</tr>
                  </thead>
                  <tbody>';

                  // Generamos las filas por Profesión
                  foreach ($profesiones as $campo => $label) {
                      $tabla .= '
                      <tr>
                          <td style="background: #f9f9f9; font-weight: bold; padding: 5px 10px; text-align:left; border-right: 2px solid #eee;">
                              '.$label.'
                          </td>';
                          
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              for ($tp = 1; $tp <= 3; $tp++) {
                                  // Búsqueda del valor en el array
                                  $valor = 0;
                                  foreach ($detalle_rrhh as $res) {
                                      if ($res['gestion'] == $anio && $res['tp_rrhh_form'] == $tp) {
                                          $valor = $res[$campo];
                                          break;
                                      }
                                  }

                                  // Estilo condicional para Acefalías (columna 3 de cada año)
                                  $tdStyle = ($tp == 3) ? 'background-color: #fff8f8;' : '';

                                  $tabla .= '
                                  <td style="padding: 2px; '.$tdStyle.'">
                                      <input type="number" 
                                          class="form-control auto-save-rrhh limpiar-cero" 
                                          value="'.$valor.'" 
                                          min="0" 
                                          max="9999"
                                          data-form="'.$get_form_distrital[0]['form_id'].'" 
                                          data-gestion="'.$anio.'" 
                                          data-tipo="'.$tp.'" 
                                          data-campo="'.$campo.'" 
                                          style="width:100%; height:24px; padding:2px; text-align:right; font-size:15px; border: 1px solid transparent; background:transparent; font-weight: 500;"
                                          onfocus="this.style.border=\'1px solid #E91E63\'; this.style.background=\'#fff\'"
                                          onblur="this.style.border=\'1px solid transparent\'; this.style.background=\'transparent\'">
                                  </td>';
                              }
                          }
                      $tabla .= '</tr>';
                  }

                  $tabla .= '
                  </tbody>
                      <tfoot>
                        <tr style="background: #f5f5f5; font-weight: bold; border-top: 2px solid #E91E63;">
                            <td style="text-align:right; padding: 10px; font-size: 11px;">TOTAL PERSONAL:</td>';
                            
                            for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                                for ($tp = 1; $tp <= 3; $tp++) {
                                    // ID único para cada celda de total por gestión y tipo
                                    $id_total = "total_".$anio."_".$tp;
                                    $bg_total = ($tp == 3) ? '#ffebee' : '#e3f2fd'; // Color diferente para total acefalías

                                    $tabla .= '
                                    <td style="text-align:right; padding: 5px; background-color: '.$bg_total.'; border: 1px solid #ddd;">
                                        <span id="'.$id_total.'" class="total-columna" style="font-size:13px; color:#0d47a1;">0</span>
                                    </td>';
                                }
                            }
                $tabla .= '
                        </tr>
                    </tfoot>
              </table>
            
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="7">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="7"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion7']).'</textarea>
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
          document.getElementById("fecha-actual8").innerText = new Date().toLocaleDateString();
        </script>
       <script>
        $(document).ready(function() {
            var BASE_URL = "'.base_url().'";

            // --- 1. FUNCIÓN DE SUMATORIA AUTOMÁTICA POR COLUMNA ---
            function calcularTotalesRRHH() {
                // Recorremos los años y los 3 tipos (1:Items, 2:Contr, 3:Acef)
                for (var anio = 2021; anio <= 2025; anio++) {
                    for (var tp = 1; tp <= 3; tp++) {
                        var suma_col = 0;
                        // Sumamos todos los inputs que coincidan con el año y el tipo
                        $(\'input.auto-save-rrhh[data-gestion="\' + anio + \'"][data-tipo="\' + tp + \'"]\').each(function() {
                            var v = parseInt($(this).val()) || 0;
                            suma_col += v;
                        });
                        // Actualizamos el span del total (ID: total_2021_1, etc)
                        $("#total_" + anio + "_" + tp).text(suma_col.toLocaleString("en-US"));
                    }
                }
            }

            // Ejecutar suma al cargar por si ya hay datos
            calcularTotalesRRHH();

            // --- 2. EVENTO PRINCIPAL: GUARDADO RÁPIDO E INDEPENDIENTE ---
            $(document).on("keyup change", ".auto-save-rrhh", function(e) {
                var $el = $(this);
                
                // Recalcular totales visuales de inmediato (UX Rápida)
                calcularTotalesRRHH();

                // Validar que no sea negativo
                if (parseInt($el.val()) < 0) { $el.val(0); return false; }

                // Manejo de temporizador independiente por cada input
                clearTimeout($el.data("h_timer"));
                
                // Feedback: Amarillo mientras espera los milisegundos de inactividad
                if (e.type === "keyup") $el.css("background-color", "#fff9c4");

                var t_id = setTimeout(function() {
                    $.ajax({
                        url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_rrhh_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            tp_rrhh: $el.data("tipo"),
                            campo:   $el.data("campo"),
                            valor:   $el.val()
                        },
                        success: function(resp) {
                            if (resp.status == "success") {
                                // Feedback: Verde al guardar con éxito
                                $el.css("background-color", "#e8f5e9");
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(600).fadeOut(100);
                                
                                // Limpiar color tras 800ms
                                setTimeout(function() { 
                                    $el.css("background-color", "transparent"); 
                                }, 800);
                            } else {
                                $el.css("background-color", "#ffcdd2"); // Rojo si falla
                            }
                        },
                        error: function() {
                            $el.css("background-color", "#ffcdd2");
                        }
                    });
                }, 400); // 400ms es el tiempo ideal para escritura rápida

                $el.data("h_timer", t_id);
            });

            // --- 3. NAVEGACIÓN INTELIGENTE (TECLA ENTER) ---
            $(document).on("keypress", ".auto-save-rrhh", function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var $inputs = $(".auto-save-rrhh");
                    var idx = $inputs.index(this);
                    if (idx > -1 && idx + 1 < $inputs.length) {
                        $inputs.eq(idx + 1).focus().select();
                    }
                }
            });

            // --- 4. GESTIÓN DE CEROS (LIMPIEZA AL ENTRAR) ---
            $(document).on("focus", ".limpiar-cero", function() {
                if (parseInt($(this).val()) === 0) $(this).val("");
                $(this).select();
            }).on("blur", ".limpiar-cero", function() {
                if ($(this).val() === "" || $(this).val() === null) $(this).val("0");
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

    /*------- Detalle formulario N 8 Compra de Servicios -------*/
    public function formulario_N8($get_form_distrital){
      $detalle_cservicios=$this->model_diagnosticopei->get_diagnostico_compra_servicios($get_form_distrital[0]['dist_id']);

      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/9/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page_horizontal_corto">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual9"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE COMPRA DE SERVICIOS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Identificación de las tres principales compra de servicios</div>
                <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border-collapse: collapse;">
                  <thead>
                      <tr style="background: #FFF3E0; color: #000000; font-weight: bold;">
                          <th style="width:8%; text-align:center; vertical-align: middle;">AÑO</th>
                          <th style="width:30%; text-align:center; vertical-align: middle;">SERVICIO CONTRATADO</th>
                          <th style="width:12%; text-align:center; vertical-align: middle;">N° ATENCIONES</th>
                          <th style="width:15%; text-align:center; vertical-align: middle;">COSTO TOTAL (BS.)</th>
                          <th style="width:35%; text-align:center; vertical-align: middle;">OBSERVACIONES</th>
                      </tr>
                  </thead>
                  <tbody>';

                  // Mapeo de colores de fondo suaves para cada gestión
                  $colores = [
                      '2021' => '#F5F5F5', // Gris claro
                      '2022' => '#FFFFFF', // Blanco
                      '2023' => '#F5F5F5', 
                      '2024' => '#FFFFFF', 
                      '2025' => '#F5F5F5'
                  ];

                  foreach ($detalle_cservicios as $row) {
                      $bg = isset($colores[$row['gestion']]) ? $colores[$row['gestion']] : '#fff';
                      $id_detalle = (isset($row['det8_form8_id']) ? $row['det8_form8_id'] : 0);
                      
                      $tabla .= '
                      <tr style="background-color: '.$bg.';" class="fila-servicio" data-fila="'.$row['nro_fila'].'">
                        <td style="text-align:center; vertical-align:middle; font-weight:bold; border-right: 2px solid #ddd; font-size: 15px;">
                            '.$row['gestion'].'
                        </td>
                        
                        <!-- Servicio -->
                        <td>
                            <textarea class="form-control auto-save-serv" rows="2" onpaste="return false;"
                                data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion'].'" data-fila="'.$row['nro_fila'].'" 
                                data-id="'.$id_detalle.'" data-campo="serv_contratado"
                                style="text-transform:uppercase; border:1px solid #ccc; resize:none; font-size:10px;">'.strtoupper($row['serv_contratado']).'</textarea>
                        </td>
                        
                        <!-- Atenciones -->
                        <td style="vertical-align: middle;">
                            <input type="number" class="form-control auto-save-serv limpiar-cero" value="'.$row['nro_atenciones'].'"
                                min="0" max="999999"
                                oninput="if(this.value.length > 6) this.value = this.value.slice(0, 6);"
                                onkeypress="return event.charCode >= 48 && event.charCode <= 57 && this.value.length < 6"
                                data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion'].'" data-fila="'.$row['nro_fila'].'" 
                                data-id="'.$id_detalle.'" data-campo="nro_atenciones"
                                style="text-align:right; border:1px solid #ccc; font-weight:500;">
                        </td>
                        
                        <!-- Costo Total (ESTE ES EL QUE FALTABA COMPLETAR) -->
                        <td style="vertical-align: middle;">
                            <input type="number" step="0.01" class="form-control auto-save-serv limpiar-cero" 
                                value="'.number_format($row['costo_total'], 2, '.', '').'"
                                data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion'].'" data-fila="'.$row['nro_fila'].'" 
                                data-id="'.$id_detalle.'" data-campo="costo_total" 
                                onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46 || event.charCode == 44"
                                style="text-align:right; font-weight:bold; border:1px solid #ccc; color: #2E7D32;">
                        </td>
                        
                        <!-- Observaciones -->
                        <td>
                            <textarea class="form-control auto-save-serv" rows="2" onpaste="return false;"
                                data-form="'.$row['form_id'].'" data-gestion="'.$row['gestion'].'" data-fila="'.$row['nro_fila'].'" 
                                data-id="'.$id_detalle.'" data-campo="cservicios_observaciones"
                                style="text-transform:uppercase; border:1px solid #ccc; resize:none; font-size:10px;">'.strtoupper($row['cservicios_observaciones']).'</textarea>
                        </td>
                    </tr>';
                  }

                  $tabla .= '
                  </tbody>
              </table>
            
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="8">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="8"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 70px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion8']).'</textarea>
              </div>
            </div>
          </div>
          <hr>
        </div>

        <script>
          document.getElementById("fecha-actual9").innerText = new Date().toLocaleDateString();
        </script>
        <script>
         $(document).ready(function() {
          var BASE_URL = "'.base_url().'";

            // --- 1. GUARDADO AUTOMÁTICO CON BLOQUEO DE DUPLICADOS ---
            $(document).on("keyup change", ".auto-save-serv", function() {
                var $el = $(this);
                var $fila = $el.closest("tr");
                var campo = $el.data("campo");
                var valor = $el.val();
                var id_actual = $el.data("id");

                // SI LA FILA ESTÁ BLOQUEADA POR UNA PETICIÓN EN CURSO, IGNORAR
                if ($fila.data("guardando") === true) return;

                // Si el usuario está escribiendo un punto o coma decimal, esperar
                if (valor.endsWith(".") || valor.endsWith(",")) return;

                $el.css("border-color", "#ffc107"); 
                clearTimeout($el.data("h_timer"));

                var t_id = setTimeout(function() {
                    // Si el ID es 0, bloqueamos la fila para evitar que otras celdas disparen otro INSERT
                    if (id_actual == 0) {
                        $fila.data("guardando", true);
                    }

                    var valorEnvio = (campo === "costo_total") ? valor.replace(",", ".") : valor.toUpperCase();

                    $.ajax({
                        url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_servicios_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            fila:    $el.data("fila"), // Posición 1, 2 o 3
                            id:      id_actual,
                            campo:   campo,
                            valor:   valorEnvio
                        },
                        success: function(resp) {
                            if(resp.status == "success") {
                                // Sincronizar el nuevo ID en TODOS los inputs de la fila inmediatamente
                                if(resp.nuevo_id) {
                                    $fila.find(".auto-save-serv").data("id", resp.nuevo_id).attr("data-id", resp.nuevo_id);
                                }
                                $el.css("border-color", "#28a745");
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(600).fadeOut(100);
                            }
                        },
                        complete: function() {
                            // LIBERAR EL BLOQUEO DE LA FILA SIEMPRE
                            $fila.data("guardando", false);
                            setTimeout(function(){ $el.css("border-color", ""); }, 1000);
                        }
                    });
                }, 600); 

                $el.data("h_timer", t_id);
            });

            // --- 2. FORMATEO SOLO AL SALIR (BLUR) ---
            $(document).on("blur", ".auto-save-serv", function() {
                var $el = $(this);
                if ($el.data("campo") === "costo_total") {
                    var val = $el.val().replace(",", ".");
                    if (val !== "" && !isNaN(val)) {
                        $el.val(parseFloat(val).toFixed(2));
                    } else {
                        $el.val("0.00");
                    }
                } else if ($el.hasClass("limpiar-cero") && $el.val() === "") {
                    $el.val("0");
                }
            });

            // --- 3. LIMPIEZA AL ENTRAR (FOCUS) ---
            $(document).on("focus", ".limpiar-cero", function() {
                var val = $(this).val();
                if (parseFloat(val) === 0) {
                    $(this).val(""); 
                }
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


    /*------- Detalle formulario N 9 Presupuestos -------*/
    public function formulario_N9($get_form_distrital){
      $listado_presupuesto=$this->model_diagnosticopei->get_diagnostico_presupuestos($get_form_distrital[0]['dist_id']);

      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/10/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page_horizontal_corto">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual9"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE INGRESOS Y GASTOS ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Diagnostico de Ingresos y Gastos</div>
                <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border-collapse: collapse;">
                  <thead>
                      <tr style="background: #FFF3E0; color: #000000; font-weight: bold;">
                          <th style="width:8%; text-align:center; vertical-align: middle;">GESTIÓN</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">INGRESOS PROPIOS programados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">INGRESOS PROPIOS ejecutados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">RECURSOS FINANCIEROS programados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">RECURSOS FINANCIEROS ejecutados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">TOTAL INGRESOS EJECUTADOS</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">GASTOS programados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">GASTOS ejecutados</th>
                          <th style="width:11%; text-align:center; vertical-align: middle;">DEFICIT/SUPERAVIT</th>
                      </tr>
                  </thead>
                  <tbody>';
                    foreach ($listado_presupuesto as $row) {
                    $id_det = (isset($row['det9_form9_id']) ? $row['det9_form9_id'] : 0);
                    $tabla .= '
                    <tr class="fila-presupuesto">
                        <td style="text-align:center; vertical-align:middle; font-weight:bold; font-size:13px;">'.$row['gestion'].'</td>
                        
                        <!-- INGRESOS PROPIOS -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre limpiar-cero" value="'.$row['ingresos_propios_programados'].'" data-campo="ingresos_propios_programados" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"></td>
                        <td><input type="number" step="0.01" class="form-control auto-save-pre limpiar-cero" value="'.$row['ingresos_propios_ejecutados'].'" data-campo="ingresos_propios_ejecutados" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"></td>
                        
                        <!-- RECURSOS FINANCIEROS -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre limpiar-cero" value="'.$row['recursos_financieros_programados'].'" data-campo="recursos_financieros_programados" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"></td>
                        <td><input type="number" step="0.01" class="form-control auto-save-pre limpiar-cero" value="'.$row['recursos_financieros_ejecutados'].'" data-campo="recursos_financieros_ejecutados" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"></td>
                        
                        <!-- TOTAL INGRESOS EJECUTADOS (AUTOMÁTICO) -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre" value="'.$row['total_ingresos_ejecutados'].'" data-campo="total_ingresos_ejecutados" data-id="'.$id_det.'" style="background:#eee; font-weight:bold;" readonly></td>
                        
                        <!-- GASTOS PROGRAMADOS (AUTOMÁTICO segun tu regla) -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre" value="'.$row['gastos_programados'].'" data-campo="gastos_programados" data-id="'.$id_det.'" style="background:#eee; font-weight:bold;" readonly></td>
                        
                        <!-- GASTOS EJECUTADOS -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre limpiar-cero" value="'.$row['gastos_ejecutados'].'" data-campo="gastos_ejecutados" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"></td>
                        
                        <!-- DEFICIT / SUPERAVIT (AUTOMÁTICO) -->
                        <td><input type="number" step="0.01" class="form-control auto-save-pre" value="'.$row['deficit_superavit'].'" data-campo="deficit_superavit" data-id="'.$id_det.'" style="background:#eee; font-weight:bold; color:'.($row['deficit_superavit'] < 0 ? 'red' : 'green').';" readonly></td>
                    </tr>';
                }
                  $tabla.='
                  </tbody>
              </table>
              
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="9">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="9"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion9']).'</textarea>
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
          document.getElementById("fecha-actual9").innerText = new Date().toLocaleDateString();
        </script>
      
       <script>
        $(document).ready(function() {
            var BASE_URL = "'.base_url().'";

            // --- 1. LÓGICA DE GUARDADO Y CÁLCULOS ---
            $(document).on("keyup change", ".auto-save-pre", function() {
                var $el = $(this);
                var $fila = $el.closest("tr");
                var valor = $el.val();
                var campo = $el.data("campo");

                // EVITAR SALTOS DE CURSOR AL ESCRIBIR DECIMALES
                if (valor.endsWith(".") || valor.endsWith(",")) return;

                // A. RECALCULO AUTOMÁTICO VISUAL (UX Instantánea)
                var i_p_p = parseFloat($fila.find(\'[data-campo="ingresos_propios_programados"]\').val()) || 0;
                var i_p_e = parseFloat($fila.find(\'[data-campo="ingresos_propios_ejecutados"]\').val()) || 0;
                var r_f_p = parseFloat($fila.find(\'[data-campo="recursos_financieros_programados"]\').val()) || 0;
                var r_f_e = parseFloat($fila.find(\'[data-campo="recursos_financieros_ejecutados"]\').val()) || 0;
                var g_e   = parseFloat($fila.find(\'[data-campo="gastos_ejecutados"]\').val()) || 0;

                var total_ing_ejec = i_p_e + r_f_e;
                var gastos_prog    = i_p_p + r_f_p;
                var deficit        = total_ing_ejec - g_e;

                // Actualizar campos automáticos visualmente
                $fila.find(\'[data-campo="total_ingresos_ejecutados"]\').val(total_ing_ejec.toFixed(2));
                $fila.find(\'[data-campo="gastos_programados"]\').val(gastos_prog.toFixed(2));
                
                var $defInput = $fila.find(\'[data-campo="deficit_superavit"]\');
                $defInput.val(deficit.toFixed(2));
                $defInput.css("color", (deficit < 0) ? "#C62828" : "#2E7D32");

                // B. GESTIÓN DE TIEMPO Y AJAX
                clearTimeout($el.data("h_timer"));
                $el.css("border-color", "#ffc107"); // Naranja: Pendiente

                var t_id = setTimeout(function() {
                    var valorEnvio = valor.replace(",", ".");
                    
                    $.ajax({
                        url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_presupuesto_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            id:      $el.data("id"),
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            campo:   campo,
                            valor:   valorEnvio
                        },
                        success: function(resp) {
                            if(resp.status == "success") {
                                // Sincronizar ID si la fila era nueva
                                if($el.data("id") == 0 && resp.nuevo_id) {
                                    $fila.find(".auto-save-pre").data("id", resp.nuevo_id).attr("data-id", resp.nuevo_id);
                                }
                                
                                // INDICADOR VISUAL VERDE
                                $el.css("border-color", "#28a745");
                                
                                // MENSAJE DE CONFIRMACIÓN (TOAST)
                                $("#toast-notificacion").stop(true, true).fadeIn(200).delay(800).fadeOut(200);
                                
                                // Limpiar borde tras 1.5 segundos
                                setTimeout(function(){ $el.css("border-color", ""); }, 1500);
                            } else {
                                $el.css("border-color", "#dc3545");
                            }
                        },
                        error: function() { $el.css("border-color", "#dc3545"); }
                    });
                }, 600);

                $el.data("h_timer", t_id);
            });

            // --- 2. FORMATEO AL SALIR (BLUR) ---
            $(document).on("blur", ".auto-save-pre", function() {
                var $el = $(this);
                var val = $el.val().replace(",", ".");
                if (val !== "" && !isNaN(val)) {
                    $el.val(parseFloat(val).toFixed(2));
                } else {
                    $el.val("0.00");
                }
            });

            // --- 3. LIMPIEZA AL ENTRAR (FOCUS) ---
            $(document).on("focus", ".limpiar-cero", function() {
                var val = $(this).val();
                if (parseFloat(val) === 0) $(this).val("");
                $(this).select();
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


  /*------- Detalle formulario N 10 Reembolsos -------*/
    public function formulario_N10($get_form_distrital){
      $listado_reembolsos=$this->model_diagnosticopei->get_diagnostico_reembolsos($get_form_distrital[0]['dist_id']);

      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/11/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual11"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DIAGNÓSTICO DE REEMBOLSOS ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Diagnostico de Reembolsos</div>
                <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border-collapse: collapse;">
                  <thead>
                    <tr style="background: #e8eaf6; color: #1a237e;">
                        <th style="width:10%; text-align:center;">GESTIÓN</th>
                        <th style="width:18%; text-align:center;">MEDICAMENTOS</th>
                        <th style="width:18%; text-align:center;">LABORATORIO</th>
                        <th style="width:18%; text-align:center;">IMAGENOLOGÍA</th>
                        <th style="width:18%; text-align:center;">OTROS</th>
                        <th style="width:18%; text-align:center; background:#c5cae9;">TOTAL REEMBOLSOS</th>
                    </tr>
                  </thead>
                  <tbody>';
                     foreach ($listado_reembolsos as $row) {
                      $id_det = (isset($row['det10_form10_id']) ? $row['det10_form10_id'] : 0);
                      
                      $tabla .= '
                      <tr class="fila-reembolso">
                          <td style="text-align:center; vertical-align:middle; font-weight:bold; background:#f9f9f9; font-size:14px;">
                              '.$row['gestion'].'
                          </td>
                          <td>
                              <input type="number" step="0.01" class="form-control auto-save-reemb limpiar-cero" 
                                  value="'.number_format($row['reemb_concep_medicamentos'], 2, '.', '').'"
                                  data-campo="reemb_concep_medicamentos" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"
                                  onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46"
                                  style="text-align:right;">
                          </td>
                          <td>
                              <input type="number" step="0.01" class="form-control auto-save-reemb limpiar-cero" 
                                  value="'.number_format($row['reemb_concep_laboratorio'], 2, '.', '').'"
                                  data-campo="reemb_concep_laboratorio" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"
                                  onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46"
                                  style="text-align:right;">
                          </td>
                          <td>
                              <input type="number" step="0.01" class="form-control auto-save-reemb limpiar-cero" 
                                  value="'.number_format($row['reemb_concep_imagenologia'], 2, '.', '').'"
                                  data-campo="reemb_concep_imagenologia" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"
                                  onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46"
                                  style="text-align:right;">
                          </td>
                          <td>
                              <input type="number" step="0.01" class="form-control auto-save-reemb limpiar-cero" 
                                  value="'.number_format($row['reemb_otros_conceptos'], 2, '.', '').'"
                                  data-campo="reemb_otros_conceptos" data-id="'.$id_det.'" data-gestion="'.$row['gestion'].'" data-form="'.$row['form_id'].'"
                                  onkeypress="return (event.charCode >= 48 && event.charCode <= 57) || event.charCode == 46"
                                  style="text-align:right;">
                          </td>
                          <td style="background:#f5f5f5;">
                              <input type="number" step="0.01" class="form-control" 
                                  value="'.number_format($row['total_reembolsos'], 2, '.', '').'"
                                  data-campo="total_reembolsos" 
                                  style="text-align:right; font-weight:bold; background:#eee; color:#1a237e;" readonly>
                          </td>
                      </tr>';
                  }
                  $tabla.='
                  </tbody>
              </table>
              
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="10">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="10"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion10']).'</textarea>
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
          document.getElementById("fecha-actual11").innerText = new Date().toLocaleDateString();
        </script>
      
       <script>
        $(document).ready(function() {
            var BASE_URL = "'.base_url().'";

            $(document).on("keyup change", ".auto-save-reemb", function() {
                var $el = $(this);
                var $fila = $el.closest(".fila-reembolso");
                var valor = $el.val();
                var campo = $el.data("campo");

                // 1. CÁLCULO VISUAL INMEDIATO
                // Usamos comillas simples internas para no chocar con las de PHP
                var med = parseFloat($fila.find("input[data-campo=\'reemb_concep_medicamentos\']").val()) || 0;
                var lab = parseFloat($fila.find("input[data-campo=\'reemb_concep_laboratorio\']").val()) || 0;
                var img = parseFloat($fila.find("input[data-campo=\'reemb_concep_imagenologia\']").val()) || 0;
                var otr = parseFloat($fila.find("input[data-campo=\'reemb_otros_conceptos\']").val()) || 0;

                var total = med + lab + img + otr;
                
                // Actualizamos el campo visual del total
                $fila.find("input[data-campo=\'total_reembolsos\']").val(total.toFixed(2));

                // 2. EVITAR SALTOS DE CURSOR
                if (valor.endsWith(".") || valor.endsWith(",")) return;

                // 3. GUARDADO AJAX
                clearTimeout($el.data("h_timer"));
                $el.css("border-color", "#ffc107");

                var t_id = setTimeout(function() {
                    $.ajax({
                        url: BASE_URL + "index.php/Cdiagnostico_pei/CDiagnostico_pei/guarda_reembolso_automatica",
                        type: "POST",
                        dataType: "json",
                        data: {
                            id:      $el.data("id"),
                            form_id: $el.data("form"),
                            gestion: $el.data("gestion"),
                            campo:   campo,
                            valor:   valor.replace(",", ".")
                        },
                        success: function(resp) {
                            if(resp.status == "success") {
                                // Sincronizar ID en toda la fila para evitar duplicados
                                if(resp.nuevo_id) {
                                    $fila.find("input").data("id", resp.nuevo_id).attr("data-id", resp.nuevo_id);
                                }
                                $el.css("border-color", "#28a745");
                                $("#toast-notificacion").stop(true, true).fadeIn(100).delay(800).fadeOut(100);
                            } else {
                                $el.css("border-color", "#dc3545");
                            }
                        }
                    });
                }, 600);
                $el.data("h_timer", t_id);
            });

            $(document).on("blur", ".auto-save-reemb", function() {
                var val = $(this).val().replace(",", ".");
                if (val !== "" && !isNaN(val)) $(this).val(parseFloat(val).toFixed(2));
                else $(this).val("0.00");
            });

            $(document).on("focus", ".limpiar-cero", function() {
                if (parseFloat($(this).val()) === 0) $(this).val("");
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


    /*------- Detalle formulario N 11 Detalle Ambulancias -------*/
    public function formulario_N11($get_form_distrital){
      $listado_ambulancias=$this->model_diagnosticopei->get_detalle_ambulancias($get_form_distrital[0]['dist_id']);
      $UnidadEstablecimientos=$this->model_diagnosticopei->get_establecimientos_distrital($get_form_distrital[0]['dist_id'],$this->gestion);
      $tabla='';
      $tabla.='
      <div class="viewport-container">';
        if($this->tp_adm==1 || $get_form_distrital[0]['form_impresion']==1){
          $tabla.='
          <div style="padding: 15px 0;">
                <a href="javascript:void(0);" 
                   onclick="abreVentana_poa(\''.site_url("Diagnostico_pei/rep_diagnostico_form/12/".$get_form_distrital[0]['dist_id']).'\');" 
                   class="btn-imprimir" 
                   title="Imprimir Formulario">
                   <span class="icon">🖨️</span> IMPRIMIR FORMULARIO
                </a>
            </div>';
        }
        $tabla.='
          <div class="modal fade" id="modal_alta_ambulancia" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 5px 15px rgba(0,0,0,0.5);">
                    
                    <div class="modal-header" style="background:#3276b1; color:white; padding: 10px 15px;">
                        <button type="button" class="close" data-dismiss="modal" style="color:white; opacity:0.8;">&times;</button>
                        <h4 class="modal-title"><b>REGISTRAR DETALLE DE AMBULANCIA</b></h4>
                    </div>
                    
                    <form id="form_guardar_ambulancia_maestro" autocomplete="off">
                        <div class="modal-body" style="padding: 20px;">
                            <!-- Llave foránea oculta de la cabecera distrital -->
                            <input type="hidden" name="form_id" id="modal_ambulancia_form_id">

                            <!-- PARTE 1: SELECCIÓN DEL ESTABLECIMIENTO -->
                            <div class="form-group">
                                <label><b>1. Seleccione el Establecimiento:</b></label>
                                <select class="form-control" name="act_id" id="m_act_id" style="width:100%" onchange="mostrarCamposAmbulancia(this.value)">
                                    <option value="">Seleccione...</option>';
                                    foreach($UnidadEstablecimientos as $e){
                                        $tabla .= '<option value="'.$e['act_id'].'">'.$e['tipo'].' '.strtoupper($e['act_descripcion']).'</option>';
                                    }
                                $tabla .= '
                                </select>
                            </div>

                            <!-- PARTE 2: DETALLES DE VEHÍCULO (Ocultos por defecto) -->
                            <div id="campos_detalle_ambulancia" style="display:none; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                                
                                <div class="form-group">
                                    <label><b>2. Número de Placa:</b></label>
                                    <input type="text" 
                                           name="placa" 
                                           id="m_placa" 
                                           class="form-control" 
                                           style="text-transform:uppercase; font-weight: bold; letter-spacing: 1px; text-align: center; font-size: 13px; color: #1a237e;" 
                                           placeholder="EJ. 4852-XYZ" 
                                           maxlength="8" 
                                           pattern="^[0-9]{4}-[A-Z]{3}$" 
                                           title="El formato obligatorio es: 4 números, un guion y 3 letras (Ej: 4852-XYZ)" 
                                           required>
                                </div>
                                
                                <div class="form-group" style="margin-top:12px;">
                                    <label><b>3. Año de Adjudicación / Adquisición:</b></label>
                                    <input type="number" name="gestion" id="m_gestion" class="form-control" 
                                           min="1950" 
                                           max="2027"
                                           oninput="if(this.value.length > 4) this.value = this.value.slice(0, 4);"
                                           onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                                           placeholder="EJ. 2020" required>
                                </div>
                                
                                <div class="form-group" style="margin-top:12px;">
                                    <label><b>4. Estado de Conservación Actual:</b></label>
                                    <select name="estado_ambulancia" id="m_estado" class="form-control" required>
                                        <option value="">Seleccione el estado...</option>
                                        <option value="1">EXCELENTE</option>
                                        <option value="2">BUENO</option>
                                        <option value="3">REGULAR</option>
                                        <option value="4">MALO</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="margin-top:12px;">
                                    <label><b>5. Situación Técnico Legal:</b></label>
                                    <select name="situacion_ambulancia" id="m_situacion" class="form-control" required>
                                        <option value="">Seleccione la situación...</option>
                                        <option value="1">ACTIVO</option>
                                        <option value="2">BAJA TEMPORAL</option>
                                        <option value="3">BAJA DEFINITIVA</option>
                                        <option value="4">RETENIDO</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                        
                        <div class="modal-footer" style="background:#fafafa; padding: 10px 15px; border-top: 1px solid #eee;">
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight:bold;">CANCELAR</button>
                            
                            <!-- CORRECCIÓN: Botón modificado a type="submit" y removido el atributo onclick defectuoso -->
                            <button type="submit" class="btn btn-primary" id="btnGuardarAmbulancia" style="display:none; background:#3276b1; border-color:#2c6aa0; font-weight:bold;">
                                <i class="fa fa-save"></i> GUARDAR Y AGREGAR
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

          <div class="page">
              <!-- Fecha de Impresión Automática -->
              <div class="fecha-impresion">
                  Fecha: <span id="fecha-actual11"></span><br>
                  Pei : '.$get_form_distrital[0]['pei_id'].'<br>
                  Formulario : '.$get_form_distrital[0]['form_id'].'<br>
                  Dist : '.$get_form_distrital[0]['dist_id'].'
              </div>
              <div class="header">
                  <p>CAJA NACIONAL DE SALUD</p>
                  <h1><b>DETALLE DE AMBULANCIAS</b></h1>
              </div>

              <div style="margin: 20px 0; font-weight: bold;">
                  Regional / Distrital: <span style="border-bottom: 1px solid black; display: inline-block; width: 400px;">'.strtoupper($get_form_distrital[0]['dist_distrital']).'</span>
              </div>

              <div style="font-weight: bold; margin-bottom: 10px;">1. Detalle de Ambulancias por Regional/Distrital</div>
                <div style="margin-bottom: 15px; text-align: right;">
                  <button type="button" class="btn btn-success btn-sm" 
                          style="font-weight: bold; background-color: #00a65a; border-color: #008d4c; box-shadow: 0 2px 4px rgba(0,0,0,0.15);" 
                          onclick="abrirModalNuevaAmbulancia(' . intval($get_form_distrital[0]['form_id']) . ')">
                      <i class="fa fa-plus"></i> Registrar Nueva Ambulancia
                  </button>
              </div>';

              // --- TU TABLA DE LISTADO MODIFICADA ---
              $tabla .= '
              <table class="table table-bordered" style="width: 100%; margin-bottom: 0; font-size: 11px; border-collapse: collapse;" border="1">
                <thead>
                  <tr style="background: #e8eaf6; color: #1a237e; height: 32px;">
                      <th style="width:5%; text-align:center;">#</th>
                      <th style="width:15%; text-align:center;">Nro. Placa</th>
                      <th style="width:15%; text-align:center;">Año de Adjudicación</th>
                      <th style="width:18%; text-align:center;">Estado Ambulancia</th>
                      <th style="width:18%; text-align:center;">Situación</th>
                      <th style="width:29%; text-align:center; background:#c5cae9;">Establecimiento</th>
                  </tr>
                </thead>
                <tbody id="tabla_ambulancias_body">';
                
                $nro = 0;
                foreach ($listado_ambulancias as $row) {
                    $nro++;
                    $tabla .= '
                    <tr style="height: 30px;">
                      <td style="text-align:center; background:#f9f9f9; font-weight:bold;">' . $nro . '</td>
                      <td style="text-align:center; font-weight:bold; color:#0d47a1;">' . strtoupper($row['placa']) . '</td>
                      <td style="text-align:center;">' . ($row['anio_adjudicacion'] > 0 ? $row['anio_adjudicacion'] : '---') . '</td>
                      <td style="text-align:center; font-weight:500;">' . strtoupper($row['estado_ambulancia']) . '</td>
                      <td style="text-align:center; font-weight:500;">' . strtoupper($row['situacion_ambulancia']) . '</td>
                      <td style="text-align:left; padding-left:8px; font-weight:bold; color:#1a237e;">' . strtoupper($row['establecimiento']) . '</td>
                    </tr>';
                }
                
                $tabla .= '
                </tbody>
              </table>
              
              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="10">

              <div style="margin-top: 30px;">
                  <strong>Observaciones adicionales</strong>
                  <textarea 
                      class="observaciones-input" 
                      name="obs" 
                      id="obs" 
                      data-nro="10"
                      onpaste="return false;" 
                      placeholder="Escriba aquí sus observaciones..."
                      style="width: 100%; height: 100px; resize: none;"
                  >'.strtoupper($get_form_distrital[0]['observacion10']).'</textarea>
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
          document.getElementById("fecha-actual11").innerText = new Date().toLocaleDateString();
        </script>
       <script type="text/javascript">
    // ==========================================================================
    // --- 1. FUNCIONES GLOBALES (Disponibles para eventos onclick/onchange HTML) ---
    // ==========================================================================
    function abrirModalNuevaAmbulancia(form_id) {
        if($("#form_guardar_ambulancia_maestro").length > 0) {
            $("#form_guardar_ambulancia_maestro")[0].reset();
        }
        $("#campos_detalle_ambulancia").hide();
        $("#btnGuardarAmbulancia").hide();
        $("#m_placa").css("border-color", ""); // Limpiamos alertas visuales previas
        
        $("#modal_ambulancia_form_id").val(form_id);
        $("#modal_alta_ambulancia").modal("show");
    }

    function mostrarCamposAmbulancia(valorSelect) {
        if(valorSelect !== "" && valorSelect !== undefined) {
            $("#campos_detalle_ambulancia").slideDown(250);
            $("#btnGuardarAmbulancia").fadeIn(250);
        } else {
            $("#campos_detalle_ambulancia").slideUp(200);
            $("#btnGuardarAmbulancia").fadeOut(200);
        }
    }

    // ==========================================================================
    // --- 2. MANEJO DE EVENTOS DINÁMICOS DEL DOM Y PROCESAMIENTO AJAX ---
    // ==========================================================================
    document.addEventListener("DOMContentLoaded", function() {
        
        // --- A. MÁSCARA EN TIEMPO REAL PARA EL INPUT DE LA PLACA (XXXX-YYY) ---
        $(document).on("keyup keypress input", "#m_placa", function() {
            var val = $(this).val().toUpperCase();
            
            // Filtro 1: Eliminar cualquier caracter que no sea número, letra o guion
            val = val.replace(/[^0-9A-Z-]/g, "");
            
            // Filtro 2: Insertar el guion de forma automática tras el cuarto dígito
            if (val.length > 4 && val.charAt(4) !== "-") {
                val = val.substr(0, 4) + "-" + val.substr(4);
            }
            
            // Filtro 3: Forzar segmentación quirúrgica por posición
            var parteNumerica = val.substr(0, 4).replace(/[^0-9]/g, "");
            var guion = val.length > 4 ? "-" : "";
            var parteLetras = val.substr(5, 3).replace(/[^A-Z]/g, "");
            
            var resultado = parteNumerica + guion + parteLetras;
            
            // Ajustamos largo máximo estricto (4 Núm + 1 Guion + 3 Letras = 8)
            if (resultado.length > 8) { resultado = resultado.substr(0, 8); }
            
            $(this).val(resultado);
        });

        // --- B. INTERCEPCIÓN DEL SUBMIT CON INYECCIÓN DIRECTA EN EL DOM EN CALIENTE ---
        $(document).on("submit", "#form_guardar_ambulancia_maestro", function(e) {
            e.preventDefault();
            
            // Captura de datos y textos descriptivos de los elementos seleccionados
            var form_id = $("#modal_ambulancia_form_id").val();
            var act_id = $("#m_act_id").val();
            var est_nombre = $("#m_act_id option:selected").text().trim().toUpperCase();
            var placa = $("#m_placa").val().trim().toUpperCase();
            var gestion = $("#m_gestion").val();
            
            var estado_txt = $("#m_estado option:selected").text().trim().toUpperCase();
            var situacion_txt = $("#m_situacion option:selected").text().trim().toUpperCase();

            // Expresión regular estructural estricta (4 números - 3 letras)
            var patronPlaca = /^[0-9]{4}-[A-Z]{3}$/;

            if (!patronPlaca.test(placa)) {
                alert("⚠️ Formato de Placa Inválido.\n\nEl sistema requiere exactamente 4 números, un guion intermedio y 3 letras mayúsculas.\nEjemplo institucional correcto: 4852-XYZ");
                $("#m_placa").focus().css("border-color", "#c62828"); 
                return false;
            }

            $("#btnGuardarAmbulancia").prop("disabled", true).html("<i class=\'fa fa-refresh fa-spin\'></i> Guardando...");

            $.ajax({
                url: "' . base_url() . 'index.php/Cdiagnostico_pei/CDiagnostico_pei/insertar_ambulancia_detalle",
                type: "POST",
                data: $(this).serialize(), // Empaqueta y serializa todos los campos de forma automática
                dataType: "json",
                success: function(resp) {
                    if (resp.status === "success") {
                        
                        // 1. Calculamos el siguiente número correlativo basado en las filas actuales de la grilla
                        var siguienteNro = $("#tabla_ambulancias_body tr").length + 1;

                        // 2. Construimos el casillero HTML plano con animación inicial display:none
                        var nuevaFila = `
                        <tr id="fila_amb_${resp.id}" style="display:none; height: 30px; background-color: #fafffa;">
                            <td style="text-align:center; background:#f9f9f9; font-weight:bold;">${siguienteNro}</td>
                            <td style="text-align:center; font-weight:bold; color:#0d47a1;">${placa}</td>
                            <td style="text-align:center;">${gestion > 0 ? gestion : "---"}</td>
                            <td style="text-align:center; font-weight:500;">${estado_txt}</td>
                            <td style="text-align:center; font-weight:500;">${situacion_txt}</td>
                            <td style="text-align:left; padding-left:8px; font-weight:bold; color:#1a237e;">${est_nombre}</td>
                        </tr>`;

                        // 3. Eliminamos la fila comodín de advertencia si la tabla estaba vacía
                        $("#tabla_ambulancias_body tr:has(td:contains(\'Sin unidades registradas\'))").remove();

                        // 4. Inyectamos la fila armada al final del cuerpo de la tabla
                        $("#tabla_ambulancias_body").append(nuevaFila);
                        
                        // 5. Desplegamos la animación de aparición y el scroll automático (Espejo de Equipos)
                        $("#fila_amb_" + resp.id).fadeIn(800);
                        $("html, body").animate({ scrollTop: $(document).height() }, 1000);

                        // 6. Cierre del modal y lanzamiento del Toast de confirmación institucional
                        $("#modal_alta_ambulancia").modal("hide");
                        $("#toast-notificacion").text("✅ Registro añadido exitosamente").fadeIn().delay(2000).fadeOut();
                    } else {
                        alert("⚠️ Restricción del Servidor: " + resp.msg);
                    }
                },
                error: function() {
                    alert("❌ Error crítico de comunicación. Verifique que la sesión en el SIIPLAS siga activa.");
                },
                complete: function() {
                    $("#btnGuardarAmbulancia").prop("disabled", false).html("<i class=\'fa fa-save\'></i> GUARDAR Y AGREGAR");
                }
            });
        });
    });
</script>';
      
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
                    width: 10in; 
                    min-width: 10in; /* Evita que se encoja en celulares */
                    height: 12in; 
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
                    width: 11in; 
                    min-width: 11in; /* Evita que se encoja en celulares */
                    height: 25in; 
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
                    height: 25in; 
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
                    width: 20in; 
                    min-width: 20in; /* Mantiene el ancho horizontal en celulares con scroll */
                    height: 14in; 
                    padding: 0.4in 0.5in; /* Reducimos un poco el padding para ganar espacio */
                    box-sizing: border-box; 
                    position: relative; 
                    box-shadow: 0 0 20px rgba(0,0,0,0.5);
                    display: flex;
                    flex-direction: column;
                    border: 1px solid #ccc;
                    overflow: hidden; /* Evita que el contenido "chorree" fuera de la hoja */
                }

                .page_horizontal_mas_corto_height { 
                    background-color: white; 
                    /* Invertimos: Ancho ahora es 11 pulgadas y alto 8.5 */
                    width: 23in; 
                    min-width: 23in; /* Mantiene el ancho horizontal en celulares con scroll */
                    height: 9in; 
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