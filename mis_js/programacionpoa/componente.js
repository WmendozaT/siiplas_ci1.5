base = $('[name="base"]').val();

    ////----- Exportar Actividades por Unidad Responsable
    function exportarExcelConLoading(botonElemento, id) {
        // 🛠️ REPARADO: Mapeo relativo para congelar únicamente la celda pulsada
        var $btn = $(botonElemento);
        var $txt = $btn.find('.txt-btn-excel-fila');
        
        var token = new Date().getTime(); // Token único cronológico
        var segundos_transcurridos = 0;
        var limite_segundos_resguardo = 8; // Tiempo estimado para que Apache despache el .xls

        // Guardamos los HTML e íconos originales para restaurarlos al centavo
        var html_interno_origen = $txt.html();

        // 1. ESTADO CARGANDO LOCAL: Bloqueo de fila activa en tono naranja de procesamiento
        $btn.prop('disabled', true).css({
            'background-color': '#fef3c7',
            'border-color': '#fde68a',
            'cursor': 'not-allowed'
        });
        
        // Inyectamos el spinner vectorial mini adentro del botón de la fila
        $txt.html('<i class="fa fa-refresh fa-spin text-warning" style="font-size:14px;"></i>');

        // 2. Redirección para iniciar la descarga tradicional en el navegador
        window.location.href = base + "index.php/reportes_cns/exporting_datos/exportar_poa_uresponsable/" + id + "/" + token;

        // 3. MONITOR DE CONTROL HÍBRIDO (Bucle por Segundo)
        var checkDownload = setInterval(function() {
            segundos_transcurridos++;

            // A. Intento de lectura tradicional por si alguna pantalla sí inyecta la cookie
            var cookieValue = document.cookie.split('; ').find(function(row) {
                return row.trim().startsWith('downloadToken=');
            });
            
            // B. EVALUACIÓN DE COMPUERTA: Apaga el loader si se detecta la cookie o si se agota el tiempo de resguardo
            if ((cookieValue && cookieValue.split('=')[1] == token) || segundos_transcurridos >= limite_segundos_resguardo) {
                
                // 4. FINALIZAR LOADING Y RESTAURAR FILA INTACTA
                clearInterval(checkDownload);
                
                // Limpieza higiénica de la cookie de control si existiera
                document.cookie = "downloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; 
                
                // Devolvemos la prolijidad, colores e interacción original al botón de la hilera
                $btn.prop('disabled', false).css({
                    'background-color': '#f0fdf4',
                    'border-color': '#bbf7d0',
                    'cursor': 'pointer'
                });
                
                $txt.html(html_interno_origen); // Repone el ícono del Excel verde original
                
                if (typeof alertify !== "undefined" && segundos_transcurridos < limite_segundos_resguardo) {
                    alertify.success("✔ Reporte Excel descargado.");
                }
            }
        }, 1000);
    }
    //// -------------------------



