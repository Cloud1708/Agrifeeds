<?php
session_start();
 
require_once('../includes/db.php');
$con = new database();
 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_settings') {
    $bronze = intval($_POST['bronze']);
    $silver = intval($_POST['silver']);
    $gold = intval($_POST['gold']);
    $minPurchase = floatval($_POST['min_purchase']);
    $pointsPerPeso = floatval($_POST['points_per_peso']);
        $pointsExpireAfter = intval($_POST['points_expire_after']);
 
    $con->saveLoyaltySettings($bronze, $silver, $gold, $minPurchase, $pointsPerPeso, $pointsExpireAfter);
    $members = $con->viewLoyaltyProgram();
    foreach ($members as $member) {
        $con->updateMemberTier($member['CustomerID']);
    }
 
    echo json_encode(['success' => true]);
    exit;
}
 
$settings = $con->opencon()->query("SELECT bronze, silver, gold, min_purchase, points_per_peso, points_expire_after FROM loyalty_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
$members = $con->viewLoyaltyProgram();
$con->resetExpiredPoints($settings['points_expire_after']);
 
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
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="membersTableBody">
            <?php foreach ($members as $member) { ?>
<tr>
    <td><?php echo $member['LoyaltyID']; ?></td>
    <td>
        <?php echo htmlspecialchars($member['Cust_FN'] . ' ' . $member['Cust_LN']); ?>
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
        <?php
            $expireMonths = isset($settings['points_expire_after']) ? (int)$settings['points_expire_after'] : 12;
            $expireDate = date('Y-m-d', strtotime($member['LP_LastUpdt'] . " +$expireMonths months"));
            $today = date('Y-m-d');
            $status = ($today <= $expireDate) ? 'Active' : 'Inactive';
            $badge = ($status == 'Active') ? 'bg-success' : 'bg-danger';
        ?>
        <span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span>
    </td>
 
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
                                <input type="number" class="form-control" id="pointsPerPeso" min="0" step="0.01"
                                    value="<?php echo isset($settings['points_per_peso']) ? $settings['points_per_peso'] : 1; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="minPointsEarn" class="form-label">Minimum Purchase for Points</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control" id="minPointsEarn" min="0" step="0.01"
                                        value="<?php echo isset($settings['min_purchase']) ? $settings['min_purchase'] : 100; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>
 
                   
                    <div class="mb-4">
                        <h6>Tier Requirements</h6>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="bronzeTier" class="form-label">Bronze Tier (Points)</label>
                                <input type="number" class="form-control" id="bronzeTier"
                                    value="<?php echo isset($settings['bronze']) ? $settings['bronze'] : 5000; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="silverTier" class="form-label">Silver Tier (Points)</label>
                                <input type="number" class="form-control" id="silverTier"
                                    value="<?php echo isset($settings['silver']) ? $settings['silver'] : 10000; ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="goldTier" class="form-label">Gold Tier (Points)</label>
                                <input type="number" class="form-control" id="goldTier"
                                    value="<?php echo isset($settings['gold']) ? $settings['gold'] : 15000; ?>">
                            </div>
                        </div>
                    </div>
 
                    <div class="mb-4">
    <h6>Points Redemption</h6>
    <ul>
        <li><strong>Gold:</strong> <?php echo isset($settings['gold']) ? number_format($settings['gold']) : '15,000'; ?> points</li>
        <li><strong>Silver:</strong> <?php echo isset($settings['silver']) ? number_format($settings['silver']) : '10,000'; ?> points</li>
        <li><strong>Bronze:</strong> <?php echo isset($settings['bronze']) ? number_format($settings['bronze']) : '5,000'; ?> points</li>
    </ul>
</div>
                    <div class="col-md-4">
    <label for="points_expire_after" class="form-label">Points Expire After (Months)</label>
    <input type="number" class="form-control" id="points_expire_after" name="points_expire_after"
        value="<?php echo isset($settings['points_expire_after']) ? $settings['points_expire_after'] : 12; ?>">
</div>
                    <div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">Save</button>
</div>
                </form>
            </div>
        </div>
    </div>
</div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
 
<script>// ...existing code...
// In your JS
document.getElementById('programSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const bronze = document.getElementById('bronzeTier').value;
    const silver = document.getElementById('silverTier').value;
    const gold = document.getElementById('goldTier').value;
    const minPurchase = document.getElementById('minPointsEarn').value;
    const pointsPerPeso = document.getElementById('pointsPerPeso').value;
    const pointsExpireAfter = document.getElementById('points_expire_after').value; // NEW
 
    fetch('loyalty_program.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `action=save_settings&bronze=${bronze}&silver=${silver}&gold=${gold}&min_purchase=${minPurchase}&points_per_peso=${pointsPerPeso}&points_expire_after=${pointsExpireAfter}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to save settings.');
        }
    });
});
</script>
 
</body>
</html>