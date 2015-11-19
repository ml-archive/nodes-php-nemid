<?php

namespace Nodes\NemId\Login\CertificationCheck\Exceptions;

/**
 * Class InvalidSignatureException
 * @author  Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId\Login\CertificationCheck\Exception
 */
class InvalidSignatureException extends \Exception
{
    /**
     * InvalidSignatureException constructor.
     *
     * @param null $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'Invalid signature');
    }
}