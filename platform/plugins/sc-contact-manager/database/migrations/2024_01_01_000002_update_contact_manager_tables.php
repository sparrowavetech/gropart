<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Skillcraft\ContactManager\Models\ContactManager;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn($this->getContactTable(), 'uuid')) {
            Schema::table($this->getContactTable(), function (Blueprint $table) {
                $table->dropColumn('uuid');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn($this->getContactTable(), 'uuid')) {
            Schema::table($this->getContactTable(), function (Blueprint $table) {
                $table->string('uuid')->nullable()->after('id');
            });

            //add a uuid back for all the records
            ContactManager::all()->each(function (ContactManager $contact) {
                $contact->uuid = Str::orderedUuid();
                $contact->save();
            });
        }
    }

    private function getContactTable():string
    {
        return (new ContactManager)->getTable();
    }
};
