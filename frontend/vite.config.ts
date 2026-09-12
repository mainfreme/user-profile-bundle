import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { userProfileApiMock } from './vite.user-api-mock'

const useMockApi = process.env.VITE_API_MOCK !== 'false'

export default defineConfig({
  plugins: [
    vue(),
    ...(useMockApi ? [userProfileApiMock()] : []),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: '0.0.0.0',
    port: 5173,
    proxy: useMockApi
      ? undefined
      : {
          '/api': {
            target: process.env.VITE_DEV_PROXY_TARGET || 'http://127.0.0.1:8080',
            changeOrigin: true,
          },
        },
  },
})
