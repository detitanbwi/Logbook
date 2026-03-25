# Feature-by-Feature Implementation Plan

This document maps each feature from the plan specification (`docs/plans/03-MOBILE-API-DESIGN.md`) to its backend implementation status, controller methods, required tests per actor, and use cases.

---

## Implementation Status Legend

| Status | Meaning |
|--------|---------|
| ✅ | Fully implemented and tested |
| ⚠️ | Partially implemented or needs enhancement |
| ❌ | Not implemented (missing) |
| 🔄 | Needs refactoring |

---

## 1. Authentication Endpoints

### 1.1 Login (`POST /auth/login`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuthController@login` |
| Route | ✅ | `POST /api/v1/auth/login` |
| Tests | ✅ | `AuthFeatureTest` |

**Test Coverage by Actor:**
- [x] Staff can login with valid NPP/password
- [x] Invalid credentials return 401
- [x] `nip` payload rejected (only `npp` accepted)

### 1.2 Logout (`POST /auth/logout`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuthController@logout` |
| Route | ✅ | `POST /api/v1/auth/logout` |
| Tests | ✅ | `AuthFeatureTest` |

**Test Coverage by Actor:**
- [x] Authenticated user can logout

### 1.3 Get Current User (`GET /auth/me`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuthController@me` |
| Route | ✅ | `GET /api/v1/auth/me` |
| Tests | ✅ | `AuthFeatureTest` |

**Test Coverage by Actor:**
- [x] Authenticated user can get profile

### 1.4 Change Password (`PUT /auth/change-password`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuthController@changePassword` |
| Route | ✅ | `PUT /api/v1/auth/change-password` |
| Tests | ✅ | `AuthFeatureTest` |

**Test Coverage by Actor:**
- [x] User can change password with valid current password

### 1.5 Update Profile (`PUT /auth/profile`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuthController@updateProfile` |
| Route | ✅ | `PUT /api/v1/auth/profile` |
| Tests | ✅ | `AuthFeatureTest` |

**Test Coverage by Actor:**
- [x] Staff can update own profile
- [x] Manager can update own profile
- [x] Admin can update own profile
- [x] SuperAdmin can update own profile
- [x] User can upload profile photo
- [x] User cannot update protected fields (`npp`, `role`) via profile endpoint
- [x] Validation for photo type/size

---

## 2. KPI Endpoints

### 2.1 Get My KPI Assignments (`GET /kpi/me`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `KpiAssignmentController@me` |
| Route | ✅ | `GET /api/v1/kpi/me` |
| Tests | ✅ | `KpiAssignmentTest` |

**Test Coverage by Actor:**
- [x] Staff can see their own KPI assignments

### 2.2 List KPI Assignments (`GET /kpi/assignments`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `KpiAssignmentController@index` |
| Route | ✅ | `GET /api/v1/kpi/assignments` |
| Tests | ✅ | `KpiAssignmentTest` |

### 2.3 Assign KPI (`POST /kpi/assignments`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `KpiAssignmentController@store` |
| Route | ✅ | `POST /api/v1/kpi/assignments` |
| Tests | ✅ | `KpiAssignmentTest` |

**Test Coverage by Actor:**
- [x] Manager can assign KPI to subordinate
- [x] Manager cannot assign KPI to non-subordinate

### 2.4 Remove KPI Assignment (`DELETE /kpi/assignments/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `KpiAssignmentController@destroy` |
| Route | ✅ | `DELETE /api/v1/kpi/assignments/{assignment}` |
| Tests | ⚠️ | Basic coverage |

**Required Tests:**
- [ ] Manager can remove assignment from subordinate
- [ ] Manager cannot remove assignment from non-subordinate
- [ ] Admin can remove any assignment

---

## 3. Logbook Endpoints

### 3.1 Start Work / Create Logbook (`POST /logbooks/start`, `POST /logbooks`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@start`, `LogbookController@store` |
| Route | ✅ | Both routes registered |
| Tests | ✅ | `LogbookTest` |

**Test Coverage by Actor:**
- [x] Staff can start logbook
- [x] Staff can create logbook with manual time input
- [x] Multiple logbooks per day allowed
- [x] Start time >= 07:00 enforced
- [x] End time > start time enforced

### 3.2 List My Logbooks (`GET /logbooks`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@index` |
| Route | ✅ | `GET /api/v1/logbooks` |
| Tests | ✅ | `LogbookTest` |

### 3.3 Get Logbook Detail (`GET /logbooks/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@show` |
| Route | ✅ | `GET /api/v1/logbooks/{logbook}` |
| Tests | ✅ | `LogbookTest` |

