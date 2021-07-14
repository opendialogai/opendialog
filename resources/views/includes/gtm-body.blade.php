@if (config('gtm.enable'))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('gtm.container') }}&gtm_auth={{ config('gtm.auth') }}&gtm_preview={{ config('gtm.environment') }}&gtm_cookies_win=x"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif
