<?php
class CDiagnostico_pei extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('diagnosticoPei/model_diagnosticopei');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->load->library('lib_diagnostico_pei');
      $this->load->library('lib_diagnosticopei_reporte');
        // Si CI no creó la propiedad, la asignamos nosotros a mano
        if (!isset($this->lib_diagnosticopei_reporte)) {
            $CI =& get_instance();
            $this->lib_diagnosticopei_reporte = $CI->lib_diagnosticopei_reporte;
        }

     
      }else{
          redirect('/','refresh');
      }
    }

    /// formulario principal
    public function diagnostico_principal() {
        $pei = $this->model_diagnosticopei->get_diagnostico_activo();
        $dist_id = $this->dist_id; // Asegúrate de que esta variable esté definida
        $data['titulo']='';
        // 1. Verificación temprana (Early Return) para evitar anidación
        if (empty($pei)) {
            $data['cuerpo'] = $this->_mensaje_error("Solicitar que se habilite el formulario de diagnóstico PEI.");
            return $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data);
        }

        $pei_id = $pei[0]['pei_id'];
        $form_distrital = $this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id, $dist_id);

        if($this->tp_adm == 1){
          // Administrador Nacional
            $data['titulo'] = $this->Seleccion_unidadEjecutora();
            $data['cuerpo'] = '<div id="contenedor_formulario"></div>';
        }elseif ($this->conf_pei == 1) {
            // Usuario con permiso de llenado
            $data['cuerpo'] = $this->unidad_ejecutora_eleccionado($pei_id, $dist_id);
        } else {
            // Acceso restringido por configuración
            $data['cuerpo'] = $this->_mensaje_error("Usted no cuenta con los privilegios necesarios para el llenado.");
        }


        $this->load->view('admin/diagnostico_pei/View_diagnostico_pei', $data);
      
    }

    // Función auxiliar para no repetir código HTML de alertas
    private function _mensaje_error($mensaje) {
        return '
        <div class="alert alert-block alert-danger">
            <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Atención!</h4>
            <p>' . $mensaje . '</p>
        </div>';
    }

    /*------- Selecciona Unidad Ejecutora -------*/
    public function Seleccion_unidadEjecutora(){
      $get_diagnostico=$this->model_diagnosticopei->get_diagnostico_activo();
      $UnidadEjecutora=$this->model_diagnosticopei->lista_UnidadEjecutora(); 
      $tabla=''; 
      if(count($get_diagnostico)!=0){
        $tabla.='
          <article class="col-sm-12">
            <input name="base" type="hidden" value="'.base_url().'">
            <div class="well">
              <form class="smart-form">
                  <header>DIAGNOSTICO PEI ('.$get_diagnostico[0]['g_id_inicio'].' - '.$get_diagnostico[0]['g_id_fin'].')</header>
                  <fieldset>          
                    <div class="row">
                      <section class="col col-3">
                          <label class="label">Seleccione Unidad Ejecutora</label>
                            <select class="form-control" id="dist_id" name="dist_id" title="SELECCIONE">
                            <option value="0">Seleccione ..</option>';
                            foreach($UnidadEjecutora as $row){
                              $tabla.='<option value="'.$row['dist_id'].'">'.$row['dist_id'].'.- '.strtoupper($row['dist_distrital']).'</option>';
                            }
                            $tabla.='
                          </select>
                      </section>

                      <!-- BOTÓN PARA DESCARGAR CONSOLIDADO -->
                      <section class="col col-3">
                          <label class="label">&nbsp;</label>
                          <a href="'.site_url("Diagnostico_pei/exportar_consolidado_excel/".$get_diagnostico[0]['pei_id']."/0").'" class="btn btn-success btn-sm" style="padding: 10px; width: 100%; text-align: center; color: white;">
                            <i class="fa fa-file-excel-o"></i> DESCARGAR CONSOLIDADO
                          </a>
                      </section>
                    </div>
                  </fieldset>
              </form>
              </div>
          </article>';
      }
      else{
        $tabla.='
        <div class="alert alert-block alert-danger">
                <h4 class="alert-heading"><i class="fa fa-lock"></i> ¡Sin PEI asignado!</h4>
                <p>Porfavor asigne datos del PEI .</p>
            </div>';
      }
      
      return $tabla;
    }


    /*--- GET LISTA DE UNIDAD EJECUTORA ----*/
    public function get_unidad_ejecutora(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            // Cambiado de 'dist_id' a 'id' para que coincida con el JS
            $dist_id = $this->security->xss_clean($post['id']); 
            $get_diagnostico=$this->model_diagnosticopei->get_diagnostico_activo();

            // Aquí puedes cargar una vista y pasarla a string
            if(count($this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($get_diagnostico[0]['pei_id'],$dist_id))==0){
                $data_to_store = array(
                 'pei_id' => $get_diagnostico[0]['pei_id'],
                 'dist_id' => $dist_id,
                );
                $this->db->insert('formulario_diagnostico_pei', $data_to_store);
            }
           
           $tabla = $this->unidad_ejecutora_eleccionado($get_diagnostico[0]['pei_id'],$dist_id);

            $result = array(
                'respuesta' => 'correcto',
                'tabla' => $tabla,
            );
            
            // Indicamos al navegador que es un JSON
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($result));
        } else {
            show_404();
        }
    }




    /*------- Listado de formularios -------*/
    public function unidad_ejecutora_eleccionado($pei_id,$dist_id){
      $get_form_distrital=$this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id,$dist_id);

      $tabla='';
      $tabla.='
          <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              '.$this->lib_diagnostico_pei->style_form().'
              <div id="toast-notificacion" class="toast-msg">
                ¡Información guardada correctamente! ✓
              </div>
              <div class="well well-sm well-light">
              <h2>'.strtoupper($get_form_distrital[0]['dist_distrital']).'</h2>
                <div id="tabs" data-pei="'.$pei_id.'" data-dist="'.$dist_id.'">
                  <ul>
                    <li>
                      <a href="#tabs-a" data-url="poblacion_afiliada"><b>I.- POBLACIÓN AFILIADA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-b" data-url="grupo_etareo"><b>I.I.- POBLACIÓN POR GRUPO ETAREO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-c" data-url="empresas_aportantes"><b>II.- EMPRESAS APORTANTES</b></a>
                    </li>
                    <li>
                      <a href="#tabs-d" data-url="perfil_epidemiologico"><b>III.- PERFIL EPIDEMIOLOGICO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-e" data-url="infraestructura"><b>IV.- INFRAESTRUCTURA</b></a>
                    </li>
                    <li>
                      <a href="#tabs-f" data-url="diagnostico_camas"><b>V.- DIAGNOSTICO CAMAS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-g" data-url="equipo"><b>VI.- EQUIPO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-h" data-url="recursos_humanos"><b>VII.- RECURSOS HUMANOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-i" data-url="compra_servicios"><b>VIII.- COMPRA DE SERVICIOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-j" data-url="presupuestos"><b>IX.- PRESUPUESTOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-k" data-url="reembolsos"><b>X.- REEMBOLSOS</b></a>
                    </li>
                  </ul>
                  <div id="tabs-a">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-b">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>
                  
                  <div id="tabs-c">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-d">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>
                  
                  <div id="tabs-e">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-f">
                    <div class="row">
                        <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-g">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-h">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-i">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-j">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                  <div id="tabs-k">
                    <div class="row">
                      <div class="contenido-ajax"></div>
                    </div>
                  </div>

                </div>
              </div>
            </article>

            <script type="text/javascript">
              $(document).ready(function() {
                  $("#tabs").tabs({
                      beforeActivate: function(event, ui) {
                          var panel = $(ui.newPanel);
                          var seccion = $(ui.newTab).find("a").attr("data-url");

                          // CAPTURAMOS LOS VALORES DEL DIV PADRE
                          var pei_id = $("#tabs").attr("data-pei");
                          var dist_id = $("#tabs").attr("data-dist");

                          // Si el panel no tiene contenido real, cargamos
                              panel.html("<div style=\'text-align:center; padding:50px;\'><i class=\'fa fa-spinner fa-spin\'></i> Cargando formulario ...</div>");
                              
                              $.post("'.base_url().'index.php/Cdiagnostico_pei/CDiagnostico_pei/cargar_formulario", { seccion: seccion,pei: pei_id,dist: dist_id }, function(data) {
                                  panel.html(data);

                              }).error(function() {
                                  panel.html("Error al cargar datos.");
                              });
                          
                      }
                  });

                  // Carga manual de la primera pestaña al cargar la página
                  var firstTab = $("#tabs ul li:first-child");
                  var firstPanel = $("#tabs-a");
                  $("#tabs").tabs("option", "beforeActivate")({}, {
                      newPanel: firstPanel,
                      newTab: firstTab
                  });
              });
            </script>

          
            <script type="text/javascript">
              // DO NOT REMOVE : GLOBAL FUNCTIONS!
              $(document).ready(function() {
                pageSetUp();
                $("#menu").menu();
                $(".ui-dialog :button").blur();
                $("#tabs").tabs();
              })
            </script>';


      return $tabla;
    }
    

    //// Cargar formulario view
  function cargar_formulario() {
      $seccion = $this->input->post('seccion');
      $pei_id  = $this->input->post('pei');
      $dist_id = $this->input->post('dist');
      
      // Importante: Aquí debes cargar tus datos necesarios
      $get_form_distrital=$this->model_diagnosticopei->get_distrital_formulario_diagnostico_activo($pei_id,$dist_id); 

      switch ($seccion) {
          case 'poblacion_afiliada':
              echo $this->lib_diagnostico_pei->formulario_N1($get_form_distrital);
              break;
          case 'grupo_etareo':
              echo $this->lib_diagnostico_pei->formulario_N1_1($get_form_distrital);
              break;
          case 'empresas_aportantes':
              echo $this->lib_diagnostico_pei->formulario_N2($get_form_distrital);
              break;
          case 'perfil_epidemiologico':
              echo $this->lib_diagnostico_pei->formulario_N3($get_form_distrital);
              break;
          case 'infraestructura':
              echo $this->lib_diagnostico_pei->formulario_N4($get_form_distrital);
              break;
          case 'diagnostico_camas':
              echo $this->lib_diagnostico_pei->formulario_N5($get_form_distrital);
              break;
          case 'equipo':
              echo $this->lib_diagnostico_pei->formulario_N6($get_form_distrital);
              break;
          case 'recursos_humanos':
              echo $this->lib_diagnostico_pei->formulario_N7($get_form_distrital);
              break;
          case 'compra_servicios':
              echo $this->lib_diagnostico_pei->formulario_N8($get_form_distrital);
              break;
          case 'presupuestos':
              echo $this->lib_diagnostico_pei->formulario_N9($get_form_distrital);
              break;
          case 'reembolsos':
              echo $this->lib_diagnostico_pei->formulario_N10($get_form_distrital);
              break;
          // ... otros casos
          default:
              echo "Sección no válida";
              break;
      }

  }

  /// Reporte Formulario Diagnostico Pei
  public function reporte_formulario_pei($tp_rep,$dist_id){
    $get_formulario=$this->model_diagnosticopei->get_dist_formulario_diagnostico($dist_id);
     $data['reporte']= $this->lib_diagnosticopei_reporte->select_reporte_diagnostico_pei($tp_rep,$get_formulario);
     $data['pie_rep']='dnp';
     $this->load->view('admin/diagnostico_pei/View_report_form_diagpei', $data);
  }

  /// Exportar Diagnostico en Excel
  public function exportar_consolidado_excel($tp_rep,$dist_id){
    // 1. Cargar librería (Depende de la que tengas instalada)
      $this->load->library('excel'); 
      $objPHPExcel = new PHPExcel();
      $objPHPExcel->getProperties()->setTitle("Consolidado Institucional PEI");

      // --- PESTAÑA 1: FORMULARIO 1 (POBLACIÓN) ---
      $objPHPExcel->setActiveSheetIndex(0);
      $sheet1 = $objPHPExcel->getActiveSheet();
      $sheet1->setTitle('Población Afiliada');
      $sheet1->setCellValue('A1', 'UNIDAD EJECUTORA');
      $sheet1->setCellValue('B1', 'GESTIÓN');
      $sheet1->setCellValue('C1', 'TITULARES');
      // ... Cargar datos del modelo y hacer bucle para llenar filas ...

      // --- PESTAÑA 2: FORMULARIO 2 (GRUPOS ETÁREOS) ---
      $objPHPExcel->createSheet();
      $objPHPExcel->setActiveSheetIndex(1);
      $sheet2 = $objPHPExcel->getActiveSheet();
      $sheet2->setTitle('Grupos Etáreos');
      $sheet2->setCellValue('A1', 'UNIDAD EJECUTORA');
      $sheet2->setCellValue('B1', 'GRUPO ETÁREO');
      // ... Bucle de datos ...

      // --- PESTAÑA 3: FORMULARIO 4 (INFRAESTRUCTURA) ---
      $objPHPExcel->createSheet();
      $objPHPExcel->setActiveSheetIndex(2);
      $sheet3 = $objPHPExcel->getActiveSheet();
      $sheet3->setTitle('Infraestructura');
      // ... Bucle de datos ...

      // Descarga del archivo
      header('Content-Type: application/vnd.ms-excel');
      header('Content-Disposition: attachment;filename="Consolidado_Institucional_PEI.xls"');
      header('Cache-Control: max-age=0');
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
      $objWriter->save('php://output');
  }

  /// Buscador select CIe10
  public function buscar_cie10_ajax() {
      $search = $this->input->get('q'); // Palabra buscada
      $this->db->select('id, cod_3, descripcion');
      $this->db->from('tabla_cie10');
      $this->db->like('cod_3', $search);
      $this->db->or_like('descripcion', $search);
      $this->db->limit(20); // Limitamos a 20 resultados para que sea veloz
      $query = $this->db->get();

      $resultados = array();
      foreach ($query->result() as $row) {
          $resultados[] = array(
              'id'   => $row->id,
              'text' => $row->cod_3 . " - " . $row->descripcion
          );
      }
      echo json_encode($resultados);
  }


  //// Guarda Observacion al formulario
  function guarda_observacion() {
      $fid = $this->input->post('form_id');
      $nro = $this->input->post('nro');
      $txt = $this->input->post('observacion');

     // 1. Verificamos si ya existe un registro en la tabla de observaciones
     // Usamos el form_id como referencia
      $this->db->where('form_id', $fid);
      $this->db->where('obs_nro', $nro);
      $query = $this->db->get('form_observacion');

      $data = array(
          'form_id'       => $fid,
          'obs_nro'       => $nro, // Nota: Asegúrate de que obs_nro deba ser igual al form_id
          'obs_contenido' => $txt
      );

      if ($query->num_rows() > 0) {
          // 2. Si el registro YA EXISTE en la tabla, actualizamos
          $this->db->where('form_id', $fid);
          $this->db->where('obs_nro', $nro);
          $this->db->update('form_observacion', $data);
          echo "updated";
      } else {
          // 3. Si NO EXISTE, insertamos
          $this->db->insert('form_observacion', $data);
          echo "inserted";
      }
  }


    //// Guarda informacion de las tablas automaticamente form 1
