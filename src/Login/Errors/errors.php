<?php
/**
 * Author: cr@nodes.dk
 */

return [
    'APP001'           => [
        'english' => 'The applet calculated the digest of its parameters, and it did not match the digest that was submitted in the parameter paramsdigest. ',
        'code'        => 'APP001',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'APP002'           => [
        'english' => 'The sign danish was illegal, e.g. the HTML document contained illegal tags. ',
        'code'        => 'APP002',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'APP003'           => [
        'english' => 'An unrecoverable, internal error occurred in the applet. ',
        'code'        => 'APP003',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Kontakt NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'APP004'           => [
        'english' => 'Returned by the applet if it is unable to resume an existing user session and the No_Fallback parameter is specified. ',
        'code'        => 'APP004',
        'danish'        => 'Der er sket en teknisk fejl. Kontakt indehaveren af websitet',
    ],
    'APP005'           => [
        'english' => 'The user chose not to trust the certificate that can verify the signature on the applet.',
        'code'        => 'APP005',
        'danish'        => 'Du har ikke givet tilladelse til at NemID login kan k\\u00f8re p\\u00e5 din computer. Du skal give tilladelse for at kunne logge p\\u00e5.',
    ],
    'SRV001'           => [
        'english' => 'The signature on the applet parameters could not be verified by DanID. ',
        'code'        => 'SRV001',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'SRV002'           => [
        'english' => 'The authentication request could not be parsed by DanID. ',
        'code'        => 'SRV002',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'SRV003'           => [
        'english' => 'The time stamp of the authentication request was not within the allowed time span. ',
        'code'        => 'SRV003',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'SRV004'           => [
        'english' => 'An unrecoverable error occurred at DanID. ',
        'code'        => 'SRV004',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Kontakt NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'SRV005'           => [
        'english' => 'An unrecoverable error occurred at DanID. ',
        'code'        => 'SRV005',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Kontakt NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'SRV006'           => [
        'english' => 'The server lost the session it had established with the applet.',
        'code'        => 'SRV006',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen.',
    ],
    'CAN001'           => [
        'english' => 'The user chose to cancel an activation flow by pressing the "Afbryd" (Cancel) button.',
        'code'        => 'CAN001',
        'danish'        => 'Du har afbrudt aktiveringen.',
    ],
    'CAN002'           => [
        'english' => 'The user chose to cancel by pressing the "Afbryd" (Cancel) button. ',
        'code'        => 'CAN002',
        'danish'        => 'Du har afbrudt login.',
    ],
    'AUTH001'          => [
        'english' => 'The user exceeded the allowed number password attempts.',
        'code'        => 'AUTH001',
        'danish'        => 'Din NemID er sp\\u00e6rret. Kontakt venligst NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'AUTH003'          => [
        'english' => 'The user does not have an established agreement with the service provider. ',
        'code'        => 'AUTH003',
        'danish'        => 'Login er gennemf\\u00f8rt korrekt, men du har ikke adgang til tjenesten.',
    ],
    'AUTH004'          => [
        'english' => 'OTP device is quarantined due to too many failed authentication attempts. ',
        'code'        => 'AUTH004',
        'danish'        => 'Din NemID er midlertidigt l\\u00e5st og du kan endnu ikke logge p\\u00e5.',
    ],
    'AUTH005'          => [
        'english' => 'NemID is locked due to too many failed attempts to enter a password.',
        'code'        => 'AUTH005',
        'danish'        => 'Din NemID er sp\\u00e6rret. Kontakt venligst NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'AUTH006'          => [
        'english' => 'The user has run out of OTP codes, and does not have a pending one.',
        'code'        => 'AUTH006',
        'danish'        => 'Du har ikke flere koder p\\u00e5 n\\u00f8glekortet. Kontakt venligst NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'AUTH007'          => [
        'english' => 'NemID password is revoked due to too many failed entry attempts.',
        'code'        => 'AUTH007',
        'danish'        => 'Din NemID-adgangskode er sp\\u00e6rret pga. for mange fejlede fors\\u00f8g. Kontakt NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'AUTH008'          => [
        'english' => 'NemID is not activated and does not have an active Activation Password.',
        'code'        => 'AUTH008',
        'danish'        => 'Din NemID er ikke aktiveret og har ikke en aktiveringskode. Kontakt NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'AUTH009'          => [
        'english' => 'The user has not authenticated with 2-factor, sso is not possible.',
        'code'        => 'AUTH009',
        'danish'        => 'AUTH009',
    ],
    'LOCK001'          => [
        'english' => 'The user entered an incorrect password too many times. The OTP device is now quarantined.',
        'code'        => 'LOCK001',
        'danish'        => 'Du har angivet forkert bruger-id eller adgangskode 5 gange i tr\\u00e6k. NemID er nu sp\\u00e6rret i 8 timer. Kontakt evt. NemID-support p\\u00e5 tlf: 80 30 70 50. ',
    ],
    'LOCK002'          => [
        'english' => 'The user entered an incorrect password too many times. The OTP device is now locked permanently.',
        'code'        => 'LOCK002',
        'danish'        => 'Du har angivet forkert adgangskode for mange gange. Din NemID er sp\\u00e6rret. Kontakt evt. NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'LOCK003'          => [
        'english' => 'Number of allowed otp code attempts exceeded. The OTP device is revoked.',
        'code'        => 'LOCK003',
        'danish'        => 'Du har angivet forkert n\\u00f8gle for mange gange. Din NemID er sp\\u00e6rret. Kontakt evt. NemID-support p\\u00e5 tlf: 80 30 70 50.',
    ],
    'OCES001'          => [
        'english' => 'The user does not have an OCES, so he cannot authenticate with an OCES service provider. ',
        'code'        => 'OCES001',
        'danish'        => 'Du har kun sagt ja til at bruge NemID til netbank. \\u00d8nsker du at bruge NemID til andre hjemmesider klik her www.nemid.nu [https://www.nemid.nu/privat/bestil_nemid/nemid_i_netbank/]',
    ],
    'OCES002'          => [
        'english' => 'The user was not declared OCES-qualified ',
        'code'        => 'OCES002',
        'danish'        => '\\u00d8\\u0098nsker du at bruge NemID til andet end netbank klik her www.nemid.nu [https://www.nemid.nu/privat/bestil_nemid/]',
    ],
    'OCES003'          => [
        'english' => 'The user does not have OCES on this OTP device, but another of the user\'s devices has OCES. ',
        'code'        => 'OCES003',
        'danish'        => 'Der er ikke knyttet et certifikat til det NemID du har fors\\u00f8gt at logge p\\u00e5 med.',
    ],
    'OCES004'          => [
        'english' => 'The user is OCES-unqualified, eg. due to age or lack of a Danish CPR-number, and is not able to obtain an OCES certificate at all. ',
        'code'        => 'OCES004',
        'danish'        => 'Du kan ikke bruge NemID til andet end netbank. Det kan skyldes at du er under 15 \\u00e5r eller at du ikke har et dansk CPRnummer. ',
    ],
    'OCES005'          => [
        'english' => 'An error occurred when the CA tried to issue a certificate. ',
        'code'        => 'OCES005',
        'danish'        => 'Der er opst\\u00e5et en teknisk fejl. Fors\\u00f8g igen. Kontakt [TU], hvis problemet forts\\u00e6tter.',
    ],
    'OCES006'          => [
        'english' => 'The user has only inaccessible or inactive keys on any of his devices ',
        'code'        => 'OCES006',
        'danish'        => 'Du har ikke knyttet et aktivt OCES-certifikat til NemID. Klik her for at bestille et nyt OCES-certifikat www.nemid.nu [https://www.nemid.nu/privat/bestil_nemid/]',
    ],
    'cancel'           => [
        'english' => 'User chose to cancel the operation.',
        'code'        => 'cancel',
        'danish'        => 'Du har afbrudt login.',
    ],
    'cancelsign'       => [
        'english' => 'User chose to cancel the operation.',
        'code'        => 'cancelsign',
        'danish'        => 'Du har afbrudt signering.',
    ],
];