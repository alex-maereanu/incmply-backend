<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Passport\ClientRepository;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->char('user_id', 36)->nullable()->index();
            $table->char('client_id', 36);
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->char('client_id', 36);
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_personal_access_clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('client_id', 36);
            $table->timestamps();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->timestamps();
            $table->json('data')->nullable();
        });

        Schema::create('tenants_users', function (Blueprint $table) {
            $table->increments('id');
            $table->char('user_id', 36)->index('user_id');
            $table->char('tenant_id', 36)->index('tenant_id');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->boolean('is_otp_enabled')->default(false);
            $table->string('name');
            $table->string('email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->text('google2fa_secret')->nullable();
            $table->timestamps();
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->foreign(['user_id'], 'oauth_access_tokens_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
        });

        Schema::table('tenants_users', function (Blueprint $table) {
            $table->foreign(['user_id'], 'tenants_users_ibfk_1')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign(['tenant_id'], 'tenants_users_ibfk_2')->references(['id'])->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });

        $client = new ClientRepository();
        $client->createPersonalAccessClient(null, 'Incmply Personal Access Client', env('FRONTEND_URL'));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants_users', function (Blueprint $table) {
            $table->dropForeign('tenants_users_ibfk_1');
            $table->dropForeign('tenants_users_ibfk_2');
        });

        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropForeign('oauth_access_tokens_ibfk_1');
        });

        Schema::dropIfExists('users');

        Schema::dropIfExists('tenants_users');

        Schema::dropIfExists('tenants');

        Schema::dropIfExists('password_reset_tokens');

        Schema::dropIfExists('oauth_refresh_tokens');

        Schema::dropIfExists('oauth_personal_access_clients');

        Schema::dropIfExists('oauth_clients');

        Schema::dropIfExists('oauth_auth_codes');

        Schema::dropIfExists('oauth_access_tokens');

        Schema::dropIfExists('jobs');

        Schema::dropIfExists('job_batches');

        Schema::dropIfExists('failed_jobs');
    }
};
