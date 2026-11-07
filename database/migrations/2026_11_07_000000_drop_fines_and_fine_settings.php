<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('fines');

        DB::table('settings')->whereIn('key', [
            'fine_per_day',
            'lost_fee',
            'damaged_fee',
            'block_on_unpaid_fine',
        ])->delete();
    }

    public function down(): void
    {
        //
    }
};
