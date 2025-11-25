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
            'phone' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        // Telegram Bot Configuration
        // TODO: Add these to .env file:
        // TELEGRAM_BOT_TOKEN=your_bot_token_here
        // TELEGRAM_CHAT_ID=your_chat_id_here
        
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        if (!$botToken || !$chatId) {
            Log::error('Telegram bot configuration missing');
            return response()->json([
                'success' => false,
                'message' => 'Contact service is not configured. Please contact us directly.'
            ], 500);
        }

        // Format message for Telegram
        $telegramMessage = "🔔 *New Contact Form Submission*\n\n";
        $telegramMessage .= "👤 *Name:* " . $validated['name'] . "\n";
        $telegramMessage .= "📞 *Phone/Email:* " . $validated['phone'] . "\n";
        $telegramMessage .= "💬 *Message:*\n" . $validated['message'] . "\n";
        $telegramMessage .= "\n📅 *Date:* " . now()->format('Y-m-d H:i:s');

        try {
            // Send message to Telegram
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $telegramMessage,
                'parse_mode' => 'Markdown',
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully! We will get back to you soon.'
                ]);
            } else {
                Log::error('Telegram API error', [
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message. Please try again later.'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again later.'
            ], 500);
        }
    }
}
