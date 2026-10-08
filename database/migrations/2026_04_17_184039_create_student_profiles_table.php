<?php

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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('session_id')->constrained('academic_sessions')->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->string('roll_no')->nullable();
            $table->string('university_registration_number')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('category')->nullable();
            $table->string('caste')->nullable();
            $table->string('religion')->nullable();
            $table->text('address')->nullable();
            $table->text('local_address')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('whatsapp_no')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('degree_obtained_college')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
