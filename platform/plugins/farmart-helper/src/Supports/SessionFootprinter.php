<?php
namespace SparroWave\FarmartHelper\Supports;

use Botble\Ecommerce\Services\Footprints\Footprinter;
use Illuminate\Support\Facades\Cookie;

class SessionFootprinter extends Footprinter
{
    public function getFootprints(): array
    {
        $data = parent::getFootprints();
        if (!$data && ($queued = Cookie::queued('botble_footprints_cookie_data'))) {
            $data = json_decode($queued->getValue(), true) ?: [];
        }
        if (!$data && is_string($input = request()->input('fh_source')) && strlen($input) <= 5000) {
            $input = json_decode($input, true);
            if (is_array($input)) {
                $allowed = ['landing_domain','landing_page','referrer_domain','utm_source','utm_medium','utm_campaign','utm_term','utm_content','gclid','fclid'];
                $data = array_intersect_key($input, array_flip($allowed));
                $data = array_map(fn ($v) => is_string($v) ? mb_substr(strip_tags($v), 0, 190) : null, $data);
            }
        }
        if (request()->hasSession()) {
            $data = session('farmart_source', $data);
            if ($data) session()->put('farmart_source', $data);
        }
        return is_array($data) ? $data : [];
    }
}
