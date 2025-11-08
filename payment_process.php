<?php
// payment_process.php
require 'config.php';
if(!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['user']['id'];

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!verify_csrf($_POST['csrf'] ?? '')) {
        $_SESSION['payment_error'] = 'Invalid CSRF token';
        header('Location: payment.php');
        exit;
    }
    
    // Get payment details
    $amount = floatval($_POST['amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'razorpay';
    $transaction_id = $_POST['transaction_id'] ?? ('TXN_' . time() . '_' . $uid);
    $razorpay_payment_id = $_POST['razorpay_payment_id'] ?? null;
    $razorpay_order_id = $_POST['razorpay_order_id'] ?? null;
    $razorpay_signature = $_POST['razorpay_signature'] ?? null;
    
    if($amount <= 0) {
        $_SESSION['payment_error'] = 'Invalid payment amount';
        header('Location: payment.php');
        exit;
    }
    
    try {
        // Start transaction
        $mysqli->begin_transaction();
        
        // 1. Create payment record
        $stmt = $mysqli->prepare('
            INSERT INTO payments (
                user_id, amount, payment_type, payment_method, 
                payment_status, transaction_id, razorpay_payment_id,
                razorpay_order_id, razorpay_signature
            ) VALUES (?, ?, "dues", ?, "completed", ?, ?, ?, ?)
        ');
        $stmt->bind_param('idsssss', $uid, $amount, $payment_method, $transaction_id, 
                         $razorpay_payment_id, $razorpay_order_id, $razorpay_signature);
        
        if(!$stmt->execute()) {
            throw new Exception('Failed to create payment record: ' . $stmt->error);
        }
        
        $payment_id = $mysqli->insert_id;
        
        // 2. Update user dues (subtract paid amount)
        $update_dues = $mysqli->prepare('UPDATE users SET dues = GREATEST(0, dues - ?) WHERE id = ?');
        $update_dues->bind_param('di', $amount, $uid);
        
        if(!$update_dues->execute()) {
            throw new Exception('Failed to update dues: ' . $update_dues->error);
        }
        
        // 3. If payment covers collection fees, update collection requests
        if($amount >= 50) {
            // Mark one collection request as paid for every ₹50 paid
            $collections_to_update = floor($amount / 50);
            if($collections_to_update > 0) {
                $update_collections = $mysqli->prepare('
                    UPDATE collection_requests 
                    SET payment_status = "paid" 
                    WHERE user_id = ? AND payment_status = "pending" AND status != "cancelled"
                    ORDER BY created_at ASC 
                    LIMIT ?
                ');
                $update_collections->bind_param('ii', $uid, $collections_to_update);
                $update_collections->execute();
            }
        }
        
        // Commit transaction
        $mysqli->commit();
        
        // Set success message
        $_SESSION['payment_success'] = [
            'amount' => $amount,
            'transaction_id' => $transaction_id,
            'payment_type' => 'dues'
        ];
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $mysqli->rollback();
        $_SESSION['payment_error'] = 'Payment failed: ' . $e->getMessage();
        header('Location: payment.php');
        exit;
    }
}

header('Location: user_dashboard.php');
exit;