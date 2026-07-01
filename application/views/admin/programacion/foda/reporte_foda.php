<?php
// 1. Ampliación de recursos para el procesamiento del reporte de laboratorio
set_time_limit(900);           
ini_set('memory_limit', '1024M'); 
?>
<style type="text/css">
    table.page_header { width: 100%; border: none; border-bottom: solid 1mm; padding: 2mm; }
    table.page_footer { width: 100%; border: none; background-color: #739e73; border-top: solid 1mm #AAAADD; padding: 2mm; }
    .verde { width: 100%; height: 5px; background-color: #1c7368; }
    .blanco { width: 100%; height: 5px; background-color: #F1F2F1; }
    .siipp { width: 120px; }
    .tabla {
        font-size: 7px;
        width: 100%;
    }
    /* REVISIÓN: Eliminada la llave doble de cierre errónea que rompía el CSS */
</style>

<!-- Inyección limpia de las etiquetas <page> controladas desde el Servidor -->
<?php echo $informacion; ?>
