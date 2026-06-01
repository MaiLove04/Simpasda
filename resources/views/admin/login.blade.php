<!DOCTYPE html>
<html>
<head>
    <title>ASRI Admin</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f5f7fa;
            font-family: Arial, sans-serif;
        }

        .card {
            width: 350px;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,.1);
        }

        h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }

        /* Pesan error bergaya alert */
        .error-message {
            background: #fde8e8;
            color: #e74c3c;
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }

        /* Container khusus untuk password field */
        .password-container {
            position: relative;
            width: 100%;
        }

        /* Memberikan space di kanan input agar teks tidak tertutup tombol */
        .password-container input {
            padding-right: 45px; 
        }

        /* Tombol toggle mata */
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 15px;
            background: none;
            border: none;
            color: #777;
            cursor: pointer;
            padding: 0;
            font-size: 14px;
            width: auto; /* Mencegah tombol melar penuh */
        }

        .toggle-password:hover {
            color: #333;
        }

        /* Tombol login utama */
        .btn-login {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #27ae60;
            color: white;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        .btn-login:hover {
            background: #219653;
        }
    </style>
</head>
<body>

<div class="card">

    <h2>Login Admin</h2>

    @if(session('error'))
        <div class="error-message">
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf

        <input type="email" name="email" placeholder="Email" required>

        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required>
            <button type="button" id="toggleBtn" class="toggle-password" onclick="togglePasswordVisibility()">Lihat</button>
        </div>

        <button type="submit" class="btn-login">Login</button>
    </form>

</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('toggleBtn');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleButton.textContent = 'Sembunyi';
        } else {
            passwordInput.type = 'password';
            toggleButton.textContent = 'Lihat';
        }
    }
</script>

</body>
</html>