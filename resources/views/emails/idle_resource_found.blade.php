@extends('emails.layout')

@section('content')
    <div class="badge">Efficiency Alert</div>
    <h2>Zombie Resources Detected</h2>
    <p>Our intelligent scanners have identified unutilized infrastructure in your account for workspace <span class="highlight">{{ $workspaceName }}</span>.</p>
    
    <table class="data-table">
        <thead>
            <tr>
                <th>Resource Detail</th>
                <th style="text-align: right;">Projected Monthly Leak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resources as $resource)
            <tr>
                <td class="type-mono">
                    <strong>{{ strtoupper($resource['type']) }}</strong><br>
                    <span style="font-size: 11px; color: #64748b;">{{ $resource['id'] }}</span>
                </td>
                <td style="text-align: right; font-weight: 700;">
                    ${{ number_format($resource['cost'], 2) }}
                </td>
            </tr>
            @endforeach
            <tr style="border-top: 2px solid #0f172a;">
                <td style="padding-top: 20px; font-weight: 800; text-transform: uppercase; font-size: 11px;">Total Potential Savings</td>
                <td style="padding-top: 20px; text-align: right; font-size: 18px; font-weight: 800;">
                    ${{ number_format($totalSavings, 2) }}
                </td>
            </tr>
        </tbody>
    </table>
    
    <p style="margin-top: 32px; font-size: 13px; color: #64748b;">
        Terminating these resources now will immediately halt further cost accumulation for this billing cycle.
    </p>
@endsection
