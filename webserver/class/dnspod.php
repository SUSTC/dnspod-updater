<?php

/**
 *      (C)2011-2099 SUSTC-IT
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: class/dnspod.php 0 2014-09-06 tengattack $
 */

if(!defined('IN_SUSTC')) {
  exit('Access Denied');
}



class dnspod {
  public function message($status, $code, $message='') {
    $json = array();

    switch ($status) {
      case 'error':
      case 'danger':
        $json['err'] = array(
          'code' => $code,
          'message' => $message
        );
        break;
      case 'success':
        $json['err'] = array(
          'code' => 0
        );
        break;
      default:
        $json['err'] = array(
          'code' => -1
        );
        break;
    }

    return $json;
  }

  public function call_api($api, $data) {
    if ($api == '' || !is_array($data)) {
      return $this->message('danger', 2, '内部错误：参数错误');
    }

    $api = 'https://dnsapi.cn/' . $api;
    $data = array_merge($data, array(
        'login_email' => DNSPOD_EMAIL,
        'login_password' => DNSPOD_PASSWORD,
        /* 'login_code' => $_SESSION['login_code'], */
        'format' => 'json', 'lang' => 'cn', 'error_on_empty' => 'no'));

    $result = $this->post_data($api, $data);  //, $_SESSION['cookies']
    if (!$result) {
      return $this->message('danger', 3, '内部错误：调用失败');
    }

    $result = explode("\r\n\r\n", $result);
    /*if (preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result[0], $cookies)) {
      $_SESSION['login_code'] = '';
      foreach ($cookies[1] as $key => $value) {
        if (substr($value, 0, 1) == 't') {
          $_SESSION['cookies'] = $value;
        }
      }
    }*/

    $results = @json_decode($result[1], 1);
    if (!is_array($results)) {
      return $this->message('danger', '内部错误：返回异常', '');
    }
    
    if ($results['status']['code'] != 1 && $results['status']['code'] != 50) {
      return $this->message('danger', 5, $results['status']['message']);
    } else {
      return $this->message('success', 0);
    }
  }

  private function post_data($url, $data, $cookie='') {
    if ($url == '' || !is_array($data)) {
      $this->message('danger', 2, '内部错误：参数错误');
    }

    $ch = @curl_init();
    if (!$ch) {
      $this->message('danger', 1, '内部错误：服务器不支持CURL');
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_USERAGENT, 'DNSPod SUSTC Proxy/1.0.0 (tengattack@foxmail.com)');
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
  }
}

?>