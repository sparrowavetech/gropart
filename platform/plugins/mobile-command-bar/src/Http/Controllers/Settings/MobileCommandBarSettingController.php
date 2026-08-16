<?php

namespace Botble\MobileCommandBar\Http\Controllers\Settings;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\MobileCommandBar\Http\Requests\MobileCommandBarSettingRequest;
use Botble\MobileCommandBar\Supports\MobileCommandBarHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MobileCommandBarSettingController extends BaseController
{
    public function edit(): View
    {
        $this->pageTitle(trans('plugins/mobile-command-bar::mobile-command-bar.settings_title'));

        return view('plugins/mobile-command-bar::settings', [
            'settings' => MobileCommandBarHelper::settings(),
        ]);
    }

    public function update(
        MobileCommandBarSettingRequest $request,
        BaseHttpResponse $response
    ): BaseHttpResponse|RedirectResponse {
        MobileCommandBarHelper::save((array) $request->validated('mcb', []));

        return $response
            ->setPreviousUrl(route('mobile-command-bar.settings'))
            ->withUpdatedSuccessMessage();
    }

    public function reset(BaseHttpResponse $response): BaseHttpResponse|RedirectResponse
    {
        MobileCommandBarHelper::resetToDefaults();

        return $response
            ->setPreviousUrl(route('mobile-command-bar.settings'))
            ->withUpdatedSuccessMessage();
    }
}
