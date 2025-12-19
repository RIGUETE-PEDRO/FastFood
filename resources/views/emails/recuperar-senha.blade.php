<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        p {
            color: #666;
            line-height: 1.6;
            font-size: 16px;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #39c259;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #18a737;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Recuperação de Senha</h1>

        <p>Olá!</p>

        <p>Você está recebendo este e-mail porque recebemos uma solicitação de recuperação de senha para sua conta.</p>

        <div class="center">
            <a href="{{ $url }}" class="button">Redefinir Senha</a>
        </div>

        <p>Este link de recuperação expirará em 60 minutos.</p>

        <p>Se você não solicitou a recuperação de senha, nenhuma ação adicional é necessária.</p>

        <p>Atenciosamente,<br>Equipe FlashFood</p>

        <div class="footer">
            <p>Se você está tendo problemas para clicar no botão "Redefinir Senha", copie e cole a URL abaixo em seu navegador:</p>
            <p>{{ $url }}</p>
        </div>
    </div>
</body>
</html>
