<?php

namespace Botble\Ecommerce\Http\Requests;

use Botble\Support\Http\Requests\Request;

class EditAccountRequest extends Request
{

    protected function guard()
    {
        //dd(auth('customer'));
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
       // $session = $this->session;
       // dd($session['attributes']);

        //$customer = $this->guard()->getLastAttempted();
        return [
            'name'  => 'required|max:255',
            'phone' => 'required|min:10|max:10',
            'dob'   => 'max:20|sometimes',
        ];
    }
}
