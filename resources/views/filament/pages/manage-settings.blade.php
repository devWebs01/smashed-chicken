<x-filament::page>
    <form wire:submit.prevent="save" class="space-y-6 pb-8"> {{-- pb-8 memberi ruang bawah --}}
        {{ $this->form }}

        <div class="flex items-center gap-3 mt-4" style="margin-top: 20px;">
            <x-filament::button type="submit">
                Simpan Perubahan
            </x-filament::button>

            <x-filament::button color="gray" type="button" wire:click="$refresh">
                Batal
            </x-filament::button>
        </div>
    </form>
</x-filament::page>
