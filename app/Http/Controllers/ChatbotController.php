<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function reply(Request $request)
    {
        $message = strtolower($request->message);

        $rules = json_decode(
            file_get_contents(storage_path('app/chatbot_rules.json')),
            true
        );

        foreach ($rules as $rule) {

            foreach ($rule['keywords'] as $keyword) {

                if (str_contains($message, $keyword)) {

                    $responses = $rule['responses'];

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

