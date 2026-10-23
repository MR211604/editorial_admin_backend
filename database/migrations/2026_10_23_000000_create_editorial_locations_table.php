<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEditorialLocationsTable extends Migration
{
    public function up()
    {
        Schema::create('editorial_locations', function (Blueprint $table) {
            $table->increments('idLocation');
            $table->string('name', 255);
            $table->string('image', 2048);
            $table->string('address', 500);
            $table->string('mapsQuery', 500);
            $table->boolean('state')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Seed the seven cards currently shown on the public locations page.
        $now = date('Y-m-d H:i:s');
        DB::table('editorial_locations')->insert([
            [
                'name' => 'Universidad Don Bosco',
                'image' => 'img/logos_ubicaciones/udb.png',
                'address' => 'Campus Soyapango y Campus Antiguo Cuscatlán',
                'mapsQuery' => 'Universidad Don Bosco Campus Soyapango El Salvador',
                'state' => 1,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Librería y papelería UCA',
                'image' => 'img/logos_ubicaciones/libUCA.png',
                'address' => 'San Salvador – Campus UCA y Antiguo Cuscatlán – SOHO Cascadas',
                'mapsQuery' => 'Librería y papelería UCA San Salvador El Salvador',
                'state' => 1,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'La Ceiba',
                'image' => 'img/logos_ubicaciones/laceiba.png',
                'address' => 'La Ceiba Sucursales',
                'mapsQuery' => 'La Ceiba librería El Salvador',
                'state' => 1,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Clásicos Roskil',
                'image' => 'img/logos_ubicaciones/roskil.png',
                'address' => '4a Avenida Sur, Santa Tecla',
                'mapsQuery' => 'Clásicos Roskil Santa Tecla El Salvador',
                'state' => 1,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Gize Bakery',
                'image' => 'img/logos_ubicaciones/gize.png',
                'address' => 'Av. 14 de Julio, Reparto Los Héroes, San Salvador',
                'mapsQuery' => 'Gize Bakery Reparto Los Héroes San Salvador El Salvador',
                'state' => 1,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'La Biblioteca Café',
                'image' => 'img/logos_ubicaciones/bibliocafe.png',
                'address' => 'Presidente Plaza, BINAES – Centro Histórico',
                'mapsQuery' => 'La Biblioteca Café Presidente Plaza BINAES San Salvador',
                'state' => 1,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'American Coffeeshop',
                'image' => 'img/logos_ubicaciones/coffeeshop.png',
                'address' => 'Calle Chiltiupán #30, Ciudad Merliot',
                'mapsQuery' => 'American Coffeeshop Calle Chiltiupán Ciudad Merliot El Salvador',
                'state' => 1,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('editorial_locations');
    }
}
