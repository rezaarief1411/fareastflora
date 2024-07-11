<?php

namespace Smartosc\Customer\Model;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PhoneNumberByCountry
{
    const SG_DIGIT_NUMBER = '8';
    public $countriesList = null;

    public $mappingCountry = array(

        array(
            'country_name' => 'Afghanistan',
            'country_code' => '93',
        ),

        array(
            'country_name' => 'Åland Islands',
            'country_code' => '35818',
        ),

        array(
            'country_name' => 'Albania',
            'country_code' => '355',
        ),

        array(
            'country_name' => 'Antarctica',
            'country_code' => '672',
        ),

        array(
            'country_name' => 'Algeria',
            'country_code' => '213',
        ),

        array(
            'country_name' => 'American Samoa',
            'country_code' => '1684',
        ),

        array(
            'country_name' => 'Andorra',
            'country_code' => '376',
        ),

        array(
            'country_name' => 'Angola',
            'country_code' => '244',
        ),

        array(
            'country_name' => 'Anguilla',
            'country_code' => '1264',
        ),

        array(
            'country_name' => 'Antigua & Barbuda',
            'country_code' => '1268',
        ),

        array(
            'country_name' => 'Argentina',
            'country_code' => '54',
        ),

        array(
            'country_name' => 'Armenia',
            'country_code' => '374',
        ),

        array(
            'country_name' => 'Aruba',
            'country_code' => '297',
        ),

        array(
            'country_name' => 'Ascension',
            'country_code' => '247',
        ),

        array(
            'country_name' => 'Australia',
            'country_code' => '61',
        ),

        array(
            'country_name' => 'Australian Antarctic Territory',
            'country_code' => '6721',
        ),

        array(
            'country_name' => 'Australian External Territories',
            'country_code' => '672',
        ),

        array(
            'country_name' => 'Austria',
            'country_code' => '43',
        ),

        array(
            'country_name' => 'Azerbaijan',
            'country_code' => '994',
        ),

        array(
            'country_name' => 'Bahamas',
            'country_code' => '1242',
        ),

        array(
            'country_name' => 'Bahrain',
            'country_code' => '973',
        ),

        array(
            'country_name' => 'Bangladesh',
            'country_code' => '880',
        ),

        array(
            'country_name' => 'Barbados',
            'country_code' => '1246',
        ),

        array(
            'country_name' => 'Barbuda',
            'country_code' => '1268',
        ),

        array(
            'country_name' => 'Belarus',
            'country_code' => '375',
        ),

        array(
            'country_name' => 'Belgium',
            'country_code' => '32',
        ),

        array(
            'country_name' => 'Belize',
            'country_code' => '501',
        ),

        array(
            'country_name' => 'Benin',
            'country_code' => '229',
        ),

        array(
            'country_name' => 'Bermuda',
            'country_code' => '1441',
        ),

        array(
            'country_name' => 'Bhutan',
            'country_code' => '975',
        ),

        array(
            'country_name' => 'Bolivia',
            'country_code' => '591',
        ),

        array(
            'country_name' => 'Bonaire',
            'country_code' => '5997',
        ),

        array(
            'country_name' => 'Bosnia & Herzegovina',
            'country_code' => '387',
        ),

        array(
            'country_name' => 'Botswana',
            'country_code' => '267',
        ),
        array(
            'country_name' => 'Bouvet Island',
            'country_code' => '55',
        ),
        array(
            'country_name' => 'Brunei',
            'country_code' => '673',
        ),

        array(
            'country_name' => 'Brazil',
            'country_code' => '55',
        ),

        array(
            'country_name' => 'British Indian Ocean Territory',
            'country_code' => '246',
        ),

        array(
            'country_name' => 'British Virgin Islands',
            'country_code' => '1284',
        ),

        array(
            'country_name' => 'Brunei Darussalam',
            'country_code' => '673',
        ),

        array(
            'country_name' => 'Bulgaria',
            'country_code' => '359',
        ),

        array(
            'country_name' => 'Burkina Faso',
            'country_code' => '226',
        ),

        array(
            'country_name' => 'Burundi',
            'country_code' => '257',
        ),

        array(
            'country_name' => 'Cape Verde',
            'country_code' => '238',
        ),

        array(
            'country_name' => 'Cambodia',
            'country_code' => '855',
        ),

        array(
            'country_name' => 'Cameroon',
            'country_code' => '237',
        ),

        array(
            'country_name' => 'Canada',
            'country_code' => '1',
        ),

        array(
            'country_name' => 'Caribbean Netherlands',
            'country_code' => '5993',
        ),

        array(
            'country_name' => 'Cayman Islands',
            'country_code' => '1345',
        ),

        array(
            'country_name' => 'Central African Republic',
            'country_code' => '236',
        ),

        array(
            'country_name' => 'Chad',
            'country_code' => '235',
        ),

        array(
            'country_name' => 'Chatham Island, New Zealand',
            'country_code' => '64',
        ),

        array(
            'country_name' => 'Chile',
            'country_code' => '56',
        ),

        array(
            'country_name' => 'China',
            'country_code' => '86',
        ),

        array(
            'country_name' => 'Christmas Island',
            'country_code' => '6189164',
        ),

        array(
            'country_name' => 'Cocos (Keeling) Islands',
            'country_code' => '6189162',
        ),

        array(
            'country_name' => 'Colombia',
            'country_code' => '57',
        ),

        array(
            'country_name' => 'Comoros',
            'country_code' => '269',
        ),

        array(
            'country_name' => 'Congo - Brazzaville',
            'country_code' => '242',
        ),

        array(
            'country_name' => 'Congo - Kinshasa',
            'country_code' => '243',
        ),

        array(
            'country_name' => 'Cook Islands',
            'country_code' => '682',
        ),

        array(
            'country_name' => 'Costa Rica',
            'country_code' => '506',
        ),

        array(
            'country_name' => 'Côte d’Ivoire',
            'country_code' => '225',
        ),
        array(
            'country_name' => 'Czechia',
            'country_code' => '420',
        ),

        array(
            'country_name' => 'Croatia',
            'country_code' => '385',
        ),

        array(
            'country_name' => 'Cuba',
            'country_code' => '53',
        ),

        array(
            'country_name' => 'Curaçao',
            'country_code' => '5999',
        ),

        array(
            'country_name' => 'Cyprus',
            'country_code' => '357',
        ),

        array(
            'country_name' => 'Czech Republic',
            'country_code' => '420',
        ),

        array(
            'country_name' => 'Denmark',
            'country_code' => '45',
        ),

        array(
            'country_name' => 'Diego Garcia',
            'country_code' => '246',
        ),

        array(
            'country_name' => 'Djibouti',
            'country_code' => '253',
        ),

        array(
            'country_name' => 'Dominica',
            'country_code' => '1767',
        ),

        array(
            'country_name' => 'Dominican Republic',
            'country_code' => '1809',
        ),

        array(
            'country_name' => 'Easter Island',
            'country_code' => '56',
        ),

        array(
            'country_name' => 'Ecuador',
            'country_code' => '593',
        ),

        array(
            'country_name' => 'Egypt',
            'country_code' => '20',
        ),

        array(
            'country_name' => 'Swaziland',
            'country_code' => '268',
        ),

        array(
            'country_name' => 'El Salvador',
            'country_code' => '503',
        ),

        array(
            'country_name' => 'Ellipso (Mobile Satellite service)',
            'country_code' => '8812',
        ),

        array(
            'country_name' => 'EMSAT (Mobile Satellite service)',
            'country_code' => '88213',
        ),

        array(
            'country_name' => 'Equatorial Guinea',
            'country_code' => '240',
        ),

        array(
            'country_name' => 'Eritrea',
            'country_code' => '291',
        ),

        array(
            'country_name' => 'Estonia',
            'country_code' => '372',
        ),

        array(
            'country_name' => 'Eswatini',
            'country_code' => '268',
        ),

        array(
            'country_name' => 'Ethiopia',
            'country_code' => '251',
        ),

        array(
            'country_name' => 'Falkland Islands',
            'country_code' => '500',
        ),

        array(
            'country_name' => 'Faroe Islands',
            'country_code' => '298',
        ),

        array(
            'country_name' => 'Fiji',
            'country_code' => '679',
        ),

        array(
            'country_name' => 'Finland',
            'country_code' => '358',
        ),

        array(
            'country_name' => 'France',
            'country_code' => '33',
        ),

        array(
            'country_name' => 'French Antilles',
            'country_code' => '596',
        ),

        array(
            'country_name' => 'French Guiana',
            'country_code' => '594',
        ),
        array(
            'country_name' => 'French Southern Territories',
            'country_code' => '262',
        ),

        array(
            'country_name' => 'French Polynesia',
            'country_code' => '689',
        ),

        array(
            'country_name' => 'Gabon',
            'country_code' => '241',
        ),

        array(
            'country_name' => 'Gambia',
            'country_code' => '220',
        ),

        array(
            'country_name' => 'Georgia',
            'country_code' => '995',
        ),

        array(
            'country_name' => 'Germany',
            'country_code' => '49',
        ),

        array(
            'country_name' => 'Ghana',
            'country_code' => '233',
        ),

        array(
            'country_name' => 'Gibraltar',
            'country_code' => '350',
        ),

        array(
            'country_name' => 'Global Mobile Satellite System (GMSS)',
            'country_code' => '881',
        ),

        array(
            'country_name' => 'Globalstar (Mobile Satellite Service)',
            'country_code' => '8818',
        ),

        array(
            'country_name' => 'Greece',
            'country_code' => '30',
        ),

        array(
            'country_name' => 'Greenland',
            'country_code' => '299',
        ),

        array(
            'country_name' => 'Grenada',
            'country_code' => '1473',
        ),

        array(
            'country_name' => 'Guadeloupe',
            'country_code' => '590',
        ),

        array(
            'country_name' => 'Guam',
            'country_code' => '1671',
        ),

        array(
            'country_name' => 'Guatemala',
            'country_code' => '502',
        ),

        array(
            'country_name' => 'Guernsey',
            'country_code' => '441481',
        ),

        array(
            'country_name' => 'Guinea',
            'country_code' => '224',
        ),

        array(
            'country_name' => 'Guinea-Bissau',
            'country_code' => '245',
        ),

        array(
            'country_name' => 'Guyana',
            'country_code' => '592',
        ),

        array(
            'country_name' => 'Haiti',
            'country_code' => '509',
        ),

        array(
            'country_name' => 'Honduras',
            'country_code' => '504',
        ),

        array(
            'country_name' => 'Hong Kong SAR China',
            'country_code' => '852',
        ),
        array(
            'country_name' => 'Heard & McDonald Islands',
            'country_code' => '672',
        ),

        array(
            'country_name' => 'Hungary',
            'country_code' => '36',
        ),

        array(
            'country_name' => 'Iceland',
            'country_code' => '354',
        ),

        array(
            'country_name' => 'ICO Global (Mobile Satellite Service)',
            'country_code' => '8810',
        ),

        array(
            'country_name' => 'India',
            'country_code' => '91',
        ),

        array(
            'country_name' => 'Indonesia',
            'country_code' => '62',
        ),

        array(
            'country_name' => 'Inmarsat SNAC',
            'country_code' => '870',
        ),

        array(
            'country_name' => 'International Freephone Service (UIFN)',
            'country_code' => '800',
        ),

        array(
            'country_name' => 'International Networks',
            'country_code' => '882,883',
        ),

        array(
            'country_name' => 'International Premium Rate Service',
            'country_code' => '979',
        ),

        array(
            'country_name' => 'International Shared Cost Service (ISCS)',
            'country_code' => '808',
        ),

        array(
            'country_name' => 'Iran',
            'country_code' => '98',
        ),

        array(
            'country_name' => 'Iraq',
            'country_code' => '964',
        ),

        array(
            'country_name' => 'Ireland',
            'country_code' => '353',
        ),

        array(
            'country_name' => 'Iridium (Mobile Satellite service)',
            'country_code' => '8816,8817',
        ),

        array(
            'country_name' => 'Isle of Man',
            'country_code' => '44',
        ),

        array(
            'country_name' => 'Israel',
            'country_code' => '972',
        ),

        array(
            'country_name' => 'Italy',
            'country_code' => '39',
        ),

        array(
            'country_name' => 'Jamaica',
            'country_code' => '1',
        ),

        array(
            'country_name' => 'Jan Mayen',
            'country_code' => '4779',
        ),

        array(
            'country_name' => 'Japan',
            'country_code' => '81',
        ),

        array(
            'country_name' => 'Jersey',
            'country_code' => '441534',
        ),

        array(
            'country_name' => 'Jordan',
            'country_code' => '962',
        ),

        array(
            'country_name' => 'Kazakhstan',
            'country_code' => '7',
        ),

        array(
            'country_name' => 'Kenya',
            'country_code' => '254',
        ),

        array(
            'country_name' => 'Kiribati',
            'country_code' => '686',
        ),

        array(
            'country_name' => 'North Korea',
            'country_code' => '850',
        ),

        array(
            'country_name' => 'South Korea',
            'country_code' => '82',
        ),

        array(
            'country_name' => 'Kosovo',
            'country_code' => '383',
        ),

        array(
            'country_name' => 'Kuwait',
            'country_code' => '965',
        ),

        array(
            'country_name' => 'Kyrgyzstan',
            'country_code' => '996',
        ),

        array(
            'country_name' => 'Laos',
            'country_code' => '856',
        ),

        array(
            'country_name' => 'Latvia',
            'country_code' => '371',
        ),

        array(
            'country_name' => 'Lebanon',
            'country_code' => '961',
        ),

        array(
            'country_name' => 'Lesotho',
            'country_code' => '266',
        ),

        array(
            'country_name' => 'Liberia',
            'country_code' => '231',
        ),

        array(
            'country_name' => 'Libya',
            'country_code' => '218',
        ),

        array(
            'country_name' => 'Liechtenstein',
            'country_code' => '423',
        ),

        array(
            'country_name' => 'Lithuania',
            'country_code' => '370',
        ),

        array(
            'country_name' => 'Luxembourg',
            'country_code' => '352',
        ),

        array(
            'country_name' => 'Macau SAR China',
            'country_code' => '853',
        ),

        array(
            'country_name' => 'Madagascar',
            'country_code' => '261',
        ),

        array(
            'country_name' => 'Malawi',
            'country_code' => '265',
        ),

        array(
            'country_name' => 'Malaysia',
            'country_code' => '60',
        ),

        array(
            'country_name' => 'Maldives',
            'country_code' => '960',
        ),

        array(
            'country_name' => 'Mali',
            'country_code' => '223',
        ),

        array(
            'country_name' => 'Malta',
            'country_code' => '356',
        ),

        array(
            'country_name' => 'Marshall Islands',
            'country_code' => '692',
        ),

        array(
            'country_name' => 'Martinique',
            'country_code' => '596',
        ),

        array(
            'country_name' => 'Mauritania',
            'country_code' => '222',
        ),

        array(
            'country_name' => 'Mauritius',
            'country_code' => '230',
        ),

        array(
            'country_name' => 'Mayotte',
            'country_code' => '262',
        ),

        array(
            'country_name' => 'Mexico',
            'country_code' => '52',
        ),

        array(
            'country_name' => 'Micronesia',
            'country_code' => '691',
        ),

        array(
            'country_name' => 'Midway Island, USA',
            'country_code' => '1808',
        ),

        array(
            'country_name' => 'Moldova',
            'country_code' => '373',
        ),

        array(
            'country_name' => 'Monaco',
            'country_code' => '377',
        ),

        array(
            'country_name' => 'Mongolia',
            'country_code' => '976',
        ),

        array(
            'country_name' => 'Montenegro',
            'country_code' => '382',
        ),

        array(
            'country_name' => 'Montserrat',
            'country_code' => '1664',
        ),

        array(
            'country_name' => 'Morocco',
            'country_code' => '212',
        ),

        array(
            'country_name' => 'Mozambique',
            'country_code' => '258',
        ),

        array(
            'country_name' => 'Myanmar (Burma)',
            'country_code' => '95',
        ),

        array(
            'country_name' => 'Artsakh',
            'country_code' => '37447,37497',
        ),

        array(
            'country_name' => 'Namibia',
            'country_code' => '264',
        ),
        array(
            'country_name' => 'Palestinian Territories',
            'country_code' => '970',
        ),

        array(
            'country_name' => 'Nauru',
            'country_code' => '674',
        ),

        array(
            'country_name' => 'Nepal',
            'country_code' => '977',
        ),

        array(
            'country_name' => 'Netherlands',
            'country_code' => '31',
        ),

        array(
            'country_name' => 'Nevis',
            'country_code' => '1869',
        ),

        array(
            'country_name' => 'New Caledonia',
            'country_code' => '687',
        ),

        array(
            'country_name' => 'New Zealand',
            'country_code' => '64',
        ),

        array(
            'country_name' => 'Nicaragua',
            'country_code' => '505',
        ),

        array(
            'country_name' => 'Niger',
            'country_code' => '227',
        ),

        array(
            'country_name' => 'Nigeria',
            'country_code' => '234',
        ),

        array(
            'country_name' => 'Niue',
            'country_code' => '683',
        ),

        array(
            'country_name' => 'Norfolk Island',
            'country_code' => '6723',
        ),

        array(
            'country_name' => 'Macedonia',
            'country_code' => '389',
        ),

        array(
            'country_name' => 'Northern Cyprus',
            'country_code' => '90392',
        ),

        array(
            'country_name' => 'Northern Ireland',
            'country_code' => '4428',
        ),

        array(
            'country_name' => 'Northern Mariana Islands',
            'country_code' => '1670',
        ),

        array(
            'country_name' => 'Norway',
            'country_code' => '47',
        ),

        array(
            'country_name' => 'Oman',
            'country_code' => '968',
        ),

        array(
            'country_name' => 'Pakistan',
            'country_code' => '92',
        ),

        array(
            'country_name' => 'Palau',
            'country_code' => '680',
        ),

        array(
            'country_name' => 'Palestine, State of',
            'country_code' => '970',
        ),

        array(
            'country_name' => 'Panama',
            'country_code' => '507',
        ),

        array(
            'country_name' => 'Papua New Guinea',
            'country_code' => '675',
        ),

        array(
            'country_name' => 'Paraguay',
            'country_code' => '595',
        ),

        array(
            'country_name' => 'Peru',
            'country_code' => '51',
        ),

        array(
            'country_name' => 'Philippines',
            'country_code' => '63',
        ),

        array(
            'country_name' => 'Pitcairn Islands',
            'country_code' => '64',
        ),

        array(
            'country_name' => 'Poland',
            'country_code' => '48',
        ),

        array(
            'country_name' => 'Portugal',
            'country_code' => '351',
        ),

        array(
            'country_name' => 'Puerto Rico',
            'country_code' => '1787,1939',
        ),

        array(
            'country_name' => 'Qatar',
            'country_code' => '974',
        ),

        array(
            'country_name' => 'Réunion',
            'country_code' => '262',
        ),

        array(
            'country_name' => 'Romania',
            'country_code' => '40',
        ),

        array(
            'country_name' => 'Russia',
            'country_code' => '7',
        ),

        array(
            'country_name' => 'Rwanda',
            'country_code' => '250',
        ),

        array(
            'country_name' => 'Saba',
            'country_code' => '5994',
        ),

        array(
            'country_name' => 'St. Barthélemy',
            'country_code' => '590',
        ),

        array(
            'country_name' => 'St. Helena',
            'country_code' => '290',
        ),

        array(
            'country_name' => 'St. Kitts & Nevis',
            'country_code' => '1869',
        ),

        array(
            'country_name' => 'St. Lucia',
            'country_code' => '1758',
        ),

        array(
            'country_name' => 'St. Martin',
            'country_code' => '590',
        ),

        array(
            'country_name' => 'St. Pierre & Miquelon',
            'country_code' => '508',
        ),

        array(
            'country_name' => 'St. Vincent & Grenadines',
            'country_code' => '1784',
        ),

        array(
            'country_name' => 'Samoa',
            'country_code' => '685',
        ),

        array(
            'country_name' => 'San Marino',
            'country_code' => '378',
        ),

        array(
            'country_name' => 'São Tomé & Príncipe',
            'country_code' => '239',
        ),

        array(
            'country_name' => 'Saudi Arabia',
            'country_code' => '966',
        ),

        array(
            'country_name' => 'Senegal',
            'country_code' => '221',
        ),

        array(
            'country_name' => 'Serbia',
            'country_code' => '381',
        ),

        array(
            'country_name' => 'Seychelles',
            'country_code' => '248',
        ),

        array(
            'country_name' => 'Sierra Leone',
            'country_code' => '232',
        ),

        array(
            'country_name' => 'Singapore',
            'country_code' => '65',
        ),

        array(
            'country_name' => 'Sint Eustatius',
            'country_code' => '5993',
        ),

        array(
            'country_name' => 'Sint Maarten (Netherlands)',
            'country_code' => '1721',
        ),
        array(
            'country_name' => 'Sint Maarten',
            'country_code' => '721',
        ),

        array(
            'country_name' => 'Slovakia',
            'country_code' => '421',
        ),

        array(
            'country_name' => 'Slovenia',
            'country_code' => '386',
        ),

        array(
            'country_name' => 'Solomon Islands',
            'country_code' => '677',
        ),

        array(
            'country_name' => 'Somalia',
            'country_code' => '252',
        ),

        array(
            'country_name' => 'South Africa',
            'country_code' => '27',
        ),

        array(
            'country_name' => 'South Georgia & South Sandwich Islands',
            'country_code' => '500',
        ),

        array(
            'country_name' => 'South Ossetia',
            'country_code' => '99534',
        ),

        array(
            'country_name' => 'South Sudan',
            'country_code' => '211',
        ),

        array(
            'country_name' => 'Spain',
            'country_code' => '34',
        ),

        array(
            'country_name' => 'Sri Lanka',
            'country_code' => '94',
        ),

        array(
            'country_name' => 'Sudan',
            'country_code' => '249',
        ),

        array(
            'country_name' => 'Suriname',
            'country_code' => '597',
        ),

        array(
            'country_name' => 'Svalbard & Jan Mayen',
            'country_code' => '4779',
        ),

        array(
            'country_name' => 'Sweden',
            'country_code' => '46',
        ),

        array(
            'country_name' => 'Switzerland',
            'country_code' => '41',
        ),

        array(
            'country_name' => 'Syria',
            'country_code' => '963',
        ),

        array(
            'country_name' => 'Taiwan, Province of China',
            'country_code' => '886',
        ),

        array(
            'country_name' => 'Timor-Leste',
            'country_code' => '670',
        ),

        array(
            'country_name' => 'Tajikistan',
            'country_code' => '992',
        ),

        array(
            'country_name' => 'Tanzania',
            'country_code' => '255',
        ),

        array(
            'country_name' => 'Telecommunications for Disaster Relief by OCHA',
            'country_code' => '888',
        ),

        array(
            'country_name' => 'Thailand',
            'country_code' => '66',
        ),

        array(
            'country_name' => 'Thuraya (Mobile Satellite service)',
            'country_code' => '88216',
        ),

        array(
            'country_name' => 'Togo',
            'country_code' => '228',
        ),

        array(
            'country_name' => 'Tokelau',
            'country_code' => '690',
        ),

        array(
            'country_name' => 'Tonga',
            'country_code' => '676',
        ),

        array(
            'country_name' => 'Transnistria',
            'country_code' => '3732,3735',
        ),

        array(
            'country_name' => 'Trinidad & Tobago',
            'country_code' => '1868',
        ),

        array(
            'country_name' => 'Tristan da Cunha',
            'country_code' => '2908',
        ),

        array(
            'country_name' => 'Tunisia',
            'country_code' => '216',
        ),

        array(
            'country_name' => 'Turkey',
            'country_code' => '90',
        ),

        array(
            'country_name' => 'Turkmenistan',
            'country_code' => '993',
        ),

        array(
            'country_name' => 'Turks & Caicos Islands',
            'country_code' => '1649',
        ),

        array(
            'country_name' => 'Tuvalu',
            'country_code' => '688',
        ),

        array(
            'country_name' => 'Uganda',
            'country_code' => '256',
        ),
        array(
            'country_name' => 'U.S. Outlying Islands',
            'country_code' => '246',
        ),
        array(
            'country_name' => 'U.S. Virgin Islands',
            'country_code' => '1',
        ),

        array(
            'country_name' => 'Ukraine',
            'country_code' => '380',
        ),

        array(
            'country_name' => 'United Arab Emirates',
            'country_code' => '971',
        ),

        array(
            'country_name' => 'United Kingdom',
            'country_code' => '44',
        ),

        array(
            'country_name' => 'United States',
            'country_code' => '1',
        ),

        array(
            'country_name' => 'Universal Personal Telecommunications (UPT)',
            'country_code' => '878',
        ),

        array(
            'country_name' => 'Uruguay',
            'country_code' => '598',
        ),

        array(
            'country_name' => 'US Virgin Islands',
            'country_code' => '1340',
        ),

        array(
            'country_name' => 'Uzbekistan',
            'country_code' => '998',
        ),

        array(
            'country_name' => 'Vanuatu',
            'country_code' => '678',
        ),

        array(
            'country_name' => 'Vatican City',
            'country_code' => '3906698',
        ),

        array(
            'country_name' => 'Venezuela',
            'country_code' => '58',
        ),

        array(
            'country_name' => 'Vietnam',
            'country_code' => '84',
        ),

        array(
            'country_name' => 'Wake Island, USA',
            'country_code' => '1808',
        ),

        array(
            'country_name' => 'Wallis & Futuna',
            'country_code' => '681',
        ),

        array(
            'country_name' => 'Yemen',
            'country_code' => '967',
        ),
        array(
            'country_name' => 'Western Sahara',
            'country_code' => '212',
        ),

        array(
            'country_name' => 'Zambia',
            'country_code' => '260',
        ),

        array(
            'country_name' => 'Zanzibar',
            'country_code' => '259',
        ),

        array(
            'country_name' => 'Zimbabwe',
            'country_code' => '263',
        ),
    );

