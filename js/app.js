//THE CODES IN THIS FILE IS USED TO ADD ENTERACTION WITH THE SYSTEM


const DB = {
  categories: [],
  suppliers: [],
  products: [],
  customers: [],
  staff: [],
  orders: [],
  payments: [],
  invoices: [],
  order_items: [],
  inventory_logs: []
};

window.DB = DB;

const App = {
  currentPage: 'dashboard',
  pageSize: 8,
  pagination: {},
  salesChart: null,

  init() {
    this.bindEvents();
    this.loadData()
      .then(() => {
        this.populateFilters();
        this.navigate('dashboard');
      })
      .catch((error) => {
        console.error(error);
        this.showToast('Could not load data from the database', 'error');
      });
  },

  bindEvents() {
    document.querySelectorAll('.nav-link[data-page]').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        this.navigate(link.dataset.page);
        document.getElementById('sidebar').classList.remove('open');
      });
    });

    document.getElementById('menuToggle').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('open');
    });

    document.getElementById('modalClose').addEventListener('click', () => this.closeModal());
    document.getElementById('modalOverlay').addEventListener('click', (e) => {
      if (e.target === e.currentTarget) this.closeModal();
    });

    document.getElementById('logoutBtn').addEventListener('click', (e) => {
      e.preventDefault();
      if (confirm('Are you sure you want to logout?')) {
        window.location.href = 'logout.php';
      }
    });

    document.addEventListener('click', (e) => {
      const addBtn = e.target.closest('[data-action="add"]');
      if (addBtn) {
        this.openForm(addBtn.dataset.entity);
        return;
      }

      const actionBtn = e.target.closest('[data-action]');
      if (actionBtn && actionBtn.dataset.action !== 'add') {
        this.handleAction(actionBtn);
      }
    });

    const searchMap = {
      productSearch: ['products', this.renderProducts.bind(this)],
      categorySearch: ['categories', this.renderCategories.bind(this)],
      supplierSearch: ['suppliers', this.renderSuppliers.bind(this)],
      customerSearch: ['customers', this.renderCustomers.bind(this)],
      staffSearch: ['staff', this.renderStaff.bind(this)],
      inventorySearch: ['inventory', this.renderInventory.bind(this)],
      orderSearch: ['orders', this.renderOrders.bind(this)],
      paymentSearch: ['payments', this.renderPayments.bind(this)],
      invoiceSearch: ['invoices', this.renderInvoices.bind(this)]
    };

    Object.entries(searchMap).forEach(([id, [, renderFn]]) => {
      const el = document.getElementById(id);
      if (el) el.addEventListener('input', () => renderFn());
    });

    document.getElementById('productCategoryFilter')?.addEventListener('change', () => this.renderProducts());
    document.getElementById('productSupplierFilter')?.addEventListener('change', () => this.renderProducts());
    document.getElementById('orderStatusFilter')?.addEventListener('change', () => this.renderOrders());
  },

  navigate(page) {
    this.currentPage = page;
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    document.getElementById(`page-${page}`)?.classList.add('active');

    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector(`.nav-link[data-page="${page}"]`)?.classList.add('active');

    const titles = {
      dashboard: 'Dashboard', products: 'Products', categories: 'Categories',
      suppliers: 'Suppliers', customers: 'Customers', staff: 'Staff',
      inventory: 'Inventory', orders: 'Orders', payments: 'Payments',
      invoices: 'Invoices', reports: 'Reports'
    };

    document.getElementById('pageTitle').textContent = titles[page] || page;
    // Render the page content============
    const renderers = {
      dashboard: () => this.renderDashboard(),
      products: () => this.renderProducts(),
      categories: () => this.renderCategories(),
      suppliers: () => this.renderSuppliers(),
      customers: () => this.renderCustomers(),
      staff: () => this.renderStaff(),
      inventory: () => this.renderInventory(),
      orders: () => this.renderOrders(),
      payments: () => this.renderPayments(),
      invoices: () => this.renderInvoices(),
      reports: () => this.renderReports()
    };

    renderers[page]?.();
  },

  populateFilters() {
    const catFilter = document.getElementById('productCategoryFilter');
    if (catFilter) {
      catFilter.innerHTML = '<option value="">All Categories</option>';
      DB.categories.forEach(c => {
        catFilter.innerHTML += `<option value="${c.id}">${c.name}</option>`;
      });
    }

    const supFilter = document.getElementById('productSupplierFilter');
    if (supFilter) {
      supFilter.innerHTML = '<option value="">All Suppliers</option>';
      DB.suppliers.forEach(s => {
        supFilter.innerHTML += `<option value="${s.id}">${s.name}</option>`;
      });
    }
  },

  async loadData() {
    const [categories, suppliers, products, customers, staff, orders, payments, invoices] = await Promise.all([
      API.getAll('categories').catch(() => []),
      API.getAll('suppliers').catch(() => []),
      API.getAll('products').catch(() => []),
      API.getAll('customers').catch(() => []),
      API.getAll('staff').catch(() => []),
      API.getAll('orders').catch(() => []),
      API.getAll('payments').catch(() => []),
      API.getAll('invoices').catch(() => [])
    ]);

    DB.categories = Array.isArray(categories) ? categories : [];
    DB.suppliers = Array.isArray(suppliers) ? suppliers : [];
    DB.products = Array.isArray(products) ? products : [];
    DB.customers = Array.isArray(customers) ? customers : [];
    DB.staff = Array.isArray(staff) ? staff : [];
    DB.orders = Array.isArray(orders) ? orders : [];
    DB.payments = Array.isArray(payments) ? payments : [];
    DB.invoices = Array.isArray(invoices) ? invoices : [];
    DB.order_items = [];
    DB.inventory_logs = [];
  },

  async refreshData() {
    await this.loadData();
    this.populateFilters();
  },

  // ── Dashboard ──

  async renderDashboard() {
    // Fetch dashboard stats from API (simulated here)
    const stats = await API.getDashboard();

    // Render summary cards===========
    document.getElementById('summaryCards').innerHTML = `
      <div class="summary-card blue">
        <div class="icon"><i class="fas fa-box"></i></div>
        <div class="info">
          <h3>${stats.totalProducts}</h3>
          <p>Total Products</p>
          <a href="#" onclick="App.navigate('products');return false;">View all products</a>
        </div>
      </div>
      <div class="summary-card green">
        <div class="icon"><i class="fas fa-shopping-cart"></i></div>
        <div class="info">
          <h3>${stats.totalOrders}</h3>
          <p>Total Orders</p>
          <a href="#" onclick="App.navigate('orders');return false;">View all orders</a>
        </div>
      </div>
      <div class="summary-card orange">
        <div class="icon"><i class="fas fa-users"></i></div>
        <div class="info">
          <h3>${stats.totalCustomers}</h3>
          <p>Total Customers</p>
          <a href="#" onclick="App.navigate('customers');return false;">View all customers</a>
        </div>
      </div>
      <div class="summary-card purple">
        <div class="icon"><i class="fas fa-chart-line"></i></div>
        <div class="info">
          <h3>${(stats.totalSales)}</h3>
          <p>Total Sales</p>
          <a href="#" onclick="App.navigate('reports');return false;">View sales report</a>
        </div>
      </div>`;

    // Render low stock items===========
    document.getElementById('lowStockList').innerHTML = stats.lowStock.length
      ? stats.lowStock.map(p => `
          <div class="list-item">
            <div class="product-img"><i class="fas fa-box"></i></div>
            <div class="item-info">
              <h4>${p.name}</h4>
              <p>Bottles</p>
            </div>
            <span class="item-value low-stock">${p.quantity}</span>
          </div>`).join('')
      : '<div class="empty-state"><p>All items well stocked</p></div>';

      // Render recent orders===========
    document.getElementById('recentOrdersList').innerHTML = stats.recentOrders.map(o => `
      <div class="list-item">
        <div class="item-info">
          <h4><a href="#" class="order-link" onclick="App.viewOrder(${o.id});return false;">#${o.id}</a></h4>
          <p>${o.customer_name} &middot; ${formatDate(o.order_date)}</p>
        </div>
        <span class="item-value">${formatCurrency(o.total_amount)}</span>
      </div>`).join('');

      // Render top products===========
    document.getElementById('topProductsList').innerHTML = stats.topProducts.map((p, i) => `
      <div class="list-item">
        <span class="rank">${i + 1}</span>
        <div class="product-img"><i class="fas fa-box"></i></div>
        <div class="item-info">
          <h4>${p.name}</h4>
          <p>${p.quantity} sold</p>
        </div>
      </div>`).join('');

    this.renderSalesChart(stats.salesByDay);
    this.renderCalendar();
  },

  renderSalesChart(salesByDay) {
    const ctx = document.getElementById('salesChart');
    if (this.salesChart) this.salesChart.destroy();

    const days = Array.from({ length: 30 }, (_, i) => String(i + 1).padStart(2, '0'));
    const data = days.map(d => salesByDay[d] || 0);

    this.salesChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: days,
        datasets: [{
          label: 'Sales (GH₵)',
          data,
          borderColor: '#3498db',
          backgroundColor: 'rgba(52, 152, 219, 0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { callback: v => `GH₵${v}` } },
          x: { grid: { display: false } }
        }
      }
    });
  },

  renderCalendar() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth();
    const today = now.getDate();

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrev = new Date(year, month, 0).getDate();
    const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];

    let html = `<div class="calendar-widget">
      <div class="calendar-header">
        <button onclick="App.renderCalendar()"><i class="fas fa-chevron-left"></i></button>
        <h4>${monthNames[month]} ${year}</h4>
        <button onclick="App.renderCalendar()"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div class="calendar-grid">
        ${['Su','Mo','Tu','We','Th','Fr','Sa'].map(d => `<div class="calendar-day-name">${d}</div>`).join('')}`;

    for (let i = firstDay - 1; i >= 0; i--) {
      html += `<div class="calendar-day other-month">${daysInPrev - i}</div>`;
    }
    for (let d = 1; d <= daysInMonth; d++) {
      html += `<div class="calendar-day${d === today ? ' today' : ''}">${d}</div>`;
    }
    const remaining = 42 - (firstDay + daysInMonth);
    for (let d = 1; d <= remaining; d++) {
      html += `<div class="calendar-day other-month">${d}</div>`;
    }

    html += '</div></div>';
    document.getElementById('calendarWidget').innerHTML = html;
  },

  // ── Pagination Helper ──

  paginate(items, pageKey) {
    if (!this.pagination[pageKey]) this.pagination[pageKey] = 1;
    const page = this.pagination[pageKey];
    const total = items.length;
    const start = (page - 1) * this.pageSize;
    const paged = items.slice(start, start + this.pageSize);

    return { paged, page, total, totalPages: Math.ceil(total / this.pageSize) || 1 };
  },

  renderPagination(containerId, pageKey, total, totalPages) {
    const page = this.pagination[pageKey];
    const start = total === 0 ? 0 : (page - 1) * this.pageSize + 1;
    const end = Math.min(page * this.pageSize, total);

    let buttons = '';
    for (let i = 1; i <= totalPages; i++) {
      buttons += `<button class="pagination-btn${i === page ? ' active' : ''}" onclick="App.goToPage('${pageKey}', ${i})">${i}</button>`;
    }

    document.getElementById(containerId).innerHTML = `
      <span>Showing ${start} to ${end} of ${total}</span>
      <div class="pagination-controls">
        <button class="pagination-btn" ${page <= 1 ? 'disabled' : ''} onclick="App.goToPage('${pageKey}', ${page - 1})">Prev</button>
        ${buttons}
        <button class="pagination-btn" ${page >= totalPages ? 'disabled' : ''} onclick="App.goToPage('${pageKey}', ${page + 1})">Next</button>
      </div>`;
  },

  goToPage(pageKey, page) {
    this.pagination[pageKey] = page;
    const renderMap = {
      products: () => this.renderProducts(),
      categories: () => this.renderCategories(),
      suppliers: () => this.renderSuppliers(),
      customers: () => this.renderCustomers(),
      staff: () => this.renderStaff(),
      inventory: () => this.renderInventory(),
      orders: () => this.renderOrders(),
      payments: () => this.renderPayments(),
      invoices: () => this.renderInvoices()
    };
    renderMap[pageKey]?.();
  },

  // ── Table Renderers ──


  // Render products table
  renderProducts() {
    let items = [...DB.products];
    const search = document.getElementById('productSearch').value.toLowerCase();
    const catFilter = document.getElementById('productCategoryFilter').value;
    const supFilter = document.getElementById('productSupplierFilter').value;

    if (search) items = items.filter(p => p.name.toLowerCase().includes(search));
    if (catFilter) items = items.filter(p => p.category_id === parseInt(catFilter));
    if (supFilter) items = items.filter(p => p.supplier_id === parseInt(supFilter));

    const { paged, total, totalPages } = this.paginate(items, 'products');
    const tbody = document.querySelector('#productsTable tbody');

    tbody.innerHTML = paged.map(p => `
      <tr>
        <td>${p.id}</td>
        <td>${p.name}</td>
        <td>${getCategoryName(p.category_id)}</td>
        <td>${getSupplierName(p.supplier_id)}</td>
        <td>${formatCurrency(p.unit_price)}</td>
        <td>${p.quantity}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-view" data-action="view" data-entity="product" data-id="${p.id}">View</button>
            <button class="btn btn-sm btn-edit" data-action="edit" data-entity="product" data-id="${p.id}">Edit</button>
            <button class="btn btn-sm btn-delete" data-action="delete" data-entity="product" data-id="${p.id}">Delete</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('productsPagination', 'products', total, totalPages);
  },

  renderCategories() {
    let items = [...DB.categories];
    const search = document.getElementById('categorySearch').value.toLowerCase();
    if (search) items = items.filter(c => c.name.toLowerCase().includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'categories');
    document.querySelector('#categoriesTable tbody').innerHTML = paged.map(c => `
      <tr>
        <td>${c.id}</td>
        <td>${c.name}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-edit" data-action="edit" data-entity="category" data-id="${c.id}">Edit</button>
            <button class="btn btn-sm btn-delete" data-action="delete" data-entity="category" data-id="${c.id}">Delete</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('categoriesPagination', 'categories', total, totalPages);
  },

  renderSuppliers() {
    let items = [...DB.suppliers];
    const search = document.getElementById('supplierSearch').value.toLowerCase();
    if (search) items = items.filter(s => s.name.toLowerCase().includes(search) || s.phone.includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'suppliers');
    document.querySelector('#suppliersTable tbody').innerHTML = paged.map(s => `
      <tr>
        <td>${s.id}</td>
        <td>${s.name}</td>
        <td>${s.phone}</td>
        <td>${s.address || ''}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-edit" data-action="edit" data-entity="supplier" data-id="${s.id}">Edit</button>
            <button class="btn btn-sm btn-delete" data-action="delete" data-entity="supplier" data-id="${s.id}">Delete</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('suppliersPagination', 'suppliers', total, totalPages);
  },

  renderCustomers() {
    let items = [...DB.customers];
    const search = document.getElementById('customerSearch').value.toLowerCase();
    if (search) items = items.filter(c => c.name.toLowerCase().includes(search) || c.phone.includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'customers');
    document.querySelector('#customersTable tbody').innerHTML = paged.map(c => `
      <tr>
        <td>${c.id}</td>
        <td>${c.name}</td>
        <td>${c.phone}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-edit" data-action="edit" data-entity="customer" data-id="${c.id}">Edit</button>
            <button class="btn btn-sm btn-delete" data-action="delete" data-entity="customer" data-id="${c.id}">Delete</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('customersPagination', 'customers', total, totalPages);
  },

  renderStaff() {
    let items = [...DB.staff];
    const search = document.getElementById('staffSearch').value.toLowerCase();
    if (search) items = items.filter(s => s.name.toLowerCase().includes(search) || s.role.toLowerCase().includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'staff');
    document.querySelector('#staffTable tbody').innerHTML = paged.map(s => `
      <tr>
        <td>${s.id}</td>
        <td>${s.name}</td>
        <td>${s.phone}</td>
        <td>${s.email}</td>
        <td>${s.role}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-edit" data-action="edit" data-entity="staff" data-id="${s.id}">Edit</button>
            <button class="btn btn-sm btn-delete" data-action="delete" data-entity="staff" data-id="${s.id}">Delete</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('staffPagination', 'staff', total, totalPages);
  },

  renderInventory() {
    let items = [...DB.products];
    const search = document.getElementById('inventorySearch').value.toLowerCase();
    if (search) items = items.filter(p => p.name.toLowerCase().includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'inventory');
    document.querySelector('#inventoryTable tbody').innerHTML = paged.map(p => `
      <tr>
        <td>${p.id}</td>
        <td>${p.name}</td>
        <td>${p.quantity <= p.low_stock_threshold ? `<span style="color:var(--danger);font-weight:600">${p.quantity}</span>` : p.quantity}</td>
        <td>${p.updated_at ? formatDate(p.updated_at) : '—'}</td>
        <td>
          <button class="btn btn-sm btn-update" data-action="updateStock" data-id="${p.id}">Update Stock</button>
        </td>
      </tr>`).join('');

    this.renderPagination('inventoryPagination', 'inventory', total, totalPages);
  },

  // Render orders table=====
  renderOrders() {
    let items = [...DB.orders];
    const search = document.getElementById('orderSearch').value.toLowerCase();
    const statusFilter = document.getElementById('orderStatusFilter').value;

    if (search) {
      items = items.filter(o =>
        String(o.id).includes(search) ||
        getCustomerName(o.customer_id).toLowerCase().includes(search)
      );
    }
    if (statusFilter) items = items.filter(o => o.status === statusFilter);

    const { paged, total, totalPages } = this.paginate(items, 'orders');
    document.querySelector('#ordersTable tbody').innerHTML = paged.map(o => `
      <tr>
        <td>${o.id}</td>
        <td>${getCustomerName(o.customer_id)}</td>
        <td>${formatDate(o.order_date)}</td>
        <td>${formatCurrency(o.total_amount)}</td>
        <td><span class="status-badge ${getStatusClass(o.status)}">${o.status}</span></td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-view" data-action="view" data-entity="order" data-id="${o.id}">View</button>
            <button class="btn btn-sm btn-invoice" data-action="invoice" data-id="${o.id}">Invoice</button>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('ordersPagination', 'orders', total, totalPages);
  },

  // Render payments table======
  renderPayments() {
    let items = [...DB.payments];
    const search = document.getElementById('paymentSearch').value.toLowerCase();
    if (search) items = items.filter(p => String(p.id).includes(search) || String(p.order_id).includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'payments');
    document.querySelector('#paymentsTable tbody').innerHTML = paged.map(p => `
      <tr>
        <td>${p.id}</td>
        <td>${p.order_id}</td>
        <td>${formatDate(p.payment_date)}</td>
        <td>${p.method}</td>
        <td>${formatCurrency(p.amount)}</td>
        <td>
          <button onclick="printPayment(${p.id})" class="btn btn-sm btn-print" data-action="print" data-entity="payment" data-id="${p.id}">Print</button>
        </td>
      </tr>`).join('');
      
    this.renderPagination('paymentsPagination', 'payments', total, totalPages);
  },


  // Render invoices table==
  renderInvoices() {
    let items = [...DB.invoices];
    const search = document.getElementById('invoiceSearch').value.toLowerCase();
    if (search) items = items.filter(i => String(i.id).includes(search) || String(i.order_id).includes(search));

    const { paged, total, totalPages } = this.paginate(items, 'invoices');
    document.querySelector('#invoicesTable tbody').innerHTML = paged.map(i => `
      <tr>
        <td>INV-${String(i.id).padStart(4, '0')}</td>
        <td>${i.order_id}</td>
        <td>${formatDate(i.invoice_date)}</td>
        <td>${formatCurrency(i.total_amount)}</td>
        <td>
          <div class="action-buttons">
            <button class="btn btn-sm btn-view" data-action="view" data-entity="invoice" data-id="${i.id}">View</button>
            <a href="print_invoice.php?id=${i.id}" target="_blank" class="btn btn-sm btn-print" data-action="print" data-entity="invoice" data-id="${i.id}">Print</a>
          </div>
        </td>
      </tr>`).join('');

    this.renderPagination('invoicesPagination', 'invoices', total, totalPages);
  },

  renderReports() {
    const reports = [
      { 
        type: 'daily-sales', 
        title: 'Daily Sales', 
        desc: 'View sales breakdown by day', 
        icon: 'fa-calendar-day', 
        color: '#3498db' 
      },
      { 
        type: 'monthly-sales', 
        title: 'Monthly Sales', 
        desc: 'Monthly sales trends and totals', 
        icon: 'fa-chart-line', 
        color: '#27ae60' 
      },
      { 
        type: 'inventory', 
        title: 'Inventory Report', 
        desc: 'Stock levels and movement history', 
        icon: 'fa-warehouse', 
        color: '#f39c12' 
      },
      { 
        type: 'customer', 
        title: 'Customer Report', 
        desc: 'Customer purchase history and analytics', 
        icon: 'fa-users', 
        color: '#9b59b6' 
      },
      { 
        type: 'payment', 
        title: 'Payment Report', 
        desc: 'Payment methods and transaction summary', 
        icon: 'fa-money-bill-wave', 
        color: '#e67e22' 
      },
      { 
        type: 'supplier', 
        title: 'Supplier Report', 
        desc: 'Supplier deliveries and product sourcing', 
        icon: 'fa-truck', 
        color: '#1e3a5f' 
      }
    ];

    document.getElementById('reportsGrid').innerHTML = reports.map(r => `
      <div class="report-card">
        <div class="report-icon" style="background:${r.color}"><i class="fas ${r.icon}"></i></div>
        <h3>${r.title}</h3>
        <p>${r.desc}</p>
        <button class="btn btn-primary" onclick="App.generateReport('${r.type}')">Generate Report</button>
      </div>`).join('');
  },

  // reports table===============
async generateReport(type) {
    const result = await API.generateReport(type);
    const data = Array.isArray(result.data) ? result.data : [];
    let html = `
        <div class="report">
            <h2>Addaduma Prestige Enterprise</h2>
            <h3>${type.replace(/-/g, ' ').toUpperCase()}</h3>
            <div class="report-info">
                <p><strong>Date:</strong> ${new Date().toLocaleString()}</p>
                <p><strong>Total Records:</strong> ${data.length}</p>
            </div>
    `;
    if (data.length > 0) {
        const headers = Object.keys(data[0]);
        html += `
            <table class="report-table">
                <thead>
                <tr>
        `;
        headers.forEach(header => {
            html += `<th>${header.replace(/_/g,' ')}</th>`;
        });
        html += `
                </tr>
                </thead>
                <tbody>
        `;
        data.forEach(row => {
            html += "<tr>";
            headers.forEach(header => {
                html += `<td>${row[header]}</td>`;
            });
            html += "</tr>";
        });
        html += `
            </tbody>
            </table>
        `;
    } else {
        html += "<p>No records found.</p>";
    }
    html += "</div>";
    this.openModal(
        "Report",
        html,
        `
        <button class="btn btn-primary" onclick="printReport()"> Print</button>
        <button class="btn btn-secondary" onclick="App.closeModal()">Close</button>
        `
    );
},

  // ── Actions ──

  handleAction(btn) {
    const { action, entity, id } = btn.dataset;
    const numId = parseInt(id);

    switch (action) {
      case 'edit': this.openForm(entity, numId); break;
      case 'view': this.viewEntity(entity, numId); break;
      case 'update Status': this.openForm(entity, numId); break;
      case 'delete': this.confirmDelete(entity, numId); break;
      case 'updateStock': this.openStockUpdate(numId); break;
      case 'invoice': this.viewInvoice(numId); break;
      case 'print': this.showToast(`Printing ${entity} ${numId}...`, 'info'); break;
      case 'pdf': this.showToast(`Generating PDF for invoice #${numId}...`, 'info'); break;
    }
  },
  // update status===========

  viewEntity(entity, id) {
    if (entity === 'product') {
      const p = DB.products.find(x => x.id === id);
      this.openModal('Product Details', `
        <p><strong>Name:</strong> ${p.name}</p>
        <p><strong>Category:</strong> ${getCategoryName(p.category_id)}</p>
        <p><strong>Supplier:</strong> ${getSupplierName(p.supplier_id)}</p>
        <p><strong>Unit Price:</strong> ${formatCurrency(p.unit_price)}</p>
        <p><strong>Quantity:</strong> ${p.quantity}</p>`, '');
    } else if (entity === 'order') {
      this.viewOrder(id);
    } else if (entity === 'invoice') {
      this.viewInvoiceById(id);
    }
  },

  async viewOrder(id) {
    const order = await API.getById('orders', id);
    const items = Array.isArray(order?.items) ? order.items : [];
    const itemsHtml = items.map(i =>
      `<tr>
        <td>${i.product_name || 'Unknown'}</td>
        <td>${i.quantity}</td>
        <td>${formatCurrency(i.unit_price)}</td>
        <td>${formatCurrency(i.subtotal)}</td>
      </tr>`
    ).join('');

    this.openModal(`Order ${id}`, `
      <p><strong>Customer:</strong> ${order?.customer_name || 'Unknown'}</p>
      <p><strong>Date:</strong> ${formatDate(order?.order_date)}</p>

      <p>
        <strong>Status:</strong> 
        <span class="status-badge ${getStatusClass(order?.status)}">${order?.status || 'Unknown'}</span>
      </p>

      <table class="data-table" style="margin-top:16px">
        <thead>
          <tr>
            <th>Product</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        
        <tbody>${itemsHtml}</tbody>
      </table>
      <p style="margin-top:12px;text-align:right"><strong>Total: ${formatCurrency(order?.total_amount)}</strong></p>`, '');
  },

  viewInvoice(orderId) {
    const inv = DB.invoices.find(i => i.order_id === orderId);
    if (inv) this.viewInvoiceById(inv.id);
    else this.showToast('No invoice found for this order', 'error');
  },

  viewInvoiceById(id) {
    const inv = DB.invoices.find(i => i.id === id);
    const o = DB.orders.find(x => x.id === inv.order_id);
    this.openModal(`Invoice INV-${String(id).padStart(4, '0')}`, `
      <p><strong>Order:</strong> ${inv.order_id}</p>
      <p><strong>Customer:</strong> ${getCustomerName(o.customer_id)}</p>
      <p><strong>Date:</strong> ${formatDate(inv.invoice_date)}</p>
      <p><strong>Total:</strong> ${formatCurrency(inv.total_amount)}</p>`, '');
  },

  // ===== Forms ====
  openForm(entity, id = null) {
    const isEdit = id !== null;
    const forms = {
      product: () => {
        const p = isEdit ? DB.products.find(x => x.id === id) : {};
        const catOptions = DB.categories.map(c => `<option value="${c.id}" ${p.category_id === c.id ? 'selected' : ''}>${c.name}</option>`).join('');
        const supOptions = DB.suppliers.map(s => `<option value="${s.id}" ${p.supplier_id === s.id ? 'selected' : ''}>${s.name}</option>`).join('');
        return {
          title: isEdit ? 'Edit Product' : 'Add Product',
          html: `<form id="entityForm">
            <div class="form-group">
              <label>Product Name</label>
              <input name="name" value="${p.name || ''}" required>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                  ${catOptions}
                </select>
              </div>
              <div class="form-group">
                <label>Supplier</label>
                <select name="supplier_id" required>
                  ${supOptions}
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Unit Price (GH₵)</label>
                <input name="unit_price" type="number" step="0.01" value="${p.unit_price || ''}" required>
              </div>
              <div class="form-group">
                <label>Quantity</label>
                <input name="quantity" type="number" value="${p.quantity || 0}" required>
              </div>
            </div>
          </form>`,
          resource: 'products'
        };
      },
      category: () => {
        const c = isEdit ? DB.categories.find(x => x.id === id) : {};
        return {
          title: isEdit ? 'Edit Category' : 'Add Category',
          html: `<form id="entityForm">
            <div class="form-group">
              <label>Category Name</label>
              <input name="name" value="${c.name || ''}" required>
            </div>
          </form>`,
          resource: 'categories'
        };
      },
      supplier: () => {
        const s = isEdit ? DB.suppliers.find(x => x.id === id) : {};
        return {
          title: isEdit ? 'Edit Supplier' : 'Add Supplier',
          html: `<form id="entityForm">
            <div class="form-group">
              <label>Supplier Name</label><input name="name" value="${s.name || ''}" required>
            </div>
            <div class="form-group">
              <label>Phone</label><input name="phone" value="${s.phone || ''}" required>
            </div>
            <div class="form-group">
              <label>Address</label><textarea name="address">${s.address || ''}</textarea>
            </div>
          </form>`,
          resource: 'suppliers'
        };
      },
      customer: () => {
        const c = isEdit ? DB.customers.find(x => x.id === id) : {};
        return {
          title: isEdit ? 'Edit Customer' : 'Add Customer',
          html: `<form id="entityForm">
            <div class="form-group"><label>Customer Name</label><input name="name" value="${c.name || ''}" required></div>
            <div class="form-group"><label>Phone</label><input name="phone" value="${c.phone || ''}" required></div>
          </form>`,
          resource: 'customers'
        };
      },

      staff: () => {
        const s = isEdit ? DB.staff.find(x => x.id === id) : {};
        const roles = ['Administrator', 'Manager', 'Sales'];
        const roleOptions = roles.map(r => 
            `<option value="${r}" ${s.role === r ? 'selected' : ''}>${r}</option>`).join('');
        return {
          title: isEdit ? 'Edit Staff' : 'Add Staff',
          html: `<form id="entityForm">
            <div class="form-group">
              <label>Staff Name</label>
              <input name="name" value="${s.name || ''}" required>
              <label>Staff Email</label>
              <input name="email" value="${s.email || ''}" required>
              <label>Staff Password</label>
              <input name="password_hash" type="password" value="${s.password_hash || ''}" required>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Phone</label>
                <input name="phone" value="${s.phone || ''}" required>
              </div>

              <div class="form-group">
                <label>Role</label>
                <select name="role" required>${roleOptions}</select>
              </div>
            </div>
          </form>`,
          resource: 'staff'
        };
      },

      order: () => {
        const custOptions = DB.customers.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        const productOptions = DB.products.map(p => `<option value="${p.id}" data-unit-price="${p.unit_price}">${p.name}</option>`).join('');
        const defaultProduct = DB.products[0];
        const defaultPrice = defaultProduct ? defaultProduct.unit_price : 0;
        return {
          title: 'Add Order',
          html: `<form id="entityForm">
            <div class="form-group">
              <label>Customer</label>
              <select name="customer_id" required>${custOptions}</select>
            </div>

            <div class="form-row">
              <div class="form-group">
              <label>Date</label>
                <input name="order_date" type="date" value="${new Date().toISOString().split('T')[0]}" required>
              </div>

              <div class="form-group">
                <label>Status</label>
                <select name="status">
                  <option>Pending</option>
                  <option>Paid</option>
                  <option>Delivered</option>
                </select>
              </div>

            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Product</label>
                <select name="product_id" id="orderProductSelect" onchange="App.updateOrderTotals()" required>${productOptions}</select>
              </div>

              <div class="form-group">
                <label>Quantity</label>
                <input name="quantity" id="orderQuantityInput" type="number" min="1" value="1" oninput="App.updateOrderTotals()" required></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label>Unit Price (GH₵)</label><input name="unit_price" id="orderUnitPriceInput" type="number" step="0.01" value="${defaultPrice}" required></div>
              <div class="form-group"><label>Total (GH₵)</label><input name="total_amount" id="orderTotalInput" type="number" step="0.01" value="${defaultPrice}" readonly></div>
            </div>
          </form>`,
          resource: 'orders'
        };
      }
    };

    const form = forms[entity]?.();
    if (!form) return;

    const footer = `<button class="btn btn-secondary" onclick="App.closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="App.saveForm('${form.resource}', ${id})">${isEdit ? 'Update' : 'Save'}</button>`;

    this.openModal(form.title, form.html, footer);
  },

  updateOrderTotals() {
    const productSelect = document.getElementById('orderProductSelect');
    const quantityInput = document.getElementById('orderQuantityInput');
    const unitPriceInput = document.getElementById('orderUnitPriceInput');
    const totalInput = document.getElementById('orderTotalInput');

    if (!productSelect || !quantityInput || !unitPriceInput || !totalInput) return;

    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const selectedPrice = parseFloat(selectedOption?.dataset.unitPrice || '0');
    const quantity = parseInt(quantityInput.value, 10) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || selectedPrice;

    if (selectedOption && !unitPriceInput.value) {
      unitPriceInput.value = selectedPrice.toFixed(2);
    }

    totalInput.value = (quantity * unitPrice).toFixed(2);
  },

  async saveForm(resource, id) {
    const form = document.getElementById('entityForm');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());

    if (data.category_id) data.category_id = parseInt(data.category_id);
    if (data.supplier_id) data.supplier_id = parseInt(data.supplier_id);
    if (data.customer_id) data.customer_id = parseInt(data.customer_id);
    if (data.product_id) data.product_id = parseInt(data.product_id);
    if (data.unit_price) data.unit_price = parseFloat(data.unit_price);
    if (data.quantity !== undefined && data.quantity !== '') data.quantity = parseInt(data.quantity);
    if (data.total_amount) data.total_amount = parseFloat(data.total_amount);

    // The order form now sends the product line item details, so the backend can
    // insert an order item and reduce stock without needing any separate flow.
    if (resource === 'orders' && data.product_id) {
      const quantity = parseInt(data.quantity || 0, 10);
      const unitPrice = parseFloat(data.unit_price || 0);
      data.total_amount = (quantity * unitPrice).toFixed(2);
    }

    try {
      if (id) {
        await API.update(resource, id, data);
        this.showToast('Updated successfully', 'success');
      } else {
        await API.create(resource, data);
        this.showToast('Created successfully', 'success');
      }

      await this.refreshData();
      this.closeModal();
      this.navigate(this.currentPage);
    } catch (error) {
      this.showToast(error.message || 'Unable to save the record', 'error');
    }
  },

  openStockUpdate(productId) {
    const p = DB.products.find(x => x.id === productId);

    this.openModal('Update Stock', `
      <form id="stockForm">
        <p><strong>Product:</strong> ${p.name}</p>
        <p><strong>Current Stock:</strong> ${p.quantity}</p>
        <div class="form-group">
          <label>New Quantity</label>
          <input name="quantity" type="number" value="${p.quantity}" required>
        </div>
        <div class="form-group">
          <label>Notes</label>
          <textarea name="notes" placeholder="Reason for adjustment..."></textarea>
        </div>
      </form>`,
      `<button class="btn btn-secondary" onclick="App.closeModal()">Cancel</button>
       <button class="btn btn-primary" onclick="App.saveStock(${productId})">Update</button>`);
  },

  async saveStock(productId) {
    const form = document.getElementById('stockForm');
    const data = Object.fromEntries(new FormData(form).entries());

    try {
      await API.updateStock(productId, parseInt(data.quantity), data.notes);
      await this.refreshData();
      this.showToast('Stock updated', 'success');
      this.closeModal();
      this.renderInventory();
    } catch (error) {
      this.showToast(error.message || 'Unable to update stock', 'error');
    }
  },

  confirmDelete(entity, id) {
    const resourceMap = { product: 'products', category: 'categories', supplier: 'suppliers', customer: 'customers', staff: 'staff' };
    const resource = resourceMap[entity];
    if (!resource) return;

    this.openModal('Confirm Delete', 
      `<p>Are you sure you want to delete this ${entity}? This action cannot be undone.</p>`,
      `<button class="btn btn-secondary" onclick="App.closeModal()">Cancel</button>
       <button class="btn btn-delete" onclick="App.deleteEntity('${resource}', ${id})">Delete</button>`);
  },

  async deleteEntity(resource, id) {
    try {
      await API.delete(resource, id);
      await this.refreshData();
      this.showToast('Deleted successfully', 'success');
      this.closeModal();
      this.navigate(this.currentPage);
    } catch (error) {
      this.showToast(error.message || 'Unable to delete the record', 'error');
      this.closeModal();
    }
  },

  // ── Modal & Toast ──
  openModal(title, body, footer) {
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalBody').innerHTML = body;
    document.getElementById('modalFooter').innerHTML = footer;
    document.getElementById('modalOverlay').classList.add('active');
  },

  closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
  },

  showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.getElementById('toastContainer').appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
  },

};
function printPayment(id) {
    window.open(`print_payment.php?id=${id}`, "_blank");
}

function printReport(){
  window.print();
}



document.addEventListener('DOMContentLoaded', () => App.init());
