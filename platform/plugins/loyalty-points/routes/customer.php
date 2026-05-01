<?php

use Botble\Language\Facades\Language;
use Botble\LoyaltyPoints\Http\Controllers\Fronts\LoyaltyCardController;
use Botble\LoyaltyPoints\Http\Controllers\Fronts\LoyaltyCheckoutController;
use Botble\LoyaltyPoints\Http\Controllers\Fronts\LoyaltyMemberController;
use Botble\LoyaltyPoints\Http\Controllers\Fronts\LoyaltyPointController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    $registeredSlugs = [];

    $registerLoyaltyRoutes = function (string $slug, bool $withNames = true) use (&$registeredSlugs): void {
        if (in_array($slug, $registeredSlugs)) {
            return;
        }

        $registeredSlugs[] = $slug;

        $routeConfig = [
            'prefix' => $slug,
            'middleware' => ['customer'],
        ];

        if ($withNames) {
            $routeConfig['as'] = 'customer.loyalty-points.';
        }

        Route::group($routeConfig, function () use ($withNames): void {
            $indexRoute = Route::get('/', [LoyaltyPointController::class, 'index']);
            $cardRoute = Route::get('card/download', [LoyaltyCardController::class, 'download']);
            $applyRoute = Route::post('apply', [LoyaltyCheckoutController::class, 'applyPoints']);
            $removeRoute = Route::post('remove', [LoyaltyCheckoutController::class, 'removePoints']);

            if ($withNames) {
                $indexRoute->name('index');
                $cardRoute->name('card.download');
                $applyRoute->name('apply');
                $removeRoute->name('remove');
            }
        });
    };

    $defaultSlug = get_loyalty_setting('customer_page_slug', 'customer/loyalty-points');
    $registerLoyaltyRoutes($defaultSlug, true);

    // Public member verification route (no auth required, for QR code scanning)
    Route::get($defaultSlug . '/member/{token}', [LoyaltyMemberController::class, 'show'])
        ->name('public.loyalty-points.member');

    if (is_plugin_active('language')) {
        $languages = Language::getActiveLanguage(['lang_locale']);

        foreach ($languages as $language) {
            $langSlug = get_loyalty_setting('customer_page_slug_' . $language->lang_locale);

            if ($langSlug && $langSlug !== $defaultSlug) {
                $registerLoyaltyRoutes($langSlug, false);

                // Also register member route for this language slug
                Route::get($langSlug . '/member/{token}', [LoyaltyMemberController::class, 'show']);
            }
        }
    }

    Route::group(['prefix' => 'loyalty-points', 'as' => 'public.loyalty-points.'], function (): void {
        Route::post('validate-member-id', [LoyaltyCheckoutController::class, 'validateMemberId'])
            ->name('validate-member-id');
        Route::post('remove-member-id', [LoyaltyCheckoutController::class, 'removeMemberId'])
            ->name('remove-member-id');
    });
});
