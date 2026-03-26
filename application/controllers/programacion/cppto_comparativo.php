<?php
class Cppto_comparativo extends CI_Controller {  
  public function __construct (){
      parent::__construct();
      if($this->session->userdata('fun_id')!=null){
      $this->load->library('pdf2');
      $this->load->model('menu_modelo');
      $this->load->model('Users_model','',true);
      $this->load->model('programacion/model_faseetapa');
      $this->load->model('programacion/model_proyecto');
      $this->load->model('programacion/model_componente');
      $this->load->model('programacion/model_producto');
    //  $this->load->model('programacion/model_actividad');
    //  $this->load->model('mantenimiento/mapertura_programatica');
    //  $this->load->model('mantenimiento/munidad_organizacional');
    //  $this->load->model('mantenimiento/model_estructura_org');
      $this->load->model('programacion/insumos/minsumos');
      $this->load->model('programacion/insumos/model_insumo');
    //  $this->load->model('mestrategico/model_objetivoregion');
      $this->load->model('mantenimiento/model_ptto_sigep');
      $this->gestion = $this->session->userData('gestion');
      $this->adm = $this->session->userData('adm'); // 1: Nacional, 2: Regional, Distrital
      $this->dist = $this->session->userData('dist');
      $this->rol = $this->session->userData('rol_id');
      $this->fun_id = $this->session->userdata("fun_id");
      $this->tp_adm = $this->session->userdata("tp_adm");
      $this->mes = $this->mes_nombre();
      $this->dist_tp = $this->session->userData('dist_tp'); /// dist_tp->1 Regional, dist_tp->0 Distritales
      $this->verif_ppto = $this->session->userData('verif_ppto'); /// AnteProyecto Ptto POA : 0, Ptto Aprobado Sigep : 1
      $this->load->library('programacionpoa');
      }else{
          $this->session->sess_destroy();
          redirect('/','refresh');
      }
    }

    //// ====== COMPARATIVO POR REGIONAL
    /*----- REPORTE COMPARATIVO DE PARTIDAS (ASIG - PROG) ----*/
/*    public function reporte_presupuesto_consolidado_comparativo_regional($dep_id,$tp_id){
      $data['mes'] = $this->mes_nombre();
      $data['regional']=$this->model_proyecto->get_departamento($dep_id);
      $data['titulo_reporte']='CONSOLIDADO_PARTIDAS_NACIONAL';
      if($dep_id!=0){
        $data['titulo_reporte']=strtoupper($data['regional'][0]['dep_departamento']);
      }

      $titulo='CONSOLIDADO INSTITUCIONAL';
      if($dep_id!=0){
        $titulo=strtoupper($data['regional'][0]['dep_departamento']);
      }

      $data['cabecera']='
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
        <tr style="border: solid 0px;">              
            <td style="width:70%;height: 2%">
                <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                    <tr style="font-size: 13px;font-family: Arial;">
                        <td style="width:40%;height: 20%;">&nbsp;&nbsp;<b> '.$this->session->userData('entidad').'</b></td>
                    </tr>
                    <tr>
                        <td style="width:50%;height: 20%;font-size: 8px;">&nbsp;&nbsp;DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</td>
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
            <td style="width:100%; height: 5%">
              <table align="center" border="0" style="width:100%;">
                  <tr style="font-size: 27px;font-family: Arial;">
                      <td style="height: 30%;"><b>PLAN OPERATIVO ANUAL GESTIÓN - '.$this->gestion.'</b></td>
                  </tr>
                  <tr style="font-size: 17px;font-family: Arial;">
                    <td style="height: 5%;">CUADRO CONSOLIDADO POA - PRESUPUESTO</td>
                  </tr>
                  <tr style="font-size: 25px;font-family: Arial;">
                    <td style="height: 5%;"><b>'.$titulo.'</b></td>
                  </tr>
              </table>
            </td>
          </tr>
      </table>
      <hr>';

      $tabla='';
      if(count($data['regional'])!=0){
        $suma_ppto_asig=0;$suma_ppto_prog=0;

        /// ppto asignado
        $suma_ppto_asignado=$this->model_ptto_sigep->sum_ppto_asignado_regional($dep_id); //// Suma Ptto Asignado por Regional
        if(count($suma_ppto_asignado)!=0){
          $suma_ppto_asig=$suma_ppto_asignado[0]['asignado'];
        }

        /// ppto programado
        $suma_ppto_programado=$this->model_ptto_sigep->sum_ppto_programado_regional($dep_id,$tp_id); //// Suma Ptto Programado por Regional
        if(count($suma_ppto_programado)!=0){
          $suma_ppto_prog=$suma_ppto_programado[0]['programado'];
        }

        /// Lista de partidas Asig, prog
        $partidas_asig=$this->model_ptto_sigep->lista_partidas_asignado_regional($dep_id,$tp_id); /// Lista de Partidas Asignadas
        $partidas_prog=$this->model_ptto_sigep->lista_partidas_programado_regional($dep_id,$tp_id); /// Lista de Partidas Programado


        /// comparando suma de presupuesto
        if($suma_ppto_asig==$suma_ppto_prog){ /// igualdad en los techos
          $data['tabla'] = $this->comparativo_partidas_normal_regional($partidas_prog,$dep_id);
        }
        else{
          //$data['tabla'] = 'REGIONAL NO AJUSTADO';
          $data['tabla'] =$this->comparativo_update_partidas_normal_regional($partidas_asig,$partidas_prog,$dep_id);
        }
        echo $data['tabla'];
        //$data['tabla']=$tabla;

       // $this->load->view('admin/programacion/reportes/reporte_consolidado_presupuesto_comparativo_regional', $data);
      }
      else{
          echo "<b>ERROR !!!!!</b>";
      }
    }*/



