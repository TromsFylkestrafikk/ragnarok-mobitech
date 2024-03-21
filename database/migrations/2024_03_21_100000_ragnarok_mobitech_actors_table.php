<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Ragnarok\Mobitech\Models\Actor;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mobitech_actors', function (Blueprint $table) {
            $table->integer('id')->primary()->comment('Unique actor ID');
            $table->string('name')->comment('Actor name');
        });

        $actors = [
            101010 => 'Norled',
            101677 => 'Torghatten Nord',
            101678 => 'Boreal',
        ];
        foreach ($actors as $id => $name) {
            Actor::create(['id' => $id, 'name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobitech_actors');
    }
};
