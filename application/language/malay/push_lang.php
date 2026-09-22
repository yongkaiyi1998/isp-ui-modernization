<?php if (! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Billing
|--------------------------------------------------------------------------
*/

$lang['push_bill_issued_title']       = 'Bil Baharu Tersedia';
$lang['push_bill_issued_body']        = 'Akaun %CUSTOMER_NO%: bil baharu RM %BALANCE% telah dikeluarkan. Sila buat bayaran sebelum %DUE_DATE%.';

$lang['push_bill_reminder_title']     = 'Peringatan Pembayaran';
$lang['push_bill_reminder_body']      = 'Akaun %CUSTOMER_NO%: RM %BALANCE% perlu dibayar sebelum %DUE_DATE% (%DAYS_LEFT% hari lagi). Tekan untuk bayar.';

$lang['push_bill_overdue_title']      = 'Bayaran Tertunggak';
$lang['push_bill_overdue_body']       = 'Akaun %CUSTOMER_NO%: RM %BALANCE% telah tertunggak sejak %DUE_DATE%. Sila buat bayaran untuk mengelakkan gangguan perkhidmatan.';

$lang['push_bill_suspension_title']   = 'Notis Penggantungan Perkhidmatan';
$lang['push_bill_suspension_body']    = 'Akaun %CUSTOMER_NO%: RM %BALANCE% masih belum dibayar. Perkhidmatan anda mungkin digantung. Tekan untuk bayar sekarang.';

/*
|--------------------------------------------------------------------------
| Service Ticket  (ticket.php / trouble_ticket table)
|--------------------------------------------------------------------------
*/

$lang['push_ticket_status_title']     = 'Status Tiket Servis Dikemas Kini';
$lang['push_ticket_status_body']      = 'Tiket %TT_NO% kini berstatus %STATUS_NAME%.';

$lang['push_ticket_closed_title']     = 'Tiket Servis Ditutup';
$lang['push_ticket_closed_body']      = 'Tiket %TT_NO% telah ditutup. Jika anda masih memerlukan bantuan, sila hubungi kami atau hantar permintaan baharu.';

$lang['push_ticket_reply_title']      = 'Maklum Balas Baharu';
$lang['push_ticket_reply_body']       = 'Terdapat maklum balas baharu bagi tiket %TT_NO%.';

$lang['push_ticket_assigned_title']   = 'Juruteknik Ditugaskan';
$lang['push_ticket_assigned_body']    = 'Juruteknik telah ditugaskan bagi tiket %TT_NO%.';

$lang['push_ticket_not_related_title'] = 'Kemas Kini Tiket Servis';
$lang['push_ticket_not_related_body']  = 'Tiket %TT_NO% telah disemak dan isu yang dilaporkan tidak berkaitan dengan gangguan perkhidmatan. Sila hubungi kami jika anda masih memerlukan bantuan.';

/*
|--------------------------------------------------------------------------
| Trouble Ticket  (customer_support.php / cs table)
|--------------------------------------------------------------------------
*/

$lang['push_cs_status_title']         = 'Tiket Aduan Dikemas Kini';
$lang['push_cs_status_body']          = 'Tiket aduan %CS_NO% kini berstatus %STATUS_NAME%.';

$lang['push_cs_closed_title']         = 'Tiket Aduan Ditutup';
$lang['push_cs_closed_body']          = 'Tiket aduan %CS_NO% telah ditutup. Jika anda masih memerlukan bantuan, sila hubungi kami atau hantar permintaan baharu.';

/*
| Label status. See the English file for the explanation.
*/
$lang['push_ticket_status_label_0'] = 'Ditutup';
$lang['push_ticket_status_label_1'] = 'Dibuka';
$lang['push_ticket_status_label_2'] = 'Ditugaskan';
$lang['push_ticket_status_label_3'] = 'Dalam Proses';
$lang['push_ticket_status_label_4'] = 'Selesai';
$lang['push_ticket_status_label_5'] = 'Tidak Berkaitan dengan Gangguan Perkhidmatan';

$lang['push_cs_status_label_0'] = 'Dibuka';
$lang['push_cs_status_label_1'] = 'Dalam Proses';
$lang['push_cs_status_label_2'] = 'Dengan pasukan pakar kami';
$lang['push_cs_status_label_3'] = 'Ditutup';

/*
|--------------------------------------------------------------------------
| Payment  (Customer Portal / FPX)
|--------------------------------------------------------------------------
*/

$lang['push_payment_received_title']  = 'Pembayaran Diterima';
$lang['push_payment_received_body']   = 'Akaun %CUSTOMER_NO%: kami telah menerima pembayaran RM %AMOUNT%. Terima kasih.';

$lang['push_payment_failed_title']    = 'Pembayaran Tidak Berjaya';
$lang['push_payment_failed_body']     = 'Akaun %CUSTOMER_NO%: pembayaran RM %AMOUNT% tidak berjaya. Tiada wang ditolak. Sila cuba lagi.';

$lang['push_service_restored_title']  = 'Perkhidmatan Dipulihkan';
$lang['push_service_restored_body']   = 'Akaun %CUSTOMER_NO%: kami telah menerima pembayaran RM %AMOUNT% dan perkhidmatan telah dipulihkan. Sambungan semula mengambil masa beberapa minit.';

/*
|--------------------------------------------------------------------------
| Admin triggered
|--------------------------------------------------------------------------
*/

$lang['push_admin_triggered_title']   = 'Pemberitahuan';
$lang['push_admin_triggered_body']    = 'Anda mempunyai mesej baharu. Tekan untuk membuka aplikasi.';
