import * as v from 'valibot';

export const LoginRequestSchema = v.object({
	nip: v.string(),
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
