/**
 * API Client Module
 * Maneja todas las llamadas a la API REST
 */

const API = {
    // URL base de la API
    baseURL: 'http://localhost:8000',
    token: null,

    /**
     * Ejecuta una solicitud HTTP
     */
    async request(method, endpoint, data = null) {
        const url = this.baseURL + endpoint;
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (this.token) {
            options.headers['Authorization'] = `Bearer ${this.token}`;
        }

        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Error en la solicitud');
            }

            return result;
        } catch (error) {
            throw error;
        }
    },

    // ==================== AUTENTICACIÓN ====================

    /**
     * Login
     */
    async login(email, password) {
        return this.request('POST', '/auth/login', { email, password });
    },

    /**
     * Obtiene el perfil del usuario actual
     */
    async getProfile() {
        return this.request('GET', '/me');
    },

    /**
     * Cambia la contraseña
     */
    async changePassword(oldPassword, newPassword, confirmPassword) {
        return this.request('POST', '/auth/change-password', {
            oldPassword,
            newPassword,
            confirmPassword
        });
    },

    // ==================== PRODUCTOS ====================

    /**
     * Obtiene todos los productos
     */
    async getProductos(page = 1, perPage = 20) {
        return this.request('GET', `/productos?page=${page}&per_page=${perPage}`);
    },

    /**
     * Obtiene un producto por ID
     */
    async getProductoById(id) {
        return this.request('GET', `/productos/${id}`);
    },

    /**
     * Crea un nuevo producto
     */
    async createProducto(data) {
        return this.request('POST', '/productos', data);
    },

    /**
     * Actualiza un producto
     */
    async updateProducto(id, data) {
        return this.request('PUT', `/productos/${id}`, data);
    },

    /**
     * Elimina un producto
     */
    async deleteProducto(id) {
        return this.request('DELETE', `/productos/${id}`);
    },

    /**
     * Busca productos
     */
    async searchProductos(keyword, page = 1, perPage = 20) {
        return this.request('GET', `/productos/search/${keyword}?page=${page}&per_page=${perPage}`);
    },

    /**
     * Obtiene productos por categoría
     */
    async getProductosByCategoria(categoriaId, page = 1, perPage = 20) {
        return this.request('GET', `/productos/categoria/${categoriaId}?page=${page}&per_page=${perPage}`);
    },

    // ==================== CATEGORÍAS ====================

    /**
     * Obtiene todas las categorías
     */
    async getCategorias() {
        return this.request('GET', '/categorias');
    },

    /**
     * Obtiene una categoría por ID
     */
    async getCategoriaById(id) {
        return this.request('GET', `/categorias/${id}`);
    },

    /**
     * Crea una nueva categoría
     */
    async createCategoria(data) {
        return this.request('POST', '/categorias', data);
    },

    /**
     * Actualiza una categoría
     */
    async updateCategoria(id, data) {
        return this.request('PUT', `/categorias/${id}`, data);
    },

    /**
     * Elimina una categoría
     */
    async deleteCategoria(id) {
        return this.request('DELETE', `/categorias/${id}`);
    },

    // ==================== PROVEEDORES ====================

    /**
     * Obtiene todos los proveedores
     */
    async getProveedores(page = 1, perPage = 20) {
        return this.request('GET', `/proveedores?page=${page}&per_page=${perPage}`);
    },

    /**
     * Obtiene un proveedor por ID
     */
    async getProveedorById(id) {
        return this.request('GET', `/proveedores/${id}`);
    },

    /**
     * Crea un nuevo proveedor
     */
    async createProveedor(data) {
        return this.request('POST', '/proveedores', data);
    },

    /**
     * Actualiza un proveedor
     */
    async updateProveedor(id, data) {
        return this.request('PUT', `/proveedores/${id}`, data);
    },

    /**
     * Elimina un proveedor
     */
    async deleteProveedor(id) {
        return this.request('DELETE', `/proveedores/${id}`);
    },

    // ==================== INVENTARIO ====================

    /**
     * Obtiene el inventario de un producto
     */
    async getInventario(productoId) {
        return this.request('GET', `/inventario/${productoId}`);
    },

    /**
     * Registra una entrada de inventario
     */
    async registrarEntrada(productoId, cantidad, motivo) {
        return this.request('POST', `/inventario/${productoId}/entrada`, {
            cantidad,
            motivo
        });
    },

    /**
     * Registra una salida de inventario
     */
    async registrarSalida(productoId, cantidad, motivo) {
        return this.request('POST', `/inventario/${productoId}/salida`, {
            cantidad,
            motivo
        });
    },

    /**
     * Registra un ajuste de inventario
     */
    async registrarAjuste(productoId, cantidadNueva, motivo) {
        return this.request('POST', `/inventario/${productoId}/ajuste`, {
            cantidad_nueva: cantidadNueva,
            motivo
        });
    },

    /**
     * Reserva un producto
     */
    async reservarProducto(productoId, cantidad) {
        return this.request('POST', `/inventario/${productoId}/reserva`, {
            cantidad
        });
    },

    /**
     * Libera una reserva
     */
    async liberarReserva(productoId, cantidad) {
        return this.request('POST', `/inventario/${productoId}/liberar-reserva`, {
            cantidad
        });
    },

    /**
     * Actualiza parámetros de inventario
     */
    async actualizarParametrosInventario(productoId, data) {
        return this.request('PUT', `/inventario/${productoId}/parametros`, data);
    },

    /**
     * Obtiene productos con bajo stock
     */
    async getProductosBajoStock() {
        return this.request('GET', '/inventario/bajo-stock');
    },

    /**
     * Valida la disponibilidad de un producto
     */
    async validarDisponibilidad(productoId, cantidad) {
        return this.request('GET', `/inventario/${productoId}/disponibilidad?cantidad=${cantidad}`);
    },

    // ==================== USUARIOS ====================

    /**
     * Obtiene todos los usuarios
     */
    async getUsuarios(page = 1, perPage = 20) {
        return this.request('GET', `/usuarios?page=${page}&per_page=${perPage}`);
    },

    /**
     * Obtiene un usuario por ID
     */
    async getUsuarioById(id) {
        return this.request('GET', `/usuarios/${id}`);
    },

    /**
     * Crea un nuevo usuario
     */
    async createUsuario(data) {
        return this.request('POST', '/auth/register', data);
    },

    /**
     * Actualiza un usuario
     */
    async updateUsuario(id, data) {
        return this.request('PUT', `/usuarios/${id}`, data);
    },

    /**
     * Elimina un usuario
     */
    async deleteUsuario(id) {
        return this.request('DELETE', `/usuarios/${id}`);
    },

    /**
     * Obtiene usuarios por rol
     */
    async getUsuariosByRol(rol) {
        return this.request('GET', `/usuarios/rol/${rol}`);
    }
};