    //// ====== DISTRIBUCION PRESUPUESTO NACIONAL
    /*----- REPORTE COMPARATIVO distribucion nacional Presupuesto ----*/
    /*public function reporte_presupuesto_consolidado_distribucion_nacional(){
      $data['mes'] = $this->mes_nombre();

      $data['cabecera']='
      <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
        <tr style="border: solid 0px;">              
            <td style="width:70%;height: 2%">
              <table border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;">
                <tr style="font-size: 13px;font-family: Arial;">
                    <td style="width:40%;height: 20%;">&nbsp;&nbsp;<b> '.$this->session->userData('entidad').'</b></td>
                </tr>
                <tr>
                    <td style="width:50%;height: 20%;font-size: 8px;">&nbsp;&nbsp;DEPARTAMENTO NACIONAL DE PLANIFICACIÓN</td>
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
            <td style="width:100%; height: 5%">
              <table align="center" border="0" style="width:100%;">
                <tr style="font-size: 27px;font-family: Arial;">
                    <td style="height: 30%;"><b>PLAN OPERATIVO ANUAL GESTIÓN - '.$this->gestion.'</b></td>
                </tr>
                <tr style="font-size: 17px;font-family: Arial;">
                  <td style="height: 5%;">CUADRO CONSOLIDADO POA - PRESUPUESTO</td>
                </tr>
                <tr style="font-size: 25px;font-family: Arial;">
                  <td style="height: 5%;"><b>PPTO. CONSOLIDADO INSTITUCIONAL</b></td>
                </tr>
              </table>
            </td>
          </tr>
      </table>
      <hr>';

      $tabla='';
      $ppto_asignado_regional=$this->model_ptto_sigep->lista_ppto_total_asignado_nacional();

      $tabla .='
      <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;" align=center>
        <thead>
          <tr style="height:15px;" bgcolor="#eceaea" align=center>
            <th style="width:3%;height:13px;" align=center>#</th>
            <th style="width:10%;">REGIONAL</th>
            <th style="width:15%;">PPTO. ASIGNADO '.$this->gestion.'</th>
            <th style="width:15%;">PRESUPUESTO POA (SIIPLAS)</th>
            <th style="width:15%;">SALDO POA</th></tr>
        </thead>
        <tbody>';

        $nro=0;$ppto_asig=0;$ppto_prog=0;
        foreach($ppto_asignado_regional as $row){
          $get_ppto_prog=$this->model_ptto_sigep->get_ppto_total_programado_regional($row['dep_id']); /// ppto programado
          $monto_programado=0;

          if(count($get_ppto_prog)!=0){
            $monto_programado=$get_ppto_prog[0]['programado'];
          }

          $nro++;
          $tabla.='
          <tr>
            <td style="width:3%;height:15px;" align=center>'.$nro.'</td>
            <td style="width: 20%;">'.strtoupper($row['dep_departamento']).'</td>
            <td style="width: 12%; text-align: right;">'.number_format($row['asignado'], 2, ',', '.').'</td>
            <td style="width: 12%; text-align: right;">'.number_format($monto_programado, 2, ',', '.').'</td>
            <td style="width: 12%; text-align: right;">'.number_format(($row['asignado']-$monto_programado), 2, ',', '.').'</td>
          </tr>';
          $ppto_asig=$ppto_asig+$row['asignado'];
          $ppto_prog=$ppto_prog+$monto_programado;
        }
        $tabla.='
          <tr>
            <td></td>
            <td style="height:13px;" align=right><b>TOTAL</b></td>
            <td style="width: 12%; text-align: right;"><b>'.number_format($ppto_asig, 2, ',', '.').'</b></td>
            <td style="width: 12%; text-align: right;"><b>'.number_format($ppto_prog, 2, ',', '.').'</b></td>
            <td style="width: 12%; text-align: right;"><b>'.number_format(($ppto_asig-$ppto_prog), 2, ',', '.').'</b></td>
          </tr>
        </tbody>
      </table>';

      $data['titulo_reporte']='CONSOLIDADO_PPTO_ASIGNADO_PROGRAMADO_INSTITUCIONAL';
      $data['tabla']=$tabla;

        $this->load->view('admin/programacion/reportes/reporte_consolidado_presupuesto_comparativo_regional', $data);

    }*/









