<?php

use Mockery as m;
use PayumTW\Esunbank\Api;

class ApiTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function test_create_transaction()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        // (1)一般交易(無分期無紅利)
        $params = $api->createTransaction([
            'ONO' => '20160518100237',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => '8089000016',
            'TA' => '879',
        ]);
        $this->assertSame('003f4aa7ee5607c29eee3b67d2943e83a7c4ddbf1b0b28175b83df4ca2747101', $params['mac']);

        // (2)有分期無紅利
        $params = $api->createTransaction([
            'ONO' => '20160518101607',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => '8089000016',
            'IC' => '0100106',
            'TA' => '709',
            'TID' => 'EC000002',
        ]);
        $this->assertSame('c8c420088a8600c467a75d8098a07ec9c000662e51e54f655d2c83f57c718541', $params['mac']);

        // (3)無分期有紅利
        $params = $api->createTransaction([
            'ONO' => '20160518102002',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => '8089000016',
            'BPF' => 'Y',
            'TA' => '225',
            'TID' => 'EC000001',
        ]);
        $this->assertSame('374b4870a2cbbc8367eff3881455fce89b9c1bca895f5179a5a16e488f0bfb36', $params['mac']);

        // (4)有分期有紅利
        $params = $api->createTransaction([
            'ONO' => '20160518102121',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => '8089000016',
            'BPF' => 'Y',
            'IC' => '0100106',
            'TA' => '288',
            'TID' => 'EC000002',
        ]);
        $this->assertSame('e2d6079a5623815c09f5108789c867beb532a630756e12d27ecb1ecc3909dffe', $params['mac']);
    }

    public function test_get_transaction_data()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        $api = new Api($options, $httpClient, $message);

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $params = [
            'response' => [
                'DATA' => 'RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******185',
                'MACD' => 'c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee',
            ],
        ];
        $api->getTransactionData($params);
    }

    public function test_desktop_endpoint_url()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $api = new Api($options, $httpClient, $message);
        $this->assertSame('https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014s', $api->getApiEndpoint());

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => false,
        ];
        $api = new Api($options, $httpClient, $message);
        $this->assertSame('https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014s', $api->getApiEndpoint());
    }

    public function test_mobile_endpoint_url()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'mobile' => true,
            'sandbox' => true,
        ];
        $api = new Api($options, $httpClient, $message);
        $this->assertSame('https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014m', $api->getApiEndpoint());

        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'mobile' => true,
            'sandbox' => false,
        ];
        $api = new Api($options, $httpClient, $message);
        $this->assertSame('https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014m', $api->getApiEndpoint());
    }

    public function test_refund_transaction()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */

        $httpClient = m::mock('Payum\Core\HttpClientInterface');
        $message = m::mock('Http\Message\MessageFactory');
        $request = m::mock('stdClass');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */

        $options = [
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
        ];
        $api = new Api($options, $httpClient, $message);

        $this->assertSame(hash('sha256', '{"TYP":"05","ONO":"1452836854182","MID":"8089000016"}WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B'), $api->calculateHash([
            'TYP' => '05',
            'ONO' => '1452836854182',
            'MID' => '8089000016',
        ]));
    }
}
