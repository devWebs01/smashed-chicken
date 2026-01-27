  <!-- Checkout Modal -->
  @if ($showCheckout)
      <div class="fixed inset-0 flex items-center justify-center z-60 bg-transparent">
          <div class="bg-white rounded-lg w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto border">
              <div class="p-6">
                  <div class="flex justify-between items-center mb-4">
                      <h3 class="text-xl font-bold">Checkout</h3>
                      <button wire:click="$set('showCheckout', false)" class="text-gray-500 hover:text-gray-700">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                      </button>
                  </div>

                  <form wire:submit.prevent="submitOrder" class="space-y-4">
                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                          <input type="text" wire:model="customerName"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                              placeholder="Masukkan nama lengkap">
                          @error('customerName')
                              <span class="text-red-500 text-xs">{{ $message }}</span>
                          @enderror
                      </div>

                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP *</label>
                          <input type="tel" wire:model="customerPhone"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                              placeholder="08xxxxxxxxxx">
                          @error('customerPhone')
                              <span class="text-red-500 text-xs">{{ $message }}</span>
                          @enderror
                      </div>

                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Metode
                              Pengantaran</label>
                          <select wire:model="deliveryMethod"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                              <option value="delivery">Diantar</option>
                              <option value="takeaway">Bawa Pulang</option>
                          </select>
                      </div>

                      @if ($deliveryMethod === 'delivery')
                          <div>
                              <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap
                                  *</label>
                              <textarea wire:model="customerAddress"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                                  rows="3" placeholder="Masukkan alamat lengkap untuk pengiriman"></textarea>
                              @error('customerAddress')
                                  <span class="text-red-500 text-xs">{{ $message }}</span>
                              @enderror
                          </div>
                      @endif

                      <div>
                          <label class="block text-sm font-medium text-gray-700 mb-1">Metode
                              Pembayaran</label>
                          <select wire:model="paymentMethod"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500">
                              <option value="cod">Bayar di Tempat (COD)</option>
                              <option value="cash">Tunai</option>
                          </select>
                      </div>

                      <!-- Order Summary -->
                      <div class="bg-gray-50 p-4 rounded-lg">
                          <h4 class="font-medium mb-2">Ringkasan Pesanan</h4>
                          <div class="space-y-1 text-sm">
                              @foreach ($cart as $item)
                                  <div class="flex justify-between">
                                      <span>{{ $item['name'] }} x{{ $item['quantity'] }}</span>
                                      <span>{{ formatRupiah($item['price'] * $item['quantity']) }}</span>
                                  </div>
                              @endforeach
                              <div class="border-t pt-1 mt-2">
                                  <div class="flex justify-between font-semibold">
                                      <span>Total:</span>
                                      <span class="text-orange-600">{{ formatRupiah($this->getCartTotal()) }}</span>
                                  </div>
                              </div>
                          </div>
                      </div>

                      @if (session()->has('error'))
                          <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                              {{ session('error') }}
                          </div>
                      @endif

                      <button type="submit"
                          class="w-full bg-orange-500 text-white py-3 px-4 rounded-lg font-semibold hover:bg-orange-600 disabled:opacity-50"
                          wire:loading.attr="disabled">
                          <span wire:loading.remove>Pesan Sekarang</span>
                          <span wire:loading>Memproses...</span>
                      </button>
                  </form>
              </div>
          </div>
      </div>
  @endif

  <!-- Floating Cart Button -->
  @if (!empty($cart))
      <div class="fixed bottom-6 right-6 z-40">
          <button wire:click="toggleCart"
              class="bg-orange-500 text-white rounded-full p-4 shadow-lg hover:bg-orange-600 relative">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 01-2 2H9a2 2 0 01-2-2v-6m8 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01">
                  </path>
              </svg>
              <span
                  class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
                  {{ $this->getCartItemCount() }}
              </span>
          </button>
      </div>
  @endif
