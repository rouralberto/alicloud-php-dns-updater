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
    public $domainName;
    public $rR;
    public $type;
    public $value;
    public $tTL;
    public $accessKeyId;
    public $accessKeySecret;

    /**
     * AlicloudUpdateRecord constructor.
     *
     * @param String $accessKeyId
     * @param String $accessKeySecret
     */
    function __construct(String $accessKeyId, String $accessKeySecret)
    {
        $this->accessKeyId     = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    /**
     * @param String $CanonicalQueryString
     * @return string
     */
    public function getSignature(String $CanonicalQueryString): string
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
     * @throws Exception
     */
    public function getRecordId()
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
     * @param String $domainName
     */
    public function setDomainName(String $domainName)
    {
        $this->domainName = $domainName;
    }

    /**
     * @param String $value
     */
    public function setValue(String $value)
    {
        $this->value = $value;
    }

    /**
     * @param String $rR
     */
    public function setRR(String $rR)
    {
        $this->rR = $rR;
    }

    /**
     * @param String $recordId
     */
    public function setRecordId(String $recordId)
    {
        $this->recordId = $recordId;
    }

    /**
     * @param String $type
     */
    public function setRecordType(String $type)
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
