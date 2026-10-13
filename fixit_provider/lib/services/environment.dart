//FOR DATA ENTRY
import 'dart:developer';
import 'dart:io';

import '../config.dart';

// Backend address is supplied at build time, never hardcoded here:
//   flutter build apk --dart-define=BACKEND_URL=http://192.168.1.127:8001
// Everything else the app needs comes from the backend settings API.
const String backendUrl = String.fromEnvironment('BACKEND_URL');
String apiUrl = "$backendUrl/api";
String paymentUrl = "$backendUrl/";
String providerAppUrl = "$backendUrl/";
// Global SharedPreferences and Locale
late SharedPreferences sharedPreferences;
String local = appSettingModel!.general!.defaultLanguage!.locale!;

// Initialize SharedPreferences and Locale
Future<void> initializeAppSettings() async {
  sharedPreferences = await SharedPreferences.getInstance();
  local = sharedPreferences.getString('selectedLocale') ?? "en";
  log("set language:: $local");
}

Map<String, String>? headersToken(
  String? token, {
  bool isLang = false,
  String? localLang,
}) => {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  "Accept-Lang": isLang ? "$localLang" : 'en',
  "Authorization": "Bearer $token",
};

Map<String, String>? get headers => {
  'Accept': 'application/json',
  'Content-Type': 'application/json',
  "Accept-Lang": local,
};
