<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: linear-gradient(135deg, #BFC3D1, #14CCE8);
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            text-align: center;
            width: 350px;
        }
        h2 { color: black; margin-bottom: 20px; font-weight: 600; }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: black;
            outline: none;
            text-align: center;
            font-size: 18px;
            letter-spacing: 5px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #2E4EB0;
            border: none;
            border-radius: 5px;
            color: black;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn:hover { background: #525252; }
        .error, .success-message {
            font-size: 14px;
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
        }
        .error { background-color: #ff4d4d; color: white; }
        .success-message { background-color: #28a745; color: white; }
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7); display: none;
            justify-content: center; align-items: center; z-index: 1;
        }
        .loader {
            border: 10px solid #f3f3f3;
            border-top: 10px solid #3498db;
            border-radius: 50%;
            width: 80px; height: 80px;
            animation: spin 1.5s linear infinite;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verificación 2FA</h2>

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

        <form action="{{ route('2fa.verify') }}" method="POST" id="verifyForm">
            @csrf
            <input type="text" class="input-field" name="code" id="code" placeholder="••••••" maxlength="6" required>
            
            <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>

            <button type="submit" class="btn" id="submitBtn">Verificar</button>
        </form>
    </div>

    <div class="overlay" id="loaderOverlay">
        <div class="loader"></div>
    </div>

    <script>
        const form = document.getElementById('verifyForm');
        const submitBtn = document.getElementById('submitBtn');
        const loaderOverlay = document.getElementById('loaderOverlay');
        const codeInput = document.getElementById('code');

        form.addEventListener('submit', function(event) {
            if (codeInput.value.trim().length !== 6 || isNaN(codeInput.value)) {
                event.preventDefault();
                Swal.fire({ title: 'Error', text: 'AUTH_009-Insert a valid 6-digit code.', icon: 'error', confirmButtonText: 'OK' });
                return;
            }

            if (!grecaptcha.getResponse()) {
                event.preventDefault();
                Swal.fire({ title: 'Error', text: 'Please complete the reCAPTCHA.', icon: 'error', confirmButtonText: 'OK' });
                return;
            }

            submitBtn.disabled = true;
            loaderOverlay.style.display = 'flex';
        });

        codeInput.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').substring(0, 6);
        });

        window.addEventListener('pageshow', function(event) {
            if (event.persisted || performance.getEntriesByType('navigation')[0].type === 'back_forward') {
                loaderOverlay.style.display = 'none';
                submitBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
