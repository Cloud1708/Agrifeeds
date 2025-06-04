// Global variables
let products = [];
let customers = [];
let sales = [];
let salesMetrics = {};
let suppliers = [];
let purchaseOrders = [];
let poMetrics = {};
let promotions = [];
let promotionMetrics = {};
let currentUser = null;
let loyaltyMembers = [];
let loyaltyRewards = [];
let loyaltyMetrics = {};
let inventoryAlerts = [];
let alertSettings = {};
let alertMetrics = {};

// Report functions
let trendChart = null;
let distributionChart = null;

// Load mock data
async function loadMockData() {
    try {
        const response = await fetch('../js/mock-data.json');
        const data = await response.json();
        
        products = data.products;
        customers = data.customers;
        sales = data.sales;
        salesMetrics = data.sales_metrics;
        suppliers = data.suppliers;
        purchaseOrders = data.purchase_orders;
        poMetrics = data.po_metrics;
        promotions = data.promotions;
        promotionMetrics = data.promotion_metrics;
        loyaltyMembers = data.loyalty_members;
        loyaltyRewards = data.loyalty_rewards;
        loyaltyMetrics = data.loyalty_metrics;
        inventoryAlerts = data.inventory_alerts;
        alertSettings = data.alert_settings;
        alertMetrics = data.alert_metrics;
        
        // Initialize pages based on current URL
        const currentPage = window.location.pathname.split('/').pop();
        switch(currentPage) {
            case 'dashboard.php':
                populateDashboardMetrics(data.dashboard_metrics);
                break;
            case 'products.php':
                populateProductsTable();
                break;
            case 'customers.php':
                populateCustomersTable();
                break;
            case 'sales.php':
                populateSalesMetrics();
                populateSalesTable();
                break;
            case 'suppliers.php':
                populateSuppliersTable();
                break;
            case 'purchase_orders.php':
                populatePOMetrics();
                populatePurchaseOrdersTable();
                populateSupplierSelect();
                populateProductSelects();
                break;
            case 'promotions.php':
                populatePromotionMetrics();
                populatePromotionsTable();
                populatePromotionProducts();
                break;
            case 'loyalty_program.php':
                populateLoyaltyMetrics();
                populateMembersTable();
                populateRewardsTable();
                break;
            case 'inventory_alerts.php':
                populateAlertMetrics();
                populateAlertsTable();
                loadAlertSettings();
                break;
        }
    } catch (error) {
        console.error('Error loading mock data:', error);
    }
}

