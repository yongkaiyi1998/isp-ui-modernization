<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_template
{
    protected $CI;

    protected $isp_name = '';
    protected $company_name = '';
    protected $company_email = '';

    public function __construct()
    {
        $this->CI =& get_instance();

        $this->CI->load->model('common_model');

        $config_data = $this->CI->common_model->get_table(
            'sys_config',
            '*',
            "`key` IN (
                'isp_name',
                'company_full_name',
                'company_email'
            )"
        );

        $config_values = array_column($config_data, 'val', 'key');

        $this->isp_name     = $config_values['isp_name'] ?? '';
        $this->company_name = $config_values['company_full_name'] ?? '';
        $this->company_email = $config_values['company_email'] ?? '';
    }

    /**
     * Build WhatsApp template payload.
     *
     * @param string $template_name ERP Template Name or WhatsApp Template Name
     * @param array  $variables
     * @return array|false
     */
    public function build($template_name, array $variables = [])
    {
        return $this->buildPayload($template_name, $variables);
    }

    /**
     * Build WhatsApp template payload.
     *
     * @param string $template_name
     * @param array  $v
     * @return array|false
     */
    private function buildPayload($template_name, array $v)
    {
        switch ($template_name) {

            /*
             * Reminder Before Due
             */
            case 'REMINDER BEFORE DUE':
            case 'itelco_payment_notice':
                return [
                    'meta_template_name' => 'itelco_payment_notice',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'overdue_amount'    => $v['balance'] ?? '',
                        'bill_due_date'     => $v['bill_due_date'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Overdue SMS
             */
            case 'OVERDUE SMS':
            case 'itelco_overdue_msg':
                return [
                    'meta_template_name' => 'itelco_overdue_msg',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'balance'           => $v['balance'] ?? '',
                        'bill_due_date'     => $v['bill_due_date'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Reminder About Suspension
             */
            case 'REMINDER ABOUT SUSPENSION':
            case 'itelco_suspension_notice':
                return [
                    'meta_template_name' => 'itelco_suspension_notice',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'balance'           => $v['balance'] ?? '',
                        'bill_due_date'     => $v['bill_due_date'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Reminder Before Contract
             */
            case 'REMINDER BEFORE CONTRACT':
            case 'itelco_reminder_contract':
                return [
                    'meta_template_name' => 'itelco_reminder_contract',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'days_left'         => $v['days_left'] ?? '',
                        'contract_due_date' => $v['contract_due_date'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Auto Billing
             */
            case 'AUTO BILLING':
            case 'itelco_auto_bill':
                return [
                    'meta_template_name' => 'itelco_auto_bill',
                    'meta_variable' => [
                        'customer_no'   => $v['customer_no'] ?? '',
                        'bill_no'       => $v['bill_no'] ?? '',
                        'isp_name'      => $this->isp_name,
                        'bill_date'     => $v['bill_date'] ?? '',
                        'company_email' => $this->company_email,
                        'company_name'  => $this->company_name,
                        'lang'          => 'en'
                    ]
                ];

            /*
             * Termination Signature
             */
            case 'TERMINATION SIGNATURE':
            case 'itelco_termination_signature':
                return [
                    'meta_template_name' => 'itelco_termination_signature',
                    'meta_variable' => [
                        'title_isp_name' => $this->isp_name,
                        'isp_name'     => $this->isp_name,
                        'customer_no'    => $v['customer_no'] ?? '',
                        'isp_name_2'     => $this->isp_name,
                        'customer_email' => $v['customer_email'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Reply
             */
            case 'SERVICE TICKET REPLY':
            case 'itelco_service_ticket_reply':
                return [
                    'meta_template_name' => 'itelco_service_ticket_reply',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'tt_no'         => $v['tt_no'] ?? '',
                        'customer_no'   => $v['customer_no'] ?? '',
                        'tt_reply'      => $v['tt_reply'] ?? '',
                        'lang'          => 'en'

                    ]
                ];

            /*
             * Reactivate Account
             */
            case 'REACTIVATE ACCOUNT':
            case 'itelco_reactivate_account':
                return [
                    'meta_template_name' => 'itelco_reactivate_account',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_name'     => $v['customer_name'] ?? '',
                        'customer_no'       => $v['customer_no'] ?? '',
                        'isp_name'          => $this->isp_name,
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Payment Success
             */
            case 'PAYMENT SUCCESS':
            case 'itelco_payment_success':
                return [
                    'meta_template_name' => 'itelco_payment_success',
                    'meta_variable' => [
                        'customer_no'   => $v['customer_no'] ?? '',
                        'pay_date'      => $v['pay_date'] ?? '',
                        'amount'        => $v['amount'] ?? '',
                        'txn_id'        => $v['txn_id'] ?? '',
                        'order_no'      => $v['order_no'] ?? '',
                        'lang'          => 'en'
                    ]
                ];

            /*
             * New Registration
             */
            case 'NEW REGISTRATION':
            case 'itelco_new_registration':
                return [
                    'meta_template_name' => 'itelco_new_registration',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'reg_no'            => $v['reg_no'] ?? '',
                        'reg_type'          => $v['reg_type'] ?? '',
                        'comp_name'         => $v['comp_name'] ?? '',
                        'building'          => $v['building'] ?? '',
                        'name'              => $v['name'] ?? '',
                        'icno'              => $v['icno'] ?? '',
                        'phone'             => $v['phone'] ?? '',
                        'interested'        => $v['interested'] ?? '',
                        'contact_date_time' => $v['contact_date_time'] ?? '',
                        'agent'             => $v['agent'] ?? '',
                        'reg_url'           => $v['reg_url'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * New Sales Order
             */
            case 'NEW SALES ORDER':
            case 'itelco_new_so':
                return [
                    'meta_template_name' => 'itelco_new_so',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'so_id'             => $v['so_id'] ?? '',
                        'reg_type'          => $v['reg_type'] ?? '',
                        'comp_name'         => $v['comp_name'] ?? '',
                        'name'              => $v['name'] ?? '',
                        'building'          => $v['building'] ?? '',
                        'lang'              => 'en'
                    ]
                ];
                
            /*
             * Technical Config Updated
             */
            case 'TECHNICAL CONFIG UPDATED':
            case 'itelco_technical_config_updated':
                return [
                    'meta_template_name' => 'itelco_technical_config_updated',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'changed_column'    => $v['changed_column'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * DIA Jumpstart
             */
            case 'DIA JUMPSTART':
            case 'itelco_dia_jumpstart':
                return [
                    'meta_template_name' => 'itelco_dia_jumpstart',
                    'meta_variable' => [
                        'title_isp_name' => $this->isp_name,
                        'customer_no'    => $v['customer_no'] ?? '',
                        'activate'       => $v['activate'] ?? '',
                        'lang'           => 'en'
                    ]
                ];

            /*
             * Itelco Docs
             */
            case 'ITELCO DOCS':
            case 'itelco_docs':
                return [
                    'meta_template_name' => 'itelco_docs',
                    'meta_variable' => [
                        'doc_type'      => $v['doc_type'] ?? '',
                        'company_name'  => $this->company_name,
                        'lang'          => 'en'
                    ]
                ];

            /*
             * Manual Bill Approvel
             */
            case 'MANUAL BILL APPROVAL':
            case 'itelco_manual_bill_approval':
                return [
                    'meta_template_name' => 'itelco_manual_bill_approval',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'approval_level'    => $v['approval_level'] ?? '',
                        'customer_name'     => $v['customer_name'] ?? '',
                        'bill_draft_no'     => $v['bill_draft_no'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Manual Bill Update Status
             */
            case 'MANUAL BILL STATUS':
            case 'itelco_manual_bill_status':
                return [
                    'meta_template_name' => 'itelco_manual_bill_status',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'status'            => $v['status'] ?? '',
                        'customer_name'     => $v['customer_name'] ?? '',
                        'bill_draft_no'     => $v['bill_draft_no'] ?? '',
                        'performed_by'      => $v['performed_by'] ?? '',
                        'lang'              => 'en'
                    ]
                ];
                
            /*
             * Termination Form Status Updated
             */
            case 'TERMINATION FORM STATUS UPDATED':
            case 'itelco_termination_signature_status':
                return [
                    'meta_template_name' => 'itelco_termination_signature_status',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'status'            => $v['status'] ?? '',
                        'remarks'           => $v['remarks'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Customer Support Escalation
             */
            case 'TROUBLE TICKET ESCALATION':
            case 'itelco_customer_support_escalation':
                return [
                    'meta_template_name' => 'itelco_customer_support_escalation',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'cs_no'             => $v['cs_no'] ?? '',
                        'report_on'         => $v['report_on'] ?? '',
                        'onsite_on'         => $v['onsite_on'] ?? '',
                        'service_type'      => $v['service_type'] ?? '',
                        'service_remark'    => $v['service_remark'] ?? '',
                        'service_problem'   => $v['service_problem'] ?? '',
                        'action_remark'     => $v['action_remark'] ?? '',
                        'lang'              => 'en'
                    ]
                ];
                    
            /*
             * Profile Created
             */
            case 'PROFILE CREATED':
            case 'itelco_profile_created':
                return [
                    'meta_template_name' => 'itelco_profile_created',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'company_name'      => $this->company_name,
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Notification
             */
            case 'SERVICE TICKET CREATED':
            case 'SERVICE TICKET ASSIGN':
            case 'itelco_service_ticket_notification':
                return [
                    'meta_template_name' => 'itelco_service_ticket_notification',
                    'meta_variable' => [
                        'title_isp_name'          => $this->isp_name,
                        'notification'      => $v['notification'] ?? '',
                        'tt_no'             => $v['tt_no'] ?? '',
                        'customer_no'       => $v['customer_no'] ?? '',
                        'pic_name'          => $v['pic_name'] ?? '',
                        'datetime_open'     => $v['datetime_open'] ?? '',
                        'tt_complaint_name' => $v['tt_complaint_name'] ?? '',
                        'tt_remark'         => $v['tt_remark'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Assigned - Customer facing 
             */
            case 'SERVICE TICKET ASSIGNED - CUSTOMER':
            case 'itelco_service_ticket_assigned_customer':
                return [
                    'meta_template_name' => 'itelco_service_ticket_assigned_customer',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'tt_no'             => $v['tt_no'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Status Update
             */
            case 'SERVICE TICKET STATUS UPDATE':
            case 'itelco_service_ticket_status_update':
                return [
                    'meta_template_name' => 'itelco_service_ticket_status_update',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'tt_no'             => $v['tt_no'] ?? '',
                        'prev_status_name'  => $v['prev_status_name'] ?? '',
                        'status_name'       => $v['status_name'] ?? '',
                        'tt_sof_name'       => $v['tt_sof_name'] ?? '',
                        'tt_cof_name'       => $v['tt_cof_name'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Status Update - Customer facing
             */
            case 'SERVICE TICKET STATUS UPDATE - CUSTOMER':
            case 'itelco_service_ticket_status_update_customer':
                return [
                    'meta_template_name' => 'itelco_service_ticket_status_update_customer',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'tt_no'             => $v['tt_no'] ?? '',
                        'status_name'       => $v['status_name'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Service Ticket Not Related - Customer facing
             */
            case 'SERVICE TICKET NOT RELATED - CUSTOMER':
            case 'itelco_service_ticket_not_related_customer':
                return [
                    'meta_template_name' => 'itelco_service_ticket_not_related_customer',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'tt_no'             => $v['tt_no'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
            * Ticket Job Tracking
            */
            case 'TICKET JOB TRACKING':
            case 'itelco_ticket_job_tracking':
                return [
                    'meta_template_name' => 'itelco_ticket_job_tracking',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'ticket_type'       => $v['ticket_type'] ?? '',
                        'ticket_no'         => $v['ticket_no'] ?? '',
                        'notification'      => $v['notification'] ?? '',
                        'remark'            => $v['remark'] ?? '',
                        'updated_on'        => $v['updated_on'] ?? '',
                        'updated_by'        => $v['updated_by'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Change Package Request
             */
            case 'CHANGE PACKAGE REQUEST':
            case 'itelco_change_package_request':
                return [
                    'meta_template_name' => 'itelco_change_package_request',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'customer_no'       => $v['customer_no'] ?? '',
                        'old_package_name'  => $v['old_package_name'] ?? '',
                        'new_package_name'  => $v['new_package_name'] ?? '',
                        'reason'            => $v['reason'] ?? '',
                        'created_at'        => $v['created_at'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * Account Deletion Request
             */
            case 'ACCOUNT DELETION REQUEST':
            case 'itelco_account_deletion_request':
                return [
                    'meta_template_name' => 'itelco_account_deletion_request',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'acc_id'            => $v['acc_id'] ?? '',
                        'acc_name'          => $v['acc_name'] ?? '',
                        'acc_type'          => $v['acc_type'] ?? '',
                        'icno'              => $v['icno'] ?? '',
                        'acc_email'         => $v['acc_email'] ?? '',
                        'acc_mobileno'      => $v['acc_mobileno'] ?? '',
                        'lang'              => 'en'
                    ]
                ];

            /*
             * New Profile
             */
            case 'NEW PROFILE':
            case 'itelco_new_profile':
                return [
                    'meta_template_name' => 'itelco_new_profile',
                    'meta_variable' => [
                        'title_isp_name'    => $this->isp_name,
                        'acc_name'          => $v['acc_name'] ?? '',
                        'type'              => $v['type'] ?? '',
                        'phone'             => $v['phone'] ?? '',
                        'icno'              => $v['icno'] ?? '',
                        'comp_name'         => $v['comp_name'] ?? '',
                        'lang'              => 'en'
                    ]
                ];
            
            default:
                return false;
        }
    }
}