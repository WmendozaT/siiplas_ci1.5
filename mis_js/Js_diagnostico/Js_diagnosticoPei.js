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


// DO NOT REMOVE : GLOBAL FUNCTIONS!
  $(document).ready(function() {
      pageSetUp();
      $("#menu").menu();
      $('.ui-dialog :button').blur();
      $('#tabs').tabs();
  })

  //// Obtiene distrital
  $(document).ready(function() {
    $('#dist_id').change(function() {
        var dist_id = $(this).val();

        var $contenedor = $('#contenedor_formulario');

        // Si selecciona la opción "Seleccione..", limpiamos el contenedor
        if (dist_id == 0) {
            $contenedor.fadeOut().html('');
            return;
        }

        $.ajax({
          url: base + "index.php/Cdiagnostico_pei/CDiagnostico_pei/get_unidad_ejecutora",
          type: 'POST',
          data: { id: dist_id }, // Enviamos como 'id'
          dataType: 'json',      // Especificamos que esperamos un JSON
          beforeSend: function() {
              $contenedor.html(
                    '<div class="well text-center" style="padding: 50px;">' +
                    '   <i class="fa fa-refresh fa-spin fa-3x text-primary"></i>' +
                    '   <h4 class="text-primary" style="margin-top:20px;">Cargando Diagnóstico...</h4>' +
                    '   <p class="text-muted">Por favor espere un momento.</p>' +
                    '</div>'
                );
          },
          success: function(data) {
              if(data.respuesta == 'correcto') {
                  // Accedemos a data.tabla
                  $contenedor.hide().html(data.tabla).fadeIn(600);
              } else {
                  $contenedor.html(
                    '<div class="alert alert-danger">' +
                    '   <i class="fa fa-times"></i> Error crítico al conectar con el servidor.' +
                    '</div>'
                );
              }
          },
          error: function(xhr) {
              console.error(xhr.responseText);
              $contenedor.html('<div class="alert alert-danger">Error en el servidor.</div>');
          }
      });
    });
  });



