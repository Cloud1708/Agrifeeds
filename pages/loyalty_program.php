<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Loyalty Program</title>
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
            <h1>Loyalty Program</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#programSettingsModal">
                <i class="bi bi-gear"></i> Program Settings
            </button>
        </div>

        <!-- Program Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <p class="card-text" id="totalMembers">150</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Members</h5>
                        <p class="card-text" id="activeMembers">120</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Points Issued</h5>
                        <p class="card-text" id="pointsIssued">25,000</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Points Redeemed</h5>
                        <p class="card-text" id="pointsRedeemed">8,500</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="memberSearch" 
                           placeholder="Search members..." aria-label="Search members">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="tierFilter">
                    <option value="all">All Tiers</option>
                    <option value="Bronze">Bronze</option>
                    <option value="Silver">Silver</option>
                    <option value="Gold">Gold</option>
                    <option value="Platinum">Platinum</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Active">Active</option>
                    <option value="Inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Members Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Member ID</th>
                        <th>Name</th>
                        <th>Tier</th>
                        <th>Points Balance</th>
                        <th>Join Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Program Settings Modal -->
    <div class="modal fade" id="programSettingsModal" tabindex="-1" aria-labelledby="programSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="programSettingsModalLabel">Loyalty Program Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="programSettingsForm">
                        <div class="mb-4">
                            <h6>Points Earning Rules</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pointsPerPeso" class="form-label">Points per ₱1 Spent</label>
                                    <input type="number" class="form-control" id="pointsPerPeso" 
                                           min="0" step="0.01" value="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="minPointsEarn" class="form-label">Minimum Purchase for Points</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="minPointsEarn" 
                                               min="0" step="0.01" value="100" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Tier Requirements</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="silverTier" class="form-label">Silver Tier (Points)</label>
                                    <input type="number" class="form-control" id="silverTier" 
                                           min="0" value="1000" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="goldTier" class="form-label">Gold Tier (Points)</label>
                                    <input type="number" class="form-control" id="goldTier" 
                                           min="0" value="5000" required>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <label for="platinumTier" class="form-label">Platinum Tier (Points)</label>
                                    <input type="number" class="form-control" id="platinumTier" 
                                           min="0" value="10000" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Points Redemption</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pointsValue" class="form-label">Points Value (₱)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="pointsValue" 
                                               min="0" step="0.01" value="0.10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="minPointsRedeem" class="form-label">Minimum Points to Redeem</label>
                                    <input type="number" class="form-control" id="minPointsRedeem" 
                                           min="0" value="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Tier Benefits</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="silverDiscount" checked>
                                <label class="form-check-label" for="silverDiscount">
                                    Silver Tier: 5% Discount
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="goldDiscount" checked>
                                <label class="form-check-label" for="goldDiscount">
                                    Gold Tier: 10% Discount
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platinumDiscount" checked>
                                <label class="form-check-label" for="platinumDiscount">
                                    Platinum Tier: 15% Discount
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Points Expiration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pointsExpiry" class="form-label">Points Expire After (Months)</label>
                                    <input type="number" class="form-control" id="pointsExpiry" 
                                           min="0" value="12" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSettingsBtn">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->

</body>
</html> 