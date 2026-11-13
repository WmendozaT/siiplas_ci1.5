<?php
class Cevaluacion_form2 extends CI_Controller {
  public $rol = array('1' => '3','2' => '4');
  public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_faseetapa');
      
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('ejecucion/model_ejecucion');
        $this->load->model('mestrategico/model_mestrategico');
        $this->load->model('mestrategico/model_objetivogestion');
        $this->load->model('mestrategico/model_objetivoregion');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('programacion/insumos/model_insumo');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dep_id = $this->session->userData('dep_id');
        $this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->fun_id = $this->session->userData('fun_id');
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->conf_estado = $this->session->userData('conf_estado'); /// conf estado Gestion (1: activo, 0: no activo)
        $this->fecha_plazo_actualizacion = strtotime(date('2022-04-30'));
        $this->load->library('eval_oregional');

        }else{
          $this->session->sess_destroy();
          redirect('/','refresh');
        }
    }

    /*-- Menu Regional 2027 FORMULARIO N° 2 --*/
    public function menu_regional(){
      $data['menu']=$this->eval_oregional->menu(4); //// genera menu
      $data['titulo']=$this->eval_oregional->titulo();
      if($this->tp_adm==1){
        $data['tabla']=$this->eval_oregional->regionales();
      }
      else{
        $data['tabla']=$this->eval_oregional->ver_relacion_ogestion($this->dep_id);
      }

      $data['modal_grafico_x_form2']=$this->eval_oregional->modal_graficos_x_form2();
      $data['modal_grafico_form2_regional']=$this->eval_oregional->modal_grafico_cumplimiento_form2_x_regional();
      
      $this->load->view('admin/evaluacion/evaluacion_form2/menu_regionales', $data);
    }


    /*---- UPDATE DATOS OPERACIONES TIPO DE META----*/
    // public function update_datos_tpmeta(){
    //   if($this->input->is_ajax_request() && $this->input->post()){
    //     $post = $this->input->post();
    //     $or_id = $this->security->xss_clean($post['or_id']);
    //     $id = $this->security->xss_clean($post['id']);
    //     $detalle_oregional=$this->model_objetivoregion->get_objetivosregional($or_id); /// Objetivo Regional

    //     if($detalle_oregional[0]['indi_id']==2){
    //       $update_or = array(
    //         'tp_meta' => $id,
    //       );
    //       $this->db->where('or_id', $or_id);
    //       $this->db->update('objetivos_regionales', $update_or);
    //     }
          
    //     $result = array(
    //       'respuesta' => 'correcto',
    //     );

    //     echo json_encode($result);
    //   }else{
    //     show_404();
    //   }
    // }


    /*---- FUNCION GET LISTA DE OPERACIONES POR REGIONAL 2027 --------*/
    public function get_lista_operaciones_x_regionales() {
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $dep_id = intval($this->input->post('dep_id')); // Conversión segura a entero

          if ($dep_id > 0) {
              $result = array(
                  'respuesta' => 'correcto',
                  'tabla'     => $this->eval_oregional->ver_relacion_ogestion($dep_id)
              );
          } else {
              $result = array(
                  'respuesta' => 'error',
                  'tabla'     => ''
              );
          }

          echo json_encode($result);
      } else {
          show_404();
      }
    }

  /*---- FUNCION GET LISTA DE ACTIVIDADES PRIORIZADOS 2027--------*/
  public function ver_actividades_priorizados(){
    if($this->input->is_ajax_request() && $this->input->post()){
      $post = $this->input->post();
      $or_id = $this->security->xss_clean($post['or_id']); /// or id
      $dep_id = $this->security->xss_clean($post['dep_id']); /// Regional
      $detalle_oregional=$this->model_objetivoregion->get_objetivosregional($or_id); /// Objetivo Regional
      $regional=$this->model_proyecto->get_departamento($dep_id);
      $meta='';
      if($detalle_oregional[0]['indi_id']==2){
        $meta='%';
      }

      $titulo='
      <b style="font-family:Verdana;font-size: 16px;">
        OBJ. REGIONAL ('.strtoupper($regional[0]['dep_departamento']).'): '.$detalle_oregional[0]['or_codigo'].' '.$detalle_oregional[0]['or_objetivo'].'<br>
        META '.$this->gestion.' : '.round($detalle_oregional[0]['or_meta'],2).' '.$meta.'
      </b>';

      $boton_imprimir='
        <hr>
          <div align=right>
            <a href="javascript:abreVentana(\''.site_url("").'/rep_list_form4_priori_oregional/'.$or_id.'\');" title="IMPRIMIR ACP DISTRIBUCION REGIONAL" class="btn btn-default">
              <img src="'.base_url().'assets/Iconos/printer_empty.png" WIDTH="20" HEIGHT="20"/>&nbsp;&nbsp;IMPRIMIR ACTIVIDADES PRIORITARIOS
            </a>
          </div>
        </hr>';


        $listado=$this->eval_oregional->get_mis_form4_priorizados_x_oregional($detalle_oregional); /// lista de actividades 
        if($detalle_oregional[0]['or_tp']==1){
          $listado=$this->eval_oregional->get_mis_Inversion_priorizados_x_oregional($detalle_oregional[0]['or_id']); //// lista inversion
        }

        $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$listado,
          'titulo'=>$titulo,
          'imprimir_act_priori'=>$boton_imprimir,
        );

      echo json_encode($result);
    }else{
        show_404();
    }
  }


  //// Reporte Formulario N° 2 2027
  public function reporte_form2($dep_id){
    // 1. Ampliación y control de recursos de hardware en el servidor
      ini_set('memory_limit', '2048M'); 
      set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
      
      // Limpieza preliminar del búfer de salida para proteger el binario del PDF
      if (ob_get_length()) ob_clean();
          $regional=$this->model_proyecto->get_departamento($dep_id);
          if(count($regional)!=0){
            $pie_report='REPORTE_FORM2_'.$regional[0]['dep_departamento'].'_'.$this->gestion;
            $listado=$this->eval_oregional->lista_form2_reporte_x_regional($dep_id);
            $cabecera=$this->eval_oregional->cabecera_form2($regional,0);
            $pie=$this->eval_oregional->pie_form2($regional);
            
            $data['lista'] = '
              <page orientation="landscape" backtop="50mm" backbottom="26mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$cabecera.'
                </page_header>
                
                <!-- Pie de Página Fijo en la Base de la Hoja -->
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$pie.'
                    </div>
                </page_footer>
                    '.$listado.'
              </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4_consolidado', $data, true); 
          // 2. Limpieza radical del búfer de CodeIgniter para que Chrome no rechace el PDF
          if (ob_get_length()) ob_clean();
          // 3. Importación segura del motor conversor usando la ruta física del servidor
          require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
          try {
              // Inicializamos en orientación horizontal ('L' de Landscape / Paysage) para que coincida con tu diseño
              $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
              $html2pdf->pdf->SetDisplayMode('fullpage');
              $html2pdf->writeHTML($html_reporte);
              
              // 4. Enviamos el flujo binario limpio directo al visor de Chrome
              $html2pdf->Output($pie_report. '.pdf', 'I');
          }
          catch(HTML2PDF_exception $e) {
              echo "Error al compilar el reporte: " . $e;
          }
          exit;
        }
        else{
          echo "Error !!!";
      }
  }

    ///// Reporte Evaluacion Formulario N2
    public function reporte_evaluacion_form2($dep_id){
      // 1. Ampliación y control de recursos de hardware en el servidor
      ini_set('memory_limit', '2048M'); 
      set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
      
      // Limpieza preliminar del búfer de salida para proteger el binario del PDF
      if (ob_get_length()) ob_clean();
          $regional=$this->model_proyecto->get_departamento($dep_id);
          if(count($regional)!=0){
            $pie_report='EVALUACION_FORM2_'.$regional[0]['dep_departamento'].'_'.$this->gestion;
            $listado=$this->eval_oregional->lista_form2_evaluacion_reporte_x_regional($dep_id);
            $cabecera=$this->eval_oregional->cabecera_form2($regional,1);
            $pie=$this->eval_oregional->pie_form2($regional);
            
            $data['lista'] = '
              <page orientation="landscape" backtop="50mm" backbottom="26mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$cabecera.'
                </page_header>
                
                <!-- Pie de Página Fijo en la Base de la Hoja -->
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$pie.'
                    </div>
                </page_footer>
                    '.$listado.'
              </page>';

          // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4_consolidado', $data, true); 
          // 2. Limpieza radical del búfer de CodeIgniter para que Chrome no rechace el PDF
          if (ob_get_length()) ob_clean();
          // 3. Importación segura del motor conversor usando la ruta física del servidor
          require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
          try {
              // Inicializamos en orientación horizontal ('L' de Landscape / Paysage) para que coincida con tu diseño
              $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
              $html2pdf->pdf->SetDisplayMode('fullpage');
              $html2pdf->writeHTML($html_reporte);
              
              // 4. Enviamos el flujo binario limpio directo al visor de Chrome
              $html2pdf->Output($pie_report. '.pdf', 'I');
          }
          catch(HTML2PDF_exception $e) {
              echo "Error al compilar el reporte: " . $e;
          }
          exit;
      }
      else{
        echo "Error !!!";
      }
    }


    /*---- GRAFICO CONSOLIDADO de OPERACIONES (barras) POR REGIONAL 2027 --------*/
    // public function get_cumplimiento_operaciones_grafico(){
    //   if($this->input->is_ajax_request() && $this->input->post()){
    //     $post = $this->input->post();
    //     $dep_id = $this->security->xss_clean($post['dep_id']); /// Regional
    //     $trm_id = $this->security->xss_clean($post['trm_id']); /// Trimestre
    //     $regional=$this->model_proyecto->get_departamento($dep_id);
    //     $tabla='<iframe id="ipdf" width="100%" height="950px;" src="'.base_url().'index.php/rep_meta_oregional_grafico/'.$dep_id.'"></iframe>';

    //       $result = array(
    //         'respuesta' => 'correcto',
    //         'titulo_graf'=> '<b>REGIONAL : '.strtoupper($regional[0]['dep_departamento']).'</b>',
    //         'tabla' => $tabla,
    //       );

    //     echo json_encode($result);
    //   }else{
    //       show_404();
    //   }
    // }

    public function get_grafico_cumplimiento_regional() {
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $dep_id = intval($this->input->post('dep_id'));
          $tmes   = intval($this->input->post('tmes'));

          // Asegurar que el índice trimestral esté entre 1 y 4
          $trm_indice = ($tmes >= 1 && $tmes <= 4) ? $tmes : 1;

          // 1. Obtener datos de la regional
          $departamento = $this->model_proyecto->get_departamento($dep_id);
          $nombre_regional = !empty($departamento) ? strtoupper($departamento[0]['dep_departamento']) : 'REGIONAL';

          // 2. Llamar a tu consulta SQL optimizada
          // Cambia $this->model_evaluacionpoa por el nombre exacto con el que cargas ese modelo
          $operaciones = $this->model_objetivoregion->list_form2_regional_x_regional($dep_id);

          $categorias = array();
          $valores    = array();
          $suma_porcentajes  = 0;
          $total_operaciones = 0;
          $tabla='';
          $tabla.='
                <table class="table table-bordered" border=0.2 style="width:100%;" id="datos">
                  <thead>
                  <tr style="font-size: 11px;">
                    <th style="width:1%;height:10px;color:#FFF; text-align: center" bgcolor="#1c7368">#</th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. ACP.</b></th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. OPE.</b></th>
                    <th style="width:11%;color:#FFF; text-align: center" bgcolor="#1c7368">OPERACI&Oacute;N</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. AL TRIMESTRE</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. A LA GESTIÓN</th>
                  </tr>
                  </thead>
                  <tbody>';
                  $nro=0;
                    foreach ($operaciones as $row) {
                        // Etiqueta de la operación para el eje vertical
                        $cod_ope = 'OPE ' . $row['og_codigo'] . '.' . $row['or_codigo'];
                        
                        // Capturar el porcentaje acumulado según la columna de tu consulta (trm_porc_ejec1 ... 4)
                        $campo_porc = 'trm_porc_ejec' . $trm_indice;
                        $porcentaje = isset($row[$campo_porc]) ? round(floatval($row[$campo_porc]), 2) : 0;

                        // Tope opcional de 100% si alguna operación supera su meta
                        if ($porcentaje > 100) {
                            $porcentaje = 100;
                        }

                        $categorias[] = $cod_ope;
                        $valores[]    = $porcentaje;

                        $suma_porcentajes += $porcentaje;
                        $total_operaciones++;
                      $nro++;
                      $tabla.='
                      <tr>
                        <td>'.$nro.'</td>
                        <td><b>'.$row['og_codigo'].'</b></td>
                        <td><b>'.$row['or_codigo'].'</b></td>
                        <td>'.$row['or_objetivo'].'</td>
                        <td>'.round($row['porc_cump_'.$this->tmes],2).' %</b></td>
                        <td>'.round($row['trm_porc_ejec'.$this->tmes],2).' %</b></td>
                      </tr>';
                    }
                  $tabla.='
                  </tbody>
                </table>';

          // 3. Calcular porcentaje global de la regional
          $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

          // 4. Determinar calificación y color del banner superior
          if ($promedio_global <= 50) {
              $calificacion = 'INSATISFACTORIO';
              $color_banner = '#ef4444'; // Rojo
          } elseif ($promedio_global <= 75) {
              $calificacion = 'REGULAR';
              $color_banner = '#f59e0b'; // Naranja / Amarillo
          } elseif ($promedio_global <= 90) {
              $calificacion = 'BUENO';
              $color_banner = '#0284c7'; // Azul
          } else {
              $calificacion = 'ÓPTIMO';
              $color_banner = '#16a34a'; // Verde
          }

          $respuesta = array(
              'status'          => 'success',
              'regional'        => $nombre_regional,
              'promedio_global' => $promedio_global,
              'calificacion'    => $calificacion,
              'color_banner'    => $color_banner,
              'categorias'      => $categorias,
              'valores'         => $valores,
              'lista'         => $tabla,
              'gestion'         => $this->gestion
          );

          echo json_encode($respuesta);
      } else {
          show_404();
      }
  }





  //   public function get_grafico_cumplimiento_regional2() {
  //     if ($this->input->is_ajax_request() && $this->input->post()) {
  //         $dep_id = intval($this->input->post('dep_id'));
  //         $tmes   = intval($this->input->post('tmes'));

  //         // 1. Obtener datos de la regional
  //         $departamento = $this->model_proyecto->get_departamento($dep_id);
  //         $nombre_regional = !empty($departamento) ? strtoupper($departamento[0]['dep_departamento']) : 'REGIONAL';

  //         // 2. Obtener lista de operaciones de la regional (Usa el método de tu modelo que trae el Form 2)
  //         $operaciones = $this->eval_oregional->list_operaciones_regional($dep_id); // Ajusta al nombre exacto de tu método de consulta

  //         $categorias = array();
  //         $valores    = array();
  //         $suma_porcentajes = 0;
  //         $total_operaciones = 0;

  //         foreach ($operaciones as $row) {
  //             // Filtrar solo operaciones priorizadas o activas si corresponde
  //             $cod_ope = 'OPE ' . $row['og_codigo'] . '.' . $row['or_codigo'];
              
  //             // Capturar el porcentaje acumulado a la gestión según el mes ($tmes)
  //             $campo_porc = 'trm_porc_ejec' . $tmes;
  //             $porcentaje = isset($row[$campo_porc]) ? round(floatval($row[$campo_porc]), 2) : 0;

  //             $categorias[] = $cod_ope;
  //             $valores[]    = $porcentaje;

  //             $suma_porcentajes += $porcentaje;
  //             $total_operaciones++;
  //         }

  //         // 3. Calcular porcentaje global de la regional
  //         $promedio_global = ($total_operaciones > 0) ? round(($suma_porcentajes / $total_operaciones), 1) : 0;

  //         // 4. Determinar calificación y color del banner
  //         if ($promedio_global <= 50) {
  //             $calificacion = 'INSATISFACTORIO';
  //             $color_banner = '#ef4444'; // Rojo
  //         } elseif ($promedio_global <= 75) {
  //             $calificacion = 'REGULAR';
  //             $color_banner = '#f59e0b'; // Naranja / Amarillo
  //         } elseif ($promedio_global <= 90) {
  //             $calificacion = 'BUENO';
  //             $color_banner = '#0284c7'; // Azul
  //         } else {
  //             $calificacion = 'ÓPTIMO';
  //             $color_banner = '#16a34a'; // Verde
  //         }

  //         $respuesta = array(
  //             'status'          => 'success',
  //             'regional'        => $nombre_regional,
  //             'promedio_global' => $promedio_global,
  //             'calificacion'    => $calificacion,
  //             'color_banner'    => $color_banner,
  //             'categorias'      => $categorias,
  //             'valores'         => $valores,
  //             'gestion'         => $this->gestion
  //         );

  //         echo json_encode($respuesta);
  //     } else {
  //         show_404();
  //     }
  // }

    /*----- CUADRO EVALUACION OPERACIONES REGIONALES (GRAFICO FORM 2)  a eliminar ----*/
    public function cuadro_evaluacion_grafico_form2($dep_id){
      $data['regional']=$this->model_proyecto->get_departamento($dep_id);
      $data['trimestre']=$this->model_evaluacion->trimestre();
      $data['nro']=count($this->model_objetivoregion->list_form2_regional_x_regional($dep_id));
      $data['eval']=$this->eval_oregional->matriz_cumplimiento_operaciones_regional($dep_id); /// matriz form 2  
      $data['cabecera']=$this->eval_oregional->cabecera_reporte_grafico($data['regional']);
      $data['calificacion']=$this->eval_oregional->calificacion_total_form2_regional($dep_id,1);

      
      $tabla='';
      $tabla.='<div style="font-family: Arial;">DETALLE DE OPERACIONES REGIONALES '.$this->gestion.'</div>
              <table>';
                for ($i=0; $i <$data['nro'] ; $i++) { 
                  $tabla.='<tr><td style="font-family: Arial;font-size: 11px;height: 1%;">OPE. '.$data['eval'][$i][0].'.'.$data['eval'][$i][1].'.- '.$data['eval'][$i][2].' - <b>'.$data['eval'][$i][4].' %</b></td></tr>';
                }
                $tabla.='
              </table>
              <hr>';
      $data['tabla_detalle']=$tabla;
//echo $tabla;
      $this->load->view('admin/evaluacion/evaluacion_form2/reporte_grafico_meta_form2', $data);

    }

}