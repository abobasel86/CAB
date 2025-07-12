import axios from 'axios';
import { 
  User, 
  Transaction, 
  FieldSetting, 
  LoginRequest, 
  RegisterRequest, 
  AuthResponse, 
  PaginatedResponse,
  FieldConfig 
} from '../types';

const API_BASE_URL = 'http://localhost:8000/api'; // process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

console.log('Environment variables:', {
  REACT_APP_API_URL: process.env.REACT_APP_API_URL,
  NODE_ENV: process.env.NODE_ENV,
  API_BASE_URL: API_BASE_URL
});

// Debug: Log all process.env to see what's available
console.log('All environment variables:', process.env);

const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Add auth token to requests
api.interceptors.request.use((config) => {
  console.log('=== API REQUEST DEBUG ===');
  console.log('Method:', config.method?.toUpperCase());
  console.log('URL:', config.url);
  console.log('Base URL:', config.baseURL);
  console.log('Full URL:', (config.baseURL || '') + (config.url || ''));
  console.log('Headers:', config.headers);
  console.log('Data:', config.data);
  console.log('========================');
  
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle auth errors
api.interceptors.response.use(
  (response) => {
    console.log('API Response:', response.status, response.config.url);
    return response;
  },
  (error) => {
    console.error('API Error:', error);
    console.error('Error config:', error.config);
    console.error('Error response:', error.response);
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export const authApi = {
  login: async (data: LoginRequest): Promise<AuthResponse> => {
    console.log('authApi.login called with:', data);
    console.log('About to POST to /login');
    try {
      const response = await api.post('/login', data);
      console.log('Login response received:', response);
      return response.data;
    } catch (error) {
      console.error('Login request failed:', error);
      throw error;
    }
  },

  register: async (data: RegisterRequest): Promise<AuthResponse> => {
    const response = await api.post('/register', data);
    return response.data;
  },

  logout: async (): Promise<void> => {
    await api.post('/logout');
  },

  getCurrentUser: async (): Promise<User> => {
    const response = await api.get('/me');
    return response.data;
  },
};

export const transactionApi = {
  getTransactions: async (params?: {
    page?: number;
    per_page?: number;
    search?: string;
  }): Promise<PaginatedResponse<Transaction>> => {
    const response = await api.get('/transactions', { params });
    return response.data;
  },

  getTransaction: async (id: number): Promise<Transaction> => {
    const response = await api.get(`/transactions/${id}`);
    return response.data;
  },

  createTransaction: async (data: Partial<Transaction>): Promise<Transaction> => {
    const response = await api.post('/transactions', data);
    return response.data;
  },

  updateTransaction: async (id: number, data: Partial<Transaction>): Promise<Transaction> => {
    const response = await api.put(`/transactions/${id}`, data);
    return response.data;
  },

  deleteTransaction: async (id: number): Promise<void> => {
    await api.delete(`/transactions/${id}`);
  },
};

export const fieldSettingApi = {
  getFieldSettings: async (): Promise<FieldSetting[]> => {
    const response = await api.get('/field-settings');
    return response.data;
  },

  getFieldConfig: async (): Promise<FieldConfig> => {
    const response = await api.get('/field-config');
    return response.data;
  },

  updateFieldSetting: async (id: number, data: Partial<FieldSetting>): Promise<FieldSetting> => {
    const response = await api.put(`/field-settings/${id}`, data);
    return response.data;
  },
};

export const importApi = {
  importTransactions: async (file: File): Promise<{ message: string }> => {
    const formData = new FormData();
    formData.append('file', file);
    
    const response = await api.post('/import/transactions', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  downloadTemplate: async (): Promise<void> => {
    const response = await api.get('/import/template', {
      responseType: 'blob',
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'transaction_template.csv');
    document.body.appendChild(link);
    link.click();
    link.remove();
  },
};

export const exportApi = {
  exportToExcel: async (filters?: {
    search?: string;
    date_from?: string;
    date_to?: string;
  }): Promise<void> => {
    const response = await api.get('/export/excel', {
      params: filters,
      responseType: 'blob',
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.xlsx`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  },

  exportToPdf: async (filters?: {
    search?: string;
    date_from?: string;
    date_to?: string;
  }): Promise<void> => {
    const response = await api.get('/export/pdf', {
      params: filters,
      responseType: 'blob',
    });
    
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
  },
};

export default api;
