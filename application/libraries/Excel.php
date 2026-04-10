<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Excel {
    function __construct() {
        // Apuntamos directamente al IOFactory que se ve en tu imagen
        // Esto permite leer archivos sin necesitar el archivo PHPExcel.php principal
        require_once APPPATH.'libraries/PHPExcel/IOFactory.php';
    }

    // Función auxiliar para cargar archivos fácilmente
    public function filtrar_y_leer($ruta_archivo) {
        $tipo = PHPExcel_IOFactory::identify($ruta_archivo);
        $lector = PHPExcel_IOFactory::createReader($tipo);
        return $lector->load($ruta_archivo);
    }
}