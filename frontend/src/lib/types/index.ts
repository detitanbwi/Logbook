export type UserRole = 'Admin' | 'Manager' | 'Staff';
export type LogbookStatus = 'DRAFT' | 'SUBMITTED' | 'REVIEWED' | 'REVERTED';

export interface User {
	id: string;
	nip: string;
	name: string;
	email: string;
	role: UserRole;
	manager_id: string | null;
	manager?: User;
	last_password_change?: string;
	created_at: string;
	updated_at: string;
	deleted_at?: string | null;
}

export interface KpiMaster {
	id: string;
	nama: string;
	status_aktif: boolean;
	created_at: string;
	updated_at: string;
}

export interface UserKpiAssignment {
	id: string;
	user_id: string;
	kpi_id: string;
	assigned_by: string;
	created_at: string;
	updated_at: string;
	// Untuk kemudahan di frontend
	kpi?: KpiMaster;
}

export interface LogbookKpiDetail {
	id: string;
	logbook_id: string;
	kpi_id: string;
	kpi_nama: string;
	kpi?: KpiMaster;
	is_finished: boolean;
	finished_at?: string;
	created_at: string;
	updated_at: string;
}

export interface Logbook {
	id: string;
	user_id: string;
	start_kerja: string;
	end_kerja?: string;
	lokasi_start?: string;
	lokasi_end?: string;
	gambar_bukti?: string[];
	status: LogbookStatus;
	rating?: number;
	reviewed_by?: string;
	reviewed_at?: string;
	created_at: string;
	updated_at: string;
	// Relasi
	details?: LogbookKpiDetail[];
	user?: User;
}

export type NotificationType =
	| 'KPI_ASSIGNMENT'
	| 'LOGBOOK_SUBMITTED'
	| 'LOGBOOK_REVERTED'
	| 'LOGBOOK_REVIEWED';

export interface Notification {
	id: string;
	user_id: string;
	title: string;
	message: string;
	type: NotificationType | string;
	reference_id?: string;
	is_read: boolean;
	data?: Record<string, unknown>;
	created_at: string;
	read_at?: string | null;
}

export interface AuditLog {
	id: string;
	table_name: string;
	record_id: string;
	action: 'created' | 'updated' | 'deleted';
	old_data: Record<string, unknown> | null;
	new_data: Record<string, unknown> | null;
	performed_by: string | null;
	performed_at: string;
	ip_address: string | null;
	user_agent: string | null;
	user?: Pick<User, 'id' | 'name' | 'nip' | 'role'>;
	created_at: string;
	updated_at: string;
}

export interface PaginationMeta {
	current_page: number;
	from: number | null;
	last_page: number;
	per_page: number;
	to: number | null;
	total: number;
	links: Array<{
		url: string | null;
		label: string;
		active: boolean;
	}>;
	path: string;
}

export interface PaginatedResponse<T> {
	data: T[];
	meta: PaginationMeta;
}

export interface TeamLocation {
	lat: number;
	lng: number;
	title: string;
	status: LogbookStatus;
}

export interface AdminDashboard {
	total_active_users: number;
	total_logbooks_this_month: number;
	total_logbooks_last_month?: number;
	pending_logbooks_count: number;
	active_kpis: number;
	logbook_trend?: number;
	logbooks_by_day?: Array<{ date: string; count: number }>;
	logbooks_by_status?: Array<{ status: string; count: number }>;
	users_by_role?: Array<{ role: string; count: number }>;
}

export interface ManagerDashboard {
	subordinates: Array<{
		id: string;
		name: string;
		total_kpi: number;
		completed_kpi: number;
		completion_rate: number;
	}>;
	pending_logbooks_count: number;
	team_locations?: TeamLocation[];
}

export interface StaffDashboard {
	personal_kpi_completion_rate: number;
	missed_logbooks_count: number;
	average_rating: number;
}
