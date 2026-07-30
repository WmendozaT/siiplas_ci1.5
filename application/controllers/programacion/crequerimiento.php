<?php
class crequerimiento extends CI_Controller { 
  public function __construct (){ 
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('programacion/model_producto');
        $this->load->model('programacion/model_componente');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('ejecucion/model_ejecucion');
        $this->load->model('programacion/insumos/model_insumo');
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->dist = $this->session->userData('dist');
        $this->rol = $this->session->userData('rol_id');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userdata("fun_id");
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->verif_mes=$this->session->userdata('mes_actual');
        $this->conf_form4 = $this->session->userData('conf_form4');
        $this->conf_form5 = $this->session->userData('conf_form5');
        $this->conf_poa_estado = $this->session->userData('conf_poa_estado'); /// Ajuste POA 1: Inicial, 2 : Ajuste, 3 : aprobado
        $this->load->library('programacionpoa');
      }else{
        $this->session->sess_destroy();
          redirect('/','refresh');
      }
    }

    
    /*------- LISTA DE FORM 5 x Unidad Responsable (Componentes) 2027----------*/
    public function list_requerimientos_x_unidadresponsable($com_id){
        $tabla='';
        $data['stylo'] = $this->programacionpoa->estilo_tabla_form5();
        $get_componente = $this->model_componente->get_componente($com_id, $this->gestion);
        if (!empty($get_componente) && count($get_componente) > 0) {
            $data['part_padres'] = $this->model_partidas->lista_padres(); // Partidas padres (Agrupadores)
            $data['part_hijos']  = $this->model_partidas->lista_partidas(); // Partidas hijos (Sub-ítems)
            $data['titulo']=$this->cabecera_f5_uresponsable($get_componente); //// Cabecera 
        
            // Recuperamos la colección base de requerimientos vinculados (Form 5)
            $lista_insumos = $this->model_insumo->list_requerimientos_uresponsable($com_id);
            $lista_form4=$this->model_producto->lista_productos($com_id);
            $base='<input type="hidden" name="com_id" id="com_id" value="' . $com_id . '">';
            $tabla.=$this->vista_listado_de_requerimientos_programados($lista_insumos,$lista_form4,$base,$this->button_opciones_componente($get_componente));
            $tabla.=$this->programacionpoa->modal_migracion_form5x_componente($get_componente);
            $tabla.=$this->programacionpoa->modal_partidas_programadas_unidad_responsable();

            $data['tabla']=$tabla;
            $this->load->view('admin/programacion/requerimiento/form_anteproyecto_form5', $data); /// Gasto Corriente
      }
      else{
        show_error("🚨 Error SIIPLAS: La Actividad física solicitada no existe en PostgreSQL o fue purgada del Formulario N° 4.");
      }
       
    }



    /*------- LISTA DE FORM 5 x Actividad (Form N° 4) ----------*/
    public function list_requerimientos($prod_id_activo){
      $tabla='';
      $data['stylo'] = $this->programacionpoa->estilo_tabla_form5();
      $get_producto = $this->model_producto->get_producto_id($prod_id_activo);
      if (!empty($get_producto) && count($get_producto) > 0) {
        $get_componente = $this->model_componente->get_componente($get_producto[0]['com_id'], $this->gestion);
        
        $data['part_padres'] = $this->model_partidas->lista_padres(); // Partidas padres (Agrupadores)
        $data['part_hijos']  = $this->model_partidas->lista_partidas(); // Partidas hijos (Sub-ítems)
        $data['titulo']=$this->cabecera($get_producto, $get_componente); //// Cabecera


        // Recuperamos la colección base de requerimientos vinculados (Form 5)
        $lista_insumos = $this->model_insumo->lista_insumos_x_form4($prod_id_activo);
        $lista_form4=$this->model_producto->lista_productos($get_componente[0]['com_id']);
        $base='<input type="hidden" name="prod_id" id="prod_id" value="' . $prod_id_activo . '">';
        $tabla.=$this->vista_listado_de_requerimientos_programados($lista_insumos,$lista_form4,$base,$this->button_opciones_actividad());
        $tabla.=$this->programacionpoa->modal_migracion_form5x_actividad($get_producto, $get_componente); /// modal de migracion x actividad
        $data['tabla']=$tabla;
        $this->load->view('admin/programacion/requerimiento/form_anteproyecto_form5', $data); /// Gasto Corriente
      }
      else{
        show_error("🚨 Error SIIPLAS: La Actividad física solicitada no existe en PostgreSQL o fue purgada del Formulario N° 4.");
      }
    }





    /*------- VISTA LISTA DE REQUERIMIENTOS (ANTEPROYECTO POA) 2027 ----------*/
    public function vista_listado_de_requerimientos_programados($lista_insumos,$lista_form4,$base,$button_opciones){
        $tabla = '';
        $total = 0;
        // Totales verticales acumuladores para el pie de la grilla contable
        $total_meses = array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0, 7=>0, 8=>0, 9=>0, 10=>0, 11=>0, 12=>0);
        $tabla.=$base;
        $tabla .= '<input type="hidden" name="base" id="base_url_siiplas" value="' . base_url() . '">';
        
        // Tabla con diseño vectorial responsivo de SmartAdmin
        $tabla .= '
        <div style="margin-bottom: 15px; display: flex; gap: 8px; justify-content: space-between; align-items: center; background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
            <div style="display: flex; gap: 6px;">'.$button_opciones.'</div>
        </div>';
        $tabla .= '
        <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #e2e8f0; border-radius: 4px;">
        <table id="dt_basic" class="table table-striped table-bordered table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11px; border-collapse: collapse;">
            <thead>
              <tr style="background: #475569; color: #ffffff; text-transform: uppercase; font-size: 10px; letter-spacing: 0.3px;">
                <th style="text-align: center; vertical-align: middle; width: 4%; padding: 8px;">ACCIONES</th>
                <th style="text-align: center; vertical-align: middle; width: 2%; padding: 8px;">COD. ACT.</th>
                <th style="text-align: center; vertical-align: middle; width: 2%; padding: 8px;">ELIMINAR</th>
                <th style="text-align: center; vertical-align: middle; width: 5%; padding: 8px;">PARTIDA</th>
                <th style="text-align: left; vertical-align: middle; width: 15%; padding: 8px;">DETALLE REQUERIMIENTO</th>
                <th style="text-align: left; vertical-align: middle; width: 4%; padding: 8px;">UNIDAD MEDIDA</th>
                <th style="text-align: center; vertical-align: middle; width: 4%; padding: 8px;">CANTIDAD</th>
                <th style="text-align: right; vertical-align: middle; width: 5%; padding: 8px;">PRECIO</th>
                <th style="text-align: right; vertical-align: middle; width: 5%; padding: 8px;">COSTO TOTAL</th>
                
                <!-- Cabeceras Mensuales en Verde Agua Institucional CNS -->
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">ENE</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">FEB</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">MAR</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">ABR</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">MAY</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">JUN</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">JUL</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">AGO</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">SEP</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">OCT</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">NOV</th>
                <th style="background-color: #0aa699; color: #ffffff; text-align: right; width: 4.5%; padding: 8px 4px;">DIC</th>
                
                <th style="text-align: left; vertical-align: middle; width: 10%; padding: 8px;">OBSERVACIONES</th>
              </tr>
            </thead>
            <tbody>';
            
            $cont = 0;
            foreach ($lista_insumos as $row) {
                $cont++;
                $ins_id_actual = intval($row['ins_id']);
                $ins_cert = (floatval($row['ins_monto_certificado']) > 0) ? 1 : 0;
                $tr_style = ($ins_cert == 1) ? 'style="background: #fef2f2;"' : '';
                $tabla .= '<tr ' . $tr_style . ' title="ID Insumo: ' . $ins_id_actual . '">';
                $tabla .= '<td style="text-align: center; padding: 6px; vertical-align: middle; height:40px;">';
                if ($this->tp_adm == 1 || $this->conf_form5 == 1) {
                    if ($ins_cert == 0) {
                        $tabla .= '
                        <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                            <a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-xs btn-default mod_ff" name="' . $ins_id_actual . '" title="MODIFICAR REQUERIMIENTO PRESUPUESTARIO" style="padding: 10px 15px;"><i class="fa fa-pencil text-warning" style="font-size:18px;"></i></a>
                            
                            <a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-xs btn-default del_ff" name="' . $ins_id_actual . '" title="ELIMINAR REQUERIMIENTO PRESUPUESTARIO" style="padding: 10px 15px; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 4px; transition: all 0.2s ease; display: inline-flex; align-items: center; justify-content: center;">
                                <img src="' . base_url() . 'assets/ifinal/eliminar.png" WIDTH="20" HEIGHT="20"/>
                            </a>
                        </div>';
                    } else {
                        $tabla .= '<span class="label label-danger" style="font-size: 9px; padding: 12px 27px; font-weight: bold; background-color: #ef4444;"><i class="fa fa-lock"></i> CERTIFICADO</span>';
                    }
                } else {
                    $tabla .= '<i class="fa fa-eye text-muted" title="Solo lectura"></i>';
                }
                $tabla .= '</td>';

                // Columnas descriptivas base del clasificador nacional
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e293b; padding: 6px; vertical-align: middle;">';
                if (($this->tp_adm == 1 || $this->conf_form5 == 1) && $ins_cert == 0) {
                  $tabla.='<select class="form-control select-actividad" data-id="' . $row['ins_id'] . '" style="width: auto; min-width: 50px;">';
                                foreach ($lista_form4 as $pr) {
                                    $selected = ($pr['prod_id'] == $row['prod_id']) ? 'selected' : '';
                                    $tabla .= '<option value="' . $pr['prod_id'] . '" ' . $selected . '>' . $pr['prod_cod'] . '</option>';
                                }
                            $tabla .= '
                            </select>';
                }
                else{
                  $tabla.='<b>' . $row['prod_cod'] . '</b>';
                }
                $tabla.='</td>';
                $tabla.='<td>';
                if (($this->tp_adm == 1 || $this->conf_form5 == 1) && $ins_cert == 0) {
                  $tabla.='
                  <center style="margin: 0; padding: 0;">
                     <input type="checkbox" 
                            class="check-eliminar" 
                            name="ins[]" 
                            value="' . $row['ins_id'] . '" 
                            style="cursor: pointer; width: 22px; height: 22px; accent-color: #dc2626; outline: 1px solid #fecdd3; border-radius: 4px; transition: transform 0.1s ease;"
                            onmouseover="this.style.transform=\'scale(1.1)\'"
                            onmouseout="this.style.transform=\'scale(1.0)\'">
                  </center>';
                }
                $tabla.='</td>';
                $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e293b; padding: 6px; vertical-align: middle;">' . $row['par_codigo'] . '</td>';
                $tabla .= '<td style="text-align: left; padding: 6px; color: #334155; vertical-align: middle;">' . strtoupper($row['ins_detalle']) . '</td>';
                $tabla .= '<td style="text-align: left; padding: 6px; width: 4%; color: #475569; vertical-align: middle;">' . strtoupper($row['ins_unidad_medida']) . '</td>';
                $tabla .= '<td style="text-align: center; font-weight: bold; width: 4%;color: #1e293b; padding: 6px; vertical-align: middle;">' . intval($row['ins_cant_requerida']) . '</td>';
                $tabla .= '<td style="text-align: right; padding: 6px; width: 5%; vertical-align: middle;">' . number_format($row['ins_costo_unitario'], 2, '.', ',') . '</td>';
                $tabla .= '<td style="text-align: right; font-weight: width: 5%; bold; background: #f8fafc; color: #0f172a; padding: 6px; vertical-align: middle;">' . number_format($row['ins_costo_total'], 2, '.', ',') . '</td>';
                for ($m = 1; $m <= 12; $m++) {
                    $monto_mes_real = isset($row['mes_' . $m]) ? floatval($row['mes_' . $m]) : 0.00;
                    $style_celda_mes = ($monto_mes_real > 0) ? 'style="text-align: right; width: 4.5%; background: #f0fdf4; color: #16a34a; font-weight: bold; padding: 6px; vertical-align: middle;"' : 'style="text-align: right; color: #cbd5e1; padding: 6px; vertical-align: middle;"';
                    $tabla .= '<td ' . $style_celda_mes . '>' . ($monto_mes_real > 0 ? number_format($monto_mes_real, 2, '.', ',') : '0.00') . '</td>';
                    $total_meses[$m] += $monto_mes_real;
                }
                $tabla .= '<td style="text-align: left; color: #64748b; font-style: italic; padding: 6px; vertical-align: middle;">' . htmlspecialchars(strtoupper($row['ins_observacion']), ENT_QUOTES, 'UTF-8') . '</td>';
                $tabla .= '</tr>';
                $total += floatval($row['ins_costo_total']);
            }
            $tabla.='
            </tbody>
              <tr class="modo1">
                <td colspan="8"> TOTAL PROGRAMADO</td>
                <td style="text-align: right; font-size:20px;"><font color="blue" size=1>'.number_format($total, 2, ',', '.') .'</font></td>
                <td colspan="13"></td>
              </tr>
          </table>';
        
        return $tabla;
    }







    //// Cambia alineacion de Actividad
    public function cambia_actividad() {
        // 1. Validar que la petición sea una solicitud asíncrona legítima de red
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }
        // 2. Recibir y sanitizar los datos numéricos enviados desde el JQuery
        $ins_id  = intval($this->input->post('ins_id'));
        $prod_id = floatval($this->input->post('nuevo_prod_id')); // numeric(18,0) de Postgres

        if ($ins_id <= 0 || $prod_id <= 0) {
            echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Parámetros relacionales corruptos o vacíos.'));
            return;
        }
        // 3. Ejecución del query físico sobre la tabla intermedia pivote
        $update_prod = array(
            'prod_id' => $prod_id,
        );
        
        $this->db->where('ins_id', $ins_id);
        $db_status = $this->db->update('public._insumoproducto', $update_prod);

        // 🌟 PASO EXCLUSIVO ANTI-FALLAS JSON: Purgamos buffers ocultos de CodeIgniter
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        // 4. Responder al cliente de forma simétrica
        if ($db_status) {
            $respuesta = array(
                'status'    => 'success', 
                'respuesta' => 'correcto', // Sincronizado para tu response.respuesta === 'correcto'
                'message'   => '¡Se ha re-alineado el requerimiento de forma exitosa!'
            );
        } else {
            $respuesta = array(
                'status'    => 'error', 
                'respuesta' => 'error', 
                'message'   => 'PostgreSQL rechazó la re-alineación por restricciones de clave externa.'
            );
        }
        echo json_encode($respuesta);
        exit; // Detiene el hilo impidiendo filtraciones HTML
    }

    //// Eliminar Requerimiento
    public function eliminar_requerimiento_unitario() {
          // 1. Validar que sea una solicitud asíncrona legítima de red (Evita accesos directos por URL)
          if (!$this->input->is_ajax_request()) {
              show_404();
              return;
          }

          // 2. Recibir y sanitizar el identificador único del insumo enviado por POST
          $ins_id = intval($this->input->post('ins_id'));

          if ($ins_id <= 0) {
              echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador relacional de requerimiento vacío o corrupto.'));
              return;
          }

          // ==========================================================================
          // 🔒 CANDADO DE AUDITORÍA: VERIFICACIÓN PREVENTIVA DE CERTIFICACIÓN VIGENTE
          // ==========================================================================
          // Si el requerimiento ya está amarrado a una certificación, detenemos la purga física
          $sql_verif_cert = "
              SELECT COUNT(cert_temp.ctins_id) AS certificado_total
              FROM public.temporalidad_prog_insumo temp
              INNER JOIN public.cert_temporalidad_prog_insumo cert_temp ON temp.tins_id = cert_temp.tins_id
              WHERE temp.ins_id = ?
          ";
          $query_cert = $this->db->query($sql_verif_cert, array($ins_id));
          $res_cert   = $query_cert->row_array();

          if (!empty($res_cert) && intval($res_cert['certificado_total']) > 0) {
              echo json_encode(array(
                  'status'    => 'error',
                  'respuesta' => 'error',
                  'message'   => 'Restricción de Control de Gasto: Este requerimiento específico ya cuenta con celdas mensuales CERTIFICADAS dentro del SIGEP. Revierta el trámite en el Departamento de Presupuestos antes de poder eliminar el registro.'
              ));
              return;
          }
          // ==========================================================================

          // ==========================================================================
          // 🌟 INICIO DE COMPUERTA TRANSACCIONAL ATÓMICA DE POSTGRESQL (CASCADA)
          // ==========================================================================
          $this->db->trans_start();

          // PASO A: Purgamos la temporalidad programada de los 12 meses financieros (Form 5)
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('public.temporalidad_prog_insumo');

          // PASO B: Purgamos el nudo relacional de la tabla intermedia pivote _insumoproducto
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('public._insumoproducto');

          // PASO C: Finalmente, barremos el registro maestro en la tabla de insumos
          $this->db->where('ins_id', $ins_id);
          $this->db->delete('public.insumos');

          // Sella las instrucciones obligando al driver a verificar consistencias físicas en disco
          $this->db->trans_complete();
          // ==========================================================================

          // 🌟 REPARACIÓN MÁSTER ANTI-FALLAS: Purgamos buffers ocultos de CodeIgniter 
          // Esto garantiza un flujo JSON puro libre de código HTML (Evita el Unexpected token '<')
          while (ob_get_level() > 0) {
              ob_end_clean();
          }
          header('Content-Type: application/json; charset=utf-8');

          // 3. Evaluar el estatus final de la transacción de base de datos
          if ($this->db->trans_status() !== FALSE) {
              echo json_encode(array(
                  'status'    => 'success',
                  'respuesta' => 'correcto', // Sincronizado milimétricamente con tu form5.js
                  'message'   => 'El requerimiento presupuestario y su desglose mensualizado fueron eliminados del SIIPLAS v2.0 con éxito.'
              ));
          } else {
              echo json_encode(array(
                  'status'    => 'error',
                  'respuesta' => 'error',
                  'message'   => 'PostgreSQL rechazó la remoción física debido a una restricción de integridad relacional interna.'
              ));
          }
          
          exit; // Detiene la ejecución impidiendo que el framework inyecte layouts o vistas muertas
      }
  

      ///// Valida Elimnacion masiva de requerimientos
      public function eliminar_requerimientos_masivo() {
        // 1. Validar que sea una solicitud asíncrona legítima de red (Evita accesos directos por URL)
        if (!$this->input->is_ajax_request()) {
            show_404();
            return;
        }

        // 2. Recibir la matriz de identificadores seleccionados por el operador regional
        $ins_ids = $this->input->post('ins_ids');

        if (empty($ins_ids) || !is_array($ins_ids)) {
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'La lista de requerimientos seleccionados se encuentra vacía.'));
            exit;
        }

        // Sanitizamos y casteamos cada ID del arreglo a enteros puros para blindar contra inyecciones SQL
        $clean_ids = array_map('intval', $ins_ids);
        // ==========================================================================
        // 🌟 INICIO DE COMPUERTA TRANSACCIONAL ATÓMICA MASIVA EN POSTGRESQL (WHERE IN)
        // ==========================================================================
        $this->db->trans_start();

        // PASO A: Barremos en lote la temporalidad de meses programada (Form 5)
        $this->db->where_in('ins_id', $clean_ids);
        $this->db->delete('public.temporalidad_prog_insumo');

        // PASO B: Barremos en lote el nudo intermedio pivote _insumoproducto
        $this->db->where_in('ins_id', $clean_ids);
        $this->db->delete('public._insumoproducto');

        // PASO C: Barremos en lote la cabecera maestra de insumos
        $this->db->where_in('ins_id', $clean_ids);
        $this->db->delete('public.insumos');

        // Sella las instrucciones obligando al driver a verificar consistencias físicas en disco
        $this->db->trans_complete();
        // ==========================================================================

        // 🌟 REPARACIÓN MÁSTER ANTI-FALLAS: Purgamos buffers ocultos de CodeIgniter 
        // Esto garantiza un flujo JSON puro libre de código HTML (Evita el Unexpected token '<')
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        // 3. Evaluar el estatus final de la transacción de base de datos de la CNS
        if ($this->db->trans_status() !== FALSE) {
            echo json_encode(array(
                'status'    => 'success',
                'respuesta' => 'correcto', // Engancha perfecto con tu res.respuesta === 'correcto' en el JS
                'message'   => 'Se han eliminado correctamente los (' . count($clean_ids) . ') requerimientos seleccionados y sus desgloses mensuales del SIIPLAS.'
            ));
        } else {
            echo json_encode(array(
                'status'    => 'error', 
                'respuesta' => 'error', 
                'message'   => 'PostgreSQL rechazó la purga en lote debido a una restricción de integridad relacional externa.'
            ));
        }
        exit; // Detiene la ejecución impidiendo que el framework filtre layouts o vistas pie de página
    }



    //// Cabecera titulo por Actividad
    public function cabecera($producto_row, $componente){
      $componente_row = $componente[0]; // Hilera única activa del componente
      $producto_row = $producto_row[0]; // Hilera única activa de la consulta
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

      $tabla='';
      $tabla.='
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <input type="hidden" name="base" value="'.base_url().'">
        <div class="well">
          '.$data['datos'].'
          '.$data['prog_especial'].'
        </div>
      </article>';

      return $tabla;
    }


    //// Cabecera titulo por Unidad Responsable
    public function cabecera_f5_uresponsable($componente){
      $componente_row = $componente[0]; // Hilera única activa del componente
      $tit = '<small>PROYECTO : </small>' . $componente_row['aper_programa'] . ' ' . $componente_row['proy_sisin'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['proy_nombre'];
      /*--------- Caso Gasto Corriente (Apertura tipo 4) ----------*/
      if (intval($componente_row['tp_id']) == 4) {
          $tit = '<h2>' . $componente_row['aper_programa'] . ' ' . $componente_row['aper_proyecto'] . ' ' . $componente_row['aper_actividad'] . ' - ' . $componente_row['tipo'] . ' ' . $componente_row['act_descripcion'] . ' - ' . $componente_row['abrev'] . '  / <b>' . $componente_row['serv_cod'] . ' </b>' . $componente_row['tipo_subactividad'] . ' ' . $componente_row['serv_descripcion'] . '</h2>';
      }

      $data['datos'] = '<h1>' . $tit . '</h1>';

      $tabla='';
      $tabla.='
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <input type="hidden" name="base" value="'.base_url().'">
        <div class="well">
          '.$data['datos'].'
        </div>
      </article>';

      return $tabla;
    }

    //// Opciones formulario 5 x Actividad
    public function button_opciones_actividad(){
      $tabla='';
      if($this->tp_adm==1 || $this->conf_form5==1){
            $tabla.='
            <a href="#" data-toggle="modal" data-target="#modal_importar_f5" class="btn btn-default importar_f5" title="SUBIR ARCHIVO REQUERIMIENTO (GLOBAL)" >
              <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="30" HEIGHT="20"/>&nbsp;<b>Subir Archivo Requerimientos.xls</b>
            </a>
            <button type="button" id="btn_eliminar_masivo_f5" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Eliminar Items Seleccionados</button>';
          }
      return $tabla;
    }


    //// Opciones formulario 5 x Unidad Responsable
    public function button_opciones_componente($componente){
      $tabla='';
      if($this->tp_adm==1 || $this->conf_form5==1){
            $tabla .= '
            <div style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; background: #f8fafc; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
                
                <!-- Bloque Izquierdo: Operaciones y Acciones sobre la Grilla -->
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <!-- 📥 Botón Importar Excel Global -->
                    <a href="#" 
                       data-toggle="modal" 
                       data-target="#modal_importar_f5" 
                       class="btn btn-sm btn-success importar_f5" 
                       title="IMPORTAR ARCHIVO EXCEL DE REQUERIMIENTOS (GLOBAL)" 
                       style="font-weight: bold; background: #16a34a; border-color: #16a34a; color: #ffffff; padding: 6px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease;"
                       onmouseover="this.style.background=\'#15803d\'; this.style.borderColor=\'#15803d\';"
                       onmouseout="this.style.background=\'#16a34a\'; this.style.borderColor=\'#16a34a\';">
                        <i class="fa fa-upload" style="font-size: 13px;"></i> Subir Requerimientos (.xls)
                    </a>

                    <!-- 🚨 Botón Eliminar Items Seleccionados (Lotes) -->
                    <button type="button" 
                            id="btn_eliminar_masivo_f5" 
                            class="btn btn-sm btn-danger" 
                            title="ELIMINAR DEFINITIVAMENTE LOS REQUERIMIENTOS SELECCIONADOS POR CHECKBOX"
                            style="font-weight: bold; background: #dc2626; border-color: #dc2626; color: #ffffff; padding: 6px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;"
                            onmouseover="this.style.background=\'#b91c1c\'; this.style.borderColor=\'#b91c1c\';"
                            onmouseout="this.style.background=\'#dc2626\'; this.style.borderColor=\'#dc2626\';">
                        <i class="fa fa-trash-o" style="font-size: 13px;"></i> Eliminar Items Seleccionados
                    </button>
                </div>

                <button type="button" 
                        class="btn btn-xs btn-default btn-ver-partidas-unidad" 
                        data-id="' . $componente[0]['com_id']. '" 
                        data-codigo="' . $componente[0]['serv_cod'] . ' ' . $componente[0]['tipo_subactividad'] . '" 
                        data-nombre="' . htmlspecialchars($componente[0]['serv_descripcion'], ENT_QUOTES, 'UTF-8') . '" 
                        data-toggle="modal" 
                        data-target="#modal_desglose_partidas_unidad"
                        title="VER MATRIZ DE REQUERIMIENTOS POR PARTIDA" 
                        style="font-weight: bold; padding: 5px 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius:3px; color:#334155;">
                    <i class="fa fa-table text-info"></i> Ver Detalle de Partidas
                </button>

                <!-- Bloque Derecho: Reportes Oficiales en PDF (Salidas de Auditoría) -->
                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                    <!-- 🖨️ Reporte POA Estándar de la Unidad -->
                    <a href="javascript:abreVentana(\'' . site_url("prog/reporte_form5_uresponsable/" . $componente[0]['com_id']) . '\');" 
                       title="GENERAR REPORTE POA FORMULARIO N° 5 ESTÁNDAR (PDF)" 
                       class="btn btn-sm btn-default" 
                       style="font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 6px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease;"
                       onmouseover="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#94a3b8\';"
                       onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">
                        <i class="fa fa-file-pdf-o text-danger" style="font-size: 13px;"></i> Form. N° 5 (Requerimientos)
                    </a>

                    <!-- 🖨️ Reporte POA Consolidado Programa Bolsa -->
                    <a href="javascript:abreVentana(\'' . site_url("prog/reporte_form5_uresponsable_programa_bolsa_consoldado/" . $componente[0]['com_id']) . '\');" 
                       title="GENERAR REPORTE CONSOLIDADO FORMULARIO N° 5 - PROGRAMA BOLSA (PDF)" 
                       class="btn btn-sm btn-default" 
                       style="font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 6px 14px; border-radius: 4px; display: inline-flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease;"
                       onmouseover="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#94a3b8\';"
                       onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">
                        <i class="fa fa-file-pdf-o text-danger" style="font-size: 13px;"></i> Form. N° 5 (Consolidado de Bolsas)
                    </a>
                </div>

            </div>';

          }
      return $tabla;
    }




   

    //// Valida Migracion de Requerimientos por Actividad
    public function valida_migracion_form5_x_actividad() {
        if (function_exists('ini_set')) {
            ini_set('max_execution_time', 900); 
            ini_set('memory_limit', '3072M'); 
        }
        if (function_exists('set_time_limit')) { @set_time_limit(900); }
        if (function_exists('gc_enable')) { gc_enable(); }

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
                // 📋 🛠️ REPARADO REGLA 2: VALIDACIÓN ESTRICTA DE PRECIO UNITARIO (MÁXIMO 2 DECIMALES)
                if ($precio_raw === NULL || trim($precio_raw) === '' || !is_numeric($precio_raw)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' es obligatorio y debe ser numérico.";
                } else {
                    $precio_float = floatval($precio_raw);
                    
                    // Condicional contable: Multiplicamos por 100 y comparamos contra su entero truncado para cazar un 3er decimal (Ej: 2.345 * 100 = 234.5 != 234)
                    if (floor($precio_float * 100) != ($precio_float * 100)) {
                        // Tolerancia por ruido flotante residual de memoria PHP (0.00001)
                        if (abs(($precio_float * 100) - floor($precio_float * 100)) > 0.00001 && abs(($precio_float * 100) - ceil($precio_float * 100)) > 0.00001) {
                            $errores[] = "Fila $i: Restricción Contable -> El 'PRECIO UNITARIO' ($precio_raw) excede el límite permitido de la CNS. Solo se aceptan hasta 2 decimales puros (Ej: 3.45).";
                        }
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
                          'ins_gestion'             => $this->gestion,
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

    //// Get Requerimiento
    public function get_requerimiento(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $ins_id = $this->security->xss_clean($post['ins_id']);
        $insumo= $this->model_insumo->get_requerimiento($ins_id); /// Datos requerimientos productos
        $producto=$this->model_producto->get_producto_id($insumo[0]['prod_id']); /// Get producto
        $componente = $this->model_componente->get_componente($producto[0]['com_id'],$this->gestion); /// Get Componente

/*        $monto_asig=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($componente[0]['aper_id'],1);
        $monto_prog=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($componente[0]['aper_id'],2);
        

        $m_asig=0;$m_prog=0;
        if(count($monto_asig)!=0){
          $m_asig=$monto_asig[0]['monto'];
        }
        if(count($monto_prog)!=0){
          $m_prog=$monto_prog[0]['monto'];
        }*/

        //$saldo=($m_asig-$m_prog);
        $saldo=0;
        $par_padre=$this->model_partidas->get_partida_padre($insumo[0]['par_depende']); /// lista de partidas padres
        $lista_partidas=$this->programacionpoa->partidas_dependientes($insumo); /// Lista de Insumos dependientes
        $lista_umedida=$this->programacionpoa->unidades_medida($insumo); /// Lista de Unidad de medida

        if(count($insumo)!=0){
          $result = array(
            'respuesta' => 'correcto',
            'insumo' => $insumo,
            'monto_saldo' => $saldo+$insumo[0]['ins_costo_total'],
            'lista_partidas'=> $lista_partidas,
            'lista_umedida'=> $lista_umedida,
            'ppdre' => $par_padre,
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


      /// Valida Update Insumo - Formulario de modificacion
      public function valida_update_insumo(){
        // 🌟 REPARADO: Se adapta para validar tanto peticiones POST como solicitudes legítimas AJAX
        if($this->input->post()) {
            
            $post = $this->input->post();
            
            // Sanitización y tipado estricto de claves primarias contables
            $ins_id         = intval($this->security->xss_clean($post['ins_id'])); 
            $detalle        = $this->security->xss_clean($post['detalle']); 
            $cantidad       = intval($this->security->xss_clean($post['cantidad'])); 
            $costo_unitario = round(floatval($this->security->xss_clean($post['costou'])), 2); 
            $umedida        = $this->security->xss_clean($post['iumedida']); 
            $partida        = intval($this->security->xss_clean($post['par_hijo'])); 
            $observacion    = $this->security->xss_clean($post['observacion']); 

            // 🌟 CONTROL ARITMÉTICO DE SEGURIDAD: Recalculamos el costo total al centavo
            $costo_total = round(($cantidad * $costo_unitario), 2); 

            if ($ins_id <= 0 || $cantidad <= 0 || $costo_unitario <= 0 || $partida <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Se detectaron celdas con montos vacíos o tipados incorrectos en la planilla.'));
                exit;
            }

            // Recuperamos la herencia jerárquica para amarrar los datos del componente POA
            $insumo      = $this->model_insumo->get_requerimiento($ins_id); 
            $producto    = $this->model_producto->get_producto_id($insumo[0]['prod_id']); 
            $componente  = $this->model_componente->get_componente($producto[0]['com_id'], $this->gestion); 

            // ==========================================================================
            // 🌟 INICIO DE COMPUERTA TRANSACCIONAL ATÓMICA EN POSTGRESQL (CASCADA)
            // ==========================================================================
            $this->db->trans_start();

            /*------------ A. UPDATE REQUERIMIENTO (CABECERA) -------*/
            $update_ins = array(
                'ins_cant_requerida'=> $cantidad,
                'ins_costo_unitario'=> $costo_unitario,
                'ins_costo_total'   => $costo_total,
                'ins_detalle'       => strtoupper($detalle),
                'par_id'            => $partida, 
                'ins_unidad_medida' => strtoupper($umedida),
                'ins_observacion'   => strtoupper($observacion),
                'fun_id'            => intval($this->session->userdata('fun_id')), // Se extrae seguro de la sesión activa
                'com_id'            => $producto[0]['com_id'], 
                'form4_cod'         => $producto[0]['prod_cod'], 
                'ins_estado'        => 2, // Estado modificado de auditoría
                'num_ip'            => $this->input->ip_address(), 
                'nom_ip'            => gethostbyaddr($_SERVER['REMOTE_ADDR'])
            );
            
            $this->db->where('ins_id', $ins_id);
            $this->db->update('insumos', $update_ins); // 🛠️ REPARADO: Se remueve el xss_clean duplicado que rompía arrays en CI

            /*-------- B. DELETE CRONOGRAMA MENSUAL PREVIO --------*/  
            $this->db->where('ins_id', $ins_id);
            $this->db->delete('temporalidad_prog_insumo');

            /*-------- C. INSERT NUEVA DISTRIBUCIÓN MENSUALIZADA --------*/  
            for ($i = 1; $i <= 12; $i++) {
                if (isset($post['mm' . $i])) {
                    $pfin = round(floatval($this->security->xss_clean($post['mm' . $i])), 2);
                    
                    if ($pfin > 0) {
                        $data_to_store4 = array( 
                            'ins_id'  => $ins_id, 
                            'mes_id'  => $i, 
                            'ipm_fis' => $pfin, 
                            'g_id'    => intval($this->gestion), 
                        );
                        $this->db->insert('temporalidad_prog_insumo', $data_to_store4);
                    }
                }
            }

            // Sella las instrucciones y obliga al driver nativo a verificar consistencias relacionales
            $this->db->trans_complete();
            // ==========================================================================

            // 🌟 COMPUETA ANTI-FALLAS CRÍTICAS: Purgamos buffers ocultos de CodeIgniter 
            // Garantiza una salida JSON limpia desprovista de cualquier layout HTML
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');

            // Evaluamos el estatus final del compromiso en la base de datos de la CNS
            if ($this->db->trans_status() !== FALSE) {
                echo json_encode(array(
                    'status'    => 'success',
                    'respuesta' => 'correcto', // Engancha perfecto con tu if (response.respuesta === "correcto")
                    'message'   => '¡El requerimiento presupuestario y su distribución mensual fueron modificados con éxito en el SIIPLAS!'
                ));
            } else {
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'message'   => 'PostgreSQL rechazó la modificación por lotes debido a un conflicto de integridad referencial externa.'
                ));
            }
            exit; // Congela el hilo impidiendo filtraciones de layouts o vistas pie de página del framework

        } else {
            show_404();
        }
    }


    /// Get listado de partidas programadas por Unidad Responsable / Componente
    public function get_desglose_partidas_unidad_ajax() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            $com_id = intval($this->input->post('com_id'));
            $g_id   = intval($this->gestion);

            $list_partidas=$this->model_insumo->list_consolidado_partidas_uResponsable($com_id);

            $html = '
            <div style="margin-bottom: 12px; text-align: right;">
                <button type="button" class="btn btn-xs btn-default" onclick="imprimirDetallePartidasModal();" style="font-family: Arial, sans-serif; font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; padding: 5px 14px; font-size:11px; color:#334155; border-radius:3px; cursor:pointer; transition: all 0.15s ease;">
                    <i class="fa fa-print text-primary" style="font-size:12px;"></i> Imprimir Detalle
                </button>
            </div>';

            // ==========================================================================
            // 📊 SUPERESTRUCTURA DE LA TABLA EJECUTIVA FORMAL
            // ==========================================================================
            $html .= '
            <div id="area_impresion_detalle_partidas" class="table-responsive" style="border: 1px solid #cbd5e1; border-radius: 4px;">
                <table class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11.5px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #334155; color: #ffffff; text-transform: uppercase; font-size: 10px; height: 34px; letter-spacing:0.3px;">
                            <th style="padding: 8px; text-align: left; background: #1e293b; vertical-align: middle;">PARTIDA PRESUPUESTARIA Clasificadora</th>
                            <th style="padding: 8px; text-align: right; width: 22%; background: #1e3a8a; vertical-align: middle;">PROGRAMADO (Bs.)</th>
                            <th style="padding: 8px; text-align: right; width: 22%; background: #0aa699; vertical-align: middle;">CERTIFICADO (Bs.)</th>
                            <th style="padding: 8px; text-align: right; width: 22%; background: #475569; vertical-align: middle;">SALDO DISPONIBLE (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            // 🛠️ REPARADO: Se ajusta el colspan a 4 para cubrir la estructura simétrica real
            if(empty($list_partidas)) {
                $html .= '<tr><td colspan="4" class="text-center" style="padding: 15px; font-weight: bold; color: #64748b;"><i class="fa fa-info-circle"></i> Sin requerimientos presupuestarios asignados en esta unidad.</td></tr>';
            } else {
                foreach($list_partidas as $row) {
                    $saldo_item = floatval($row['saldo']);
                    
                    // Alerta visual cromática formal si la partida registra sobregiro (Monto menor a cero)
                    $style_saldo = ($saldo_item < 0) ? 'background: #fef2f2; color: #dc2626; font-weight: bold;' : 'background: #f8fafc; color: #334155; font-weight: bold;';

                    $html .= '<tr style="height: 28px; vertical-align: middle;">';
                    $html .= '<td style="font-weight: bold; color: #0f172a; padding-left: 8px;">' . $row['par_codigo'] . ' - ' . strtoupper($row['par_nombre']) . '</td>';
                    
                    // 🌟 CÓDIGO CROMÁTICO REPARADO: Colores independientes y coherentes por columna
                    $html .= '<td style="text-align: right; padding-right: 8px; font-weight: bold; color: #1e40af;">' . number_format($row['monto'], 2, '.', ',') . '</td>';
                    $html .= '<td style="text-align: right; padding-right: 8px; font-weight: bold; color: #16a34a;">' . number_format($row['monto_certificado'], 2, '.', ',') . '</td>';
                    $html .= '<td style="text-align: right; padding-right: 8px; ' . $style_saldo . '">' . number_format($saldo_item, 2, '.', ',') . '</td>';
                    $html .= '</tr>';
                }
            }
            $html .= '</tbody></table></div>';

            // Blindaje contra errores de token '<': Vacíamos buffers intermedios de PHP
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            // Despachamos el payload hacia el done del form5.js
            echo json_encode(array(
                'status' => 'success',
                'respuesta' => 'correcto',
                'html_reporte' => $html
            ));
            exit;
        }
    }



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


    //// Get partidas asignadas y programadas
   public function get_resumen_techo_proyecto_global_ajax() {
        // Validamos la legitimidad asíncrona de la solicitud de red
        if ($this->input->is_ajax_request() && $this->input->post()) {
            
            $proy_id = intval($this->input->post('proy_id'));
            $g_id    = intval($this->gestion); // Gestión POA de la sesión activa

            if ($proy_id <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'message' => 'Identificador de proyecto inválido o vacío.'));
                return;
            }

            // Recuperamos la ficha e indicadores maestros del Proyecto CNS
            $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id);

            if (empty($proyecto)) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'message' => 'El proyecto solicitado no existe en PostgreSQL.'));
                return;
            }

           $tabla=$this->programacionpoa->modal_partidas_unidad_organizacional($proyecto);

            // Purgamos búferes intermedios ocultos de CodeIgniter para garantizar salida JSON pura libre de HTML
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            // 🌟 REPARADO: Agregados los puntos de concatenación contables obligatorios para los strings
            echo json_encode(array(
                'status'       => 'success',
                'respuesta'    => 'correcto',
                'proy_nombre'  => strtoupper($proyecto[0]['tipo'].' '.$proyecto[0]['proy_nombre'] . " [" . $proyecto[0]['abrev'] . "]"),
                'html_reporte' => $tabla
            ));
            exit;

        } else {
            show_404();
        }
    }




}