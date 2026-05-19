import 'dart:developer';

import '../config.dart';

String apiUrl = "Enter your Url";
String paymentUrl = "Enter your payment url"; //

String playstoreUrl =
    "Enter your playstore app url";

String userAppPlayStoreUrl =
    "enter your user app play store url";

late SharedPreferences sharedPreferences;
String local = appSettingModel!.general!.defaultLanguage!.locale!;

// Initialize SharedPreferences and Locale
Future<void> initializeAppSettings() async {
  sharedPreferences = await SharedPreferences.getInstance();
  local = sharedPreferences.getString('s') ?? 'en';
  log("set language:: $local");
}

// Headers Token Function
Map<String, String>? headersToken(String? token) => {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      "Accept-Lang": local,
      "Authorization": "Bearer $token",
    };

// Default Headers
Map<String, String>? get headers => {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      "Accept-Lang": local,
    };
