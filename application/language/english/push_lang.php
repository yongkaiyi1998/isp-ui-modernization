<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Push Notification
|--------------------------------------------------------------------------
|
| Load with:  $this->lang->load('push', 'english');
| Read with:  $this->lang->line('push_bill_reminder_title');
|
| Every type in config/push_types.php needs exactly two lines here:
|   <lang_key>_title  and  <lang_key>_body
|
| To add a language: copy this file to
|   application/language/<languages>/push_lang.php
| and translate. Push_service picks the file via $opts['lang'], falling back
| to push.default_lang. No code change is needed to add a language.
*/

/*
|--------------------------------------------------------------------------
| Billing
|--------------------------------------------------------------------------
*/

$lang['push_bill_issued_title']      = 'New Bill Available';
$lang['push_bill_issued_body']       = 'Service %CUSTOMER_NO%: your new bill of RM %BALANCE% is ready. Please pay by %DUE_DATE%.';

$lang['push_bill_reminder_title']    = 'Payment Reminder';
$lang['push_bill_reminder_body']     = 'Service %CUSTOMER_NO%: RM %BALANCE% is due on %DUE_DATE% (%DAYS_LEFT% days left). Tap to make payment.';

$lang['push_bill_overdue_title']     = 'Payment Overdue';
$lang['push_bill_overdue_body']      = 'Service %CUSTOMER_NO%: RM %BALANCE% was due on %DUE_DATE%. Please make payment to avoid service disruption.';

$lang['push_bill_suspension_title']  = 'Service Suspension Notice';
$lang['push_bill_suspension_body']   = 'Service %CUSTOMER_NO%: RM %BALANCE% is still unpaid. Your service may be suspended soon. Tap to pay now.';

/*
|--------------------------------------------------------------------------
| Service Ticket  (ticket.php / trouble_ticket table)
|--------------------------------------------------------------------------
*/

$lang['push_ticket_status_title']    = 'Service Ticket Updated';
$lang['push_ticket_status_body']     = 'Ticket %TT_NO% has been updated to %STATUS_NAME%.';

$lang['push_ticket_closed_title']    = 'Service Ticket Closed';
$lang['push_ticket_closed_body']     = 'Ticket %TT_NO% has been closed. If you still need help, please contact us or submit a new request.';

$lang['push_ticket_reply_title']     = 'New Reply on Your Ticket';
$lang['push_ticket_reply_body']      = 'There is a new reply on ticket %TT_NO%.';

$lang['push_ticket_assigned_title']  = 'Technician Assigned';
$lang['push_ticket_assigned_body']   = 'A technician has been assigned to ticket %TT_NO%.';

$lang['push_ticket_not_related_title'] = 'Service Ticket Update';
$lang['push_ticket_not_related_body']  = 'Ticket %TT_NO% has been reviewed and the issue was not identified as a service fault. Please contact us if you still need assistance.';

/*
|--------------------------------------------------------------------------
| Trouble Ticket  (customer_support.php / cs table)
|--------------------------------------------------------------------------
*/

$lang['push_cs_status_title']        = 'Trouble Ticket Updated';
$lang['push_cs_status_body']         = 'Trouble ticket %CS_NO% is now %STATUS_NAME%.';

$lang['push_cs_closed_title']        = 'Trouble Ticket Closed';
$lang['push_cs_closed_body']         = 'Trouble ticket %CS_NO% has been closed. If you still need help, please contact us or submit a new request.';

/*
| Status labels. The call site passes STATUS_CODE; Push_template resolves
| the label in the DEVICE's language. Codes come from
| ticket::get_status_name() and customer_support::get_status_name().
*/
$lang['push_ticket_status_label_0'] = 'Closed';
$lang['push_ticket_status_label_1'] = 'Open';
$lang['push_ticket_status_label_2'] = 'Assigned';
$lang['push_ticket_status_label_3'] = 'In Progress';
$lang['push_ticket_status_label_4'] = 'Solved';
$lang['push_ticket_status_label_5'] = 'Not Service Related';

$lang['push_cs_status_label_0'] = 'Open';
$lang['push_cs_status_label_1'] = 'In Progress';
$lang['push_cs_status_label_2'] = 'With our specialist team';
$lang['push_cs_status_label_3'] = 'Closed';

/*
|--------------------------------------------------------------------------
| Payment  (Customer Portal / FPX)
|--------------------------------------------------------------------------
*/

$lang['push_payment_received_title']  = 'Payment Received';
$lang['push_payment_received_body']   = 'Service %CUSTOMER_NO%: we have received your payment of RM %AMOUNT%. Thank you.';

$lang['push_payment_failed_title']    = 'Payment Unsuccessful';
$lang['push_payment_failed_body']     = 'Service %CUSTOMER_NO%: your payment of RM %AMOUNT% was not successful. You have not been charged. Please try again.';

$lang['push_service_restored_title']  = 'Service Restored';
$lang['push_service_restored_body']   = 'Service %CUSTOMER_NO%: we have received your payment of RM %AMOUNT% and your service has been restored. Reconnection may take a few minutes.';

/*
|--------------------------------------------------------------------------
| Admin triggered
|--------------------------------------------------------------------------
*/

$lang['push_admin_triggered_title']  = 'Notification';
$lang['push_admin_triggered_body']   = 'You have a new message. Tap to open the app.';
