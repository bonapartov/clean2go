// `calls` group of /api/settings: only whether calls are on. The LiveKit
// server address and keys stay on the backend (App\Support\Calls).
class CallsSettingModel {
  final bool status;

  CallsSettingModel({this.status = false});

  CallsSettingModel.fromJson(Map<String, dynamic> json)
      : status = json['status'] == true ||
            json['status'] == 1 ||
            json['status'] == '1';

  Map<String, dynamic> toJson() => {'status': status};
}
