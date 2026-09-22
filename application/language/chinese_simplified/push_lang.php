<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Billing
|--------------------------------------------------------------------------
*/

$lang['push_bill_issued_title']       = '新账单已生成';
$lang['push_bill_issued_body']        = '账户 %CUSTOMER_NO%：您的新账单 RM %BALANCE% 已生成，请于 %DUE_DATE% 前缴费。';

$lang['push_bill_reminder_title']     = '缴费提醒';
$lang['push_bill_reminder_body']      = '账户 %CUSTOMER_NO%：账单 RM %BALANCE% 将于 %DUE_DATE% 到期，剩余 %DAYS_LEFT% 天。请及时缴费。';

$lang['push_bill_overdue_title']      = '账单已逾期';
$lang['push_bill_overdue_body']       = '账户 %CUSTOMER_NO%：账单 RM %BALANCE% 已于 %DUE_DATE% 逾期。请尽快缴费，以避免服务受影响。';

$lang['push_bill_suspension_title']   = '服务暂停通知';
$lang['push_bill_suspension_body']    = '账户 %CUSTOMER_NO%：账单 RM %BALANCE% 尚未缴清，您的服务可能会被暂停。点击立即缴费。';

/*
|--------------------------------------------------------------------------
| Service Ticket  (ticket.php / trouble_ticket table)
|--------------------------------------------------------------------------
*/

$lang['push_ticket_status_title']     = '服务工单已更新';
$lang['push_ticket_status_body']      = '工单 %TT_NO% 当前状态为：%STATUS_NAME%。';

$lang['push_ticket_closed_title']     = '服务工单已关闭';
$lang['push_ticket_closed_body']      = '工单 %TT_NO% 已关闭。如有其他问题，欢迎联系我们或重新提交服务请求。';

$lang['push_ticket_reply_title']      = '工单有新回复';
$lang['push_ticket_reply_body']       = '工单 %TT_NO% 收到新的回复。';

$lang['push_ticket_assigned_title']   = '已安排技术人员';
$lang['push_ticket_assigned_body']    = '已为工单 %TT_NO% 安排技术人员。';

$lang['push_ticket_not_related_title'] = '服务工单更新';
$lang['push_ticket_not_related_body']  = '工单 %TT_NO% 已完成审核，所报告的问题暂未发现与服务故障有关。如仍需要帮助，请联系我们。';

/*
|--------------------------------------------------------------------------
| Trouble Ticket  (customer_support.php / cs table)
|--------------------------------------------------------------------------
*/

$lang['push_cs_status_title']         = '投诉工单已更新';
$lang['push_cs_status_body']          = '投诉工单 %CS_NO% 当前状态为：%STATUS_NAME%。';

$lang['push_cs_closed_title']         = '投诉工单已关闭';
$lang['push_cs_closed_body']          = '投诉工单 %CS_NO% 已关闭。如有其他问题，欢迎联系我们或重新提交服务请求。';

/*
| Status labels - see the English file for the explanation.
*/
$lang['push_ticket_status_label_0'] = '已关闭';
$lang['push_ticket_status_label_1'] = '已开启';
$lang['push_ticket_status_label_2'] = '已派工';
$lang['push_ticket_status_label_3'] = '处理中';
$lang['push_ticket_status_label_4'] = '已解决';
$lang['push_ticket_status_label_5'] = '与服务故障无关';

$lang['push_cs_status_label_0'] = '已开启';
$lang['push_cs_status_label_1'] = '处理中';
$lang['push_cs_status_label_2'] = '已转交专业团队';
$lang['push_cs_status_label_3'] = '已关闭';

/*
|--------------------------------------------------------------------------
| Payment  (Customer Portal / FPX)
|--------------------------------------------------------------------------
*/

$lang['push_payment_received_title']  = '付款成功';
$lang['push_payment_received_body']   = '账户 %CUSTOMER_NO%：我们已收到您的付款 RM %AMOUNT%，谢谢您的付款。';

$lang['push_payment_failed_title']    = '付款未成功';
$lang['push_payment_failed_body']     = '账户 %CUSTOMER_NO%：RM %AMOUNT% 付款未成功，款项并未扣除。请重新尝试。';

$lang['push_service_restored_title']  = '服务已恢复';
$lang['push_service_restored_body']   = '账户 %CUSTOMER_NO%：我们已收到您的付款 RM %AMOUNT%，服务已恢复。重新连接可能需要几分钟。';

/*
|--------------------------------------------------------------------------
| Admin triggered
|--------------------------------------------------------------------------
| Fallbacks only - real copy comes from the admin form.
*/

$lang['push_admin_triggered_title']   = '通知';
$lang['push_admin_triggered_body']    = '您有一条新消息。点击打开应用。';
