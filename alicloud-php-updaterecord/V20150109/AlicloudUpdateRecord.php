<?php

namespace Roura\Alicloud\V20150109;

class AlicloudUpdateRecord
{
    public $rR;
    public $recordId;
    public $type;
    public $value;
    public $tTL;
    public $accessKeyId;
    public $accessKeySecret;

    function __construct($accessKeyId, $accessKeySecret)
    {
        $this->accessKeyId     = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
    }

    public function getSignature($CanonicalQueryString): string
    {
        $HTTPMethod                  = 'GET';
        $slash                       = urlencode('/');
        $EncodedCanonicalQueryString = urlencode($CanonicalQueryString);
        $StringToSign                = "{$HTTPMethod}&{$slash}&{$EncodedCanonicalQueryString}";
        $StringToSign                = str_replace('%40', '%2540', $StringToSign);
        $HMAC                        = hash_hmac('sha1', $StringToSign, "{$this->accessKeySecret}&", true);
        $base64HMAC                  = base64_encode($HMAC);
        $urlbase64HMAC               = urlencode($base64HMAC);

        return $urlbase64HMAC;
    }

    public function getDate(): string
    {
        $timestamp = date('U');
        $date      = date('Y-m-d', $timestamp);
        $H         = date('H', $timestamp);
        $i         = date('i', $timestamp);
        $s         = date('s', $timestamp);

        return "{$date}T{$H}%3A{$i}%3A{$s}";
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function setRR($rR)
    {
        $this->rR = $rR;
    }

    public function setRecordId($recordId)
    {
        $this->recordId = $recordId;
    }

    public function setRecordType($type)
    {
        $this->type = $type;
    }

    public function sendRequest(): array
    {
        $queries = [
            'AccessKeyId' => $this->accessKeyId,
            'Action' => 'UpdateDomainRecord',
            'Format' => 'json',
            'RR' => $this->rR,
            'RecordId' => $this->recordId,
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => random_int(1000000000, 9999999999),
            'SignatureVersion' => '1.0',
            'Timestamp' => $this->getDate(),
            'Type' => $this->type,
            'Value' => $this->value,
            'Version' => '2015-01-09'
        ];

        $CanonicalQueryString = '';
        $i                    = 0;
        foreach ($queries as $param => $query) {
            $CanonicalQueryString .= $i === 0 ? null : '&';
            $CanonicalQueryString .= "{$param}={$query}";
            $i++;
        }

        $signature  = $this->getSignature($CanonicalQueryString);
        $requestUrl = "http://dns.aliyuncs.com/?{$CanonicalQueryString}&Signature={$signature}";
        $response   = file_get_contents($requestUrl, false, stream_context_create([
            'http' => [
                'ignore_errors' => true
            ]
        ]));

        return json_decode($response, true);
    }
}
