<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

class Eval_oregional extends CI_Controller{
    public function __construct (){
      parent::__construct();
      $this->load->model('programacion/model_proyecto');
      $this->load->model('resultados/model_resultado');
      $this->load->model('mestrategico/model_mestrategico');
      $this->load->model('mestrategico/model_objetivogestion');
      $this->load->model('mestrategico/model_objetivoregion');
      $this->load->model('ejecucion/model_evaluacion');
      $this->load->model('programacion/model_producto');
      $this->load->model('mantenimiento/model_configuracion');
      $this->load->model('mantenimiento/model_ptto_sigep');
      $this->load->model('programacion/insumos/model_insumo');
      $this->load->model('menu_modelo');
      $this->load->library('security');

      $this->gestion = $this->session->userData('gestion');
      $this->adm = $this->session->userData('adm');
      $this->dist = $this->session->userData('dist');
      $this->tmes = $this->session->userData('trimestre');
      $this->fun_id = $this->session->userData('fun_id');
      $this->verif_mes=$this->session->userData('mes_actual');
      $this->resolucion=$this->session->userdata('rd_poa');
      $this->tp_adm = $this->session->userData('tp_adm');
      $this->mes = $this->mes_nombre();
      $this->dep_id = $this->session->userData('dep_id');
      $this->conf_form4 = $this->session->userData('conf_form4');
      $this->conf_form5 = $this->session->userData('conf_form5');
      $this->conf_estado = $this->session->userData('conf_estado'); /// conf estado Gestion (1: activo, 0: no activo)
      $this->fecha_plazo_actualizacion = strtotime(date('2022-05-6'));
    }

    
    /*------- TITULO --------*/
    public function titulo(){
      $tabla='';
      $trimestre=$this->model_evaluacion->trimestre();
      $tabla.='
        <article class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
            <div class="well">
              <h2>EVALUACI&Oacute;N DE OPERACIONES (FORMULARIO N° 2) '.$trimestre[0]['trm_descripcion'].' / '.$this->gestion.'</h2>
            </div>
        </article>';

      // if ($this->tp_adm==1) {
      //   $tabla.='
      //     <div id="row">
      //       <article class="col-xs-12 col-sm-12 col-md-2 col-lg-2">
      //           <a href="#" data-toggle="modal" data-target="#modal_update_evaluacion" class="btn btn-default update_evaluacion" style="width:100%;" title="ACTUALIZAR EVALUACION OBJETIVO REGIONAL" ><img src="'.base_url().'assets/Iconos/arrow_refresh.png" WIDTH="25" HEIGHT="30"/>&nbsp;UPDATE EVALUACIÓN DE OPERACIONES</a>    
      //       </article>
      //     </div>';
      // }

      // $tabla.='
      // <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
      //   <div class="well" id="load_update_temp_general" style="display: none;" >
      //     <center><img src="'.base_url().'/assets/img_v1.1/preloader.gif" style="width:13%; height:2.5%"><br><b>ACTUALIZANDO EVALUACIÓN POA - OPERACIONES ...</b></center>
      //   </div>
      // </article>';

      return $tabla;
    } 


    /*-------- LISTA DE REGIONALES ----------*/
    public function regionales(){
      $regionales=$this->model_proyecto->list_departamentos();
      $trimestre=$this->model_evaluacion->trimestre();
      $tabla = '
        <input name="base" type="hidden" value="'.base_url().'">
        <article class="col-sm-12">
          <div class="well">
            <form class="smart-form">
              <header><h2><b>EVALUACI&Oacute;N DE OPERACIONES (FORMULARIO N° 2) </b> - '.$trimestre[0]['trm_descripcion'].' / '.$this->gestion.'</h2></header>
              <fieldset>          
                <div class="row">
                  <section class="col col-3">
                    <label class="label">DIRECCIÓN ADMINISTRATIVA</label>
                    <select class="form-control" id="dep_id" name="dep_id" title="SELECCIONE REGIONAL">
                      <option value="0">SELECCIONE REGIONAL</option>';
                      foreach($regionales as $row){
                        if($row['dep_id'] != 0){
                          $tabla .= '<option value="'.$row['dep_id'].'">'.$row['dep_id'].'.- '.strtoupper($row['dep_departamento']).'</option>';
                        }
                      }
                    $tabla .= '
                    </select>
                  </section>
                </div>
              </fieldset>
            </form>
          </div>
        </article>
        <article class="col-sm-12">
          <div id="titulo_lista"></div>
        </article>';

      return $tabla;
    }

  

