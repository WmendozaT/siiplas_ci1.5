base = $('[name="base"]').val();


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


    function doSearch_lista(){
      var tableReg = document.getElementById('datos_lista');
      var searchText = document.getElementById('searchTerm_lista').value.toLowerCase();
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


    function doSearch_form4(){
      var tableReg = document.getElementById('datos_form4');
      var searchText = document.getElementById('searchTerm_form4').value.toLowerCase();
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

    function doSearch_form5(){
      var tableReg = document.getElementById('datos_form5');
      var searchText = document.getElementById('searchTerm_form5').value.toLowerCase();
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


  /// ver reportes POA FORMULARIO 4 Y 5
  function ver_poa(proy_id) {
    $('#titulo').html('<font size=3><b>Cargando ..</b></font>');
    $('#content1').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Informacion</div>');
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
        $('#titulo').html('<font size=3><b>'+response.titulo_poa+'</b></font>');
        $('#content1').fadeIn(1000).html(response.tabla);
      }
      else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

    });
  }

  /// ver reportes MODIFICACION POA FORMULARIO 4 Y 5
  function ver_mpoa(proy_id) {
   // alert(proy_id)
    $('#titulo_mod').html('<font size=3><b>Cargando ..</b></font>');
    $('#content_mod').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Informacion</div>');
    var url = base+"index.php/consultas_cns/c_consultas/get_mpoa";
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
        $('#titulo_mod').html('<font size=3><b>'+response.titulo_poa+'</b></font>');
        $('#content_mod').fadeIn(1000).html(response.tabla);
      }
      else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

    });
  }


  /// ver reportes CERTIFICACION POA FORMULARIO 5
  function ver_certpoa(proy_id) {
   // alert(proy_id)
    $('#titulo_certpoa').html('<font size=3><b>Cargando ..</b></font>');
    $('#content_certpoa').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Informacion</div>');
    var url = base+"index.php/consultas_cns/c_consultas/get_certpoa";
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
        $('#titulo_certpoa').html('<font size=3><b>'+response.titulo_poa+'</b></font>');
        $('#content_certpoa').fadeIn(1000).html(response.tabla);
      }
      else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

    });
  }


  /// ver reportes CERTIFICACION POA FORMULARIO 5 por Componente
  function ver_certpoa_uresponsable(com_id) {

    $('#titulo_certpoa').html('<font size=3><b>Cargando ..</b></font>');
    $('#content_certpoa').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Informacion</div>');
    var url = base+"index.php/consultas_cns/c_consultas/get_certpoa_uresponsable";
    var request;
    if (request) {
        request.abort();
    }
      request = $.ajax({
        url: url,
        type: "POST",
        dataType: 'json',
        data: "com_id="+com_id
      });

      request.done(function (response, textStatus, jqXHR) {

      if (response.respuesta == 'correcto') {
        $('#titulo_certpoa').html('<font size=3><b>'+response.titulo_poa+'</b></font>');
        $('#content_certpoa').fadeIn(1000).html(response.tabla);
      }
      else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

    });
  }


  /// ver reportes EVALUACION POA
  function ver_evaluacionpoa(proy_id) {
   // alert(proy_id)
    $('#titulo_evalpoa').html('<font size=3><b>Cargando ..</b></font>');
    $('#content_evalpoa').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Informacion</div>');
    var url = base+"index.php/consultas_cns/c_consultas/get_evalpoa";
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
        $('#titulo_evalpoa').html('<font size=3><b>'+response.titulo_poa+'</b></font>');
        $('#content_evalpoa').fadeIn(1000).html(response.tabla);


        /////////// ACUMULADO

        chart = new Highcharts.Chart({
        chart: {
          renderTo: 'parametro_efi',  // Le doy el nombre a la gráfica
          defaultSeriesType: 'line' // Pongo que tipo de gráfica es
        },
        title: {
          text: 'CUADRO DE CUMPLIMIENTO POA'  // Titulo (Opcional)
        },
        subtitle: {
          text: ''   // Subtitulo (Opcional)
        },
        // Pongo los datos en el eje de las 'X'
        xAxis: {
          categories: ['','I Trimestre','II Trimestre','III Trimestre','IV Trimestre'],
          // Pongo el título para el eje de las 'X'
          title: {
            text: '% Operaciones Acumulados por Trimestre'
          }
        },
        yAxis: {
          // Pongo el título para el eje de las 'Y'
          title: {
            text: '% Ejecucion'
          }
        },
        // Doy formato al la "cajita" que sale al pasar el ratón por encima de la gráfica
        tooltip: {
          enabled: true,
          formatter: function() {
            return '<b>'+ this.series.name +'</b><br/>'+
              this.x +': '+ this.y +' '+this.series.name;
          }
        },
        // Doy opciones a la gráfica
        plotOptions: {
          line: {
            dataLabels: {
              enabled: true
            },
            enableMouseTracking: true
          }
        },
        // Doy los datos de la gráfica para dibujarlas
        series: [
            {
              name: 'Nro. ACT. Programado',
              data: [0,response.evaluacion[2][1],response.evaluacion[2][2],response.evaluacion[2][3],response.evaluacion[2][4]]
            },
            {
              name: 'Nro. ACT, Cumplidas',
              data: [0,response.evaluacion[3][1],response.evaluacion[3][2],response.evaluacion[3][3],response.evaluacion[3][4]]
            }
           /* ,
            {
            name: '(%) cumplimiento',
            data: [0,response.evaluacion[5][1],response.evaluacion[5][2],response.evaluacion[5][3],response.evaluacion[5][4]]
            },
            {
            name: '(%) no cumplido',
            data: [0,response.evaluacion[6][1],response.evaluacion[6][2],response.evaluacion[6][3],response.evaluacion[6][4]]
            }*/
            ],
        });
      }
      else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
      }

    });
  }


