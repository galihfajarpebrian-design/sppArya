<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Login - SPP Online</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .title-header {
            color: #ffffff;
            margin-bottom: 25px;
            font-size: 28px;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .title-header span {
            color: #38bdf8;
            font-weight: 800;
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 380px;
            padding: 35px 30px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        .profile-wrapper {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-wrapper img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            background: #f1f5f9;
            padding: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .login-card h2 {
            text-align: center;
            color: #1e293b;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        .alert {
            background-color: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group input[type="text"],
        .input-group input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            background-color: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            color: #334155;
            outline: none;
            transition: all 0.2s ease-in-out;
        }

        .input-group input[type="text"]:focus,
        .input-group input[type="password"]:focus {
            border-color: #38bdf8;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
            transition: background-color 0.2s ease-in-out;
        }

        .btn-submit:hover {
            background-color: #1d4ed8;
        }

        /* Styling Tambahan untuk Link CEK PEMBAYARAN */
        .cek-pembayaran-wrapper {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }

        .btn-cek-pembayaran {
            color: #0284c7;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s ease-in-out;
        }

        .btn-cek-pembayaran:hover {
            color: #0369a1;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1 class="title-header"><span>SPP</span> ONLINE</h1>

    <div class="login-card">
        <form action="login_validasi.php" method="post">
            
            <div class="profile-wrapper">
                <img src="profile.png" alt="Profile">
            </div>

            <h2>LOGIN</h2>

            <?php
            if(isset($_GET['pesan'])){
                if($_GET['pesan']=="gagal"){
                    echo "<div class='alert'>Username dan Password tidak sesuai !</div>";
                }
            }
            ?>

            <div class="input-group">
                <input name="username" type="text" placeholder="Username" required autocomplete="off">
            </div>

            <div class="input-group">
                <input name="password" type="password" placeholder="Password" required>
            </div>

            <button name="login" type="submit" class="btn-submit">LOGIN</button>

        </form>

        <!-- Tambahan Link Cek Pembayaran -->
        <div class="cek-pembayaran-wrapper">
            <a href="bayar.php" class="btn-cek-pembayaran">CEK PEMBAYARAN</a>
        </div>
    </div>

</body>
</html>