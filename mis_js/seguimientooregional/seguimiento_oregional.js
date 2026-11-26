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

    ///                 ---------- Formulario N 2 - Evaluacion Operaciones
    //// Selecciona lista de regionales y actualiza la ejecucion 2027
    $(function() {
      //// LISTA DE OPERACIONES POR REGIONAL 2027
      var requestOperaciones = null; // Declarada fuera para abortar peticiones previas si cambian rápido de opción
      jQuery("#dep_id").on("change", function () {

          var dep_id = jQuery(this).val();
          var baseUrl = jQuery('input[name="base"]').val(); // Captura segura de la URL base

          if (dep_id != 0) {
              // Indicador visual de carga centrado
              jQuery('#titulo_lista').html(
                  '<div style="padding: 40px 20px; text-align: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; margin-top: 15px;">' +
                      '<i class="fa fa-circle-o-notch fa-spin" style="font-size: 150px; color: #1c7368; margin-bottom: 12px;"></i>' +
                      '<h4 style="margin: 0; font-weight: bold; color: #334155;">Consultando y Actualizando Operaciones de la regional</h4>' +
                      '<p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">Por favor espere un momento...</p>' +
                  '</div>'
              );

              // Si ya había una petición en curso, la cancela para evitar cruces de datos
              if (requestOperaciones != null) {
                  requestOperaciones.abort();
              }

              requestOperaciones = jQuery.ajax({
                  url: baseUrl + "index.php/ejecucion/cevaluacion_form2/get_lista_operaciones_x_regionales",
                  type: "POST",
                  dataType: "json",
                  data: { dep_id: dep_id }
              });

              requestOperaciones.done(function (response) {
                  if (response.respuesta === 'correcto') {
                      jQuery('#titulo_lista').html(response.tabla);
                  } else {
                      jQuery('#titulo_lista').html('');
                      alertify.error("ERROR AL RECUPERAR INFORMACIÓN");
                  }
              });

              requestOperaciones.fail(function (jqXHR, textStatus) {
                  // Ignora el mensaje de error si fue cancelada intencionalmente por el usuario
                  if (textStatus !== 'abort') {
                      jQuery('#titulo_lista').html('<div class="alert alert-danger">Error de comunicación con el servidor.</div>');
                  }
              });

          } else {
              jQuery('#titulo_lista').html('');
          }
      });
    });


  /// Lista de Actividades Priorizados por cada Objetivo Regional FORM 2
  function ver_actividades_priorizados(or_id,dep_id) {
    //alert('hola mundo')
    $('#titulo').html('<font size=3><b>Cargando ..</b></font>');
    $('#content1').html('<div class="loading" align="center"><img src="'+base+'/assets/img_v1.1/preloader.gif" alt="loading" /><br/>Un momento por favor, Cargando Ediciones </div>');
    $('#imprimir_act_priori').html('');
  //  alert(dep_id)
    var url = base+"index.php/ejecucion/cevaluacion_form2/ver_actividades_priorizados";
    var request;
    if (request) {
        request.abort();
    }
    request = $.ajax({
        url: url,
        type: "POST",
        dataType: 'json',
        data: "or_id="+or_id+"&dep_id="+dep_id
    });

    request.done(function (response, textStatus, jqXHR) {

    if (response.respuesta == 'correcto') {
        $('#titulo').html(response.titulo);
        $('#content1').fadeIn(1000).html(response.tabla);
        $('#imprimir_act_priori').fadeIn(1000).html(response.imprimir_act_priori);
    }
    else{
        alertify.error("ERROR AL RECUPERAR INFORMACION");
    }

    });
  }

  // Variables globales para controlar las instancias de los gráficos de este modal 2027 (Modulo : Evaluacion POA)
  function nivel_cumplimiento(elemento, datosFila) {
    // 1. Extraer metadatos para el encabezado del modal
    var codigoOpe = datosFila.or_codigo || '';
    var objetivo  = datosFila.or_objetivo || '';
    var meta      = datosFila.or_meta || '0';

    jQuery('#titulo_grafico').html(
        '<b style="font-family:Verdana; font-size: 16px;">' +
            'OBJ. REGIONAL : ' + codigoOpe + ' ' + objetivo + '<br>' +
            'META : ' + meta +
        '</b>'
    );
    // 2. Convertir obligatoriamente todos los valores de texto a números decimales (parseFloat)
    var trm1 = parseFloat(datosFila.trm1) || 0;
    var trm2 = parseFloat(datosFila.trm2) || 0;
    var trm3 = parseFloat(datosFila.trm3) || 0;
    var trm4 = parseFloat(datosFila.trm4) || 0;

    var etrm1 = parseFloat(datosFila.e_trm1) || 0;
    var etrm2 = parseFloat(datosFila.e_trm2) || 0;
    var etrm3 = parseFloat(datosFila.e_trm3) || 0;
    var etrm4 = parseFloat(datosFila.e_trm4) || 0;

    var porc1 = parseFloat(datosFila.porc_cump_1) || 0;
    var porc2 = parseFloat(datosFila.porc_cump_2) || 0;
    var porc3 = parseFloat(datosFila.porc_cump_3) || 0;
    var porc4 = parseFloat(datosFila.porc_cump_4) || 0;

    var trm_porc1 = parseFloat(datosFila.trm_porc1) || 0;
    var trm_porc2 = parseFloat(datosFila.trm_porc2) || 0;
    var trm_porc3 = parseFloat(datosFila.trm_porc3) || 0;
    var trm_porc4 = parseFloat(datosFila.trm_porc4) || 0;

    var trm_ejec_porc1 = parseFloat(datosFila.trm_porc_ejec1) || 0;
    var trm_ejec_porc2 = parseFloat(datosFila.trm_porc_ejec2) || 0;
    var trm_ejec_porc3 = parseFloat(datosFila.trm_porc_ejec3) || 0;
    var trm_ejec_porc4 = parseFloat(datosFila.trm_porc_ejec4) || 0;

        // 4. Llenar la tabla TRIMESTRAL (<tr id="tab">)
     var htmlTrimestral = 
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm1 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + etrm1 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%)</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + porc1 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm2 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + etrm2 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%)</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + porc2 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm3 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + etrm3 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%)</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + porc3 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm4 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + etrm4 + '</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%)</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + porc4 + '%</b></td>'+
                  '</tr>'+
            '</table>'
         '</td>';
    
     jQuery('#tab').html(htmlTrimestral);

    // // 5. Llenar la tabla ACUMULADA (<tr id="tab_acumulado">)
    var htmlAcumulado = 
        '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>(%) PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm_porc1 + '%</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%) EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + trm_ejec_porc1 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>(%) PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm_porc2 + '%</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%) EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + trm_ejec_porc2 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>(%) PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm_porc3 + '%</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%) EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + trm_ejec_porc3 + '%</b></td>'+
                  '</tr>'+
            '</table>'+
         '</td>' +
         '<td align="center" style="font-size:15px;">' +
            '<table class="table table-bordered" border=0.2 style="width:80%;">'+
                  '<tr>'+
                  '  <td style="font-size: 11px;;width:50%;"><b>(%) PROG.</b></td>'+
                  '  <td style="width:50%;font-size: 11px; color:blue" align=right><b>' + trm_porc4 + '%</b></td>'+
                  '</tr>'+
                  '<tr>'+
                  '  <td style="font-size: 11px;"><b>(%) EJEC.</b></td>'+
                  '  <td style="font-size: 11px; color:blue" align=right><b>' + trm_ejec_porc4 + '%</b></td>'+
                  '</tr>'+
            '</table>'
         '</td>';
    
    jQuery('#tab_acumulado').html(htmlAcumulado);

    // 3. Esperar a que el modal termine de abrirse para que Highcharts calcule bien el ancho del contenedor
    jQuery('#modal_cumplimiento').off('shown.bs.modal').on('shown.bs.modal', function () {
        
        var chart = new Highcharts.Chart({
            chart: {
                renderTo: 'parametro_efi',
                defaultSeriesType: 'line'
            },
            title: {
                text: 'EJECUCIÓN DE OPERACIONES POR TRIMESTRE'
            },
            subtitle: {
                text: 'Cód. Ope: ' + codigoOpe + ' - ' + objetivo
            },
            xAxis: {
                categories: ['', 'I TRIMESTRE', 'II TRIMESTRE', 'III TRIMESTRE', 'IV TRIMESTRE'],
                title: {
                    text: 'Periodos de Evaluación Trimestral'
                }
            },
            // 🔥 MEJORA: Doble eje Y para separar las cantidades físicas de los porcentajes (%)
            yAxis: [
                {
                    // Eje izquierdo (Índice 0): Para Meta Programada y Ejecutada
                    title: { text: 'Cantidad / Meta' },
                    min: 0
                },
                {
                    // Eje derecho (Índice 1): Exclusivo para el % de Cumplimiento (0 a 100%)
                    title: { text: 'Porcentaje (%)' },
                    opposite: true,
                    min: 0,
                    max: 100
                }
            ],
            tooltip: {
                enabled: true,
                shared: true // Muestra los 3 valores juntos en un solo cuadro al pasar el ratón
            },
            plotOptions: {
                line: {
                    dataLabels: {
                        enabled: true
                    },
                    enableMouseTracking: true
                }
            },
            series: [
                {
                    name: 'Meta Programado',
                    yAxis: 0, // Usa el eje izquierdo
                    color: '#0284c7',
                    dashStyle: 'Dash', // Línea segmentada para no tapar la ejecutada
                    data: [0, trm1, trm2, trm3, trm4]
                },
                {
                    name: 'Meta Ejecutado',
                    yAxis: 0, // Usa el eje izquierdo
                    color: '#16a34a',
                    data: [0, etrm1, etrm2, etrm3, etrm4]
                },
                {
                    name: '% Cumplimiento',
                    yAxis: 1, // Usa el eje derecho porcentual
                    color: '#f59e0b',
                    data: [0, porc1, porc2, porc3, porc4]
                }
            ]
        });

        chart = new Highcharts.Chart({
        chart: {
          renderTo: 'parametro_efi2',  // Le doy el nombre a la gráfica
          defaultSeriesType: 'line' // Pongo que tipo de gráfica es
        },
        title: {
          text: '% EJECUCIÓN ANUAL'  // Titulo (Opcional)
        },
        subtitle: {
          text: 'Cód. Ope: ' + codigoOpe + ' - ' + objetivo
        },
        // Pongo los datos en el eje de las 'X'
        xAxis: {
          categories: ['','I TRIMESTRE','II TRIMESTRE','III TRIMESTRE','IV TRIMESTRE'],
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
        series: [{
            name: '(%) Programado',
            data: [0,trm_porc1,trm_porc2,trm_porc3,trm_porc4]
          },
          {
            name: '(%) Ejecutado',
            data: [0,trm_ejec_porc1,trm_ejec_porc2,trm_ejec_porc3,trm_ejec_porc4]
          }],
        });
    });
  }



    /// Grafico cumplimiento por Objetivo Regional x ACP REGIONAL (GRAFICO) (Modulo : Evaluacion POA)
    var request_acp_op;
    function nivel_cumplimiento_form2_regional(dep_id) {
        var modalEl    = jQuery('#modal_grafico_xform2');
        var contenedor = modalEl.find('#tabla');

        modalEl.modal('show');

        // Resetear banner superior
        jQuery('#lbl_acp_regional_titulo').text('REGIONAL : CARGANDO...');
        jQuery('#lbl_porcentaje_acp').text('0.0%');
        jQuery('#lbl_estado_acp').text('CALCULANDO...');
        jQuery('#banner_calificacion_acp').css('background-color', '#64748b');

        contenedor.html(
            '<div class="text-center" style="padding: 60px 20px;">' +
                '<i class="fa fa-spinner fa-spin fa-3x" style="color: #1e3a8a; margin-bottom: 10px;"></i>' +
                '<h4 style="color: #1e3a8a; font-weight: bold;">Cargando Operaciones Alineadas...</h4>' +
            '</div>'
        );

        if (request_acp_op) {
            request_acp_op.abort();
        }

        request_acp_op = jQuery.ajax({
            url: base + "index.php/ejecucion/cevaluacion_form2/get_grafico_cumplimiento_regional", /// form2 
            type: "POST",
            dataType: "json",
            data: { dep_id: dep_id }
        });

        request_acp_op.done(function (response) {
            if (response.status === 'success') {
                // Actualizar banner superior
                jQuery('#lbl_acp_regional_titulo').text('REGIONAL : ' + response.regional);
                jQuery('#lbl_porcentaje_acp').text(response.promedio_global + '%');
                jQuery('#lbl_estado_acp').text(response.calificacion);
                jQuery('#banner_calificacion_acp').css('background-color', response.color_banner);

                // Insertar contenedores de gráficos y tabla
                contenedor.html(response.lista).show();

                // Ajustar altura dinámica según cantidad de operaciones
                var altoDinamico = Math.max(320, response.categorias.length * 30);
                jQuery('#container1, #container2').css('height', altoDinamico + 'px');

                setTimeout(function() {
                    dibujarGraficoBarrasHorizontal(
                        'container1',
                        'CUMPLIMIENTO DE OPERACIONES AL ' + response.trimestre + ' - ' + response.gestion,
                        response.categorias,
                        response.valores_trimestre,
                        '#38bdf8'
                    );

                    dibujarGraficoBarrasHorizontal(
                        'container2',
                        'CUMPLIMIENTO DE OPERACIONES ACUMULADO A LA GESTIÓN ' + response.gestion,
                        response.categorias,
                        response.valores_gestion,
                        '#0ea5e9'
                    );
                }, 300);

            } else {
                contenedor.html('<div class="alert alert-danger">No se pudieron obtener los datos de la ACP.</div>');
                alertify.error("ERROR AL RECUPERAR INFORMACION");
            }
        });
    }

  /////////////


                            // ------- MODULO REPORTE EVALUACION FORM 2

    /////=========== FORMULARIO DE EVUALUACION FORM 2 - REPORTES
    // Selecciona la Regional para ver su evaluacion del form 2
    var request;
    $("#d_id").change(function () {
        var dep_id = $(this).val();
        var contenedor = $('#lista_consolidado');

        // 1. Si no seleccionó ninguna opción válida, mostramos la portada por defecto
        if (dep_id === '') {
            contenedor.hide().html(
                '<div class="well">' +
                    '<div class="jumbotron">' +
                        '<h1>Evaluaci&oacute;n OPERACIONES ' + gestion + '</h1>' +
                    '</div>' +
                '</div>'
            ).fadeIn(400);
            return;
        }

        // 2. Definimos la ruta (URL) dinámicamente según sea Institucional (0) o Regional
        var metodo = (dep_id == 0) 
            ? "get_cuadro_evaluacion_formulario2_institucional" 
            : "get_cuadro_evaluacion_formulario2_regional";
        
        var url = base + "index.php/reporte_evalform2/crep_evalform2/" + metodo;
        var titulo_carga = (dep_id == 0) ? "Consolidado Evaluación Formulario N° 2 a nivel Institucional" : "Consolidado Evaluación Formulario N° 2 a nivel Regional";

        // 3. Mostramos un Loading moderno y centrado
        var html_loading = 
            '<div style="padding: 40px 20px; text-align: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; margin-top: 15px;">' +
                '<i class="fa fa-circle-o-notch fa-spin" style="font-size: 150px; color: #1c7368; margin-bottom: 12px;"></i>' +
                '<h4 style="margin: 0; font-weight: bold; color: #334155;">' + titulo_carga + '</h4>' +
                '<p style="margin: 5px 0 0 0; font-size: 12px; color: #64748b;">Por favor espere un momento...</p>' +
            '</div>';

        contenedor.html(html_loading);

        // 4. Si el usuario cambia rápido el select, cancelamos la petición anterior
        if (request) {
            request.abort();
        }

        // 5. Ejecutamos una sola petición AJAX limpia
        request = $.ajax({
            url: url,
            type: "POST",
            dataType: "json",
            data: { dep_id: dep_id }
        });

        request.done(function (response) {
            if (response.respuesta === 'correcto') {
                contenedor.html(response.tabla).show();

                // --- BLOQUE A: CONSOLIDADO INSTITUCIONAL (dep_id == 0) ---
                if (dep_id == 0 && response.categorias) {
                    setTimeout(function() {
                        new Highcharts.Chart({
                        chart: {
                            renderTo: 'grafico_combo_institucional',
                            type: 'column',
                            backgroundColor: '#ffffff'
                        },
                        title: {
                            text: 'COMPARATIVO DE CUMPLIMIENTO POR REGIONAL - GESTIÓN ' + response.gestion,
                            style: { fontSize: '14px', fontWeight: 'bold', color: '#0f172a' }
                        },
                        subtitle: {
                            text: 'Cumplimiento Trimestral vs. Acumulado a la Gestión (Trimestre N° ' + response.trimestre + ')',
                            style: { fontSize: '11px', color: '#64748b' }
                        },
                        credits: { enabled: false },
                        xAxis: {
                            categories: response.categorias,
                            crosshair: true,
                            labels: { style: { fontSize: '10px', fontWeight: 'bold', color: '#334155' } }
                        },
                        yAxis: {
                            min: 0,
                            max: 105,
                            title: { text: 'Porcentaje de Cumplimiento (%)' }
                        },
                        tooltip: {
                            shared: true,
                            valueSuffix: ' %'
                        },
                        plotOptions: {
                            column: {
                                borderRadius: 3,
                                groupPadding: 0.15,
                                pointPadding: 0.05,
                                dataLabels: {
                                    enabled: true,
                                    format: '{y}%',
                                    style: { fontSize: '8.5px', fontWeight: 'bold', color: '#1e293b', textOutline: 'none' }
                                }
                            }
                        },
                        series: [
                            {
                                name: '(%) Cumplimiento Trimestral',
                                data: response.serie_trm,
                                color: '#38bdf8' // Celeste
                            },
                            {
                                name: '(%) Cumplimiento Acumulado Gestión',
                                data: response.serie_ges,
                                color: '#1c7368' // Verde institucional
                            }
                        ]
                    });

                    }, 200);
                }
                // --- BLOQUE B: REPORTE REGIONAL INDIVIDUAL (dep_id != 0 - INTACTO) ---
                else if (dep_id != 0 && response.categorias) {
                    var altoDinamico = Math.max(550, response.categorias.length * 24);
                    jQuery('#grafico_trimestral, #grafico1').css('height', altoDinamico + 'px');

                    setTimeout(function() {
                        // Gráfico 1: Cumplimiento Trimestral
                        dibujarGraficoBarrasHorizontal(
                            'grafico_trimestral',
                            'CUMPLIMIENTO TRIMESTRAL (' + response.trimestre + ' - ' + response.gestion + ')',
                            response.categorias,
                            response.valores_trimestre,
                            '#38bdf8'
                        );

                        // Gráfico 2: Cumplimiento Anual / Gestión
                        dibujarGraficoBarrasHorizontal(
                            'grafico1',
                            'CUMPLIMIENTO ACUMULADO A LA GESTIÓN ' + response.gestion,
                            response.categorias,
                            response.valores_gestion,
                            '#10b981'
                        );
                    }, 200);
                }
            } else {
                contenedor.html('<div class="alert alert-danger"><i class="fa fa-warning"></i> No se pudieron cargar los datos solicitados.</div>');
                alertify.error("ERROR AL LISTAR");
            }
        });

        request.fail(function (jqXHR, textStatus) {
            if (textStatus !== 'abort') {
                contenedor.html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Error de conexi&oacute;n con el servidor. Intente nuevamente.</div>');
                alertify.error("ERROR DE SERVIDOR AL PROCESAR EL REPORTE");
            }
        });
    });




    ///// Exportar en PDF el cuadro de cumplimiento Insitucional Form 2 (Modulo : Reportes POA)
    function imprimirConsolidadoInstitucional() {
        if (typeof html2pdf === 'undefined') {
            alert('Error: No se encuentra cargada la librería html2pdf.');
            return;
        }

        var svgElement = document.querySelector('#grafico_combo_institucional svg');
        if (!svgElement) {
            alert('Error: El gráfico institucional aún no se ha renderizado.');
            return;
        }

        var anioGestion     = (typeof gestion !== 'undefined') ? gestion : new Date().getFullYear();
        var tituloPrincipal = 'CONSOLIDADO INSTITUCIONAL FORMULARIO N° 2 - GESTIÓN ' + anioGestion;
        var subtituloRep    = 'EVALUACIÓN DE CUMPLIMIENTO TRIMESTRAL Y ACUMULADO POR REGIONAL';

        // 1. Clonar y escalar el SVG a 935px de ancho y 300px de alto
        var svgClone      = svgElement.cloneNode(true);
        var anchoOriginal = parseFloat(svgElement.getAttribute('width')) || 950;
        var altoOriginal  = parseFloat(svgElement.getAttribute('height')) || 420;
        var anchoPdf      = 935;
        var altoPdf       = 300;

        svgClone.setAttribute('width', anchoPdf);
        svgClone.setAttribute('height', altoPdf);
        svgClone.setAttribute('viewBox', '0 0 ' + anchoOriginal + ' ' + altoOriginal);
        svgClone.setAttribute('preserveAspectRatio', 'none');

        var svgData = new XMLSerializer().serializeToString(svgClone);
        var imgSrc  = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

        // 2. Clonar y dar estilo ejecutivo a la tabla
        var tablaOriginal = document.getElementById('tabla_detalle_institucional');
        var tablaHtml     = '';

        if (tablaOriginal) {
            var tablaClon = tablaOriginal.cloneNode(true);
            tablaClon.removeAttribute('id');
            tablaClon.removeAttribute('class');

            tablaClon.style.width          = '100%';
            tablaClon.style.tableLayout    = 'fixed'; // Garantiza que nunca sobrepase el borde derecho
            tablaClon.style.borderCollapse = 'collapse';
            tablaClon.style.marginTop      = '10px';
            tablaClon.style.fontFamily     = 'Helvetica, Arial, sans-serif';

            // Reemplazar espacios simples por &nbsp; en textos para mantener separación limpia
            jQuery(tablaClon).find('th, td').each(function() {
                var textoLimpio = jQuery(this).text().replace(/\s+/g, ' ').trim();
                jQuery(this).html(textoLimpio.replace(/ /g, '&nbsp;'));
            });

            // Ajustar anchos exactos de las 11 columnas para que sumen 100% sin recortes
            var colgroup = 
                '<colgroup>' +
                    '<col style="width: 3.5%;">' +   // #
                    '<col style="width: 24.5%;">' +  // REGIONAL / DEPARTAMENTO
                    '<col style="width: 8%;">' +     // TOTAL OPE.
                    '<col style="width: 8%;">' +     // I TRM
                    '<col style="width: 8%;">' +     // II TRM
                    '<col style="width: 8%;">' +     // III TRM
                    '<col style="width: 8%;">' +     // IV TRM
                    '<col style="width: 8%;">' +     // I TRM (Gestión)
                    '<col style="width: 8%;">' +     // II TRM (Gestión)
                    '<col style="width: 8%;">' +     // III TRM (Gestión)
                    '<col style="width: 8%;">' +     // IV TRM (Gestión)
                '</colgroup>';
            jQuery(tablaClon).prepend(colgroup);

            // Estilo de encabezados (th)
            jQuery(tablaClon).find('th').css({
                'border': '1px solid #cbd5e1',
                'padding': '4px 2px',
                'font-size': '7.5px',
                'font-weight': 'bold',
                'text-align': 'center',
                'font-family': 'Helvetica, Arial, sans-serif',
                'line-height': '1.2'
            });

            // Estilo de filas alternas (zebra) y celdas del cuerpo (tbody td)
            jQuery(tablaClon).find('tbody tr:even').css('background-color', '#ffffff');
            jQuery(tablaClon).find('tbody tr:odd').css('background-color', '#f8fafc');

            jQuery(tablaClon).find('tbody td').css({
                'border': '1px solid #e2e8f0',
                'padding': '3.5px 5px',
                'font-size': '7.5px',
                'color': '#1e293b',
                'font-family': 'Helvetica, Arial, sans-serif',
                'line-height': '1.2'
            });

            // Estilo de la fila de Promedio Institucional (tfoot td)
            jQuery(tablaClon).find('tfoot td').css({
                'border': '1px solid #94a3b8',
                'border-top': '2px solid #1e3a8a',
                'background-color': '#eff6ff',
                'color': '#1e3a8a',
                'font-weight': 'bold',
                'padding': '4.5px 5px',
                'font-size': '8px',
                'font-family': 'Helvetica, Arial, sans-serif'
            });

            tablaHtml = tablaClon.outerHTML;
        }

        // 3. Contenedor con ancho exacto (955px) y padding lateral para que los bordes izquierdo y derecho se vean completos
        var contenedorLimpio = document.createElement('div');
        contenedorLimpio.style.fontFamily      = 'Helvetica, Arial, sans-serif';
        contenedorLimpio.style.width           = '955px';
        contenedorLimpio.style.padding         = '0 4px';
        contenedorLimpio.style.margin          = '0 auto';
        contenedorLimpio.style.backgroundColor = '#ffffff';
        contenedorLimpio.style.boxSizing       = 'border-box';

        contenedorLimpio.innerHTML = 
            '<div style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px; margin-bottom: 8px; text-align: center; box-sizing: border-box; background: #ffffff;">' +
                '<img src="' + imgSrc + '" style="width: 100%; height: ' + altoPdf + 'px; display: block; margin: 0 auto;" />' +
            '</div>' +
            tablaHtml;

        // 4. Configurar opciones de descarga PDF (Márgenes simétricos: 12mm izquierda y 12mm derecha)
        var opciones = {
            margin:      new Array(23, 12, 12, 12),
            filename:    'Consolidado_Institucional_Form2_' + anioGestion + '.pdf',
            image:       { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, scrollX: 0, scrollY: 0, useCORS: true, logging: false },
            jsPDF:       { unit: 'mm', format: 'letter', orientation: 'landscape' }
        };

        // 5. Generar PDF e inyectar Cabecera y Pie de página alineados al margen de 12mm
        html2pdf().set(opciones).from(contenedorLimpio).toPdf().get('pdf').then(function (pdf) {
            var totalPaginas = pdf.internal.getNumberOfPages();
            var anchoPagina  = pdf.internal.pageSize.getWidth();
            var altoPagina   = pdf.internal.pageSize.getHeight();

            for (var i = 1; i <= totalPaginas; i++) {
                pdf.setPage(i);

                // Caja de cabecera institucional
                pdf.setFillColor(240, 249, 255);
                pdf.setDrawColor(186, 230, 253);
                pdf.roundedRect(12, 6, anchoPagina - 24, 14.5, 2, 2, 'FD');

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(11);
                pdf.setTextColor(30, 58, 138);
                pdf.text(tituloPrincipal, anchoPagina / 2, 12.2, { align: 'center' });

                pdf.setFontSize(8.5);
                pdf.setTextColor(2, 132, 199);
                pdf.text(subtituloRep, anchoPagina / 2, 17.5, { align: 'center' });

                // Pie de página
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(8);
                pdf.setTextColor(100, 116, 139);
                pdf.text('Fecha de impresión: ' + new Date().toLocaleDateString('es-ES'), 12, altoPagina - 6);
                pdf.text('Página ' + i + ' de ' + totalPaginas, anchoPagina - 12, altoPagina - 6, { align: 'right' });
            }
        }).save();
    }


    //// Exportar Operaciones Intiucional / regionales
    function exportarExcelInstitucional(dep_id, btnElement) {
        if (dep_id === undefined || dep_id === null || dep_id === '') {
            dep_id = jQuery('#d_id').val() || 0;
        }

        var boton = btnElement ? jQuery(btnElement) : jQuery('#btn_exportar_excel');
        var textoOriginal = boton.html();
        var url = base + "index.php/exportar_form2/" + dep_id;

        // 1. Activar estado Loading y deshabilitar botón para evitar múltiples clics
        boton.prop('disabled', true)
             .css('opacity', '0.85')
             .html('<i class="fa fa-spinner fa-spin"></i> GENERANDO EXCEL...');

        // 2. Descargar el archivo en segundo plano mediante fetch
        fetch(url)
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }

                // Extraer el nombre exacto del archivo desde la cabecera Content-Disposition de PHP
                var disposition = response.headers.get('Content-Disposition');
                var filename = 'Consolidado_Formulario_N2_' + dep_id + '.xls';
                if (disposition && disposition.indexOf('filename=') !== -1) {
                    var matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, '');
                    }
                }

                return response.blob().then(function(blob) {
                    return { blob: blob, filename: filename };
                });
            })
            .then(function(archivo) {
                // 3. Crear enlace temporal y disparar la descarga en el navegador
                var downloadUrl = window.URL.createObjectURL(archivo.blob);
                var a = document.createElement('a');
                a.style.display = 'none';
                a.href = downloadUrl;
                a.download = archivo.filename;
                document.body.appendChild(a);
                a.click();

                // Limpiar memoria y restaurar botón
                window.URL.revokeObjectURL(downloadUrl);
                document.body.removeChild(a);
                
                if (typeof alertify !== 'undefined') {
                    alertify.success("Archivo Excel descargado correctamente.");
                }
            })
            .catch(function(error) {
                console.error('Error al exportar Excel:', error);
                if (typeof alertify !== 'undefined') {
                    alertify.error("Error al generar el archivo Excel.");
                } else {
                    alert("Ocurrió un error al generar el archivo Excel.");
                }
            })
            .finally(function() {
                // 4. Restaurar el botón a su estado original (éxito o error)
                boton.prop('disabled', false)
                     .css('opacity', '1')
                     .html(textoOriginal);
            });
    }



    /// Grafico cumplimiento por Objetivo Regional x ACP REGIONAL (GRAFICO) (Modulo : Reportes POA)
    var request_acp_op;
    function nivel_cumplimiento_rep_form2_regional(og_id, dep_id) {
        var modalEl    = jQuery('#modal_grafico_xform2');
        var contenedor = modalEl.find('#tabla');

        modalEl.modal('show');

        // Resetear banner superior
        jQuery('#lbl_acp_regional_titulo').text('REGIONAL : CARGANDO...');
        jQuery('#lbl_porcentaje_acp').text('0.0%');
        jQuery('#lbl_estado_acp').text('CALCULANDO...');
        jQuery('#banner_calificacion_acp').css('background-color', '#64748b');

        contenedor.html(
            '<div class="text-center" style="padding: 60px 20px;">' +
                '<i class="fa fa-spinner fa-spin fa-3x" style="color: #1e3a8a; margin-bottom: 10px;"></i>' +
                '<h4 style="color: #1e3a8a; font-weight: bold;">Cargando Operaciones Alineadas...</h4>' +
            '</div>'
        );

        if (request_acp_op) {
            request_acp_op.abort();
        }

        request_acp_op = jQuery.ajax({
            url: base + "index.php/reporte_evalform2/crep_evalform2/ver_datos_avance_oregional_acp", /// form2 x acp
            type: "POST",
            dataType: "json",
            data: { og_id: og_id, dep_id: dep_id }
        });

        request_acp_op.done(function (response) {
            if (response.status === 'success') {
                // Actualizar banner superior
                jQuery('#lbl_acp_regional_titulo').text('REGIONAL : ' + response.regional);
                jQuery('#lbl_porcentaje_acp').text(response.promedio_global + '%');
                jQuery('#lbl_estado_acp').text(response.calificacion);
                jQuery('#banner_calificacion_acp').css('background-color', response.color_banner);

                // Insertar contenedores de gráficos y tabla
                contenedor.html(response.lista).show();

                // Ajustar altura dinámica según cantidad de operaciones
                var altoDinamico = Math.max(320, response.categorias.length * 30);
                jQuery('#container1, #container2').css('height', altoDinamico + 'px');

                setTimeout(function() {
                    dibujarGraficoBarrasHorizontal(
                        'container1',
                        'CUMPLIMIENTO DE OPERACIONES AL ' + response.trimestre + ' - ' + response.gestion,
                        response.categorias,
                        response.valores_trimestre,
                        '#38bdf8'
                    );

                    dibujarGraficoBarrasHorizontal(
                        'container2',
                        'CUMPLIMIENTO DE OPERACIONES ACUMULADO A LA GESTIÓN ' + response.gestion,
                        response.categorias,
                        response.valores_gestion,
                        '#0ea5e9'
                    );
                }, 300);

            } else {
                contenedor.html('<div class="alert alert-danger">No se pudieron obtener los datos de la ACP.</div>');
                alertify.error("ERROR AL RECUPERAR INFORMACION");
            }
        });
    }

    //// dibuja el Grafico de las Operaciones Form 2
    function dibujarGraficoBarrasHorizontal(renderId, titulo, categorias, valores, colorBarra) {
        if (!document.getElementById(renderId)) {
            return;
        }

        // Cálculo dinámico del ancho (grosor) de cada barra según cantidad de operaciones
        var totalOps = categorias.length;
        var grosorBarra = 16;

        if (totalOps <= 3) {
            grosorBarra = 40;
        } else if (totalOps <= 6) {
            grosorBarra = 26;
        } else if (totalOps <= 10) {
            grosorBarra = 20;
        }

        // AJUSTE CLAVE: Leer los objetos {y, color, borderColor, borderWidth} enviados desde PHP
        var datosEstilizados = valores.map(function(val) {
            if (typeof val === 'object' && val !== null) {
                return {
                    y: parseFloat(val.y) || 0,
                    color: val.color || colorBarra,
                    borderColor: val.borderColor || 'transparent',
                    borderWidth: val.borderWidth || 0
                };
            }

            // Respaldo por si algún otro gráfico envía solo números simples
            var numero = parseFloat(val) || 0;
            return { y: numero, color: colorBarra };
        });

        new Highcharts.Chart({
            chart: {
                renderTo: renderId,
                type: 'bar',
                backgroundColor: '#ffffff',
                style: { fontFamily: "'Segoe UI', Roboto, Helvetica, Arial, sans-serif" },
                spacingTop: 15,
                spacingBottom: 15
            },
            title: {
                text: titulo,
                align: 'left',
                margin: 20,
                style: { fontSize: '13px', fontWeight: '700', color: '#0f172a', textTransform: 'uppercase' }
            },
            credits: { enabled: false },
            xAxis: {
                categories: categorias,
                lineColor: '#cbd5e1',
                tickLength: 0,
                labels: {
                    style: { fontSize: '11px', fontWeight: '600', color: '#334155' }
                }
            },
            yAxis: {
                min: 0,
                max: 108,
                tickInterval: 20,
                gridLineColor: '#f1f5f9',
                gridLineDashStyle: 'Dash',
                title: {
                    text: 'Porcentaje de Cumplimiento (%)',
                    style: { fontSize: '11px', color: '#64748b', fontWeight: '600' }
                },
                labels: {
                    format: '{value}%',
                    style: { fontSize: '10px', color: '#64748b' }
                },
                plotLines: [{
                    value: 100,
                    color: '#94a3b8',
                    dashStyle: 'ShortDot',
                    width: 1.5,
                    zIndex: 3
                }]
            },
            legend: { enabled: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                borderWidth: 0,
                borderRadius: 6,
                style: { color: '#ffffff', fontSize: '11px' },
                headerFormat: '<span style="font-size: 11px; font-weight: bold; color: #38bdf8;">{point.key}</span><br/>',
                pointFormat: 'Cumplimiento: <b>{point.y}%</b>'
            },
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    pointWidth: grosorBarra,
                    // Se quitó borderWidth: 0 fijo aquí para permitir el borde gris en las barras blancas
                    dataLabels: {
                        enabled: true,
                        format: '{y}%',
                        align: 'left',
                        x: 6,
                        style: {
                            fontSize: (totalOps <= 4) ? '11px' : '10px',
                            fontWeight: '700',
                            color: '#1e293b',
                            textOutline: 'none'
                        }
                    }
                }
            },
            series: [{
                name: 'Cumplimiento',
                data: datosEstilizados
            }]
        });
    }


    //// imprimir Grafico de la ACP alineado a la Operacion -> FORM2 A ACP 
