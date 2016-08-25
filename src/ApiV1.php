<?php

namespace PayumTW\Esunbank;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;

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
     * @var array
     */
    protected $code = [
        '00' => '核准',
        '01' => '請查詢銀行',
        '02' => '請查詢銀行',
        '05' => '請查詢銀行',
        '14' => '卡號錯誤',
        '33' => '過期卡',
        '54' => '有效年月過期',
        'E1' => '卡片過期',
        'EI' => '未開卡',
        'EE' => '持卡人 ID 錯誤',
        'L1' => '產品代碼錯誤',
        'L2' => '期數錯誤',
        'L3' => '不支援分期(他行卡)',
        'L4' => '產品代碼過期',
        'L5' => '金額無效',
        'L6' => '不支援分期',
        'G0' => '系統功能有誤',
        'G1' => '交易逾時',
        'G2' => '資料格式錯誤',
        'G3' => '非使用中特店',
        'G4' => '特店交易類型不合',
        'G5' => '連線 IP 不合',
        'G6' => '訂單編號重複',
        'G7' => '使用未定義之紅利點數進行交易',
        'G8' => '押碼錯誤',
        'G9' => 'Session 檢查有誤',
        'GA' => '無效的持卡人資料',
        'GB' => '不允許執行授權取消交易',
        'GC' => '交易資料逾期',
        'GD' => '查無訂單編號',
        'GE' => '查無交易明細',
        'GF' => '交易資料狀態不符',
        'GG' => '交易失敗',
        'GT' => '交易時間逾時',
        'GH' => '訂單編號重複送出交易',
        'GI' => '銀行紅利狀態不符',
        'GJ' => '出團日期不合法',
        'GK' => '延後出團天數超過限定天數',
        'GL' => '非限定特店，不可使用「玉山卡」參數',
        'GM' => '限定特店，必須傳送「玉山卡」參數',
        'GN' => '該卡號非玉山卡所屬',
        'XA' => '紅利自付額有誤',
        'XB' => '紅利商品數量有誤',
        'XC' => '紅利商品數量超過可折抵上限',
        'XD' => '紅利商品折抵點數超過最高折',
        'XE' => '紅利商品傳入之固定點數有誤',
        'X1' => '不允許使用紅利折抵現金功能',
        'X2' => '點數未達可折抵點數下限',
        'X3' => '他行卡不支援紅利折抵',
        'X4' => '此活動已逾期',
        'X5' => '金額未超過限額不允許使用',
        'X6' => '特店不允許紅利交易',
        'X7' => '點數不足',
        'X8' => '非正卡持卡人',
        'X9' => '紅利商品編號有誤或空白',
        'TG' => '風險卡管制',
    ];

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
    protected function doRequest($method, array $fields)
    {
        $headers = [];

        $request = $this->messageFactory->createRequest($method, $this->getApiEndpoint(), $headers, http_build_query($fields));

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        return $this->options['sandbox'] ?
            'https://acqtest.esunbank.com.tw/acq_online/online/sale47.htm' :
            'https://acq.esunbank.com.tw/acq_online/online/sale47.htm';
    }

    /**
     * preparePayment.
     *
     * @param array $params
     * @param mixed $request
     *
     * @return array
     */
    public function preparePayment(array $params, $request)
    {
        $supportedParams = [
            'MID' => $this->options['MID'],
            'CID' => '',
            'TID' => $this->options['TID'],
            'ONO' => '',
            'TA'  => '',
            'U'   => $this->getRedirectUrl($request),
            'IC'  => '',
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        return array_merge([
            'M' => md5(implode('&', array_merge($params, [
                $this->options['M'],
            ]))),
        ], $params);
    }

    /**
     * getRedirectUrl.
     *
     * @param mixed $request
     *
     * @return string
     */
    public function getRedirectUrl($request)
    {
        $scheme = parse_url($request->getToken()->getTargetUrl());

        return sprintf('%s://%s%s', $scheme['scheme'], $scheme['host'], $scheme['path']);
    }

    /**
     * parseResult.
     *
     * @param mixed $result
     *
     * @return array
     */
    public function parseResult($result)
    {
        $data = [];
        parse_str(str_replace(',', '&', $result), $data);
        $data['statusReason'] = $this->getStatusReason($data['RC']);

        return $data;
    }

    /**
     * getStatusReason.
     *
     * @param string $code
     *
     * @return string
     */
    public function getStatusReason($code)
    {
        $statusReason = '拒絕交易';
        if (isset($this->code[$code]) === true) {
            $statusReason = $this->code[$code];
        }

        return $statusReason;
    }
}
