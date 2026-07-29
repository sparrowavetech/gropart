<?php

namespace Botble\LoyaltyPoints\Services;

use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Response;
use Mpdf\Language\LanguageToFont;

class LoyaltyCardPdfService
{
    protected const CARD_WIDTH_MM = 85.6;

    protected const CARD_HEIGHT_MM = 53.98;

    protected const DEFAULT_FONT_FAMILY = 'DejaVu Sans, sans-serif';

    public function __construct(protected LoyaltyCardService $loyaltyCardService)
    {
    }

    public function download(Customer $customer, CustomerPointBalance $balance): Response|string|null
    {
        $pdf = $this->makePdf($customer, $balance);
        $filename = sprintf('loyalty-card-%s.pdf', $customer->id);

        return $pdf->download($filename);
    }

    public function stream(Customer $customer, CustomerPointBalance $balance): Response|string|null
    {
        $pdf = $this->makePdf($customer, $balance);
        $filename = sprintf('loyalty-card-%s.pdf', $customer->id);

        return $pdf->stream($filename);
    }

    protected function makePdf(Customer $customer, CustomerPointBalance $balance): Pdf
    {
        return (new Pdf())
            ->setProcessingLibrary('mpdf')
            ->templatePath($this->getTemplatePath())
            ->destinationPath($this->getTemplateCustomizedPath())
            ->paperSize($this->getPaperSize())
            ->data($this->getData($customer, $balance));
    }

    protected function getTemplatePath(): string
    {
        return plugin_path('loyalty-points/resources/templates/loyalty-card.tpl');
    }

    protected function getTemplateCustomizedPath(): string
    {
        return storage_path('app/templates/loyalty-points/loyalty-card.tpl');
    }

    protected function getPaperSize(): array
    {
        // Convert mm to points (1mm = 2.83465 points)
        // Credit card: 85.6mm x 53.98mm
        $widthPt = 242.65;  // 85.6 * 2.83465
        $heightPt = 153.01; // 53.98 * 2.83465

        return [0, 0, $widthPt, $heightPt];
    }

    protected function getData(Customer $customer, CustomerPointBalance $balance): array
    {
        $logo = theme_option('logo') ?: Theme::getLogo();

        $locale = $this->getLocaleCode();
        $fontFamily = $this->getFontFamily($locale);
        $usesFallbackFont = $fontFamily !== self::DEFAULT_FONT_FAMILY;

        return [
            'locale' => $locale,
            'font_family' => $fontFamily,
            // Fallback script fonts (FreeSerif, etc.) ship no bold variant with the
            // script's glyphs, so bold non-Latin text would render as empty boxes.
            // Drop bold for those locales to keep the card readable.
            'heading_weight' => $usesFallbackFont ? 'normal' : 'bold',
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'member_id' => sprintf('L%09d', $customer->id),
                'member_since' => $customer->created_at?->translatedFormat('M Y') ?? '-',
            ],
            'balance' => [
                'total_points' => number_format($balance->total_points),
                'lifetime_points' => number_format($balance->lifetime_points),
            ],
            'level' => $balance->level ? [
                'name' => $balance->level->name,
            ] : null,
            'qr_code_base64' => $this->svgToDataUri(
                $this->loyaltyCardService->getQrCodeSvg($customer)
            ),
            'logo_url' => $logo ? RvMedia::getRealPath($logo) : null,
            'site_title' => theme_option('site_title', config('app.name')),
            'translations' => [
                'member_id' => trans('plugins/loyalty-points::loyalty-points.card.member_id'),
                'current_balance' => trans('plugins/loyalty-points::loyalty-points.points.current_balance'),
                'lifetime' => trans('plugins/loyalty-points::loyalty-points.points.lifetime'),
                'default_member' => trans('plugins/loyalty-points::loyalty-points.levels.default_member'),
            ],
        ];
    }

    protected function svgToDataUri(string $svg): string
    {
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    protected function getLocaleCode(): string
    {
        // Use only the primary language subtag, e.g. "pt_BR" or "zh-HK" -> "pt" / "zh".
        return strtolower(preg_split('/[_-]/', (string) app()->getLocale())[0] ?: 'en');
    }

    protected function getFontFamily(string $locale): string
    {
        // The card defaults to DejaVu Sans, which has no glyphs for many non-Latin
        // scripts (Bengali, Hindi, Thai, CJK, Arabic...) and renders them as empty
        // boxes. mPDF ships fonts that cover those scripts, so resolve the proper
        // font for the active locale and only override DejaVu when the script is not
        // one DejaVu already covers - this keeps Latin/Cyrillic/Greek cards unchanged.
        $result = (new LanguageToFont())->getLanguageOptions($locale, false);
        $unifont = is_array($result) ? (string) ($result[1] ?? '') : '';

        if ($unifont !== '' && ! str_starts_with($unifont, 'dejavu')) {
            return $unifont;
        }

        return self::DEFAULT_FONT_FAMILY;
    }
}
