<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

///// EVALUACION POA REGIONAL, DISTRITAL 
class Modificacionpoa extends CI_Controller{
    public function __construct (){
        parent::__construct();
        $this->load->model('programacion/model_proyecto');
        $this->load->model('mantenimiento/model_entidad_tras');
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('programacion/model_faseetapa');
        $this->load->model('modificacion/model_modrequerimiento');
        $this->load->model('programacion/insumos/minsumos');
        $this->load->model('ejecucion/model_seguimientopoa');
        $this->load->model('programacion/model_componente');
        $this->load->model('ejecucion/model_notificacion');
        $this->load->model('programacion/model_producto');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('mantenimiento/model_configuracion');
        $this->load->model('modificacion/model_modfisica'); /// Gestion 2020
        $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020

        $this->load->model('modificacion/model_modificacion');

        $this->load->model('reporte_eval/model_evalunidad'); /// Model Evaluacion Unidad
        $this->load->model('reporte_eval/model_evalinstitucional'); /// Model Evaluacion Institucional
        $this->load->model('ejecucion/model_certificacion');

        $this->load->model('menu_modelo');
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        //$this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        $this->dep_id = $this->session->userData('dep_id');
        //$this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->fun_id = $this->session->userData('fun_id');
       // $this->tp_adm = $this->session->userData('tp_adm');
        $this->verif_mes=$this->session->userData('mes_actual');
        $this->resolucion=$this->session->userdata('rd_poa');
        $this->tp_adm = $this->session->userData('tp_adm');
        $this->conf_mod_ope = $this->session->userData('conf_mod_ope');
        $this->conf_mod_req = $this->session->userData('conf_mod_req');
        $this->mes = $this->mes_nombre();
    }