  //// FORMULARIO DE EVALUACION DE FORMULARIO 2 - REGIONAL ALINEADO A OBJETIVOS REGIONALES 2027 ajustando
    public function ver_relacion_ogestion($dep_id){
    $tabla='';
    $acp_regional=$this->model_objetivogestion->lista_acp_x_regional($dep_id);
    $departamento=$this->model_proyecto->get_departamento($dep_id);
    $trimestre=$this->model_evaluacion->trimestre();
    $metas = $this->model_producto->tp_metas(); /// tp metas
    $tabla.='
      <input name="base" type="hidden" value="'.base_url().'">
      <div class="jarviswidget well" id="wid-id-3" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-togglebutton="false" data-widget-deletebutton="false" data-widget-fullscreenbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
        <header>
          <span class="widget-icon"> <i class="fa fa-comments"></i> </span>
          <h2>...</h2>
        </header>
        <div>
          <div class="jarviswidget-editbox">
          </div>
          <div class="widget-body">
            <p>
              <h2><b>EVALUACIÓN OPERACIONES '.strtoupper($departamento[0]['dep_departamento']).' </b> - '.$trimestre[0]['trm_descripcion'].' / '.$this->gestion.'</h2>
              <br>
                <a href="javascript:abreVentana(\''.site_url("").'/rep_oregional/'.$dep_id.'\');" title="REPORTE REGIONAL" class="btn btn-lg btn-default" style="font-size: 12px; color:#1e5e56; border-color:#1e5e56"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/> &nbsp;<b>IMPRIMIR OPERACIONES (Form N° 2)</b></a>
                <a href="javascript:abreVentana(\''.site_url("").'/rep_eval_oregional/'.$dep_id.'\');" title="REPORTE EVALUACIÓN META REGIONAL" class="btn btn-lg btn-default" style="font-size: 12px; color:#1e5e56; border-color:#1e5e56"><img src="'.base_url().'assets/Iconos/printer.png" WIDTH="20" HEIGHT="20"/> &nbsp;<b>IMPRIMIR EVALUACIÓN (Form N° 2)</b></a>
                <a href="#" data-toggle="modal" data-target="#modal_cumplimiento_grafico" class="btn btn-lg btn-default" name="'.$dep_id.'" onclick="nivel_cumplimiento_operaciones_grafico('.$dep_id.','.$this->tmes.');" title="NIVEL DE CUMPLIMIENTO DE OPERACIONES (GRAFICO)" style="font-size: 12px; color:#1e5e56; border-color:#1e5e56"><img src="'.base_url().'assets/Iconos/chart_bar.png" WIDTH="20" HEIGHT="20"/> &nbsp;<b>DETALLE CUMPLIMIENTO (Form N° 2)</b></a>
            </p>
            <ul id="myTab1" class="nav nav-tabs bordered">';
              $nro=0;
              foreach($acp_regional as $row){
                $nro++;
                $activo='';
                if($nro==1){
                  $activo='class="active"';
                }
                $tabla.='
                <li '.$activo.'>
                  <a href="#s'.$nro.'" data-toggle="tab"><b>A. C. P.</b><span class="badge bg-color-green txt-color-white">'.$row['og_codigo'].'</span></a>
                </li>';
              }
            $tabla.='
            </ul>

            <div id="myTabContent1" class="tab-content padding-10">';
            $nro2=0;
            foreach($acp_regional as $oge){
              $lista_form2=$this->model_objetivoregion->list_form2_regional_x_form1($oge['og_id'],$dep_id);
              $nro2++;
              $active='class="tab-pane fade"';
              if($nro2==1){
                $active='class="tab-pane fade in active"';
              }

              $tipo=''; $size='col col-5';
              if($oge['indi_id']==2){
                $tipo='%';
                $size='col col-7';
              }

              $tabla.='
              <div '.$active.' id="s'.$nro2.'">
                <div class="row">
                <form class="smart-form">
                <legend><b>A.C.P. '.$oge['og_codigo'].'</b>.- '.$oge['og_objetivo'].'</legend>
                <fieldset>
                <table class="table table-bordered" border=0.2 style="width:100%;" id="datos">
                  <thead>
                  <tr style="font-size: 11px;">
                    <th style="width:1%;height:10px;color:#FFF; text-align: center" bgcolor="#1c7368">#</th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. ACP.</b></th>
                    <th style="width:1.5%;color:#FFF; text-align: center" bgcolor="#1c7368"><b>COD. OPE.</b></th>
                    <th style="width:11%;color:#FFF; text-align: center" bgcolor="#1c7368">OPERACI&Oacute;N</th>
                    <th style="width:11%;color:#FFF; text-align: center" bgcolor="#1c7368">RESULTADO</th>
                    <th style="width:10%;color:#FFF; text-align: center" bgcolor="#1c7368">INDICADOR</th>
                    <th style="width:5%;color:#FFF; text-align: center" bgcolor="#1c7368">TP. INDI.</th>
                    <th style="width:10%;color:#FFF; text-align: center" bgcolor="#1c7368">MEDIO VERIFICACI&Oacute;N</th>
                    <th style="width:2%;color:#FFF; text-align: center" bgcolor="#1c7368">META</th>
                    <th style="width:2%;color:#FFF; text-align: center" bgcolor="#1c7368">META ALINEADO</th>
                    <th style="width:6%;color:#FFF; text-align: center" bgcolor="#1c7368">META (I) TRIMESTRE</th>
                    <th style="width:6%;color:#FFF; text-align: center" bgcolor="#1c7368">META (II) TRIMESTRE</th>
                    <th style="width:6%;color:#FFF; text-align: center" bgcolor="#1c7368">META (III) TRIMESTRE</th>
                    <th style="width:6%;color:#FFF; text-align: center" bgcolor="#1c7368">META (IV) TRIMESTRE</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. AL TRIMESTRE</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368">(%) CUMP. A LA GESTIÓN</th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368"></th>
                    <th style="width:5%;color:#FFF;" bgcolor="#1c7368"></th>
                  </tr>
                  </thead>
                  <tbody>';
                  $nro_ope=0;
                  foreach($lista_form2 as $row){
                    $meta='';
                    if ($row['indi_id']==2) {
                      $meta='%';
                    }

                    $tp_meta='ACUMULADO';
                    if($row['tp_meta']==1){
                      $tp_meta='RECURRENTE MENSUAL';
                    }
                    elseif($row['tp_meta']==5){
                      $tp_meta='RECURRENTE TRIMESTRAL';
                    }

                    $color=''; $titulo='';$grafico='';
                    $boton_ajustar_apriorizados='
                        <center><a href="'.site_url("").'/me/alineacion_ope_acp/'.$row['og_id'].'" target="_blank" class="btn btn-default" title="VER ALINEACION ACP-FORM4"><img src="'.base_url().'assets/Iconos/application_double.png" WIDTH="30" HEIGHT="30"/></a>
                        <br>AJUSTAR ALINEACIÓN </center>';

                  
                      if($row['indi_id']==1 || $row['indi_id']==2){
                        $meta_priorizado=round($row['trm4'],2); /// suma trm4
                      }
                      else{
                        $meta_priorizado=$row['nro']; /// nro
                      }

                      ///-----------------------------------------------------
                        $this->db->where('or_id', $row['or_id']);
                        $this->db->delete('temp_trm_prog_objetivos_regionales');

                         /// --- eliminando ejecucion de Objetivos Regionales
                         $this->db->where('or_id', $row['or_id']);
                         $this->db->delete('temp_trm_ejec_objetivos_regionales');
                      ///-----------------------------------------------------


                      if(round($row['or_meta'], 2) == $meta_priorizado) {
                          $boton_ajustar_apriorizados = '<div style="font-size: 15px; color:blue" align=center><b>'.$meta_priorizado.''.$meta.'</b></div>';
                          $datos_fila_json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                          $grafico = '<br><a href="#" data-toggle="modal" data-target="#modal_cumplimiento" class="btn btn-lg btn-default" name="'.$row['or_id'].'" onclick="nivel_cumplimiento(this, '.$datos_fila_json.');" title="NIVEL DE CUMPLIMIENTO"><img src="'.base_url().'assets/Iconos/chart_bar.png" WIDTH="30" HEIGHT="30"/></a>';
                        
                          /// registrando temporalidad
                          for ($i=1; $i <=4 ; $i++) { 
                            if($row['trm'.$i]!=0){
                                $data_to_store2 = array( ///// Tabla temp prog oregional
                                'or_id' => $row['or_id'], /// or id
                                'trm_id' => $i, /// trimestre
                                'pg_fis' => round($row['trm'.$i],2), /// valor Programado Trimestral
                                'g_id' => $this->gestion, /// gestion                
                              );
                              $this->db->insert('temp_trm_prog_objetivos_regionales', $data_to_store2);
                            }
                            
                            if($row['e_trm'.$i]!=0){
                              $data_to_store2 = array( ///// Tabla temp prog oregional
                                'or_id' => $row['or_id'], /// or id
                                'trm_id' => $i, /// trimestre
                                'ejec_fis' => round($row['e_trm'.$i],2), /// valor Ejecutado Trimestral
                                'g_id' => $this->gestion, /// gestion                
                              );
                              $this->db->insert('temp_trm_ejec_objetivos_regionales', $data_to_store2);
                            }
                          }
                          /// -----------------------
                      }
                      else{
                        $boton_ajustar_apriorizados='
                        <center><b>('.round($meta_priorizado,2).')</b><br><a href="'.site_url("").'/me/alineacion_ope_acp/'.$row['og_id'].'" target="_blank" class="btn btn-default" title="VER ALINEACION ACP-FORM4"><img src="'.base_url().'assets/Iconos/application_double.png" WIDTH="30" HEIGHT="30"/></a>
                        <br>AJUSTAR ALINEACIÓN</center>';
                      }
                    

                    $nro_ope++;
                    if($row['or_priorizado']==1){
                      $color='#DFF5E0';
                    }

                    $tabla.='
                      <tr style="font-size: 10px;" bgcolor='.$color.'>
                        <td style="width:1%; height:10px;" align=center title='.$row['pog_id'].'>'.$nro_ope.'</td>
                        <td style="width:1.5%; font-size: 17px; color:blue" align="center"><b>'.$row['og_codigo'].'</b></td>
                        <td style="width:1.5%; font-size: 17px;" align="center" bgcolor="#f1eeee" title='.$row['or_id'].'><b>'.$row['or_codigo'].'</b></td>
                        <td style="width:11%;">'.$row['or_objetivo'].'</td>
                        <td style="width:11%;">'.$row['or_resultado'].'</td>
                        <td style="width:10%;">'.$row['or_indicador'].'</td>
                        <td style="width:5%;" title="'.$row['indi_id'].' -> '.$row['tp_meta'].'">
                          <b>'.strtoupper($row['indi_descripcion']).'</b>';
                        $tabla.='
                          <font color=blue>'.$tp_meta.'</font>
                        </td>
                        <td style="width:10%;">'.$row['or_verificacion'].'</td>
                        <td style="width:2%; font-size: 15px;" align=center title="'.$row['tp_meta'].'"><b>'.round($row['or_meta'],2).' '.$meta.'</b></td>
                        <td style="width:2%;" align=center>'.$boton_ajustar_apriorizados.'</td>';
                         $tabla.=$this->genera_temporalidad_form2($row);
                        $tabla.='
                        <td style="font-family:Verdana;font-size: 18px;" align=center><b>'.round($row['porc_cump_'.$this->tmes],2).' %</b></td>
                        <td style="font-family:Verdana;font-size: 18px;" align=center><b>'.round($row['trm_porc_ejec'.$this->tmes],2).' %</b></td>';
                        if($meta_priorizado==0){
                          $tabla.='
                          <td></td>
                          <td></td>';
                        }
                        else{
                          $tabla.='
                          <td>
                            <a href="#" data-toggle="modal" data-target="#modal_act_priorizados" style="font-size: 10px;" class="btn btn-lg btn-default" name="'.$row['or_id'].'"  onclick="ver_actividades_priorizados('.$row['or_id'].','.$dep_id.');" title="VER MIS ACTIVIDADES PRIORIZADOS">ACT. PRIORIZADOS</a>
                          </td>
                          <td align=center>'.$grafico.'</td>';
                        }
                        $tabla.='
                      </tr>';
                  }
                  $tabla.='
                  </tbody>
                </table>
                </fieldset>
                </form>
              </div>
              </div>';
            }

            $tabla.='
            </div>
          </div>
        </div>
      </div>';

      return $tabla;
    }



