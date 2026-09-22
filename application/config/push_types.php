<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Push Notification - Type Registry
|--------------------------------------------------------------------------
|
| Adding a new notification type is:
|
|   1. Add one block below.
|   2. Add *_title and *_body to language/english/push_lang.php.
|   3. Call Push_service::notify_customer($customer_no, 'your_type', array(...)) once,
|      at the point in the business flow where the event happens.
|
|--------------------------------------------------------------------------
| FIELD REFERENCE
|--------------------------------------------------------------------------
|
| lang_key  Prefix into push_lang.php. Resolves to <lang_key>_title
|           and <lang_key>_body.
|
| channel   Android notification channel id. MUST exist in the Flutter app
|           (created in PushNotificationService). Adding a channel here
|           without adding it in Flutter means Android silently drops the
|           notification to the default channel. Current channels:
|           'bills', 'tickets', 'general'.
|
| route     Deep-link hint carried in data.route. The Flutter
|           NotificationRouter switches on data.type, not this - route is a
|           fallback and a debugging aid. Keep them consistent anyway.
|
| collapse  FCM collapse key / apns-collapse-id. Messages sharing a key
|           replace each other while undelivered. Prevents a customer who
|           was offline for a week from waking to five identical bill
|           reminders. Set NULL to disable collapsing.
|
| ttl       How long FCM holds an undelivered message. A bill reminder that
|           arrives four days late is noise; a ticket update is still useful
|           a week on. Format: seconds with an 's' suffix.
|
| id_field  Documents what data.related_id contains for this type, so the
|           Flutter side knows what it is deep-linking with.
|
| vars      The %PLACEHOLDER% names this type's copy expects. Purely
|           declarative - Push_template logs a warning when a
|           declared var is missing at render time, which turns a silent
|           "%BALANCE%" in a customer's notification tray into a log line.
|
| enabled   Per-type kill switch. Combined with the global switch in
|           config/push.php, this is how the rollout phases are
|           gated (tickets first, then bills).
|
*/

