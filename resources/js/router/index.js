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
import CategoriesPage from "../pages/Categories/Index.vue"
import BrandsPage from "../pages/Brands/Index.vue"

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
      meta: { titleKey: "menu.dashboard" },
    },
    {
      path: "/products",
      name: "products",
      component: ProductsPage,
      meta: { titleKey: "menu.products" },
    },
    {
      path: "/products/add",
      name: "products.add",
      component: ProductAddPage,
      meta: { titleKey: "product.add.title", requiresAdmin: true },
    },
    {
      path: "/products/:id",
      name: "products.detail",
      component: ProductDetailPage,
      meta: { titleKey: "product.detail.title" },
    },
    {
      path: "/sales",
      name: "sales",
      component: SalesPage,
      meta: { titleKey: "menu.sales" },
    },
    {
      path: "/orders",
      name: "orders",
      component: OrdersPage,
      meta: { titleKey: "menu.orders" },
    },
    {
      path: "/categories",
      name: "categories",
      component: CategoriesPage,
      meta: { titleKey: "menu.categories", requiresAdmin: true },
    },
    {
      path: "/brands",
      name: "brands",
      component: BrandsPage,
      meta: { titleKey: "menu.brands", requiresAdmin: true },
    },
    {
      path: "/settings",
      name: "settings",
      component: SettingsPage,
      meta: { titleKey: "menu.settings" },
    },
  ],
})

function currentUser() {
  try {
    const stored = localStorage.getItem("auth_user")
    return stored ? JSON.parse(stored) : null
  } catch {
    return null
  }
}

router.beforeEach((to) => {
  const token = localStorage.getItem("auth_token")
  const user = currentUser()

  if (to.meta.public) {
    if (token) return { name: "home" }
    return true
  }

  if (!token) return { name: "login" }

  if (to.meta.requiresAdmin && user?.role !== "admin") {
    return { name: "home" }
  }

  return true
})

export default router