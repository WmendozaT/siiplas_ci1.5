<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

///// DASHBOARD SISTEMA SIIPLAS
class Dashboard extends CI_Controller{
    public function __construct (){
        parent::__construct();
        $this->load->model('programacion/model_proyecto');
        $this->load->model('mantenimiento/model_entidad_tras');
        $this->load->model('mantenimiento/model_partidas');
        $this->load->model('mantenimiento/model_ptto_sigep');
        $this->load->model('modificacion/model_modrequerimiento');
        $this->load->model('programacion/insumos/minsumos');
        $this->load->model('ejecucion/model_seguimientopoa');
        $this->load->model('programacion/model_componente');
        $this->load->model('ejecucion/model_notificacion');
        $this->load->model('programacion/model_producto');
        $this->load->model('ejecucion/model_evaluacion');
        $this->load->model('mantenimiento/model_configuracion');
        $this->load->model('reporte_eval/model_evalprograma'); /// Model Evaluacion Programas
        $this->load->model('mantenimiento/model_estructura_org');
        $this->load->model('programacion/insumos/model_insumo'); /// gestion 2026

        $this->load->model('reporte_eval/model_evalunidad'); /// Model Evaluacion Unidad
        $this->load->model('reporte_eval/model_evalinstitucional'); /// Model Evaluacion Institucional
        $this->load->model('ejecucion/model_certificacion');

        $this->load->model('menu_modelo');
        $this->load->library('security');
        $this->gestion = $this->session->userData('gestion');
        $this->adm = $this->session->userData('adm');
        //$this->rol = $this->session->userData('rol_id');
        $this->dist = $this->session->userData('dist');
        //$this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->fun_id = $this->session->userData('fun_id');
       // $this->tp_adm = $this->session->userData('tp_adm');
        $this->verif_mes=$this->session->userData('mes_actual');
        $this->resolucion=$this->session->userdata('rd_poa');
        $this->tp_adm = $this->session->userData('tp_adm');
        
    }



