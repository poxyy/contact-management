import { createRouter, createWebHistory } from 'vue-router';

/** @type {import('vue-router').RouteRecordRaw[]} */
const routes = [
    {
        path: '/',
        redirect: { name: 'login' },
        component: () => import('../features/Layouts/AuthLayout.vue'),
        meta: { guestOnly: true },
        children: [
            {
                path: 'register',
                name: 'register',
                component: () => import('../features/User/pages/UserRegister.vue')
            },
            {
                path: 'login',
                name: 'login',
                component: () => import('../features/User/pages/UserLogin.vue')
            },
        ]
    },
    {
        path: '/dashboard',
        component: () => import('../features/Layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: 'contacts',
                name: 'contact-list',
                component: () => import('../features/Contact/pages/ContactList.vue')
            },
            {
                path: 'contacts/create',
                name: 'contact-create',
                component: () => import('../features/Contact/pages/ContactCreate.vue')
            },
            {
                path: 'contacts/:id/edit',
                name: 'contact-edit',
                component: () => import('../features/Contact/pages/ContactEdit.vue')
            },
            {
                path: 'contacts/:id',
                name: 'contact-detail',
                component: () => import('../features/Contact/pages/ContactDetail.vue')
            },
            {
                path: 'contacts/:id/addresses/create',
                name: 'address-create',
                component: () => import('../features/Address/pages/AddressCreate.vue')
            },
            {
                path: 'contacts/:id/addresses/:addressId/edit',
                name: 'address-edit',
                component: () => import('../features/Address/pages/AddressEdit.vue')
            },
            {
                path: 'users/profile',
                name: 'profile',
                component: () => import('../features/User/pages/UserProfile.vue')
            },
            {
                path: 'users/logout',
                name: 'logout',
                component: () => import('../features/User/pages/UserLogout.vue')
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/login'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');

    if (to.meta.requiresAuth && !token) {
        next({ name: 'login' });
    } else if (to.meta.guestOnly && token) {
        next({ name: 'contact-list' });
    } else {
        next();
    }
});

export default router;
