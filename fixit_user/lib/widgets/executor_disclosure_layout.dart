import '../config.dart';
import '../models/executor_model.dart';

// "Исполнитель услуги" block on the payment screen: name, tax status and
// ИНН of every provider in the order, before the client pays (agency model
// B2C, ROADMAP Sprint 6). Status labels come translated from the backend;
// the few fixed words here are Russian-only like the market.
class ExecutorDisclosureLayout extends StatelessWidget {
  final List<ExecutorModel> executors;

  const ExecutorDisclosureLayout({super.key, required this.executors});

  @override
  Widget build(BuildContext context) {
    if (executors.isEmpty) return const SizedBox.shrink();

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(executors.length == 1 ? 'Исполнитель услуги' : 'Исполнители услуг',
          style: appCss.dmDenseSemiBold14
              .textColor(appColor(context).lightText)),
      const VSpace(Sizes.s8),
      ...executors.map((e) => Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(e.name ?? '',
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).darkText)),
                if (e.verified) ...[
                  Text(e.statusLabel ?? '',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).lightText)),
                  Text(
                      'ИНН ${e.inn ?? ''}'
                      '${e.ogrn != null ? ', ОГРН/ОГРНИП ${e.ogrn}' : ''}',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).lightText)),
                ] else
                  Text('Данные исполнителя ещё не проверены',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).lightText)),
              ]).paddingOnly(bottom: Insets.i10)),
    ]).paddingSymmetric(horizontal: Insets.i20, vertical: Insets.i10);
  }
}
