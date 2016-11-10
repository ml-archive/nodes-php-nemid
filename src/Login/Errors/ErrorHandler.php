<?php

namespace Nodes\NemId\Login\Errors;

/**
 * Class ErrorHandler.
 *
 * @author Casper Rasmussen <cr@nodes.dk>
 */
class ErrorHandler
{
    /**
     * Get Error object back from code.
     * Remember all responses from nemid are base64 encoded.
     *
     * @author Casper Rasmussen <cr@nodes.dk>
     *
     * @param $code
     *
     * @return \Nodes\NemId\Login\Errors\Error
     */
    public static function getByCode($code)
    {
        $codes = require_once dirname(__FILE__).'/errors.php';

        if (isset($codes[$code])) {
            return new Error($codes[$code]);
        } else {
            return new Error([
                'english'       => 'Uknown error',
                'code'          => $code,
                'danish'        => 'Ukendt fejl med fejlkoden '.$code,
            ]);
        }
    }
}
