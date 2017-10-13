<?php

class CurlHelper
{
    /**
     * @var resource
     */
    private $ch;

    /**
     * @var string
     */
    private $url = '';

    /**
     * @var bool
     */
    private $isSslVerify = false;

    /**
     * @var bool
     */
    private $isPostRequest = false;

    /**
     * @var bool
     */
    private $isGetRequest = true;

    /**
     * @var array
     */
    private $postData = [];

    /**
     * @var array
     */
    private $httpHeader = [];

    /**
     * @var bool
     */
    private $returnTransfer = true;

    /**
     * @var bool
     */
    private $isSafeUpload = true;

    /**
     * @var mixed
     */
    private $return;

    /**
     * @var int
     */
    private $errorNumber = 0;

    /**
     * @var string
     */
    private $error = '';

    /**
     * @var string
     */
    private $customRequest = '';

    /**
     * Curl constructor.
     */
    public function __construct()
    {
        $this->ch = curl_init();

        $this->enableSafeUpload();
        $this->disableSslVerify();
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
        }

        $this->disableGetRequest();

        return $this;
    }

    /**
     * @param bool $postRequest
     */
    private function setPostRequest($postRequest)
    {
        $this->isPostRequest = (bool) $postRequest;
        curl_setopt($this->ch, CURLOPT_POST, $this->isPostRequest);
    }

    /**
     * @return array
     */
    public function getPostDate()
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
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, http_build_query($this->postData));

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

    private function disablePostRequest()
    {
        $this->setPostRequest(false);
        
        $this->deletePostData();
    }

    /**
     * @return bool
     */
    public function isGetRequest()
    {
        return $this->isGetRequest;
    }

    private function disableGetRequest()
    {
        $this->setGetRequest(false);
    }

    /**
     * @return $this
     */
    public function enableGetRequest()
    {
        $this->setGetRequest(true);

        $this->disablePostRequest();

        return $this;
    }

    /**
     * @param bool $getRequest
     */
    private function setGetRequest($getRequest)
    {
        $this->isGetRequest = (bool) $getRequest;
        curl_setopt($this->ch, CURLOPT_HTTPGET, $this->isGetRequest);
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
     */
    private function setReturnTransfer($returnTransfer)
    {
        $this->returnTransfer = (bool) $returnTransfer;
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, $this->returnTransfer);
    }

    /**
     * @param bool $safeUpload
     */
    private function setSafeUpload($safeUpload)
    {
        $this->isSafeUpload = (bool) $safeUpload;
        curl_setopt($this->ch, CURLOPT_SAFE_UPLOAD, $this->isSafeUpload);
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
     *
     * @return mixed
     */
    public function execute($url = '')
    {
        if ($this->url === '' && $url !== '') {
            $this->setUrl($url);
        }

        $this->return      = curl_exec($this->ch);
        $this->errorNumber = curl_errno($this->ch);
        $this->error       = curl_error($this->ch);

        if ($this->returnTransfer === false) {
            echo $this->return;
        }

        return curl_exec($this->ch);
    }

    /**
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
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
}
