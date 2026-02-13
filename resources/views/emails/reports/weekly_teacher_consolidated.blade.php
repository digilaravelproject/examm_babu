<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-top: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: white; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .btn { display: inline-block; padding: 6px 12px; background: #3498db; color: white; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Weekly Student Performance Report</h2>
            <p>Here is the consolidated report for the past week.</p>
        </div>

        <div class="content">
            <p>Hello,</p>
            <p>The following students have shared their exam results with you this week:</p>

            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Exam Name</th>
                        <th>Score</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentReports as $report)
                    <tr>
                        <td>
                            <strong>{{ $report->student_name }}</strong><br>
                            <span style="font-size: 12px; color: #666;">{{ $report->student_email }}</span>
                        </td>
                        <td>{{ $report->exam_name }}</td>
                        <td>
                            <strong>{{ $report->score }}</strong> / {{ $report->total_marks }}
                        </td>
                        <td>
                            <a href="{{ $report->result_url }}" class="btn" target="_blank">View Result</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <p style="margin-top: 20px;">
                <em>Note: The "View Result" links are valid for 30 days.</em>
            </p>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
