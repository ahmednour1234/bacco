<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('رمز التحقق') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:'Segoe UI', Tahoma, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:18px; overflow:hidden; box-shadow:0 10px 35px rgba(15,23,42,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#0ea5e9 0%,#2563eb 100%); padding:36px 32px; text-align:center;">
                            <div style="font-size:26px; font-weight:800; color:#ffffff; letter-spacing:0.5px;">
                                {{ config('app.name') }}
                            </div>
                            <div style="margin-top:6px; font-size:14px; color:#dbeafe;">
                                {{ __('إعادة تعيين كلمة المرور') }}
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:38px 32px 12px 32px; text-align:center;">
                            <h1 style="margin:0 0 10px; font-size:20px; color:#0f172a; font-weight:700;">
                                {{ __('رمز التحقق الخاص بك') }}
                            </h1>
                            <p style="margin:0 0 26px; font-size:15px; line-height:1.7; color:#64748b;">
                                {{ __('استخدم الرمز التالي لإكمال عملية إعادة تعيين كلمة المرور. هذا الرمز صالح لمدة') }}
                                <strong style="color:#0f172a;">{{ $ttlMinutes }} {{ __('دقائق') }}</strong>.
                            </p>

                            <!-- OTP Box -->
                            <div style="display:inline-block; background:#f0f9ff; border:2px dashed #38bdf8; border-radius:14px; padding:18px 34px; margin-bottom:26px;">
                                <span style="font-size:38px; font-weight:800; letter-spacing:12px; color:#1d4ed8; direction:ltr; display:inline-block;">
                                    {{ $otp }}
                                </span>
                            </div>

                            <p style="margin:0; font-size:13px; line-height:1.7; color:#94a3b8;">
                                {{ __('إذا لم تطلب هذا الرمز، يمكنك تجاهل هذه الرسالة بأمان.') }}
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <div style="border-top:1px solid #e2e8f0;"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:18px 32px 30px; text-align:center;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('جميع الحقوق محفوظة') }}.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
