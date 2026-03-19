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
  </head>
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
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    .admin-footer {
        background: #fdfdfd;
        border-top: 1px solid #e1e4e8;
        padding: 12px 25px;
        margin-top: 50px;
        font-family: "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    .footer-version {
        background: #34495e;
        color: #fff;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        vertical-align: middle;
    }

    .footer-sep {
        margin: 0 10px;
        color: #d1d5da;
    }

    .footer-org {
        color: #586069;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.8px;
    }

    .footer-author {
        font-size: 11px;
        color: #6a737d;
    }

    .footer-author i {
        color: #27ae60;
        margin-right: 5px;
    }

    .footer-author strong {
        color: #24292e;
        text-transform: lowercase;
        background: #f1f8ff;
        padding: 2px 6px;
        border-radius: 3px;
        border: 1px solid #c8e1ff;
    }

    /* Alineación en móviles */
    @media (max-width: 767px) {
        .admin-footer .text-right {
            text-align: left !important;
            margin-top: 8px;
        }
    }
</style>

  <body>
    <!-- Fixed navbar -->
    <div class="navbar navbar-default navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#"><font color="#1c7368"><b><?php echo $this->session->userdata('name')?></b></font></a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="#">Home</a></li>
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" title="Descarga de Archivos / Documentos">Descargas <b class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="<?php echo base_url(); ?>assets/video/configurar_csv.mp4" style="cursor: pointer;" download>Configurar equipo a .CSV</a></li>
                <li class="divider"></li>
                <li class="dropdown-header">Archivos/Tutoriales</li>
                <li><a href="<?php echo base_url(); ?>assets/video/SEGUIMIENTO_POA.pdf" style="cursor: pointer;" download>Manual Notificacion POA</a></li>
                <li><a href="<?php echo base_url(); ?>assets/video/SEGUIMIENTO_POA_2021_ES.pdf" style="cursor: pointer;" download>Seguimiento POA</a></li>
                <li><a href="<?php echo base_url(); ?>assets/video/plantilla_migracion_poa.xlsx" style="cursor: pointer;" download>Plantilla de Migracion POa 2022</a></li>
              </ul>
            </li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="active"><a href="<?php echo base_url(); ?>index.php/admin/logout" title="CERRAR SESI&Oacute;N"><b>SALIR</b></a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </div>

    <div class="container">

    <!-- Main component for a primary marketing message or call to action -->
    <?php echo $dasboard;?>

    </div> <!-- /container -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php echo base_url(); ?>assets/dashboard/jquery-1.js"></script>
    <script src="<?php echo base_url(); ?>assets/dashboard/bootstrap.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
          $('[data-toggle="tooltip"]').tooltip();
            $("#myBtn1").click(function(e) {
                // 1. Mostrar el loading
                $("#overlay-loading").css("display", "flex").hide().fadeIn(300);
                
                // 2. Desactivar el botón visualmente para evitar clics repetidos
                $(this).css("pointer-events", "none").css("opacity", "0.7");
            });
        });
    </script>
    <script type="text/javascript">
        $(document).ready(function() {

            // Evento para todos los botones de reporte
            $(".btn-reporte").click(function(e) {
                e.preventDefault(); // Detiene la navegación normal

                $("#iframe_pdf").hide(); // Ocultamos el reporte anterior
                $("#loading_pdf").show(); // Mostramos la animación de barras

                var url_reporte = $(this).attr('href');
                var titulo_reporte = $(this).find('h1').text(); // Captura el texto (SPO N° 4, etc.)

                // 1. Preparamos el modal (limpiamos el iframe previo y mostramos loading)
                $("#iframe_pdf").hide().attr("src", ""); 
                $("#loading_pdf").show();
                $(".modal-title-text").text(titulo_reporte); // Cambia el título dinámicamente
                
                // 2. Abrimos el modal
                $("#modal_pdf").modal('show');

                // 3. Cargamos la nueva URL
                $("#iframe_pdf").attr("src", url_reporte);

                // 4. Cuando el PDF termina de cargar, ocultamos el loading
                $("#iframe_pdf").on('load', function() {
                    $("#loading_pdf").fadeOut(300, function() {
                        $("#iframe_pdf").fadeIn();
                    });
                });
            });
        });
    </script>
</body>
</html>