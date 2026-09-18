<?php

namespace Database\Seeders;

use App\Models\ChatbotIntent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotKnowledgeSeeder extends Seeder
{
    public function run(): void
    {
        $rules = json_decode(
            (string) file_get_contents(database_path('seeders/chatbot_rules.json')),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        foreach ($rules as $rule) {
            $intent = ChatbotIntent::updateOrCreate(
                ['name' => $rule['intent']],
                ['is_active' => true, 'priority' => 0]
            );

            DB::table('chatbot_keywords')->where('intent_id', $intent->id)->delete();
            DB::table('chatbot_responses')->where('intent_id', $intent->id)->delete();

            foreach ($rule['keywords'] as $keyword) {
                DB::table('chatbot_keywords')->insert([
                    'intent_id' => $intent->id,
                    'keyword' => $keyword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($rule['responses'] as $response) {
                DB::table('chatbot_responses')->insert([
                    'intent_id' => $intent->id,
                    'response_text' => $response,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