     /*--- Modal Para Migrar Requerimientos x Componente 2027 ---*/
  public function modal_migracion_form5x_modpoa($cite_id){
    $tabla='';
    $tabla.='
    <div class="modal fade" id="modal_importar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
          <div class="modal-dialog" id="dialog_subir">
              <div class="modal-content" style="border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none; overflow: hidden;">
                  
                  <!-- CABECERA DEL COMPONENTE -->
                  <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                      <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                          <span aria-hidden="true">&times;</span>
                      </button>
                      <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                          <i class="fa fa-upload text-primary"></i> Importar Requerimientos GLOBAL
                      </h4>
                  </div>

                  <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                  <div class="modal-body" style="padding: 25px; background: #ffffff;">
                      <div class="text-center" style="margin-bottom: 20px;">
                        <h5 style="font-weight: bold; text-transform: uppercase; color: #555;">Subir archivo Excel (.xls, .xlsx)</h5>
                        <p  style="font-size:12px; color: blue;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                        <p  style="font-size:15px; color: red;" class="text-muted"><b>NOTA !!!!<b/> No es Necesario que el archivo este en formato .CSV</p>
                      </div>

                      <!-- Vista previa de columnas (Corregido: Concatenación nativa base_url) -->
                      <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                         
                          <img src="' . base_url('assets/img/img_migracion/migracion_form5.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                      </div>

                      <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                      <form action="' . site_url('modificaciones/cmod_insumo/valida_add_requerimientos') . '" method="post" enctype="multipart/form-data" id="form_subir_requerimientoss" autocomplete="off" style="padding:0; background:transparent;">
                          <input name="cite_id" value="'.$cite_id.'" type="hidden" > 
                          <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                              <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                              
                              <div class="input-group input-group-sm">
                                  <span class="input-group-btn">
                                      <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                          <i class="fa fa-folder-open"></i> Examinar...
                                      </button>
                                      
                                     <input id="archivo_f5" accept=".xlsx, .xls" name="archivo" 
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

                          <!-- Animación Pre-Loader de la Planilla -->
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


    /*---- Lista de Unidades / Establecimientos de Salud (2023) -----*/
    public function list_unidades_es($proy_estado){
        $unidades=$this->model_proyecto->list_unidades(4,$proy_estado);
        
        $titulo_ppto='TECHO PPTO.'; /// Administrador
        if($this->tp_adm!=1){
          $titulo_ppto='REV. PPTO.'; /// Responsables POA
        }

        $tabla='';
        $tabla.='
        <table id="dt_basic" class="table table-bordered" style="width:100%;">
          <thead>
            <tr style="font-size:10.5px;">
              <th style="width:1%;" bgcolor="#fafafa">#</th>
              <th style="width:3%; text-align:center;" bgcolor="#fafafa" title="MODIFICACION FORMULARIO N° 4">MOD. FORM. N° 4</th>
              <th style="width:3%; text-align:center;" bgcolor="#fafafa" title="MODIFICACION FORMULARIO N° 5">MOD. FORM. N° 5</th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="HISTORIAL DE CITES"></th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="REVERSION DE SALDOS CERTIFICADOS">REVERSION DE SALDOS</th>';
              if($this->tp_adm==1){
                $tabla.='<th style="width:5%; text-align:center;" bgcolor="#fafafa" title="TECHO PRESUPUESTARIO"></th>';
              }
              $tabla.='
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="APERTURA PROGRAM&Aacute;TICA">CATEGORIA PROGRAM&Aacute;TICA '.$this->gestion.'</th>
              <th style="width:20%; text-align:center;" bgcolor="#fafafa" title="DESCRIPCI&Oacute;N">GASTO CORRIENTE</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="NIVEL">ESCALON</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="NIVEL">NIVEL</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="TIPO DE ADMINISTRACIÓN">TIPO DE ADMINISTRACI&Oacute;N</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="UNIDAD ADMINISTRATIVA">UNIDAD ADMINISTRATIVA</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="UNIDAD EJECUTORA">UNIDAD EJECUTORA</th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="ESTADO"></th>
            </tr>
          </thead>
          <tbody>';
            $nro=0;
            foreach($unidades as $row){
              $color='#ccefcc';
              $estado='APROBADO';

              if($row['proy_estado']==4){
                
                /*$link=site_url("").'/mod/cite_techo/'.$row['proy_id']; /// link de modificacion presupuestaria
                if($this->tp_adm!=1){
                  $link=site_url("").'/mod/add_ppto_reversion/'.$row['proy_id']; /// link de reversion de presupuestos
                }*/

                $nro++;
                $tabla.='
                <tr style="font-size:10px;">
                  <td align=center><b>'.$nro.'</b></td>
                  <td align=center>';
                    if($this->conf_mod_ope==1 || $this->tp_adm==1){
                      $tabla.='<a href="'.site_url("").'/mod/list_componentes/'.$row['proy_id'].'" title="MODIFICAR ACTIVIDADES" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_edit.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px;"><b>FORM. N°4</b></div></a>';
                    }
                    else{
                      $tabla.='<div style="color:red; font-size:9px"><b>NO DISPONIBLE</b></div>';
                    }
                    $tabla.='
                  </td>
                  <td align=center>';
                    if($this->conf_mod_req==1  || $this->tp_adm==1){
                      $tabla.='<a href="'.site_url("").'/mod/form5/'.$row['proy_id'].'" title="MODIFICAR REQUERIMIENTOS" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_edit.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px;"><b>FORM. N°5</b></div></a>';
                    }
                    else{
                      $tabla.='<div style="color:red; font-size:9px"><b>NO DISPONIBLE</b></div>';
                    }
                  $tabla.='
                  </td>
                  <td align=center>
                    <a href="'.site_url("").'/mod/list_cites/'.$row['proy_id'].'" title="LISTA DE CITES GENERADOS POR MODIFICACI&Oacute;N" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_side_list.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:blue"><b>LISTA CITES</b></div></a>
                  </td>
                  <td align=center>
                    <a href="'.site_url("").'/mod/add_ppto_reversion/'.$row['proy_id'].'" title="REVERSION DE SALDOS" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/money_add.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:green"><b>SALDOS POA</b></div></a>
                  </td>';
                  if($this->tp_adm==1){
                    $tabla.='
                    <td align=center bgcolor="green">
                      <a href="'.site_url("").'/mod/cite_techo/'.$row['proy_id'].'" title="TECHO PRESUPUESTARIO" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/money_dollar.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:green"><b>TECHO PPTO.</b></div></a>
                    </td>';
                  }
                  $tabla.='
                  <td><center>'.$row['aper_programa'].''.$row['aper_proyecto'].''.$row['aper_actividad'].'</center></td>
                  <td><b>'.$row['tipo'].' '.$row['act_descripcion'].' '.$row['abrev'].'</b></td>
                  <td></td>
                  <td>'.$row['nivel'].'</td>
                  <td>'.$row['tipo_adm'].'</td>
                  <td>'.strtoupper($row['dep_departamento']).'</td>
                  <td>'.strtoupper($row['dist_distrital']).'</td>
                  <td><center><b>'.$estado.'</b></center></td>
                </tr>';
              }
            }
          $tabla.='
          </tbody>
        </table>';
      return $tabla;
    }

    /*---- Lista de Proyectos de Inversion Aprobados -----*/
    public function list_pinversion_aprobados(){
      $proyectos=$this->model_proyecto->listado_proyectos_inversion_aprobados_segun_tipo_responsable();
        $titulo_ppto='TECHO PPTO.'; /// Administrador
        if($this->tp_adm!=1){
          $titulo_ppto='REV. PPTO.'; /// Responsables POA
        }

      $tabla='';
      $tabla.='
        <table id="dt_basic1" class="table1 table-bordered" style="width:100%;">
          <thead>
            <tr style="font-size:10.5px;">
              <th style="width:1%; height:70px;" bgcolor="#fafafa">#</th>
              <th style="width:4%; text-align:center;" bgcolor="#fafafa" title="MODIFICACION FORMULARIO N° 4">MOD. FORM. N° 4</th>
              <th style="width:4%; text-align:center;" bgcolor="#fafafa" title="MODIFICACION FORMULARIO N° 5">MOD. FORM. N° 5</th>
              <th style="width:4%; text-align:center;" bgcolor="#fafafa" title="HISTORIAL DE CITES"></th>
              <th style="width:4%; text-align:center;" bgcolor="#fafafa" title="REVERSION DE SALDOS">REVERSION DE SALDOS</th>';
              if($this->tp_adm==1){
                $tabla.='<th style="width:4%; text-align:center;" bgcolor="#fafafa" title="TECHO PRESUPUESTARIO"></th>';
              }
              $tabla.='
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="LISTA DE CITES GENERADOS">CATEGORIA PROGRAM&Aacute;TICA '.$this->gestion.'</th>
              <th style="width:10%; text-align:center;" bgcolor="#fafafa" title="APERTURA PROGRAM&Aacute;TICA">PROYECTO DE INVERSI&Oacute;N</th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="UNIDAD ADMINISTRATIVA">UNIDAD ADMINISTRATIVA</th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="UNIDAD EJECUTORA">UNIDAD EJECUTORA</th>
              <th style="width:5%; text-align:center;" bgcolor="#fafafa" title="FASE - ETAPA">DESCRIPCI&Oacute;N FASE</th>
              <th style="width:15%;" bgcolor="#fafafa"></th>
              <th style="width:15%;" bgcolor="#fafafa"></th>
            </tr>
          </thead>
          <tbody>';
            $nro=0;
            foreach($proyectos as $row){
              /*$link=site_url("").'/mod/cite_techo/'.$row['proy_id']; /// link de modificacion presupuestaria
                if($this->tp_adm!=1){
                  $link=site_url("").'/mod/add_ppto_reversion/'.$row['proy_id']; /// link de reversion de presupuestos
                }*/
              $nro++;
              $tabla.='
              <tr style="font-size:10px;">
                <td style="height:70px;width:1%;" title='.$row['proy_id'].'></td>
                <td style="width:4%;" align=center>';
                  if($this->conf_mod_ope==1){
                    $tabla.='<a href="'.site_url("").'/mod/list_componentes/'.$row['proy_id'].'" title="MODIFICAR ACTIVIDADES" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_edit.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px;"><b>FORM. N°4</b></div></a>';
                  }
                  else{
                    $tabla.='<div style="color:red; font-size:9px"><b>NO DISPONIBLE</b></div>';
                  }
                  $tabla.='
                </td>
                <td style="width:4%;" align=center>';
                  if($this->conf_mod_req==1){
                    $tabla.='<a href="'.site_url("").'/mod/form5/'.$row['proy_id'].'" title="MODIFICAR REQUERIMIENTOS" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_edit.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px;"><b>FORM. N°5</b></div></a>';
                  }
                  else{
                    $tabla.='<div style="color:red; font-size:9px"><b>NO DISPONIBLE</b></div>';
                  }
                $tabla.='
                </td>
                <td style="width:4%;" align=center>
                  <a href="'.site_url("").'/mod/list_cites/'.$row['proy_id'].'" title="LISTA DE CITES GENERADOS POR MODIFICACI&Oacute;N" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/application_side_list.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:blue"><b>LISTA CITES</b></div></a>
                </td>
                <td style="width:4%;" align=center>
                  <a href="'.site_url("").'/mod/add_ppto_reversion/'.$row['proy_id'].'" title="REVERSION DE SALDOS" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/money_add.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:green"><b>SALDOS POA</b></div></a>
                </td>';
                  if($this->tp_adm==1){
                    $tabla.='
                    <td style="width:4%;" align=center bgcolor="green">
                      <a href="'.site_url("").'/mod/cite_techo/'.$row['proy_id'].'" title="TECHO PRESUPUESTARIO" target="_blank" class="btn btn-default"><img src="'.base_url().'assets/Iconos/money_dollar.png" WIDTH="30" HEIGHT="30"/><br><div style="font-size: 9px; color:green"><b>TECHO PPTO.</b></div></a>
                    </td>';
                  }
                  $tabla.='
                <td style="width:5%;"><center>'.$row['proy'].'</center></td>
                <td style="width:10%;">'.$row['proyecto'].'</td>
                <td style="width:5%;">'.strtoupper($row['dep_departamento']).'</td>
                <td style="width:5%;">'.strtoupper($row['dist_distrital']).'</td>
                <td style="width:5%;">'.strtoupper($row['pfec_descripcion']).'</td>
                <td style="width:15%;">'.strtoupper($row['proy_obj_general']).'</td>
                <td style="width:15%;">'.strtoupper($row['proy_obj_especifico']).'</td>
              </tr>';
            }
          $tabla.='
          </tbody>
        </table>';
      
      return $tabla;
    }


    /*------ TITULO CABECERA (2026) (FORMULARIO N° 4 Y 5)-----*/
    public function titulo_cabecera($cite,$tp){
      if($cite[0]['cite_estado']!=0){
        $estado_cite='<font color=blue><b>'.$cite[0]['cite_codigo'].'</b></font>';
      }
      else{
        $estado_cite=' <font color="red"><b>DEBE CERRAR LA MODIFICACI&Oacute;N !!</b></font>';
      }

      $tabla='';
      $tabla.='<h1><b> CITE Nro. : <small>'.$cite[0]['cite_nota'].'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;FECHA : <small>'.date('d/m/Y',strtotime($cite[0]['cite_fecha'])).'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;C&Oacute;DIGO : '.$estado_cite.'</b></h1>';

      
      if($cite[0]['tp_id']==1){ /// Proyecto de Inversion
        $tabla.=' <h1> <b>PROYECTO : </b><small>'.$cite[0]['proy_sisin'].' - '.$cite[0]['proy_nombre'].'</small> / <b>UNIDAD RESPONSABLE : </b><small>'.$cite[0]['serv_cod'].' '.$cite[0]['tipo_subactividad'].' '.$cite[0]['com_componente'].'</small></h1>';
      }
      else{ /// Gasto Corriente
        $tabla.='<h1 title='.$cite[0]['aper_id'].'><small>'.$cite[0]['aper_programa'].' '.$cite[0]['aper_proyecto'].' '.$cite[0]['aper_actividad'].' - '.$cite[0]['tipo'].' '.$cite[0]['act_descripcion'].' '.$cite[0]['abrev'].'</small> / <small>'.$cite[0]['tipo_subactividad'].' '.$cite[0]['com_componente'].'</small></h1>';
      }

      //// ------ Monto Presupuesto Programado-Asignado POA
        if($cite[0]['tipo_modificacion']==0){
          $monto=$this->ppto($cite);
          $tabla.='<h1><b> PPTO. ASIGNADO : <small>'.number_format($monto[1], 2, ',', '.').'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;PPTO PROGRAMADO : <small>'.number_format($monto[2], 2, ',', '.').'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;SALDO : <small>'.number_format($monto[3], 2, ',', '.').'</small></b></h1>';
        }
        else{
          $monto=$this->ppto_revertido($cite);
          $tabla.='<h1><b> PPTO. ASIGNADO (REVERTIDO) : <small>'.number_format($monto[1], 2, ',', '.').'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;PPTO PROGRAMADO (REVERTIDO) : <small>'.number_format($monto[2], 2, ',', '.').'</small>&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;SALDO : <small>'.number_format($monto[3], 2, ',', '.').'</small></b></h1>';
        }

        if($tp==1){
          if($monto[3]>0){
            $tabla.='
            <a role="menuitem" tabindex="-1" href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default" title="NUEVO REGISTRO">
              <img src="'.base_url().'assets/Iconos/add.png" WIDTH="20" HEIGHT="20"/>&nbsp;<b>NUEVO REGISTRO (FORM. N 5)</b>
            </a>
            <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR ARCHIVO EXCEL">
              <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>SUBIR ARCHIVO EXCEL.xls </b>
            </a>';
          }
        }
        
      return $tabla;
    }


    /*--- MONTO PRESUPUESTO ASIGNADO - PROGRAMADO (TOTAL UNIDAD)(2023) ---*/
    public function ppto($proyecto){
      $monto_a=0;$monto_p=0;$monto_saldo=0;
      $monto_asig=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($proyecto[0]['aper_id'],1);
      $monto_prog=$this->model_ptto_sigep->suma_ptto_UnidadOrganizacional($proyecto[0]['aper_id'],2);
      if(count($monto_asig)!=0){
        $monto_a=$monto_asig[0]['monto'];
      }
      if(count($monto_prog)!=0){
        $monto_p=$monto_prog[0]['monto'];
      }

      $monto[1]=$monto_a; /// Monto Asignado
      $monto[2]=$monto_p; /// Monto Programado
      $monto[3]=($monto_a-$monto_p); /// Saldo

      return $monto;
    }

    /*--- MONTO PRESUPUESTO ASIGNADO - PROGRAMADO (TOTAL UNIDAD REVERTIDO)(2023) ---*/
    public function ppto_revertido($proyecto){
      $monto_a=0;$monto_p=0;$monto_saldo=0;
      $monto_asig=$this->model_ptto_sigep->suma_ptto_revertido_total_unidad($proyecto[0]['aper_id'],1); /// asig revertido
      $monto_prog=$this->model_ptto_sigep->suma_ptto_revertido_total_unidad($proyecto[0]['aper_id'],2); /// prog revertido
      if(count($monto_asig)!=0){
        $monto_a=$monto_asig[0]['ppto_revertido'];
      }
      if(count($monto_prog)!=0){
        $monto_p=$monto_prog[0]['poa_revertido'];
      }

      $monto[1]=$monto_a; /// Monto Asignado Revertido
      $monto[2]=$monto_p; /// Monto Programado Revertido
      $monto[3]=($monto_a-$monto_p); /// Saldo

      return $monto;
    }


     /*------ LISTA FORMULARIO N° 4 (2026) (VISTA) ------*/
    public function mod_mis_formulariosN4($cite){
      if($cite[0]['por_id']==0){
        $form4 = $this->model_producto->lista_form4_x_unidadresponsable($cite[0]['com_id']); // Lista de Actividades
      }
      else{
        $form4 = $this->model_producto->get_lista_form4_uresp_consolidado_programa_bolsas($cite[0]['com_id']); // Lista de Actividades que estan en programas bolsa
      }

      
      $tabla ='';
      $tabla .='
        <input type="hidden" name="base" value="'.base_url().'">
        <table id="dt_basic" class="table table-bordered">
          <thead>
            <tr class="modo1">
              <th style="width:1%; text-align=center"><b>#</b></th>
              <th style="width:1%; text-align=center"><b>E/B</b></th>
              <th style="width:2%;"><b>COD. ACP.</b></th>
              <th style="width:2%;"><b>COD. OPE.</b></th>
              <th style="width:2%;"><b>COD. ACT.</b></th>
              <th style="width:10%;"><b>DESCRIPCI&Oacute;N ACTIVIDAD</b></th>
              <th style="width:10%;"><b>RESULTADO</b></th>
              <th style="width:5%;"><b>TIP. IND.</b></th>
              <th style="width:10%;"><b>INDICADOR</b></th>
              <th style="width:10%;"><b>UNIDAD RESPONSABLE</b></th>
              <th style="width:5%;"><b>META</b></th>
              <th style="width:2.5%;"><b>ENE.</b></th>
              <th style="width:2.5%;"><b>FEB.</b></th>
              <th style="width:2.5%;"><b>MAR.</b></th>
              <th style="width:2.5%;"><b>ABR.</b></th>
              <th style="width:2.5%;"><b>MAY.</b></th>
              <th style="width:2.5%;"><b>JUN.</b></th>
              <th style="width:2.5%;"><b>JUL.</b></th>
              <th style="width:2.5%;"><b>AGO.</b></th>
              <th style="width:2.5%;"><b>SEP.</b></th>
              <th style="width:2.5%;"><b>OCT.</b></th>
              <th style="width:2.5%;"><b>NOV.</b></th>
              <th style="width:2.5%;"><b>DIC.</b></th>
              <th style="width:8%;"><b>MEDIO DE VERIFICACI&Oacute;N</b></th>
              <th style="width:5%;"><b>NRO. REQ.</b></th>
            </tr>
          </thead>
          <tbody>';
          $cont = 0;
          foreach($form4 as $rowp){
            $cont++;
         //   $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);

            $color=''; $titulo=''; $por='';
            if($cite[0]['tp_id']==1){
              if(($rowp['total_anual']+$rowp['prod_linea_base'])!=$rowp['prod_meta'] || $rowp['or_id']==0){
                $color='#fbd5d5';
                $titulo='ERROR EN LA DISTRIBUCION O FALTA DE ALINEACION';
              }
            }
            else{
              if($rowp['indi_id']==2){ // Relativo
                $por='%';
                if($rowp['mt_id']==3){
                  if($rowp['total_anual']!=$rowp['prod_meta'] || $rowp['or_id']==0){
                    $color='#fbd5d5';
                    $titulo='ERROR EN LA DISTRIBUCION O FALTA DE ALINEACION';
                  }
                }
              }
              else{ // Absoluto
                if($rowp['total_anual']!=$rowp['prod_meta'] || $rowp['or_id']==0){
                  $color='#fbd5d5';
                  $titulo='ERROR EN LA DISTRIBUCION O FALTA DE ALINEACION';
                }
              }
            }


            $base_url = base_url();
            $img_path = $base_url . 'assets/ifinal/';
            $prod_id  = $rowp['prod_id'];
            $cite_id  = $cite[0]['cite_id'];
            $es_admin = ($this->fun_id == 399);

            // 2. Definición de componentes (Templates)
            $btn_modificar = "<a href='#' data-toggle='modal' data-target='#modal_mod_form4' class='btn btn-default mod_form4' name='{$prod_id}' title='MODIFICAR ACTIVIDAD'><img src='{$img_path}modificar.png' WIDTH='33' HEIGHT='34'/></a>";
            $btn_eliminar = "<a href='#' data-toggle='modal' data-target='#modal_mdel_ff' class='btn btn-default mdel_ff' title='ELIMINAR FORM 4' name='{$prod_id}' id='{$cite_id}'><img src='{$img_path}eliminar.png' WIDTH='35' HEIGHT='35'/>" . ($es_admin ? "<br>Adm." : "") . "</a>";
            $label_priorizado = "<br><img src='{$img_path}ok.png' WIDTH='37' HEIGHT='30'/><br><font size=1 color=green><b>PRIORIZADO</b></font>";

            $tabla .='
              <tr bgcolor="'.$color.'" class="modo1" title='.$titulo.'>
                <td align="center" title='.$rowp['prod_id'].'><font color="blue" size="2"><b>'.$rowp['prod_cod'].'</b></font></td>
                <td align="center">';

                if ($rowp['prod_priori'] == 0) {
                    // Caso: No priorizado
                    $tabla .= $btn_modificar;

                    // Lógica de eliminación
                    if ($this->tmes == 1) {
                        // Solo si no tiene insumos (Optimización: llamar al modelo solo si tmes == 1)
                        if (empty($this->model_producto->insumo_producto($prod_id))) {
                            $tabla .= $btn_eliminar;
                        }
                    } elseif ($es_admin) {
                        $tabla .= $btn_eliminar;
                    }
                } else {
                    // Caso: Priorizado
                    if ($es_admin) {
                        $tabla .= $btn_modificar;
                    }
                    $tabla .= $label_priorizado;
                }

                $tabla .= '
                </td>
                <td style="width:2%;text-align=center" bgcolor="#c1e1fb"><b><font size=5 color=blue>'.$rowp['og_codigo'].'</font></b></td>
                <td style="width:2%;text-align=center" bgcolor="#c1e1fb"><b><font size=5 color=blue>'.$rowp['or_codigo'].'</font></b></td>
                <td style="width:2%;text-align=center"><b><font size=5>'.$rowp['prod_cod'].'</font></b></td>
                <td style="width:10%;">'.$rowp['prod_producto'].'</td>
                <td style="width:10%;">'.$rowp['prod_resultado'].'</td>
                <td style="width:5%;">'.$rowp['indi_abreviacion'].'</td>
                <td style="width:10%;">'.$rowp['prod_indicador'].'</td>
                <td style="width:10%;">'.$rowp['prod_unidades'].'</td>
                <td style="width:5%;" align=right><b>'.round($rowp['prod_meta'],2).''.$por.'</b></td>';
                for ($i=1; $i <=12 ; $i++) { 
                  $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($rowp['m'.$i],2).' '.$por.'</td>';
                }
              
              $tabla.='<td style="width:8%;" bgcolor="#e5fde5" >'.$rowp['prod_fuente_verificacion'].'</td>';
              $tabla.='<td style="width:5%;" align="center"><font color="blue" size="2"><b>'.count($this->model_producto->insumo_producto($rowp['prod_id'])).'</b></font></td>';
            $tabla .='</tr>';
          }
          $tabla.='</tbody>
          </table>';

      return $tabla;
    }

  //// Lista de Items MODIFICADOS (Nuevo) para el reporte 2023
  public function items_modificados_form4_historial($cite,$tp_rep){
    /// tp_rep : 0 update
    /// tp_rep : 1 reporte
    $tabla='';
    $form4_add = $this->model_modfisica->list_form4_historial_modificados($cite[0]['cite_id'],1); /// Add
    $form4_mod = $this->model_modfisica->list_form4_historial_modificados($cite[0]['cite_id'],2); /// Mod
    $form4_del = $this->model_modfisica->list_form4_historial_modificados($cite[0]['cite_id'],3); /// Del
    
      if(count($form4_add)!=0){
        $tabla.=$this->tabla_form4($cite[0]['por_id'],$form4_add,'ITEMS NUEVOS ('.count($form4_add).')');
      }
      if(count($form4_mod)!=0){
        $tabla.=$this->tabla_form4($cite[0]['por_id'],$form4_mod,'ITEMS MODIFICADOS ('.count($form4_mod).')');
      }
      if(count($form4_del)!=0){
        $tabla.=$this->tabla_form4($cite[0]['por_id'],$form4_del,'ITEMS ELIMINADOS ('.count($form4_del).')');
      }
    
    $tabla.='
            <div style="font-size: 7.5px;font-family: Arial;">
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; En atención a requerimiento de su unidad, comunicamos a usted que se ha procedido a efectivizar la modificación solicitada, toda vez que:<br>

              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a)&nbsp;&nbsp;&nbsp;No compromete u obstaculiza el cumplimiento de los objetivos previstos en la gestión fiscal.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b)&nbsp;&nbsp;&nbsp;No vulnera o contraviene disposiciones legales.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c)&nbsp;&nbsp;&nbsp;No genera obligaciones o deudas por las modificaciones efectuadas.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d)&nbsp;&nbsp;&nbsp;No compromete el pago de obligaciones previstas en el presupuesto.
            </div>';
    return $tabla;
  }


  //// Lista de Items MODIFICADOS PARA EL REPORTE (listado nuevo 2023) FORM 4
  public function tabla_form4($por_id,$listado,$detalle){
    $tabla='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$detalle.'</b></div>';
              $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
              $tabla.='<thead>';
              $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
                $tabla.='<th style="width:1%;height:20px;">#</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>ACP.</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>OPE.</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
                $tabla.='<th style="width:15%;">ACTIVIDAD</th>';
                $tabla.='<th style="width:14%;">RESULTADO</th>';
                $tabla.='<th style="width:9%;">UNIDAD RESPONSABLE</th>';
                $tabla.='<th style="width:8%;">INDICADOR</th>';
                $tabla.='<th style="width:2%;">L.B.</th>';
                $tabla.='<th style="width:2%;">META</th>';
                $tabla.='<th style="width:2.5%;">ENE.</th>';
                $tabla.='<th style="width:2.5%;">FEB.</th>';
                $tabla.='<th style="width:2.5%;">MAR.</th>';
                $tabla.='<th style="width:2.5%;">ABR.</th>';
                $tabla.='<th style="width:2.5%;">MAY.</th>';
                $tabla.='<th style="width:2.5%;">JUN.</th>';
                $tabla.='<th style="width:2.5%;">JUL.</th>';
                $tabla.='<th style="width:2.5%;">AGO.</th>';
                $tabla.='<th style="width:2.5%;">SEPT.</th>';
                $tabla.='<th style="width:2.5%;">OCT.</th>';
                $tabla.='<th style="width:2.5%;">NOV.</th>';
                $tabla.='<th style="width:2.5%;">DIC.</th>';
                $tabla.='<th style="width:10%;">MEDIO DE VERIFICACIÓN</th>';
             
              $tabla.='</tr>';
              $tabla.='</thead>';
              $tabla.='<tbody>';

              $nro=0;
              foreach($listado as $rowp){
               // $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
               // $programado=$this->model_producto->producto_programado($rowp['prod_id'],$this->gestion);
                $color=''; $tp='';
                if($rowp['indi_id']==1){
                  if(($rowp['total_anual'])!=$rowp['prodh_meta']){
                    $color='#fbd5d5';
                  }
                }
                elseif ($rowp['indi_id']==2) {
                  $tp='%';
                  if($rowp['mt_id']==3){
                    if($rowp['total_anual']!=$rowp['prodh_meta']){
                      $color='#fbd5d5';
                    }
                  }
                }

                $color_or='';
                if($rowp['or_id']==0){
                  $color_or='#fbd5d5';
                }

                if($por_id==0){
                  $uresp=strtoupper($rowp['prodh_unidades']);
                }
                else{
                  $unidad=$this->model_componente->get_componente($rowp['huni_resp'],$this->gestion);
                  
                  $uresp='';
                  if(count($unidad)!=0){
                    $proy = $this->model_proyecto->get_UnidadOrganizacional($unidad[0]['proy_id']);
                    $uresp='<font size=1.5><b>'.$proy[0]['tipo'].' '.$proy[0]['act_descripcion'].' - '.$proy[0]['abrev'].' -> '.$unidad[0]['tipo_subactividad'].' '.$unidad[0]['serv_descripcion'].'</b></font>';
                  }
                }

                $nro++;
                $tabla.=
                '<tr style="font-size: 6.5px;" bgcolor="'.$color.'">
                  <td style="width: 1%; height:12px;text-align: center;" bgcolor='.$color_or.'>'.$nro.'</td>
                  <td style="width: 2.1%; text-align: center; font-size:10px" bgcolor='.$color_or.' ><b>'.$rowp['og_codigo'].'</b></td>
                  <td style="width: 2.1%; text-align: center; font-size:10px" bgcolor='.$color_or.' ><b>'.$rowp['or_codigo'].'</b></td>
                  <td style="width: 2.1%; text-align: center; font-size: 10px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
                  <td style="width: 15%; text-align: left;font-size: 7px;">'.$rowp['prodh_producto'].'</td>
                  <td style="width: 14%; text-align: left;">'.$rowp['prod_resultado'].'</td>
                  <td style="width: 9%; text-align: left;">'.$uresp.'</td>
                  <td style="width: 8%; text-align: left;">'.$rowp['prodh_indicador'].'</td>
                  <td style="width: 2%; text-align: right;">'.round($rowp['prodh_linea_base'],2).'</td>
                  <td style="width: 3%; text-align: right;" bgcolor="#eceaea"><b>'.round($rowp['prodh_meta'],2).' '.$tp.'</b></td>';
                  for ($i=1; $i <=12 ; $i++) { 
                    $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($rowp['mes'.$i],2).''.$tp.'</td>';
                  }
                  
                  $tabla.='
                  <td style="width: 10%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                 
                </tr>';

              }
              $tabla.='</tbody>
              </table><br>';

    return $tabla;
  }


  //// Lista de Items Modificados en la Edicion (Reporte PDF) 2022 (reporte antiguo)
  // public function items_modificados_form4($cite_id){
  //   $tabla='';
  //           $ope_adicionados=$this->model_modfisica->operaciones_adicionados($cite_id);
  //           if(count($ope_adicionados)!=0){
  //             $tabla.='<div style="font-size: 10px;height:16px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTIVIDADES AGREGADOS ('.count($ope_adicionados).')</b></div>';
  //             $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
  //             $tabla.='<thead>';
  //             $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
  //               $tabla.='<th style="width:1%;height:20px;">#</th>';
  //               $tabla.='<th style="width:2.2%;">COD.<br>ACE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACP.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>OPE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
  //               $tabla.='<th style="width:14%;">ACTIVIDAD</th>';
  //               $tabla.='<th style="width:14%;">RESULTADO</th>';
  //               $tabla.='<th style="width:7%;">UNIDAD RESPONSABLE</th>';
  //               $tabla.='<th style="width:8%;">INDICADOR</th>';
  //               $tabla.='<th style="width:2%;">L.B.</th>';
  //               $tabla.='<th style="width:2%;">META</th>';
  //               $tabla.='<th style="width:2.5%;">ENE.</th>';
  //               $tabla.='<th style="width:2.5%;">FEB.</th>';
  //               $tabla.='<th style="width:2.5%;">MAR.</th>';
  //               $tabla.='<th style="width:2.5%;">ABR.</th>';
  //               $tabla.='<th style="width:2.5%;">MAY.</th>';
  //               $tabla.='<th style="width:2.5%;">JUN.</th>';
  //               $tabla.='<th style="width:2.5%;">JUL.</th>';
  //               $tabla.='<th style="width:2.5%;">AGO.</th>';
  //               $tabla.='<th style="width:2.5%;">SEPT.</th>';
  //               $tabla.='<th style="width:2.5%;">OCT.</th>';
  //               $tabla.='<th style="width:2.5%;">NOV.</th>';
  //               $tabla.='<th style="width:2.5%;">DIC.</th>';
  //               $tabla.='<th style="width:10%;">MEDIO DE VERIFICACIÓN</th>';
             
  //             $tabla.='</tr>';
  //             $tabla.='</thead>';
  //             $tabla.='<tbody>';
  //             $nro=0;
  //             foreach($ope_adicionados as $rowp){
  //               $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
  //             //  $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
  //               $programado=$this->model_producto->producto_programado($rowp['prod_id'],$this->gestion);
  //               $color=''; $tp='';
  //               if($rowp['indi_id']==1){
  //                 if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                   $color='#fbd5d5';
  //                 }
  //               }
  //               elseif ($rowp['indi_id']==2) {
  //                 $tp='%';
  //                 if($rowp['mt_id']==3){
  //                   if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                     $color='#fbd5d5';
  //                   }
  //                 }
  //               }

  //               /*$ptto=number_format(0, 2, '.', ',');
  //               if(count($monto)!=0){
  //                 $ptto="<b>".number_format($monto[0]['total'], 2, ',', '.')."</b>";
  //               }*/

  //               $color_or='';
  //               if($rowp['or_id']==0){
  //                 $color_or='#fbd5d5';
  //               }

  //               $nro++;
  //               $tabla.=
  //               '<tr style="font-size: 6.5px;" bgcolor="'.$color.'">
  //                 <td style="width: 1%; height:12px;text-align: center;" bgcolor='.$color_or.'>'.$nro.'</td>
  //                 <td style="width: 2.2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['acc_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['og_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['or_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center; font-size: 8px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
  //                 <td style="width: 14%; text-align: left;font-size: 7px;">'.$rowp['prod_producto'].'</td>
  //                 <td style="width: 14%; text-align: left;">'.$rowp['prod_resultado'].'</td>
  //                 <td style="width: 7%; text-align: left;">'.strtoupper($rowp['prod_unidades']).'</td>
  //                 <td style="width: 8%; text-align: left;">'.$rowp['prod_indicador'].'</td>
  //                 <td style="width: 2%; text-align: right;">'.round($rowp['prod_linea_base'],2).'</td>
  //                 <td style="width: 3%; text-align: right;" bgcolor="#eceaea"><b>'.round($rowp['prod_meta'],2).' '.$tp.'</b></td>';

  //                 if(count($programado)!=0){
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['enero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['febrero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['marzo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['abril'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['mayo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['junio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['julio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['agosto'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['septiembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['octubre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['noviembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['diciembre'],2).''.$tp.'</td>';
  //                 }
  //                 else{
  //                   for ($i=1; $i <=12 ; $i++) { 
  //                     $tabla.='<td style="width:2.5%;" bgcolor="#f5cace" align=right>0.00</td>';
  //                   }
  //                 }

  //                 $tabla.='
  //                 <td style="width: 10%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                 
  //               </tr>';

  //             }
  //             $tabla.='</tbody>
  //             </table><br>';
  //           }

  //           $ope_modificados=$this->model_modfisica->operaciones_modificados($cite_id);
  //           if(count($ope_modificados)!=0){
  //             $tabla.='<div style="font-size: 10px;height:16px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTIVIDADES MODIFICADOS ('.count($ope_modificados).')</b></div>';
  //             $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
  //             $tabla.='<thead>';
  //             $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
  //               $tabla.='<th style="width:1%;height:20px;">#</th>';
  //               $tabla.='<th style="width:2.2%;">COD.<br>ACE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACP.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>OPE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
  //               $tabla.='<th style="width:14%;">ACTIVIDAD</th>';
  //               $tabla.='<th style="width:14%;">RESULTADO</th>';
  //               $tabla.='<th style="width:7%;">UNIDAD RESPONSABLE</th>';
  //               $tabla.='<th style="width:8%;">INDICADOR</th>';
  //               $tabla.='<th style="width:2%;">L.B.</th>';
  //               $tabla.='<th style="width:2%;">META</th>';
  //               $tabla.='<th style="width:2.5%;">ENE.</th>';
  //               $tabla.='<th style="width:2.5%;">FEB.</th>';
  //               $tabla.='<th style="width:2.5%;">MAR.</th>';
  //               $tabla.='<th style="width:2.5%;">ABR.</th>';
  //               $tabla.='<th style="width:2.5%;">MAY.</th>';
  //               $tabla.='<th style="width:2.5%;">JUN.</th>';
  //               $tabla.='<th style="width:2.5%;">JUL.</th>';
  //               $tabla.='<th style="width:2.5%;">AGO.</th>';
  //               $tabla.='<th style="width:2.5%;">SEPT.</th>';
  //               $tabla.='<th style="width:2.5%;">OCT.</th>';
  //               $tabla.='<th style="width:2.5%;">NOV.</th>';
  //               $tabla.='<th style="width:2.5%;">DIC.</th>';
  //               $tabla.='<th style="width:10%;">MEDIO DE VERIFICACIÓN</th>';
  //             //  $tabla.='<th style="width:5%;">PPTO.</th>';
  //             $tabla.='</tr>';
  //             $tabla.='</thead>';
  //             $tabla.='<tbody>';
  //             $nro=0;
  //             foreach($ope_modificados as $rowp){
  //               $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
  //              // $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
  //               $programado=$this->model_producto->producto_programado($rowp['prod_id'],$this->gestion);
  //               $color=''; $tp='';
  //               if($rowp['indi_id']==1){
  //                 if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                   $color='#fbd5d5';
  //                 }
  //               }
  //               elseif ($rowp['indi_id']==2) {
  //                 $tp='%';
  //                 if($rowp['mt_id']==3){
  //                   if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                     $color='#fbd5d5';
  //                   }
  //                 }
  //               }

  //              /* $ptto=number_format(0, 2, '.', ',');
  //               if(count($monto)!=0){
  //                 $ptto="<b>".number_format($monto[0]['total'], 2, ',', '.')."</b>";
  //               }*/

  //               $color_or='';
  //               if($rowp['or_id']==0){
  //                 $color_or='#fbd5d5';
  //               }

  //               $nro++;
  //               $tabla.=
  //               '<tr style="font-size: 6.5px;" bgcolor="'.$color.'">
  //                 <td style="width: 1%; height:12px;text-align: center;" bgcolor='.$color_or.'>'.$nro.'</td>
  //                 <td style="width: 2.2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['acc_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['og_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['or_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center; font-size: 8px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
  //                 <td style="width: 14%; text-align: left;font-size: 7px;">'.$rowp['prod_producto'].'</td>
  //                 <td style="width: 14%; text-align: left;">'.$rowp['prod_resultado'].'</td>
  //                 <td style="width: 7%; text-align: left;">'.strtoupper($rowp['prod_unidades']).'</td>
  //                 <td style="width: 8%; text-align: left;">'.$rowp['prod_indicador'].'</td>
  //                 <td style="width: 2%; text-align: right;">'.round($rowp['prod_linea_base'],2).'</td>
  //                 <td style="width: 3%; text-align: right;" bgcolor="#eceaea"><b>'.round($rowp['prod_meta'],2).' '.$tp.'</b></td>';

  //                 if(count($programado)!=0){
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['enero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['febrero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['marzo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['abril'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['mayo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['junio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['julio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['agosto'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['septiembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['octubre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['noviembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['diciembre'],2).''.$tp.'</td>';
  //                 }
  //                 else{
  //                   for ($i=1; $i <=12 ; $i++) { 
  //                     $tabla.='<td style="width:2.5%;" bgcolor="#f5cace" align=right>0.00</td>';
  //                   }
  //                 }

  //                 $tabla.='
  //                 <td style="width: 10%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                 
  //               </tr>';

  //             }
  //             $tabla.='</tbody>
  //             </table><br>';
  //           }

  //           $ope_eliminados=$this->model_modfisica->operaciones_eliminados($cite_id);
  //           if(count($ope_eliminados)!=0){
  //             $tabla.='<div style="font-size: 10px;height:16px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ACTIVIDADES ELIMINADOS ('.count($ope_eliminados).')</b></div>';
  //             $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
  //             $tabla.='<thead>';
  //             $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
  //               $tabla.='<th style="width:1%;height:20px;">#</th>';
  //               $tabla.='<th style="width:2.2%;">COD.<br>ACE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACP.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>OPE.</th>';
  //               $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
  //               $tabla.='<th style="width:14%;">ACTIVIDAD</th>';
  //               $tabla.='<th style="width:14%;">RESULTADO</th>';
  //               $tabla.='<th style="width:7%;">UNIDAD RESPONSABLE</th>';
  //               $tabla.='<th style="width:8%;">INDICADOR</th>';
  //               $tabla.='<th style="width:2%;">L.B.</th>';
  //               $tabla.='<th style="width:2%;">META</th>';
  //               $tabla.='<th style="width:2.5%;">ENE.</th>';
  //               $tabla.='<th style="width:2.5%;">FEB.</th>';
  //               $tabla.='<th style="width:2.5%;">MAR.</th>';
  //               $tabla.='<th style="width:2.5%;">ABR.</th>';
  //               $tabla.='<th style="width:2.5%;">MAY.</th>';
  //               $tabla.='<th style="width:2.5%;">JUN.</th>';
  //               $tabla.='<th style="width:2.5%;">JUL.</th>';
  //               $tabla.='<th style="width:2.5%;">AGO.</th>';
  //               $tabla.='<th style="width:2.5%;">SEPT.</th>';
  //               $tabla.='<th style="width:2.5%;">OCT.</th>';
  //               $tabla.='<th style="width:2.5%;">NOV.</th>';
  //               $tabla.='<th style="width:2.5%;">DIC.</th>';
  //               $tabla.='<th style="width:10%;">MEDIO DE VERIFICACIÓN</th>';
  //             //  $tabla.='<th style="width:5%;">PPTO.</th>';
  //             $tabla.='</tr>';
  //             $tabla.='</thead>';
  //             $tabla.='<tbody>';
  //             $nro=0;
  //             foreach($ope_eliminados as $rowp){
  //               $sum=$this->model_producto->meta_prod_gest($rowp['prod_id']);
  //             //  $monto=$this->model_producto->monto_insumoproducto($rowp['prod_id']);
  //               $programado=$this->model_producto->producto_programado($rowp['prod_id'],$this->gestion);
  //               $color=''; $tp='';
  //               if($rowp['indi_id']==1){
  //                 if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                   $color='#fbd5d5';
  //                 }
  //               }
  //               elseif ($rowp['indi_id']==2) {
  //                 $tp='%';
  //                 if($rowp['mt_id']==3){
  //                   if(($sum[0]['meta_gest'])!=$rowp['prod_meta']){
  //                     $color='#fbd5d5';
  //                   }
  //                 }
  //               }

  //             /*  $ptto=number_format(0, 2, '.', ',');
  //               if(count($monto)!=0){
  //                 $ptto="<b>".number_format($monto[0]['total'], 2, ',', '.')."</b>";
  //               }*/

  //               $color_or='';
  //               if($rowp['or_id']==0){
  //                 $color_or='#fbd5d5';
  //               }

  //               $nro++;
  //               $tabla.=
  //               '<tr style="font-size: 6.5px;" bgcolor="'.$color.'">
  //                 <td style="width: 1%; height:12px;text-align: center;" bgcolor='.$color_or.'>'.$nro.'</td>
  //                 <td style="width: 2.2%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['acc_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['og_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center;" bgcolor='.$color_or.' >'.$rowp['or_codigo'].'</td>
  //                 <td style="width: 2.1%; text-align: center; font-size: 8px;" bgcolor="#eceaea"><b>'.$rowp['prod_cod'].'</b></td>
  //                 <td style="width: 14%; text-align: left;font-size: 7px;">'.$rowp['prod_producto'].'</td>
  //                 <td style="width: 14%; text-align: left;">'.$rowp['prod_resultado'].'</td>
  //                 <td style="width: 7%; text-align: left;">'.strtoupper($rowp['prod_unidades']).'</td>
  //                 <td style="width: 8%; text-align: left;">'.$rowp['prod_indicador'].'</td>
  //                 <td style="width: 2%; text-align: right;">'.round($rowp['prod_linea_base'],2).'</td>
  //                 <td style="width: 3%; text-align: right;" bgcolor="#eceaea"><b>'.round($rowp['prod_meta'],2).' '.$tp.'</b></td>';

  //                 if(count($programado)!=0){
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['enero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['febrero'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['marzo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['abril'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['mayo'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['junio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['julio'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['agosto'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['septiembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['octubre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['noviembre'],2).''.$tp.'</td>';
  //                   $tabla.='<td style="width:2.5%;" bgcolor="#e5fde5" align=right>'.round($programado[0]['diciembre'],2).''.$tp.'</td>';
  //                 }
  //                 else{
  //                   for ($i=1; $i <=12 ; $i++) { 
  //                     $tabla.='<td style="width:2.5%;" bgcolor="#f5cace" align=right>0.00</td>';
  //                   }
  //                 }

  //                 $tabla.='
  //                 <td style="width: 10%; text-align: left;">'.$rowp['prod_fuente_verificacion'].'</td>
                 
  //               </tr>';

  //             }
  //             $tabla.='</tbody>
  //             </table><br>';
  //           }

  //           $tabla.='
  //           <div style="font-size: 7.5px;font-family: Arial;">
  //             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; En atención a requerimiento de su unidad, comunicamos a usted que se ha procedido a efectivizar la modificación solicitada, toda vez que:<br>

  //             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a)&nbsp;&nbsp;&nbsp;No compromete u obstaculiza el cumplimiento de los objetivos previstos en la gestión fiscal.<br>
  //             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b)&nbsp;&nbsp;&nbsp;No vulnera o contraviene disposiciones legales.<br>
  //             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c)&nbsp;&nbsp;&nbsp;No genera obligaciones o deudas por las modificaciones efectuadas.<br>
  //             &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d)&nbsp;&nbsp;&nbsp;No compromete el pago de obligaciones previstas en el presupuesto.
  //           </div>';
    
  //   return $tabla;
  // }

  





//////////////// FORMULARIO N5

 /*------ Lista de Servicios para modificacion de Requerimientos --------*/
    public function lista_unidades_responsables($proyecto){
      $tabla='';
      if($proyecto[0]['por_id']==1){
        $tabla.=$this->unidades_responsables_progBolsas($proyecto); /// actividades de la bolsa
      }
      else{
        $tabla.=$this->unidades_responsables($proyecto);  
      }
      
      return $tabla;
    }


    /*------ Lista de Unidades Responsables (Programas Bolsas) ------*/
      public function unidades_responsables_progBolsas($proyecto){
      $saldos_revertidos_partidas=$this->model_ptto_sigep->lista_monto_partidas_revertidos_unidad($proyecto[0]['proy_id']);
      $tabla='';
      if(count($proyecto)!=0){
        $componente=$this->model_componente->proyecto_componente($proyecto[0]['proy_id']);
        $form4=$this->model_producto->get_lista_form4_uresp_consolidado_programa_bolsas($componente[0]['com_id']);

         $tabla.='
          <div class="well">
            <section class="col col-6">
              <input id="searchTerm" type="text" onkeyup="doSearch()" class="form-control" placeholder="BUSCADOR...." style="width:45%;"/><br>
            </section>
            <table class="table table-bordered" border=1 style="width:100%;" id="datos">
              <thead>
                <tr style="height:45px;">
                  <th style="width:0.5%; text-align:center;">COD. <br> ACT.</th>
            
                  <th style="width:15%; text-align:center;">UNIDAD RESPONSABLE</th>
                  <th style="width:12%; text-align:center;">ACTIVIDAD</th>
                  <th style="width:1%; text-align:center;">VER</th>
                  <th style="width:3%; text-align:center;">REG. CITE<br>MODIFICACION POA </th>
                  <th style="width:5%; text-align:center;">';
                  if(count($saldos_revertidos_partidas)!=0){
                    $tabla.='<b>REG. CITE<br>REVERSION POA</b>';
                  }
                  $tabla.='
                  </th>
                </tr>
              </thead>
              <tbody>';
              $num=0; $ponderacion=0; $sum=0;
              $url_base = base_url();
              $img_ver  = $url_base . 'assets/ifinal/ver_proyecto.png';
              $img_add  = $url_base . 'assets/Iconos/application_form_add.png';
              $img_rev  = $url_base . 'assets/Iconos/application_form_magnify.png';
              $has_saldos = (!empty($saldos_revertidos_partidas)); // Booleano rápido
              $com_id   = $componente[0]['com_id'];

              foreach($form4 as $row) {
                  $num++;
                  $p_id = $row['prod_id'];
                  $p_cod = $row['prod_cod'];
                  $unidades = trim($row['prod_unidades']); // Limpiamos espacios en blanco

                  // 2. Iniciamos la fila con ID para borrado dinámico
                  $tabla .= '<tr id="tr_'.$p_id.'">
                      <td style="font-size: 20px; text-align:center; color:blue" bgcolor="#d4f1fb" title="ID: '.$p_id.'"><b>'.$p_cod.'</b></td>
                      <td style="color:blue"><b>'.$unidades.'</b></td>
                      <td>'.$row['prod_producto'].'</td>';

                  // --- CONDICIÓN MAESTRA: Si no hay unidades, las celdas de botones van vacías ---
                  if (empty($unidades)) {
                      $tabla .= '<td align="center">-</td><td align="center">-</td><td align="center">-</td>';
                  } 
                  else {
                      // Celda: Ver Requerimientos
                      $tabla .= '
                      <td align="center">
                          <a href="#" data-toggle="modal" data-target="#modal_ver" class="btn btn-default ver" title="VER REQUERIMIENTOS" name="'.$p_id.'" id="'.$p_cod.'.- '.$unidades.'">
                              <img src="'.$img_ver.'" width="40" height="40"/>
                          </a>
                      </td>';

                      // Celda: Modificar Requerimientos (Condicional Adm o Config)
                      $tabla .= '<td align="center">';
                      if (($p_cod != '' && $this->conf_mod_req == 1) || $this->tp_adm == 1) {
                          $tabla .= '
                              <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default nuevo_ff" title="MODIFICAR REQUERIMIENTOS" onclick="update_temp('.$com_id.', 0, '.$p_id.');">
                                  <img src="'.$img_add.'" width="30" height="30"/>&nbsp;
                                  <b style="font-size:10px; color:blue">INGRESAR DATOS CITE</b>
                              </a>';
                      }
                      $tabla .= '</td>';

                      // Celda: Saldos Revertidos
                      $tabla .= '<td align="center">';
                      if ($has_saldos) {
                          $tabla .= '
                              <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-warning nuevo_ff" title="REVERSIÓN DE SALDOS" onclick="update_temp('.$com_id.', 1, '.$p_id.');">
                                  <img src="'.$img_rev.'" width="30" height="30"/>&nbsp;
                                  <b style="font-size:10px;">INGRESAR DATOS CITE</b>
                              </a>';
                      }
                      $tabla .= '</td>';
                  }

                  $tabla .= '</tr>';
              }
              $tabla.='
              </tbody>
            </table>
          </div>';
      }

      return $tabla;
    }



    /*------ Lista de Unidades Responsables (Gasto Corriente) ------*/
    public function unidades_responsables($proyecto){

      $saldos_revertidos_partidas=$this->model_ptto_sigep->lista_monto_partidas_revertidos_unidad($proyecto[0]['proy_id']);
      $tabla='';

            $UniResponsables=$this->model_componente->lista_UnidadesResponsables($proyecto[0]['proy_id']);
            $tabla.='
              <div class="well">
              <table class="table table-bordered" width="100%">
                <thead>
                  <tr style="height:45px;">
                    <th style="width:1%; text-align:center;">'.count($saldos_revertidos_partidas).' </th>
                    <th style="width:5%; text-align:center;">CODIGO</th>
                    <th style="width:20%; text-align:center;">UNIDAD RESPONSABLE</th>
                    <th style="width:5%; text-align:center;">REG. CITE<br>MODIFICACION POA</th>
                    <th style="width:5%; text-align:center;">';
                    if(count($saldos_revertidos_partidas)!=0){
                      $tabla.='<b>REG. CITE<br>REVERSION POA</b>';
                    }
                    $tabla.='
                    </th>
                    <th style="width:2%;"></th>
                  </tr>
                </thead>
                <tbody>';
                $num=0; $ponderacion=0; $sum=0;
                foreach($UniResponsables as $row){
                  $num++;
                  $tabla.='
                  <tr>
                    <td align=center title="'.$row['com_id'].'">'.$num.'</td>
                    <td bgcolor="#d4f1fb" align="center" title="C&Oacute;DIGO UNIDAD : '.$row["serv_descripcion"].'"><font color="blue" size=3><b>'.$row['serv_cod'].'</b></font></td>
                    <td title='.$row['com_id'].'>'.$row['serv_cod'].' '.$row['tipo_subactividad'].' '.$row['serv_descripcion'].'</td>
                    <td align=center>';
                      if($this->conf_mod_req==1 || $this->tp_adm==1){
                        $tabla.='
                        <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-default nuevo_ff"  title="MODIFICAR REQUERIMIENTOS" onclick="update_temp('.$row['com_id'].',0,0);">
                          <img src="'.base_url().'assets/Iconos/application_form_add.png" WIDTH="30" HEIGHT="30"/>&nbsp;
                          <b style="font-size:10px;">INGRESAR DATOS CITE</b>
                        </a>';
                      }
                    $tabla.='
                    </td>
                    <td align=center>';
                      if(count($saldos_revertidos_partidas)!=0){
                        $tabla.='
                        <a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-warning nuevo_ff"  title="MODIFICAR REQUERIMIENTOS POR REVERSION DE SALDOS" onclick="update_temp('.$row['com_id'].',1,0);">
                          <img src="'.base_url().'assets/Iconos/application_form_magnify.png" WIDTH="30" HEIGHT="30"/>&nbsp;
                          <b style="font-size:10px;">INGRESAR DATOS CITE</b>
                        </a>';
                      }
                    $tabla.='
                    </td>
                    <td align=center>';
                      if($this->fun_id==399){
                        $tabla.='
                        <a href="'.site_url("").'/mod/delete_insumos_eliminados/'.$row['com_id'].'" title="LIMPIAR" class="btn btn-default">
                        <img src="'.base_url().'assets/ifinal/registrono.png" WIDTH="34" HEIGHT="30"/>
                        </a>';
                      }
                    $tabla.='
                    </td>
                  </tr>';
                }
                $tabla.='    
                </tbody>
              </table>
            </div>';


      return $tabla;
    }



    /*----- LISTA REQUERIMIENTOS POR UNIDAD RESPONSABLE AUXILIAR (2027) en casos de que sean muchos requerimientos ------*/
    // public function formN5_mod_lista_requerimientos_SinTemporalidad($cite){
    //   //$proyecto = $this->model_proyecto->get_UnidadOrganizacional($cite[0]['proy_id']); /// PROYECTO
    //   //$lista_insumos=$this->model_modrequerimiento->lista_requerimientos($cite[0]['com_id'],$cite[0]['tipo_modificacion']); /// Listado normal
    //   if($proyecto[0]['por_id']==1){
    //     $lista_insumos=$this->model_insumo->lista_insumos_prod($cite[0]['prod_id']); /// listado de items segun actividad Programas Bolsas
    //   }
    //   else{
    //     $lista_insumos=$this->model_modrequerimiento->lista_requerimientos($cite[0]['com_id'],$cite[0]['tipo_modificacion']); /// Listado normal
    //   }
      
    //   $tabla='';
    //   $total=0;
    //   $tabla .= ' 
    //         <input type="hidden" name="proy_id" value="'.$cite[0]['proy_id'].'">
    //         <input type="hidden" name="aper_id" value="'.$cite[0]['aper_id'].'">
    //         <input type="hidden" name="cite_id" value="'.$cite[0]['cite_id'].'">
    //         <input type="hidden" name="base" value="'.base_url().'">
            
    //         <!-- 🌟 REPARADO CORE: Contenedor elástico con desborde horizontal controlado por hardware -->
    //         <div class="alert alert-info" style="border-left: 4px solid #475569; background-color: #f8fafc; border-color: #cbd5e1; color: #334155; padding: 10px 14px; margin-bottom: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 8px;">
    //             <i class="fa fa-bolt" style="font-size: 13px; color: #d97706; animation: flash_siiplas_opt 2s infinite;"></i>
    //             <span style="font-family: Arial, sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: 0.2px; line-height: 1.4;">
    //                 <b>Optimización de rendimiento activo:</b> Despliegue de registros de alta densidad unificado (Caché RAM). Procesamiento optimizado para listados concurrentes superiores a 400 ítems.
    //             </span>
    //             <style>
    //                 @keyframes flash_siiplas_opt { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
    //             </style>
    //         </div>
    //         <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; margin-bottom: 15px;">
    //             <table id="dt_basic" class="table table-bordered table-striped table-hover" width="100%" style="margin-bottom: 0; min-width: 1600px; border-collapse: collapse; font-size: 11px;">
    //             <thead>
    //               <tr class="modo1" style="height: 36px; background: #334155; color: #ffffff;">
    //                 <th style="width:2%; text-align: center; vertical-align: middle;">#</th>
    //                 <th style="width:2%; text-align: center; vertical-align: middle;">COD. ACT.</th>
    //                 <th style="width:2%; text-align: center; vertical-align: middle;">ACCIONES</th>
    //                 <th style="width:5%; text-align: left; vertical-align: middle;">PARTIDA</th>
    //                 <th style="width:20%; text-align: left; vertical-align: middle;">DETALLE REQUERIMIENTO</th>
    //                 <th style="width:10%; text-align: left; vertical-align: middle;">UNIDAD</th>
    //                 <th style="width:5%; text-align: right; vertical-align: middle;">CANTIDAD</th>
    //                 <th style="width:5%; text-align: right; vertical-align: middle;">UNITARIO</th>
    //                 <th style="width:5%; text-align: right; vertical-align: middle;">TOTAL</th>
    //                 <th style="width:5%; text-align: right; vertical-align: middle;">TOTAL CERT.</th>';
                    
    //                 // Matriz de nombres de meses para sanear tildes y codificaciones
    //                 $meses = array(1=>"ENE.", 2=>"FEB.", 3=>"MAR.", 4=>"ABR.", 5=>"MAY.", 6=>"JUN.", 7=>"JUL.", 8=>"AGO.", 9=>"SEPT.", 10=>"OCT.", 11=>"NOV.", 12=>"DIC.");
    //                 for ($m = 1; $m <= 12; $m++) {
    //                     // 🛠️ REPARADO: Se fusionan los estilos en una sola directiva style válida
    //                     $tabla .= '<th style="width:4%; text-align: right; vertical-align: middle; background-color: #0AA699; color: #FFFFFF;">'.$meses[$m].'</th>';
    //                 }
                    
    //                 $tabla .= ' 
    //                 <th style="width:8%; text-align: left; vertical-align: middle;">OBSERVACIONES</th>
    //                 <th style="width:2%; text-align: center; vertical-align: middle;">DELETE</th>
    //               </tr>
    //             </thead>
    //             <tbody>';
    //              $cont = 0;
    //             $total = 0;

    //             foreach ($lista_insumos as $row) {
    //                 $cont++;
    //                 $total += $row['ins_costo_total'];
                    
    //                 // Lógica de colores y estados
    //                 $color_tr = ($row['ins_monto_certificado'] != 0 && $row['ins_monto_certificado'] == $row['ins_costo_total']) ? 'style="background-color:#f9d8e0;"' : '';
    //                 $valor_mod = ($row['ins_monto_certificado'] != 0 && (round($row['ins_monto_certificado'],2) == round($row['ins_costo_total'],2))) ? 1 : 0;
    //                 $valor_delete = ($row['ins_monto_certificado'] != 0) ? 1 : 0;

    //                 // Etiqueta de tipo de registro
    //                 $tp_label = ($row['ins_tipo_modificacion'] == 1) 
    //                     ? '<span class="label label-warning" style="display:block; text-align:center;width="25">REG. x REV.</span>' 
    //                     : '<span class="label label-primary" style="display:block; text-align:center;width="25">REG. x POA</span>';

    //                 // Construcción de Botones
    //                 $botones = '';
    //                 if ($valor_mod == 0 && $valor_delete == 0) {
    //                     if ($this->fun_id == 399) {
    //                         $botones .= '<a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default btn-xs mod_ff" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" title="MODIFICAR" style="padding: 6px 8px;"><img src="'.base_url('assets/ifinal/modificar.png').'" width="30"></a> ';
    //                         $botones .= '<a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default btn-xs del_ff" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" title="ELIMINAR" style="padding: 6px 8px;"><img src="'.base_url('assets/img/delete.png').'" width="30"></a>';
    //                     }
    //                 } elseif ($valor_mod == 0 && $valor_delete == 1) {
    //                     $botones .= '<a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default btn-xs mod_ff" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/ifinal/modificar.png').'" width="30"></a> ';
    //                     $botones .= '<a href="#" data-toggle="modal" data-target="#modal_certpoas" class="btn btn-default btn-xs certpoas" name="'.$row['ins_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/img/ifinal/doc.jpg').'" width="30"></a>';
    //                 } else {
    //                     $botones .= '<a href="#" data-toggle="modal" data-target="#modal_certpoas" class="btn btn-default btn-xs certpoas" name="'.$row['ins_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/img/ifinal/doc.jpg').'" width="30"></a>';
    //                 }

    //                 $tabla .= '
    //                 <tr '.$color_tr.' style="height: 30px; vertical-align: middle;">
    //                     <td title="'.$row['ins_id'].'" style="vertical-align: middle;">'.$tp_label.'</td>
    //                     <td class="text-center" style="background-color:#ecf9f7; color:blue; font-weight:bold; font-size:14px; vertical-align: middle;">'.$row['prod_cod'].'</td>
    //                     <td class="text-center" style="vertical-align: middle; white-space: nowrap;">'.$botones.'</td>
    //                     <td style="vertical-align: middle; font-weight: bold; color: #475569;">'.$row['par_codigo'].'</td>
    //                     <td style="vertical-align: middle; white:20%;">'.htmlspecialchars($row['ins_detalle'], ENT_QUOTES, 'UTF-8').'</td>
    //                     <td style="vertical-align: middle;">'.htmlspecialchars($row['ins_unidad_medida'], ENT_QUOTES, 'UTF-8').'</td>
    //                     <td class="text-right" style="vertical-align: middle;">'.number_format($row['ins_cant_requerida'], 0, '.', ',').'</td>
    //                     <td class="text-right" style="vertical-align: middle;">'.number_format($row['ins_costo_unitario'], 2, '.', ',').'</td>
    //                     <td class="text-right" style="vertical-align: middle;"><b>'.number_format($row['ins_costo_total'], 2, '.', ',').'</b></td>
    //                     <td class="text-right" style="background-color:#f1dfb9; vertical-align: middle; font-weight: 600;">'.number_format($row['ins_monto_certificado'], 2, '.', ',').'</td>';
    //                     for ($i=1; $i <=12 ; $i++) { 
    //                       $tabla.='<td class="text-right" bgcolor="#eaf9f7" style="vertical-align: middle;">-</td>';
    //                     }

    //                 $tabla .= '
    //                     <td style="vertical-align: middle;">'.htmlspecialchars($row['ins_observacion'], ENT_QUOTES, 'UTF-8').'</td>
    //                     <td class="text-center" style="background-color:#f3cbcb; vertical-align: middle;">';
    //                     if ($valor_mod == 0 && $valor_delete == 0) {
    //                         $tabla .= '<input type="checkbox" class="check-insumo" name="ins[]" value="'.$row['ins_id'].'" style="transform: scale(1.1); cursor: pointer;">';
    //                     }
    //                 $tabla .= '</td></tr>';
    //             }
    //           $tabla.='
    //           </tbody>
    //           <tfoot>
    //               <tr class="modo1" style="height: 36px; background: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1; vertical-align: middle;">
    //                 <td colspan="8" style="padding-left: 10px; font-size: 11px;">proy: '.$cite[0]['proy_id'].' | aper: '.$cite[0]['aper_id'].' <span style="float: right; padding-right: 10px; text-transform: uppercase;"><b>TOTAL ACUMULADO PROYECTO:</b></span> </td>
    //                 <td class="text-right" style="padding-right: 8px;"><font color="blue" size=2><b>'.number_format($total, 2, '.', ',') .'</b></font></td>
    //                 <td colspan="15"></td>
    //               </tr>
    //           </tfoot>
    //         </table>
    //         </div>';

    //   return $tabla;
    // }


    /*----- LISTA REQUERIMIENTOS POR UNIDAD RESPONSABLE COMPLETO (2027) ------*/
    public function formN5_mod_lista_requerimientos_ConTemporalidad($cite){
      if($cite[0]['por_id']==1){ /// bolsa
        $lista_insumos=$this->model_insumo->lista_insumos_x_form4($cite[0]['prod_id']); /// listado de items segun actividad Programas Bolsas
      }
      else{
        $lista_insumos=$this->model_modrequerimiento->lista_requerimientos_con_temporalidad($cite[0]['com_id'],$cite[0]['tipo_modificacion']); 
      }

      $tabla='';
      $total=0;
     $tabla .= ' 
            <input type="hidden" name="proy_id" value="'.$cite[0]['proy_id'].'">
            <input type="hidden" name="aper_id" value="'.$cite[0]['aper_id'].'">
            <input type="hidden" name="cite_id" value="'.$cite[0]['cite_id'].'">
            <input type="hidden" name="base" value="'.base_url().'">';

            if($cite[0]['por_id']==1){
              $tabla.='
              <div class="alert alert-info" style="border-left: 4px solid #475569; background-color: #f8fafc; border-color: #cbd5e1; color: #334155; padding: 10px 14px; margin-bottom: 15px; border-radius: 4px; box-shadow: 0 1px 2px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 8px;">
                    <i class="fa fa-bolt" style="font-size: 13px; color: #d97706; animation: flash_siiplas_opt 2s infinite;"></i>
                    <span style="font-family: Arial, sans-serif; font-size: 11.5px; font-weight: 600; letter-spacing: 0.2px; line-height: 1.4;">
                        <b>ACTIVIDAD : '.$cite[0]['prod_cod'].'.- </b>'.$cite[0]['prod_producto'].'<br>
                        <div style="color:blue;">('.$cite[0]['prod_id'].')<b> UNIDAD RESPONSABLE : </b>'.$cite[0]['unidad_responsable'].'</div>
                    </span>
                    <style>
                        @keyframes flash_siiplas_opt { 0%, 100% { opacity: 1; } 50% { opacity: 0.6; } }
                    </style>
                </div>';
            }
            $tabla.='
            <div class="table-responsive" style="overflow-x: auto; width: 100%; border: 1px solid #cbd5e1; border-radius: 4px; margin-bottom: 15px;">
                <table id="dt_basic" class="table table-bordered" width="100%" style="margin-bottom: 0; min-width: 1600px; border-collapse: collapse; font-size: 11px;">
                <thead>
                  <tr class="modo1" style="height: 36px; background: #334155; color: #ffffff;">
                    <th style="width:4%; text-align: center; vertical-align: middle;">#</th>
                    <th style="width:2%; text-align: center; vertical-align: middle;">COD. ACT.</th>
                    <th style="width:2%; text-align: center; vertical-align: middle;">ACCIONES</th>
                    <th style="width:5%; text-align: left; vertical-align: middle;">PARTIDA</th>
                    <th style="width:20%; text-align: left; vertical-align: middle;">DETALLE REQUERIMIENTO</th>
                    <th style="width:10%; text-align: left; vertical-align: middle;">UNIDAD</th>
                    <th style="width:5%; text-align: right; vertical-align: middle;">CANTIDAD</th>
                    <th style="width:5%; text-align: right; vertical-align: middle;">PRECIO</th>
                    <th style="width:5%; text-align: right; vertical-align: middle;">COSTO TOTAL</th>
                    <th style="width:5%; text-align: right; vertical-align: middle;">TOTAL CERT.</th>';
                    
                    // Matriz de nombres de meses para sanear tildes y codificaciones
                    $meses = array(1=>"ENE.", 2=>"FEB.", 3=>"MAR.", 4=>"ABR.", 5=>"MAY.", 6=>"JUN.", 7=>"JUL.", 8=>"AGO.", 9=>"SEPT.", 10=>"OCT.", 11=>"NOV.", 12=>"DIC.");
                    for ($m = 1; $m <= 12; $m++) {
                        // 🛠️ REPARADO: Se fusionan los estilos en una sola directiva style válida
                        $tabla .= '<th style="width:4%; text-align: right; vertical-align: middle; background-color: #0AA699; color: #FFFFFF;">'.$meses[$m].'</th>';
                    }
                    
                    $tabla .= ' 
                    <th style="width:8%; text-align: left; vertical-align: middle;">OBSERVACIONES</th>
                    <th style="width:2%; text-align: center; vertical-align: middle;">DELETE</th>
                  </tr>
                </thead>
                <tbody>';
                 $cont = 0;
                $total = 0;

                foreach ($lista_insumos as $row) {
                    $cont++;
                    $total += $row['ins_costo_total'];
                    
                    // Lógica de colores y estados
                    $color_tr = ($row['ins_monto_certificado'] != 0 && $row['ins_monto_certificado'] == $row['ins_costo_total']) ? 'style="background-color:#f9d8e0;"' : '';
                    $valor_mod = ($row['ins_monto_certificado'] != 0 && (round($row['ins_monto_certificado'],2) == round($row['ins_costo_total'],2))) ? 1 : 0;
                    $valor_delete = ($row['ins_monto_certificado'] != 0) ? 1 : 0;

                    // Etiqueta de tipo de registro
                    $tp_label = ($row['ins_tipo_modificacion'] == 1) 
                        ? '<span class="label label-warning" style="display:block; text-align:center;padding: 10px 12px;">REGISTRO x REVERSIÓN</span>' 
                        : '<span class="label label-primary" style="display:block; text-align:center;padding: 10px 12px;">POA</span>';

                    // Construcción de Botones
                    $botones = '';
                    if ($valor_mod == 0 && $valor_delete == 0) {
                      $botones .= '<a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default btn-xs mod_ff5" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" title="MODIFICAR" style="padding: 6px 8px;"><img src="'.base_url('assets/ifinal/modificar.png').'" width="30"></a> ';
                      $botones .= '<a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default btn-xs del_ff" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" title="ELIMINAR" style="padding: 6px 8px;"><img src="'.base_url('assets/img/delete.png').'" width="30"></a>';
                    } elseif ($valor_mod == 0 && $valor_delete == 1) {
                        $botones .= '<a href="#" data-toggle="modal" data-target="#modal_mod_ff" class="btn btn-default btn-xs mod_ff5" name="'.$row['ins_id'].'" id="'.$cite[0]['cite_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/ifinal/modificar.png').'" width="30"></a> ';
                        $botones .= '<a href="#" data-toggle="modal" data-target="#modal_certpoas" class="btn btn-default btn-xs certpoas" name="'.$row['ins_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/img/ifinal/doc.jpg').'" width="30"></a>';
                    } else {
                        $botones .= '<a href="#" data-toggle="modal" data-target="#modal_certpoas" class="btn btn-default btn-xs certpoas" name="'.$row['ins_id'].'" style="padding: 6px 8px;"><img src="'.base_url('assets/img/ifinal/doc.jpg').'" width="30"></a>';
                    }

                    $tabla .= '
                    <tr '.$color_tr.' style="height: 30px; vertical-align: middle;">
                        <td title="'.$row['ins_id'].'" style="vertical-align: middle; width:4%;">'.$tp_label.'</td>
                        <td class="text-center" style="background-color:#ecf9f7; color:blue; font-weight:bold; font-size:14px; vertical-align: middle;">'.$row['prod_cod'].'</td>
                        <td class="text-center" style="vertical-align: middle; white-space: nowrap;">'.$botones.'</td>
                        <td style="vertical-align: middle; font-weight: bold; color: #475569;">'.$row['par_codigo'].'</td>
                        <td style="vertical-align: middle; white:20%;">'.htmlspecialchars($row['ins_detalle'], ENT_QUOTES, 'UTF-8').'</td>
                        <td style="vertical-align: middle;">'.htmlspecialchars($row['ins_unidad_medida'], ENT_QUOTES, 'UTF-8').'</td>
                        <td class="text-right" style="vertical-align: middle;">'.number_format($row['ins_cant_requerida'], 0, '.', ',').'</td>
                        <td class="text-right" style="vertical-align: middle;">'.number_format($row['ins_costo_unitario'], 2, '.', ',').'</td>
                        <td class="text-right" style="vertical-align: middle;"><b>'.number_format($row['ins_costo_total'], 2, '.', ',').'</b></td>
                        <td class="text-right" style="background-color:#f1dfb9; vertical-align: middle; font-weight: 600;">'.number_format($row['ins_monto_certificado'], 2, '.', ',').'</td>';
                        for ($i=1; $i <=12 ; $i++) { 
                          $tabla.='<td class="text-right" bgcolor="#eaf9f7" style="vertical-align: middle;">'.number_format($row['mes'.$i], 2, '.', ',').'</td>';
                        }

                    $tabla .= '
                        <td style="vertical-align: middle;">'.htmlspecialchars($row['ins_observacion'], ENT_QUOTES, 'UTF-8').'</td>
                        <td class="text-center" style="background-color:#f3cbcb; vertical-align: middle;">';
                        if ($valor_mod == 0 && $valor_delete == 0) {
                            $tabla .= '<input type="checkbox" class="check-insumo" name="ins[]" value="'.$row['ins_id'].'" style="transform: scale(1.1); cursor: pointer;">';
                        }
                    $tabla .= '</td></tr>';
                }
              $tabla.='
              </tbody>
              <tfoot>
                  <tr class="modo1" style="height: 36px; background: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1; vertical-align: middle;">
                    <td colspan="8" style="padding-left: 10px; font-size: 11px;">proy: '.$cite[0]['proy_id'].' | aper: '.$cite[0]['aper_id'].' <span style="float: right; padding-right: 10px; text-transform: uppercase;"><b>TOTAL ACUMULADO PROYECTO:</b></span> </td>
                    <td class="text-right" style="padding-right: 8px;"><font color="blue" size=2><b>'.number_format($total, 2, '.', ',') .'</b></font></td>
                    <td colspan="15"></td>
                  </tr>
              </tfoot>
            </table>
            </div>';

      return $tabla;
    }


  //// Lista de Items MODIFICADOS PARA EL REPORTE (listado nuevo 2024)
  public function tabla($tipo_mod,$listado,$detalle){
    $tabla='';

    if($tipo_mod==2){ /// MODIFICACION
       $tabla.='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$detalle.'</b></div>
            <table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
              <thead>
              <tr class="modo1" style="text-align: center;" bgcolor="#efefef">
                <th style="width:1%;height:20px;">#</th>
                <th style="width:2.1%;">COD.<br>ACT.</th>
                <th style="width:3.5%;">PARTIDA</th>
                <th style="width:16.5%;">DETALLE REQUERIMIENTO</th>
                <th style="width:4.6%;">UNIDAD MEDIDA</th>
                <th style="width:3.5%;">CANT.</th>
                <th style="width:4%;">PRECIO UNI.</th>
                <th style="width:4%;">COSTO TOTAL</th>
                <th style="width:4.4%;">ENE.</th>
                <th style="width:4.4%;">FEB.</th>
                <th style="width:4.4%;">MAR.</th>
                <th style="width:4.4%;">ABR.</th>
                <th style="width:4.4%;">MAY.</th>
                <th style="width:4.4%;">JUN.</th>
                <th style="width:4.4%;">JUL.</th>
                <th style="width:4.4%;">AGO.</th>
                <th style="width:4.4%;">SEPT.</th>
                <th style="width:4.4%;">OCT.</th>
                <th style="width:4.4%;">NOV.</th>
                <th style="width:4.4%;">DIC.</th>
                <th style="width:6.5%;">OBSERVACIÓN</th>
              </tr>
              </thead>
              <tbody>';
              $nro=0;
              $monto=0;
              foreach ($listado as $row){
                $item_mod=$this->model_modrequerimiento->get_item_insumo_modificado_ultimo($row['cite_id'],2,$row['ins_id']);
                $prog = $this->model_modrequerimiento->list_temporalidad_insumo_historial($item_mod[0]['insh_id']);
                $nro++;
                $tabla.='<tr class="modo1">
                  <td style="width: 1%;height:11px; text-align: center;font-size: 6px;">'.$nro.'</td>
                  <td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$item_mod[0]['prod_cod'].'</b></td>
                  <td style="width: 3.5%; text-align: center;">'.$item_mod[0]['par_codigo'].'</td>
                  <td style="width: 16.5%; text-align: left;text-align: justify;">'.$item_mod[0]['ins_detalle'].'</td>
                  <td style="width: 4.6%; text-align: left;">'.$item_mod[0]['ins_unidad_medida'].'</td>
                  <td style="width: 3.5%; text-align: right;">'.$item_mod[0]['ins_cant_requerida'].'</td>
                  <td style="width: 4%; text-align: right;">'.number_format($item_mod[0]['ins_costo_unitario'], 2, ',', '.').'</td>
                  <td style="width: 4%; text-align: right;">'.number_format($item_mod[0]['ins_costo_total'], 2, ',', '.').'</td>';
                  if(count($prog)!=0){
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                    }
                  }
                  else{
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;" bgcolor=red>-</td>';
                    }
                  }
                  $tabla.='<td style="width: 6.5%; text-align: left;text-align: justify;font-size: 6px;">'.$item_mod[0]['ins_observacion'].'</td>';
                $tabla.='</tr>';
                $monto=$monto+$item_mod[0]['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:10px;" colspan=7></td>
                  <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                  <td colspan=13></td>
                </tr>
              </table><br>';
    }
    else{ /// ADICION Y ELIMINACION
      $tabla.='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>'.$detalle.'</b></div>
            <table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
              <thead>
              <tr class="modo1" style="text-align: center;" bgcolor="#efefef">
                <th style="width:1%;height:20px;">#</th>
                <th style="width:2.1%;">COD.<br>ACT.</th>
                <th style="width:3.5%;">PARTIDA</th>
                <th style="width:16.5%;">DETALLE REQUERIMIENTO</th>
                <th style="width:4.6%;">UNIDAD MEDIDA</th>
                <th style="width:3.5%;">CANT.</th>
                <th style="width:4%;">PRECIO UNI.</th>
                <th style="width:4%;">COSTO TOTAL</th>
                <th style="width:4.4%;">ENE.</th>
                <th style="width:4.4%;">FEB.</th>
                <th style="width:4.4%;">MAR.</th>
                <th style="width:4.4%;">ABR.</th>
                <th style="width:4.4%;">MAY.</th>
                <th style="width:4.4%;">JUN.</th>
                <th style="width:4.4%;">JUL.</th>
                <th style="width:4.4%;">AGO.</th>
                <th style="width:4.4%;">SEPT.</th>
                <th style="width:4.4%;">OCT.</th>
                <th style="width:4.4%;">NOV.</th>
                <th style="width:4.4%;">DIC.</th>
                <th style="width:6.5%;">OBSERVACIÓN</th>
              </tr>
              </thead>
              <tbody>';
              $nro=0;
              $monto=0;
              foreach ($listado as $row){
                $prog = $this->model_modrequerimiento->list_temporalidad_insumo_historial($row['insh_id']);
                $nro++;
                $tabla.='<tr class="modo1">
                  <td style="width: 1%;height:11px; text-align: center;font-size: 6px;">'.$nro.'</td>
                  <td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>
                  <td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>
                  <td style="width: 16.5%; text-align: left;text-align: justify;">'.$row['ins_detalle'].'</td>
                  <td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>
                  <td style="width: 3.5%; text-align: right;">'.$row['ins_cant_requerida'].'</td>
                  <td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>
                  <td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
                  if(count($prog)!=0){
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                    }
                  }
                  else{
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;" bgcolor=red>-</td>';
                    }
                  }
                  $tabla.='<td style="width: 6.5%; text-align: left;text-align: justify;font-size: 6px;">'.$row['ins_observacion'].'</td>';
                $tabla.='</tr>';
                $monto=$monto+$row['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:10px;" colspan=7></td>
                  <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                  <td colspan=13></td>
                </tr>
              </table><br>';
    }


    

    return $tabla;
  }




  //// Lista de Items MODIFICADOS PARA EL EDITADO (listado nuevo 2023)
  public function tabla_update($tipo_mod,$listado,$detalle,$table){
    $tabla='';
    $tabla.='
    <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
    <input type="hidden" name="base" value="'.base_url().'">
      <div class="jarviswidget jarviswidget-color-darken">
        <header>
          <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
            <h2 class="font-md"><strong>'.$detalle.'</strong></h2>  
        </header>
        <div>
          <div class="widget-body no-padding">';
                if($tipo_mod==1 || $tipo_mod==3){ /// Adicion y Eliminacion
                    $tabla.='
                    '.$table.'
                      <thead>
                      <tr class="modo1" style="text-align: center;" bgcolor="#efefef">
                        <th style="width:1%;height:20px;background-color: #1c7368; color: #FFFFFF"">#</th>
                        <th style="width:2.1%;background-color: #1c7368; color: #FFFFFF"">COD.<br>ACT.</th>
                        <th style="width:3.5%;background-color: #1c7368; color: #FFFFFF"">PARTIDA</th>
                        <th style="width:16%;background-color: #1c7368; color: #FFFFFF"">DETALLE REQUERIMIENTO</th>
                        <th style="width:4.6%;background-color: #1c7368; color: #FFFFFF"">UNIDAD MEDIDA</th>
                        <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">CANT.</th>
                        <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">PRECIO UNI.</th>
                        <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">COSTO TOTAL</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">ENE.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">FEB.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">MAR.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">ABR.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">MAY.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">JUN.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">JUL.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">AGO.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">SEPT.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">OCT.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">NOV.</th>
                        <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">DIC.</th>
                        <th style="width:6.3%;background-color: #1c7368; color: #FFFFFF"">OBSERVACIÓN</th>
                        <th style="width:2%;background-color: #1c7368; color: #FFFFFF"></th>
                      </tr>
                      </thead>
                      <tbody>';
                      $nro=0;
                      $monto=0;
                      foreach ($listado as $row){
                        $prog = $this->model_modrequerimiento->list_temporalidad_insumo_historial($row['insh_id']);
                        $nro++;
                        $tabla.='
                        <tr class="modo1">
                          <td style="width: 1%;height:11px; text-align: center;font-size: 6px;" title='.$row['ins_id'].'>'.$nro.'</td>
                          <td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>
                          <td style="width: 3.5%; text-align: center;">'.$row['par_codigo'].'</td>
                          <td style="width: 16%; text-align: left; font-size:10.5px;">'.$row['ins_detalle'].'</td>
                          <td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>
                          <td style="width: 4%; text-align: right;">'.$row['ins_cant_requerida'].'</td>
                          <td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>
                          <td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
                          if(count($prog)!=0){
                            for ($i=1; $i <=12 ; $i++) { 
                              $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                            }
                          }
                          else{
                            for ($i=1; $i <=12 ; $i++) { 
                              $tabla .= '<td style="width: 4.4%; text-align: right;" bgcolor=red>-</td>';
                            }
                          }
                          $tabla.='
                          <td style="width: 6.3%; text-align: left; font-size:11px;">'.$row['ins_observacion'].'</td>
                          <td style="width: 2%; text-align: left;">
                            <a href="#" data-toggle="modal" data-target="#modal_anular_mod" class="btn btn-default anular_mod" title="NO MOSTRAR MODIFICACIÓN"  name="'.$row['insh_id'].'"><img src="'.base_url().'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></a>
                          </td>';
                        $tabla.='</tr>';
                        $monto=$monto+$row['ins_costo_total'];
                      }
                      $tabla.='</tbody>
                        <tr class="modo1">
                          <td style="height:11px;" colspan=7></td>
                          <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                          <td colspan=13></td>
                        </tr>
                      </table>
                      </div>';
                }
                else{ /// Modificacion
                $tabla.='
                '.$table.'
                  <thead>
                  <tr class="modo1" style="text-align: center;" bgcolor="#efefef">
                    <th style="width:1%;height:20px;background-color: #1c7368; color: #FFFFFF"">#</th>
                    <th style="width:2.1%;background-color: #1c7368; color: #FFFFFF"">COD.<br>ACT.</th>
                    <th style="width:3.5%;background-color: #1c7368; color: #FFFFFF"">PARTIDA</th>
                    <th style="width:16%;background-color: #1c7368; color: #FFFFFF"">DETALLE REQUERIMIENTO</th>
                    <th style="width:4.6%;background-color: #1c7368; color: #FFFFFF"">UNIDAD MEDIDA</th>
                    <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">CANT.</th>
                    <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">PRECIO UNI.</th>
                    <th style="width:4%;background-color: #1c7368; color: #FFFFFF"">COSTO TOTAL</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">ENE.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">FEB.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">MAR.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">ABR.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">MAY.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">JUN.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">JUL.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">AGO.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">SEPT.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">OCT.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">NOV.</th>
                    <th style="width:4.4%;background-color: #1c7368; color: #FFFFFF"">DIC.</th>
                    <th style="width:6.3%;background-color: #1c7368; color: #FFFFFF"">OBSERVACIÓN</th>
                    <th style="width:2%;background-color: #1c7368; color: #FFFFFF"></th>
                  </tr>
                  </thead>
                  <tbody>';
                  $nro=0;
                  $monto=0;
                  foreach ($listado as $row){
                    $item_mod=$this->model_modrequerimiento->get_item_insumo_modificado_ultimo($row['cite_id'],2,$row['ins_id']);
                    $prog = $this->model_modrequerimiento->list_temporalidad_insumo_historial($item_mod[0]['insh_id']);

                    $nro++;
                    $tabla.='<tr class="modo1">
                      <td style="width: 1%;height:11px; text-align: center;font-size: 6px;" title='.$row['ins_id'].'>'.$nro.'</td>
                      <td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$item_mod[0]['prod_cod'].'</b></td>
                      <td style="width: 3.5%; text-align: center;">'.$item_mod[0]['par_codigo'].'</td>
                      <td style="width: 16%; text-align: left; font-size:10.5px;">'.$item_mod[0]['ins_detalle'].'</td>
                      <td style="width: 4.6%; text-align: left;">'.$item_mod[0]['ins_unidad_medida'].'</td>
                      <td style="width: 4%; text-align: right;">'.$item_mod[0]['ins_cant_requerida'].'</td>
                      <td style="width: 4%; text-align: right;">'.number_format($item_mod[0]['ins_costo_unitario'], 2, ',', '.').'</td>
                      <td style="width: 4%; text-align: right;">'.number_format($item_mod[0]['ins_costo_total'], 2, ',', '.').'</td>';
                      if(count($prog)!=0){
                        for ($i=1; $i <=12 ; $i++) { 
                          $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                        }
                      }
                      else{
                        for ($i=1; $i <=12 ; $i++) { 
                          $tabla .= '<td style="width: 4.4%; text-align: right;" bgcolor=red>-</td>';
                        }
                      }
                      $tabla.='
                      <td style="width: 6.3%; text-align: left; font-size:11px;">'.$item_mod[0]['ins_observacion'].'</td>
                      <td style="width: 2%; text-align: left;">
                        <a href="#" data-toggle="modal" data-target="#modal_anular_mod" class="btn btn-default anular_mod" title="NO MOSTRAR MODIFICACIÓN"  name="'.$item_mod[0]['insh_id'].'"><img src="'.base_url().'assets/img/neg.jpg" WIDTH="35" HEIGHT="35"/></a>
                      </td>';
                    $tabla.='</tr>';
                    $monto=$monto+$item_mod[0]['ins_costo_total'];
                  }
                  $tabla.='</tbody>
                    <tr class="modo1">
                      <td style="height:11px;" colspan=7></td>
                      <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                      <td colspan=13></td>
                    </tr>
                  </table>
                  </div>';
                }
          
              $tabla.='
            </div>
          </div>
        </article>
        <br>';

    return $tabla;
  }

  //// Lista de Items MODIFICADOS (Nuevo) para el reporte
  public function items_modificados_form5_historial($cite_id,$tp_rep){
    /// tp_rep : 0 update
    /// tp_rep : 1 reporte
    $cite=$this->model_modrequerimiento->get_cite_insumo($cite_id);
    $tabla='';
    $requerimientos_add = $this->model_modrequerimiento->list_form5_historial_modificados($cite_id,1); /// Add
    $requerimientos_mod = $this->model_modrequerimiento->get_list_form5_historial_modificados($cite_id,2); /// Mod
    $requerimientos_del = $this->model_modrequerimiento->list_form5_historial_modificados($cite_id,3); /// Del
    
    if($tp_rep==0){
      if(count($requerimientos_add)!=0){
        $tabla.=$this->tabla_update(1,$requerimientos_add,'ITEMS AGREGADOS ('.count($requerimientos_add).')','<table id="dt_basic1" class="table1 table-bordered" style="width:100%;" border="0.2">');
      }
      if(count($requerimientos_mod)!=0){
        $tabla.=$this->tabla_update(2,$requerimientos_mod,'ITEMS MODIFICADOS ('.count($requerimientos_mod).')','<table id="dt_basic" class="table table-bordered" style="width:100%;" border="0.2">',0);
      }
      if(count($requerimientos_del)!=0){
        $tabla.=$this->tabla_update(3,$requerimientos_del,'ITEMS ELIMINADOS ('.count($requerimientos_del).')','<table id="dt_basic3" class="table1 table-bordered" style="width:100%;" border="0.2">');
      }
    }
    else{

      if(count($requerimientos_add)!=0){
        $tabla.=$this->tabla(1,$requerimientos_add,'ITEMS AGREGADOS ('.count($requerimientos_add).')');
      }
      if(count($requerimientos_mod)!=0){
        $tabla.=$this->tabla(2,$requerimientos_mod,'ITEMS MODIFICADOS ('.count($requerimientos_mod).')');
      }
      if(count($requerimientos_del)!=0){
        $tabla.=$this->tabla(3,$requerimientos_del,'ITEMS ELIMINADOS ('.count($requerimientos_del).')');
      }


      
      $tabla.='
            <div style="font-size: 7.5px;font-family: Arial;">
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; En atención a requerimiento de su unidad, comunicamos a usted que se ha procedido a efectivizar la modificación solicitada, toda vez que:<br>

              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a)&nbsp;&nbsp;&nbsp;No compromete u obstaculiza el cumplimiento de los objetivos previstos en la gestión fiscal.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b)&nbsp;&nbsp;&nbsp;No vulnera o contraviene disposiciones legales.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c)&nbsp;&nbsp;&nbsp;No genera obligaciones o deudas por las modificaciones efectuadas.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d)&nbsp;&nbsp;&nbsp;No compromete el pago de obligaciones previstas en el presupuesto.
              <br><br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>OBSERVACION :</b> '.$cite[0]['cite_observacion'].'
            </div>';
    }
    
    return $tabla;
  }


  //// Lista de Items MODIFICADOS (Nuevo) Nacional
  public function items_modificados_form5_historial_nacional(){
    $items_modificados=$this->model_modrequerimiento->lista_requerimientos_modificados_nacional();
    $tabla='';
    $tabla.='
      <style>
        table{font-size: 9px;
          width: 100%;
          max-width:1550px;
          overflow-x: scroll;
        }
        th{
          padding: 1.4px;
          text-align: center;
          font-size: 10px;
        }
      </style>
    <table border="1" cellpadding="0" cellspacing="0" class="tabla">
      <thead>
        <tr style="background-color: #66b2e8">
          <th style="width:1%;height:20px;background-color: #eceaea;">#</th>
          <th style="width:5%;background-color: #eceaea;">COD. DEP.</th>
          <th style="width:10%;background-color: #eceaea;">REGIONAL</th>
          <th style="width:5%;background-color: #eceaea;">COD. DIST.</th>
          <th style="width:10%;background-color: #eceaea;">DISTRITAL</th>
          <th style="width:10%;background-color: #eceaea;">GASTO CORRIENTE / PROYECTO DE INVERSION</th>
          <th style="width:10%;background-color: #eceaea;">CITE CODIGO</th>
          <th style="width:10%;background-color: #eceaea;">CITE NOTA</th>
          <th style="width:10%;background-color: #eceaea;">PARTIDA</th>
          <th style="width:2.1%;background-color: #eceaea;">COD.<br>ACT.</th>
          <th style="width:3.8%;background-color: #eceaea;">PARTIDA</th>
          <th style="width:16%;background-color: #eceaea;">DETALLE REQUERIMIENTO</th>
          <th style="width:5%;background-color: #eceaea;">UNIDAD MEDIDA</th>
          <th style="width:5%;background-color: #eceaea;">CANTIDAD</th>
          <th style="width:5%;background-color: #eceaea;">PRECIO UNITARIO</th>
          <th style="width:5%;background-color: #eceaea;">COSTO TOTAL</th>
          <th style="width:5%;background-color: #eceaea;">ENE.</th>
          <th style="width:5%;background-color: #eceaea;">FEB.</th>
          <th style="width:5%;background-color: #eceaea;">MAR.</th>
          <th style="width:5%;background-color: #eceaea;">ABR.</th>
          <th style="width:5%;background-color: #eceaea;">MAY.</th>
          <th style="width:5%;background-color: #eceaea;">JUN.</th>
          <th style="width:5%;background-color: #eceaea;">JUL.</th>
          <th style="width:5%;background-color: #eceaea;">AGO.</th>
          <th style="width:5%;background-color: #eceaea;">SEPT.</th>
          <th style="width:5%;background-color: #eceaea;">OCT.</th>
          <th style="width:5%;background-color: #eceaea;">NOV.</th>
          <th style="width:5%;background-color: #eceaea;">DIC.</th>
          <th style="width:6%;background-color: #eceaea;">OBSERVACION</th>
          <th style="width:8%;background-color: #eceaea;">TIPO MODIFICACION</th>
          <th style="width:8%;background-color: #eceaea;">FECHA MODIFICACION</th>
          <th style="width:8%;background-color: #eceaea;">RESPONSABLE</th>
        </tr>
      </thead>
      <tbody>';
      $nro=0;
      foreach($items_modificados as $row){
        $nro++;
        $tabla.='
        <tr>
          <td>'.$nro.'</td>
          <td>'.$row['dep_id'].'</td>
          <td>'.$row['da'].'</td>
          <td>'.$row['dep_departamento'].'</td>
          <td>'.$row['ue'].'</td>
          <td>'.$row['dist_distrital'].'</td>
          <td>'.mb_convert_encoding(strtoupper($row['tipo'].' '.$row['actividad'].' '.$row['abrev']), 'cp1252', 'UTF-8').'</td>
          <td>'.$row['cite_codigo'].'</td>
          <td style="width:3.8%; font-size: 15px;">'.$row['cite_nota'].'</td>
          <td style="width:2.1%; font-size: 15px;" align=center>'.$row['prod_cod'].'</td>
          <td style="width:3.8%; font-size: 15px;" align=center><b>'.$row['par_codigo'].'</b></td>
          <td>'.$row['ins_detalle'].'</td>
          <td>'.round($row['ins_unidad_medida'],2).'</td>
          <td>'.round($row['ins_cant_requerida'],2).'</td>
          <td>'.round($row['ins_costo_unitario'],2).'</td>
          <td>'.round($row['ins_costo_total'],2).'</td>';
          for ($i=1; $i <=12 ; $i++) { 
            $tabla.='<td align="left">'.round($row['mes'.$i]).'</td>';
          }
          $tabla.='
          <td>'.mb_convert_encoding(strtoupper($row['ins_observacion']), 'cp1252', 'UTF-8').'</td>
          <td>';
            $tipo='AGREGADO';
            if($row['tipo_mod']==2){
              $tipo='MODIFICADO';
            }
            else{
              $tipo='ELIMINADO'; 
            }
          $tabla.='
          </td>
          <td bgcolor="#dbfbf6">'.date('d/m/Y',strtotime($row['fecha_creacion'])).'</td>
          <td bgcolor="#dbfbf6">'.$row['fun_nombre'].' '.$row['fun_paterno'].' '.$row['fun_materno'].'</td>
        </tr>';
      }
      $tabla.='
      </tbody>
    </table>';


    return $tabla;
  }


















  //// Lista de Items MODIFICADOS (listado anterior vigente)
  public function items_modificados_form5($cite_id){
    $tabla='';
            $requerimientos_add = $this->model_modrequerimiento->list_requerimientos_adicionados($cite_id);
            if(count($requerimientos_add)!=0){
              $tabla.='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>ITEMS AGREGADOS ('.count($requerimientos_add).')</b></div>';
              $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
              $tabla.='<thead>';
              $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
                $tabla.='<th style="width:1%;height:20px;">#</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
                $tabla.='<th style="width:3.8%;">PARTIDA</th>';
                $tabla.='<th style="width:16%;">DETALLE REQUERIMIENTO</th>';
                $tabla.='<th style="width:4.6%;">UNIDAD MEDIDA</th>';
                $tabla.='<th style="width:4%;">CANTIDAD</th>';
                $tabla.='<th style="width:4%;">PRECIO UNITARIO</th>';
                $tabla.='<th style="width:4%;">COSTO TOTAL</th>';
                $tabla.='<th style="width:4.4%;">ENE.</th>';
                $tabla.='<th style="width:4.4%;">FEB.</th>';
                $tabla.='<th style="width:4.4%;">MAR.</th>';
                $tabla.='<th style="width:4.4%;">ABR.</th>';
                $tabla.='<th style="width:4.4%;">MAY.</th>';
                $tabla.='<th style="width:4.4%;">JUN.</th>';
                $tabla.='<th style="width:4.4%;">JUL.</th>';
                $tabla.='<th style="width:4.4%;">AGO.</th>';
                $tabla.='<th style="width:4.4%;">SEPT.</th>';
                $tabla.='<th style="width:4.4%;">OCT.</th>';
                $tabla.='<th style="width:4.4%;">NOV.</th>';
                $tabla.='<th style="width:4.4%;">DIC.</th>';
                $tabla.='<th style="width:6%;">OBSERVACIÓN</th>';
              $tabla.='</tr>';
              $tabla.='</thead>';
              $tabla.='<tbody>';
              $nro=0;
              $monto=0;
              foreach ($requerimientos_add as $row){
                $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
                $nro++;
                $tabla.='<tr class="modo1">';
                  $tabla.='<td style="width: 1%;height:11px; text-align: center;font-size: 6px;">'.$nro.'</td>';
                  $tabla.='<td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
                  $tabla.='<td style="width: 3.8%; text-align: center;">'.$row['par_codigo'].'</td>';
                  $tabla.='<td style="width: 16%; text-align: left;">'.$row['ins_detalle'].'</td>';
                  $tabla.='<td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
                  if(count($prog)!=0){
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                    }
                  }
                  else{
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla.='<td style="width: 4.4%; text-align: right;">-</td>';
                    }
                  }
                  $tabla.='<td style="width: 6%; text-align: left;">'.$row['ins_observacion'].'</td>';
                $tabla.='</tr>';
                $monto=$monto+$row['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:10px;" colspan=7></td>
                  <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                  <td colspan=13></td>
                </tr>
              </table><br>';
            }

            $requerimientos_mod = $this->model_modrequerimiento->list_requerimientos_modificados($cite_id);
            if(count($requerimientos_mod)!=0){
              $tabla.='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>ITEMS MODIFICADOS ('.count($requerimientos_mod).')</b></div>';
              $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
              $tabla.='<thead>';
              $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
                $tabla.='<th style="width:1%;height:20px;">#</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
                $tabla.='<th style="width:3.8%;">PARTIDA</th>';
                $tabla.='<th style="width:16%;">DETALLE REQUERIMIENTO</th>';
                $tabla.='<th style="width:4.6%;">UNIDAD MEDIDA</th>';
                $tabla.='<th style="width:4%;">CANTIDAD</th>';
                $tabla.='<th style="width:4%;">PRECIO UNITARIO</th>';
                $tabla.='<th style="width:4%;">COSTO TOTAL</th>';
                $tabla.='<th style="width:4.4%;">ENE.</th>';
                $tabla.='<th style="width:4.4%;">FEB.</th>';
                $tabla.='<th style="width:4.4%;">MAR.</th>';
                $tabla.='<th style="width:4.4%;">ABR.</th>';
                $tabla.='<th style="width:4.4%;">MAY.</th>';
                $tabla.='<th style="width:4.4%;">JUN.</th>';
                $tabla.='<th style="width:4.4%;">JUL.</th>';
                $tabla.='<th style="width:4.4%;">AGO.</th>';
                $tabla.='<th style="width:4.4%;">SEPT.</th>';
                $tabla.='<th style="width:4.4%;">OCT.</th>';
                $tabla.='<th style="width:4.4%;">NOV.</th>';
                $tabla.='<th style="width:4.4%;">DIC.</th>';
                $tabla.='<th style="width:6%;">OBSERVACIÓN</th>';
              $tabla.='</tr>';
              $tabla.='</thead>';
              $tabla.='<tbody>';
              $nro=0;
              $monto=0;
              foreach ($requerimientos_mod as $row){
                $prog = $this->model_insumo->list_temporalidad_insumo($row['ins_id']);
                $nro++;
                  $tabla.='<tr class="modo1">';
                  $tabla.='<td style="width: 1%;height:11px; text-align: center;font-size: 6px;">'.$nro.'</td>';
                  $tabla.='<td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
                  $tabla.='<td style="width: 3.8%; text-align: center;">'.$row['par_codigo'].'</td>';
                  $tabla.='<td style="width: 16%; text-align: left;">'.$row['ins_detalle'].'</td>';
                  $tabla.='<td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
                  if(count($prog)!=0){
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($prog[0]['mes'.$i], 2, ',', '.') . '</td>';
                    }
                  }
                  else{
                    for ($i=1; $i <=12 ; $i++) { 
                      $tabla.='<td style="width: 4.4%; text-align: right;">-</td>';
                    }
                  }
                  $tabla.='<td style="width: 6%; text-align: left;">'.$row['ins_observacion'].'</td>';
                $tabla.='</tr>';
                $monto=$monto+$row['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:10px;" colspan=7></td>
                  <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                  <td colspan=13></td>
                </tr>
              </table><br>';
            }

            $requerimientos_del = $this->model_modrequerimiento->list_requerimientos_eliminados($cite_id);
            if(count($requerimientos_del)!=0){
              $tabla.='<div style="font-size: 10px;height:16px;">&nbsp;&nbsp;&nbsp;&nbsp;<b>ITEMS ELIMINADOS ('.count($requerimientos_del).')</b></div>';
              $tabla.='<table border="0.2" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">';
              $tabla.='<thead>';
              $tabla.='<tr class="modo1" style="text-align: center;" bgcolor="#efefef">';
                $tabla.='<th style="width:1%;height:20px;">#</th>';
                $tabla.='<th style="width:2.1%;">COD.<br>ACT.</th>';
                $tabla.='<th style="width:3.8%;">PARTIDA</th>';
                $tabla.='<th style="width:16%;">DETALLE REQUERIMIENTO</th>';
                $tabla.='<th style="width:4.6%;">UNIDAD MEDIDA</th>';
                $tabla.='<th style="width:4%;">CANTIDAD</th>';
                $tabla.='<th style="width:4%;">PRECIO UNITARIO</th>';
                $tabla.='<th style="width:4%;">COSTO TOTAL</th>';
                $tabla.='<th style="width:4.4%;">ENE.</th>';
                $tabla.='<th style="width:4.4%;">FEB.</th>';
                $tabla.='<th style="width:4.4%;">MAR.</th>';
                $tabla.='<th style="width:4.4%;">ABR.</th>';
                $tabla.='<th style="width:4.4%;">MAY.</th>';
                $tabla.='<th style="width:4.4%;">JUN.</th>';
                $tabla.='<th style="width:4.4%;">JUL.</th>';
                $tabla.='<th style="width:4.4%;">AGO.</th>';
                $tabla.='<th style="width:4.4%;">SEPT.</th>';
                $tabla.='<th style="width:4.4%;">OCT.</th>';
                $tabla.='<th style="width:4.4%;">NOV.</th>';
                $tabla.='<th style="width:4.4%;">DIC.</th>';
                $tabla.='<th style="width:6%;">OBSERVACIÓN</th>';
              $tabla.='</tr>';
              $tabla.='</thead>';
              $tabla.='<tbody>';
              $nro=0;
              $monto=0;
              foreach ($requerimientos_del as $row){
                $nro++;
                $tabla.='<tr class="modo1">';
                  $tabla.='<td style="width: 1%; height:11px;text-align: center;font-size: 6px;">'.$nro.'</td>';
                  $tabla.='<td style="width: 2.1%; text-align: center;font-size: 12px;"><b>'.$row['prod_cod'].'</b></td>';
                  $tabla.='<td style="width: 3.8%; text-align: center;">'.$row['par_codigo'].'</td>';
                  $tabla.='<td style="width: 16%; text-align: left;">'.$row['ins_detalle'].'</td>';
                  $tabla.='<td style="width: 4.6%; text-align: left;">'.$row['ins_unidad_medida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.$row['ins_cant_requerida'].'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_unitario'], 2, ',', '.').'</td>';
                  $tabla.='<td style="width: 4%; text-align: right;">'.number_format($row['ins_costo_total'], 2, ',', '.').'</td>';
                  for ($i=1; $i <=12 ; $i++) { 
                    $tabla .= '<td style="width: 4.4%; text-align: right;">' . number_format($row['mes'.$i], 2, ',', '.') . '</td>';
                  }
                $tabla.='<td style="width: 6%; text-align: left;">'.$row['ins_observacion'].'</td>';
                $tabla.='</tr>';
                $monto=$monto+$row['ins_costo_total'];
              }
              $tabla.='</tbody>
                <tr class="modo1">
                  <td style="height:10px;" colspan=7></td>
                  <td style="text-align: right;">' . number_format($monto, 2, ',', '.') . '</td>
                  <td colspan=13></td>
                </tr>
              </table><br>';
            }

          $tabla.='
            <div style="font-size: 8px;font-family: Arial;">
              &nbsp;&nbsp;&nbsp;&nbsp;En atención a requerimiento de su unidad, comunicamos a usted que se ha procedido a efectivizar la modificación solicitada, toda vez que:<br>

              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;a)&nbsp;&nbsp;No compromete u obstaculiza el cumplimiento de los objetivos previstos en la gestión fiscal.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;b)&nbsp;&nbsp;No vulnera o contraviene disposiciones legales.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;c)&nbsp;&nbsp;No genera obligaciones o deudas por las modificaciones efectuadas.<br>
              &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;d)&nbsp;&nbsp;No compromete el pago de obligaciones previstas en el presupuesto.
            </div>';
    
    return $tabla;
  }











  //// REPORTE MODIFICACION POA
  //// Cabecera Modifcacion poa
    public function cabecera_modpoa($cite,$tp){
      $titulo_mod='ACTIVIDADES';
      if($tp==2){
        $titulo_mod='REQUERIMIENTOS';
      }

      $tabla='';
      $codigo='Sin Codigo ... debe cerrar la modificación poa ';
      if($cite[0]['cite_codigo']!=''){
        $codigo=$cite[0]['cite_codigo'];
      }

      $tipo_mod='';
      if($cite[0]['tipo_modificacion']==1){
        $tipo_mod='(Rev. POA)';
      }

      $comp='';
      if($cite[0]['por_id']==0){
        $comp='
        <tr>
          <td style="width:20%;">
            <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 8px;">
              <tr><td style="width:95%;height: 40%;" bgcolor="#e6e5e5"><b>&nbsp;UNIDAD RESPONSABLE</b></td><td style="width:5%;"></td></tr>
            </table>
          </td>
          <td style="width:80%;">
            <table border="0.4" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 7.5px;">
              <tr><td style="width:100%;height: 40%;" bgcolor="#f9f9f9">&nbsp;'.$cite[0]['tipo_subactividad'].' '.$cite[0]['com_componente'].'</td></tr>
            </table>
          </td>
        </tr>';
      }

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
                '.strtoupper($cite[0]['dist_distrital']).' '.$this->mes[ltrim(date("m"), "0")].' de '.date("Y").'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
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
                      <td style="height: 30%;"><b>MODIFICACIÓN POA '.$this->gestion.' - '.$titulo_mod.'</b></td>
                  </tr>
                  <tr style="font-size: 20px;font-family: Arial;">
                    <td style="height: 5%;font-family: Arial;">'.$codigo.'</td>
                  </tr>
                </table>
              </td>
              <td style="width:10%; text-align:center;">
              </td>
            </tr>
        </table>
        <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
            <tr style="border: solid 0px;">              
                <td style="width:50%;">
                </td>
                <td style="width:50%; height: 3%">
                    <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                      <tr style="font-size: 15px;font-family: Arial;">
                          <td colspan=2 align=center style="width:100%;height: 40%;"><b>FORMULARIO MOD. N° 8 </b> '.$tipo_mod.'</td>
                      </tr>
                      <tr style="font-size: 10px;font-family: Arial;">
                          <td style="width:47%;height: 30%;"><b>CITE : '.$cite[0]['cite_nota'].'</b></td>
                          <td style="width:47%;height: 30%"><b>FECHA : '.date('d-m-Y',strtotime($cite[0]['cite_fecha'])).'</b></td>
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
          <tr>
              <td style="width:2%;"></td>
              <td style="width:96%;height: 3%;">
                <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                  <tr>
                      <td style="width:20%;">
                          <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 8px;">
                              <tr><td style="width:95%;height: 40%;" bgcolor="#eceaea"><b>REGIONAL / DEPARTAMENTO</b></td><td style="width:5%;"></td></tr>
                          </table>
                      </td>
                      <td style="width:80%;">
                          <table border="0.4" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 7.5px;">
                              <tr><td style="width:100%;height: 40%;" bgcolor="#f9f9f9">&nbsp;'.strtoupper ($cite[0]['dep_departamento']).'</td></tr>
                          </table>
                      </td>
                  </tr>
                  <tr>
                      <td style="width:20%;">
                          <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 8px;">
                              <tr><td style="width:95%;height: 40%;" bgcolor="#eceaea"><b>UNIDAD EJECUTORA</b></td><td style="width:5%;"></td></tr>
                          </table>
                      </td>
                      <td style="width:80%;">
                          <table border="0.4" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 7.5px;">
                              <tr><td style="width:100%;height: 40%;" bgcolor="#f9f9f9">&nbsp;'.strtoupper ($cite[0]['dist_distrital']).'</td></tr>
                          </table>
                      </td>
                  </tr>';

                    if($cite[0]['tp_id']==1){
                      $tabla.='
                      <tr>
                        <td style="width:20%;">
                            <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 8px;">
                                <tr><td style="width:95%;height: 40%;" bgcolor="#eceaea"><b>PROY. INVERSI&Oacute;N</b></td><td style="width:5%;"></td></tr>
                            </table>
                        </td>
                        <td style="width:80%;">
                            <table border="0.4" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 7.5px;">
                                <tr><td style="width:100%;height: 40%;" bgcolor="#f9f9f9">&nbsp;'.$cite[0]['proy_sisin'].' '.strtoupper ($cite[0]['proy_nombre']).'</td></tr>
                            </table>
                        </td>
                      </tr>';
                    }
                    else{
                      $tabla.='
                      <tr>
                        <td style="width:20%;">
                            <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 8px;">
                                <tr><td style="width:95%;height: 40%;" bgcolor="#eceaea"><b>CAT. PROGRAMATICA '.$this->gestion.'</b></td><td style="width:5%;"></td></tr>
                            </table>
                        </td>
                        <td style="width:80%;">
                            <table border="0.4" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;font-size: 7.5px;">
                                <tr><td style="width:100%;height: 40%;" bgcolor="#f9f9f9">&nbsp;'.$cite[0]['aper_programa'].''.$cite[0]['aper_proyecto'].''.$cite[0]['aper_actividad'].' '.strtoupper ($cite[0]['act_descripcion']).' '.$cite[0]['abrev'].'</td></tr>
                            </table>
                        </td>
                      </tr>';
                    }

                  $tabla.='
                  '.$comp.'
              </table>
            </td>
            <td style="width:2%;"></td>
          </tr>
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


//// Pie de Modificacion POA
  public function pie_modpoa($cite,$codigo){
    $tabla='';
/*    $tabla.='
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:96%;">
          <tr>
            <td style="width: 1%;"></td>
            <td style="width: 75%;">
                <b>OBSERVACIÓN</b><hr>
                <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                  <tr bgcolor="#cae4fb">
                    <td style="width: 100%;height: 2%; font-size:5px">
                      <b>'.$cite[0]['cite_observacion'].'</b>
                    </td>
                  </tr>
                </table>
            </td>
          </tr>
        </table>';*/
      $tabla.='
      <table border=0 style="width:100%;">
        <tr>
          <td style="width:1%;"></td>
          <td style="width:98%;">
            <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
            <tr>';
            
            if($cite[0]['dep_id']==10){ /// Ritha
              $tabla.='
              <td style="width:30%;">
                  <table border="0.5" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                    <tr style="font-size: 8px;font-family: Arial;">
                        <td style="width:100%;height:13px;"><b>ELABORADO POR<br></b></td>
                    </tr>
                   
                    <tr style="font-size: 8.5px;font-family: Arial; height:65px;">
                        <td><br><br>
                          <table border=0>
                            <tr style="font-size: 7px;font-family: Arial; height:65px;">
                                <td><b>RESPONSABLE : </b></td>
                                <td>'.$cite[0]['fun_nombre'].' '.$cite[0]['fun_paterno'].' '.$cite[0]['fun_materno'].'</td>
                            </tr>
                            <tr style="font-size: 7px;font-family: Arial; height:65px;">
                                <td><b>CARGO : </b></td>
                                <td><b>'.$cite[0]['fun_cargo'].'</b></td>
                            </tr>
                          </table>
                        </td>
                    </tr>
                  </table>
                </td>
                
                <td style="width:30%;">
                  <table border="0.5" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                      <tr style="font-size: 8px;font-family: Arial;">
                          <td style="width:100%;height:13px;"><b>APROBADO POR</b></td>
                      </tr>
                     
                      <tr style="font-size: 8px;font-family: Arial; height:65px;" align="center">
                          <td><b><br><br><br><br>FIRMA</b></td>
                      </tr>
                  </table>
                </td>

                <td style="width:30%;">
                  <table border="0.5" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                      <tr style="font-size: 8px;font-family: Arial;">
                          <td style="width:100%;height:13px;"><b>FIRMA / SELLO DE RECEPCION DE LA UNIDAD SOLICITANTE (FECHA)<br></b></td>
                      </tr>
                     
                      <tr style="font-size: 8px;font-family: Arial; height:65px;" align="center">
                          <td><b><br><br><br><br>FIRMA</b></td>
                      </tr>
                  </table>
                </td>';
            }
            else{
              $tabla.='
              <td style="width:45%;">
                <table border="0.5" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                  <tr style="font-size: 9px;font-family: Arial;">
                      <td style="width:100%;height:13px;"><b>ELABORADO POR<br></b></td>
                  </tr>
                 
                  <tr style="font-size: 8.5px;font-family: Arial; height:65px;">
                      <td><br><br>
                        <table border=0>
                          <tr style="font-size: 7px;font-family: Arial; height:65px;">
                              <td><b>RESPONSABLE : </b></td>
                              <td>'.$cite[0]['fun_nombre'].' '.$cite[0]['fun_paterno'].' '.$cite[0]['fun_materno'].'</td>
                          </tr>
                          <tr style="font-size: 7px;font-family: Arial; height:65px;">
                              <td><b>CARGO : </b></td>
                              <td><b>'.$cite[0]['fun_cargo'].'</b></td>
                          </tr>
                        </table>
                      </td>
                  </tr>
                </table>
              </td>
              <td style="width:45%;">

                <table border="0.5" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;" align="center">
                    <tr style="font-size: 9px;font-family: Arial;">
                        <td style="width:100%;height:13px;"><b>FIRMA / SELLO DE RECEPCION DE LA UNIDAD SOLICITANTE (FECHA)<br></b></td>
                    </tr>
                   
                    <tr style="font-size: 8px;font-family: Arial; height:65px;" align="center">
                        <td><b><br><br><br><br>FIRMA</b></td>
                    </tr>
                </table>

            </td>';
            }
            $tabla.='
                <td style="width:10%;" align=center>';
                  $cod='<div style="color: red;width:30%;"><b>Sin Codigo</b></div>';
                  if($codigo!=''){
                    $cod='<qrcode value="'.$codigo.'" style="border: none; width: 18mm;"></qrcode>';
                  }
                $tabla.=' '.$cod.'
                </td>
              </tr>
              <tr>
                <td colspan=2 style="height:18px;">'.$this->session->userdata('sistema').'</td>
                <td align=right>'.$cite[0]['fun_paterno'].' - pag. [[page_cu]]/[[page_nb]]</td>
              </tr>
            </table>
          </td>
         
        </tr>
      </table>';

    return $tabla;
  }




  //// loading para la siguiente pagina
    public function loading_siguiente_pagina(){
      $tabla='<div id="screen-blocker" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.85); z-index: 9999; cursor: wait;">
                <style>
                    /* Estilos específicos para el spinner sin afectar al resto de la página */
                    .loader-wrapper { 
                        position: absolute; 
                        top: 50%; 
                        left: 50%; 
                        transform: translate(-50%, -50%); 
                        text-align: center; 
                    }
                    .spinner-custom { 
                        border: 8px solid #f3f3f3; 
                        border-top: 8px solid #5B9360; /* Color verde sugerido */
                        border-radius: 50%; 
                        width: 60px; 
                        height: 60px; 
                        animation: spin-custom 1s linear infinite; 
                        margin: 0 auto 15px; 
                    }
                    @keyframes spin-custom { 
                        0% { transform: rotate(0deg); } 
                        100% { transform: rotate(360deg); } 
                    }
                    .loading-text { 
                        font-family: Arial, sans-serif; 
                        color: #333; 
                        font-weight: bold; 
                        font-size: 16px; 
                    }
                </style>
                
                <div class="loader-wrapper">
                    <div class="spinner-custom"></div>
                    <div class="loading-text">espere por favor...</div>
                </div>
            </div> ';
            return $tabla;
    }


    //// loading para ctualizar listado
    public function loading($titulo){
      $tabla='<div id="loading-overlay">
                  <div class="loader-content">
                      <div class="spinner-custom"></div>
                      <h2 style="color: white;">'.$titulo.'</h2>
                      <p style="color: white;">Por favor, no cierre la ventana...</p>
                  </div>
              </div>

                <style>
                #loading-overlay {
                    position: fixed; /* Se mantiene fijo aunque hagas scroll */
                    top: 0;
                    left: 0;
                    width: 125vw;    /* 120% del ancho de la ventana */
                    height: 125vh;   /* 120% del alto de la ventana */
                    background-color: rgba(0, 0, 0, 0.85); /* Fondo oscuro semitransparente */
                    z-index: 999999; /* Valor extremadamente alto para estar sobre todo */
                    display: none;   /* Se activa con JS */
                    justify-content: center;
                    align-items: center;
                    color: white;
                    text-align: center;
                    font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                }

                .loader-content h2 {
                    margin-top: 20px;
                    letter-spacing: 2px;
                    font-weight: bold;
                }

                .spinner-custom {
                    width: 80px;
                    height: 80px;
                    border: 8px solid rgba(255, 255, 255, 0.1);
                    border-top: 8px solid #3276b1; /* Color azul de SmartAdmin */
                    border-radius: 50%;
                    animation: spin-loading 1s linear infinite;
                    display: inline-block;
                }

                @keyframes spin-loading {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
                </style>';
      return $tabla;
    }











    /*------- GENERAR MENU --------*/
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


    /*------ NOMBRE MES -------*/
    public function mes_nombre_completo(){
        $mes[1] = 'ENERO';
        $mes[2] = 'FEBRERO';
        $mes[3] = 'MARZO';
        $mes[4] = 'ABRIL';
        $mes[5] = 'MAYO';
        $mes[6] = 'JUNIO';
        $mes[7] = 'JULIO';
        $mes[8] = 'AGOSTO';
        $mes[9] = 'SEPTIEMBRE';
        $mes[10] = 'OCTUBRE';
        $mes[11] = 'NOVIEMBRE';
        $mes[12] = 'DICIEMBRE';

      return $mes;
    }

    /*------ NOMBRE MES -------*/
    public function mes_nombre(){
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



    /*------- TIPO DE RESPONSABLE ----------*/
    public function tp_resp(){
      $ddep = $this->model_proyecto->dep_dist($this->dist);
      if($this->adm==1){
        $titulo='RESPONSABLE NACIONAL';
      }
      elseif($this->adm==2){
        $titulo='RESPONSABLE '.strtoupper($ddep[0]['dist_distrital']);
      }

      return $titulo;
    }
}
?>