    //// Modal graficos por OPERACION 2027
    public function modal_graficos_x_form2(){
      $tabla='';
       $tabla.='
          <div class="modal fade" id="modal_cumplimiento" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog" id="mdialTamanio3">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" id="amcl" title="SALIR"><span aria-hidden="true">&times; <b>Salir Formulario</b></span></button>
                    </div>
                    <div class="modal-body">
                    <h2 class="alert alert-info"><center>(%) CUMPLIMIENTO OPERACIÓN - '.$this->gestion.'</center></h2>
                        <div class="row">
                          <div> 
                            <h3 class="text-primary" id="titulo_grafico"></h3><hr>
                            <div class="row">
                              <article class="col-sm-12 col-md-12 col-lg-6">
                                <div class="well">
                                  <center><div id="parametro_efi" style="width: 750px; height: 390px; margin: 0 auto"></div></center>
                                </div>
                                <hr>
                                  <table class="table table-bordered" border=0.2 style="width:100%;">
                                    <thead>
                                      <tr>
                                        <th><center>I TRIMESTRE</center></th>
                                        <th><center>II TRIMESTRE</center></th>
                                        <th><center>III TRIMESTRE</center></th>
                                        <th><center>IV TRIMESTRE</center></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr id="tab"></tr>  
                                    </tbody>
                                  </table>
                              </article>
                            
                              <article class="col-sm-12 col-md-12 col-lg-6">
                                <div class="well">
                                  <center><div id="parametro_efi2" style="width: 750px; height: 390px; margin: 0 auto"></div></center>
                                </div>
                                <hr>
                                  <table class="table table-bordered" border=0.2 style="width:100%;">
                                    <thead>
                                      <tr>
                                        <th><center>I TRIMESTRE</center></th>
                                        <th><center>II TRIMESTRE</center></th>
                                        <th><center>III TRIMESTRE</center></th>
                                        <th><center>IV TRIMESTRE</center></th>
                                      </tr>
                                    </thead>
                                    <tbody>
                                      <tr id="tab_acumulado"></tr>  
                                    </tbody>
                                  </table>
                              </article>
                            </div>
                            <hr>
                          </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        
      return $tabla;
    }


    //// Modal Grafico Cumplimiento de Operaciones x Regional
    public function modal_grafico_cumplimiento_form2_x_regional(){
      $tabla='';
      $tabla .= '
      <div class="modal fade" id="modal_cumplimiento_grafico" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
          <!-- 🔥 CAMBIO: Se redujo el margen superior/inferior (margin: 10px auto) para ganar espacio vertical -->
          <div class="modal-dialog modal-lg" style="width: 94%; max-width: 1300px; margin: 10px auto;">
              <div class="modal-content" style="border-radius: 6px; overflow: hidden;">
                  
                  <!-- BARRA SUPERIOR CON BOTÓN DE IMPRESIÓN Y SALIR -->
                  <div class="modal-header" style="background: #1e3a8a; color: #ffffff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center;">
                      <h4 class="modal-title" style="font-weight: bold; margin: 0; font-size: 16px;">
                          <i class="fa fa-bar-chart"></i> DETALLE DE CUMPLIMIENTO DE OPERACIONES
                      </h4>
                      
                      <div style="margin-left: auto;">
                          <button type="button" class="btn btn-success btn-sm" onclick="imprimirGraficoRegional();" style="background: #10b981; border: none; font-weight: bold; margin-right: 10px; padding: 6px 14px;">
                              <i class="fa fa-print"></i> EXPORTAR REPORTE PDF
                          </button>
                          <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" title="SALIR" style="background: #ef4444; border: none; font-weight: bold; padding: 6px 12px;">
                              <i class="fa fa-times"></i> Salir
                          </button>
                      </div>
                  </div>
                  
                  <!-- 🔥 CAMBIO: Se aumentó max-height a 85vh (85% de la altura total de la pantalla) -->
                  <div class="modal-body" id="area_impresion_regional" style="max-height: 85vh; min-height: 75vh; overflow-y: auto; background: #ffffff; padding: 15px 20px;">
                      
                      <!-- Cabecera Institucional -->
                      <div class="alert alert-info text-center" style="margin-bottom: 10px; padding: 10px; background-color: #f0f9ff; border: 1px solid #bae6fd; color: #1e3a8a; border-radius: 4px;">
                          <h4 style="margin: 0; font-weight: bold; font-size: 15px;">CUADRO DE EVALUACIÓN DE OPERACIONES GESTIÓN ' . $this->gestion . '</h4>
                          <h5 style="margin: 4px 0 0 0; font-weight: bold; font-size: 13px;" id="lbl_regional_titulo">REGIONAL : CARGANDO...</h5>
                      </div>

                      <!-- Banner Dinámico de Porcentaje y Calificación -->
                      <div id="banner_calificacion_regional" class="text-center" style="padding: 12px; border-radius: 4px; color: #ffffff; margin-bottom: 15px; background-color: #64748b; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                          <div style="font-size: 36px; font-weight: bold; line-height: 1.1;" id="lbl_porcentaje_global">0.0%</div>
                          <div style="font-size: 12px; font-weight: bold; letter-spacing: 1.5px; margin-top: 4px; text-transform: uppercase;" id="lbl_estado_calificacion">CALCULANDO...</div>
                      </div>

                      <!-- 🔥 CAMBIO: Contenedor del Gráfico con min-height aumentado a 580px -->
                      <div class="well" style="background: #ffffff; border: 1px solid #e2e8f0; padding: 15px; margin-bottom: 0;">
                          <div id="grafico_barras_regional" style="width: 100%; min-height: 580px;"></div>
                      </div>
                  </div>
              </div>
          </div>
      </div>';


      return $tabla;
    }


    ///// Genera temporalidad 2027
    public function genera_temporalidad_form2($row){
      $tabla='';
      $por='';
      if($row['or_tp']==1){
        $por='%';
      }
      $color='color:blue';
      if($row['trm4']==0){
        $color='color:red;';
      }
      if($row['or_tp']==1){ /// Inversion
        for ($i=1; $i <=4 ; $i++) { 

          $tabla.='
            <td style="width:6%;" align=center>
              <table class="table table-bordered" border=0.2 style="width:100%;">
                <tr>
                  <td style="width:50%;"><b>(%)PROG.</b></td>
                  <td style="width:50%;font-size: 12px; '.$color.'" align=right><b>'.round($row['trm_porc'.$i],2).''.$por.'</b></td>
                </tr>
                <tr>
                  <td><b>(%)EJEC.</b></td>
                  <td style="font-size: 12px; '.$color.'" align=right><b>'.round($row['trm_porc_ejec'.$i],2).''.$por.'</b></td>
                </tr>
              </table>
          </td>';
        }
      }
      else{
        for ($i=1; $i <=4 ; $i++) { 
          $tabla.='
            <td style="width:6%;" align=center>
              <table class="table table-bordered" border=0.2 style="width:100%;">
                <tr>
                  <td style="width:50%;"><b>PROG.</b></td>
                  <td style="width:50%;font-size: 12px; '.$color.'" align=right><b>'.round($row['trm'.$i],2).''.$por.'</b></td>
                </tr>
                <tr>
                  <td><b>EJEC.</b></td>
                  <td style="font-size: 12px; '.$color.'" align=right><b>'.round($row['e_trm'.$i],2).''.$por.'</b></td>
                </tr>
                <tr>
                  <td><b>(%)</b></td>
                  <td style="font-size: 13px; '.$color.'" align=right><b>'.round($row['porc_cump_'.$i],2).'%</b></td>
                </tr>
              </table>
          </td>';
        }
      }
      
      return $tabla;
    }


    // /*--- ACTUALIZA PARAMETRO DE CALIFICACION OPERACIONES REGIONAL ---*/
    // public function calificacion_total_form2_regional($dep_id,$tp_calificacion){
    //   /// tp_calificacion : 0 (trimestral)
    //   /// tp_calificacion : 1 (Acumulado a la gestion)

    //   $lista_ogestion=$this->model_objetivoregion->list_form2_regional_x_regional($dep_id);
    //   $nro_prog=count($lista_ogestion);
    //   $suma_cumplimiento_trimestral=0;
    //   $suma_cumplimiento_gestion=0;
      
