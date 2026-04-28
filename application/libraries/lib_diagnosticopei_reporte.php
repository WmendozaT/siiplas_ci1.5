<?php if (!defined('BASEPATH')) exit('No direct script access');

class Lib_diagnosticopei_reporte {

    private $CI;
    public $dist_id;
    public $dep_id;
    public $gestion;
    public $fun_id;
    public $conf_pei;
    public $tp_adm;

    public function __construct() {
        // Obtenemos la instancia de CodeIgniter
        $this->CI =& get_instance();
        
        // Cargamos el modelo usando la instancia
        $this->CI->load->model('diagnosticoPei/model_diagnosticopei');
        $this->dist_id  = $this->CI->session->userdata('dist');
        $this->dep_id   = $this->CI->session->userdata('dep_id');
        $this->gestion  = $this->CI->session->userdata('gestion');
        $this->fun_id   = $this->CI->session->userdata('fun_id');
        $this->conf_pei = $this->CI->session->userdata('conf_pei');
        $this->tp_adm   = $this->CI->session->userdata("tp_adm");
    }

    public function select_reporte_diagnostico_pei($tp_form,$dist_selec) {
        if($tp_form==1){
            return $this->form_pdf($dist_selec);
        }
        else{
            return "Trabajando ... ";
        }
        
    }

    /// formulario reporte
    public function form_pdf($dist_id) {
        $tabla='';
        $tabla.='
        <style>
    /* Forzamos el ancho máximo para que no se pase de la hoja */
    .contenedor-reporte { width: 100%; font-family: Arial, sans-serif; }
    
    /* Tabla de encabezado para evitar que el título se desplace */
    .tabla-header { width: 100%; border: none; margin-bottom: 10px; }
    .titulo-principal { 
        font-size: 16pt; 
        text-align: center; 
        font-weight: bold; 
        width: 100%;
        display: block;
    }
    
    /* Ajuste para que las tablas no se salgan */
    table { table-layout: fixed; width: 100%; border-collapse: collapse; }
    
    /* Línea divisoria */
    .linea { border-bottom: 1px solid #000; margin-bottom: 10px; }
</style>

        <style>
    /* Estilos específicos para HTML2PDF */
    .page-body { font-family: Arial, sans-serif; font-size: 10pt; width: 100%; }
    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .bold { font-weight: bold; }
    
    .header-table { width: 100%; border: none; }
    .main-title { font-size: 14pt; margin-top: 5px; }
    
    /* Estilo de la tabla de datos */
    .tabla-datos { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .tabla-datos th { 
        background-color: #FFC107; 
        border: 1px solid #000; 
        padding: 5px; 
        font-size: 9pt;
    }
    .tabla-datos td { border: 1px solid #000; padding: 5px; text-align: center; }
    
    /* Cuadros de texto */
    .box-container { 
        border: 1px solid #ccc; 
        padding: 10px; 
        width: 100%; 
        margin-top: 5px;
        background-color: #fff;
    }
    .footer { font-size: 8pt; text-align: center; margin-top: 30px; color: #555; }
</style>

<!-- Definición de página para HTML2PDF -->
<page backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
    
   
        <div class="contenedor-reporte">
        
        <!-- Fecha alineada a la derecha -->
        <table class="tabla-header">
            <tr>
                <td style="width: 100%; text-align: right; font-weight: bold;">
                    Fecha: ddd
                </td>
            </tr>
        </table>

        <!-- Título centrado y ajustado -->
        <div class="text-center">
            <span style="font-size: 10pt; font-weight: bold;">CAJA NACIONAL DE SALUD</span><br>
            <span class="titulo-principal">DIAGNÓSTICO DE LA POBLACIÓN ASEGURADA</span>
        </div>
        <div class="linea"></div>

        <!-- Resto del contenido -->
        <p><strong>Regional / Distrital:</strong>distr</p>
        
        <div style="margin-top: 20px;">
            
            <span style="border-bottom: 1px solid #000; width: 450px; display: inline-block;">
                fdsfsd
            </span>
        </div>

        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="border: 1px solid #000;">
            Recopilar, validar y sistematizar información cuantitativa de la población afiliada...
        </div>

        <p class="bold">2. Relevamiento de población afiliada (2021 - 2025)</p>
        
        <table class="tabla-datos">
            <thead>
                <tr>
                    <th colspan="5" style="background-color: #FFC107;">Tipo de población afiliada</th>
                </tr>
                <tr>
                    <th style="width: 15%;">Gestión</th>
                    <th style="width: 25%;">Cotizantes Titulares</th>
                    <th style="width: 20%;">Cotizantes Pasivos</th>
                    <th style="width: 25%;">Beneficiarios</th>
                    <th style="width: 15%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>e</td>
                    <td>e</td>
                    <td>e</td>
                    <td>ee</td>
                    <td class="bold">sew</td>
                </tr>
            </tbody>
        </table>

        <div class="box-container" style="margin-top: 15px; font-size: 9pt;">
            <span class="bold">Recomendaciones:</span><br>
            - Extraer información anual por cada categoría.<br>
            - Verificar consistencia entre fuentes oficiales.
        </div>

        <p class="bold">3. Observaciones adicionales</p>
        <div class="box-container" style="height: 80px; border: 1px solid #000;">
            gfdgdgfd
        </div>

        <div class="footer">
            DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIPLAS
        </div>
    </div>
</page>';

        return $tabla;
    }
}