### 3.4 Update Logbook Time (`PATCH /logbooks/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@update` |
| Route | ✅ | `PATCH /api/v1/logbooks/{logbook}` |
| Tests | ✅ | `LogbookUpdateTest` |

**Test Coverage by Actor:**
- [x] Staff can update end_kerja on DRAFT
- [x] Staff can update start_kerja on DRAFT
- [x] Staff can update tanggal on DRAFT
- [x] Staff cannot update with start_kerja < 07:00
- [x] Staff cannot update with end_kerja < start_kerja
- [x] Staff cannot update SUBMITTED logbook
- [x] Staff cannot update ACCEPTED logbook
- [x] Staff cannot update another user's logbook

### 3.5 Delete Logbook (`DELETE /logbooks/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@destroy` |
| Route | ✅ | `DELETE /api/v1/logbooks/{logbook}` |
| Tests | ✅ | `LogbookDeleteTest` |

**Test Coverage by Actor:**
- [x] Staff can delete own DRAFT logbook
- [x] Staff cannot delete SUBMITTED/ACCEPTED/REJECTED logbook
- [x] Staff cannot delete another user's logbook
- [x] Admin cannot delete another user's logbook via staff endpoint
- [x] SuperAdmin cannot delete another user's logbook via staff endpoint

### 3.6 Get Work Duration (`GET /logbooks/{id}/duration`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@duration` |
| Route | ✅ | `GET /api/v1/logbooks/{logbook}/duration` |
| Tests | ✅ | `LogbookTest` |

### 3.7 Update KPI Progress (`PATCH /logbooks/{id}/kpi/{detail}/progress`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@updateProgress` |
| Route | ✅ | `PATCH /api/v1/logbooks/{logbook}/kpi/{detail}/progress` |
| Tests | ✅ | `LogbookTest` |

**Test Coverage:**
- [x] Staff can update KPI progress in DRAFT
- [x] Staff cannot update KPI progress in SUBMITTED
- [x] Staff cannot update KPI progress in ACCEPTED
- [x] Negative capaian_angka rejected

### 3.8 Upload KPI Attachment (`POST /logbooks/{id}/kpi/{detail}/attachment`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@uploadAttachment` |
| Route | ✅ | `POST /api/v1/logbooks/{logbook}/kpi/{detail}/attachment` |
| Tests | ✅ | `LogbookTest` |

**Test Coverage:**
- [x] Staff can upload attachment in DRAFT
- [x] Staff cannot upload attachment in SUBMITTED
- [x] Staff cannot upload attachment in ACCEPTED
- [x] Staff cannot upload attachment in REJECTED

### 3.9 Delete KPI Attachment (`DELETE /logbooks/{id}/kpi/{detail}/attachment`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@deleteAttachment` |
| Route | ✅ | `DELETE /api/v1/logbooks/{logbook}/kpi/{detail}/attachment` |
| Tests | ✅ | `LogbookTest` |

**Test Coverage:**
- [x] Staff can delete attachment in DRAFT
- [x] Staff cannot delete attachment in non-DRAFT status

### 3.10 Submit Logbook (`POST /logbooks/{id}/submit`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@submit` |
| Route | ✅ | `POST /api/v1/logbooks/{logbook}/submit` |
| Tests | ✅ | `LogbookTest` |

**Test Coverage:**
- [x] Staff can submit DRAFT logbook
- [x] Cannot submit already submitted logbook
- [x] Cannot submit already accepted logbook
- [x] Submit requires at least one KPI with capaian > 0
- [x] Submit notifies manager

---

## 4. Manager Endpoints

### 4.1 Get Subordinates (`GET /users/{id}/subordinates`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@subordinates` |
| Route | ✅ | `GET /api/v1/users/{user}/subordinates` |
| Tests | ✅ | `UserFeatureTest` |

**Test Coverage by Actor:**
- [x] Manager can list their subordinates
- [x] Staff without subordinates cannot access endpoint
- [x] Manager cannot access another manager's subordinates
- [x] Admin can view any user's subordinates
- [x] SuperAdmin can view any user's subordinates

### 4.2-4.4 KPI Assignment Endpoints
✅ Already covered in Section 2.

### 4.5 List Team Logbooks (Pending Review)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `LogbookController@index` with status filter |
| Route | ✅ | `GET /api/v1/logbooks?status=SUBMITTED` |
| Tests | ⚠️ | Partial coverage |

**Required Tests:**
- [ ] Manager sees subordinates' SUBMITTED logbooks
- [ ] Manager does not see non-subordinates' logbooks

