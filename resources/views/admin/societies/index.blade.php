@extends('layouts.app')

@section('title', 'Societies Directory')

@section('breadcrumb')
<li class="breadcrumb-item active">Societies</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Societies Master List</h2>
        <a href="{{ route('admin.societies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Society
        </a>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Societies Table -->
        <div class="col-md-8">
            <x-card title="Societies List">
        <table id="societiesTable" class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Society Name</th>
                    <th>Default Contractor</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($societies as $society)
                <tr>
                    <td>{{ $society->id }}</td>
                    <td><strong>{{ $society->name }}</strong></td>
                    <td>
                        @if($society->contractor)
                            <span class="badge bg-info text-dark"><i class="fas fa-hard-hat me-1"></i>{{ $society->contractor->name }}</span>
                        @else
                            <span class="text-muted small">Not Assigned</span>
                        @endif
                    </td>
                    <td>{{ $society->created_at ? $society->created_at->format('d M Y') : 'N/A' }}</td>
                    <td>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.societies.edit', $society) }}" class="btn btn-sm btn-warning" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.societies.destroy', $society) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this society?');" class="m-0 p-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
            </x-card>
        </div>
        
        <!-- Side Panel: Customers per Society -->
        <div class="col-md-4">
            <x-card title="Customers per Society">
                @if(count($customerCounts) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($customerCounts->sortByDesc('count') as $stat)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 society-list-item" data-society="{{ $stat->society }}" style="cursor: pointer;" title="Click to view customers">
                                <span class="fw-medium text-dark"><i class="fas fa-building text-primary me-2"></i> {{ $stat->society }}</span>
                                <span class="badge bg-primary rounded-pill" style="font-size: 0.9rem;">{{ $stat->count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center text-muted p-4">
                        <i class="fas fa-info-circle fs-4 mb-2"></i>
                        <p class="mb-0">No customer data linked to societies yet.</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>

</div>

<!-- Customers Modal -->
<div class="modal fade" id="customersModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold" id="customersModalTitle">Customers in Society</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
          <div class="table-responsive">
              <table class="table table-hover mb-0" id="societyCustomersTable">
                  <thead class="table-light">
                      <tr>
                          <th>Name</th>
                          <th>CRN No</th>
                          <th>Phone</th>
                          <th>Stage</th>
                          <th>Action</th>
                      </tr>
                  </thead>
                  <tbody id="customersTableBody">
                      <!-- Loaded via AJAX -->
                  </tbody>
              </table>
          </div>
          <div id="loadingCustomers" class="text-center p-4 d-none">
              <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
              </div>
          </div>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#societiesTable').DataTable({
        order: [[1, 'asc']]
    });

    $('.society-list-item').on('click', function() {
        const society = $(this).data('society');
        $('#customersModalTitle').text('Customers in ' + society);
        $('#customersTableBody').empty();
        $('#loadingCustomers').removeClass('d-none');
        $('#societyCustomersTable').addClass('d-none');
        
        const modal = new bootstrap.Modal(document.getElementById('customersModal'));
        modal.show();
        
        $.ajax({
            url: '{{ route("admin.societies.customers") }}',
            type: 'GET',
            data: { society: society },
            success: function(response) {
                $('#loadingCustomers').addClass('d-none');
                $('#societyCustomersTable').removeClass('d-none');
                
                if (response.length === 0) {
                    $('#customersTableBody').html('<tr><td colspan="5" class="text-center text-muted">No customers found</td></tr>');
                    return;
                }
                
                let rows = '';
                response.forEach(function(customer) {
                    const badgeClass = getBadgeClass(customer.customer_stage);
                    const viewUrl = '{{ url("admin/customers") }}/' + customer.id;
                    rows += `
                        <tr>
                            <td class="fw-medium">${customer.name}</td>
                            <td>${customer.crn_no || '-'}</td>
                            <td>${customer.phone || '-'}</td>
                            <td><span class="badge bg-${badgeClass}">${customer.customer_stage || 'New'}</span></td>
                            <td>
                                <a href="${viewUrl}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> View
                                </a>
                            </td>
                        </tr>
                    `;
                });
                $('#customersTableBody').html(rows);
            },
            error: function() {
                $('#loadingCustomers').addClass('d-none');
                $('#customersTableBody').html('<tr><td colspan="5" class="text-center text-danger">Error loading customers</td></tr>');
                $('#societyCustomersTable').removeClass('d-none');
            }
        });
    });

    function getBadgeClass(stage) {
        switch(stage) {
            case 'Converted': return 'success';
            case 'JMR Done': return 'info';
            case 'RFC Done': return 'primary';
            case 'LMC Done': return 'warning';
            case 'Registered': return 'secondary';
            default: return 'light text-dark';
        }
    }
});
</script>
@endpush
