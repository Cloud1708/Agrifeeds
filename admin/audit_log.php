<?php
session_start();
require_once '../includes/db.php';
$con = new database();

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header("Location: ../login.php");
    exit();
}

// Get filter from URL parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get all activities
$activities = [];

// Get product history
$products = $con->viewProducts();
foreach ($products as $product) {
    // For product creation
    $user = $con->getUserById($product['UserID']);
    $userName = $user ? $user['User_Name'] : 'Unknown User';
    
    $activities[] = [
        'date' => $product['Prod_Created_at'],
        'text' => '<strong>' . htmlspecialchars($userName) . '</strong> added product <strong>"' . htmlspecialchars($product['Prod_Name']) . '"</strong>',
        'type' => 'product_add'
    ];
    
    // Get product access logs
    $accessLogs = $con->getProductAccessLogs($product['ProductID']);
    foreach ($accessLogs as $log) {
        if (strpos($log['Pal_Action'], 'Updated product:') === 0) {
            // Check if this is a stock change
            if (strpos($log['Pal_Action'], 'stock from') !== false) {
                // Extract stock information
                preg_match('/stock from (\d+) to (\d+)/', $log['Pal_Action'], $matches);
                if (count($matches) === 3) {
                    $oldStock = $matches[1];
                    $newStock = $matches[2];
                    $activities[] = [
                        'date' => $log['Pal_TimeStamp'],
                        'text' => '<strong>' . htmlspecialchars($log['User_Name']) . '</strong> changed stock level for <strong>"' . htmlspecialchars($product['Prod_Name']) . '"</strong> from <strong>' . $oldStock . '</strong> to <strong>' . $newStock . ' units</strong>',
                        'type' => 'stock_update'
                    ];
                    continue; // Skip adding as product_update
                }
            }
            // Check if this is a price change
            if (strpos($log['Pal_Action'], 'price from') !== false) {
                // Extract price information
                preg_match('/price from ([\d,.]+) to ([\d,.]+)/', $log['Pal_Action'], $matches);
                if (count($matches) === 3) {
                    $oldPrice = $matches[1];
                    $newPrice = $matches[2];
                    $activities[] = [
                        'date' => $log['Pal_TimeStamp'],
                        'text' => '<strong>' . htmlspecialchars($log['User_Name']) . '</strong> updated price for <strong>"' . htmlspecialchars($product['Prod_Name']) . '"</strong> from <strong>₱' . $oldPrice . '</strong> to <strong>₱' . $newPrice . '</strong>',
                        'type' => 'price_update'
                    ];
                    continue; // Skip adding as product_update
                }
            }
            // Handle other product updates
            $activities[] = [
                'date' => $log['Pal_TimeStamp'],
                'text' => '<strong>' . htmlspecialchars($log['User_Name']) . '</strong> ' . htmlspecialchars($log['Pal_Action']) . ' <strong>"' . htmlspecialchars($product['Prod_Name']) . '"</strong>',
                'type' => 'product_update'
            ];
        }
    }

    // Get inventory history
    $inventoryHistory = $con->getInventoryHistory($product['ProductID']);
    if ($inventoryHistory) {
        foreach ($inventoryHistory as $history) {
            $user = $con->getUserById($history['UserID']);
            $userName = $user ? $user['User_Name'] : 'Unknown User';
            
            $activities[] = [
                'date' => $history['IH_ChangeDate'],
                'text' => '<strong>' . htmlspecialchars($userName) . '</strong> ' . 
                         ($history['IH_QtyChange'] > 0 ? 'added' : 'reduced') . ' stock for <strong>"' . 
                         htmlspecialchars($product['Prod_Name']) . '"</strong> by <strong>' . 
                         abs($history['IH_QtyChange']) . ' units</strong> (New stock: <strong>' . 
                         $history['IH_NewStckLvl'] . ' units</strong>)',
                'type' => 'stock_update'
            ];
        }
    }

    // Get pricing history
    $pricingHistory = $con->getPricingHistory($product['ProductID']);
    if ($pricingHistory) {
        foreach ($pricingHistory as $history) {
            $user = $con->getUserById($history['UserID']);
            $userName = $user ? $user['User_Name'] : 'Unknown User';
            
            $activities[] = [
                'date' => $history['PH_ChangeDate'],
                'text' => '<strong>' . htmlspecialchars($userName) . '</strong> updated price for <strong>"' . 
                         htmlspecialchars($product['Prod_Name']) . '"</strong> from <strong>₱' . 
                         number_format($history['PH_OldPrice'], 2) . '</strong> to <strong>₱' . 
                         number_format($history['PH_NewPrice'], 2) . '</strong>',
                'type' => 'price_update'
            ];
        }
    }
}

// Sort activities by date (newest first)
usort($activities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Calculate activity counts
$activity_counts = [
    'all' => count($activities),
    'product_add' => count(array_filter($activities, fn($a) => $a['type'] === 'product_add')),
    'product_update' => count(array_filter($activities, fn($a) => $a['type'] === 'product_update')),
    'price_update' => count(array_filter($activities, fn($a) => $a['type'] === 'price_update')),
    'stock_update' => count(array_filter($activities, fn($a) => $a['type'] === 'stock_update'))
];

// Apply filter if not 'all'
if ($filter !== 'all') {
    $activities = array_filter($activities, function($activity) use ($filter) {
        return $activity['type'] === $filter;
    });
}

// Calculate total pages
$total_activities = count($activities);
$total_pages = ceil($total_activities / $items_per_page);

// Get current page activities
$current_page_activities = array_slice($activities, $offset, $items_per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Audit Log</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="../css/audit_log.css" rel="stylesheet">
    <link href="../css/components.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h1>User Activity Log</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="number"><?php echo $activity_counts['all']; ?></div>
                <div class="label">Total Activities</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $activity_counts['product_add']; ?></div>
                <div class="label">Product Additions</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $activity_counts['product_update']; ?></div>
                <div class="label">Product Updates</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $activity_counts['price_update']; ?></div>
                <div class="label">Price Changes</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $activity_counts['stock_update']; ?></div>
                <div class="label">Stock Changes</div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="bi bi-list-ul"></i> All Activities
            </a>
            <a href="?filter=product_add" class="filter-btn <?php echo $filter === 'product_add' ? 'active' : ''; ?>">
                <i class="bi bi-plus-circle"></i> Product Additions
            </a>
            <a href="?filter=product_update" class="filter-btn <?php echo $filter === 'product_update' ? 'active' : ''; ?>">
                <i class="bi bi-pencil"></i> Product Updates
            </a>
            <a href="?filter=price_update" class="filter-btn <?php echo $filter === 'price_update' ? 'active' : ''; ?>">
                <i class="bi bi-currency-dollar"></i> Price Changes
            </a>
            <a href="?filter=stock_update" class="filter-btn <?php echo $filter === 'stock_update' ? 'active' : ''; ?>">
                <i class="bi bi-box"></i> Stock Changes
            </a>
        </div>

        <!-- Activity Feed -->
        <div class="activity-feed">
            <?php foreach ($current_page_activities as $activity): ?>
                <div class="feed-item" data-type="<?php echo $activity['type']; ?>">
                    <div class="date">
                        <i class="bi bi-clock"></i>
                        <?php echo date('M d, Y H:i', strtotime($activity['date'])); ?>
                    </div>
                    <div class="text">
                        <?php echo $activity['text']; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1&filter=' . $filter . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?page=' . $i . '&filter=' . $filter . '">' . $i . '</a>';
                    echo '</li>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&filter=' . $filter . '">' . $total_pages . '</a></li>';
                }
                ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html> 