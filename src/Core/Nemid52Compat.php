<?php

namespace Nodes\NemId\Core;

/**
 * Class Nemid52Compat.
 *
 * @author  cr@nodes.dk
 *          Original taken from https://code.google.com/p/nemid-php/
 */
class Nemid52Compat
{
    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $data
     * @param $privateKeyId
     * @param $signatureAlgorithm
     *
     * @return mixed
     */
    public static function openSslSign($data, $privateKeyId, $signatureAlgorithm)
    {
        $eb = self::myRsaShaEncode($data, $privateKeyId, $signatureAlgorithm);
        openssl_private_encrypt($eb, $signature, $privateKeyId, OPENSSL_NO_PADDING);

        return $signature;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $data
     * @param $signature
     * @param $publicKeyId
     * @param $signatureAlgorithm
     *
     * @return int 1|0
     */
    public static function openSslVerify($data, $signature, $publicKeyId, $signatureAlgorithm)
    {
        openssl_public_decrypt($signature, $decryptedSignature, $publicKeyId, OPENSSL_NO_PADDING);
        $eb = self::myRsaShaEncode($data, $publicKeyId, $signatureAlgorithm);

        return $decryptedSignature === $eb ? 1 : 0;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $data
     * @param $keyId
     * @param $signatureAlgorithmLong
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function myRsaShaEncode($data, $keyId, $signatureAlgorithmLong)
    {
        // Init possible algorithms
        $algorithms = [
            'sha1WithRSAEncryption'   => ['alg' => 'sha1', 'oid' => '1.3.14.3.2.26'],
            'sha256WithRSAEncryption' => ['alg' => 'sha256', 'oid' => '2.16.840.1.101.3.4.2.1'],
            'sha384WithRSAEncryption' => ['alg' => 'sha384', 'oid' => '2.16.840.1.101.3.4.2.2'],
            'sha512WithRSAEncryption' => ['alg' => 'sha512', 'oid' => '2.16.840.1.101.3.4.2.3'],
        ];

        // Returns an array with the key details
        $pInfo = openssl_pkey_get_details($keyId);

        if (empty($algorithms[$signatureAlgorithmLong])) {
            throw new \Exception('Unsupported signature algorithm: '.$signatureAlgorithmLong);
        }

        // find the alg in options
        $signatureAlgorithm = $algorithms[$signatureAlgorithmLong]['alg'];

        // Find iod
        $oid = $algorithms[$signatureAlgorithmLong]['oid'];

        // Hash
        $digest = hash($signatureAlgorithm, $data, true);

        $temp = self::sequence(self::sequence(self::s2oid($oid)."\x05\x00").self::octetstring($digest));
        $psLen = $pInfo['bits'] / 8 - (strlen($temp) + 3);

        $eb = "\x00\x01".str_repeat("\xff", $psLen)."\x00".$temp;

        return $eb;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $pdu
     *
     * @return string
     */
    public static function sequence($pdu)
    {
        return "\x30".self::len($pdu).$pdu;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $s
     *
     * @return string
     */
    public static function s2oid($s)
    {
        $e = explode('.', $s);
        $der = chr(40 * $e[0] + $e[1]);

        foreach (array_slice($e, 2) as $c) {
            $mask = 0;
            $derrev = '';
            while ($c) {
                $derrev .= chr(bcmod($c, 128) + $mask);
                $c = bcdiv($c, 128, 0);
                $mask = 128;
            }
            $der .= strrev($derrev);
        }

        return "\x06".self::len($der).$der;
    }

    public static function octetstring($s)
    {
        return "\x04".self::len($s).$s;
    }

    public static function len($i)
    {
        $i = strlen($i);
        if ($i <= 127) {
            $res = pack('C', $i);
        } elseif ($i <= 255) {
            $res = pack('CC', 0x81, $i);
        } elseif ($i <= 65535) {
            $res = pack('Cn', 0x82, $i);
        } else {
            $res = pack('CN', 0x84, $i);
        }

        return $res;
    }
}
