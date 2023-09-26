<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Edition;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Edition::create([
            'name' => 'SINF 2023',
            'year' => 2023,
        ]);
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'usertype_id' => '0',
            'usertype_type' => Admin::class,
        ]);
        $admin = Admin::create(['user_id' => $user->id]);
        $user->usertype()->associate($admin);
        $user->save();
    }
}
