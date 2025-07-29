import { createRouter, createWebHistory } from "vue-router"
import { isAuthenticated } from "@/utils/auth"

const Landing = () => import('@/components/user/landing-page.vue')
const Eval = () => import('@/components/user/evalForm.vue')
const Honepage = () => import('@/components/admin/homepage.vue')
const adminUser = () => import('@/components/admin/admin-user.vue')

const routes = [
    {
        path: "/",
        name: "landingPage",
        component: Landing,
        meta: {title: 'Home Page', requiresAuth: false}
    },
    
    {
      path: "/eval",
      name: "evalPage",
      component: Eval,
      meta: {title: "Evaluation", requiresAuth: false}
    },

    {
      path: "/homepage",
      name: "homePage",
      component: Honepage,
      meta: {title: "Home Page", requiresAuth: false}
    },

    {
      path: "/adminUser",
      name: "adminUser",
      component: adminUser,
      meta: {title: "Users", requiresAuth: false}
    }
];


const router = createRouter({
  history: createWebHistory(),
  routes,
});


router.beforeEach((to, from, next) => {
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!isAuthenticated()) {
      // Redirect to login page if not authenticated
      next({
        path: '/',
        query: { redirect: to.fullPath }
      })
    } else {
      next()
    }
  } else {
    next()
  }

  if (to.meta?.title) {
    document.title = to.meta.title
  }
})

export default router;
