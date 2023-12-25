<?php

namespace ArchiElite\IpBlocker\Http\Controllers;

use ArchiElite\IpBlocker\Http\Requests\AvailableCountriesRequest;
use ArchiElite\IpBlocker\Http\Requests\CheckSecretKeyRequest;
use ArchiElite\IpBlocker\Http\Requests\UpdateSettingsRequest;
use ArchiElite\IpBlocker\IpBlocker;
use ArchiElite\IpBlocker\Models\History;
use ArchiElite\IpBlocker\Repositories\Interfaces\IpBlockerInterface;
use ArchiElite\IpBlocker\Tables\HistoryTable;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Traits\HasDeleteManyItemsTrait;
use Exception;
use Illuminate\Http\Request;

class IpBlockerController extends BaseController
{
    use HasDeleteManyItemsTrait;

    public function __construct(protected IpBlockerInterface $ipBlockerRepository)
    {
    }

    public function settings(Request $request, HistoryTable $historyTable)
    {
        if ($request->expectsJson()) {
            return $historyTable->renderTable();
        }

        PageTitle::setTitle(trans('plugins/ip-blocker::ip-blocker.menu'));

        Assets::addStylesDirectly('vendor/core/core/base/libraries/tagify/tagify.css')
            ->addScriptsDirectly([
                'vendor/core/core/setting/js/setting.js',
                'vendor/core/core/base/libraries/tagify/tagify.js',
                'vendor/core/core/base/js/tags.js',
            ]);


        $ips = implode(',', json_decode((string) setting('ip_blocker_addresses', ''), true) ?: []);

        $wildcardIPAddress = implode(',', json_decode((string) setting('ip_blocker_addresses_range', ''), true) ?: []);

        $secretKey = setting('ip_blocker_secret_key');

        $countriesCode = json_decode(setting('ip_blocker_available_countries'), true);

        return view('plugins/ip-blocker::settings', compact('ips', 'wildcardIPAddress', 'secretKey', 'countriesCode', 'historyTable'));
    }

    public function updateSettings(UpdateSettingsRequest $request, BaseHttpResponse $response)
    {
        $ips = $request->input('ip_addresses');

        $clientIp = $request->ip();

        foreach ($ips as $key => $value) {
            if ($value['value'] === $clientIp) {
                unset($ips[$key]);
            }
        }

        setting()->set('ip_blocker_addresses', json_encode(collect($ips)->pluck('value')))->save();

        $wildcardIPAddress = $request->input('wildcard_ip_address');

        $explodeClientwildcardIPAddress = explode('.', $clientIp);

        $formatClientwildcardIPAddress = implode('.', [
            $explodeClientwildcardIPAddress[0],
            $explodeClientwildcardIPAddress[1],
        ]);

        foreach ($wildcardIPAddress as $key => $value) {
            if (str_starts_with(substr($value['value'], 0, -2), $formatClientwildcardIPAddress)) {
                unset($wildcardIPAddress[$key]);
            }
        }

        setting()->set('ip_blocker_addresses_range', json_encode(collect($wildcardIPAddress)->pluck('value')))->save();

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.update_settings_success'));
    }

    public function checkSecretKey(CheckSecretKeyRequest $request, BaseHttpResponse $response)
    {
        $secretKey = $request->input('secret_key');

        $data = IpBlocker::checkApiResponse($secretKey);

        if ($data->ok()) {
            setting()->set('ip_blocker_secret_key', $secretKey)->save();

            return $response
                ->setNextUrl(route('ip-blocker.settings'))
                ->setMessage(trans('plugins/ip-blocker::ip-blocker.activation_success'));
        }

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setError()
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.activation_failed'));
    }

    public function availableCountries(AvailableCountriesRequest $request, BaseHttpResponse $response)
    {
        $data = json_encode($request->input('available_countries'));

        setting()->set('ip_blocker_available_countries', $data)->save();

        return $response
            ->setNextUrl(route('ip-blocker.settings'))
            ->setMessage(trans('plugins/ip-blocker::ip-blocker.update_settings_success'));
    }

    public function destroy(History $ipBlocker, Request $request, BaseHttpResponse $response)
    {
        try {
            $this->ipBlockerRepository->delete($ipBlocker);

            event(new DeletedContentEvent(IP_BLOCKER_MODULE_SCREEN_NAME, $request, $ipBlocker));

            return $response->setMessage(trans('plugins/ip-blocker::ip-blocker.delete_success'));
        } catch (Exception $ex) {
            return $response
                ->setError()
                ->setMessage($ex->getMessage());
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        return $this->executeDeleteItems($request, $response, $this->ipBlockerRepository, IP_BLOCKER_MODULE_SCREEN_NAME);
    }

    public function deleteAll(BaseHttpResponse $response)
    {
        $this->ipBlockerRepository->getModel()->truncate();

        return $response->setMessage(trans('plugins/ip-blocker::ip-blocker.delete_success'));
    }
}
