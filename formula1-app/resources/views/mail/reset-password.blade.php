{{-- filepath: c:\Users\Usuario\git\ProyectoIntegradoDAW\formula1-app\resources\views\mail\reset-password.blade.php --}}
@component('mail::layout')

{{-- Header --}}
@slot('header')
@component('mail::header', ['url' => config('app.url')])
<div style="text-align: center; margin-bottom: 10px;">
    <img src="{{ asset('img/logo-f1.png') }}" class="logo" alt="F1 Stats Logo" style="height: 100px !important; max-height: 100px;">
</div>
@endcomponent
@endslot

{{-- Body --}}
<div style="text-align: center; margin-bottom: 25px;">
    <h1 style="color: #E10600; margin-bottom: 5px; font-size: 24px;">Password Reset Request</h1>
    <p style="color: #666; font-size: 16px; margin-top: 0;">Hello, Racer!</p>
</div>

<p>You are receiving this email because we received a password reset request for your Formula 1 Stats account.</p>

@component('mail::button', ['url' => $url, 'color' => 'red'])
Reset Password
@endcomponent

<p style="color: #666; font-size: 14px; margin-top: 30px;">
    This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.
</p>

<p style="color: #666; font-size: 14px;">
    If you did not request a password reset, no further action is required.
</p>

<p>Thanks,<br>The F1 Stats Team</p>

{{-- Subcopy --}}
@slot('subcopy')
@component('mail::subcopy')
If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser: {{ $url }}
@endcomponent
@endslot

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
<div style="text-align: center;">
    <div style="background-color: #E10600; height: 3px; width: 60px; margin: 0 auto;"></div>
    <p style="margin-top: 15px;">© {{ date('Y') }} Formula 1 Stats. All rights reserved.</p>
</div>
@endcomponent
@endslot

@endcomponent