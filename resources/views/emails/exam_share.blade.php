@component('mail::message')
# Exam Result Shared

**Student:** {{ $studentName }}
**Exam:** {{ $examTitle }}
**Score:** {{ $score }} / {{ $totalMarks }}

The student has shared their detailed report card with you. Click the button below to view the full analysis, solutions, and answers.

@component('mail::button', ['url' => $link])
View Full Report Card
@endcomponent

Thanks,<br>
{{ $settings->site_name ?? config('app.name') }}
@endcomponent
