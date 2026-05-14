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
            return $this->form_pdf1('portrait',$get_form_distrital); //// vertical - poblacion afiliada
        }
        elseif($tp_rep == 2){
            return $this->form_pdf1_1('landscape',$get_form_distrital); //// horizontal - poblacion por grupo etareo
        }
        elseif($tp_rep == 3){
            return $this->form_pdf2('portrait',$get_form_distrital); //// vertital - empresas aportantes
        }
        elseif($tp_rep == 4){
            return $this->form_pdf3('landscape',$get_form_distrital); //// horizontal - perfil epidemiologico
        }
        elseif($tp_rep == 5){
            return $this->form_pdf4('portrait',$get_form_distrital); //// vertital - Infraestructura
        }
        else{
            return "Trabajando ... ";
        }
    }

    /// cabecera reporte
    public function cabecera_report($nro_rep,$titulo,$get_form_distrital) {
        $tabla='';
        $tabla.='
        <page_header>
            <!-- Quitamos paddings en px que rompen el DOM del PDF. Usamos margin-lateral para alinear con la página -->
            <div style="padding-top: 25px; margin-left: 15mm; margin-right: 15mm; display: block;">
                
                <!-- Forzamos table-layout fixed para que respete estrictamente los porcentajes en Portrait y Landscape -->
                <table style="width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; table-layout: fixed;">
                    <tr>
                        <td style="width: 20%; text-align: left; vertical-align: middle; font-size:9px;">
                            <b>FORMULARIO PEI N° '.$nro_rep.'</b>
                        </td>
                        
                        <td style="width: 60%; text-align: center; vertical-align: middle;">
                            <span style="font-size: 13px; font-weight: bold; color: #004640; letter-spacing: 0.5px;">
                                CAJA NACIONAL DE SALUD
                            </span>
                            <br>
                            <span style="font-size: 17px; font-weight: bold; color: #212121; line-height: 1.2;">
                                '.$titulo.'
                            </span>
                            <br>
                            <span style="font-size: 11px; font-weight: bold; color: #212121; line-height: 1.2;">
                                '.strtoupper($get_form_distrital[0]['dist_distrital']).'
                            </span>
                        </td>
                        
                        <td style="width: 20%; text-align: right; vertical-align: middle; font-size: 8px; color: #424242; line-height: 1.3;">
                            PERIODO PEI: <b style="color: #212121;">'.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'</b>
                            <br>
                            Fecha de Impresión: '.date('d/m/Y').'
                        </td>
                    </tr>
                </table>
                
                <!-- Barras Estéticas alineadas perfectamente con los datos -->
                <div style="width: 100%; height: 3px; background-color: #004640; margin-top: 12px; margin-bottom: 2px;"></div>
                <div style="width: 100%; height: 1px; background-color: #e0e0e0;"></div>
            </div>
        </page_header>
            <!-- ==================== PIE DE PÁGINA ESTÁTICO ==================== -->
        <page_footer>
            <!-- Usamos los mismos márgenes en milímetros que la cabecera y el contenido base -->
            <div style="margin-left: 15mm; margin-right: 15mm; padding-bottom: 15px; display: block;">
                
                <!-- Línea divisoria superior limpia -->
                <div style="width: 100%; height: 1px; background-color: #cccccc; margin-bottom: 6px;"></div>
                
                <!-- Tabla elástica con ancho total controlado al 100% -->
                <table style="width: 100%; border-collapse: collapse; font-family: Helvetica, Arial, sans-serif; table-layout: fixed;">
                    <tr>
                        <!-- Zona Izquierda (50% proporcional) -->
                        <td style="width: 50%; text-align: left; vertical-align: middle; font-size: 8.5px; color: #666666; font-weight: 500;">
                            Sistema de Planificación SIIPLAS
                        </td>
                        
                        <!-- Zona Derecha (50% proporcional) -->
                        <td style="width: 50%; text-align: right; vertical-align: middle; font-size: 8.5px; color: #424242; font-weight: bold;">
                            Página [[page_cu]] de [[page_nb]]
                        </td>
                    </tr>
                </table>
            </div>
        </page_footer>';
        
        return $tabla;
    }

    /// formulario reporte 1 - Poblacion afiliada
    public function form_pdf1($orientacion,$get_form_distrital) {
        $detalle_form1=$this->CI->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(1,'DIAGNÓSTICO DE LA POBLACIÓN ASEGURADA',$get_form_distrital).'
                <p class="bold">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000;font-size:10.5px;">
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
                            <td class="bold" style="background-color: #d9edf7>'.$row['total_gestion'].'</td>
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
                <div class="box-container" style="height: 150px; border: 1px solid #000;font-size:10px;">
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

    /// formulario reporte 1.2 - Poblacion por grupo etareo
    public function form_pdf1_1($orientacion,$get_form_distrital) {
        $detalle_form1=$this->CI->model_diagnosticopei->get_formulario_N1($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $detalle_form1_etareo=$this->CI->model_diagnosticopei->get_formulario_N1_etareo($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $totales_gestion = array();
        for ($i = $get_form_distrital[0]['g_id_inicio']; $i <= $get_form_distrital[0]['g_id_fin']; $i++) {
            $totales_gestion[$i] = 0;
        }
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report('1.1.','DIAGNÓSTICO DE LA POBLACIÓN PROTEGIDA POR GRUPOS ETAREOS',$get_form_distrital[0]['dist_distrital']).'
                <table class="tabla-datos" style="font-size:8.7px;" >
                    <thead>
                        <tr style="text-align:center">
                            <th rowspan="2" class="nro-col" >GRUPO ETÁREO</th>
                            <th colspan="3" >2021</th>
                            <th colspan="3" >2022</th>
                            <th colspan="3" >2023</th>
                            <th colspan="3" >2024</th>
                            <th colspan="3" >2025</th>
                        </tr>
                        <tr style="text-align:center">
                            <!-- Usamos M, F, T para que no se amontone el texto -->
                            <th class="col-dato" style="width:5.9%;">M</th><th class="col-dato" style="width:5.9%;">F</th><th class="col-dato" style="width:5.9%;">Total</th>
                            <th class="col-dato" style="width:5.9%;">M</th><th class="col-dato" style="width:5.9%;">F</th><th class="col-dato" style="width:5.9%;">Total</th>
                            <th class="col-dato" style="width:5.9%;">M</th><th class="col-dato" style="width:5.9%;">F</th><th class="col-dato" style="width:5.9%;">Total</th>
                            <th class="col-dato" style="width:5.9%;">M</th><th class="col-dato" style="width:5.9%;">F</th><th class="col-dato" style="width:5.9%;">Total</th>
                            <th class="col-dato" style="width:5.9%;">M</th><th class="col-dato" style="width:5.9%;">F</th><th class="col-dato" style="width:5.9%;">Total</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($detalle_form1_etareo as $row){
                    $tabla.='
                    <tr>
                      <td style="font-size:12px;"><b>'.$row['grupo_etareo'].'</b></td>';
                      // Bucle para generar los 5 años (2021 al 2025)
                       for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $m = round($row['m_'.$anio],2);
                          $f = round($row['f_'.$anio],2);
                          $t = round($row['t_'.$anio],2);
                          $totales_gestion[$anio] += $t;
                          $tabla.='
                          <!-- Masculino -->
                          <td>'.number_format($m, 2, '.', ',').'</td>
                          <!-- Femenino -->
                          <td>'.number_format($f, 2, '.', ',').'</td>
                          <td style="background-color: #d9edf7;"><b>'.number_format($t, 2, '.', ',').'</b></td>';
                      }
                            
                    $tabla.='</tr>';
                   }
                    $tabla .= '
                        <tr style="background-color: #EEEEEE; font-weight: bold;">
                            <td style="text-align:right; font-size:8.5px;">TOTALES POR GESTIÓN:</td>';
                            
                            for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                                $tabla .= '
                                <td colspan="2" style="text-align:center; font-size:9px;">Total '.$anio.'</td>
                                <td style="text-align:center; background-color: #d9edf7; font-size:10.5px;">
                                    '.number_format($totales_gestion[$anio], 2, '.', ',').'
                                </td>';
                            }
                            
                    $tabla .= '
                        </tr>
                    </tbody>
                </table>

                <br><br><br><br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
            </div>
        </page>';

        return $tabla;
    }


    /// formulario reporte 2 - empresas aportantes
    public function form_pdf2($orientacion,$get_form_distrital) {
        $detalle_form2=$this->CI->model_diagnosticopei->get_formulario_N2($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="15mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(2,'DIAGNÓSTICO DE EMPRESAS',$get_form_distrital[0]['dist_distrital']).'
                <p class="bold">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000; font-size:10.5px;">
                    Recolectar, validar y sistematizar información anual del número de empresas aportantes, permitiendo analizar su evolución, cobertura institucional y comportamiento contributivo.
                </div>

                <p class="bold">2. Definición Operativa</p>
                <div class="box-container" style="border: 1px solid #000;font-size:10.5px;">
                    Empresa aportante: unidad económica registrada que realiza aportes al sistema en un periodo determinado, independientemente del número de trabajadores afiliados.
                </div>
                
                <table class="tabla-datos">
                    <thead>
                        <tr>
                          <th style="width: 25%;text-align:center;">GESTIÓN</th>
                          <th style="width: 25%;text-align:center;">N° DE EMPRESAS REGISTRADAS</th>
                          <th style="width: 25%;text-align:center;">CON APORTES AL DIA</th>
                          <th style="width: 25%;text-align:center;">EN MORA</th>
                        </tr>
                    </thead>
                    <tbody>';
                       foreach($detalle_form2 as $row){
                        $tabla.='
                        <tr>
                            <td><b>'.$row['gestion'].'</b></td>
                            <td><b>'.$row['empresas'].'</b></td>
                            <td>'.$row['aportes'].'</td>
                            <td>'.$row['mora'].'</td>
                        </tr>';
                       }
                    $tabla.='
                    </tbody>
                </table>

                <p class="bold">3. Observaciones adicionales</p>
                <div class="box-container" style="height: 150px; border: 1px solid #000;font-size:10px;">
                    '.strtoupper($get_form_distrital[0]['observacion2']).'
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


    /// formulario reporte 3 - Perfil Epidemiologico
    public function form_pdf3($orientacion,$get_form_distrital) {
        $detalle_form3_perfil1=$this->CI->model_diagnosticopei->get_formulario_N3($get_form_distrital[0]['dist_id'],1); /// Perfil 1
        $detalle_form3_perfil2=$this->CI->model_diagnosticopei->get_formulario_N3($get_form_distrital[0]['dist_id'],2); /// Perfil 2
        $detalle_form3_perfil3=$this->CI->model_diagnosticopei->get_formulario_N3($get_form_distrital[0]['dist_id'],3); /// Perfil 3
        $tabla='';
        $totales_gestion = array();
        for ($i = $get_form_distrital[0]['g_id_inicio']; $i <= $get_form_distrital[0]['g_id_fin']; $i++) {
            $totales_gestion[$i] = 0;
        }
        $tabla.='
        '.$this->style_report().'
        <!-- Definición de página para HTML2PDF -->
        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="5mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(3,'DIAGNÓSTICO DEL PERFIL EPIDEMIOLOGICO',$get_form_distrital[0]['dist_distrital']).'
                
                <p class="bold">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000;font-size:10.5px;">
                    Recolectar, organizar y analizar información epidemiológica de la población afiliada, identificando tendencias de morbilidad, mortalidad y factores de riesgo en el periodo '.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'.
                </div>

                <p class="bold">2. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Externa)</p>

                <table class="tabla-datos" style="font-size:8.7px; width:100%;" >
                    <thead>
                        <tr style="text-align:center;">
                            <th rowspan="2" class="nro-col" >N°</th>
                            <th colspan="2" >2021</th>
                            <th colspan="2" >2022</th>
                            <th colspan="2" >2023</th>
                            <th colspan="2" >2024</th>
                            <th colspan="2" >2025</th>
                        </tr>
                        <tr style="text-align:center">
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($detalle_form3_perfil1 as $row){
                        $tabla.='
                        <tr>
                            <td>'.$row['nro'].'</td>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $nro_casos = round($row['nro_casos_'.$anio],2);
                              $cod_cie = $row['codigo_cie_'.$anio];
                              $tabla.='
                              <!-- nro -->
                              <td style="width:4.2%;">'.$nro_casos.'</td>
                              <!-- cod_cie -->
                              <td style="width:15.4%; font-size:7px; text-align:left;">'.$cod_cie.'</td>';
                        }
                        $tabla.='
                        </tr>';
                    }     
                  
                    $tabla .= '
                    </tbody>
                </table>

                <br><br><br><br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
            </div>
        </page>


        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="5mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(3,'DIAGNÓSTICO DEL PERFIL EPIDEMIOLOGICO',$get_form_distrital[0]['dist_distrital']).'
                
                <p class="bold">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000; font-size:10.5px;">
                    Recolectar, organizar y analizar información epidemiológica de la población afiliada, identificando tendencias de morbilidad, mortalidad y factores de riesgo en el periodo '.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'.
                </div>

                <p class="bold">3. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Hospitalaria)</p>

                <table class="tabla-datos" style="font-size:8.7px; width:100%;" >
                    <thead>
                        <tr style="text-align:center;">
                            <th rowspan="2" class="nro-col" >N°</th>
                            <th colspan="2" >2021</th>
                            <th colspan="2" >2022</th>
                            <th colspan="2" >2023</th>
                            <th colspan="2" >2024</th>
                            <th colspan="2" >2025</th>
                        </tr>
                        <tr style="text-align:center">
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($detalle_form3_perfil2 as $row){
                        $tabla.='
                        <tr>
                              <td>'.$row['nro'].'</td>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $nro_casos = round($row['nro_casos_'.$anio],2);
                              $cod_cie = $row['codigo_cie_'.$anio];
                              $tabla.='
                              <!-- nro -->
                              <td style="width:4.2%;">'.$nro_casos.'</td>
                              <!-- cod_cie -->
                              <td style="width:15.4%; font-size:7px; text-align:left;">'.$cod_cie.'</td>';
                        }
                        $tabla.='
                        </tr>';
                    }     
                  
                    $tabla .= '
                    </tbody>
                </table>

                <br><br><br><br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
            </div>
        </page>


        <page orientation="'.$orientacion.'" backtop="15mm" backbottom="3mm" backleft="15mm" backright="15mm">
                <div class="contenedor-reporte">
                '.$this->cabecera_report(3,'DIAGNÓSTICO DEL PERFIL EPIDEMIOLOGICO',$get_form_distrital[0]['dist_distrital']).'
                
                <p class="bold">4. Perfil de mortalidad (principales causas)</p>

                <table class="tabla-datos" style="font-size:8.7px; width:100%;" >
                    <thead>
                        <tr style="text-align:center;">
                            <th rowspan="2" class="nro-col" >N°</th>
                            <th colspan="2" >2021</th>
                            <th colspan="2" >2022</th>
                            <th colspan="2" >2023</th>
                            <th colspan="2" >2024</th>
                            <th colspan="2" >2025</th>
                        </tr>
                        <tr style="text-align:center">
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                            <th style="width:4.2%; font-size:9px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                        </tr>
                    </thead>
                    <tbody>';
                    foreach($detalle_form3_perfil3 as $row){
                        $tabla.='
                        <tr>
                            <td>'.$row['nro'].'</td>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $nro_casos = round($row['nro_casos_'.$anio],2);
                              $cod_cie = $row['codigo_cie_'.$anio];
                              $tabla.='
                              <!-- nro -->
                              <td style="width:4.2%;">'.$nro_casos.'</td>
                              <!-- cod_cie -->
                              <td style="width:15.4%; font-size:7px; text-align:left;">'.$cod_cie.'</td>';
                        }
                        $tabla.='
                        </tr>';
                    }     
                  
                    $tabla .= '
                    </tbody>
                </table>

                 <p class="bold">5. Observaciones adicionales</p>
                <div class="box-container" style="height: 40px; border: 1px solid #000; font-size:8px;">
                    '.strtoupper($get_form_distrital[0]['observacion3']).'
                </div>

                <br><br><br>
                <p style="text-align:center;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
            </div>
        </page>';

        return $tabla;
    }



