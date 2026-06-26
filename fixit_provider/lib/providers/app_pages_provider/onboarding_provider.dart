// ignore_for_file: use_build_context_synchronously
import 'dart:convert';
import 'dart:developer';
import 'package:dio/dio.dart' as dio;
import 'package:fixit_provider/config.dart';

class OnboardingProvider extends ChangeNotifier {
  // ── State ──────────────────────────────────────────────────────────────────
  bool isLoading = false;

  // Step 1 — INN
  final innController = TextEditingController();
  final innFormKey    = GlobalKey<FormState>();
  String? taxpayerType;
  String? legalName;
  String? innError;

  // Step 3 — Passport
  XFile? passportPhoto;
  XFile? passportSelfie;
  String passportStatus = 'not_uploaded'; // not_uploaded | pending | approved | rejected

  // Step 5 — Contract
  final smsCodeController = TextEditingController();
  final contractFormKey   = GlobalKey<FormState>();
  bool smsSent = false;
  String? contractUrl;

  // Status
  bool   onboardingCompleted = false;
  int    onboardingStep      = 0;
  bool   paymentsFrozen      = false;
  String paymentsFrozenReason = '';
  String onboardingStatus    = '';

  void _setLoading(bool v) { isLoading = v; notifyListeners(); }

  // ── API: fetch status ──────────────────────────────────────────────────────
  Future<void> fetchStatus(BuildContext context) async {
    _setLoading(true);
    try {
      final res = await apiServices.getApi(api.onboardingStatus, null, isToken: true, isData: true);
      if (res.isSuccess == true && res.data != null) {
        final d = res.data is String ? jsonDecode(res.data) : res.data;
        onboardingCompleted = d['onboarding_completed'] == true;
        onboardingStep      = d['onboarding_step'] ?? 0;
        taxpayerType        = d['taxpayer_type'];
        passportStatus      = d['passport_status'] ?? 'not_uploaded';
        paymentsFrozen      = d['payments_frozen'] == true;
        paymentsFrozenReason = d['payments_frozen_reason'] ?? '';
        onboardingStatus    = d['onboarding_status'] ?? '';
        notifyListeners();
      }
    } catch (e) {
      log('onboarding fetchStatus error: $e');
    } finally {
      _setLoading(false);
    }
  }

  // ── API: check INN ─────────────────────────────────────────────────────────
  Future<void> submitInn(BuildContext context) async {
    if (!innFormKey.currentState!.validate()) return;
    innError = null;
    _setLoading(true);
    showLoading(context);
    try {
      final body = jsonEncode({'inn': innController.text.trim()});
      final res  = await apiServices.postApi(api.onboardingInn, body, isToken: true);
      hideLoading(context);
      if (res.isSuccess == true && res.data != null) {
        final d = res.data is String ? jsonDecode(res.data) : res.data;
        if (d['error'] != null) {
          innError = d['message'];
          notifyListeners();
          return;
        }
        taxpayerType = d['taxpayer_type'];
        legalName    = d['legal_name'];
        onboardingStep = d['next_step'] ?? 3;
        notifyListeners();
        route.pushNamed(context, routeName.onboardingPassport);
      } else {
        innError = res.message ?? 'Ошибка проверки ИНН';
        notifyListeners();
      }
    } catch (e) {
      hideLoading(context);
      innError = 'Ошибка соединения. Попробуйте позже.';
      notifyListeners();
      log('submitInn error: $e');
    } finally {
      _setLoading(false);
    }
  }

  // ── Passport: pick images ──────────────────────────────────────────────────
  Future<void> pickPassportPhoto(BuildContext context, ImageSource source) async {
    final picker = ImagePicker();
    final img = await picker.pickImage(source: source, imageQuality: 80);
    if (img != null) { passportPhoto = img; notifyListeners(); }
  }

  Future<void> pickPassportSelfie(BuildContext context, ImageSource source) async {
    final picker = ImagePicker();
    final img = await picker.pickImage(source: source, imageQuality: 80);
    if (img != null) { passportSelfie = img; notifyListeners(); }
  }

