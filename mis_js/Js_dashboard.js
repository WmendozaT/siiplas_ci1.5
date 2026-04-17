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
        function abreVentana(PDF){             
          var direccion;
          direccion = '' + PDF;
          window.open(direccion, "IMPRESION" , "width=800,height=700,scrollbars=NO") ; 
        }

        function abreVentana2(PDF) {
          var direccion = '' + PDF;
          var ventana = window.open(direccion, "IMPRESION", "width=800,height=700,scrollbars=NO");

          // Esperar a que la ventana se cargue completamente
            ventana.onload = function() {
              ventana.print(); // Imprimir automáticamente

              // Cerrar la ventana después de un breve retraso
              setTimeout(function() {
                  ventana.close(); // Cerrar la ventana
              }, 2000); // Ajusta el tiempo según sea necesario
          };
      }



       $( document ).ready(function() {
            $('#myModal').modal('toggle')
        });

        /*------ Evaluacion de Formulario N 4 ------*/
        $(function () {
          var prod_id = ''; var proy_id = '';
          $(".form4_mes").on("click", function (e) {
              dist_id = $(this).attr('id');
              $('#operaciones').html('<div class="loading" align="center"><img src="'+base_url()+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Cargando lista de Actividades a ejecutar este mes ...</div>');

              var url = base+"index.php/ejecucion/cseguimiento/get_form4_gc_mes";
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
                      $('#operaciones').html(response.tabla);
                  } else {
                      alertify.error("ERROR AL RECUPERAR DATOS, PORFAVOR CONTACTESE CON EL ADMINISTRADOR"); 
                  }
              });
          });


          //// Proyectos de Inversion
          $(".pi_mes").on("click", function (e) {

            dist_id = $(this).attr('id');

            $('#pinversion').html('<div class="loading" align="center"><img src="'+base_url()+'/assets/img_v1.1/preloader.gif" alt="loading" alt="loading" /><br/>Cargando lista de Proyectos de Inversión a ejecutar este mes ...</div>');

            var url = base+"index.php/ejecucion/cseguimiento/get_form5_pi_mes";
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
                    $('#pinversion').html(response.tabla);
                } else {
                    alertify.error("ERROR AL RECUPERAR DATOS, PORFAVOR CONTACTESE CON EL ADMINISTRADOR"); 
                }
            });

          });


          //// Seguimiento POA a unidades por responsable poa Regional
          $(".seg_uni").on("click", function (e) {
            dist_id = $(this).attr('id');

            $('#seg').html('<div class="loading" align="center"><br/>Cargando lista de Proyectos de Inversión a ejecutar este mes ...</div>');

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