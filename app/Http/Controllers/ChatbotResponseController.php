<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ChatbotResponseController extends Controller
{
    public function update(Request $request,$id)
{
ChatbotResponse::findOrFail($id)
->update([
'response_text'=>$request->response
]);

return back();
}


public function destroy($id)
{
ChatbotResponse::destroy($id);

return back();
}
}
