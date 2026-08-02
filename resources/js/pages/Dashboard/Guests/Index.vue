<script setup lang="ts">
import DashboardLayout from '@/layouts/DashboardLayout.vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

interface Guest {
  id: number
  name: string
  phone: string | null
  category: string
  unique_code: string
  max_pax: number
  notes: string | null
  personal_link: string
  has_rsvp: boolean
  rsvp: {
    attendance: string
    pax_count: number
    message: string | null
  } | null
}

interface Stats {
  total: number
  family: number
  friends: number
  colleagues: number
  others: number
  confirmed: number
  declined: number
  pending: number
}

const props = defineProps<{
  guests: {
    data: Guest[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  stats: Stats
  filters: {
    search: string | null
    category: string
  }
}>()

const showAddModal = ref(false)
const showEditModal = ref(false)
const showImportModal = ref(false)
const editingGuest = ref<Guest | null>(null)
const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || 'all')

const addForm = useForm({
  name: '',
  phone: '',
  category: 'family',
  max_pax: 1,
  notes: '',
})

const editForm = useForm({
  name: '',
  phone: '',
  category: 'family',
  max_pax: 1,
  notes: '',
})

const importForm = useForm({
  file: null as File | null,
})

const categoryLabels: Record<string, string> = {
  all: 'Semua',
  family: 'Keluarga',
  friends: 'Teman',
  colleagues: 'Rekan Kerja',
  others: 'Lainnya',
}

const categoryColors: Record<string, string> = {
  family: 'bg-green-100 text-green-800',
  friends: 'bg-blue-100 text-blue-800',
  colleagues: 'bg-green-100 text-green-800',
  others: 'bg-gray-100 text-gray-800',
}

const openAddModal = () => {
  addForm.reset()
  showAddModal.value = true
}

const closeAddModal = () => {
  showAddModal.value = false
  addForm.reset()
}

const submitAdd = () => {
  addForm.post('/dashboard/guests', {
    onSuccess: () => {
      closeAddModal()
    },
  })
}

const openEditModal = (guest: Guest) => {
  editingGuest.value = guest
  editForm.name = guest.name
  editForm.phone = guest.phone || ''
  editForm.category = guest.category
  editForm.max_pax = guest.max_pax
  editForm.notes = guest.notes || ''
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingGuest.value = null
  editForm.reset()
}

const submitEdit = () => {
  if (!editingGuest.value) return
  
  editForm.put(`/dashboard/guests/${editingGuest.value.id}`, {
    onSuccess: () => {
      closeEditModal()
    },
  })
}

const deleteGuest = (guest: Guest) => {
  if (confirm(`Hapus tamu ${guest.name}?`)) {
    router.delete(`/dashboard/guests/${guest.id}`)
  }
}

const copyLink = (link: string) => {
  navigator.clipboard.writeText(link)
  alert('Link berhasil disalin!')
}

const sendWhatsApp = (guestId: number) => {
  window.open(`/dashboard/guests/${guestId}/whatsapp`, '_blank')
}

const filterByCategory = (category: string) => {
  selectedCategory.value = category
  router.get('/dashboard/guests', {
    category,
    search: searchQuery.value,
  }, {
    preserveState: true,
  })
}

const search = () => {
  router.get('/dashboard/guests', {
    category: selectedCategory.value,
    search: searchQuery.value,
  }, {
    preserveState: true,
  })
}

const openImportModal = () => {
  importForm.reset()
  showImportModal.value = true
}

const closeImportModal = () => {
  showImportModal.value = false
  importForm.reset()
}

const handleFileChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    importForm.file = target.files[0]
  }
}

const submitImport = () => {
  importForm.post('/dashboard/guests/import', {
    onSuccess: () => {
      closeImportModal()
    },
  })
}

const exportGuests = () => {
  window.location.href = '/dashboard/guests/export'
}

const downloadTemplate = () => {
  // Create CSV template
  const csv = 'Nama,Telepon,Kategori,Max Pax,Catatan\nJohn Doe,081234567890,family,2,Catatan opsional'
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = 'template-import-tamu.csv'
  a.click()
  window.URL.revokeObjectURL(url)
}

</script>

