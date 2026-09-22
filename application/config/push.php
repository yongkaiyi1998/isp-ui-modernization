<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

$config['push'] = array(
	/*
	|----------------------------------------------------------------------
	| Master switch
	|----------------------------------------------------------------------
    */
	'push_enabled'                   => FALSE,
	/*
	|----------------------------------------------------------------------
	| Firebase credentials
	|----------------------------------------------------------------------
	| project_id           : the Firebase PROJECT ID
	| service_account_path : absolute path to the service account JSON.
	|                        MUST be outside the webroot. MUST be chmod 0600 (or equivalent ACL on Windows)
	|                        and owned by the PHP process user. MUST NOT be committed to source control.
	| Dev (current)        : C:/wamp64/private/firebase-service-account.json
	| Staging/Prod         : path outside the document root
	*/
	'project_id'                => 'ifibre-inf',
	'service_account_path'      => 'CHANGE_ME',
	/*
	|----------------------------------------------------------------------
	| Google endpoints (FCM HTTP v1)
	|----------------------------------------------------------------------
	*/
	'endpoint'                  => 'https://fcm.googleapis.com/v1/projects/%s/messages:send',
	'oauth_token_url'           => 'https://oauth2.googleapis.com/token',
	'oauth_scope'               => 'https://www.googleapis.com/auth/firebase.messaging',
	/*
	|----------------------------------------------------------------------
	| OAuth access token cache
	|----------------------------------------------------------------------
	| Google access tokens live 3600s. We cache for 3300s to leave headroom.
	| This is what keeps a 500-customer cron run to ONE token exchange
	| instead of 500. Do not disable it.
	*/
	'access_token_cache_key'    => 'fcm_access_token',
	'access_token_ttl'          => 3300,
	/*
	|----------------------------------------------------------------------
	| HTTP behaviour
	|----------------------------------------------------------------------
	| retry_backoff_ms is indexed by attempt number (0-based) and only
	| applies to RETRYABLE failures (429, 5xx, curl timeout). Permanent
	| failures (404 UNREGISTERED, 400 INVALID_ARGUMENT) never retry.
	*/
	'connect_timeout'           => 5,
	'timeout'                   => 10,
	'max_attempts'              => 3,
	'retry_backoff_ms'          => array(500, 1000, 2000),
	/*
	|----------------------------------------------------------------------
	| TLS
	|----------------------------------------------------------------------
	| verify_ssl MUST stay TRUE in staging and production.
	| ca_bundle_path is the local-dev escape hatch for WAMP's missing CA
	| bundle - set it to a cacert.pem path. Leave empty on the server.
	*/
	'verify_ssl'                => TRUE,
	'ca_bundle_path'            => '',
	/*
	|----------------------------------------------------------------------
	| Language
	|----------------------------------------------------------------------
	| lang_map translates the locale the Flutter app reports into the
	| CodeIgniter language folder name.
	|
	| The app sends "<languageCode>_<countryCode>" from LanguageController,
	| built from AppConstants.languages: en_US, ms_MY, zh_CN. The bare
	| language code is also mapped so a device reporting only "ms" (or a
	| future ms_SG) still resolves.
	|
	| Adding a language:
	|   1. add the entry here
	|   2. create application/language/<folder>/push_lang.php
	|      (the folder name must match this application's existing language folders exactly)
	|
	| An unmapped locale falls back to default_lang rather than failing.
	*/
	'lang_map'                  => array(
		'en_US' => 'english',
		'en_GB' => 'english',
		'en'    => 'english',
		'ms_MY' => 'malay',
		'ms'    => 'malay',
		'zh_CN' => 'chinese_simplified',
		'zh'    => 'chinese_simplified',
	),
	'default_lang'              => 'english',
	/*
	|----------------------------------------------------------------------
	| Content limits
	|----------------------------------------------------------------------
	| Enforced server-side on BOTH template output and admin free-text.
	| Android collapses notification body around ~240 chars on most
	| launchers; iOS shows ~4 lines. 200 is a safe practical ceiling.
	*/
	'title_max_length'          => 50,
	'body_max_length'           => 200,
	/*
	|----------------------------------------------------------------------
	| Queue worker
	|----------------------------------------------------------------------
	| dispatch_batch        : deliveries claimed per cron run. 50 at roughly 150ms each is about 8s 
	|                         - comfortably inside a one-minute schedule with room for a backlog.
	| stuck_after_minutes   : a delivery left in 'R' for this long is assumed abandoned (the worker died mid-send)
	|                         and is returned to pending. Must comfortably exceed the worst-case send time; releasing a
	|                         row that is still in flight causes a duplicate notification.
	| immediate_timeout     : seconds allowed for the optimistic send that happens right after enqueue.
    |                         Short on purpose: this runs inside a payment callback or a ticket save, and the cron is the safety net.
	| immediate_max_devices : cap on devices attempted inline. Beyond this the rest wait for the cron rather than making
	|                         one customer's many devices slow a request.
	*/
	'dispatch_batch'            => 50,
	'stuck_after_minutes'       => 10,
	'immediate_timeout'         => 4,
	'immediate_max_devices'     => 10,
	/*
	|----------------------------------------------------------------------
	| Internal API (Customer Portal -> Admin Portal dispatch ping)
	|----------------------------------------------------------------------
	| The Customer Portal enqueues payment notifications by writing to the
	| push_scheduler / push_outgoing tables. This endpoint only lets
	| it say "drain now" so the customer sees the confirmation in about a
	| second instead of waiting up to a minute for the cron.
	|
	| internal_api_secret MUST be changed before this is enabled. An empty
	| or CHANGE_ME value fails closed - the endpoint refuses every request
	| rather than running unauthenticated.
	|
	| Generate one with:  php -r "echo bin2hex(random_bytes(32));"
	| Keep it out of source control. Both applications need the same value.
	*/
	'internal_api_enabled'   => TRUE,
	'internal_api_secret'    => 'CHANGE_ME',
	'internal_api_window'    => 300,
	/*
	|----------------------------------------------------------------------
	| Queue bookkeeping
	|----------------------------------------------------------------------
	| queue_zero_device_rows: whether to write a row for a customer with no app installed.
    | FALSE by default - during rollout that is most customers, and the billing cron would 
    | insert thousands of rows a month recording that we did not notify people who cannot be notified. 
    | Set TRUE if you want that audit trail and accept the volume.
	*/
	'queue_zero_device_rows'    => FALSE,
	/*
	| billing_schedule_time: the time of day check_overdue() and
	| generate_monthly_bill() schedule push delivery for, matching the
	| existing 12pm email/whatsapp/telegram schedule from the same cron
	| jobs. Only those two cron call sites use this (opts['send_at']) -
	| every other notification still dispatches immediately and only
	| retries later on failure.
	*/
	'billing_schedule_time'  => '12:00:00',
	/*
	|----------------------------------------------------------------------
	| Cron safety
	|----------------------------------------------------------------------
	| Hard wall-clock budget (seconds) that a single cron run may spend on push.
	| Once exceeded, Push_service stops sending for the rest of the run and logs a warning. 
	| Billing MUST finish even if FCM is degraded.
	*/
	'cron_time_budget'          => 120,
	/*
	|----------------------------------------------------------------------
	| Rollout whitelist
	|----------------------------------------------------------------------
	| Empty array = send to everyone (normal operation).
	| Non-empty   = ONLY these customer_no receive pushes. Used in internal accounts against live data.
	*/
	'whitelist'                 => array(),
	/*
	|----------------------------------------------------------------------
	| Logging
	|----------------------------------------------------------------------
	| An FCM token is a send credential for that device. Never log it whole.
	| Only this many leading characters appear in logs.
	*/
	'log_token_prefix'          => 12,
);
