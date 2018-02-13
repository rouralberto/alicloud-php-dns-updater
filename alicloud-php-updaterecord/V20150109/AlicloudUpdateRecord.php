<?php

namespace Roura\Alicloud\V20150109;

use \Exception;

/**
 * Class AlicloudUpdateRecord
 *
 * @package Roura\Alicloud\V20150109
 */
class AlicloudUpdateRecord
{
    /**
     * @var string
     */
    public $domainName;

    /**
     * @var string
     */
    public $rR;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $accessKeyId;

    /**
     * @var string
     */
    public $accessKeySecret;

    /**
     * AlicloudUpdateRecord constructor.
     *
     * @param string $accessKeyId
     * @param string $accessKeySecret
     */
    function __construct(
        string $accessKeyId,
        string $accessKeySecret
    ) {
        $this->accessKeyId     = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @param string $CanonicalQueryString
     * @return string
     */
    public function getSignature(string $CanonicalQueryString): string
    {
        $HTTPMethod                  = 'GET';
        $slash                       = urlencode('/');
        $EncodedCanonicalQueryString = urlencode($CanonicalQueryString);
        $StringToSign                = "{$HTTPMethod}&{$slash}&{$EncodedCanonicalQueryString}";
        $StringToSign                = str_replace('%40', '%2540', $StringToSign);
        $HMAC                        = hash_hmac('sha1', $StringToSign, "{$this->accessKeySecret}&", true);

        return base64_encode($HMAC);
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        $timestamp = date('U');
        $date      = date('Y-m-d', $timestamp);
        $H         = date('H', $timestamp);
        $i         = date('i', $timestamp);
        $s         = date('s', $timestamp);

        return "{$date}T{$H}%3A{$i}%3A{$s}";
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRecordId(): string
    {
        $queries = [
            'AccessKeyId' => $this->accessKeyId,
            'Action' => 'DescribeDomainRecords',
            'DomainName' => $this->domainName,
            'Format' => 'json',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => random_int(1000000000, 9999999999),
            'SignatureVersion' => '1.0',
            'Timestamp' => $this->getDate(),
            'Version' => '2015-01-09'
        ];

        $response = $this->doRequest($queries);

        $recordList = $response['DomainRecords']['Record'];

        $RR = null;
        foreach ($recordList as $key => $record) {
            if ($this->rR === $record['RR']) {
                $RR = $record;
            }
        }

        if ($RR === null) {
            die('RR ' . $this->rR . ' not found.');
        }

        return $RR['RecordId'];
    }

    /**
     * @param string $domainName
     */
    public function setDomainName(string $domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @param string $value
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @param string $rR
     */
    public function setRR(string $rR)
    {
        $this->rR = $rR;
    }

    /**
     * @param string $recordId
     */
    public function setRecordId(string $recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * @param string $type
     */
    public function setRecordType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @param array $queries
     * @return array
     */
    public function doRequest(Array $queries): array
    {
        $CanonicalQueryString = '';
        $i                    = 0;
        foreach ($queries as $param => $query) {
            $CanonicalQueryString .= $i === 0 ? null : '&';
            $CanonicalQueryString .= "{$param}={$query}";
            $i++;
        }

        $signature  = $this->getSignature($CanonicalQueryString);
        $requestUrl = "http://dns.aliyuncs.com/?{$CanonicalQueryString}&Signature=" . urlencode($signature);
        $response   = file_get_contents($requestUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true
            ]
        ]));

        return json_decode($response, true);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function sendRequest(): array
    {
        $queries = [
            'AccessKeyId' => $this->accessKeyId,
            'Action' => 'UpdateDomainRecord',
            'Format' => 'json',
            'RR' => $this->rR,
            'RecordId' => $this->getRecordId(),
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => random_int(1000000000, 9999999999),
            'SignatureVersion' => '1.0',
            'Timestamp' => $this->getDate(),
            'Type' => $this->type,
            'Value' => $this->value,
            'Version' => '2015-01-09'
        ];

        return $this->doRequest($queries);
    }
}
