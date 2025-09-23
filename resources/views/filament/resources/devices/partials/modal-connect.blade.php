 <x-filament::modal id="connectDevice">
     <x-slot name="heading">
         Hubungkan WhatsApp
     </x-slot>

     <x-slot name="description">
         <p class="mb-3 text-sm text-gray-600">
             Untuk menggunakan WhatsApp di komputer:
         </p>
         <ol class="list-decimal list-inside text-sm space-y-1">
             <li>Buka WhatsApp di HP kamu</li>
             <li>Pilih <b>Menu</b> atau <b>Pengaturan</b> → <b>Perangkat Tertaut</b></li>
             <li>Arahkan kamera HP ke QR Code di bawah</li>
         </ol>

         @if ($qrUrl)
             <div class="flex justify-center mt-4">
                 <img src="data:image/png;base64,{{ $qrUrl }}" alt="QR Code"
                     class="rounded-lg border p-2 shadow-md">
             </div>
         @else
             <p class="text-center text-gray-500 mt-4">Loading QR...</p>
         @endif
     </x-slot>

     <x-slot name="footerActions">

         <x-filament::button color="gray" wire:click="$dispatch('close-modal', { id: 'connectDevice' })">
             Batal
         </x-filament::button>
         <x-filament::button color="primary" wire:click="checkDeviceStatus">
             Konfirmasi
         </x-filament::button>
     </x-slot>
 </x-filament::modal>
