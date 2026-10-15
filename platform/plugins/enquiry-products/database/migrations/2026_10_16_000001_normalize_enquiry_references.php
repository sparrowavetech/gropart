<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
return new class extends Migration {
    public function up(): void
    {
        $rows = DB::table('ec_product_enquiry')->select('id', 'code')->where('code', 'like', 'ENQ-%')->get()
            ->filter(fn ($row) => preg_match('/^ENQ-[A-F0-9]{16}$/', $row->code));
        if ($rows->isEmpty()) { return; }
        Storage::disk('local')->put('enquiry-products-backups/references-' . now()->format('YmdHis') . '.json', $rows->values()->toJson());
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                DB::table('ec_product_enquiry')->where('id', $row->id)->where('code', $row->code)->update(['code' => '#GPEQ-' . $row->id]);
            }
        });
    }
    public function down(): void {}
};
