<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();
\ = new App\Http\Controllers\Admin\ChatbotController();
\ = new Illuminate\Http\Request(['query' => 'SKU-100']);
echo json_encode(\->search(\)->getData());
