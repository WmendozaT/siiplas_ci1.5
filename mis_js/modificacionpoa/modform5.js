base = $('[name="base"]').val();
prod_id = $('[name="prod_id"]').val();
proy_id = $('[name="proy_id"]').val();
aper_id = $('[name="aper_id"]').val();
cite_id = $('[name="cite_id"]').val();


/*  function abreVentana(PDF){             
    var direccion;
    direccion = '' + PDF;
    window.open(direccion, "REPORTE FORMULARIO N° 5" , "width=800,height=700,scrollbars=NO") ; 
  }*/

  function abreVentana_comparativo(PDF){             
    var direccion;
    direccion = '' + PDF;
    window.open(direccion, "Cuadro Comparativo" , "width=700,height=600,scrollbars=NO") ; 
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
  $("div.toolbar").html('');
  // Apply the filter
  $("#datatable_fixed_column thead th input[type=text]").on( 'keyup change', function () {
      otable
          .column( $(this).parent().index()+':visible' )
          .search( this.value )
          .draw();   
  } );
  /* END COLUMN FILTER */   
})

  //// Para eliminar los items seleccionados del formulario de modificacion poa
  $(document).on('change', '.check-insumo', function() {
    var totalSeleccionados = $('.check-insumo:checked').length;
    $('[name="tot"]').val(totalSeleccionados);
  });
  //// ------------------


  ////------------  PARA MIGRAR ARCHIVO EN EXCEL 2027 ==========2027
  var request_mod_f5 = null;
var xhr_guardar_f5 = null; 

