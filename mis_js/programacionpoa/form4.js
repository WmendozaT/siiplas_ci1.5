
    function doSearch(){
      var tableReg = document.getElementById('datos');
      var searchText = document.getElementById('searchTerm').value.toLowerCase();
      var cellsOfRow="";
      var found=false;
      var compareWith="";

      // Recorremos todas las filas con contenido de la tabla
      for (var i = 1; i < tableReg.rows.length; i++){
        cellsOfRow = tableReg.rows[i].getElementsByTagName('td');
        found = false;
        // Recorremos todas las celdas
        for (var j = 0; j < cellsOfRow.length && !found; j++){
          compareWith = cellsOfRow[j].innerHTML.toLowerCase();
          // Buscamos el texto en el contenido de la celda
          if (searchText.length == 0 || (compareWith.indexOf(searchText) > -1)){
            found = true;
          }
        }
        if(found) {
          tableReg.rows[i].style.display = '';
        } else {
          // si no ha encontrado ninguna coincidencia, esconde la
          // fila de la tabla
          tableReg.rows[i].style.display = 'none';
        }
      }
    }
      
  $(document).ready(function() {
    pageSetUp();
    /* BASIC ;*/
        var responsiveHelper_dt_basic = undefined;
        var responsiveHelper_datatable_fixed_column = undefined;
        var responsiveHelper_datatable_col_reorder = undefined;
        var responsiveHelper_datatable_tabletools = undefined;
        
        var breakpointDefinition = {
            tablet : 1024,
            phone : 480
        };

    /* END BASIC */
    
    /* COLUMN FILTER  */
    var otable = $('#datatable_fixed_column').DataTable({
        "sDom": "<'dt-toolbar'<'col-xs-12 col-sm-6 hidden-xs'f><'col-sm-6 col-xs-12 hidden-xs'<'toolbar'>>r>"+
                "t"+
                "<'dt-toolbar-footer'<'col-sm-6 col-xs-12 hidden-xs'i><'col-xs-12 col-sm-6'p>>",
        "autoWidth" : true,
        "preDrawCallback" : function() {
            // Initialize the responsive datatables helper once.
            if (!responsiveHelper_datatable_fixed_column) {
                responsiveHelper_datatable_fixed_column = new ResponsiveDatatablesHelper($('#datatable_fixed_column'), breakpointDefinition);
            }
        },
        "rowCallback" : function(nRow) {
            responsiveHelper_datatable_fixed_column.createExpandIcon(nRow);
        },
        "drawCallback" : function(oSettings) {
            responsiveHelper_datatable_fixed_column.respond();
        }       
    
    });
    
    // custom toolbar
  //  $("div.toolbar").html('');
    // Apply the filter
    $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
        otable
            .column( $(this).parent().index()+':visible' )
            .search( this.value )
            .draw();   
    } );
    /* END COLUMN FILTER */   
  })

  //// ================================================================================

var base = "";
var com_id = "";
var timeout_maestro = null;
var timeout_mensual = null;

$(document).ready(function() {
    
    // ==========================================================================
    // 1. INICIALIZACIÓN DE VARIABLES DESDE EL DOM
    // ==========================================================================
    base   = $('[name="base"]').val();
    com_id = $('[name="com_id"]').val();

    // ==========================================================================
    // 2. TUS LÓGICAS ANTERIORES: ADAPTADAS DE FORMA DINÁMICA POR FILA
    // ==========================================================================
    
    /**
     * LÓGICA A: CAMBIO EN EL TIPO DE INDICADOR (Dinamizado por fila)
     */
    $(document).on('change', '.auto-save-field-indicador', function() {
        var $combo_indi = $(this);
        var prod_id = $combo_indi.data('id');
        var val_id  = $combo_indi.val(); // 1 = Absoluto, 2 = Relativo

        // Captura de token CSRF por seguridad perimetral de CodeIgniter
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        // Retroalimentación visual en amarillo (Procesando celda)
        $combo_indi.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

        $.ajax({
            url: base + "index.php/programacion/producto/update_datos_form4_uresp",
            type: "POST",
            dataType: 'json',
            // 🛠️ AJUSTADO: Envío por Query String crudo para compatibilidad estricta con CI 1.5
            data: "prod_id=" + prod_id + "&id=" + val_id + "&tp=3" + token_seguridad,
            success: function(response) {
                if (response.respuesta == 'correcto') {
                    // Éxito: Retorna el color celeste original del SIIPLAS
                    $combo_indi.css({'border-color': '#cbd5e1', 'background-color': '#d7fcfa'});

                    if (val_id == 1) { 
                        /// --- COMPORTAMIENTO CASO ABSOLUTO ---
                        // Oculta bloque de metas y deshabilita el input de la meta global
                        $('#tp_met' + prod_id).slideUp(150);
                        document.getElementById("meta" + prod_id).disabled = true;
                        
                        // 🌟 ACTUALIZACIÓN AUTOMÁTICA: Inyectamos inmediatamente la sumatoria calculada por Postgres
                        if (response.update_meta !== undefined) {
                            $("#meta" + prod_id).val(response.update_meta);
                        }
                    } 
                    else { 
                        /// --- COMPORTAMIENTO CASO RELATIVO ---
                        // Despliega bloque de metas y libera la meta global para digitación libre
                        $('#tp_met' + prod_id).slideDown(150);
                        document.getElementById("meta" + prod_id).disabled = false;

                        if (response.update_meta !== undefined) {
                            $("#meta" + prod_id).val(response.update_meta);
                        }
                    }

                    // Revalida de forma reactiva los colores de consistencia matemática de la fila
                    if (typeof validar_coincidencia_meta_total === "function") {
                        validar_coincidencia_meta_total(prod_id);
                    }

                    if (typeof alertify !== "undefined") alertify.success("✔ Tipo de indicador sincronizado.");
                } else {
                    $combo_indi.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                    if (typeof alertify !== "undefined") alertify.error("ERROR AL RECUPERAR INFORMACIÓN");
                }
            },
            error: function() {
                $combo_indi.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                if (typeof alertify !== "undefined") alertify.error("❌ Error crítico de red en indicador.");
            }
        });
    });

    /**
     * INTERCEPTOR COHERENTE: Cambio asíncrono en el selector dinámico "TIPO DE META"
     */
    $(document).on('change', 'select[id^="tp_met"]', function() {
        var $combo_meta = $(this);
        var prod_id = $combo_meta.data('id');
        var val_meta = $combo_meta.val();

        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        $combo_meta.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

        $.ajax({
            url: base + "index.php/programacion/producto/update_datos_tpmeta",
            type: "POST",
            dataType: 'json',
            // 🛠️ AJUSTADO: Envío por Query String crudo serializado
            data: "prod_id=" + prod_id + "&id=" + val_meta + token_seguridad,
            success: function(response) {
                if (response.respuesta == 'correcto') {
                    // Éxito: Retorna al color verde agua del SIIPLAS
                    $combo_meta.css({'border-color': '#cbd5e1', 'background-color': '#e3fcf8'});

                    // Si tu backend recalculó la meta global, la refresca en pantalla
                    if (response.update_meta !== undefined) {
                        $("#meta" + prod_id).val(response.update_meta);
                    }

                    // Revalida de forma inmediata la ecuación mensual de la fila
                    if (typeof validar_coincidencia_meta_total === "function") {
                        validar_coincidencia_meta_total(prod_id);
                    }

                    if (typeof alertify !== "undefined") alertify.success("✔ Distribución de meta procesada.");
                } else {
                    $combo_meta.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                    if (typeof alertify !== "undefined") alertify.error("ERROR AL GUARDAR TIPO DE META");
                }
            },
            error: function() {
                $combo_meta.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                if (typeof alertify !== "undefined") alertify.error("❌ Error de comunicación en tipo de meta.");
            }
        });
    });

    /**
     * INTERCEPTOR EXTRA: Auto-guardado asíncrono para las casillas de los 12 meses (Enero a Diciembre)
     */
    $(document).on('input', '.auto-save-month', function() {
        var $input = $(this);
        clearTimeout(timeout_mensual);
        
        timeout_mensual = setTimeout(function() {
            var prod_id = $input.data('prod');
            var mes_id  = $input.data('mes');
            var pg_fis  = $input.val();

            if (!validar_formato_decimal(pg_fis)) {
                $input.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                return false;
            }

            var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
            var csrf_hash = $('[name="csrf_test_name"]').val() || '';
            var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

            $input.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

            $.ajax({
                url: base + "index.php/programacion/producto/guardar_temporalidad_mes_en_caliente",
                type: "POST",
                dataType: 'json',
                data: "prod_id=" + prod_id + "&m_id=" + mes_id + "&pg_fis=" + pg_fis + token_seguridad,
                success: function(res) {
                    if (res.status === 'success' || res.respuesta === 'correcto') {
                        $input.css({'border-color': '#cbd5e1', 'background-color': '#e5fde5'});
                        
                        // 🌟 EN VIVO: Si es absoluto, Postgres calcula y sobreescribe la meta acumulada en pantalla
                        if (res.update_meta !== undefined) {
                            $("#meta" + prod_id).val(res.update_meta);
                        }

                        validar_coincidencia_meta_total(prod_id);
                    } else {
                        $input.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                    }
                },
                error: function() {
                    $input.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                }
            });
        }, 400); // Debounce preventivo de buffer
    });

    // ==========================================================================
    // 4. TUS ÚLTIMOS AJUSTES: DESAPARICIÓN AUTOMÁTICA DE CEROS EN CALIENTE
    // ==========================================================================
    $(document).on('focus', '.auto-save-month', function() {
        var $input = $(this);
        var valor_actual = $.trim($input.val());
        if (parseFloat(valor_actual) === 0 || valor_actual === "0") {
            $input.val(''); 
        }
    });

    $(document).on('blur', '.auto-save-month', function() {
        var $input = $(this);
        var valor_actual = $.trim($input.val());
        if (valor_actual === "") {
            $input.val('0');
        }
        // Ejecuta un re-cálculo preventivo al salir de la casilla vaciada
        var prod_id = $input.data('prod');
        if(prod_id) validar_coincidencia_meta_total(prod_id);
    });

});

