// Entry points for calls: start a call from a chat, and show an incoming
// call when its push arrives (foreground, opened from notification, cold
// start) or when the app comes back to the foreground (GET /calls/incoming).
// Own code, shared by both apps (lib/calls/).
import 'package:firebase_messaging/firebase_messaging.dart';

import '../config.dart';
import 'call_api.dart';
import 'call_screen.dart';
import 'incoming_call_screen.dart';

class CallManager with WidgetsBindingObserver {
  CallManager._();
  static final CallManager instance = CallManager._();

  bool _started = false;
  int? _shownCallId;

  /// Calls are on in the admin (Настройки → Сторонние сервисы → Звонки).
  static bool get enabled => appSettingModel?.calls?.status ?? false;

  void init() {
    if (_started) return;
    _started = true;
    WidgetsBinding.instance.addObserver(this);
    FirebaseMessaging.onMessage.listen(_onPush);
    FirebaseMessaging.onMessageOpenedApp.listen(_onPush);
    FirebaseMessaging.instance.getInitialMessage().then((m) {
      if (m != null) _onPush(m);
    });
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) checkIncoming();
  }

  void _onPush(RemoteMessage message) {
    if (message.data['type'] == 'call') checkIncoming();
  }

  /// Shows the incoming-call screen if a call is ringing for this user.
  Future<void> checkIncoming() async {
    if (!enabled || userModel == null) return;
    final r = await CallApi.incoming();
    final call = r.call;
    final nav = navigatorKey.currentState;
    if (call == null || nav == null || call.id == _shownCallId) return;
    _shownCallId = call.id;
    nav.push(MaterialPageRoute(builder: (_) => IncomingCallScreen(call: call)));
  }

  /// Starts a call to the other side of a booking chat.
  static Future<void> start(BuildContext context,
      {required int calleeId,
      required String peerName,
      String? bookingId,
      bool video = false}) async {
    final r =
        await CallApi.start(calleeId, bookingId: bookingId, video: video);
    if (!context.mounted) return;
    if (r.call == null || r.call!.token == null) {
      ScaffoldMessenger.maybeOf(context)?.showSnackBar(
          SnackBar(content: Text(r.error ?? 'Не удалось позвонить')));
      return;
    }
    Navigator.of(context).push(MaterialPageRoute(
        builder: (_) =>
            CallScreen(call: r.call!, peerName: peerName, isCaller: true)));
  }
}

/// Audio + video call buttons for a chat header. Hidden when calls are off.
class CallButtons extends StatelessWidget {
  final int? calleeId;
  final String peerName;
  final String? bookingId;

  const CallButtons(
      {super.key, this.calleeId, this.peerName = '', this.bookingId});

  @override
  Widget build(BuildContext context) {
    if (!CallManager.enabled || calleeId == null || calleeId == 0) {
      return const SizedBox.shrink();
    }
    Widget btn(IconData icon, bool video) => IconButton(
        icon: Icon(icon, color: appColor(context).primary),
        onPressed: () => CallManager.start(context,
            calleeId: calleeId!,
            peerName: peerName,
            bookingId: bookingId,
            video: video));
    return Row(mainAxisSize: MainAxisSize.min, children: [
      btn(Icons.call, false),
      btn(Icons.videocam, true),
    ]);
  }
}