    //// ====== COMPARATIVO POR UNIDAD ORGANIZACIONAL
    /*-------- GET CUADRO COMPARATIVO ASIGNADO-POA --------*/
    public function get_cuadro_comparativo_ptto(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $proy_id = $this->security->xss_clean($post['proy_id']);
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO

        $tabla='Actualizando';

        $result = array(
          'respuesta' => 'correcto',
          'tabla'=>$tabla,
        );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }



    /*----- REPORTE COMPARATIVO DE PARTIDAS (ASIG - PROG) ----*/
    public function reporte_presupuesto_consolidado_comparativo_programa($proy_id){
      $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
      $cabecera=$this->programacionpoa->cabecera($proyecto,0);
      $lista_partidas=$this->model_ptto_sigep->get_lista_ppto_partidas_UOrganizacional($proyecto[0]['aper_id']);
      $partidas='';
      $partidas.='
                <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:80%;" align=center>
                  <thead>
                      <tr style="font-size: 8px;height:12px;" bgcolor="#eceaea" align=center>
                          <th style="width:5%;"style="height:11px;">N°</th>
                          <th style="width:10%;">C&Oacute;DIGO</th>
                          <th style="width:30%;">DETALLE PARTIDA</th>
                          <th style="width:12%;">PPTO. ASIGNADO '.$this->gestion.'</th>
                          <th style="width:12%;">PPTO. PROGRAMADO POA '.$this->gestion.'</th>
                          <th style="width:12%;">SALDO POA</th>
                          <th style="width:12%;">PPTO. ASIGNADO REVERTIDO</th>
                          <th style="width:12%;">PPTO. PROGRAMADO REVERTIDO</th>
                          <th style="width:12%;">SALDO REVERSION</th>
                      </tr>
                  </thead>
                  <tbody>';
                  $nro=0;
                  $suma_ppto_asignado=0;
                  $suma_ppto_programado=0;
                  $suma_saldo_poa=0;
                  $suma_ppto_asignado_revertido=0;
                  $suma_ppto_programado_revertido=0;
                  $suma_saldo_revertido=0;
                  foreach($lista_partidas as $row){

                    $nro++;
                    $suma_ppto_asignado=$suma_ppto_asignado+$row['ppto_asignado'];
                    $suma_ppto_programado=$suma_ppto_programado+$row['ppto_programado'];
                    $suma_saldo_poa=$suma_saldo_poa+$row['saldo_poa'];
                    $suma_ppto_asignado_revertido=$suma_ppto_asignado_revertido+$row['ppto_asignado_revertido'];
                    $suma_ppto_programado_revertido=$suma_ppto_programado_revertido+$row['ppto_programado_revertido'];
                    $suma_saldo_revertido=$suma_saldo_revertido+$row['saldo_revertido'];

                    
                    if($row['estado_poa']==0){
                      $text_color='#ffffff';
                    }
                    elseif($row['estado_poa']==3){
                      $text_color='#e39595';
                    }
                    else{
                      $text_color='#dbedbd';
                    }

                    $partidas.='
                    <tr style="font-size: 8px;">
                      <td style="height:10px;"text-align:center">'.$nro.'</td>
                      <td style="text-align:center"><b>'.$row['codigo_partida'].'</b></td>
                      <td>'.$row['partida'].'</td>
                      <td style="text-align:right">'.number_format($row['ppto_asignado'], 2, ',', '.').'</td>
                      <td style="text-align:right">'.number_format($row['ppto_programado'], 2, ',', '.').'</td>
                      <td style="text-align:right; background-color:'.$text_color.'"><b>'.number_format($row['saldo_poa'], 2, ',', '.').'</b></td>
                      <td style="text-align:right; background-color:#e9f5f5">'.number_format($row['ppto_asignado_revertido'], 2, ',', '.').'</td>
                      <td style="text-align:right; background-color:#e9f5f5">'.number_format($row['ppto_programado_revertido'], 2, ',', '.').'</td>
                      <td style="text-align:right; background-color:#e9f5f5">'.number_format($row['saldo_revertido'], 2, ',', '.').'</td>
                    </tr>';

                  }
            $partidas.='
                  <tr>
                      <td style="height:12px;"text-align:right" colspan=3>TOTAL</td>
                      <td style="text-align:right"><b>'.number_format($suma_ppto_asignado, 2, ',', '.').'</b></td>
                      <td style="text-align:right"><b>'.number_format($suma_ppto_programado, 2, ',', '.').'</b></td>
                      <td style="text-align:right"><b>'.number_format($suma_saldo_poa, 2, ',', '.').'</b></td>
                      <td style="text-align:right; background-color:#e9f5f5"><b>'.number_format($suma_ppto_asignado_revertido, 2, ',', '.').'</b></td>
                      <td style="text-align:right; background-color:#e9f5f5"><b>'.number_format($suma_ppto_programado_revertido, 2, ',', '.').'</b></td>
                      <td style="text-align:right; background-color:#e9f5f5"><b>'.number_format($suma_saldo_revertido, 2, ',', '.').'</b></td>
                  </tr>
                  </tbody>
                </table>';

      $tabla='';
      $tabla.='
      <page backtop="75mm" backbottom="35.5mm" backleft="5mm" backright="5mm" pagegroup="new">
          <page_header>
              <br><div class="verde"></div>
              '.$cabecera.'
          </page_header>
          <page_footer>
           
          </page_footer>
          '.$partidas.'
      </page>';

      $data['lista_partidas']=$tabla;
      $data['pie_rep']='PPTO_POA';
      $this->load->view('admin/programacion/reportes/reporte_consolidado_presupuesto', $data);
    }




