<x-filament::page>
    <style>
    /* Expired status row styling */
    .expired-row {
        background-color: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
    }

    .expired-row td {
        background-color: transparent !important;
    }

    /* More specific targeting for Filament table rows */
    .fi-ta-row.expired-row {
        background-color: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
    }

    .fi-ta-row.expired-row td {
        background-color: transparent !important;
    }

    /* Even more specific targeting */
    .fi-ta tbody .fi-ta-row.expired-row {
        background-color: #fef2f2 !important;
        border-left: 4px solid #ef4444 !important;
    }

    .fi-ta tbody .fi-ta-row.expired-row td {
        background-color: transparent !important;
    }
    </style>

    {{ $this->table }}
</x-filament::page>
