<?php

namespace Nodes\NemId\Core;

/**
 * More or less 1:1 copy from WAYF Library
 *
 * Class X509Helper
 * @author  Taken from the WAYF repo
 *
 * @package Nodes\NemId\Core
 */
class X509Helper extends Der
{

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @return array
     * @throws \Exception
     */
    public function generalName()
    {
        $tag = $this->peek();
        switch ($tag) {
            case 0:
                $res['otherName'] = $this->oid(-6);
                break;
            case 1:
                $res['rfc822Name'] = $this->next(-22);
                break;
            case 2:
                $res['dNSName'] = $this->next(-22);
                break;
            case 4:
                $this->next(4);
                $res['directoryName'] = $this->name();
                $res['directoryName_'] = $this->nameAsString($res['directoryName']);
                break;
            case 6:
                $res['uniformResourceIdentifier'] = $this->next(-22);
                break;
            default:
                throw new \Exception("Unsupported GeneralName: $tag");
        }

        return $res;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param null $tag
     * @return array
     * @throws \Exception
     */
    public function generalNames($tag = null)
    {
        $res = array();
        $this->beginsequence($tag);
        while ($this->in()) {
            $res[] = $this->generalName();
        }
        $this->end();
        return $res;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     * @param $name
     * @return string
     */
    public function nameAsString($name)
    {
        $rdnd = '';
        $res = '';
        $abbvrs = array(
            'countryName' => 'c',
            'organizationName' => 'o',
            'commonName' => 'cn',
            'stateOrProvinceName' => 'state',
            'localityName' => 'l',
            'organizationalUnitName' => 'ou',
            'domainComponent' => 'dc',
        );
        foreach ($name as $rdn) {
            $mrdnd = '';
            $r = "";
            foreach ($rdn as $type => $value) {
                if (substr($type, -1) == '*') continue;
                $type = empty($abbvrs[$type]) ? $type : $abbvrs[$type];
                $r .= $mrdnd . $type . '=' . $value;
                $mrdnd = '+';
            }
            $res .= $rdnd . $r;
            $rdnd = ',';
        }
        return $res;
    }
}