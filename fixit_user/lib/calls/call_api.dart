// App-to-app calls (client ↔ executor) over Clean2Go's own LiveKit server.
// Backend: App\Http\Controllers\API\CallController, App\Support\Calls.
// Shared between fixit_user and fixit_provider (lib/calls/), own code.
import '../config.dart';

class CallInfo {
  final int id;
  final String type; // audio | video
  final String status; // ringing | accepted | declined | cancelled | missed | ended
  final int callerId;
  final String callerName;
  final int calleeId;
  final String calleeName;
  final String? livekitUrl;
  final String? token;

  CallInfo.fromJson(Map<String, dynamic> j)
      : id = int.tryParse('${j['id']}') ?? 0,
        type = '${j['type'] ?? 'audio'}',
        status = '${j['status'] ?? ''}',
        callerId = int.tryParse('${j['caller']?['id']}') ?? 0,
        callerName = '${j['caller']?['name'] ?? ''}',
        calleeId = int.tryParse('${j['callee']?['id']}') ?? 0,
        calleeName = '${j['callee']?['name'] ?? ''}',
        livekitUrl = j['livekit_url'],
        token = j['token'];

  bool get isVideo => type == 'video';
  bool get isOver =>
      const ['declined', 'cancelled', 'missed', 'ended'].contains(status);
}

/// Result of a call API request: the call, or an error message to show.
class CallResult {
  final CallInfo? call;
  final String? error;
  CallResult(this.call, this.error);
}

class CallApi {
  static String get _base => api.calls;

  static CallResult _parse(dynamic r) {
    if (r.isSuccess == true && r.data is Map) {
      return CallResult(
          CallInfo.fromJson(Map<String, dynamic>.from(r.data)), null);
    }
    return CallResult(null, r.message);
  }

  static Future<CallResult> start(int calleeId,
          {String? bookingId, bool video = false}) async =>
      _parse(await apiServices.postApi(
          _base,
          {
            'callee_id': calleeId,
            if (bookingId != null && bookingId.isNotEmpty && bookingId != '0')
              'booking_id': int.tryParse(bookingId),
            'type': video ? 'video' : 'audio',
          },
          isToken: true));

  static Future<CallResult> show(int id) async =>
      _parse(await apiServices.getApi('$_base/$id', [], isToken: true));

  static Future<CallResult> incoming() async =>
      _parse(await apiServices.getApi('$_base/incoming', [], isToken: true));

  static Future<CallResult> accept(int id) async =>
      _parse(await apiServices.postApi('$_base/$id/accept', {}, isToken: true));

  static Future<void> decline(int id) =>
      apiServices.postApi('$_base/$id/decline', {}, isToken: true);

  static Future<void> end(int id) =>
      apiServices.postApi('$_base/$id/end', {}, isToken: true);
}
