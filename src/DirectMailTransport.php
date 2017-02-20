<?php
namespace BTCCOM\DirectMail;

use Dm\Request\V20151123 as DM;
use Illuminate\Mail\Transport\Transport;

/**
 * @link API Reference: https://help.aliyun.com/document_detail/29444.html
 * @link PHPSDK: https://help.aliyun.com/document_detail/29460.html
 */
class DirectMailTransport extends Transport {
    protected $acs_client;
    protected $account_name;
    protected $account_alias;

    public function __construct(\DefaultAcsClient $acs_client, $account_name, $account_alias) {
        $this->acs_client = $acs_client;
        $this->account_name = $account_name;
        $this->account_alias = $account_alias;
    }

    /**
     * @param \Swift_Mime_Message $message
     * @param null $failedRecipients
     * @return int
     */
    public function send(\Swift_Mime_Message $message, &$failedRecipients = null) {
        $this->beforeSendPerformed($message);

        $this->sendSingle($message);

        $this->sendPerformed($message);

        return 1;
    }

    protected function buildException(\ClientException $e) {
        if ($e->getErrorCode() === 'InvalidToAddress') {
            return new InvalidToAddressException($e->getErrorCode(), 400, $e);
        }

        return new DirectMailException(
            $e->getErrorCode(),      // errCode 为 API 定义的错误消息字符串
            400,        // 返回码均为 400
            $e
        );
    }

    protected function sendSingle(\Swift_Mime_Message $message) {
        $request = new DM\SingleSendMailRequest();

        $request->setAccountName($this->account_name);    //控制台创建的发信地址
        $request->setFromAlias($this->account_alias);
        $request->setAddressType(1);
        $request->setReplyToAddress('true');

        $request->setToAddress($this->getToAddress($message));
        $request->setSubject($message->getSubject());
        $request->setHtmlBody($message->getBody());

        try {
            $this->acs_client->getAcsResponse($request);
        } catch (\ClientException $e) {     // 阿里云错误码定义不合理，ClientException 包含了 ServerException
            throw $this->buildException($e);
        }

        return 1;
    }

    // 多个地址使用逗号分隔
    protected function getToAddress(\Swift_Mime_Message $message) {
        return join(',', array_keys($message->getTo()));
    }
}