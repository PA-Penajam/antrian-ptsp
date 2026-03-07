# Issues - Admin Dashboard Redesign

## 2026-03-07 Session Init

### Known Limitations
- Flux tidak punya pie/donut chart native → gunakan Custom SVG donut ring
- Playwright tidak tersedia → gunakan manual verification untuk F1, F2

### Edge Cases to Handle
- Zero state: Chart 7 hari tetap render semua tanggal dengan value 0
- Empty donut: Tampilkan "Belum ada data" callout
- Many services: Top 5 + "Lainnya" bucket
- No counter/user: Fallback label "Sistem"