// Populate products table
function populateProductsTable() {
    const tableBody = document.getElementById('productsTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    products.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>$${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-product" data-id="${product.id}" aria-label="Edit ${product.name}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}" aria-label="Delete ${product.name}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Populate customers table
function populateCustomersTable() {
    const tableBody = document.getElementById('customersTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    customers.forEach(customer => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer.name}</td>
            <td>${customer.type}</td>
            <td>${customer.contact}</td>
            <td>${customer.email}</td>
            <td>${customer.total_orders}</td>
            <td>${customer.loyalty_points}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-customer" data-id="${customer.id}" aria-label="Edit ${customer.name}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-info view-customer" data-id="${customer.id}" aria-label="View ${customer.name}">
                    <i class="bi bi-eye"></i> View
                </button>
                <button class="btn btn-sm btn-danger delete-customer" data-id="${customer.id}" aria-label="Delete ${customer.name}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Populate dashboard metrics
function populateDashboardMetrics(metrics) {
    const metricsContainer = document.getElementById('dashboardMetrics');
    if (!metricsContainer) return;

    metricsContainer.innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <p class="card-text">$${metrics.total_sales.toLocaleString()}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Products</h5>
                    <p class="card-text">${metrics.low_stock_products}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Promotions</h5>
                    <p class="card-text">${metrics.active_promotions}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Customers</h5>
                    <p class="card-text">${metrics.total_customers}</p>
                </div>
            </div>
        </div>
    `;
}

// Search products
function searchProducts(query) {
    const filteredProducts = products.filter(product => 
        product.name.toLowerCase().includes(query.toLowerCase()) ||
        product.category.toLowerCase().includes(query.toLowerCase())
    );
    
    const tableBody = document.getElementById('productsTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    filteredProducts.forEach(product => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>$${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-product" data-id="${product.id}" aria-label="Edit ${product.name}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-danger delete-product" data-id="${product.id}" aria-label="Delete ${product.name}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Search customers
function searchCustomers(query, type = '') {
    let filteredCustomers = customers.filter(customer => 
        customer.name.toLowerCase().includes(query.toLowerCase()) ||
        customer.email.toLowerCase().includes(query.toLowerCase()) ||
        customer.contact.includes(query)
    );

    if (type) {
        filteredCustomers = filteredCustomers.filter(customer => customer.type === type);
    }
    
    const tableBody = document.getElementById('customersTableBody');
    if (!tableBody) return;

    tableBody.innerHTML = '';
    filteredCustomers.forEach(customer => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer.name}</td>
            <td>${customer.type}</td>
            <td>${customer.contact}</td>
            <td>${customer.email}</td>
            <td>${customer.total_orders}</td>
            <td>${customer.loyalty_points}</td>
            <td>
                <button class="btn btn-sm btn-primary edit-customer" data-id="${customer.id}" aria-label="Edit ${customer.name}">
                    <i class="bi bi-pencil"></i> Edit
                </button>
                <button class="btn btn-sm btn-info view-customer" data-id="${customer.id}" aria-label="View ${customer.name}">
                    <i class="bi bi-eye"></i> View
                </button>
                <button class="btn btn-sm btn-danger delete-customer" data-id="${customer.id}" aria-label="Delete ${customer.name}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

// Sales functions
function populateSalesMetrics() {
    document.getElementById('todaySales').textContent = `$${salesMetrics.today_sales.toFixed(2)}`;
    document.getElementById('monthSales').textContent = `$${salesMetrics.month_sales.toFixed(2)}`;
    document.getElementById('totalOrders').textContent = salesMetrics.total_orders;
    document.getElementById('averageOrder').textContent = `$${salesMetrics.average_order.toFixed(2)}`;
}

function populateSalesTable() {
    const tbody = document.getElementById('salesTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    sales.forEach(sale => {
        const customer = customers.find(c => c.id === sale.customer_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sale.id}</td>
            <td>${sale.date}</td>
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td>${sale.items.length} items</td>
            <td>$${sale.total.toFixed(2)}</td>
            <td><span class="badge bg-${sale.status === 'Completed' ? 'success' : 'warning'}">${sale.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewSale('${sale.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSale('${sale.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function searchSales() {
    const searchTerm = document.getElementById('saleSearch').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredSales = sales.filter(sale => {
        const customer = customers.find(c => c.id === sale.customer_id);
        const matchesSearch = sale.id.toLowerCase().includes(searchTerm) ||
                            (customer && customer.name.toLowerCase().includes(searchTerm));
        const matchesDate = !dateFilter || sale.date === dateFilter;
        const matchesStatus = statusFilter === 'all' || sale.status === statusFilter;
        return matchesSearch && matchesDate && matchesStatus;
    });
    
    const tbody = document.getElementById('salesTableBody');
    tbody.innerHTML = '';
    filteredSales.forEach(sale => {
        const customer = customers.find(c => c.id === sale.customer_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sale.id}</td>
            <td>${sale.date}</td>
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td>${sale.items.length} items</td>
            <td>$${sale.total.toFixed(2)}</td>
            <td><span class="badge bg-${sale.status === 'Completed' ? 'success' : 'warning'}">${sale.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewSale('${sale.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSale('${sale.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function addNewSale() {
    const customerSelect = document.getElementById('customerSelect');
    const dateInput = document.getElementById('saleDate');
    const statusSelect = document.getElementById('saleStatus');
    
    // Create new sale object
    const newSale = {
        id: `S${String(sales.length + 1).padStart(3, '0')}`,
        date: dateInput.value,
        customer_id: parseInt(customerSelect.value),
        items: [], // This would be populated from the product list in a real application
        total: 0, // This would be calculated from the items
        status: statusSelect.value
    };
    
    // Add to sales array
    sales.push(newSale);
    
    // Update metrics
    salesMetrics.total_orders++;
    salesMetrics.today_sales += newSale.total;
    salesMetrics.month_sales += newSale.total;
    salesMetrics.average_order = (salesMetrics.month_sales / salesMetrics.total_orders);
    
    // Refresh the table and metrics
    populateSalesMetrics();
    populateSalesTable();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('newSaleModal'));
    modal.hide();
}

function viewSale(saleId) {
    const sale = sales.find(s => s.id === saleId);
    if (!sale) return;
    
    const customer = customers.find(c => c.id === sale.customer_id);
    const modal = new bootstrap.Modal(document.getElementById('viewSaleModal'));
    
    // Populate modal with sale details
    document.getElementById('viewSaleId').textContent = sale.id;
    document.getElementById('viewSaleDate').textContent = sale.date;
    document.getElementById('viewSaleCustomer').textContent = customer ? customer.name : 'Unknown';
    document.getElementById('viewSaleStatus').textContent = sale.status;
    document.getElementById('viewSaleTotal').textContent = `$${sale.total.toFixed(2)}`;
    
    // Populate items table
    const tbody = document.getElementById('viewSaleItemsBody');
    tbody.innerHTML = '';
    sale.items.forEach(item => {
        const product = products.find(p => p.id === item.product_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product ? product.name : 'Unknown'}</td>
            <td>${item.quantity}</td>
            <td>$${item.price.toFixed(2)}</td>
            <td>$${(item.quantity * item.price).toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
    
    modal.show();
}

function deleteSale(saleId) {
    if (confirm('Are you sure you want to delete this sale?')) {
        const index = sales.findIndex(s => s.id === saleId);
        if (index !== -1) {
            const sale = sales[index];
            sales.splice(index, 1);
            
            // Update metrics
            salesMetrics.total_orders--;
            salesMetrics.today_sales -= sale.total;
            salesMetrics.month_sales -= sale.total;
            salesMetrics.average_order = salesMetrics.total_orders > 0 ? 
                (salesMetrics.month_sales / salesMetrics.total_orders) : 0;
            
            // Refresh the table and metrics
            populateSalesMetrics();
            populateSalesTable();
        }
    }
}

// Suppliers functions
function populateSuppliersTable() {
    const tbody = document.getElementById('suppliersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    suppliers.forEach(supplier => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${supplier.name}</td>
            <td>${supplier.category}</td>
            <td>${supplier.contact_person}</td>
            <td>${supplier.email}</td>
            <td>${supplier.phone}</td>
            <td><span class="badge bg-${supplier.status === 'Active' ? 'success' : 'danger'}">${supplier.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewSupplier(${supplier.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${supplier.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function searchSuppliers() {
    const searchTerm = document.getElementById('supplierSearch').value.toLowerCase();
    const categoryFilter = document.getElementById('categoryFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredSuppliers = suppliers.filter(supplier => {
        const matchesSearch = supplier.name.toLowerCase().includes(searchTerm) ||
                            supplier.contact_person.toLowerCase().includes(searchTerm) ||
                            supplier.email.toLowerCase().includes(searchTerm);
        const matchesCategory = categoryFilter === 'all' || supplier.category === categoryFilter;
        const matchesStatus = statusFilter === 'all' || supplier.status === statusFilter;
        return matchesSearch && matchesCategory && matchesStatus;
    });
    
    const tbody = document.getElementById('suppliersTableBody');
    tbody.innerHTML = '';
    filteredSuppliers.forEach(supplier => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${supplier.name}</td>
            <td>${supplier.category}</td>
            <td>${supplier.contact_person}</td>
            <td>${supplier.email}</td>
            <td>${supplier.phone}</td>
            <td><span class="badge bg-${supplier.status === 'Active' ? 'success' : 'danger'}">${supplier.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewSupplier(${supplier.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteSupplier(${supplier.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function addNewSupplier() {
    const name = document.getElementById('supplierName').value;
    const category = document.getElementById('supplierCategory').value;
    const contactPerson = document.getElementById('contactPerson').value;
    const email = document.getElementById('supplierEmail').value;
    const phone = document.getElementById('supplierPhone').value;
    const status = document.getElementById('supplierStatus').value;
    const address = document.getElementById('supplierAddress').value;
    const notes = document.getElementById('supplierNotes').value;
    
    const newSupplier = {
        id: suppliers.length + 1,
        name,
        category,
        contact_person: contactPerson,
        email,
        phone,
        status,
        address,
        notes
    };
    
    suppliers.push(newSupplier);
    populateSuppliersTable();
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('addSupplierModal'));
    modal.hide();
    
    // Reset form
    document.getElementById('addSupplierForm').reset();
}

function viewSupplier(supplierId) {
    const supplier = suppliers.find(s => s.id === supplierId);
    if (!supplier) return;
    
    // Populate modal with supplier details
    document.getElementById('viewSupplierName').textContent = supplier.name;
    document.getElementById('viewSupplierCategory').textContent = supplier.category;
    document.getElementById('viewContactPerson').textContent = supplier.contact_person;
    document.getElementById('viewSupplierStatus').textContent = supplier.status;
    document.getElementById('viewSupplierEmail').textContent = supplier.email;
    document.getElementById('viewSupplierPhone').textContent = supplier.phone;
    document.getElementById('viewSupplierAddress').textContent = supplier.address;
    document.getElementById('viewSupplierNotes').textContent = supplier.notes || 'No notes available';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewSupplierModal'));
    modal.show();
}

function deleteSupplier(supplierId) {
    if (confirm('Are you sure you want to delete this supplier?')) {
        const index = suppliers.findIndex(s => s.id === supplierId);
        if (index !== -1) {
            suppliers.splice(index, 1);
            populateSuppliersTable();
        }
    }
}

// Purchase Order functions
function populatePOMetrics() {
    document.getElementById('pendingOrders').textContent = poMetrics.pending_orders;
    document.getElementById('monthOrders').textContent = `$${poMetrics.month_orders.toFixed(2)}`;
    document.getElementById('totalOrders').textContent = poMetrics.total_orders;
    document.getElementById('avgOrder').textContent = `$${poMetrics.average_order.toFixed(2)}`;
}

function populatePurchaseOrdersTable() {
    const tbody = document.getElementById('purchaseOrdersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    purchaseOrders.forEach(po => {
        const supplier = suppliers.find(s => s.id === po.supplier_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${po.id}</td>
            <td>${po.date}</td>
            <td>${supplier ? supplier.name : 'Unknown'}</td>
            <td>${po.items.length} items</td>
            <td>$${po.total.toFixed(2)}</td>
            <td><span class="badge bg-${getStatusBadgeColor(po.status)}">${po.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewPurchaseOrder('${po.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deletePurchaseOrder('${po.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getStatusBadgeColor(status) {
    switch(status) {
        case 'Pending': return 'warning';
        case 'Approved': return 'info';
        case 'Received': return 'success';
        case 'Cancelled': return 'danger';
        default: return 'secondary';
    }
}

function searchPurchaseOrders() {
    const searchTerm = document.getElementById('poSearch').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredPOs = purchaseOrders.filter(po => {
        const supplier = suppliers.find(s => s.id === po.supplier_id);
        const matchesSearch = po.id.toLowerCase().includes(searchTerm) ||
                            (supplier && supplier.name.toLowerCase().includes(searchTerm));
        const matchesDate = !dateFilter || po.date === dateFilter;
        const matchesStatus = statusFilter === 'all' || po.status === statusFilter;
        return matchesSearch && matchesDate && matchesStatus;
    });
    
    const tbody = document.getElementById('purchaseOrdersTableBody');
    tbody.innerHTML = '';
    filteredPOs.forEach(po => {
        const supplier = suppliers.find(s => s.id === po.supplier_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${po.id}</td>
            <td>${po.date}</td>
            <td>${supplier ? supplier.name : 'Unknown'}</td>
            <td>${po.items.length} items</td>
            <td>$${po.total.toFixed(2)}</td>
            <td><span class="badge bg-${getStatusBadgeColor(po.status)}">${po.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewPurchaseOrder('${po.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deletePurchaseOrder('${po.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populateSupplierSelect() {
    const select = document.getElementById('supplierSelect');
    if (!select) return;

    select.innerHTML = '<option value="">Select Supplier</option>';
    suppliers.forEach(supplier => {
        if (supplier.status === 'Active') {
            const option = document.createElement('option');
            option.value = supplier.id;
            option.textContent = supplier.name;
            select.appendChild(option);
        }
    });
}

function populateProductSelects() {
    const selects = document.querySelectorAll('.product-select');
    selects.forEach(select => {
        select.innerHTML = '<option value="">Select Product</option>';
        products.forEach(product => {
            const option = document.createElement('option');
            option.value = product.id;
            option.textContent = product.name;
            option.dataset.price = product.price;
            select.appendChild(option);
        });
    });
}

function addProductRow() {
    const productList = document.getElementById('productList');
    const newRow = document.createElement('div');
    newRow.className = 'row mb-2 product-row';
    newRow.innerHTML = `
        <div class="col-md-5">
            <select class="form-select product-select" required>
                <option value="">Select Product</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" class="form-control quantity-input" 
                   placeholder="Qty" min="1" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control price-display" readonly>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-product">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    productList.appendChild(newRow);
    populateProductSelects();
}

function calculateTotal() {
    const rows = document.querySelectorAll('.product-row');
    let total = 0;
    
    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const quantity = row.querySelector('.quantity-input').value;
        const price = select.options[select.selectedIndex]?.dataset.price || 0;
        
        if (select.value && quantity) {
            total += price * quantity;
        }
    });
    
    document.getElementById('poTotal').value = `$${total.toFixed(2)}`;
    return total;
}

function addNewPurchaseOrder() {
    const supplierId = document.getElementById('supplierSelect').value;
    const date = document.getElementById('poDate').value;
    const status = document.getElementById('poStatus').value;
    const notes = document.getElementById('poNotes').value;
    
    const items = [];
    const rows = document.querySelectorAll('.product-row');
    rows.forEach(row => {
        const select = row.querySelector('.product-select');
        const quantity = row.querySelector('.quantity-input').value;
        const price = select.options[select.selectedIndex]?.dataset.price;
        
        if (select.value && quantity) {
            items.push({
                product_id: parseInt(select.value),
                quantity: parseInt(quantity),
                price: parseFloat(price)
            });
        }
    });
    
    const total = calculateTotal();
    
    const newPO = {
        id: `PO${String(purchaseOrders.length + 1).padStart(3, '0')}`,
        date,
        supplier_id: parseInt(supplierId),
        items,
        total,
        status,
        notes
    };
    
    purchaseOrders.push(newPO);
    
    // Update metrics
    poMetrics.total_orders++;
    poMetrics.month_orders += total;
    poMetrics.average_order = poMetrics.month_orders / poMetrics.total_orders;
    if (status === 'Pending') {
        poMetrics.pending_orders++;
    }
    
    // Refresh the table and metrics
    populatePOMetrics();
    populatePurchaseOrdersTable();
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('newPurchaseOrderModal'));
    modal.hide();
    document.getElementById('newPurchaseOrderForm').reset();
}

function viewPurchaseOrder(poId) {
    const po = purchaseOrders.find(p => p.id === poId);
    if (!po) return;
    
    const supplier = suppliers.find(s => s.id === po.supplier_id);
    
    // Populate modal with PO details
    document.getElementById('viewPONumber').textContent = po.id;
    document.getElementById('viewPODate').textContent = po.date;
    document.getElementById('viewPOSupplier').textContent = supplier ? supplier.name : 'Unknown';
    document.getElementById('viewPOStatus').textContent = po.status;
    document.getElementById('viewPOTotal').textContent = `$${po.total.toFixed(2)}`;
    document.getElementById('viewPONotes').textContent = po.notes || 'No notes available';
    
    // Populate items table
    const tbody = document.getElementById('viewPOItems');
    tbody.innerHTML = '';
    po.items.forEach(item => {
        const product = products.find(p => p.id === item.product_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product ? product.name : 'Unknown'}</td>
            <td>${item.quantity}</td>
            <td>$${item.price.toFixed(2)}</td>
            <td>$${(item.quantity * item.price).toFixed(2)}</td>
        `;
        tbody.appendChild(row);
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewPurchaseOrderModal'));
    modal.show();
}

function deletePurchaseOrder(poId) {
    if (confirm('Are you sure you want to delete this purchase order?')) {
        const index = purchaseOrders.findIndex(p => p.id === poId);
        if (index !== -1) {
            const po = purchaseOrders[index];
            purchaseOrders.splice(index, 1);
            
            // Update metrics
            poMetrics.total_orders--;
            poMetrics.month_orders -= po.total;
            poMetrics.average_order = poMetrics.total_orders > 0 ? 
                (poMetrics.month_orders / poMetrics.total_orders) : 0;
            if (po.status === 'Pending') {
                poMetrics.pending_orders--;
            }
            
            // Refresh the table and metrics
            populatePOMetrics();
            populatePurchaseOrdersTable();
        }
    }
}

// Promotion functions
function populatePromotionMetrics() {
    document.getElementById('activePromotions').textContent = promotionMetrics.active_promotions;
    document.getElementById('totalRedemptions').textContent = promotionMetrics.total_redemptions;
    document.getElementById('totalDiscount').textContent = `$${promotionMetrics.total_discount.toFixed(2)}`;
    document.getElementById('avgDiscount').textContent = `$${promotionMetrics.average_discount.toFixed(2)}`;
}

function populatePromotionsTable() {
    const tbody = document.getElementById('promotionsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    promotions.forEach(promo => {
        const row = document.createElement('tr');
        const discountDisplay = promo.discount_type === 'Percentage' ? 
            `${promo.discount_value}%` : `$${promo.discount_value.toFixed(2)}`;
        
        row.innerHTML = `
            <td>${promo.name}</td>
            <td>${promo.type}</td>
            <td>${discountDisplay}</td>
            <td>${promo.start_date}</td>
            <td>${promo.end_date}</td>
            <td><span class="badge bg-${getPromotionStatusBadgeColor(promo.status)}">${promo.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewPromotion('${promo.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deletePromotion('${promo.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getPromotionStatusBadgeColor(status) {
    switch(status) {
        case 'Active': return 'success';
        case 'Scheduled': return 'info';
        case 'Expired': return 'danger';
        default: return 'secondary';
    }
}

function searchPromotions() {
    const searchTerm = document.getElementById('promotionSearch').value.toLowerCase();
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredPromos = promotions.filter(promo => {
        const matchesSearch = promo.name.toLowerCase().includes(searchTerm) ||
                            promo.description.toLowerCase().includes(searchTerm);
        const matchesType = typeFilter === 'all' || promo.type === typeFilter;
        const matchesStatus = statusFilter === 'all' || promo.status === statusFilter;
        return matchesSearch && matchesType && matchesStatus;
    });
    
    const tbody = document.getElementById('promotionsTableBody');
    tbody.innerHTML = '';
    filteredPromos.forEach(promo => {
        const row = document.createElement('tr');
        const discountDisplay = promo.discount_type === 'Percentage' ? 
            `${promo.discount_value}%` : `$${promo.discount_value.toFixed(2)}`;
        
        row.innerHTML = `
            <td>${promo.name}</td>
            <td>${promo.type}</td>
            <td>${discountDisplay}</td>
            <td>${promo.start_date}</td>
            <td>${promo.end_date}</td>
            <td><span class="badge bg-${getPromotionStatusBadgeColor(promo.status)}">${promo.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewPromotion('${promo.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deletePromotion('${promo.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function populatePromotionProducts() {
    const select = document.getElementById('promotionProducts');
    if (!select) return;

    select.innerHTML = '';
    products.forEach(product => {
        const option = document.createElement('option');
        option.value = product.id;
        option.textContent = product.name;
        select.appendChild(option);
    });
}

function updateDiscountDisplay() {
    const discountType = document.getElementById('discountType').value;
    const prefix = document.getElementById('discountPrefix');
    const suffix = document.getElementById('discountSuffix');
    
    if (discountType === 'Percentage') {
        prefix.style.display = 'none';
        suffix.style.display = 'block';
    } else {
        prefix.style.display = 'block';
        suffix.style.display = 'none';
    }
}

function addNewPromotion() {
    const name = document.getElementById('promotionName').value;
    const type = document.getElementById('promotionType').value;
    const discountType = document.getElementById('discountType').value;
    const discountValue = parseFloat(document.getElementById('discountValue').value);
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const description = document.getElementById('promotionDescription').value;
    const minimumPurchase = document.getElementById('minimumPurchase').checked;
    const minimumAmount = minimumPurchase ? 
        parseFloat(document.getElementById('minimumAmount').value) : 0;
    
    const productSelect = document.getElementById('promotionProducts');
    const selectedProducts = Array.from(productSelect.selectedOptions).map(option => parseInt(option.value));
    
    const newPromo = {
        id: `PROMO${String(promotions.length + 1).padStart(3, '0')}`,
        name,
        type,
        discount_type: discountType,
        discount_value: discountValue,
        start_date: startDate,
        end_date: endDate,
        status: new Date(startDate) <= new Date() ? 'Active' : 'Scheduled',
        products: selectedProducts,
        description,
        minimum_purchase: minimumAmount,
        redemptions: 0,
        total_discount: 0,
        avg_discount: 0
    };
    
    promotions.push(newPromo);
    
    // Update metrics
    if (newPromo.status === 'Active') {
        promotionMetrics.active_promotions++;
    }
    
    // Refresh the table and metrics
    populatePromotionMetrics();
    populatePromotionsTable();
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('newPromotionModal'));
    modal.hide();
    document.getElementById('newPromotionForm').reset();
}

function viewPromotion(promoId) {
    const promo = promotions.find(p => p.id === promoId);
    if (!promo) return;
    
    // Populate modal with promotion details
    document.getElementById('viewPromotionName').textContent = promo.name;
    document.getElementById('viewPromotionType').textContent = promo.type;
    document.getElementById('viewPromotionDiscount').textContent = 
        promo.discount_type === 'Percentage' ? 
        `${promo.discount_value}%` : `$${promo.discount_value.toFixed(2)}`;
    document.getElementById('viewPromotionStatus').textContent = promo.status;
    document.getElementById('viewStartDate').textContent = promo.start_date;
    document.getElementById('viewEndDate').textContent = promo.end_date;
    document.getElementById('viewPromotionDescription').textContent = promo.description;
    
    // Populate products list
    const productsList = document.getElementById('viewPromotionProducts');
    productsList.innerHTML = '';
    promo.products.forEach(productId => {
        const product = products.find(p => p.id === productId);
        if (product) {
            const li = document.createElement('li');
            li.className = 'list-group-item';
            li.textContent = product.name;
            productsList.appendChild(li);
        }
    });
    
    // Populate statistics
    document.getElementById('viewTotalRedemptions').textContent = promo.redemptions;
    document.getElementById('viewTotalDiscount').textContent = `$${promo.total_discount.toFixed(2)}`;
    document.getElementById('viewAvgDiscount').textContent = `$${promo.avg_discount.toFixed(2)}`;
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewPromotionModal'));
    modal.show();
}

function deletePromotion(promoId) {
    if (confirm('Are you sure you want to delete this promotion?')) {
        const index = promotions.findIndex(p => p.id === promoId);
        if (index !== -1) {
            const promo = promotions[index];
            promotions.splice(index, 1);
            
            // Update metrics
            if (promo.status === 'Active') {
                promotionMetrics.active_promotions--;
            }
            promotionMetrics.total_redemptions -= promo.redemptions;
            promotionMetrics.total_discount -= promo.total_discount;
            promotionMetrics.average_discount = promotionMetrics.total_redemptions > 0 ? 
                (promotionMetrics.total_discount / promotionMetrics.total_redemptions) : 0;
            
            // Refresh the table and metrics
            populatePromotionMetrics();
            populatePromotionsTable();
        }
    }
}

// Loyalty Program functions
function populateLoyaltyMetrics() {
    document.getElementById('totalMembers').textContent = loyaltyMetrics.total_members;
    document.getElementById('totalPoints').textContent = loyaltyMetrics.total_points;
    document.getElementById('pointsRedeemed').textContent = loyaltyMetrics.points_redeemed;
    document.getElementById('activeRewards').textContent = loyaltyMetrics.active_rewards;
}

function populateMembersTable() {
    const tbody = document.getElementById('membersTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    loyaltyMembers.forEach(member => {
        const customer = customers.find(c => c.id === member.customer_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td><span class="badge bg-${getTierBadgeColor(member.tier)}">${member.tier}</span></td>
            <td>${member.points}</td>
            <td>${member.join_date}</td>
            <td><span class="badge bg-${member.status === 'Active' ? 'success' : 'danger'}">${member.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewMember(${member.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getTierBadgeColor(tier) {
    switch(tier) {
        case 'Bronze': return 'warning';
        case 'Silver': return 'secondary';
        case 'Gold': return 'success';
        case 'Platinum': return 'primary';
        default: return 'secondary';
    }
}

function populateRewardsTable() {
    const tbody = document.getElementById('rewardsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    loyaltyRewards.forEach(reward => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${reward.name}</td>
            <td>${reward.points_required}</td>
            <td>$${reward.value.toFixed(2)}</td>
            <td>${reward.end_date}</td>
            <td><span class="badge bg-${reward.status === 'Active' ? 'success' : 'danger'}">${reward.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewReward('${reward.id}')">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteReward('${reward.id}')">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function searchMembers() {
    const searchTerm = document.getElementById('memberSearch').value.toLowerCase();
    const tierFilter = document.getElementById('tierFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    const filteredMembers = loyaltyMembers.filter(member => {
        const customer = customers.find(c => c.id === member.customer_id);
        const matchesSearch = customer && customer.name.toLowerCase().includes(searchTerm);
        const matchesTier = tierFilter === 'all' || member.tier === tierFilter;
        const matchesStatus = statusFilter === 'all' || member.status === statusFilter;
        return matchesSearch && matchesTier && matchesStatus;
    });
    
    const tbody = document.getElementById('membersTableBody');
    tbody.innerHTML = '';
    filteredMembers.forEach(member => {
        const customer = customers.find(c => c.id === member.customer_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td><span class="badge bg-${getTierBadgeColor(member.tier)}">${member.tier}</span></td>
            <td>${member.points}</td>
            <td>${member.join_date}</td>
            <td><span class="badge bg-${member.status === 'Active' ? 'success' : 'danger'}">${member.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewMember(${member.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="deleteMember(${member.id})">Delete</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function viewMember(memberId) {
    const member = loyaltyMembers.find(m => m.id === memberId);
    if (!member) return;
    
    const customer = customers.find(c => c.id === member.customer_id);
    
    // Populate modal with member details
    document.getElementById('viewMemberName').textContent = customer ? customer.name : 'Unknown';
    document.getElementById('viewMemberId').textContent = member.id;
    document.getElementById('viewMemberTier').textContent = member.tier;
    document.getElementById('viewMemberPoints').textContent = member.points;
    document.getElementById('viewJoinDate').textContent = member.join_date;
    document.getElementById('viewMemberStatus').textContent = member.status;
    
    // Populate points history
    const pointsHistory = document.getElementById('viewPointsHistory');
    pointsHistory.innerHTML = '';
    member.points_history.forEach(history => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${history.date}</td>
            <td>${history.transaction}</td>
            <td class="${history.points > 0 ? 'text-success' : 'text-danger'}">
                ${history.points > 0 ? '+' : ''}${history.points}
            </td>
        `;
        pointsHistory.appendChild(row);
    });
    
    // Populate redemptions
    const redemptions = document.getElementById('viewRedemptions');
    redemptions.innerHTML = '';
    member.redemptions.forEach(redemption => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${redemption.date}</td>
            <td>${redemption.reward}</td>
            <td class="text-danger">-${redemption.points}</td>
        `;
        redemptions.appendChild(row);
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('viewMemberModal'));
    modal.show();
}

function deleteMember(memberId) {
    if (confirm('Are you sure you want to delete this member?')) {
        const index = loyaltyMembers.findIndex(m => m.id === memberId);
        if (index !== -1) {
            const member = loyaltyMembers[index];
            loyaltyMembers.splice(index, 1);
            
            // Update metrics
            loyaltyMetrics.total_members--;
            loyaltyMetrics.total_points -= member.points;
            
            // Refresh the table and metrics
            populateLoyaltyMetrics();
            populateMembersTable();
        }
    }
}

function addNewReward() {
    const name = document.getElementById('rewardName').value;
    const type = document.getElementById('rewardType').value;
    const pointsRequired = parseInt(document.getElementById('pointsRequired').value);
    const value = parseFloat(document.getElementById('rewardValue').value);
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    const description = document.getElementById('rewardDescription').value;
    const tierRestriction = document.getElementById('tierRestriction').value;
    
    const newReward = {
        id: `RWD${String(loyaltyRewards.length + 1).padStart(3, '0')}`,
        name,
        type,
        points_required: pointsRequired,
        value,
        start_date: startDate,
        end_date: endDate,
        status: 'Active',
        description,
        tier_restriction: tierRestriction
    };
    
    loyaltyRewards.push(newReward);
    
    // Update metrics
    loyaltyMetrics.active_rewards++;
    
    // Refresh the table and metrics
    populateLoyaltyMetrics();
    populateRewardsTable();
    
    // Close modal and reset form
    const modal = bootstrap.Modal.getInstance(document.getElementById('newRewardModal'));
    modal.hide();
    document.getElementById('newRewardForm').reset();
}

function viewReward(rewardId) {
    const reward = loyaltyRewards.find(r => r.id === rewardId);
    if (!reward) return;
    
    // Show reward details in a modal or alert
    alert(`
        Reward: ${reward.name}
        Type: ${reward.type}
        Points Required: ${reward.points_required}
        Value: $${reward.value.toFixed(2)}
        Description: ${reward.description}
        Tier Restriction: ${reward.tier_restriction || 'None'}
        Status: ${reward.status}
        Valid: ${reward.start_date} to ${reward.end_date}
    `);
}

function deleteReward(rewardId) {
    if (confirm('Are you sure you want to delete this reward?')) {
        const index = loyaltyRewards.findIndex(r => r.id === rewardId);
        if (index !== -1) {
            const reward = loyaltyRewards[index];
            loyaltyRewards.splice(index, 1);
            
            // Update metrics
            if (reward.status === 'Active') {
                loyaltyMetrics.active_rewards--;
            }
            
            // Refresh the table and metrics
            populateLoyaltyMetrics();
            populateRewardsTable();
        }
    }
}

// Inventory Alerts functions
function populateAlertMetrics() {
    document.getElementById('lowStockCount').textContent = alertMetrics.low_stock_count;
    document.getElementById('outOfStockCount').textContent = alertMetrics.out_of_stock_count;
    document.getElementById('expiringCount').textContent = alertMetrics.expiring_count;
    document.getElementById('totalAlerts').textContent = alertMetrics.total_alerts;
}

function populateAlertsTable() {
    const tbody = document.getElementById('alertsTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    inventoryAlerts.forEach(alert => {
        const product = products.find(p => p.id === alert.product_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product ? product.name : 'Unknown'}</td>
            <td><span class="badge bg-${getAlertTypeBadgeColor(alert.alert_type)}">${formatAlertType(alert.alert_type)}</span></td>
            <td>${alert.current_stock}</td>
            <td>${alert.threshold}</td>
            <td><span class="badge bg-${getPriorityBadgeColor(alert.priority)}">${alert.priority}</span></td>
            <td>${alert.last_updated}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewAlert(${alert.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="dismissAlert(${alert.id})">Dismiss</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getAlertTypeBadgeColor(type) {
    switch(type) {
        case 'low_stock': return 'warning';
        case 'out_of_stock': return 'danger';
        case 'expiring': return 'info';
        default: return 'secondary';
    }
}

function getPriorityBadgeColor(priority) {
    switch(priority) {
        case 'high': return 'danger';
        case 'medium': return 'warning';
        case 'low': return 'info';
        default: return 'secondary';
    }
}

function formatAlertType(type) {
    return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
}

function searchAlerts() {
    const searchTerm = document.getElementById('alertSearch').value.toLowerCase();
    const typeFilter = document.getElementById('alertTypeFilter').value;
    const priorityFilter = document.getElementById('priorityFilter').value;
    
    const filteredAlerts = inventoryAlerts.filter(alert => {
        const product = products.find(p => p.id === alert.product_id);
        const matchesSearch = product && product.name.toLowerCase().includes(searchTerm);
        const matchesType = typeFilter === 'all' || alert.alert_type === typeFilter;
        const matchesPriority = priorityFilter === 'all' || alert.priority === priorityFilter;
        return matchesSearch && matchesType && matchesPriority;
    });
    
    const tbody = document.getElementById('alertsTableBody');
    tbody.innerHTML = '';
    filteredAlerts.forEach(alert => {
        const product = products.find(p => p.id === alert.product_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${product ? product.name : 'Unknown'}</td>
            <td><span class="badge bg-${getAlertTypeBadgeColor(alert.alert_type)}">${formatAlertType(alert.alert_type)}</span></td>
            <td>${alert.current_stock}</td>
            <td>${alert.threshold}</td>
            <td><span class="badge bg-${getPriorityBadgeColor(alert.priority)}">${alert.priority}</span></td>
            <td>${alert.last_updated}</td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewAlert(${alert.id})">View</button>
                <button class="btn btn-sm btn-danger" onclick="dismissAlert(${alert.id})">Dismiss</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function viewAlert(alertId) {
    const alert = inventoryAlerts.find(a => a.id === alertId);
    if (!alert) return;
    
    const product = products.find(p => p.id === alert.product_id);
    
    // Show alert details in a modal or alert
    alert(`
        Product: ${product ? product.name : 'Unknown'}
        Alert Type: ${formatAlertType(alert.alert_type)}
        Current Stock: ${alert.current_stock}
        Threshold: ${alert.threshold}
        Priority: ${alert.priority}
        Last Updated: ${alert.last_updated}
        Status: ${alert.status}
    `);
}

function dismissAlert(alertId) {
    if (confirm('Are you sure you want to dismiss this alert?')) {
        const index = inventoryAlerts.findIndex(a => a.id === alertId);
        if (index !== -1) {
            const alert = inventoryAlerts[index];
            inventoryAlerts.splice(index, 1);
            
            // Update metrics
            switch(alert.alert_type) {
                case 'low_stock':
                    alertMetrics.low_stock_count--;
                    break;
                case 'out_of_stock':
                    alertMetrics.out_of_stock_count--;
                    break;
                case 'expiring':
                    alertMetrics.expiring_count--;
                    break;
            }
            alertMetrics.total_alerts--;
            
            // Refresh the table and metrics
            populateAlertMetrics();
            populateAlertsTable();
        }
    }
}

function loadAlertSettings() {
    document.getElementById('lowStockThreshold').value = alertSettings.low_stock_threshold;
    document.getElementById('outOfStockThreshold').value = alertSettings.out_of_stock_threshold;
    document.getElementById('expirationDays').value = alertSettings.expiration_days;
    document.getElementById('emailNotifications').checked = alertSettings.notifications.email;
    document.getElementById('smsNotifications').checked = alertSettings.notifications.sms;
    document.getElementById('dashboardNotifications').checked = alertSettings.notifications.dashboard;
    document.getElementById('alertFrequency').value = alertSettings.frequency;
}

function saveAlertSettings() {
    const lowStockThreshold = parseInt(document.getElementById('lowStockThreshold').value);
    const outOfStockThreshold = parseInt(document.getElementById('outOfStockThreshold').value);
    const expirationDays = parseInt(document.getElementById('expirationDays').value);
    const emailNotifications = document.getElementById('emailNotifications').checked;
    const smsNotifications = document.getElementById('smsNotifications').checked;
    const dashboardNotifications = document.getElementById('dashboardNotifications').checked;
    const frequency = document.getElementById('alertFrequency').value;
    
    alertSettings = {
        low_stock_threshold: lowStockThreshold,
        out_of_stock_threshold: outOfStockThreshold,
        expiration_days: expirationDays,
        notifications: {
            email: emailNotifications,
            sms: smsNotifications,
            dashboard: dashboardNotifications
        },
        frequency: frequency
    };
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('alertSettingsModal'));
    modal.hide();
    
    // Show success message
    alert('Alert settings saved successfully!');
}

// Report functions
function generateReport() {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    let startDate, endDate;

    if (dateRange === 'custom') {
        startDate = document.getElementById('startDate').value;
        endDate = document.getElementById('endDate').value;
    } else {
        const dates = getDateRange(dateRange);
        startDate = dates.start;
        endDate = dates.end;
    }

    switch(reportType) {
        case 'sales':
            generateSalesReport(startDate, endDate);
            break;
        case 'inventory':
            generateInventoryReport(startDate, endDate);
            break;
        case 'customer':
            generateCustomerReport(startDate, endDate);
            break;
        case 'supplier':
            generateSupplierReport(startDate, endDate);
            break;
        case 'promotion':
            generatePromotionReport(startDate, endDate);
            break;
        case 'loyalty':
            generateLoyaltyReport(startDate, endDate);
            break;
    }
}

function getDateRange(range) {
    const today = new Date();
    let start = new Date();
    let end = new Date();

    switch(range) {
        case 'today':
            start.setHours(0, 0, 0, 0);
            end.setHours(23, 59, 59, 999);
            break;
        case 'week':
            start.setDate(today.getDate() - today.getDay());
            end.setDate(start.getDate() + 6);
            break;
        case 'month':
            start.setDate(1);
            end.setMonth(start.getMonth() + 1);
            end.setDate(0);
            break;
        case 'quarter':
            const quarter = Math.floor(today.getMonth() / 3);
            start.setMonth(quarter * 3);
            start.setDate(1);
            end.setMonth((quarter + 1) * 3);
            end.setDate(0);
            break;
        case 'year':
            start.setMonth(0, 1);
            end.setMonth(11, 31);
            break;
    }

    return {
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0]
    };
}

function generateSalesReport(startDate, endDate) {
    // Filter sales within date range
    const filteredSales = sales.filter(sale => 
        sale.date >= startDate && sale.date <= endDate
    );

    // Calculate metrics
    const totalSales = filteredSales.reduce((sum, sale) => sum + sale.total, 0);
    const totalOrders = filteredSales.length;
    const averageOrder = totalOrders > 0 ? totalSales / totalOrders : 0;

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Sales</h5>
                    <p class="card-text">$${totalSales.toFixed(2)}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text">${totalOrders}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Average Order</h5>
                    <p class="card-text">$${averageOrder.toFixed(2)}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Period</h5>
                    <p class="card-text">${startDate} to ${endDate}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart
    const dailySales = {};
    filteredSales.forEach(sale => {
        dailySales[sale.date] = (dailySales[sale.date] || 0) + sale.total;
    });

    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: Object.keys(dailySales),
            datasets: [{
                label: 'Daily Sales',
                data: Object.values(dailySales),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart
    const productSales = {};
    filteredSales.forEach(sale => {
        sale.items.forEach(item => {
            const product = products.find(p => p.id === item.product_id);
            if (product) {
                productSales[product.name] = (productSales[product.name] || 0) + (item.quantity * item.price);
            }
        });
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(productSales),
            datasets: [{
                data: Object.values(productSales),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)',
                    'rgb(153, 102, 255)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Date</th>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    filteredSales.forEach(sale => {
        const customer = customers.find(c => c.id === sale.customer_id);
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sale.date}</td>
            <td>${sale.id}</td>
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td>${sale.items.length} items</td>
            <td>$${sale.total.toFixed(2)}</td>
            <td><span class="badge bg-${sale.status === 'Completed' ? 'success' : 'warning'}">${sale.status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function generateInventoryReport(startDate, endDate) {
    // Calculate metrics
    const totalProducts = products.length;
    const lowStockProducts = products.filter(p => p.stock < 20).length;
    const outOfStockProducts = products.filter(p => p.stock === 0).length;
    const totalValue = products.reduce((sum, p) => sum + (p.stock * p.price), 0);

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Products</h5>
                    <p class="card-text">${totalProducts}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Items</h5>
                    <p class="card-text">${lowStockProducts}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Out of Stock</h5>
                    <p class="card-text">${outOfStockProducts}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Value</h5>
                    <p class="card-text">$${totalValue.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart (stock levels over time)
    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: products.map(p => p.name),
            datasets: [{
                label: 'Current Stock',
                data: products.map(p => p.stock),
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart (by category)
    const categoryDistribution = {};
    products.forEach(product => {
        categoryDistribution[product.category] = (categoryDistribution[product.category] || 0) + 1;
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(categoryDistribution),
            datasets: [{
                data: Object.values(categoryDistribution),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Current Stock</th>
            <th>Price</th>
            <th>Value</th>
            <th>Status</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    products.forEach(product => {
        const row = document.createElement('tr');
        const value = product.stock * product.price;
        const status = product.stock === 0 ? 'Out of Stock' : 
                      product.stock < 20 ? 'Low Stock' : 'In Stock';
        const statusClass = product.stock === 0 ? 'danger' : 
                          product.stock < 20 ? 'warning' : 'success';
        
        row.innerHTML = `
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>${product.stock}</td>
            <td>$${product.price.toFixed(2)}</td>
            <td>$${value.toFixed(2)}</td>
            <td><span class="badge bg-${statusClass}">${status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function generateCustomerReport(startDate, endDate) {
    // Filter sales within date range
    const filteredSales = sales.filter(sale => 
        sale.date >= startDate && sale.date <= endDate
    );

    // Calculate metrics
    const totalCustomers = customers.length;
    const activeCustomers = new Set(filteredSales.map(sale => sale.customer_id)).size;
    const totalRevenue = filteredSales.reduce((sum, sale) => sum + sale.total, 0);
    const averageOrderValue = filteredSales.length > 0 ? 
        totalRevenue / filteredSales.length : 0;

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Customers</h5>
                    <p class="card-text">${totalCustomers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Customers</h5>
                    <p class="card-text">${activeCustomers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Revenue</h5>
                    <p class="card-text">$${totalRevenue.toFixed(2)}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Average Order Value</h5>
                    <p class="card-text">$${averageOrderValue.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart (customer activity)
    const customerActivity = {};
    filteredSales.forEach(sale => {
        const customer = customers.find(c => c.id === sale.customer_id);
        if (customer) {
            customerActivity[customer.name] = (customerActivity[customer.name] || 0) + sale.total;
        }
    });

    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(customerActivity),
            datasets: [{
                label: 'Customer Spending',
                data: Object.values(customerActivity),
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart (by customer type)
    const typeDistribution = {};
    customers.forEach(customer => {
        typeDistribution[customer.type] = (typeDistribution[customer.type] || 0) + 1;
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(typeDistribution),
            datasets: [{
                data: Object.values(typeDistribution),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Customer</th>
            <th>Type</th>
            <th>Total Orders</th>
            <th>Total Spent</th>
            <th>Loyalty Points</th>
            <th>Status</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    customers.forEach(customer => {
        const customerSales = filteredSales.filter(sale => sale.customer_id === customer.id);
        const totalSpent = customerSales.reduce((sum, sale) => sum + sale.total, 0);
        const member = loyaltyMembers.find(m => m.customer_id === customer.id);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer.name}</td>
            <td>${customer.type}</td>
            <td>${customerSales.length}</td>
            <td>$${totalSpent.toFixed(2)}</td>
            <td>${member ? member.points : 0}</td>
            <td><span class="badge bg-${member ? 'success' : 'secondary'}">${member ? 'Member' : 'Regular'}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function generateSupplierReport(startDate, endDate) {
    // Filter purchase orders within date range
    const filteredPOs = purchaseOrders.filter(po => 
        po.date >= startDate && po.date <= endDate
    );

    // Calculate metrics
    const totalSuppliers = suppliers.length;
    const activeSuppliers = new Set(filteredPOs.map(po => po.supplier_id)).size;
    const totalOrders = filteredPOs.length;
    const totalValue = filteredPOs.reduce((sum, po) => sum + po.total, 0);

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Suppliers</h5>
                    <p class="card-text">${totalSuppliers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Suppliers</h5>
                    <p class="card-text">${activeSuppliers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Orders</h5>
                    <p class="card-text">${totalOrders}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Value</h5>
                    <p class="card-text">$${totalValue.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart (supplier activity)
    const supplierActivity = {};
    filteredPOs.forEach(po => {
        const supplier = suppliers.find(s => s.id === po.supplier_id);
        if (supplier) {
            supplierActivity[supplier.name] = (supplierActivity[supplier.name] || 0) + po.total;
        }
    });

    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: Object.keys(supplierActivity),
            datasets: [{
                label: 'Supplier Orders',
                data: Object.values(supplierActivity),
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart (by category)
    const categoryDistribution = {};
    suppliers.forEach(supplier => {
        categoryDistribution[supplier.category] = (categoryDistribution[supplier.category] || 0) + 1;
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(categoryDistribution),
            datasets: [{
                data: Object.values(categoryDistribution),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Supplier</th>
            <th>Category</th>
            <th>Total Orders</th>
            <th>Total Value</th>
            <th>Status</th>
            <th>Last Order</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    suppliers.forEach(supplier => {
        const supplierPOs = filteredPOs.filter(po => po.supplier_id === supplier.id);
        const totalValue = supplierPOs.reduce((sum, po) => sum + po.total, 0);
        const lastOrder = supplierPOs.length > 0 ? 
            supplierPOs.sort((a, b) => new Date(b.date) - new Date(a.date))[0].date : 'N/A';
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${supplier.name}</td>
            <td>${supplier.category}</td>
            <td>${supplierPOs.length}</td>
            <td>$${totalValue.toFixed(2)}</td>
            <td><span class="badge bg-${supplier.status === 'Active' ? 'success' : 'danger'}">${supplier.status}</span></td>
            <td>${lastOrder}</td>
        `;
        tbody.appendChild(row);
    });
}

function generatePromotionReport(startDate, endDate) {
    // Filter promotions within date range
    const filteredPromos = promotions.filter(promo => 
        promo.start_date <= endDate && promo.end_date >= startDate
    );

    // Calculate metrics
    const totalPromotions = filteredPromos.length;
    const activePromotions = filteredPromos.filter(p => p.status === 'Active').length;
    const totalRedemptions = filteredPromos.reduce((sum, p) => sum + p.redemptions, 0);
    const totalDiscount = filteredPromos.reduce((sum, p) => sum + p.total_discount, 0);

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Promotions</h5>
                    <p class="card-text">${totalPromotions}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Promotions</h5>
                    <p class="card-text">${activePromotions}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Redemptions</h5>
                    <p class="card-text">${totalRedemptions}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Discount</h5>
                    <p class="card-text">$${totalDiscount.toFixed(2)}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart (promotion performance)
    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: filteredPromos.map(p => p.name),
            datasets: [{
                label: 'Redemptions',
                data: filteredPromos.map(p => p.redemptions),
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart (by type)
    const typeDistribution = {};
    filteredPromos.forEach(promo => {
        typeDistribution[promo.type] = (typeDistribution[promo.type] || 0) + 1;
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(typeDistribution),
            datasets: [{
                data: Object.values(typeDistribution),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Promotion</th>
            <th>Type</th>
            <th>Discount</th>
            <th>Redemptions</th>
            <th>Total Discount</th>
            <th>Status</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    filteredPromos.forEach(promo => {
        const discountDisplay = promo.discount_type === 'Percentage' ? 
            `${promo.discount_value}%` : `$${promo.discount_value.toFixed(2)}`;
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${promo.name}</td>
            <td>${promo.type}</td>
            <td>${discountDisplay}</td>
            <td>${promo.redemptions}</td>
            <td>$${promo.total_discount.toFixed(2)}</td>
            <td><span class="badge bg-${getPromotionStatusBadgeColor(promo.status)}">${promo.status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function generateLoyaltyReport(startDate, endDate) {
    // Calculate metrics
    const totalMembers = loyaltyMembers.length;
    const activeMembers = loyaltyMembers.filter(m => m.status === 'Active').length;
    const totalPoints = loyaltyMembers.reduce((sum, m) => sum + m.points, 0);
    const pointsRedeemed = loyaltyMembers.reduce((sum, m) => 
        sum + m.redemptions.reduce((rSum, r) => rSum + r.points, 0), 0);

    // Update summary cards
    document.getElementById('reportSummary').innerHTML = `
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Members</h5>
                    <p class="card-text">${totalMembers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Active Members</h5>
                    <p class="card-text">${activeMembers}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Total Points</h5>
                    <p class="card-text">${totalPoints}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dashboard-card">
                <div class="card-body">
                    <h5 class="card-title">Points Redeemed</h5>
                    <p class="card-text">${pointsRedeemed}</p>
                </div>
            </div>
        </div>
    `;

    // Generate trend chart (points distribution)
    if (trendChart) {
        trendChart.destroy();
    }

    const trendCtx = document.getElementById('trendChart').getContext('2d');
    trendChart = new Chart(trendCtx, {
        type: 'bar',
        data: {
            labels: loyaltyMembers.map(m => {
                const customer = customers.find(c => c.id === m.customer_id);
                return customer ? customer.name : 'Unknown';
            }),
            datasets: [{
                label: 'Points Balance',
                data: loyaltyMembers.map(m => m.points),
                backgroundColor: 'rgb(75, 192, 192)'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Generate distribution chart (by tier)
    const tierDistribution = {};
    loyaltyMembers.forEach(member => {
        tierDistribution[member.tier] = (tierDistribution[member.tier] || 0) + 1;
    });

    if (distributionChart) {
        distributionChart.destroy();
    }

    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    distributionChart = new Chart(distributionCtx, {
        type: 'pie',
        data: {
            labels: Object.keys(tierDistribution),
            datasets: [{
                data: Object.values(tierDistribution),
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });

    // Update detailed table
    document.getElementById('reportTableHead').innerHTML = `
        <tr>
            <th>Member</th>
            <th>Tier</th>
            <th>Points</th>
            <th>Join Date</th>
            <th>Redemptions</th>
            <th>Status</th>
        </tr>
    `;

    const tbody = document.getElementById('reportTableBody');
    tbody.innerHTML = '';
    loyaltyMembers.forEach(member => {
        const customer = customers.find(c => c.id === member.customer_id);
        const totalRedemptions = member.redemptions.reduce((sum, r) => sum + r.points, 0);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${customer ? customer.name : 'Unknown'}</td>
            <td><span class="badge bg-${getTierBadgeColor(member.tier)}">${member.tier}</span></td>
            <td>${member.points}</td>
            <td>${member.join_date}</td>
            <td>${totalRedemptions}</td>
            <td><span class="badge bg-${member.status === 'Active' ? 'success' : 'danger'}">${member.status}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function exportReport(format) {
    const reportType = document.getElementById('reportType').value;
    const dateRange = document.getElementById('dateRange').value;
    let startDate, endDate;

    if (dateRange === 'custom') {
        startDate = document.getElementById('startDate').value;
        endDate = document.getElementById('endDate').value;
    } else {
        const dates = getDateRange(dateRange);
        startDate = dates.start;
        endDate = dates.end;
    }

    // In a real application, this would generate and download the file
    alert(`Exporting ${reportType} report for ${startDate} to ${endDate} in ${format} format`);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', () => {
    loadMockData();

    // Product search functionality
    const productSearch = document.getElementById('productSearch');
    if (productSearch) {
        productSearch.addEventListener('input', (e) => {
            searchProducts(e.target.value);
        });
    }

    // Customer search functionality
    const customerSearch = document.getElementById('customerSearch');
    if (customerSearch) {
        customerSearch.addEventListener('input', (e) => {
            const typeFilter = document.getElementById('customerTypeFilter');
            searchCustomers(e.target.value, typeFilter.value);
        });
    }

    // Customer type filter
    const customerTypeFilter = document.getElementById('customerTypeFilter');
    if (customerTypeFilter) {
        customerTypeFilter.addEventListener('change', (e) => {
            const searchInput = document.getElementById('customerSearch');
            searchCustomers(searchInput.value, e.target.value);
        });
    }

    // Add Product Modal
    const addProductBtn = document.getElementById('addProductBtn');
    if (addProductBtn) {
        addProductBtn.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('addProductModal'));
            modal.show();
        });
    }

    // Add Customer Modal
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', () => {
            const modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
            modal.show();
        });
    }

    // Sale search functionality
    const saleSearch = document.getElementById('saleSearch');
    if (saleSearch) {
        saleSearch.addEventListener('input', searchSales);
    }

    // Date filter
    const dateFilter = document.getElementById('dateFilter');
    if (dateFilter) {
        dateFilter.addEventListener('change', searchSales);
    }

    // Status filter
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', searchSales);
    }

    // New sale form
    const newSaleForm = document.getElementById('newSaleForm');
    if (newSaleForm) {
        newSaleForm.addEventListener('submit', (e) => {
            e.preventDefault();
            addNewSale();
        });
    }

    // Supplier search functionality
    const supplierSearch = document.getElementById('supplierSearch');
    if (supplierSearch) {
        supplierSearch.addEventListener('input', searchSuppliers);
    }
    
    // Supplier category filter
    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', searchSuppliers);
    }
    
    // Supplier status filter
    const supplierStatusFilter = document.getElementById('statusFilter');
    if (supplierStatusFilter) {
        supplierStatusFilter.addEventListener('change', searchSuppliers);
    }
    
    // Add supplier form
    const addSupplierForm = document.getElementById('addSupplierForm');
    if (addSupplierForm) {
        addSupplierForm.addEventListener('submit', (e) => {
            e.preventDefault();
            addNewSupplier();
        });
    }
    
    // Save supplier button
    const saveSupplierBtn = document.getElementById('saveSupplierBtn');
    if (saveSupplierBtn) {
        saveSupplierBtn.addEventListener('click', () => {
            addNewSupplier();
        });
    }

    // Purchase Order search functionality
    const poSearch = document.getElementById('poSearch');
    if (poSearch) {
        poSearch.addEventListener('input', searchPurchaseOrders);
    }

    // Date filter
    const poDateFilter = document.getElementById('dateFilter');
    if (poDateFilter) {
        poDateFilter.addEventListener('change', searchPurchaseOrders);
    }

    // Status filter
    const poStatusFilter = document.getElementById('statusFilter');
    if (poStatusFilter) {
        poStatusFilter.addEventListener('change', searchPurchaseOrders);
    }

    // Add product row button
    const addProductRowBtn = document.getElementById('addProductRow');
    if (addProductRowBtn) {
        addProductRowBtn.addEventListener('click', addProductRow);
    }

    // Product selection change
    document.addEventListener('change', (e) => {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('.product-row');
            const price = e.target.options[e.target.selectedIndex]?.dataset.price || 0;
            row.querySelector('.price-display').value = `$${price}`;
            calculateTotal();
        }
    });

    // Quantity input change
    document.addEventListener('input', (e) => {
        if (e.target.classList.contains('quantity-input')) {
            calculateTotal();
        }
    });

    // Remove product row
    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-product')) {
            const row = e.target.closest('.product-row');
            row.remove();
            calculateTotal();
        }
    });

    // Save purchase order button
    const savePurchaseOrderBtn = document.getElementById('savePurchaseOrderBtn');
    if (savePurchaseOrderBtn) {
        savePurchaseOrderBtn.addEventListener('click', () => {
            addNewPurchaseOrder();
        });
    }

    // Promotion search functionality
    const promotionSearch = document.getElementById('promotionSearch');
    if (promotionSearch) {
        promotionSearch.addEventListener('input', searchPromotions);
    }

    // Promotion type filter
    const typeFilter = document.getElementById('typeFilter');
    if (typeFilter) {
        typeFilter.addEventListener('change', searchPromotions);
    }

    // Promotion status filter
    const promotionStatusFilter = document.getElementById('statusFilter');
    if (promotionStatusFilter) {
        promotionStatusFilter.addEventListener('change', searchPromotions);
    }

    // Discount type change
    const discountType = document.getElementById('discountType');
    if (discountType) {
        discountType.addEventListener('change', updateDiscountDisplay);
    }

    // Minimum purchase checkbox
    const minimumPurchase = document.getElementById('minimumPurchase');
    if (minimumPurchase) {
        minimumPurchase.addEventListener('change', (e) => {
            const minimumAmount = document.getElementById('minimumPurchaseAmount');
            minimumAmount.style.display = e.target.checked ? 'block' : 'none';
        });
    }

    // Save promotion button
    const savePromotionBtn = document.getElementById('savePromotionBtn');
    if (savePromotionBtn) {
        savePromotionBtn.addEventListener('click', addNewPromotion);
    }

    // Member search functionality
    const memberSearch = document.getElementById('memberSearch');
    if (memberSearch) {
        memberSearch.addEventListener('input', searchMembers);
    }

    // Tier filter
    const tierFilter = document.getElementById('tierFilter');
    if (tierFilter) {
        tierFilter.addEventListener('change', searchMembers);
    }

    // Member status filter
    const memberStatusFilter = document.getElementById('statusFilter');
    if (memberStatusFilter) {
        memberStatusFilter.addEventListener('change', searchMembers);
    }

    // Save reward button
    const saveRewardBtn = document.getElementById('saveRewardBtn');
    if (saveRewardBtn) {
        saveRewardBtn.addEventListener('click', addNewReward);
    }

    // Alert search functionality
    const alertSearch = document.getElementById('alertSearch');
    if (alertSearch) {
        alertSearch.addEventListener('input', searchAlerts);
    }

    // Alert type filter
    const alertTypeFilter = document.getElementById('alertTypeFilter');
    if (alertTypeFilter) {
        alertTypeFilter.addEventListener('change', searchAlerts);
    }

    // Priority filter
    const priorityFilter = document.getElementById('priorityFilter');
    if (priorityFilter) {
        priorityFilter.addEventListener('change', searchAlerts);
    }

    // Save settings button
    const saveSettingsBtn = document.getElementById('saveSettingsBtn');
    if (saveSettingsBtn) {
        saveSettingsBtn.addEventListener('click', saveAlertSettings);
    }

    // Date range change
    const dateRange = document.getElementById('dateRange');
    if (dateRange) {
        dateRange.addEventListener('change', (e) => {
            const customDateRange = document.getElementById('customDateRange');
            customDateRange.style.display = e.target.value === 'custom' ? 'block' : 'none';
        });
    }

    // Generate initial report
    if (window.location.pathname.includes('reports.php')) {
        generateReport();
    }
}); 