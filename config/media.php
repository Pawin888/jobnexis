<?php

return [
    // Default max upload size per file for teaching media (in MB)
    // 3072 MB ≈ 3 GB, suitable for ~3 hours of 720p-1080p compressed video
    'max_upload_mb' => (int) env('MEDIA_MAX_UPLOAD_MB', 3072),

    // Disk used for storing teaching media files
    // Use 'public' for local storage, or 'b2' for Backblaze B2
    'disk' => env('MEDIA_DISK', 'public'),
];