    /**
     * @var CollectionFactory
     */
    protected $countryCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        CollectionFactory    $countryCollectionFactory,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->countryCollectionFactory = $countryCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function getCountries($isReload = false)
    {
        if (!empty($this->countriesList) && !$isReload) {
            return $this->countriesList;
        }
        $options = $this->getCountryCollection()
            ->setForegroundCountries($this->getTopDestinations())
            ->toOptionArray();

        foreach ($options as $index => $option) {
            $initData = [
                'country_name' => '',
                'country_code' => '',
                'digits_number' => '',
            ];
            if (isset($option['label']) && isset($option['value']) && !empty($option['label']) && !empty($option['value'])) {
                $searchedIndex = array_search(strtolower($option['label']), array_map('strtolower', array_column($this->mappingCountry, 'country_name')));
                if ($searchedIndex !== false) {
                    if (isset($this->mappingCountry[$searchedIndex]['country_name']) && $this->mappingCountry[$searchedIndex]['country_name'] == 'Singapore') {
                        $this->mappingCountry[$searchedIndex]['digits_number'] = self::SG_DIGIT_NUMBER;
                    } elseif (!isset($this->mappingCountry[$searchedIndex]['digits_number'])) {
                        $this->mappingCountry[$searchedIndex]['digits_number'] = '';
                    }
                    $initData = $this->mappingCountry[$searchedIndex];
                }
            }
            $options[$index] = array_merge($option, $initData);
        }
        $this->countriesList = $options;
        return $this->countriesList;
    }

    public function getCountryCollection()
    {
        return $this->countryCollectionFactory->create()->loadByStore();
    }

    /**
     * Retrieve list of top destinations countries
     *
     * @return array
     */
    protected function getTopDestinations()
    {
        $destinations = (string)$this->scopeConfig->getValue(
            'general/country/destinations',
            ScopeInterface::SCOPE_STORE
        );
        return !empty($destinations) ? explode(',', $destinations) : [];
    }
}
