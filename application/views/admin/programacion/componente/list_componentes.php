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
	</head>
	<body class="">
		<!-- possible classes: minified, fixed-ribbon, fixed-header, fixed-width-->
		<!-- HEADER -->
		<header id="header">
			<!-- pulled right: nav area -->
			<div class="pull-right">
				<!-- collapse menu button -->
				<div id="hide-menu" class="btn-header pull-right">
					<span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
				</div>
				<!-- end collapse menu -->
				<!-- logout button -->
				<div id="logout" class="btn-header transparent pull-right">
					<span> <a href="<?php echo base_url(); ?>index.php/admin/logout" title="Sign Out" data-action="userLogout" data-logout-msg="Estas seguro de salir del sistema"><i class="fa fa-sign-out"></i></a> </span>
				</div>
				<!-- end logout button -->
				<!-- search mobile button (this is hidden till mobile view port) -->
				<div id="search-mobile" class="btn-header transparent pull-right">
					<span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
				</div>
				<!-- end search mobile button -->
				<!-- fullscreen button -->
				<div id="fullscreen" class="btn-header transparent pull-right">
					<span> <a href="javascript:void(0);" data-action="launchFullscreen" title="Full Screen"><i class="fa fa-arrows-alt"></i></a> </span>
				</div>
				<!-- end fullscreen button -->
			</div>
			<!-- end pulled right: nav area -->
		</header>
		<!-- Left panel : Navigation area -->
		<aside id="left-panel">
			<!-- User info -->
			<div class="login-info">
				<span> <!-- User image size is adjusted inside CSS, it should stay as is --> 
					<a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
            <span>
                <i class="fa fa-user" aria-hidden="true"></i>  <?php echo $this->session->userdata("user_name");?>
            </span>
					</a> 
				</span>
			</div>

			<?php echo $menu;?>
			<span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>
		</aside>

		<!-- MAIN PANEL -->
		<div id="main" role="main">
			<!-- RIBBON -->
			<div id="ribbon">
				<!-- breadcrumb -->
				<ol class="breadcrumb">
					<li><a href="<?php echo base_url().'index.php/admin/proy/list_proy';?>" title="POA">Programaci&oacute;n POA</a></li><li>Programaci&oacute;n F&iacute;sica</a></li><li>Mis Unidades Responsables</li>
				</ol>
			</div>
			<!-- END RIBBON -->

			<!-- MAIN CONTENT -->
			<div id="content">
				<div class="row">
					<article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<section id="widget-grid" class="well">
							<ul class="nav nav-pills">
							  <li class="active"><a href="#">MIS UNIDADES RESPONSABLES</a></li>
							  <li><a href="#">MIS ACTIVIDADES</a></li>
							</ul>
						</section>
					</article>
				</div>
				
					<?php echo $listado; ?>

			</div>
			<!-- END MAIN CONTENT -->
		</div>


	<!-- SUBIR PLANTILLA DE MIGRACION -->
	<!-- ================== MODAL SUBIR ARCHIVO ========================== -->
		<style>
        #dialog_subir{
          width: 40% !important;
        }
    </style>
		<div class="modal fade" id="modal_importar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
		    <div class="modal-dialog" id="dialog_subir">
		        <div class="modal-content" style="border-radius: 8px; border: none; overflow: hidden;">
		            
		            <!-- Cabecera más limpia -->
		            <div class="modal-header" style="background: #f8f9fa; border-bottom: 1px solid #eee; padding: 15px 20px;">
		                <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 24px;">
		                    <span aria-hidden="true">&times;</span>
		                </button>
		                <h4 class="modal-title" style="font-weight: bold; color: #333;">
		                    <i class="fa fa-upload text-primary"></i> Importar Consolidado Actividades
		                </h4>
		            </div>

		            <div class="modal-body" style="padding: 25px;">
		                <!-- Título e Instrucción -->
		                <div class="text-center" style="margin-bottom: 20px;">
		                    <h5 style="font-weight: bold; text-transform: uppercase; color: #555;">Subir archivo Excel (.xls, .xlsx)</h5>
		                    <p  style="font-size:12px;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
		                </div>

		                <!-- Vista previa de columnas (Imagen optimizada) -->
		                <div class="thumbnail" style="border: 1px dashed #ddd; padding: 10px; background: #fafafa;">
		                    <img src="<?= base_url('assets/img/img_migracion/migracion_form4_unidad.JPG'); ?>" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto;">
		                </div>

		                <form action="<?= site_url('modificaciones/cmod_insumo/valida_add_requerimientos'); ?>" method="post" enctype="multipart/form-data" id="form_subir_sigep">
		               
		                    
		                    <div class="form-group" style="margin-top: 20px;">
		                        <label style="display: block; font-weight: bold; margin-bottom: 10px; color: #444;">SELECCIONAR ARCHIVO:</label>
		                        <div class="input-group input-group-lg">
		                            <span class="input-group-btn">
		                                <button type="button" class="btn btn-primary" onclick="$(this).parent().find('input[type=file]').click();" style="border-radius: 4px 0 0 4px;">
		                                    <i class="fa fa-folder-open"></i> Examinar...
		                                </button>
		                                <input id="archivo" accept=".xlsx, .xls" name="archivo" 
		                                       onchange="$(this).parent().parent().find('.file-name-display').val($(this).val().split(/[\\|/]/).pop());" 
		                                       style="display: none;" type="file" required>
		                            </span>
		                            <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #fff; cursor: default;">
		                        </div>
		                    </div>

		                    <div id="mensaje" style="margin: 10px 0;"></div>

		                    <!-- Botón de Acción -->
		                    <div style="margin-top: 25px;">
		                        <button type="button" id="btn_subir" class="btn btn-success btn-lg btn-block" style="font-weight: bold; border-radius: 4px; transition: all 0.3s;">
		                            <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
		                        </button>
		                    </div>

		                    <!-- Loader -->
		                    <div id="loads" class="text-center" style="display: none; margin-top: 15px;">
		                        <i class="fa fa-spinner fa-spin fa-2x text-success"></i>
		                        <p style="margin-top: 10px;"><b>Validando datos, por favor espere...</b></p>
		                    </div>
		                </form>
		            </div>
		        </div>
		    </div>
		</div>


		<!-- END MAIN PANEL -->    
		<div class="page-footer">
			<div class="row">
				<div class="col-xs-12 col-sm-6">
					<span class="txt-color-white"><?php echo $this->session->userData('name').' @ '.$this->session->userData('gestion') ?></span>
				</div>
			</div>
		</div>
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
						<script src="<?php echo base_url(); ?>assets/js/session_time/jquery-idletimer.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/app.config.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/mis_js/validacion_form.js"></script>
		<!-- JS TOUCH : include this plugin for mobile drag / drop touch events-->
		<script src="<?php echo base_url(); ?>assets/js/plugin/jquery-touch/jquery.ui.touch-punch.min.js"></script> 
		<!-- BOOTSTRAP JS -->
		<script src="<?php echo base_url(); ?>assets/js/bootstrap/bootstrap.min.js"></script>
		<!-- CUSTOM NOTIFICATION -->
		<script src="<?php echo base_url(); ?>assets/js/notification/SmartNotification.min.js"></script>
		<!-- JARVIS WIDGETS -->
		<script src="<?php echo base_url(); ?>assets/js/smartwidgets/jarvis.widget.min.js"></script>
		<!-- EASY PIE CHARTS -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/easy-pie-chart/jquery.easy-pie-chart.min.js"></script>
		<!-- SPARKLINES -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/sparkline/jquery.sparkline.min.js"></script>
		<!-- JQUERY VALIDATE -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/jquery-validate/jquery.validate.min.js"></script>
		<!-- JQUERY MASKED INPUT -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/masked-input/jquery.maskedinput.min.js"></script>
		<!-- JQUERY SELECT2 INPUT -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/select2/select2.min.js"></script>
		<!-- JQUERY UI + Bootstrap Slider -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/bootstrap-slider/bootstrap-slider.min.js"></script>
		<!-- browser msie issue fix -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/msie-fix/jquery.mb.browser.min.js"></script>
		<!-- FastClick: For mobile devices -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/fastclick/fastclick.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/lib_alerta/alertify.min.js"></script>
		<!-- Demo purpose only -->
		<script src="<?php echo base_url(); ?>assets/js/demo.min.js"></script>
		<!-- MAIN APP JS FILE -->
		<script src="<?php echo base_url(); ?>assets/js/app.min.js"></script>
		<!-- ENHANCEMENT PLUGINS : NOT A REQUIREMENT -->
		<!-- Voice command : plugin -->
		<script src="<?php echo base_url(); ?>assets/js/speech/voicecommand.min.js"></script>
		
		<!-- PAGE RELATED PLUGIN(S) -->
		<script src="<?php echo base_url(); ?>assets/js/plugin/datatables/jquery.dataTables.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.colVis.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.tableTools.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
		<script src="<?php echo base_url(); ?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
		<script>
			////------------  PARA MIGRAR ARCHIVO EN EXCEL 2026 ==========2026
