<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const fillDemo = (role) => {
    if (role === 'admin') {
        form.email = 'admin@source-x.test';
        form.password = 'password';
    } else {
        form.email = 'buyer@source-x.test';
        form.password = 'password';
    }
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <!-- Demo Accounts Box -->
        <div class="mb-6 rounded-lg border border-indigo-100 bg-indigo-50/80 p-3.5 text-xs">
            <div class="flex items-center justify-between mb-2">
                <span class="font-semibold text-indigo-900">Demo Login Accounts</span>
                <span class="text-[11px] text-indigo-600">Click any card to auto-fill</span>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <button
                    type="button"
                    @click="fillDemo('admin')"
                    class="flex flex-col items-start rounded-md border border-indigo-200 bg-white p-2.5 text-left shadow-sm hover:border-indigo-400 hover:bg-indigo-50 transition"
                >
                    <span class="font-bold text-indigo-700">Admin Account</span>
                    <span class="text-gray-700 font-mono text-[11px] mt-0.5">admin@source-x.test</span>
                    <span class="text-gray-500 text-[11px]">Pass: <code class="bg-gray-100 px-1 py-0.5 rounded">password</code></span>
                </button>

                <button
                    type="button"
                    @click="fillDemo('buyer')"
                    class="flex flex-col items-start rounded-md border border-emerald-200 bg-white p-2.5 text-left shadow-sm hover:border-emerald-400 hover:bg-emerald-50 transition"
                >
                    <span class="font-bold text-emerald-700">Buyer Account</span>
                    <span class="text-gray-700 font-mono text-[11px] mt-0.5">buyer@source-x.test</span>
                    <span class="text-gray-500 text-[11px]">Pass: <code class="bg-gray-100 px-1 py-0.5 rounded">password</code></span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600"
                        >Remember me</span
                    >
                </label>
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Forgot your password?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Log in
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
