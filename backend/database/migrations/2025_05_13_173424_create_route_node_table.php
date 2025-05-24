<?php

use App\Models\Node;
use App\Models\Route;
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
        Schema::create('route_node', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignIdFor(Route::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Node::class)->constrained()->cascadeOnDelete();
            $table->integer('seq')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_node');
    }
};
