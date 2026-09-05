@extends('layouts.admin')

@section('title', 'Task Details: ' . $task->title)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-{{ $task->priority == 'high' ? 'danger' : ($task->priority == 'medium' ? 'warning text-dark' : 'info') }} px-3 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">
                    {{ $task->priority }} Priority
                </span>
                <span class="badge bg-{{ $task->status == 'done' ? 'success' : ($task->status == 'progress' ? 'primary' : ($task->status == 'review' ? 'warning text-dark' : 'secondary')) }} px-3 py-1 text-uppercase fw-bold" style="font-size: 0.75rem;">
                    {{ $task->status }}
                </span>
                @if($task->customer)
                <span class="badge bg-light text-dark border px-3 py-1">
                    <i class="fas fa-user-tag text-primary me-1"></i> {{ $task->customer->name }}
                </span>
                @endif
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $task->title }}</h3>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                <i class="fas fa-edit me-1"></i> Edit Task
            </a>
            <a href="{{ route('admin.tasks.index') }}" class="btn btn-outline-secondary shadow-sm rounded-pill px-3">
                <i class="fas fa-arrow-left me-1"></i> Back to Tasks
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Content (Left Column) -->
        <div class="col-lg-8">
            <!-- Task Overview Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-info-circle text-primary me-2"></i>Task Details & Scope</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small">Status:</span>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="updateTaskStatus(this.value)">
                            <option value="todo" {{ $task->status == 'todo' ? 'selected' : '' }}>To Do</option>
                            <option value="progress" {{ $task->status == 'progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="review" {{ $task->status == 'review' ? 'selected' : '' }}>Under Review</option>
                            <option value="done" {{ $task->status == 'done' ? 'selected' : '' }}>Done / Completed</option>
                        </select>
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($task->description)
                    <div class="mb-4 text-dark" style="white-space: pre-line; line-height: 1.6;">{{ $task->description }}</div>
                    @else
                    <div class="text-muted fst-italic mb-4">No detailed description provided.</div>
                    @endif

                    <!-- Task Checklist / Subtasks Section -->
                    <div class="p-3 rounded-4 border mb-3" style="background: #f8fafc;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-tasks text-primary me-2"></i>Task Checklist & Subtasks</h6>
                            @php
                                $checklist = $task->checklist ?? [];
                                $totalItems = count($checklist);
                                $completedItems = count(array_filter($checklist, fn($it) => !empty($it['completed'])));
                                $progressPercent = $totalItems > 0 ? round(($completedItems / $totalItems) * 100) : 0;
                            @endphp
                            <span class="badge bg-primary rounded-pill px-3 py-1">{{ $completedItems }} / {{ $totalItems }} Done</span>
                        </div>

                        @if($totalItems > 0)
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercent }}%;"></div>
                        </div>

                        <div class="list-group list-group-flush rounded-3">
                            @foreach($checklist as $item)
                            <div class="list-group-item bg-transparent px-2 py-2 border-0 d-flex align-items-center gap-3">
                                <input class="form-check-input mt-0 checklist-checkbox" type="checkbox" data-id="{{ $item['id'] }}" {{ !empty($item['completed']) ? 'checked' : '' }} style="width: 1.25rem; height: 1.25rem; cursor: pointer;" onchange="toggleChecklistItem('{{ $item['id'] }}', this.checked)">
                                <span class="{{ !empty($item['completed']) ? 'text-decoration-line-through text-muted' : 'text-dark fw-medium' }}">
                                    {{ $item['text'] }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-muted small mb-0">No checklist items defined. You can add them by editing this task.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Customer Bills & Invoices Section -->
            @if($task->customer)
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-invoice-dollar text-success me-2"></i>Customer GST Bills & Invoices</h6>
                        <small class="text-muted">All billing records linked to {{ $task->customer->name }}</small>
                    </div>
                    <a href="{{ route('admin.invoices.create') }}?customer_id={{ $task->customer->id }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> New Bill
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc;" class="small fw-bold text-secondary">
                                <tr>
                                    <th class="ps-4">Bill No</th>
                                    <th>Date</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due Balance</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($task->customer->invoices as $inv)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">
                                            {{ $inv->invoice_number }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">{{ $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d M Y') : '-' }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($inv->total_amount, 2) }}</td>
                                    <td class="text-end text-success fw-semibold">₹{{ number_format($inv->paid_amount, 2) }}</td>
                                    <td class="text-end text-danger fw-bold">₹{{ number_format(max(0, $inv->total_amount - $inv->paid_amount), 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-1" style="background: {{ $inv->payment_status == 'paid' ? '#dcfce7' : ($inv->payment_status == 'partial' ? '#fef3c7' : '#fee2e2') }}; color: {{ $inv->payment_status == 'paid' ? '#15803d' : ($inv->payment_status == 'partial' ? '#b45309' : '#b91c1c') }};">
                                            {{ strtoupper($inv->payment_status ?? 'UNPAID') }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.invoices.pdf', $inv) }}" class="btn btn-sm btn-light text-danger border" title="Download PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('admin.invoices.show', $inv) }}" class="btn btn-sm btn-light text-primary border" title="View Bill">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No GST bills found for this customer.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Customer Quotations Section -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-file-alt text-primary me-2"></i>Customer Quotations</h6>
                        <small class="text-muted">Proposals & Quotations created for {{ $task->customer->name }}</small>
                    </div>
                    <a href="{{ route('admin.quotations.create') }}?customer_id={{ $task->customer->id }}" class="btn btn-sm btn-primary rounded-pill px-3">
                        <i class="fas fa-plus me-1"></i> New Quotation
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background: #f8fafc;" class="small fw-bold text-secondary">
                                <tr>
                                    <th class="ps-4">Quotation #</th>
                                    <th>Date</th>
                                    <th>Valid Till</th>
                                    <th class="text-end">Value</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($task->customer->quotations as $quot)
                                <tr>
                                    <td class="ps-4">
                                        <a href="{{ route('admin.quotations.show', $quot) }}" class="fw-bold text-decoration-none" style="color: #2563eb;">
                                            {{ $quot->quotation_number }}
                                        </a>
                                    </td>
                                    <td class="small text-muted">{{ $quot->quotation_date ? \Carbon\Carbon::parse($quot->quotation_date)->format('d M Y') : '-' }}</td>
                                    <td class="small text-muted">{{ $quot->valid_until ? \Carbon\Carbon::parse($quot->valid_until)->format('d M Y') : '-' }}</td>
                                    <td class="text-end fw-bold">₹{{ number_format($quot->total_amount, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge rounded-pill px-3 py-1" style="background: {{ $quot->status == 'accepted' ? '#dcfce7' : ($quot->status == 'sent' ? '#eff6ff' : '#f1f5f9') }}; color: {{ $quot->status == 'accepted' ? '#15803d' : ($quot->status == 'sent' ? '#1d4ed8' : '#475569') }};">
                                            {{ strtoupper($quot->status) }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.quotations.pdf', $quot) }}" class="btn btn-sm btn-light text-danger border" title="Download PDF" target="_blank">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('admin.quotations.show', $quot) }}" class="btn btn-sm btn-light text-primary border" title="View Quotation">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No quotations generated yet for this customer.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Comments & Discussion -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-comments text-primary me-2"></i>Task Discussion & Notes</h6>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.tasks.add-comment', $task) }}" method="POST" class="mb-4">
                        @csrf
                        <div class="mb-2">
                            <textarea class="form-control" name="comment" rows="2" placeholder="Write an update, note, or comment..." required></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-sm px-4 rounded-pill">
                                <i class="fas fa-paper-plane me-1"></i> Post Comment
                            </button>
                        </div>
                    </form>

                    <div class="space-y-3">
                        @forelse($task->comments as $comment)
                        <div class="p-3 rounded-3 mb-2 border" style="background: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark"><i class="fas fa-user-circle text-muted me-1"></i> {{ $comment->user->name }}</span>
                                <span class="text-muted small">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="mb-0 text-dark small" style="white-space: pre-line;">{{ $comment->comment }}</p>
                        </div>
                        @empty
                        <p class="text-muted small text-center mb-0">No comments yet. Start the conversation!</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar / Details Column (Right Column) -->
        <div class="col-lg-4">
            <!-- Assignment & Constraints Card -->
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #e2e8f0 !important;">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-user-check text-primary me-2"></i>Employee Assignment</h6>
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Assigned Team Member</label>
                        <select class="form-select" onchange="quickAssignEmployee(this.value)">
                            <option value="">-- Unassigned --</option>
                            @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ $task->assigned_to == $emp->id ? 'selected' : '' }}>
                                {{ $emp->name }} ({{ $emp->email }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td class="text-muted"><strong>Created By:</strong></td>
                            <td class="text-end fw-semibold text-dark">{{ $task->creator ? $task->creator->name : 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><strong>Due Date:</strong></td>
                            <td class="text-end fw-semibold text-dark">{{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('d M Y') : 'No deadline' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted"><strong>Created On:</strong></td>
                            <td class="text-end text-muted">{{ $task->created_at ? \Carbon\Carbon::parse($task->created_at)->format('d M Y') : '-' }}</td>
                        </tr>
                        @if($task->completed_at)
                        <tr>
                            <td class="text-muted"><strong>Completed On:</strong></td>
                            <td class="text-end text-success fw-bold">{{ \Carbon\Carbon::parse($task->completed_at)->format('d M Y') }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Customer Details Profile Card -->
            @if($task->customer)
            <div class="card border-0 shadow-sm rounded-4 mb-4" style="border: 1px solid #bfdbfe !important; background: linear-gradient(180deg, #f0f7ff, #ffffff);">
                <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-primary"><i class="fas fa-address-card me-2"></i>Customer Profile & Data</h6>
                    <a href="{{ route('admin.customers.show', $task->customer) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-0" style="font-size: 0.75rem;">
                        View Full CRM
                    </a>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-3">
                        <div class="d-inline-block rounded-circle bg-primary text-white p-3 mb-2 shadow-sm" style="width: 55px; height: 55px;">
                            <i class="fas fa-user-tie fa-lg"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-0">{{ $task->customer->name }}</h6>
                        @if($task->customer->city)
                        <div class="text-muted small fw-medium">{{ $task->customer->city }}, {{ $task->customer->state ?? 'India' }}</div>
                        @endif
                    </div>

                    <div class="list-group list-group-flush rounded-3 small">
                        <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="text-muted"><i class="fas fa-phone me-1 text-primary"></i> Mobile:</span>
                            <span class="fw-semibold text-dark">{{ $task->customer->phone ?? 'N/A' }}</span>
                        </div>
                        @if($task->customer->email)
                        <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="text-muted"><i class="fas fa-envelope me-1 text-primary"></i> Email:</span>
                            <span class="fw-semibold text-dark text-truncate" style="max-width: 170px;">{{ $task->customer->email }}</span>
                        </div>
                        @endif
                        @if($task->customer->gstin)
                        <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="text-muted"><i class="fas fa-id-badge me-1 text-primary"></i> GSTIN:</span>
                            <span class="fw-bold font-monospace text-primary">{{ $task->customer->gstin }}</span>
                        </div>
                        @endif
                        @if($task->customer->address)
                        <div class="list-group-item bg-transparent px-0 py-2 border-bottom">
                            <div class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1 text-danger"></i> Address:</div>
                            <div class="text-dark small">{{ $task->customer->address }}</div>
                        </div>
                        @endif
                        @if($task->customer->society)
                        <div class="list-group-item bg-transparent px-0 py-2 d-flex justify-content-between align-items-center border-bottom">
                            <span class="text-muted"><i class="fas fa-city me-1 text-primary"></i> Society:</span>
                            <span class="fw-semibold text-dark">{{ $task->customer->society }}</span>
                        </div>
                        @endif
                    </div>

                    <!-- Quick WhatsApp & Call Buttons -->
                    @if($task->customer->phone)
                    <div class="d-grid gap-2 mt-3">
                        <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $task->customer->phone) }}?text=Hello%20{{ urlencode($task->customer->name) }},%20regarding%20task:%20{{ urlencode($task->title) }}" target="_blank" class="btn btn-success btn-sm rounded-pill">
                            <i class="fab fa-whatsapp me-1"></i> WhatsApp Customer
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card border-0 shadow-sm rounded-4 mb-4 text-center p-4 bg-light" style="border: 1px dashed #cbd5e1 !important;">
                <i class="fas fa-user-slash fa-2x text-muted mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">No Customer Linked</h6>
                <p class="text-muted small mb-3">Link a customer to this task to track customer profile, quotes, and GST bills.</p>
                <a href="{{ route('admin.tasks.edit', $task) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-link me-1"></i> Link Customer
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateTaskStatus(status) {
    fetch("{{ route('admin.tasks.update-status', $task) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function quickAssignEmployee(userId) {
    fetch("{{ route('admin.tasks.quick-assign', $task) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ assigned_to: userId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
        }
    });
}

function toggleChecklistItem(itemId, isCompleted) {
    fetch("{{ route('admin.tasks.toggle-checklist', $task) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ item_id: itemId, completed: isCompleted })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endpush
@endsection
