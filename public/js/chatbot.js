/**
 * CRM AI Chatbot Widget v2 — Frontend Engine
 * Features: Smart search, context follow-ups, voice input, open/edit buttons
 */
(function () {
    'use strict';

    // ── State ─────────────────────────────────────────────────────────────
    const state = {
        isOpen: false,
        isTyping: false,
        contextId: null,
        contextType: null,
        contextName: null,
        voiceEnabled: true,
        isRecording: false,
        recognition: null,
    };

    // ── DOM refs ──────────────────────────────────────────────────────────
    let trigger, windowEl, messages, inputEl, sendBtn, micBtn,
        suggestionsEl, contextBar, contextNameEl, voiceStatusEl;

    // ── Routes from Blade ─────────────────────────────────────────────────
    const ROUTES = window.CHATBOT_ROUTES || {};

    // ── Suggestion sets ───────────────────────────────────────────────────
    const SUGGESTIONS_DEFAULT = [
        'Latest entry dikhao', 'Customer search karo', 'Invoice dhundho'
    ];
    const SUGGESTIONS_CONTEXT = [
        'Status batao', 'Payment due kab?', 'Invoice dikhao',
        'Tasks kya hain?', 'Timeline dikhao', 'Kaun assign hai?'
    ];

    // ─────────────────────────────────────────────────────────────────────
    // INIT
    // ─────────────────────────────────────────────────────────────────────
    function init() {
        trigger       = document.getElementById('crm-chatbot-trigger');
        windowEl      = document.getElementById('crm-chatbot-window');
        messages      = document.getElementById('crm-chatbot-messages');
        inputEl       = document.getElementById('crm-chatbot-input');
        sendBtn       = document.getElementById('crm-chatbot-send');
        micBtn        = document.getElementById('crm-chatbot-mic');
        suggestionsEl = document.getElementById('crm-chatbot-suggestions');
        contextBar    = document.getElementById('crm-chatbot-context-bar');
        contextNameEl = document.getElementById('crm-chatbot-context-name');
        voiceStatusEl = document.getElementById('crm-chatbot-voice-status');

        if (!trigger) return;

        trigger.addEventListener('click', toggleChat);
        sendBtn.addEventListener('click', handleSend);
        inputEl.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); }
        });
        inputEl.addEventListener('input', autoResizeTextarea);

        // New search / clear buttons
        const newSearchBtn = document.getElementById('crm-chatbot-new-search');
        if (newSearchBtn) newSearchBtn.addEventListener('click', clearContext);
        const clearCtxBtn = document.getElementById('crm-chatbot-clear-context');
        if (clearCtxBtn) clearCtxBtn.addEventListener('click', clearContext);

        // Voice init
        initVoice();

        renderSuggestions(SUGGESTIONS_DEFAULT);

        // Welcome message
        setTimeout(function () {
            appendBotMessage(
                '👋 **Namaste!** Main aapka CRM Assistant hoon.\n\n' +
                'Koi bhi **customer ka naam**, **phone number**, **email**, **invoice number** ya **LMC ID** type karein — ' +
                'ya poochein: _"Latest entry dikhao"_ 🔍\n\n' +
                'Follow-up questions bhi puch sakte hain!'
            );
        }, 350);
    }

    // ─────────────────────────────────────────────────────────────────────
    // VOICE INPUT (Web Speech API — browser native, no external API)
    // ─────────────────────────────────────────────────────────────────────
    function initVoice() {
        if (!micBtn) return;

        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            micBtn.classList.add('not-supported');
            micBtn.title = 'Voice input aapke browser mein support nahi hai (Chrome recommended)';
            micBtn.addEventListener('click', function () {
                appendBotMessage('⚠️ Voice input sirf Chrome browser mein kaam karta hai. Please Chrome use karein ya text type karein.');
            });
            return;
        }

        const rec = new SpeechRecognition();
        rec.lang = 'hi-IN';          // Hindi primary
        rec.interimResults = true;
        rec.maxAlternatives = 1;
        rec.continuous = false;
        state.recognition = rec;

        rec.onstart = function () {
            state.isRecording = true;
            micBtn.classList.add('is-recording');
            if (voiceStatusEl) {
                voiceStatusEl.innerHTML = '<span style="display:flex;align-items:center;gap:8px;justify-content:center">' +
                    '<span class="chatbot-voice-waves">' +
                    '<span class="chatbot-voice-wave"></span><span class="chatbot-voice-wave"></span>' +
                    '<span class="chatbot-voice-wave"></span><span class="chatbot-voice-wave"></span>' +
                    '<span class="chatbot-voice-wave"></span></span>' +
                    ' Bol raha hoon... (baat karo)</span>';
                voiceStatusEl.classList.add('visible');
            }
        };

        rec.onresult = function (event) {
            let transcript = '';
            for (let i = event.resultIndex; i < event.results.length; i++) {
                transcript += event.results[i][0].transcript;
            }
            inputEl.value = transcript;
            autoResizeTextarea();
        };

        rec.onend = function () {
            state.isRecording = false;
            micBtn.classList.remove('is-recording');
            if (voiceStatusEl) voiceStatusEl.classList.remove('visible');

            // Auto-send if something was captured
            if (inputEl.value.trim()) {
                setTimeout(handleSend, 300);
            }
        };

        rec.onerror = function (e) {
            state.isRecording = false;
            micBtn.classList.remove('is-recording');
            if (voiceStatusEl) voiceStatusEl.classList.remove('visible');

            if (e.error !== 'no-speech') {
                appendBotMessage('🎤 Voice error: ' + (e.error === 'not-allowed' ? 'Microphone permission deny hai. Browser settings mein allow karein.' : e.error));
            }
        };

        micBtn.addEventListener('click', function () {
            if (!state.isRecording) {
                try {
                    rec.start();
                } catch (err) {
                    appendBotMessage('🎤 Voice input shuru nahi ho saka. Dobara try karein.');
                }
            } else {
                rec.stop();
            }
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // TOGGLE CHAT
    // ─────────────────────────────────────────────────────────────────────
    function toggleChat() {
        state.isOpen = !state.isOpen;
        trigger.classList.toggle('is-open', state.isOpen);
        windowEl.classList.toggle('is-visible', state.isOpen);
        if (state.isOpen) { inputEl.focus(); scrollToBottom(); }
    }

    // ─────────────────────────────────────────────────────────────────────
    // SEND
    // ─────────────────────────────────────────────────────────────────────
    function handleSend() {
        const query = inputEl.value.trim();
        if (!query || state.isTyping) return;
        appendUserMessage(query);
        inputEl.value = '';
        inputEl.style.height = 'auto';
        sendQuery(query);
    }

    // ─────────────────────────────────────────────────────────────────────
    // AJAX — SEARCH
    // ─────────────────────────────────────────────────────────────────────
    function sendQuery(query) {
        showTyping();
        setSendDisabled(true);

        jQuery.ajax({
            url: ROUTES.search,
            method: 'POST',
            data: {
                query: query,
                context_id: state.contextId,
                context_type: state.contextType,
                _token: jQuery('meta[name="csrf-token"]').attr('content'),
            },
            success: function (res) {
                hideTyping();
                setSendDisabled(false);
                handleResponse(res);
            },
            error: function (xhr) {
                hideTyping();
                setSendDisabled(false);
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : `Server Error: Status ${xhr.status}. ` + (xhr.responseText ? xhr.responseText.substring(0, 100) : 'No response');
                appendBotMessage('❌ **Error:** ' + msg);
            },
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // AJAX — GET FULL PROFILE
    // ─────────────────────────────────────────────────────────────────────
    function fetchProfile(customerId, customerName) {
        appendUserMessage('👤 ' + customerName);
        showTyping();
        setSendDisabled(true);

        jQuery.ajax({
            url: ROUTES.profile,
            method: 'POST',
            data: {
                customer_id: customerId,
                _token: jQuery('meta[name="csrf-token"]').attr('content'),
            },
            success: function (res) {
                hideTyping();
                setSendDisabled(false);
                handleResponse(res);
            },
            error: function (xhr) {
                hideTyping();
                setSendDisabled(false);
                const msg = `Status ${xhr.status}. ` + (xhr.responseText ? xhr.responseText.substring(0, 100) : 'No response');
                appendBotMessage('❌ Profile load nahi ho saka. Error: ' + msg);
            },
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // RESPONSE HANDLER
    // ─────────────────────────────────────────────────────────────────────
    function handleResponse(res) {
        if (res.context_id) {
            state.contextId   = res.context_id;
            state.contextType = res.context_type || 'customer';
        }

        switch (res.type) {
            case 'customer_profile':
                // Show conversational intro first (if provided)
                if (res.intro) {
                    appendBotMessage(res.intro);
                }
                renderCustomerProfile(res.profile);
                state.contextName = res.profile.name;
                updateContextBar();
                renderSuggestions(SUGGESTIONS_CONTEXT);
                break;

            case 'multiple':
                appendBotMessage(res.message);
                renderMultipleResults(res.list);
                break;

            case 'followup':
            case 'invoice':
            case 'quotation':
            case 'not_found':
            case 'cleared':
                appendBotMessage(res.message);
                if (res.type === 'cleared') clearContext(false);
                break;

            default:
                appendBotMessage(res.message || 'Kuch problem hui. Dobara try karein.');
        }

        scrollToBottom();
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER — USER MESSAGE
    // ─────────────────────────────────────────────────────────────────────
    function appendUserMessage(text) {
        const div = document.createElement('div');
        div.className = 'chatbot-msg user';
        div.innerHTML = '<div class="chatbot-bubble">' + escHtml(text) + '</div>';
        messages.appendChild(div);
        scrollToBottom();
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER — BOT MESSAGE
    // ─────────────────────────────────────────────────────────────────────
    function appendBotMessage(text) {
        const div = document.createElement('div');
        div.className = 'chatbot-msg bot';
        const bubble = document.createElement('div');
        bubble.className = 'chatbot-bubble';
        bubble.innerHTML = formatMarkdown(text);
        div.appendChild(bubble);
        messages.appendChild(div);
        scrollToBottom();
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER — CUSTOMER PROFILE CARD (with Open/Edit buttons)
    // ─────────────────────────────────────────────────────────────────────
    function renderCustomerProfile(p) {
        const card = document.createElement('div');
        card.className = 'chatbot-profile-card';

        const initials = (p.name || '?').split(' ').map(function (w) { return w[0]; }).join('').toUpperCase().slice(0, 2);

        // ── Header
        let html = '<div class="chatbot-profile-header">';
        html += '<div class="chatbot-profile-avatar">' + escHtml(initials) + '</div>';
        html += '<div><div class="chatbot-profile-name">' + escHtml(p.name || '') + '</div>';
        html += '<div class="chatbot-profile-sub">' + (p.phone ? escHtml(p.phone) : '') + (p.phone && p.email ? ' · ' : '') + (p.email ? escHtml(p.email) : '') + '</div></div>';
        html += '<span class="chatbot-stage-badge ' + escHtml(p.stage_color || 'light') + '">' + escHtml(p.stage || 'New') + '</span>';
        html += '</div>';

        // ── Open / Edit Action Buttons ─────────────────────────────────────
        html += '<div class="chatbot-action-buttons">';
        if (p.profile_url) {
            html += '<a href="' + escHtml(p.profile_url) + '" target="_blank" class="chatbot-btn-open">';
            html += '<i class="fas fa-external-link-alt" style="font-size:12px"></i> Open Profile</a>';
        }
        if (p.edit_url) {
            html += '<a href="' + escHtml(p.edit_url) + '" target="_blank" class="chatbot-btn-edit">';
            html += '<i class="fas fa-edit" style="font-size:12px"></i> Edit</a>';
        }
        html += '</div>';

        html += '<div class="chatbot-profile-body">';

        // ── Basic Info
        html += '<div class="chatbot-profile-section">';
        html += '<div class="chatbot-section-title">📋 Basic Info</div>';
        html += '<div class="chatbot-info-grid">';
        html += infoItem('Society', p.society);
        html += infoItem('Assigned To', p.assigned_to);
        html += infoItem('Source', p.source);
        html += infoItem('CRN No', p.crn_no);
        html += infoItem('LMC ID', p.lmc_id);
        html += infoItem('Meter No', p.meter_no);
        html += infoItem('Burner Type', p.burner_type);
        html += infoItem('Contractor', p.contractor);
        html += '</div>';
        if (p.address) {
            html += '<div style="margin-top:7px"><span class="chatbot-info-label">📍 Address</span>';
            html += '<div class="chatbot-info-value" style="margin-top:2px">' + escHtml(p.address) + '</div></div>';
        }
        if (p.time_ago) {
            html += '<div style="margin-top:5px;font-size:10px;color:rgba(255,255,255,0.3)">🕐 Added ' + escHtml(p.time_ago) + '</div>';
        }
        html += '</div>';

        // ── Timeline
        const stages = ['Registration', 'LMC Done', 'RFC Done', 'JMR Done', 'Converted'];
        const tl = p.timeline || {};
        html += '<div class="chatbot-profile-section"><div class="chatbot-section-title">📅 Progress Timeline</div>';
        html += '<div class="chatbot-timeline">';
        stages.forEach(function (s) {
            const dt = tl[s];
            html += '<div class="chatbot-timeline-item">';
            html += '<div class="chatbot-timeline-dot ' + (dt ? 'done' : 'empty') + '"></div>';
            html += '<span class="chatbot-timeline-label">' + escHtml(s) + '</span>';
            html += '<span class="chatbot-timeline-date ' + (dt ? '' : 'empty') + '">' + (dt ? escHtml(dt) : 'Pending') + '</span>';
            html += '</div>';
        });
        html += '</div></div>';

        // ── Billing (admin)
        if (typeof p.invoices !== 'undefined') {
            html += '<div class="chatbot-profile-section"><div class="chatbot-section-title">💰 Billing</div>';
            if (p.total_invoiced) {
                html += '<div class="chatbot-billing-summary">';
                html += '<div class="chatbot-billing-box"><div class="chatbot-billing-box-label">Invoiced</div><div class="chatbot-billing-box-value white">₹' + escHtml(p.total_invoiced) + '</div></div>';
                html += '<div class="chatbot-billing-box"><div class="chatbot-billing-box-label">Paid</div><div class="chatbot-billing-box-value green">₹' + escHtml(p.total_paid) + '</div></div>';
                const dueClass = parseFloat(p.total_due) > 0 ? 'red' : 'green';
                html += '<div class="chatbot-billing-box"><div class="chatbot-billing-box-label">Balance</div><div class="chatbot-billing-box-value ' + dueClass + '">₹' + escHtml(p.total_due) + '</div></div>';
                html += '</div>';
            }
            if (p.invoices && p.invoices.length > 0) {
                html += '<div style="margin-top:8px">';
                p.invoices.forEach(function (inv) {
                    const sc = inv.status === 'paid' ? 'paid' : (inv.status === 'partial' ? 'partial' : 'unpaid');
                    html += '<div class="chatbot-invoice-item">';
                    html += '<div class="chatbot-invoice-row"><span class="chatbot-invoice-no">' + escHtml(inv.invoice_no) + '</span><span class="chatbot-status-pill ' + sc + '">' + escHtml(inv.status || 'unpaid') + '</span></div>';
                    html += '<div class="chatbot-invoice-row" style="margin-top:4px;color:rgba(255,255,255,0.45);font-size:11px">';
                    html += '<span>₹' + escHtml(inv.total) + ' · Paid: ₹' + escHtml(inv.paid) + ' · Due: ₹' + escHtml(inv.balance) + '</span>';
                    html += inv.overdue ? '<span class="chatbot-overdue-tag">⚠️ OVERDUE</span>' : '';
                    html += '</div>';
                    if (inv.due_date) {
                        html += '<div style="font-size:10px;color:rgba(255,255,255,0.3);margin-top:2px">Due: ' + escHtml(inv.due_date) + '</div>';
                    }
                    html += '</div>';
                });
                html += '</div>';
            } else {
                html += '<div style="color:rgba(255,255,255,0.28);font-size:12px;font-style:italic;margin-top:5px">Koi invoice nahi hai</div>';
            }
            html += '</div>';
        }

        // ── Tasks
        if (p.tasks && p.tasks.length > 0) {
            html += '<div class="chatbot-profile-section"><div class="chatbot-section-title">✅ Active Tasks (' + p.tasks.length + ')</div>';
            p.tasks.forEach(function (t) {
                html += '<div class="chatbot-list-item">';
                html += '<div class="chatbot-list-dot ' + (t.overdue ? 'overdue' : (t.priority || 'medium')) + '"></div>';
                html += '<div style="flex:1"><div style="font-size:12px;color:#ddd">' + escHtml(t.title) + '</div>';
                html += '<div style="font-size:10px;color:rgba(255,255,255,0.38)">' + escHtml(t.assignee || '—') + ' · Due: ' + escHtml(t.due_date || 'N/A') + (t.overdue ? ' ⚠️' : '') + '</div></div>';
                html += '<span style="font-size:10px;background:rgba(255,255,255,0.07);padding:2px 7px;border-radius:8px;color:rgba(255,255,255,0.45)">' + escHtml(t.status) + '</span>';
                html += '</div>';
            });
            html += '</div>';
        }

        // ── Tickets
        if (p.tickets && p.tickets.length > 0) {
            html += '<div class="chatbot-profile-section"><div class="chatbot-section-title">🎫 Open Tickets (' + p.tickets.length + ')</div>';
            p.tickets.forEach(function (t) {
                html += '<div class="chatbot-list-item">';
                html += '<div class="chatbot-list-dot ' + (t.priority || 'medium') + '"></div>';
                html += '<div style="flex:1"><div style="font-size:12px;color:#ddd">' + escHtml(t.title) + '</div>';
                html += '<div style="font-size:10px;color:rgba(255,255,255,0.38)">#' + escHtml(t.ticket_no) + ' · Priority: ' + escHtml(t.priority) + '</div></div>';
                html += '<span style="font-size:10px;background:rgba(255,255,255,0.07);padding:2px 7px;border-radius:8px;color:rgba(255,255,255,0.45)">' + escHtml(t.status) + '</span>';
                html += '</div>';
            });
            html += '</div>';
        }

        // ── Leads
        if (p.leads && p.leads.length > 0) {
            html += '<div class="chatbot-profile-section"><div class="chatbot-section-title">🎯 Leads (' + p.leads.length + ')</div>';
            p.leads.forEach(function (l) {
                html += '<div class="chatbot-list-item">';
                html += '<div class="chatbot-list-dot medium"></div>';
                html += '<div style="flex:1"><div style="font-size:12px;color:#ddd">' + escHtml(l.title || 'Lead') + '</div>';
                html += '<div style="font-size:10px;color:rgba(255,255,255,0.38)">' + escHtml(l.stage || '—') + ' · ₹' + escHtml(l.value || '0') + ' · Follow up: ' + escHtml(l.follow_up || 'N/A') + '</div></div>';
                html += '</div>';
            });
            html += '</div>';
        }

        html += '</div>'; // profile-body
        card.innerHTML = html;
        messages.appendChild(card);
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER — MULTIPLE RESULTS
    // ─────────────────────────────────────────────────────────────────────
    function renderMultipleResults(list) {
        const container = document.createElement('div');
        container.className = 'chatbot-result-list';

        list.forEach(function (item) {
            const el = document.createElement('div');
            el.className = 'chatbot-result-item';
            el.innerHTML =
                '<div>' +
                  '<div class="chatbot-result-name">' + escHtml(item.name) + '</div>' +
                  '<div class="chatbot-result-meta">' +
                    (item.phone ? '📞 ' + escHtml(item.phone) : '') +
                    (item.society ? ' · 🏘️ ' + escHtml(item.society) : '') +
                    (item.stage ? ' · ' + escHtml(item.stage) : '') +
                  '</div>' +
                  (item.time_ago ? '<div class="chatbot-result-timeago">🕐 ' + escHtml(item.time_ago) + '</div>' : '') +
                '</div>' +
                '<i class="fas fa-chevron-right chatbot-result-arrow"></i>';

            el.addEventListener('click', function () {
                fetchProfile(item.id, item.name);
                container.remove();
            });

            container.appendChild(el);
        });

        messages.appendChild(container);
        scrollToBottom();
    }

    // ─────────────────────────────────────────────────────────────────────
    // RENDER — SUGGESTIONS
    // ─────────────────────────────────────────────────────────────────────
    function renderSuggestions(list) {
        suggestionsEl.innerHTML = '';
        list.forEach(function (text) {
            const btn = document.createElement('button');
            btn.className = 'chatbot-suggestion-btn';
            btn.textContent = text;
            btn.addEventListener('click', function () { inputEl.value = text; handleSend(); });
            suggestionsEl.appendChild(btn);
        });
    }

    // ─────────────────────────────────────────────────────────────────────
    // CONTEXT BAR
    // ─────────────────────────────────────────────────────────────────────
    function updateContextBar() {
        if (state.contextId && state.contextName) {
            contextBar.classList.add('visible');
            if (contextNameEl) contextNameEl.textContent = state.contextName;
        } else {
            contextBar.classList.remove('visible');
        }
    }

    function clearContext(callApi) {
        state.contextId   = null;
        state.contextType = null;
        state.contextName = null;
        contextBar.classList.remove('visible');
        renderSuggestions(SUGGESTIONS_DEFAULT);

        if (callApi !== false) {
            jQuery.post(ROUTES.clear, { _token: jQuery('meta[name="csrf-token"]').attr('content') });
            appendBotMessage('🔄 **Naya search!** Customer naam, phone, email, invoice ya _"latest entry dikhao"_ type karein.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────
    function showTyping() {
        state.isTyping = true;
        const div = document.createElement('div');
        div.className = 'chatbot-msg bot';
        div.id = 'chatbot-typing-indicator';
        div.innerHTML = '<div class="chatbot-bubble" style="padding:8px 14px"><div class="chatbot-typing">' +
            '<div class="chatbot-typing-dot"></div><div class="chatbot-typing-dot"></div><div class="chatbot-typing-dot"></div>' +
            '</div></div>';
        messages.appendChild(div);
        scrollToBottom();
    }

    function hideTyping() {
        state.isTyping = false;
        const el = document.getElementById('chatbot-typing-indicator');
        if (el) el.remove();
    }

    function setSendDisabled(val) { sendBtn.disabled = val; }

    function scrollToBottom() {
        setTimeout(function () { messages.scrollTop = messages.scrollHeight; }, 50);
    }

    function autoResizeTextarea() {
        inputEl.style.height = 'auto';
        inputEl.style.height = Math.min(inputEl.scrollHeight, 90) + 'px';
    }

    function escHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function formatMarkdown(text) {
        if (!text) return '';
        return escHtml(text)
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/_(.+?)_/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    function infoItem(label, value) {
        const val = value
            ? '<span class="chatbot-info-value">' + escHtml(value) + '</span>'
            : '<span class="chatbot-info-value na">—</span>';
        return '<div class="chatbot-info-item"><span class="chatbot-info-label">' + escHtml(label) + '</span>' + val + '</div>';
    }

    // ─────────────────────────────────────────────────────────────────────
    // BOOT
    // ─────────────────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
