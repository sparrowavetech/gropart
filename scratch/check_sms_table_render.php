<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$admin = Botble\ACL\Models\User::first();
auth()->guard('web')->login($admin);

view()->share('errors', new Illuminate\Support\ViewErrorBag());

$req = Illuminate\Http\Request::create('/admin/sms', 'GET');
app('router')->getRoutes()->match($req);
$req->setRouteResolver(fn () => app('router')->getRoutes()->match($req));
app()->instance('request', $req);

$table = app(Botble\Sms\Tables\SmsTable::class);
$view = $table->renderTable();
$html = $view->render();

preg_match_all('/<table[^>]*>/i', $html, $matches);
echo "Table tags found: " . count($matches[0]) . PHP_EOL;
foreach ($matches[0] as $m) {
    echo "  " . $m . PHP_EOL;
}

preg_match_all('/data-ajax-url=[\'"][^\'"]+[\'"]/i', $html, $mUrl);
echo "data-ajax-url: " . json_encode($mUrl[0]) . PHP_EOL;

preg_match_all('/"ajax":\s*\{[^\}]+\}/i', $html, $mAjax);
echo "ajax config: " . json_encode($mAjax[0]) . PHP_EOL;

preg_match_all('/"url":\s*"[^"]+"/i', $html, $mUrls);
echo "urls in script: " . json_encode($mUrls[0]) . PHP_EOL;

