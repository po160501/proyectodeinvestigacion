<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — SoundGuard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F0F2F5;
        }

        /* Panel izquierdo — branding */
        .left-panel {
            width: 45%;
            background: linear-gradient(160deg, #0D47A1 0%, #1565C0 50%, #00ACC1 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
            padding: 60px 56px;
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            top: -100px; right: -100px;
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 250px; height: 250px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
            bottom: -60px; left: -60px;
        }
        .brand-logo {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 48px;
        }
        .brand-logo .icon-wrap {
            width: 52px; height: 52px; border-radius: 14px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
        }
        .brand-logo .icon-wrap .material-icons { color: #fff; font-size: 28px; }
        .brand-logo span { color: #fff; font-size: 1.5rem; font-weight: 700; letter-spacing: .3px; }

        .left-panel h1 { color: #fff; font-size: 2rem; font-weight: 700; line-height: 1.3; margin-bottom: 16px; }
        .left-panel p  { color: rgba(255,255,255,.75); font-size: .95rem; line-height: 1.7; margin-bottom: 40px; }

        .feature-list { list-style: none; display: flex; flex-direction: column; gap: 14px; }
        .feature-list li {
            display: flex; align-items: center; gap: 12px;
            color: rgba(255,255,255,.85); font-size: .875rem;
        }
        .feature-list li .material-icons {
            font-size: 18px; color: #80DEEA;
            background: rgba(255,255,255,.1);
            padding: 6px; border-radius: 8px;
        }

        .about-link {
            margin-top: 48px;
            display: inline-flex; align-items: center; gap: 6px;
            color: rgba(255,255,255,.7); font-size: .82rem;
            text-decoration: none; transition: color .2s;
        }
        .about-link:hover { color: #fff; }
        .about-link .material-icons { font-size: 16px; }

        /* Panel derecho — formulario */
        .right-panel {
            flex: 1;
            display: flex; align-items: center; justify-content: center;
            padding: 40px 24px;
        }
        .form-card {
            width: 100%; max-width: 420px;
        }
        .form-card h2 { font-size: 1.6rem; font-weight: 700; color: #212121; margin-bottom: 6px; }
        .form-card .subtitle { color: #757575; font-size: .875rem; margin-bottom: 36px; }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: .8rem; font-weight: 600;
            color: #424242; margin-bottom: 6px; letter-spacing: .3px;
        }
        .input-wrap { position: relative; }
        .input-wrap .material-icons {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: #9E9E9E; font-size: 20px; pointer-events: none;
        }
        .input-wrap input {
            width: 100%; padding: 11px 14px 11px 40px;
            border: 1.5px solid #E0E0E0; border-radius: 8px;
            font-size: .9rem; font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
            outline: none; background: #FAFAFA;
        }
        .input-wrap input:focus {
            border-color: #1565C0;
            box-shadow: 0 0 0 3px rgba(21,101,192,.12);
            background: #fff;
        }

        .remember-row {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px; font-size: .82rem;
        }
        .remember-row label { color: #616161; font-weight: 400; cursor: pointer; }
        .remember-row input[type=checkbox] { accent-color: #1565C0; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #1565C0, #1E88E5);
            color: #fff; border: none; border-radius: 8px;
            font-size: .95rem; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: opacity .2s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { opacity: .92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }

        .divider { text-align: center; margin: 24px 0; position: relative; }
        .divider::before {
            content: ''; position: absolute; top: 50%; left: 0; right: 0;
            height: 1px; background: #EEEEEE;
        }
        .divider span {
            position: relative; background: #fff;
            padding: 0 12px; color: #9E9E9E; font-size: .8rem;
        }

        .register-link {
            text-align: center; font-size: .875rem; color: #616161;
        }
        .register-link a {
            color: #1565C0; font-weight: 600; text-decoration: none;
        }
        .register-link a:hover { text-decoration: underline; }

        .error-box {
            background: #FFEBEE; border-left: 4px solid #E53935;
            border-radius: 6px; padding: 10px 14px;
            color: #C62828; font-size: .83rem; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .error-box .material-icons { font-size: 18px; flex-shrink: 0; }

        /* Responsive */
        @media (max-width: 768px) {
            .left-panel { display: none; }
            .right-panel { padding: 32px 20px; }
        }
    </style>
</head>
<body>

    <!-- Panel izquierdo -->
    <div class="left-panel">
        <div class="brand-logo">
            <div class="icon-wrap"><span class="material-icons">sensors</span></div>
            <span>SoundGuard</span>
        </div>

        <h1>Monitoreo de Ruido<br>Ambiental IoT</h1>
        <p>Sistema de vigilancia en tiempo real para la prevención de enfermedades auditivas en trabajadores de Trujillo.</p>

        <ul class="feature-list">
            <li><span class="material-icons">graphic_eq</span> Monitoreo en tiempo real con sensores ESP32</li>
            <li><span class="material-icons">notifications_active</span> Alertas automáticas al superar 85 dB</li>
            <li><span class="material-icons">assessment</span> Reportes diarios, semanales y mensuales</li>
            <li><span class="material-icons">health_and_safety</span> Prevención de enfermedades auditivas</li>
        </ul>

        <a href="{{ route('about') }}" class="about-link">
            <span class="material-icons">info</span>
            Conocer más sobre el sistema
        </a>
    </div>

    <!-- Panel derecho -->
    <div class="right-panel">
        <div class="form-card">
            <h2>Bienvenido</h2>
            <p class="subtitle">Ingresa tus credenciales para acceder al sistema</p>

            @if ($errors->any())
            <div class="error-box">
                <span class="material-icons">error_outline</span>
                {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <div class="input-wrap">
                        <span class="material-icons">email</span>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <span class="material-icons">lock</span>
                        <input type="password" name="password" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="remember-row">
                    <label>
                        <input type="checkbox" name="remember"> &nbsp;Recordarme
                    </label>
                </div>

                <button type="submit" class="btn-login">
                    <span class="material-icons" style="font-size:20px">login</span>
                    Iniciar sesión
                </button>
            </form>

            <div class="divider"><span>¿No tienes cuenta?</span></div>

            <div class="register-link">
                <a href="{{ route('register') }}">Crear una cuenta nueva</a>
            </div>
        </div>
    </div>

</body>
</html>
