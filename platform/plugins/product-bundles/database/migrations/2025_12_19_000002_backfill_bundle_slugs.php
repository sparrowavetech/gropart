<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up(): void
    {
        // Backfill slug for existing bundles created before the slug field was introduced.
        // We keep it deterministic to avoid collisions: bundle-{id}
        try {
            $items = DB::table('product_bundles')
                ->select('id')
                ->whereNull('slug')
                ->orWhere('slug', '')
                ->get();

            foreach ($items as $row) {
                DB::table('product_bundles')
                    ->where('id', $row->id)
                    ->update(['slug' => 'bundle-' . $row->id]);
            }
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        // no-op
    }
};
