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
    function actualizarMontoAsignado(sp_id) {
        // 1. Obtenemos el nuevo monto asignado que escribió el usuario
        var nuevo_monto = parseFloat($('#monto_' + sp_id).val()) || 0;
        
        // 2. Leemos el monto programado directamente desde el atributo data-programado
        var programado = parseFloat($('#monto_' + sp_id).attr('data-programado')) || 0;
        
        // 3. Calculamos el saldo matemáticamente en el navegador
        var nuevo_saldo = (nuevo_monto - programado).toFixed(2);

        // 4. Actualizamos la celda del saldo en caliente (UI)
        var celda_saldo = $('#saldo_' + sp_id);
        celda_saldo.text(nuevo_saldo);
        
        // Aplicamos el estilo de alerta cromática si queda sobregirado
        if (parseFloat(nuevo_saldo) < 0) {
            celda_saldo.css({ 'background': '#fef2f2', 'color': '#dc2626', 'font-weight': 'bold' });
        } else {
            celda_saldo.css({ 'background': '#f8fafc', 'color': '#334155', 'font-weight': 'bold' });
        }

        // 5. Enviamos a la base de datos en segundo plano
        $.ajax({
            url: base + "index.php/mantenimiento/cptto_poa/actualizar_monto",
            type: 'POST',
            data: { sp_id: sp_id, importe: nuevo_monto },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success') {
                    console.log('Monto guardado en la BD exitosamente.');
                } else {
                    alert('El saldo cambió en pantalla, pero hubo un error al guardar en la base de datos.');
                }
            },
            error: function() {
                alert('Error de red. No se pudo guardar el cambio en el servidor.');
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























    function eliminarPartidaAsignada(sp_id) {
        if (confirm('¿Está completamente seguro de eliminar esta asignación presupuestaria?')) {
            $.ajax({
                url: 'partidas/eliminar_asignacion', // Modifica esta URL según tu enrutamiento/controlador
                type: 'POST',
                data: { sp_id: sp_id },
                dataType: 'json',
                success: function(response) {
                    if(response.status == 'success') {
                        // Remueve la fila de la tabla de forma animada y automática
                        $('#fila_partida_' + sp_id).fadeOut(400, function() {
                            $(this).remove();
                        });
                    } else {
                        alert('Error al intentar eliminar el registro.');
                    }
                },
                error: function() {
                    alert('Error de comunicación con el servidor al eliminar.');
                }
            });
        }
    }