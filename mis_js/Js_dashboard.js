base = $('[name="base"]').val();

        function doSearch(nro){
          var tableReg = document.getElementById('datos'+nro);
          var searchText = document.getElementById('searchTerm'+nro).value.toLowerCase();
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


        $(document).on("click", ".btn-ajuste-loading", function(e) {
          e.preventDefault();
          const $boton = $(this);
          const urlRedireccion = $boton.data('url');

          // 1. Reemplazar el icono por un spinner de carga
          $boton.html('<i class="fa fa-spinner fa-spin" style="font-size:18px; color:#2d3e50;"></i>');
          
          // 2. Deshabilitar para evitar clics repetidos
          $boton.css({
              'pointer-events': 'none',
              'opacity': '0.7'
          });

          // 3. Pequeño delay para feedback visual antes de ir al formulario
          setTimeout(function() {
              window.location.href = urlRedireccion;
          }, 400); 
      });







        function abreVentana(url) {
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

      //   function abreVentana2(PDF) {
      //     var direccion = '' + PDF;
      //     var ventana = window.open(direccion, "IMPRESION", "width=800,height=700,scrollbars=NO");

      //     // Esperar a que la ventana se cargue completamente
      //       ventana.onload = function() {
      //         ventana.print(); // Imprimir automáticamente

      //         // Cerrar la ventana después de un breve retraso
      //         setTimeout(function() {
      //             ventana.close(); // Cerrar la ventana
      //         }, 2000); // Ajusta el tiempo según sea necesario
      //     };
      // }



       $( document ).ready(function() {
            $('#myModal').modal('toggle')
        });



        /*------ Notificacion POA Gasto Corriente 2026 ------*/
        $(function () {
          $(".form4_mes").on("click", function(e) {
              const dist_id = $(this).attr('id');
              const $contenedor = $('#Notificacion_formN4');
              
              // Insertamos el loading con una estructura CSS limpia
              $contenedor.html(`
                  <div class="loading-container">
                      <div class="spinner"></div>
                      <p>Cargando actividades del mes...</p>
                  </div>
              `);

              if (window.activeRequest) window.activeRequest.abort();

              window.activeRequest = $.ajax({
                  url: `${base}index.php/ejecucion/cseguimiento/get_form4_gc_mes`,
                  type: "POST",
                  dataType: 'json',
                  data: { dist_id: dist_id }
              })
              .done(response => {
                  if (response.respuesta === 'correcto') {
                      $contenedor.hide().html(response.tabla).fadeIn(); // Efecto visual suave
                  } else {
                      alertify.error("Error al recuperar datos.");
                  }
              })
              .fail((jqXHR, textStatus) => {
                  if (textStatus !== "abort") $contenedor.html('<p>Error de conexión.</p>');
              });
          });


          //// Notificacion Poa Proyectos de Inversion
          $(".pi_mes").on("click", function(e) {
              const dist_id = $(this).attr('id');
              const $contenedor = $('#Notificacion_formN4');
              
              // Insertamos el loading con una estructura CSS limpia
              $contenedor.html(`
                  <div class="loading-container">
                      <div class="spinner"></div>
                      <p>Cargando Notificacion POA - Inversión del mes...</p>
                  </div>
              `);

              if (window.activeRequest) window.activeRequest.abort();

              window.activeRequest = $.ajax({
                  url: `${base}index.php/ejecucion/cseguimiento/get_form5_pi_mes`,
                  type: "POST",
                  dataType: 'json',
                  data: { dist_id: dist_id }
              })
              .done(response => {
                  if (response.respuesta === 'correcto') {
                      $contenedor.hide().html(response.tabla).fadeIn(); // Efecto visual suave
                  } else {
                      alertify.error("Error al recuperar datos.");
                  }
              })
              .fail((jqXHR, textStatus) => {
                  if (textStatus !== "abort") $contenedor.html('<p>Error de conexión.</p>');
              });
          });
          ////// ------ FIN NOTIFICACIONES POA























          //// Seguimiento POA a unidades por responsable poa Regional 2026
          $(".seg_uni").on("click", function (e) {
            dist_id = $(this).attr('id');

            $('#seg').html('<div class="loading-container"><div class="spinner"><p>Cargando Listado ....</p></div></div>');

            var url = base+"index.php/ejecucion/cseguimiento/get_unidades_seguimiento_poa_mensual";
            var request;
            if (request) {
                request.abort();
            }
            request = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: "dist_id="+dist_id
            });

            request.done(function (response, textStatus, jqXHR) { 
                if (response.respuesta == 'correcto') {
                    $('#seg').html(response.tabla);
                } else {
                    alertify.error("ERROR AL RECUPERAR DATOS, PORFAVOR CONTACTESE CON EL ADMINISTRADOR"); 
                }
            });

          });
        });



       
        $('#seg_reg').change(function() {
            alert('hola')
            // var selectedValue = $(this).val(); // Obtener el valor seleccionado
            // if (selectedValue !== "0") { // Verifica si se ha seleccionado una opción válida
            //     $.ajax({
                    
            //         url = base+"index.php/ejecucion/cseguimiento/get_unidades_seguimiento_poa_mensual_nacional";
            //         type: 'POST',
            //         data: { value: selectedValue },
            //         dataType: 'json', // Esperar una respuesta en formato JSON
            //         success: function(response) {
            //             if (response.status === 'success') {
            //                 //alert(response.message)
            //                 $('#responsee').html(response.message);
            //                 $('#botones').html(response.button);
            //                 //$('#responsee').text(response.message).show(); // Mostrar el mensaje en el modal
            //             } else {
            //                 $('#response').text("Error en la respuesta.").show(); // Mensaje de error
            //             }
            //             $('#modal_respuesta').modal('show'); // Muestra el modal
            //         },
            //         error: function() {
            //             $('#response').text("Error al procesar la solicitud.").show(); // Mensaje de error
            //             $('#modal_respuesta').modal('show'); // Muestra el modal
            //         }
            //     });
            // }
        });



          $(function () {
          $("#subir_form").on("click", function () {
            val=document.getElementById("gestion_usu").value;

            if(val!=0 & val!=''){
              if(document.getElementById("gest").value!=document.getElementById("gestion_usu").value){
                alertify.confirm("CAMBIAR GESTI&Oacute;N ?", function (a) {
                    if (a) {
                        document.getElementById("load").style.display = 'block';
                        document.getElementById('subir_form').disabled = true;
                        document.forms['form_nuevo'].submit();
                    } else {
                        alertify.error("OPCI\u00D3N CANCELADA");
                    }
                });
              }
              else{
                alertify.success("GESTI&Oacute;N SELECCIONADA");
              }
            }
            else{
              alertify.error("SELECCIONE GESTI&Oacute;N");
            }
              
          });

          $("#subir_formt").on("click", function () {
            val=document.getElementById("trimestre_usu").value;

            if(val!=0 & val!=''){
              if(document.getElementById("tmes").value!=document.getElementById("trimestre_usu").value){
                alertify.confirm("CAMBIAR TRIMESTRE ?", function (a) {
                    if (a) {
                        document.getElementById("loadt").style.display = 'block';
                        document.getElementById('subir_formt').disabled = true;
                        document.forms['form_trimestre'].submit();
                    } else {
                        alertify.error("OPCI\u00D3N CANCELADA");
                    }
                });
              }
              else{
                alertify.success("TRIMESTRE SELECCIONADO");
              }
            }
            else{
              alertify.error("SELECCIONE TRIMESTRE");
            }
              
          });
      });