            {{-- modal request Delete Otp --}}
            <x-filament::modal id="requestDeleteOtp">
                <x-slot name="heading">
                    Konfirmasi Hapus Device
                </x-slot>
                <x-slot name="description">
                    Masukkan OTP yang sudah dikirim ke WhatsApp untuk menghapus device ini.
                </x-slot>

                <x-filament::input wire:model="otp" placeholder="Masukkan OTP" />

                <x-slot name="footerActions">
                    <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'requestDeleteOtp' })">
                        Batal
                    </x-filament::button>
                    <x-filament::button color="danger" wire:click="confirmDelete">
                        Hapus
                    </x-filament::button>
                </x-slot>

            </x-filament::modal>
