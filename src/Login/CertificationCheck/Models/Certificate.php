<?php

namespace Nodes\NemId\Login\CertificationCheck\Models;

/**
 * Class Certificate
 * @author  Casper Rasmussen <cr@nodes.dk>
 *
 * @package Nodes\NemId\Login\CertificationCheck\Models
 */
class Certificate
{
    /**
     * @var string
     */
    protected $certificateDer, $signature, $signatureAlgorithm;

    /**
     * @var array
     */
    protected $tbsCertificate;

    /**
     * Certificate constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->certificateDer = $data['certificate_der'];
        $this->tbsCertificate = $data['tbsCertificate'];
        $this->signatureAlgorithm = $data['signatureAlgorithm'];
        $this->signature = $data['signature'];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getCertificateDer()
    {
        return $this->certificateDer;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return array
     */
    public function getTbsCertificate()
    {
        return $this->tbsCertificate;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getSignatureAlgorithm()
    {
        return $this->signatureAlgorithm;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return array
     */
    public function toArray()
    {
        return [
            'certificate_der' => $this->getCertificateDer(),
            'tbsCertificate' => $this->getTbsCertificate(),
            'signatureAlgorithm' => $this->getSignatureAlgorithm(),
            'signature' => $this->getSignature()
        ];
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return \Nodes\NemId\Login\CertificationCheck\Models\Subject
     */
    public function getSubject()
    {
        return new Subject(end($this->getTbsCertificate()['subject']));
    }
}