<?php

namespace System\Libs\Data;


class JSON implements ParseDataInterface
{
    private array $data;
    public function __construct(string $data)
    {
        $encodedData = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

        if (is_array($encodedData)) {
            $this->data = $encodedData;
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function encodeData(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    public function decodeData(string $data): string|array
    {
        return json_decode($data, true, 512, JSON_THROW_ON_ERROR);
    }

}
