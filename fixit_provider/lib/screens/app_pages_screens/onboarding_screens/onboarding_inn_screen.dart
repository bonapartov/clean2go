// ignore_for_file: use_build_context_synchronously
import '../../../config.dart';
import '../../../providers/app_pages_provider/onboarding_provider.dart';

class OnboardingInnScreen extends StatelessWidget {
  const OnboardingInnScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<OnboardingProvider>(builder: (context, value, child) {
      return Scaffold(
        appBar: AppBarCommon(title: 'Верификация ИНН'),
        body: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const VSpace(Sizes.s20),
              Text(
                'Шаг 1 из 5 — Введите ваш ИНН',
                style: appCss.dmDenseMedium16
                    .textColor(appColor(context).appTheme.darkText),
              ),
              const VSpace(Sizes.s8),
              Text(
                'Мы проверим ваш статус в ФНС (самозанятый, ИП или ООО) '
                'и подготовим подходящий договор.',
                style: appCss.dmDenseRegular14
                    .textColor(appColor(context).appTheme.lightText),
              ),
              const VSpace(Sizes.s24),
              Form(
                key: value.innFormKey,
                child: TextFieldCommon(
                  controller: value.innController,
                  hintText: 'ИНН (10 или 12 цифр)',
                  keyboardType: TextInputType.number,
                  maxLength: 12,
                  validator: (v) {
                    if (v == null || v.isEmpty) return 'Введите ИНН';
                    if (v.length != 10 && v.length != 12) {
                      return 'ИНН должен содержать 10 или 12 цифр';
                    }
                    if (!RegExp(r'^\d+$').hasMatch(v)) {
                      return 'ИНН должен содержать только цифры';
                    }
                    return null;
                  },
                ),
              ),
              if (value.innError != null) ...[
                const VSpace(Sizes.s8),
                Text(
                  value.innError!,
                  style: appCss.dmDenseRegular13
                      .textColor(appColor(context).appTheme.red),
                ),
              ],
              const VSpace(Sizes.s32),
              ButtonCommon(
                title: 'Проверить ИНН',
                isLoading: value.isLoading,
                onTap: () => value.submitInn(context),
              ),
            ],
          ).paddingAll(Insets.i20),
        ),
      );
    });
  }
}
