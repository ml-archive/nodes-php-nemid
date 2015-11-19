<?php

namespace Nodes\NemId\Login;
use Nodes\NemId\Core\Mode;

/**
 * Class Settings
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId
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
    protected $baseUrl, $uiMode, $privateKey, $privateKeyPassword, $certificate;

    /**
     * var boolean
     */
    protected $isTest;

    /**
     * @var boolean | string
     */
    protected $origin;

    /**
     * @var bool
     */
    protected $showCancelBtn;

    /**
     * Settings constructor.
     *
     * @param \Nodes\NemId\Mode|null $mode
     * @throws \Exception
     */
    public function __construct($mode = null)
    {
        // Fallback to default mode
        if(!$mode || !($mode instanceof Mode)) {
            $mode =  new Mode();
        }

        // Retrieve settings
        $this->settings = config('nemid');

        if(!$this->settings) {
            throw new \Exception('Missing config');
        }

        // Decide on mode and key in settings
        if($mode->isFromSettings()) {
            $this->isTest = (bool) $this->settings['test'];
        } else {
            $this->isTest = $mode->isTest();
        }

        // Find key for setting array
        $key = $this->isTest ? 'testSettings' : 'settings';

        // Subtract settings for mode
        $settings = $this->settings['login'][$key];

        // Init variables
        $this->baseUrl = $settings['baseUrl'];
        $this->uiMode = $settings['uiMode'];
        $this->origin = $settings['origin'];
        $this->showCancelBtn = $settings['showCancelBtn'];
        $this->privateKeyPassword = $settings['privateKeyPassword'];
        $this->privateKey = file_get_contents($settings['privateKeyLocation']);
        $this->certificate = file_get_contents($settings['certificateLocation']);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getCertificate()
    {
        return $this->certificate;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->privateKey;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getPrivateKeyPassword()
    {
        return $this->privateKeyPassword;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getUiMode()
    {
        return $this->uiMode;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function isTest()
    {
        return $this->isTest;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function hasOrigin()
    {
        return ! empty($this->origin);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool|string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function showCancelBtn()
    {
        return $this->showCancelBtn;
    }
}