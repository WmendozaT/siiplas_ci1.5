<?php
class User extends CI_Controller{
    public function __construct(){
        parent::__construct();
        $this->load->model('Users_model', '', true);
        $this->load->model('mantenimiento/model_funcionario');
        //$this->load->model('programacion/model_faseetapa');
        $this->load->model('programacion/model_proyecto');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('mantenimiento/model_configuracion');
        $this->load->model('ejecucion/model_seguimientopoa');
        $this->load->model('programacion/model_producto');
        $this->load->model('ejecucion/model_notificacion');
        $this->load->model('mantenimiento/model_estructura_org');
        $this->load->model('programacion/model_componente');
        $this->load->model('ejecucion/model_certificacion');
        $this->load->model('mantenimiento/model_ptto_sigep');
        //$this->load->model('reportes/mreporte_operaciones/mrep_operaciones');
        $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
        //$this->load->model('model_control_menus');
        $this->load->library('session');
        $this->load->library('encrypt');
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion'); /// Gestion
        $this->ppto_poa = $this->session->userData('ppto_poa'); /// PPTO
        $this->adm = $this->session->userData('adm');
        $this->dist_id = $this->session->userData('dist');
        $this->dep_id = $this->session->userData('dep_id');
        $this->rol = $this->session->userData('rol_id');
        $this->dist_tp = $this->session->userData('dist_tp');
        $this->fun_id = $this->session->userData('fun_id'); ///
        $this->conf_pei = $this->session->userData('conf_pei'); /// Conf Pei

        $this->modulo = $this->session->userdata('modulos');
        $this->tp_adm = $this->session->userdata("tp_adm");
        $this->tmes = $this->session->userData('trimestre');

        $this->notificaciones=$this->session->userData('estado_notificaciones');
        $this->verif_mes=$this->session->userdata('mes_actual');
        $this->conf_ajuste_poa=$this->session->userdata('conf_ajuste_poa'); 
        $this->conf_credenciales=$this->session->userdata('conf_psw'); /// configuracion de credenciales
        $this->fun_credencial=$this->session->userdata('credencial_funcionario'); /// credenciales del funcionario
        $this->load->library('dashboard');
    }

    public function vaca_404(){
        $this->load->view('rewriten_404');
    }
    






    /*-------- Valida Cambio gestion Session -----------*/
    public function cambiar_gestion(){
        $conf=$this->model_proyecto->get_configuracion($this->input->post('gestion_usu'));

         $data = array(
                'gestion' => $conf[0]['ide'],
                'mes' => $conf[0]['conf_mes'],
                'mes_actual'=>$this->verif_mes_gestion($conf[0]['conf_mes']),
                'estado_notificaciones' => $conf[0]['conf_poa'], /// Estado para las Notificaciones 0:no activo, 1: Habilitado
                'conf_estado' => $conf[0]['conf_estado'], //7 Estado 1: Activo, 0: No activo
                'conf_poa_estado' => $conf[0]['conf_poa_estado'], //7 Estado poa-presupuesto 1: inicial, 2 ajustado, 3 aprobado
                'trimestre' => $conf[0]['conf_mes_otro'], /// Trimestre 1,2,3,4
                'tr_id' => ($conf[0]['conf_mes_otro']+$conf[0]['conf_mes_otro']*2), /// Trimestre 3,6,9,12
                'desc_mes' => $this->mes_texto($conf[0]['conf_mes']),
                'verif_ppto' => $conf[0]['ppto_poa'], /// Ppto poa : 0 (Vigente), 1: (Aprobado)
                'conf_form3' => $conf[0]['conf_form3'], /// Estado de Registro del formulario N3, 0 (Inactivo), 1 (Activo)
                'conf_form4' => $conf[0]['conf_form4'], /// Estado de Registro del formulario N4, 0 (Inactivo), 1 (Activo)
                'conf_form5' => $conf[0]['conf_form5'], /// Estado de Registro del formulario N5, 0 (Inactivo), 1 (Activo)
                'conf_mod_ope' => $conf[0]['conf_mod_ope'], /// Estado de Modificacion del formulario N4, 0 (Inactivo), 1 (Activo)
                'conf_mod_req' => $conf[0]['conf_mod_req'], /// Estado de Modificacion del formulario N5, 0 (Inactivo), 1 (Activo)
                'conf_certificacion' => $conf[0]['conf_certificacion'], /// Estado de Certificacion del formulario N5, 0 (Inactivo), 1 (Activo)
                'rd_poa' => $conf[0]['rd_aprobacion_poa'], /// Ppto poa : 0 (Vigente), 1: (Aprobado)
                'conf_ajuste_poa' => $conf[0]['conf_ajuste_poa'] /// Ajuste POA
            );
            $this->session->set_userdata($data);

        redirect('admin/dashboard','refresh');
    }


/// cambia mes para ejec inversion
  function update_mes(){
    if($this->input->is_ajax_request() && $this->input->post()){
        $this->form_validation->set_rules('i', 'Mes', 'required|trim');
        $mes_id= $this->security->xss_clean($post['mes_id']);
        $conf=$this->model_proyecto->get_configuracion($this->gestion); 


         /*$data = array(
                'gestion' => $conf[0]['ide'],
                'mes' => $mes_id,
                'mes_actual'=>$this->verif_mes_gestion($mes_id),
                'tr_id' => ($conf[0]['conf_mes_otro']+$conf[0]['conf_mes_otro']*2), /// Trimestre 3,6,9,12
                'desc_mes' => $this->mes_texto($conf[0]['conf_mes']),
                'verif_ppto' => $conf[0]['ppto_poa'], /// Ppto poa : 0 (Vigente), 1: (Aprobado)
                'conf_ajuste_poa' => $conf[0]['conf_ajuste_poa'] /// Ajuste POA
            );
            $this->session->set_userdata($data);*/
            $this->session->set_userdata('mes_actual', $this->verif_mes_gestion($mes_id));

    }else{
        show_404();
    }
  } 
    /*-------- Valida Cambio trimestre Session -----------*/
    public function cambiar_trimestre(){
        $conf=$this->model_proyecto->get_configuracion($this->gestion);

         $data = array(
                'gestion' => $conf[0]['ide'],
                'mes_actual'=>$this->verif_mes_gestion($conf[0]['conf_mes']),
                'estado_notificaciones' => $conf[0]['conf_poa'], /// Estado para las Notificaciones 0:no activo, 1: Habilitado
                'conf_estado' => $conf[0]['conf_estado'], //7 Estado 1: Activo, 0: No activo
                'conf_poa_estado' => $conf[0]['conf_poa_estado'], //7 Estado poa-presupuesto 1: inicial, 2 ajustado, 3 aprobado
                'trimestre' => $this->input->post('trimestre_usu'), /// Trimestre 1,2,3,4
                'tr_id' => ($conf[0]['conf_mes_otro']+$conf[0]['conf_mes_otro']*2), /// Trimestre 3,6,9,12
                'desc_mes' => $this->mes_texto($conf[0]['conf_mes']),
                'verif_ppto' => $conf[0]['ppto_poa'], /// Ppto poa : 0 (Vigente), 1: (Aprobado)
                'conf_ajuste_poa' => $conf[0]['conf_ajuste_poa'] /// Ajuste POA
            );
            $this->session->set_userdata($data);

        redirect('admin/dashboard','refresh');
    }

