<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 11pt; line-height: 1.5; color: #000; }
    .page { margin: 20mm 25mm; }
    h2 { font-size: 13pt; text-align: center; margin-bottom: 6mm; }
    .subtitle { text-align: center; font-size: 10pt; margin-bottom: 8mm; }
    .parties { margin-bottom: 6mm; }
    p { margin: 0 0 4mm 0; text-align: justify; }
    .section-title { font-weight: bold; margin-top: 6mm; margin-bottom: 3mm; }
    .tax-notice { border: 1px solid #666; padding: 3mm; font-size: 9pt; margin: 6mm 0; }
    .signatures { margin-top: 12mm; }
    .sig-table { width: 100%; border-collapse: collapse; }
    .sig-table td { width: 50%; vertical-align: top; padding: 0 4mm; }
    .sig-line { border-top: 1px solid #000; margin-top: 12mm; padding-top: 2mm; font-size: 9pt; }
</style>
</head>
<body>
<div class="page">

<h2>ДОГОВОР ГРАЖДАНСКО-ПРАВОВОГО ХАРАКТЕРА №{{ date('Y') }}/ГПХ/{{ $verification->id }}</h2>
<div class="subtitle">на оказание услуг физическим лицом</div>
<div class="subtitle">г. Москва &nbsp;&nbsp;&nbsp; {{ $contract_date }}</div>

<div class="parties">
<p>
<strong>{{ $company_name }}</strong>, ИНН {{ $company_inn }}, в лице {{ $company_director }}, действующего на основании Устава, именуемое в дальнейшем <strong>«Заказчик»</strong>, с одной стороны,
</p>
<p>
и <strong>{{ $provider_name }}</strong>, ИНН {{ $inn }},
паспорт серия {{ $verification->passport_series ?? '__ __' }} № {{ $verification->passport_number ?? '______' }},
именуемый в дальнейшем <strong>«Исполнитель»</strong>, с другой стороны,
</p>
<p>совместно именуемые <strong>«Стороны»</strong>, заключили настоящий Договор о нижеследующем:</p>
</div>

{{-- TODO: вставить текст разделов из contract_gph.docx --}}
{{-- Разделы: 1. Предмет договора; 2. Права и обязанности сторон; 3. Порядок оказания услуг;
     4. Стоимость и порядок расчётов; 5. Налогообложение; 6. Ответственность сторон;
     7. Конфиденциальность; 8. Срок действия; 9. Заключительные положения --}}

<div class="tax-notice">
    <strong>Налогообложение:</strong> Заказчик является налоговым агентом Исполнителя.
    Вознаграждение выплачивается за вычетом НДФЛ 13% (п. 1 ст. 226 НК РФ).
    Страховые взносы уплачиваются Заказчиком сверх вознаграждения.
</div>

<div class="signatures">
<table class="sig-table">
    <tr>
        <td>
            <strong>ЗАКАЗЧИК:</strong><br>
            {{ $company_name }}<br>
            ИНН: {{ $company_inn }}<br>
            <div class="sig-line">{{ $company_director }}, подпись</div>
        </td>
        <td>
            <strong>ИСПОЛНИТЕЛЬ:</strong><br>
            {{ $provider_name }}<br>
            ИНН: {{ $inn }}<br>
            <div class="sig-line">{{ $provider_name }}, подпись</div>
        </td>
    </tr>
</table>
<p style="margin-top:8mm; font-size:9pt; color:#555;">
    Договор подписан электронной подписью (SMS OTP) {{ $contract_date }}.
    IP-адрес: {{ $verification->contract_ip_address }}
</p>
</div>

</div>
</body>
</html>
