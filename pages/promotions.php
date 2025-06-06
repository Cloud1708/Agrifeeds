<?php
session_start();
require_once('../includes/db.php');
$con = new database();
$sweetAlertConfig = "";

// SweetAlert config from session
if (isset($_SESSION['sweetAlertConfig'])) {
    $sweetAlertConfig = $_SESSION['sweetAlertConfig'];
    unset($_SESSION['sweetAlertConfig']);
}

// Handle Add Promotion
if (isset($_POST['AddPromotion'])) {
    $code = $_POST['Prom_Code'];
    $desc = $_POST['Promo_Description'];
    $amount = $_POST['Promo_DiscAmnt'];
    $type = $_POST['Promo_DiscountType'];
    $start = $_POST['Promo_StartDate'];
    $end = $_POST['Promo_EndDate'];
    $limit = $_POST['UsageLimit'];
    $isActive = $_POST['Promo_IsActive'];

    $PromID = $con->addPromotion($code, $desc, $amount, $type, $start, $end, $limit, $isActive);

    if ($PromID) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'success',
                title: 'Promotion Added',
                text: 'A new promotion has been added!',
                confirmButtonText: 'Continue'
            });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Something went wrong',
                text: 'Please try again.'
            });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all promotions
$allPromotions = $con->viewPromotions();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Promotions</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php if (!empty($sweetAlertConfig)) echo $sweetAlertConfig; ?>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Promotions</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPromotionModal">
                <i class="bi bi-plus-lg"></i> New Promotion
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="promotionSearch" 
                           placeholder="Search promotions..." aria-label="Search promotions">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="typeFilter">
                    <option value="all">All Types</option>
                    <option value="Discount">Discount</option>
                    <option value="BOGO">Buy One Get One</option>
                    <option value="Bundle">Bundle Deal</option>
                    <option value="Clearance">Clearance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Expired">Expired</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Promotions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Promotion ID</th>
                        <th>Promotion Code</th>
                        <th>Description</th>
                        <th>Discount</th>
                        <th>Discount Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Usage Limit</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="promotionsTableBody">
                    <?php if (!empty($allPromotions)): ?>
                    <?php foreach ($allPromotions as $promo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($promo['PromotionID']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Prom_Code']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_Description']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_DiscAmnt']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_DiscountType']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_StartDate']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_EndDate']); ?></td>
                        <td><?php echo htmlspecialchars($promo['UsageLimit']); ?></td>
                        <td>
                            <?php
$now = strtotime(date('Y-m-d H:i:s'));
$start = strtotime($promo['Promo_StartDate']);
$end = strtotime($promo['Promo_EndDate']);
$isActive = $promo['Promo_IsActive'];

if ($isActive) {
    if ($now < $start) {
        $status = 'Scheduled';
        $badge = 'bg-info text-dark';
    } elseif ($now > $end) {
        // Set to inactive in the database if expired
        $status = 'Expired';
        $badge = 'bg-danger';
        // Update Promo_IsActive to 0 if not already
        if ($promo['Promo_IsActive'] != 0) {
            $con->opencon()->prepare("UPDATE promotions SET Promo_IsActive=0 WHERE PromotionID=?")->execute([$promo['PromotionID']]);
            $isActive = 0; // update local variable too
        }
    } else {
        $status = 'Active';
        $badge = 'bg-success';
    }
} else {
    $status = 'Inactive';
    $badge = 'bg-secondary';
}
echo "<span class='badge $badge'>$status</span>";
?>
                        </td>
                        <td>
                            <!-- View Description Modal Trigger -->
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewDescModal<?php echo $promo['PromotionID']; ?>">
                                <i class="bi bi-eye"></i> View
                            </button>
                            <!-- View Description Modal -->
                            <div class="modal fade" id="viewDescModal<?php echo $promo['PromotionID']; ?>" tabindex="-1" aria-labelledby="viewDescLabel<?php echo $promo['PromotionID']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="viewDescLabel<?php echo $promo['PromotionID']; ?>">Promotion Description</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php echo nl2br(htmlspecialchars($promo['Promo_Description'])); ?>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="10" class="text-center">No promotions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Promotion Modal -->
    <div class="modal fade" id="addPromotionModal" tabindex="-1" aria-labelledby="addPromotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addPromotionForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addPromotionModalLabel">New Promotion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="promCode" class="form-label">Promotion Code</label>
                            <input type="text" class="form-control" id="promCode" name="Prom_Code" required>
                        </div>
                        <div class="mb-3">
                            <label for="promoDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="promoDescription" name="Promo_Description" rows="2" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="promoDiscAmnt" class="form-label">Discount Amount</label>
                                <input type="number" class="form-control" id="promoDiscAmnt" name="Promo_DiscAmnt" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="promoDiscountType" class="form-label">Discount Type</label>
                                <select class="form-select" id="promoDiscountType" name="Promo_DiscountType" required>
                                    <option value="Percentage">Percentage</option>
                                    <option value="Fixed">Fixed Amount</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="promoStartDate" class="form-label">Start Date & Time</label>
                                <input type="datetime-local" class="form-control" id="promoStartDate" name="Promo_StartDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="promoEndDate" class="form-label">End Date & Time</label>
                                <input type="datetime-local" class="form-control" id="promoEndDate" name="Promo_EndDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="usageLimit" class="form-label">Usage Limit</label>
                            <input type="number" class="form-control" id="usageLimit" name="UsageLimit" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="promoIsActive" class="form-label">Status</label>
                            <select class="form-select" id="promoIsActive" name="Promo_IsActive" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="AddPromotion" class="btn btn-primary">Save Promotion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>