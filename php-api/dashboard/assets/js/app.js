/**
 * Main Application Module
 */

let currentUser = null;
let currentPage = 'dashboard';
let charts = {};

// ==================== INICIALIZACIÓN ====================

document.addEventListener('DOMContentLoaded', () => {
    console.log('App iniciada');

    // Verificar si hay token guardado
    const savedToken = localStorage.getItem('token');
    if (savedToken) {
        API.token = savedToken;
        loadApp();
    } else {
        showLoginModal();
    }

    // Event Listeners
    setupEventListeners();
});

function setupEventListeners() {
    // Login
    document.getElementById('loginForm').addEventListener('submit', handleLogin);

    // Navbar links
    document.querySelectorAll('[data-page]').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const page = e.target.closest('[data-page]').dataset.page;
            navigateTo(page);
        });
    });

    // User dropdown
    document.getElementById('logoutLink')?.addEventListener('click', handleLogout);
    document.getElementById('profileLink')?.addEventListener('click', (e) => {
        e.preventDefault();
        showAlert('Perfil del usuario', 'Nombre: ' + currentUser.nombre);
    });

    // Buscar productos
    document.getElementById('searchProductos')?.addEventListener('input', debounce(loadProductos, 300));
    document.getElementById('filterCategoria')?.addEventListener('change', loadProductos);

    // Botones
    document.getElementById('btnNuevoProducto')?.addEventListener('click', () => {
        alert('Funcionalidad de crear producto disponible en versión expandida');
    });

    document.getElementById('btnNuevoUsuario')?.addEventListener('click', () => {
        alert('Funcionalidad de crear usuario disponible en versión expandida');
    });
}

// ==================== LOGIN ====================

async function handleLogin(e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    try {
        const response = await API.login(email, password);
        
        API.token = response.data.token;
        currentUser = response.data.user;

        // Guardar token
        localStorage.setItem('token', API.token);
        localStorage.setItem('user', JSON.stringify(currentUser));

        // Cargar aplicación
        loadApp();
    } catch (error) {
        showAlert('Error', error.message, 'error');
    }
}

async function loadApp() {
    try {
        // Obtener perfil del usuario
        const response = await API.getProfile();
        currentUser = response.data;

        // Mostrar interfaz
        document.getElementById('loginModal').style.display = 'none';
        document.getElementById('navbar').style.display = 'block';
        document.getElementById('mainContent').style.display = 'block';

        // Actualizar navbar
        document.getElementById('userName').textContent = currentUser.nombre;

        // Mostrar elementos según rol
        updateUIByRole();

        // Cargar dashboard
        navigateTo('dashboard');
    } catch (error) {
        console.error('Error cargando app:', error);
        handleLogout();
    }
}

function updateUIByRole() {
    const isAdmin = currentUser.rol === 'admin';
    const isVendedor = currentUser.rol === 'vendedor';

    // Mostrar menús según rol
    document.getElementById('productosNav').style.display = isVendedor || isAdmin ? 'block' : 'none';
    document.getElementById('inventarioNav').style.display = isVendedor || isAdmin ? 'block' : 'none';
    document.getElementById('usuariosNav').style.display = isAdmin ? 'block' : 'none';

    // Mostrar botones según rol
    document.getElementById('btnNuevoProducto').style.display = isAdmin ? 'block' : 'none';
    document.getElementById('btnNuevoUsuario').style.display = isAdmin ? 'block' : 'none';
}

function handleLogout(e) {
    if (e) e.preventDefault();
    
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    API.token = null;
    currentUser = null;

    document.getElementById('navbar').style.display = 'none';
    document.getElementById('mainContent').style.display = 'none';
    
    showLoginModal();
    document.getElementById('loginForm').reset();
}

function showLoginModal() {
    document.getElementById('loginModal').style.display = 'flex';
}

// ==================== NAVEGACIÓN ====================

