<?php

namespace System\Libs\Data;


interface ParseDataInterface
{
    public function getData(): array;
    public function encodeData(array $data): string;
    public function decodeData(string $data): string|array;
}