public function form_pdf4($orientacion, $get_form_distrital) {
    // 1. Carga de datos
    $dist_id = $get_form_distrital[0]['dist_id'];
    $gestion_fin = $get_form_distrital[0]['g_id_fin'];
    
    $detalle_1er = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '1');
    $detalle_2do = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '2,3');
    $detalle_otros = $this->CI->model_diagnosticopei->get_otros_infraestructura_por_nivel($dist_id);

    $tabla = $this->style_report();
    
    // Reducimos backbottom a 15mm para ganar espacio útil y evitar hojas vacías
    $tabla .= ' 
    <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
    
    <!-- ==================== CABECERA ESTÁTICA CORPORATIVA ==================== -->
    '.$this->cabecera_report(1,'DIAGNÓSTICO INFRAESTRUCTURA DE SALUD',$get_form_distrital).'

    <!-- ==================== CONTENIDO DINÁMICO ==================== -->
    <p class="bold">1. Objetivo del instrumento</p>
    <div class="box-container" style="border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
        Identificar, registrar y evaluar las condiciones de la infraestructura de los establecimientos de salud, para determinar su capacidad operativa y soporte a la demanda poblacional.
    </div>

    <!-- SECCIÓN 2: PRIMER NIVEL -->
    <p class="bold" style="margin-top: 15px;">2. Matriz de inventario de establecimientos de PRIMER NIVEL (segun poa '.$gestion_fin.')</p>
    <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
        <thead>
             <tr style="text-align:center; background-color: #004640; color: #fff;">
                 <th style="width:5%; font-size:8px; padding:3px;">#</th>
                 <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                 <th style="width:35%; font-size:8px;">Ubicación</th>
                 <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                 <th style="width:10%; font-size:7px;">¿Cuenta con Internet?</th>
                 <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
             </tr>
         </thead>
         <tbody>';
         $nro = 0;
         foreach ($detalle_1er as $row) {
                $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                $situacion = strtoupper($row['tipo_situacion']);
                if($situacion == '1') $situacion = 'PROPIA';
                if($situacion == '2') $situacion = 'ALQUILADA';
                $nro++;
                $tabla .= '
                    <tr>
                        <td style="width:5%; text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                        <td style="width:25%; text-align:left; font-size:7px; padding:3px;">'.$row['tipo'].' '.$row['act_descripcion'].'</td>
                        <td style="width:35%; text-align:left; font-size:6.5px; padding-left:3px;">'.strtoupper($row['ubicacion']).'</td>
                        <td style="width:10%; text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                        <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                        <td style="width:15%; text-align:left; font-size:6.5px; padding-left:3px;">'.$situacion.'</td>
                    </tr>';
            }
         $tabla .= '
         </tbody>
    </table>';

    // SECCIÓN 3: SEGUNDO Y TERCER NIVEL
    // Envolvemos esta sección en un contenedor que evita cortes huérfanos innecesarios
    $tabla .= '
    <div style="page-break-inside: auto; margin-top: 15px;">
        <p class="bold">3. Matriz de inventario de establecimientos de SEGUNDO Y TERCER NIVEL (segun poa '.$gestion_fin.')</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                     <th style="width:5%; font-size:8px; padding:3px;">#</th>
                     <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                     <th style="width:35%; font-size:8px;">Ubicación</th>
                     <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                     <th style="width:10%; font-size:7px;">¿Cuenta con Internet?</th>
                     <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
                 </tr>
             </thead>
             <tbody>';
             $nro = 0;
             foreach ($detalle_2do as $row) {
                    $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                    $situacion = strtoupper($row['tipo_situacion']);
                    if($situacion == '1') $situacion = 'PROPIA';
                    if($situacion == '2') $situacion = 'ALQUILADA';
                    $nro++;
                    $tabla .= '
                        <tr>
                            <td style="width:5%; text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                            <td style="width:25%; text-align:left; font-size:7px; padding:3px;">'.$row['tipo'].' '.$row['act_descripcion'].'</td>
                            <td style="width:35%; text-align:left; font-size:6.5px; padding-left:3px;">'.strtoupper($row['ubicacion']).'</td>
                            <td style="width:10%; text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                            <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                            <td style="width:15%; text-align:left; font-size:6.5px; padding-left:3px;">'.$situacion.'</td>
                        </tr>';
                }
             $tabla .= '
             </tbody>
        </table>
    </div>';

    // SECCIÓN 4: OTROS ESTABLECIMIENTOS (SI EXISTEN)
    if (count($detalle_otros) > 0) {
        $tabla .= '
        <div style="page-break-inside: auto; margin-top: 15px;">
            <p class="bold">4. Otros Establecimientos</p>
            <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                <thead>
                     <tr style="text-align:center; background-color: #004640; color: #fff;">
                         <th style="width:5%; font-size:8px; padding:3px;">#</th>
                         <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                         <th style="width:35%; font-size:8px;">Ubicación</th>
                         <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                         <th style="width:10%; font-size:7px;">¿Cuenta con Internet?</th>
                         <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
                     </tr>
                 </thead>
                 <tbody>';
                 $nro = 0;
                 foreach ($detalle_otros as $row) {
                        $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                        $situacion = strtoupper($row['tipo_situacion']);
                        if($situacion == '1') $situacion = 'PROPIA';
                        if($situacion == '2') $situacion = 'ALQUILADA';
                        $nro++;
                        $tabla .= '
                            <tr>
                                <td style="width:5%; text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                                <td style="width:25%; text-align:left; font-size:7px; padding:3px;">'.$row['tipo_establecimiento'].' '.$row['otro_establecimiento'].'</td>
                                <td style="width:35%; text-align:left; font-size:6.5px; padding-left:3px;">'.strtoupper($row['ubicacion']).'</td>
                                <td style="width:10%; text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                                <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                                <td style="width:15%; text-align:left; font-size:6.5px; padding-left:3px;">'.$situacion.'</td>
                            </tr>';
                    }
                 $tabla .= '</tbody></table></div>';
    }

    // SECCIÓN 5: OBSERVACIONES ADICIONALES
    $tabla .= '
    <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
    <div class="box-container" style="width: 96%; border: 0.5px solid #000; font-size:8px; padding:5px; margin-bottom: 25px;">
        '.(!empty($get_form_distrital[0]['observacion4']) ? strtoupper($get_form_distrital[0]['observacion4']) : 'SIN OBSERVACIONES').'
    </div>';

    // SECCIÓN 6: CONTENEDOR DE FIRMAS CON PROTECCIÓN DE SALTO DE HOJA
    $tabla .= '
    <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
        <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
    </div>';

    // CRÍTICO: CERRAMOS CORRECTAMENTE LA PÁGINA PARA ELIMINAR HOJAS BLANCAS FANTASMA
    $tabla .= '</page>';

    return $tabla;
}
    /// formulario reporte 4 - Diagnostico Infraestructura
    public function form_pdf4_($orientacion, $get_form_distrital) {
        // 1. Carga de datos
        $dist_id = $get_form_distrital[0]['dist_id'];
        $gestion_fin = $get_form_distrital[0]['g_id_fin'];
        
        $detalle_1er = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '1');
        $detalle_2do = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '2,3');
        $detalle_otros = $this->CI->model_diagnosticopei->get_otros_infraestructura_por_nivel($dist_id);

        $observacion = '
            <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
            <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                '.strtoupper($get_form_distrital[0]['observacion4']).'
            </div>';

    $tabla = $this->style_report();
    $tabla.=' 
    <page orientation="'.$orientacion.'" backtop="30mm" backbottom="20mm" backleft="15mm" backright="15mm">
    
    <!-- ==================== CABECERA DINÁMICA UNIVERSAL CORREGIDA ==================== -->
    '.$this->cabecera_report(1,'DIAGNÓSTICO INFRAESTRUCTURA DE SALUD',$get_form_distrital).'

    <!-- ==================== CONTENIDO DINÁMICO ==================== -->
    <div class="contenedor-reporte">
        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Identificar, registrar y evaluar las condiciones de la infraestructura de los establecimientos de salud, para determinar su capacidad operativa y soporte a la demanda poblacional.
        </div>

        <p class="bold" style="margin-top: 15px;">2. Matriz de inventario de establecimientos de PRIMER NIVEL (segun poa '.$get_form_distrital[0]['g_id_fin'].')</p>

        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640;">
                     <th style="width:5%; font-size:8px; padding:3px;">#</th>
                     <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                     <th style="width:35%; font-size:8px;">Ubicación</th>
                     <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                     <th style="width:10%; font-size:7px;">cuenta con Internet?</th>
                     <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
                 </tr>
             </thead>
             <tbody>';
             $nro=0;
             foreach ($detalle_1er as $row) {
                    $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                    $situacion = strtoupper($row['tipo_situacion']);
                    if($situacion == '1') $situacion = 'PROPIA';
                    if($situacion == '2') $situacion = 'ALQUILADA';
                    $nro++;
                    $tabla .= '
                        <tr>
                            <td style="width:5%;text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                            <td style="width:25%;text-align:left; font-size:7px; padding:3px;"><b>'.$row['tipo'].' '.$row['act_descripcion'].'</b></td>
                            <td style="width:35%;text-align:left; font-size:6.5px;">'.strtoupper($row['ubicacion']).'</td>
                            <td style="width:10%;text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                            <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                            <td style="width:15%;text-align:left; font-size:6.5px;">'.$situacion.'</td>
                        </tr>';
                }
             $tabla.='
             </tbody>
        </table>

        <p class="bold" style="margin-top: 15px;">3. Matriz de inventario de establecimientos de SEGUNDO Y TERCER NIVEL (segun poa '.$get_form_distrital[0]['g_id_fin'].')</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640;">
                     <th style="width:5%; font-size:8px; padding:3px;">#</th>
                     <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                     <th style="width:35%; font-size:8px;">Ubicación</th>
                     <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                     <th style="width:10%; font-size:7px;">cuenta con Internet?</th>
                     <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
                 </tr>
             </thead>
             <tbody>';
             $nro=0;
             foreach ($detalle_2do as $row) {
                    $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                    $situacion = strtoupper($row['tipo_situacion']);
                    if($situacion == '1') $situacion = 'PROPIA';
                    if($situacion == '2') $situacion = 'ALQUILADA';
                    $nro++;
                    $tabla .= '
                        <tr>
                            <td style="width:5%;text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                            <td style="width:25%;text-align:left; font-size:7px; padding:3px;"><b>'.$row['tipo'].' '.$row['act_descripcion'].'</b></td>
                            <td style="width:35%;text-align:left; font-size:6.5px;">'.strtoupper($row['ubicacion']).'</td>
                            <td style="width:10%;text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                            <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                            <td style="width:15%;text-align:left; font-size:6.5px;">'.$situacion.'</td>
                        </tr>';
                }
             $tabla.='
             </tbody>
        </table>';

        if(count($detalle_otros)!=0){
            $tabla.='
            <p class="bold" style="margin-top: 15px;">4. Otros Establecimientos</p>
                <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse;" border="1">
                    <thead>
                         <tr style="text-align:center; background-color: #004640;">
                             <th style="width:5%; font-size:8px; padding:3px;">#</th>
                             <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                             <th style="width:35%; font-size:8px;">Ubicación</th>
                             <th style="width:10%; font-size:7px;">Nro. Consultorios</th>
                             <th style="width:10%; font-size:7px;">cuenta con Internet?</th>
                             <th style="width:15%; font-size:7px;">Situación Técnico Legal</th>
                         </tr>
                     </thead>
                     <tbody>';
                     $nro=0;
                     foreach ($detalle_otros as $row) {
                            $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                            $situacion = strtoupper($row['tipo_situacion']);
                            if($situacion == '1') $situacion = 'PROPIA';
                            if($situacion == '2') $situacion = 'ALQUILADA';
                            $nro++;
                            $tabla .= '
                                <tr>
                                    <td style="width:5%;text-align:center; font-size:7px; padding:3px;"><b>'.$nro.'</b></td>
                                    <td style="width:25%;text-align:left; font-size:7px; padding:3px;"><b>'.$row['tipo_establecimiento'].' '.$row['otro_establecimiento'].'</b></td>
                                    <td style="width:35%;text-align:left; font-size:6.5px;">'.strtoupper($row['ubicacion']).'</td>
                                    <td style="width:10%;text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                                    <td style="width:10%; text-align:center; font-size:7px;">'.$internet.'</td>
                                    <td style="width:15%;text-align:left; font-size:6.5px;">'.$situacion.'</td>
                                </tr>';
                        }
                     $tabla.='
                     </tbody>
                </table>';
        }
        $tabla.='
        '.$observacion.'

        <!-- Bloque de Firmas protegido contra huérfanos -->
        <div style="margin-top: 40px; page-break-inside: avoid; text-align:center;">
            <p><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>

    </div>
