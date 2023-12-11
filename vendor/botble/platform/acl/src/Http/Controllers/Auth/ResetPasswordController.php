<?php

namespace Botble\ACL\Http\Controllers\Auth;

use Botble\ACL\Http\Requests\ResetPasswordRequest;
use Botble\ACL\Traits\ResetsPasswords;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\JsValidation\Facades\JsValidator;
use Illuminate\Http\Request;

class ResetPasswordController extends BaseController
{
    use ResetsPasswords;

    protected string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest');

        $this->redirectTo = route('dashboard.index');
    }

    public function showResetForm(Request $request, $token = null)
    {
        $this->pageTitle(trans('core/acl::auth.reset.title'));

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

        $email = $request->input('email');

        $jsValidator = JsValidator::formRequest(ResetPasswordRequest::class);

        return view('core/acl::auth.reset', compact('email', 'token', 'jsValidator'));
    }
}
