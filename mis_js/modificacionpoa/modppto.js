/*  base = $('[name="base"]').val();

$(document).ready(function() {
    
    // 1. EVENTO GUARDAR (Individual)
    $('.btn-guardar').on('click', function() {
        var id = $(this).data('id'); // Capturamos el ID de la fila
        var mod = $('#ppto_mod' + id).val();
        var rev = $('#ppto_rev' + id).val();
        var btn = $(this);

        if (confirm('¿Desea registrar los cambios para esta partida?')) {
            btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '<?= site_url("modificaciones/cmod_insumo/guardar_partida_individual") ?>',
                type: 'POST',
                data: { sp_id: id, monto_mod: mod, monto_rev: rev },
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        alert('Guardado correctamente');
                        btn.prop('disabled', false).html('<i class="fa fa-save text-primary"></i>');
                    } else {
                        alert('Error: ' + res.msj);
                        btn.prop('disabled', false).html('<i class="fa fa-save text-primary"></i>');
                    }
                },
                error: function() {
                    alert('Error de conexión');
                    btn.prop('disabled', false).html('<i class="fa fa-save text-primary"></i>');
                }
            });
        }
    });

    // 2. EVENTO ELIMINAR (Individual)
    $('.btn-eliminar').on('click', function() {
        var id = $(this).data('id');
        var fila = $('#fila' + id);

        if (confirm('¿Está seguro de eliminar esta partida asignada?')) {
            $.post('<?= site_url("modificaciones/cmod_insumo/eliminar_partida") ?>', { sp_id: id }, function(response) {
                var res = JSON.parse(response);
                if (res.status === 'success') {
                    fila.fadeOut(500, function() { $(this).remove(); });
                } else {
                    alert(res.msj);
                }
            });
        }
    });

    // 3. CÁLCULO VISUAL EN TIEMPO REAL (Opcional)
    $('.val-mod, .val-rev').on('keyup change', function() {
        var id = $(this).data('id');
        var val = $(this).val();
        // Solo para reflejar el valor en los divs azules de tu tabla
        if ($(this).hasClass('val-mod')) {
            $('#total_asignado' + id).text(parseFloat(val).toFixed(2));
        } else {
            $('#total_asignado_rev' + id).text(parseFloat(val).toFixed(2));
        }
    });
});
*/

















  // function abreVentana_sol(PDF){             
  //   var direccion;
  //   direccion = '' + PDF;
  //   window.open(direccion, "SOLICITUD CERTIFICACIÓN POA" , "width=1000,height=900,scrollbars=NO") ; 
  // }

  // function abreVentana(PDF){             
  //   var direccion;
  //   direccion = '' + PDF;
  //   window.open(direccion, "CERTIFICACIÓN POA" , "width=800,height=750,scrollbars=NO") ; 
  // }

    ///// SELECT DISTRITAL - Para subir Modificacion presupuestaria (vigente)
/*    $(document).ready(function() {
        pageSetUp();
        $("#dep_id").change(function () {
            $("#dep_id option:selected").each(function () {
                elegido=$(this).val();
                $.post(base+"index.php/admin/proy/combo_uejecutoras", { elegido: elegido,accion:'distrital' }, function(data){
                    $("#ue_id").html(data);
                });     
            });
        });
    })*/

/*    $(document).ready(function() {
        pageSetUp();
        $("#reg_id").change(function () {
            $("#reg_id option:selected").each(function () {
                elegido=$(this).val();
                $.post(base+"index.php/admin/proy/combo_uejecutoras", { elegido: elegido,accion:'distrital' }, function(data){
                    $("#uejec_id").html(data);
                    document.getElementById("but").style.display = 'block';
                });     
            });
        });
    })
*/


  //   /// ---- Generar Reporte Detallado por Regional sobre las modificaciones presupuestarias
  //   function generar_modppto_regional(mp_id) {
  //     document.getElementById("mp_id").value = mp_id;
  //     var url = base+"index.php/modificaciones/cmod_presupuestario/get_datos_modificacion_presupuestaria";
  //     var request;
  //     if (request) {
  //         request.abort();
  //     }
  //     request = $.ajax({
  //         url: url,
  //         type: "POST",
  //         dataType: 'json',
  //         data: "mp_id="+mp_id
  //     });

  //     request.done(function (response, textStatus, jqXHR) { 
  //       if (response.respuesta == 'correcto') {
  //           $('#titulo_sol').html('<h2 class="alert alert-success"><center> RESOLUCIÓN : '+response.modificacion[0]['resolucion']+'</center></h2>');
  //           document.getElementById("reg_id").value = response.modificacion[0]['dep_id'];
  //           $('#dist').html(response.distritales);
  //        //  header ("Location: http://www.cristalab.com");
  //       } else {
  //           alertify.error("ERROR AL RECUPERAR DATOS, PORFAVOR CONTACTESE CON EL ADMINISTRADOR"); 
  //       }
  //     });

  //     request.fail(function (jqXHR, textStatus, thrown) {
  //         console.log("ERROR: " + textStatus);
  //     });
  //     request.always(function () {
  //         //console.log("termino la ejecuicion de ajax");
  //     });

  //       // ===VALIDAR REPORTE CLASIFICADO POR REGIONAL
  //       $("#generar_rep").on("click", function (e) {
  //           var error='false';
  //           var regional=document.getElementById('reg_id').value;
  //           var ue=document.getElementById('uejec_id').value;
            
          
  //           if(regional==''){
  //               $('#reg').html('<font color="red" size="1">SELECCIONE REGIONAL (*)</font>');
  //               document.form_rep.regional.focus() 
  //               return 0;
  //           }

  //           if(ue!=''){
  //             window.open(base+"index.php/mod_ppto/rep_mod_ppto_distrital/"+mp_id+"/"+ue, "Modificación Presupuestaria", "width=800, height=800");
  //             $("#modal_regional").modal("hide");
  //            // document.getElementById("but").style.display = 'none';
  //           }
  //           else{
  //             $('#ue').html('<font color="red" size="1">SELECCIONE UNIDAD EJECUTORA (*)</font>');
  //               document.form_rep.ue.focus() 
  //               return 0;
  //           }
  //       });
  //   }

