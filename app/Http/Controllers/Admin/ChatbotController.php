<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Quotation;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\Product;
use App\Models\VendorPo;
use App\Models\Supplier;
use App\Models\ChatbotLog;
use App\Models\Appointment;
use App\Models\User;
use App\Models\ClientPo;
use App\Models\VendorInvoice;
use App\Models\MaterialLog;
use App\Models\Society;
use App\Models\Contractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // QUERY PARSER
    // ─────────────────────────────────────────────────────────────────────────

    private function localParseQuery(string $query): array
    {
        $query = trim($query);
        $lower = mb_strtolower($query);

        // ── "Latest / Newest / Sabse Naya" Intent ──────────────────────────
        $latestKeywords = ['latest', 'newest', 'naya', 'naye', 'recent', 'last entry',
                           'sabse naya', 'abhi', 'just added', 'new entry', 'latest entry',
                           'sabse latest', 'new customer', 'naye customer'];
        foreach ($latestKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return ['intent' => 'latest_customer', 'identifier' => null, 'id_type' => 'latest', 'raw' => $query];
            }
        }

        // ── "Overdue Payments" Intent ──────────────────────────
        $overdueKeywords = ['overdue', 'pending payment', 'baqi', 'due payment', 'kitna due hai', 'outstanding'];
        foreach ($overdueKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return ['intent' => 'overdue_payments', 'identifier' => null, 'id_type' => 'overdue', 'raw' => $query];
            }
        }

        // ── "Pending Tasks" Intent ──────────────────────────
        $taskKeywords = ['pending task', 'meri task', 'kya kaam hai', 'pending tasks', 'mere tasks', 'tasks', 'todo'];
        foreach ($taskKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return ['intent' => 'pending_tasks', 'identifier' => null, 'id_type' => 'tasks', 'raw' => $query];
            }
        }

        // ── Generic Module Lookups (Vendor, Product, Lead, etc.) ───────────────────────
        if (preg_match('/^(appointment)s?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'appointment_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(employee|staff|agent)s?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'employee_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(client po|work order|wo)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'client_po_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(vendor invoice|purchase invoice)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'vendor_invoice_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(inventory|material|stock)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'inventory_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(society|societies)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'society_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(contractor)s?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'contractor_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(vendor|supplier)s?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'supplier_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(product|item)s?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'product_lookup', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^leads?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'lead_lookup', 'identifier' => trim($m[1]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^customers?\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'customer_lookup', 'identifier' => trim($m[1]), 'id_type' => 'name', 'raw' => $query];
        }
        if (preg_match('/^(invoice|invoices|bill|bills)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'invoice_lookup', 'identifier' => trim($m[2]), 'id_type' => 'invoice_no', 'raw' => $query];
        }
        if (preg_match('/^(quotation|quotations|quote|quotes|estimate)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'quotation_lookup', 'identifier' => trim($m[2]), 'id_type' => 'quotation_no', 'raw' => $query];
        }
        if (preg_match('/^(ticket|tickets|complaint|complaints)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'ticket_lookup', 'identifier' => trim($m[2]), 'id_type' => 'ticket_no', 'raw' => $query];
        }
        if (preg_match('/^(task|tasks|kaam)\s*(.*)$/i', $query, $m)) {
            return ['intent' => 'pending_tasks', 'identifier' => trim($m[2]), 'id_type' => 'name', 'raw' => $query];
        }

        // ── Invoice number pattern: INV-XXXX or INV/XXXX ───────────────────
        if (preg_match('/\b(INV[-\/]\S+)\b/i', $query, $m)) {
            return ['intent' => 'invoice_lookup', 'identifier' => $m[1], 'id_type' => 'invoice_no', 'raw' => $query];
        }

        // ── LMC ID: LMC-YYYY-XXXXXX ────────────────────────────────────────
        if (preg_match('/\b(LMC[-]\d{4}[-]\w+)\b/i', $query, $m)) {
            return ['intent' => 'customer_lookup', 'identifier' => $m[1], 'id_type' => 'lmc_id', 'raw' => $query];
        }

        // ── Quotation number: QT/QUO-XXXX ──────────────────────────────────
        if (preg_match('/\b(Q[OT][-\/]\S+)\b/i', $query, $m)) {
            return ['intent' => 'quotation_lookup', 'identifier' => $m[1], 'id_type' => 'quotation_no', 'raw' => $query];
        }

        // ── Vendor PO number: PO-XXXX / VPO-XXXX ───────────────────────────
        if (preg_match('/\b(V?PO[-\/]\S+)\b/i', $query, $m)) {
            return ['intent' => 'vendor_po_lookup', 'identifier' => $m[1], 'id_type' => 'po_number', 'raw' => $query];
        }

        // ── Product SKU: SKU-XXXX ──────────────────────────────────────────
        if (preg_match('/\b(SKU[-\/]\S+)\b/i', $query, $m)) {
            return ['intent' => 'product_lookup', 'identifier' => $m[1], 'id_type' => 'sku', 'raw' => $query];
        }

        // ── Ticket number: TKT-XXXX ─────────────────────────────────────────
        if (preg_match('/\b(TKT[-\/]\S+)\b/i', $query, $m)) {
            return ['intent' => 'ticket_lookup', 'identifier' => $m[1], 'id_type' => 'ticket_no', 'raw' => $query];
        }

        // ── Pure phone number (10-12 digits) ────────────────────────────────
        if (preg_match('/\b(\d{10,12})\b/', $query, $m)) {
            return ['intent' => 'customer_lookup', 'identifier' => $m[1], 'id_type' => 'phone', 'raw' => $query];
        }

        // ── Email address ────────────────────────────────────────────────────
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $query, $m)) {
            return ['intent' => 'customer_lookup', 'identifier' => $m[0], 'id_type' => 'email', 'raw' => $query];
        }

        // ── Follow-up intent keywords ─────────────────────────────────────────
        $followUpIntents = [
            'payment'   => ['payment', 'paid', 'due', 'pending', 'baki', 'paisa', 'amount', 'kitna baki'],
            'invoice'   => ['invoice', 'bill', 'billing', 'receipt', 'raseed'],
            'quotation' => ['quotation', 'quote', 'qoutation', 'estimate'],
            'status'    => ['status', 'stage', 'progress', 'update', 'kahan', 'kahaan', 'kya hua'],
            'task'      => ['task', 'kaam', 'work', 'assign'],
            'ticket'    => ['ticket', 'complaint', 'issue', 'problem', 'shikayat'],
            'contact'   => ['phone', 'email', 'contact', 'number', 'mobile', 'address'],
            'timeline'  => ['timeline', 'history', 'dates', 'lmc', 'rfc', 'jmr', 'conversion', 'kab hua'],
            'assigned'  => ['assigned', 'agent', 'employee', 'kaun', 'representative', 'kisne'],
        ];

        $detectedIntent = 'customer_lookup';
        foreach ($followUpIntents as $intentKey => $keywords) {
            foreach ($keywords as $kw) {
                if (str_contains($lower, $kw)) {
                    $detectedIntent = 'followup_' . $intentKey;
                    break 2;
                }
            }
        }

        // Extract name from natural language
        $nameExtracted = $this->extractName($query);

        return [
            'intent'     => $detectedIntent,
            'identifier' => $nameExtracted ?? $query,
            'id_type'    => 'name',
            'raw'        => $query,
        ];
    }

    /**
     * Use OpenAI to parse the query intent. Falls back to local parsing if no API key is provided.
     */
    private function parseQuery(string $query): array
    {
        $apiKey = env('ANTHROPIC_API_KEY');
        if (empty($apiKey)) {
            return $this->localParseQuery($query);
        }

        try {
            $dbIntents = \App\Models\ChatbotIntent::distinct()->pluck('intent')->toArray();
            $dynamicIntentsList = implode("', '", $dbIntents);
            
            $prompt = "You are a CRM intent classification AI. Analyze this user query: '{$query}'
            Classify the intent into one of these exact strings: 'latest_customer', 'overdue_payments', 'pending_tasks', 'invoice_lookup', 'quotation_lookup', 'ticket_lookup', 'customer_lookup', 'product_lookup', 'vendor_po_lookup', 'supplier_lookup', 'lead_lookup', 'appointment_lookup', 'client_po_lookup', 'vendor_invoice_lookup', 'inventory_lookup', 'employee_lookup', 'society_lookup', 'contractor_lookup', 'followup_payment', 'followup_invoice', 'followup_quotation', 'followup_status', 'followup_task', 'followup_ticket', 'followup_contact', 'followup_timeline', 'followup_assigned', 'not_found', or one of these: '{$dynamicIntentsList}'.
            If it is a lookup (like customer, product, vendor_po, supplier, lead, invoice, ticket, quotation, employee, appointment), extract the 'identifier' (the name, phone, SKU, PO number, or ID).
            If it's a followup, leave identifier empty.
            Return ONLY a valid JSON object in this format: {\"intent\": \"intent_name\", \"identifier\": \"extracted_value_or_null\", \"id_type\": \"name\"}";

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => 150,
                'temperature' => 0,
                'system' => $prompt,
                'messages' => [
                    ['role' => 'user', 'content' => "Classify this query: {$query}"]
                ]
            ]);

            if ($response->successful()) {
                $jsonStr = $response->json('content.0.text');
                
                // Clean markdown blocks if Claude wraps the json
                $jsonStr = preg_replace('/```json/i', '', $jsonStr);
                $jsonStr = preg_replace('/```/', '', $jsonStr);
                
                $data = json_decode(trim($jsonStr), true);

                if ($data && isset($data['intent'])) {
                    return [
                        'intent'     => $data['intent'],
                        'identifier' => $data['identifier'] ?? $query,
                        'id_type'    => $data['id_type'] ?? 'name',
                        'raw'        => $query,
                    ];
                }
            } else {
                \Log::error("Anthropic API Error: " . $response->body());
            }
        } catch (\Exception $e) {
            \Log::error("Anthropic Parse Error: " . $e->getMessage());
        }

        // Fallback to local parsing on API failure
        return $this->localParseQuery($query);
    }

    /**
     * Extract a proper name from a natural language sentence.
     */
    private function extractName(string $query): ?string
    {
        $stopWords = ['ka', 'ki', 'ke', 'ko', 'se', 'mein', 'the', 'a', 'an', 'of', 'is',
                      'show', 'dikhao', 'dikha', 'batao', 'bata', 'details', 'status',
                      'find', 'search', 'dhundho', 'check', 'get', 'tell', 'me',
                      'invoice', 'payment', 'ticket', 'task', 'lead', 'customer', 'info',
                      'information', 'kya', 'hai', 'hain', 'wala', 'wali', 'latest', 'naya',
                      'recent', 'entry', 'open', 'edit', 'update', 'please', 'karo'];

        $words     = preg_split('/\s+/', trim($query));
        $nameWords = [];
        foreach ($words as $word) {
            $clean = preg_replace('/[^a-zA-Z]/', '', $word);
            if (strlen($clean) >= 2 && !in_array(mb_strtolower($clean), $stopWords)) {
                $nameWords[] = $clean;
            }
        }
        return count($nameWords) > 0 ? implode(' ', $nameWords) : null;
    }

    /**
     * Human-friendly time ago string.
     */
    private function timeAgo(Carbon $dt): string
    {
        $diff = $dt->diffForHumans(null, false, true, 2);
        // Make more Hinglish
        return $diff . ' pehle';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CUSTOMER PROFILE BUILDER
    // ─────────────────────────────────────────────────────────────────────────

    private function buildCustomerProfile(Customer $customer, bool $isAdmin = true): array
    {
        $profile = [
            'id'          => $customer->id,
            'name'        => $customer->name,
            'phone'       => $customer->phone,
            'email'       => $customer->email,
            'address'     => trim(implode(', ', array_filter([
                $customer->address, $customer->city, $customer->state, $customer->pin
            ]))),
            'society'     => $customer->society,
            'source'      => $customer->source,
            'stage'       => $customer->auto_stage,
            'stage_color' => $customer->stage_badge_color,
            'assigned_to' => $customer->assignee ? $customer->assignee->name : 'Unassigned',
            'crn_no'      => $customer->crn_no,
            'lmc_id'      => $customer->lmc_id,
            'meter_no'    => $customer->meter_no,
            'burner_type' => $customer->burner_type,
            'contractor'  => $customer->contractor,
            'gstin'       => $customer->gstin,
            'created_at'  => $customer->created_at ? $customer->created_at->format('d M Y, h:i A') : null,
            'time_ago'    => $customer->created_at ? $this->timeAgo($customer->created_at) : null,
            'timeline' => [
                'Registration' => $customer->registration_date ? $customer->registration_date->format('d M Y') : null,
                'LMC Done'     => $customer->lmc_date ? $customer->lmc_date->format('d M Y') : null,
                'RFC Done'     => $customer->rfc_date ? $customer->rfc_date->format('d M Y') : null,
                'JMR Done'     => $customer->jmr_date ? $customer->jmr_date->format('d M Y') : null,
                'Converted'    => $customer->conversion_date ? $customer->conversion_date->format('d M Y') : null,
            ],
            // Direct links for "Open" button
            'profile_url' => route('admin.customers.show', $customer->id),
            'edit_url'    => route('admin.customers.edit', $customer->id),
        ];

        // ── Billing (admin only) ─────────────────────────────────────────────
        if ($isAdmin) {
            $invoices = $customer->invoices()->with('payments')->latest()->take(5)->get();

            $profile['invoices'] = $invoices->map(function ($inv) {
                $overdueFlag = $inv->due_date && $inv->due_date->isPast() && $inv->payment_status !== 'paid';
                return [
                    'invoice_no' => $inv->invoice_no,
                    'date'       => $inv->invoice_date ? $inv->invoice_date->format('d M Y') : null,
                    'due_date'   => $inv->due_date ? $inv->due_date->format('d M Y') : null,
                    'total'      => number_format($inv->total ?? 0, 2),
                    'paid'       => number_format($inv->paid_amount ?? 0, 2),
                    'balance'    => number_format($inv->balance_due, 2),
                    'status'     => $inv->payment_status ?? 'unpaid',
                    'overdue'    => $overdueFlag,
                    'url'        => route('admin.invoices.show', $inv->id),
                ];
            })->toArray();

            $profile['total_invoiced'] = number_format($invoices->sum('total'), 2);
            $profile['total_paid']     = number_format($invoices->sum('paid_amount'), 2);
            $profile['total_due']      = number_format($invoices->sum(function ($i) { return $i->balance_due; }), 2);

            $profile['quotations'] = $customer->quotations()->latest()->take(3)->get()
                ->map(function ($q) {
                    return [
                        'quotation_no' => $q->quotation_no,
                        'date'         => $q->date ? $q->date->format('d M Y') : null,
                        'total'        => number_format($q->total ?? 0, 2),
                        'status'       => $q->status,
                        'url'          => route('admin.quotations.show', $q->id),
                    ];
                })->toArray();
        }

        // ── Tasks ───────────────────────────────────────────────────────────
        $profile['tasks'] = $customer->tasks()
            ->whereIn('status', ['todo', 'progress', 'review'])
            ->with('assignee')->latest()->take(3)->get()
            ->map(function ($t) {
                $overdueFlag = $t->due_date && $t->due_date->isPast();
                return [
                    'title'    => $t->title,
                    'status'   => $t->status,
                    'priority' => $t->priority,
                    'due_date' => $t->due_date ? $t->due_date->format('d M Y') : null,
                    'assignee' => $t->assignee ? $t->assignee->name : null,
                    'overdue'  => $overdueFlag,
                ];
            })->toArray();

        // ── Tickets ──────────────────────────────────────────────────────────
        $profile['tickets'] = $customer->tickets()
            ->whereNotIn('status', ['closed'])->latest()->take(3)->get()
            ->map(function ($t) {
                return [
                    'ticket_no' => $t->ticket_no,
                    'title'     => $t->title,
                    'status'    => $t->status,
                    'priority'  => $t->priority,
                    'url'       => route('admin.tickets.show', $t->id),
                ];
            })->toArray();

        // ── Leads ────────────────────────────────────────────────────────────
        $profile['leads'] = $customer->leads()->latest()->take(3)->get()
            ->map(function ($l) {
                return [
                    'title'      => $l->title,
                    'stage'      => $l->stage,
                    'value'      => number_format($l->value ?? 0, 2),
                    'follow_up'  => $l->follow_up_date ? $l->follow_up_date->format('d M Y') : null,
                    'source'     => $l->source,
                ];
            })->toArray();

        return $profile;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CONVERSATIONAL RESPONSE BUILDERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a conversational intro message for a customer profile.
     */
    public function search(Request $request)
    {
        try {
            \Log::info("Chatbot search called: " . $request->input('query'));
            $startTime = microtime(true);

            $request->validate([
                'query'        => 'required|string|max:500',
                'context_id'   => 'nullable|integer',
                'context_type' => 'nullable|string',
            ]);

            $query       = trim($request->input('query'));
            $contextId   = $request->input('context_id');
            $contextType = $request->input('context_type', 'customer');
            $isAdmin     = Auth::user() ? Auth::user()->hasRole('admin') : false;
            $userId      = Auth::id();

            // ── Follow-up with existing context ───────────────────────────────────
            $parsed     = $this->parseQuery($query);
            $intent     = $parsed['intent'];
            $identifier = $parsed['identifier'];
            $idType     = $parsed['id_type'] ?? 'name';

            // ── Convert loose followups to generic lookups if no context ──────────
            if (!$contextId && str_starts_with($intent, 'followup_')) {
                $baseIntent = str_replace('followup_', '', $intent);
                if (in_array($baseIntent, ['invoice', 'quotation', 'ticket'])) {
                    $intent = $baseIntent . '_lookup';
                    $identifier = ''; // Empty to show lists
                } else if (in_array($baseIntent, ['contact', 'status', 'timeline', 'assigned'])) {
                    $intent = 'customer_lookup';
                    $identifier = '';
                }
            }

            if ($contextId && str_starts_with($intent, 'followup_')) {
                $customer = Customer::find($contextId);
                if ($customer) {
                    $subIntent = str_replace('followup_', '', $intent);
                    $response  = $this->handleFollowUp($customer, $subIntent, $isAdmin);
                    $this->logQuery($userId, $query, $intent, 'customer', $contextId, 1, $startTime, $request);
                    return response()->json([
                        'type'         => 'followup',
                        'message'      => $response,
                        'context_id'   => $contextId,
                        'context_type' => 'customer',
                    ]);
                }
            }

            // ── Dynamic Intent Mapping (from ChatbotIntent table) ────────────────
            $dynamicIntents = \App\Models\ChatbotIntent::distinct()->pluck('intent')->toArray();
            if (in_array($intent, $dynamicIntents)) {
                $intentMap = [
                    'check_stock_level' => 'inventory_lookup',
                    'check_invoice_status' => 'invoice_lookup',
                    'accounts_payable' => 'vendor_invoice_lookup',
                    'accounts_receivable' => 'invoice_lookup',
                    'purchase_order_status' => 'vendor_po_lookup',
                    'vendor_management' => 'supplier_lookup',
                    'order_status' => 'quotation_lookup',
                    'customer_info' => 'customer_lookup',
                    'quotation' => 'quotation_lookup',
                    'lead_management' => 'lead_lookup'
                ];

                if (array_key_exists($intent, $intentMap)) {
                    $intent = $intentMap[$intent];
                } else {
                    if ($intent === 'create_sales_order' || $intent === 'create_purchase_order' || $intent === 'create_invoice') {
                        return response()->json([
                            'type'    => 'not_found',
                            'message' => "To perform this action, please visit the respective module in the sidebar."
                        ]);
                    }
                    return response()->json([
                        'type'    => 'not_found',
                        'message' => "Yeh feature ('$intent') abhi ERP mein fully available nahi hai. Coming soon! 🚀"
                    ]);
                }
            }

            // ── "Latest/Newest Customer" Intent ───────────────────────────────────
            if ($intent === 'latest_customer') {
                $customerQuery = Customer::query()->with('assignee', 'invoices');
                if (!$isAdmin) {
                    $customerQuery->where('assigned_to', $userId);
                }
                $customer = $customerQuery->latest()->first();

                if (!$customer) {
                    return $this->notFound($query, $userId, $startTime, $request);
                }

                $profile = $this->buildCustomerProfile($customer, $isAdmin);
                $intro   = $this->buildConversationalIntro($customer, true);

                $this->logQuery($userId, $query, 'latest_customer', 'customer', $customer->id, 1, $startTime, $request);

                return response()->json([
                    'type'         => 'customer_profile',
                    'intro'        => $intro,
                    'profile'      => $profile,
                    'context_id'   => $profile['id'],
                    'context_type' => 'customer',
                ]);
            }

            // ── Overdue Payments ─────────────────────────────────────────────
            if ($intent === 'overdue_payments') {
                $invoicesQuery = Invoice::with('customer')->where('payment_status', '!=', 'paid')->whereDate('due_date', '<', now());
                
                $invoices = $invoicesQuery->latest('due_date')->limit(10)->get();

                if ($invoices->isEmpty()) {
                    return $this->notFound($query, $userId, $startTime, $request, "Abhi koi overdue payments nahi hain! 🎉");
                }

                $list = $invoices->map(function ($i) {
                    return [
                        'id'          => $i->id,
                        'name'        => $i->customer ? $i->customer->name : 'Unknown',
                        'total'       => $i->balance_due,
                        'invoice_no'  => $i->invoice_no,
                        'due_date'    => $i->due_date ? $i->due_date->format('d M Y') : 'N/A',
                        'assigned_to' => $i->customer && $i->customer->assignee ? $i->customer->assignee->name : '—',
                        'url'         => route('admin.invoices.show', $i->id),
                    ];
                });

                $this->logQuery($userId, $query, 'overdue_payments', 'invoice', null, $invoices->count(), $startTime, $request);

                return response()->json([
                    'type'    => 'multiple',
                    'message' => "**{$invoices->count()} overdue invoices** mile hain:",
                    'list'    => $list,
                ]);
            }

            // ── Pending Tasks ─────────────────────────────────────────────
            if ($intent === 'pending_tasks') {
                $tasksQuery = Task::with('customer', 'assignee')->where('status', '!=', 'completed');
                if (!$isAdmin) {
                    $tasksQuery->where('assigned_to', $userId);
                }

                $tasks = $tasksQuery->orderBy('due_date', 'asc')->limit(10)->get();

                if ($tasks->isEmpty()) {
                    return $this->notFound($query, $userId, $startTime, $request, "Abhi aapke paas koi pending tasks nahi hain! 🎉");
                }

                $list = $tasks->map(function ($t) {
                    return [
                        'id'          => $t->id,
                        'name'        => $t->customer ? $t->customer->name : 'N/A',
                        'title'       => $t->title,
                        'priority'    => ucfirst($t->priority),
                        'due_date'    => $t->due_date ? $t->due_date->format('d M Y') : 'No Date',
                        'assigned_to' => $t->assignee ? $t->assignee->name : '—',
                        'url'         => route('admin.tasks.show', $t->id),
                    ];
                });

                $this->logQuery($userId, $query, 'pending_tasks', 'task', null, $tasks->count(), $startTime, $request);

                return response()->json([
                    'type'    => 'multiple',
                    'message' => "**{$tasks->count()} pending tasks** mile hain:",
                    'list'    => $list,
                ]);
            }

            // ── Product Lookup ────────────────────────────────────────────────────────
            if ($intent === 'product_lookup') {
                $products = Product::where('sku', 'LIKE', '%' . $identifier . '%')
                                   ->orWhere('name', 'LIKE', '%' . $identifier . '%')
                                   ->limit(10)->get();

                if ($products->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Product '$identifier' nahi mila.");

                $list = $products->map(function ($p) {
                    return [
                        'id'         => $p->id,
                        'name'       => $p->name,
                        'title'      => 'SKU: ' . $p->sku,
                        'stage'      => 'Price: ₹' . $p->price . ' | Stock: ' . $p->current_stock,
                        'url'        => route('admin.products.show', $p->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh products mile:", 'list' => $list]);
            }

            // ── Vendor PO Lookup ──────────────────────────────────────────────────────
            if ($intent === 'vendor_po_lookup') {
                $pos = VendorPo::where('po_number', 'LIKE', '%' . $identifier . '%')
                               ->orWhere('vendor_name', 'LIKE', '%' . $identifier . '%')
                               ->limit(10)->get();

                if ($pos->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Vendor PO '$identifier' nahi mila.");

                $list = $pos->map(function ($p) {
                    return [
                        'id'         => $p->id,
                        'name'       => 'PO: ' . $p->po_number,
                        'title'      => $p->vendor_name,
                        'stage'      => $p->status,
                        'total'      => $p->po_value,
                        'invoice_no' => 'Total',
                        'url'        => route('admin.vendor-pos.show', $p->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh Vendor POs mile:", 'list' => $list]);
            }

            // ── Supplier Lookup ───────────────────────────────────────────────────────
            if ($intent === 'supplier_lookup') {
                $suppliers = Supplier::where('name', 'LIKE', '%' . $identifier . '%')->limit(10)->get();

                if ($suppliers->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Supplier '$identifier' nahi mila.");

                $list = $suppliers->map(function ($s) {
                    return [
                        'id'         => $s->id,
                        'name'       => $s->name,
                        'phone'      => $s->phone,
                        'society'    => $s->contact_person,
                        'url'        => route('admin.suppliers.show', $s->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh suppliers mile:", 'list' => $list]);
            }

            // ── Employee Lookup ────────────────────────────────────────────────────────
            if ($intent === 'employee_lookup') {
                $employees = User::role('employee')->where('name', 'LIKE', '%' . $identifier . '%')
                                 ->orWhere('email', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($employees->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Employee '$identifier' nahi mila.");
                $list = $employees->map(function ($e) {
                    return [
                        'id'         => $e->id,
                        'name'       => $e->name,
                        'title'      => $e->department ?? 'Employee',
                        'stage'      => $e->status ?? 'Active',
                        'url'        => route('admin.employees.show', $e->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh employees mile:", 'list' => $list]);
            }

            // ── Appointment Lookup ────────────────────────────────────────────────────────
            if ($intent === 'appointment_lookup') {
                $appointments = Appointment::whereHas('customer', function($q) use ($identifier) {
                                    $q->where('name', 'LIKE', '%' . $identifier . '%');
                                })->with('customer', 'assignee')->limit(10)->get();
                if ($appointments->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Appointment nahi mili.");
                $list = $appointments->map(function ($a) {
                    return [
                        'id'         => $a->id,
                        'name'       => 'For: ' . ($a->customer->name ?? 'Unknown'),
                        'title'      => 'Type: ' . $a->appointment_type,
                        'stage'      => $a->status,
                        'assigned_to'=> $a->assignee->name ?? 'N/A',
                        'url'        => route('admin.appointments.show', $a->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh appointments mile:", 'list' => $list]);
            }

            // ── Society Lookup ────────────────────────────────────────────────────────
            if ($intent === 'society_lookup') {
                $societies = Society::where('name', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($societies->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Society '$identifier' nahi mili.");
                $list = $societies->map(function ($s) {
                    return [
                        'id'         => $s->id,
                        'name'       => $s->name,
                        'title'      => 'City: ' . $s->city,
                        'stage'      => 'Active',
                        'url'        => route('admin.societies.index'),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh societies mili:", 'list' => $list]);
            }

            // ── Contractor Lookup ────────────────────────────────────────────────────────
            if ($intent === 'contractor_lookup') {
                $contractors = Contractor::where('name', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($contractors->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Contractor '$identifier' nahi mila.");
                $list = $contractors->map(function ($c) {
                    return [
                        'id'         => $c->id,
                        'name'       => $c->name,
                        'title'      => 'Phone: ' . $c->phone,
                        'stage'      => 'Active',
                        'url'        => route('admin.contractors.index'),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh contractors mile:", 'list' => $list]);
            }

            // ── Client PO Lookup ────────────────────────────────────────────────────────
            if ($intent === 'client_po_lookup') {
                $clientPos = ClientPo::where('po_number', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($clientPos->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Client PO '$identifier' nahi mila.");
                $list = $clientPos->map(function ($c) {
                    return [
                        'id'         => $c->id,
                        'name'       => 'PO: ' . $c->po_number,
                        'title'      => 'Value: ₹' . $c->po_value,
                        'stage'      => $c->status,
                        'url'        => route('admin.client-pos.show', $c->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh Client POs mile:", 'list' => $list]);
            }

            // ── Vendor Invoice Lookup ────────────────────────────────────────────────────────
            if ($intent === 'vendor_invoice_lookup') {
                $vendorInvoices = VendorInvoice::where('invoice_number', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($vendorInvoices->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Vendor Invoice '$identifier' nahi mili.");
                $list = $vendorInvoices->map(function ($v) {
                    return [
                        'id'         => $v->id,
                        'name'       => 'Inv: ' . $v->invoice_number,
                        'title'      => 'Amount: ₹' . $v->total_amount,
                        'stage'      => $v->status,
                        'url'        => route('admin.vendor-invoices.show', $v->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh Vendor Invoices mili:", 'list' => $list]);
            }

            // ── Inventory Lookup ────────────────────────────────────────────────────────
            if ($intent === 'inventory_lookup') {
                $inventoryLogs = MaterialLog::where('reference_no', 'LIKE', '%' . $identifier . '%')->limit(10)->get();
                if ($inventoryLogs->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Inventory record '$identifier' nahi mila.");
                $list = $inventoryLogs->map(function ($i) {
                    return [
                        'id'         => $i->id,
                        'name'       => 'Ref: ' . $i->reference_no,
                        'title'      => 'Type: ' . $i->type,
                        'stage'      => 'Date: ' . ($i->date ? \Carbon\Carbon::parse($i->date)->format('d M') : ''),
                        'url'        => route('admin.inventory.logs'),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh Inventory records mile:", 'list' => $list]);
            }

            // ── Lead Lookup ───────────────────────────────────────────────────────────
            if ($intent === 'lead_lookup') {
                $leads = Lead::where('title', 'LIKE', '%' . $identifier . '%')
                             ->orWhereHas('customer', function($q) use ($identifier) {
                                 $q->where('name', 'LIKE', '%' . $identifier . '%');
                             })
                             ->with('customer', 'assignee')->limit(10)->get();

                if ($leads->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Lead '$identifier' nahi mila.");

                $list = $leads->map(function ($l) {
                    return [
                        'id'         => $l->id,
                        'name'       => $l->title,
                        'title'      => 'Customer: ' . ($l->customer ? $l->customer->name : 'N/A'),
                        'stage'      => $l->stage,
                        'time_ago'   => $l->follow_up_date ? 'Follow: ' . $l->follow_up_date->format('d M') : '',
                        'url'        => $l->customer ? route('admin.customers.show', $l->customer_id) : null,
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh leads mile:", 'list' => $list]);
            }

            // ── Direct Invoice Lookup ─────────────────────────────────────────────
            if ($intent === 'invoice_lookup') {
                $invoices = Invoice::where('invoice_no', 'LIKE', '%' . $identifier . '%')
                    ->with('customer', 'payments')
                    ->limit(10)->get();

                if ($invoices->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Invoice nahi mili.");

                if ($invoices->count() === 1 && !empty($identifier)) {
                    $invoice = $invoices->first();
                    $this->logQuery($userId, $query, $intent, 'invoice', $invoice->id, 1, $startTime, $request);
                    return response()->json([
                        'type'         => 'invoice',
                        'message'      => $this->formatInvoiceResponse($invoice, $isAdmin),
                        'context_id'   => $invoice->customer_id,
                        'context_type' => 'customer',
                    ]);
                } else {
                    $list = $invoices->map(function ($i) {
                        return [
                            'id'         => $i->id,
                            'name'       => 'Invoice: ' . $i->invoice_no,
                            'title'      => 'Customer: ' . ($i->customer->name ?? 'Unknown'),
                            'stage'      => ucfirst($i->payment_status),
                            'total'      => $i->total_amount,
                            'url'        => route('admin.invoices.show', $i->id),
                        ];
                    });
                    return response()->json(['type' => 'multiple', 'message' => "Yeh Invoices mile:", 'list' => $list]);
                }
            }

            // ── Quotation Lookup ─────────────────────────────────────────────────
            if ($intent === 'quotation_lookup') {
                $quotations = Quotation::where('quotation_no', 'LIKE', '%' . $identifier . '%')
                    ->with('customer')->limit(10)->get();

                if ($quotations->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Quotation nahi mili.");

                if ($quotations->count() === 1 && !empty($identifier)) {
                    $quotation = $quotations->first();
                    $this->logQuery($userId, $query, $intent, 'quotation', $quotation->id, 1, $startTime, $request);
                    return response()->json([
                        'type'         => 'quotation',
                        'message'      => $this->formatQuotationResponse($quotation),
                        'context_id'   => $quotation->customer_id,
                        'context_type' => 'customer',
                    ]);
                } else {
                    $list = $quotations->map(function ($q) {
                        return [
                            'id'         => $q->id,
                            'name'       => 'Quote: ' . $q->quotation_no,
                            'title'      => 'Customer: ' . ($q->customer->name ?? 'Unknown'),
                            'stage'      => ucfirst($q->status),
                            'total'      => $q->total_amount,
                            'url'        => route('admin.quotations.show', $q->id),
                        ];
                    });
                    return response()->json(['type' => 'multiple', 'message' => "Yeh Quotations mile:", 'list' => $list]);
                }
            }

            // ── Ticket Lookup ────────────────────────────────────────────────────
            if ($intent === 'ticket_lookup') {
                $tickets = \App\Models\Ticket::where('ticket_no', 'LIKE', '%' . $identifier . '%')
                    ->orWhere('title', 'LIKE', '%' . $identifier . '%')
                    ->with('customer', 'assignee')->limit(10)->get();

                if ($tickets->isEmpty()) return $this->notFound($query, $userId, $startTime, $request, "Ticket nahi mili.");

                $list = $tickets->map(function ($t) {
                    return [
                        'id'         => $t->id,
                        'name'       => 'Ticket: ' . $t->ticket_no,
                        'title'      => $t->title,
                        'stage'      => ucfirst($t->status),
                        'assigned_to'=> $t->assignee ? $t->assignee->name : 'N/A',
                        'url'        => route('admin.tickets.show', $t->id),
                    ];
                });
                return response()->json(['type' => 'multiple', 'message' => "Yeh Tickets mile:", 'list' => $list]);
            }

            // ── Customer Lookup ───────────────────────────────────────────────────
            $customerQuery = Customer::query()->with('assignee');
            if (!$isAdmin) {
                $customerQuery->where('assigned_to', $userId);
            }

            switch ($idType) {
                case 'phone':
                    $customerQuery->where(function ($q) use ($identifier) {
                        $q->where('phone', $identifier)
                          ->orWhere('phone', 'LIKE', '%' . $identifier . '%');
                    });
                    break;
                case 'email':
                    $customerQuery->where('email', 'LIKE', '%' . $identifier . '%');
                    break;
                case 'lmc_id':
                    $customerQuery->where('lmc_id', $identifier);
                    break;
                default:
                    $customerQuery->where(function ($q) use ($identifier) {
                        $q->where('name', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('phone', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('email', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('crn_no', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('lmc_id', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('meter_no', 'LIKE', '%' . $identifier . '%')
                          ->orWhere('society', 'LIKE', '%' . $identifier . '%');
                    });
            }

            $customers = $customerQuery->latest()->limit(10)->get();

            if ($customers->isEmpty()) {
                return $this->notFound($query, $userId, $startTime, $request);
            }

            if ($customers->count() === 1) {
                $customer = $customers->first();
                $profile  = $this->buildCustomerProfile($customer, $isAdmin);
                $intro    = $this->buildConversationalIntro($customer, false);
                $this->logQuery($userId, $query, $intent, 'customer', $customer->id, 1, $startTime, $request);
                return response()->json([
                    'type'         => 'customer_profile',
                    'intro'        => $intro,
                    'profile'      => $profile,
                    'context_id'   => $profile['id'],
                    'context_type' => 'customer',
                ]);
            }

            // Multiple matches
            $list = $customers->map(function ($c) {
                return [
                    'id'          => $c->id,
                    'name'        => $c->name,
                    'phone'       => $c->phone,
                    'email'       => $c->email,
                    'stage'       => $c->auto_stage,
                    'society'     => $c->society,
                    'assigned_to' => $c->assignee ? $c->assignee->name : '—',
                    'time_ago'    => $c->created_at ? $this->timeAgo($c->created_at) : null,
                ];
            });

            $this->logQuery($userId, $query, 'multiple_results', 'customer', null, $customers->count(), $startTime, $request);

            return response()->json([
                'type'    => 'multiple',
                'message' => "**{$customers->count()} customers** mile \"**{$query}**\" ke liye. Ek select karein:",
                'list'    => $list,
            ]);
        } catch (\Exception $e) {
            \Log::error("Chatbot Error: " . $e->getMessage() . " on line " . $e->getLine());
            return response()->json([
                'type' => 'not_found',
                'message' => 'PHP Exception: ' . $e->getMessage() . ' (Line ' . $e->getLine() . ')'
            ]);
        }
    }

    private function localBuildConversationalIntro(Customer $customer, bool $isLatest = false): string
    {
        $stage    = $customer->auto_stage;
        $timeAgo  = $customer->created_at ? $this->timeAgo($customer->created_at) : 'kuch samay';
        $assignee = $customer->assignee ? $customer->assignee->name : 'kisi ko assign nahi';

        $overdueCount = 0;
        $totalDue = 0;
        if ($customer->relationLoaded('invoices') || true) {
            $invoices = $customer->invoices()->get();
            foreach ($invoices as $inv) {
                if ($inv->due_date && $inv->due_date->isPast() && $inv->payment_status !== 'paid') {
                    $overdueCount++;
                    $totalDue += $inv->balance_due;
                }
            }
        }

        if ($isLatest) {
            $msg = "Sabse naya entry **{$customer->name}** ka hai, jo **{$timeAgo}** add hua. ";
        } else {
            $msg = "Mila! **{$customer->name}** ki poori details yahan hain. ";
        }

        $msg .= "Stage: **{$stage}**";

        if ($customer->society) {
            $msg .= " · Society: **{$customer->society}**";
        }

        $msg .= ". Assigned: **{$assignee}**.";

        if ($overdueCount > 0) {
            $msg .= "\n\n⚠️ **Dhyan dein:** {$overdueCount} invoice(s) overdue hain — ₹" . number_format($totalDue, 2) . " baki hai!";
        }

        $msg .= "\n\nNeeche poori summary hai. Follow-up mein puch sakte hain: \"payment kab due hai?\", \"tasks kya hain?\", \"status batao\" etc.";

        return $msg;
    }

    /**
     * Use OpenAI to generate a conversational natural language summary of the customer.
     * Falls back to local string building if API fails or key is missing.
     */
    private function buildConversationalIntro(Customer $customer, bool $isLatest = false): string
    {
        $apiKey = env('OPENAI_API_KEY');
        if (empty($apiKey)) {
            return $this->localBuildConversationalIntro($customer, $isLatest);
        }

        try {
            $client = \OpenAI::client($apiKey);
            
            // Build the raw data to send to AI
            $data = [
                'name' => $customer->name,
                'stage' => $customer->auto_stage,
                'assigned_to' => $customer->assignee ? $customer->assignee->name : 'Unassigned',
                'created' => $customer->created_at ? $customer->created_at->diffForHumans() : 'Unknown',
            ];

            $overdueCount = 0;
            $totalDue = 0;
            $invoices = $customer->invoices()->get();
            foreach ($invoices as $inv) {
                if ($inv->due_date && $inv->due_date->isPast() && $inv->payment_status !== 'paid') {
                    $overdueCount++;
                    $totalDue += $inv->balance_due;
                }
            }

            if ($overdueCount > 0) {
                $data['overdue_invoices'] = $overdueCount;
                $data['total_due'] = $totalDue;
            }

            $contextStr = $isLatest ? "This is the latest customer added to the CRM." : "This is a customer the admin searched for.";
            $prompt = "You are a helpful CRM AI Assistant chatting with the Admin in Hinglish (Hindi written in English).
            Here is the raw data for a customer from the MySQL database:
            " . json_encode($data) . "
            Context: " . $contextStr . "
            Task: Write a 2-3 line natural, polite conversational summary of this customer. If there are overdue invoices, warn the admin. Keep it professional but helpful.";

            $response = $client->chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => $prompt],
                ],
                'temperature' => 0.7,
            ]);

            return $response->choices[0]->message->content;
        } catch (\Exception $e) {
            \Log::error("OpenAI Generation Error: " . $e->getMessage());
        }

        // Fallback
        return $this->localBuildConversationalIntro($customer, $isLatest);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET PROFILE (from list selection)
    // ─────────────────────────────────────────────────────────────────────────

    public function getProfile(Request $request)
    {
        $startTime = microtime(true);
        $request->validate(['customer_id' => 'required|integer']);

        $isAdmin = Auth::user()->hasRole('admin');
        $userId  = Auth::id();

        $query = Customer::query();
        if (!$isAdmin) {
            $query->where('assigned_to', $userId);
        }

        $customer = $query->findOrFail($request->customer_id);
        $profile  = $this->buildCustomerProfile($customer, $isAdmin);
        $intro    = $this->buildConversationalIntro($customer, false);

        $this->logQuery($userId, 'select:' . $customer->id, 'customer_select', 'customer', $customer->id, 1, $startTime, $request);

        return response()->json([
            'type'         => 'customer_profile',
            'intro'        => $intro,
            'profile'      => $profile,
            'context_id'   => $profile['id'],
            'context_type' => 'customer',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FOLLOW-UP HANDLER
    // ─────────────────────────────────────────────────────────────────────────

    private function handleFollowUp(Customer $customer, string $subIntent, bool $isAdmin): string
    {
        $name = $customer->name;

        switch ($subIntent) {
            case 'payment':
                if (!$isAdmin) return '🔒 Payment details sirf admin dekh sakta hai.';
                $invoices = $customer->invoices()->latest()->get();
                if ($invoices->isEmpty()) return "**{$name}** ke liye koi bhi invoice record nahi mila abhi tak.";

                $lines    = [];
                $anyOverdue = false;
                foreach ($invoices as $inv) {
                    $overdueTag = '';
                    if ($inv->due_date && $inv->due_date->isPast() && $inv->payment_status !== 'paid') {
                        $overdueTag = ' ⚠️ OVERDUE';
                        $anyOverdue = true;
                    }
                    $lines[] = "• **{$inv->invoice_no}** — Total: ₹{$inv->total} | Paid: ₹{$inv->paid_amount} | Balance: ₹{$inv->balance_due} | " . strtoupper($inv->payment_status ?? 'unpaid') . $overdueTag;
                    if ($inv->due_date) {
                        $lines[] = "  📅 Due date: " . $inv->due_date->format('d M Y') . ($inv->due_date->isPast() && $inv->payment_status !== 'paid' ? ' (Beeet gaya!)' : '');
                    }
                }

                $response = "**{$name}** ke payment details:\n\n" . implode("\n", $lines);
                if ($anyOverdue) {
                    $response .= "\n\n⚠️ Kuch invoices overdue hain — inhe jaldi settle karna chahiye!";
                }
                return $response;

            case 'status':
                $stage    = $customer->auto_stage;
                $assigned = $customer->assignee ? $customer->assignee->name : 'kisi ko nahi';
                $timeFields = [
                    'Registration' => $customer->registration_date,
                    'LMC Done'     => $customer->lmc_date,
                    'RFC Done'     => $customer->rfc_date,
                    'JMR Done'     => $customer->jmr_date,
                    'Converted'    => $customer->conversion_date,
                ];
                $done    = collect($timeFields)->filter()->map(function ($d, $k) { return "✅ **{$k}**: " . $d->format('d M Y'); });
                $pending = collect($timeFields)->reject(function ($d) { return $d; })->map(function ($v, $k) { return "⏳ **{$k}**: Abhi baki hai"; });
                $all     = $done->merge($pending)->join("\n");
                return "**{$name}** ka current stage: **{$stage}**\nAssigned to: **{$assigned}**\n\n**Progress:**\n{$all}";

            case 'task':
                $tasks = $customer->tasks()->whereIn('status', ['todo', 'progress', 'review'])->with('assignee')->get();
                if ($tasks->isEmpty()) return "Acchi baat hai! **{$name}** ke liye koi bhi pending task nahi hai abhi.";
                $lines = $tasks->map(function ($t) {
                    $assigneeName = $t->assignee ? $t->assignee->name : '—';
                    $dueDate      = $t->due_date ? $t->due_date->format('d M Y') : 'N/A';
                    $overdueTag   = $t->due_date && $t->due_date->isPast() ? ' ⚠️' : '';
                    return "• [{$t->status}] **{$t->title}** — {$assigneeName} | Due: {$dueDate}{$overdueTag}";
                })->join("\n");
                return "**{$name}** ke active tasks ({$tasks->count()}):\n\n{$lines}";

            case 'ticket':
                $tickets = $customer->tickets()->whereNotIn('status', ['closed'])->get();
                if ($tickets->isEmpty()) return "**{$name}** ke koi bhi open ticket nahi hain — sab theek hai! ✅";
                $lines = $tickets->map(function ($t) {
                    return "• [{$t->status}] **#{$t->ticket_no}** — {$t->title} | Priority: {$t->priority}";
                })->join("\n");
                return "**{$name}** ke open tickets ({$tickets->count()}):\n\n{$lines}";

            case 'contact':
                $parts = ["📞 Phone: **{$customer->phone}**"];
                if ($customer->email)   $parts[] = "📧 Email: **{$customer->email}**";
                if ($customer->address) $parts[] = "🏠 Address: {$customer->address}, {$customer->city}";
                if ($customer->society) $parts[] = "🏘️ Society: **{$customer->society}**";
                return "**{$name}** ka contact info:\n\n" . implode("\n", $parts);

            case 'invoice':
                if (!$isAdmin) return '🔒 Invoice details sirf admin dekh sakta hai.';
                $invoices = $customer->invoices()->latest()->take(5)->get();
                if ($invoices->isEmpty()) return "**{$name}** ke koi invoice nahi hain abhi tak.";
                $lines = $invoices->map(function ($i) {
                    return "• **{$i->invoice_no}** — ₹{$i->total} | " . strtoupper($i->payment_status ?? 'unpaid') . " | " . ($i->invoice_date ? $i->invoice_date->format('d M Y') : '—');
                })->join("\n");
                return "**{$name}** ke invoices:\n\n{$lines}";

            case 'timeline':
                $fields = [
                    'Registration' => $customer->registration_date,
                    'LMC'          => $customer->lmc_date,
                    'RFC'          => $customer->rfc_date,
                    'JMR'          => $customer->jmr_date,
                    'Conversion'   => $customer->conversion_date,
                ];
                $done    = collect($fields)->filter()->map(function ($d, $k) { return "✅ **{$k}**: " . $d->format('d M Y'); });
                $pending = collect($fields)->reject(function ($d) { return $d; })->map(function ($v, $k) { return "⏳ **{$k}**: Pending"; });
                if ($done->isEmpty()) return "**{$name}** ke liye abhi koi milestone complete nahi hua.";
                return "**{$name}** ki progress timeline:\n\n" . $done->merge($pending)->join("\n");

            case 'assigned':
                $agentName = $customer->assignee ? $customer->assignee->name : 'Koi assign nahi hai';
                return "**{$name}** currently **{$agentName}** ko assign hai." . ($customer->assignee ? " Inhe aap admin panel se change kar sakte hain." : " Admin panel se kisi ko assign karein.");

            default:
                return "Aur kya jaanna chahenge **{$name}** ke baare mein?\n\nType karein: **Status**, **Payment**, **Invoice**, **Task**, **Ticket**, **Contact**, **Timeline**";
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORMAT HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function formatInvoiceResponse(Invoice $invoice, bool $isAdmin): string
    {
        $customer = $invoice->customer;
        $paid     = number_format($invoice->paid_amount ?? 0, 2);
        $balance  = number_format($invoice->balance_due, 2);
        $total    = number_format($invoice->total ?? 0, 2);
        $status   = strtoupper($invoice->payment_status ?? 'unpaid');
        $overdue  = $invoice->due_date && $invoice->due_date->isPast() && $invoice->payment_status !== 'paid'
            ? "\n\n⚠️ **OVERDUE!** Payment due tha " . $invoice->due_date->format('d M Y') . " ko — abhi bhi baki hai!"
            : '';

        $text  = "Invoice **#{$invoice->invoice_no}** mila!\n";
        $text .= "👤 Customer: **" . ($customer ? $customer->name : 'Unknown') . "**\n";
        $text .= "📅 Date: " . ($invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : '—') . "\n";
        if ($invoice->due_date) {
            $text .= "⏰ Due: " . $invoice->due_date->format('d M Y') . "\n";
        }
        if ($isAdmin) {
            $text .= "💰 Total: ₹{$total} | Paid: ₹{$paid} | Balance: ₹{$balance}\n";
            $text .= "📊 Status: **{$status}**";
            $text .= $overdue;
        }
        return $text;
    }

    private function formatQuotationResponse(Quotation $quotation): string
    {
        $customer = $quotation->customer;
        return "Quotation **#{$quotation->quotation_no}** mila!\n"
            . "👤 Customer: **" . ($customer ? $customer->name : 'Unknown') . "**\n"
            . "📅 Date: " . ($quotation->date ? $quotation->date->format('d M Y') : '—') . "\n"
            . "⏰ Valid Till: " . ($quotation->valid_till ? $quotation->valid_till->format('d M Y') : '—') . "\n"
            . "💰 Total: ₹" . number_format($quotation->total ?? 0, 2) . "\n"
            . "📊 Status: **" . strtoupper($quotation->status ?? 'draft') . "**";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function notFound(string $query, int $userId, float $startTime, Request $request, string $customMessage = null)
    {
        $this->logQuery($userId, $query, 'not_found', null, null, 0, $startTime, $request);
        
        $msg = $customMessage ?? "Hmm, \"**{$query}**\" ke liye koi record nahi mila database mein. 🔍\nKya aap spelling check kar sakte hain, ya phone number try kar sakte hain?\n\nKuch aur try karein:\n• Customer ka poora naam ya phone\n• Invoice number (e.g. INV-001)\n• LMC ID (e.g. LMC-2024-XXXX)\n• Ya type karein: **latest entry dikhao**";
        
        return response()->json([
            'type'    => 'not_found',
            'message' => $msg,
        ]);
    }

    private function logQuery(int $userId, string $query, string $intent, ?string $resultType, ?int $resultId, int $count, float $startTime, Request $request): void
    {
        try {
            ChatbotLog::create([
                'user_id'          => $userId,
                'query'            => $query,
                'intent'           => $intent,
                'result_type'      => $resultType,
                'result_id'        => $resultId,
                'results_count'    => $count,
                'response_time_ms' => (int) round((microtime(true) - $startTime) * 1000),
                'ip_address'       => $request->ip(),
            ]);
        } catch (\Exception $e) {
            // Non-critical
        }
    }

    public function clearContext(Request $request)
    {
        return response()->json(['type' => 'cleared', 'message' => '🔄 Context clear ho gaya! Ab naya search karein — naam, phone, invoice number ya "latest entry dikhao" type karein.']);
    }
}
