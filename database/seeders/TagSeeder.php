<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Tag::create(['name' => 'has_ac']);
        Tag::create(['name' => 'has_private_bathroom']);
        Tag::create(['name' => 'has_coffee_machine']);
    }
}
