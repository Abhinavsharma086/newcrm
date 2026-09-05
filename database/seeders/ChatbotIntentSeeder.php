<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatbotIntentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $file = storage_path('chatbot_intents.csv');
        if (!file_exists($file)) {
            return;
        }

        $data = array_map('str_getcsv', file($file));
        $header = array_shift($data);

        foreach ($data as $row) {
            if (count($row) === 3) {
                \App\Models\ChatbotIntent::updateOrCreate(
                    [
                        'category' => trim($row[0]),
                        'intent' => trim($row[1]),
                        'sample_user_prompt' => trim($row[2]),
                    ]
                );
            }
        }
    }
}
