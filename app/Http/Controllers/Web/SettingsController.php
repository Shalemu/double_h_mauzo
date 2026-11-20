<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PanelPermissionMiddleware;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Settings module: landing page + Business Profile.
 * Payment Methods and Financial Years have their own controllers.
 */
class SettingsController extends Controller
{
    /** The tabs of the Settings module, in order. */
    public const TABS = [
        'settings.business'              => ['Business Profile', 'bi-building'],
        'settings.payment-methods.index' => ['Payment Methods', 'bi-credit-card-2-front'],
        'settings.financial-years.index' => ['Financial Years', 'bi-calendar-range'],
    ];

    /** /settings → the first tab this user may open. */
    public function index()
    {
        $user = Auth::user();

        foreach (array_keys(self::TABS) as $route) {
            $permission = PanelPermissionMiddleware::permissionFor($route);
            if ($permission === null || collect((array) $permission)->contains(fn ($p) => $user->hasPermission($p))) {
                return redirect()->route($route);
            }
        }

        abort(403, 'Your role does not have permission to open Settings.');
    }

    public function business()
    {
        $values = collect(Setting::BUSINESS_FIELDS)->mapWithKeys(fn ($key) => [$key => Setting::get($key)])->all();
        $values['business_name'] = Setting::businessName();
        $logoUrl = Setting::logoUrl();
        $hasCustomLogo = (bool) Setting::get('logo');

        return view('pages.settings.business', compact('values', 'logoUrl', 'hasCustomLogo'));
    }

    public function updateBusiness(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:150',
            'phone'         => 'nullable|string|max:50',
            'email'         => 'nullable|email|max:150',
            'address'       => 'nullable|string|max:255',
            'tin'           => 'nullable|string|max:50',
            'vrn'           => 'nullable|string|max:50',
            'logo'          => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'remove_logo'   => 'nullable|boolean',
        ], [
            'logo.max' => 'The logo must be 2 MB or smaller.',
        ]);

        $values = collect($data)->only(Setting::BUSINESS_FIELDS)->map(fn ($v) => $v === null ? null : trim($v))->all();

        $oldLogo = Setting::get('logo');
        if ($request->hasFile('logo')) {
            $values['logo'] = $request->file('logo')->store('branding', 'public');
        } elseif ($request->boolean('remove_logo')) {
            $values['logo'] = null;
        }
        if (array_key_exists('logo', $values) && $oldLogo && $oldLogo !== $values['logo']) {
            Storage::disk('public')->delete($oldLogo);
        }

        Setting::put($values);

        return redirect()->route('settings.business')->with('success', 'Business profile saved.');
    }
}
