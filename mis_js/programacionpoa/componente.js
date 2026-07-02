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
                alertify.confirm("ESTA SEGURO EN DESHABILITAR LA SUB ACTIVIDAD ?", function (a) {
                    if (a) { 
                        var url = base+"index.php/programacion/componente/des_sactividad";
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