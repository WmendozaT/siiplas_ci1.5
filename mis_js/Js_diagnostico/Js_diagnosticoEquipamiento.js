base = $('[name="base"]').val();

    
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


     
function abreVentana_poa(url) {
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


/// Requerimientos
    $(document).on('click', '#btn_subir_f5', function(e) {
        e.preventDefault();
        $('#mensaje_f5').html(''); 

        // Validación preventiva en el cliente antes de consumir canal de red
        if ($('#archivo_f5').val() == '') {
            $('#mensaje_f5').html('<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>');
            if (typeof alertify !== "undefined") {
                alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .XLSX");
            }
            return false;
        }

        var form = $('#form_subir_equipamiento')[0];
        var data_multipart = new FormData(form);
        var $btn = $(this);

        // Bloquear interfaz de usuario (UI) e inyectar cargador animado institucional (Loader)
        $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA EN POSTGRES...');
        $('#loads_f5').show();

        // Captura perimetral automática del Token CSRF por si está activo en la CNS
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        if (csrf_name !== '') {
            data_multipart.append(csrf_name, csrf_hash);
        }

        $.ajax({
            type: "POST",
            url: $('#form_subir_equipamiento').attr('action'),
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
                    $('#loads_f5').hide();
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

                    $('#mensaje_f5').html(html_success);
                    $('#loads_f5').hide();
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
                    
                    $('#mensaje_f5').html('<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">' + errorMsg + '</div>');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                    $('#loads_f5').hide();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error("Falla Crítica en canal de carga masiva de Excel. Detalle:", xhr.responseText);
                $('#loads_f5').hide();
                $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR MIGRACIÓN');
                
                var txt_err = "❌ Error crítico de red (" + xhr.status + "): Imposible comunicar con el cargador de productos.";
                $('#mensaje_f5').html('<div class="alert alert-danger" style="margin-bottom:0;">' + txt_err + '</div>');
                
                if (typeof alertify !== "undefined") {
                    alertify.error("Falla de red en Apache.");
                }
            }
        });
    });