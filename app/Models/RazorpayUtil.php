<?php

class RazorpayUtil
{
    /**
     * Create a Razorpay Order using cURL
     */
    public static function createOrder($amountInPaise, $receiptId)
    {
        $data = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $receiptId,
            'payment_capture' => 1 // Auto capture
        ];

        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ":" . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => $error];
        }

        return json_decode($response, true);
    }

    /**
     * Verify the Razorpay Payment Signature
     */
    public static function verifySignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)
    {
        $generatedSignature = hash_hmac('sha256', $razorpayOrderId . "|" . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
        return hash_equals($generatedSignature, $razorpaySignature);
    }
}
