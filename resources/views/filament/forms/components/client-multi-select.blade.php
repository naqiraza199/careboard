@php
    $wirePath        = $wirePath ?? 'data.client_section.client_id';
    $selectedDetails = collect($selectedDetails ?? [])
        ->filter(fn($d) => !empty($d['client_id']))
        ->values()
        ->toArray();
    $availableClients = $availableClients ?? [];
    $selectedIds     = collect($selectedIds ?? [])->filter()->values()->toArray();
    $clientsForJs    = collect($availableClients)
        ->map(fn($name, $id) => ['id' => (int)$id, 'name' => $name])
        ->values()
        ->toArray();
@endphp

<div
    x-data="{
        open: false,
        search: '',
        clients: @js($clientsForJs),
        get filtered() {
            if (!this.search) return this.clients;
            const q = this.search.toLowerCase();
            return this.clients.filter(c => c.name.toLowerCase().includes(q));
        },
        add(id) {
            id = parseInt(id);
            const raw = $wire.get('{{ $wirePath }}') || [];
            const current = raw.map(i => parseInt(i));
            if (!current.includes(id)) {
                $wire.set('{{ $wirePath }}', [...current, id]);
            }
            this.open = false;
            this.search = '';
        },
        remove(id) {
            $wire.call('removeClientBadge', parseInt(id));
        }
    }"
    class="relative w-full"
    style="margin-bottom: 1rem;"
>
    <div class="text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1">Select Clients</div>

    <div
        class="flex flex-wrap items-center gap-1 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-2 py-1.5 shadow-sm cursor-text transition focus-within:ring-2 focus-within:ring-primary-500 focus-within:border-primary-500"
        style="min-height: 40px;"
        @click="$refs.clientInput.focus(); open = true"
    >
        @foreach($selectedDetails as $detail)
            <span style="display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:999px;padding:2px 10px;font-size:13px;font-weight:500;white-space:nowrap;">
                {{ $detail['client_name'] ?? 'Client' }}
                <button
                    type="button"
                    @click.stop="remove({{ $detail['client_id'] }})"
                    style="font-size:16px;line-height:1;color:#93c5fd;cursor:pointer;background:none;border:none;padding:0 0 0 2px;"
                >×</button>
            </span>
        @endforeach

        <input
            x-ref="clientInput"
            x-model="search"
            @focus="open = true"
            @keydown.escape="open = false; search = ''"
            @input="open = true"
            @click.stop
            placeholder="{{ empty($selectedDetails) ? 'Type to search clients by name.' : '' }}"
            class="flex-1 bg-transparent text-sm text-gray-900 dark:text-white placeholder:text-gray-400 outline-none border-none shadow-none"
            style="min-width: 120px; min-height: 26px;"
        >
    </div>

    <div
        x-show="open"
        @click.away="open = false"
        class="absolute left-0 right-0 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg overflow-y-auto"
        style="z-index: 9999; max-height: 200px; top: 100%; margin-top: 4px;"
        x-cloak
    >
        <template x-for="c in filtered" :key="c.id">
            <div
                @click.stop="add(c.id)"
                x-text="c.name"
                class="px-3 py-2 text-sm text-gray-900 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-800 cursor-pointer select-none"
            ></div>
        </template>
        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">No clients found.</div>
    </div>
</div>
