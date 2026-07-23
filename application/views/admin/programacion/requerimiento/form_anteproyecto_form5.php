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
        <?php echo $stylo;?>
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
                    <li><a href="<?php echo base_url().'index.php/admin/dashboard';?>" title="MENU PRINCIPAL">DASHBOARD</a></li><li><a href="<?php echo base_url().'index.php/admin/proy/list_proy';?>" title="Lista POA">Programacion POA</a></li><li>Formulario N° 5</li><li>Ante Proyecto POA</li>
                </ol>
            </div>
            <!-- MAIN CONTENT -->
            <div id="content">
                <!-- widget grid -->
                <section id="widget-grid" class="">
                        <?php echo $titulo; ?>
                        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="jarviswidget jarviswidget-color-darken">
                              <header>
                                  <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                                  <h2 class="font-md"><strong></strong></h2>  
                              </header>
                                <div>
                                    <div class="widget-body no-padding">
                                        <?php echo $tabla;?>
                                    </div>
                                    <!-- end widget content -->
                                </div>
                                <!-- end widget div -->
                            </div>
                            <!-- end widget -->
                        </article>
                        <!-- WIDGET END -->
                </section>
            </div>
            <!-- END MAIN CONTENT -->

        <!-- ============ Modal Modificar requerimiento ========= -->
        <div class="modal fade" id="modal_mod_ff" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
              <div class="modal-dialog" id="mdialTamanio">
                <div class="modal-content">
                    <div class="modal-header">
                        <button class="close" data-dismiss="modal" id="amcl" title="SALIR"><span aria-hidden="true">&times; <b>Salir Formulario</b></span></button>
                    </div>
                  <div class="modal-body">
                    <h2 class="alert alert-info"><center>MODIFICAR REQUERIMIENTO</center></h2>
                    <form action="<?php echo site_url().'/programacion/crequerimiento/valida_update_insumo'?>" method="post" id="form_mod" name="form_mod" class="smart-form">
                        <input type="hidden" name="ins_id" id="ins_id">
                            <header><b>DATOS GENERALES DEL REQUERIMIENTO</b></header>
                            <fieldset>
                                <div class="row">
                                    <section class="col col-3">
                                        <label class="label"><b>GRUPO PARTIDA</b></label>
                                        <label class="input">
                                            <select class="form-control" id="par_padre" name="par_padre" title="SELECCIONE GRUPO DE PARTIDA">
                                                <option value="">Seleccione Grupo Partida</option>
                                                <?php 
                                                    foreach($part_padres as $row){ ?>
                                                        <option value="<?php echo $row['par_codigo'];?>" <?php if(@$_POST['pais']==$row['par_codigo']){ echo "selected";} ?>><?php echo $row['par_codigo'].' - '.$row['par_nombre'];?></option>
                                                <?php } ?>        
                                            </select>
                                        </label>
                                    </section>
                                    <section class="col col-3">
                                        <label class="label"><b>PARTIDA</b></label>
                                        <label class="input">
                                            <select class="form-control" id="par_hijo" name="par_hijo" title="SELECCIONE PARTIDA">       
                                            </select>
                                        </label>
                                    </section>
                                    <section class="col col-3">
                                        <label class="label"><b>UNIDAD DE MEDIDA</b></label>
                                        <label class="input">
                                            <input type="text" name="iumedida" id="iumedida" title="MODIFICAR UNIDAD DE MEDIDA">
                                        </label>
                                    </section>
                                    <section class="col col-3">
                                        <label class="label"><b><font color="blue">MONTO SALDO (TECHO)</font></b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-tag"></i>
                                            <input type="hidden" name="saldo" id="saldo">
                                            <input type="text" name="sal" id="sal" disabled="true">
                                        </label>
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-6">
                                        <label class="label"><b>DETALLE</b></label>
                                        <label class="textarea">
                                            <i class="icon-append fa fa-tag"></i>
                                            <textarea rows="2" name="detalle" id="detalle" title="MODIFICAR DETALLE DEL REQUERIMIENTO"></textarea>
                                        </label>
                                    </section>
                                    <section class="col col-2">
                                        <label class="label"><b>CANTIDAD</b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-tag"></i>
                                            <input type="text" name="cantidad" id="cantidad" onkeyup="costo_totalm()" onkeypress="return justNumbers(event);" title="MODIFICAR CANTIDAD">
                                        </label>
                                    </section>
                                    <section class="col col-2">
                                        <label class="label"><b>PRECIO <font color="red">(Máx. 2 decimales)</font></b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-tag"></i>
                                            <input type="text" name="costou" id="costou" onkeyup="costo_totalm()" onkeypress="return justNumbers(event);" onpaste="return false" title="MODIFICAR COSTO UNITARIO" style="font-weight: bold; text-align: right;">
                                        </label>
                                    </section>
                                    <section class="col col-2">
                                        <label class="label"><b>COSTO TOTAL</b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-tag"></i>
                                            <input type="hidden" name="costot" id="costot">
                                            <input type="text" name="costot2" id="costot2" disabled="true">
                                        </label>
                                    </section>
                                </div>

                                <div class="row">
                                    <section class="col col-6">
                                        <label class="label"><b>OBSERVACI&Oacute;N</b></label>
                                        <label class="textarea">
                                            <i class="icon-append fa fa-tag"></i>
                                            <textarea rows="2" name="observacion" id="observacion"></textarea>
                                        </label>
                                    </section>
                                </div>
                                <br>
                                <div id="amtit"></div>
                                <header><b>DISTRIBUCI&Oacute;N FINANCIERA: <?php echo $this->session->userdata('gestion')?></b><br>
                                <label class="label"><div id="ff"></div></label>
                                </header>
                                <br>
                                <div class="row">
                                    <section class="col col-2">
                                        <label class="label"><b>PROGRAMADO TOTAL</b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-money"></i>
                                            <input type="text" name="mtot" id="mtot" value="0" disabled="true">
                                        </label>
                                    </section>
                                </div>
                               <div class="row">
                                <?php 
                                // Arreglo de correspondencia indexada para rotulación corporativa del SIIPLAS v2.0
                                $meses_cns = array(
                                    1  => "ENERO",      2  => "FEBRERO",    3  => "MARZO", 
                                    4  => "ABRIL",      5  => "MAYO",       6  => "JUNIO", 
                                    7  => "JULIO",      8  => "AGOSTO",     9  => "SEPTIEMBRE", 
                                    10 => "OCTUBRE",    11 => "NOVIEMBRE",  12 => "DICIEMBRE"
                                );

                                for ($m = 1; $m <= 12; $m++) { 
                                    // 🛠️ REPARADO: Cada seis meses, forzamos un cierre y apertura de fila para mantener la grilla original col-2 exacta
                                    if ($m == 7) {
                                        echo '</div><div class="row" style="margin-top: 10px;">';
                                    }
                                    ?>
                                    <section class="col col-2">
                                        <label class="label"><b><?php echo $meses_cns[$m]; ?></b></label>
                                        <label class="input">
                                            <i class="icon-append fa fa-money"></i>
                                            <!-- 🌟 INMUNIDAD TRANSACCIONAL: Atributos Name, ID, Eventos y Validaciones clonados al centavo -->
                                            <input type="text" 
                                                   name="mm<?php echo $m; ?>" 
                                                   id="mm<?php echo $m; ?>" 
                                                   value="0" 
                                                   onkeyup="suma_programado_modificado()" 
                                                   onkeypress="return justNumbers(event);" 
                                                   onpaste="return false" 
                                                   required="true" 
                                                   title="PROGRAMACION FINANCIERA MES DE <?php echo $meses_cns[$m]; ?> - <?php echo $this->session->userdata('gestion')?>">
                                        </label>
                                    </section>
                                <?php } ?>
                            </div>

                            </fieldset>
                            
                            <div id="mbut">
                                <footer>
                                    <button type="button" name="subir_mins" id="subir_mins" class="btn btn-info" >MODIFICAR REQUERIMIENTO</button>
                                    <button class="btn btn-default" data-dismiss="modal" id="amcl" title="CANCELAR">CANCELAR</button>
                                </footer>
                                <center><img id="loadm" style="display: none" src="<?php echo base_url() ?>/assets/img/loading.gif" width="45" height="45"></center>
                            </div>
                        </form>
                </div>
              </div>
            </div>
        </div>

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
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/jquery.dataTables.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.colVis.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.tableTools.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatables/dataTables.bootstrap.min.js"></script>
        <script src="<?php echo base_url(); ?>assets/js/plugin/datatable-responsive/datatables.responsive.min.js"></script>
        <script src="<?php echo base_url(); ?>mis_js/programacionpoa/form5.js"></script> 
        <script src = "<?php echo base_url(); ?>mis_js/programacion/programacion/tablas.js"></script>
    </body>
</html>
