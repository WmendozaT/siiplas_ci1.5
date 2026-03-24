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
                <li class="dropdown-header">Archivos/Tutoriales</li>
                <li><a href="<?php echo base_url(); ?>assets/video/FORMULARIOSPOA/FORM_MOD_4_Y_5_2026.xlsx" style="cursor: pointer;" download>Formulario de Solicitud de Modificacion POA</a></li>
                <li><a href="<?php echo base_url(); ?>assets/video/FORMULARIOSPOA/FORM_SOL_POA_5_2026.xlsx" style="cursor: pointer;" download>Formulario de Solicitud de Certificación POA</a></li>
                <li><a href="<?php echo base_url(); ?>assets/video/FORMULARIOSPOA/FORM_JUST_SALDOS_CPOAS_2026.docx" style="cursor: pointer;" download>Formulario de Reversion de Saldos POA</a></li>
                <li><a href="<?php echo base_url(); ?>assets/video/FORMULARIOSPOA/FORM_JUST_EDICION_CPOAS_2026.docx" style="cursor: pointer;" download>Formulario de Modificación de Certificación POA</a></li>
                <li class="divider"></li>
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
            var ahora = new Date();
            var fechaHora = ahora.toLocaleDateString('es-ES', { 
                day: '2-digit', month: '2-digit', year: 'numeric' 
            }) + ' | ' + ahora.toLocaleTimeString('es-ES', { 
                hour: '2-digit', minute: '2-digit', second: '2-digit' 
            });

            // Insertar en el modal
            $("#fecha_actualizacion").text(fechaHora);
            // Evento para todos los botones de reporte
            $(".btn-reporte").click(function(e) {
                e.preventDefault();

                // --- LÓGICA DE FECHA Y HORA DETALLADA ---
                var ahora = new Date();
                
                // Opciones para el formato de fecha: Día de la semana, día, mes y año
                var opcionesFecha = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                var fechaLarga = ahora.toLocaleDateString('es-ES', opcionesFecha);
                
                // Formato de hora: HH:MM:SS
                var horaLarga = ahora.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });

                // Capitalizar la primera letra del día (ej: lunes -> Lunes)
                var fechaFinal = fechaLarga.charAt(0).toUpperCase() + fechaLarga.slice(1) + " - " + horaLarga;

                // Insertar en los elementos correspondientes
                $("#fecha_completa_txt").text(fechaFinal);
                $("#fecha_loading_txt").text(fechaFinal);
                // ----------------------------------------

                var url_reporte = $(this).attr('href');
                var titulo_reporte = $(this).find('.btn-title').text() || $(this).find('h1').text();

                $("#iframe_pdf").hide().attr("src", ""); 
                $("#loading_pdf").show();
                $(".modal-title-text").text(titulo_reporte);
                
                $("#modal_pdf").modal('show');
                $("#iframe_pdf").attr("src", url_reporte);

                $("#iframe_pdf").on('load', function() {
                    $("#loading_pdf").fadeOut(300, function() {
                        $("#iframe_pdf").fadeIn();
                    });
                });
            });
        });


        ////
        function exportarExcelConLoading(id) {
            var $btn = $('#btn_exportar_excel');
            var $txt = $('#txt_btn_excel');
            var token = new Date().getTime(); // Token único

            // 1. Estado "Cargando"
            $btn.prop('disabled', true).addClass('btn-warning').removeClass('btn-success');
            $txt.html('<i class="fa fa-spinner fa-spin"></i> Generando archivo...');

            // 2. Redirección para iniciar descarga enviando el token
            window.location.href = "<?php echo base_url(); ?>index.php/rep/exportar_poa_uresponsable/" + id + "/" + token;

            // 3. Revisar cada segundo si la cookie del token ya existe
            var checkDownload = setInterval(function() {
                var cookieValue = document.cookie.split('; ').find(row => row.startsWith('downloadToken='));
                
                if (cookieValue && cookieValue.split('=')[1] == token) {
                    // 4. Finalizar Loading
                    clearInterval(checkDownload);
                    document.cookie = "downloadToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; // Borrar cookie
                    $btn.prop('disabled', false).addClass('btn-success').removeClass('btn-warning');
                    $txt.text('Exportar POA.xls');
                }
            }, 1000);
        }
    </script>
</body>
</html>