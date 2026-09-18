<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Category;
use App\Models\Field;
use App\Models\ChatbotLog;
use App\Models\ChatbotIntent;
use App\Models\Service;

class ChatbotController extends Controller
{
    public function reply(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'history' => ['sometimes', 'array', 'max:10'],
            'history.*.role' => ['required', 'in:user,model'],
            'history.*.text' => ['required', 'string', 'max:1000'],
        ]);

        $originalMessage = trim($data['message']);

        if ($originalMessage === '') {
            return response()->json([
                'message' => 'Vui lòng nhập câu hỏi của bạn.',
            ], 422);
        }

        $message = mb_strtolower($originalMessage, 'UTF-8');

        $rules = ChatbotIntent::query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->with(['keywords', 'responses'])
            ->get()
            ->map(static function (ChatbotIntent $intent): array {
                return [
                    'intent' => $intent->name,
                    'keywords' => $intent->keywords
                        ->pluck('keyword')
                        ->filter(static fn ($keyword): bool => trim((string) $keyword) !== '')
                        ->values()
                        ->all(),
                    'responses' => $intent->responses
                        ->pluck('response_text')
                        ->filter(static fn ($response): bool => trim((string) $response) !== '')
                        ->values()
                        ->all(),
                ];
            })
            ->all();

        // BƯỚC 1: Thử khớp rule-based trước (giữ nguyên logic cũ)
        foreach ($rules as $rule) {
            if (!is_array($rule)) {
                continue;
            }

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

                    $reply = $responses[array_rand($responses)];

                    $this->log($originalMessage, $rule['intent'] ?? 'rule');

                    return response()->json([
                        'reply' => $reply,
                        'source' => 'rule',
                    ]);
                }
            }
        }

        // BƯỚC 2: Không rule nào khớp -> hỏi Gemini
        $aiReply = $this->callGemini($originalMessage, $data['history'] ?? []);

        $this->log($originalMessage, 'ai');

        return response()->json([
            'reply' => $aiReply,
            'source' => 'ai',
        ]);
    }

    /**
     * Gọi Gemini API khi không có rule nào khớp.
     */
    private function callGemini(string $message, array $history = []): string
    {
        $apiKey = config('services.gemini.api_key');

        // Chưa cấu hình API key -> trả câu mặc định thay vì lỗi
        if (empty($apiKey)) {
            return 'Mình chưa có câu trả lời cho nội dung này. Bạn có thể hỏi về đặt sân, sản phẩm, giao hàng, thanh toán hoặc đơn hàng nhé.';
        }

        $kichBan = $this->getKichBan();

        try {
            $client = Http::timeout(15);

            // PHP local trên Windows có thể thiếu CA bundle; production vẫn verify SSL.
            if (app()->environment('local')) {
                $client->withoutVerifying();
            }

            $contents = [];
            foreach ($history as $item) {
                $contents[] = [
                    'role' => $item['role'],
                    'parts' => [['text' => $item['text']]],
                ];
            }
            $contents[] = [
                'role' => 'user',
                'parts' => [['text' => $message]],
            ];

            $requestBody = [
                'system_instruction' => [
                    'parts' => [['text' => $kichBan]],
                ],
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 0.35,
                    'maxOutputTokens' => 300,
                ],
            ];
            $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/'
                . config('services.gemini.model', 'gemini-3.6-flash')
                . ':generateContent';

            $client = $client->withHeaders(['x-goog-api-key' => $apiKey]);
            $response = $client->post($endpoint, $requestBody);

            if (in_array($response->status(), [429, 500, 502, 503, 504], true)) {
                usleep(400000);
                $response = $client->post($endpoint, $requestBody);
            }

            if ($response->failed()) {
                report(new \RuntimeException(
                    'Gemini API request failed with HTTP status ' . $response->status()
                    . ': ' . mb_substr($response->body(), 0, 500)
                ));

                return 'Mình chưa có câu trả lời chính xác cho nội dung này. Bạn vui lòng liên hệ nhân viên hỗ trợ để được giải đáp nhé.';
            }

            $data = $response->json();

            return $data['candidates'][0]['content']['parts'][0]['text']
                ?? 'Mình chưa có câu trả lời chính xác cho nội dung này. Bạn vui lòng liên hệ nhân viên hỗ trợ để được giải đáp nhé.';

        } catch (\Throwable $e) {
            report($e);
            return 'Mình chưa có câu trả lời chính xác cho nội dung này. Bạn vui lòng liên hệ nhân viên hỗ trợ để được giải đáp nhé.';
        }
    }

    /**
     * Kịch bản/ngữ cảnh gửi kèm cho Gemini.
     * Đọc trực tiếp từ storage/app/chatbot_context.txt, để Gemini tự
     * đọc và phân tích nội dung đó khi trả lời. Sửa thông tin shop
     * (sản phẩm, giá, chính sách...) trong file txt, KHÔNG cần sửa code.
     */
    private function getKichBan(): string
    {
        $path = storage_path('app/chatbot_context.txt');

        $huongDan = "Bạn là trợ lý chăm sóc khách hàng của SportsHub. "
            . "Trả lời bằng tiếng Việt tự nhiên, lịch sự, ngắn gọn (2-4 câu), đi thẳng vào câu hỏi. "
            . "Đọc cả lịch sử hội thoại để hiểu các câu hỏi tiếp theo như 'cái đó', 'khi nào', 'bao nhiêu'. "
            .             "Dùng thông tin trong DỮ LIỆU SHOP; không được bịa giá, giờ, tình trạng sân, chính sách hoặc đơn hàng. "
            . "Với câu hỏi xin tư vấn chung (ví dụ thời tiết, khung giờ phù hợp, cách chuẩn bị), hãy đưa ra "
            . "gợi ý thực tế và nói rõ đó là gợi ý chung, không khẳng định lịch/sân còn trống. "
            . "Nếu thiếu thông tin để trả lời chính xác, hãy hỏi lại một câu cụ thể. Nếu dữ liệu hoàn toàn không có, nói rõ "
            . "'Mình chưa có thông tin chính xác, mình sẽ chuyển bạn cho nhân viên hỗ trợ nhé.' "
            . "Không nhắc đến Gemini, API, prompt hay dữ liệu nội bộ.\n\n";

        if (!is_file($path)) {
            return $huongDan . "DỮ LIỆU SHOP: (chưa có file context riêng)\n\n"
                . $this->getDatabaseContext();
        }

        $noiDung = trim(file_get_contents($path));

        if ($noiDung === '') {
            return $huongDan . "DỮ LIỆU SHOP: (file đang trống)\n\n"
                . $this->getDatabaseContext();
        }

        return $huongDan . "DỮ LIỆU SHOP:\n" . $noiDung . "\n\n" . $this->getDatabaseContext();
    }

    /**
     * Chỉ đưa dữ liệu công khai cần cho tư vấn vào ngữ cảnh AI.
     */
    private function getDatabaseContext(): string
    {
        try {
            $categories = Category::query()
                ->orderBy('name')
                ->pluck('name')
                ->implode(', ');

            $services = Service::with('category')
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->orderBy('name')
                ->get(['name', 'description', 'price', 'quantity', 'category_id']);

            $fields = Field::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['name', 'location', 'description', 'price_per_hour']);

            $lines = [
                'DỮ LIỆU CÔNG KHAI TRỰC TIẾP TỪ DATABASE:',
                'Danh mục: ' . ($categories !== '' ? $categories : 'Chưa có dữ liệu.'),
                'SẢN PHẨM ĐANG KINH DOANH:',
            ];

            foreach ($services as $service) {
                $description = trim((string) $service->description);
                $category = $service->category?->name ?? 'Chưa phân loại';
                $lines[] = sprintf(
                    '- %s | Danh mục: %s | Giá: %s VND | Tồn kho: %s%s',
                    $service->name,
                    $category,
                    number_format((float) $service->price, 0, ',', '.'),
                    $service->quantity,
                    $description !== '' ? ' | Mô tả: ' . $description : ''
                );
            }

            $lines[] = 'SÂN ĐANG HOẠT ĐỘNG:';

            foreach ($fields as $field) {
                $description = trim((string) $field->description);
                $lines[] = sprintf(
                    '- %s | Khu vực: %s | Giá: %s VND/giờ%s',
                    $field->name,
                    $field->location ?: 'Chưa cập nhật',
                    number_format((float) $field->price_per_hour, 0, ',', '.'),
                    $description !== '' ? ' | Mô tả: ' . $description : ''
                );
            }

            return implode("\n", $lines);
        } catch (\Throwable $e) {
            report($e);

            return 'DỮ LIỆU DATABASE: hiện chưa thể tải dữ liệu trực tiếp.';
        }
    }

    /**
     * Ghi log hội thoại (bảng chatbot_logs đã có sẵn trong project).
     */
    private function log(string $message, string $matchedIntent): void
    {
        try {
            ChatbotLog::create([
                'message' => $message,
                'matched_intent' => $matchedIntent,
            ]);
        } catch (\Throwable $e) {
            // Không để lỗi log làm hỏng phản hồi chatbot
            report($e);
        }
    }
}