    //   foreach($lista_ogestion as $row){
    //     $calificacion=$this->calificacion_trimestral_acumulado_x_oregional($row['or_id'],$this->tmes,$row['or_tp'],$row['tp_meta']);
    //     $suma_cumplimiento_trimestral=$suma_cumplimiento_trimestral+$calificacion[3];
    //     $suma_cumplimiento_gestion=$suma_cumplimiento_gestion+$calificacion[4];
    //  }

    //  $cumplimiento=0;
    //  if($nro_prog!=0){
    //     $cumplimiento= round(($suma_cumplimiento_trimestral/$nro_prog),2);  
    //     if($tp_calificacion==1){
    //       $cumplimiento= round(($suma_cumplimiento_gestion/$nro_prog),2); 
    //     }
    //  }


    //   $calificacion='';$resp='';$color='';

    //   if($cumplimiento>0 & $cumplimiento<=50){
    //     $resp='<b>INSATISFACTORIO</b>';
    //     $color='#f95b4f';
    //   }
    //   elseif($cumplimiento>50 & $cumplimiento<=75){
    //    $resp='<b>REGULAR</b>';
    //    $color='#edd094';
    //   }
    //   elseif($cumplimiento>75 & $cumplimiento<=99){
    //    $resp='<b>BUENO</b>';
    //    $color='#83bad1';
    //   }
    //   elseif($cumplimiento==100){
    //    $resp='<b>OPTIMO</b>';
    //    $color='#4caf50';
    //   }

    //   $calificacion.='<div style="color:white; background-color:'.$color.'"><center><font size="7px">'.$cumplimiento.'%</font><br>'.$resp.'</center></div>';
    //   return $calificacion;
    // }


    /*-- ARMANDO TEMPORALIDAD PARA OBJETIVOS REGIONAL POR REGIONAL (ACUMULADO) --*/
    // public function get_temporalidad_acumulado_objetivo_regional($or_id,$tp_rep,$tp_or,$tp_meta){
    //   /// tp_rep=0 normal
    //   /// tp_rep=1 Reporte
    //   /// tp_or=1 (inversion), 0 (gasto corriente) 1

    //   $valor=$this->tabla_trimestral_acumulado_x_oregional($or_id,$tp_or,$tp_meta); /// 
    //   $tabla='';

    //       if($tp_rep==0){ /// VISTA NORMAL
    //         for ($i=1; $i <=4 ; $i++) {
    //           $tabla.='
    //           <td style="width:6%;" align=center>
    //             <table class="table table-bordered" border=0.2 style="width:80%;">
    //               <tr>
    //                 <td style="width:50%;"><b>(%) PROG.</b></td>
    //                 <td style="width:50%;font-size: 12px; color:blue" align=right><b>'.$valor[5][$i].'%</b></td>
    //               </tr>
    //               <tr>
    //                 <td><b>(%) EJEC.</b></td>
    //                 <td style="font-size: 12px; color:blue" align=right><b>'.$valor[6][$i].'%</b></td>
    //               </tr>
    //             </table>
    //           </td>';
    //         }
    //       }
    //       else{ /// VISTA PARA REPORTES
    //         for ($i=1; $i <=4 ; $i++) {
    //           $tabla.='
    //           <td style="width:4.5%;" align=center>
    //             <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:90%;" align=center>
    //               <tr>
    //                 <td style="width:50%; height: 2%; font-size: 8px;"><b>%PROG.</b></td>
    //                 <td style="width:50%;font-size: 8.5px;" align=right><b>'.$valor[5][$i].'%</b></td>
    //               </tr>
    //               <tr>
    //                 <td style="width:50%; height: 2%; font-size: 8px;"><b>%EJEC.</b></td>
    //                 <td style="font-size: 8.5px;" align=right><b>'.$valor[2][$i].'%</b></td>
    //               </tr>
    //             </table>
    //           </td>';
    //         }
    //       }

    //   return $tabla;
    // }




    /*-- GENERA TABLA PARA EVALUACION TRIMESTRAL POR OBJETIVO REGIONAL --*/
    // public function tabla_trimestral_acumulado_x_oregional($or_id,$tp_or,$tp_meta){

    //     for ($i=1; $i <=4 ; $i++) { 
    //       $valor=$this->calificacion_trimestral_acumulado_x_oregional($or_id,$i,$tp_or,$tp_meta); /// CALIFICACION TRIMESTRAL POR OBJETIVO REGIONAL GASTO CORRIENTE 
    //       $matriz[1][$i]=$valor[1];  /// prog
    //       $matriz[2][$i]=$valor[2];  /// ejec
    //       $matriz[3][$i]=$valor[3];  /// % cumplimiento trimestral
    //       $matriz[4][$i]=(100-$valor[3]);  /// % no cumplido
    //     }

    //     $total=$matriz[1][4];

    //     for ($i=1; $i <=4 ; $i++) { 
    //       $matriz[5][$i]=round((($matriz[1][$i]/$total)*100),2);  /// % Programado con respecto al total acumulado
    //       $matriz[6][$i]=round((($matriz[2][$i]/$total)*100),2);  /// % Ejecutado con respecto al total acumulado
    //     }

    //   return $matriz;
    // }

    /*-- CALIFICACION TRIMESTRAL POR OBJETIVO REGIONAL GASTO CORRIENTE wil--*/
    // public function calificacion_trimestral_acumulado_x_oregional($or_id,$trimestre,$tp_or,$tp_meta){
    //   $valor = array( '1' => '0','2' => '0','3' => '0','4' => '0');

    //   if(count($this->model_objetivoregion->verif_temporalidad_oregional($or_id))!=0){
    //     $suma_total_prog=0; $suma_prog=0; $suma_ejec=0;$ejecucion=0;
        
    //     if($tp_or==0){ //// GASTO CORRIENTE
              
    //           //// Suma total programado por operacion (Gasto Corriente)
    //           $prog_total=$this->model_objetivoregion->get_trm_temporalidad_prog_total_oregional($or_id);
    //           if(count($prog_total)!=0){
    //             $suma_total_prog=$prog_total[0]['total_prog'];
    //           }


    //             for ($i=1; $i <=$trimestre; $i++) {
    //               $get_trm=$this->model_objetivoregion->get_trm_temporalidad_prog_oregional($or_id,$i); /// Temporalidad Programado
    //               $get_trm_ejec=$this->model_objetivoregion->get_trm_temporalidad_ejec_oregional($or_id,$i); /// Temporalidad Ejecutado

    //               if(count($get_trm)!=0){
    //                 if($tp_meta==5){ /// recurrente trimestral
    //                   $suma_prog=round($get_trm[0]['pg_fis'],2); 
    //                 }
    //                 else{
    //                   $suma_prog=$suma_prog+$get_trm[0]['pg_fis']; 
    //                 }
    //               }

    //               if(count($get_trm_ejec)!=0){
    //                 if($tp_meta==5){ /// recurrente trimestral
    //                   $suma_ejec=$get_trm_ejec[0]['ejec_fis'];
    //                 }
    //                 else{
    //                   $suma_ejec=$suma_ejec+$get_trm_ejec[0]['ejec_fis'];
    //                 }
    //               }

    //               $ejecucion=0;
    //               if($suma_ejec!=0){
    //                 $ejecucion=round((($suma_ejec/$suma_prog)*100),2);
    //               }

    //               $cumplimiento_gestion=0;
    //               if($suma_total_prog!=0){
    //                 $cumplimiento_gestion=round((($suma_ejec/$suma_total_prog)*100),2);
    //               }
    //             }

    //     }
    //     else{ //// INVERSION
    //         $get_trm=$this->model_objetivoregion->get_trm_temporalidad_prog_oregional($or_id,$trimestre); /// Temporalidad Programado Inversion
    //         $get_trm_ejec=$this->model_objetivoregion->get_trm_temporalidad_ejec_oregional($or_id,$trimestre); /// Temporalidad Ejecutado Inversion
    //         $suma_total_prog=$this->model_objetivoregion->get_trm_temporalidad_prog_oregional($or_id,4);

    //           if(count($get_trm)!=0){
    //             $suma_prog=round($get_trm[0]['pg_fis'],2); 
    //           }

    //           if(count($get_trm_ejec)!=0){
    //             $suma_ejec=round($get_trm_ejec[0]['ejec_fis'],2);
    //           }

    //           $ejecucion=0;
    //           if($suma_ejec!=0 && $suma_prog!=0){
    //             $ejecucion=round((($suma_ejec/$suma_prog)*$suma_total_prog[0]['pg_fis']),2);
    //           }

