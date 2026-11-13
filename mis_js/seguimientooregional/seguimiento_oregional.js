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



  /// Actualiza el Tipo de meta (Relativo)
    // function select_tp_meta(id,or_id){ /// 
    //   var url = base+"index.php/ejecucion/cevaluacion_form2/update_datos_tpmeta";
    //   var request;
    //   if (request) {
    //       request.abort();
    //   }
    //   request = $.ajax({
    //       url: url,
    //       type: "POST",
    //       dataType: 'json',
    //       data: "or_id="+or_id+"&id="+id
    //   });

    //   request.done(function (response, textStatus, jqXHR) {

    //   if (response.respuesta == 'correcto') {
    //       alertify.success("Seleccion procesada correctamente ...");
    //   }
    //   else{
    //       alertify.error("ERROR AL RECUPERAR INFORMACION");
    //   }

    //   });
    // }



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


  // /// Parametros de cumplimiento
  // function parametros_cumplimiento(valor) {
  //   resp='';
  //   if(valor>0 & valor<=50){
  //     resp='<b>INSATISFACTORIO</b>';
  //   }
  //   if(valor>50 & valor<=75){
  //    resp='<b>REGULAR</b>'; 
  //   }
  //   if(valor>75 & valor<=99){
  //    resp='<b>BUENO</b>'; 
  //   }
  //   if(valor==100){
  //    resp='<b>OPTIMO</b>'; 
  //   }

  //   return resp;
  // }


  /// Lista de Actividades Priorizados por cada Objetivo Regional FORM 2
  function ver_actividades_priorizados(or_id,dep_id) {
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

  // Variables globales para controlar las instancias de los gráficos de este modal 2027
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





  /// GRADO DE CUMPLIMIENTO DE OPERACIONES CONSOLIDADO POR REGIONAL (GRAFICO) FORM 2 2027
  // function nivel_cumplimiento_operaciones_grafico(dep_id,trm_id) {

  //   var url = base+"index.php/ejecucion/cevaluacion_form2/get_cumplimiento_operaciones_grafico";
  //   var request;
  //   if (request) {
  //       request.abort();
  //   }
  //   request = $.ajax({
  //       url: url,
  //       type: "POST",
  //       dataType: 'json',
  //       data: "dep_id="+dep_id+"&trm_id="+trm_id
  //   });

  //   request.done(function (response, textStatus, jqXHR) {

  //     if (response.respuesta == 'correcto') {
  //       $('#titulo_graf').html(response.titulo_graf);
  //       $('#tabla').html(response.tabla);
  //     }
  //     else{
  //         alertify.error("ERROR AL RECUPERAR INFORMACION");
  //     }

  //   });
  // }


  /////////////
  function nivel_cumplimiento_operaciones_grafico(dep_id, tmes) {
    // 1. Limpiar textos mientras llega la respuesta del servidor
    jQuery('#lbl_regional_titulo').text('REGIONAL : CARGANDO...');
    jQuery('#lbl_porcentaje_global').text('...');
    jQuery('#lbl_estado_calificacion').text('PROCESANDO DATOS...');
    jQuery('#banner_calificacion_regional').css('background-color', '#64748b');
    jQuery('#grafico_barras_regional').html('<div class="text-center" style="padding: 80px;"><i class="fa fa-spinner fa-spin fa-3x" style="color:#1e3a8a;"></i></div>');

    // 2. Petición AJAX al controlador
    jQuery.ajax({
        url: base + "index.php/ejecucion/cevaluacion_form2/get_grafico_cumplimiento_regional",
        type: "POST",
        dataType: "json",
        data: { dep_id: dep_id, tmes: tmes },
        success: function(response) {
            if (response.status === 'success') {
                // Actualizar banner superior
                jQuery('#lbl_regional_titulo').text('REGIONAL : ' + response.regional);
                jQuery('#lbl_porcentaje_global').text(response.promedio_global + '%');
                jQuery('#lbl_estado_calificacion').text(response.calificacion);

                jQuery('#banner_calificacion_regional').css('background-color', response.color_banner);

                // Ajustar altura según la cantidad de filas devueltas por list_form2_regional_x_regional
                var altoDinamico = Math.max(580, response.categorias.length * 24);
                jQuery('#grafico_barras_regional').css('height', altoDinamico + 'px');

                // Esperar a que el modal termine de abrirse para renderizar Highcharts
                setTimeout(function() {
                    new Highcharts.Chart({
                        chart: {
                            renderTo: 'grafico_barras_regional',
                            type: 'bar'
                        },
                        title: {
                            text: 'CUMPLIMIENTO DE OPERACIONES ACUMULADO A LA GESTIÓN ' + response.gestion,
                            style: { fontSize: '13px', fontWeight: 'bold', color: '#1e293b' }
                        },
                        xAxis: {
                            categories: response.categorias,
                            labels: {
                                style: { fontSize: '10px', fontWeight: 'bold', color: '#334155' }
                            }
                        },
                        yAxis: {
                            min: 0,
                            max: 100,
                            title: { text: 'Porcentaje de Cumplimiento (%)' }
                        },
                        legend: { enabled: false },
                        tooltip: {
                            valueSuffix: ' %'
                        },
                        plotOptions: {
                            bar: {
                                color: '#38bdf8',
                                borderRadius: 2,
                                pointWidth: 12,
                                dataLabels: {
                                    enabled: true,
                                    format: '{y}%',
                                    style: { fontSize: '9px', fontWeight: 'bold', color: '#0f172a' }
                                }
                            }
                        },
                        series: [{
                            name: 'Cumplimiento',
                            data: response.valores
                        }]
                    });
                }, 300);

            } else {
                alertify.error("No se pudieron obtener los datos de la regional.");
            }
        },
        error: function() {
            alertify.error("Error de comunicación con el servidor.");
        }
    });
  }

  //// Exportar Grafico
  function imprimirGraficoRegional() {
    // 1. Verificar librería
    if (typeof html2pdf === 'undefined') {
        alert('Error: No se encuentra cargada la librería html2pdf.');
        return;
    }

    // 2. Extraer los textos limpios del modal
    // 🔥 CORRECCIÓN: Agregamos .first() para tomar solo el primer h4 y evitar que se duplique
    var tituloGestion = jQuery('#area_impresion_regional h4').first().text().trim() || 'CUADRO DE EVALUACIÓN DE OPERACIONES';
    var subtituloReg     = jQuery('#lbl_regional_titulo').text().trim() || 'REGIONAL';
    var porcentajeGlobal = jQuery('#lbl_porcentaje_global').text().trim() || '0.0%';
    var estadoCalif      = jQuery('#lbl_estado_calificacion').text().trim() || '';
    var colorBanner      = jQuery('#banner_calificacion_regional').css('background-color') || '#f59e0b';

    // 3. Extraer el SVG de Highcharts y convertirlo a imagen limpia ajustada a la hoja
    var svgElement = document.querySelector('#grafico_barras_regional svg');
    if (!svgElement) {
        alert('Error: El gráfico aún no se ha renderizado.');
        return;
    }

    // Clonar el SVG para darle un ancho exacto de 700px (calza perfecto en hoja Carta)
    var svgClone      = svgElement.cloneNode(true);
    var anchoOriginal = parseFloat(svgElement.getAttribute('width')) || 800;
    var altoOriginal  = parseFloat(svgElement.getAttribute('height')) || 600;
    var anchoPdfPx    = 700;
    var altoPdfPx     = Math.round((altoOriginal * anchoPdfPx) / anchoOriginal);

    svgClone.setAttribute('width', anchoPdfPx);
    svgClone.setAttribute('height', altoPdfPx);
    svgClone.setAttribute('viewBox', '0 0 ' + anchoOriginal + ' ' + altoOriginal);

    var svgData = new XMLSerializer().serializeToString(svgClone);
    var imgSrc  = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));

    // 4. Construir el contenedor aislado para el cuerpo del reporte
    var contenedorLimpio = document.createElement('div');
    contenedorLimpio.style.fontFamily = 'Arial, Helvetica, sans-serif';
    contenedorLimpio.style.width = '100%';
    contenedorLimpio.style.backgroundColor = '#ffffff';

    var bannerHtml = 
        '<div style="background-color: ' + colorBanner + '; color: #ffffff; text-align: center; padding: 12px; border-radius: 5px; margin-bottom: 15px;">' +
            '<div style="font-size: 32px; font-weight: bold; line-height: 1.1; letter-spacing: 0.5px;">' + porcentajeGlobal + '</div>' +
            '<div style="font-size: 11px; font-weight: bold; letter-spacing: 1px; word-spacing: 2px; margin-top: 4px;">' + estadoCalif + '</div>' +
        '</div>';

    var graficoHtml = 
        '<div style="width: 100%; text-align: center; border: 1px solid #e2e8f0; border-radius: 5px; padding: 10px 5px;">' +
            '<img src="' + imgSrc + '" style="width: 100%; height: auto; display: block; margin: 0 auto;" />' +
        '</div>';

    contenedorLimpio.innerHTML = bannerHtml + graficoHtml;

    // 5. Configurar html2pdf con márgenes válidos (Arriba: 28mm, Izquierda: 12mm, Abajo: 14mm, Derecha: 12mm)
    var regionalArchivo = subtituloReg.replace('REGIONAL :', '').trim() || 'Regional';
    var opciones = {
        margin:       new Array(28, 12, 14, 12),
        filename:     'Cumplimiento_Operaciones_' + regionalArchivo + '.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2, useCORS: true, logging: false },
        jsPDF:        { unit: 'mm', format: 'letter', orientation: 'portrait' }
    };

    // 6. Generar el PDF e inyectar la CABECERA Y PIE DE PÁGINA EN TODAS LAS HOJAS
    html2pdf().set(opciones).from(contenedorLimpio).toPdf().get('pdf').then(function (pdf) {
        var totalPaginas = pdf.internal.getNumberOfPages();
        var anchoPagina  = pdf.internal.pageSize.getWidth();
        var altoPagina   = pdf.internal.pageSize.getHeight();

        for (var i = 1; i <= totalPaginas; i++) {
            pdf.setPage(i);

            // Cabecera repetitiva en cada hoja
            pdf.setFillColor(240, 249, 255);
            pdf.setDrawColor(186, 230, 253);
            pdf.roundedRect(12, 8, anchoPagina - 24, 16, 2, 2, 'FD');

            pdf.setFont('helvetica', 'bold');
            pdf.setFontSize(11);
            pdf.setTextColor(30, 58, 138);
            pdf.text(tituloGestion, anchoPagina / 2, 14.5, { align: 'center' });

            pdf.setFontSize(9.5);
            pdf.setTextColor(2, 132, 199);
            pdf.text(subtituloReg, anchoPagina / 2, 20.5, { align: 'center' });

            // Pie de página en cada hoja
            pdf.setFont('helvetica', 'normal');
            pdf.setFontSize(8);
            pdf.setTextColor(100, 116, 139);
            pdf.text('Fecha de impresión: ' + new Date().toLocaleDateString('es-ES'), 12, altoPagina - 7);
            pdf.text('Página ' + i + ' de ' + totalPaginas, anchoPagina - 12, altoPagina - 7, { align: 'right' });
        }
    }).save();
  }




  