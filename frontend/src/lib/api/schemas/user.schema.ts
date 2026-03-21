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
	manager_id: v.optional(v.nullable(v.string())),
	nik: v.optional(v.nullable(v.string())),
	npwp: v.optional(v.nullable(v.string())),
	alamat: v.optional(v.nullable(v.string())),
	tempat_lahir: v.optional(v.nullable(v.string())),
	tanggal_lahir: v.optional(v.nullable(v.string())),
	status_kawin: v.optional(v.nullable(v.string())),
	riwayat_pendidikan: v.optional(v.nullable(v.array(v.record(v.string(), v.unknown())))),
	riwayat_karir: v.optional(v.nullable(v.array(v.record(v.string(), v.unknown()))))
});
export type UserUpdateDto = v.InferOutput<typeof UserUpdateSchema>;
