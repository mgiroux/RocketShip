<?php

namespace RocketShip\Helpers;

use RocketShip\Base;

class World extends Base
{
    /**
     *
     * getAmericanStates
     *
     * Get an array of the American States (perfect for select box)
     *
     * @param   string  the current language to retreive
     * @return  array   array of code => state name
     * @access  public
     */
    public function getAmericanStates($locale='fr')
    {
        $list['en'] = array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas',
            'California', 'Colorado', 'Connecticut', 'Delaware',
            'Washington D.C.', 'Florida', 'Georgia', 'Hawaii',
            'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas',
            'Kentucky', 'Louisiana', 'Maine', 'Maryland',
            'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi',
            'Missouri', 'Montana', 'Nebraska', 'Nevada',
            'New Hampshire', 'New Jersey', 'New Mexico',
            'New York', 'North Carolina', 'North Dakota',
            'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania',
            'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee',
            'Texas', 'Utah', 'Vermont', 'Virginia',
            'Washington', 'West Virginia', 'Wisconsin', 'Wyoming',
            'Puerto Rico', 'Guam'
        );

        $list['fr'] = array(
            'Alabama', 'Alaska', 'Arizona', 'Arkansas',
            'Californie', 'Colorado', 'Connecticut', 'Delaware',
            'Washington D.C.', 'Floride', 'Georgie', 'Hawaii',
            'Idaho', 'Illinois', 'Indiana', 'Iowa',
            'Kansas', 'Kentucky', 'Louisiane', 'Maine',
            'Maryland', 'Massachusetts', 'Michigan', 'Minnesota',
            'Mississippi', 'Missouri', 'Montana', 'Nebraska',
            'Nevada', 'New Hampshire', 'New Jersey', 'Nouveau Mexique',
            'New York', 'Caroline du Nord', 'Dakota du Nord', 'Ohio',
            'Oklahoma', 'Oregon', 'Pennsylvanie', 'Rhode Island',
            'Caroline du Sud', 'Dakota du Sud', 'Tennessee', 'Texas',
            'Utah', 'Vermont', 'Virginie', 'Washington',
            'Virginie de l\'Ouest', 'Wisconsin', 'Wyoming',
            'Puerto Rico', 'Guam'
        );

        $codes = array(
            'AL', 'AK', 'AZ', 'AR',
            'CA', 'CO', 'CT', 'DE',
            'DC', 'FL', 'GA', 'HI',
            'ID', 'IL', 'IN', 'IA',
            'KS', 'KY', 'LA', 'ME',
            'MD', 'MA', 'MI', 'MN',
            'MS', 'MO', 'MT', 'NE',
            'NV', 'NH', 'NJ', 'NM',
            'NY', 'NC', 'ND', 'OH',
            'OK', 'OR', 'PA', 'RI',
            'SC', 'SD', 'TN', 'TX',
            'UT', 'VA', 'WA', 'WV',
            'WV', 'WI', 'WY', 'PR',
            'GU'
        );

