<?php

use Ashikul\IndiaSmsGateway\Services\DatabaseInstaller;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        app(DatabaseInstaller::class)->ensure();
    }

    public function down(): void
    {
    }
};
