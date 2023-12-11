<?php

namespace Botble\ACL\Http\Controllers\Auth;

use Botble\ACL\Http\Requests\ForgotPasswordRequest;
use Botble\ACL\Traits\SendsPasswordResetEmails;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\JsValidation\Facades\JsValidator;
use Illuminate\Http\Request;

class ForgotPasswordController extends BaseController
{
    use SendsPasswordResetEmails;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        $this->pageTitle(trans('core/acl::auth.forgot_password.title'));

        Assets::addScripts('form-validation')
            ->removeStyles([
                'select2',
                'fancybox',
                'spectrum',
                'custom-scrollbar',
                'datepicker',
                'fontawesome',
                'toastr',
            ])
            ->removeScripts([
                'select2',
                'fancybox',
                'cookie',
                'spectrum',
                'toastr',
                'modernizr',
                'excanvas',
                'jquery-waypoints',
                'stickytableheaders',
                'ie8-fix',
            ]);

        $jsValidator = JsValidator::formRequest(ForgotPasswordRequest::class);

        return view('core/acl::auth.forgot-password', compact('jsValidator'));
    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $this
            ->httpResponse()
            ->setMessage(trans($response))
            ->toResponse($request);
    }
}
