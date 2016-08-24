<?php

namespace PayumTW\Esunbank;

use Detection\MobileDetect;
use Http\Message\MessageFactory;
use Payum\Core\HttpClientInterface;
use Payum\Core\Reply\HttpPostRedirect;

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
        '33' => '過期卡',
        '54' => '卡片過期',
        '62' => '尚未開卡',
        'L1' => '產品代碼錯誤',
        'L2' => '期數錯誤',
        'L3' => '不支援分期(他行卡)',
        'L4' => '產品代碼過期',
        'L5' => '金額無效',
        'L6' => '不支援分期',
        'L7' => '非限定卡別交易',
        'XA' => '紅利自付額有誤',
        'XB' => '紅利商品數量有誤',
        'XC' => '紅利商品數量超過可折抵上限',
        'XD' => '紅利商品折抵點數超過最高折',
        'XE' => '紅利商品傳入之固定點數有誤',
        'XF' => '紅利折抵金額超過消費金額',
        'X1' => '不允許使用紅利折抵現金功能',
        'X2' => '點數未達可折抵點數下限',
        'X3' => '他行卡不支援紅利折抵',
        'X4' => '此活動已逾期',
        'X5' => '金額未超過限額不允許使用',
        'X6' => '特店不允許紅利交易',
        'X7' => '點數不足',
        'X8' => '非正卡持卡人',
        'X9' => '紅利商品編號有誤或空白',
        'G0' => '系統功能有誤',
        'G1' => '交易異常',
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
        'GS' => '系統暫停服務',
        'QQ' => '不允許 Debit Card 交易',
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
     * getApiEndpoint.
     *
     * @return string
     */
    public function getApiEndpoint()
    {
        $isDesktop = $this->isDesktop(),
        if ($this->options['sandbox'] === false) {
            return $isDesktop === true ?
                'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014s' :
                'https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014m';
        } else {
            return $isDesktop === true ?
                'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014s' :
                'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014m';
        }
    }

    /**
     * request.
     *
     * @param array $params
     * @param mixed $request
     *
     * @return array
     */
    public function request(array $params, $request)
    {
        $supportedParams = [
            // 特店代碼
            'MID' => $this->options['MID'],
            // 終端機代號, EC000001(一般交易) EC000002(分期)
            'TID' => 'EC000001',
            // 訂單編號, 由特約商店產生，不可重複，不可 包含【_】字元，英數限用大寫
            'ONO' => '',
            // 交易金額, 台幣(901)
            'TA'  => '',
            // 回覆位址, 'https://acqtest.esunbank.com.tw/ACQTrans/test/print.jsp',
            'U'   => $this->getRedirectUrl($request),
            // 分期代碼, 三期：0100103  六期：0100106 正式環境參數由業務經辦提供
            'IC'  => '',
            // 銀行紅利折抵, Y：使用銀行紅利交易。 N：不使用銀行紅利交易。
            'BPF' => 'N',
        ];

        $params = array_replace(
            $supportedParams,
            array_intersect_key($params, $supportedParams)
        );

        $params = json_encode($params);

        return new HttpPostRedirect($this->getApiEndpoint(), [
            'data' => $params,
            'mac'  => $this->generateKey($params),
            'ksn'  => 1,
        ]);
    }

    /**
     * generateKey.
     *
     * @param array $params
     *
     * @return string
     */
    protected function generateKey($params)
    {
        if (is_array($params) === true) {
            $params = json_encode($params);
        }

        return hash('sha256', $params.$this->options['M']);
    }

    /**
     * getRedirectUrl.
     *
     * @param mixed $request
     *
     * @return string
     */
    protected function getRedirectUrl($request)
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
    protected function getStatusReason($code)
    {
        $statusReason = '拒絕交易';
        if (isset($this->code[$code]) === true) {
            $statusReason = $this->code[$code];
        }

        return $statusReason;
    }

    /**
     * isDesktop.
     *
     * @return bool [description]
     */
    protected function isDesktop()
    {
        $detect = new MobileDetect();

        return $detect->isMobile() === false && $detect->isTablet() === false;
    }
}
