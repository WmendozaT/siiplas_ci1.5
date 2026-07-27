var base = $('[name="base"]').val();
var prod_id = $('[name="prod_id"]').val();

var request_mod_f5 = null;
var xhr_guardar_f5 = null; 
/// Subir Requerimientos Global
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

        var form = $('#form_subir_requerimientos')[0];
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
            url: $('#form_subir_requerimientos').attr('action'),
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



///// Subir archivo de Migracion de Requerimientos por Actividad
 $(document).on('click', '#btn_subir_f5_act', function(e) {
        e.preventDefault();
        $('#mensaje_f5_act').html(''); 

        // Validación preventiva en el cliente antes de consumir canal de red
        if ($('#archivo_f5').val() == '') {
            $('#mensaje_f5_act').html('<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>');
            if (typeof alertify !== "undefined") {
                alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .XLSX");
            }
            return false;
        }

        var form = $('#form_subir_requerimientos_act')[0];
        var data_multipart = new FormData(form);
        var $btn = $(this);

        // Bloquear interfaz de usuario (UI) e inyectar cargador animado institucional (Loader)
        $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA EN POSTGRES...');
        $('#loads_f5_act').show();

        // Captura perimetral automática del Token CSRF por si está activo en la CNS
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        if (csrf_name !== '') {
            data_multipart.append(csrf_name, csrf_hash);
        }

        $.ajax({
            type: "POST",
            url: $('#form_subir_requerimientos_act').attr('action'),
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
                    $('#loads_f5_act').hide();
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

                    $('#mensaje_f5_act').html(html_success);
                    $('#loads_f5_act').hide();
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
                    
                    $('#mensaje_f5_act').html('<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">' + errorMsg + '</div>');
                    $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                    $('#loads_f5_act').hide();
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error("Falla Crítica en canal de carga masiva de Excel. Detalle:", xhr.responseText);
                $('#loads_f5_act').hide();
                $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR MIGRACIÓN');
                
                var txt_err = "❌ Error crítico de red (" + xhr.status + "): Imposible comunicar con el cargador de productos.";
                $('#mensaje_f5_act').html('<div class="alert alert-danger" style="margin-bottom:0;">' + txt_err + '</div>');
                
                if (typeof alertify !== "undefined") {
                    alertify.error("Falla de red en Apache.");
                }
            }
        });
    });
 //// -----------------------------------------------------------------
  
  //// ----------- cambiar alineacion de actividades por insumo
  $(document).ready(function() {
      // 1. Declarar request FUERA del evento para que guarde el estado entre cambios
      var request;
      
      // Escucha el cambio en cualquier select con la clase asignada
      $(document).on('change', '.select-actividad', function() {
          const select = $(this);
          const nuevoProdId = select.val(); // El nuevo valor seleccionado
          const insId = select.data('id');  // El ID de la fila/registro
          const url = base + "index.php/programacion/crequerimiento/cambia_actividad";

          // Localizamos la fila HTML y pintamos un tono naranja de procesamiento masivo
          var $fila_tr = select.closest('tr');
          $fila_tr.css({'background-color': '#fef3c7', 'opacity': '0.7'});

          // Deshabilitar temporalmente para evitar rebotaderos de doble clic
          select.prop('disabled', true);

          // 2. Ahora abortar peticiones previas sí funcionará correctamente
          if (request) {
              request.abort();
          }
          
          // 3. Configuración del AJAX
          request = $.ajax({
              url: url,
              type: "POST",
              dataType: 'json',
              data: {
                  ins_id: insId,        
                  nuevo_prod_id: nuevoProdId 
              }
          });

          // Manejo de la respuesta
           request.done(function (response, textStatus, jqXHR) {
              if (response.respuesta === 'correcto' || response.status === 'success') {
                  if (typeof alertify !== "undefined") {
                      alertify.success("✔ " + (response.message || "Alineación cambiada."));
                  }

                  // 🌟 CORRECCIÓN EXCLUSIVA: Desvanecimiento y recarga asíncrona por AJAX
                  $fila_tr.fadeOut(400, function() {
                      $(this).remove(); // Eliminamos físicamente la fila movida

                      // Recuperamos el ID de la actividad activa desde el input hidden general del DOM
                      var prod_id_global = $('#prod_id').val();
                      
                      // Invocamos la subfunción asíncrona que muestra el loading y refresca la tabla
                      if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                          recargar_listado_requerimientos_cns_ajax(prod_id_global);
                      } else {
                          // Resguardo técnico por si no encuentra la subfunción en el documento
                          window.location.reload(true);
                      }
                  });

              } else {
                  // Restauramos el color blanco original de la fila si el servidor detecta una falla
                  $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
                  select.prop('disabled', false);
                  alertify.error(response.message || "ERROR AL MODIFICAR REQUERIMIENTO");
              }
          });

          // Manejar si la petición falla a nivel de servidor (ej: error 500 o aborto)
          request.fail(function (jqXHR, textStatus, errorThrown) {
              if (textStatus !== 'abort') {
                  $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
                  select.prop('disabled', false);
                  alertify.error("Error de conexión con el servidor");
              }
          });
      });
  });
  /// ------------------------------------------------


 /// ----- Eliminar Requerimiento
    $(document).on('click', '.del_ff', function (e) {
      e.preventDefault();
      
      var $btn = $(this);
      var ins_id = $btn.attr('name') || $btn.data('id');
      var $fila_tr = $btn.closest('tr'); // Ubicamos la fila para feedback visual

      if (!ins_id || ins_id === undefined || ins_id === "0") {
          if (typeof alertify !== "undefined") alertify.error("❌ Identificador de requerimiento inválido.");
          return false;
      }

      var mensaje_advertencia = "🚨 ¿ESTÁ SEGURO DE ELIMINAR ESTE REQUERIMIENTO?<br><br>" +
                                "<b>Impacto Presupuestario:</b><br>" +
                                "• Se borrará físicamente el insumo del Formulario N° 5.<br>" +
                                "• Se purgará de forma definitiva su desglose de Enero a Diciembre.<br>" +
                                "• El listado general se actualizará de forma automatizada.<br><br>" +
                                "<i>Esta acción es irreversible y auditable en los registros de la CNS.</i>";

      if (typeof alertify !== "undefined") {
          alertify.confirm(mensaje_advertencia, function (a) {
              if (a) {
                  ejecutar_borrado_requerimiento_f5_sincronizado(ins_id, $fila_tr);
              } else {
                  alertify.log("Operación cancelada. El registro permanece intacto.");
              }
          });
      } else {
          if (confirm("🚨 ¿Seguro que desea eliminar este requerimiento y toda su temporalidad mensual de dinero?")) {
              ejecutar_borrado_requerimiento_f5_sincronizado(ins_id, $fila_tr);
          }
      }
  });

  /**
   * Purgado físico relacional y llamada al motor asíncrono de recarga con preloader
   */
  function ejecutar_borrado_requerimiento_f5_sincronizado(ins_id, $fila_tr) {
      var url = base + "index.php/programacion/crequerimiento/eliminar_requerimiento_unitario";
      
      // Pintamos la celda en un tono rojo sutil de destrucción inminente
      $fila_tr.css({'background-color': '#fee2e2', 'opacity': '0.6'});

      // Captura automática de Token CSRF perimetral de la CNS
      var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
      var csrf_hash = $('[name="csrf_test_name"]').val() || '';
      var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

      $.ajax({
          type: "POST",
          url: url,
          dataType: "json",
          data: "ins_id=" + ins_id + token_seguridad,
          success: function (res) {
              if (res.status === 'success' || res.respuesta === 'correcto') {
                  
                  if (typeof alertify !== "undefined") {
                      alertify.success("✔ " + res.message);
                  }

                  // 🌟 MÓDULO SINCRONIZADO: Desvanecimiento estético e invocación al motor de recarga AJAX
                  $fila_tr.fadeOut(400, function() {
                      $(this).remove(); // Removemos físicamente el nodo TR viejo

                      // 🛠️ IGUAL A CAMBIAR ACTIVIDAD: Jalamos el ID del producto desde el input hidden global
                      var prod_id_global = $('#prod_id').val();
                      
                      // Invocamos la subfunción asíncrona máster que muestra el loading y refresca la tabla
                      if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                          recargar_listado_requerimientos_cns_ajax(prod_id_global);
                      } else {
                          // Resguardo lineal por si hay fallas de compilación en caliente
                          location.reload();
                      }
                  });

              } else {
                  // Restablecemos los estilos si el servidor rechaza la operación (ej: celda certificada)
                  $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
                  alert("🚨 CONTROL PRESUPUESTARIO CNS:\n\nImposible eliminar el requerimiento.\nDetalle: " + res.message);
              }
          },
          error: function (xhr, textStatus, errorThrown) {
              $fila_tr.css({'background-color': '#ffffff', 'opacity': '1'});
              console.error("CNS ERROR DELETE REQ -> Status: " + textStatus + " | Detalle: " + errorThrown);
              if (typeof alertify !== "undefined") alertify.error("❌ Falla de red. El servidor abortó la remoción.");
          }
      });
  }
  /// ---------------------------------------------------











  ////// ===== MODIFICAR REQUERIMIENTO 2027
  $(document).ready(function() {
      // 🌟 REPARADO CORE: Declaramos la variable de petición FUERA del evento para un abort legítimo
      var request;

      // Mutamos la escucha a delegación global por si la grilla se actualiza asíncronamente
      $(document).on("click", ".mod_ff", function (e) {
          e.preventDefault();
          
          var ins_id = $(this).attr('name');
          document.getElementById("ins_id").value = ins_id;

          var url = base + "index.php/programacion/crequerimiento/get_requerimiento";

          // Ocultamos la botonera de guardado y vaciamos banners previos
          $('#mbut').hide();
          $('#amtit').html('');

          // 🌟 INYECCIÓN DEL PRELOADER VECTORIAL COPORATIVO MIENTRAS CARGA LA RED
          // Ocupa el contenedor central para dar un feedback visual fluido de alta velocidad
          $('#amtit').html(
              '<div id="loading_modal_f5" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 200px; padding: 20px; background: #ffffff;">' +
                  '<div style="position: relative; width: 45px; height: 45px; margin-bottom: 15px;">' +
                      '<div style="box-sizing: border-box; display: block; position: absolute; width: 40px; height: 48px; border: 4px solid #e2e8f0; border-radius: 50%;"></div>' +
                      '<div style="box-sizing: border-box; display: block; position: absolute; width: 40px; height: 48px; border: 4px solid transparent; border-top-color: #0aa699; border-radius: 50%; animation: spin_f5_mod 0.8s linear infinite;"></div>' +
                  '</div>' +
                  '<h5 style="font-family: Arial, sans-serif; font-weight: 700; color: #1e293b; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 4px 0;">' +
                      'Buscando Requerimiento' +
                  '</h5>' +
                  '<p style="font-family: Arial, sans-serif; font-size: 11px; color: #64748b; margin: 0; font-weight: 500;">' +
                      '<i class="fa fa-database text-warning" style="animation: pulse_f5_mod 1.5s infinite; margin-right: 4px;"></i> ' +
                      'Extrayendo saldos y temporalidades desde PostgreSQL...' +
                  '</p>' +
                  '<style>' +
                      '@keyframes spin_f5_mod { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
                      '@keyframes pulse_f5_mod { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }' +
                  '</style>' +
              '</div>'
          );

          // 2. Abortamos peticiones simultáneas previas encoladas en red
          if (request) {
              request.abort();
          }

          // 3. Configuración del AJAX Multipart estándar de la plataforma
          request = $.ajax({
              url: url,
              type: "POST",
              dataType: 'json',
              data: "ins_id=" + ins_id
          });

          request.done(function (response, textStatus, jqXHR) {
              // 🌟 REMOCIÓN DEL LOADING: Limpiamos el banner de espera para dar paso a los datos
              $('#amtit').html('');

              if (response.respuesta == 'correcto') {
                 // Devolvemos la visibilidad a la botonera de persistencia
                 $('#mbut').fadeIn(200);

                 document.getElementById("saldo").value = parseFloat(response.monto_saldo).toFixed(2);
                 document.getElementById("sal").value = parseFloat(response.monto_saldo).toFixed(2);
                 
                 // Mapeo seguro con los índices bidimensionales de tu modelo original [0]
                 document.getElementById("detalle").value  = response.insumo[0]['ins_detalle'];

                // 1. Redondeo estricto de CANTIDAD a número entero puro (0 decimales)
                var cantidad_maestra = parseFloat(response.insumo[0]['ins_cant_requerida']) || 0;
                document.getElementById("cantidad").value = cantidad_maestra.toFixed(0);

                // 2. Redondeo estricto de PRECIO UNITARIO a 2 decimales contables
                var precio_maestro = parseFloat(response.insumo[0]['ins_costo_unitario']) || 0.00;
                document.getElementById("costou").value   = precio_maestro.toFixed(2);

                // 3. Redondeo estricto de COSTOS TOTALES a 2 decimales contables
                var total_maestro = parseFloat(response.insumo[0]['ins_costo_total']) || 0.00;
                document.getElementById("costot").value   = total_maestro.toFixed(2);  // Input hidden
                document.getElementById("costot2").value  = total_maestro.toFixed(2); // Input visible disabled
                 
                 document.getElementById("par_padre").value = response.ppdre[0]['par_codigo'];
                 
                 $("#par_hijo").html(response.lista_partidas);
                 document.getElementById("iumedida").value = response.insumo[0]['ins_unidad_medida'];
                 document.getElementById("mtot").value = parseFloat(response.insumo[0]['programado_total'] || 0).toFixed(2);
                 document.getElementById("observacion").value = response.insumo[0]['ins_observacion'];
                 
                 // Auditoría de consistencia horizontal en vivo
                 if(parseFloat(response.insumo[0]['programado_total'] || 0) != parseFloat(response.insumo[0]['ins_costo_total'] || 0)){
                  $('#amtit').html('<center><div class="alert alert-danger alert-block" style="font-weight:bold; margin-top:10px;">⚠️ EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO</div></center>');
                  $('#mbut').slideUp(150);
                 }

                 // Seteo y blindaje elástico de celdas de Enero a Diciembre
                 for (var i = 1; i <= 12; i++) {
                      var input_mes = document.getElementById("mm" + i);
                      
                      if (input_mes) {
                          var monto_mes_crudo = parseFloat(response.insumo[0]['mes' + i]) || 0.00;
                          input_mes.value = monto_mes_crudo.toFixed(2);

                          // FOCUS EVENT: Limpia el cero flotante al recibir el foco
                          input_mes.addEventListener('focus', function() {
                              var valor_actual = parseFloat(this.value) || 0;
                              if (valor_actual === 0) {
                                  this.value = ''; 
                              }
                          });

                          // BLUR EVENT: Repone el 0.00 formateado si abandonan la celda vacía
                          input_mes.addEventListener('blur', function() {
                              if ($.trim(this.value) === '') {
                                  this.value = '0.00';
                              } else {
                                  var numero_digitado = parseFloat(this.value) || 0.00;
                                  this.value = numero_digitado.toFixed(2);
                              }
                              // Invoca el re-cálculo horizontal masivo
                              suma_programado_modificado();
                          });
                      }
                  }
                 
              } else {
                  if (typeof alertify !== "undefined") {
                      alertify.error("ERROR AL RECUPERAR DATOS DEL REQUERIMIENTO");
                  }
              }
          });

          // Captura perimetral por si el servidor local XAMPP sufre caídas de red o timeouts
          request.fail(function (jqXHR, textStatus, errorThrown) {
              if (textStatus !== 'abort') {
                  $('#amtit').html('<div class="alert alert-danger text-center" style="font-weight:bold;">❌ Error crítico de comunicación con el servidor CNS.</div>');
                  if (typeof alertify !== "undefined") alertify.error("Falla de red.");
              }
          });
      });

    //// subir informacion editada
    $(document).on("click", "#subir_mins", function (e) {
        e.preventDefault();

        // 1. Inicializamos y ejecutamos las reglas de jQuery Validate sobre el SmartForm
        var $validator = $("#form_mod").validate({
            rules: {
                ins_id: { required: true },
                detalle: { required: true },
                cantidad: { required: true, digits: true },
                costou: { required: true, number: true },
                iumedida: { required: true },
                par_padre: { required: true },
                par_hijo: { required: true }
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

        // Si el formulario tiene campos vacíos obligatorios, frena la ejecución
        if (!$("#form_mod").valid()) {
            $validator.focusInvalid();
            return false;
        }

        // 2. Extracción de variables para control de balances contables locales
        var costot = parseFloat($("#costot").val()) || 0;
        var mtot   = parseFloat($("#mtot").val()) || 0;
        var saldo  = parseFloat($("#saldo").val()) || 0;

        // Candado A: Verificación de cuadre horizontal estricto (Total == Suma meses)
        if (Math.round(costot * 100) !== Math.round(mtot * 100)) {
            if (typeof alertify !== "undefined") {
                alertify.error("🚨 Descuadre Contable: La suma de mensualidades no coincide con el Costo Total.");
            }
            return false;
        }

        // Candado B: Verificación de Techo Presupuestario Máximo de la Partida
       /* if (costot > saldo) {
            if (typeof alertify !== "undefined") {
                alertify.error("🚨 Restricción SIGEP: El Costo Total excede el Techo de Saldo asignado.");
            }
            return false;
        }*/

        // 3. Confirmación institucional mediante Alertify antes de tocar la base de datos
        if (typeof alertify !== "undefined") {
            alertify.confirm("🚨 ¿CONFIRMA LA ACTUALIZACIÓN EN CALIENTE DE ESTE REQUERIMIENTO?", function (a) {
                if (a) {
                    
                    // Bloqueamos la botonera e inyectamos el pre-loader local del modal
                    $("#loadm").show();
                    $("#subir_mins").prop('disabled', true).val("GUARDANDO CAMBIOS CONTABLES...");

                    // Empaquetamos el formulario de forma nativa multiparte para el AJAX
                    var formElement = document.getElementById('form_mod');
                    var data_multipart = new FormData(formElement);

                    // Captura e inyección automática del Token CSRF de seguridad de la CNS
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    if (csrf_name !== '') { 
                        data_multipart.append(csrf_name, csrf_hash); 
                    }

                    // Abortamos solicitudes previas en cola para evitar hilos huérfanos
                    if (xhr_guardar_f5 && xhr_guardar_f5.readyState !== 4) { 
                        xhr_guardar_f5.abort(); 
                    }

                    // 4. Despachamos la ráfaga de red hacia el controlador de CodeIgniter
                    xhr_guardar_f5 = $.ajax({
                        type: "POST",
                        url: base + "index.php/programacion/crequerimiento/valida_update_insumo",
                        data: data_multipart,
                        processData: false, // Evita que JQuery intente transformar los inputs en texto plano
                        contentType: false, // Forza a Apache a leer las cabeceras como multipart/form-data
                        success: function (res) {
                            // Apagamos los loaders de espera y devolvemos la interacción al botón
                            $("#loadm").hide();
                            $("#subir_mins").prop('disabled', false).val("GUARDAR MODIFICACIÓN");

                            var response = (typeof res === 'object') ? res : JSON.parse(res);

                            if (response.respuesta === 'correcto' || response.status === 'success') {
                                
                                alertify.success("✔ " + (response.message || "Cambios guardados con éxito."));
                                
                                // Ocultamos el modal de SmartAdmin de forma limpia y reseteamos el backdrop oscuro
                                $('#modal_mod_ff').modal('hide');
                                $('.modal-backdrop').remove();
                                $('body').removeClass('modal-open');

                                // 🌟 ACTUALIZACIÓN SUTIL ASÍNCRONA: Ejecuta el preloader vectorial verde agua
                                // que refresca la tabla web de inmediato leyendo el nuevo estado de Postgres
                                var prod_id_global = $('#prod_id').val();
                                if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                                    recargar_listado_requerimientos_cns_ajax(prod_id_global);
                                } else {
                                    location.reload(); // Resguardo clásico si no estuviera la subfunción
                                }

                            } else {
                                alertify.error("🚨 Rechazo de Consistencia: " + response.message);
                            }
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            $("#loadm").hide();
                            $("#subir_mins").prop('disabled', false).val("GUARDAR MODIFICACIÓN");
                            if (textStatus !== 'abort') {
                                alertify.error("❌ Falla crítica de red. El servidor abortó la transacción.");
                            }
                        }
                    });
                } else {
                    alertify.error("OPCIÓN MODIFICACIÓN CANCELADA");
                }
            });
        }
    });
  });


  /////// Suma programado de meses
  function suma_programado_modificado() {
      var suma_acumulada = 0;

      // 1. Iteramos de forma elástica del 1 al 12 recolectando las celdas del DOM
      for (var i = 1; i <= 12; i++) {
          var $input_mes = $("#mm" + i);
          
          if ($input_mes.length > 0) {
              // Sanitizamos el valor removiendo posibles espacios o caracteres espurios
              var valor_crudo = $input_mes.val().replace(/,/g, '');
              var monto_mes   = parseFloat(valor_crudo) || 0.00;
              
              // Acumulamos la sumatoria contable
              suma_acumulada += monto_mes;
          }
      }

      // 2. Redondeamos el total a precisión fija de dos centavos para evitar ruidos de punto flotante de JS
      var total_programado = Math.round(suma_acumulada * 100) / 100;

      // 3. Estampamos la sumatoria en caliente en el campo resumen del modal
      $("#mtot").val(total_programado.toFixed(2));

      // 4. AUDITORÍA DE CONSISTENCIA HORIZONTAL: Comparamos contra el Costo Total Calculado
      var costo_total_raw = $("#costot").val() || "0";
      var costo_total     = parseFloat(costo_total_raw.replace(/,/g, '')) || 0.00;

      if (total_programado !== costo_total) {
          // 🚨 DESCUADRE CONTABLE: Inyectamos banner rojo informativo y congelamos el botón guardar
          $('#amtit').html(
              '<div class="alert alert-danger text-center" style="font-weight:bold; margin-bottom:15px; padding:10px; border-radius:4px; border-left:5px solid #ef4444; background:#fef2f2; color:#991b1b; font-size:12px;">' +
                  '<i class="fa fa-exclamation-triangle"></i> ⚠️ ALERTA CONTABLE DE COINCIDENCIA:<br>' +
                  'La sumatoria de la distribución mensual (' + total_programado.toFixed(2) + ' Bs.) no cuadra con el COSTO TOTAL calculado para el requerimiento (' + costo_total.toFixed(2) + ' Bs.). Ajuste los meses.' +
              '</div>'
          );
          
          // Escondemos contenedor y deshabilitamos el botón para mitigar inyecciones erróneas en PostgreSQL
          $('#mbut').slideUp(150);
          $('#subir_mins').prop('disabled', true).css('cursor', 'not-allowed');
      } else {
          // ✔️ CUADRE CONTABLE EXITOSO: Limpiamos alertas y liberamos los controles de persistencia
          $('#amtit').html('');
          $('#mbut').slideDown(150);
          $('#subir_mins').prop('disabled', false).css('cursor', 'pointer');
      }
  }

    $(document).ready(function() {
      pageSetUp();
      $("#par_padre").change(function () {
          $("#par_padre option:selected").each(function () {
            elegido=$(this).val();
            $.post(base+"index.php/prog/combo_partidas", { elegido: elegido }, function(data){ 
            $("#par_hijo").html(data);
            });     
        });
      });
    })


  function costo_totalm() {
      var cantidad_str = document.getElementById("cantidad").value.trim();
      var costou_str   = document.getElementById("costou").value.trim();
      var saldo        = parseFloat(document.getElementById("saldo").value) || 0;

      // 🌟 CANDADO 1: CONTROL DE DECIMALES ESTRICTO MEDIANTE EXPRESIÓN REGULAR
      // ^[0-9]+(\.[0-9]{1,2})?$ -> Solo permite números enteros o con máximo 2 decimales separados por punto
      if (costou_str !== "") {
          var regex_decimales = /^[0-9]+(\.[0-9]{1,2})?$/;
          
          if (!regex_decimales.test(costou_str)) {
              $('#amtit').html(
                  '<div class="alert alert-danger text-center" style="font-weight:bold; margin-bottom:15px; padding:10px; border-radius:4px; border-left:5px solid #ef4444; background:#fef2f2; color:#991b1b; font-size:12px;">' +
                      '<i class="fa fa-times-circle"></i> 🚨 RESTRICCIÓN DE FORMATO CNS:<br>' +
                      'El precio unitario introducido (' + costou_str + ') es inválido. Solo se permiten hasta dos decimales separados por punto (Ej. correcto: 150.50 o 200).' +
                  '</div>'
              );
              
              // Bloqueamos físicamente el botón guardar e impedimos el envío
              $('#mbut').slideUp(150);
              $('#subir_mins').prop('disabled', true).css('cursor', 'not-allowed');
              return false;
          }
      }

      // Si el formato del precio es válido, procedemos con los cálculos matemáticos tradicionales
      var cantidad = parseInt(cantidad_str) || 0;
      var costou   = parseFloat(costou_str) || 0;
      
      // Calculamos el costo total con redondeo de precisión
      var total = Math.round((cantidad * costou) * 100) / 100;
      
      // Estampamos los valores calculados en las cajas correspondientes
      document.getElementById("costot").value  = total.toFixed(2);
      document.getElementById("costot2").value = total.toFixed(2);

      // 🔒 CANDADO 2: CONTROL DE SALDO MÁXIMO DISPONIBLE (SIGEP)
/*      if (total > saldo) {
          $('#amtit').html(
              '<div class="alert alert-danger text-center" style="font-weight:bold; margin-bottom:15px; padding:10px; border-radius:4px; border-left:5px solid #ef4444; background:#fef2f2; color:#991b1b; font-size:12px;">' +
                  '<i class="fa fa-exclamation-circle"></i> 🚨 RESTRICCIÓN DE PRESUPUESTO:<br>' +
                  'El Costo Total calculado (' + total.toFixed(2) + ' Bs.) excede el Techo de Saldo Máximo Disponible para esta partida (' + saldo.toFixed(2) + ' Bs.). Ajuste los valores.' +
              '</div>'
          );
          $('#mbut').slideUp(150);
          $('#subir_mins').prop('disabled', true).css('cursor', 'not-allowed');
          return false;
      }*/

      // Si pasa los dos controles (formato y saldo), evalúa la coincidencia horizontal de los 12 meses
      evaluar_concordancia_modal_f5();
  }

  function evaluar_concordancia_modal_f5() {
    var costot = parseFloat(document.getElementById("costot").value) || 0;
    var mtot   = parseFloat(document.getElementById("mtot").value) || 0;
    
    // Aplicamos tolerancia de centavos para mitigar ruidos flotantes de JS
    if (Math.round(costot * 100) !== Math.round(mtot * 100)) {
        $('#amtit').html(
            '<div class="alert alert-danger text-center" style="font-weight:bold; margin-bottom:15px; padding:10px; border-radius:4px; border-left:5px solid #ef4444; background:#fef2f2; color:#991b1b; font-size:12px;">' +
                '<i class="fa fa-exclamation-triangle"></i> ⚠️ ALERTA CONTABLE DE COINCIDENCIA:<br>' +
                'La sumatoria de la distribución mensual (' + mtot.toFixed(2) + ' Bs.) no cuadra con el COSTO TOTAL calculado para el requerimiento (' + costot.toFixed(2) + ' Bs.). Ajuste los meses.' +
            '</div>'
        );
        $('#mbut').slideUp(150); // Esconde el botón guardar si hay descuadre contable
        $('#subir_mins').prop('disabled', true).css('cursor', 'not-allowed');
    } else {
        // Cuadre contable exitoso: Limpiamos advertencias y liberamos los controles de persistencia
        $('#amtit').html('');
        $('#mbut').slideDown(150); // Libera el botón guardar
        $('#subir_mins').prop('disabled', false).css('cursor', 'pointer');
    }
  }

  function justNumbers(e) {
      var key = e.keyCode || e.which;
      var keyboard = String.fromCharCode(key);
      
      // Permitir números (0-9), el punto decimal (.) y teclas de control
      var regex = /[0-9.]/;
      
      // Validamos caracteres especiales de control (Backspace, Delete, flechas, Tab, Enter)
      if (key == 8 || key == 9 || key == 46 || key == 13 || key == 37 || key == 39) {
          return true;
      }
      
      // Impedir que digiten un segundo punto si la celda ya tiene uno activo (Evita NaN)
      if (keyboard == '.' && e.target.value.indexOf('.') !== -1) {
          return false;
      }
      
      if (!regex.test(keyboard)) {
          return false;
      }
  }


      //// eliminacion masiva de requerimientos
    $(document).on('click', '#btn_eliminar_masivo_f5', function(e) {
        e.preventDefault();

        var ids_seleccionados = [];
        
        // Recorremos todos los checkboxes que el operador regional tildó en la grilla
        $('.check-eliminar:checked').each(function() {
            ids_seleccionados.push($(this).val());
        });

        if (ids_seleccionados.length === 0) {
            if (typeof alertify !== "undefined") {
                alertify.error("⚠️ Restricción: Debe seleccionar al menos un requerimiento para eliminar.");
            }
            return false;
        }

        var mensaje = `🚨 <b>¿CONFIRMA LA ELIMINACIÓN MASIVA DEL POA?</b><br><br>` +
                      `• Se eliminarán físicamente <b>${ids_seleccionados.length}</b> requerimientos seleccionados.<br>` +
                      `• Se purgarán en cascada todos sus desgloses mensuales de Enero a Diciembre.<br>` +
                      `• El presupuesto general de la actividad se re-ajustará de inmediato.<br><br>` +
                      `<i>Esta acción masiva es irreversible. ¿Desea proceder?</i>`;

        if (typeof alertify !== "undefined") {
            alertify.confirm(mensaje, function(a) {
                if (a) {
                    ejecutar_purga_masiva_f5_ajax(ids_seleccionados);
                }
            });
        }
    });

    function ejecutar_purga_masiva_f5_ajax(ids_lote) {
        var url = base + "index.php/programacion/crequerimiento/eliminar_requerimientos_masivo";
        var $btn = $('#btn_eliminar_masivo_f5');

        $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> Eliminando lote de Requerimientos seleccionados...');

        // Captura automática de Token CSRF perimetral
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';

        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: {
                ins_ids: ids_lote, // Enviamos la matriz limpia de IDs al controlador
                csrf_test_name: csrf_hash
            },
            success: function(res) {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Eliminar Seleccionados');

                if (res.status === 'success' || res.respuesta === 'correcto') {
                    if (typeof alertify !== "undefined") alertify.success("✔ " + res.message);

                    // Refrescamos de inmediato la grilla llamando a tu loader asíncrono unificado
                    var prod_id_global = $('#prod_id').val();
                    if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                        recargar_listado_requerimientos_cns_ajax(prod_id_global);
                    } else {
                        location.reload();
                    }
                } else {
                    alert("🚨 CONTROL PRESUPUESTARIO CNS:\n\nOperación abortada.\nDetalle: " + res.message);
                }
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-trash"></i> Eliminar Seleccionados');
                if (typeof alertify !== "undefined") alertify.error("❌ Falla de red en el lote masivo.");
            }
        });
    }