    //// view login
    public function index(){
        if ($this->session->userdata('is_logged_in')) {
            redirect('admin/dashboard');
        } else {

            $data['formulario']=$this->dashboard->form_login();
            $this->load->view('admin/login',$data);
        }
    }

    public function password_decod($pass)
    {
        $this->load->library('encrypt');
        $password = $this->encrypt->decode($pass);
        return $password;
    }

    /// GET CAPTCHA
    public function get_captcha(){
      if($this->input->is_ajax_request()){
          $post = $this->input->post();
          $captcha= $this->dashboard->generar_captcha(array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R'),4);
         
          $result = array(
          'respuesta' => 'correcto',
          'cod_captcha' => $captcha,
          'captcha' => md5($captcha),
        );
          
        echo json_encode($result);
 
      }else{
        show_404();
      }
    }


    public function tp_resp(){
      $ddep = $this->model_proyecto->dep_dist($this->dist_id);
      if($this->adm==1){
        $titulo='<b>RESPONSABLE :</b> NACIONAL';
      }
      elseif($this->adm==2){
        $titulo='<b>RESPONSABLE :</b> '.strtoupper($ddep[0]['dist_distrital']);
      }

      return $titulo;
    }



    /// ====== DASDHBOARD ADMINISTRATIVO 2026
    public function dashboard_index(){
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
            $data['menu_disponible'] = $this->dashboard->menu_disponibles_administrativo(); //// MENU SEGUN EL ROL DEL USUARIO
            //$data['gestiones']=$this->list_gestiones();

            $data['cabecera']='
            <style>
            #mdialTamanio{
                width: 80% !important;
            }
            #mdialTamanio_saldos{
                width: 50% !important;
            }

            .loading-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            /* El Spinner */
            .spinner {
                width: 40px;
                height: 40px;
                border: 4px solid rgba(0, 0, 0, 0.1);
                border-left-color: #09f; /* Color del giro */
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 10px;
            }

            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            .loading-container p {
                font-family: Arial, sans-serif;
                color: #666;
                font-size: 14px;
            }


            .table-custom {
                border-collapse: separate;
                border-spacing: 0;
                font-size: 11px;
                background-color: #fff;
                border: 1px solid #e0e0e0;
            }

            /* Cabecera elegante */
            .table-custom thead {
                background-color: #2c3e50;
                color: #ffffff;
            }

            .table-custom thead th {
                border: none;
                text-transform: uppercase;
                font-weight: 600;
                padding: 1px;
            }

            /* Celdas resaltadas (reemplaza el antiguo bgcolor) */
            .table-custom td.col-highlight {
                background-color: #f0fdfa; /* Un verde azulado muy suave */
                color: #0d9488;
                font-weight: 500;
            }

            /* Efectos al pasar el mouse */
            .table-hover tbody tr:hover {
                background-color: #f8fafc !important;
                transition: background 0.2s;
            }

