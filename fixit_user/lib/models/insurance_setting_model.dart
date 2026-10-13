// «Застраховать услугу» settings from /api/settings (`insurance` group),
// set by the admin in Settings → «Общие» → «Страхование услуги».
// ROADMAP Sprint 6; backend logic: App\Support\ServiceInsurance. Own code.
class InsuranceSettingModel {
  bool status;
  String priceType; // 'fixed' | 'percentage'
  double price;
  String insurerName;
  String termsUrl;

  InsuranceSettingModel({
    this.status = false,
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
        priceType = json['price_type'] == 'percentage' ? 'percentage' : 'fixed',
        price = double.tryParse('${json['price'] ?? 0}') ?? 0,
        insurerName = '${json['insurer_name'] ?? ''}',
        termsUrl = '${json['terms_url'] ?? ''}';

  Map<String, dynamic> toJson() => {
        'status': status,
        'price_type': priceType,
        'price': price,
        'insurer_name': insurerName,
        'terms_url': termsUrl,
      };
}