function navigateTo(page) {
    currentPage = page;

    // Ocultar todas las páginas
    document.querySelectorAll('.page-content').forEach(el => {
        el.style.display = 'none';
    });

    // Mostrar página seleccionada
    const pageEl = document.getElementById(page + 'Page');
    if (pageEl) {
        pageEl.style.display = 'block';
    }

    // Actualizar navbar
    document.querySelectorAll('[data-page]').forEach(link => {
        link.closest('li')?.classList.remove('active');
    });
    document.querySelector(`[data-page="${page}"]`)?.closest('li')?.classList.add('active');

    // Cargar datos de la página
    switch (page) {
        case 'dashboard':
            loadDashboard();
            break;
        case 'productos':
            loadProductos();
            break;
        case 'inventario':
            loadInventario();
            break;
        case 'usuarios':
            loadUsuarios();
            break;
    }
}

// ==================== DASHBOARD ====================

async function loadDashboard() {
    try {
        // Cargar datos
        const productosRes = await API.getProductos(1, 1000);
        const usuariosRes = await API.getUsuarios(1, 1000);
        const categoriesRes = await API.getCategorias();

        const productos = productosRes.data || [];
        const usuarios = usuariosRes.data || [];
        const categorias = categoriesRes.data || [];

        // Actualizar stats
        document.getElementById('totalProductos').textContent = productos.length;
        document.getElementById('totalUsuarios').textContent = usuarios.length;

        // Calcular stock
        let totalStock = 0;
        let stockBajo = 0;
        const stockPorCategoria = {};
        const stockData = {};

        for (const producto of productos) {
            if (producto.inventario) {
                const disponible = producto.inventario.cantidad_disponible || 0;
                totalStock += disponible;

                if (disponible < producto.inventario.cantidad_minima) {
                    stockBajo++;
                }

                // Stock por categoría
                const cat = producto.categoria_nombre || 'Sin categoría';
                stockPorCategoria[cat] = (stockPorCategoria[cat] || 0) + 1;
                stockData[producto.nombre] = disponible;
            }
        }

        document.getElementById('totalStock').textContent = totalStock;
        document.getElementById('stockBajo').textContent = stockBajo;

        // Gráfico de categorías
        createChartCategorias(stockPorCategoria);

        // Gráfico de stock
        createChartStock(stockData);

        // Últimos productos
        loadTopProducts(productos);

    } catch (error) {
        console.error('Error cargando dashboard:', error);
        showAlert('Error', error.message, 'error');
    }
}

