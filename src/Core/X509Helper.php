<?php

namespace Nodes\NemId\Core;


class X509Helper extends Der {

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
                $res['directoryName_'] = $this->nameasstring($res['directoryName']);
                break;
            case 6:
                $res['uniformResourceIdentifier'] = $this->next(-22);
                break;
            default:
                throw new \Exception("Unsupported GeneralName: $tag");
#                trigger_error("Unsupported GeneralName: $tag", E_USER_ERROR);
        }
        return $res;
    }

    public function generalNames($tag = null) {
        $res = array();
        $this->beginsequence($tag);
        while ($this->in()) {
            $res[] = $this->generalName();
        }
        $this->end();
        return $res;
    }

    public function nameasstring($name)
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