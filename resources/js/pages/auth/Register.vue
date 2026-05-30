<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'

defineOptions({
  layout: undefined
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  terms: false,
})

const submit = () => {
  form.post('/register', {
    onFinish: () => {
      form.reset('password', 'password_confirmation')
    },
  })
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50 flex items-center justify-center px-4 py-12">
    <Head title="Daftar" />

    <div class="w-full max-w-md">
      <!-- Logo -->
      <Link href="/" class="flex items-center justify-center space-x-2 mb-8">
        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-purple-600 rounded-lg flex items-center justify-center">
          <span class="text-white font-bold text-2xl">M</span>
        </div>
        <span class="text-3xl font-bold bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-transparent">
          MyAkad
        </span>
      </Link>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">Buat Akun Baru</h1>
          <p class="text-gray-600">Mulai buat undangan digital Anda sekarang</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-6">
          <!-- Name -->
          <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
              Nama Lengkap
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              autofocus
              autocomplete="name"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.name }"
              placeholder="John Doe"
            />
            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
              {{ form.errors.name }}
            </p>
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              autocomplete="username"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.email }"
              placeholder="nama@email.com"
            />
            <p v-if="form.errors.email" class="mt-2 text-sm text-red-600">
              {{ form.errors.email }}
            </p>
          </div>

          <!-- Password -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.password }"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password" class="mt-2 text-sm text-red-600">
              {{ form.errors.password }}
            </p>
            <p class="mt-2 text-xs text-gray-500">
              Minimal 8 karakter
            </p>
          </div>

          <!-- Password Confirmation -->
          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
              Konfirmasi Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 focus:border-transparent transition"
              :class="{ 'border-red-500': form.errors.password_confirmation }"
              placeholder="••••••••"
            />
            <p v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
              {{ form.errors.password_confirmation }}
            </p>
          </div>

          <!-- Terms -->
          <div>
            <label class="flex items-start">
              <input
                v-model="form.terms"
                type="checkbox"
                required
                class="w-4 h-4 text-pink-600 border-gray-300 rounded focus:ring-pink-500 mt-1"
              />
              <span class="ml-2 text-sm text-gray-700">
                Saya setuju dengan
                <a href="#" class="text-pink-600 hover:text-pink-700 font-medium">Syarat & Ketentuan</a>
                dan
                <a href="#" class="text-pink-600 hover:text-pink-700 font-medium">Kebijakan Privasi</a>
              </span>
            </label>
            <p v-if="form.errors.terms" class="mt-2 text-sm text-red-600">
              {{ form.errors.terms }}
            </p>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="form.processing"
            class="w-full bg-gradient-to-r from-pink-600 to-purple-600 text-white py-3 rounded-lg font-semibold hover:shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ form.processing ? 'Memproses...' : 'Daftar Sekarang' }}
          </button>
        </form>

        <!-- Login Link -->
        <div class="mt-6 text-center">
          <p class="text-gray-600">
            Sudah punya akun?
            <Link href="/login" class="text-pink-600 hover:text-pink-700 font-semibold">
              Masuk
            </Link>
          </p>
        </div>
      </div>

      <!-- Back to Home -->
      <div class="text-center mt-6">
        <Link href="/" class="text-gray-600 hover:text-gray-900 text-sm">
          ← Kembali ke beranda
        </Link>
      </div>
    </div>
  </div>
</template>
