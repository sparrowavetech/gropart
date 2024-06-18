<?php

namespace Botble\Comment\Supports;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Models\BaseModel;
use Botble\Comment\Models\CommentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class BbComment
{
    public function getModel(): Authenticatable|BaseModel
    {
        return new CommentUser();
    }

    public function setAuthProvider(): bool
    {
        config([
            'auth.guards.' . COMMENT_GUARD => [
                'driver' => 'session',
                'provider' => COMMENT_GUARD,
            ],
            'auth.providers.' . COMMENT_GUARD => [
                'driver' => 'eloquent',
                'model' => CommentUser::class,
            ],
        ]);

        return true;
    }

    public function getVersion(): ?string
    {
        $content = BaseHelper::getFileData(plugin_path('comment/plugin.json'));

        return Arr::get($content, 'version');
    }

    public function checkCurrentUser(): bool
    {
        $user = $this->getCurrentUser();

        if (! auth(COMMENT_GUARD)->check()) {
            if (is_plugin_active('member') && auth('member')->check()) {
                $user = auth('member')->user();
            } else {
                $user = auth()->user();
            }

            if ($user) {
                $userComment = $this->getModel()->firstWhere(['email' => $user->email]);
                if (! isset($userComment)) {
                    $user = $this->getModel()->forceCreate([
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name'],
                        'email' => $user['email'],
                        'password' => Hash::make($user['password']),
                    ]);
                }
            }
        }

        if ($user && ! auth(COMMENT_GUARD)->check()) {
            auth(COMMENT_GUARD)->loginUsingId($user->getAuthIdentifier());

            return true;
        }

        return (bool) $user;
    }

    public function getCurrentUser(): ?Authenticatable
    {
        return auth(COMMENT_GUARD)->user();
    }
}
