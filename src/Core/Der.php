<?php

namespace Nodes\NemId\Core;

/**
 * Class Der.
 *
 * @author  Taken from the WAYF repo
 */
class Der extends Oids
{
    /**
     * @var
     */
    protected $tag;
    protected $len;
    protected $value;
    protected $class;
    protected $constructed;

    /**
     * @var array
     */
    protected $buffer;
    protected $stack = [];

    /**
     * @var
     */
    protected $i;

    /**
     * @var array
     */
    private $ignoredextensions = [
        'netscape-cert-type' => 1,
    ];

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $der
     */
    protected function init($der)
    {
        $this->buffer = $der;
        $this->i = 0;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param string $note
     */
    protected function dump($note = '')
    {
        $z = strlen($this->buffer) - $this->i;
        print_r("$note\n");
        print_r("len: $z\n");
        print_r(chunk_split(bin2hex(substr($this->buffer, $this->i)), 2, ':'));
        echo "\n";
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param string $note
     */
    protected function pr($note = '')
    {
        $savei = $this->i;
        $byte = ord($this->buffer[$this->i++]);
        $tag = $byte & 0x1f;
        $class = $byte & 0xc0;
        $constructed = $byte & 0x20;
        $len = $this->vallen();
        $this->i = $savei;
        print_r("$note\n");
        print_r("i  : {$this->i}\n");
        print_r("len: {$len}\n");
        print_r("class:   {$class}\n");
        print_r("tag  :   {$tag}\n");
        print_r(chunk_split(bin2hex(substr($this->buffer, $this->i, min(32, strlen($this->buffer) - $this->i)))."\n", 2,
            ':'));
        print_r("---\n");
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null $expectedtag
     */
    private function tlv($expectedtag = null)
    {
        $byte = ord($this->buffer[$this->i++]);
        $this->tag = $byte & 0x1f;
        if ($expectedtag < 0) {
            $this->tag = $expectedtag = -$expectedtag;
        }
        if ($expectedtag && $expectedtag != $this->tag) {
            $x = $this->i - 1;
            print_r(bin2hex(substr($this->buffer, $x, 32)));
            trigger_error("expected tag == $expectedtag, got {$this->tag}\n", E_USER_ERROR);
        }
        $this->class = $byte & 0xc0;
        $this->constructed = $byte & 0x20;
        $this->len = $this->vallen();
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null $expectedtag
     *
     * @return bool|int|null|string|void
     */
    protected function next($expectedtag = null)
    {
        $this->tlv($expectedtag);
        if ($this->constructed) {
            return;
        } else {
            $value = substr($this->buffer, $this->i, $this->len);
            if ($this->class == 0 || $this->class == 0x80) {
                if ($this->tag == 2 || $this->tag == 10) { // ints and enums
                    $int = 0;
                    foreach (str_split($value) as $byte) {
                        $int = bcmul($int, '256', 0);
                        $int = bcadd($int, ord($byte), 0);
                    }
                    $this->value = $int;
                } elseif ($this->tag == 1) { // boolean
                    $this->value = ord($value) != 0;
                } elseif ($this->tag == 3) { // bit string
                    $this->value = $value;
                } elseif ($this->tag == 5) { // null
                    $this->value = null;
                } else {
                    $this->value = $value;
                }
            }
            $this->i += $this->len;

            return $this->value;
        }
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null       $expectedtag
     * @param bool|false $pass
     *
     * @return string
     */
    protected function der($expectedtag = null, $pass = false)
    {
        $oldi = $this->i;
        $this->tlv($expectedtag);
        $i = $this->i;
        if (!$pass) {
            $this->i = $oldi;
        } else {
            $this->i += $this->len;
        }

        return substr($this->buffer, $oldi, $this->len + $i - $oldi);
    }

    /**
     * If provided with a tag and the tag is equal to the current tag
     * peek considers it EXPLICIT, consumes it and return true.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null $tag
     *
     * @return bool|int|null
     */
    protected function peek($tag = null)
    {
        $t = null;
        if ($this->i < end($this->stack)) {
            $t = ord($this->buffer[$this->i]) & 0x1f;
        }
        if ($tag !== null) {
            if ($t === $tag) {
                $this->next($tag);

                return true;
            } else {
                return false;
            }
        }

        return $t;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return int
     */
    protected function vallen()
    {
        $byte = ord($this->buffer[$this->i++]);
        $res = $len = $byte & 0x7f;
        if ($byte >= 0x80) {
            $res = 0;
            for ($c = 0; $c < $len; $c++) {
                $res = $res * 256 + ord($this->buffer[$this->i++]);
            }
        }

        return $res;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param int $tag
     */
    protected function beginsequence($tag = 16)
    {
        $this->begin($tag);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param int $tag
     */
    protected function beginset($tag = 17)
    {
        $this->begin($tag);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $tag
     */
    protected function begin($tag)
    {
        $this->next($tag);
        array_push($this->stack, $this->i + $this->len);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return bool
     */
    protected function in()
    {
        return $this->i < end($this->stack);
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     */
    protected function end()
    {
        $end = array_pop($this->stack);
        if ($end != $this->i) {
            trigger_error("sequence or set length does not match: $end != {$this->i}", E_USER_ERROR);
        }
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return array
     */
    protected function extensions()
    {
        $this->beginsequence();
        $extns = [];
        while ($this->in()) {
            $this->beginsequence();
            $extnID = $this->oid();
            $theext['critical'] = $this->peek(1);
            $theext['extnValue'] = $this->next(4);

            try {
                if (method_exists($this, $extnID)) {
                    $theext['extnValue'] = call_user_func([$this, $extnID], $theext['extnValue']);
                } elseif (!empty($this->ignoredextensions['$extnID'])) {
                    trigger_error("Unknown extension $extnID", E_USER_ERROR);
                } else {
                    $theext['extnValue'] = chunk_split(bin2hex($theext['extnValue']), 2, ':');
                }
            } catch (\Exception $e) {
                $theext['extnValue'] = chunk_split(bin2hex($theext['extnValue']), 2, ':');
            }
            $this->end();
            $extns[$extnID] = $theext;
        }
        $this->end();

        return $extns;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @return string
     */
    protected function signatureAlgorithm()
    {
        $this->beginsequence();
        $salg = $this->oid();
        if ($this->in()) {
            $this->next(); // alg param - ignore for now
        }
        $this->end();

        return $salg;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null $tag
     *
     * @return array
     */
    protected function name($tag = null)
    {
        $this->beginsequence($tag);  // seq of RDN
        $res = [];
        while ($this->in()) {
            $parts = [];
            $this->beginset(); // set of AttributeTypeAndValue
            while ($this->in()) {
                $this->beginsequence();
                $parts[$this->oid()] = $this->next(); // AttributeValue
                $this->end();
            }
            $this->end();
            $res[] = $parts;
        }
        $this->end();

        return $res;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param int $tag
     *
     * @return string
     */
    protected function oid($tag = 6)
    {
        $v = $this->oid_($this->next($tag));
        if (isset($this->oids[$v])) {
            return $this->oids[$v];
        }

        return $v;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $oid
     *
     * @return string
     */
    protected function oid_($oid)
    {
        $len = strlen($oid);
        $v = '';
        $n = 0;
        for ($c = 0; $c < $len; $c++) {
            $x = ord($oid[$c]);
            $n = $n * 128 + ($x & 0x7f);
            if ($x <= 127) {
                $v .= $v ? '.'.$n : ((int) ($n / 40).'.'.($n % 40));
                $n = 0;
            }
        }

        return $v.'*';
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param null $tag
     *
     * @return bool|int|null|string|void
     */
    protected function time($tag = null)
    {
        $time = $this->next($tag);
        if ($this->tag == 23) {
            $time = (substr($time, 0, 2) < 50 ? '20' : '19').$time;
        } elseif ($this->tag != 24) {
            trigger_error('expected der utc or generalized time', E_USER_ERROR);
        }

        return $time;
    }

    /**
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param int $tag
     *
     * @return string
     */
    protected function keyident($tag = 4)
    {
        return chunk_split(bin2hex($this->next($tag)), 2, ':');
    }
}
