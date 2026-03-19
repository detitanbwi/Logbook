# API Flow Testing Results

Generated on: 2026-03-19T00:37:46.526Z

## 1. Login

### Request

```json
{
	"url": "http://localhost:8000/api/v1/auth/login",
	"method": "POST",
	"headers": {
		"Content-Type": "application/json",
		"Accept": "application/json"
	},
	"body": {
		"nip": "198001012000011001",
		"password": "password"
	}
}
```

### Response

```json
{
	"status": 200,
	"statusText": "OK",
	"data": {
		"message": "Login successful",
		"access_token": "38|nQSHImOFDZyxB0gCqBwpcZTYoG2EquBopEfe7Fh0d9c2d999",
		"token_type": "Bearer",
		"user": {
			"id": "019d02f1-5c35-729d-bb06-db75c2f1eaf5",
			"name": "Admin System",
			"email": "admin@logbook.com",
			"nip": "198001012000011001",
			"role": "ADMIN",
			"manager_id": null,
			"last_password_change": "2026-03-18 21:54:25",
			"deleted_at": null,
			"email_verified_at": "2026-03-18T21:54:24.000000Z",
			"created_at": "2026-03-18T21:54:25.000000Z",
			"updated_at": "2026-03-18T21:54:25.000000Z"
		}
	}
}
```

## 2. Get Current User (/auth/me)

### Request

```json
{
	"url": "http://localhost:8000/api/v1/auth/me",
	"method": "GET",
	"headers": {
		"Authorization": "Bearer 38|nQSHImOFDZyxB0gCqBwpcZTYoG2EquBopEfe7Fh0d9c2d999",
		"Accept": "application/json"
	}
}
```

### Response

```json
{
	"status": 200,
	"statusText": "OK",
	"data": {
		"user": {
			"id": "019d02f1-5c35-729d-bb06-db75c2f1eaf5",
			"name": "Admin System",
			"email": "admin@logbook.com",
			"nip": "198001012000011001",
			"role": "ADMIN",
			"manager_id": null,
			"last_password_change": "2026-03-18 21:54:25",
			"deleted_at": null,
			"email_verified_at": "2026-03-18T21:54:24.000000Z",
			"created_at": "2026-03-18T21:54:25.000000Z",
			"updated_at": "2026-03-18T21:54:25.000000Z"
		}
	}
}
```

## 3. List Users (/users)

### Request

```json
{
	"url": "http://localhost:8000/api/v1/users",
	"method": "GET",
	"headers": {
		"Authorization": "Bearer 38|nQSHImOFDZyxB0gCqBwpcZTYoG2EquBopEfe7Fh0d9c2d999",
		"Accept": "application/json"
	}
}
```

### Response

