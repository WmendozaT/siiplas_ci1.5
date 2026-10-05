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


/// boton para exportar el grafico en PDF
function exportarPDF() {
    // 1. Obtener el área de gráficos original
    var elementoOriginal = document.querySelector('.print-area-graficos');
    var semaforoOriginal = document.getElementById('calificacion');
    
    var semaforoHtmlInyectar = '';
    if (semaforoOriginal && semaforoOriginal.innerHTML.trim() !== '') {
        // Envolvemos el semáforo en un contenedor plano ideal para PDF (sin sombras pesadas ni paddings web)
        semaforoHtmlInyectar = '<div style="margin-bottom: 25px; width: 100%;">' + semaforoOriginal.innerHTML + '</div>';
    }

    // 2. Crear un contenedor raíz completamente NUEVO exclusivo para el PDF
    var contenedorPdf = document.createElement('div');
    contenedorPdf.style.fontFamily = 'Arial, sans-serif';
    contenedorPdf.style.padding = '10px';
    contenedorPdf.style.color = '#334155';

    // ==========================================
    // 🏛️ PASO A: DISEÑO DE LA CABECERA / MEMBRETE
    // ==========================================
    var cabeceraHtml = 
    '<table style="width: 100%; margin-bottom: 15px; border-collapse: collapse; table-layout: fixed;">' +
        '<tr>' +
            // Logo Izquierdo
            '<td style="width: 20%; vertical-align: middle; text-align: center;">' +
                '<img src="' + base + 'assets/ifinal/caja.png" style="height: 60px; width: auto; object-fit: contain;">' +
            '</td>' +
            // Títulos Centrales
            '<td style="width: 60%; text-align: center; vertical-align: middle;">' +
                '<h2 style="font-size: 15px; margin: 0 0 6px 0; color: #1e3a8a; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; word-spacing: 2px;">CUADRO DE EVALUACIÓN POA</h2>' +
                '<h3 style="font-size: 9.5px; margin: 0 0 6px 0; color: #0284c7; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; word-spacing: 1px;">' + nombreUnidadGlobal + '</h3>' +
                '<p style="font-size: 8.5px; margin: 0 0 6px 0; color: #64748b; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px; word-spacing: 1px;">' + trimestre + '</p>' +
            '</td>' +
            // Metadatos Derecha
            '<td style="width: 20%; text-align: right; vertical-align: middle; font-size: 8px; color: #64748b; line-height: 1.4;">' +
                '<span style="white-space: nowrap;"><strong>Fecha : </strong> ' + new Date().toLocaleDateString('es-ES') + '</span><br>' +
                '<span style="white-space: nowrap;"><strong>Hora : </strong> ' + new Date().toLocaleTimeString('es-ES', {hour: '2-digit', minute:'2-digit'}) + '</span><br>' +
                '<span style="white-space: nowrap;"><strong>Estado : </strong> Finalizado</span>' +
            '</td>' +
        '</tr>' +
    '</table>' +
    '<div style="width: 100%; height: 2px; background: linear-gradient(to right, #1e3a8a, #0284c7); margin-bottom: 20px; border-radius: 2px;"></div>' +
    // 🌟 INYECCIÓN ASÍNCRONA DEL SEMÁFORO DE CALIFICACIÓN AQUÍ
    semaforoHtmlInyectar; 
    
    contenedorPdf.innerHTML = cabeceraHtml;

    // ==========================================
    // 📊 PASO B: CLONACIÓN Y REDISEÑO DE GRÁFICOS
    // ==========================================
    var clonGraficos = elementoOriginal.cloneNode(true);

    // Reemplazar canvas por imágenes base64
    var canvasOriginales = elementoOriginal.querySelectorAll('canvas');
    var imagenesClonadas = clonGraficos.querySelectorAll('canvas');

    canvasOriginales.forEach(function(canvas, indice) {
        if (canvas) {
            var img = document.createElement('img');
            img.src = canvas.toDataURL('image/png');
            img.style.width = '100%';
            img.style.height = 'auto';
            img.style.maxHeight = '150px'; // Altura controlada para evitar estiramientos
            img.style.objectFit = 'contain';

            var canvasAEmplazar = imagenesClonadas[indice];
            if (canvasAEmplazar && canvasAEmplazar.parentNode) {
                canvasAEmplazar.parentNode.replaceChild(img, canvasAEmplazar);
            }
        }
    });

    // ==========================================
    // 🛠️ PASO C: CORRECCIÓN DEL TEXTO ENCIMADO (Estructura de Tabla Fija)
    // ==========================================
    // Extraemos los bloques de los dos gráficos usando sus textos como referencia
    var h5s = clonGraficos.querySelectorAll('h5');
    var tituloPastel = h5s[0] ? h5s[0].innerHTML : '(%)_Cumplimiento_POA';
    var tituloBarras = h5s[1] ? h5s[1].innerHTML : 'Acumulado_Trimestral_(Tendencia)';

    var imgs = clonGraficos.querySelectorAll('img');
    var imgPastelHtml = imgs[0] ? imgs[0].outerHTML : '';
    var imgBarrasHtml = imgs[1] ? imgs[1].outerHTML : '';
   // Creamos una estructura de tabla limpia de 2 columnas fijas para que NADA se encime ni colapse
    var seccionGraficosEstructural = 
        '<table style="width: 100%; table-layout: fixed; margin-bottom: 35px; border-collapse: collapse;">' +
            '<tr>' +
                // Gráfico Izquierdo (Pastel)
                '<td style="width: 48%; vertical-align: top; text-align: center; padding-right: 12px;">' +
                    '<div style="vertical-align: middle;font-size: 9px; font-weight: bold; color: #334155; margin: 0 0 8px 0; line-height: 1.2; min-height: 10px;"><b>' + tituloPastel + '</b></div>' +
                    '<div style="width: 100%; display: block; text-align: center;">' + imgPastelHtml + '</div>' +
                '</td>' +
                // Columna invisible de separación
                '<td style="width: 4%;"></td>' +
                // Gráfico Derecho (Barras)
                '<td style="width: 48%; vertical-align: top; text-align: center; padding-left: 12px;">' +
                    '<div style="vertical-align: middle;font-size: 9px; font-weight: bold; color: #334155; margin: 0 0 8px 0; line-height: 1.2; min-height: 10px;"><b>' + tituloBarras + '</b></div>' +
                    '<div style="width: 100%; display: block; text-align: center;">' + imgBarrasHtml + '</div>' +
                '</td>' +
            '</tr>' +
        '</table>';

    // Inyectar los gráficos ordenados al contenedor principal
    contenedorPdf.innerHTML += seccionGraficosEstructural;

    // ==========================================
    // 📋 PASO D: AGREGAR LA TABLA DE DATOS AL FINAL
    // ==========================================
    var tablaOriginal = clonGraficos.querySelector('table');
    if (tablaOriginal) {
        // Estilizar los textos interiores de las celdas para corregir superposiciones de letras extrañas
        var todasLasCeldas = tablaOriginal.querySelectorAll('th, td');
        todasLasCeldas.forEach(function(celda) {
            celda.style.padding = '8px 9px';
            celda.style.fontSize = '9px';
            celda.style.letterSpacing = '0.3px';
            celda.style.lineHeight = '1.3';
            celda.style.wordSpacing = 'normal';
        });
   // Aplicar estilos estructurales limpios a la tabla
        tablaOriginal.style.width = '100%';
        tablaOriginal.style.marginTop = '12px';
        tablaOriginal.style.borderCollapse = 'collapse';
        
        // Adjuntar el código HTML limpio de la tabla al contenedor raíz del PDF
        contenedorPdf.innerHTML += '<div style="width:100%;">' + tablaOriginal.outerHTML + '</div>';
    }

    // ==========================================
    // ⚙️ PASO E: CONFIGURACIÓN GENERAL DE HTML2PDF
    // ==========================================
    var opciones = {
        margin:       15, // 15mm de margen garantizan que nada se corte en los bordes de la hoja
        filename:     'Reporte_POA_' + new Date().toISOString().slice(0,10) + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { 
            scale: 3,           // Subimos a escala 3 para máxima nitidez de letras y evitar bugs visuales
            useCORS: true,      
            logging: false 
        },
        jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' } 
    };

    // 5. Compilar y procesar la descarga usando el contenedor limpio de impresión
    html2pdf().set(opciones).from(contenedorPdf).save();
}


