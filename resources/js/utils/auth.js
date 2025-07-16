// js/utils/auth.js
import axios from 'axios'
import { reactive } from 'vue'

export const authState = reactive({
  user: null,
  token: null,
  permissions: []
})

export function setAuth(data) {
  authState.user = data.user
  authState.token = data.token
  authState.permissions = data.permissions

  localStorage.setItem('token', data.token)
  localStorage.setItem('user', JSON.stringify(data.user))
  localStorage.setItem('permissions', JSON.stringify(data.permissions))

  axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`
}

export function initAuth() {
  const token = localStorage.getItem('token')
  const user = localStorage.getItem('user')
  const permissions = localStorage.getItem('permissions')

  if (token && user && permissions) {
    authState.token = token
    authState.user = JSON.parse(user)
    authState.permissions = JSON.parse(permissions)

    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
  }
}

export function initLogout() {
  authState.user = null
  authState.token = null
  authState.permissions = []
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  localStorage.removeItem('permissions')
  delete axios.defaults.headers.common['Authorization']
}

export function can(permission) {
  return authState.permissions.includes(permission)
}
