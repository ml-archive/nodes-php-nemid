<?php

namespace Nodes\NemId\Login\CertificationCheck;

use GuzzleHttp\Client;
use Nodes\NemId\Core\Nemid52Compat;
use Nodes\NemId\Core\OCSP;
use Nodes\NemId\Core\X509;
use Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidCertificateException;
use Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException;
use Nodes\NemId\Login\CertificationCheck\Models\Certificate;

/**
 * Class UserCertificateCheck.
 *
 * @author  Casper Rasmussen <cr@nodes.dk>
 */
class CertificationCheck
{
    /**
     * Constant for time format.
     */
    const GENERALIZED_TIME_FORMAT = 'YmdHis\Z';

    /**
     * @var array
     */
    protected $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Validates xml string.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $xml
     *
     * @return bool
     */
    public static function isXml(string $xml)
    {
        try {
            $document = new \DOMDocument();
            $result = $document->loadXML($xml, LIBXML_NOWARNING | LIBXML_NOERROR);

            return (bool) $result;
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
     *
     * @param $xml
     *
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidCertificateException
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException
     *
     * @return \Nodes\NemId\Login\CertificationCheck\Models\Certificate
     */
    public function checkAndReturnCertificate($xml)
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

        // Verify signature
        $this->verifySignature($xp, $leafCertificate);

        // Verify certificate chain
        $this->simpleVerifyCertificateChain($certificateChain);

        // Check ocsp
        if ($this->settings['login']['checkOcsp']) {
            $this->checkOcsp($certificateChain);
        }

        return $leafCertificate;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param \Nodes\NemId\Login\CertificationCheck\Models\Certificate $certificate
     *
     * @return string
     */
    private function certificateAsPem(Certificate $certificate)
    {
        return "-----BEGIN CERTIFICATE-----\n"
               .chunk_split(base64_encode($certificate->getCertificateDer()))
               .'-----END CERTIFICATE-----';
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $data
     * @param $signature
     * @param $signatureAlgorithm
     * @param $publicKeyPem
     *
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException
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

    /**
     * Verify certificate chain.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $certificateChain
     *
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidCertificateException
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException
     */
    protected function simpleVerifyCertificateChain(array $certificateChain)
    {
        // Init variable
        $keyUsages = ['digitalSignature'];
        $maxPathLength = 1; // as per RFC 5280: 'maximum number of non-self-issued intermediate certificates'
        $now = gmdate(self::GENERALIZED_TIME_FORMAT);

        // Check length
        if (count($certificateChain) != ($maxPathLength + 2)) {
            throw new InvalidCertificateException('Length of certificate chain is not '.$maxPathLength);
        }

        // Check key usage
        $leaf = $maxPathLength + 1;
        foreach ($keyUsages as $usage) {
            if (!($certificateChain[$leaf]->getTbsCertificate()['extensions']['keyUsage']['extnValue'][$usage])) {
                throw new InvalidCertificateException('Certificate has not keyUsage: '.$usage);
            }
        }

        // Loop over chain
        for ($i = $leaf; $i > 0; $i--) {
            $issuer = max($i - 1, 0);
            $der = $certificateChain[$i]->getTbsCertificate()['tbsCertificate_der'];
            // skip first null byte - number of unused bits at the end ...
            $signature = substr($certificateChain[$i]->getSignature(), 1);
            $this->verifyRSASignature($der, $signature, $certificateChain[$i]->getSignatureAlgorithm(), $this->certificateAsPem($certificateChain[$issuer]));

            if ($certificateChain[$i]->getTbsCertificate()['validity']['notBefore'] > $now
                && $now > $certificateChain[$i]->getTbsCertificate()['validity']['notAfter']
            ) {
                throw new InvalidCertificateException('certificate is outside it\'s validity time');
            }

            $extensions = $certificateChain[$issuer]->getTbsCertificate()['extensions'];

            if (!($extensions['basicConstraints']['extnValue']['cA'])) {
                throw new InvalidCertificateException('Issueing certificate has not cA = true');
            }

            if (isset($extensions['basicConstraints']['extnValue']['pathLenConstraint'])) {
                $pathLenConstraint = @$extensions['basicConstraints']['extnValue']['pathLenConstraint'];
            }

            if (!(empty($pathLenConstraint) || $pathLenConstraint < $leaf - $issuer - 1)) {
                throw new InvalidCertificateException('pathLenConstraint violated');
            }

            if (!($extensions['keyUsage']['extnValue']['keyCertSign'])) {
                throw new InvalidCertificateException('Issueing certificate has not keyUsage: keyCertSign');
            }
        }

        // first digest is for the root ...
        // check the root digest against a list of known root oces certificates
        $digest = hash('sha256', $certificateChain[0]->getCertificateDer());
        if (!in_array($digest, array_values($this->settings['login']['certificationDigests']))) {
            throw new InvalidCertificateException('Certificate chain not signed by any trustedroots');
        }
    }

    /**
     * Verifies the signed element in the returned signature.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param \DomXPath                                                $xp
     * @param \Nodes\NemId\Login\CertificationCheck\Models\Certificate $certificate
     *
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException
     */
    protected function verifySignature(\DomXPath $xp, Certificate $certificate)
    {
        $context = $xp->query('/openoces:signature/ds:Signature')->item(0);

        $signedElement = $xp->query('ds:Object[@Id="ToBeSigned"]', $context)->item(0)->C14N();
        $digestValue = base64_decode($xp->query('ds:SignedInfo/ds:Reference/ds:DigestValue', $context)
                                        ->item(0)->textContent);

        $signedInfo = $xp->query('ds:SignedInfo', $context)->item(0)->C14N();
        $signatureValue = base64_decode($xp->query('ds:SignatureValue', $context)->item(0)->textContent);
        $publicKey = openssl_get_publickey($this->certificateAsPem($certificate));

        // Check digest
        if (hash('sha256', $signedElement, true) != $digestValue) {
            throw new InvalidSignatureException('Digest did not match signed element');
        }

        // Check open ssl
        if (openssl_verify($signedInfo, $signatureValue, $publicKey, 'sha256WithRSAEncryption') != 1) {
            throw new InvalidSignatureException('Open ssl was not verified');
        }
    }

    /**
     * Does the ocsp check of the last certificate in the $certificateChain
     * $certificateChain contains root + intermediate + user certs.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $certificateChain
     *
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidCertificateException
     * @throws \Nodes\NemId\Login\CertificationCheck\Exceptions\InvalidSignatureException
     */
    protected function checkOcsp(array $certificateChain)
    {
        // the cert we are checking
        $certificate = array_pop($certificateChain);

        // assumed to be the issuer of the signing certificate of the ocsp response as well
        $issuer = array_pop($certificateChain);
        $ocspClient = new OCSP();

        $certID = $ocspClient->certOcspID([
            'issuerName'   => $issuer->getTbsCertificate()['subject_der'],
            //remember to skip the first byte it is the number of unused bits and it is always 0 for keys and certificates
            'issuerKey'    => substr($issuer->getTbsCertificate()['subjectPublicKeyInfo']['subjectPublicKey'], 1),
            'serialNumber' => $certificate->getTbsCertificate()['serialNumber'],
        ]);

        $ocsPreq = $ocspClient->request([$certID]);

        $url = $certificate->getTbsCertificate()['extensions']['authorityInfoAccess']['extnValue']['ocsp'][0]['accessLocation']['uniformResourceIdentifier'];

        // Init guzzle client
        $client = new Client();

        try {
            // Build params
            $params = [
                'headers'         => [
                    'Content-type: application/ocsp-request',
                ],
                'body'            => $ocsPreq,
                'connect_timeout' => 10,
            ];

            // Set proxy
            if ($proxy = $this->settings['login']['proxy']) {
                $params['proxy'] = $proxy;
            }

            // Execute request
            $response = $client->request('POST', $url, $params);
            $ocspResponse = $ocspClient->response($response->getBody()->getContents());

            // TODO, in php 7 responseStatus is malformed
            // $ocspResponse['responseStatus'] == 'malformedRequest'
        } catch (\Exception $e) {
            throw new InvalidCertificateException('Failed to check certificate: '.$e->getMessage());
        }
        // Check that the response was signed with the accompanying certificate
        $der = $ocspResponse['responseBytes']['BasicOCSPResponse']['tbsResponseData_der'];

        // Skip first null byte - the unused number bits in the end ...
        $signature = substr($ocspResponse['responseBytes']['BasicOCSPResponse']['signature'], 1);
        $signatureAlgorithm = $ocspResponse['responseBytes']['BasicOCSPResponse']['signatureAlgorithm'];

        $ocspCertificate = new Certificate($ocspResponse['responseBytes']['BasicOCSPResponse']['certs'][0]);

        $this->verifyRSASignature($der, $signature, $signatureAlgorithm, $this->certificateAsPem($ocspCertificate));

        // Check that the accompanying certificate was signed with the intermediate certificate
        $der = $ocspCertificate->getTbsCertificate()['tbsCertificate_der'];
        $signature = substr($ocspCertificate->getSignature(), 1);

        $this->verifyRSASignature($der, $signature, $ocspCertificate->getSignatureAlgorithm(), $this->certificateAsPem($issuer));

        $response = $ocspResponse['responseBytes']['BasicOCSPResponse']['tbsResponseData']['responses'][0];

        // Check OCSP response
        if ($ocspResponse['responseStatus'] !== 'successful') {
            throw new InvalidCertificateException('OCSP Response Status not successful');
        }

        // Check certificate status
        if ($response['certStatus'] !== 'good') {
            throw new InvalidCertificateException('OCSP Revocation status is not good');
        }

        // Check hash algorithm
        if ($response['certID']['hashAlgorithm'] !== 'sha-256') {
            throw new InvalidCertificateException('Hash algorithm is not correct');
        }

        // Check hash name
        if ($response['certID']['issuerNameHash'] !== $certID['issuerNameHash']) {
            throw new InvalidCertificateException('Name hash is not correct');
        }

        // Check key hash
        if ($response['certID']['issuerKeyHash'] !== $certID['issuerKeyHash']) {
            throw new InvalidCertificateException('Key hash is not correct');
        }

        // Check serial number
        if ($response['certID']['serialNumber'] !== $certID['serialNumber']) {
            throw new InvalidCertificateException('Serial number is not correct');
        }

        // Init time
        $now = gmdate(self::GENERALIZED_TIME_FORMAT);

        // Check OCSP revocation status
        if ($response['thisUpdate'] >= $now && $now <= $response['nextupdate']) {
            throw new InvalidCertificateException('OCSP Revocation status not current');
        }

        $ocspCertificateExtensions = $ocspCertificate->getTbsCertificate()['extensions'];

        // Check ocsp signing
        if (!$ocspCertificateExtensions['extKeyUsage']['extnValue']['ocspSigning']) {
            throw new InvalidCertificateException('ocspcertificate is not for ocspSigning');
        }

        // Check extension
        if ($ocspCertificateExtensions['ocspNoCheck']['extnValue'] !== null) {
            throw new InvalidCertificateException('ocspcertificate has not ocspNoCheck extension');
        }
    }

    /**
     * Extracts, parses and orders - leaf to root - the certificates returned by NemID.
     * $xp is the DomXPath object - the XML text isn't needed
     * $x509 is the parser object.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param \DOMXPath              $xp
     * @param \Nodes\NemId\Core\X509 $x509
     *
     * @throws InvalidCertificateException
     *
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

        $count = [];
        foreach ($certsbysubject as $cert) {
            $count[$cert['tbsCertificate']['subject_']] = 0;
            $count[$cert['tbsCertificate']['issuer_']] = 0;
        }
        // maybe hash of structure instead ...
        foreach ($certsbysubject as $cert) {
            $count[$cert['tbsCertificate']['subject_']]++;
            $count[$cert['tbsCertificate']['issuer_']]++;
        }

        $checks = array_count_values($count);

        // the subject of the leaf certificate appears only once ...
        if ($checks[1] != 1) {
            throw new InvalidCertificateException('Couldn\'t find leaf certificate');
        }

        $certpath = [];
        $leafcert = array_search(1, $count);

        // $certpath is sorted list root first ..
        while ($leafcert) {
            array_unshift($certpath, $certsbysubject[$leafcert]);
            //$certpath[] = $certsbysubject[$leafcert];
            $next = $certsbysubject[$leafcert]['tbsCertificate']['issuer_'];
            if ($next == $leafcert) {
                break;
            }
            $leafcert = $next;
        }

        $certificateObjectList = [];
        foreach ($certpath as $cert) {
            $certificateObjectList[] = new Certificate($cert);
        }

        return $certificateObjectList;
    }
}
