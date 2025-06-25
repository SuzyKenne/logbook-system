<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Logbook Progress Report</title>
    <style>
        /* Step 7: Professional PDF styling */
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #6b7280;
        }

        .section {
            margin-bottom: 25px;
        }

        .section h2 {
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
            margin-bottom: 15px;
            font-size: 16px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 40%;
            padding: 8px 10px 8px 0;
            vertical-align: top;
        }

        .info-value {
            display: table-cell;
            padding: 8px 0;
            vertical-align: top;
        }

        .progress-bar {
            width: 200px;
            height: 20px;
            background-color: #f3f4f6;
            border-radius: 10px;
            overflow: hidden;
            display: inline-block;
        }

        .progress-fill {
            height: 100%;
            background-color: #10b981;
            border-radius: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }

        .status-active {
            background-color: #10b981;
        }

        .status-draft {
            background-color: #6b7280;
        }

        .status-completed {
            background-color: #3b82f6;
        }

        .status-cancelled {
            background-color: #ef4444;
        }

        .status-on_hold {
            background-color: #f59e0b;
        }

        .entries-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .entries-table th,
        .entries-table td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left;
        }

        .entries-table th {
            background-color: #f9fafb;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }

        .highlight-box {
            background-color: #f0f9ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    <!-- Step 8: Report Header -->
    <div class="header">
        {{-- <h1>{{ $logbook->session_title }}</h1> --}}
        <p><strong>Course:</strong> {{ $logbook->course_code }} - {{ $logbook->course_name }}</p>
        <p><strong>Report Generated:</strong> {{ \Carbon\Carbon::parse($report_date)->format('F d, Y \a\t g:i A') }}</p>
    </div>

    <!-- Step 9: Basic Information Section -->
    <div class="section">
        <h2>Logbook Overview</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-{{ $logbook->status }}">
                        {{ ucwords(str_replace('_', ' ', $logbook->status)) }}
                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Department:</div>
                <div class="info-value">{{ $logbook->department->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Academic Level:</div>
                <div class="info-value">{{ $logbook->level->name ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Logbook Type:</div>
                <div class="info-value">{{ ucwords(str_replace('_', ' ', $logbook->logbook_type)) }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Duration:</div>
                <div class="info-value">
                    @if ($logbook->start_date && $logbook->end_date)
                        {{ \Carbon\Carbon::parse($logbook->start_date)->format('M d, Y') }} -
                        {{ \Carbon\Carbon::parse($logbook->end_date)->format('M d, Y') }}
                        ({{ $duration_days }} days)
                    @else
                        Not specified
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Step 10: Progress Statistics -->
    <div class="section">
        <h2>Progress Statistics</h2>

        <div class="highlight-box">
            <strong>Overall Progress: {{ $completion_percentage }}%</strong><br>
            <div class="progress-bar" style="margin-top: 10px;">
                <div class="progress-fill" style="width: {{ $completion_percentage }}%;"></div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Total Sessions Planned:</div>
                <div class="info-value">{{ $logbook->total_sessions }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sessions Completed:</div>
                <div class="info-value">{{ $total_entries }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Sessions Remaining:</div>
                <div class="info-value">{{ $remaining_sessions }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Average Entries per Week:</div>
                <div class="info-value">{{ $weekly_progress['average'] }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Entries This Week:</div>
                <div class="info-value">{{ $weekly_progress['this_week'] }}</div>
            </div>
        </div>
    </div>

    <!-- Step 11: Recent Entries -->
    @if ($recent_entries->count() > 0)
        <div class="section">
            <h2>Recent Entries (Last 5)</h2>
            <table class="entries-table">
                <thead>
                    <tr>
                        <th>Entry Title</th>
                        <th>Session Date</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($recent_entries as $entry)
                        <tr>
                            {{-- <td>{{ $entry->session_title }}</td> --}}
                            <td>{{ $entry['entry_date'] ? \Carbon\Carbon::parse($entry['entry_date'])->format('M d, Y') : 'N/A' }}
                            </td>
                            <td>{{ \Carbon\Carbon::parse($entry['created_at'])->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Step 12: Summary and Recommendations -->
    <div class="section">
        <h2>Summary & Recommendations</h2>
        <div class="highlight-box">
            @if ($completion_percentage >= 90)
                <strong>Excellent Progress!</strong> You're almost done with this logbook. Keep up the great work!
            @elseif($completion_percentage >= 70)
                <strong>Good Progress!</strong> You're in the final stretch. Stay consistent with your entries.
            @elseif($completion_percentage >= 50)
                <strong>Steady Progress!</strong> You're halfway there. Maintain your current pace.
            @elseif($completion_percentage >= 25)
                <strong>Getting Started!</strong> You've made a good start. Try to increase your entry frequency.
            @else
                <strong>Time to Focus!</strong> Consider setting a regular schedule for completing your logbook entries.
            @endif
        </div>

        @if ($remaining_sessions > 0)
            <p><strong>Next Steps:</strong> You have {{ $remaining_sessions }} sessions remaining.
                @if ($logbook->end_date)
                    With {{ now()->diffInDays($logbook->end_date) }} days until your end date,
                    aim for {{ ceil($remaining_sessions / max(1, now()->diffInWeeks($logbook->end_date))) }} entries
                    per week.
                @endif
            </p>
        @endif
    </div>

    <!-- Step 13: Footer -->
    <div class="footer">
        <p>This report was automatically generated on
            {{ \Carbon\Carbon::parse($report_date)->format('F d, Y \a\t g:i A') }}</p>
        <p>Created by: {{ $logbook->creator->name ?? 'System' }}</p>
    </div>
</body>

</html>
