import 'dart:developer';

import '../config.dart';

/// Nudge shown on the provider dashboard once a `freelancer` (самозанятый)
/// provider's serviceman count reaches 2 or more — under Russian tax law
/// they can no longer stay registered as self-employed and must switch to
/// ИП/ООО. Dismissal is persisted per serviceman-count so it stays hidden
/// until the count changes again (e.g. they hire someone else later).
class FreelancerEmployeeNotice extends StatefulWidget {
  final int servicemanCount;

  const FreelancerEmployeeNotice({super.key, required this.servicemanCount});

  @override
  State<FreelancerEmployeeNotice> createState() =>
      _FreelancerEmployeeNoticeState();
}

class _FreelancerEmployeeNoticeState extends State<FreelancerEmployeeNotice> {
  bool _isReady = false;
  bool _isDismissed = false;

  @override
  void initState() {
    super.initState();
    _loadDismissState();
  }

  Future<void> _loadDismissState() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      int dismissedAtCount =
          pref.getInt(session.freelancerEmployeeNoticeDismissedCount) ?? 0;
      if (!mounted) return;
      setState(() {
        _isDismissed = dismissedAtCount >= widget.servicemanCount;
        _isReady = true;
      });
    } catch (e) {
      log("FreelancerEmployeeNotice _loadDismissState: $e");
      if (!mounted) return;
      setState(() => _isReady = true);
    }
  }

  Future<void> _dismiss() async {
    try {
      SharedPreferences pref = await SharedPreferences.getInstance();
      pref.setInt(session.freelancerEmployeeNoticeDismissedCount,
          widget.servicemanCount);
    } catch (e) {
      log("FreelancerEmployeeNotice _dismiss: $e");
    }
    if (!mounted) return;
    setState(() => _isDismissed = true);
  }

  @override
  Widget build(BuildContext context) {
    if (!_isReady || _isDismissed) return const SizedBox.shrink();

    return Column(children: [
      Stack(children: [
        Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(
                  "У вас появились сотрудники — самозанятые не могут иметь "
                  "наёмных работников. Обновите тип регистрации в "
                  "настройках профиля.",
                  style: appCss.dmDenseRegular14
                      .textColor(appColor(context).appTheme.red))
              .paddingOnly(right: Sizes.s20),
          const VSpace(Sizes.s10),
          Text("Обновить тип регистрации",
                  style: appCss.dmDenseBold14
                      .textColor(appColor(context).appTheme.red))
              .inkWell(
                  onTap: () =>
                      route.pushNamed(context, routeName.onboardingInn)),
        ]).paddingAll(Sizes.s20),
        Positioned(
            top: Insets.i8,
            right: Insets.i8,
            child: SvgPicture.asset(eSvgAssets.cross,
                    height: Sizes.s14,
                    colorFilter: ColorFilter.mode(
                        appColor(context).appTheme.red, BlendMode.srcIn))
                .paddingAll(Insets.i6)
                .inkWell(onTap: _dismiss)),
      ]).boxShapeExtension(
          color: appColor(context).appTheme.red.withValues(alpha: .10),
          radius: 8),
      const VSpace(Sizes.s15)
    ]).padding(horizontal: Sizes.s20).width(MediaQuery.sizeOf(context).width);
  }
}