function validar_coincidencia_meta_total(prod_id) {
    var meta_definida = parseFloat($('#meta' + prod_id).val());
    if (isNaN(meta_definida)) meta_definida = 0;

    var suma_total_meses = 0;
    for (var i = 1; i <= 12; i++) {
        // Captura exacta mapeada al prefijo de ID "m" de las 12 celdas PHP
        var $m_celda = $('#m' + i + prod_id);
        var v_mes = parseFloat($m_celda.val());
        if (!isNaN(v_mes)) {
            suma_total_meses += v_mes;
        }
    }

    // Comparación con tolerancia de decimales contables de la CNS
    if (Math.abs(suma_total_meses - meta_definida) < 0.02) {
        // Cuadre Perfecto: Saca el color de error y restablece el marco normal
        $('#meta' + prod_id).css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});
    } else {
        // Descuadre Físico: Alerta pintando la meta en un rosa suave de advertencia contable
        $('#meta' + prod_id).css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
    }
}

/**
 * Envía datos maestros asíncronos a public._productos (Descripciones y Códigos)
 * Inmunizado para compatibilidad estricta con CodeIgniter 1.5 mediante Query String
 */
function enviar_datos_maestro_caliente($elemento) {
    var prod_id = $elemento.data('id');
    var campo   = $elemento.data('campo');
    var valor   = $elemento.val();

    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
    var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

    // Feedback visual preventivo (Amarillo de procesamiento)
    $elemento.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

    $.ajax({
        type: "POST",
        url: base + "index.php/prog/componente/guardar_campo_form4_en_caliente",
        data: "prod_id=" + prod_id + "&campo=" + campo + "&valor=" + encodeURIComponent(valor) + token_seguridad,
        dataType: "json",
        success: function(res) {
            if (res.status === 'success') {
                $elemento.css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});
            } else {
                $elemento.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
                if (typeof alertify !== "undefined") alertify.error("🚨 Error: " + res.message);
            }
        },
        error: function() {
            $elemento.css({'border-color': '#ef4444', 'background-color': '#fef2f2'});
        }
    });
}

/**
 * Inserción unitaria directa de lotes automatizados (Recurrentes/Trimestrales)
 */
function sincronizar_mes_directo(prod_id, mes_id, valor) {
    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
    var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

    $.ajax({
        type: "POST",
        url: base + "index.php/programacion/producto/guardar_temporalidad_mes_en_caliente",
        data: "prod_id=" + prod_id + "&m_id=" + mes_id + "&pg_fis=" + valor + token_seguridad,
        dataType: "json"
    });
}

/**
 * Validación nativa por Expresión Regular: Solo números enteros o decimales con punto
 */
function validar_formato_decimal(valor) {
    if (valor === "" || valor === null) return true; 
    var patron = /^[0-9]+(\.[0-9]+)?$/;
    return patron.test(valor);
}

/**
 * Restringe la digitación en tiempo real solo a caracteres numéricos y punto decimal
 */
function numerosDecimales(evt) {
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    
    // Permitir punto decimal (46), números (48-57) y teclas de control básicas
    if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    
    // Evitar que el operador digite más de un punto decimal en la misma celda
    var texto = (evt.target) ? evt.target.value : "";
    if (charCode == 46 && texto.indexOf('.') !== -1) {
        return false;
    }
    
    return true;
}


































