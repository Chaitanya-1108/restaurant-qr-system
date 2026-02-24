<?php
require_once __DIR__ . '/config/app.php';

$orderNumber = sanitize($_GET['order_number'] ?? '');
if (!$orderNumber) {
    redirect(BASE_URL . '/');
}

$orderModel = new OrderModel();
$order = $orderModel->getOrderByNumber($orderNumber);

if (!$order) {
    die("Order not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed -
        <?= SITE_NAME ?>
    </title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">

    <?php if (defined('ENABLE_ONLINE_PAYMENT') && ENABLE_ONLINE_PAYMENT): ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <?php endif; ?>
</head>

<body class="confirm-page">

    <div class="confirm-card">
        <?php if ($order['status'] === 'Cancelled'): ?>
            <div class="success-icon" style="background: var(--danger); box-shadow: 0 0 30px rgba(231, 76, 60, 0.4);">
                <i class="fas fa-times"></i>
            </div>
            <h2 style="margin-bottom: 10px; font-size: 1.8rem; color: var(--danger);">Order Cancelled</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">This order has been cancelled by the restaurant.
                Please contact the staff for more details.</p>
        <?php else: ?>
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="margin-bottom: 10px; font-size: 1.8rem;">Order Placed!</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px;">Your order has been sent to the kitchen. Please relax
                while we prepare your meal.</p>
        <?php endif; ?>

        <div class="glass-card" style="padding: 20px; margin-bottom: 25px; text-align: left;">
            <div
                style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid var(--border); padding-bottom: 10px;">
                <span style="color: var(--text-muted);">Order Number</span>
                <span style="font-weight: 700; color: var(--primary-light);">
                    <?= $order['order_number'] ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Status</span>
                <span>
                    <?= getStatusBadge($order['status']) ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 15px;">
                <span style="color: var(--text-muted);">Table</span>
                <span style="font-weight: 600;">Table
                    <?= $order['table_number'] ?>
                </span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-muted);">Payment Method</span>
                <span
                    style="font-weight: 600; color: <?= $order['payment_method'] === 'UPI' ? '#9b59b6' : '#2ecc71' ?>;">
                    <i
                        class="fas <?= $order['payment_method'] === 'UPI' ? 'fa-mobile-alt' : 'fa-money-bill-wave' ?> me-1"></i>
                    <?= $order['payment_method'] ?>
                </span>
            </div>
        </div>

        <?php
        $hidePayment = in_array($order['status'], ['Cancelled', 'Completed']) || $order['payment_status'] === 'Paid';
        if ($order['payment_method'] === 'UPI' && !$hidePayment):
            ?>
            <!-- UPI Payment Section -->
            <div class="upi-payment-section" style="margin-bottom: 25px;">
                <div class="glass-card"
                    style="padding: 24px; text-align: center; border: 1.5px solid var(--primary); background: rgba(232, 82, 26, 0.05);">

                    <?php if (defined('ENABLE_ONLINE_PAYMENT') && ENABLE_ONLINE_PAYMENT): ?>
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #fff; margin-bottom: 10px;">Instant Payment</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px;">Pay securely via UPI,
                                Card, or Netbanking for automatic confirmation.</p>
                            <button id="rzp-button" onclick="startRazorpayPayment()" class="btn-primary-custom"
                                style="width: 100%; background: #3399cc; color: #fff; font-weight: 700; border: none;">
                                <i class="fas fa-shield-alt me-2"></i> Pay Now (Automatic)
                            </button>
                        </div>
                        <div style="margin: 15px 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <div style="flex: 1; height: 1px; background: var(--border);"></div>
                            <span style="font-size: 0.7rem; color: var(--text-muted);">OR SCAN STATIC QR</span>
                            <div style="flex: 1; height: 1px; background: var(--border);"></div>
                        </div>
                    <?php else: ?>
                        <div style="margin-bottom: 20px;">
                            <h4 style="color: #fff; margin-bottom: 10px;">Demo Payment</h4>
                            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 20px;">Simulate a mobile app
                                payment for demonstration purposes.</p>
                            <button id="demo-pay-btn" onclick="startDemoPayment()" class="btn-primary-custom"
                                style="width: 100%; height: 50px; background: linear-gradient(135deg, #00b09b, #96c93d); color: #fff; font-weight: 700; border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 176, 155, 0.3);">
                                <i class="fas fa-mobile-alt me-2"></i> Pay with App (Demo)
                            </button>
                        </div>
                        <div style="margin: 15px 0; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <div style="flex: 1; height: 1px; background: var(--border);"></div>
                            <span style="font-size: 0.7rem; color: var(--text-muted);">OR SCAN STATIC QR</span>
                            <div style="flex: 1; height: 1px; background: var(--border);"></div>
                        </div>
                    <?php endif; ?>

                    <div
                        style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 15px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">
                        <i class="fas fa-qrcode me-1"></i> Scan to Pay
                    </div>

                    <?php
                    $upiId = UPI_ID;
                    $amount = $order['total_amount'];
                    $name = urlencode(UPI_NAME);
                    $orderNum = $order['order_number'];
                    // Generate UPI Intent Link
                    $upiUrl = "upi://pay?pa={$upiId}&pn={$name}&am={$amount}&cu=INR&tn=Order-{$orderNum}";
                    $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&margin=10&data=" . urlencode($upiUrl);
                    ?>

                    <div class="qr-container"
                        style="background: #fff; padding: 12px; border-radius: 16px; display: inline-block; margin-bottom: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.4);">
                        <img src="<?= $qrUrl ?>" alt="UPI QR Code" style="width: 160px; height: 160px; display: block;">
                    </div>

                    <div style="margin-bottom: 15px;">
                        <div style="font-size: 1.3rem; font-weight: 800; color: #fff;">
                            <?= formatPrice($amount) ?>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            Paying to: <span style="color: var(--primary-light); font-weight: 600;"><?= UPI_NAME ?></span>
                        </div>
                    </div>

                    <a href="<?= $upiUrl ?>" class="btn-primary-custom"
                        style="display: block; margin: 0 auto; width: 80%; padding: 12px; font-size: 0.9rem; text-decoration: none; background: #fff; color: #000; font-weight: 700;">
                        <i class="fas fa-mobile-alt me-2"></i> Pay with UPI App
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <div style="text-align: left; margin-bottom: 30px;">
            <h4 style="font-size: 1rem; margin-bottom: 15px; font-family: 'Poppins', sans-serif;">Order Summary</h4>
            <?php foreach ($order['items'] as $item): ?>
                <div style="margin-bottom: 12px; border-bottom: 1px dashed rgba(255,255,255,0.05); padding-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                        <span><strong style="color: var(--primary-light);">
                                <?= $item['quantity'] ?>x
                            </strong>
                            <?= $item['item_name'] ?>
                        </span>
                        <span style="color: var(--text-muted);">
                            <?= formatPrice($item['subtotal']) ?>
                        </span>
                    </div>
                    <?php if ($item['notes']): ?>
                        <div
                            style="font-size: 0.75rem; color: var(--accent); margin-top: 4px; border-left: 2px solid var(--accent); padding-left: 8px; font-style: italic;">
                            "<?= $item['notes'] ?>"
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div
                style="margin-top: 15px; padding-top: 15px; border-top: 1px solid var(--border); display: flex; justify-content: space-between; font-weight: 700; font-size: 1.1rem;">
                <span>Total Amount</span>
                <span style="color: var(--accent);">
                    <?= formatPrice($order['total_amount']) ?>
                </span>
            </div>
        </div>

        <?php if ($order['status'] === 'Cancelled'): ?>
            <a href="<?= BASE_URL ?>/?table=<?= $order['table_number'] ?>" class="btn-primary-custom"
                style="width: 100%; text-decoration: none; display: block; padding: 15px; background: var(--border);">
                <i class="fas fa-arrow-left me-2"></i> Back to Menu
            </a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/?table=<?= $order['table_number'] ?>" class="btn-primary-custom"
                style="width: 100%; text-decoration: none; display: block; padding: 15px;">
                <i class="fas fa-plus me-2"></i> Order More Items
            </a>

            <?php if ($order['table_number']): ?>
                <button class="btn-primary-custom"
                    style="width: 100%; margin-top: 15px; background: var(--bg-card); border: 1px solid var(--border); color: #fff;"
                    onclick="window.location.href='<?= BASE_URL ?>/?table=<?= $order['table_number'] ?>&call_waiter=1'">
                    <i class="fas fa-bell me-2" style="color: var(--accent);"></i> Needs Assistance?
                </button>
            <?php endif; ?>
        <?php endif; ?>

        <p id="status-update-info" style="margin-top: 25px; font-size: 0.8rem; color: var(--text-muted);">
            <i class="fas fa-sync-alt fa-spin me-1"></i> Checking for status updates...
        </p>

        <!-- Feedback Section -->
        <div id="feedback-section"
            style="margin-top: 40px; padding-top: 30px; border-top: 1px solid var(--border); display: block;">
            <h4 style="margin-bottom: 20px; font-family: 'Playfair Display', serif;">Enjoyed your meal?</h4>
            <div id="feedback-form">
                <div class="rating-stars"
                    style="margin-bottom: 20px; display: flex; justify-content: center; gap: 10px; font-size: 2rem; color: var(--text-muted);">
                    <i class="far fa-star" data-rating="1" onclick="setRating(1)"></i>
                    <i class="far fa-star" data-rating="2" onclick="setRating(2)"></i>
                    <i class="far fa-star" data-rating="3" onclick="setRating(3)"></i>
                    <i class="far fa-star" data-rating="4" onclick="setRating(4)"></i>
                    <i class="far fa-star" data-rating="5" onclick="setRating(5)"></i>
                </div>
                <input type="hidden" id="selected-rating" value="0">
                <textarea id="feedback-comment" class="search-wrapper"
                    style="width: 100%; height: 80px; margin-bottom: 15px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 12px;"
                    placeholder="Leave a comment (optional)..."></textarea>
                <button onclick="submitFeedback()" class="btn-primary-custom" style="width: 100%;"><i
                        class="fas fa-paper-plane me-2"></i> Submit Feedback</button>
            </div>
            <div id="feedback-thanks" style="display: none; text-align: center; padding: 20px;">
                <i class="fas fa-heart" style="color: var(--primary); font-size: 2rem; margin-bottom: 15px;"></i>
                <p>Thank you for your feedback!</p>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="paymentSuccessModal" class="modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.9); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
        <div class="glass-card"
            style="max-width: 350px; width: 90%; text-align: center; padding: 40px 20px; border: 2px solid #2ecc71; border-radius: 30px; animation: modalPop 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position: relative;">
            <div
                style="width: 80px; height: 80px; background: #2ecc71; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 25px; box-shadow: 0 0 30px rgba(46, 204, 113, 0.4);">
                <i class="fas fa-check"></i>
            </div>
            <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 10px; color: #fff;">Payment Received!</h2>
            <p style="color: var(--text-muted); margin-bottom: 30px; font-size: 0.95rem;">Order #
                <?= $order['order_number'] ?> is now being prepared in the kitchen.
            </p>
            <button onclick="location.reload()" class="btn-primary-custom"
                style="width: 100%; background: #2ecc71; border: none; padding: 15px; color: #fff; font-weight: 600; cursor: pointer; border-radius: 12px;">Awesome!</button>
        </div>
    </div>

    <style>
        @keyframes modalPop {
            from {
                transform: scale(0.8);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>

    <script>
        const orderId = <?= $order['id'] ?>;
        const currentStatus = '<?= $order['status'] ?>';
        const statusElement = document.querySelector('.glass-card div:nth-child(2) span:last-child');
        const updateText = document.getElementById('status-update-info');

        async function checkStatus() {
            try {
                const response = await fetch(`<?= BASE_URL ?>/api/get_orders.php?order_id=${orderId}`);
                const data = await response.json();

                if (data.success) {
                    const newStatus = data.order.status;
                    const tableStatus = data.order.table_status;

                    // If table is freed by admin, redirect to menu
                    if (tableStatus === 'available') {
                        location.href = `<?= BASE_URL ?>/?table=<?= $order['table_number'] ?>`;
                        return;
                    }

                    if (newStatus !== currentStatus) {
                        if (newStatus === 'Completed' || newStatus === 'Cancelled') {
                            location.reload();
                        } else {
                            location.reload();
                        }
                    }
                }
            } catch (error) {
                console.error('Error polling status:', error);
            }
        }

        // Feedback Logic
        function setRating(r) {
            document.getElementById('selected-rating').value = r;
            const stars = document.querySelectorAll('.rating-stars i');
            stars.forEach((s, idx) => {
                if (idx < r) {
                    s.classList.remove('far');
                    s.classList.add('fas');
                    s.style.color = '#e8521a';
                } else {
                    s.classList.remove('fas');
                    s.classList.add('far');
                    s.style.color = 'var(--text-muted)';
                }
            });
        }

        async function submitFeedback() {
            const rating = document.getElementById('selected-rating').value;
            const comment = document.getElementById('feedback-comment').value;

            if (rating == 0) {
                alert('Please select a rating first');
                return;
            }

            try {
                const response = await fetch('<?= BASE_URL ?>/api/submit_feedback.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_id: orderId,
                        customer_name: '<?= $order['customer_name'] ?>',
                        rating: rating,
                        comment: comment
                    })
                });
                const result = await response.json();
                if (result.success) {
                    document.getElementById('feedback-form').style.display = 'none';
                    document.getElementById('feedback-thanks').style.display = 'block';
                }
            } catch (e) {
                alert('Failed to submit feedback');
            }
        }

        // Demo Payment Flow
        async function startDemoPayment() {
            const btn = document.getElementById('demo-pay-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Processing...';

            // Brief delay to simulate app loading
            await new Promise(resolve => setTimeout(resolve, 1500));

            try {
                const response = await fetch('<?= BASE_URL ?>/api/demo_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_number: '<?= $order['order_number'] ?>' })
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('paymentSuccessModal').style.display = 'flex';
                    // Show feedback section immediately for the demo
                    document.getElementById('feedback-section').style.display = 'block';
                } else {
                    alert('❌ ' + data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-mobile-alt me-2"></i> Pay with App (Demo)';
                }
            } catch (e) {
                alert('Connection error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-mobile-alt me-2"></i> Pay with App (Demo)';
            }
        }

        // Razorpay Payment Flow
        async function startRazorpayPayment() {
            const btn = document.getElementById('rzp-button');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Initializing...';

            try {
                const response = await fetch('<?= BASE_URL ?>/api/create_razorpay_order.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ order_number: '<?= $order['order_number'] ?>' })
                });
                const data = await response.json();

                if (data.success) {
                    var options = {
                        "key": data.key_id,
                        "amount": data.amount,
                        "currency": data.currency,
                        "name": "<?= SITE_NAME ?>",
                        "description": "Payment for Order #<?= $order['order_number'] ?>",
                        "order_id": data.razorpay_order_id,
                        "handler": function (response) {
                            verifyRazorpayPayment(response);
                        },
                        "prefill": {
                            "name": data.customer.name,
                            "email": data.customer.email,
                            "contact": data.customer.contact
                        },
                        "theme": { "color": "#e8521a" },
                        "modal": {
                            "ondismiss": function () {
                                btn.disabled = false;
                                btn.innerHTML = '<i class="fas fa-shield-alt me-2"></i> Pay Now (Automatic)';
                            }
                        }
                    };
                    var rzp = new Razorpay(options);
                    rzp.open();
                } else {
                    alert(data.message || 'Error creating payment');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-shield-alt me-2"></i> Pay Now (Automatic)';
                }
            } catch (e) {
                alert('Connection error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-shield-alt me-2"></i> Pay Now (Automatic)';
            }
        }

        async function verifyRazorpayPayment(response) {
            const updateText = document.getElementById('status-update-info');
            updateText.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying payment...';

            try {
                const res = await fetch('<?= BASE_URL ?>/api/verify_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        order_number: '<?= $order['order_number'] ?>',
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature
                    })
                });
                const result = await res.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Signature verification failed');
                    location.reload();
                }
            } catch (e) {
                alert('Error updating payment status');
                location.reload();
            }
        }

        // Initial check for feedback section
        // Always visible now as per user request
        document.getElementById('feedback-section').style.display = 'block';

        // Poll every 5 seconds
        setInterval(checkStatus, 5000);
    </script>

</body>

</html>