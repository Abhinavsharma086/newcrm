<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the app
$kernel->handle(Illuminate\Http\Request::capture());

// Auth as user 1
$user = App\Models\User::find(1);
if ($user) {
    Illuminate\Support\Facades\Auth::login($user);
    echo "Logged in as: " . $user->name . "\n";
} else {
    echo "No user found!\n";
    exit(1);
}

// Test queries
$queries = ['vendor', 'invoice', 'customer', 'ticket', 'quotation', 'product', 'lead', 'supplier'];

foreach ($queries as $q) {
    echo "\n--- Query: '$q' ---\n";
    try {
        $request = Illuminate\Http\Request::create('/admin/chatbot/search', 'POST', [
            'query' => $q,
        ]);
        $request->setUserResolver(function () use ($user) { return $user; });
        
        $controller = new App\Http\Controllers\Admin\ChatbotController();
        $result = $controller->search($request);
        $data = json_decode($result->getContent(), true);
        echo "Type: " . ($data['type'] ?? 'unknown') . "\n";
        echo "Message: " . substr($data['message'] ?? '', 0, 80) . "\n";
        if (isset($data['list'])) {
            echo "List count: " . count($data['list']) . "\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}
