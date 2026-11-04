<div {{ $attributes->class('overflow-x-auto') }}>
    <table
        class="w-full min-w-[40rem] text-left text-sm text-slate-700 [&_tbody_td]:px-4 [&_tbody_td]:py-3.5 [&_thead_th:first-child]:rounded-l-xl [&_thead_th:last-child]:rounded-r-xl [&_thead_th]:bg-slate-50 [&_thead_th]:px-4 [&_thead_th]:py-3 [&_thead_th]:font-medium [&_thead_th]:text-slate-500">
        <thead>
            <tr>{{ $head }}</tr>
        </thead>
        <tbody class="divide-y divide-slate-100">{{ $slot }}</tbody>
    </table>
</div>
