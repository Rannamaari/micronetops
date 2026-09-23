<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'service' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $respond = function (bool $success, string $message, int $status = 200) use ($request) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => $success, 'message' => $message], $status);
            }

            return back()->with($success ? 'success' : 'error', $message)->withInput();
        };

        // Telegram Bot Configuration
        // TODO: Add these to .env file:
        // TELEGRAM_BOT_TOKEN=your_bot_token_here
        // TELEGRAM_CHAT_ID=your_chat_id_here
        
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            Log::error('Telegram bot configuration missing');
            return $respond(false, 'Contact service is not configured. Please contact us directly.', 500);
        }

        // Format message for Telegram
        $telegramMessage = "New Micronet project enquiry\n\n";
        $telegramMessage .= "Name: {$validated['name']}\n";
        $telegramMessage .= "Company: " . ($validated['company_name'] ?? '-') . "\n";
        $telegramMessage .= "Phone: {$validated['phone']}\n";
        $telegramMessage .= "Email: " . ($validated['email'] ?? '-') . "\n";
        $telegramMessage .= "Service: " . ($validated['service'] ?? 'General enquiry') . "\n\n";
        $telegramMessage .= "Requirements:\n{$validated['message']}\n\n";
        $telegramMessage .= 'Received: ' . now()->format('Y-m-d H:i:s');

        try {
            // Send message to Telegram
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $telegramMessage,
            ]);

            if ($response->successful()) {
                return $respond(true, 'Thank you. Our team will get back to you soon.');
            } else {
                Log::error('Telegram API error', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                
                return $respond(false, 'Failed to send your enquiry. Please try again later.', 500);
            }
        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $respond(false, 'An error occurred. Please try again later.', 500);
        }
    }
}
