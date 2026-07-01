<?php
class Componente extends CI_Controller { 
  public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null){
            $this->load->library('pdf2');
            $this->load->model('programacion/model_proyecto');
            $this->load->model('programacion/model_faseetapa');
            $this->load->model('programacion/model_componente');
            $this->load->model('programacion/model_producto');
           // $this->load->model('programacion/model_actividad');
            $this->load->model('programacion/insumos/minsumos');
            $this->load->model('mantenimiento/model_estructura_org');
            $this->load->model('mestrategico/model_objetivoregion');
            $this->load->model('menu_modelo');
            $this->load->library('security');
            $this->load->model('Users_model','',true);
            $this->gestion = $this->session->userData('gestion');
            $this->adm = $this->session->userData('adm');
            $this->tp_adm = $this->session->userData('tp_adm');
            $this->dist = $this->session->userData('dist');
            $this->rol = $this->session->userData('rol_id');
            $this->dist_tp = $this->session->userData('dist_tp');
            $this->fun_id = $this->session->userdata("fun_id");
            $this->conf_form4 = $this->session->userData('conf_form4');
            $this->conf_form5 = $this->session->userData('conf_form5');
            $this->load->library('programacionpoa');

            }else{
                redirect('/','refresh');
            }
    }


    /*----- VERIFICA EL TIPO DE GASTO ------*/
    public function verif_tipo_gasto($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id); // Proy
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            if($data['proyecto'][0]['tp_id']==1){ //// Proyecto
                $this->lista_componentes($proy_id);
            }
            else{ /// Gasto Corriente

                if($data['proyecto'][0]['por_id']==0){
                    $this->lista_uresponsables($proy_id); /// lista de unidades responsables
                }
                else{
                    $componente=$this->model_componente->proyecto_componente($proy_id); /// Programas Bolsa
                    redirect(site_url("").'/admin/prog/list_prod/'.$componente[0]['com_id'].''); /// redireccionadmos a Lista de form 4
                }
            }
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }

    }

    /*--------- LISTA DE COMPONENTES------*/
    public function lista_componentes($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id); // Proy
        $data['fase'] = $this->model_faseetapa->get_id_fase($proy_id); //// recupera datos de la tabla fase activa
        if(count($data['fase'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $data['unidad']=$this->model_componente->list_subactividades_pi();
            $data['componente']=$this->list_componentes_pi($proy_id); 
            $this->load->view('admin/programacion/componente/list_componentes_pi', $data);
        }
        else{
            redirect('admin/proy/fase_etapa/'.$proy_id); ///// fase sin habilitar
        }
        
    }

    /*------- GASTO CORRIENTE-----------*/
    /*--------- LISTA DE UNIDADES RESPONSABLES ------*/
    public function lista_uresponsables($proy_id){
        $unidad_responsable = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        

        if(count($unidad_responsable)!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $button='';
            if($this->conf_form4==1 || $this->tp_adm==1){
                $button='
                <br>&nbsp;
                <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
                  <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO ACTIVIDADES.Xls </b>
                </a>
                <hr>';
            }
            $listado='';
            $listado.='
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <section id="widget-grid" class="well" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                  <div class="row" style="margin: 0; display: flex; flex-direction: column; gap: 12px;">
                      
                      <!-- Tu etiqueta h1 con un espaciado regular y tipografía color plomo oscuro -->
                      <h1 style="margin: 0; line-height: 1.4;">
                          <small>
                              ' . $unidad_responsable[0]['aper_programa'] . ' ' . $unidad_responsable[0]['aper_proyecto'] . ' ' . $unidad_responsable[0]['aper_actividad'] . ' - ' . $unidad_responsable[0]['tipo'] . ' ' . $unidad_responsable[0]['act_descripcion'] . ' - ' . $unidad_responsable[0]['abrev'] . '
                          </small>
                      </h1>
                      
                      <!-- Contenedor flex dinámico para alinear tus botones en la misma fila horizontal -->
                      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-top: 5px;">
                          
                          <!-- Tu párrafo original con el botón de colapso estilizado al formato SmartAdmin -->
                          <p style="margin: 0;">
                              <button class="btn btn-default btn-sm" type="button" data-toggle="collapse" data-target=".multi-collapse" aria-expanded="false" aria-controls="multiCollapseExample1 multiCollapseExample2" style="font-weight: bold; color: #334155; border-color: #cbd5e1; background: #ffffff; padding: 6px 12px;">
                                  <i class="fa fa-arrows-v"></i> LISTA DE OBJETIVOS REGIONALES ALINEADOS
                              </button>
                          </p>
                          
                          <!-- Tu enlace original "VOLVER" adaptado estéticamente a la botonera formal -->
                          <a href="' . site_url("admin/proy/list_proy") . '" 
                             title="VOLVER AL MENÚ ANTERIOR" 
                             class="btn btn-default btn-sm" 
                             style="font-weight: bold; color: #475569; border-color: #cbd5e1; background: #ffffff; padding: 6px 12px; display: inline-block;">
                              <i class="fa fa-arrow-left"></i> VOLVER
                          </a>
                          
                      </div>
                      
                      <!-- Tu contenedor collapse sin alterar identificadores -->
                      <div class="collapse multi-collapse" id="multiCollapseExample1" style="margin-top: 5px;">
                          <!-- Tu tarjeta adaptada con bordes discontinuos elegantes para resaltar el contenido -->
                          <div class="card card-body well" style="background: #f8fafc; border: 1px dashed #cbd5e1; padding: 12px; margin-bottom: 0; box-shadow: none;">
                              ' . $this->verif_oregional($proy_id) . '
                          </div>
                      </div>
                      
                  </div>
              </section>
          </article>

          <section id="widget-grid" class="">
            <div class="row">
              <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <!-- Widget ID (each widget will need unique ID)-->
                <div class="jarviswidget jarviswidget-color-darken" >
                  <header>
                    <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                    <h2 class="font-md"><strong>MIS UNIDADES RESPONSABLES</strong></h2>               
                  </header>
                  <div>
                    <div class="widget-body no-padding">
                        '.$button.'
                      <div class="table-responsive">
                        '.$this->unidades_resp($proy_id).'
                      </div>
                    </div>
                    <!-- end widget content -->
                  </div>
                  <!-- end widget div -->
                </div>
                <!-- end widget -->
              </article>
            <!-- WIDGET END -->
            </div>
          </section>';

          $data['listado']=$listado;
          $this->load->view('admin/programacion/componente/list_componentes', $data);
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }
    }





    //// PARA MIGRACION DE REQUERIMIENTOS POR ARCHIVO EXCEL 2026
    public function valida_add_requerimientos() {
      ini_set('max_execution_time', 300); // 5 minutos
      ini_set('memory_limit', '512M');    // Aumentar memoria

      $this->load->library('excel'); // Carga el archivo que creamos arriba
     // $path = $_FILES['archivo']['tmp_name'];
      $cite_id = $this->input->post('cite_id');
      $cite = $this->model_modrequerimiento->get_cite_insumo($cite_id);

      // Validar que el CITE exista antes de seguir
      if (empty($cite)) {
        echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información del CITE. Verifique su sesión.')));
        return;
      }

      $archivo = $_FILES['archivo']['tmp_name'];
      $errores = array();
      $data_insertar = array();

      try {
          $archivoTipo = PHPExcel_IOFactory::identify($archivo);
          $lector = PHPExcel_IOFactory::createReader($archivoTipo);
          $phpExcel = $lector->load($archivo);
          $hoja = $phpExcel->getSheet(0);
          $filasMax = $hoja->getHighestRow();
          // --- 1. VALIDACIÓN DE ESTRUCTURA (Columnas) ---
          // Obtener la última columna con datos (ej: 'S') y convertirla a número (19)
          $columnaMaxLetra = $hoja->getHighestDataColumn(); 
          $totalColumnas = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
          $limitePermitido = 20; // Columna T es la 20

          if (($totalColumnas > $limitePermitido) || ($totalColumnas < $limitePermitido)) {
              echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. Solo se permiten $limitePermitido (hasta la 'T'). Por favor, elimine columnas sobrantes.")));
              return;
          }

          // --- 1. VALIDACIÓN DE ENCABEZADOS (Columnas A a la T) ---
          // Verificamos las primeras columnas críticas para asegurar que sea el formato correcto
          if (trim($hoja->getCell('A1')->getValue()) != 'COD ACT' || 
              trim($hoja->getCell('B1')->getValue()) != 'PARTIDA' || 
              trim($hoja->getCell('G1')->getValue()) != 'TOTAL') {
              echo json_encode(array('status' => 'error', 'errors' => array('El formato del Excel no es válido. Verifique los encabezados.')));
              return;
          }

          // --- 2. VALIDACIÓN FILA POR FILA ---
          for ($i = 2; $i <= $filasMax; $i++) {
              // Extraer valores básicos
              $cod_act = $hoja->getCell('A' . $i)->getValue();
              $partida = $hoja->getCell('B' . $i)->getValue();
              $cantidad = $hoja->getCell('E' . $i)->getValue();
              //$precio = $hoja->getCell('F' . $i)->getValue();
              //$total   = $hoja->getCell('G' . $i)->getOldCalculatedValue() ? $hoja->getCell('G' . $i)->getCalculatedValue() : $hoja->getCell('G' . $i)->getValue();
              $precio_crudo = $hoja->getCell('F' . $i)->getCalculatedValue();
              $precio = ($precio_crudo !== NULL && trim($precio_crudo) !== '') ? trim($precio_crudo) : 0;

              // AJUSTE: Extracción calculada del TOTAL resolviendo fórmulas en caliente
            $celda_total = $hoja->getCell('G' . $i)->getCalculatedValue();
            $total = (!empty($celda_total) && is_numeric($celda_total)) ? floatval($celda_total) : 0.00;



              if($total!=($cantidad*$precio)){
                $errores[] = "Fila $i: Error en el Costo Total != (Cantidad*Precio) verificar los valores..";
              }


              // --- VALIDACION CODIGO DE ACTIVIDAD---
              if (!empty($cod_act)) {
                  $get_form4=$this->model_producto->verif_form4_vigente_para_alineacion($cite[0]['com_id'],$cod_act);
                  if(count($get_form4)==1){
                    $prod_id=$get_form4[0]['prod_id'];
                  }
                  else{
                    $errores[] = "Fila $i: sin Actividad disponible para su alineacion, revisar el codigo de Actividad.";
                  }
              } else {
                  $errores[] = "Fila $i: 'CODIGO DE ACTIVIDAD' es obligatoria.";
              }

              // --- NUEVA VALIDACIÓN: TAMAÑO DE PARTIDA ---
              if (!empty($partida)) {
                  // strlen cuenta cuántos caracteres tiene la cadena

                  if (strlen($partida) != 5) {
                      $errores[] = "Fila $i: La 'PARTIDA' ($partida) debe tener exactamente 5 caracteres (tiene " . strlen($partida) . ").";
                  }
                  else{
                    $get_partida=$this->model_partidas->dato_par_codigo($partida);
                    if(count($get_partida)==1){
                      if(count($this->model_ptto_sigep->vista_get_seguimiento_partida_UOrganizacional($cite[0]['aper_id'],$get_partida[0]['par_id']))==0){
                        $errores[] = "Fila $i: Error !! la 'PARTIDA' ($partida) Nose encuentra asignado al programa, verifique la asignacion de partida..";
                      }
                    }
                    else{
                      $errores[] = "Fila $i: Error en el registro de la 'PARTIDA' ($partida) No existe en nuestra Base de Datos.";
                    }
                  }
              } else {
                  $errores[] = "Fila $i: 'PARTIDA' es obligatoria.";
              }


              if (!is_numeric($precio)) {
                    $errores[] = "Fila $i: El 'PRECIO UNITARIO' debe ser un valor numérico válido.";
                } else {
                    $precio_float = floatval($precio);
                    
                    // Verificación matemática: Multiplicamos por 100 y evaluamos si queda un residuo decimal
                    // Si multiplicamos 10.55 * 100 = 1055 (Entero, residuo 0) -> OK
                    // Si multiplicamos 10.553 * 100 = 1055.3 (Flotante, tiene residuo) -> ERROR
                    if (floor($precio_float * 100) != ($precio_float * 100)) {
                        $errores[] = "Fila $i: El 'PRECIO UNITARIO' ($precio) excede el límite permitido. Solo se aceptan hasta 2 decimales (Ej: 10.55).";
                    }
                }

              // Validaciones básicas
              if (empty($cod_act)) $errores[] = "Fila $i: 'COD ACT' es obligatorio.";
              if (empty($partida)) $errores[] = "Fila $i: 'PARTIDA' es obligatoria.";
              if (!is_numeric($total)) $errores[] = "Fila $i: El 'TOTAL' debe ser un número.";

              // --- 3. VALIDACIÓN DE MESES (Columnas G a R) ---
              $suma_meses = 0;
              $columnas_meses = array('H','I','J','K','L','M','N','O','P','Q','R','S');
              
              ///----------
              foreach ($columnas_meses as $col) {
                // Se evalúa la ecuación mensual directa en caliente
                $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                
                // Si la celda con fórmula o vacía no tiene valor, la homologamos a 0 puros
                $val_mes = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);

                if (!is_numeric($val_mes)) {
                    $errores[] = "Fila $i: Valor no numérico detectado en el mes de la columna '$col'.";
                    break;
                }
                $suma_meses += floatval($val_mes);
              }
              ///----------

              // Validación de integridad: ¿La suma de los meses coincide con el TOTAL?
              if (abs($suma_meses - $total) > 0.01) { // Usamos margen por decimales
                  $errores[] = "Fila $i: La suma de los meses ($suma_meses) no coincide con el TOTAL ($total).";
              }

              if (empty($errores)) {
                  // Preparamos el array para PostgreSQL
                  $data_insertar[] = array(
                      'ins_codigo'   => $this->session->userdata("name").'/REQ/'.$this->gestion,
                      'ins_fecha_requerimiento' => date('d/m/Y'), /// Fecha de Requerimiento
                      'par_id'   => $get_partida[0]['par_id'],
                      'ins_detalle'   => strtoupper($hoja->getCell('C' . $i)->getValue()),
                      'ins_unidad_medida'    => strtoupper($hoja->getCell('D' . $i)->getValue()),
                      'ins_cant_requerida'    => $hoja->getCell('E' . $i)->getValue(),
                      'ins_costo_unitario'      => round(floatval($precio), 2),
                      //'ins_costo_unitario'    => $hoja->getCell('F' . $i)->getValue(),
                      'ins_costo_total'     => $total,
                      'ins_observacion'=> $hoja->getCell('T' . $i)->getValue(),
                      'ins_tipo_modificacion' => $cite[0]['tipo_modificacion'], /// tipo modificacion
                      'fun_id' => $this->fun_id, /// Funcionario
                      'aper_id' => $cite[0]['aper_id'], /// aper id
                      'com_id' => $cite[0]['com_id'], /// com id 
                      'form4_cod' => $cod_act, /// cod act
                      'ins_mod' => 2, /// mod
                      'num_ip' => $this->input->ip_address(), 
                      'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                  );

                      // Creamos un vector temporal con los meses para esta fila
                  $meses_vector = array(
                      1  => $hoja->getCell('H' . $i)->getCalculatedValue(),
                      2  => $hoja->getCell('I' . $i)->getCalculatedValue(),
                      3  => $hoja->getCell('J' . $i)->getCalculatedValue(),
                      4  => $hoja->getCell('K' . $i)->getCalculatedValue(),
                      5  => $hoja->getCell('L' . $i)->getCalculatedValue(),
                      6  => $hoja->getCell('M' . $i)->getCalculatedValue(),
                      7  => $hoja->getCell('N' . $i)->getCalculatedValue(),
                      8  => $hoja->getCell('O' . $i)->getCalculatedValue(),
                      9  => $hoja->getCell('P' . $i)->getCalculatedValue(),
                      10 => $hoja->getCell('Q' . $i)->getCalculatedValue(),
                      11 => $hoja->getCell('R' . $i)->getCalculatedValue(),
                      12 => $hoja->getCell('S' . $i)->getCalculatedValue()
                  );
              }
              if (count($errores) > 15) break; // Límite de errores para no saturar
          }
          // --- 4. INSERCIÓN FINAL ---
          if (ob_get_length()) ob_clean(); 
          header('Content-Type: application/json');
          ob_clean();
          if (empty($errores) && !empty($data_insertar)) {
              $this->db->trans_start(); // Iniciar transacción en Postgres
              
              foreach ($data_insertar as $fila) {
                  // Cambia 'tu_tabla_requerimientos' por el nombre real de tu tabla
                  $this->db->insert('insumos', $fila);
                  $ins_id=$this->db->insert_id();
                  /*-----------------------------------------------*/
                  $data_to_store2 = array( ///// Tabla InsumoProducto
                    'prod_id' => $prod_id, /// prod id 
                    'ins_id' => $ins_id, /// ins_id
                  );
                  $this->db->insert('_insumoproducto', $data_to_store2);
                  /*---------------------------------------------*/
                    /*------------ REGISTRO DE LA TEMPORALIDAD ---------*/
                      for ($i=1; $i <=12 ; $i++) {
                        $pfin=$this->security->xss_clean($meses_vector[$i]);
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
                    /*------------------------------------------*/
                    /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
                    if($this->copia_insumo($cite_id,$ins_id,1)){ /// inserta historial reporte
                      /*---- iNSERT AUDI ADICIONAR INSUMOS ---*/
                        $this->update_activo_modificacion($cite_id);
                      /*--------------------------------------*/
                    }
              }
              $this->db->trans_complete();
              if ($this->db->trans_status() === FALSE) {
                  echo json_encode(array(
                      'status' => 'error', 
                      'errors' => array('Error al insertar en la base de datos (Transacción fallida).')
                  ));
              } else {
                  echo json_encode(array(
                      'status' => 'success', 
                      'msj' => 'Importación finalizada con éxito.',
                      'conteo' => count($data_insertar) 
                  ));
              }
          } else {
              // Si hay errores de validación o no hay datos
              echo json_encode(array(
                  'status' => 'error', 
                  'errors' => !empty($errores) ? $errores : array('El archivo parece estar vacío o no tiene datos válidos.')
              ));
          }
          exit; 
      } catch (Exception $e) {
          echo json_encode(array('status' => 'error', 'errors' => array('Excepción: ' . $e->getMessage())));
      }
    }



    /*----------- VERIFICA LA ALINEACION DE OBJETIVO REGIONAL -----*/
    public function verif_oregional($proy_id){
        $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
        $tabla='';
        $nro=0;
        foreach($list_oregional as $row){
            $nro++;
            $tabla.='<h1> '.$nro.'.- OPERACI&Oacute;N REGIONAL : <small> '.$row['or_codigo'].' | '.$row['or_codigo'].' .- '.$row['or_objetivo'].'</small></h1>';
        }

        return $tabla;
    }



    /*-------- VERIFICACION DE CODIGO COMPONENTE (PI) --------*/
    function verif_codigo_componente(){
      if($this->input->is_ajax_request()){
          $post = $this->input->post();
          $codigo = $this->security->xss_clean($post['cod']); /// Codigo
          $pfec_id = $this->security->xss_clean($post['pfec_id']); /// pfec id
          $fase = $this->model_faseetapa->get_fase($pfec_id);
          $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']); 

          $variable= $this->model_componente->get_fase_componente_nro($pfec_id,$codigo,1);
          if(count($variable)==0){
            echo "true"; /// Codigo Habilitado
          }
          else{
            echo "false"; /// No Existe Registrado
          }
      }else{
        show_404();
      }
    }


/*---- UNIDADES RESPONSABLES (2024) a optimizar ---------*/
  function unidades_resp($proy_id){
    $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
    $componente=$this->model_componente->lista_UnidadesResponsables($proy_id);
    $tabla='';
    $tabla.='<table id="dt_basic4" class="table table table-bordered" width="100%">
                <thead>
                    <tr style="height:45px;">
                        <th style="width:1%;">#</th>
                        <th style="width:5%;">COD. UNIDAD</th>
                        <th style="width:20%;">UNIDAD RESPONSABLE</th>
                        <th style="width:5%;">PONDERACI&Oacute;N</th>
                        <th style="width:5%;">NRO. ACT.</th>
                        <th style="width:5%;">MIS ACTIVIDADES</th>
                        <th style="width:5%;">FORM. POA N 4</th>
                        <th style="width:5%;">FORM. POA N 5</th>
                        <th style="width:5%;">EXCEL ACTIVIDADES</th>
                        <th style="width:5%;">ELIMINAR ACTIVIDADES </th>
                    </tr>
                </thead>
                <tbody>';
                $num=0; $ponderacion=0; $sum=0;
                foreach($componente as $row){
                    $num++;
                    $tabla.='
                    <tr>';
                        if(count($this->model_producto->lista_productos($row['com_id']))==0 & $this->tp_adm==1){
                            $tabla.='<td><a href="#" data-toggle="modal" data-target="#modal_neg_ff" class="btn btn-default neg_ff" title="DESHABILITAR SUB-ACTIVIDAD"  name="'.$row['com_id'].'" id="'.count($this->model_producto->lista_productos($row['com_id'])).'" ><img src="' . base_url() . 'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></td>';
                        }
                        else{
                            if($this->fun_id==399){
                                $tabla.='<td>';
                                $tp_sact = $this->model_componente->tp_subactividad(); // tp de subactividad
                                  $tabla .='<select class="form-control" onchange="doSelectAlert(event,this.value,'.$row['com_id'].');">';
                                    foreach($tp_sact as $pr){
                                        if($pr['tp_sact']==$row['tp_sact']){
                                          $tabla .="<option value=".$pr['tp_sact']." selected>".$pr['tipo_subactividad']."</option>";
                                        }
                                        else{
                                          $tabla .="<option value=".$pr['tp_sact'].">".$pr['tipo_subactividad']."</option>"; 
                                        }
                                    }
                                  $tabla.='</select>';
                                $tabla.='</td>';
                            }
                            else{
                                $tabla.='<td>'.$num.'</td>';
                            }
                        }
                        $tabla.='
                        <td bgcolor="#d4f1fb" align="center" title="'.$row["com_id"].'"><font color="blue" size=3><b>'.$row['serv_cod'].'</b></font></td>
                        <td>'.$row['serv_descripcion'].'</td>
                        <td>'.$row['com_ponderacion'].' %</td>
                        <td align=center bgcolor="#bee6e1"><font size=2 color=blue>'.count($this->model_producto->lista_productos($row['com_id'])).'</font></td>
                        <td align="center">
                            <a href="'.site_url("admin").'/prog/list_prod/'.$row['com_id'].'" title="MIS ACTIVIDADES" class="btn btn-default" target=_black><img src="'.base_url().'assets/ifinal/archivo.png" WIDTH="34" HEIGHT="34"/></a>
                        </td>
                        <td align="center"><a href="javascript:abreVentana(\''.site_url("").'/prog/rep_operacion_componente/'.$row['com_id'].'\');" title="REPORTE POA FORM 4" class="btn btn-default"><img src="'.base_url().'assets/ifinal/pdf.png" WIDTH="35" HEIGHT="35"/></a></td>
                        <td align="center"><a href="javascript:abreVentana(\''.site_url("").'/proy/orequerimiento_proceso/'.$row['com_id'].'\');" title="REPORTE POA FORM 5" class="btn btn-default"><img src="'.base_url().'assets/ifinal/pdf.png" WIDTH="35" HEIGHT="35"/></a></td>
                        <td align="center"><a href="'.site_url("").'/prog/exportar_productos/'.$row['com_id'].'" title="EXPORTAR ACTIVIDADES" class="btn btn-default"><img src="' . base_url() . 'assets/ifinal/excel.jpg" WIDTH="38"/></a></td>
                        <td align="center">';
                        if(count($this->model_producto->lista_productos($row['com_id']))!=0 & $this->tp_adm==1){
                            $tabla.='<a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default del_ff" title="ELIMINAR TODAS LAS ACTIVIDADES DE LA UNIDAD"  name="'.$row['com_id'].'" id="'.count($this->model_producto->lista_productos($row['com_id'])).'" ><img src="' . base_url() . 'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a>';
                        }
                        $tabla.='
                        </td>
                    </tr>';
                    $sum=$sum+count($this->model_producto->lista_productos($row['com_id']));
                    $ponderacion=$ponderacion+$row['com_ponderacion'];
                }
                $tabla.='    
                </tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>'.$ponderacion.'%</td>
                    <td align=center><b>'.$sum.'</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>';

    return $tabla;
    }

 //    /*------- DESHABILITAR SUB ACTIVIDAD (SERVICIO) ------*/
    function deshabilitar_sactividad(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $com_id = $this->security->xss_clean($post['com_id']);
          $productos = $this->model_producto->list_prod($com_id);

            $update_com= array(
                'fun_id' => $this->fun_id,
                'serv_id' => 0,
                'estado' => 3
            );
            $this->db->where('com_id', $com_id);
            $this->db->update('_componentes', $this->security->xss_clean($update_com));

            $dato_comp = $this->model_componente->get_componente($com_id,$this->gestion);
            if($dato_comp[0]['estado']==3){
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


    /*---- CONSOLIDADO DE OPERACIONES POR SUB ACTIVIDADES, COMPONENTES (2019)----*/
    public function reporte_consolidado_operaciones_componentes($proy_id){
        $data['proyecto']=$this->model_proyecto->get_id_proyecto($proy_id);
        if(count($data['proyecto'])!=0){
            $data['mes'] = $this->mes_nombre();
            $data['componente_operaciones']=$this->get_proceso_consolidado($proy_id);
            $this->load->view('admin/programacion/componente/reporte_operaciones_componentes', $data);
        }
        else{
            echo "<center><b>ERROR!!!! AL GENERAR REPORTE</b></center>";
        }
    }

    /*------- LISTA DE OPERACIONES POR SUB ACTIVIDADES (2019) ------*/
    public function get_proceso_consolidado($proy_id){
      $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); //// DATOS DEL PROYECTO
      $fase = $this->model_faseetapa->get_id_fase($proy_id); //// DATOS FASE ACTIVA
      $componentes=$this->model_componente->componentes_id($fase[0]['id'],$proyecto[0]['tp_id']); /// COMPONENTES/PROCESOS  
        
        $tabla ='';
        if(count($componentes)!=0){
            foreach ($componentes as $rowc){
                $productos = $this->model_producto->list_prod($rowc['com_id']);
                if(count($productos)!=0){
                    $tabla .='
                    <table>
                        <tr><td><font size="1"> '.$rowc['serv_cod'].'.- '.$rowc['com_componente'].'</font></td></tr>
                    </table>';
                    $nro_p=0;
                    $tabla .='<table border="0" cellpadding="0" cellspacing="0" class="tabla">';
                        $tabla.='<thead>
                                <tr class="modo1" style="height:45px;">
                                <th style="width:1%;" bgcolor="#1c7368"><font color="#ffffff">#</font></th>';
                                if($this->gestion==2018){
                                  $tabla.='<th style="width:7%;" bgcolor="#1c7368"><font color="#ffffff">PRODUCTO</font></th>';
                                }
                                else{
                                  $tabla.='
                                      <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">OBJETIVO ESTRATEGICO</font></th>
                                      <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">ACCI&Oacute;N ESTRATEGICA</font></th>
                                      <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">OPERACI&Oacute;N</font></th>
                                      <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">RESULTADO</font></th>';
                                }
                                $tabla.='
                                <th style="width:2%;" bgcolor="#1c7368"><font color="#ffffff">TIP.</font></th>
                                <th style="width:8%;" bgcolor="#1c7368"><font color="#ffffff">INDICADOR</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">LINEA BASE</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">META</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">ENE.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">FEB.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">MAR.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">ABR.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">MAY.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">JUN.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">JUL.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">AGO.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">SEP.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">OCT.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">NOV.</font></th>
                                <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">DIC.</font></th>
                                <th style="width:8%;" bgcolor="#1c7368"><font color="#ffffff">VERIFICACI&Oacute;N</font></th>
                            </tr>
                            </thead>
                        <tbody>';
                        $nro=0;
                        foreach($productos as $rowp){
                          $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
                          $color='';
                            if(($sum[0]['meta_gest']+$rowp['prod_linea_base'])!=$rowp['prod_meta']){
                              $color='#fbd5d5';
                            }
                            $nro++;
                            $tabla.='<tr class="modo1" bgcolor="'.$color.'" style="height:45px;">';
                            $tabla.='<td style="width: 1%; text-align: center" style="height:14px;">'.$nro.'</td>';
                              if($this->gestion==2018){
                               $tabla.='<td style="width: 7%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_producto'].'', 'cp1252', 'UTF-8').'</td>'; 
                              }
                              else{
                                if($rowp['acc_id']!=null){
                                  $alineacion=$this->model_producto->operacion_accion($rowp['acc_id']);
                                  if(count($alineacion)!=0){
                                    $tabla.=' <td style="width: 9%; text-align: left">'.$alineacion[0]['obj_codigo'].'-'.$alineacion[0]['obj_descripcion'].'</td>
                                              <td style="width: 9%; text-align: left">'.$alineacion[0]['acc_codigo'].'-'.$alineacion[0]['acc_descripcion'].'</td>';
                                  }
                                  else{
                                    $tabla.=' <td style="width: 9%; text-align: left"></td>
                                              <td style="width: 9%; text-align: left"><font color="red">'.$rowp['acc_id'].'</font></td>';
                                  }
                                }
                                else{
                                  $tabla.=' <td style="width: 9%; text-align: left"></td>
                                            <td style="width: 9%; text-align: left"><font color="red"></font></td>';
                                }
                                $tabla.='<td style="width: 9%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_producto'].'', 'cp1252', 'UTF-8').'</td>
                                         <td style="width: 9%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_resultado'].'', 'cp1252', 'UTF-8').'</td>';
                              }
                              
                              
                              $tabla.='
                                       <td style="width: 2%; text-align: left">'.mb_convert_encoding(''.$rowp['indi_abreviacion'].'', 'cp1252', 'UTF-8').'</td>
                                       <td style="width: 8%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_indicador'].'', 'cp1252', 'UTF-8').'</td>
                                       <td style="width: 3%; text-align: left">'.$rowp['prod_linea_base'].'</td>
                                       <td style="width: 3%; text-align: left">'.$rowp['prod_meta'].'</td>';
                                       $tabla.=''.$this->temporalizacion_prod($rowp['prod_id'],$this->gestion).'';
                              $tabla .='<td style="width: 8%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_fuente_verificacion'].'', 'cp1252', 'UTF-8').'</td>';         
                            $tabla.='</tr>';
                        }
                        $tabla.='
                        </tbody>
                    </table>'; 
                }
            }
        }

      return $tabla;
    }

     /*--------- TEMPORALIDAD PROGRAMACION FISICA (2019)---------*/
    public function temporalizacion_prod($prod_id,$gestion){
        $prod=$this->model_producto->get_producto_id($prod_id); /// Producto Id
        $programado=$this->model_producto->producto_programado($prod_id,$gestion); /// Producto Programado
        $tp='';
        if($prod[0]['indi_id']==2){$tp='%';};
        $m[0]='g_id';
        $m[1]='enero';
        $m[2]='febrero';
        $m[3]='marzo';
        $m[4]='abril';
        $m[5]='mayo';
        $m[6]='junio';
        $m[7]='julio';
        $m[8]='agosto';
        $m[9]='septiembre';
        $m[10]='octubre';
        $m[11]='noviembre';
        $m[12]='diciembre';

        for ($i=1; $i <=12 ; $i++) { 
            $prog[1][$i]=0;
            $prog[2][$i]=0;
            $prog[3][$i]=0;
        }

        $pa=0;
        if(count($programado)!=0){
            for ($i=1; $i <=12 ; $i++) { 
                $prog[1][$i]=$programado[0][$m[$i]];
/*                $pa=$pa+$prog[1][$i];
                $prog[2][$i]=$pa+$prod[0]['prod_linea_base'];

              if($prod[0]['prod_meta']!=0){
                $prog[3][$i]=round(((($pa+$prod[0]['prod_linea_base'])/$prod[0]['prod_meta'])*100),1);
              } */ 
            } 
        }
        $tr_return = '';
          for($i = 1 ;$i<=12 ;$i++){
            $tr_return .= '<td bgcolor="#d2f5d2" style="width: 3%; text-align: right" title="'.$m[$i].'"><b>'.$prog[1][$i].''.$tp.'</b></td>';
          }
                                 
        return $tr_return;
    }

    // public function actividades($prod_id){
    //    $actividad=$this->model_actividad->list_act_anual($prod_id); /// Actividad
    //    $tabla='';
    //    $nro_a=0;
    //    if(count($actividad)!=0){
    //         foreach ($actividad as $row){
    //             $nro_a++;
    //             $tabla.='<tr class="modo1" bgcolor="#e5f3f1">';
    //                 $tabla.='<td>'.$nro_a.'</td>';
    //                 $tabla.='<td></td>';
    //                 $tabla.='<td>'.$row['act_actividad'].'</td>';
    //                 $tabla.='<td>'.$row['indi_abreviacion'].'</td>';
    //                 $tabla.='<td>'.$row['act_indicador'].'</td>';
    //                 $tabla.='<td>'.round($row['act_linea_base'],2).'</td>';
    //                 $tabla.='<td>'.round($row['act_meta'],2).'</td>';
    //                 $tabla.='<td>'.$row['act_ponderacion'].' %</td>';
    //                 $tabla.='<td>'.$row['act_fuente_verificacion'].'</td>';
    //                 $tabla.='<td>'.$this->temporalizacion_act($row['act_id'],$this->session->userdata('gestion')).'</td>';
    //             $tabla.='</tr>';
    //         }
    //    }

    //    return $tabla;
    // }

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
    /*----------------------------------- ACTIVIDADES ----------------------------*/
    // public function temporalizacion_act($act_id,$gestion){
    //     $act=$this->model_actividad->get_actividad_id($act_id); /// programado
    //     $programado=$this->model_actividad->actividad_programado($act_id,$gestion); /// Actividad Programado

    //     $m[0]='g_id';
    //     $m[1]='enero';
    //     $m[2]='febrero';
    //     $m[3]='marzo';
    //     $m[4]='abril';
    //     $m[5]='mayo';
    //     $m[6]='junio';
    //     $m[7]='julio';
    //     $m[8]='agosto';
    //     $m[9]='septiembre';
    //     $m[10]='octubre';
    //     $m[11]='noviembre';
    //     $m[12]='diciembre';

    //     for ($i=1; $i <=12 ; $i++) { 
    //         $prog[1][$i]=0;
    //         $prog[2][$i]=0;
    //         $prog[3][$i]=0;
    //     }

    //     $pa=0;
    //     if(count($programado)!=0){
    //         for ($i=1; $i <=12 ; $i++) { 
    //             $prog[1][$i]=$programado[0][$m[$i]];
    //            /* $pa=$pa+$prog[1][$i];
    //             $prog[2][$i]=$pa+$act[0]['act_linea_base'];

    //           if($act[0]['act_meta']!=0){
    //             $prog[3][$i]=round(((($pa+$act[0]['act_linea_base'])/$act[0]['act_meta'])*100),2);
    //           }  */
    //         } 
    //     }
        
    //     $tr_return = '';
    //     $tr_return .= '<table>
    //                     <thead>
    //                     <tr>
    //                           <th style="width:6%;"></th>
    //                           <th style="width:7%;">Ene.</th>
    //                           <th style="width:7%;">Feb.</th>
    //                           <th style="width:7%;">Mar.</th>
    //                           <th style="width:7%;">Abr.</th>
    //                           <th style="width:7%;">May.</th>
    //                           <th style="width:7%;">Jun.</th>
    //                           <th style="width:7%;">Jul.</th>
    //                           <th style="width:7%;">Agos.</th>
    //                           <th style="width:7%;">Sept.</th>
    //                           <th style="width:7%;">Oct.</th>
    //                           <th style="width:7%;">Nov.</th>
    //                           <th style="width:7%;">Dic.</th>
    //                     </tr>
    //                     </thead>
    //                     <tbody>
    //                       <tr>
    //                       <td>P.</td>';
    //                       for($i = 1 ;$i<=12 ;$i++)
    //                       {
    //                         $tr_return .= '<td>'.$prog[1][$i].'</td>';
    //                       }
    //                       $tr_return .= '
    //                       </tr>
    //                     </tbody>
    //                 </table>';
    //     return $tr_return;
    // }

    function estilo_vertical(){
        $estilo_vertical = '<style>
        .saltopagina{page-break-after:always;}
        body{
            font-family: sans-serif;
            }
        table{
            font-size: 7px;
            width: 100%;
            background-color:#fff;
        }
        .mv{font-size:10px;}
        .verde{ width:100%; height:5px; background-color:#1c7368;}
        .blanco{ width:100%; height:5px; background-color:#F1F2F1;}
        .siipp{width:120px;}

        .titulo_pdf {
            text-align: left;
            font-size: 7px;
        }
        .tabla {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 7px;
        width: 100%;

        }
        .tabla th {
        padding: 2px;
        font-size: 7px;
        background-color: #1c7368;
        background-repeat: repeat-x;
        color: #FFFFFF;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-right-style: solid;
        border-bottom-style: solid;
        border-right-color: #558FA6;
        border-bottom-color: #558FA6;
        font-family: "Trebuchet MS", Arial;
        text-transform: uppercase;
        }
        .tabla .modo1 {
        font-size: 7px;
        font-weight:bold;
       
        background-image: url(fondo_tr01.png);
        background-repeat: repeat-x;
        color: #34484E;
        font-family: "Trebuchet MS", Arial;
        }
        .tabla .modo1 td {
        padding: 1px;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-right-style: solid;
        border-bottom-style: solid;
        border-right-color: #A4C4D0;
        border-bottom-color: #A4C4D0;
        }
    </style>';
        return $estilo_vertical;
    }


   //    /*--------------- GENERA MENU -------------*/
    public function genera_menu($proy_id){
        $id_f = $this->model_faseetapa->get_id_fase($proy_id);
        $enlaces=$this->menu_modelo->get_Modulos_programacion(2);
        $tabla='';
        $tabla.='<nav>
                <ul>
                    <li>
                        <a href='.site_url("admin").'/dashboard'.' title="MENU PRINCIPAL"><i class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
                    </li>
                    <li class="text-center">
                        <a href='.base_url().'index.php/admin/proy/mis_proyectos/1'.' title="PROGRAMACI&Oacute;N POA"> <span class="menu-item-parent">PROGRAMACI&Oacute;N POA</span></a>
                    </li>';
                    if(count($id_f)!=0){
                        for($i=0;$i<count($enlaces);$i++){ 
                            $tabla.='
                            <li>
                                <a href="#" >
                                    <i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>
                                <ul >';
                                $submenu= $this->menu_modelo->get_Modulos_sub($enlaces[$i]['o_child']);
                                foreach($submenu as $row) {
                                   $tabla.='<li><a href='.base_url($row['o_url'])."/".$id_f[0]['proy_id'].'>'.$row['o_titulo'].'</a></li>';
                                }
                            $tabla.='</ul>
                            </li>';
                        }
                    }
                $tabla.='
                </ul>
            </nav>';

        return $tabla;
    }
}