$(document).ready(function() {
    // Mostrar nombre del archivo al seleccionar
    $('#archivo').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        if (fileName) {
            $('.file-name-display').val(fileName); // Ajustado al input readonly del diseño anterior
        }
    });

    $('#btn_subir').on('click', function(e) {
        e.preventDefault();
        $('#mensaje').html(''); 

        if ($('#archivo').val() == '') {
            $('#mensaje').html('<div class="alert alert-warning"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel.</div>');
            return false;
        }

        var form = $('#form_subir_sigep')[0];
        var data = new FormData(form);

        // Bloquear UI
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> PROCESANDO...');
        $('#loads').show();

        $.ajax({
            type: "POST",
            url: $('#form_subir_sigep').attr('action'),
            data: data,
            processData: false,
            contentType: false,
            success: function(response) {
                    var res;
                    try {
                        res = (typeof response === 'object') ? response : JSON.parse(response);
                    } catch (e) {
                        console.error("Error parseando JSON:", response);
                        $('#mensaje').html('<div class="alert alert-danger">Error de respuesta del servidor (Posible tiempo de espera agotado).</div>');
                        $('#btn_subir').prop('disabled', false).text('REINTENTAR');
                        $('#loads').hide();
                        return;
                    }

                if (res.status === 'success') {
                    // Construimos un mensaje más visual
                    var html = `
                        <div class="alert alert-success text-center" style="border-left: 5px solid #2d8a39;">
                            <i class="fa fa-check-circle fa-3x" style="margin-bottom:12px;"></i>
                            <h4>¡PROCESO COMPLETADO!</h4>
                            <p style="font-size: 16px;">${res.msj}</p>
                            <div style="font-size: 24px; font-weight: bold;">
                                <span class="label label-success">${res.conteo}</span>
                            </div>
                            <p><small>Requerimientos registrados en el sistema.</small></p>
                        </div>`;

                    $('#mensaje').html(html);
                    $('#loads').hide();
                    
                    // Ocultar el botón para evitar doble clic
                    $('#btn_subir').hide();

                    // Recargar la página después de 3 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 3000);
                } else {
                    // LÓGICA DE ERRORES
                    var errorMsg = "<strong>SE ENCONTRARON ERRORES:</strong><ul style='margin-top:12px;'>";
                    $.each(res.errors, function(index, value) {
                        errorMsg += "<li>" + value + "</li>";
                    });
                    errorMsg += "</ul>";
                    
                    $('#mensaje').html('<div class="alert alert-danger">' + errorMsg + '</div>');
                    $('#btn_subir').prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> VALIDAR Y SUBIR ARCHIVO EXCEL');
                    $('#loads').hide();
                }
            },
            error: function() {
                $('#mensaje').html('<div class="alert alert-danger">Error crítico: No se pudo procesar el archivo en el servidor.</div>');
                $('#btn_subir').prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> REINTENTAR SUBIDA');
                $('#loads').hide();
            }
        });
    });
});
		function doSelectAlert(event,tp_id,com_id) {
		    var option = event.srcElement.children[event.srcElement.selectedIndex];
		    if (option.dataset.noAlert !== undefined) {
		        return;
		    }

		    alertify.confirm("CAMBIAR TIPO DE SUBACTIVIDAD ?", function (a) {
	            if (a) {
                	var url = "<?php echo site_url().'/programacion/cservicios/cambia_tp_sact'?>";
			        $.ajax({
			            type: "post",
			            url: url,
			            data:{com_id:com_id,tp_id:tp_id},
			                success: function (data) {
			                window.location.reload(true);
			            }
			        });
	            } else {
	                alertify.error("OPCI\u00D3N CANCELADA");
	            }
          	});
		}
		</script>
		<script type="text/javascript">
	      $(function () {
	        //SUBIR ARCHIVO
	        $("#subir_archivo").on("click", function () {
	          var $valid = $("#form_subir_sigep").valid();
	          if (!$valid) {
	              $validator.focusInvalid();
	          } else {
	            if(document.getElementById('archivo').value==''){
	              alertify.alert('PORFAVOR SELECCIONE ARCHIVO .CSV');
	              return false;
	            }

	            alertify.confirm("REALMENTE DESEA SUBIR ESTE ARCHIVO?", function (a) {
	              if (a) {
	                  document.getElementById("load").style.display = 'block';
	                  document.getElementById('subir_archivo').disabled = true;
	                  document.forms['form_subir_sigep'].submit();
	              } else {
	                  alertify.error("OPCI\u00D3N CANCELADA");
	              }
	            });
	          }
	        });
	      });
	    </script>
		<script type="text/javascript">
		    $(function () {
		        function reset() {
		            $("#toggleCSS").attr("href", "<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css");
		            alertify.set({
		                labels: {
		                    ok: "ACEPTAR",
		                    cancel: "CANCELAR"
		                },
		                delay: 5000,
		                buttonReverse: false,
		                buttonFocus: "ok"
		            });
		        }

		        /*----------- ELIMINAR OPERACIONES ---------------*/
		        $(".del_ff").on("click", function (e) {
		            reset();
		            var name = $(this).attr('name');
		            var nro = $(this).attr('id');
		            var request;
		            alertify.confirm("ESTA SEGURO DE ELIMINAR "+nro+" ACTIVIDADES ?", function (a) {
		                if (a) { 
		                    url = "<?php echo site_url("")?>/prog/delete_operaciones_componente";
		                    if (request) {
		                        request.abort();
		                    }
		                    request = $.ajax({
		                        url: url,
		                        type: "POST",
		                        dataType: "json",
                    			data: "com_id="+name

		                    });

		                    request.done(function (response, textStatus, jqXHR) { 
			                    reset();
			                    if (response.respuesta == 'correcto') {
			                        alertify.alert("LAS OPERACIONES SE ELIMINARON CORRECTAMENTE ", function (e) {
			                            if (e) {
			                                window.location.reload(true);
			                            }
			                        });
			                    } else {
			                        alertify.alert("ERROR AL ELIMINAR OPERACIONES!!!", function (e) {
			                            if (e) {
			                                window.location.reload(true);
			                            }
			                        });
			                    }
			                });
		                    request.fail(function (jqXHR, textStatus, thrown) {
		                        console.log("ERROR: " + textStatus);
		                    });
		                    request.always(function () {
		                        //console.log("termino la ejecuicion de ajax");
		                    });

		                    e.preventDefault();

		                } else {
		                    // user clicked "cancel"
		                    alertify.error("OPCIÓN CANCELADA");
		                }
		            });
		            return false;
		        });

		        /*----------- DESHABILITAR SUB ACTIVIDAD ---------------*/
		        $(".neg_ff").on("click", function (e) {
		            reset();
		            var name = $(this).attr('name');
		            var request;
		            alertify.confirm("ESTA SEGURO EN DESHABILITAR LA SUB ACTIVIDAD ?", function (a) {
		                if (a) { 
		                    url = "<?php echo site_url("")?>/prog/des_sactividad";
		                    if (request) {
		                        request.abort();
		                    }
		                    request = $.ajax({
		                        url: url,
		                        type: "POST",
		                        dataType: "json",
                    			data: "com_id="+name

		                    });

		                    request.done(function (response, textStatus, jqXHR) { 
			                    reset();
			                    if (response.respuesta == 'correcto') {
			                        alertify.alert("LAS SUB ACTIVIDAD SE DESHABILITO CORRECTAMENTE ", function (e) {
			                            if (e) {
			                                window.location.reload(true);
			                            }
			                        });
			                    } else {
			                        alertify.alert("ERROR AL DESHABILITAR !!!", function (e) {
			                            if (e) {
			                                window.location.reload(true);
			                            }
			                        });
			                    }
			                });
		                    request.fail(function (jqXHR, textStatus, thrown) {
		                        console.log("ERROR: " + textStatus);
		                    });
		                    request.always(function () {
		                        //console.log("termino la ejecuicion de ajax");
		                    });

		                    e.preventDefault();

		                } else {
		                    // user clicked "cancel"
		                    alertify.error("OPCIÓN CANCELADA");
		                }
		            });
		            return false;
		        });

		    });
		</script>
		<!-- ============================================================================== -->
		<script src = "<?php echo base_url(); ?>mis_js/programacion/programacion/tablas.js"></script>
	</body>
</html>
