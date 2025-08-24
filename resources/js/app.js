import "./bootstrap";

import Alpine from "alpinejs";
import persist from "@alpinejs/persist";
import focus from "@alpinejs/focus";

Alpine.plugin(persist);
Alpine.plugin(focus);

Alpine.data("auth", () => ({
    user: Alpine.$persist(null),
    token: Alpine.$persist(null),

    async login(email, password) {
        try {
            const response = await fetch("/api/login", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify({ email, password }),
            });

            const data = await response.json();

            if (data.success) {
                this.user = data.data.user;
                this.token = data.data.access_token;
                this.showToast("Login realizado com sucesso!", "success");
                return true;
            } else {
                this.showToast(data.message || "Erro no login", "error");
                return false;
            }
        } catch (error) {
            this.showToast("Erro de conexão", "error");
            return false;
        }
    },

    async logout() {
        try {
            if (this.token) {
                await fetch("/api/logout", {
                    method: "POST",
                    headers: {
                        Authorization: `Bearer ${this.token}`,
                        Accept: "application/json",
                    },
                });
            }
        } catch (error) {
            console.error("Erro no logout:", error);
        } finally {
            this.user = null;
            this.token = null;
            this.showToast("Logout realizado com sucesso!", "success");
        }
    },

    isLoggedIn() {
        return this.user && this.token;
    },

    isAdmin() {
        return this.user?.role === "admin";
    },

    isEmployee() {
        return this.user?.role === "employee";
    },

    showToast(message, type = "info") {
        window.dispatchEvent(
            new CustomEvent("show-toast", {
                detail: { message, type },
            })
        );
    },
}));

// Toast notification system
Alpine.data("toastManager", () => ({
    toasts: [],

    init() {
        window.addEventListener("show-toast", (e) => {
            this.addToast(e.detail.message, e.detail.type);
        });
    },

    addToast(message, type = "info") {
        const id = Date.now();
        this.toasts.push({ id, message, type });

        setTimeout(() => {
            this.removeToast(id);
        }, 5000);
    },

    removeToast(id) {
        this.toasts = this.toasts.filter((toast) => toast.id !== id);
    },
}));

// API helper functions
Alpine.data("api", () => ({
    async request(endpoint, options = {}) {
        const auth = Alpine.store("auth");
        const defaultOptions = {
            headers: {
                "Content-Type": "application/json",
                Accept: "application/json",
                ...(auth.token && { Authorization: `Bearer ${auth.token}` }),
            },
        };

        const config = { ...defaultOptions, ...options };

        try {
            const response = await fetch(`/api${endpoint}`, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || "Erro na requisição");
            }

            return data;
        } catch (error) {
            console.error("API Error:", error);
            throw error;
        }
    },

    async get(endpoint) {
        return this.request(endpoint);
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: "POST",
            body: JSON.stringify(data),
        });
    },

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: "PUT",
            body: JSON.stringify(data),
        });
    },

    async delete(endpoint) {
        return this.request(endpoint, {
            method: "DELETE",
        });
    },
}));

// Time Record functionality
Alpine.data("timeRecord", () => ({
    recording: false,
    todayStatus: null,

    async recordPunch() {
        console.log("=== RECORDING PUNCH ===");
        this.recording = true;
        try {
            const result = await window.apiRequest("/time-records", "POST");
            console.log("Punch result:", result);

            if (result.success) {
                this.showToast("Ponto registrado com sucesso!", "success");
                await this.loadTodayStatus();
            }
        } catch (error) {
            console.error("Error recording punch:", error);
            this.showToast(error.message || "Erro ao registrar ponto", "error");
        } finally {
            this.recording = false;
        }
    },

    async loadTodayStatus() {
        try {
            const result = await window.apiRequest(
                "/time-records/today",
                "GET"
            );
            this.todayStatus = result.data;
            console.log("Today status loaded:", this.todayStatus);
        } catch (error) {
            console.error("Erro ao carregar status:", error);
        }
    },

    showToast(message, type = "info") {
        // Simple alert for now, can be enhanced later
        alert(message);
    },

    init() {
        this.loadTodayStatus();
    },
}));