/// Subir Requerimientos Global
$(document).on('click', '#btn_subir_f5', function(e) {
    e.preventDefault();
    $('#mensaje_f5').html(''); 

    // Validación preventiva en el cliente antes de consumir canal de red
    if ($('#archivo_f5').val() == '') {
        $('#mensaje_f5').html('<div class="alert alert-warning" style="margin-bottom:0; font-size:12px;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>');
        if (typeof alertify !== "undefined") {
            alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .XLSX");
        }
        return false;
    }

    var form = $('#form_subir_requerimientoss')[0];
    var data_multipart = new FormData(form);
    var $btn = $(this);

    // Bloquear interfaz de usuario (UI) e inyectar cargador animado institucional (Loader)
    // 🛠️ AJUSTE: Mantiene consistencia visual con el botón original de tu modal
    $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ EN SERVIDOR...');
    $('#loads_f5').show();

    // Captura perimetral automática del Token CSRF por si está activo en la CNS
    var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
    var csrf_hash = $('[name="csrf_test_name"]').val() || '';
    if (csrf_name !== '') {
        data_multipart.append(csrf_name, csrf_hash);
    }

    $.ajax({
        type: "POST",
        url: $('#form_subir_requerimientoss').attr('action'),
        data: data_multipart,
        processData: false,
        contentType: false,
        success: function(response) {
            var res;
            try {
                res = (typeof response === 'object') ? response : JSON.parse(response);
            } catch (err) {
                console.error("Error parseando JSON:", response);
                // 🛠️ CORREGIDO: Cambiado #mensaje por #mensaje_f5 para evitar colgar la interfaz
                $('#mensaje_f5').html('<div class="alert alert-danger" style="margin-bottom:0; font-size:12px;"><b>❌ Error de Transacción:</b> La respuesta del servidor devolvió un buffer de texto corrupto o PHP agotó su memoria.</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                $('#loads_f5').hide();
                return;
            }

            // Evalúa el éxito transaccional unificado
            if (res.status === 'success' || res.respuesta === 'correcto') {
                var mensaje_exito = res.message || res.mensaje || "Registros migrados exitosamente.";
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
                        <p style="margin:0;"><small class="text-muted">Requerimientos y presupuesto mensualizado distribuidos correctamente.</small></p>
                    </div>`;

                $('#mensaje_f5').html(html_success);
                $('#loads_f5').hide();
                $btn.hide(); 

                if (typeof alertify !== "undefined") {
                    alertify.success("✔ Plantilla procesada correctamente.");
                }

                // Temporizador inteligente para limpiar el modal y recargar grilla activa
                setTimeout(function() {
                    $('#modal_importar').modal("hide");
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css('padding-right', '');

                    if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
                         recargar_listado_requerimientos_cns_ajax();
                     } else {
                        location.reload(); 
                     }
                }, 2500);

            } else {
                // MÓDULO DE EXTRACTOS DE ERRORES DE CONSISTENCIA DE CELDAS
                var mensaje_error = res.message || res.mensaje || "El archivo contiene celdas o tipados inválidos.";
                var errorMsg = '<strong style="font-size:12px; color:#b91c1c;"><i class="fa fa-times-circle"></i> SE DETECTARON INCONSISTENCIAS EN LA PLANILLA EXCEL:</strong><br><small class="text-muted">' + mensaje_error + '</small>';
                
                if (res.errors || res.errores) {
                    var coleccion_errores = res.errors || res.errores;
                    // 🛠️ MEJORA: Añadido max-height y scroll para que los errores de celdas no deformen el modal
                    errorMsg += "<div style='max-height:180px; overflow-y:auto; margin-top:8px;'><ul style='padding-left:15px; text-align:left; font-size:11px; margin-bottom:0;'>";
                    $.each(coleccion_errores, function(index, value) {
                        errorMsg += "<li style='margin-bottom:3px;'>" + value + "</li>";
                    });
                    errorMsg += "</ul></div>";
                }
                
                $('#mensaje_f5').html('<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">' + errorMsg + '</div>');
                $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                $('#loads_f5').hide();
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.error("Falla Crítica en canal de carga masiva de Excel. Detalle:", xhr.responseText);
            $('#loads_f5').hide();
            $btn.prop('disabled', false).html('<i class="fa fa-check-circle"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
            
            var txt_err = "❌ Error crítico de red (" + xhr.status + "): Imposible comunicar con el cargador de productos.";
            $('#mensaje_f5').html('<div class="alert alert-danger" style="margin-bottom:0; font-size:12px;">' + txt_err + '</div>');
            
            if (typeof alertify !== "undefined") {
                alertify.error("Falla de red en el servidor.");
            }
        }
    });
});




// var xhr_migracion_sigep = null;

// $(document).ready(function() {
    
//     // Mostrar nombre del archivo al seleccionar en el input formalizado
//     $(document).on('change', '#archivo', function() {
//         var fileName = $(this).val().split('\\').pop();
//         if (fileName) {
//             $('.file-name-display').val(fileName); 
//         }
//     });

//     /* ==========================================================================
//        📥 MÓDULO CNS: IMPORTADOR Y PROCESADOR DE PLANILLAS DE ALTA VELOCIDAD
//        ========================================================================== */
//     $(document).on('click', '#btn_subir', function(e) {
//         e.preventDefault();
        
//         var $btn = $(this);
//         $('#mensaje').html(''); 

//         // Candado perimetral local: Validación de búfer vacío
//         if ($('#archivo').val() == '') {
//             $('#mensaje').html(
//                 '<div class="alert alert-warning" style="display:flex; align-items:center; gap:8px; margin-bottom:0;">' +
//                     '<i class="fa fa-exclamation-triangle" style="font-size:14px;"></i>' +
//                     '<span style="font-weight:600; font-size:11.5px;">Restricción: Por favor, seleccione una planilla oficial estructurada (.xls o .xlsx).</span>' +
//                 '</div>'
//             );
//             if (typeof alertify !== "undefined") alertify.error("⚠️ Operación cancelada: Ningún archivo seleccionado.");
//             return false;
//         }

//         var formElement = $('#form_subir_sigep')[0];
//         var data_multipart = new FormData(formElement);

//         // Bloquear UI e inyectar estados de procesamiento para mitigar dobles clics
//         $btn.prop('disabled', true).html('<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA EN POSTGRES...');
//         $('#loads').css('display', 'flex'); // Ajustado a flex para la simetría del preloader vectorial

//         // Captura perimetral automática del Token CSRF activo para el blindaje de la CNS
//         var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
//         var csrf_hash = $('[name="csrf_test_name"]').val() || '';
//         if (csrf_name !== '') {
//             data_multipart.append(csrf_name, csrf_hash);
//         }

//         // Cancelamos hilos de red huérfanos simultáneos previos en cola para liberar RAM de Apache
//         if (xhr_migracion_sigep && xhr_migracion_sigep.readyState !== 4) {
//             xhr_migracion_sigep.abort();
//         }

//         // Despacho de la ráfaga AJAX hacia tu controlador CodeIgniter
//         xhr_migracion_sigep = $.ajax({
//             type: "POST",
//             url: $('#form_subir_sigep').attr('action'),
//             data: data_multipart,
//             processData: false, // Impide que jQuery intente transformar las cadenas binarias en texto plano
//             contentType: false, // Forza a Apache a interpretar las cabeceras como multipart/form-data
            
//             // 🌟 REPARADO CORE: Desactiva el límite de tiempo de espera del navegador (Ilimitado)
//             // Esto obliga al explorador a aguardar la respuesta del lector PHP de más de 500 filas
//             timeout: 0, 

//             success: function(response) {
//                 var res;
//                 try {
//                     res = (typeof response === 'object') ? response : JSON.parse(response);
//                 } catch (err) {
//                     console.error("Error parseando JSON:", response);
//                     $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0; font-size:11.5px;"><b>❌ Error de Transacción:</b> La respuesta devolvió un búfer de texto corrupto. Verifique que no se hayan filtrado Warnings de PHP en el controlador.</div>');
//                     $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
//                     $('#loads').hide();
//                     return;
//                 }

//                 // Evalúa el éxito transaccional unificado para el SIIPLAS v2.0
//                 if (res.status === 'success' || res.respuesta === 'correcto') {
//                     var mensaje_exito = res.msj || res.mensaje || "Planilla procesada correctamente.";
//                     var conteo_filas  = res.conteo || res.filas_procesadas || "0";

//                     // Construcción geométrica del banner de auditoría aprobada
//                     var html_success = `
//                         <div class="alert alert-success text-center" style="border-left: 5px solid #16a34a; background:#f0fdf4; color:#16a34a; padding:15px; margin-bottom:0;">
//                             <i class="fa fa-check-circle fa-2x" style="margin-bottom:8px; display:block;"></i>
//                             <h4 style="font-weight:bold; margin:0 0 5px 0; color:#15803d; font-size:13.5px;">¡PROCESO COMPLETADA CON ÉXITO!</h4>
//                             <p style="font-size: 12px; color:#166534; font-weight:500; margin:5px 0;">${mensaje_exito}</p>
//                             <div style="margin: 10px 0;">
//                                 <span class="label label-success" style="font-size: 15px; padding: 4px 12px; font-weight:bold; background:#16a34a; border-radius:3px;">Registros: ${conteo_filas}</span>
//                             </div>
//                             <p style="margin:0;"><small class="text-muted" style="font-size:10.5px;">Requerimientos validados e inyectados en la base de datos de productos.</small></p>
//                         </div>`;

//                     $('#mensaje').html(html_success);
//                     $('#loads').hide();
//                     $btn.hide(); 

//                     if (typeof alertify !== "undefined") {
//                         alertify.success("✔ Plantilla procesada correctamente.");
//                     }

//                     // Temporizador inteligente para refrescar la grilla activa de la CNS
//                     setTimeout(function() {
//                         $('#modal_importar').modal("hide");
//                         $('.modal-backdrop').remove();
//                         $('body').removeClass('modal-open');
                        
//                         // Si existe tu función reactiva en caliente de recarga AJAX, la llamamos, sino reload
//                         if (typeof recargar_listado_requerimientos_cns_ajax === "function") {
//                             recargar_listado_requerimientos_cns_ajax();
//                         } else {
//                             location.reload(); 
//                         }
//                     }, 2500);

//                 } else {
//                     // MÓDULO DE EXTRACTOS DE ERRORES DE CONSISTENCIA DE CELDAS
//                     var mensaje_error = res.mensaje || res.msj || "El archivo contiene celdas o tipados inválidos.";
//                     var errorMsg = '<strong style="font-size:11.5px; color:#b91c1c;"><i class="fa fa-times-circle"></i> SE DETECTARON INCONSISTENCIAS EN LA PLANILLA EXCEL:</strong><br><small class="text-muted" style="font-size:11px;">' + mensaje_error + '</small>';
                    
//                     if (res.errors || res.errores) {
//                         var coleccion_errores = res.errors || res.errores;
//                         errorMsg += "<ul style='margin-top:6px; padding-left:15px; text-align:left; font-size:11px; max-height:120px; overflow-y:auto;'>";
//                         $.each(coleccion_errores, function(index, value) {
//                             errorMsg += "<li style='margin-bottom:2px; font-weight:500;'>" + value + "</li>";
//                         });
//                         errorMsg += "</ul>";
//                     }
                    
//                     $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b; padding:12px;">' + errorMsg + '</div>');
//                     $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> VALIDAR Y SUBIR ARCHIVO EXCEL');
//                     $('#loads').hide();
//                 }
//             },
//             error: function(xhr, textStatus, errorThrown) {
//                 if (textStatus === 'abort') return;
//                 console.error("Falla Crítica en canal de carga masiva de Excel. Detalle:", xhr.responseText);
//                 $('#loads').hide();
//                 $btn.prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA');
                
//                 // Mensaje semántico contextualizado según el tipo de rebote de Apache
//                 var mensaje_diagnostico = "Error crítico de hardware: No se pudo procesar el archivo masivo en el servidor.";
//                 if(xhr.status === 504 || textStatus === "timeout") {
//                     mensaje_diagnostico = "❌ Error 504 (Gateway Timeout): El servidor Apache interrumpió el proceso porque la planilla excede los límites de tiempo. Aumente el 'max_execution_time' en el php.ini.";
//                 } else if(xhr.status === 500) {
//                     mensaje_diagnostico = "❌ Error 500 (Internal Server Error): El lector de Excel agotó la memoria RAM asignada al script. Aumente el 'memory_limit' en su controlador.";
//                 }
                
//                 $('#mensaje').html('<div class="alert alert-danger" style="margin-bottom:0; font-size:11.5px; font-weight:500; padding:12px;">' + mensaje_diagnostico + '</div>');
                
//                 if (typeof alertify !== "undefined") {
//                     alertify.error("Falla de red en el servidor.");
//                 }
//             }
//         });
//     });
// });
  ////-----------------------------------------



///// subir archivos anterior
// $(function () {
//     //SUBIR ARCHIVO
//     $("#subir_archivo").on("click", function () {
//       var $valid = $("#form_subir_sigep").valid();
//       if (!$valid) {
//           $validator.focusInvalid();
//       } else {
//         if(document.getElementById('archivo').value==''){
//           alertify.alert('POR FAVOR SELECCIONE ARCHIVO .CSV');
//           return false;
//         }
//           alertify.confirm("SUBIR ARCHIVO REQUERIMIENTOS.CSV?", function (a) {
//               if (a) {
//                   document.getElementById("subir_archivo").value = "AGREGANDO REQUERIMIENTOS...";
//                   document.getElementById("loads").style.display = 'block';
//                   document.getElementById('subir_archivo').disabled = true;
//                   document.forms['form_subir_sigep'].submit();
//               } else {
//                   alertify.error("OPCI\u00D3N CANCELADA");
//               }
//           });
//       }
//     });
//   });







  function justNumbers(e){
      var keynum = window.event ? window.event.keyCode : e.which;
      if ((keynum == 8) || (keynum == 46))
      return true;
       
      return /\d/.test(String.fromCharCode(keynum));
  }

  //// ELIMINA REQUERIMIENTOS SELECCIONADOS del listado 2026
function valida_eliminar(){
  if(document.del_req.tot.value!=0){
    alertify.confirm("ESTA SEGURO DE ELIMINAR "+document.del_req.tot.value+" REQUERIMIENTOS ?", function (a) {
      if (a) {
        $("#loading-overlay").css("display", "flex");
        $(".loader-content h2").text("ELIMINANDO REQUERIMIENTOS SELECCIONADOS...");


          document.getElementById("btsubmit").value = "ELIMINANDO REQUERIMIENTOS...";
          document.getElementById("btsubmit").disabled = true;
          document.del_req.submit();
          return true;
      } else {
          alertify.error("OPCI\u00D3N CANCELADA");
      }
     });
  }
  else{
    alertify.error("SELECCIONE REQUERIMIENTOS A ELIMINAR !!! ");
  }
}


    /// asignar unidad responsable para Bienes y Servicios 2022
    function doSelectAlert(event,com_id,ins_id) {
     //  alert(event+'--'+com_id+'--'+ins_id)
      var url = base+"index.php/modificaciones/cmod_insumo/asignar_uresponsable";
        $.ajax({
            type: "post",
            url: url,
            data:{com_id:com_id,ins_id:ins_id},
                success: function (data) {
                alertify.success('Asignado');  
                //window.location.reload(true);
            }
        });
    }


 //// Cerrar Modificacion POA (Requerimientos)
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
          alertify.confirm("CERRAR MODIFICACIÓN FINANCIERA ?", function (a) {
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



/*$(function () {
  $(".comparativo").on("click", function (e) {
    proy_id = $(this).attr('name');
    establecimiento = $(this).attr('id');
    
    $('#titulo').html('<font size=3><b>'+establecimiento+'</b></font>');
    $('#cuadro_comparativo').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Cuadro Comparativo Presupuestario - <br>'+establecimiento+'</div>');
    
    var url = base+"index.php/modificaciones/cmod_insumo/get_comparativo_ptto";
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
        $('#cuadro_comparativo').fadeIn(1000).html(response.tabla);
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


    /*---- BOTON PARA CARGAR EL CUADRO COMPARATIVO POR PARTIDAS ----*/
   $(function () {
    // Definimos request fuera para que el .abort() funcione realmente entre clics
    var request;

    $(".boton_cuadro_comparativo").on("click", function (e) {
        e.preventDefault(); // Siempre al inicio

        var cite_id = $('[name="cite_id"]').val();
        
        // 1. Validar que exista un ID seleccionado
        if (!cite_id) {
            alertify.warning("Por favor, seleccione un CITE.");
            return;
        }

        // 2. Mostrar loader y ocultar botón
        $('#partidas').html('<div class="loadin" align="center"><br><br><img src="'+base+'/assets/img/cargando-loading-039.gif" alt="loading" style="width:50%;""")/>></div>');
        $("#boton_comparativo").hide(); // Más simple que .style.display = 'none'

        // 3. Abortar petición previa si el usuario hace clic rápido varias veces
        if (request) {
            request.abort();
        }

        // 4. Petición AJAX
        request = $.ajax({
            url: base + "index.php/modificaciones/cmod_insumo/get_cuadro_comparativo_ptto",
            type: "POST",
            dataType: 'json',
            // Es mejor enviar un objeto que un string manual
            data: { cite_id: cite_id } 
        });

        request.done(function (response) {
            if (response.respuesta === 'correcto') {
                // Insertamos el iframe y lo mostramos con efecto
                $('#partidas').hide().html(response.tabla).fadeIn(1000);
            } else {
                alertify.error(response.mensaje || "ERROR AL RECUPERAR DATOS");
                $("#boton_comparativo").show(); // Reaparece el botón si falló
            }
        });

        request.fail(function (jqXHR, textStatus) {
            if (textStatus !== 'abort') {
                console.error("Error en la petición: " + textStatus);
                alertify.error("Error de conexión al servidor");
            }
        });
    });
  });




  //// VER LISTA DE CERTIFICACIONES POA POR ITEMS
  $(function () {
    $(".certpoas").on("click", function (e) {
        ins_id = $(this).attr('name');

      var url = base+"index.php/ejecucion/ccertificacion_poa/get_lista_certificaciones_por_items";
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
        $("#cpoas").html(response.lista);
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
       
    });
  });



 //// VALIDA NUEVO REGISTRO DE REQUERIMIENTOS POA 2026
  $(function () {
    $("#subir_ins").on("click", function () {
        var $validator = $("#form_nuevo").validate({
            rules: {
                cite_id: { required: true },
                ins_detalle: { required: true },
                ins_cantidad: { required: true },
                ins_costo_u: { required: true },
                costo: { required: true },
                ins_um: { required: true },
                padre: { required: true },
                partida_id: { required: true },
                dato_id: { required: true }
            },
            messages: {
                ins_detalle: "<font color=red>REGISTRE DETALLE DEL REQUERIMIENTO</font>",
                ins_cantidad: "<font color=red>CANTIDAD</font>",
                ins_costo_u: "<font color=red>COSTO UNITARIO</font>",
                costo: "<font color=red>COSTO TOTAL</font>",
                ins_um: "<font color=red>REGISTRE UNIDAD DE MEDIDA</font>",
                padre: "<font color=red>SELECCIONE GRUPO DE PARTIDAS</font>",
                partida_id: "<font color=red>SELECCIONE PARTIDA</font>",
                dato_id: "<font color=red>ACTIVIDAD</font>",
            },
            highlight: function (element) {
                $(element).closest('section').removeClass('has-success').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('section').removeClass('has-error').addClass('has-success');
            },
            errorElement: 'span',
            errorClass: 'help-block',
            errorPlacement: function (error, element) {
                error.insertAfter(element.parent());
            }
        });

        var $valid = $("#form_nuevo").valid();
        if (!$valid) {
            $validator.focusInvalid();
        } else {
            // CAPTURA DE VALORES PARA VALIDACIÓN MONETARIA
            var saldo = parseFloat($("#saldo").val()) || 0;
            var costo_total = parseFloat($('[name="costo"]').val()) || 0;
            var programado = parseFloat($("#tot").val()) || 0;

            // 1. Validar Saldo de Partida
            if (costo_total > saldo) {
                $('#atit').html('<div class="alert alert-danger text-center"><b>ERROR:</b> EL COSTO TOTAL (' + costo_total.toFixed(2) + ') SUPERA EL SALDO DISPONIBLE (' + saldo.toFixed(2) + ')</div>');
                alertify.error("EL MONTO SUPERA EL SALDO DE LA PARTIDA");
                return false;
            }

            // 2. Validar Coincidencia con Programación Mensual (Temporalidad)
            // Usamos toFixed(2) para evitar problemas con decimales flotantes
            if (costo_total.toFixed(2) !== programado.toFixed(2)) {
                $('#atit').html('<div class="alert alert-danger text-center"><b>ERROR:</b> EL MONTO PROGRAMADO (' + programado.toFixed(2) + ') NO COINCIDE CON EL COSTO TOTAL (' + costo_total.toFixed(2) + ')</div>');
                alertify.error("EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL");
                return false;
            }

            // 3. Si todo está correcto, proceder al guardado
            $('#atit').html(''); // Limpiar errores
            alertify.confirm("¿DESEA GUARDAR REQUERIMIENTO?", function (a) {
                if (a) {
                   // === INCORPORACIÓN DEL LOADING ===
                    $("#loading-overlay").css("display", "flex"); // Mostrar overlay centrado
                    // Deshabilitar botón y cambiar texto
                    $("#subir_ins").prop('disabled', true).text("GUARDANDO...");
                    // Enviar formulario
                    document.forms['form_nuevo'].submit();
                } else {
                    alertify.error("OPCI\u00D3N CANCELADA");
                }
            });
        }
    });
});




///// get form mod requerimiento
$(function () {
    var xhr_requerimiento; // Variable externa para controlar peticiones

    $(".mod_ff5").on("click", function (e) {

        e.preventDefault();
        const $this = $(this);
        const ins_id = $this.attr('name');
        const cite_id = $this.attr('id');
       // alert(ins_id+' - '+cite_id)

        // 1. Estado de carga: Mostrar loading y opacar formulario
        $("#loading_req").show();
        $("#formulario_requerimiento").css("opacity", "0.3");
        $("#mbut").hide(); 

        // Abortar petición anterior si el usuario hace clic rápido
        if (xhr_requerimiento) xhr_requerimiento.abort();

        xhr_requerimiento = $.ajax({
            url: `${base}index.php/modificaciones/cmod_insumo/get_requerimiento`,
            type: "POST",
            dataType: 'json',
            data: { ins_id, cite_id }
        });

        xhr_requerimiento.done(function (response) {
            if (response.respuesta !== 'correcto') {
                alertify.error("ERROR AL RECUPERAR DATOS");
                return;
            }

            const ins = response.insumo[0];
            const esCertificado = (ins.ins_monto_certificado != 0);

            // 2. Control de Inputs (Habilitar/Deshabilitar en bloque)
            const campos = ["#detalle", "#umedida", "#par_padre", "#par_hijo", "#observacion"];
            $(campos.join(",")).prop("disabled", esCertificado);
            
            // Lógica específica para cantidad
            $("#cantidad").prop("disabled", (esCertificado && ins.ins_monto_certificado == ins.programado_total));
            $("#costou").prop("disabled", false);

            // 3. Llenado masivo de datos al formulario
            $("#saldo, #sal").val(parseFloat(response.monto_saldo).toFixed(2));
            $("#monto_dif").val(parseFloat(response.saldo_dif).toFixed(2));
            $("#ins_id").val(ins.ins_id);
            $("#detalle").val(ins.ins_detalle);
            $("#cantidad").val(ins.ins_cant_requerida);
            $("#costou").val(parseFloat(ins.ins_costo_unitario).toFixed(2));
            $("#costot, #costot2").val(parseFloat(ins.ins_costo_total).toFixed(2));
            $("#umedida").val(ins.ins_unidad_medida);
            $("#par_hijo, #par_id").val(ins.par_id);
            $("#mtot").val(ins.programado_total);
            $("#observacion").val(ins.ins_observacion);
            $("#monto_cert").val(ins.ins_monto_certificado);

            // Inyección de HTML dinámico (Selects y Listas)
            $("#par_padre").html(response.partidas);
            $("#par_hijo").html(response.lista_partidas);
            $("#id").html(response.lista_prod_act);
            $('#monto').html(`<font color="blue" size="2"><b>MONTO CERTIFICADO : ${ins.ins_monto_certificado}</b></font>`);

            // 4. Bucle de Meses (Uso de la nueva vista)
            for (let i = 1; i <= 12; i++) {
                let nombreMes = mes_texto(i);
                let estaCertificado = (ins['certmes' + i] == 1);
                
                $(`#mm${i}`).val(ins['mes' + i]).prop("disabled", estaCertificado);
                $(`#mess${i}`).html(estaCertificado ? 
                    `<font color="red"><b>${nombreMes} (*)</b></font>` : 
                    `<b>${nombreMes}</b>`
                );
            }

            // 5. Gestión de Alertas y Títulos Finales
            let alertaHtml = "";
            let tituloHtml = "";
            let mostrarBoton = true;

            // Validación: Programado vs Costo Total
            if (ins.programado_total != ins.ins_costo_total) {
                alertaHtml = '<center><div class="alert alert-danger">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL</div></center>';
                mostrarBoton = false;
            }

            // Validación: Certificación Total
            if (ins.ins_monto_certificado == ins.programado_total) {
                tituloHtml = '<center><h2 class="alert alert-danger">REQUERIMIENTO CERTIFICADO</h2></center>';
                mostrarBoton = false;
            } else {
                const tipoMod = ins.ins_tipo_modificacion == 0 ? "" : "<br><b>(REVERSIÓN DE REQUERIMIENTO)</b>";
                tituloHtml = `<center><h2 class="alert alert-info">MODIFICAR DATOS DEL REQUERIMIENTO${tipoMod}</h2></center>`;
            }

            // Validación: Saldo disponible
            if (ins.programado_total > response.monto_saldo) {
                alertaHtml = '<center><div class="alert alert-danger">COSTO TOTAL ES MAYOR AL SALDO, VERIFIQUE MONTOS</div></center>';
                mostrarBoton = false;
            }

            $("#amtit").html(alertaHtml);
            $("#titulo_req").html(tituloHtml);
            $("#mbut")[mostrarBoton ? "slideDown" : "slideUp"]();

        });

        xhr_requerimiento.fail(function (e) {
            if (e.statusText !== 'abort') {
                alertify.error("ERROR AL RECUPERAR DATOS DEL REQUERIMIENTO");
            }
        });

        xhr_requerimiento.always(function () {
            // 6. Finalización: Ocultar loading y restaurar opacidad siempre
            $("#loading_req").hide();
            $("#formulario_requerimiento").css("opacity", "1");
        });
    });

    //// subir datos de la modificacion poa del requerimiento
    $("#subir_mins").on("click", function (e) {
        e.preventDefault(); // Evita el envío automático para validar primero

        var $validator = $("#form_mod").validate({
            rules: {
                ins_id: { required: true },
                detalle: { required: true },
                cantidad: { required: true, min: 0.1 },
                costou: { required: true, min: 0.1 },
                costot: { required: true },
                umedida: { required: true },
                par_padre: { required: true },
                par_hijo: { required: true },
                id: { required: true } // Vinculación Form 4
            },
            messages: {
                ins_id: "<font color=red>S/ID</font>",
                detalle: "<font color=red>REGISTRE DETALLE DEL REQUERIMIENTO</font>",
                cantidad: "<font color=red>CANTIDAD</font>",
                costou: "<font color=red>COSTO UNITARIO</font>",
                umedida: "<font color=red>REGISTRE UNIDAD DE MEDIDA</font>",
                par_padre: "<font color=red>SELECCIONE GRUPO</font>",
                par_hijo: "<font color=red>SELECCIONE PARTIDA</font>",
                id: "<font color=red>SELECCIONE VINCULACIÓN</font>"
            },
            highlight: function (element) {
                $(element).closest('section').addClass('has-error');
            },
            unhighlight: function (element) {
                $(element).closest('section').removeClass('has-error');
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element.parent());
            }
        });

        if ($("#form_mod").valid()) {
            const saldo = parseFloat($("#sal").val()) || 0;
            const costoTotal = parseFloat($("#costot").val()) || 0;
            
            // 1. Validar Suma de Meses (Temporalidad)
            let sumaMeses = 0;
            for (let i = 1; i <= 12; i++) {
                sumaMeses += parseFloat($(`#mm${i}`).val()) || 0;
            }

            // Redondear a 2 decimales para evitar errores de precisión en JS
            sumaMeses = parseFloat(sumaMeses.toFixed(2));

            if (sumaMeses !== costoTotal) {
                alertify.error("LA SUMA DE LOS MESES (" + sumaMeses + ") NO COINCIDE CON EL COSTO TOTAL (" + costoTotal + ")");
                $('#amtit').html('<div class="alert alert-danger text-center">LA SUMA DE LA PROGRAMACIÓN MENSUAL DEBE SER IGUAL AL COSTO TOTAL.</div>');
                return;
            }

            // 2. Validar Saldo de Partida
            // Importante: El saldo ya debería incluir el monto anterior del insumo (calculado en el controlador)
            if (costoTotal <= saldo) {
                alertify.confirm("¿ESTÁ SEGURO DE MODIFICAR EL REQUERIMIENTO?", function (a) {
                    if (a) {
                        // Bloqueo de botón y visualización de load
                        $("#loading-overlay").css("display", "flex"); // Usar flex para centrar
                        $("#subir_mins").prop("disabled", true).val("PROCESANDO...");
                        
                        // Asegurar que campos disabled se envíen si es necesario 
                        // (Opcional: $("#form_mod").find(':disabled').prop('disabled', false);)
                        
                        document.forms['form_mod'].submit();
                    } else {
                        alertify.error("OPERACIÓN CANCELADA");
                    }
                });
            } else {
                $('#amtit').html('<div class="alert alert-danger text-center">EL MONTO SUPERA EL SALDO DISPONIBLE DE LA PARTIDA.</div>');
                alertify.error("VERIFIQUE EL SALDO DISPONIBLE");
            }
        } else {
            $validator.focusInvalid();
        }
    });


});



//// ELIMINAR REQUERIMIENTO POA 2026
    $(function () {
        // Función de configuración de Alertify
        function reset_alertify() { 
            alertify.set({
                labels: { ok: "ACEPTAR", cancel: "CANCELAR" },
                delay: 5000,
                buttonFocus: "ok"
            });
        }

        // Delegación de evento para que funcione en tablas dinámicas
        $(document).on("click", ".del_ff", function (e) {
            e.preventDefault();
            reset_alertify();

            var ins_id = $(this).attr('name');
            var cite_id = $(this).attr('id'); // cite id

            alertify.confirm("¿ESTÁ SEGURO DE ELIMINAR EL REQUERIMIENTO?", function (a) {
                if (a) {
                    // 1. Mostrar tu Loading Overlay Personalizado
                    $("#loading-overlay").css("display", "flex");
                    $(".loader-content h2").text("ELIMINANDO REQUERIMIENTO...");

                    // 2. Petición AJAX
                    $.ajax({
                        url: base + "index.php/modificaciones/cmod_insumo/delete_requerimiento",
                        type: "POST",
                        dataType: "json",
                        data: { ins_id: ins_id, cite_id: cite_id }, // Enviado como objeto, más limpio
                        success: function (response) {
                            if (response.respuesta == 'correcto') {
                                alertify.success("ELIMINADO CON ÉXITO");
                                // Recarga suave para actualizar totales
                                location.reload();
                            } else {
                                $("#loading-overlay").hide(); // Ocultar si hay error para dejar ver el mensaje
                                alertify.error("ERROR AL ELIMINAR: " + (response.mensaje || "Consulte al administrador"));
                            }
                        },
                        error: function (jqXHR, textStatus) {
                            $("#loading-overlay").hide();
                            console.error("Error AJAX: " + textStatus);
                            alertify.error("ERROR DE CONEXIÓN AL SERVIDOR");
                        }
                    });
                } else {
                    alertify.error("Opción cancelada");
                }
            });
        });
    });


/////// FUNCIONES EXTRAS ======================
//// partidas hijos (add nuevo) 2026
var requestPartidaAdicion; 
$(document).ready(function () {
    $("#partida_id").change(function () {            
        var par_id = $(this).val();
        var cite_id = $('[name="cite_id"]').val();
        var id = 0; // Es 0 porque es una adición nueva
        var tp = 0; // Modo adición
        var url = base + "index.php/modificaciones/cmod_insumo/get_monto_partida";

        if (requestPartidaAdicion) {
            requestPartidaAdicion.abort();
        }

        requestPartidaAdicion = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: { par_id: par_id, cite_id: cite_id, tp: tp, id: id },
            beforeSend: function() {
                // Ocultar botón de guardado y mostrar loading
                $('#but').slideUp(); 
                $('#atit').html('<div class="text-center"><img src="'+base+'assets/img/loading.gif" width="35"/><br>Verificando saldo...</div>');
            }
        });

        requestPartidaAdicion.done(function (response) {
            if (response.respuesta == 'correcto') {
                var saldo_partida = parseFloat(response.monto) || 0;
                var costo_total = parseFloat($('[name="costot"]').val()) || 0;
                var total_programado = parseFloat($('[name="tot"]').val()) || 0;

                // Actualizar campo de saldo en la vista
                $('[name="saldo"]').val(saldo_partida.toFixed(2));

                if (saldo_partida > 0) {
                    // Validar: Saldo suficiente Y Costo coincide con Programación Temporal
                    if (costo_total <= saldo_partida && costo_total === total_programado) {
                        $('#atit').html('');
                        $('#but').slideDown(); // Mostrar botón si todo está OK
                    } 
                    else {
                        var msg = (costo_total > saldo_partida) ? 
                                  "EL COSTO EXCEDE EL SALDO DE LA PARTIDA" : 
                                  "EL COSTO TOTAL NO COINCIDE CON LA PROGRAMACIÓN MENSUAL";
                        
                        $('#atit').html('<div class="alert alert-danger text-center"><b>ERROR:</b> ' + msg + '</div>');
                        $('#but').slideUp();
                    }
                } 
                else {
                    $('#atit').html('<div class="alert alert-danger text-center">NO EXISTE PRESUPUESTO DISPONIBLE EN ESTA PARTIDA</div>');
                    $('#but').slideUp();
                }
            } else {
                alertify.error("ERROR AL RECUPERAR MONTO ASIGNADO");
                $('#atit').html('');
            }
        });

        requestPartidaAdicion.fail(function(jqXHR, textStatus) {
            if (textStatus !== 'abort') {
                $('#atit').html('<div class="alert alert-warning text-center">Error de conexión al verificar saldo.</div>');
            }
        });
    });
});


  //// partidas hijos (modificacion poa) 2026
  var requestPartida; 
  $(document).ready(function () {
    $("#par_hijo").change(function () {            
        var par_id = $(this).val();
        var tp = 1;
        var ins_id = $('[name="ins_id"]').val();
        var cite_id = $('[name="cite_id"]').val();
        var costo = parseFloat($('[name="costot"]').val()) || 0;
        var url = base + "index.php/modificaciones/cmod_insumo/get_monto_partida";

        if (requestPartida) {
            requestPartida.abort();
        }

        requestPartida = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: { par_id: par_id, cite_id: cite_id, tp: tp, id: ins_id },
            beforeSend: function() {
                // Bloqueamos el botón y mostramos el loading
                $('#mbut').slideUp();
                $('#amtit').html('<div class="text-center"><img src="'+base+'assets/img/loading.gif" width="35"/><br>Verificando saldo de partida...</div>');
            },
            complete: function() {
                // Esta parte se ejecuta siempre al finalizar
                // Puedes quitar un spinner general aquí si lo deseas
            }
        });

        requestPartida.done(function (response) {
            if (response.respuesta == 'correcto') {
                var par_id_original = parseFloat($('[name="par_id"]').val());
                var monto_base = parseFloat(response.monto);
                
                var saldo_disponible = (par_id_original == par_id) ? (monto_base + costo) : monto_base;

                $("#saldo, [name='sal']").val(saldo_disponible.toFixed(2));
                
                var programado = parseFloat($('[name="mtot"]').val()) || 0;
                var diferencia = saldo_disponible - costo;
                $('[name="monto_dif"]').val(diferencia.toFixed(2));

                if (costo > saldo_disponible) {
                    $('#amtit').html('<div class="alert alert-danger text-center">EL MONTO SUPERA EL SALDO DE LA PARTIDA</div>');
                    $('#mbut').slideUp();
                } 
                else if (programado !== costo) {
                    $('#amtit').html('<div class="alert alert-danger text-center">EL MONTO PROGRAMADO ('+programado+') NO COINCIDE CON EL COSTO TOTAL ('+costo+')</div>');
                    $('#mbut').slideUp();
                } 
                else {
                    $('#amtit').html(''); // Limpiamos el loading
                    $('#mbut').slideDown();
                }
            } else {
                $('#amtit').html('');
                alertify.error("ERROR AL RECUPERAR MONTO ASIGNADO");
            }
        });

        requestPartida.fail(function(jqXHR, textStatus) {
            if(textStatus !== 'abort') {
                $('#amtit').html('<div class="alert alert-warning text-center">Error de conexión al verificar saldo.</div>');
            }
        });
    });
});

///// Modificacion FOrmulario Nuevo de Rgistro
var requestPartida = null; 

$(document).ready(function () {
    
    // Escucha delegada global para evitar pérdida de foco si la tabla se repinta
    $(document).on("change", "#padre", function () {            
        var $combo_padre = $(this);
        var par_id = $combo_padre.val();
        var cite_id = $('[name="cite_id"]').val() || $('#cite_id').val() || 0;
        var $combo_hijo = $("#partida_id");
        
        var url = base + "index.php/modificaciones/cmod_insumo/get_partidas_dependientes_nuevo";

        // Candado preventivo local: Si limpian el grupo, restauramos el bloqueo neutro
        if (par_id === "" || par_id === undefined || par_id === null) {
            $combo_hijo.prop('disabled', true).html('<option value="">SELECCIONE PARTIDA...</option>');
            $('#atit').html('');
            return false;
        }

        // Feedback visual al operador de las regionales
        $combo_hijo.prop('disabled', true).html('<option value="">⏳ Cargando clasificadores dependientes...</option>');
        $('#atit').html('');

        // 🌟 REPARADO: Captura e inyección automática del Token CSRF de resguardo perimetral de la CNS
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        
        var datos_multipart = { 
            par_id: parseInt(par_id), 
            cite_id: parseInt(cite_id) 
        };
        if (csrf_name !== '') { datos_multipart[csrf_name] = csrf_hash; }

        // Cancelamos ráfagas o solicitudes concurrentes previas en cola para liberar RAM
        if (requestPartida && requestPartida.readyState !== 4) {
            requestPartida.abort();
        }

        // Despacho de la llamada AJAX Multipart unificada
        requestPartida = $.ajax({
            url: url,
            type: "POST",
            dataType: 'json',
            data: datos_multipart
        });

        requestPartida.done(function (response) {
            if (response.respuesta === 'correcto' || response.status === 'success') {
                // Inyectamos las subpartidas y liberamos el casillero
                $combo_hijo.prop('disabled', false).html(response.partidas_dependientes);
            } else {
                $combo_hijo.prop('disabled', true).html('<option value="">Error al recuperar partidas</option>');
                $('#atit').html('');
                if (typeof alertify !== "undefined") {
                    alertify.error("🚨 Error técnico: " + (response.message || "No se localizó presupuesto asignado."));
                }
            }
        });

        requestPartida.fail(function(jqXHR, textStatus) {
            if(textStatus !== 'abort') {
                $combo_hijo.prop('disabled', true).html('<option value="">Error de conexión</option>');
                $('#atit').html('<div class="alert alert-warning text-center" style="margin-top:10px; font-weight:bold;">⚠️ Error de conexión con el servidor central al verificar saldo.</div>');
            }
        });
    });
});



/*---------- PARTIDAS ------------*/
  $(document).ready(function() {
    pageSetUp();
    $("#partida_id").change(function () {
          $("#partida_id option:selected").each(function () {
            elegido=$(this).val();

            $.post(base+"index.php/prog/combo_umedida", { elegido: elegido }, function(data){ 
            $("#ins_um").html(data);
            });     
        });
      }); 
  })



$(document).ready(function() {
    pageSetUp();
    $("#par_padre").change(function () {
      
          $("#par_padre option:selected").each(function () {
          elegido=$(this).val();
          ins_id = $('[name="ins_id"]').val(); //// costo Total Programado
          tp=1; /// modificado
          $('[name="sal"]').val((0).toFixed(2));
          $('[name="saldo"]').val((0).toFixed(2));
          $('[name="monto_dif"]').val((0).toFixed(2));
          $('#amtit').html('');
          $('#mbut').slideUp();

          $.post(base+"index.php/prog/combo_partidas_asig", { elegido: elegido,aper:aper_id,tp:tp,id:ins_id }, function(data){ 
          $("#par_hijo").html(data);
          });     
      });
    });  
  })


  function suma_programado(input){ 
      //-------------------------------
        const valor = input.value;
        if (valor.indexOf('.') !== -1) {
          const partes = valor.split('.');
          
          if (partes[1].length > 2) {
            input.value = partes[0] + '.' + partes[1].slice(0, 2);
          }
        }
      //------------------------------

        
      sum=0;
      for (var i = 1; i<=12; i++) {
        sum=parseFloat(sum)+parseFloat($('[name="m'+i+'"]').val());
      }

      $('[name="tot"]').val((sum).toFixed(2));
      programado = parseFloat($('[name="tot"]').val()); //// programado total
      ctotal = parseFloat($('[name="costo"]').val()); //// Costo Total
      saldo = parseFloat($('[name="saldo"]').val()); //// saldo

      if(programado!=ctotal){

        $('#atit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO, VERIFIQUE DATOS</div></center>');
            $('#but').slideUp();
      }
      else{
        if(ctotal>saldo){
          $('#atit').html('<center><div class="alert alert-danger alert-block">COSTO TOTAL SUPERA AL SALDO DE LA PARTIDA, VERIFIQUE MONTOS</div></center>');
              $('#but').slideUp();
        }
        else{
          $('#atit').html('');
              $('#but').slideDown();
        }
        
      }
  }

    function suma_programado_modificado(input){
        //-------------------------------
        const valor = input.value;
        if (valor.indexOf('.') !== -1) {
          const partes = valor.split('.');
          
          if (partes[1].length > 2) {
            input.value = partes[0] + '.' + partes[1].slice(0, 2);
          }
        }
        //------------------------------

      sum=0;
      for (var i = 1; i <=12; i++) {
        sum=parseFloat(sum)+parseFloat($('[name="mm'+i+'"]').val());
      }

      $('[name="mtot"]').val((sum).toFixed(2));
      programado = parseFloat($('[name="mtot"]').val()); //// programado total
      ctotal = parseFloat($('[name="costot"]').val()); //// Costo Total
      saldo = parseFloat($('[name="sal"]').val()); //// saldo

      if(programado!=ctotal){
        $('#amtit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO, VERIFIQUE DATOS</div></center>');
            $('#mbut').slideUp();
      }
      else{
        if(ctotal>saldo){
          $('#amtit').html('<center><div class="alert alert-danger alert-block">COSTO TOTAL SUPERA AL SALDO DE LA PARTIDA, VERIFIQUE MONTOS</div></center>');
              $('#mbut').slideUp();
        }
        else{
          $('#amtit').html('');
          $('#mbut').slideDown();
        }
      }
    }

    function costo_totalm(input){
      const valor = input.value;
      if (valor.indexOf('.') !== -1) {
        const partes = valor.split('.');
        
        if (partes[1].length > 2) {
          input.value = partes[0] + '.' + partes[1].slice(0, 2);
        }
      }

      s = parseFloat($('[name="sal"]').val()); //// saldo
      a = parseFloat($('[name="cantidad"]').val()); //// cantidad
      b = parseFloat($('[name="costou"]').val()); //// Costo
      
      $('[name="costot"]').val((b*a).toFixed(2) );
      $('[name="costot2"]').val((b*a).toFixed(2) );

      ct = parseFloat($('[name="costot"]').val()); //// total
      mt = parseFloat($('[name="mtot"]').val()); //// prog

      saldo_partida = parseFloat($('[name="sal"]').val()); //// saldo partida
      $('[name="monto_dif"]').val((saldo_partida-ct).toFixed(2) ); // Saldo Disponible

      if(ct!=mt ||  isNaN(a)){
        $('#amtit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO</div></center>');
            $('#mbut').slideUp();
      }
      else{
        if(ct>saldo_partida){
          $('#amtit').html('<center><div class="alert alert-danger alert-block">COSTO TOTAL SUPERA AL SALDO DE LA PARTIDA, VERIFIQUE MONTOS</div></center>');
              $('#mbut').slideUp();
        }
        else{
          $('#amtit').html('');
          $('#mbut').slideDown();
        }
        
      }
    }

    function costo_total(input){ 
      const valor = input.value;
      if (valor.indexOf('.') !== -1) {
        const partes = valor.split('.');
        
        if (partes[1].length > 2) {
          input.value = partes[0] + '.' + partes[1].slice(0, 2);
        }
      }

      a = parseFloat($('[name="ins_cantidad"]').val()); //// cantidad
      b = parseFloat($('[name="ins_costo_u"]').val()); //// Costo unitario
      
      $('[name="costo"]').val((b*a).toFixed(2) );
      $('[name="costo2"]').val((b*a).toFixed(2) );

      ct = parseFloat($('[name="costo"]').val()); //// total
      mt = parseFloat($('[name="tot"]').val()); //// prog
      saldo_partida = parseFloat($('[name="saldo"]').val()); //// saldo partida
      $('[name="saldo_disp"]').val((saldo_partida-ct).toFixed(2) ); // Saldo Disponible

      if(ct!=mt ||  isNaN(a) || ct==0){
        $('#atit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO</div></center>');
            $('#but').slideUp();
      }
      else{
        if(ct>saldo_partida){
          $('#atit').html('<center><div class="alert alert-danger alert-block">COSTO TOTAL SUPERA AL SALDO DE LA PARTIDA, VERIFIQUE MONTOS</div></center>');
              $('#but').slideUp();
        }
        else{
          $('#atit').html('');
              $('#but').slideDown();
        }
      }
    }

    function verif(){ 
      a = parseFloat($('[name="costot"]').val()); //// total
      b = parseFloat($('[name="mtot"]').val()); //// prog
      if(a!=b){
        $('#amtit').html('<center><div class="alert alert-danger alert-block">EL MONTO PROGRAMADO NO COINCIDE CON EL COSTO TOTAL DEL REQUERIMIENTO</div></center>');
            $('#mbut').slideUp();
      }
      else{
        $('#amtit').html('');
        $('#mbut').slideDown();
      }
    }


    function mes_texto(mes){
      switch (mes) {
        case 1:
            texto = 'ENERO';
            break;
        case 2:
            texto = 'FEBRERO';
            break;
        case 3:
            texto = 'MARZO';
            break;
        case 4:
            texto = 'ABRIL';
            break;
        case 5:
            texto = 'MAYO';
            break;
        case 6:
            texto = 'JUNIO';
            break;
        case 7:
            texto = 'JULIO';
            break;
        case 8:
            texto = 'AGOSTO';
            break;
        case 9:
            texto = 'SEPTIEMBRE';
            break;
        case 10:
            texto = 'OCTUBRE';
            break;
        case 11:
            texto = 'NOVIEMBRE';
            break;
        case 12:
            texto = 'DICIEMBRE';
            break;
        default:
            texto = 'SIN REGISTRO';
            break;
      }
      return texto;
    }

    ///////////////// Anular (ocultar) el item del reporte
      $(function () {
        /*------- Anular Modifcación -------*/
        $(".anular_mod").on("click", function (e) {
            var id = $(this).attr('name');
          
            var request;
            // confirm dialog
            alertify.confirm("QUITAR REQUERIMIENTO DEL CITE ?", function (a) {
                if (a) { 
                    var url = base+"index.php/modificaciones/cmod_insumo/quitar_requerimiento_cite";
                    $('#loading-overlay').css('display', 'flex');
                    if (request) {
                        request.abort();
                    }
                    request = $.ajax({
                        url: url,
                        type: "POST",
                        dataType: "json",
                      data: "id="+id
                    });

                    request.done(function (response, textStatus, jqXHR) { 
                     
                      if (response.respuesta == 'correcto') {
                          window.location.reload(true);
                      } else {
                        $('#loading-overlay').hide();
                          alertify.error("Error al anular Item !!! ");
                      }
                  });
                    request.fail(function (jqXHR, textStatus, thrown) {
                      $('#loading-overlay').hide();
                        console.log("ERROR: " + textStatus);
                    });
                    request.always(function () {
                        //console.log("termino la ejecuicion de ajax");
                    });

                    e.preventDefault();

                } else {
                    // user clicked "cancel"
                    alertify.error("Opcion cancelada");
                }
            });
            return false;
        });

    });