    //           $cumplimiento_gestion=0;
    //           if(count($suma_total_prog)!=0){
    //             $cumplimiento_gestion=round((($suma_ejec/$suma_total_prog[0]['pg_fis'])*100),2);
    //           }

    //     }

    //       $valor[1]=$suma_prog; /// Programado Acumulado al trimestre
    //       $valor[2]=$suma_ejec; /// Ejecutado Acumulado al trimestre
    //       $valor[3]=$ejecucion; /// Cumplimiento al trimestre
    //       $valor[4]=$cumplimiento_gestion; /// Cumplimiento a la Gestion
    //   }

    //   return $valor; 
    // }


    //// Lista de Actividades Priorizados a la Operacion 2027 (Gasto Corriente)
    public function get_mis_form4_priorizados_x_oregional($detalle_oregional){
      $tabla='<script src = "'.base_url().'mis_js/programacion/programacion/tablas.js"></script>';
      $list_form4_priorizados=$this->model_producto->lista_form4_priorizados_x_form2($detalle_oregional[0]['or_id']);

      $tabla.='
          <div class="table-responsive">
            <table id="dt_basic" class="table table-bordered" border=0.2 style="width:100%;">
              <thead>
                <tr style="font-size: 11px;" bgcolor="#eceaea" >
                  <th style="width:1%;height:10px; text-align: center">#</th>
                  <th style="width:4%; text-align: center"><b>PROGRAMA</b></th>
                  <th style="width:8%; text-align: center"><b>GASTO CORRIENTE</b></th>
                  <th style="width:8%; text-align: center"><b>UNIDAD RESPONSABLE</b></th>
                  <th style="width:2%; text-align: center">COD. ACT.</th>
                  <th style="width:8%; text-align: center">ACTIVIDAD</th>
                  <th style="width:7%; text-align: center">RESPONSABLE</th>
                  <th style="width:8%; text-align: center">INDICADOR</th>
                  <th style="width:3%; text-align: center">META</th>
                  <th style="width:2.3%; text-align: center">ENE.</th>
                  <th style="width:2.3%; text-align: center">FEB.</th>
                  <th style="width:2.3%; text-align: center">MAR.</th>
                  <th style="width:2.3%; text-align: center">ABR.</th>
                  <th style="width:2.3%; text-align: center">MAY.</th>
                  <th style="width:2.3%; text-align: center">JUN.</th>
                  <th style="width:2.3%; text-align: center">JUL.</th>
                  <th style="width:2.3%; text-align: center">AGO.</th>
                  <th style="width:2.3%; text-align: center">SEPT.</th>
                  <th style="width:2.3%; text-align: center">OCT.</th>
                  <th style="width:2.3%; text-align: center">NOV.</th>
                  <th style="width:2.3%; text-align: center">DIC.</th>
                  <th style="width:7%; text-align: center">MEDIO DE VERIFICACIÓN</th>
                </tr>
              </thead>
              <tbody>';
              $nro=0;$suma_meta=0;
              foreach($list_form4_priorizados as $row){
                $nro++;
                $suma_meta=$suma_meta+$row['prod_meta'];
                $tp_indi='';
                if($row['indi_id']==2){
                  $tp_indi='%';
                }
                $tabla.='
                <tr style="font-size: 10.5px;">
                  <td style="width:1%;">'.$nro.'</td>
                  <td style="width:4%;" bgcolor="#f9fdfc"><b>'.$row['aper_programa'].' '.$row['aper_proyecto'].' '.$row['aper_actividad'].'</b></td>
                  <td style="width:8%;" bgcolor="#f9fdfc"><b>'.$row['tipo'].' '.$row['proy_nombre'].' '.$row['abrev'].'</b></td>
                  <td style="width:8%;" bgcolor="#f9fdfc"><b>'.$row['tipo_subactividad'].' '.$row['com_componente'].'</b></td>
                  <td style="width:2%; text-align:center; font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>
                  <td style="width:8%;">'.$row['prod_producto'].'</td>
                  <td style="width:7%;">'.$row['prod_unidades'].'</td>
                  <td style="width:8%;">'.$row['prod_indicador'].'</td>
                  <td style="width:3%; text-align:center; font-size: 12px;"><b>'.round($row['prod_meta'],2).''.$tp_indi.'</b></td>';
                  for ($i=1; $i <=12; $i++) { 
                    $tabla.='
                    <td style="width:2.3; font-size: 11px;"><b>
                    P : '.round($row['m'.$i],2).''.$tp_indi.'<br>
                    E : '.round($row['ejec_m'.$i],2).''.$tp_indi.'
                    </b></td>';
                  }
                $tabla.='
                  <td style="width:7%;">'.$row['prod_fuente_verificacion'].'</td>
                </tr>';
              }
        $tabla.='
              </tbody>
              <tr>
                <td style="height:10px;"colspan=8 align=right><b>META PRIORIZADO : </b></td>
                <td style="width:2%; height:5px; font-size:13px;" align=right><b>'.round($suma_meta,2).''.$tp_indi.'</b></td>
                <td colspan=13></td>
              </tr>
            </table>
        </div>';
      return $tabla;
    }





    //// Lista de Proyectos Priorizados a la Operacion 2027 (Inversion todos de la regional)
    public function get_mis_Inversion_priorizados_x_oregional($or_id){
      $tabla='<script src = "'.base_url().'mis_js/programacion/programacion/tablas.js"></script>';
      $list_form4_priorizados=$this->model_producto->list_Inversion_todos_alineados_a_formN2($or_id);

      $tabla.='
          <div class="table-responsive">
            <table id="dt_basic" class="table table-bordered" border=0.2 style="width:100%;">
              <thead>
                <tr style="font-size: 11px;" bgcolor="#eceaea" >
                  <th style="width:1%;height:10px; text-align: center">#</th>
                  <th style="width:10%; text-align: center"><b>CÓDIGO SISÍN</b></th>
                  <th style="width:15%; text-align: center"><b>INVERSIÓN</b></th>
                  <th style="width:2.2%; text-align: center">ENE.</th>
                  <th style="width:2.2%; text-align: center">FEB.</th>
                  <th style="width:2.2%; text-align: center">MAR.</th>
                  <th style="width:2.2%; text-align: center">ABR.</th>
                  <th style="width:2.2%; text-align: center">MAY.</th>
                  <th style="width:2.2%; text-align: center">JUN.</th>
                  <th style="width:2.2%; text-align: center">JUL.</th>
                  <th style="width:2.2%; text-align: center">AGO.</th>
                  <th style="width:2.2%; text-align: center">SEPT.</th>
                  <th style="width:2.2%; text-align: center">OCT.</th>
                  <th style="width:2.2%; text-align: center">NOV.</th>
                  <th style="width:2.2%; text-align: center">DIC.</th>
                </tr>
              </thead>
              <tbody>';
              $nro=0;$suma_meta=0;
              foreach($list_form4_priorizados as $row){
                $nro++;
                $tabla.='
                <tr style="font-size: 10.5px;">
                  <td style="width:1%;">'.$nro.'</td>
                  <td style="width:10%;" bgcolor="#f9fdfc"><b>'.$row['proy_sisin'].'</b></td>
                  <td style="width:15%;" bgcolor="#f9fdfc"><b>'.$row['proy_nombre'].'</b></td>';
                  for ($i=1; $i <=12; $i++) { 
                    $tabla.='
                    <td style="width:2.2; font-size: 11px;"><b>
                    P : '.number_format($row['mes'.$i], 2, ',', '.').'<br>
                    E : '.number_format($row['e'.$i], 2, ',', '.').'
                    </b></td>';
                  }
                $tabla.='
                </tr>';
              }
        $tabla.='
              </tbody>
            </table>
        </div>';
      return $tabla;
    }



//// ======== REPORTE FORMULARIO N2
  //// Cabecera Reporte form2
  public function cabecera_form2($regional,$tp){ 
    $tp_titulo='';
    if($tp==1){
      $tp_titulo='EVALUACI&Oacute;N';
    } 
    
    $tabla='';
       $tabla.='
        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
          <tr style="border: solid 0px;">              
              <td style="width:70%;height: 2%">
                  <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                      <tr style="font-size: 15px;font-family: Arial;">
                          <td style="width:45%;height: 20%;">&nbsp;&nbsp;<b>'.$this->session->userData('entidad').'</b></td>
                      </tr>
                      <tr>
                          <td style="width:50%;height: 20%;font-size: 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</td>
                      </tr>
                  </table>
              </td>
              <td style="width:30%; height: 2%; font-size: 8px;text-align:right;">
                '.date("d").' de '.$this->mes[ltrim(date("m"), "0")]. " de " . date("Y").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
              </td>
          </tr>
        </table>
        <hr>
        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
            <tr style="border: solid 0px black; text-align: center;">
                <td style="width:10%; text-align:center;">
                </td>
                <td style="width:80%; height: 5%">
                  <table align="center" border="0" style="width:100%;">
                    <tr style="font-size: 23px;font-family: Arial;">
                      <td style="height: 32%; text-align:center"><b>PLAN OPERATIVO ANUAL - GESTI&Oacute;N '.$this->gestion.'</b></td>
                    </tr>
                    <tr style="font-size: 20px;font-family: Arial;">
                      <td style="height: 5%; text-align:center">OPERACIONES - '.strtoupper($regional[0]['dep_departamento']).'</td>
                    </tr>
                  </table>
                </td>
                <td style="width:10%; text-align:center;">
                </td>
            </tr>
        </table>
        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
            <tr style="border: solid 0px;">              
                <td style="width:70%;">
                </td>
                <td style="width:30%; height: 3%">
                  <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                    <tr style="font-size: 15px;font-family: Arial;">
                      <td align=center style="width:100%;height: 10%;"><b>'.$tp_titulo.' FORMULARIO SPO N° 2 </b></td>
                    </tr>
                  </table>
                </td>
            </tr>
        </table>
        
        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
           <tr>
              <td style="width:2%;"></td>
              <td style="width:96%;height: 1%;">
                <hr>
              </td>
              <td style="width:2%;"></td>
          </tr>
        </table>';

    return $tabla;
  }


