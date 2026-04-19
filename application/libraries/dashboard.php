<?php if (!defined('BASEPATH')) exit('No se permite el acceso directo al script');

///// DASHBOARD SISTEMA SIIPLAS
class Dashboard extends CI_Controller{
    public function __construct (){
        parent::__construct();
        $this->load->model('mantenimiento/model_funcionario');
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
       // $this->dist = $this->session->userData('dist');
        $this->dist_id = $this->session->userData('dist');
        //$this->dist_tp = $this->session->userData('dist_tp');
        $this->tmes = $this->session->userData('trimestre');
        $this->fun_id = $this->session->userData('fun_id');
       // $this->tp_adm = $this->session->userData('tp_adm');
        $this->verif_mes=$this->session->userData('mes_actual');
        $this->resolucion=$this->session->userdata('rd_poa');
        $this->tp_adm = $this->session->userData('tp_adm');
         $this->notificaciones=$this->session->userData('estado_notificaciones');
        $this->verif_mes=$this->session->userdata('mes_actual');
        $this->conf_ajuste_poa=$this->session->userdata('conf_ajuste_poa'); 
        $this->conf_credenciales=$this->session->userdata('conf_psw'); /// configuracion de credenciales
        $this->fun_credencial=$this->session->userdata('credencial_funcionario'); /// credenciales del funcionario
        
    }



 /*----- Login 2026 -----*/
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


    /*----- dashboard Administrativo 2026 -----*/
    public function dashboard_siiplas(){


    }


    /*---- Menu Disponible parte Administrativa --------*/
    public function menu_disponibles_administrativo(){
        $menu_disponibles=$this->model_configuracion->modulos_disponibles();
        $tabla='';

        // 1. Estilos del Loading (Fondo Negro Formal)
        $tabla.='
        <style>
            #formal-overlay {
                display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: #000000; z-index: 99999; 
                flex-direction: column; align-items: center; justify-content: center;
                transition: all 0.5s;
            }
            .spinner-light {
                width: 50px; height: 50px; border: 3px solid rgba(255,255,255,0.1);
                border-top: 3px solid #ffffff; border-radius: 50%;
                animation: spin-formal 1s linear infinite;
            }
            @keyframes spin-formal { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
            .loading-text {
                color: white; margin-top: 20px; font-family: "Segoe UI", sans-serif;
                letter-spacing: 4px; text-transform: uppercase; font-size: 13px; font-weight: 300;
            }
        </style>

        <div id="formal-overlay">
            <div class="spinner-light"></div>
            <div class="loading-text">Ingresando al modulo, espere porfavor ....</div>
            <div id="mod-target" style="color: #555; font-size: 10px; margin-top: 5px; letter-spacing: 2px;"></div>
        </div>';

        $tabla.='<div class="row">';
        if(count($menu_disponibles)!=0){
            foreach($menu_disponibles as $row){
            $tabla.='
                <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                    <!-- Mantenemos tus clases originales y añadimos onclick -->
                    <a href="'.base_url().'index.php/'.$row['url'].'" 
                       class="jarvismetro-tile big-cubes bg-color-greenLight" 
                       onclick="showFormalLoading(\''.$row['mod_descripcion'].'\')">
                        <div class="well1" align="center">
                            <img class="img-circle" src="'.base_url().''.$row['icono_mod'].'"  style="margin-left:0px; width: 95px"/>
                            <h1 style="font-size: 11px;"><b>'.$row['mod_descripcion'].'</b></h1>
                        </div>
                    </a>
                </div>';
            }
        }
        else{
            $tabla.='SIN MODULOS DISPONIBLES';
        }
        $tabla.='</div>';

        // 2. Script de activación
        $tabla.='
        <script>
            function showFormalLoading(nombre) {
                document.getElementById("mod-target").innerText = nombre;
                document.getElementById("formal-overlay").style.display = "flex";
            }
        </script>';

        return $tabla;
    }
    

