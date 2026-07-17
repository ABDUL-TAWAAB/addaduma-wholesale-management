// THIS PAGE THIS PAGE IS USED TO GET HTTP REQUEST AND HANDLE THE API

const API = {
  USE_API: true,

  getBaseUrl() {
    try {
      const scripts = Array.from(document.getElementsByTagName('script'));
      const apiScript = scripts.find(script => /api\.js(?:\?.*)?$/.test(script.src));
      if (apiScript) {
        const scriptUrl = new URL(apiScript.src, window.location.href);
        const scriptPath = scriptUrl.pathname;
        const scriptDir = scriptPath.substring(0, scriptPath.lastIndexOf('/'));
        return scriptDir.replace(/\/js$/, '/api');
      }

      const pageUrl = new URL(window.location.href);
      const pathParts = pageUrl.pathname.split('/').filter(Boolean);
      const appIndex = pathParts.lastIndexOf('addaduma-wholesale-system');
      const basePath = appIndex >= 0 ? `/${pathParts.slice(0, appIndex + 1).join('/')}` : '';
      return `${basePath}/api`;
    } catch {
      return '/api';
    }
  },

  getRequestUrls(endpoint) {
    const cleanEndpoint = endpoint.startsWith('/') ? endpoint : `/${endpoint}`;
    const baseUrl = this.getBaseUrl();
    const prettyUrl = `${baseUrl}${cleanEndpoint}`;
    const scriptUrl = `${baseUrl.replace(/\/api$/, '/api/index.php')}?path=${cleanEndpoint.replace(/^\/+/, '')}`;
    return [prettyUrl, scriptUrl];
  },

  async request(endpoint, options = {}) {
    if (!this.USE_API) {
      return this.mockRequest(endpoint, options);
    }

    const config = {
      headers: { 'Content-Type': 'application/json' },
      ...options
    };

    if (config.body && typeof config.body === 'object') {
      config.body = JSON.stringify(config.body);
    }

    const method = (options.method || 'GET').toUpperCase();
    const urls = this.getRequestUrls(endpoint);
    
    // For DELETE and PUT requests, prioritize the query string URL (more reliable with .htaccess)
    if (method === 'DELETE' || method === 'PUT') {
      urls.reverse();
    }

    let lastResponse = null;
    for (const url of urls) {
      try {
        const response = await fetch(url, config);
        if (response.ok) {
          return response.json();
        }
        lastResponse = response;
        // Continue trying other URLs even on 405, but break on other client errors
        if (response.status !== 404 && response.status !== 405) {
          break;
        }
      } catch (error) {
        lastResponse = error;
      }
    }

    const error = await lastResponse?.json?.().catch(() => ({}));
    throw new Error(error.message || `API Error: ${lastResponse?.status || 404}`);
  },

  mockRequest(endpoint, options = {}) {
    return new Promise((resolve) => {
      setTimeout(() => {
        const method = (options.method || 'GET').toUpperCase();
        const body = options.body ? JSON.parse(options.body) : null;
        resolve(this.handleMock(endpoint, method, body));
      }, 100);
    });
  },

  handleMock(endpoint, method, body) {
    const parts = endpoint.replace(/^\//, '').split('/');
    const resource = parts[0];
    const id = parts[1] ? parseInt(parts[1]) : null;

    if (resource === 'dashboard') {
      return this.getDashboardStats();
    }

    if (resource === 'reports') {
      return { message: `Report "${parts[1]}" generated successfully`, data: [] };
    }

    const tableMap = {
      categories: 'categories',
      suppliers: 'suppliers',
      products: 'products',
      customers: 'customers',
      staff: 'staff',
      orders: 'orders',
      payments: 'payments',
      invoices: 'invoices',
      inventory: 'products'
    };


    
    const table = tableMap[resource];
    if (!table) return { error: 'Not found' };

    // Handle CRUD operations based on the method
    switch (method) {
      case 'GET':
        if (id) return DB[table].find(item => item.id === id) || null;
        return [...DB[table]];

      case 'POST': {
        const newItem = { ...body, id: nextId(table) };
        if (table === 'products') {
          newItem.updated_at = new Date().toISOString().split('T')[0];
        }
        DB[table].push(newItem);
        return newItem;
      }

      case 'PUT': {
        const idx = DB[table].findIndex(item => item.id === id);
        if (idx === -1) return null;
        DB[table][idx] = { ...DB[table][idx], ...body, id };
        if (table === 'products') {
          DB[table][idx].updated_at = new Date().toISOString().split('T')[0];
        }
        return DB[table][idx];
      }

      case 'DELETE': {
        const delIdx = DB[table].findIndex(item => item.id === id);
        if (delIdx === -1) return false;
        DB[table].splice(delIdx, 1);
        return true;
      }

      default:
        return null;
    }
  },

  getDashboardStats() {
    const totalSales = DB.orders
      .filter(o => o.status === 'Paid' || o.status === 'Delivered')
      .reduce((sum, o) => sum + o.total_amount, 0);

    const lowStock = DB.products.filter(p => p.quantity <= p.low_stock_threshold);

    const productSales = {};
    DB.order_items.forEach(item => {
      productSales[item.product_id] = (productSales[item.product_id] || 0) + item.quantity;
    });

    const topProducts = Object.entries(productSales)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 5)
      .map(([productId, qty]) => ({
        product_id: parseInt(productId),
        name: getProductName(parseInt(productId)),
        quantity: qty
      }));

    const recentOrders = [...DB.orders]
      .sort((a, b) => new Date(b.order_date) - new Date(a.order_date))
      .slice(0, 5)
      .map(o => ({
        ...o,
        customer_name: getCustomerName(o.customer_id)
      }));

    const salesByDay = {};
    DB.orders.forEach(o => {
      if (o.status === 'Paid' || o.status === 'Delivered') {
        const day = o.order_date.split('-')[2];
        salesByDay[day] = (salesByDay[day] || 0) + o.total_amount;
      }
    });

    return {
      totalProducts: DB.products.length,
      totalOrders: DB.orders.length,
      totalCustomers: DB.customers.length,
      totalSales,
      lowStock,
      recentOrders,
      topProducts,
      salesByDay
    };
  },

  // CRUD shortcuts
  getAll(resource) { return this.request(`/${resource}`); },
  getById(resource, id) { return this.request(`/${resource}/${id}`); },
  create(resource, data) { return this.request(`/${resource}`, { method: 'POST', body: data }); },
  update(resource, id, data) { return this.request(`/${resource}/${id}`, { method: 'PUT', body: data }); },
  delete(resource, id) { return this.request(`/${resource}/${id}`, { method: 'DELETE' }); },
  getDashboard() { return this.request('/dashboard/stats'); },
  generateReport(type) { return this.request(`/reports/${type}`); },

  updateStock(productId, newQuantity, notes = '') {
    return this.request(`/inventory/${productId}`, {
      method: 'PUT',
      body: { quantity: newQuantity, notes }
    });
  }
};
