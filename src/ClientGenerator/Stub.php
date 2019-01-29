<?php

namespace Tochka\JsonRpcClient\ClientGenerator;

interface Stub
{
    public function getClassSource();

    public function __toString();
}