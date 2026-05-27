import '../../../config.dart';
import '../../../providers/app_pages_provider/onboarding_provider.dart';

class OnboardingPendingScreen extends StatelessWidget {
  const OnboardingPendingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<OnboardingProvider>(builder: (context, value, child) {
      final isApproved = value.passportStatus == 'approved';
      return Scaffold(
        appBar: AppBarCommon(title: 'Статус верификации'),
        body: Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                isApproved ? Icons.check_circle_outline : Icons.hourglass_top,
                size: 80,
                color: isApproved
                    ? appColor(context).appTheme.primary
                    : appColor(context).appTheme.orange,
              ),
              const VSpace(Sizes.s24),
              Text(
                isApproved ? 'Паспорт подтверждён!' : 'Документы на проверке',
                style: appCss.dmDenseMedium20
                    .textColor(appColor(context).appTheme.darkText),
                textAlign: TextAlign.center,
              ),
              const VSpace(Sizes.s12),
              Text(
                isApproved
                    ? 'Теперь подпишите договор.'
                    : 'Обычно это занимает до 24 часов. '
                        'Мы пришлём уведомление, когда проверка завершится.',
                style: appCss.dmDenseRegular14
                    .textColor(appColor(context).appTheme.lightText),
                textAlign: TextAlign.center,
              ).paddingSymmetric(horizontal: Insets.i32),
              const VSpace(Sizes.s40),
              if (isApproved)
                ButtonCommon(
                  title: 'Подписать договор',
                  onTap: () =>
                      route.pushNamed(context, routeName.onboardingContract),
                ).paddingSymmetric(horizontal: Insets.i20)
              else ...[
                ButtonCommon(
                  title: 'Проверить статус',
                  isLoading: value.isLoading,
                  onTap: () => value.fetchStatus(context),
                ).paddingSymmetric(horizontal: Insets.i20),
              ],
            ],
          ),
        ),
      );
    });
  }
}