var xhr_cambia_subactividad = null;

    // ==========================================================================
    // 14. CAMBIO EN CALIENTE DE TIPO DE SUBACTIVIDAD (DELEGACIÓN GLOBAL)
    // ==========================================================================
    $(document).on('change', '.select-subactividad-cns', function(e) {
        var $select = $(this);
        var com_id = $select.data('id'); // ID de la Unidad Organizacional
        var nuevo_tp_id = $select.val(); // Nuevo valor seleccionado
        
        // Almacenamos el valor previo por si el operador regional cancela el cambio
        var valor_previo = $select.data('previo') || $select.find('option[selected]').val();
        
        var url = base + "index.php/programacion/componente/cambia_tp_sact";

        // Feedback visual sutil: Pintamos un fondo amarillo de procesamiento
        $select.css('background-color', '#fef3c7');

        if (typeof alertify !== "undefined") {
            alertify.confirm("🚨 ¿ESTÁ SEGURO DE CAMBIAR EL TIPO DE SUBACTIVIDAD DE ESTA UNIDAD?", function(a) {
                if (a) {
                    $select.prop('disabled', true);

                    // Captura perimetral automática del Token CSRF de resguardo
                    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
                    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
                    var data_post = { com_id: com_id, tp_id: nuevo_tp_id };
                    if (csrf_name !== '') { data_post[csrf_name] = csrf_hash; }

                    if (xhr_cambia_subactividad && xhr_cambia_subactividad.readyState !== 4) {
                        xhr_cambia_subactividad.abort();
                    }

                    // Despachamos la ráfaga asíncrona hacia CodeIgniter
                    xhr_cambia_subactividad = $.ajax({
                        type: "POST",
                        url: url,
                        dataType: "json",
                        data: data_post,
                        success: function(res) {
                            $select.prop('disabled', false).css('background-color', '#ffffff');
                            
                            if (res.status === 'success' || res.respuesta === 'correcto') {
                                alertify.success("✔ " + (res.message || "Tipo de subactividad actualizado."));
                                // Seteamos el nuevo valor como el previo legítimo
                                $select.data('previo', nuevo_tp_id);
                            } else {
                                alertify.error("🚨 Rechazo: " + res.message);
                                $select.val(valor_previo); // Revertimos el control
                            }
                        },
                        error: function(xhr, textStatus) {
                            $select.prop('disabled', false).css('background-color', '#ffffff').val(valor_previo);
                            if (textStatus !== 'abort') {
                                alertify.error("❌ Falla de red. El servidor abortó la modificación.");
                            }
                        }
                    });
                } else {
                    // Si presionan cancelar, limpiamos los colores y reponemos el valor original
                    $select.css('background-color', '#ffffff').val(valor_previo);
                    alertify.log("Operación cancelada. El registro permanece intacto.");
                }
            });
        }
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


            /*----------- DESHABILITAR SUB ACTIVIDAD ---------------*/
            $(".neg_ff").on("click", function (e) {
                reset();
                var name = $(this).attr('name');
                var request;
                alertify.confirm("ESTA SEGURO EN DESHABILITAR?", function (a) {
                    if (a) { 
                        var url = base+"index.php/programacion/componente/deshabilitar_sactividad";
                        if (request) {
                            request.abort();
                        }
                        request = $.ajax({
                            url: url,
                            type: "POST",
                            dataType: "json",
                          data: "com_id="+name

                        });

                        request.done(function (response, textStatus, jqXHR) { 
                          reset();
                          if (response.respuesta == 'correcto') {
                              alertify.alert("LAS SUB ACTIVIDAD SE DESHABILITO CORRECTAMENTE ", function (e) {
                                  if (e) {
                                      window.location.reload(true);
                                  }
                              });
                          } else {
                              alertify.alert("ERROR AL DESHABILITAR !!!", function (e) {
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

////-----------------

    $("#subir_form").on("click", function () {
        var $validator = $("#form_nuevo").validate({
                rules: {
                    serv_id: { //// unidad
                    required: true,
                    },
                    descripcion: { //// descripcion Componente
                        required: true,
                    }
                },
                messages: {
                    serv_id: "<font color=red>SELECCIONE UNIDAD</font>", 
                    descripcion: "<font color=red>REGISTRE DESCRIPCIÓN DEL COMPONENTE</font>",                     
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

        var $valid = $("#form_nuevo").valid();
        if (!$valid) {
            $validator.focusInvalid();
        } else {

                alertify.confirm("GUARDAR COMPONENTE ?", function (a) {
                    if (a) {
                        document.getElementById("load").style.display = 'block';
                        document.getElementById('subir_form').disabled = true;
                        document.forms['form_nuevo'].submit();
                    } else {
                        alertify.error("OPCI\u00D3N CANCELADA");
                    }
                });
        }
    });


    $(".mod_ff").on("click", function (e) {
            com_id = $(this).attr('name');
            var url = base+"index.php/programacion/componente/get_componente";
            var request;
            if (request) {
                request.abort();
            }
            request = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: "com_id=" + com_id
            });

            request.done(function (response, textStatus, jqXHR) {
            if (response.respuesta == 'correcto') {
                document.getElementById("com_id").value = response.componente[0]['com_id'];
                document.getElementById("mserv_id").value = response.componente[0]['serv_id'];
                document.getElementById("mcomponente").value = response.componente[0]['com_componente'];
            }
            else{
                alertify.error("ERROR AL RECUPERAR DATOS DEL COMPONENTE");
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
            $("#mod_ffenviar").on("click", function (e) {
                var $validator = $("#form_mod").validate({
                       rules: {
                        com_id: { //// com
                        required: true,
                        },
                        mserv_id: { //// codigo
                            required: true,
                        },
                        mcomponente: { //// descripcion
                            required: true,
                        }
                    },
                    messages: {
                        com_id: "<font color=red>COMPONENTE ID</font>",
                        mser_id: "<font color=red>UNIDAD RESPONSABLE</font>",
                        mcomponente: "<font color=red>REGISTRE COMPONENTE</font>",                     
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

                    alertify.confirm("MODIFICAR DATOS UNIDAD RESPONSABLE ?", function (a) {
                        if (a) {
                            document.getElementById("loadd").style.display = 'block';
                            document.getElementById('mod_ffenviar').disabled = true;
                            document.forms['form_mod'].submit();
                        } else {
                            alertify.error("OPCI\u00D3N CANCELADA");
                        }
                    });

                }
            });
        });
