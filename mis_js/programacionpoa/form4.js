
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

        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        $combo_indi.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

        if (val_id == 1) { /// Absoluto
            $('#tp_met' + prod_id).slideUp(150);
            $('#tp_met' + prod_id).find('select').val('3'); 
        } 
        else if (val_id == 2) { /// Relativo
            $('#tp_met' + prod_id).slideDown(150);
            var tipo_meta_actual = $('#tp_met' + prod_id).find('select').val();
        }

         // 2. Bloquea input meta global (Suma automática de meses)
            $('#meta' + prod_id).attr('disabled', 'disabled').css('background-color', '#f1f5f9');
            
            // 3. Libera de forma obligatoria los 12 meses para registro manual libre
            for (var i = 1; i <= 12; i++) {
                $('#m' + i + '_' + prod_id).removeAttr('disabled').css({'border-color': '#cbd5e1', 'background-color': '#e5fde5'});
            }

            calcular_sumatoria_meses_a_meta(prod_id)

        // Envío seguro mediante query string compatible con CI 1.5
        $.ajax({
            url: base + "index.php/programacion/producto/update_datos_form4_uresp",
            type: "POST",
            dataType: 'json',
            data: "prod_id=" + prod_id + "&id=" + val_id + "&tp=3" + token_seguridad,
            success: function(response) {
               // alert(response.respuesta+'----'+response.update_meta)
                if (response.respuesta == 'correcto') {
                    $combo_indi.css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});
                    
                    if (response.update_meta !== undefined) {
                        $("#meta" + prod_id).val(response.update_meta);
                    }
                    validar_coincidencia_meta_total(prod_id);
                    if (typeof alertify !== "undefined") alertify.success("✔ Tipo de indicador guardado.");
                }
            }
        });
    });

    /**
     * REGLA MÁSTER B: CAMBIO EN EL TIPO DE META OPERATIVA (RECURRENTE 1, MENSUAL 3, TRIMESTRAL 5)
     */
    $(document).on('change', 'select[name^="tp_met"], select[id^="tp_met"]', function() {
        var $combo_meta = $(this);
        var id_completo = $combo_meta.attr('id');
        var prod_id     = id_completo.replace('tp_met', '');
        var mt_id       = $combo_meta.val();

        // Captura perimetral de token de seguridad CSRF
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        // Alerta de diagnóstico 1: Comprobar qué datos envía el navegador
        console.log("CNS DIAGNÓSTICO -> URL armada:", base + "index.php/programacion/producto/update_datos_tpmeta");
        console.log("CNS DIAGNÓSTICO -> Datos a enviar: prod_id=" + prod_id + "&id=" + mt_id);

        $combo_meta.css({'border-color': '#eab308', 'background-color': '#fef9c3'});

        $.ajax({
            url: base + "index.php/programacion/producto/update_datos_tpmeta",
            type: "POST",
            dataType: 'json',
            data: "prod_id=" + prod_id + "&id=" + mt_id + token_seguridad,
            success: function(response) {
                // Alerta de diagnóstico 2: Imprimir en consola todo lo que responde el servidor si tiene éxito
                console.log("CNS DIAGNÓSTICO -> Servidor respondió éxito. JSON recibido:", response);

                if (response.respuesta == 'correcto') {
                    $combo_meta.css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});
                    $('#meta' + prod_id).val(response.producto['prod_meta']);
                    
                    if (response.producto['mt_id'] == 1 || response.producto['mt_id'] == 5) { 
                        $('#meta' + prod_id).prop('disabled', false).css('background-color', '#ffffff');
                        if (typeof alertify !== "undefined") {
                            var tipo_txt = (response.producto['mt_id'] == 1) ? "Meta Recurrente" : "Trimestre Recurrente";
                            alertify.log(tipo_txt + ": Meses bloqueados a 100.00");
                        }

                        for (var i = 1; i <= 12; i++) {
                            var $m_input = $('#m' + i + prod_id);
                            if ($m_input.length) {
                                $m_input.val(response.producto['m' + i])
                                        .prop('disabled', true)
                                        .css('background-color', '#f1f5f9');
                            }
                        }
                    }
                    else {
                        if (typeof alertify !== "undefined") {
                            alertify.log("Mensualizado Manual: Digitación libre habilitada.");
                        }

                        $('#meta' + prod_id).prop('disabled', true).css('background-color', '#ffffff');

                        for (var i = 1; i <= 12; i++) {
                            var $m_input = $('#m' + i + prod_id);
                            if ($m_input.length) {
                                $m_input.val(response.producto['m' + i])
                                        .prop('disabled', false)
                                        .css('background-color', '#e5fde5');
                            }
                        }
                    }

                    if (typeof validar_coincidencia_meta_total === "function") {
                        validar_coincidencia_meta_total(prod_id);
                    }

                    if (typeof alertify !== "undefined") alertify.success("✔ Distribución física asentada en Postgres.");
                } else {
                    // 🌟 ALERTA DE ERROR DE NEGOCIO: Si el controlador responde pero con un mensaje de error controlado
                    alert("🚨 RESPUESTA DEL CONTROLADOR PHP:\n\n" + response.message);
                    $combo_meta.css({'border-color': '#ef4444', 'background-color': '#ffeeec'});
                }
            },
            // 🛠️ REPARADO: Se inyectan parámetros forenses (xhr, status, error) para capturar el desplome de red
            error: function(xhr, textStatus, errorThrown) {
                $combo_meta.css({'border-color': '#ef4444', 'background-color': '#ffeeec'});
                
                // 🌟 ALERTA FORENSE MÁSTER: Captura el error crudo de Apache (Ej: Error 500, Error 404)
                var mensaje_alerta = "❌ FALLA CRÍTICA DE COMUNICACIÓN (APACHE / PHP)\n\n" +
                                     "• Estado HTTP: " + xhr.status + " (" + errorThrown + ")\n" +
                                     "• Estado Técnico: " + textStatus + "\n\n" +
                                     "Presiona F12 -> pestaña 'Console' para ver el volcado de error completo del servidor.";
                
                alert(mensaje_alerta);

                // Volcado forense en la consola del navegador para inspeccionar la línea de PHP rota
                console.error("CNS DETALLE CRUDO DEL ERROR ->", errorThrown);
                console.error("CNS TEXTO DEVUELTO POR EL SERVIDOR (HTML de error de CodeIgniter) ->\n", xhr.responseText);

                if (typeof alertify !== "undefined") {
                    alertify.error("❌ Error crítico de comunicación de red.");
                }
            }
        });
    });



    //// sumatoria de meses a la meta (Absoluto)
    function calcular_sumatoria_meses_a_meta(prod_id) {
        var suma = 0;
        for (var i = 1; i <= 12; i++) {
            var val = parseFloat($('#m' + i + '_' + prod_id).val());
            if (!isNaN(val)) suma += val;
        }
        $('#meta' + prod_id).val(suma.toFixed(2));
    }


    function validar_coincidencia_meta_total(prod_id) {
        // 1. Recuperamos el valor de la meta global definida en la pantalla
        var meta_definida = parseFloat($('#meta' + prod_id).val());
        if (isNaN(meta_definida)) meta_definida = 0;

        // 2. Ejecutamos el bucle matemático acumulador de Enero a Diciembre
        var suma_total_meses = 0;
        for (var i = 1; i <= 12; i++) {
            // 🛠️ COMPATIBILIDAD ESTRICTA: Mapea directamente a id="m145", id="m245"... sin guiones intermedios
            var $m_celda = $('#m' + i + prod_id);
            
            var v_mes = parseFloat($m_celda.val());
            if (!isNaN(v_mes)) {
                suma_total_meses += v_mes;
            }
        }

        // 3. COMPARACIÓN CON TOLERANCIA DECIMAL CONTABLE
        // Usamos Math.abs para mitigar variaciones ínfimas de centavos en coma flotante
        if (Math.abs(suma_total_meses - meta_definida) < 0.02) {
            // --- 🌟 CUADRE PERFECTO ---
            // Saca la alerta visual de peligro y restablece el fondo normal limpio
            $('#fila_prod_' + prod_id).css('background-color', 'transparent');
            $('#meta' + prod_id).css({
                'border-color': '#cbd5e1', 
                'background-color': '#ffffff',
                'color': 'blue'
            });
        } else {
            // --- 🚨 DESCUADRE OPERATIVO ---
            // Alerta al analista pintando la caja de la meta en un rosa suave de advertencia
            $('#meta' + prod_id).css({
                'border-color': '#ef4444', 
                'background-color': '#fef2f2',
                'color': '#b91c1c'
            });
        }
    }


    $(document).on('input', '.auto-save-field', function() {
        var $el = $(this);
        clearTimeout(timeout_maestro);
        timeout_maestro = setTimeout(function() {
            enviar_datos_maestro_caliente($el);
        }, 400); 
    });

    $(document).on('change', '.auto-save-field', function() {
        enviar_datos_maestro_caliente($(this));
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
 //   alert('hola')
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

    var campo_db = campo;
    if (campo == 'prod_form4')  campo_db = 'prod_producto';
    if (campo == 'prod_res')    campo_db = 'prod_resultado';
    if (campo == 'prod_uni')    campo_db = 'prod_unidades';

    $.ajax({
        type: "POST",
        url: base + "index.php/programacion/producto/guardar_campo_form4_en_caliente",
        data: "prod_id=" + prod_id + "&campo=" + campo_db + "&valor=" + encodeURIComponent(valor) + token_seguridad,
        dataType: "json",
        success: function(res) {
            if (res.status === 'success' || res.respuesta === 'correcto') {
                $elemento.css({'border-color': '#cbd5e1', 'background-color': '#ffffff'});

                // 🌟 REPARACIÓN MAESTRA EN VIVO: Si alteraron la meta global, repinta el cronograma
                if (campo === "prod_meta" || campo === "meta") {
                    var valor_meta = parseFloat(valor);
                    if (isNaN(valor_meta)) valor_meta = 0;

                    // Recuperamos el tipo de indicador y meta de la fila actual para aplicar la regla
                    var indi_id = $('#indi_id' + prod_id).val();
                    var mt_id   = $('#tp_met' + prod_id).find('select').val();

                    if (indi_id == 2) { // Solo si es indicador Relativo (%)
                        if (mt_id == 1) {
                            // --- 🌟 REPINTADO RECURRENTE: Clona el valor en los 12 meses en vivo ---
                            for (var i = 1; i <= 12; i++) {
                                $('#m' + i + prod_id).val(valor_meta.toFixed(2));
                            }
                        } 
                        else if (mt_id == 5) {
                            // --- 🌟 REPINTADO TRIMESTRAL: Clona solo en Marzo, Junio, Septiembre y Diciembre ---
                            for (var i = 1; i <= 12; i++) {
                                var monto_trim = (i == 3 || i == 6 || i == 9 || i == 12) ? valor_meta : 0;
                                $('#m' + i + prod_id).val(monto_trim.toFixed(2));
                            }
                        }
                    }
                }

                // Revalida de forma reactiva los colores de consistencia contable
                if (typeof validar_coincidencia_meta_total === "function") {
                    validar_coincidencia_meta_total(prod_id);
                }

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

    /// =============== Modal Subir Archivo Actividades form 4
    $(document).on('change', '#archivo', function() {
        // 🛠️ REPARADO: Se remueven las dobles barras inversas fijas de escape de PHP
        var fileName = $(this).val().split('\\').pop(); 
        if (fileName) {
            $('.file-name-display').val(fileName);
        }
    });

    /// Actividades
    $(document).on('click', '#btn_subir', function(e) {
        e.preventDefault();
        $('#mensaje').html(''); 

        // Validación preventiva en el cliente antes de consumir canal de red
        if ($('#archivo').val() == '') {
            $('#mensaje').html('<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>');
            if (typeof alertify !== "undefined") {
                alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .XLSX");
            }
            return false;
        }

        var form = $('#form_subir_actividades')[0];
        var data_multipart = new FormData(form);
        var $btn = $(this);

        // Bloquear interfaz de usuario (UI) e inyectar cargador animado institucional (Loader)
        $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA EN POSTGRES...');
        $('#loads').show();

        // Captura perimetral automática del Token CSRF por si está activo en la CNS
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        if (csrf_name !== '') {
            data_multipart.append(csrf_name, csrf_hash);
        }

        $.ajax({
            type: "POST",
            url: $('#form_subir_actividades').attr('action'),
            data: data_multipart,
            processData: false,
            contentType: false,
            success: function(response) {
                var res;
                try {
                    res = (typeof response === 'object') ? response : JSON.parse(response);
                } catch (err) {
                    console.error("Error parseando JSON:", response);
                    $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0;"><b>❌ Error de Transacción:</b> La respuesta de CodeIgniter devolvió un buffer de texto corrupto o PHP agotó su memoria.</div>');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR ACCIÓN');
                    $('#loads').hide();
                    return;
                }

                // Evalúa el éxito transaccional unificado para el SIIPLAS v2.0
                if (res.respuesta === 'correcto' || res.status === 'success') {
                    var mensaje_exito = res.mensaje || res.msj || "Registros de actividades migrados exitosamente.";
                    var conteo_filas  = res.filas_procesadas || res.conteo || "0";

                    // Construcción geométrica limpia del banner de auditoría aprobada
                    var html_success = `
                        <div class="alert alert-success text-center" style="border-left: 5px solid #2e7d32; background:#f0fdf4; color:#16a34a; padding:15px; margin-bottom:0;">
                            <i class="fa fa-check-circle fa-3x" style="margin-bottom:10px;"></i>
                            <h4 style="font-weight:bold; margin:0 0 5px 0; color:#15803d;">¡MIGRACIÓN COMPLETADA CON ÉXITO!</h4>
                            <p style="font-size: 12.5px; color:#166534; font-weight:500;">${mensaje_exito}</p>
                            <div style="margin: 10px 0;">
                                <span class="label label-success" style="font-size: 16px; padding: 4px 12px; font-weight:bold; background:#16a34a;">${conteo_filas}</span>
                            </div>
                            <p style="margin:0;"><small class="text-muted">Actividades y metas distribuidas en la base de datos de productos.</small></p>
                        </div>`;

                    $('#mensaje').html(html_success);
                    $('#loads').hide();
                    $btn.hide(); 

                    if (typeof alertify !== "undefined") {
                        alertify.success("✔ Plantilla procesada correctamente.");
                    }

                    // Temporizador inteligente multi-rol para recargar la grilla activa de la CNS
                    setTimeout(function() {
                        $('#modal_importar').modal("hide");
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('padding-right', '');

                        var combo_admin = $('#dist_id').val();
                        if (combo_admin !== undefined && combo_admin !== "" && combo_admin !== "0") {
                            // Si es Administrador Nacional, fuerza el change para recargar la regional consultada
                            $("#dist_id").trigger("change");
                        } else {
                            // Si es un Operador de Unidad Regional, usa la función reactiva o recarga
                            if (typeof forzar_refresco_grilla_siiplas_directo === "function") {
                                var dist_id_oculto = $('input[name="dist_id"]').val() || 0;
                                forzar_refresco_grilla_siiplas_directo(dist_id_oculto);
                            } else {
                                location.reload(); 
                            }
                        }
                    }, 2500);

                } else {
                    // MÓDULO DE EXTRACTOS DE ERRORES DE CONSISTENCIA DE CELDAS
                    var mensaje_error = res.mensaje || res.msj || "El archivo contiene celdas o tipados inválidos.";
                    var errorMsg = '<strong style="font-size:12px; color:#b91c1c;"><i class="fa fa-times-circle"></i> SE DETECTARON INCONSISTENCIAS EN LA PLANILLA EXCEL:</strong><br><small class="text-muted">' + mensaje_error + '</small>';
                    
                    if (res.errors || res.errores) {
                        var coleccion_errores = res.errors || res.errores;
                        errorMsg += "<ul style='margin-top:8px; padding-left:15px; text-align:left; font-size:11px;'>";
                        $.each(coleccion_errores, function(index, value) {
                            errorMsg += "<li>" + value + "</li>";
                        });
                        errorMsg += "</ul>";
                    }
                    
                    $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">' + errorMsg + '</div>');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                    $('#loads').hide();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error("Falla Crítica en canal de carga masiva de Excel. Detalle:", xhr.responseText);
                $('#loads').hide();
                $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR MIGRACIÓN');
                
                var txt_err = "❌ Error crítico de red (" + xhr.status + "): Imposible comunicar con el cargador de productos.";
                $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0;">' + txt_err + '</div>');
                
                if (typeof alertify !== "undefined") {
                    alertify.error("Falla de red en Apache.");
                }
            }
        });
    });


/// -------------------------------------------

  //// ELIMINAR ACTIVIDAD 2027
    function delete_form4(prod_id) {
        if (!prod_id || prod_id === undefined || prod_id === 0) {
            if (typeof alertify !== "undefined") alertify.error("❌ Identificador de actividad inválido.");
            return false;
        }

        // Cabecera institucional POA CNS de doble confirmación
        var mensaje_advertencia = "🚨 ¿ESTÁ SEGURO DE ELIMINAR ESTA ACTIVIDAD?<br><br>" +
                                  "<b>Consecuencias Transaccionales:</b><br>" +
                                  "• Se desactivará el registro del Formulario N° 4.<br>" +
                                  "• La numeración correlativa se re-ajustará en pantalla.<br><br>" +
                                  "<i>Esta acción quedará registrada en las bitácoras del SIIPLAS v2.0.</i>";

        // Alerta de confirmación clásica de Alertify
        alertify.confirm(mensaje_advertencia, function (a) {
            if (a) { 
                var url = base + "index.php/programacion/producto/desactiva_producto";
                
                // Buscamos la fila HTML exacta en el DOM para dar feedback visual inmediato
                var $fila_tr = $('#fila_prod_' + prod_id);
                $fila_tr.css({'background-color': '#fee2e2', 'opacity': '0.6'}); // Tono rojo de advertencia de borrado

                // Captura perimetral automática del Token CSRF de resguardo
                var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

                var request = $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                    data: "prod_id=" + prod_id + token_seguridad
                });

                request.done(function (response) { 
                    if (response.respuesta == 'correcto') {
                        
                        // 🌟 REPARACIÓN MÁSTER: Desvanecimiento de la fila en vivo sin recargar la pantalla
                        $fila_tr.fadeOut(400, function() {
                            $(this).remove(); // Remueve físicamente el nodo del árbol HTML
                            
                            if (typeof alertify !== "undefined") {
                                alertify.success("✔ Se eliminó correctamente la actividad.");
                            }

                            // 🌟 RE-ENUMERACIÓN EN CALIENTE: Ajusta de forma automatizada los códigos visuales (1, 2, 3...)
                            reordenar_codigos_actividades_pantalla_local();
                        });

                    } else {
                        // Si el servidor rebota por alguna restricción (ej: tiene insumos amarrados)
                        $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
                        if (typeof alertify !== "undefined") {
                            alertify.error("🚨 Error: " + (response.message || "No se pudo desactivar el registro."));
                        }
                    }
                });

                request.fail(function (jqXHR, textStatus, errorThrown) {
                    $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
                    console.error("CNS ERROR ELIMINACIÓN -> Status: " + textStatus + " | Detalle: " + errorThrown);
                    if (typeof alertify !== "undefined") alertify.error("❌ Error de comunicación con el servidor.");
                });

            } else {
                // El usuario canceló la acción
                if (typeof alertify !== "undefined") alertify.error("CANCELADA");
            }
        });

        return false;
    }

    /**
     * Re-enumera visualmente las actividades remanentes en pantalla tras una eliminación
     * Sincronizado al selector compacto id="prod_cod[ID]" de tu listado PHP
     */
    function reordenar_codigos_actividades_pantalla_local() {
        var correlativo = 0;
        // Recorremos todos los inputs de la grilla cuyos IDs inicien con 'prod_cod'
        $('input[id^="prod_cod"]').each(function() {
            correlativo++;
            $(this).val(correlativo.toFixed(2)); // Mantiene el formato con dos decimales de tu value="'.round(..., 2).'"
        });
    }
/*  function delete_form4(prod_id){
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
*/























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
/*  $(function () {
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
  });*/


/*      $(document).ready(function() {
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
      })*/;



/*      function verif_codigo(){ 
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
      }*/

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
      // function eliminar_requerimientos_UnidadReponsable(){
      //   alertify.confirm("DESEA ELIMINAR TODOS LOS REQUERIMIENTOS DE LA UNIDAD ?", function (a) {
      //     if (a) {
      //       window.location=base+"index.php/programacion/producto/delete_insumos_servicios/"+com_id;
      //     } else {
      //         alertify.error("OPCI\u00D3N CANCELADA");
      //     }
      //   });
      // }



    // /*------- ELIMINAR SOLO ACTIVIDADES Y REQUERIMIENTOS DE LA UNIDAD (TODOS) 2027 --------*/
   function eliminar_form4_todos(elemento) {
    // Capturamos el botón usando el parámetro 'elemento' o por su clase si se disparó directo
    var $btn_loading = (elemento) ? $(elemento) : $('a[onclick="eliminar_form4_todos()"]').first();
    var texto_original_btn = $btn_loading.html(); // Guardamos el HTML original (imagen + texto)

    // Capturamos de forma dinámica el com_id oculto del DOM de tu vista
    var com_id = $('input[name="com_id"]').val() || $('#com_id').val() || "";

    if (com_id === "" || com_id === "0") {
        if (typeof alertify !== "undefined") alertify.error("❌ Error: No se localizó el ID del Componente.");
        return false;
    }

    // Cabecera institucional POA CNS de máxima advertencia
    var mensaje_advertencia = "🚨 ¿ESTÁ COMPLETAMENTE SEGURO DE ELIMINAR TODO EL FORMULARIO?<br><br>" +
                              "<b>Consecuencias Críticas:</b><br>" +
                              "• Se borrarán TODAS las actividades de esta Unidad Organizacional.<br>" +
                              "• Se purgarán en cascada todos sus requerimientos e insumos (Form 5).<br><br>" +
                              "<i>Esta acción es destructiva e irreversible y se registra bajo auditoría.</i>";

    // Primera Alerta: Confirmación de intencionalidad de Alertify
    alertify.confirm(mensaje_advertencia, function (a) {
        if (a) {
            // Segunda Alerta: Doble confirmación para mitigar clics por error del operador
            alertify.confirm("🚨 CORRECCIÓN DE SEGURIDAD: ¿Confirma la purga absoluta del Formulario N° 4?", function (confirmado) {
                if (confirmado) {
                    
                    // 🛠️ ACTIVACIÓN DEL LOADING EN EL BOTÓN
                    // Deshabilitamos clicks futuros usando pointer-events y cambiamos el contenido por un spinner animado
                    $btn_loading.css({'pointer-events': 'none', 'opacity': '0.7'})
                                .html('<i class="fa fa-refresh fa-spin"></i>&nbsp;<b>ELIMINANDO REGISTRO ...</b>');

                    var url = base + "index.php/programacion/producto/delete_form4/" + com_id;
                    
                    // Bloqueamos la grilla visualmente dándole un fondo opaco de procesamiento
                    $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '0.5', 'background-color': '#fef2f2'});
                    if (typeof alertify !== "undefined") alertify.log("💥 Vaciando matriz en PostgreSQL, espere...");

                    // Captura perimetral automática del Token CSRF de resguardo de la CNS
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

                    // Despachamos la petición asíncrona
                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        data: "com_id=" + com_id + token_seguridad,
                        success: function (response) {
                            // Verificamos de forma elástica ambas banderas de éxito del servidor
                            if (response.respuesta == 'correcto' || response.status === 'success') {
                                
                                if (typeof alertify !== "undefined") {
                                    alertify.success("✔ Se vació correctamente el Formulario N° 4.");
                                }
                                
                                // Refresco limpio automático controlado tras un breve segundo de retraso
                                setTimeout(function() {
                                    window.location.reload(true);
                                }, 1500);

                            } else {
                                // 🛠️ RESTAURACIÓN DEL BOTÓN EN CASO DE ERROR CONTROLADO
                                $btn_loading.css({'pointer-events': 'auto', 'opacity': '1'}).html(texto_original_btn);

                                // Restablecemos los colores originales de la grilla si el controlador manda un error controlado
                                $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '1', 'background-color': '#ffffff'});
                                if (typeof alertify !== "undefined") {
                                    alertify.error("🚨 Error: " + (response.message || "No se pudo vaciar la matriz."));
                                }
                            }
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            // 🛠️ RESTAURACIÓN DEL BOTÓN EN CASO DE FALLA CRÍTICA DE RED / TIMEOUT
                            $btn_loading.css({'pointer-events': 'auto', 'opacity': '1'}).html(texto_original_btn);

                            $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '1', 'background-color': '#ffffff'});
                            console.error("CNS ERROR VACIADO TOTAL -> Status: " + textStatus + " | Detalle: " + errorThrown);
                            
                            // Muestra en la alerta el HTML de error de CodeIgniter si el servidor explota por base de datos
                            var msg_error_crudo = "❌ FALLA CRÍTICA EN EL CONTROLADOR PHP:\n\n" + 
                                                  "Código HTTP: " + xhr.status + " (" + errorThrown + ")\n\n" +
                                                  "Revise los logs de Apache o verifique la consola (F12).";
                            alert(msg_error_crudo);
                            
                            if (typeof alertify !== "undefined") alertify.error("❌ Falla de red. PostgreSQL abortó el vaciado.");
                        }
                    });
                }
            });
        } else {
            if (typeof alertify !== "undefined") alertify.error("OPCIÓN CANCELADA");
        }
    });
}


    /// Eliminar todos los requerimientos de las Actividades 2027
    function eliminar_requerimientos_UnidadReponsable(elemento) {
    // 🛠️ CAPTURA DEL BOTÓN: Usa el parámetro 'elemento' o lo busca de forma automática en el DOM
    var $btn_loading = (elemento) ? $(elemento) : $('a[onclick="eliminar_requerimientos_UnidadReponsable()"]').first();
    var texto_original_btn = $btn_loading.html(); // Guardamos el HTML original (imagen + texto)

    // Captura el com_id de forma elástica desde el árbol del DOM
    var com_id = $('input[name="com_id"]').val() || $('#com_id').val() || "";

    if (com_id === "" || com_id === "0") {
        if (typeof alertify !== "undefined") alertify.error("❌ Error: No se localizó el ID del Componente.");
        return false;
    }

    var mensaje_advertencia = "🚨 ¿ESTÁ COMPLETAMENTE SEGURO DE ELIMINAR TODOS LOS REQUERIMIENTOS?<br><br>" +
                              "<b>Consecuencias Transaccionales:</b><br>" +
                              "• Se borrarán TODOS los insumos y partidas del Formulario N° 5.<br>" +
                              "• Las actividades del Formulario N° 4 se mantendrán intactas pero con metas en cero.<br><br>" +
                              "<i>Esta acción es irreversible y afectará los techos financieros de la Unidad.</i>";

    // Primera Alerta: Confirmación de Alertify
    alertify.confirm(mensaje_advertencia, function (a) {
        if (a) {
            // Segunda Alerta: Doble confirmación de resguardo contra errores involuntarios
            alertify.confirm("🚨 CANDADO SIIPLAS: ¿Confirma el vaciado absoluto de requerimientos de insumos?", function (confirmado) {
                if (confirmado) {
                    
                    // 🛠️ ACTIVACIÓN DEL LOADING EN EL BOTÓN DEL FORM 5
                    // Congela los clics usando CSS y cambia el contenido por el spinner giratorio
                    $btn_loading.css({'pointer-events': 'none', 'opacity': '0.7'})
                                .html('<i class="fa fa-refresh fa-spin"></i>&nbsp;<b>ELIMINANDO REQUERIMIENTOS TOTAL...</b>');

                    var url = base + "index.php/programacion/producto/delete_insumos_Unidad_Responsable/" + com_id;
                    
                    // Feedback visual sutil (Fondo opaco de procesamiento)
                    $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '0.5', 'background-color': '#fef2f2'});
                    if (typeof alertify !== "undefined") alertify.log("💥 Purgando insumos en PostgreSQL, espere...");

                    // Captura automática del Token CSRF de seguridad de la CNS
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

                    $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        data: "com_id=" + com_id + token_seguridad,
                        success: function (response) {
                            if (response.respuesta == 'correcto' || response.status === 'success') {
                                
                                if (typeof alertify !== "undefined") {
                                    alertify.success("✔ Se eliminaron todos los requerimientos de la unidad.");
                                }
                                
                                // Refresco automático controlado tras breve segundo de retraso
                                setTimeout(function() {
                                    window.location.reload(true);
                                }, 1500);

                            } else {
                                // 🛠️ RESTAURACIÓN DEL BOTÓN EN CASO DE ERROR CONTROLADO
                                $btn_loading.css({'pointer-events': 'auto', 'opacity': '1'}).html(texto_original_btn);

                                // Restablecemos los estilos si el servidor devuelve un error de negocio o certificaciones
                                $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '1', 'background-color': '#ffffff'});
                                if (typeof alertify !== "undefined") {
                                    alertify.error("🚨 Error: " + (response.message || "No se pudo vaciar la matriz de insumos."));
                                }
                            }
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            // 🛠️ RESTAURACIÓN DEL BOTÓN EN CASO DE FALLA CRÍTICA DE RED
                            $btn_loading.css({'pointer-events': 'auto', 'opacity': '1'}).html(texto_original_btn);

                            $('table.tabla-datos, #contenido_grilla_poa').css({'opacity': '1', 'background-color': '#ffffff'});
                            console.error("CNS ERROR VACIADO F5 -> Status: " + textStatus + " | Detalle: " + errorThrown);
                            alert("❌ FALLA CRÍTICA EN EL CONTROLADOR PHP:\n\nCódigo HTTP: " + xhr.status + " (" + errorThrown + ")");
                            
                            if (typeof alertify !== "undefined") alertify.error("❌ Falla de red. PostgreSQL abortó la purga del Form 5.");
                        }
                    });
                }
            });
        } else {
            if (typeof alertify !== "undefined") alertify.error("OPCIÓN CANCELADA");
        }
    });
}

    ///// vigente para asignar prioridad
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

