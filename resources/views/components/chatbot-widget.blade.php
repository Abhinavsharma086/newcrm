{{-- ═══════════════════════════════════════════════════════════════
     CRM AI Chatbot Widget v2 — Component
     Includes: Voice input, Open/Edit buttons, conversational UI
     ═══════════════════════════════════════════════════════════════ --}}

@auth
<link rel="stylesheet" href="{{ asset('css/chatbot.css') }}?v={{ filemtime(public_path('css/chatbot.css')) }}">

{{-- ── Floating Trigger Button ────────────────────────────────── --}}
<button id="crm-chatbot-trigger" title="CRM AI Assistant" aria-label="Open CRM Chatbot">
    <i class="fas fa-robot chatbot-icon"></i>
    <i class="fas fa-times chatbot-close-icon"></i>
</button>

{{-- ── Chat Window ─────────────────────────────────────────────── --}}
<div id="crm-chatbot-window" role="dialog" aria-label="CRM AI Assistant" aria-modal="true">

    {{-- Header --}}
    <div id="crm-chatbot-header">
        <div class="chatbot-header-avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="chatbot-header-info">
            <div class="chatbot-header-title">Anthropic AI Assistant</div>
            <div class="chatbot-header-subtitle">Powered by Claude 3</div>
        </div>
        <div class="chatbot-header-status">Live</div>
        <div class="chatbot-header-actions">
            <button class="chatbot-header-btn" id="crm-chatbot-new-search" title="Naya Search">
                <i class="fas fa-search"></i>
            </button>
            <button class="chatbot-header-btn" onclick="document.getElementById('crm-chatbot-trigger').click()" title="Band Karo">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    {{-- Context Bar (shows selected customer name) --}}
    <div id="crm-chatbot-context-bar">
        <span>Context: <span class="chatbot-context-name" id="crm-chatbot-context-name">—</span></span>
        <button class="chatbot-context-clear" id="crm-chatbot-clear-context" title="Context Clear Karo">
            <i class="fas fa-times-circle"></i> Clear
        </button>
    </div>

    {{-- Messages Area --}}
    <div id="crm-chatbot-messages" aria-live="polite" aria-relevant="additions"></div>

    {{-- Quick Suggestions --}}
    <div id="crm-chatbot-suggestions"></div>

    {{-- Voice Recording Status Bar --}}
    <div id="crm-chatbot-voice-status"></div>

    {{-- Input Area --}}
    <div id="crm-chatbot-input-area">

        {{-- Mic Button (Voice Input) --}}
        <button
            id="crm-chatbot-mic"
            class="chatbot-icon-btn"
            title="🎤 Voice se bolo (Hindi / English)"
            aria-label="Voice input"
        >
            <i class="fas fa-microphone"></i>
        </button>

        {{-- Text Input --}}
        <textarea
            id="crm-chatbot-input"
            placeholder="Customer naam, phone, invoice ya 'latest entry dikhao'…"
            rows="1"
            maxlength="500"
            autocomplete="off"
            aria-label="Search query"
        ></textarea>

        {{-- Send Button --}}
        <button id="crm-chatbot-send" class="chatbot-icon-btn" title="Send (Enter)" aria-label="Send message">
            <i class="fas fa-paper-plane"></i>
        </button>

    </div>

</div>

{{-- ── Pass Routes + Config to JS ─────────────────────────────── --}}
<script>
window.CHATBOT_ROUTES = {
    @if(auth()->user()->hasRole('admin'))
    search:  "{{ route('admin.chatbot.search') }}",
    profile: "{{ route('admin.chatbot.profile') }}",
    clear:   "{{ route('admin.chatbot.clear') }}",
    @else
    search:  "{{ route('employee.chatbot.search') }}",
    profile: "{{ route('employee.chatbot.profile') }}",
    clear:   "{{ route('employee.chatbot.clear') }}",
    @endif
};
window.CHATBOT_USER_ROLE = "{{ auth()->user()->hasRole('admin') ? 'admin' : 'employee' }}";
</script>
<script src="{{ asset('js/chatbot.js') }}?v={{ filemtime(public_path('js/chatbot.js')) }}"></script>
@endauth
