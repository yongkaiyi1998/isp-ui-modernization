<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notification_service
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->model('email_model');
		$this->CI->load->model('message_scheduler_model');
		$this->CI->load->model('common_model');

		$this->CI->load->library('whatsapp_template');
	}

	/**
	 * Build contact list for notificatin
	 *
	 * @param array $emails
	 * @param array $whatsapps
	 * @param array $telegrams
	 * @return array
	 */
	public function buildContacts(array $emails = [], array $whatsapps = [], array $telegrams = []): array
	{
		$contacts = [];

		foreach ([
			'email'     => $emails,
			'whatsapp'  => $whatsapps,
			'telegram'  => $telegrams,
		] as $type => $values) {

			foreach (array_filter($values) as $value) {
				$contacts[] = [
					'type'  => $type,
					'value' => $value,
				];
			}
		}

		return $contacts;
	}

	/**
	 * Queue notification.
	 *
	 * @param array  $contacts
	 * @param string $title
	 * @param string $body
	 * @param string $template_name
	 * @param array  $template_vars
	 * @param string $customer_no
	 * @param array  $options (optional, better to fill some important data)
	 * @return bool
	 */
	public function queue(array $contacts, $title, $body, $template_name = '', array $template_vars = [], $customer_no = '', array $options = [] ) {

		if (empty($contacts)) {
			return false;
		}

		$config = $this->CI->common_model->get_table(
			'sys_config',
			'*',
			"`category` = 'email' AND `key` = 'from_name'"
		);

		$from_name = $config[0]['val'] ?? 'no_reply@itelco.net';

		$meta_template = [];

		if (!empty($template_name)) {
			$meta_template = $this->CI->whatsapp_template->build($template_name, $template_vars);
		}

		foreach ($contacts as $contact) {

			if (empty($contact['value'])) {
				continue;
			}

			switch ($contact['type']) {

				case 'email':
					$this->queue_email($contact['value'], $title, $body);
					break;

				case 'whatsapp':
				case 'telegram':
					$this->queue_message($contact['type'], $contact['value'], $title, $body, $template_name, $meta_template, $customer_no, $from_name, $options);
					break;
			}
		}

		return true;
	}

	private function queue_email($email, $title, $body)
	{
		$this->CI->email_model->add_email_schedule([
			'recipient_emails'  => $email,
			'scheduler_id'      => '',
			'send_by'           => '',
			'email_title'       => $title,
			'email_msg'         => $body,
			'email_cust_status' => 1,
			'email_attachment'  => '',
			'email_schedule_on' => date('Y-m-d H:i:s')
		]);
	}

	private function queue_message($type,$receiver,$title,$body,$template_name,$meta_template,$customer_no,$from_name,$options) {

		$list_key = $type . '_list';

		$send_array = [
			'send_type'         => $options['send_type']      ?? 'custom',
			'acc_id'            => $options['acc_id']         ?? 0,
			'customer_no'       => $customer_no,
			'user_id'           => $options['user_id']        ?? 0,
			'controller'        => $options['controller']     ?? '',
			'doc_id'            => $options['doc_id']         ?? 0,
			'send_method'       => $options['send_method']    ?? 'manual',
			'acc_name'          => $options['acc_name']       ?? '',
			'attachment'        => $options['attachment']     ?? [],
			'subject'           => $type == 'whatsapp'
                                    ? '[FOLLOW WHATSAPP META TEMPLATE]'
                                    : $title,
			'body'              => $type == 'whatsapp'
                                    ? '[FOLLOW WHATSAPP META TEMPLATE]'
                                    : $body,
			'from'              => $from_name,
			'email_starter'     => $options['email_starter']  ?? '',
			'doc_type'          => $options['doc_type']       ?? '',
			$list_key           => [$receiver],
			'meta_template'     => $meta_template['meta_template_name'] ?? '',
			'meta_vars'         => $meta_template['meta_variable'] ?? []
		];

		$message_data = [
			'message'           => $template_name,
			'msg_type'          => $type,
			'msg_to'            => $receiver,
			'customer_no'       => $customer_no,
			'msg_schedule_on'   => date('Y-m-d H:i:s')
		];

		$this->CI->message_scheduler_model->insert_new_scheduled_message($message_data, $send_array);
	}
}