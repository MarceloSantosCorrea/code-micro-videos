<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CastMemberSeeder extends Seeder
{
    public function run()
    {
        \App\Models\CastMember::factory(100)->create();
    }
}
