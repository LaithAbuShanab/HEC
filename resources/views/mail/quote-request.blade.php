<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $quote->reference }}</title>
</head>
<body style="margin:0;background:#eef1f5;font-family:Tahoma,Arial,sans-serif;color:#0b1f3a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eef1f5;padding:24px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:640px;background:#ffffff;border-radius:14px;overflow:hidden;">
                <tr>
                    <td style="background:#0b1f3a;padding:22px 28px;color:#ffffff;">
                        <div style="font-size:13px;color:#e4b42a;">{{ __('site.quote.mail.heading', [], 'ar') }}</div>
                        <div style="font-size:24px;font-weight:bold;margin-top:6px;" dir="ltr">{{ $quote->reference }}</div>
                        <div style="font-size:12px;color:#aab6c6;margin-top:6px;" dir="ltr">{{ $quote->created_at?->timezone('Asia/Amman')->format('Y-m-d H:i') }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 28px;">
                        <table role="presentation" width="100%" cellpadding="6" cellspacing="0" style="font-size:14px;">
                            @foreach ([
                                'name' => $quote->name,
                                'phone' => $quote->phone,
                                'company' => $quote->company,
                                'email' => $quote->email,
                                'city' => $quote->city,
                                'fulfillment' => $quote->fulfillment->label('ar'),
                                'needed_by' => $quote->needed_by?->toDateString(),
                            ] as $field => $value)
                                @if (filled($value))
                                    <tr>
                                        <td style="color:#5b6b80;width:35%;">{{ __("site.quote.fields.{$field}", [], 'ar') }}</td>
                                        <td style="font-weight:bold;"><bdi>{{ $value }}</bdi></td>
                                    </tr>
                                @endif
                            @endforeach
                        </table>

                        @if (filled($quote->notes))
                            <p style="margin:18px 0 0;padding:12px 14px;background:#f5f7fa;border-radius:10px;font-size:14px;line-height:1.7;white-space:pre-line;">{{ $quote->notes }}</p>
                        @endif

                        <h2 style="font-size:16px;margin:26px 0 10px;">{{ __('site.quote.mail.items', ['count' => $quote->items->count()], 'ar') }}</h2>
                        <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="font-size:13px;border-collapse:collapse;">
                            @foreach ($quote->items as $item)
                                <tr style="border-top:1px solid #e3e8ef;">
                                    <td style="vertical-align:top;">
                                        <strong>{{ $item->product_name }}</strong>
                                        @if ($item->is_custom)
                                            <span style="color:#b7791f;">({{ __('site.quote.custom_item', [], 'ar') }})</span>
                                        @endif
                                        @foreach (array_filter(['variant' => $item->variant_label, 'thickness' => $item->thickness, 'dimensions' => $item->dimensions, 'notes' => $item->notes]) as $key => $value)
                                            <div style="color:#5b6b80;margin-top:3px;">{{ __("site.quote.fields.{$key}", [], 'ar') }}: <bdi>{{ $value }}</bdi></div>
                                        @endforeach
                                    </td>
                                    <td style="vertical-align:top;white-space:nowrap;text-align:left;" dir="ltr">
                                        @if ($item->quantity !== null)
                                            {{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        <p style="font-size:13px;color:#5b6b80;margin:20px 0 0;">
                            {{ __('site.quote.mail.attachments', ['count' => $quote->attachments->count()], 'ar') }}
                        </p>

                        <p style="margin:26px 0 0;">
                            <a href="{{ $adminUrl }}" style="display:inline-block;background:#e4b42a;color:#0b1f3a;text-decoration:none;font-weight:bold;padding:12px 22px;border-radius:999px;">{{ __('site.quote.mail.open', [], 'ar') }}</a>
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
