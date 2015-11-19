<?php

namespace Nodes\NemId\UserCertificateCheck\Exception;

/**
 * Class InvalidCertificateException
 * @author  Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId\UserCertificateCheck\Exception
 */
class InvalidCertificateException extends \Exception
{
    /**
     * InvalidSignatureException constructor.
     *
     * @param null $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message ?: 'Invalid certificate');
    }
}