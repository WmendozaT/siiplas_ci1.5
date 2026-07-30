<?php
class Cobjetivo_regional extends CI_Controller {
  public $rol = array('1' => '3','2' => '4');  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
        $this->load->library('pdf');
        $this->load->library('pdf2');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('resultados/model_resultado');
        $this->load->model('mestrategico/model_mestrategico');
        $this->load->model('mestrategico/model_objetivogestion');
        $this->load->model('mestrategico/model_objetivoregion');
        $this->load->model('menu_modelo');
        $this->load->model('Users_model','',true);
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        $this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userData('fun_id');
        $this->dep_id = $this->session->userData('dep_id');

        $this->load->library('oregional');
        //$this->load->CI_Controller('reporte_evaluacion/crep_evalunidad');
      }else{
          redirect('/','refresh');
      }
    }

    
    /*----- LISTA DE OBJETIVOS REGIONALES (OPERACIONES)----*/
    public function objetivos_regional($og_id){
      $data['menu']=$this->oregional->menu(1);
      $data['ogestion']=$this->model_objetivogestion->get_objetivosgestion($og_id);
      if(count($data['ogestion'])!=0){
        $data['obj_estrategico']=$this->model_mestrategico->get_objetivos_estrategicos($data['ogestion'][0]['oe_id']);
        $data['regionales']=$this->oregional->regionales_seleccionados($og_id);

        $this->load->view('admin/mestrategico/objetivos_region/list_oregion', $data);
      }
      else{
        redirect(site_url("").'/me/mis_ogestion');
      }
      
    }



    /*---------- FORMULARIO ADD OBJ. REGIONAL ------------*/
    public function form_oregional($dep_id,$og_id){
      $data['menu']=$this->oregional->menu(1);
      $data['ogestion']=$this->model_objetivogestion->get_objetivosgestion($og_id);
      //$data['accion_estrategica']=$this->model_mestrategico->get_acciones_estrategicas($data['ogestion'][0]['acc_id']);
      $data['obj_estrategico']=$this->model_mestrategico->get_objetivos_estrategicos($data['ogestion'][0]['oe_id']);
      $data['regional']=$this->model_proyecto->get_departamento($dep_id);
      
      $data['formulario']=$this->oregional->formulario_add($dep_id,$og_id);
      $this->load->view('admin/mestrategico/objetivos_region/form_oregional', $data);
    }

    /*------ CAMBIA ALINEACION A ACP 2024---------*/
    function cambia_alineacion_acp(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('select_og_id', 'Objetivo Regional', 'required|trim');
          $this->form_validation->set_message('required', 'El campo es es obligatorio');
        
          $post = $this->input->post();
          $select_og_id= $this->security->xss_clean($post['select_og_id']);
          $or_id= $this->security->xss_clean($post['or_id']);
          $dep_id= $this->security->xss_clean($post['dep_id']);
          
          $get_form2=$this->model_objetivoregion->get_form2_oregional($or_id); /// get operacion (formulario 2)
          $get_form1=$this->model_objetivoregion->list_oregional_regional($select_og_id,$dep_id); /// get acp donde se va a cambiar


          $update_form2 = array(
            'pog_id' => $get_form1[0]['pog_id'],
          );
          $this->db->where('or_id', $or_id);
          $this->db->update('objetivos_regionales', $update_form2);
    
      }else{
          show_404();
      }
    }

    /*------ CAMBIAR PRIORIZACION---------*/
    function update_priorizar_form2(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('select_og_id', 'Objetivo Regional', 'required|trim');
          $this->form_validation->set_message('required', 'El campo es es obligatorio');
        
          $post = $this->input->post();
          $select_og_id= $this->security->xss_clean($post['select_og_id']); // priori
          $or_id= $this->security->xss_clean($post['or_id']); /// or_id
          $dep_id= $this->security->xss_clean($post['dep_id']); /// dep_id
          

          $update_form2 = array(
            'or_priorizado' => $select_og_id,
          );
          $this->db->where('or_id', $or_id);
          $this->db->update('objetivos_regionales', $update_form2);
    
      }else{
          show_404();
      }
    }

    /*------ CAMBIAR PRIORIZACION 2---------*/
    function update_priorizar2_form2(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('select_og_id', 'Objetivo Regional', 'required|trim');
          $this->form_validation->set_message('required', 'El campo es es obligatorio');
        
          $post = $this->input->post();
          $select_og_id= $this->security->xss_clean($post['select_og_id']); // priori
          $or_id= $this->security->xss_clean($post['or_id']); /// or_id
          $dep_id= $this->security->xss_clean($post['dep_id']); /// dep_id
          

          $update_form2 = array(
            'or_priorizado2' => $select_og_id,
          );
          $this->db->where('or_id', $or_id);
          $this->db->update('objetivos_regionales', $update_form2);
    
      }else{
          show_404();
      }
    }
    
    /*---------- FORMULARIO UPDATE OBJ. REGIONAL ------------*/
    public function form_update_oregional($or_id){
      $data['menu']=$this->oregional->menu(1);
      $data['oregion']=$this->model_objetivoregion->get_objetivosregional($or_id); /// Objetivo Regional
      $data['ogestion']=$this->model_objetivogestion->get_objetivosgestion($data['oregion'][0]['og_id']); /// Objetivo de Gestion
      //$data['accion_estrategica']=$this->model_mestrategico->get_acciones_estrategicas($data['ogestion'][0]['acc_id']);
      $data['obj_estrategico']=$this->model_mestrategico->get_objetivos_estrategicos($data['ogestion'][0]['oe_id']);
      $data['regional']=$this->model_proyecto->get_departamento($data['oregion'][0]['dep_id']);
      
      $data['formulario']=$this->oregional->formulario_update($data['oregion']);
      $this->load->view('admin/mestrategico/objetivos_region/form_update_oregional', $data);
    }



    /*--- VALIDA ADD / UPDATE OBJETIVO REGIONAL ---*/
    public function add_ogestion(){
      if($this->input->post()) {
        $post = $this->input->post();
        $tp = $this->security->xss_clean($post['tp']); /// tipo id

       // $objetivo = $this->security->xss_clean($post['oregional']); /// Objetivo
       // $observacion = $this->security->xss_clean($post['observaciones']); /// Observacion
        $meta_reg = $this->security->xss_clean($post['meta_reg']); /// Meta regional

        if($tp==1){ //// INSERT
          $pog_id = $this->security->xss_clean($post['pog_id']); /// pog id
          $dep_id = $this->security->xss_clean($post['dep_id']); /// dep id
          $ogestion=$this->model_objetivogestion->get_objetivo_temporalidad($pog_id);
          $data_to_store = array(
            'pog_id' => $pog_id,
            'or_objetivo' => strtoupper($this->security->xss_clean($post['oregional'])),
            'or_producto' => strtoupper($this->security->xss_clean($post['producto'])),
            'or_codigo' => $this->security->xss_clean($post['codigo']),
            'or_resultado' => strtoupper($this->security->xss_clean($post['resultado'])),
            'indi_id' => 1,
            'or_indicador' => strtoupper($this->security->xss_clean($post['indicador'])),
            'or_linea_base' => $this->security->xss_clean($post['lbase']),
            'or_meta' => $this->security->xss_clean($post['meta']),
            'or_verificacion' => strtoupper($this->security->xss_clean($post['mverificacion'])),
            'or_observacion' => strtoupper($this->security->xss_clean($post['observaciones'])),
            'g_id' => $this->gestion,
            'fun_id' => $this->fun_id,
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
          );
          $this->db->insert('objetivos_regionales', $data_to_store);
          $or_id=$this->db->insert_id();

          /*-------- REGISTRANDO UNIDADES, ESTABLECIMIENTOS -----*/
          if (!empty($_POST["act_id"]) && is_array($_POST["act_id"])) {
            foreach ( array_keys($_POST["act_id"]) as $como){
              $estado=0;
              $prog_fis=0;
              if($_POST["uni_id"][$como]!=0){
                if($meta_reg!=0){
                  $estado=1;
                }
                $prog_fis=$_POST["uni_id"][$como];
              }
              
              $data_to_store4 = array( 
                'or_id' => $or_id, /// or id
                'act_id' => $_POST["act_id"][$como], /// act id 
                'prog_fis' => $prog_fis, /// Valor prog
                'g_id' => $this->gestion, /// Gestion
                'or_estado' => $estado, /// Estado
              );
              $this->db->insert('objetivo_regional_programado', $data_to_store4);
            }
          }
          /*----------------------------------------------------*/

        }
        else{ //// UPDATE
          $or_id = $this->security->xss_clean($post['or_id']); /// or id

          $update_or= array(
            'or_objetivo' => strtoupper($this->security->xss_clean($post['oregional'])),
            'or_producto' => strtoupper($this->security->xss_clean($post['producto'])),
            'or_indicador' => strtoupper($this->security->xss_clean($post['indicador'])),
            'or_resultado' => strtoupper($this->security->xss_clean($post['resultado'])),
            'or_linea_base' => $this->security->xss_clean($post['lbase']),
            'indi_id' => $this->security->xss_clean($post['indi_id']),
            'or_meta' => $this->security->xss_clean($post['meta']),
            'or_verificacion' => strtoupper($this->security->xss_clean($post['mverificacion'])),
            'or_observacion' => strtoupper($this->security->xss_clean($post['observaciones'])),
            'or_codigo' => $this->security->xss_clean($post['codigo']),
            'estado' => 2,
            'fun_id' => $this->fun_id,
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
          );
          $this->db->where('or_id', $or_id);
          $this->db->update('objetivos_regionales', $update_or);


          if (!empty($_POST["act_id"]) && is_array($_POST["act_id"])) {
            foreach ( array_keys($_POST["act_id"]) as $como){
              
              $estado=0;
              $prog_fis=0;
              if($_POST["uni_id"][$como]!=0){
                if($meta_reg!=0){
                  $estado=1;
                }
                $prog_fis=$_POST["uni_id"][$como];
              }

              $verif=$this->model_objetivoregion->get_unidad_programado($or_id,$_POST["act_id"][$como]);
              if(count($verif)!=0){ // Update

                $update_orp= array(
                  'prog_fis' => $prog_fis,
                  'or_estado' => $estado
                );
                $this->db->where('por_id', $verif[0]['por_id']);
                $this->db->update('objetivo_regional_programado', $update_orp);
              }
              else{ // Add
                //echo "add : ".$_POST["act_id"][$como]." - ".$_POST["tp_id"][$como]."<br>";
                $data_to_store4 = array( 
                  'or_id' => $or_id, /// or id
                  'act_id' => $_POST["act_id"][$como], /// act id 
                  'prog_fis' => $prog_fis, /// Valor prog
                  'g_id' => $this->gestion, /// Gestion
                  'or_estado' => $estado, /// Estado
                  'tp_id' => $_POST["tp_id"][$como], /// Estado
                );
                $this->db->insert('objetivo_regional_programado', $data_to_store4);
              }
            }
          }
        }

        $get_or=$this->model_objetivoregion->get_objetivosregional($or_id);

       // $obj_gestion=$this->model_objetivogestion->get_objetivo_temporalidad($pog_id);
        $this->session->set_flashdata('success','REGISTRO CORRECTO !!! ');
        redirect(site_url("").'/me/objetivos_regionales/'.$get_or[0]['og_id'].'#tabs-'.$get_or[0]['dep_id'].'');

      } else {
          show_404();
      }
    }

    /*---- ELIMINAR OBJETIVO REGIONAL ----*/
    function delete_oregional(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $or_id = $this->security->xss_clean($post['or_id']);

          $list_act_prog=$this->model_objetivoregion->list_actividades_oregional($or_id);
          foreach($list_act_prog  as $row){
            /*----- UPDATE TABLA PROYECTO ----*/
            $update_proy= array(
              'por_id' =>0
            );
            $this->db->where('por_id', $row['por_id']);
            $this->db->update('_proyectos', $update_proy);


            $this->db->where('por_id', $row['por_id']);
            $this->db->delete('proy_oregional');
          }

          // -----------------------
          $update_prod= array(
            'or_id' =>0
          );
          $this->db->where('or_id', $or_id);
          $this->db->update('_productos', $update_prod);
          // ----------------------


          $this->db->where('or_id', $or_id);
          $this->db->delete('objetivo_regional_programado');

          $this->db->where('or_id', $or_id);
          $this->db->delete('objetivos_regionales');

          $oregion=$this->model_objetivoregion->get_objetivosregional($or_id); 
          if(count($oregion)==0){
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



    /*---- REPORTE - LISTA DE OBJETIVOS REGIONALES SEGUN OBJETIVO DE GESTION ----*/
    public function reporte_objetivos_regionales($og_id){
      $ogestion=$this->model_objetivogestion->get_objetivosgestion($og_id); 
      if(count($ogestion)!=0){
        $data['gestion']=$this->gestion;
        $data['lista_operaciones']=$this->reporte_lista_oregionales($og_id);
        $this->load->view('admin/mestrategico/objetivos_gestion/reporte_objetivos_regionales', $data); 
      }
      else{
        echo "Error !!!";
      }
    }

    /// --- Lista de Operaciones por Objetivo de Gestion 2022
    public function reporte_lista_oregionales($og_id){
      $regionales=$this->model_objetivogestion->list_temporalidad_regional($og_id);
      $ogestion=$this->model_objetivogestion->get_objetivosgestion($og_id); 

      $tabla='';
        $nro_pag=0;
        foreach($regionales as $row){ 
          $oregional=$this->model_objetivoregion->list_oregional_regional($og_id,$row['dep_id']);
          $nro_pag++;
          $tabla.='<page backtop="66mm" backbottom="20mm" backleft="5mm" backright="5mm" pagegroup="new">
                    <page_header>
                        <br><div class="verde"></div>
                        '.$this->oregional->cabecera_rep_operaciones($ogestion).'
                    </page_header>
                    <page_footer>
                        '.$this->oregional->pie_rep_operaciones($ogestion).'
                    </page_footer>';

          $tabla.='
          <div style="font-size: 12px;font-family: Arial; height:20px;"><b>REGIONAL : </b>'.strtoupper($row['dep_departamento']).' |<b> META REGIONAL : </b>'.round($row['prog_fis'],2).'</div>';

          $nro=0;
          if(count($oregional)!=0){
            $tabla.=$this->reporte_datos_objetivo_regional_por_regional($oregional,$row['dep_id']);
          }
          else{
            $tabla.='<div style="font-size: 9px;font-family: Arial; height:20px;">SIN REGISTROS</div>';
          }

          $tabla.='</page>';
        }

      return $tabla;
    }


    //// Reporte muestra el reporte de operaciones por regional 2022
    public function reporte_datos_objetivo_regional_por_regional($oregional,$dep_id){
      $tabla='';
        $nro=0;
        foreach($oregional as $row_or){
         $nro++;

         $tabla.='
         <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;">
            <thead>
              <tr style="font-size: 8px;" bgcolor="#d2d2d2" align=center>
                <th style="width:2%;height:15px;">#</th>
                <th style="width:5%;">COD. OPE.</th>
                <th style="width:15%;">OPERACI&Oacute;N '.$this->gestion.'</th>
                <th style="width:15%;">PRODUCTO</th>
                <th style="width:14%;">RESULTADO (LOGROS)</th>
                <th style="width:13%;">INDICADOR</th>
                <th style="width:5%;">LINEA BASE</th>
                <th style="width:5%;">META</th>
                <th style="width:13%;">MEDIO DE VERIFICACI&Oacute;N</th>
                <th style="width:13%;">OBSERVACIONES DETALLE DE DISTRIBUCI&Oacute;N</th>
              </tr>
            </thead>
          <tbody>
            <tr style="font-size: 7px;">
            <td style="width:2%; height:20px;" align=center>'.$nro.'</td>
            <td style="width:5%; font-size: 15px; text-align: center"><b>'.$row_or['or_codigo'].'</b></td>
            <td style="width:15%;">'.$row_or['or_objetivo'].'</td>
            <td style="width:15%;">'.$row_or['or_producto'].'</td>
            <td style="width:14%;">'.$row_or['or_resultado'].'</td>
            <td style="width:13%;">'.$row_or['or_indicador'].'</td>
            <td style="width:5%;font-size: 10px;text-align:right">'.round($row_or['or_linea_base'],2).'</td>
            <td style="width:5%;font-size: 10px;text-align:right">'.round($row_or['or_meta'],2).'</td>
            <td style="width:13%;">'.$row_or['or_verificacion'].'</td>
            <td style="width:13%;">'.$row_or['or_observacion'].'</td>
          </tr>
          </tbody>
        </table><br>';
        $num=0;
        $distritales=$this->model_proyecto->list_distritales($dep_id);
        foreach($distritales as $rowd){
          $niveles=$this->model_objetivoregion->list_niveles();
          $tabla.='
             <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;">
                <thead>
                <tr>
                  <th colspan=4 bgcolor="#e4e2e2" style="height:12px;" align=center>DISTRIBUCI&Oacute;N - '.strtoupper($rowd['dist_distrital']).'</th>
                </tr>
                <tr>
                  <th style="width:25%; height:12px;" bgcolor="#e4e2e2" align=center>REGIONAL / DISTRITAL</th>
                  <th style="width:25%;" bgcolor="#e4e2e2" align=center>PRIMER NIVEL</th>
                  <th style="width:25%;" bgcolor="#e4e2e2" align=center>SEGUNDO NIVEL</th>
                  <th style="width:25%;" bgcolor="#e4e2e2" align=center>TERCER NIVEL</th>
                </tr>
                </thead>
                <tbody>
                  <tr style="text-align: center;">';
                  
                  foreach($niveles as $rown){
                    $nivel=$this->model_objetivoregion->list_unidades_distrital_niveles($rowd['dist_id'],$rown['tn_id']);
                    $tabla.='
                    <td style="width:25%;">
                      <table class="tabla" cellpadding="0" cellspacing="0" border=0.1 style="width:100%; font-size: 6.3px;">
                        <thead>
                        <tr>
                          <th style="width:10px; height:10px;">#</th>
                          <th style="width:30px;">CAT. PROG.</th>
                          <th style="width:135px;">UNIDAD / ESTABLECIMIENTO</th>
                          <th style="width:50px;">PROG.</th>
                        </tr>
                        </thead>
                        <tbody>';
                        $nro=0;
                        foreach($nivel as $rowu){
                          $uni=$this->model_objetivoregion->get_unidad_programado($row_or['or_id'],$rowu['act_id']);
                          $color='';$valor_prog=0;
                          if(count($uni)!=0){
                              if($uni[0]['or_estado']==1){
                                  $color='#cbf7cb';      
                              }
                            $valor_prog=$uni[0]['prog_fis'];
                          }
                          $nro++;
                          $tabla.='
                          <tr bgcolor='.$color.'>
                            <td style="width:10px;">'.$nro.'</td>
                            <td style="width:30px;">'.$rowu['aper_programa'].'</td>
                            <td style="width:135px;text-align: left;">'.$rowu['tipo'].' '.$rowu['act_descripcion'].'</td>
                            <td style="width:50px;">'.round($valor_prog,2).'</td>
                          </tr>';
                        }

                        $tabla.='
                        </tbody>
                      </table>
                    </td>';
                  }
              $tabla.='
                </tr>
              </tbody>
            </table><br>';
            }
        }

      return $tabla;
    }

    /*------------------------- COMBO RESPONSABLES ----------------------*/
    public function combo_funcionario_unidad_organizacional($accion=''){ 
      $salida="";
      $accion=$_POST["accion"];
      switch ($accion) {
        case 'unidad':
        $salida="";
          $id_pais=$_POST["elegido"];
          
          $combog = pg_query('SELECT u.*
          from funcionario f
          Inner Join unidadorganizacional as u On u."uni_id"=f."uni_id"
          where  f."fun_id"='.$id_pais.'');
          while($sql_p = pg_fetch_row($combog))
          {$salida.= "<option value='".$sql_p[0]."'>".$sql_p[2]."</option>";}

        echo $salida; 
        //return $salida;
        break;
      }
    }

  ///// ===== REPORTE FORMULARIO N° 2

  public function reporte_form2($dep_id){
    $data['regional']=$this->model_proyecto->get_departamento($dep_id);
    //$data['mes'] = $this->mes_nombre();
    $data['cabecera']=$this->oregional->cabecera_form2($data['regional']);
    $data['oregional']=$this->oregional->rep_lista_form2_sp($dep_id);
    $data['pie']=$this->oregional->pie_form2($data['regional']);

    $this->load->view('admin/mestrategico/objetivos_region/reporte_form2', $data);
  }

  
  ///// ===== EXPORTAR FORMULARIO N° 2
  public function exportar_form2($dep_id){
    $operaciones=$this->oregional->exportar_lista_form2_sp($dep_id);
    date_default_timezone_set('America/Lima');
    header('Content-type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=Operaciones.xls"); //Indica el nombre del archivo resultante
    header("Pragma: no-cache");
    header("Expires: 0");
    echo "";
    echo $operaciones;
  }


  //////// MIGRAR OPERACIONES REGIONALES (Archivo Excel)
  public function valida_add_operaciones_regionales() {
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '512M');    // Aumentar memoria

        $this->load->library('excel'); 
        //$com_id = $this->input->post('com_id');
        //$get_unidad = $this->model_componente->get_componente($com_id, $this->gestion);
        
        // Carga de catálogo relacional de validación de los Objetivos Regionales
       // $list_oregional = $this->model_objetivoregion->list_proyecto_oregional($get_unidad[0]['proy_id']);

        if (empty($get_unidad)) {
            echo json_encode(array('status' => 'error', 'errors' => array('No se encontró información de la Unidad Organizacional. Verifique su sesión.')));
            return;
        }

        if (!isset($_FILES['archivo']) || empty($_FILES['archivo']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        $archivo = $_FILES['archivo']['tmp_name'];
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
            $limitePermitido = 22; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial estructurado exige exactamente $limitePermitido columnas (Hasta la 'V').")));
                return;
            }

            // --- 2. VALIDACIÓN FILA POR FILA ---
            for ($i = 2; $i <= $filasMax; $i++) {
                //$com_id = 0;
                $acc_id  = 0;

                // Extraer valores básicos de la fila activa
                $cod_acp          = trim($hoja->getCell('A' . $i)->getValue());
                $cod_ope            = trim($hoja->getCell('B' . $i)->getValue());
                $operacion            = trim($hoja->getCell('C' . $i)->getValue());
                $producto            = trim($hoja->getCell('D' . $i)->getValue());

                $resultado          = trim($hoja->getCell('E' . $i)->getValue());
                $indicador          = trim($hoja->getCell('F' . $i)->getValue());
                $medio_verificacion = trim($hoja->getCell('G' . $i)->getValue());
                $observacion          = trim($hoja->getCell('H' . $i)->getValue());


                // 🌟 BLINDAJE ANTIFALLA: Filtra y salta las filas vacías inferiores del Excel
                if (empty($cod_acp) && empty($cod_ope) && empty($operacion)) {
                    continue;
                }

                /*if (!empty($cod_uresp)) {
                    if (strlen($cod_uresp) != 4) {
                        $errores[] = "Fila $i: El código de 'UNIDAD RESPONSABLE' ($cod_uresp) debe tener exactamente 4 caracteres.";
                    } 
                    else {
                      if($get_unidad[0]['serv_cod']!=$cod_uresp){
                        $errores[] = "Fila $i: Error en la 'UNIDAD RESPONSABLE' ($cod_uresp). debe exluir del archivo a migrar, ya que corresponde a otra Unidad Responsable.";
                      }
                    }
                } else {
                    $errores[] = "Fila $i: 'CODIGO DE UNIDAD RESPONSABLE' es obligatoria.";
                }*/

                // Verificando códigos ACP y Operación
                /*if (!empty($cod_acp) && is_numeric($cod_acp) && !empty($cod_ope) && is_numeric($cod_ope)) {
                    if (count($list_oregional) != 0) {
                        $get_acc = $this->model_objetivoregion->get_alineacion_proyecto_oregional($proy_id, $cod_acp, $cod_ope);
                        if (count($get_acc) != 0) {
                            $or_id = $get_acc[0]['or_id'];
                        } else {
                            $errores[] = "Fila $i: La combinación ACP ($cod_acp) y OPERACIÓN ($cod_ope) no guarda relación con los Objetivos Regionales.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: 'CODIGO ACP Y OPERACION' son obligatorios y deben ser numéricos.";
                }*/

                if (!is_numeric($cod_acp) <= 0) {
                    $errores[] = "Fila $i: Codigo de ACP debe ser un número válido mayor a cero.";
                }
                else{
                  $get_acp=$this->model_objetivogestion->verif_get_objetivosgestion($cod_acp); /// verif acp
                  if(count($get_acp)==0){
                    $errores[] = "Fila $i: No existe el ACP vigente";
                  }
                  else{
                    $acp_id=$get_acp[0]['acp_id'];
                  }
                }






                // Validación C: Cronograma Mensualizado Saneado (J al U)
                $suma_meses = 0;
                $columnas_meses = array('J','K','L','M','N','O','P','Q','R','S','T','U');
                $meses_valores = array();
                $m_index = 1;
                
                foreach ($columnas_meses as $col) {
                    $celda_cruda = $hoja->getCell($col . $i)->getCalculatedValue();
                    $val_mes     = ($celda_cruda === NULL || trim($celda_cruda) === '') ? 0 : trim($celda_cruda);

                    if (!is_numeric($val_mes)) {
                        $errores[] = "Fila $i: Valor no numérico detectado en el mes de la columna '$col'.";
                        break;
                    }
                    $monto_mes = floatval($val_mes);
                    $suma_meses += $monto_mes;
                    $meses_valores[$m_index] = $monto_mes; // Guardamos en el array indexado temporal de la fila
                    $m_index++;
                }

                // Validación de Coincidencia Física Matemática
                if (abs($suma_meses - floatval($meta)) > 0.01) {
                    $errores[] = "Fila $i: La suma de los meses ($suma_meses) no coincide con la meta ($meta).";
                }

                if (empty($errores)) {
                    // 🌟 SOLUCIÓN RAÍZ: Agrupamos el Maestro y su Detalle mensual correspondiente en paralelo
                    $data_insertar[] = array(
                        'maestro' => array(
                            'com_id'                   => $com_id,
                            'prod_cod'                 => intval($cod_act),
                            'prod_producto'            => strtoupper($actividad),
                            'prod_resultado'           => strtoupper($resultado),
                            'indi_id'                  => 1,
                            'prod_indicador'           => strtoupper($indicador),
                            'prod_fuente_verificacion' => strtoupper($medioverificacion), 
                            'prod_linea_base'          => 0,
                            'prod_meta'                => floatval($meta),
                            'uni_resp'                 => 0, 
                            'prod_unidades'            => strtoupper($unidad_responsable),
                            'acc_id'                   => 0,
                            'prod_ppto'                => 1,
                            'fecha'                    => date("d/m/Y H:i:s"),
                            'or_id'                    => $or_id,
                            'fun_id'                   => intval($this->session->userdata('fun_id')),
                            'num_ip'                   => $this->input->ip_address(), 
                            'nom_ip'                   => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                        ),
                        'meses_lote' => $meses_valores // Queda amarrado en paralelo
                    );
                }
                
                if (count($errores) > 15) break; 
            } // Fin del bucle general FOR por fila

            if (ob_get_length()) ob_clean(); 
            header('Content-Type: application/json');

            if (empty($errores) && !empty($data_insertar)) {
                $this->db->trans_start(); // Iniciar transacción atómica en Postgres
                
                foreach ($data_insertar as $fila) {
                    // Inserción A: Registro maestro del Producto en tu tabla '_productos'
                    $this->db->insert('_productos', $fila['maestro']);
                    $prod_id = $this->db->insert_id(); // Capturamos el ID de la base de datos
                    
                    /*------------ REGISTRO DE LA TEMPORALIDAD EN TU TABLA REAL ---------*/
                    for ($m = 1; $m <= 12; $m++) {
                        $pfin = $this->security->xss_clean($fila['meses_lote'][$m]);
                        
                        if ($pfin != 0) {
                            $data_to_store4 = array( 
                                'prod_id' => $prod_id,
                                'm_id'    => $m, 
                                'pg_fis'  => $pfin, 
                                'g_id'    => intval($this->gestion), 
                            );
                            $this->db->insert('prod_programado_mensual', $data_to_store4);
                        }
                    }
                    /*-------------------------------------------------------------------*/
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'errors' => array('Error al insertar en la base de datos (Transacción fallida en Postgres).')));
                } else {
                    echo json_encode(array(
                        'status' => 'success', 
                        'msj'    => 'Importación finalizada con éxito.',
                        'conteo' => count($data_insertar) 
                    ));
                }
            } else {
                echo json_encode(array(
                    'status' => 'error', 
                    'errors' => !empty($errores) ? $errores : array('El archivo parece estar vacío o no tiene datos válidos para procesar.')
                ));
            }
            exit; 

        } catch (Exception $e) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(array('status' => 'error', 'errors' => array('Excepción crítica de PHPExcel: ' . $e->getMessage())));
        }
    }


    function valida_add_operaciones_regionales2(){
      if ($this->input->post()) {
          $post = $this->input->post();

          $tipo = $_FILES['archivo']['type'];
          $tamanio = $_FILES['archivo']['size'];
          $archivotmp = $_FILES['archivo']['tmp_name'];

          $filename = $_FILES["archivo"]["name"];
          $file_basename = substr($filename, 0, strripos($filename, '.'));
          $file_ext = substr($filename, strripos($filename, '.'));
          $allowed_file_types = array('.csv');

          if (in_array($file_ext, $allowed_file_types) && ($tamanio < 90000000)) {
              $lineas = file($archivotmp);
             
              $i=0;

              foreach ($lineas as $linea_num => $linea){ /// A
                if($i != 0){ /// B
                  $datos = explode(";",$linea);
                  //$nro++;
                  $acp_id=intval(trim($datos[0])); /// Acp id
                  $dep_id=intval(trim($datos[1])); /// dep id
                  $codigo=intval(trim($datos[2])); /// codigo
                  $operacion=trim($datos[3]); /// operacion
                  $producto=trim($datos[4]); /// producto
                  $resultado=trim($datos[5]); /// resultado
                  $indicador=trim($datos[6]); /// indicador
                  $linea_base=0;
                  $meta=0;
                  $mverificacion=trim($datos[7]); /// indicador
                  $observacion=trim($datos[8]); /// observacion

                  $acp=$this->model_objetivogestion->get_objetivosgestion($acp_id);
                  if(count($acp)!=0){
                    $get_meta_prog=$this->model_objetivogestion->get_temporalidad_regional($acp_id,$dep_id);
                    $pog_id = $this->security->xss_clean($get_meta_prog[0]['pog_id']); /// pog id
                    $data_to_store = array(
                      'pog_id' => $pog_id,
                      'or_objetivo' =>  strtoupper(utf8_encode($operacion)),
                      'or_producto' =>  strtoupper(utf8_encode($producto)),
                      'or_codigo' => $codigo,
                      'or_resultado' =>  strtoupper(utf8_encode($resultado)),
                      'indi_id' => 1,
                      'or_indicador' =>  strtoupper(utf8_encode($indicador)),
                      'or_linea_base' => $linea_base,
                      'or_meta' => $meta,
                      'or_verificacion' =>  strtoupper(utf8_encode($mverificacion)),
                      'or_observacion' => strtoupper(utf8_encode($observacion)),
                      'g_id' => $this->gestion,
                      'fun_id' => $this->fun_id,
                      'num_ip' => $this->input->ip_address(), 
                      'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
                    );
                    $this->db->insert('objetivos_regionales', $data_to_store);
                    $or_id=$this->db->insert_id();
                  }

                } /// B
                $i++;
              } /// A

              $this->session->set_flashdata('success','SE REGISTRARON '.$i.' OPERACIONES');
              redirect(site_url("").'/me/mis_ogestion');
          }
          else{
            echo "Error !!!";
          }
      }
      else{
        echo "Error !!!!";
      }
    }




}