```json
{
	"status": 200,
	"statusText": "OK",
	"data": {
		"data": [
			{
				"id": "019d02f1-5c35-729d-bb06-db75c2f1eaf5",
				"name": "Admin System",
				"email": "admin@logbook.com",
				"nip": "198001012000011001",
				"role": "ADMIN",
				"manager_id": null,
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:24.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"name": "Manager Budi",
				"email": "manager@logbook.com",
				"nip": "198502022005011002",
				"role": "MANAGER",
				"manager_id": null,
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"name": "Staff Siti",
				"email": "staff@logbook.com",
				"nip": "199003032010012003",
				"role": "STAFF",
				"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5c9a-71e8-a9ca-4ca417bbd602",
				"name": "Tugiman Situmorang",
				"email": "yfahey@example.org",
				"nip": "197702192000051518",
				"role": "MANAGER",
				"manager_id": null,
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"name": "Lintang Usyi Lestari",
				"email": "hudson.jordan@example.net",
				"nip": "197105272020082598",
				"role": "MANAGER",
				"manager_id": null,
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5cb5-71cb-9347-80dd3dabce5c",
				"name": "Margana Saefullah",
				"email": "kuhn.wellington@example.org",
				"nip": "199611142014101305",
				"role": "STAFF",
				"manager_id": "019d02f1-5c9a-71e8-a9ca-4ca417bbd602",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5cc2-725a-b6d5-e652828a3f45",
				"name": "Zamira Susanti",
				"email": "beier.marion@example.net",
				"nip": "199407122006082057",
				"role": "STAFF",
				"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5ce6-7211-b201-dca2feff5023",
				"name": "Harsaya Nashiruddin",
				"email": "dylan68@example.com",
				"nip": "197102242015121078",
				"role": "STAFF",
				"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5cf4-703d-984a-a5ba53af8d18",
				"name": "Adikara Mahendra S.Psi",
				"email": "araceli.frami@example.net",
				"nip": "198206042015091313",
				"role": "STAFF",
				"manager_id": "019d02f1-5c9a-71e8-a9ca-4ca417bbd602",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d02-7141-aa1a-0b1e086956c6",
				"name": "Karna Najmudin",
				"email": "maxime27@example.com",
				"nip": "199701062015051301",
				"role": "STAFF",
				"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d11-7186-99f1-ac9942dca7e8",
				"name": "Kasiyah Bella Riyanti M.TI.",
				"email": "christ.considine@example.net",
				"nip": "198309042016082495",
				"role": "STAFF",
				"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d22-7336-bdda-1b44b59975f3",
				"name": "Mahesa Damanik",
				"email": "miracle10@example.org",
				"nip": "199807102017012943",
				"role": "STAFF",
				"manager_id": "019d02f1-5c9a-71e8-a9ca-4ca417bbd602",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"name": "Nabila Melinda Nasyiah S.Psi",
				"email": "gerardo77@example.net",
				"nip": "198207172014042704",
				"role": "STAFF",
				"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d3c-7073-8ef2-bda77ea9c1be",
				"name": "Hesti Sudiati M.Pd",
				"email": "hammes.macy@example.org",
				"nip": "199809032015031578",
				"role": "STAFF",
				"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"last_password_change": "2026-03-18 21:54:25",
				"deleted_at": null,
				"email_verified_at": "2026-03-18T21:54:25.000000Z",
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			}
		],
		"links": {
			"first": "http://localhost:8000/api/v1/users?page=1",
			"last": "http://localhost:8000/api/v1/users?page=1",
			"prev": null,
			"next": null
		},
		"meta": {
			"current_page": 1,
			"from": 1,
			"last_page": 1,
			"links": [
				{
					"url": null,
					"label": "&laquo; Previous",
					"page": null,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/users?page=1",
					"label": "1",
					"page": 1,
					"active": true
				},
				{
					"url": null,
					"label": "Next &raquo;",
					"page": null,
					"active": false
				}
			],
			"path": "http://localhost:8000/api/v1/users",
			"per_page": 15,
			"to": 14,
			"total": 14
		}
	}
}
```

## 4. List Master KPIs (/kpi/master)

### Request

```json
{
	"url": "http://localhost:8000/api/v1/kpi/master",
	"method": "GET",
	"headers": {
		"Authorization": "Bearer 38|nQSHImOFDZyxB0gCqBwpcZTYoG2EquBopEfe7Fh0d9c2d999",
		"Accept": "application/json"
	}
}
```

### Response

