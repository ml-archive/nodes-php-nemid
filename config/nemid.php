<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Test
    |--------------------------------------------------------------------------
    | Default setting for test or production mode
    */
    'test' => false,
    /*
    |--------------------------------------------------------------------------
    | Login settings for Nem-Id
    |--------------------------------------------------------------------------
    |
    */
    'login' => [
        'settings'     => [
            'baseUrl'             => 'https://applet.danid.dk/', // Official url for production
            'uiMode'              => 'lmt', // lmt / std
            'origin'              => false, // Can be domain where iframe is hosted
            'showCancelBtn'       => true, // Show the cancel button in the iframe
            'privateKeyPassword'  => '', // Password for the private key
            'privateKeyLocation'  => 'privateKey.pem', // Location for private key
            'certificateLocation' => 'publicCert.pem', // Location for public certificate
            'language'            => 'da', // Supported languages: da (Danish, default), en (English), kl (Greenlandic)
        ],
        'testSettings' => [
            'baseUrl'             => 'https://appletk.danid.dk/', // Official url for testing environment
            'uiMode'              => 'lmt', // lmt / std
            'origin'              => false, // Can be domain where iframe is hosted
            'showCancelBtn'       => true, // Show the cancel button in the iframe
            'privateKeyPassword'  => '', // Password for the private key
            'privateKeyLocation'  => 'testPrivateKey.pem', // Location for private key
            'certificateLocation' => 'testPublicCert.pem', // Location for public certificate
            'language'            => 'da', // Supported languages: da (Danish, default), en (English), kl (Greenlandic)
        ],

        // Check for certificate matching after login
        'certificationDigests' => [
        ],
        'checkOcsp' => true, // The certificate can be validated through a external request
        'proxy'     => false, // Since you only have 10 ip whitelisted, it can be smart to proxy the ip calls
    ],
    /*
    |--------------------------------------------------------------------------
    | Webservice settings Nem-Id
    |--------------------------------------------------------------------------
    |
    */
    'webservice' => [
        'settings' => [
            'server'            => 'https://pidws.certifikat.dk/pid_serviceprovider_server/pidxml/', // Official url for production
            'certificateAndKey' => 'certificatePrivateKey.pem', // Location for certificateAndPrivateKey
            'password'          => '', // Password for certificateAndPrivateKey
            'serviceId'         => '', // ServiceId also called SPID
            'proxy'             => false, // Since you only have 10 ip whitelisted, it can be smart to proxy the ip calls
        ],
        'testSettings' => [
            'server'            => 'https://pidws.pp.certifikat.dk/pid_serviceprovider_server/pidxml/', // Official url for testing environment
            'certificateAndKey' => 'testCertificatePrivateKey.pem', // Location for certificateAndPrivateKey
            'password'          => '', // Password for certificateAndPrivateKey
            'serviceId'         => '', // ServiceId also called SPID
            'proxy'             => false, // Since you only have 10 ip whitelisted, it can be smart to proxy the ip calls
        ],
    ],
];