////============================================================


    //// ACTUALIZAR DATOS PARA LLENADOS DEL FORM 4
    function datos_form4(tp,nro,prod_id,name_input){ /// 
      //alert(tp+'--'+nro+'--'+prod_id+'--'+name_input)
      // tp: 0 (datos), 1 (temporalidad)
      // nro: 1 (cod act)
      // nro: 2 (actividad)
      // nro: 3 (resultado)
      // nro: 4 (unidad res)
      // nro: 5 (índicador)
      // nro: 6 (medio de verificacion)
      // nro: 7 (meta)
      //alert(tp+'--'+nro+'--'+prod_id+'--'+name_input)
     if(tp==0){
      informacion = document.getElementById(name_input+prod_id).value;
     }
     else{
      informacion = document.getElementById('m'+name_input+prod_id).value;
     }
      
//alert(informacion)
      var url = base+"index.php/programacion/producto/update_datos_form4";
      var request;
      if (request) {
          request.abort();
      }
      request = $.ajax({
          url: url,
          type: "POST",
          dataType: 'json',
          data: "prod_id="+prod_id+"&nro="+nro+"&detalle="+informacion+"&name_input="+name_input+"&tp="+tp
      });

      request.done(function (response, textStatus, jqXHR) {

      if (response.respuesta == 'correcto') {
          //document.getElementById(name_input+prod_id).value = response.update_informacion;
          document.getElementById('meta'+prod_id).value = response.update_meta;
      }
      else{
          alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

      });
    }



/*function select_uresp_acp_indi(tp, id, prod_id){ 
        var url = base + "index.php/programacion/producto/update_datos_form4_uresp";
        
        // Bloque visual de procesamiento sutil (Color amarillo temporal)
        var selector_elemento = "";
        if(tp == 1) selector_elemento = "#or_id" + prod_id;
        if(tp == 2) selector_elemento = "#u_resp" + prod_id;
        if(tp == 3) selector_elemento = "#indi_id" + prod_id;
        if(selector_elemento !== "") $(selector_elemento).css({'border-color': '#eab308', 'background-color': '#fef9c3'});

        $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: { prod_id: prod_id, id: id, tp: tp },
            success: function (response) {
                if (response.respuesta == 'correcto') {
                    
                    // Retornamos colores base del SIIPLAS
                    if(selector_elemento !== "") $(selector_elemento).css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});

                    if(tp == 3){ // Si alteramos el tipo de indicador
                        if(id == 1){ /// --- REACCION AUTOMATICA CASO ABSOLUTO ---
                            document.getElementById("meta" + prod_id).disabled = true;
                            document.getElementById("tp_met" + prod_id).style.display = 'none';
                            
                            // 🌟 SOLUCIÓN AL PROBLEMA: Inyectamos la sumatoria de meses calculada en Postgres directo en pantalla
                            if (response.update_meta !== undefined) {
                                $("#meta" + prod_id).val(response.update_meta);
                            }
                        }
                        else{ /// --- CASO RELATIVO ---
                            document.getElementById("meta" + prod_id).disabled = false;
                            document.getElementById("tp_met" + prod_id).style.display = 'block';
                            
                            if (response.update_meta !== undefined) {
                                $("#meta" + prod_id).val(response.update_meta);
                            }
                        }
                        
                        // Sincronizamos dinámicamente los estilos y colores de coincidencia física de la hilera entera
                        if (typeof validar_coincidencia_meta_total === "function") {
                            validar_coincidencia_meta_total(prod_id);
                        }
                    }

                    if (typeof alertify !== "undefined") alertify.success("Selección procesada correctamente ...");
                }
                else {
                    if (typeof alertify !== "undefined") alertify.error("ERROR AL RECUPERAR INFORMACION");
                }
            },
            error: function() {
                if (typeof alertify !== "undefined") alertify.error("❌ Error crítico de comunicación con Apache.");
            }
        });
    }

    /**
     * LÓGICA DE SELECCIÓN DE TIPO DE META (RECURRENTE / TRIMESTRAL)
     */
    // function select_tp_meta(id, prod_id){ 
    //     var url = base + "index.php/programacion/producto/update_datos_tpmeta";
    //     $("#tp_met" + prod_id).css({'border-color': '#eab308', 'background-color': '#fef9c3'});

    //     $.ajax({
    //         url: url,
    //         type: "POST",
    //         dataType: 'json',
    //         data: { prod_id: prod_id, id: id },
    //         success: function (response) {
    //             if (response.respuesta == 'correcto') {
    //                 $("#tp_met" + prod_id).css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});
                    
    //                 if (typeof validar_coincidencia_meta_total === "function") {
    //                     validar_coincidencia_meta_total(prod_id);
    //                 }
    //                 if (typeof alertify !== "undefined") alertify.success("Selección procesada correctamente ...");
    //             }
    //             else {
    //                 if (typeof alertify !== "undefined") alertify.error("ERROR AL RECUPERAR INFORMACION");
    //             }
    //         },
    //         error: function() {
    //             if (typeof alertify !== "undefined") alertify.error("❌ Error de comunicación de temporalidad.");
    //         }
    //     });
    // }

    //// ACTUALIZAR UNIDAD RESPONSABLE (PROGRAMAS BOLSAS)
    // function select_uresp_acp_indi(tp,id,prod_id){ /// 
    //   /// tp 1 (acp), 2 (uni resp), 3 (indi id)
    //  // document.getElementById("meta"+prod_id).disabled = true;
    //   var url = base+"index.php/programacion/producto/update_datos_form4_uresp";
    //   var request;
    //   if (request) {
    //       request.abort();
    //   }
    //   request = $.ajax({
    //       url: url,
    //       type: "POST",
    //       dataType: 'json',
    //       data: "prod_id="+prod_id+"&id="+id+"&tp="+tp
    //   });

    //   request.done(function (response, textStatus, jqXHR) {

    //   if (response.respuesta == 'correcto') {
    //       if(tp==3){ // indi id (tipo de indicador)
    //         if(id==1){ /// Absoluto
    //           document.getElementById("meta"+prod_id).disabled = true;
    //           document.getElementById("tp_met"+prod_id).style.display = 'none';
    //         }
    //         else{ /// relativo
    //           //alert('relativo')

    //           document.getElementById("meta"+prod_id).disabled = false;
    //           document.getElementById("tp_met"+prod_id).style.display = 'block';
    //         }
    //       }

    //       alertify.success("Seleccion procesada correctamente ...");
    //   }
    //   else{
    //       alertify.error("ERROR AL RECUPERAR INFORMACION");
    //   }

    //   });
    // }


    // /// selecciona tipo de meta
    // function select_tp_meta(id,prod_id){ /// 
    //   var url = base+"index.php/programacion/producto/update_datos_tpmeta";
    //   var request;
    //   if (request) {
    //       request.abort();
    //   }
    //   request = $.ajax({
    //       url: url,
    //       type: "POST",
    //       dataType: 'json',
    //       data: "prod_id="+prod_id+"&id="+id
    //   });

    //   request.done(function (response, textStatus, jqXHR) {

    //   if (response.respuesta == 'correcto') {
    //       alertify.success("Seleccion procesada correctamente ...");
    //   }
    //   else{
    //       alertify.error("ERROR AL RECUPERAR INFORMACION");
    //   }

    //   });
    // }

    //// ELIMINAR ACTIVIDAD 2025
  function delete_form4(prod_id){
    alertify.confirm("DESEA ELIMINAR ACTIVIDAD ?", function (a) {
        if (a) { 
        //  alert(prod_id)
          var url = base+"index.php/programacion/producto/desactiva_producto";
          
          request = $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: "prod_id="+prod_id
          });

          request.done(function (response, textStatus, jqXHR) { 
           // alert('hola')
            if (response.respuesta == 'correcto') {
                alertify.success("Se elimino correctamente ...");
                window.location.reload(true);
            } else {
              alertify.danger("Error ...");
            }
          });
            request.fail(function (jqXHR, textStatus, thrown) {
              console.log("ERROR: " + textStatus);
            });
            request.always(function () {
                //console.log("termino la ejecuicion de ajax");
            });

            e.preventDefault();

        } else {
            // user clicked "cancel"
            alertify.error("CANCELADA");
        }
      });
    return false;
  }




  //// Subir Archivo de Migracionform 4 y form5
  $(function () {
    $(".importar_ff").on("click", function (e) {
      tipo = $(this).attr('name');
      document.getElementById("tp").value=tipo;
      if(tipo==1){
          $('#titulo').html('<h2 class="row-seperator-header"><i class="glyphicon glyphicon-import"></i> <b>IMPORTAR ARCHIVO FORM 4.CSV</b></h2>');
          $('#img').html('<img src="'+base+'/assets/img/img_migracion/migracion_f4.JPG" style="border-style:solid;border-width:5px;" style="width:10px;">');
          $('#buton').html('SUBIR ARCHIVO ACTIVIDADES.SCV');
        }
        else{
          $('#titulo').html('<h2 class="row-seperator-header"><i class="glyphicon glyphicon-import"></i> <font color=blue><b> IMPORTAR ARCHIVO DE FORM 5.SCV (GLOBAL)</b></font></h2>');
          $('#img').html('<img src="'+base+'/assets/img/img_migracion/migracion_form5.JPG" style="border-style:solid;border-width:5px;" style="width:10px;">');
          $('#buton').html('SUBIR ARCHIVO DE REQUERIMIENTOS.SCV');
        }
    });
  });

    /// ---- TIPO DE INDICADOR
    $(document).ready(function () {
        $("#tipo_i").change(function () {   
      //  alert('hola')         
          var tp_id = $(this).val();
            if(tp_id==2){
              $('#trep').slideDown();
            }
            else{
              $('#trep').slideUp();
              for (var i = 1; i <= 12; i++) {
                  $('[name="m'+i+'"]').val((0).toFixed(0));
                  $("#m"+i).html('');
                  $('[name="m'+i+'"]').prop('disabled', false);
              }
              $('[name="total"]').val((0).toFixed(0));
              $('[name="tp_met"]').val((3).toFixed(0));
            }
          });
      });

    /// TIPO DE META
      $(document).ready(function () {
        $("#tp_met").change(function () {   
        //alert('hola')         
          var tp_met = $(this).val();

            if(tp_met==1){ /// recurrente
              meta = parseFloat($('[name="meta"]').val());
              for (var i = 1; i <= 12; i++) {
                $('[name="m'+i+'"]').val((meta).toFixed(0));
                $("#m"+i).html('%');
                $('[name="m'+i+'"]').prop('disabled', true);
              }
              $('[name="total"]').val((meta).toFixed(0));
            }
            else{
              if(tp_met==5){
                  meta = parseFloat($('[name="meta"]').val());
                  for (var i = 1; i <= 12; i++) {
                    if(i==3 || i==6 || i==9 || i==12){
                      $('[name="m'+i+'"]').val((meta).toFixed(0));
                    }
                    else{
                      $('[name="m'+i+'"]').val(0);
                    }
                    
                    $("#m"+i).html('%');
                    
                    $('[name="m'+i+'"]').prop('disabled', true);
                  }
                  $('[name="total"]').val((meta).toFixed(0));
              }
              else{
                for (var i = 1; i <= 12; i++) {
                  $('[name="m'+i+'"]').val((0).toFixed(0));
                  $("#m"+i).html('');
                  $('[name="m'+i+'"]').prop('disabled', false);
                }
                $('[name="total"]').val((0).toFixed(0));
              }
            }
            
            total = parseFloat($('[name="total"]').val());

            if(meta==total){
              $('#atit').html('');
              $ ('#but').slideDown();
            }
            else{
              $('#atit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
              $('#but').slideUp();
            }

          });
      });


  //// Subir archivo de migracion form4 y 5
  $(function () {
    //SUBIR ARCHIVO
    $("#subir_archivo").on("click", function () {
      var $valid = $("#form_subir_sigep").valid();
      if (!$valid) {
          $validator.focusInvalid();
      } else {
        if(document.getElementById('archivo').value==''){
          alertify.alert('PORFAVOR SELECCIONE ARCHIVO .CSV');
          return false;
        }

        alertify.confirm("REALMENTE DESEA SUBIR ESTE ARCHIVO?", function (a) {
          if (a) {
              document.getElementById("load").style.display = 'block';
              document.getElementById('subir_archivo').disabled = true;
              document.forms['form_subir_sigep'].submit();
          } else {
              alertify.error("OPCI\u00D3N CANCELADA");
          }
        });
      }
    });
  });


      $(document).ready(function() {
        pageSetUp();
        $("#obj_id").change(function () {
            $("#obj_id option:selected").each(function () {
            elegido=$(this).val();
            $.post(+base+"/index.php/prog/combo_acciones", { elegido: elegido }, function(data){ 
              $("#acc_id").html(data);
              });     
          });
        });  
      });
      $("#acc_id").change(function () {
        $("#acc_id option:selected").each(function () {
          elegido=$(this).val();
            $.post(+base+"/index.php/prog/combo_indicadores", { elegido: elegido}, function(data){
              $("#indi_pei").html(data);
            });     
          });
      });



      function verif_codigo(){ 
        codigo = parseFloat($('[name="cod"]').val()); //// codigo
        com_id=com_id;
        if(!isNaN(codigo) & codigo!=0){

          var url = base+"index.php/programacion/producto/verif_codigo";
          $.ajax({
            type:"post",
            url:url,
            data:{codigo:codigo,com_id:com_id},
            success:function(datos){
              if(datos.trim() =='true'){
                $('#atit').html('<center><div class="alert alert-danger alert-block">C&Oacute;DIGO DE ACTIVIDAD '+codigo+' YA SE ENCUENTRA REGISTRADO</div></center>');
                $('[name="cod"]').val((0).toFixed(0));
                $('#but').slideUp();
              }else{
                $('#atit').html('');
                $('#but').slideDown();
              }
          }});
        }
        else{
          alertify.error("REGISTRE CÓDIGO DE ACTIVIDAD");
          $('#but').slideUp();
        }
      }

      //// VERIF META PROGRAMADO
      function verif_suma_programado(){ /// meta
        meta = parseFloat($('[name="meta"]').val()); //// linea base
        tipo_meta = parseFloat($('[name="tp_met"]').val()); //// tipo de meta

        if(tipo_meta==1){ /// recurrente
          for (var i = 1; i <= 12; i++) {
            $('[name="m'+i+'"]').val((meta).toFixed(0));
          }
          $('[name="total"]').val((meta).toFixed(0));
        }


        if(meta!=0){
          total = parseFloat($('[name="total"]').val()); //// linea base
          if(meta==total){
            $('#atit').html('');
            $ ('#but').slideDown();
          }
          else{
            $('#atit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
            $('#but').slideUp();
          }
        }
        else{
          $('#but').slideUp();
        }
      }

      /// -- SUMA PROGRAMADO
      function suma_programado(){ 
        sum=0;
        //linea = parseFloat($('[name="lbase"]').val()); //// linea base
        codigo = parseFloat($('[name="cod"]').val()); //// codigo
        for (var i = 1; i<=12; i++) {
          sum=parseFloat(sum)+parseFloat($('[name="m'+i+'"]').val());
        }

        $('[name="total"]').val((sum).toFixed(2));
        programado = parseFloat($('[name="total"]').val()); //// programado total
        meta = parseFloat($('[name="meta"]').val()); //// Meta

        if(programado!='' || programado!=0){
          if(programado!=meta){
            $('#atit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
            $('#but').slideUp();
          }
          else{

            if(codigo==0){
              $('#but').slideUp();
            }
            else{
              $('#atit').html('');
              $ ('#but').slideDown();
            }
          }
        }
        else{
          $('#but').slideUp();
        }
      }

      /*------- ELIMINAR SOLO REQUERIMIENTOS DE LA UNIDAD (TODOS) --------*/
      function eliminar_requerimientos_servicio(){
        alertify.confirm("DESEA ELIMINAR TODOS LOS REQUERIMIENTOS DE LA UNIDAD ?", function (a) {
          if (a) {
            window.location=base+"index.php/prog/delete_insumos_servicio/"+com_id;
          } else {
              alertify.error("OPCI\u00D3N CANCELADA");
          }
        });
      }


      /*------- ELIMINAR SOLO ACTIVIDADES Y REQUERIMIENTOS DE LA UNIDAD (TODOS) --------*/
      function eliminar_form4_todos(){
        alertify.confirm("DESEA ELIMINAR ACTIVIDADES ?", function (a) {
          if (a) {
            window.location=base+"index.php/prog/delete_form4/"+com_id;
          } else {
              alertify.error("OPCI\u00D3N CANCELADA");
          }
        });
      }


      /*------- UPDATE CÓDIGO --------*/
      function update_codigo(){
        alertify.confirm("DESEA ACTUALIZAR LOS CÓDIGOS DE ACTIVIDAD ?", function (a) {
          if (a) {
            window.location=base+"index.php/prog/update_codigo/"+com_id;
          } else {
              alertify.error("OPCI\u00D3N CANCELADA");
          }
        });
      }


    function doSelectAlert(event,priori,prod_id) {
     //  alert(event+'--'+priori+'--'+prod_id)
        var option = event.srcElement.children[event.srcElement.selectedIndex];
        if (option.dataset.noAlert !== undefined) {
            return;
        }

          var mensaje='QUITAR PRIORIDAD';
          var mensaje_resultado='SIN PRIORIDAD';
          if(priori==1){
              var mensaje='ASIGNAR PRIORIDAD';
              var mensaje_resultado='PRIORIDAD ASIGNADO';
          }
          alertify.confirm("DESEA "+mensaje+" ?", function (a) {
            if (a) {
            var url = base+"index.php/programacion/producto/asignar_prioridad";
            $.ajax({
                type: "post",
                url: url,
                data:{prod_id:prod_id,prioridad:priori},
                    success: function (data) {
                    alertify.success(mensaje_resultado);  
                    //window.location.reload(true);
                }
            });
            } else {
                alertify.error("OPCI\u00D3N CANCELADA");
            }
      });
    }


////--------------

$(function () {
    const $form = $("#form_nuevo");
    const $btnSubir = $("#subir_ope");

    // 1. Configuración de Reglas Básicas
    const validator = $form.validate({
        rules: {
            cod: "required", prod: "required", resultado: "required",
            tipo_i: "required", indicador: "required", unidad: "required",
            meta: { required: true, number: true },
            lbase: { required: true, number: true }
        },
        errorElement: 'span',
        errorClass: 'help-block text-danger',
        highlight: e => $(e).closest('section').addClass('has-error'),
        unhighlight: e => $(e).closest('section').removeClass('has-error'),
        errorPlacement: (error, element) => {
            error.css({"color": "red", "font-size": "11px"});
            element.parent().after(error);
        }
    });

    // 2. Función para validar que todos los meses tengan datos
    function validarMeses() {
        let faltanDatos = false;
        for (let i = 1; i <= 12; i++) {
            let valor = $(`input[name="m${i}"]`).val();
            if (valor === "" || isNaN(valor)) {
                $(`input[name="m${i}"]`).closest('section').addClass('has-error');
                faltanDatos = true;
            }
        }
        return !faltanDatos;
    }

    $btnSubir.on("click", function (e) {
        e.preventDefault();

        // Validar campos generales
        if (!$form.valid()) {
            validator.focusInvalid();
            return false;
        }

        // Validar que los meses m1...m12 estén llenos
        if (!validarMeses()) {
            alertify.error("POR FAVOR, COMPLETE TODOS LOS MESES (USE 0 SI NO HAY PROGRAMACIÓN)");
            return false;
        }

        // Lógica de Metas
        const tipoI = $("#tipo_i").val();
        const tpMet = $("#tp_met").val();
        const meta = parseFloat($("#meta").val()) || 0;
        const totalProgramado = parseFloat($("#total").val()) || 0;

        // Validación de suma vs meta
        if (tipoI == 1 || (tipoI == 2 && tpMet == 3)) {
            if (Math.abs(meta - totalProgramado) > 0.01) { // Uso de margen pequeño por decimales
                alertify.error(`ERROR: META (${meta}) NO COINCIDE CON TOTAL PROGRAMADO (${totalProgramado})`);
                $("#meta").focus();
                return false;
            }
        } 

        if (tipoI != 1 && (tpMet == "" || tpMet == 0)) {
            alertify.error("SELECCIONE TIPO DE META");
            $("#tp_met").focus();
            return false;
        }

        // Confirmación y Bloqueo de pantalla
        alertify.confirm("¿CONFIRMA GUARDAR ESTA ACTIVIDAD?", function (ok) {
            if (ok) {
                $("#loading-overlay").css("display", "flex"); // Mostrar Loading
                $btnSubir.prop('disabled', true).text("Procesando...");
                $form.submit();
            }
        });
    });
});


////--------------
  /*---- MODIFICAR FORMULARIO N 4 ---*/
    $(function () {

        $(".mod_ff").on("click", function (e) {
       
            prod_id = $(this).attr('name');
            document.getElementById("prod_id").value=prod_id;
            
            var url = base+"index.php/programacion/producto/get_producto";
            var request;
            if (request) {
                request.abort();
            }
            request = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: "prod_id="+prod_id
            });

            request.done(function (response, textStatus, jqXHR) {
            if (response.respuesta == 'correcto') {

               document.getElementById("mcod").value = response.producto[0]['prod_cod']; 
               document.getElementById("mprod").value = response.producto[0]['prod_producto']; 
               document.getElementById("mresultado").value = response.producto[0]['prod_resultado'];
               document.getElementById("mtipo_i").value = response.producto[0]['indi_id'];

               document.getElementById("mindicador").value = response.producto[0]['prod_indicador'];
               document.getElementById("mverificacion").value = response.producto[0]['prod_fuente_verificacion'];
               //document.getElementById("munidad").value = response.producto[0]['prod_unidades'];

               document.getElementById("mlbase").value = parseInt(response.producto[0]['prod_linea_base']);
               document.getElementById("mmeta").value = parseInt(response.producto[0]['prod_meta']);
               //document.getElementById("munidad").value = response.producto[0]['prod_unidades'];

               document.getElementById("mor_id").value = response.producto[0]['or_id'];
               document.getElementById("mtp_met").value = response.producto[0]['mt_id'];

               for (var i = 1; i <=12; i++) {
                document.getElementById("mm"+i).value = parseInt(response.temp[i]);
                if((response.producto[0]['indi_id']==2 && response.producto[0]['mt_id']==1) || (response.producto[0]['indi_id']==2 && response.producto[0]['mt_id']==5)){
                  document.getElementById("mm"+i).disabled = true;
                }
                else{
                document.getElementById("mm"+i).disabled = false;
                }
               }
                
              // alert(response.prioridad)
               $('#priori').html(response.prioridad);
               $('#resp').html(response.uresponsable);

               if((response.producto[0]['indi_id']==2 && response.producto[0]['mt_id']==1) || (response.producto[0]['indi_id']==2 && response.producto[0]['mt_id']==5)){
                $('[name="mtotal"]').val((parseInt(response.producto[0]['prod_meta'])).toFixed(0));
                document.getElementById("mtrep").style.display = 'block';
               }
               else{
                $('[name="mtotal"]').val((parseInt(response.sum_temp)).toFixed(0));
                document.getElementById("mtrep").style.display = 'none';
                
                prog = parseFloat($('[name="mtotal"]').val());
                meta = parseFloat($('[name="mmeta"]').val());


                if(prog==meta){
                  $('#matit').html('');
                  $('#mbut').slideDown();
                }
                else{
                  $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
                  $('#mbut').slideUp();
                }
               }

            }
            else{
                alertify.error("ERROR AL RECUPERAR DATOS DE LA ACTIVIDAD");
            }

            });
            request.fail(function (jqXHR, textStatus, thrown) {
                console.log("ERROR: " + textStatus);
            });
            request.always(function () {
                //console.log("termino la ejecuicion de ajax");
            });
            e.preventDefault();
            // =============================VALIDAR EL FORMULARIO DE MODIFICACION
            $("#subir_mform4").on("click", function (e) {
                var $validator = $("#form_mod").validate({
                       rules: {
                        prod_id: { //// prod id
                          required: true,
                        },
                        mprod: { //// prod
                            required: true,
                        },
                        mresultado: { //// resultado
                            required: true,
                        },
                        mtipo_i: { //// tipo de indi
                            required: true,
                        },
                        mindicador: { //// indicador
                            required: true,
                        },
                        munidad: { //// unidad
                            required: true,
                        },
                        mlbase: { //// linea base
                            required: true,
                        },
                        mmeta: { //// meta
                            required: true,
                        },
                        mor_id: { //// meta
                            required: true,
                        }
                    },
                    messages: {
                        prod_id: "<font color=red>ACTIVIDAD/font>",
                        mprod: "<font color=red>REGISTRE DETALLE DE LA ACTIVIDAD</font>", 
                        mresultado: "<font color=red>REGISTRE RESULTADO</font>",
                        mtipo_i: "<font color=red>TIPO DE INDICADOR</font>",
                        mindicador: "<font color=red>RESGISTRE INDICADOR</font>",
                        munidad: "<font color=red>REGISTRE UNIDAD RESPONSABLE</font>",
                        mlbase: "<font color=red>REGISTRE LINEA BASE</font>",
                        mmeta: "<font color=red>REGISTRE META</font>", 
                        mor_id: "<font color=red>SELECCIONE OPERACION</font>",                     
                    },
                    highlight: function (element) {
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    },
                    unhighlight: function (element) {
                        $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                    },
                    errorElement: 'span',
                    errorClass: 'help-block',
                    errorPlacement: function (error, element) {
                        if (element.parent('.input-group').length) {
                            error.insertAfter(element.parent());
                        } else {
                            error.insertAfter(element);
                        }
                    }
                });
                var $valid = $("#form_mod").valid();
                if (!$valid) {
                    $validator.focusInvalid();
                } else {

                  $('#matit').html('');
                //  alert(document.getElementById("mlbase").value)
                    alertify.confirm("MODIFICAR DATOS DE LA ACTIVIDAD ?", function (a) {

                      if (a) {
                        document.getElementById("loadm").style.display = 'block';
                          document.getElementById('subir_mform4').disabled = true;
                          document.getElementById("subir_mform4").value = "MODIFICANDO DATOS ACTIVIDAD...";
                          document.forms['form_mod'].submit();
                      } else {
                          alertify.error("OPCI\u00D3N CANCELADA");
                      }
                  });
                }
            });
        });
    });



    /*---- VER REQUERIMIENTOS CARGADOS POR COMPONENTE ---*/
  $(function () {
    $(".ver_requerimientos").on("click", function (e) {
        com_id = $(this).attr('name');
        
        $('#contenido').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Requerimientos !</div>');
        
        var url = base+"index.php/programacion/cservicios/get_ver_requerimientos";
        var request;
        if (request) {
            request.abort();
        }
        request = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "com_id="+com_id
        });

        request.done(function (response, textStatus, jqXHR) {
        if (response.respuesta == 'correcto') {
            $('#contenido').fadeIn(1000).html(response.tabla);
           // $('#caratula').fadeIn(1000).html(response.caratula);
        }
        else{
            alertify.error("ERROR AL RECUPERAR DATOS DE LOS SERVICIOS");
        }

        });
        request.fail(function (jqXHR, textStatus, thrown) {
            console.log("ERROR: " + textStatus);
        });
        request.always(function () {
            //console.log("termino la ejecuicion de ajax");
        });
        e.preventDefault();
        
      });
  });
















    /// ------ Editar Datos de Modificacion POA (Formulario N° 4)
    $(".mod_form4").on("click", function (e) {
        prod_id = $(this).attr('name');

        document.getElementById("prod_id").value=prod_id;
        //alert(prod_id)
        var url = base+"index.php/modificaciones/cmod_fisica/get_form4_mod";
        var request;
        if (request) {
            request.abort();
        }
        request = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "prod_id="+prod_id
        });

        request.done(function (response, textStatus, jqXHR) {
        if (response.respuesta == 'correcto') {
        //  alert(response.alineacion_form2)

            document.getElementById("mcod").value = response.producto[0]['prod_cod']; 
            document.getElementById("mprod").value = response.producto[0]['prod_producto']; 
            document.getElementById("mresultado").value = response.producto[0]['prod_resultado'];
            document.getElementById("mverificacion").value = response.producto[0]['prod_fuente_verificacion'];
            document.getElementById("mmeta").value = parseInt(response.producto[0]['prod_meta']);

            document.getElementById("mtipo_i").value = response.producto[0]['indi_id'];
            document.getElementById("mlbase").value = parseInt(response.producto[0]['prod_linea_base']);
            document.getElementById("mmeta").value = parseInt(response.producto[0]['prod_meta']);
            document.getElementById("mtp_met").value = response.producto[0]['mt_id'];

            document.getElementById("mindicador").value = response.producto[0]['prod_indicador'];

            $('#resp').html(response.uresponsable);
            $('#alineacion_form2').html(response.alineacion_form2);
           // $('#indi').html(response.indicador);
           // $('#tp_meta').html(response.tp_meta);

           if(response.trimestre==1){
            document.getElementById("mtipo_i").disabled = false;
            document.getElementById("mlbase").disabled = false;
            document.getElementById("mtp_met").disabled = false;
           }
           else{ 

            document.getElementById("mtipo_i").disabled = true;
            document.getElementById("mlbase").disabled = true;
            document.getElementById("mtp_met").disabled = true;
           }
           
           //// MOUESTRA LOS MESES YA EVALUADOS
           for (var i = 1; i <=12; i++) {
             document.getElementById("mm"+i).disabled = false;
                $('#e'+i).html('<font color=green><b>'+(response.mes[i].toUpperCase())+'</b></font>');
           }

           /// MUESTYRA LA TEMPORALIDAD
           if(response.producto[0]['indi_id']==2 && response.producto[0]['mt_id']==1){ /// META RECURRENTE
            for (var i = 1; i <=12; i++) {
              document.getElementById("mm"+i).value = parseInt(response.producto[0]['prod_meta']);
              document.getElementById("mm"+i).disabled = true;
            }

            $('[name="mtotal"]').val((parseInt(response.producto[0]['prod_meta'])).toFixed(0));
            document.getElementById("mtrep").style.display = 'block';
           }
           else{ //// META ACUMULADO

            for (var i = 1; i <=12; i++) {
              document.getElementById("mm"+i).value = parseInt(response.producto[0]['mes'+i]);
            }

            $('[name="mtotal"]').val((parseInt(response.producto[0]['total_anual'])).toFixed(0));
            document.getElementById("mtrep").style.display = 'none';
            
            prog = parseFloat($('[name="mtotal"]').val());
            meta = parseFloat($('[name="mmeta"]').val());


            if(prog==meta){
              $('#matit').html('');
              $('#mbut').slideDown();
            }
            else{
              $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
              $('#mbut').slideUp();
            }
           }

        }
        else{
            alertify.error("ERROR AL RECUPERAR DATOS DE LA ACTIVIDAD");
        }

        });
        request.fail(function (jqXHR, textStatus, thrown) {
            console.log("ERROR: " + textStatus);
        });
        request.always(function () {
            //console.log("termino la ejecuicion de ajax");
        });
        e.preventDefault();
        // =============================VALIDAR EL FORMULARIO DE MODIFICACION
        $("#subir_mform4").on("click", function (e) {
            // 1. Configuración de validación
            var $validator = $("#form_mod").validate({
                rules: {
                    prod_id: { required: true },
                    mprod: { required: true },
                    mresultado: { required: true },
                    mtipo_i: { required: true },
                    mindicador: { required: true },
                    munidad: { required: true },
                    mlbase: { required: true },
                    mmeta: { required: true },
                    mor_id: { required: true }
                },
                // ... (mantén tus mensajes y estilos de error igual)
                errorElement: 'span',
                errorClass: 'help-block'
            });

            // 2. Si el formulario es válido
            if ($("#form_mod").valid()) {
                alertify.confirm("¿MODIFICAR DATOS DE LA ACTIVIDAD?", function (a) {
                    if (a) {
                        // --- PROCESO DE LOADING PERSONALIZADO ---
                        
                        // Activa tu overlay con el estilo flex para centrar el contenido
                        $("#loading-overlay").css("display", "flex"); 
                        
                        // Ocultamos también los botones del modal por si el overlay es traslúcido
                        $("#mbut footer").hide(); 
                        
                        // Deshabilitamos el botón para evitar doble clic accidental
                        $("#subir_mform4").prop("disabled", true).text("PROCESANDO...");

                        // 3. Enviamos el formulario
                        document.getElementById("form_mod").submit();

                    } else {
                        alertify.error("OPCI\u00D3N CANCELADA");
                    }
                });
            } else {
                $validator.focusInvalid();
            }
        });
    });
    

    /// Tipo de indicador (Modificacion POA)


    $(document).ready(function () {
      $("#mtipo_i").change(function () { 

        var tp_id = $(this).val();
        
          if(tp_id==2){
            $('#mtrep').slideDown();
          }
          else{
            $('#mtrep').slideUp();
            for (var i = 1; i <= 12; i++) {
                $('[name="mm'+i+'"]').val((0).toFixed(0));
                $("#mm"+i).html('');
                $('[name="mm'+i+'"]').prop('disabled', false);
            }
            $('[name="mtotal"]').val((0).toFixed(0));
            $('[name="mtp_met"]').val((3).toFixed(0));
          }

          programado = parseFloat($('[id="mtotal"]').val()); //// programado total
          meta = parseFloat($('[id="mmeta"]').val()); //// Meta
          if(meta==programado){
            $('#matit').html('');
            $('#mbut').slideDown();
          }
          else{
            $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
            $('#mbut').slideUp();
          }

        });
    });

    /// Tipo de Meta (modificacion poa)
    $(document).ready(function () {
      $("#mtp_met").change(function () {            
        var tp_met = $(this).val();
        
          if(tp_met==0){
            $('#mbut').slideUp();
          }
          else{
            if(tp_met==1){
              meta = parseFloat($('[name="mmeta"]').val());
              for (var i = 1; i <= 12; i++) {
                $('[name="mm'+i+'"]').val((meta).toFixed(0));
                $("#m"+i).html('%');
                $('[name="mm'+i+'"]').prop('disabled', true);
              }
              $('[name="mtotal"]').val((meta).toFixed(0));

              $('#matit').html('');
              $('#mbut').slideDown();
            }
            else{
              if(tp_met==5){

                meta = parseFloat($('[name="mmeta"]').val());
                for (var i = 1; i <= 12; i++) {
                  if(i==3 || i==6 || i==9 || i==12){
                    $('[name="mm'+i+'"]').val((meta).toFixed(0));
                  }
                  else{
                    $('[name="mm'+i+'"]').val(0);
                  }
                  
                  $("#m"+i).html('%');
                  $('[name="mm'+i+'"]').prop('disabled', true);
                }
                $('[name="mtotal"]').val((meta).toFixed(0));

                $('#matit').html('');
                $('#mbut').slideDown();

              }
              else{
                for (var i = 1; i <= 12; i++) {
                  $('[name="mm'+i+'"]').val((0).toFixed(0));
                  $("#mm"+i).html('');
                  $('[name="mm'+i+'"]').prop('disabled', false);
                }
                $('[name="mtotal"]').val((0).toFixed(0));
                
                programado = parseFloat($('[id="mtotal"]').val()); //// programado total
                meta = parseFloat($('[id="mmeta"]').val()); //// Meta
                if(meta==programado){
                  $('#matit').html('');
                  $('#mbut').slideDown();
                }
                else{
                  $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
                  $('#mbut').slideUp();
                }
              }
            }
            
          }
        });
    });

  /// ---- Suma Programado Modificado
    function suma_programado_modificado(){ 
      sum=0;
      linea = parseFloat($('[name="mlbase"]').val()); //// linea base
      codigo = parseFloat($('[name="mcod"]').val()); //// codigo
      for (var i = 1; i<=12; i++) {
        sum=parseFloat(sum)+parseFloat($('[name="mm'+i+'"]').val());
      }

      $('[name="mtotal"]').val((sum+linea).toFixed(2));
      programado = parseFloat($('[name="mtotal"]').val()); //// programado total
      meta = parseFloat($('[name="mmeta"]').val()); //// Meta

      if(programado!='' || programado!=0){
        if(programado!=meta){
          $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
          $('#mbut').slideUp();
        }
        else{

          if(codigo==0){
            $('#mbut').slideUp();
          }
          else{
            $('#matit').html('');
            $ ('#mbut').slideDown();
          }
        }
      }
      else{
        $('#mbut').slideUp();
      }
    }


    //// VERIF META (Modificado)
    function verif_meta_mod(){ /// meta
      meta = document.getElementById("mmeta").value;

      if(meta!='' & meta!=0){
          total = parseFloat($('[name="mtotal"]').val()); //// linea base
          indicador = parseFloat($('[name="mtipo_i"]').val()); //// Indicador
          tipo_meta = parseFloat($('[name="mtp_met"]').val()); //// tipo Meta

            if(indicador==2 & tipo_meta==1){
              for (var i = 1; i <=12; i++) {
                document.getElementById("mm"+i).value = parseInt(meta);
              }
              document.getElementById("mtotal").value = parseInt(meta);
              document.getElementById("mmeta").value = parseInt(meta);
              $('#mbut').slideDown();
            }
            else{
              if(indicador==2 & tipo_meta==5){
                  for (var i = 1; i <=12; i++) {
                    if(i==3 || i==6 || i==9 || i==12){
                      document.getElementById("mm"+i).value = parseInt(meta);
                    }
                    else{
                      document.getElementById("mm"+i).value = parseInt(0);
                    }
                    
                  }
                  document.getElementById("mtotal").value = parseInt(meta);
                  document.getElementById("mmeta").value = parseInt(meta);
                  $('#mbut').slideDown();
              }
              else{
                if(meta==total){
                  $('#matit').html('');
                  $('#mbut').slideDown();
                }
                else{
                  $('#matit').html('<center><div class="alert alert-danger alert-block">LA SUMA PROGRAMADA NO COINCIDE CON LA META DE LA ACTIVIDAD</div></center>');
                  $('#mbut').slideUp();
                }
              }

            }
      }
      else{
        $('#mbut').slideUp();
      }
    }


    //// Elimina Actividades Seleccionados
    function valida_eliminar(){
      if (document.del_req.tot.value=="" || document.del_req.tot.value==0){
        alertify.error("SELECCIONE ACTIVIDADES A ELIMINAR");
      }
      else{
        alertify.confirm("DESEA ELIMINAR "+document.del_req.tot.value+" ACTIVIDAD(es) ?", function (a) {
          if (a) {
              document.getElementById("btsubmit").value = "ELIMINANDO ACTIVIDAD(es)...";
              document.getElementById("btsubmit").disabled = true;
              document.del_req.submit();
              return true;
          } else {
              alertify.error("OPCI\u00D3N CANCELADA");
          }
        });
      }
    }


    //// ELiminar Actividad
