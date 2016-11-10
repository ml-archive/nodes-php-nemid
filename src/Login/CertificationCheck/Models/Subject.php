<?php

namespace Nodes\NemId\Login\CertificationCheck\Models;

/**
 * Class Subject.
 *
 * @author  Casper Rasmussen <cr@nodes.dk>
 */
class Subject
{
    /**
     * @var string
     */
    protected $name;
    protected $pid;

    /**
     * Subject constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->name = $data['commonName'];
        $this->pid = explode(':', $data['serialNumber'])[1];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function getPid()
    {
        return $this->pid;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->getName(),
            'pid'  => $this->getPid(),
        ];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }
}
