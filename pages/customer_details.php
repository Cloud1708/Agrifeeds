<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Customer Details</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Customer Details - <?php echo htmlspecialchars($customer['Cust_Name']); ?></h1>
            <div>
                <a href="customers.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Customers
                </a>
                <a href="edit_customer.php?id=<?php echo $customerId; ?>" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Edit Customer
                </a>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Customer ID:</th>
                                <td><?php echo htmlspecialchars($customer['CustomerID']); ?></td>
                            </tr>
                            <tr>
                                <th>Name:</th>
                                <td><?php echo htmlspecialchars($customer['Cust_Name']); ?></td>
                            </tr>
                            <tr>
                                <th>Contact Info:</th>
                                <td><?php echo htmlspecialchars($customer['Cust_CoInfo']); ?></td>
                            </tr>
                            <tr>
                                <th>Current Discount Rate:</th>
                                <td><?php echo number_format($customer['Cust_DiscRate'], 2); ?>%</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Loyalty Program</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 200px;">Membership Tier:</th>
                                <td><?php echo htmlspecialchars($customer['LP_MbspTier'] ?? 'Not Enrolled'); ?></td>
                            </tr>
                            <tr>
                                <th>Points Balance:</th>
                                <td><?php echo number_format($customer['LP_PtsBalance'] ?? 0); ?></td>
                            </tr>
                            <tr>
                                <th>Last Updated:</th>
                                <td><?php echo $customer['LP_LastUpdt'] ? date('M d, Y H:i', strtotime($customer['LP_LastUpdt'])) : 'N/A'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales History -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Sales History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Sale ID</th>
                                <th>Date</th>
                                <th>Method</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            //insert
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Discount History -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Discount History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Discount ID</th>
                                <th>Rate</th>
                                <th>Effective Date</th>
                                <th>Expiration Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($discountHistory as $discount): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($discount['CusDiscountID']); ?></td>
                                <td><?php echo number_format($discount['CDR_DiscountRate'], 2); ?>%</td>
                                <td><?php echo date('M d, Y', strtotime($discount['CDR_EffectiveDate'])); ?></td>
                                <td><?php echo $discount['CDR_ExpirationDate'] ? date('M d, Y', strtotime($discount['CDR_ExpirationDate'])) : 'Present'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 