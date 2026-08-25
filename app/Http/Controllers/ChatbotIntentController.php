<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotIntent;
use App\Models\ChatbotKeyword;
use App\Models\ChatbotResponse;

use Illuminate\Support\Facades\File;

class ChatbotIntentController extends Controller
{
 public function index(Request $request)
{
    $query = ChatbotIntent::with(['keywords','responses']);

    if ($request->filled('search')) {
        $query->where('name','like','%'.$request->search.'%');
    }

    $intents = $query
        ->latest()
        ->paginate(5)
        ->withQueryString();

    $rulesPath = storage_path('app/chatbot_rules.json');
    $generatedRules = File::exists($rulesPath)
        ? File::get($rulesPath)
        : null;

    return view('admin.chatbot.index', compact('intents', 'generatedRules'));
}

public function generateRules()
{
    $intents = ChatbotIntent::with([
        'keywords',
        'responses'
    ])
    ->where('is_active', true)
    ->get();

    $data = [];

    foreach ($intents as $intent) {

        $data[] = [
            'intent' => $intent->name,
            'keywords' => $intent->keywords->pluck('keyword'),
            'responses' => $intent->responses->pluck('response_text')
        ];
    }

    file_put_contents(
        storage_path('app/chatbot_rules.json'),
        json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    cache()->forget('chatbot_rules');

    return back()->with('success', 'Generated chatbot rules successfully');
}


public function storeIntent(Request $request)
{
    ChatbotIntent::create([
        'name'=>$request->name,
        'is_active'=>true
    ]);

    return back();
}


public function storeKeyword(Request $request)
{
    ChatbotKeyword::create([
        'intent_id'=>$request->intent_id,
        'keyword'=>$request->keyword
    ]);

    return back();
}


public function storeResponse(Request $request)
{
    ChatbotResponse::create([
        'intent_id'=>$request->intent_id,
        'response_text'=>$request->response
    ]);

    return back();
}

public function update(Request $request, $id)
{
    ChatbotIntent::findOrFail($id)->update([
        'name' => $request->name,
    ]);

    return back();
}

public function destroy($id)
{
    ChatbotIntent::destroy($id);

    return back();
}
}
