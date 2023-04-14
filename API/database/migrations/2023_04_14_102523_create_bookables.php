<?php

use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookables', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Room::class);
            $table->morphs('bookable');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->boolean('present')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookables');
    }
};