// Employee Dashboard functionality
Alpine.data("employeeDashboard", () => ({
    recording: false,
    todayStatus: null,
    summaryData: null,
    loadingStats: true,
    weekRecords: 0,
    averagePerDay: 0,

    async init() {
        await Promise.all([this.loadTodayStatus(), this.loadSummary()]);
    },

    async recordPunch() {
        console.log("=== RECORDING PUNCH ===");
        this.recording = true;
        try {
            const result = await window.apiRequest("/time-records", "POST");
            console.log("Punch result:", result);

            if (result.success) {
                this.showToast("Ponto registrado com sucesso!", "success");
                await Promise.all([this.loadTodayStatus(), this.loadSummary()]);
            }
        } catch (error) {
            console.error("Error recording punch:", error);
            this.showToast(error.message || "Erro ao registrar ponto", "error");
        } finally {
            this.recording = false;
        }
    },

    async loadTodayStatus() {
        try {
            const result = await window.apiRequest(
                "/time-records/today",
                "GET"
            );
            this.todayStatus = result.data;
            console.log("Today status loaded:", this.todayStatus);
        } catch (error) {
            console.error("Erro ao carregar status:", error);
        }
    },

    async loadSummary() {
        this.loadingStats = true;
        try {
            const today = new Date();
            const startDate = new Date(today);
            startDate.setDate(today.getDate() - 30);

            const params = new URLSearchParams();
            params.append("start_date", startDate.toISOString().split("T")[0]);
            params.append("end_date", today.toISOString().split("T")[0]);

            const result = await window.apiRequest(
                `/time-records/summary?${params}`,
                "GET"
            );
            console.log("Summary loaded:", result);

            if (result.success) {
                this.summaryData = result.data;

                this.weekRecords =
                    this.summaryData?.last_7_days?.daily_records?.reduce(
                        (total, day) => total + day.count,
                        0
                    ) || 0;

                const totalRecords =
                    this.summaryData?.this_month?.total_records || 0;
                const daysInPeriod = 30;
                this.averagePerDay = (totalRecords / daysInPeriod).toFixed(1);
            }
        } catch (error) {
            console.error("Erro ao carregar resumo:", error);
        } finally {
            this.loadingStats = false;
        }
    },

    showToast(message, type = "info") {
        alert(message);
    },
}));

// Loading states
Alpine.data("loading", () => ({
    states: {},

    start(key) {
        this.states[key] = true;
    },

    stop(key) {
        this.states[key] = false;
    },

    isLoading(key) {
        return this.states[key] || false;
    },
}));

// Form utilities
Alpine.data("form", (initialData = {}) => ({
    data: { ...initialData },
    errors: {},
    submitting: false,

    setError(field, message) {
        this.errors[field] = message;
    },

    clearErrors() {
        this.errors = {};
    },

    hasError(field) {
        return this.errors[field];
    },

    reset() {
        this.data = { ...initialData };
        this.errors = {};
        this.submitting = false;
    },
}));

// Date utilities
Alpine.data("dateUtils", () => ({
    formatDate(date, format = "dd/MM/yyyy") {
        if (!date) return "";
        const d = new Date(date);
        return d.toLocaleDateString("pt-BR");
    },

    formatDateTime(date) {
        if (!date) return "";
        const d = new Date(date);
        return d.toLocaleString("pt-BR");
    },

    formatTime(date) {
        if (!date) return "";
        const d = new Date(date);
        return d.toLocaleTimeString("pt-BR");
    },
}));

// Start Alpine
window.Alpine = Alpine;

// Global helpers for all pages
window.apiRequest = async function (endpoint, method = "GET", data = null) {
    const token = localStorage.getItem("auth_token");

    const config = {
        method: method.toUpperCase(),
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
            ...(token && { Authorization: `Bearer ${token}` }),
        },
    };

    if (data && ["POST", "PUT", "PATCH"].includes(config.method)) {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(`/api${endpoint}`, config);

        let responseData;
        try {
            responseData = await response.json();
        } catch (e) {
            responseData = { message: "Invalid response format" };
        }

        if (response.status === 401) {
            console.log(
                "Authentication failed, clearing localStorage and redirecting"
            );
            localStorage.removeItem("auth_token");
            localStorage.removeItem("user_data");
            window.location.href = "/login";
            return;
        }

        if (!response.ok) {
            const error = new Error(
                responseData.message || `HTTP ${response.status}`
            );
            error.status = response.status;
            error.errors = responseData.errors || {};
            throw error;
        }

        return responseData;
    } catch (error) {
        console.error("API Error:", error);
        throw error;
    }
};

Alpine.start();