            /* Botón de acción */
            .btn-action {
                display: inline-block;
                padding: 4px;
                border-radius: 4px;
                transition: transform 0.2s;
            }

            .btn-action:hover {
                transform: scale(1.2);
                background-color: #e2e8f0;
            }

            .text-center { text-align: center; }



                /* Cabecera roja vibrante AJUSTE DE SALDOS*/
                .modal-header.bg-danger {
                    background: linear-gradient(45deg, #d9534f 0%, #c9302c 100%);
                    border-bottom: none;
                }

                /* Efecto de sombra y bordes suaves */
                .modal-content {
                    border-radius: 12px;
                    overflow: hidden;
                }

                /* Contenedor de lista con scroll si es muy larga */
                .saldos-container {
                    max-height: 300px;
                    overflow-y: auto;
                    border: 1px solid #e9ecef;
                }

                /* Estilo para los botones */
                .btn-outline-danger {
                    border-width: 2px;
                    font-weight: bold;
                    text-transform: uppercase;
                    font-size: 12px;
                }

                /* Ajuste de tipografía */
                .modal-body h5 {
                    margin-bottom: 5px;
                }
                .modal-body p {
                    color: #555;
                    line-height: 1.5;
                }

            </style>';
            $data['cabecera'].='<div class="container">
                        <div class="navbar-header">
                          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                          </button>
                          <a class="navbar-brand" href="#"><font color="#1c7368"><b>'.$this->session->userdata('name').'</b></font></a>
                        </div>
                        <div class="navbar-collapse collapse">
                          <ul class="nav navbar-nav">
                            <li class="active"><a href="#"><b>Home</b></a></li>';
                            if($this->tp_adm==1){
                                $data['cabecera'].='
                                <li><a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" title="CAMBIAR GESTI&Oacute;N">Gesti&oacute;n</a></li>
                                <li><a href="#" data-toggle="modal" data-target="#modal_nuevo_tr" title="CAMBIAR TRIMESTRE">Trimestre</a></li>
                                <li><a href="#" data-toggle="modal" data-target="#modal_seguimiento_nacional" title="SEGUIMIENTO POA NACIONAL" class="seg_uni"><b>Seguimiento POA NACIONAL</b></a></li>';
                            }
                            else{
                                $data['cabecera'].='<li><a href="#" data-toggle="modal" data-target="#modal_seguimiento" id="'.$this->dist_id.'" title="SEGUIMIENTO POA" class="seg_uni"><b>Seguimiento POA</b></a></li>';
                            }

                            $data['cabecera'].='
                            <li class="dropdown">
                              <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Descarga de Archivos / Documentos">Descargas <b class="caret"></b></a>
                              <ul class="dropdown-menu">
                                <div style="color:blue; text-align: center;"><b>ANTEPROYECTO POA 2026</b></div>
                                <li><a href="'.base_url().'assets/video/FORMULARIOS_APROY2026/Plan_de_Trabajo_y_Directrices_Formulacion_POA_2026.pdf" style="cursor: pointer;" download><b>1.- Plan de Trabajo y Directrices Formulacion poa 2026</b></a></li>
                              </ul>
                            </li>
                          </ul>
                          <ul class="nav navbar-nav navbar-right">
                            <li class="active"><a href="'.base_url().'index.php/admin/logout" title="CERRAR SESI&Oacute;N"><b>SALIR</b></a></li>
                          </ul>
                        </div>
                    </div>';


            $data['titulo']='';
            $data['titulo'].='
            <input name="base" type="hidden" value="'.base_url().'">
            <div class="row box-green1">
                <div class="col-md-8">
                  <h3>BIENVENIDO : '.$this->session->userdata('funcionario').'</h3>
                  <h4>'.$this->tp_resp().'</h4>
                  <h4><b>CARGO : </b>'.$this->session->userdata("cargo").'</h4>
                  <h4><b>MES / GESTI&Oacute;N VIGENTE : '.$this->verif_mes[2].' / '.$this->gestion.'</b></h4>
                  <h4><b>TRIMESTRE VIGENTE : </b>'.$this->model_evaluacion->trimestre()[0]['trm_descripcion'].'</h4>
                </div>
                <div class="col-md-4" align="center">
                  <img src="'.base_url('assets/img_v1.1/moni.png').'" style="width:85%;">
                </div>
            </div>';

            $data['mensaje_alertas']=$this->dashboard->mensaje_sistema_dasboard_seguimiento(); 
            $data['modal_cambiar_gestion']=$this->dashboard->cambiar_gestion();
            $data['cambiar_trimestre']=$this->dashboard->cambiar_trimestre();
            $data['Notificacion_poa']='';
            if($this->notificaciones==1){
                $dia_cambios = 19;
                $hoy = date("j");
                if ($this->gestion>2025 & ($hoy == $dia_cambios)) {
                   if($this->dep_id==2){ //// Exclusivo para la Regional LA paz
                        $nro_poa=count($this->model_seguimientopoa->get_seguimiento_poa_mes_regional($this->dep_id,$this->verif_mes[1],$this->gestion));;
                    }
                    else{ /// Listado normal
                        $nro_poa=count($this->model_seguimientopoa->get_seguimiento_poa_mes_distrital($this->dist_id,$this->verif_mes[1],$this->gestion));
                    }
                    
                    if($nro_poa!=0){
                        $data['Notificacion_poa']=$this->dashboard->notificacion_poa_mensual($nro_poa); /// listado de actividades
                    }
                }
                else{
                    $data['Notificacion_poa']='';
                }
            }

            $data['modal_notificacion_poa']=$this->dashboard->modal_notificacion_poa_mensual();
            $data['get_unidades_seguimiento_poa_mensual']=$this->dashboard->list_seguimiento_a_unidades();
            $data['get_unidades_seguimiento_poa_mensual_nacional']=$this->dashboard->list_seguimiento_a_unidades_regional();
            $data['select_distrital']=$this->dashboard->modal_distritales();

            //// ------ SOLICITUDES DE PASSWORD
            $data['solicitudes_pass']=$this->dashboard->solicitudes_password();
            $data['popup_credenciales']=$this->dashboard->popup_credenciales_para_actualizar_contraseñas();
            ///------- Verificando Saldos
            $data['popup_saldos']=$this->dashboard->popup_ajustar_saldos();

            $this->load->view('admin/dashboard',$data);
            
        } else{
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }



