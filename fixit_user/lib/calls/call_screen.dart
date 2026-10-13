// Active call: joins the LiveKit room with this user's token, shows the
// other side (video or avatar + timer), mic / speaker / camera / hang up.
// Polls the backend so a hang-up on the other phone closes this screen.
// Own code, shared by both apps (lib/calls/).
import 'dart:async';

import 'package:livekit_client/livekit_client.dart';
import 'package:permission_handler/permission_handler.dart';

import '../config.dart';
import 'call_api.dart';

class CallScreen extends StatefulWidget {
  final CallInfo call;
  final String peerName;
  final bool isCaller;

  const CallScreen(
      {super.key,
      required this.call,
      required this.peerName,
      required this.isCaller});

  @override
  State<CallScreen> createState() => _CallScreenState();
}

class _CallScreenState extends State<CallScreen> {
  Room? _room;
  EventsListener<RoomEvent>? _listener;
  Timer? _poll, _ticker;
  bool _micOn = true, _speakerOn = false, _cameraOn = false, _closing = false;
  String _status = '';
  int _seconds = 0;

  @override
  void initState() {
    super.initState();
    _cameraOn = widget.call.isVideo;
    _speakerOn = widget.call.isVideo;
    _status = widget.isCaller ? 'Вызов…' : 'Соединение…';
    _join();
    _poll = Timer.periodic(const Duration(seconds: 2), (_) => _checkStatus());
  }

  Future<void> _join() async {
    final perms = [Permission.microphone, if (widget.call.isVideo) Permission.camera];
    final granted = await perms.request();
    if (granted.values.any((s) => !s.isGranted)) {
      _close('Нет доступа к микрофону или камере');
      return;
    }
    try {
      final room = Room(
          roomOptions: const RoomOptions(adaptiveStream: true, dynacast: true));
      _listener = room.createListener()
        ..on<ParticipantConnectedEvent>((_) => _onPeerJoined())
        ..on<TrackSubscribedEvent>((_) => setState(() {}))
        ..on<TrackUnsubscribedEvent>((_) => setState(() {}))
        ..on<ParticipantDisconnectedEvent>((_) => _hangUp('Звонок завершён'))
        ..on<RoomDisconnectedEvent>((_) => _close('Звонок завершён'));
      await room.connect(widget.call.livekitUrl!, widget.call.token!);
      _room = room;
      await room.localParticipant?.setMicrophoneEnabled(true);
      if (widget.call.isVideo) {
        await room.localParticipant?.setCameraEnabled(true);
      }
      await AudioManager.instance.setSpeakerOutputPreferred(_speakerOn);
      if (room.remoteParticipants.isNotEmpty) _onPeerJoined();
      if (mounted) setState(() {});
    } catch (e) {
      _close('Не удалось подключиться к звонку');
    }
  }

  void _onPeerJoined() {
    if (_ticker != null) return;
    _ticker = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _seconds++);
    });
    if (mounted) setState(() => _status = '');
  }

  Future<void> _checkStatus() async {
    final r = await CallApi.show(widget.call.id);
    final call = r.call;
    if (call == null || !mounted) return;
    if (call.isOver) {
      _close(const {
            'declined': 'Звонок отклонён',
            'missed': 'Нет ответа',
            'cancelled': 'Звонок отменён',
          }[call.status] ??
          'Звонок завершён');
    } else if (call.status == 'ringing' && widget.isCaller) {
      setState(() => _status = 'Вызов…');
    }
  }

  Future<void> _hangUp([String? message]) async {
    await CallApi.end(widget.call.id);
    _close(message);
  }

  Future<void> _close([String? message]) async {
    if (_closing) return;
    _closing = true;
    _poll?.cancel();
    _ticker?.cancel();
    await _listener?.dispose();
    await _room?.disconnect();
    await _room?.dispose();
    if (!mounted) return;
    if (message != null) {
      ScaffoldMessenger.maybeOf(context)
          ?.showSnackBar(SnackBar(content: Text(message)));
    }
    Navigator.of(context).maybePop();
  }

  @override
  void dispose() {
    _poll?.cancel();
    _ticker?.cancel();
    if (!_closing) {
      CallApi.end(widget.call.id);
      _listener?.dispose();
      _room?.disconnect();
      _room?.dispose();
    }
    super.dispose();
  }

  VideoTrack? _track(Participant? p) {
    for (final pub in p?.videoTrackPublications ?? <TrackPublication>[]) {
      final t = pub.track;
      if (t is VideoTrack && !pub.muted) return t;
    }
    return null;
  }

  String get _time =>
      '${(_seconds ~/ 60).toString().padLeft(2, '0')}:${(_seconds % 60).toString().padLeft(2, '0')}';

  Widget _button(IconData icon, bool active, VoidCallback onTap,
          {Color? color}) =>
      InkResponse(
        onTap: onTap,
        child: CircleAvatar(
          radius: 30,
          backgroundColor: color ?? (active ? Colors.white : Colors.white24),
          child: Icon(icon,
              color: color != null
                  ? Colors.white
                  : (active ? Colors.black87 : Colors.white)),
        ),
      );

  @override
  Widget build(BuildContext context) {
    final remote = _room?.remoteParticipants.values.firstOrNull;
    final remoteVideo = _track(remote);
    final localVideo = _cameraOn ? _track(_room?.localParticipant) : null;

    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: const Color(0xFF1B1F2A),
        body: SafeArea(
          child: Stack(children: [
            if (remoteVideo != null)
              Positioned.fill(
                  child: VideoTrackRenderer(remoteVideo,
                      fit: VideoViewFit.cover))
            else
              Center(
                child: Column(mainAxisSize: MainAxisSize.min, children: [
                  CircleAvatar(
                      radius: 48,
                      backgroundColor: Colors.white24,
                      child: Text(
                          widget.peerName.isNotEmpty
                              ? widget.peerName.characters.first.toUpperCase()
                              : '?',
                          style: const TextStyle(
                              fontSize: 36, color: Colors.white))),
                  const SizedBox(height: 16),
                  Text(widget.peerName,
                      style:
                          const TextStyle(fontSize: 22, color: Colors.white)),
                  const SizedBox(height: 8),
                  Text(_status.isNotEmpty ? _status : _time,
                      style:
                          const TextStyle(fontSize: 16, color: Colors.white70)),
                ]),
              ),
            if (localVideo != null)
              Positioned(
                  right: 16,
                  top: 16,
                  width: 110,
                  height: 160,
                  child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: VideoTrackRenderer(localVideo,
                          fit: VideoViewFit.cover))),
            Positioned(
              left: 0,
              right: 0,
              bottom: 32,
              child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _button(_micOn ? Icons.mic : Icons.mic_off, _micOn,
                        () async {
                      _micOn = !_micOn;
                      await _room?.localParticipant
                          ?.setMicrophoneEnabled(_micOn);
                      setState(() {});
                    }),
                    _button(Icons.volume_up, _speakerOn, () async {
                      _speakerOn = !_speakerOn;
                      await AudioManager.instance.setSpeakerOutputPreferred(_speakerOn);
                      setState(() {});
                    }),
                    if (widget.call.isVideo)
                      _button(
                          _cameraOn ? Icons.videocam : Icons.videocam_off,
                          _cameraOn, () async {
                        _cameraOn = !_cameraOn;
                        await _room?.localParticipant
                            ?.setCameraEnabled(_cameraOn);
                        setState(() {});
                      }),
                    _button(Icons.call_end, false, () => _hangUp(),
                        color: Colors.red),
                  ]),
            ),
          ]),
        ),
      ),
    );
  }
}
