import * as v from 'valibot';

export const UserCreateSchema = v.object({
	nama: v.pipe(v.string(), v.minLength(1, 'Nama wajib diisi')),
	email: v.pipe(v.string(), v.email('Format email tidak valid')),
	npp: v.pipe(v.string(), v.minLength(1, 'NPP wajib diisi')),
	password: v.pipe(v.string(), v.minLength(8, 'Password minimal 8 karakter')),
	role: v.picklist(['SuperAdmin', 'Admin', 'Staff']),
	manager_id: v.optional(v.nullable(v.string()))
});
export type UserCreateDto = v.InferOutput<typeof UserCreateSchema>;

export const UserUpdateSchema = v.object({
	nama: v.optional(v.string()),
	email: v.optional(v.string()),
	npp: v.optional(v.string()),
	role: v.optional(v.picklist(['SuperAdmin', 'Admin', 'Staff'])),
	manager_id: v.optional(v.nullable(v.string()))
});
export type UserUpdateDto = v.InferOutput<typeof UserUpdateSchema>;
