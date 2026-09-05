@extends('layouts.app')
@section('title', 'Add Supplier')
@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.suppliers.index') }}">Suppliers</a></li>
<li class="breadcrumb-item active">Add</li>
@endsection
@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <x-card>
            <h5 class="mb-4">Add Supplier</h5>
            <form action="{{ route('admin.suppliers.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Supplier Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Supplier Pvt Ltd" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contact Person</label>
                    <input type="text" name="contact_person" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone 1</label>
                    <input type="text" name="phone" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone 2</label>
                    <input type="text" name="phone_2" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" checked>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </form>
        </x-card>
    </div>
</div>
@endsection
