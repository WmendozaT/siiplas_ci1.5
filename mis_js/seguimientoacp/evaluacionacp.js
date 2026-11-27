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


    //// Exportar Operaciones Intiucional / regionales
    function exportarExcelInstitucional_form1() {
        if (dep_id === undefined || dep_id === null || dep_id === '') {
            dep_id = jQuery('#d_id').val() || 0;
        }

        var boton = btnElement ? jQuery(btnElement) : jQuery('#btn_exportar_excel');
        var textoOriginal = boton.html();
        var url = base + "index.php/exportar_form1";

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