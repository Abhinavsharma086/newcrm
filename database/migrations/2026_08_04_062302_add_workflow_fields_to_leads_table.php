<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Appointment status
            $table->enum('appointment_status', [
                'scheduled', 'confirmed', 'visited', 'converted', 'denied', 'rescheduled'
            ])->default('scheduled')->after('stage');

            // Field Technician assignment
            $table->unsignedBigInteger('assigned_technician')->nullable()->after('assigned_to');
            $table->foreign('assigned_technician')->references('id')->on('users')->onDelete('set null');

            // Follow-up fields (follow_up_date already exists)
            $table->text('follow_up_note')->nullable()->after('follow_up_date');
            $table->date('next_follow_up_date')->nullable()->after('follow_up_note');

            // Society grouping
            $table->string('society')->nullable()->after('customer_id');

            // Denial fields
            $table->text('denial_reason')->nullable()->after('follow_up_note');
            $table->string('denied_by_person')->nullable()->after('denial_reason');
            $table->string('denial_photo')->nullable()->after('denied_by_person');
            $table->enum('further_action', ['reschedule', 'drop', 'callback', 'escalate'])->nullable()->after('denial_photo');
            $table->date('further_action_date')->nullable()->after('further_action');

            // Conversion tracking
            $table->timestamp('converted_at')->nullable()->after('further_action_date');
            $table->text('conversion_notes')->nullable()->after('converted_at');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['assigned_technician']);
            $table->dropColumn([
                'appointment_status', 'assigned_technician',
                'follow_up_note', 'next_follow_up_date', 'society',
                'denial_reason', 'denied_by_person', 'denial_photo',
                'further_action', 'further_action_date',
                'converted_at', 'conversion_notes'
            ]);
        });
    }
};
