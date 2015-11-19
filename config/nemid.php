<?php
return [
    'test' => false,
    'login' => [
        'settings'     => [
            'baseUrl'             => 'https://applet.danid.dk/',
            'uiMode'              => 'std',
            'origin'              => false,// Can be domain where iframe is hosted
            'showCancelBtn'       => true,
            'privateKeyPassword'  => '',
            'privateKeyLocation'  => 'privateKey.pem',
            'certificateLocation' => 'publicCert.pem',
        ],
        'testSettings' => [
            'baseUrl'             => 'https://appletk.danid.dk/',
            'uiMode'              => 'std',
            'origin'              => false,// Can be domain where iframe is hosted
            'showCancelBtn'       => true,
            'privateKeyPassword'  => '',
            'privateKeyLocation'  => 'testPrivateKey.pem',
            'certificateLocation' => 'testPublicCert.pem',
        ],
        'certificationDigests' => [
        ]
    ],
    'webservice' => [
        'settings' => [
            'server' => 'https://pidws.certifikat.dk/pid_serviceprovider_server/pidxml/',
            'certificateAndKey' => 'certificatePrivateKey.pem',
            'password' => '',
            'serviceId' => '',
            'proxy' => false
        ],
        'testSettings' => [
            'server' => 'https://pidws.pp.certifikat.dk/pid_serviceprovider_server/pidxml/',
            'certificateAndKey' => 'testCertificatePrivateKey.pem',
            'password' => '',
            'serviceId' => '',
            'proxy' => false
        ]
    ]
];