var base = $('[name="base"]').val();
var prod_id = $('[name="prod_id"]').val();

///// Subir archivo de Migracion de Requerimientos por Actividad
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
 //// -----------------------------------------------------------------

  ////// ===== MODIFICAR REUQERIMIENTO
    $(function () {
        $(".mod_ff").on("click", function (e) {
            ins_id = $(this).attr('name');
            document.getElementById("ins_id").value=ins_id;

            var url = base+"index.php/programacion/crequerimiento/get_requerimiento";
            var request;
            if (request) {
                request.abort();
            }
            request = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: "ins_id="+ins_id
            });

            request.done(function (response, textStatus, jqXHR) {
            if (response.respuesta == 'correcto') {
               document.getElementById("saldo").value = parseFloat(response.monto_saldo).toFixed(2);
               document.getElementById("sal").value = parseFloat(response.monto_saldo).toFixed(2);
               document.getElementById("detalle").value = response.insumo[0]['ins_detalle'];
               document.getElementById("cantidad").value = response.insumo[0]['ins_cant_requerida'];
               document.getElementById("costou").value = parseFloat(response.insumo[0]['ins_costo_unitario']).toFixed(2);
               document.getElementById("costot").value = parseFloat(response.insumo[0]['ins_costo_total']).toFixed(2);
               document.getElementById("costot2").value = parseFloat(response.insumo[0]['ins_costo_total']).toFixed(2);
               document.getElementById("par_padre").value = response.ppdre[0]['par_codigo'];
               $("#par_hijo").html(response.lista_partidas);
               document.getElementById("iumedida").value = response.insumo[0]['ins_unidad_medida'];
               //$("#mum_id").html(response.lista_umedida);
               document.getElementById("mtot").value = response.prog[0];
               document.getElementById("observacion").value = response.insumo[0]['ins_observacion'];
               //$('#ff').html('FUENTE DE FINANCIAMIENTO : '+response.prog[0]['ff_codigo']+' || ORGANISMO FINANCIADOR : '+response.prog[0]['of_codigo']);
               if(response.prog[0]!=response.insumo[0]['ins_costo_total']){
                $('#amtit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO</div></center>');
                $('#mbut').slideUp();
               }

               for (var i = 1; i <=12; i++) {
                document.getElementById("mm"+i).value = response.prog[i];
               }
               
            }
            else{
                alertify.error("ERROR AL RECUPERAR DATOS DEL REQUERIMIENTO");
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
            $("#subir_mins").on("click", function (e) {
                var $validator = $("#form_mod").validate({
                       rules: {
                        ins_id: { //// Insumo
                        required: true,
                        },
                        proy_id: { //// Proyecto
                            required: true,
                        },
                        detalle: { //// Detalle
                            required: true,
                        },
                        cantidad: { //// Cantidad
                            required: true,
                        },
                        costou: { //// Costo U
                            required: true,
                        },
                        costot: { //// costo tot
                            required: true,
                        },
                        mum_id: { //// unidad medida
                            required: true,
                        },
                        par_padre: { //// par padre
                            required: true,
                        },
                        par_hijo: { //// par hijo
                            required: true,
                        }
                    },
                    messages: {
                        ins_id: "<font color=red>INSUMO/font>",
                        detalle: "<font color=red>REGISTRE DETALLE DEL REQUERIMIENTO</font>", 
                        cantidad: "<font color=red>CANTIDAD</font>",
                        costou: "<font color=red>COSTO UNITARIO</font>",
                        costot: "<font color=red>COSTO TOTAL</font>",
                        mum_id: "<font color=red>SELECCIONE UNIDAD DE MEDIDA</font>",
                        par_padre: "<font color=red>SELECCIONE GRUPO DE PARTIDAS</font>",
                        par_hijo: "<font color=red>SELECCIONE PARTIDA</font>",                     
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
                  saldo=document.getElementById("sal").value;
                  programado=document.getElementById("mtot").value;
                  dif=saldo-programado;
            
                $('#amtit').html('');
                    alertify.confirm("MODIFICAR REQUERIMIENTO ?", function (a) {
                        if (a) {
                          document.getElementById("loadm").style.display = 'block';
                            document.getElementById('subir_mins').disabled = true;
                            document.getElementById("subir_mins").value = "MODIFICANDO DATOS REQUERIMIENTO...";
                            document.forms['form_mod'].submit();
                        } else {
                            alertify.error("OPCI\u00D3N CANCELADA");
                        }
                    });
                }
            });
        });
    });
