<?php

namespace Nodes\NemId\Login;

use Nodes\NemId\Core\Mode;

/**
 * Class Settings.
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 */
class Settings
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var string
     */
    protected $baseUrl;
    protected $uiMode;
    protected $privateKey;
    protected $privateKeyPassword;
    protected $certificate;
    protected $language;

    /**
     * var boolean.
     */
    protected $isTest;

    /**
     * @var bool | string
     */
    protected $origin;

    /**
     * @var bool
     */
    protected $showCancelBtn;

    /**
     * Settings constructor.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $settings
     * @param null  $mode
     *
     * @throws \Exception
     */
    public function __construct(array $settings, $mode = null)
    {
        // Fallback to default mode
        if (!$mode || !($mode instanceof Mode)) {
            $mode = new Mode();
        }

        $this->settings = $settings;

        // Decide on mode and key in settings
        if ($mode->isFromSettings()) {
            $this->isTest = (bool) $this->settings['test'];
        } else {
            $this->isTest = $mode->isTest();
        }

        // Find key for setting array
        $key = $this->isTest ? 'testSettings' : 'settings';

        // Subtract settings for mode
        $settings = $this->settings['login'][$key];

        // prevent update errors
        if (!isset($settings['language'])) {
            $settings['language'] = 'da';
        }

        // Init variables
        $this->baseUrl = $settings['baseUrl'];
        $this->uiMode = $settings['uiMode'];
        $this->origin = $settings['origin'];
        $this->showCancelBtn = $settings['showCancelBtn'];
        $this->privateKeyPassword = $settings['privateKeyPassword'];
        $this->privateKey = file_get_contents($settings['privateKeyLocation']);
        $this->certificate = file_get_contents($settings['certificateLocation']);
        $this->language = $settings['language'] ?: 'da';
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getPrivateKeyPassword()
    {
        return $this->privateKeyPassword;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getUiMode()
    {
        return $this->uiMode;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->isTest;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    public function hasOrigin()
    {
        return !empty($this->origin);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool|string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    public function showCancelBtn()
    {
        return $this->showCancelBtn;
    }

    /**
     * @author Tim Johannessen <twj@smbsolutions.dk>
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @author Tim Johannessen <twj@smbsolutions.dk>
     *
     * @param string $language
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
    }
}
