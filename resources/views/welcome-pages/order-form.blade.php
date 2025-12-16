  <!-- Success Modal -->
  @if ($orderSuccess)
      <div class="fixed inset-0 flex items-center justify-center z-60">
          <div class="bg-white rounded-lg p-8 max-w-md mx-4 text-center border">
              <div class="text-green-500 text-6xl mb-4">✓</div>
              <h3 class="text-2xl font-bold text-gray-800 mb-4">Pesanan Berhasil!</h3>
              <p class="text-gray-600 mb-4">
                  Pesanan Anda dengan nomor <strong>#{{ $orderNumber }}</strong> telah berhasil dibuat.
              </p>
              <p class="text-sm text-gray-500 mb-6">
                  Kami akan segera memproses pesanan Anda. Terima kasih!
              </p>
              <button wire:click="closeSuccessModal"
                  class="w-full bg-orange-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-orange-600">
                  Tutup
              </button>
          </div>
      </div>
  @endif

  <!-- Shopping Cart Modal -->
  @if ($showCart)
      <div class="fixed inset-0 flex items-center justify-center z-60 bg-transparent">
          <div class="bg-white rounded-lg w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto border">
              <div class="p-6">
                  <div class="flex justify-between items-center mb-4">
                      <h3 class="text-xl font-bold">Keranjang Belanja</h3>
                      <button wire:click="toggleCart" class="text-gray-500 hover:text-gray-700">
                          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M6 18L18 6M6 6l12 12"></path>
                          </svg>
                      </button>
                  </div>

                  @if (empty($cart))
                      <div class="text-center py-8">
                          <div class="text-gray-400 text-4xl mb-4">🛒</div>
                          <p class="text-gray-500">Keranjang masih kosong</p>
                      </div>
                  @else
                      <div class="space-y-4">
                          @foreach ($cart as $item)
                              <div class="flex items-center space-x-3 border-b pb-3">
                                  <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}"
                                      class="w-16 h-16 object-cover rounded-lg">
                                  <div class="flex-1">
                                      <h4 class="font-medium text-sm">{{ $item['name'] }}</h4>
                                      <p class="text-orange-600 font-semibold">
                                          {{ formatRupiah($item['price']) }}</p>
                                      <div class="flex items-center space-x-2 mt-2">
                                          <button
                                              wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                              class="w-7 h-7 flex items-center justify-center bg-gray-200 rounded text-sm">-</button>
                                          <span class="text-sm font-medium">{{ $item['quantity'] }}</span>
                                          <button
                                              wire:click="updateCartQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                              class="w-7 h-7 flex items-center justify-center bg-gray-200 rounded text-sm">+</button>
                                          <button wire:click="removeFromCart({{ $item['id'] }})"
                                              class="ml-2 text-red-500 text-sm">Hapus</button>
                                      </div>
                                  </div>
                                  <div class="text-right">
                                      <p class="font-semibold">
                                          {{ formatRupiah($item['price'] * $item['quantity']) }}</p>
                                  </div>
                              </div>
                          @endforeach
                      </div>

                      <div class="mt-6 pt-4 border-t">
                          <div class="flex justify-between items-center mb-4">
                              <span class="text-lg font-bold">Total:</span>
                              <span
                                  class="text-lg font-bold text-orange-600">{{ formatRupiah($this->getCartTotal()) }}</span>
                          </div>
                          <button wire:click="proceedToCheckout"
                              class="w-full bg-orange-500 text-white py-3 px-4 rounded-lg font-semibold hover:bg-orange-600">
                              Lanjut ke Checkout
                          </button>
                      </div>
                  @endif
              </div>
          </div>
      </div>
  @endif
