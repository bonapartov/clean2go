@use('App\Helpers\Helpers')
@php
    $settings = Helpers::getSettings();
    $frontendAnnouncement = $settings['frontend_announcement_settings'] ?? [];
@endphp
@if (($frontendAnnouncement['status'] ?? 0) == 1 && !empty($frontendAnnouncement['title']))
<div class="announcement-bar">
    <h6 class="offer-text">{{ $frontendAnnouncement['title'] ?? '' }}</h6>
    <a href="{{ $frontendAnnouncement['link'] ?? '#' }}" class="btn">{{ $frontendAnnouncement['link_text'] ?? '' }}</a>
</div>
@endif
