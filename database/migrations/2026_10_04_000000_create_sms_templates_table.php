<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard-configurable SMS: one editable template (with its own on/off
 * switch) per automatic SMS the system sends, plus two global switches on
 * general_settings — a master SMS switch and the admin-login OTP switch.
 *
 * Templates are seeded with the exact wording the code used to hard-code, so
 * running this migration changes nothing until an admin edits them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('message');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        $donationMessage = 'Dear {name}, Your donation {amount} BDT has been successfully received by SDF gratefully. Download the payment slip by logging into www.sdf.info.bd/login';

        $templates = [
            'donation_online'        => $donationMessage,
            'donation_manual'        => $donationMessage,
            'donation_approved'      => $donationMessage,
            'admin_login_otp'        => 'Your SDF admin login verification code is: {code}. It expires in 1 minute.',
            'user_login_otp'         => 'Your one time SDF password is: {code}',
            'user_register_otp'      => 'Hello {name}, Your one-time SDF password is: {code}',
            'phone_verification_otp' => 'Your one-time SDF password is: {code}',
            'password_reset_otp'     => 'Your OTP for SDF password reset is: {code}',
        ];

        DB::table('sms_templates')->insert(collect($templates)->map(fn ($message, $key) => [
            'key'        => $key,
            'message'    => $message,
            'status'     => true,
            'created_at' => now(),
            'updated_at' => now(),
        ])->values()->all());

        Schema::table('general_settings', function (Blueprint $table) {
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('admin_login_otp')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_templates');

        Schema::table('general_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_enabled', 'admin_login_otp']);
        });
    }
};
