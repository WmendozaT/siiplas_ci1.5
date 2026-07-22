<?php
class crequerimiento extends CI_Controller{
    var $gestion;
    var $rol;
    var $fun_id;

    function __construct(){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf');
        $this->load->library('pdf2');
        $this->load->model('menu_modelo');
        $this->load->model('programacion/insumos/minsumos'); /// gestion 2019
        $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
      //  $this->load->model('programacion/insumos/minsumos_delegado');
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('mantenimiento/model_entidad_tras');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_componente');
        $this->load->model('programacion/model_producto');
       
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('mestrategico/model_objetivoregion');
        $this->load->model('modificacion/model_modrequerimiento'); /// Gestion 2020
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->rol = $this->session->userData('rol_id');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userdata("fun_id");
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->conf_form5 = $this->session->userData('conf_form5');
        $this->load->library('programacionpoa');
        }else{
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }

    /*---- LISTA DE REQUERIMIENTO POR ACTIVIDAD ----*/
    public function list_requerimientos($prod_id) {
        // 🌟 REGLA 1: Blindaje elástico de hardware y cast numérico compatible con Postgres
        if (function_exists('ini_set')) {
            ini_set('memory_limit', '256M'); // Espacio de búfer para el cronograma de insumos
        }
        
        // Casteamos la clave primaria debido al DDL numeric(18,0) de tu tabla maestra
        $prod_id_clean = floatval($this->security->xss_clean($prod_id));

        if ($prod_id_clean <= 0) {
            show_error("Identificador de Actividad inválido o corrupto en el SIIPLAS.");
            return;
        }

        // 1. Recuperamos la ficha maestra de la actividad seleccionada
        $get_producto = $this->model_producto->get_producto_id($prod_id_clean);
        
        // 🛠️ REPARADO: Validación de existencia real antes de evaluar posiciones de arreglos
        if (!empty($get_producto) && count($get_producto) > 0) {
            
            // Asignamos el sub-arreglo plano para simplificar la nomenclatura de tus variables
            $data['producto'] = $get_producto;
            $producto_row = $get_producto[0]; // Hilera única activa de la consulta
            
            $data['stylo'] = $this->programacionpoa->estilo_tabla_form5();
            $com_id_actual = intval($producto_row['com_id']);

            // 2. Recuperamos el Componente / Unidad Organizacional vinculada
            $get_componente = $this->model_componente->get_componente($com_id_actual, $this->gestion);
            
            if (empty($get_componente) || count($get_componente) == 0) {
                show_error("Error de Consistencia Relacional: La actividad está vinculada a un componente inexistente.");
                return;
            }
            
            $data['componente'] = $get_componente;
            $componente_row = $get_componente[0]; // Hilera única activa del componente

            // 3. CONSTRUCCIÓN DE LA CABECERA INSTITUCIONAL CNS
            // Plantilla base para Proyectos de Inversión (tp_id != 4)
            $tit = '<small>PROYECTO : </small>' . $componente_row['aper_programa'] . ' ' . $componente_row['proy_sisin'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['proy_nombre'];
            
            /*--------- Caso Gasto Corriente (Apertura tipo 4) ----------*/
            if (intval($componente_row['tp_id']) == 4) {
                $tit = '<h2>' . $componente_row['aper_programa'] . ' ' . $componente_row['aper_proyecto'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['tipo'] . ' ' . $componente_row['act_descripcion'] . ' - ' . $componente_row['abrev'] . '  / <b>' . $componente_row['serv_cod'] . ' </b>' . $componente_row['tipo_subactividad'] . ' ' . $componente_row['serv_descripcion'] . '</h2>';
            }

            $data['datos'] = '<h1>' . $tit . '</h1>
                             <h1><small>ACTIVIDAD : </small>COD - ' . round($producto_row['prod_cod'], 2) . '. ' . $producto_row['prod_producto'] . '</h1>';

            $data['prog_especial'] = '';
            
            // 4. 🛠️ REPARADO: Validación elástica de la Unidad Responsable para proyectos de arrastre Bolsa
            if (intval($componente_row['por_id']) == 1) {
                $uni_resp_id = intval($producto_row['uni_resp']);
                
                // Inicializamos la alerta roja restrictiva institucional
                $data['prog_especial'] = '<h1><font color="red"><b>🚨 RESTRICCIÓN: DEBE SELECCIONAR UNIDAD RESPONSABLE EN LA GRILA MAESTRA ANTES DE ASIGNAR INSUMOS V5 !!!!!</b></font></h1>';
                
                if ($uni_resp_id > 0) {
                    $unidad = $this->model_componente->get_componente($uni_resp_id, $this->gestion);
                    
                    if (!empty($unidad) && count($unidad) > 0) {
                        $data['prog_especial'] = '<h1><font color="blue">UNIDAD RESPONSABLE : <b>' . $unidad[0]['tipo_subactividad'] . ' ' . $unidad[0]['serv_descripcion'] . '</b></font></h1>';
                    }
                }
            }
            
            // 5. Carga de clasificadores nacionales de partidas presupuestarias
            $data['part_padres'] = $this->model_partidas->lista_padres(); // Partidas padres (Agrupadores)
            $data['part_hijos']  = $this->model_partidas->lista_partidas(); // Partidas hijos (Sub-ítems)
            
            // 6. Carga de requerimientos vigentes asignados
            $data['requerimientos'] = $this->mis_requerimientos($get_producto, $data['componente']); 
            
            // Inyectamos el botón de validación de techos del Formulario N° 5
            $data['button_form5'] = $this->programacionpoa->button_form5($com_id_actual);

            // 7. Despachamos el pool de datos a la vista unificada de SmartAdmin de la CNS
            $this->load->view('admin/programacion/requerimiento/list_requerimientos', $data);

        } else {
            // Protección forense por si fuerzan en la URL un ID de producto que ya fue borrado
            show_error("🚨 Error SIIPLAS: La Actividad física solicitada no existe en PostgreSQL o fue purgada del Formulario N° 4.");
        }
    }



    /*--------- VALIDA ADD REQUERIMIENTO ----------*/
    //  public function valida_insumo(){
    //   if($this->input->post()) {
    //     $post = $this->input->post();
    //     $prod_id = $this->security->xss_clean($post['prod_id']); /// prod
    //     $detalle = $this->security->xss_clean($post['ins_detalle']); /// detalle
    //     $cantidad = $this->security->xss_clean($post['ins_cantidad']); /// cantidad
    //     $costo_unitario = $this->security->xss_clean($post['ins_costo_u']); /// costo unitario
    //     $costo_total = $this->security->xss_clean($post['costo']); /// costo Total
    //     $um_id = $this->security->xss_clean($post['um_id']); /// Unidad de medida
    //     $partida = $this->security->xss_clean($post['partida_id']); /// costo unitario
    //     $observacion = $this->security->xss_clean($post['ins_observacion']); /// Observacion

    //     $producto=$this->model_producto->get_producto_id($prod_id); // Producto
    //     $componente=$this->model_componente->get_componente($producto[0]['com_id'],$this->gestion); // Componente
    //     $proyecto = $this->model_proyecto->get_id_proyecto($componente[0]['proy_id']); /// DATOS DEL PROYECTO
        
    //     $umedida=$this->model_insumo->get_unidadmedida($um_id);

    //       $query=$this->db->query('set datestyle to DMY');
    //       $data_to_store = array( 
    //       'ins_codigo' => $this->session->userdata("name").'/REQ/'.$this->gestion, /// Codigo Insumo
    //       'ins_fecha_requerimiento' => date('d/m/Y'), /// Fecha de Requerimiento
    //       'ins_detalle' => strtoupper($detalle), /// Insumo Detalle
    //       'ins_cant_requerida' => round($cantidad,0), /// Cantidad Requerida
    //       'ins_costo_unitario' => $costo_unitario, /// Costo Unitario
    //       'ins_costo_total' => $costo_total, /// Costo Total
    //       'ins_unidad_medida' => $umedida[0]['um_descripcion'], /// Insumo Unidad de Medida
    //       'ins_gestion' => $this->gestion, /// Insumo gestion
    //       'par_id' => $partida, /// Partidas
    //       'ins_tipo' => 1, /// Ins Tipo
    //       'ins_observacion' => strtoupper($observacion), /// Observacion
    //       'fun_id' => $this->fun_id, /// Funcionario
    //       'aper_id' => $proyecto[0]['aper_id'], /// aper id
    //       'com_id' => $producto[0]['com_id'], /// com id 
    //       'form4_cod' => $producto[0]['prod_cod'], /// aper id
    //       'num_ip' => $this->input->ip_address(), 
    //       'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
    //       );
    //       $this->db->insert('insumos', $data_to_store); ///// Guardar en Tabla Insumos 
    //       $ins_id=$this->db->insert_id();

    //       /*--------------------------------------------------------*/
    //         $data_to_store2 = array( ///// Tabla InsumoProducto
    //             'prod_id' => $prod_id, /// prod id
    //             'ins_id' => $ins_id, /// ins_id
    //             'tp_ins' => $proyecto[0]['tp_id'], /// tp id                
    //           );
    //           $this->db->insert('_insumoproducto', $data_to_store2);
    //         /*----------------------------------------------------------*/
          

    //         /*------------ PARA LA GESTION 2020 ---------*/
    //         for ($i=1; $i <=12 ; $i++) {
    //           $pfin=$this->security->xss_clean($post['m'.$i]);
    //           if($pfin!=0){
    //               $data_to_store4 = array( 
    //                 'ins_id' => $ins_id, /// Id Insumo
    //                 'mes_id' => $i, /// Mes 
    //                 'ipm_fis' => $pfin, /// Valor mes
    //                 'g_id' => $this->gestion, /// Gestion
    //                 );
    //               $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
    //           }
    //         }

    //       $get_ins=$this->model_insumo->get_insumo_producto($ins_id);
    //         if(count($get_ins)==1){
    //           $this->session->set_flashdata('success','EL REQUERIMIENTO SE REGISTRO CORRECTAMENTE :)');
    //         }
    //         else{
    //           $this->session->set_flashdata('danger','EL REQUERIMIENTO NOSE REGISTRO CORRECTAMENTE, VERIFIQUE DATOS :(');
    //         }

    //     redirect(site_url("").'/prog/requerimiento/'.$prod_id.'');
            
    //   } else {
    //       show_404();
    //   }
    // }

    /*--- VALIDA UPDATE REQUERIMIENTO A NIVEL DE OPERACIONES ---*/
     public function valida_update_insumo(){
      if($this->input->post()) {
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']); /// Ins id
        $detalle = $this->security->xss_clean($post['detalle']); /// detalle
        $cantidad = $this->security->xss_clean($post['cantidad']); /// cantidad
        $costo_unitario = $this->security->xss_clean($post['costou']); /// costo unitario
        $costo_total = $this->security->xss_clean($post['costot']); /// costo Total
        $umedida = $this->security->xss_clean($post['iumedida']); /// Unidad de medida
        $partida = $this->security->xss_clean($post['par_hijo']); /// costo unitario
        $observacion = $this->security->xss_clean($post['observacion']); /// Observacion

        $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos requerimientos productos
        $producto=$this->model_producto->get_producto_id($insumo[0]['prod_id']); /// Get producto
        $componente = $this->model_componente->get_componente($producto[0]['com_id'],$this->gestion); /// Get Componente
        $proyecto = $this->model_proyecto->get_id_proyecto($componente[0]['proy_id']); ////// DATOS DEL PROYECTO

      
        /*------------ UPDATE REQUERIMIENTO -------*/
          $update_ins= array(
            'ins_cant_requerida' => $cantidad,
            'ins_costo_unitario' => $costo_unitario,
            'ins_costo_total' => $costo_total,
            'ins_detalle' => strtoupper($detalle),
            'par_id' => $partida, /// Partidas
            'ins_unidad_medida' => strtoupper($umedida),
            'ins_observacion' => strtoupper($observacion),
            'fun_id' => $this->fun_id,
            'com_id' => $producto[0]['com_id'], /// com id 
            'form4_cod' => $producto[0]['prod_cod'], /// aper id
            'ins_estado' => 2,
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
          );
          $this->db->where('ins_id', $ins_id);
          $this->db->update('insumos', $this->security->xss_clean($update_ins));
        /*-----------------------------------------*/

        /*-------- DELETE INSUMO PROGRAMADO --------*/  
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('temporalidad_prog_insumo');
          /*------------------------------------------*/ 

          for ($i=1; $i <=12 ; $i++) {
            $pfin=$this->security->xss_clean($post['mm'.$i]);
            if($pfin!=0){
              $data_to_store4 = array( 
                'ins_id' => $ins_id, /// Id Insumo
                'mes_id' => $i, /// Mes 
                'ipm_fis' => $pfin, /// Valor mes
                'g_id' => $this->gestion, /// Gestion
                );
              $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
            }
          }

        $this->session->set_flashdata('success','EL REQUERIMIENTO SE MODIFICO CORRECTAMENTE :)');
        redirect(site_url("").'/prog/requerimiento/'.$producto[0]['prod_id']);

      } else {
          show_404();
      }
    }


    //// Modal de Migracion de requerimientos
/*     public function modal_migracion_form5x_actividad2($producto, $componente){
        $prog_especial = '';
        $bloquear_formulario = false;
            
        $tabla = '';
        // Inyectamos el fondo carbón oscuro y el desenfoque elástico de SmartAdmin
        $tabla .= '
        <div class="modal fade" id="modal_importar_f5" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
            <div class="modal-dialog" id="dialog_subirr">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); border: none; overflow: hidden;">
                    
                    <!-- CABECERA DEL COMPONENTE -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                            <i class="fa fa-upload text-primary"></i> Importar Requerimientos x Actividad
                        </h4>
                    </div>

                    <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                    <div class="modal-body" style="padding: 25px; background: #ffffff;">
                        
                        <!-- 🌟 INYECCIÓN DEL CANDADO DE AUDITORÍA: Alerta o Banner Informativo -->
                        ' . $prog_especial . '

                        <!-- Título e Instrucción -->
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Requerimientos x Actividad (.xls, .xlsx)</h5>
                            <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                        </div>

                        <!-- Vista previa de columnas -->
                        <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                            <div style="color:blue; font-weight:bold; font-size:11px;">CÓD. ACT.: <span style="color:#334155; font-size:11.5px;">' . round($producto[0]['prod_cod'], 2) . '.- ' . strtoupper($producto[0]['prod_producto']) . '</span></div><br>
                            <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                        </div>

                        <form action="' . site_url('programacion/crequerimiento/valida_migracion_form5_x_actividad') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="prod_id" value="' . $producto[0]['prod_id'] . '" type="text" > 
                                <input type="text" name="base" value="'.base_url().'">
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo_f5" accept=".xlsx, .xls" name="archivo_f5" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje_f5" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir_f5" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- 🛠️ COMPLETADO: Cierre simétrico y limpio del Pre-Loader animado institucional -->
                                <div id="loads_f5" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>';

        return $tabla;
    }*/


    public function modal_migracion_form5x_actividad2($producto, $componente){
        $prog_especial = '';
        $bloquear_formulario = false;
            
        // 🛠️ REPARADO: Evaluación elástica de la matriz de la Unidad Organizacional
        if (!empty($componente) && isset($componente[0]['por_id']) && intval($componente[0]['por_id']) == 1) {
            $uni_resp_id = intval($producto[0]['uni_resp']);
            
            // Mensaje corporativo SmartAdmin de bloqueo elástico si uni_resp es 0
            $prog_especial = '
                <div class="alert alert-danger text-center" style="margin-bottom: 20px; border-left: 5px solid #ef4444; background: #fef2f2; color: #991b1b; padding: 12px; font-size:12px; font-weight:bold;">
                    <i class="fa fa-exclamation-triangle fa-2x" style="margin-bottom:5px; display:block;"></i>
                    🚨 RESTRICCIÓN DE FORMULACIÓN: DEBE SELECCIONAR LA UNIDAD RESPONSABLE EN LA GRILLA PRINCIPAL (FORM 4) ANTES DE PODER CARGAR O ASIGNAR INSUMOS.
                </div>';
            
            $bloquear_formulario = true; // Gatilla el conmutador de bloqueo de red
            
            if ($uni_resp_id > 0) {
                $unidad = $this->model_componente->get_componente($uni_resp_id, $this->gestion);
                
                if (!empty($unidad) && count($unidad) > 0) {
                    // Si ya tiene asignación, muestra un banner azul informativo limpio
                    $prog_especial = '
                        <div class="alert alert-info" style="margin-bottom: 20px; border-left: 5px solid #3b82f6; background: #eff6ff; color: #1e3a8a; padding: 10px; font-size:11.5px; font-weight:600;">
                            <i class="fa fa-info-circle"></i> UNIDAD RESPONSABLE VINCULADA: ' . strtoupper($unidad[0]['tipo_subactividad'] . ' ' . $unidad[0]['serv_descripcion']) . '
                        </div>';
                    $bloquear_formulario = false; // Libera el formulario
                }
            }
        }

        $tabla = '';
        // Inyectamos el fondo carbón oscuro y el desenfoque elástico de SmartAdmin
        $tabla .= '
        <div class="modal fade" id="modal_importar_f5" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
            <div class="modal-dialog" id="dialog_subirr">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); border: none; overflow: hidden;">
                    
                    <!-- CABECERA DEL COMPONENTE -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                            <i class="fa fa-upload text-primary"></i> Importar Requerimientos x Actividad
                        </h4>
                    </div>

                    <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                    <div class="modal-body" style="padding: 25px; background: #ffffff;">
                        
                        <!-- 🌟 INYECCIÓN DEL CANDADO DE AUDITORÍA: Alerta o Banner Informativo -->
                        ' . $prog_especial . '

                        <!-- Título e Instrucción -->
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Requerimientos x Actividad (.xls, .xlsx)</h5>
                            <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                        </div>

                        <!-- Vista previa de columnas -->
                        <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                            <div style="color:blue; font-weight:bold; font-size:11px;">CÓD. ACT.: <span style="color:#334155; font-size:11.5px;">' . round($producto[0]['prod_cod'], 2) . '.- ' . strtoupper($producto[0]['prod_producto']) . '</span></div><br>
                            <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                        </div>';

                        // 📋 REGLA DE BLOQUEO: Ocultamos el formulario si la unidad obligatoria no fue seleccionada
                        if ($bloquear_formulario === false) {
                            $tabla .= '
                            <!-- Formulario de persistencia binaria -->
                            <!-- 🛠️ REPARADO: Sincronizada la URL de acción exacta hacia el controlador unificado de productos -->
                            <form action="' . site_url('programacion/crequerimiento/valida_migracion_form5_x_actividad') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="prod_id" value="' . $producto[0]['prod_id'] . '" type="text" > 
                                <input type="text" name="base" value="'.base_url().'">
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo_f5" accept=".xlsx, .xls" name="archivo_f5" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje_f5" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir_f5" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- 🛠️ COMPLETADO: Cierre simétrico y limpio del Pre-Loader animado institucional -->
                                <div id="loads_f5" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>';
                        } else {
                            // Si está bloqueado por auditoría, inyectamos un botón deshabilitado inactivo de advertencia
                            $tabla .= '
                            <button type="button" class="btn btn-default btn-block" disabled style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; text-transform: uppercase; background:#f1f5f9; color:#94a3b8; border-color:#e2e8f0; cursor:not-allowed;">
                                <i class="fa fa-lock"></i> Importación bloqueada por Auditoría
                            </button>';
                        }
                        
                    $tabla .= '
                    </div>
                </div>
            </div>
        </div>';

        return $tabla;
    }

    /*----------- LISTA DE REQUERIMIENTOS (2020) (A optimizar) --------------*/
    public function mis_requerimientos($producto,$componente){
      $lista_insumos = $this->model_insumo->lista_insumos_prod($producto[0]['prod_id']);
      $tabla='';
      $total=0;
      $tabla.='
      <input type="hidden" name="prod_id" id="prod_id" value="'.$producto[0]['prod_id'].'">
      <input type="hidden" name="base" value="'.base_url().'">
      <table id="dt_basic" class="table table table-bordered" width="100%">
          <thead>
            <tr class="modo1">
              <th></th>
              <th>PARTIDA</th>
              <th>DETALLE REQUERIMIENTO</th>
              <th>UNIDAD</th>
              <th>CANTIDAD</th>
              <th>UNITARIO</th>
              <th>TOTAL</th>
              <th style="background-color: #0AA699;color: #FFFFFF">ENE.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">FEB.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">MAR.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">ABR.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">MAY.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">JUN.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">JUL.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">AGO.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">SEPT.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">OCT.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">NOV.</th>
              <th style="background-color: #0AA699;color: #FFFFFF">DIC.</th>
              <th>OBSERVACIONES</th>
              <th>ELIMINAR</th>
              <th>COD. ACT.</th>
            </tr>
          </thead>
          <tbody>';
          $cont = 0;
          foreach ($lista_insumos as $row) {
            $color='';
            $ins_cert=0;
            if($row['ins_monto_certificado']!=0){
              $ins_cert=1;
            }     
            $cont++;
            $tabla .= '<tr class="modo1" bgcolor="'.$color.'" title='.$row['ins_id'].'>';
              $tabla .= '<td align="center">';
              if($this->tp_adm==1 || $this->conf_form5==1){
                if($ins_cert==0){
                  $tabla.='
                  <a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn-default mod_ff" name="'.$row['ins_id'].'" title="MODIFICAR REQUERIMIENTO" ><img src="'.base_url().'assets/ifinal/modificar.png" WIDTH="35" HEIGHT="35"/></a><br>
                  <a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn-default del_ff" title="ELIMINAR REQUERIMIENTO"  name="'.$row['ins_id'].'"><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a>';
                }
                else{
                  $tabla.='<font color=red><b>CERTIFICADO</b></font>';
                }
                
              }
              else{
                $tabla.=''.$cont.'';
              }
              $tabla .='</td>';
              $tabla .='<td>'.$row['par_codigo'].'</td>'; /// partida
              $tabla .= '<td>'.$row['ins_detalle'].'</td>'; /// detalle requerimiento
              $tabla .= '<td>'.$row['ins_unidad_medida'].'</td>'; /// Unidad
              $tabla .= '<td>'.$row['ins_cant_requerida'].'</td>'; /// cantidad
              $tabla .= '<td>'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
              $tabla .= '<td>'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
              for ($i=1; $i <=12 ; $i++) { 
                $tabla.='<td>0</td>';
              }

              $tabla .= '<td>'.$row['ins_observacion'].'</td>
              <td></td>
              <td></td>
            </tr>';
            $total=$total+$row['ins_costo_total'];
          }
          $tabla.='
          </tbody>
            <tr class="modo1">
              <td colspan="6"> TOTAL </td>
              <td><font color="blue" size=1>'.number_format($total, 2, ',', '.') .'</font></td>
              <td colspan="15"></td>
            </tr>
        </table>';

        $tabla.='
        <div class="modal fade" id="modal_importar_f5" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog" style="background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
            <div class="modal-dialog" id="dialog_subirr">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 10px 10px -5px rgba(0, 0, 0, 0.2); border: none; overflow: hidden;">
                    <!-- CABECERA DEL COMPONENTE -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                        <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                            <i class="fa fa-upload text-primary"></i> Importar Requerimientos x Actividad
                        </h4>
                    </div>

                    <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                    <div class="modal-body" style="padding: 25px; background: #ffffff;">
                        <!-- Título e Instrucción -->
                        <div class="text-center" style="margin-bottom: 20px;">
                            <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Requerimientos x Actividad (.xls, .xlsx)</h5>
                            <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                        </div>

                        <!-- Vista previa de columnas -->
                        <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                            <div style="color:blue; font-weight:bold; font-size:11px;">CÓD. ACT.: <span style="color:#334155; font-size:11.5px;">' . round($producto[0]['prod_cod'], 2) . '.- ' . strtoupper($producto[0]['prod_producto']) . '</span></div><br>
                            <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                        </div>

                        <form action="' . site_url('programacion/crequerimiento/valida_migracion_form5_x_actividad') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientos" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="prod_id" value="' . $producto[0]['prod_id'] . '" type="text" > 
                                <input type="text" name="base" value="'.base_url().'">
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo_f5" accept=".xlsx, .xls" name="archivo_f5" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje_f5" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir_f5" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- 🛠️ COMPLETADO: Cierre simétrico y limpio del Pre-Loader animado institucional -->
                                <div id="loads_f5" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>';

      return $tabla;
    }


    /*--- ELIMINAR TODOS LOS REQUERIMIENTOS DE LA OPERACION/ACTIVIDAD ---*/
    // function eliminar_todos_insumos($prod_id){
    //   $insumos = $this->model_insumo->lista_insumos_prod($prod_id); //// Insumos Operacion

    //   foreach ($insumos as $row) {
    //     /*-------- DELETE INSUMO PROGRAMADO --------*/  
    //       $this->db->where('ins_id', $row['ins_id']);
    //       $this->db->delete('temporalidad_prog_insumo');
    //     /*------------------------------------------*/

    //     /*-------- DELETE INSUMO --------*/
    //       $this->db->where('prod_id', $prod_id);
    //       $this->db->where('ins_id', $row['ins_id']);
    //       $this->db->delete('_insumoproducto');
    //       /*--------------------------------*/

    //     /*-------- DELETE INSUMO  --------*/  
    //       $this->db->where('ins_id', $row['ins_id']);
    //       $this->db->delete('insumos');
    //     /*--------------------------------*/
    //   }
      
    //   redirect(site_url("").'/prog/requerimiento/'.$prod_id.'');    
    // }
   
   /// ==== MIGRACION EXCEL DE REQUERIMIENTOS x ACTIVIDAD - Formulario N° 5 / 2027
public function valida_migracion_form5_x_actividad() {
        // Elevamos los recursos básicos de hardware por si la plantilla es pesada
        if (function_exists('ini_set')) {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M'); 
        }
        
        $this->load->library('excel'); 
        
        // 1. Captura del parámetro de texto enviado por form5.js
        $prod_id = $this->input->post('prod_id');
        $get_form4 = $this->model_producto->get_producto_id(floatval($prod_id));

        // 2. 🌟 AUDITORÍA FORENSE: Inspección del archivo en el búfer temporal de Apache
        $archivo_detectado = "❌ NO SE DETECTÓ NINGÚN ARCHIVO EN EL SERVIDOR (BÚFER VACÍO)";
        $nombre_original   = "---";
        $tamano_bytes      = 0;
        $tipo_mime         = "---";

        if (isset($_FILES['archivo_f5']) && !empty($_FILES['archivo_f5']['tmp_name'])) {
            $archivo_detectado = "✔ ¡ÉXITO! ARCHIVO RECIBIDO CORRECTAMENTE EN EL SERVIDOR";
            $nombre_original   = $_FILES['archivo_f5']['name'];
            $tamano_bytes      = $_FILES['archivo_f5']['size'];
            $tipo_mime         = $_FILES['archivo_f5']['type'];
        }

        // 🌟 PASO 1: Limpieza absoluta de cualquier HTML previo en la memoria de CodeIgniter
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 🌟 PASO 2: Forzar estrictamente al servidor a declarar la salida como JSON puro
        // 🛠️ REPARADO: Descomentado obligatoriamente para extinguir el error Unexpected token '<'
        header('Content-Type: application/json; charset=utf-8');

        // Sincronizamos los índices para que form5.js entre de forma controlada al bloque de inconsistencias
        $respuesta_prueba = array(
            'status'    => 'error',
            'respuesta' => 'error', 
            'mensaje'   => '📊 SIIPLAS v2.0 - RADAR DE COMUNICACIÓN BINARIA ASÍNCRONA',
            'errors'    => array(
                "Estatus del Archivo: " . $archivo_detectado,
                "Nombre del documento: " . $nombre_original,
                "Tamaño en el Servidor: " . number_format(($tamano_bytes / 1024), 2) . " KB (" . $tamano_bytes . " bytes)",
                "Tipo de archivo (MIME): " . $tipo_mime,
                "ID de Actividad recibido en POST: (" . ($prod_id ? $prod_id : 'VACÍO / NO ENVIADO') . ")",
                "Verificación en Base de Datos: " . (empty($get_form4) ? '❌ ACTIVIDAD NO ENCONTRADA EN _PRODUCTOS' : '✔ ACTIVIDAD LOCALIZADA CON ÉXITO')
            )
        );

        // 🌟 PASO 3: Imprimir el JSON y congelar la ejecución para que no se filtre ni una etiqueta HTML
        echo json_encode($respuesta_prueba);
        exit; 
    }

      public function valida_migracion_form5_x_actividad3() {
        ini_set('max_execution_time', 900); // 15 minutos máximos de procesamiento de CPU
        ini_set('memory_limit', '3072M'); 
        $this->load->library('excel'); 
        $prod_id = $this->input->post('prod_id');
        $get_form4 = $this->model_producto->get_producto_id($prod_id);

        if (empty($get_form4) || count($get_form4) == 0) {
            echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información de la Actividad. Verifique su sesión.')));
            return;
        }

        $get_unidad=$this->model_componente->get_componente($get_form4[0]['com_id'],$this->gestion);
        if (!isset($_FILES['archivo_f5']) || empty($_FILES['archivo_f5']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        $archivo = $_FILES['archivo_f5']['tmp_name'];
        $errores = array();
        $data_insertar = array();

        try {
            $archivoTipo = PHPExcel_IOFactory::identify($archivo);
            $lector      = PHPExcel_IOFactory::createReader($archivoTipo);
            
            // OPTIMIZACIÓN DE MEMORIA: Ignoramos estilos gráficos pesados para no colapsar la RAM
            $lector->setReadDataOnly(true);
            
            $phpExcel    = $lector->load($archivo);
            $hoja        = $phpExcel->getSheet(0);
            $filasMax    = $hoja->getHighestRow();
            
            // --- 1. VALIDACIÓN DE ESTRUCTURA METRICA (Columnas Max V = 22) ---
            $columnaMaxLetra = $hoja->getHighestDataColumn(); 
            $totalColumnas   = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
            $limitePermitido = 20; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial estructurado exige exactamente $limitePermitido columnas (Hasta la 'T').")));
                return;
            }

            // --- 2. VALIDACIÓN FILA POR FILA ---
           for ($i = 2; $i <= $filasMax; $i++) {
                $par_id  = 0;
                // Extraer valores básicos de la fila activa según la imagen enviada
                $cod_act       = trim($hoja->getCell('A' . $i)->getValue());
                $partida       = trim($hoja->getCell('B' . $i)->getValue());
                $requerimiento = trim($hoja->getCell('C' . $i)->getValue());
                $unidad_medida = trim($hoja->getCell('D' . $i)->getValue());
                
                $cantidad_raw  = $hoja->getCell('E' . $i)->getCalculatedValue();
                $precio_raw    = $hoja->getCell('F' . $i)->getCalculatedValue();
                $total_raw     = $hoja->getCell('G' . $i)->getCalculatedValue();
                $observacion   = trim($hoja->getCell('T' . $i)->getValue());

                if (empty($cod_act) && empty($partida) && empty($requerimiento) && (empty($total_raw) || floatval($total_raw) == 0)) {
                    $errores[] = "🚨 RECHAZO DE PLANILLA: Se detectó que la Fila N° $i está completamente vacía o contiene residuos de formato invisible de Excel. Por favor, abra su archivo Excel, seleccione la Fila $i completa (haciendo clic en el número de la fila a la izquierda), haga clic derecho y elija la opción 'Eliminar' para purgar la planilla antes de reintentar la subida.";
                    break; 
                }

                // 📋 REGLA 1: VALIDACIÓN DE CANTIDAD ENTERA (Sin decimales)
                if ($cantidad_raw === NULL || trim($cantidad_raw) === '' || !is_numeric($cantidad_raw)) {
                    $errores[] = "Fila $i: La 'CANTIDAD' es obligatoria y debe ser numérica.";
                } else {
                    $cantidad_float = floatval($cantidad_raw);
                    if ($cantidad_float != floor($cantidad_float)) {
                        $errores[] = "Fila $i: Restricción contable -> La 'CANTIDAD' ($cantidad_raw) debe ser un número entero puro, sin decimales.";
                    }
                }
                $cantidad = intval($cantidad_raw);
                // 📋 REGLA 2: VALIDACIÓN DE PRECIO UNITARIO (Máximo 2 decimales)
                if ($precio_raw === NULL || trim($precio_raw) === '' || !is_numeric($precio_raw)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' es obligatorio y debe ser numérico.";
                } else {
                    $precio_float = floatval($precio_raw);
                    if (round($precio_float, 2) != $precio_float) {
                        $errores[] = "Fila $i: El 'PRECIO UNITARIO' ($precio_raw) excede el límite. Solo se aceptan hasta 2 decimales (Ej: 2500.00).";
                    }
                }
                $precio = round(floatval($precio_raw), 2);
                // 📋 REGLA 3: VALIDACIÓN DEL COSTO TOTAL MATEMÁTICO (Cantidad * Precio)
                $total_calculado = round(($cantidad * $precio), 2);
                $total_archivo   = round(floatval($total_raw), 2);

                if (abs($total_archivo - $total_calculado) > 0.05) {
                    $errores[] = "Fila $i: El 'PRECIO TOTAL' registrado ($total_raw) no coincide con la ecuación aritmética (Cantidad: $cantidad * Precio: $precio = $total_calculado).";
                }

                // Validación de Partida
                if (!empty($partida)) {
                    if (strlen($partida) != 5) {
                        $errores[] = "Fila $i: La 'PARTIDA' ($partida) debe tener exactamente 5 caracteres.";
                    } else {
                        $get_partida = $this->model_partidas->dato_par_codigo($partida);
                        if (!empty($get_partida) && count($get_partida) == 1) {
                            $par_id = $get_partida[0]['par_id'];
                        } else {
                            $errores[] = "Fila $i: La partida contable ($partida) no existe en el clasificador de la base de datos.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: La 'PARTIDA' es obligatoria.";
                }

                // 📋 REGLA 4: VALIDACIÓN MÁSTER Y RESOLUCIÓN DE FÓRMULAS EN LOS 12 MESES (H hasta la S)
                $suma_meses = 0;
                $columnas_meses = array('H' => 1,'I' => 2,'J' => 3,'K' => 4,'L' => 5,'M' => 6,'N' => 7,'O' => 8,'P' => 9,'Q' => 10,'R' => 11,'S' => 12);
                $meses_valores = array();
                
                foreach ($columnas_meses as $col => $mes_nro) {
                    // 🛠️ REPARADO: getCalculatedValue() resuelve la fórmula de Excel (ej: =SUMA(), =5000/12) y extrae el resultado numérico puro
                    $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                    $val_mes     = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);
                    
                    if (!is_numeric($val_mes)) {
                        $errores[] = "Fila $i: Valor o fórmula no numérica detectada en la columna del mes '$col'.";
                        break;
                    }
                    
                    $monto_mes = round(floatval($val_mes), 2);
                    $suma_meses += $monto_mes;
                    $meses_valores[$mes_nro] = $monto_mes; 
                }

                // 📋 REGLA 5: COMPROBACIÓN DE COINCIDENCIA (Suma de meses == Costo Total)
                if (abs($suma_meses - $total_archivo) > 0.05) { 
                    $errores[] = "Fila $i: La suma de la distribución mensual ($suma_meses) no cuadra con el PRECIO TOTAL ($total_archivo) de la celda G.";
                }

                if (empty($errores)) {
                    $data_insertar[] = array(
                      'maestro' => array(
                          'ins_codigo'              => $this->session->userdata("name") . '/REQ/' . $this->gestion,
                          'ins_fecha_requerimiento' => date('Y-m-d'), 
                          'par_id'                  => $par_id,
                          'ins_detalle'             => strtoupper($this->security->xss_clean($requerimiento)),
                          'ins_unidad_medida'       => strtoupper($this->security->xss_clean($unidad_medida)),
                          'ins_cant_requerida'      => $cantidad,
                          'ins_costo_unitario'      => $precio,
                          'ins_costo_total'         => $total_archivo,
                          'ins_observacion'         => strtoupper($this->security->xss_clean($observacion)),
                          'fun_id'                  => $this->fun_id,
                          'aper_id'                 => $get_unidad[0]['aper_id'], 
                          'com_id'                  => $get_unidad[0]['com_id'], 
                          'form4_cod'               => $get_form4[0]['prod_cod'], 
                          'ins_mod'                 => 1, // Conmutador de registro insertado
                          'num_ip'                  => $this->input->ip_address(), 
                          'nom_ip'                  => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                      ),
                      'meses' => $meses_valores // Array indexado del 1 al 12 resuelto por fórmulas
                    );
                }
            }

            if (empty($errores) && count($data_insertar) > 0) {
                
                // Levantamos los muros de control transaccional para aislar fallas de presupuesto
                $this->db->trans_start(); 
                $filas_insertadas_conteo = 0;

                foreach ($data_insertar as $registro) {
                    
                    // 🛠️ REPARADO: Se inserta únicamente la estructura plana del sub-arreglo 'maestro'
                    $this->db->insert('insumos', $registro['maestro']);
                    
                    // Recuperamos el ID autogenerado asignado por la secuencia en Postgres
                    $ins_id = $this->db->insert_id();

                    /*-----------------------------------------------*/
                    // B. Registro de la alineación relacional en la tabla _insumoproducto
                    $data_to_store2 = array(
                        'prod_id' => $prod_id, // Variable física relacional obtenida en la validación
                        'ins_id'  => $ins_id
                    );
                    $this->db->insert('_insumoproducto', $data_to_store2);
                    /*---------------------------------------------*/
                    
                    /*------------ REGISTRO DE LA TEMPORALIDAD ---------*/
                    // 🛠️ REPARADO: Se recorre la colección real 'meses' usando $m_id para no pisar el iterador superior $i
                    for ($m_id = 1; $m_id <= 12; $m_id++) {
                        $pfin = isset($registro['meses'][$m_id]) ? $registro['meses'][$m_id] : 0;
                        
                        if ($pfin != 0) {
                            $data_to_store4 = array( 
                                'ins_id'  => $ins_id,          // Id Insumo maestro correlativo
                                'mes_id'  => $m_id,            // Mes dinámico (1 al 12)
                                'ipm_fis' => $pfin,            // Valor físico financiero del mes resuelto
                                'g_id'    => $this->gestion,   // Gestión POA activa de sesión
                            );
                            $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                        }
                    }
                    
                    $filas_insertadas_conteo++;
                }

                // Cerramos e indicamos a CodeIgniter que evalúe el estatus de las inserciones
                $this->db->trans_complete();

                // Si PostgreSQL detecta un desbordamiento numérico o violación de tope, aplica Rollback total
                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array(
                        'status'    => 'error', 
                        'respuesta' => 'error', 
                        'mensaje'   => 'PostgreSQL rechazó las restricciones físicas o techos de los requerimientos. Matriz revertida de forma íntegra.'
                    ));
                    return;
                }

                // 🌟 ÉXITO ABSOLUTO: Despachamos el payload esperado por tu $.ajax en form4.js
                echo json_encode(array(
                    'status'           => 'success',
                    'respuesta'        => 'correcto',
                    'mensaje'          => '¡Matriz de requerimientos contables consolidados e inyectados en el sistema de forma exitosa!',
                    'filas_procesadas' => $filas_insertadas_conteo
                ));

            } else {
                // Si la colección de errores contiene advertencias estructurales, frena e informa al usuario
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'mensaje'   => 'Se detectaron observaciones de validación en la estructura o coincidencia de la plantilla.',
                    'errores'   => !empty($errores) ? $errores : array("No se encontraron registros consistentes para migrar.")
                ));
            }

        } catch (Exception $e) {
            // Captura forense de desbordamientos de memoria del motor de PHPExcel
            echo json_encode(array(
                'status'    => 'error', 
                'respuesta' => 'error', 
                'mensaje'   => 'Falla crítica del lector de planillas: ' . $e->getMessage()
            ));
        }
    }

    /*------ CAMBIA ALINEACION A ACTIVIDAD 2022---------*/
    function cambia_actividad(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('prod_id', 'id producto', 'required|trim');
          $this->form_validation->set_message('required', 'El campo es es obligatorio');
        
          $post = $this->input->post();
          $prod_id= $this->security->xss_clean($post['prod_id']);
          $ins_id= $this->security->xss_clean($post['ins_id']);
           
          $update_proy = array(
            'prod_id' => $prod_id,
          );
          $this->db->where('ins_id', $ins_id);
          $this->db->update('_insumoproducto', $update_proy);
              
      }else{
          show_404();
      }
    }

    /*---- GET DATOS REQUERIMIENTO (Vigente)-----*/
    public function get_requerimiento(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']);
        $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos requerimientos productos
        $producto=$this->model_producto->get_producto_id($insumo[0]['prod_id']); /// Get producto
        $componente = $this->model_componente->get_componente($producto[0]['com_id'],$this->gestion); /// Get Componente
        $proyecto = $this->model_proyecto->get_id_proyecto($componente[0]['proy_id']); ////// DATOS DEL PROYECTO

        $monto_asig=$this->model_ptto_sigep->suma_ptto_accion($proyecto[0]['aper_id'],1);
        $monto_prog=$this->model_ptto_sigep->suma_ptto_accion($proyecto[0]['aper_id'],2);
        

        $m_asig=0;$m_prog=0;
        if(count($monto_asig)!=0){
          $m_asig=$monto_asig[0]['monto'];
        }
        if(count($monto_prog)!=0){
          $m_prog=$monto_prog[0]['monto'];
        }

        $saldo=($m_asig-$m_prog);
        
        $par_padre=$this->model_partidas->get_partida_padre($insumo[0]['par_depende']); /// lista de partidas padres
        $lista_partidas=$this->programacionpoa->partidas_dependientes($insumo); /// Lista de Insumos dependientes
        $temporalidad=$this->programacionpoa->distribucion_financiera($insumo); /// Distribucion Financiera
        $lista_umedida=$this->programacionpoa->unidades_medida($insumo); /// Lista de Unidad de medida

        if(count($insumo)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'insumo' => $insumo,
            'monto_saldo' => $saldo+$insumo[0]['ins_costo_total'],
            'lista_partidas'=> $lista_partidas,
            'lista_umedida'=> $lista_umedida,
            'ppdre' => $par_padre,
            'prog' => $temporalidad,
          );
        }
        else{
          $result = array(
            'respuesta' => 'error',
          );
        }
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }


    /*------ ELIMINAR GET REQUERIMIENTO ------*/
    function delete_get_requerimiento(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $ins_id = $this->security->xss_clean($post['ins_id']); // ins id

          /*-------- DELETE INSUMO PROGRAMADO --------*/  
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('temporalidad_prog_insumo');
          /*------------------------------------------*/

          /*---- DELETE INSUMO PRODUCTO ----*/  
            $this->db->where('ins_id', $ins_id);
            $this->db->delete('_insumoproducto');
          /*--------------------------------*/
          
          /*-------- DELETE INSUMO  --------*/  
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('insumos');
          /*--------------------------------*/

          $insumo=$this->model_insumo->get_requerimiento($ins_id);
          if(count($insumo)==0){
            $result = array(
              'respuesta' => 'correcto'
            );
          }
          else{
            $result = array(
              'respuesta' => 'error'
            );
          }
          
          echo json_encode($result);

      } else {
          echo 'DATOS ERRONEOS';
      }
    }

    /*---- CAMBIA EL ID DEL INSUMO Y LO LLEVA A INSUMOPRODUCTO ----*/
    function update_id_requerimientos_pi($proy_id){
      $productos=$this->model_producto->list_productos_proyecto($proy_id);
      foreach($productos as $rowp){
        //echo "prod_id : ".$rowp['prod_id']." - DESC. ".$rowp['prod_producto']."<br>";
          $lista_insumos=$this->model_producto->lista_insumos_por_producto($rowp['prod_id']);
          if(count($lista_insumos)!=0){
            foreach($lista_insumos as $rowi){
              //echo "ins_id : ".$rowi['ins_id']." - ".$rowi['ins_detalle']."<br>";
              //----- Inserrta el id insumo a insumoproducto
              $data_to_store = array( 
                'prod_id' => $rowp['prod_id'],
                'ins_id' => $rowi['ins_id'],
                'tp_ins' => 1,
              );
              $this->db->insert('_insumoproducto', $data_to_store);
              //--------------------------------------------

              //----- Elimina la relacion Insumoactividad
              $this->db->where('ins_id', $rowi['ins_id']);
              $this->db->where('act_id', $rowi['act_id']);
              $this->db->delete('_insumoactividad');
              //--------------------------------------------
            }
          }
          else{
            redirect('admin/proy/list_proy#tabs-a'); ///// Lista de Proyectos de Inversion
          }
      }

      redirect('admin/proy/list_proy#tabs-a'); ///// Lista de Proyectos de Inversion
    }



    /*--------- Lista Partidas Hijos -----------*/
    public function combo_partidas_hijos(){
      //echo "urbanizaciones";
      $salida = "";
      $id_pais = $_POST["elegido"];
      // construimos el combo de ciudades deacuerdo al pais seleccionado
      $combog = pg_query("SELECT * FROM partidas WHERE par_depende=$id_pais");
      $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE PARTIDA', 'cp1252', 'UTF-8') . "</option>";
      while ($sql_p = pg_fetch_row($combog)) {
          $salida .= "<option value='" . $sql_p[0] . "'>" .$sql_p[4]." - ".$sql_p[1] . "</option>";
      }
      echo $salida;
    }

    /*--------- Lista Unidades de Medida -----------*/
    public function combo_unidad_medida(){
      //echo "urbanizaciones";
      $salida = "";
      $par_id = $_POST["elegido"];
      // construimos el combo de ciudades deacuerdo al pais seleccionado
      $combog = pg_query('select *
              from par_umedida pum
              Inner Join insumo_unidadmedida as ium on ium.um_id = pum.um_id
              where pum.par_id='.$par_id.'
              order by ium.um_id asc');
      $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE UNIDAD DE MEDIDA', 'cp1252', 'UTF-8') . "</option>";
      while ($sql_p = pg_fetch_row($combog)) {
          $salida .= "<option value='" . $sql_p[3] . "'>" .$sql_p[4]. "</option>";
      }
      echo $salida;
    }

    /*--------- Lista Partidas Hijos Asignados-----------*/
    public function combo_partidas_hijos_asignados(){
        $salida = "";
        $id_pais = $_POST["elegido"]; /// codigo Partida
        $aper_id = $_POST["aper"]; /// aper id
        $tp=$_POST["tp"]; /// tp
        $id = $_POST["id"]; /// cite id | ins id

        if($tp==0){
          $cite=$this->model_modrequerimiento->get_cite_insumo($id); /// Datos cite
          $tipo_mod=$cite[0]['tipo_modificacion'];
        }
        else{
          $insumo= $this->model_insumo->get_requerimiento($id); /// Datos requerimientos productos
          $tipo_mod=$insumo[0]['ins_tipo_modificacion'];
        }
        

        if($tipo_mod==0){
          $combog = pg_query('
            select pg.par_id,pg.partida as par_codigo,p.par_nombre,p.par_depende,pg.importe
            from ptto_partidas_sigep pg
            Inner Join partidas as p On p.par_id=pg.par_id
            where pg.aper_id='.$aper_id.' and pg.estado!=\'3\' and pg.g_id='.$this->gestion.' and p.par_depende='.$id_pais.'
            order by pg.partida asc');
        }
        else{
          $combog = pg_query('
            select par.par_id,par.par_codigo,par.par_nombre,par.par_depende,SUM(pr.presupuesto_revertido) ppto_revertido
            from lista_partidas_revertidas('.$this->gestion.') pr
            Inner Join partidas as par On par.par_id=pr.par_id
            where pr.aper_id='.$aper_id.' and par.par_depende='.$id_pais.'
            group by par.par_id,par.par_codigo,par.par_nombre,par.par_depende
            order by par.par_codigo asc');
        }

        $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE PARTIDA', 'cp1252', 'UTF-8') . "</option>";
        while ($sql_p = pg_fetch_row($combog)) {
            $salida .= "<option value='" . $sql_p[0] . "'>" .$sql_p[1]." - ".$sql_p[2] . "</option>";
        }
        echo $salida;
    }






    function mes_nombre(){
        $mes[1] = 'ENE.';
        $mes[2] = 'FEB.';
        $mes[3] = 'MAR.';
        $mes[4] = 'ABR.';
        $mes[5] = 'MAY.';
        $mes[6] = 'JUN.';
        $mes[7] = 'JUL.';
        $mes[8] = 'AGOS.';
        $mes[9] = 'SEPT.';
        $mes[10] = 'OCT.';
        $mes[11] = 'NOV.';
        $mes[12] = 'DIC.';
        return $mes;
    }

    /*--------------- GENERA MENU -------------*/
    // public function genera_menu($proy_id){
    //   $id_f = $this->model_faseetapa->get_id_fase($proy_id);
    //   $enlaces=$this->menu_modelo->get_Modulos_programacion(2);
    //   $tabla='';
    //   $tabla.='<nav>
    //           <ul>
    //               <li>
    //                   <a href='.site_url("admin").'/dashboard'.' title="MENU PRINCIPAL"><i class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
    //               </li>
    //               <li class="text-center">
    //                   <a href='.base_url().'index.php/admin/proy/mis_proyectos/1'.' title="PROGRAMACI&Oacute;N POA"> <span class="menu-item-parent">PROGRAMACI&Oacute;N POA</span></a>
    //               </li>';
    //               if(count($id_f)!=0){
    //                   for($i=0;$i<count($enlaces);$i++){ 
    //                       $tabla.='
    //                       <li>
    //                           <a href="#" >
    //                               <i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>
    //                           <ul >';
    //                           $submenu= $this->menu_modelo->get_Modulos_sub($enlaces[$i]['o_child']);
    //                           foreach($submenu as $row) {
    //                              $tabla.='<li><a href='.base_url($row['o_url'])."/".$id_f[0]['proy_id'].'>'.$row['o_titulo'].'</a></li>';
    //                           }
    //                       $tabla.='</ul>
    //                       </li>';
    //                   }
    //               }
    //           $tabla.='
    //           </ul>
    //       </nav>';

    //   return $tabla;
    // }

}