// ignore_for_file: use_build_context_synchronously
import '../../../config.dart';
import '../../../providers/app_pages_provider/onboarding_provider.dart';

class OnboardingPassportScreen extends StatelessWidget {
  const OnboardingPassportScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Consumer<OnboardingProvider>(builder: (context, value, child) {
      return Scaffold(
        appBar: AppBarCommon(title: 'Паспортные данные'),
        body: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const VSpace(Sizes.s20),
              Text(
                'Шаг 3 из 5 — Загрузите паспорт',
                style: appCss.dmDenseMedium16
                    .textColor(appColor(context).appTheme.darkText),
              ),
              const VSpace(Sizes.s8),
              Text(
                'Необходимо фото разворота паспорта (страница с фото) '
                'и ваше селфи с паспортом в руке.',
                style: appCss.dmDenseRegular14
                    .textColor(appColor(context).appTheme.lightText),
              ),
              const VSpace(Sizes.s24),

              // Фото паспорта
              _ImagePickerCard(
                title: 'Фото паспорта',
                subtitle: 'Разворот с фотографией',
                image: value.passportPhoto,
                onTap: () => _showSourcePicker(context, (source) {
                  value.pickPassportPhoto(context, source);
                }),
              ),
              const VSpace(Sizes.s16),

              // Селфи с паспортом
              _ImagePickerCard(
                title: 'Селфи с паспортом',
                subtitle: 'Ваше лицо и паспорт в кадре',
                image: value.passportSelfie,
                onTap: () => _showSourcePicker(context, (source) {
                  value.pickPassportSelfie(context, source);
                }),
              ),

              const VSpace(Sizes.s32),
              ButtonCommon(
                title: 'Отправить на проверку',
                isLoading: value.isLoading,
                onTap: () => value.submitPassport(context),
              ),
            ],
          ).paddingAll(Insets.i20),
        ),
      );
    });
  }

  void _showSourcePicker(BuildContext context, Function(ImageSource) onSelect) {
    showLayout(context, onTap: (index) {
      onSelect(index == 0 ? ImageSource.gallery : ImageSource.camera);
    });
  }
}

class _ImagePickerCard extends StatelessWidget {
  final String title;
  final String subtitle;
  final XFile? image;
  final VoidCallback onTap;

  const _ImagePickerCard({
    required this.title,
    required this.subtitle,
    required this.image,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: double.infinity,
        height: 140,
        decoration: BoxDecoration(
          color: appColor(context).appTheme.fieldCardBg,
          borderRadius: BorderRadius.circular(AppRadius.r12),
          border: Border.all(
            color: image != null
                ? appColor(context).appTheme.primary
                : appColor(context).appTheme.stroke,
            width: image != null ? 2 : 1,
          ),
        ),
        child: image != null
            ? ClipRRect(
                borderRadius: BorderRadius.circular(AppRadius.r12),
                child: Image.network(image!.path, fit: BoxFit.cover),
              )
            : Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.add_a_photo_outlined,
                      size: 36, color: appColor(context).appTheme.primary),
                  const VSpace(Sizes.s8),
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
    );
  }
}
