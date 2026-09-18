<?php
  class Exporting_datos extends CI_Controller { 
  public function __construct (){ 
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_componente');
        $this->load->model('programacion/model_producto');
        
        $this->load->model('programacion/insumos/minsumos');
        $this->load->model('reportes/mreporte_operaciones/mrep_operaciones');
        $this->load->model('ejecucion/model_ejecucion');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('programacion/insumos/model_insumo');
        $this->load->model('ejecucion/model_certificacion');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->tmes = $this->session->userData('trimestre');
        $this->load->library('genera_informacion');
      }else{
          redirect('/','refresh');
      }
    }
    
    /*----------- TIPO DE REGISTRO ---------*/
    public function tp_resp(){
      $ddep = $this->model_proyecto->dep_dist($this->dist);
      if($this->adm==1){
        $titulo='RESPONSABLE NACIONAL';
      }
      elseif($this->adm==2){
        $titulo='RESPONSABLE '.strtoupper($ddep[0]['dist_distrital']);
      }

      return $titulo;
    }

    /*----- exportar COMPARATIVO PRESUPUESTO ASIG-POA (EXCEL) -----*/
    // public function comparativo_presupuesto_xls($dep_id,$tp){
    //   $data['dep']=$this->model_proyecto->get_departamento($dep_id);
    //   if(count($data['dep'])!=0){
    //     $data['tp']=$tp;

    //     if($tp==1){ /// Unidad Organizacional
    //       $data['lista']=$this->lista_uo($dep_id,$data['dep']);
    //     }
    //     else{ /// Proyecto de Inversion
    //       $data['lista']=$this->lista_partidas($dep_id);
    //     }
    //   }
    //   else{
    //     echo "Error!! no existe Region";
    //   }
    // }

    /*------ LISTA UNIDADES ORGANIZACIONALES (EXCEL) -----*/
