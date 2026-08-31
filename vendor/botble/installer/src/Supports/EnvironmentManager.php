<?php

namespace Botble\Installer\Supports;

use Illuminate\Http\Request;
use Throwable;

class EnvironmentManager
{
    public function save(Request $request): string
    {
        $results = trans('packages/installer::installer.environment.success');

        try {
            file_put_contents(base_path('.env'), $this->buildEnvironmentContent($request));
        } catch (Throwable) {
            $results = trans('packages/installer::installer.environment.errors');
        }

        return $results;
    }

    public function buildEnvironmentContent(Request $request): string
    {
        $content = file_get_contents(base_path('.env.example'));

        $appUrl = rtrim((string) $request->input('app_url'), '/');

        $replacements = [
            'APP_NAME' => [
                'default' => '"Your App"',
                'value' => '"' . str_replace('"', '', $request->input('app_name')) . '"',
            ],
            'APP_URL' => [
                'default' => 'http:\/\/localhost',
                'value' => $appUrl,
            ],
            'DB_CONNECTION' => [
                'default' => 'mysql',
                'value' => $request->input('database_connection'),
            ],
            'DB_HOST' => [
                'default' => '127.0.0.1',
                'value' => $request->input('database_hostname'),
            ],
            'DB_PORT' => [
                'default' => '3306',
                'value' => $request->input('database_port'),
            ],
            'DB_DATABASE' => [
                'default' => '"laravel"',
                'value' => '"' . str_replace('"', '', $request->input('database_name')) . '"',
            ],
            'DB_USERNAME' => [
                'default' => '"root"',
                'value' => '"' . str_replace('"', '', $request->input('database_username')) . '"',
            ],
            'DB_PASSWORD' => [
                'default' => '"your_db_password"',
                'value' => '"' . str_replace('"', '', $request->input('database_password')) . '"',
            ],
        ];

        // FORCE_ROOT_URL pins every generated URL to a fixed origin (URL::useOrigin()).
        // Sub-folder installs need it, because the folder segment is otherwise lost from the
        // request root. On a root-domain install it only causes harm: after the site is moved
        // to another domain, every link keeps pointing at the old one until this line is found
        // and edited by hand - changing APP_URL alone has no effect. So only write it when the
        // install really lives in a sub-folder, and leave it commented out otherwise.
        if ($this->isSubFolderInstallation($appUrl)) {
            $replacements['FORCE_ROOT_URL'] = [
                'default' => 'https:\/\/your-domain.com',
                'value' => $appUrl,
            ];
        }

        foreach ($replacements as $key => $replacement) {
            // Allow an optional leading "#" (and spaces/tabs, not newlines) so commented-out
            // defaults (e.g. #FORCE_ROOT_URL=...) are uncommented and written. Without this,
            // FORCE_ROOT_URL is never set, and sub-folder installs fall back to the wrong root
            // URL (e.g. http://localhost). [ \t]* avoids matching across line breaks.
            $content = preg_replace(
                '/^#?[ \t]*' . $key . '=' . $replacement['default'] . '/m',
                $key . '=' . $replacement['value'],
                $content
            );
        }

        return $content;
    }

    protected function isSubFolderInstallation(string $appUrl): bool
    {
        return trim((string) parse_url($appUrl, PHP_URL_PATH), '/') !== '';
    }

    public function turnOffDebugMode(): void
    {
        $content = file_get_contents(base_path('.env'));

        $content = preg_replace('/^APP_DEBUG=true/m', 'APP_DEBUG=false', $content);

        try {
            file_put_contents(base_path('.env'), $content);
        } catch (Throwable) {
        }
    }
}
