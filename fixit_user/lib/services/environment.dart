import 'dart:developer';

import '../config.dart';

// Backend address is supplied at build time, never hardcoded here:
//   flutter build apk --dart-define=BACKEND_URL=http://192.168.1.127:8001
// Everything else the app needs comes from the backend settings API.
const String backendUrl = String.fromEnvironment('BACKEND_URL');
String apiUrl = "$backendUrl/api";
String paymentUrl = "$backendUrl/";

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
