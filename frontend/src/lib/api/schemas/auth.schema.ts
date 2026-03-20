import * as v from 'valibot';

export const LoginRequestSchema = v.object({
	npp: v.string(),
	password: v.string()
});
export type LoginRequest = v.InferOutput<typeof LoginRequestSchema>;

export const LoginResponseSchema = v.object({
	token: v.string(),
	user: v.any() // Can be refined later based on actual User object
});
export type LoginResponse = v.InferOutput<typeof LoginResponseSchema>;

export const ChangePasswordRequestSchema = v.object({
	old_password: v.string(),
	new_password: v.pipe(v.string(), v.minLength(8)),
	new_password_confirmation: v.pipe(v.string(), v.minLength(8))
});
export type ChangePasswordRequest = v.InferOutput<typeof ChangePasswordRequestSchema>;

export const UpdateProfileSchema = v.object({
	nama: v.optional(v.string()),
	email: v.optional(v.pipe(v.string(), v.email())),
	foto: v.optional(v.any()),
	alamat: v.optional(v.string()),
	tempat_lahir: v.optional(v.string()),
	tanggal_lahir: v.optional(v.string()),
	nik: v.optional(v.string()),
	npwp: v.optional(v.string()),
	status_kawin: v.optional(v.string()),
	riwayat_pendidikan: v.optional(v.array(v.any())),
	riwayat_karir: v.optional(v.array(v.any()))
});
export type UpdateProfileRequest = v.InferOutput<typeof UpdateProfileSchema>;