 /*----- Login -----*/
    public function form_login(){
        $tabla='';
        
            $captcha= $this->generar_captcha(array(0,1,2,3,4,5,6,7,8,9,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R'),4);
            
            $data['cod_captcha']=$captcha;
            $data['captcha']=md5($captcha);


            $tabla='
            <style>
            /* --- Elementos Base y Carga --- */
            .caja {
                font-family: sans-serif; font-size: 28px; font-weight: 100;
                color: #000; background: #d1d9dc; margin: 0 0 15px;
                padding: 3px; overflow: hidden; text-align: center;
            }

            /* --- Pantalla de Carga (Overlay Negro) --- */
            #loading-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0, 0, 0, 0.85); backdrop-filter: blur(5px);
                z-index: 99999; display: none; align-items: center;
                justify-content: center; text-align: center; color: white;
                font-family: "Segoe UI", Tahoma, sans-serif;
            }
            .spinner-modern {
                width: 70px; height: 70px; margin: 0 auto 20px;
                border: 5px solid rgba(255, 255, 255, 0.1);
                border-top: 5px solid #146f64; border-radius: 50%;
                animation: spin 1s linear infinite; box-shadow: 0 0 15px rgba(20, 111, 100, 0.5);
            }
            .loader-text { font-size: 22px; letter-spacing: 2px; font-weight: bold; margin: 0; }
            .loader-subtext { font-size: 14px; color: #aaa; margin-top: 10px; }

            /* --- Modales SIIPLAS (Estáticos) --- */
            .modal-siiplas-overlay {
                display: none; position: fixed; z-index: 10000;
                top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.8); align-items: center; 
                justify-content: center; padding: 20px;
            }
            .modal-siiplas-content {
                background: #fff; width: 95%; max-width: 800px; min-height: 500px;
                border-radius: 10px; display: flex; flex-direction: column;
                box-shadow: 0 15px 35px rgba(0,0,0,0.5); overflow: hidden;
            }
            .modal-siiplas-header {
                background: #146f64; color: #fff; padding: 20px;
                display: flex; justify-content: space-between; align-items: center;
            }
            .modal-siiplas-body { padding: 25px; flex-grow: 1; overflow-y: auto; }
            .modal-siiplas-footer { padding: 15px; background: #f4f4f4; text-align: right; }

            /* --- Tablas y Acciones --- */
            .tabla-archivos { width: 100%; border-collapse: collapse; margin-top: 10px; }
            .tabla-archivos th, .tabla-archivos td {
                text-align: left; padding: 12px; border-bottom: 1px solid #eee; vertical-align: middle;
            }
            .tabla-archivos tr:hover { background-color: #f9f9f9; }

            .btn-accion {
                padding: 6px 12px; border-radius: 4px; text-decoration: none;
                font-size: 13px; font-weight: bold; display: inline-block; transition: 0.3s; margin: 2px;
            }
            .btn-vista { background: #e3f2fd; color: #1976d2 !important; border: 1px solid #1976d2; }
            .btn-vista:hover { background: #1976d2; color: #fff !important; }
            .btn-bajar, .btn-descarga { background: #146f64; color: #fff !important; }
            .btn-bajar:hover, .btn-descarga:hover { background: #0d4d45; }

            /* --- Botones Especiales --- */
            .btn-cerrar-modal { background: none; border: none; color: white; font-size: 28px; cursor: pointer; }
            .btn-cerrar-final {
                background: #666; color: white; border: none; padding: 10px 20px;
                border-radius: 5px; cursor: pointer; font-weight: bold;
            }
            .btn-disabled { background: #eee; color: #bbb; cursor: not-allowed; }

            /* --- Animaciones --- */
            @keyframes spin { 100% { transform: rotate(360deg); } }
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            .loader-container { animation: fadeIn 0.5s; }
            </style>';
        $tabla.='
        <div id="kc-content-wrapper">
        <input name="base" type="hidden" value="'.base_url().'">
        <div class="background-siat-login overflow-hidden d-flex justify-content-center align-items-center" style="height: 100vh;">
            <div class="container px-md-5 text-center text-lg-start my-5 ">
            <div>
            <a href="javascript:void(0)" 
               style="font-size:11px; color: hsl(150, 80%, 90%); text-decoration: none;" 
               onclick="abrirModalEstatico()">
               <b><i class="fas fa-paperclip"></i> ABRIR ARCHIVOS ADJUNTOS</b>
            </a>
            </div>
                <div class="row gx-lg-5 align-items-center mb-sm-0">
                    <div class="col-lg-6 mb-sm-0 mb-lg-0 text-center mt-lg-0" style="z-index: 10">
                        <div class="imgSiat">
                            <picture>
                                <source srcset="'.base_url().'assets/login_nuevo/img/logo_CNS_header.png" media="(min-width: 992px)" width="200px" height="auto">
                                <source srcset="'.base_url().'assets/login_nuevo/img/logo_CNS_header.png" media="(min-width: 768px)" width="200px" height="auto">
                                <img class="img-fluid animateBolivia" src="'.base_url().'assets/login_nuevo/img/logo_CNS_header.png"alt="logoSiatBolivia" width="200px" height="auto">
                            </picture>
                            
                            <h1 class="my-5 display-5 fw-bold ls-tight text-center titleSiat" style="color: hsl(218, 81%, 95%)">
                                Sistema de Planificaci&oacute;n y Seguimiento al POA
                                <br/>
                                <span style="color: #FFFF">SIIPLAS v2.0</span>
                            </h1>
                            
                            <div class="redesSocialesHeader">
                                <a href="https://www.facebook.com/CNS.Bolivia/" target="_blank"><img class="rrss mx-2" src="'.base_url().'assets/login_nuevo/img/facebook.svg"/ alt="rrssFacebook"></a>
                                <a href="https://www.instagram.com/cnsbolivia/" target="_blank"><img class="rrss mx-2" src="'.base_url().'assets/login_nuevo/img/instagram.svg"/ alt="rrssinstagram"></a>
                                <a href="https://www.youtube.com/channel/UCH8i2IHse60iSiyeYAihomg" target="_blank"><img class="rrss mx-2" src="'.base_url().'assets/login_nuevo/img/youtube.svg"/ alt="rrssYoutube"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mb-lg-0 position-relative">
                    <br/>
                        <div class="card bg-card">
                            <div class="card-body px-4 py-4 px-md-5">
                                <form role="form" action="'.base_url().'index.php/admin/validate" method="post" id="form" class="login-form">
                                    <input type="hidden" name="tp" id="tp" value="0">
                                    <div align=center>
                                        <b style="color:black;">DEPARTAMENTO NACIONAL DE PLANIFICACIÓN - C.N.S.</b>
                                    </div>';
                                        if($this->session->flashdata('success')){
                                            $tabla.='
                                                <div class="alert alert-success" role="alert">
                                                <h4 class="alert-heading">Solicitud Enviada!</h4>
                                                <p>'.$this->session->flashdata('success').'</p>
                                                </div>';
                                            
                                            }
                                            elseif($this->session->flashdata('danger')){
                                                $tabla.='
                                                <div class="alert alert-danger" role="alert">
                                                <h4 class="alert-heading">Solicitud Enviada!</h4>
                                                <p>'.$this->session->flashdata('danger').'</p>
                                                </div>';
                                            }
                                    $tabla.='
                                    <h5 class="text-center fw-bold my-4 titleBienvenido">Bienvenido/a!</h5>
                                    <div class="row align-items-center">
                                        <div class="col">
                                        <div id="form-login-username" class="form-group">      
                                            <input type="radio" name="radio-inline" id="radio0" checked="checked">
                                            <i></i><b>Unidad Administrativa</b></label> &nbsp;&nbsp; 
                                            <input type="radio" name="radio-inline" id="radio1">
                                            <i></i><font color="#146f64"><b>Establecimiento de Salud</b></font></label>
                                        </div>
                                        </div>
                                    </div>

                                    <input id="deviceId" class="dOt" name="deviceId">

                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="form-floating mb-2">
                                                <input tabindex="1" type="text" class="form-control form-input-bg" name="user_name" placeholder="USUARIO" minlength="5" maxlength="20" autocomplete="off" style="text-transform:uppercase;" oninput="this.value = this.value.toUpperCase();">
                                                <label for="user_name">USUARIO SIIPLAS</label>
                                                <div id="usu" class="text-danger text-start" style="font-size:9px;visibility: hidden;">
                                                   <b> Este campo es requerido</b>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto pf-0">
                                            <img src="'.base_url().'assets/login_nuevo/img/help.svg" class="tootip" title="USUARIO: Acceso asignado por el Departamento Nacional de Planificación"/>
                                        </div>
                                    </div>

                                    <input id="deviceId" class="dOt" name="deviceId">

                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="form-floating mb-2">
                                                <input tabindex="3" id="password" class="form-control form-input-bg" name="password" type="password" autocomplete="off" placeholder="CONTRASEÑA" minlength="4" maxlength="50"/>
                                                <label for="password">PASSWORD</label>
                                                <div id="pass" class="text-danger text-start" style="font-size:9px; visibility: hidden;" style="font-size:8px;">
                                                  <b>  Este campo es requerido</b>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-auto pf-0">
                                            <img src="'.base_url().'assets/login_nuevo/img/help.svg" onclick="togglePassword(\'password\')" class="tootip" id="toggleIcon" title="CLAVE DE ACCESO: Acceso asignado por el Departamento Nacional de Planificación"/>
                                        </div>
                                    </div>

                                    <div class="text-center py-3">
                                        <p class="caja" id="refreshs" style="text-align:center"><b>'.$data['cod_captcha'].'</b></p>
                                        <input type="hidden" name="captcha" id="captcha"  value="'.$data['captcha'].'" style="text-transform:uppercase;" oninput="this.value = this.value.toUpperCase();">
                                    </div>

                                    <div class="mb-4">
                                        <input tabindex="4" id="dat_captcha" name="dat_captcha" type="text" class="form-control form-input-bg text-center" placeholder="Ingrese el texto de la imagen" autofocus minlength="4" maxlength="4" >
                                        <div id="cat" class="text-danger text-start" style="font-size:9px; visibility: hidden;" style="font-size:8px;">
                                            <b>  Este campo es requerido</b>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 mt-2">
                                        <input tabindex="4" class="btn btn-lg mdl-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" style="width: 100%;" name="login" id="kc-login" type="submit" value="INGRESAR"/>
                                    </div>
                                </form>
                                <br>
                                <a href="javascript:void(0)" 
                                   style="color: #146f64; font-size: 11px; text-decoration: none; font-weight: bold;" 
                                   onclick="abrirModalRecuperar()">
                                   <i class="fas fa-key"></i> ¿Olvidaste tu Contraseña?
                                </a>
                            </div>
                        </div>
                    </div>
                
                </div>
            </div>
        </div>

            <div id="loading-overlay" style="display: none;">
                <div class="loader-container">
                    <div class="spinner-modern"></div>
                    <h2 class="loader-text">INGRESANDO AL SISTEMA</h2>
                    <p class="loader-subtext">Por favor, espere un momento...</p>
                </div>
            </div>

           <div id="modalRecuperar" class="modal-siiplas-overlay">
                <div class="modal-siiplas-content" style="max-width: 500px; min-height: auto;"> <!-- Más pequeño que el de archivos -->
                    <div class="modal-siiplas-header">
                        <h4 style="margin:0;">RECUPERAR CONTRASEÑA</h4>
                        <button class="btn-cerrar-modal" onclick="cerrarRecuperar()">&times;</button>
                    </div>
                    
                    <div class="modal-siiplas-body">
                        <p class="text-muted" style="font-size: 14px;">Por favor, registre sus datos para validar la cuenta.</p>

                        <form role="form" action="'.base_url().'index.php/validatepsw" method="post" id="formpws" class="login-form">
                            
                            <div class="form-floating mb-3">
                                <input tabindex="1" type="text" class="form-control" name="user_namepws" id="user_namepws" placeholder="USUARIO" minlength="5" maxlength="20" autocomplete="off" style="text-transform:uppercase;">
                                <label for="user_namepws">Usuario SIIPLAS</label>
                                <div id="usupsw" class="text-danger text-start" style="font-size:10px; visibility: hidden;">
                                   <b><i class="fas fa-exclamation-circle"></i> Este campo es requerido</b>
                                </div>
                            </div>

                            <div class="form-floating mb-3">
                                <input tabindex="2" id="emailpws" class="form-control" name="emailpws" type="email" autocomplete="off" placeholder="CORREO ELECTRÓNICO">
                                <label for="emailpws">Correo Electrónico Registrado</label>
                                <div id="email_err" class="text-danger text-start" style="font-size:10px; visibility: hidden;">
                                  <b><i class="fas fa-exclamation-circle"></i> Ingrese un correo válido</b>
                                </div>
                            </div>

                            <div class="d-grid gap-2 mt-4">
                                <button type="submit" class="btn-cerrar-final" style="background: #146f64; font-weight: bold;">
                                    <i class="fas fa-paper-plane"></i> ENVIAR SOLICITUD
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="modal-siiplas-footer">
                        <button class="btn btn-sm btn-secondary" onclick="cerrarRecuperar()">Cancelar</button>
                    </div>
                </div>
            </div>

            <div id="modalSiiplas" class="modal-siiplas-overlay">
                <div class="modal-siiplas-content">
                    <div class="modal-siiplas-header">
                        <h4 style="margin:0;">Repositorio de Documentos y Manuales</h4>
                        <!-- Único botón para cerrar -->
                        <button class="btn-cerrar-modal" onclick="cerrarModalEstatico()">&times;</button>
                    </div>
                    <div class="modal-siiplas-body">
                        <p style="color: #666; font-size: 14px;">Lista de archivos disponibles para el sistema <b>SIIPLAS v2.0</b>:</p>
                        
                        <table class="tabla-archivos">
                            <thead>
                                <tr style="color: #146f64; border-bottom: 2px solid #146f64;">
                                    <th>Descripción del Documento</th>
                                    <th style="text-align:center;">Formato</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Fila de ejemplo 1 (PDF) -->
                                <tr>
                                    <td>Manual de Usuario - solicitud de Modificación y Certificación POA </td>
                                    <td style="text-align:center;"><small>PDF</small></td>
                                    <td style="text-align:center;">
                                        <a href="'.base_url().'assets/video/FORMULARIOSPOA/Manual_sol_poa.pdf" download class="btn-accion btn-descarga" title="Descargar">💾 Descargar</a>
                                    </td>
                                </tr>
                                <!-- Fila de ejemplo 2 (Excel) -->
                                <tr>
                                    <td>Formulario de Solicitud de Modificación POA</td>
                                    <td style="text-align:center;"><small>XLSX</small></td>
                                    <td style="text-align:center;">
                                        <a href="'.base_url().'assets/video/FORMULARIOSPOA/FORM_MOD_4_Y_5_2026.xlsx" download class="btn-accion btn-descarga">💾 Descargar</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Formulario de Solicitud de Certificación POA</td>
                                    <td style="text-align:center;"><small>XLSX</small></td>
                                    <td style="text-align:center;">
                                        <a href="'.base_url().'assets/video/FORMULARIOSPOA/FORM_SOL_POA_5_2026.xlsx" download class="btn-accion btn-descarga">💾 Descargar</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Formulario de Reversión de Saldos de Certificación POA </td>
                                    <td style="text-align:center;"><small>WORD</small></td>
                                    <td style="text-align:center;">
                                        <a href="'.base_url().'assets/video/FORMULARIOSPOA/FORM_JUST_SALDOS_CPOAS_2026.docx" download class="btn-accion btn-descarga">💾 Descargar</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Formulario de Modificacion y Eliminación de Certificación POA </td>
                                    <td style="text-align:center;"><small>WORD</small></td>
                                    <td style="text-align:center;">
                                        <a href="'.base_url().'assets/video/FORMULARIOSPOA/FORM_JUST_EDICION_CPOAS_2026.docx" download class="btn-accion btn-descarga">💾 Descargar</a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-siiplas-footer">
                        <button class="btn-cerrar-final" onclick="cerrarModalEstatico()">Finalizar</button>
                    </div>
                </div>
            </div>


        </div>
        
        <script>
        function abrirModalRecuperar() {
            // Asegúrate de que el ID coincida con el div del modal de recuperación
            const modal = document.getElementById("modalRecuperar");
            
            if (modal) {
                modal.style.display = "flex";
                document.body.style.overflow = "hidden"; // Bloquea el scroll del login
            }
        }

        function cerrarRecuperar() {
            const modal = document.getElementById("modalRecuperar");
            
            if (modal) {
                modal.style.display = "none";
                document.body.style.overflow = "auto"; // Devuelve el scroll
            }
        }

        function abrirModalEstatico() {
            document.getElementById("modalSiiplas").style.display = "flex";
            // Evita que la página haga scroll al abrir el modal
            document.body.style.overflow = "hidden";
        }

        function cerrarModalEstatico() {
            document.getElementById("modalSiiplas").style.display = "none";
            // Devuelve el scroll a la página
            document.body.style.overflow = "auto";
        }
        </script>';

        return $tabla;
    }







    /*----- dashboard seguimiento Administracion/Establecimiento-----*/
    public function dashboard_seguimientopoa(){

            if($this->session->userdata('tp_usuario')==0){ /// Unidad Administrativa
                $responsable=$this->session->userdata('funcionario');
                $link_form1='seguimiento_poa';
                $com_id=$this->session->userData('com_id');
            }
            else{ /// Establecimiento de Salud
                $establecimiento=$this->model_seguimientopoa->get_unidad_programado_gestion($this->session->userData('act_id'));
                $responsable=$establecimiento[0]['tipo'].' '.$establecimiento[0]['act_descripcion'].' '.$establecimiento[0]['abrev'];
                $link_form1='seguimiento_establecimientos';
                $com_id=$establecimiento[0]['com_id'];
            }

            $formulario='';
            $formulario.='
                '.$this->style_dashboard_seguimiento().'
                <div class="jumbotron fade-in-anim">
                <div class="row box-green1">
                    <div class="col-md-8">
                        <h2 class="no-margin"><b>BIENVENIDO: '.$responsable.'</b></h2>
                        <hr style="border-top: 1px solid rgba(255,255,255,0.2);">
                        <h4><i class="fa fa-user"></i> <b>PERFIL:</b> SEGUIMIENTO AL POA</h4>
                        <h4><i class="fa fa-calendar"></i> <b>MES / GESTI&Oacute;N:</b> '.$this->verif_mes[2].' / '.$this->session->userdata("gestion").'</h4>
                        <h4><i class="fa fa-clock-o"></i> <b>TRIMESTRE:</b> '.$this->model_evaluacion->trimestre()[0]['trm_descripcion'].'</h4>
                    </div>
                    <div class="col-md-4" align="center">
                        <img src="'.base_url('assets/img_v1.1/logo_CNS_header.png').'" 
                         class="img-responsive pulso-latido" 
                         style="width:40%; filter: drop-shadow(2px 4px 6px rgba(0,0,0,0.5));">
                    </div>
                   
                </div>
            </div>

            <!-- Modal de carga (se mantiene oculto) -->
            <div id="overlay-loading" style="display:none;">
                <div class="loading-content">
                    <i class="fa fa-refresh fa-spin fa-4x spinner-custom"></i>
                    <h2 class="loading-text"><b>CARGANDO FORMULARIO</b></h2>
                    <p class="pulse" style="color: #bdc3c7; margin-top: 10px;">Preparando Formulario de Evaluación POA...</p>
                </div>
            </div>

            <!-- Sección de botones con movimiento y un pequeño retraso -->
            <section id="widget-grid" class="well fade-in-anim delay-1">
                '.$this->mensaje_sistema_dasboard_seguimiento().'

                <div class="row">
                   <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                        <a href="'.base_url().'index.php/'.$link_form1.'" id="myBtn1" 
                           class="jarvismetro-tile big-cubes bg-color-greenLight" 
                           data-toggle="tooltip" data-placement="bottom" title="Ingresar al Registro de Ejecución de mis Actividades al Formulario de Seguimiento y Evaluación POA">
                            <div class="well" align="center">
                                <img src="'.base_url().'assets/ifinal/select.png" style="width: 95px"/>
                                <h1 style="font-size: 11px;">FORMULARIO DE SEGUIMIENTO POA</h1>
                            </div>
                        </a>
                    </div>

                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                        <a href="'.base_url().'index.php/prog/rep_operacion_componente/'.$com_id.'" 
                           class="jarvismetro-tile big-cubes bg-color-greenLight btn-reporte" 
                           data-toggle="tooltip" data-placement="bottom" title="Ver Mis Actividades Programados en mi POA en formato PDF">
                            <div class="well" align="center">
                                <img src="'.base_url().'assets/ifinal/requerimiento.png" style="width: 95px"/>
                                <h1 style="font-size: 11px;">FORM. SPO N° 4 - <b>ACTIVIDADES</b></h1>
                            </div>
                        </a>
                    </div>';

                    if(count($this->model_insumo->list_consolidado_partidas_componentes($com_id))!=0){
                        $formulario.='
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                            <a href="'.base_url().'index.php/prog/rep_formulario5_uresp/'.$com_id.'" 
                               class="jarvismetro-tile big-cubes bg-color-greenLight btn-reporte" 
                               data-toggle="tooltip" data-placement="bottom" title="Ver Mis Requerimientos Programados en mi POA en formato PDF">
                                <div class="well" align="center">
                                    <img src="'.base_url().'assets/ifinal/requerimiento.png" style="width: 95px"/>
                                    <h1 style="font-size: 11px;">FORM. SPO N° 5 - <b>REQUERIMIENTOS</b></h1>
                                </div>
                            </a>
                        </div>';
                    }
                    if(count($this->model_insumo->verif_insumos_en_bolsas($com_id))!=0){
                        $formulario.='
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                            <a href="'.base_url().'index.php/prog/rep_consolidado_formulario5_bolsas_uresp/'.$com_id.'" 
                               class="jarvismetro-tile big-cubes bg-color-greenLight btn-reporte" 
                               data-toggle="tooltip" data-placement="bottom" title="Ver Mis Requerimientos Programados en mi POA en formato PDF - en los Programas de Bienes y Servicios y Fortalecimiento">
                                <div class="well" align="center">
                                    <img src="'.base_url().'assets/ifinal/rep_pdf.png"  style="width: 95px"/>
                                    <h1 style="font-size: 11px;">FORM. SPO N° 5 - REQUERIMIENTOS <br> <b>BIENES Y SERVICIOS / FORTALECIMIENTO</b></h1>
                                </div>
                            </a>
                        </div>';    
                    }
                    $formulario.='
              </div>
        </section>
        <footer class="admin-footer fade-in-anim delay-1">
            <div class="row">
                <!-- Lado Izquierdo: Info Institucional -->
                <div class="col-xs-12 col-md-8">
                    <span class="footer-version">Siiplas v1.0</span>
                    <span class="footer-sep">|</span>
                    <span class="footer-org">DEPARTAMENTO NACIONAL DE PLANIFICACIÓN - DNP</span>
                </div>
                
                <!-- Lado Derecho: Créditos de Autor -->
                <div class="col-xs-12 col-md-4 text-right">
                    <div class="footer-author">
                        <i class="fa fa-terminal"></i> <span>Developed by:</span> <strong>Wmendoza7</strong>
                    </div>
                </div>
            </div>
        </footer>



        <div class="modal fade" id="modal_pdf" data-backdrop="static" data-keyboard="false" tabindex="-1">
            <div class="modal-dialog modal-lg" style="width: 95%;">
                <div class="modal-content">
                    <div class="modal-header" style="background: #2c3e50; color: white; padding: 10px 15px;">
                        <button type="button" class="close" data-dismiss="modal" style="color:white; opacity:1;">&times;</button>
                        <h4 class="modal-title" style="display: flex; justify-content: space-between; align-items: center; padding-right: 20px;">
                            <span>
                                <i class="fa fa-file-pdf-o"></i> 
                                <span class="modal-title-text">REPORTE</span>
                            </span>
                            <!-- Contenedor dinámico de Fecha y Hora -->
                            <div style="text-align: right; line-height: 1.2;">
                                <small style="font-size: 9px; display: block; opacity: 0.8; text-transform: uppercase;">Generado el:</small>
                                <span id="fecha_completa_txt" style="font-size: 13px; font-weight: bold; color: #18bc9c; letter-spacing: 0.5px;"></span>
                            </div>
                        </h4>
                    </div>
                    <div class="modal-body" style="padding: 0; position: relative; height: 80vh;">
                        <div id="loading_pdf" style="position: absolute; top:0; left:0; width:100%; height:100%; background: #fdfdfd; display: flex; flex-direction: column; justify-content: center; align-items: center; z-index: 10;">
                            <div class="loader-document">
                                <div class="bar"></div><div class="bar"></div><div class="bar"></div>
                            </div>
                            <div style="text-align: center; margin-top: 25px;">
                                <h3 class="shimmer-text"><b>GENERANDO REPORTE POA ACTUALIZADO</b></h3>
                                <p style="color: #7f8c8d; font-size: 14px;">A fecha : <b id="fecha_loading_txt" style="color: #2c3e50;"></b></p>
                                Información Proporcionada de nuestra Base de Datos del Sistema de Planificación SIIPLAS
                            </div>
                        </div>
                        <iframe id="iframe_pdf" src="" width="100%" height="100%" frameborder="0"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>';

        return $formulario;
    }

    public function mensaje_sistema_dasboard_seguimiento() {
        $conf = $this->model_configuracion->get_configuracion_session();
        $tabla = '';

        if (!empty($conf)) {
            // 1. Definimos los parámetros según el tipo de mensaje
            $tipos = [
                1 => ['clase' => 'danger',  'icono' => 'fa-ban',      'titulo' => 'ATENCIÓN'],
                2 => ['clase' => 'warning', 'icono' => 'fa-warning',  'titulo' => 'ADVERTENCIA'],
                3 => ['clase' => 'success', 'icono' => 'fa-check',    'titulo' => 'ÉXITO']
            ];

            $t = $conf[0]['tp_msn'];
            // Si el tipo no existe en el array, usamos 'warning' por defecto
            $config = isset($tipos[$t]) ? $tipos[$t] : $tipos[2];

            // 2. Construimos un único bloque de HTML dinámico
            $tabla .= '
            <div class="alert-modern alert-modern-'.$config['clase'].' fade-in-alert">
                <i class="fa '.$config['icono'].' alert-bg-icon"></i>
                <div class="alert-content-wrapper">
                    <div class="alert-icon-main">
                        <i class="fa '.$config['icono'].' fa-2x"></i>
                    </div>
                    <div class="alert-text-container">
                    <b>comunicado :</b><br>
                        <a class="alert-link-modern"><b>'.$conf[0]['conf_mensaje'].'</b></a>
                    </div>
                </div>
            </div>';
        }

        return $tabla;
    }

 /*----- estilo dashboard seguimiento -----*/
    public function style_dashboard_seguimiento(){
    $tabla='
    <style>
    .fade-in-anim {
        animation: slideInDown 0.8s ease-out both;
    }

    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Retraso opcional para que los botones aparezcan después del banner */
    .delay-1 { animation-delay: 0.2s; }
    </style>
    <style>
    #overlay-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6); /* Fondo oscuro suave */
        backdrop-filter: blur(8px);    /* Desenfoque de fondo (Efecto Glass) */
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        transition: all 0.3s ease;
    }

    .loading-content {
        background: rgba(255, 255, 255, 0.1);
        padding: 40px 60px;
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        text-align: center;
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    }

    .spinner-custom {
        color: #2ecc71; /* Color verde como tu botón */
        text-shadow: 0 0 15px rgba(46, 204, 113, 0.5);
        margin-bottom: 20px;
    }

    .loading-text {
        color: #ffffff;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        letter-spacing: 1px;
        margin: 0;
    }

    /* Animación de pulso para el texto */
    .pulse {
        animation: pulse-animation 2s infinite;
    }

    @keyframes pulse-animation {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }


    </style>
    <style>
        .alert-modern {
            position: relative;
            padding: 18px 25px;
            border-radius: 12px;
            color: #fff !important;
            margin-bottom: 20px;
            overflow: hidden;
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        /* Colores con degradados suaves */
        .alert-modern-danger  { background: linear-gradient(135deg, #ed5565, #da4453); }
        .alert-modern-warning { background: linear-gradient(135deg, #f39c12, #e67e22); }
        .alert-modern-success { background: linear-gradient(135deg, #2ecc71, #27ae60); }

        .alert-content-wrapper { display: flex; align-items: center; position: relative; z-index: 5; }
        .alert-icon-main { margin-right: 20px; border-right: 1px solid rgba(255,255,255,0.2); padding-right: 20px; }
        
        .alert-title-tag { font-size: 0.75em; font-weight: 800; letter-spacing: 1px; opacity: 0.9; }
        .alert-link-modern { color: #fff !important; text-decoration: none !important; font-size: 1.15em; font-weight: 500; }

        .alert-bg-icon {
            position: absolute;
            right: -15px;
            bottom: -20px;
            font-size: 6em;
            opacity: 0.12;
            transform: rotate(-12deg);
            z-index: 1;
        }

        .fade-in-alert { animation: slideInDown 0.6s ease-out; }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-15px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
    <style>
        /* Animación de las barras (simulando hojas/datos) */
        .loader-document {
            display: flex;
            align-items: flex-end;
            gap: 8px;
            height: 50px;
        }

        .loader-document .bar {
            width: 12px;
            background: #2ecc71;
            border-radius: 4px;
            animation: loading-bars 1s ease-in-out infinite;
        }

        .loader-document .bar:nth-child(1) { height: 20px; animation-delay: 0.1s; }
        .loader-document .bar:nth-child(2) { height: 45px; animation-delay: 0.2s; background: #27ae60; }
        .loader-document .bar:nth-child(3) { height: 30px; animation-delay: 0.3s; }

        @keyframes loading-bars {
            0%, 100% { transform: scaleY(1); opacity: 0.5; }
            50% { transform: scaleY(1.5); opacity: 1; }
        }

        /* Efecto de brillo en el texto */
        .shimmer-text {
            background: linear-gradient(90deg, #34495e 0%, #2ecc71 50%, #34495e 100%);
            background-size: 200% auto;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmer 2s linear infinite;
            font-size: 20px;
            letter-spacing: 1px;
        }

        @keyframes shimmer {
            to { background-position: 200% center; }
        }
    </style>
    <style>
        /* Cambia el fondo del tooltip */
        .tooltip-inner {
            background-color: #27ae60 !important; 
            color: #fff !important;
            font-weight: bold;
            border-radius: 4px;
            padding: 8px 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        /* Cambia el color de la flechita del tooltip */
        .tooltip.bottom .tooltip-arrow {
            border-bottom-color: #27ae60 !important;
        }
    </style>

    <style>
    .pulso-latido {
        /* Nombre de la animación, duración (2s), infinita, suavizado suave */
        animation: latido 2s infinite ease-in-out;
        transform-origin: center; /* Asegura que el pulso sea desde el centro */
    }

    @keyframes latido {
        0% { transform: scale(1);}
        50% { transform: scale(1.05); /* Agranda el logo un 5% */}
        100% { transform: scale(1);}
    }
    </style>';

    return $tabla;
    }



    //// GENERAR CAPTCHA
    function generar_captcha($chars,$length){
        $captcha=null;
        for ($i=0; $i <$length ; $i++) { 
            $rand= rand(0,count($chars)-1);
            $captcha .=$chars[$rand];
        }

        return $captcha;
    }


}
?>