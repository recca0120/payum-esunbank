<?php

namespace PayumTW\Esunbank;

class Encrypter
{
    protected $key;

    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    public function encrypt($attributes = [], $option = JSON_UNESCAPED_SLASHES)
    {
        if (is_array($attributes) === true) {
            $attributes = json_encode($attributes, $option);
        }

        return hash('sha256', $attributes.$this->key);
    }

    public function encryptRequest($attributes, $ksn = 1, $option = JSON_UNESCAPED_SLASHES)
    {
        return [
            'data' => json_encode($attributes, $option),
            'mac' => $this->encrypt($attributes, $option),
            'ksn' => $ksn,
        ];
    }
}