<template>
  <DashboardLayout>
    <Head title="Manajemen Tamu" />

    <div class="py-8">
      <div class="container mx-auto px-4">
        <!-- Page Header -->
        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Manajemen Tamu</h1>
            <p class="text-gray-600 mt-1">Kelola daftar tamu undangan Anda</p>
          </div>
          <div class="flex gap-3">
            <button
              @click="openImportModal"
              class="border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:border-gray-400 transition"
            >
              📥 Import
            </button>
            <button
              @click="exportGuests"
              class="border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:border-gray-400 transition"
            >
              📤 Export
            </button>
            <button
              @click="openAddModal"
              class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition"
            >
              + Tambah Tamu
            </button>
          </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
          {{ $page.props.flash.success }}
        </div>
        <div v-if="$page.props.flash?.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
          {{ $page.props.flash.error }}
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-3xl font-bold text-gray-900 mb-1">{{ stats.total }}</div>
            <div class="text-sm text-gray-600">Total Tamu</div>
          </div>
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-3xl font-bold text-green-600 mb-1">{{ stats.confirmed }}</div>
            <div class="text-sm text-gray-600">Konfirmasi Hadir</div>
          </div>
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-3xl font-bold text-red-600 mb-1">{{ stats.declined }}</div>
            <div class="text-sm text-gray-600">Tidak Hadir</div>
          </div>
          <div class="bg-white rounded-xl shadow-md p-6">
            <div class="text-3xl font-bold text-yellow-600 mb-1">{{ stats.pending }}</div>
            <div class="text-sm text-gray-600">Belum Konfirmasi</div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
          <div class="flex flex-wrap gap-4 items-center">
            <!-- Search -->
            <div class="flex-1 min-w-[300px]">
              <input
                v-model="searchQuery"
                @keyup.enter="search"
                type="text"
                placeholder="Cari nama atau telepon..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              />
            </div>

            <!-- Category Filter -->
            <div class="flex gap-2">
              <button
                v-for="(label, key) in categoryLabels"
                :key="key"
                @click="filterByCategory(key)"
                class="px-4 py-2 rounded-lg font-medium transition"
                :class="selectedCategory === key
                  ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                {{ label }}
                <span v-if="key !== 'all'" class="ml-1">
                  ({{ stats[key as keyof Stats] }})
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Guest List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
          <div v-if="guests.data.length === 0" class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada tamu</h3>
            <p class="text-gray-600 mb-4">Mulai tambahkan tamu undangan Anda</p>
            <button
              @click="openAddModal"
              class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition"
            >
              + Tambah Tamu Pertama
            </button>
          </div>

          <table v-else class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telepon</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Pax</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status RSVP</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="guest in guests.data" :key="guest.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="font-medium text-gray-900">{{ guest.name }}</div>
                  <div v-if="guest.notes" class="text-sm text-gray-500">{{ guest.notes }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ guest.phone || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full" :class="categoryColors[guest.category]">
                    {{ categoryLabels[guest.category] }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ guest.max_pax }} orang
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    v-if="guest.has_rsvp"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="guest.rsvp?.attendance === 'hadir'
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800'"
                  >
                    {{ guest.rsvp?.attendance === 'hadir' ? `Hadir (${guest.rsvp.pax_count})` : 'Tidak Hadir' }}
                  </span>
                  <span v-else class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                    Belum Konfirmasi
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <div class="flex gap-2">
                    <button
                      @click="copyLink(guest.personal_link)"
                      class="text-blue-600 hover:text-blue-800"
                      title="Copy Link"
                    >
                      🔗
                    </button>
                    <button
                      v-if="guest.phone"
                      @click="sendWhatsApp(guest.id)"
                      class="text-green-600 hover:text-green-800"
                      title="Kirim WhatsApp"
                    >
                      📱
                    </button>
                    <button
                      @click="openEditModal(guest)"
                      class="text-gray-600 hover:text-gray-800"
                      title="Edit"
                    >
                      ✏️
                    </button>
                    <button
                      @click="deleteGuest(guest)"
                      class="text-red-600 hover:text-red-800"
                      title="Hapus"
                    >
                      🗑️
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Pagination -->
          <div v-if="guests.last_page > 1" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Menampilkan {{ (guests.current_page - 1) * guests.per_page + 1 }} - 
              {{ Math.min(guests.current_page * guests.per_page, guests.total) }} dari {{ guests.total }} tamu
            </div>
            <div class="flex gap-2">
              <Link
                v-for="page in guests.last_page"
                :key="page"
                :href="`/dashboard/guests?page=${page}&category=${selectedCategory}&search=${searchQuery}`"
                class="px-3 py-1 rounded"
                :class="page === guests.current_page
                  ? 'bg-green-600 text-white'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
              >
                {{ page }}
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Modal -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Tambah Tamu</h2>
        
        <form @submit.prevent="submitAdd" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
            <input
              v-model="addForm.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="addForm.errors.name" class="text-red-600 text-sm mt-1">{{ addForm.errors.name }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
            <input
              v-model="addForm.phone"
              type="text"
              placeholder="081234567890"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="addForm.errors.phone" class="text-red-600 text-sm mt-1">{{ addForm.errors.phone }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
            <select
              v-model="addForm.category"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option value="family">Keluarga</option>
              <option value="friends">Teman</option>
              <option value="colleagues">Rekan Kerja</option>
              <option value="others">Lainnya</option>
            </select>
            <div v-if="addForm.errors.category" class="text-red-600 text-sm mt-1">{{ addForm.errors.category }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Maksimal Tamu *</label>
            <input
              v-model.number="addForm.max_pax"
              type="number"
              min="1"
              max="10"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="addForm.errors.max_pax" class="text-red-600 text-sm mt-1">{{ addForm.errors.max_pax }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
            <textarea
              v-model="addForm.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ></textarea>
            <div v-if="addForm.errors.notes" class="text-red-600 text-sm mt-1">{{ addForm.errors.notes }}</div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeAddModal"
              class="flex-1 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="addForm.processing"
              class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
            >
              {{ addForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Modal -->
    <div v-if="showEditModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Edit Tamu</h2>
        
        <form @submit.prevent="submitEdit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nama *</label>
            <input
              v-model="editForm.name"
              type="text"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="editForm.errors.name" class="text-red-600 text-sm mt-1">{{ editForm.errors.name }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
            <input
              v-model="editForm.phone"
              type="text"
              placeholder="081234567890"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="editForm.errors.phone" class="text-red-600 text-sm mt-1">{{ editForm.errors.phone }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
            <select
              v-model="editForm.category"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option value="family">Keluarga</option>
              <option value="friends">Teman</option>
              <option value="colleagues">Rekan Kerja</option>
              <option value="others">Lainnya</option>
            </select>
            <div v-if="editForm.errors.category" class="text-red-600 text-sm mt-1">{{ editForm.errors.category }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Maksimal Tamu *</label>
            <input
              v-model.number="editForm.max_pax"
              type="number"
              min="1"
              max="10"
              required
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="editForm.errors.max_pax" class="text-red-600 text-sm mt-1">{{ editForm.errors.max_pax }}</div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
            <textarea
              v-model="editForm.notes"
              rows="3"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            ></textarea>
            <div v-if="editForm.errors.notes" class="text-red-600 text-sm mt-1">{{ editForm.errors.notes }}</div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeEditModal"
              class="flex-1 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="editForm.processing"
              class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
            >
              {{ editForm.processing ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Import Tamu dari CSV</h2>
        
        <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <p class="text-sm text-blue-800 mb-2">Format CSV:</p>
          <code class="text-xs text-blue-900">Nama,Telepon,Kategori,Max Pax,Catatan</code>
          <p class="text-xs text-blue-700 mt-2">
            Kategori: family, friends, colleagues, others
          </p>
        </div>

        <button
          @click="downloadTemplate"
          class="w-full mb-4 border-2 border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-50 transition"
        >
          📥 Download Template CSV
        </button>

        <form @submit.prevent="submitImport" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">File CSV *</label>
            <input
              type="file"
              accept=".csv,.txt"
              required
              @change="handleFileChange"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            />
            <div v-if="importForm.errors.file" class="text-red-600 text-sm mt-1">{{ importForm.errors.file }}</div>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="button"
              @click="closeImportModal"
              class="flex-1 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="importForm.processing"
              class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 text-white px-6 py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50"
            >
              {{ importForm.processing ? 'Mengimport...' : 'Import' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>
