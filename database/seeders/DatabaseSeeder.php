<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\Member;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrador',
            'email' => 'admin@ironid.com',
        ]);

        User::factory()->create([
            'name' => 'Recepcao',
            'email' => 'recepcao@ironid.com',
        ]);

        $monthly = Plan::factory()->create([
            'name' => 'Mensal',
            'price' => 99.90,
            'duration_days' => 30,
        ]);

        $quarterly = Plan::factory()->create([
            'name' => 'Trimestral',
            'price' => 249.90,
            'duration_days' => 90,
        ]);

        $annual = Plan::factory()->create([
            'name' => 'Anual',
            'price' => 899.90,
            'duration_days' => 365,
        ]);

        $plans = collect([$monthly, $quarterly, $annual]);

        Member::factory(30)
            ->create()
            ->each(function (Member $member) use ($plans) {
                $member->update(['plan_id' => $plans->random()->id]);

                CheckIn::factory(rand(0, 8))->create(['member_id' => $member->id]);
            });
    }
}
