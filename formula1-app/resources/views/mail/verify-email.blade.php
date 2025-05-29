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
    <h1 style="color: #E10600; margin-bottom: 5px; font-size: 24px;">Welcome to Formula 1 Stats!</h1>
    <p style="color: #666; font-size: 16px; margin-top: 0;">{{ $userName ?? 'Racer' }}</p>
</div>

<p>Thank you for joining our Formula 1 community! We're excited to have you on board.</p>

<p>Please verify your email address to unlock all features of the app:</p>

@component('mail::button', ['url' => $url, 'color' => 'red'])
Verify Email Address
@endcomponent

<div style="margin: 30px 0; padding: 20px; background-color: #f9f9f9; border-radius: 8px; border-left: 4px solid #e10600;">
    <h3 style="margin-top: 0; color: #333; font-size: 18px;">What you'll unlock:</h3>
    <ul style="padding-left: 20px; margin-bottom: 0;">
        <li style="margin-bottom: 8px;">📊 <strong>Predictions</strong> for upcoming races</li>
        <li style="margin-bottom: 8px;">📈 <strong>Exclusive statistics</strong> and insights</li>
        <li style="margin-bottom: 8px;">🏎️ <strong>Driver and team</strong> comparisons</li>
        <li style="margin-bottom: 0;">🔔 <strong>Personalized</strong> race notifications</li>
    </ul>
</div>

@if(isset($nextRace))
<div style="background-color: #1E1E1E; color: white; padding: 15px; border-radius: 8px; margin: 25px 0; text-align: center;">
    <h4 style="margin-top: 0; color: #E10600; margin-bottom: 10px;">Next Race Coming Up!</h4>
    <p style="margin-bottom: 0;">{{ $nextRace }}</p>
</div>
@endif

<p>Get ready to experience the thrill of Formula 1 like never before!</p>

<p style="color: #666; font-size: 14px; margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee;">If you did not create an account, no further action is required.</p>

<p>Thanks,<br>
The F1 Stats Team</p>

{{-- Subcopy --}}
@isset($subcopy)
@slot('subcopy')
@component('mail::subcopy')
{{ $subcopy }}
@endcomponent
@endslot
@endisset

{{-- Footer --}}
@slot('footer')
@component('mail::footer')
<div style="text-align: center;">
    <div style="margin-bottom: 10px;">
        <a href="{{ $appUrl ?? config('app.url') }}/dashboard" style="text-decoration: none; margin: 0 10px; color: #718096;">Dashboard</a>
        <a href="{{ $appUrl ?? config('app.url') }}/calendar" style="text-decoration: none; margin: 0 10px; color: #718096;">Race Calendar</a>
        <a href="{{ $appUrl ?? config('app.url') }}/contact" style="text-decoration: none; margin: 0 10px; color: #718096;">Contact Us</a>
    </div>
    <div style="margin-top: 15px; display: flex; justify-content: center;">
        <div style="background-color: #E10600; height: 3px; width: 60px; margin: 0 auto;"></div>
    </div>
    <p style="margin-top: 15px;">© {{ date('Y') }} Formula 1 Stats. All rights reserved.</p>
</div>
@endcomponent
@endslot
@endcomponent