<?php
/* set our flash message */
function msg($message, $type) {
	$_SESSION['flash'] = array(
		'type' => $type,
		'message' => $message
	);
}

/* redirect to specified url */
function go($url = '') {
	header('Location: ' . $url);
	die();
}

/* grab the full url */
function url($url = '') {
	$host = $_SERVER['HTTP_HOST'];
	$host = !preg_match('/^http/', $host) ? 'http://' . $host : $host;
	$path = preg_replace('/\w+\.php/', '', $_SERVER['REQUEST_URI']);
	$path = preg_replace('/\?.*$/', '', $path);
	$path = !preg_match('/\/$/', $path) ? $path . '/' : $path;
	if ( preg_match('/http:/', $host) && is_ssl() ) {
		$host = preg_replace('/http:/', 'https:', $host);
	}
	if ( preg_match('/https:/', $host) && !is_ssl() ) {
		$host = preg_replace('/https:/', 'http:', $host);
	}
	return $host . $path . $url;
}

/* send an email using phpmailer */
function email($to, $file, $values, $subject) {
	global $config;
	// add config data to values array
	$values = array_merge($values, $config);
	// get email header
	$content = file_get_contents('templates/emails/layout/header.php');
	// get email content
	$content .= file_get_contents('templates/emails/' . $file . '.php');
	// get email footer
	$content .= file_get_contents('templates/emails/layout/footer.php');
	// inject values for placeholders
	foreach ( $values as $key => $value ) {
		$content = str_replace('{' . $key . '}', $value, $content);
	}
	// build email and send
	require_once 'lib/vendor/PHPMailer/PHPMailerAutoload.php';
	$mail = new PHPMailer;
	$mail->isMail();
	$mail->From = $config['email'];
	$mail->FromName = $config['name'];
	$mail->addAddress($to);
	$mail->isHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $content;
	$mail->send();
}

/* require a template file */
function template($path, $container = true) {
	global $csrf;
	require 'templates/' . $path . '.php';
}

/* check if connection is ssl or not */
function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

/* grab post data */
function post($key = null) {
	if ( is_null($key) ) {
		return $_POST;
	}
	$post = isset($_POST[$key]) ? $_POST[$key] : null;
	if ( is_string($post) ) {
		$post = trim($post);
	}
	return $post;
}

/* grab get data */
function get($key = null) {
	if ( is_null($key) ) {
		return $_GET;
	}
	$get = isset($_GET[$key]) ? $_GET[$key] : null;
	if ( is_string($get) ) {
		$get = trim($get);
	}
	return $get;
}

/* get the font awesome currency code */
function currencyCode() {
	global $config;
	switch ( $config['currency'] ) {
		case 'USD':
		case 'CAD':
		case 'AUD':
			return 'usd';
		break;
		case 'EUR':
			return 'eur';
		break;
		case 'GBP':
			return 'gbp';
		break;
	}
}

/* get the currency symbol */
function currencySymbol() {
	global $config;
	switch ( $config['currency'] ) {
		case 'USD':
		case 'CAD':
		case 'AUD':
			return '$';
		break;
		case 'EUR':
			return '&euro;';
		break;
		case 'GBP':
			return '&pound;';
		break;
	}
}

/* get the currency suffix if needed */
function currencySuffix() {
	global $config;
	switch ( $config['currency'] ) {
		case 'AUD':
			return '(AUD)';
		break;
		case 'CAD':
			return '(CAD)';
		break;
	}
}

/* format number with currency code */
function currency($amount) {
	return currencySymbol() . number_format($amount, 2, '.', ',');
}

