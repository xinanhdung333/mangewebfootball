<?php

namespace App\Http\Controllers;

use App\Models\ChatbotKeyword;
use Illuminate\Http\Request;

class ChatbotKeywordController extends Controller
{
public function update(Request $request, $id)
{
	$data = $request->validate([
		'keyword' => ['required', 'string', 'max:255'],
	]);

	ChatbotKeyword::findOrFail($id)->update([
		'keyword' => trim($data['keyword']),
	]);

	return back()->with('success', 'Đã cập nhật từ khóa.');
}


public function destroy($id)
{
	ChatbotKeyword::findOrFail($id)->delete();

	return back()->with('success', 'Đã xóa từ khóa.');
}
}
