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
            event.preventDefault(); // Evitar el envío del formulario

            // Mostrar el loading
            $('#loading').show();

            // Validación de datos
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
            if (captcha.trim() === '') {
                $('#cat').css('visibility', 'visible');
                valid = false;
            } else {
                $('#cat').css('visibility', 'hidden');
            }

            if (valid) {
                // Simulación de envío de datos
                this.submit(); // Enviar el formulario si es válido
            } else {
                $('#loading').hide(); // Ocultar loading si hay un error
            }
        });
    });

        $(document).ready(function() {
        $('#formpws').on('submit', function(event) {
            event.preventDefault(); // Evitar el envío del formulario

            // Mostrar el loading
            $('#loadingpws').show();

            // Validación de datos
            let valid = true;
            const alphanumericRegex = /^[A-Za-z0-9.]+$/; // Regex para letras y números
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Expresión regular para validación

            // Validar usuario
            const userName = $('input[name="user_namepws"]').val().trim();
            if (!userName) {
                $('#usupsw').text('Campo obligatorio').css('visibility', 'visible');
                valid = false;
            } else if (!alphanumericRegex.test(userName)) {
                $('#usupsw').text('Solo letras y números permitidos').css('visibility', 'visible');
                valid = false;
            } else {
                $('#usupsw').css('visibility', 'hidden');
            }

            // Validar contraseña
              const email = $('#emailpws').val().trim();
                if (!email) {
                    $('#email').text('Email requerido').css('visibility', 'visible');
                    valid = false;
                } else if (!emailRegex.test(email)) {
                    $('#email').text('Formato inválido (ej: usuario@dominio.com)').css('visibility', 'visible');
                    valid = false;
                } else {
                    $('#email').css('visibility', 'hidden');
                }

            if (valid) {
                // Simulación de envío de datos
                this.submit(); // Enviar el formulario si es válido
            } else {
                $('#loadingpws').hide(); // Ocultar loading si hay un error
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