    /*----- RESPUESTA A SOLICITUDES -----*/
    public function respuesta_pass($fun_id){
        $funcionario=$this->model_funcionario->get_funcionario($fun_id);
        $historial=$this->model_funcionario->historial_contraseñas($fun_id);
        $tabla='';

        $tabla.='
        <hr>
        Buenas estimad@, le escribimos del <b>Departamento Nacional de Planificación</b>, en atención a solicitud de recuperacion de contraseña al Sistema Siiplas, le enviamos por este medio las credenciales de acceso.
        <hr>
        <div style="font-size:30px;"><b>CREDENCIALES DE ACCESO SIIPLAS</b></div><br>
        <b>NOMBRE: </b>'.$funcionario[0]['fun_nombre'].' '.$funcionario[0]['fun_paterno'].' '.$funcionario[0]['fun_materno'].'<br>
        <b>USUARIO: </b>'.$funcionario[0]['fun_usuario'].'<br>
        <b>CONTRASEÑA: </b>'.$this->encrypt->decode($funcionario[0]['fun_password']).';
        <hr>
        Saludos Cordiales.
        <br>
        Atentamente: <b>Wilmer Mendoza Trujillo - ADMINISTRADOR SIIPLAS</b>
        <br>
        <br>
        <br>
        BOTON';


        $update_psw = array(
                'sol_estado' => 1
              );
              $this->db->where('fun_id', $fun_id);
              $this->db->update('solicitudes_psw', $update_psw);


        echo $tabla;
    }


    /*----- VERIFICA SI EXISTE SALDO A DISTRBUIR (DASHBOARD) -----*/
    public function verif_saldos_disponibles_distrital($dep_id,$dist_id){
      $valor=0;
      $ppto_asignado=0;
      $ppto_programado=0;

      if($dep_id==2){ /// Regional La paz
        $asignado=$this->model_ptto_sigep->suma_ptto_regional($dep_id,1);
        $programado=$this->model_ptto_sigep->suma_ptto_regional($dep_id,2);
      }
      else{
        $asignado=$this->model_ptto_sigep->suma_ptto_distrital($dist_id,1);
        $programado=$this->model_ptto_sigep->suma_ptto_distrital($dist_id,2);
      }


      if(count($asignado)!=0){
        $ppto_asignado=$asignado[0]['asignado'];
      }

      if(count($programado)!=0){
        $ppto_programado=$programado[0]['programado'];
      }

      if(round(($ppto_asignado-$ppto_programado),2)>5 || round(($ppto_asignado-$ppto_programado),2)<0){ /// 
        $valor=1;
      }

      return $valor;
    }


