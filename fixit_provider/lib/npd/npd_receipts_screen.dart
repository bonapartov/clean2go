// «Мои чеки (НПД)»: the executor's receipts issued through Clean2Go and
// their НПД income this year against the 2.4 mln ₽ limit (ROADMAP Sprint 8).
// Backend: GET /api/npd/my-receipts (App\Http\Controllers\API\
// NpdReceiptController). Russian-only like the market. Own code.
import 'package:url_launcher/url_launcher.dart';

import '../config.dart';

class NpdReceiptsScreen extends StatefulWidget {
  /// Title of the profile menu item that opens this screen.
  static const menuTitle = 'Мои чеки (НПД)';

  const NpdReceiptsScreen({super.key});

  @override
  State<NpdReceiptsScreen> createState() => _NpdReceiptsScreenState();
}

class _NpdReceiptsScreenState extends State<NpdReceiptsScreen> {
  late Future<Map<String, dynamic>?> _data = _load();

  Future<Map<String, dynamic>?> _load() async {
    final r = await apiServices.getApi(api.npdMyReceipts, [], isToken: true);
    return r.isSuccess == true && r.data is Map
        ? Map<String, dynamic>.from(r.data)
        : null;
  }

  String _rub(dynamic v) {
    final n = (double.tryParse('$v') ?? 0).round().toString();
    return '${n.replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (_) => ' ')} ₽';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: const AppBarCommon(title: NpdReceiptsScreen.menuTitle),
      body: FutureBuilder<Map<String, dynamic>?>(
        future: _data,
        builder: (context, snap) {
          if (snap.connectionState != ConnectionState.done) {
            return const Center(child: CircularProgressIndicator());
          }
          final d = snap.data;
          if (d == null) {
            return Center(
                child: TextButton(
                    onPressed: () => setState(() => _data = _load()),
                    child: const Text('Не удалось загрузить. Повторить')));
          }
          final percent = (double.tryParse('${d['percent']}') ?? 0);
          final warn = (double.tryParse('${d['warn_at']}') ?? 0);
          final income = (double.tryParse('${d['income']}') ?? 0);
          final color = percent >= 100
              ? Colors.red
              : income >= warn && warn > 0
                  ? Colors.orange
                  : appColor(context).primary;
          final receipts = (d['receipts'] as List? ?? [])
              .map((e) => Map<String, dynamic>.from(e))
              .toList();

          return RefreshIndicator(
            onRefresh: () async {
              setState(() => _data = _load());
              await _data;
            },
            child: ListView(padding: const EdgeInsets.all(Insets.i20), children: [
              Text('Доход через Clean2Go в ${d['year']} году',
                  style: appCss.dmDenseMedium14
                      .textColor(appColor(context).lightText)),
              const VSpace(Sizes.s8),
              Text('${_rub(d['income'])} из ${_rub(d['limit'])}',
                  style: appCss.dmDenseBold18
                      .textColor(appColor(context).darkText)),
              const VSpace(Sizes.s8),
              ClipRRect(
                borderRadius: BorderRadius.circular(6),
                child: LinearProgressIndicator(
                    value: (percent / 100).clamp(0, 1).toDouble(),
                    minHeight: 8,
                    color: color,
                    backgroundColor: appColor(context).stroke),
              ),
              const VSpace(Sizes.s8),
              Text(
                  percent >= 100
                      ? 'Лимит НПД превышен: для дальнейшей работы нужно сменить налоговый режим (ИП).'
                      : 'Лимит самозанятого — 2,4 млн ₽ в год. Здесь учтён только доход через платформу.',
                  style: appCss.dmDenseRegular12.textColor(
                      percent >= 100 ? Colors.red : appColor(context).lightText)),
              if (d['enabled'] != true) ...[
                const VSpace(Sizes.s15),
                Text('Формирование чеков на платформе пока выключено.',
                    style: appCss.dmDenseRegular12
                        .textColor(appColor(context).lightText)),
              ],
              const VSpace(Sizes.s20),
              Text('Чеки',
                  style: appCss.dmDenseBold16
                      .textColor(appColor(context).darkText)),
              const VSpace(Sizes.s10),
              if (receipts.isEmpty)
                Text('Чеков пока нет',
                    style: appCss.dmDenseRegular14
                        .textColor(appColor(context).lightText)),
              ...receipts.map((r) => ListTile(
                    contentPadding: EdgeInsets.zero,
                    title: Text(
                        'Заказ №${r['booking_number'] ?? ''} · ${r['service_name'] ?? ''}',
                        style: appCss.dmDenseMedium14
                            .textColor(appColor(context).darkText)),
                    subtitle: Text(
                        [
                          '${r['status_label'] ?? ''}',
                          if (r['number'] != null) '№ ${r['number']}',
                          if (r['is_test'] == true) 'тестовый',
                        ].join(' · '),
                        style: appCss.dmDenseRegular12
                            .textColor(appColor(context).lightText)),
                    trailing: Text(_rub(r['amount']),
                        style: appCss.dmDenseMedium14
                            .textColor(appColor(context).darkText)),
                    onTap: r['url'] == null
                        ? null
                        : () => launchUrl(Uri.parse('${r['url']}'),
                            mode: LaunchMode.externalApplication),
                  )),
            ]),
          );
        },
      ),
    );
  }
}
