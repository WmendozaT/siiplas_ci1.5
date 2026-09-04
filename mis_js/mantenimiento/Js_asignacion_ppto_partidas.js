var base = $('[name="base"]').val();

/////------- Get muestra el listado de partidas por Unidad Responsable 2027
    var xhr_partidas_unidad = null;
    $(document).on('click', '.btn-ver-partidas-unidad', function(e) {
        e.preventDefault();

        var $btn = $(this);
        
        // 1. Extraemos de forma relativa los metadatos indexados en el botón
        var aper_id    = $btn.data('id');     // ID único del componente
        var serv_cod  = $btn.data('codigo'); // Código de servicio (Ej. 0017)
        var serv_desc = $btn.data('nombre'); // Nombre/Descripción de la Unidad Responsable
        
        var $contenedor_cuerpo = $('#contenedor_desglose_dinamico_cns');

        if (!aper_id) {
            if (typeof alertify !== "undefined") alertify.error("⚠️ Error: Identificador relacional corrupto.");
            return false;
        }

        // 2. TIMING VISUAL: Inyección del preloader vectorial institucional
        $contenedor_cuerpo.html(
            '<div id="loading_partidas" style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 180px; padding: 25px; background: #ffffff;">' +
                '<div style="position: relative; width: 42px; height: 42px; margin-bottom: 12px;">' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 36px; height: 36px; border: 3.5px solid #cbd5e1; border-radius: 50%;"></div>' +
                    '<div style="box-sizing: border-box; display: block; position: absolute; width: 36px; height: 36px; border: 3.5px solid transparent; border-top-color: #334155; border-radius: 50%; animation: spin_partidas 0.8s linear infinite;"></div>' +
                '</div>' +
                '<h5 style="font-family: Arial, sans-serif; font-weight: bold; color: #1e293b; font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 4px 0;">Procesando Consulta</h5>' +
                '<p style="font-family: Arial, sans-serif; font-size: 11px; color: #64748b; margin: 0; font-weight: 500;">' +
                    '<i class="fa fa-database text-warning" style="animation: pulse_partidas 1.5s infinite; margin-right: 4px;"></i> Extrayendo clasificadores contables desde PostgreSQL...' +
                '</p>' +
                '<style>' +
                    '@keyframes spin_partidas { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }' +
                    '@keyframes pulse_partidas { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }' +
                '</style>' +
            '</div>'
        );

        var url = base + "index.php/mantenimiento/cptto_poa/get_desglose_partidas_unidad_ajax";
        
        // Captura automática perimetral del Token CSRF de resguardo
        var csrf_name = $('[name="csrf_test_name"]').attr('name') || '';
        var csrf_hash = $('[name="csrf_test_name"]').val() || '';
        var token_seguridad = (csrf_name !== '') ? "&" + csrf_name + "=" + csrf_hash : "";

        // 3. ANULACIÓN DE RÁFAGAS: Cancelamos solicitudes previas para no saturar la RAM de Apache
        if (xhr_partidas_unidad && xhr_partidas_unidad.readyState !== 4) {
            xhr_partidas_unidad.abort();
        }

        // 4. DESPACHO ASÍNCRONO DEL HILO DE RED
        xhr_partidas_unidad = $.ajax({
            type: "POST",
            url: url,
            dataType: "json",
            data: "aper_id=" + aper_id + token_seguridad,
            success: function(res) {
                // Vacíamos el contenedor removiendo la animación del spinner
                $contenedor_cuerpo.html('');

                if (res.status === 'success' || res.respuesta === 'correcto') {
                    
                    // Estructuramos el membrete formal corporativo de la unidad
                    var html_membrete = `
                        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #334155; padding: 10px 14px; margin-bottom: 15px; border-radius: 3px;">
                            <span style="display:block; font-size:10px; font-weight:bold; color:#64748b; text-transform:uppercase; letter-spacing:0.3px;">Unidad Responsable Consultada:</span>
                            <strong style="color:#0f172a; font-size:12px; font-family:Arial, sans-serif; display:block; margin-top:2px;">
                             ${aper_id} - ${serv_desc}
                            </strong>
                        </div>
                    `;
                    
                    // Estampamos la tabla de partidas desvaneciendo suavemente el loading
                    $contenedor_cuerpo.hide().html(html_membrete + res.html_reporte).fadeIn(200);

                } else {
                    $contenedor_cuerpo.html('<div class="alert alert-danger text-center" style="font-weight:bold; margin:0;"><i class="fa fa-times-circle"></i> Error: ' + res.message + '</div>');
                }
            },
            error: function(xhr, textStatus, errorThrown) {
                if (textStatus === 'abort') return; // Ignoramos si fue cancelado por el abort de control
                console.error("CNS ERROR GET DETAIL PARTIDAS -> " + textStatus + " | " + errorThrown);
                $contenedor_cuerpo.html('<div class="alert alert-danger text-center" style="font-weight:bold; margin:0;"><i class="fa fa-exclamation-triangle"></i> Falla crítica al conectar con el servidor de presupuestos centralizados.</div>');
            }
        });
    });

    //// Actualiza montos en las partidas asigandas
    function actualizarMontoAsignado(sp_id, tipo) {
        tip_reg='revertido poa';
        if(tipo==0){
            tip_reg='asignacion poa';
        }
        // 1. Apuntamos al input dinámico según el tipo (0 o 1)
        var input_elemento = $('#monto_' + tipo + '_' + sp_id);
        var nuevo_monto    = parseFloat(input_elemento.val()) || 0;
        
        // 2. Obtenemos lo programado guardado en el data-programado correspondiente
        var programado     = parseFloat(input_elemento.attr('data-programado')) || 0;
        
        // 3. Calculamos el nuevo saldo en caliente (Monto - Programado)
        var nuevo_saldo    = (nuevo_monto - programado).toFixed(2);
        
        // 4. Actualizamos el texto de la celda de saldo respectiva (saldo_0_... o saldo_1_...)
        var celda_saldo    = $('#saldo_' + tipo + '_' + sp_id);
        celda_saldo.text(nuevo_saldo);
        
        // Control visual de colores si queda en sobregiro
        if (parseFloat(nuevo_saldo) < 0) {
            celda_saldo.css({ 'background': '#fef2f2', 'color': '#dc2626', 'font-weight': 'bold' });
        } else {
            if(tipo === 1) {
                celda_saldo.css({ 'background': '#F5E9EA', 'color': '#334155', 'font-weight': 'bold' });
            } else {
                celda_saldo.css({ 'background': '#f8fafc', 'color': '#334155', 'font-weight': 'bold' });
            }
        }

        // 5. Enviamos la petición AJAX al servidor mandando la variable 'tipo'
        $.ajax({
            url: base + "index.php/mantenimiento/cptto_poa/actualizar_monto",
            type: 'POST',
            data: { 
                sp_id: sp_id, 
                importe: nuevo_monto,
                tipo: tipo // 🎯 Enviamos si es 0 (Normal) o 1 (Revertido)
            },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    //console.log('Monto de tipo ' + tipo + ' guardado en BD con éxito.');
                    alert('Monto ' + tip_reg + ' de Bs. '+ nuevo_monto +' guardado en BD con éxito.');
                } else {
                    alert('El cambio se reflejó en pantalla, pero hubo un error al guardar en la base de datos.');
                }
            },
            error: function() {
                alert('Error de red. No se pudo conectar con el servidor.');
            }
        });
    }


    ///// Agregar Nueva partida y su monto
    function guardarNuevaPartidaModal(aper_id) {
        var par_id = $('#select_nueva_partida').val();
        var monto  = $('#monto_nueva_partida').val();

        // 1. Validación estricta de Selección de Partida
        if(par_id == "" || par_id == undefined) {
            alert("Por favor, seleccione una partida del listado.");
            return;
        }

        // 2. 🛡️ VALIDACIÓN REFORZADA: Prohibir campos vacíos, nulos, menores o IGUALES a 0
        if(monto === "" || monto === undefined || monto === null) {
            alert("Por favor, ingrese el monto asignado para la partida.");
            return;
        }
        
        var monto_float = parseFloat(monto);
        if(isNaN(monto_float) || monto_float <= 0) {
            alert("El monto asignado debe ser un número mayor a cero (0).");
            return;
        }

        // 3. Envío de datos al Servidor
        $.ajax({
            url: base + "index.php/mantenimiento/cptto_poa/guardar_nueva_partida",
            type: 'POST',
            data: { 
                aper_id: aper_id, 
                par_id: par_id,
                importe: monto_float
            },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    alert('Partida adicionada con éxito.');
                    
                    // 🔄 ACTUALIZACIÓN AUTOMÁTICA EN CALIENTE (Sin cerrar ni resetear el modal)
                    // Realizamos una petición directa al desglose para traer el HTML actualizado de la tabla
                    $.ajax({
                        url: base + "index.php/mantenimiento/cptto_poa/get_desglose_partidas_unidad_ajax",
                        type: 'POST',
                        data: { aper_id: aper_id },
                        dataType: 'json',
                        success: function(res_modal) {
                            if(res_modal.status == 'success') {
                                // Extraemos temporalmente el contenido del nuevo tbody generado
                                var nuevo_html_tabla = $(res_modal.html_reporte).find('#cuerpo_tabla_partidas').html();
                                
                                // Reemplazamos en caliente el cuerpo viejo por el nuevo sin tocar las propiedades del modal
                                $('#cuerpo_tabla_partidas').html(nuevo_html_tabla);
                                
                                // Limpiamos los campos del formulario de inserción superior para dejarlo listo
                                $('#select_nueva_partida').val('');
                                $('#monto_nueva_partida').val('');
                            }
                        }
                    });

                } else {
                    // Muestra el mensaje enviado desde el controlador (ej: "la partida seleccionada ya se encuentra asignado...")
                    alert(response.msg || 'No se pudo agregar la partida.');
                }
            },
            error: function() {
                alert('Error de conexión con el servidor al intentar registrar la partida.');
            }
        });
    }


 ///// Imprime el listado (Modal) de partidas por Unidad Organizacional 
    function imprimirDetallePartidasModal() {
        var contenido_tabla = document.getElementById("area_impresion_detalle_partidas").innerHTML;
        var membrete_unidad = $(".modal-body div[style*='border-left']").html() || "DESGLOSE DE PARTIDAS";

        var ventana_impresion = window.open('', '_blank', 'height=650,width=1000');

        ventana_impresion.document.write('<html><head><title>SIIPLAS v2.0 - Detalle Partidas por Unidad Organizacional</title>');
        ventana_impresion.document.write('<style>');
        
        // Fuerza la pre-configuración de hoja carta horizontal con márgenes de seguridad
        ventana_impresion.document.write('@page { size: letter landscape; margin: 12mm 10mm; }');
        
        ventana_impresion.document.write('body { font-family: Arial, sans-serif; padding: 0; margin: 0; color: #000; background:#fff; }');
        ventana_impresion.document.write('.header-print { border-bottom: 2px double #000; padding-bottom: 6px; margin-bottom: 15px; }');
        
        // Mantenemos el tamaño exacto del contenido original solicitado pero forzando el auto-ajuste de ancho
        ventana_impresion.document.write('table { width: 100% !important; border-collapse: collapse; font-size: 10px; margin-top: 10px; table-layout: auto; }');
        ventana_impresion.document.write('th, td { border: 1px solid #000000; padding: 5px;  }');
        ventana_impresion.document.write('th { background: #cbd5e1 !important; color: #000 !important; text-align: center; font-weight: bold; font-size: 10px; height: 24px; }');
        ventana_impresion.document.write('td:first-child, th:first-child { text-align: left; font-weight: bold; }');
        
        // Sombreado de resguardo de celdas rojas de sobregiro para la versión física impresa
        ventana_impresion.document.write('td[style*="color: rgb(220, 38, 38)"], td[style*="color: #dc2626"] { background-color: #fee2e2 !important; color: #b91c1c !important; }');
        
        // Factor de escala elástico controlado para compactar el lote de columnas
        ventana_impresion.document.write('@media print { body { zoom: 90%; -webkit-print-color-adjust: exact; print-color-adjust: exact; } }');
        ventana_impresion.document.write('</style></head><body>');
        
        // Membrete oficial de la Caja Nacional de Salud
        ventana_impresion.document.write('<div class="header-print">');
        ventana_impresion.document.write('<h3 style="margin:0; font-size:12px; text-transform:uppercase;">Caja Nacional de Salud</h3>');
        ventana_impresion.document.write('<small style="color:#475569; font-size:9.5px; font-weight:600; display:block; margin-top:1px;">SIIPLAS v2.0 - Departamento Nacional de Planificación</small>');
        ventana_impresion.document.write('<div style="margin-top:8px; font-size:11px; background:#f8fafc; padding:6px; border:1px solid #cbd5e1; border-radius:3px; font-weight:500;">' + membrete_unidad + '</div>');
        ventana_impresion.document.write('</div>');
        
        ventana_impresion.document.write(contenido_tabla);
        ventana_impresion.document.write('</body></html>');

        ventana_impresion.document.close();
        ventana_impresion.focus();
        
        setTimeout(function() {
            ventana_impresion.print();
            ventana_impresion.close();
        }, 380);
    }


    ///// Eliminar registro de la partida asignada
    function eliminarPartidaAsignada(sp_id) {
        if (confirm('¿Está completamente seguro de eliminar esta asignación presupuestaria?')) {
            $.ajax({
                // 🎯 URL ajustada al controlador ejecutivo correcto
                url: base + "index.php/mantenimiento/cptto_poa/eliminar_asignacion",
                type: 'POST',
                data: { sp_id: sp_id },
                dataType: 'json',
                success: function(response) {
                    if(response.status == 'success') {
                        // 🌟 Mensaje de confirmación OK al usuario
                        alert('Registro eliminado correctamente.');
                        
                        // Remueve la fila de la tabla de forma animada y automática en caliente
                        $('#fila_partida_' + sp_id).fadeOut(400, function() {
                            $(this).remove();
                            
                            // Opcional: Si la tabla se queda vacía, puedes mostrar el aviso de "Sin requerimientos"
                            if ($('#cuerpo_tabla_partidas tr').length === 0) {
                                $('#cuerpo_tabla_partidas').html('<tr><td colspan="9" class="text-center" style="padding: 15px; font-weight: bold; color: #64748b;"><i class="fa fa-info-circle"></i> Sin requerimientos presupuestarios asignados en esta unidad.</td></tr>');
                            }
                        });
                    } else {
                        alert(response.msg || 'Error al intentar eliminar el registro.');
                    }
                },
                error: function() {
                    alert('Error de comunicación con el servidor al eliminar.');
                }
            });
        }
    }