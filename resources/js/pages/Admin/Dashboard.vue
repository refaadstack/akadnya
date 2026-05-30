<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'

defineOptions({
  layout: AdminLayout
})

interface Stats {
  total_users: number
  total_orders: number
  total_revenue: number
  total_templates: number
  active_templates: number
  total_invitations: number
  published_invitations: number
  pending_orders: number
}

interface Order {
  id: number
  order_number: string
  user_name: string
  user_email: string
  total_amount: number
  status: string
  payment_status: string
  created_at: string
}

interface User {
  id: number
  name: string
  email: string
  role: string
  has_invitation: boolean
  created_at: string
}

interface RevenueData {
  date: string
  revenue: number
}

interface TemplateStats {
  name: string
  usage_count: number
}

const props = defineProps<{
  stats: Stats
  recentOrders: Order[]
  recentUsers: User[]
  revenueChart: RevenueData[]
  templateStats: TemplateStats[]
}>()

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
  }).format(amount)
}

const getStatusColor = (status: string) => {
  const colors: Record<string, string> = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    success: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
  <div>
    <Head title="Admin Dashboard" />

    <div class="container mx-auto px-4 py-8">
        <!-- Stats Grid -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
          <!-- Total Users -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_users }}</div>
            <div class="text-sm text-gray-600">Total Users</div>
          </div>

          <!-- Total Orders -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_orders }}</div>
            <div class="text-sm text-gray-600">Total Orders</div>
            <div v-if="stats.pending_orders > 0" class="mt-2 text-xs text-yellow-600">
              {{ stats.pending_orders }} pending
            </div>
          </div>

          <!-- Total Revenue -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
            <div class="text-2xl font-bold text-gray-900 mb-1">{{ formatCurrency(stats.total_revenue) }}</div>
            <div class="text-sm text-gray-600">Total Revenue</div>
          </div>

          <!-- Total Templates -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="w-12 h-12 bg-pink-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                </svg>
              </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total_templates }}</div>
            <div class="text-sm text-gray-600">Total Templates</div>
            <div class="mt-2 text-xs text-green-600">
              {{ stats.active_templates }} active
            </div>
          </div>
        </div>

        <!-- Secondary Stats -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Invitations</h3>
            <div class="flex items-center justify-between">
              <div>
                <div class="text-3xl font-bold text-gray-900">{{ stats.total_invitations }}</div>
                <div class="text-sm text-gray-600">Total Invitations</div>
              </div>
              <div class="text-right">
                <div class="text-2xl font-bold text-green-600">{{ stats.published_invitations }}</div>
                <div class="text-sm text-gray-600">Published</div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Revenue (Last 7 Days)</h3>
            <div class="flex items-end space-x-2 h-32">
              <div
                v-for="(data, index) in revenueChart"
                :key="index"
                class="flex-1 flex flex-col items-center"
              >
                <div
                  class="w-full bg-gradient-to-t from-pink-600 to-purple-600 rounded-t"
                  :style="{ height: (data.revenue / Math.max(...revenueChart.map(d => d.revenue)) * 100) + '%' }"
                ></div>
                <div class="text-xs text-gray-600 mt-2">{{ data.date }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Orders & Users -->
        <div class="grid md:grid-cols-2 gap-6 mb-8">
          <!-- Recent Orders -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Recent Orders</h3>
              <Link href="/admin/orders" class="text-sm text-pink-600 hover:text-pink-700 font-medium">
                View All →
              </Link>
            </div>

            <div class="space-y-3">
              <div
                v-for="order in recentOrders"
                :key="order.id"
                class="flex items-start justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
              >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">{{ order.order_number }}</p>
                  <p class="text-sm text-gray-600">{{ order.user_name }}</p>
                  <div class="flex items-center space-x-2 mt-1">
                    <span
                      class="px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusColor(order.payment_status)"
                    >
                      {{ order.payment_status }}
                    </span>
                    <span class="text-xs text-gray-500">{{ order.created_at }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="font-bold text-gray-900">{{ formatCurrency(order.total_amount) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Users -->
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-bold text-gray-900">Recent Users</h3>
              <Link href="/admin/users" class="text-sm text-pink-600 hover:text-pink-700 font-medium">
                View All →
              </Link>
            </div>

            <div class="space-y-3">
              <div
                v-for="user in recentUsers"
                :key="user.id"
                class="flex items-start justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
              >
                <div class="flex-1">
                  <p class="font-semibold text-gray-900">{{ user.name }}</p>
                  <p class="text-sm text-gray-600">{{ user.email }}</p>
                  <div class="flex items-center space-x-2 mt-1">
                    <span
                      v-if="user.role === 'admin'"
                      class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                    >
                      Admin
                    </span>
                    <span
                      v-if="user.has_invitation"
                      class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                    >
                      Has Invitation
                    </span>
                    <span class="text-xs text-gray-500">{{ user.created_at }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Template Usage Stats -->
        <div class="bg-white rounded-xl shadow-md p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-4">Top Templates</h3>
          <div class="space-y-3">
            <div
              v-for="(template, index) in templateStats"
              :key="index"
              class="flex items-center justify-between"
            >
              <div class="flex items-center flex-1">
                <div class="w-8 h-8 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                  <span class="text-white font-bold text-sm">{{ index + 1 }}</span>
                </div>
                <span class="font-medium text-gray-900">{{ template.name }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-2xl font-bold text-pink-600 mr-2">{{ template.usage_count }}</span>
                <span class="text-sm text-gray-600">uses</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

