<!DOCTYPE html>
<html>
<head>
    <title>Bienvenue</title>
</head>
<body>
    <h1>Bienvenue, {{ $user->name }}!</h1>
    <p>Merci de vous être inscrit sur notre plateforme.</p>
    
    @if($user->isLocataire())
        <p>En tant que locataire, vous pouvez maintenant rechercher des logements.</p>
    @elseif($user->isBailleur())
        <p>En tant que bailleur, vous pouvez maintenant publier vos annonces.</p>
    @endif
    
    <p>Nous sommes ravis de vous compter parmi nous.</p>
</body>
</html>
