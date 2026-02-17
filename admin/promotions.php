<?php
 
// Set timezone to ensure correct date comparisons
date_default_timezone_set('Asia/Manila');
 
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/validation.php';
$con = new database();
$sweetAlertConfig = "";

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}

// SweetAlert config from session
if (isset($_SESSION['sweetAlertConfig'])) {
    $sweetAlertConfig = $_SESSION['sweetAlertConfig'];
    unset($_SESSION['sweetAlertConfig']);
}
 
// Handle Add Promotion
if (isset($_POST['AddPromotion'])) {
    csrf_require();
    // Validate using allow-lists; reject any path-like input early (scanner payloads like "promotions.php")
    $rawCode = $_POST['Prom_Code'] ?? '';
    if (contains_path_traversal($rawCode)) {
        $code = '';
    } else {
        // Typical promo codes: letters/numbers + - _ ; keep tight
        $code = strtoupper(sanitize_string_allowlist($rawCode, 32, "-_"));
    }
    $desc = sanitize_string($_POST['Promo_Description'] ?? '', 500);
    $type = validate_enum($_POST['Promo_DiscountType'] ?? null, ['Percentage', 'Fixed'], null);

    // Amount: numeric only (Percentage 0-100, Fixed 0-1,000,000)
    $amount = validate_float_safe($_POST['Promo_DiscAmnt'] ?? null, 0.0, 1000000.0);

    $start = validate_date_ymd($_POST['Promo_StartDate'] ?? null);
    $end = validate_date_ymd($_POST['Promo_EndDate'] ?? null);
    $limit = validate_int($_POST['UsageLimit'] ?? null, 0, 1000000);

    if ($code === '' || $type === null || $amount === null || $start === null || $end === null || $limit === null) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please provide valid promotion details.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Business rule checks
    if ($type === 'Percentage' && ($amount < 0 || $amount > 100)) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Percentage discounts must be between 0 and 100.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (strtotime($end) < strtotime($start)) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'End date must be on or after the start date.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    // Always set to active when adding
    $isActive = 1;
 
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
 
if (isset($_POST['EditPromotion'])) {
    csrf_require();
    $promotionId = validate_id_safe($_POST['PromotionID'] ?? null);
    $rawCode = $_POST['Prom_Code'] ?? '';
    if (contains_path_traversal($rawCode)) {
        $code = '';
    } else {
        $code = strtoupper(sanitize_string_allowlist($rawCode, 32, "-_"));
    }
    $desc = sanitize_string($_POST['Promo_Description'] ?? '', 500);
    $amount = validate_float_safe($_POST['Promo_DiscAmnt'] ?? null, 0.0, 1000000.0);
    $type = validate_enum($_POST['Promo_DiscountType'] ?? null, ['Percentage', 'Fixed'], null);
    $start = validate_date_ymd($_POST['Promo_StartDate'] ?? null);
    $end = validate_date_ymd($_POST['Promo_EndDate'] ?? null);
    $limit = validate_int($_POST['UsageLimit'] ?? null, 0, 1000000);

    if ($promotionId === null || $code === '' || $type === null || $amount === null || $start === null || $end === null || $limit === null) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please provide valid promotion details.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    if ($type === 'Percentage' && ($amount < 0 || $amount > 100)) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Percentage discounts must be between 0 and 100.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if (strtotime($end) < strtotime($start)) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'End date must be on or after the start date.',
                confirmButtonText: 'OK'
            });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
 
    $result = $con->updatePromotionDetails($code, $desc, $amount, $type, $start, $end, $limit, $promotionId);
 
    if ($result) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'success',
                title: 'Promotion Updated',
                text: 'Promotion details have been updated!',
                confirmButtonText: 'Continue'
            });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Could not update promotion. Please try again.'
            });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
// Fetch all promotions
$allPromotions = $con->viewPromotions();

// Pagination logic
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Fixed items per page
$totalRecords = count($allPromotions);
$totalPages = max(1, ceil($totalRecords / $perPage));
$paginatedPromotions = array_slice($allPromotions, ($currentPage - 1) * $perPage, $perPage);
 
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
     <link href="../css/background.css" rel="stylesheet">
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
        <option value="Percentage">Percentage</option>
        <option value="Fixed">Fixed Amount</option>
    </select>
