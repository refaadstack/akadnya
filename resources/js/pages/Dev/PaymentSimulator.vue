<script setup lang="ts">
import { router } from '@inertiajs/vue3'

defineOptions({ layout: undefined })

const props = defineProps<{
  orders: Array<{
    id: number
    order_number: string
    user_name: string
    user_email: string
    total_amount: number
    status: string
    created_at: string
    items: Array<{
      name: string
      price: number
    }>
    payment: {
      provider_transaction_id: string
      status: string
    } | null
  }>
  flash?: {
    success?: string
    error?: string
  }
}>()

const simulateSuccess = (orderId: number) => {
  if (confirm('Simulasikan pembayaran berhasil untuk order ini?')) {
    router.post('/dev/payment-simulator/success', { order_id: orderId })
  }
}

const simulateFailure = (orderId: number) => {
  if (confirm('Simulasikan pembayaran gagal untuk order ini?')) {
    router.post('/dev/payment-simulator/failure', { order_id: orderId })
  }
}

const simulateExpired = (orderId: number) => {
  if (confirm('Simulasikan pembayaran expired untuk order ini?')) {
    router.post('/dev/payment-simulator/expired', { order_id: orderId })
  }
}

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-purple-50 to-pink-50">
    <!-- Header -->
    <div class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
              Payment Simulator
            </h1>
            <p class="mt-1 text-sm text-gray-600">
              Simulasi webhook Midtrans untuk testing di local development
            </p>
          </div>
          <a
            href="/"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
          >
            ← Kembali
          </a>
        </div>
      </div>
    </div>

    <!-- Flash Messages -->
    <div v-if="props.flash?.success || props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
      <div
        v-if="props.flash?.success"
        class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg"
      >
        {{ props.flash.success }}
      </div>
      <div
        v-if="props.flash?.error"
        class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg"
      >
        {{ props.flash.error }}
      </div>
    </div>

    <!-- Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Info Box -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">Development Tool</h3>
            <div class="mt-2 text-sm text-blue-700">
              <p>Tool ini hanya untuk development. Gunakan untuk mensimulasikan webhook Midtrans tanpa perlu payment gateway yang sebenarnya.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Orders List -->
      <div v-if="props.orders.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada order pending</h3>
        <p class="mt-1 text-sm text-gray-500">Buat order baru dari halaman checkout untuk testing.</p>
      </div>

      <div v-else class="space-y-4">
        <div
          v-for="order in props.orders"
          :key="order.id"
          class="bg-white rounded-lg shadow overflow-hidden"
        >
          <div class="p-6">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ order.order_number }}
                  </h3>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-yellow-100 text-yellow-800': order.status === 'pending',
                      'bg-green-100 text-green-800': order.status === 'paid',
                      'bg-red-100 text-red-800': order.status === 'failed',
                      'bg-gray-100 text-gray-800': order.status === 'expired',
                    }"
                  >
                    {{ order.status }}
                  </span>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                  <p><strong>User:</strong> {{ order.user_name }} ({{ order.user_email }})</p>
                  <p><strong>Total:</strong> {{ formatCurrency(order.total_amount) }}</p>
                  <p><strong>Dibuat:</strong> {{ order.created_at }}</p>
                  <p v-if="order.payment">
                    <strong>Transaction ID:</strong> {{ order.payment.provider_transaction_id }}
                  </p>
                </div>
                <div class="mt-3">
                  <p class="text-sm font-medium text-gray-700">Items:</p>
                  <ul class="mt-1 text-sm text-gray-600 list-disc list-inside">
                    <li v-for="(item, idx) in order.items" :key="idx">
                      {{ item.name }} - {{ formatCurrency(item.price) }}
                    </li>
                  </ul>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
              <button
                @click="simulateSuccess(order.id)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
              >
                ✓ Simulasi Success
              </button>
              <button
                @click="simulateFailure(order.id)"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
              >
                ✗ Simulasi Failure
              </button>
              <button
                @click="simulateExpired(order.id)"
                class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition text-sm font-medium"
              >
                ⏱ Simulasi Expired
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