//    function imprimirGraficoAcp() {
//     if (typeof html2pdf === 'undefined') {
//         alert('Error: No se encuentra cargada la librería html2pdf.');
//         return;
//     }

//     var tituloGestion    = jQuery('#area_impresion_acp h4').first().text().trim() || 'CUADRO DE EVALUACIÓN DE OPERACIONES';
//     var subtituloReg     = jQuery('#lbl_acp_regional_titulo').text().trim() || 'REGIONAL';
//     var tituloAcp        = jQuery('#tit_acp').text().replace(/\s+/g, ' ').trim() || '';
//     var porcentajeGlobal = jQuery('#lbl_porcentaje_acp').text().trim() || '0.0%';
//     var estadoCalif      = jQuery('#lbl_estado_acp').text().trim() || '';
//     var colorBanner      = jQuery('#banner_calificacion_acp').css('background-color') || '#f59e0b';

//     var svg1 = document.querySelector('#container1 svg');
//     var svg2 = document.querySelector('#container2 svg');
//     if (!svg1 || !svg2) {
//         alert('Error: Los gráficos aún no se han renderizado por completo.');
//         return;
//     }

//     function convertirSvgAImagen(svgElement, anchoPdf, altoPdf) {
//         var svgClone      = svgElement.cloneNode(true);
//         var anchoOriginal = parseFloat(svgElement.getAttribute('width')) || 650;
//         var altoOriginal  = parseFloat(svgElement.getAttribute('height')) || 350;

