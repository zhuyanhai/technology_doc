<?php
/**
 * router 异常
 *
 */
class F_Route_Exception extends F_Exception
{
    function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}