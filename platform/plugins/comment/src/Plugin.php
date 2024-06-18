<?php

namespace Botble\Comment;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('bb_comments');
        Schema::dropIfExists('bb_comment_users');
        Schema::dropIfExists('bb_comment_likes');
        Schema::dropIfExists('bb_comment_recommends');
    }
}
