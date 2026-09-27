import { ref, computed } from "vue"
import api from "../api"

const user = ref(null)
const isAuthenticated = computed(() => !!user.value)

function loadStoredUser() {
  const stored = localStorage.getItem("auth_user")
  if (stored) {
    try {
      user.value = JSON.parse(stored)
    } catch {
      user.value = null
    }
  }
}

async function fetchUser() {
  const { data } = await api.get("/user")
  user.value = data.data
  localStorage.setItem("auth_user", JSON.stringify(user.value))
  return user.value
}

async function login(payload) {
  const { data } = await api.post("/login", payload)
  const { token, user: userData } = data.data
  localStorage.setItem("auth_token", token)
  user.value = userData
  localStorage.setItem("auth_user", JSON.stringify(userData))
  return userData
}

async function register(payload) {
  const { data } = await api.post("/register", payload)
  const { token, user: userData } = data.data
  localStorage.setItem("auth_token", token)
  user.value = userData
  localStorage.setItem("auth_user", JSON.stringify(userData))
  return userData
}

async function logout() {
  try {
    await api.post("/logout")
  } catch (error) {
    // token already invalid, continue
  }
  user.value = null
  localStorage.removeItem("auth_token")
  localStorage.removeItem("auth_user")
}

export { user, isAuthenticated, loadStoredUser, fetchUser, login, register, logout }