<?php

namespace Botble\LoyaltyPoints\Services;

use Botble\Base\Supports\Pdf;
use Botble\Ecommerce\Models\Customer;
use Botble\LoyaltyPoints\Models\CustomerPointBalance;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Response;

class LoyaltyCardPdfService
{
    protected const CARD_WIDTH_MM = 85.6;

    protected const CARD_HEIGHT_MM = 53.98;

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

        return [
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
}