/*$(function () {
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
});*/


////--------------
  /*---- MODIFICAR FORMULARIO N 4 ---*/
/*    $(function () {

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
    });*/



  /*---- VER REQUERIMIENTOS CARGADOS POR UNIDAD RESPONSABLE ---*/
    $(function () {
        // 🌟 REPARACIÓN CORE: Ámbito global del objeto de petición para un Abort legítimo
        var xhr_requerimientos = null;

        /**
         * ESCUCHA OPTIMIZADA: Delegación global en el documento (Inmune a recargas AJAX de la grilla)
         */
        $(document).on("click", ".ver_requerimientos", function (e) {
            e.preventDefault();
            
            var $btn = $(this);
            // Usamos el atributo estándar data-id o name de forma segura
            var com_id = $btn.attr('name') || $btn.data('id'); 

            if (!com_id || com_id === "0" || com_id === "") {
                if (typeof alertify !== "undefined") alertify.error("⚠️ Identificador de componente inválido.");
                return false;
            }

            // 🌟 REPARADO: Cancelación legítima de peticiones fantasmas en cola ante doble clic
            if (xhr_requerimientos && xhr_requerimientos.readyState !== 4) {
                xhr_requerimientos.abort();
                console.log("CNS OPTIMIZACIÓN -> Petición previa abortada para salvaguardar canal de red.");
            }

            // Inyección inmediata del Pre-loader institucional de la CNS
            $('#contenido').html(
            '<div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 260px; padding: 40px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); margin: 15px 0; transition: all 0.3s ease;">' +
                
                // Spinner Vectorial con CSS puro (Sin GIFs pesados de red)
                '<div style="position: relative; width: 50px; height: 50px; margin-bottom: 20px;">' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 48px; height: 48px; border: 4px solid #cbd5e1; border-radius: 50%;"></div>' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 48px; height: 48px; border: 4px solid transparent; border-top-color: #2563eb; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>' +
                '</div>' +
                
                // Mensaje Principal Corporativo
                '<h4 style="font-family: Helvetica, Arial, sans-serif; font-weight: 700; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 6px 0; padding: 0;">' +
                    'Sincronizando Requerimientos' +
                '</h4>' +
                
                // Micro-texto de progreso de red
                '<p style="font-family: Helvetica, Arial, sans-serif; font-size: 11.5px; color: #64748b; margin: 0; padding: 0; font-weight: 500;">' +
                    '<i class="fa fa-database text-primary" style="animation: pulse 1.5s infinite; margin-right: 4px;"></i> ' +
                    'Conectando con la base de datos PostgreSQL, un momento por favor...' +
                '</p>' +
                
                // Inyección dinámica de la regla CSS de animación (Blindado contra archivos CSS externos desactualizados)
                '<style>' +
                    '@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
                    '@keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }' +
                '</style>' +
            '</div>'
        );

            // Captura perimetral automática del Token CSRF de resguardo
            var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
            var csrf_hash = $('[name="csrf_test_name"]').val() || '';
            var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

            // Ejecución transaccional AJAX optimizada
            xhr_requerimientos = $.ajax({
                url: base + "index.php/programacion/producto/get_ver_requerimientos",
                type: "POST",
                dataType: 'json',
                cache: false, // Evita que el navegador cachee listados desactualizados
                data: "com_id=" + com_id + token_seguridad
            });

            // Respuesta Exitosa
            xhr_requerimientos.done(function (response) {
                if (response.respuesta === 'correcto' || response.status === 'success') {
                    // fadeIn controlado sin parpadeos bruscos de interfaz
                    $('#contenido').hide().html(response.tabla).fadeIn(300);
                    if (typeof alertify !== "undefined") alertify.success("✔ Requerimientos cargados.");
                } else {
                    $('#contenido').html('<div class="alert alert-danger">❌ Error: ' + (response.message || 'No se pudieron procesar los servicios.') + '</div>');
                    if (typeof alertify !== "undefined") alertify.error("ERROR AL RECUPERAR DATOS DE LOS SERVICIOS");
                }
            });

            // Falla de Canal de Red
            xhr_requerimientos.fail(function (jqXHR, textStatus, errorThrown) {
                // Ignoramos la falla si fue provocada intencionalmente por nuestro propio .abort()
                if (textStatus === 'abort') return;

                console.error("CNS SIIPLAS CRITICAL ERROR -> Status: " + textStatus + " | Detalle: " + errorThrown);
                
                var msg_caida = '<div class="alert alert-danger" style="margin:10px 0;">' +
                                    '<strong>❌ Error de Red (' + jqXHR.status + '):</strong> ' +
                                    'Imposible comunicar con el catálogo de servicios. Intente de nuevo.' +
                                '</div>';
                $('#contenido').html(msg_caida);
            });
        });
    });















    ///// MODULO DE MODIFICACION POA 2027
    /// ------ Editar Datos de Modificacion POA (Formulario N° 4)
