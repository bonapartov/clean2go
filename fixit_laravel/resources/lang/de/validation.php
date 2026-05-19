<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validierungs-Sprachzeilen
    |--------------------------------------------------------------------------
    |
    | Die folgenden Sprachzeilen enthalten die Standard-Fehlermeldungen, die
    | von der Validator-Klasse verwendet werden. Einige dieser Regeln haben
    | mehrere Versionen, wie die Größenvorgaben. Passen Sie diese Meldungen nach Bedarf an.
    |
    */
    'referral_code_belongs_to_a_different_user_type' => 'Der Empfehlungscode gehört zu einem anderen Benutzertyp.',
    'invalid_referral_code_or_referrer_not_found' => 'Ungültiger Empfehlungscode oder Empfehlender nicht gefunden.',
    'document_already_exists' => 'Ein Dokument für diesen Anbieter existiert bereits. Bitte aktualisieren Sie es stattdessen.',
    'notification_not_found' => 'Benachrichtigung nicht gefunden oder bereits gelesen.',
    'at_least_two_services_required' => 'Das Servicepaket muss mindestens zwei Services enthalten.',
    'notification_mark_as_read' => 'Benachrichtigung als gelesen markiert.',
    'please_select_a_file_smaller_than' => 'Bitte wählen Sie eine Datei kleiner als :max aus.',
    'additional_service_invalid_with_id' => 'Der ausgewählte Zusatzservice mit der ID :id ist ungültig.',
    'accepted' => 'Das Feld :attribute muss akzeptiert werden.',
    'active_url' => 'Das Feld :attribute ist keine gültige URL.',
    'after' => 'Das Feld :attribute muss ein Datum nach dem :date sein.',
    'after_or_equal' => 'Das Feld :attribute muss ein Datum nach oder gleich dem :date sein.',
    'alpha' => 'Das Feld :attribute darf nur Buchstaben enthalten.',
    'alpha_dash' => 'Das Feld :attribute darf nur Buchstaben, Zahlen, Bindestriche und Unterstriche enthalten.',
    'alpha_num' => 'Das Feld :attribute darf nur Buchstaben und Zahlen enthalten.',
    'array' => 'Das Feld :attribute muss ein Array sein.',
    'before' => 'Das Feld :attribute muss ein Datum vor dem :date sein.',
    'before_or_equal' => 'Das Feld :attribute muss ein Datum vor oder gleich dem :date sein.',
    'between' => [
        'numeric' => 'Das Feld :attribute muss zwischen :min und :max liegen.',
        'file' => 'Das Feld :attribute muss zwischen :min und :max Kilobytes groß sein.',
        'string' => 'Das Feld :attribute muss zwischen :min und :max Zeichen lang sein.',
        'array' => 'Das Feld :attribute muss zwischen :min und :max Elemente enthalten.',
    ],
    'boolean' => 'Das Feld :attribute muss wahr oder falsch sein.',
    'confirmed' => 'Die Bestätigung für :attribute stimmt nicht überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'date' => 'Das Feld :attribute ist kein gültiges Datum.',
    'date_equals' => 'Das Feld :attribute muss ein Datum gleich dem :date sein.',
    'date_format' => 'Das Feld :attribute entspricht nicht dem Format :format.',
    'different' => 'Die Felder :attribute und :other müssen unterschiedlich sein.',
    'digits' => 'Das Feld :attribute muss :digits Ziffern lang sein.',
    'digits_between' => 'Das Feld :attribute muss zwischen :min und :max Ziffern lang sein.',
    'dimensions' => 'Das Feld :attribute hat ungültige Bildabmessungen.',
    'distinct' => 'Das Feld :attribute hat einen doppelten Wert.',
    'email' => 'Das Feld :attribute muss eine gültige E-Mail-Adresse sein.',
    'ends_with' => 'Das Feld :attribute muss mit einem der folgenden enden: :values.',
    'exists' => 'Der ausgewählte Wert für :attribute ist ungültig.',
    'file' => 'Das Feld :attribute muss eine Datei sein.',
    'filled' => 'Das Feld :attribute muss einen Wert haben.',
    'gt' => [
        'numeric' => 'Das Feld :attribute muss größer als :value sein.',
        'file' => 'Das Feld :attribute muss größer als :value Kilobytes sein.',
        'string' => 'Das Feld :attribute muss länger als :value Zeichen sein.',
        'array' => 'Das Feld :attribute muss mehr als :value Elemente enthalten.',
    ],
    'gte' => [
        'numeric' => 'Das Feld :attribute muss größer oder gleich :value sein.',
        'file' => 'Das Feld :attribute muss größer oder gleich :value Kilobytes sein.',
        'string' => 'Das Feld :attribute muss mindestens :value Zeichen lang sein.',
        'array' => 'Das Feld :attribute muss :value oder mehr Elemente enthalten.',
    ],
    'image' => 'Das Feld :attribute muss ein Bild sein.',
    'in' => 'Der ausgewählte Wert für :attribute ist ungültig.',
    'in_array' => 'Das Feld :attribute existiert nicht in :other.',
    'integer' => 'Das Feld :attribute muss eine ganze Zahl sein.',
    'ip' => 'Das Feld :attribute muss eine gültige IP-Adresse sein.',
    'ipv4' => 'Das Feld :attribute muss eine gültige IPv4-Adresse sein.',
    'ipv6' => 'Das Feld :attribute muss eine gültige IPv6-Adresse sein.',
    'json' => 'Das Feld :attribute muss ein gültiger JSON-String sein.',
    'lt' => [
        'numeric' => 'Das Feld :attribute muss kleiner als :value sein.',
        'file' => 'Das Feld :attribute muss kleiner als :value Kilobytes sein.',
        'string' => 'Das Feld :attribute muss kürzer als :value Zeichen sein.',
        'array' => 'Das Feld :attribute muss weniger als :value Elemente enthalten.',
    ],
    'lte' => [
        'numeric' => 'Das Feld :attribute muss kleiner oder gleich :value sein.',
        'file' => 'Das Feld :attribute muss kleiner oder gleich :value Kilobytes sein.',
        'string' => 'Das Feld :attribute darf maximal :value Zeichen lang sein.',
        'array' => 'Das Feld :attribute darf nicht mehr als :value Elemente enthalten.',
    ],
    'max' => [
        'numeric' => 'Das Feld :attribute darf nicht größer als :max sein.',
        'file' => 'Das Feld :attribute darf nicht größer als :max Kilobytes sein.',
        'string' => 'Das Feld :attribute darf nicht länger als :max Zeichen sein.',
        'array' => 'Das Feld :attribute darf nicht mehr als :max Elemente enthalten.',
    ],
    'mimes' => 'Das Feld :attribute muss eine Datei vom Typ: :values sein.',
    'mimetypes' => 'Das Feld :attribute muss eine Datei vom Typ: :values sein.',
    'min' => [
        'numeric' => 'Das Feld :attribute muss mindestens :min sein.',
        'file' => 'Das Feld :attribute muss mindestens :min Kilobytes groß sein.',
        'string' => 'Das Feld :attribute muss mindestens :min Zeichen lang sein.',
        'array' => 'Das Feld :attribute muss mindestens :min Elemente enthalten.',
    ],
    'multiple_of' => 'Das Feld :attribute muss ein Vielfaches von :value sein.',
    'not_in' => 'Der ausgewählte Wert für :attribute ist ungültig.',
    'not_regex' => 'Das Format des Feldes :attribute ist ungültig.',
    'numeric' => 'Das Feld :attribute muss eine Zahl sein.',
    'password' => 'Das Passwort ist falsch.',
    'present' => 'Das Feld :attribute muss vorhanden sein.',
    'regex' => 'Das Format des Feldes :attribute ist ungültig.',
    'required' => 'Das Feld :attribute ist erforderlich.',
    'required_if' => 'Das Feld :attribute ist erforderlich, wenn :other den Wert :value hat.',
    'required_unless' => 'Das Feld :attribute ist erforderlich, es sei denn, :other ist in :values enthalten.',
    'required_with' => 'Das Feld :attribute ist erforderlich, wenn :values vorhanden ist.',
    'required_with_all' => 'Das Feld :attribute ist erforderlich, wenn alle :values vorhanden sind.',
    'required_without' => 'Das Feld :attribute ist erforderlich, wenn :values nicht vorhanden ist.',
    'required_without_all' => 'Das Feld :attribute ist erforderlich, wenn keiner der :values vorhanden ist.',
    'prohibited' => 'Das Feld :attribute ist verboten.',
    'prohibited_if' => 'Das Feld :attribute ist verboten, wenn :other den Wert :value hat.',
    'prohibited_unless' => 'Das Feld :attribute ist verboten, es sei denn, :other ist in :values enthalten.',
    'same' => 'Die Felder :attribute und :other müssen übereinstimmen.',
    'size' => [
        'numeric' => 'Das Feld :attribute muss :size sein.',
        'file' => 'Das Feld :attribute muss :size Kilobytes groß sein.',
        'string' => 'Das Feld :attribute muss :size Zeichen lang sein.',
        'array' => 'Das Feld :attribute muss genau :size Elemente enthalten.',
    ],
    'starts_with' => 'Das Feld :attribute muss mit einem der folgenden beginnen: :values.',
    'string' => 'Das Feld :attribute muss eine Zeichenkette sein.',
    'timezone' => 'Das Feld :attribute muss eine gültige Zeitzone sein.',
    'unique' => 'Der Wert für :attribute ist bereits vergeben.',
    'uploaded' => 'Das Hochladen von :attribute ist fehlgeschlagen.',
    'url' => 'Das Format des Feldes :attribute ist ungültig.',
    'uuid' => 'Das Feld :attribute muss eine gültige UUID sein.',

    'service_id_invalid' => 'Die ausgewählte service_id ist ungültig.',
    'coupon_code_not_found' => 'Wir konnten keinen Gutscheincode :code finden.',
    'service_ids_required' => 'Mindestens eine Service-ID ist erforderlich.',

    'service_ids_array' => 'Die Service-IDs müssen als Array bereitgestellt werden.',
    'service_ids_exists' => 'Die Service-IDs müssen als Array bereitgestellt werden.',
    'is_multiple_serviceman_required' => 'Das Feld isMultipleServiceman ist erforderlich.',
    'is_multiple_serviceman_boolean' => 'Das Feld isMultipleServiceman muss ein Boolean sein.',
    'required_servicemen_required' => 'Das Feld required_servicemen ist erforderlich, wenn isMultipleServiceman wahr ist.',
    'required_servicemen_integer' => 'Das Feld required_servicemen muss eine ganze Zahl sein.',
    'select_serviceman_required' => 'Das Feld select_serviceman ist erforderlich.',
    'select_serviceman_in' => 'Der ausgewählte Wert für select_serviceman ist ungültig. Es muss entweder "user_choice" oder "app_choose" sein.',
    'select_date_time_required' => 'Das Feld select_date_time ist erforderlich.',
    'select_date_time_in' => 'Der ausgewählte Wert für select_date_time ist ungültig. Es muss entweder "custom" oder "as_provider" sein.',

    'providerId_exists' => 'Anbieter existiert nicht.',
    'serviceId_exists' => 'Service existiert nicht.',

    'user_is_not_provider' => 'Der Benutzer ist kein Anbieter.',
    'login_type_google_apple_or_phone' => 'Der Anmeldetyp kann entweder Google oder Apple sein.',
    'address_ids_exists' => 'Die Adresse mit der ID :value existiert nicht.',

    'banner_images_required' => 'Bitte wählen Sie mindestens ein Bild aus.',
    'banner_type_required' => 'Bitte wählen Sie einen Banner-Typ aus.',
    'banner_related_id_required' => 'Bitte wählen Sie einen Banner-Kategorietyp aus.',
    'blog_categories_required' => 'Das Feld Kategorien ist erforderlich.',
    'zones_required' => 'Das Feld Zonen ist erforderlich.',

    'commission_regex' => 'Geben Sie eine Provisionsrate in Prozent zwischen 0 und 99,99 ein.',
    'category_type' => 'Der Kategorietyp kann entweder Blog oder Service sein.',

    'user_id_required' => 'Bitte wählen Sie einen Anbieter aus.',
    'document_id_required' => 'Bitte wählen Sie ein Dokument aus.',
    'identity_no_required' => 'Die Dokumentennummer ist erforderlich.',

    'service_id_required' => 'Das Feld Services ist erforderlich.',
    'start_end_date_required' => 'Das Feld Startdatum & Enddatum ist erforderlich.',
    'image_required' => 'Mindestens ein Bild ist erforderlich.',

    'provider_id_required' => 'Das Feld Anbieter ist erforderlich.',
    'service_id_required_if' => 'Das Feld Verwandte Services ist erforderlich, wenn zufällige verwandte Services ausgeschaltet sind.',
    'type' => 'Bitte wählen Sie einen Service-Typ aus.',
    'price_required_if' => 'Das Feld Preis ist erforderlich.',

    'type_in' => 'Der Tag-Typ kann entweder Beitrag oder Produkt sein.',
    'type_in_wallet_bonus' => 'Der Wallet-Bonustyp kann entweder fest oder prozentual sein.',
    'type_in_wallet_bonus'        => 'Ungültiger Bonustyp ausgewählt.',
    'amount_required'             => 'Der Betrag ist für den festen Typ erforderlich.',
    'percentage_amount_required'  => 'Der Prozentbetrag ist für den prozentualen Typ erforderlich.',
    'amount_min'                  => 'Der Betrag muss mindestens 1 sein.',
    'percentage_min'              => 'Der Prozentsatz muss mindestens 1 sein.',
    'percentage_max'              => 'Der Prozentsatz darf 100 nicht überschreiten.',
    'min_top_up_required'         => 'Der Mindestaufladebetrag ist erforderlich.',
    'total_usage_limit'           =>  'Die Gesamtnutzungsbegrenzung der Bonusnummer ist erforderlich.',
    'usage_limit_per_user'        =>  'Die Nutzungsbegrenzung pro Benutzer ist erforderlich.',
    'max_bonus_required'          => 'Der maximale Bonusbetrag ist erforderlich.',
    'rate_regex' => 'Geben Sie einen Steuersatz zwischen 0 und 99,99 an.',

    'provider_id_exists' => 'Der ausgewählte Anbieter ist ungültig.',
    'gap_required' => 'Das Feld gap ist erforderlich.',
    'gap_integer' => 'Die gap muss eine ganze Zahl sein.',
    'gap_min' => 'Die gap muss mindestens 1 betragen.',
    'time_unit_required' => 'Das Feld time unit ist erforderlich.',
    'time_unit_in' => 'Die ausgewählte Zeiteinheit ist ungültig.',
    'time_slots_required' => 'Mindestens ein Zeitfenster ist erforderlich.',
    'time_slots_day_required' => 'Das Feld day ist erforderlich.',
    'time_slots_day_in' => 'Der ausgewählte Tag ist ungültig.',
    'time_slots_start_time_required' => 'Das Feld start time ist erforderlich.',
    'time_slots_start_time_date_format' => 'Die Startzeit entspricht nicht dem Format H:i.',
    'time_slots_end_time_required' => 'Das Feld end time ist erforderlich.',
    'time_slots_end_time_date_format' => 'Die Endzeit entspricht nicht dem Format H:i.',
    'time_slots_end_time_after' => 'Die Endzeit muss nach der Startzeit liegen.',

    'payment_type_in' => 'Der Zahlungstyp sollte PayPal oder Bank sein.',

    // neue Schlüssel
    'invalid_address_id' => 'Ungültige Adress-ID',
    'user_not_exists' => 'Benutzer existiert nicht oder ist deaktiviert',

    'openai' => [
        'input_text_required' => 'Bitte geben Sie den Text ein.',
        'input_text_min'      => 'Der Text muss mindestens 2 Zeichen lang sein.',

        'locale_required'     => 'Der Sprachcode ist erforderlich.',
        'locale_size'         => 'Der Sprachcode muss genau 2 Zeichen lang sein.',

        'type_required'       => 'Der Inhaltstyp ist erforderlich.',
        'type_invalid'        => 'Ungültiger Inhaltstyp.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Benutzerdefinierte Validierungs-Sprachzeilen
    |--------------------------------------------------------------------------
    |
    | Hier können Sie benutzerdefinierte Validierungsmeldungen für Attribute
    | angeben, indem Sie die Konvention "attribute.rule" verwenden, um die
    | Zeilen zu benennen. So können Sie schnell eine spezifische benutzer-
    | definierte Sprachzeile für eine bestimmte Attributregel festlegen.
    |
    */

    'custom' => [
        // Profil aktualisieren
        'name' => [
            'max' => 'Der Name darf nicht länger als :max Zeichen sein.',
        ],
        'email' => [
            'email' => 'Die E-Mail-Adresse muss eine gültige E-Mail-Adresse sein.',
            'unique' => 'Die E-Mail-Adresse ist bereits vergeben.',
        ],
        'phone' => [
            'required' => 'Die Telefonnummer ist erforderlich.',
            'digits_between' => 'Die Telefonnummer muss zwischen :min und :max Ziffern lang sein.',
            'unique' => 'Die Telefonnummer ist bereits vergeben.',
        ],
        'code' => [
            'required' => 'Der Code ist erforderlich.',
        ],
        'role_id' => [
            'exists' => 'Die ausgewählte Rolle ist ungültig.',
        ],

        // Adresse erstellen
        'country_id' => [
            'required' => 'Das Feld Land ist erforderlich.',
            'exists' => 'Das ausgewählte Land ist ungültig.',
        ],
        'state_id' => [
            'required' => 'Das Feld Bundesland/Staat ist erforderlich.',
            'exists' => 'Das ausgewählte Bundesland/der ausgewählte Staat ist ungültig.',
        ],
        'city' => [
            'required' => 'Die Stadt ist erforderlich.',
            'string' => 'Die Stadt muss eine Zeichenkette sein.',
        ],
        'address' => [
            'required' => 'Die Adresse ist erforderlich.',
        ],
        'latitude' => [
            'required' => 'Der Breitengrad ist erforderlich.',
            'latitude_longitude' => 'Der Breitengrad muss ein gültiger Breitengrad sein.',
        ],
        'longitude' => [
            'required' => 'Der Längengrad ist erforderlich.',
            'latitude_longitude' => 'Der Längengrad muss ein gültiger Längengrad sein.',
        ],
        'postal_code' => [
            'required' => 'Die Postleitzahl ist erforderlich.',
        ],
        'alternative_phone' => [
            'required_if' => 'Die alternative Telefonnummer ist erforderlich, wenn der Rollentyp Service ist.',
        ],
        'alternative_name' => [
            'required_if' => 'Der alternative Name ist erforderlich, wenn der Rollentyp Service ist.',
        ],

        // Adresse aktualisieren
        'user_id' => [
            'nullable' => 'Die Benutzer-ID ist optional.',
            'exists' => 'Die ausgewählte Benutzer-ID ist ungültig.',
        ],
        'type' => [
            'required' => 'Der Typ ist erforderlich.',
            'string' => 'Der Typ muss eine Zeichenkette sein.',
        ],
        'unique_category_zone' => 'Der Kategorietitel muss innerhalb der ausgewählten Zonen eindeutig sein.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Benutzerdefinierte Validierungsattribute
    |--------------------------------------------------------------------------
    |
    | Die folgenden Sprachzeilen werden verwendet, um unseren Attribut-Platzhalter
    | gegen etwas leserfreundlicheres auszutauschen, wie z.B. "E-Mail-Adresse"
    | anstelle von "email". Dies hilft uns einfach, unsere Nachricht verständlicher
    | zu gestalten.
    |
    */

    'attributes' => [],

];