    /*----- UNIDADES CON SALDOS A DISTRBUIR (DASHBOARD) -----*/
   public function lista_unidades_con_saldo($dep_id, $dist_id) {
    $unidades = $this->model_ptto_sigep->lista_unidades_con_saldo_a_distribuir($dep_id, $dist_id);
    $tabla = '';

    $tabla .= '
    <style>
        .spin-custom {
            width: 18px; height: 18px;
            border: 3px solid rgba(0,0,0,.1);
            border-top-color: #1c7368;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .btn-table-opt { 
            padding: 5px; background: white; border: 1px solid #ddd; border-radius: 4px; display: inline-block; cursor: pointer;
        }
    </style>

    <form method="post">
        <table class="table" style="width:100%; border-collapse: collapse; font-family: sans-serif; font-size: 11px;">
          <thead>
            <tr style="background-color: #1c7368; color: white;">
              <th style="padding: 10px; text-align: center; border: 1px solid #165a52;">#</th>
              <th style="padding: 10px; border: 1px solid #165a52;">UNIDAD ORGANIZACIONAL</th>
              <th style="padding: 10px; text-align: right; border: 1px solid #165a52;">PPTO. ASIGNADO</th>
              <th style="padding: 10px; text-align: right; border: 1px solid #165a52;">PPTO. PROGRAMADO</th>
              <th style="padding: 10px; text-align: right; border: 1px solid #165a52;">SALDO</th>
              <th style="padding: 10px; text-align: center; border: 1px solid #165a52;">AJUSTAR</th>
              <th style="padding: 10px; text-align: center; border: 1px solid #165a52;"></th>
            </tr>
          </thead>
          <tbody>';

    $nro = 0;
    foreach ($unidades as $row) {
        $nro++;
        $bg_fila = ($row['saldo'] < 0) ? '#fff5f5' : '#f9fafb';
        $color_texto = ($row['saldo'] < 0) ? '#c53030' : '#333';

        $tabla .= '
        <tr style="background-color: '.$bg_fila.'; color: '.$color_texto.'; border-bottom: 1px solid #eee;">
            <td style="text-align: center; padding: 8px;">'.$nro.'</td>
            <td style="padding: 8px;">
                <small style="color: #666; display: block; font-size: 9px;">'.$row['prog'].'</small>
                <b>'.$row['tipo'].' '.$row['proy_nombre'].'</b>
            </td>
            <td style="text-align: right; padding: 8px;">'.number_format($row['asignado'], 2, ',', '.').'</td>
            <td style="text-align: right; padding: 8px;">'.number_format($row['programado'], 2, ',', '.').'</td>
            <td style="text-align: right; padding: 8px; font-weight: bold;">'.number_format($row['saldo'], 2, ',', '.').'</td>
            
            <!-- Icono Ajustar con Loading -->
            <td style="text-align: center; padding: 8px;">
                <a href="javascript:void(0);" 
                   onclick="irFormulario(this, \''.site_url("mod/form5/".$row['proy_id']).'\')" 
                   class="btn-table-opt" title="AJUSTE POA">
                    <img src="'.base_url().'assets/Iconos/page_edit.png" width="18" height="18"/>
                </a>
            </td>

            <td style="text-align: center; padding: 8px;">
                <a href="javascript:abreVentana(\''.site_url("proy/ptto_consolidado_comparativo/".$row['proy_id']).'\');" 
                   class="btn-table-opt" title="COMPARATIVO">
                    <img src="'.base_url().'assets/ifinal/requerimiento.png" width="18" height="18" />
                </a>
            </td>
        </tr>';
    }

    $tabla .= '</tbody></table></form>

    <script>
    function irFormulario(elemento, url) {
        // Cambia el contenido del botón por el spinner de carga
        elemento.innerHTML = \'<div class="spin-custom"></div>\';
        elemento.style.pointerEvents = "none";
        
        // Redirige después de medio segundo
        setTimeout(function() {
            window.location.href = url;
        }, 500);
    }
    </script>';

    return $tabla;
}







    /*---- MENSAJE SISTEMA ----*/
    // public function mensaje_sistema(){
    //     $conf = $this->model_configuracion->get_configuracion_session();
    //     $tabla = '';

    //     if (!empty($conf)) {
    //         // 1. Definimos los parámetros según el tipo de mensaje
    //         $tipos = [
    //             1 => ['clase' => 'danger',  'icono' => 'fa-ban',      'titulo' => 'ATENCIÓN'],
    //             2 => ['clase' => 'warning', 'icono' => 'fa-warning',  'titulo' => 'ADVERTENCIA'],
    //             3 => ['clase' => 'success', 'icono' => 'fa-check',    'titulo' => 'ÉXITO']
    //         ];

    //         $t = $conf[0]['tp_msn'];
    //         // Si el tipo no existe en el array, usamos 'warning' por defecto
    //         $config = isset($tipos[$t]) ? $tipos[$t] : $tipos[2];

    //         // 2. Construimos un único bloque de HTML dinámico
    //         $tabla .= '
    //         <div class="alert-modern alert-modern-'.$config['clase'].' fade-in-alert">
    //             <i class="fa '.$config['icono'].' alert-bg-icon"></i>
    //             <div class="alert-content-wrapper">
    //                 <div class="alert-icon-main">
    //                     <i class="fa '.$config['icono'].' fa-2x"></i>
    //                 </div>
    //                 <div class="alert-text-container">
    //                 <b>comunicado :</b><br>
    //                     <a class="alert-link-modern"><b>'.$conf[0]['conf_mensaje'].'</b></a>
    //                 </div>
    //             </div>
    //         </div>';
    //     }
    // }


    /// DASHBOARD SEGUIMIENTO POA (UNIDAD ADMINISTRATIVA / ESTABLECIMIENTO DE SALUD) 2026
    public function dashboard_seguimientopoa(){
        if($this->session->userdata('fun_id')!=null & $this->session->userdata('fun_estado')!=3){
        $data['dasboard']=$this->dashboard->dashboard_seguimientopoa();
        $this->load->view('admin/dashboard_seguimiento',$data); 

        } else{
            $this->session->sess_destroy();
            redirect('/','refresh');
        }
    }


    function __encrip_password($password){
        return md5($password);
    }