    /*----- dashboard seguimiento Administracion/Establecimiento 2026-----*/
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
                        <a href="'.base_url().'index.php/prog/reporte_form4_uresponsable/'.$com_id.'" 
                           class="jarvismetro-tile big-cubes bg-color-greenLight btn-reporte" 
                           data-toggle="tooltip" data-placement="bottom" title="Ver Mis Actividades Programados en mi POA en formato PDF">
                            <div class="well" align="center">
                                <img src="'.base_url().'assets/ifinal/requerimiento.png" style="width: 95px"/>
                                <h1 style="font-size: 11px;">FORM. SPO N° 4 - <b>ACTIVIDADES</b></h1>
                            </div>
                        </a>
                    </div>';

                    if(count($this->model_insumo->list_consolidado_partidas_uResponsable($com_id))!=0){
                        $formulario.='
                        <div class="col-xs-12 col-sm-6 col-md-6 col-lg-3">
                            <a href="'.base_url().'index.php/prog/reporte_form5_uresponsable/'.$com_id.'" 
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
                            <a href="'.base_url().'index.php/prog/reporte_form5_uresponsable_programa_bolsa_consoldado/'.$com_id.'" 
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
                        <button type="button" id="btn_exportar_excel" class="btn btn-success" onclick="exportarExcelConLoading('.$com_id.')">
                            <i class="fa fa-file-excel-o"></i> <span id="txt_btn_excel">Exportar POA.xls</span>
                        </button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>';

        return $formulario;
    }


    //// Mensaje Dashboard 2026
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


    //// Cambio de Gestion 2026
    /*----------- Lista de Gestiones Disponibles ---------*/
    public function list_gestiones(){
        $listar_gestion= $this->model_configuracion->lista_gestion();
        $tabla='';

        $tabla.='
                <input type="hidden" name="gest" id="gest" value="'.$this->gestion.'">
                <select name="gestion_usu" id="gestion_usu" class="form-control" required>
                <option value="0">seleccionar gestión</option>'; 
        foreach ($listar_gestion as $row) {
            if($row['ide']==$this->gestion){
                $tabla.='<option value="'.$row['ide'].'" select >'.$row['ide'].'</option>';
            }
            else{
                $tabla.='<option value="'.$row['ide'].'" >'.$row['ide'].'</option>';
            }
        };
        $tabla.='</select>';
        return $tabla;
    }

    public function cambiar_gestion() {
            $gestiones=$this->list_gestiones();
            $cambiar_gestion='';
            $cambiar_gestion.='
                <div class="modal fade" id="modal_nuevo_ff" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                      <div class="modal-body">
                        <form action="'.site_url().'/cambiar_session" id="form_nuevo" name="form_nuevo" class="form-horizontal" method="post">
                            <h3 class="alert alert-info"><center>CAMBIAR GESTI&Oacute;N - '.$this->gestion.'</center></h3>   
                            <fieldset>
                              <div class="form-group">
                                  <div class="form-group">
                                    <label class="col-md-2 control-label">GESTI&Oacute;N</label>
                                    <div class="col-md-10">
                                        '.$gestiones.'
                                    </div>
                                  </div>
                              </div>
                            </fieldset>                    
                            <div class="form-actions">
                                <div class="row">
                                  <div class="col-md-12" align="right">
                                    <button class="btn btn-default" data-dismiss="modal" id="cl" title="CANCELAR">CANCELAR</button>
                                    <button type="button" name="subir_form" id="subir_form" class="btn btn-info" >CAMBIAR GESTI&Oacute;N</button>
                                    <center><img id="load" style="display: none" src="'.base_url().'/assets/img/loading.gif" width="50" height="50"></center>
                                  </div>
                                </div>
                            </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>';

        return $cambiar_gestion;
    }
    //// Fin Cambio de Gestion 2026

