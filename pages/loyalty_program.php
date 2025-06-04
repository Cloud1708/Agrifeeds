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

        <!-- Program Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <p class="card-text" id="totalMembers">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Members</h5>
                        <p class="card-text" id="activeMembers">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Points Issued</h5>
                        <p class="card-text" id="pointsIssued">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Points Redeemed</h5>
                        <p class="card-text" id="pointsRedeemed">0</p>
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
                    <option value="bronze">Bronze</option>
                    <option value="silver">Silver</option>
                    <option value="gold">Gold</option>
                    <option value="platinum">Platinum</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
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
                                    <label for="pointsPerDollar" class="form-label">Points per Dollar</label>
                                    <input type="number" class="form-control" id="pointsPerDollar" 
                                           min="0" step="0.1" value="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="minimumPurchase" class="form-label">Minimum Purchase for Points</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="minimumPurchase" 
                                               min="0" step="0.01" value="1" required>
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
                                    <label for="pointsValue" class="form-label">Points Value (in dollars)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="pointsValue" 
                                               min="0" step="0.01" value="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="minimumRedemption" class="form-label">Minimum Points for Redemption</label>
                                    <input type="number" class="form-control" id="minimumRedemption" 
                                           min="0" value="100" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Tier Benefits</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="silverDiscount" checked>
                                <label class="form-check-label" for="silverDiscount">
                                    Silver Tier: 5% Discount on All Purchases
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="goldDiscount" checked>
                                <label class="form-check-label" for="goldDiscount">
                                    Gold Tier: 10% Discount on All Purchases
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="platinumDiscount" checked>
                                <label class="form-check-label" for="platinumDiscount">
                                    Platinum Tier: 15% Discount on All Purchases
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Points Expiration</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pointsExpiry" class="form-label">Points Expire After (months)</label>
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
    <script>
    // Mock members data array
    const members = [
        {
            id: 'M001',
            name: 'Anna Cruz',
            tier: 'Gold',
            points: 3200,
            joinDate: '2023-01-15',
            status: 'active'
        },
        {
            id: 'M002',
            name: 'Ben Santos',
            tier: 'Silver',
            points: 1100,
            joinDate: '2022-11-10',
            status: 'inactive'
        },
        {
            id: 'M003',
            name: 'Carla Reyes',
            tier: 'Platinum',
            points: 10500,
            joinDate: '2021-08-22',
            status: 'active'
        }
    ];

    function getMemberStatusBadge(status) {
        if (status === 'active') return '<span class="badge bg-success">Active</span>';
        if (status === 'inactive') return '<span class="badge bg-danger">Inactive</span>';
        return '';
    }

    function renderMembersTable() {
        const tbody = document.getElementById('membersTableBody');
        tbody.innerHTML = '';
        members.forEach(member => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${member.id}</td>
                <td>${member.name}</td>
                <td>${member.tier}</td>
                <td>${member.points}</td>
                <td>${member.joinDate}</td>
                <td>${getMemberStatusBadge(member.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Initial render
    renderMembersTable();
    </script>
    <script src="../js/scripts.js"></script>
</body>
</html> 