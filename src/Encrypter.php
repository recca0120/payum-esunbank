<?php

namespace PayumTW\Esunbank;

class Encrypter
{
    /**
     * $key.
     *
     * @var string
     */
    protected $key;

    /**
     * setKey.
     *
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * encrypt.
     *
     * @param array $params
     * @return string
     */
    public function encrypt($params = [])
    {
        if (isset($params['MACD']) === true) {
            foreach (['DATA', 'MACD', 'returnCode', 'version'] as $key) {
                if (isset($params[$key]) === true) {
                    unset($params[$key]);
                }
            }
            $string = urldecode(http_build_query($params, '', ',')).',';
        } else {
            $string = json_encode($params, JSON_UNESCAPED_SLASHES);
        }

        return hash('sha256', $string.$this->key);
    }

    /**
     * encryptRequest.
     *
     * @param array $params
     * @param int $ksn
     * @return array
     */
    public function encryptRequest($params, $ksn = 1)
    {
        return [
            'data' => json_encode($params, JSON_UNESCAPED_SLASHES),
            'mac' => $this->encrypt($params),
            'ksn' => $ksn,
        ];
    }
}
