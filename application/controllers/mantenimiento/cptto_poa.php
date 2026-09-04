<?php
class Cptto_poa extends CI_Controller {
    public $rol = array('1' => '1');
    public function __construct(){
        parent::__construct();
        if($this->session->userdata('fun_id')!=null){
            $this->load->model('Users_model','',true);
            if($this->rolfun($this->rol)){ 
                $this->load->library('pdf');
                $this->load->library('pdf2');
                $this->load->model('Users_model','',true);
                $this->load->model('menu_modelo');
                $this->load->model('mantenimiento/model_configuracion');
                $this->load->model('mantenimiento/model_ptto_sigep');
                $this->load->model('programacion/model_proyecto');
                $this->load->model('programacion/model_faseetapa');
                $this->load->model('programacion/insumos/minsumos');
                $this->load->model('programacion/insumos/model_insumo'); /// gestion 2020
                $this->load->model('mantenimiento/model_partidas');
                $this->load->model('reporte_eval/model_evalregional');
                $this->load->model('ejecucion/model_ejecucion');
                $this->load->model('mantenimiento/model_partidas');
                $this->load->library("security");
                $this->gestion = $this->session->userData('gestion');
                $this->rol = $this->session->userData('rol');
                $this->fun_id = $this->session->userData('fun_id');
                $this->tp_adm = $this->session->userData('tp_adm');
                //$this->ppto_poa = $this->session->userData('verif_ppto');
                $this->modulos = $this->session->userData('modulos');
                $this->verif_ppto = $this->session->userData('verif_ppto'); /// AnteProyecto Ptto POA : 0, Ptto Aprobado Sigep : 1
            }
            else{
                redirect('admin/dashboard');
            }
        }
        else{
                redirect('/','refresh');
        }
    }

    /*----- Lista Poa -------*/
    public function list_poa(){ 
      $data['menu']=$this->menu(9);
      //$data['resp']=$this->session->userdata('funcionario');
      $tabla='';
      $tabla.='
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-9">
            <section id="widget-grid" class="well">
                <div class="">
                  <h1><b>UNIDADES ORGANIZACIONALES - '.$this->gestion.'</b>
                </div>
            </section>
        </article>
        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-3" align="center">
            <section id="widget-grid" class="well">
                <a href="#" data-toggle="modal" data-target="#modal_importar" class="btn btn-default importar_ff" title="SUBIR TECHO PRESUPUESTARIO EXCEL">
                  <img src="'.base_url().'assets/Iconos/arrow_up.png" WIDTH="25" HEIGHT="20"/>&nbsp;<b>Subir Techo Presupuestario.xls </b>
                </a>
            </section>
        </article>

        <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
          <div class="jarviswidget jarviswidget-color-darken" >
            <header>
                <span class="widget-icon"> <i class="fa fa-arrows-v"></i> </span>
                <h2 class="font-md"><strong></strong></h2>  
            </header>
              <div>
                  <div class="widget-body no-padding">
                      '.$this->lista_poa_general().'
                  </div>
              </div>
          </div>
        </article>';

        $tabla.=$this->modal_migracion_techo();
         
        $data['lista_poa']=$tabla;
        $this->load->view('admin/mantenimiento/ptto_sigep/vista_asignacion_ppto_poa', $data);
    }


