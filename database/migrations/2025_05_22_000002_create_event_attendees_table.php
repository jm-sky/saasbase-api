<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('event_id')->constrained()->cascadeOnDelete();
            $table->string('attendee_type');
            $table->uuid('attendee_id');
            $table->string('response_status');
            $table->dateTime('response_at')->nullable();
            $table->string('custom_note')->nullable();
            $table->timestamps();

            $table->index(['attendee_type', 'attendee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendees');
    }
};
