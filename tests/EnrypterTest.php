<?php

namespace PayumTW\Esunbank\Tests;

use Mockery as m;
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
