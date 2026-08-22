---
slug: admin-management
primary_target: resources/views/pages/admin/layanan/index.blade.php
related_targets:
  - resources/views/pages/admin/loket/index.blade.php
  - resources/views/pages/admin/users/index.blade.php
  - resources/views/pages/admin/wilayah/index.blade.php
  - app/Http/Controllers/Admin/ServiceManagementController.php
  - app/Http/Controllers/Admin/CounterManagementController.php
  - app/Http/Controllers/Admin/UserManagementController.php
  - app/Http/Controllers/Admin/WilayahSettingController.php
mode: Operate
---

# Surface Brief: Admin Management & Configuration

## 1. Job and Audience
- **Visitor / Audience:** Administrator sistem PTSP dan Tim IT Pengadilan Agama.
- **Context:** Konfigurasi master data layanan, loket fisik, queue pool routing, akun staf, dan cakupan wilayah hukum yurisdiksi.
- **Mode:** **Operate (Precise CRUD, Modal Drawers, Live Assignments)**

## 2. Outcome and Proof
- **Layanan:** Kode huruf antrian (A, B, C), nama, deskripsi, kuota harian, status aktif, dan daftar persyaratan berkas dinamis.
- **Loket & Pool:** Pengaturan loket fisik, penugasan petugas (*Assign/Release Officer*), dan pemetaan queue pool.
- **Users & Wilayah:** Manajemen pengguna berbasis peran (Admin, Frontdesk, Officer, Monitor), reset PIN Kiosk/TV, dan boundary kecamatan/kelurahan.

## 3. Selected Direction
- **Visual Authority:** *The Digital Balai Admin Framework* (Flux UI Pro Drawers & Modals, Responsive Data Grid, Status Badges).
