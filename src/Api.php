<?php

namespace PayumTW\Esunbank;

use LogicException;
use Detection\MobileDetect;
use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Exception\Http\HttpException;

class Api
{
    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory, Encrypter $encrypter = null)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->encrypter = $encrypter ?: new Encrypter();
        $this->encrypter->setKey($this->options['M']);
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields, $type = 'sync')
    {
        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint($type), [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($this->encrypter->encryptRequest($fields)));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $this->parseResponse(
            $response->getBody()->getContents()
        );
    }

    /**
     * parseResponse.
     *
     * @param array $response
     *
     * @return array
     */
    public function parseResponse($response)
    {
        if (is_string($response) === true) {
            $response = $this->parseStr($response);
        }

        if (empty($response['DATA']) === true) {
            throw new LogicException('Response content is not valid');
        }

        $data = json_decode($response['DATA'], true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = [];
            parse_str(str_replace(',', '&', $response['DATA']), $data);
        }

        if (isset($data['returnCode']) === true) {
            $response['returnCode'] = $data['returnCode'];
        }

        if (isset($data['version']) === true) {
            $response['version'] = $data['version'];
        }

        if (isset($data['txnData']) === true) {
            $response = array_merge($response, $data['txnData']);
            unset($data['txnData']);
        }

        return array_merge($response, $data);
    }

    protected function parseStr($str)
    {
        $response = [];
        parse_str($str, $response);

        return $response;
    }

    /**
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint($type = 'capture')
    {
        if ($this->options['sandbox'] === false) {
            $urls = [
                'capture' => $this->isMobile() === false ? 'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014s' : 'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014m',
                'cancel' => 'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf0150',
                'refund' => 'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf0160',
                'sync' => 'https://acq.esunbank.com.tw/ACQQuery/esuncard/txnf0180',
            ];
        } else {
            $urls = [
                'capture' => $this->isMobile() === false ? 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014s' : 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014m',
                'cancel' => 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf0150',
                'refund' => 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf0160',
                'sync' => 'https://acqtest.esunbank.com.tw/ACQQuery/esuncard/txnf0180',
            ];
        }

        return $urls[$type];
    }

    /**
     * createTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function createTransaction(array $params)
    {
        $supportedParams = [
            // 訂單編號, 由特約商店產生，不可重複，不可 包含【_】字元，英數限用大寫
            'ONO' => '',
            // 回覆位址, 'https://acqtest.esunbank.com.tw/ACQTrans/test/print.jsp',
            'U' => 'https://acqtest.esunbank.com.tw/ACQTrans/test/print.jsp',
            // 特店代碼
            'MID' => $this->options['MID'],
            // 銀行紅利折抵, Y：使用銀行紅利交易。 N：不使用銀行紅利交易。
            'BPF' => 'N',
            // 分期代碼, 三期：0100103  六期：0100106 正式環境參數由業務經辦提供
            'IC' => '',
            // 交易金額, 台幣(901)
            'TA' => '',
            // 終端機代號, EC000001(一般交易) EC000002(分期)
            'TID' => 'EC000001',
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        if (empty($params['IC']) === true) {
            unset($params['IC']);
        } else {
            $params['TID'] = 'EC000002';
        }

        $params['BPF'] = strtoupper($params['BPF']);
        if ($params['BPF'] === 'N') {
            unset($params['BPF']);
        }

        return $this->encrypter->encryptRequest($params);
    }

    /**
     * getTransactionData.
     *
     * @param mixed $params
     *
     * @return array
     */
    public function getTransactionData(array $params)
    {
        $supportedParams = [
            // 訂單編號, 由特約商店產生，不可重複，不可 包含【_】字元，英數限用大寫
            'ONO' => '',
            // 特店代碼
            'MID' => $this->options['MID'],
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        return $this->doRequest($params, 'sync');
    }

    /**
     * refundTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function refundTransaction(array $params)
    {
        $supportedParams = [
            // 05:授權 51:取消授權 71:退貨授權
            'TYP' => '71',
            // 訂單編號, 由特約商店產生，不可重複，不可 包含【_】字元，英數限用大寫
            'ONO' => null,
            // 特店代碼
            'MID' => $this->options['MID'],
            // 專案資訊
            'C' => null,
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        return $this->doRequest($params, 'refund');
    }

    /**
     * cancelTransaction.
     *
     * @param array $params
     *
     * @return array
     */
    public function cancelTransaction(array $params)
    {
        $supportedParams = [
            // 訂單編號, 由特約商店產生，不可重複，不可 包含【_】字元，英數限用大寫
            'ONO' => '',
            // 特店代碼
            'MID' => $this->options['MID'],
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        return $this->doRequest($params, 'cancel');
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function calculateHash($params)
    {
        return $this->encrypter->encrypt($params);
    }

    /**
     * verifyHash.
     *
     * @method verifyHash
     *
     * @param array $params
     *
     * @return bool
     */
    public function verifyHash($response)
    {
        // 尚未確定
        return empty($response['MACD']) === true
            ? false
            : $this->calculateHash($response) === $response['MACD'];
    }

    /**
     * isMobile.
     *
     * @return bool
     */
    protected function isMobile()
    {
        if (isset($this->options['mobile']) === true && is_null($this->options['mobile']) === false) {
            return $this->options['mobile'];
        }

        $detect = new MobileDetect();

        return ($detect->isMobile() === false && $detect->isTablet() === false) ? false : true;
    }
}
