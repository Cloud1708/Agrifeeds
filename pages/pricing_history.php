<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing History</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .dashboard-card .card-title { font-size: 1rem; }
        .dashboard-card .card-text { font-size: 1.3rem; font-weight: 600; }
        .main-content { margin-left: 260px; padding: 2rem 2.5rem; }
    </style>
</head>
<body>
    <!-- Sidebar include (replace with your own sidebar code if needed) -->
    <!-- <?php include '../includes/sidebar.php'; ?> -->

    <div class="main-content">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Pricing History</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHistoryModal">
                <i class="bi bi-plus-lg"></i> Add History
            </button>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Records</h5>
                        <p class="card-text" id="totalRecords">12</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Current Prices</h5>
                        <p class="card-text" id="currentPrices">8</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Changes</h5>
                        <p class="card-text" id="upcomingChanges">2</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Expired Prices</h5>
                        <p class="card-text" id="expiredPrices">2</p>
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
                    <input type="text" class="form-control" id="historySearch" placeholder="Search pricing history..." aria-label="Search pricing history">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="productFilter">
                    <option value="all">All Products</option>
                    <option value="2001">Product 2001</option>
                    <option value="2002">Product 2002</option>
                    <option value="2003">Product 2003</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="current">Current</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>

        <!-- Pricing History Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>HistoryID</th>
                        <th>ProductID</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>Change Date</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Created At</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>101</td>
                        <td>2001</td>
                        <td>₱150.00</td>
                        <td>₱180.00</td>
                        <td>2025-05-01</td>
                        <td>2025-05-05</td>
                        <td>2025-06-01</td>
                        <td>2025-05-01 10:30:00</td>
                        <td><span class="badge bg-success">Current</span></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button 
                                    type="button" 
                                    class="btn btn-warning btn-sm editHistoryBtn"
                                    data-id="101"
                                    data-product="2001"
                                    data-oldprice="150.00"
                                    data-newprice="180.00"
                                    data-changedate="2025-05-01"
                                    data-effectivefrom="2025-05-05"
                                    data-effectiveto="2025-06-01"
                                    data-createdat="2025-05-01 10:30:00"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editHistoryModal"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm deleteHistoryBtn" data-id="101">
                                    <i class="bi bi-x-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>102</td>
                        <td>2002</td>
                        <td>₱90.00</td>
                        <td>₱100.00</td>
                        <td>2025-04-20</td>
                        <td>2025-04-25</td>
                        <td>2025-05-25</td>
                        <td>2025-04-20 09:15:00</td>
                        <td><span class="badge bg-secondary">Expired</span></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button 
                                    type="button" 
                                    class="btn btn-warning btn-sm editHistoryBtn"
                                    data-id="102"
                                    data-product="2002"
                                    data-oldprice="90.00"
                                    data-newprice="100.00"
                                    data-changedate="2025-04-20"
                                    data-effectivefrom="2025-04-25"
                                    data-effectiveto="2025-05-25"
                                    data-createdat="2025-04-20 09:15:00"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editHistoryModal"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm deleteHistoryBtn" data-id="102">
                                    <i class="bi bi-x-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <!-- More rows as needed -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add History Modal -->
    <div class="modal fade" id="addHistoryModal" tabindex="-1" aria-labelledby="addHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addHistoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHistoryModalLabel">Add Pricing History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addProductID" class="form-label">Product ID</label>
                            <input type="text" class="form-control" id="addProductID" required>
                        </div>
                        <div class="mb-3">
                            <label for="addOldPrice" class="form-label">Old Price</label>
                            <input type="number" class="form-control" id="addOldPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="addNewPrice" class="form-label">New Price</label>
                            <input type="number" class="form-control" id="addNewPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="addChangeDate" class="form-label">Change Date</label>
                            <input type="date" class="form-control" id="addChangeDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="addEffectiveFrom" class="form-label">Effective From</label>
                            <input type="date" class="form-control" id="addEffectiveFrom" required>
                        </div>
                        <div class="mb-3">
                            <label for="addEffectiveTo" class="form-label">Effective To</label>
                            <input type="date" class="form-control" id="addEffectiveTo">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save History</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit History Modal -->
    <div class="modal fade" id="editHistoryModal" tabindex="-1" aria-labelledby="editHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHistoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHistoryModalLabel">Edit Pricing History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editHistoryID">
                        <div class="mb-3">
                            <label for="editProductID" class="form-label">Product ID</label>
                            <input type="text" class="form-control" id="editProductID" required>
                        </div>
                        <div class="mb-3">
                            <label for="editOldPrice" class="form-label">Old Price</label>
                            <input type="number" class="form-control" id="editOldPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editNewPrice" class="form-label">New Price</label>
                            <input type="number" class="form-control" id="editNewPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="editChangeDate" class="form-label">Change Date</label>
                            <input type="date" class="form-control" id="editChangeDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEffectiveFrom" class="form-label">Effective From</label>
                            <input type="date" class="form-control" id="editEffectiveFrom" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEffectiveTo" class="form-label">Effective To</label>
                            <input type="date" class="form-control" id="editEffectiveTo">
                        </div>
                        <div class="mb-3">
                            <label for="editCreatedAt" class="form-label">Created At</label>
                            <input type="text" class="form-control" id="editCreatedAt" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update History</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Fill Edit Modal with pricing history data
        document.querySelectorAll('.editHistoryBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('editHistoryID').value = this.dataset.id;
                document.getElementById('editProductID').value = this.dataset.product;
                document.getElementById('editOldPrice').value = this.dataset.oldprice;
                document.getElementById('editNewPrice').value = this.dataset.newprice;
                document.getElementById('editChangeDate').value = this.dataset.changedate;
                document.getElementById('editEffectiveFrom').value = this.dataset.effectivefrom;
                document.getElementById('editEffectiveTo').value = this.dataset.effectiveto;
                document.getElementById('editCreatedAt').value = this.dataset.createdat;
            });
        });

        // Example for search and filter (static, for demo)
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table.table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const searchInput = document.getElementById('historySearch');
            const productFilter = document.getElementById('productFilter');
            const statusFilter = document.getElementById('statusFilter');
            let currentSort = { col: null, dir: 1 };

            function filterRows() {
                const search = searchInput.value.toLowerCase();
                const product = productFilter.value;
                const status = statusFilter.value;
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const prodID = cells[1].textContent.toLowerCase();
                    const statusText = cells[8].textContent.toLowerCase();
                    let show = true;
                    if (search && !row.textContent.toLowerCase().includes(search)) {
                        show = false;
                    }
                    if (product !== 'all' && prodID !== product) {
                        show = false;
                    }
                    if (status !== 'all' && statusText !== status) {
                        show = false;
                    }
                    row.style.display = show ? '' : 'none';
                });
            }
            searchInput.addEventListener('input', filterRows);
            productFilter.addEventListener('change', filterRows);
            statusFilter.addEventListener('change', filterRows);

            // Sort columns
            table.querySelectorAll('th').forEach((th, idx) => {
                if (idx === 9) return; // Skip Actions column
                th.style.cursor = 'pointer';
                th.addEventListener('click', function() {
                    let dir = 1;
                    if (currentSort.col === idx) dir = -currentSort.dir;
                    currentSort = { col: idx, dir };
                    rows.sort((a, b) => {
                        let aText = a.children[idx].textContent.trim();
                        let bText = b.children[idx].textContent.trim();
                        // Numeric sort for id and price columns
                        if ([0,1,2,3].includes(idx)) {
                            aText = parseFloat(aText.replace(/[^\d.\-]/g, ''));
                            bText = parseFloat(bText.replace(/[^\d.\-]/g, ''));
                            if (isNaN(aText)) aText = 0;
                            if (isNaN(bText)) bText = 0;
                            return dir * (aText - bText);
                        }
                        return dir * aText.localeCompare(bText);
                    });
                    // Re-append sorted rows
                    rows.forEach(row => tbody.appendChild(row));
                });
            });
        });
    </script>
</body>
</html>