    public function validate_credentials_psw() {
        // 1. Solo permitir POST
        if (!$this->input->post()) {
            show_404();
            return;
        }

        $post = $this->input->post();
        $usuario = $this->security->xss_clean($post['user_namepws']);
        $email = $this->security->xss_clean($post['emailpws']);

        // 2. Validación de formato inicial
        if (!preg_match('/^[A-Za-z0-9.]+$/', $usuario) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->session->set_flashdata('danger', 'Formato de datos no válido.');
            redirect('/', 'refresh');
        }

        // 3. Validación de dominio de correo
        $dominio = substr(strrchr($email, "@"), 1);
        if (!checkdnsrr($dominio, 'MX')) {
            $this->session->set_flashdata('danger', 'El dominio del correo no parece ser válido.');
            redirect('/', 'refresh');
        }

        // 4. Búsqueda del usuario
        $busca_responsable = $this->model_funcionario->fun_usuario($usuario);
        if (empty($busca_responsable)) {
            $this->session->set_flashdata('danger', 'Usuario no registrado en el sistema.');
            redirect('/', 'refresh');
        }

        // 5. Preparación de datos e inserción
        $data_to_store = [
            'fun_id'    => $busca_responsable[0]['fun_id'],
            'email'     => $email,
            'sol_fecha' => date("Y-m-d H:i:s"), // Formato estándar SQL recomendado
            'num_ip'    => $this->input->ip_address(),
            'nom_ip'    => gethostbyaddr($_SERVER['REMOTE_ADDR']),
        ];

        $this->db->insert('solicitudes_psw', $data_to_store);
          $sol_id=$this->db->insert_id();

          if(count($this->model_funcionario->solicitud_contraseñas($sol_id))!=0){
            $this->session->set_flashdata('success', 'Solicitud enviada. En unos minutos el Administrador remitirá la información a su correo.');
          }
          else{
            $this->session->set_flashdata('danger', 'Error interno al procesar la solicitud.');
          }

        redirect('/', 'refresh');
    }


    function validate_credentials(){

        $this->load->model('Users_model');
        if(isset($_POST['user_name']) && isset($_POST['password']) && isset($_POST['dat_captcha'])){
            if($this->input->post('user_name') && preg_match('/^[a-zA-Z0-9!@+,:?_.^\/\*&%$-]*$/i', $this->input->post('password')) && preg_match('/^[A-Z0-9\/]*$/i', $this->input->post('dat_captcha'))){
                
                if(md5($this->input->post('dat_captcha'))==$this->input->post('captcha')){
                    $user_name = $this->security->sanitize_filename(strtoupper(htmlspecialchars($this->input->post('user_name'))), TRUE) ;
                    $password = $this->input->post('password'); 
                 
                    if($this->input->post('tp')==0){ /// Administrador
                        
                        $is_valid = $this->model_funcionario->verificar_loggin($this->security->xss_clean($user_name), $this->security->xss_clean($password));
                        if($is_valid['bool']){
                            $this->session->set_userdata($this->session_administrador($is_valid['fun_id'])); /// Sesion Administrador
                            
                            if($this->session->userData('rol_id')!=9){
                                redirect('admin/dashboard');
                            }
                            else{
                                if(count($this->model_componente->get_componente($this->session->userData('com_id'),$this->session->userData('gestion')))!=0){
                                    redirect('dashboar_seguimiento_poa');
                                }
                                else{
                                    $this->session->sess_destroy();
                                    $this->session->set_flashdata('warning', 'RESPONSABLE NO HABILITADO PARA LA PRESENTE GESTION');
                                    redirect('default_controller', 'refresh');
                                }
                            }
                            
                        }
                        else{
                            $this->session->sess_destroy();
                            $this->session->set_flashdata('warning', 'DATOS DE USUARIO SON INCORRECTOS !!!');
                            redirect('default_controller', 'refresh');
                        }
                    }
                    else{ //// Establecimiento de salud
                        $gestion = $this->Users_model->obtener_gestion();
                        $is_valid=$this->model_estructura_org->verif_establecimiento_ingreso($user_name,$password,$gestion[0]['ide']);
          
                        if($is_valid['bool']){
                            $this->session->set_userdata($this->session_establecimiento($is_valid['act_id']));
                            redirect('dashboar_seguimiento_poa');
                        }
                        else{
                            $this->session->sess_destroy();
                            $this->session->set_flashdata('warning', 'DATOS INCORRECTOS !!!');
                            redirect('default_controller', 'refresh');
                        }
                    }
                }
                else{
                    $this->session->sess_destroy();
                    $this->session->set_flashdata('warning', 'DATOS DE CÓDIGO SON INCORRECTOS !!!');
                    redirect('default_controller', 'refresh');
                }

            }
            else{
                $this->session->sess_destroy();
                $this->session->set_flashdata('danger','DATOS NO VALIDOS !!');
                redirect('default_controller', 'refresh');
            }
            
        }
        else{
            $this->session->sess_destroy();
            $this->session->set_flashdata('danger','DATOS NO VALIDOS !!');
            redirect('default_controller', 'refresh');
        }
        
    }

