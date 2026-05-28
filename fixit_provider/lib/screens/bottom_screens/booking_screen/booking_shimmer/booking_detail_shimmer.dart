import '../../../../config.dart';

class BookingDetailShimmer extends StatelessWidget {
  const BookingDetailShimmer({super.key});

  @override
  Widget build(BuildContext context) {
    return ListView.separated(
        padding: const EdgeInsets.all(Insets.i20),
        physics: const NeverScrollableScrollPhysics(),
        shrinkWrap: true,
        itemCount: 6,
        separatorBuilder: (_, __) => const VSpace(Sizes.s10),
        itemBuilder: (_, __) => Row(children: [
              CommonSkeleton(
                  height: Sizes.s55,
                  width: Sizes.s55,
                  radius: AppRadius.r10),
              const HSpace(Sizes.s10),
              Expanded(
                  child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                    CommonSkeleton(
                        height: Sizes.s15,
                        width: double.infinity,
                        radius: AppRadius.r5),
                    const VSpace(Sizes.s5),
                    CommonSkeleton(
                        height: Sizes.s12,
                        width: Sizes.s120,
                        radius: AppRadius.r5),
                  ]))
            ]));
  }
}
