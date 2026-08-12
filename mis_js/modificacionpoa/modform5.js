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


  ////------------  PARA MIGRAR ARCHIVO EN EXCEL 2026 ==========2026
$(document).ready(function() {
    // Mostrar nombre del archivo al seleccionar
    $('#archivo').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('.file-name-display').val(fileName); // Ajustado al input readonly del diseño anterior
        }
    });

    $('#btn_subir').on('click', function(e) {
        e.preventDefault();
        $('#mensaje').html(''); 

        if ($('#archivo').val() == '') {
            $('#mensaje').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel.</div>');
            return false;
        }

        var form = $('#form_subir_sigep')[0];
        var data = new FormData(form);

        // Bloquear UI
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> PROCESANDO...');
        $('#loads').show();

        $.ajax({
            type: "POST",
            url: $('#form_subir_sigep').attr('action'),
            data: data,
            processData: false,
            contentType: false,
            success: function(response) {
                    var res;
                    try {
                        res = (typeof response === 'object') ? response : JSON.parse(response);
                    } catch (e) {
                        console.error("Error parseando JSON:", response);
                        $('#mensaje').html('<div class="alert alert-danger">Error de respuesta del servidor (Posible tiempo de espera agotado).</div>');
                        $('#btn_subir').prop('disabled', false).text('REINTENTAR');
                        $('#loads').hide();
                        return;
                    }

                if (res.status === 'success') {
                    // Construimos un mensaje más visual
                    var html = `
                        <div class="alert alert-success text-center" style="border-left: 5px solid #2d8a39;">
                            <i class="fa fa-check-circle fa-3x" style="margin-bottom:12px;"></i>
                            <h4>¡PROCESO COMPLETADO!</h4>
                            <p style="font-size: 16px;">${res.msj}</p>
                            <div style="font-size: 24px; font-weight: bold;">
                                <span class="label label-success">${res.conteo}</span>
                            </div>
                            <p><small>Requerimientos registrados en el sistema.</small></p>
                        </div>`;

                    $('#mensaje').html(html);
                    $('#loads').hide();
                    
                    // Ocultar el botón para evitar doble clic
                    $('#btn_subir').hide();

                    // Recargar la página después de 3 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    // LÓGICA DE ERRORES
                    var errorMsg = "<strong>SE ENCONTRARON ERRORES:</strong><ul style='margin-top:12px;'>";
                    $.each(res.errors, function(index, value) {
                        errorMsg += "<li>" + value + "</li>";
                    });
                    errorMsg += "</ul>";
                    
                    $('#mensaje').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    $('#btn_subir').prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> VALIDAR Y SUBIR ARCHIVO EXCEL');
                    $('#loads').hide();
                }
            },
            error: function() {
                $('#mensaje').html('<div class="alert alert-danger">Error crítico: No se pudo procesar el archivo en el servidor.</div>');
                $('#btn_subir').prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR SUBIDA');
                $('#loads').hide();
            }
        });
    });
});
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

    $(".mod_ff").on("click", function (e) {
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
            const esCertificado = (ins.certificado_total != 0);

            // 2. Control de Inputs (Habilitar/Deshabilitar en bloque)
            const campos = ["#ins_id","#detalle", "#umedida", "#par_padre", "#par_hijo", "#observacion"];
            $(campos.join(",")).prop("disabled", esCertificado);
            
            // Lógica específica para cantidad
            $("#cantidad").prop("disabled", (esCertificado && ins.certificado_total == ins.programado_total));
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
            $("#monto_cert").val(ins.certificado_total);

            // Inyección de HTML dinámico (Selects y Listas)
            $("#par_padre").html(response.partidas);
            $("#par_hijo").html(response.lista_partidas);
            $("#id").html(response.lista_prod_act);
            $('#monto').html(`<font color="blue" size="2"><b>MONTO CERTIFICADO : ${ins.certificado_total}</b></font>`);

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
            if (ins.certificado_total == ins.programado_total) {
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


/*---------- PARTIDAS ------------*/
  $(document).ready(function() {
    pageSetUp();
      $("#padre").change(function () {
          $("#padre option:selected").each(function () {
          elegido=$(this).val();
          tp=0; /// nuevo
          //alert(elegido+' '+aper_id+'-'+tp+'-'+cite_id)
          $('[name="saldo"]').val((0).toFixed(2));
          $('#atit').html('');
          $('#but').slideUp();

          $.post(base+"index.php/prog/combo_partidas_asig", { elegido: elegido,aper:aper_id,tp:tp,id:cite_id }, function(data){ 
          console(data)
          //$("#partida_id").html(data);
          });     
        });
      });

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






