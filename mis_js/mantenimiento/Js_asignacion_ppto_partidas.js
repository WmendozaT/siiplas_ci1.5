var base = $('[name="base"]').val();

$(document).ready(function() {
    $('.btn-ver-partidas').max; // Asegurar selectores limpios si usas DataTables
    
    $(document).on('click', '.btn-ver-partidas', function() {
        var aper_id = $(this).data('id');
        
        // Abrir el modal y mostrar mensaje de carga
        $('#modalPartidas').modal('show');
        $('#contenido_modal_partidas').html('<div class="text-center"><i class="glyphicon glyphicon-refresh" style="font-size:20px;"></i> Cargando partidas...</div>');
        
        // Petición AJAX al controlador
        $.ajax({
            url: "<?php echo site_url('tu_controlador/cargar_partidas_ajax'); ?>",
            type: "POST",
            data: { id_aper: aper_id },
            dataType: "html",
            success: function(response) {
                $('#contenido_modal_partidas').html(response);
            },
            error: function() {
                $('#contenido_modal_partidas').html('<div class="alert alert-danger">Error al cargar los datos. Intente de nuevo.</div>');
            }
        });
    });
});