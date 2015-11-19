<?php

namespace Nodes\NemId\UserCertificateCheck;

use Nodes\NemId\Core\Nemid52Compat;
use Nodes\NemId\Core\X509;
use Nodes\NemId\UserCertificateCheck\Exception\InvalidCertificateException;
use Nodes\NemId\UserCertificateCheck\Exception\InvalidSignatureException;
use Nodes\NemId\UserCertificateCheck\Model\Certificate;

/**
 * Class UserCertificateCheck
 * @author  Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId\UserCertificateCheck
 */
class UserCertificateCheck
{
    /**
     * Constant for time format
     */
    const GENERALIZED_TIME_FORMAT = 'YmdHis\Z';

    /**
     * Validates xml string
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param $xml
     * @return bool
     */
    public static function isXml($xml)
    {
        try {
            $document = new \DOMDocument();
            $document->loadXML($xml);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Does the checks according to 'Implementation instructions for NemID' p. 35.
     * 1. Validate the signature on XMLDSig.
     * 2. Extract the certificate from XMLDSig.
     * 3. Validate the certificate and identify CA as OCES I or OCES II throughout the whole certificate chain to the
     * root certificate.
     * 4. Check that the certificate has not expired.
     * 5. Check that the certificate has not been revoked.
     * 6. Extract the PID or RID from the certificate.
     * 7. Translate the PID or RID to a CPR number.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param            $xml
     * @param            $trustedRootDigest
     * @param bool|false $checkOcsp
     * @return \Nodes\NemId\UserCertificateCheck\Model\Certificate
     * @throws \Nodes\NemId\UserCertificateCheck\Exception\InvalidCertificateException
     * @throws \Nodes\NemId\UserCertificateCheck\Exception\InvalidSignatureException
     */
    public function checkAndReturnCertificate($xml, $trustedRootDigest, $checkOcsp = false)
    {
        // Parse the xml
        $document = new \DOMDocument();
        $document->loadXML($xml);

        // Create DomXPath
        $xp = new \DomXPath($document);
        $xp->registerNamespace('openoces', 'http://www.openoces.org/2006/07/signature#');
        $xp->registerNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');

        // Init X509
        $x509 = new X509();

        // Convert to certificates
        $certificateChain = $this->xml2certs($xp, $x509);

        // Extract leaf certificate from chain
        $leafCertificate = end($certificateChain);

        // Init path length
        $nemIdFixedPathLength = 1; # as per RFC 5280: 'maximum number of non-self-issued intermediate certificates'

        // Verify signature
        $this->verifySignature($xp, $leafCertificate);

        // Verify certificate chain
//        $this->simpleVerifyCertificateChain($certificateChain, array('digitalSignature'), $trustedRootDigest, $nemIdFixedPathLength);

        // Check ocsp
        if ($checkOcsp) {
            die('Not supported');
            $this->checkOcsp($certificateChain, $x509);
        }

        return $leafCertificate;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param \Nodes\NemId\UserCertificateCheck\Model\Certificate $certificate
     * @return string
     */
    private function certificateAsPem(Certificate $certificate)
    {
        return "-----BEGIN CERTIFICATE-----\n"
        . chunk_split(base64_encode($certificate->getCertificateDer()))
        . "-----END CERTIFICATE-----";
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param $data
     * @param $signature
     * @param $signatureAlgorithm
     * @param $publicKeyPem
     * @throws \Nodes\NemId\UserCertificateCheck\Exception\InvalidSignatureException
     */
    protected function verifyRSASignature($data, $signature, $signatureAlgorithm, $publicKeyPem)
    {
        $publicKey = openssl_get_publickey($publicKeyPem);
        if (!$publicKey) {
            throw new InvalidSignatureException(openssl_error_string());
        }
        if (Nemid52Compat::openSslVerify($data, $signature, $publicKey, $signatureAlgorithm) != 1) {
            throw new InvalidSignatureException(openssl_error_string());
        }
    }


    protected function simpleVerifyCertificateChain(array $certificateChain, $keyUsages, $trustedRootDigest, $maxPathLength)
    {
        // Check length
        if(sizeof($certificateChain) != ($maxPathLength + 2)) {
            throw new InvalidCertificateException('Length of certificate chain is not ' . $maxPathLength);
        }

        // Check key usage
        $leaf = $maxPathLength + 1;
        foreach ($keyUsages as $usage) {
            if(!($certificateChain[$leaf]->getTbsCertificate()['extensions']['keyUsage']['extnValue'][$usage])) {
                throw new InvalidCertificateException('Certificate has not keyUsage: ' . $usage);
            }
        }

        // Generate timestamp in format
        $now = gmdate(self::GENERALIZED_TIME_FORMAT);

        // Loop over chain
        for ($i = $leaf; $i > 0; $i--) {
            $issuer = max($i - 1, 0);
            $der = $certificateChain[$i]->getTbsCertificate()['tbsCertificate_der'];
            # skip first null byte - number of unused bits at the end ...
            $signature = substr($certificateChain[$i]->getSignature(), 1);
            $this->verifyRSASignature($der, $signature, $certificateChain[$i]->getSignatureAlgorithm(), $this->certificateAsPem($certificateChain[$issuer]));

            if($certificateChain[$i]->getTbsCertificate()['validity']['notBefore'] > $now
                && $now > $certificateChain[$i]->getTbsCertificate()['validity']['notAfter']) {

                throw new InvalidCertificateException('certificate is outside it\'s validity time');
            }

            $extensions = $certificateChain[$issuer]->getTbsCertificate()['extensions'];

            if(!($extensions['basicConstraints']['extnValue']['cA'])) {
                throw new InvalidCertificateException('Issueing certificate has not cA = true');
            }

            if (isset($extensions['basicConstraints']['extnValue']['pathLenConstraint'])) {
                $pathLenConstraint = @$extensions['basicConstraints']['extnValue']['pathLenConstraint'];
            }

            if(!(empty($pathLenConstraint) || $pathLenConstraint < $leaf - $issuer - 1)) {
                throw new InvalidCertificateException('pathLenConstraint violated');
            }

            if(!($extensions['keyUsage']['extnValue']['keyCertSign'])) {
                throw new InvalidCertificateException('Issueing certificate has not keyUsage: keyCertSign');
            }
        }

        # first digest is for the root ...
        # check the root digest against a list of known root oces certificates
        $digest = hash('sha256', $certificateChain[0]->getCertificateDer());
        if(!in_array($digest, array_values($trustedRootDigest))) {
            throw new InvalidCertificateException('Certificate chain not signed by any trustedroots');
        }
    }

    /**
     * Verifies the signed element in the returned signature
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param \DomXPath                                           $xp
     * @param \Nodes\NemId\UserCertificateCheck\Model\Certificate $certificate
     * @throws \Nodes\NemId\UserCertificateCheck\Exception\InvalidSignatureException
     */
    protected function verifySignature(\DomXPath $xp, Certificate $certificate)
    {
        $context = $xp->query('/openoces:signature/ds:Signature')->item(0);

        $signedElement = $xp->query('ds:Object[@Id="ToBeSigned"]', $context)->item(0)->C14N();
        $digestValue = base64_decode($xp->query('ds:SignedInfo/ds:Reference/ds:DigestValue', $context)->item(0)->textContent);

        $signedInfo = $xp->query('ds:SignedInfo', $context)->item(0)->C14N();
        $signatureValue = base64_decode($xp->query('ds:SignatureValue', $context)->item(0)->textContent);
        $publicKey = openssl_get_publickey($this->certificateAsPem($certificate));

        $hash = hash('sha256', $signedElement, true);

        if (!(($hash == $digestValue) && openssl_verify($signedInfo, $signatureValue, $publicKey, 'sha256WithRSAEncryption') == 1)) {
            throw new InvalidSignatureException();
        }
    }

    /**
     * Does the ocsp check of the last certificate in the $certificateChain
     * $certificateChain contains root + intermediate + user certs
     */
    protected function checkOcsp($certificateChain, $x509)
    {
        $certificate = array_pop($certificateChain); # the cert we are checking
        $issuer = array_pop($certificateChain); # assumed to be the issuer of the signing certificate of the ocsp response as well
        $ocspclient = new OCSP();

        $certID = $ocspclient->certOcspID(array(
            'issuerName' => $issuer['tbsCertificate']['subject_der'],

            #remember to skip the first byte it is the number of unused bits and it is always 0 for keys and certificates
            'issuerKey' => substr($issuer['tbsCertificate']['subjectPublicKeyInfo']['subjectPublicKey'], 1),
            'serialNumber' => $certificate['tbsCertificate']['serialNumber']));

        $ocspreq = $ocspclient->request(array($certID));

        $url = $certificate['tbsCertificate']['extensions']['authorityInfoAccess']['extnValue']['ocsp'][0]['accessLocation']['uniformResourceIdentifier'];

        $stream_options = array(
            'http' => array(
                'ignore_errors' => false,
                'method' => 'POST',
                'header' => 'Content-type: application/ocsp-request' . "\r\n",
                'content' => $ocspreq,
                'timeout' => 5,
            ),
        );

        $context = stream_context_create($stream_options);
        $derresponse = file_get_contents($url, null, $context);

        $ocspresponse = $ocspclient->response($derresponse);

        /* check that the response was signed with the accompanying certificate */
        $der = $ocspresponse['responseBytes']['BasicOCSPResponse']['tbsResponseData_der'];
        # skip first null byte - the unused number bits in the end ...
        $signature = substr($ocspresponse['responseBytes']['BasicOCSPResponse']['signature'], 1);
        $signatureAlgorithm = $ocspresponse['responseBytes']['BasicOCSPResponse']['signatureAlgorithm'];

        $ocspcertificate = $ocspresponse['responseBytes']['BasicOCSPResponse']['certs'][0];

        $this->verifyRSASignature($der, $signature, $signatureAlgorithm, $this->certificateAsPem($ocspcertificate));

        /* check that the accompanying certificate was signed with the intermediate certificate */
        $der = $ocspcertificate['tbsCertificate']['tbsCertificate_der'];
        $signature = substr($ocspcertificate['signature'], 1);

        $this->verifyRSASignature($der, $signature, $ocspcertificate['signatureAlgorithm'], $this->certificateAsPem($issuer));

        $resp = $ocspresponse['responseBytes']['BasicOCSPResponse']['tbsResponseData']['responses'][0];

        $ocspresponse['responseStatus'] === 'successful' or trigger_error("OCSP Response Status not 'successful'", E_USER_ERROR);
        $resp['certStatus'] === 'good' or trigger_error("OCSP Revocation status is not 'good'", E_USER_ERROR);
        $resp['certID']['hashAlgorithm'] === 'sha-256'
        && $resp['certID']['issuerNameHash'] === $certID['issuerNameHash']
        && $resp['certID']['issuerKeyHash'] === $certID['issuerKeyHash']
        && $resp['certID']['serialNumber'] === $certID['serialNumber']
        or trigger_error("OCSP Revocation, mismatch between original and checked certificate", E_USER_ERROR);
        $now = gmdate(self::GENERALIZED_TIME_FORMAT);
        $resp['thisUpdate'] <= $now && $now <= $resp['nextupdate']
        or trigger_error("OCSP Revocation status not current: {$returnedCertResponse['thisUpdate']} <= $now <= {$returnedCertResponse['nextupdate']}", E_USER_ERROR);

        $ocspcertificateextns = $ocspcertificate['tbsCertificate']['extensions'];
        $ocspcertificateextns['extKeyUsage']['extnValue']['ocspSigning'] or trigger_error('ocspcertificate is not for ocspSigning', E_USER_ERROR);
        $ocspcertificateextns['ocspNoCheck']['extnValue'] === null or trigger_error('ocspcertificate has not ocspNoCheck extension', E_USER_ERROR);
    }

    /**
     * Extracts, parses and orders - leaf to root - the certificates returned by NemID.
     * $xp is the DomXPath object - the XML text isn't needed
     * $x509 is the parser object
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param \DOMXPath              $xp
     * @param \Nodes\NemId\Core\X509 $x509
     * @return array
     */
    protected function xml2certs(\DOMXPath $xp, X509 $x509)
    {
        $nodeList = $xp->query('/openoces:signature/ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate');

        foreach ($nodeList as $node) {
            $cert = $node->nodeValue;
            $certhash = $x509->certificate(base64_decode($cert));
            $certsbysubject[$certhash['tbsCertificate']['subject_']] = $certhash;
        }

        $count = array();
        foreach ($certsbysubject as $cert) {
            $count[$cert['tbsCertificate']['subject_']] = 0;
            $count[$cert['tbsCertificate']['issuer_']] = 0;
        }
        # maybe hash of structure instead ...
        foreach ($certsbysubject as $cert) {
            $count[$cert['tbsCertificate']['subject_']]++;
            $count[$cert['tbsCertificate']['issuer_']]++;
        }

        $checks = array_count_values($count);

        # the subject of the leaf certificate appears only once ...
        if ($checks[1] != 1) {
            trigger_error("Couldn't find leaf certificate ...", E_USER_ERROR);
        }

        $certpath = array();
        $leafcert = array_search(1, $count);

        # $certpath is sorted list root first ..
        while ($leafcert) {
            array_unshift($certpath, $certsbysubject[$leafcert]);
            #$certpath[] = $certsbysubject[$leafcert];
            $next = $certsbysubject[$leafcert]['tbsCertificate']['issuer_'];
            if ($next == $leafcert)
                break;
            $leafcert = $next;
        }

        $certificateObjectList = [];
        foreach($certpath as $cert) {
            $certificateObjectList[] = new Certificate($cert);
        }

        return $certificateObjectList;
    }
}
