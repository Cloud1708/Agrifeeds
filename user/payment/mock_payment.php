<?php
session_start();
require_once('../../includes/db.php');
$con = new database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get order details from session
$orderDetails = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : null;
if (!$orderDetails) {
    header('Location: ../products.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mock Payment Gateway</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .payment-container {
            max-width: 500px;
            margin: 50px auto;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
        }
        .btn-primary {
            border-radius: 10px;
            padding: 12px;
        }
        .processing-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="card">
            <div class="card-header p-4">
                <h4 class="mb-0">Mock Payment Gateway</h4>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    This is a mock payment gateway for testing purposes.
                </div>

                <div class="mb-4">
                    <h5>Order Summary</h5>
                    <div class="table-responsive">
                        <table class="table">
                            <tbody>
                                <?php foreach ($orderDetails['items'] as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                                    <td class="text-end">₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr>
                                    <td><strong>Total Amount</strong></td>
                                    <td class="text-end"><strong>₱<?php echo number_format($orderDetails['final_total'], 2); ?></strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <form id="payment-form">
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control" id="expiry-date" placeholder="MM/YY" maxlength="5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CVV</label>
                            <input type="text" class="form-control" id="cvv" placeholder="123" maxlength="3" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control" id="cardholder-name" placeholder="John Doe" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            Pay ₱<?php echo number_format($orderDetails['final_total'], 2); ?>
                        </button>
                        <a href="../products.php" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Processing Overlay -->
    <div class="processing-overlay" id="processingOverlay">
        <div class="text-center">
            <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Processing...</span>
            </div>
            <h4>Processing Payment...</h4>
            <p class="text-muted">Please wait while we process your payment.</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Format card number with spaces
        document.getElementById('card-number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = '';
            for(let i = 0; i < value.length; i++) {
                if(i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }
            e.target.value = formattedValue;
        });

        // Format expiry date
        document.getElementById('expiry-date').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if (value.length >= 2) {
                value = value.substring(0,2) + '/' + value.substring(2);
            }
            e.target.value = value;
        });

        // Only allow numbers in CVV
        document.getElementById('cvv').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/gi, '');
        });

        // Handle form submission
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            const cardNumber = document.getElementById('card-number').value.replace(/\s+/g, '');
            const expiryDate = document.getElementById('expiry-date').value;
            const cvv = document.getElementById('cvv').value;
            const cardholderName = document.getElementById('cardholder-name').value;

            if (cardNumber.length !== 16) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Card Number',
                    text: 'Please enter a valid 16-digit card number.',
                    confirmButtonText: 'Close'
                });
                return;
            }

            if (!expiryDate.match(/^\d{2}\/\d{2}$/)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Expiry Date',
                    text: 'Please enter a valid expiry date (MM/YY).',
                    confirmButtonText: 'Close'
                });
                return;
            }

            if (cvv.length !== 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid CVV',
                    text: 'Please enter a valid 3-digit CVV.',
                    confirmButtonText: 'Close'
                });
                return;
            }

            if (!cardholderName.trim()) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Name',
                    text: 'Please enter the cardholder name.',
                    confirmButtonText: 'Close'
                });
                return;
            }
            
            // Show processing overlay
            document.getElementById('processingOverlay').style.display = 'flex';
            
            // Simulate payment processing
            setTimeout(() => {
                // Always return success
                fetch('payment_callback.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'status=success'
                })
                .then(response => response.json())
                .then(data => {
                    // Notify parent window of successful payment
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage('payment_completed', '*');
                    }
                    // Close this window
                    window.close();
                })
                .catch(error => {
                    // Even if there's an error, still return success
                    if (window.opener && !window.opener.closed) {
                        window.opener.postMessage('payment_completed', '*');
                    }
                    window.close();
                });
            }, 2000); // Simulate 2 second processing time
        });

        // Add window unload handler
        window.addEventListener('beforeunload', function() {
            // If the window is being closed without completing payment
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage('payment_failed', '*');
            }
        });
    </script>
</body>
</html> 