 /*------- CABECERA REPORTE SEGUIMIENTO POA (GRAFICO)------*/
  // function cabecera_reporte_grafico($regional){
  //   $tabla='';

  //   $tabla.='
  //     <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
  //       <tr style="border: solid 0px;">              
  //           <td style="width:70%;height: 2%">
  //               <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
  //                   <tr style="font-size: 15px;font-family: Arial;">
  //                       <td style="width:45%;height: 20%;">&nbsp;&nbsp;<b>'.$this->session->userData('entidad').'</b></td>
  //                   </tr>
  //                   <tr>
  //                       <td style="width:50%;height: 20%;font-size: 8px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</td>
  //                   </tr>
  //               </table>
  //           </td>
  //           <td style="width:30%; height: 2%; font-size: 8px;text-align:right;">
  //             '.date("d").' de '.$this->mes[ltrim(date("m"), "0")]. " de " . date("Y").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  //           </td>
  //       </tr>
  //     </table>
  //     <hr>
  //     <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
  //         <tr style="border: solid 0px black; text-align: center;">
  //             <td style="width:10%; text-align:center;">
  //             </td>
  //             <td style="width:80%; height: 5%">
  //               <table align="center" border="0" style="width:100%;">
  //                 <tr style="font-size: 23px;font-family: Arial;">
  //                   <td style="height: 32%; text-align:center"><b>PLAN OPERATIVO ANUAL - GESTI&Oacute;N '.$this->gestion.'</b></td>
  //                 </tr>
  //                 <tr style="font-size: 20px;font-family: Arial;">
  //                   <td style="height: 5%; text-align:center">OPERACIONES - '.strtoupper($regional[0]['dep_departamento']).'</td>
  //                 </tr>
  //               </table>
  //             </td>
  //             <td style="width:10%; text-align:center;">
  //             </td>
  //         </tr>
  //     </table>';

  //   return $tabla;
  // }

  //// Detalle Lista Operaciones Regional form 2 2027
  public function lista_form2_reporte_x_regional($dep_id){ 
    $tabla='';
    $lista_ogestion=$this->model_objetivoregion->list_form2_regional_x_regional($dep_id);

    $tabla.='
    <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;" align=center>
      <thead>
        <tr style="font-size: 6.7px;" bgcolor="#eceaea" align=center>
          <th style="width:0.9%;height:20px;">#</th>
          <th style="width:2%;"><b>COD. ACP.</b></th>
          <th style="width:2%;"><b>COD. OPE.</b></th>
          <th style="width:10%;">OPERACI&Oacute;N REGIONAL '.$this->gestion.'</th>
          <th style="width:10%;">PRODUCTO</th>
          <th style="width:10%;">RESULTADO</th>
          <th style="width:10%;">INDICADOR</th>
          <th style="width:20%;">UNIDADES RESPONSABLES</th>
          <th style="width:4%;">META</th>
          
          <th style="width:5.2%;">I TRIM.</th>
          <th style="width:5.2%;">II. TRIM.</th>
          <th style="width:5.2%;">III. TRIM.</th>
          <th style="width:5.2%;">IV. TRIM.</th>
          <th style="width:9%;">MEDIO DE VERIFICACI&Oacute;N</th>
        </tr>
      </thead>
      <tbody>';
    $nro=0;$monto_total=0; $suma1=0;
    foreach($lista_ogestion as $row){
      $uresponsables=$this->model_objetivoregion->get_urep_oregional($row['or_id']);
      $por='';
      if($row['or_tp']==1){
        $por='%';
      }
      $nro++; $bgcolor='';
      if($row['or_priorizado']==1){
        $bgcolor='#ecf7f5';
      }

      $tabla.='
      <tr style="font-size: 6.5px;" bgcolor='.$bgcolor.'>
        <td style="width:0.9%; height:18px;" align=center>'.$nro.'</td>
        <td style="width:2%; font-size: 10px;" align="center"><b>'.$row['og_codigo'].'</b></td>
        <td style="width:2%; font-size: 10px;" align="center"><b>'.$row['or_codigo'].'</b></td>
        <td style="width:10%;text-align: justify;">'.$row['or_objetivo'].'</td>
        <td style="width:10%;text-align: justify;">'.$row['or_producto'].'</td>
        <td style="width:10%;text-align: justify;">'.$row['or_resultado'].'</td>
        <td style="width:10%;text-align: justify;">'.$row['or_indicador'].'</td>
        <td style="width:20%;text-align: justify;">';
          $uresp='';
          foreach($uresponsables as $row_ue){
            $uresp=$uresp.' '.$row_ue['tipo'].' '.$row_ue['proy_nombre'].' '.$row_ue['abrev'].' - ';
          }
        $tabla.=''.$uresp.'
        </td>
        <td style="width:4%; font-size: 9px;" align=center><b>'.round($row['or_meta'],2).''.$por.'</b></td>';
        $campo_prog='trm'; $campo_ejec='e_trm'; 
        if($row['or_tp']==1){
          $campo_prog='trm_porc'; $campo_ejec='trm_porc_ejec';
        }


          for ($i=1; $i <=4 ; $i++) { 
          $tabla.='
            <td style="width:5.2%;" align=center>
              <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:90%;" align=center>
                <tr>
                  <td style="width:50%; height: 1.5%; font-size: 8px;"><b>P.</b></td>
                  <td style="width:50%;font-size: 8.5px;" align=right><b>'.round($row[$campo_prog.$i],2).''.$por.'</b></td>
                </tr>
                <tr>
                  <td style="width:50%; height: 1.5%; font-size: 8px;"><b>E.</b></td>
                  <td style="font-size: 8.5px;" align=right><b>'.round($row[$campo_ejec.$i],2).''.$por.'</b></td>
                </tr>
              </table>
            </td>';
          }
        $tabla.='
        <td style="width:9%;text-align: justify;">'.$row['or_verificacion'].'</td>
      </tr>';
    }
    $tabla.='
      </tbody>
    </table>';

    return $tabla;
  }

