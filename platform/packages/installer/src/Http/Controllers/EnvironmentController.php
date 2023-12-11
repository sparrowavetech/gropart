<?php

namespace Botble\Installer\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Supports\Database;
use Botble\Installer\Events\EnvironmentSaved;
use Botble\Installer\Http\Requests\SaveEnvironmentRequest;
use Botble\Installer\Supports\EnvironmentManager;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;

class EnvironmentController extends BaseController
{
    public function index(Request $request): View|RedirectResponse
    {
        if (! URL::hasValidSignature($request)) {
            return redirect()->route('installers.requirements.index');
        }

        return view('packages/installer::environment');
    }

    public function store(SaveEnvironmentRequest $request, EnvironmentManager $environmentManager): RedirectResponse
    {
        $driverName = $request->input('database_connection');
        $connectionName = 'database.connections.' . $driverName;
        $databaseName = $request->input('database_name');

        config([
            'database.default' => $driverName,
            $connectionName => array_merge(config($connectionName), [
                'host' => $request->input('database_hostname'),
                'port' => $request->input('database_port'),
                'database' => $databaseName,
                'username' => $request->input('database_username'),
                'password' => $request->input('database_password'),
            ]),
        ]);

        try {
            Database::restoreFromPath(base_path('database.sql'));

            File::delete(app()->bootstrapPath('cache/plugins.php'));
        } catch (QueryException $exception) {
            $errors = new MessageBag();
            $errors->add('database', $exception->getMessage());

            return back()->withInput()->withErrors($errors);
        }

        $results = $environmentManager->save($request);

        event(new EnvironmentSaved($request));

        BaseHelper::saveFileData(storage_path(INSTALLING_SESSION_NAME), Carbon::now()->toDateTimeString());

        return redirect()
            ->to(URL::temporarySignedRoute('installers.accounts.index', Carbon::now()->addMinutes(30)))
            ->with('install_message', $results);
    }
}
