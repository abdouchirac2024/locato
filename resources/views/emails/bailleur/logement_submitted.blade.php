<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Logement Soumis pour Validation - {{ config('app.name') }}</title>
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
                                {{-- REMPLACEZ PAR L'URL DE VOTRE LOGO --}}
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
                            <h1 style="font-family: 'Inter', Arial, sans-serif; color: #e0252c; margin-top: 0; margin-bottom: 25px; font-weight: bold; font-size: 28px;">Votre Logement est en Cours d'Examen</h1>

                            <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">Bonjour {{ $logement->bailleur?->user?->name ?? 'Bailleur' }},</p>
                            <p style="margin-bottom: 20px; font-size: 16px; line-height: 1.6;">Nous avons bien reçu la soumission de votre logement "<strong>{{ $logement->libelle_display ?? ($logement->libelle ?? $logement->reference) }}</strong>".</p>

                            <div style="margin: 25px 0; padding: 20px; background-color: #f5ecd7; border-left: 4px solid #F1C40F;">
                                <p style="margin:0; font-size: 16px;">Notre équipe d'administration est actuellement en train d'examiner les détails de votre propriété. Ce processus nous permet de garantir la qualité et la conformité des annonces sur {{ config('app.name') }}.</p>
                                <p style="margin-top:10px; font-size: 16px;">Vous recevrez une notification par email dès que le statut de votre logement sera mis à jour (approuvé ou si des informations supplémentaires sont nécessaires).</p>
                            </div>

                            <p style="font-size: 16px; line-height: 1.6;">En attendant, vous pouvez consulter les détails de votre soumission via le lien ci-dessous :</p>

                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        {{-- Adaptez l'URL pour votre front-end --}}
                                        <a href="{{ $logementUrl }}" style="display: inline-block; background-color: #e0252c; color: white; padding: 14px 30px; text-decoration: none; font-weight: bold; border-radius: 4px;">Voir Mon Logement Soumis</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="font-size: 16px; line-height: 1.6;">Merci pour votre patience et votre confiance.</p>
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