</page>';
        
        return $tabla;
    }



















public function style_report() {
    $tabla = '        
    <style>
        /* ==========================================================================
           1. AJUSTES GENERALES Y CONTENEDORES DE SEGURIDAD
           ========================================================================== */
        .page-body { font-family: Helvetica, Arial, sans-serif; font-size: 10pt; width: 100%; }
        .contenedor-reporte { font-family: Helvetica, Arial, sans-serif; width: 100%; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        
        /* Fuerza a todas las tablas del documento a respetar los porcentajes asignados */
        table { table-layout: fixed; width: 100%; border-collapse: collapse; }
        
        /* ==========================================================================
           2. NUEVA CABECERA INSTITUCIONAL ESTÁTICA (<page_header>)
           ========================================================================== */
        .header-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .main-title { font-size: 13pt; font-weight: bold; color: #212121; line-height: 1.3; }
        
        /* Barra de acento estético corporativo */
        .linea-institucional { width: 100%; height: 3px; background-color: #3276b1; margin-top: 12px; margin-bottom: 2px; }
        .linea-subti-gris { width: 100%; height: 1px; background-color: #e0e0e0; }

        /* ==========================================================================
           3. TABLA DE DATOS COMPACTA (Optimizado para múltiples columnas)
           ========================================================================== */
        .tabla-datos { width: 100%; border-collapse: collapse; margin-top: 15px; table-layout: fixed; }
        .tabla-datos th { 
            background-color: #004640; /* Sincronizado con el azul corporativo de la cabecera */
            color: #ffffff; /* Contraste óptimo para lectura */
            border: 1px solid #ccc; 
            padding: 6px 4px; 
            font-size: 8.5pt;
            vertical-align: middle;
            text-align: center;
        }
        .tabla-datos td { 
            border: 1px solid #ddd; 
            padding: 5px 3px; 
            text-align: center; 
            font-size: 8pt;
            vertical-align: middle;
        }
        
        /* ==========================================================================
           4. CUADROS DE TEXTO (Objetivos e Instrumentos)
           ========================================================================== */
        .box-container { 
            border: 1px solid #b3d4fc; /* Borde azul tenue muy sutil */
            padding: 10px; 
            width: 96%; /* Reducción estratégica del ancho para que el padding no desborde la hoja */
            margin-top: 5px;
            background-color: #f8f9fa; /* Fondo gris tiza sofisticado */
            border-radius: 4px;
            line-height: 1.4;
        }
        
        /* ==========================================================================
           5. NUEVO PIE DE PÁGINA CORPORATIVO (<page_footer>)
           ========================================================================== */
        .footer-linea { width: 100%; height: 1px; background-color: #e0e0e0; margin-bottom: 6px; }
        .footer-text { font-size: 8.5px; color: #888888; font-weight: 500; }
    </style>';
    
    return $tabla;
}


    /// estilo reporte
    public function style_report_anterior() {
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