{{-- filepath: resources/views/emails/guest_otp.blade.php --}}
@component('mail::message')
Hello,

You are receiving this e-mail following a request to verify your address.

Your email verification code is :

<span style="font-size: 28px; font-weight: bold; text-align: center;">
{{ $otp_code }}
</span>
<br><br>
If you did not request this, you can ignore this message. No action will be taken without your confirmation.

{{ $bestRegards }}
@endcomponent