$(document).ready(function() {
    
    // 🌟 CONTROL DE CONTEXTO GLOBAL DE RED (FUERA DE LOS EVENTOS)
    var request_mod_f4 = null;
    var xhr_guardar_f4 = null;

    /* ==========================================================================
       1. EVENTO: APERTURA Y RECUPERACIÓN ASÍNCRONA DE LA ACTIVIDAD (.mod_form4)
       ========================================================================== */
    $(document).on("click", ".mod_form4", function (e) {
        e.preventDefault();
        
        var prod_id = $(this).attr('name');
        document.getElementById("prod_id").value = prod_id;

        var url = base + "index.php/modificaciones/cmod_fisica/get_form4_mod";

        // Limpieza preventiva de alertas y ocultamiento de botones de guardado
        $('#matit').html('');
        $('#mbut').hide();

        // INYECCIÓN DE PRELOADER VECTORIAL AZUL INSTITUCIONAL MIENTRAS RESPONDEN LAS TABLAS
        $('#matit').html(
            '<div id="loading_modal_f4" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; padding: 20px; background: #ffffff;">' +
                '<div style="position: relative; width: 45px; height: 45px; margin-bottom: 15px;">' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 40px; height: 40px; border: 4px solid #e2e8f0; border-radius: 50%;"></div>' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 40px; height: 40px; border: 4px solid transparent; border-top-color: #2563eb; border-radius: 50%; animation: spin_f4_mod 0.8s linear infinite;"></div>' +
                '</div>' +
                '<h5 style="font-family: Arial, sans-serif; font-weight: 700; color: #1e293b; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 4px 0;">Buscando Actividad</h5>' +
                '<p style="font-family: Arial, sans-serif; font-size: 11px; color: #64748b; margin: 0; font-weight: 500;">' +
                    '<i class="fa fa-database text-warning" style="animation: pulse_f4_mod 1.5s infinite; margin-right: 4px;"></i> Extrayendo metas y cronograma físico desde PostgreSQL...' +
                '</p>' +
                '<style>' +
                    '@keyframes spin_f4_mod { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
                    '@keyframes pulse_f4_mod { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }' +
                '</style>' +
            '</div>'
        );

        // Abortamos solicitudes simultáneas previas en cola de red
        if (request_mod_f4 && request_mod_f4.readyState !== 4) {
            request_mod_f4.abort();
        }

        request_mod_f4 = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "prod_id=" + prod_id
        });

        request_mod_f4.done(function (response, textStatus, jqXHR) {
            // Limpiamos el loader de espera
            $('#matit').html('');

            if (response.respuesta == 'correcto' || response.status == 'success') {
                // Mostramos la botonera de guardado al cargar con éxito
                $('#mbut').show();

                // 🌟 REPARADO: Mapeo directo de propiedades sin el índice [0] gracias al ajuste del backend
                document.getElementById("mcod").value = response.producto.prod_cod; 
                document.getElementById("mprod").value = response.producto.prod_producto; 
                document.getElementById("mresultado").value = response.producto.prod_resultado;
                document.getElementById("mverificacion").value = response.producto.prod_fuente_verificacion;
                
                var meta_maestra = parseInt(response.producto.prod_meta) || 0;
                document.getElementById("mmeta").value = meta_maestra;

                document.getElementById("mtipo_i").value = response.producto.indi_id;
                document.getElementById("mlbase").value = parseInt(response.producto.prod_linea_base) || 0;
                document.getElementById("mtp_met").value = response.producto.mt_id;
                document.getElementById("mindicador").value = response.producto.prod_indicador;

                $('#resp').html(response.uresponsable);
                $('#alineacion_form2').html(response.alineacion_form2);

                // Control de edición condicional según el trimestre vigente
                var es_editable_trimestre = (parseInt(response.trimestre) === 1);
                document.getElementById("mtipo_i").disabled = !es_editable_trimestre;
                document.getElementById("mlbase").disabled  = !es_editable_trimestre;
                document.getElementById("mtp_met").disabled = !es_editable_trimestre;
               
                // --- EVALUACIÓN DE LA TEMPORALIDAD CONTABLE DE LA ACTIVIDAD ---
                if (parseInt(response.producto.indi_id) === 2 && (parseInt(response.producto.mt_id) === 1 || parseInt(response.producto.mt_id) === 5)) { 
                    // CASO A: Meta Recurrente Mensual o trimestral
                    for (var i = 1; i <= 12; i++) {
                        var input_mes = document.getElementById("mm" + i);
                        var $label_mes = $('#e' + i);
                        var texto_mes = (response.mes[i]) ? response.mes[i].toUpperCase() : '';

                        if(input_mes) input_mes.disabled = true;
                        $label_mes.html('<span style="color: #dc2626; font-weight: bold;"><i class="fa fa-lock"></i> ' + texto_mes + '</span>');
                    }
                    for (var i = 1; i <= 12; i++) {
                        var input_mes = document.getElementById("mm" + i);
                        if(input_mes) {
                            input_mes.value = parseInt(response.producto['m' + i]) || 0;
                            input_mes.disabled = true;
                        }
                    }
                    $('[name="mtotal"]').val(meta_maestra.toFixed(0));
                    document.getElementById("mtrep").style.display = 'block';
                } else { 
                    // CASO B: Meta Acumulada
                    for (var i = 1; i <= 12; i++) {
                        var input_mes = document.getElementById("mm" + i);
                        var $label_mes = $('#e' + i);
                        var texto_mes = (response.mes[i]) ? response.mes[i].toUpperCase() : '';

                        if (i <= parseInt(response.mes_actual)) {
                            if(input_mes) input_mes.disabled = true;
                            $label_mes.html('<span style="color: #dc2626; font-weight: bold;"><i class="fa fa-lock"></i> ' + texto_mes + '</span>');
                        } else {
                            if(input_mes) input_mes.disabled = false;
                            $label_mes.html('<span style="color: #16a34a; font-weight: bold;"><i class="fa fa-unlock"></i> ' + texto_mes + '</span>');
                        }
                    }

                    for (var i = 1; i <= 12; i++) {
                        var input_mes = document.getElementById("mm" + i);
                        if(input_mes) input_mes.value = parseInt(response.producto['m' + i]) || 0;
                    }

                    var total_anual_acumulado = parseInt(response.producto.total_anual) || 0;
                    $('[name="mtotal"]').val(total_anual_acumulado.toFixed(0));
                    document.getElementById("mtrep").style.display = 'none';
                    
                    var prog = parseFloat($('[name="mtotal"]').val()) || 0;
                    var meta = parseFloat($('[name="mmeta"]').val()) || 0;

                    // Validación de concordancia horizontal antes del guardado
                    if (prog === meta) {
                        $('#matit').html('');
                        $('#mbut').show();
                    } else {
                        $('#matit').html('<center><div class="alert alert-danger alert-block" style="font-weight:bold; margin-top:10px;">⚠️ LA SUMA PROGRAMADA DE LOS MESES ('+prog+') NO COINCIDE CON LA META DE LA ACTIVIDAD ('+meta+')</div></center>');
                        $('#mbut').hide();
                    }
                }
            } else {
                if (typeof alertify !== "undefined") alertify.error("ERROR AL RECUPERAR DATOS DE LA ACTIVIDAD");
            }
        });

        request_mod_f4.fail(function (jqXHR, textStatus) {
            if (textStatus !== 'abort') {
                $('#matit').html('<div class="alert alert-danger text-center" style="font-weight:bold;">❌ Error de comunicación con el servidor CNS.</div>');
            }
        });
    });

    /* ==========================================================================
       2. EVENTO INDEPENDIENTE: PERSISTENCIA Y GUARDADO ASÍNCRONO DEL SMART-FORM
       ========================================================================== */
    $(document).on("click", "#subir_form4", function (e) {
        e.preventDefault();

        // Configuración de jQuery Validate sobre el formulario
        var $validator = $("#form_mod").validate({
            rules: {
                prod_id: { required: true },
                mprod: { required: true },
                mresultado: { required: true },
                mtipo_i: { required: true },
                mindicador: { required: true },
                mlbase: { required: true, number: true },
                mmeta: { required: true, digits: true }
            },
            highlight: function (element) {
                $(element).closest('section').removeClass('has-success').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('section').removeClass('has-error').addClass('has-success');
            },
            errorElement: 'span',
            errorClass: 'help-block'
        });

        if (!$("#form_mod").valid()) {
            $validator.focusInvalid();
            return false;
        }

        // Control horizontal estricto antes de consumir red
        var prog_total = parseFloat($('[name="mtotal"]').val()) || 0;
        var meta_total = parseFloat($('[name="mmeta"]').val()) || 0;
        var tipo_ind    = parseInt($("#mtipo_i").val()) || 0;
        var tipo_meta   = parseInt($("#mtp_met").val()) || 0;

        if (!(tipo_ind === 2 && tipo_meta === 1)) {
        if (prog_total !== meta_total) {
            if (typeof alertify !== "undefined") {
                alertify.error("🚨 Descuadre Físico: La suma de mensualidades no coincide con la Meta de la actividad.");
                }
                return false;
            }}
        if (typeof alertify !== "undefined") {   
        alertify.confirm("🚨 ¿CONFIRMA LA ACTUALIZACIÓN EN CALIENTE DE ESTA ACTIVIDAD?", function (a) {
        if (a) {
            $("#loading-overlay").css("display", "flex");
            $("#mbut").hide();
            $("#subir_form4").prop("disabled", true).text("PROCESANDO...");
            var formElement = document.getElementById('form_mod');
            var data_multipart = new FormData(formElement);
            var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
            var csrf_hash = $('[name="csrf_test_name"]').val() || '';
            if (csrf_name !== '') { 
            data_multipart.append(csrf_name, csrf_hash); }
            if (xhr_guardar_f4 && xhr_guardar_f4.readyState !== 4) {
                xhr_guardar_f4.abort();}
                xhr_guardar_f4 = $.ajax({
                    type: "POST",url: $("#form_mod").attr('action'),data: data_multipart,processData: false,contentType: false,success: function (res) { $("#loading-overlay").hide();$("#subir_form4").prop("disabled", false).html(' Guardar Modificación');var response = (typeof res === 'object') ? res : JSON.parse(res);if (response.respuesta === 'correcto' || response.status === 'success') {alertify.success("✔ " + (response.message || "Actividad modificada correctamente."));$('#modal_mod_form4').modal('hide');$('.modal-backdrop').remove();$('body').removeClass('modal-open');
                    var com_id_global = $('#com_id').val();
                    if (typeof recargar_listado_actividades_cns_ajax === "function") {
                        recargar_listado_actividades_cns_ajax(com_id_global);
                    } else {
                        location.reload();}
                    } else {
                        $("#mbut").show();
                        alertify.error("🚨 Rechazo: " + response.message);}
                    },error: function (xhr, textStatus) {
                        $("#loading-overlay").hide();
                        $("#mbut").show();
                        $("#subir_form4").prop("disabled", false).html(' Guardar Modificación');
                        if (textStatus !== 'abort') {
                            alertify.error("❌ Falla crítica de red. PostgreSQL abortó la modificación.");}
                        }
                    });
                }
            });
        }});
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
    function suma_programado_modificado() { 
        var sum = 0;
        
        // 🛠️ REPARADO CORE: La Línea Base NO se suma a la programación actual (Es solo histórico)
        // El bucle lee las 12 cajas del DOM de forma ágil
        for (var i = 1; i <= 12; i++) {
            var valor_mes = parseFloat($('[name="mm' + i + '"]').val()) || 0;
            sum += valor_mes;
        }

        // Estampamos la sumatoria pura de los 12 meses en la caja deshabilitada
        $('[name="mtotal"]').val(sum.toFixed(0)); // Las metas físicas de actividades son enteros nativos

        var programado = sum; 
        var meta = parseFloat($('[name="mmeta"]').val()) || 0;
        var codigo = parseInt($('[name="mcod"]').val()) || 0;

        // 🛠️ REPARADO: Operador lógico estricto && para descartar vacíos o ceros reales
        if (programado > 0 && meta > 0) {
            
            // --- ESCENARIO A: DESCUADRE FÍSICO HORIZONTAL ---
            if (programado !== meta) {
                $('#matit').html(
                    '<div style="text-align:center; margin-top:10px;">' +
                        '<div class="alert alert-danger alert-block" style="font-weight:bold; padding:8px; margin:0;">' +
                            '⚠️ LA SUMA PROGRAMADA DE LOS MESES (' + programado + ') NO COINCIDE CON LA META DE LA ACTIVIDAD (' + meta + ')' +
                        '</div>' +
                    '</div>'
                );
                $('#mbut').slideUp(130);
            } 
            // --- ESCENARIO B: CUADRE COHERENTE DE METAS ---
            else {
                // Candado de validación institucional: Código de actividad no puede ser cero
                if (codigo <= 0) {
                    $('#matit').html(
                        '<div style="text-align:center; margin-top:10px;">' +
                            '<div class="alert alert-warning alert-block" style="font-weight:bold; padding:8px; margin:0;">' +
                                '⚠️ EL CÓDIGO CORRELATIVO DE LA ACTIVIDAD DEBE SER MAYOR A CERO.' +
                            '</div>' +
                        '</div>'
                    );
                    $('#mbut').slideUp(130);
                } else {
                    // Si todo está perfectamente cuadrado, limpiamos advertencias y liberamos el botón "Guardar"
                    $('#matit').html('');
                    $('#mbut').slideDown(130);
                }
            }
        } else {
            // Si el formulario está vacío o en cero, ocultamos el panel de persistencia
            $('#mbut').slideUp(130);
        }
    }


    function verif_meta_mod() {
        // 🛠️ REPARADO: Declaración de variables con ámbito local seguro (let / var)
        var meta_input = document.getElementById("mmeta").value;
        var meta = parseInt(meta_input);

        // 🛠️ REPARADO: Reemplazados los operadores binarios & por operadores lógicos &&
        if (meta_input !== "" && meta > 0) {
          
            var total = parseFloat($('[name="mtotal"]').val()) || 0;
            var indicador = parseInt($('[name="mtipo_i"]').val()) || 0;
            var tipo_meta = parseInt($('[name="mtp_met"]').val()) || 0;

            // --- CASO 1: INDICADOR PORCENTUAL (2) Y META RECURRENTE (1) ---
            if (indicador === 2 && tipo_meta === 1) {
                for (var i = 1; i <= 12; i++) {
                    var input_mes = document.getElementById("mm" + i);
                    if (input_mes) input_mes.value = meta;
                }
                // 🛠️ REPARADO: Inyección forzada por jQuery para inputs deshabilitados (disabled)
                $('[name="mtotal"]').val(meta.toFixed(0));
                document.getElementById("mmeta").value = meta;
                
                $('#matit').html('');
                $('#mbut').slideDown(150);
            }
            // --- CASO 2: INDICADOR PORCENTUAL (2) Y META TRIMESTRAL (5) ---
            else if (indicador === 2 && tipo_meta === 5) {
                for (var i = 1; i <= 12; i++) {
                    var input_mes = document.getElementById("mm" + i);
                    if (input_mes) {
                        if (i === 3 || i === 6 || i === 9 || i === 12) {
                            input_mes.value = meta;
                        } else {
                            input_mes.value = 0;
                        }
                    }
                }
                // 🛠️ REPARADO: Inyección por jQuery para evitar bloqueos del DOM
                $('[name="mtotal"]').val(meta.toFixed(0));
                document.getElementById("mmeta").value = meta;
                
                $('#matit').html('');
                $('#mbut').slideDown(150);
            }
            // --- CASO 3: METAS ACUMULADAS / ABSOLUTAS (Validación Horizontal Estricta) ---
            else {
                if (meta === total) {
                    $('#matit').html('');
                    $('#mbut').slideDown(150);
                } else {
                    // Alerta elástica de descuadre financiero regional
                    $('#matit').html(
                        '<div style="text-align:center; margin-top:10px;">' +
                            '<div class="alert alert-danger alert-block" style="font-weight:bold; padding:8px; margin:0;">' +
                                '⚠️ LA SUMA PROGRAMADA DE LOS MESES (' + total + ') NO COINCIDE CON LA NUEVA META DE LA ACTIVIDAD (' + meta + ')' +
                            '</div>' +
                        '</div>'
                    );
                    $('#mbut').slideUp(150);
                }
            }
        } else {
            $('#matit').html(
                        '<div style="text-align:center; margin-top:10px;">' +
                            '<div class="alert alert-danger alert-block" style="font-weight:bold; padding:8px; margin:0;">' +
                                '⚠️ Registre Meta' +
                            '</div>' +
                        '</div>'
                    );
            // Si el campo se vacía o se pone en cero, escondemos el botón de guardar inmediatamente
            $('#mbut').slideUp(150);
        }
    }


    //// Elimina Actividades Seleccionados
