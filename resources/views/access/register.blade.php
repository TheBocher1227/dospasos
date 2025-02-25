<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #BFC3D1, #14CCE8);
        }
        .register-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            text-align: center;
            width: 400px;
            position: relative;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #2E4EB0;
            border: none;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-bottom: 5px;
            text-align: left;
        }
        .success-message {
            background-color: #28a745;
            color: white;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-weight: bold;
        }
        .password-tooltip {
            background: #fff;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            font-size: 0.9rem;
            display: none;
            width: 250px;
            color: #333;
            text-align: left;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
        }
        .valid { color: green; }
        .invalid { color: red; }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Registro</h2>

        @if ($errors->any())
            <div class="error">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="success-message">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="error">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        <form action="{{ route('auth.registeruser') }}" method="POST" id="registerForm">
            @csrf

            <input type="text" class="input-field" name="name" placeholder="Nombre" value="{{ old('name') }}" required>
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror

            <input type="email" class="input-field" name="email" placeholder="Correo Electrónico" value="{{ old('email') }}" required>
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror

            <input type="text" class="input-field" name="phonenumber" placeholder="Teléfono (+521234567890)" value="{{ old('phonenumber') }}" required>
            @error('phonenumber')
                <div class="error">{{ $message }}</div>
            @enderror

            <input type="password" class="input-field" name="password" id="password" placeholder="Contraseña" required>
            <div id="password-tooltip" class="password-tooltip">
                <p id="length" class="invalid">❌ Al menos 8 caracteres</p>
                <p id="uppercase" class="invalid">❌ Una letra mayúscula</p>
                <p id="number" class="invalid">❌ Un número</p>
                <p id="special" class="invalid">❌ Un carácter especial (!@#$%^&*)</p>
            </div>
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror

            <!-- reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
            @error('g-recaptcha-response')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn" id="submitBtn" disabled>Registrarse</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const passwordTooltip = document.getElementById("password-tooltip");
            const submitButton = document.getElementById("submitBtn");
            const requirements = {
                length: document.getElementById("length"),
                uppercase: document.getElementById("uppercase"),
                number: document.getElementById("number"),
                special: document.getElementById("special")
            };

            passwordInput.addEventListener("focus", function () {
                passwordTooltip.style.display = "block";
            });

            passwordInput.addEventListener("blur", function () {
                passwordTooltip.style.display = "none";
            });

            passwordInput.addEventListener("input", function () {
                const password = passwordInput.value;
                const lengthValid = password.length >= 8;
                const uppercaseValid = /[A-Z]/.test(password);
                const numberValid = /\d/.test(password);
                const specialValid = /[!@#$%^&*]/.test(password);

                updateRequirement(requirements.length, lengthValid);
                updateRequirement(requirements.uppercase, uppercaseValid);
                updateRequirement(requirements.number, numberValid);
                updateRequirement(requirements.special, specialValid);

                submitButton.disabled = !(lengthValid && uppercaseValid && numberValid && specialValid);
            });

            function updateRequirement(element, isValid) {
                element.classList.toggle("valid", isValid);
                element.classList.toggle("invalid", !isValid);
                element.innerHTML = (isValid ? "✅ " : "❌ ") + element.innerHTML.slice(2);
            }

            document.getElementById('registerForm').addEventListener('submit', function(event) {
                var response = grecaptcha.getResponse();
                if (!response) {
                    event.preventDefault();
                    alert("Por favor, verifica que no eres un robot.");
                }
            });
        });
    </script>
</body>
</html>