  // ── API: upload passport ───────────────────────────────────────────────────
  Future<void> submitPassport(BuildContext context) async {
    if (passportPhoto == null || passportSelfie == null) {
      snackBarMessengers(context,
          message: 'Загрузите фото паспорта и селфи',
          color: appColor(context).appTheme.red);
      return;
    }
    _setLoading(true);
    showLoading(context);
    try {
      final formData = dio.FormData.fromMap({
        'passport_photo': await dio.MultipartFile.fromFile(
            passportPhoto!.path, filename: passportPhoto!.path.split('/').last),
        'passport_selfie': await dio.MultipartFile.fromFile(
            passportSelfie!.path, filename: passportSelfie!.path.split('/').last),
      });
      final res = await apiServices.postApi(
          api.onboardingPassport, formData, isToken: true);
      hideLoading(context);
      if (res.isSuccess == true) {
        passportStatus = 'pending';
        notifyListeners();
        route.pushNamed(context, routeName.onboardingPending);
      } else {
        snackBarMessengers(context,
            message: res.message ?? 'Ошибка загрузки паспорта',
            color: appColor(context).appTheme.red);
      }
    } catch (e) {
      hideLoading(context);
      log('submitPassport error: $e');
      snackBarMessengers(context,
          message: 'Ошибка соединения', color: appColor(context).appTheme.red);
    } finally {
      _setLoading(false);
    }
  }

  // ── API: contract ──────────────────────────────────────────────────────────
  Future<void> generateAndSendSms(BuildContext context) async {
    _setLoading(true);
    showLoading(context);
    try {
      await apiServices.postApi(
          api.onboardingContractGenerate, '{}', isToken: true);
      final res = await apiServices.postApi(
          api.onboardingContractSendSms, '{}', isToken: true);
      hideLoading(context);
      if (res.isSuccess == true) {
        smsSent = true;
        notifyListeners();
        snackBarMessengers(context,
            message: 'SMS-код отправлен',
            color: appColor(context).appTheme.primary);
      } else {
        snackBarMessengers(context,
            message: res.message ?? 'Ошибка отправки SMS',
            color: appColor(context).appTheme.red);
      }
    } catch (e) {
      hideLoading(context);
      log('generateAndSendSms error: $e');
    } finally {
      _setLoading(false);
    }
  }

  Future<void> signContract(BuildContext context) async {
    if (!contractFormKey.currentState!.validate()) return;
    _setLoading(true);
    showLoading(context);
    try {
      final body = jsonEncode({'sms_code': smsCodeController.text.trim()});
      final res  = await apiServices.postApi(
          api.onboardingContractSign, body, isToken: true);
      hideLoading(context);
      if (res.isSuccess == true) {
        onboardingCompleted = true;
        notifyListeners();
        snackBarMessengers(context,
            message: 'Договор подписан! Добро пожаловать.',
            color: appColor(context).appTheme.primary);
        route.pushReplacementNamed(context, routeName.dashboard);
      } else {
        snackBarMessengers(context,
            message: res.message ?? 'Неверный код',
            color: appColor(context).appTheme.red);
      }
    } catch (e) {
      hideLoading(context);
      log('signContract error: $e');
    } finally {
      _setLoading(false);
    }
  }

  // ── Payments frozen: NPD actions ───────────────────────────────────────────
  Future<void> reportNpdRestored(BuildContext context) async {
    _setLoading(true);
    showLoading(context);
    try {
      final res = await apiServices.postApi(
          api.onboardingNpdLost, jsonEncode({'action': 'check'}), isToken: true);
      hideLoading(context);
      final d = res.data is String ? jsonDecode(res.data ?? '{}') : (res.data ?? {});
      if (d['npd_status'] == 'active') {
        paymentsFrozen = false;
        notifyListeners();
        snackBarMessengers(context,
            message: 'НПД восстановлен, выплаты разморожены',
            color: appColor(context).appTheme.primary);
        route.pushReplacementNamed(context, routeName.dashboard);
      } else {
        snackBarMessengers(context,
            message: 'НПД ещё не активен. Проверьте в приложении ФНС.',
            color: appColor(context).appTheme.red);
      }
    } catch (e) {
      hideLoading(context);
      log('reportNpdRestored error: $e');
    } finally {
      _setLoading(false);
    }
  }
}
