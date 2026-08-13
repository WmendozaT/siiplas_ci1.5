<?php
class Proyecto extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->library('pdf2');
      //$this->load->model('menu_modelo');
      //$this->load->model('Users_model','',true);
      $this->load->model('programacion/model_faseetapa');
      $this->load->model('programacion/model_proyecto');
      $this->load->model('programacion/model_componente');
      $this->load->model('programacion/model_producto');
      $this->load->model('analisis_situacion/model_analisis_situacion');
      $this->load->model('mantenimiento/mapertura_programatica');
      //$this->load->model('mantenimiento/munidad_organizacional');
      $this->load->model('mantenimiento/model_estructura_org');
      //$this->load->model('programacion/insumos/minsumos');
      $this->load->model('programacion/insumos/model_insumo');
      $this->load->model('mestrategico/model_objetivoregion');
      $this->load->model('mantenimiento/model_ptto_sigep');
      $this->load->library('security');
      $this->gestion = $this->session->userData('gestion');
      $this->adm = $this->session->userData('adm'); // 1: Nacional, 2: Regional, Distrital
      $this->dist = $this->session->userData('dist');
      $this->rol = $this->session->userData('rol_id');
      $this->fun_id = $this->session->userdata("fun_id");
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->dist_tp = $this->session->userData('dist_tp'); /// dist_tp->1 Regional, dist_tp->0 Distritales
      $this->verif_ppto = $this->session->userData('verif_ppto'); /// AnteProyecto Ptto POA : 0, Ptto Aprobado Sigep : 1
      $this->conf_form3 = $this->session->userData('conf_form3');
      $this->conf_form4 = $this->session->userData('conf_form4');
      $this->conf_form5 = $this->session->userData('conf_form5');
      $this->conf_poa_estado = $this->session->userData('conf_poa_estado'); /// Ajuste POA 1: Inicial, 2 : Ajuste, 3 : aprobado
      $this->load->library('programacionpoa');
      }else{
          $this->session->sess_destroy();
          redirect('/','refresh');
      }
    }


  /*=== Programacion - LISTA POA 2027 (Anteproyecto) ===*/  
    public function list_poa(){
      $data['menu']=$this->programacionpoa->menu(2);
      $data['mod']=1;
      $data['estilo']=$this->programacionpoa->estilo_tabla();
      $data['modal_form4']=$this->programacionpoa->modal_migracion_form4_institucional(); /// modal para subir form4 de manera general
      $tabla='';
      $titulo_btn_prog='PROG. FORM. N 3';
      if($this->conf_form3==0){
        $titulo_btn_prog='PROG. FORM. N 4 - 5';
      }
      $tabla .= $this->programacionpoa->tp_resp();
      $tabla .= '<input name="base" type="hidden" value="'.base_url().'">

    <div id="tabs" style="border: none; background: transparent;">
        <!-- 📌 MENÚ DE PESTAÑAS PRINCIPALES (TABS) -->
        <ul style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1; padding: 0; border-radius: 4px 4px 0 0;">
            <li style="margin-bottom: -2px;">
                <a href="#tabs-c" style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11.5px; color: #1e293b; text-transform: uppercase; padding: 10px 16px;"><i class="fa fa-folder-open text-primary"></i> Gasto Corriente</a>
            </li>
            <li style="margin-bottom: -2px;">
                <a href="#tabs-a" style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11.5px; color: #1e293b; text-transform: uppercase; padding: 10px 16px;"><i class="fa fa-university text-success"></i> Proyectos de Inversión Pública</a>
            </li>
        </ul>

        <div id="tabs-c" style="padding: 15px 0 0 0; background: transparent;">
            <div class="row">
                <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-darken" style="margin-bottom: 15px;">
                        <header style="background: #334155; color: #ffffff; height: 38px; display: flex; align-items: center; padding: 0 10px; border-radius: 4px 4px 0 0;">
                            <span class="widget-icon" style="margin-right: 8px;"> <i class="fa fa-arrows-v text-muted"></i> </span>
                            <h2 class="font-md" style="margin: 0; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.3px;"><strong>Gasto Corriente - Gestión Regular</strong></h2>  
                        </header>
                        <div>
                            <div class="widget-body no-padding" style="background: #ffffff; padding: 15px !important; border: 1px solid #cbd5e1; border-top: none; border-radius: 0 0 4px 4px;">
                                
                                <!-- 🛠️ BARRA DE ACCIONES SUPERIOR: GASTO CORRIENTE -->
                                <div style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 6px; background: #f8fafc; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 4px;">
                                    <!-- Botón Agregar Unidad -->
                                    <a href="'.site_url("proy/add_unidad").'" 
                                       class="btn btn-sm btn-default" 
                                       title="AGREGAR NUEVA UNIDAD ORGANIZACIONAL" 
                                       target="_blank" rel="noopener noreferrer"
                                       style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11px; padding: 5px 12px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;"
                                       onmouseover="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#94a3b8\';"
                                       onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">
                                        <i class="fa fa-plus text-primary" style="font-size: 12px;"></i> Agregar Unidad
                                    </a>';
                                    
                                    if($this->fun_id == 399) {
                                        // Botón Importar Excel Saneado en Verde Institucional
                                        $tabla .= '
                                        <a href="#" 
                                           data-toggle="modal" 
                                           data-target="#modal_importar" 
                                           class="btn btn-sm btn-default importar_ff" 
                                           title="MIGRAR PLANILLA EXCEL ACTIVIDADES"
                                           style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11px; padding: 5px 12px; background: #16a34a; border: 1px solid #15803d; color: #ffffff; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;"
                                           onmouseover="this.style.background=\'#15803d\'; this.style.borderColor=\'#166534\';"
                                           onmouseout="this.style.background=\'#16a34a\'; this.style.borderColor=\'#15803d\';">
                                            <i class="fa fa-cloud-upload" style="font-size: 12px; color: #ffffff;"></i> Subir Formulario N° 4 (.xls)
                                        </a>';
                                    }
                                    
                                $tabla .= '
                                </div>

                                <!-- 🌟 REPARADO: Contenedor elástico responsivo con scrolling horizontal controlado -->
                                <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px;">
                                    <table id="dt_basic3" class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; min-width: 1500px; font-size: 11px; border-collapse: collapse;">
                                        <thead>
                                            <tr style="height: 42px; background: #475569; color: #ffffff; text-transform: uppercase; font-size: 9.5px; letter-spacing: 0.3px;">
                                                <th style="width:1%; text-align: center; vertical-align: middle;">#</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">VALIDAR POA</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">REPORTE POA</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">CONSOLIDADO</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">FORM. N° 3</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">PROG. POA</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">MODIFICAR</th>
                                                <th style="width:5%; text-align: center; vertical-align: middle;">ELIMINAR</th>
                                                <th style="width:10%; text-align: left; vertical-align: middle;">CATEGORIA PROGRAMÁTICA '.$this->gestion.'</th>
                                                <th style="width:20%; text-align: left; vertical-align: middle;">GASTO CORRIENTE DESCRIPCIÓN</th>
                                                <th style="width:8%; text-align: left; vertical-align: middle;">DISTRITAL</th>
                                                <th style="width:9%; text-align: right; vertical-align: middle; background-color: #1e3a8a;">PPTO. ASIGNADO</th>
                                                <th style="width:9%; text-align: right; vertical-align: middle; background-color: #d97706;">PPTO. POA</th>
                                                <th style="width:9%; text-align: right; vertical-align: middle;">SALDO REMANENTE</th>
                                                <th style="width:4%; text-align: center; vertical-align: middle;">ESTADO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            '.$this->list_unidades_es(1).'
                                        </tbody>
                                    </table>
                                </div> <!-- Fin .table-responsive -->

                            </div>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div id="tabs-a" style="padding: 15px 0 0 0; background: transparent;">
            <div class="row">';
                if($this->session->userdata('rol_id') == 1) {
                    // Botón Aperturar Proyecto Saneado en Blanco Ejecutivo
                    $tabla .= ' 
                    <div style="margin-bottom: 15px; padding-left: 15px;">
                        <a href="'.site_url("admin/proy/proyecto").'" 
                           class="btn btn-sm btn-default" 
                           title="APERTURAR NUEVO PROYECTO DE INVERSIÓN" 
                           target="_blank" rel="noopener noreferrer"
                           style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11px; padding: 5px 12px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; border-radius: 3px; display: inline-flex; align-items: center; gap: 5px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;"
                           onmouseover="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#94a3b8\';"
                           onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">
                            <i class="fa fa-plus text-success" style="font-size: 12px;"></i> Aperturar Proyecto PIP
                        </a>
                    </div>';
                }
                
                $tabla .= '
                <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                          <div class="jarviswidget jarviswidget-color-darken" >
                            <header>
                              <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                                <h2 class="font-md"><strong>PROYECTOS DE INVERSI&Oacute;N PUBLICA </strong></h2>  
                            </header>
                            <div>
                              <div class="widget-body no-padding">
                                <table id="dt_basic" class="table table-bordered" style="width:100%;">
                                  <thead>
                                    <tr style="height:65px;">
                                      <th style="width:4%;">VALIDAR</th>
                                      <th style="width:4%;">PROG. POA</th>
                                      <th style="width:4%;" title="REPORTE POA">REPORTE POA</th>
                                      <th style="width:4%;" title="MODIFICAR">MODIFICAR</th>
                                      <th style="width:4%;" title="FASE">FASE</th>
                                      <th style="width:4%;" title="ELIMINAR">ELIMINAR</th>
                                      <th style="width:10%;" title="APERTURA PROGRAM&Aacute;TICA">CATEGORIA PROGRAM&Aacute;TICA '.$this->gestion.'</th>
                                      <th style="width:20%;" title="NOMBRE DEL PROYECTO DE INVERSI&Oacute;N">PROYECTO DE INVERSI&Oacute;N</th>
                                      <th style="width:10%;" title="C&Oacute;DIGO SISIN">C&Oacute;DIGO SISIN</th>
                                      <th style="width:10%;" title="UNIDAD ADMINISTRATIVA">UNIDAD_ADMINISTRATIVA</th>
                                      <th style="width:10%;" title="UNIDAD EJECUTORA">UNIDAD_EJECUTORA</th>
                                      <th style="width:10%;" title="FASE - ETAPA DE LA OPERACI&Oacute;N">FASE_ETAPA</th>
                                      <th style="width:10%;" title="NUEVO - CONTINUO">NUEVO_CONTINUIDAD</th>
                                      <th style="width:10%;" title="TIEMPO DE OPERACI&Oacute;N">ANUAL_PLURIANUAL</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    '.$this->list_pinversion(1).'
                                  </tbody>
                                </table>
                              </div>
                            </div>
                          </div>
                </article>
            </div>
        </div>
    </div>';

        $data['listado']=$tabla;
        $this->load->view('admin/programacion/proy_anual/top/list_proy', $data);
    }

    /*--- Programacion - POA APROBADO (2027) ---*/
    public function list_proyectos_aprobados(){
      $data['menu']=$this->programacionpoa->menu(2);
      $data['estilo']=$this->programacionpoa->estilo_tabla();

      $listado='';
      $listado.='

      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="well well-sm well-light">
            '.$this->programacionpoa->tp_resp().'
            <input name="base" type="hidden" value="'.base_url().'">
            <div id="tabs">
              <ul>
                <li>
                  <a href="#tabs-c">GASTO CORRIENTE</a>
                </li>
                <li>
                  <a href="#tabs-a">PROYECTOS DE INVERSI&Oacute;N PUBLICA</a>
                </li>
              </ul>
              <div id="tabs-c">
                <div class="row">
                  <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                  <div class="jarviswidget jarviswidget-color-darken">
                      <header>
                        <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                        <h2 class="font-md"><strong>GASTO CORRIENTE</strong></h2>  
                      </header>
                    <div>
                      <div class="widget-body no-padding">
                        <table id="dt_basic3" class="table1 table-bordered" style="width:100%;">
                          <thead>
                            <tr style="height:65px;">
                              <th style="width:1%;">#</th>
                              <th style="width:5%;" title="REPORTE FORMULARIO 3">FORM. 3</th>
                              <th style="width:5%;" title="REPORTE POA">REP. POA - FORM. 4 Y 5</th>';
                                if($this->tp_adm==1){
                                  $listado.='
                                  <th style="width:5%;" title="RECHAZAR POA">RECHAZAR POA</th>
                                  <th style="width:5%;" title="APROBAR POA">APROBAR POA</th>';
                                }
                              $listado.='
                              <th style="width:5%;" title="REPORTE POA APROBADO">REP. POA '.$this->gestion.'</th>
                              <th style="width:10%;" title="APERTURA PROGRAM&Aacute;TICA">CATEGORIA PROGRAM&Aacute;TICA '.$this->gestion.'</th>
                              <th style="width:20%;" title="DESCRIPCI&Oacute;N">GASTO CORRIENTE</th>
                              <th style="width:10%;" title="UNIDAD EJECUTORA">UNIDAD EJECUTORA</th>
                              <th style="width:10%;" title="PPTO">PPTO. ASIGNADO</th>
                              <th style="width:10%;" title="PPTO">PPTO. POA</th>
                              <th style="width:10%;" title="SALDO">SALDO</th>
                              <th style="width:5%;" title="ESTADO POA"></th>
                            </tr>
                          </thead>
                          <tbody>
                          '.$this->list_unidades_es(4).'
                          </tbody>
                        </table>
                      </div>
                      <!-- end widget content -->
                    </div>
                    <!-- end widget div -->
                  </div>
                  <!-- end widget -->
                  </article>
                </div>
              </div>

              <div id="tabs-a">
                <div class="row">
                  <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="jarviswidget jarviswidget-color-darken" >
                        <header>
                          <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                          <h2 class="font-md"><strong>PROYECTOS DE INVERSI&Oacute;N PUBLICA </strong></h2>  
                        </header>
                      <div>
                        <div class="widget-body no-padding">
                          <table id="dt_basic" class="table table-bordered" style="width:100%;">
                            <thead>
                              <tr style="height:60px;">
                                <th style="width:1%;"></th>
                                <th style="width:5%;"title="REPORTE POA">REPORTE POA</th>
                                <th style="width:5%;" title="REPORTE POA APROBADO">REP. POA '.$this->gestion.'</th>
                                <th style="width:5%;" title="ERROR EN EL POA"></th>
                                <th style="width:10%;" title="APERTURA PROGRAM&Aacute;TICA">CATEGORIA PROGRAM&Aacute;TICA <?php echo $this->session->userdata("gestion");?></th>
                                <th style="width:25%;" title="NOMBRE DEL PROYECTO DE INVERSI&Oacute;N">PROYECTO DE INVERSIÓN</th>
                                <th style="width:10%;" title="C&Oacute;DIGO SISIN">C&Oacute;DIGO_SISIN</th>
                                <th style="width:15%;" title="UNIDAD ADMINISTRATIVA">UNIDAD_ADMINISTRATIVA</th>
                                <th style="width:15%;" title="UNIDAD EJECUTORA">UNIDAD_EJECUTORA</th>
                                <th style="width:20%;" title="FASE - ETAPA DE LA OPERACI&Oacute;N">FASE_ETAPA</th>
                                <th style="width:10%;" title="GESTION ACTIVA">GESTI&Oacute;N ACTIVA</th>
                              </tr>
                            </thead>
                            <tbody>
                            '.$this->list_pinversion(4).'
                            </tbody>
                          </table>
                        </div>
                        <!-- end widget content -->
                      </div>
                      <!-- end widget div -->
                    </div>
                  <!-- end widget -->
                  </article>
                </div>
              </div>
            </div>
          </div>
        </article>';

      $data['listado']=$listado;
      $this->load->view('admin/programacion/proy_anual/aprobados/list_proy', $data);
    }



    /*---- Lista de Unidades / Establecimientos de Salud (2027) -----*/
    public function list_unidades_es($proy_estado){
      $unidades=$this->model_proyecto->list_unidades(4,$proy_estado);
      $tabla='';
      $nro=0;

      if($proy_estado==1){ /// Inicial
        foreach($unidades as $row){
          
          $nro++;
          $tabla.='<tr style="height:35px;">';
            $tabla.= '<td align=center title="'.$row['aper_id'].'"><b>'.$nro.'</b></td>';
            $tabla.='<td bgcolor="#fafafa">';
              if($this->conf_form5==1){
                $tabla.='
                  <div style="text-align: center;">
                    <a href="#" 
                       data-toggle="modal" 
                       data-target="#modal_verif_poa" 
                       class="btn btn-sm btn-default verif_poa" 
                       name="' . $row['proy_id'] . '" 
                       id="' . htmlspecialchars(strtoupper($row['tipo'] . " " . $row['proy_nombre'] . " - " . $row['abrev']), ENT_QUOTES, 'UTF-8') . '" 
                       title="FISCALIZAR Y VALIDAR COMPROMISOS POA"
                       style="font-family: Arial, sans-serif; font-weight: bold; font-size: 11px; padding: 20px 25px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; border-radius: 3px; display: inline-flex; align-items: center; justify-content: center; height: 26px; transition: all 0.15s ease;"
                       onmouseover="this.style.background=\'#f0fdf4\'; this.style.borderColor=\'#bbf7d0\';"
                       onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">
                        <i class="fa fa-check text-success" style="font-size: 13px;"></i>
                    </a>
                </div>';
              }
            $tabla.='</td>';
            $tabla .='<td bgcolor="#fafafa">';
              if($this->conf_form4==1){
                $tabla.='<center><a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default enlace" name="'.$row['proy_id'].'" id="'.strtoupper($row['tipo']).' '.strtoupper($row['proy_nombre']).' - '.strtoupper($row['abrev']).'"><img src="'.base_url().'assets/ifinal/doc.jpg" WIDTH="30" HEIGHT="30" title="VER POA '.$this->gestion.'"/></a></center>';
              }
            $tabla.='</td>';
            $tabla .='<td bgcolor="#fafafa">';
              if($this->conf_form4==1){
                $tabla.='<center><a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_consolidado/'.$row['proy_id'].'\');" class="btn btn-default" title="CONSOLIDADO POA"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30" title="VER POA '.$this->gestion.'"/></a></center>';
              }
            $tabla.='
            </td>
            <td style="text-align: center; vertical-align: middle; padding: 4px; background: #ffffff; border: 1px solid #cbd5e1;">';
                if ($this->conf_form3 == 1) {
                    $tabla .= '<div style="display: inline-flex; gap: 4px; justify-content: center; align-items: center; width: 100%;">';
                        
                        // 👁️ Botón Formulario FODA (Estilo Blanco Formal)
                        $tabla .= '<a href="' . site_url("as/list_foda/" . $row['proy_id']) . '" 
                                     title="INGRESAR AL FORMULARIO FODA V2.0" 
                                     class="btn btn-sm btn-default" 
                                     target="_blank" rel="noopener noreferrer"
                                     style="padding: 10px 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 3px; display: inline-flex; align-items: center; justify-content: center; height: 40px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;" 
                                     onmouseover="this.style.background=\'#f1f5f9\'; this.style.borderColor=\'#94a3b8\';" 
                                     onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#cbd5e1\';">';
                            $tabla .= '<img src="'.base_url().'assets/ifinal/mod.png" WIDTH="30" HEIGHT="30"/>';
                        $tabla .= '</a>';
                        
                        // 🖨️ Botón Imprimir FODA - Condicional (Estilo Azul Auditoría)
                        if (count($this->model_analisis_situacion->list_analisis_problemas_reporte($row['proy_id'])) != 0) {
                            $tabla .= '<a href="javascript:abreVentana(\'' . site_url("as/rep_list_foda/" . $row['proy_id']) . '\');" 
                                         title="IMPRIMIR FORMULARIO FODA (PDF)" 
                                         class="btn btn-sm btn-default" 
                                         style="padding: 10px 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 3px; display: inline-flex; align-items: center; justify-content: center; height: 40px; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;" 
                                         onmouseover="this.style.background=\'#dbeafe\'; this.style.borderColor=\'#3b82f6\';" 
                                         onmouseout="this.style.background=\'#eff6ff\'; this.style.borderColor=\'#bfdbfe\';">';
                                $tabla .= '<img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30" title="VER POA '.$this->gestion.'"/>';
                            $tabla .= '</a>';
                        }
                        
                    $tabla .= '</div>';
                }
            $tabla .= '
            </td>
            <td bgcolor="#fafafa">';
                if($this->conf_form4==1 || $this->conf_form5==1){
                  $tabla.='<center><a href="'.site_url("").'/prog/list_serv/'.$row['proy_id'].'" title="PROGRAMACION F&Iacute;SICA - FINANCIERA" target=_blank class="btn btn-default"><img src="'.base_url().'assets/ifinal/bien.png" WIDTH="30" HEIGHT="30"/></a></center>';
                }
            $tabla.='
            </td>';
            $tabla .= '<td aling="center" bgcolor="#F7F9BC">';
              if($this->conf_form4==1 || $this->fun_id==401 || $this->fun_id==399){
                $tabla .= '<center><a href="'.site_url("").'/proy/update_unidad/'.$row['proy_id'].'" title="MODIFICAR" class="btn btn-default"><img src="'.base_url().'assets/ifinal/modificar.png" WIDTH="30" HEIGHT="30"/></a></center>';
              }
            $tabla .= '</td>';
            $tabla .= '<td aling="center" bgcolor="#F7F9BC">';
              /*---------------------------------------------*/
              if($this->conf_form4==1 || $this->fun_id==401 || $this->fun_id==399){
                $tabla .= '<center><a href="'.site_url("admin").'/proy/delete/1/'.$row['proy_id'].'" title="ELIMINAR" onclick="return confirmar()" class="btn btn-default"><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="30" HEIGHT="30"/></a></center>';
              }                 
            $tabla .= '</td>';
            $tabla .= '<td style="font-size: 14px;"><center><b>'.$row['aper_programa'].' '.$row['aper_proyecto'].' '.$row['aper_actividad'].'</b></center></td>';
            $tabla.='<td style="font-size: 11px;"><b>'.$row['tipo'].' '.$row['act_descripcion'].' - '.$row['abrev'].'</b></td>';
            $tabla.='<td>'.strtoupper($row['dist_distrital']).'</td>';
            $tabla.='<td>'.number_format($row['ppto_asignado'], 2, ',', '.').'</td>';
            $tabla.='<td>'.number_format($row['ppto_poa'], 2, ',', '.').'</td>';
            $tabla.='<td>'.number_format($row['ppto_saldo'], 2, ',', '.').'</td>';
            $tabla.='<td><b>'.$row['estado_poa'].'</b></td>';
          $tabla.='</tr>';
        }
      }
      else{ /// Aprobado
        $nro=0;
        foreach($unidades as $row){
        $nro++;
        $color='#f5e9ce';
          $estado='REVISI&Oacute;N';
          if($row['proy_estado']==4){
            $color='#ccefcc';
            $estado='APROBADO';
          }
        $tabla.='
          <tr style="height:35px;" bgcolor="'.$color.'">
            <td><center>'.$nro.'</center></td>
            <td>';
              if($row['te_id']!=14 & $row['te_id']!=17 & $row['te_id']!=18 & $row['te_id']!=20){
                $tabla .= '<center><a href="javascript:abreVentana_poa(\''.site_url("").'/as/rep_list_foda/'.$row['proy_id'].'\');" title="REPORTE FORMULARIO N 3" class="btn btn-success">FORM N 3</a></center>';
              }
            $tabla.='
            </td>
            <td><center><a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-success enlace" name="'.$row['proy_id'].'" id="'.strtoupper($row['tipo']).' '.strtoupper($row['proy_nombre']).' '.strtoupper($row['abrev']).'">VER POA</a></center></td>';
            if($this->tp_adm==1){ 
              $tabla .= '<td><center><a href="#" data-toggle="modal" data-target="#modal_neg_ff" class="btn btn-danger neg_ff" title="OBSERVAR POA"  name="'.$row['proy_id'].'" ><img src="'.base_url().'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></a></center></td>';
                $tabla .= '
                        <td>';
                          if($row['proy_estado']!=4){
                            $tabla.='<center><a href="#" data-toggle="modal" data-target="#modal_aprobar_poa" class="btn btn-success aprobar_poa" title="APROBAR POA"  name="'.$row['proy_id'].'" ><img src="'.base_url().'assets/img/ok1.jpg" WIDTH="35" HEIGHT="35"/></a></center>';
                          }
                          $tabla.='
                        </td>';
            }
            $tabla.='
            <td>';
              if($row['aper_proy_estado']==4){
                 $tabla.='<center><a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_consolidado/'.$row['proy_id'].'\');" title="REPORTE CONSOLIDADO POA" class="btn btn-default"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30"/></a></center>';
              }
            $tabla.='
            </td>
            <td><center>'.$row['aper_programa'].''.$row['aper_proyecto'].''.$row['aper_actividad'].'</center></td>
            <td>'.$row['tipo'].' '.$row['act_descripcion'].' - '.$row['abrev'].'</td>
            <td>'.strtoupper($row['dist_distrital']).'</td>
            <td>'.number_format($row['ppto_asignado'], 2, ',', '.').'</td>
            <td>'.number_format($row['ppto_poa'], 2, ',', '.').'</td>
            <td>'.number_format($row['ppto_saldo'], 2, ',', '.').'</td>
            <td><b>'.$row['estado_poa'].'</b></td>
          </tr>';
        }
      }
      return $tabla;
    }

    /*---- Lista de Proyectos de Inversion (2020) -----*/
    public function list_pinversion($proy_estado){
      $tabla='';
      $proyectos=$this->model_proyecto->list_unidades(1,$proy_estado);
      $tabla='';
      if($proy_estado==1){
        foreach($proyectos as $row){
          $fase = $this->model_faseetapa->get_id_fase($row['proy_id']);
          $tabla.='<tr style="height:35px;">';
            $tabla .='<td title="aper_id '.$row['aper_id'].'" bgcolor="#5B9360">';
              if(count($this->model_insumo->insumos_por_unidad($row['aper_id']))!=0){
                $tabla .= '<center><a href="#" data-toggle="modal" data-target="#modal_aprob_pi" class="btn btn-default aprob_pi" title="VALIDAR PROYECTO POA" name="'.$row['proy_id'].'" ><img src="'.base_url().'assets/img/ok1.jpg" WIDTH="35" HEIGHT="35"/></a></center><br>';
              }
            $tabla .='</td>';
            $tabla .= '<td title="'.$row['proy_id'].' - '.$row['aper_id'].'" bgcolor="#5B9360">';
              if(count($fase)!=0){
                if($this->adm==1){ 
                  $tabla .= '<center><a href="'.site_url("").'/prog/list_serv/'.$row['proy_id'].'" title="PROGRAMACION FÍSICA" class="btn btn-default"><img src="'.base_url().'assets/ifinal/bien.png" WIDTH="35" HEIGHT="35"/></a></center>';
                }
              }
            $tabla .= '</td>';

            $tabla .='<td bgcolor="#5B9360"><center><a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default enlace" name="'.$row['proy_id'].'" id="'.strtoupper($row['proy_nombre']).'"><img src="'.base_url().'assets/ifinal/doc.jpg" WIDTH="35" HEIGHT="35"/></a></center></td>';

              $tabla .= '<td bgcolor="#5B9360"><center><a href="'.site_url("admin").'/proy/edit/'.$row['proy_id'].'" title="MODIFICAR OPERACION" class="btn btn-default" target=_blank><img src="'.base_url().'assets/ifinal/modificar.png" WIDTH="35" HEIGHT="35"/></a></center></td>';
              $tabla .='<td bgcolor="#5B9360"><center><a href="'.site_url("admin").'/proy/fase_etapa/'.$row['proy_id'].'" title="FASE ETAPA DEL PROYECTO" class="btn btn-default" target=_blank><img src="'.base_url().'assets/ifinal/faseetapa.png" WIDTH="35" HEIGHT="35"/></a></center></td>';
              /*---------------------------------------------*/
              $tabla .='<td bgcolor="#F7F9BC">';
              if($this->tp_adm==1){
                $tabla .= '<center><a href="'.site_url("admin").'/proy/delete/1/'.$row['proy_id'].'" title="ELIMINAR" onclick="return confirmar()" class="btn btn-default"><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a></center>';
              }
              else{
                $tabla .= '<center><a href="#" title="ELIMINAR" class="btn btn-default" title="OPCION NO VALIDA"><img src="'.base_url().'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a></center>';
              }                            
            $tabla.='</td>';
            $tabla.='<td><center>'.$row['aper_programa'].''.$row['proy_sisin'].''.$row['aper_actividad'].'</center></td>';
            $tabla.='<td>'.$row['proy_nombre'].'</td>';
            $tabla.='<td>'.$row['proy_sisin'].'</td>';
            $tabla.='<td>'.strtoupper($row['dep_departamento']).'</td>';
            $tabla.='<td>'.strtoupper($row['dist_distrital']).'</td>';
            if(count($fase)!=0){
              $nc=$this->model_faseetapa->calcula_nc($fase[0]['pfec_fecha_inicio']); //// calcula nuevo/continuo
              $ap=$this->model_faseetapa->calcula_ap($fase[0]['pfec_fecha_inicio'],$fase[0]['pfec_fecha_fin']);
              $tabla .='<td>* '.$fase[0]['fase'].'<br>* '.$fase[0]['etapa'].'</td>';
              $tabla .='<td>'.$nc.'</td>';
              $tabla .='<td>'.$ap.'</td>';
            }
            else{
              $tabla .='<td bgcolor=#efb0b0><font color=red>Sin Fase</font></td>';
              $tabla .='<td bgcolor=#efb0b0><font color=red>Sin Fase</font></td>';
              $tabla .='<td bgcolor=#efb0b0><font color=red>Sin Fase</font></td>';
            }
            
          $tabla.='</tr>';
        }
      }
      else{
        $nro=0;
        foreach($proyectos as $row){
          $nro++;
          $tabla.='<tr style="height:35px;">';
           $tabla .= '<td title='.$row['proy_id'].'><center>'.$nro.'</center></td>';
            $tabla .= '<td><center><a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-success enlace" name="'.$row['proy_id'].'" id="'.strtoupper($row['proy_nombre']).')">VER POA</a></center></td>';
            $tabla .= '<td>';
              if($row['aper_proy_estado']==4){
                 $tabla.='<center><a href="javascript:abreVentana_poa(\''.site_url("").'/prog/reporte_form4_consolidado/'.$row['proy_id'].'\');" title="REPORTE POA" class="btn btn-default"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="30" HEIGHT="30"/></a></center>';
              }
            $tabla.='</td>';
            $tabla .= '<td>';
              if($this->adm==1){ 
                $tabla.='<center><a href="#" data-toggle="modal" data-target="#modal_neg_ff" class="btn btn-default neg_ff" title="OBSERVAR PROYECTO"  name="'.$row['proy_id'].'" ><img src="'.base_url().'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></center>';
              }
            $tabla .= '</td>';
            $tabla .= '<td><center>'.$row['aper_programa'].''.$row['proy_sisin'].''.$row['aper_actividad'].'</center></td>';
            $tabla .= '</td>';
            $tabla.='<td>'.$row['proy_nombre'].'</td>';
            $tabla.='<td>'.$row['proy_sisin'].'</td>';
            $tabla.='<td>'.$row['dep_cod'].' '.strtoupper($row['dep_departamento']).'</td>';
            $tabla.='<td>'.$row['dist_cod'].' '.strtoupper($row['dist_distrital']).'</td>';
            $tabla .='<td title='.$row['pfec_id'].'>'.strtoupper($row['pfec_descripcion']).'</td>';
            if($row['pfec_estado']==0){
              $tabla.='<td bgcolor="#f5d2d2">FASE NO ACTIVA PARA LA GESTI&Oacute;N '.$this->gestion.'</td>';
            }
            else{
              $tabla.='<td >FASE ACTIVA PARA LA GESTI&Oacute;N '.$this->gestion.'</td>';
            }        
          $tabla.='</tr>';
        }
      }
      
      return $tabla;
    }


    /*-------- GET DATOS POA --------*/
    public function get_poa(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id); /// PROYECTO

        $caratula_poa='';
        $titulo_poa=$proyecto[0]['aper_programa'].' '.$proyecto[0]['proy_sisin'].' '.$proyecto[0]['proy_nombre'];
        if($proyecto[0]['tp_id']==4){
          $titulo_poa=$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' '.$proyecto[0]['abrev'];
          $caratula_poa='
          <a href="javascript:abreVentana_poa(\''.site_url("").'/proy/presentacion/'.$proy_id.'\');" title="CARATULA POA"  class="btn btn-default"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="45" HEIGHT="45"/><br>CARATULA POA</a>';  
        }

        $tabla=$this->programacionpoa->mi_poa($proy_id); /// Mis Unidades Responsables
        
        $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$tabla,
          'proyecto'=>$proyecto,
          'titulo_poa'=>$titulo_poa,
          'caratula'=>$caratula_poa,
        );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

   

    /*-------- GET AJUSTE DATOS POA --------*/
    public function get_poa_ajuste(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO

        $tabla=$this->programacionpoa->mi_poa_ajuste($proy_id);
        $result = array(
            'respuesta' => 'correcto',
            'tabla'=>$tabla,
          );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }



    /*-------- GET VERIF POA para su APROBACION --------*/
    public function verif_poa(){
        if($this->input->is_ajax_request() && $this->input->post()){
            
            $post = $this->input->post();
            $proy_id = intval($this->security->xss_clean($post['proy_id']));

            if ($proy_id <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error'));
                exit;
            }

            $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id); 
            
            // 🌟 REPARADO CORE 2: Declaración inicial explícita de la variable contra quiebres de tipos
            $tabla = ''; 
            $nro_dif = 1;

            if (!empty($proyecto)) {
                $partidas = $this->model_ptto_sigep->vista_get_lista_ppto_partidas_UOrganizacional(intval($proyecto[0]['aper_id'])); 
                
                $membrete_unidad = '<div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #334155; padding: 10px 14px; margin-bottom: 15px; border-radius: 3px; text-align: left;">
                                        <span style="display:block; font-size:10px; font-weight:bold; color:#64748b; text-transform:uppercase; letter-spacing:0.3px;">Establecimiento / Unidad Evaluada:</span>
                                        <strong style="color:#0f172a; font-size:12px; font-family:Arial, sans-serif; display:block; margin-top:2px;">'.htmlspecialchars(strtoupper($proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev']), ENT_QUOTES, 'UTF-8').'</strong>
                                    </div>';

                if(count($partidas) != 0){
                    $ppto_variacion = $this->model_ptto_sigep->verif_variacion_ppto_x_UnidadOrganizacional(intval($proyecto[0]['aper_id']));
                    
                    // Evaluamos de manera matemática que la subconsulta agrupada RAM de PostgreSQL retorne cero variaciones
                    if(intval($ppto_variacion[0]['total_variaciones']) === 0){
                        $tabla .= $membrete_unidad . '
                        <div class="alert alert-success text-center" style="border-left: 4px solid #16a34a; background-color: #f0fdf4; color: #15803d; padding: 15px; border-radius: 4px;">
                            <i class="fa fa-check-circle fa-2x" style="margin-bottom:8px; display:block;"></i>
                            <span style="font-family: Arial, sans-serif; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; display:block;">Balanza Cuadrada al Centavo</span>
                            <span style="font-family: Arial, sans-serif; font-size: 11.5px; font-weight: 500; display:block; margin-top:3px; color: #166534;">El presupuesto asignado  coincide con la programación física del POA ' . $this->gestion . '. Listo para ser validado.</span>
                        </div>';
                        $nro_dif = 0; // Liberador testigo para el slideDown del botón
                    }
                    else{
                        $tabla .= $membrete_unidad . '
                        <div class="alert alert-danger text-center" style="border-left: 4px solid #dc2626; background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 4px;">
                            <i class="fa fa-times-circle fa-2x" style="margin-bottom:8px; display:block;"></i>
                            <span style="font-family: Arial, sans-serif; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; display:block;">Variación Presupuestaria Detectada</span>
                            <span style="font-family: Arial, sans-serif; font-size: 11.5px; font-weight: 500; display:block; margin-top:3px; color: #991b1b;">Operación Denegada: Existen partidas contables donde el techo asignado difiere de los insumos formulados. Corrija las celdas desalineadas en la grilla del Formulario N° 5.</span>
                        </div>';
                    }
                }
                else{
                    $tabla .= $membrete_unidad . '
                    <div class="alert alert-danger text-center" style="border-left: 4px solid #b91c1c; background-color: #fef2f2; color: #991b1b; padding: 15px; border-radius: 4px;">
                        <i class="fa fa-exclamation-triangle fa-2x" style="margin-bottom:8px; display:block;"></i>
                        <span style="font-family: Arial, sans-serif; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; display:block;">Techo Inexistente</span>
                        <span style="font-family: Arial, sans-serif; font-size: 11.5px; font-weight: 500; display:block; margin-top:3px; color: #991b1b;">Esta Unidad Organizacional no registra un techo presupuestario asignado por el Ministerio para la presente gestión fiscal ' . $this->gestion . '.</span>
                    </div>';
                }
            }

            // Saneamiento de buffers: barremos remanentes para garantizar salida JSON pura libre de codificaciones <
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(array(
                'status'    => 'success',
                'respuesta' => 'correcto',
                'tabla'     => $tabla,
                'proyecto'  => $proyecto,
                'valor'     => $nro_dif,
            ));
            exit; // Detiene el hilo de ejecución resguardando la integridad del vector JSON
        } else {
            show_404();
        }
    }


    /*public function verif_poa(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id); /// Unidad Organizacional
        $partidas=$this->model_ptto_sigep->vista_get_lista_ppto_partidas_UOrganizacional($proyecto[0]['aper_id']); /// Partidas
        $nro_dif=1;
        if(count($partidas)!=0){
            $ppto_variacion=$this->model_ptto_sigep->verif_variacion_ppto_x_UnidadOrganizacional($proyecto[0]['aper_id']);
            if($ppto_variacion[0]['total_variaciones']==0){
                $tabla.='
              <hr><h3><b>&nbsp;&nbsp;'.$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev'].'</b></h3><hr>
              <div class="alert alert-success alert-block" align=center>
                <h2> POA-PRESUPUESTO '.$this->gestion.' LISTO PARA SER VALIDADO</2> 
              </div>';
              $nro_dif=0;
            }
            else{
                $tabla.='
                <hr><h3><b>&nbsp;&nbsp;'.$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev'].'</b></h3><hr>
                <div class="alert alert-danger alert-block" align=center>
                    <h2>POR CORREGIR PRESUPUESTO POA '.$this->gestion.'</h2>
                </div>';
            }
        }
        else{
            $tabla.='
              <hr><h3><b>&nbsp;&nbsp;'.$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' - '.$proyecto[0]['abrev'].'</b></h3><hr>
              <div class="alert alert-danger alert-block" align=center>
                  <h2>SIN PRESUPUESTO ASIGNADO '.$this->gestion.'</h2>
              </div>';
        }
        
        $result = array(
            'respuesta' => 'correcto',
            'tabla'=>$tabla,
            'proyecto'=>$proyecto,
            'valor'=>$nro_dif,
          );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }*/

    /*------ CUADRO COMPARATIVO PTTO. ASIG - PROG (Para aprobar)------*/