function createChartCategorias(data) {
    const ctx = document.getElementById('chartCategories')?.getContext('2d');
    if (!ctx) return;

    // Destruir gráfico anterior
    if (charts.categories) charts.categories.destroy();

    charts.categories = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(data),
            datasets: [{
                data: Object.values(data),
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8',
                    '#6f42c1'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function createChartStock(data) {
    const ctx = document.getElementById('chartStock')?.getContext('2d');
    if (!ctx) return;

    if (charts.stock) charts.stock.destroy();

    const labels = Object.keys(data).slice(0, 10);
    const values = Object.values(data).slice(0, 10);

    charts.stock = new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Cantidad en Stock',
                data: values,
                backgroundColor: '#007bff',
                borderColor: '#0056b3',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function loadTopProducts(productos) {
    const tbody = document.getElementById('topProductsBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    productos.slice(0, 10).forEach(producto => {
        const stock = producto.inventario?.cantidad_disponible || 0;
        const estatus = stock > 0 ? '<span class="badge bg-success">Disponible</span>' : 
                       '<span class="badge bg-danger">Agotado</span>';

        const row = `
            <tr>
                <td><strong>${producto.nombre}</strong></td>
                <td>${producto.sku}</td>
                <td>${producto.categoria_nombre || '-'}</td>
                <td>$${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                <td>${stock}</td>
                <td>${estatus}</td>
            </tr>
        `;

        tbody.innerHTML += row;
    });
}

// ==================== PRODUCTOS ====================

async function loadProductos() {
    try {
        const page = 1;
        const perPage = 20;
        const searchTerm = document.getElementById('searchProductos')?.value || '';
        const categoriaFilter = document.getElementById('filterCategoria')?.value || '';

        let response;
        if (searchTerm) {
            response = await API.searchProductos(searchTerm, page, perPage);
        } else if (categoriaFilter) {
            response = await API.getProductosByCategoria(categoriaFilter, page, perPage);
        } else {
            response = await API.getProductos(page, perPage);
        }

        // Cargar categorías en el filtro
        const categorias = await API.getCategorias();
        const filterSelect = document.getElementById('filterCategoria');
        if (filterSelect && filterSelect.children.length === 1) {
            categorias.data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.nombre;
                filterSelect.appendChild(option);
            });
        }

        // Mostrar productos
        const tbody = document.getElementById('productosTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        const productos = response.data || response;

        if (productos.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No hay productos</td></tr>';
            return;
        }

        productos.forEach(producto => {
            const stock = producto.inventario?.cantidad_disponible || 0;
            const estatus = stock > 0 ? '<span class="badge bg-success">Disponible</span>' : 
                           '<span class="badge bg-danger">Agotado</span>';

            const row = `
                <tr>
                    <td><strong>${producto.nombre}</strong></td>
                    <td>${producto.sku}</td>
                    <td>${producto.categoria_nombre || '-'}</td>
                    <td>$${parseFloat(producto.precio_unitario).toFixed(2)}</td>
                    <td>${stock}</td>
                    <td>${estatus}</td>
                </tr>
            `;

            tbody.innerHTML += row;
        });

    } catch (error) {
        console.error('Error cargando productos:', error);
        showAlert('Error', error.message, 'error');
    }
}

// ==================== INVENTARIO ====================

async function loadInventario() {
    try {
        const response = await API.getProductos(1, 1000);
        const productos = response.data || [];

        const tbody = document.getElementById('inventarioTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        for (const producto of productos) {
            const inv = producto.inventario;
            if (!inv) continue;

            const disponible = inv.cantidad_disponible || 0;
            const estadoBadge = disponible >= inv.cantidad_minima 
                ? '<span class="badge bg-success">OK</span>'
                : '<span class="badge bg-warning">BAJO</span>';

            const row = `
                <tr>
                    <td><strong>${producto.nombre}</strong></td>
                    <td>${disponible}</td>
                    <td>${inv.cantidad_reservada || 0}</td>
                    <td>${inv.cantidad_minima || 0}</td>
                    <td>${inv.cantidad_maxima || 0}</td>
                    <td>${inv.ubicacion_almacen || '-'}</td>
                </tr>
            `;

            tbody.innerHTML += row;
        }

        // Productos con bajo stock
        const bajoStockAlert = document.getElementById('stockBajoAlert');
        const bajoStockCount = document.getElementById('stockBajoCount');
        const productosBajos = productos.filter(p => 
            p.inventario && p.inventario.cantidad_disponible < p.inventario.cantidad_minima
        );

        if (productosBajos.length > 0) {
            bajoStockCount.textContent = productosBajos.length;
            bajoStockAlert.style.display = 'block';
        } else {
            bajoStockAlert.style.display = 'none';
        }

    } catch (error) {
        console.error('Error cargando inventario:', error);
        showAlert('Error', error.message, 'error');
    }
}

// ==================== USUARIOS ====================

async function loadUsuarios() {
    try {
        const response = await API.getUsuarios(1, 1000);
        const usuarios = response.data || [];

        const tbody = document.getElementById('usuariosTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        usuarios.forEach(usuario => {
            const rolBadge = usuario.rol === 'admin' 
                ? '<span class="badge bg-danger">Admin</span>'
                : usuario.rol === 'vendedor'
                ? '<span class="badge bg-primary">Vendedor</span>'
                : '<span class="badge bg-secondary">Consultor</span>';

            const estatusBadge = usuario.activo
                ? '<span class="badge bg-success">Activo</span>'
                : '<span class="badge bg-danger">Inactivo</span>';

            const row = `
                <tr>
                    <td><strong>${usuario.nombre}</strong></td>
                    <td>${usuario.email}</td>
                    <td>${rolBadge}</td>
                    <td>${estatusBadge}</td>
                </tr>
            `;

            tbody.innerHTML += row;
        });

    } catch (error) {
        console.error('Error cargando usuarios:', error);
        showAlert('Error', error.message, 'error');
    }
}

// ==================== UTILIDADES ====================

function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

function showAlert(title, message, type = 'info') {
    const alertHTML = `
        <div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show" role="alert">
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    const alertContainer = document.createElement('div');
    alertContainer.innerHTML = alertHTML;
    document.body.appendChild(alertContainer);

    setTimeout(() => {
        alertContainer.remove();
    }, 5000);
}

// Manejador de errores global
window.addEventListener('error', (event) => {
    console.error('Error global:', event.error);
});
