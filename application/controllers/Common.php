<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Common extends CI_Controller
{

	function __construct()
	{
		parent::__construct();
		define("BASE_URL", base_url());
		define("ASSETS_BASE_URL", BASE_URL);
		define('JKAPPS_NODEJS_URL', 'http://mobisuit.com:5050/');

		define('DEFAULT_FROM_EMAIL', 'kalpesh@jksol.com');
		define('DEFAULT_FROM_EMAIL_NAME', 'Kalpesh Padshala');

		$this->load->database();

		$this->load->model('Mdl_common', 'mdl_common');
	}

	public function login_required()
	{
		$user_id = $this->session->userdata('user_id');
		if ($user_id) {
			return $user_id;
		} else {
			redirect('logout');
		}
	}

	public function insert_setting($setting_key, $setting_value)
	{
		$option = array(
			'select' => 'setting_value',
			'from' => 'tbl_settings',
			'where' => array(
				'setting_key' => "$setting_key"
			),
			'pagination' => array(
				'limit' => 1
			)
		);

		$result = $this->mdl_common->select($option);

		if (isset($result[0]['setting_value'])) {
			return false;
		} else {

			// Insert new key
			$option = array(
				'from' => 'tbl_settings',
				'insert_data' => array(
					'setting_key' => $setting_key,
					'setting_value' => $setting_value
				)
			);

			return $this->mdl_common->insert($option);
		}
	}

	public function get_setting_value($setting_key)
	{
		$option = array(
			'select' => 'setting_value',
			'from' => 'tbl_settings',
			'where' => array(
				'setting_key' => "$setting_key"
			),
			'pagination' => array(
				'limit' => 1
			)
		);

		$result = $this->mdl_common->select($option);

		if (isset($result[0]['setting_value'])) {
			return $result[0]['setting_value'];
		} else {
			return false;
		}
	}

	public function update_setting_value($setting_key, $setting_value)
	{
		$option = array(
			'from' => 'tbl_settings',
			'where' => array(
				'setting_key' => "$setting_key"
			),
			'update_data' => array(
				'setting_value' => $setting_value,
				'updated_at' => date('Y-m-d H:i:s')
			)
		);

		$update = $this->mdl_common->update($option);

		if ($update) {
			return true;
		} else {
			return false;
		}
	}

	function current_datetime()
	{
		return date('Y-m-d H:i:s');
	}

	public function today()
	{
		$dateTime = getdate();
		return $this->toDate($dateTime);
	}

	public function yesterday()
	{
		$dateTime = getdate(strtotime('-1 day'));
		return $this->toDate($dateTime);
	}

	public function sevenDaysBeforeToday()
	{
		$dateTime = getdate(strtotime('-7 day'));
		return $this->toDate($dateTime);
	}

	public function thirtyDaysBeforeToday()
	{
		$dateTime = getdate(strtotime('-30 day'));
		return $this->toDate($dateTime);
	}

	public function twoDaysBeforeToday()
	{
		$dateTime = getdate(strtotime('-2 day'));
		return $this->toDate($dateTime);
	}

	public function oneWeekBeforeToday()
	{
		$dateTime = getdate(strtotime('-1 week'));
		return $this->toDate($dateTime);
	}

	public function firstDateOfCurrentMonth()
	{
		$dateTime = getdate(strtotime(date('Y-m-01')));
		return $this->toDate($dateTime);
	}

	public function firstDateOfLastMonth()
	{
		$dateTime = getdate(strtotime('first day of last month'));
		return $this->toDate($dateTime);
	}

	public function lastDateOfLastMonth()
	{
		$dateTime = getdate(strtotime('last day of last month'));
		return $this->toDate($dateTime);
	}

	public function tenDaysBeforeToday()
	{
		$dateTime = getdate(strtotime('-10 day'));
		return $this->toDate($dateTime);
	}

	public function toDate($dateTime)
	{
		$date = new \Google_Service_AdMob_Date();
		$date->setDay($dateTime['mday']);
		$date->setMonth($dateTime['mon']);
		$date->setYear($dateTime['year']);
		return $date;
	}

	function microValueConvert($microValue)
	{
		if ($microValue != 0) {
			$microValue = ($microValue / 1000000);
		}
		return $microValue;
	}

	public function get_country_name($alpha2_code)
	{
		$countries = array(
			array('country_name' => 'Andorra', 'alpha2_code' => 'AD'),
			array('country_name' => 'Afghanistan', 'alpha2_code' => 'AF'),
			array('country_name' => 'Åland Islands', 'alpha2_code' => 'AX'),
			array('country_name' => 'Albania', 'alpha2_code' => 'AL'),
			array('country_name' => 'Algeria', 'alpha2_code' => 'DZ'),
			array('country_name' => 'American Samoa', 'alpha2_code' => 'AS'),
			array('country_name' => 'Angola', 'alpha2_code' => 'AO'),
			array('country_name' => 'Anguilla', 'alpha2_code' => 'AI'),
			array('country_name' => 'Antarctica', 'alpha2_code' => 'AQ'),
			array('country_name' => 'Antigua and Barbuda', 'alpha2_code' => 'AG'),
			array('country_name' => 'Argentina', 'alpha2_code' => 'AR'),
			array('country_name' => 'Armenia', 'alpha2_code' => 'AM'),
			array('country_name' => 'Aruba', 'alpha2_code' => 'AW'),
			array('country_name' => 'Australia', 'alpha2_code' => 'AU'),
			array('country_name' => 'Austria', 'alpha2_code' => 'AT'),
			array('country_name' => 'Azerbaijan', 'alpha2_code' => 'AZ'),
			array('country_name' => 'Bahamas', 'alpha2_code' => 'BS'),
			array('country_name' => 'Bahrain', 'alpha2_code' => 'BH'),
			array('country_name' => 'Bangladesh', 'alpha2_code' => 'BD'),
			array('country_name' => 'Barbados', 'alpha2_code' => 'BB'),
			array('country_name' => 'Belarus', 'alpha2_code' => 'BY'),
			array('country_name' => 'Belgium', 'alpha2_code' => 'BE'),
			array('country_name' => 'Belize', 'alpha2_code' => 'BZ'),
			array('country_name' => 'Benin', 'alpha2_code' => 'BJ'),
			array('country_name' => 'Bermuda', 'alpha2_code' => 'BM'),
			array('country_name' => 'Bhutan', 'alpha2_code' => 'BT'),
			array('country_name' => 'Bolivia (Plurinational State of)', 'alpha2_code' => 'BO'),
			array('country_name' => 'Bonaire, Sint Eustatius and Saba', 'alpha2_code' => 'BQ'),
			array('country_name' => 'Bosnia and Herzegovina', 'alpha2_code' => 'BA'),
			array('country_name' => 'Botswana', 'alpha2_code' => 'BW'),
			array('country_name' => 'Bouvet Island', 'alpha2_code' => 'BV'),
			array('country_name' => 'Brazil', 'alpha2_code' => 'BR'),
			array('country_name' => 'British Indian Ocean Territory', 'alpha2_code' => 'IO'),
			array('country_name' => 'Brunei Darussalam', 'alpha2_code' => 'BN'),
			array('country_name' => 'Bulgaria', 'alpha2_code' => 'BG'),
			array('country_name' => 'Burkina Faso', 'alpha2_code' => 'BF'),
			array('country_name' => 'Burundi', 'alpha2_code' => 'BI'),
			array('country_name' => 'Cabo Verde', 'alpha2_code' => 'CV'),
			array('country_name' => 'Cambodia', 'alpha2_code' => 'KH'),
			array('country_name' => 'Cameroon', 'alpha2_code' => 'CM'),
			array('country_name' => 'Canada', 'alpha2_code' => 'CA'),
			array('country_name' => 'Cayman Islands', 'alpha2_code' => 'KY'),
			array('country_name' => 'Central African Republic', 'alpha2_code' => 'CF'),
			array('country_name' => 'Chad', 'alpha2_code' => 'TD'),
			array('country_name' => 'Chile', 'alpha2_code' => 'CL'),
			array('country_name' => 'China', 'alpha2_code' => 'CN'),
			array('country_name' => 'Christmas Island', 'alpha2_code' => 'CX'),
			array('country_name' => 'Cocos (Keeling) Islands', 'alpha2_code' => 'CC'),
			array('country_name' => 'Colombia', 'alpha2_code' => 'CO'),
			array('country_name' => 'Comoros', 'alpha2_code' => 'KM'),
			array('country_name' => 'Congo', 'alpha2_code' => 'CG'),
			array('country_name' => 'Congo (Democratic Republic of the)', 'alpha2_code' => 'CD'),
			array('country_name' => 'Cook Islands', 'alpha2_code' => 'CK'),
			array('country_name' => 'Costa Rica', 'alpha2_code' => 'CR'),
			array('country_name' => 'Côte d\'Ivoire', 'alpha2_code' => 'CI'),
			array('country_name' => 'Croatia', 'alpha2_code' => 'HR'),
			array('country_name' => 'Cuba', 'alpha2_code' => 'CU'),
			array('country_name' => 'Curaçao', 'alpha2_code' => 'CW'),
			array('country_name' => 'Cyprus', 'alpha2_code' => 'CY'),
			array('country_name' => 'Czech Republic', 'alpha2_code' => 'CZ'),
			array('country_name' => 'Denmark', 'alpha2_code' => 'DK'),
			array('country_name' => 'Djibouti', 'alpha2_code' => 'DJ'),
			array('country_name' => 'Dominica', 'alpha2_code' => 'DM'),
			array('country_name' => 'Dominican Republic', 'alpha2_code' => 'DO'),
			array('country_name' => 'Ecuador', 'alpha2_code' => 'EC'),
			array('country_name' => 'Egypt', 'alpha2_code' => 'EG'),
			array('country_name' => 'El Salvador', 'alpha2_code' => 'SV'),
			array('country_name' => 'Equatorial Guinea', 'alpha2_code' => 'GQ'),
			array('country_name' => 'Eritrea', 'alpha2_code' => 'ER'),
			array('country_name' => 'Estonia', 'alpha2_code' => 'EE'),
			array('country_name' => 'Ethiopia', 'alpha2_code' => 'ET'),
			array('country_name' => 'Falkland Islands (Malvinas)', 'alpha2_code' => 'FK'),
			array('country_name' => 'Faroe Islands', 'alpha2_code' => 'FO'),
			array('country_name' => 'Fiji', 'alpha2_code' => 'FJ'),
			array('country_name' => 'Finland', 'alpha2_code' => 'FI'),
			array('country_name' => 'France', 'alpha2_code' => 'FR'),
			array('country_name' => 'French Guiana', 'alpha2_code' => 'GF'),
			array('country_name' => 'French Polynesia', 'alpha2_code' => 'PF'),
			array('country_name' => 'French Southern Territories', 'alpha2_code' => 'TF'),
			array('country_name' => 'Gabon', 'alpha2_code' => 'GA'),
			array('country_name' => 'Gambia', 'alpha2_code' => 'GM'),
			array('country_name' => 'Georgia', 'alpha2_code' => 'GE'),
			array('country_name' => 'Germany', 'alpha2_code' => 'DE'),
			array('country_name' => 'Ghana', 'alpha2_code' => 'GH'),
			array('country_name' => 'Gibraltar', 'alpha2_code' => 'GI'),
			array('country_name' => 'Greece', 'alpha2_code' => 'GR'),
			array('country_name' => 'Greenland', 'alpha2_code' => 'GL'),
			array('country_name' => 'Grenada', 'alpha2_code' => 'GD'),
			array('country_name' => 'Guadeloupe', 'alpha2_code' => 'GP'),
			array('country_name' => 'Guam', 'alpha2_code' => 'GU'),
			array('country_name' => 'Guatemala', 'alpha2_code' => 'GT'),
			array('country_name' => 'Guernsey', 'alpha2_code' => 'GG'),
			array('country_name' => 'Guinea', 'alpha2_code' => 'GN'),
			array('country_name' => 'Guinea-Bissau', 'alpha2_code' => 'GW'),
			array('country_name' => 'Guyana', 'alpha2_code' => 'GY'),
			array('country_name' => 'Haiti', 'alpha2_code' => 'HT'),
			array('country_name' => 'Heard Island and McDonald Islands', 'alpha2_code' => 'HM'),
			array('country_name' => 'Holy See', 'alpha2_code' => 'VA'),
			array('country_name' => 'Honduras', 'alpha2_code' => 'HN'),
			array('country_name' => 'Hong Kong', 'alpha2_code' => 'HK'),
			array('country_name' => 'Hungary', 'alpha2_code' => 'HU'),
			array('country_name' => 'Iceland', 'alpha2_code' => 'IS'),
			array('country_name' => 'India', 'alpha2_code' => 'IN'),
			array('country_name' => 'Indonesia', 'alpha2_code' => 'ID'),
			array('country_name' => 'Iran (Islamic Republic of)', 'alpha2_code' => 'IR'),
			array('country_name' => 'Iraq', 'alpha2_code' => 'IQ'),
			array('country_name' => 'Ireland', 'alpha2_code' => 'IE'),
			array('country_name' => 'Isle of Man', 'alpha2_code' => 'IM'),
			array('country_name' => 'Israel', 'alpha2_code' => 'IL'),
			array('country_name' => 'Italy', 'alpha2_code' => 'IT'),
			array('country_name' => 'Jamaica', 'alpha2_code' => 'JM'),
			array('country_name' => 'Japan', 'alpha2_code' => 'JP'),
			array('country_name' => 'Jersey', 'alpha2_code' => 'JE'),
			array('country_name' => 'Jordan', 'alpha2_code' => 'JO'),
			array('country_name' => 'Kazakhstan', 'alpha2_code' => 'KZ'),
			array('country_name' => 'Kenya', 'alpha2_code' => 'KE'),
			array('country_name' => 'Kiribati', 'alpha2_code' => 'KI'),
			array('country_name' => 'Korea (Democratic People\'s Republic of)', 'alpha2_code' => 'KP'),
			array('country_name' => 'Korea (Republic of)', 'alpha2_code' => 'KR'),
			array('country_name' => 'Kuwait', 'alpha2_code' => 'KW'),
			array('country_name' => 'Kyrgyzstan', 'alpha2_code' => 'KG'),
			array('country_name' => 'Lao People\'s Democratic Republic', 'alpha2_code' => 'LA'),
			array('country_name' => 'Latvia', 'alpha2_code' => 'LV'),
			array('country_name' => 'Lebanon', 'alpha2_code' => 'LB'),
			array('country_name' => 'Lesotho', 'alpha2_code' => 'LS'),
			array('country_name' => 'Liberia', 'alpha2_code' => 'LR'),
			array('country_name' => 'Libya', 'alpha2_code' => 'LY'),
			array('country_name' => 'Liechtenstein', 'alpha2_code' => 'LI'),
			array('country_name' => 'Lithuania', 'alpha2_code' => 'LT'),
			array('country_name' => 'Luxembourg', 'alpha2_code' => 'LU'),
			array('country_name' => 'Macao', 'alpha2_code' => 'MO'),
			array('country_name' => 'Macedonia (the former Yugoslav Republic of)', 'alpha2_code' => 'MK'),
			array('country_name' => 'Madagascar', 'alpha2_code' => 'MG'),
			array('country_name' => 'Malawi', 'alpha2_code' => 'MW'),
			array('country_name' => 'Malaysia', 'alpha2_code' => 'MY'),
			array('country_name' => 'Maldives', 'alpha2_code' => 'MV'),
			array('country_name' => 'Mali', 'alpha2_code' => 'ML'),
			array('country_name' => 'Malta', 'alpha2_code' => 'MT'),
			array('country_name' => 'Marshall Islands', 'alpha2_code' => 'MH'),
			array('country_name' => 'Martinique', 'alpha2_code' => 'MQ'),
			array('country_name' => 'Mauritania', 'alpha2_code' => 'MR'),
			array('country_name' => 'Mauritius', 'alpha2_code' => 'MU'),
			array('country_name' => 'Mayotte', 'alpha2_code' => 'YT'),
			array('country_name' => 'Mexico', 'alpha2_code' => 'MX'),
			array('country_name' => 'Micronesia (Federated States of)', 'alpha2_code' => 'FM'),
			array('country_name' => 'Moldova (Republic of)', 'alpha2_code' => 'MD'),
			array('country_name' => 'Monaco', 'alpha2_code' => 'MC'),
			array('country_name' => 'Mongolia', 'alpha2_code' => 'MN'),
			array('country_name' => 'Montenegro', 'alpha2_code' => 'ME'),
			array('country_name' => 'Montserrat', 'alpha2_code' => 'MS'),
			array('country_name' => 'Morocco', 'alpha2_code' => 'MA'),
			array('country_name' => 'Mozambique', 'alpha2_code' => 'MZ'),
			array('country_name' => 'Myanmar', 'alpha2_code' => 'MM'),
			array('country_name' => 'Namibia', 'alpha2_code' => 'NA'),
			array('country_name' => 'Nauru', 'alpha2_code' => 'NR'),
			array('country_name' => 'Nepal', 'alpha2_code' => 'NP'),
			array('country_name' => 'Netherlands', 'alpha2_code' => 'NL'),
			array('country_name' => 'New Caledonia', 'alpha2_code' => 'NC'),
			array('country_name' => 'New Zealand', 'alpha2_code' => 'NZ'),
			array('country_name' => 'Nicaragua', 'alpha2_code' => 'NI'),
			array('country_name' => 'Niger', 'alpha2_code' => 'NE'),
			array('country_name' => 'Nigeria', 'alpha2_code' => 'NG'),
			array('country_name' => 'Niue', 'alpha2_code' => 'NU'),
			array('country_name' => 'Norfolk Island', 'alpha2_code' => 'NF'),
			array('country_name' => 'Northern Mariana Islands', 'alpha2_code' => 'MP'),
			array('country_name' => 'Norway', 'alpha2_code' => 'NO'),
			array('country_name' => 'Oman', 'alpha2_code' => 'OM'),
			array('country_name' => 'Pakistan', 'alpha2_code' => 'PK'),
			array('country_name' => 'Palau', 'alpha2_code' => 'PW'),
			array('country_name' => 'Palestine, State of', 'alpha2_code' => 'PS'),
			array('country_name' => 'Panama', 'alpha2_code' => 'PA'),
			array('country_name' => 'Papua New Guinea', 'alpha2_code' => 'PG'),
			array('country_name' => 'Paraguay', 'alpha2_code' => 'PY'),
			array('country_name' => 'Peru', 'alpha2_code' => 'PE'),
			array('country_name' => 'Philippines', 'alpha2_code' => 'PH'),
			array('country_name' => 'Pitcairn', 'alpha2_code' => 'PN'),
			array('country_name' => 'Poland', 'alpha2_code' => 'PL'),
			array('country_name' => 'Portugal', 'alpha2_code' => 'PT'),
			array('country_name' => 'Puerto Rico', 'alpha2_code' => 'PR'),
			array('country_name' => 'Qatar', 'alpha2_code' => 'QA'),
			array('country_name' => 'Réunion', 'alpha2_code' => 'RE'),
			array('country_name' => 'Romania', 'alpha2_code' => 'RO'),
			array('country_name' => 'Russian Federation', 'alpha2_code' => 'RU'),
			array('country_name' => 'Rwanda', 'alpha2_code' => 'RW'),
			array('country_name' => 'Saint Barthélemy', 'alpha2_code' => 'BL'),
			array('country_name' => 'Saint Helena, Ascension and Tristan da Cunha', 'alpha2_code' => 'SH'),
			array('country_name' => 'Saint Kitts and Nevis', 'alpha2_code' => 'KN'),
			array('country_name' => 'Saint Lucia', 'alpha2_code' => 'LC'),
			array('country_name' => 'Saint Martin (French part)', 'alpha2_code' => 'MF'),
			array('country_name' => 'Saint Pierre and Miquelon', 'alpha2_code' => 'PM'),
			array('country_name' => 'Saint Vincent and the Grenadines', 'alpha2_code' => 'VC'),
			array('country_name' => 'Samoa', 'alpha2_code' => 'WS'),
			array('country_name' => 'San Marino', 'alpha2_code' => 'SM'),
			array('country_name' => 'Sao Tome and Principe', 'alpha2_code' => 'ST'),
			array('country_name' => 'Saudi Arabia', 'alpha2_code' => 'SA'),
			array('country_name' => 'Senegal', 'alpha2_code' => 'SN'),
			array('country_name' => 'Serbia', 'alpha2_code' => 'RS'),
			array('country_name' => 'Seychelles', 'alpha2_code' => 'SC'),
			array('country_name' => 'Sierra Leone', 'alpha2_code' => 'SL'),
			array('country_name' => 'Singapore', 'alpha2_code' => 'SG'),
			array('country_name' => 'Sint Maarten (Dutch part)', 'alpha2_code' => 'SX'),
			array('country_name' => 'Slovakia', 'alpha2_code' => 'SK'),
			array('country_name' => 'Slovenia', 'alpha2_code' => 'SI'),
			array('country_name' => 'Solomon Islands', 'alpha2_code' => 'SB'),
			array('country_name' => 'Somalia', 'alpha2_code' => 'SO'),
			array('country_name' => 'South Africa', 'alpha2_code' => 'ZA'),
			array('country_name' => 'South Georgia and the South Sandwich Islands', 'alpha2_code' => 'GS'),
			array('country_name' => 'South Sudan', 'alpha2_code' => 'SS'),
			array('country_name' => 'Spain', 'alpha2_code' => 'ES'),
			array('country_name' => 'Sri Lanka', 'alpha2_code' => 'LK'),
			array('country_name' => 'Sudan', 'alpha2_code' => 'SD'),
			array('country_name' => 'Suriname', 'alpha2_code' => 'SR'),
			array('country_name' => 'Svalbard and Jan Mayen', 'alpha2_code' => 'SJ'),
			array('country_name' => 'Swaziland', 'alpha2_code' => 'SZ'),
			array('country_name' => 'Sweden', 'alpha2_code' => 'SE'),
			array('country_name' => 'Switzerland', 'alpha2_code' => 'CH'),
			array('country_name' => 'Syrian Arab Republic', 'alpha2_code' => 'SY'),
			array('country_name' => 'Taiwan, Province of China', 'alpha2_code' => 'TW'),
			array('country_name' => 'Tajikistan', 'alpha2_code' => 'TJ'),
			array('country_name' => 'Tanzania, United Republic of', 'alpha2_code' => 'TZ'),
			array('country_name' => 'Thailand', 'alpha2_code' => 'TH'),
			array('country_name' => 'Timor-Leste', 'alpha2_code' => 'TL'),
			array('country_name' => 'Togo', 'alpha2_code' => 'TG'),
			array('country_name' => 'Tokelau', 'alpha2_code' => 'TK'),
			array('country_name' => 'Tonga', 'alpha2_code' => 'TO'),
			array('country_name' => 'Trinidad and Tobago', 'alpha2_code' => 'TT'),
			array('country_name' => 'Tunisia', 'alpha2_code' => 'TN'),
			array('country_name' => 'Turkey', 'alpha2_code' => 'TR'),
			array('country_name' => 'Turkmenistan', 'alpha2_code' => 'TM'),
			array('country_name' => 'Turks and Caicos Islands', 'alpha2_code' => 'TC'),
			array('country_name' => 'Tuvalu', 'alpha2_code' => 'TV'),
			array('country_name' => 'Uganda', 'alpha2_code' => 'UG'),
			array('country_name' => 'Ukraine', 'alpha2_code' => 'UA'),
			array('country_name' => 'United Arab Emirates', 'alpha2_code' => 'AE'),
			array('country_name' => 'United Kingdom of Great Britain and Northern Ireland', 'alpha2_code' => 'GB'),
			array('country_name' => 'United States Minor Outlying Islands', 'alpha2_code' => 'UM'),
			array('country_name' => 'United States of America', 'alpha2_code' => 'US'),
			array('country_name' => 'Uruguay', 'alpha2_code' => 'UY'),
			array('country_name' => 'Uzbekistan', 'alpha2_code' => 'UZ'),
			array('country_name' => 'Vanuatu', 'alpha2_code' => 'VU'),
			array('country_name' => 'Venezuela (Bolivarian Republic of)', 'alpha2_code' => 'VE'),
			array('country_name' => 'Viet Nam', 'alpha2_code' => 'VN'),
			array('country_name' => 'Virgin Islands (British)', 'alpha2_code' => 'VG'),
			array('country_name' => 'Virgin Islands (U.S.)', 'alpha2_code' => 'VI'),
			array('country_name' => 'Wallis and Futuna', 'alpha2_code' => 'WF'),
			array('country_name' => 'Western Sahara', 'alpha2_code' => 'EH'),
			array('country_name' => 'Yemen', 'alpha2_code' => 'YE'),
			array('country_name' => 'Zambia', 'alpha2_code' => 'ZM'),
			array('country_name' => 'Zimbabwe', 'alpha2_code' => 'ZW'),
			array('country_name' => 'Unknown Region', 'alpha2_code' => 'UR')
		);

		$key = array_search(strtoupper($alpha2_code), array_column($countries, 'alpha2_code'));

		$final_country_name = "";
		if (isset($countries[$key])) {
			$final_country_name = $countries[$key]['country_name'];
		}
		return $final_country_name;
	}

	public function get_random_string($length = 10)
	{
		$alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
		$token = "";
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < $length; $i++) {
			$n = rand(0, $alphaLength);
			$token .= $alphabet[$n];
		}
		return $token;
	}

	function plog($data, $is_continue = 0)
	{
		echo "<pre>";
		print_r($data);
		echo "</pre>";

		if (!$is_continue) {
			exit();
		}
	}

	function indian_number_format($value)
	{
		if ($value) {
			return preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $value);
		} else {
			return '0';
		}
	}

	function abs($value)
	{
		return ltrim($value, '-');
	}

	function get_currency_symbol($currency_code)
	{
		if ($currency_code) {
			$currency_code = strtoupper($currency_code);
		} else {
			$currency_code = strtoupper("USD");
		}

		$currencies = array(
			"USD" => "$",
			"CAD" => "CA$",
			"EUR" => "€",
			"AED" => "AED",
			"AFN" => "Af",
			"ALL" => "ALL",
			"AMD" => "AMD",
			"ARS" => "AR$",
			"AUD" => "AU$",
			"AZN" => "man.",
			"BAM" => "KM",
			"BDT" => "Tk",
			"BGN" => "BGN",
			"BHD" => "BD",
			"BIF" => "FBu",
			"BND" => "BN$",
			"BOB" => "Bs",
			"BRL" => "R$",
			"BWP" => "BWP",
			"BYN" => "Br",
			"BZD" => "BZ$",
			"CDF" => "CDF",
			"CHF" => "CHF",
			"CLP" => "CL$",
			"CNY" => "CN¥",
			"COP" => "CO$",
			"CRC" => "₡",
			"CVE" => "CV$",
			"CZK" => "Kč",
			"DJF" => "Fdj",
			"DKK" => "Dkr",
			"DOP" => "RD$",
			"DZD" => "DA",
			"EEK" => "Ekr",
			"EGP" => "EGP",
			"ERN" => "Nfk",
			"ETB" => "Br",
			"GBP" => "£",
			"GEL" => "GEL",
			"GHS" => "GH₵",
			"GNF" => "FG",
			"GTQ" => "GTQ",
			"HKD" => "HK$",
			"HNL" => "HNL",
			"HRK" => "kn",
			"HUF" => "Ft",
			"IDR" => "Rp",
			"ILS" => "₪",
			"INR" => "₹",
			"IQD" => "IQD",
			"IRR" => "IRR",
			"ISK" => "Ikr",
			"JMD" => "J$",
			"JOD" => "JD",
			"JPY" => "¥",
			"KES" => "Ksh",
			"KHR" => "KHR",
			"KMF" => "CF",
			"KRW" => "₩",
			"KWD" => "KD",
			"KZT" => "KZT",
			"LBP" => "L.L.",
			"LKR" => "SLRs",
			"LTL" => "Lt",
			"LVL" => "Ls",
			"LYD" => "LD",
			"MAD" => "MAD",
			"MDL" => "MDL",
			"MGA" => "MGA",
			"MKD" => "MKD",
			"MMK" => "MMK",
			"MOP" => "MOP$",
			"MUR" => "MURs",
			"MXN" => "MX$",
			"MYR" => "RM",
			"MZN" => "MTn",
			"NAD" => "N$",
			"NGN" => "₦",
			"NIO" => "C$",
			"NOK" => "Nkr",
			"NPR" => "NPRs",
			"NZD" => "NZ$",
			"OMR" => "OMR",
			"PAB" => "B/.",
			"PEN" => "S/.",
			"PHP" => "₱",
			"PKR" => "PKRs",
			"PLN" => "zł",
			"PYG" => "₲",
			"QAR" => "QR",
			"RON" => "RON",
			"RSD" => "din.",
			"RUB" => "RUB",
			"RWF" => "RWF",
			"SAR" => "SR",
			"SDG" => "SDG",
			"SEK" => "Skr",
			"SGD" => "S$",
			"SOS" => "Ssh",
			"SYP" => "SY£",
			"THB" => "฿",
			"TND" => "DT",
			"TOP" => "T$",
			"TRY" => "TL",
			"TTD" => "TT$",
			"TWD" => "NT$",
			"TZS" => "TSh",
			"UAH" => "₴",
			"UGX" => "USh",
			"UYU" => '$U',
			"UZS" => "UZS",
			"VEF" => "Bs.F.",
			"VND" => "₫",
			"XAF" => "FCFA",
			"XOF" => "CFA",
			"YER" => "YR",
			"ZAR" => "R",
			"ZMK" => "ZK",
			"ZWL" => "ZWL$"
		);

		return isset($currencies[$currency_code]) ? $currencies[$currency_code] : "$";
	}

	function live_android_app_info($app_package_name)
	{

		$url = JKAPPS_NODEJS_URL . "app-info?country=us&app_package_name=" . $app_package_name;
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($curl_handle);
		$err = curl_error($curl_handle);
		curl_close($curl_handle);

		if ($err) {
			$resp = array(
				'response' => $response,
				'error' => $err
			);
		} else {

			$app_info_response_data = json_decode($response);

			if (isset($app_info_response_data->status_code) && $app_info_response_data->status_code == 0) {
				// App not found in 'us', now check in 'in'

				$url2 = JKAPPS_NODEJS_URL . "app-info?country=in&app_package_name=" . $app_package_name;
				$curl_handle2 = curl_init();
				curl_setopt($curl_handle2, CURLOPT_URL, $url2);
				curl_setopt($curl_handle2, CURLOPT_RETURNTRANSFER, true);
				$response2 = curl_exec($curl_handle2);
				$err2 = curl_error($curl_handle2);
				curl_close($curl_handle2);

				$resp = array(
					'response' => $response2,
					'error' => $err2
				);
			} else {
				$resp = array(
					'response' => $response,
					'error' => $err
				);
			}
		}

		return $resp;
	}

	function live_ios_app_info($package)
	{

		$update = array(
			'is_active' => 0
		);

		$content = file_get_contents("http://itunes.apple.com/lookup?country=us&id=" . (str_replace('id', '', $package)));

		if (!empty($content)) {

			$result = json_decode(trim($content));

			// check 'in' country
			if (!isset($result->results) || empty($result->results)) {

				$content = file_get_contents("http://itunes.apple.com/lookup?country=in&id=" . (str_replace('id', '', $package)));

				if (!empty($content)) {
					$result = json_decode(trim($content));
				}
			}

			if (isset($result->results) && !empty($result->results)) {

				$update['is_active'] = 1;

				$update['app_name'] = $result->results[0]->trackName;
				$update['app_icon'] = $result->results[0]->artworkUrl512;
				$update['app_developer_id'] = $result->results[0]->artistId;
				$update['dev_console_name'] = $result->results[0]->artistName;
				$update['app_latest_version_name'] = $result->results[0]->version;
				$update['app_release_date'] = $result->results[0]->releaseDate;
				$update['app_live_updated'] = $result->results[0]->currentVersionReleaseDate;

				if (isset($result->results[0]->userRatingCount)) {
					$update['app_ratings'] = $result->results[0]->userRatingCount;
				}

				if (isset($result->results[0]->averageUserRating)) {
					$update['app_scoreText'] = number_format($result->results[0]->averageUserRating, 1);
				}
			}
		}

		return $update;
	}

	function cors_header()
	{
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
		header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
	}

	function mail_report($custom_data)
	{

		$current_time = date('H:i');

		if (($current_time >= '05:30')) {

			if (
				(isset($custom_data['access_token']) && !empty($custom_data['access_token'])) &&
				(isset($custom_data['app_id']) && !empty($custom_data['app_id'])) &&
				(isset($custom_data['mail_setting']['from']['email_id']) && !empty($custom_data['mail_setting']['from']['email_id'])) &&
				(isset($custom_data['mail_setting']['to']) && !empty($custom_data['mail_setting']['to']))
			) {

				// [Changable Variables]
				$access_token = $custom_data['access_token'];
				$app_id = $custom_data['app_id'];
				$adUnitdisplayLabel_array = $custom_data['ad_unit_display_lables'];

				$from_email = $custom_data['mail_setting']['from']['email_id'];
				$from_email_name = isset($custom_data['mail_setting']['from']['name']) ? $custom_data['mail_setting']['from']['name'] : "Admob Report";

				// $to_email  = $custom_data['mail_setting']['to']['email_id'];
				// $to_email_name  = isset($custom_data['mail_setting']['to']['name']) ? $custom_data['mail_setting']['to']['name'] : "Admob Report";

				$cc_email = isset($custom_data['mail_setting']['cc']['email_id']) ? $custom_data['mail_setting']['cc']['email_id'] : "";
				$cc_email_name = isset($custom_data['mail_setting']['cc']['name']) ? $custom_data['mail_setting']['cc']['name'] : "";
				// [/Changable Variables]

				$app_id_parts = explode("~", $app_id);
				if (isset($app_id_parts[0])) {
					$app_id_parts = explode("-", $app_id_parts[0]);
					if (isset($app_id_parts[3])) {
						$admob_pub_id = $app_id_parts[2] . "-" . $app_id_parts[3];
					}
				}

				$client = new Google_Client();

				$client->addScope('https://www.googleapis.com/auth/admob.readonly');
				$client->setApplicationName('JK Admob');

				$client->setAccessType('offline');

				$client->setApprovalPrompt('force');
				$client->setIncludeGrantedScopes(true);

				$client->setAuthConfig('application/third_party/client_secrets.json');

				$service = new Google_Service_AdMob($client);

				$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
				$redirectUri = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
				$client->setRedirectUri($redirectUri);

				$tokenValid = 0;
				$getAccessToken = array();

				$client->setAccessToken(trim($access_token));
				if ($client->getAccessToken()) {
					$getAccessToken = $client->getAccessToken();

					if ($client->isAccessTokenExpired()) {
						if ($client->getRefreshToken()) {
							$authData = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
							if (array_key_exists('error', $authData)) {
								// throw new Exception(join(', ', $authData));
								$tokenValid = 0;
							} else {
								$tokenValid = 1;
							}
						} else {
							$tokenValid = 0;
						}
					} else {
						$tokenValid = 1;
					}
				} else {
					$tokenValid = 0;
				}

				if ($tokenValid == 1 && !empty($getAccessToken)) {

					// Get list of accounts.
					$result = $service->accounts->listAccounts();
					$accounts = $result->account;

					// Return first account name.
					$accountName = $accounts[0]['name'];

					if (isset($accountName)) {

						// $startDate = $this->toDate(getdate(strtotime(date('Y-m-01'))));
						// $endDate = $this->today();

						switch ($custom_data['report_range_type']) {
							case 1: // Today
								$startDate = $this->today();
								$endDate = $this->today();
								break;
							case 2: // Yesterday
								$startDate = $this->yesterday();
								$endDate = $this->yesterday();
								break;
							case 3: // Last 7 Days
								$startDate = $this->sevenDaysBeforeToday();
								$endDate = $this->yesterday();
								break;
							case 4: // Last 30 Days
								$startDate = $this->thirtyDaysBeforeToday();
								$endDate = $this->yesterday();
								break;
							case 5: // Current Month
								$startDate = $this->firstDateOfCurrentMonth();
								$endDate = $this->yesterday();
								break;
							default:
								$startDate = $this->yesterday();
								$endDate = $this->yesterday();
						}

						// Specify date range.
						$dateRange = new \Google_Service_AdMob_DateRange();
						$dateRange->setStartDate($startDate);
						$dateRange->setEndDate($endDate);

						// Specify dimension filters.
						$apps = new \Google_Service_AdMob_StringList();
						$apps->setValues([$app_id]);
						$dimensionFilterMatches = new \Google_Service_AdMob_MediationReportSpecDimensionFilter();
						$dimensionFilterMatches->setDimension('APP');
						$dimensionFilterMatches->setMatchesAny($apps);

						// Create network report specification.
						$dimensions = ['APP', 'COUNTRY', 'AD_UNIT', 'DATE'];
						$metrics = ['ESTIMATED_EARNINGS', 'IMPRESSION_RPM', 'MATCHED_REQUESTS', 'SHOW_RATE', 'IMPRESSIONS', 'IMPRESSION_CTR', 'CLICKS'];  // IMPRESSION_RPM - for OBSERVED_ECPM as (OBSERVED_ECPM) metric not working in network report
						if (!in_array('AD_TYPE', $dimensions)) {
							array_push($metrics, 'AD_REQUESTS', 'MATCH_RATE');
						}
						$reportSpec = new \Google_Service_AdMob_NetworkReportSpec();
						$reportSpec->setMetrics($metrics);
						$reportSpec->setDimensions($dimensions);
						$reportSpec->setDateRange($dateRange);
						$reportSpec->setDimensionFilters($dimensionFilterMatches);

						// Create network report request.
						$networkReportRequest = new \Google_Service_AdMob_GenerateNetworkReportRequest();
						$networkReportRequest->setReportSpec($reportSpec);

						$networkReportResponse = $service->accounts_networkReport->generate(
							$accountName,
							$networkReportRequest
						);

						// Convert network report response to a simple object.
						$networkReportResponse = $networkReportResponse->tosimpleObject();

						$finalData = array();

						// Print each record in the report.
						if (!empty($networkReportResponse)) {
							$currencyCode = "USD";

							foreach ($networkReportResponse as $record) {
								// printf("'%s' \n", json_encode($record));

								if (isset($record['row'])) {

									foreach ($record as $row) {
										$reportData = array();

										$dimension = $row['dimensionValues'];
										$metric = $row['metricValues'];

										if (isset($dimension['APP']['value']) && isset($dimension['AD_UNIT']['value']) && isset($dimension['DATE']['value']) && $dimension['DATE']['value'] != '') {

											$adAppId = $dimension['APP']['value'];
											$adUnitdisplayLabel = $dimension['AD_UNIT']['displayLabel'];

											if (in_array($adUnitdisplayLabel, $adUnitdisplayLabel_array)) {

												$adUnitId = $dimension['AD_UNIT']['value'];
												$reportDate = $dimension['DATE']['value'];
												$countryName = (isset($dimension['COUNTRY']['value']) ? $dimension['COUNTRY']['value'] : 'UR');

												$reportData['app_name'] = $dimension['APP']['displayLabel'];
												$reportData['ad_type'] = $adUnitdisplayLabel;
												$reportData['report_country'] = $this->get_country_name($countryName);

												$dateFormat = substr($reportDate, 0, 4) . '-' . substr($reportDate, 4, 2) . '-' . substr($reportDate, 6, 7);
												$reportData['report_date'] = $dateFormat;

												$est_earn = 0;
												if (isset($metric['ESTIMATED_EARNINGS']['microsValue'])) {
													$est_earn = $this->microValueConvert($metric['ESTIMATED_EARNINGS']['microsValue'], $currencyCode);
												}
												$reportData['report_estimate_earnings'] = number_format($est_earn, 2);

												$reportData['report_observed_ecpm'] = (isset($metric['IMPRESSION_RPM']['doubleValue']) ? number_format($metric['IMPRESSION_RPM']['doubleValue'], 2) : 0);
												$reportData['report_ad_request'] = (isset($metric['AD_REQUESTS']['integerValue']) ? $metric['AD_REQUESTS']['integerValue'] : 0);

												$matchRate = 0;
												$isMatchRate = 0;
												if (isset($metric['MATCH_RATE']['doubleValue'])) {
													$matchRate = ($metric['MATCH_RATE']['doubleValue'] * 100);
													$isMatchRate = 1;
												}
												$reportData['report_match_rate'] = ($isMatchRate == 1 ? number_format($matchRate, 2) : "0.00") . "%";

												$reportData['report_matched_request'] = (isset($metric['MATCHED_REQUESTS']['integerValue']) ? $metric['MATCHED_REQUESTS']['integerValue'] : 0);

												$showRate = 0;
												$isShowRate = 0;
												if (isset($metric['SHOW_RATE']['doubleValue'])) {
													$showRate = ($metric['SHOW_RATE']['doubleValue'] * 100);
													$isShowRate = 1;
												}
												$reportData['report_show_rate'] = ($isShowRate == 1 ? number_format($showRate, 2) : "0.00") . "%";

												$reportData['report_impression'] = (isset($metric['IMPRESSIONS']['integerValue']) ? $metric['IMPRESSIONS']['integerValue'] : 0);

												$ctrValue = 0;
												$isCTR = 0;
												if (isset($metric['IMPRESSION_CTR']['doubleValue'])) {
													$ctrValue = ($metric['IMPRESSION_CTR']['doubleValue'] * 100);
													$isCTR = 1;
												}
												$reportData['report_ctr'] = ($isCTR == 1 ? number_format($ctrValue, 2) : "0.00") . "%";

												$reportData['report_clicks'] = (isset($metric['CLICKS']['integerValue']) ? $metric['CLICKS']['integerValue'] : 0);

												$finalData[] = $reportData;
											}
										}
									}
								}
							}
						}

						if (count($finalData) > 0) {

							$reportHeader = array(
								'App', 'Ad Unit', 'Country', 'Date', 'Est. earnings (USD)', 'Observed eCPM (USD)', 'Requests', 'Matched Rate (%)', 'Matched Requests', 'Show Rate (%)',
								'Impressions', 'CTR (%)', 'Clicks'
							);
							array_unshift($finalData, $reportHeader);
							$fileName = "admob-report-" . rand(11111, 99999) . ".csv";

							$file = fopen("uploads/$fileName", 'w');
							foreach ($finalData as $value) {
								fputcsv($file, $value);
							}
							fclose($file);

							$base64_content = base64_encode(file_get_contents("uploads/$fileName"));

							$email_subject = "Admob Report of Publisher $admob_pub_id";
							$filename_for_mail = "Report_$admob_pub_id" . ".csv";
							$html_part = "<h3>Admob report </h3>";

							$cc_email_json_string = '';
							if ((isset($cc_email) && !empty($cc_email)) && (isset($cc_email_name) && !empty($cc_email_name))) {
								$cc_email_json_string = ',"Cc": [{"Email": "' . $cc_email . '","Name": "' . $cc_email_name . '"}]';
							}

							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, 'https://api.mailjet.com/v3.1/send');
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
							curl_setopt($ch, CURLOPT_HTTPHEADER, [
								'Content-Type: application/json',
							]);
							curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
							curl_setopt($ch, CURLOPT_USERPWD, '5b49a0ae4c59e4dfae5179bb89d1bf1a:d215e12f4a00ae428b820e792abe01f6');
							curl_setopt($ch, CURLOPT_POSTFIELDS, '{"Messages":[{"From":{"Email": "' . $from_email . '","Name": "' . $from_email_name . '"},"To": ' . json_encode($custom_data['mail_setting']['to']) . $cc_email_json_string . ',"Subject": "' . $email_subject . '","TextPart": "","HTMLPart": "' . $html_part . '","Attachments":[{"ContentType": "text/csv","Filename": "' . $filename_for_mail . '","Base64Content": "' . $base64_content . '"}]}]}');
							$response = curl_exec($ch);
							curl_close($ch);

							@unlink('uploads/' . $fileName);

							$res_return = array(
								'status_code' => 1,
								'msg' => $response
							);
						} else {
							// echo "No data available";

							$res_return = array(
								'status_code' => 1,
								'msg' => 'No data available'
							);
						}
					} else {
						// echo 'Please specify the account_name, which should follow a format of
						// "accounts/pub-XXXXXXXXXXXXXXXX".
						// See https://support.google.com/admob/answer/2784578
						// Sfor instructions on how to find your account name.';

						$res_return = array(
							'status_code' => 0,
							'msg' => 'Please specify the account_name, which should follow a format of
							"accounts/pub-XXXXXXXXXXXXXXXX".
							See https://support.google.com/admob/answer/2784578
							Sfor instructions on how to find your account name.'
						);
					}
				} else {

					// echo "Invalid Token";

					$res_return = array(
						'status_code' => 0,
						'msg' => 'Invalid Token'
					);
				}
			} else {
				// echo "Requierd parameters are missing....";

				$res_return = array(
					'status_code' => 0,
					'msg' => 'Requierd parameters are missing....'
				);
			}
		} else {
			// echo "Not running at this time.";
			$res_return = array(
				'status_code' => 2,
				'msg' => 'Not running at this time'
			);
		}

		return $res_return;
	}

	function cal_percentage($old_digit, $difference_digit)
	{

		if ($difference_digit != 0) {
			$count = ($difference_digit * 100) / ($old_digit <= 0 ? 1 : $old_digit);
			return number_format($count, 2);
		} else {
			return 0.00;
		}
	}

	function truncate($string, $length, $dots = "...")
	{
		return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
	}

	function get_short_id_by_admob_approval_state($state)
	{
		$state = strtoupper($state);

		switch ($state) {
			case "APPROVED":
				$short_id = 1;
				break;
			case "IN_REVIEW":
				$short_id = 2;
				break;
			case "ACTION_REQUIRED":
				$short_id = 3;
				break;
			case "APP_APPROVAL_STATE_UNSPECIFIED":
				$short_id = 4;
				break;
			default:
				$short_id = 0;
		}

		return $short_id;
	}

	function get_app_approval_state_by_short_id($short_id)
	{
		switch ($short_id) {
			case "1":
				$state = "APPROVED";
				break;
			case "2":
				$state = "IN_REVIEW";
				break;
			case "3":
				$state = "ACTION_REQUIRED";
				break;
			case "4":
				$state = "APP_APPROVAL_STATE_UNSPECIFIED";
				break;
			default:
				$state = "";
		}

		return $state;
	}
}