/* array of countries */
function countries() {
	$countries = array( 'US' => 'United States', 'CA' => 'Canada', 'UK' => 'United Kingdom', 'AU' => 'Australia', 'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda', 'AR' => 'Argentina', 'AM' => 'Armenia', 'AW' => 'Aruba', 'AT' => 'Austria', 'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus', 'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BR' => 'Brazil', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China', 'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros', 'CG' => 'Congo', 'CD' => 'Congo, The Democratic Republic of the', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CI' => 'Cote D`Ivoire', 'HR' => 'Croatia', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark', 'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt', 'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'GA' => 'Gabon', 'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar', 'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana', 'HT' => 'Haiti', 'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia', 'IR' => 'Iran (Islamic Republic Of)', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KP' => 'Korea North', 'KR' => 'Korea South', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macau', 'MK' => 'Macedonia', 'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius', 'MX' => 'Mexico', 'FM' => 'Micronesia', 'MD' => 'Moldova', 'MC' => 'Monaco', 'MN' => 'Mongolia', 'MS' => 'Montserrat', 'MA' => 'Morocco', 'MZ' => 'Mozambique', 'NA' => 'Namibia', 'NP' => 'Nepal', 'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestine Autonomous', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru', 'PH' => 'Philippines', 'PL' => 'Poland', 'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation', 'RW' => 'Rwanda', 'VC' => 'Saint Vincent and the Grenadines', 'MP' => 'Saipan', 'SM' => 'San Marino', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovak Republic', 'SI' => 'Slovenia', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'ES' => 'Spain', 'LK' => 'Sri Lanka', 'KN' => 'St. Kitts/Nevis', 'LC' => 'St. Lucia', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria', 'TW' => 'Taiwan', 'TI' => 'Tajikistan', 'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo', 'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey', 'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam', 'VG' => 'Virgin Islands (British)', 'VI' => 'Virgin Islands (U.S.)', 'WF' => 'Wallis and Futuna Islands', 'YE' => 'Yemen', 'YU' => 'Yugoslavia', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe');
	return $countries;
}

/* array of states */
function states() {
	$states = array(
	      'US States' => array('AL' => 'Alabama','AK' => 'Alaska','AZ' => 'Arizona','AR' => 'Arkansas','BVI' => 'British Virgin Islands','CA' => 'California','CO' => 'Colorado','CT' => 'Connecticut','DE' => 'Delaware','FL' => 'Florida','GA' => 'Georgia','GU' => 'Guam','HI' => 'Hawaii','ID' => 'Idaho','IL' => 'Illinois','IN' => 'Indiana','IA' => 'Iowa','KS' => 'Kansas','KY' => 'Kentucky','LA' => 'Louisiana','ME' => 'Maine','MP' => 'Mariana Islands','MPI' => 'Mariana Islands (Pacific)','MD' => 'Maryland','MA' => 'Massachusetts','MI' => 'Michigan','MN' => 'Minnesota','MS' => 'Mississippi','MO' => 'Missouri','MT' => 'Montana','NE' => 'Nebraska','NV' => 'Nevada','NH' => 'New Hampshire','NJ' => 'New Jersey','NM' => 'New Mexico','NY' => 'New York','NC' => 'North Carolina','ND' => 'North Dakota','OH' => 'Ohio','OK' => 'Oklahoma','OR' => 'Oregon','PA' => 'Pennsylvania','PR' => 'Puerto Rico','RI' => 'Rhode Island','SC' => 'South Carolina','SD' => 'South Dakota','TN' => 'Tennessee','TX' => 'Texas','UT' => 'Utah','VT' => 'Vermont','USVI' => 'VI  U.S. Virgin Islands','VA' => 'Virginia','WA' => 'Washington','DC' => 'Washington, D.C.','WV' => 'West Virginia','WI' => 'Wisconsin','WY' => 'Wyoming',
	      ),
	      'Canadian Provinces' => array('AB' => 'Alberta','BC' => 'British Columbia','MB' => 'Manitoba','NB' => 'New Brunswick','NF' => 'Newfoundland','NT' => 'Northwest Territories','NS' => 'Nova Scotia','NVT' => 'Nunavut','ON' => 'Ontario','PE' => 'Prince Edward Island','QC' => 'Quebec','SK' => 'Saskatchewan','YK' => 'Yukon',
	      ),
	      'Australian Provinces' => array('AU-NSW' => 'New South Wales','AU-QLD' => 'Queensland','AU-SA' => 'South Australia','AU-TAS' => 'Tasmania','AU-VIC' => 'Victoria','AU-WA' => 'Western Australia','AU-ACT' => 'Australian Capital Territory','AU-NT' => 'Northern Territory',
	      ),
	);
	return $states;
}

/* debug tool */
function s($input) {
	$output = '<pre>';
	if ( is_array($input) || is_object($input) ) {
		$output .= print_r($input, true);
	} else {
		$output .= $input;
	}
	$output .= '</pre>';
	echo $output;
}

/* debug tool and die */
function sd($input) {
	die(s($input));
}