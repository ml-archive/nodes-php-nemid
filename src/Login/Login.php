<?php

namespace Nodes\NemId\Login;

use Nodes\NemId\Core\Nemid52Compat;

class Login
{
    /**
     * @var int
     */
    protected $timeStamp;

    /**
     * @var string
     */
    protected $iFrameUrl;
    protected $params;

    /**
     * Login constructor.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $settings
     * @param null  $mode
     */
    public function __construct(array $settings, $mode = null)
    {
        $this->settings = new Settings($settings, $mode);
        $this->timeStamp = (string) round(microtime(true) * 1000);

        $this->generateIFrameUrl();
        $this->generateParams();
    }

    /**
     * Generates the Iframe url by combining baseUrl, uiMode and timeStamp.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    private function generateIFrameUrl()
    {
        $this->iFrameUrl =
            $this->settings->getBaseUrl().'launcher/'.$this->settings->getUiMode().'/'.$this->timeStamp;
    }

    /**
     * Generate the params for Iframe.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    private function generateParams()
    {
        // Trim certificate
        $certificate = preg_replace(
            '/(-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s)/s',
            '',
            $this->settings->getCertificate()
        );

        // Init start params
        $params = [
            'SP_CERT'    => $certificate,
            'CLIENTFLOW' => 'Oceslogin2',
            'TIMESTAMP'  => $this->timeStamp,
            'LANGUAGE'   => $this->settings->getLanguage(),
        ];

        // Add origin if set
        if ($this->settings->hasOrigin()) {
            $params['ORIGIN'] = $this->settings->getOrigin();
        }

        // Remove cancel btn if set
        if (!$this->settings->showCancelBtn()) {
            $params['DO_NOT_SHOW_CANCEL'] = 'TRUE';
        }

        // Sort & normalize
        uksort($params, 'strnatcasecmp');
        $normalized = '';
        foreach ($params as $name => $value) {
            $normalized .= $name.$value;
        }

        // UTF8 encode it
        $normalized = utf8_encode($normalized);

        // Base64 and SHA256 Encode it
        $params['PARAMS_DIGEST'] = base64_encode(hash('sha256', $normalized, true));

        // Generate private key
        $privateKey =
            openssl_pkey_get_private($this->settings->getPrivateKey(), $this->settings->getPrivateKeyPassword());

        // Sign digest
        $signedDigest = Nemid52Compat::openSslSign($normalized, $privateKey, 'sha256WithRSAEncryption');

        // Base64 encode
        $params['DIGEST_SIGNATURE'] = base64_encode($signedDigest);

        // Json encode
        $encodedParams = json_encode($params, JSON_UNESCAPED_SLASHES);

        $this->params = $encodedParams;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getIFrameUrl()
    {
        return $this->iFrameUrl;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->settings->getBaseUrl();
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return \Nodes\NemId\Login\Settings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @author Tim Johannessen <twj@smbsolutions.dk>
     *
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->settings->setLanguage($language);
        $this->generateParams();
    }
}
