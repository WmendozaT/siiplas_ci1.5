base   = $('[name="base"]').val();

function abreVentana(url) {
    var elemento = window.event ? window.event.target.closest('a') : null;
    var tituloFinal = (elemento && elemento.title) ? elemento.title : "Reporte POA...";
    var ancho = 1000;
    var alto = 800;
    var posicion_x = (screen.width / 2) - (ancho / 2);
    var posicion_y = (screen.height / 2) - (alto / 2);

    // 1. Abrimos la ventana vacía primero
    var nuevaVentana = window.open('', '_blank', "width=" + ancho + ",height=" + alto + ",menubar=0,toolbar=0,directories=0,scrollbars=no,resizable=no,left=" + posicion_x + ",top=" + posicion_y);

    // 2. Inyectamos un HTML de carga estético mientras llega la respuesta del servidor
    nuevaVentana.document.write(`
        <html>
            <head>
                <title>Cargando Reporte POA...</title>
                <style>
                    body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; background: #f4f4f4; }
                    .loader-container { text-align: center; }
                    .spinner { border: 8px solid #f3f3f3; border-top: 8px solid #5B9360; border-radius: 50%; width: 60px; height: 60px; animation: spin 1s linear infinite; margin: 0 auto 20px; }
                    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                    h2 { color: #333; }
                </style>
            </head>
            <body>
                <div class="loader-container">
                    <div class="spinner"></div>
                    <h2>Generando ${tituloFinal}</h2>
                    <p>Por favor, espere un momento.</p>
                </div>
            </body>
        </html>
    `);

    // 3. Redirigimos la ventana a la URL real del reporte
    nuevaVentana.location.href = url;
}

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
    function toggleColumnaMes(mesId) {
        var celdas = jQuery('.col-mes-' + mesId);
        var contenidoInterno = jQuery('.wrapper-mes-' + mesId);
        var boton = jQuery('#btn_toggle_' + mesId);
        var textoNombreMes = jQuery('.txt-nombre-mes-' + mesId);

        if (celdas.data('oculto') === true) {
            // 🔓 MOSTRAR: Volver al tamaño original
            celdas.css({
                'width': '33%',
                'padding': '4px'
            });
            contenidoInterno.show();
            textoNombreMes.show();
            
            // Restaurar el botón superior a su estado original
            boton.html('<i class="fa fa-eye-slash"></i> Ocultar');
            boton.css({
                'background': '#475569',
                'width': '100%',
                'padding': '4px'
            });
            
            celdas.data('oculto', false);
        } else {
            // 🔒 OCULTAR: Reducir estrictamente a 5px
            contenidoInterno.hide();
            textoNombreMes.hide();
            
            celdas.css({
                'width': '5px',
                'padding': '0px' // Eliminamos paddings para permitir los 5px reales
            });
            
            // Compactar el botón superior a un estado mínimo (se convierte en una línea verde delgada para volver a dar clic)
            boton.html('<i class="fa fa-eye"></i>');
            boton.attr('title', 'Mostrar este mes');
            boton.css({
                'background': '#16a34a',
                'width': '100%',
                'padding': '4px 0px'
            });
            
            celdas.data('oculto', true);
        }
    }


    //// Guardar Informacion POA mensual
    function guardarSeguimiento(prodId, mes) {
        // 1. Capturar los valores de los elementos del bloque
        var ejecVal   = jQuery('#ejec_' + prodId + '_' + mes).val();
        var mverifVal = jQuery('#mverif_' + prodId + '_' + mes).val().trim();
        var probVal   = jQuery('#prob_' + prodId + '_' + mes).val().trim();
        var accVal    = jQuery('#acc_' + prodId + '_' + mes).val().trim();

        // 2. 🚨 VALIDACIÓN ESTRICTA: Si la ejecución es 0 o vacía, Problemas y Acciones son obligatorios
        if (ejecVal.trim() === '' || isNaN(ejecVal) || parseFloat(ejecVal) === 0) {
            if (probVal === '' || accVal === '') {
                
                // Creamos un contenedor de alerta moderno directamente en el HTML
                var alertId = 'custom_alert_' + prodId + '_' + mes;
                // Si ya existe una alerta abierta, no duplicarla
                if (!document.getElementById(alertId)) {
                    var alertHtml = '<div id="' + alertId + '" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 99999; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 13px; font-weight: bold; min-width: 300px; text-align: center;">' +
                        '<i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Si la ejecución mensual es cero (0) o está vacía, debe registrar "PROBLEMAS PRESENTADOS" y "ACCIONES REALIZADAS".' +
                        '<br><button type="button" onclick="jQuery(\'#' + alertId + '\').remove();" style="margin-top: 10px; background-color: #856404; color: #fff; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; font-size: 11px;">Entendido</button>' +
                    '</div>';
                    jQuery('body').append(alertHtml);
                }

                // Enfocar el campo vacío automáticamente
                if (probVal === '') {
                    jQuery('#prob_' + prodId + '_' + mes).focus();
                } else {
                    jQuery('#acc_' + prodId + '_' + mes).focus();
                }
                return false;
            }
        }
        else {
            if (mverifVal === '') {
                var alertId = 'custom_alert_' + prodId + '_' + mes;
                // Si ya existe una alerta abierta, no duplicarla
                if (!document.getElementById(alertId)) {
                    var alertHtml = '<div id="' + alertId + '" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 99999; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 13px; font-weight: bold; min-width: 300px; text-align: center;">' +
                        '<i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Debe registrar el "MEDIO DE VERIFICACIÓN".' +
                        '<br><button type="button" onclick="jQuery(\'#' + alertId + '\').remove();" style="margin-top: 10px; background-color: #856404; color: #fff; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; font-size: 11px;">Entendido</button>' +
                    '</div>';
                    jQuery('body').append(alertHtml);
                }
                
                // Focusear automáticamente el campo vacío para ayudar al usuario
                jQuery('#mverif_' + prodId + '_' + mes).focus();
                return false;
            }
        }

        // 3. ⏳ ACTIVAR LOADING CON FONDO OPACO TOTAL (PANTALLA COMPLETA)
        var loadingId = 'loading_screen_overlay';
        var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 999999; display: flex; align-items: center; justify-content: center; flex-direction: column; font-family: sans-serif;">' +
            '<div style="background: #ffffff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
                '<i class="fa fa-refresh fa-spin" style="font-size: 32px; color: #1e3a8a; margin-bottom: 10px; display: block;"></i>' +
                '<span style="font-size: 14px; font-weight: bold; color: #334155;">Procesando y guardando seguimiento...</span>' +
            '</div>' +
        '</div>';
        jQuery('body').append(loadingHtml);
     // 4. ⏳ EFECTO LOADING LOCAL: Seleccionamos el contenedor y los componentes del mes
        var celdaTd = jQuery('#ejec_' + prodId + '_' + mes).closest('td');
        var wrapper = jQuery('.wrapper-mes-' + mes, celdaTd);
        wrapper.css('opacity', '0.5');

        // 5. Envío AJAX al servidor
        jQuery.ajax({
            type: "POST",
            url: base + "index.php/ejecucion/cevaluacion_form4/guardar_seguimiento",
            data: {
                prod_id: prodId,
                mes: mes,
                ejecutado: ejecVal,
                verificacion: mverifVal,
                problemas: probVal,
                acciones: accVal
            },
            dataType: 'json',
            success: function(response) {
                // 🔓 Quitar pantalla de Loading inmediatamente
                jQuery('#' + loadingId).remove();
                wrapper.css('opacity', '1');

                if (response.status === 'success') {
                    // 🎨 CAMBIO DE COLOR EN CALIENTE:
                    if (parseFloat(ejecVal) !== 0) {
                        celdaTd.css('background-color', '#bbf7d0'); // Verde suave (Con ejecución)
                    } else {
                        celdaTd.css('background-color', '#fef08a'); // Amarillo suave (Cero con justificación)
                    }

                    // Configurar el botón de eliminación por si el registro es nuevo o cambió
                    var btnEliminar = jQuery('#btn_del_' + prodId + '_' + mes);
                    if (response.id_seguimiento) {
                        btnEliminar.attr('onclick', 'eliminarSeguimiento(' + prodId + ', ' + mes + ', ' + response.id_seguimiento + ')');
                        btnEliminar.fadeIn();
                    }
                    
                    // 🔔 MENSAJE FLOTANTE DE GUARDADO EXITOSO AUTOMÁTICO (Estilo Toast sin librerías)
                    var toastId = 'toast_success_' + prodId + '_' + mes;
                    var toastHtml = '<div id="' + toastId + '" style="position: fixed; top: 20px; right: 20px; z-index: 999999; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 14px; font-weight: bold;">' +
                        '<i class="fa fa-check-circle" style="margin-right: 8px; color: #28a745;"></i> ¡Seguimiento guardado correctamente!' +
                    '</div>';
                    jQuery('body').append(toastHtml);
                    
                    // Se desvanece y se elimina solo tras 2 segundos
                    setTimeout(function(){
                        jQuery('#' + toastId).fadeOut(400, function(){ jQuery(this).remove(); });
                    }, 2000);
                } else {
                    alert('No se pudo guardar: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                // 🔓 Quitar pantalla de Loading ante errores de red
                jQuery('#' + loadingId).remove();
                wrapper.css('opacity', '1');
                
                alert('Ocurrió un error de comunicación con el servidor. Intente nuevamente.');
                console.error(error);
            }
        });
    }



    ///// Eliminar Registro de Seguimiento
    function eliminarSeguimiento(prodId, mes, idSeguimiento) {
    // 1. Validar que exista un ID de seguimiento válido para borrar
    if (!idSeguimiento || idSeguimiento === 0) {
        alert('No se puede eliminar un registro que no ha sido guardado previamente.');
        return false;
    }

    // 2. Alerta de confirmación flotante estilizada (CSS Puro)
    var confirmId = 'custom_confirm_' + prodId + '_' + mes;
    if (!document.getElementById(confirmId)) {
        var confirmHtml = '<div id="' + confirmId + '" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999999; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">' +
            '<div style="background: #ffffff; padding: 25px; border-radius: 6px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); text-align: center; max-width: 380px; width: 90%;">' +
                '<i class="fa fa-trash-o" style="font-size: 36px; color: #dc3545; margin-bottom: 12px; display: block;"></i>' +
                '<h4 style="margin: 0 0 10px 0; font-size: 16px; color: #1e293b; font-weight: bold;">¿Eliminar Seguimiento?</h4>' +
                '<p style="margin: 0 0 20px 0; font-size: 13px; color: #64748b; line-height: 1.5;">Esta acción borrará la ejecución, los medios de verificación, problemas y acciones de este mes por completo.</p>' +
                '<button type="button" id="btn_conf_si_' + prodId + '" style="background-color: #dc3545; color: #fff; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold; margin-right: 10px;">Sí, eliminar</button>' +
                '<button type="button" onclick="jQuery(\'#' + confirmId + '\').remove();" style="background-color: #64748b; color: #fff; border: none; padding: 6px 15px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: bold;">Cancelar</button>' +
            '</div>' +
        '</div>';
        jQuery('body').append(confirmHtml);
    }

    // 3. Asignar el evento del botón "Sí, eliminar" para arrancar el AJAX
    jQuery('#btn_conf_si_' + prodId).on('click', function() {
        // Removemos la ventana de confirmación
        jQuery('#' + confirmId).remove();

        // 4. ⏳ ACTIVAR LOADING CON FONDO OPACO TOTAL (PANTALLA COMPLETA)
        var loadingId = 'loading_screen_overlay_delete';
        var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 999999; display: flex; align-items: center; justify-content: center; flex-direction: column; font-family: sans-serif;">' +
            '<div style="background: #ffffff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
                '<i class="fa fa-refresh fa-spin" style="font-size: 32px; color: #dc3545; margin-bottom: 10px; display: block;"></i>' +
                '<span style="font-size: 14px; font-weight: bold; color: #334155;">Eliminando registro de seguimiento...</span>' +
            '</div>' +
        '</div>';
        jQuery('body').append(loadingHtml);

        var celdaTd = jQuery('#ejec_' + prodId + '_' + mes).closest('td');

        // 5. Envío AJAX al Servidor
        jQuery.ajax({
            type: "POST",
            url: base + "index.php/ejecucion/cevaluacion_form4/eliminar_seguimiento", // Ruta esperada en tu controlador
            data: {
                id_seguimiento: idSeguimiento,
                prod_id: prodId,
                mes: mes
            },
            dataType: 'json',
            success: function(response) {
                // 🔓 Quitar pantalla de Loading inmediatamente
                jQuery('#' + loadingId).remove();

                if (response.status === 'success') {
                    // 🌟 LIMPIEZA DE CAMPOS EN CALIENTE
                    jQuery('#ejec_' + prodId + '_' + mes).val(0);
                    jQuery('#mverif_' + prodId + '_' + mes).val('');
                     jQuery('#prob_' + prodId + '_' + mes).val('');
                    jQuery('#acc_' + prodId + '_' + mes).val('');

                    // 🎨 RESTABLECER COLOR A AMARILLO (Vuelve a estar pendiente de registro)
                    celdaTd.css('background-color', '#fef08a');

                    // Ocultar el botón de eliminar por completo
                    var btnEliminar = jQuery('#btn_del_' + prodId + '_' + mes);
                    btnEliminar.fadeOut().attr('onclick', '');

                    // 🔔 TOAST FLOTANTE DE NOTIFICACIÓN DE ELIMINACIÓN (Rojo Suave)
                    var toastId = 'toast_delete_' + prodId + '_' + mes;
                    var toastHtml = '<div id="' + toastId + '" style="position: fixed; top: 20px; right: 20px; z-index: 999999; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 14px; font-weight: bold;">' +
                        '<i class="fa fa-trash" style="margin-right: 8px; color: #dc3545;"></i> Registro eliminado correctamente.' +
                    '</div>';
                    jQuery('body').append(toastHtml);
                    
                    setTimeout(function(){
                        jQuery('#' + toastId).fadeOut(400, function(){ jQuery(this).remove(); });
                    }, 2000);

                } else {
                    alert('No se pudo eliminar el registro: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                jQuery('#' + loadingId).remove();
                alert('Ocurrió un error al intentar comunicarse con el servidor.');
                console.error(error);
            }
        });
    });
}

    //// get seguimiento x actividad en modal
    function abrirModalDetalleConAjax(prodId) {
        // A. Mostrar pantalla opaca completa de Loading
        var loadingId = 'loading_modal_ajax';
        var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 9999999; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">' +
            '<div style="background: #ffffff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
                '<i class="fa fa-refresh fa-spin" style="font-size: 32px; color: #0284c7; margin-bottom: 10px; display: block;"></i>' +
                '<span style="font-size: 13px; font-weight: bold; color: #334155;">Consultando base de datos...</span>' +
            '</div>' +
        '</div>';
        jQuery('body').append(loadingHtml);

        // B. Petición asíncrona al Servidor
        jQuery.ajax({
            type: "POST",
            url: base + "index.php/ejecucion/cevaluacion_form4/obtener_detalle_seguimiento_x_actividad", 
            data: {
                prod_id: prodId
            },
            dataType: 'json',
            success: function(response) {
                // Quitar pantalla de carga
                jQuery('#' + loadingId).remove();

                if (response.status === 'success') {
                    var act = response.actividad;
                   
                    jQuery('#detalle').html(act);
                    jQuery('#modalDetalleActividadAjax').modal('show');

                } else {
                    alert('Error al consultar los detalles: ' + response.message);
                }
            },
            error: function() {
                jQuery('#' + loadingId).remove();
                alert('Error crítico de red: No se pudo conectar con el servidor.');
            }
        });
    }

    //// get seguimiento de Actividades x Unidad Responsable en modal
    function abrirModalDetalle_UresponsableConAjax(comId) {
        // A. Mostrar pantalla opaca completa de Loading
        var loadingId = 'loading_modal_ajax';
        var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 9999999; display: flex; align-items: center; justify-content: center; font-family: sans-serif;">' +
            '<div style="background: #ffffff; padding: 20px 40px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
                '<i class="fa fa-refresh fa-spin" style="font-size: 32px; color: #0284c7; margin-bottom: 10px; display: block;"></i>' +
                '<span style="font-size: 13px; font-weight: bold; color: #334155;">Consultando base de datos...</span>' +
            '</div>' +
        '</div>';
        jQuery('body').append(loadingHtml);

        // B. Petición asíncrona al Servidor
        jQuery.ajax({
            type: "POST",
            url: base + "index.php/ejecucion/cevaluacion_form4/obtener_detalle_seguimiento_de_actividad_x_UnidadResponsable", 
            data: {
                com_id: comId
            },
            dataType: 'json',
            success: function(response) {
                // Quitar pantalla de carga
                jQuery('#' + loadingId).remove();

                if (response.status === 'success') {
                    var act = response.actividad;
                   
                    jQuery('#detalle').html(act);
                    jQuery('#modalDetalleActividadAjax').modal('show');

                } else {
                    alert('Error al consultar los detalles: ' + response.message);
                }
            },
            error: function() {
                jQuery('#' + loadingId).remove();
                alert('Error crítico de red: No se pudo conectar con el servidor.');
            }
        });
    }




///// Cuadros de Evaluacion POA
var chartPastel = null;
var chartBarras = null;

function cargarCuadrosEvaluacion(elemento, comId) {
    // 1. ⏳ ACTIVAR LOADING CON PANTALLA COMPLETA OPACA
    var loadingId = 'loading_screen_overlay_graficos';
    var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 9999999; display: flex; align-items: center; justify-content: center; flex-direction: column; font-family: sans-serif;">' +
        '<div style="background: #ffffff; padding: 25px 45px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
            '<i class="fa fa-refresh fa-spin" style="font-size: 34px; color: #1e3a8a; margin-bottom: 12px; display: block;"></i>' +
            '<span style="font-size: 14px; font-weight: bold; color: #334155;">Procesando consolidados y generando gráficos...</span>' +
        '</div>' +
    '</div>';
    jQuery('body').append(loadingHtml);

    // 2. Ejecutar petición AJAX al servidor
    jQuery.ajax({
        type: "POST",
        url: base + "index.php/ejecucion/cevaluacion_form4/obtener_graficos_cumplimiento", // Define esta ruta en tu controlador
        data: { com_id: comId },
        dataType: 'json',
        success: function(response) {
    jQuery("#" + loadingId).remove();

    if (response.status === "success") {
        // 1. Inyectar primero los datos de texto en la tabla
        jQuery("#lbl_total_prog").text(response.datos.total_programado);
        jQuery("#lbl_total_ejec").text(response.datos.total_ejecutado);
        jQuery("#lbl_total_porcentaje").text(response.datos.porcentaje_eficacia + "%");
        
        if(response.datos.porcentaje_eficacia >= 75) {
            jQuery("#lbl_total_porcentaje").css("color", "#16a34a");
        } else if(response.datos.porcentaje_eficacia >= 50) {
            jQuery("#lbl_total_porcentaje").css("color", "#ca8a04");
        } else {
            jQuery("#lbl_total_porcentaje").css("color", "#dc2626");
        }

        // 2. 🌟 PRIMERO MOSTRAR EL MODAL
        jQuery("#modal_graficos").modal("show");

        // 3. ⏳ ESPERAR A QUE EL MODAL TERMINE DE CARGAR EN PANTALLA
        // Usamos el evento nativo de Bootstrap 'shown.bs.modal' para garantizar que los canvas existan en el DOM
        jQuery('#modal_graficos').off('shown.bs.modal').on('shown.bs.modal', function () {
            
            // Destruir instancias previas si existen
            if (chartPastel) chartPastel.destroy();
            if (chartBarras) chartBarras.destroy();
  // Ahora sí, capturar los contextos con la seguridad de que no serán null
            var canvasPastel = document.getElementById("grafico_pastel_cumplimiento");
            var canvasBarras = document.getElementById("grafico_barras_temporalidad");

            if (canvasPastel && canvasBarras) {
                var ctxPastel = canvasPastel.getContext("2d");
                chartPastel = new Chart(ctxPastel, {
                    type: "doughnut",
                    data: {
                        labels: ["Cumplido (%)", "Pendiente (%)"],
                        datasets: [{
                            data: [response.datos.porcentaje_eficacia, (100 - response.datos.porcentaje_eficacia)],
                            backgroundColor: ["#22c55e", "#cbd5e1"],
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                var ctxBarras = canvasBarras.getContext("2d");
                chartBarras = new Chart(ctxBarras, {
                    type: "bar",
                    data: {
                        labels: ["Prog. Total", "Ejec. Total"],
                        datasets: [{
                            label: "Unidades POA",
                            data: [response.datos.total_programado, response.datos.total_ejecutado],
                            backgroundColor: ["#0284c7", "#16a34a"]
                        }]
                    },
           options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: { yAxes: [{ ticks: { beginAtZero: true } }] }
                    }
                });
            } else {
                console.error("Error: No se encontraron los elementos canvas en el DOM del modal.");
            }
        });

    } else {
        alert("No se pudieron consolidar los cuadros: " + response.message);
    }
},



        error: function() {
            jQuery('#' + loadingId).remove();
            alert('Error de comunicación asíncrona con el servidor.');
        }
    });
}