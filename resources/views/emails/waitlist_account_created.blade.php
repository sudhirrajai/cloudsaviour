@extends('emails.layout')

@section('content')
    <div class="badge">Access Granted</div>
    <h2>Operational Readiness</h2>
    <p>The wait is over. Your high-priority access to the CloudSaviour ecosystem has been authorized and your workspace is now operational.</p>
    
    <div style="background: #0f172a; padding: 32px; margin: 32px 0; border: 1px solid #0f172a;">
        <div style="margin-bottom: 24px;">
            <div style="color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; font-size: 10px; font-weight: 800; margin-bottom: 8px;">System Identity (Email)</div>
            <div style="font-size: 16px; color: #ffffff; font-weight: 600;">{{ $email }}</div>
        </div>
        <div>
            <div style="color: #94a3b8; text-transform: uppercase; letter-spacing: 2px; font-size: 10px; font-weight: 800; margin-bottom: 8px;">Temporary Access Token (Password)</div>
            <div style="font-size: 16px; color: #ffffff; font-weight: 700; font-family: monospace; letter-spacing: 2px;">{{ $password }}</div>
        </div>
    </div>

    <p style="font-size: 13px; color: #64748b; font-style: italic;">
        Security Protocol: You are required to rotate this temporary credential immediately upon system entry.
    </p>
@endsection
