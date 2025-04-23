<!DOCTYPE html>
<html>
<head>
    <title>Réinitialisation du mot de passe</title>
</head>
<body>
    <h1>Réinitialisation du mot de passe</h1>
    <p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe :</p>
    <a href="{{ $url }}">Réinitialiser mon mot de passe</a>
    <p>Ce lien expirera dans {{ config('auth.passwords.users.expire') }} minutes.</p>
    <p>Si vous n'avez pas demandé de réinitialisation, ignorez cet email.</p>
</body>
</html>
