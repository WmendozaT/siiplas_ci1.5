base = $('[name="base"]').val();

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

//// FUNCION MODAL PARA SUBIR ARCHIVO EXCEL
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

        var form = $('#form_subir_form4_institucional')[0];
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
            url: $('#form_subir_form4_institucional').attr('action'),
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
////



///// Eliminar Registro de lo migrado Act4 Insitucional
var xhr_eliminacion_masiva_formularios = null;
$(document).ready(function() {
    function restaurar_configuracion_alertify() {
        if (typeof alertify !== "undefined") {
            alertify.set({
                labels: { ok: "ACEPTAR Y ELIMINAR", cancel: "CANCELAR" },
                delay: 5000,
                buttonReverse: false,
                buttonFocus: "cancel" 
            });
        }
    }

    $(document).on("click", ".btn-eliminar-opcion", function (e) {
        e.preventDefault();
        restaurar_configuracion_alertify();

        var $btn_opcion = $(this);
        var opcion_id   = $btn_opcion.data("opcion") || $btn_opcion.attr("data-opcion");
        var texto_item  = $btn_opcion.text().trim(); 

        if (!opcion_id) {
            if (typeof alertify !== "undefined") alertify.error("⚠️ Error: Atributos contextuales de la fila corruptos.");
            return false;
        }

        var mensaje_confirmacion = `🚨 <b>¿CONFIRMA LA ELIMINACIÓN MASIVA SELECCIONADA?</b><br><br>` +
                                   `• Acción: <span style="color:#dc2626; font-weight:bold;">${texto_item}</span><br>` +
                                   `• FORMULARIO : <b>${opcion_id}</b><br><br>` +
                                   `<i>El motor del SIIPLAS v2.0 eliminará todos los registros de Actividades y requerimientos (si corresponde)</i>`;

        if (typeof alertify !== "undefined") {
            alertify.confirm(mensaje_confirmacion, function (a) {
                if (a) {
                    
                    // 🛠️ ACTIVACIÓN DEL LOADING Y BLOQUEO DE CONTROLES
                    var $dropdownBtn = $(".btn-group").find("button.dropdown-toggle");
                    var textoOriginalBtn = $dropdownBtn.html(); // Guardar HTML original (ícono + texto)

                    $("#loading-overlay").css("display", "flex"); // Muestra el overlay masivo
                    $dropdownBtn.prop("disabled", true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO...'); // Bloqueo visual local

                    var url = base+"index.php/programacion/proyecto/delete_formularioN4_masivo_ajax";

                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    
                    var query_post = { 
                        opcion_id: parseInt(opcion_id)
                    };
                    if (csrf_name !== '') { query_post[csrf_name] = csrf_hash; }

                    if (xhr_eliminacion_masiva_formularios && xhr_eliminacion_masiva_formularios.readyState !== 4) {
                        xhr_eliminacion_masiva_formularios.abort();
                    }

                    xhr_eliminacion_masiva_formularios = $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                        data: query_post,
                        success: function (res) {
                            // 🛠️ REMOCIÓN DEL LOADING Y DESBLOQUEO DE CONTROLES
                            $("#loading-overlay").hide();
                            $dropdownBtn.prop("disabled", false).html(textoOriginalBtn);

                            if (res.respuesta === 'correcto' || res.status === 'success') {
                                alertify.success("✔ Los registros fueron eliminados con éxito.");
                                
                                setTimeout(function() {
                                    if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                                        recargar_listado_requerimientos_cns_ajax();
                                    } else {
                                        location.reload(true);
                                    }
                                }, 1500);

                            } else {
                                alertify.error("🚨 Rechazo: " + (res.message || "PostgreSQL denegó la purga física."));
                            }
                        },
                        error: function (xhr, textStatus, errorThrown) {
                            if (textStatus === 'abort') return;
                            
                            // 🛠️ REMOCIÓN DEL LOADING EN CASO DE ERROR
                            $("#loading-overlay").hide();
                            $dropdownBtn.prop("disabled", false).html(textoOriginalBtn);
                            
                            if (xhr.status === 404) {
                                alertify.error("❌ Error 404: Ruta de destino del controlador no localizada.");
                            } else {
                                alertify.error("❌ Error de comunicación: " + textStatus);
                            }
                            console.error("CNS EXCEPTION ELIMINACIÓN MASIVA ->", errorThrown);
                        }
                    });
                } else {
                    alertify.log("Operación cancelada. Las matrices presupuestarias permanecen inalteradas.");
                }
            });
        }
        return false;
    });

});



  function confirmar(){
    if(confirm('¿Estas seguro de Eliminar ?'))
      return true;
    else
    return false;
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


  $( function() {
    $( "#grupoTablas" ).tabs();
  } );

  function justNumbers(e){
    var keynum = window.event ? window.event.keyCode : e.which;
    if ((keynum == 8) || (keynum == 46))
    return true;           
    return /\d/.test(String.fromCharCode(keynum));
  }


  /// deseleccionar TODO Operaciones Regionales
  function deseleccionar_todo(){
    proy_id = document.getElementById("proy_id").value;
    nro = document.getElementById("nro_ope").value;
    tp = 0;
/*      for (var i=1; i<=nro; i++) {
        if(document.getElementById("ope"+i).checked){
            document.getElementById("ope"+i).checked=0;
        }
      }*/

      alertify.confirm("DESELECCIONAR LA ALINEACION ?", function (a) {
        if (a) {
        var url = base+"index.php/programacion/proyecto/deseleccion_seleccion_alineacion";
        $.ajax({
          type: "post",
          url: url,
          data: {
            proy_id:proy_id,tp:tp
          },
          success: function (date) {
            alertify.success("DESELECCIÓN EXITOSA ...");
            window.location.reload(true);
          }
        });

        } else {
            alertify.error("OPCI\u00D3N CANCELADA");
            window.location.reload(true);
        }
        
    });
        
  }

  /// Seleccionar TODO Operaciones Regionales
  function seleccionar_todo(){
    proy_id = document.getElementById("proy_id").value;
    nro = document.getElementById("nro_ope").value;
    tp = 1;

/*      for (var i=1; i<=nro; i++) {
        if(!document.getElementById("ope"+i).checked){
          document.getElementById("ope"+i).checked=1;
        }
      }*/

      alertify.confirm("DESELECCIONAR LA ALINEACION ?", function (a) {
        if (a) {
        var url = base+"index.php/programacion/proyecto/deseleccion_seleccion_alineacion";
        $.ajax({
          type: "post",
          url: url,
          data: {
            proy_id:proy_id,tp:tp
          },
          success: function (date) {
            alertify.success("SELECCIÓN EXITOSA ...");
            window.location.reload(true);
          }
        });

        } else {
            alertify.error("OPCI\u00D3N CANCELADA");
            window.location.reload(true);
        }
    });

  }




  //// VER POA
  $(function () {
    $(".enlace").on("click", function (e) {

        proy_id = $(this).attr('name');
        establecimiento = $(this).attr('id');
        
        $('#titulo').html('<font size=3><b>'+establecimiento+'</b></font>');
        $('#content1').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Ediciones - <br>'+establecimiento+'</div>');
        
        var url = base+"index.php/programacion/proyecto/get_poa";
        var request;
        if (request) {
            request.abort();
        }
        request = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "proy_id="+proy_id
        });

        request.done(function (response, textStatus, jqXHR) {
        if (response.respuesta == 'correcto') {

            $('#content1').fadeIn(1000).html(response.tabla);
            $('#caratula').fadeIn(1000).html(response.caratula);
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

  /*------ AJUSTE POA ------*/
/*  $(function () {
    $(".enlace2").on("click", function (e) {
        proy_id = $(this).attr('name');
        establecimiento = $(this).attr('id');
       
        $('#titulo2').html('<font size=3><b>'+establecimiento+'</b></font>');
        $('#content2').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Poa - <br>'+establecimiento+'</div>');
        
        var url = base+"index.php/programacion/proyecto/get_poa_ajuste";
        var request;
        if (request) {
            request.abort();
        }
        request = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "proy_id="+proy_id
        });

        request.done(function (response, textStatus, jqXHR) {
        if (response.respuesta == 'correcto') {
            $('#content2').fadeIn(1000).html(response.tabla);
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
  });*/

  /*------------ VERIFICANDO POA ----------------*/
 /* $(function () {
      $(".verif_poa").on("click", function (e) {
        proy_id = $(this).attr('name');

          document.getElementById("proy_id").value=proy_id;
          
          establecimiento = $(this).attr('id');
          $('#titulo').html('<font size=3><b>'+establecimiento+'</b></font>');
          $('#content_valida').html('<div class="loading" align="center"><h2>Verificando Presupuesto POA  <br>'+establecimiento+'</h2><br><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /></div>');
          $('#but').slideUp();

          var url = base+"index.php/programacion/proyecto/verif_poa";
          var request;
          if (request) {
              request.abort();
          }
          request = $.ajax({
              url: url,
              type: "POST",
              dataType: 'json',
              data: "proy_id="+proy_id
          });

          request.done(function (response, textStatus, jqXHR) {
          if (response.respuesta == 'correcto') {
            $('#content_valida').fadeIn(1000).html(response.tabla);
                  if(response.valor==0){
                      $('#but').slideDown();
                }
          }
          else{
              alertify.error("ERROR AL RECUPERAR DATOS");
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
          $("#enviar_ff").on("click", function (e) {
              var $valid = $("#form_vpoa").valid();
              if (!$valid) {
                  $validator.focusInvalid();
              } else {

                  alertify.confirm("ESTA SEGURO EN VALIDAR EL POA, PARA SU APROBACIÓN ?", function (a) {
                      if (a) {
                      var url = base+"index.php/programacion/proyecto/validar_poa";
                      $.ajax({
                          type: "post",
                          url: url,
                          data: {
                              proy_id: proy_id
                          },
                          success: function (date) {
                              window.location.reload(true);
                              alertify.success("VALIDACION EXITOSA ...");
                          }
                      });

                      } else {
                          alertify.error("OPCI\u00D3N CANCELADA");
                      }
                  });

              }
          });
      });
  });*/


$(document).ready(function() {
    
    // 🌟 REPARADO CORE 1: Variables perimetrales de red globales en la raíz del archivo
    var xhr_verif_poa = null;
    var xhr_validar_final = null;

    /* ==========================================================================
       A. ESCUCHA DE APERTURA: CONSULTA Y VERIFICACIÓN DE VARIACIONES
       ========================================================================== */
    $(document).on("click", ".verif_poa", function (e) {
        e.preventDefault();
        
        var $btn = $(this);
        var proy_id = $btn.attr('name');
        var establecimiento = $btn.attr('id');

        var input_proy = document.getElementById("proy_id");
        if (input_proy) input_proy.value = proy_id;
        
        $('#titulo').html('<span style="font-size: 13px; font-weight: bold; color: #1e293b;">' + establecimiento + '</span>');
        
        // Inyección de preloader vectorial elástico limpio
        $('#content_valida').html(
            '<div style="text-align:center; padding: 35px 15px; color: #475569;">' +
                '<i class="fa fa-refresh fa-spin fa-2x text-primary" style="margin-bottom:10px; display:block;"></i>' +
                '<strong style="font-size:12px; display:block; text-transform:uppercase;">Analizando Balanza Presupuestaria ...</strong>' +
                '<span style="font-size:11px; color:#64748b;">Verificando variaciones decimales para: ' + establecimiento + '</span>' +
            '</div>'
        );
        $('#but').slideUp(100);

        var url = base + "index.php/programacion/proyecto/verif_poa";

        // Abortamos ráfagas simultáneas concurrentes en cola
        if (xhr_verif_poa && xhr_verif_poa.readyState !== 4) {
            xhr_verif_poa.abort();
        }

        xhr_verif_poa = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: "proy_id=" + proy_id
        });

        xhr_verif_poa.done(function (response) {
            if (response.respuesta === 'correcto' || response.status === 'success') {
                $('#content_valida').hide().html(response.tabla).fadeIn(250);
                
                // Si el valor es cero (0), significa que no existen variaciones y liberamos el botón "Enviar"
                if (parseInt(response.valor) === 0) {
                    $('#but').slideDown(150);
                }
            } else {
                if (typeof alertify !== "undefined") alertify.error("❌ Error de consistencia al procesar la balanza.");
                $('#content_valida').html('<div class="alert alert-danger">Error al recuperar balances de la base de datos.</div>');
            }
        });

        xhr_verif_poa.fail(function (xhr, textStatus) {
            if (textStatus !== 'abort') {
                $('#content_valida').html('<div class="alert alert-danger">❌ Falla crítica de red al conectar con el servidor central.</div>');
            }
        });
    });

    /* ==========================================================================
       B. ESCUCHA DE ENVÍO SEPARADA: COMPROMISO Y VALIDACIÓN FINAL DEL POA
       ========================================================================== */
    $(document).on("click", "#enviar_ff", function (e) {
        e.preventDefault();
        
        var $btn_guardar = $(this);
        var proy_id = $("#proy_id").val();

        if (!proy_id || proy_id <= 0) {
            if (typeof alertify !== "undefined") alertify.error("⚠️ Operación abortada: Hash correlativo no localizado.");
            return false;
        }

        // Si usas jQuery Validate sobre tu contenedor de formulario
        if ($("#form_vpoa").length > 0 && !$("#form_vpoa").valid()) {
            return false;
        }

        if (typeof alertify !== "undefined") {
            alertify.confirm("🚨 <b>¿CONFIRMA LA VALIDACIÓN DEFINITIVA DEL POA INSTITUCIONAL?</b><br><br><i>Al validar, la formulación se bloqueará y se enviará a la Dirección Nacional para su aprobación física. Esta acción no se puede deshacer. ¿Desea proceder?</i>", function (a) {
                if (a) {
                    
                    // Bloqueo total de la botonera y disparo del overlay
                    $("#loading-overlay").css("display", "flex");
                    $("#but").hide();
                    $btn_guardar.prop("disabled", true).text("PROCESANDO VALIDACIÓN...");

                    var url = base + "index.php/programacion/proyecto/validar_poa";
                    
                    // Captura e inyección automática del Token CSRF perimetral
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    
                    var datos_post = { proy_id: proy_id };
                    if (csrf_name !== '') { datos_post[csrf_name] = csrf_hash; }

                    if (xhr_validar_final && xhr_validar_final.readyState !== 4) {
                        xhr_validar_final.abort();
                    }

                    xhr_validar_final = $.ajax({
                        type: "POST",
                        url: url,
                        dataType: "json",
                        data: datos_post,
                        success: function (res) {
                            $("#loading-overlay").hide();

                            if (res.respuesta === 'correcto' || res.status === 'success') {
                                alertify.success("✔ ¡POA VALIDADO OFICIALMENTE CON ÉXITO!...");
                                setTimeout(function() {
                                    window.location.reload(true);
                                }, 1000);
                            } else {
                                $("#but").show();
                                $btn_guardar.prop("disabled", false).html('<i class="fa fa-save"></i> Validar POA');
                                alert("🚨 COMPILADOR POA RECHAZADO:\n\n" + res.message);
                            }
                        },
                        error: function (xhr, textStatus) {
                            $("#loading-overlay").hide();
                            $("#but").show();
                            $btn_guardar.prop("disabled", false).html('<i class="fa fa-save"></i> Validar POA');
                            if (textStatus !== 'abort') {
                                alertify.error("❌ Falla crítica de comunicación. PostgreSQL revocó el compromiso.");
                            }
                        }
                    });
                }
            });
        }
    });
});






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
      /*--- APROBAR PROYECTOS DE INVERSION ---*/
      $(".aprob_pi").on("click", function (e) {
          reset();
          var proy_id = $(this).attr('name');
          var request;
          alertify.confirm("ESTA SEGURO DE APROBAR POA ?", function (a) {
            if (a) { 
                var url = base+"index.php/programacion/proyecto/aprobar_poa";
                if (request) {
                    request.abort();
                }
                request = $.ajax({
                    url: url,
                    type: "POST",
                    dataType: "json",
                  data: "proy_id="+proy_id

                });

                request.done(function (response, textStatus, jqXHR) { 
                  reset();
                  if (response.respuesta == 'correcto') {
                      alertify.alert("EL POA SE APROBO CORRECTAMENTE ", function (e) {
                          if (e) {
                              window.location.reload(true);
                          }
                      });
                  } else {
                      alertify.alert("ERROR !!!", function (e) {
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
                alertify.error("OPCIÓN CANCELADA");
            }
          });
          return false;
      });
    });

    ///// Rechazar POA

  $(function () {
      function reset() {
        // http://localhost/SIIPLAS2021/siiplas_2021/
        //  $("#toggleCSS").attr("href", "<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css");
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
      /*----------- RECHAZAR POA ---------------*/
      $(".neg_ff").on("click", function (e) {
       // alert(base+'/assets/themes_alerta/alertify.default.css')
          reset();
          var proy_id = $(this).attr('name');
          var request;
          alertify.confirm("ESTA SEGURO DE RECHAZAR EL POA Y DEVOLVER AL RESPONSABLE POA ?", function (a) {
              if (a) { 
                  var url = base+"index.php/programacion/proyecto/observar_poa";
                  if (request) {
                      request.abort();
                  }
                  request = $.ajax({
                      url: url,
                      type: "POST",
                      dataType: "json",
                    data: "proy_id="+proy_id

                  });

                  request.done(function (response, textStatus, jqXHR) { 
                    reset();
                    if (response.respuesta == 'correcto') {
                        alertify.alert("SE RECHAZO REPORTE POA", function (e) {
                            if (e) {
                                window.location.reload(true);
                            }
                        });
                    } else {
                        alertify.alert("ERROR AL OBSERVAR !!!", function (e) {
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
                  alertify.error("OPCIÓN CANCELADA");
              }
          });
          return false;
      });
  });


  $(function () {
      function reset() {
        // http://localhost/SIIPLAS2021/siiplas_2021/
        //  $("#toggleCSS").attr("href", "<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css");
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
       /*----------- APROBAR POA ---------------*/
        $(".aprobar_poa").on("click", function (e) {
          reset();
          var proy_id = $(this).attr('name');
          var request;
          alertify.confirm("ESTA SEGURO DE APROBAR POA ?", function (a) {
              if (a) { 
                  var url = base+"index.php/programacion/proyecto/aprobar_poa";
                  if (request) {
                      request.abort();
                  }
                  request = $.ajax({
                      url: url,
                      type: "POST",
                      dataType: "json",
                    data: "proy_id="+proy_id

                  });

                  request.done(function (response, textStatus, jqXHR) { 
                    reset();
                    if (response.respuesta == 'correcto') {
                        alertify.alert("EL POA SE APROBO CORRECTAMENTE ", function (e) {
                            if (e) {
                                window.location.reload(true);
                            }
                        });
                    } else {
                        alertify.alert("ERROR AL OBSERVAR !!!", function (e) {
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
                  alertify.error("OPCIÓN CANCELADA");
              }
          });
          return false;
      });

    });