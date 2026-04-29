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

    public function select_reporte_diagnostico_pei($tp_rep, $formulario) {
        // CAMBIO AQUÍ: Agregamos "CI" antes de llamar al modelo
        $get_form_distrital = $this->CI->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($formulario[0]['pei_id'], $formulario[0]['dist_id']); 
        
        if($tp_rep == 1){
            return $this->form_pdf1('portrait',$get_form_distrital); //// vertical
        }
        elseif($tp_rep == 2){
            return $this->form_pdf1_1('landscape',$get_form_distrital); //// horizontal
        }
        else{
            return "Trabajando ... ";
        }
    }

    /// cabecera reporte
    public function cabecera_report($nro_rep,$titulo,$distrital) {
        $tabla='';
        $tabla.='
                <table class="tabla-header">
                    <tr>
                        <td style="width: 50%; text-align: left; font-weight: bold;">
                            FORMULARIO PEI N° '.$nro_rep.'
                        </td>
                        <td style="width: 50%; text-align: right; font-weight: bold;">
                            Fecha: '.date('d/m/Y').'
                        </td>
                    </tr>
                </table>

                <!-- Título centrado y ajustado -->
                <div class="text-center">
                    <span style="font-size: 12pt; font-weight: bold;">CAJA NACIONAL DE SALUD</span><br>
                    <span class="titulo-principal">'.$titulo.'</span>
                </div>
                <div class="linea"></div>

                <!-- Resto del contenido -->
                <p><strong>'.strtoupper($distrital).'</strong></p>';
        
        return $tabla;
    }

    /// formulario reporte 1
    public function form_pdf1($orientacion,$get_form_distrital) {
        $detalle_form1=$this->CI->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(1,'DIAGNÓSTICO DE LA POBLACIÓN ASEGURADA',$get_form_distrital[0]['dist_distrital']).'
                <p class="bold">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000;">
                    Recopilar, validar y sistematizar información cuantitativa de la población afiliada (titulares y Beneficiarios) para analizar tendencias y cobertura y demanda potencial de la Caja Nacional de Salud.
                </div>

                <p class="bold">2. Relevamiento de población afiliada ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>
                
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th colspan="5" style="background-color: #FFC107; text-align:center;">Tipo de población afiliada</th>
                        </tr>
                        <tr>
                            <th style="width: 15%;text-align:center;">Gestión</th>
                            <th style="width: 25%;text-align:center;">Cotizantes Titulares</th>
                            <th style="width: 20%;text-align:center;">Cotizantes Pasivos</th>
                            <th style="width: 25%;text-align:center;">Beneficiarios</th>
                            <th style="width: 15%;text-align:center;">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>';
                       foreach($detalle_form1 as $row){
                        $tabla.='
                        <tr>
                            <td><b>'.$row['gestion'].'</b></td>
                            <td>'.$row['titulares'].'</td>
                            <td>'.$row['pasivos'].'</td>
                            <td>'.$row['beneficiarios'].'</td>
                            <td class="bold">'.$row['total_gestion'].'</td>
                        </tr>';
                       }
                    $tabla.='
                    </tbody>
                </table>

                <div class="box-container" style="margin-top: 15px; font-size: 9pt;">
                    <span class="bold">Recomendaciones:</span><br>
                    - Recolección de datos.<br>
                    - Extraer información anual por cada categoría.<br>
                    - Verificar consistencia entre fuentes oficiales.
                </div>

                <p class="bold">3. Observaciones adicionales</p>
                <div class="box-container" style="height: 150px; border: 1px solid #000;">
                    '.strtoupper($get_form_distrital[0]['observacion1']).'
                </div>
                <br><br><br><br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
                <br><br><br>
                <div class="footer">
                    DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIPLAS
                </div>
            </div>
        </page>';

        return $tabla;
    }

    /// formulario reporte 1 1
    public function form_pdf1_1($orientacion,$get_form_distrital) {
        $detalle_form1_etareo=$this->CI->model_diagnosticopei->get_formulario_N1_etareo($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(2,'DIAGNÓSTICO DE LA POBLACIÓN PROTEGIDA POR GRUPOS ETAREOS',$get_form_distrital[0]['dist_distrital']).'
                <table class="tabla-datos">
                    <thead>
                        <tr>
                            <th rowspan="2" class="nro-col">GRUPO ETÁREO</th>
                            <th colspan="3">2021</th>
                            <th colspan="3">2022</th>
                            <th colspan="3">2023</th>
                            <th colspan="3">2024</th>
                            <th colspan="3">2025</th>
                        </tr>
                        <tr>
                            <!-- Usamos M, F, T para que no se amontone el texto -->
                            <th class="col-dato">M</th><th class="col-dato">F</th><th class="col-dato">T</th>
                            <th class="col-dato">M</th><th class="col-dato">F</th><th class="col-dato">T</th>
                            <th class="col-dato">M</th><th class="col-dato">F</th><th class="col-dato">T</th>
                            <th class="col-dato">M</th><th class="col-dato">F</th><th class="col-dato">T</th>
                            <th class="col-dato">M</th><th class="col-dato">F</th><th class="col-dato">T</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($detalle_form1_etareo as $row){
                    $tabla.='
                    <tr>
                      <td class="nro-label" style="font-size:15px;"><b>'.$row['grupo_etareo'].'</b></td>';
                      // Bucle para generar los 5 años (2021 al 2025)
                       for ($anio = 2021; $anio <= 2025; $anio++) {
                          $m = round($row['m_'.$anio],2);
                          $f = round($row['f_'.$anio],2);
                          $t = round($row['t_'.$anio],2);

                          $tabla.='
                          <!-- Masculino -->
                          <td>'.$m.'</td>
                          <!-- Femenino -->
                          <td>'.$f.'</td>
                          <td>'.$t.'</td>';
                      }
                            
                    $tabla.='</tr>';
                   }
                    $tabla.='
                    </tbody>
                </table>

                <br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
                <br><br><br>
                <div class="footer">
                    DEPARTAMENTO NACIONAL DE PLANIFICACION / Sistema de Planificación SIPLAS
                </div>
            </div>
        </page>';

        return $tabla;
    }






    /// estilo reporte
    public function style_report() {
        $tabla='        
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
        </style>';

        return $tabla;
    }

}