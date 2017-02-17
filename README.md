# 阿里云邮件推送 Aliyun DirectMail for Laravel 5

使用阿里云的 DirectMail 发送邮件。

当前实现仅支持[单一发信接口](https://help.aliyun.com/document_detail/29444.html)。

## 安装

1. 使用 `composer` 安装文件

   ```bash
   composer require btccom/aliyun-direct-mail
   ```

2. 发布配置文件

   ```
   php artisan vendor:publish
   ```

3. 在 `config/directmail.php` 中根据需要修改配置

   ```
   'directmail' => [
       'app_key'    => env('DIRECT_MAIL_APP_KEY'),
       'app_secret' => env('DIRECT_MAIL_APP_SECRET'),
       'region'     => 'cn-beijing',
       'account'    => [
           'alias' => env('DIRECT_MAIL_ACCOUNT_ALIAS'),
           'name' => env('DIRECT_MAIL_ACCOUNT_NAME'),
       ]
   ]
   ```

   具体配置含义请参考[官方文档](https://help.aliyun.com/document_detail/29444.html)。

   建议在`.env`中创建环境配置。

4. 修改 `config/mail.php` 中的 `driver` 为 `directmail`（或者 `.env` 中的 `MAIL_DRIVER`）。

5. 修改 `config/app.php`，在`providers`字段中添加：

   ```
   'providers' => [
       ...
       BTCCOM\DirectMail\AliyunDirectMailServiceProvider::class,
       ...
   ],
   ```