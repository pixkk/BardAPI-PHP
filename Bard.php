<?php

namespace Pixkk\PhpBardApi;

class Bard {
    private $proxies;
    private $timeout;
    private $session;
    private $conversation_id;
    private $response_id;
    private $choice_id;
    private $reqid;
    private $SNlM0e;
    private $cfb2h;
    private $og_pid;
    private $exp_id;
    private $rot;
    private $cookies;
    private $initValue;
    private $_BARD_API_KEY_1PSID;
    private $_BARD_API_KEY_1PSIDTS;

    public function __construct($timeout = 6, $proxies = null, $session = null) {
        $fileApi = file_get_contents("API_KEYS.txt");
        $lines = explode("\n", $fileApi);
        $apiKeys = array();

        foreach ($lines as $line) {
            $parts = explode(": ", $line);

            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                $apiKeys[$key] = $value;
            }
        }

        $apiKey1PSID = $apiKeys['_BARD_API_KEY_1PSID'];
        $apiKey1PSIDTS = $apiKeys['_BARD_API_KEY_1PSIDTS'];
        $this->_BARD_API_KEY_1PSID = $apiKey1PSID;
        $this->_BARD_API_KEY_1PSIDTS = $apiKey1PSIDTS;

        $this->proxies = $proxies;
        $this->timeout = $timeout;
        $headers = [
            "Host: bard.google.com",
            "X-Same-Domain: 1",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36",
            "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
            "Origin: https://bard.google.com",
            "Referer: https://bard.google.com/",
        ];
        $this->reqid = rand(pow(10, 3-1), pow(10, 3)-1);
        $this->conversation_id = "";
        $this->response_id = "";
        $this->choice_id = "";

        if ($session === null) {
            $this->session = curl_init();
            if (isset($this->cookies)) {
                $headers[] = 'Cookie: '.$this->cookies;
                curl_setopt($this->session, CURLOPT_COOKIE, $this->cookies);
            }
            else {
                $headers[] = 'Cookie: __Secure-1PSID='.$this->_BARD_API_KEY_1PSID.'; __Secure-1PSIDTS='.$this->_BARD_API_KEY_1PSIDTS;
                curl_setopt($this->session, CURLOPT_COOKIE, "__Secure-1PSID=" . $this->_BARD_API_KEY_1PSID."; __Secure-1PSIDTS=" . $this->_BARD_API_KEY_1PSIDTS);
            }
            curl_setopt($this->session, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
        } else {
            $this->session = $session;
        }

        $this->SNlM0e = $this->_get_snim0e();
    }

