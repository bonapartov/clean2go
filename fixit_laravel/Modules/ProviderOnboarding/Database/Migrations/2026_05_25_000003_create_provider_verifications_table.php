<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // ИНН
            $table->string('inn', 12)->nullable()->index();
            $table->enum('taxpayer_type', ['self_employed', 'individual_entrepreneur', 'legal_entity', 'ip_on_npd'])->nullable();
            $table->string('legal_name', 255)->nullable();
            $table->string('director_name', 255)->nullable();
            $table->string('ogrn', 15)->nullable();
            $table->date('registration_date')->nullable();
            $table->enum('inn_status', ['active', 'liquidated', 'suspended', 'not_found'])->nullable();
            $table->timestamp('inn_verified_at')->nullable();
            $table->json('inn_raw_response')->nullable();

            // НПД
            $table->enum('npd_status', ['active', 'inactive', 'not_applicable'])->nullable();
            $table->timestamp('npd_verified_at')->nullable();
            $table->json('npd_raw_response')->nullable();

            // ИП: ОКВЭД
            $table->json('okveds')->nullable();
            $table->boolean('okved_warning')->default(false);

            // Паспорт
            $table->string('passport_series', 4)->nullable();
            $table->string('passport_number', 6)->nullable();
            $table->string('passport_issued_by', 255)->nullable();
            $table->date('passport_issued_date')->nullable();
            $table->string('passport_dept_code', 7)->nullable();
            $table->string('passport_photo_path', 500)->nullable();
            $table->string('passport_selfie_path', 500)->nullable();
            $table->enum('passport_status', ['pending', 'verified', 'rejected', 'manual_review'])->nullable();
            $table->timestamp('passport_verified_at')->nullable();
            $table->json('passport_raw_response')->nullable();

            // ФССП
            $table->enum('fssp_status', ['clean', 'has_debts', 'manual_review'])->nullable();
            $table->decimal('fssp_debt_amount', 12, 2)->nullable();
            $table->timestamp('fssp_checked_at')->nullable();
            $table->json('fssp_raw_response')->nullable();

            // Договор
            $table->enum('contract_type', ['self_employed', 'ip', 'ooo'])->nullable();
            $table->string('contract_path', 500)->nullable();
            $table->timestamp('contract_signed_at')->nullable();
            $table->enum('contract_sign_method', ['sms_otp', 'ukep'])->default('sms_otp');
            $table->string('contract_sms_code', 64)->nullable(); // SHA-256 hex
            $table->timestamp('contract_sms_sent_at')->nullable();
            $table->string('contract_ip_address', 45)->nullable();

            // ООО
            $table->string('power_of_attorney_path', 500)->nullable();
            $table->string('signatory_name', 255)->nullable();

            // Статус онбординга
            $table->enum('onboarding_status', ['in_progress', 'pending_manual', 'approved', 'rejected'])->default('in_progress');
            $table->text('manual_review_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
            $table->index('onboarding_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_verifications');
    }
};