  /*---- Modal para migrar Archivo del techo presupuestario -----*/
  public function modal_migracion_techo(){
    $tabla='';
    $tabla .= '
            <style>
                /* Estilización formal e inmunizada para la rejilla del cargador masivo */
                #dialog_subir { width: 30%;}
            </style>

            <div class="modal fade" id="modal_importar" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true" role="dialog">
                <div class="modal-dialog" id="dialog_subir">
                    <div class="modal-content" style="border-radius: 4px; box-shadow: 0 8px 30px rgba(0,0,0,0.3); border: none; overflow: hidden;">
                        
                        <!-- CABECERA DEL COMPONENTE -->
                        <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 15px 20px;">
                            <button type="button" class="close" data-dismiss="modal" id="amcl" aria-label="Close" style="font-size: 20px; color: #475569; opacity: 0.8; margin-top:2px;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13.5px; text-transform: uppercase; letter-spacing: 0.3px;">
                                <i class="fa fa-upload text-primary"></i> Importar Techo Presupuestario
                            </h4>
                        </div>

                        <!-- CUERPO DEL COMPONENTE TRANSACCIONAL -->
                        <div class="modal-body" style="padding: 25px; background: #ffffff;">
                            
                            <!-- Título e Instrucción -->
                            <div class="text-center" style="margin-bottom: 20px;">
                                <h5 style="font-weight: bold; text-transform: uppercase; color: #334155; font-size:12px; margin:0 0 5px 0;">Subir archivo Excel (.xls, .xlsx)</h5>
                                <p style="font-size:11.5px; margin:0;" class="text-muted">Asegúrese de que su archivo tenga la estructura de columnas indicada abajo:</p>
                            </div>

                            <!-- Vista previa de columnas (Corregido: Concatenación nativa base_url) -->
                            <div class="thumbnail" style="border: 1px dashed #cbd5e1; padding: 10px; background: #f8fafc; box-shadow: none; margin-bottom: 20px;">
                                <img src="' . base_url('assets/img/img_migracion/mig_techo.JPG') . '" class="img-responsive" alt="Ejemplo Excel" style="border-radius: 4px; margin: 0 auto; max-height: 180px;">
                            </div>

                            <!-- Formulario de persistencia binaria (Corregido: Concatenación nativa site_url) -->
                            <form action="' . site_url('mantenimiento/cptto_poa/valida_migracion_techo') . '" method="post" enctype="multipart/form-data" id="form_subir_sigep" autocomplete="off" style="padding:0; background:transparent;">

                                <div class="form-group" style="margin-top: 15px; margin-bottom:0;">
                                    <label style="display: block; font-weight: bold; margin-bottom: 8px; color: #1e293b; font-size: 11.5px;">SELECCIONAR ARCHIVO EXCEL: *</label>
                                    
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary" onclick="$(this).parent().find(\'input[type=file]\').click();" style="border-radius: 3px 0 0 3px; font-weight: bold; height: 32px; font-size: 11.5px; background:#475569; border-color:#475569;">
                                                <i class="fa fa-folder-open"></i> Examinar...
                                            </button>
                                            
                                            <input id="archivo" accept=".xlsx, .xls" name="archivo" 
                                                   onchange="$(this).parent().parent().find(\'.file-name-display\').val($(this).val().split(/[\\\\|/]/).pop());" 
                                                   style="display: none;" type="file" required>
                                        </span>
                                        <input type="text" class="form-control file-name-display" placeholder="No se ha seleccionado archivo" readonly style="background: #ffffff; cursor: default; height: 32px; font-size: 12px; border-color: #cbd5e1; box-shadow:none;">
                                    </div>
                                </div>

                                <div id="mensaje" style="margin: 10px 0; font-size: 11px;"></div>

                                <!-- Botón de Envío y Validación Masiva -->
                                <div style="margin-top: 25px;">
                                    <button type="button" id="btn_subir" class="btn btn-success btn-block" style="font-weight: bold; border-radius: 3px; padding: 8px 16px; font-size: 13px; background: #2e7d32; border-color: #2e7d32; text-transform: uppercase; letter-spacing: 0.3px;">
                                        <i class="fa fa-check-circle"></i> VALIDAR Y SUBIR ARCHIVO
                                    </button>
                                </div>

                                <!-- Animación Pre-Loader de la Planilla -->
                                <div id="loads" class="text-center" style="display: none; margin-top: 20px; padding: 10px; border: 1px dashed #2e7d32; background: #f0fdf4; border-radius: 4px;">
                                    <i class="fa fa-refresh fa-spin fa-2x text-success" style="margin-bottom: 5px;"></i>
                                    <p style="margin: 0; font-size: 11.5px; color: #16a34a;"><b>Sincronizando celdas, por favor espere...</b></p>
                                </div>
                            </form>
                            
                        </div>
                    </div>
                </div>
            </div>';

        $tabla .= '
        <script type="text/javascript">
            // REVISIÓN DE INTEGRIDAD SIIPLAS: Espera nativamente la disponibilidad de JQuery en el DOM
            window.addEventListener("load", function() {
                if (typeof $ !== "undefined") {

                    // Mostrar nombre del archivo al seleccionar en el input simulado de SmartAdmin
                    $(document).on(\'change\', \'#archivo\', function() {
                        var fileName = $(this).val().split(\'\\\\\').pop();
                        if (fileName) {
                            $(\'.file-name-display\').val(fileName);
                        }
                    });

                    // ==========================================================================
                    // ESCUCHA SUBMIT ASÍNCRONA: MIGRACIÓN Y CONSOLIDACIÓN DE FILAS DEL EXCEL
                    // ==========================================================================
                    $(document).on(\'click\', \'#btn_subir\', function(e) {
                        e.preventDefault();
                        $(\'#mensaje\').html(\'\'); 

                        if ($(\'#archivo\').val() == \'\') {
                            $(\'#mensaje\').html(\'<div class="alert alert-warning" style="margin-bottom:0;"><i class="fa fa-exclamation-triangle"></i> Por favor, seleccione un archivo Excel válido.</div>\');
                            if (typeof alertify !== "undefined") {
                                alertify.error("⚠️ Restricción: No se seleccionó ninguna plantilla .CSV o .XLSX");
                            }
                            return false;
                        }

                        var form = $(\'#form_subir_sigep\')[0];
                        var data_multipart = new FormData(form);
                        var $btn = $(this);

                        // Bloquear UI e Inyectar Loader
                        $btn.prop(\'disabled\', true).html(\'<i class="fa fa-refresh fa-spin"></i> PROCESANDO MATRIZ POA...\');
                        $(\'#loads\').show();

                        $.ajax({
                            type: "POST",
                            url: $(\'#form_subir_sigep\').attr(\'action\'),
                            data: data_multipart,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                var res;
                                try {
                                    res = (typeof response === \'object\') ? response : JSON.parse(response);
                                } catch (err) {
                                    console.error("Error parseando JSON:", response);
                                    $(\'#mensaje\').html(\'<div class="alert alert-danger"><b>❌ Error del Servidor:</b> La respuesta de la base de datos PostgreSQL devolvió un buffer corrupto o se agotó el tiempo de espera.</div>\');
                                    $btn.prop(\'disabled\', false).text(\'REINTENTAR ACCIÓN\');
                                    $(\'#loads\').hide();
                                    return;
                                }

                                // Evalúa el éxito transaccional unificado en la CNS
                                if (res.respuesta === \'correcto\' || res.status === \'success\') {
                                    var mensaje_exito = res.mensaje || res.msj || "Registros migrados exitosamente.";
                                    var conteo_filas = res.filas_procesadas || res.conteo || "0";

                                    // Construcción de plantilla visual de éxito en auditoría
                                    var html_success = `
                                        <div class="alert alert-success text-center" style="border-left: 5px solid #2e7d32; background:#f0fdf4; color:#16a34a; padding:15px; margin-bottom:0;">
                                            <i class="fa fa-check-circle fa-3x" style="margin-bottom:10px;"></i>
                                            <h4 style="font-weight:bold; margin:0 0 5px 0; color:#15803d;">¡MIGRACIÓN COMPLETADA CON ÉXITO!</h4>
                                            <p style="font-size: 12.5px; color:#166534; font-weight:500;">${mensaje_exito}</p>
                                            <div style="margin: 10px 0;">
                                                <span class="label label-success" style="font-size: 16px; padding: 4px 12px; font-weight:bold; background:#16a34a;">${conteo_filas}</span>
                                            </div>
                                            <p style="margin:0;"><small class="text-muted">Registros validados e insertados en las aperturas programáticas.</small></p>
                                        </div>`;

                                    $(\'#mensaje\').html(html_success);
                                    $(\'#loads\').hide();
                                    $btn.hide(); 

                                    // Temporizador inteligente multi-rol CNS para refrescar grilla
                                    setTimeout(function() {
                                        $(\'#modal_importar\').modal("hide");
                                        $(\'.modal-backdrop\').remove();
                                        $(\'body\').removeClass(\'modal-open\').css(\'padding-right\', \'\');

                                        var combo_admin = $(\'#dist_id\').val();
                                        if (combo_admin !== undefined && combo_admin !== "" && combo_admin !== "0") {
                                            $("#dist_id").trigger("change");
                                        } else {
                                            if (typeof forzar_refresco_grilla_siiplas_directo === "function") {
                                                var dist_id_oculto = $(\'input[name="dist_id"]\').val() || 0;
                                                forzar_refresco_grilla_siiplas_directo(dist_id_oculto);
                                            } else {
                                                location.reload(); 
                                            }
                                        }
                                    }, 2500);

                                } else {
                                    // LÓGICA COHERENTE DE EXTRACCIÓN DE ALERTAS Y ERRORES DE CONSISTENCIA
                                    var mensaje_error = res.mensaje || res.msj || "El archivo contiene celdas inválidas.";
                                    var errorMsg = `<strong style="font-size:12px; color:#b91c1c;"><i class="fa fa-times-circle"></i> SE DETECTARON INCONSISTENCIAS EN LA PLANILLA:</strong><br><small class="text-muted">${mensaje_error}</small>`;
                                    
                                    if (res.errors || res.errores) {
                                        var coleccion_errores = res.errors || res.errores;
                                        errorMsg += "<ul style=\'margin-top:8px; padding-left:15px; text-align:left; font-size:11px;\'>";
                                        $.each(coleccion_errores, function(index, value) {
                                            errorMsg += "<li>" + value + "</li>";
                                        });
                                        errorMsg += "</ul>";
                                    }
                                    
                                    $(\'#mensaje\').html(\'<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2; border-color:#fee2e2; color:#991b1b;">\' + errorMsg + \'</div>\');
                                    $btn.prop(\'disabled\', false).html(\'<i class="fa fa-file-excel-o"></i> REINTENTAR VALIDACIÓN Y SUBIDA\');
                                    $(\'#loads\').hide();
                                }
                            },
                            error: function(xhr, textStatus, errorThrown) {
                                // 🌟 DETECTOR FORENSE INTEGRADO: Intercepta el colapso 500 y expone el Fatal Error en caliente
                                $(\'#loads\').hide();
                                $btn.prop(\'disabled\', false).html(\'<i class="fa fa-file-excel-o"></i> REINTENTAR SUBIDA\');

                                var errorDetallado = xhr.responseText;
                                var mensajeVisible = "<strong>🚨 FALLA CRÍTICA EN EL SERVIDOR (HTTP 500):</strong><br>";
                                mensajeVisible += "<small class=\'text-muted\'>Apache local abortó el hilo de procesamiento PHPExcel. Detalle del volcado:</small><hr>";
                                
                                if (errorDetallado) {
                                    mensajeVisible += "<div style=\'max-height: 200px; overflow-y: auto; background: #fff5f5; padding: 10px; border: 1px solid #fee2e2; font-family: monospace; font-size: 11px; text-align: left; color: #991b1b;\'>";
                                    mensajeVisible += errorDetallado;
                                    mensajeVisible += "</div>";
                                } else {
                                    mensajeVisible += "El backend no retornó ninguna cadena de caracteres. Verifique directivas max_input_vars en su php.ini.";
                                }

                                $(\'#mensaje\').html(\'<div class="alert alert-danger" style="margin-bottom:0; background:#fef2f2;">\' + mensajeVisible + \'</div>\');
                            }
                        });
                    });

                }
            });
        </script>';

      return $tabla;
  }

  /*---- Lista Gasto Corriente / Inversion-----*/
  public function lista_poa_general(){
    // Trae todo el listado consolidado en una sola consulta
    $unidades = $this->model_proyecto->lista_poa_consolidado();
    $tabla = '';
    
    $tabla .= '
    <input name="base" type="hidden" value="'.base_url().'">
    <table id="dt_basic" class="table table-bordered table-striped" style="width:100%;">
      <thead>
        <tr style="height:50px;">
          <th style="width:1%;">#</th>
          <th style="width:5%;">PARTIDAS</th>
          <th style="width:5%;" title="DIRECCION ADMINISTRATIVA">D.A.</th>
          <th style="width:5%;" title="UNIDAD EJECUTORA">U.E.</th>
          <th style="width:10%;">PROGRAMA '.$this->gestion.'</th>
          <th style="width:25%;">GASTO CORRIENTE / INVERSIÓN</th>
          <th style="width:10%;">DISTRITAL</th>
          <th style="width:10%;">ESTADO</th>
          <th style="width:10%;">TIPO DE GASTO</th>
          <th style="width:10%;">PPTO. ASIGNADO</th>
          <th style="width:10%;">PPTO. POA</th>
          <th style="width:10%;">SALDO</th>
        </tr>
      </thead>
      <tbody>';
      
      $nro = 0;
      foreach($unidades as $row){
        $titulo='<b>'.htmlentities($row['proy_nombre']).'</b>';
        if($row['tp_id'] == 4){
          $titulo='<b>'.$row['tipo'].' '.htmlentities($row['proy_nombre']).' '.$row['abrev'].'</b>';
        }

          $nro++;
          $tabla .= '<tr>';
          $tabla .= '<td align="center">'.$nro.'<br>par_id: '.$row['aper_id'].'</td>';
          
          // BOTÓN DEL MODAL (Carga datos dinámicamente vía AJAX usando aper_id)
          $tabla .= '
          <td style="text-align: center; vertical-align: middle; white-space: nowrap; background: #ffffff; border: 1px solid #cbd5e1;">
                      <div style="display: inline-flex; gap: 4px; justify-content: center; width: 100%;">
                        <button type="button" 
                                class="btn btn-xs btn-default btn-ver-partidas-unidad" 
                                data-id="' . $row['aper_id']. '" 
                                data-codigo="' . $row['aper_id'] . '" 
                                data-nombre="' . $titulo . '" 
                                data-toggle="modal" 
                                data-target="#modal_desglose_partidas_unidad"
                                title="VER MATRIZ DETALLE DE PPTO POR PARTIDAS" 
                                style="font-weight: bold; height:50px; padding: 5px 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius:3px; color:#334155;">
                            <i class="fa fa-table text-info"></i> Ver Detalle de Partidas
                        </button>
                      </div>
                    </td>';
                    
          $tabla .= '<td align="center"><b>'.$row['da'].'</b></td>';
          $tabla .= '<td align="center"><b>'.$row['ue'].'</b></td>';
          
          if($row['tp_id'] == 1){
              $tabla .= '<td><center>'.$row['proy_sisin'].'</center></td>';
          } else {
              $tabla .= '<td><center>'.$row['aper_programa'].' '.$row['aper_proyecto'].' '.$row['aper_actividad'].'</center></td>';
          }
          
          $tabla .= '<td>'.$titulo.'</td>';
          $tabla .= '<td>'.strtoupper($row['dist_distrital']).'</td>';
          $tabla .= '<td>'.strtoupper($row['estado_poa']).'</td>';
          $tabla .= '<td>'.strtoupper($row['tipo_gasto_nombre']).'</td>';
          $tabla .= '<td>'.number_format($row['ppto_asignado'], 2, ',', '.').'</td>';
          $tabla .= '<td>'.number_format($row['ppto_poa'], 2, ',', '.').'</td>';
          $tabla .= '<td>'.number_format($row['ppto_saldo'], 2, ',', '.').'</td>';
          $tabla .= '</tr>';
      }
      
      $tabla .= '
      </tbody>
    </table>';

// 3. ESTRUCTURA DEL MODAL BOOTSTRAP INYECTADA EN LA VARIABLE
    $tabla.= $this->modal_partidas_asignadas_UnidadOrganizacional();

    return $tabla;
}




//// Modal lista de partidas programados por Unidad Organizacional
    public function modal_partidas_asignadas_UnidadOrganizacional(){
        $tabla='';
        $tabla.='
        <div class="modal fade" id="modal_desglose_partidas_unidad" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true" style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); background: rgba(15, 23, 42, 0.45);">
            <div class="modal-dialog modal-lg" style="width: 70% !important; max-width: 95%; margin: 25px auto;">
                <div class="modal-content" style="border-radius: 4px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: none; overflow: hidden;">
                    
                    <!-- CABECERA DEL MODAL CORPORATIVA -->
                    <div class="modal-header" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between;">
                        <h4 class="modal-title" style="font-weight: bold; color: #1e293b; font-size: 13px; text-transform: uppercase; letter-spacing: 0.3px; margin:0;">
                            <i class="fa fa-folder-open text-primary"></i> Desglose de Presupuesto Programado por Partidas
                        </h4>
                        <!-- 🛠️ REPARADO: Añadido data-dismiss para que la "x" superior limpie los flujos y cierre la ventana -->
                        <button type="button" class="close" data-dismiss="modal" style="font-size: 20px; color: #475569; opacity: 0.8; border:none; background:none; cursor:pointer;">&times;</button>
                    </div>

                    <!-- CUERPO RECEPTOR DINÁMICO AJAX -->
                    <div class="modal-body" id="contenedor_desglose_dinamico_cns" style="padding: 20px 25px; background: #ffffff; max-height: calc(100vh - 165px); overflow-y: auto;">
                        <!-- Aquí el JS estampará el spinner y luego la matriz cruzada de saldos -->
                    </div>
                    
                    <!-- PIE DE VENTANA ACCIONES -->
                    <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 10px 20px; text-align: right; margin:0;">
                        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal" style="font-weight: bold; font-size: 11.5px; padding: 6px 16px; border-radius: 3px; background:#64748b; color:#fff; border-color:#475569;">
                            <i class="fa fa-arrow-circle-left"></i> Cerrar Ventana
                        </button>
                    </div>

                </div>
            </div>
        </div>';

        return $tabla;
    }



    //// get detalle de partidas por Unidad Organizacional
       public function get_desglose_partidas_unidad_ajax() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            $aper_id = intval($this->input->post('aper_id'));
            $g_id   = intval($this->gestion);

            $list_partidas = $this->model_ptto_sigep->get_lista_ppto_partidas_UOrganizacional($aper_id);
            
            // 🎯 OBTENER EL CATÁLOGO GLOBAL DE PARTIDAS PARA EL SELECTOR
            $catalogo_partidas = $this->model_partidas->lista_partida_dependientes();

            $total_asignado            = 0;
            $total_asignado_mod        = 0;
            $total_programado          = 0;
            $total_saldo               = 0;
            $total_asig_revertido      = 0;
            $total_prog_revertido      = 0;
            $total_saldo_revertido     = 0;

            if (!empty($list_partidas)) {
                foreach ($list_partidas as $row) {
                    $total_asignado        += floatval($row['ppto_asignado']);
                    $total_asignado_mod    += floatval($row['ppto_asignado']); // Toma el valor base inicializado
                    $total_programado      += floatval($row['ppto_programado']);
                    $total_saldo           += floatval($row['saldo_poa']);
                    $total_asig_revertido  += floatval($row['ppto_asignado_revertido']);
                    $total_prog_revertido  += floatval($row['ppto_programado_revertido']);
                    $total_saldo_revertido += floatval($row['saldo_revertido']);
                }
            }

            
            // Modificamos la cabecera para incrustar el selector a la izquierda del botón imprimir
            $html = '
             
            <div id="contenedor_general_partidas" style="font-family: Arial, sans-serif; margin-bottom: 15px;">
              <!-- 🧮 PANEL DE SUMATORIAS TOTALES (Estilo Tarjetas) -->
              <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1-fr)); gap: 10px; margin-bottom: 15px;">
                  
                  <div style="background: #1e3a8a; color: white; padding: 10px; border-radius: 4px; text-align: center; border-left: 5px solid #1d4ed8;">
                      <div style="font-size: 9px; text-transform: uppercase; font-weight: bold; opacity: 0.9;">Total Presupuesto Asignado</div>
                      <div style="font-size: 14px; font-weight: bold; margin-top: 3px;">Bs. ' . number_format($total_asignado, 2, ',', '.') . '</div>
                  </div>

                  <div style="background: #1e293b; color: white; padding: 10px; border-radius: 4px; text-align: center; border-left: 5px solid #475569;">
                      <div style="font-size: 9px; text-transform: uppercase; font-weight: bold; opacity: 0.9;">Total Programado POA</div>
                      <div style="font-size: 14px; font-weight: bold; margin-top: 3px; color: #22c55e;">Bs. ' . number_format($total_programado, 2, ',', '.') . '</div>
                  </div>

                  <div style="background: #0f172a; color: white; padding: 10px; border-radius: 4px; text-align: center; border-left: 5px solid #0aa699;">
                      <div style="font-size: 9px; text-transform: uppercase; font-weight: bold; opacity: 0.9;">Total Saldo Disponible</div>
                      <div style="font-size: 14px; font-weight: bold; margin-top: 3px; color: ' . ($total_saldo < 0 ? '#ef4444' : '#38bdf8') . ';">Bs. ' . number_format($total_saldo, 2, ',', '.') . '</div>
                  </div>

                  <div style="background: #fdf2f2; color: #991b1b; padding: 10px; border-radius: 4px; text-align: center; border: 1px solid #fca5a5; border-left: 5px solid #dc2626;">
                      <div style="font-size: 9px; text-transform: uppercase; font-weight: bold; color: #7f1d1d;">Total Saldo Revertido</div>
                      <div style="font-size: 14px; font-weight: bold; margin-top: 3px;">Bs. ' . number_format($total_saldo_revertido, 2, ',', '.') . '</div>
                  </div>

              </div>

            <div style="margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                <!-- 🛠️ SELECTOR Y MONTO EN CALIENTE PARA ADICIONAR NUEVA PARTIDA -->
                <div style="display: flex; gap: 6px; width: 75%;">
                    <!-- Selector de Partida -->
                    <select id="select_nueva_partida" class="form-control" style="font-family: Arial, sans-serif; font-size: 11.5px; height: 28px; padding: 2px 8px; width: 60%;">
                        <option value="">-- Seleccione una Partida para Adicionar --</option>';
                        if(!empty($catalogo_partidas)) {
                            foreach($catalogo_partidas as $p_cat) {
                                $html .= '<option value="' . $p_cat['par_id'] . '">' . $p_cat['chijo'] . ' - ' . strtoupper($p_cat['phijo']) . '</option>';
                            }
                        }
            $html .= '
                    </select>
                    
                    <!-- Input para el Monto Inicial -->
                    <input type="number" 
                           id="monto_nueva_partida" 
                           class="form-control" 
                           placeholder="Monto Asig. (Bs.)" 
                           step="0.01" 
                           min="0"
                           style="font-family: Arial, sans-serif; font-size: 11.5px; height: 28px; width: 25%; text-align: right; font-weight: bold; color: #1e40af;">

                    <!-- Botón Adicionar -->
                    <button type="button" class="btn btn-xs btn-success" onclick="guardarNuevaPartidaModal(' . $aper_id . ');" style="font-weight: bold; height: 28px; padding: 0 14px; font-size:11px; border-radius:3px; cursor:pointer; width: 15%;">
                        <i class="fa fa-plus"></i> Adicionar
                    </button>
                </div>
                
                <div>
                    <button type="button" class="btn btn-xs btn-default" onclick="imprimirDetallePartidasModal();" style="font-family: Arial, sans-serif; font-weight: bold; background: #ffffff; border: 1px solid #cbd5e1; padding: 5px 14px; font-size:11px; color:#334155; border-radius:3px; cursor:pointer; transition: all 0.15s ease;">
                        <i class="fa fa-print text-primary" style="font-size:12px;"></i> Imprimir Detalle
                    </button>
                </div>
            </div>';

            // ==========================================================================
            // 📊 SUPERESTRUCTURA DE LA TABLA EJECUTIVA FORMAL
            // ==========================================================================
            $html .= '
            <div id="area_impresion_detalle_partidas" class="table-responsive" style="border: 1px solid #cbd5e1; border-radius: 4px;">
                <table class="table table-bordered table-striped table-hover" style="width:100%; margin-bottom: 0; font-family: Arial, sans-serif; font-size: 11.5px; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #334155; color: #ffffff; text-transform: uppercase; font-size: 10px; height: 40px; letter-spacing:0.3px;">
                            <th style="padding: 8px; text-align: center; width: 5%; background: #991b1b; vertical-align: middle;">ACCIONES</th>
                            <th style="padding: 8px; text-align: center; width: 18%; background: #1e293b; vertical-align: middle;">PARTIDA PRESUPUESTARIA</th>
                            <th style="padding: 8px; text-align: center; width: 10%; background: #1e3a8a; vertical-align: middle;">ASIGNADO (Bs.)</th>
                            <th style="padding: 8px; text-align: center; width: 13%; background: #1e3a8a; vertical-align: middle;">ASIGNADO MOD.(Bs.)</th>
                            <th style="padding: 8px; text-align: center; width: 10%; background: #0aa699; vertical-align: middle;">PROGRAMADO POA (Bs.)</th>
                            <th style="padding: 8px; text-align: center; width: 10%; background: #475569; vertical-align: middle;">SALDO DISPONIBLE (Bs.)</th>

                            <th style="padding: 8px; text-align: center; color:red; width: 10%; background: #FADCE1; vertical-align: middle;">PPTO. ASIG. REVERTIDO (Bs.)</th>
                            <th style="padding: 8px; text-align: center; color:red; width: 10%; background: #FADCE1; vertical-align: middle;">PPTO. POA. REVERTIDO (Bs.)</th>
                            <th style="padding: 8px; text-align: center; color:red; width: 10%; background: #FADCE1; vertical-align: middle;">PPTO. SALDO. REVERTIDO (Bs.)</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpo_tabla_partidas">';
            
            // 🛠️ AJUSTADO A 6 COLUMNAS REALES
            if(empty($list_partidas)) {
                $html .= '<tr><td colspan="9" class="text-center" style="padding: 15px; font-weight: bold; color: #64748b;"><i class="fa fa-info-circle"></i> Sin requerimientos presupuestarios asignados en esta unidad.</td></tr>';
            } else {
                 foreach($list_partidas as $row) {
                  $saldo_item = floatval($row['saldo_poa']);
                  $saldo_revertido = floatval($row['saldo_revertido']);
                  $sp_id = intval($row['sp_id']);
                  
                  $style_saldo = ($saldo_item < 0) ? 'background: #fef2f2; color: #dc2626; font-weight: bold;' : 'background: #f8fafc; color: #334155; font-weight: bold;';
                  $style_saldo_rev = ($saldo_revertido < 0) ? 'background: #fef2f2; color: #dc2626; font-weight: bold;' : 'background: #f8fafc; color: #334155; font-weight: bold;';

                  $html .= '<tr id="fila_partida_' . $sp_id . '" style="height: 28px; vertical-align: middle;">';
                  $html .= '<td style="text-align: center; padding: 4px;">';
                  if ($row['ppto_asignado']== 0 && $row['ppto_inicial_aprobado']== 0) {
                      $html .= '<button type="button" class="btn btn-xs btn-danger" title="Eliminar Partida" onclick="eliminarPartidaAsignada(' . $sp_id . ');" style="padding: 2px 8px;">';
                      $html .= '<i class="fa fa-trash"> Eliminar</i>';
                      $button_text = '</button>';
                  } else {
                      $html .= '-';
                  }
                  $html .= '</td>';
                  $html .= '<td style="font-weight: bold; color: #0f172a; padding-left: 8px;">' . $row['codigo_partida'] . ' - ' . strtoupper($row['partida']) . '</td>';
                  $html .= '<td style="text-align: right; padding-right: 8px; font-weight: bold; color: blue;">' . number_format($row['ppto_asignado'], 2, ',', '.') . '</td>';
                  
                  // 🎯 COLUMNA ASIGNADO MOD. (TIPO 0)
                  $html .= '<td style="text-align: right; padding: 4px; font-weight: bold; color: #1e40af;">';
                  if ($sp_id > 0) {
                      // 🛠️ CORREGIDO ID A: monto_0_...
                      $html .= '<input type="number" 
                                 step="0.01" 
                                 class="form-control" 
                                 style="text-align: right; font-weight: bold; color: #1e40af; height: 26px; font-size: 11.5px; width: 100%;" 
                                 id="monto_0_' . $sp_id . '" 
                                 data-programado="' . $row['ppto_programado'] . '"
                                 data-tp="0" 
                                 value="' . round($row['ppto_asignado'], 2) . '" 
                                 onchange="actualizarMontoAsignado(' . $sp_id . ', 0);">'; // Se pasa el tipo 0
                  } else {
                      $html .= '<span class="text-muted" style="font-size:10px;">SIN PPTO. ASIG.</span>';
                  }
                  $html .= '</td>';

                  $html .= '<td style="text-align: right; padding-right: 8px; font-weight: bold; color: #16a34a;">' . number_format($row['ppto_programado'], 2, ',', '.') . '</td>';
                  $html .= '<td style="text-align: right; padding-right: 8px; ' . $style_saldo . '" id="saldo_0_' . $sp_id . '">' . number_format($saldo_item, 2, ',', '.') . '</td>';
                  
                  // 🎯 COLUMNA ASIGNADO REVERTIDO (TIPO 1)
                  $html .= '<td style="background:#F5E9EA; padding: 4px;">';
                  if ($sp_id > 0) {
                      // 🛠️ CORREGIDO ID A: monto_1_... y corregido data-programado a ppto_programado_revertido
                      $html .= '<input type="number" 
                                 step="0.01" 
                                 class="form-control" 
                                 style="text-align: right; font-weight: bold; color: #dc2626; height: 26px; font-size: 11.5px; width: 100%; background: #fff5f5;" 
                                 id="monto_1_' . $sp_id . '" 
                                 data-programado="' . $row['ppto_programado_revertido'] . '"
                                 data-tp="1"
                                 value="' . round($row['ppto_asignado_revertido'], 2) . '" 
                                 onchange="actualizarMontoAsignado(' . $sp_id . ', 1);">'; // Se pasa el tipo 1
                  } else {
                      $html .= '-';
                  }
                  $html .= '</td>';
                  
                  $html .= '<td style="background:#F5E9EA; text-align: right; padding-right: 8px;">' . number_format($row['ppto_programado_revertido'], 2, ',', '.') . '</td>';
                  // Se añade id único al saldo revertido para refrescarlo en caliente
                  $html .= '<td style="background:#F5E9EA; text-align: right; padding-right: 8px; ' . $style_saldo_rev . '" id="saldo_1_' . $sp_id . '">' . number_format($saldo_revertido, 2, ',', '.') . '</td>';

                  $html .= '</tr>';
              }
            }
            $html .= '</tbody></table></div></div>';

            while (ob_get_level() > 0) { ob_end_clean(); }
            header('Content-Type: application/json; charset=utf-8');

            echo json_encode(array(
                'status' => 'success',
                'respuesta' => 'correcto',
                'html_reporte' => $html
            ));
            exit;
        }
    }


    //// Actualizar monto en la partida asignada
    public function actualizar_monto() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            $sp_id   = intval($this->input->post('sp_id'));
            $importe = floatval($this->input->post('importe'));
            $tipo    = intval($this->input->post('tipo')); // 0 = Normal, 1 = Revertido

            if ($sp_id > 0) {
                // Evaluamos qué campo de la base de datos modificar
                if ($tipo === 1) {
                    // Actualiza el Presupuesto Asignado Revertido
                    $data = array('ppto_saldo_ncert' => $importe); 
                } else {
                    // Actualiza el Presupuesto Asignado Normal
                    $data = array('importe' => $importe); 
                }

                $this->db->where('sp_id', $sp_id);
                $this->db->update('ptto_partidas_sigep', $data);

                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'success'));
                exit;
            }
        }
        echo json_encode(array('status' => 'error'));
        exit;
    }

    //// Agregar nueva partida al listado
    public function guardar_nueva_partida() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            $aper_id = intval($this->input->post('aper_id'));
            $proyecto = $this->model_proyecto->get_aper_programa($aper_id); 
            $par_id  = intval($this->input->post('par_id'));
            $partida = $this->model_partidas->get_partida($par_id);
            $importe = floatval($this->input->post('importe')); // 🎯 Captura el monto enviado
            $g_id    = intval($this->gestion); 

            if ($aper_id > 0 && $par_id > 0) {

                if(count($this->model_ptto_sigep->vista_get_seguimiento_partida_UOrganizacional($aper_id, $par_id)) == 0) {
                    
                    // 🛠️ REPARADO: Se eliminó la clave duplicada 'par_id' y se estructuró correctamente
                    $insert_data = array(
                        'aper_id'          => $aper_id,
                        'par_id'           => $par_id,         // Guarda la llave primaria numérica
                        'partida'          => $partida[0]['par_codigo'],
                        'da'               => $proyecto[0]['da'],
                        'ue'               => $proyecto[0]['ue'],
                        'aper_programa'   => $proyecto[0]['aper_programa'],
                        'aper_proyecto'   => $proyecto[0]['aper_proyecto'],
                        'aper_actividad'  => $proyecto[0]['aper_actividad'],
                        'g_id'             => $g_id,
                        'importe'          => $importe,        // Guarda el monto digitado
                        'ppto_inicial'     => $importe,        // Presupuesto inicial
                        'ppto_saldo_ncert' => 0.00,
                        'fun_id'           => $this->fun_id,   // ID del funcionario que registra
                        'estado'           => 1                // Estado activo (diferente de 3)
                    );
                    
                    // Ejecuta la inserción directa
                    $this->db->insert('ptto_partidas_sigep', $insert_data);

                    // Limpieza de buffers contra caracteres basura
                    while (ob_get_level() > 0) { ob_end_clean(); }
                    header('Content-Type: application/json; charset=utf-8');
                    
                    echo json_encode(array('status' => 'success'));
                    exit;
                }
                else {
                    while (ob_get_level() > 0) { ob_end_clean(); }
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(array(
                        'status' => 'error', 
                        'msg'    => 'La partida seleccionada ya se encuentra asignada a la unidad organizacional.'
                    ));
                    exit;
                }
            }
        }
        
        while (ob_get_level() > 0) { ob_end_clean(); }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('status' => 'error', 'msg' => 'Petición inválida o datos incorrectos.'));
        exit;
    }


    /// eliminar registro de la partida
    public function eliminar_asignacion() {
        if ($this->input->is_ajax_request() && $this->input->post()) {
            $sp_id = intval($this->input->post('sp_id'));
            
            if ($sp_id > 0) {
                // Borrado lógico cambiando el estado a 3 para que no aparezca en tu query original
                $data = array('estado' => 3);
                $this->db->where('sp_id', $sp_id);
                $this->db->update('ptto_partidas_sigep', $data);
                
                while (ob_get_level() > 0) { ob_end_clean(); }
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('status' => 'success'));
                exit;
            }
        }
        echo json_encode(array('status' => 'error', 'msg' => 'Petición inválida o ID incorrecto.'));
        exit;
    }




    //// PARA TODAS LAS UNIDADES DE LA UNIDAD ORGANIZACIONAL
       public function valida_migracion_techo() {
        ini_set('max_execution_time', 1200); 
        ini_set('memory_limit', '1024M'); 

        $this->load->library('excel'); 
        if (!isset($_FILES['archivo']) || empty($_FILES['archivo']['tmp_name'])) {
            echo json_encode(array('status' => 'error', 'errors' => array('Por favor, seleccione un archivo Excel válido.')));
            return;
        }

        // 🌟 CRÍTICO PARA CI 1.5: Apagamos el historial de consultas en RAM para evitar que colapse con 4,000 filas
        $this->db->queries = array();
        $this->db->query_times = array();

        $archivo = $_FILES['archivo']['tmp_name'];
        $errores = array();
        $data_insertar = array();

        try {
            $archivoTipo = PHPExcel_IOFactory::identify($archivo);
            $lector      = PHPExcel_IOFactory::createReader($archivoTipo);
            
            $lector->setReadDataOnly(true);
            $phpExcel    = $lector->load($archivo);
            $hoja        = $phpExcel->getSheet(0);
            $filasMax    = $hoja->getHighestRow();
            
            $columnaMaxLetra = $hoja->getHighestDataColumn(); 
            $totalColumnas   = PHPExcel_Cell::columnIndexFromString($columnaMaxLetra);
            $limitePermitido = 8; 

            if ($totalColumnas != $limitePermitido) {
                echo json_encode(array('status' => 'error', 'errors' => array("El archivo tiene $totalColumnas columnas. El formato oficial exige exactamente $limitePermitido columnas.")));
                return;
            }
            
            // --- 2. VALIDACIÓN FILA POR FILA ---
            for ($i = 2; $i <= $filasMax; $i++) {
                $error_fila = false; 
                
                $tp_gasto         = trim($hoja->getCell('A' . $i)->getFormattedValue());
                $cod_da           = trim($hoja->getCell('B' . $i)->getFormattedValue());
                $cod_ue           = trim($hoja->getCell('C' . $i)->getFormattedValue());
                $cod_prog         = trim($hoja->getCell('D' . $i)->getFormattedValue());
                $cod_proy         = trim($hoja->getCell('E' . $i)->getFormattedValue());
                $cod_act          = trim($hoja->getCell('F' . $i)->getFormattedValue());
                $cod_partida      = trim($hoja->getCell('G' . $i)->getFormattedValue());
                $importe          = trim($hoja->getCell('H' . $i)->getFormattedValue());

                if (empty($tp_gasto) && empty($cod_da) && (empty($importe) || floatval($importe) == 0)) {
                    continue;
                }

                if (is_numeric($cod_da))   $cod_da   = str_pad($cod_da, 2, "0", STR_PAD_LEFT);
                if (is_numeric($cod_ue))   $cod_ue   = str_pad($cod_ue, 2, "0", STR_PAD_LEFT);
                if (is_numeric($cod_prog)) $cod_prog = str_pad($cod_prog, 2, "0", STR_PAD_LEFT);
                if (is_numeric($cod_act))  $cod_act  = str_pad($cod_act, 2, "0", STR_PAD_LEFT);

                if (!empty($cod_da) && !empty($cod_ue) && !empty($cod_prog) && $cod_act !== '') {
                    $aper = $this->model_ptto_sigep->get_apertura($cod_da, $cod_ue, $cod_prog, $cod_proy, $cod_act);
                    if(empty($aper) || count($aper) == 0){
                        $errores[] = "Fila $i: No existe la Apertura Estructurada (DA:$cod_da UE:$cod_ue PROG:$cod_prog PROY:$cod_proy ACT:$cod_act) en la BD.";
                        $error_fila = true;
                    }
                } else {
                    $errores[] = "Fila $i: Los códigos de estructura (DA, UE, PROG, ACT) son obligatorios.";
                    $error_fila = true;
                }

                // Validación de Partida
                $par_id = 0;
                if (strlen($cod_partida) == 5) {
                    $partida = $this->model_insumo->get_partida_codigo($cod_partida);
                    if(!empty($partida) && count($partida) != 0){
                        $par_id = $partida[0]['par_id']; 
                    } else {
                        $errores[] = "Fila $i: El código de Partida ($cod_partida) no está catalogado.";
                        $error_fila = true;
                    }
                } else {
                    $errores[] = "Fila $i: El código de la Partida debe tener 5 dígitos (Ejem: 22210).";
                    $error_fila = true;
                }

                // 🛠️ Limpieza de formato monetario por si vienen comas o puntos de millar en el Excel
                $monto_limpio = str_replace(array('.', ','), array('', '.'), $importe);
                $monto_final  = floatval($monto_limpio);

                if ($monto_final <= 0) {
                    $errores[] = "Fila $i: El importe debe ser un monto numérico mayor a cero.";
                    $error_fila = true;
                }

                if (!$error_fila) {
                    $data_insertar[] = array(
                        'aper_id'            => intval($aper[0]['aper_id']), 
                        'da'                 => $aper[0]['da'],
                        'ue'                 => $aper[0]['ue'],
                        'aper_programa'      => $aper[0]['aper_programa'],
                        'aper_proyecto'      => $aper[0]['aper_proyecto'],
                        'aper_actividad'     => $aper[0]['aper_actividad'],
                        'par_id'             => intval($par_id), 
                        'partida'            => $cod_partida,
                        'importe'            => $monto_final,
                        'g_id'               => intval($this->gestion), 
                        'estado'             => 1,
                        'fun_id'             => intval($this->fun_id),
                        'ppto_inicial'       => $monto_final
                    );
                }
                
                // Si acumula más de 30 errores, paramos el bucle para no saturar la respuesta
                if (count($errores) > 30) break; 
            } // Fin del bucle general FOR por fila

            // Liberamos la memoria RAM del archivo de Excel inmediatamente
            $phpExcel->disconnectWorksheets();
            unset($phpExcel, $hoja);

            if (ob_get_level() > 0) ob_clean(); 
            header('Content-Type: application/json; charset=utf-8');

            // --- 3. PROCESAMIENTO ATÓMICO POR LOTES (COMPATIBLE CI 1.5) ---
            if (empty($errores) && !empty($data_insertar)) {
                $this->db->trans_start(); 
                
                // Eliminamos los registros previos de la gestión activa
                $this->db->where('g_id', intval($this->gestion));
                $this->db->delete('ptto_partidas_sigep');

                // 🎯 CONSTRUCCIÓN DE MULTI-INSERT MANUAL: Agrupamos de 500 en 500 filas
                $lotes = array_chunk($data_insertar, 500);
                
                foreach ($lotes as $lote) {
                    $valores_sql = array();
                    
                    foreach ($lote as $fila) {
                        $val_da       = $this->db->escape($fila['da']);
                        $val_ue       = $this->db->escape($fila['ue']);
                        $val_prog     = $this->db->escape($fila['aper_programa']);
                        $val_proy     = $this->db->escape($fila['aper_proyecto']);
                        $val_act      = $this->db->escape($fila['aper_actividad']);
                        $val_partida  = $this->db->escape($fila['partida']);

                        $valores_sql[] = "({$fila['aper_id']}, {$val_da}, {$val_ue}, {$val_prog}, {$val_proy}, {$val_act}, {$fila['par_id']}, {$val_partida}, {$fila['importe']}, {$fila['g_id']}, {$fila['estado']}, {$fila['fun_id']}, {$fila['ppto_inicial']})";
                    }

                    $sql_consolidado = "INSERT INTO ptto_partidas_sigep 
                        (aper_id, da, ue, aper_programa, aper_proyecto, aper_actividad, par_id, partida, importe, g_id, estado, fun_id, ppto_inicial) 
                        VALUES " . implode(',', $valores_sql);
                    
                    // Ejecutamos la consulta pura agrupada
                    $this->db->query($sql_consolidado);
                    
                    // Limpiamos la RAM acumulada por consultas de CodeIgniter 1.5
                    $this->db->queries = array();
                    $this->db->query_times = array();
                }

                $this->db->trans_complete();

                if ($this->db->trans_status() === FALSE) {
                    echo json_encode(array('status' => 'error', 'errors' => array('Error al insertar en la base de datos (Transacción fallida en Postgres).')));
                } else {
                    // 🎯 🌟 REPARADO: Cambiado de 'error' a 'success' con el mensaje de éxito real
                    echo json_encode(array(
                        'status' => 'success', 
                        'msj'    => 'Importación masiva finalizada con éxito.',
                        'conteo' => count($data_insertar)
                    ));
                }
            } 
            else {
                echo json_encode(array(
                    'status' => 'error', 
                    'errors' => !empty($errores) ? $errores : array('El archivo no contiene filas válidas o los códigos no coinciden con los catálogos de la base de datos.')
                ));
            }
            exit; 

        } catch (Exception $e) {
            if (ob_get_level() > 0) ob_clean();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array('status' => 'error', 'errors' => array('Excepción crítica de PHPExcel: ' . $e->getMessage())));
            exit;
        }
    }



















    /*------------ Modificar Partidas -----------*/
/*    public function edit_partidas($proy_id){ 
      $data['menu']=$this->menu(9);
      $data['resp']=$this->session->userdata('funcionario');
      $data['proyecto'] = $this->model_proyecto->get_id_proyecto($proy_id); //// DATOS DEL PROYECTO
      if(count($data['proyecto'])!=0){
        $data['partidas']=$this->list_partidas($proy_id);
      }
      else{
        redirect('ptto_asig_poa');
      }

      $this->load->view('admin/mantenimiento/ptto_sigep/edit_partidas', $data);
    }*/


    /*------ Lista de Partidas a modificar -------*/
    // function list_partidas($proy_id){
    //   $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); //// DATOS DEL PROYECTO
    //   $partidas=$this->model_ptto_sigep->partidas_proyecto($proyecto[0]['aper_id']);
    //   $total=$this->model_ptto_sigep->suma_ptto_accion($proyecto[0]['aper_id'],1);
    //   $tabla='';
    //   $nro=0;
    //   $tabla.='<tbody>';
    //   foreach($partidas  as $row){
    //     $nro++;
    //     $tabla .='<tr class="modo1">
    //                 <td align=center>'.$nro.'<input type="hidden" name="sp_id[]" value="'.$row['sp_id'].'"></td>
    //                 <td align=center>'.$row['partida'].'</td>
    //                 <td align=left>'.$row['par_nombre'].'</td>
    //                 <td align=center>'.$row['importe'].'</td>
    //                 <td align=center><input type="text" class="form-control" onkeyup="suma_monto();" name="monto[]" id="m'.$nro.'" value="'.$row['importe'].'" title="MODIFICAR MONTO"></td>
    //                 <td align=center><a href="#" data-toggle="modal" data-target="#modal_del_ff" class="btn btn-default del_ff" title="ELIMINAR MONTO PARTIDA"  name="'.$row['sp_id'].'" id="'.$proy_id.'" ><img src="' . base_url() . 'assets/ifinal/eliminar.png" WIDTH="35" HEIGHT="35"/></a></td>
    //               </tr>';
    //   }
    //   $tabla.='</tbody>
    //   <tr>
    //     <td colspan="3">TOTAL </td>
    //     <td align=center>'.$total[0]['monto'].'</td>
    //     <td align=center><input type="text" class="form-control" name="total" value="'.$total[0]['monto'].'" disabled="true"></td>
    //     <td align=center></td>
    //   </tr>';


    //   return $tabla;
    // }


    /*-------- ELIMINAR MONTOS PARTIDA --------*/
    // function delete_partida(){
    //   if ($this->input->is_ajax_request() && $this->input->post()) {
    //       $post = $this->input->post();
    //       $sp_id = $this->security->xss_clean($post['sp_id']);
    //       $proy_id = $this->security->xss_clean($post['proy_id']);

    //       /*------------ ELIMINA ACTIVIDAD PROGRAMADO -----------*/
    //         $this->db->where('sp_id', $sp_id);
    //         $this->db->delete('ptto_partidas_sigep');
          
    //       $sp=$this->model_ptto_sigep->get_sp_id($sp_id);

    //       if(count($sp)==0){
    //         $result = array(
    //           'respuesta' => 'correcto'
    //         );
    //       }
    //       else{
    //         $result = array(
    //         'respuesta' => 'error'
    //        );
    //       }

    //     echo json_encode($result);

    //   } else {
    //       echo 'DATOS ERRONEOS';
    //   }
    // }


    /*------------------ UPDATE PARTIDAS (MANTENIMIENTO)-------------------*/
    // public function valida_update_partidas(){
    //   if ($this->input->post()) {
    //       $post = $this->input->post();
    //       $proy_id = $this->security->xss_clean($post['proy_id']);
    //       $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); 

    //       $nro=0;
    //       if (!empty($_POST["sp_id"]) && is_array($_POST["sp_id"]) ) {
    //       foreach ( array_keys($_POST["sp_id"]) as $como){
    //         //echo "SP ID : ".$_POST["sp_id"][$como]." -> MONTO : ".$_POST["monto"][$como]."<br>";
    //         $update_sigep= array(
    //           'importe' => $_POST["monto"][$como],
    //           'estado' => 2,
    //           'fun_id' => $this->fun_id
    //         );
    //         $this->db->where('sp_id', $_POST["sp_id"][$como]);
    //         $this->db->update('ptto_partidas_sigep', $this->security->xss_clean($update_sigep));          
    //       }

    //       $this->session->set_flashdata('success','SE ACTUALIZARON CORRECTAMENTE LOS MONTOS ASIGNADOS');
    //       redirect(site_url("").'/mnt/edit_ptto_asig/'.$proy_id);
    //     }
    //     else{
    //       $this->session->set_flashdata('danger','ERROR AL ACTUALIZAR MONTOS');
    //       redirect(site_url("").'/mnt/edit_ptto_asig/'.$proy_id);
    //     }
    //   }
    //   else{
    //     echo "<font color=red><b>Error al Eliminar Operaciones</b></font>";
    //   }
    // }


    /*--------------- Partidas ------------------*/
    // function partidas($aper_id,$tp){
    //     $tabla ='';
    //     if($tp==1){
    //         $tb='class="table table-bordered"';
    //     }
    //     else{
    //         $tb='border="0" cellpadding="0" cellspacing="0" class="tabla" style="width:100%;"';
    //     }
    //     $aper=$this->model_ptto_sigep->partidas_proyecto($aper_id);
    //     if(count($aper)!=0){
    //         $tabla .=' <table '.$tb.'>
    //                     <thead>
    //                         <tr class="modo1">
    //                           <th bgcolor="#1c7368">NRO.</th>
    //                           <th bgcolor="#1c7368">PARTIDA</th>
    //                           <th bgcolor="#1c7368">DETALLE PARTIDA</th>
    //                           <th bgcolor="#1c7368">IMPORTE</th>
    //                         </tr>
    //                         </thead>
    //                         <tbody>';
    //         $nro=0;
    //         $monto=0;
    //         foreach($aper  as $row){
    //             $nro++;
    //             $tabla .=' <tr class="modo1">
    //                           <td align=center>'.$nro.'</td>
    //                           <td align=center>'.$row['partida'].'</td>
    //                           <td align=left>'.$row['par_nombre'].'</td>
    //                           <td align=center>'.number_format($row['importe'], 2, ',', '.').'</td>
    //                         </tr>';
    //             $monto=$monto+$row['importe'];
    //         }
    //         $tabla .=' <tr class="modo1">
    //                       <td colspan=3>TOTAL</td>
    //                       <td align=center>'.number_format($monto, 2, ',', '.').'</td>
    //                     </tr>';
    //         $tabla .='</tbody>
    //                 </table>';  
    //     }
        
    //     return $tabla;
    // }
    /*----------------------------------------------------------------------*/
    /*------------------------- Reporte Partidas ----------------------*/
/*    public function rep_partida($aper_id){
        $html = $this->partidas_ptto($aper_id);

        $dompdf = new DOMPDF();
        $dompdf->load_html($html);
        $dompdf->set_paper('letter', 'portrait');
        ini_set('memory_limit','700M');
        ini_set('max_execution_time', 9000000000);
        $dompdf->render();
        $dompdf->stream("REPORTE_PARTIDAS.pdf", array("Attachment" => false));
    }*/


    // function partidas_ptto($aper_id){
    //     $gestion = $this->session->userdata('gestion');
    //     $apertura = $this->model_ptto_sigep->apertura_id($aper_id); //// Datos de la apertura
    //     $html = '
    //     <html>
    //       <head>' . $this->estilo_vertical() . '
    //        <style>
    //          @page { margin: 130px 20px; }
    //          #header { position: fixed; left: 0px; top: -110px; right: 0px; height: 20px; background-color: #fff; text-align: center; }
    //          #footer { position: fixed; left: 0px; bottom: -195px; right: 0px; height: 110px;}
    //          #footer .page:after { content: counter(page, upper-roman); }
    //        </style>
    //       <body>
    //        <div id="header">
    //             <div class="verde"></div>
    //             <div class="blanco"></div>
    //             <table width="100%">
    //                 <tr>
    //                     <td width=20%; text-align:center;"">
    //                         <center><img src="'.base_url().'assets/ifinal/cns_logo.JPG" alt="" width="70px"></center>
    //                     </td>
    //                     <td width=60%; class="titulo_pdf">
    //                         <FONT FACE="courier new" size="1">
    //                         <b>ENTIDAD : </b>'.$this->session->userdata('entidad').'<br>
    //                         <b>PLAN OPERATIVO ANUAL POA : </b> ' . $gestion . '<br>
    //                         <b>REPORTE : </b> PARTIDAS ASIGNADAS POR ACCIONES OPERATIVAS<br>
    //                         <b>APERTURA PROGRAMATICA : </b>'.$apertura[0]['aper_programa'].''.$apertura[0]['aper_proyecto'].''.$apertura[0]['aper_actividad'].'<br>
    //                         <b>ACCI&Oacute;N OPERATIVA : </b>'.$apertura[0]['aper_descripcion'].'
    //                         </FONT>
    //                     </td>
    //                     <td width=20%; text-align:center;"">
    //                     </td>
    //                 </tr>
    //             </table>
    //        </div>
    //        <div id="footer">
    //         <hr>
    //          <table border="0" cellpadding="0" cellspacing="0" class="tabla">
    //             <tr>
    //                 <td colspan=2><p class="izq">'.$this->session->userdata('sistema_pie').'</p></td>
    //                 <td><p class="page">Pagina </p></td>
    //             </tr>
    //         </table>
    //        </div>
    //        <div id="content">
    //          <p><div>'.$this->partidas($aper_id,2).'</div></p>
    //        </div>
    //      </body>
    //      </html>';
    //     return $html;
    // }


    /*---- SUBIR ARCHIVO SIGEP APROBADO -----*/
    // function importar_archivo_sigep2(){
    //     if ($this->input->post()) {
    //         $post = $this->input->post();
          
    //         $tipo = $_FILES['archivo']['type'];
    //         $tamanio = $_FILES['archivo']['size'];
    //         $archivotmp = $_FILES['archivo']['tmp_name'];

    //         $filename = $_FILES["archivo"]["name"];
    //         $file_basename = substr($filename, 0, strripos($filename, '.'));
    //         $file_ext = substr($filename, strripos($filename, '.'));
    //         $allowed_file_types = array('.csv');

    //         if (in_array($file_ext, $allowed_file_types) && ($tamanio < 90000000)) {
                 
    //           /*--------------------------------------------------------------*/
    //             $i=0;
    //             $nro=0;$nroo=0;
    //             $lineas = file($archivotmp);
    //             foreach ($lineas as $linea_num => $linea){ 
    //                 if($i != 0){ 

    //                     $datos = explode(";",$linea);
    //                     //echo count($datos)."<br>";
    //                     if(count($datos)==7){

    //                         $da=$datos[0]; /// Da
    //                         $ue=$datos[1]; /// Ue
    //                         $prog=$datos[2]; /// Aper Programa
    //                         $proy=trim($datos[3]);
    //                         $act=trim($datos[4]);  /// Aper Actividad
    //                         $cod_part=trim($datos[5]); /// Partida
    //                         $importe=floatval(trim($datos[6])); /// Monto

    //                       //  echo $this->gestion."<br>";
    //                         //echo $prog.'- ('.strlen($prog).') -> '.$proy.' ('.strlen($proy).') -> '.$act.' ('.strlen(trim($act)).') ----'.$importe.'-- CODIGO PARTIDA '.is_numeric($cod_part).'<br>';
    //                         if(strlen(trim($act))==3 & $importe!=0 & is_numeric($cod_part)){
    //                           //  echo "INGRESA : ".$prog.'-'.$proy.'-'.$act.'..'.$importe."<br>";
    //                             $nroo++;
    //                          //   echo "string<br>";
    //                             $aper=$this->model_ptto_sigep->get_apertura($da,$ue,$prog,$proy,$act);
    //                             if(count($aper)!=0){
    //                                 $partida = $this->model_insumo ->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                                 $par_id=0;
    //                                 if(count($partida)!=0){
    //                                     $par_id=$partida[0]['par_id'];
    //                                 }

    //                                 $ptto=$this->model_ptto_sigep->get_ptto_sigep($da,$ue,$prog,$proy,$act,$cod_part);
    //                                 if(count($ptto)!=0){
    //                                   echo "UPDATES : ".$prog.'-'.$proy.'-'.$act.' cod '.$cod_part.'-- PAR ID : '.$par_id.' ->'.$importe."<br>";
    //                                    /*------------------- Update Datos ----------------------*/
    //                                     /*$query=$this->db->query('set datestyle to DMY');
    //                                     $update_ptto = array(
    //                                       'aper_id' => $aper[0]['aper_id'],
    //                                         'importe' => $importe,
    //                                         'fun_id' => $this->session->userdata("fun_id")
    //                                     );

    //                                     $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                                     $this->db->update('ptto_partidas_sigep', $update_ptto);*/
    //                                    /*-------------------------------------------------------*/
    //                                 }
    //                                 else{
    //                                   echo "INSERTS : ".$nroo." -> ".$da.' '.$ue.'  '.$prog.'-'.$proy.'-'.$act.' cod '.$cod_part.'-- PAR ID : '.$par_id.' ->'.$importe."<br>";
    //                                    /*-------------------- Guardando Datos ------------------*/
    //                                     /*$query=$this->db->query('set datestyle to DMY');
    //                                     $data_to_store = array( 
    //                                         'aper_id' => $aper[0]['aper_id'],
    //                                         'da' => $da,
    //                                         'ue' => $ue,
    //                                         'aper_programa' => $prog,
    //                                         'aper_proyecto' => $proy,
    //                                         'aper_actividad' => $act,
    //                                         'par_id' => $par_id,
    //                                         'partida' => $cod_part,
    //                                         'importe' => $importe,
    //                                         'g_id' => $this->gestion,
    //                                         'fun_id' => $this->session->userdata("fun_id"),
    //                                     );
    //                                     $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //                                     $sp_id=$this->db->insert_id();*/
    //                                     /*-------------------------------------------------------*/ 
    //                                 }
    //                             $nro++;
    //                             }
    //                             else{
    //                               echo "NO INGRESA : ".$da.'-'.$ue.'- > '.$prog.'-'.$proy.'-'.$act.'..'.$importe."<br>";
    //                                  /* $partida = $this->minsumos->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                                   $par_id=0;
    //                                   if(count($partida)!=0){
    //                                       $par_id=$partida[0]['par_id'];
    //                                   }*/
    //                                  /*-------------------- Guardando Datos ------------------*/
    //                                  /* $query=$this->db->query('set datestyle to DMY');
    //                                   $data_to_store = array( 
    //                                       'aper_id' => 0,
    //                                       'da' => $da,
    //                                       'ue' => $ue,
    //                                       'aper_programa' => $prog,
    //                                       'aper_proyecto' => $proy,
    //                                       'aper_actividad' => $act,
    //                                       'par_id' => $par_id,
    //                                       'partida' => $cod_part,
    //                                       'importe' => $importe,
    //                                       'g_id' => $this->gestion,
    //                                       'fun_id' => $this->session->userdata("fun_id"),
    //                                   );
    //                                   $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //                                   $sp_id=$this->db->insert_id();*/
    //                                   /*-------------------------------------------------------*/ 
    //                             }
    //                         }
    //                         else{
    //                           echo "WILMER ".$da.' '.$ue.'- > '.$prog.'-'.$proy.'-'.$act.'-'.$cod_part.' - '.$importe.'<br>';
    //                         }
    //                         //elseif(strlen($act)==3 & $importe==0){
    //                         //$ptto=$this->model_ptto_sigep->get_ptto_sigep($da,$ue,$prog,$proy,$act,$cod_part);
    //                           //if(count($ptto)!=0){
    //                             //echo "UPDATES 0->VALOR : ".$prog.'-'.$proy.'-'.$act.' cod '.$cod_part.'-- PAR ID : '.$par_id.' ->'.$importe."<br>";
    //                              /*------------------- Update Datos ----------------------*/
    //                               /*$query=$this->db->query('set datestyle to DMY');
    //                               $update_ptto = array(
    //                                 'aper_id' => $aper[0]['aper_id'],
    //                                   'importe' => $importe,
    //                                   'fun_id' => $this->session->userdata("fun_id")
    //                               );

    //                               $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                               $this->db->update('ptto_partidas_sigep', $update_ptto);*/
    //                              /*-------------------------------------------------------*/
    //                           //}
    //                         //}
    //                     }
    //                 }

    //                 $i++;
    //             }

    //           /*--------------------------------------------------------------*/
    //         } 
    //         elseif (empty($file_basename)) {
    //             echo "<script>alert('SELECCIONE ARCHIVO .CSV')</script>";
    //         } 
    //         elseif ($filesize > 100000000) {
    //             //redirect('');
    //         } 
    //         else {
    //             $mensaje = "Sólo estos tipos de archivo se permiten para la carga: " . implode(', ', $allowed_file_types);
    //             echo '<script>alert("' . $mensaje . '")</script>';
    //         }

    //     } else {
    //         show_404();
    //     }
    // }


    /*-------- SUBIR ARCHIVO SIGEP -------*/
    // function importar_archivo_sigep(){
    //   if ($this->input->post()) {
    //       $post = $this->input->post();
    //       $tp = $this->security->xss_clean($post['tp_id']);
    //       $tp_id = $this->security->xss_clean($post['tp_id']);

    //       $tipo = $_FILES['archivo']['type'];
    //       $tamanio = $_FILES['archivo']['size'];
    //       $archivotmp = $_FILES['archivo']['tmp_name'];

    //       $filename = $_FILES["archivo"]["name"];
    //       $file_basename = substr($filename, 0, strripos($filename, '.'));
    //       $file_ext = substr($filename, strripos($filename, '.'));
    //       $allowed_file_types = array('.csv');
    //       if (in_array($file_ext, $allowed_file_types) && ($tamanio < 90000000)) {
               
    //         /*--------------------------------------------------------------*/
    //         if($this->verif_ppto==0){
    //           $lineas=$this->subir_archivo($archivotmp,$tp_id); /// Techo Inicial
    //         }
    //         else{
    //           //$lineas=$this->subir_archivo($archivotmp,$tp_id); /// Techo Inicial
    //           $lineas=$this->subir_archivo_aprobado($archivotmp,$tp_id); /// Techo Aprobado
    //         }
            
    //         $this->session->set_flashdata('success','SE SUBIO CORRECTAMENTE EL ARCHIVO ('.$lineas.')');
    //         redirect(site_url("").'/ptto_asig_poa');
    //         /*--------------------------------------------------------------*/
    //       } 
    //       elseif (empty($file_basename)) {
    //         echo "<script>alert('SELECCIONE ARCHIVO .CSV')</script>";
    //       } 
    //       elseif ($filesize > 100000000) {
    //         //redirect('');
    //       } 
    //       else {
    //         $mensaje = "Sólo estos tipos de archivo se permiten para la carga: " . implode(', ', $allowed_file_types);
    //         echo '<script>alert("' . $mensaje . '")</script>';
    //       }

    //   } else {
    //       show_404();
    //   }
    // }

    /*-------- Subir Archivo Presupuesto Inicial -----------*/
    // public function subir_archivo($archivotmp,$tp_id){  
    //     $i=0;
    //     $nro=0;
    //     $lineas = file($archivotmp);

    //     if($tp_id==1){ /// PROYECTOS DE INVERSION
    //       foreach ($lineas as $linea_num => $linea){ 
    //         if($i != 0){ 
    //           $datos = explode(";",$linea);
    //             if(count($datos)==4){
    //               $aper_id = intval(trim($datos[0])); //// aper_id
    //               $cod_sisin = utf8_encode(trim($datos[1])); //// Sisin
    //               $cod_part = intval(trim($datos[2])); //// partida
    //               $importe = floatval(trim($datos[3])); //// monto
    //               $proy=$this->model_proyecto->get_aper_programa($aper_id); /// Datos del Proyecto
    //               if(count($proy)!=0){ /// Datos ya almacenados
    //                   $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                   $par_id=0;
    //                     if(count($partida)!=0){
    //                       $par_id=$partida[0]['par_id'];
    //                     }

    //                   $ptto=$this->model_ptto_sigep->get_ptto_sigep_pi($aper_id,$cod_part); /// VERIFICANDO SI ESTA REGISTRADO YA PRESUPUESTO EN LA BD
    //                   if(count($ptto)!=0){
    //                     /*-------- Update Datos ---------*/
    //                     $query=$this->db->query('set datestyle to DMY');
    //                     $update_ptto = array(
    //                       'da' => $proy[0]['da'],
    //                       'ue' => $proy[0]['ue'],
    //                       'aper_programa' => $proy[0]['prog'],
    //                       'aper_proyecto' => $proy[0]['proy'],
    //                       'aper_id' => $proy[0]['aper_id'],
    //                       'importe' => $importe,
    //                       'ppto_inicial' => $importe, /// PPTO INICIAL
    //                       'fun_id' => $this->fun_id
    //                     );

    //                     $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                     $this->db->update('ptto_partidas_sigep', $update_ptto);
    //                    /*---------------------------------*/
    //                   }
    //                   else{
    //                     // adicionando
    //                     /*------ Guardando Datos -----*/
    //                     $query=$this->db->query('set datestyle to DMY');
    //                     $data_to_store = array( 
    //                       'aper_id' => $proy[0]['aper_id'],
    //                       'da' => $proy[0]['da'],
    //                       'ue' => $proy[0]['ue'],
    //                       'aper_programa' => $proy[0]['prog'],
    //                       'aper_proyecto' => $proy[0]['proy'],
    //                       'aper_actividad' => '000',
    //                       'par_id' => $par_id,
    //                       'partida' => $cod_part,
    //                       'importe' => $importe,
    //                       'ppto_inicial' => $importe, /// PPTO INICIAL
    //                       'g_id' => $this->gestion,
    //                       'fun_id' => $this->fun_id,
    //                     );
    //                     $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //                     $sp_id=$this->db->insert_id();
    //                     /*------------------------------*/ 
    //                   }

    //                 $nro++;
    //               }
                  
    //             }
    //           }

    //           $i++;
    //         }
    //     }
    //     else{  /// Gasto Corriente
    //       foreach ($lineas as $linea_num => $linea){ 
    //         if($i != 0){ 
    //             $datos = explode(";",$linea);
    //             if(count($datos)==7){
    //                 $da=$datos[0]; /// Da
    //                 $ue=$datos[1]; /// Ue
    //                 $prog=$datos[2]; /// Aper Programa
    //                 $proy=trim($datos[3]); /// Aper proyecto
    //                 $act=trim($datos[4]);  /// Aper Actividad
    //                 $cod_part=trim($datos[5]); /// Partida
    //                 $importe=floatval($datos[6]); /// Monto
                  
    //                 if(strlen($act)==3 & $importe!=0 & is_numeric($cod_part)){ /// gestion 2026
    //                     $aper=$this->model_ptto_sigep->get_apertura($da,$ue,$prog,$proy,$act); /// DATOS DEL PROGRAMA
    //                     $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                     $par_id=0;
    //                     if(count($partida)!=0){
    //                         $par_id=$partida[0]['par_id'];
    //                     }

    //                     if(count($aper)!=0){
    //                         $ptto=$this->model_ptto_sigep->get_ptto_sigep($da,$ue,$prog,$proy,$act,$cod_part); /// verif si ya estaba registrado el ppto
    //                         if(count($ptto)!=0){
    //                            /*----------- Update Datos ------------*/
    //                            //echo 'UPDATE: '.$aper[0]['aper_id'].'---'.$importe.'<br>';
    //                             $query=$this->db->query('set datestyle to DMY');
    //                             $update_ptto = array(
    //                                 'aper_id' => $aper[0]['aper_id'],
    //                                 'importe' => $importe,
    //                                 'ppto_inicial' => $importe, /// PPTO INICIAL
    //                                 'fun_id' => $this->session->userdata("fun_id")
    //                             );

    //                             $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                             $this->db->update('ptto_partidas_sigep', $update_ptto);
    //                            /*------------------------------------*/
    //                         }
    //                         else{
    //                          // echo 'INSERT: '.$aper[0]['aper_id'].'---'.$importe.'<br>'; $suma=$suma+$importe;
    //                            /*---------- Guardando Datos ---------*/
    //                             $query=$this->db->query('set datestyle to DMY');
    //                             $data_to_store = array( 
    //                                 'aper_id' => $aper[0]['aper_id'],
    //                                 'da' => $aper[0]['da'],
    //                                 'ue' => $aper[0]['ue'],
    //                                 'aper_programa' => $aper[0]['prog'],
    //                                 'aper_proyecto' => $aper[0]['proy'],
    //                                 'aper_actividad' => $aper[0]['act'],
    //                                 'par_id' => $par_id,
    //                                 'partida' => $cod_part,
    //                                 'importe' => $importe,
    //                                 'ppto_inicial' => $importe, /// PPTO INICIAL
    //                                 'g_id' => $this->gestion,
    //                                 'fun_id' => $this->session->userdata("fun_id"),
    //                             );
    //                             $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //                             $sp_id=$this->db->insert_id();
    //                             /*----------------------------------*/ 
    //                         }
    //                     $nro++;
    //                     }
    //                     else{ //// nose identifico el id de la apertura
    //                       //echo 'NO ENCONTRO:  0 ---'.$importe.'<br>';
    //                          /*---------- Guardando Datos ----------*/
    //                           $query=$this->db->query('set datestyle to DMY');
    //                           $data_to_store = array( 
    //                               'aper_id' => 0,
    //                               'da' => $da,
    //                               'ue' => $ue,
    //                               'aper_programa' => $prog,
    //                               'aper_proyecto' => $proy,
    //                               'aper_actividad' => $act,
    //                               'par_id' => $par_id,
    //                               'partida' => $cod_part,
    //                               'importe' => $importe,
    //                               'g_id' => $this->gestion,
    //                               'fun_id' => $this->session->userdata("fun_id"),
    //                           );
    //                           $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //                           $sp_id=$this->db->insert_id();
    //                           /*-------------------------------------*/ 
    //                     }
    //                 }
                   


    //             }

    //         }

    //         $i++;
    //       }
         
    //     }

    //     return $nro;
    //  }


    /*--------- Subir Archivo SIgep Aprobado ----------*/
    // public function subir_archivo_aprobado($archivotmp,$tp_id){  
    //   $i=0;
    //   $nro=0;
    //   $lineas = file($archivotmp);
      
    //   foreach ($lineas as $linea_num => $linea){ 
    //       if($i != 0){ 
    //           $datos = explode(";",$linea);
    //           if(count($datos)==7){
    //               $da=trim($datos[0]); /// Da
    //               $ue=trim($datos[1]); /// Ue
    //               $prog=trim($datos[2]); /// Programa
    //               $proy=trim($datos[3]); /// proyecto
    //               $act=trim($datos[4]);  /// Actividad
    //               $cod_part=trim($datos[5]); /// Partida
    //               if(strlen($cod_part)==3){
    //                 $cod_part=$cod_part.'00';
    //               }
    //               $importe=(float)$datos[6]; /// Monto

    //               if(strlen($da)==2 & strlen($ue)==2 & strlen($act)==3 & $importe!=0 & is_numeric($cod_part)){
    //                   $aper=$this->model_ptto_sigep->get_apertura($da,$ue,$prog,$proy,$act);
    //                   if(count($aper)!=0){
    //                       $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                       $par_id=0;
    //                       if(count($partida)!=0){
    //                         $par_id=$partida[0]['par_id'];
    //                       }

    //                       $ptto=$this->model_ptto_sigep->get_ptto_sigep_aprobado($da,$ue,$prog,$proy,$act,$cod_part);
    //                       if(count($ptto)!=0){
    //                          /*------- Update Datos -------*/
    //                           $query=$this->db->query('set datestyle to DMY');
    //                           $update_ptto = array(
    //                             'aper_id' => $aper[0]['aper_id'],
    //                             'da' => $da,
    //                             'ue' => $ue,
    //                             'importe' => $importe,
    //                             'fun_id' => $this->fun_id
    //                           );

    //                           $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                           $this->db->update('ptto_partidas_sigep_aprobado', $update_ptto);
    //                          /*-----------------------------*/
    //                          $nro++;
    //                       }
    //                       else{
    //                          /*------- Guardando Datos --------*/
    //                           $query=$this->db->query('set datestyle to DMY');
    //                           $data_to_store = array( 
    //                               'aper_id' => $aper[0]['aper_id'],
    //                               'da' => $da,
    //                               'ue' => $ue,
    //                               'aper_programa' => $prog,
    //                               'aper_proyecto' => $proy,
    //                               'aper_actividad' => $act,
    //                               'par_id' => $par_id,
    //                               'partida' => $cod_part,
    //                               'importe' => $importe,
    //                               'g_id' => $this->gestion,
    //                               'fun_id' => $this->fun_id,
    //                           );
    //                           $this->db->insert('ptto_partidas_sigep_aprobado', $data_to_store);
    //                           $sp_id=$this->db->insert_id();
    //                           /*-------------------------------------------------------*/
    //                       }
    //                   $nro++;
    //                   }
    //                   /*else{
    //                         $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                         $par_id=0;
    //                         if(count($partida)!=0){
    //                             $par_id=$partida[0]['par_id'];
    //                         }
                         
    //                         $query=$this->db->query('set datestyle to DMY');
    //                         $data_to_store = array( 
    //                             'aper_id' => 0,
    //                             'aper_programa' => $prog,
    //                             'aper_proyecto' => $proy,
    //                             'aper_actividad' => $act,
    //                             'par_id' => $par_id,
    //                             'partida' => $cod_part,
    //                             'importe' => $importe,
    //                             'g_id' => $this->gestion,
    //                             'fun_id' => $this->fun_id,
    //                         );
    //                         $this->db->insert('ptto_partidas_sigep_aprobado', $data_to_store);
    //                         $sp_id=$this->db->insert_id();
                         
    //                         $nro++;
    //                     }*/
    //               }
    //             }
    //         }
    //         $i++;
    //     }
    //     return $nro;
    //  }


    // public function subir_archivo_aprobado2($archivotmp,$tp_id){  
    //   $i=0;
    //   $nro=0;
    //   $lineas = file($archivotmp);
      
    //   foreach ($lineas as $linea_num => $linea){ 
    //       if($i != 0){ 
    //           $datos = explode(";",$linea);
    //           if(count($datos)==7){
    //               $da=trim($datos[0]); /// Da
    //               $ue=trim($datos[1]); /// Ue
    //               $prog=trim($datos[2]); /// Programa
    //               $proy=trim($datos[3]); /// proyecto
    //               $act=trim($datos[4]);  /// Actividad
    //               $cod_part=trim($datos[5]); /// Partida
    //               if(strlen($cod_part)==3){
    //                 $cod_part=$cod_part.'00';
    //               }
    //               $importe=(float)$datos[6]; /// Monto

    //               if(strlen($da)==2 & strlen($ue)==2 & strlen($act)==3 & $importe!=0 & is_numeric($cod_part)){
    //                   $aper=$this->model_ptto_sigep->get_apertura($da,$ue,$prog,$proy,$act);
    //                   if(count($aper)!=0){
    //                       $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                       $par_id=0;
    //                       if(count($partida)!=0){
    //                         $par_id=$partida[0]['par_id'];
    //                       }

    //                       $ptto=$this->model_ptto_sigep->get_ptto_sigep_aprobado($da,$ue,$prog,$proy,$act,$cod_part);
    //                       if(count($ptto)!=0){
    //                          /*------- Update Datos -------*/
    //                           $query=$this->db->query('set datestyle to DMY');
    //                           $update_ptto = array(
    //                             'aper_id' => $aper[0]['aper_id'],
    //                             'da' => $da,
    //                             'ue' => $ue,
    //                             'importe' => $importe,
    //                             'fun_id' => $this->fun_id
    //                           );

    //                           $this->db->where('sp_id', $ptto[0]['sp_id']);
    //                           $this->db->update('ptto_partidas_sigep_aprobado', $update_ptto);
    //                          /*-----------------------------*/
    //                          $nro++;
    //                       }
    //                       else{
    //                          /*------- Guardando Datos --------*/
    //                           $query=$this->db->query('set datestyle to DMY');
    //                           $data_to_store = array( 
    //                               'aper_id' => $aper[0]['aper_id'],
    //                               'da' => $da,
    //                               'ue' => $ue,
    //                               'aper_programa' => $prog,
    //                               'aper_proyecto' => $proy,
    //                               'aper_actividad' => $act,
    //                               'par_id' => $par_id,
    //                               'partida' => $cod_part,
    //                               'importe' => $importe,
    //                               'g_id' => $this->gestion,
    //                               'fun_id' => $this->fun_id,
    //                           );
    //                           $this->db->insert('ptto_partidas_sigep_aprobado', $data_to_store);
    //                           $sp_id=$this->db->insert_id();
    //                           /*-------------------------------------------------------*/
    //                       }
    //                   $nro++;
    //                   }
    //                   else{
    //                         $partida = $this->model_insumo->get_partida_codigo($cod_part); //// DATOS DE LA PARTIDA
    //                         $par_id=0;
    //                         if(count($partida)!=0){
    //                             $par_id=$partida[0]['par_id'];
    //                         }
    //                        /*-------------------- Guardando Datos ------------------*/
    //                         $query=$this->db->query('set datestyle to DMY');
    //                         $data_to_store = array( 
    //                             'aper_id' => 0,
    //                             'aper_programa' => $prog,
    //                             'aper_proyecto' => $proy,
    //                             'aper_actividad' => $act,
    //                             'par_id' => $par_id,
    //                             'partida' => $cod_part,
    //                             'importe' => $importe,
    //                             'g_id' => $this->gestion,
    //                             'fun_id' => $this->fun_id,
    //                         );
    //                         $this->db->insert('ptto_partidas_sigep_aprobado', $data_to_store);
    //                         $sp_id=$this->db->insert_id();
    //                         /*-------------------------------------------------------*/ 
    //                         $nro++;
    //                     }
    //               }
    //             }
    //         }
    //         $i++;
    //     }
    //     return $nro;
    //  }


    /*---- LISTA DE OPERACIONES PARA LA REASIGNACION DE PRESUPUESTO FINAL ---*/
    // public function list_ptto_poa_final($tp_id){
    //   $lista_aper_padres = $this->model_proyecto->list_prog();//lista de aperturas padres 
    //   $tabla ='';
    //   foreach($lista_aper_padres  as $rowa){
    //     $proyectos=$this->model_ptto_sigep->acciones_operativas($rowa['aper_programa'],$tp_id);
    //     if(count($proyectos)!=0){
    //       $tabla .='<tr bgcolor="#99DDF0" height="30">';
    //         $tabla .='<td></td>';
    //         if($this->tp_adm==1){
    //           $tabla .='<td></td>';
    //         }
    //         $tabla .='<td><center>'.$rowa['aper_programa'].''.$rowa['aper_proyecto'].''.$rowa['aper_actividad'].'</center></td>';
    //         $tabla .='<td>'.$rowa['aper_descripcion'].'</td>';
    //         $tabla .='<td>'.$rowa['aper_sisin'].'</td>';
    //         $tabla .='<td></td>';
    //         $tabla .='<td></td>';
    //         $tabla .='<td></td>';
    //         $tabla .='<td></td>';
    //         $tabla .='<td></td>';
    //       $tabla .='</tr>';
    //       $nro=0;
    //       foreach($proyectos  as $row){
    //         $nro++;
    //       //  $fase = $this->model_faseetapa->get_id_fase($row['proy_id']);
    //         $aper=$this->model_ptto_sigep->partidas_proyecto($row['aper_id']);
    //         $tabla .= '<tr height="50">';
    //           $tabla .= '<td align=center><center><img id="loadd'.$row['proy_id'].'" style="display: none" src="'.base_url().'/assets/img/loading.gif" width="25" height="25" title="ESPERE UN MOMENTO, LA PAGINA SE ESTA CARGANDO.."></center></td>';
    //           if($this->tp_adm==1){
    //             if(count($aper)!=0){
    //               $tabla .='<td><center><a href="'.site_url("").'/mnt/ver_ptto_asig_final/'.$row['proy_id'].'" id="myBtnn'.$row['proy_id'].'" title="VER PRESUPUESTO ASIGNADO INICIAL - PROGRAMADO - APROBADO" iclass="btn btn-default"><img src="'.base_url().'assets/ifinal/faseetapa.png" WIDTH="34" HEIGHT="34"/></a></center></td>';
    //             }
    //             else{
    //               $tabla .='<td></td>';
    //             }
    //           }
    //           $tabla .= '<td align=center>'.$row['aper_programa'].''.$row['aper_proyecto'].''.$row['aper_actividad'].'</td>';
    //           $tabla .= '<td>'.$row['proy_id'].' | '.$row['proy_nombre'].'</td>';
    //           $tabla .= '<td>'.$row['tp_tipo'].'</td>';
    //           $tabla .= '<td>'.$row['proy_sisin'].'</td>';
    //           $tabla .= '<td>'.$row['fun_nombre'].' '.$row['fun_paterno'].' '.$row['fun_materno'].'</td>';
    //             $tabla .='<td></td>';
    //             $tabla .='<td></td>';
    //             $tabla .='<td></td>';
    //         $tabla .= '</tr>';
            

    //       }
    //     }
    //   }
    //   return $tabla;
    // }

    /*------------ Verificar Comparativo Partidas -----------*/
/*    public function ver_comparativo_partidas($proy_id){ 
      $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);
      if(count($data['proyecto'])!=0){
        $data['menu']=$this->menu(9);
        $data['resp']=$this->session->userdata('funcionario');
        $data['partidas']= $this->comparativo_partidas_ppto_final($data['proyecto'][0]['dep_id'],$data['proyecto'][0]['aper_id'],1);
        
        $data['boton']='<a href="#" data-toggle="modal" data-target="#modal_nuevo_ff" class="btn btn-primary nuevo_ff" title="NUEVO REGISTRO PARTIDA" style="width:10%;">NUEVO PARTIDA</a><br><br>';
        $data['list_partidas']=$this->model_ptto_sigep->list_partidas_noasig($data['proyecto'][0]['aper_id']); /// Aper id

        $this->load->view('admin/mantenimiento/ptto_sigep/comparativo_partidas', $data);
      }
      else{
        redirect('ptto_asig_poa');
      }
    }*/

    /*------------ ADICIONA PARTIDAS (PROG POA)--------------*/
    // public function valida_add_partida(){
    //   if ($this->input->post()) {
    //     $post = $this->input->post();
    //     $proy_id = $this->security->xss_clean($post['proy_id']);
    //     $proyecto = $this->model_proyecto->get_id_proyecto($proy_id); //// DATOS DEL PROYECTO
    //     $par_id = $this->security->xss_clean($post['par_id']);
    //     $partida=$this->model_partidas->get_partida($par_id);
    //     $importe = $this->security->xss_clean($post['monto']);

    //     /*-------- Insert ppto_adicionado ----------*/
    //       $query=$this->db->query('set datestyle to DMY');
    //       $data_to_store = array( 
    //           'aper_id' => $proyecto[0]['aper_id'],
    //           'da' => $proyecto[0]['da'],
    //           'ue' => $proyecto[0]['ue'],
    //           'aper_programa' => $proyecto[0]['prog'],
    //           'aper_proyecto' => $proyecto[0]['proy'],
    //           'aper_actividad' => $proyecto[0]['act'],
    //           'par_id' => $par_id,
    //           'partida' => $partida[0]['par_codigo'],
    //           'importe' => $importe,
    //           'g_id' => $this->gestion,
    //           'fun_id' => $this->session->userdata("fun_id"),
    //       );
    //       $this->db->insert('ptto_partidas_sigep', $data_to_store);
    //       $sp_id=$this->db->insert_id();
    //     /*------------------------------------------*/

    //       $this->session->set_flashdata('success','SE REGISTRO CORRECTAMENTE');
    //       redirect(site_url("").'/mnt/ver_ptto_asig_final/'.$proy_id);

    //   }
    //   else{
    //     echo "<center><font color=red>Error al Registrar la Nueva Partida</font></center>";
    //   }
    // }


    /*------ Ver Lista de Partidas Comparativos 2020 -------*/
    // public function comparativo_partidas_ppto_final($dep_id,$aper_id,$tp_tab){ 
    //  // echo "DEP : ".$dep_id." aper_id : ".$aper_id."<br>";
    //   $tabla ='';
    //   $partidas_prog=$this->model_ptto_sigep->partidas_accion_region($dep_id,$aper_id,1); // Presupuesto Partidas asignado
    //   $partidas_aprobados=$this->model_ptto_sigep->list_ppto_final_aprobado($aper_id); // Presupuesto Partidas Aprobado
    //   if($tp_tab==1){
    //     $tab='id="table" class="table table-bordered"';
    //   }
    //   else{
    //     $tab='cellpadding="0" cellspacing="0" class="tabla" border=0.2 style="width:100%;" align=center';
    //   }

    //   $tabla .='

    //     <table '.$tab.'>
    //       <thead>
    //         <tr style="font-size: 7px;" align=center>
    //           <th bgcolor="#1c7368" style="width:2%;color:#FFF;height:15px;">NRO. '.$aper_id.' -- '.$dep_id.'</th>
    //           <th bgcolor="#1c7368" style="width:5%;color:#FFF;" title="CODIGO PARTIDA">C&Oacute;DIGO</th>
    //           <th bgcolor="#1c7368" style="width:40%;color:#FFF;" title="DESCRIPCI&Oacute;N PARTIDA">DETALLE PARTIDA</th>
    //           <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO PRESUPUESTO PROGRAMADO">PPTO. ASIGNADO INICIAL</th>
    //           <th bgcolor="#1c7368" style="width:5%;color:#FFF;" title="AJUSTAR"></th>
    //           <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO PRESUPUESTO FINAL APROBADO">PPTO. FINAL APROBADO (SIGEP)</th>
    //           <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO DIFERENCIA (INICIAL - FINAL)">MONTO DIFERENCIA (SIGEP-SIIPLAS)</th>
    //         </tr>
    //       </thead>
    //       <tbody>';
    //       $nro=0;
    //       $monto_poa=0;
    //       $monto_final=0;
    //       $monto_diferencia=0;
    //       foreach($partidas_prog as $row){
    //         $boton='';
    //         $ppto=$this->model_ptto_sigep->get_ptto_aprobado($aper_id,$row['par_id']);
    //         $monto_final_partida=0;
    //         $dif_monto=0;
    //         if(count($ppto)!=0){
    //           $monto_final_partida=$ppto[0]['monto'];
    //         }
    //         $dif_monto=$monto_final_partida-$row['ppto_asignado'];
    //         $color='';$sig='';
    //         if($dif_monto<0){
    //           $color='#f7b1b0';
    //           $boton='
    //           <a href="#" data-toggle="modal" data-target="#modal_update_ff" class="btn btn-danger update_ff" title="AJUSTAR PRESUPUESTO"  name="'.$row['sp_id'].'" id="'.$row['codigo'].'">
    //             AJUSTAR PPTO.
    //           </a>';
    //         }
    //         elseif ($dif_monto>0) {
    //           $sig='+';
    //           $color='#dff0d8';
    //           $boton='
    //           <a href="#" data-toggle="modal" data-target="#modal_update_ff" class="btn btn-success update_ff" title="AJUSTAR PRESUPUESTO"  name="'.$row['sp_id'].'" id="'.$row['codigo'].'">
    //             AJUSTAR PPTO.
    //           </a>';
    //         }

    //         $nro++;
    //         $tabla.='
    //           <tr bgcolor='.$color.'>
    //             <td style="width:2%;height:12px;" align=center>'.$nro.'</td>
    //             <td style="width:5%;" align=center><b>'.$row['codigo'].'</b></td>
    //             <td style="width:30%;">'.$row['nombre'].'</td>
    //             <td style="width:10%;" align=right><input type="text" class="form-control" name="monto'.$row['sp_id'].'" id="monto'.$row['sp_id'].'" value="'.round($row['ppto_asignado'],2).'" title="MODIFICAR MONTO"></td>
    //             <td align=center>'.$boton.' <div id="load'.$row['sp_id'].'" style="display: none"><br><img src="'.base_url().'assets/img/loading.gif" width="25" height="25"/></div></td>
    //             <td style="width:12%;" align=right>'.number_format($monto_final_partida, 2, ',', '.').'</td>
    //             <td style="width:12%;" align=right>'.$sig.''.number_format($dif_monto, 2, ',', '.').'</td>
    //           </tr>';
    //         $monto_poa=$monto_poa+$row['ppto_asignado'];
    //         $monto_final=$monto_final+$monto_final_partida;
    //       }
          
    //       foreach($partidas_aprobados as $row){
    //         //$ppto=$this->model_ptto_sigep->get_partida_accion($aper_id,$row['par_id']); /// programado
    //         $ppto=$this->model_ptto_sigep->get_partida_asignado_sigep($aper_id,$row['par_id']); /// Asignado Anteproyecto

    //         if(count($ppto)==0){
    //           $dif_monto=$row['importe']-0;
    //           $nro++;
    //           $tabla.='
    //             <tr bgcolor="#f7e1b4">
    //               <td style="width:2%;height:12px;" align=center title="'.$row['sp_id'].'">'.$nro.'</td>
    //               <td style="width:5%;" align=center><b>'.$row['partida'].'</b></td>
    //               <td style="width:30%;">'.$row['par_nombre'].'</td>
    //               <td style="width:10%;" align=right>
    //                 <input type="text" class="form-control" name="monto'.$row['sp_id'].'" id="monto'.$row['sp_id'].'" value="'.round($row['importe'],2).'" title="MODIFICAR MONTO">
    //               </td>
    //               <td align=center>
    //                 <a href="#" data-toggle="modal" data-target="#modal_add_ff" class="btn btn-warning add_ff" title="AGREGAR PRESUPUESTO"  name="'.$row['sp_id'].'" id="'.$row['par_codigo'].'">
    //                   AGREGAR PPTO.
    //                 </a>
    //                 <div id="loadd'.$row['sp_id'].'" style="display: none"><br><img src="'.base_url().'assets/img/loading.gif" width="25" height="25"/></div>
    //               </td>
    //               <td style="width:12%;" align=right>'.number_format($row['importe'], 2, ',', '.').'</td>
    //               <td style="width:12%;" align=right>'.number_format($dif_monto, 2, ',', '.').'</td>
    //             </tr>';
    //             $monto_final=$monto_final+$row['importe'];
    //         }
    //       }
    //     $tabla.='
    //         <tr>
    //           <td colspan=3>TOTAL</td>
    //           <td style="height:12px;" align=right>'.number_format($monto_poa, 2, ',', '.').'</td>
    //           <td></td>
    //           <td align=right>'.number_format($monto_final, 2, ',', '.').'</td>
    //           <td align=right>'.number_format(($monto_final-$monto_poa), 2, ',', '.').'</td>
    //         </tr>
    //       </tbody>
    //     </table>';

    //     return $tabla;
    // }


    /*------ ACTUALIZA PRESUPUESTO POR PARTIDA ------*/
    // function update_ppto_aprobado(){
    //   if ($this->input->is_ajax_request() && $this->input->post()) {
    //       $post = $this->input->post();
    //       $sp_id = $this->security->xss_clean($post['sp_id']); // sp id
    //       $monto_final = $this->security->xss_clean($post['ppto']); // monto
        
    //       /*--------- Update ppto Sigep ----------*/
    //       $update_ppto= array(
    //         'importe' => $monto_final,
    //         'estado' => 2,
    //         'fun_id' => $this->fun_id
    //       );
    //       $this->db->where('sp_id', $sp_id);
    //       $this->db->update('ptto_partidas_sigep', $this->security->xss_clean($update_ppto));
    //       /*----------------------------------------*/


    //       $result = array(
    //           'respuesta' => 'correcto'
    //       );

    //     echo json_encode($result);

    //   } else {
    //       echo 'DATOS ERRONEOS';
    //   }
    // }



    /*------ ADICIONA PRESUPUESTO POR PARTIDA ------*/
    // function add_ppto_aprobado(){
    //   if ($this->input->is_ajax_request() && $this->input->post()) {
    //       $post = $this->input->post();
    //       $sp_id = $this->security->xss_clean($post['sp_id']); // sp id
    //       $monto_final = $this->security->xss_clean($post['ppto']); // monto
    //       $ppto_aprobado=$this->model_ptto_sigep->get_ppto_aprobado($sp_id);

    //       if(count($ppto_aprobado)!=0){
          
    //         $data_to_store = array( 
    //           'aper_id' => $ppto_aprobado[0]['aper_id'],
    //           'aper_programa' => $ppto_aprobado[0]['aper_programa'],
    //           'aper_proyecto' => $ppto_aprobado[0]['aper_proyecto'],
    //           'aper_actividad' => $ppto_aprobado[0]['aper_actividad'],
    //           'par_id' => $ppto_aprobado[0]['par_id'],
    //           'partida' => $ppto_aprobado[0]['partida'],
    //           'importe' => $ppto_aprobado[0]['importe'],
    //           'g_id' => $ppto_aprobado[0]['g_id'],
    //           'estado' => 2,
    //           'fun_id' => $this->fun_id,
    //         );
    //         $this->db->insert('ptto_partidas_sigep', $data_to_store);
          
    //         $result = array(
    //           'respuesta' => 'correcto'
    //         );
    //       }
    //       else{
    //         $result = array(
    //           'respuesta' => 'error'
    //         );
    //       }

    //     echo json_encode($result);

    //   } else {
    //       echo 'DATOS ERRONEOS';
    //   }
    // }






    /*------ Ver Lista de Partidas Comparativos 2019 -------*/
    // public function comparativo_partidas($dep_id,$aper_id,$tp_tab){ 
    //   $tabla ='';
    //   $partidas_asig=$this->model_ptto_sigep->partidas_accion_region($dep_id,$aper_id,1); // Presupuesto Asignado
    //   $partidas_prog=$this->model_ptto_sigep->partidas_accion_region($dep_id,$aper_id,2); // Presupuesto Programado
    //   $nro=0;
    //   $monto_asig=0;
    //   $monto_prog=0;
    //   $monto_asig_final=0;
    //   if($tp_tab==1){
    //     $tab='id="table" class="table table-bordered"';
    //   }
    //   else{
    //     $tab='cellpadding="0" cellspacing="0" class="tabla" border=0.2 style="width:100%;" align=center';
    //   }

    //   $tabla .='<table '.$tab.'>
    //               <thead>
    //                 <tr style="font-size: 7px;" align=center>
    //                   <th bgcolor="#1c7368" style="width:2%;color:#FFF;height:15px;">NRO.</th>
    //                   <th bgcolor="#1c7368" style="width:3%;color:#FFF;" title="CODIGO PARTIDA">C&Oacute;DIGO</th>
    //                   <th bgcolor="#1c7368" style="width:30%;color:#FFF;" title="DESCRIPCI&Oacute;N PARTIDA">DETALLE PARTIDA</th>
    //                   <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO PRESUPUESTO ASIGNADO INICIAL">PPTO. ASIGNADO INICIAL (AI)</th>
    //                   <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO PRESUPUESTO ASIGNADO APROBADO">PPTO. PROGRAMADO POA (PP)</th>
    //                   <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO DIFERENCIA (INICIAL - PROGRAMADO)">MONTO DIFERENCIA (PP-AI)</th>
    //                   <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO PRESUPUESTO INICIAL APROBADO">PPTO. ASIGNADO APROBADO (AF)</th>
    //                   <th bgcolor="#1c7368" style="width:12%;color:#FFF;" title="MONTO DIFERENCIA (INICIAL - FINAL)">MONTO DIFERENCIA (AF-AI)</th>
    //                 </tr>

    //               </thead>
    //               <tbody>';
    //     if(count($partidas_asig)>count($partidas_prog)){
    //         foreach($partidas_asig  as $row){
    //         $part=$this->model_ptto_sigep->get_partida_accion_regional($dep_id,$aper_id,$row['par_id']); //// Presupuesto Programado
    //         $m_aprob=$this->model_ptto_sigep->get_ptto_aprobado($aper_id,$row['par_id']);

    //           /*------ Asignado-programado -----*/
    //           $prog=0;
    //           if(count($part)!=0){
    //             $prog=$part[0]['monto'];
    //           }
    //           $dif=($row['monto']-$prog);
    //           $color='#f1f1f1';
    //           if($dif<0){
    //             $color='#f9cdcd';
    //           }
    //           /*-------------------------------*/

    //           $monto_final=0; $color2='#cbf9f3';
    //           if(count($m_aprob)!=0){
    //             $monto_final=$m_aprob[0]['monto'];
    //             if($row['monto']!=$m_aprob[0]['monto']){
    //               $color2='#f9cdcd';
    //             }
    //           }

    //           $nro++;
    //           $tabla .='<tr title="aper : '.$aper_id.'-- par : '.$row['par_id'].'">
    //                       <td align=center bgcolor='.$color.' style="width:2%;height:12px;">'.$nro.'</td>
    //                       <td align=center bgcolor='.$color.' style="width:3%;"><b>'.$row['codigo'].'</b></td>
    //                       <td align=left bgcolor='.$color.' style="width:30%;">'.$row['nombre'].'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($row['monto'], 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($prog, 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($dif, 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color2.' style="width:12%;">'.number_format(($monto_final), 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color2.' style="width:12%;">'.number_format(($monto_final-$row['monto']), 2, ',', '.').'</td>
                          
    //                     </tr>';
    //           $monto_asig=$monto_asig+$row['monto'];
    //           $monto_prog=$monto_prog+$prog;

    //           $monto_asig_final=$monto_asig_final+$monto_final;
    //       }

    //     }
    //     else{
    //         foreach($partidas_prog  as $row){
    //           $part=$this->model_ptto_sigep->get_partida_asig_accion($dep_id,$aper_id,$row['par_id']);
    //           $m_aprob=$this->model_ptto_sigep->get_ptto_aprobado($aper_id,$row['par_id']);

    //           /*------ Asignado-programado -----*/
    //           $asig=0;
    //           if(count($part)!=0){
    //             $asig=$part[0]['monto'];
    //           }
    //           $dif=($asig-$row['monto']);
    //           $color='#f1f1f1';
    //           if($dif<0){
    //             $color='#f9cdcd';
    //           }
    //           /*-------------------------------*/

    //           $monto_final=0; $color2='#cbf9f3';
    //           if(count($m_aprob)!=0){
    //             $monto_final=$m_aprob[0]['monto'];
    //             if($asig!=$m_aprob[0]['monto']){
    //               $color2='#f9cdcd';
    //             }
    //           }

    //           $nro++;
    //           $tabla .='<tr title="aper : '.$aper_id.'-- par : '.$row['par_id'].'"> 
    //                       <td align=center bgcolor='.$color.' style="width:1%;height:12px;">'.$nro.'</td>
    //                       <td align=center bgcolor='.$color.' style="width:5%;"><b>'.$row['codigo'].'</b></td>
    //                       <td align=left bgcolor='.$color.' style="width:30%;">'.$row['nombre'].'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($asig, 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($row['monto'], 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color.' style="width:12%;">'.number_format($dif, 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color2.' style="width:12%;">'.number_format(($monto_final), 2, ',', '.').'</td>
    //                       <td align=right bgcolor='.$color2.' style="width:12%;">'.number_format(($monto_final-$asig), 2, ',', '.').'</td>
                          
    //                     </tr>';
    //           $monto_asig=$monto_asig+$row['monto'];
    //           $monto_prog=$monto_prog+$asig;

    //           $monto_asig_final=$monto_asig_final+$monto_final;
    //       }

    //     }

    //   $tabla .='</tbody>
    //               <tr>
    //                   <td colspan=3 style="height:12px;"><strong>TOTAL</strong></td>
    //                   <td align=right>'.number_format($monto_asig, 2, ',', '.').'</td>
    //                   <td align=right>'.number_format($monto_prog, 2, ',', '.').'</td>
    //                   <td align=right>'.number_format(($monto_asig-$monto_prog), 2, ',', '.').'</td>
    //                   <td align=right>'.number_format($monto_asig_final, 2, ',', '.').'</td>
    //                   <td align=right></td>
    //                 </tr>
    //             </table>';

    //   return $tabla;
    // }

    /*-------- REPORTE COMPARATIVO POR UNIDAD (PDF) ---------*/
/*    public function reporte_comparativo_unidad($proy_id){
      $data['proyecto'] = $this->model_proyecto->get_UnidadOrganizacional($proy_id);

      if(count($data['proyecto'])!=0){
        $data['mes'] = $this->mes_nombre();
        //$data['partidas']= $this->comparativo_partidas($data['proyecto'][0]['dep_id'],$data['proyecto'][0]['aper_id'],2); //// Cuadro comparativo de partidas
        $data['partidas']= $this->comparativo_partidas_ppto_final($data['proyecto'][0]['dep_id'],$data['proyecto'][0]['aper_id'],2); //// Cuadro comparativo de partidas
        $this->load->view('admin/mantenimiento/ptto_sigep/reporte_comparativo_partidas', $data);
      }
      else{
        echo "ERROR";
      }
    }*/
    
    /*------------- REPORTE COMPARATIVO TODOS (EXCEL) -------------*/
    // public function exportar_cuadro_comparativo($dep_id,$tp){
    //   if($tp==1 || $tp==4){
    //    // echo $this->cuadro_excel($dep_id,$tp);
    //     $dep=$this->model_proyecto->get_departamento($dep_id);
    //     $departamento=$dep[0]['dep_departamento'];
    //     $cuadro=$this->cuadro_excel($dep_id,$tp);
    //     date_default_timezone_set('America/Lima');
    //     $fecha = date("d-m-Y H:i:s");
    //     header('Content-type: application/vnd.ms-excel');
    //     header("Content-Disposition: attachment; filename=COMPARATIVO - ".$departamento."_$fecha.xls"); //Indica el nombre del archivo resultante
    //     header("Pragma: no-cache");
    //     header("Expires: 0");
    //     echo "";
    //     echo "".$cuadro."";
    //   }
    //   else{
    //     echo "ERROR";
    //   }
    // }

    // public function cuadro_excel($dep_id,$tp){
    //   $tabla='';
    //   if($tp==1){ /// Proyecto de Inversion
    //     $poa=$this->model_proyecto->list_proy_inversion_regional($dep_id);
    //     $tit='PROYECTO DE INVERSIÓN';
    //   }
    //   else{ /// Gasto Corriente
    //     $poa=$this->model_proyecto->list_gasto_corriente_regional($dep_id);
    //     $tit='GASTO CORRIENTE';
    //   }

    //   $tabla .='
    //     <style>
    //       table{font-size: 9px;
    //         width: 80%;
    //         max-width:1550px;
    //         overflow-x: scroll;
    //         }
    //         th{
    //           padding: 1.4px;
    //           text-align: center;
    //           font-size: 10px;
    //         }
    //     </style>
    //     <table border="1" cellpadding="0" cellspacing="0" class="tabla">
    //     <thead>
    //     <tr class="modo1" style="height:50px;">
    //       <th>APERTURA PROGRAMATICA '.$this->gestion.'</th>
    //       <th>'.$tit.'</th>
    //       <th>CODIGO PARTIDA</th>
    //       <th>PRESUPUESTO POA (SIIPLAS)</th>
    //       <th>PRESUPUESTO APROBADO (SIGEP)</th>
    //       <th>MONTO DIFERENCIA (SIIPLAS-SIGEP)</th>
    //     </tr>
    //     </thead>
    //     <tbody>';
    //     $ppto_siiplas=0;
    //     $ppto_sigep=0;
    //     foreach($poa as $rowp){
    //       $partidas_inicial_asignado=$this->model_ptto_sigep->partidas_accion_region($dep_id,$rowp['aper_id'],1); // Presupuesto Partidas Asignado Inicial
    //       $partidas_aprobados=$this->model_ptto_sigep->list_ppto_final_aprobado($rowp['aper_id']); // Presupuesto Partidas Aprobado
          
    //       foreach($partidas_inicial_asignado as $row){
    //         $ppto=$this->model_ptto_sigep->get_ptto_aprobado($rowp['aper_id'],$row['par_id']); /// ppto asignado aprobado
    //         $monto_final_partida=0;
    //         $dif_monto=0;
    //         if(count($ppto)!=0){
    //           $monto_final_partida=$ppto[0]['monto']; /// monto final asignado (aprobado)
    //         }
    //         $dif_monto=$monto_final_partida-$row['ppto_asignado']; /// monto final asignado (aprobado) - monto inicial asignado
    //         $color='';$sig='';
    //         if($dif_monto<0){
    //           $color='#f7b1b0';
    //         }
    //         elseif ($dif_monto>0) {
    //           $sig='+';
    //           $color='#dff0d8';
    //         }

     
    //         $tabla.='
    //           <tr bgcolor='.$color.'>
    //             <td style="width:5%;height:25px;" align=center>\''.$rowp['aper_programa'].''.$rowp['aper_proyecto'].''.$rowp['aper_actividad'].'\'</td>
    //             <td>';
    //             if($tp==1){
    //               $tabla.=''.mb_convert_encoding($rowp['proy_nombre'], 'cp1252', 'UTF-8').'';
    //             }
    //             else{
    //               $tabla.=''.mb_convert_encoding($rowp['tipo'].' '.$rowp['actividad'].' - '.$rowp['abrev'], 'cp1252', 'UTF-8').'';
    //             }
    //             $tabla.='
    //             </td>
    //             <td style="width:5%;" align=center><b>'.$row['codigo'].'</b></td>
    //             <td style="width:12%;" align=right>'.$row['ppto_asignado'].'</td>
    //             <td style="width:12%;" align=right>'.$monto_final_partida.'</td>
    //             <td style="width:12%;" align=right>'.$dif_monto.'</td>
    //           </tr>';

    //           $ppto_siiplas=$ppto_siiplas+$row['ppto_asignado'];
    //           $ppto_sigep=$ppto_sigep+$monto_final_partida;

    //       }

    //       foreach($partidas_aprobados as $row){
    //         //$ppto=$this->model_ptto_sigep->get_partida_accion($rowp['aper_id'],$row['par_id']);

    //         $ppto=$this->model_ptto_sigep->get_partida_asignado_sigep($rowp['aper_id'],$row['par_id']);
    //         if(count($ppto)==0){
    //           $dif_monto='+'.$row['importe']-0;
    //           $tabla.='
    //             <tr bgcolor="#dff0d8">
    //               <td style="width:5%;height:25px;" align=center>\''.$rowp['aper_programa'].''.$rowp['aper_proyecto'].''.$rowp['aper_actividad'].'\'</td>
    //               <td>';
    //               if($tp==1){
    //                 $tabla.=''.mb_convert_encoding($rowp['proy_nombre'], 'cp1252', 'UTF-8').'';
    //               }
    //               else{
    //                 $tabla.=''.mb_convert_encoding($rowp['tipo'].' '.$rowp['actividad'].' - '.$rowp['abrev'], 'cp1252', 'UTF-8').'';
    //               }
    //               $tabla.='
    //               </td>
    //               <td style="width:5%;" align=center><b>'.$row['partida'].'</b></td>
    //               <td style="width:12%;" align=right>0</td>
    //               <td style="width:12%;" align=right>'.$row['importe'].'</td>
    //               <td style="width:12%;" align=right>'.$dif_monto.'</td>
    //             </tr>';
    //             $ppto_sigep=$ppto_sigep+$row['importe'];
    //         }
    //       }

    //     }
    //   $tabla.='
    //     <tr>
    //       <td colspan=3></td>
    //       <td align=right>'.$ppto_siiplas.'</td>
    //       <td align=right>'.$ppto_sigep.'</td>
    //       <td></td>
    //     </tr>
    //     </tbody>
    //   </table>';
    //   return $tabla;
    // }


    function mes_nombre(){
        $mes[1] = 'ENE.';
        $mes[2] = 'FEB.';
        $mes[3] = 'MAR.';
        $mes[4] = 'ABR.';
        $mes[5] = 'MAY.';
        $mes[6] = 'JUN.';
        $mes[7] = 'JUL.';
        $mes[8] = 'AGOS.';
        $mes[9] = 'SEPT.';
        $mes[10] = 'OCT.';
        $mes[11] = 'NOV.';
        $mes[12] = 'DIC.';
        return $mes;
    }

    /*-------------------------- Menu ----------------------------*/
    function menu($mod){
        $enlaces=$this->menu_modelo->get_Modulos($mod);
        for($i=0;$i<count($enlaces);$i++){
          $subenlaces[$enlaces[$i]['o_child']]=$this->menu_modelo->get_Enlaces($enlaces[$i]['o_child'], $this->session->userdata('user_name'));
        }

        $tabla ='';
        for($i=0;$i<count($enlaces);$i++){
            if(count($subenlaces[$enlaces[$i]['o_child']])>0){
                $tabla .='<li>';
                    $tabla .='<a href="#">';
                        $tabla .='<i class="'.$enlaces[$i]['o_image'].'"></i> <span class="menu-item-parent">'.$enlaces[$i]['o_titulo'].'</span></a>';    
                        $tabla .='<ul>';    
                            foreach ($subenlaces[$enlaces[$i]['o_child']] as $item) {
                            $tabla .='<li><a href="'.base_url($item['o_url']).'">'.$item['o_titulo'].'</a></li>';
                        }
                        $tabla .='</ul>';
                $tabla .='</li>';
            }
        }

        return $tabla;
    }

    function rolfun($rol){
      $valor=false;
      for ($i=1; $i <=count($rol) ; $i++) { 
        $data = $this->Users_model->get_datos_usuario_roles($this->session->userdata('fun_id'),$rol[$i]);
        if(count($data)!=0){
          $valor=true;
          break;
        }
      }
      return $valor;
    }
    /*------------------ Rol Funcionario ---------------------*/
/*    

    
    function estilo_vertical(){
        $estilo_vertical = '<style>
        body{
            font-family: sans-serif;
            }
        table{
            font-size: 8px;
            width: 100%;
            background-color:#fff;
        }
        .mv{font-size:10px;}
        .verde{ width:100%; height:5px; background-color:#1c7368;}
        .blanco{ width:100%; height:5px; background-color:#F1F2F1;}
        .siipp{width:120px;}

        .titulo_pdf {
            text-align: left;
            font-size: 8px;
        }
        .tabla {
        font-family: Verdana, Arial, Helvetica, sans-serif;
        font-size: 8px;
        width: 100%;

        }
        .tabla th {
        padding: 2px;
        font-size: 6px;
        background-color: #1c7368;
        background-repeat: repeat-x;
        color: #FFFFFF;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-right-style: solid;
        border-bottom-style: solid;
        border-right-color: #558FA6;
        border-bottom-color: #558FA6;
        text-transform: uppercase;
        }
        .tabla .modo1 {
        font-size: 6px;
        font-weight:bold;
       
        background-image: url(fondo_tr01.png);
        background-repeat: repeat-x;
        color: #34484E;
       
        }
        .tabla .modo1 td {
        padding: 1px;
        border-right-width: 1px;
        border-bottom-width: 1px;
        border-right-style: solid;
        border-bottom-style: solid;
        border-right-color: #A4C4D0;
        border-bottom-color: #A4C4D0;
        }
    </style>';
        return $estilo_vertical;
    }*/
}