### 4.6 Review Logbook (`PUT /logbooks/{id}/review`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `ManagerLogbookController@review` |
| Route | ✅ | `PUT /api/v1/logbooks/{logbook}/review` |
| Tests | ✅ | `LogbookTest` |

**Test Coverage:**
- [x] Manager can accept logbook
- [x] Manager can reject logbook
- [x] Review requires reviewer_comment
- [x] Rating must be 1-5
- [x] Decision must be ACCEPTED or REJECTED
- [x] Cannot review DRAFT logbook
- [x] Cannot review already accepted logbook
- [x] Cannot review already rejected logbook
- [x] Manager without subordinates cannot review

### 4.7 Revert Logbook to DRAFT (`POST /logbooks/{id}/revert`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `ManagerLogbookController@revert` |
| Route | ✅ | `POST /api/v1/logbooks/{logbook}/revert` |
| Tests | ✅ | `LogbookRevertTest` |

**Test Coverage by Actor:**
- [x] Manager can revert subordinate SUBMITTED logbook to DRAFT
- [x] Manager cannot revert non-subordinate logbook
- [x] Staff cannot revert logbook
- [x] Admin can revert submitted logbook
- [x] SuperAdmin can revert submitted logbook
- [x] Revert requires reason
- [x] Revert sends notification to logbook owner

---

## 5. Dashboard Endpoints

### 5.1 Staff Dashboard (`GET /dashboard/staff`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AnalyticsController@staffDashboard` |
| Route | ✅ | `GET /api/v1/dashboard/staff` |
| Tests | ✅ | `AnalyticsFeatureTest` |

### 5.2 Manager Dashboard (`GET /dashboard/manager`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AnalyticsController@managerDashboard` |
| Route | ✅ | `GET /api/v1/dashboard/manager` |
| Tests | ✅ | `AnalyticsFeatureTest` |

### 5.3 Admin Dashboard (`GET /dashboard/admin`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AnalyticsController@adminDashboard` |
| Route | ✅ | `GET /api/v1/dashboard/admin` |
| Tests | ✅ | `AnalyticsFeatureTest` |

---

## 6. Notification Endpoints

### 6.1 List Notifications (`GET /notifications`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `NotificationController@index` |
| Route | ✅ | `GET /api/v1/notifications` |
| Tests | ✅ | `NotificationTest` |

### 6.2 Mark Notification as Read (`PUT /notifications/{id}/read`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `NotificationController@read` |
| Route | ✅ | `PUT /api/v1/notifications/{notification}/read` |
| Tests | ✅ | `NotificationTest` |

### 6.3 Mark All as Read (`PUT /notifications/read-all`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `NotificationController@readAll` |
| Route | ✅ | `PUT /api/v1/notifications/read-all` |
| Tests | ⚠️ | Basic coverage |

---

## 7. KPI Achievement Endpoints

### 7.1 Get User KPI Achievements (`GET /users/{id}/kpi-achievements`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AnalyticsController@userKpiAchievements` |
| Route | ✅ | `GET /api/v1/users/{user}/kpi-achievements` |
| Tests | ✅ | `AnalyticsFeatureTest` |

---

## 8. Summary Endpoints

### 8.1-8.7 All Summary Endpoints
| Endpoint | Status | Controller |
|----------|--------|------------|
| `GET /summaries/daily` | ✅ | `SummaryController@daily` |
| `GET /summaries/daily/{user_id}` | ✅ | `SummaryController@dailyByUser` |
| `GET /summaries/period` | ✅ | `SummaryController@period` |
| `GET /summaries/kpi/daily` | ✅ | `SummaryController@kpiDaily` |
| `GET /summaries/kpi/period` | ✅ | `SummaryController@kpiPeriod` |
| `GET /summaries/team/daily` | ✅ | `SummaryController@teamDaily` |
| `GET /summaries/staff-performance` | ✅ | `SummaryController@staffPerformance` |

**Tests:** ✅ `SummaryEndpointsTest`

---

## 9. User Management Endpoints

### 9.1 List Users (`GET /users`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@index` |
| Tests | ✅ | `UserFeatureTest` |

### 9.2 Create User (`POST /users`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@store` |
| Tests | ✅ | `UserFeatureTest` |

### 9.3 Show User (`GET /users/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@show` |
| Tests | ⚠️ | Basic coverage |

### 9.4 Update User (`PUT /users/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@update` |
| Tests | ✅ | `UserFeatureTest` |

### 9.5 Delete User (`DELETE /users/{id}`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@destroy` |
| Tests | ✅ | `UserFeatureTest` |

### 9.6 Reset User Password (`PUT /users/{id}/reset-password`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `UsersController@resetPassword` |
| Tests | ✅ | `UserFeatureTest` |

---

