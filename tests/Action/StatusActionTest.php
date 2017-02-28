<?php

namespace PayumTW\Esunbank\Tests\Action;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Payum\Core\Bridge\Spl\ArrayObject;
use PayumTW\Esunbank\Action\StatusAction;

class StatusActionTest extends TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testRequestMarkNew()
    {
        $this->validate([
        ], 'markNew');
    }

    public function testRequestMarkCaptured()
    {
        $this->validate([
            'DATA' => 'RC=00,MID=8080000002,ONO=1456296932846,LTD=20160224, LTT=150228,RRN=506055000001,AIR=702715,AN=552199******1856&MACD=c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee',
            'MACD' => 'c9bf69b8489acb6d0b49f238e8e97ffd150466ac23dbf03d721e7c4a1c7b13ee',
            'RC' => '00',
            'MID' => '8080000002',
            'ONO' => '1456296932846',
            'LTD' => '20160224',
            'LTT' => '150228',
            'RRN' => '506055000001',
            'AIR' => '702715',
            'AN' => '552199******1856',
        ], 'markCaptured');

        // 查詢
        $this->validate([
            'DATA' => '{"returnCode":"00","txnData":{"RC":"00","ONO":"58B52D4711F71","MID":"8080082790","AIR":"649188","TXNAMOUNT":"100.00","LTD":"20170228","LTT":"155734","RRN":"247059001425"},"version":"2"}',
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
        ], 'markCaptured');
    }

    public function testRequestMarkRefunded()
    {
        $this->validate([
            'DATA' => 'RC=00,MID=8089000016,ONO=1456296932846,LTD=20090605,LTT=151930',
            'MACD' => 'fb823f94e7584be22a2391843f6c6bdb99c9c9cba293b4cdd5de2155b1c2f09a',
            'RC' => '00',
            'MID' => '8089000016',
            'ONO' => '1456296932846',
            'LTD' => '20090605',
            'LTT' => '151930',
        ], 'markRefunded');
    }

    public function testRequestMarkCancel()
    {
        $this->validate([
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
        ], 'markCanceled');
    }

    public function testRequestMarkFailed()
    {
        $this->validate([
            'DATA' => 'DATA={"returnCode":"GF","txnData":{"RC":"GF","ONO":"1462519794752","MID":"8089000016"},"version":"2"}',
            'returnCode' => 'GF',
            'version' => '2',
            'RC' => 'GF',
            'ONO' => '1462519794752',
            'MID' => '8089000016',
        ], 'markFailed');
    }

    protected function validate($input, $type)
    {
        $action = new StatusAction();
        $request = m::mock('Payum\Core\Request\GetStatusInterface');
        $request->shouldReceive('getModel')->andReturn($details = new ArrayObject($input));
        $request->shouldReceive($type)->once();

        $action->execute($request);
    }
}
