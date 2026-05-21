<div
    x-data="{ show: false, info: '' }"
    x-on:shift-next-day-toggled.document="
        show = $event.detail.checked;
        if (!show) { info = ''; return; }
        const rawDate = document.getElementById('start-date-input')?.value;
        const startTime = document.getElementById('start-time-input')?.value;
        const endTime   = document.getElementById('end-time-input')?.value;
        if (!rawDate || !startTime || !endTime) { info = ''; return; }
        const [sH, sM] = startTime.split(':').map(Number);
        const [eH, eM] = endTime.split(':').map(Number);
        let sMins = sH * 60 + sM;
        let eMins = eH * 60 + eM;
        if (eMins <= sMins) eMins += 1440;
        const hours = ((eMins - sMins) / 60).toFixed(1);
        // Datepicker stores DD-MM-YYYY; convert to YYYY-MM-DD for JS Date
        const dp = rawDate.split('-');
        const isoDate = dp.length === 3 ? `${dp[2]}-${dp[1]}-${dp[0]}` : rawDate;
        const d = new Date(isoDate + 'T00:00:00');
        d.setDate(d.getDate() + 1);
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        info = 'This shift is ' + hours + ' hours, finishing next day, ' + dd + '/' + mm + '/' + yyyy + '.';
    "
    x-show="show"
    x-cloak
    style="text-align: right; color: black; display: flex; align-items: center; justify-content: flex-end;"
>
    <span x-text="info"></span>
</div>
