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
    // 'password_has_been_changed' => 'تم تغيير كلمة المرور',
    'otp' => 'OTP',
    'otp_sent' => 'OTP تم إرساله',
    'email' => 'البريد الإلكتروني',
    'failed' => 'هذه بيانات الاعتماد لا تطابق سجلاتنا.',
    'password' => 'كلمة المرور المقدمة غير صحيحة.',
    'throttle' => 'محاولات تسجيل دخول كثيرة جدًا. يرجى المحاولة مرة أخرى في :seconds ثواني.',
    'confirm_password' => 'تأكيد كلمة المرور',
    'forgot' => 'نسيت ؟',
    'forgot_password' => 'نسيت كلمة المرور',
    'send_reset_password_link' => 'إرسال رابط إعادة تعيين كلمة المرور',
    'reset_password' => 'إعادة تعيين كلمة المرور',
    'reset_Password_message' => 'مرحبًا إلى ' . config('app.name') . '، يرجى إنشاء كلمة المرور الخاصة بك',
    'login_page_message' => 'مرحبًا إلى ' . config('app.name') . '، يرجى تسجيل الدخول مع معلومات حسابك الشخصية.',
    'sign_in' => 'تسجيل الدخول',
    'verify_page_message' => 'تحقق عنوان بريدك الإلكتروني',
    'sent_verification_link_msg' => 'رابط تحقق جديد تم إرساله إلى عنوان بريدك الإلكتروني.',
    'check_verification_link_msg' => 'قبل المتابعة، يرجى التحقق بريدك الإلكتروني من أجل رابط تحقق.',
    'not_recieved__email_msg' => 'إذا أنت لم تستلم البريد الإلكتروني',
    'request_another_code' => 'انقر هنا لطلب آخر',
    'sent_verification_code_msg' => 'نحن أرسلنا رمز التحقق في تفاصيل مسجلة!',
    'invalid_email_phone_or_token' => 'بريد إلكتروني غير صالح، هاتف، أو رمز. يرجى التحقق والمحاولة مرة أخرى.',
    'password_has_been_changed' => 'كلمة المرور الخاصة بك تم تغييرها!',
    'invalid_otp_or_email' => 'OTP غير صالح أو بريد إلكتروني',
    'invalid_otp_or_phone' => 'OTP غير صالح أو هاتف',
    'user_not_exists' => 'هذا المستخدم لا يوجد أو تم تعطيله',
    'user_inactive' => 'هذا المستخدم هو غير نشط',
    'user_deleted' => 'المستخدم تم حذفه بنجاح',

    // new keys
    'password_confirmation' => 'تأكيد كلمة المرور',
    'phone' => 'هاتف',
    'token_invalid' => 'رمز الوصول المحدد غير صالح',
    'logged_out' => 'تم تسجيل الخروج بنجاح.',
    'user_deactivated' => 'هذا المستخدم تم تعطيله',
];