  //// Lista Evaluacion de Operaciones Regional form 2 para el Reporte 2027
  public function lista_form2_evaluacion_reporte_x_regional($dep_id){ 
    $tabla='';
    $lista_form2=$this->model_objetivoregion->list_form2_regional_x_regional($dep_id); /// List Operaciones x Regional

    $tabla.='
    <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;" align=center>
      <thead>
        <tr style="font-size: 6.7px;" bgcolor="#eceaea" align=center>
          <th style="width:0.9%;height:20px;">#</th>
          <th style="width:2.1%;"><b>COD. ACP.</b></th>
          <th style="width:2.1%;"><b>COD. OPE.</b></th>
          <th style="width:11.5%;">OPERACI&Oacute;N REGIONAL</th>
          <th style="width:11.5%;">PRODUCTO</th>
          <th style="width:11.5%;">RESULTADO</th>
          <th style="width:11%;">INDICADOR</th>
          <th style="width:4%;">META</th>
          <th style="width:5.3%;">I TRIM.</th>
          <th style="width:5.3%;">II. TRIM.</th>
          <th style="width:5.3%;">III. TRIM.</th>
          <th style="width:5.3%;">IV. TRIM.</th>
          <th style="width:4%;"></th>
          <th style="width:4.5%;">(%) CUMP.<br>TRIM.</th>
          <th style="width:4.5%;">(%) CUMP.<br>'.$this->gestion.'</th>
          <th style="width:8%;">MEDIO DE VERIFICACI&Oacute;N</th>
        </tr>
      </thead>
      <tbody>';
    $nro=0;$monto_total=0; $suma1=0;
    foreach($lista_form2 as $row){
      $por='';
      if($row['or_tp']==1){
        $por='%';
      }
      $nro++; $bgcolor='';
      if($row['or_priorizado']==1){
        $bgcolor='#ecf7f5';
      }

      if($row['trm'.$this->tmes]==0){
        $semaforo='<img src="'.getcwd().'/assets/ifinal/blanco.JPG" class="img-responsive" style="width:70%; height:3.5%;" align=center />';
      }
      else{
        $semaforo='<img src="'.getcwd().'/assets/ifinal/rojo1.JPG" class="img-responsive" style="width:70%; height:3.5%;" align=center />';
        if($row['trm'.$this->tmes]>50 & $row['trm'.$this->tmes]<=80){
          $semaforo='<img src="'.getcwd().'/assets/ifinal/amarillo.JPG" class="img-responsive" style="width:70%; height:3.5%;" align=center />';
        }
        elseif($row['trm'.$this->tmes]>80 & $row['trm'.$this->tmes]<=100){
          $semaforo='<img src="'.getcwd().'/assets/ifinal/verde.JPG" class="img-responsive" style="width:70%; height:3.5%;" align=center />';
        }
      }
      
      $tabla.='
      <tr style="font-size: 6.5px;" bgcolor='.$bgcolor.'>
        <td style="width:0.9%; height:18px;" align=center>'.$nro.'</td>
        <td style="width:2.1%; font-size: 10px;" align="center"><b>'.$row['og_codigo'].'</b></td>
        <td style="width:2.1%; font-size: 10px;" align="center"><b>'.$row['or_codigo'].'</b></td>
        <td style="width:11.5%;text-align: justify;">'.$row['or_objetivo'].'</td>
        <td style="width:11.5%;text-align: justify;">'.$row['or_producto'].'</td>
        <td style="width:11.5%;text-align: justify;">'.$row['or_resultado'].'</td>
        <td style="width:11%;text-align: justify;">'.$row['or_indicador'].'</td>
        <td style="width:4%; font-size: 9px;" align=center><b>'.round($row['or_meta'],2).''.$por.'</b></td>';
        $campo_prog='trm'; $campo_ejec='e_trm'; 
        if($row['or_tp']==1){
          $campo_prog='trm_porc'; $campo_ejec='trm_porc_ejec';
        }


          for ($i=1; $i <=4 ; $i++) { 
          $tabla.='
            <td style="width:5.3%;" align=center>
              <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:90%;" align=center>
                <tr>
                  <td style="width:50%; height: 1.5%; font-size: 8px;"><b>P.</b></td>
                  <td style="width:50%;font-size: 8.5px;" align=right><b>'.round($row[$campo_prog.$i],2).''.$por.'</b></td>
                </tr>
                <tr>
                  <td style="width:50%; height: 1.5%; font-size: 8px;"><b>E.</b></td>
                  <td style="font-size: 8.5px;" align=right><b>'.round($row[$campo_ejec.$i],2).''.$por.'</b></td>
                </tr>
              </table>
            </td>';
          }
        $tabla.='
        <td style="width:4%; text-align:center">'.$semaforo.'</td>
        <td style="font-family:Arial;font-size: 11px;width:4%;" align=right><b>'.round($row['porc_cump_'.$this->tmes],2).'%</b></td>
        <td style="font-family:Arial;font-size: 11px;width:4%;" align=right><b>'.round($row['trm_porc_ejec'.$this->tmes],2).'%</b></td>
        <td style="width:8%;text-align: justify;">'.$row['or_verificacion'].'</td>
      </tr>';
    }
    $tabla.='
      </tbody>
    </table>
    <br>

    <table cellpadding="0" cellspacing="0" class="tabla" border=0 style="width:50%;" align=left>
      <tbody>
        <tr>
          <td colspan=2>CUMPLIMIENTO TRIMESTRAL ACUMULADO</td>
        </tr>
        <tr>
          <td colspan=2><hr></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/blanco.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td style="width:30%;">BLANCO:<b> Sin programación al Trimestre</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/rojo1.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td style="width:30%;">ROJO:<b> Entre 0% y 50%</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/amarillo.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td>AMARILLO:<b> Entre 51% y 80%</b></td>
        </tr>
        <tr>
          <td style="width:10%; height:12px;" align=center><img src="'.getcwd().'/assets/ifinal/verde.JPG" class="img-responsive" style="width:20%; height:80%;" align=center /></td>
          <td>VERDE:<b> Entre 81% y 100%</b></td>
        </tr>
        <tr>
          <td colspan=2><hr></td>
        </tr>
      </tbody>
    </table>';

    return $tabla;
  }


  //// Pie Regional form 2
  public function pie_form2($regional){ 
    $tabla='';
    $tabla.='
  
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:97.5%;" align="center">
          <tr>
            <td style="width: 33.3%;">
              <table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                <tr>';
                  if($regional[0]['dep_id']!=10){
                      $tabla.='<td style="width:100%;height:1.4%"><b>JEFATURA DE SERVICIOS GENERALES</b></td>';
                  }
                  else{
                      $tabla.='<td style="width:100%;height:1.4%"><b>GERENCIA ADMINISTRATIVA FINANCIERA</b></td>';
                  }
                $tabla.='
                </tr>
                <tr>
                    <td align=center><br><br><br><br><br><br><b>FIRMA</b></td>
                </tr>
              </table>
            </td>
            <td style="width: 33.3%;">
              <table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                <tr>';
                  if($regional[0]['dep_id']!=10){
                      $tabla.='<td style="width:100%;height:1.4%"><b>JEFATURA MEDICA</b></td>';
                  }
                  else{
                      $tabla.='<td style="width:100%;height:1.4%"><b>GERENCIA DE SERVICIOS DE SALUD</b></td>';
                  }
                $tabla.='
                </tr>
                <tr>
                  <td align=center><br><br><br><br><br><br><b>FIRMA</b></td>
                </tr>
              </table>
            </td>
            <td style="width: 33.3%;">
              <table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                <tr>';
                  if($regional[0]['dep_id']!=10){
                    $tabla.='<td style="width:100%; height:1.4%"><b>ADMINISTRADOR REGIONAL</b></td>';
                  }
                  else{
                    $tabla.='<td style="width:100%; height:1.4%"><b>GERENCIA GENERAL</b></td>';
                  }
                $tabla.='
                </tr>
                <tr>
                  <td align=center><br><br><br><br><br><br><b>FIRMA</b></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="width: 33%; height:18px;text-align: left">
              POA - '.$this->session->userdata('gestion').". ".$this->session->userdata('rd_poa').'
            </td>
            <td style="width: 33%; text-align: center">
              '.$this->session->userdata('sistema').'
            </td>
            <td style="width: 33%; text-align: right">
              '.$this->session->userdata('funcionario').' - pag. [[page_cu]]/[[page_nb]]
            </td>
          </tr>
      </table>';

    return $tabla;
  }

  //// Pie Regional act priorizados
  public function pie_form4_priorizados(){ 
    $tabla='';
    $tabla.='
  
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:97.5%;" align="center">
          <tr>
            <td style="width: 33%; height:18px;text-align: left">
              POA - '.$this->session->userdata('gestion').". ".$this->session->userdata('rd_poa').'
            </td>
            <td style="width: 33%; text-align: center">
              '.$this->session->userdata('sistema').'
            </td>
            <td style="width: 33%; text-align: right">
              '.$this->session->userdata('funcionario').' - pag. [[page_cu]]/[[page_nb]]
            </td>
          </tr>
      </table>';

    return $tabla;
  }


