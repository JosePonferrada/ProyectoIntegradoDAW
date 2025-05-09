<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, defineExpose } from 'vue';

// Components
import InputError from '@/components/InputError.vue';

const passwordInput = ref<HTMLInputElement | null>(null);
const isOpen = ref(false);

const form = useForm({
    password: '',
});

const deleteUser = (e: Event) => {
    e.preventDefault();

    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => close(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const close = () => {
    form.clearErrors();
    form.reset();
    isOpen.value = false;
};

const open = () => {
    isOpen.value = true;
    setTimeout(() => {
        passwordInput.value?.focus();
    }, 100);
};

defineExpose({
    open,
    close
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 max-w-md w-full">
            <form @submit="deleteUser" class="space-y-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        ¿Estás seguro de que quieres eliminar tu cuenta?
                    </h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Una vez que tu cuenta sea eliminada, todos sus recursos y datos se borrarán permanentemente.
                        Introduce tu contraseña para confirmar.
                    </p>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Contraseña
                    </label>
                    <input 
                        id="password" 
                        ref="passwordInput"
                        v-model="form.password" 
                        type="password" 
                        class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                        required
                        autocomplete="current-password"
                    />
                    <InputError :message="form.errors.password" />
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button" 
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600"
                        @click="close"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                        :disabled="form.processing"
                    >
                        Eliminar cuenta
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
