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

              <input type="hidden" class="form_id" value="'.strtoupper($get_form_distrital[0]['form_id']).'">
              <input type="hidden" class="nro_obs" value="3">

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
          document.getElementById("fecha-actual3").innerText = new Date().toLocaleDateString();
        </script>
        <script>
          $(document).ready(function() {
            var timer_perfil = null;
            var base_url = "'.base_url().'"; 

            // Evento para Inputs (Números y Texto) y Selects
            $(".auto-save, .select-perfil, .input-perfil").on("keyup change", function() {
                var $el = $(this);
                var esSelect = $el.is("select");
                
                // Si es escritura, esperamos 800ms. Si es un select, guardamos casi al instante.
                var delay = esSelect ? 100 : 800;

                clearTimeout(timer_perfil);

                // Feedback visual en el status
                $("#status").show().text("Sincronizando...").css("color", "blue");

                timer_perfil = setTimeout(function() {
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

                            // Feedback de éxito
                            $("#status").text("Guardado ✓").css("color", "green").fadeOut(2000);
                            $("#toast-notificacion").fadeIn(400).delay(1500).fadeOut(400);
                        },
                        error: function() {
                            $("#status").text("Error de red").css("color", "red");
                        }
                    });
                }, delay);
            });
        });
        </script>';
        return $tabla;
    }


    public function tabla_form3tp_perfil($dist_id,$tp){
      $detalle_form3=$this->model_diagnosticopei->get_formulario_N3($dist_id,$tp); /// listado de gestiones
      $cie10_list=$this->model_diagnosticopei->get_listado_cie10();
      $tabla='';
      $tabla.='<table>
                  <thead>
                    <tr style="text-align:center;">
                        <th rowspan="3" class="nro-col">N.-</th>
                        <th colspan="3" style="text-align:center;">2021</th>
                        <th colspan="3" style="text-align:center;">2022</th>
                        <th colspan="3" style="text-align:center;">2023</th>
                        <th colspan="3" style="text-align:center;">2024</th>
                        <th colspan="3" style="text-align:center;">2025</th>
                    </tr>
                    <tr>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CE10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CE10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CE10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CE10</th><th style="width:10%;">10 primeras causas</th>
                        <th style="width:3.5%;">Nº casos</th><th style="width:6%;">Cod. CE10</th><th style="width:10%;">10 primeras causas</th>
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
                          $val_ce_id = $row['ce_id_'.$anio]; // ID de la tabla CIE10
                          $val_causa = $row['causa_'.$anio];

                          $tabla.='
                          <!-- '.$anio.' -->
                          <td>
                              <input type="number" 
                                     class="auto-save" 
                                     min="0"
                                     onkeypress="return event.charCode >= 48"
                                     data-form="'.$row['form_id'].'" 
                                     data-tp_perfil="'.$tp.'" 
                                     data-nro="'.$row['nro'].'"
                                     data-gestion="'.$anio.'" 
                                     data-col="nro_casos" 
                                     value="'.$val_casos.'">
                          </td>
                          <td>
                              <select class="select-perfil" 
                                      data-gestion="'.$anio.'" 
                                      data-form="'.$row['form_id'].'"
                                      data-tp_perfil="'.$tp.'" 
                                      data-nro="'.$row['nro'].'" 
                                      data-col="ce_id" 
                                      style="width: 100%; font-size: 8pt; border: none;">
                                      <option value="0">Seleccione...</option>';
                                      foreach($cie10_list as $cie){
                                          // Corregido: Comparamos ID de enfermedad con el ce_id guardado
                                          $selected = ($val_ce_id == $cie['id']) ? 'selected' : '';
                                          $tabla.='<option value="'.$cie['id'].'" '.$selected.'>'.$cie['cod_3']." - ".$cie['descripcion'].'</option>';
                                      }
                          $tabla.='
                              </select>
                          </td>
                          <td><input type="text" class="input-perfil" 
                              data-gestion="'.$anio.'" data-nro="'.$row['nro'].'" data-tp_perfil="'.$tp.'"  data-form="'.$row['form_id'].'"  data-col="detalle_causa" 
                              value="'.$val_causa.'">
                          </td>';
                      }
                      
                    $tabla.='</tr>';
                  }
                  $tabla.='
                  </tbody>
              </table>';

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
                    height: 8.5in; 
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