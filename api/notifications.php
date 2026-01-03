<?php
// api/notifications.php
// Lightweight notification helpers (SMS / WhatsApp / Email) - pluggable via environment variables.
require_once __DIR__ . '/config.php';

function notify_admin($subject, $message) {
    // admin notification via email
    if (!empty(getenv('ADMIN_NOTIFY_EMAIL'))) {
        @mail(getenv('ADMIN_NOTIFY_EMAIL'), $subject, $message);
    }
    error_log("ADMIN NOTIFY: $subject - $message");
}

function send_sms_via_twilio($to, $message) {
    $sid = getenv('TWILIO_SID');
    $token = getenv('TWILIO_TOKEN');
    $from = getenv('TWILIO_FROM');
    if (!$sid || !$token || !$from) return false;
    // simple curl call to Twilio REST API
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = http_build_query(['From'=>$from,'To'=>$to,'Body'=>$message]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, $sid . ':' . $token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) { error_log('Twilio error: '.$err); return false; }
    return $res;
}

function send_notifications_for_booking($booking) {
    // booking: associative array with keys name, phone, service, booking_date, time, amount
    $msg = "New booking: {$booking['name']} - {$booking['service']} on {$booking['booking_date']} at {$booking['time']} (phone: {$booking['phone']})";
    // notify admin email
    notify_admin('New Booking', $msg);
    // send SMS to admin if configured
    if (getenv('TWILIO_SID')) {
        $adminPhone = getenv('ADMIN_PHONE');
        if ($adminPhone) send_sms_via_twilio($adminPhone, $msg);
    }
    // Optionally send WhatsApp via Twilio if configured (same API with +whatsapp: prefix)
}

?>