        return array_combine($codes, $list[$locale]);
    }

    /**
     *
     * getAmericanStatesList
     *
     * Get a states list ready for <select> tag
     *
     * @param   string  the current language to retrieve
     * @param   string  state code (optional)
     * @return  string  select box options
     * @access  public
     *
     */
    public function getAmericanStatesList($locale='fr', $selected=null)
    {
        $list = $this->getAmericanStates($locale);
        $out  = '';

        foreach ($list as $code => $province) {
            if ($code == strtoupper($selected)) {
                $out .= '<option value="' . $code . '" selected="selected">' . $province . '</option>' . "\n";
            } else {
                $out .= '<option value="' . $code . '">' . $province . '</option>' . "\n";
            }
        }

        return $out;
    }

    /**
     *
     * getCanadianProvinces
     *
     * Get list of Canadian provinces
     *
     * @param   string  the current language to retreive
     * @return  array   array of code => province name
     * @access  public
     *
     */
    public function getCanadianProvinces($locale='fr')
    {
        $list['en'] = array(
            'Ontario', 'Quebec', 'Nova Scotia',
            'New Brunswick', 'Manitoba', 'British Columbia',
            'Prince Edward Island', 'Saskatchewan', 'Alberta',
            'Newfoundland and Labrador', 'Northwest Territories',
            'Yukon', 'Nunavut'
        );

        $list['fr'] = array(
            'Ontario', 'Québec', 'Nouvelle-Écosse',
            'Nouveau-Brunswick', 'Manitoba', 'Colombie Britannique',
            'l\'île du Prince-Édouard', 'Saskatchewan', 'Alberta',
            'Terre-Neuve et Labrador','Territoires du Nord-Ouest',
            'Yukon', 'Nunavut'
        );

        $codes = array(
            'ON', 'QC', 'NS',
            'NB', 'MB', 'BC',
            'PE', 'SK', 'AB',
            'NL', 'NT', 'YT',
            'NU'
        );

        return array_combine($codes, $list[$locale]);
    }

    /**
     *
     * getCanadianProvincesList
     *
     * Get a provinces list ready for <select> tag
     *
     * @param   string  the current language to retrieve
     * @param   string  province code (optional)
     * @return  string  select box options
     * @access  public
     *
     */
    public function getCanadianProvincesList($locale='fr', $selected=null)
    {
        $list = $this->getCanadianProvinces($locale);
        $out  = '';

        foreach ($list as $code => $province) {
            if ($code == strtoupper($selected)) {
                $out .= '<option value="' . $code . '" selected="selected">' . $province . '</option>' . "\n";
            } else {
                $out .= '<option value="' . $code . '">' . $province . '</option>' . "\n";
            }
        }

        return $out;
    }

    /**
     *
     * getCountryList
     *
     * Get a country list ready for <select> tag
     *
     * @param   string  the current language to retreive
     * @param   string  country code (optional)
     * @param   string  type option or list (optional)
     * @return  string  select box options
     * @access  public
     *
     */
    public function getCountryList($language='fr', $selected=null, $format='option')
    {
        $data = $this->getCountries($language);
        $out  = '';

        foreach ($data as $key => $value) {
            if ($key == strtoupper($selected)) {
                if ($format == 'option') {
                    $out .= '<option value="' . $key . '" selected="selected">' . $value . '</option>' . "\n";
                } else {
                    $out .= '<li data-value="' . $key . '">' . $value . '</li>' . "\n";
                }

            } else {
                if ($format == 'option') {
                    $out .= '<option value="' . $key . '">' . $value . '</option>' . "\n";
                } else {
                    $out .= '<li data-value="' . $key . '">' . $value . '</li>' . "\n";
                }
            }
        }

        return $out;
    }

    /**
     *
     * getCountryByCode
     *
     * Get a country by it's country code
     *
     * @param   string  the current language to retreive
     * @param   string  country code
     * @return  string  country name in the right language
     * @access  public
     *
     */
    public function getCountryByCode($code, $locale='fr')
    {
        $countries = $this->getCountries($locale);
        return $countries[strtoupper($code)];
    }

    /**
     *
     * getCountries
     *
     * Get the world countries in the right language
     *
     * @param   string  the current language to retreive
     * @return  array   country list
     * @access  public
     *
     */
    public function getCountries($locale='fr')
    {
        $countries['en'] = array(
            'CA' => 'Canada', 'US' => 'United States of America', 'AF' => 'Afghanistan',
            'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa',
            'AD' => 'Andorra', 'AO' => 'Angola', 'AI' => 'Anguilla',
            'AG' => 'Antigua &amp; Barbuda', 'AR' => 'Argentina', 'AA' => 'Armenia',
            'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria',
            'AZ' => 'Azerbaijan', 'BS' => 'Bahamas', 'BH' => 'Bahrain',
            'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus',
            'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin',
            'BM' => 'Bermuda', 'BT' => 'Bhutan', 'BO' => 'Bolivia',
            'BL' => 'Bonaire', 'BA' => 'Bosnia &amp; Herzegovina', 'BW' => 'Botswana',
            'BR' => 'Brazil', 'BC' => 'British Indian Ocean Ter', 'BN' => 'Brunei',
            'BG' => 'Bulgaria', 'BF' => 'Burkina Faso', 'BI' => 'Burundi',
            'KH' => 'Cambodia', 'CM' => 'Cameroon', 'IC' => 'Canary Islands',
            'CV' => 'Cape Verde', 'KY' => 'Cayman Islands', 'CF' => 'Central African Republic',
            'TD' => 'Chad', 'CD' => 'Channel Islands', 'CL' => 'Chile',
            'CN' => 'China', 'CI' => 'Christmas Island', 'CS' => 'Cocos Island',
            'CO' => 'Colombia', 'CC' => 'Comoros', 'CG' => 'Congo',
            'CK' => 'Cook Islands', 'CR' => 'Costa Rica', 'CT' => 'Cote D\'Ivoire',
            'HR' => 'Croatia', 'CU' => 'Cuba', 'CB' => 'Curacao',
            'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark',
            'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic',
            'TM' => 'East Timor', 'EC' => 'Ecuador', 'EG' => 'Egypt',
            'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea',
            'EE' => 'Estonia', 'ET' => 'Ethiopia', 'FA' => 'Falkland Islands',
            'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland',
            'FR' => 'France', 'GF' => 'French Guiana', 'PF' => 'French Polynesia',
            'FS' => 'French Southern Ter', 'GA' => 'Gabon', 'GM' => 'Gambia',
            'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana',
            'GI' => 'Gibraltar', 'GB' => 'Great Britain', 'GR' => 'Greece',
            'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe',
            'GU' => 'Guam', 'GT' => 'Guatemala', 'GN' => 'Guinea',
            'GY' => 'Guyana', 'HT' => 'Haiti', 'HW' => 'Hawaii',
            'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary',
            'IS' => 'Iceland', 'IN' => 'India', 'ID' => 'Indonesia',
            'IA' => 'Iran', 'IQ' => 'Iraq', 'IR' => 'Ireland',
            'IM' => 'Isle of Man', 'IL' => 'Israel', 'IT' => 'Italy',
            'JM' => 'Jamaica', 'JP' => 'Japan', 'JO' => 'Jordan',
            'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati',
            'NK' => 'Korea North', 'KS' => 'Korea South', 'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan', 'LA' => 'Laos', 'LV' => 'Latvia',
            'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia',
            'LY' => 'Libya', 'LI' => 'Liechtenstein', 'LT' => 'Lithuania',
            'LU' => 'Luxembourg', 'MO' => 'Macau', 'MK' => 'Macedonia',
            'MG' => 'Madagascar', 'MY' => 'Malaysia', 'MW' => 'Malawi',
            'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malta',
            'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania',
            'MU' => 'Mauritius', 'YT' => 'Mayotte', 'MX' => 'Mexico',
            'MI' => 'Midway Islands', 'MD' => 'Moldova', 'MC' => 'Monaco',
            'MN' => 'Mongolia', 'MS' => 'Montserrat', 'MA' => 'Morocco',
            'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Nambia',
            'NU' => 'Nauru', 'NP' => 'Nepal', 'AN' => 'Netherland Antilles',
            'NL' => 'Netherlands (Holland, Europe)', 'NV' => 'Nevis', 'NC' => 'New Caledonia',
            'NZ' => 'New Zealand', 'NI' => 'Nicaragua', 'NE' => 'Niger',
            'NG' => 'Nigeria', 'NW' => 'Niue', 'NF' => 'Norfolk Island',
            'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan',
            'PW' => 'Palau Island', 'PS' => 'Palestine', 'PA' => 'Panama',
            'PG' => 'Papua New Guinea', 'PY' => 'Paraguay', 'PE' => 'Peru',
            'PH' => 'Philippines', 'PO' => 'Pitcairn Island', 'PL' => 'Poland',
            'PT' => 'Portugal', 'PR' => 'Puerto Rico', 'QA' => 'Qatar',
            'ME' => 'Republic of Montenegro', 'RocketShip' => 'Republic of Serbia', 'RE' => 'Reunion',
            'RO' => 'Romania', 'RU' => 'Russia', 'RW' => 'Rwanda',
            'NT' => 'St Barthelemy', 'EU' => 'St Eustatius', 'HE' => 'St Helena',
            'KN' => 'St Kitts-Nevis', 'LC' => 'St Lucia', 'MB' => 'St Maarten',
            'PM' => 'St Pierre &amp; Miquelon', 'VC' => 'St Vincent &amp; Grenadines', 'SP' => 'Saipan',
            'SO' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome &amp; Principe',
            'SA' => 'Saudi Arabia', 'SN' => 'Senegal', 'SC' => 'Seychelles',
            'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia',
            'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'OI' => 'Somalia',
            'ZA' => 'South Africa', 'ES' => 'Spain', 'LK' => 'Sri Lanka',
            'SD' => 'Sudan', 'SR' => 'Suriname', 'SZ' => 'Swaziland',
            'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syria',
            'TA' => 'Tahiti', 'TW' => 'Taiwan', 'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania', 'TH' => 'Thailand', 'TG' => 'Togo',
            'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad &amp; Tobago',
            'TN' => 'Tunisia', 'TR' => 'Turkey', 'TU' => 'Turkmenistan',
            'TC' => 'Turks &amp; Caicos Is', 'TV' => 'Tuvalu', 'UG' => 'Uganda',
            'UA' => 'Ukraine', 'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom',
            'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu',
            'VS' => 'Vatican City State', 'VE' => 'Venezuela', 'VN' => 'Vietnam',
            'VB' => 'Virgin Islands (Brit)', 'WK' => 'Wake Island', 'WF' => 'Wallis &amp; Futana Is',
            'YE' => 'Yemen', 'ZR' => 'Zaire', 'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        $countries['fr'] = array(
            'CA' => 'Canada', 'US' => 'États-Unis', 'AF' => 'Afghanistan',
            'ZA' => 'Afrique du Sud', 'AL' => 'Albanie', 'DZ' => 'Algérie',
            'DE' => 'Allemagne', 'AD' => 'Andorre', 'AO' => 'Angola',
            'AI' => 'Anguilla', 'AG' => 'Antigua et Barbuda', 'AN' => 'Antilles néerlandaises',
            'SA' => 'Arabie saoudite', 'AR' => 'Argentine', 'AM' => 'Arménie',
            'AW' => 'Aruba', 'AU' => 'Australie', 'AT' => 'Autriche',
            'AZ' => 'Azerbaïdjan', 'BS' => 'Bahamas', 'BH' => 'Bahreïn',
            'BD' => 'Bangladesh', 'BB' => 'Barbade', 'BE' => 'Belgique',
            'BM' => 'Bermudes', 'BT' => 'Bhoutan', 'BY' => 'Biélorussie',
            'BO' => 'Bolivie', 'BA' => 'Bosnie et Herzégovine', 'BW' => 'Botswana',
            'BN' => 'Brunei Darussalam', 'BR' => 'Brésil', 'BG' => 'Bulgarie',
            'BF' => 'Burkina Faso', 'BI' => 'Burundi', 'BZ' => 'Bélize',
            'BJ' => 'Bénin', 'KH' => 'Cambodge', 'CM' => 'Cameroun',
            'CV' => 'Cap-Vert', 'CF' => 'Centrafrique', 'CL' => 'Chili',
            'CN' => 'Chine', 'CY' => 'Chypre', 'CO' => 'Colombiae',
            'KM' => 'Comores', 'CG' => 'Congo', 'KR' => 'Corée du Nord',
            'KP' => 'Corée du Sud', 'CR' => 'Costa Rica', 'HR' => 'Croatie',
            'CU' => 'Cuba', 'CI' => 'Côte d\'Ivoire', 'DK' => 'Danemark',
            'DJ' => 'Djibouti', 'DM' => 'Dominique', 'SV' => 'El Salvador',
            'ES' => 'Espagne', 'EE' => 'Estonie', 'EG' => 'Égypte',
            'AE' => 'Émirats arabes unis', 'EC' => 'Équateur', 'ER' => 'Érythrée',
            'FM' => 'États fédérés de Micronésie', 'ET' => 'Éthiopie', 'FJ' => 'Fidji',
            'FI' => 'Finlande', 'FR' => 'France', 'GA' => 'Gabon',
            'GM' => 'Gambie', 'GH' => 'Ghana', 'GI' => 'Gibraltar',
            'GD' => 'Grenade', 'GL' => 'Groënland', 'GR' => 'Grèce',
            'GP' => 'Guadeloupe', 'GU' => 'Guam', 'GT' => 'Guatemala',
            'GN' => 'Guinée', 'GQ' => 'Guinée équatoriale', 'GW' => 'Guinée-Bissau',
            'GY' => 'Guyane', 'GF' => 'Guyane française', 'GE' => 'Géorgie',
            'HT' => 'Haïti', 'HN' => 'Honduras', 'HK' => 'Hong Kong',
            'HU' => 'Hongrie', 'BV' => 'Ile Bouvet', 'CX' => 'Ile Christmas',
            'HM' => 'Ile Heard et iles McDonald', 'MU' => 'Ile Maurice', 'NF' => 'Ile Norfolk',
            'KY' => 'Iles Cayman', 'CC' => 'Iles Cocos (Keeling)', 'CK' => 'Iles Cook',
            'FK' => 'Iles Falkland (Malouines)', 'FO' => 'Iles Faroe', 'MH' => 'Iles Marshall',
            'MP' => 'Iles Northern Mariana', 'SB' => 'Iles Salomon', 'VG' => 'Iles Vierges, G.B.',
            'IN' => 'Inde', 'ID' => 'Indonésie', 'IQ' => 'Irak',
            'IR' => 'Iran', 'IE' => 'Irlande', 'IS' => 'Islande',
            'IL' => 'Israël', 'IT' => 'Italie', 'JM' => 'Jamaïque',
            'JP' => 'Japon', 'JO' => 'Jordan', 'KZ' => 'Kazakhstan',
            'KE' => 'Kenya', 'KG' => 'Kirghizstan', 'KI' => 'Kiribati',
            'KW' => 'Koweït', 'LA' => 'Laos', 'LV' => 'Lettonie',
            'LB' => 'Liban', 'LY' => 'Libye', 'LR' => 'Libéria',
            'LI' => 'Liechtenstein', 'LT' => 'Lituanie', 'LU' => 'Luxembourg',
            'LS' => 'Lésotho', 'MO' => 'Macao', 'MK' => 'Macédoine',
            'MG' => 'Madagascar', 'MY' => 'Malaisie', 'MW' => 'Malawi',
            'MV' => 'Maldives', 'ML' => 'Mali', 'MT' => 'Malte',
            'MA' => 'Maroc', 'MQ' => 'Martinique', 'MR' => 'Mauritanie',
            'YT' => 'Mayotte', 'MX' => 'Mexique', 'MD' => 'Moldavie',
            'MC' => 'Monaco', 'MN' => 'Mongolie', 'MS' => 'Montserrat',
            'MZ' => 'Mozambique', 'MM' => 'Myanmar (Birmanie)', 'NA' => 'Namibie',
            'NR' => 'Nauru', 'NI' => 'Nicaragua', 'NE' => 'Niger',
            'NG' => 'Nigéria', 'NU' => 'Niue', 'NO' => 'Norvège',
            'NC' => 'Nouvelle Calédonie', 'NZ' => 'Nouvelle-Zélande', 'NP' => 'Népal',
            'OM' => 'Oman', 'UG' => 'Ouganda', 'UZ' => 'Ouzbékistan',
            'PK' => 'Pakistan', 'PW' => 'Palau', 'PS' => 'Palestine',
            'PA' => 'Panama', 'PG' => 'Papouasie Nouvelle Guinée', 'PY' => 'Paraguay',
            'NL' => 'Pays-Bas', 'PH' => 'Philippines', 'PN' => 'Pitcairn',
            'PL' => 'Pologne', 'PF' => 'Polynésie française', 'PT' => 'Portugal',
            'PR' => 'Puerto Rico', 'PE' => 'Pérou', 'QA' => 'Qatar',
            'RO' => 'Roumanie', 'GB' => 'Royaume-Uni', 'RU' => 'Russie',
            'RW' => 'Rwanda', 'CD' => 'République Démocratique du Congo', 'DO' => 'République dominicaine',
            'CZ' => 'République tchèque', 'RE' => 'Réunion, île de la', 'EH' => 'Sahara Ouest',
            'KN' => 'Saint-Kitts et Nevis', 'PM' => 'Saint-Pierre et Miquelon', 'VC' => 'Saint-Vincent et Les Grenadines',
            'SH' => 'Sainte-Hélène', 'LC' => 'Sainte-Lucie', 'WS' => 'Samoa', 'AS' => 'Samoa américaine',
            'SM' => 'San Marino', 'ST' => 'San Tomé et Principe', 'SC' => 'Seychelles',
            'SL' => 'Sierra Leone', 'SG' => 'Singapour', 'SK' => 'Slovaquie',
            'SI' => 'Slovénie', 'SO' => 'Somalie', 'SD' => 'Soudan',
            'LK' => 'Sri Lanka', 'GS' => 'St-George et les iles Sandwich', 'CH' => 'Suisse',
            'SR' => 'Surinam', 'SE' => 'Suède', 'SJ' => 'Svalbard et Jan Mayen',
            'SZ' => 'Swaziland', 'SY' => 'Syrie', 'SN' => 'Sénégal',
            'TJ' => 'Tadjikistan', 'TZ' => 'Tanzanie', 'TW' => 'Taïwan',
            'TD' => 'Tchad', 'IO' => 'Territoire britannique de l\'Océan Indien', 'TF' => 'Territoires français du Sud',
            'TH' => 'Thaïlande', 'TP' => 'Timor Est', 'TG' => 'Togo',
            'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad et Tobago',
            'TN' => 'Tunisie', 'TM' => 'Turkmenistan', 'TC' => 'Turks et iles Caicos',
            'TR' => 'Turquie', 'TV' => 'Tuvalu',  'UA' => 'Ukraine',
            'UM' => 'United States Minor Outlying Islands', 'UY' => 'Uruguay', 'VU' => 'Vanuatu',
            'VA' => 'Vatican, cité du', 'VN' => 'Vietnam', 'VE' => 'Vénézuela',
            'WF' => 'Wallis et Futuna', 'YU' => 'Yougoslavie', 'YE' => 'Yémen',
            'ZM' => 'Zambie', 'ZW' => 'Zimbabwé',
        );

        return $countries[$locale];
    }
}
