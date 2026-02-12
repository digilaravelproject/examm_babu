@component('mail::message')
# Welcome, {{ $user->first_name }}!

Your account has been successfully created on **{{ $school_name }}**.

Here are your login credentials:

@component('mail::panel')
**Role:** {{ $role }}
**Email:** {{ $user->email }}
**Password:** {{ $password }}
@endcomponent

Please keep your password safe. You can change it after logging in.

@component('mail::button', ['url' => $login_url])
Login to Dashboard
@endcomponent

Thanks,<br>
{{ $school_name }} Team
@endcomponent
