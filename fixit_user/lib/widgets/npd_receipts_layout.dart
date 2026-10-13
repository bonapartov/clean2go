import 'package:url_launcher/url_launcher.dart';

import '../config.dart';
import '../models/npd_receipt_model.dart';

// «Чеки исполнителей» on the booking screens: number and link of each
// self-employed executor's receipt («Мой налог», ROADMAP Sprint 8).
// Russian-only like the market. Own code.
class NpdReceiptsLayout extends StatelessWidget {
  final List<NpdReceiptModel> receipts;

  const NpdReceiptsLayout({super.key, required this.receipts});

  @override
  Widget build(BuildContext context) {
    if (receipts.isEmpty) return const SizedBox.shrink();

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text('Чеки исполнителей',
              style: appCss.dmDenseSemiBold14
                  .textColor(appColor(context).darkText))
          .paddingOnly(top: Insets.i15, bottom: Insets.i10),
      ...receipts.map((r) => Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${r.executorName} · ИНН ${r.executorInn}',
                            style: appCss.dmDenseMedium14
                                .textColor(appColor(context).darkText)),
                        GestureDetector(
                          onTap: r.url == null
                              ? null
                              : () => launchUrl(Uri.parse(r.url!),
                                  mode: LaunchMode.externalApplication),
                          child: Text(
                              [
                                r.statusLabel,
                                if (r.number != null) '№ ${r.number}',
                                if (r.isTest) 'тестовый, в ФНС не отправлялся',
                              ].join(' · '),
                              style: appCss.dmDenseRegular12.textColor(
                                  r.url == null
                                      ? appColor(context).lightText
                                      : appColor(context).primary)),
                        ),
                      ]),
                ),
                Text(
                    '${getSymbol(context)}${(currency(context).currencyVal * r.amount).toStringAsFixed(2)}',
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).darkText)),
              ]).paddingOnly(bottom: Insets.i10)),
    ]);
  }
}
