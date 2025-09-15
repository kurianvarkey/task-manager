<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->index('users_name');
            $table->string('email', 100)->unique();
            $table->string('api_key')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('role', 10); // enum class
            $table->timestamps();
        });

        // Insert default users
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@net4ideas.com',
                'api_key' => env('ADMIN_API_KEY', '12345678'),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'role' => Role::Admin,
            ],
            [
                'name' => 'Normal User',
                'email' => 'user@net4ideas.com',
                'api_key' => env('USER_API_KEY', '1234567891011'),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'role' => Role::User,
            ],
        ];

        DB::table('users')->insert($users);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
