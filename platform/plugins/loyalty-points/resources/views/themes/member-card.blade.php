@php
    use Botble\Base\Facades\BaseHelper;
    use Botble\Language\Facades\Language;

    $tierClass = 'default';
    if ($balance->level) {
        $levelName = strtolower($balance->level->name);
        if (str_contains($levelName, 'bronze')) {
            $tierClass = 'bronze';
        } elseif (str_contains($levelName, 'silver')) {
            $tierClass = 'silver';
        } elseif (str_contains($levelName, 'gold')) {
            $tierClass = 'gold';
        } elseif (str_contains($levelName, 'platinum')) {
            $tierClass = 'platinum';
        } elseif (str_contains($levelName, 'diamond')) {
            $tierClass = 'diamond';
        }
    }

    $supportedLocales = [];
    $showLanguageSwitcher = false;
    $currentUrl = request()->url();
    $currentLocale = app()->getLocale();
    $isRtl = BaseHelper::isRtlEnabled();

    if (is_plugin_active('language')) {
        $supportedLocales = Language::getSupportedLocales();
        $showLanguageSwitcher = count($supportedLocales) > 1;

        foreach ($supportedLocales as $localeCode => $properties) {
            $supportedLocales[$localeCode]['url'] = Language::getLocalizedURL($localeCode, $currentUrl, [], false);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" @if($isRtl) dir="rtl" @endif>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --lp-primary: #6366f1;
            --lp-primary-dark: #4f46e5;
            --lp-success: #10b981;
            --lp-success-light: #d1fae5;
            --lp-white: #ffffff;
            --lp-gray-50: #f9fafb;
            --lp-gray-100: #f3f4f6;
            --lp-gray-200: #e5e7eb;
            --lp-gray-400: #9ca3af;
            --lp-gray-500: #6b7280;
            --lp-gray-600: #4b5563;
            --lp-gray-700: #374151;
            --lp-gray-800: #1f2937;
            --lp-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --lp-shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.1);

            /* Tier colors */
            --lp-tier-default-bg: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            --lp-tier-bronze-bg: linear-gradient(135deg, #cd7f32 0%, #8b5a2b 100%);
            --lp-tier-silver-bg: linear-gradient(135deg, #c0c0c0 0%, #808080 100%);
            --lp-tier-gold-bg: linear-gradient(135deg, #ffd700 0%, #b8860b 100%);
            --lp-tier-platinum-bg: linear-gradient(135deg, #e5e4e2 0%, #a0a0a0 100%);
            --lp-tier-diamond-bg: linear-gradient(135deg, #b9f2ff 0%, #7dd3fc 100%);

            /* Page backgrounds */
            --lp-page-default: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --lp-page-bronze: linear-gradient(135deg, #cd7f32 0%, #8b4513 100%);
            --lp-page-silver: linear-gradient(135deg, #c0c0c0 0%, #71797E 100%);
            --lp-page-gold: linear-gradient(135deg, #ffd700 0%, #ff8c00 100%);
            --lp-page-platinum: linear-gradient(135deg, #e5e4e2 0%, #8e9eab 100%);
            --lp-page-diamond: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--lp-page-default);
        }

        body.tier-bronze { background: var(--lp-page-bronze); }
        body.tier-silver { background: var(--lp-page-silver); }
        body.tier-gold { background: var(--lp-page-gold); }
        body.tier-platinum { background: var(--lp-page-platinum); }
        body.tier-diamond { background: var(--lp-page-diamond); }

        /* Language Switcher */
        .lp-lang-switcher {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 100;
        }

        .lp-lang-switcher__toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            color: var(--lp-gray-700);
            box-shadow: var(--lp-shadow-sm);
            transition: all 0.2s ease;
        }

        .lp-lang-switcher__toggle:hover {
            background: var(--lp-white);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        }

        .lp-lang-switcher__toggle svg {
            width: 16px;
            height: 16px;
            transition: transform 0.2s ease;
        }

        .lp-lang-switcher.is-open .lp-lang-switcher__toggle svg {
            transform: rotate(180deg);
        }

        .lp-lang-switcher__dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            min-width: 160px;
            background: var(--lp-white);
            border-radius: 12px;
            box-shadow: var(--lp-shadow);
            overflow: hidden;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }

        .lp-lang-switcher.is-open .lp-lang-switcher__dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .lp-lang-switcher__item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: var(--lp-gray-700);
            text-decoration: none;
            font-size: 14px;
            transition: background 0.15s ease;
        }

        .lp-lang-switcher__item:hover {
            background: var(--lp-gray-50);
        }

        .lp-lang-switcher__item.is-active {
            background: var(--lp-gray-100);
            font-weight: 600;
        }

        .lp-lang-switcher__flag {
            width: 20px;
            height: 15px;
            object-fit: cover;
            border-radius: 2px;
            box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.1);
        }

        /* Member Card */
        .lp-member-card {
            width: 100%;
            max-width: 400px;
            background: var(--lp-white);
            border-radius: 24px;
            box-shadow: var(--lp-shadow);
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Header */
        .lp-member-card__header {
            background: linear-gradient(135deg, var(--lp-primary) 0%, var(--lp-primary-dark) 100%);
            padding: 20px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .lp-member-card__header-icon {
            width: 28px;
            height: 28px;
        }

        .lp-member-card__header-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: var(--lp-white);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-member-card__header-title {
            color: var(--lp-white);
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        /* Body */
        .lp-member-card__body {
            padding: 32px 24px;
            text-align: center;
        }

        /* Avatar */
        .lp-member-card__avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 16px;
        }

        .lp-member-card__avatar,
        .lp-member-card__avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            border: 4px solid var(--lp-gray-100);
            object-fit: cover;
        }

        .lp-member-card__avatar-placeholder {
            background: linear-gradient(135deg, var(--lp-primary) 0%, var(--lp-primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .lp-member-card__avatar-initial {
            color: var(--lp-white);
            font-size: 36px;
            font-weight: 700;
        }

        .lp-member-card__verified-badge {
            position: absolute;
            bottom: 4px;
            right: 4px;
            width: 28px;
            height: 28px;
            background: var(--lp-success);
            border-radius: 50%;
            border: 3px solid var(--lp-white);
            display: flex;
            align-items: center;
            justify-content: center;
            animation: pulse 2s ease-in-out infinite;
        }

        .lp-member-card__verified-badge svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: var(--lp-white);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        /* Name */
        .lp-member-card__name {
            font-size: 24px;
            font-weight: 700;
            color: var(--lp-gray-800);
            margin: 0 0 12px;
        }

        /* Tier Badge */
        .lp-member-card__tier {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            color: var(--lp-white);
            margin-bottom: 16px;
            background: var(--lp-tier-default-bg);
        }

        .lp-member-card__tier--bronze { background: var(--lp-tier-bronze-bg); }
        .lp-member-card__tier--silver { background: var(--lp-tier-silver-bg); color: var(--lp-gray-800); }
        .lp-member-card__tier--gold { background: var(--lp-tier-gold-bg); color: var(--lp-gray-800); }
        .lp-member-card__tier--platinum { background: var(--lp-tier-platinum-bg); color: var(--lp-gray-700); }
        .lp-member-card__tier--diamond { background: var(--lp-tier-diamond-bg); color: var(--lp-gray-700); }

        .lp-member-card__tier-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
        }

        /* Member ID */
        .lp-member-card__member-id {
            margin-bottom: 24px;
        }

        .lp-member-card__member-id-label {
            display: block;
            font-size: 12px;
            color: var(--lp-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .lp-member-card__member-id-value {
            font-family: 'SF Mono', 'Fira Code', monospace;
            font-size: 18px;
            font-weight: 600;
            color: var(--lp-gray-800);
            letter-spacing: 1px;
        }

        /* Divider */
        .lp-member-card__divider {
            height: 1px;
            background: var(--lp-gray-200);
            margin: 0 -24px 24px;
        }

        /* Points */
        .lp-member-card__points {
            display: flex;
            gap: 16px;
        }

        .lp-member-card__points-item {
            flex: 1;
            padding: 16px;
            background: var(--lp-gray-50);
            border-radius: 12px;
            text-align: center;
        }

        .lp-member-card__points-item--primary {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(79, 70, 229, 0.1) 100%);
            border: 1px solid rgba(99, 102, 241, 0.2);
        }

        .lp-member-card__points-label {
            display: block;
            font-size: 11px;
            color: var(--lp-gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .lp-member-card__points-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--lp-gray-800);
        }

        .lp-member-card__points-item--primary .lp-member-card__points-value {
            color: var(--lp-primary);
        }

        /* Footer */
        .lp-member-card__footer {
            background: var(--lp-success-light);
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .lp-member-card__footer-icon {
            width: 20px;
            height: 20px;
        }

        .lp-member-card__footer-icon svg {
            width: 100%;
            height: 100%;
            fill: none;
            stroke: var(--lp-success);
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .lp-member-card__footer-text {
            color: var(--lp-success);
            font-size: 14px;
            font-weight: 600;
            margin: 0;
        }

        /* Level Badge Section */
        .lp-member-card__level-badge {
            margin-bottom: 16px;
        }

        .lp-member-card__level-badge img {
            width: 64px;
            height: 64px;
            object-fit: contain;
        }

        /* Responsive */
        @media (max-width: 440px) {
            .lp-member-card {
                border-radius: 20px;
            }

            .lp-member-card__body {
                padding: 24px 20px;
            }

            .lp-member-card__name {
                font-size: 20px;
            }

            .lp-member-card__points {
                flex-direction: column;
                gap: 12px;
            }

            .lp-member-card__points-value {
                font-size: 24px;
            }

            .lp-lang-switcher {
                top: 10px;
                right: 10px;
            }

            .lp-lang-switcher__toggle {
                padding: 8px 12px;
                font-size: 13px;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white !important;
                padding: 0;
            }

            .lp-member-card {
                box-shadow: none;
                border: 1px solid var(--lp-gray-200);
            }

            .lp-lang-switcher {
                display: none;
            }
        }

        /* RTL Support */
        [dir="rtl"] {
            direction: rtl;
            text-align: right;
        }

        [dir="rtl"] .lp-lang-switcher {
            right: auto;
            left: 20px;
        }

        [dir="rtl"] .lp-lang-switcher__dropdown {
            right: auto;
            left: 0;
        }

        [dir="rtl"] .lp-lang-switcher__toggle,
        [dir="rtl"] .lp-lang-switcher__item {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .lp-member-card__header {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .lp-member-card__footer {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .lp-member-card__tier {
            flex-direction: row-reverse;
        }

        [dir="rtl"] .lp-member-card__body {
            text-align: center;
        }

        [dir="rtl"] .lp-member-card__points {
            flex-direction: row-reverse;
        }

        @media (max-width: 440px) {
            [dir="rtl"] .lp-lang-switcher {
                left: 10px;
                right: auto;
            }

            [dir="rtl"] .lp-member-card__points {
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="tier-{{ $tierClass }}">
    
    @if($showLanguageSwitcher)
        <div class="lp-lang-switcher" id="langSwitcher">
            <button type="button" class="lp-lang-switcher__toggle" onclick="toggleLangSwitcher()">
                {!! language_flag(Language::getCurrentLocaleFlag(), Language::getCurrentLocaleName()) !!}
                <span>{{ Language::getCurrentLocaleName() }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="lp-lang-switcher__dropdown">
                @foreach ($supportedLocales as $localeCode => $properties)
                    <a
                        href="{{ $properties['url'] ?? '#' }}"
                        class="lp-lang-switcher__item {{ $localeCode == Language::getCurrentLocale() ? 'is-active' : '' }}"
                    >
                        {!! language_flag($properties['lang_flag'], $properties['lang_name']) !!}
                        <span>{{ $properties['lang_name'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <div class="lp-member-card">
        
        <div class="lp-member-card__header">
            <div class="lp-member-card__header-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 12l2 2 4-4"/>
                    <path d="M12 3c1.333 1.333 3.167 2 5.5 2 .468 0 .922-.034 1.362-.1A9.006 9.006 0 0 1 21 12c0 4.97-3.582 9.063-8.5 9.934C7.582 21.063 4 16.97 4 12a9.006 9.006 0 0 1 2.138-5.1c.44.066.894.1 1.362.1 2.333 0 4.167-.667 5.5-2z"/>
                </svg>
            </div>
            <h1 class="lp-member-card__header-title">
                {{ trans('plugins/loyalty-points::loyalty-points.member_card.verified_member') }}
            </h1>
        </div>

        
        <div class="lp-member-card__body">
            
            <div class="lp-member-card__avatar-wrapper">
                @if($customer->avatar)
                    <img
                        src="{{ RvMedia::getImageUrl($customer->avatar) }}"
                        alt="{{ $customer->name }}"
                        class="lp-member-card__avatar"
                    >
                @else
                    <div class="lp-member-card__avatar-placeholder">
                        <span class="lp-member-card__avatar-initial">{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                    </div>
                @endif
                <div class="lp-member-card__verified-badge">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
            </div>

            
            @if($balance->level && $balance->level->badge)
                <div class="lp-member-card__level-badge">
                    <img
                        src="{{ RvMedia::getImageUrl($balance->level->badge) }}"
                        alt="{{ $balance->level->name }}"
                    >
                </div>
            @endif

            
            <h2 class="lp-member-card__name">{{ $customer->name }}</h2>

            
            @if($balance->level)
                <div class="lp-member-card__tier lp-member-card__tier--{{ $tierClass }}">
                    @if($balance->level->badge)
                        <img
                            src="{{ RvMedia::getImageUrl($balance->level->badge) }}"
                            alt="{{ $balance->level->name }}"
                            class="lp-member-card__tier-icon"
                        >
                    @endif
                    <span>{{ $balance->level->name }}</span>
                </div>
            @else
                <div class="lp-member-card__tier lp-member-card__tier--default">
                    <span>{{ trans('plugins/loyalty-points::loyalty-points.levels.default_member') }}</span>
                </div>
            @endif

            
            <div class="lp-member-card__member-id">
                <span class="lp-member-card__member-id-label">
                    {{ trans('plugins/loyalty-points::loyalty-points.card.member_id') }}
                </span>
                <span class="lp-member-card__member-id-value">{{ $memberId }}</span>
            </div>

            
            <div class="lp-member-card__divider"></div>

            
            <div class="lp-member-card__points">
                <div class="lp-member-card__points-item lp-member-card__points-item--primary">
                    <span class="lp-member-card__points-label">
                        {{ trans('plugins/loyalty-points::loyalty-points.points.current_balance') }}
                    </span>
                    <span class="lp-member-card__points-value">{{ number_format($balance->total_points) }}</span>
                </div>
                <div class="lp-member-card__points-item">
                    <span class="lp-member-card__points-label">
                        {{ trans('plugins/loyalty-points::loyalty-points.points.lifetime') }}
                    </span>
                    <span class="lp-member-card__points-value">{{ number_format($balance->lifetime_points) }}</span>
                </div>
            </div>
        </div>

        
        <div class="lp-member-card__footer">
            <div class="lp-member-card__footer-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
            <p class="lp-member-card__footer-text">
                {{ trans('plugins/loyalty-points::loyalty-points.card.valid_member') }}
            </p>
        </div>
    </div>

    @if($showLanguageSwitcher)
        <script>
            function toggleLangSwitcher() {
                document.getElementById('langSwitcher').classList.toggle('is-open');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                var switcher = document.getElementById('langSwitcher');
                if (switcher && !switcher.contains(e.target)) {
                    switcher.classList.remove('is-open');
                }
            });
        </script>
    @endif
</body>
</html>
