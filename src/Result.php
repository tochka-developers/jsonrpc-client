<?php

namespace Tochka\JsonRpcClient;

class Result
{
    protected $result;

    public function get()
    {
        return $this->result;
    }

    public function setResult($value): void
    {
        $this->result = $value;
    }

    public function __toString()
    {
        return 'The response has not yet been initialized';
    }
}