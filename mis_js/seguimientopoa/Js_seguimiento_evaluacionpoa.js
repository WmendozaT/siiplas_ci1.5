base   = $('[name="base"]').val();

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

    // 2. 🚨 VALIDACIÓN ESTRICTA: Si la ejecución es 0, Problemas y Acciones son obligatorios
    if (ejecVal.trim() === '' || isNaN(ejecVal) || parseFloat(ejecVal) === 0) {
        if (probVal === '' || accVal === '') {
            
            // Creamos un contenedor de alerta moderno directamente en el HTML
            var alertId = 'custom_alert_' + prodId + '_' + mes;
            // Si ya existe una alerta abierta, no duplicarla
            if (!document.getElementById(alertId)) {
                var alertHtml = '<div id="' + alertId + '" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 13px; font-weight: bold; min-width: 300px; text-align: center;">' +
                    '<i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> Si la ejecución mensual es cero (0), debe registrar "PROBLEMAS PRESENTADOS" y "ACCIONES REALIZADAS".' +
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
    else{
        if (mverifVal === '') {
            var alertId = 'custom_alert_' + prodId + '_' + mes;
            // Si ya existe una alerta abierta, no duplicarla
            if (!document.getElementById(alertId)) {
                var alertHtml = '<div id="' + alertId + '" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 15px 25px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif; font-size: 13px; font-weight: bold; min-width: 300px; text-align: center;">' +
                    '<i class="fa fa-exclamation-triangle" style="margin-right: 8px;"></i> debe registrar el "MEDIO DE VERIFICACIÓN".' +
                    '<br><button type="button" onclick="jQuery(\'#' + alertId + '\').remove();" style="margin-top: 10px; background-color: #856404; color: #fff; border: none; padding: 4px 10px; border-radius: 3px; cursor: pointer; font-size: 11px;">Entendido</button>' +
                '</div>';
                jQuery('body').append(alertHtml);
            }
            
            // Focusear automáticamente el campo vacío para ayudar al usuario
            if (mverifVal === '') {
                jQuery('#mverif_' + prodId + '_' + mes).focus();
            }
            return false;
        }
    }

    // 3. ⏳ EFECTO LOADING: Seleccionamos el contenedor y los componentes del mes
    var celdaTd = jQuery('#ejec_' + prodId + '_' + mes).closest('td');
    var wrapper = jQuery('.wrapper-mes-' + mes, celdaTd);
    var botonGuardar = jQuery('button[onclick*="guardarSeguimiento(' + prodId + '"]', celdaTd);

    // Guardamos el HTML original del botón para restaurarlo después
    var htmlOriginalBoton = botonGuardar.html();

    // Bloqueamos los elementos visualmente poniendo opacidad y un spinner
    wrapper.css('opacity', '0.5');
    botonGuardar.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> Guardando...');

    // 4. Envío AJAX al servidor
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
            // Restaurar estados normales al finalizar la petición
            wrapper.css('opacity', '1');
            botonGuardar.prop('disabled', false).html(htmlOriginalBoton);

            if (response.status === 'success') {
                alert(response.status)
                // 🎨 CAMBIO DE COLOR EN CALIENTE:
                if (parseFloat(ejecVal) !== 0) {
                    celdaTd.css('background-color', '#bbf7d0'); // Verde suave (Con ejecución)
                } else {
                    celdaTd.css('background-color', '#fef08a'); // Amarillo suave (Cero con justificación)
                }

                // Configurar el botón de eliminación por si el registro es nuevo
                var btnEliminar = jQuery('#btn_del_' + prodId + '_' + mes);
                if (response.id_seguimiento) {
                    btnEliminar.attr('onclick', 'eliminarSeguimiento(' + prodId + ', ' + mes + ', ' + response.id_seguimiento + ')');
                    btnEliminar.fadeIn();
                }
                
                console.log('Mes ' + mes + ' guardado exitosamente.');
            } else {
                alert('No se pudo guardar: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            // Restaurar estados normales ante un error de red
            wrapper.css('opacity', '1');
            botonGuardar.prop('disabled', false).html(htmlOriginalBoton);
            
            alert('Ocurrió un error de comunicación con el servidor. Intente nuevamente.');
            console.error(error);
        }
    });
}