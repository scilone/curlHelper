<?php

namespace Scilone\CurlHelper;

/**
 * Class CurlHelper
 */
class CurlHelper
{
    const THROW_BAD_ENCODING = 'Unknown encoding set, encoding available are : "", "identity", "deflate", "gzip"';
    const THROW_BAD_HTTP_AUTH = 'Unknown http auth set';

    /**
     * @var resource
     */
    private $ch;

    /**
     * @var string
     */
    private $url;

    /**
     * @var bool
     */
    private $isSslVerify;

    /**
     * @var bool
     */
    private $isPostRequest;

    /**
     * @var bool
     */
    private $isGetRequest;

    /**
     * @var array
     */
    private $postData;

    /**
     * @var array
     */
    private $httpHeader;

    /**
     * @var bool
     */
    private $returnTransfer;

    /**
     * @var bool
     */
    private $isSafeUpload;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var int
     */
    private $errorNumber;

    /**
     * @var string
     */
    private $error;

    /**
     * @var string
     */
    private $customRequest;

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var array
     */
    private $info;

    /**
     * @var bool
     */
    private $noSignal;

    /**
     * @var int
     */
    private $connectTimeout; //in ms

    /**
     * @var int
     */
    private $timeout; //in ms

    /**
     * @var bool
     */
    private $failOnError;

    /**
     * @var bool
     */
    private $getBodyResponse;

    /**
     * @var bool
     */
    private $includeHeader;

    /**
     * @var bool
     */
    private $followLocation;

    /**
     * @var int
     */
    private $maxRedirection;

    /**
     * @var array
     */
    private $cookies;

    /**
     * @var int
     */
    private $httpAuth;

    /**
     * @var array
     */
    private $httpIdentifiers;

    /**
     * Curl constructor.
     */
    public function __construct()
    {
        $this->initCurl();
    }

    /**
     * Reset all params in class with default value
     */
    private function resetParams()
    {
        $this->url             = '';
        $this->result          = '';
        $this->isSslVerify     = false;
        $this->isPostRequest   = false;
        $this->isGetRequest    = true;
        $this->postData        = [];
        $this->httpHeader      = [];
        $this->returnTransfer  = true;
        $this->isSafeUpload    = true;
        $this->errorNumber     = 0;
        $this->error           = '';
        $this->customRequest   = '';
        $this->encoding        = '';
        $this->info            = [];
        $this->noSignal        = true;
        $this->timeout         = 0;
        $this->connectTimeout  = 0;
        $this->failOnError     = false;
        $this->getBodyResponse = false;
        $this->includeHeader   = false;
        $this->followLocation  = true;
        $this->maxRedirection  = -1;
        $this->cookies         = [];
        $this->httpAuth        = '';
        $this->httpIdentifiers = [];
    }

