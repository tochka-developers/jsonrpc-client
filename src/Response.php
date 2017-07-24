<?php

namespace Tochka\JsonRpcClient;

class Response
{
    /** @var bool */
    public $success = true;
    /** @var StdClass */
    public $error = null;
    /** @var mixed */
    public $data = null;
}