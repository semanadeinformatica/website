<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminadmin'),
            'usertype_id' => '0',
            'usertype_type' => Admin::class,
        ]);
        $admin = Admin::create(['user_id' => $user->id]);
        $user->usertype_id = $admin->id;
        $user->save();
    }
}
