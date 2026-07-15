<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class SetupProject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup-project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run initial setup for the project (migrate database and create admin users)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting project setup...');

        // 1. Run Database Migrations
        $this->info('Running database migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());
        $this->info('✅ Database migration completed.');

        \App\Models\BankSampah::updateOrCreate([
            'nama_bank' => 'Bank Sampah Basayan',
            'alamat' => 'Jl. Raya Basayan No. 123, Kota Contoh',
            'status' => 'active'
        ]);
        // 2. Create DLH Admin Account
        $this->info('Creating Admin DLH account...');
        User::updateOrCreate(
            ['email' => 'dlh@gmail.com'],
            [
                'name' => 'Admin DLH Pusat',
                'password' => Hash::make('password123'),
                'role' => 'admin_dlh',
                'status' => 'aktif',
                'alamat' => 'Kantor DLH Pusat',
                'no_hp' => '081234567899',
                'bank_sampah_id' => 1 // Asumsi ID 1 untuk pusat
            ]
        );
        $this->info('✅ Admin DLH account created/updated (dlh@gmail.com / password123).');

        // 3. Create Bank Sampah Admin Account
        $this->info('Creating Admin Bank Sampah account...');
        User::updateOrCreate(
            ['email' => 'adminbasayan@gmail.com'],
            [
                'name' => 'Admin Bank Sampah',
                'password' => Hash::make('password123'),
                'role' => 'admin_bank_sampah',
                'status' => 'aktif',
                'alamat' => 'Kantor Bank Sampah',
                'no_hp' => '081122334455',
                'bank_sampah_id' => 1 // Asumsi ID 1 untuk bank sampah pertama
            ]
        );
        $this->info('✅ Admin Bank Sampah account created/updated (adminbasayan@gmail.com / password123).');

        $this->info('---------------------------------');
        $this->info('🎉 Project setup completed successfully! 🎉');

        return 0;
    }
}