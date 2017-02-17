<?php
namespace BTCCOM\DirectMail\Test;

use BTCCOM\DirectMail\DirectMailException;
use Dm\Request\V20151123 as DM;
use BTCCOM\DirectMail\DirectMailTransport;
use PHPUnit_Framework_TestCase;

require_once __DIR__ . "/../src/aliyun-php-sdk-dm/aliyun-php-sdk-core/Config.php";

class DirectMailTransportTest extends PHPUnit_Framework_TestCase {
    public function testSingleReceiver() {
        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('me@example.com');
        $message->setBcc('you@example.com');

        $directmail_client = $this->getMockBuilder(\DefaultAcsClient::class)
            ->setMethods(['getAcsResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $directmail_client->expects($this->once())
            ->method('getAcsResponse')
            ->with($this->callback(function ($request) {
                return $request instanceof DM\SingleSendMailRequest &&
                    $request->getAccountName() === 'account_name' &&
                    $request->getFromAlias() === 'account_alias';
            }));

        $transport = new DirectMailTransport($directmail_client, 'account_name', 'account_alias');

        $result = $transport->send($message);

        $this->assertEquals(1, $result);
    }

    public function testMultipleReceivers() {
        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');

        $message->setTo([
            'me@example.com',
            'you@example.com' => 'You',
            'he@example.com' => 'He',
        ]);

        $message->setBcc('you@example.com');

        $directmail_client = $this->getMockBuilder(\DefaultAcsClient::class)
            ->setMethods(['getAcsResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $directmail_client->expects($this->once())
            ->method('getAcsResponse')
            ->with($this->callback(function ($request) {
                return $request instanceof DM\SingleSendMailRequest &&
                    $request->getAccountName() === 'account_name' &&
                    $request->getFromAlias() === 'account_alias' &&
                    $request->getToAddress() === 'me@example.com,you@example.com,he@example.com';
            }));

        $transport = new DirectMailTransport($directmail_client, 'account_name', 'account_alias');

        $result = $transport->send($message);

        $this->assertEquals(1, $result);
    }

    public function testException() {
        $message = new \Swift_Message('Foo subject', 'Bar body');
        $message->setSender('myself@example.com');
        $message->setTo('ekousp!@outlook.com');

        $directmail_client = $this->getMockBuilder(\DefaultAcsClient::class)
            ->setMethods(['getAcsResponse'])
            ->disableOriginalConstructor()
            ->getMock();

        $directmail_client->expects($this->once())
            ->method('getAcsResponse')
            ->willThrowException(new \ServerException('msg', 'code'));

        $transport = new DirectMailTransport($directmail_client, 'noreply@mail.btc.com', 'BTC.com');

        try {
            $transport->send($message);
        } catch (DirectMailException $e) {
            $this->assertEquals('code', $e->getMessage());
            $this->assertEquals(400, $e->getCode());

            return;
        }

        $this->fail('Transport::send should throw an exception');
    }

}