/*    public function lista_uo($dep_id,$dep){
      $tabla='';
        $unidades=$this->mrep_operaciones->list_unidades_regional($dep_id); /// Unidades, Proyectos de la Regional
        $tabla .='
          <style>
            table{font-size: 9px;
              width: 100%;
              max-width:1550px;
              overflow-x: scroll;
            }
            th{
              padding: 1.4px;
              text-align: center;
              font-size: 10px;
            }
          </style>';

        $tabla.='<table><tr><td colspan=7 align=center style="height:50px;font-size: 15pt;"><b>CUADRO COMPARATIVO DE PRESUPUESTO ASIGNADO Vs PROGRAMADO POA '.$this->gestion.'<br>REGIONAL : '.strtoupper($dep[0]['dep_departamento']).'</b></td></tr></table>';
        $tabla.='<table  border="1" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                <thead>
                  <tr style="font-size: 7px;" bgcolor=#1c7368 align=center>
                    <th style="width:2%;height:20px;color:#FFF;">#</th>
                    <th style="width:10%;color:#FFF;">CATEGORIA PROGRAM&Aacute;TICA</th>
                    <th style="width:30%;color:#FFF;">UNIDAD, ESTABLECIMIENTO/PROYECTO DE INVERSI&Oacute;N</th>
                    <th style="width:15%;color:#FFF;">TIPO DE OPERACI&Oacute;N</th>
                    <th style="width:10%; color:#FFF;">PRESUPUESTO ASIGNADO '.$this->gestion.'</th> 
                    <th style="width:10%; color:#FFF;">PRESUPUESTO PROGRAMADO '.$this->gestion.'</th>
                    <th style="width:10%; color:#FFF;">DIFERENCIA</th>  
                  </tr>
                </thead>
                <tbody>';
                $nro=0; $monto_total_asig=0; $monto_total_poa=0; $monto_total_dif=0;
                foreach($unidades as $row){
                  $m_asig=0;$m_poa=0;$dif=0;$color='';
                  $monto_asig=$this->model_ptto_sigep->suma_ptto_accion($row['aper_id'],1);
                  if(count($monto_asig)!=0){
                    $m_asig=$monto_asig[0]['monto'];
                  }
                  
                  if($row['tp_id']==1){ /// Proyecto de Inversion
                    $monto_poa=$this->model_ptto_sigep->suma_ptto_pinversion($row['proy_id']);
                  }
                  else{ /// Gasto Corriente
                    $monto_poa=$this->model_ptto_sigep->suma_ptto_accion($row['aper_id'],2);
                  }

                  if(count($monto_poa)!=0){
                    $m_poa=$monto_poa[0]['monto'];
                  }

                  $dif=$m_asig-$m_poa;
                  if($dif<0){
                    $color='#f9cdcd';
                  }

                  $nro++;
                  $monto_total_asig=$monto_total_asig+$m_asig;
                  $monto_total_poa=$monto_total_poa+$m_poa;
                  $monto_total_dif=$monto_total_asig-$monto_total_poa;
                  $tabla.='<tr bgcolor="'.$color.'">';
                    $tabla.='<td style="width: 2%; text-align: center; height:15px;"><b>'.$nro.'</b></td>
                              <td style="width: 10%; text-align: center;">\''.$row['aper_programa'].''.$row['aper_proyecto'].''.$row['aper_actividad'].'\'</td>';
                              if($row['tp_id']==1){
                                $tabla.='<td style="width: 30%; text-align: left;">'.mb_convert_encoding(strtoupper($row['proy_nombre']), 'cp1252', 'UTF-8').'</td>';
                              }
                              else{
                                $tabla.='<td style="width: 30%; text-align: left;">'.mb_convert_encoding(strtoupper($row['tipo'].' '.$row['act_descripcion'].' '.$row['abrev']), 'cp1252', 'UTF-8').'</td>';
                              }
                              $tabla.='
                              
                              <td style="width: 15%; text-align: left;">'.mb_convert_encoding(strtoupper($row['tp_tipo']), 'cp1252', 'UTF-8').'</td>
                              <td style="width: 10%; text-align: right;">'.round($m_asig,2).'</td>
                              <td style="width: 10%; text-align: right;">'.round($m_poa,2).'</td>
                              <td style="width: 10%; text-align: right;">'.round($dif,2).'</td>';
                  $tabla.='</tr>';
                }
        $tabla.='</tbody>
                  <tr>
                    <td colspan="4"><b>PRESUPUESTO TOTAL : </b></td>
                    <td style="text-align: right;height:15px;">'.round($monto_total_asig,2).'</td>
                    <td style="text-align: right;">'.round($monto_total_poa,2).'</td>
                    <td style="text-align: right;">'.round($monto_total_dif,2).'</td>
                  </tr>
              </table>';

          date_default_timezone_set('America/Lima');
          header('Content-type: application/vnd.ms-excel');
          header("Content-Disposition: attachment; filename=Ptto_Comparativo_Regional.xls"); //Indica el nombre del archivo resultante
          header("Pragma: no-cache");
          header("Expires: 0");
          echo "";
          echo $tabla;
    }*/


    /*------ EXPORTAR ACTIVIDADES Institucional (Reporte Gerencial) -------*/
    public function exportar_formularioN4_Institucional() {
        // 1. Configuración y ampliación drástica de recursos del servidor
        @set_time_limit(1200);             // 20 minutos de ejecución interna elástica
        ini_set('memory_limit', '2048M'); // 2 GB de memoria RAM asignada para alta densidad

        // 2. Extracción de datos nacionales parametrizados por la función SQL corregida
        $form4 = $this->model_producto->lista_form4_institucional_completo(); 

        if (!empty($form4)) {
            // Estructuración formal del nombre del archivo según el estándar del PEI
            $nombre_archivo = "Consolidado_Formulario_N4_" . $this->gestion . "_Institucional.xls";

            // 3. Limpieza radical de buffers fantasmas para blindar el binario
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();

            // 4. Protocolo de cabeceras HTTP rígidas para forzar descarga directa
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Expires: 0");

            // 5. Inyección de la directiva BOM UTF-8 para proteger acentos y Ñs en Windows
            echo "\xEF\xBB\xBF";

            // 6. Construcción idéntica de la sábana de datos en una sola variable string
            $tabla = '';
            $tabla.= $this->stream_lista_excel_actividades($form4,$tp_id);
            
            // 7. Despacho directo del buffer de datos purificado hacia la red institucional
            echo $tabla;
            ob_end_flush();
            exit; // Detiene la ejecución física impidiendo layouts o filtraciones del framework
        } else {
            echo "<center><br><h3>🚨 Error de Consistencia: No se registran datos disponibles del Formulario N° 4 Nacional para la gestión activa " . $this->gestion . ".</h3></center>";
        }
    }





    /*------ EXPORTAR ACTIVIDADES Regional - Distrital 2027 (Reporte Gerencial) -------*/
    public function exportar_formularioN4_Regional($dep_id, $dist_id, $tp_id) {
      // 1. Configuración y ampliación drástica de recursos del servidor
      set_time_limit(1200);             // 20 minutos de ejecución interna
      ini_set('memory_limit', '1024M'); // 1 GB de memoria RAM asignada

      // 2. Extracción de datos nacionales parametrizados por la función SQL corregida
      //$form4 = $this->model_proyecto->get_formulario4_consolidado_nacional($this->gestion, $dep_id, $dist_id, $tp_id);

        if($dist_id==0){
          $regional=$this->model_proyecto->get_departamento($dep_id);
          $form4=$this->model_producto->lista_form4_x_regional_completo($dep_id,$tp_id); /// Actividades a Nivel de Regional
          $tit=strtoupper($regional[0]['dep_departamento']);
        }
        else{
          $dist=$this->model_proyecto->dep_dist($dist_id);
          $form4=$this->model_producto->lista_form4_x_distrital_completo($dist_id,$tp_id); /// Actividades a Nivel de distritales
          $tit=strtoupper($dist[0]['dist_distrital']);
        }

        if (!empty($form4)) {
          // Estructuración formal del nombre del archivo según el estándar del PEI
          $nombre_archivo = "Consolidado_Formulario_N4_" . $this->gestion . "_Reg_" . $tit . ".xls";

          // 3. Limpieza radical de buffers fantasmas para blindar el binario
          if (ob_get_length()) {
              ob_clean();
          }
          ob_start();

          // 4. Protocolo de cabeceras HTTP rígidas para forzar descarga directa
          header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
          header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
          header("Cache-Control: max-age=0, no-cache, must-revalidate");
          header("Pragma: public");

          // 5. Inyección de la directiva BOM UTF-8 para proteger acentos y Ñs en Windows
          echo "\xEF\xBB\xBF";

          // 6. Construcción idéntica de la sábana de datos en una sola variable string
          $tabla = '';
          $tabla.= $this->stream_lista_excel_actividades($form4,$tp_id);
          // Imprimimos la variable que contiene el HTML y vaciamos el buffer
          echo $tabla;
          ob_end_flush();
          exit;
      } else {
          echo "<h3>⚠️ Error SIIPLAS: No se encontraron registros actividades cargados para los criterios seleccionados.</h3>";
      }
    }

     //// matriz Actividades segun el tipo de Gasto
    private function stream_lista_excel_actividades($form4,$tp_id) {
      $tabla='';
      $tabla .= '
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <table border="0.5" cellpadding="4" cellspacing="0">
            <thead>
              <tr style="background-color: #1a237e; color: #ffffff; font-weight: bold; height: 35px; font-family: Arial, sans-serif; font-size: 9px; text-transform: uppercase;">
                  <th style="background-color: #1a237e; color: #ffffff;">REG.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. REG.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">REGIONAL</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. DIST.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. DISTRITAL</th>

                  <th style="background-color: #1a237e; color: #ffffff;">COD. DA.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. UE.</th>

                  <th style="background-color: #1a237e; color: #ffffff;">APER. PROG. '.$this->gestion.'</th>
                  <th style="background-color: #1a237e; color: #ffffff;">APER. PROY. '.$this->gestion.'</th>
                  <th style="background-color: #1a237e; color: #ffffff;">APER. ACT. '.$this->gestion.'</th>

                  <th style="background-color: #1a237e; color: #ffffff;">TIPO GASTO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. SISIN</th>

                  <th style="background-color: #1a237e; color: #ffffff;">GASTO CORRIENTE / INVERSIÓN</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COMPONENTE / UNIDAD RESPONSABLE</th>
                  
                  <th style="background-color: #1a237e; color: #ffffff;">ID</th>

                  <th style="background-color: #1a237e; color: #ffffff;">COD. ACP</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. OPE</th>

                  <th style="background-color: #1a237e; color: #ffffff;">DESCRIPCIÓN OPERACIÓN '.$this->gestion.'</th>

                  <th style="background-color: #1a237e; color: #ffffff;">COD. ACT. POA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRIORIDAD POA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRIORIDAD SIGEP</th>
                  <th style="background-color: #1a237e; color: #ffffff;">DESCRIPCIÓN DE LA ACTIVIDAD</th>
                  <th style="background-color: #1a237e; color: #ffffff;">RESULTADO ESPERADO</th>

                  <th style="background-color: #1a237e; color: #ffffff;">TIPO INDICADOR</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TIPO META</th>

                  <th style="background-color: #1a237e; color: #ffffff;">FÓRMULA DEL INDICADOR</th>
                  <th style="background-color: #1a237e; color: #ffffff;">UNIDAD RESPONSABLE</th>
                  <th style="background-color: #1a237e; color: #ffffff;">META ANUAL</th>
                  
                  <!-- Meses Programados -->
                  <th style="background-color: #2e7d32; color: #ffffff;">P. ENE</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. FEB</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. MAR</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. ABR</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. MAY</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. JUN</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. JUL</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. AGO</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. SEP</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. OCT</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. NOV</th>
                  <th style="background-color: #2e7d32; color: #ffffff;">P. DIC</th>
                  <th style="background-color: #1a237e; color: #ffffff;">MEDIO DE VERIFICACIÓN</th>
                  <!-- Meses Ejecutados -->
                  <th style="background-color: #0284c7; color: #000000;">E. ENE</th>
                  <th style="background-color: #0284c7; color: #000000;">E. FEB</th>
                  <th style="background-color: #0284c7; color: #000000;">E. MAR</th>
                  <th style="background-color: #0284c7; color: #000000;">E. ABR</th>
                  <th style="background-color: #0284c7; color: #000000;">E. MAY</th>
                  <th style="background-color: #0284c7; color: #000000;">E. JUN</th>
                  <th style="background-color: #0284c7; color: #000000;">E. JUL</th>
                  <th style="background-color: #0284c7; color: #000000;">E. AGO</th>
                  <th style="background-color: #0284c7; color: #000000;">E. SEP</th>
                  <th style="background-color: #0284c7; color: #000000;">E. OCT</th>
                  <th style="background-color: #0284c7; color: #000000;">E. NOV</th>
                  <th style="background-color: #0284c7; color: #000000;">E. DIC</th>
                </tr>
              </thead>
              <tbody style="font-family: Arial, sans-serif; font-size: 8.5px;">';

            foreach ($form4 as $row) {
                $priori = (intval($row['prod_priori']) === 1) ? 'SÍ' : 'NO';
                $priori_sigep = (intval($row['sigep_priori']) === 1) ? 'SÍ' : 'NO';

                $tabla .= '<tr>';
                    // Formato de texto estricto (@) para amarrar ceros a la izquierda (ej. "01")
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . strtoupper($row['dep_id']) . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;"></b>' . (!empty($row['dep_cod']) ? strtoupper($row['dep_cod']) : '0') . '</b></td>';
                    $tabla .= '<td><b>' . strtoupper(htmlspecialchars(!empty($row['dep_departamento']) ? $row['dep_departamento'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</b></td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;"></b>' . (!empty($row['dist_cod']) ? strtoupper($row['dist_cod']) : '0') . '</b></td>';
                    $tabla .= '<td><b>' . strtoupper(htmlspecialchars(!empty($row['dist_distrital']) ? $row['dist_distrital'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</b></td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . (!empty($row['da']) ? strtoupper($row['da']) : '0') . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . (!empty($row['ue']) ? strtoupper($row['ue']) : '0') . '</td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;font-weight: bold;">' . $row['aper_programa'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;font-weight: bold;">' . $row['aper_proyecto'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;font-weight: bold;">' . $row['aper_actividad'] . '</td>';

                    $tabla .= '<td>' . htmlspecialchars(!empty($row['tipo_gasto_nombre']) ? $row['tipo_gasto_nombre'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    // 🌟 REPARADO CORE 1: Se corrige la variable cortada $r por $row y se blinda el SISIN
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: left;">' . (!empty($row['proy_sisin']) ? strtoupper($row['proy_sisin']) : '0') . '</td>';

                    $tabla .= '<td>' . htmlspecialchars($row['tipo'] . ' ' . $row['proy_nombre'] . ' ' . $row['abrev'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td>' . htmlspecialchars($row['tipo_subactividad'] . ' ' . $row['com_componente'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td style="text-align: center; font-weight: bold;">' . intval($row['prod_id']) . '</td>';
                    
                    $tabla .= '<td style="text-align: center;font-weight: bold;">' . intval($row['og_codigo']) . '</td>';
                    $tabla .= '<td style="text-align: center;font-weight: bold;">' . intval($row['or_codigo']) . '</td>';
                    $tabla .= '<td ><b>' . htmlspecialchars(!empty($row['or_objetivo']) ? $row['or_objetivo'] : 'Sin Alineacion ..', ENT_QUOTES, 'UTF-8') . '</b></td>';
                    $tabla .= '<td style="text-align: center; font-weight: bold; color: blue;">' . intval($row['prod_cod']) . '</td>';
                    $tabla .= '<td style="text-align: center;font-weight: bold;">' . $priori . '</td>';
                    $tabla .= '<td style="text-align: center;color: green;font-weight: bold;"><b>' . $priori_sigep . '</b></td>';

                    // 🌟 COMPLETADO CORE: Columnas descriptivas blindadas contra caracteres especiales XML/Excel
                    $tabla .= '<td>' . htmlspecialchars(!empty($row['prod_producto']) ? $row['prod_producto'] : 'S/D', ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td>' . htmlspecialchars(!empty($row['prod_resultado']) ? $row['prod_resultado'] : 'S/D', ENT_QUOTES, 'UTF-8') . '</td>';

                    $tabla .= '<td>' . htmlspecialchars(!empty($row['indi_descripcion']) ? $row['indi_descripcion'] : 'S/I', ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td>' . htmlspecialchars(!empty($row['mt_tipo']) ? $row['mt_tipo'] : 'S/I', ENT_QUOTES, 'UTF-8') . '</td>';
                    

                    $tabla .= '<td>' . htmlspecialchars(!empty($row['prod_indicador']) ? $row['prod_indicador'] : 'S/D', ENT_QUOTES, 'UTF-8') . '</td>';

                    // Evaluación de tipo de estructura (Normal / Bolsa)
                    if (intval($row['por_id']) === 0) {
                        $tabla .= '<td>' . htmlspecialchars(!empty($row['prod_unidades']) ? $row['prod_unidades'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    } else {
                        $tabla .= '<td>' . htmlspecialchars(!empty($row['unidad_responsable']) ? $row['unidad_responsable'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    }

                    // Meta Anual formateada como número contable válido
                    $tabla .= '<td style="text-align: right; font-weight: bold;font-weight: bold;">' . round($row['prod_meta'], 2) . '</td>';

                    // 🌟 COMPLETADO MÁSTER BUCLE 1: Programación Física Mensual (P. ENE a P. DIC)
                    for ($i = 1; $i <= 12; $i++) {
                        $val_mes = isset($row['m' . $i]) ? $row['m' . $i] : 0;
                        $estilo_mes = ($val_mes > 0) ? 'background-color: #e8f5e9; font-weight: bold;' : '';
                        
                        $tabla .= '<td style="text-align: right; ' . $estilo_mes . '">' . number_format($val_mes, 2, '.', '') . '</td>';
                    }
                    $tabla .= '<td>' . htmlspecialchars($p_ver  = !empty($row['prod_fuente_verificacion']) ? $row['prod_fuente_verificacion'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    // Renderizado directo de la matriz de temporalidad
                    for ($i = 1; $i <= 12; $i++) {
                        $val_mes = isset($row['ejec_m' . $i]) ? $row['ejec_m' . $i] : 0;
                        $estilo_mes = ($val_mes > 0) ? 'background-color: #e3f6f8; font-weight: bold;' : '';
                        
                        $tabla .= '<td style="text-align: right; ' . $estilo_mes . '">' . number_format($val_mes, 2, '.', '') . '</td>';
                    }

                $tabla .= '</tr>';
            } // 🔒 FIN DEL FOREACH GENERAL DE REGIONALES

            $tabla .= '
            </tbody>
            </table>';

      return $tabla;
    }


    /*------ EXPORTAR ACTIVIDADES Consolidado Institucional (Reporte Gerencial) -------*/
    public function exportar_formularioN5_Institucional($tp_id) {
        // 1. Configuración y ampliación drástica de recursos del servidor
        @set_time_limit(1200);             // 20 minutos de ejecución interna elástica
        ini_set('memory_limit', '2048M'); // 2 GB de memoria RAM asignada para alta densidad

        // 2. Extracción de datos nacionales parametrizados por la función SQL corregida
        $form5 = $this->model_insumo->list_requerimientos_Institucional($tp_id); 
        $titulo = ($tp_id == 4) ? 'Gcorriente' : 'Inversion';

        if (!empty($form5)) {
            // Estructuración formal del nombre del archivo según el estándar del PEI
            $nombre_archivo = "Consolidado_Formulario_N5_".$titulo."_" . $this->gestion . "_Institucional.xls";

            // 3. Limpieza radical de buffers fantasmas para blindar el binario
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();

            // 4. Protocolo de cabeceras HTTP rígidas para forzar descarga directa
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Expires: 0");

            // 5. Inyección de la directiva BOM UTF-8 para proteger acentos y Ñs en Windows
            echo "\xEF\xBB\xBF";

            // 6. Construcción idéntica de la sábana de datos en una sola variable string
            $tabla = '';
            $tabla .= $this->stream_lista_excel_requerimientos($form5,$tp_id); /// lista de requerimientos Insitucional segun el tipo de gasto
            

            // 7. Despacho directo del buffer de datos purificado hacia la red institucional
            echo $tabla;
            ob_end_flush();
            exit; // Detiene la ejecución física impidiendo layouts o filtraciones del framework
        } else {
            echo "<center><br><h3>🚨 Error de Consistencia: No se registran datos disponibles del Formulario N° 4 Nacional para la gestión activa " . $this->gestion . ".</h3></center>";
        }
    }


    /*------ EXPORTAR ACTIVIDADES Consolidado Regional/Distrital (Reporte Gerencial) -------*/
    public function exportar_formularioN5_regional($dep_id,$dist_id,$tp_id) {
        // 1. Configuración y ampliación drástica de recursos del servidor
        @set_time_limit(1200);             // 20 minutos de ejecución interna elástica
        ini_set('memory_limit', '2048M'); // 2 GB de memoria RAM asignada para alta densidad

        if($dist_id!=0){ /// distrital
          $dist=$this->model_proyecto->dep_dist($dist_id);
          $form5 = $this->model_insumo->list_requerimientos_distrital($dist_id,$tp_id);
          $tit_reg=$dist[0]['dist_distrital']; 
        }
        else{ /// regional
          $dep=$this->model_proyecto->get_departamento($dep_id);
          $form5 = $this->model_insumo->list_requerimientos_regional($dep_id,$tp_id); 
          $tit_reg='Regional '.$dep[0]['dep_departamento']; 
        }

        // 2. Extracción de datos nacionales parametrizados por la función SQL corregida
        
        $titulo = ($tp_id == 4) ? 'Gcorriente' : 'Inversion';

        if (!empty($form5)) {
            // Estructuración formal del nombre del archivo según el estándar del PEI
            $nombre_archivo = "Consolidado_Formulario_N5_".$tit_reg."_".$titulo."_" . $this->gestion . "_".date("d-m-Y H:i:s")."_Institucional.xls";

            // 3. Limpieza radical de buffers fantasmas para blindar el binario
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            ob_start();

            // 4. Protocolo de cabeceras HTTP rígidas para forzar descarga directa
            header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
            header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
            header("Cache-Control: max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
            header("Pragma: public");
            header("Expires: 0");

            // 5. Inyección de la directiva BOM UTF-8 para proteger acentos y Ñs en Windows
            echo "\xEF\xBB\xBF";

            // 6. Construcción idéntica de la sábana de datos en una sola variable string
            $tabla = '';
            $tabla .= $this->stream_lista_excel_requerimientos($form5,$tp_id); /// lista de requerimientos Insitucional segun el tipo de gasto
            

            // 7. Despacho directo del buffer de datos purificado hacia la red institucional
            echo $tabla;
            ob_end_flush();
            exit; // Detiene la ejecución física impidiendo layouts o filtraciones del framework
        } else {
            echo "<center><br><h3>🚨 Error de Consistencia: No se registran datos disponibles del Formulario N° 4 Nacional para la gestión activa " . $this->gestion . ".</h3></center>";
        }
    }


    //// matriz Requerimientos segun el tipo de Gasto
    private function stream_lista_excel_requerimientos($form5,$tp_id) {
      $titulo_tpgasto='GASTO CORRIENTE';
      $titulo_tpcomponente='UNIDAD OPERATIVA';
      if($tp_id==1){
        $titulo_tpgasto='PROYECTO DE INVERSIÓN';
        $titulo_tpcomponente='COMPONENTE';
      }

      $tabla='';
      $tabla .= '
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <table border="0.5" cellpadding="4" cellspacing="0">
            <thead>
              <tr style="background-color: #1a237e; color: #ffffff; font-weight: bold; height: 35px; font-family: Arial, sans-serif; font-size: 9px; text-transform: uppercase;">
                  <th style="background-color: #1a237e; color: #ffffff;">DEP. ID.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. REG.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">REGIONAL</th>

                  <th style="background-color: #1a237e; color: #ffffff;">DIST. ID.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. DIST.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">DISTRITAL</th>

                  <th style="background-color: #1a237e; color: #ffffff;">COD. DA.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. UE.</th>

                  <th style="background-color: #1a237e; color: #ffffff;">APER. PROG.-'.$this->gestion.'</th>
                  <th style="background-color: #1a237e; color: #ffffff;">APER. PROY.-'.$this->gestion.'</th>
                  <th style="background-color: #1a237e; color: #ffffff;">APER. ACT.-'.$this->gestion.'</th>

                  <th style="background-color: #1a237e; color: #ffffff;">TIPO GASTO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">CÓDIGO SISIN</th>

                  <th style="background-color: #1a237e; color: #ffffff;">'.$titulo_tpgasto.'</th>
                  <th style="background-color: #1a237e; color: #ffffff;">'.$titulo_tpcomponente.'</th>
                  
                  <th style="background-color: #1a237e; color: #ffffff;">COD. ACP.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">COD. OPE.</th>

                  <th style="background-color: #1a237e; color: #ffffff;">DESCRIPCIÓN OPERACIÓN '.$this->gestion.'</th>

                  <th style="background-color: #1a237e; color: #ffffff;">COD. ACT. POA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRIORIDAD POA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRIORIDAD SIGEP</th>
                  <th style="background-color: #1a237e; color: #ffffff;">DESCRIPCIÓN DE LA ACTIVIDAD</th>
                  <th style="background-color: #1a237e; color: #ffffff;">UNIDAD RESPONSABLE</th>
                  <th style="background-color: #1a237e; color: #ffffff;">META ANUAL</th>
                  
                  <th style="background-color: #1a237e; color: #ffffff;">PARTIDA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">DETALLE REQUERIMIENTO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">UNIDAD MEDIDA</th>
                  <th style="background-color: #1a237e; color: #ffffff;">CANTIDAD</th>
                  <th style="background-color: #1a237e; color: #ffffff;">PRECIO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TOTAL</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TOTAL CERT.</th>


                  <!-- Meses Programados -->
                  <th style="background-color: #1a237e; color: #ffffff;">P. ENE.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. FEB.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. MAR.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. ABR.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. MAY.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. JUN.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. JUL.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. AGO.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. SEP.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. OCT.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. NOV.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">P. DIC.</th>
                  <th style="background-color: #1a237e; color: #ffffff;">OBSERVACIÓN</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TP. REGISTRO</th>
                  <th style="background-color: #1a237e; color: #ffffff;">TP. POA</th>
                </tr>
              </thead>
              <tbody style="font-family: Arial, sans-serif; font-size: 8.5px;">';

            foreach ($form5 as $row) {
                $priori = (intval($row['prod_priori']) === 1) ? 'SÍ' : 'NO';
                $priori_sigep = (intval($row['sigep_priori']) === 1) ? 'SÍ' : 'NO';
                $color_tp='';
                if($row['ins_tipo_modificacion']==1){
                  $color_tp='#D62F43';
                }

                $tabla .= '<tr style="vertical-align: middle;">';
                    // Formato de texto estricto (@) para amarrar ceros a la izquierda (ej. "01")
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . strtoupper($row['dep_id']) . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;"></b>' . (!empty($row['dep_cod']) ? strtoupper($row['dep_cod']) : '0') . '</b></td>';
                    $tabla .= '<td><b>' . strtoupper(htmlspecialchars(!empty($row['dep_departamento']) ? $row['dep_departamento'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</b></td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . strtoupper($row['dist_id']) . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;"></b>' . (!empty($row['dist_cod']) ? strtoupper($row['dist_cod']) : '0') . '</b></td>';
                    $tabla .= '<td><b>' . strtoupper(htmlspecialchars(!empty($row['dist_distrital']) ? $row['dist_distrital'] : 'S/R', ENT_QUOTES, 'UTF-8')) . '</b></td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . (!empty($row['da']) ? strtoupper($row['da']) : '0') . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . (!empty($row['ue']) ? strtoupper($row['ue']) : '0') . '</td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . $row['aper_programa'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . $row['aper_proyecto'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . $row['aper_actividad'] . '</td>';

                    $tabla .= '<td >' . htmlspecialchars(!empty($row['tipo_gasto_nombre']) ? $row['tipo_gasto_nombre'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    // 🌟 REPARADO CORE 1: Se corrige la variable cortada $r por $row y se blinda el SISIN
                    $tabla .= '<td style="text-align: center; font-weight: bold;">' . (!empty($row['proy_sisin']) ? strtoupper($row['proy_sisin']) : '0') . '</td>';

                    $tabla .= '<td >' . htmlspecialchars($row['tipo'] . ' ' . $row['proy_nombre'] . ' ' . $row['abrev'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td >' . htmlspecialchars($row['tipo_subactividad'] . ' ' . $row['com_componente'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td style="text-align: center; font-weight: bold;">' . intval($row['og_codigo']) . '</td>';
                    $tabla .= '<td style="text-align: center; font-weight: bold;">' . intval($row['or_codigo']) . '</td>';
                    $tabla .= '<td ><b>' . htmlspecialchars(!empty($row['or_objetivo']) ? $row['or_objetivo'] : 'Sin Alineacion ..', ENT_QUOTES, 'UTF-8') . '</b></td>';
                    $tabla .= '<td style="text-align: center; font-weight: bold; color: blue;">' . intval($row['prod_cod']) . '</td>';
                    $tabla .= '<td style="text-align: center;font-weight: bold;">' . $priori . '</td>';
                    $tabla .= '<td style="text-align: center;color: green;font-weight: bold;"><b>' . $priori_sigep . '</b></td>';

                    // 🌟 COMPLETADO CORE: Columnas descriptivas blindadas contra caracteres especiales XML/Excel
                    $tabla .= '<td style="font-weight: bold;">' . htmlspecialchars(!empty($row['prod_producto']) ? $row['prod_producto'] : 'S/D', ENT_QUOTES, 'UTF-8') . '</td>';
                    // Evaluación de tipo de estructura (Normal / Bolsa)
                    if (intval($row['por_id']) === 0) {
                        $tabla .= '<td>' . htmlspecialchars(!empty($row['prod_unidades']) ? $row['prod_unidades'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    } else {
                        $tabla .= '<td >' . htmlspecialchars(!empty($row['unidad_responsable']) ? $row['unidad_responsable'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    }

                    // Meta Anual formateada como número contable válido
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . round($row['prod_meta'],2) . '</td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;font-weight: bold;">' . $row['par_codigo'] . '</td>';
                    
                    // 🌟 REPARADO: Protección perimetral htmlspecialchars contra caracteres comerciales rotos
                    $tabla .= '<td>' . htmlspecialchars($row['ins_detalle'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td >' . htmlspecialchars($row['ins_unidad_medida'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold;">' . floatval($row['ins_cant_requerida']) . '</td>';
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold;">' . floatval($row['ins_costo_unitario']) . '</td>';
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold; color: blue;">' . floatval($row['ins_costo_total']) . '</td>';
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold; background-color: #EBFAE8;">' . floatval($row['ins_monto_certificado']) . '</td>';
                    //$tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold;">' . floatval($row['programado_total']) . '</td>';

                    // 🌟 COMPLETADO MÁSTER BUCLE 1: Programación Física Mensual (P. ENE a P. DIC)
                    for ($i = 1; $i <= 12; $i++) {
                        $val_mes = isset($row['mes' . $i]) ? $row['mes' . $i] : 0;
                        //$estilo_mes = ($val_mes > 0) ? 'background-color: #E0EAFF; font-weight: bold;' : '';
                        $estilo_mes='background-color: #F0FAFF; font-weight: bold;'; 
                        if($val_mes > 0){
                          $estilo_mes='background-color: #E0EAFF; font-weight: bold;';
                        }
                        
                        $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\';text-align: right; ' . $estilo_mes . 'font-weight: bold;">' . number_format($val_mes, 2, '.', '') . '</td>';
                    }
                    $tabla .= '<td>' . htmlspecialchars($p_ver  = !empty($row['ins_observacion']) ? $row['ins_observacion'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: right; ">' . htmlspecialchars($row['tipo_registro_poa'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: right; color: '.$color_tp.';font-weight: bold;">' . htmlspecialchars($row['tipo_insumo_poa'], ENT_QUOTES, 'UTF-8') . '</td>';
                $tabla .= '</tr>';
            } // 🔒 FIN DEL FOREACH GENERAL DE REGIONALES

            $tabla .= '
            </tbody>
            </table>';

      return $tabla;
    }



    

    ////= Exportar Formulario N° 4 por Unidad Organizacional 2027
    function exportar_poa_unidad_organizacional($proy_id, $token = NULL) {
    // En tu función principal
    $data['form4'] = $this->exportar_form4_uOrganizacional($proy_id);
    // Pestaña 2 también debe ser una tabla para que se vea bien
    $data['form5'] = $this->exportar_form5_uresponsable($com_id);

    // 3. Manejo del Token para el Loading de JS
    if($token != NULL) {
        // Importante: El path "/" asegura que la cookie sea visible en todo el sitio
        header("Set-Cookie: downloadToken=$token; path=/; SameSite=Lax");
    }

    // 4. Cabeceras para Excel (Formato XML Spreadsheet 2003)
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Reporte_POA_UResponsable.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // 5. Cargar la vista especial XML que definimos antes
    $this->load->view('admin/reportes_cns/exportar_requerimientos/exportar_poa_uresponsable',$data); 

    }


    ////= (Archivo) Exportar Formulario N° 4 por Unidad Organizacional 2027
    public function exportar_form4_uOrganizacional($proy_id){
    $tabla='';
    $formularioN4 = $this->model_producto->get_lista_form4_uOrganizacional_consolidado($proy_id); /// poa normal + Bolsa por Unidad Organizacional

    $tabla.='
          <Row ss:Height="30">
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD ORGANIZACIONAL</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD RESPONSABLE</Data></Cell>

          <Cell ss:StyleID="header"><Data ss:Type="String">PROG.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACP.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. OPE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACT.</Data></Cell> 
          <Cell ss:StyleID="header"><Data ss:Type="String">ACTIVIDAD</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">RESULTADO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD RESPONSABLE</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">INDICADOR</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">META</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ENE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">FEB.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ABR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAY.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUN.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUL.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">AGO.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">SEPT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OCT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">NOV.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DIC.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">VERIFICACIÓN</Data></Cell>
      </Row>';

    $nro=0;
    foreach($formularioN4 as $rowp){
        $tabla .= '<Row ss:AutoFitHeight="1">'; // Autoajusta la altura si el texto es largo
        
        // Celdas de Códigos (Centradas)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['aper_descripcion'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['unidad_tipo_subactividad'].' '.$rowp['com_componente'].'</Data></Cell>';

        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['aper_programa'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['og_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['or_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['prod_cod'].'</Data></Cell>';
        
        // Celdas de Texto Largo (Alineadas a la izquierda con ajuste de texto)
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_producto'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_resultado'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_unidades'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_indicador'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        // Meta (Centrada)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['prod_meta'],2).'</Data></Cell>';
        
        // Meses (Estrechos y Centrados)
        for ($i=1; $i <=12 ; $i++) { 
            $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['m'.$i],2).'</Data></Cell>';
        }

        // Fuente de Verificación
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_fuente_verificacion'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '</Row>';
    }

    return $tabla;
  }




















    ////= Exportar Formulario N° 4 por Unidad Responsable 2027
    function exportar_poa_uresponsable($com_id, $token = NULL) {
    // En tu función principal
    $data['form4'] = $this->exportar_form4_uresponsable($com_id);
    // Pestaña 2 también debe ser una tabla para que se vea bien
    $data['form5'] = $this->exportar_form5_uresponsable($com_id);

    // 3. Manejo del Token para el Loading de JS
    if($token != NULL) {
        // Importante: El path "/" asegura que la cookie sea visible en todo el sitio
        header("Set-Cookie: downloadToken=$token; path=/; SameSite=Lax");
    }

    // 4. Cabeceras para Excel (Formato XML Spreadsheet 2003)
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=Reporte_POA_UResponsable.xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    
    // 5. Cargar la vista especial XML que definimos antes
    $this->load->view('admin/reportes_cns/exportar_requerimientos/exportar_poa_uresponsable',$data); 

    }

  ////= (Archivo) Exportar Formulario N° 4 por Unidad Responsable 2027
  public function exportar_form4_uresponsable($com_id){
    $tabla='';
    $formularioN4 = $this->model_producto->get_lista_form4_uresp_consolidado($com_id); /// poa normal + Bolsa

    $tabla.='
          <Row ss:Height="30">

          <Cell ss:StyleID="header"><Data ss:Type="String">PROG.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACP.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. OPE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACT.</Data></Cell> 
          <Cell ss:StyleID="header"><Data ss:Type="String">ACTIVIDAD</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">RESULTADO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD RESPONSABLE</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">INDICADOR</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">META</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ENE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">FEB.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ABR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAY.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUN.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUL.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">AGO.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">SEPT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OCT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">NOV.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DIC.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">VERIFICACIÓN</Data></Cell>
      </Row>';

    $nro=0;
    foreach($formularioN4 as $rowp){
        $tabla .= '<Row ss:AutoFitHeight="1">'; // Autoajusta la altura si el texto es largo
        
        // Celdas de Códigos (Centradas)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['aper_programa'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['og_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['or_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['prod_cod'].'</Data></Cell>';
        
        // Celdas de Texto Largo (Alineadas a la izquierda con ajuste de texto)
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_producto'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_resultado'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_unidades'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_indicador'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        // Meta (Centrada)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['prod_meta'],2).'</Data></Cell>';
        
        // Meses (Estrechos y Centrados)
        for ($i=1; $i <=12 ; $i++) { 
            $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['m'.$i],2).'</Data></Cell>';
        }

        // Fuente de Verificación
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['prod_fuente_verificacion'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '</Row>';
    }

    return $tabla;
  }


  ////= (Archivo) Exportar Formulario N° 5 por Unidad Responsable 2026
  public function exportar_form5_uresponsable($com_id){
    $tabla='';

    $formularioN5=$this->model_insumo->list_requerimientos_uresponsable($com_id); /// list form 5
    $formularioN5_bolsa=$this->model_insumo->lista_total_requerimientos_inscritos_en_programas_bolsas_uresponsable($com_id); /// lista form 5 de las bolsas

    $tabla.='
          <Row ss:Height="30">
          <Cell ss:StyleID="header"><Data ss:Type="String">PROG.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COD. ACT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">PARTIDA.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DETALLE REQUERIMIENTO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">UNIDAD MEDIDA</Data></Cell> 
          <Cell ss:StyleID="header"><Data ss:Type="String">CANTIDAD</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">PRECIO UNITARIO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">COSTO TOTAL</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">TOTAL CERTIFICADO</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ENE.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">FEB.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">ABR.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">MAY.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUN.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">JUL.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">AGO.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">SEPT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OCT.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">NOV.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">DIC.</Data></Cell>
          <Cell ss:StyleID="header"><Data ss:Type="String">OBSERVACIÓN</Data></Cell>
      </Row>';

    $nro=0;
    foreach($formularioN5 as $rowp){
        $tabla .= '<Row ss:AutoFitHeight="1">'; // Autoajusta la altura si el texto es largo
        
        // Celdas de Códigos (Centradas)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['aper_programa'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['prod_cod'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['par_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_detalle'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_unidad_medida'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_cant_requerida'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_costo_unitario'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_costo_total'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_monto_certificado'].'</Data></Cell>';
        
        // Meses (Estrechos y Centrados)
        for ($i=1; $i <=12 ; $i++) { 
            $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['mes_'.$i],2).'</Data></Cell>';
        }

        // Observacion
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_observacion'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '</Row>';
    }

    foreach($formularioN5_bolsa as $rowp){
        $tabla .= '<Row ss:AutoFitHeight="1">'; // Autoajusta la altura si el texto es largo
        
        // Celdas de Códigos (Centradas)
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="String">'.$rowp['aper_programa'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['prod_cod'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['par_codigo'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_detalle'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_unidad_medida'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_cant_requerida'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_costo_unitario'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_costo_total'].'</Data></Cell>';
        $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.$rowp['ins_monto_certificado'].'</Data></Cell>';
        
        // Meses (Estrechos y Centrados)
        for ($i=1; $i <=12 ; $i++) { 
            $tabla .= '<Cell ss:StyleID="cuerpoCentro"><Data ss:Type="Number">'.round($rowp['mes_'.$i],2).'</Data></Cell>';
        }

        // Observacion
        $tabla .= '<Cell ss:StyleID="cuerpoTexto"><Data ss:Type="String">'.htmlspecialchars($rowp['ins_observacion'], ENT_XML1, 'UTF-8').'</Data></Cell>';
        
        $tabla .= '</Row>';
    }

    return $tabla;
  }


  /*-------- GET LISTA DE DISTRITALES --------*/
  public function get_distritales(){
    if($this->input->is_ajax_request() && $this->input->post()){
      $post = $this->input->post();
      $dep_id = $this->security->xss_clean($post['dep_id']);
      $regional=$this->model_proyecto->get_departamento($dep_id);

      $tabla=$this->list_distritales($dep_id);
      $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$tabla,
          'caratula'=>$regional,
        );
        
      echo json_encode($result);
    }else{
        show_404();
    }
  }

}