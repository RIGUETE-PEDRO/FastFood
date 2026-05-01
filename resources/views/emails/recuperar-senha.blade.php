<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - FlashFood</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            /* Gradiente inspirado no fundo da sua imagem */
            background: linear-gradient(135deg, #f27022 0%, #ed1c24 100%);
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        /* Header com a cor Bege/Creme da imagem */
        .header {
            background-color: #bca998;
            padding: 30px;
            text-align: center;
            border-bottom: 4px solid #f27022;
        }

        .header h2 {
            margin: 0;
            color: #F6D036;
            text-shadow:
                -2px -2px 0 black,
                2px -2px 0 black,
                -2px 2px 0 black,
                2px 2px 0 black,
                -3px 0px 0 black,
                3px 0px 0 black,
                0px -3px 0 black,
                0px 3px 0 black;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 800;
        }

        .header h2 span {
            color: #B52A2B;
            text-shadow:
                -2px -2px 0 black,
                2px -2px 0 black,
                -2px 2px 0 black,
                2px 2px 0 black,
                -3px 0px 0 black,
                3px 0px 0 black,
                0px -3px 0 black,
                0px 3px 0 black;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
        }

        .content {
            padding: 40px 30px;
            color: #4b5563;
        }

        h1 {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            line-height: 1.6;
            font-size: 16px;
        }

        .button-container {
            text-align: center;
            margin: 30px 0;
        }

        /* Verde idêntico ao botão da imagem */
        .button-green {
            display: inline-block;
            padding: 15px 40px;
            background-color: #3ecf5e;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 0 #2ca347;
        }

        .footer {
            background-color: #f9fafb;
            padding: 25px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
        }

        .url-box {
            background-color: #ffffff;
            border: 1px dashed #d1d5db;
            padding: 10px;
            margin-top: 10px;
            word-break: break-all;
            color: #f27022;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h2>FLASH<span>FOOD</span></h2>
            </div>

            <div class="content">
                <h1>Recuperar Senha</h1>
                <p>Olá!</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta no sistema <strong>FlashFood - Lanchonete Expresso</strong>.</p>

                <div class="button-container">
                    <a href="{{ $url }}" class="button-green">Redefinir Senha</a>
                </div>

                <p>Se você não solicitou esta alteração, ignore este e-mail. Este link é válido por 60 minutos.</p>

                <p><strong>Rapidez no Sabor!</strong><br>Equipe FlashFood</p>
            </div>

            <div class="footer">
                <p>Se estiver com problemas no botão, copie o link abaixo:</p>
                <div class="url-box">{{ $url }}</div>
            </div>
        </div>
    </div>
</body>

</html>
