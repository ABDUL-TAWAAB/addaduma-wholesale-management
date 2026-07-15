// /**
//  * Mock data store - mirrors SQL database schema
//  * Replace with API calls when backend is connected
//  */

// const DB = {
//   categories: [
//     { id: 1, name: 'Beverages' },
//     { id: 2, name: 'Snacks' },
//     { id: 3, name: 'Household' },
//     { id: 4, name: 'Personal Care' },
//     { id: 5, name: 'Frozen Foods' }
//   ],

//   suppliers: [
//     { id: 1, name: 'Coca-Cola Ghana', phone: '0302-123456', address: 'Accra Industrial Area' },
//     { id: 2, name: 'Unilever Ghana', phone: '0302-234567', address: 'Tema Free Zone' },
//     { id: 3, name: 'Nestle Ghana', phone: '0302-345678', address: 'Tema' },
//     { id: 4, name: 'Local Distributors Ltd', phone: '0302-456789', address: 'Kumasi' },
//     { id: 5, name: 'Fresh Foods Co', phone: '0302-567890', address: 'Tamale' }
//   ],

//   products: [
//     { id: 1, name: 'Coca-Cola 500ml', category_id: 1, supplier_id: 1, unit_price: 3.50, quantity: 8, low_stock_threshold: 15, updated_at: '2026-07-04' },
//     { id: 2, name: 'Fanta Orange 500ml', category_id: 1, supplier_id: 1, unit_price: 3.50, quantity: 45, low_stock_threshold: 15, updated_at: '2026-07-03' },
//     { id: 3, name: 'Sprite 500ml', category_id: 1, supplier_id: 1, unit_price: 3.50, quantity: 120, low_stock_threshold: 15, updated_at: '2026-07-04' },
//     { id: 4, name: 'Omo Detergent 1kg', category_id: 3, supplier_id: 2, unit_price: 25.00, quantity: 60, low_stock_threshold: 10, updated_at: '2026-07-02' },
//     { id: 5, name: 'Close Up Toothpaste', category_id: 4, supplier_id: 2, unit_price: 8.50, quantity: 5, low_stock_threshold: 10, updated_at: '2026-07-01' },
//     { id: 6, name: 'Maggi Cubes Pack', category_id: 2, supplier_id: 3, unit_price: 12.00, quantity: 200, low_stock_threshold: 20, updated_at: '2026-07-04' },
//     { id: 7, name: 'Milo 400g', category_id: 1, supplier_id: 3, unit_price: 35.00, quantity: 75, low_stock_threshold: 15, updated_at: '2026-07-03' },
//     { id: 8, name: 'Vita Milk 1L', category_id: 1, supplier_id: 3, unit_price: 18.00, quantity: 90, low_stock_threshold: 20, updated_at: '2026-07-04' },
//     { id: 9, name: 'Indomie Noodles', category_id: 2, supplier_id: 4, unit_price: 4.50, quantity: 150, low_stock_threshold: 25, updated_at: '2026-07-02' },
//     { id: 10, name: 'Peak Milk 170g', category_id: 1, supplier_id: 3, unit_price: 6.00, quantity: 12, low_stock_threshold: 15, updated_at: '2026-07-04' }
//   ],

//   customers: [
//     { id: 1, name: 'Kwame Mensah', phone: '0244-111111' },
//     { id: 2, name: 'Ama Osei', phone: '0244-222222' },
//     { id: 3, name: 'Kofi Asante', phone: '0244-333333' },
//     { id: 4, name: 'Abena Darko', phone: '0244-444444' },
//     { id: 5, name: 'Yaw Boateng', phone: '0244-555555' },
//     { id: 6, name: 'Efua Addo', phone: '0244-666666' },
//     { id: 7, name: 'Nana Agyeman', phone: '0244-777777' },
//     { id: 8, name: 'Akua Frimpong', phone: '0244-888888' }
//   ],

//   staff: [
//     { id: 1, name: 'Admin User', phone: '0302-000001', role: 'Administrator' },
//     { id: 2, name: 'Grace Adom', phone: '0302-000002', role: 'Manager' },
//     { id: 3, name: 'Samuel Tetteh', phone: '0302-000003', role: 'Sales' },
//     { id: 4, name: 'Mary Ofori', phone: '0302-000004', role: 'Inventory' },
//     { id: 5, name: 'Daniel Kusi', phone: '0302-000005', role: 'Cashier' }
//   ],

