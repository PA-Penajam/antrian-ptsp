# Real-time Queue Monitoring

<cite>
**Referenced Files in This Document**
- [TvDisplay.php](file://app/Livewire/TvDisplay.php)
- [tv-display.blade.php](file://resources/views/livewire/tv-display.blade.php)
- [TvDisplayController.php](file://app/Http/Controllers/TvDisplayController.php)
- [web.php](file://routes/web.php)
- [api.php](file://routes/api.php)
- [broadcasting.php](file://config/broadcasting.php)
- [reverb.php](file://config/reverb.php)
- [TicketCalled.php](file://app/Events/TicketCalled.php)
- [TvDisplayTtsController.php](file://app/Http/Controllers/TvDisplayTtsController.php)
- [CheckModulePassword.php](file://app/Http/Middleware/CheckModulePassword.php)
- [QueueStatus.php](file://app/Enums/QueueStatus.php)
- [QueueTicket.php](file://app/Models/QueueTicket.php)
- [index.blade.php](file://resources/views/pages/tv-display/index.blade.php)
- [login.blade.php](file://resources/views/pages/tv-display/login.blade.php)
- [tv-display.js](file://resources/js/tv-display.js)
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
This document explains the Real-time Queue Monitoring system that powers TV displays for queue management. It covers Livewire-based dynamic updates, the API endpoints that supply current and recent queue calls, the broadcast system synchronizing queue state across multiple displays, queue status filtering, pagination and caching, and integration with Laravel Reverb for real-time communication. It also provides performance optimization techniques for smooth display updates under frequent data changes.

## Project Structure
The TV monitoring system is composed of:
- Livewire component that renders the TV display and reacts to real-time events
- Blade templates for the TV display layout and login
- Controller that serves both the Livewire page and a legacy API endpoint
- Routes that expose login, index, and legacy API endpoints
- Broadcasting configuration for Reverb/Pusher
- Event that broadcasts queue changes to subscribed clients
- TTS controller for audio announcements
- Middleware for module password protection
- Eloquent model and enum for queue data and statuses

```mermaid
graph TB
subgraph "TV Display UI"
LWT["Livewire: TvDisplay<br/>renders tv-display.blade.php"]
BLD["Blade: tv-display.blade.php"]
end
subgraph "HTTP Layer"
RWEB["Routes: web.php"]
CTRL["Controller: TvDisplayController"]
TTSC["Controller: TvDisplayTtsController"]
end
subgraph "Real-time"
EVT["Event: TicketCalled"]
BRDC["Config: broadcasting.php"]
RVCONF["Config: reverb.php"]
end
subgraph "Data"
QMODEL["Model: QueueTicket"]
QENUM["Enum: QueueStatus"]
end
RWEB --> LWT
LWT --> BLD
RWEB --> CTRL
RWEB --> TTSC
EVT --> BRDC
BRDC --> RVCONF
LWT --> QMODEL
CTRL --> QMODEL
QMODEL --> QENUM
```

**Diagram sources**
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [tv-display.blade.php:1-213](file://resources/views/livewire/tv-display.blade.php#L1-L213)
- [web.php:108-124](file://routes/web.php#L108-L124)
- [TvDisplayController.php:52-142](file://app/Http/Controllers/TvDisplayController.php#L52-L142)
- [TvDisplayTtsController.php:12-61](file://app/Http/Controllers/TvDisplayTtsController.php#L12-L61)
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)
- [QueueStatus.php:5-37](file://app/Enums/QueueStatus.php#L5-L37)

**Section sources**
- [web.php:108-124](file://routes/web.php#L108-L124)
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [tv-display.blade.php:1-213](file://resources/views/livewire/tv-display.blade.php#L1-L213)
- [TvDisplayController.php:52-142](file://app/Http/Controllers/TvDisplayController.php#L52-L142)
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)
- [QueueStatus.php:5-37](file://app/Enums/QueueStatus.php#L5-L37)

## Core Components
- Livewire component for TV display rendering and real-time updates
- Blade template for the TV display layout, including video playback and TTS integration
- Controller serving the TV display page and legacy API endpoint
- Broadcasting configuration and event for real-time synchronization
- TTS controller for audio announcements via external provider
- Middleware enforcing module password protection
- Data model and enum for queue tickets and statuses

**Section sources**
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [tv-display.blade.php:1-213](file://resources/views/livewire/tv-display.blade.php#L1-L213)
- [TvDisplayController.php:52-142](file://app/Http/Controllers/TvDisplayController.php#L52-L142)
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [TvDisplayTtsController.php:12-61](file://app/Http/Controllers/TvDisplayTtsController.php#L12-L61)
- [CheckModulePassword.php:10-33](file://app/Http/Middleware/CheckModulePassword.php#L10-L33)
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)
- [QueueStatus.php:5-37](file://app/Enums/QueueStatus.php#L5-L37)

## Architecture Overview
The system combines a Livewire-driven UI with Laravel Reverb for real-time updates. When a ticket is called, an event is dispatched to a public channel. Livewire components listening on that channel trigger a re-render, updating the display instantly. A legacy API endpoint provides current and recent queue data for non-Livewire displays. TTS announcements are generated via a dedicated controller and stored for efficient playback.

```mermaid
sequenceDiagram
participant Operator as "Operator"
participant Controller as "TvDisplayController"
participant Livewire as "Livewire : TvDisplay"
participant Blade as "Blade : tv-display.blade.php"
participant Reverb as "Reverb/Broadcaster"
participant Clients as "TV Displays"
Operator->>Controller : "Call Next Ticket"
Controller-->>Reverb : "Broadcast TicketCalled(public-queue)"
Reverb-->>Clients : "Push event to subscribers"
Clients->>Livewire : "Receive echo : public-queue,TicketCalled"
Livewire->>Livewire : "refreshQueue() triggers re-render"
Livewire->>Blade : "render() returns updated view"
Blade-->>Clients : "Updated queue info, TTS, videos"
```

**Diagram sources**
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)

## Detailed Component Analysis

### Livewire TV Display Component
The Livewire component orchestrates:
- Real-time refresh via an event listener
- Rendering current and recent calls
- TTS announcement logic with phonetic formatting
- Video playlist caching and playback

```mermaid
classDiagram
class TvDisplay {
+string lastAnnouncedCall
+refreshQueue() void
+render() View
-checkAndAnnounce(Collection) void
-formatForTts(string) string
-currentCalls() Collection
-recentCalls() Collection
-videos() array
}
class QueueTicket {
+int id
+string ticket_number
+datetime called_at
+Counter counter
+Service service
}
TvDisplay --> QueueTicket : "queries"
```

**Diagram sources**
- [TvDisplay.php:18-142](file://app/Livewire/TvDisplay.php#L18-L142)
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)

**Section sources**
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [TvDisplay.php:41-68](file://app/Livewire/TvDisplay.php#L41-L68)
- [TvDisplay.php:85-113](file://app/Livewire/TvDisplay.php#L85-L113)
- [TvDisplay.php:118-140](file://app/Livewire/TvDisplay.php#L118-L140)

### TV Display Blade Template
The Blade template:
- Initializes Alpine.js state for connectivity, audio unlocking, and video playback
- Listens for TTS announcements and fetches audio URLs
- Renders current calls, recent history, branding, and optional video playlist
- Provides a fallback YouTube playlist when no local videos are available

```mermaid
flowchart TD
Start(["Template Load"]) --> Init["Initialize Alpine state<br/>connected, audioUnlocked, videos"]
Init --> Online["Listen to online/offline events"]
Online --> Click["Listen to click/keydown to unlock audio"]
Click --> TTS["Listen to play-tts window event"]
TTS --> Fetch["Fetch /tv-display/tts/announcement?text=..."]
Fetch --> AudioURL{"Has audio_url?"}
AudioURL --> |Yes| Play["Play audio"]
AudioURL --> |No| Fallback["Use browser audio provider"]
Play --> End(["Rendered"])
Fallback --> End
```

**Diagram sources**
- [tv-display.blade.php:2-40](file://resources/views/livewire/tv-display.blade.php#L2-L40)
- [tv-display.blade.php:96-174](file://resources/views/livewire/tv-display.blade.php#L96-L174)

**Section sources**
- [tv-display.blade.php:1-213](file://resources/views/livewire/tv-display.blade.php#L1-L213)

### TV Display Controller and Legacy API
The controller exposes:
- Index page for the TV display
- Login/logout endpoints with module password middleware
- Legacy API endpoint returning current and recent calls plus videos

```mermaid
sequenceDiagram
participant Client as "Client"
participant Route as "Route : /tv-legacy/api/state"
participant Ctrl as "TvDisplayController@apiState"
participant DB as "QueueTicket"
participant Cache as "Cache"
Client->>Route : GET /tv-legacy/api/state
Route->>Ctrl : apiState()
Ctrl->>DB : Query currentCalls (limit 6)
DB-->>Ctrl : Collection
Ctrl->>DB : Query recentCalls (limit 4)
DB-->>Ctrl : Collection
Ctrl->>Cache : remember('tv-display : videos', 60)
Cache-->>Ctrl : Array
Ctrl-->>Client : JSON {currentCalls, recentCalls, videos}
```

**Diagram sources**
- [web.php:121-122](file://routes/web.php#L121-L122)
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)

**Section sources**
- [TvDisplayController.php:52-55](file://app/Http/Controllers/TvDisplayController.php#L52-L55)
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)

### Broadcasting and Real-time Updates
The system uses a broadcast event to synchronize queue state across displays:
- Event is dispatched when a ticket is called
- Broadcast channel is public-queue
- Livewire listens for echo events and triggers re-render

```mermaid
sequenceDiagram
participant Backend as "Backend"
participant Event as "TicketCalled"
participant Broadcaster as "Broadcast Driver"
participant Display as "Livewire : TvDisplay"
Backend->>Event : "new TicketCalled(queueTicketId)"
Event->>Broadcaster : "broadcastOn(public-queue)"
Broadcaster-->>Display : "echo : public-queue,TicketCalled"
Display->>Display : "refreshQueue() -> render()"
```

**Diagram sources**
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)

**Section sources**
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [TvDisplay.php:22-27](file://app/Livewire/TvDisplay.php#L22-L27)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)

### TTS Announcement Controller
The TTS controller:
- Validates text input and generates audio via external provider
- Returns either a cache key and audio URL or falls back to browser audio
- Serves audio files from storage with appropriate headers

```mermaid
sequenceDiagram
participant Client as "Client"
participant Route as "Route : /tv-display/tts/announcement"
participant Ctrl as "TvDisplayTtsController@announcement"
participant TTS as "MiniMaxTtsService"
participant Storage as "Storage Disk"
Client->>Route : GET /tv-display/tts/announcement?text=...
Route->>Ctrl : announcement()
Ctrl->>TTS : "getOrCreateAnnouncement(text)"
TTS-->>Ctrl : "{cache_key, ...}"
Ctrl->>Storage : "cachePathFromKey(cache_key)"
Storage-->>Ctrl : "exists?"
Ctrl-->>Client : JSON {provider, cache_key, audio_url}
Client->>Route : GET /tv-display/tts/audio/{cacheKey}
Route->>Ctrl : audio(cacheKey)
Ctrl->>Storage : "get(cachePath)"
Storage-->>Ctrl : "content"
Ctrl-->>Client : 200 audio/mpeg
```

**Diagram sources**
- [TvDisplayTtsController.php:14-60](file://app/Http/Controllers/TvDisplayTtsController.php#L14-L60)
- [web.php:123-124](file://routes/web.php#L123-L124)

**Section sources**
- [TvDisplayTtsController.php:14-60](file://app/Http/Controllers/TvDisplayTtsController.php#L14-L60)

### Authentication and Access Control
Module password middleware enforces secure access to TV display pages:
- Validates session keys and timestamps
- Redirects unauthenticated users to login
- Supports multiple modules with consistent session handling

```mermaid
flowchart TD
A["Incoming Request"] --> B["CheckModulePassword middleware"]
B --> C{"Authenticated & Not Expired?"}
C --> |Yes| D["Allow Request"]
C --> |No| E["Redirect to module login"]
```

**Diagram sources**
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)

**Section sources**
- [CheckModulePassword.php:10-33](file://app/Http/Middleware/CheckModulePassword.php#L10-L33)

### Data Model and Status Filtering
Queue data is filtered by status and date, with eager loading for counters and services:
- Current calls: tickets with status "called" and today's date
- Recent calls: tickets with today's date and non-null called_at
- Videos: cached file listing from storage with allowed extensions

```mermaid
erDiagram
QUEUE_TICKET {
int id PK
int service_id
int queue_pool_id
int counter_id
string ticket_number
date service_date
datetime called_at
enum status
}
SERVICE {
int id PK
string name
}
COUNTER {
int id PK
string name
}
QUEUE_TICKET }o--|| SERVICE : "belongs to"
QUEUE_TICKET }o--|| COUNTER : "belongs to"
```

**Diagram sources**
- [QueueTicket.php:17-52](file://app/Models/QueueTicket.php#L17-L52)
- [QueueStatus.php:5-13](file://app/Enums/QueueStatus.php#L5-L13)

**Section sources**
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)
- [QueueStatus.php:5-37](file://app/Enums/QueueStatus.php#L5-L37)
- [TvDisplay.php:85-113](file://app/Livewire/TvDisplay.php#L85-L113)
- [TvDisplayController.php:89-142](file://app/Http/Controllers/TvDisplayController.php#L89-L142)

## Dependency Analysis
The TV monitoring system exhibits clear separation of concerns:
- Routes delegate to controllers and Livewire components
- Livewire depends on models and enums for data retrieval and status representation
- Broadcasting configuration integrates with Reverb for real-time updates
- TTS controller depends on external service and storage for audio delivery
- Middleware ensures secure access to module-specific pages

```mermaid
graph LR
WEB["routes/web.php"] --> CTRL["TvDisplayController"]
WEB --> LWT["Livewire: TvDisplay"]
LWT --> QMODEL["QueueTicket"]
QMODEL --> QENUM["QueueStatus"]
EVT["TicketCalled"] --> BRDC["broadcasting.php"]
BRDC --> RVCONF["reverb.php"]
LWT --> BLADE["tv-display.blade.php"]
WEB --> TTSC["TvDisplayTtsController"]
```

**Diagram sources**
- [web.php:108-124](file://routes/web.php#L108-L124)
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [QueueTicket.php:12-77](file://app/Models/QueueTicket.php#L12-L77)
- [QueueStatus.php:5-37](file://app/Enums/QueueStatus.php#L5-L37)
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)
- [TvDisplayTtsController.php:12-61](file://app/Http/Controllers/TvDisplayTtsController.php#L12-L61)

**Section sources**
- [web.php:108-124](file://routes/web.php#L108-L124)
- [TvDisplay.php:18-39](file://app/Livewire/TvDisplay.php#L18-L39)
- [TicketCalled.php:11-33](file://app/Events/TicketCalled.php#L11-L33)
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)
- [TvDisplayTtsController.php:12-61](file://app/Http/Controllers/TvDisplayTtsController.php#L12-L61)

## Performance Considerations
- Minimize database queries by eager-loading related models (counter, service) and limiting result sets
- Cache video file listings to avoid repeated filesystem scans
- Use real-time broadcasting to reduce polling overhead
- Optimize TTS generation and caching to avoid repeated synthesis
- Employ Alpine.js animations judiciously to prevent layout thrashing
- Ensure proper caching headers for audio assets to improve playback reliability

[No sources needed since this section provides general guidance]

## Troubleshooting Guide
Common issues and resolutions:
- No real-time updates on displays: verify broadcasting driver configuration and that clients are subscribed to the public-queue channel
- TTS audio not playing: confirm the TTS controller returns a valid audio URL or falls back to browser audio; check storage disk permissions and cache key validity
- Videos not playing: ensure allowed file extensions and that the video cache is populated; verify autoplay policies and audio unlocking
- Authentication failures: confirm module password middleware is applied and session keys are set with valid timestamps

**Section sources**
- [broadcasting.php:31-47](file://config/broadcasting.php#L31-L47)
- [reverb.php:29-55](file://config/reverb.php#L29-L55)
- [TvDisplayTtsController.php:41-60](file://app/Http/Controllers/TvDisplayTtsController.php#L41-L60)
- [CheckModulePassword.php:17-33](file://app/Http/Middleware/CheckModulePassword.php#L17-L33)

## Conclusion
The Real-time Queue Monitoring system leverages Livewire and Laravel Reverb to deliver seamless, synchronized updates across multiple TV displays. The combination of event-driven updates, efficient data retrieval, caching strategies, and TTS integration ensures a responsive and accessible queue display experience. Proper configuration of broadcasting and middleware guarantees secure and reliable operation.