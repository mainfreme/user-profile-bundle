import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'
import UserListView from '@/views/UserListView.vue'
import UserCreateView from '@/views/UserCreateView.vue'
import UserProfileView from '@/views/UserProfileView.vue'
import RoleCatalogView from '@/views/RoleCatalogView.vue'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'users',
    component: UserListView,
    meta: { title: 'Użytkownicy' },
  },
  {
    path: '/users/new',
    name: 'user-create',
    component: UserCreateView,
    meta: { title: 'Nowy użytkownik' },
  },
  {
    path: '/users/:id',
    name: 'user-profile',
    component: UserProfileView,
    props: true,
    meta: { title: 'Profil użytkownika' },
  },
  {
    path: '/roles',
    name: 'roles',
    component: RoleCatalogView,
    meta: { title: 'Role i grupy' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

export default router