</div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Expired">Expired</option>
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
                    <?php if (!empty($paginatedPromotions)): ?>
                    <?php foreach ($paginatedPromotions as $promo): ?>
                    <?php
                        $now = strtotime(date('Y-m-d H:i:s'));
                        $start = strtotime($promo['Promo_StartDate']);
                        $end = strtotime($promo['Promo_EndDate']);
                        $isActive = $promo['Promo_IsActive'];
                        $usageCount = $con->getPromoUsageCount($promo['PromotionID']);
                        $usageLimit = $promo['UsageLimit'];

                        // Determine status and update database if needed
                        if ($now < $start && $isActive == 1) {
                            $status = 'Scheduled';
                            $badge = 'bg-info text-dark';
                        } elseif (($now > $end || ($usageLimit > 0 && $usageCount >= $usageLimit)) && $isActive == 1) {
                            // Expired: set to 0 in DB if not already
                            $status = 'Expired';
                            $badge = 'bg-danger';
                            $con->opencon()->prepare("UPDATE promotions SET Promo_IsActive=0 WHERE PromotionID=?")->execute([$promo['PromotionID']]);
                            $isActive = 0;
                        } elseif ($now >= $start && $now <= $end && $isActive == 1) {
                            $status = 'Active';
                            $badge = 'bg-success';
                        } elseif ($isActive == 0) {
                            // Only show expired promos (not inactive)
                            $status = 'Expired';
                            $badge = 'bg-danger';
                        } else {
                            // Skip any other status (like inactive)
                            continue;
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($promo['PromotionID']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Prom_Code']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_DiscAmnt']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_DiscountType']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_StartDate']); ?></td>
                        <td><?php echo htmlspecialchars($promo['Promo_EndDate']); ?></td>
                        <td><?php echo htmlspecialchars($promo['UsageLimit']); ?></td>
                        <td>
                            <?php echo "<span class='badge $badge'>$status</span>"; ?>
                        </td>
                        <td>
                            <!-- View Description Modal Trigger -->
                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewDescModal<?php echo $promo['PromotionID']; ?>">
                                <i class="bi bi-eye"></i> View
                            </button>
 
                            <?php if ($status !== 'Expired'): ?>
                            <!-- Edit Promotion Modal Trigger (only if not expired) -->
                            <button class="btn btn-sm btn-warning editPromotionBtn"
                                data-id="<?php echo $promo['PromotionID']; ?>"
                                data-code="<?php echo htmlspecialchars($promo['Prom_Code']); ?>"
                                data-desc="<?php echo htmlspecialchars($promo['Promo_Description']); ?>"
                                data-amount="<?php echo htmlspecialchars($promo['Promo_DiscAmnt']); ?>"
                                data-type="<?php echo htmlspecialchars($promo['Promo_DiscountType']); ?>"
                                data-start="<?php echo date('Y-m-d\TH:i', strtotime($promo['Promo_StartDate'])); ?>"
                                data-end="<?php echo date('Y-m-d\TH:i', strtotime($promo['Promo_EndDate'])); ?>"
                                data-limit="<?php echo htmlspecialchars($promo['UsageLimit']); ?>"
                                data-bs-toggle="modal" data-bs-target="#editPromotionModal">
                                <i class="bi bi-pencil"></i> Edit
                            </button>
                            <?php endif; ?>
 
                            <!-- View Description Modal -->
                            <div class="modal fade" id="viewDescModal<?php echo $promo['PromotionID']; ?>" tabindex="-1" aria-labelledby="viewDescLabel<?php echo $promo['PromotionID']; ?>" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title border-start border-4 border-success ps-3 text-success" id="viewDescLabel<?php echo $promo['PromotionID']; ?>">
                                                Promotion Details
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item"><strong>Promotion Code:</strong> <?php echo htmlspecialchars($promo['Prom_Code']); ?></li>
                                                <li class="list-group-item"><strong>Discount:</strong> <?php echo htmlspecialchars($promo['Promo_DiscAmnt']); ?></li>
                                                <li class="list-group-item"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($promo['Promo_Description'])); ?></li>
                                                <li class="list-group-item"><strong>Discount Type:</strong> <?php echo htmlspecialchars($promo['Promo_DiscountType']); ?></li>
                                                <li class="list-group-item"><strong>Start Date:</strong> <?php echo htmlspecialchars($promo['Promo_StartDate']); ?></li>
                                                <li class="list-group-item"><strong>End Date:</strong> <?php echo htmlspecialchars($promo['Promo_EndDate']); ?></li>
                                                <li class="list-group-item"><strong>Usage Limit:</strong> <?php echo htmlspecialchars($promo['UsageLimit']); ?></li>
                                                <li class="list-group-item"><strong>Status:</strong> <span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span></li>
                                            </ul>
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
        <!-- Pagination -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center align-items-center mt-4 mb-4">
                        <nav aria-label="Page navigation" class="mx-auto">
                            <ul class="pagination mb-0 justify-content-center">
                                <!-- First Page -->
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=1" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <!-- Previous Page -->
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php
                                $range = 2;
                                $startPage = max(1, $currentPage - $range);
                                $endPage = min($totalPages, $currentPage + $range);
                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                for($i = $startPage; $i <= $endPage; $i++) {
                                    echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                                    echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                                    echo '</li>';
                                }
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                                }
                                ?>
                                <!-- Next Page -->
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <!-- Last Page -->
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Add Promotion Modal -->
    <div class="modal fade" id="addPromotionModal" tabindex="-1" aria-labelledby="addPromotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addPromotionForm" method="POST">
                    <?php echo csrf_field(); ?>
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
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="AddPromotion" class="btn btn-primary">Save Promotion</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- Edit Promotion Modal  -->
    <div class="modal fade" id="editPromotionModal" tabindex="-1" aria-labelledby="editPromotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <?php echo csrf_field(); ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="editPromotionModalLabel">Edit Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Promotion Code</label>
                        <input type="text" class="form-control" name="Prom_Code" id="editPromCode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="Promo_Description" id="editPromoDescription" rows="2" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Discount Amount</label>
                            <input type="number" class="form-control" name="Promo_DiscAmnt" id="editPromoDiscAmnt" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Discount Type</label>
                            <select class="form-select" name="Promo_DiscountType" id="editPromoDiscountType" required>
                                <option value="Percentage">Percentage</option>
                                <option value="Fixed">Fixed Amount</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date & Time</label>
                            <input type="datetime-local" class="form-control" name="Promo_StartDate" id="editPromoStartDate" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date & Time</label>
                            <input type="datetime-local" class="form-control" name="Promo_EndDate" id="editPromoEndDate" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Usage Limit</label>
                        <input type="number" class="form-control" name="UsageLimit" id="editUsageLimit" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="PromotionID" id="editPromotionID">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="EditPromotion" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
 
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Fill Edit Promotion Modal with row data
    document.querySelectorAll('.editPromotionBtn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('editPromotionID').value = this.getAttribute('data-id');
        document.getElementById('editPromCode').value = this.getAttribute('data-code');
        document.getElementById('editPromoDescription').value = this.getAttribute('data-desc');
        document.getElementById('editPromoDiscAmnt').value = this.getAttribute('data-amount');
        document.getElementById('editPromoDiscountType').value = this.getAttribute('data-type');
        document.getElementById('editPromoStartDate').value = this.getAttribute('data-start');
        document.getElementById('editPromoEndDate').value = this.getAttribute('data-end');
        document.getElementById('editUsageLimit').value = this.getAttribute('data-limit');
    });
});

