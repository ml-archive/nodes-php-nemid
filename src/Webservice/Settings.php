<?php

namespace Nodes\NemId\Webservice;

use Nodes\NemId\Core\Mode;

/**
 * Class Settings.
 *
 * @author  Casper Rasmussen <cr@nodes.dk>
 */
class Settings
{
    /**
     * @var bool
     */
    protected $isTest;

    /**
     * @var string
     */
    protected $server;

    /**
     * @var string
     */
    protected $certificateAndKey;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $serviceId;

    /**
     * @var string|false
     */
    protected $proxy;

    /**
     * Settings constructor.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param array $settings
     * @param null  $mode
     */
    public function __construct(array $settings, $mode = null)
    {
        // Fallback to default mode
        if (!$mode || !($mode instanceof Mode)) {
            $mode = new Mode();
        }

        // Retrieve settings
        $this->settings = $settings;

        // Decide on mode and key in settings
        if ($mode->isFromSettings()) {
            $this->isTest = (bool) $this->settings['test'];
        } else {
            $this->isTest = $mode->isTest();
        }

        $key = $this->isTest ? 'testSettings' : 'settings';

        // Subtract settings for mode
        $settings = $this->settings['webservice'][$key];

        // Init variables
        $this->server = $settings['server'];
        $this->certificateAndKey = $settings['certificateAndKey'];
        $this->password = $settings['password'];
        $this->serviceId = $settings['serviceId'];
        $this->proxy = $settings['proxy'];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getCertificateAndKey()
    {
        return $this->certificateAndKey;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    public function hasProxy()
    {
        return boolval($this->proxy);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return false|string
     */
    public function getProxy()
    {
        return $this->proxy;
    }
}
