<!DOCTYPE html>
<html lang="en"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $this->session->userdata('name')?></title>
    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url(); ?>assets/dashboard/bootstrap.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="<?php echo base_url(); ?>assets/dashboard/navbar-fixed-top.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.core.css" />
    <link rel="stylesheet" href="<?php echo base_url(); ?>assets/themes_alerta/alertify.default.css" id="toggleCSS" />
    <script src="<?php echo base_url(); ?>assets/lib_alerta/alertify.min.js"></script>

<!--       <style>
        #mdialTamanio{
          width: 80% !important;
        }
        #mdialTamanio_psw{
          width: 40% !important;
        }
        #mdialTamanio_saldos{
          width: 65% !important;
        }
        table{
          font-size: 10px;
          width: 100%;
          max-width:1550px;;
          overflow-x: scroll;
        }
        th{
          padding: 1.4px;
          font-size: 10px;
        }
        td{
          font-size: 10px;
        }
        #myModal {
          background: #000000;
          opacity:0.9
        }
      </style> -->
  </head>

  <body >
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <?php echo $cabecera; ?>
    </div>

    <div class="container">

    <!-- Main component for a primary marketing message or call to action -->
    <div class="jumbotron">
        <?php echo $titulo; ?>
    </div>
        
    <section id="widget-grid" class="well">
      <!-- row -->
      
      <?php echo $mensaje_alertas;?>
      <?php echo $seguimiento_poa;?>
      <?php echo $menu_disponible; ?>
      <!-- end row -->
    </section>

    </div> <!-- /container -->

    <?php echo $modal_cambiar_gestion; ?>

    <!-- MODAL SEGUIMIENTO POA FORM 4 -->
    <?php echo $modal_notificacion_form4; ?>


    <!-- MODAL SEGUIMIENTO POA FORM 5 (PROYECTOS DE INVERSION) -->
    <?php echo $modal_notificacion_form4_pi; ?>

    <!-- MODAL SALDOS -->
    <?php echo $get_unidades_seguimiento_poa_mensual;?>

    <?php echo $cambiar_trimestre;?>

    <!--   SEGUIMIENTO A UNIDADES / ESTABLECIMIENTOS POR REGIONAL -->
    <?php echo $select_distrital; ?>

    <!-- Modal listado de Unidades para el seguimiento a Nivel Nacional -->
    <?php echo $get_unidades_seguimiento_poa_mensual_nacional; ?>

    <!--  MODAL DE solicitudes     -->
    <?php echo $solicitudes_pass; ?>
    <!--  END MODAL  -->

    <!--  MODAL DE ALERTA CREDENCIALES     -->
    <?php echo $popup_credenciales; ?>
    <!--  END MODAL  -->

    <!--  MODAL DE ALERTA DE SALDOS     -->
    <?php echo $popup_saldos; ?>
    <!--  END MODAL  -->

    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url(); ?>assets/dashboard/jquery-1.js"></script>
    <script src="<?php echo base_url(); ?>assets/dashboard/bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>mis_js/Js_dashboard.js"></script> 
</body>
</html>