    //// Cambiar Trimestre 2026
    /*--- Lista de Gestiones Disponibles ---*/
    public function list_trimestre(){
        $listar_trimestre= $this->model_configuracion->get_mes_trimestre();
        $tmes=$this->model_evaluacion->trimestre();
        $tabla='';

        $tabla.='
                <input type="hidden" name="tmes" id="tmes" value="'.$this->tmes.'">
                <select name="trimestre_usu" id="trimestre_usu" class="form-control" required>
                <option value="0">seleccionar Trimestre</option>'; 
        foreach ($listar_trimestre as $row) {
                if($row['trm_id']!=0 & $row['trm_id']<4){
                    if($row['trm_id']==$tmes[0]['trm_id']){
                        $tabla.='<option value="'.$row['trm_id'].'" select>'.$row['trm_descripcion'].'</option>';
                    }
                    else{
                        $tabla.='<option value="'.$row['trm_id'].'" >'.$row['trm_descripcion'].'</option>';
                    }
                }
        };
        $tabla.='</select>';
        return $tabla;
    }
    public function cambiar_trimestre() {
            $cambiar_trimestre='';
            $cambiar_trimestre.='
            <div class="modal fade" id="modal_nuevo_tr" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-body">
                    <form action="'.site_url().'/cambiar_session_trimestre" id="form_trimestre" name="form_trimestre" class="form-horizontal" method="post">
                        <h4 class="alert alert-info"><center>CAMBIAR TRIMESTRE - '.$this->model_evaluacion->trimestre()[0]['trm_descripcion'].'</center></h4>   
                        <fieldset>
                          <div class="form-group">
                              <div class="form-group">
                                  <label class="col-md-2 control-label">TRIMESTRE</label>
                                  <div class="col-md-10">
                                      '.$this->list_trimestre().'
                                  </div>
                              </div>
                          </div>
                        </fieldset>                    
                        <div class="form-actions">
                            <div class="row">
                              <div class="col-md-12" align="right">
                                <button class="btn btn-default" data-dismiss="modal" id="cl" title="CANCELAR">CANCELAR</button>
                                <button type="button" name="subir_formt" id="subir_formt" class="btn btn-info" >CAMBIAR TRIMESTRE</button>
                                <center><img id="loadt" style="display: none" src="'.base_url().'/assets/img/loading.gif" width="50" height="50"></center>
                              </div>
                            </div>
                        </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>';

        return $cambiar_trimestre;
    }
    //// FIN Cambiar Trimestre 2026


    /*---- GET LISTADO NOTIFICACION POA MENSUAL 2026 ----*/
    public function notificacion_poa_mensual($nro){
        $tabla='';
        if($this->fun_id==592 || $this->fun_id==709){ /// Exclusivo La paz
            $req=$this->model_notificacion->nro_requerimientos_acertificar_mensual_x_mes_regional($this->dep_id,$this->verif_mes[1]);
        }
        else{
            $req=$this->model_notificacion->nro_requerimientos_acertificar_mensual_x_mes_distrital($this->dist_id,$this->verif_mes[1]);
        }

        
        $ddep = $this->model_proyecto->dep_dist($this->dist_id);
        $tit_requerimiento='';
        
        if(count($req)!=0){
            $tit_requerimiento='y '.$req[0]['requerimientos'].' Requerimientos con un monto de Bs. '.number_format($req[0]['monto'], 2, ',', '.').' que deben ser <b>EVALUADOS</b> y <b>CERTIFICADOS</b>';
        }

        
        $tabla.='
            <div class="alert alert-success" role="alert" title='.$this->dist_id.' style="text-align:justify">
                <h4 class="alert-heading"><b>PROCESO DE NOTIFICACIÓN POA '.$this->verif_mes[2].' / '.$this->gestion.' !!</b></h4>
                <p>Hola '.$this->session->userdata('funcionario').', la '.strtoupper($ddep[0]['dist_distrital']).' tiene programado en su POA '.$this->gestion.' para el mes de '.$this->verif_mes[2].' : '.$nro.' Actividades </b> '.$tit_requerimiento.'. </p>
                <hr>
                <p class="mb-0">
                    <a data-toggle="modal" data-target="#modal_form4_mes" id="'.$this->dist_id.'" class="btn btn-success form4_mes" title=""><img src="'.base_url().'assets/Iconos/application_cascade.png" width="20" height="20"/>&nbsp;<b style="font-size:10px">NOTIFICACIÓN GASTO CORRIENTE</b></a>';
                    if(count($this->model_notificacion->list_requerimiento_pinversion_programado_al_mes_distrital($this->dist_id,$this->verif_mes[1]))!=0){
                       $tabla.='&nbsp;<a data-toggle="modal" data-target="#modal_form4_mes" id="'.$this->dist_id.'" class="btn btn-success pi_mes" title=""><img src="'.base_url().'assets/Iconos/application_cascade.png" width="20" height="20"/>&nbsp;<b style="font-size:10px">NOTIFICACIÓN PROY. INVERSIÓN</b></a>';
                    }
                    $tabla.='
                    
                </p>
            </div>';

        return $tabla;
    }

