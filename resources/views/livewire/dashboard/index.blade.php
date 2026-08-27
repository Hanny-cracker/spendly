<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">
                Dashboard
            </h1>

            <p class="text-sm text-gray-500">
                Your financial overview
            </p>
        </div>

        <div class="flex gap-3">
            <input
                type="date"
                wire:model.live="startDate"
                class="rounded border px-3 py-2"
            >

            <input
                type="date"
                wire:model.live="endDate"
                class="rounded border px-3 py-2"
            >
        </div>
    </div>


    {{-- Financial Overview --}}
    @if (! empty($dashboardData))
        @include('livewire.dashboard.partials.financial-overview')

        {{-- Spending Overview --}}
        @include('livewire.dashboard.partials.spending-overview')
    @endif

</div>