    /// Sesion Administrador
    public function session_administrador($fun_id){
        $this->session->sess_destroy();
        $data = $this->Users_model->get_datos_usuario($fun_id);

        $gestion = $this->Users_model->obtener_gestion();
        $entidad = $this->Users_model->get_entidad($gestion[0]['ide']);
        $conf = $this->model_configuracion->get_configuracion();
        //$modulos = $this->model_configuracion->modulos($conf[0]['ide']);
        $entidad = $entidad->conf_nombre_entidad;
        $data = array(
            'user_name' => $data[0]['fun_usuario'],
            'funcionario' => $data[0]['fun_nombre']." ".$data[0]['fun_paterno'],
            'usuario' => $data[0]['fun_usuario'],
            'cargo' => $data[0]['fun_cargo'],
            'credencial_funcionario' => $data[0]['sw_pass'], // 0,1
            'fun_estado' => $data[0]['fun_estado'],
            'com_id' => $data[0]['cm_id'], /// componente para el seguimeinto
            'fun_id' => $data[0]['fun_id'],
            'tp_rep' => 1, /// tp rep 1:borrador, 0: Limpio
            'rol_id' => $data[0]['rol_id'],
            'adm' => $data[0]['fun_adm'],
            'tp_adm' => $data[0]['tp_adm'],
            'dist' => $data[0]['fun_dist'], /// Distrital
            'name_distrital' => $data[0]['dist_distrital'],
            'dist_tp' => $data[0]['dist_tp'],
            'dep_id' => $data[0]['dep_id'],
            'conf_pei' => $data[0]['conf_pei'], /// Diagnostico Pei
            'gestion' => $gestion[0]['ide'],
            'mes' => $gestion[0]['conf_mes'],
            'conf_ajuste_poa' => $gestion[0]['conf_ajuste_poa'],
            'conf_psw' => $gestion[0]['conf_psw'], /// Credenciales

            'estado_notificaciones' => $gestion[0]['conf_poa'], /// Estado para las Notificaciones 0:no activo, 1: Habilitado
            'entidad' => $gestion[0]['conf_nombre_entidad'],
            'trimestre' => $gestion[0]['conf_mes_otro'], /// Trimestre 1,2,3,4
            'verif_ppto' => $gestion[0]['ppto_poa'], /// Ppto poa : 0 (Ante proyecto), 1: (Aprobado)
            'conf_poa_estado' => $gestion[0]['conf_poa_estado'], /// Estado Poa Estado : 1 (Inicial), 2: (Ajuste), 3: (Aprobado)
            'conf_form3' => $gestion[0]['conf_form3'], /// Estado de Registro del formulario N3, 0 (Inactivo), 1 (Activo)
            'conf_form4' => $gestion[0]['conf_form4'], /// Estado de Registro del formulario N4, 0 (Inactivo), 1 (Activo)
            'conf_form5' => $gestion[0]['conf_form5'], /// Estado de Registro del formulario N5, 0 (Inactivo), 1 (Activo)
            'conf_mod_ope' => $gestion[0]['conf_mod_ope'], /// Estado de modificacion del formulario N4, 0 (Inactivo), 1 (Activo)
            'conf_mod_req' => $gestion[0]['conf_mod_req'], /// Estado de modificacion del formulario N5, 0 (Inactivo), 1 (Activo)
            'conf_certificacion' => $gestion[0]['conf_certificacion'], /// Estado de modificacion del formulario N5, 0 (Inactivo), 1 (Activo)
            'tr_id' => ($gestion[0]['conf_mes_otro']+$gestion[0]['conf_mes_otro']*2), /// Trimestre 3,6,9,12
            'tp_msn' => $gestion[0]['tp_msn'], /// tipo de mensaje 1: rojo, 2: amarillo, 3: verde
            'mensaje' => $gestion[0]['conf_mensaje'], /// Mensaje
            'rd_poa' => $gestion[0]['rd_aprobacion_poa'], /// Resolucion Directorio POA
            'conf_estado' => $conf[0]['conf_estado'], /// Estado de la Gestion (1: activo, 0 No activo)
            'tp_usuario' => 0,
            'img' => base_url().'assets/ifinal/cns_logo.JPG',
           // 'img' => 'assets/ifinal/cns_logo.JPG',
            'mes_actual'=>$this->verif_mes_gestion($gestion[0]['conf_mes']),
           // 'modulos' => $modulos,
            'desc_mes' => $this->mes_texto($gestion[0]['conf_mes']),
            'name' => 'SIIPLAS V1.0',
            'direccion' => 'DEPARTAMENTO NACIONAL DE PLANIFICACI&Oacute;N',
            'sistema' => 'SISTEMA DE PLANIFICACI&Oacute;N DE SALUD - SIIPLAS V2.0',
            'sistema_pie' => 'SIIPLAS - Sistema de Planificaci&oacute;n de Salud',
            'logged' => true
        );

        return $data;
    }