    /*----- REPORTE COMPARATIVO DE PARTIDAS (ASIG - PROG) a borrar ----*/
      public function reporte_presupuesto_consolidado_comparativo2($proy_id){
      $data['mes'] = $this->mes_nombre();
      $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
      $data['cabecera']=$this->programacionpoa->cabecera($data['proyecto'],0);
      //$data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO
      if(count($data['proyecto'])!=0){
          $monto_asignado=0;$monto_programado=0;
          $cod_part_asig=$this->model_ptto_sigep->sum_codigos_partidas_asig_prog($data['proyecto'][0]['aper_id'],1);  //// suma codigo de partidas asignadas
          
          if(count($cod_part_asig)!=0){
            $monto_asignado=$cod_part_asig[0]['sum_cod_partida'];
          }

          $cod_part_prog=$this->model_ptto_sigep->sum_codigos_partidas_asig_prog($data['proyecto'][0]['aper_id'],2); //// suma codigo de partidas programadas
          $suma_codigos_prog=0;
          if(count($cod_part_prog)!=0){
            $suma_codigos_prog=$cod_part_prog[0]['sum_cod_partida'];
          }


          ///----- Genera lista de Partidas Asignadas y Programadas
          $partidas_asig=$this->model_ptto_sigep->partidas_accion_region($data['proyecto'][0]['dep_id'],$data['proyecto'][0]['aper_id'],1); // Asig
          $partidas_prog=$this->model_ptto_sigep->partidas_accion_region($data['proyecto'][0]['dep_id'],$data['proyecto'][0]['aper_id'],2); // Prog


          if($monto_asignado==$suma_codigos_prog){ //// if (monto asignado = monto programado)
            $data['tabla'] = $this->comparativo_partidas_normal($partidas_asig,$partidas_prog,$data['proyecto'],1);
          }
          else{ /// Cuando existen diferencias en las partidas asignadas con las programas
            $data['tabla'] = $this->comparativo_update_partidas_normal($partidas_asig,$partidas_prog,$data['proyecto'],1);
          }

            $data['titulo']='<div align="center">PLAN OPERATIVO ANUAL '.$this->gestion.' - PROGRAMACI&Oacute;N F&Iacute;SICO FINANCIERO <br><b>CONSOLIDADO CUADRO COMPARATIVO DE PRESUPUESTO (ANTEPROYECTO - POA)</b></div>';
            if($data['proyecto'][0]['proy_estado']==4){
              $data['titulo']='<div align="center">PLAN OPERATIVO ANUAL '.$this->gestion.' - PROGRAMACI&Oacute;N F&Iacute;SICO FINANCIERO <br><b>CONSOLIDADO CUADRO COMPARATIVO DE PRESUPUESTO FINAL (APROBADO - ANTEPROYECTO)</b></div>';
            }

/*            echo "proy_id : ".$proy_id." ------ ".$data['proyecto'][0]['proy_id']."<br>";
            echo "aper_id : ".$data['proyecto'][0]['aper_id'];
            echo "<br>";
            echo count($cod_part_asig);
            echo "<br>";
            echo $monto_asignado.'---'.$cod_part_prog[0]['sum_cod_partida'];
            echo "<br>";
            echo count($partidas_asig).'---'.count($partidas_prog);*/
            // echo $monto_asignado;
          $this->load->view('admin/programacion/reportes/reporte_consolidado_presupuesto_comparativo', $data);
      }
      else{
          echo "<b>ERROR !!!!!</b>";
      }
    }

  

////=======================
/*---- COMPARATIVO DE PARTIDAS A NIVEL DE REGIONAL  (las partidas no cambian)---*/
  public function comparativo_partidas_normal_regional($partidas_prog,$dep_id){ 
    $tabla ='';
    $tabla .='
      <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;" align=center>
        <thead>
          <tr style="height:15px;" bgcolor="#eceaea" align=center>
            <th style="width:3%;height:13px;" align=center>#</th>
            <th style="width:10%;">C&Oacute;DIGO PARTIDA</th>
            <th style="width:25%;">DETALLE PARTIDA</th>
            <th style="width:15%;">PPTO. ASIGNADO '.$this->gestion.'</th>
            <th style="width:15%;">PRESUPUESTO POA (SIIPLAS)</th>
            <th style="width:15%;">SALDO POA</th></tr>
        </thead>
        <tbody>';

    $nro=0;
    $monto_asig=0;
    $monto_prog=0;
    foreach($partidas_prog as $row){
      $part=$this->model_ptto_sigep->get_partida_asig_regional($dep_id,$row['par_id']); /// get partida asignado
        $asig=0;
        if(count($part)!=0){
          $asig=$part[0]['asignado'];
        }
        $dif=($asig-$row['programado']);

        $color='';
          $sig='';
          if($dif!=0){
            if($dif<0){
              $color='#f9cdcd';
            }
            else{
              $color='#e5efd7';
              $sig='+';
            }
          }

      $nro++;
      $tabla .='<tr  bgcolor='.$color.'>
                  <td style="width: 3%;height:11px; text-align: center">'.$nro.'</td>
                  <td style="width: 10%; text-align: center;">'.$row['par_codigo'].'</td>
                  <td style="width: 25%; text-align: left;">'.$row['par_nombre'].'</td>
                  <td style="width: 15%; text-align: right;">'.number_format($asig, 2, ',', '.').'</td>
                  <td style="width: 15%; text-align: right;">'.number_format($row['programado'], 2, ',', '.').'</td>
                  <td style="width: 15%; text-align: right;">'.$sig.''.number_format($dif, 2, ',', '.').'</td>';
              $tabla.='</tr>';
      $monto_asig=$monto_asig+$asig;
      $monto_prog=$monto_prog+$row['programado'];
    }

    $dif=($monto_asig-$monto_prog);
    $color='#f1f1f1';
    $sig='';
    if($dif!=0){
      if($dif<0){
        $color='#f9cdcd';
      }
      else{
        $color='#e5efd7';
        $sig='+';
      }
    }

    $tabla .='
      </tbody>
        <tr  bgcolor="'.$color.'">
            <td colspan=3 style="height:11px;"><strong>TOTAL</strong></td>
            <td align=right>'.number_format($monto_asig, 2, ',', '.').'</td>
            <td align=right>'.number_format($monto_prog, 2, ',', '.').'</td>
            <td align=right>'.$sig.''.number_format($dif, 2, ',', '.').'</td>
          </tr>
      </table>';

    return $tabla;
  }

