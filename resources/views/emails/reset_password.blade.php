<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Réinitialisation du mot de passe - Locato</title>
    <!--[if mso]>
    <style type="text/css">
    body, table, td {font-family: 'Inter', Arial, Helvetica, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: 'Inter', Arial, sans-serif; background-color: #f9f9f9; color: #333333;">
    <!-- Wrapper principal pour la largeur maximale -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 20px 0;">
                <!-- Container principal -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <!-- En-tête avec logo et slogan -->
                    <tr>
                    <td align="center" style="padding: 30px 0; border-bottom: 3px solid #e0252c;">
                            <img src="https://i.imgur.com/XoaP9kz.png" alt="Logo Locato" width="60" height="65" style="display: block; margin: 0 auto;" />
                            
                            <div style="font-family: 'Inter', Arial, sans-serif; color: #e0252c; font-weight: bold; font-size: 20px; margin-top: 15px;">Trouvez vite, louez mieux</div>
                            <div style="font-family: 'Inter', Arial, sans-serif; color: #555555; font-size: 14px; margin-top: 5px;">Votre maison idéale n'est qu'à quelques clics</div>
                            <div style="background-color: #F1C40F; width: 50px; height: 5px; margin: 15px auto;"></div>
                        </td>
                    </tr>
                    
                    <!-- Contenu principal -->
                    <tr>
                        <td style="padding: 30px; border-left: 5px solid #e0252c;">
                            <h1 style="font-family: 'Inter', Arial, sans-serif; color: #e0252c; text-align: center; margin-top: 0; margin-bottom: 25px; font-weight: bold; font-size: 26px;">Réinitialisation du mot de passe</h1>
                            
                            <p style="margin-bottom: 20px; font-family: 'Inter', Arial, sans-serif; line-height: 1.6;">Bonjour,</p>
                            <p style="margin-bottom: 20px; font-family: 'Inter', Arial, sans-serif; line-height: 1.6;">Nous avons reçu une demande de réinitialisation de mot de passe pour votre compte Locato. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
                            
                            <!-- Bouton de réinitialisation -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" style="background-color: #e0252c; color: #ffffff; text-decoration: none; padding: 15px 30px; border-radius: 4px; font-weight: bold; font-family: 'Inter', Arial, sans-serif; display: inline-block; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">Réinitialiser mon mot de passe</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin-bottom: 10px; font-family: 'Inter', Arial, sans-serif; line-height: 1.6;">Ce lien expirera dans <span style="background-color: #f5ecd7; padding: 2px 5px; border-bottom: 2px solid #F1C40F; font-weight: bold;">{{ config('auth.passwords.users.expire') }} minutes</span>.</p>
                            
                            <p style="margin-bottom: 10px; font-family: 'Inter', Arial, sans-serif; line-height: 1.6;">Si vous ne pouvez pas cliquer sur le bouton, copiez et collez ce lien dans votre navigateur :</p>
                            
                            <div style="background-color: #f8f8f8; padding: 12px; margin: 15px 0; word-break: break-all; border-left: 3px solid #dddddd; font-size: 14px; font-family: 'Inter', Arial, sans-serif;">
                                {{ $url }}
                            </div>
                            
                            <!-- Note importante -->
                            <div style="font-size: 14px; color: #555555; margin-top: 30px; padding: 15px; background-color: #f8f8f8; border-left: 3px solid #dddddd; font-family: 'Inter', Arial, sans-serif;">
                                <p style="margin: 0;"><strong>Important :</strong> Si vous n'avez pas demandé de réinitialisation, ignorez cet email et contactez notre équipe de sécurité via <a href="mailto:security@locato.com" style="color: #e0252c; text-decoration: none; font-weight: bold;">security@locato.com</a>.</p>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Pied de page -->
                    <tr>
                        <td style="padding: 20px; background-color: #f9f9f9; text-align: center; border-top: 1px solid #e0e0e0; color: #777777; font-size: 13px; font-family: 'Inter', Arial, sans-serif;">
                            <p style="margin-bottom: 10px;"><strong>Locato</strong> - Votre partenaire immobilier professionnel</p>
                            <p style="margin-bottom: 10px;"><a href="mailto:contact@locato.com" style="color: #e0252c; text-decoration: none; font-weight: bold;">contact@locato.com</a> | <a href="https://www.locato.com" style="color: #e0252c; text-decoration: none; font-weight: bold;">www.locato.com</a></p>
                            <p style="margin-bottom: 10px;">Trouve ta maison parmi les millions de maisons qui sont sur la plateforme.</p>
                            <table border="0" cellpadding="0" cellspacing="0" width="180" style="margin: 15px auto; border-collapse: collapse;">
                                <tr>
                                    <td width="40" align="center">
                                        <a href="#" style="display: inline-block; width: 32px; height: 32px; background-color: #e0252c; border-radius: 50%; text-align: center; line-height: 32px; color: white; text-decoration: none; font-weight: bold;">FB</a>
                                    </td>
                                    <td width="40" align="center">
                                        <a href="#" style="display: inline-block; width: 32px; height: 32px; background-color: #e0252c; border-radius: 50%; text-align: center; line-height: 32px; color: white; text-decoration: none; font-weight: bold;">IN</a>
                                    </td>
                                    <td width="40" align="center">
                                        <a href="#" style="display: inline-block; width: 32px; height: 32px; background-color: #e0252c; border-radius: 50%; text-align: center; line-height: 32px; color: white; text-decoration: none; font-weight: bold;">TW</a>
                                    </td>
                                    <td width="40" align="center">
                                        <a href="#" style="display: inline-block; width: 32px; height: 32px; background-color: #e0252c; border-radius: 50%; text-align: center; line-height: 32px; color: white; text-decoration: none; font-weight: bold;">IG</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin: 0;">© 2025 Locato. Tous droits réservés.</p>
                        </td>
                    </tr>
                </table>
                
                <!-- Note de désabonnement -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse;">
                    <tr>
                        <td align="center" style="padding: 15px 0; font-size: 12px; color: #999999; font-family: 'Inter', Arial, sans-serif;">
                            <p style="margin: 0;">Cet email vous a été envoyé car vous êtes inscrit sur Locato. <a href="#" style="color: #999999;">Gérer mes préférences d'email</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
