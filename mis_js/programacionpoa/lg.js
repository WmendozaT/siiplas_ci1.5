 base = $('[name="base"]').val();
    $(function(){
        $('#radio0').click(function(){
          $('[name="tp"]').val(0);
        });

        $('#radio1').click(function(){
          $('[name="tp"]').val(1);
        });
    })

$(document).ready(function() {
    $('#form').on('submit', function(event) {
        // 1. Detenemos el envío automático siempre para validar primero
        event.preventDefault(); 

        let valid = true;

        // Validar usuario
        const userName = $('input[name="user_name"]').val();
        if (userName.trim() === '') {
            $('#usu').css('visibility', 'visible');
            valid = false;
        } else {
            $('#usu').css('visibility', 'hidden');
        }

        // Validar contraseña
        const password = $('#password').val();
        if (password.trim() === '') {
            $('#pass').css('visibility', 'visible');
            valid = false;
        } else {
            $('#pass').css('visibility', 'hidden');
        }

        // Validar captcha
        const captcha = $('#dat_captcha').val();
        if (captcha.trim() === '' || captcha.length < 4) {
            $('#cat').css('visibility', 'visible');
            valid = false;
        } else {
            $('#cat').css('visibility', 'hidden');
        }

        // 2. Solo si todo es válido, mostramos el loading y enviamos
        if (valid) {
            // Mostrar el overlay negro "bonito" que creamos
            $('#loading-overlay').css('display', 'flex').hide().fadeIn(300);
            
            // Deshabilitar botón para evitar doble clic
            $('#kc-login').prop('disabled', true).val('PROCESANDO...');

            // Enviamos el formulario programáticamente
            this.submit(); 
        } else {
            // Si no es válido, nos aseguramos que el loading esté oculto
            $('#loading-overlay').hide();
            return false;
        }
    });
});


       $(document).ready(function() {
            $('#formpws').on('submit', function(event) {
                // 1. Detenemos el envío para validar
                event.preventDefault(); 

                let valid = true;
                const alphanumericRegex = /^[A-Za-z0-9.]+$/; 
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                // Validar usuario
                const userName = $('input[name="user_namepws"]').val().trim();
                if (!userName) {
                    $('#usupsw').html('<b><i class="fas fa-exclamation-circle"></i> Campo obligatorio</b>').css('visibility', 'visible');
                    valid = false;
                } else if (!alphanumericRegex.test(userName)) {
                    $('#usupsw').html('<b><i class="fas fa-exclamation-triangle"></i> Solo letras, números y puntos</b>').css('visibility', 'visible');
                    valid = false;
                } else {
                    $('#usupsw').css('visibility', 'hidden');
                }

                // Validar Email
                const email = $('#emailpws').val().trim();
                if (!email) {
                    $('#email').html('<b><i class="fas fa-exclamation-circle"></i> Email requerido</b>').css('visibility', 'visible');
                    valid = false;
                } else if (!emailRegex.test(email)) {
                    $('#email').html('<b><i class="fas fa-envelope"></i> Formato inválido</b>').css('visibility', 'visible');
                    valid = false;
                } else {
                    $('#email').css('visibility', 'hidden');
                }

                // 2. Si todo es válido, lanzamos el loading "bonito"
                if (valid) {
                    // Personalizamos el texto del overlay antes de mostrarlo
                    $('.loader-text').text('VALIDANDO SOLICITUD');
                    $('.loader-subtext').text('Buscando cuenta vinculada al correo...');
                    
                    // Mostrar overlay negro con desenfoque
                    $('#loading-overlay').css('display', 'flex').hide().fadeIn(300);
                    
                    // Bloqueamos el botón de envío del modal
                    $(this).find('input[type="submit"], button').prop('disabled', true).val('ENVIANDO...');

                    // Enviamos el formulario al servidor
                    this.submit(); 
                } else {
                    // Aseguramos que el overlay esté oculto si hay error de validación
                    $('#loading-overlay').hide();
                }
            });
        });

        $(document).ready(function(e) {
          $('#refreshs').click(function(){
              var url = base+"index.php/user/get_captcha";
 
              var request;
              if (request) {
                  request.abort();
              }
              request = $.ajax({
                url: url,
                type: "POST",
                dataType: 'json', 
              });

              request.done(function (response, textStatus, jqXHR) {
                if (response.respuesta == 'correcto') {
                  $("#refreshs").html(response.cod_captcha);
                  document.getElementById("captcha").value = response.captcha;
                }
              }); 
          });
        });

        $("#sub").on("click", function (e) {
          document.getElementById("but").style.display = 'none';
          document.getElementById("but2").style.display = 'none';
          document.getElementById("load").style.display = 'block';
        });


    /////-----------------------------------------
    function togglePassword(fieldId) {
        const passwordInput = document.getElementById(fieldId);
        let toggleIconId;

         // Determinar el ID del icono basado en el campo
        switch(fieldId) {
            case 'password':
                toggleIconId = 'toggleIcon';
                break;
        }
        
        const toggleIcon = document.getElementById(toggleIconId);
        if (passwordInput && toggleIcon) {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                //toggleIcon.textContent = '🙈';
            } else {
                passwordInput.type = 'password';
                //toggleIcon.textContent = '👁️';
            }
        }
    }