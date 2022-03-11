<?php

namespace Tochka\JsonRpcClient\Support;

class Result
{
    /** @var mixed */
    private $result;
    
    /**
     * @return mixed
     * @codeCoverageIgnore
     */
    public function get()
    {
        return $this->result;
    }
    
    /**
     * @param mixed $value
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
