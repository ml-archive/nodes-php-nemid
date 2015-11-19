<?php

namespace Nodes\NemId\PidCprMatch;

/**
 * Class Settings
 *
 * @author  Casper Rasmussen <cr@nodes.dk>
 * @package Nodes\NemId\PidCprMatch
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
    protected $privateKeyPassword;

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
     * @param \Nodes\NemId\Model\Mode|null $mode
     */
    public function __construct($mode = null)
    {
        // Retrieve settings
        $this->settings = require_once dirname(__FILE__) . '/settings/settings.php';

        // Decide on mode and key in settings
        if ($mode->isFromSettings()) {
            $this->isTest = (bool)$this->settings['test'];
        } else {
            $this->isTest = $mode->isTest();
        }

        $key = $this->isTest ? 'testSettings' : 'settings';

        // Subtract settings for mode
        $settings = $this->settings[$key];

        // Init variables
        $this->server = $settings['server'];
        $this->certificateAndKey = $settings['certificateAndKey'];
        $this->privateKeyPassword = $settings['privateKeyPassword'];
        $this->serviceId = $settings['serviceId'];
        $this->proxy = $settings['proxy'];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getServiceId()
    {
        return $this->serviceId;
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
    public function getCertificateAndKey()
    {
        return $this->certificateAndKey;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function hasProxy()
    {
        return boolval($this->proxy);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return false|string
     */
    public function getProxy() {
        return $this->proxy;
    }
}