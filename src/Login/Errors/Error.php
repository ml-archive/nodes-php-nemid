<?php
namespace Nodes\NemId\Login\Errors;

/**
 * Class Error
 * @author Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId\Login\Errors
 */
class Error
{

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $english;

    /**
     * @var string
     */
    protected $danish;

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * Inputted error should have following fields
     * - code
     * - english
     * - danish
     *
     * @param array $error
     * @throws \Exception
     */
    public function __construct(array $error)
    {
        if (empty($error['code']) || empty($error['english']) || empty($error['danish'])) {
            throw new \Exception('Could not initialize the error. Missing values');
        }

        $this->code = $error['code'];
        $this->english = $error['english'];
        $this->danish = $error['danish'];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getEnglishDescription()
    {
        return $this->english;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getDanishDescription()
    {
        return $this->danish;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return array
     */
    public function toArray() {
        return [
            'code' => $this->code,
            'danish' => $this->danish,
            'english' => $this->english
        ];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function toJson() {
        return json_encode($this->toArray());
    }

}
