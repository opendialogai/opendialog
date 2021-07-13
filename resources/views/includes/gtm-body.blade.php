@if (env("ENABLE_GTM"))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ env("GTM_CONTAINER") }}&gtm_auth={{ env("GTM_AUTH") }}&gtm_preview={{ env("GTM_ENVIRONMENT") }}&gtm_cookies_win=x"
                      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
@endif
