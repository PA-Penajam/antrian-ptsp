# Decisions - Admin Dashboard Redesign

## 2026-03-07 Session Init

### Architecture Decisions
1. **Donut chart**: Custom SVG (bukan library eksternal)
2. **Live updates**: `wire:poll.visible` 30s (bukan WebSocket)
3. **URL**: Hardcoded (bukan named routes)
4. **Top N services**: 5 + "Lainnya" bucket
5. **Recent events**: 10 items

### Scope Boundaries (LOCKED)
- Named route refactor → OUT OF SCOPE
- WebSocket → OUT OF SCOPE
- Cross-page notification → OUT OF SCOPE
- User preference settings → OUT OF SCOPE
- Export PDF → OUT OF SCOPE

### Old Widgets Fate
- `Ringkasan Failure Operasional` → REMOVE
- `Aktivitas Pengguna Layanan` → REMOVE
- `Shortcut Manajemen` → REPLACE dengan Grid Quick Actions

### Wave Execution Plan
- Wave 1: Task 1 + Task 2 (PARALLEL)
- Wave 2: Task 3 + Task 4 (PARALLEL), then Task 5 (sequential)
- Wave 3: Task 6 + Task 7 (PARALLEL), then Task 8 (sequential)
- Wave 4: Task 9 + Task 10 + Task 11 (PARALLEL), then Task 12 (sequential)
- Wave Final: F1 + F2 + F3 (PARALLEL)