$(document).ready(function() {
  pageSetUp();

    //// select POA Oficina Nacional
    $("#proy_id").change(function () {
      $("#proy_id option:selected").each(function () {
        elegido=$(this).val();
        //alert(elegido)
        if(elegido!=0){
          //$('#listado').slideDown();
          $.post(base+"index.php/rep/get_consultas_da", { elegido: elegido,accion:'componentes' }, function(data){
            $("#com_id").html(data);
              $("#informacion_poa").html('');
          });
        }
      });
    });


    //// select consulta POa NAcional
    $("#dep_id").change(function () {
        $("#dep_id option:selected").each(function () {
          elegido=$(this).val();

          if(elegido!=0){
            $('#ue').slideDown();
            $('#tp').slideDown();
            $.post(base+"index.php/rep/get_consultas_da", { elegido: elegido,accion:'reporte' }, function(data){
                $("#tp_rep").html(data);
                $("#lista_consolidado").html('');
            });
          }
        });
    });

    $("#tp_rep").change(function () {
        $("#tp_rep option:selected").each(function () {
          elegido=$(this).val();
          $.post(base+"index.php/rep/get_consultas_da", { elegido: elegido,accion:'tipo' }, function(data){
              $("#tipo").html(data);
              $("#lista_consolidado").html('');
          });
        });
    });



    var request_consolidado = null; 

    $("#tipo").change(function () {
        $("#tipo option:selected").each(function () {
            // Asignación de variables nativas de tu entorno
            dep_id = $('[name="dep_id"]').val();
            tp_rep = $('[name="tp_rep"]').val();
            tp_id  = $(this).val();

            // 1. Validación de cortesía institucional
            if (tp_id == "" || tp_id == "0") {
                $("#lista_consolidado").fadeOut(150).html("");
                return false;
            }

            // 2. Intercepción operativa: aborta de forma segura la petición previa si el usuario cambia rápido de combo
            if (request_consolidado && request_consolidado.readyState !== 4) {
                request_consolidado.abort();
            }

            // 3. Despliegue estético del template de carga unificado
            var loading_template = `
            <div style="padding: 30px 0; text-align: center; background: #fafafa; border: 1px dashed #ddd; border-radius: 4px; margin-top: 15px;">
                <i class="fa fa-refresh fa-spin text-primary" style="font-size: 28px; margin-bottom: 10px;"></i>
                <h5 style="margin: 0; font-weight: bold; color: #444; font-size: 12px; letter-spacing: 0.3px;">
                    CONSOLIDANDO INFORMACIÓN FINANCIERA POA 2026
                </h5>
                <small class="text-muted" style="font-size: 10.5px;">Un momento por favor, procesando registros en el servidor...</small>
            </div>`;
            
            $("#lista_consolidado").html(loading_template).show();
            
            // 4. Configuración de ruta y envío asíncrono manteniendo tu formato original
            var url = base + "index.php/consultas_cns/c_consultas/get_lista_reportepoa";
            
            // Asignamos el AJAX directo a tu canal de control global resguardando los hilos
            request_consolidado = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json',
                data: "dep_id=" + dep_id + "&tp_rep=" + tp_rep + "&tp_id=" + tp_id
            });

            // 5. Procesamiento de la respuesta exitosa
            request_consolidado.done(function (response, textStatus, jqXHR) {
                if (response.respuesta == 'correcto') {
                    $('#lista_consolidado').hide().html(response.lista_reporte).fadeIn(1000);
                }
                else {
                    if (typeof alertify !== "undefined") {
                        alertify.error("ERROR AL LISTAR");
                    } else {
                        alert("⚠️ ERROR AL LISTAR");
                    }
                    $("#lista_consolidado").html("");
                }
            }); 

            // 6. Control de contingencia ante caídas del servidor local Apache/XAMPP
            request_consolidado.fail(function (jqXHR, textStatus) {
                if (textStatus !== "abort") {
                    if (typeof alertify !== "undefined") {
                        alertify.error("ERROR CRÍTICO DE RED");
                    }
                    $("#lista_consolidado").html("");
                }
            });
            
        });
    });


    ///// ============== MUESTRA INFORMACION DEL POA OFICINA NACIONAL
    $("#com_id").change(function () {
      $("#com_id option:selected").each(function () {
        //dep_id=$('[name="dep_id"]').val();
        //tp_rep=$('[name="tp_rep"]').val();
        com_id=$(this).val();
        
        if(com_id!=0){
          $('#informacion_poa').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Información POA ...</div>');
          var url = base+"index.php/consultas_cns/c_consultas/get_informacion_poa_ofc";
          var request;
          if (request) {
              request.abort();
          }
          request = $.ajax({
              url: url,
              type: "POST",
              dataType: 'json',
              data: "com_id="+com_id
          });

          request.done(function (response, textStatus, jqXHR) {
              if (response.respuesta == 'correcto') {
                  $('#informacion_poa').fadeIn(1000).html(response.lista_reporte);
              }
              else{
                  alertify.error("ERROR AL LISTAR");
              }
          });
        }
        else{
          $("#informacion_poa").html('SELECCIONE');
        }

         
          
      });
    });


})