//         svgClone.setAttribute('width', anchoPdf);
//         svgClone.setAttribute('height', altoPdf);
//         svgClone.setAttribute('viewBox', '0 0 ' + anchoOriginal + ' ' + altoOriginal);
//         svgClone.setAttribute('preserveAspectRatio', 'none');

//         var svgData = new XMLSerializer().serializeToString(svgClone);
//         var imgSrc  = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

//         return '<img src="' + imgSrc + '" style="width: 100%; height: ' + altoPdf + 'px; display: block;" />';
//     }

//     // Alto ajustado a 320px para compensar el espacio extra de la cabecera con el título de la ACP
//     var imgGrafico1 = convertirSvgAImagen(svg1, 700, 350);
//     var imgGrafico2 = convertirSvgAImagen(svg2, 700, 350);

//     var contenedorLimpio = document.createElement('div');
//     contenedorLimpio.style.fontFamily      = 'Arial, Helvetica, sans-serif';
//     contenedorLimpio.style.width           = '100%';
//     contenedorLimpio.style.backgroundColor = '#ffffff';
//     contenedorLimpio.style.letterSpacing   = 'normal';
//     contenedorLimpio.style.wordSpacing     = 'normal';

//     var bannerHtml = 
//         '<div style="background-color: ' + colorBanner + '; color: #ffffff; text-align: center; padding: 9px; border-radius: 5px; margin-bottom: 10px;">' +
//             '<div style="font-size: 26px; font-weight: bold; line-height: 1.1;">' + porcentajeGlobal + '</div>' +
//             '<div style="font-size: 10px; font-weight: bold; margin-top: 2px;">' + estadoCalif + '</div>' +
//         '</div>';

