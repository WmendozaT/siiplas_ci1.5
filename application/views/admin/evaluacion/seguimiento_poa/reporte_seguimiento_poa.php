<?php
// Saneamiento de recursos en capa de renderizado
ini_set('max_execution_time', 0); 
ini_set('memory_limit', '2048M');
?>
<style type="text/css">
    table.page_header { width: 100%; border: none; border-bottom: solid 5mm #1c7368; padding: 2mm; }
    table.page_footer { width: 100%; border: none; background-color: #739e73; border-top: solid 5mm #AAAADD; padding: 2mm; }
    .verde { width: 100%; height: 5px; background-color: #1c7368; }
    .blanco { width: 100%; height: 5px; background-color: #F1F2F1; }
    .siipp { width: 120px; }
    .tabla {
        font-size: 7px;
        width: 100%;
    }
</style>
<?php echo $lista; ?>