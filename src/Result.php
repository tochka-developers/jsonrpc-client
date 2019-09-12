<?php

namespace Tochka\JsonRpcClient;

class Result
{
    protected $result;

    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function get()
    {
        return $this->result;
    }

    /**
     * @param $value
     *
     * @codeCoverageIgnore
     */
    public function setResult($value): void
    {
        $this->result = $value;
    }

    public function __toString()
    {
        return 'The response has not yet been initialized';
    }
}