/*  $(function () {
      function reset() {
        $("#toggleCSS").attr("href", base+"/assets/themes_alerta/alertify.default.css");
        alertify.set({
            labels: {
                ok: "ACEPTAR",
                cancel: "CANCELAR"
            },
            delay: 5000,
            buttonReverse: false,
            buttonFocus: "ok"
        });
      }

      // =====================================================================
    $(".del_ff").on("click", function (e) {
        reset();
        var prod_id = $(this).attr('name');
        var request;
        // confirm dialog
        alertify.confirm("DESEA ELIMINAR ACTIVIDAD ?", function (a) {
          if (a) { 
            var url = base+"index.php/programacion/producto/desactiva_producto";
            if (request) {
              request.abort();
            }
            request = $.ajax({
              url: url,
              type: "POST",
              dataType: "json",
              data: "prod_id="+prod_id
            });

            request.done(function (response, textStatus, jqXHR) { 
              reset();
            //  alert(response.verif)
              if (response.respuesta == 'correcto') {
                alertify.alert("LA ACTIVIDAD SE ELIMINO CORRECTAMENTE ", function (e) {
                  if (e) {
                    window.location.reload(true);
                  }
                });
              } else {
                alertify.alert("ERROR AL ELIMINAR LA ACTIVIDAD !!!", function (e) {
                  if (e) {
                    window.location.reload(true);
                  }
                });
              }
            });
              request.fail(function (jqXHR, textStatus, thrown) {
                console.log("ERROR: " + textStatus);
              });
              request.always(function () {
                  //console.log("termino la ejecuicion de ajax");
              });

              e.preventDefault();

          } else {
              // user clicked "cancel"
              alertify.error("OPERACION CANCELADA");
          }
        });
      return false;
    });
  });*/


  //// MODIFICACION POA (ELIMINAR FORM 4)
    $(function () {
          function reset() {
            $("#toggleCSS").attr("href", base+"/assets/themes_alerta/alertify.default.css");
            alertify.set({
                labels: {
                    ok: "ACEPTAR",
                    cancel: "CANCELAR"
                },
                delay: 5000,
                buttonReverse: false,
                buttonFocus: "ok"
            });
          }

          // =====================================================================
          $(".mdel_ff").on("click", function (e) {
              reset();
              var prod_id = $(this).attr('name');
              var cite_id = $(this).attr('id');

            //  alert(prod_id+'--'+cite_id)
              var request;
              // confirm dialog
              alertify.confirm("DESEA ELIMINAR ACTIVIDAD ?", function (a) {
                if (a) {
                    $("#loading-overlay").css("display", "flex");

                    // Construcción segura de la URL para evitar el 404
                    var url = base+"index.php/modificaciones/cmod_fisica/delete_modFormN4";

                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        data: { prod_id: prod_id, cite_id: cite_id },
                        success: function (response) {
                            if (response.respuesta == 'correcto') {
                                window.location.reload(true);
                                alertify.success("correcto !!!.");
                            } else {
                                $("#loading-overlay").hide(); // Liberar pantalla si hay error lógico
                                alertify.error("Error en la base de datos.");
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            // AQUÍ CAPTURAMOS EL 404 O CUALQUIER OTRO ERROR DE RED
                            $("#loading-overlay").hide(); // ¡IMPORTANTE! Ocultar el loading para que no bloquee
                            
                            if (jqXHR.status === 404) {
                                alertify.error("Error 404: No se encontró la ruta del controlador.");
                            } else {
                                alertify.error("Error de red: " + textStatus);
                            }
                            console.error("Detalle:", errorThrown);
                        }
                    });
                }
              });

            return false;
          });
      });






////////////////////////////// MODIFICACION POA
  $(function () {
      $("#cerrar_mod").on("click", function () {
          var $validator = $("#form_cerrar").validate({
                rules: {
                    cite_id: { //// cite
                      required: true,
                    },
                    observacion: { //// Observacion
                        required: true,
                    }
                },
                messages: {
                    observacion: "<font color=red>REGISTRE OBSERVACIÓN</font>",                     
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                unhighlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                },
                errorElement: 'span',
                errorClass: 'help-block',
                errorPlacement: function (error, element) {
                    if (element.parent('.input-group').length) {
                        error.insertAfter(element.parent());
                    } else {
                        error.insertAfter(element);
                    }
                }
            });

        var $valid = $("#form_cerrar").valid();
        if (!$valid) {
            $validator.focusInvalid();
        } else {
          alertify.confirm("CERRAR MODIFICACIÓN DE ACTIVIDADES ?", function (a) {
                if (a) {
                    document.getElementById("mload").style.display = 'block';
                    document.forms['form_cerrar'].submit();
                    document.getElementById("mbut").style.display = 'none';
                } else {
                    alertify.error("OPCI\u00D3N CANCELADA");
                }
            });
        }
      });

     
  });