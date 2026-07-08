# Tài liệu Cấu trúc Cơ sở dữ liệu

### Bảng: `attendance_records`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_session_id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `is_account` | `tinyint` | [Mô tả] | [Mô tả] |
| `check_in_time` | `timestamp` | [Mô tả] | [Mô tả] |
| `ip_address` | `varchar` | [Mô tả] | [Mô tả] |
| `device_fingerprint` | `varchar` | [Mô tả] | [Mô tả] |
| `device_id` | `varchar` | [Mô tả] | [Mô tả] |
| `distance_meters` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_accuracy_meters` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_latitude_recorded` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_longitude_recorded` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_fraud_flag` | `varchar` | [Mô tả] | [Mô tả] |
| `note` | `text` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `attendance_summaries`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `class_member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `total_present` | `int` | [Mô tả] | [Mô tả] |
| `total_late` | `int` | [Mô tả] | [Mô tả] |
| `total_absent` | `int` | [Mô tả] | [Mô tả] |
| `total_excused` | `int` | [Mô tả] | [Mô tả] |
| `is_banned_from_exam` | `tinyint` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `audit_logs`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `action` | `varchar` | [Mô tả] | [Mô tả] |
| `table_name` | `varchar` | [Mô tả] | [Mô tả] |
| `row_id` | `varchar` | [Mô tả] | [Mô tả] |
| `old_values` | `json` | [Mô tả] | [Mô tả] |
| `new_values` | `json` | [Mô tả] | [Mô tả] |
| `ip_address` | `varchar` | [Mô tả] | [Mô tả] |
| `user_agent` | `text` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `check_in_scans`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_session_id` | `bigint` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `student_code_attempt` | `varchar` | [Mô tả] | [Mô tả] |
| `scan_type` | `varchar` | [Mô tả] | [Mô tả] |
| `payload_signature` | `varchar` | [Mô tả] | [Mô tả] |
| `is_valid` | `tinyint` | [Mô tả] | [Mô tả] |
| `fail_reason` | `varchar` | [Mô tả] | [Mô tả] |
| `ip_address` | `varchar` | [Mô tả] | [Mô tả] |
| `device_fingerprint` | `varchar` | [Mô tả] | [Mô tả] |
| `device_id` | `varchar` | [Mô tả] | [Mô tả] |
| `scanned_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `class_join_requests`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `class_meetings`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `user_Created` | `bigint` | [Mô tả] | [Mô tả] |
| `name` | `varchar` | [Mô tả] | [Mô tả] |
| `date` | `date` | [Mô tả] | [Mô tả] |
| `start_time` | `time` | [Mô tả] | [Mô tả] |
| `end_time` | `time` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `class_member_profiles`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `student_code` | `varchar` | [Mô tả] | [Mô tả] |
| `full_name` | `varchar` | [Mô tả] | [Mô tả] |
| `email` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `class_members`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `status_changed_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `class_sessions`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `meeting_id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_id` | `char` | [Mô tả] | [Mô tả] |
| `created_by` | `bigint` | [Mô tả] | [Mô tả] |
| `name` | `varchar` | [Mô tả] | [Mô tả] |
| `date` | `date` | [Mô tả] | [Mô tả] |
| `start_time` | `time` | [Mô tả] | [Mô tả] |
| `end_time` | `time` | [Mô tả] | [Mô tả] |
| `qr_token` | `varchar` | [Mô tả] | [Mô tả] |
| `token_expires_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `qr_refresh_rate` | `int` | [Mô tả] | [Mô tả] |
| `gps_latitude` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_longitude` | `decimal` | [Mô tả] | [Mô tả] |
| `gps_radius` | `int` | [Mô tả] | [Mô tả] |
| `device_check` | `tinyint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `classes`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `char` | [Mô tả] | [Mô tả] |
| `owner_user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `join_key` | `varchar` | [Mô tả] | [Mô tả] |
| `class_code` | `varchar` | [Mô tả] | [Mô tả] |
| `name` | `varchar` | [Mô tả] | [Mô tả] |
| `description` | `text` | [Mô tả] | [Mô tả] |
| `late_threshold` | `int` | [Mô tả] | [Mô tả] |
| `deduct_excused_absence` | `tinyint` | [Mô tả] | [Mô tả] |
| `require_approval` | `tinyint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `total_sessions` | `int` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `coupons`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `code` | `varchar` | [Mô tả] | [Mô tả] |
| `applicable_plan_id` | `bigint` | [Mô tả] | [Mô tả] |
| `type` | `varchar` | [Mô tả] | [Mô tả] |
| `value` | `decimal` | [Mô tả] | [Mô tả] |
| `usage_limit` | `int` | [Mô tả] | [Mô tả] |
| `used_count` | `int` | [Mô tả] | [Mô tả] |
| `valid_from` | `timestamp` | [Mô tả] | [Mô tả] |
| `valid_until` | `timestamp` | [Mô tả] | [Mô tả] |
| `is_active` | `tinyint` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `gps_verifications`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `session_id` | `bigint` | [Mô tả] | [Mô tả] |
| `member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `token` | `varchar` | [Mô tả] | [Mô tả] |
| `check_token` | `varchar` | [Mô tả] | [Mô tả] |
| `ip_address` | `varchar` | [Mô tả] | [Mô tả] |
| `lat` | `decimal` | [Mô tả] | [Mô tả] |
| `lng` | `decimal` | [Mô tả] | [Mô tả] |
| `accuracy` | `decimal` | [Mô tả] | [Mô tả] |
| `fraud_score` | `tinyint` | [Mô tả] | [Mô tả] |
| `fraud_reasons` | `varchar` | [Mô tả] | [Mô tả] |
| `is_used` | `tinyint` | [Mô tả] | [Mô tả] |
| `expires_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `import_errors`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `import_token` | `varchar` | [Mô tả] | [Mô tả] |
| `row_index` | `int` | [Mô tả] | [Mô tả] |
| `error_message` | `text` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `leave_requests`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_session_id` | `bigint` | [Mô tả] | [Mô tả] |
| `reason` | `text` | [Mô tả] | [Mô tả] |
| `proof_image` | `text` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `rejected_reason` | `text` | [Mô tả] | [Mô tả] |
| `reviewed_by` | `bigint` | [Mô tả] | [Mô tả] |
| `reviewed_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `meeting_summaries`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `meeting_id` | `bigint` | [Mô tả] | [Mô tả] |
| `class_member_id` | `bigint` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `deduction` | `decimal` | [Mô tả] | [Mô tả] |
| `auto_status` | `varchar` | [Mô tả] | [Mô tả] |
| `is_overridden` | `tinyint` | [Mô tả] | [Mô tả] |
| `notified_status` | `varchar` | [Mô tả] | [Mô tả] |
| `note` | `text` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `notifications`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `char` | [Mô tả] | [Mô tả] |
| `type` | `varchar` | [Mô tả] | [Mô tả] |
| `notifiable_type` | `varchar` | [Mô tả] | [Mô tả] |
| `notifiable_id` | `bigint` | [Mô tả] | [Mô tả] |
| `data` | `json` | [Mô tả] | [Mô tả] |
| `read_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `plan_configs`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `plan_id` | `bigint` | [Mô tả] | [Mô tả] |
| `max_classes` | `int` | [Mô tả] | [Mô tả] |
| `max_students_per_class` | `int` | [Mô tả] | [Mô tả] |
| `max_gps_radius` | `int` | [Mô tả] | [Mô tả] |
| `can_export_excel` | `tinyint` | [Mô tả] | [Mô tả] |

