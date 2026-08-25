<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function reply(Request $request)
    {
        $message = mb_strtolower(trim((string) $request->input('message')), 'UTF-8');

        $rulesPath = storage_path('app/chatbot_rules.json');
        $rules = is_file($rulesPath)
            ? json_decode(file_get_contents($rulesPath), true)
            : [];

        if (!is_array($rules)) {
            $rules = [];
        }

        foreach ($rules as $rule) {

            foreach ($rule['keywords'] ?? [] as $keyword) {
                $keyword = mb_strtolower(trim((string) $keyword), 'UTF-8');

                if ($keyword !== '' && str_contains($message, $keyword)) {

                    $responses = array_values(array_filter(
                        $rule['responses'] ?? [],
                        static fn ($response) => is_string($response) && trim($response) !== ''
                    ));

                    if ($responses === []) {
                        continue;
                    }

                    return response()->json([
                        'reply' => $responses[array_rand($responses)]
                    ]);
                }
            }
        }

        return response()->json([
            'reply' => 'Shop sẽ phản hồi bạn sớm.'
        ]);
    }
}