    //// Modal para el listado de la notificacion poa
    public function modal_notificacion_poa_mensual(){
        $modal_notificacion_poa='';
            $modal_notificacion_poa.='
                <div class="modal fade" id="modal_form4_mes" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document" id="mdialTamanio">
                    <div class="modal-content">
                      <div class="modal-header">
                        <button class="close" data-dismiss="modal" id="amcl" title="SALIR"><span aria-hidden="true">&times; <b>Salir Formulario</b></span></button>
                      </div>
                      <div class="modal-body" align="center">
                        <div id="Notificacion_formN4"></div>
                      </div>
                    </div>
                  </div>
                </div>';
        return $modal_notificacion_poa;
    }

    ///// Solicitudes de Password
    public function solicitudes_password(){
        $tabla='';
            $solicitudes_password=$this->model_funcionario->listado_solicitud_contraseñas();
            if(count($solicitudes_password)!=0 & $this->fun_id==399){
                $data['solicitudes_pass']='
                <input name="base" type="hidden" value="'.base_url().'">
                    <div id="myModal" class="modal fade" data-backdrop="static" data-keyboard="false" style="">
                        <div class="modal-dialog modal-login" id="mdialTamanio_psw">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 style="color:blue; text-align:center"><b>Atención de Solicitudes de Contraseña</b></h4>
                                </div>
                                <div class="modal-body">
                                    <div style="color:blue; font-size:10px;">Tienes ('.count($solicitudes_password).') Solicitudes por atender..</div>
                                    <table class="table table-bordered">
                                    <thead>
                                      <tr title="" >
                                        <th scope="col" style="text-align:center;">#</th>
                                        <th scope="col" style="text-align:center;">TRABAJADOR</th>
                                        <th scope="col" style="text-align:center;">USUARIO</th>
                                        <th scope="col" style="text-align:center;">DISTRITAL</th>
                                        <th scope="col" style="text-align:center;">CORREO ELECTRONICO</th>
                                        <th scope="col" style="text-align:center;"></th>
                                      </tr>
                                    </thead>
                                    <tbody>';
                                    $nro=0;
                                    foreach($solicitudes_password as $row){
                                        $nro++;
                                        $tabla.='
                                        <tr>
                                            <td>'.$nro.'</td>
                                            <td>'.$row['fun_nombre'].' '.$row['fun_paterno'].' '.$row['fun_materno'].'</td>
                                            <td>'.$row['fun_usuario'].'</td>
                                            <td>'.$row['dist_distrital'].'</td>
                                            <td><b>'.$row['email'].'</b></td>
                                            <td>
                                                <a href="javascript:abreVentana(\''.site_url("").'/solpassw/'.$row['fun_id'].'\');" class="btn btn-default" title="GENERAR REPORTE"><img src="'.base_url().'assets/ifinal/requerimiento.png" WIDTH="25" HEIGHT="25"/><br><font size=1><b>USUARIO</b></font></a>
                                            </td>
                                        </tr>';
                                    }
                                    $tabla.='
                                    </tbody>
                                    </table>                                  
                                </div>
                            </div>
                        </div>
                    </div>';
            }

        return $tabla;
    }


