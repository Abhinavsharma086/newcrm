<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::first();
Auth::login($user);

$query = $argv[1] ?? 'ABC Electricals';
$request = Illuminate\Http\Request::create('/admin/chatbot/search', 'POST', ['query' => $query]);

$controller = new \App\Http\Controllers\Admin\ChatbotController();
try {
    $response = $controller->search($request);
    echo $response->getContent();
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
