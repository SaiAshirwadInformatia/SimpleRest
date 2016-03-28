<?php
namespace saiashirwadinformatia;

class SimpleRest
{
    public $ch;

    public $url;

    public $method;

    public $data;

    public $headers;

    const USER_AGENT = 'SaiAshirwadInformatia-PHP/1.0';

    /**
     * @param $url
     * @param null   $method
     * @param array  $data
     * @param array  $headers
     */
    public function __construct($url = null, $method = 'GET', $data = [], $headers = [])
    {
        $this->method = "GET";
        $this->data = [];
        $this->headers = [
            "User-Agent" => self::USER_AGENT,
        ];
        $this->loadData($url, $method, $data, $headers);
    }

    /**
     * @param $url
     * @param $method
     * @param $data
     * @param $headers
     */
    private function loadData($url, $method, $data, $headers)
    {
        if (!is_null($url)) {
            $this->url = $url;
        }

        if (!is_null($method)) {
            $this->method = $method;
        }
        if (!is_null($data) and count($data) > 0) {
            if (count($this->data) > 0) {
                $this->data = array_merge($this->data, $data);
            } else {
                $this->data = $data;
            }
        }

        if (!is_null($headers) and count($headers) > 0) {
            if (count($this->headers) > 0) {
                $this->headers = array_merge($this->headers, $headers);
            } else {
                $this->headers = $headers;
            }
        }
    }

    public function init()
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->ch, CURLOPT_HEADER, true);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 600);

        // Ignore SSL certificate verification
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
        $this->data = null;
    }

    private function prepareRequest()
    {
        curl_setopt($this->ch, CURLOPT_URL, $this->url);

        if (!is_null($this->headers) and count($this->headers) > 0) {
            $request_headers = [];
            foreach ($this->headers as $key => $value) {
                if (strpos($value, ": ") !== false) {
                    $request_headers[] = $value;
                } else {
                    $request_headers[] = "$key: $value";
                }
            }
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $request_headers);
        }
        switch (strtolower($this->method)) {
            case "get":
                break;
            case "post":
                curl_setopt($this->ch, CURLOPT_POST, true);
                break;
            case "update":
                curl_setopt($this->ch, CURLOPT_PUT, true);
                break;
            case "delete":
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            default:
        }

        if (!is_null($this->data) and count($this->data) > 0) {
            if (isset($this->headers['Content-Type']) and
                strpos($this->headers['Content-Type'], 'application/json') !==
                false) {
                $json_data = json_encode($this->data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $json_data);
                $this->headers['Content-Length'] = strlen($json_data);
            } else {
                $data_str = http_build_query($this->data);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data_str);
            }
        }
    }

    /**
     * @param $url
     * @param $method
     * @param array     $data
     * @param array     $headers
     */
    public function execute($url, $method = 'GET', $data = [], $headers = [])
    {
        $this->init();
        $this->loadData($url, $method, $data, $headers);
        $this->prepareRequest();

        $response = curl_exec($this->ch);
        // Print error if any
        if (curl_errno($this->ch)) {
            $response = curl_error($this->ch);
        }
        $http_status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        // Then, after your curl_exec call:
        $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $header_size);
        $headers = explode("\n", $headers);

        $responseHeaders = [];
        foreach ($headers as $h) {
            if (strpos($h, 'HTTP/1.1') === 0) {
                continue;
            }
            if (is_null($h)) {
                continue;
            }
            $h = trim($h);
            if (empty($h)) {
                continue;
            }
            $responseHeaders[substr($h, 0, strpos($h, ":"))] = trim(
                substr($h, strpos($h, ":") + 1));
        }
        $body = substr($response, $header_size);

        if (isset($responseHeaders['Content-Type']) and !empty($body) and
            strpos($responseHeaders['Content-Type'], 'application/json') !== false) {
            $body = json_decode($body, true);
        }
        curl_close($this->ch);
        return [
            "response"         => $body,
            "status_code"      => $http_status,
            "response_headers" => $responseHeaders,
        ];
    }

    /**
     * @param  $url
     * @param  array   $data
     * @param  array   $headers
     * @return mixed
     */
    public function post($url, $data = [], $headers = [])
    {
        return $this->execute($url, 'post', $data, $headers);
    }

    /**
     * @param  $url
     * @param  array   $data
     * @param  array   $headers
     * @return mixed
     */
    public function get($url, $data = [], $headers = [])
    {
        return $this->execute($url, 'get', $data, $headers);
    }

    /**
     * @param  $url
     * @param  array   $data
     * @param  array   $headers
     * @return mixed
     */
    public function put($url, $data = [], $headers = [])
    {
        return $this->execute($url, 'put', $data, $headers);
    }

    /**
     * @param  $url
     * @param  array   $data
     * @param  array   $headers
     * @return mixed
     */
    public function delete($url, $data = [], $headers = [])
    {
        return $this->execute($url, 'delete', $data, $headers);
    }

    /**
     * Short hand version to get JSON via GET request. 
     * Analogy : $.getJSON from jQuery
     *
     * @param  $url
     * @param  array   $data
     * @return mixed
     */
    public function getJSON($url,$data,$headers)
    {

        $response = $this->get($url,$data,$headers);
        $response["response"] = json_encode($response["response"]); // Again decoding it back to JSON

        return $response;
    }
}
