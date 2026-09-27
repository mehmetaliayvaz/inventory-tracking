import { createRouter, createWebHistory } from "vue-router"

import HomePage from "../pages/HomePage/index.vue"
import SettingsPage from "../pages/SettingsPage/index.vue"
import LoginPage from "../pages/Auth/Login.vue"
import RegisterPage from "../pages/Auth/Register.vue"
import ProductsPage from "../pages/Products/Index.vue"
import ProductAddPage from "../pages/Products/Add.vue"
import ProductDetailPage from "../pages/Products/Detail.vue"
import SalesPage from "../pages/Sales/Index.vue"
import OrdersPage from "../pages/Orders/Index.vue"

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginPage,
      meta: { public: true },
    },
    {
      path: "/register",
      name: "register",
      component: RegisterPage,
      meta: { public: true },
    },
    {
      path: "/",
      name: "home",
      component: HomePage,
    },
    {
      path: "/products",
      name: "products",
      component: ProductsPage,
    },
    {
      path: "/products/add",
      name: "products.add",
      component: ProductAddPage,
    },
    {
      path: "/products/:id",
      name: "products.detail",
      component: ProductDetailPage,
    },
    {
      path: "/sales",
      name: "sales",
      component: SalesPage,
    },
    {
      path: "/orders",
      name: "orders",
      component: OrdersPage,
    },
    {
      path: "/settings",
      name: "settings",
      component: SettingsPage,
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem("auth_token")

  if (to.meta.public) {
    if (token) return { name: "home" }
    return true
  }

  if (!token) return { name: "login" }
  return true
})

export default router