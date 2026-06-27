<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('user:create-admin {email?} {--name=}', function (?string $email = null) {
    $email ??= $this->ask('Email');
    $name = $this->option('name') ?: $this->ask('Name', 'Admin');
    $password = $this->secret('Password');

    if (! $password || strlen($password) < 8) {
        $this->error('Password must be at least 8 characters.');
        return 1;
    }

    User::updateOrCreate(
        ['email' => $email],
        [
            'name' => $name,
            'password' => $password,
            'role' => 'admin',
            'location_id' => null,
            'is_active' => true,
        ],
    );

    $this->info('Admin user saved.');
    return 0;
})->purpose('Create or update an admin user without storing credentials in seeders');
