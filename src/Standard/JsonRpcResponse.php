<?php

namespace Tochka\JsonRpcClient\Standard;

class JsonRpcResponse
{
    public $jsonrpc;
    public $result;
    public $id;
    public $error;

    public function __construct($data)
    {
        $this->jsonrpc = $data->jsonrpc;
        $this->id = $data->id ?? null;
        $this->result = $data->result ?? null;
        if (!empty($data->error)) {
            $this->error = new JsonRpcError($data->error);
        } else {
            $this->result = $data->result;
        }
    }
}