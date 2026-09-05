<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied | HisabMittra ERP</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #084298 0%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: #fff;
            border-radius: 16px;
            padding: 50px 40px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .error-icon {
            width: 90px;
            height: 90px;
            background: #ED1C24;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
        }
        .error-icon i { font-size: 40px; color: #fff; }
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: #084298;
            line-height: 1;
            margin-bottom: 5px;
        }
        .error-title {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        .error-msg {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .btn-home {
            background: #ED1C24;
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: background 0.2s;
        }
        .btn-home:hover { background: #c91119; color: #fff; }
        .btn-back {
            background: #084298;
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            transition: background 0.2s;
        }
        .btn-back:hover { background: #063180; color: #fff; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">
            <i class="fas fa-ban"></i>
        </div>
        <div class="error-code">403</div>
        <div class="error-title">Access Denied</div>
        <p class="error-msg">
            You don't have permission to access this page.<br>
            Please contact your administrator if you believe this is a mistake.
        </p>
        <div>
            <a href="javascript:history.back()" class="btn-back">
                <i class="fas fa-arrow-left me-1"></i> Go Back
            </a>
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="btn-home">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                @else
                    <a href="{{ route('employee.dashboard') }}" class="btn-home">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                @endif
            @else
                <a href="{{ route('login') }}" class="btn-home">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </a>
            @endauth
        </div>
        <div class="mt-4">
            <small class="text-muted">HisabMittra ERP+CRM &copy; {{ date('Y') }}</small>
        </div>
    </div>
</body>
</html>
