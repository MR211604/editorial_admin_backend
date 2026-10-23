<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateEditorialDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('editorial_documents', function (Blueprint $table) {
            $table->increments('idDocument');
            $table->string('title', 255);
            $table->string('slug', 191)->unique();
            $table->longText('content')->nullable();
            $table->boolean('state')->default(1);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = date('Y-m-d H:i:s');
        DB::table('editorial_documents')->insert([
            [
                'title' => 'Reglamento',
                'slug' => 'reglamento',
                'content' => '',
                'state' => 1,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Procedimientos de publicación',
                'slug' => 'procedimientos-de-publicacion',
                'content' => '',
                'state' => 1,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Publicacion para revista',
                'slug' => 'publicacion-para-revista',
                'content' => '',
                'state' => 1,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title' => 'Consejo editorial',
                'slug' => 'consejo-editorial',
                'content' => '',
                'state' => 1,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('editorial_documents');
    }
}
