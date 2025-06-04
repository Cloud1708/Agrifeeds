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
</head>
<body>
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
                    <option value="discount">Discount</option>
                    <option value="bogo">Buy One Get One</option>
                    <option value="bundle">Bundle Deal</option>
                    <option value="clearance">Clearance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>

        <!-- Promotions Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Promotion Name</th>
                        <th>Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Discount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="promotionsTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Promotion Modal -->
    <div class="modal fade" id="addPromotionModal" tabindex="-1" aria-labelledby="addPromotionModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addPromotionModalLabel">New Promotion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPromotionForm">
                        <div class="mb-3">
                            <label for="promotionName" class="form-label">Promotion Name</label>
                            <input type="text" class="form-control" id="promotionName" required>
                        </div>
                        <div class="mb-3">
                            <label for="promotionType" class="form-label">Promotion Type</label>
                            <select class="form-select" id="promotionType" required>
                                <option value="">Select Type</option>
                                <option value="discount">Discount</option>
                                <option value="bogo">Buy One Get One</option>
                                <option value="bundle">Bundle Deal</option>
                                <option value="clearance">Clearance</option>
                            </select>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="startDate" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="startDate" required>
                            </div>
                            <div class="col-md-6">
                                <label for="endDate" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="endDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="discountType" class="form-label">Discount Type</label>
                            <select class="form-select" id="discountType" required>
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed Amount</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="discountValue" class="form-label">Discount Value</label>
                            <div class="input-group">
                                <span class="input-group-text" id="discountSymbol">%</span>
                                <input type="number" class="form-control" id="discountValue" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="minPurchase" class="form-label">Minimum Purchase Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="minPurchase" 
                                       step="0.01" min="0" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="applicableProducts" class="form-label">Applicable Products</label>
                            <select class="form-select" id="applicableProducts" multiple required>
                                <!-- Product options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="promotionDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="promotionDescription" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="excludeDiscounts">
                                <label class="form-check-label" for="excludeDiscounts">
                                    Cannot be combined with other discounts
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePromotionBtn">Save Promotion</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/scripts.js"></script>
</body>
</html> 