/*    public function verif_cuadro_comparativo($proyecto){
      $partidas_asig=$this->model_ptto_sigep->partidas_accion_region($proyecto[0]['dep_id'],$proyecto[0]['aper_id'],1); // Asig
      $partidas_prog=$this->model_ptto_sigep->partidas_accion_region($proyecto[0]['dep_id'],$proyecto[0]['aper_id'],2); // Prog

      $nro_dif=0;
      foreach($partidas_asig as $row){
        $part=$this->model_ptto_sigep->get_partida_programado_poa($proyecto[0]['aper_id'],$row['par_id']);
        $prog=0;
        
        if(count($part)!=0){
          $prog=$part[0]['ppto_programado'];
        }

        $dif=($row['ppto_asignado']-$prog);
        
        if($dif!=0){
          $nro_dif++;
          break;
        }
      }

      foreach($partidas_prog as $row){
        $part=$this->model_ptto_sigep->get_partida_asignado_sigep($proyecto[0]['aper_id'],$row['par_id']);
         if(count($part)==0){ 
          $asig=0;
          if(count($part)!=0){
            $asig=$part[0]['ppto_asignado'];
          }
          $dif=($asig-$row['monto']);

          if($dif!=0){
            $nro_dif++;
            break;
          }

        }  
      }

      return $nro_dif;
    }*/


   






  /*----- FORMULARIO DE REGISTRO DE UNIDADES/ESTABLECIMIENTOS (2020) -----*/
  function form_poa_unidades(){
    $data['menu']=$this->programacionpoa->menu(2);
    $data['res_dep']=$this->programacionpoa->tp_resp();
    if($this->tp_adm==1){
      $data['form']=$this->programacionpoa->formulacion_add_poa_adm();
    }
    else{
      $data['form']=$this->programacionpoa->formulacion_add_poa();
    }

    $this->load->view('admin/programacion/prog_operaciones/formularios/form_add_of', $data);
  }


  /*--- FORMULARIO DE MODIFICACIÓN DE UNIDADES/ESTABLECIMIENTOS (2020) ---*/
  function form_update_poa_unidades($proy_id){
    $data['menu']=$this->programacionpoa->menu(2);
    $data['res_dep']=$this->programacionpoa->tp_resp();
    $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
    if(count($data['proyecto'])!=0){
      $data['form']=$this->programacionpoa->formulacion_update_poa($data['proyecto']);

      $this->load->view('admin/programacion/prog_operaciones/formularios/form_edit_of', $data);
    }
    else{
        $this->session->set_flashdata('danger','ERROR AL REGISTRAR');
        redirect('admin/proy/list_proy'); ///// Lista de Unidades/ Establecimientos
    }

  }



    /*--- ACTIVAR, DESACTIVAR OBJETIVO REGIONAL -----*/
    function estado_oregional(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('id', 'id unidad', 'required|trim'); // por_id 
          $this->form_validation->set_rules('estado', 'estado', 'required|trim'); // Activo/Desactivo
          
          $post = $this->input->post();
          $id= $this->security->xss_clean($post['id']); 
          $estado_activo = $this->security->xss_clean($post['estado']);
          $proy_id = $this->security->xss_clean($post['proy_id']);
         
          if($estado_activo==1){ /// Activar unidad a la gestion
              $data_to_store3 = array(
                'proy_id' => $proy_id,
                'por_id' => $id,
              );
              $this->db->insert('proy_oregional', $data_to_store3);
          }

          else{ /// Desactivar unidad a la gestion
            $this->db->where('por_id', $id);
            $this->db->delete('proy_oregional');
          }
    
      }else{
          show_404();
      }
    }

    /*--- ACTIVAR, DESACTIVAR SERVICIOS / COMPONENTES -----*/
    function estado_servicios(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $this->form_validation->set_rules('id', 'id unidad', 'required|trim'); // serv_id 
          $this->form_validation->set_rules('estado', 'estado', 'required|trim'); // Activo/Desactivo
          
          $post = $this->input->post();
          $id= $this->security->xss_clean($post['id']); 
          $estado_activo = $this->security->xss_clean($post['estado']);
          $pfec_id = $this->security->xss_clean($post['pfec_id']);
          $servicio=$this->model_estructura_org->get_servicio_actividad_id($id);

          if($estado_activo==1){ /// Activar Servicio
              $data_to_store = array( 
                'pfec_id' => $pfec_id,
                'com_componente' => $servicio[0]['serv_descripcion'],
                'resp_id' => $this->fun_id,
                'serv_id' => $id,
                'fun_id' => $this->fun_id,
                );
              $this->db->insert('_componentes', $data_to_store);
          }
          else{ /// Desactivar Servicio
            $this->db->where('serv_id', $id);
            $this->db->where('pfec_id', $pfec_id);
            $this->db->delete('_componentes');
          }
    
      }else{
          show_404();
      }
    }



    /*-- SELECCIONAR Y DESECCIONALES ALINEACION A OPERACIONES 2022 --*/
    public function deseleccion_seleccion_alineacion(){
      if($this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $tp = $this->security->xss_clean($post['tp']);
        $proyecto = $this->model_proyecto->get_UnidadOrganizacional($proy_id); /// PROYECTO
        $oregionales=$this->model_objetivoregion->get_unidad_pregional_programado($proyecto[0]['act_id']); /// Objetivos Regionales

          if($tp==0){ /// Deselecciona
            $this->db->where('proy_id', $proy_id);
            $this->db->delete('proy_oregional');
          }
          else{ /// Selecciona

            $this->db->where('proy_id', $proy_id);
            $this->db->delete('proy_oregional');
            
            foreach($oregionales as $row){
              $data_to_store3 = array(
                'proy_id' => $proy_id,
                'por_id' => $row['por_id'],
              );
              $this->db->insert('proy_oregional', $data_to_store3);
            }
          }

        $result = array(
          'respuesta' => 'correcto',
        );

      //  echo json_encode($result);
      }else{
          show_404();
      }
    }


    /*---------  DIRECCION ADMINISTRATIVA --------------*/
    public function combo_da($accion=''){ 
      $salida="";
      $accion=$_POST["accion"];
      switch ($accion) {
        case 'distrital':
        $salida="";
          $id_pais=$_POST["elegido"];
          
          $combog = pg_query('SELECT *
          from _distritales 
          where  dep_id='.$id_pais.' and dist_adm=1');
          while($sql_p = pg_fetch_row($combog))
          {$salida.= "<option value='".$sql_p[0]."'>".$sql_p[5]." - ".$sql_p[2]."</option>";}

        echo $salida; 
        //return $salida;
        break;
      }
    }

    /*---COMBO DE UNIDADES / ESTABLECIMIENTOS SEGUN SU REGIONAL (2020)---*/
    public function como_unidad(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $reg_id = $this->security->xss_clean($post['reg_id']);
        
        $tabla=$this->list_unidades_de_regional($reg_id);
        $result = array(
          'respuesta' => 'correcto',
          'unidades' => $tabla,
        );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*--- LISTA DE UNIDADES SEGUN LA REGIONAL ----*/
    public function list_unidades_de_regional($dep_id){
      $tabla='';
      $unidades=$this->model_estructura_org->list_unidades_de_regional($dep_id);
      $tabla.='<option value="">SELECCIONE UNIDAD / ESTABLECIMIENTO</option>';
      foreach($unidades as $row){
        if(count($this->model_proyecto->get_uni_apertura_programatica($row['act_id']))==0){
          $tabla.='<option value='.$row['act_id'].'>'.$row['act_cod'].'.- '.$row['tipo'].' '.$row['act_descripcion'].' - '.$row['abrev'].'</option>';
        }
      }
      return $tabla;
    }
    

    /*---------- VALIDA POA DATOS (2020) ---------*/
    public function valida_poa_unidades(){
      if($this->input->post()) {
        $post = $this->input->post();
        $act_id = $this->security->xss_clean($post['act_id']); /// unidad id
        $cod_act = $this->security->xss_clean($post['act']); /// codigo actividad
        $actividad= $this->model_estructura_org->get_actividad($act_id); /// get actividad
        
          /*--- Insert a la tabla proyectos ---*/
          $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
            'proy_codigo' => $actividad[0]['act_cod'],
            'proy_nombre' => strtoupper($actividad[0]['act_descripcion']),
            'tp_id' => 4,
            'proy_gestion_inicio_ddmmaaaa' => '01/01/'.$this->gestion.'',
            'proy_gestion_fin_ddmmaaaa' => '31/12/'.$this->gestion.'',
            'dep_id' => $actividad[0]['dep_id'],
            'dist_id' => $actividad[0]['dist_id'],
            'proy_fecha_registro' => date("d/m/Y H:i:s"),
            'fun_id' => $this->fun_id,
            'act_id' => $act_id,
            'num_ip' => $this->input->ip_address(), 
            'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR']),
          );
          $this->db->insert('_proyectos', $data_to_store);
          $proy_id=$this->db->insert_id();
          /*---------------------------------*/
          /*--------- RESPONSABLES DE LA OPERACION --------*/
          $this->model_proyecto->add_resp_proy($proy_id,$this->fun_id,$this->fun_id,0,0,0);
          
          /*--------- FECHAS INICIAL-FINAL : OPERACION ----------*/
          $fechas = $this->model_proyecto->fechas_proyecto($proy_id);  // devuelve las fechas del proyecto inicio-conclusion

          /*--------- UPDATE DATOS OPERACION ------------*/
          $update_proyect = array(
            'proy_gestion_inicio' => $fechas[0]['inicio'],
            'proy_gestion_fin' => $fechas[0]['final'],
            'proy_gestion_impacto' => ($fechas[0]['final']-$fechas[0]['inicio'])+1
          );
          $this->db->where('proy_id', $proy_id);
          $this->db->update('_proyectos', $update_proyect);

          /*--------- ADD APERTURA PROGRAMATICA ---------*/
          $this->model_proyecto->add_apertura($proy_id,$actividad[0]['aper_gestion'],$actividad[0]['aper_programa'],$actividad[0]['aper_proyecto'],$cod_act,$actividad[0]['act_descripcion'],$this->fun_id);
          $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);

           /*--------- INSERT FASE ETAPA COMPONENTE --------*/ 
           $query=$this->db->query('set datestyle to DMY');
           $data_to_store3 = array(
            'proy_id' => $proy_id,
            'pfec_fecha_inicio_ddmmaaa' => '01/01/'.$this->gestion.'',
            'pfec_fecha_fin_ddmmaaa' => '31/12/'.$this->gestion.'',
            'pfec_fecha_registro' => date('d/m/Y h:i:s'),
            'pfec_fecha_inicio' => $fechas[0]['inicio'],
            'pfec_fecha_fin' => $fechas[0]['final'],
            'pfec_ptto_fase' => 0,
            'pfec_ejecucion' => 1,
            'fun_id' => $this->fun_id,
            'aper_id' => $proyecto[0]['aper_id'],
            );
            $this->db->insert('_proyectofaseetapacomponente', $data_to_store3);
            $pfec_id=$this->db->insert_id();

        $nro=0;
        if (!empty($_POST["por_id"]) && is_array($_POST["por_id"]) ) {
          foreach ( array_keys($_POST["por_id"]) as $como){

            $data_to_store3 = array(
            'proy_id' => $proy_id,
            'por_id' => $_POST["por_id"][$como],
            );
            $this->db->insert('proy_oregional', $data_to_store3);
            $nro++;
          }
        }

        $nro_serv=0;
        if (!empty($_POST["serv"]) && is_array($_POST["serv"]) ) {
          foreach ( array_keys($_POST["serv"]) as $como){
            $servicio=$this->model_estructura_org->get_servicio_actividad_id($_POST["serv"][$como]);
            $veri_cs=$this->model_proyecto->verif_componente_servicio($pfec_id,$_POST["serv"][$como]);
            if(count($veri_cs)==0){

              $data_to_store = array( 
                'pfec_id' => $pfec_id,
                'com_componente' => $servicio[0]['serv_descripcion'],
                //'resp_id' => $_POST["resp_id"][$como],
                'resp_id' => $this->fun_id,
                'serv_id' => $_POST["serv"][$como],
                'fun_id' => $this->fun_id,
                );
              $this->db->insert('_componentes', $data_to_store);
            }
            else{

              $update_com = array(
                'com_componente' => $servicio[0]['serv_descripcion'],
                'resp_id' => $this->fun_id,
                'serv_id' => $_POST["serv"][$como],
                'fun_id' => $this->fun_id
              );
              $this->db->where('com_id', $veri_cs[0]['com_id']);
              $this->db->update('_componentes', $update_com);
            }
            $nro_serv++;
          }
         
        }

        redirect('proy/update_unidad/'.$proy_id.''); ///// Formulario de registro-operaciones
      }
    }

   
    /*-- GET ACTIVIDAD UNIDAD - ESTABLECIMIENTO (2020) --*/
    public function get_actividad(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $act_id = $this->security->xss_clean($post['act_id']);
        $actividad= $this->model_estructura_org->get_actividad($act_id);

        $tabla=$this->list_objetivos_regionales($act_id);
        
        if(count($actividad)!=0){
          $servicios=$this->list_servicios_habilitados($actividad[0]['te_id']);
          $result = array(
            'respuesta' => 'correcto',
            'actividad' => $actividad,
            'oregional' => $tabla,
            'servicios' => $servicios,
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


    /*--- LISTA DE OBJETIVOS REGIONALES VICNULADOS A UNA UNIDAD (2020)*/
    public function list_objetivos_regionales($act_id){
      $oregionales=$this->model_objetivoregion->get_unidad_pregional_programado($act_id);
      $tabla='';
      $tabla.='
      <table class="table table-bordered">
        <thead>
          <tr title="act '.$act_id.'">
            <th scope="col">#</th>
            <th scope="col">OBJETIVO REGIONAL '.$this->gestion.'</th>
            <th scope="col">OBJETIVO DE GESTI&Oacute;N '.$this->gestion.'</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>';
        $cont = 0;
        foreach($oregionales as $row){
          $tit='RELACIÓN INDIRECTA';
          $color='#f9eeee';
          if($row['or_estado']!=0){
            $color='#e2f9f6';
            $tit='RELACIÓN DIRECTA';
          }
          $cont++;
          $tabla.='
          <tr bgcolor='.$color.'>
            <td>'.$cont.'</td>
            <td><b style="font-size: 10pt;">'.$row['or_codigo'].'.-</b> '.$row['or_objetivo'].'</td>
            <td>'.$row['og_codigo'].'.- '.$row['og_objetivo'].'</td>
            <td><center><input type="checkbox" name="por_id[]" value="'.$row['por_id'].'" title="SELECCIONE OBJETIVO REGIONAL" checked/></center></td>
          </tr>';
        }
        $tabla.='
        </tbody>
      </table>';
      return $tabla;
    }

    /*--- LISTA DE SERVICIOS (2020)*/
    public function list_servicios_habilitados($te_id){
      $servicios=$this->model_estructura_org->list_establecimiento_servicio($te_id);
      $tabla='';
      $tabla.='
      <table class="table table-bordered">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">C&Oacute;DIGO</th>
            <th scope="col">SERVICIO '.$this->gestion.'</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody>';
        $cont = 0;
        foreach($servicios as $row){
          $cont++;
          $tabla.='
          <tr>
            <td>'.$cont.'</td>
            <td>'.$row['serv_cod'].'</td>
            <td>'.$row['serv_descripcion'].'</td>
            <td><center><input type="checkbox" name="serv[]" value="'.$row['serv_id'].'" title="SELECCIONE SERVICIO"/></center></td>
          </tr>';
        }
        $tabla.='
        </tbody>
      </table>';
      return $tabla;
    }

  /*----- FORMULARIO DE REGISTRO PROYECTOS DE INVERSIÓN -----*/
  function form_proy_inv(){
    $data['menu']=$this->programacionpoa->menu(2);
    $cod=count($this->model_proyecto->cod_proy());
    $data['codigo']=$cod[0]['proy_codigo']+1;
    $data['tp_proy']=$this->model_proyecto->tip_proy();
    $data['tp_gasto']=$this->model_proyecto->tip_gasto();
    $data['list_dep']=$this->model_proyecto->list_departamentos();
    $data['programas'] = $this->model_proyecto->list_prog($this->gestion); ///// lista aperturas padres

    $data['unidad']=$this->model_proyecto->unidades_ejecu(); ////// Unidad Ejecutora
    $data['unidad2']=$this->model_proyecto->list_unidad_org(); ////// Unidad responsables

    $data['titulo']='';
    $data['tp_id']=1;

    $this->load->view('admin/programacion/prog_operaciones/formularios/form_add_pi', $data);
    
  }



  /*----- EDIT DATOS UNIDAD, ESTABLECIMIENTO  -----*/
  public  function edit_operacion($proy_id){
    $data['menu']=$this->programacionpoa->menu(2);
    $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id);
    if(count($data['proyecto'])!=0){
      $data['nro_f'] = $this->model_faseetapa->nro_fase($proy_id);
      $data['fase'] = $this->model_faseetapa->get_id_fase($proy_id); ///// datos fase encendida

      $data['tp_proy']=$this->model_proyecto->tip_proy();
      //$data['tp_gasto']=$this->model_proyecto->tip_gasto();
      $data['oregional_prog']=$this->model_objetivoregion->get_pregional_programado($data['proyecto'][0]['por_id']); /// get objetivo regional programado 
      $data['list_dep']=$this->model_proyecto->list_departamentos();
      $data['list_dist']=$this->model_estructura_org->list_unidades_adm_ue(1,$data['proyecto'][0]['dep_id']); /// Unidades Ejecutoras
      $data['unidad_ejec'] = $this->model_estructura_org->list_unidades_adm_ue(2,$data['proyecto'][0]['dep_id']); /// Unidades Ejecutoras
      $data['list_actividades'] = $this->model_estructura_org->list_actividades_institucionales($data['proyecto'][0]['dist_id']); /// Actividades Institucionales
    //  $data['actividad'] = $this->model_estructura_org->get_actividad($data['proyecto'][0]['act_id']);
      
      $data['programas'] = $this->model_proyecto->list_prog($this->gestion); ///// lista aperturas padres
      //$data['prog_padre']=$this->model_proyecto->get_programa_padre($data['proyecto'][0]['aper_programa']);
      /*--- Responsables Asignados a la operacion ---*/
      $data['resp1']=$this->model_proyecto->responsable_proy($proy_id,1);
      $data['resp2']=$this->model_proyecto->responsable_proy($proy_id,2);

      /*--- Responsables ---*/
      $data['fun1']=$this->model_proyecto->list_responsables_regionales(3,$data['proyecto'][0]['dep_id']); ////// tecnico OPERATIVO
      $data['fun2']=$this->model_proyecto->asig_responsables_vpoa($data['proyecto'][0]['tp_id']); ////// validador POA

      if($data['proyecto'][0]['tp_id']==1){
        $this->load->view('admin/programacion/prog_operaciones/formularios/form_update_pi', $data);
      }
      else{
        $data['actividad'] = $this->model_estructura_org->get_actividad($data['proyecto'][0]['act_id']);
        $data['servicios']=$this->list_sub_actividades($proy_id,$data['actividad']);
        $this->load->view('admin/programacion/prog_operaciones/formularios/form_update_of', $data);
      }
      
    }
    else{
      redirect('/','refresh');
    }
     
  }


  /*----- CARATULA UNIDAD ORGANIZACIONAL POA (2023) -----*/
  public  function presentacion_poa($proy_id){
    $data['menu']=$this->programacionpoa->menu(2);
    $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
    if(count($data['proyecto'])!=0){
      $data['mes'] = $this->mes_nombre();
      $data['cuerpo']=$this->programacionpoa->caratula_poa_gacorriente($data['proyecto']);
      $this->load->view('admin/programacion/reportes/presentacion_poa', $data);
    }
    else{
      echo "ERROR !!!!";
    }
  }



  /*----- DATOS GENERALES POA (2020) -----*/
  public  function datos_generales_pi($proy_id){
    $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO
    if(count($data['proyecto'])!=0){
      $data['mes'] = $this->mes_nombre();
      $data['fase']=$this->model_faseetapa->get_id_fase($proy_id);
      $this->load->view('admin/programacion/reportes/reporte_datos_generales', $data);
    }
    else{
      echo "ERROR !!!!";
    }
  }


/*------ NOMBRE MES -------*/
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

  /*================= PROYECTO DE INVERSION (2019) ===================*/
  /*---------- FORMULARIO RESUMEN TECNICO DEL PROYECTO ----------*/
  public  function form_operacion_resumen($proy_id){
    $data['menu']=$this->programacionpoa->menu(2);
    $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id);
    if(count($data['proyecto'])!=0){

      $this->load->view('admin/programacion/prog_operaciones/formularios/form_add_pi2', $data);
    }
    else{
      redirect('/','refresh');
    }
     
  }
    /*---------- VALIDA PROYECTO DE INVERSION ---------*/
    function valida_proyecto(){
    if ($this->input->post()) {
        $post = $this->input->post();
        $form = $this->security->xss_clean($post['form']); /// Formulario

        if($form==1){
          $tipo = $this->security->xss_clean($post['tp_id']); /// Tipo de Operacion
          $nombre = $this->security->xss_clean($post['nombre']); /// Nombre 
          $fi = $this->security->xss_clean(date("d/m/Y", strtotime($post['ini']))); /// Fecha Inicio
          $ff = $this->security->xss_clean(date("d/m/Y", strtotime($post['fin']))); /// Fecha Final
          $proy_act = 1; /// Programacion hasta actividades
          $dep_id = $this->security->xss_clean($post['dep_id']); /// Departamento id
          $dist_id = $this->security->xss_clean($post['ue_id']); /// Distrito id
          $cod_sisin = $this->security->xss_clean($post['cod_sisin']); /// Codigo SISIN
          $ue = 0; /// UE
          $ur = 0; /// RESP
          $prog = $this->security->xss_clean($post['prog']); /// PROGRAMA
          $proy= $this->security->xss_clean($post['proy']); /// PROYECTO
          $act = '000'; /// ACTIVIDAD
          $aper=$this->model_proyecto->get_aper_programa($prog);

          //echo "tipo : ".$tipo." - NOMNRE : ".$nombre." - FECHA : ".$fi."-".$ff." - DEP ID : ".$dep_id."";

          /*------------ Insert Proyectos --------------*/
          $query=$this->db->query('set datestyle to DMY');
          $data_to_store = array( 
            'proy_codigo' => 0,
            'proy_nombre' => strtoupper($nombre),
            'tp_id' => $tipo,
            'proy_gestion_inicio_ddmmaaaa' => $fi,
            'proy_gestion_fin_ddmmaaaa' => $ff,
            'proy_act' => $proy_act,
            'dep_id' => $dep_id,
            'dist_id' => $dist_id,
            'proy_fecha_registro' => date("d/m/Y H:i:s"),
            'fun_id' => $this->fun_id,
            'act_id' => 0,
          );
          $this->db->insert('_proyectos', $data_to_store);
          $id_p=$this->db->insert_id();
          /*-------------------------------------------*/
          /*----------- RESPONSABLES DEL PROYECTO ----------*/
          $this->model_proyecto->add_resp_proy($id_p,583,583,583,$ue,$ur);
          /*-----------------------------------------------*/
          /*--------- FECHAS INICIAL-FINAL : PROYECTO ----------*/
          $fechas = $this->model_proyecto->fechas_proyecto($id_p);

          /*--------- UPDATE DATOS OPERACION ------------*/
          $update_proyect = array(
              'proy_gestion_inicio' => $fechas[0]['inicio'],
              'proy_gestion_fin' => $fechas[0]['final'],
              'proy_gestion_impacto' => ($fechas[0]['final']-$fechas[0]['inicio'])+1
          );
          $this->db->where('proy_id', $id_p);
          $this->db->update('_proyectos', $update_proyect);
          /*---------------------------------------------*/

          $gestiones=$fechas[0]['final']-$fechas[0]['inicio'];
          $gestion=$fechas[0]['inicio'];

          /*------------- APERTURA PROGRAMATICA -------------*/
          for($i=0;$i<=$gestiones;$i++){
            if($this->gestion==$gestion){
              $this->model_proyecto->add_apertura($id_p,$gestion,$aper[0]['aper_programa'],$proy,$act,$actividad[0]['act_descripcion'],$this->fun_id);
            }
            else{
              $this->model_proyecto->add_apertura($id_p,$gestion,$aper[0]['aper_programa'],'','',$actividad[0]['act_descripcion'],$this->fun_id);
            }
            $gestion++;
          }
          /*--------------------------------------------------*/
            $proyecto = $this->model_proyecto->get_id_proyecto($id_p);
            if(count($proyecto)!=0){
              $this->session->set_flashdata('success','LOS DATOS DEL PROYECTO SE REGISTRARON CORRECTAMENTE');
              redirect('admin/proy/proyecto_pi/'.$id_p.''); ///// Formulario de REsumen Tecnico
            }
            else{
              $this->session->set_flashdata('danger','ERROR AL REGISTRAR DATOS GENERALES DEL PROYECTOS');
              redirect('admin/proy/proyecto/'.$tipo.'/false'); ///// Formulario de registro-operaciones
            }
        }
        /*---------- Objetivos - Problemas --------*/
        else{
          $proy_id = $this->security->xss_clean($post['proy_id']); /// proyecto Id
          $desc_prob = $this->security->xss_clean($post['desc_prob']); /// Descripcion del problema
          $desc_sol = $this->security->xss_clean($post['desc_sol']); /// Descripcion de la solucion
          $obj_gral = $this->security->xss_clean($post['obj_gral']); /// Descripcion Objetivo General
          $obj_esp = $this->security->xss_clean($post['obj_esp']); /// Descripcion Objetivo Especifico

          /*--------- UPDATE DATOS OPERACION ------------*/
          $update_proy = array(
            'proy_desc_problema' => $desc_prob,
            'proy_desc_solucion' => $desc_sol,
            'proy_obj_general' => $obj_gral,
            'proy_obj_especifico' => $obj_esp,
            'estado' => 2,
            'proy_fecha_registro' => date("d/m/Y H:i:s"),
            'fun_id' => $this->fun_id
          );
          $this->db->where('proy_id', $proy_id);
          $this->db->update('_proyectos', $update_proy);
          /*---------------------------------------------*/

            $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);
            if($proyecto[0]['estado']==2){
              $this->session->set_flashdata('success','EL PROYECTO DE INVERSI&Oacute;N SE REGISTRO CORRECTAMENTE');
              redirect('admin/proy/fase_etapa/'.$proy_id.'/true'); ///// Formulario Problemas y Objetivos
            //  redirect('admin/proy/list_proy#tabs-a'); ///// Lista de Proyectos de Inversion
            }
            else{
              $this->session->set_flashdata('danger','ERROR AL REGISTRAR PROBLEMAS - OBJETIVOS');
              redirect('admin/proy/proyecto_pi/'.$proy_id.'/false'); ///// Formulario Problemas y Objetivos
            }
        }

    }
    else{
      echo "<center><font color='red'>Error!!!!</font></center>";
    }
  }

  /*--- VALIDA UPDATE PROYECTO DE INVERSION ---*/
  function valida_update_proyecto(){
    if ($this->input->post() & $this->input->server('REQUEST_METHOD') === 'POST') {
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']); /// Proyecto Id
        $form = $this->security->xss_clean($post['form']); /// Formulario

        if($form==1){
          $fi = $this->security->xss_clean($post['ini']); /// Fecha Inicio
          $ff = $this->security->xss_clean($post['final']); /// Fecha Final

          $dep_id = $this->security->xss_clean($post['dep_id']); /// Departamento id
          $dist_id = $this->security->xss_clean($post['ue_id']); /// Distrito id

          $ppto = $this->security->xss_clean($post['ppto_proy']); /// presupuesto total

          $nombre = $this->security->xss_clean($post['nombre']); /// Nombre del Proyecto
          $cod_sisin = $this->security->xss_clean($post['cod_sisin']); /// Codigo SISIN

          $tue = 583; /// Tue
          $poa = 583; /// POA
          $fin = 583; /// FIn

          $ue = 0; /// UE
          $ur = 0; /// RESP

          $gi = $this->security->xss_clean($post['gi']); /// Gestion Inicio
          $gf = $this->security->xss_clean($post['gf']); /// Gestion Final

          $aper_prog = $this->security->xss_clean($post['aper_id']); /// Aper Id anterior
          $aper=$this->model_proyecto->get_aper_programa($post['prog']); /// Aper Id Nuevo actual

          $ap_proy = $this->security->xss_clean($post['aper_proy']); /// cod proyecto anterior
          $proy=$this->security->xss_clean($post['proy']); /// cod proyecto nuevo

          $act='000'; /// cod proyecto nuevo

          $fechas = $this->model_proyecto->fechas_proyecto($proy_id);  // devuelve las fechas del proyecto inicio-conclusion
          
        //  $aper=$this->model_proyecto->get_aper_programa($prog); /// Aper Id Nuevo
        //  echo "PROGRAMA ANTERIOR : ".$aper_prog." - PROGRAMA NUEVO : ".$prog[0]['aper_id']."";
          /*------- update apertura programatica ------*/
          if($aper_prog!=$aper[0]['aper_programa'] || $ap_proy!=$proy){
            $aper_proy=$this->model_proyecto->mis_programas($proy_id);
              foreach ($aper_proy as $rowa){
                $this->model_proyecto->update_apertura($rowa['aper_id'],$aper[0]['aper_programa'],$proy,$act,$nombre,$this->fun_id);
              }
          }

          $query=$this->db->query('set datestyle to DMY');
          $update_proy = array(
            'proy_nombre' => $nombre,
            'proy_sisin' => $cod_sisin,
            'proy_gestion_inicio_ddmmaaaa' => $fi,
            'proy_gestion_fin_ddmmaaaa' => $ff,
            'dep_id' => $dep_id,
            'dist_id' => $dist_id,
            'proy_ppto_total' => $ppto,
            'proy_fecha_registro' => date('d/m/Y h:i:s'),
            'fun_id' => $this->fun_id,
            'estado' => 2
          );
          $this->db->where('proy_id', $proy_id);
          $this->db->update('_proyectos', $update_proy);

          $fechas = $this->model_proyecto->fechas_proyecto($proy_id);  // devuelve las fechas del proyecto inicio-conclusion
          $query=$this->db->query('set datestyle to DMY');
          $update_proyect = array(
            'proy_gestion_inicio' => $fechas[0]['inicio'],
            'proy_gestion_fin' => $fechas[0]['final'],
            'proy_gestion_impacto' => ($fechas[0]['final']-$fechas[0]['inicio'])+1);
          $this->db->where('proy_id', $proy_id);
          $this->db->update('_proyectos', $update_proyect);

          $this->model_proyecto->update_resp_proy($proy_id,$tue,$poa,0,0,0);

          /*---------------- en caso de que la fecha inicial se adelante ---------------*/
            if($fechas[0]['inicio']<$gi){
              $fecha=$fechas[0]['inicio'];
              $nro=$this->input->post('gi')-$fechas[0]['inicio'];
              for($i=1;$i<=$nro;$i++){
                $aper_gestion=$this->model_proyecto->verif_apertura_gestion($proy_id,$fecha);
                if(count($aper_gestion)==0){
                  $this->model_proyecto->add_apertura($proy_id,$fecha,$aper[0]['aper_programa'],'','',$nombre,$this->fun_id);
                }
                $fecha++;
              }
            }

          /*---------------- en caso en que la fecha inicial se reduzca ---------------*/
            if($fechas[0]['inicio']>$gi){
              $fecha=$gi;
              $nro=$fechas[0]['inicio']-$gi;
              for($i=1;$i<=$nro;$i++){
                $aper = $this->model_proyecto->aper_id($proy_id,$fecha); //// aper_id buscado
                $this->model_proyecto->delete_aper_id($aper[0]['aper_id']); //// elimando apertura programatica
                $fecha++;
              }
            }

            /*---------------- en caso de que la fecha final se amplie ---------------*/
            if($fechas[0]['final']>$gf){ 
              $fecha=$gf+1;
              $nro=$fechas[0]['final']-$gf;
              for($i=1;$i<=$nro;$i++){
                $aper_gestion=$this->model_proyecto->verif_apertura_gestion($proy_id,$fecha);
                if(count($aper_gestion)==0){
                  $this->model_proyecto->add_apertura($proy_id,$fecha,$aper[0]['aper_programa'],'','',$nombre,$this->fun_id);
                }
                $fecha++;
              }
            }

            elseif($fechas[0]['final']<$gf){
              $fecha=$gf;
              $nro=$gf-$fechas[0]['final'];

              for($i=1;$i<=$nro;$i++){
               $apertura = $this->model_proyecto->aper_id($proy_id,$fecha); //// aper_id buscado
               $this->model_proyecto->delete_aper_id($apertura[0]['aper_id']); //// elimando apertura programatica
                $fecha--;
              }
            }

            $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);
            if($proyecto[0]['estado']==2){
              $this->session->set_flashdata('success','LOS DATOS DEL PROYECTO SE MODIFICARON CORRECTAMENTE');
              redirect('admin/proy/proyecto_pi/'.$proy_id.''); ///// Formulario de Objetivos y problemas
            }
            else{
              $this->session->set_flashdata('danger','ERROR AL MODIFICAR DATOS GENERALES');
              redirect('admin/proy/edit/'.$proy_id.''); ///// Formulario de registro-Proyectos
            }

        }
        
    }
    else{
        echo "<font color='red'><b>ERROR - SISTEMA!!!!</b></font>";
    }
  }


  /*========= OBTIENE LOS DATOS DE LOS RESPONSABLES  =======*/
    public function get_responsables(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            $id = $post['id_p']; /// id proyecto
            $tp = $post['tp']; /// tipo de responsable
            $id = $this->security->xss_clean($id);
            $tp = $this->security->xss_clean($tp);
            $dato_resp = $this->model_proyecto->responsable_proy($id,$tp);
            //caso para modificar el codigo de proyecto y actividades
            foreach($dato_resp as $row){
                $result = array(
                    'proy_id' => $row['proy_id'],
                    "fun_id" =>$row['fun_id'],
                    "responsable" =>$row['fun_nombre'].' '.$row['fun_paterno'].' '.$row['fun_materno']
                );
            }
            echo json_encode($result);
        }else{
            show_404();
        }
    }
  /*================================================================*/

    /*---- OBSERVAR UNIDAD/ESTABLECIMIENTO/PROYECTO ------*/
    public function observar_poa(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); 

        /*--------- UPDATE ESTADO APERTURA ----------*/
          $update_aper = array(
            'aper_proy_estado' => 1,
            'fun_id' => $this->fun_id
          );
          $this->db->where('aper_id', $proyecto[0]['aper_id']);
          $this->db->update('aperturaprogramatica', $update_aper);

          $update_proy = array(
            'proy_estado' => 1,
            'fun_id' => $this->fun_id
          );
          $this->db->where('proy_id', $proyecto[0]['proy_id']);
          $this->db->update('_proyectos', $update_proy);

          if($proyecto[0]['tp_id']==1){
            /// eliminando registro de temporalidad inicial form5
            $this->db->where('proy_id', $proy_id);
            $this->db->delete('temporalidad_inicial_total_insumo');
          }
          
          $result = array(
            'respuesta' => 'correcto',
          
          );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*---- VALIDAR POA PARA SU APROBACION ------*/
    public function validar_poa(){
        // Validamos la legitimidad asíncrona de la ráfaga de red (Evita accesos directos por URL)
        if($this->input->is_ajax_request() && $this->input->post()){
            
            $post = $this->input->post();
            $proy_id = intval($this->security->xss_clean($post['proy_id']));

            if ($proy_id <= 0) {
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'Identificador numérico de proyecto inválido.'));
                exit;
            }

            // Recuperamos el ID relacional de la Apertura Programática
            $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); 

            if(empty($proyecto)){
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'error', 'respuesta' => 'error', 'message' => 'No se localizó la Categoria Programática del proyecto consultado.'));
                exit;
            }

            // ==========================================================================
            // 🌟 INICIO DE ENTORNO TRANSACCIONAL ATÓMICO EN POSTGRESQL (CNS STANDARD)
            // ==========================================================================
            $this->db->trans_start();

            /*--- PASO UNIQUE: UPDATE ESTADO APERTURA (Bloqueo por Aprobación = 4) ---*/
            $update_aper = array(
                'aper_proy_estado' => 4, // Estado oficial de cierre de formulación
                'fun_id'           => $this->fun_id
            );
            
            $this->db->where('aper_id', intval($proyecto[0]['aper_id']));
            $this->db->update('aperturaprogramatica', $update_aper);

            $this->db->trans_complete();
            // ==========================================================================

            // 📬 SANEAMIENTO MÁSTER: Purgamos buffers intermedios para una salida JSON pura
            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            if ($this->db->trans_status() === FALSE) {
                echo json_encode(array(
                    'status'    => 'error',
                    'respuesta' => 'error',
                    'message'   => 'PostgreSQL rechazó el cierre del POA debido a una restricción de integridad relacional en el clasificador.'
                ));
            } else {
                echo json_encode(array(
                    'status'    => 'success',
                    'respuesta' => 'correcto' // Engancha perfecto con tu response.respuesta === 'correcto' del done en form5.js
                ));
            }
            exit; // Congela el hilo de red impidiendo layouts extras de CodeIgniter al cierre

        } else {
            // Despacha un error formal de Apache si intentan ingresar por URL directa del navegador
            show_404();
        }
    }



    /*---- APROBAR POA ------*/
    public function aprobar_poa(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);

        if($proyecto[0]['tp_id']==1){ /// Proyecto de Inversion
          /*--- UPDATE ESTADO APERTURA ---*/
          $update_aper = array(
            'aper_proy_estado' => 4,
            'fun_id' => $this->fun_id
          );
          $this->db->where('aper_id', $proyecto[0]['aper_id']);
          $this->db->update('aperturaprogramatica', $update_aper);

              if($proyecto[0]['sw_ppto_ini']==0){
                  ///----------- eliminando el registro anterior de la temporalidad inicial
                  if(count($this->model_insumo->temporalidad_inicial_total_unidad($proy_id))!=0){
                    $this->db->where('proy_id', $proy_id);
                    $this->db->delete('temporalidad_inicial_total_insumo');
                  }
                ///-------------

                /// ----- registramos temporalidad inicial
                  $get_temporalidad=$this->model_insumo->list_temporalidad_programado_unidad($proyecto[0]['aper_id']);
                  for ($i=1; $i <=12 ; $i++) { 
                    if($get_temporalidad[0]['mes'.$i]!=0){
                        $data_to_store3 = array(
                          'proy_id' => $proy_id,
                          'aper_id' => $proyecto[0]['aper_id'],
                          'mes_id' => $i,
                          'temp_fis' => $get_temporalidad[0]['mes'.$i],
                        );
                        $this->db->insert('temporalidad_inicial_total_insumo', $data_to_store3);
                    }
                  }

                  /// actualizando el sw delppto inicial
                    $update_proy = array(
                        'sw_ppto_ini' => 1,
                        'fun_id' => $this->fun_id
                      );
                      $this->db->where('proy_id', $proyecto[0]['proy_id']);
                      $this->db->update('_proyectos', $update_proy);
                  /// --------------------------------------  
              }

        }
        else{ /// Gasto Corriente
          /*--- UPDATE ESTADO POA ---*/
          $update_proy = array(
            'proy_estado' => 4,
            'fun_id' => $this->fun_id
          );
          $this->db->where('proy_id', $proyecto[0]['proy_id']);
          $this->db->update('_proyectos', $update_proy);
        }

          $result = array(
            'respuesta' => 'correcto',
          );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*=========== ELIMINAR PROYECTO ==============*/
    public function delete_proyecto($tp,$proy_id){
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);
        $aperturas = $this->model_proyecto->mis_programas($proy_id);

          foreach($aperturas as $row){
            $update_prog = array(
              'aper_estado' => '3',
              'fun_id' => $this->fun_id);
            $this->db->where('aper_id', $row['aper_id']);
            $this->db->update('aperturaprogramatica', $update_prog);
          }

          /*--------- ACTUALIZANDO ESTADO DEL PROYECTO ---------*/
            $update_proy = array(
              'estado' => '3',
              'fun_id' => $this->fun_id);
            $this->db->where('proy_id', $proy_id);
            $this->db->update('_proyectos', $update_proy);
          /*----------------------------------------------------*/

          /*------ ACTUALIZANDO ESTADO FASEETAPACOMPONENTE -----*/
            $update_fase = array(
              'estado' => '3',
              'fun_id' => $this->fun_id);
            $this->db->where('proy_id', $proy_id);
            $this->db->update('_proyectofaseetapacomponente', $update_fase);
          /*----------------------------------------------------*/

          /*----------- Anulando requerimientos ------------*/
            $update_ins= array(
              'fun_id' => $this->fun_id,
              'aper_id' => 0,
              'ins_estado' => 3,
              'num_ip' => $this->input->ip_address(), 
              'nom_ip' => gethostbyaddr($_SERVER['REMOTE_ADDR'])
            );
            $this->db->where('aper_id', $proyecto[0]['aper_id']);
            $this->db->update('insumos', $this->security->xss_clean($update_ins));
          /*-----------------------------------------------*/

            $this->session->set_flashdata('success','EL PROYECTO SE ELIMINO CORRECTAMENTE');
            if($tp==1){
              redirect('admin/proy/list_proy');
            }
            elseif ($tp==2) {
              redirect('admin/proy/list_proy_poa');
            }
            else{
              echo "<font color=red><center>ERROR AL ELIMINAR</center></font>";
            }
              
    }

    /*------------------------ Get Presupuesto Operacion ----------------------*/
    public function obtiene_presupuesto(){
      if($this->input->is_ajax_request() && $this->input->post()){
          $post = $this->input->post();
          $proy_id = $this->security->xss_clean($post['proy_id']);
          $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); /// Datos Proyecto
          $fase = $this->model_faseetapa->get_id_fase($proy_id); //// Datos de la Fase
          $fase_gestion=$this->model_faseetapa->list_fases_gestiones($fase[0]['id']); /// Lista de Fases Gestiones
          
          if(count($proyecto)!=0){
            $result = array(
              'respuesta' => 'correcto',
              'proyecto' => $proyecto,
              'fase' => $fase,
              'fase_gestion' => $fase_gestion
            );
          }
          else{
            $result = array(
                'respuesta' => 'error'
            );
          }
          echo json_encode($result);
      }else{
          show_404();
      }
    }

    /*================ OBTIENE LOS DATOS DEL PROYECTO ================*/
    public function get_proyecto(){
        if($this->input->is_ajax_request() && $this->input->post()){
            $post = $this->input->post();
            $cod = $post['id_proy'];
            $id = $this->security->xss_clean($cod);
            $dato_proy = $this->model_proyecto->get_id_proyecto($id);
            //caso para modificar el codigo de proyecto y actividades
            foreach($dato_proy as $row){
                $result = array(
                  'proy_id' => $row['proy_id'],
                  "proy_nombre" =>$row['proy_nombre']
                );
            }
            echo json_encode($result);
        }else{
            show_404();
        }
    }


