import * as v from 'valibot';

export const StartLogbookRequestSchema = v.object({
	gps_location_start: v.string()
});
export type StartLogbookRequest = v.InferOutput<typeof StartLogbookRequestSchema>;

export const ToggleKpiRequestSchema = v.object({
	is_finished: v.boolean()
});
export type ToggleKpiRequest = v.InferOutput<typeof ToggleKpiRequestSchema>;

export const SubmitLogbookRequestSchema = v.object({
	gps_location_end: v.string(),
	gambar_bukti: v.optional(v.nullable(v.array(v.nullable(v.string()))))
});
export type SubmitLogbookRequest = v.InferOutput<typeof SubmitLogbookRequestSchema>;

export const RateLogbookRequestSchema = v.object({
	rating: v.pipe(v.number(), v.minValue(1), v.maxValue(5))
});
export type RateLogbookRequest = v.InferOutput<typeof RateLogbookRequestSchema>;
