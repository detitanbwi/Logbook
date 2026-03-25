export type UserRole = 'SuperAdmin' | 'Admin' | 'Staff';
export type LogbookStatus = 'SUBMITTED' | 'ACCEPTED' | 'REJECTED';

export interface User {
	id: string;
	npp: string;
	nama: string;
	/** @deprecated temporary compatibility alias */
	nip?: string;
	/** @deprecated temporary compatibility alias */
	name?: string;
	email: string;
	role: UserRole;
	manager_id: string | null;
	has_subordinates?: boolean;
	foto?: string | null;
	foto_url?: string | null;
	manager?: User;
	last_password_change?: string;
	tempat_lahir?: string | null;
	tanggal_lahir?: string | null;
	nik?: string | null;
	npwp?: string | null;
	alamat?: string | null;
	status_kawin?: string | null;
	riwayat_pendidikan?: Record<string, unknown>[] | null;
	riwayat_karir?: Record<string, unknown>[] | null;
	created_at: string;
	updated_at: string;
	deleted_at?: string | null;
}

export interface KpiMaster {
	id: string;
	nama: string;
	target_angka: number;
	satuan: string;
	deskripsi?: string | null;
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
	kpi?: KpiMaster;
}

export interface LogbookKpiDetail {
	id: string;
	logbook_id: string;
	kpi_id: string;
	kpi_nama: string;
	target_angka: number;
	satuan: string;
	capaian_angka: number;
	lampiran_file?: string | null;
	kpi?: KpiMaster;
	finished_at?: string | null;
	created_at: string;
	updated_at: string;
}

export interface Logbook {
	id: string;
	user_id: string;
	tanggal: string;
	start_kerja: string;
	end_kerja?: string | null;
	lokasi?: string | null;
	lokasi_lat?: number | null;
	lokasi_lng?: number | null;
	status: LogbookStatus;
	rating?: number | null;
	reviewer_comment?: string | null;
	reviewed_by?: string | null;
	reviewed_at?: string | null;
	created_at: string;
	updated_at: string;
	gross_work_minutes?: number;
	break_overlap_minutes?: number;
	net_work_minutes?: number;
	details?: LogbookKpiDetail[];
	user?: User;
	reviewer?: User;
}

export type NotificationType =
	| 'KPI_ASSIGNMENT'
	| 'LOGBOOK_SUBMITTED'
	| 'LOGBOOK_ACCEPTED'
	| 'LOGBOOK_REJECTED';

export interface Notification {
	id: string;
	user_id?: string | null;
	title?: string | null;
	message: string | null;
	preview_message?: string | null;
	type: NotificationType | string;
	reference_id?: string | null;
	is_read?: boolean;
	data?: Record<string, unknown> | null;
	target_path?: string | null;
	target_params?: Record<string, unknown> | null;
	created_at: string;
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
	user?: Pick<User, 'id' | 'nama' | 'npp' | 'role'> & { name?: string; nip?: string };
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
		nama: string;
		name?: string;
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

export interface DailyStaffSummary {
	id: string;
	user_id: string;
	tanggal: string;
	total_logbooks: number;
	submitted_logbooks: number;
	accepted_logbooks: number;
	rejected_logbooks: number;
	total_work_minutes: number;
	total_kpi: number;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
	user?: User;
}

export interface DailyKpiSummary {
	id: string;
	user_id: string;
	kpi_id: string;
	tanggal: string;
	kpi_nama: string;
	satuan: string;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
	total_lampiran: number;
}

export interface StaffPerformanceSummaryItem {
	user_id: string;
	nama: string;
	npp: string;
	total_logbooks: number;
	accepted_logbooks: number;
	rejected_logbooks: number;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
}

export interface StaffPerformanceSummaryResponse {
	date_from: string;
	date_to: string;
	items: StaffPerformanceSummaryItem[];
}

export interface LogbookDuration {
	logbook_id: string;
	tanggal: string;
	start_kerja: string;
	end_kerja: string | null;
	gross_work_minutes: number;
	break_overlap_minutes: number;
	net_work_minutes: number;
}

export interface PeriodSummary {
	date_from: string;
	date_to: string;
	total_logbooks: number;
	submitted_logbooks: number;
	accepted_logbooks: number;
	rejected_logbooks: number;
	total_work_minutes: number;
	total_kpi: number;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
}

export interface KpiPeriodItem {
	kpi_id: string;
	kpi_nama: string;
	satuan: string;
	target_angka_total: number;
	capaian_angka_total: number;
	progress_percent: number;
	total_lampiran: number;
}

export interface KpiPeriodSummary {
	date_from: string;
	date_to: string;
	items: KpiPeriodItem[];
}
