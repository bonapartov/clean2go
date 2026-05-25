<?php
namespace Modules\Smsc\SMS;
use Exception;
class Smsc
{
    public static function getIntent($sendTo, $message)
    {
        $text = is_array($message) ? ($message['message'] ?? implode(' ', $message)) : $message;
        $params = ['login' => env('SMSC_LOGIN'), 'psw' => md5(env('SMSC_PASSWORD')), 'phones' => $sendTo, 'mes' => $text, 'fmt' => 3, 'charset' => 'utf-8'];
        if ($sender = env('SMSC_SENDER', '')) $params['sender'] = $sender;
        $ch = curl_init('https://smsc.ru/sys/send.php?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
        if ($err) throw new Exception('SMSC.ru cURL error: ' . $err, 500);
        $result = json_decode($response, true);
        if (!$result || isset($result['error'])) throw new Exception('SMSC.ru error ' . ($result['error_code'] ?? 0) . ': ' . ($result['error'] ?? 'unknown'), 500);
        return $result;
    }
}
