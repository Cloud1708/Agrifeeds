<?php
require_once('../includes/db.php');
require_once __DIR__ . '/../includes/session.php';
$con = new database();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) { // 2 for customer
    header('Location: ../index.php');
    exit();
}
// Get user information
$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);
$customerInfo = $con->getCustomerInfo($userID);

// Update member tier before displaying
if (isset($customerInfo['CustomerID'])) {
    $con->updateMemberTier($customerInfo['CustomerID']);
    // Refresh customer info to get updated tier
    $customerInfo = $con->getCustomerInfo($userID);
}

// Calculate points needed for next tier
$currentPoints = $customerInfo['LP_PtsBalance'];
$nextTier = '';
$pointsNeeded = 0;

// Get tier thresholds from settings
$settings = $con->opencon()->query("SELECT bronze, silver, gold FROM loyalty_settings WHERE LSID = 1")->fetch(PDO::FETCH_ASSOC);
$bronze = (int)$settings['bronze'];
$silver = (int)$settings['silver'];
$gold = (int)$settings['gold'];

if ($currentPoints < $bronze) {
    $nextTier = 'Bronze';
    $pointsNeeded = $bronze - $currentPoints;
} elseif ($currentPoints < $silver) {
    $nextTier = 'Silver';
    $pointsNeeded = $silver - $currentPoints;
} elseif ($currentPoints < $gold) {
    $nextTier = 'Gold';
    $pointsNeeded = $gold - $currentPoints;
} else {
    $nextTier = 'Platinum';
    $pointsNeeded = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - My Perks</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
     <link href="../css/background.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">AgriFeeds</a>
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box me-2"></i> View Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-cart me-2"></i> My Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="perks.php">
                        <i class="bi bi-gift me-2"></i> My Perks
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Perks</h1>
        </div>

        <!-- Loyalty Status Card -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <h5 class="card-title">Loyalty Status</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <i class="bi bi-trophy-fill" style="font-size: 2rem; color: #ffd700;"></i>
                            </div>
                            <div>
                                <h3 class="mb-0"><?php echo htmlspecialchars($customerInfo['Cust_LoStat']); ?></h3>
                                <p class="text-muted mb-0">Current Tier</p>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 10px;">
                            <?php
                            $progress = 0;
                            if ($currentPoints >= 15000) {
                                $progress = 100;
                            } elseif ($currentPoints >= 10000) {
                                $progress = ($currentPoints - 10000) / 5000 * 100;
                            } elseif ($currentPoints >= 5000) {
                                $progress = ($currentPoints - 5000) / 5000 * 100;
                            } else {
                                $progress = $currentPoints / 5000 * 100;
                            }
                            ?>
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <p class="text-muted">
                            <?php if ($pointsNeeded > 0): ?>
                                <?php echo number_format($pointsNeeded); ?> points needed for <?php echo $nextTier; ?> tier
                            <?php else: ?>
                                You've reached the highest tier!
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Available Points</h6>
                                        <h3 class="mb-0"><?php echo number_format($currentPoints); ?></h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Discount Rate</h6>
                                        <h3 class="mb-0"><?php echo number_format($customerInfo['Cust_DiscRate'], 1); ?>%</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benefits Grid -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-percent text-primary me-2"></i>
                            Discount Benefits
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?php echo number_format($customerInfo['Cust_DiscRate'], 1); ?>% off on all purchases
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Special member-only discounts
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Early access to sales
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-gift text-primary me-2"></i>
                            Rewards Program
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Earn points on every purchase
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Redeem points for discounts
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Birthday rewards
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="bi bi-star text-primary me-2"></i>
                            Exclusive Benefits
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Priority customer support
                            </li>
                            <li class="mb-2">
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Free delivery on orders
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill text-success me-2"></i>
                                Exclusive product previews
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Points History -->
        <div class="card">
    <div class="card-body">
        <h5 class="card-title">Points History</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Transaction</th>
                        <th>Points</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
<?php
$pointsHistory = $con->getPointsHistoryByCustomer($customerInfo['CustomerID']);
if ($pointsHistory && count($pointsHistory) > 0):
    // Start from current balance
    $runningBalance = (int)$customerInfo['LP_PtsBalance'];
    foreach ($pointsHistory as $row):
?>
<tr>
    <td><?= htmlspecialchars($row['Date']) ?></td>
    <td><?= htmlspecialchars($row['Transaction']) ?></td>
    <td>
        <?= $row['PointsEarned'] > 0 ? '+' . htmlspecialchars($row['PointsEarned']) : '' ?>
        <?= $row['PointsRedeemed'] > 0 ? '-' . htmlspecialchars($row['PointsRedeemed']) : '' ?>
    </td>
    <td><?= $runningBalance ?></td>
</tr>
<?php
    // Subtract earned, add redeemed as you go UP the history
    $runningBalance -= (int)$row['PointsEarned'];
    $runningBalance += (int)$row['PointsRedeemed'];
    endforeach;
else:
?>
<tr>
    <td colspan="4" class="text-center text-muted">No points history found.</td>
</tr>
<?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Add any custom JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize any components or load data
        });
    </script>
</body>
</html> 