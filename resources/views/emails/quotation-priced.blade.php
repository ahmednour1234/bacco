<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تم تسعير عرض السعر الخاص بك</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Tahoma,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 10px 35px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:#059669;padding:34px 32px;text-align:center;">
                            <div style="font-size:26px;font-weight:800;color:#ffffff;">{{ config('app.name') }}</div>
                            <div style="margin-top:7px;font-size:14px;color:#d1fae5;">عرض السعر جاهز للمراجعة</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:36px 32px 12px;text-align:right;">
                            <h1 style="margin:0 0 12px;font-size:21px;line-height:1.5;color:#0f172a;font-weight:800;">
                                تم تسعير عرض السعر الخاص بك
                            </h1>
                            <p style="margin:0 0 18px;font-size:15px;line-height:1.9;color:#475569;">
                                مرحباً {{ $quotation->client?->name ?? 'عميلنا العزيز' }}،
                                تم الانتهاء من تسعير عرض السعر
                                <strong style="color:#0f172a;">{{ $quotation->quotation_no ?? '#' . $quotation->id }}</strong>.
                                يمكنك الآن الدخول إلى حسابك لمراجعة الأسعار واستكمال الخطوات.
                            </p>

                            <div style="margin:24px 0;padding:16px 18px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;">
                                <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px;">اسم المشروع</div>
                                <div style="font-size:15px;font-weight:700;color:#0f172a;">{{ $quotation->project_name ?? 'غير محدد' }}</div>
                            </div>

                            <div style="text-align:center;margin:30px 0 24px;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;border-radius:12px;padding:13px 28px;font-size:15px;font-weight:800;">
                                    الدخول إلى الحساب
                                </a>
                            </div>

                            <p style="margin:0;font-size:13px;line-height:1.8;color:#94a3b8;text-align:center;">
                                إذا لم يعمل الزر، انسخ الرابط التالي وافتحه في المتصفح:<br>
                                <span style="direction:ltr;unicode-bidi:embed;word-break:break-all;color:#64748b;">{{ $actionUrl }}</span>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px 32px 0;">
                            <div style="border-top:1px solid #e2e8f0;"></div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 32px 30px;text-align:center;">
                            <p style="margin:0;font-size:12px;color:#94a3b8;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
