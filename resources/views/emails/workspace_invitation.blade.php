@extends('emails.layout')

@section('content')
    <div class="badge">WORKSPACE INVITATION</div>
    <h2>Join the Team</h2>
    
    <p>Hello,</p>
    
    <p><span class="highlight">{{ $inviterName }}</span> has invited you to join the <span class="highlight">{{ $workspaceName }}</span> workspace on CloudSaviour as a <span class="highlight text-uppercase">{{ strtoupper($role) }}</span>.</p>
    
    <p>CloudSaviour helps teams manage AWS resources, optimize costs, and automate infrastructure schedules with intelligence.</p>

    <div style="margin-top: 32px; padding: 24px; background-color: #f8fafc; border: 1px dashed #0f172a;">
        <p style="margin: 0; font-size: 13px; color: #0f172a;">
            <strong>Workspace:</strong> {{ $workspaceName }}<br>
            <strong>Role assigned:</strong> {{ strtoupper($role) }}<br>
            <strong>Invited by:</strong> {{ $inviterName }}
        </p>
    </div>

    <p style="margin-top: 32px;">This invitation link will expire in <span class="highlight">7 days</span>.</p>

    <div style="margin-top: 10px;">
        <a href="{{ $acceptUrl }}" class="button">Accept Invitation</a>
    </div>
    
    <p style="margin-top: 32px; font-size: 12px; color: #64748b;">
        If you don't have an account, clicking the button above will guide you through the registration process to join the workspace.
    </p>
@endsection
