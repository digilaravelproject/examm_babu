<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Settings\AiSettings;
use App\Settings\BillingSettings;
use App\Settings\EmailSettings;
use App\Settings\PaymentSettings;
use App\Settings\RazorpaySettings;
use App\Settings\SiteSettings;
use App\Settings\TaxSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Jackiedo\DotenvEditor\Facades\DotenvEditor;

class SettingController extends Controller
{
    /**
     * General Settings View
     */
    public function general(SiteSettings $settings)
    {
        return view('admin.settings.general', compact('settings'));
    }

    public function updateSiteSettings(Request $request, SiteSettings $settings)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:160',
            'tag_line' => 'required|string|max:160',
            'seo_description' => 'required|string|max:255',
            'can_register' => 'nullable',
        ]);

        $settings->app_name = $validated['app_name'];
        $settings->tag_line = $validated['tag_line'];
        $settings->seo_description = $validated['seo_description'];
        $settings->can_register = $request->has('can_register'); // Checkbox logic
        $settings->save();

        // Update .env
        try {
            $env = DotenvEditor::load();
            $env->setKey('APP_NAME', '"' . $validated['app_name'] . '"');
            $env->save();
        } catch (\Exception $e) {
        }

        return redirect()->back()->with('success', 'General settings updated successfully.');
    }

    public function updateLogo(Request $request, SiteSettings $settings)
    {
        $request->validate([
            'logo_path' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        if ($request->hasFile('logo_path')) {
            // Delete old
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            // Save new
            $path = $request->file('logo_path')->store('settings', 'public');
            $settings->logo_path = $path;
            $settings->save();
        }

        return redirect()->back()->with('success', 'Logo updated successfully.');
    }

    public function updateFavicon(Request $request, SiteSettings $settings)
    {
        $request->validate([
            'favicon_path' => 'required|image|mimes:png,ico|max:1024',
        ]);

        if ($request->hasFile('favicon_path')) {
            if ($settings->favicon_path && Storage::disk('public')->exists($settings->favicon_path)) {
                Storage::disk('public')->delete($settings->favicon_path);
            }
            $path = $request->file('favicon_path')->store('settings', 'public');
            $settings->favicon_path = $path;
            $settings->save();
        }

        return redirect()->back()->with('success', 'Favicon updated successfully.');
    }

    /**
     * Email Settings View
     */
    public function email(EmailSettings $settings)
    {
        return view('admin.settings.email', compact('settings'));
    }

    public function updateEmailSettings(Request $request, EmailSettings $settings)
    {
        $validated = $request->validate([
            'host' => 'required|string',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'password' => 'required|string',
            'encryption' => 'required|string',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $settings->fill($validated);
        $settings->save();

        // Update .env
        try {
            $env = DotenvEditor::load();
            $env->setKey('MAIL_HOST', $validated['host']);
            $env->setKey('MAIL_PORT', $validated['port']);
            $env->setKey('MAIL_USERNAME', $validated['username']);
            $env->setKey('MAIL_PASSWORD', $validated['password']);
            $env->setKey('MAIL_ENCRYPTION', $validated['encryption']);
            $env->setKey('MAIL_FROM_ADDRESS', $validated['from_address']);
            $env->setKey('MAIL_FROM_NAME', '"' . $validated['from_name'] . '"');
            $env->save();
        } catch (\Exception $e) {
        }

        return redirect()->back()->with('success', 'Email settings updated successfully.');
    }

    /**
     * Payment Settings View (Razorpay Only)
     */
    public function payment(PaymentSettings $payment, RazorpaySettings $razorpay)
    {
        return view('admin.settings.payment', compact('payment', 'razorpay'));
    }

    public function updatePaymentSettings(Request $request, PaymentSettings $settings)
    {
        $validated = $request->validate([
            'default_currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'currency_symbol_position' => 'required|in:left,right,left_space,right_space',
        ]);

        $settings->default_currency = $validated['default_currency'];
        $settings->currency_symbol = $validated['currency_symbol'];
        $settings->currency_symbol_position = $validated['currency_symbol_position'];
        $settings->save();

        return redirect()->back()->with('success', 'Currency settings updated.');
    }

    public function updateRazorpaySettings(Request $request, RazorpaySettings $settings)
    {
        $validated = $request->validate([
            'key_id' => 'required|string',
            'key_secret' => 'required|string',
            'webhook_secret' => 'nullable|string',
        ]);

        $settings->key_id = $validated['key_id'];
        $settings->key_secret = $validated['key_secret'];
        $settings->webhook_secret = $validated['webhook_secret'] ?? '';
        $settings->save();

        return redirect()->back()->with('success', 'Razorpay credentials updated.');
    }

    /**
     * Billing Settings View
     */
    public function billing(BillingSettings $settings)
    {
        return view('admin.settings.billing', compact('settings'));
    }

    public function updateBillingSettings(Request $request, BillingSettings $settings)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string',
            'invoice_prefix' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
            'phone_number' => 'required|string',
            'vat_number' => 'nullable|string',
            'enable_invoicing' => 'nullable',
        ]);

        $settings->fill($validated);
        $settings->enable_invoicing = $request->has('enable_invoicing'); // Checkbox logic
        $settings->save();

        return redirect()->back()->with('success', 'Billing settings updated.');
    }

    /**
     * Tax / GST Settings View
     */
    public function tax(TaxSettings $settings)
    {
        return view('admin.settings.tax', compact('settings'));
    }

    public function updateTaxSettings(Request $request, TaxSettings $settings)
    {
        $validated = $request->validate([
            'tax_name'                  => 'required|string|max:50',
            'tax_type'                  => 'required|in:exclusive,inclusive',
            'tax_amount_type'           => 'required|in:percentage,fixed',
            'tax_amount'                => 'required|numeric|min:0|max:100',
            'additional_tax_name'       => 'nullable|string|max:50',
            'additional_tax_type'       => 'nullable|in:exclusive,inclusive',
            'additional_tax_amount_type'=> 'nullable|in:percentage,fixed',
            'additional_tax_amount'     => 'nullable|numeric|min:0|max:100',
        ]);

        // Primary Tax
        $settings->enable_tax               = $request->has('enable_tax');
        $settings->tax_name                 = $validated['tax_name'];
        $settings->tax_type                 = $validated['tax_type'];
        $settings->tax_amount_type          = $validated['tax_amount_type'];
        $settings->tax_amount               = (float) $validated['tax_amount'];

        // Additional Tax
        $settings->enable_additional_tax         = $request->has('enable_additional_tax');
        $settings->additional_tax_name           = $validated['additional_tax_name'] ?? 'Extra Tax';
        $settings->additional_tax_type           = $validated['additional_tax_type'] ?? 'exclusive';
        $settings->additional_tax_amount_type    = $validated['additional_tax_amount_type'] ?? 'percentage';
        $settings->additional_tax_amount         = (float) ($validated['additional_tax_amount'] ?? 0);

        $settings->save();

        return redirect()->back()->with('success', 'Tax / GST settings updated successfully.');
    }

    /**
     * AI Settings View
     */
    public function ai(AiSettings $settings)
    {
        return view('admin.settings.ai', compact('settings'));
    }

    public function updateAiSettings(Request $request, AiSettings $settings)
    {
        $validated = $request->validate([
            'gemini_api_key' => 'required|string',
            'model_name' => 'required|string',
            'custom_model' => 'nullable|string',
        ]);

        $settings->gemini_api_key = $validated['gemini_api_key'];
        $settings->model_name = $validated['model_name'];
        $settings->custom_model = $validated['custom_model'];
        $settings->save();

        // Sync with .env to keep them aligned
        try {
            $env = DotenvEditor::load();
            $env->setKey('GEMINI_API_KEY', $validated['gemini_api_key']);
            $env->save();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to update .env: " . $e->getMessage());
        }

        // Clear settings cache and general cache to ensure immediate activation
        try {
            \Illuminate\Support\Facades\Artisan::call('settings:clear-cache');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        } catch (\Exception $e) {
        }

        return redirect()->back()->with('success', 'AI settings updated and cache cleared.');
    }
}
