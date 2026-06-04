<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - SIMPASDA</title>

    <!-- Bootstrap Icons & Google Fonts Premium -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --primary-green: #16a34a;
            --primary-hover: #15803d;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: var(--bg-gradient);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
            padding: 20px;
        }

        /* 🏛️ CARD PLATINUM DESIGN */
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            padding: 40px 32px;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        /* LOGO & HEADLINE BRANDING SIMPASDA */
        .brand-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            background: rgba(22, 163, 74, 0.1);
            color: var(--primary-green);
            border-radius: 14px;
            font-size: 28px;
            margin-bottom: 16px;
        }

        .brand-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: var(--text-dark);
            letter-spacing: -0.5px;
        }

        .brand-header p {
            margin: 6px 0 0 0;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 500;
        }

        /* ALERT ERROR MINIMALIS */
        .error-alert {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 500;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
        }

        .error-alert i {
            font-size: 16px;
            margin-right: 10px;
            flex-shrink: 0;
        }

        /* INPUT FIELD STYLING */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            width: 100%;
        }

        .input-wrapper i.input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 16px;
        }

        input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            font-size: 14.5px;
            font-family: inherit;
            font-weight: 500;
            color: var(--text-dark);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        input::placeholder {
            color: #94a3b8;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-green);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(22, 163, 74, 0.15);
        }

        /* PASSWORD CONTAINER & TOGGLE EYE */
        .password-wrapper input {
            padding-right: 42px;
        }

        .toggle-password-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 8px 10px;
            font-size: 16px;
            display: flex;
            align-items: center;
            border-radius: 6px;
        }

        .toggle-password-btn:hover {
            color: var(--text-dark);
            background: rgba(0, 0, 0, 0.02);
        }

        /* BUTTON LOGIN UTAMA */
        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            background: var(--primary-green);
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.2);
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(22, 163, 74, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="login-card">

    <!-- Header Brand SIMPASDA -->
    <div class="brand-header">
        <div class="brand-icon">
            <i class="bi bi-recycle"></i>
        </div>
        <h2>SIMPASDA</h2>
        <p>Sistem Manajemen Bank Sampah Daerah</p>
    </div>

    <!-- Tampilan Error Laravel Session -->
    @if(session('error'))
        <div class="error-alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf

        <!-- Input Email -->
        <div class="form-group">
            <label for="email">Alamat Email Admin</label>
            <div class="input-wrapper">
                <i class="bi bi-envelope input-icon"></i>
                <input type="email" id="email" name="email" placeholder="nama@email.com" required autocomplete="email">
            </div>
        </div>

        <!-- Input Password -->
        <div class="form-group">
            <label for="password">Kata Sandi</label>
            <div class="input-wrapper password-wrapper">
                <i class="bi bi-lock input-icon"></i>
                <input type="password" id="password" name="password" placeholder="••••••••" required>
                <button type="button" id="toggleBtn" class="toggle-password-btn" onclick="togglePasswordVisibility()" aria-label="Tampilkan sandi">
                    <i id="eyeIcon" class="bi bi-eye"></i>
                </button>
            </div>
        </div>

        <!-- Tombol Kirim Form -->
        <button type="submit" class="btn-submit">Masuk ke Dashboard</button>
    </form>

</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const toggleButton = document.getElementById('toggleBtn');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.className = 'bi bi-eye-slash';
            toggleButton.setAttribute('aria-label', 'Sembunyikan sandi');
        } else {
            passwordInput.type = 'password';
            eyeIcon.className = 'bi bi-eye';
            toggleButton.setAttribute('aria-label', 'Tampilkan sandi');
        }
    }
</script>

</body>
</html>