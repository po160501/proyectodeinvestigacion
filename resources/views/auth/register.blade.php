<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta — SoundGuard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            background: #F0F2F5;
        }

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
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            top: -100px;
            right: -100px;
        }

        .left-panel::after {
            content: '';
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            bottom: -60px;
            left: -60px;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 48px;
        }

        .brand-logo .icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .brand-logo .icon-wrap .material-icons {
            color: #fff;
            font-size: 28px;
        }

        .brand-logo span {
            color: #fff;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .left-panel h1 {
            color: #fff;
            font-size: 1.9rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 16px;
        }

        .left-panel p {
            color: rgba(255, 255, 255, .75);
            font-size: .9rem;
            line-height: 1.7;
            margin-bottom: 36px;
        }

        .steps {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .step {
            display: flex;
            align-items: flex-start;
            gap: 14px;
        }

        .step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .2);
            color: #fff;
            font-size: .8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .step-text {
            color: rgba(255, 255, 255, .85);
            font-size: .85rem;
            line-height: 1.5;
        }

        .step-text strong {
            color: #fff;
            display: block;
            margin-bottom: 2px;
        }

        .right-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
        }

        .form-card {
            width: 100%;
            max-width: 420px;
        }

        .form-card h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #212121;
            margin-bottom: 6px;
        }

        .form-card .subtitle {
            color: #757575;
            font-size: .875rem;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #424242;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .material-icons {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #9E9E9E;
            font-size: 20px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 11px 14px 11px 40px;
            border: 1.5px solid #E0E0E0;
            border-radius: 8px;
            font-size: .9rem;
            font-family: 'Inter', sans-serif;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
            background: #FAFAFA;
        }

        .input-wrap input:focus {
            border-color: #1565C0;
            box-shadow: 0 0 0 3px rgba(21, 101, 192, .12);
            background: #fff;
        }

        .input-wrap input.is-invalid {
            border-color: #E53935;
        }

        .invalid-msg {
            color: #C62828;
            font-size: .78rem;
            margin-top: 4px;
        }

        .rol-note {
            background: #E3F2FD;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: .8rem;
            color: #1565C0;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .rol-note .material-icons {
            font-size: 18px;
        }

        .btn-register {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1565C0, #1E88E5);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: .95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-register:hover {
            opacity: .92;
            transform: translateY(-1px);
        }

        .login-link {
            text-align: center;
            font-size: .875rem;
            color: #616161;
            margin-top: 20px;
        }

        .login-link a {
            color: #1565C0;
            font-weight: 600;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        .error-box {
            background: #FFEBEE;
            border-left: 4px solid #E53935;
            border-radius: 6px;
            padding: 10px 14px;
            color: #C62828;
            font-size: .83rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-box .material-icons {
            font-size: 18px;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            .left-panel {
                display: none;
            }

            .right-panel {
                padding: 32px 20px;
            }
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

        <h1>Únete al sistema<br>de monitoreo</h1>
        <p>Crea tu cuenta y comienza a monitorear los niveles de ruido en tu área laboral.</p>

        <div class="steps">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-text">
                    <strong>Crea tu cuenta</strong>
                    Regístrate con tu correo institucional
                </div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-text">
                    <strong>Accede al dashboard</strong>
                    Visualiza métricas y gráficos en tiempo real
                </div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-text">
                    <strong>Monitorea y reporta</strong>
                    Genera reportes y gestiona alertas de ruido
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho -->
    <div class="right-panel">
        <div class="form-card">
            <h2>Crear cuenta</h2>
            <p class="subtitle">Completa el formulario para registrarte</p>

            @if ($errors->any())
                <div class="error-box">
                    <span class="material-icons">error_outline</span>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="form-group">
                    <label>Nombre completo</label>
                    <div class="input-wrap">
                        <span class="material-icons">person</span>
                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="Tu nombre completo" class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                            required>
                    </div>
                    @error('name')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Correo electrónico</label>
                    <div class="input-wrap">
                        <span class="material-icons">email</span>
                        <input type="email" name="email" value="{{ old('email') }}"
                            placeholder="correo@ejemplo.com" class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                            required>
                    </div>
                    @error('email')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Contraseña</label>
                    <div class="input-wrap">
                        <span class="material-icons">lock</span>
                        <input type="password" name="password" placeholder="Mínimo 6 caracteres"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}" required>
                    </div>
                    @error('password')
                        <div class="invalid-msg">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Confirmar contraseña</label>
                    <div class="input-wrap">
                        <span class="material-icons">lock_outline</span>
                        <input type="password" name="password_confirmation" placeholder="Repite tu contraseña" required>
                    </div>
                </div>

                <div class="rol-note">
                    <span class="material-icons">info</span>
                    Tu cuenta se creará con rol <strong>Visualizador</strong>. Un administrador puede cambiar tu rol.
                </div>

                <button type="submit" class="btn-register">
                    <span class="material-icons" style="font-size:20px">how_to_reg</span>
                    Crear cuenta
                </button>
            </form>

            <div class="login-link">
                ¿Ya tienes cuenta? <a href="{{ route('login') }}">Iniciar sesión</a>
            </div>
        </div>
    </div>

</body>

</html>
