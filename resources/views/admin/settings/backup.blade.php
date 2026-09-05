@extends('layouts.app')

@section('title', 'Backup & Restore')

@section('breadcrumb')
<li class="breadcrumb-item"><a href="{{ route('admin.settings.index') }}">Settings</a></li>
<li class="breadcrumb-item active">Backup & Restore</li>
@endsection

@section('content')
<div class="container-fluid">
    
    <div class="mb-4">
        <h2>Backup & Restore</h2>
        <p class="text-muted">Manage system database exports and restore configurations.</p>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Download Backup -->
        <div class="col-md-6 mb-4">
            <x-card>
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box navy me-3" style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-download fa-lg text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Download Database Backup</h5>
                        <small class="text-muted">Generate and download a standard SQL dump of all tables.</small>
                    </div>
                </div>
                <hr>
                <p>Downloading a backup will generate an SQL script containing all tables, schema definitions, and records. This file can be used to restore the system later.</p>
                <a href="{{ route('admin.settings.backup.download') }}" class="btn btn-primary">
                    <i class="fas fa-file-download"></i> Generate & Download SQL
                </a>
            </x-card>
        </div>

        <!-- Restore Backup -->
        <div class="col-md-6 mb-4">
            <x-card>
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-box red me-3" style="width:50px;height:50px;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-upload fa-lg text-white"></i>
                    </div>
                    <div>
                        <h5 class="mb-0">Restore Database</h5>
                        <small class="text-muted">Upload an SQL backup script to restore the database.</small>
                    </div>
                </div>
                <hr>
                <form action="{{ route('admin.settings.backup.restore') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="backup_file" class="form-label">Select SQL Backup File</label>
                        <input class="form-control" type="file" id="backup_file" name="backup_file" accept=".sql" required>
                    </div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('WARNING: This will overwrite your existing database records. Are you sure you want to proceed?')">
                        <i class="fas fa-file-upload"></i> Upload & Restore
                    </button>
                </form>
            </x-card>
        </div>
    </div>

</div>
@endsection