//     contenedorLimpio.innerHTML = 
//         '<div>' +
//             bannerHtml +
//             '<div style="width: 100%; text-align: center; border: 1px solid #e2e8f0; border-radius: 5px; padding: 6px; margin-bottom: 10px;">' +
//                 imgGrafico1 +
//             '</div>' +
//             '<div style="width: 100%; text-align: center; border: 1px solid #e2e8f0; border-radius: 5px; padding: 6px;">' +
//                 imgGrafico2 +
//             '</div>' +
//         '</div>';

//     var regionalArchivo = subtituloReg.replace('REGIONAL :', '').trim() || 'Regional';
//     var opciones = {
//         // Margen superior ampliado a 36mm para albergar las 3 líneas de cabecera (Título, Regional y ACP)
//         margin:       new Array(36, 12, 14, 12),
//         filename:     'Grafico_Cumplimiento_ACP_' + regionalArchivo + '.pdf',
//         image:        { type: 'jpeg', quality: 0.98 },
//         html2canvas:  { scale: 2, useCORS: true, logging: false },
//         jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
//     };

//     html2pdf().set(opciones).from(contenedorLimpio).toPdf().get('pdf').then(function (pdf) {
//         var totalPaginas = pdf.internal.getNumberOfPages();
//         var anchoPagina  = pdf.internal.pageSize.getWidth();
//         var altoPagina   = pdf.internal.pageSize.getHeight();