/*====================================================================================================================*/
    /*--- VERIFICANDO APERTURA PROGRAMATICA GASTO CORRIENTE---*/
    function verif(){
      if($this->input->is_ajax_request()){
          $post = $this->input->post();
          $cod1 = $post['prog'];
          $cod2= $post['proy'];
          $cod3 = $post['act'];

          $variable= $this->model_proyecto->verif_programa_unidad($cod1,$cod3);
          /*if(count($variable)==0){
            echo "true"; ///// no existe un CI registrado
          }
          else{
            echo "false"; //// existe el CI ya registrado
          }*/
          echo "true";
 
      }else{
        show_404();
      }
    }

    /*--- VERIFICANDO APERTURA PROGRAMATICA PROYECTO DE INVERSION ---*/
    function verif_apg_pi(){
      if($this->input->is_ajax_request()){
          $post = $this->input->post();
          $aper_id = $post['prog']; /// aper id
          $proyecto= $post['proy']; /// cod. proyecto
          $actividad = $post['act']; /// cod Actividad

          $get_programa=$this->model_proyecto->get_aper_programa($aper_id);
          $variable= $this->model_proyecto->verif_programa_pi($get_programa[0]['aper_programa'],$proyecto);

          if(count($variable)==1){
            echo "true"; /////  existe Apertura registrado
          }
          else{
            echo "false"; //// no existe Apertura
          }
 
      }else{
        show_404();
      }
    }


    /*---------  UNIDAD DISTRITALES ---------*/
    public function combo_distrital($accion=''){ 
      $salida="";
      $accion=$_POST["accion"];
      switch ($accion) {
        case 'distrital':
        $salida="";
          $id_pais=$_POST["elegido"];
          
          $combog = pg_query('SELECT *
          from _distritales 
          where  dep_id='.$id_pais.'');
          $salida.= "<option value='0'>SELECCIONE DISTRITAL</option>";
          while($sql_p = pg_fetch_row($combog))
          {$salida.= "<option value='".$sql_p[0]."'>".$sql_p[5]." - ".$sql_p[2]."</option>";}

        echo $salida; 
        //return $salida;
        break;
      }
    }

    /*---------  UNIDAD EJECUTORA -----------*/
    public function combo_ue($accion=''){ 
      $salida="";
      $accion=$_POST["accion"];
      switch ($accion) {
        case 'distrital':
        $salida="";
          $id_pais=$_POST["elegido"];
          
          $combog = pg_query('SELECT *
          from _distritales 
          where  dep_id='.$id_pais.' and dist_ue=1');
          $salida.= "<option value=''>Seleccione Unidad Ejecutora</option>";
          while($sql_p = pg_fetch_row($combog))
          {$salida.= "<option value='".$sql_p[0]."'>".$sql_p[5]." - ".$sql_p[2]."</option>";}

        echo $salida; 
        //return $salida;
        break;
      }
    }

    //////// MIGRAR ACTIVIDADES A NIVEL INSITUCIONAL (Archivo Excel) 2027
    public function valida_add_form4_insitucional() {
        ini_set('max_execution_time', 300); // 5 minutos
        ini_set('memory_limit', '512M');    // Aumentar memoria

        $this->load->library('excel'); 
        if (!isset($_FILES['archivo']) || empty($_FILES['archivo']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        $tn_id = intval($this->input->post('tn_id')); // Nivel capturado dinámicamente
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
            $limitePermitido = 21; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial estructurado exige exactamente $limitePermitido columnas (Hasta la 'V').")));
                return;
            }

            if($tn_id==1 || $tn_id==2 || $tn_id==3){ /// Establecimiento de Salud
              $lista_poa_segun_tipo_establecimiento=$this->model_proyecto->get_tp_UnidadOrganizacional($tn_id);
            }
            elseif($tn_id==4){ /// fortalecimiento
              $lista_poa_segun_tipo_establecimiento=$this->model_proyecto->get_tp_UnidadOrganizacional_prog_bolsa('771');
            }
            elseif($tn_id==5){ /// Bienes y Servicio
              $lista_poa_segun_tipo_establecimiento=$this->model_proyecto->get_tp_UnidadOrganizacional_prog_bolsa('720');
            }
            else{ /// Medicina del Trabajo
              $lista_poa_segun_tipo_establecimiento=$this->model_proyecto->get_tp_UnidadOrganizacional_prog_bolsa('730');
            }

            
            for ($i = 2; $i <= $filasMax; $i++) {
                $or_id  = 0;
                // Extraer valores básicos de la fila activa
                $cod_act            = trim($hoja->getCell('A' . $i)->getValue());
                $actividad          = trim($hoja->getCell('B' . $i)->getValue());
                $resultado          = trim($hoja->getCell('C' . $i)->getValue());
                $unidad_responsable = trim($hoja->getCell('D' . $i)->getValue());
                $indicador          = trim($hoja->getCell('E' . $i)->getValue());
                $meta               = $hoja->getCell('F' . $i)->getValue();
                $medioverificacion  = trim($hoja->getCell('S' . $i)->getValue());

                $indi  = trim($hoja->getCell('T' . $i)->getValue());
                $tp_meta  = trim($hoja->getCell('U' . $i)->getValue());

                // 🌟 BLINDAJE ANTIFALLA: Filtra y salta las filas vacías inferiores del Excel
                if (empty($cod_act) && empty($actividad) && empty($resultado) && empty($indicador) && (empty($meta) || floatval($meta) == 0)) {
                    continue;
                }

                if (!is_numeric($meta) || floatval($meta) <= 0) {
                    $meta=0;
                    //$errores[] = "Fila $i: La 'META' debe ser un número válido mayor a cero.";
                }

                // Validación C: Cronograma Mensualizado Saneado (J al U)
                $suma_meses = 0;
                $columnas_meses = array('G','H','I','J','K','L','M','N','O','P','Q','R');
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

                if (empty($errores)) {
                    //// replicando el registro a las regionales
                    foreach($lista_poa_segun_tipo_establecimiento as $row){
                        $data_insertar[] = array(
                          'maestro' => array(
                            'com_id'                   => $row['com_id'],
                            'prod_cod'                 => intval($cod_act),
                            'prod_producto'            => strtoupper($actividad),
                            'prod_resultado'           => strtoupper($resultado),
                            'indi_id'                  => floatval($indi),
                            'mt_id'                    => floatval($tp_meta),
                            'prod_indicador'           => strtoupper($indicador),
                            'prod_fuente_verificacion' => strtoupper($medioverificacion), 
                            'prod_linea_base'          => 0,
                            'prod_meta'                => floatval($meta),
                            'uni_resp'                 => 0, 
                            'prod_unidades'            => strtoupper($unidad_responsable),
                            'acc_id'                   => 0,
                            'prod_ppto'                => 1,
                            'fecha'                    => date("d/m/Y H:i:s"),
                            'or_id'                    => 0,
                            'fun_id'                   => intval($this->session->userdata('fun_id')),
                            'num_ip'                   => $this->input->ip_address(), 
                            'nom_ip'                   => gethostbyaddr($_SERVER['REMOTE_ADDR'])
                          ),
                        'meses_lote' => $meses_valores // Queda amarrado en paralelo
                      );
                    }
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


}