    private function _get_snim0e() {
        curl_setopt($this->session, CURLOPT_URL, "https://bard.google.com/");
        curl_setopt($this->session, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->session, CURLOPT_PROXY, $this->proxies);
        curl_setopt($this->session, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($this->session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);
        $resp = curl_exec($this->session);
        if (curl_getinfo($this->session, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception("Response Status: " . curl_getinfo($this->session, CURLINFO_HTTP_CODE));
        }
        preg_match('/"SNlM0e":"(.*?)"/', $resp, $matches);
        preg_match('/"cfb2h":"(.*?)"/', $resp, $matchesCfb2h);
        $this->cfb2h = $matchesCfb2h[1];

        $this->_set_CookieRefreshData($resp);

        return $matches[1];
    }

    private function _set_CookieRefreshData($input) {

        $regex = '/https:\/\/accounts.google.com\/ListAccounts\?authuser=[0-9]+\\\\u0026pid=[0-9]+/i';

        preg_match($regex, $input, $matches, PREG_OFFSET_CAPTURE, 0);

        $parsedUrl = parse_url(str_replace("\u0026", "&", $matches[0][0]));
        $queryString = $parsedUrl['query'];
        parse_str($queryString, $queryParams);
        $og_pid = $queryParams['pid'];
        $this->og_pid = $og_pid;

        $regex = '/https:\/\/accounts.google.com\/RotateCookiesPage"],[0-9]+,[0-9]+,[0-9]+,[0-9]+,[0-9]+/i';
        preg_match($regex, $input, $matches, PREG_OFFSET_CAPTURE, 0);

        $delimiter = '"],';
        $startIndex = strpos($matches[0][0], $delimiter);

        if ($startIndex !== false) {
            $substring = substr($matches[0][0], $startIndex + strlen($delimiter));
            $array = explode(',', $substring);
            $array = array_map('trim', $array);
            $rot = $array[0];
            $this->rot = $rot;
            $exp_id = $array[4];
            $this->exp_id = $exp_id;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/RotateCookiesPage?og_pid='.$this->og_pid.'&rot='.$this->rot.'&origin=https%3A%2F%2Fbard.google.com&exp_id='.$this->exp_id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers = array();
        $headers[] = 'Cookie: __Secure-1PSID='.$this->_BARD_API_KEY_1PSID.'; __Secure-1PSIDTS='.$this->_BARD_API_KEY_1PSIDTS.'; ';
        $headers[] = 'Referer: https://bard.google.com/';
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        $regex = '/init\(\'[0-9-]+\',/i';
        preg_match($regex, $result, $matches, PREG_OFFSET_CAPTURE, 0);
        $initValue = trim($matches[0][0], "init('',");
        $this->initValue = $initValue;
    }
    public function update_1PSIDTS() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.google.com/RotateCookies');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "[".$this->og_pid.",\"".$this->initValue."\"]");
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

        $headers2 = array();
        $headers2[] = 'Content-Type: application/json';
        if (isset($this->cookies)) {
            $headers2[] = 'Cookie: '.$this->cookies;
            curl_setopt($this->session, CURLOPT_COOKIE, $this->cookies);
        }
        else {
        $headers2[] = 'Cookie: __Secure-1PSID='.$this->_BARD_API_KEY_1PSID.'; __Secure-1PSIDTS='.$this->_BARD_API_KEY_1PSIDTS;
            curl_setopt($this->session, CURLOPT_COOKIE, "__Secure-1PSID=" . $this->_BARD_API_KEY_1PSID."; __Secure-1PSIDTS=" . $this->_BARD_API_KEY_1PSIDTS);
        }

        $headers2[] = 'Referer: https://accounts.google.com/RotateCookiesPage?og_pid='.$this->og_pid.'&rot='.$this->rot.'&origin=https%3A%2F%2Fbard.google.com&exp_id='.$this->exp_id;
        $headers2[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers2);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        preg_match_all('/^Set-Cookie:\s*([^;\r\n]*)/mi', $result, $matches);
        $cookiesString = implode('; ', $matches[1]);
        $this->cookies = $cookiesString;
        $keyword = '__Secure-1PSIDTS=';

        $startIndex = strpos($cookiesString, $keyword);

        if ($startIndex !== false) {
            $startIndex += strlen($keyword);
            $endIndex = strpos($cookiesString, ';', $startIndex);
            $value = substr($cookiesString, $startIndex, $endIndex - $startIndex);
            $fileContent = file_get_contents("API_KEYS.txt");
            $fileContent = str_replace('_BARD_API_KEY_1PSIDTS: ' . $this->_BARD_API_KEY_1PSIDTS, '_BARD_API_KEY_1PSIDTS: ' . $value, $fileContent);
            $this->_BARD_API_KEY_1PSIDTS = $value;
            file_put_contents("API_KEYS.txt", $fileContent);
        }

    }

    public function get_answer($input_text) {
        $params = [
            "bl" => $this->cfb2h,
            "_reqid" => (string) $this->reqid,
            "rt" => "c",
        ];
        $input_text_struct = [
            [$input_text],
            null,
            [$this->conversation_id, $this->response_id, $this->choice_id],
        ];
        $data = [
            "f.req" => json_encode([null, json_encode($input_text_struct)]),
            "at" => $this->SNlM0e,
        ];
        curl_setopt($this->session, CURLOPT_URL, "https://bard.google.com/_/BardChatUi/data/assistant.lamda.BardFrontendService/StreamGenerate?" . http_build_query($params));
        curl_setopt($this->session, CURLOPT_POST, true);
        curl_setopt($this->session, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($this->session, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($this->session, CURLOPT_PROXY, $this->proxies);
        curl_setopt($this->session, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($this->session, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->session, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->session, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->session, CURLOPT_RETURNTRANSFER, true);


        $resp = curl_exec($this->session);
        $resp_dict = json_decode(explode("\n", $resp)[3], true)[0][2];
        if ($resp_dict === null) {
            $this->update_1PSIDTS();
            $resp = curl_exec($this->session);
            $resp_dict = json_decode(explode("\n", $resp)[3], true)[0][2];
            if ($resp_dict === null) {
                return ["content" => "Response Error: " . $resp . "."];
            }
        }

        $parsed_answer = json_decode($resp_dict, true);
        $bard_answer = [
            "content" => $parsed_answer[0][0],
            "conversation_id" => $parsed_answer[1][0],
            "response_id" => $parsed_answer[1][1],
            "factualityQueries" => $parsed_answer[3],
            "textQuery" => $parsed_answer[2][0] ?? "",
            "choices" => array_map(function ($i) {
                return ["id" => $i[0], "content" => $i[1]];
            }, $parsed_answer[4]),
        ];
        $this->conversation_id = $bard_answer["conversation_id"];
        $this->response_id = $bard_answer["response_id"];
        $this->choice_id = $bard_answer["choices"][0]["id"];
        $this->reqid += 100000;

        return $bard_answer;
    }
}