//////////////////////////////


///// Cuadros de Evaluacion POA
var chartPastel = null;
var chartBarras = null;
var nombreUnidadGlobal = ""; 
var trimestre = "";
var calificacion = ""; 

function cargarCuadrosEvaluacion(elemento, comId) {
    // 1. ⏳ Activar pantalla de carga opaca
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
        url: base + "index.php/ejecucion/cevaluacion_form4/obtener_graficos_cumplimiento", 
        data: { com_id: comId },
        dataType: 'json',
        success: function(response) {
            jQuery("#" + loadingId).remove();

            if (response.status === "success") {
                // Captura de metadatos globales
                nombreUnidadGlobal = response.datos.UnidadResponsable || "UNIDAD RESPONSABLE";
                trimestre = response.datos.trimestre || "TRIMESTRE";

                // Actualizar dinámicamente el título del modal
                jQuery("#modal_graficos .modal-title").html(
                    '<i class="fa fa-bar-chart-o"></i> Cuadros y Gráficos de Evaluación POA - <small style="color:#cbd5e1; font-weight:bold;">' + response.datos.UnidadResponsable + '</small>' +
                    '<br><span style="font-size: 11px; font-weight: normal; color:#94a3b8; display:block; margin-top:2px;">Periodo: ' + response.datos.trimestre + '</span>'
                );

                // Aplicar semáforo de colores al porcentaje de cumplimiento global
                if(response.datos.porcentaje_cumplimiento >= 75) {
                    jQuery("#porcentaje_cumplimiento").css("color", "#16a34a");
                } else if(response.datos.porcentaje_cumplimiento >= 50) {
                    jQuery("#porcentaje_cumplimiento").css("color", "#ca8a04");
                } else {
                    jQuery("#porcentaje_cumplimiento").css("color", "#dc2626");
                }

                // Desplegar el modal estático
                jQuery("#modal_graficos").modal("show");

                // 3. ⏳ Dibujar los gráficos en el Canvas una vez que el modal termine de abrirse
                jQuery('#modal_graficos').off('shown.bs.modal').on('shown.bs.modal', function () {
                //    alert(response.datos.tabla_detalle)
                jQuery("#calificacion").html(response.datos.calificacion);
                jQuery("#btn_reporte").html(response.datos.reporte);
                jQuery("#detalles").html(response.datos.tabla_detalle);

                

                    if (chartPastel) chartPastel.destroy();
                    if (chartBarras) chartBarras.destroy();

                    var canvasPastel = document.getElementById("grafico_pastel_cumplimiento");
                    var canvasBarras = document.getElementById("grafico_barras_temporalidad");

                    if (canvasPastel && canvasBarras) {
                        // =========================================================
                        // 🍩 1. GRÁFICO PASTEL/DONA: ESTILO MODERNO NATIVO (SIN PLUGINS EXTRA)
                        // =========================================================
                        var ctxPastel = canvasPastel.getContext("2d");
                        chartPastel = new Chart(ctxPastel, {
                            type: "doughnut",
                            data: {
                                labels: ["CUMPLIDO", "NO CUMPLIDO", "EN PROCESO"],
                                datasets: [{
                                    data: [
                                        response.datos.act_cumplidas, 
                                        response.datos.act_no_cumplidas, 
                                        response.datos.act_en_proceso
                                    ],
                                    // Paleta de colores moderna con alto contraste
                                    backgroundColor: ["#10b981", "#ef4444", "#f59e0b"], // Verde esmeralda, Rojo y Ámbar
                                    hoverBackgroundColor: ["#059669", "#dc2626", "#d97706"],
                                    borderWidth: 3,            // Separación elegante entre bloques
                                    borderColor: "#ffffff",    // Línea divisoria blanca y limpia
                                    borderRadius: 6            // 🔥 Esquinas redondeadas estilo Dashboard moderno
                                }]
                            },
                            // ❌ ELIMINADO EL ARREGLO DE PLUGINS EXTERNOS PARA EVITAR EL ERROR DE REFERENCIA
                            options: { 
                                responsive: true, 
                                maintainAspectRatio: false,
                                cutout: '70%', // Anillo más fino y estilizado
                                plugins: {
                                    title: { display: false },
                                    // 📋 CONFIGURACIÓN DE LEYENDA DERECHA INTELIGENTE NATIVA
                                    legend: { 
                                        position: 'right', 
                                        labels: {
                                            boxWidth: 10,
                                            boxHeight: 10,
                                            usePointStyle: true, // Viñetas circulares en lugar de cuadrados toscos
                                            pointStyle: 'circle',
                                            font: { size: 10, weight: '600', family: 'sans-serif' },
                                            padding: 12,
                                            // 🔥 TRUCO NATIVO AUTOMÁTICO: Calcula e inyecta el % y las act. en el menú derecho
                                            generateLabels: function(chart) {
                                                var data = chart.data;
                                                if (data.labels.length && data.datasets.length) {
                                                    var dataset = data.datasets[0];
                                                    // Sumar el volumen total físico de las actividades actuales
                                                    var total = dataset.data.reduce(function(a, b) { return a + b; }, 0);

                                                    return data.labels.map(function(label, i) {
                                                        var valor = dataset.data[i];
                                                        // Regla de tres simple para el porcentaje individual
                                                        var porcentaje = total > 0 ? ((valor * 100) / total).toFixed(1) : "0.0";
                                                        
                                                        // Retorna la cadena armada limpia: CUMPLIDO (75.5% - 12 act.)
                                                        return {
                                                            text: label + " (" + porcentaje + "% - " + valor + " act.)",
                                                            fillStyle: dataset.backgroundColor[i],
                                                            strokeStyle: dataset.borderColor,
                                                            lineWidth: dataset.borderWidth,
                                                            hidden: isNaN(dataset.data[i]) || chart.getDatasetMeta(0).data[i].hidden,
                                                            index: i
                                                        };
                                                    });
                                                }
                                                return [];
                                            }
                                        }
                                    },
                                    // 🔥 CONFIGURACIÓN DE LOS TOOLTIPS (Al pasar el cursor)
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                var valor = context.raw;
                                                var total = context.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                                                var porcentaje = total > 0 ? ((valor * 100) / total).toFixed(1) : 0;
                                                return " " + context.label + ": " + porcentaje + "% (" + valor + " act.)";
                                            }
                                        }
                                    }
                                }
                            }
                        });


                        // ==========================================
                        // 📈 2. GRÁFICO DE LÍNEAS (EVOLUCIÓN TRIMESTRAL)
                        // ==========================================
                        var ctxBarras = canvasBarras.getContext("2d");
                        chartBarras = new Chart(ctxBarras, {
                            type: "line", 
                            data: {
                                labels: response.datos.regresion_labels, 
                                datasets: [
                                    {
                                        label: 'PROG. ACUMULADO',
                                        data: response.datos.regresion_prog, 
                                        borderColor: '#0284c7', // Azul institucional
                                        backgroundColor: 'transparent',

                                        borderWidth: 2.5, // 🌟 HACE LA LÍNEA PUNTEADA/SEGMENTADA DE TU IMAGEN
                                        pointBackgroundColor: '#ffffff',
                                        pointBorderColor: '#0284c7',
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        fill: false,
                                        lineTension: 0 // 🌟 Fuerza líneas totalmente rectas (Sin curvas suavizadas)
                                    },
                                    {
                                        label: 'CUMPLIDO ACUMULADO',
                                        data: response.datos.regresion_ejec, 
                                        borderColor: '#10b981', // Verde esmeralda continuo
                                        backgroundColor: 'transparent',
                                        borderWidth: 3, 
                                        pointBackgroundColor: '#10b981',
                                        pointBorderColor: '#10b981',
                                        pointRadius: 5,
                                        pointHoverRadius: 7,
                                        fill: false,
                                        lineTension: 0
                                    }
                                ]
                            },
                             options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: { 
                                    y: { 
                                        beginAtZero: true, 
                                        grid: { color: "#f1f5f9" },
                                        // Añadir un margen superior interno en el eje Y para que los números del último punto no se corten arriba
                                        grace: '12%' 
                                    },
                                    x: { 
                                        grid: { display: false } 
                                    }
                                },
                                plugins: {
                                    title: { display: false },
                                    legend: { 
                                        position: 'top', 
                                        labels: { boxWidth: 25, font: { size: 9 } } 
                                    },
                                    // Desactivar el datalabel global predeterminado para configurarlo de forma única por dataset arriba
                                    datalabels: {
                                        display: true
                                    }
                                }
                            }
                        });
                    } else {
                        console.error("Error: No se encontraron los elementos canvas en el DOM.");
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




    ////// para listar las unidades responsable para evaluar
    // 🌟 CORRECCIÓN CRÍTICA: La variable de control debe nacer afuera del bloque on("click")
    var xhrRequest = null;

    $(function () {
        $(".enlace").on("click", function (e) {
            e.preventDefault(); // Frenar comportamiento nativo de inmediato

            var proy_id = $(this).attr('name');
            var establecimiento = $(this).attr('id');
            
            // Reemplazar <font> antiguo por CSS en línea estable
            $('#titulo').html('<span style="font-size: 15px; font-weight: bold; color: #1e3a8a;">' + establecimiento + '</span>');
            
            // Loader visual estilizado
            $('#content1').html(
                '<div class="loading" align="center" style="padding: 20px;">' +
                    '<img src="' + base + '/assets/img_v1.1/preloader.gif" alt="loading" style="margin-bottom: 10px;" /><br/>' +
                    '<span style="font-weight: bold; color: #475569; font-size: 13px;">Un momento por favor, Cargando Unidades Responsables...</span>' +
                '</div>'
            );
            
            var url = base + "index.php/ejecucion/cevaluacion_form4/get_evaluar_unidadresponsable";

            // 🌟 ABORTAR CONTROLADO: Si el usuario cliquea otra fila rápido, cancelamos la anterior
            if (xhrRequest && xhrRequest.readyState !== 4) {
                xhrRequest.abort();
            }

            // Petición AJAX pasando el parámetro de forma segura como Objeto JSON
            xhrRequest = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: { proy_id: proy_id }
            });

            xhrRequest.done(function (response) {
                if (response.respuesta === 'correcto') {
                    // Inyectamos y ejecutamos un fadeIn fluido
                    $('#content1').hide().html(response.tabla).fadeIn(400);
                    $('#evaluacion').hide().html(response.evaluacion).fadeIn(400);
                } else {
                    alertify.error("ERROR AL RECUPERAR INFORMACIÓN");
                }
            });

            xhrRequest.fail(function (jqXHR, textStatus, thrown) {
                // Si el fallo fue gatillado por el abort de control interno, ignoramos la alerta de error
                if (textStatus === 'abort') { return; }
                alertify.error("Error de red: No se pudo conectar con el servidor.");
                console.error("AJAX Error: " + textStatus);
            });
        });
    });

    function cargarFormularioEvaluacion(event, urlDestino, nombreUnidad) {
        // 1. Prevenir la acción predeterminada del click
        if (event) {
            event.preventDefault();
        }

        // 2. ⏳ ACTIVAR LOADING CON FONDO OPACO TOTAL (Mismo diseño de tus opciones anteriores)
        var loadingId = 'loading_screen_overlay_formulario';
        
        // Si por algún motivo ya existe un loading abierto, no duplicarlo
        if (!document.getElementById(loadingId)) {
            var loadingHtml = '<div id="' + loadingId + '" style="position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0, 0, 0, 0.4); z-index: 99999999; display: flex; align-items: center; justify-content: center; flex-direction: column; font-family: sans-serif;">' +
                '<div style="background: #ffffff; padding: 25px 45px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); text-align: center;">' +
                    '<i class="fa fa-refresh fa-spin" style="font-size: 34px; color: #16a34a; margin-bottom: 12px; display: block;"></i>' +
                    '<span style="font-size: 14px; font-weight: bold; color: #334155;">Abriendo matriz de seguimiento...</span><br>' +
                    '<small style="color: #64748b; font-size: 11px; display: block; margin-top: 5px;">Unidad: ' + nombreUnidad.toUpperCase() + '</small>' +
                '</div>' +
            '</div>';
            
            jQuery('body').append(loadingHtml);
        }

        // 3. 🚀 REDIRECCIÓN CONTROLADA: Enviamos al usuario a la nueva página pasados 100ms
        // Esto da tiempo suficiente a que el navegador renderice el preloader en la pantalla actual
        setTimeout(function() {
            window.location.href = urlDestino;
        }, 100);
    }

  // $(function () {
  //   $(".enlace").on("click", function (e) {
  //     proy_id = $(this).attr('name');
  //     establecimiento = $(this).attr('id');
      
  //     $('#titulo').html('<font size=3><b>'+establecimiento+'</b></font>');
  //     $('#content1').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Ediciones - <br>'+establecimiento+'</div>');
      
  //     var url = base+"index.php/ejecucion/cevaluacion_form4/get_evaluar_unidadresponsable";
  //     var request;
  //     if (request) {
  //         request.abort();
  //     }
  //     request = $.ajax({
  //         url: url,
  //         type: "POST",
  //         dataType: 'json',
  //         data: "proy_id="+proy_id
  //     });

  //     request.done(function (response, textStatus, jqXHR) {

  //     if (response.respuesta == 'correcto') {
  //         $('#content1').fadeIn(1000).html(response.tabla);
  //         $('#evaluacion').fadeIn(1000).html(response.evaluacion);
  //     }
  //     else{
  //         alertify.error("ERROR AL RECUPERAR INFORMACION");
  //     }

  //     });
  //     request.fail(function (jqXHR, textStatus, thrown) {
  //         console.log("ERROR: " + textStatus);
  //     });
  //     request.always(function () {
  //         //console.log("termino la ejecuicion de ajax");
  //     });
  //     e.preventDefault();
      
  //   });
  // });