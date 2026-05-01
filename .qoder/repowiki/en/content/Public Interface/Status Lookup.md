# Status Lookup

<cite>
**Referenced Files in This Document**
- [PublicQueueController.php](file://app/Http/Controllers/PublicQueueController.php)
- [PublicQueueController.php (API)](file://app/Http/Controllers/Api/PublicQueueController.php)
- [LookupQueueTicketRequest.php](file://app/Http/Requests/LookupQueueTicketRequest.php)
- [LookupTicketRequest.php (API)](file://app/Http/Requests/Api/LookupTicketRequest.php)
- [PublicQueueTicketResource.php](file://app/Http/Resources/PublicQueueTicketResource.php)
- [QueueTicket.php](file://app/Models/QueueTicket.php)
- [QueueStatus.php](file://app/Enums/QueueStatus.php)
- [web.php](file://routes/web.php)
- [lookup.blade.php](file://resources/views/pages/public/antrian/lookup.blade.php)
- [confirmation.blade.php](file://resources/views/pages/public/antrian/confirmation.blade.php)
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
9. [Security Measures](#security-measures)
10. [Conclusion](#conclusion)

## Introduction
This document explains the Public Status Lookup functionality that allows visitors to check the current status of their queue ticket. It covers the end-to-end process from entering a ticket number and service date, to validating inputs, querying the database, computing queue position, and presenting results in a user-friendly interface. It also documents the API endpoint for programmatic access, error handling strategies, and security measures to prevent enumeration attacks.

## Project Structure
The Public Status Lookup spans several layers:
- Web routes define the public endpoints for browsing and submitting the lookup form.
- A controller handles the lookup request, validates inputs, queries the database, computes queue position, and renders the result page.
- A model encapsulates the queue ticket business logic, including queue position calculation.
- Blade templates render the lookup form and results.
- An API controller provides JSON responses for external integrations.
- Validation requests enforce input rules for both web and API flows.

```mermaid
graph TB
subgraph "Routing"
RWeb["routes/web.php<br/>Defines /antrian/cek"]
end
subgraph "Controllers"
PC["PublicQueueController.php<br/>Handles web lookup"]
APIC["PublicQueueController.php (API)<br/>Handles API lookup"]
end
subgraph "Validation"
VR["LookupQueueTicketRequest.php<br/>Web validation"]
VRAPI["LookupTicketRequest.php (API)<br/>API validation"]
end
subgraph "Domain"
QT["QueueTicket.php<br/>Model + queue position calc"]
QS["QueueStatus.php<br/>Status enum"]
end
subgraph "Presentation"
BL["lookup.blade.php<br/>Lookup UI"]
CF["confirmation.blade.php<br/>Confirmation UI"]
PR["PublicQueueTicketResource.php<br/>Public API resource"]
end
RWeb --> PC
RWeb --> APIC
PC --> VR
APIC --> VRAPI
PC --> QT
APIC --> QT
QT --> QS
PC --> BL
PC --> CF
APIC --> PR
```

**Diagram sources**
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [QueueStatus.php:5](file://app/Enums/QueueStatus.php#L5)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)
- [confirmation.blade.php:1](file://resources/views/pages/public/antrian/confirmation.blade.php#L1)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

**Section sources**
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [QueueStatus.php:5](file://app/Enums/QueueStatus.php#L5)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)
- [confirmation.blade.php:1](file://resources/views/pages/public/antrian/confirmation.blade.php#L1)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

## Core Components
- PublicQueueController (web): Processes the lookup form submission, validates inputs, finds the ticket by number and service date, computes queue position for waiting tickets, and renders the result page.
- PublicQueueController (API): Provides a JSON endpoint to look up tickets by number and service date, returning masked visitor information and queue position via a resource.
- LookupQueueTicketRequest (web): Validates that ticket_number and service_date are present and properly formatted for the web lookup.
- LookupTicketRequest (API): Validates that ticket_number and service_date are required and valid for the API lookup.
- QueueTicket model: Encapsulates the ticket domain logic, including queue position calculation for waiting tickets within the same pool and day.
- QueueStatus enum: Defines status values and provides localized labels/colors used in UI rendering.
- PublicQueueTicketResource: Transforms a ticket into a JSON response with masked visitor name and queue position.
- lookup.blade.php: Presents the lookup form and displays results, including status-specific guidance and queue position.
- confirmation.blade.php: Displays booking confirmation with queue position and printing instructions.

**Section sources**
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [QueueStatus.php:5](file://app/Enums/QueueStatus.php#L5)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)
- [confirmation.blade.php:1](file://resources/views/pages/public/antrian/confirmation.blade.php#L1)

## Architecture Overview
The lookup flow integrates routing, validation, controller logic, model computation, and presentation.

```mermaid
sequenceDiagram
participant U as "Visitor Browser"
participant R as "routes/web.php"
participant C as "PublicQueueController"
participant Q as "QueueTicket model"
participant V as "LookupQueueTicketRequest"
participant UI as "lookup.blade.php"
U->>R : GET /antrian/cek?ticket_number=&service_date=
R->>C : lookup(request)
C->>V : validate()
V-->>C : validated data
C->>Q : find by ticket_number + service_date
Q-->>C : ticket or null
alt ticket found and status=Waiting
C->>Q : count tickets with smaller sequence_number
Q-->>C : queuePosition
else not found or not Waiting
C->>C : queuePosition = 0
end
C->>UI : render with ticket + queuePosition
UI-->>U : display status + guidance
```

**Diagram sources**
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)

## Detailed Component Analysis

### Web Lookup Flow
- Endpoint: GET /antrian/cek
- Request validation: ticket_number and service_date are optional in the web request but required for meaningful results.
- Ticket retrieval: Finds a single ticket matching both ticket_number and service_date.
- Queue position calculation: For waiting tickets in the same queue pool and service date, counts how many waiting tickets precede the current ticket by sequence_number and adds 1.
- Result presentation: Renders lookup.blade.php with either the ticket details and queue position or an error message when not found.

```mermaid
flowchart TD
Start(["GET /antrian/cek"]) --> Validate["Validate request fields"]
Validate --> BuildQuery["Query ticket by ticket_number and service_date"]
BuildQuery --> Found{"Ticket found?"}
Found --> |No| NotFound["Render 'not found' card"]
Found --> |Yes| StatusCheck["Is status Waiting and has queuePool?"]
StatusCheck --> |No| ShowDetails["Render ticket details without position"]
StatusCheck --> |Yes| CountAhead["Count waiting tickets with smaller sequence_number"]
CountAhead --> ComputePos["queuePosition = count + 1"]
ComputePos --> ShowDetails
ShowDetails --> End(["Page rendered"])
NotFound --> End
```

**Diagram sources**
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [lookup.blade.php:78](file://resources/views/pages/public/antrian/lookup.blade.php#L78)

**Section sources**
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [lookup.blade.php:78](file://resources/views/pages/public/antrian/lookup.blade.php#L78)

### API Lookup Flow
- Endpoint: GET /api/public/queue/lookup
- Request validation: ticket_number and service_date are required and must be valid.
- Ticket retrieval: Same as web flow, with eager loading of related entities.
- Response: Returns PublicQueueTicketResource, which masks the visitor name and includes queue position computed by the model.
- Error handling: Returns 404 with a message when the ticket is not found.

```mermaid
sequenceDiagram
participant Client as "External Client"
participant API as "PublicQueueController (API)"
participant Q as "QueueTicket model"
participant Res as "PublicQueueTicketResource"
Client->>API : GET /api/public/queue/lookup?ticket_number=&service_date=
API->>API : validate()
API->>Q : find by ticket_number + service_date
Q-->>API : ticket or null
alt ticket found
API->>Res : transform ticket
Res-->>Client : JSON with masked visitor name + queue position
else not found
API-->>Client : 404 Not Found
end
```

**Diagram sources**
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

**Section sources**
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

### Queue Position Calculation Algorithm
- Precondition: Only applies when the ticket status is Waiting and belongs to a queue pool.
- Method: Counts all waiting tickets in the same queue pool and on the same service_date that have a smaller sequence_number than the current ticket, then adds 1 to derive the position.
- Complexity: Linear in the number of waiting tickets sharing the same pool and date; acceptable for typical daily volumes.

```mermaid
flowchart TD
A["Input: ticket (Waiting, has queuePool)"] --> B["Filter: same queue_pool_id and same service_date"]
B --> C["Filter: status = Waiting"]
C --> D["Filter: sequence_number < current.sequence_number"]
D --> E["Count results"]
E --> F["Position = count + 1"]
```

**Diagram sources**
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [PublicQueueController.php:71](file://app/Http/Controllers/PublicQueueController.php#L71)

**Section sources**
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [PublicQueueController.php:71](file://app/Http/Controllers/PublicQueueController.php#L71)

### UI Presentation Details
- Lookup form: Two fields (ticket_number and service_date) with icons and inline validation feedback.
- Results area: Shows ticket header with status badge, visitor/service/date details, and a contextual message based on status. Includes queue position display for waiting tickets.
- Not found state: Friendly message with a button to retry the search.
- Confirmation page: Displays booking details, queue position, and printing instructions.

**Section sources**
- [lookup.blade.php:15](file://resources/views/pages/public/antrian/lookup.blade.php#L15)
- [lookup.blade.php:78](file://resources/views/pages/public/antrian/lookup.blade.php#L78)
- [confirmation.blade.php:90](file://resources/views/pages/public/antrian/confirmation.blade.php#L90)

## Dependency Analysis
- Controllers depend on:
  - Validation requests for input rules.
  - QueueTicket model for querying and computing queue position.
  - Blade templates for rendering results.
- PublicQueueTicketResource depends on:
  - QueueTicket model for queue position computation.
  - QueueStatus enum for labels/colors.
- Routes bind:
  - GET /antrian/cek to the web controller’s lookup method.
  - GET /api/public/queue/lookup to the API controller’s lookup method.

```mermaid
graph LR
R["routes/web.php"] --> PC["PublicQueueController"]
R --> APIC["PublicQueueController (API)"]
PC --> VR["LookupQueueTicketRequest"]
APIC --> VRAPI["LookupTicketRequest (API)"]
PC --> QT["QueueTicket"]
APIC --> QT
QT --> QS["QueueStatus"]
PC --> UI["lookup.blade.php"]
PC --> CF["confirmation.blade.php"]
APIC --> PR["PublicQueueTicketResource"]
```

**Diagram sources**
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [QueueStatus.php:5](file://app/Enums/QueueStatus.php#L5)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)
- [confirmation.blade.php:1](file://resources/views/pages/public/antrian/confirmation.blade.php#L1)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

**Section sources**
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueController.php:58](file://app/Http/Controllers/PublicQueueController.php#L58)
- [PublicQueueController.php (API):36](file://app/Http/Controllers/Api/PublicQueueController.php#L36)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [QueueTicket.php:82](file://app/Models/QueueTicket.php#L82)
- [QueueStatus.php:5](file://app/Enums/QueueStatus.php#L5)
- [lookup.blade.php:1](file://resources/views/pages/public/antrian/lookup.blade.php#L1)
- [confirmation.blade.php:1](file://resources/views/pages/public/antrian/confirmation.blade.php#L1)
- [PublicQueueTicketResource.php:10](file://app/Http/Resources/PublicQueueTicketResource.php#L10)

## Performance Considerations
- Indexing: Ensure database indexes exist on queue tickets for (ticket_number, service_date) and (queue_pool_id, service_date, status, sequence_number) to optimize lookups and counting.
- Query efficiency: The controller performs two queries (find ticket and count preceding waiting tickets). For very large pools, consider caching recent positions or precomputing daily rankings.
- Rendering cost: Blade templates are lightweight; avoid heavy computations in views.
- Throttling: Routes are throttled to limit abuse (e.g., 30 requests per minute for the web lookup endpoint).

**Section sources**
- [web.php:25](file://routes/web.php#L25)

## Troubleshooting Guide
Common scenarios and their handling:
- Ticket not found:
  - Web: Displays a friendly “not found” card with guidance to retry.
  - API: Returns 404 with a message indicating the ticket was not found.
- Invalid inputs:
  - Web: Validation errors are surfaced via field-level messages.
  - API: Validation messages indicate missing or invalid fields.
- Expired or cancelled bookings:
  - The system does not auto-expire tickets; however, cancelled tickets are excluded from queue calculations. If a ticket is marked as Cancelled, the UI shows cancellation messaging.
- Maintenance or downtime:
  - The lookup endpoints are part of the public web/API surface. Ensure monitoring and fallbacks are configured at the infrastructure level.

**Section sources**
- [lookup.blade.php:133](file://resources/views/pages/public/antrian/lookup.blade.php#L133)
- [PublicQueueController.php (API):40](file://app/Http/Controllers/Api/PublicQueueController.php#L40)
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):22](file://app/Http/Requests/Api/LookupTicketRequest.php#L22)

## Security Measures
- Input validation:
  - Web: Optional fields in the request allow graceful handling of empty submissions; the controller still requires both fields for meaningful lookups.
  - API: Required fields ensure clients must provide both ticket_number and service_date.
- Rate limiting:
  - The web lookup endpoint is throttled to reduce brute-force attempts.
- Output sanitization:
  - Public API resource masks the visitor name to protect privacy.
  - Resource includes only necessary fields and derived queue position.
- Signed URLs:
  - Confirmation page uses signed routes to prevent open redirect-style misuse.
- Enumeration resistance:
  - The lookup returns a generic “not found” message without revealing whether the ticket exists but is in a different state.
  - Consider adding CAPTCHA or additional rate limits for repeated failed attempts if under attack.

**Section sources**
- [LookupQueueTicketRequest.php:22](file://app/Http/Requests/LookupQueueTicketRequest.php#L22)
- [LookupTicketRequest.php (API):14](file://app/Http/Requests/Api/LookupTicketRequest.php#L14)
- [web.php:25](file://routes/web.php#L25)
- [PublicQueueTicketResource.php:28](file://app/Http/Resources/PublicQueueTicketResource.php#L28)
- [PublicQueueController.php:104](file://app/Http/Controllers/PublicQueueController.php#L104)

## Conclusion
The Public Status Lookup provides a robust, user-friendly mechanism for visitors to track their queue ticket status. It combines strict validation, efficient model-driven queue position computation, and clear UI messaging. The API endpoint supports external integrations while maintaining privacy and security. With appropriate indexing and rate limiting, the system remains performant and resilient against common abuse patterns.