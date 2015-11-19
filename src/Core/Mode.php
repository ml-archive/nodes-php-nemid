<?php
namespace Nodes\NemId\Core;

/**
 * Class Mode
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 */
class Mode
{
    /**
     * @var int constant
     */
    private $fromSettings = 0;

    /**
     * @var int constant
     */
    private $test = 1;

    /**
     * @var int constant
     */
    private $production = 2;

    /**
     * @var int mode
     */
    protected $mode = 0;

    /**
     * Mode constructor.
     *
     * @param null $isTest
     */
    public function __construct($isTest = null)
    {
        if (!is_null($isTest)) {
            $this->setTestMode($isTest);
        }
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    public function setFromSettings()
    {
        $this->mode = $this->fromSettings;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    public function setTest()
    {
        $this->mode = $this->test;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    public function setProduction()
    {
        $this->mode = $this->production;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param $isTest
     */
    public function setTestMode($isTest)
    {
        if ($isTest) {
            $this->setTest();
        } else {
            $this->setProduction();
        }
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function isFromSettings()
    {
        return $this->mode == $this->fromSettings;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return bool
     */
    public function isTest()
    {
        return $this->mode == $this->test;
    }

}