    /**
     * @return void
     */
    private function initCurl()
    {
        $this->ch = curl_init();

        $this->resetParams();
        $this->enableSafeUpload();
        $this->enableReturnTransfer();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = (string) $url;
        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSslVerify()
    {
        return $this->isSslVerify;
    }

    /**
     * @return $this
     */
    public function enableSslVerify()
    {
        $this->setSslVerify(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSslVerify()
    {
        $this->setSslVerify(false);

        return $this;
    }

    /**
     * @param bool $sslVerify
     */
    private function setSslVerify($sslVerify)
    {
        $this->isSslVerify = (bool) $sslVerify;
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, $this->isSslVerify);
    }

    /**
     * @return bool
     */
    public function isPostRequest()
    {
        return $this->isPostRequest;
    }

    /**
     * @param array|null $dataPost
     *
     * @return $this
     */
    public function enablePostRequest(array $dataPost = null)
    {
        $this->setPostRequest(true);

        if ($dataPost !== null) {
            $this->setPostData($dataPost);
        } else {
            $this->addHttpHeader('Content-Length', '0');
        }

        $this->disableGetRequest();

        return $this;
    }

    /**
     * @param bool $postRequest
     *
     * @return $this
     */
    private function setPostRequest($postRequest)
    {
        $this->isPostRequest = (bool) $postRequest;
        curl_setopt($this->ch, CURLOPT_POST, $this->isPostRequest);

        return $this;
    }

    /**
     * @return array
     */
    public function getPostData()
    {
        return $this->postData;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setPostData(array $data)
    {
        $this->postData = $data;

        if (isset($this->httpHeader['Content-Type']) === true
            && strpos($this->httpHeader['Content-Type'], 'json') !== false
        ) {
            $postDataQuery  = json_encode($this->postData);
        } else {
            $postDataQuery  = http_build_query($this->postData);
        }

        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postDataQuery);

        $this->addHttpHeader('Content-Length', strlen($postDataQuery));

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function addPostData(array $data)
    {
        $this->setPostData($this->postData + $data);

        return $this;
    }

    /**
     * @return $this
     */
    public function deletePostData()
    {
        $this->setPostData([]);

        return $this;
    }

    /**
     * @return $this
     */
    private function disablePostRequest()
    {
        $this->setPostRequest(false);

        $this->deletePostData();

        return $this;
    }

    /**
     * @return bool
     */
    public function isGetRequest()
    {
        return $this->isGetRequest;
    }

    /**
     * @return $this
     */
    private function disableGetRequest()
    {
        $this->setGetRequest(false);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableGetRequest()
    {
        $this->disablePostRequest();

        $this->setGetRequest(true);

        return $this;
    }

    /**
     * @param bool $getRequest
     *
     * @return $this
     */
    private function setGetRequest($getRequest)
    {
        $this->isGetRequest = (bool) $getRequest;
        curl_setopt($this->ch, CURLOPT_HTTPGET, $this->isGetRequest);

        return $this;
    }

    /**
     * @return array
     */
    public function getHttpHeader()
    {
        return $this->httpHeader;
    }

    /**
     * @param array $header
     *
     * @return $this
     */
    public function setHttpHeader(array $header)
    {
        $this->httpHeader = $header;
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->httpHeader);

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addHttpHeader($name, $value)
    {
        $name  = (string) $name;
        $value = (string) $value;

        $this->httpHeader[] = "$name: $value";

        $this->setHttpHeader($this->httpHeader);

        return $this;
    }

    /**
     * @return $this
     */
    public function deleteHttpHeader()
    {
        $this->setHttpHeader([]);

        return $this;
    }

    /**
     * @return bool
     */
    public function isReturnTransfer()
    {
        return $this->returnTransfer;
    }

    /**
     * @return $this
     */
    public function enableReturnTransfer()
    {
        $this->setReturnTransfer(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableReturnTransfer()
    {
        $this->setReturnTransfer(false);

        return $this;
    }

    /**
     * @param bool $returnTransfer
     *
     * @return $this
     */
    private function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = (bool) $returnTransfer;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->returnTransfer);

        return $this;
    }

    /**
     * @param bool $safeUpload
     *
     * @return $this
     */
    private function setSafeUpload($safeUpload)
    {
        $this->isSafeUpload = (bool) $safeUpload;
        curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, $this->isSafeUpload);

        return $this;
    }

    /**
     * @return bool
     */
    public function isSafeUpload()
    {
        return $this->isSafeUpload;
    }

    /**
     * @return $this
     */
    public function enableSafeUpload()
    {
        $this->setSafeUpload(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableSafeUpload()
    {
        $this->setSafeUpload(false);

        return $this;
    }

    /**
     * @param string $url
     * @param bool   $close
     *
     * @return mixed
     */
    public function execute($url = '', $close = false)
    {
        if ($url !== '') {
            $this->setUrl($url);
        }

        $this->result      = curl_exec($this->ch);
        $this->errorNumber = curl_errno($this->ch);
        $this->error       = curl_error($this->ch);
        $this->info        = curl_getinfo($this->ch);

        if ($this->returnTransfer === false) {
            echo $this->result;
        }

        if ($close === true) {
            $this->close();
        }

        return $this->result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $customRequest
     *
     * @return $this
     */
    public function setCustomRequest($customRequest)
    {
        $this->customRequest = (string) $customRequest;
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $this->customRequest);

        $this->disablePostRequest();

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomRequest()
    {
        return $this->customRequest;
    }

    /**
     * @return $this
     */
    public function renew()
    {
        $this->initCurl();

        return $this;
    }

    /**
     * @param string $encoding
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setEncoding($encoding)
    {
        if (in_array($encoding, ['', 'identity', 'deflate', 'gzip']) === false) {
            throw new Exception(self::THROW_BAD_ENCODING);
        }

        $this->encoding = (string) $encoding;
        curl_setopt($this->ch, CURLOPT_ENCODING, $this->encoding);

        return $this;
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return void
     */
    public function close()
    {
        curl_close($this->ch);
    }

    /**
     * @return bool
     */
    public function isNoSignal()
    {
        return $this->noSignal;
    }

    /**
     * @param bool $noSignal
     *
     * @return $this
     */
    private function setNoSignal($noSignal)
    {
        $this->noSignal = (bool) $noSignal;
        curl_setopt($this->ch, CURLOPT_NOSIGNAL, $this->noSignal);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableNoSignal()
    {
        $this->setNoSignal(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableNoSignal()
    {
        $this->setNoSignal(false);

        return $this;
    }

    /**
     * @param bool $timeInMs //true if you want the time in millisecondes
     *
     * @return int
     */
    public function getConnectTimeout($timeInMs = false)
    {
        return $this->connectTimeout / $this->coeffMsToSecondes($timeInMs);
    }

    /**
     * Set the connect timeout, by default is in secondes, use second param if you wanna set it in ms
     *
     * @param int  $connectTimeout
     * @param bool $timeInMs        //true if you want the time in millisecondes
     *
     * @return $this
     */
    public function setConnectTimeout($connectTimeout, $timeInMs = false)
    {
        $this->connectTimeout = (int) $connectTimeout * $this->coeffMsToSecondes($timeInMs);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT_MS, $this->connectTimeout);

        return $this;
    }

    /**
     * @param bool $timeInMs
     *
     * @return int
     */
    private function coeffMsToSecondes($timeInMs = false)
    {
        $coeff = 1000;
        if ($timeInMs === true) {
            $coeff = 1;
        }

        return $coeff;
    }

    /**
     * @param bool $timeInMs //true if you want the time in millisecondes
     *
     * @return int
     */
    public function getTimeout($timeInMs = false)
    {
        return $this->timeout / $this->coeffMsToSecondes($timeInMs);
    }

    /**
     * Set the timeout, by default is in secondes, use second param if you wanna set it in ms
     *
     * @param int  $timeout
     * @param bool $timeInMs //true if you want the time in millisecondes
     *
     * @return $this
     */
    public function setTimeout($timeout, $timeInMs = false)
    {
        $this->timeout = (int) $timeout * $this->coeffMsToSecondes($timeInMs);
        curl_setopt($this->ch, CURLOPT_TIMEOUT_MS, $this->timeout);

        return $this;
    }

    /**
     * @return bool
     */
    public function isFailingOnError()
    {
        return $this->failOnError;
    }

    /**
     * @param bool $failOnError
     *
     * @return $this
     */
    private function setFailOnError($failOnError)
    {
        $this->failOnError = (bool) $failOnError;
        curl_setopt($this->ch, CURLOPT_FAILONERROR, $this->failOnError);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableFailingOnError()
    {
        $this->setFailOnError(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableFailingOnError()
    {
        $this->setFailOnError(false);

        return $this;
    }

    /**
     * @return bool
     */
    public function isGettingBodyResponse()
    {
        return $this->getBodyResponse;
    }

    /**
     * @param bool $getBodyResponse
     *
     * @return $this
     */
    private function setGetBodyResponse($getBodyResponse)
    {
        $this->getBodyResponse = (bool) $getBodyResponse;
        curl_setopt($this->ch, CURLOPT_NOBODY, $this->getBodyResponse === false);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableGettingBodyResponse()
    {
        $this->setGetBodyResponse(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableGettingBodyResponse()
    {
        $this->setGetBodyResponse(false);

        return $this;
    }

    /**
     * @return bool
     */
    public function isIncludingHeader()
    {
        return $this->includeHeader;
    }

    /**
     * @param bool $includeHeader
     *
     * @return $this
     */
    private function setIncludeHeader($includeHeader)
    {
        $this->includeHeader = (bool) $includeHeader;
        curl_setopt($this->ch, CURLOPT_HEADER, $this->includeHeader);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableIncludingHeader()
    {
        $this->setIncludeHeader(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableIncludingHeader()
    {
        $this->setIncludeHeader(false);

        return $this;
    }

    /**
     * @return bool
     */
    public function isFollowingLocation()
    {
        return $this->followLocation;
    }

    /**
     * @param bool $followLocation
     *
     * @return $this
     */
    private function setFollowLocation($followLocation)
    {
        $this->followLocation = (bool) $followLocation;
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, $this->followLocation);

        return $this;
    }

    /**
     * @return $this
     */
    public function enableFollowingLocation()
    {
        $this->setFollowLocation(true);

        return $this;
    }

    /**
     * @return $this
     */
    public function disableFollowingLocation()
    {
        $this->setFollowLocation(false);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxRedirection()
    {
        return $this->maxRedirection;
    }

    /**
     * @param int $maxRedirection
     *
     * @return $this
     */
    public function setMaxRedirection($maxRedirection)
    {
        $this->maxRedirection = (int) $maxRedirection;
        curl_setopt($this->ch, CURLOPT_MAXREDIRS, $this->maxRedirection);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function enableCookieJar($name)
    {
        $name = (string) $name;

        $this->addCookie($name, '');

        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $name);

        return $this;
    }

    /**
     * @param array $cookies
     *
     * @return $this
     */
    public function setCookies(array $cookies)
    {
        $this->cookies = $cookies;

        $cookiesFormatted = [];
        foreach ($this->cookies as $cookieName => $cookieValue) {
            $cookiesFormatted[]="$cookieName=$cookieValue";
        }

        curl_setopt($this->ch, CURLOPT_COOKIE, implode(';', $cookiesFormatted));

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addCookie($name, $value)
    {
        $this->cookies[(string) $name] = (string) $value;

        $this->setCookies($this->cookies);

        return $this;
    }

    /**
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * @param int $httpAuth
     *
     * @throws Exception
     *
     * @return $this
     */
    public function setHttpAuth($httpAuth)
    {
        $this->httpAuth = (int) $httpAuth;

        $arrayAuth = [
            CURLAUTH_BASIC,
            CURLAUTH_DIGEST,
            CURLAUTH_GSSNEGOTIATE,
            CURLAUTH_NTLM,
            CURLAUTH_ANY,
            CURLAUTH_ANYSAFE
        ];

        if (in_array($httpAuth, $arrayAuth) === false) {
            throw new Exception(self::THROW_BAD_HTTP_AUTH);
        }

        curl_setopt($this->ch, CURLOPT_HTTPAUTH, $this->httpAuth);

        return $this;
    }

    /**
     * @return int
     */
    public function getHttpAuth()
    {
        return $this->httpAuth;
    }

    /**
     * @param string $username
     * @param string $password
     *
     * @return $this
     */
    public function setHttpIdentifiers($username, $password)
    {
        $this->httpIdentifiers['username'] = (string) $username;
        $this->httpIdentifiers['password'] = (string) $password;

        curl_setopt(
            $this->ch,
            CURLOPT_USERPWD,
            $this->httpIdentifiers['username'] . ':' . $this->httpIdentifiers['password']
        );

        return $this;
    }

    /**
     * @return array
     */
    public function getHttpIdentifiers()
    {
        return $this->httpIdentifiers;
    }
}
