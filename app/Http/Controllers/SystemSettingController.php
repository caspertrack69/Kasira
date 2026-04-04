<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SystemSettingController extends Controller
{
    public function index(): View
    {
        abort_unless(request()->user()->can('settings.manage'), 403);

        return view('settings.index', [
            'settings' => SystemSetting::query()->orderBy('group')->orderBy('key')->get(),
            'gatewayStatuses' => $this->gatewayStatuses(),
            'protectedSettingKeys' => $this->protectedSettingKeys(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);

        $validator = Validator::make($request->all(), [
            'settings' => ['required', 'array'],
            'settings.*.group' => ['required', 'string', 'max:60'],
            'settings.*.key' => ['required', 'string', 'max:120'],
            'settings.*.value' => ['nullable', 'string'],
        ]);

        $protectedKeys = $this->protectedSettingKeys();

        $validator->after(function ($validator) use ($request, $protectedKeys): void {
            foreach ((array) $request->input('settings', []) as $index => $item) {
                $key = Str::lower((string) ($item['key'] ?? ''));

                if (in_array($key, $protectedKeys, true)) {
                    $validator->errors()->add(
                        "settings.$index.key",
                        'Sensitive credentials must be stored in the environment file, not in the database.',
                    );
                }
            }
        });

        $validated = $validator->validate();

        foreach ($validated['settings'] as $item) {
            SystemSetting::query()->updateOrCreate(
                ['key' => Str::lower($item['key'])],
                ['group' => $item['group'], 'value' => $item['value'] ?? null],
            );
        }

        return back()->with('status', 'Settings saved.');
    }

    /**
     * @return array<int, string>
     */
    private function protectedSettingKeys(): array
    {
        return [
            'aws_access_key_id',
            'aws_secret_access_key',
            'mail_password',
            'midtrans_client_key',
            'midtrans_is_production',
            'midtrans_merchant_id',
            'midtrans_server_key',
            'postmark_api_key',
            'resend_api_key',
            'slack_bot_user_oauth_token',
            'smtp_password',
            'xendit_callback_token',
            'xendit_secret_key',
            'xendit_webhook_verification_token',
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function gatewayStatuses(): array
    {
        return [
            [
                'label' => 'Midtrans',
                'summary' => 'Snap and gateway credentials are loaded from .env so secrets never enter the database.',
                'ready' => filled(config('services.midtrans.server_key'))
                    && filled(config('services.midtrans.client_key'))
                    && filled(config('services.midtrans.merchant_id')),
                'mode' => config('services.midtrans.is_production') ? 'Production' : 'Sandbox',
                'items' => [
                    ['label' => 'Server Key', 'env' => 'MIDTRANS_SERVER_KEY', 'configured' => filled(config('services.midtrans.server_key'))],
                    ['label' => 'Client Key', 'env' => 'MIDTRANS_CLIENT_KEY', 'configured' => filled(config('services.midtrans.client_key'))],
                    ['label' => 'Merchant ID', 'env' => 'MIDTRANS_MERCHANT_ID', 'configured' => filled(config('services.midtrans.merchant_id'))],
                ],
            ],
            [
                'label' => 'Xendit',
                'summary' => 'Callback credentials stay environment-backed to keep webhook secrets outside application data.',
                'ready' => filled(config('services.xendit.secret_key'))
                    && filled(config('services.xendit.callback_token'))
                    && filled(config('services.xendit.webhook_verification_token')),
                'mode' => 'Environment managed',
                'items' => [
                    ['label' => 'Secret Key', 'env' => 'XENDIT_SECRET_KEY', 'configured' => filled(config('services.xendit.secret_key'))],
                    ['label' => 'Callback Token', 'env' => 'XENDIT_CALLBACK_TOKEN', 'configured' => filled(config('services.xendit.callback_token'))],
                    ['label' => 'Webhook Token', 'env' => 'XENDIT_WEBHOOK_VERIFICATION_TOKEN', 'configured' => filled(config('services.xendit.webhook_verification_token'))],
                ],
            ],
        ];
    }
}
