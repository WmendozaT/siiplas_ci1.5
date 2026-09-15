<?php
 ////// CONTROLADOR PARA LOS CITES DE MODIFICACION POA DE FORM 4 Y FORM 5
class Cmodificaciones extends CI_Controller {  
    public $rol = array('1' => '3','2' => '6','3' => '4'); 
    public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
            $this->load->model('Users_model','',true);
            if($this->rolfun($this->rol)){ 
            $this->load->library('pdf2');
            $this->load->model('menu_modelo');
            $this->load->model('programacion/model_proyecto');
            $this->load->model('modificacion/model_modificacion');
            $this->load->model('programacion/model_faseetapa');
          
            $this->load->model('programacion/model_producto');
            $this->load->model('programacion/model_componente');
            $this->load->model('programacion/insumos/minsumos');
            $this->load->model('mestrategico/model_mestrategico');
            $this->load->model('mantenimiento/model_ptto_sigep');
            $this->load->model('modificacion/model_modrequerimiento'); /// Gestion 2020
            $this->load->model('modificacion/model_modfisica'); /// Gestion 2020
            $this->load->library('security');
            $this->gestion = $this->session->userData('gestion'); /// Gestion
            $this->fun_id = $this->session->userData('fun_id'); /// Fun id
            $this->rol_id = $this->session->userData('rol_id'); /// Rol Id
            $this->adm = $this->session->userData('adm');
            $this->dist = $this->session->userData('dist');
            $this->dist_tp = $this->session->userData('dist_tp');
            $this->tp_adm = $this->session->userData('tp_adm');
            $this->conf_mod_ope = $this->session->userData('conf_mod_ope');
            $this->conf_mod_req = $this->session->userData('conf_mod_req');
            $this->load->library('modificacionpoa');

            }else{
                redirect('admin/dashboard');
            }
        }
        else{
          $this->session->sess_destroy();
          redirect('/','refresh');
        }
    }

    /*--- Lista de Poas Aprobados ---*/
    public function list_poas_aprobados(){
      $data['menu']=$this->menu(3); //// genera menu
      $data['proyectos']='';
      $data['gasto_corriente']='';
      
      $data['base']='<input name="base" type="hidden" value="'.base_url().'">';
      $data['proyectos']=$this->modificacionpoa->list_pinversion_aprobados(); // Aprobados
      $data['gasto_corriente']=$this->modificacionpoa->list_unidades_es(4); // Aprobados
      $data['rep_listado_modificacionespoa']='';

      if($this->tp_adm==1){
        $data['rep_listado_modificacionespoa']='<center><a href="'.site_url("").'/mod/exportar_mod_requerimientos_institucional" target=_blank class="btn btn-default" title="EXPORTAR MODIFICACION FORM. N5"><img src="'.base_url().'assets/Iconos/page_excel.png" WIDTH="20" HEIGHT="20"/>&nbsp;EXPORTAR MOD. FORM 5</a></center>';
      }
      
/*      $requerimientos=$this->model_modrequerimiento->lista_requerimientos(5367);

      foreach($requerimientos as $row){
        if(round($row['ins_costo_total'],2)==round($row['ins_monto_certificado'],2)){
          $update_com = array(
                'ins_activo' => 1
              );
              $this->db->where('ins_id', $row['ins_id']);
              $this->db->update('insumos', $update_com);
          }
        }*/
        

      $this->load->view('admin/modificacion/list_poa_aprobados',$data);
    }


    /*--- EXPORTAR CONSOLIDADO DE MODIFICACION POA INSTITUCIONAL ---*/
    public function consolidado_modificacion_requerimientos_institucional(){
      date_default_timezone_set('America/Lima');
      $fecha = date("d-m-Y H:i:s");
      $titulo='INSTITUCIONAL';
      $tabla=$this->modificacionpoa->items_modificados_form5_historial_nacional();

      header('Content-type: application/vnd.ms-excel');
      header("Content-Disposition: attachment; filename=Consolidado_Modificacion_Requerimiento_".$titulo."_$fecha.xls"); //Indica el nombre del archivo resultante
      header("Pragma: no-cache");
      header("Expires: 0");
      echo "";
      ini_set('max_execution_time', 0); 
      ini_set('memory_limit','3072M');
      echo $tabla;
    }









  /*--- LISTA DE CITES FORM 4-FORM 5 (2026) ---*/
    public function lista_cites($proy_id){
    $data['menu']=$this->menu(3); //// genera menu
    $UniOrg=$this->model_proyecto->get_UnidadOrganizacional($proy_id);
    if(count($UniOrg)!=0){
      $tabla='';
      $tabla.='
      <style>
      .table1{
            display: inline-block;
            width:100%;
            max-width:1550px;
            overflow-x: scroll;
            }
      table{font-size: 9px;
            width: 100%;
            max-width:1550px;;
            overflow-x: scroll;
            }
            th{
              padding: 1.4px;
              text-align: center;
              font-size: 9px;
              background-color: #fafafa;
            }
            td{
              font-size: 9px;
            }
    </style>
    '.$this->modificacionpoa->loading_siguiente_pagina().'
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <section id="widget-grid" class="well">
               <h1> '.$UniOrg[0]['aper_programa'].' '.$UniOrg[0]['aper_proyecto'].' '.$UniOrg[0]['aper_actividad'].' - '.$UniOrg[0]['proy_nombre'].' '.$UniOrg[0]['abrev'].'</h1>;
              </section>
            </article>
            <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <div class="well well-sm well-light">
                <h3>HISTORIAL DE MODIFICACIONES POA - GESTI&Oacute;N '.$this->gestion.'</h3>
                <div class="row">
                  <article class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                  <div class="jarviswidget jarviswidget-color-darken" >
                    <header>
                      <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                      <h2 class="font-md"><strong>FORMULARIO N° 5 (Requerimientos)</strong></h2>  
                    </header>
                    <div>
                      <div class="widget-body no-padding">
                        <table id="dt_basic2" class="table table-bordered" style="width:100%;">
                          <thead>
                            <tr style="height:40px;">
                              <th style="width:1%;">#</th>
                              <th style="width:10%;">NRO CITE</th>
                              <th style="width:10%;">FECHA CITE </th>
                              <th style="width:10%;">C&Oacute;DIGO </th>
                              <th style="width:15%;">UNIDAD RESPONSABLE</th>
                              <th style="width:5%;"></th>
                              <th style="width:5%;"></th>
                            </tr>
                          </thead>
                          <tbody>
                            '.$this->list_cites_generados($proy_id,1).' 
                          </tbody>
                          <tr bgcolor="#fafafa">
                            <td></td>
                            <td colspan="5"><b>CONSOLIDADO MODIFICACIONES</b></td>
                            <td><center><a href="'.base_url().'index.php/mod/consolidado_mod_requerimiento/'.$UniOrg[0]['proy_id'].'" class="btn btn-default" title="EXPORTAR CONSOLIDADO MODIFICACIONES.XLS" target="_blank"><img src="'.base_url().'assets/ifinal/excel.jpg'.'" width="30" height="30"/></a></center></td>
                          </tr>
                        </table>
                      </div>
                    </div>
                  </div>
                  </article>
                  <article class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                  <div class="jarviswidget jarviswidget-color-darken" >
                    <header>
                      <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                      <h2 class="font-md"><strong>FORMULARIO N° 4</strong></h2>  
                    </header>
                    <div>
                      <div class="widget-body no-padding">
                        <table id="dt_basic1" class="table table-bordered" style="width:100%;">
                          <thead>
                            <tr style="height:40px;">
                              <th style="width:1%;">#</th>
                              <th style="width:10%;">NRO CITE</th>
                              <th style="width:10%;">FECHA CITE </th>
                              <th style="width:10%;">C&Oacute;DIGO </th>
                              <th style="width:20%;">UNIDAD RESPONSABLE</th>
                              <th style="width:5%;"></th>
                              <th style="width:5%;"></th>
                            </tr>
                          </thead>
                          <tbody>
                            '.$this->list_cites_generados($proy_id,2).'
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  </article>
                  <article class="col-xs-12 col-sm-12 col-md-12 col-lg-4">
                  <div class="jarviswidget jarviswidget-color-darken" >
                    <header>
                      <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                        <h2 class="font-md"><strong>TECHO</strong></h2>  
                      </header>
                    <div>
                      <div class="widget-body no-padding">
                        <table id="dt_basic3" class="table table-bordered" style="width:100%;">
                          <thead>
                            <tr style="height:40px;">
                              <th style="width:1%;">#</th>
                              <th style="width:10%;">NRO CITE</th>
                              <th style="width:10%;">FECHA CITE </th>
                              <th style="width:10%;">TIPO DE MODIFICACION </th>
                              <th style="width:5%;"></th>
                              <th style="width:5%;"></th>
                            </tr>
                          </thead>
                          <tbody>
                            '.$this->list_cites_generados($proy_id,3).'
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </div>
                  </article>
                </div>
              </div>
            </article>';

            $data['vista']=$tabla;
            $this->load->view('admin/modificacion/list_cites', $data);

      }
      else{
        redirect(site_url("").'/mod/list_top');
      }
      
    }



    /*----- UPDATE ESTADO ACTIVO DE LA MODIFICACION ------*/
    function update_activo_modificacion($cite_id){
      $update_cite= array(
        'cite_activo' => 1
      );
      $this->db->where('cite_id', $cite_id);
      $this->db->update('cite_mod_requerimientos', $this->security->xss_clean($update_cite));
    }



    /*--- LISTA DE MODIFCACIONES (FORMULARIO 4 - FORMULARIO 5 - TECHO PRESUPUESTARIO) 2027 ---*/
    public function list_cites_generados($proy_id,$tp){
      $tabla='';
      // === LIST CITES REQUERIMIENTOS 
      if($tp==1){
        $cites=$this->model_modrequerimiento->list_cites_requerimientos_proy($proy_id);
        if(count($cites)!=0){
          $nro=0;
          foreach($cites  as $cit){
            $color='';
            $codigo='<font color=blue><b>'.$cit['cite_codigo'].'</b></font>';
            if($cit['cite_estado']==0){
              $color='#fbdfdf';
              $codigo='<font color=red><b>SIN CÓDIGO DE MODIFICACIÓN</b></font>';
            }

              $nro++;
              $tabla .='<tr bgcolor='.$color.'>';
                $tabla .='<td align="center">'.$nro.'</td>';
                $tabla .='<td><b>'.$cit['cite_nota'].'</b></td>';
                $tabla .='<td align="center">'.date('d/m/Y',strtotime($cit['cite_fecha'])).'</td>';
                $tabla .='<td>'.$codigo.'</td>';
                $tabla .='<td><font size=1.5px;><b>'.$cit['tipo_subactividad'].' '.$cit['serv_descripcion'].'</b></font></td>';
                if($cit['tipo_modificacion']==0){
                  $tabla .='<td align=center><a href="javascript:abreVentana(\''.site_url("").'/mod/rep_mod_financiera/'.$cit['cite_id'].'\');" title="REPORTE CITES - MODIFICACION DE REQUERIMIENTOS"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/></a></td>';
                }
                else{
                  $tabla .='<td align=center><a href="javascript:abreVentana(\''.site_url("").'/mod/rep_mod_financiera/'.$cit['cite_id'].'\');" title="REPORTE CITES - MODIFICACION DE REQUERIMIENTOS POR REVERSION"><img src="'.base_url().'assets/ifinal/rep_pdf.png" WIDTH="33" HEIGHT="28"/><br><b>REVERSIÓN<br>POA</b></a></td>';
                }
                
                $tabla .='<td align="center">';
                  if($this->conf_mod_req==1 || $this->tp_adm==1){

                    $tabla .='
                        <a href="'.base_url().'index.php/mod/update_cite/'.$cit['cite_id'].'" class="btn-modificar" title="MODIFICAR CITE">
                        <img src="'.base_url().'assets/ifinal/form1.jpg" width="30" height="30"/></a><br>';
                  }
                $tabla .='</td>';

              $tabla .='</tr>';
            }
        }
      }
      // ----- LIST CITES FORM 4
      elseif($tp==2){
        $cites=$this->model_modfisica->list_cites_modpoa_form4($proy_id);
        
        $nro=0;
          foreach($cites as $cit){
              $nro++;
              $citeId = $cit['cite_id'];
              $urlReporte = site_url("mod/reporte_modfis/".$citeId);
              $urlModificar = base_url("index.php/mod/lista_mod_form4/".$citeId);
              
              $tabla .= '<tr>';
              $tabla .= '<td align="center">'.$nro.'</td>';
              $tabla .= '<td><b>'.htmlspecialchars($cit['cite_nota']).'</b></td>';
              $tabla .= '<td align="center">'.date('d/m/Y', strtotime($cit['cite_fecha'])).'</td>';
              $tabla .= '<td><b>'.htmlspecialchars($cit['cite_codigo']).'</b></td>';
              $tabla .= '<td>'.htmlspecialchars($cit['com_componente']).'</td>';
              $tabla .= '<td align="center"><a href="javascript:abreVentana(\''.$urlReporte.'\');" title="REPORTE"><img src="'.base_url('assets/ifinal/requerimiento.png').'" width="25" height="25"/></a></td>';
              $tabla .= '<td align="center">';
              
              if($this->conf_mod_ope == 1 || $this->tp_adm == 1){
              $tabla .= '
              <a href="'.$urlModificar.'" class="btn-modificar" data-id="'.$citeId.'" title="MODIFICAR CITE">
                <img src="'.base_url('assets/ifinal/form1.jpg').'" width="30" height="30"/>
              </a>';
              }
              
              $tabla .= '</td>';
              $tabla .= '</tr>';
          }
      }
      // ----- LIST DE CITES TECHO PRESUPUESTARIO
      else{
        $cites=$this->model_modificacion->list_cites_techo($proy_id);
        if(count($cites)!=0){
            $nro=0;
              foreach($cites  as $cit){
                $nro++;
                $tp_mod='<b>MODIFICACION RESPUESTARIA</b>';
                if($cit['tp']==1){
                  $tp_mod='<b>REVERSION DE SALDOS</b><br>'.$cit['observacion'].'';
                }
                $tabla .='<tr>';
                  $tabla .='<td align="center">'.$nro.'</td>';
                  $tabla .='<td><b>'.$cit['cppto_cite'].'</b></td>';
                  $tabla .='<td align="center"><b>'.date('d/m/Y',strtotime($cit['cppto_fecha'])).'</b></td>';
                  $tabla .='<td align="center">'.$tp_mod.'</td>';
                  $tabla .='<td align=center><a href="javascript:abreVentana(\''.site_url("").'/mod/rep_mod_techo/'.$cit['cppto_id'].'\');"  title="REPORTE CITES - MODIFICACIÓN TECHO PRESUPUESTARIO"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/></a></td>';
                  $tabla .='<td align=center>';
                    if($this->fun_id==399){
                      $tabla.='<a href="'.base_url().'index.php/mod/techo/'.$cit['cppto_id'].'" title="MODIFICAR TECHO PRESUPUESTARIO">MOD. TECHO</a>';
                    }
                  $tabla.='</td>';
                $tabla .='</tr>';
              }
          }
      }

      return $tabla;
    }


    //// Historial de Modificacion de Requerimientos por Unidad Organizacional 2027
    public function Xls_historial_mod_formN5_UnidadOrganizacional($proy_id){
        // 1. Configuración y ampliación drástica de recursos del servidor
        set_time_limit(1200);             // 20 minutos de ejecución interna
        ini_set('memory_limit', '1024M'); // 1 GB de memoria RAM asignada

        $historial_form5=$this->model_modificacion->get_lista_historial_modPoa_formN5_UnidadOrganizacional($proy_id); /// historial por Unidad Organizacional

        if (!empty($historial_form5)) {
          // Estructuración formal del nombre del archivo según el estándar del PEI
          $nombre_archivo = "Historial_Modificacion_form5_".$this->gestion. ".xls";

          // 3. Limpieza radical de buffers fantasmas para blindar el binario
          if (ob_get_length()) {
              ob_clean();
          }
          ob_start();

          // 4. Protocolo de cabeceras HTTP rígidas para forzar descarga directa
          header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
          header("Content-Disposition: attachment; filename=\"" . $nombre_archivo . "\"");
          header("Cache-Control: max-age=0, no-cache, must-revalidate");
          header("Pragma: public");

          // 5. Inyección de la directiva BOM UTF-8 para proteger acentos y Ñs en Windows
          echo "\xEF\xBB\xBF";

          // 6. Construcción idéntica de la sábana de datos en una sola variable string
          $tabla = '';
          $tabla .= '
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <table border="0.5" cellpadding="4" cellspacing="0">
            <thead>
              <tr style="background-color: #1a237e; color: #ffffff; font-weight: bold; height: 35px; font-family: Arial, sans-serif; font-size: 10px; text-transform: uppercase;">
                <th style="background-color: #1a237e; color: #ffffff;">COD. DA.</th>
                <th style="background-color: #1a237e; color: #ffffff;">COD. UE.</th>
                <th style="background-color: #1a237e; color: #ffffff;">APER. PROG.</th>
                <th style="background-color: #1a237e; color: #ffffff;">APER. PROY.</th>
                <th style="background-color: #1a237e; color: #ffffff;">APER. ACT.</th>
                <th style="background-color: #1a237e; color: #ffffff;">UNIDAD ORGANIZACIONAL</th>
                <th style="background-color: #1a237e; color: #ffffff;">UNIDAD OPERATIVA</th>

                <th style="background-color: #1a237e; color: #ffffff;">CITE ID</th>
                <th style="background-color: #1a237e; color: #ffffff;">NOTA CITE</th>
                <th style="background-color: #1a237e; color: #ffffff;">FECHA CITE</th>
                <th style="background-color: #1a237e; color: #ffffff;">FECHA REGISTRO</th>
                <th style="background-color: #1a237e; color: #ffffff;">CODIGO MODIFICACIÓN POA</th>
                <th style="background-color: #1a237e; color: #ffffff;">ESTADO MODIFICACIÓN</th>
                <th style="background-color: #1a237e; color: #ffffff;">CITE OBSERVACIÓN</th>
                <th style="background-color: #1a237e; color: #ffffff;">TIPO DE REGISTRO</th>

                <th style="background-color: #1a237e; color: #ffffff;">COD. ACT.</th>
                <th style="background-color: #1a237e; color: #ffffff;">PARTIDA</th>
                <th style="background-color: #1a237e; color: #ffffff;">DETALLE REQUERIMIENTO</th>
                <th style="background-color: #1a237e; color: #ffffff;">UNIDAD MEDIDA</th>
                <th style="background-color: #1a237e; color: #ffffff;">CANTIDAD</th>
                <th style="background-color: #1a237e; color: #ffffff;">PRECIO</th>
                <th style="background-color: #1a237e; color: #ffffff;">COSTO TOTAL</th>
                
                <!-- Meses Programados -->
                <th style="background-color: #2e7d32; color: #ffffff;">ENE.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">FEB.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">MAR.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">ABR.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">MAY.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">JUN.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">JUL.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">AGO.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">SEP.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">OCT.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">NOV.</th>
                <th style="background-color: #2e7d32; color: #ffffff;">DIC.</th>
                <th style="background-color: #1a237e; color: #ffffff;">OBSERVACIÓN</th>
                <th style="background-color: #1a237e; color: #ffffff;">TIPO DE ACCIÓN</th>
                <th style="background-color: #1a237e; color: #ffffff;">RESPONSABLE</th>
              </tr>
            </thead>
            <tbody style="font-family: Arial, sans-serif; font-size: 9px;">';

            foreach ($historial_form5 as $row) {
                $tabla .= '<tr style="vertical-align: middle;">';
                    // Formato de texto estricto (vnd.ms-excel.numberformat:@) para amarrar ceros a la izquierda
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . (!empty($row['dep_cod']) ? strtoupper($row['dep_cod']) : '0') . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . (!empty($row['dist_cod']) ? strtoupper($row['dist_cod']) : '0') . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . $row['aper_programa'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . $row['aper_proyecto'] . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . $row['aper_actividad'] . '</td>';
                    
                    $tabla .= '<td>' . htmlspecialchars($row['tipo'] . ' ' . $row['proy_nombre'] . ' - ' . $row['abrev'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td>' . htmlspecialchars($row['tipo_subactividad'] . ' ' . $row['com_componente'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td style="text-align: center;">' . intval($row['cite_id']) . '</td>';
                    $tabla .= '<td>' . htmlspecialchars($row['cite_nota'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: center;">' . $row['cite_fecha'] . '</td>';
                    $tabla .= '<td style="text-align: center;">' . $row['cite_creacion'] . '</td>';

                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center; font-weight: bold;">' . htmlspecialchars($row['cite_codigo'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: center;">' . htmlspecialchars($row['estado_modificacion'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td>' . htmlspecialchars(!empty($row['cite_observacion']) ? $row['cite_observacion'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: center;">' . htmlspecialchars($row['tipo_modificacion_poa'], ENT_QUOTES, 'UTF-8') . '</td>';

                    // 🛠️ SINCRO: 'prod_cod' mapeado desde tu última función relacional SQL de Postgres
                    $tabla .= '<td style="text-align: center; font-weight: bold; color: #1e3a8a;">' . intval($row['prod_cod']) . '</td>';
                    $tabla .= '<td style="vnd.ms-excel.numberformat:@; text-align: center;">' . $row['par_codigo'] . '</td>';
                    
                    // 🌟 REPARADO: Protección perimetral htmlspecialchars contra caracteres comerciales rotos
                    $tabla .= '<td>' . htmlspecialchars($row['ins_detalle'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: center;">' . htmlspecialchars($row['ins_unidad_medida'], ENT_QUOTES, 'UTF-8') . '</td>';
                    
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\';">' . floatval($row['ins_cant_requerida']) . '</td>';
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\';">' . floatval($row['ins_costo_unitario']) . '</td>';
                    $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; font-weight: bold;">' . floatval($row['ins_costo_total']) . '</td>';

                    // 🌟 REPARADO CORE: Corrección sintáctica del text-align y limpieza del style de meses
                    for ($i = 1; $i <= 12; $i++) {
                        $val_mes = isset($row['mes' . $i]) ? floatval($row['mes' . $i]) : 0.00;
                        $estilo_mes = ($val_mes > 0) ? 'background-color: #f0fdf4; font-weight: bold; color: #15803d;' : 'color: #94a3b8;';
                        
                        $tabla .= '<td style="text-align: right; mso-number-format:\'#,##0.00\'; ' . $estilo_mes . '">' . ($val_mes > 0 ? $val_mes : '0') . '</td>';
                    }
                    
                    $tabla .= '<td>' . htmlspecialchars(!empty($row['ins_observacion']) ? $row['ins_observacion'] : 'S/R', ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: center; font-weight: bold;">' . htmlspecialchars($row['tipo_de_accion'], ENT_QUOTES, 'UTF-8') . '</td>';
                    $tabla .= '<td style="text-align: left; text-transform: uppercase;">' . htmlspecialchars($row['fun_usuario'], ENT_QUOTES, 'UTF-8') . '</td>';
                $tabla .= '</tr>';
            }

          $tabla .= '
            </tbody>
          </table>';

          // Imprimimos la variable que contiene el HTML y vaciamos el buffer
          echo $tabla;
          ob_end_flush();
          exit;
      } else {
          echo "<h3>⚠️ Error SIIPLAS: No se encontraron registros de modificacion cargados para los criterios seleccionados.</h3>";
      }
    }









    /*------------------------- TEMPORALIDAD PRODUCTOS ----------------------------*/
    // public function temporalizacion_prod($prod_id,$gestion){
    //     $prod=$this->model_producto->get_producto_id($prod_id); /// Producto Id
    //     $programado=$this->model_producto->producto_programado($prod_id,$gestion); /// Producto Programado

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
    //       $prog[1][$i]=0;
    //       $prog[2][$i]=0;
    //       $prog[3][$i]=0;
    //     }

    //     $pa=0;
    //     if(count($programado)!=0){
    //         for ($i=1; $i <=12 ; $i++) { 
    //           $prog[1][$i]=$programado[0][$m[$i]];
    //         } 
    //     }
        
    //     $tr_return = '';
    //     $tr_return .= '<table class="table table-bordered">
    //                     <thead>
    //                     <tr >
    //                         <th style="width:6%;" bgcolor="#6ec7bc"><font color=#fff></font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>ENE.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>FEB.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>MAR.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>ABR.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>MAY.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>JUN.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>JUL</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>AGO.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>SEPT.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>OCT.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>NOV.</font></th>
    //                         <th style="width:7%;" bgcolor="#6ec7bc"><font color=#fff>DIC.</font></th>
    //                     </tr>
    //                     </thead>
    //                     <tbody>
    //                       <tr >
    //                       <td>P.</td>';
    //                       for($i = 1 ;$i<=12 ;$i++){
    //                         $tr_return .= '<td>'.$prog[1][$i].'</td>';
    //                       }
    //                       $tr_return .= '
    //                       </tr>
    //                     </tbody>
    //                 </table>';
    //     return $tr_return;
    // }

    /*==============================================================================*/




    /*------ TEMPORALIZACION DE PRODUCTOS (nose esta tomando encuenta lb) ------*/
    // public function temporalizacion_productos($prod_id){
    //   $producto = $this->model_producto->get_producto_id($prod_id);
    //   $prod_prog= $this->model_producto->producto_programado($prod_id,$this->gestion);//// Temporalidad Programado

    //   $mp[1]='enero';
    //   $mp[2]='febrero';
    //   $mp[3]='marzo';
    //   $mp[4]='abril';
    //   $mp[5]='mayo';
    //   $mp[6]='junio';
    //   $mp[7]='julio';
    //   $mp[8]='agosto';
    //   $mp[9]='septiembre';
    //   $mp[10]='octubre';
    //   $mp[11]='noviembre';
    //   $mp[12]='diciembre';

    //   for ($i=1; $i <=12 ; $i++) { 
    //     $matriz[1][$i]=0; /// Programado
    //   }
      
    //   $pa=0; $ea=0;
    //   if(count($prod_prog)!=0){
    //     for ($i=1; $i <=12 ; $i++) { 
    //       $matriz[1][$i]=$prod_prog[0][$mp[$i]];
    //     }
    //   }

    //   return $matriz;
    // }
    /*-------------------------------- GENERAR MENU -------------------------------------*/
    function menu($mod){
      $enlaces=$this->menu_modelo->get_Modulos($mod);
      for($i=0;$i<count($enlaces);$i++) {
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
    /*--------------------------------------------------------------------------------*/
    function rolfun($rol){
      $valor=false;
      for ($i=1; $i <=count($rol) ; $i++) { 
        $data = $this->Users_model->get_datos_usuario_roles($this->session->userdata('fun_id'),$rol[$i]);
        if(count($data)!=0){
          $valor=true;
          break;
        }
      }
      return $valor;
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
}