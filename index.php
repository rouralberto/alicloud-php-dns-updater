<?php

date_default_timezone_set('UTC');

include_once 'alicloud-php-updaterecord/V20150109/AlicloudUpdateRecord.php';

$AccessKeyId     = 'ACCESS_KEY_ID';
$AccessKeySecret = 'ACCESS_KEY_SECRET';

$newIp = 'xxx.xxx.xxx.xxx'; // New IP

$updater = new Roura\Alicloud\V20150109\AlicloudUpdateRecord($AccessKeyId, $AccessKeySecret);

$updater->setRecordId('XXXXXXXXXXXXXXXX');
$updater->setRecordType('A');
$updater->setRR('@');
$updater->setValue($newIp);

print_r($updater->sendRequest());
