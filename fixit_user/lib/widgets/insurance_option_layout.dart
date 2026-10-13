import '../config.dart';

// «Застраховать услугу» checkbox in the cart (ROADMAP Sprint 6, agency model
// B2C). Shown only when the admin enabled it (/api/settings → insurance).
// Ticking recalculates the cart: the backend adds the insurance price to the
// total (App\Support\ServiceInsurance). Promise wording: only "insured under
// the policy terms" — never "we guarantee". Russian-only like the market.
class InsuranceOptionLayout extends StatelessWidget {
  const InsuranceOptionLayout({super.key});

  @override
  Widget build(BuildContext context) {
    final insurance = appSettingModel?.insurance;
    if (insurance == null || !insurance.isAvailable) {
      return const SizedBox.shrink();
    }

    return Consumer<CartProvider>(builder: (context1, cart, child) {
      final price = insurance.isPercentage
          ? '${insurance.price.toStringAsFixed(insurance.price % 1 == 0 ? 0 : 1)}% от суммы'
          : '${getSymbol(context)}${(currency(context).currencyVal * insurance.price).toStringAsFixed(2)}';

      return GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTap: () => cart.onInsuranceChange(context, !cart.isInsured),
        child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
          CheckBoxCommon(isCheck: cart.isInsured),
          const HSpace(Sizes.s10),
          Expanded(
            child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Застраховать услугу ($price)',
                      style: appCss.dmDenseMedium14
                          .textColor(appColor(context).darkText)),
                  Text(
                      '${insurance.insurerName.isNotEmpty ? 'Услуга будет застрахована в ${insurance.insurerName} по условиям полиса. ' : ''}'
                      'Покрываются только работы, оформленные на платформе.',
                      style: appCss.dmDenseRegular12
                          .textColor(appColor(context).lightText)),
                ]),
          ),
        ]),
      ).paddingSymmetric(horizontal: Insets.i20);
    });
  }
}
