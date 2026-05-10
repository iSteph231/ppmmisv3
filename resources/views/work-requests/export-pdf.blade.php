<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Work Request - {{ $request->request_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #16a34a;
        }
        
        .header h1 {
            color: #16a34a;
            margin: 0;
            font-size: 24px;
        }
        
        .header p {
            color: #6b7280;
            margin: 5px 0 0;
        }
        
        .info-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            background: #f1f5f9;
            padding: 10px 15px;
            border-bottom: 1px solid #e5e7eb;
            font-weight: bold;
            color: #1e293b;
        }
        
        .card-body {
            padding: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 13px;
            color: #1e293b;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #d97706;
        }
        
        .badge-approved {
            background: #dbeafe;
            color: #2563eb;
        }
        
        .badge-completed {
            background: #d1fae5;
            color: #059669;
        }
        
        .description-box {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #16a34a;
            margin: 15px 0;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PPMMIS</h1>
        <p>Physical Plant Maintenance and Management Information System</p>
        <h3>Work Request Details</h3>
        <p>Generated on: {{ date('F d, Y h:i A') }}</p>
    </div>

    <div class="info-card">
        <div class="card-header">REQUEST INFORMATION</div>
        <div class="card-body">
            <div class="info-grid">
                <div>
                    <div class="info-label">Request Number</div>
                    <div class="info-value">{{ $request->request_number ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="badge badge-{{ $request->status }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <div class="info-label">Title</div>
                    <div class="info-value">{{ $request->title }}</div>
                </div>
                <div>
                    <div class="info-label">Requested By</div>
                    <div class="info-value">{{ $request->user->name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label">Department</div>
                    <div class="info-value">{{ $request->user->department ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="info-label">Date Requested</div>
                    <div class="info-value">{{ $request->created_at->format('F d, Y h:i A') }}</div>
                </div>
                @if($workRequest->location)
                <div>
                    <div class="info-label">Location</div>
                    <div class="info-value">{{ $request->location }}</div>
                </div>
                @endif
                @if($workRequest->completed_at)
                <div>
                    <div class="info-label">Date Completed</div>
                    <div class="info-value">{{ $request->completed_at->format('F d, Y h:i A') }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="info-card">
        <div class="card-header">DESCRIPTION</div>
        <div class="card-body">
            <div class="description-box">
                <p>{{ $workRequest->description }}</p>
            </div>
        </div>
    </div>

    @if($workRequest->admin_notes)
    <div class="info-card">
        <div class="card-header">ADMINISTRATOR NOTES</div>
        <div class="card-body">
            <div class="description-box" style="background: #fef3c7; border-left-color: #f59e0b;">
                <p>{{ $workRequest->admin_notes }}</p>
            </div>
        </div>
    </div>
    @endif

    <div class="footer">
        <p>This document is generated automatically by PPMMIS.</p>
        <p>© {{ date('Y') }} Physical Plant Maintenance and Management Information System</p>
    </div>
</body>
</html>