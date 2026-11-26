base = $('[name="base"]').val();
gestion = $('[name="gestion"]').val();

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


  $( function() {
    $( "#grupoTablas" ).tabs();
  } );

  function justNumbers(e){
    var keynum = window.event ? window.event.keyCode : e.which;
    if ((keynum == 8) || (keynum == 46))
    return true;           
    return /\d/.test(String.fromCharCode(keynum));
  }




jQuery(document).ready(function() {
    if (typeof window.datosEvalACP !== 'undefined' && window.datosEvalACP.categorias.length > 0) {
        var datos        = window.datosEvalACP;
        var altoDinamico = Math.max(350, datos.categorias.length * 38);
        
        jQuery('#container1, #container2').css('height', altoDinamico + 'px');

        // Dibujar Gráfico 1: Cumplimiento Trimestral ACP
        dibujarGraficoBarrasHorizontal(
            'container1',
            'CUMPLIMIENTO DE ACP AL ' + datos.trimestre + ' - ' + datos.gestion,
            datos.categorias,
            datos.valoresTrm,
            '#38bdf8'
        );

        // Dibujar Gráfico 2: Cumplimiento Anual / Gestión ACP
        dibujarGraficoBarrasHorizontal(
            'container2',
            'CUMPLIMIENTO DE ACP ACUMULADO A LA GESTIÓN ' + datos.gestion,
            datos.categorias,
            datos.valoresGes,
            '#10b981'
        );
    }
});






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