    /// Sesion Establecimiento
    public function session_establecimiento($act_id){
        $this->session->sess_destroy();
        $gestion = $this->Users_model->obtener_gestion();
        $actividad=$this->model_estructura_org->datos_unidad_organizacional($act_id,$gestion[0]['ide']);
        
        $data = array(
            'user_name' => $actividad[0]['dato_ingreso'],
            'act_id' => $actividad[0]['act_id'],
            'usuario' => $actividad[0]['tipo'].' '.$actividad[0]['act_descripcion'].' '.$actividad[0]['abrev'],
            'estado' => $actividad[0]['act_estado'],
            'dist' => $actividad[0]['dist_id'], /// Distrital
            'name_distrital' => $actividad[0]['dist_distrital'],
            'com_id' => $actividad[0]['act_id'],
            'adm' => 2,
            'fun_id' => 399,
            'fun_estado' => 1,
            'dep_id' => $actividad[0]['dep_id'],
            'img' => $actividad[0]['img'],
            'tp_adm' => 0,
            'gestion' => $gestion[0]['ide'],
            'mes' => $gestion[0]['conf_mes'],
            'estado_notificaciones' => $gestion[0]['conf_poa'], /// Estado para las Notificaciones 0:no activo, 1: Habilitado
            'entidad' => $gestion[0]['conf_nombre_entidad'],
            'trimestre' => $gestion[0]['conf_mes_otro'], /// Trimestre 1,2,3,4
            'verif_ppto' => $gestion[0]['ppto_poa'], /// Ppto poa : 0 (Vigente), 1: (Aprobado)
            'tr_id' => ($gestion[0]['conf_mes_otro']+$gestion[0]['conf_mes_otro']*2), /// Trimestre 3,6,9,12
            'tp_msn' => $gestion[0]['tp_msn'], /// tipo de mensaje 1: rojo, 2: amarillo, 3: verde
            'mensaje' => $gestion[0]['conf_mensaje'], /// Mensaje
            'rd_poa' => $gestion[0]['rd_aprobacion_poa'], /// Resolucion Directorio POA
            'tp_usuario' => 1,
            'img' => base_url().'assets/ifinal/cns_logo.JPG',
           // 'img' => 'assets/ifinal/cns_logo.JPG',
            'mes_actual'=>$this->verif_mes_gestion($gestion[0]['conf_mes']),
            'desc_mes' => $this->mes_texto($gestion[0]['conf_mes']),
            'name' => 'SIIPLAS V1.0',
            'direccion' => 'DEPARTAMENTO NACIONAL DE PLANIFICACI&Oacute;N',
            'sistema' => 'SISTEMA DE PLANIFICACI&Oacute;N DE SALUD - SIIPLAS V2.0',
            'sistema_pie' => 'SIIPLAS - Sistema de Planificaci&oacute;n de Salud',
            'logged' => true
        );

        return $data;
    }


    function logout(){
        $this->session->sess_destroy();
        redirect('admin/dashboard');
    }

    function tasks(){
        $this->load->view('ajax/notify/tasks');
    }

    function notifications(){
        $this->load->view('ajax/notify/notifications');
    }

    function mail(){
        $this->load->view('ajax/notify/mail');
    }

    function menu(){
        $this->load->model('menu_modelo');
        $enlaces = $this->menu_modelo->get_Modulos();
        $data['enlaces'] = $enlaces;
        for ($i = 0; $i < count($enlaces); $i++) {
            $subenlaces[$enlaces[$i]['idchild']] = $this->menu_modelo->get_Enlaces($enlaces[$i]['idchild'], $this->session->userdata('user_name'));
        }
        $data['subenlaces'] = $subenlaces;/**/
        //$this->load->view('menu',$data);
    }

/*    function menu_enlace($sup){
        $this->load->model('menu_modelo');
        $data['enlaces'] = $this->menu_modelo->get_Enlaces(0);
    }*/

    /// mision institucional
    function mision(){
        redirect('admin/dashboard');
    }

    /// vision institucional
    function vision(){
        redirect('admin/dashboard');
    }


    //// A eliminar
    public function combo_fases_etapas(){
        //echo "urbanizaciones";
        $salida = "";
        $id_pais = $_POST["elegido"];
        // construimos el combo de ciudades deacuerdo al pais seleccionado
        $combog = pg_query("SELECT * FROM _etapas WHERE eta_clase=$id_pais");
        $salida .= "<option value=''>" . mb_convert_encoding('SELECCIONE FASE', 'cp1252', 'UTF-8') . "</option>";
        while ($sql_p = pg_fetch_row($combog)) {
            $salida .= "<option value='" . $sql_p[0] . "'>" . $sql_p[1] . "</option>";
        }
        echo $salida;
    }

   

    function login_exit() {
        $this->load->view('admin/login');
    }

    public function mes_texto($mes) {
        $meses = [
            1  => 'Enero',
            2  => 'Febrero',
            3  => 'Marzo',
            4  => 'Abril',
            5  => 'Mayo',
            6  => 'Junio',
            7  => 'Julio',
            8  => 'Agosto',
            9  => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $meses[(int)$mes];
    }


    /*--- verifica datos del mes y año ---*/
    public function verif_mes_gestion($mes_sistema){
      $valor=$mes_sistema; // numero mes segun el sistema
      //$valor=ltrim(date("m"), "0"); // numero mes por defecto
      $mes=$this->mes_nombre_completo($valor);

      $datos[1]=$valor; // numero del mes
      $datos[2]=$mes[$valor]; // mes
      $datos[3]=$this->gestion; // Gestion

      return $datos;
    }

    /*------ NOMBRE MES -------*/
    function mes_nombre_completo(){
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
}