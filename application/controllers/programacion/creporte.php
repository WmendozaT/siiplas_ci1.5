<?php
class Creporte extends CI_Controller { 
  public function __construct (){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null){
            $this->load->library('pdf2');
            $this->load->model('programacion/model_proyecto');
            $this->load->model('programacion/model_faseetapa');
            $this->load->model('programacion/model_componente');
            $this->load->model('programacion/model_producto');
           
            $this->load->model('programacion/insumos/model_insumo');
            $this->load->model('mantenimiento/model_estructura_org');
            $this->load->model('mestrategico/model_objetivoregion');
            $this->load->model('mantenimiento/model_ptto_sigep');
            $this->load->model('menu_modelo');
            
            $this->load->model('Users_model','',true);
            $this->gestion = $this->session->userData('gestion');
            $this->adm = $this->session->userData('adm');
            $this->tp_adm = $this->session->userData('tp_adm');
            $this->dist = $this->session->userData('dist');
            $this->rol = $this->session->userData('rol_id');
            $this->dist_tp = $this->session->userData('dist_tp');
            $this->fun_id = $this->session->userdata("fun_id");
            $this->load->library('programacionpoa');

            }else{
                $this->session->sess_destroy();
                redirect('/','refresh');
            }
    }


    /*------ REPORTE - CARATULA ------*/
    public function presentacion_poa($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $data['oregional']=$this->verif_oregional($proy_id);
            $data['titulo']='Caratula POA';
            $data['contenido']='<hr><iframe id="ipdf" width="100%"  height="1000px;" src="'.base_url().'index.php/proy/presentacion/'.$data['proyecto'][0]['proy_id'].'"></iframe>';
            $this->load->view('admin/programacion/reportes/reporte_poa', $data);
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }
    }

    /*------ REPORTE - IDENTIFICACION ------*/
    public function datos_generales($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $data['oregional']=$this->verif_oregional($proy_id);
            $data['titulo']='Datos Generales';
            $data['contenido']='<hr><iframe id="ipdf" width="100%"  height="1000px;" src="'.base_url().'index.php/prog/rep_datos_unidad/'.$data['proyecto'][0]['act_id'].'"></iframe>';
            $this->load->view('admin/programacion/reportes/reporte_poa', $data);
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }
    }

    /*------ REPORTE - PROGRAMACION FISICA ------*/
    public function programacion_fisica($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $data['oregional']=$this->verif_oregional($proy_id);
            $data['titulo']='Programación Física';
            $data['contenido']=$this->mis_servicios_componentes($proy_id);
            
            $this->load->view('admin/programacion/reportes/reporte_poa', $data);
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }
    }

    /*------ MIS SERVICIOS - FUNCIONAMIENTO ------*/
    public function mis_servicios_componentes($proy_id){
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id);
        $fase = $this->model_faseetapa->get_id_fase($proy_id);
        $componente=$this->model_componente->componentes_id($fase[0]['id'],$proyecto[0]['tp_id']);
        if($proyecto[0]['tp_id']==1){
            $titulo='MIS COMPONENTES';
            $titulo_sub='COMPONENTE';
        }
        else{
            $titulo='MIS SERVICIOS';
            $titulo_sub='SERVICIO';
        }

        $tabla='';
        $tabla.='<div class="row"><br>';
        $tabla.='<article class="col-sm-12 col-md-12 col-lg-3">
                    <div class="jarviswidget" id="wid-id-0" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
                        <header>
                            <span class="widget-icon"></span>
                            <h2>'.$titulo.'</h2>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox">
                            </div>
                            <div class="widget-body">

                            <table class="table table-bordered" width="100%">
                                <thead>
                                    <tr style="height:45px;">
                                        <th style="width:1%;">#</th>
                                        <th style="width:15%;">'.$titulo_sub.'</th>
                                        <th style="width:5%;">VER. ACTIVIDADES</th>
                                        <th style="width:5%;">VER REQUERIMIENTOS</th>
                                    </tr>
                                </thead>
                                <tbody>';
                                $nro=0;
                                foreach($componente as $row){
                                    $nro++;
                                    $tabla.=
                                    '<tr>
                                        <td>'.$nro.'</td>
                                        <td>'.$row['com_componente'].'</td>
                                        <td><a href="#" class="btn btn-info enlace" name="'.$row['com_id'].'" id="'.strtoupper($row['com_componente']).'" id1="1" >Ver Actividades</a></td>
                                        <td><a href="#" class="btn btn-info enlace" name="'.$row['com_id'].'" id="'.strtoupper($row['com_componente']).'" id1="2" >Ver requerimientos</a></td>
                                    </tr>';
                                }
                                $tabla.='
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </article>
                <article class="col-sm-12 col-md-12 col-lg-9">
                    <div class="jarviswidget" id="wid-id-0" data-widget-colorbutton="false" data-widget-editbutton="false" data-widget-custombutton="false" data-widget-sortable="false">
                        <header>
                            <span class="widget-icon"></span>
                            <h2><div id="tp_rep"></div></h2>
                        </header>
                        <div>
                            <div class="jarviswidget-editbox">
                            </div>
                            <div class="widget-body">
                                <div id="content1"></div>
                            </div>
                        </div>
                    </div>
                </article>';
        $tabla.='</div>';
        return $tabla;
    }

    /*-------- GET REPORTES POAS ------------*/
    public function get_reportes_poa(){
      if($this->input->is_ajax_request() && $this->input->post()){
        $post = $this->input->post();
        $com_id = $this->security->xss_clean($post['com_id']); // com id
        $tp = $this->security->xss_clean($post['tp']); // tp 1:ope, 2:req

        $componente = $this->model_componente->get_componente($com_id,$this->gestion);
        $fase=$this->model_faseetapa->get_fase($componente[0]['pfec_id']);

       if($tp==1){
        $tabla='<hr><iframe id="ipdf" width="100%"  height="800px;" src="'.base_url().'index.php/prog/rep_operacion_componente/'.$com_id.'"></iframe>';
       }
       else{
        $tabla='<hr><iframe id="ipdf" width="100%"  height="800px;" src="'.base_url().'index.php/proy/orequerimiento_proceso/'.$fase[0]['proy_id'].'/'.$com_id.'"></iframe>';
       }

        $result = array(
            'respuesta' => 'correcto',
            'tabla'=>$tabla,
          );
          
        echo json_encode($result);
      }else{
          show_404();
      }
    }


    /*------ REPORTE - PROGRAMACION FINANCIERA ------*/
    public function programacion_financiera($proy_id){
        $data['proyecto'] = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);
        if(count($data['proyecto'])!=0){
            $data['menu']=$this->genera_menu($proy_id);
            $data['oregional']=$this->verif_oregional($proy_id);
            $data['titulo']='Programación Financiera';
            $data['contenido']='Contenido 2';
            $this->load->view('admin/programacion/reportes/reporte_poa', $data);
        }
        else{
            $this->session->set_flashdata('danger','ERROR !!!');
            redirect('admin/proy/list_proy');
        }
    }





    /*----- VERIFICA LA ALINEACION DE OBJETIVO REGIONAL -----*/
    public function verif_oregional($proy_id){
        $list_oregional=$this->model_objetivoregion->list_proyecto_oregional($proy_id);
        $tabla='';
        $nro=0;
        foreach($list_oregional as $row){
            $nro++;
            $tabla.='<h1> '.$nro.'.- OPERACIÓN REGIONAL : <small> '.$row['or_codigo'].'.- '.$row['or_objetivo'].'</small></h1>';
        }

        return $tabla;
    }

    

    /*----- REPORTE - CONSOLIDADO PARTIDAS X UNIDAD RESPONSABLE (2020 - 2022) -----*/
    public function consolidado_partida_reporte($partidas){
        $tabla='';
        //$partidas=$this->model_insumo->list_consolidado_partidas_componentes($com_id);

        $tabla.='
            <table cellpadding="0" cellspacing="0" class="tabla" border=0.1 style="width:70%;" align=center>
                <thead>
                    <tr style="font-size: 7px;height:12px;" bgcolor="#eceaea" align=center>
                        <th style="width:3%;"style="height:11px;">N°</th>
                        <th style="width:10%;">C&Oacute;DIGO</th>
                        <th style="width:50%;">DETALLE PARTIDA</th>
                        <th style="width:12%;">MONTO PROGRAMADO</th>
                    </tr>
                </thead>
                <tbody>';
                $nro=0; $total=0;
                    foreach ($partidas as $row){ 
                        $nro++; $total=$total+$row['monto'];
                        $tabla.=
                        '<tr style="font-size: 7px;">
                            <td style="width: 3%; height:11px; text-align: center">'.$nro.'</td>
                            <td style="width: 10%; text-align: center;font-size: 8px;"><b>'.$row['par_codigo'].'</b></td>
                            <td style="width: 50%; text-align: left;">'.$row['par_nombre'].'</td>
                            <td style="width: 12%; text-align: right;">'.number_format($row['monto'], 2, ',', '.').'</td>
                        </tr>';
                    }
            $tabla.=
                '</tbody>
                    <tr style="font-size: 7px;" bgcolor="#eceaea">
                        <td style="width: 50%; height:10px; text-align: left;" colspan=3><b>TOTAL PROGRAMADO </b></td>
                        <td style="width: 12%; text-align: right;"><b>'.number_format($total, 2, ',', '.').'</b></td>
                    </tr>
            </table>';

        return $tabla;
    }


    //// REPORTE FORMULARIO POA N 4 PDF 2026 - Unidad Responsable
    public function reporte_formulario4_unidadResponsable($com_id){
        $componente=$this->model_componente->get_componente($com_id,$this->gestion); /// GET COMP -> PROY -> APER
        if(count($componente)!=0){
            if($componente[0]['tp_id']==1){
                $pie_report='FORM_SPO_N4_'.$componente[0]['proy_nombre'].'-'.$componente[0]['serv_descripcion'].' '.$this->gestion;
            }
            else{
                $pie_report='FORM_SPO_N4_'.$componente[0]['tipo'].' '.$componente[0]['proy_nombre'].' '.$componente[0]['abrev'].'-'.$componente[0]['serv_descripcion'].' '.$this->gestion;
            }
            
            $listado=$this->programacionpoa->rep_formulario_N4_Uresponsable($componente); /// 2026
            $cabecera=$this->programacionpoa->cabecera($componente,4);
            $pie=$this->programacionpoa->pie_form($componente);
            
            $data['informacion'] = '
              <page orientation="landscape" backtop="67mm" backbottom="35mm" backleft="4mm" backright="4mm" pagegroup="new">
                <!-- Cabecera Institucional Inalterada -->
                <page_header>
                    <br><div class="verde"></div>
                    '.$cabecera.'
                </page_header>
                
                <!-- Pie de Página Fijo en la Base de la Hoja -->
                <page_footer>
                    <div style="width: 100%; display: block;">
                    '.$pie.'
                    </div>
                </page_footer>
                    '.$listado.'
              </page>';

              // 1. Capturamos el HTML estructurado de la vista en una variable
          $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4', $data, true); 

          // 2. Limpieza radical del búfer de CodeIgniter para que Chrome no rechace el PDF
          if (ob_get_length()) ob_clean();

          // 3. Importación segura del motor conversor usando la ruta física del servidor
          require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
          
          try {
              // Inicializamos en orientación horizontal ('L' de Landscape / Paysage) para que coincida con tu diseño
              $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
              $html2pdf->pdf->SetDisplayMode('fullpage');
              $html2pdf->writeHTML($html_reporte);
              
              // 4. Enviamos el flujo binario limpio directo al visor de Chrome
              $html2pdf->Output($pie_report. '.pdf', 'I');
          }
          catch(HTML2PDF_exception $e) {
              echo "Error al compilar el reporte: " . $e;
          }
          exit;
        }
        else{
            echo "Error !!!";
        }
    }


    public function reporte_formulario4_consolidado($proy_id){
        // 1. Ampliación y control de recursos de hardware en el servidor
        ini_set('memory_limit', '2048M'); 
        set_time_limit(1800); // 30 minutos de procesamiento máximo institucional
        
        // Limpieza preliminar del búfer de salida para proteger el binario del PDF
        if (ob_get_length()) ob_clean();

        $tabla = '';
        $data['mes'] = $this->mes_nombre();
        $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($proy_id); 

        if (count($proyecto) != 0) {
            $unidades_responsables = $this->model_componente->lista_UnidadesResponsables($proy_id); 
            $pie = $this->programacionpoa->pie_form($proyecto);
            
            if ($proyecto[0]['tp_id'] == 4) { 
                $tabla .= $this->programacionpoa->caratula_poa_gacorriente($proyecto);
                $data['pie_rep'] = $proyecto[0]['tipo'] . ' ' . $proyecto[0]['proy_nombre'] . ' ' . $proyecto[0]['abrev'] . '-' . $this->gestion;
            } else { 
                $proyecto_inv = $this->model_proyecto->get_id_proyecto($proy_id); 
                $tabla .= $this->programacionpoa->caratula_poa_pinversion($proyecto_inv);
                $data['pie_rep'] = $proyecto_inv[0]['proy_nombre'] . '-' . $this->gestion;
            }
            
            foreach ($unidades_responsables as $pr) {
                if ($this->model_producto->lista_productos($pr['com_id']) != 0) {
                    $componente = $this->model_componente->get_componente($pr['com_id'], $this->gestion);
                    $cabecera = $this->programacionpoa->cabecera($componente, 4);
                    $formulario_N4 = $this->programacionpoa->rep_formulario_N4_Uresponsable($componente);
                    
                    $cabecera_f5 = $this->programacionpoa->cabecera($componente, 5);

                    $lista_partidas = $this->model_insumo->list_consolidado_partidas_uResponsable($pr['com_id']);
                    $partidas = $this->consolidado_partida_reporte($lista_partidas);

                    $list_insumos = $this->model_insumo->list_requerimientos_uresponsable($pr['com_id']); 
                    $requerimientos = $this->programacionpoa->rep_formulario_N5_Uresponsable($list_insumos);

                    $tabla .= '
                    <page orientation="paysage" backtop="75mm" backbottom="35.5mm" backleft="5mm" backright="5mm" pagegroup="new">
                        <page_header>
                            <br><div class="verde"></div>
                            ' . $cabecera . '
                        </page_header>
                        <page_footer>
                            ' . $pie . '
                        </page_footer>
                        ' . $formulario_N4 . '
                    </page>';

                    if (count($list_insumos) != 0) {
                        $tabla .= '
                        <page backtop="75mm" backbottom="29mm" backleft="5mm" backright="5mm" pagegroup="new">
                            <page_header>
                                <br><div class="verde"></div>
                                ' . $cabecera_f5 . '
                            </page_header>
                            <page_footer>
                                ' . $pie . '
                            </page_footer>
                            ' . $requerimientos . '
                        </page>
                        <page orientation="portrait" backtop="80mm" backbottom="33mm" backleft="5mm" backright="5mm" pagegroup="new">
                            <page_header>
                                <br><div class="verde"></div>
                                ' . $cabecera_f5 . '
                            </page_header>
                            <page_footer>
                                ' . $pie . '
                            </page_footer>
                            ' . $partidas . '
                        </page>';
                    }

                    // Inyección y acople del módulo de programas bolsas distritales
                    $programas_bolsas = $this->model_proyecto->lista_programas_bolsas_distrital($proyecto[0]['dist_id']);
             
                    if (count($programas_bolsas) != 0) {
                        foreach ($programas_bolsas as $row) {
                            $get_prog_bolsa = array($row);
                            $lista_insumos = $this->model_insumo->lista_requerimientos_inscritos_en_programas_bosas($row['aper_id'], $pr['com_id']); 

                            if (count($lista_insumos) != 0) {
                                $requerimientos_bolsa = $this->programacionpoa->rep_formulario_N5_Uresponsable($lista_insumos);
                                $lista_partidas_bolsa = $this->model_insumo->list_consolidado_partidas_programas_boLsas_uresponsable($row['aper_id'], $pr['com_id']);
                                $partidas_bolsa = $this->consolidado_partida_reporte($lista_partidas_bolsa);
                                $cabecera_bolsa = $this->programacionpoa->cabecera_bolsa($get_prog_bolsa, $componente);

                                $tabla .= '
                                <page orientation="paysage" backtop="75mm" backbottom="35.5mm" backleft="5mm" backright="5mm" pagegroup="new">
                                    <page_header>
                                        <br><div class="verde"></div>
                                        ' . $cabecera_bolsa . '
                                    </page_header>
                                    <page_footer>
                                        ' . $pie . '
                                    </page_footer>
                                    ' . $requerimientos_bolsa . '
                                </page>
                                <page orientation="portrait" backtop="80mm" backbottom="33mm" backleft="5mm" backright="5mm" pagegroup="new">
                                    <page_header>
                                        <br><div class="verde"></div>
                                        ' . $cabecera_bolsa . '
                                    </page_header>
                                    <page_footer>
                                        ' . $pie . '
                                    </page_footer>
                                    ' . $partidas_bolsa . '
                                </page>';
                            } 
                        }                      
                    }
                }
            }

            $data['lista'] = $tabla;

            // 2. CAPTURA ASÍNCRONA: Guardamos la estructuración HTML en una variable
            $html_reporte = $this->load->view('admin/programacion/reportes/reporte_form4_consolidado', $data, true); 

            // 3. Vaciamos buffers internos remanentes de CodeIgniter para blindar el binario
            if (ob_get_length()) ob_clean();

            // 4. INSTANCIACIÓN DE COMPILACIÓN DESDE LA RUTA FÍSICA CORPORATIVA FCPATH
            require_once(FCPATH . 'assets/html2pdf-4.4.0/html2pdf.class.php');
            
            try {
                // Inicializamos por defecto en formato horizontal ('L') y hoja Carta (Letter)
                $html2pdf = new HTML2PDF('L', 'Letter', 'es', true, 'UTF-8', array(0, 0, 0, 0));
                $html2pdf->pdf->SetDisplayMode('fullpage');
                $html2pdf->writeHTML($html_reporte);
                
                // 5. ENVIAMOS EL FLUJO BINARIO REPARADO DIRECTO AL VISOR DE GOOGLE CHROME
                $html2pdf->Output('FORM_POA_' . $data['pie_rep'] . '.pdf', 'I');
            }
            catch(HTML2PDF_exception $e) {
                echo "Error crítico al compilar el reporte maestro consolidado: " . $e;
            }
            exit;
        } else {
            echo "Error !!! El código de unidad o proyecto especificado no registra datos activos.";
        }
    }


    //// REPORTE FORMULARIO POA N 5 ( REQUERIMIENTOS NORMAL) 2026
    public function reporte_formulario5($com_id){
        $componente=$this->model_componente->get_componente($com_id,$this->gestion);
        if(count($componente)!=0){
            //$proyecto = $this->model_proyecto->get_id_proyecto($componente[0]['proy_id']); //// DATOS PROYECTO
            $data['pie_rep']=$componente[0]['proy_nombre'].'-'.$componente[0]['serv_descripcion'].' '.$this->gestion;
            if($componente[0]['tp_id']==4){
                $data['pie_rep']=$componente[0]['tipo'].' '.$componente[0]['proy_nombre'].' '.$componente[0]['abrev'].'-'.$componente[0]['serv_descripcion'].' '.$this->gestion;
            }

            $cabecera=$this->programacionpoa->cabecera($componente,5);
            
            $lista_partidas=$this->model_insumo->list_consolidado_partidas_uResponsable($com_id);
            $partidas=$this->consolidado_partida_reporte($lista_partidas);
            $pie=$this->programacionpoa->pie_form($componente);

            $requerimientos='<b>SIN REQUERIMIENTOS PROGRAMADOS .</b>';
            $items=$this->model_insumo->list_requerimientos_uresponsable($com_id); //// nuevo
            if(count($items)!=0){
                $requerimientos=$this->programacionpoa->rep_formulario_N5_Uresponsable($items);
            }

            $tabla='';
            $tabla.='
                <page backtop="75mm" backbottom="22mm" backleft="5mm" backright="5mm" pagegroup="new">
                    <page_header>
                        <br><div class="verde"></div>
                        '.$cabecera.'
                    </page_header>
                    <page_footer>
                        '.$pie.'
                    </page_footer>
                    '.$requerimientos.'
                </page>

                <page orientation="portrait" backtop="80mm" backbottom="33mm" backleft="5mm" backright="5mm" pagegroup="new">
                    <page_header>
                        <br><div class="verde"></div>
                        '.$cabecera.'
                    </page_header>
                    <page_footer>
                        '.$pie.'
                    </page_footer>
                    '.$partidas.'
                </page>';

            $data['informacion']=$tabla;
            $this->load->view('admin/programacion/reportes/reporte_form5', $data);
        }
        else{
            echo "Error !!!";
        }
    }


    //// REPORTE FORMULARIO POA N 5 PARA PROGRAMAS BIENES Y SERVICIOS Y FORTALECIMIENTO BOLSA 2026 POR SEPARADADO
    public function reporte_prog_bolsa_formulario5($aper_id,$com_id){
        $get_programa=$this->model_proyecto->get_aper_programa($aper_id);
        $componente = $this->model_componente->get_componente($com_id,$this->gestion); /// datos de la unidad responsable
        $tabla='';

                $data['pie_rep']= $get_programa[0]['proy_nombre'].' '.$componente[0]['tipo'].' '.$componente[0]['proy_nombre'].' '.$componente[0]['abrev'].'-'.$componente[0]['serv_descripcion'].' '.$this->gestion;
                $pie=$this->programacionpoa->pie_form($componente);


                    $cabecera=$this->programacionpoa->cabecera_bolsa($get_programa,$componente);
                    $lista_insumos=$this->model_insumo->lista_requerimientos_inscritos_en_programas_bosas($aper_id,$com_id);

                    if(count($lista_insumos)!=0){
                        $requerimientos=$this->programacionpoa->rep_formulario_N5_Uresponsable($lista_insumos);
                        $lista_partidas=$this->model_insumo->list_consolidado_partidas_programas_boLsas_uresponsable($aper_id,$com_id);
                        $partidas=$this->consolidado_partida_reporte($lista_partidas);
                        
                        $tabla.='
                        <page orientation="paysage" backtop="75mm" backbottom="22mm" backleft="5mm" backright="5mm" pagegroup="new">
                            <page_header>
                                <br><div class="verde"></div>
                                '.$cabecera.'
                            </page_header>
                            <page_footer>
                                '.$pie.'
                            </page_footer>
                            '.$requerimientos.'
                        </page>

                        <page orientation="portrait" backtop="80mm" backbottom="33mm" backleft="5mm" backright="5mm" pagegroup="new">
                            <page_header>
                                <br><div class="verde"></div>
                                '.$cabecera.'
                            </page_header>
                            <page_footer>
                                '.$pie.'
                            </page_footer>
                            '.$partidas.'

                        </page>';
                    }


                $data['informacion']=$tabla;
               // echo $data['informacion'];
                $this->load->view('admin/programacion/reportes/reporte_form5', $data);

    }


    //// REPORTE FORMULARIO POA N 5 CONSOLIDADO DEL PROGRAMA BOLSA x UNIDAD RESPONSABLE
    public function reporte_formulario5_bolsas_consolidado($com_id){
        $componente=$this->model_componente->get_componente($com_id,$this->gestion);
        $programas_bolsas=$this->model_proyecto->lista_programas_bolsas_distrital($componente[0]['dist_id']);
        $pie=$this->programacionpoa->pie_form($componente);
        $data['pie_rep']='BOLSAS '.$this->gestion;

        $tabla='';
        if(count($programas_bolsas)!=0){
            foreach($programas_bolsas as $row){
                $get_prog_bolsa=[];
                $get_prog_bolsa[]=$row;
                $lista_insumos=$this->model_insumo->lista_requerimientos_inscritos_en_programas_bosas($row['aper_id'],$com_id); /// lista de requerimientos

                if(count($lista_insumos)!=0){
                    $requerimientos=$this->programacionpoa->rep_formulario_N5_Uresponsable($lista_insumos);
                    $lista_partidas=$this->model_insumo->list_consolidado_partidas_programas_boLsas_uresponsable($row['aper_id'],$com_id);
                    $partidas=$this->consolidado_partida_reporte($lista_partidas);
                    $cabecera=$this->programacionpoa->cabecera_bolsa($get_prog_bolsa,$componente);

                    $tabla.='
                    <page orientation="paysage" backtop="75mm" backbottom="35.5mm" backleft="5mm" backright="5mm" pagegroup="new">
                        <page_header>
                        <br><div class="verde"></div>
                        '.$cabecera.'
                        </page_header>
                        <page_footer>
                        '.$pie.'
                        </page_footer>
                        '.$requerimientos.'
                    </page>
                    <page orientation="portrait" backtop="80mm" backbottom="33mm" backleft="5mm" backright="5mm" pagegroup="new">
                        <page_header>
                        <br><div class="verde"></div>
                        '.$cabecera.'
                        </page_header>
                        <page_footer>
                        '.$pie.'
                        </page_footer>
                        '.$partidas.'
                    </page>';
                } 
            }                      
        }

        $data['informacion']=$tabla;
        $this->load->view('admin/programacion/reportes/reporte_form5', $data);
    }






















    /*------- REPORTE CONSOLIDADO PRESUPUESTO POR PARTIDAS (2020) ------*/
/*    public function reporte_presupuesto_consolidado($proy_id){
        $data['mes'] = $this->mes_nombre();
        $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); /// PROYECTO
        if(count($proyecto)!=0){
            $data['pie_rep']=$proyecto[0]['proy_nombre'].' '.$this->gestion;
            if($proyecto[0]['tp_id']==4){
                $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($proy_id); /// PROYECTO
                $data['pie_rep']=$proyecto[0]['tipo'].' '.$proyecto[0]['act_descripcion'].' '.$proyecto[0]['abrev'].' '.$this->gestion;
                $proyecto = $this->model_proyecto->get_datos_proyecto_unidad($proy_id);    
            }
            
            $data['cabecera']=$this->programacionpoa->cabecera($proyecto[0]['tp_id'],0,$proyecto,0);
            $data['consolidado']=$this->consolidado_ptto_reporte($proyecto);
            $data['pie']=$this->programacionpoa->pie_form($proyecto);
            $this->load->view('admin/programacion/reportes/reporte_consolidado_presupuesto', $data);
        }
        else{
            echo "<b>ERROR !!!!!</b>";
        }
    }*/


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