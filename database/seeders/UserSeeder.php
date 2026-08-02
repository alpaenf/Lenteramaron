<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin Utama',
                'email' => 'admin@lenteramaron.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Admin',
            ],
            [
                'name' => 'Siti Pustakawan, S.IP',
                'email' => 'pustakawan@lenteramaron.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Pustakawan',
            ],
            [
                'name' => 'Budi Santoso, S.Pd',
                'email' => 'guru@lenteramaron.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Guru',
            ],
            [
                'name' => 'Drs. H. Mulyono, M.Pd',
                'email' => 'kepsek@lenteramaron.sch.id',
                'password' => Hash::make('password'),
                'role' => 'Kepala Sekolah',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
