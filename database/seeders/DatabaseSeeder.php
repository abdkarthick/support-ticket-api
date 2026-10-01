<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => 'password', 'role' => 'admin']);
        User::factory()->create(['name' => 'Agent', 'email' => 'agent@test.com', 'password' => 'password', 'role' => 'agent']);
        $customer = User::factory()->create(['name' => 'Customer', 'email' => 'customer@test.com', 'password' => 'password', 'role' => 'customer']);

        User::factory(5)->create(['role' => 'customer'])->each(function ($user) {
            Ticket::factory(3)->create(['user_id' => $user->id]);
        });
        Ticket::factory(5)->create(['user_id' => $customer->id]);
    }
}
