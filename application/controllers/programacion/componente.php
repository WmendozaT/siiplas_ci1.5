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
        $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id); // Proy
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            if($data['proyecto'][0]['tp_id']==1){ //// Proyecto de Inversion
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


    /*------- GASTO CORRIENTE-----------*/
    /*--------- LISTA DE UNIDADES RESPONSABLES ------*/
    public function lista_uresponsables($proy_id){
        $unidad_responsable = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        if(count($unidad_responsable)!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $listado='';
            $listado.='
            <input type="hidden" name="base" value="'.base_url().'">
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
                        
                      <div class="table-responsive">
                        '.$this->unidades_resp($unidad_responsable).'
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

    //// Lista de Componentes - Proyectos de Inversion
    public function lista_componentes($proy_id){
        $unidad_responsable = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        
        if(count($unidad_responsable)!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $listado='';
            $listado.='
            <div class="row">
                    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <section id="widget-grid" class="well">
                            <ul class="nav nav-pills">
                              <li class="active"><a href="#">MIS COMPONENTES</a></li>
                              <li><a href="#">MIS ACTIVIDADES</a></li>
                            </ul>
                        </section>
                    </article>
                </div>
            <input type="hidden" name="base" value="'.base_url().'">
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <section id="widget-grid" class="well" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 15px; border-radius: 4px; margin-bottom: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                  <div class="row" style="margin: 0; display: flex; flex-direction: column; gap: 12px;">
                      
                      <!-- Tu etiqueta h1 con un espaciado regular y tipografía color plomo oscuro -->
                      <h1 style="margin: 0; line-height: 1.4;">
                          <small>
                              ' . $unidad_responsable[0]['proy_sisin'] . ' - ' . $unidad_responsable[0]['proy_nombre'] . '
                          </small>
                      </h1>
                      
                      <!-- Contenedor flex dinámico para alinear tus botones en la misma fila horizontal -->
                      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-top: 5px;">
                          
                          <!-- Tu párrafo original con el botón de colapso estilizado al formato SmartAdmin -->
                          <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-success nuevo_ff" title="NUEVO REGISTRO - COMPONENTE" class="btn btn-success" style="width:15.5%;">NUEVA UNIDAD RESPONSABLE</a>
                          <!-- Tu enlace original "VOLVER" adaptado estéticamente a la botonera formal -->
                          <a href="' . site_url("admin/proy/list_proy") . '" 
                             title="VOLVER AL MENÚ ANTERIOR" 
                             class="btn btn-default btn-sm" 
                             style="font-weight: bold; color: #475569; border-color: #cbd5e1; background: #ffffff; padding: 6px 12px; display: inline-block;">
                              <i class="fa fa-arrow-left"></i> VOLVER
                          </a>
                          
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
                    <h2 class="font-md"><strong>COMPONENTES DEL PROYECTO</strong></h2>               
                  </header>
                  <div>
                    <div class="widget-body no-padding">
                        
                      <div class="table-responsive">
                        '.$this->unidades_resp($unidad_responsable).'
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


  /*---- UNIDADES RESPONSABLES / COMPONENTES ---------*/
  function unidades_resp($proyecto){
    //$proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
    $componente=$this->model_componente->lista_UnidadesResponsables($proyecto[0]['proy_id']);
    $unidad=$this->model_componente->list_subactividades_pi(); /// lista de unidades
    $tabla='';
    if($proyecto[0]['tp_id']==1){ /// Inversion
        $tabla.='
            <table id="dt_basic4" class="table table table-bordered" width="100%">
                <thead>
                    <tr style="height:45px;">
                      <th style="width:1%; text-align:center;">#</th>
                      <th style="width:1%; text-align:center;">MODIFICAR</th>
                      <th style="width:15%; text-align:center;">UNIDAD RESPONSABLE</th>
                      <th style="width:15%; text-align:center;">DESCRIPCI&Oacute;N COMPONENTE</th>
                      <th style="width:5%; text-align:center;">NRO. ACT.</th>
                      <th style="width:5%; text-align:center;">MIS ACTIVIDADES</th>
                      <th style="width:5%; text-align:center;">MIS ACTIVIDADES</th>
                      <th style="width:5%; text-align:center;">FORM. POA N 4</th>
                      <th style="width:5%; text-align:center;">FORM. POA N 5</th>
                      <th style="width:5%; text-align:center;">EXCEL ACTIVIDADES</th>
                      <th style="width:5%; text-align:center;">ELIMINAR ACTIVIDADES </th>
                    </tr>
                </thead>
                <tbody>';
                $num=0; $ponderacion=0; $sum=0;
                foreach($componente as $row){
                    $num++;
                    $tabla.='
                    <tr>';
                        if(count($this->model_producto->lista_productos($row['com_id']))==0){
                            $tabla.='<td title="'.$row['com_id'].'"><a href="#" data-toggle="modal" data-target="#modal_neg_ff" class="btn btn-default neg_ff" title="DESHABILITAR COMPONENTE"  name="'.$row['com_id'].'" id="'.count($this->model_producto->lista_productos($row['com_id'])).'" ><img src="' . base_url() . 'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></td>';
                        }
                        else{
                            $tabla.='<td title="'.$row['com_id'].'">'.$num.'</td>';
                        }
                        $tabla.='
                        <td><a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default mod_ff" name="'.$row['com_id'].'" title="MODIFICAR COMPONENTE" ><img src="'.base_url().'assets/ifinal/modificar.png" WIDTH="35" HEIGHT="35"/></a></td>
                        <td bgcolor="#d4f1fb" align="center" ><font color="blue" size=2><b>'.$row['serv_cod'].' .- '.$row['serv_descripcion'].'</b></font></td>
                        <td>'.$row['com_componente'].'</td>
                        <td align=center bgcolor="#bee6e1"><font size=2 color=blue>'.count($this->model_producto->lista_productos($row['com_id'])).'</font></td>
                        <td align="center">
                            <a href="'.site_url("admin").'/prog/list_prod/'.$row['com_id'].'" title="MIS ACTIVIDADES" class="btn btn-default" target=_black><img src="'.base_url().'assets/ifinal/archivo.png" WIDTH="34" HEIGHT="34"/></a>
                        </td>
                        <td align="center"><a href="javascript:abreVentana(\''.site_url("").'/prog/reporte_form4_uresponsable/'.$row['com_id'].'\');" title="REPORTE POA FORM 4" class="btn btn-default"><img src="'.base_url().'assets/ifinal/pdf.png" WIDTH="35" HEIGHT="35"/></a></td>
                        <td align="center"><a href="javascript:abreVentana(\''.site_url("").'/prog/reporte_form5_uresponsable/'.$row['com_id'].'\');" title="REPORTE POA FORM 5" class="btn btn-default"><img src="'.base_url().'assets/ifinal/pdf.png" WIDTH="35" HEIGHT="35"/></a></td>
                        <td align="center"></td>
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
            </table>';
    }
    else{ /// Gasto Corriente
        $tabla .= '
        <div style="margin-bottom: 15px; display: flex; gap: 8px; justify-content: space-between; align-items: center; background: #f8fafc; padding: 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
            <div style="display: flex; gap: 6px;">';
                if($this->conf_form4==1 || $this->tp_adm==1){
                  $tabla.='
                    <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
                      <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>Subir Actividades.xls </b>
                    </a>
                    <!-- 📊 Exportación General Consolidadas -->
                    <a href="' . site_url('programacion/crequerimiento/exportar_excel_consolidado_form4') . '" class="btn btn-sm btn-success" title="EXPORTAR EXCEL CONSOLIDADO" style="font-weight: bold; background: #16a34a; border-color: #16a34a; color: #fff;">
                        <i class="fa fa-file-excel-o"></i> Excel Consolidado
                    </a>
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modal_techos_resumen_global" style="font-weight: bold; background: #2563eb; border-color: #2563eb;">
                        <i class="fa fa-sliders"></i> Ver Techos Generales
                    </button>';
                }
                $tabla.='
            </div>
        </div>';

        // ==========================================================================
        // 📊 GRID MÁSTER: LISTADO DE UNIDADES RESPONSABLES E INSUMOS COMPILADOS
        // ==========================================================================
        $tabla .= '
        <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px;">
            <table id="dt_basic4" class="table table-striped table-bordered table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11.5px; border-collapse: collapse;">
                <thead>
                    <tr style="background: #334155; color: #ffffff; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.3px; height:45px;">
                        <th style="width: 2%; text-align:center; vertical-align: middle;">#</th>
                        <th style="width: 4%; text-align:center; vertical-align: middle;">ACCIONES</th>
                        <th style="width: 8%; text-align:center; vertical-align: middle;">CÓDIGO UNIDAD</th>
                        <th style="width: 30%; text-align:left; vertical-align: middle; padding-left:10px;">UNIDAD RESPONSABLE</th>
                        <th style="width: 6%; text-align:center; vertical-align: middle;">NRO. ACT.</th>
                        <th style="width: 15%; text-align:center; vertical-align: middle;">FORM. POA N° 4 (ACTIVIDADES)</th>
                        <th style="width: 15%; text-align:center; vertical-align: middle;">FORM. POA N° 5 (REQUERIMIENTOS)</th>
                        <th style="width: 10%; text-align:center; vertical-align: middle;">EXPORTAR POA.</th>
                        <th style="width: 10%; text-align:center; vertical-align: middle;">PPTO. POA.</th>
                    </tr>
                </thead>
                <tbody>';

                $num = 0;
                foreach($componente as $row) {
                    $num++;
                    $com_id_actual = intval($row['com_id']);
                    $productos_unidad = $this->model_producto->lista_productos($com_id_actual);
                    $conteo_actividades = count($productos_unidad);

                    $tabla .= '<tr>';
                    
                    // COLUMNA 1: Correlativo numérico
                    $tabla .= '<td style="text-align: center; font-weight: bold; color: #64748b; vertical-align: middle;" title="'.$row['com_id'].'">' . $num . '</td>';
                    
                    // COLUMNA 2: Filtros de Control / Roles de Configuración en el cliente
                    $tabla .= '<td style="text-align: center; vertical-align: middle;">';
                    if ($conteo_actividades == 0 && $this->tp_adm == 1) {
                        $tabla .= '
                        <a href="#" data-toggle="modal" data-target="#modal_neg_ff" class="btn btn-xs btn-danger neg_ff" title="DESHABILITAR SUB-ACTIVIDAD" name="' . $com_id_actual . '" id="' . $conteo_actividades . '" style="padding: 4px 8px;">
                            <i class="fa fa-ban" style="font-size:14px;"></i>
                        </a>';
                    } else {
                        if (intval($this->tp_adm) == 1) {
                            $tp_sact = $this->model_componente->tp_subactividad();
                            // 🌟 REPARADO: Estructura HTML elástica acoplada al motor de delegación del form5.js
                            $tabla .= '<select class="form-control input-sm select-subactividad-cns" data-id="' . $com_id_actual . '" style="font-size:11px; height:28px; padding:2px 4px; font-weight:bold; cursor:pointer;">';
                            foreach($tp_sact as $pr) {
                                $selected = ($pr['tp_sact'] == $row['tp_sact']) ? 'selected' : '';
                                $tabla .= '<option value="' . $pr['tp_sact'] . '" ' . $selected . '>' . $pr['tipo_subactividad'] . '</option>';
                            }
                            $tabla .= '</select>';
                        } else {
                            $tabla .= '<span class="text-muted" style="font-size:11px;"><i class="fa fa-lock" title="Bloqueado por Auditoría"></i> F-' . $num . '</span>';
                        }
                    }
                    $tabla .= '</td>';

                    // Columnas de Datos Identificadores de la Unidad Organizacional
                    $tabla .= '<td style="text-align: center; font-weight: bold; background-color: #fef08a; color: #1e3a8a; vertical-align: middle; font-size: 12.5px;">' . $row['serv_cod'] . '</td>';
                    $tabla .= '<td style="text-align: left; vertical-align: middle; font-weight: 500; color: #1e293b; padding-left:10px;">' . strtoupper($row['serv_descripcion']) . '</td>';
                    
                    // Conteo Dinámico de Actividades con resalte cromático azul CNS
                    $tabla .= '<td style="text-align: center; font-weight: bold; background-color: #e0f2fe; color: #0369a1; vertical-align: middle; font-size:12px;">' . $conteo_actividades . '</td>';
                    
                    // 🌟 COLUMNA: FORMULARIO N° 4 - GESTIÓN DE ACTIVIDADES Y EXPORTACIÓN INDIVIDUAL
                    $tabla .= '
                    <td style="text-align: center; vertical-align: middle;">
                      <div style="display: inline-flex; gap: 4px;">
                        <a href="' . site_url("admin/prog/list_prod/" . $com_id_actual) . '" title="VER MIS ACTIVIDADES (FORM 4)" class="btn btn-xs btn-default" target=_black style="padding: 15px 20px; background:#f1f5f9;"><i class="fa fa-list text-primary" style="font-size:15px;"></i></a>
                        <a href="javascript:abreVentana(\'' . site_url("prog/reporte_form4_uresponsable/" . $com_id_actual) . '\');" title="REPORTE POA FORM 4 (PDF)" class="btn btn-xs btn-default" style="padding: 15px 20px; background:#fff1f2;"><i class="fa fa-file-pdf-o text-danger" style="font-size:15px;"></i></a>
                      </div>
                    </td>';
                    
                    // 🌟 COLUMNA: FORMULARIO N° 5 - GESTIÓN DE INSUMOS Y EXPORTACIÓN INDIVIDUAL
                    $tabla .= '
                    <td style="text-align: center; vertical-align: middle;">
                      <div style="display: inline-flex; gap: 4px;">
                        <a href="' . site_url("prog/requerimiento_x_uresponsable/" . $com_id_actual) . '" title="REQUERIMIENTOS DE LA UNIDAD (FORM 5)" class="btn btn-xs btn-default" target=_black  style="padding: 15px 20px; background:#f1f5f9;"><i class="fa fa-usd text-primary" style="font-size:15px; font-weight: bold;"></i></a>
                        <a href="javascript:abreVentana(\'' . site_url("prog/reporte_form5_uresponsable/" . $com_id_actual) . '\');" title="REPORTE POA FORM 5 (PDF)" class="btn btn-xs btn-default" style="padding: 15px 20px; background:#fff1f2;"><i class="fa fa-file-pdf-o text-danger" style="font-size:15px;"></i></a>
                      </div>
                    </td>';
                    
                    $tabla.='
                    <td style="text-align: center; vertical-align: middle;">
                        <button type="button" 
                                 class="btn btn-xs btn-default btn-exportar-excel-fila" 
                                 onclick="exportarExcelConLoading(this, ' . $com_id_actual . ')" 
                                 style="padding: 15px 20px; background:#f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px;" 
                                 title="EXPORTAR CONSOLIDADO EN EXCEL">
                              <span class="txt-btn-excel-fila">
                                  <i class="fa fa-file-excel-o text-success" style="font-size:14px;"></i>
                              </span>
                        </button>
                    </td>';
                    // 🌟 COLUMNA 8: DISPARADOR INDIVIDUAL DEL MODAL DE PRESUPUESTO POA
                    $tabla .= '
                    <td style="text-align: center; vertical-align: middle;">
                        <button type="button" class="btn btn-xs btn-info btn-ver-presupuesto" data-id="' . $com_id_actual . '" data-codigo="' . $row['serv_cod'] . '" data-nombre="' . htmlspecialchars($row['serv_descripcion'], ENT_QUOTES, 'UTF-8') . '" title="VER RESUMEN DE TECHOS PRESUPUESTARIOS" style="font-weight: bold; padding: 5px 10px; background: #0284c7; border-color:#0284c7;">
                            <i class="fa fa-eye"></i> Techo F5
                            </button>
                    </td>';
                    
                    // Cierre simétrico de la hilera activa de la Unidad Responsable
                    $tabla .= '</tr>';
                }
        $tabla .= '    
                </tbody>
            </table>
        </div>';
    }
   
      //// modal para mostrar la programacion de requerimientos por partidas por cada componente
      $tabla.='
      <div class="modal fade" id="modal_techos_resumen_global" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.45);">
          <div class="modal-dialog" style="width: 50% !important; margin: 30px auto;">
              <div class="modal-content" style="border-radius: 4px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: none; overflow: hidden;">
                  
                  <!-- CABECERA DEL MODAL -->
                  <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                      <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; margin:0;">
                          <i class="fa fa-calculator text-primary"></i> RESUMEN PRESUPUESTARIO INSTITUCIONAL
                      </h4>
                      <button type="button" class="close" data-dismiss="modal" style="font-size: 20px; color: #475569; opacity: 0.8; border:none; background:none; cursor:pointer;">&times;</button>
                  </div>

                  <!-- CUERPO RECEPTOR DINÁMICO AJAX -->
                  <div class="modal-body" id="contenedor_techo_dinamico_cns" style="padding: 25px; background: #ffffff;">
                      <!-- Aquí el JS estampará el spinner y luego la tabla analítica de saldos -->
                  </div>
                  
                  <!-- PIE DE VENTANA -->
                  <div class="modal-header" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 20px; text-align: right;">
                      <button type="button" class="btn btn-default" data-dismiss="modal" style="font-weight: bold; font-size: 11.5px; padding: 5px 14px; border-radius: 3px;">CERRAR PANEL</button>
                  </div>

              </div>
          </div>
      </div>';



            $tabla.='
            <div class="modal fade" id="modal_nuevo_ff" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">

                    <div class="modal-body">
                        <form action="'.site_url().'/prog/valida_comp" id="form_nuevo" name="form_nuevo" class="form-horizontal" method="post">
                            <input  type="hidden" name="pfec_id" id="pfec" value="'.$proyecto[0]['pfec_id'].'">
                            <h2 class="alert alert-info"><center>UNIDAD RESPONSABLE (Agregar)</center></h2>                           
                            <fieldset>
                                <div id="tit"></div>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">UNIDAD RESPONSABLE</label>
                                        <div class="col-md-10">
                                            <select class="form-control" id="serv_id" name="serv_id" title="SELECCIONE UNIDAD RESPONSABLE">
                                              <option value="">Seleccione Unidad</option>';
                                                foreach($unidad as $row){
                                                    $tabla.='<option value="'.$row['serv_id'].'">'.$row['serv_cod'].' - '.$row['serv_descripcion'].'</option>';
                                                  }
                                                $tabla.='
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">COMPONENTE DE PROYECTO</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="descripcion" id="descripcion" maxlength="200" rows="3" ></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                            </fieldset>                    
                            <div class="form-actions">
                                <div class="row">
                                    <div id="but">
                                        <div class="col-md-12">
                                           <button class="btn btn-default" data-dismiss="modal" id="cl" title="CANCELAR">CANCELAR</button>
                                           <button type="button" name="subir_form" id="subir_form" class="btn btn-info" >GUARDAR UNIDAD</button>
                                            <center><img id="load" style="display: none" src="<?php echo base_url() ?>/assets/img/loading.gif" width="50" height="50"></center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        </div>
                    </div>
                </div>
            </div>';

            $tabla.='
            <div class="modal fade" id="modal_mod_ff" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">

                      <div class="modal-body">
                        <form action="'.site_url().'/prog/valida_update_comp" id="form_mod" name="form_mod" class="form-horizontal" method="post">
                        <input type="hidden" name="com_id" id="com_id">

                            <h2 class="alert alert-info"><center>COMPONENTE (Modificar)</center></h2>                           
                            <fieldset>
                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">UNIDAD RESPONSABLE</label>
                                        <div class="col-md-10">
                                            <select class="form-control" id="mserv_id" name="mserv_id" title="SELECCIONE UNIDAD RESPONSABLE">
                                              <option value="">Seleccione Unidad</option>';
                                                foreach($unidad as $row){
                                                $tabla.='<option value="'.$row['serv_id'].'">'.$row['serv_cod'].' - '.$row['serv_descripcion'].'</option>';
                                                }
                                            $tabla.='
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="form-group">
                                        <label class="col-md-2 control-label">DESCRIPCI&Oacute;N COMPONENTE</label>
                                        <div class="col-md-10">
                                            <textarea class="form-control" name="mcomponente" id="mcomponente" maxlength="200" rows="3" ></textarea>
                                        </div>
                                    </div>
                                </div>
                             
                            </fieldset>                    
                            <div class="form-actions">
                                <div class="row">
                                    <div id="mbut">
                                        <div class="col-md-12">
                                           <button class="btn btn-default" data-dismiss="modal" id="mcl" title="CANCELAR">CANCELAR</button>
                                           <button type="button" name="mod_ffenviar" id="mod_ffenviar" class="btn btn-info" >MODIFICAR UNIDAD</button>
                                            <center><img id="loadd" style="display: none" src="<?php echo base_url() ?>/assets/img/loading.gif" width="50" height="50"></center>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                  </div>
                </div>
            </div>';

            ////----------------------- Modal para Subir Actividades por Unidad Organizacional
            $tabla .= '
            <style>
                /* Estilización formal e inmunizada para la rejilla del cargador masivo */
                #dialog_subir { width: 45%;}
            </style>

            <div class="modal fade" id="modal_importar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
                <div class="modal-dialog" id="dialog_subir">
                    <div class="modal-content" style="border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none; overflow: hidden;">
                        
                        <!-- CABECERA DEL COMPONENTE -->
                        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                            <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                <i class="fa fa-upload text-primary"></i> Importar Consolidado Actividades
                            </h4>
                        </div>

                        <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                        <div class="modal-body" style="padding: 25px; background: #ffffff;">
                            
                            <!-- Título e Instrucción -->
                            <div class="text-center" style="margin-bottom: 20px;">
                                <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Excel (.xls, .xlsx)</h5>
                                <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                            </div>

                            <!-- Vista previa de columnas (Corregido: Concatenación nativa base_url) -->
                            <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                                <img src="' . base_url('assets/img/img_migracion/migracion_form4_unidad.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                            </div>

                            <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                            <form action="' . site_url('programacion/componente/valida_migracion_form4_consolidado') . '" method="post" enctype="multipart/form-data" id="form_subir_sigep" autocomplete="off" style="padding:0; background:transparent;">
                                <input name="proy_id" value="'.$proyecto[0]['proy_id'].'" type="hidden" > 
                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo" accept=".xlsx, .xls" name="archivo" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- Animación Pre-Loader de la Planilla -->
                                <div id="loads" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>';

$tabla .= '
        <script type="text/javascript">
            // REVISIÓN DE INTEGRIDAD SIIPLAS: Espera nativamente la disponibilidad de JQuery en el DOM
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // Mostrar nombre del archivo al seleccionar en el input simulado de SmartAdmin
                    $(document).on(\'change\', \'#archivo\', function() {
                        var fileName = $(this).val().split(\'\\\\\').pop();
                        if (fileName) {
                            $(\'.file-name-display\').val(fileName);
                        }
                    });

                    // ==========================================================================
                    // ESCUCHA SUBMIT ASÍNCRONA: MIGRACIÓN Y CONSOLIDACIÓN DE FILAS DEL EXCEL
                    // ==========================================================================
                    $(document).on(\'click\', \'#btn_subir\', function(e) {
                        e.preventDefault();
                        $(\'#mensaje\').html(\'\'); 

                        if ($(\'#archivo\').val() == \'\') {
                            $(\'#mensaje\').html(\'<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>\');
                            if (typeof alertify !== "undefined") {
                                alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .CSV o .XLSX");
                            }
                            return false;
                        }

                        var form = $(\'#form_subir_sigep\')[0];
                        var data_multipart = new FormData(form);
                        var $btn = $(this);

                        // Bloquear UI e Inyectar Loader
                        $btn.prop(\'disabled\', true).html(\'<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA...\');
                        $(\'#loads\').show();

                        $.ajax({
                            type: "POST",
                            url: $(\'#form_subir_sigep\').attr(\'action\'),
                            data: data_multipart,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                var res;
                                try {
                                    res = (typeof response === \'object\') ? response : JSON.parse(response);
                                } catch (err) {
                                    console.error("Error parseando JSON:", response);
                                    $(\'#mensaje\').html(\'<div class="alert alert-danger"><b>❌ Error del Servidor:</b> La respuesta de la base de datos PostgreSQL devolvió un buffer corrupto o se agotó el tiempo de espera.</div>\');
                                    $btn.prop(\'disabled\', false).text(\'REINTENTAR ACCIÓN\');
                                    $(\'#loads\').hide();
                                    return;
                                }

                                // Evalúa el éxito transaccional unificado en la CNS
                                if (res.respuesta === \'correcto\' || res.status === \'success\') {
                                    var mensaje_exito = res.mensaje || res.msj || "Registros migrados exitosamente.";
                                    var conteo_filas = res.filas_procesadas || res.conteo || "0";

                                    // Construcción de plantilla visual de éxito en auditoría
                                    var html_success = `
                                        <div class="alert alert-success text-center" style="border-left: 5px solid #2e7d32; background:#f0fdf4; color:#16a34a; padding:15px; margin-bottom:0;">
                                            <i class="fa fa-check-circle fa-3x" style="margin-bottom:10px;"></i>
                                            <h4 style="font-weight:bold; margin:0 0 5px 0; color:#15803d;">¡MIGRACIÓN COMPLETADA CON ÉXITO!</h4>
                                            <p style="font-size: 12.5px; color:#166534; font-weight:500;">${mensaje_exito}</p>
                                            <div style="margin: 10px 0;">
                                                <span class="label label-success" style="font-size: 16px; padding: 4px 12px; font-weight:bold; background:#16a34a;">${conteo_filas}</span>
                                            </div>
                                            <p style="margin:0;"><small class="text-muted">Registros validados e insertados en las aperturas programáticas.</small></p>
                                        </div>`;

                                    $(\'#mensaje\').html(html_success);
                                    $(\'#loads\').hide();
                                    $btn.hide(); 

                                    // Temporizador inteligente multi-rol CNS para refrescar grilla
                                    setTimeout(function() {
                                        $(\'#modal_importar\').modal("hide");
                                        $(\'.modal-backdrop\').remove();
                                        $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');

                                        var combo_admin = $(\'#dist_id\').val();
                                        if (combo_admin !== undefined && combo_admin !== "" && combo_admin !== "0") {
                                            $("#dist_id").trigger("change");
                                        } else {
                                            if (typeof forzar_refresco_grilla_siiplas_directo === "function") {
                                                var dist_id_oculto = $(\'input[name="dist_id"]\').val() || 0;
                                                forzar_refresco_grilla_siiplas_directo(dist_id_oculto);
                                            } else {
                                                location.reload(); 
                                            }
                                        }
                                    }, 2500);

                                } else {
                                    // LÓGICA COHERENTE DE EXTRACCIÓN DE ALERTAS Y ERRORES DE CONSISTENCIA
                                    var mensaje_error = res.mensaje || res.msj || "El archivo contiene celdas inválidas.";
                                    var errorMsg = `<strong style="font-size:12px; color:#b91c1c;"><i class="fa fa-times-circle"></i> SE DETECTARON INCONSISTENCIAS EN LA PLANILLA:</strong><br><small class="text-muted">${mensaje_error}</small>`;
                                    
                                    if (res.errors || res.errores) {
                                        var coleccion_errores = res.errors || res.errores;
                                        errorMsg += "<ul style=\'margin-top:8px; padding-left:15px; text-align:left; font-size:11px;\'>";
                                        $.each(coleccion_errores, function(index, value) {
                                            errorMsg += "<li>" + value + "</li>";
                                        });
                                        errorMsg += "</ul>";
                                    }
                                    
                                    $(\'#mensaje\').html(\'<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">\' + errorMsg + \'</div>\');
                                    $btn.prop(\'disabled\', false).html(\'<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA\');
                                    $(\'#loads\').hide();
                                }
                            },
                            error: function(xhr, textStatus, errorThrown) {
                                // 🌟 DETECTOR FORENSE INTEGRADO: Intercepta el colapso 500 y expone el Fatal Error en caliente
                                $(\'#loads\').hide();
                                $btn.prop(\'disabled\', false).html(\'<i class="fa fa-file-excel-o"></i> REINTENTAR SUBIDA\');

                                var errorDetallado = xhr.responseText;
                                var mensajeVisible = "<strong>🚨 FALLA CRÍTICA EN EL SERVIDOR (HTTP 500):</strong><br>";
                                mensajeVisible += "<small class=\'text-muted\'>Apache local abortó el hilo de procesamiento PHPExcel. Detalle del volcado:</small><hr>";
                                
                                if (errorDetallado) {
                                    mensajeVisible += "<div style=\'max-height: 200px; overflow-y: auto; background: #fff5f5; padding: 10px; border: 1px solid #fee2e2; font-family: monospace; font-size: 11px; text-align: left; color: #991b1b;\'>";
                                    mensajeVisible += errorDetallado;
                                    mensajeVisible += "</div>";
                                } else {
                                    mensajeVisible += "El backend no retornó ninguna cadena de caracteres. Verifique directivas max_input_vars en su php.ini.";
                                }

                                $(\'#mensaje\').html(\'<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2;">\' + mensajeVisible + \'</div>\');
                            }
                        });
                    });

                }
            });
        </script>';

    return $tabla;
    }


      //// PARA MIGRACION CONSOLIDADO DE ACTIVIDADES POR ARCHIVO EXCEL 2026 
      //// PARA TODAS LAS UNIDADES DE LA UNIDAD ORGANIZACIONAL
      public function valida_migracion_form4_consolidado() {
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '512M');    // Aumentar memoria

        $this->load->library('excel'); 
        $proy_id = $this->input->post('proy_id');
        
        // Carga de catálogo relacional de validación de los Objetivos Regionales
        $list_oregional = $this->model_objetivoregion->list_proyecto_oregional($proy_id);

        if (empty($proy_id)) {
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
                
                $com_id = 0;
                $or_id  = 0;

                // Extraer valores básicos de la fila activa
                $cod_uresp          = trim($hoja->getCell('A' . $i)->getValue());
                $cod_acp            = trim($hoja->getCell('B' . $i)->getValue());
                $cod_ope            = trim($hoja->getCell('C' . $i)->getValue());
                $cod_act            = trim($hoja->getCell('D' . $i)->getValue());

                $actividad          = trim($hoja->getCell('E' . $i)->getValue());
                $resultado          = trim($hoja->getCell('F' . $i)->getValue());
                $unidad_responsable = trim($hoja->getCell('G' . $i)->getValue());
                $indicador          = trim($hoja->getCell('H' . $i)->getValue());
                $meta               = $hoja->getCell('I' . $i)->getValue();
                $medioverificacion  = trim($hoja->getCell('V' . $i)->getValue());

                // 🌟 BLINDAJE ANTIFALLA: Filtra y salta las filas vacías inferiores del Excel
                if (empty($cod_uresp) && empty($actividad) && (empty($meta) || floatval($meta) == 0)) {
                    continue;
                }

                if (!empty($cod_uresp)) {
                    if (strlen($cod_uresp) != 4) {
                        $errores[] = "Fila $i: El código de 'UNIDAD RESPONSABLE' ($cod_uresp) debe tener exactamente 4 caracteres.";
                    } else {
                        $get_unidad = $this->model_componente->get_UnidadesResponsables($proy_id, $cod_uresp);
                        if (count($get_unidad) == 1) {
                            $com_id = $get_unidad[0]['com_id'];
                        } else {
                            $errores[] = "Fila $i: Error en la 'UNIDAD RESPONSABLE' ($cod_uresp). No existe en nuestra Base de Datos.";
                        }
                    }
                } else {
                    $errores[] = "Fila $i: 'CODIGO DE UNIDAD RESPONSABLE' es obligatoria.";
                }

                // Verificando códigos ACP y Operación
                if (!empty($cod_acp) && is_numeric($cod_acp) && !empty($cod_ope) && is_numeric($cod_ope)) {
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
                }

                if (!is_numeric($meta) || floatval($meta) <= 0) {
                    $errores[] = "Fila $i: La 'META' debe ser un número válido mayor a cero.";
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

            // ==========================================================================
            // --- 3. CONSOLIDACIÓN FINAL TRANSACCIONAL (POSTGRESQL) --------------------
            // ==========================================================================
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


    ///// Cambia el tipo de Subactividad
    public function cambia_tp_sact(){
        // Validamos que sea una solicitud asíncrona legítima de JQuery (Evita accesos por URL)
        if($this->input->is_ajax_request() && $this->input->post()){
            
            $com_id = intval($this->input->post('com_id'));
            $tp_id  = intval($this->input->post('tp_id'));
           
            if ($com_id <= 0 || $tp_id <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'message' => 'Parámetros numéricos relacionales vacíos.'));
                exit;
            }

            // Ejecución limpia directa del query físico de actualización en _componentes
            $update_comp = array(
                'tp_sact' => $tp_id,
            );
            
            $this->db->where('com_id', $com_id);
            $db_status = $this->db->update('_componentes', $update_comp);
              
            // 🌟 COMPUETA ANTI-PARSEO: Purgamos búferes intermedios de CodeIgniter antes del eco
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: application/json; charset=utf-8');

            if ($db_status) {
                echo json_encode(array(
                    'status'    => 'success',
                    'respuesta' => 'correcto',
                    'message'   => '¡Se ha reclasificado el tipo de subactividad de la unidad con éxito contable!'
                ));
            } else {
                echo json_encode(array(
                    'status'    => 'error',
                    'message'   => 'PostgreSQL denegó la actualización debido a un conflicto de constraint.'
                ));
            }
            exit; // Detiene el hilo de ejecución para garantizar un payload limpio

        } else {
            show_404();
        }
    }

 // /*------- DESHABILITAR SUB ACTIVIDAD (SERVICIO) ------*/
    function deshabilitar_sactividad(){
      if ($this->input->is_ajax_request() && $this->input->post()) {
          $post = $this->input->post();
          $com_id = $this->security->xss_clean($post['com_id']);
          

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


    public function get_componente(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            $com_id = $post['com_id'];
            $com_id = $this->security->xss_clean($com_id);
            $dato_comp = $this->model_componente->get_componente($com_id,$this->gestion);
            //caso para modificar el codigo de proyecto y actividades
            
            if(count($dato_comp)!=0){
              $result = array(
                  'respuesta' => 'correcto',
                  'componente' => $dato_comp,
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


    /// Valida componente 
    public function valida_componente(){
      if ($this->input->post()) {
          $post = $this->input->post();
          $pfec_id = $this->security->xss_clean($post['pfec_id']); /// pfec id
          $descripcion = $this->security->xss_clean($post['descripcion']); /// Descripcion Componente 
          $serv_id = $this->security->xss_clean($post['serv_id']); //// serv id

          if(isset($pfec_id) & isset($descripcion) & isset($serv_id)){
                $fase = $this->model_faseetapa->get_fase($pfec_id);
                $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']);
                $reponsable=$this->model_proyecto->responsable_proy($fase[0]['proy_id'],2);
                /*--------- COMPONENTE ----------*/
                $data = array(
                    'pfec_id' => $pfec_id,
                    'serv_id' => $serv_id,
                    'com_componente' => strtoupper($descripcion), 
                    'resp_id' => $reponsable[0]['fun_id'], 
                    'fun_id' => $this->fun_id,
                );
                $this->db->insert('_componentes',$data);
                $com_id=$this->db->insert_id();
                /*------------------------------------*/

                if(count($this->model_componente->get_componente($com_id,$this->gestion))!=0){
                    $this->session->set_flashdata('success','EL COMPONENTE SE REGISTRO CORRECTAMENTE');
                    redirect(site_url("").'/prog/list_serv/'.$fase[0]['proy_id']);
                }
                else{
                    $this->session->set_flashdata('danger','ERROR EN EL REGISTRO DEL COMPONENTE');
                    redirect(site_url("").'/prog/list_serv/'.$fase[0]['proy_id']);
                }           
          }
          else{
            $this->session->set_flashdata('danger','NO INGRESAN LOS DATOS ');
            redirect(site_url("").'/prog/list_serv/'.$fase[0]['proy_id']);
          }

      } else {
          show_404();
      }
    }

    /*------ Valida Update Componente (2026) optimizar -----*/
    public function valida_update_componente(){
      if ($this->input->post()) {
          $post = $this->input->post();
          $com_id = $this->security->xss_clean($post['com_id']); /// com id
          $serv_id = $this->security->xss_clean($post['mserv_id']); /// Descripcion Componente 
          $comp = $this->security->xss_clean($post['mcomponente']); //// Codigo
          $componente=$this->model_componente->get_componente($com_id,$this->gestion);
            $fase = $this->model_faseetapa->get_fase($componente[0]['pfec_id']);
            $proyecto = $this->model_proyecto->get_id_proyecto($fase[0]['proy_id']);

          if(isset($com_id) & isset($serv_id) & isset($componente)){
              
              /*--------- COMPONENTE ----------*/
              $update_comp = array(
                'serv_id' => $serv_id,
                'com_componente' => strtoupper($comp), 
                'estado' => 2,
                'fun_id' => $this->fun_id
                );
                $this->db->where('com_id', $com_id);
                $this->db->update('_componentes', $update_comp);

                $this->session->set_flashdata('success','EL REGISTRO SE MODIFICO CORRECTAMENTE');
                redirect(site_url("").'/prog/list_serv/'.$fase[0]['proy_id']);

          }
          else{
            $this->session->set_flashdata('danger','ERROR EN EL REGISTRO DEL COMPONENTE');
            redirect(site_url("").'/prog/list_serv/'.$fase[0]['proy_id']);
          }

      } else {
          show_404();
      }
    }




















    /*---- CONSOLIDADO DE OPERACIONES POR SUB ACTIVIDADES, COMPONENTES (2019)----*/
/*    public function reporte_consolidado_operaciones_componentes($proy_id){
        $data['proyecto']=$this->model_proyecto->get_id_proyecto($proy_id);
        if(count($data['proyecto'])!=0){
            $data['mes'] = $this->mes_nombre();
            $data['componente_operaciones']=$this->get_proceso_consolidado($proy_id);
            $this->load->view('admin/programacion/componente/reporte_operaciones_componentes', $data);
        }
        else{
            echo "<center><b>ERROR!!!! AL GENERAR REPORTE</b></center>";
        }
    }*/

    /*------- LISTA DE OPERACIONES POR SUB ACTIVIDADES (2019) ------*/
    // public function get_proceso_consolidado($proy_id){
    //   $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); //// DATOS DEL PROYECTO
    //   $fase = $this->model_faseetapa->get_id_fase($proy_id); //// DATOS FASE ACTIVA
    //   $componentes=$this->model_componente->componentes_id($fase[0]['id'],$proyecto[0]['tp_id']); /// COMPONENTES/PROCESOS  
        
    //     $tabla ='';
    //     if(count($componentes)!=0){
    //         foreach ($componentes as $rowc){
    //             $productos = $this->model_producto->list_prod($rowc['com_id']);
    //             if(count($productos)!=0){
    //                 $tabla .='
    //                 <table>
    //                     <tr><td><font size="1"> '.$rowc['serv_cod'].'.- '.$rowc['com_componente'].'</font></td></tr>
    //                 </table>';
    //                 $nro_p=0;
    //                 $tabla .='<table border="0" cellpadding="0" cellspacing="0" class="tabla">';
    //                     $tabla.='<thead>
    //                             <tr class="modo1" style="height:45px;">
    //                             <th style="width:1%;" bgcolor="#1c7368"><font color="#ffffff">#</font></th>';
    //                             if($this->gestion==2018){
    //                               $tabla.='<th style="width:7%;" bgcolor="#1c7368"><font color="#ffffff">PRODUCTO</font></th>';
    //                             }
    //                             else{
    //                               $tabla.='
    //                                   <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">OBJETIVO ESTRATEGICO</font></th>
    //                                   <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">ACCI&Oacute;N ESTRATEGICA</font></th>
    //                                   <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">OPERACI&Oacute;N</font></th>
    //                                   <th style="width:9%;" bgcolor="#1c7368"><font color="#ffffff">RESULTADO</font></th>';
    //                             }
    //                             $tabla.='
    //                             <th style="width:2%;" bgcolor="#1c7368"><font color="#ffffff">TIP.</font></th>
    //                             <th style="width:8%;" bgcolor="#1c7368"><font color="#ffffff">INDICADOR</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">LINEA BASE</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">META</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">ENE.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">FEB.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">MAR.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">ABR.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">MAY.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">JUN.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">JUL.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">AGO.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">SEP.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">OCT.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">NOV.</font></th>
    //                             <th style="width:3%;" bgcolor="#1c7368"><font color="#ffffff">DIC.</font></th>
    //                             <th style="width:8%;" bgcolor="#1c7368"><font color="#ffffff">VERIFICACI&Oacute;N</font></th>
    //                         </tr>
    //                         </thead>
    //                     <tbody>';
    //                     $nro=0;
    //                     foreach($productos as $rowp){
    //                       $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
    //                       $color='';
    //                         if(($sum[0]['meta_gest']+$rowp['prod_linea_base'])!=$rowp['prod_meta']){
    //                           $color='#fbd5d5';
    //                         }
    //                         $nro++;
    //                         $tabla.='<tr class="modo1" bgcolor="'.$color.'" style="height:45px;">';
    //                         $tabla.='<td style="width: 1%; text-align: center" style="height:14px;">'.$nro.'</td>';
    //                           if($this->gestion==2018){
    //                            $tabla.='<td style="width: 7%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_producto'].'', 'cp1252', 'UTF-8').'</td>'; 
    //                           }
    //                           else{
    //                             if($rowp['acc_id']!=null){
    //                               $alineacion=$this->model_producto->operacion_accion($rowp['acc_id']);
    //                               if(count($alineacion)!=0){
    //                                 $tabla.=' <td style="width: 9%; text-align: left">'.$alineacion[0]['obj_codigo'].'-'.$alineacion[0]['obj_descripcion'].'</td>
    //                                           <td style="width: 9%; text-align: left">'.$alineacion[0]['acc_codigo'].'-'.$alineacion[0]['acc_descripcion'].'</td>';
    //                               }
    //                               else{
    //                                 $tabla.=' <td style="width: 9%; text-align: left"></td>
    //                                           <td style="width: 9%; text-align: left"><font color="red">'.$rowp['acc_id'].'</font></td>';
    //                               }
    //                             }
    //                             else{
    //                               $tabla.=' <td style="width: 9%; text-align: left"></td>
    //                                         <td style="width: 9%; text-align: left"><font color="red"></font></td>';
    //                             }
    //                             $tabla.='<td style="width: 9%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_producto'].'', 'cp1252', 'UTF-8').'</td>
    //                                      <td style="width: 9%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_resultado'].'', 'cp1252', 'UTF-8').'</td>';
    //                           }
                              
                              
    //                           $tabla.='
    //                                    <td style="width: 2%; text-align: left">'.mb_convert_encoding(''.$rowp['indi_abreviacion'].'', 'cp1252', 'UTF-8').'</td>
    //                                    <td style="width: 8%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_indicador'].'', 'cp1252', 'UTF-8').'</td>
    //                                    <td style="width: 3%; text-align: left">'.$rowp['prod_linea_base'].'</td>
    //                                    <td style="width: 3%; text-align: left">'.$rowp['prod_meta'].'</td>';
    //                                    $tabla.=''.$this->temporalizacion_prod($rowp['prod_id'],$this->gestion).'';
    //                           $tabla .='<td style="width: 8%; text-align: left">'.mb_convert_encoding(''.$rowp['prod_fuente_verificacion'].'', 'cp1252', 'UTF-8').'</td>';         
    //                         $tabla.='</tr>';
    //                     }
    //                     $tabla.='
    //                     </tbody>
    //                 </table>'; 
    //             }
    //         }
    //     }

    //   return $tabla;
    // }

     /*--------- TEMPORALIDAD PROGRAMACION FISICA (2019)---------*/
// /*    public function temporalizacion_prod($prod_id,$gestion){
//         $prod=$this->model_producto->get_producto_id($prod_id); /// Producto Id
//         $programado=$this->model_producto->producto_programado($prod_id,$gestion); /// Producto Programado
//         $tp='';
//         if($prod[0]['indi_id']==2){$tp='%';};
//         $m[0]='g_id';
//         $m[1]='enero';
//         $m[2]='febrero';
//         $m[3]='marzo';
//         $m[4]='abril';
//         $m[5]='mayo';
//         $m[6]='junio';
//         $m[7]='julio';
//         $m[8]='agosto';
//         $m[9]='septiembre';
//         $m[10]='octubre';
//         $m[11]='noviembre';
//         $m[12]='diciembre';

//         for ($i=1; $i <=12 ; $i++) { 
//             $prog[1][$i]=0;
//             $prog[2][$i]=0;
//             $prog[3][$i]=0;
//         }

//         $pa=0;
//         if(count($programado)!=0){
//             for ($i=1; $i <=12 ; $i++) { 
//                 $prog[1][$i]=$programado[0][$m[$i]];
// /*                $pa=$pa+$prog[1][$i];
//                 $prog[2][$i]=$pa+$prod[0]['prod_linea_base'];

//               if($prod[0]['prod_meta']!=0){
//                 $prog[3][$i]=round(((($pa+$prod[0]['prod_linea_base'])/$prod[0]['prod_meta'])*100),1);
//               } */ 
//             } 
//         }
//         $tr_return = '';
//           for($i = 1 ;$i<=12 ;$i++){
//             $tr_return .= '<td bgcolor="#d2f5d2" style="width: 3%; text-align: right" title="'.$m[$i].'"><b>'.$prog[1][$i].''.$tp.'</b></td>';
//           }
                                 
//         return $tr_return;
//     }*/


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