//         for (var i = 1; i <= totalPaginas; i++) {
//             pdf.setPage(i);

//             // Caja de cabecera institucional ampliada (25mm de alto)
//             pdf.setFillColor(240, 249, 255);
//             pdf.setDrawColor(186, 230, 253);
//             pdf.roundedRect(12, 7, anchoPagina - 24, 25, 2, 2, 'FD');

//             // Línea 1: Título de gestión
//             pdf.setFont('helvetica', 'bold');
//             pdf.setFontSize(10.5);
//             pdf.setTextColor(30, 58, 138);
//             pdf.text(tituloGestion, anchoPagina / 2, 13, { align: 'center' });

//             // Línea 2: Regional
//             pdf.setFontSize(9);
//             pdf.setTextColor(2, 132, 199);
//             pdf.text(subtituloReg, anchoPagina / 2, 18.5, { align: 'center' });

//             // Línea 3: Título de la ACP (ajustado a máximo 2 líneas si es muy largo)
//             if (tituloAcp) {
//                 pdf.setFont('helvetica', 'bold');
//                 pdf.setFontSize(8);
//                 pdf.setTextColor(30, 41, 59);
//                 var lineasAcp = pdf.splitTextToSize(tituloAcp, anchoPagina - 32);
//                 pdf.text(lineasAcp.slice(0, 2), anchoPagina / 2, 24, { align: 'center' });
//             }

