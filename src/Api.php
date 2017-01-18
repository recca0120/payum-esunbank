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
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function doRequest(array $fields, $type = 'sync')
    {
        $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ];

        $request = $this->messageFactory->createRequest('POST', $this->getApiEndpoint($type), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        $details = [];
        parse_str($response->getBody()->getContents(), $details);

        if (empty($details['DATA']) === true) {
            throw new LogicException('Response content is not valid');
        }

        return $details;
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
        }

        $params['BPF'] = strtoupper($params['BPF']);
        if ($params['BPF'] === 'N') {
            unset($params['BPF']);
        }

        return $this->prepareRequestData($params);
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

        $data = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        $data['ONO'] = strtoupper($data['ONO']);

        $body = $this->doRequest($this->prepareRequestData($data), 'sync');
        $response = json_decode($body['DATA'], true);
        $details = $response['txnData'];
        $details['response'] = $response;

        return $details;
    }

    /**
     * refundTransaction.
     *
     * @param  array  $params
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

        $data = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        $data['ONO'] = strtoupper($data['ONO']);

        $body = $this->doRequest($this->prepareRequestData($data), 'refund');

        return json_decode($body['DATA'], true);
    }

    /**
     * cancelTransaction.
     *
     * @param  array  $params
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

        $params['ONO'] = strtoupper($params['ONO']);

        $data['ONO'] = strtoupper($data['ONO']);

        $body = $this->doRequest($this->prepareRequestData($data), 'cancel');

        return json_decode($body['DATA'], true);
    }

    /**
     * prepareRequestData.
     *
     * @method prepareRequestData
     *
     * @param array $params
     * @param int   $option
     *
     * @return array
     */
    protected function prepareRequestData($params, $option = JSON_UNESCAPED_SLASHES)
    {
        return [
            'data' => json_encode($params, $option),
            'mac' => $this->calculateHash($params, $option),
            'ksn' => 1,
        ];
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function calculateHash($params, $option = JSON_UNESCAPED_SLASHES)
    {
        if (is_array($params) === true) {
            $params = json_encode($params, $option);
        }

        return hash('sha256', $params.$this->options['M']);
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
    public function verifyHash($macd, $data)
    {
        return true;

        $result = false;
        if ($macd === $this->calculateHash($data)) {
            $result = true;
        }

        return $result;
    }

    /**
     * parseResponse.
     *
     * @param  array $response
     *
     * @return array
     */
    public function parseResponse($response)
    {
        $result = [];
        parse_str(str_replace(',', '&', $response['DATA']), $result);

        return array_merge($response, $result);
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
