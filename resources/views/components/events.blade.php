<style>
    .timeline {
        position: relative;
        margin: 0 auto;
        padding: 20px 0;
        width: 100%;
        max-width: 600px;
    }

    .timeline::before {
        content: "";
        position: absolute;
        top: 0;
        left: 40px;
        width: 2px;
        height: 100%;
        background: #858585;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 40px;
        padding-left: 80px;
    }

    .timeline-item-slider{
        position: relative;
        padding-left: 80px;
    }

    .timeline-icon {
        position: absolute;
        left: 25px;
        top: 0;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-weight: bold;
        font-size: 16px;
    }

    .timeline-icon.green { background: #22c55e; }
    .timeline-icon.blue { background: #3b82f6; }
    .timeline-icon.orange { background: #f59e0b; }

    .timeline-date {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 6px;
    }

    .timeline-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        position: relative;
    }

    .timeline-card b {
        color: #1d4ed8;
        display: block;
        margin-bottom: 5px;
    }

    .timeline-text {
        font-size: 14px;
        color: #374151;
        line-height: 1.4;
    }

    .timeline-text .more {
        display: none;
    }

    .timeline-text .attachment {
        display: inline-block;
        margin-top: 5px;
    }

    .timeline-text .attachment img {
        max-width: 100px;
        max-height: 100px;
        border-radius: 4px;
        margin-right: 10px;
    }

    .view-more, .view-less {
        display: inline-block;
        margin-top: 5px;
        font-size: 13px;
        color: #2563eb;
        cursor: pointer;
        text-decoration: underline;
    }

    .attachment-icon {
        position: absolute;
        top: 5px;
        right: 10px;
        cursor: pointer;
        font-size: 16px;
        background: none;
        border: none;
        padding: 2px;
    }

    .attachment-icon::after {
        content: "📎";
        color: #6b7280;
    }

    .attachment-icon:hover::after {
        color: #3b82f6;
    }

    .dots {
        display: inline;
    }
</style>
  
<div x-data="{ open: false }" class="timeline">
    <div style="width:100%;text-align: end;">
        <x-filament::button 
            color="primary" 
            @click="open = true" 
            style="padding: 10px 15px;">
            View more
        </x-filament::button>
    </div>
    @foreach($events as $event)
    <div class="timeline-item">
        <div>
           {{ $event->created_at->format('D, d M Y') }}
        </div>
        <div class="timeline-icon green">✓</div>
        <div class="timeline-date">{{ $event->created_at->diffForHumans() }} | {{ $event->created_at->format('h:i a') }}</div>
        <div class="timeline-card">
            <b>{{ $event->title }}</b>
            <div class="timeline-text" x-data="{ expanded: false }">
                <span x-show="!expanded">
                    {{ \Illuminate\Support\Str::limit($event->body, 50) }}
                    @if(strlen($event->body) > 50)
                        <span class="view-more" @click="expanded = true">View more</span>
                    @endif
                </span>

                <span x-show="expanded">
                    {{ $event->body }}
                    <span class="view-less" @click="expanded = false">View less</span>
                </span>
            </div>
            @if(!empty($event->note_attachments))
                @php
                    $attachmentJson = collect($event->note_attachments)->map(fn($a) => [
                        'url'  => Storage::url($a['file_path']),
                        'name' => basename($a['file_path']),
                    ])->toJson();
                @endphp
                <span class="attachment-icon" title="View attachments"
                      onclick="openAttachmentPopup(this)"
                      data-attachments="{{ $attachmentJson }}">
                </span>
            @endif
        </div>
    </div>
    @endforeach
   <div
        x-show="open"
        class="fixed inset-0 z-50 flex"
        x-transition
    >
        <!-- Background overlay -->
        <div 
            class="flex-1 bg-black bg-opacity-50"
            @click="open = false"
        ></div>

        <!-- Drawer -->
        <div 
            class="w-full max-w-md bg-white shadow-xl h-full transform transition-transform duration-300 ease-in-out overflow-y-auto"
            x-show="open"
            x-transition:enter="translate-x-full"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="translate-x-0"
            x-transition:leave-end="translate-x-full"
            style="background-color:#EFEFEF;"
        >
            <div style="background-color:white;" class="p-6 border-b flex justify-between items-center">
                <h2 class="text-xl font-bold">All Events</h2>
                <x-filament::button color="danger" size="md" @click="open = false">
                    Cancel
                </x-filament::button>
            </div>

            <div class="p-6 space-y-4">
                @foreach(\App\Models\Event::where('shift_id', $this->shiftId)->orderByDesc('created_at')->get() as $event)
    <div class="timeline-item-slider" style="
        margin-bottom: 40px;
    ">
        <div>
           {{ $event->created_at->format('D, d M Y') }}
        </div>
        <div class="timeline-icon green">✓</div>
        <div class="timeline-date">{{ $event->created_at->diffForHumans() }} | {{ $event->created_at->format('h:i a') }}</div>
        <div class="timeline-card">
            <b>{{ $event->title }}</b>
            <div class="timeline-text" x-data="{ expanded: false }">
                <span x-show="!expanded">
                    {{ \Illuminate\Support\Str::limit($event->body, 50) }}
                    @if(strlen($event->body) > 50)
                        <span class="view-more" @click="expanded = true">View more</span>
                    @endif
                </span>

                <span x-show="expanded">
                    {{ $event->body }}
                    <span class="view-less" @click="expanded = false">View less</span>
                </span>
            </div>
            @if(!empty($event->note_attachments))
                @php
                    $attachmentJson = collect($event->note_attachments)->map(fn($a) => [
                        'url'  => Storage::url($a['file_path']),
                        'name' => basename($a['file_path']),
                    ])->toJson();
                @endphp
                <span class="attachment-icon" title="View attachments"
                      onclick="openAttachmentPopup(this)"
                      data-attachments="{{ $attachmentJson }}">
                </span>
            @endif
        </div>
    </div>
    @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function openAttachmentPopup(el) {
    const attachments = JSON.parse(el.dataset.attachments || '[]');
    if (!attachments.length) return;

    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp', 'webp'];
    const fileIcons = { pdf: '📄', doc: '📝', docx: '📝', xls: '📊', xlsx: '📊', csv: '📊', txt: '📋', zip: '🗜️', eml: '📧' };

    let html = '<div style="display:flex;flex-direction:column;gap:12px;max-height:70vh;overflow-y:auto;padding:4px;">';
    attachments.forEach(att => {
        const ext = att.name.split('.').pop().toLowerCase();
        const isImage = imageExts.includes(ext);
        const icon = fileIcons[ext] || '📎';

        if (isImage) {
            html += `
                <div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                    <img src="${att.url}" style="max-width:100%;border-radius:6px;display:block;margin-bottom:8px;" alt="${att.name}">
                    <a href="${att.url}" download="${att.name}"
                       style="display:inline-block;background:#3b82f6;color:#fff;padding:6px 14px;border-radius:4px;font-size:13px;text-decoration:none;">
                       ⬇ Download
                    </a>
                </div>`;
        } else {
            html += `
                <div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;display:flex;align-items:center;gap:12px;">
                    <span style="font-size:28px;">${icon}</span>
                    <div style="flex:1;font-size:14px;font-weight:500;word-break:break-all;">${att.name}</div>
                    <a href="${att.url}" download="${att.name}"
                       style="flex-shrink:0;background:#3b82f6;color:#fff;padding:6px 14px;border-radius:4px;font-size:13px;text-decoration:none;">
                       ⬇ Download
                    </a>
                </div>`;
        }
    });
    html += '</div>';

    Swal.fire({
        title: 'Attachments',
        html: html,
        width: '600px',
        showConfirmButton: false,
        showCloseButton: true,
    });
}

document.addEventListener("livewire:load", () => {
    attachViewMoreListeners();

    Livewire.hook('morph.updated', () => {
        attachViewMoreListeners();
    });

    function attachViewMoreListeners() {
        document.querySelectorAll(".view-more").forEach(btn => {
            if (btn.dataset.bound) return; // prevent double-binding
            btn.dataset.bound = true;

            btn.addEventListener("click", () => {
                const text = btn.previousElementSibling;
                const dots = text.querySelector(".dots");
                const more = text.querySelector(".more");
                const viewLess = document.createElement("span");

                viewLess.className = "view-less";
                viewLess.textContent = "View less";
                viewLess.style.marginLeft = "10px";
                viewLess.style.color = "#2563eb";
                viewLess.style.cursor = "pointer";
                viewLess.style.textDecoration = "underline";

                more.style.display = "inline";
                dots.style.display = "none";
                btn.style.display = "none";
                btn.parentNode.appendChild(viewLess);

                viewLess.addEventListener("click", () => {
                    more.style.display = "none";
                    dots.style.display = "inline";
                    btn.style.display = "inline";
                    viewLess.remove();
                });
            });
        });
    }
});
</script>