### Bảng: `plans`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `plan_tier` | `varchar` | [Mô tả] | [Mô tả] |
| `name` | `varchar` | [Mô tả] | [Mô tả] |
| `description` | `text` | [Mô tả] | [Mô tả] |
| `price` | `decimal` | [Mô tả] | [Mô tả] |
| `duration_days` | `int` | [Mô tả] | [Mô tả] |
| `is_active` | `tinyint` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `sessions`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `varchar` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `ip_address` | `varchar` | [Mô tả] | [Mô tả] |
| `user_agent` | `text` | [Mô tả] | [Mô tả] |
| `payload` | `longtext` | [Mô tả] | [Mô tả] |
| `last_activity` | `int` | [Mô tả] | [Mô tả] |

### Bảng: `settings`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `key` | `varchar` | [Mô tả] | [Mô tả] |
| `value` | `text` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `subscriptions`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `plan_id` | `bigint` | [Mô tả] | [Mô tả] |
| `paid_plan_id` | `bigint` | [Mô tả] | [Mô tả] |
| `start_date` | `timestamp` | [Mô tả] | [Mô tả] |
| `end_date` | `timestamp` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `transactions`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `plan_id` | `bigint` | [Mô tả] | [Mô tả] |
| `amount` | `decimal` | [Mô tả] | [Mô tả] |
| `currency` | `varchar` | [Mô tả] | [Mô tả] |
| `payment_method` | `varchar` | [Mô tả] | [Mô tả] |
| `transaction_code` | `varchar` | [Mô tả] | [Mô tả] |
| `reference_code` | `varchar` | [Mô tả] | [Mô tả] |
| `gateway_transaction_id` | `varchar` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `payment_url` | `text` | [Mô tả] | [Mô tả] |
| `payment_response` | `json` | [Mô tả] | [Mô tả] |
| `failure_reason` | `text` | [Mô tả] | [Mô tả] |
| `paid_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `expired_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `user_devices`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `user_id` | `bigint` | [Mô tả] | [Mô tả] |
| `fcm_token` | `varchar` | [Mô tả] | [Mô tả] |
| `device_name` | `varchar` | [Mô tả] | [Mô tả] |
| `last_active_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |

### Bảng: `users`
**Dùng để:** [Mô tả]

| Tên thuộc tính | Kiểu dữ liệu | Dùng làm gì | Sử dụng ở đâu |
| --- | --- | --- | --- |
| `id` | `bigint` | [Mô tả] | [Mô tả] |
| `role` | `varchar` | [Mô tả] | [Mô tả] |
| `google_id` | `varchar` | [Mô tả] | [Mô tả] |
| `name` | `varchar` | [Mô tả] | [Mô tả] |
| `email` | `varchar` | [Mô tả] | [Mô tả] |
| `email_verified_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `password` | `varchar` | [Mô tả] | [Mô tả] |
| `avatar` | `varchar` | [Mô tả] | [Mô tả] |
| `status` | `varchar` | [Mô tả] | [Mô tả] |
| `remember_token` | `varchar` | [Mô tả] | [Mô tả] |
| `created_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `updated_at` | `timestamp` | [Mô tả] | [Mô tả] |
| `deleted_at` | `timestamp` | [Mô tả] | [Mô tả] |

