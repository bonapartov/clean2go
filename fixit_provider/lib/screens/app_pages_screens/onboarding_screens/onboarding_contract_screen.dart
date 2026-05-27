// ignore_for_file: use_build_context_synchronously
import '../../../config.dart';
import '../../../providers/app_pages_provider/onboarding_provider.dart';

class OnboardingContractScreen extends StatelessWidget {
  const OnboardingContractScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<OnboardingProvider>(builder: (context, value, child) {
      return Scaffold(
        appBar: AppBarCommon(title: 'Подписание договора'),
        body: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const VSpace(Sizes.s20),
              Text(
                'Шаг 5 из 5 — Подпишите договор',
                style: appCss.dmDenseMedium16
                    .textColor(appColor(context).appTheme.darkText),
              ),
              const VSpace(Sizes.s8),
              Text(
                'Мы подготовили договор на основе ваших данных. '
                'Нажмите «Получить SMS-код», прочитайте условия и подпишите.',
                style: appCss.dmDenseRegular14
                    .textColor(appColor(context).appTheme.lightText),
              ),
              const VSpace(Sizes.s24),

              // Кнопка получить SMS
              if (!value.smsSent)
                ButtonCommon(
                  title: 'Получить SMS-код',
                  isLoading: value.isLoading,
                  onTap: () => value.generateAndSendSms(context),
                )
              else ...[
                Container(
                  padding: const EdgeInsets.all(Insets.i16),
                  decoration: BoxDecoration(
                    color: appColor(context).appTheme.primary.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(AppRadius.r10),
                  ),
                  child: Row(children: [
                    Icon(Icons.check_circle,
                        color: appColor(context).appTheme.primary,
                        size: Sizes.s20),
                    const HSpace(Sizes.s8),
                    Text('SMS-код отправлен на ваш номер',
                        style: appCss.dmDenseRegular13.textColor(
                            appColor(context).appTheme.primary)),
                  ]),
                ),
                const VSpace(Sizes.s24),
                Text(
                  'Введите код из SMS',
                  style: appCss.dmDenseMedium14
                      .textColor(appColor(context).appTheme.darkText),
                ),
                const VSpace(Sizes.s8),
                Form(
                  key: value.contractFormKey,
                  child: TextFieldCommon(
                    controller: value.smsCodeController,
                    hintText: '6-значный код',
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    validator: (v) {
                      if (v == null || v.isEmpty) return 'Введите код';
                      if (v.length < 4) return 'Код слишком короткий';
                      return null;
                    },
                  ),
                ),
                const VSpace(Sizes.s24),
                ButtonCommon(
                  title: 'Подписать договор',
                  isLoading: value.isLoading,
                  onTap: () => value.signContract(context),
                ),
                const VSpace(Sizes.s16),
                Center(
                  child: GestureDetector(
                    onTap: () => value.generateAndSendSms(context),
                    child: Text(
                      'Отправить код повторно',
                      style: appCss.dmDenseRegular13.textColor(
                          appColor(context).appTheme.primary),
                    ),
                  ),
                ),
              ],
            ],
          ).paddingAll(Insets.i20),
        ),
      );
    });
  }
}
