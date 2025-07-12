export interface User {
  id: number;
  name: string;
  email: string;
  role: 'admin' | 'importer' | 'editor' | 'viewer';
  created_at: string;
  updated_at: string;
  canEdit?: () => boolean;
  canImport?: () => boolean;
  isAdmin?: () => boolean;
  isEditor?: () => boolean;
  isImporter?: () => boolean;
  isViewer?: () => boolean;
}

export interface Transaction {
  id: number;
  post_date?: string;
  value_date?: string;
  description?: string;
  doctor_name?: string;
  reference?: string;
  amount?: number;
  balance?: number;
  specialist?: number;
  registration?: number;
  yearly?: number;
  exam?: number;
  certificate?: number;
  newsletters?: number;
  other?: number;
  visa?: number;
  unspecified?: number;
  summary?: number;
  commission?: number;
  total?: number;
  difference?: number;
  inward_number?: string;
  inward_date?: string;
  notes?: string;
  is_locked: boolean;
  completed_by_user_id?: number;
  completed_at?: string;
  created_at: string;
  updated_at: string;
  completed_by_user?: User;
}

export interface FieldSetting {
  id: number;
  field_name: string;
  field_type: 'imported' | 'manual' | 'calculated';
  is_editable: boolean;
  display_order: number;
  created_at: string;
  updated_at: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: 'admin' | 'importer' | 'editor' | 'viewer';
}

export interface AuthResponse {
  access_token: string;
  token_type: string;
  user: User;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  per_page: number;
  total: number;
  last_page: number;
  from: number;
  to: number;
}

export interface FieldConfig {
  imported: Array<{
    name: string;
    editable: boolean;
    order: number;
  }>;
  manual: Array<{
    name: string;
    editable: boolean;
    order: number;
  }>;
  calculated: Array<{
    name: string;
    editable: boolean;
    order: number;
  }>;
}
