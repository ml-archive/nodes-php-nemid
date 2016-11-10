<?php
/**
 * Author: cr@nodes.dk.
 */

return [
    0 => [
        'short'   => 'OK',
        'danish'  => 'OK',
        'english' => 'OK',
    ],
    1 => [
        'short'   => 'NO_MATCH',
        'danish'  => 'CPR svarer ikke til PID',
        'english' => 'CPR does not match PID',
    ],
    2 => [
        'short'   => 'NO_PID',
        'danish'  => 'PID eksisterer ikke',
        'english' => 'PID doesn\'t exist',
    ],
    4 => [
        'short'   => 'NO_PID_IN_CERTIFICATE',
        'danish'  => 'PID kunne ikke findes i certifikatet',
        'english' => 'No PID in certificate',
    ],
    8 => [
        'short'   => 'NOT_AUTHORIZED_FOR_CPR_LOOKUP',
        'danish'  => 'Der er ikke rettighed til at foretage CPR opslag',
        'english' => 'Caller not authorized for CPR lookup',
    ],
    16 => [
        'short'   => 'BRUTE_FORCE_ATTEMPT_DETECTED',
        'danish'  => 'Forsøg på systematisk søgning på CPR',
        'english' => 'Brute force attempt detected',
    ],
    17 => [
        'short'   => 'NOT_AUTHORIZED_FOR_SERVICE_LOOKUP',
        'danish'  => 'Der er ikke rettighed til at foretage opslag på service',
        'english' => 'Caller not authorized for service lookup',
    ],
    4096 => [
        'short'   => 'NOT_PID_SERVICE_REQUEST',
        'danish'  => 'Modtaget message ikke pidCprRequest eller pidCprServerStatus',
        'english' => 'Non request XML received',
    ],
    8192 => [
        'short'   => 'XML_PARSE_ERROR',
        'danish'  => 'XML pakke kan ikke parses',
        'english' => 'Non parsable XML received',
    ],
    8193 => [
        'short'   => 'XML_VERSION_NOT_SUPPORTED',
        'danish'  => 'Version er ikke understøttet',
        'english' => 'Version not supported',
    ],
    8194 => [
        'short'   => 'PID_DOES_NOT_MATCH_BASE64_CERTIFICATE',
        'danish'  => 'PID stemmer med ikke med certifikat',
        'english' => 'PID does not match certifikat',
    ],
    8195 => [
        'short'   => 'MISSING_CLIENT_CERT',
        'danish'  => 'Klient certifikat ikke præsenteret',
        'english' => 'No client certificate presented',
    ],
    16384 => [
        'short'   => 'INTERNAL_ERROR',
        'danish'  => 'Intern DanID fejl',
        'english' => 'Internal DanID error',
    ],
];