  /*---- COMPARATIVO DE PARTIDAS A NIVEL REGIONAL (las partidas cambian)---*/
    public function comparativo_update_partidas_normal_regional($partidas_asig,$partidas_prog,$dep_id){
      $tabla ='';
      $tabla .='
      <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:100%;" align=center>
        <thead>
          <tr style="height:13px;" bgcolor="#eceaea" align=center>
            <th style="width:3%;" align=center># ASIG</th>
            <th style="width:10%;">C&Oacute;DIGO PARTIDA</th>
            <th style="width:35%;">DETALLE PARTIDA</th>
            <th style="width:12%;">PPTO. ASIGNADO '.$this->gestion.'</th>
            <th style="width:12%;">PRESUPUESTO POA (SIIPLAS)</th>
            <th style="width:12%;">SALDO POA</th>
          </tr>
        </thead>
        <tbody>';
        $nro=0;
        $monto_asig=0;
        $monto_prog=0;

        foreach($partidas_asig  as $row){
          $part=$this->model_ptto_sigep->get_partidas_programado_regional($dep_id,4,$row['par_id']);
          
            $prog=0;
            if(count($part)!=0){
              $prog=$part[0]['programado'];
            }
            $dif=(($row['asignado']+$row['saldo'])-$prog);
           
           $color='';
            $sig='';
            if($dif!=0){
              if($dif<0){
                $color='#f9cdcd';
              }
              else{
                $color='#e5efd7';
                $sig='+';
              }
            }

            $nro++;
            $tabla .='
              <tr class="modo1" bgcolor='.$color.'> 
                <td style="width: 3%;height:11px; text-align: center">'.$nro.'</td>
                <td style="width: 10%; text-align: center;">'.$row['par_codigo'].'</td>
                <td style="width: 35%; text-align: left;">'.$row['par_nombre'].'</td>
                <td style="width: 12%; text-align: right;">'.number_format($row['asignado'], 2, ',', '.').'</td>
                <td style="width: 12%; text-align: right;">'.number_format($prog, 2, ',', '.').'</td>
                <td style="width: 12%; text-align: right;">'.$sig.''.number_format($dif, 2, ',', '.').'</td>
              </tr>';
            $monto_asig=$monto_asig+($row['asignado']+$row['saldo']);
            $monto_prog=$monto_prog+$prog; 
        }

        foreach($partidas_prog as $row){
          $part=$this->model_ptto_sigep->get_partida_asig_regional($dep_id,$row['par_id']); /// get partida asignado
           if(count($part)==0){ 
            $asig=0;
            if(count($part)!=0){
              $asig=($part[0]['asignado']-$part[0]['saldo']);
            }
            $dif=($asig-$row['programado']);
            $color='';
            $sig='';
            if($dif!=0){
              if($dif<0){
                $color='#f9cdcd';
              }
              else{
                $color='#e5efd7';
                $sig='+';
              }
            }
            
          $nro++;
          $tabla .='<tr class="modo1" bgcolor='.$color.'>
                      <td style="width: 3%;height:11px; text-align: center">'.$nro.'</td>
                      <td style="width: 10%; text-align: center;">'.$row['par_codigo'].'</td>
                      <td style="width: 35%; text-align: left;">'.$row['par_nombre'].'</td>
                      <td style="width: 15%; text-align: right;">'.number_format($asig, 2, ',', '.').'</td>
                      <td style="width: 15%; text-align: right;">'.number_format($row['programado'], 2, ',', '.').'</td>
                      <td style="width: 15%; text-align: right;">'.$sig.''.number_format($dif, 2, ',', '.').'</td>';
                if($this->tp_adm==1){
                  $tabla.='<td style="width: 10%; text-align: right;"></td>';
                }
                $tabla.='
                    </tr>';
          $monto_asig=$monto_asig+$asig;
          $monto_prog=$monto_prog+$row['programado'];
          }  
        }


        $dif=($monto_asig-$monto_prog);
        $color='#f1f1f1';
        $sig='';
        if($dif!=0){
          if($dif<0){
            $color='#f9cdcd';
          }
          else{
            $color='#e5efd7';
            $sig='+';
          }
        }

       $tabla.='
        </tbody>
          <tr class="modo1" bgcolor='.$color.'>
              <td colspan=3 style="height:11px;"><strong>TOTAL</strong></td>
              <td align=right><b>'.number_format($monto_asig, 2, ',', '.').'</b></td>
              <td align=right><b>'.number_format($monto_prog, 2, ',', '.').'</b></td>
              <td align=right><b>'.$sig.''.number_format($dif, 2, ',', '.').'</b></td>
              
            </tr>
        </table>';

      return $tabla;
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