$config['push_types'] = array(

	/*
	|======================================================================
	| BILLING
	|======================================================================
	| Trigger points: controllers/cron.php
	|   bill_issued      -> generate_monthly_bill()
	|   bill_reminder    -> check_overdue(), stages 1 and 2
	|   bill_overdue     -> check_overdue(), stages 3 and 4
	|   bill_suspension  -> check_overdue(), stage 6
	|
	| CUSTOMER_NO is mandatory in the body: one profile can hold several
	| customer_no, and all of them push to the same devices.
	*/

	'bill_issued' => array(
		'lang_key' => 'push_bill_issued',
		'channel'  => 'bills',
		'route'    => '/payment',
		'collapse' => 'bill',
		'ttl'      => '259200s',   	// 3 days
		'id_field' => 'bill_no',
		'vars'     => array('CUSTOMER_NO', 'BALANCE', 'DUE_DATE'),
		'enabled'  => TRUE,
	),
	'bill_reminder' => array(
		'lang_key' => 'push_bill_reminder',
		'channel'  => 'bills',
		'route'    => '/payment',
		'collapse' => 'bill',
		'ttl'      => '259200s',   	// 3 days
		'id_field' => 'bill_no',
		'vars'     => array('CUSTOMER_NO', 'BALANCE', 'DUE_DATE', 'DAYS_LEFT'),
		'enabled'  => TRUE,
	),
	'bill_overdue' => array(
		'lang_key' => 'push_bill_overdue',
		'channel'  => 'bills',
		'route'    => '/payment',
		'collapse' => 'bill',
		'ttl'      => '432000s',   	// 5 days
		'id_field' => 'bill_no',
		'vars'     => array('CUSTOMER_NO', 'BALANCE', 'DUE_DATE'),
		'enabled'  => TRUE,
	),
	'bill_suspension' => array(
		'lang_key' => 'push_bill_suspension',
		'channel'  => 'bills',
		'route'    => '/payment',
		'collapse' => 'bill',
		'ttl'      => '432000s',	// 5 days
		'id_field' => 'bill_no',
		'vars'     => array('CUSTOMER_NO', 'BALANCE'),
		'enabled'  => TRUE,
	),

	/*
	|======================================================================
	| PAYMENT  (Customer Portal - FPX callback)
	|======================================================================
	| Enqueued by the Customer Portal, not the Admin Portal:
	|   payment_received -> PaybillModel::process_paynet_ac()
	|   payment_failed   -> Fpx::postDirect(), INSIDE the checksum-valid branch only
	|
	| collapse is NULL - every payment is its own fact and must never
	| replace another.
	*/

	'payment_received' => array(
		'lang_key' => 'push_payment_received',
		'channel'  => 'bills',
		'route'    => '/history',
		'collapse' => NULL,
		'ttl'      => '86400s',	// 1 day
		'id_field' => 'payment_no',
		'vars'     => array('CUSTOMER_NO', 'AMOUNT'),
		'enabled'  => TRUE,
	),
	'payment_failed' => array(
		'lang_key' => 'push_payment_failed',
		'channel'  => 'bills',
		'route'    => '/home',
		'collapse' => NULL,
		'ttl'      => '86400s',	// 1 day
		'id_field' => 'order_no',
		'vars'     => array('CUSTOMER_NO', 'AMOUNT'),
		'enabled'  => TRUE,
	),
	'service_restored' => array(
		'lang_key' => 'push_service_restored',
		'channel'  => 'bills',
		'route'    => '/home',
		'collapse' => NULL,
		'ttl'      => '86400s',	// 1 day
		'id_field' => 'payment_no',
		'vars'     => array('CUSTOMER_NO', 'AMOUNT'),
		'enabled'  => TRUE,
	),

	/*
	|======================================================================
	| SERVICE TICKET
	|======================================================================
	| Trigger points: models/ticket_model.php
	|   ticket_status   -> update_ticket_status(), after the UPDATE succeeds
	|   ticket_reply    -> send_reply_to_customer(), alongside email/whatsapp
	|   ticket_assigned -> save_trouble_ticket(), when tt_assign_to changes
	*/

	'ticket_status' => array(
		'lang_key'   => 'push_ticket_status',
		'label_key'  => 'push_ticket_status_label',
		'channel'    => 'tickets',
		'route'      => '/ticket',
		'collapse'   => NULL,        // status changes are individually meaningful
		'ttl'        => '604800s',   // 7 days
		'id_field'   => 'tt_no',
		'vars'       => array('TT_NO', 'STATUS_CODE'),
		'enabled'    => TRUE,
	),
	'ticket_closed' => array(
		'lang_key' => 'push_ticket_closed',
		'channel'   => 'tickets',
		'route'     => '/ticket',
		'collapse' => NULL,
		'ttl'      => '604800s',	// 7 days
		'id_field' => 'tt_no',
		'vars'     => array('TT_NO'),
		'enabled'  => TRUE,
	),
	'ticket_reply' => array(
		'lang_key' => 'push_ticket_reply',
		'channel'   => 'tickets',
		'route'     => '/ticket',
		'collapse' => NULL,
		'ttl'      => '604800s',	// 7 days
		'id_field' => 'tt_no',
		'vars'     => array('TT_NO'),
		'enabled'  => FALSE,
	),
	'ticket_assigned' => array(
		'lang_key' => 'push_ticket_assigned',
		'channel'   => 'tickets',
		'route'     => '/ticket',
		'collapse' => NULL,
		'ttl'      => '604800s',	// 7 days
		'id_field' => 'tt_no',
		'vars'     => array('TT_NO'),
		'enabled'  => TRUE,
	),
	'ticket_not_related' => array(
		'lang_key' => 'push_ticket_not_related',
		'channel'   => 'tickets',
		'route'     => '/ticket',
		'collapse' => NULL,
		'ttl'      => '604800s',	// 7 days
		'id_field' => 'tt_no',
		'vars'     => array('TT_NO'),
		'enabled'  => TRUE,
	),

	/*
	|======================================================================
	| TROUBLE TICKET
	|======================================================================
	| Trigger point: customer_support::save_customer_support()
	|
	| Status codes come from customer_support::get_status_name():
	|   0 Open | 1 In Progress | 2 Escalated to 3rd Level | 3 Closed
	*/

	'cs_status' => array(
		'lang_key'   => 'push_cs_status',
		'label_key'  => 'push_cs_status_label',
		'channel'    => 'tickets',
		'route'      => '/ticket',
		'collapse'   => NULL,
		'ttl'        => '604800s',	// 7 days
		'id_field'   => 'cs_no',
		'vars'       => array('CS_NO', 'STATUS_CODE'),
		'enabled'    => FALSE,
	),
	'cs_closed' => array(
		'lang_key' => 'push_cs_closed',
		'channel'   => 'tickets',
		'route'     => '/ticket',
		'collapse' => NULL,
		'ttl'      => '604800s',	// 7 days
		'id_field' => 'cs_no',
		'vars'     => array('CS_NO'),
		'enabled'  => FALSE,
	),

	/*
	|======================================================================
	| ADMIN TRIGGERED
	|======================================================================
	| Trigger point: admin push notification (in future)
	*/

	'admin_triggered' => array(
		'lang_key' => 'push_admin_triggered',
		'channel'  => 'general',
		'route'    => '/home',
		'collapse' => NULL,
		'ttl'      => '86400s',    // 1 day
		'id_field' => NULL,
		'vars'     => array(),
		'enabled'  => FALSE,
	),

);
