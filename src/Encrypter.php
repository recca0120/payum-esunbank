<?php

namespace PayumTW\Esunbank;

class Encrypter
{
    protected $key;

    /**
     * setKey.
     *
     * @param string $key [description]
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * encrypt.
     * @param array $attributes
     * @return string
     */
    public function encrypt($attributes = [])
    {
        if (isset($attributes['MACD']) === true) {
            foreach (['DATA', 'MACD', 'returnCode', 'version'] as $key) {
                if (isset($attributes[$key]) === true) {
                    unset($attributes[$key]);
                }
            }
            $string = urldecode(http_build_query($attributes, '', ',')).',';
        } else {
            $string = json_encode($attributes, JSON_UNESCAPED_SLASHES);
        }

        return hash('sha256', $string.$this->key);
    }

    /**
     * encryptRequest.
     *
     * @param array $attributes
     * @param int $ksn
     * @return array
     */
    public function encryptRequest($attributes, $ksn = 1)
    {
        return [
            'data' => json_encode($attributes, JSON_UNESCAPED_SLASHES),
            'mac' => $this->encrypt($attributes),
            'ksn' => $ksn,
        ];
    }
}
