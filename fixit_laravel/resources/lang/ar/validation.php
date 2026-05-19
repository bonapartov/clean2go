<?php

return [

    /*
    |--------------------------------------------------------------------------
    | سطور لغة التحقق
    |--------------------------------------------------------------------------
    |
    | تحتوي السطور اللغوية التالية على رسائل الخطأ الافتراضية المستخدمة من قبل
    | فئة المدقق. بعض هذه القواعد لها إصدارات متعددة مثل
    | قواعد الحجم. لا تتردد في تعديل كل من هذه الرسائل هنا.
    |
    */
    'referral_code_belongs_to_a_different_user_type' => 'رمز الإحالة ينتمي لنوع مستخدم مختلف.',
    'invalid_referral_code_or_referrer_not_found' => 'رمز إحالة غير صالح أو المُحيل غير موجود.',
    'document_already_exists' => 'المستند موجود بالفعل لهذا مقدم الخدمة. يرجى تحديثه بدلاً من ذلك.',
    'notification_not_found' => 'الإشعار غير موجود أو تم قراءته بالفعل',
    'at_least_two_services_required' => 'يجب أن تتضمن باقة الخدمة خدمتين على الأقل.',
    'notification_mark_as_read' => 'تم تمييز الإشعار كمقروء',
    'please_select_a_file_smaller_than' => 'يرجى اختيار ملف أصغر من :max',
    'additional_service_invalid_with_id' => 'الخدمة الإضافية المحددة بالمعرف :id غير صالحة.',
    'accepted' => 'يجب قبول :attribute.',
    'active_url' => ':attribute ليس عنوان URL صالحًا.',
    'after' => 'يجب أن يكون :attribute تاريخًا بعد :date.',
    'after_or_equal' => 'يجب أن يكون :attribute تاريخًا بعد أو يساوي :date.',
    'alpha' => 'يجب أن يحتوي :attribute على أحرف فقط.',
    'alpha_dash' => 'يجب أن يحتوي :attribute على أحرف وأرقام وشرطات وشرطات سفلية فقط.',
    'alpha_num' => 'يجب أن يحتوي :attribute على أحرف وأرقام فقط.',
    'array' => 'يجب أن يكون :attribute مصفوفة.',
    'before' => 'يجب أن يكون :attribute تاريخًا قبل :date.',
    'before_or_equal' => 'يجب أن يكون :attribute تاريخًا قبل أو يساوي :date.',
    'between' => [
        'numeric' => 'يجب أن يكون :attribute بين :min و :max.',
        'file' => 'يجب أن يكون :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون :attribute بين :min و :max حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على ما بين :min و :max عنصر.',
    ],
    'boolean' => 'يجب أن يكون حقل :attribute صحيحًا أو خاطئًا.',
    'confirmed' => 'تأكيد :attribute غير متطابق.',
    'current_password' => 'كلمة المرور غير صحيحة.',
    'date' => ':attribute ليس تاريخًا صالحًا.',
    'date_equals' => 'يجب أن يكون :attribute تاريخًا يساوي :date.',
    'date_format' => ':attribute لا يتطابق مع التنسيق :format.',
    'different' => 'يجب أن يكون :attribute و :other مختلفين.',
    'digits' => 'يجب أن يكون :attribute :digits أرقامًا.',
    'digits_between' => 'يجب أن يكون :attribute بين :min و :max أرقامًا.',
    'dimensions' => ':attribute له أبعاد صورة غير صالحة.',
    'distinct' => 'يحتوي حقل :attribute على قيمة مكررة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صالحًا.',
    'ends_with' => 'يجب أن ينتهي :attribute بواحد مما يلي: :values.',
    'exists' => ':attribute المحدد غير صالح.',
    'file' => 'يجب أن يكون :attribute ملفًا.',
    'filled' => 'يجب أن يحتوي حقل :attribute على قيمة.',
    'gt' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من :value.',
        'file' => 'يجب أن يكون :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أكبر من :value حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عنصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن يكون :attribute أكبر من أو يساوي :value.',
        'file' => 'يجب أن يكون :attribute أكبر من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أكبر من أو يساوي :value حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على :value عنصر أو أكثر.',
    ],
    'image' => 'يجب أن يكون :attribute صورة.',
    'in' => ':attribute المحدد غير صالح.',
    'in_array' => 'حقل :attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صالحًا.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صالحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صالحًا.',
    'json' => 'يجب أن يكون :attribute سلسلة JSON صالحة.',
    'lt' => [
        'numeric' => 'يجب أن يكون :attribute أقل من :value.',
        'file' => 'يجب أن يكون :attribute أقل من :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أقل من :value حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن يكون :attribute أقل من أو يساوي :value.',
        'file' => 'يجب أن يكون :attribute أقل من أو يساوي :value كيلوبايت.',
        'string' => 'يجب أن يكون :attribute أقل من أو يساوي :value حرفًا.',
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :value عنصر.',
    ],
    'max' => [
        'numeric' => 'يجب ألا يزيد :attribute عن :max.',
        'file' => 'يجب ألا يزيد :attribute عن :max كيلوبايت.',
        'string' => 'يجب ألا يزيد :attribute عن :max حرفًا.',
        'array' => 'يجب ألا يحتوي :attribute على أكثر من :max عنصر.',
    ],
    'mimes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'mimetypes' => 'يجب أن يكون :attribute ملفًا من النوع: :values.',
    'min' => [
        'numeric' => 'يجب أن يكون :attribute على الأقل :min.',
        'file' => 'يجب أن يكون :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون :attribute على الأقل :min حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على الأقل :min عنصر.',
    ],
    'multiple_of' => 'يجب أن يكون :attribute مضاعفًا لـ :value.',
    'not_in' => ':attribute المحدد غير صالح.',
    'not_regex' => 'تنسيق :attribute غير صالح.',
    'numeric' => 'يجب أن يكون :attribute رقمًا.',
    'password' => 'كلمة المرور غير صحيحة.',
    'present' => 'يجب أن يكون حقل :attribute حاضرًا.',
    'regex' => 'تنسيق :attribute غير صالح.',
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب عندما يكون :other هو :value.',
    'required_unless' => 'حقل :attribute مطلوب ما لم يكن :other في :values.',
    'required_with' => 'حقل :attribute مطلوب عندما تكون :values موجودة.',
    'required_with_all' => 'حقل :attribute مطلوب عندما تكون جميع :values موجودة.',
    'required_without' => 'حقل :attribute مطلوب عندما لا تكون :values موجودة.',
    'required_without_all' => 'حقل :attribute مطلوب عندما لا تكون أي من :values موجودة.',
    'prohibited' => 'حقل :attribute محظور.',
    'prohibited_if' => 'حقل :attribute محظور عندما يكون :other هو :value.',
    'prohibited_unless' => 'حقل :attribute محظور ما لم يكن :other في :values.',
    'same' => 'يجب أن يتطابق :attribute و :other.',
    'size' => [
        'numeric' => 'يجب أن يكون :attribute :size.',
        'file' => 'يجب أن يكون :attribute :size كيلوبايت.',
        'string' => 'يجب أن يكون :attribute :size حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على :size عنصر.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بواحد مما يلي: :values.',
    'string' => 'يجب أن يكون :attribute سلسلة نصية.',
    'timezone' => 'يجب أن يكون :attribute منطقة زمنية صالحة.',
    'unique' => ':attribute تم أخذه بالفعل.',
    'uploaded' => 'فشل تحميل :attribute.',
    'url' => 'تنسيق :attribute غير صالح.',
    'uuid' => 'يجب أن يكون :attribute UUID صالحًا.',

    'service_id_invalid' => 'معرف الخدمة المحدد غير صالح.',
    'coupon_code_not_found' => 'لم نتمكن من العثور على قسيمة :code',
    'service_ids_required' => 'مطلوب معرف خدمة واحد على الأقل.',

    'service_ids_array' => 'يجب تقديم معرفات الخدمات كمصفوفة.',
    'service_ids_exists' => 'يجب تقديم معرفات الخدمات كمصفوفة.',
    'is_multiple_serviceman_required' => 'حقل isMultipleServiceman مطلوب.',
    'is_multiple_serviceman_boolean' => 'يجب أن يكون حقل isMultipleServiceman منطقيًا (true/false).',
    'required_servicemen_required' => 'حقل required_servicemen مطلوب عندما يكون isMultipleServiceman صحيحًا.',
    'required_servicemen_integer' => 'يجب أن يكون حقل required_servicemen عددًا صحيحًا.',
    'select_serviceman_required' => 'حقل select_serviceman مطلوب.',
    'select_serviceman_in' => 'الخيار المحدد لـ select_serviceman غير صالح. يجب أن يكون إما "user_choice" أو "app_choose".',
    'select_date_time_required' => 'حقل select_date_time مطلوب.',
    'select_date_time_in' => 'الخيار المحدد لـ select_date_time غير صالح. يجب أن يكون إما "custom" أو "as_provider".',

    'providerId_exists' => 'مقدم الخدمة غير موجود.',
    'serviceId_exists' => 'الخدمة غير موجودة.',

    'user_is_not_provider' => 'المستخدم ليس مقدم خدمة.',
    'login_type_google_apple_or_phone' => 'تسجيل الدخول بالنوع يمكن أن يكون إما google أو apple',
    'address_ids_exists' => 'العنوان بالمعرف :value غير موجود.',

    'banner_images_required' => 'يرجى اختيار صورة واحدة على الأقل.',
    'banner_type_required' => 'يرجى اختيار نوع البانر.',
    'banner_related_id_required' => 'يرجى اختيار نوع فئة البانر.',
    'blog_categories_required' => 'حقل الفئات مطلوب',
    'zones_required' => 'حقل المناطق مطلوب',

    'commission_regex' => 'أدخل نسبة العمولة بين 0 إلى 99.99',
    'category_type' => 'نوع الفئة يمكن أن يكون إما مدونة أو خدمة',

    'user_id_required' => 'يرجى اختيار مقدم الخدمة.',
    'document_id_required' => 'يرجى اختيار المستند.',
    'identity_no_required' => 'رقم المستند مطلوب.',

    'service_id_required' => 'حقل الخدمات مطلوب',
    'start_end_date_required' => 'حقل تاريخ البدء والانتهاء مطلوب',
    'image_required' => 'مطلوب صورة واحدة على الأقل',

    'provider_id_required' => 'حقل مقدم الخدمة مطلوب',
    'service_id_required_if' => 'حقل الخدمات ذات الصلة مطلوب عندما تكون الخدمات ذات الصلة العشوائية معطلة.',
    'type' => 'يرجى اختيار نوع الخدمة',
    'price_required_if' => 'حقل السعر مطلوب',

    'type_in' => 'نوع الوسم يمكن أن يكون إما منشورًا أو منتجًا',
    'type_in_wallet_bonus' => 'نوع مكافأة المحفظة يمكن أن يكون إما ثابتًا أو نسبة مئوية',
    'amount_required' => 'المبلغ مطلوب للنوع الثابت.',
    'percentage_amount_required' => 'النسبة المئوية مطلوبة للنوع النسبة المئوية.',
    'amount_min' => 'يجب أن يكون المبلغ 1 على الأقل.',
    'percentage_min' => 'يجب أن تكون النسبة المئوية 1 على الأقل.',
    'percentage_max' => 'لا يمكن أن تتجاوز النسبة المئوية 100.',
    'min_top_up_required' => 'الحد الأدنى لمبلغ إعادة الشحن مطلوب.',
    'total_usage_limit' =>  'العدد الإجمالي لاستخدام المكافأة مطلوب.',
    'usage_limit_per_user' =>  'الحد الأقصى للاستخدام لكل مستخدم مطلوب.',
    'max_bonus_required' => 'الحد الأقصى لمبلغ المكافأة مطلوب.',
    'rate_regex' => 'حدد معدل الضريبة بين 0 و 99.99.',

    'provider_id_exists' => 'مقدم الخدمة المحدد غير صالح.',
    'gap_required' => 'حقل الفجوة مطلوب.',
    'gap_integer' => 'يجب أن تكون الفجوة عددًا صحيحًا.',
    'gap_min' => 'يجب أن تكون الفجوة 1 على الأقل.',
    'time_unit_required' => 'حقل وحدة الوقت مطلوب.',
    'time_unit_in' => 'وحدة الوقت المحددة غير صالحة.',
    'time_slots_required' => 'مطلوب فترة زمنية واحدة على الأقل.',
    'time_slots_day_required' => 'حقل اليوم مطلوب.',
    'time_slots_day_in' => 'اليوم المحدد غير صالح.',
    'time_slots_start_time_required' => 'حقل وقت البدء مطلوب.',
    'time_slots_start_time_date_format' => 'وقت البدء لا يتطابق مع التنسيق H:i.',
    'time_slots_end_time_required' => 'حقل وقت الانتهاء مطلوب.',
    'time_slots_end_time_date_format' => 'وقت الانتهاء لا يتطابق مع التنسيق H:i.',
    'time_slots_end_time_after' => 'يجب أن يكون وقت الانتهاء بعد وقت البدء.',

    'payment_type_in' => 'يجب أن يكون نوع الدفع PayPal أو بنك.',

    // مفاتيح جديدة
    'invalid_address_id' => 'معرف عنوان غير صالح',
    'user_not_exists' => 'المستخدم غير موجود أو معطل',

    'openai' => [
        'input_text_required' => 'يرجى إدخال النص.',
        'input_text_min'      => 'يجب أن يكون النص حرفين على الأقل.',

        'locale_required'     => 'رمز اللغة مطلوب.',
        'locale_size'         => 'يجب أن يكون رمز اللغة حرفين بالضبط.',

        'type_required'       => 'نوع المحتوى مطلوب.',
        'type_invalid'        => 'نوع محتوى غير صالح.',
    ],

    /*
    |--------------------------------------------------------------------------
    | سطور لغة تحقق مخصصة
    |--------------------------------------------------------------------------
    |
    | هنا يمكنك تحديد رسائل تحقق مخصصة للسمات باستخدام
    | الاصطلاح "attribute.rule" لتسمية الأسطر. هذا يجعل من السريع
    | تحديد سطر لغة مخصص محدد لقاعدة سمة معينة.
    |
    */

    'custom' => [
        // تحديث الملف الشخصي
        'name' => [
            'max' => 'لا يجوز أن يزيد الاسم عن :max حرفًا.',
        ],
        'email' => [
            'email' => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالحًا.',
            'unique' => 'البريد الإلكتروني تم أخذه بالفعل.',
        ],
        'phone' => [
            'required' => 'رقم الهاتف مطلوب.',
            'digits_between' => 'يجب أن يكون رقم الهاتف بين :min و :max رقمًا.',
            'unique' => 'رقم الهاتف تم أخذه بالفعل.',
        ],
        'code' => [
            'required' => 'الرمز مطلوب.',
        ],
        'role_id' => [
            'exists' => 'الدور المحدد غير صالح.',
        ],

        // إنشاء عنوان
        'country_id' => [
            'required' => 'حقل البلد مطلوب.',
            'exists' => 'البلد المحدد غير صالح.',
        ],
        'state_id' => [
            'required' => 'حقل الولاية مطلوب.',
            'exists' => 'الولاية المحددة غير صالحة.',
        ],
        'city' => [
            'required' => 'المدينة مطلوبة.',
            'string' => 'يجب أن تكون المدينة سلسلة نصية.',
        ],
        'address' => [
            'required' => 'العنوان مطلوب.',
        ],
        'latitude' => [
            'required' => 'خط العرض مطلوب.',
            'latitude_longitude' => 'يجب أن يكون خط العرض خط عرض صالحًا.',
        ],
        'longitude' => [
            'required' => 'خط الطول مطلوب.',
            'latitude_longitude' => 'يجب أن يكون خط الطول خط طول صالحًا.',
        ],
        'postal_code' => [
            'required' => 'الرمز البريدي مطلوب.',
        ],
        'alternative_phone' => [
            'required_if' => 'الهاتف البديل مطلوب عندما يكون نوع الدور خدمة.',
        ],
        'alternative_name' => [
            'required_if' => 'الاسم البديل مطلوب عندما يكون نوع الدور خدمة.',
        ],

        // تحديث العنوان
        'user_id' => [
            'nullable' => 'معرف المستخدم اختياري.',
            'exists' => 'معرف المستخدم المحدد غير صالح.',
        ],
        'type' => [
            'required' => 'النوع مطلوب.',
            'string' => 'يجب أن يكون النوع سلسلة نصية.',
        ],
        'unique_category_zone' => 'يجب أن يكون عنوان الفئة فريدًا داخل المناطق المحددة.',
    ],

    /*
    |--------------------------------------------------------------------------
    | سمات التحقق المخصصة
    |--------------------------------------------------------------------------
    |
    | تُستخدم الأسطر اللغوية التالية لتبديل العنصر النائب للسمة
    | بشيء أكثر سهولة للقراءة مثل "عنوان البريد الإلكتروني" بدلاً من
    | "email". هذا ببساطة يساعدنا على جعل رسالتنا أكثر تعبيرًا.
    |
    */

    'attributes' => [],

];