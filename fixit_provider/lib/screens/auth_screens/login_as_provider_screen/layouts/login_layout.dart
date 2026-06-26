import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import '../../../../config.dart';

class LoginLayout extends StatelessWidget {
  final TapGestureRecognizer? recognizer;
  final bool? isProvider;
  final VoidCallback? onForget;

  const LoginLayout({
    super.key,
    this.recognizer,
    this.isProvider,
    this.onForget,
  });

  @override
  Widget build(BuildContext context) {
    return Consumer<LoginAsProvider>(builder: (context, value, child) {
      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          TextFormField(
            controller: value.emailController,
            focusNode: value.emailFocus,
            keyboardType: TextInputType.emailAddress,
            textInputAction: TextInputAction.next,
            decoration: InputDecoration(
              labelText: translations?.email ?? 'Email',
              hintText: translations?.enterEmail ?? 'Enter Email',
              prefixIcon: const Icon(Icons.email_outlined),
              border: const OutlineInputBorder(),
            ),
            validator: (v) {
              if (v == null || v.isEmpty) return translations?.pleaseEnterEmail ?? 'Enter Email';
              return null;
            },
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: value.passwordController,
            focusNode: value.passwordFocus,
            obscureText: value.isPassword,
            textInputAction: TextInputAction.done,
            decoration: InputDecoration(
              labelText: translations?.password ?? 'Password',
              hintText: translations?.enterPassword ?? 'Enter Password',
              prefixIcon: const Icon(Icons.lock_outline),
              suffixIcon: IconButton(
                icon: Icon(value.isPassword ? Icons.visibility_off_outlined : Icons.visibility_outlined),
                onPressed: value.passwordSeenTap,
              ),
              border: const OutlineInputBorder(),
            ),
            validator: (v) {
              if (v == null || v.isEmpty) return translations?.pleaseEnterPassword ?? 'Enter Password';
              return null;
            },
          ),
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: onForget,
              child: Text(
                translations?.forgotPassword ?? 'Forgot Password?',
                style: TextStyle(color: appColor(context).appTheme.primary),
              ),
            ),
          ),
          const SizedBox(height: 8),
          ButtonCommon(
            title: translations?.login ?? 'LOGIN',
            onTap: () => value.login(context),
          ),
          const SizedBox(height: 16),
          Center(
            child: RichText(
              text: TextSpan(
                text: translations?.donHaveAccount ?? "Don't Have An Account? ",
                style: appCss.dmDenseMedium14.textColor(appColor(context).appTheme.darkText),
                children: [
                  TextSpan(
                    recognizer: recognizer,
                    text: translations?.signUp ?? ' Sign Up',
                    style: appCss.dmDenseMedium14.textColor(appColor(context).appTheme.primary),
                  ),
                ],
              ),
            ),
          ),
        ],
      );
    });
  }
}
