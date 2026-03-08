<style>
    .shipmozo-timeline {
        list-style-type: none;
        padding: 0;
        margin: 0;
        position: relative;
    }

    .shipmozo-timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 15px;
        width: 2px;
        background: #e9ecef;
    }

    .shipmozo-timeline-item {
        position: relative;
        padding-left: 45px;
        margin-bottom: 20px;
    }

    .shipmozo-timeline-item::before {
        content: '';
        position: absolute;
        left: 11px;
        top: 5px;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #206bc4;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px #206bc4;
    }

    .shipmozo-timeline-item:last-child {
        margin-bottom: 0;
    }

    .shipmozo-timeline-date {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .shipmozo-timeline-title {
        font-weight: bold;
        color: #333;
        margin-bottom: 3px;
    }

    .shipmozo-timeline-desc {
        color: #555;
        font-size: 0.9rem;
    }
</style>

<ul class="shipmozo-timeline">
    @forelse($trackingData as $track)
    <li class="shipmozo-timeline-item">
        <div class="shipmozo-timeline-date">{{ \Illuminate\Support\Arr::get($track, 'date') }}</div>
        <div class="shipmozo-timeline-title">{{ \Illuminate\Support\Arr::get($track, 'status') }}</div>
        <div class="shipmozo-timeline-desc">
            {{ \Illuminate\Support\Arr::get($track, 'activity') }}
            @if(\Illuminate\Support\Arr::get($track, 'location'))
            - {{ \Illuminate\Support\Arr::get($track, 'location') }}
            @endif
        </div>
    </li>
    @empty
    <li class="shipmozo-timeline-item">
        <div class="shipmozo-timeline-title">No tracking updates available from the carrier yet.</div>
    </li>
    @endforelse
</ul>
