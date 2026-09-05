@extends('layouts.admin')

@section('title', 'Create Journal Entry')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Create Journal Entry</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.accounts.journal.store') }}" method="POST" id="journalForm">
            @csrf
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="entry_no" class="form-label">Entry No</label>
                    <input type="text" class="form-control" value="{{ $entryNo }}" readonly>
                </div>
                <div class="col-md-6">
                    <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('date') is-invalid @enderror" 
                           id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                    @error('date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="narration" class="form-label">Narration <span class="text-danger">*</span></label>
                <textarea class="form-control @error('narration') is-invalid @enderror" 
                          id="narration" name="narration" rows="2" required>{{ old('narration') }}</textarea>
                @error('narration')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <h6 class="mb-3">Entry Lines</h6>
            <div id="entryLines">
                <div class="row entry-line mb-2">
                    <div class="col-md-5">
                        <select class="form-select" name="lines[0][account_id]" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control debit-input" name="lines[0][debit]" placeholder="Debit" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control credit-input" name="lines[0][credit]" placeholder="Credit" value="0" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-line" disabled>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="row entry-line mb-2">
                    <div class="col-md-5">
                        <select class="form-select" name="lines[1][account_id]" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->account_code }} - {{ $account->account_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control debit-input" name="lines[1][debit]" placeholder="Debit" value="0" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" step="0.01" class="form-control credit-input" name="lines[1][credit]" placeholder="Credit" value="0" required>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger btn-sm remove-line">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="addLine">
                <i class="fas fa-plus"></i> Add Line
            </button>

            <div class="row mb-3">
                <div class="col-md-5"></div>
                <div class="col-md-3">
                    <strong>Total Debit: <span id="totalDebit">0.00</span></strong>
                </div>
                <div class="col-md-3">
                    <strong>Total Credit: <span id="totalCredit">0.00</span></strong>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Total debits must equal total credits
            </div>

            @error('lines')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Entry
                </button>
                <a href="{{ route('admin.accounts.journal.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let lineIndex = 2;

function updateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;
    
    document.querySelectorAll('.debit-input').forEach(input => {
        totalDebit += parseFloat(input.value) || 0;
    });
    
    document.querySelectorAll('.credit-input').forEach(input => {
        totalCredit += parseFloat(input.value) || 0;
    });
    
    document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
    document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);
}

document.getElementById('addLine').addEventListener('click', function() {
    const accountOptions = @json($accounts->map(fn($a) => ['id' => $a->id, 'text' => $a->account_code . ' - ' . $a->account_name]));
    const optionsHtml = '<option value="">Select Account</option>' + 
        accountOptions.map(opt => `<option value="${opt.id}">${opt.text}</option>`).join('');
    
    const newLine = document.createElement('div');
    newLine.className = 'row entry-line mb-2';
    newLine.innerHTML = `
        <div class="col-md-5">
            <select class="form-select" name="lines[${lineIndex}][account_id]" required>${optionsHtml}</select>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" class="form-control debit-input" name="lines[${lineIndex}][debit]" placeholder="Debit" value="0" required>
        </div>
        <div class="col-md-3">
            <input type="number" step="0.01" class="form-control credit-input" name="lines[${lineIndex}][credit]" placeholder="Credit" value="0" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm remove-line">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.getElementById('entryLines').appendChild(newLine);
    lineIndex++;
    
    newLine.querySelectorAll('.debit-input, .credit-input').forEach(input => {
        input.addEventListener('input', updateTotals);
    });
});

document.getElementById('entryLines').addEventListener('click', function(e) {
    if (e.target.closest('.remove-line')) {
        const line = e.target.closest('.entry-line');
        if (document.querySelectorAll('.entry-line').length > 2) {
            line.remove();
            updateTotals();
        }
    }
});

document.querySelectorAll('.debit-input, .credit-input').forEach(input => {
    input.addEventListener('input', updateTotals);
});

updateTotals();
</script>
@endpush
@endsection
