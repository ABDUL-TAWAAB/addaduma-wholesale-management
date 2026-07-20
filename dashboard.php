<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");


session_start();
if (!isset($_SESSION["staff_id"])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ADDADUMA PRESTIGE ENTERPRISE Management System</title>
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
  <script defer>
const currentUser = {
    id: <?= json_encode($_SESSION["staff_id"]) ?>,
    name: <?= json_encode($_SESSION["staff_name"]) ?>,
    role: <?= json_encode($_SESSION["staff_role"]) ?>
};


</script>
  <div class="app-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="logo">
          <i class="fas fa-store"></i>
        </div>
        <div class="company-info">
          <h2>ADDADUMA</h2>
          <p>PRESTIGE ENTERPRISE</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="nav-link active" data-page="dashboard">
          <i class="fas fa-th-large"></i><span>Dashboard</span>
        </a>
        <a href="#" class="nav-link" data-page="suppliers">
          <i class="fas fa-truck"></i><span>Suppliers</span>
        </a>
        <a href="#" class="nav-link" data-page="categories">
          <i class="fas fa-tags"></i><span>Categories</span>
        </a>
        <a href="#" class="nav-link" data-page="customers">
          <i class="fas fa-users"></i><span>Customers</span>
        </a>
        <a href="#" class="nav-link" data-page="products">
          <i class="fas fa-box"></i><span>Products</span>
        </a>
        <a href="#" class="nav-link" data-page="inventory" id="inventory">
          <i class="fas fa-warehouse"></i><span>Inventory</span>
          <script>
            if (currentUser.role !== "Administrator" && currentUser.role !== "Manager") {
              document.getElementById('inventory').style.display = 'none';
            }
            </script>
        </a>
        <a href="#" class="nav-link" data-page="orders">
          <i class="fas fa-shopping-cart"></i><span>Orders</span>
        </a>
        <a href="#" class="nav-link" data-page="payments">
          <i class="fas fa-money-bill-wave"></i><span>Payments</span>
        </a>
        <a href="#" class="nav-link" data-page="staff" id="staff">
          <i class="fas fa-user-tie"></i><span>Staff</span>
          <script>
            if (currentUser.role !== "Administrator") {
              document.getElementById('staff').style.display = 'none';
            }
            </script>
        </a>
        <a href="#" class="nav-link" data-page="invoices">
          <i class="fas fa-file-invoice"></i><span>Invoices</span>
        </a>
        <a href="#" class="nav-link" data-page="reports" id="reports">
          <i class="fas fa-chart-bar"></i><span>Reports</span>
          <script>
            if (currentUser.role !== "Administrator" && currentUser.role !== "Manager") {
              document.getElementById('reports').style.display = 'none';
            }
          </script>
        </a>
        <a href="" class="nav-link logout" id="logoutBtn">
          <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
      </nav>

      <div class="sidebar-footer">
        <p><i class="fas fa-map-marker-alt"></i> Tanoso, Kumasi</p>
        <p><i class="fas fa-phone"></i> +233 53 1691 093</p>
        <p><i class="fas fa-envelope"></i> info@addaduma.com</p>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
      <header class="header">
        <div class="header-left">
          <button class="menu-toggle" id="menuToggle" aria-label="Toggle menu">
            <i class="fas fa-bars"></i>
          </button>
          <h1 class="page-title" id="pageTitle">Dashboard</h1>
        </div>
        <div class="header-right">
          <button class="notification-btn" aria-label="Notifications">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
          </button>
          <div class="user-profile">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=1e3a5f&color=fff" alt="Admin User">
            <div class="user-info">
              <span class="user-name"><?php echo $_SESSION["staff_name"]; ?></span>
              <span class="user-role"><?php echo $_SESSION["staff_role"]; ?></span>
            </div>
          </div>
        </div>
      </header>

      <div class="content-area">
        <!-- Dashboard -->
        <section class="page active" id="page-dashboard">

          <div class="summary-cards" id="summaryCards">
            <!-- populated with js -->
          </div>

          <div class="dashboard-grid">
            <div class="card">
              <div class="card-header"><h3>Low Stock Items</h3></div>

              <div class="card-body" id="lowStockList">
                <!-- populated with js -->
              </div>

            </div>
            <div class="card">
              <div class="card-header"><h3>Recent Orders</h3></div>

              <div class="card-body" id="recentOrdersList">
                <!-- populated with js -->
              </div>

            </div>
            <div class="card">
              <div class="card-header"><h3>Top Selling Products</h3></div>

              <div class="card-body" id="topProductsList">
                <!-- populated with js -->
              </div>

            </div>
          </div>
          <div class="dashboard-bottom">
            <div class="card chart-card">
              <div class="card-header">
                <h3>Sales Overview (This Month)</h3>

                <select id="salesMonthSelect" class="form-select-sm">
                    <!-- dropdown options will be populated here -->
                </select>

              </div>

              <div class="card-body">

                <canvas id="salesChart">
                  <!-- populated with js -->
                </canvas>

              </div>
            </div>
            <div class="card calendar-card">
              <div class="card-header"><h3>Calendar</h3></div>
              <div class="card-body" id="calendarWidget"></div>
            </div>
          </div>
        </section>

        <!-- Products -->
        <section class="page" id="page-products">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="productSearch" placeholder="Search products...">
              <select class="form-select" id="productCategoryFilter">
                <option value="">All Categories</option>
              </select>
              <select class="form-select" id="productSupplierFilter">
                <option value="">All Suppliers</option>
              </select>
            </div>
            <!-- ======================= upload excel file to ============================================== -->
            <!-- <div>
              <input class="btn btn-primary" type="file" value="upload excel file" id="upload-file">
              <button type="submit">Upload</button>
            </div> -->
             
            <button class="btn btn-primary" data-action="add" data-entity="product">
              <i class="fas fa-plus"></i> Add Product
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="productsTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Product Name</th>
                  <th>Category</th>
                  <th>Supplier</th>
                  <th>Unit Price (GH₵)</th>
                  <th>Quantity</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="productsPagination"></div>
        </section>

        <!-- Categories -->
        <section class="page" id="page-categories">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="categorySearch" placeholder="Search categories...">
            </div>
            <button class="btn btn-primary" data-action="add" data-entity="category">
              <i class="fas fa-plus"></i> Add Category
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="categoriesTable">
              <thead>
                <tr><th>ID</th><th>Category Name</th><th>Actions</th></tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="categoriesPagination"></div>
        </section>

        <!-- Suppliers -->
        <section class="page" id="page-suppliers">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="supplierSearch" placeholder="Search suppliers...">
            </div>
            <button class="btn btn-primary" data-action="add" data-entity="supplier">
              <i class="fas fa-plus"></i> Add Supplier
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="suppliersTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Supplier Name</th>
                  <th>Phone</th>
                  <th>Address</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="suppliersPagination"></div>
        </section>

        <!-- Customers -->
        <section class="page" id="page-customers">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="customerSearch" placeholder="Search customers...">
            </div>
            <button class="btn btn-primary" data-action="add" data-entity="customer">
              <i class="fas fa-plus"></i> Add Customer
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="customersTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer Name</th>
                  <th>Phone</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="customersPagination"></div>
        </section>

        <!-- Staff -->
        <section class="page" id="page-staff">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="staffSearch" placeholder="Search staff...">
            </div>
            <button class="btn btn-primary" data-action="add" data-entity="staff">
              <i class="fas fa-plus"></i> Add Staff
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="staffTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Staff Name</th>
                  <th>Phone</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="staffPagination"></div>
        </section>

        <!-- Inventory -->
        <section class="page" id="page-inventory">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="inventorySearch" placeholder="Search inventory...">
            </div>
          </div>
          <div class="table-container">
            <table class="data-table" id="inventoryTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Product</th>
                  <th>Stock Quantity</th>
                  <th>Last Update</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="inventoryPagination"></div>
        </section>

        <!-- Orders -->
        <section class="page" id="page-orders">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="orderSearch" placeholder="Search orders...">
              <select class="form-select" id="orderStatusFilter">
                <option value="">All Status</option>
                <option value="Pending">Pending</option>
                <option value="Paid">Paid</option>
                <option value="Delivered">Delivered</option>
                <option value="Cancelled">Cancelled</option>
              </select>
            </div>
            <button class="btn btn-primary" data-action="add" data-entity="order">
              <i class="fas fa-plus"></i> Add Order
            </button>
          </div>
          <div class="table-container">
            <table class="data-table" id="ordersTable">
              <thead>
                <tr>
                  <th>Order ID</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Total (GH₵)</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="ordersPagination"></div>
        </section>

        <!-- Payments -->
        <section class="page" id="page-payments">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="paymentSearch" placeholder="Search payments...">
            </div>
          </div>
          <div class="table-container">
            <table class="data-table" id="paymentsTable">
              <thead>
                <tr>
                  <th>Payment ID</th>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Method</th>
                  <th>Amount</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <!-- populated with JavaScript -->
              </tbody>
            </table>
          </div>
          <div class="pagination" id="paymentsPagination"></div>
        </section>

        <!-- Invoices -->
        <section class="page" id="page-invoices">
          <div class="page-toolbar">
            <div class="toolbar-left">
              <input type="search" class="search-input" id="invoiceSearch" placeholder="Search invoices...">
            </div>
          </div>
          <div class="table-container">
            <table class="data-table" id="invoicesTable">
              <thead>
                <tr>
                  <th>Invoice ID</th>
                  <th>Order ID</th>
                  <th>Date</th>
                  <th>Total</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
          <div class="pagination" id="invoicesPagination"></div>
        </section>

        <!-- Reports -->
        <section class="page" id="page-reports">
          <div class="reports-grid" id="reportsGrid">
            <!-- populated with js -->
          </div>
        </section>
      </div>
    </main>
  </div>

  <!-- Modal -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal" id="modal">
      <div class="modal-header">
        <h2 id="modalTitle">Modal Title</h2>
        <button class="modal-close" id="modalClose" aria-label="Close">&times;</button>
      </div>
      <div class="modal-body" id="modalBody"></div>
      <div class="modal-footer" id="modalFooter"></div>
    </div>
  </div>

  <!-- Toast -->
  <div class="toast-container" id="toastContainer"></div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script src="js/data.js"></script>
  <script src="js/api.js"></script>
  <script src="js/app.js"></script>
</body>
</html>
