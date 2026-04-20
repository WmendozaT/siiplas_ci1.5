base = $('[name="base"]').val();

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