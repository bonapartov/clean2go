// Incoming call: caller name, accept / decline. Closes by itself when the
// caller hangs up or the call rings out (backend marks it missed).
// Own code, shared by both apps (lib/calls/).
import 'dart:async';

import '../config.dart';
import 'call_api.dart';
import 'call_screen.dart';

class IncomingCallScreen extends StatefulWidget {
  final CallInfo call;

  const IncomingCallScreen({super.key, required this.call});

  @override
  State<IncomingCallScreen> createState() => _IncomingCallScreenState();
}

class _IncomingCallScreenState extends State<IncomingCallScreen> {
  Timer? _poll;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _poll = Timer.periodic(const Duration(seconds: 2), (_) async {
      final r = await CallApi.show(widget.call.id);
      if (mounted && (r.call?.isOver ?? false)) {
        _poll?.cancel();
        Navigator.of(context).maybePop();
      }
    });
  }

  @override
  void dispose() {
    _poll?.cancel();
    super.dispose();
  }

  Future<void> _accept() async {
    if (_busy) return;
    setState(() => _busy = true);
    _poll?.cancel();
    final r = await CallApi.accept(widget.call.id);
    if (!mounted) return;
    if (r.call == null || r.call!.token == null) {
      ScaffoldMessenger.maybeOf(context)?.showSnackBar(
          SnackBar(content: Text(r.error ?? 'Звонок уже завершён')));
      Navigator.of(context).maybePop();
      return;
    }
    Navigator.of(context).pushReplacement(MaterialPageRoute(
        builder: (_) => CallScreen(
            call: r.call!, peerName: widget.call.callerName, isCaller: false)));
  }

  Future<void> _decline() async {
    _poll?.cancel();
    await CallApi.decline(widget.call.id);
    if (mounted) Navigator.of(context).maybePop();
  }

  @override
  Widget build(BuildContext context) {
    final name = widget.call.callerName;
    return PopScope(
      canPop: false,
      child: Scaffold(
        backgroundColor: const Color(0xFF1B1F2A),
        body: SafeArea(
          child: Column(children: [
            const Spacer(),
            CircleAvatar(
                radius: 48,
                backgroundColor: Colors.white24,
                child: Text(
                    name.isNotEmpty ? name.characters.first.toUpperCase() : '?',
                    style: const TextStyle(fontSize: 36, color: Colors.white))),
            const SizedBox(height: 16),
            Text(name,
                style: const TextStyle(fontSize: 22, color: Colors.white)),
            const SizedBox(height: 8),
            Text(
                widget.call.isVideo
                    ? 'Входящий видеозвонок'
                    : 'Входящий звонок',
                style: const TextStyle(fontSize: 16, color: Colors.white70)),
            const Spacer(),
            Row(mainAxisAlignment: MainAxisAlignment.spaceEvenly, children: [
              InkResponse(
                  onTap: _decline,
                  child: const CircleAvatar(
                      radius: 34,
                      backgroundColor: Colors.red,
                      child: Icon(Icons.call_end, color: Colors.white))),
              InkResponse(
                  onTap: _accept,
                  child: CircleAvatar(
                      radius: 34,
                      backgroundColor: Colors.green,
                      child: Icon(
                          widget.call.isVideo ? Icons.videocam : Icons.call,
                          color: Colors.white))),
            ]),
            const SizedBox(height: 48),
          ]),
        ),
      ),
    );
  }
}
