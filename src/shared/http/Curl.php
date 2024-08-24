<?php declare(strict_types=1);
/*
 * This file is part of Phive.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de>, Sebastian Heuer <sebastian@phpeople.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace PharIo\Phive;

use const CURLINFO_HEADER_SIZE;
use const CURLINFO_HTTP_CODE;
use const CURLHEADER_UNIFIED;
use const CURLOPT_CAINFO;
use const CURLOPT_HEADER;
use const CURLOPT_HEADERFUNCTION;
use const CURLOPT_HEADEROPT;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_NOBODY;
use const CURLOPT_NOPROGRESS;
use const CURLOPT_PROGRESSFUNCTION;
use const CURLOPT_RESOLVE;
use function curl_errno;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;

/**
 * @codeCoverageIgnore
 */
class Curl {
    /** @var string */
    private $url;

    /** @var array<int, mixed> */
    private $options = [];

    /** @var int */
    private $httpCode = 0;

    public function init(string $url): void {
        $this->url      = $url;
        $this->options  = [];
        $this->httpCode = 0;
    }

    public function setResolve(string $resolveString): void {
        $this->options[CURLOPT_RESOLVE] = [$resolveString];
    }

    public function addHttpHeaders(array $headers): void {
echo '$headers in Curl::addHttpHeaders', PHP_EOL, var_export($headers, true), PHP_EOL;
        $this->options[CURLOPT_HTTPHEADER] = $headers;
    }

    public function disableProgressMeter(): void {
        $this->options[CURLOPT_NOPROGRESS] = true;
    }

    public function doNotReturnBody(): void {
        $this->options[CURLOPT_NOBODY] = true;
    }

    public function enableProgressMeter(callable $progressFunction): void {
        $this->options[CURLOPT_NOPROGRESS]       = false;
        $this->options[CURLOPT_PROGRESSFUNCTION] = $progressFunction;
    }

    public function setCertificateFile(string $filename): void {
        $this->options[CURLOPT_CAINFO] = $filename;
    }

    public function setHeaderFunction(callable $headerFunction): void {
echo 'CURLOPT_HEADERFUNCTION added to options',PHP_EOL;
        $this->options[CURLOPT_HEADER] = true;
//      $this->options[CURLOPT_HEADEROPT] = CURLHEADER_UNIFIED;
        $this->options[CURLOPT_HEADERFUNCTION] = $headerFunction;
    }

    public function setOptArray(array $options): void {
        $this->options = $options + $this->options;
    }

    /**
     * @throws CurlException
     */
    public function exec(): string {
echo '$this->url in Curl::exec', PHP_EOL, var_export($this->url, true), PHP_EOL;
        $ch = curl_init($this->url);
        assert($ch !== false);
echo '$this->options keys in Curl::exec before curl_setopt_array', PHP_EOL, var_export(array_keys($this->options), true), PHP_EOL;
        curl_setopt_array($ch, $this->options);
        $result         = curl_exec($ch);
echo '$result in Curl::exec', PHP_EOL, var_export($result, true), PHP_EOL;
        $this->httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo '$this->httpCode in Curl::exec', PHP_EOL, var_export($this->httpCode, true), PHP_EOL;
echo 'Received header size in Curl::exec: ',PHP_EOL, var_export(curl_getinfo($ch, CURLINFO_HEADER_SIZE), true), PHP_EOL;

        if ($result === false) {
            throw new CurlException(curl_error($ch), curl_errno($ch));
        }

        return (string)$result;
    }

    public function getHttpCode(): int {
        return $this->httpCode;
    }
}
