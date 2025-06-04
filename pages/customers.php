<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Customers</title>
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
            <h1>Customers</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-plus-lg"></i> Add Customer
            </button>
        </div>

        <!-- Customer Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text" id="totalCustomers">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Customers</h5>
                        <p class="card-text" id="activeCustomers">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">New This Month</h5>
                        <p class="card-text" id="newCustomers">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Loyalty Members</h5>
                        <p class="card-text" id="loyaltyMembers">0</p>
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
                    <input type="text" class="form-control" id="customerSearch" 
                           placeholder="Search customers..." aria-label="Search customers">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="customerTypeFilter">
                    <option value="all">All Types</option>
                    <option value="retail">Retail</option>
                    <option value="wholesale">Wholesale</option>
                    <option value="farm">Farm</option>
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

        <!-- Customers Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Total Orders</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm">
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerType" class="form-label">Customer Type</label>
                            <select class="form-select" id="customerType" required>
                                <option value="">Select Type</option>
                                <option value="retail">Retail</option>
                                <option value="wholesale">Wholesale</option>
                                <option value="farm">Farm</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taxId" class="form-label">Tax ID</label>
                            <input type="text" class="form-control" id="taxId">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="loyaltyProgram">
                                <label class="form-check-label" for="loyaltyProgram">
                                    Enroll in Loyalty Program
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
    // Mock customer data array
    const customers = [
        {
            id: 'C001',
            name: 'Juan Dela Cruz',
            type: 'Retail',
            phone: '09171234567',
            email: 'juan@email.com',
            totalOrders: 12,
            status: 'active'
        },
        {
            id: 'C002',
            name: 'Maria Santos',
            type: 'Wholesale',
            phone: '09181234567',
            email: 'maria@email.com',
            totalOrders: 5,
            status: 'inactive'
        },
        {
            id: 'C003',
            name: 'Pedro Reyes',
            type: 'Farm',
            phone: '09191234567',
            email: 'pedro@email.com',
            totalOrders: 20,
            status: 'active'
        }
    ];

    function getCustomerStatusBadge(status) {
        if (status === 'active') return '<span class="badge bg-success">Active</span>';
        if (status === 'inactive') return '<span class="badge bg-danger">Inactive</span>';
        return '';
    }

    function renderCustomersTable() {
        const tbody = document.getElementById('customersTableBody');
        tbody.innerHTML = '';
        customers.forEach(customer => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${customer.id}</td>
                <td>${customer.name}</td>
                <td>${customer.type}</td>
                <td>${customer.phone}</td>
                <td>${customer.email}</td>
                <td>${customer.totalOrders}</td>
                <td>${getCustomerStatusBadge(customer.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i> Edit</button>
                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Delete</button>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    // Initial render
    renderCustomersTable();
    </script>
    <script src="../js/scripts.js"></script>
</body>
</html> 