<?php
class CDiagnostico_equipamiento extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->model('mdiagnostico_equipamiento/model_diagnosticoequip');

      $this->dist_id = $this->session->userData('dist');
      $this->dep_id = $this->session->userData('dep_id');
      $this->gestion = $this->session->userData('gestion');
      $this->fun_id = $this->session->userData('fun_id'); ///
      $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->load->library('lib_diagnostico_equipamiento');
     // $this->load->library('lib_diagnosticopei_reporte');
        // Si CI no creó la propiedad, la asignamos nosotros a mano
        // if (!isset($this->lib_diagnosticopei_reporte)) {
        //     $CI =& get_instance();
        //     $this->lib_diagnosticopei_reporte = $CI->lib_diagnosticopei_reporte;
        // }
        
      }else{
          redirect('/','refresh');
      }
    }

    /// formulario principal
    public function diagnostico_principal() {
        $equipamiento = $this->model_diagnosticoequip->get_diagnostico_equipamiento_activo();
        $dist_id = $this->dist_id; // Asegúrate de que esta variable esté definida
        $data['titulo']='
                    <div style="margin-bottom: 15px; text-align: left;">
                    <button type="button" class="btn btn-success btn-sm font-md" data-toggle="modal" data-target="#modal_nuevo_equipamiento" style="font-weight: bold; background: #e67e22; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.15); letter-spacing:0.3px; padding: 6px 15px;">
                        <i class="fa fa-plus-circle"></i> + REGISTRAR NUEVO REQUERIMIENTO DE EQUIPAMIENTO
                    </button>
                </div>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_nuevo_equipamiento">
    Abrir Formulario
</button>';
        if(count($equipamiento)!=0){
            $data['listado']=$this->lib_diagnostico_equipamiento->listado_equipamiento($equipamiento);
        }
        else{
            echo "Error !!!";
        }

        $this->load->view('admin/diagnostico_equipamiento/View_diagnostico_equipamiento', $data);

    }



 public function get_establecimientos_por_dist_json() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX
        if ($this->input->is_ajax_request()) {
            
            // Sanitización estricta de variables contra inyecciones XSS
            $dist_id = $this->security->xss_clean($this->input->post('dist_id'));
            $dist_id = intval($dist_id);

            $lista_json = array();

            if ($dist_id > 0) {
                // Consulta al Modelo pasándole la distrital del POST y la gestión de la sesión
                // NOTA TÉCNICA: Asegúrate de que $this->gestion esté bien cargada en el constructor
                $establecimientos = $this->model_diagnosticoequip->get_establecimientos_distrital($dist_id, $this->gestion);

                if (!empty($establecimientos)) {
                    foreach ($establecimientos as $est) {
                        // Formateamos la matriz con las llaves exactas que espera tu bucle $.each
                        $lista_json[] = array(
                            'act_id'          => intval($est['act_id']),
                            'establecimiento' => trim(strtoupper($est['tipo'] . ' ' . $est['act_descripcion']))
                        );
                    }
                }
            }

            // 🌟 EL BLINDAJE RAÍZ: Limpiamos cualquier Notice de PHP remanente en el búfer de salida
            if (ob_get_length()) {
                ob_clean();
            }

            // 2. Despacho seguro con cabeceras HTTP explícitas de CodeIgniter
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode($lista_json));

        } else {
            show_404();
        }
    }





     public function eliminar_requerimiento_equipamiento() {
        // 1. Capa de Seguridad: Validar que sea una petición legítima AJAX por POST
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            // Sanitización estricta de variables contra inyecciones XSS
            $post = $this->security->xss_clean($this->input->post());
            $form_equip_id = isset($post['form_equip_id']) ? intval($post['form_equip_id']) : 0;

            if ($form_equip_id <= 0) {
                $result = array('respuesta' => 'error', 'mensaje' => 'Identificador de registro inválido o inexistente.');
                $this->_retornar_json($result);
                return;
            }

            // 2. Protocolo de Transacción en Bloque (PostgreSQL)
            $this->db->trans_begin();

            try {
                // Paso A: Cambiamos el estado a 3 (Eliminado / Dado de baja) en la tabla principal
                $data_baja = array('estado' => 3);
                $this->db->where('form_equip_id', $form_equip_id);
                $this->db->update('formulario_diagnostico_equipamiento', $data_baja);

                // Paso B: Purgamos físicamente las filas del quinquenio para liberar espacio en disco
                $this->db->where('form_equip_id', $form_equip_id);
                $this->db->delete('temporalidad_diagnostico_equipamiento');

                // 3. Evaluación del estado de la transacción
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $result = array('respuesta' => 'error', 'mensaje' => 'Error interno al intentar procesar la baja en las tablas relacionales.');
                } else {
                    $this->db->trans_commit();
                    $result = array('respuesta' => 'correcto');
                }

            } catch (Exception $e) {
                $this->db->trans_rollback();
                $result = array('respuesta' => 'error', 'mensaje' => 'Excepción crítica en la base de datos: ' . $e->getMessage());
            }

            // Despachamos la respuesta en un JSON legítimo interpretado por tu script
            $this->_retornar_json($result);

        } else {
            show_404();
        }
    }
}