## 10. KPI Master Endpoints

### 10.1-10.5 CRUD KPI Master
| Endpoint | Status | Controller |
|----------|--------|------------|
| `GET /kpi/master` | ✅ | `MasterKpiController@index` |
| `POST /kpi/master` | ✅ | `MasterKpiController@store` |
| `GET /kpi/master/{id}` | ✅ | `MasterKpiController@show` |
| `PUT /kpi/master/{id}` | ✅ | `MasterKpiController@update` |
| `DELETE /kpi/master/{id}` | ✅ | `MasterKpiController@destroy` |

**Tests:** ✅ `MasterKpiFeatureTest`

---

## 11. Audit Endpoints

### 11.1 List Audit Logs (`GET /audit-logs`)
| Aspect | Status | Details |
|--------|--------|---------|
| Implementation | ✅ | `AuditController@index` |
| Tests | ✅ | `AuditTrailFeatureTest` |

---

## Missing Features Priority

### Priority 1: Critical (Required for Mobile SPA)
1. **Delete Logbook** (`DELETE /logbooks/{id}`)
   - Effort: 2h
   - Impact: High - Users need to delete draft logbooks

2. **Revert Logbook** (`POST /logbooks/{id}/revert`)
   - Effort: 3h
   - Impact: High - Managers need to send logbooks back for correction

3. **Get Subordinates** (`GET /users/{id}/subordinates`)
   - Effort: 2h
   - Impact: High - Required for Team Performance view

### Priority 2: Important (User Experience)
4. **Update Profile** (`PUT /auth/profile`)
   - Effort: 3h
   - Impact: Medium - Users need to update profile/photo

### Priority 3: Nice to Have
- Additional filter options for existing endpoints
- Pagination enhancements
- Response format optimizations

---

## Test Coverage Matrix by Actor

| Feature | Staff | Manager | Admin | SuperAdmin |
|---------|-------|---------|-------|------------|
| Login/Logout | ✅ | ✅ | ✅ | ✅ |
| View Profile | ✅ | ✅ | ✅ | ✅ |
| Update Profile | ✅ | ✅ | ✅ | ✅ |
| Change Password | ✅ | ✅ | ✅ | ✅ |
| Create Logbook | ✅ | ✅ | N/A | N/A |
| Update Logbook | ✅ | ✅ | N/A | N/A |
| Delete Logbook | ✅ | ✅ | ✅ | ✅ |
| Submit Logbook | ✅ | ✅ | N/A | N/A |
| Review Logbook | N/A | ✅ | ✅ | ✅ |
| Revert Logbook | N/A | ✅ | ✅ | ✅ |
| View Subordinates | N/A | ✅ | ✅ | ✅ |
| KPI Assignment | N/A | ✅ | ⚠️ | ⚠️ |
| View KPI Progress | ✅ | ✅ | ✅ | ✅ |
| View Summaries | ✅ | ✅ | ✅ | ✅ |
| Manage Users | N/A | N/A | ✅ | ✅ |
| Manage KPI Master | N/A | ⚠️ | ✅ | ✅ |
| View Audit Logs | N/A | N/A | ❌ | ✅ |

**Legend:**
- ✅ = Tested
- ⚠️ = Partial/Basic coverage
- ❌ = Not tested / Missing feature
- N/A = Not applicable for this role

---

## Implementation Order

### Phase 1: Critical Missing Endpoints (Week 1)
1. ✅ `DELETE /logbooks/{id}` - Delete draft logbook
2. ✅ `POST /logbooks/{id}/revert` - Manager/Admin/SuperAdmin revert to draft
3. ✅ `GET /users/{id}/subordinates` - Get subordinates list

### Phase 2: Profile Enhancement (Week 1)
4. ✅ `PUT /auth/profile` - Update profile with photo upload

### Phase 3: Test Coverage Enhancement (Week 2)
5. Add missing tests for:
   - ✅ Admin/SuperAdmin review permissions
   - Manager list subordinates' logbooks
   - KPI assignment removal by manager
   - Mark all notifications as read

### Phase 4: Documentation Update (Week 2)
6. Update `api_reference.md` with new endpoints
7. Update `context.md` with new features
8. Add OpenAPI/Swagger documentation

---

## Next Steps

1. Implement `DELETE /logbooks/{id}` endpoint and tests
2. Implement `POST /logbooks/{id}/revert` endpoint and tests
3. Implement `GET /users/{id}/subordinates` endpoint and tests
4. Implement `PUT /auth/profile` endpoint and tests
5. Run full test suite and ensure 100% pass rate
6. Update API documentation

---

*Document created: March 20, 2026*
*Last updated: March 20, 2026*
