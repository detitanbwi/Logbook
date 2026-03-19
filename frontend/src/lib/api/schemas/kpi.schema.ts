import * as v from 'valibot';

export const MasterKpiCreateSchema = v.object({
	nama: v.pipe(v.string(), v.minLength(1, 'Nama KPI wajib diisi'), v.maxLength(255)),
	status_aktif: v.optional(v.boolean(), true)
});
export type MasterKpiCreateDto = v.InferOutput<typeof MasterKpiCreateSchema>;

export const MasterKpiUpdateSchema = v.object({
	nama: v.optional(v.pipe(v.string(), v.maxLength(255))),
	status_aktif: v.optional(v.boolean())
});
export type MasterKpiUpdateDto = v.InferOutput<typeof MasterKpiUpdateSchema>;

export const KpiAssignmentSchema = v.object({
	user_id: v.string(),
	kpi_id: v.string()
});
export type KpiAssignmentDto = v.InferOutput<typeof KpiAssignmentSchema>;
