<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PayumTW\Esunbank\Api;
use PHPUnit\Framework\TestCase;
use Http\Adapter\Guzzle6\Client;
use Payum\Core\Bridge\Httplug\HttplugClient;
use Http\Message\MessageFactory\GuzzleMessageFactory;

class ApiTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    protected function realHttpClient()
    {
        $options = [
            'MID' => '8080082790',
            'M' => '8ND0W3DKWBU10I8B9F1ELX15FRG0JNUM',
            'sandbox' => false,
        ];

        $api = new Api(
            $options,
            $httpClient = new HttplugClient(Client::createWithConfig(['verify' => false])),
            $messageFactory = new GuzzleMessageFactory()
        );

        $ONO = '58B52D4711F71';

        return $api;
    }

    public function testCreateTransaction()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with($params = [
            'ONO' => '20160518100237',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => $options['MID'],
            'TA' => '879',
            'TID' => 'EC000001',
        ])->andReturn($requestData = 'foo');

        // (1)一般交易(無分期無紅利)
        $this->assertSame($requestData, $api->createTransaction([
            'ONO' => $params['ONO'],
            'U' => $params['U'],
            'TA' => $params['TA'],
        ]));
    }

    public function testCreateTransactionHasStagingNoBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with($params = [
            'ONO' => '20160518100237',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => $options['MID'],
            'IC' => '0100106',
            'TA' => '709',
            'TID' => 'EC000002',
        ])->andReturn($requestData = 'foo');

        // (2)有分期無紅利
        $this->assertSame($requestData, $api->createTransaction([
            'ONO' => $params['ONO'],
            'U' => $params['U'],
            'IC' => $params['IC'],
            'TA' => $params['TA'],
        ]));
    }

    public function testCreateTransactionNoStagingHasBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with($params = [
            'ONO' => '20160518102002',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => $options['MID'],
            'BPF' => 'Y',
            'TA' => '225',
            'TID' => 'EC000001',
        ])->andReturn($requestData = 'foo');

        // (3)無分期有紅利
        $this->assertSame($requestData, $api->createTransaction([
            'ONO' => $params['ONO'],
            'U' => $params['U'],
            'BPF' => $params['BPF'],
            'TA' => $params['TA'],
        ]));
    }

    public function testCreateTransactionHasStagingHasBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with($params = [
            'ONO' => '20160518102121',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => $options['MID'],
            'BPF' => 'Y',
            'IC' => '0100106',
            'TA' => '225',
            'TID' => 'EC000002',
        ])->andReturn($requestData = 'foo');

        // (3)無分期有紅利
        $this->assertSame($requestData, $api->createTransaction([
            'ONO' => $params['ONO'],
            'U' => $params['U'],
            'BPF' => $params['BPF'],
            'IC' => $params['IC'],
            'TA' => $params['TA'],
        ]));
    }

    public function testParseResponse()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $content = 'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224, LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856&MACD=c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee';
        parse_str($content, $query);

        $this->assertSame([
            'DATA' => $query['DATA'],
            'MACD' => $query['MACD'],
            'RC' => '00',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
            'LTD' => '20160224',
            'LTT' => '150228',
            'RRN' => '506055000001',
            'AIR' => '702715',
            'AN' => '552199******1856',
        ], $query = $api->parseResponse($query));

        $this->assertTrue($api->verifyHash($query['MACD'], $query));
    }

    public function testParseResponseHasStagingNoBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $content = 'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,ITA=300.00,IP=3,IFPA=100.00,IPA=100.00&MACD=c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee';
        parse_str($content, $query);

        $this->assertSame([
            'DATA' => $query['DATA'],
            'MACD' => $query['MACD'],
            'RC' => '00',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
            'LTD' => '20160224',
            'LTT' => '150228',
            'RRN' => '506055000001',
            'AIR' => '702715',
            'AN' => '552199******1856',
            'ITA' => '300.00',
            'IP' => '3',
            'IFPA' => '100.00',
            'IPA' => '100.00',
        ], $query = $api->parseResponse($query));

        $this->assertTrue($api->verifyHash($query['MACD'], $query));
    }

    public function testParseResponseNoStagingHasBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $content = 'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,BRP=0,BB=0,BRA=0.00&MACD=c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee';
        parse_str($content, $query);

        $this->assertSame([
            'DATA' => $query['DATA'],
            'MACD' => $query['MACD'],
            'RC' => '00',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
            'LTD' => '20160224',
            'LTT' => '150228',
            'RRN' => '506055000001',
            'AIR' => '702715',
            'AN' => '552199******1856',
            'BRP' => '0',
            'BB' => '0',
            'BRA' => '0.00',
        ], $query = $api->parseResponse($query));

        $this->assertTrue($api->verifyHash($query['MACD'], $query));
    }

    public function testParseResponseHasStagingHasBonus()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $content = 'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,ITA=300.00,IP=3,IFPA=100.00,IPA=100.00,BRP=0,BB=0,BRA=0.00&MACD=c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee';
        parse_str($content, $query);

        $this->assertSame([
            'DATA' => $query['DATA'],
            'MACD' => $query['MACD'],
            'RC' => '00',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
            'LTD' => '20160224',
            'LTT' => '150228',
            'RRN' => '506055000001',
            'AIR' => '702715',
            'AN' => '552199******1856',
            'ITA' => '300.00',
            'IP' => '3',
            'IFPA' => '100.00',
            'IPA' => '100.00',
            'BRP' => '0',
            'BB' => '0',
            'BRA' => '0.00',
        ], $query = $api->parseResponse($query));

        $this->assertTrue($api->verifyHash($query['MACD'], $query));
    }

    public function testParseResponseFail()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $content = 'DATA=RC=G1,MID=8080000002,ONO=1456296932846';
        parse_str($content, $query);

        $this->assertSame([
            'DATA' => $query['DATA'],
            'RC' => 'G1',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
        ], $query = $api->parseResponse($query));
    }

    public function testGetTransactionData()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with([
            'ONO' => $ONO = '58B52D4711F71',
            'MID' => $options['MID'],
        ])->andReturn($params = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with('POST', 'https://acqtest.esunbank.com.tw/ACQQuery/esuncard/txnf0180', [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($params))->andReturn($request = m::mock('Psr\Http\Message\RequestInterface'));

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn($response = m::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->twice()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = 'DATA={"returnCode":"00","txnData":{"RC":"00","ONO":"58B52D4711F71","MID":"8080082790","AIR":"649188","TXNAMOUNT":"100.00","LTD":"20170228","LTT":"155734","RRN":"247059001425"},"version":"2"}'
        );

        $this->assertSame([
            'DATA' => str_replace('DATA=', '', $content),
            'returnCode' => '00',
            'version' => '2',
            'RC' => '00',
            'ONO' => '58B52D4711F71',
            'MID' => '8080082790',
            'AIR' => '649188',
            'TXNAMOUNT' => '100.00',
            'LTD' => '20170228',
            'LTT' => '155734',
            'RRN' => '247059001425',
        ], $api->getTransactionData([
            'ONO' => $ONO,
        ]));
    }

    public function testRefundTransaction()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with([
            'TYP' => $TYP = '71',
            'ONO' => $ONO = '58B52D4711F71',
            'MID' => $options['MID'],
            'C' => null,
        ])->andReturn($params = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with('POST', 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf0160', [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($params))->andReturn($request = m::mock('Psr\Http\Message\RequestInterface'));

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn($response = m::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->twice()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = 'DATA=RC=00,MID=8089000016,ONO=1456296932846,LTD=20090605,LTT=151930&MACD=fb823f94e7584be22a2391843f6c6bdb99c9c9cba293b4cdd5de2155b1c2f09a'
        );

        $this->assertSame([
            'DATA' => 'RC=00,MID=8089000016,ONO=1456296932846,LTD=20090605,LTT=151930',
            'MACD' => 'fb823f94e7584be22a2391843f6c6bdb99c9c9cba293b4cdd5de2155b1c2f09a',
            'RC' => '00',
            'MID' => '8089000016',
            'ONO' => '1456296932846',
            'LTD' => '20090605',
            'LTT' => '151930',
        ], $api->refundTransaction([
            'ONO' => $ONO,
        ]));
    }

    public function testCancelTransaction()
    {
        $options = [
            'MID' => '8089000016',
            'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
            'sandbox' => true,
        ];
        $encrypter = m::mock('PayumTW\Esunbank\Encrypter');
        $encrypter->shouldReceive('setKey')->once()->with($options['M']);

        $api = new Api(
            $options,
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory'),
            $encrypter
        );

        $encrypter->shouldReceive('encryptRequest')->once()->with([
            'ONO' => $ONO = '58B52D4711F71',
            'MID' => $options['MID'],
        ])->andReturn($params = ['foo' => 'bar']);

        $messageFactory->shouldReceive('createRequest')->once()->with('POST', 'https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf0150', [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], http_build_query($params))->andReturn($request = m::mock('Psr\Http\Message\RequestInterface'));

        $httpClient->shouldReceive('send')->once()->with($request)->andReturn($response = m::mock('Psr\Http\Message\ResponseInterface'));
        $response->shouldReceive('getStatusCode')->twice()->andReturn(200);
        $response->shouldReceive('getBody->getContents')->once()->andReturn(
            $content = 'DATA={"returnCode":"00","txnData":{"RC":"00","ONO":"1462519794752","MID":"8089000016","AIR":"730942","LTD":"20160506","LTT":"153032","RRN":"106127000019"},"version":"2"}'
        );

        $this->assertSame([
            'DATA' => '{"returnCode":"00","txnData":{"RC":"00","ONO":"1462519794752","MID":"8089000016","AIR":"730942","LTD":"20160506","LTT":"153032","RRN":"106127000019"},"version":"2"}',
            'returnCode' => '00',
            'version' => '2',
            'RC' => '00',
            'ONO' => '1462519794752',
            'MID' => '8089000016',
            'AIR' => '730942',
            'LTD' => '20160506',
            'LTT' => '153032',
            'RRN' => '106127000019',
        ], $api->cancelTransaction([
            'ONO' => $ONO,
        ]));
    }

    // public function test_desktop_endpoint_url()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Arrange
    //     |------------------------------------------------------------
    //     */

    //     $httpClient = m::spy('Payum\Core\HttpClientInterface');
    //     $message = m::spy('Http\Message\MessageFactory');

    //     /*
    //     |------------------------------------------------------------
    //     | Act
    //     |------------------------------------------------------------
    //     */

    //     /*
    //     |------------------------------------------------------------
    //     | Assert
    //     |------------------------------------------------------------
    //     */

    //     $options = [
    //         'MID' => '8089000016',
    //         'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
    //         'sandbox' => true,
    //     ];
    //     $api = new Api($options, $httpClient, $message);
    //     $this->assertSame('https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014s', $api->getApiEndpoint());

    //     $options = [
    //         'MID' => '8089000016',
    //         'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
    //         'sandbox' => false,
    //     ];
    //     $api = new Api($options, $httpClient, $message);
    //     $this->assertSame('https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014s', $api->getApiEndpoint());
    // }

    // public function test_mobile_endpoint_url()
    // {
    //     /*
    //     |------------------------------------------------------------
    //     | Arrange
    //     |------------------------------------------------------------
    //     */

    //     $httpClient = m::spy('Payum\Core\HttpClientInterface');
    //     $message = m::spy('Http\Message\MessageFactory');

    //     /*
    //     |------------------------------------------------------------
    //     | Act
    //     |------------------------------------------------------------
    //     */

    //     /*
    //     |------------------------------------------------------------
    //     | Assert
    //     |------------------------------------------------------------
    //     */

    //     $options = [
    //         'MID' => '8089000016',
    //         'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
    //         'mobile' => true,
    //         'sandbox' => true,
    //     ];
    //     $api = new Api($options, $httpClient, $message);
    //     $this->assertSame('https://acqtest.esunbank.com.tw/ACQTrans/esuncard/txnf014m', $api->getApiEndpoint());

    //     $options = [
    //         'MID' => '8089000016',
    //         'M' => 'WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B',
    //         'mobile' => true,
    //         'sandbox' => false,
    //     ];
    //     $api = new Api($options, $httpClient, $message);
    //     $this->assertSame('https://acq.esunbank.com.tw/ACQTrans/esuncard/txnf014m', $api->getApiEndpoint());
    // }
}
