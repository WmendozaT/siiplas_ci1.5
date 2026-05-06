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
                      <a href="#tabs-f" data-url="equipamiento"><b>V.- EQUIPO</b></a>
                    </li>
                    <li>
                      <a href="#tabs-g" data-url="recursos_humanos"><b>VI.- RECURSOS HUMANOS</b></a>
                    </li>
                    <li>
                      <a href="#tabs-h" data-url="compra_servicios"><b>VII.- COMPRA DE SERVICIOS</b></a>
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
          case 'equipamiento':
              echo $this->lib_diagnostico_pei->trabajando($get_form_distrital);
              break;
          case 'compra_servicios':
              echo $this->lib_diagnostico_pei->trabajando($get_form_distrital);
              break;
          case 'compra_servicios':
              echo $this->lib_diagnostico_pei->trabajando($get_form_distrital);
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
        $form_id = $this->input->post('form_id');
        $gestion = $this->input->post('gestion');
        $columna = $this->input->post('columna'); // Ej: nro_cot_tit
        $valor   = $this->input->post('valor');

        // Validar que la columna sea permitida (Seguridad)
        $columnas_permitidas = array('nro_cot_tit', 'nro_cot_pas', 'nro_cot_ben');
        if (!in_array($columna, $columnas_permitidas)) return;

          $this->db->where('form_id', $form_id);
          $this->db->where('g_id', $gestion);
          $existe = $this->db->get('formularion1_detalle')->num_rows();

          if ($existe > 0) {
              $this->db->where('form_id', $form_id);
              $this->db->where('g_id', $gestion);
              return $this->db->update('formularion1_detalle', array($columna => $valor));
          } else {
              return $this->db->insert('formularion1_detalle', array(
                  'form_id' => $form_id,
                  'g_id'    => $gestion,
                  $columna  => $valor
              ));
          }

        echo "ok";
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

        // 1. Buscamos si el registro ya existe
        $this->db->where(array('form_id' => $form_id, 'g_id' => $gestion, 'eta_id' => $eta_id));
        $fila = $this->db->get('formularion1_grupo_etareo')->row();

        if ($fila) {
            // Determinamos los valores finales de ambos campos
            $nro_mas = ($campo == 'nro_masculino') ? $valor : $fila->nro_masculino;
            $nro_fem = ($campo == 'nro_femenino') ? $valor : $fila->nro_femenino;

            // REGLA: Si AMBOS son 0, se elimina de la tabla
            if ($nro_mas == 0 && $nro_fem == 0) {
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $res = $this->db->delete('formularion1_grupo_etareo');
                $msg = '🗑️ Registro eliminado por ser ambos valores 0';
            } else {
                // Si al menos uno no es 0, actualizamos
                $data_update = array(
                    $campo => $valor,
                    'total_poblacion' => ($nro_mas + $nro_fem)
                );
                $this->db->where('det_etareo_id', $fila->det_etareo_id);
                $res = $this->db->update('formularion1_grupo_etareo', $data_update);
                $msg = '✅ Información Actualizada correctamente !!';
            }
        } else {
            // Si no existe y el valor es mayor a 0, insertamos
            if ($valor > 0) {
                $data_insert = array(
                    'form_id' => $form_id,
                    'g_id'    => $gestion,
                    'eta_id'  => $eta_id,
                    $campo    => $valor,
                    'total_poblacion' => $valor 
                );
                $res = $this->db->insert('formularion1_grupo_etareo', $data_insert);
                $msg = '✅ Información Guardada';
            } else {
                // Si el valor es 0 y no existe el registro, no hacemos nada
                echo json_encode(array('status' => 'success', 'msg' => 'Nada que guardar (valor 0)'));
                return;
            }
        }

        if ($res) {
            echo json_encode(array('status' => 'success', 'msg' => $msg));
        } else {
            echo json_encode(array('status' => 'error', 'msg' => 'Error en la Base de Datos'));
        }
    }
    
  //// Guarda informacion de las tablas automaticamente form2
  public function guarda_detalle_automatica_form2() {
      $form_id = $this->input->post('form_id');
      $gestion = $this->input->post('gestion');
      $columna = $this->input->post('columna'); // Ej: nro_cot_tit
      $valor   = $this->input->post('valor');

      // Validar que la columna sea permitida (Seguridad)
      $columnas_permitidas = array('nro_empresas_reg', 'nro_aportes_dia', 'nro_empresa_mora');
      if (!in_array($columna, $columnas_permitidas)) return;

        $this->db->where('form_id', $form_id);
        $this->db->where('g_id', $gestion);
        $existe = $this->db->get('formularion2_detalle')->num_rows();

        if ($existe > 0) {
            $this->db->where('form_id', $form_id);
            $this->db->where('g_id', $gestion);
            return $this->db->update('formularion2_detalle', array($columna => $valor));
        } else {
            return $this->db->insert('formularion2_detalle', array(
                'form_id' => $form_id,
                'g_id'    => $gestion,
                $columna  => $valor
            ));
        }

      echo "ok";
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
}