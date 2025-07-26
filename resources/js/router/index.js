import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '../layouts/MainLayout.vue'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import Users from '../views/Users.vue'
import Settings from '../views/Settings.vue'
import Role from '../views/Role.vue'
import Permission from '../views/Permission.vue'

const routes = [
  { path: '/login', name: 'Login', component: Login, meta: { title: 'Login' } },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/views/ForgotPassword.vue'),
    meta: { title: 'Forgot Password' }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: () => import('@/views/ResetPassword.vue'), meta: { title: 'Reset Password' }
  },
  {
    path: '/',
    component: MainLayout,
    children: [
      { path: '', name: 'Dashboard', component: Dashboard, meta: { title: 'dashboard' } },
      { path: 'users', name: 'Users', component: Users, meta: { title: 'users' } },
      { path: 'settings', name: 'Settings', component: Settings, meta: { title: 'settings' } },
      { path: 'roles', name: 'Role', component: Role, meta: { title: 'Roles' } },
      { path: 'permissions', name: 'Permission', component: Permission, meta: { title: 'Permissions' } },
      {
        path: 'profile',
        name: 'MyProfile',
        component: () => import('@/views/MyProfile.vue'),
        meta: { requiresAuth: true, title: 'my_profile' }
      },
      {
        path: 'logs',
        name: 'LogActivity',
        component: () => import('@/views/LogActivity.vue'),
        meta: { requiresAuth: true, title: 'log_activity' },
      },
      {
        path: 'backups',
        name: 'Backups',
        component: () => import('@/views/Backup.vue'),
        meta: { requiresAuth: true, title: 'Backup Database' },
      }
    ]
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/views/404.vue'),
    meta: { title: 'Error 404' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Middleware auth
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  // Cek apakah route membutuhkan auth
  if (to.meta.requiresAuth && !token) {
    next({ name: 'Login' })
  } else {
    next()
  }
})

export default router
