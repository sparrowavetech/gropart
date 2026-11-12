<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = Botble\ACL\Models\User::first();
auth()->guard('web')->login($admin);
view()->share('errors', new Illuminate\Support\ViewErrorBag());

$sms = Botble\Sms\Models\Sms::find(1);
$req = Illuminate\Http\Request::create('/admin/sms/1/edit', 'GET');
app('router')->getRoutes()->match($req);
$req->setRouteResolver(fn () => app('router')->getRoutes()->match($req));
app()->instance('request', $req);

$form = app(Botble\Base\Forms\FormBuilder::class)->create(Botble\Sms\Forms\SmsForm::class, ['model' => $sms]);
$html = $form->renderForm();
preg_match('/<form[^>]+action=[\'"]([^\'"]+)[\'"]/i', $html, $m);
echo "Edit Form Action: " . ($m[1] ?? 'None') . PHP_EOL;
preg_match('/name=[\'"]_method[\'"][^>]+value=[\'"]([^\'"]+)[\'"]/i', $html, $mMethod);
echo "Edit Form Method override: " . ($mMethod[1] ?? 'None') . PHP_EOL;
