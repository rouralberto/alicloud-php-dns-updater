# Alicloud PHP DNS Updater

PHP DNS auto-updater for Alicloud/Aliyun/Alidns. The script uses the Alicloud API to update a domains DNS. This script can be automated in a cron job to easily keep up to date the target IP in a given domain.

Code is meant to run on PHP 7.0+

## Example

```php
<?php

date_default_timezone_set('UTC');

include_once 'alicloud-php-updaterecord/V20150109/AlicloudUpdateRecord.php';

use Roura\Alicloud\V20150109\AlicloudUpdateRecord;

$AccessKeyId     = 'ACCESS_KEY_ID';
$AccessKeySecret = 'ACCESS_KEY_SECRET';
$updater         = new AlicloudUpdateRecord($AccessKeyId, $AccessKeySecret);

$newIp = '<NEW_IP_GOES_HERE>;

$updater->setDomainName('<DOMAIN_NAME_HERE>');
$updater->setRecordType('A');
$updater->setRR('@');
$updater->setValue($newIp);

print_r($updater->sendRequest());
```