// Promotion Search and Filter
function filterPromotions() {
    const search = document.getElementById('promotionSearch').value.toLowerCase();
    const type = document.getElementById('typeFilter').value.toLowerCase();
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#promotionsTableBody tr');

    rows.forEach(row => {
        // Skip "No promotions found" row
        if (row.children.length < 8) {
            row.style.display = '';
            return;
        }
        const code = row.children[1].textContent.toLowerCase();
        const disc = row.children[2].textContent.toLowerCase();
        const discType = row.children[3].textContent.toLowerCase();
        const start = row.children[4].textContent.toLowerCase();
        const end = row.children[5].textContent.toLowerCase();
        const usage = row.children[6].textContent.toLowerCase();
        const stat = row.children[7].innerText.trim().toLowerCase();

        // Search filter (matches any column)
        const searchMatch =
            code.includes(search) ||
            disc.includes(search) ||
            discType.includes(search) ||
            start.includes(search) ||
            end.includes(search) ||
            usage.includes(search) ||
            stat.includes(search);

        // Type filter
        const typeMatch = (type === 'all') || (discType === type);

        // Status filter
        const statusMatch = (status === 'all') || (stat === status);

        row.style.display = (searchMatch && typeMatch && statusMatch) ? '' : 'none';
    });
}

document.getElementById('promotionSearch').addEventListener('input', filterPromotions);
document.getElementById('typeFilter').addEventListener('change', filterPromotions);
document.getElementById('statusFilter').addEventListener('change', filterPromotions);

    </script>
</body>
</html>