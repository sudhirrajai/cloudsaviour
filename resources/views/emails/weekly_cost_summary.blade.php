@extends('emails.layout')

@section('content')
    <div class="badge">Financial Intelligence</div>
    <h2>AWS Expenditure Report</h2>
    <p>Infrastructure cost analysis for workspace <span class="highlight">{{ $workspaceName }}</span> (Last 7 Days).</p>
    
    <div style="background: #0f172a; padding: 40px; text-align: left; margin: 32px 0;">
        <div style="color: #94a3b8; font-size: 10px; text-transform: uppercase; font-weight: 800; letter-spacing: 0.2em; margin-bottom: 8px;">Total System Spend</div>
        <div style="color: #ffffff; font-size: 42px; font-weight: 800; letter-spacing: -0.05em;">${{ number_format($totalSpent, 2) }}</div>
        <div style="margin-top: 16px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: {{ $trend > 0 ? '#ff4d4d' : '#00ffcc' }}; font-weight: 800;">
            {{ $trend > 0 ? '▲' : '▼' }} {{ abs($trend) }}% TREND VS LAST CYCLE
        </div>
    </div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Infrastructure Component</th>
                <th style="text-align: right;">Allocated Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($breakdown as $service => $amount)
            <tr>
                <td class="type-mono" style="font-weight: 600;">{{ strtoupper($service) }}</td>
                <td style="text-align: right; font-weight: 700;">${{ number_format($amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p style="margin-top: 32px; font-size: 13px; color: #64748b;">
        This summary is generated from CloudWatch and Cost Explorer data. For a granular daily breakdown, please access the platform analytics.
    </p>
@endsection