public function guarda_detalle_automatica_form1() {
    // Seguridad para solo aceptar AJAX
    if (!$this->input->is_ajax_request()) return;

    $form_id = $this->input->post('form_id');
    $gestion = $this->input->post('gestion');
    $columna = $this->input->post('columna');
    $valor   = $this->input->post('valor');

    // Validación de columnas
    $columnas_permitidas = array('nro_cot_tit', 'nro_cot_pas', 'nro_cot_ben');
    if (!in_array($columna, $columnas_permitidas)) {
        echo json_encode(array('status' => 'error', 'msg' => 'Columna no permitida'));
        return;
    }

    // Asegurar que el valor sea numérico y no pase de 999,999,999
    $valor = (is_numeric($valor) && $valor >= 0) ? substr($valor, 0, 9) : 0;

    $where = array('form_id' => $form_id, 'g_id' => $gestion);
    $this->db->where($where);
    $existe = $this->db->get('formularion1_detalle')->num_rows();

    if ($existe > 0) {
        $this->db->where($where);
        $res = $this->db->update('formularion1_detalle', array($columna => $valor));
    } else {
        $res = $this->db->insert('formularion1_detalle', array(
            'form_id' => $form_id,
            'g_id'    => $gestion,
            $columna  => $valor
        ));
    }

    // Devolvemos el JSON que el script espera
    echo json_encode(array('status' => $res ? 'success' : 'error'));
}


    //// Guarda informacion de las tablas automaticamente form 1 Grupo Etareo
    public function guarda_detalle_automatica_form1_etareo() {
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        $form_id = $this->input->post('form_id');
        $eta_id  = $this->input->post('eta_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = ($this->input->post('valor') == '' || $this->input->post('valor') < 0) ? 0 : $this->input->post('valor');

        // Iniciar transacción para evitar duplicidad por peticiones rápidas
        $this->db->trans_start();

        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion, 'eta_id' => $eta_id));
        $fila = $this->db->get('formularion1_grupo_etareo')->row();

        if ($fila) {
            $nro_mas = ($campo == 'nro_masculino') ? $valor : $fila->nro_masculino;
            $nro_fem = ($campo == 'nro_femenino') ? $valor : $fila->nro_femenino;

            if ($nro_mas == 0 && $nro_fem == 0) {
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $this->db->delete('formularion1_grupo_etareo');
                $msg = '🗑️ Registro eliminado';
            } else {
                $data_update = array(
                    $campo => $valor,
                    'total_poblacion' => ($nro_mas + $nro_fem)
                );
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $this->db->update('formularion1_grupo_etareo', $data_update);
                $msg = '✅ Actualizado';
            }
        } else {
            if ($valor > 0) {
                $data_insert = array(
                    'form_id' => $form_id,
                    'g_id'    => $gestion,
                    'eta_id'  => $eta_id,
                    $campo    => $valor,
                    'total_poblacion' => $valor 
                );
                $this->db->insert('formularion1_grupo_etareo', $data_insert);
                $msg = '✅ Guardado';
            } else {
                $this->db->trans_complete();
                echo json_encode(array('status' => 'success', 'msg' => 'Nada que guardar'));
                return;
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'error', 'msg' => 'Error de concurrencia en la BD'));
        } else {
            echo json_encode(array('status' => 'success', 'msg' => $msg));
        }
    }
    
  //// Guarda informacion de las tablas automaticamente form2
