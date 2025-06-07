<?php
session_start();

require_once('../includes/db.php');
$con = new database();
$members = $con->viewLoyaltyProgram();

// Update each member's tier before displaying
foreach ($members as &$member) {
    $con->updateMemberTier($member['CustomerID']);
    // Optionally, fetch the updated tier if you want to display it from DB
    $stmt = $con->opencon()->prepare("SELECT LP_MbspTier FROM loyalty_program WHERE CustomerID = ?");
    $stmt->execute([$member['CustomerID']]);
    $member['LP_MbspTier'] = $stmt->fetchColumn();
}
unset($member);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Loyalty Program</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

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
                        <p class="card-text" id="totalMembers"><?php echo count($members); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Members</h5>
                        <p class="card-text" id="activeMembers">
                            <?php echo count(array_filter($members, function($m) { return $m['LP_PtsBalance'] > 0; })); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Points Issued</h5>
                        <p class="card-text" id="pointsIssued">
                            <?php echo number_format(array_sum(array_column($members, 'LP_PtsBalance'))); ?>
                        </p>
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
                        <th>LoyaltyID</th>
                        <th>CustomerID</th>
                        <th>Points Balance</th>
                        <th>Tier</th>
                        <th>Last Update</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
            <?php foreach ($members as $member) { ?>
            <tr>
                <td><?php echo $member['LoyaltyID']; ?></td>
                <td>
                    <?php echo htmlspecialchars($member['Cust_Name']); ?>
                    <span class="text-muted small">(ID: <?php echo $member['CustomerID']; ?>)</span>
                </td>
                <td><?php echo number_format($member['LP_PtsBalance']); ?></td>
                <td>
                    <?php
                        $tier = isset($member['LP_MbspTier']) ? $member['LP_MbspTier'] : 'None';
                        switch ($tier) {
                            case 'Gold':
                                echo '<span class="badge bg-warning text-dark">Gold</span>';
                                break;
                            case 'Silver':
                                echo '<span class="badge bg-secondary">Silver</span>';
                                break;
                            case 'Bronze':
                                echo '<span class="badge bg-dark text-light">Bronze</span>';
                                break;
                            default:
                                echo '<span class="badge bg-light text-dark">None</span>';
                        }
                    ?>
                </td>
                <td><?php echo date('Y-m-d', strtotime($member['LP_LastUpdt'])); ?></td>
                <td>
                    <button class="btn btn-sm btn-info" onclick="viewMember(<?php echo $member['LoyaltyID']; ?>)">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editMember(<?php echo $member['LoyaltyID']; ?>)">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>
            <?php } ?>
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
                                    <input type="number" class="form-control" id="silverTier">
                                </div>
                                <div class="col-md-6">
                                    <label for="goldTier" class="form-label">Gold Tier (Points)</label>
                                    <input type="number" class="form-control" id="goldTier">
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Points Redemption</h6>
                            <div class="row">
                                <!-- Redemption settings here -->
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>