```json
{
	"status": 200,
	"statusText": "OK",
	"data": {
		"data": [
			{
				"id": "019d02f1-5d52-7046-8370-629f651ae17b",
				"nama": "Menganalisis Data Penjualan aut",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d5f-71fa-9cd0-5beb43a6d465",
				"nama": "Menyusun Rencana Anggaran voluptatibus",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d6a-714a-bb54-9926975a8d37",
				"nama": "Melakukan Pengujian Sistem nam",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d75-7259-8d80-280e70b257f0",
				"nama": "Mengembangkan Fitur Aplikasi Baru maiores",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d81-728c-9d1d-ff0d6d0b5abf",
				"nama": "Menganalisis Data Penjualan explicabo",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d8e-7207-9b5a-f063d9561334",
				"nama": "Mengadakan Rapat Evaluasi totam",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5d9b-7259-bda8-dbd0dcad7dff",
				"nama": "Mengelola Keluhan Pelanggan aut",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5da8-72bc-850b-679e1be6c7f3",
				"nama": "Melakukan Riset Pasar tempora",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5dc7-70b3-9d7f-29928cdcd191",
				"nama": "Mengembangkan Fitur Aplikasi Baru veniam",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5dd4-73bd-8789-67ad5335f0f5",
				"nama": "Evaluasi Kinerja Vendor quis",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5ddf-70f7-92f4-27aa9f1ca956",
				"nama": "Melakukan Riset Pasar occaecati",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5dec-7296-9591-bb0828dba661",
				"nama": "Mengelola Keluhan Pelanggan nemo",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5df8-73b7-8dbc-1ee82c48511c",
				"nama": "Pembaruan Dokumentasi Proyek sed",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5e05-71a8-ab80-2f8dc5524adf",
				"nama": "Melakukan Audit Keamanan quisquam",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:25.000000Z",
				"updated_at": "2026-03-18T21:54:25.000000Z"
			},
			{
				"id": "019d02f1-5e8d-725c-b0e1-b60614992d49",
				"nama": "Evaluasi Kinerja Vendor neque",
				"status_aktif": true,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:26.000000Z",
				"updated_at": "2026-03-18T21:54:26.000000Z"
			}
		],
		"links": {
			"first": "http://localhost:8000/api/v1/kpi/master?page=1",
			"last": "http://localhost:8000/api/v1/kpi/master?page=2",
			"prev": null,
			"next": "http://localhost:8000/api/v1/kpi/master?page=2"
		},
		"meta": {
			"current_page": 1,
			"from": 1,
			"last_page": 2,
			"links": [
				{
					"url": null,
					"label": "&laquo; Previous",
					"page": null,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/kpi/master?page=1",
					"label": "1",
					"page": 1,
					"active": true
				},
				{
					"url": "http://localhost:8000/api/v1/kpi/master?page=2",
					"label": "2",
					"page": 2,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/kpi/master?page=2",
					"label": "Next &raquo;",
					"page": 2,
					"active": false
				}
			],
			"path": "http://localhost:8000/api/v1/kpi/master",
			"per_page": 15,
			"to": 15,
			"total": 20
		}
	}
}
```

## 5. List Logbooks (/logbooks)

### Request

```json
{
	"url": "http://localhost:8000/api/v1/logbooks",
	"method": "GET",
	"headers": {
		"Authorization": "Bearer 38|nQSHImOFDZyxB0gCqBwpcZTYoG2EquBopEfe7Fh0d9c2d999",
		"Accept": "application/json"
	}
}
```

### Response

