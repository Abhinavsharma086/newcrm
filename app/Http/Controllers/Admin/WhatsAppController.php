<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappMessage;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function webhook(Request $request)
    {
        // Handle WhatsApp webhook
        // This will be called by WhatsApp Business API
        
        $data = $request->all();
        
        // Log the webhook data
        \Log::info('WhatsApp Webhook:', $data);
        
        // Process incoming messages
        if (isset($data['messages'])) {
            foreach ($data['messages'] as $message) {
                WhatsappMessage::create([
                    'phone_number' => $message['from'] ?? null,
                    'message' => $message['text']['body'] ?? null,
                    'type' => 'received',
                    'status' => 'delivered',
                    'external_id' => $message['id'] ?? null,
                ]);
            }
        }
        
        return response()->json(['status' => 'success'], 200);
    }
    
    public function messages()
    {
        $messages = WhatsappMessage::latest()->paginate(50);
        return view('admin.whatsapp.messages', compact('messages'));
    }
    
    public function send(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'message' => 'required|string',
        ]);
        
        // Here you would integrate with WhatsApp Business API
        // For now, we'll just save it to database
        
        $whatsappMessage = WhatsappMessage::create([
            'phone_number' => $validated['phone_number'],
            'message' => $validated['message'],
            'type' => 'sent',
            'status' => 'pending',
        ]);
        
        // TODO: Integrate with actual WhatsApp Business API
        // $response = $this->sendToWhatsApp($validated['phone_number'], $validated['message']);
        
        return response()->json([
            'success' => true,
            'message' => 'Message queued for sending',
            'data' => $whatsappMessage
        ]);
    }
    
    private function sendToWhatsApp($phoneNumber, $message)
    {
        // WhatsApp Business API integration
        // This requires WHATSAPP_API_URL and WHATSAPP_API_TOKEN in .env
        
        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.api_token');
        
        if (!$apiUrl || !$apiToken) {
            return ['success' => false, 'message' => 'WhatsApp API not configured'];
        }
        
        // Make API call to WhatsApp
        // Implementation depends on your WhatsApp Business API provider
        
        return ['success' => true, 'message' => 'Sent'];
    }
}
