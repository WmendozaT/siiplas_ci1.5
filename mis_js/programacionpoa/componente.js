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

    /////------------------- Get Partidas programadas x Unidad Organizacional
    $(document).on('click', '[data-target="#modal_techos_resumen_global"]', function(e) {
        e.preventDefault();

        var $btn = $(this);
        var $target_body = $('#contenedor_techo_dinamico_cns');

        // 🌟 1. Evitamos que se ejecute la carga relacional si el clic provino de un botón de fila individual
        // Ya que este listener solo debe reaccionar cuando pulsan el botón Máster Superior
        if ($btn.hasClass('btn-ver-presupuesto')) {
            return; // Cede el control a la función 15 de hileras individuales
        }

        // 🌟 2. INYECCIÓN DE PRELOADER VECTORIAL COPORATIVO MIENTRAS RESPONDE POSTGRESQL
        $target_body.html(
            '<div style="text-align:center; padding: 40px 20px; color: #475569; font-weight: bold;">' +
                '<i class="fa fa-refresh fa-spin fa-2x text-primary" style="margin-bottom:12px; display:block;"></i>' +
                'Compilando Techos de Gasto de la Unidad Organizacional...' +
            '</div>' +
            '<style>' +
                '@keyframes spin_f5_mod { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
            '</style>'
        );

        // 🌟 3. LEER VARIABLES GLOBALES DEL DOM (Asegúrate de que en tu vista exista un input hidden con name="proy_id" o id="proy_id")
        var proy_id_global = $('#proy_id').val() || $('[name="proy_id"]').val() || '';

        if (proy_id_global === "") {
            $target_body.html('<div class="alert alert-danger text-center" style="font-weight:bold;"><i class="fa fa-times-circle"></i> Error SIIPLAS: Identificador del Proyecto no detectado en el DOM.</div>');
            return false;
        }

        var url = base + "index.php/programacion/crequerimiento/get_resumen_techo_proyecto_global_ajax";
        
        // Captura automática de Token CSRF perimetral de la CNS para evitar bloqueos perimetrales
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        // 🌟 4. EJECUCIÓN DE LA RÁFAGA AJAX HACIA EL COMPILADOR MÁSTER
        $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: "proy_id=" + proy_id_global + token_seguridad,
            success: function(res) {
                if (res.status === 'success' || res.respuesta === 'correcto') {
                    
                    // Formamos el encabezado descriptivo del Proyecto POA consolidado
                    var html_header = `
                        <div style="background: #eff6ff; border-left: 4px solid #2563eb; padding: 12px; margin-bottom: 20px; border-radius: 4px;">
                            <span style="display:block; font-size:10.5px; font-weight:bold; color:#1e40af; text-transform:uppercase; letter-spacing:0.3px;"><i class="fa fa-folder-open"></i> Resumen Consolidado por Partidas:</span>
                            <strong style="color:#0f172a; font-size:13px; display:block; margin-top:2px;">${res.proy_nombre}</strong>
                        </div>
                    `;
                    
                    // Estampamos el bloque completo de la tabla financiera desvaneciendo suavemente el loading
                    $target_body.hide().html(html_header + res.html_reporte).fadeIn(250);

                } else {
                    $target_body.html('<div class="alert alert-danger" style="font-weight:bold;"><i class="fa fa-times-circle"></i> ' + res.message + '</div>');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error("CNS ERROR GET GLOBAL CEIL -> " + textStatus + " | " + errorThrown);
                $target_body.html('<div class="alert alert-danger" style="font-weight:bold;"><i class="fa fa-exclamation-triangle"></i> Falla crítica de red al conectar con el servidor de presupuestos centralizados.</div>');
            }
        });
    });

    function imprimirTechosModal() {
        // Capturamos el HTML del cuadro y los títulos institucionales cargados en el modal
        var contenido_tabla = document.getElementById("area_impresion_techos_f5").innerHTML;
        var info_proyecto = $(".modal-body div[style*='border-left']").html() || "RESUMEN DE TECHOS PRESUPUESTARIOS POA";

        // Creamos una ventana en memoria flotante temporal
        var ventana_impresion = window.open('', '_blank', 'height=700,width=1100');

        ventana_impresion.document.write('<html><head><title>Sistema de Planificación de Salud - SIIPLAS V2.0</title>');
        
        // Inyectamos estilos de impresión limpios estilo reporte oficial de la CNS
        ventana_impresion.document.write('<style>');
        
        // 🌟 REPARADO: Fuerza al cuadro de diálogo a pre-configurarse en Carta Horizontal con márgenes optimizados
        ventana_impresion.document.write('@page { size: letter landscape; margin: 8mm 6mm 8mm 6mm; }');
        
        // Mantenemos tu tipografía y colores base intactos en la pantalla intermedia
        ventana_impresion.document.write('body { font-family: Arial, sans-serif; padding: 20px; color: #000; background:#fff; }');
        ventana_impresion.document.write('.header-print { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; }');
        
        // 🌟 MANTENIDO: El tamaño original de fuente (10px) solicitado para no alterar la visualización
        ventana_impresion.document.write('table { width: 100% !important; border-collapse: collapse; font-size: 10px; margin-top: 10px; table-layout: auto; page-break-inside: auto; }');
        ventana_impresion.document.write('tr { page-break-inside: avoid; page-break-after: auto; }');
        ventana_impresion.document.write('th, td { border: 1px solid #000; padding: 5px; text-align: right; word-wrap: break-word; }');
        ventana_impresion.document.write('th { background: #e2e8f0 !important; color: #000 !important; text-align: center; font-weight: bold; }');
        ventana_impresion.document.write('td:first-child, th:first-child { text-align: left; }');
        
        // 🌟 REPARADO CORE: El secreto de ingeniería para ajustar N columnas a la hoja respetando el font-size de 10px
        // Al imprimir, encogemos proporcionalmente un 15% el renderizado para que calce exacto en el ancho Letter Horizontal
        ventana_impresion.document.write('@media print { body { zoom: 85%; -webkit-print-color-adjust: exact; print-color-adjust: exact; } table { width: 100% !important; } }');
        
        ventana_impresion.document.write('<' + '/style></head><body>'); // Separación preventiva de etiqueta de cierre script
        
        // Estampamos el membrete corporativo institucional de la Caja Nacional de Salud
        ventana_impresion.document.write('<div class="header-print">');
        ventana_impresion.document.write('<h3 style="margin:0; text-transform:uppercase;">Caja Nacional de Salud</h3>');
        ventana_impresion.document.write('<small style="color:#475569;">SIIPLAS v2.0 - Departamento Nacional de Planificación</small>');
        ventana_impresion.document.write('<div style="margin-top:10px; font-size:10px;">' + info_proyecto + '</div>');
        ventana_impresion.document.write('</div>');
        
        // Inyectamos la estructura del listado de techos extraído en caliente desde el DOM
        ventana_impresion.document.write(contenido_tabla);
        ventana_impresion.document.write('</body></html>');

        ventana_impresion.document.close();
        ventana_impresion.focus();
        
        // Pausa técnica de 400ms para asegurar que el navegador asimile la escala y el tamaño carta horizontal
        setTimeout(function() {
            ventana_impresion.print();
            ventana_impresion.close();
        }, 400);
    }

    /**
     * 📊 MÓDULO B: EXPORTACIÓN FLASH A EXCEL DESDE EL DOM
     * Transforma el árbol HTML renderizado en un archivo descargable .xls en microsegundos sin tocar la BD.
     */
    function exportarExcelModalDirecto() {
        var tabla_html = document.getElementById("tabla_techos_reporte_cns");
        
        if (!tabla_html) {
            if (typeof alertify !== "undefined") alertify.error("No se encontró la tabla de datos para exportar.");
            return;
        }

        var html_crudo = tabla_html.outerHTML;
        
        // Aplicamos un formateo básico de soporte de tildes y caracteres en español para Excel de escritorio
        var plantilla_excel = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://w3.org">';
        plantilla_excel += '<head><meta charset="utf-8"/><style>table, th, td { border: 0.5pt solid #cbd5e1; font-family: Arial; font-size: 10pt; }</style></head><body>';
        plantilla_excel += html_crudo;
        plantilla_excel += '</body></html>';

        // Generamos el Blob binario de descarga directa
        var blob = new Blob([plantilla_excel], { type: "application/vnd.ms-excel;charset=utf-8" });
        var url_descarga = URL.createObjectURL(blob);
        
        var disparador_link = document.createElement("a");
        disparador_link.href = url_descarga;
        
        // Seteamos el nombre del reporte adjuntando la marca de tiempo para evitar solapamientos
        disparador_link.download = "SIIPLAS_Reporte_Techos_Consolidado_" + new Date().getTime() + ".xls";
        
        document.body.appendChild(disparador_link);
        disparador_link.click();
        document.body.removeChild(disparador_link);
    }


    