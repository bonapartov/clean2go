<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */
    'failed_to_send_email' => '',
    // 'password_has_been_changed' => 'The Password Has Been Changed',
    'otp' => 'OTP',
    'otp_sent' => 'OTP envoyé',
    'email' => 'E-mail',
    'failed' => 'Ces identifiants ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',
    'confirm_password' => 'Confirmer le mot de passe',
    'forgot' => 'Mot de passe oublié ?',
    'forgot_password' => 'Mot de passe oublié',
    'send_reset_password_link' => 'Envoyer le lien de réinitialisation du mot de passe',
    'reset_password' => 'Réinitialiser le mot de passe',
    'reset_Password_message' => 'Bienvenue sur '. config('app.name') .', veuillez créer votre mot de passe',
    'login_page_message' => 'Bienvenue sur ' . config('app.name') . ', veuillez vous connecter avec vos informations personnelles de compte.',
    'sign_in' => 'Se connecter',
    'verify_page_message' => 'Vérifiez votre adresse e-mail',
    'sent_verification_link_msg' => 'Un nouveau lien de vérification a été envoyé à votre adresse e-mail.',
    'check_verification_link_msg' => 'Avant de continuer, veuillez vérifier votre e-mail pour un lien de vérification.',
    'not_recieved__email_msg' => 'Si vous n\'avez pas reçu l\'e-mail',
    'request_another_code' => 'cliquez ici pour en demander un autre',
    'sent_verification_code_msg' => 'Nous avons envoyé le code de vérification dans les détails enregistrés !',
    'invalid_email_phone_or_token' => 'E-mail, téléphone ou jeton invalide. Veuillez vérifier et réessayer.',
    'password_has_been_changed' => 'Votre mot de passe a été modifié !',
    'invalid_otp_or_email' => 'OTP ou adresse e-mail invalide',
    'invalid_otp_or_phone' => 'OTP ou téléphone invalide',
    'user_not_exists' => 'Cet utilisateur n\'existe pas ou est désactivé',
    'user_inactive' => 'Cet utilisateur est inactif',
    'user_deleted' => 'Utilisateur supprimé avec succès',

    // new keys
    'password_confirmation' => 'Confirmation du mot de passe',
    'phone' => 'Téléphone',
    'token_invalid' => 'Le jeton d\'accès sélectionné est invalide',
    'logged_out' => 'Déconnexion réussie.',
    'user_deactivated' => 'Cet utilisateur est désactivé',
];
