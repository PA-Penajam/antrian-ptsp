# User Roles and Permissions

<cite>
**Referenced Files in This Document**
- [UserRole.php](file://app/Enums/UserRole.php)
- [EnsureUserHasRole.php](file://app/Http/Middleware/EnsureUserHasRole.php)
- [CheckModulePassword.php](file://app/Http/Middleware/CheckModulePassword.php)
- [ModuleSession.php](file://app/Enums/ModuleSession.php)
- [User.php](file://app/Models/User.php)
- [2026_03_06_024605_add_role_to_users_table.php](file://database/migrations/2026_03_06_024605_add_role_to_users_table.php)
- [web.php](file://routes/web.php)
- [app.php](file://bootstrap/app.php)
- [UserManagementController.php](file://app/Http/Controllers/Admin/UserManagementController.php)
- [KioskController.php](file://app/Http/Controllers/KioskController.php)
- [TvDisplayController.php](file://app/Http/Controllers/TvDisplayController.php)
- [kiosk.php](file://config/kiosk.php)
- [CreateNewUser.php](file://app/Actions/Fortify/CreateNewUser.php)
</cite>

## Table of Contents
1. [Introduction](#introduction)
2. [Project Structure](#project-structure)
3. [Core Components](#core-components)
4. [Architecture Overview](#architecture-overview)
5. [Detailed Component Analysis](#detailed-component-analysis)
6. [Dependency Analysis](#dependency-analysis)
7. [Performance Considerations](#performance-considerations)
8. [Troubleshooting Guide](#troubleshooting-guide)
9. [Conclusion](#conclusion)

## Introduction
This document describes the user role and permission system used by the application. It covers the role enumeration, middleware enforcement mechanisms, module-specific authentication, and administrative user management. The system supports four primary roles: Admin, Frontdesk, Officer, and Monitor. Administrative users can manage roles and permissions through dedicated routes and controllers, while operational modules (Kiosk and TV Display) use a separate password-based session mechanism.

## Project Structure
The role and permission system spans several layers:
- Enumerations define role values and metadata
- Middleware enforces role-based access control for authenticated routes
- Controllers implement module-specific authentication and business logic
- Routes group endpoints by role requirements
- Models and migrations define the persisted role data
- Configuration governs module passwords and session lifetimes

```mermaid
graph TB
subgraph "Web Routes"
R_web["routes/web.php"]
end
subgraph "HTTP Layer"
MW_role["EnsureUserHasRole<br/>middleware"]
MW_module["CheckModulePassword<br/>middleware"]
C_kiosk["KioskController"]
C_tv["TvDisplayController"]
end
subgraph "Domain Layer"
E_role["UserRole<br/>enum"]
E_sess["ModuleSession<br/>enum"]
M_user["User<br/>model"]
end
subgraph "Configuration"
CFG_kiosk["config/kiosk.php"]
end
R_web --> MW_role
R_web --> MW_module
MW_role --> M_user
MW_module --> E_sess
C_kiosk --> CFG_kiosk
C_tv --> CFG_kiosk
M_user --> E_role
```

**Diagram sources**
- [web.php:1-127](file://routes/web.php#L1-L127)
- [EnsureUserHasRole.php:1-37](file://app/Http/Middleware/EnsureUserHasRole.php#L1-L37)
- [CheckModulePassword.php:1-68](file://app/Http/Middleware/CheckModulePassword.php#L1-L68)
- [UserRole.php:1-32](file://app/Enums/UserRole.php#L1-L32)
- [ModuleSession.php:1-15](file://app/Enums/ModuleSession.php#L1-L15)
- [User.php:1-99](file://app/Models/User.php#L1-L99)
- [KioskController.php:1-144](file://app/Http/Controllers/KioskController.php#L1-L144)
- [TvDisplayController.php:1-144](file://app/Http/Controllers/TvDisplayController.php#L1-L144)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

**Section sources**
- [web.php:1-127](file://routes/web.php#L1-L127)
- [app.php:1-31](file://bootstrap/app.php#L1-L31)

## Core Components
- UserRole enum defines the supported roles and provides helper methods for labels and colors.
- EnsureUserHasRole middleware enforces role-based access control for authenticated routes.
- CheckModulePassword middleware enforces module-specific password authentication and session lifetime.
- User model persists role data and exposes helper methods for role checks and active role resolution.
- Migration adds the role column to the users table with a default value.
- Controllers implement module login/logout flows and maintain module-specific session keys.

**Section sources**
- [UserRole.php:1-32](file://app/Enums/UserRole.php#L1-L32)
- [EnsureUserHasRole.php:1-37](file://app/Http/Middleware/EnsureUserHasRole.php#L1-L37)
- [CheckModulePassword.php:1-68](file://app/Http/Middleware/CheckModulePassword.php#L1-L68)
- [User.php:1-99](file://app/Models/User.php#L1-L99)
- [2026_03_06_024605_add_role_to_users_table.php:1-29](file://database/migrations/2026_03_06_024605_add_role_to_users_table.php#L1-L29)

## Architecture Overview
The system enforces two distinct access control mechanisms:
- Role-based access control (RBAC) for authenticated routes using the EnsureUserHasRole middleware.
- Module password authentication for unauthenticated modules (Kiosk and TV Display) using CheckModulePassword middleware.

```mermaid
sequenceDiagram
participant Client as "Client"
participant Router as "routes/web.php"
participant RoleMW as "EnsureUserHasRole"
participant ModuleMW as "CheckModulePassword"
participant Controller as "Controller"
Client->>Router : "Request to protected route"
Router->>RoleMW : "Apply role middleware"
RoleMW->>RoleMW : "Check user auth and role"
alt "Unauthorized or insufficient role"
RoleMW-->>Client : "401/403"
else "Authorized"
RoleMW->>Controller : "Proceed to controller"
Controller-->>Client : "Response"
end
Client->>Router : "Request to module route"
Router->>ModuleMW : "Apply module password middleware"
ModuleMW->>ModuleMW : "Check session and lifetime"
alt "Not authenticated or expired"
ModuleMW-->>Client : "Redirect to login"
else "Authenticated"
ModuleMW->>Controller : "Proceed to controller"
Controller-->>Client : "Response"
end
```

**Diagram sources**
- [web.php:28-90](file://routes/web.php#L28-L90)
- [EnsureUserHasRole.php:16-35](file://app/Http/Middleware/EnsureUserHasRole.php#L16-L35)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)

## Detailed Component Analysis

### UserRole Enum
The UserRole enum defines the supported roles and provides convenience methods for labels and colors. The enum values are mapped to string identifiers stored in the database.

```mermaid
classDiagram
class UserRole {
+string Admin
+string Frontdesk
+string Officer
+string Monitor
+label() string
+color() string
}
```

**Diagram sources**
- [UserRole.php:5-31](file://app/Enums/UserRole.php#L5-L31)

**Section sources**
- [UserRole.php:1-32](file://app/Enums/UserRole.php#L1-L32)

### EnsureUserHasRole Middleware
The EnsureUserHasRole middleware enforces role-based access control:
- Denies access if the user is not authenticated (401).
- Allows access for Admin users regardless of required roles.
- Compares the user's role against the allowed roles list; denies if not matched (403).
- Proceeds to the next middleware/controller if authorized.

```mermaid
flowchart TD
Start(["Request enters middleware"]) --> GetUser["Get authenticated user"]
GetUser --> IsAuth{"User exists?"}
IsAuth --> |No| Abort401["Abort 401 Unauthorized"]
IsAuth --> |Yes| GetRole["Resolve user role value"]
GetRole --> IsAdmin{"Role is 'admin'?"}
IsAdmin --> |Yes| Next["Call next()"]
IsAdmin --> |No| CheckAllowed{"Role in allowed roles?"}
CheckAllowed --> |No| Abort403["Abort 403 Forbidden"]
CheckAllowed --> |Yes| Next
Next --> End(["Return response"])
Abort401 --> End
Abort403 --> End
```

**Diagram sources**
- [EnsureUserHasRole.php:16-35](file://app/Http/Middleware/EnsureUserHasRole.php#L16-L35)

**Section sources**
- [EnsureUserHasRole.php:1-37](file://app/Http/Middleware/EnsureUserHasRole.php#L1-L37)
- [app.php:20-23](file://bootstrap/app.php#L20-L23)

### CheckModulePassword Middleware
The CheckModulePassword middleware enforces module-specific authentication:
- Resolves session keys and timestamp keys based on the module name.
- Checks if the user is authenticated and within the session lifetime.
- Redirects to the appropriate login route if authentication is missing or expired.
- Maintains separate session keys for Kiosk and TV Display modules.

```mermaid
flowchart TD
Start(["Request enters module middleware"]) --> ResolveKeys["Resolve session and timestamp keys"]
ResolveKeys --> LoadSession["Load session values"]
LoadSession --> CheckAuth{"Authenticated and timestamp exists?"}
CheckAuth --> |No| ClearSession["Forget session keys"] --> Redirect["Redirect to module login"]
CheckAuth --> |Yes| CheckLifetime{"Within session lifetime?"}
CheckLifetime --> |No| ClearSession2["Forget session keys"] --> Redirect
CheckLifetime --> |Yes| Next["Call next()"]
Next --> End(["Return response"])
Redirect --> End
```

**Diagram sources**
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [ModuleSession.php:8-14](file://app/Enums/ModuleSession.php#L8-L14)

**Section sources**
- [CheckModulePassword.php:1-68](file://app/Http/Middleware/CheckModulePassword.php#L1-L68)
- [ModuleSession.php:1-15](file://app/Enums/ModuleSession.php#L1-L15)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

### User Model and Role Assignment
The User model:
- Casts the role attribute to the UserRole enum.
- Provides helper methods for role checks and active role resolution.
- Supports service assignments for Officer users.

Role assignment during user registration:
- The default CreateNewUser action does not set a role; it only hashes the password.
- Administrative user management via UserManagementController assigns roles and service permissions.

```mermaid
classDiagram
class User {
+int id
+string name
+string email
+UserRole role
+initials() string
+hasRole(role) bool
+activeRole() UserRole
+services() BelongsToMany
}
class UserRole {
<<enumeration>>
}
User --> UserRole : "casts role"
```

**Diagram sources**
- [User.php:14-99](file://app/Models/User.php#L14-L99)
- [UserRole.php:5-31](file://app/Enums/UserRole.php#L5-L31)

**Section sources**
- [User.php:1-99](file://app/Models/User.php#L1-L99)
- [CreateNewUser.php:1-34](file://app/Actions/Fortify/CreateNewUser.php#L1-L34)
- [UserManagementController.php:35-74](file://app/Http/Controllers/Admin/UserManagementController.php#L35-L74)

### Module-Specific Authentication Controllers
KioskController and TvDisplayController implement:
- Login with password validation against configuration values.
- Session management using ModuleSession keys.
- Logout that clears module-specific session data.
- Index pages for authenticated module access.

```mermaid
sequenceDiagram
participant Client as "Client"
participant KC as "KioskController"
participant TC as "TvDisplayController"
participant Config as "config/kiosk.php"
participant Session as "Session"
Client->>KC : "POST /kiosk/login"
KC->>Config : "Read kiosk_password"
KC->>KC : "Validate password hash"
alt "Valid"
KC->>Session : "Set authenticated keys"
KC-->>Client : "Redirect to /kiosk"
else "Invalid"
KC-->>Client : "Back with error"
end
Client->>TC : "POST /tv-display/login"
TC->>Config : "Read tv_display_password"
TC->>TC : "Validate password hash"
alt "Valid"
TC->>Session : "Set authenticated keys"
TC-->>Client : "Redirect to /tv-display"
else "Invalid"
TC-->>Client : "Back with error"
end
```

**Diagram sources**
- [KioskController.php:25-52](file://app/Http/Controllers/KioskController.php#L25-L52)
- [TvDisplayController.php:23-43](file://app/Http/Controllers/TvDisplayController.php#L23-L43)
- [kiosk.php:3-6](file://config/kiosk.php#L3-L6)

**Section sources**
- [KioskController.php:1-144](file://app/Http/Controllers/KioskController.php#L1-L144)
- [TvDisplayController.php:1-144](file://app/Http/Controllers/TvDisplayController.php#L1-L144)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

### Role-Based Routing and Access Control Patterns
Routes are grouped by role requirements:
- Frontdesk and Admin routes require Frontdesk or Admin roles.
- Officer routes require Officer role.
- Monitor routes require Monitor role.
- Admin routes require Admin role and include administrative endpoints.

```mermaid
graph LR
A["/frontdesk/antrian"]:::fd & A2["/petugas/loket/{counter}/*"]:::off & A3["/laporan/antrian"]:::mon & A4["/admin/*"]:::adm
classDef fd fill:#fff,stroke:#333,color:#000;
classDef off fill:#fff,stroke:#333,color:#000;
classDef mon fill:#fff,stroke:#333,color:#000;
classDef adm fill:#fff,stroke:#333,color:#000;
```

**Diagram sources**
- [web.php:42-90](file://routes/web.php#L42-L90)

**Section sources**
- [web.php:28-90](file://routes/web.php#L28-L90)

### Role Hierarchy and Permission Inheritance
- Admin has implicit access to all routes and can override role checks.
- Other roles (Frontdesk, Officer, Monitor) have explicit access to their designated routes.
- Officer users may be scoped to specific services via service assignments.

**Section sources**
- [EnsureUserHasRole.php:24-34](file://app/Http/Middleware/EnsureUserHasRole.php#L24-L34)
- [User.php:69-76](file://app/Models/User.php#L69-L76)
- [UserManagementController.php:46-70](file://app/Http/Controllers/Admin/UserManagementController.php#L46-L70)

### Examples of Role-Based Protection
- Controller method protection: Apply the role middleware to route groups to restrict access to specific roles.
- View rendering restrictions: Use the active role to conditionally render UI elements in views.
- Role switching: Admin users can switch roles using session-based role switching.

**Section sources**
- [web.php:28-90](file://routes/web.php#L28-L90)
- [User.php:81-91](file://app/Models/User.php#L81-L91)

### Administrative User Management
Administrative users can:
- Create users with assigned roles.
- Update user roles and service permissions.
- Delete users with safeguards (preventing self-deletion and users with active sessions).
- Manage services and counters through dedicated controllers.

```mermaid
sequenceDiagram
participant Admin as "Admin User"
participant UM as "UserManagementController"
participant DB as "Database"
Admin->>UM : "POST /admin/users"
UM->>DB : "Create user with role and hashed password"
UM-->>Admin : "Redirect with success"
Admin->>UM : "PUT /admin/users/{user}"
UM->>DB : "Update role and sync services"
UM-->>Admin : "Redirect with success"
Admin->>UM : "DELETE /admin/users/{user}"
UM->>DB : "Guard : self-delete and active sessions"
alt "Allowed"
UM->>DB : "Detach services and delete user"
UM-->>Admin : "Redirect with success"
else "Denied"
UM-->>Admin : "Redirect with error"
end
```

**Diagram sources**
- [UserManagementController.php:35-100](file://app/Http/Controllers/Admin/UserManagementController.php#L35-L100)

**Section sources**
- [UserManagementController.php:1-102](file://app/Http/Controllers/Admin/UserManagementController.php#L1-L102)

## Dependency Analysis
The following diagram shows key dependencies among components:

```mermaid
graph TB
E_role["UserRole enum"]
M_user["User model"]
MW_role["EnsureUserHasRole middleware"]
MW_module["CheckModulePassword middleware"]
C_kiosk["KioskController"]
C_tv["TvDisplayController"]
E_sess["ModuleSession enum"]
CFG_kiosk["config/kiosk.php"]
M_user --> E_role
MW_role --> M_user
MW_module --> E_sess
C_kiosk --> CFG_kiosk
C_tv --> CFG_kiosk
```

**Diagram sources**
- [UserRole.php:5-31](file://app/Enums/UserRole.php#L5-L31)
- [User.php:52-54](file://app/Models/User.php#L52-L54)
- [EnsureUserHasRole.php:16-24](file://app/Http/Middleware/EnsureUserHasRole.php#L16-L24)
- [CheckModulePassword.php:17-24](file://app/Http/Middleware/CheckModulePassword.php#L17-L24)
- [ModuleSession.php:8-14](file://app/Enums/ModuleSession.php#L8-L14)
- [KioskController.php:31-42](file://app/Http/Controllers/KioskController.php#L31-L42)
- [TvDisplayController.php:29-40](file://app/Http/Controllers/TvDisplayController.php#L29-L40)
- [kiosk.php:3-6](file://config/kiosk.php#L3-L6)

**Section sources**
- [app.php:20-23](file://bootstrap/app.php#L20-L23)
- [web.php:1-127](file://routes/web.php#L1-L127)

## Performance Considerations
- Middleware evaluation occurs early in the request lifecycle; keep role checks lightweight.
- Session-based module authentication avoids repeated password hashing for subsequent requests.
- Use enum casting for role comparisons to minimize string operations.

## Troubleshooting Guide
Common issues and resolutions:
- 401 Unauthorized: Ensure the user is authenticated before accessing role-protected routes.
- 403 Forbidden: Verify the user's role matches the required roles for the route.
- Module login failures: Confirm module passwords in configuration and that the session keys are being set correctly.
- Session expiration: Check module session lifetime configuration and ensure clients refresh sessions before expiry.

**Section sources**
- [EnsureUserHasRole.php:20-34](file://app/Http/Middleware/EnsureUserHasRole.php#L20-L34)
- [CheckModulePassword.php:22-30](file://app/Http/Middleware/CheckModulePassword.php#L22-L30)
- [kiosk.php:6-7](file://config/kiosk.php#L6-L7)

## Conclusion
The application implements a clear separation between authenticated RBAC and module-specific password authentication. Roles are enforced at the routing layer using middleware, while modules maintain independent session lifetimes. Administrative users can manage roles and permissions centrally, ensuring secure and flexible access control across the system.