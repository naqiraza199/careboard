@php

use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\PublicHoliday;

$user = Auth::user();
$company = Company::where('user_id', $user->id)->first();

$publicHolidays = PublicHoliday::where('company_id',$company->id)->get();
@endphp


<style>
    .holiday-tag-wrapper {
        transition: all 0.3s ease !important;
        border: 1px solid transparent;
    }
    .holiday-tag-wrapper:hover {
        color: #000000 !important;
        background-color: #c0f2a6 !important;
        border-color: #9cd37b !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05) !important;
        transform: translateY(-2px);
    }
    .holiday-tag-wrapper .delete-holiday-btn {
        display: none !important;
    }
    .holiday-tag-wrapper:hover .delete-holiday-btn {
        display: flex !important;
        animation: popIn 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    @keyframes popIn {
        0% { opacity: 0; transform: scale(0.8); }
        100% { opacity: 1; transform: scale(1); }
    }
</style>

<div class="tags-container" style="display: flex; flex-wrap: wrap; gap: 8px;">
    @foreach($publicHolidays as $holiday)
        <div class="category-tag holiday-tag-wrapper relative flex items-center" style="background-color:#daf7c9; padding-right: 30px;">
            {{ \Carbon\Carbon::parse($holiday->date)->format('D - j M Y') }}
            <button 
                type="button" 
                wire:click="deleteHoliday({{ $holiday->id }})"
                wire:confirm="Are you sure you want to delete this holiday?"
                class="delete-holiday-btn items-center justify-center absolute text-white rounded-full cursor-pointer transition-colors"
                style="background-color: #374151; right: 6px; width: 16px; height: 16px; border: none; padding: 0;"
                onmouseover="this.style.backgroundColor='#000000'"
                onmouseout="this.style.backgroundColor='#374151'"
                title="Delete"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" style="width: 10px; height: 10px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endforeach
</div>
