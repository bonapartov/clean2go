// Who the client contracts with, shown before payment (agency model B2C,
// ROADMAP Sprint 6). Comes from the backend checkout response
// (`executors`, built by App\Support\ExecutorDisclosure from the provider's
// ИНН verification). Own code — not from Fixit.
class ExecutorModel {
  int? providerId;
  String? name;
  String? status;
  String? statusLabel;
  String? inn;
  String? ogrn;
  bool verified;

  ExecutorModel({
    this.providerId,
    this.name,
    this.status,
    this.statusLabel,
    this.inn,
    this.ogrn,
    this.verified = false,
  });

  ExecutorModel.fromJson(Map<String, dynamic> json)
      : providerId = json['provider_id'],
        name = json['name'],
        status = json['status'],
        statusLabel = json['status_label'],
        inn = json['inn'],
        ogrn = json['ogrn'],
        verified = json['verified'] == true;

  Map<String, dynamic> toJson() => {
        'provider_id': providerId,
        'name': name,
        'status': status,
        'status_label': statusLabel,
        'inn': inn,
        'ogrn': ogrn,
        'verified': verified,
      };
}
