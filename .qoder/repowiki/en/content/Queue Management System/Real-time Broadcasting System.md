# Real-time Broadcasting System

<cite>
**Referenced Files in This Document**
- [TicketCalled.php](file://app/Events/TicketCalled.php)
- [LogQueueActivity.php](file://app/Actions/Queue/LogQueueActivity.php)
- [reverb.php](file://config/reverb.php)
- [broadcasting.php](file://config/broadcasting.php)
- [channels.php](file://routes/channels.php)
- [echo.js](file://resources/js/echo.js)
- [app.js](file://resources/js/app.js)
- [PublicQueueController.php](file://app/Http/Controllers/PublicQueueController.php)
- [KioskController.php](file://app/Http/Controllers/KioskController.php)
- [TvDisplayController.php](file://app/Http/Controllers/TvDisplayController.php)
- [TvDisplay.php](file://app/Livewire/TvDisplay.php)
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
This document describes the real-time broadcasting system that synchronizes queue interfaces across the application. It focuses on the TicketCalled event, the LogQueueActivity action for audit trails, Laravel Reverb configuration, WebSocket connections, and message routing. It also documents end-to-end workflows for public web, kiosk, TV display, and administrative interfaces, along with connection management, error handling, message queuing, performance optimization, and client-side integration using Echo.js.

## Project Structure
The real-time system spans backend event and action classes, configuration files for broadcasting and Reverb, route channels, and frontend integration via Echo.js. Livewire components subscribe to events and update UI reactively.

```mermaid
graph TB
subgraph "Backend"
Evt["TicketCalled Event"]
Act["LogQueueActivity Action"]
CfgB["Broadcasting Config"]
CfgR["Reverb Config"]
Ch["Route Channels"]
PubCtrl["PublicQueueController"]
KioskCtrl["KioskController"]
TvCtrl["TvDisplayController"]
LwTv["Livewire TvDisplay Component"]
end
subgraph "Frontend"
EchoJS["Echo.js Client"]
AppJS["App Bootstrap"]
end
PubCtrl --> Act
KioskCtrl --> Act
Act --> Evt
Evt --> CfgB
CfgB --> CfgR
CfgR --> EchoJS
EchoJS --> LwTv
TvCtrl --> LwTv
Ch --> EchoJS
AppJS --> EchoJS
```

**Diagram sources**
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [LogQueueActivity.php:8-28](file://app/Actions/Queue/LogQueueActivity.php#L8-L28)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-57](file://config/reverb.php#L29-L57)
- [channels.php:5-7](file://routes/channels.php#L5-L7)
- [PublicQueueController.php:39-56](file://app/Http/Controllers/PublicQueueController.php#L39-L56)
- [KioskController.php:114-142](file://app/Http/Controllers/KioskController.php#L114-L142)
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [app.js:9](file://resources/js/app.js#L9)

**Section sources**
- [broadcasting.php:18](file://config/broadcasting.php#L18)
- [reverb.php:16](file://config/reverb.php#L16)
- [channels.php:5-7](file://routes/channels.php#L5-L7)

## Core Components
- TicketCalled event: Defines the payload and broadcast channel for queue announcements.
- LogQueueActivity action: Persists audit trail entries for queue operations.
- Reverb and broadcasting configs: Configure transport, TLS, rate limiting, and scaling.
- Route channels: Secure per-user private channels.
- Client-side Echo integration: Establishes WebSocket connections and subscribes to channels.
- Livewire TvDisplay component: Subscribes to events and drives UI updates and TTS announcements.

**Section sources**
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)
- [LogQueueActivity.php:13-27](file://app/Actions/Queue/LogQueueActivity.php#L13-L27)
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:31-57](file://config/reverb.php#L31-L57)
- [channels.php:5-7](file://routes/channels.php#L5-L7)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

## Architecture Overview
The system uses Laravel Reverb as the WebSocket server and Laravel Echo on the client to subscribe to channels. When a queue operation occurs, an action persists activity and fires the TicketCalled event. The event broadcasts to the public channel, and Livewire components re-render automatically.

```mermaid
sequenceDiagram
participant Client as "Client App"
participant Controller as "Controllers"
participant Action as "LogQueueActivity"
participant Event as "TicketCalled"
participant Broadcast as "Broadcast Manager"
participant Reverb as "Reverb Server"
participant Echo as "Echo.js"
participant Livewire as "Livewire TvDisplay"
Client->>Controller : "User performs queue action"
Controller->>Action : "Persist activity"
Action-->>Controller : "Activity recorded"
Controller->>Event : "Dispatch TicketCalled(queueTicketId)"
Event->>Broadcast : "broadcastOn() returns 'public-queue'"
Broadcast->>Reverb : "Publish to channel"
Reverb-->>Echo : "Deliver event"
Echo-->>Livewire : "Trigger 'TicketCalled'"
Livewire->>Livewire : "refreshQueue() and re-render"
```

**Diagram sources**
- [PublicQueueController.php:39-56](file://app/Http/Controllers/PublicQueueController.php#L39-L56)
- [KioskController.php:114-142](file://app/Http/Controllers/KioskController.php#L114-L142)
- [LogQueueActivity.php:13-27](file://app/Actions/Queue/LogQueueActivity.php#L13-L27)
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:31-57](file://config/reverb.php#L31-L57)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

## Detailed Component Analysis

### TicketCalled Event
- Purpose: Announces that a queue ticket has been called.
- Payload: queueTicketId (integer identifier).
- Broadcast channel: public-queue.
- Timing: ShouldBroadcastNow ensures immediate delivery without queueing.

```mermaid
classDiagram
class TicketCalled {
+int queueTicketId
+__construct(queueTicketId)
+broadcastOn() Channel[]
}
```

**Diagram sources**
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)

**Section sources**
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)

### LogQueueActivity Action
- Purpose: Creates a persistent audit trail for queue operations.
- Inputs: QueueTicket, action string, optional user and counter identifiers, optional metadata.
- Output: QueueActivity model instance.

```mermaid
flowchart TD
Start(["Call handle()"]) --> Validate["Validate inputs"]
Validate --> Persist["Create QueueActivity record"]
Persist --> Return["Return QueueActivity instance"]
```

**Diagram sources**
- [LogQueueActivity.php:8-28](file://app/Actions/Queue/LogQueueActivity.php#L8-L28)

**Section sources**
- [LogQueueActivity.php:13-27](file://app/Actions/Queue/LogQueueActivity.php#L13-L27)

### Laravel Reverb Configuration
- Driver selection: reverb configured as the default and primary broadcaster.
- Server options: host, port, path, hostname, TLS options, max request size.
- Scaling: Redis-backed clustering with URL/host/port/username/password/database/timeout.
- Application options: host, port, scheme/useTLS, allowed origins, ping interval, activity timeout, max connections, max message size, client event acceptance policy, rate limiting.
- Pulse and Telescope ingest intervals configurable.

```mermaid
graph LR
Cfg["broadcasting.php 'reverb'"] --> Srv["reverb.php 'servers.reverb'"]
Cfg --> Apps["reverb.php 'apps.apps'"]
Srv --> TLS["TLS options"]
Srv --> Scale["Redis scaling"]
Apps --> Limits["Rate limits & timeouts"]
```

**Diagram sources**
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:29-57](file://config/reverb.php#L29-L57)
- [reverb.php:70-99](file://config/reverb.php#L70-L99)

**Section sources**
- [broadcasting.php:18](file://config/broadcasting.php#L18)
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:31-57](file://config/reverb.php#L31-L57)
- [reverb.php:70-99](file://config/reverb.php#L70-L99)

### Route Channels
- Private user channel: App.Models.User.{id} validates that only the matching user may listen.
- Public channel: The 'public-queue' channel is used by the TicketCalled event.

```mermaid
graph TB
UserCh["Broadcast::channel('App.Models.User.{id}')"] --> Authz["Return user ID match"]
PubCh["TicketCalled broadcastOn()"] --> PubChan["Channel('public-queue')"]
```

**Diagram sources**
- [channels.php:5-7](file://routes/channels.php#L5-L7)
- [TicketCalled.php:27-32](file://app/Events/TicketCalled.php#L27-L32)

**Section sources**
- [channels.php:5-7](file://routes/channels.php#L5-L7)
- [TicketCalled.php:27-32](file://app/Events/TicketCalled.php#L27-L32)

### Client-side Integration with Echo.js
- Echo initialization sets broadcaster to reverb, key, host, ports, TLS mode, and transports.
- App bootstrap imports Echo to enable global subscription.
- Livewire component listens for echo:public-queue,TicketCalled and triggers a re-render.

```mermaid
sequenceDiagram
participant Boot as "app.js"
participant Echo as "Echo.js"
participant Comp as "Livewire TvDisplay"
Boot->>Echo : "import and initialize"
Echo-->>Comp : "subscribe to 'public-queue'"
Echo-->>Comp : "on 'TicketCalled' -> refreshQueue()"
Comp->>Comp : "re-render UI"
```

**Diagram sources**
- [app.js:9](file://resources/js/app.js#L9)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

**Section sources**
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

### End-to-End Workflows

#### Public Web Booking and Confirmation
- The public booking flow creates a ticket and redirects to a confirmation page.
- Activity is logged via LogQueueActivity.
- The TicketCalled event is dispatched when a ticket is called, broadcasting to public-queue.
- Livewire components on the TV display re-render to reflect the latest state.

```mermaid
sequenceDiagram
participant Pub as "PublicQueueController"
participant Act as "LogQueueActivity"
participant Ev as "TicketCalled"
participant Echo as "Echo.js"
participant TV as "Livewire TvDisplay"
Pub->>Act : "Record activity"
Pub->>Ev : "Dispatch when called"
Ev->>Echo : "Broadcast to 'public-queue'"
Echo-->>TV : "Trigger 'TicketCalled'"
TV->>TV : "refreshQueue() and re-render"
```

**Diagram sources**
- [PublicQueueController.php:39-56](file://app/Http/Controllers/PublicQueueController.php#L39-L56)
- [LogQueueActivity.php:13-27](file://app/Actions/Queue/LogQueueActivity.php#L13-L27)
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

#### Kiosk Walk-in Registration
- The kiosk prints a ticket for walk-in registrations.
- Activity is logged and the TicketCalled event is dispatched when the ticket is called.
- TV display updates accordingly.

```mermaid
sequenceDiagram
participant Kiosk as "KioskController"
participant Act as "LogQueueActivity"
participant Ev as "TicketCalled"
participant Echo as "Echo.js"
participant TV as "Livewire TvDisplay"
Kiosk->>Act : "Record activity"
Kiosk->>Ev : "Dispatch when called"
Ev->>Echo : "Broadcast to 'public-queue'"
Echo-->>TV : "Trigger 'TicketCalled'"
TV->>TV : "refreshQueue() and re-render"
```

**Diagram sources**
- [KioskController.php:114-142](file://app/Http/Controllers/KioskController.php#L114-L142)
- [LogQueueActivity.php:13-27](file://app/Actions/Queue/LogQueueActivity.php#L13-L27)
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

#### TV Display Interface
- The TV display controller serves an API endpoint returning current and recent calls.
- The Livewire component subscribes to the public channel and reacts to TicketCalled events.
- It also manages TTS announcements based on the latest call.

```mermaid
sequenceDiagram
participant TVCtrl as "TvDisplayController"
participant TVComp as "Livewire TvDisplay"
participant Echo as "Echo.js"
participant Ev as "TicketCalled"
TVCtrl->>TVCtrl : "apiState() returns current/recent calls"
Echo-->>TVComp : "on 'TicketCalled' -> refreshQueue()"
TVComp->>TVComp : "checkAndAnnounce() and dispatch play-tts"
```

**Diagram sources**
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)
- [TvDisplay.php:41-68](file://app/Livewire/TvDisplay.php#L41-L68)

## Dependency Analysis
- Controllers depend on LogQueueActivity to persist audit trails.
- LogQueueActivity depends on QueueActivity and QueueTicket models.
- TicketCalled depends on broadcasting configuration and Reverb server.
- Livewire TvDisplay depends on Echo.js and the public channel.
- Route channels secure private channels; public channel is used by TicketCalled.

```mermaid
graph LR
PubCtrl["PublicQueueController"] --> LogAct["LogQueueActivity"]
KioskCtrl["KioskController"] --> LogAct
LogAct --> QAct["QueueActivity Model"]
LogAct --> QT["QueueTicket Model"]
TicketEvt["TicketCalled"] --> BroadcastCfg["Broadcasting Config"]
BroadcastCfg --> ReverbCfg["Reverb Config"]
Echo["Echo.js"] --> TvComp["Livewire TvDisplay"]
TicketEvt --> Echo
```

**Diagram sources**
- [PublicQueueController.php:39-56](file://app/Http/Controllers/PublicQueueController.php#L39-L56)
- [KioskController.php:114-142](file://app/Http/Controllers/KioskController.php#L114-L142)
- [LogQueueActivity.php:8-28](file://app/Actions/Queue/LogQueueActivity.php#L8-L28)
- [TicketCalled.php:18-32](file://app/Events/TicketCalled.php#L18-L32)
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:29-57](file://config/reverb.php#L29-L57)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

**Section sources**
- [broadcasting.php:33-47](file://config/broadcasting.php#L33-L47)
- [reverb.php:31-57](file://config/reverb.php#L31-L57)
- [TicketCalled.php:27-32](file://app/Events/TicketCalled.php#L27-L32)

## Performance Considerations
- Use ShouldBroadcastNow for immediate delivery of TicketCalled to avoid queueing delays.
- Tune Reverb server options: adjust max_request_size, scaling Redis settings, and pulse/telescope ingest intervals.
- Apply rate limiting at the application level to prevent flooding.
- Cache heavy TV display data (e.g., video assets) to reduce repeated disk reads.
- Minimize payload size by sending only essential fields in event payloads.
- Use Livewire’s reactive rendering to avoid full-page reloads.

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
- Verify broadcasting driver and Reverb server configuration.
- Confirm Echo client options match server host, port, scheme, and TLS settings.
- Ensure the public-queue channel is reachable and not blocked by firewall or proxy.
- Check that controllers call LogQueueActivity prior to dispatching TicketCalled.
- Validate Livewire component subscription to echo:public-queue,TicketCalled.
- Inspect Reverb logs and Pulse/Telescope metrics for connection health and throughput.

**Section sources**
- [broadcasting.php:18](file://config/broadcasting.php#L18)
- [reverb.php:31-57](file://config/reverb.php#L31-L57)
- [echo.js:6-14](file://resources/js/echo.js#L6-L14)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)

## Conclusion
The real-time broadcasting system leverages Laravel Reverb and Echo.js to synchronize queue interfaces. The TicketCalled event and LogQueueActivity action form the backbone of live updates and auditability. Proper configuration of broadcasting and Reverb, combined with efficient client-side handling in Livewire, delivers responsive, scalable real-time experiences across public, kiosk, and TV display interfaces.