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
    'failed_to_send_email' => 'E-Mail konnte nicht gesendet werden',
    // 'password_has_been_changed' => 'The Password Has Been Changed',
    'otp' => 'OTP',
    'otp_sent' => 'OTP gesendet',
    'email' => 'E-Mail',
    'failed' => 'Diese Anmeldedaten stimmen nicht mit unseren Aufzeichnungen überein.',
    'password' => 'Das eingegebene Passwort ist falsch.',
    'throttle' => 'Zu viele Anmeldeversuche. Bitte versuche es in :seconds Sekunden erneut.',
    'confirm_password' => 'Passwort bestätigen',
    'forgot' => 'Vergessen?',
    'forgot_password' => 'Passwort vergessen',
    'send_reset_password_link' => 'Link zum Zurücksetzen des Passworts senden',
    'reset_password' => 'Passwort zurücksetzen',
    'reset_Password_message' => 'Willkommen bei ' . config('app.name') . ', bitte erstelle dein Passwort',
    'login_page_message' => 'Willkommen bei ' . config('app.name') . ', bitte melde dich mit deinen persönlichen Zugangsdaten an.',
    'sign_in' => 'Anmelden',
    'verify_page_message' => 'Bestätige deine E-Mail-Adresse',
    'sent_verification_link_msg' => 'Ein neuer Bestätigungslink wurde an deine E-Mail-Adresse gesendet.',
    'check_verification_link_msg' => 'Bitte überprüfe deine E-Mails auf den Bestätigungslink, bevor du fortfährst.',
    'not_recieved__email_msg' => 'Falls du keine E-Mail erhalten hast',
    'request_another_code' => 'klicke hier, um einen neuen Code anzufordern',
    'sent_verification_code_msg' => 'Wir haben den Bestätigungscode an die registrierten Daten gesendet!',
    'invalid_email_phone_or_token' => 'Ungültige E-Mail, Telefonnummer oder Token. Bitte überprüfe die Angaben und versuche es erneut.',
    'password_has_been_changed' => 'Dein Passwort wurde erfolgreich geändert!',
    'invalid_otp_or_email' => 'Ungültige OTP oder E-Mail-Adresse',
    'invalid_otp_or_phone' => 'Ungültige OTP oder Telefonnummer',
    'user_not_exists' => 'Dieser Benutzer existiert nicht oder ist deaktiviert',
    'user_inactive' => 'Dieser Benutzer ist inaktiv',
    'user_deleted' => 'Benutzer erfolgreich gelöscht',

    // new keys
    'password_confirmation' => 'Passwortbestätigung',
    'phone' => 'Telefon',
    'token_invalid' => 'Das ausgewählte Zugriffstoken ist ungültig',
    'logged_out' => 'Erfolgreich abgemeldet.',
    'user_deactivated' => 'Dieser Benutzer ist deaktiviert',
];
