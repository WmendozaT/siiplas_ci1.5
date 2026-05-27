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
        $this->entidad   = $this->CI->session->userdata("entidad");
        $this->sistema   = $this->CI->session->userdata("sistema");
        $this->sistema_pie   = $this->CI->session->userdata("sistema_pie");
        $this->usuario   = $this->CI->session->userdata("usuario");
        $this->direccion   = $this->CI->session->userdata("direccion");
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
            return $this->form_pdf4('portrait',$get_form_distrital); //// horizontal - Infraestructura
        }
        elseif($tp_rep == 6){
            return $this->form_pdf5('portrait',$get_form_distrital); //// horizontal - Camas
        }
        elseif($tp_rep == 7){
            return $this->form_pdf6('portrait',$get_form_distrital); //// horizontal - Equipamiento
        }
        elseif($tp_rep == 8){
            return $this->form_pdf7('landscape',$get_form_distrital); //// horizontal - Recursos Humanos
        }
        elseif($tp_rep == 9){
            return $this->form_pdf8('landscape',$get_form_distrital); //// horizontal - Compra de Servicios
        }
        elseif($tp_rep == 10){
            return $this->form_pdf9('landscape',$get_form_distrital); //// horizontal - Presupuestos
        }
        elseif($tp_rep == 11){
            return $this->form_pdf10('landscape',$get_form_distrital); //// horizontal - Reembolsos
        }
        elseif($tp_rep == 12){
            return $this->form_pdf11('portrait',$get_form_distrital); //// horizontal - Ambulancia
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
                                '.$this->entidad.'
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
                            PERIODO: <b style="color: #212121;">'.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'</b>
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
                            '.$this->sistema_pie.'
                        </td>
                        
                        <!-- Zona Derecha (50% proporcional) -->
                        <td style="width: 50%; text-align: right; vertical-align: middle; font-size: 8.5px; color: #424242; font-weight: bold;">
                        '.$this->usuario.' - Página [[page_cu]] de [[page_nb]]
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
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(1,'DIAGNÓSTICO DE LA POBLACIÓN ASEGURADA',$get_form_distrital).'

        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Recopilar, validar y sistematizar información cuantitativa de la población afiliada (titulares y Beneficiarios) para analizar tendencias y cobertura y demanda potencial de la Caja Nacional de Salud.
        </div>

        <p class="bold" style="margin-top: 15px;">2. Relevamiento de la población afiliada ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                     <th style="width:20%; font-size:8px; padding:3px;">Gestión</th>
                     <th style="width:20%; font-size:8px;">Cotizantes Titulares</th>
                     <th style="width:20%; font-size:7px;">Cotizantes Pasivos</th>
                     <th style="width:20%; font-size:7px;">Beneficiarios</th>
                     <th style="width:20%; font-size:7px;">Total</th>
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
             $tabla .= '
             </tbody>
        </table>';

        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion1']) ? strtoupper($get_form_distrital[0]['observacion1']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
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

        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report('1.1.','DIAGNÓSTICO DE LA POBLACIÓN PROTEGIDA POR GRUPOS ETAREOS',$get_form_distrital).'

        <!-- ==================== CONTENIDO DINÁMICO ==================== -->
        <p class="bold">1. Relevamiento de poblacion afiliada por grupo etareo ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>
        <table class="tabla-datos" style="font-size:8px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th rowspan="2" class="nro-col" >GRUPO ETÁREO</th>
                    <th colspan="3" >2021</th>
                    <th colspan="3" >2022</th>
                    <th colspan="3" >2023</th>
                    <th colspan="3" >2024</th>
                    <th colspan="3" >2025</th>
                </tr>
                <tr style="text-align:center; background-color: #004640; color: #fff;">
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
                      <td style="font-size:10px;"><b>'.$row['grupo_etareo'].'</b></td>';
                      // Bucle para generar los 5 años (2021 al 2025)
                       for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $m = round($row['m_'.$anio],2);
                          $f = round($row['f_'.$anio],2);
                          $t = round($row['t_'.$anio],2);
                          $totales_gestion[$anio] += $t;
                          $tabla.='
                          <!-- Masculino -->
                          <td style="font-size:8px; text-align:right;">'.number_format($m, 2, '.', ',').'</td>
                          <!-- Femenino -->
                          <td style="font-size:8px; text-align:right;">'.number_format($f, 2, '.', ',').'</td>
                          <td style="background-color: #d9edf7; font-size:8px;text-align:right;"><b>'.number_format($t, 2, '.', ',').'</b></td>';
                      }
                            
                    $tabla.='</tr>';
                }
             $tabla .= '
                        <tr style="background-color: #EEEEEE; font-weight: bold;">
                            <td style="text-align:right; font-size:8px;">TOTALES POR GESTIÓN:</td>';
                            
                            for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                                $tabla .= '
                                <td colspan="2" style="text-align:center; font-size:8.5px;">Total Gestión: '.$anio.'</td>
                                <td style="text-align:center; background-color: #d9edf7; font-size:8.5px; text-align:right;">
                                    '.number_format($totales_gestion[$anio], 2, '.', ',').'
                                </td>';
                            }                    
                        $tabla .= '
                        </tr>
                    </tbody>
                </table>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        // CRÍTICO: CERRAMOS CORRECTAMENTE LA PÁGINA PARA ELIMINAR HOJAS BLANCAS FANTASMA
        $tabla .= '
        </page>';

        return $tabla;
    }


    /// formulario reporte 2 - empresas aportantes
    public function form_pdf2($orientacion,$get_form_distrital) {
        $detalle_form2=$this->CI->model_diagnosticopei->get_formulario_N2($get_form_distrital[0]['dist_id']); /// listado de gestiones
        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(2,'DIAGNÓSTICO DE EMPRESAS',$get_form_distrital).'

        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Recolectar, validar y sistematizar información anual del número de empresas aportantes, permitiendo analizar su evolución, cobertura institucional y comportamiento contributivo.
        </div>

        <p class="bold">2. Definición Operativa</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Empresa aportante: unidad económica registrada que realiza aportes al sistema en un periodo determinado, independientemente del número de trabajadores afiliados.
        </div>

        <p class="bold" style="margin-top: 15px;">2. Relevamiento de la población afiliada ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                     <th style="width:25%; font-size:8.5px; padding:3px;">Gestión</th>
                     <th style="width:25%; font-size:8.5px;">Nro. de Empresas</th>
                     <th style="width:25%; font-size:8.5px;">Con aportes al dia</th>
                     <th style="width:25%; font-size:8.5px;">En mora</th>
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
             $tabla .= '
             </tbody>
        </table>';

        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion2']) ? strtoupper($get_form_distrital[0]['observacion2']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
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

        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(3,'DIAGNÓSTICO DEL PERFIL EPIDEMIOLOGICO',$get_form_distrital).'

        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Recolectar, organizar y analizar información epidemiológica de la población afiliada, identificando tendencias de morbilidad, mortalidad y factores de riesgo en el periodo '.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].'.
        </div>

        <p class="bold" style="margin-top: 15px;">2. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Externa)</p>
        <table class="tabla-datos" style="font-size:7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th rowspan="2" class="nro-col" >N°</th>
                    <th colspan="2" >2021</th>
                    <th colspan="2" >2022</th>
                    <th colspan="2" >2023</th>
                    <th colspan="2" >2024</th>
                    <th colspan="2" >2025</th>
                 </tr>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                </tr>
             </thead>
             <tbody>';
                foreach($detalle_form3_perfil1 as $row){
                    $tabla.='
                    <tr>
                        <td style="font-size:8.5px;"><b>'.$row['nro'].'</b></td>';
                    for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $nro_casos = round($row['nro_casos_'.$anio],2);
                          $cod_cie = $row['codigo_cie_'.$anio];
                          $tabla.='
                          <!-- nro -->
                          <td style="width:4.2%; font-size:7.5px; text-align:right">'.$nro_casos.'</td>
                          <!-- cod_cie -->
                          <td style="width:15.4%; font-size:6.5px; text-align:left;">'.$cod_cie.'</td>';
                    }
                    $tabla.='
                    </tr>';
                } 
             $tabla .= '
             </tbody>
        </table>

        <p class="bold" style="margin-top: 15px;">3. Perfil de mortalidad (principales causas)</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th rowspan="2" class="nro-col" >N°</th>
                    <th colspan="2" >2021</th>
                    <th colspan="2" >2022</th>
                    <th colspan="2" >2023</th>
                    <th colspan="2" >2024</th>
                    <th colspan="2" >2025</th>
                 </tr>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                </tr>
             </thead>
             <tbody>';
                foreach($detalle_form3_perfil2 as $row){
                    $tabla.='
                    <tr>
                        <td style="font-size:8.5px;"><b>'.$row['nro'].'</b></td>';
                    for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $nro_casos = round($row['nro_casos_'.$anio],2);
                          $cod_cie = $row['codigo_cie_'.$anio];
                          $tabla.='
                          <!-- nro -->
                          <td style="width:4.2%; font-size:7.5px; text-align:right">'.$nro_casos.'</td>
                          <!-- cod_cie -->
                          <td style="width:15.4%; font-size:6.5px; text-align:left;">'.$cod_cie.'</td>';
                    }
                    $tabla.='
                    </tr>';
                } 
             $tabla .= '
             </tbody>
        </table>

        <p class="bold" style="margin-top: 15px;">2. Perfil de morbilidad (Enfermedades prevalentes / 10 primeras causas de consulta Externa)</p>
        <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
            <thead>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th rowspan="2" class="nro-col" >N°</th>
                    <th colspan="2" >2021</th>
                    <th colspan="2" >2022</th>
                    <th colspan="2" >2023</th>
                    <th colspan="2" >2024</th>
                    <th colspan="2" >2025</th>
                 </tr>
                 <tr style="text-align:center; background-color: #004640; color: #fff;">
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                    <th style="width:4.2%; font-size:8px;">Nº casos</th><th style="width:15.4%; font-size:9px;">Cod. CIE-10</th>
                </tr>
             </thead>
             <tbody>';
                foreach($detalle_form3_perfil3 as $row){
                    $tabla.='
                    <tr>
                        <td style="font-size:8.5px;"><b>'.$row['nro'].'</b></td>';
                    for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $nro_casos = round($row['nro_casos_'.$anio],2);
                          $cod_cie = $row['codigo_cie_'.$anio];
                          $tabla.='
                          <!-- nro -->
                          <td style="width:4.2%; font-size:7.5px; text-align:right">'.$nro_casos.'</td>
                          <!-- cod_cie -->
                          <td style="width:15.4%; font-size:6.5px; text-align:left;">'.$cod_cie.'</td>';
                    }
                    $tabla.='
                    </tr>';
                } 
             $tabla .= '
             </tbody>
        </table>';

        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion3']) ? strtoupper($get_form_distrital[0]['observacion3']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }


    //// Reporte Formulario Diagnostico de Infraestructura de Salud
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
        '.$this->cabecera_report(4,'DIAGNÓSTICO DE INFRAESTRUCTURA DE SALUD',$get_form_distrital).'

        <!-- ==================== CONTENIDO DINÁMICO ==================== -->
        <p class="bold">1. Objetivo del instrumento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
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
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
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


    /// formulario reporte 5 - Diagnostico Camas
    public function form_pdf5($orientacion,$get_form_distrital) {
        $establecimientos=$this->CI->model_diagnosticopei->get_diagnostico_camas($get_form_distrital[0]['dist_id']);
        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(5,'DIAGNÓSTICO CAMAS',$get_form_distrital).'

        <p class="bold">1. Matriz de gestión de camas Hospitalarias (II Y III nivel)</p>';
             foreach($establecimientos as $row){
                $tabla.='
                <small>(' . $row['tipo'] . ') </small><b>' . strtoupper($row['act_descripcion']) . '</b>
                <table class="tabla-datos" style="font-size:8.7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                    <thead>
                     <tr style="text-align:center; background-color: #004640; color: #fff;">
                        <th style="width:10%;">GESTIÓN</th>
                        <th style="width:20%;">NRO. DE CAMAS</th>
                        <th style="width:20%;">(%) DE OCUPACIÓN</th>
                        <th style="width:25%;">ESTANCIA MEDIA</th>
                        <th style="width:25%;">GIRO CAMA</th>
                     </tr>
                    </thead>
                    <tbody>';
                        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                          $tabla .= '
                          <tr>
                            <td >' . $anio . '</td>
                            <td>' . $row['camas_'.$anio] . '</td>
                            <td>' . round($row['ocupacion_'.$anio],2) . '</td>
                            <td>' . $row['estancia_'.$anio] . '</td>
                            <td>' . $row['giro_'.$anio] . '</td>
                        </tr>';
                      }
                    $tabla.='
                    </tbody>
                </table><br>';
               }

        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion5']) ? strtoupper($get_form_distrital[0]['observacion5']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }


    /// formulario reporte 6 - Diagnostico Equipamiento
    public function form_pdf6($orientacion,$get_form_distrital) {
        $listado_equipamiento=$this->CI->model_diagnosticopei->get_diagnostico_equipamiento($get_form_distrital[0]['dist_id']);
        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(6,'DIAGNÓSTICO DE EQUIPAMIENTO MAYOR',$get_form_distrital).'

        <p class="bold">1. Identificación del establecimiento</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Detalle el equipo medico mayor requerido para el funcionamiento operativo de su regional / Distrital (ej. tomógrafo, resonador, equipo de rayos X, ventiladores, etc.)
        </div>';
            $tabla.='
                <table class="tabla-datos" style="font-size:8px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                    <thead>
                     <tr style="text-align:center; background-color: #004640; color: #fff;">
                        <th style="width:2%;">#</th>
                        <th style="width:20%;">Establecimiento</th>
                        <th style="width:20%;">Servicio / Area</th>
                        <th style="width:30%;">Detalle de Equipamiento Mayor</th>
                        <th style="width:28%;">Precio Referencial</th>
                     </tr>
                    </thead>
                    <tbody>';
                    $nro=0;
                    foreach($listado_equipamiento as $row){
                    $nro++;
                    $tabla .= '
                        <tr style="font-size:9px;">
                            <td style="font-size:9px;width:2%;">'.$nro.'</td>
                            <td style="font-size:8px; text-align:left;width:20%;">'.$row['tipo']. ' '.$row['act_descripcion'].'</td>
                            <td style="font-size:8px;width:20%;">'.strtoupper($row['servicio']).'</td>
                            <td style="font-size:8px;text-align:left;width:30%;">'.strtoupper($row['detalle_equipo']).'</td>
                            <td style="font-size:8px;text-align:right;width:28%;">'.number_format($row['precio_referencial'], 2, '.', ',').'</td>
                        </tr>';
                    }
                $tabla.='
                    </tbody>
                </table>';
        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion6']) ? strtoupper($get_form_distrital[0]['observacion6']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }


    /// formulario reporte 7 - Recursos Humanos
    public function form_pdf7($orientacion,$get_form_distrital) {
        $detalle_rrhh=$this->CI->model_diagnosticopei->get_diagnostico_rrhh($get_form_distrital[0]['dist_id']);
        $profesiones = array(
          'nro_medicos' => 'MEDICOS',
          'nro_odontologos' => 'ODONTOLOGOS',
          'nro_farmaceuticos' => 'FARMACEUTICOS',
          'nro_laboratoristas' => 'LABORATORISTAS',
          'nro_otros_prof' => 'OTROS PROFESIONALES',
          'nro_nutricionistas' => 'NUTRICIONISTAS',
          'nro_trabajo_social' => 'TRABAJO SOCIAL',
          'nro_jefe_superv_enf' => 'JEF. SUPERV. ENFERMERÍA',
          'nro_lic_grad_enf' => 'LIC. EN ENFERMERÍA',
          'nro_aux_enf' => 'AUXILIARES DE ENFERMERÍA',
          'nro_pers_adm' => 'PERSONAL ADM. (ÍTEM)',
          'nro_pers_adm_salud' => 'PERSONAL ADM. SALUD',
          'nro_pers_adm_tec' => 'PERS. ADM. TÉCNICO',
          'nro_pers_adm_aux' => 'PERS. ADM. AUXILIAR',
          'nro_pers_adm_chof' => 'CHÓFERES',
          'nro_pers_adm_artesanos' => 'ARTESANOS',
          'nro_pers_adm_trab_manual' => 'TRAB. MANUALES'
        );

        $totales_columnas = array();
        for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
            for ($tp = 1; $tp <= 3; $tp++) {
                $totales_columnas[$anio][$tp] = 0; // Inicializamos cada columna en cero
            }
        }

        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(7,'DIAGNÓSTICO DE RECURSOS HUMANOS',$get_form_distrital).'

        <p class="bold">1. Cuadro del Personal por Items, Contrato, Acefalias</p>';
            $tabla.='
                <table class="tabla-datos" style="font-size:7px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                    <thead>
                      <tr style="background: #f5f5f5; color: #666;">
                          <th rowspan="2" style="width: 10%; vertical-align: middle; text-align:center; min-width: 180px; border-top:none; font-size:8px;">CATEGORÍA / PROFESIÓN</th>';
                          
                          // Cabecera de Años
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $tabla .= '<th colspan="3" style="text-align:center; font-size: 10px;">GESTIÓN '.$anio.'</th>';
                          }
                      
                      $tabla .= '
                      </tr>
                      <tr>';
                          // Sub-cabecera de Tipos
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              $tabla .= '<th style="text-align:center; width:36.2px; font-size:7px;">ITEMS</th>';
                              $tabla .= '<th style="text-align:center; width:36.2px; font-size:7px;">CONTR.</th>';
                              $tabla .= '<th style="text-align:center; width:36.2px; font-size:7px;">ACEF.</th>';
                          }
                      $tabla .= '</tr>
                    </thead>
                    <tbody>';
                    
                    // Generación de filas de profesiones
                    foreach ($profesiones as $campo => $label) {
                      $tabla .= '
                      <tr>
                          <td style="background: #f9f9f9; font-weight: bold; padding: 5px 10px; text-align:left; border-right: 2px solid #eee; font-size:6.5px;">
                              '.$label.'
                          </td>';
                          
                          for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                              for ($tp = 1; $tp <= 3; $tp++) {
                                  // Búsqueda del valor en el array de la base de datos
                                  $valor = 0;
                                  foreach ($detalle_rrhh as $res) {
                                      if ($res['gestion'] == $anio && $res['tp_rrhh_form'] == $tp) {
                                          $valor = (int)$res[$campo]; // Forzamos a entero para la suma
                                          break;
                                      }
                                  }

                                  // 2. ACUMULAMOS EL VALOR EN LA GESTIÓN Y TIPO CORRESPONDIENTE
                                  $totales_columnas[$anio][$tp] += $valor;

                                  // Estilo condicional para Acefalías (columna 3 de cada año)
                                  $tdStyle = ($tp == 3) ? 'background-color: #fff8f8;' : '';

                                  $tabla .= '
                                  <td style="padding: 2px; font-size:8px; text-align:right; padding-right:3px; '.$tdStyle.'">'.number_format($valor, 0, '.', ',').'</td>';
                              }
                          }
                      $tabla .= '</tr>';
                    }

                    $tabla .= '
                    </tbody>
                    <tfoot>
                        <tr style="background: #e0e0e0; font-weight: bold; border-top: 2px solid #E91E63;">
                            <td style="text-align:right; padding: 5px 10px; font-size: 8px; vertical-align: middle;">TOTAL PERSONAL:</td>';
                            
                            // 3. RENDERIZAMOS LOS TOTALES PRECALCULADOS EN EL TFOOT
                            for ($anio = $get_form_distrital[0]['g_id_inicio']; $anio <= $get_form_distrital[0]['g_id_fin']; $anio++) {
                                for ($tp = 1; $tp <= 3; $tp++) {
                                    
                                    // Obtenemos el gran total acumulado para esta columna
                                    $total_final_columna = $totales_columnas[$anio][$tp];
                                    
                                    $bg_total = ($tp == 3) ? '#ffebee' : '#e3f2fd'; // Color diferente para total acefalías

                                    $tabla .= '
                                    <td style="text-align:right; padding: 5px; padding-right:3px; background-color: '.$bg_total.'; border: 1px solid #ddd; font-size:8px; color:#0d47a1; font-weight: bold;">
                                        '.number_format($total_final_columna, 0, '.', ',').'
                                    </td>';
                                }
                            }
                    $tabla .= '
                        </tr>
                    </tfoot>
                </table>';
        $tabla .= '
        <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
        <div class="box-container" style="width: 100%; height: 35px; border: 0.5px solid #000; font-size:8px; padding:5px;">
            '.(!empty($get_form_distrital[0]['observacion7']) ? strtoupper($get_form_distrital[0]['observacion7']) : 'SIN OBSERVACIONES').'
        </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }


    /// formulario reporte 8 - Compra de Servicios
    public function form_pdf8($orientacion,$get_form_distrital) {
        $detalle_cservicios=$this->CI->model_diagnosticopei->get_diagnostico_compra_servicios($get_form_distrital[0]['dist_id']);

        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(8,'DIAGNÓSTICO DE COMPRA DE SERVICIOS',$get_form_distrital).'

        <p class="bold">1. Identificación de las tres principales compra de servicios</p>';
            $tabla.='
                <table class="tabla-datos" style="font-size:8px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                    <thead>
                      <tr style="background: #FFF3E0; color: #E65100; font-weight: bold;">
                          <th style="width:8%; text-align:center; vertical-align: middle; padding: 5px 0;font-size: 8px;">AÑO</th>
                          <th style="width:32%; text-align:center; vertical-align: middle;font-size: 8px;">SERVICIO CONTRATADO</th>
                          <th style="width:12%; text-align:center; vertical-align: middle;font-size: 8px;">N° ATENCIONES</th>
                          <th style="width:15%; text-align:center; vertical-align: middle;font-size: 8px;">COSTO TOTAL (BS.)</th>
                          <th style="width:33%; text-align:center; vertical-align: middle;font-size: 8px;">OBSERVACIONES</th>
                      </tr>
                    </thead>
                    <tbody>';
                    
                    $colores = [
                      '2021' => '#F5F5F5', // Gris claro
                      '2022' => '#FFFFFF', // Blanco
                      '2023' => '#F5F5F5', 
                      '2024' => '#FFFFFF', 
                      '2025' => '#F5F5F5'
                    ];

                    foreach ($detalle_cservicios as $row) {
                      $bg = isset($colores[$row['gestion']]) ? $colores[$row['gestion']] : '#fff';
                      
                      // Convertimos a tipos numéricos para el acumulador seguro
                      $atenciones = intval($row['nro_atenciones']);
                      $costo = floatval($row['costo_total']);

                      
                      // Control de textos vacíos para evitar celdas en blanco huérfanas
                      $servicio = !empty($row['serv_contratado']) ? strtoupper(trim($row['serv_contratado'])) : '-';
                      $observaciones = !empty($row['cservicios_observaciones']) ? strtoupper(trim($row['cservicios_observaciones'])) : '-';

                      $tabla .= '
                      <tr style="background-color: '.$bg.';">
                        <td style="width:8%;text-align:center; vertical-align:middle; font-weight:bold; border-right: 2px solid #ddd; font-size: 11px; padding: 4px 0;">
                            '.$row['gestion'].'
                        </td>
                        
                        <!-- Servicio contratado (Alineado a la izquierda con margen interno de lectura) -->
                        <td style="width: 32%; vertical-align: middle; text-align: left; padding: 4px; font-size: 9px; word-wrap: break-word;">
                            '.$servicio.'
                        </td>
                        
                        <!-- Nro Atenciones (Alineado a la derecha) -->
                        <td style="width:12%;vertical-align: middle; text-align:right; padding: 4px; padding-right: 6px; font-size: 9px;">
                            '.($atenciones > 0 ? number_format($atenciones, 0, '.', ',') : '0').'
                        </td>
                        
                        <!-- Costo Total Anual (Formato monetario alineado a la derecha) -->
                        <td style="width:15%;vertical-align: middle; text-align:right; padding: 4px; padding-right: 6px; font-weight:bold; color: #1b5e20; font-size: 9px;">
                            '.number_format($costo, 2, '.', ',').'
                        </td>
                        
                        <!-- Observaciones -->
                        <td style="width:33%;vertical-align: middle; text-align:left; padding: 4px; color: #555; font-size: 9px;">'.$observaciones.'</td>
                    </tr>';
                  }

                $tabla .= '
                    </tbody>
                </table>';

                $tabla .= '
                <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
                <div class="box-container" style="width: 100%; height: 35px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                    '.(!empty($get_form_distrital[0]['observacion8']) ? strtoupper($get_form_distrital[0]['observacion8']) : 'SIN OBSERVACIONES').'
                </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }



    /// formulario reporte 9 - Presupuestos
    public function form_pdf9($orientacion,$get_form_distrital) {
        $listado_presupuesto=$this->CI->model_diagnosticopei->get_diagnostico_presupuestos($get_form_distrital[0]['dist_id']);
        $totales = array(
            'ipp' => 0, 'ipe' => 0, 'rfp' => 0, 'rfe' => 0, 
            'tie' => 0, 'gp'  => 0, 'ge'  => 0, 'ds'  => 0
        );
        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(9,'DIAGNÓSTICO DE INGRESOS Y GASTOS ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')',$get_form_distrital).'

        <p class="bold">1. Diagnostico de Ingresos y Gastos ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>';
            $tabla.='
                <table class="tabla-datos" style="font-size:7.5px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                    <thead>
                      <tr style="background: #FFF3E0; color: #000000; font-weight: bold;">
                          <th style="width:8%; text-align:center; vertical-align: middle; padding: 5px 0;font-size:7.5px;">GESTIÓN</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">INGRESOS PROPIOS Prog.</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">INGRESOS PROPIOS Ejec.</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">RECURSOS FINAN. Prog.</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">RECURSOS FINAN. Ejec.</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">TOTAL INGRESOS EJECUTADOS</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">GASTOS Programados</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">GASTOS Ejecutados</th>
                          <th style="width:11.4%; text-align:center; vertical-align: middle;font-size:8px;">DEFICIT / SUPERAVIT</th>
                      </tr>
                    </thead>
                    <tbody>';
                    
                    foreach ($listado_presupuesto as $row) {
                        // Conversión segura de tipos float para cálculos internos precisos
                        $ipp = floatval($row['ingresos_propios_programados']);
                        $ipe = floatval($row['ingresos_propios_ejecutados']);
                        $rfp = floatval($row['recursos_financieros_programados']);
                        $rfe = floatval($row['recursos_financieros_ejecutados']);
                        $tie = floatval($row['total_ingresos_ejecutados']);
                        $gp  = floatval($row['gastos_programados']);
                        $ge  = floatval($row['gastos_ejecutados']);
                        $ds  = floatval($row['deficit_superavit']);

                        // 2. ACUMULAMOS LOS VALORES DE CADA COLUMNA
                        $totales['ipp'] += $ipp; $totales['ipe'] += $ipe;
                        $totales['rfp'] += $rfp; $totales['rfe'] += $rfe;
                        $totales['tie'] += $tie; $totales['gp']  += $gp;
                        $totales['ge']  += $ge;  $totales['ds']  += $ds;

                        // Color condicional nativo para la celda de Déficit/Superávit
                        $color_ds = ($ds < 0) ? '#C62828' : '#2E7D32';

                        $tabla .= '
                        <tr class="fila-presupuesto">
                            <td style="text-align:center; vertical-align:middle; font-weight:bold; font-size:11px; padding: 4px 0;">
                                '.$row['gestion'].'
                            </td>
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px;font-size:8px;">'.number_format($ipp, 2, '.', ',').'</td>
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px;font-size:8px;">'.number_format($ipe, 2, '.', ',').'</td>
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px;font-size:8px;">'.number_format($rfp, 2, '.', ',').'</td>
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px;font-size:8px;">'.number_format($rfe, 2, '.', ',').'</td>
                            
                            <!-- Campos Calculados (Estilo diferenciado con fondo gris suave) -->
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px; background:#f5f5f5; font-weight:bold;font-size:8px;">'.number_format($tie, 2, '.', ',').'</td>
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px; background:#f5f5f5; font-weight:bold;font-size:8px;">'.number_format($gp, 2, '.', ',').'</td>
                            
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px;font-size:8px;">'.number_format($ge, 2, '.', ',').'</td>
                            
                            <!-- Déficit / Superávit con color adaptado -->
                            <td style="text-align:right; vertical-align:middle; padding: 4px; padding-right:3px; background:#f5f5f5; font-weight:bold; color:'.$color_ds.';font-size:8px;">
                                '.number_format($ds, 2, '.', ',').'
                            </td>
                        </tr>';
                    }

                $color_total_ds = ($totales['ds'] < 0) ? '#C62828' : '#2E7D32';

                $tabla .= '
                    </tbody>
                    <!-- 3. PIE DE TABLA CON TOTALES CONSOLIDADOS DE TODO EL PERIODO -->
                    <tfoot>
                        <tr style="background: #FFF3E0; font-weight: bold; border-top: 2px solid #000;">
                            <td style="text-align:right; padding: 5px; font-size: 8px; vertical-align: middle;font-size:10px;">TOTAL:</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px;font-size:8px;">'.number_format($totales['ipp'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px;font-size:8px;">'.number_format($totales['ipe'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px;font-size:8px;">'.number_format($totales['rfp'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px;font-size:8px;">'.number_format($totales['rfe'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px; background:#e0e0e0;font-size:8px;">'.number_format($totales['tie'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px; background:#e0e0e0;font-size:8px;">'.number_format($totales['gp'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px;font-size:8px;">'.number_format($totales['ge'], 2, '.', ',').'</td>
                            <td style="text-align:right; padding: 5px; padding-right:3px; background:#e0e0e0; color:'.$color_total_ds.';font-size:8px;">
                                '.number_format($totales['ds'], 2, '.', ',').'
                            </td>
                        </tr>
                    </tfoot>
                </table>';
                $tabla .= '
                <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
                <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                    '.(!empty($get_form_distrital[0]['observacion9']) ? strtoupper($get_form_distrital[0]['observacion9']) : 'SIN OBSERVACIONES').'
                </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }


    /// formulario reporte 10 - Reembolsos
    public function form_pdf10($orientacion,$get_form_distrital) {
        $listado_reembolsos=$this->CI->model_diagnosticopei->get_diagnostico_reembolsos($get_form_distrital[0]['dist_id']);
        $totales_reemb = array(
            'med'   => 0,
            'lab'   => 0,
            'img'   => 0,
            'otr'   => 0,
            'total' => 0
        );
        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(10,'DIAGNÓSTICO DE REEMBOLSOS ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')',$get_form_distrital).'

        <p class="bold">1. Diagnostico de Reembolsos ('.$get_form_distrital[0]['g_id_inicio'].' - '.$get_form_distrital[0]['g_id_fin'].')</p>';
            $tabla.='
                <table class="tabla-datos" style="font-size:8px; width:100%; border-collapse: collapse; table-layout: fixed;" border="1">
                <thead>
                    <tr style="background: #e8eaf6; color: #1a237e; font-weight: bold;">
                        <th style="width:10%; text-align:center; vertical-align: middle;font-size: 8.5px; padding: 5px 0;">GESTIÓN</th>
                        <th style="width:18%; text-align:center; vertical-align: middle;font-size: 8.5px;">MEDICAMENTOS</th>
                        <th style="width:18%; text-align:center; vertical-align: middle;font-size: 8.5px;">LABORATORIO</th>
                        <th style="width:18%; text-align:center; vertical-align: middle;font-size: 8.5px;">IMAGENOLOGÍA</th>
                        <th style="width:18%; text-align:center; vertical-align: middle;font-size: 8.5px;">OTROS CONCEPTOS</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;background:#c5cae9;">TOTAL REEMBOLSOS</th>
                    </tr>
                </thead>
                <tbody>';
                
                foreach ($listado_reembolsos as $row) {
                    // Conversión segura a punto flotante para cálculos matemáticos exactos en el backend
                    $med = floatval($row['reemb_concep_medicamentos']);
                    $lab = floatval($row['reemb_concep_laboratorio']);
                    $img = floatval($row['reemb_concep_imagenologia']);
                    $otr = floatval($row['reemb_otros_conceptos']);
                    $tot = floatval($row['total_reembolsos']);

                    // 2. ACUMULAMOS LOS MONTOS CELDA POR CELDA
                    $totales_reemb['med']   += $med;
                    $totales_reemb['lab']   += $lab;
                    $totales_reemb['img']   += $img;
                    $totales_reemb['otr']   += $otr;
                    $totales_reemb['total'] += $tot;

                    $tabla .= '
                    <tr class="fila-reembolso">
                        <td style="text-align:center; vertical-align:middle; font-weight:bold; background:#f9f9f9; font-size:11px; padding: 5px 0;">
                            '.$row['gestion'].'
                        </td>
                        <!-- Importes formateados y alineados a la derecha con margen interno de seguridad -->
                        <td style="text-align:right; vertical-align:middle; padding: 5px; padding-right:4px;font-size: 8.5px;">'.number_format($med, 2, '.', ',').'</td>
                        <td style="text-align:right; vertical-align:middle; padding: 5px; padding-right:4px;font-size: 8.5px;">'.number_format($lab, 2, '.', ',').'</td>
                        <td style="text-align:right; vertical-align:middle; padding: 5px; padding-right:4px;font-size: 8.5px;">'.number_format($img, 2, '.', ',').'</td>
                        <td style="text-align:right; vertical-align:middle; padding: 5px; padding-right:4px;font-size: 8.5px;">'.number_format($otr, 2, '.', ',').'</td>
                        
                        <!-- Columna del Total por Gestión (Resaltado con fondo gris suave) -->
                        <td style="text-align:right; vertical-align:middle; padding: 5px; padding-right:4px; background:#f5f5f5; font-weight:bold; color:#1a237e;">
                            '.number_format($tot, 2, '.', ',').'
                        </td>
                    </tr>';
                }

            $tabla .= '
                </tbody>
                <!-- 3. PIE DE TABLA CON CONSOLIDADO DE SUMATORIAS FINALES -->
                <tfoot>
                    <tr style="background: #e8eaf6; font-weight: bold; border-top: 2px solid #1a237e;">
                        <td style="text-align:right; padding: 6px; font-size: 8.5px; vertical-align: middle; color:#1a237e;">TOTAL:</td>
                        <td style="text-align:right; padding: 6px; padding-right:4px; font-size: 8.5px;">'.number_format($totales_reemb['med'], 2, '.', ',').'</td>
                        <td style="text-align:right; padding: 6px; padding-right:4px; font-size: 8.5px;">'.number_format($totales_reemb['lab'], 2, '.', ',').'</td>
                        <td style="text-align:right; padding: 6px; padding-right:4px; font-size: 8.5px;">'.number_format($totales_reemb['img'], 2, '.', ',').'</td>
                        <td style="text-align:right; padding: 6px; padding-right:4px; font-size: 8.5px;">'.number_format($totales_reemb['otr'], 2, '.', ',').'</td>
                        
                        <!-- Gran Total Nacional/Regional (Fondo Azul Claro de Enfoque) -->
                        <td style="text-align:right; padding: 6px; padding-right:4px; font-size: 9px; background:#c5cae9; color:#1a237e;">
                            '.number_format($totales_reemb['total'], 2, '.', ',').'
                        </td>
                    </tr>
                </tfoot>
            </table>';
                $tabla .= '
                <p class="bold" style="margin-top: 15px;">- Observaciones adicionales</p>
                <div class="box-container" style="width: 100%; height: 45px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                    '.(!empty($get_form_distrital[0]['observacion10']) ? strtoupper($get_form_distrital[0]['observacion10']) : 'SIN OBSERVACIONES').'
                </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
        </page>';

        return $tabla;
    }



    /// formulario reporte 11 - Ambulancias
    public function form_pdf11($orientacion,$get_form_distrital) {
        $listado_ambulancias=$this->CI->model_diagnosticopei->get_detalle_ambulancias($get_form_distrital[0]['dist_id']);

        $tabla='';
        $tabla = $this->style_report();
        $tabla .= ' 
        <page orientation="'.$orientacion.'" backtop="30mm" backbottom="15mm" backleft="15mm" backright="15mm">
        '.$this->cabecera_report(11,'DETALLE DE AMBULANCIAS',$get_form_distrital).'

        <p class="bold">1. Objetivo</p>
        <div class="box-container" style="width: 100%; border: 1px solid #000; font-size:10.5px; padding: 8px; margin-bottom: 15px;">
            Inventario General del Parque Automotor de Ambulancias por Establecimiento de Salud de la Regional/Distrital
        </div>';
            
            $tabla.='
            <table class="tabla-datos" style="font-size: 8px; width: 100%; border-collapse: collapse; table-layout: fixed;" border="1">
                <thead>
                    <tr style="background: #e8eaf6; color: #1a237e; font-weight: bold; height: 25px;">
                        <th style="width:3%; text-align:center; vertical-align: middle; font-size: 8.5px; padding: 5px 0;">#</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">PLACA</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">AÑO ADJUDICACIÓN</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">ESTADO</th>
                        <th style="width:18%; text-align:center; vertical-align: middle; font-size: 8.5px;">SITUACIÓN</th>
                        <th style="width:25%; text-align:center; vertical-align: middle; font-size: 8.5px;">ESTABLECIMIENTO</th>
                    </tr>
                </thead>
                <tbody>';
                
                // Contador correlativo plano independiente
                $nro = 0; 
                
                foreach ($listado_ambulancias as $row) {
                    $nro++;
                    
                    // Formateamos las cadenas para asegurar consistencia contable en mayúsculas
                    $placa_rep       = !empty($row['placa']) ? strtoupper(trim($row['placa'])) : '---';
                    $gestion_rep     = ($row['anio_adjudicacion'] > 0) ? intval($row['anio_adjudicacion']) : '---';
                    $estado_rep      = !empty($row['estado_ambulancia']) ? strtoupper(trim($row['estado_ambulancia'])) : 'SIN REGISTRO';
                    $situacion_rep   = !empty($row['situacion_ambulancia']) ? strtoupper(trim($row['situacion_ambulancia'])) : 'SIN REGISTRO';
                    $establecimiento = !empty($row['establecimiento']) ? strtoupper(trim($row['establecimiento'])) : 'SIN ASIGNACIÓN';

                    $tabla .= '
                    <tr style="height: 22px;">
                        <!-- Número correlativo automático de la grilla -->
                        <td style="text-align:center; vertical-align: middle; font-weight: bold; background:#f9f9f9;">' . $nro . '</td>
                        
                        <!-- Datos técnicos del parque automotor sanitario -->
                        <td style="text-align:center; vertical-align: middle; font-weight: bold; color: #0d47a1;">' . $placa_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-size:8px;">' . $gestion_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-weight: 500;font-size:8px;">' . $estado_rep . '</td>
                        <td style="text-align:center; vertical-align: middle; font-weight: 500;font-size:8px;">' . $situacion_rep . '</td>
                        
                        <!-- Alineación del Centro de Salud a la derecha con padding de resguardo -->
                        <td style="text-align:left; vertical-align: middle; font-weight: bold; color: #1a237e; padding-left: 5px;font-size:8px;">' . $establecimiento . '</td>
                    </tr>';
                }

                // CONTROL DE REJILLA VACÍA: Si no hay registros inyectados, dibuja una fila informativa para mantener la estética
                if ($nro === 0) {
                    $tabla .= '
                    <tr style="height: 30px;">
                        <td style="text-align:center; vertical-align: middle; color:#777; font-weight:bold;">-</td>
                        <td colspan="5" style="text-align:center; vertical-align: middle; color:#999; font-style:italic; font-size:9px;">
                            <i class="fa fa-info-circle"></i> No se encontraron unidades de transporte sanitario registradas en el inventario oficial de esta regional.
                        </td>
                    </tr>';
                }

            $tabla .= '
                </tbody>
            </table>';
                $tabla .= '
                <p class="bold" style="margin-top: 15px;">2. Observaciones adicionales</p>
                <div class="box-container" style="width: 100%; height: 55px; border: 0.5px solid #000; font-size:8px; padding:5px;">
                    '.(!empty($get_form_distrital[0]['observacion11']) ? strtoupper($get_form_distrital[0]['observacion11']) : 'SIN OBSERVACIONES').'
                </div>';

        $tabla .= '
        <div style="width: 100%; margin-top: 20mm; text-align: center; page-break-inside: avoid; display: block;">
            <p style="font-size: 11px; margin: 0; padding: 0;"><strong>'.strtoupper($get_form_distrital[0]['tipo_firma']).'</strong></p>
        </div>';

        $tabla .= '
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


}