<?php

namespace Nodes\NemId\Webservice\PidCprMatch\Responses;

/**
 * Class Response.
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 */
class Response
{
    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $short;

    /**
     * @var string
     */
    protected $danish;

    /**
     * @var string
     */
    protected $english;

    /**
     * @param int $code
     */
    public function __construct($code, $exception = null)
    {
        $this->setBy($code, $exception);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'code'    => $this->code,
            'short'   => $this->short,
            'danish'  => $this->danish,
            'english' => $this->english,
        ];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $code
     */
    private function setBy($code, $exception)
    {
        $codes = require_once dirname(__FILE__).'/response_codes.php';
        $this->code = $code;
        if (!empty($exception) && $exception instanceof \Exception) {
            $this->short = 'Exception';
            $this->danish = $exception->getMessage();
            $this->english = $exception->getMessage();
        } elseif (!empty($codes[$code])) {
            $this->short = $codes[$code]['short'];
            $this->danish = $codes[$code]['danish'];
            $this->english = $codes[$code]['english'];
        } else {
            $this->short = 'Error';
            $this->danish = 'Ukendt fejl';
            $this->english = 'Unknown error';
        }
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    public function didMatch()
    {
        return $this->code == 0;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getDanish()
    {
        return $this->danish;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getEnglish()
    {
        return $this->english;
    }
}
