<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Turns (type, vars, language) into (title, body, data).
 */
class Push_template {

	/** @var CI_Controller */
	protected $CI;
	/** @var array transport config (length limits, default language) */
	protected $cfg = array();
	/** @var array the type registry from config/push_types.php */
	protected $types = array();
	/** @var array language lines, keyed by idiom - lets one request render in several languages */
	protected $lines = array();
	// Reserved by FCM
	protected $reserved_data_keys = array('from', 'notification', 'message_type', 'collapse_key');

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->config->load('push', TRUE, TRUE);
		$this->CI->config->load('push_types', TRUE, TRUE);

		$this->cfg   = (array) $this->CI->config->item('push', 'push');
		$this->types = (array) $this->CI->config->item('push_types', 'push_types');

		if (empty($this->types))
		{
			log_message('error', '[push] push_types registry is empty or failed to load');
		}
	}

	// ==================================================================
	// PUBLIC API
	// ==================================================================

	/**
	 * Whether a type exists in the registry at all. 
	 */
	public function type_exists($type)
	{
		return isset($this->types[$type]);
	}

	/**
	 * Every status label defined for a label_key, as code => label.
	 *
	 * @param  string      $label_key e.g. push_ticket_status_label
	 * @param  string|null $idiom     language folder; NULL = default_lang
	 * @return array       code => label, sorted by code
	 */
	public function get_status_labels($label_key, $idiom = NULL)
	{
		if (empty($label_key))
		{
			return array();
		}

		$idiom = ($idiom !== NULL && $idiom !== '')
			? $idiom
			: $this->item('default_lang', 'english');

		$lines  = $this->lang_lines($idiom);
		$prefix = $label_key . '_';
		$out    = array();

		foreach ($lines as $key => $value)
		{
			if (strpos($key, $prefix) === 0)
			{
				$out[substr($key, strlen($prefix))] = $value;
			}
		}

		ksort($out);

		return $out;
	}

	/**
	 * The registry entry for a type, or NULL.
	 */
	public function get_type($type)
	{
		return isset($this->types[$type]) ? $this->types[$type] : NULL;
	}

	public function get_types()
	{
		return $this->types;
	}

	/**
	 * Render one notification.
	 *
	 * @param string $type   registry key, e.g. 'bill_overdue'
	 * @param array  $vars   %PLACEHOLDER% => value (keys without the % signs)
	 * @param array  $opts   title, body  - override the language file (admin_triggered)
	 *                       lang         - idiom, defaults to push.default_lang
	 *                       related_id   - goes into data.related_id
	 *                       customer_no  - goes into data.customer_no
	 *                       route        - overrides the registry route
	 *                       data         - extra data pairs, merged last
	 * @return array|null    array(title, body, data, channel, collapse, ttl)
	 *                       NULL when the type is unknown
	 */
	public function render($type, array $vars = array(), array $opts = array())
	{
		$def = $this->get_type($type);

		if ($def === NULL)
		{
			log_message('error', '[push] render() called with unknown type "' . $type . '"');
			return NULL;
		}

		$lang  = isset($opts['lang']) ? $opts['lang'] : $this->item('default_lang', 'english');
		$lines = $this->lang_lines($lang);

		$lang_key = isset($def['lang_key']) ? $def['lang_key'] : '';

		// Admin-triggered content comes from the form, not the language file. Everything else is translated copy.
		$title = isset($opts['title']) && $opts['title'] !== ''
			? $opts['title']
			: $this->line($lines, $lang_key . '_title', $type);

		$body = isset($opts['body']) && $opts['body'] !== ''
			? $opts['body']
			: $this->line($lines, $lang_key . '_body', '');

		// Validate BEFORE transforming
		$this->warn_missing_vars($type, $def, $vars);

		$vars = $this->resolve_labels($def, $vars, $lines);

		$title = $this->substitute($title, $vars);
		$body  = $this->substitute($body, $vars);

		// Refuse to send a half-subtituted notification
		$is_custom = (isset($opts['title']) && $opts['title'] !== '')
			OR (isset($opts['body']) && $opts['body'] !== '');

		if ( ! $is_custom && preg_match('/%[A-Z0-9_]+%/', $title . ' ' . $body, $m))
		{
			log_message('error', '[push] type "' . $type . '" left "' . $m[0]
				. '" unsubstituted (lang=' . $lang . ') - not sending');

			return NULL;
		}

		$title = $this->clamp($title, (int) $this->item('title_max_length', 50));
		$body  = $this->clamp($body, (int) $this->item('body_max_length', 200));

		return array(
			'title'    => $title,
			'body'     => $body,
			'data'     => $this->build_data($type, $def, $opts),
			'channel'  => isset($def['channel']) ? $def['channel'] : 'general',
			'collapse' => isset($def['collapse']) ? $def['collapse'] : NULL,
			'ttl'      => isset($def['ttl']) ? $def['ttl'] : '86400s',
		);
	}

	// ==================================================================
	// INTERNALS
	// ==================================================================

	/**
	 * The data payload the Flutter app receives.
	 */
	protected function build_data($type, array $def, array $opts)
	{
		$data = array(
			'type'  => $type,
			'route' => isset($opts['route']) ? $opts['route'] : (isset($def['route']) ? $def['route'] : '/home'),
			'ts'    => (string) time(),   // UTC epoch - the app formats for local display
		);

		if (isset($opts['related_id']) && $opts['related_id'] !== '' && $opts['related_id'] !== NULL)
		{
			$data['related_id'] = $opts['related_id'];
		}

		// Always present for billing: one profile can own several
		// customer_no and they all push to the same devices, so the app
		// needs to know which service the notification is about.
		if (isset($opts['customer_no']) && $opts['customer_no'] !== '' && $opts['customer_no'] !== NULL)
		{
			$data['customer_no'] = $opts['customer_no'];
		}

		if ( ! empty($opts['data']) && is_array($opts['data']))
		{
			foreach ($opts['data'] as $key => $value)
			{
				if (in_array($key, $this->reserved_data_keys, TRUE) OR strpos($key, 'google') === 0 OR strpos($key, 'gcm') === 0)
				{
					log_message('error', '[push] dropped reserved data key "' . $key . '" on type ' . $type);
					continue;
				}

				$data[$key] = $value;
			}
		}

		// FCM v1 rejects the ENTIRE message with a 400 if any data value is an int, bool or null.
		foreach ($data as $key => $value)
		{
			$data[$key] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
		}

		return $data;
	}

	/**
	 * Replace %PLACEHOLDER% tokens.
	 */
	protected function substitute($text, array $vars)
	{
		if ($text === '' OR empty($vars))
		{
			return (string) $text;
		}

		$search  = array();
		$replace = array();

		foreach ($vars as $key => $value)
		{
			$search[]  = '%' . strtoupper($key) . '%';
			$replace[] = is_bool($value) ? ($value ? '1' : '0') : (string) $value;
		}

		return str_replace($search, $replace, $text);
	}

	/**
	 * A placeholder that never got substituted renders literally in the
	 * customer's notification tray - "RM %BALANCE% is due".
	 */
	/**
	 * Turn a status CODE into a translated status LABEL.
	 *
	 * @param  array $def   registry entry for the type
	 * @param  array $vars  caller-supplied values
	 * @param  array $lines language lines for the target idiom
	 * @return array $vars with STATUS_CODE swapped for a localised STATUS_NAME
	 */
	protected function resolve_labels(array $def, array $vars, array $lines)
	{
		if (empty($def['label_key']) OR ! isset($vars['STATUS_CODE']))
		{
			return $vars;
		}

		$code  = (string) $vars['STATUS_CODE'];
		$key   = $def['label_key'] . '_' . $code;
		$label = $this->line($lines, $key, '');

		if ($label === '')
		{
			log_message('error', '[push] no status label for "' . $key
				. '" - add it to the language files');
		}
		else
		{
			$vars['STATUS_NAME'] = $label;
		}

		// Never leave STATUS_CODE in $vars
		unset($vars['STATUS_CODE']);

		return $vars;
	}

	protected function warn_missing_vars($type, array $def, array $vars)
	{
		if (empty($def['vars']) OR ! is_array($def['vars']))
		{
			return;
		}

		$provided = array_map('strtoupper', array_keys($vars));
		$missing  = array();

		foreach ($def['vars'] as $expected)
		{
			if ( ! in_array(strtoupper($expected), $provided, TRUE))
			{
				$missing[] = $expected;
			}
		}

		if ( ! empty($missing))
		{
			log_message('error', '[push] type=' . $type . ' missing template vars: ' . implode(', ', $missing));
		}
	}

	/**
	 * Truncate on a word boundary where possible.
	 */
	protected function clamp($text, $max)
	{
		$text = trim(preg_replace('/\s+/u', ' ', (string) $text));

		if ($max <= 0 OR $this->strlen($text) <= $max)
		{
			return $text;
		}

		$cut   = $this->substr($text, 0, $max - 1);
		$space = strrpos($cut, ' ');

		if ($space !== FALSE && $space > ($max * 0.6))
		{
			$cut = substr($cut, 0, $space);
		}

		return rtrim($cut) . "\xE2\x80\xA6";   // ellipsis
	}

	/**
	 * Load a language file without polluting CI's global lang state.
	 */
	protected function lang_lines($idiom)
	{
		if (isset($this->lines[$idiom]))
		{
			return $this->lines[$idiom];
		}

		$lines = $this->CI->lang->load('push', $idiom, TRUE);

		if ( ! is_array($lines) OR empty($lines))
		{
			$fallback = $this->item('default_lang', 'english');

			if ($idiom !== $fallback)
			{
				log_message('error', '[push] language "' . $idiom . '" not found, falling back to ' . $fallback);
				return $this->lang_lines($fallback);
			}

			log_message('error', '[push] push_lang.php missing for ' . $idiom);
			$lines = array();
		}

		$this->lines[$idiom] = $lines;

		return $lines;
	}

	protected function line(array $lines, $key, $default = '')
	{
		if (isset($lines[$key]) && $lines[$key] !== '')
		{
			return $lines[$key];
		}

		if ($key !== '_title' && $key !== '_body')
		{
			log_message('error', '[push] missing language line "' . $key . '"');
		}

		return $default;
	}

	protected function item($key, $default = NULL)
	{
		return isset($this->cfg[$key]) ? $this->cfg[$key] : $default;
	}

	protected function strlen($str)
	{
		return function_exists('mb_strlen') ? mb_strlen($str, 'UTF-8') : strlen($str);
	}

	protected function substr($str, $start, $length)
	{
		return function_exists('mb_substr') ? mb_substr($str, $start, $length, 'UTF-8') : substr($str, $start, $length);
	}
}
