base = $('[name="base"]').val();

  ////------------  PARA MIGRAR ARCHIVO EN EXCEL 2026 ==========



    function doSelectAlert(event,tp_id,com_id) {
        var option = event.srcElement.children[event.srcElement.selectedIndex];
        if (option.dataset.noAlert !== undefined) {
            return;
        }

        alertify.confirm("CAMBIAR TIPO DE SUBACTIVIDAD ?", function (a) {
              if (a) {
              var url = base+"index.php/programacion/componente/cambia_tp_sact";
              $.ajax({
                  type: "post",
                  url: url,
                  data:{com_id:com_id,tp_id:tp_id},
                      success: function (data) {
                      window.location.reload(true);
                  }
              });
              } else {
                  alertify.error("OPCI\u00D3N CANCELADA");
              }
            });
    }

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

            /*----------- ELIMINAR OPERACIONES ---------------*/
            $(".del_ff").on("click", function (e) {
                reset();
                var name = $(this).attr('name');
                var nro = $(this).attr('id');
                var request;
                alertify.confirm("ESTA SEGURO DE ELIMINAR "+nro+" ACTIVIDADES ?", function (a) {
                    if (a) { 
                        var url = base+"index.php/programacion/componente/elimina_operaciones_componente";
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
                              alertify.alert("LAS OPERACIONES SE ELIMINARON CORRECTAMENTE ", function (e) {
                                  if (e) {
                                      window.location.reload(true);
                                  }
                              });
                          } else {
                              alertify.alert("ERROR AL ELIMINAR OPERACIONES!!!", function (e) {
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
