<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TicketStatus::create([
            'id' => 1,
            'name' => 'Abierto',
        ]);
        TicketStatus::create([
            'id' => 2,
            'name' => 'Cerrado',
        ]);
    }
}
