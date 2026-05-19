import 'dart:convert';
import 'dart:developer';
import 'package:http/http.dart' as http;

import '../../../../config.dart';

class SearchLocation extends StatefulWidget {
  const SearchLocation({super.key});

  @override
  State<SearchLocation> createState() => _SearchLocationState();
}

class _SearchLocationState extends State<SearchLocation> {
  List placePredictions = [];
  FocusNode focusNode = FocusNode();
  TextEditingController search = TextEditingController();

  placeAutoComplete(query) async {
    if (query == null || query.toString().isEmpty) return;
    final apiKey = appSettingModel!.firebase!.yandexMapApiKey ?? '';
    final uri = Uri.parse(
        "https://suggest-maps.yandex.ru/v1/suggest?apikey=$apiKey&text=${Uri.encodeComponent(query.toString())}&lang=ru_RU&results=7");

    try {
      var res = await http.get(uri);
      log("yandex suggest result :${res.body}");
      if (res.statusCode == 200) {
        final body = jsonDecode(res.body);
        final results = (body['results'] as List?) ?? [];
        setState(() {
          placePredictions = results.map((item) {
            final title = item['title']?['text'] ?? '';
            final subtitle = item['subtitle']?['text'] ?? '';
            final fullText = subtitle.isNotEmpty ? '$title, $subtitle' : title;
            return {'description': fullText, 'uri': item['uri'] ?? fullText};
          }).toList();
        });
      } else {
        log("Yandex Suggest error :${res.body}");
      }
    } catch (e) {
      log("Yandex Suggest exception: $e");
    }
  }

  findCord(context, String addressText) async {
    final apiKey = appSettingModel!.firebase!.yandexMapApiKey ?? '';
    final uri = Uri.parse(
        "https://geocode-maps.yandex.ru/1.x/?apikey=$apiKey&geocode=${Uri.encodeComponent(addressText)}&format=json&results=1");

    try {
      var d = await http.get(uri);
      dynamic a = jsonDecode(d.body);
      log("yandex geocode result :$a");
      final pos = a['response']?['GeoObjectCollection']?['featureMember']
          ?[0]?['GeoObject']?['Point']?['pos'];
      if (pos != null) {
        final parts = pos.toString().split(' ');
        final lng = double.parse(parts[0]);
        final lat = double.parse(parts[1]);
        route.pop(context, arg: LatLng(lat, lng));
      }
    } catch (e) {
      log("Yandex Geocode exception: $e");
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
        appBar: AppBarCommon(title: language(context, translations!.location)),
        body: ListView(
          children: [
            TextFieldCommon(
                    border: OutlineInputBorder(
                        borderSide:
                            BorderSide(color: appColor(context).stroke)),
                    focusNode: focusNode,
                    onChanged: (v) => placeAutoComplete(v),
                    controller: search,
                    hintText: language(context, translations!.searchHere),
                    prefixIcon: eSvgAssets.location)
                .paddingSymmetric(horizontal: Insets.i20),
            const VSpace(Sizes.s20),
            Divider(color: appColor(context).stroke, height: 0),
            if (placePredictions.isNotEmpty) const VSpace(Sizes.s20),
            ButtonCommon(
                margin: 20,
                onTap: () => route.pop(context),
                title: language(context, translations!.useCurrentLocation),
                icon: SvgPicture.asset(eSvgAssets.zipcode,
                    colorFilter: ColorFilter.mode(
                        appColor(context).whiteBg, BlendMode.srcIn))),
            const VSpace(Sizes.s20),
            ...placePredictions.asMap().entries.map((e) => LocationListTile(
                loc: e.value['description'],
                onTap: () {
                  log("dvghh:${e.value}");
                  findCord(context, e.value['uri'] ?? e.value['description']);
                })),
          ],
        ));
  }
}

class LocationListTile extends StatelessWidget {
  final String? loc;
  final GestureTapCallback? onTap;

  const LocationListTile({super.key, this.loc, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            SvgPicture.asset(eSvgAssets.location),
            const HSpace(Sizes.s10),
            Expanded(child: Text(loc ?? "")),
          ],
        ).inkWell(onTap: onTap),
        Divider(
          color: appColor(context).stroke,
          height: 0,
        ).paddingSymmetric(vertical: Sizes.s15)
      ],
    ).paddingSymmetric(horizontal: Sizes.s20);
  }
}
