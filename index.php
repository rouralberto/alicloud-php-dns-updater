<?php

date_default_timezone_set('UTC');

include_once 'alicloud-php-updaterecord/V20150109/AlicloudUpdateRecord.php';

use Roura\Alicloud\V20150109\AlicloudUpdateRecord;

$AccessKeyId     = 'ACCESS_KEY_ID';
$AccessKeySecret = 'ACCESS_KEY_SECRET';
$updater         = new AlicloudUpdateRecord($AccessKeyId, $AccessKeySecret);

$newIp = $_SERVER['REMOTE_ADDR']; // New IP

$updater->setDomainName('DOMAIN.COM');
$updater->setRecordType('A');
$updater->setRR('@');
$updater->setValue($newIp);

print_r($updater->sendRequest());