//   orders: [
//     { id: 1, customer_id: 1, order_date: '2026-07-01', total_amount: 450.00, status: 'Paid' },
//     { id: 2, customer_id: 2, order_date: '2026-07-02', total_amount: 320.50, status: 'Paid' },
//     { id: 3, customer_id: 3, order_date: '2026-07-03', total_amount: 175.00, status: 'Pending' },
//     { id: 4, customer_id: 4, order_date: '2026-07-03', total_amount: 890.00, status: 'Paid' },
//     { id: 5, customer_id: 5, order_date: '2026-07-04', total_amount: 210.00, status: 'Pending' },
//     { id: 6, customer_id: 6, order_date: '2026-06-28', total_amount: 560.00, status: 'Paid' },
//     { id: 7, customer_id: 7, order_date: '2026-06-29', total_amount: 125.00, status: 'Delivered' },
//     { id: 8, customer_id: 8, order_date: '2026-06-30', total_amount: 780.00, status: 'Paid' }
//   ],

//   order_items: [
//     { id: 1, order_id: 1, product_id: 1, quantity: 50, unit_price: 3.50, subtotal: 175.00 },
//     { id: 2, order_id: 1, product_id: 3, quantity: 50, unit_price: 3.50, subtotal: 175.00 },
//     { id: 3, order_id: 1, product_id: 6, quantity: 10, unit_price: 12.00, subtotal: 120.00 },
//     { id: 4, order_id: 2, product_id: 4, quantity: 8, unit_price: 25.00, subtotal: 200.00 },
//     { id: 5, order_id: 2, product_id: 5, quantity: 14, unit_price: 8.50, subtotal: 119.00 },
//     { id: 6, order_id: 3, product_id: 9, quantity: 30, unit_price: 4.50, subtotal: 135.00 },
//     { id: 7, order_id: 4, product_id: 7, quantity: 20, unit_price: 35.00, subtotal: 700.00 },
//     { id: 8, order_id: 4, product_id: 8, quantity: 10, unit_price: 18.00, subtotal: 180.00 }
//   ],

//   payments: [
//     { id: 1, order_id: 1, payment_date: '2026-07-01', method: 'Mobile Money', amount: 450.00 },
//     { id: 2, order_id: 2, payment_date: '2026-07-02', method: 'Cash', amount: 320.50 },
//     { id: 3, order_id: 4, payment_date: '2026-07-03', method: 'Bank Transfer', amount: 890.00 },
//     { id: 4, order_id: 6, payment_date: '2026-06-28', method: 'Cash', amount: 560.00 },
//     { id: 5, order_id: 7, payment_date: '2026-06-29', method: 'Mobile Money', amount: 125.00 },
//     { id: 6, order_id: 8, payment_date: '2026-06-30', method: 'Bank Transfer', amount: 780.00 }
//   ],

//   invoices: [
//     { id: 1, order_id: 1, invoice_date: '2026-07-01', total_amount: 450.00 },
//     { id: 2, order_id: 2, invoice_date: '2026-07-02', total_amount: 320.50 },
//     { id: 3, order_id: 4, invoice_date: '2026-07-03', total_amount: 890.00 },
//     { id: 4, order_id: 6, invoice_date: '2026-06-28', total_amount: 560.00 },
//     { id: 5, order_id: 7, invoice_date: '2026-06-29', total_amount: 125.00 },
//     { id: 6, order_id: 8, invoice_date: '2026-06-30', total_amount: 780.00 }
//   ],

//   inventory_logs: [
//     { id: 1, product_id: 1, change_amount: -50, log_date: '2026-07-01', type: 'Sale' },
//     { id: 2, product_id: 3, change_amount: -50, log_date: '2026-07-01', type: 'Sale' },
//     { id: 3, product_id: 4, change_amount: 100, log_date: '2026-07-02', type: 'Stock In' },
//     { id: 4, product_id: 5, change_amount: -14, log_date: '2026-07-02', type: 'Sale' },
//     { id: 5, product_id: 9, change_amount: 200, log_date: '2026-07-02', type: 'Stock In' }
//   ]
// };

// // Auto-increment ID helper
// function nextId(table) {
//   if (!DB[table] || DB[table].length === 0) return 1;
//   return Math.max(...DB[table].map(item => item.id)) + 1;
// }

// Lookup helpers
function getCategoryName(id) {
  const cat = DB.categories.find(c => c.id === id);
  return cat ? cat.name : 'Unknown';
}

function getSupplierName(id) {
  const sup = DB.suppliers.find(s => s.id === id);
  return sup ? sup.name : 'Unknown';
}

function getCustomerName(id) {
  const cust = DB.customers.find(c => c.id === id);
  return cust ? cust.name : 'Unknown';
}

function getProductName(id) {
  const prod = DB.products.find(p => p.id === id);
  return prod ? prod.name : 'Unknown';
}

function formatCurrency(amount) {
  return `GH₵ ${Number(amount).toLocaleString('en-GH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatDate(dateStr) {
  const d = new Date(dateStr);
  if (Number.isNaN(d.getTime())) return 'Unknown';
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function getStatusClass(status) {
  const map = {
    Paid: 'status-paid',
    Pending: 'status-pending',
    Delivered: 'status-delivered',
    Cancelled: 'status-cancelled'
  };
  return map[status] || 'status-pending';
}
