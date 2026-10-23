<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KeepOnlyOneActiveBanner extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('banners')) {
            return;
        }

        $activeBannerId = DB::table('banners')
            ->where('state', 1)
            ->max('idBanner');

        if ($activeBannerId) {
            DB::table('banners')
                ->where('state', 1)
                ->where('idBanner', '!=', $activeBannerId)
                ->update(['state' => 0]);
        }
    }

    public function down()
    {
        // No es posible restaurar de forma segura los estados anteriores.
    }
}
