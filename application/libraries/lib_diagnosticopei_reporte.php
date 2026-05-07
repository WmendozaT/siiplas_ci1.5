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
    public function cabecera_report($nro_rep,$titulo,$distrital) {
        $tabla='';
        $tabla.='
                <table class="tabla-header">
                    <tr>
                        <td style="width: 50%; text-align: left; font-weight: bold;">
                            FORMULARIO PEI N° '.$nro_rep.'
                        </td>
                        <td style="width: 50%; font-size:9px; text-align: right; font-weight: bold;">
                            Fecha de Impresión: '.date('d/m/Y').' <br>
                            Hora: '.date('H:i:s').'
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

    /// formulario reporte 1 - Poblacion afiliada
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




    /// formulario reporte 4 - Diagnostico Infraestructura
    public function form_pdf4($orientacion, $get_form_distrital) {
        // 1. Carga de datos
        $dist_id = $get_form_distrital[0]['dist_id'];
        $gestion_fin = $get_form_distrital[0]['g_id_fin'];
        
        $detalle_1er = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '1');
        $detalle_2do = $this->CI->model_diagnosticopei->get_infraestructura_por_nivel($dist_id, '2,3');
        $detalle_otros = $this->CI->model_diagnosticopei->get_otros_infraestructura_por_nivel($dist_id);

        $observacion = '
            <p class="bold" style="font-size:9px; margin-top:10px;">* Observaciones adicionales</p>
            <div class="box-container" style="height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                '.strtoupper($get_form_distrital[0]['observacion4']).'
            </div>';

        $tabla = $this->style_report();
        
        // Función interna optimizada
        $generar_tabla = function($titulo, $datos, $es_otros = false, $mostrar_poa = true) use ($gestion_fin) {
            if (empty($datos)) return '';
            
            // Ajuste de título: Solo agrega POA si mostrar_poa es true
            $texto_poa = ($mostrar_poa) ? ' (según POA '.$gestion_fin.')' : '';
            $html = '<p class="bold" style="font-size:9px; margin-top:10px;">* '.$titulo.$texto_poa.'</p>';
            
            $html .= '<table class="tabla-datos" style="width:100%; border-collapse: collapse;" border="0.5">
                        <thead>
                            <tr style="text-align:center; background-color: #f2f2f2;">
                                <th style="width:25%; font-size:8px; padding:3px;">Establecimiento</th>
                                <th style="width:10%; font-size:8px;">Tipo</th>
                                <th style="width:25%; font-size:8px;">Ubicación</th>
                                <th style="width:8%; font-size:7px;">Nro. Cons.</th>
                                <th style="width:8%; font-size:7px;">Internet</th>
                                <th style="width:24%; font-size:7px;">Situación Técnico Legal</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            foreach ($datos as $row) {
                $nombre = $es_otros ? $row['otro_establecimiento'] : $row['act_descripcion'];
                $tipo   = $es_otros ? $row['tipo_establecimiento'] : $row['tipo'];
                
                $internet = ($row['serv_internet'] == '1' || strtoupper($row['serv_internet']) == 'SI') ? 'SI' : 'NO';
                $situacion = strtoupper($row['tipo_situacion']);
                if($situacion == '1') $situacion = 'PROPIA';
                if($situacion == '2') $situacion = 'ALQUILADA';

                $html .= '
                    <tr>
                        <td style="width:25%;text-align:left; font-size:7px; padding:3px;">'.strtoupper($nombre).'</td>
                        <td style="width:10%;text-align:center; font-size:7px;">'.strtoupper($tipo).'</td>
                        <td style="width:25%;text-align:left; font-size:6.5px;">'.strtoupper($row['ubicacion']).'</td>
                        <td style="width:8%;text-align:center; font-size:7.5px;">'.$row['nro_consultorios'].'</td>
                        <td style="width:8%; text-align:center; font-size:7px;">'.$internet.'</td>
                        <td style="width:24%;text-align:left; font-size:6.5px;">'.$situacion.'</td>
                    </tr>';
            }
            $html .= '</tbody></table>';
            return $html;
        };

        $total_filas = count($detalle_1er) + count($detalle_2do) + count($detalle_otros);

        // Primera Página
        $tabla .= '<page orientation="'.$orientacion.'" backtop="15mm" backbottom="5mm" backleft="15mm" backright="15mm">
                    <div class="contenedor-reporte">
                        '.$this->cabecera_report(4, 'DIAGNÓSTICO DE INFRAESTRUCTURA DE SALUD', $get_form_distrital[0]['dist_distrital']).'
                <p class="bold" style="font-size:9px;">1. Objetivo del instrumento</p>
                <div class="box-container" style="border: 1px solid #000; font-size:8.5px;">
                    Identificar, registrar y evaluar las condiciones de la infraestructura de los establecimientos de salud, para determinar su capacidad operativa y soporte a la demanda poblacional.
                </div>
                        '.$generar_tabla('Perfil inventario de establecimientos de PRIMER NIVEL', $detalle_1er).'
                        '.$generar_tabla('Perfil inventario de establecimientos de SEGUNDO y TERCER NIVEL', $detalle_2do);

        if ($total_filas <= 20) {
            // En "Otros" mandamos el cuarto parámetro como FALSE para que no salga "según POA"
            $tabla .= $generar_tabla('Otros Establecimientos', $detalle_otros, true, false);
            $tabla .= $observacion;
            $tabla .= '<br><br><p style="text-align:center; margin-top:30px;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>';
            $tabla .= '</div></page>';
        } else {
            $tabla .= '<br><p style="text-align:right; font-size:8px;">Continúa en la siguiente página...</p>';
            $tabla .= '</div></page>';
            
            $tabla .= '<page orientation="'.$orientacion.'" backtop="15mm" backbottom="10mm" backleft="15mm" backright="15mm">
                        <div class="contenedor-reporte">
                        '.$this->cabecera_report(4, 'DIAGNÓSTICO DE INFRAESTRUCTURA DE SALUD (CONT.)', $get_form_distrital[0]['dist_distrital']).'
                        
                        '.$generar_tabla('Otros Establecimientos', $detalle_otros, true, false).'
                        '.$observacion.'
                        <br><br>
                        <p style="text-align:center; margin-top:50px;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
                        </div></page>';
        }

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