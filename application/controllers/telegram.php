<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Telegram extends CI_Controller {

	// telegram set webhook: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://yourdomain.com/telegram
	// telegram get updates: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates
	// telegram delete webhook: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/deleteWebhook
	// Telegram check webhook: https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getWebhookInfo

    function __construct() {
        parent::__construct();
    }

    public function index() {
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			show_error('No direct script access allowed', 403);
		}

        $update = json_decode(file_get_contents("php://input"), TRUE);

        if (!empty($update["message"])) {
            $chat_id = $update["message"]["chat"]["id"];
            $username = $update["message"]["from"]["username"] ?? '';
            $text = $update["message"]["text"];
            
            log_message("info", "Telegram Bot: [Chat ID: $chat_id], [Username: $username], [Text: $text]");

			$this->load->model('telegram_model');
            
            if (strpos($text, '/start') === 0) {
				$this->telegram_model->send([ 'chat_id' => $chat_id, 'text' => 'Welcome to Infonal Bot, this is your telegram ID, please save it for user settings.' ]);
				$this->telegram_model->send([ 'chat_id' => $chat_id, 'text' => "$chat_id" ]);
            } else if($text === '/info' || $text === '/help'){
				$this->telegram_model->send([ 'chat_id' => $chat_id, 'text' => "This bot is developed by Infonal, please contact us for any issues." ]);
            }
        }
    }
}