    ///// popup para actualizar el Password
    public function popup_credenciales_para_actualizar_contraseñas(){
            //// ------ CREDENCIALES (INACTIVO)
        $popup_credenciales='';
            if(($this->conf_credenciales==1 || $this->fun_credencial==0) & $this->fun_id!=399){
                $popup_credenciales = '
                <div id="myModal" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                            
                            <!-- Cabecera con Gradiente de Seguridad (Verde) -->
                            <div class="modal-header" style="background: linear-gradient(45deg, #1d976c, #93f9b9); color: white; padding: 20px; border: none;">
                                <h4 class="modal-title" style="margin: 0; font-weight: bold; text-align: center; width: 100%; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);">
                                    <i class="fa fa-shield"></i> ACTUALIZACIÓN DE CREDENCIALES - SIIPLAS
                                </h4>
                            </div>

                            <div class="modal-body" style="padding: 30px; font-family: sans-serif;">
                                <!-- Mensaje de Introducción -->
                                <div style="margin-bottom: 25px;">
                                    <h5 style="color: #2d3e50;">Estimad@: <b>'.$this->session->userdata('funcionario').'</b></h5>
                                    <p style="color: #4a5568; line-height: 1.6; text-align: justify;">
                                        Para garantizar la integridad y seguridad de la información institucional, hemos implementado nuevas medidas de protección bajo las políticas del <b>Plan de Seguridad de la Información (PISI)</b>. Es obligatorio actualizar sus credenciales para continuar operando.
                                    </p>
                                </div>

                                <!-- Recuadro de Políticas -->
                                <div style="background-color: #f0fdf4; border-radius: 10px; padding: 20px; border: 1px solid #dcfce7;">
                                    <h6 style="color: #166534; font-weight: bold; margin-top: 0; display: flex; align-items: center; gap: 10px;">
                                        <i class="fa fa-lock"></i> POLÍTICA DE GESTIÓN DE CONTRASEÑAS (V. 1.1)
                                    </h6>
                                    <hr style="border: 0; border-top: 1px solid #bbf7d0; margin: 10px 0;">
                                    
                                    <ul style="color: #1e40af; font-size: 13px; padding-left: 20px; line-height: 1.8;">
                                        <li><b>Complejidad:</b> Combine mayúsculas, minúsculas, números y símbolos (ej: @, #, $, %).</li>
                                        <li><b>Longitud:</b> Mínimo de doce <b>(12)</b> caracteres alfanuméricos.</li>
                                        <li><b>Historial:</b> El sistema no permitirá reutilizar contraseñas anteriores.</li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Footer con Acciones -->
                            <div class="modal-footer" style="background: #f9fafb; padding: 20px; border-top: 1px solid #edf2f7; display: flex; justify-content: space-between; align-items: center;">
                                <a href="'.base_url().'index.php/admin/logout" style="color: #e53e3e; text-decoration: none; font-weight: bold; font-size: 13px;">
                                    <i class="fa fa-sign-out"></i> Cerrar sesión
                                </a>
                                
                                <a href="'.base_url().'index.php/admin/mod_contra" class="btn" style="background: #1d976c; color: white; border-radius: 25px; padding: 10px 25px; font-weight: bold; text-transform: uppercase; font-size: 12px; box-shadow: 0 4px 10px rgba(29, 151, 108, 0.3); border: none; transition: 0.3s;">
                                    <i class="fa fa-refresh"></i> Actualizar ahora
                                </a>
                            </div>

                        </div>
                    </div>
                </div>';
            }

        return $popup_credenciales;
    }


    ///// popup para Ajustar Saldos
    public function popup_ajustar_saldos(){
        $popup_saldos='';
            $ddep = $this->model_proyecto->dep_dist($this->dist_id);
             if($this->conf_ajuste_poa==1){
                if($this->verif_saldos_disponibles_distrital($this->dep_id,$this->dist_id)==1 & $this->dep_id!=10 & $this->gestion>2025){
                
                    $popup_saldos = '
                        <div id="myModal" class="modal fade" data-backdrop="static" data-keyboard="false" tabindex="-1">
                            <div class="modal-dialog modal-lg" id="mdialTamanio_saldos">
                                <div class="modal-content" style="border-radius: 15px; overflow: hidden; border: none;">
                                    
                                    <!-- Cabecera con Estilo -->
                                    <div class="modal-header" style="background: linear-gradient(45deg, #ed213a, #93291e); color: white; padding: 20px;">
                                        <h4 class="modal-title" style="margin: 0; font-weight: bold; text-align: center; width: 100%;">
                                            <i class="fa fa-warning"></i> AJUSTAR DISTRIBUCIÓN DE SALDOS - GESTIÓN '.$this->gestion.'
                                        </h4>
                                    </div>

                                    <div class="modal-body" style="padding: 25px;">
                                        <!-- Alerta Mensaje -->
                                        <div style="background-color: #fef2f2; border-left: 5px solid #ef4444; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                                            <h5 style="color: #991b1b; margin-top: 0;">Hola, <b>'.$this->session->userdata('funcionario').'</b></h5>
                                            <p style="color: #7f1d1d; line-height: 1.6; margin-bottom: 0;">
                                                La <b>'.strtoupper($ddep[0]['dist_distrital']).'</b> presenta saldos en su POA que deben ser ajustados o inscritos. 
                                                Mientras no se regularice, las <b>CERTIFICACIONES POA</b> permanecerán inhabilitadas.
                                            </p>
                                        </div>

                                        <!-- Lista de Unidades -->
                                        <div style="max-height: 350px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; background: #fafafa;">
                                            <p style="font-weight: bold; color: #4b5563; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                                                <i class="fa fa-list"></i> Unidades con saldos pendientes:
                                            </p>
                                            '.$this->lista_unidades_con_saldo($this->dep_id, $this->dist_id).'
                                        </div>
                                    </div>

                                    <!-- Footer con cierre de sesión -->
                                    <div class="modal-footer" style="background: #f9fafb; padding: 15px;">
                                        <span style="font-size: 12px; color: #9ca3af; margin-right: auto;">Acceso restringido hasta ajuste.</span>
                                        <a href="'.base_url().'index.php/admin/logout" class="btn btn-danger" style="border-radius: 20px; padding: 8px 20px; font-weight: bold; text-transform: uppercase; font-size: 12px;">
                                            <i class="fa fa-sign-out"></i> Salir de la Sesión
                                        </a>
                                    </div>

                                </div>
                            </div>
                        </div>';
                }
            }

        return $popup_saldos;
    }


    ///// Modal muestra el listado de unidades para el seguimiewnto si cargaron o no el seguimiento poa
    public function list_seguimiento_a_unidades(){
        $get_unidades_seguimiento_poa_mensual='';
            $get_unidades_seguimiento_poa_mensual.='
            <div class="modal fade" id="modal_seguimiento" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document" id="mdialTamanio_saldos">
                <div class="modal-content">
                  <div class="modal-header">
                    <button class="close" data-dismiss="modal" id="amcl" title="SALIR"><span aria-hidden="true">&times; <b>Salir Formulario</b></span></button>
                  </div>
                  <div class="modal-body" align="center">
                    <div id="seg"></div>
                  </div>
                </div>
              </div>
            </div>';

        return $get_unidades_seguimiento_poa_mensual;
    }

    ///// Modal lista de distritales para ver si registraron el seguimiento poa trimestral
    public function modal_distritales(){
        $distritales=$this->model_proyecto->lista_distritales();
            $select_distrital='';
            $select_distrital.='
            <div class="modal fade" id="modal_seguimiento_nacional" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-body">
                    <form id="form_seg" name="form_seg" class="form-horizontal" method="post">
                        <h3 class="alert alert-info"><center>SEGUIMIENTO POA - '.$this->verif_mes[2].' / '.$this->gestion.'</center></h3>   
                        <fieldset>
                          <div class="form-group">
                            <div class="col-md-12">
                              <select name="seg_reg" id="seg_reg" class="form-control" required>
                                <option value="0">seleccionar Distrital...</option>';
                                foreach($distritales as $row){
                                    $select_distrital.='<option value="'.$row['dist_id'].'">'.strtoupper($row['dist_distrital']).'</option>';
                                }
                            $select_distrital.='
                              </select>
                            </div>
                          </div>
                        </fieldset>
                    </form>
                  </div>
                </div>
              </div>
            </div>';

        return $select_distrital;
    }

    ///// Modal muestra el listado de unidades para el seguimiewnto si cargaron o no el seguimiento poa por regional seleccionado
    public function list_seguimiento_a_unidades_regional(){
         $get_unidades_seguimiento_poa_mensual_nacional='';
            $get_unidades_seguimiento_poa_mensual_nacional.='
            <div class="modal fade" id="modal_respuesta" tabindex="-1" role="dialog" aria-labelledby="respuestaModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-lg" role="document" id="mdialTamanio_saldos"> <!-- Modal grande -->
                      <div class="modal-content">
                          <div class="modal-body">
                              <div id="responsee"></div> <!-- Div para mostrar la respuesta -->
                          </div>
                          <div class="modal-footer">
                              <div id="botones"></div>
                          </div>
                      </div>
                  </div>
              </div>';

        return $get_unidades_seguimiento_poa_mensual_nacional;
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