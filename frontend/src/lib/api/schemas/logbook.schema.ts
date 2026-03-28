import * as v from 'valibot';

export const StartLogbookRequestSchema = v.object({
	tanggal: v.pipe(v.string(), v.minLength(1, 'Tanggal wajib diisi')),
	start_kerja: v.pipe(v.string(), v.minLength(1, 'Jam mulai wajib diisi')),
	end_kerja: v.optional(v.nullable(v.string())),
	lokasi: v.pipe(v.string(), v.minLength(1, 'Lokasi wajib diisi'), v.maxLength(1000)),
	lokasi_lat: v.optional(v.nullable(v.number())),
	lokasi_lng: v.optional(v.nullable(v.number()))
});
export type StartLogbookRequest = v.InferOutput<typeof StartLogbookRequestSchema>;

export const UpdateLogbookRequestSchema = v.object({
	tanggal: v.optional(v.string()),
	start_kerja: v.optional(v.string()),
	end_kerja: v.optional(v.nullable(v.string())),
	lokasi: v.optional(v.string()),
	lokasi_lat: v.optional(v.nullable(v.number())),
	lokasi_lng: v.optional(v.nullable(v.number()))
});
export type UpdateLogbookRequest = v.InferOutput<typeof UpdateLogbookRequestSchema>;

export const UpdateKpiProgressSchema = v.object({
	capaian_angka: v.pipe(v.number(), v.minValue(0, 'Capaian tidak boleh negatif'))
});
export type UpdateKpiProgressRequest = v.InferOutput<typeof UpdateKpiProgressSchema>;

export const SubmitLogbookRequestSchema = v.object({});
export type SubmitLogbookRequest = v.InferOutput<typeof SubmitLogbookRequestSchema>;

export const ReviewLogbookRequestSchema = v.object({
	decision: v.pipe(v.string(), v.minLength(1)),
	rating: v.pipe(v.number(), v.minValue(1), v.maxValue(5)),
	reviewer_comment: v.pipe(v.string(), v.minLength(1, 'Komentar wajib diisi'), v.maxLength(5000))
});
export type ReviewLogbookRequest = v.InferOutput<typeof ReviewLogbookRequestSchema>;


