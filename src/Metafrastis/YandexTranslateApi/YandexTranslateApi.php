<?php

namespace Metafrastis\YandexTranslateApi;

class YandexTranslateApi {

    public $queue = [];
    public $response;
    public $responses = [];

    public function translate($args = [], $opts = []) {
        $args['base'] = isset($args['base']) ? $args['base'] : 'https://translate.yandex.net/api';
        $args['version'] = isset($args['version']) ? $args['version'] : '1.5';
        $args['key'] = isset($args['key']) ? $args['key'] : null;
        $args['from'] = isset($args['from']) ? $args['from'] : null;
        $args['to'] = isset($args['to']) ? $args['to'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        $args['format'] = isset($args['format']) ? $args['format'] : 'plain';
        $args['options'] = isset($args['options']) ? $args['options'] : null;
        $args['callback'] = isset($args['callback']) ? $args['callback'] : null;
        if (!$args['base']) {
            return false;
        }
        if (!$args['version']) {
            return false;
        }
        if (!$args['key']) {
            return false;
        }
        if (!$args['from']) {
            return false;
        }
        if (!$args['to']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        $url = $args['base'].'/v'.$args['version'].'/tr.json/translate';
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0',
        ];
        $params = ['key' => $args['key'],'text' => $args['text'], 'lang' => $args['from'].'-'.$args['to'], 'format' => $args['format']];
        if (!empty($args['options'])) {
            $params['options'] = $args['options'];
        }
        if (!empty($args['callback'])) {
            $params['callback'] = $args['callback'];
        }
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang']) || !isset($json['text'])) {
            return false;
        }
        return is_array($json['text']) && isset($json['text'][0]) ? $json['text'][0] : $json['text'];
    }

    public function detect($args = [], $opts = []) {
        $args['base'] = isset($args['base']) ? $args['base'] : 'https://translate.yandex.net/api';
        $args['version'] = isset($args['version']) ? $args['version'] : '1.5';
        $args['key'] = isset($args['key']) ? $args['key'] : null;
        $args['text'] = isset($args['text']) ? $args['text'] : null;
        $args['hint'] = isset($args['hint']) ? $args['hint'] : null;
        $args['callback'] = isset($args['callback']) ? $args['callback'] : null;
        if (!$args['base']) {
            return false;
        }
        if (!$args['version']) {
            return false;
        }
        if (!$args['key']) {
            return false;
        }
        if (!$args['text']) {
            return false;
        }
        $url = $args['base'].'/v'.$args['version'].'/tr.json/detect';
        $headers = [
            'Accept: '.'*'.'/'.'*',
            'Content-Type: application/x-www-form-urlencoded',
            'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:71.0) Gecko/20100101 Firefox/71.0',
        ];
        $params = ['key' => $args['key'],'text' => $args['text']];
        if (!empty($args['hint'])) {
            $params['hint'] = $args['hint'];
        }
        if (!empty($args['callback'])) {
            $params['callback'] = $args['callback'];
        }
        $options = $opts;
        $queue = isset($args['queue']) ? $args['queue'] : false;
        $response = $this->post($url, $headers, $params, $options, $queue);
        if (!$queue) {
            $this->response = $response;
        }
        if ($queue) {
            return;
        }
        $json = json_decode($response['body'], true);
        if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang'])) {
            return false;
        }
        return is_array($json['lang']) && isset($json['lang'][0]) ? $json['lang'][0] : $json['lang'];
    }

    public function post($url, $headers = [], $params = [], $options = [], $queue = false) {
        $opts = [];
        $opts[CURLINFO_HEADER_OUT] = true;
        $opts[CURLOPT_CONNECTTIMEOUT] = 5;
        $opts[CURLOPT_ENCODING] = '';
        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_HEADER] = true;
        $opts[CURLOPT_HTTPHEADER] = $headers;
        $opts[CURLOPT_POST] = true;
        $opts[CURLOPT_POSTFIELDS] = is_array($params) || is_object($params) ? http_build_query($params) : $params;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_SSL_VERIFYHOST] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_TIMEOUT] = 10;
        $opts[CURLOPT_URL] = $url;
        foreach ($opts as $key => $value) {
            if (!array_key_exists($key, $options)) {
                $options[$key] = $value;
            }
        }
        if ($queue) {
            $this->queue[] = ['options' => $options];
            return;
        }
        $follow = false;
        if ($options[CURLOPT_FOLLOWLOCATION]) {
            $follow = true;
            $options[CURLOPT_FOLLOWLOCATION] = false;
        }
        $errors = 2;
        $redirects = isset($options[CURLOPT_MAXREDIRS]) ? $options[CURLOPT_MAXREDIRS] : 5;
        while (true) {
            $ch = curl_init();
            curl_setopt_array($ch, $options);
            $body = curl_exec($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            if ($error || $errno) {
                if ($errors > 0) {
                    $errors--;
                    continue;
                }
            } elseif ($info['redirect_url'] && $follow) {
                if ($redirects > 0) {
                    $redirects--;
                    $options[CURLOPT_URL] = $info['redirect_url'];
                    continue;
                }
            }
            break;
        }
        return $response;
    }

    public function multi($args = []) {
        if (!$this->queue) {
            return [];
        }
        $mh = curl_multi_init();
        $chs = [];
        foreach ($this->queue as $key => $request) {
            $ch = curl_init();
            $chs[$key] = $ch;
            curl_setopt_array($ch, $request['options']);
            curl_multi_add_handle($mh, $ch);
        }
        $running = 1;
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        $responses = [];
        foreach ($chs as $key => $ch) {
            curl_multi_remove_handle($mh, $ch);
            $body = curl_multi_getcontent($ch);
            $info = curl_getinfo($ch);
            $head = substr($body, 0, $info['header_size']);
            $body = substr($body, $info['header_size']);
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            $response = [
                'info' => $info,
                'head' => $head,
                'body' => $body,
                'error' => $error,
                'errno' => $errno,
            ];
            $this->responses[$key] = $response;
            $options = $this->queue[$key]['options'];
            if (strpos($options[CURLOPT_URL], 'tr.json/translate') !== false) {
                $json = json_decode($body, true);
                if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang']) || !isset($json['text'])) {
                    $responses[$key] = false;
                    continue;
                }
                $responses[$key] = is_array($json['text']) && isset($json['text'][0]) ? $json['text'][0] : $json['text'];
            } elseif (strpos($options[CURLOPT_URL], 'tr.json/detect') !== false) {
                $json = json_decode($body, true);
                if (!$json || !isset($json['code']) || $json['code'] !== 200 || !isset($json['lang'])) {
                    $responses[$key] = false;
                    continue;
                }
                $responses[$key] = is_array($json['lang']) && isset($json['lang'][0]) ? $json['lang'][0] : $json['lang'];
            } else {
                $responses[$key] = $body;
            }
        }
        curl_multi_close($mh);
        $this->queue = [];
        return $responses;
    }

}
