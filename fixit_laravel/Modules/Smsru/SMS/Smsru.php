<?php
namespace Modules\Smsru\SMS;
use Exception;
class Smsru
{
    public static function getIntent($sendTo, $message)
    {
        $text = is_array($message) ? ($message['message'] ?? implode(' ', $message)) : $message;
        $params = ['api_id' => env('SMSRU_API_KEY'), 'to' => $sendTo, 'msg' => $text, 'json' => 1];
        if ($sender = env('SMSRU_SENDER', '')) $params['from'] = $sender;
        $ch = curl_init('https://sms.ru/sms/send?' . http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch); $err = curl_error($ch); curl_close($ch);
        if ($err) throw new Exception('SMS.ru cURL error: ' . $err, 500);
        $result = json_decode($response, true);
        if (!$result || ($result['status'] ?? '') === 'ERROR') throw new Exception('SMS.ru error code: ' . ($result['status_code'] ?? 0), 500);
        return $result;
    }
}