/*    function valida_eliminar(){
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
    }*/



  //// MODIFICACION POA (ELIMINAR FORM 4)
    $(document).ready(function() {
    
    // Variable global de contexto de red para el aborto seguro de colas
    var xhr_eliminar_actividad = null;

    // Inicializador higiénico de las propiedades de alertas Alertify
    function reset_alertify_config() {
        if (typeof alertify !== "undefined") {
            alertify.set({
                labels: { ok: "ACEPTAR", cancel: "CANCELAR" },
                delay: 5000,
                buttonReverse: false,
                buttonFocus: "ok"
            });
        }
    }

    // =====================================================================
    // 🚨 MÓDULO CNS: ELIMINACIÓN FÍSICA ASÍNCRONA DE ACTIVIDAD (FORM 4)
    // =====================================================================
    $(document).on("click", ".mdel_ff", function (e) {
        e.preventDefault();
        reset_alertify_config();

        var $btn = $(this);
        var $fila_dom = $btn.closest('tr'); // Capturamos el nodo de la hilera en la grilla HTML
        
        var prod_id = $btn.attr('name') || $btn.data('prod');
        var cite_id = $btn.attr('id') || $btn.data('cite');

        if (!prod_id || !cite_id) {
            if (typeof alertify !== "undefined") alertify.error("⚠️ Error: Identificadores corruptos en la fila.");
            return false;
        }

        if (typeof alertify !== "undefined") {
            alertify.confirm("🚨 <b>¿ESTÁ SEGURO DE ELIMINAR ESTA ACTIVIDAD DEFINITIVAMENTE?</b><br><br><i>El motor del SIIPLAS v2.0 purgará de forma física el registro, su cronograma de metas programadas de Enero a Diciembre y sus ejecuciones de PostgreSQL. Esta acción no se puede deshacer.</i>", function (a) {
                if (a) {
                    
                    // Bloqueamos la interfaz visual e inyectamos el preloader de red
                    $("#loading-overlay").css("display", "flex");

                    var url = base + "index.php/modificaciones/cmod_fisica/delete_modFormN4";

                    // Mapeo e Inyección automática del Token CSRF de seguridad de la CNS
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    
                    var datos_post = { prod_id: prod_id, cite_id: cite_id };
                    if (csrf_name !== '') { datos_post[csrf_name] = csrf_hash; }

                    // Abortamos hilos de red huérfanos concurrentes en cola
                    if (xhr_eliminar_actividad && xhr_eliminar_actividad.readyState !== 4) {
                        xhr_eliminar_actividad.abort();
                    }

                    // Despachamos la ráfaga asíncrona hacia CodeIgniter
                    xhr_eliminar_actividad = $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        data: datos_post,
                        success: function (response) {
                            $("#loading-overlay").hide();

                            if (response.respuesta === 'correcto' || response.status === 'success') {
                                
                                // 🌟 REPARADO CORE: Eliminación reactiva del nodo en el DOM con desvanecimiento sutil
                                // Evita el reload total y la descarga masiva de megabytes redundantes de Apache
                                $fila_dom.fadeOut(350, function() {
                                    $(this).remove();
                                    
                                    // Re-enumeramos dinámicamente los correlativos numéricos de la primera columna
                                    $('table#dt_basic4 tbody tr').each(function(index) {
                                        $(this).find('td:first').text(index + 1);
                                    });
                                });

                                alertify.success("✔ La actividad se eliminó físicamente de PostgreSQL con éxito.");

                            } else {
                                alertify.error("🚨 Rechazo: " + (response.message || "PostgreSQL denegó la purga del registro."));
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            if (textStatus === 'abort') return;
                            $("#loading-overlay").hide();
                            
                            if (jqXHR.status === 404) {
                                alertify.error("❌ Error 404: Ruta de destino del controlador no localizada.");
                            } else {
                                alertify.error("❌ Error de comunicación: " + textStatus);
                            }
                            console.error("CNS EXCEPTION DELETE ->", errorThrown);
                        }
                    });
                } else {
                    alertify.log("Operación de purga abortada. El registro permanece intacto.");
                }
            });
        }
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


  //// nuevo registro formulario N | 4
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