<?php defined('BASEPATH') or exit('No direct script access allowed');
class Migration_Tbl_country extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('tbl_country')) {
            $this->dbforge->add_field(array(
                'country_auto_id' => array(
                    'type' => 'INT',
                    'auto_increment' => TRUE
                ),
                'country_alpha2_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '10',
                    'null' => TRUE,
                    'default' => null
                ),
                'country_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE,
                    'default' => null
                ),
            ));
            $this->dbforge->add_key('country_auto_id', TRUE);
            $this->dbforge->create_table('tbl_country');

            // Insert Default Records
            $data = array(
                array('country_auto_id' => '1', 'country_alpha2_code' => 'AD', 'country_name' => 'Andorra'),
                array('country_auto_id' => '2', 'country_alpha2_code' => 'AF', 'country_name' => 'Afghanistan'),
                array('country_auto_id' => '3', 'country_alpha2_code' => 'AX', 'country_name' => 'Åland Islands'),
                array('country_auto_id' => '4', 'country_alpha2_code' => 'AL', 'country_name' => 'Albania'),
                array('country_auto_id' => '5', 'country_alpha2_code' => 'DZ', 'country_name' => 'Algeria'),
                array('country_auto_id' => '6', 'country_alpha2_code' => 'AS', 'country_name' => 'American Samoa'),
                array('country_auto_id' => '7', 'country_alpha2_code' => 'AO', 'country_name' => 'Angola'),
                array('country_auto_id' => '8', 'country_alpha2_code' => 'AI', 'country_name' => 'Anguilla'),
                array('country_auto_id' => '9', 'country_alpha2_code' => 'AQ', 'country_name' => 'Antarctica'),
                array('country_auto_id' => '10', 'country_alpha2_code' => 'AG', 'country_name' => 'Antigua and Barbuda'),
                array('country_auto_id' => '11', 'country_alpha2_code' => 'AR', 'country_name' => 'Argentina'),
                array('country_auto_id' => '12', 'country_alpha2_code' => 'AM', 'country_name' => 'Armenia'),
                array('country_auto_id' => '13', 'country_alpha2_code' => 'AW', 'country_name' => 'Aruba'),
                array('country_auto_id' => '14', 'country_alpha2_code' => 'AU', 'country_name' => 'Australia'),
                array('country_auto_id' => '15', 'country_alpha2_code' => 'AT', 'country_name' => 'Austria'),
                array('country_auto_id' => '16', 'country_alpha2_code' => 'AZ', 'country_name' => 'Azerbaijan'),
                array('country_auto_id' => '17', 'country_alpha2_code' => 'BS', 'country_name' => 'Bahamas'),
                array('country_auto_id' => '18', 'country_alpha2_code' => 'BH', 'country_name' => 'Bahrain'),
                array('country_auto_id' => '19', 'country_alpha2_code' => 'BD', 'country_name' => 'Bangladesh'),
                array('country_auto_id' => '20', 'country_alpha2_code' => 'BB', 'country_name' => 'Barbados'),
                array('country_auto_id' => '21', 'country_alpha2_code' => 'BY', 'country_name' => 'Belarus'),
                array('country_auto_id' => '22', 'country_alpha2_code' => 'BE', 'country_name' => 'Belgium'),
                array('country_auto_id' => '23', 'country_alpha2_code' => 'BZ', 'country_name' => 'Belize'),
                array('country_auto_id' => '24', 'country_alpha2_code' => 'BJ', 'country_name' => 'Benin'),
                array('country_auto_id' => '25', 'country_alpha2_code' => 'BM', 'country_name' => 'Bermuda'),
                array('country_auto_id' => '26', 'country_alpha2_code' => 'BT', 'country_name' => 'Bhutan'),
                array('country_auto_id' => '27', 'country_alpha2_code' => 'BO', 'country_name' => 'Bolivia (Plurinational State of)'),
                array('country_auto_id' => '28', 'country_alpha2_code' => 'BQ', 'country_name' => 'Bonaire, Sint Eustatius and Saba'),
                array('country_auto_id' => '29', 'country_alpha2_code' => 'BA', 'country_name' => 'Bosnia and Herzegovina'),
                array('country_auto_id' => '30', 'country_alpha2_code' => 'BW', 'country_name' => 'Botswana'),
                array('country_auto_id' => '31', 'country_alpha2_code' => 'BV', 'country_name' => 'Bouvet Island'),
                array('country_auto_id' => '32', 'country_alpha2_code' => 'BR', 'country_name' => 'Brazil'),
                array('country_auto_id' => '33', 'country_alpha2_code' => 'IO', 'country_name' => 'British Indian Ocean Territory'),
                array('country_auto_id' => '34', 'country_alpha2_code' => 'BN', 'country_name' => 'Brunei Darussalam'),
                array('country_auto_id' => '35', 'country_alpha2_code' => 'BG', 'country_name' => 'Bulgaria'),
                array('country_auto_id' => '36', 'country_alpha2_code' => 'BF', 'country_name' => 'Burkina Faso'),
                array('country_auto_id' => '37', 'country_alpha2_code' => 'BI', 'country_name' => 'Burundi'),
                array('country_auto_id' => '38', 'country_alpha2_code' => 'CV', 'country_name' => 'Cabo Verde'),
                array('country_auto_id' => '39', 'country_alpha2_code' => 'KH', 'country_name' => 'Cambodia'),
                array('country_auto_id' => '40', 'country_alpha2_code' => 'CM', 'country_name' => 'Cameroon'),
                array('country_auto_id' => '41', 'country_alpha2_code' => 'CA', 'country_name' => 'Canada'),
                array('country_auto_id' => '42', 'country_alpha2_code' => 'KY', 'country_name' => 'Cayman Islands'),
                array('country_auto_id' => '43', 'country_alpha2_code' => 'CF', 'country_name' => 'Central African Republic'),
                array('country_auto_id' => '44', 'country_alpha2_code' => 'TD', 'country_name' => 'Chad'),
                array('country_auto_id' => '45', 'country_alpha2_code' => 'CL', 'country_name' => 'Chile'),
                array('country_auto_id' => '46', 'country_alpha2_code' => 'CN', 'country_name' => 'China'),
                array('country_auto_id' => '47', 'country_alpha2_code' => 'CX', 'country_name' => 'Christmas Island'),
                array('country_auto_id' => '48', 'country_alpha2_code' => 'CC', 'country_name' => 'Cocos (Keeling) Islands'),
                array('country_auto_id' => '49', 'country_alpha2_code' => 'CO', 'country_name' => 'Colombia'),
                array('country_auto_id' => '50', 'country_alpha2_code' => 'KM', 'country_name' => 'Comoros'),
                array('country_auto_id' => '51', 'country_alpha2_code' => 'CG', 'country_name' => 'Congo'),
                array('country_auto_id' => '52', 'country_alpha2_code' => 'CD', 'country_name' => 'Congo (Democratic Republic of the)'),
                array('country_auto_id' => '53', 'country_alpha2_code' => 'CK', 'country_name' => 'Cook Islands'),
                array('country_auto_id' => '54', 'country_alpha2_code' => 'CR', 'country_name' => 'Costa Rica'),
                array('country_auto_id' => '55', 'country_alpha2_code' => 'CI', 'country_name' => 'Côte d\'Ivoire'),
                array('country_auto_id' => '56', 'country_alpha2_code' => 'HR', 'country_name' => 'Croatia'),
                array('country_auto_id' => '57', 'country_alpha2_code' => 'CU', 'country_name' => 'Cuba'),
                array('country_auto_id' => '58', 'country_alpha2_code' => 'CW', 'country_name' => 'Curaçao'),
                array('country_auto_id' => '59', 'country_alpha2_code' => 'CY', 'country_name' => 'Cyprus'),
                array('country_auto_id' => '60', 'country_alpha2_code' => 'CZ', 'country_name' => 'Czech Republic'),
                array('country_auto_id' => '61', 'country_alpha2_code' => 'DK', 'country_name' => 'Denmark'),
                array('country_auto_id' => '62', 'country_alpha2_code' => 'DJ', 'country_name' => 'Djibouti'),
                array('country_auto_id' => '63', 'country_alpha2_code' => 'DM', 'country_name' => 'Dominica'),
                array('country_auto_id' => '64', 'country_alpha2_code' => 'DO', 'country_name' => 'Dominican Republic'),
                array('country_auto_id' => '65', 'country_alpha2_code' => 'EC', 'country_name' => 'Ecuador'),
                array('country_auto_id' => '66', 'country_alpha2_code' => 'EG', 'country_name' => 'Egypt'),
                array('country_auto_id' => '67', 'country_alpha2_code' => 'SV', 'country_name' => 'El Salvador'),
                array('country_auto_id' => '68', 'country_alpha2_code' => 'GQ', 'country_name' => 'Equatorial Guinea'),
                array('country_auto_id' => '69', 'country_alpha2_code' => 'ER', 'country_name' => 'Eritrea'),
                array('country_auto_id' => '70', 'country_alpha2_code' => 'EE', 'country_name' => 'Estonia'),
                array('country_auto_id' => '71', 'country_alpha2_code' => 'ET', 'country_name' => 'Ethiopia'),
                array('country_auto_id' => '72', 'country_alpha2_code' => 'FK', 'country_name' => 'Falkland Islands (Malvinas)'),
                array('country_auto_id' => '73', 'country_alpha2_code' => 'FO', 'country_name' => 'Faroe Islands'),
                array('country_auto_id' => '74', 'country_alpha2_code' => 'FJ', 'country_name' => 'Fiji'),
                array('country_auto_id' => '75', 'country_alpha2_code' => 'FI', 'country_name' => 'Finland'),
                array('country_auto_id' => '76', 'country_alpha2_code' => 'FR', 'country_name' => 'France'),
                array('country_auto_id' => '77', 'country_alpha2_code' => 'GF', 'country_name' => 'French Guiana'),
                array('country_auto_id' => '78', 'country_alpha2_code' => 'PF', 'country_name' => 'French Polynesia'),
                array('country_auto_id' => '79', 'country_alpha2_code' => 'TF', 'country_name' => 'French Southern Territories'),
                array('country_auto_id' => '80', 'country_alpha2_code' => 'GA', 'country_name' => 'Gabon'),
                array('country_auto_id' => '81', 'country_alpha2_code' => 'GM', 'country_name' => 'Gambia'),
                array('country_auto_id' => '82', 'country_alpha2_code' => 'GE', 'country_name' => 'Georgia'),
                array('country_auto_id' => '83', 'country_alpha2_code' => 'DE', 'country_name' => 'Germany'),
                array('country_auto_id' => '84', 'country_alpha2_code' => 'GH', 'country_name' => 'Ghana'),
                array('country_auto_id' => '85', 'country_alpha2_code' => 'GI', 'country_name' => 'Gibraltar'),
                array('country_auto_id' => '86', 'country_alpha2_code' => 'GR', 'country_name' => 'Greece'),
                array('country_auto_id' => '87', 'country_alpha2_code' => 'GL', 'country_name' => 'Greenland'),
                array('country_auto_id' => '88', 'country_alpha2_code' => 'GD', 'country_name' => 'Grenada'),
                array('country_auto_id' => '89', 'country_alpha2_code' => 'GP', 'country_name' => 'Guadeloupe'),
                array('country_auto_id' => '90', 'country_alpha2_code' => 'GU', 'country_name' => 'Guam'),
                array('country_auto_id' => '91', 'country_alpha2_code' => 'GT', 'country_name' => 'Guatemala'),
                array('country_auto_id' => '92', 'country_alpha2_code' => 'GG', 'country_name' => 'Guernsey'),
                array('country_auto_id' => '93', 'country_alpha2_code' => 'GN', 'country_name' => 'Guinea'),
                array('country_auto_id' => '94', 'country_alpha2_code' => 'GW', 'country_name' => 'Guinea-Bissau'),
                array('country_auto_id' => '95', 'country_alpha2_code' => 'GY', 'country_name' => 'Guyana'),
                array('country_auto_id' => '96', 'country_alpha2_code' => 'HT', 'country_name' => 'Haiti'),
                array('country_auto_id' => '97', 'country_alpha2_code' => 'HM', 'country_name' => 'Heard Island and McDonald Islands'),
                array('country_auto_id' => '98', 'country_alpha2_code' => 'VA', 'country_name' => 'Holy See'),
                array('country_auto_id' => '99', 'country_alpha2_code' => 'HN', 'country_name' => 'Honduras'),
                array('country_auto_id' => '100', 'country_alpha2_code' => 'HK', 'country_name' => 'Hong Kong'),
                array('country_auto_id' => '101', 'country_alpha2_code' => 'HU', 'country_name' => 'Hungary'),
                array('country_auto_id' => '102', 'country_alpha2_code' => 'IS', 'country_name' => 'Iceland'),
                array('country_auto_id' => '103', 'country_alpha2_code' => 'IN', 'country_name' => 'India'),
                array('country_auto_id' => '104', 'country_alpha2_code' => 'ID', 'country_name' => 'Indonesia'),
                array('country_auto_id' => '105', 'country_alpha2_code' => 'IR', 'country_name' => 'Iran (Islamic Republic of)'),
                array('country_auto_id' => '106', 'country_alpha2_code' => 'IQ', 'country_name' => 'Iraq'),
                array('country_auto_id' => '107', 'country_alpha2_code' => 'IE', 'country_name' => 'Ireland'),
                array('country_auto_id' => '108', 'country_alpha2_code' => 'IM', 'country_name' => 'Isle of Man'),
                array('country_auto_id' => '109', 'country_alpha2_code' => 'IL', 'country_name' => 'Israel'),
                array('country_auto_id' => '110', 'country_alpha2_code' => 'IT', 'country_name' => 'Italy'),
                array('country_auto_id' => '111', 'country_alpha2_code' => 'JM', 'country_name' => 'Jamaica'),
                array('country_auto_id' => '112', 'country_alpha2_code' => 'JP', 'country_name' => 'Japan'),
                array('country_auto_id' => '113', 'country_alpha2_code' => 'JE', 'country_name' => 'Jersey'),
                array('country_auto_id' => '114', 'country_alpha2_code' => 'JO', 'country_name' => 'Jordan'),
                array('country_auto_id' => '115', 'country_alpha2_code' => 'KZ', 'country_name' => 'Kazakhstan'),
                array('country_auto_id' => '116', 'country_alpha2_code' => 'KE', 'country_name' => 'Kenya'),
                array('country_auto_id' => '117', 'country_alpha2_code' => 'KI', 'country_name' => 'Kiribati'),
                array('country_auto_id' => '118', 'country_alpha2_code' => 'KP', 'country_name' => 'Korea (Democratic People\'s Republic of)'),
                array('country_auto_id' => '119', 'country_alpha2_code' => 'KR', 'country_name' => 'Korea (Republic of)'),
                array('country_auto_id' => '120', 'country_alpha2_code' => 'KW', 'country_name' => 'Kuwait'),
                array('country_auto_id' => '121', 'country_alpha2_code' => 'KG', 'country_name' => 'Kyrgyzstan'),
                array('country_auto_id' => '122', 'country_alpha2_code' => 'LA', 'country_name' => 'Lao People\'s Democratic Republic'),
                array('country_auto_id' => '123', 'country_alpha2_code' => 'LV', 'country_name' => 'Latvia'),
                array('country_auto_id' => '124', 'country_alpha2_code' => 'LB', 'country_name' => 'Lebanon'),
                array('country_auto_id' => '125', 'country_alpha2_code' => 'LS', 'country_name' => 'Lesotho'),
                array('country_auto_id' => '126', 'country_alpha2_code' => 'LR', 'country_name' => 'Liberia'),
                array('country_auto_id' => '127', 'country_alpha2_code' => 'LY', 'country_name' => 'Libya'),
                array('country_auto_id' => '128', 'country_alpha2_code' => 'LI', 'country_name' => 'Liechtenstein'),
                array('country_auto_id' => '129', 'country_alpha2_code' => 'LT', 'country_name' => 'Lithuania'),
                array('country_auto_id' => '130', 'country_alpha2_code' => 'LU', 'country_name' => 'Luxembourg'),
                array('country_auto_id' => '131', 'country_alpha2_code' => 'MO', 'country_name' => 'Macao'),
                array('country_auto_id' => '132', 'country_alpha2_code' => 'MK', 'country_name' => 'Macedonia (the former Yugoslav Republic of)'),
                array('country_auto_id' => '133', 'country_alpha2_code' => 'MG', 'country_name' => 'Madagascar'),
                array('country_auto_id' => '134', 'country_alpha2_code' => 'MW', 'country_name' => 'Malawi'),
                array('country_auto_id' => '135', 'country_alpha2_code' => 'MY', 'country_name' => 'Malaysia'),
                array('country_auto_id' => '136', 'country_alpha2_code' => 'MV', 'country_name' => 'Maldives'),
                array('country_auto_id' => '137', 'country_alpha2_code' => 'ML', 'country_name' => 'Mali'),
                array('country_auto_id' => '138', 'country_alpha2_code' => 'MT', 'country_name' => 'Malta'),
                array('country_auto_id' => '139', 'country_alpha2_code' => 'MH', 'country_name' => 'Marshall Islands'),
                array('country_auto_id' => '140', 'country_alpha2_code' => 'MQ', 'country_name' => 'Martinique'),
                array('country_auto_id' => '141', 'country_alpha2_code' => 'MR', 'country_name' => 'Mauritania'),
                array('country_auto_id' => '142', 'country_alpha2_code' => 'MU', 'country_name' => 'Mauritius'),
                array('country_auto_id' => '143', 'country_alpha2_code' => 'YT', 'country_name' => 'Mayotte'),
                array('country_auto_id' => '144', 'country_alpha2_code' => 'MX', 'country_name' => 'Mexico'),
                array('country_auto_id' => '145', 'country_alpha2_code' => 'FM', 'country_name' => 'Micronesia (Federated States of)'),
                array('country_auto_id' => '146', 'country_alpha2_code' => 'MD', 'country_name' => 'Moldova (Republic of)'),
                array('country_auto_id' => '147', 'country_alpha2_code' => 'MC', 'country_name' => 'Monaco'),
                array('country_auto_id' => '148', 'country_alpha2_code' => 'MN', 'country_name' => 'Mongolia'),
                array('country_auto_id' => '149', 'country_alpha2_code' => 'ME', 'country_name' => 'Montenegro'),
                array('country_auto_id' => '150', 'country_alpha2_code' => 'MS', 'country_name' => 'Montserrat'),
                array('country_auto_id' => '151', 'country_alpha2_code' => 'MA', 'country_name' => 'Morocco'),
                array('country_auto_id' => '152', 'country_alpha2_code' => 'MZ', 'country_name' => 'Mozambique'),
                array('country_auto_id' => '153', 'country_alpha2_code' => 'MM', 'country_name' => 'Myanmar'),
                array('country_auto_id' => '154', 'country_alpha2_code' => 'NA', 'country_name' => 'Namibia'),
                array('country_auto_id' => '155', 'country_alpha2_code' => 'NR', 'country_name' => 'Nauru'),
                array('country_auto_id' => '156', 'country_alpha2_code' => 'NP', 'country_name' => 'Nepal'),
                array('country_auto_id' => '157', 'country_alpha2_code' => 'NL', 'country_name' => 'Netherlands'),
                array('country_auto_id' => '158', 'country_alpha2_code' => 'NC', 'country_name' => 'New Caledonia'),
                array('country_auto_id' => '159', 'country_alpha2_code' => 'NZ', 'country_name' => 'New Zealand'),
                array('country_auto_id' => '160', 'country_alpha2_code' => 'NI', 'country_name' => 'Nicaragua'),
                array('country_auto_id' => '161', 'country_alpha2_code' => 'NE', 'country_name' => 'Niger'),
                array('country_auto_id' => '162', 'country_alpha2_code' => 'NG', 'country_name' => 'Nigeria'),
                array('country_auto_id' => '163', 'country_alpha2_code' => 'NU', 'country_name' => 'Niue'),
                array('country_auto_id' => '164', 'country_alpha2_code' => 'NF', 'country_name' => 'Norfolk Island'),
                array('country_auto_id' => '165', 'country_alpha2_code' => 'MP', 'country_name' => 'Northern Mariana Islands'),
                array('country_auto_id' => '166', 'country_alpha2_code' => 'NO', 'country_name' => 'Norway'),
                array('country_auto_id' => '167', 'country_alpha2_code' => 'OM', 'country_name' => 'Oman'),
                array('country_auto_id' => '168', 'country_alpha2_code' => 'PK', 'country_name' => 'Pakistan'),
                array('country_auto_id' => '169', 'country_alpha2_code' => 'PW', 'country_name' => 'Palau'),
                array('country_auto_id' => '170', 'country_alpha2_code' => 'PS', 'country_name' => 'Palestine, State of'),
                array('country_auto_id' => '171', 'country_alpha2_code' => 'PA', 'country_name' => 'Panama'),
                array('country_auto_id' => '172', 'country_alpha2_code' => 'PG', 'country_name' => 'Papua New Guinea'),
                array('country_auto_id' => '173', 'country_alpha2_code' => 'PY', 'country_name' => 'Paraguay'),
                array('country_auto_id' => '174', 'country_alpha2_code' => 'PE', 'country_name' => 'Peru'),
                array('country_auto_id' => '175', 'country_alpha2_code' => 'PH', 'country_name' => 'Philippines'),
                array('country_auto_id' => '176', 'country_alpha2_code' => 'PN', 'country_name' => 'Pitcairn'),
                array('country_auto_id' => '177', 'country_alpha2_code' => 'PL', 'country_name' => 'Poland'),
                array('country_auto_id' => '178', 'country_alpha2_code' => 'PT', 'country_name' => 'Portugal'),
                array('country_auto_id' => '179', 'country_alpha2_code' => 'PR', 'country_name' => 'Puerto Rico'),
                array('country_auto_id' => '180', 'country_alpha2_code' => 'QA', 'country_name' => 'Qatar'),
                array('country_auto_id' => '181', 'country_alpha2_code' => 'RE', 'country_name' => 'Réunion'),
                array('country_auto_id' => '182', 'country_alpha2_code' => 'RO', 'country_name' => 'Romania'),
                array('country_auto_id' => '183', 'country_alpha2_code' => 'RU', 'country_name' => 'Russian Federation'),
                array('country_auto_id' => '184', 'country_alpha2_code' => 'RW', 'country_name' => 'Rwanda'),
                array('country_auto_id' => '185', 'country_alpha2_code' => 'BL', 'country_name' => 'Saint Barthélemy'),
                array('country_auto_id' => '186', 'country_alpha2_code' => 'SH', 'country_name' => 'Saint Helena, Ascension and Tristan da Cunha'),
                array('country_auto_id' => '187', 'country_alpha2_code' => 'KN', 'country_name' => 'Saint Kitts and Nevis'),
                array('country_auto_id' => '188', 'country_alpha2_code' => 'LC', 'country_name' => 'Saint Lucia'),
                array('country_auto_id' => '189', 'country_alpha2_code' => 'MF', 'country_name' => 'Saint Martin (French part)'),
                array('country_auto_id' => '190', 'country_alpha2_code' => 'PM', 'country_name' => 'Saint Pierre and Miquelon'),
                array('country_auto_id' => '191', 'country_alpha2_code' => 'VC', 'country_name' => 'Saint Vincent and the Grenadines'),
                array('country_auto_id' => '192', 'country_alpha2_code' => 'WS', 'country_name' => 'Samoa'),
                array('country_auto_id' => '193', 'country_alpha2_code' => 'SM', 'country_name' => 'San Marino'),
                array('country_auto_id' => '194', 'country_alpha2_code' => 'ST', 'country_name' => 'Sao Tome and Principe'),
                array('country_auto_id' => '195', 'country_alpha2_code' => 'SA', 'country_name' => 'Saudi Arabia'),
                array('country_auto_id' => '196', 'country_alpha2_code' => 'SN', 'country_name' => 'Senegal'),
                array('country_auto_id' => '197', 'country_alpha2_code' => 'RS', 'country_name' => 'Serbia'),
                array('country_auto_id' => '198', 'country_alpha2_code' => 'SC', 'country_name' => 'Seychelles'),
                array('country_auto_id' => '199', 'country_alpha2_code' => 'SL', 'country_name' => 'Sierra Leone'),
                array('country_auto_id' => '200', 'country_alpha2_code' => 'SG', 'country_name' => 'Singapore'),
                array('country_auto_id' => '201', 'country_alpha2_code' => 'SX', 'country_name' => 'Sint Maarten (Dutch part)'),
                array('country_auto_id' => '202', 'country_alpha2_code' => 'SK', 'country_name' => 'Slovakia'),
                array('country_auto_id' => '203', 'country_alpha2_code' => 'SI', 'country_name' => 'Slovenia'),
                array('country_auto_id' => '204', 'country_alpha2_code' => 'SB', 'country_name' => 'Solomon Islands'),
                array('country_auto_id' => '205', 'country_alpha2_code' => 'SO', 'country_name' => 'Somalia'),
                array('country_auto_id' => '206', 'country_alpha2_code' => 'ZA', 'country_name' => 'South Africa'),
                array('country_auto_id' => '207', 'country_alpha2_code' => 'GS', 'country_name' => 'South Georgia and the South Sandwich Islands'),
                array('country_auto_id' => '208', 'country_alpha2_code' => 'SS', 'country_name' => 'South Sudan'),
                array('country_auto_id' => '209', 'country_alpha2_code' => 'ES', 'country_name' => 'Spain'),
                array('country_auto_id' => '210', 'country_alpha2_code' => 'LK', 'country_name' => 'Sri Lanka'),
                array('country_auto_id' => '211', 'country_alpha2_code' => 'SD', 'country_name' => 'Sudan'),
                array('country_auto_id' => '212', 'country_alpha2_code' => 'SR', 'country_name' => 'Suriname'),
                array('country_auto_id' => '213', 'country_alpha2_code' => 'SJ', 'country_name' => 'Svalbard and Jan Mayen'),
                array('country_auto_id' => '214', 'country_alpha2_code' => 'SZ', 'country_name' => 'Swaziland'),
                array('country_auto_id' => '215', 'country_alpha2_code' => 'SE', 'country_name' => 'Sweden'),
                array('country_auto_id' => '216', 'country_alpha2_code' => 'CH', 'country_name' => 'Switzerland'),
                array('country_auto_id' => '217', 'country_alpha2_code' => 'SY', 'country_name' => 'Syrian Arab Republic'),
                array('country_auto_id' => '218', 'country_alpha2_code' => 'TW', 'country_name' => 'Taiwan, Province of China'),
                array('country_auto_id' => '219', 'country_alpha2_code' => 'TJ', 'country_name' => 'Tajikistan'),
                array('country_auto_id' => '220', 'country_alpha2_code' => 'TZ', 'country_name' => 'Tanzania, United Republic of'),
                array('country_auto_id' => '221', 'country_alpha2_code' => 'TH', 'country_name' => 'Thailand'),
                array('country_auto_id' => '222', 'country_alpha2_code' => 'TL', 'country_name' => 'Timor-Leste'),
                array('country_auto_id' => '223', 'country_alpha2_code' => 'TG', 'country_name' => 'Togo'),
                array('country_auto_id' => '224', 'country_alpha2_code' => 'TK', 'country_name' => 'Tokelau'),
                array('country_auto_id' => '225', 'country_alpha2_code' => 'TO', 'country_name' => 'Tonga'),
                array('country_auto_id' => '226', 'country_alpha2_code' => 'TT', 'country_name' => 'Trinidad and Tobago'),
                array('country_auto_id' => '227', 'country_alpha2_code' => 'TN', 'country_name' => 'Tunisia'),
                array('country_auto_id' => '228', 'country_alpha2_code' => 'TR', 'country_name' => 'Turkey'),
                array('country_auto_id' => '229', 'country_alpha2_code' => 'TM', 'country_name' => 'Turkmenistan'),
                array('country_auto_id' => '230', 'country_alpha2_code' => 'TC', 'country_name' => 'Turks and Caicos Islands'),
                array('country_auto_id' => '231', 'country_alpha2_code' => 'TV', 'country_name' => 'Tuvalu'),
                array('country_auto_id' => '232', 'country_alpha2_code' => 'UG', 'country_name' => 'Uganda'),
                array('country_auto_id' => '233', 'country_alpha2_code' => 'UA', 'country_name' => 'Ukraine'),
                array('country_auto_id' => '234', 'country_alpha2_code' => 'AE', 'country_name' => 'United Arab Emirates'),
                array('country_auto_id' => '235', 'country_alpha2_code' => 'GB', 'country_name' => 'United Kingdom of Great Britain and Northern Ireland'),
                array('country_auto_id' => '236', 'country_alpha2_code' => 'UM', 'country_name' => 'United States Minor Outlying Islands'),
                array('country_auto_id' => '237', 'country_alpha2_code' => 'US', 'country_name' => 'United States of America'),
                array('country_auto_id' => '238', 'country_alpha2_code' => 'UY', 'country_name' => 'Uruguay'),
                array('country_auto_id' => '239', 'country_alpha2_code' => 'UZ', 'country_name' => 'Uzbekistan'),
                array('country_auto_id' => '240', 'country_alpha2_code' => 'VU', 'country_name' => 'Vanuatu'),
                array('country_auto_id' => '241', 'country_alpha2_code' => 'VE', 'country_name' => 'Venezuela (Bolivarian Republic of)'),
                array('country_auto_id' => '242', 'country_alpha2_code' => 'VN', 'country_name' => 'Viet Nam'),
                array('country_auto_id' => '243', 'country_alpha2_code' => 'VG', 'country_name' => 'Virgin Islands (British)'),
                array('country_auto_id' => '244', 'country_alpha2_code' => 'VI', 'country_name' => 'Virgin Islands (U.S.)'),
                array('country_auto_id' => '245', 'country_alpha2_code' => 'WF', 'country_name' => 'Wallis and Futuna'),
                array('country_auto_id' => '246', 'country_alpha2_code' => 'EH', 'country_name' => 'Western Sahara'),
                array('country_auto_id' => '247', 'country_alpha2_code' => 'YE', 'country_name' => 'Yemen'),
                array('country_auto_id' => '248', 'country_alpha2_code' => 'ZM', 'country_name' => 'Zambia'),
                array('country_auto_id' => '249', 'country_alpha2_code' => 'ZW', 'country_name' => 'Zimbabwe'),
                array('country_auto_id' => '250', 'country_alpha2_code' => 'UR', 'country_name' => 'Unknown Region')
            );
            $this->db->insert_batch('tbl_country', $data);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('tbl_country');
    }
}