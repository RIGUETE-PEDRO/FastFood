<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700;800&display=swap" rel="stylesheet">
    <title>Recuperar Senha - FlashFood</title>
    <style>
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #faf7f2;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .wrapper {
            width: 100%;
            background-color: #faf7f2;
            padding: 40px 20px;
        }

        .container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid rgba(249, 115, 22, 0.16);
            overflow: hidden;
            box-shadow: 0 18px 44px rgba(63, 52, 45, 0.10);
        }

        .header {
            background: linear-gradient(180deg, #3f342d, #211d1a);
            padding: 24px;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            color: #F6D036;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .header h2 span {
            color: #f97316;
        }

        .content {
            padding: 40px 30px;
            color: #667085;
        }

        h1 {
            color: #101828;
            font-size: 24px;
            margin-bottom: 16px;
            text-align: center;
            font-weight: 800;
        }

        p {
            line-height: 1.6;
            font-size: 16px;
            margin: 0 0 16px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        .button-primary {
            display: inline-block;
            padding: 14px 36px;
            background-color: #f97316;
            /* Fallback for browsers that don't support gradients */
            background: linear-gradient(135deg, #f97316, #c2410c);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }

        .footer {
            background-color: #ffffff;
            padding: 25px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #d9e1ec;
        }

        .url-box {
            background-color: #ffffff;
            border: 1px dashed #d9e1ec;
            padding: 12px;
            margin-top: 12px;
            word-break: break-all;
            color: #c2410c;
            border-radius: 6px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2>Flash<span>Food</span></h2>
            </div>

            <div class="content">
                <h1>Recuperar Senha</h1>
                <p>Olá!</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>FlashFood</strong>. Clique no botão abaixo para criar uma nova senha:</p>

                <div class="button-container">
                    <a href="{{ $url }}" class="button-primary">Redefinir Senha</a>
                </div>

                <p>Se você não solicitou esta alteração, pode ignorar este e-mail com segurança. Este link de redefinição é válido por 60 minutos.</p>

                <p>Atenciosamente,<br>Equipe FlashFood</p>
            </div>

            <div class="footer">
                <p>Se estiver com problemas para clicar no botão "Redefinir Senha", copie e cole a URL abaixo em seu navegador:</p>
                <div class="url-box">{{ $url }}</div>
            </div>
        </div>
    </div>
</body>

</html>
