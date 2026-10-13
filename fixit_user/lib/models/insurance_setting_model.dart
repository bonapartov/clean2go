// «Застраховать услугу» settings from /api/settings (`insurance` group),
// set by the admin in Settings → «Общие» → «Страхование услуги».
// ROADMAP Sprint 6; backend logic: App\Support\ServiceInsurance. Own code.
class InsuranceSettingModel {
  bool status;
  // Checkbox pre-ticked for the client (decided 2026-10-13; legal check
  // pending — ЗоЗПП ст. 16). Admin can switch it off.
  bool defaultOn;
  String priceType; // 'fixed' | 'percentage'
  double price;
  String insurerName;
  String termsUrl;

  InsuranceSettingModel({
    this.status = false,
    this.defaultOn = true,
    this.priceType = 'fixed',
    this.price = 0,
    this.insurerName = '',
    this.termsUrl = '',
  });

  /// The admin switched it on and set a price — same rule as the backend.
  bool get isAvailable => status && price > 0;

  bool get isPercentage => priceType == 'percentage';

  InsuranceSettingModel.fromJson(Map<String, dynamic> json)
      : status = json['status'] == true ||
            json['status'] == 1 ||
            json['status'] == '1',
        defaultOn = json['default_on'] == null ||
            json['default_on'] == true ||
            json['default_on'] == 1 ||
            json['default_on'] == '1',
        priceType = json['price_type'] == 'percentage' ? 'percentage' : 'fixed',
        price = double.tryParse('${json['price'] ?? 0}') ?? 0,
        insurerName = '${json['insurer_name'] ?? ''}',
        termsUrl = '${json['terms_url'] ?? ''}';

  Map<String, dynamic> toJson() => {
        'status': status,
        'default_on': defaultOn,
        'price_type': priceType,
        'price': price,
        'insurer_name': insurerName,
        'terms_url': termsUrl,
      };
}
