@extends('emails.layout')

@section('content')
    <div class="badge">Infrastructure Alert</div>
    <h2>Server Status Change</h2>
    <p>A manual or automated status change has been detected for a resource in your workspace <span class="highlight">{{ $workspaceName }}</span>.</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Resource Identifier</th>
                <th>Operation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="type-mono"><strong>{{ $resourceId }}</strong></td>
                <td>
                    <span style="font-weight: 700; text-transform: uppercase;">{{ $action }}</span>
                </td>
            </tr>
        </tbody>
    </table>
    
    <p style="margin-top: 32px; font-size: 13px; color: #64748b;">
        This event was registered in your system activity logs. If you did not initiate this change, we recommend reviewing your access tokens and IAM policies.
    </p>
@endsection
