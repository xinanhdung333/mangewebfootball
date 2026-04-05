<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotKeywordController extends Controller
{
public function update(Request $request,$id)
{
ChatbotKeyword::findOrFail($id)
->update([
'keyword'=>$request->keyword
]);

return back();
}


public function destroy($id)
{
ChatbotKeyword::destroy($id);

return back();
}
}
