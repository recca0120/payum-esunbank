<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
use PayumTW\Esunbank\Api;
use PayumTW\Esunbank\Encrypter;
use PHPUnit\Framework\TestCase;

class EnrypterTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testEncrypt()
    {
        $encrypter = new Encrypter();
        $encrypter->setKey('WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B');

        // (1)一般交易(無分期無紅利)
        $this->assertSame(
            '003f4aa7ee5607c29eee3b67d2943e83a7c4ddbf1b0b28175b83df4ca2747101',
            $encrypter->encrypt([
                'ONO' => '20160518100237',
                'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
                'MID' => '8089000016',
                'TA' => '879',
                'TID' => 'EC000001',
            ])
        );

        // (2)有分期無紅利
        $this->assertSame(
            'c8c420088a8600c467a75d8098a07ec9c000662e51e54f655d2c83f57c718541',
            $encrypter->encrypt([
                'ONO' => '20160518101607',
                'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
                'MID' => '8089000016',
                'IC' => '0100106',
                'TA' => '709',
                'TID' => 'EC000002',
            ])
        );

        // (3)無分期有紅利
        $this->assertSame(
            '374b4870a2cbbc8367eff3881455fce89b9c1bca895f5179a5a16e488f0bfb36',
            $encrypter->encrypt([
                'ONO' => '20160518102002',
                'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
                'MID' => '8089000016',
                'BPF' => 'Y',
                'TA' => '225',
                'TID' => 'EC000001',
            ])
        );

        // (4)有分期有紅利
        $this->assertSame(
            'e2d6079a5623815c09f5108789c867beb532a630756e12d27ecb1ecc3909dffe',
            $encrypter->encrypt([
                'ONO' => '20160518102121',
                'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
                'MID' => '8089000016',
                'BPF' => 'Y',
                'IC' => '0100106',
                'TA' => '288',
                'TID' => 'EC000002',
            ])
        );
    }

    public function testEncryptResponse()
    {
        $api = new Api(
            $options = [
                'M' => '8089000016',
            ],
            $httpClient = m::mock('Payum\Core\HttpClientInterface'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $encrypter = new Encrypter();
        $encrypter->setKey('WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B');

        // (1)一般交易(無分期無紅利)
        $this->assertSame(
            'fe58d597b3314776d600887c513bbf224a1d4be3d98b0f076be81d32086ec3cb',
            $encrypter->encrypt(
                $api->parseResponse(
                    'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856&MACD=fe58d597b3314776d600887c513bbf224a1d4be3d98b0f076be81d32086ec3cb'
                )
            )
        );

        // (2)有分期無紅利
        $this->assertSame(
            '51e649ad1e47551da41ea6eeb183f57ebf888a7bf81e9420028044c70a88880c',
            $encrypter->encrypt(
                $api->parseResponse(
                    'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,ITA=300.00,IP=3,IFPA=100.00,IPA=100.00&MACD=51e649ad1e47551da41ea6eeb183f57ebf888a7bf81e9420028044c70a88880c'
                )
            )
        );

        // (3)無分期有紅利
        $this->assertSame(
            '76584fb23a4cffd072ffdde1b9a4d30770559bca7e7d3da1b92c603991eafad5',
            $encrypter->encrypt(
                $api->parseResponse(
                    'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,BRP=0,BB=0,BRA=0.00&MACD=76584fb23a4cffd072ffdde1b9a4d30770559bca7e7d3da1b92c603991eafad5'
                )
            )
        );

        // (4)有分期有紅利
        $this->assertSame(
            '169d4d8f59112243b8f344f2eaccf950f189e862306cc7cff636959cb742b56b',
            $encrypter->encrypt(
                $api->parseResponse(
                    'DATA=RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224,LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856,ITA=300.00,IP=3,IFPA=100.00,IPA=100.00,BRP=0,BB=0,BRA=0.00&MACD=169d4d8f59112243b8f344f2eaccf950f189e862306cc7cff636959cb742b56b'
                )
            )
        );
    }

    public function testEncryptRequest()
    {
        $data = [
            'ONO' => '20160518100237',
            'U' => 'https://220.128.166.170/ACQTrans/test/print.jsp',
            'MID' => '8089000016',
            'TA' => '879',
            'TID' => 'EC000001',
        ];
        $encrypter = new Encrypter();
        $encrypter->setKey('WEGSC0Q7BAJGTQYL8BV8KRQRZXH6VK0B');

        $this->assertSame([
            'data' => json_encode($data, JSON_UNESCAPED_SLASHES),
            'mac' => '003f4aa7ee5607c29eee3b67d2943e83a7c4ddbf1b0b28175b83df4ca2747101',
            'ksn' => 1,
        ], $encrypter->encryptRequest($data));
    }
}
