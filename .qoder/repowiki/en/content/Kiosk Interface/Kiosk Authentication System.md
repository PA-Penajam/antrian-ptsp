# Kiosk Authentication System

<cite>
**Referenced Files in This Document**
- [CheckModulePassword.php](file://app/Http/Middleware/CheckModulePassword.php)
- [ModuleSession.php](file://app/Enums/ModuleSession.php)
- [KioskController.php](file://app/Http/Controllers/KioskController.php)
- [TvDisplayController.php](file://app/Http/Controllers/TvDisplayController.php)
- [kiosk.php](file://config/kiosk.php)
- [session.php](file://config/session.php)
- [web.php](file://routes/web.php)
- [login.blade.php](file://resources/views/pages/kiosk/login.blade.php)
- [index.blade.php](file://resources/views/pages/kiosk/index.blade.php)
- [kiosk.blade.php](file://resources/views/layouts/kiosk.blade.php)
- [KioskAuthTest.php](file://tests/Feature/Kiosk/KioskAuthTest.php)
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
This document describes the Kiosk Authentication System used for password-based access to kiosk and TV display modules. It explains the hashed password validation process, session management using ModuleSession enums, authentication state handling, differences between modern and legacy flows, error handling for invalid credentials, session timeout mechanisms, security considerations, session cleanup, and middleware integration. It also provides troubleshooting guidance and security best practices tailored for kiosk environments.

## Project Structure
The authentication system spans controllers, middleware, configuration, routes, and Blade views. Key areas:
- Controllers handle login, logout, and module-specific pages
- Middleware enforces session validity and redirects unauthenticated requests
- Configuration defines password hashes and session lifetimes
- Routes register both modern and legacy endpoints
- Views render login pages and protected content

```mermaid
graph TB
subgraph "Routes"
R1["web.php<br/>Defines kiosk and tv-display routes"]
end
subgraph "Middleware"
MW["CheckModulePassword<br/>Session validation and redirects"]
end
subgraph "Controllers"
KC["KioskController<br/>Handles kiosk auth and pages"]
TVC["TvDisplayController<br/>Handles TV display auth and pages"]
end
subgraph "Configuration"
CFG["kiosk.php<br/>Module passwords and session lifetime"]
SCFG["session.php<br/>Framework session defaults"]
end
subgraph "Views"
LV["login.blade.php<br/>Modern kiosk login form"]
LIDX["index.blade.php<br/>Modern kiosk content"]
LAYOUT["kiosk.blade.php<br/>Kiosk layout"]
end
R1 --> MW
MW --> KC
MW --> TVC
KC --> CFG
TVC --> CFG
KC --> LV
KC --> LIDX
LIDX --> LAYOUT
LV --> LAYOUT
CFG --> SCFG
```

**Diagram sources**
- [web.php:92-124](file://routes/web.php#L92-L124)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [TvDisplayController.php:23-43](file://app/Http/Controllers/TvDisplayController.php#L23-L43)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)
- [session.php:21-37](file://config/session.php#L21-L37)
- [login.blade.php:49-85](file://resources/views/pages/kiosk/login.blade.php#L49-L85)
- [index.blade.php:1-4](file://resources/views/pages/kiosk/index.blade.php#L1-L4)
- [kiosk.blade.php:1-67](file://resources/views/layouts/kiosk.blade.php#L1-L67)

**Section sources**
- [web.php:92-124](file://routes/web.php#L92-L124)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [TvDisplayController.php:23-43](file://app/Http/Controllers/TvDisplayController.php#L23-L43)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)
- [session.php:21-37](file://config/session.php#L21-L37)
- [login.blade.php:49-85](file://resources/views/pages/kiosk/login.blade.php#L49-L85)
- [index.blade.php:1-4](file://resources/views/pages/kiosk/index.blade.php#L1-L4)
- [kiosk.blade.php:1-67](file://resources/views/layouts/kiosk.blade.php#L1-L67)

## Core Components
- ModuleSession enum: Centralized session key constants for kiosk and TV display authentication flags and timestamps
- CheckModulePassword middleware: Validates session state and enforces session lifetime
- KioskController: Handles kiosk login/logout and modern/legacy flows
- TvDisplayController: Handles TV display login/logout and modern/legacy flows
- Configuration: kiosk.php defines module passwords and session lifetime; session.php defines framework session defaults
- Routes: Register authentication endpoints and protected routes under module.password middleware

Key responsibilities:
- Password hashing and verification using framework hash utilities
- Session creation and cleanup using ModuleSession keys
- Redirects to appropriate login URLs based on module
- Throttling to mitigate brute-force attempts

**Section sources**
- [ModuleSession.php:8-14](file://app/Enums/ModuleSession.php#L8-L14)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [TvDisplayController.php:23-43](file://app/Http/Controllers/TvDisplayController.php#L23-L43)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)
- [session.php:21-37](file://config/session.php#L21-L37)
- [web.php:92-124](file://routes/web.php#L92-L124)

## Architecture Overview
The system uses a layered approach:
- Presentation: Blade views for login and content
- Routing: Route definitions with middleware groups
- Authentication: Module-specific controllers validating hashed passwords
- Session Management: Middleware checking session flags and timestamps
- Configuration: Environment-driven passwords and session lifetimes

```mermaid
sequenceDiagram
participant U as "User"
participant R as "Router (web.php)"
participant MW as "CheckModulePassword"
participant C as "KioskController"
participant CFG as "kiosk.php"
participant S as "Session Store"
U->>R : GET /kiosk
R->>MW : module.password : kiosk
MW->>S : read kiosk_authenticated, kiosk_authenticated_at
MW->>MW : compare timestamp vs config('kiosk.session_lifetime')
alt Not authenticated or expired
MW->>U : redirect to /kiosk/login
else Authenticated and valid
MW->>C : pass to controller
C-->>U : render /kiosk content
end
U->>R : POST /kiosk/login
R->>C : login(request)
C->>CFG : read hashed password
C->>C : Hash : : check(password, hashed)
alt Invalid
C-->>U : redirect back with error
else Valid
C->>S : set kiosk_authenticated=true, kiosk_authenticated_at=timestamp
C-->>U : redirect to /kiosk
end
```

**Diagram sources**
- [web.php:92-98](file://routes/web.php#L92-L98)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

## Detailed Component Analysis

### ModuleSession Enum
The enum centralizes session key naming for authentication flags and timestamps, ensuring consistency across kiosk and TV display modules.

```mermaid
classDiagram
class ModuleSession {
<<enumeration>>
+string KioskAuthenticated
+string KioskAuthenticatedAt
+string TvDisplayAuthenticated
+string TvDisplayAuthenticatedAt
}
```

**Diagram sources**
- [ModuleSession.php:8-14](file://app/Enums/ModuleSession.php#L8-L14)

**Section sources**
- [ModuleSession.php:8-14](file://app/Enums/ModuleSession.php#L8-L14)

### CheckModulePassword Middleware
Responsibilities:
- Resolves session and timestamp keys based on module
- Reads authentication flags and timestamps from session
- Enforces session lifetime using config('kiosk.session_lifetime')
- Clears invalid/expired session data and redirects to module login URL
- Allows request to proceed if authenticated and not expired

```mermaid
flowchart TD
Start(["Incoming Request"]) --> ResolveKeys["Resolve session and timestamp keys"]
ResolveKeys --> ReadSession["Read flags and timestamps"]
ReadSession --> CheckState{"Authenticated and not expired?"}
CheckState --> |No| ClearSession["Forget auth flags and timestamps"]
ClearSession --> Redirect["Redirect to module login URL"]
CheckState --> |Yes| Next["Call next middleware/controller"]
Redirect --> End(["Exit"])
Next --> End
```

**Diagram sources**
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [CheckModulePassword.php:38-66](file://app/Http/Middleware/CheckModulePassword.php#L38-L66)

**Section sources**
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)
- [CheckModulePassword.php:38-66](file://app/Http/Middleware/CheckModulePassword.php#L38-L66)

### KioskController: Modern and Legacy Flows
Modern flow:
- Login endpoint validates password against hashed value from configuration
- On success, sets authentication flags and timestamp in session
- Redirects to kiosk index page

Legacy flow:
- Mirrors modern behavior but targets legacy endpoints and views
- Used for older devices without modern frontend frameworks

Additional behaviors:
- Logout clears authentication flags and timestamps
- Index page renders the kiosk booking interface

```mermaid
sequenceDiagram
participant U as "User"
participant R as "Router"
participant KC as "KioskController"
participant CFG as "kiosk.php"
participant S as "Session Store"
U->>R : POST /kiosk/login
R->>KC : login(request)
KC->>CFG : get('kiosk.kiosk_password')
KC->>KC : Hash : : check(password, hashed)
alt Invalid
KC-->>U : back with error
else Valid
KC->>S : set kiosk_authenticated=true, kiosk_authenticated_at=timestamp
KC-->>U : redirect to /kiosk
end
U->>R : POST /kiosk/logout
R->>KC : logout()
KC->>S : forget auth flags and timestamps
KC-->>U : redirect to /kiosk/login
```

**Diagram sources**
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [web.php:92-98](file://routes/web.php#L92-L98)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

**Section sources**
- [KioskController.php:25-45](file://app/Http/Controllers/KioskController.php#L25-L45)
- [KioskController.php:47-52](file://app/Http/Controllers/KioskController.php#L47-L52)
- [KioskController.php:54-57](file://app/Http/Controllers/KioskController.php#L54-L57)
- [KioskController.php:59-84](file://app/Http/Controllers/KioskController.php#L59-L84)
- [web.php:92-98](file://routes/web.php#L92-L98)
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)

### TvDisplayController: Modern and Legacy Flows
Similar pattern to KioskController but for TV display:
- Uses TV display password from configuration
- Sets TV display authentication flags and timestamps
- Provides legacy endpoints for older devices

**Section sources**
- [TvDisplayController.php:23-43](file://app/Http/Controllers/TvDisplayController.php#L23-L43)
- [TvDisplayController.php:45-50](file://app/Http/Controllers/TvDisplayController.php#L45-L50)
- [TvDisplayController.php:57-82](file://app/Http/Controllers/TvDisplayController.php#L57-L82)
- [web.php:108-114](file://routes/web.php#L108-L114)
- [web.php:117-124](file://routes/web.php#L117-L124)

### Configuration and Session Lifetime
- kiosk.php:
  - Defines module passwords for kiosk and TV display
  - Defines session lifetime in minutes
- session.php:
  - Framework session defaults (driver, lifetime, cookie policies)
  - Separate from module session lifetime; module middleware overrides per-module

**Section sources**
- [kiosk.php:1-8](file://config/kiosk.php#L1-L8)
- [session.php:21-37](file://config/session.php#L21-L37)
- [CheckModulePassword.php:24](file://app/Http/Middleware/CheckModulePassword.php#L24)

### Views and Layouts
- Modern login view renders a password field with CSRF protection and error display
- Kiosk layout provides shared styling and scripts for kiosk pages
- Index view hosts the kiosk booking Livewire component

**Section sources**
- [login.blade.php:49-85](file://resources/views/pages/kiosk/login.blade.php#L49-L85)
- [kiosk.blade.php:1-67](file://resources/views/layouts/kiosk.blade.php#L1-L67)
- [index.blade.php:1-4](file://resources/views/pages/kiosk/index.blade.php#L1-L4)

## Dependency Analysis
- Controllers depend on:
  - Hash utilities for password verification
  - Configuration for hashed passwords and session lifetime
  - ModuleSession enum for consistent session keys
- Middleware depends on:
  - ModuleSession enum for session key resolution
  - Configuration for session lifetime
  - Session store for flags and timestamps
- Routes depend on:
  - Middleware registration for module.password
  - Controller actions for handling requests

```mermaid
graph LR
KC["KioskController"] --> MS["ModuleSession"]
KC --> CFG["kiosk.php"]
KC --> HASH["Hash Utilities"]
TVC["TvDisplayController"] --> MS
TVC --> CFG
MW["CheckModulePassword"] --> MS
MW --> CFG
MW --> S["Session Store"]
R["web.php Routes"] --> MW
R --> KC
R --> TVC
```

**Diagram sources**
- [KioskController.php:15](file://app/Http/Controllers/KioskController.php#L15)
- [TvDisplayController.php:12](file://app/Http/Controllers/TvDisplayController.php#L12)
- [CheckModulePassword.php:5-6](file://app/Http/Middleware/CheckModulePassword.php#L5-L6)
- [web.php:92-124](file://routes/web.php#L92-L124)

**Section sources**
- [KioskController.php:15](file://app/Http/Controllers/KioskController.php#L15)
- [TvDisplayController.php:12](file://app/Http/Controllers/TvDisplayController.php#L12)
- [CheckModulePassword.php:5-6](file://app/Http/Middleware/CheckModulePassword.php#L5-L6)
- [web.php:92-124](file://routes/web.php#L92-L124)

## Performance Considerations
- Hash verification cost: Password checks are constant-time comparisons after hashing; ensure passwords are properly hashed at rest
- Session reads/writes: Minimal overhead; avoid excessive session writes outside login/logout
- Middleware evaluation: Single pass per request; keep session keys short and consistent
- Throttling: Built-in throttling on authentication endpoints reduces load during brute-force attempts
- Session lifetime: Shorter lifetimes reduce stale session accumulation; adjust based on operational needs

## Troubleshooting Guide
Common issues and resolutions:
- Invalid credentials:
  - Symptom: Redirect back to login with error message
  - Cause: Hash mismatch or empty configured password
  - Resolution: Verify environment variables and ensure password is hashed
  - Evidence: Tests assert redirect with errors and missing authenticated flags
- Unauthenticated access:
  - Symptom: Redirect to module login
  - Cause: Missing or cleared authentication flags
  - Resolution: Ensure login succeeds and session is set
  - Evidence: Tests assert redirect to login and session clearing on logout
- Expired session:
  - Symptom: Automatic redirect to login after session lifetime
  - Cause: Timestamp older than configured lifetime
  - Resolution: Adjust kiosk.session_lifetime or re-authenticate
  - Evidence: Middleware compares timestamp against config value
- Legacy device behavior:
  - Symptom: Different endpoints and views
  - Cause: Legacy routes and controllers
  - Resolution: Use legacy endpoints for older devices

Security best practices:
- Use strong, randomly generated hashed passwords for kiosk and TV display
- Regularly rotate module passwords and update environment variables
- Limit session lifetime to minimize exposure windows
- Apply network-level controls (firewalls, VLANs) around kiosk/TV devices
- Monitor authentication failures and throttle thresholds
- Ensure HTTPS and secure cookie settings for production deployments

**Section sources**
- [KioskAuthTest.php:35-46](file://tests/Feature/Kiosk/KioskAuthTest.php#L35-L46)
- [KioskAuthTest.php:48-57](file://tests/Feature/Kiosk/KioskAuthTest.php#L48-L57)
- [KioskAuthTest.php:6-12](file://tests/Feature/Kiosk/KioskAuthTest.php#L6-L12)
- [CheckModulePassword.php:24](file://app/Http/Middleware/CheckModulePassword.php#L24)
- [web.php:94](file://routes/web.php#L94)
- [web.php:102](file://routes/web.php#L102)

## Conclusion
The Kiosk Authentication System provides a robust, module-specific authentication mechanism using hashed passwords and session-based state management. The CheckModulePassword middleware ensures session validity and enforces configurable timeouts, while controllers implement consistent login/logout flows for both modern and legacy devices. Proper configuration, session cleanup, and security practices are essential for reliable operation in kiosk environments.