public function guarda_detalle_automatica_form2() {
    $form_id = $this->input->post('form_id');
    $gestion = $this->input->post('gestion');
    $columna = $this->input->post('columna');
    $valor   = $this->input->post('valor');

    // 1. Columnas específicas para el Formulario 2
    $columnas_permitidas = array('nro_empresas_reg', 'nro_aportes_dia', 'nro_empresa_mora');
    if (!in_array($columna, $columnas_permitidas)) return;

    // 2. Definición del filtro
    $where = array('form_id' => $form_id, 'g_id' => $gestion);
    
    // 3. Verificamos existencia en la tabla del Formulario 2
    $existe = $this->db->get_where('formularion2_detalle', $where)->num_rows();

    if ($existe > 0) {
        // ACTUALIZACIÓN
        $this->db->where($where);
        $this->db->update('formularion2_detalle', array($columna => $valor));
    } else {
        // INSERCIÓN
        $this->db->insert('formularion2_detalle', array(
            'form_id' => $form_id,
            'g_id'    => $gestion,
            $columna  => $valor
        ));
    }

    echo "ok"; // Esto garantiza que el AJAX reciba el 'success' y no se quede cargando
}


  //// Guarda informacion de las tablas automaticamente form3
    public function guarda_detalle_automatica_form3() {
        // 1. Recibir datos por POST
        $form_id  = $this->input->post('form_id');
        $gestion  = $this->input->post('gestion');
        $posicion = $this->input->post('nro_posicion'); // 1 al 10
        $cat      = $this->input->post('categoria');    // tp perfil 1: Morbilidad, 2: Mortalidad
        $columna  = $this->input->post('columna');     // nro_casos, ce_id o detalle_causa
        $valor    = $this->input->post('valor');

        // 2. Validación básica de seguridad
        if (!$form_id || !$gestion || !$posicion) {
            echo "error_datos_incompletos";
            return;
        }

        // 3. Validar que la columna sea permitida para evitar inyecciones
        $columnas_permitidas = array('nro_casos', 'ce_id', 'detalle_causa', 'tipo_perfil_cat');
        if (!in_array($columna, $columnas_permitidas)) {
            echo "error_columna_no_permitida";
            return;
        }

        // 4. Preparar el array para el modelo
        $params = array(
            'form_id'    => (int)$form_id,
            'g_id'       => (int)$gestion,
            'posicion'   => (int)$posicion,
            'categoria'  => (int)$cat,
            'columna'    => $columna,
            'valor'      => $valor
        );

        // 5. Llamar al modelo para procesar el Upsert
        $resultado = $this->upsert_detalle_perfil($params);

        if ($resultado) {
            echo "success";
        } else {
            echo "error_en_db";
        }

    }

    /// update form3
    public function upsert_detalle_perfil($d) {
        // 1. BUSCAR EL ID DE LA TABLA MAESTRA (formularion3_detalle_perfil)
        // Necesitamos el det3_id para poder guardar en la tabla de detalles
        $this->db->where('form_id', $d['form_id']);
        $this->db->where('g_id', $d['g_id']);
        $query_master = $this->db->get('formularion3_detalle_perfil');

        if ($query_master->num_rows() > 0) {
            $master = $query_master->row();
            $det3_id = $master->det3_id;
        } else {
            // Si por alguna razón no existe el maestro para ese año, lo creamos
            $data_master = array(
                'form_id' => $d['form_id'],
                'g_id'    => $d['g_id'],
                'nro_causas' => 10
            );
            $this->db->insert('formularion3_detalle_perfil', $data_master);
            $det3_id = $this->db->insert_id();
        }

        // 2. BUSCAR SI YA EXISTE EL DETALLE ESPECÍFICO
        // Filtramos por el ID maestro, la posición (1-10) y la categoría (Morbilidad/Mortalidad)
        $this->db->where('det3_id', $det3_id);
        $this->db->where('tp_perfil', $d['posicion']);
        $this->db->where('tipo_perfil_cat', $d['categoria']);
        $query_detail = $this->db->get('detalle_form3_perfil');

        // Preparamos los datos a guardar
        $data_save = array(
            $d['columna'] => $d['valor'] // Ej: nro_casos => 50
        );

        if ($query_detail->num_rows() > 0) {
            // 3. ACTUALIZAR
            $this->db->where('det3_id', $det3_id);
            $this->db->where('tp_perfil', $d['posicion']);
            $this->db->where('tipo_perfil_cat', $d['categoria']);
            return $this->db->update('detalle_form3_perfil', $data_save);
        } else {
            // 4. INSERTAR
            $data_save['det3_id'] = $det3_id;
            $data_save['tp_perfil'] = $d['posicion'];
            $data_save['tipo_perfil_cat'] = $d['categoria'];
            return $this->db->insert('detalle_form3_perfil', $data_save);
        }
    }


    //// Validacion form 4 - Establecimientos inscritos en el poa
    public function guarda_detalle_infraestructura_form4() {
        if (!$this->input->is_ajax_request()) { show_404(); return; }

        $form_id = $this->input->post('form_id');
        $act_id  = $this->input->post('act_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = $this->input->post('valor');

        if ($campo == 'nro_consultorios') {
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // 1. Iniciar Transacción para evitar que dos procesos escriban al mismo tiempo
        $this->db->trans_start();

        // 2. Asegurar Cabecera
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();

        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            $this->db->insert('formularion4_detalle_infra', array(
                'form_id' => $form_id,
                'g_id' => $gestion,
                'form4_estado' => 1
            ));
            $det4_id = $this->db->insert_id();
        }

        // 3. VERIFICACIÓN CRÍTICA: ¿Existe ya este act_id para este det4_id?
        // Usamos select(1) para rapidez
        $this->db->where(array('det4_id' => $det4_id, 'act_id' => $act_id));
        $existe = $this->db->get('infraestructura_form4')->row();

        if ($existe) {
            // ACTUALIZACIÓN ESTRICTA por ID primario
            $this->db->where('infra_id', $existe->infra_id);
            $res = $this->db->update('infraestructura_form4', array($campo => $valor));
        } else {
            // INSERCIÓN: Solo si el valor no es vacío o cero (opcional, para no llenar basura)
            $data_insert = array(
                'det4_id'  => $det4_id,
                'act_id'   => $act_id,
                'tp_infra' => 1, 
                $campo     => $valor
            );
            $res = $this->db->insert('infraestructura_form4', $data_insert);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 'error', 'msg' => '❌ Error de concurrencia'));
        } else {
            echo json_encode(array('status' => 'success', 'msg' => '✅ Guardado correctamente ..'));
        }
    }



    //// agregamos nueva fila para otros establecimientos
    public function nuevo_infra_otro() {
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');

        // 1. Asegurar det4_id (Cabecera)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();
        
        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            $this->db->insert('formularion4_detalle_infra', array('form_id' => $form_id, 'g_id' => $gestion, 'form4_estado' => 1));
            $det4_id = $this->db->insert_id();
        }

        // 2. Insertar registro en blanco en la tabla de Otros
        $res = $this->db->insert('infraestructura_otros_form4', array(
            'det4_id' => $det4_id,
            'tp_infra' => 0,
            'nro_consultorios' => 0
        ));
        $nuevo_id = $this->db->insert_id();

        echo json_encode(array('status' => $res ? 'success' : 'error', 'id' => $nuevo_id));
    }

    //// guarda informacion de otros establecimientos
    public function guarda_infra_otros_automatica() {
        // 1. Verificación de seguridad para peticiones AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Recepción de parámetros desde el JS
        $infra_otro_id = $this->input->post('id');      // ID primario de la tabla otros
        $form_id       = $this->input->post('form_id'); // ID del formulario diagnóstico
        $gestion       = $this->input->post('gestion'); // Año (ej. 2025)
        $campo         = $this->input->post('campo');   // Columna a modificar
        $valor         = $this->input->post('valor');   // Valor ingresado

        // 3. Validación de Negativos para campos numéricos
        if ($campo == 'nro_consultorios') {
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // 4. ASEGURAR CABECERA (formularion4_detalle_infra)
        // Buscamos si ya existe el vínculo con el diagnóstico pei
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion4_detalle_infra')->row();

        if ($cabecera) {
            $det4_id = $cabecera->det4_id;
        } else {
            // Si no existe (caso de fila nueva), creamos la cabecera automáticamente
            $data_cabecera = array(
                'form_id'      => $form_id,
                'g_id'         => $gestion,
                'form4_estado' => 1
            );
            $this->db->insert('formularion4_detalle_infra', $data_cabecera);
            $det4_id = $this->db->insert_id();
        }

        // 5. ACTUALIZAR EL REGISTRO DE "OTROS"
        // Aseguramos que el det4_id esté actualizado por si se acaba de crear
        $data_update = array(
            'det4_id' => $det4_id,
            $campo    => $valor
        );

        $this->db->where('infra_otro_id', $infra_otro_id);
        $res = $this->db->update('infraestructura_otros_form4', $data_update);

        // 6. Respuesta JSON para el Script
        if ($res) {
            echo json_encode(array('status' => 'success', 'msg' => '✅ Informacion guardado correctamente ...'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => '❌ Error al actualizar registro'));
        }
    }

    public function eliminar_infra_otro() {
        if (!$this->input->is_ajax_request()) return;

        $id = $this->input->post('id');

        if ($id) {
            $this->db->where('infra_otro_id', $id);
            $res = $this->db->delete('infraestructura_otros_form4');
            
            echo json_encode(array('status' => $res ? 'success' : 'error'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'ID no válido'));
        }
    }


    ///// Guarda formulario 5
    public function guarda_produccion_cama_automatica() {
        // 1. Verificación de seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Recepción de parámetros
        $form_id = $this->input->post('form_id');
        $act_id  = $this->input->post('act_id');
        $gestion = $this->input->post('gestion');
        $campo   = $this->input->post('campo');
        $valor   = $this->input->post('valor');

        // 3. Validación de datos en el servidor
         // VALIDACIÓN EN EL SERVIDOR
        if ($campo == 'ocupacion') {
            if (!is_numeric($valor) || $valor < 0) {
                $valor = 0;
            } elseif ($valor > 100) {
                $valor = 100; // Truncamos al máximo permitido
            }
        } else {
            // Para los otros campos numéricos (camas, estancia, giro)
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        }

        // 4. ASEGURAR CABECERA (formularion5_produccion_cama)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion5_produccion_cama')->row();

        if ($cabecera) {
            $det5_id = $cabecera->det5_id;
        } else {
            // Si no existe para esa gestión, la creamos
            $this->db->insert('formularion5_produccion_cama', array(
                'form_id' => $form_id,
                'g_id'    => $gestion,
                'form3_estado' => 1
            ));
            $det5_id = $this->db->insert_id();
        }

        // 5. GUARDADO DEL DETALLE (Upsert eficiente)
        // Intentamos actualizar primero
        $this->db->where(array('det5_id' => $det5_id, 'act_id' => $act_id));
        $this->db->update('detalle_form5_produccion_cama', array($campo => $valor));

        // Si no se afectó ninguna fila, es que el registro no existe
        if ($this->db->affected_rows() == 0) {
            // Verificamos si realmente no existe (por si el valor era el mismo)
            $this->db->where(array('det5_id' => $det5_id, 'act_id' => $act_id));
            $check = $this->db->get('detalle_form5_produccion_cama')->num_rows();

            if ($check == 0) {
                $data_insert = array(
                    'det5_id' => $det5_id,
                    'act_id'  => $act_id,
                    $campo    => $valor
                );
                $res = $this->db->insert('detalle_form5_produccion_cama', $data_insert);
            } else {
                $res = true; // El registro existía pero el valor enviado era igual al actual
            }
        } else {
            $res = true; // Update exitoso
        }

        // 6. Respuesta para el script
        echo json_encode(array('status' => $res ? 'success' : 'error'));
    }

    /// valida formulario 6 equipos
    public function crear_equipo_completo() {
        if (ob_get_length()) ob_clean(); // Limpieza de salida
        if (!$this->input->is_ajax_request()) { show_404(); return; }

        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $act_id  = $this->input->post('act_id');
        $servicio = $this->input->post('servicio');
        $detalle  = $this->input->post('detalle');
        $precio   = $this->input->post('precio');

        // Validación de seguridad
        $precio = (is_numeric($precio) && $precio >= 0) ? $precio : 0;

        // 1. Asegurar Cabecera
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion6_equipos')->row();
        $det6_id = ($cabecera) ? $cabecera->det6_id : 0;

        if (!$det6_id) {
            $this->db->insert('formularion6_equipos', array(
                'form_id' => $form_id,
                'g_id' => $gestion,
                'form6_estado' => 1
            ));
            $det6_id = $this->db->insert_id();
        }

        // 2. Insertar Detalle con nombre de columna corregido
        $data_detalle = array(
            'det6_id'           => $det6_id,
            'act_id'            => $act_id,
            'servicio'          => strtoupper(trim($servicio)),
            'detalle_equipo'    => strtoupper(trim($detalle)),
            'precio_referencial' => $precio // <--- SINCRONIZADO CON TU SQL
        );

        $res = $this->db->insert('detalle_form6_equipos', $data_detalle);
        $nuevo_id = $this->db->insert_id();

        echo json_encode(array(
            'status' => $res ? 'success' : 'error', 
            'id' => $nuevo_id
        ));
    }

    //// update equipo
    public function guarda_detalle_equipo_form6() {
        // 1. Limpieza de salida para evitar que espacios o errores previos rompan el JSON
        if (ob_get_length()) ob_clean();

        // 2. Verificación de seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 3. Recepción de parámetros (det6_form6_id, columna, valor)
        $id    = $this->input->post('id');
        $campo = $this->input->post('campo');
        $valor = $this->input->post('valor');

        // 4. Validación rápida por tipo de columna
        if ($campo == 'precio_referencial') {
            // Aseguramos que sea un número positivo
            $valor = (is_numeric($valor) && $valor >= 0) ? $valor : 0;
        } else {
            // Para 'servicio' y 'detalle_equipo', limpiamos y pasamos a mayúsculas
            $valor = strtoupper(trim($valor));
        }

        // 5. UPDATE DIRECTO (Es más rápido que buscar con un IF/SELECT previo)
        $this->db->where('det6_form6_id', $id);
        $res = $this->db->update('detalle_form6_equipos', array($campo => $valor));

        // 6. Respuesta JSON veloz
        if ($res) {
            echo json_encode(array('status' => 'success'));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'Error de actualización'));
        }
    }

    public function eliminar_equipo_form6() {
        // 1. Verificación de seguridad para peticiones AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Limpieza de cualquier salida previa para evitar errores de JSON
        if (ob_get_length()) ob_clean();

        // 3. Recepción del ID del detalle
        $id = $this->input->post('id'); // det6_form6_id

        if ($id) {
            // 4. Ejecución del borrado
            $this->db->where('det6_form6_id', $id);
            $res = $this->db->delete('detalle_form6_equipos');

            if ($res) {
                // Éxito: el JS ejecutará el .fadeOut() de la fila
                echo json_encode(array('status' => 'success'));
            } else {
                echo json_encode(array('status' => 'error', 'msg' => 'No se pudo eliminar el registro de la base de datos.'));
            }
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'ID de registro no válido.'));
        }
    }
    /////////////////////////////////////////////////

    //// Guarda formulario Recursos Humanos
    public function guarda_rrhh_automatica() {
        // 1. Limpieza de salida para evitar errores de JSON
        if (ob_get_length()) ob_clean();

        // 2. Seguridad AJAX
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 3. Parámetros recibidos del JS
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $tp_rrhh = $this->input->post('tp_rrhh'); // 1:item, 2:contrato, 3:acefalia
        $campo   = $this->input->post('campo');
        $valor   = intval($this->input->post('valor'));

        // 4. ASEGURAR CABECERA (formularion7_rrhh)
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
        $cabecera = $this->db->get('formularion7_rrhh')->row();

        if ($cabecera) {
            $det7_id = $cabecera->det7_id;
        } else {
            // Crear cabecera si no existe para ese año
            $this->db->insert('formularion7_rrhh', array(
                'form_id' => $form_id,
                'g_id'    => $gestion,
                'form7_estado' => 1
            ));
            $det7_id = $this->db->insert_id();
        }

        // 5. GUARDADO DEL DETALLE (Upsert)
        // Intentamos actualizar la fila que coincida con la cabecera y el tipo (Item/Contr/Acef)
        $this->db->where(array('det7_id' => $det7_id, 'tp_rrhh_form' => $tp_rrhh));
        $this->db->update('detalle_form7_rrhh', array($campo => $valor));

        // Si no se afectó ninguna fila, el registro para ese tipo no existe, lo creamos
        if ($this->db->affected_rows() == 0) {
            // Doble verificación de existencia real
            $this->db->where(array('det7_id' => $det7_id, 'tp_rrhh_form' => $tp_rrhh));
            $check = $this->db->get('detalle_form7_rrhh')->num_rows();

            if ($check == 0) {
                $data_insert = array(
                    'det7_id'      => $det7_id,
                    'tp_rrhh_form' => $tp_rrhh,
                    $campo         => $valor
                );
                $res = $this->db->insert('detalle_form7_rrhh', $data_insert);
                $id_detalle = $this->db->insert_id();
            } else {
                $res = true; // El valor era el mismo, no hubo cambio
                $id_detalle = 0; // Obtener de la DB si fuera necesario
            }
        } else {
            $res = true;
        }

        // 6. RECALCULAR TOTAL DE LA FILA (Integridad de datos en DB)
        // Esto asegura que la columna 'total' de la tabla detalle esté siempre sincronizada
        if ($res) {
            $this->db->query("
                UPDATE detalle_form7_rrhh 
                SET total = (
                    nro_medicos + nro_odontologos + nro_farmaceuticos + nro_laboratoristas + 
                    nro_otros_prof + nro_nutricionistas + nro_trabajo_social + nro_jefe_superv_enf + 
                    nro_lic_grad_enf + nro_aux_enf + nro_pers_adm + nro_pers_adm_salud + 
                    nro_pers_adm_tec + nro_pers_adm_aux + nro_pers_adm_chof + 
                    nro_pers_adm_artesanos + nro_pers_adm_trab_manual
                ) 
                WHERE det7_id = $det7_id AND tp_rrhh_form = $tp_rrhh
            ");
        }

        // 7. Respuesta final
        echo json_encode(array('status' => $res ? 'success' : 'error'));
    }
    /////////////////////////////

    ///// Guarda formulario Compra de Servicios 
    public function guarda_servicios_automatica() {
      if (ob_get_length()) ob_clean(); // Limpieza para evitar errores de JSON
      if (!$this->input->is_ajax_request()) return;

      $form_id  = $this->input->post('form_id');
      $gestion  = $this->input->post('gestion');
      $nro_fila = $this->input->post('fila'); // El nro_fila (1, 2 o 3) es nuestra clave
      $campo    = $this->input->post('campo');
      $valor    = $this->input->post('valor');
      $id_sent  = $this->input->post('id'); // ID que viene del JS

      // 1. Asegurar cabecera (det8_id)
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cab = $this->db->get('formularion8_compra_servicios')->row();
      
      if ($cab) {
          $det8_id = $cab->det8_id;
      } else {
          $this->db->insert('formularion8_compra_servicios', array(
              'form_id' => $form_id, 
              'g_id' => $gestion,
              'form8_estado' => 1
          ));
          $det8_id = $this->db->insert_id();
      }

      // 2. LÓGICA ANTIDUPLICADOS: Buscar por "Casillero Fijo"
      // Buscamos si ya existe un registro vinculado a esta cabecera Y a esta posición (1, 2 o 3)
      $this->db->where(array(
          'det8_id' => $det8_id, 
          'nro_posicion' => $nro_fila // Usamos la fila como identificador único
      ));
      $registro_existente = $this->db->get('detalle_form8_compra_servicios')->row();

      if ($registro_existente) {
          // ACCIÓN: ACTUALIZAR (Si ya existe la fila 1, 2 o 3 para ese año)
          $this->db->where('det8_form8_id', $registro_existente->det8_form8_id);
          $res = $this->db->update('detalle_form8_compra_servicios', array($campo => $valor));
          $id_final = $registro_existente->det8_form8_id;
      } else {
          // ACCIÓN: INSERTAR (Es la primera vez que se toca este casillero)
          $data_ins = array(
              'det8_id' => $det8_id,
              'nro_posicion' => $nro_fila, // Guardamos la posición para futuras validaciones
              $campo => $valor
          );
          $res = $this->db->insert('detalle_form8_compra_servicios', $data_ins);
          $id_final = $this->db->insert_id();
      }

      // 3. Respuesta JSON con el ID real de la base de datos
      echo json_encode(array(
          'status' => $res ? 'success' : 'error', 
          'nuevo_id' => $id_final
      ));
  }

  //// guarda formulario Presupuesto
  public function guarda_presupuesto_automatica() {
      // 1. Limpieza de salida para evitar errores de JSON
      if (ob_get_length()) ob_clean();

      $id_det  = $this->input->post('id');
      $campo   = $this->input->post('campo');
      $valor   = $this->input->post('valor');
      $form_id = $this->input->post('form_id');
      $gestion = $this->input->post('gestion');

      // --- 1. ASEGURAR CABECERA (formularion9_presupuestos) ---
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cabecera = $this->db->get('formularion9_presupuestos')->row();

      if ($cabecera) {
          $det9_id = $cabecera->det9_id;
      } else {
          $this->db->insert('formularion9_presupuestos', array(
              'form_id' => $form_id, 
              'g_id' => $gestion,
              'form9_estado' => 1
          ));
          $det9_id = $this->db->insert_id();
      }

      // --- 2. LÓGICA UPSERT PARA EL DETALLE ---
      // Si el ID es 0, buscamos si ya existe una fila para este det9_id para no duplicar
      if (empty($id_det) || $id_det == 0) {
          $this->db->where('det9_id', $det9_id);
          $existe_det = $this->db->get('detalle_form9_presupuestos')->row();
          
          if ($existe_det) {
              $id_det = $existe_det->det9_form9_id;
          } else {
              // Si realmente es nuevo, insertamos
              $this->db->insert('detalle_form9_presupuestos', array(
                  'det9_id' => $det9_id,
                  $campo => $valor
              ));
              $id_det = $this->db->insert_id();
          }
      }

      // --- 3. ACTUALIZAR EL VALOR DEL CAMPO ACTUAL ---
      $this->db->where('det9_form9_id', $id_det);
      $res = $this->db->update('detalle_form9_presupuestos', array($campo => $valor));

      // --- 4. RECALCULO AUTOMÁTICO (Usando COALESCE para evitar errores con NULL) ---
      if ($res) {
          $sql = "UPDATE detalle_form9_presupuestos 
                  SET total_ingresos_ejecutados = (COALESCE(ingresos_propios_ejecutados,0) + COALESCE(recursos_financieros_ejecutados,0)),
                      gastos_programados        = (COALESCE(ingresos_propios_programados,0) + COALESCE(recursos_financieros_programados,0)),
                      deficit_superavit         = (COALESCE(ingresos_propios_ejecutados,0) + COALESCE(recursos_financieros_ejecutados,0)) - COALESCE(gastos_ejecutados,0)
                  WHERE det9_form9_id = " . intval($id_det);
          $this->db->query($sql);
      }

      // --- 5. RESPUESTA AL SCRIPT ---
      echo json_encode(array(
          'status' => $res ? 'success' : 'error', 
          'nuevo_id' => $id_det
      ));
  }

  /////
    public function guarda_reembolso_automatica() {
      // 1. Limpieza radical de salida
      if (ob_get_length()) ob_clean();

      $id_det  = $this->input->post('id');
      $campo   = $this->input->post('campo');
      $valor   = $this->input->post('valor');
      $form_id = $this->input->post('form_id');
      $gestion = $this->input->post('gestion');

      // Asegurar valor numérico
      $valor = (is_numeric($valor)) ? $valor : 0;

      // 2. Asegurar cabecera (formularion10_reembolsos)
      $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion));
      $cab = $this->db->get('formularion10_reembolsos')->row();
      $det10_id = ($cab) ? $cab->det10_id : 0;

      if (!$det10_id) {
          $this->db->insert('formularion10_reembolsos', array('form_id' => $form_id, 'g_id' => $gestion, 'form10_estado' => 1));
          $det10_id = $this->db->insert_id();
      }

      // 3. Lógica Upsert para el Detalle (Tabla: detalle_form10_presupuestos)
      if (empty($id_det) || $id_det == 0) {
          $this->db->where('det10_id', $det10_id);
          $existe_det = $this->db->get('detalle_form10_presupuestos')->row();
          
          if ($existe_det) {
              $id_det = $existe_det->det10_form10_id;
          } else {
              $this->db->insert('detalle_form10_presupuestos', array('det10_id' => $det10_id, $campo => $valor));
              $id_det = $this->db->insert_id();
          }
      }

      // 4. Actualizar campo
      $this->db->where('det10_form10_id', $id_det);
      $res = $this->db->update('detalle_form10_presupuestos', array($campo => $valor));

      // 5. Recalculo del Total en Servidor (Evita descuadres)
      if ($res) {
          $sql = "UPDATE detalle_form10_presupuestos 
                  SET total_reembolsos = (
                      COALESCE(reemb_concep_medicamentos,0) + 
                      COALESCE(reemb_concep_laboratorio,0) + 
                      COALESCE(reemb_concep_imagenologia,0) + 
                      COALESCE(reemb_otros_conceptos,0)
                  )
                  WHERE det10_form10_id = " . intval($id_det);
          $this->db->query($sql);
      }

      echo json_encode(array('status' => $res ? 'success' : 'error', 'nuevo_id' => $id_det));
  }
}