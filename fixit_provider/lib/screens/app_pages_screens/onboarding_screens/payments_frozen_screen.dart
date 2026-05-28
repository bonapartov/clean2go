// ignore_for_file: use_build_context_synchronously
import '../../../config.dart';
import '../../../providers/app_pages_provider/onboarding_provider.dart';

class PaymentsFrozenScreen extends StatelessWidget {
  const PaymentsFrozenScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<OnboardingProvider>(builder: (context, value, child) {
      return Scaffold(
        appBar: AppBarCommon(title: 'Выплаты заморожены'),
        body: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const VSpace(Sizes.s32),
              Icon(Icons.lock_outline,
                  size: 72, color: appColor(context).appTheme.red),
              const VSpace(Sizes.s20),
              Text(
                'Выплаты временно заморожены',
                style: appCss.dmDenseMedium18
                    .textColor(appColor(context).appTheme.darkText),
                textAlign: TextAlign.center,
              ),
              const VSpace(Sizes.s12),
              if (value.paymentsFrozenReason.isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.all(Insets.i14),
                  decoration: BoxDecoration(
                    color: appColor(context).appTheme.red.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(AppRadius.r10),
                  ),
                  child: Text(
                    value.paymentsFrozenReason,
                    style: appCss.dmDenseRegular13
                        .textColor(appColor(context).appTheme.red),
                    textAlign: TextAlign.center,
                  ),
                ),
                const VSpace(Sizes.s20),
              ],
              Text(
                'Выберите один из вариантов для разблокировки выплат:',
                style: appCss.dmDenseRegular14
                    .textColor(appColor(context).appTheme.lightText),
                textAlign: TextAlign.center,
              ),
              const VSpace(Sizes.s32),

              // Вариант 1: Восстановить НПД
              _OptionCard(
                icon: Icons.refresh,
                title: 'Восстановить НПД',
                subtitle: 'Если вы снова зарегистрировались как самозанятый '
                    'в приложении «Мой налог»',
                color: appColor(context).appTheme.primary,
                isLoading: value.isLoading,
                onTap: () => value.reportNpdRestored(context),
              ),
              const VSpace(Sizes.s16),

              // Вариант 2: Стать ИП
              _OptionCard(
                icon: Icons.business_center_outlined,
                title: 'Стать ИП',
                subtitle: 'Зарегистрируйте ИП и пройдите верификацию заново',
                color: const Color(0xFFFF9800),
                onTap: () => route.pushNamed(context, routeName.onboardingInn),
              ),
              const VSpace(Sizes.s16),

              // Вариант 3: Подписать ГПХ
              _OptionCard(
                icon: Icons.description_outlined,
                title: 'Подписать договор ГПХ',
                subtitle: 'Продолжить работу как физлицо. Платформа удержит '
                    'НДФЛ 13% с каждой выплаты.',
                color: appColor(context).appTheme.darkText,
                onTap: () =>
                    route.pushNamed(context, routeName.onboardingContract),
              ),
            ],
          ).paddingAll(Insets.i20),
        ),
      );
    });
  }
}

class _OptionCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;
  final bool isLoading;

  const _OptionCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.color,
    required this.onTap,
    this.isLoading = false,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: isLoading ? null : onTap,
      child: Container(
        padding: const EdgeInsets.all(Insets.i16),
        decoration: BoxDecoration(
          color: appColor(context).appTheme.whiteBg,
          borderRadius: BorderRadius.circular(AppRadius.r12),
          border: Border.all(color: color.withOpacity(0.4)),
          boxShadow: [
            BoxShadow(
              color: color.withOpacity(0.08),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(children: [
          Container(
            padding: const EdgeInsets.all(Insets.i10),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(AppRadius.r8),
            ),
            child: Icon(icon, color: color, size: Sizes.s24),
          ),
          const HSpace(Sizes.s14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: appCss.dmDenseMedium14
                        .textColor(appColor(context).appTheme.darkText)),
                const VSpace(Sizes.s4),
                Text(subtitle,
                    style: appCss.dmDenseRegular12
                        .textColor(appColor(context).appTheme.lightText)),
              ],
            ),
          ),
          if (isLoading)
            const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2))
          else
            Icon(Icons.chevron_right,
                color: appColor(context).appTheme.lightText),
        ]),
      ),
    );
  }
}
