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

/*function abreVentana(url) {
    // 0. Detectamos el título del elemento que hizo clic
    var elemento = window.event ? window.event.target.closest('a') : null;
    var tituloFinal = (elemento && elemento.title) ? elemento.title : "Cargando Reporte...";

    var ancho = 1000;
    var alto = 800;
    var posicion_x = (screen.width / 2) - (ancho / 2);
    var posicion_y = (screen.height / 2) - (alto / 2);

    var nuevaVentana = window.open('', '_blank', "width=" + ancho + ",height=" + alto + ",menubar=0,toolbar=0,directories=0,scrollbars=no,resizable=no,left=" + posicion_x + ",top=" + posicion_y);

    nuevaVentana.document.write(`
        <html>
            <head>
                <title>${tituloFinal}</title>
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
                    <h2>${tituloFinal}</h2>
                    <p>Por favor, espere un momento.</p>
                </div>
            </body>
        </html>
    `);

    nuevaVentana.location.href = url;
}
*/
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
  $(function () {
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
  });

  /*------------ VERIFICANDO POA ----------------*/
  $(function () {
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