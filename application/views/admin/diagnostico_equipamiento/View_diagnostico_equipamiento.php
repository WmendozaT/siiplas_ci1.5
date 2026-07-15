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
        <!-- FAVICONS -->
        <link rel="stylesheet" type="text/css" media="screen" href="<?php echo base_url(); ?>assets/css/estilosh.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.core.css" />
        <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css" id="toggleCSS" />
        <script src="<?php echo base_url(); ?>assets/lib_alerta/alertify.min.js"></script>

        <meta name="viewport" content="width=device-width">
    </head>
    <body class="">
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
        <!-- END HEADER -->
        <!-- Left panel : Navigation area -->


            <!-- RIBBON -->
            <div id="ribbon">
                <span class="ribbon-button-alignment"> 
                    <span id="refresh" class="btn btn-ribbon" data-action="resetWidgets" data-title="refresh"  rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true">
                        <i class="fa fa-refresh"></i>
                    </span> 
                </span>
                <!-- breadcrumb -->
                <ol class="breadcrumb">
                    <li>CNS</li><li>Formulario de Información Quincenal PEI</li>
                </ol>
            </div>
            <!-- MAIN CONTENT -->
            <div id="content">
                <!-- widget grid -->
                <section id="widget-grid" class="">
                    <div class="row">
                        <?php echo $titulo; ?>
                    </div>
                    <div class="row">
                        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="jarviswidget jarviswidget-color-darken">
                                <header>
                                    <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                                    <h2 class="font-md">Detalle de Requerimientos de Equipamiento Quinquenal</h2>  
                                </header>
                                <div>
                                    <div class="widget-body no-padding">
                                        
                                        <!-- 🌟 AJUSTE 1: Bloque responsivo forzado para Scroll Horizontal Limpio -->
                                        <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid #e2e8f0;">
                                            
                                            <!-- Se removió width="100%" para evitar que aplaste las columnas y se usa table-layout: fixed condicional opcional si es necesario, pero style="min-width: 1600px;" asegura la retícula perfecta -->
                                            <table id="datatable_fixed_column" class="table table-bordered table-striped" style="min-width: 1700px; margin-bottom: 0; font-family: sans-serif;">
                                                <thead>
                                                    <!-- 🌟 AJUSTE 2: Sincronización exacta de las 20 celdas de inputs de búsqueda -->
                                                    <tr>
                                                        <th style="width: 1%;"></th> <!-- # -->
                                                        <th style="width: 5%;"></th> <!-- OPCIONES -->
                                                        <th style="width: 5%;"></th> <!-- ADICIONALES -->
                                                        <th class="hasinput" style="min-width:110px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Distrital.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:130px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Responsable.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:160px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Establecimiento.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:160px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Equipamiento.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:150px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Servicio.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:130px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Ubicación.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:100px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Tipo compra.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:80px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Partida.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:80px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Cantidad.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:100px;">
                                                            <input type="text" class="form-control" placeholder="🔍 C. Unitario.."/>
                                                        </th>
                                                        <th class="hasinput" style="min-width:100px;">
                                                            <input type="text" class="form-control" placeholder="🔍 C. Total.."/>
                                                        </th>
                                                        <!-- 🌟 CORREGIDO: Inyección de THs vacíos para los filtros del quinquenio (Evita descuadre de DataTables) -->
                                                        <th style="width: 4%;"></th> <!-- 2026 -->
                                                        <th style="width: 4%;"></th> <!-- 2027 -->
                                                        <th style="width: 4%;"></th> <!-- 2028 -->
                                                        <th style="width: 4%;"></th> <!-- 2029 -->
                                                        <th style="width: 4%;"></th> <!-- 2030 -->
                                                        <th class="hasinput" style="min-width:150px;">
                                                            <input type="text" class="form-control" placeholder="🔍 Observacion.."/>
                                                        </th>
                                                    </tr>                          
                                                    
                                                    <!-- Encabezado de Títulos con min-width en pixeles -->
                                                    <tr>
                                                        <th style="width:1%; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc; color:#64748b;">#</th>
                                                        <th style="min-width:130px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">OPCIONES</th>
                                                        <th style="min-width:100px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">ADICIONALES</th>
                                                        <th style="min-width:110px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">DISTRITAL</th>
                                                        <th style="min-width:130px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">RESPONSABLE</th>
                                                        <th style="min-width:160px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">ESTABLECIMIENTO / INVERSIÓN</th>
                                                        <th style="min-width:160px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">NOMBRE DEL EQUIPO</th>
                                                        <th style="min-width:150px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">SERVICIO / UNIDAD</th>
                                                        <th style="min-width:130px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">UBICACIÓN FÍSICA</th>
                                                        <th style="min-width:100px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">TIPO COMPRA</th>
                                                        <th style="min-width:80px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">PARTIDA</th>
                                                        <th style="min-width:80px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">CANTIDAD</th>
                                                        <th style="min-width:100px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">COSTO UNITARIO (Bs.)</th>
                                                        <th style="min-width:100px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">COSTO TOTAL (Bs.)</th>
                                                        
                                                        <!-- Temporalidad Quinquenal de Asignación Financiera (Se mantiene tu estilo verde llamativo) -->
                                                        <th style="width:4%; text-align:center; vertical-align: middle; background:#059669; color: #ffffff; font-size: 11px; font-weight: bold;">2026</th>
                                                        <th style="width:4%; text-align:center; vertical-align: middle; background:#059669; color: #ffffff; font-size: 11px; font-weight: bold;">2027</th>
                                                        <th style="width:4%; text-align:center; vertical-align: middle; background:#059669; color: #ffffff; font-size: 11px; font-weight: bold;">2028</th>
                                                        <th style="width:4%; text-align:center; vertical-align: middle; background:#059669; color: #ffffff; font-size: 11px; font-weight: bold;">2029</th>
                                                        <th style="width:4%; text-align:center; vertical-align: middle; background:#059669; color: #ffffff; font-size: 11px; font-weight: bold;">2030</th>
                                                        
                                                        <th style="min-width:150px; text-align:center; vertical-align: middle; font-size: 11px; background:#f8fafc;">OBSERVACIONES</th>
                                                    </tr>
                                                </thead>
                                                <?php echo $listado;?>
                                            </table>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    </div>
                </section>
            </div>
            <!-- END MAIN CONTENT -->
        <script>
            if (!window.jQuery) {
                document.write('<script src="<?php echo base_url(); ?>assets/js/libs/jquery-2.0.2.min.js"><\/script>');
            }
        </script>
        <script>
            if (!window.jQuery.ui) {
                document.write('<script src="<?php echo base_url(); ?>assets/js/libs/jquery-ui-1.10.3.min.js"><\/script>');
            }
        </script>
        <!-- IMPORTANT: APP CONFIG -->
        <script src="<?php echo base_url(); ?>assets/js/session_time/jquery-idletimer.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/app.config.js"></script>
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
        <script src="<?php echo base_url(); ?>assets/js/app.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/speech/voicecommand.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
        <script src="<?php echo base_url(); ?>mis_js/Js_diagnostico/Js_diagnosticoEquipamiento.js"></script> 
    </body>
</html>
