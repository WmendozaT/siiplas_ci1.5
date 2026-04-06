<!DOCTYPE html>
<html lang="en-us">
	<head>
		<meta charset="utf-8">
		<!--<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">-->
		<title><?php echo $this->session->userdata('name')?></title>
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<!-- Basic Styles -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/font-awesome.min.css">
		<!-- SmartAdmin Styles : Please note (smartadmin-production.css) was created using LESS variables -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/smartadmin-production.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/smartadmin-skins.min.css">
		<!-- Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp -->
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/demo.min.css">
		<link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/estilosh.css">
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.core.css" />
		<link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css" id="toggleCSS" />
		<script src="<?php echo base_url(); ?>assets/lib_alerta/alertify.min.js"></script>
		<!--para las alertas-->
    	<meta name="viewport" content="width=device-width">
	</head>
	<body class="">
		<!-- possible classes: minified, fixed-ribbon, fixed-header, fixed-width-->

		<!-- HEADER -->
		<header id="header">
			<!-- pulled right: nav area -->
			<div id="logo-group">
				<!-- <span id="logo"> <img src="<?php echo base_url(); ?>assets/img/logo.png" alt="SmartAdmin"> </span> -->
			</div>
			<div class="pull-right">
				<!-- collapse menu button -->
				<div id="hide-menu" class="btn-header pull-right">
					<span> <a href="javascript:void(0);" data-action="toggleMenu" title="Menu"><i class="fa fa-reorder"></i></a> </span>
				</div>
				<!-- end collapse menu -->
				<!-- logout button -->
				<div id="logout" class="btn-header transparent pull-right">
					<span> <a href="<?php echo base_url(); ?>index.php/admin/logout" title="Salir" data-action="userLogout" data-logout-msg="Estas seguro de salir del sistema"><i class="fa fa-sign-out"></i></a> </span>
				</div>
				<!-- end logout button -->
				<!-- search mobile button (this is hidden till mobile view port) -->
				<div id="search-mobile" class="btn-header transparent pull-right">
					<span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
				</div>
				<!-- end search mobile button -->
				<!-- fullscreen button -->
				<div id="fullscreen" class="btn-header transparent pull-right">
					<span> <a href="javascript:void(0);" data-action="launchFullscreen" title="Pantalla Completa"><i class="fa fa-arrows-alt"></i></a> </span>
				</div>
				<!-- end fullscreen button -->
			</div>
			<!-- end pulled right: nav area -->
		</header>
		<!-- END HEADER -->

		<!-- Left panel : Navigation area -->
		<aside id="left-panel">

			<!-- User info -->
			<div class="login-info">
				<span> <!-- User image size is adjusted inside CSS, it should stay as is --> 
					<a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
                            <span>
                                <i class="fa fa-user" aria-hidden="true"></i>  <?php echo $this->session->userdata("user_name");?>
                            </span>
						<i class="fa fa-angle-down"></i>
					</a> 
				</span>
			</div>

			<nav>
				<ul>
					<li class="">
	                <a href="<?php echo site_url("admin") . '/dashboard'; ?>" title="MENÚ PRINCIPAL"><i
	                        class="fa fa-lg fa-fw fa-home"></i> <span class="menu-item-parent">MEN&Uacute; PRINCIPAL</span></a>
	            	</li>
		            <li class="text-center">
		                <a href="#" title="PROGRAMACION"> <span class="menu-item-parent">MODIFICACIONES</span></a>
		            </li>
				<?php echo $menu;?>
				</ul>
			</nav>
			<span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>

		</aside>

		<!-- MAIN PANEL -->
		<div id="main" role="main">
			<!-- RIBBON -->
			<div id="ribbon">
				<span class="ribbon-button-alignment"> 
					<span id="refresh" class="btn btn-ribbon" data-action="resetWidgets" data-title="refresh"  rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true">
						<i class="fa fa-refresh"></i>
					</span> 
				</span>
				<!-- breadcrumb -->
				<ol class="breadcrumb">
					<li>Modificaciones</li><li>Modificaci&oacute;n POA</li><li>Mis Unidades Responsables</li>
				</ol>
			</div>
			<!-- END RIBBON -->
			<!-- MAIN CONTENT -->
			<div id="content">
			<div class="row">
				<article class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
					<section id="widget-grid" class="well">
						<?php echo $titulo_proy;?>
					</section>
				</article>
				<article class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
                    <div class="well">
                        <div class="btn-group btn-group-justified">
                            <a class="btn btn-default" href="<?php echo base_url();?>index.php/mod/list_top" title="SALIR A LISTA DE CITES"><i class="fa fa-caret-square-o-left"></i> SALIR</a>
                        </div>
                    </div>
                </article>
			</div>
			<div class="row">
				<article class="col-xs-12 col-sm-12 col-md-12 col-lg-3">
				</article>
				<article class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
					<h2 class="alert alert-success"><center>MODIFICACI&Oacute;N POA (ACTIVIDADES) - <?php echo $this->session->userData('gestion') ?> </center></h2>
					<div class="well"><?php echo $componentes;?></div>
				</article>
			</div>
			<!-- END MAIN CONTENT -->
			</div>
		</div>
		<!-- END MAIN PANEL -->
		<!-- PAGE FOOTER -->
		<div class="page-footer">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<span class="txt-color-white"><?php echo $this->session->userData('name').' @ '.$this->session->userData('gestion') ?></span>
				</div>
			</div>
		</div>

		<!-- ============================================ Modal NUEVO COMPONENTE  =============================================== -->
	    <div class="modal animated fadeInDown" id="modal_nuevo_ff" tabindex="-1" role="dialog">
	        <div class="modal-dialog">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <button type="button" class="close text-danger " data-dismiss="modal" aria-hidden="true">
	                        &times;
	                    </button>
	                    <h4 class="modal-title text-center text-info">
	                        <b>INGRESAR DATOS DE CITE</b>
	                    </h4>
	                </div>
	                <div class="modal-body no-padding">
	                    <div class="row">
	                        <div id="bootstrap-wizard-1" class="col-sm-12">
	                        	<div class="well">
	                        		<form action="<?php echo site_url().'/modificaciones/cmod_fisica/valida_cite_modificacion'?>"  id="form_nuevo" name="form_nuevo" class="smart-form" method="post">
									   	<input type="hidden" name="proy_id" id="proy_id" value="<?php echo $proyecto[0]['proy_id'];?>">
									   	<input type="hidden" name="com_id" id="com_id">
									   	<fieldset>
											<section>
												<div class="row">
													<label class="label col col-2">CITE</label>
													<div class="col col-10">
														<label class="input"> <i class="icon-append fa fa-user"></i>
															<input type="text" name="cite" id="cite" placeholder="XX-XX-XXX" title="REGISTRE NUMERO DE CITE">
														</label>
													</div>
												</div>
											</section>
											<section>
												<div class="row">
													<label class="label col col-2">FECHA</label>
													<div class="col col-10">
														<label class="input"> <i class="icon-append fa fa-calendar"></i>
														<input type="text" name="fm" id="fm" class="form-control datepicker" data-dateformat="dd/mm/yy" onKeyUp="this.value=formateafecha(this.value);" placeholder="dd/mm/YY" title="SELECCIONE FECHA DE MODIFICACI&Oacute;N DE CITE">
													</label>
													</div>
												</div>
											</section>
										</fieldset>
										
										<footer>
											<button type="button" name="add_form" id="add_form" class="btn btn-primary">Ingresar a Modificar</button>
											<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
										</footer>
										<center><img id="load" style="display: none" src="<?php echo base_url() ?>/assets/img/loading.gif" width="35" height="35"></center></td>
									</form>	
								</div>
	                        </div>
	                    </div>
	                </div>
	            </div><!-- /.modal-content -->
	        </div><!-- /.modal-dialog -->
	    </div>
		<?php echo $loading;?>
		<!-- END PAGE FOOTER -->
		<script>
			if (!window.jQuery) {
				document.write('<script src="<?php echo base_url();?>/assets/js/libs/jquery-2.0.2.min.js"><\/script>');
			}
		</script>
		<script>
			if (!window.jQuery.ui) {
				document.write('<script src="<?php echo base_url();?>/assets/js/libs/jquery-ui-1.10.3.min.js"><\/script>');
			}
		</script>

		<!-- IMPORTANT: APP CONFIG -->
		<script src="<?php echo base_url();?>/assets/js/app.config.js"></script>
		<script src="<?php echo base_url(); ?>assets/lib_alerta/alertify.min.js"></script>
		<!-- JS TOUCH : include this plugin for mobile drag / drop touch events-->
		<script src="<?php echo base_url();?>/assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> 
		<!-- BOOTSTRAP JS -->
		<script src="<?php echo base_url();?>/assets/js/bootstrap/bootstrap.min.js"></script>
		<!-- CUSTOM NOTIFICATION -->
		<script src="<?php echo base_url();?>/assets/js/notification/SmartNotification.min.js"></script>
		<!-- JARVIS WIDGETS -->
		<script src="<?php echo base_url();?>/assets/js/smartwidgets/jarvis.widget.min.js"></script>
		<!-- EASY PIE CHARTS -->
		<script src="<?php echo base_url();?>/assets/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js"></script>
		<!-- SPARKLINES -->
		<script src="<?php echo base_url();?>/assets/js/plugin/sparkline/jquery.sparkline.min.js"></script>
		<!-- JQUERY VALIDATE -->
		<script src="<?php echo base_url();?>/assets/js/plugin/jquery-validate/jquery.validate.min.js"></script>
		<!-- JQUERY MASKED INPUT -->
		<script src="<?php echo base_url();?>/assets/js/plugin/masked-input/jquery.maskedinput.min.js"></script>
		<!-- JQUERY SELECT2 INPUT -->
		<script src="<?php echo base_url();?>/assets/js/plugin/select2/select2.min.js"></script>
		<!-- JQUERY UI + Bootstrap Slider -->
		<script src="<?php echo base_url();?>/assets/js/plugin/bootstrap-slider/bootstrap-slider.min.js"></script>
		<!-- browser msie issue fix -->
		<script src="<?php echo base_url();?>/assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
		<!-- FastClick: For mobile devices -->
		<script src="<?php echo base_url();?>/assets/js/plugin/fastclick/fastclick.min.js"></script>
		<!-- Demo purpose only -->
		<script src="<?php echo base_url();?>/assets/js/demo.min.js"></script>
		<!-- MAIN APP JS FILE -->
		<script src="<?php echo base_url();?>/assets/js/app.min.js"></script>

		<script type="text/javascript">
		// DO NOT REMOVE : GLOBAL FUNCTIONS!
		$(document).ready(function() {
			pageSetUp();
		})
		</script>
		<!-- MODIFICAR COMPONENTE -->
        <script type="text/javascript">
       	$(function () {
		    // 1. Al abrir el modal: Solo asignar el ID
		    $(".nuevo_ff").on("click", function (e) {
			    // Asegurarnos de que el overlay esté oculto al abrir el modal
			    $("#loading-overlay").hide(); 
			    $("#add_form").prop('disabled', false); // Rehabilitar botón por si acaso
			    
			    var com_id = $(this).attr('name'); 
			    $("#com_id").val(com_id);
			});

		    // 2. Configuración única de validación
		    var $validator = $("#form_nuevo").validate({
		        rules: {
		            cite: { required: true },
		            fm: { required: true }
		        },
		        messages: {
		            cite: "<font color=red>REGISTRE CITE</font>",
		            fm: "<font color=red>SELECCIONE FECHA</font>",		                    
		        },
		        highlight: function (element) {
		            $(element).closest('.section').addClass('has-error');
		        },
		        unhighlight: function (element) {
		            $(element).closest('.section').removeClass('has-error');
		        },
		        errorElement: 'span',
		        errorClass: 'help-block'
		    });

		    // 3. Evento de click en el botón de envío (fuera de .nuevo_ff)
		    $("#add_form").on("click", function () {
		        if ($("#form_nuevo").valid()) {
		            var fecha = $("#fm").val();

		            if (!validarFormatoFecha(fecha)) {
		                alertify.error("El formato de la fecha es incorrecto.");
		                return;
		            }

		            if (!existeFecha(fecha)) {
		                alertify.error("La fecha introducida no existe.");
		                return;
		            }

		            alertify.confirm("¿DESEA INGRESAR A REALIZAR LA MODIFICACIÓN DE OPERACIONES?", function (a) {
		                if (a) {
							    $("#loading-overlay").css("display", "flex"); // Usar flex en lugar de show() para centrar
							    $("#add_form").prop('disabled', true);
							    $("#form_nuevo").submit();
							} else {
		                    alertify.error("OPCIÓN CANCELADA");
		                }
		            });
		        }
		    });
		});

		// Funciones de fecha simplificadas
		function validarFormatoFecha(campo) {
		    return /^\d{1,2}\/\d{1,2}\/\d{2,4}$/.test(campo);
		}

		function existeFecha(fecha) {
		    var fechaf = fecha.split("/");
		    var d = parseInt(fechaf[0], 10);
		    var m = parseInt(fechaf[1], 10);
		    var y = parseInt(fechaf[2], 10);
		    var date = new Date(y, m - 1, d);
		    return date && (date.getMonth() + 1) == m && date.getDate() == d;
		}
        </script>
		<!-- ============================================================================== -->

	</body>
</html>