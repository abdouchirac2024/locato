<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Nouveau Logement Ajouté (Admin) - {{ config('app.name') }}</title>
    <!--[if mso]>
    <style type="text/css">
    body, table, td, h1, p {font-family: 'Inter', Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', Arial, sans-serif; background-color: #f9f9f9; color: #333333;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- En-tête -->
                    <tr>
                        <td align="center" style="padding: 30px 0; border-bottom: 3px solid #e0252c;">
                            <a href="{{ config('app.url') }}" target="_blank">
                                <img src="https://i.imgur.com/XoaP9kz.png" alt="{{ config('app.name') }} Logo" width="60" height="65" style="display: block; margin: 0 auto;" />
                            </a>
                            <div style="font-family: 'Inter', Arial, sans-serif; color: #e0252c; font-weight: bold; font-size: 20px; margin-top: 15px;">Trouvez vite, louez mieux</div>
                            <div style="font-family: 'Inter', Arial, sans-serif; color: #555555; font-size: 14px; margin-top: 5px;">Votre maison idéale n'est qu'à quelques clics</div>
                            <div style="background-color: #F1C40F; width: 50px; height: 5px; margin: 15px auto;"></div>
                        </td>
                    </tr>

                    <!-- Contenu -->
                    <tr>
                        <td style="padding: 40px 30px; border-left: 5px solid #e0252c;">
                            <h1 style="font-family: 'Inter', Arial, sans-serif; color: #e0252c; margin-top: 0; margin-bottom: 25px; font-weight: bold; font-size: 28px;">Nouveau Logement Ajouté à Votre Compte</h1>

                            <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">Bonjour {{ $logement->bailleur?->user?->name ?? 'Bailleur' }},</p>
                            <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">Bonne nouvelle ! L'équipe d'administration de {{ config('app.name') }} a créé et approuvé le logement suivant, qui est maintenant associé à votre compte :</p>

                            <div style="margin: 25px 0; padding: 20px; background-color: #f5ecd7; border-left: 4px solid #F1C40F;">
                                <p style="margin-top:0; font-size: 18px; font-weight: bold; color: #333333;">Détails du Logement :</p>
                                <p style="margin: 5px 0; font-size: 15px;"><strong>Référence :</strong> {{ $logement->reference ?? 'N/A' }}</p>
                                <p style="margin: 5px 0; font-size: 15px;"><strong>Libellé :</strong> {{ $logement->libelle_display ?? ($logement->libelle ?? $logement->descrip_fr) }}</p>
                                <p style="margin: 5px 0; font-size: 15px;"><strong>Prix :</strong> {{ number_format($logement->prix, 0, ',', ' ') }} FCFA</p>
                                <p style="margin: 5px 0; font-size: 15px;"><strong>Quartier :</strong> {{ $logement->quartier?->nomQuartier ?? 'N/A' }}</p>
                            </div>

                            <p style="font-size: 16px; line-height: 1.6;">Ce logement est maintenant <strong>actif et visible</strong> sur la plateforme. Vous pouvez le consulter et le gérer depuis votre espace bailleur.</p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        {{-- Adaptez l'URL pour votre front-end --}}
                                        <a href="{{ $logementUrl }}" style="display: inline-block; background-color: #e0252c; color: white; padding: 14px 30px; text-decoration: none; font-weight: bold; border-radius: 4px;">Voir le Logement</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="padding: 20px; background-color: #f9f9f9; text-align: center; border-top: 1px solid #e0e0e0; color: #777777; font-size: 13px;">
                            <p style="margin-bottom: 10px;"><strong>{{ config('app.name') }}</strong> - Votre partenaire immobilier</p>
                            <p style="margin-bottom: 10px;"><a href="mailto:contact@locato.com" style="color: #e0252c; text-decoration: none; font-weight: bold;">contact@locato.com</a> | <a href="{{ config('app.url') }}" style="color: #e0252c; text-decoration: none; font-weight: bold;">www.locato.com</a></p>
                            <p style="margin-bottom: 10px;">Trouvez vite, louez mieux.</p>
                            <p style="margin: 0;">© {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
