<?php

namespace FriendsOfBotble\ExpectedDeliveryDate\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Ecommerce\Models\Product;
use Carbon\Carbon;
use FriendsOfBotble\ExpectedDeliveryDate\Models\DeliveryEstimate;

class DeliveryEstimateService
{
    public function supportedDateFormats(): array
    {
        $formats = [
            'M d',
            'F d',
            'F j',
            'd M',
            'd F',
            'j F',
            'M d, Y',
            'F d, Y',
            'F j, Y',
            'd M, Y',
            'd F, Y',
            'j F, Y',
            'Y-m-d',
            'Y-M-d',
            'd-m-Y',
            'd-M-Y',
            'm/d/Y',
            'M/d/Y',
            'd/m/Y',
            'd/M/Y',
            'd.m.Y',
        ];

        return apply_filters('expected_delivery_date_formats', $formats);
    }
    public function calculateDeliveryDate(Product $product): array
    {
        $estimate = DeliveryEstimate::query()
            ->where('product_id', $product->getKey())
            ->where('is_active', true)
            ->first();

        if (! $estimate) {
            return $this->getDefaultEstimate();
        }

        $minTime = (int) $estimate->min_days ?: 4;
        $maxTime = (int) $estimate->max_days ?: 7;
        $timeUnit = $estimate->time_unit ?: 'days';

        return $this->calculateEstimate($minTime, $maxTime, $timeUnit);
    }

    protected function getDefaultEstimate(): array
    {
        $defaultMinTime = (int) setting('expected_delivery_date_default_min_days', 3);
        $defaultMaxTime = (int) setting('expected_delivery_date_default_max_days', 7);
        $defaultTimeUnit = setting('expected_delivery_date_default_time_unit', 'days');

        return $this->calculateEstimate($defaultMinTime, $defaultMaxTime, $defaultTimeUnit);
    }

    protected function calculateEstimate(int $minTime, int $maxTime, string $timeUnit): array
    {
        $now = Carbon::now();

        // Calculate the delivery dates based on time unit
        switch ($timeUnit) {
            case 'minutes':
                $minDate = $now->copy()->addMinutes($minTime);
                $maxDate = $now->copy()->addMinutes($maxTime);
                break;
            case 'hours':
                $minDate = $now->copy()->addHours($minTime);
                $maxDate = $now->copy()->addHours($maxTime);
                break;
            case 'days':
            default:
                $minDate = $now->copy()->addDays($minTime);
                $maxDate = $now->copy()->addDays($maxTime);
                break;
        }

        // Check if delivery is within the same day (for minutes/hours)
        $isSameDay = $minDate->isSameDay($maxDate) && $minDate->isSameDay($now);

        if ($isSameDay && in_array($timeUnit, ['minutes', 'hours'])) {
            // For same-day delivery, show time ranges
            $value = $this->formatTimeRange($minTime, $maxTime, $timeUnit);
        } else {
            // For multi-day delivery, show date ranges
            $dateFormat = setting('expected_delivery_date_format', 'M d');
            $value = sprintf(
                '%s - %s',
                BaseHelper::formatDate($minDate, $dateFormat),
                BaseHelper::formatDate($maxDate, $dateFormat)
            );
        }

        return [
            'min_date' => $minDate->format('Y-m-d H:i:s'),
            'max_date' => $maxDate->format('Y-m-d H:i:s'),
            'time_unit' => $timeUnit,
            'label' => trans('plugins/fob-expected-delivery-date::expected-delivery-date.estimated_delivery'),
            'value' => $value,
            'formatted' => sprintf(
                '%s: %s',
                trans('plugins/fob-expected-delivery-date::expected-delivery-date.estimated_delivery'),
                $value
            ),
        ];
    }

    protected function formatTimeRange(int $minTime, int $maxTime, string $timeUnit): string
    {
        $unitLabel = trans('plugins/fob-expected-delivery-date::expected-delivery-date.time_units.' . $timeUnit);

        if ($minTime === $maxTime) {
            return sprintf('%d %s', $minTime, strtolower($unitLabel));
        }

        return sprintf('%d - %d %s', $minTime, $maxTime, strtolower($unitLabel));
    }
}
