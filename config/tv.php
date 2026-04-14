<?php

return [
    'video_volume' => (float) env('TV_VIDEO_VOLUME', 0.08),
    'video_volume_during_tts' => (float) env('TV_VIDEO_VOLUME_DURING_TTS', 0.01),
    'tts_volume' => (float) env('TV_TTS_VOLUME', 1.0),
];