  //// Matriz lista de cumplimiento de Operaciones por Regional ANUAL
  // public function matriz_cumplimiento_operaciones_regional($dep_id){
  //   $lista_ogestion=$this->model_objetivoregion->list_form2_regional_x_regional($dep_id);
  //    for ($i=0; $i <count($lista_ogestion); $i++) { 
  //     for ($j=0; $j <5 ; $j++) { 
  //       $matriz[$i][$j]=0;
  //     } 
  //    }

  //    $nro=0;
  //    foreach($lista_ogestion as $row){
  //     $calificacion=$this->calificacion_trimestral_acumulado_x_oregional($row['or_id'],$this->tmes,$row['or_tp'],$row['tp_meta']);
        
  //       $matriz[$nro][0]=$row['og_codigo']; /// cod OG
  //       $matriz[$nro][1]=$row['or_codigo']; /// cod OR
  //       $matriz[$nro][2]=$row['or_objetivo']; /// Descripcion
  //       $matriz[$nro][3]=$calificacion[3]; /// Cumplimiento al Trimestre
  //       $matriz[$nro][4]=$calificacion[4]; /// Cumplimiento a la Gestion

  //       $nro++;
  //    }

  //    return $matriz;
  // }






  //// Matriz lista de cumplimiento de Operaciones Institucional a la Gestion
  // public function matriz_cumplimiento_operaciones_institucional(){
  //   $lista_ogestion=$this->model_objetivogestion->get_list_ogestion_por_regional_institucional();
  //    for ($i=0; $i <count($lista_ogestion); $i++) { 
  //     for ($j=0; $j <4 ; $j++) { 
  //       $matriz[$i][$j]=0;
  //     } 
  //    }

  //    $nro=0;
  //    foreach($lista_ogestion as $row){
  //     $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional($row['og_codigo'],$row['or_codigo']); /// Temporalidad Ejecutado
  //     $ejec_form2_institucional=0;
  //     if(count($get_trm_ejec)!=0){
  //       $ejec_form2_institucional=$get_trm_ejec[0]['ejecutado'];
  //     }  


  //       $matriz[$nro][0]=$row['og_codigo']; /// cod OG
  //       $matriz[$nro][1]=$row['or_codigo']; /// cod OR
  //       $matriz[$nro][2]=$row['programado_total']; /// Programado Total
  //       $matriz[$nro][3]=$ejec_form2_institucional; /// ejecutado Total
  //       $ejecutado=0;
  //       if($row['programado_total']!=0){
  //         $ejecutado=round((($ejec_form2_institucional/$row['programado_total'])*100),2);
  //       }
  //       $matriz[$nro][4]=$ejecutado; /// ejecutado Total %

  //       $nro++;
  //    }

  //    return $matriz;
  // }


  //// Matriz lista de cumplimiento de Operaciones Institucional al Trimestre
  // public function matriz_cumplimiento_operaciones_institucional_al_trimestre(){
  //   $lista_ogestion=$this->model_objetivogestion->get_list_ogestion_por_regional_institucional_al_trimestre($this->tmes);
  //    for ($i=0; $i <count($lista_ogestion); $i++) { 
  //     for ($j=0; $j <4 ; $j++) { 
  //       $matriz[$i][$j]=0;
  //     } 
  //    }

  //    $nro=0;
  //    foreach($lista_ogestion as $row){
  //     $get_trm_ejec=$this->model_objetivoregion->get_ejec_form2_institucional_al_trimestre($row['og_codigo'],$row['or_codigo'],$this->tmes); /// Temporalidad Ejecutado
  //     $ejec_form2_institucional=0;
  //     if(count($get_trm_ejec)!=0){
  //       $ejec_form2_institucional=$get_trm_ejec[0]['ejecutado'];
  //     }  


  //       $matriz[$nro][0]=$row['og_codigo']; /// cod OG
  //       $matriz[$nro][1]=$row['or_codigo']; /// cod OR
  //       $matriz[$nro][2]=$row['programado_total']; /// Programado Total
  //       $matriz[$nro][3]=$ejec_form2_institucional; /// ejecutado Total
  //       $ejecutado=0;
  //       if($row['programado_total']!=0){
  //         $ejecutado=round((($ejec_form2_institucional/$row['programado_total'])*100),2);
  //       }
  //       $matriz[$nro][4]=$ejecutado; /// ejecutado Total %

  //       $nro++;
  //    }

  //    return $matriz;
  // }



 /*-- CALIFICACION TRIMESTRAL POR OBJETIVO REGIONAL (INSTITUCIONAL)--*/
/*    public function calificacion_trimestral_acumulado_x_oregional_institucional($og_codigo,$or_codigo,$programado_total){
      $valor = array( '1' => '0','2' => '0','3' => '0','4' => '0');

      if(count($this->model_objetivoregion->verif_temporalidad_oregional($or_id))!=0){
        $suma_total_prog=0; $suma_prog=0; $suma_ejec=0;
        
        //// Suma total programado por operacion
        $prog_total=$this->model_objetivoregion->get_trm_temporalidad_prog_total_oregional($or_id);
        if(count($prog_total)!=0){
          $suma_total_prog=$prog_total[0]['total_prog'];
        }
        ///-----



        for ($i=1; $i <=$trimestre; $i++) {
          $get_trm=$this->model_objetivoregion->get_trm_temporalidad_prog_oregional($or_id,$i); /// Temporalidad Programado
          $get_trm_ejec=$this->model_objetivoregion->get_trm_temporalidad_ejec_oregional($or_id,$i); /// Temporalidad Ejecutado

          if(count($get_trm)!=0){
            $suma_prog=$suma_prog+$get_trm[0]['pg_fis']; 
          }

          if(count($get_trm_ejec)!=0){
            $suma_ejec=$suma_ejec+$get_trm_ejec[0]['ejec_fis'];
          }

          $ejecucion=0;
          if($suma_ejec!=0){
            $ejecucion=round((($suma_ejec/$suma_prog)*100),2);
          }

          $cumplimiento_gestion=0;
          if($suma_total_prog!=0){
            $cumplimiento_gestion=round((($suma_ejec/$suma_total_prog)*100),2);
          }
        }

        //  $text=strval( $suma_prog);
          $valor[1]=$suma_prog; /// Programado Acumulado al trimestre
          $valor[2]=$suma_ejec; /// Ejecutado Acumulado al trimestre
          $valor[3]=$ejecucion; /// Cumplimiento al trimestre
          $valor[4]=$cumplimiento_gestion; /// Cumplimiento a la Gestion
      }

      return $valor; 
    }*/


  /*-------- MENU -----*/
    function menu($mod){
        $enlaces=$this->menu_modelo->get_Modulos($mod);
        for($i=0;$i<count($enlaces);$i++){
          $subenlaces[$enlaces[$i]['o_child']]=$this->menu_modelo->get_Enlaces($enlaces[$i]['o_child'], $this->session->userdata('user_name'));
        }

        $tabla ='';
        for($i=0;$i<count($enlaces);$i++){
            if(count($subenlaces[$enlaces[$i]['o_child']])>0){
                $tabla .='<li>';
                    $tabla .='<a href="#">';
                        $tabla .='<i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>';    
                        $tabla .='<ul>';    
                            foreach ($subenlaces[$enlaces[$i]['o_child']] as $item) {
                            $tabla .='<li><a href="'.base_url($item['o_url']).'">'.$item['o_titulo'].'</a></li>';
                        }
                        $tabla .='</ul>';
                $tabla .='</li>';
            }
        }

        return $tabla;
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


    public function get_mes($mes_id){
      $mes[1]='ENERO';
      $mes[2]='FEBRERO';
      $mes[3]='MARZO';
      $mes[4]='ABRIL';
      $mes[5]='MAYO';
      $mes[6]='JUNIO';
      $mes[7]='JULIO';
      $mes[8]='AGOSTO';
      $mes[9]='SEPTIEMBRE';
      $mes[10]='OCTUBRE';
      $mes[11]='NOVIEMBRE';
      $mes[12]='DICIEMBRE';

      $dias[1]='31';
      $dias[2]='28';
      $dias[3]='31';
      $dias[4]='30';
      $dias[5]='31';
      $dias[6]='30';
      $dias[7]='31';
      $dias[8]='31';
      $dias[9]='30';
      $dias[10]='31';
      $dias[11]='30';
      $dias[12]='31';

      $valor[1]=$mes[$mes_id];
      $valor[2]=$dias[$mes_id];

      return $valor;
    }
}
?>