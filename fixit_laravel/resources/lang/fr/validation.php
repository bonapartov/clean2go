<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */
    'referral_code_belongs_to_a_different_user_type' => 'Le code de parrainage appartient à un type d\'utilisateur différent.',
    'invalid_referral_code_or_referrer_not_found' => 'Code de parrainage invalide ou parrain non trouvé.',
    'document_already_exists' => 'Un document existe déjà pour ce prestataire. Veuillez le mettre à jour à la place.',
    'notification_not_found' => 'Notification non trouvée ou déjà lue',
    'at_least_two_services_required' => 'Le forfait de service doit inclure au moins deux services.',
    'notification_mark_as_read' => 'Notification marquée comme lue',
    'please_select_a_file_smaller_than' => 'Veuillez sélectionner un fichier plus petit que :max',
    'additional_service_invalid_with_id' => 'Le service supplémentaire sélectionné avec l\'ID :id est invalide.',
    'accepted' => 'Le champ :attribute doit être accepté.',
    'active_url' => 'Le champ :attribute n\'est pas une URL valide.',
    'after' => 'Le champ :attribute doit être une date après :date.',
    'after_or_equal' => 'Le champ :attribute doit être une date après ou égale à :date.',
    'alpha' => 'Le champ :attribute ne doit contenir que des lettres.',
    'alpha_dash' => 'Le champ :attribute ne doit contenir que des lettres, des chiffres, des tirets et des underscores.',
    'alpha_num' => 'Le champ :attribute ne doit contenir que des lettres et des chiffres.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date avant :date.',
    'before_or_equal' => 'Le champ :attribute doit être une date avant ou égale à :date.',
    'between' => [
        'numeric' => 'Le champ :attribute doit être compris entre :min et :max.',
        'file' => 'Le champ :attribute doit être compris entre :min et :max kilo-octets.',
        'string' => 'Le champ :attribute doit contenir entre :min et :max caractères.',
        'array' => 'Le champ :attribute doit contenir entre :min et :max éléments.',
    ],
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute n\'est pas une date valide.',
    'date_equals' => 'Le champ :attribute doit être une date égale à :date.',
    'date_format' => 'Le champ :attribute ne correspond pas au format :format.',
    'different' => 'Les champs :attribute et :other doivent être différents.',
    'digits' => 'Le champ :attribute doit contenir :digits chiffres.',
    'digits_between' => 'Le champ :attribute doit contenir entre :min et :max chiffres.',
    'dimensions' => 'Le champ :attribute a des dimensions d\'image invalides.',
    'distinct' => 'Le champ :attribute a une valeur en double.',
    'email' => 'Le champ :attribute doit être une adresse e-mail valide.',
    'ends_with' => 'Le champ :attribute doit se terminer par l\'un des éléments suivants : :values.',
    'exists' => 'Le champ :attribute sélectionné est invalide.',
    'file' => 'Le champ :attribute doit être un fichier.',
    'filled' => 'Le champ :attribute doit avoir une valeur.',
    'gt' => [
        'numeric' => 'Le champ :attribute doit être supérieur à :value.',
        'file' => 'Le champ :attribute doit être supérieur à :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir plus de :value caractères.',
        'array' => 'Le champ :attribute doit contenir plus de :value éléments.',
    ],
    'gte' => [
        'numeric' => 'Le champ :attribute doit être supérieur ou égal à :value.',
        'file' => 'Le champ :attribute doit être supérieur ou égal à :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir au moins :value caractères.',
        'array' => 'Le champ :attribute doit contenir :value éléments ou plus.',
    ],
    'image' => 'Le champ :attribute doit être une image.',
    'in' => 'Le champ :attribute sélectionné est invalide.',
    'in_array' => 'Le champ :attribute n\'existe pas dans :other.',
    'integer' => 'Le champ :attribute doit être un entier.',
    'ip' => 'Le champ :attribute doit être une adresse IP valide.',
    'ipv4' => 'Le champ :attribute doit être une adresse IPv4 valide.',
    'ipv6' => 'Le champ :attribute doit être une adresse IPv6 valide.',
    'json' => 'Le champ :attribute doit être une chaîne JSON valide.',
    'lt' => [
        'numeric' => 'Le champ :attribute doit être inférieur à :value.',
        'file' => 'Le champ :attribute doit être inférieur à :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir moins de :value caractères.',
        'array' => 'Le champ :attribute doit contenir moins de :value éléments.',
    ],
    'lte' => [
        'numeric' => 'Le champ :attribute doit être inférieur ou égal à :value.',
        'file' => 'Le champ :attribute doit être inférieur ou égal à :value kilo-octets.',
        'string' => 'Le champ :attribute doit contenir au plus :value caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :value éléments.',
    ],
    'max' => [
        'numeric' => 'Le champ :attribute ne doit pas être supérieur à :max.',
        'file' => 'Le champ :attribute ne doit pas être supérieur à :max kilo-octets.',
        'string' => 'Le champ :attribute ne doit pas contenir plus de :max caractères.',
        'array' => 'Le champ :attribute ne doit pas contenir plus de :max éléments.',
    ],
    'mimes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'mimetypes' => 'Le champ :attribute doit être un fichier de type : :values.',
    'min' => [
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'file' => 'Le champ :attribute doit être au moins :min kilo-octets.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
    ],
    'multiple_of' => 'Le champ :attribute doit être un multiple de :value.',
    'not_in' => 'Le champ :attribute sélectionné est invalide.',
    'not_regex' => 'Le format du champ :attribute est invalide.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'password' => 'Le mot de passe est incorrect.',
    'present' => 'Le champ :attribute doit être présent.',
    'regex' => 'Le format du champ :attribute est invalide.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire lorsque :other vaut :value.',
    'required_unless' => 'Le champ :attribute est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Le champ :attribute est obligatoire lorsque :values est présent.',
    'required_with_all' => 'Le champ :attribute est obligatoire lorsque :values sont présents.',
    'required_without' => 'Le champ :attribute est obligatoire lorsque :values n\'est pas présent.',
    'required_without_all' => 'Le champ :attribute est obligatoire lorsqu\'aucun des :values n\'est présent.',
    'prohibited' => 'Le champ :attribute est interdit.',
    'prohibited_if' => 'Le champ :attribute est interdit lorsque :other vaut :value.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other est dans :values.',
    'same' => 'Les champs :attribute et :other doivent correspondre.',
    'size' => [
        'numeric' => 'Le champ :attribute doit être :size.',
        'file' => 'Le champ :attribute doit être :size kilo-octets.',
        'string' => 'Le champ :attribute doit contenir :size caractères.',
        'array' => 'Le champ :attribute doit contenir :size éléments.',
    ],
    'starts_with' => 'Le champ :attribute doit commencer par l\'un des éléments suivants : :values.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'timezone' => 'Le champ :attribute doit être un fuseau horaire valide.',
    'unique' => 'Le champ :attribute a déjà été pris.',
    'uploaded' => 'Le champ :attribute n\'a pas pu être téléchargé.',
    'url' => 'Le format du champ :attribute est invalide.',
    'uuid' => 'Le champ :attribute doit être un UUID valide.',

    'service_id_invalid' => 'Le service_id sélectionné est invalide.',
    'coupon_code_not_found' => 'Nous n\'avons pas trouvé de coupon :code',
    'service_ids_required' => 'Au moins un ID de service est requis.',

    'service_ids_array' => 'Les IDs de service doivent être fournis sous forme de tableau.',
    'service_ids_exists' => 'Les IDs de service doivent être fournis sous forme de tableau.',
    'is_multiple_serviceman_required' => 'Le champ isMultipleServiceman est requis.',
    'is_multiple_serviceman_boolean' => 'Le champ isMultipleServiceman doit être un booléen.',
    'required_servicemen_required' => 'Le champ required_servicemen est requis lorsque isMultipleServiceman est vrai.',
    'required_servicemen_integer' => 'Le champ required_servicemen doit être un entier.',
    'select_serviceman_required' => 'Le champ select_serviceman est requis.',
    'select_serviceman_in' => 'Le select_serviceman sélectionné est invalide. Il doit être soit "user_choice" soit "app_choose".',
    'select_date_time_required' => 'Le champ select_date_time est requis.',
    'select_date_time_in' => 'Le select_date_time sélectionné est invalide. Il doit être soit "custom" soit "as_provider".',

    'providerId_exists' => 'Le prestataire n\'existe pas.',
    'serviceId_exists' => 'Le service n\'existe pas.',

    'user_is_not_provider' => 'L\'utilisateur n\'est pas un prestataire.',
    'login_type_google_apple_or_phone' => 'La connexion avec type peut être soit google soit apple',
    'address_ids_exists' => 'L\'adresse avec l\'ID :value n\'existe pas.',

    'banner_images_required' => 'Veuillez sélectionner au moins une image.',
    'banner_type_required' => 'Veuillez sélectionner le type de bannière.',
    'banner_related_id_required' => 'Veuillez sélectionner le type de catégorie de bannière.',
    'blog_categories_required' => 'Le champ categories est requis',
    'zones_required' => 'Le champ zones est requis',

    'commission_regex' => 'Entrez un taux de commission en pourcentage entre 0 et 99,99',
    'category_type' => 'Le type de catégorie peut être soit blog soit service',

    'user_id_required' => 'Veuillez sélectionner un prestataire.',
    'document_id_required' => 'Veuillez sélectionner un document.',
    'identity_no_required' => 'Le numéro de document est requis.',

    'service_id_required' => 'Le champ Services est requis',
    'start_end_date_required' => 'Le champ Date de début & Date de fin est requis',
    'image_required' => 'Au moins une image est requise',

    'provider_id_required' => 'Le champ Prestataire est requis',
    'service_id_required_if' => 'Le champ Services associés est requis lorsque les services associés aléatoires sont désactivés.',
    'type' => 'Veuillez sélectionner un type de service',
    'price_required_if' => 'Le champ prix est requis',

    'type_in' => 'Le type de tag peut être soit post soit produit',
    'type_in_wallet_bonus' => 'Le type de bonus de portefeuille peut être soit fixe soit pourcentage',
    'type_in_wallet_bonus'        => 'Type de bonus invalide sélectionné.',
    'amount_required'             => 'Le montant est requis pour le type fixe.',
    'percentage_amount_required'  => 'Le pourcentage est requis pour le type pourcentage.',
    'amount_min'                  => 'Le montant doit être d\'au moins 1.',
    'percentage_min'              => 'Le pourcentage doit être d\'au moins 1.',
    'percentage_max'              => 'Le pourcentage ne peut pas dépasser 100.',
    'min_top_up_required'         => 'Le montant minimum de recharge est requis.',
    'total_usage_limit'           => 'Le nombre total d\'utilisations du bonus est requis.',
    'usage_limit_per_user'        => 'Le nombre d\'utilisations par utilisateur est requis.',
    'max_bonus_required'          => 'Le montant maximum du bonus est requis.',
    'rate_regex' => 'Spécifiez un taux de taxe entre 0 et 99,99.',

    'provider_id_exists' => 'Le prestataire sélectionné est invalide.',
    'gap_required' => 'Le champ intervalle est requis.',
    'gap_integer' => 'L\'intervalle doit être un entier.',
    'gap_min' => 'L\'intervalle doit être d\'au moins 1.',
    'time_unit_required' => 'Le champ unité de temps est requis.',
    'time_unit_in' => 'L\'unité de temps sélectionnée est invalide.',
    'time_slots_required' => 'Au moins un créneau horaire est requis.',
    'time_slots_day_required' => 'Le champ jour est requis.',
    'time_slots_day_in' => 'Le jour sélectionné est invalide.',
    'time_slots_start_time_required' => 'Le champ heure de début est requis.',
    'time_slots_start_time_date_format' => 'L\'heure de début ne correspond pas au format H:i.',
    'time_slots_end_time_required' => 'Le champ heure de fin est requis.',
    'time_slots_end_time_date_format' => 'L\'heure de fin ne correspond pas au format H:i.',
    'time_slots_end_time_after' => 'L\'heure de fin doit être après l\'heure de début.',

    'payment_type_in' => 'Le type de paiement doit être PayPal ou banque.',

    // new keys
    'invalid_address_id' => 'ID d\'adresse invalide',
    'user_not_exists' => 'L\'utilisateur n\'existe pas ou est désactivé',

    'openai' => [
        'input_text_required' => 'Veuillez saisir le texte.',
        'input_text_min'      => 'Le texte doit contenir au moins 2 caractères.',

        'locale_required'     => 'Le code de langue est requis.',
        'locale_size'         => 'Le code de langue doit contenir exactement 2 caractères.',

        'type_required'       => 'Le type de contenu est requis.',
        'type_invalid'        => 'Type de contenu invalide.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        // update profile
        'name' => [
            'max' => 'Le nom ne peut pas dépasser :max caractères.',
        ],
        'email' => [
            'email' => 'L\'e-mail doit être une adresse e-mail valide.',
            'unique' => 'L\'e-mail a déjà été pris.',
        ],
        'phone' => [
            'required' => 'Le numéro de téléphone est requis.',
            'digits_between' => 'Le numéro de téléphone doit contenir entre :min et :max chiffres.',
            'unique' => 'Le numéro de téléphone a déjà été pris.',
        ],
        'code' => [
            'required' => 'Le code est requis.',
        ],
        'role_id' => [
            'exists' => 'Le rôle sélectionné est invalide.',
        ],

        //create address
        'country_id' => [
            'required' => 'Le champ pays est requis.',
            'exists' => 'Le pays sélectionné est invalide.',
        ],
        'state_id' => [
            'required' => 'Le champ état est requis.',
            'exists' => 'L\'état sélectionné est invalide.',
        ],
        'city' => [
            'required' => 'La ville est requise.',
            'string' => 'La ville doit être une chaîne de caractères.',
        ],
        'address' => [
            'required' => 'L\'adresse est requise.',
        ],
        'latitude' => [
            'required' => 'La latitude est requise.',
            'latitude_longitude' => 'La latitude doit être une latitude valide.',
        ],
        'longitude' => [
            'required' => 'La longitude est requise.',
            'latitude_longitude' => 'La longitude doit être une longitude valide.',
        ],
        'postal_code' => [
            'required' => 'Le code postal est requis.',
        ],
        'alternative_phone' => [
            'required_if' => 'Le téléphone alternatif est requis lorsque le type de rôle est service.',
        ],
        'alternative_name' => [
            'required_if' => 'Le nom alternatif est requis lorsque le type de rôle est service.',
        ],

        //update address
        'user_id' => [
            'nullable' => 'L\'ID utilisateur est facultatif.',
            'exists' => 'L\'ID utilisateur sélectionné est invalide.',
        ],
        'type' => [
            'required' => 'Le type est requis.',
            'string' => 'Le type doit être une chaîne de caractères.',
        ],
        'unique_category_zone' => 'Le titre de la catégorie doit être unique dans les zones sélectionnées.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
