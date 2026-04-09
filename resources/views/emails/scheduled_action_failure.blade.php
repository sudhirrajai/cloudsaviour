@extends('emails.layout')

@section('content')
    <div class="badge" style="border-color: #0f172a; color: #0f172a;">System Failure</div>
    <h2>Automation Execution Error</h2>
    <p>A high-priority scheduled action failed to complete for workspace <span class="highlight">{{ $workspaceName }}</span>.</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Target Automation</th>
                <th>System Response</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="type-mono"><strong>{{ $scheduleName }}</strong></td>
                <td style="color: #0f172a; font-family: monospace; font-size: 11px; padding: 12px; border: 1px solid #0f172a; background: #f8fafc;">
                    {{ $errorMessage }}
                </td>
            </tr>
        </tbody>
    </table>
    
    <p style="margin-top: 32px; font-size: 13px; color: #64748b;">
        This failure may prevent cost-saving measures from executing. We recommend verifying your AWS bridge connection and IAM permissions immediately.
    </p>
@endsection
