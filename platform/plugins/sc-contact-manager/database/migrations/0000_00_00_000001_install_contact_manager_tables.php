<?php

use Illuminate\Database\Migrations\Migration;
use Skillcraft\ContactManager\Plugin;

return new class () extends Migration {
    public function up(): void
    {
        (new Plugin())->pluginInstallSchema();
    }

    public function down(): void
    {
        (new Plugin())->remove();
    }
};