```json
{
	"status": 200,
	"statusText": "OK",
	"data": {
		"data": [
			{
				"id": "019d0328-d975-7050-a10a-c949b103dc47",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:55:01.000000Z",
				"end_kerja": null,
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": null,
				"gambar_bukti": null,
				"status": "DRAFT",
				"rating": null,
				"reviewed_by": null,
				"reviewed_at": null,
				"deleted_at": null,
				"created_at": "2026-03-18T22:55:02.000000Z",
				"updated_at": "2026-03-18T22:55:02.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": null
			},
			{
				"id": "019d0328-bbe2-72f5-8fb0-d290fcedbfbf",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:54:54.000000Z",
				"end_kerja": "2026-03-18T22:54:56.000000Z",
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": "-6.200, 106.816",
				"gambar_bukti": [],
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"reviewed_at": "2026-03-18T22:54:59.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T22:54:54.000000Z",
				"updated_at": "2026-03-18T22:54:59.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"name": "Manager Budi",
					"email": "manager@logbook.com",
					"nip": "198502022005011002",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d0325-cada-7230-b5b9-696462360b91",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:51:41.000000Z",
				"end_kerja": null,
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": null,
				"gambar_bukti": null,
				"status": "DRAFT",
				"rating": null,
				"reviewed_by": null,
				"reviewed_at": null,
				"deleted_at": null,
				"created_at": "2026-03-18T22:51:41.000000Z",
				"updated_at": "2026-03-18T22:51:41.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": null
			},
			{
				"id": "019d0325-a552-708e-ac5c-d111397e8913",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:51:32.000000Z",
				"end_kerja": "2026-03-18T22:51:35.000000Z",
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": "-6.200, 106.816",
				"gambar_bukti": [],
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"reviewed_at": "2026-03-18T22:51:38.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T22:51:32.000000Z",
				"updated_at": "2026-03-18T22:51:38.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"name": "Manager Budi",
					"email": "manager@logbook.com",
					"nip": "198502022005011002",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d0324-78a1-70ef-aa5b-37a429fe0561",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:50:15.000000Z",
				"end_kerja": null,
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": null,
				"gambar_bukti": null,
				"status": "DRAFT",
				"rating": null,
				"reviewed_by": null,
				"reviewed_at": null,
				"deleted_at": null,
				"created_at": "2026-03-18T22:50:15.000000Z",
				"updated_at": "2026-03-18T22:50:15.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": null
			},
			{
				"id": "019d0324-541b-7201-b047-6d4ad8c2dc45",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:50:05.000000Z",
				"end_kerja": "2026-03-18T22:50:09.000000Z",
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": "-6.200, 106.816",
				"gambar_bukti": [],
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"reviewed_at": "2026-03-18T22:50:10.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T22:50:05.000000Z",
				"updated_at": "2026-03-18T22:50:10.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"name": "Manager Budi",
					"email": "manager@logbook.com",
					"nip": "198502022005011002",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d031e-66f5-7289-9850-da0d0da60e13",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:43:37.000000Z",
				"end_kerja": "2026-03-18T22:43:38.000000Z",
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": "-6.200, 106.816",
				"gambar_bukti": [],
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"reviewed_at": "2026-03-18T22:43:40.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T22:43:37.000000Z",
				"updated_at": "2026-03-18T22:43:40.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"name": "Manager Budi",
					"email": "manager@logbook.com",
					"nip": "198502022005011002",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d031e-1ed1-71e0-8d9f-a4269477eda9",
				"user_id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
				"start_kerja": "2026-03-18T22:43:18.000000Z",
				"end_kerja": "2026-03-18T22:43:20.000000Z",
				"lokasi_start": "-6.200, 106.816",
				"lokasi_end": "-6.200, 106.816",
				"gambar_bukti": [],
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5c75-7052-b518-0f68f59ae537",
				"reviewed_at": "2026-03-18T22:43:22.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T22:43:18.000000Z",
				"updated_at": "2026-03-18T22:43:22.000000Z",
				"user": {
					"id": "019d02f1-5c83-7290-8ee0-9f8023eae53c",
					"name": "Staff Siti",
					"email": "staff@logbook.com",
					"nip": "199003032010012003",
					"role": "STAFF",
					"manager_id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5c75-7052-b518-0f68f59ae537",
					"name": "Manager Budi",
					"email": "manager@logbook.com",
					"nip": "198502022005011002",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7d91-71a9-b675-67d06501f4e7",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-08T09:48:33.000000Z",
				"end_kerja": "2026-03-08T23:34:25.000000Z",
				"lokasi_start": "{\"lat\":-6.298811,\"lng\":106.775725}",
				"lokasi_end": "{\"lat\":-6.297124,\"lng\":106.864462}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/009977?text=tempora\"]",
				"status": "REVIEWED",
				"rating": 5,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-03-09T23:34:25.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7db0-7061-b748-d0efe9b57d58",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-09T09:06:33.000000Z",
				"end_kerja": "2026-02-26T20:30:48.000000Z",
				"lokasi_start": "{\"lat\":-6.187379,\"lng\":106.709113}",
				"lokasi_end": "{\"lat\":-6.228827,\"lng\":106.848835}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/00bbcc?text=ratione\"]",
				"status": "REVIEWED",
				"rating": 1,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-02-27T20:30:48.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7dcd-7137-ba5e-f766d6d0a421",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-10T09:17:33.000000Z",
				"end_kerja": "2026-03-05T14:17:03.000000Z",
				"lokasi_start": "{\"lat\":-6.245586,\"lng\":106.885657}",
				"lokasi_end": "{\"lat\":-6.240169,\"lng\":106.795364}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/0066ff?text=ab\"]",
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-03-06T14:17:03.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7dea-70c7-bd38-bf018946f895",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-11T09:55:33.000000Z",
				"end_kerja": "2026-02-26T19:15:19.000000Z",
				"lokasi_start": "{\"lat\":-6.16644,\"lng\":106.865116}",
				"lokasi_end": "{\"lat\":-6.210883,\"lng\":106.76032}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/005544?text=a\"]",
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-02-27T19:15:19.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7e07-73f2-a7b9-91bff18bc404",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-12T09:17:33.000000Z",
				"end_kerja": "2026-03-08T20:36:13.000000Z",
				"lokasi_start": "{\"lat\":-6.124795,\"lng\":106.895848}",
				"lokasi_end": "{\"lat\":-6.216474,\"lng\":106.838545}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/00ddff?text=doloremque\"]",
				"status": "REVIEWED",
				"rating": 4,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-03-09T20:36:13.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7e25-720e-91e1-053c717c8519",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-13T09:28:33.000000Z",
				"end_kerja": "2026-03-16T14:52:55.000000Z",
				"lokasi_start": "{\"lat\":-6.169531,\"lng\":106.819251}",
				"lokasi_end": "{\"lat\":-6.242137,\"lng\":106.732251}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/0088ee?text=quam\"]",
				"status": "REVIEWED",
				"rating": 3,
				"reviewed_by": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
				"reviewed_at": "2026-03-17T14:52:55.000000Z",
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": {
					"id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"name": "Lintang Usyi Lestari",
					"email": "hudson.jordan@example.net",
					"nip": "197105272020082598",
					"role": "MANAGER",
					"manager_id": null,
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				}
			},
			{
				"id": "019d02f1-7e41-73c1-a55a-dfa1f15f9a15",
				"user_id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
				"start_kerja": "2026-03-15T08:49:33.000000Z",
				"end_kerja": "2026-02-25T09:36:38.000000Z",
				"lokasi_start": "{\"lat\":-6.195156,\"lng\":106.868797}",
				"lokasi_end": "{\"lat\":-6.114279,\"lng\":106.885286}",
				"gambar_bukti": "[\"https:\\/\\/via.placeholder.com\\/640x480.png\\/000011?text=alias\"]",
				"status": "SUBMITTED",
				"rating": null,
				"reviewed_by": null,
				"reviewed_at": null,
				"deleted_at": null,
				"created_at": "2026-03-18T21:54:34.000000Z",
				"updated_at": "2026-03-18T21:54:34.000000Z",
				"user": {
					"id": "019d02f1-5d30-739b-8ead-ef2d1f6c87d8",
					"name": "Nabila Melinda Nasyiah S.Psi",
					"email": "gerardo77@example.net",
					"nip": "198207172014042704",
					"role": "STAFF",
					"manager_id": "019d02f1-5ca7-7281-a3b0-a0961323d57a",
					"last_password_change": "2026-03-18 21:54:25",
					"deleted_at": null,
					"email_verified_at": "2026-03-18T21:54:25.000000Z",
					"created_at": "2026-03-18T21:54:25.000000Z",
					"updated_at": "2026-03-18T21:54:25.000000Z"
				},
				"reviewer": null
			}
		],
		"links": {
			"first": "http://localhost:8000/api/v1/logbooks?page=1",
			"last": "http://localhost:8000/api/v1/logbooks?page=15",
			"prev": null,
			"next": "http://localhost:8000/api/v1/logbooks?page=2"
		},
		"meta": {
			"current_page": 1,
			"from": 1,
			"last_page": 15,
			"links": [
				{
					"url": null,
					"label": "&laquo; Previous",
					"page": null,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=1",
					"label": "1",
					"page": 1,
					"active": true
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=2",
					"label": "2",
					"page": 2,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=3",
					"label": "3",
					"page": 3,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=4",
					"label": "4",
					"page": 4,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=5",
					"label": "5",
					"page": 5,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=6",
					"label": "6",
					"page": 6,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=7",
					"label": "7",
					"page": 7,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=8",
					"label": "8",
					"page": 8,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=9",
					"label": "9",
					"page": 9,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=10",
					"label": "10",
					"page": 10,
					"active": false
				},
				{
					"url": null,
					"label": "...",
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=14",
					"label": "14",
					"page": 14,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=15",
					"label": "15",
					"page": 15,
					"active": false
				},
				{
					"url": "http://localhost:8000/api/v1/logbooks?page=2",
					"label": "Next &raquo;",
					"page": 2,
					"active": false
				}
			],
			"path": "http://localhost:8000/api/v1/logbooks",
			"per_page": 15,
			"to": 15,
			"total": 224
		}
	}
}
```