//             // Pie de página
//             pdf.setFont('helvetica', 'normal');
//             pdf.setFontSize(8);
//             pdf.setTextColor(100, 116, 139);
//             pdf.text('Fecha de impresión: ' + new Date().toLocaleDateString('es-ES'), 12, altoPagina - 7);
//             pdf.text('Página ' + i + ' de ' + totalPaginas, anchoPagina - 12, altoPagina - 7, { align: 'right' });
//         }
//     }).save();
// }


/////// impresion de los graficos CONSOLIDADO POR REGIONAL FORM2 REGIONAL (Modulo : Reportes POA)
function imprimirGraficoIndividual(idGrafico, idBanner, tipoReporte) {
    if (typeof html2pdf === 'undefined') {
        alert('Error: No se encuentra cargada la librería html2pdf.');
        return;
    }

    var svgElement = document.querySelector('#' + idGrafico + ' svg');
    if (!svgElement) {
        alert('Error: El gráfico aún no se ha renderizado.');
        return;
    }

    var tituloPrincipal = jQuery('#wid-id-3 h2').first().text().trim() || 'Evaluación del Formulario N° 2 (Operaciones)';
    var subtituloTipo   = (tipoReporte === 'Trimestral') ? 'REPORTE DE CUMPLIMIENTO TRIMESTRAL' : 'REPORTE DE CUMPLIMIENTO ANUAL / GESTIÓN';

    var bannerEl    = jQuery('#' + idBanner);
    var colorBanner = bannerEl.css('background-color') || '#1c7368';
    var porcentaje  = bannerEl.find('div').eq(0).text().trim() || '0.0%';
    var textoCalif  = bannerEl.find('div').eq(1).text().trim() || '';

    var svgClone      = svgElement.cloneNode(true);
    var anchoOriginal = parseFloat(svgElement.getAttribute('width')) || 700;
    var altoOriginal  = parseFloat(svgElement.getAttribute('height')) || 550;
    var anchoPdfPx    = 700;
    var altoPdfPx     = Math.round((altoOriginal * anchoPdfPx) / anchoOriginal);

    svgClone.setAttribute('width', anchoPdfPx);
    svgClone.setAttribute('height', altoPdfPx);
    svgClone.setAttribute('viewBox', '0 0 ' + anchoOriginal + ' ' + altoOriginal);

    var svgData = new XMLSerializer().serializeToString(svgClone);
    var imgSrc  = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

    var contenedorLimpio = document.createElement('div');
    contenedorLimpio.style.fontFamily      = 'Arial, Helvetica, sans-serif';
    contenedorLimpio.style.width           = '100%';
    contenedorLimpio.style.backgroundColor = '#ffffff';
    contenedorLimpio.style.letterSpacing   = 'normal';
    contenedorLimpio.style.wordSpacing     = 'normal';

    var bannerHtml = 
        '<div style="background-color: ' + colorBanner + '; color: #ffffff; text-align: center; padding: 12px; border-radius: 5px; margin-bottom: 12px;">' +
            '<div style="font-size: 30px; font-weight: bold; line-height: 1.1;">' + porcentaje + '</div>' +
            '<div style="font-size: 11px; font-weight: bold; margin-top: 4px;">' + textoCalif + '</div>' +
        '</div>';

    var graficoHtml = 
        '<div style="width: 100%; text-align: center; border: 1px solid #e2e8f0; border-radius: 5px; padding: 8px; margin-bottom: 16px;">' +
            '<img src="' + imgSrc + '" style="width: 100%; height: auto; display: block; margin: 0 auto;" />' +
        '</div>';

    // RANGO DE CUMPLIMIENTO CON DIVS (Inmune a estilos externos de tablas)
    // 7. LEYENDA DE RANGOS DE CUMPLIMIENTO (Compatible 100% con html2canvas sin superposición de texto)
    var tituloLeyenda = (tipoReporte === 'Trimestral') ? 'CUMPLIMIENTO TRIMESTRAL ACUMULADO' : 'CUMPLIMIENTO ANUAL / GESTIÓN';
    var textoSinProg  = (tipoReporte === 'Trimestral') ? 'Sin programación al Trimestre' : 'Sin programación en la Gestión';

    var estiloTexto = 'font-family: Helvetica, sans-serif; font-size: 10px; color: #0f172a; letter-spacing: 0.4px; word-spacing: 2px; white-space: nowrap;';

    var leyendaHtml = 
        '<div style="width: 75%; page-break-inside: avoid; margin-top: 12px; ' + estiloTexto + '">' +
            '<div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase; ' + estiloTexto + '">' +
                tituloLeyenda +
            '</div>' +
            '<div style="border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a; padding: 6px 8px; background-color: #ffffff;">' +
                '<div style="display: table; width: 100%; margin-bottom: 5px;">' +
                    '<div style="display: table-cell; width: 26px; vertical-align: middle;">' +
                        '<span style="display: inline-block; width: 13px; height: 13px; border-radius: 50%; background-color: #ffffff; border: 1.5px solid #64748b;"></span>' +
                    '</div>' +
                    '<div style="display: table-cell; vertical-align: middle; ' + estiloTexto + '">' +
                        'BLANCO:&nbsp;&nbsp;<b style="' + estiloTexto + '">' + textoSinProg + '</b>' +
                    '</div>' +
                '</div>' +
                '<div style="display: table; width: 100%; margin-bottom: 5px;">' +
                    '<div style="display: table-cell; width: 26px; vertical-align: middle;">' +
                        '<span style="display: inline-block; width: 13px; height: 13px; border-radius: 50%; background-color: #ef4444; border: 1px solid #991b1b;"></span>' +
                    '</div>' +
                    '<div style="display: table-cell; vertical-align: middle; ' + estiloTexto + '">' +
                        'ROJO:&nbsp;&nbsp;<b style="' + estiloTexto + '">Entre 0% y 50%</b>' +
                    '</div>' +
                '</div>' +
                '<div style="display: table; width: 100%; margin-bottom: 5px;">' +
                    '<div style="display: table-cell; width: 26px; vertical-align: middle;">' +
                        '<span style="display: inline-block; width: 13px; height: 13px; border-radius: 50%; background-color: #f59e0b; border: 1px solid #b45309;"></span>' +
                    '</div>' +
                    '<div style="display: table-cell; vertical-align: middle; ' + estiloTexto + '">' +
                        'AMARILLO:&nbsp;&nbsp;<b style="' + estiloTexto + '">Entre 51% y 80%</b>' +
                    '</div>' +
                '</div>' +
                '<div style="display: table; width: 100%;">' +
                    '<div style="display: table-cell; width: 26px; vertical-align: middle;">' +
                        '<span style="display: inline-block; width: 13px; height: 13px; border-radius: 50%; background-color: #10b981; border: 1px solid #047857;"></span>' +
                    '</div>' +
                    '<div style="display: table-cell; vertical-align: middle; ' + estiloTexto + '">' +
                        'VERDE:&nbsp;&nbsp;<b style="' + estiloTexto + '">Entre 81% y 100%</b>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';


    // IMPORTANTE: Concatenar las 3 partes (Banner + Gráfico + Leyenda)
    contenedorLimpio.innerHTML = bannerHtml + graficoHtml + leyendaHtml;
        var nombreArchivo = 'Grafico_' + tipoReporte + '_Form2.pdf';
        var opciones = {
            margin:       new Array(28, 12, 14, 12),
            filename:     nombreArchivo,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, logging: false },
            jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
        };

        html2pdf().set(opciones).from(contenedorLimpio).toPdf().get('pdf').then(function (pdf) {
            var totalPaginas = pdf.internal.getNumberOfPages();
            var anchoPagina  = pdf.internal.pageSize.getWidth();
            var altoPagina   = pdf.internal.pageSize.getHeight();

            for (var i = 1; i <= totalPaginas; i++) {
                pdf.setPage(i);

                // Cabecera institucional
                pdf.setFillColor(240, 249, 255);
                pdf.setDrawColor(186, 230, 253);
                pdf.roundedRect(12, 8, anchoPagina - 24, 16, 2, 2, 'FD');

                pdf.setFont('helvetica', 'bold');
                pdf.setFontSize(10.5);
                pdf.setTextColor(30, 58, 138);
                pdf.text(tituloPrincipal, anchoPagina / 2, 14.5, { align: 'center' });

                pdf.setFontSize(9);
                pdf.setTextColor(2, 132, 199);
                pdf.text(subtituloTipo, anchoPagina / 2, 20.5, { align: 'center' });

                // Pie de página
                pdf.setFont('helvetica', 'normal');
                pdf.setFontSize(8);
                pdf.setTextColor(100, 116, 139);
                pdf.text('Fecha de impresión: ' + new Date().toLocaleDateString('es-ES'), 12, altoPagina - 7);
                pdf.text('Página ' + i + ' de ' + totalPaginas, anchoPagina - 12, altoPagina - 7, { align: 'right' });
            }
        }).save();
    }



    ///// Grafico Form 2 Institucional (0 Total Operaciones | 1 Priorizados) (Modulo : Reportes POA)
    var request_acp_op;
    function nivel_cumplimiento_form2_institucional_total_o_priorizados(tp) {
        // tp 0 : Total Operaciones Institucional
        // tp 1 : Total Operaciones Institucional Priorizados
        var modalEl    = jQuery('#modal_grafico_xform2');
        var contenedor = modalEl.find('#tabla');

        modalEl.modal('show');

        // Resetear título superior mientras carga
        jQuery('#lbl_acp_regional_titulo').text('INSTITUCIONAL : CARGANDO...');

        contenedor.html(
            '<div class="text-center" style="padding: 60px 20px;">' +
                '<i class="fa fa-spinner fa-spin fa-3x" style="color: #1e3a8a; margin-bottom: 10px;"></i>' +
                '<h4 style="color: #1e3a8a; font-weight: bold;">Cargando Operaciones ...</h4>' +
                '<p class="text-muted">Procesando indicadores trimestrales y anuales, por favor espere.</p>' +
            '</div>'
        );

        if (request_acp_op) {
            request_acp_op.abort();
        }

        request_acp_op = jQuery.ajax({
            url: base + "index.php/reporte_evalform2/crep_evalform2/get_grafico_cumplimiento_form2_institucional_total_o_priorizados",
            type: "POST",
            dataType: "json",
            data: { tp: tp }
        });

        request_acp_op.done(function (response) {
            if (response.status === 'success') {
                // 1. Actualizar subtítulo institucional
                if (tp == 0) {
                    titulo='INSTITUCIONAL';
                    jQuery('#lbl_acp_regional_titulo').text('INSTITUCIONAL FORMULARIO N° 2 - Total Operaciones');
                } else {
                    titulo='INSTITUCIONAL (PRIORIZADOS)';
                    jQuery('#lbl_acp_regional_titulo').text('INSTITUCIONAL FORMULARIO N° 2 - Operaciones Priorizadas');
                }

                // 2. Insertar primero el HTML (contiene los 2 banners, #container1, #container2 y la tabla)
                contenedor.html(response.lista).show();

                // 3. Actualizar ambos banners de calificación (Trimestral y Anual)
                jQuery('#lbl_porcentaje_acp_trm').text(response.promedio_trm + '%');
                jQuery('#lbl_estado_acp_trm').text('' + response.calificacion_trm);
                jQuery('#banner_calificacion_acp_trm').css('background-color', response.color_banner_trm);

                jQuery('#lbl_porcentaje_acp').text(response.promedio_global + '%');
                jQuery('#lbl_estado_acp').text('' + response.calificacion);
                jQuery('#banner_calificacion_acp').css('background-color', response.color_banner);

                // 4. Ajustar altura dinámica según la cantidad de operaciones
                var altoDinamico = Math.max(550, response.categorias.length * 24);
                jQuery('#container1, #container2').css('height', altoDinamico + 'px');

                // 5. Renderizar ambos gráficos Highcharts
                setTimeout(function() {
                    dibujarGraficoBarrasHorizontal(
                        'container1',
                        '(%) CUMPLIMIENTO OPERACIONES '+titulo+' ACUMULADO AL ' + response.trimestre + ' - ' + response.gestion,
                        response.categorias,
                        response.valores_trimestre,
                        '#38bdf8'
                    );

                    dibujarGraficoBarrasHorizontal(
                        'container2',
                        '(%) CUMPLIMIENTO OPERACIONES '+titulo+' A LA GESTIÓN ' + response.gestion,
                        response.categorias,
                        response.valores_gestion,
                        '#0ea5e9'
                    );
                }, 300);

                    } else {
                contenedor.html('<div class="alert alert-danger"><i class="fa fa-warning"></i> No se pudieron obtener los datos solicitados.</div>');
                if (typeof alertify !== 'undefined') {
                    alertify.error("ERROR AL RECUPERAR INFORMACIÓN");
                }
            }
        });

        request_acp_op.fail(function (jqXHR, textStatus, errorThrown) {
            if (textStatus !== 'abort') {
                // Mostramos el error exacto del servidor dentro del modal para facilitar la depuración
                contenedor.html(
                    '<div class="alert alert-danger">' +
                        '<h4><i class="fa fa-times-circle"></i> Error del Servidor (' + jqXHR.status + ' - ' + errorThrown + ')</h4>' +
                        '<p>Detalle t&eacute;cnico: ' + jqXHR.responseText + '</p>' +
                    '</div>'
                );

                if (typeof alertify !== 'undefined') {
                    alertify.error("ERROR DE CONEXIÓN");
                }
            }
        });

        request_acp_op.fail(function (jqXHR, textStatus) {
            if (textStatus !== 'abort') {
                contenedor.html('<div class="alert alert-danger"><i class="fa fa-times-circle"></i> Error de conexi&oacute;n con el servidor.</div>');
                alertify.error("ERROR DE CONEXIÓN");
            }
        });
    }
