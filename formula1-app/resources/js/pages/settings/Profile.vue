<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import CountryFlag from '../../components/CountryFlag.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import axios from 'axios';

// Props existentes
const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    countries: Array,
    drivers: Array,
    constructors: Array,
});

// Usuario actual
const page = usePage();
const user = page.props.auth.user;

// Añade estos console.log para depuración
console.log('Usuario country_id:', user.country_id, typeof user.country_id);
console.log('Países disponibles:', props.countries);

// Avatar preview
const avatarPreview = ref(null);

// Forzar explícitamente la conversión de tipos al inicializar el formulario
const form = useForm({
    name: user.name,
    email: user.email,
    country_id: user.country.country_id || '', // Forzar a número
    favorite_driver_id: user.favorite_driver_id ? parseInt(user.favorite_driver_id) : '',
    favorite_constructor_id: user.favorite_constructor_id ? parseInt(user.favorite_constructor_id) : '',
    avatar: null
});

// Manejar cambio de avatar
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    // Reseteo importante para evitar problemas de estado
    form.avatar = null; // Resetear primero
    form.avatar = file; // Luego asignar nuevo valor
    
    const reader = new FileReader();
    reader.onload = (e) => {
        avatarPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// Eliminar avatar
const removeAvatar = () => {
    form.avatar = 'remove';
    avatarPreview.value = null;
};

// Enviar formulario con datos primitivos para mejor control
const submit = () => {
    // Validación manual
    if (!form.name || !form.email) {
        alert('Please complete all required fields.');
        return;
    }
    
    // Importante: crear un FormData manual para asegurar que todos los campos se envíen correctamente
    const formData = new FormData();
    
    // Método PATCH simulado para Laravel
    formData.append('_method', 'PATCH');
    
    // Campos de texto
    formData.append('name', form.name);
    formData.append('email', form.email);
    // Campos relacionales (asegurarse de que son strings y no vacíos)
    if (form.country_id) {
        formData.append('country_id', form.country_id.toString());
    } else {
        // Enviar explícitamente un valor nulo
        formData.append('country_id', '');
    }
    if (form.favorite_driver_id) {
        formData.append('favorite_driver_id', form.favorite_driver_id.toString());
    }
    if (form.favorite_constructor_id) {
        formData.append('favorite_constructor_id', form.favorite_constructor_id.toString());
    }
    
    // Avatar (archivo o comando para eliminar)
    if (form.avatar === 'remove') {
        formData.append('avatar', 'remove');
    } else if (form.avatar instanceof File) {
        formData.append('avatar', form.avatar);
    }
    
    // Enviar usando POST con _method=PATCH
    axios.post(route('profile.update'), formData)
        .then(response => {
            alert('Profile updated successfully');
            window.location.reload(); // Recargar para mostrar los cambios
        })
        .catch(error => {
            console.error('Validation errors:', error.response?.data);
            alert('Error updating profile');
        });
};

// Confirmar eliminación de cuenta
const deleteUserRef = ref(null); // ✅ Declara la referencia fuera de la función

const confirmDeleteAccount = () => {
    deleteUserRef.value?.open(); // ✅ Usa la referencia correctamente
};

// Modificar la función selectedCountry para ser más tolerante
const selectedCountry = computed(() => {
    if (!form.country_id) return null;
    console.log("Buscando país con ID:", form.country_id, typeof form.country_id);
    
    // Búsqueda más flexible
    const found = props.countries.find(c => {
        const countryId = parseInt(c.country_id);
        const formId = parseInt(form.country_id);
        console.log(`Comparando ${countryId} (${typeof countryId}) con ${formId} (${typeof formId})`);
        return countryId === formId;
    });
    
    console.log("País encontrado:", found);
    return found;
});
</script>

<template>
    <AppLayout>
        <Head title="My Profile" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h1 class="text-2xl font-bold mb-6 dark:text-white">My Profile</h1>

                    <!-- Mensaje de estado -->
                    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                        {{ status }}
                    </div>
                    
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Columna del avatar -->
                            <div class="md:col-span-1 flex flex-col items-center space-y-4">
                                <!-- Avatar actual o preview -->
                                <div class="h-32 w-32 rounded-full overflow-hidden border-4 border-gray-200 dark:border-gray-700 shadow-md">
                                    <img 
                                        v-if="avatarPreview" 
                                        :src="avatarPreview" 
                                        class="h-full w-full object-cover" 
                                        alt="Avatar Preview"
                                    />
                                    <img 
                                        v-else-if="user.avatar" 
                                        :src="`/storage/${user.avatar}`" 
                                        class="h-full w-full object-cover" 
                                        alt="Avatar"
                                    />
                                    <div v-else class="h-full w-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center text-gray-700 dark:text-gray-200 text-4xl">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                </div>
                                
                                <!-- Botones para cambiar o eliminar avatar -->
                                <div class="flex space-x-2">
                                    <label class="cursor-pointer bg-f1-red hover:bg-red-700 text-white py-2 px-4 rounded-md text-sm">
                                        Change Avatar
                                        <input 
                                            type="file" 
                                            class="hidden" 
                                            accept="image/*"
                                            @change="handleFileChange"
                                        />
                                    </label>
                                    
                                    <button 
                                        v-if="user.avatar || avatarPreview"
                                        type="button" 
                                        @click="removeAvatar"
                                        class="py-2 px-4 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm"
                                    >
                                        Remove Avatar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Columna de datos -->
                            <div class="md:col-span-2 space-y-6">
                                <!-- Información básica -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Name
                                        </label>
                                        <input 
                                            id="name" 
                                            v-model="form.name" 
                                            type="text"
                                            name="name"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <InputError :message="form.errors.name" />
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Email
                                        </label>
                                        <input 
                                            id="email" 
                                            v-model="form.email" 
                                            type="email"
                                            name="email"
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                                            required
                                        />
                                        <InputError :message="form.errors.email" />
                                    </div>
                                </div>
                                
                                <!-- País -->
                                <div class="relative">
                                    <select 
                                        id="country" 
                                        v-model="form.country_id" 
                                        class="mt-1 block w-full pl-10 pr-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm"
                                    >
                                        <option value="">Select your country</option>
                                        <option 
                                            v-for="country in countries" 
                                            :key="country.country_id" 
                                            :value="country.country_id"
                                        >
                                            {{ country.name }}
                                        </option>
                                    </select>
                                    <div v-if="selectedCountry" class="absolute left-3 top-1/2 transform -translate-y-1/2">
                                        <CountryFlag 
                                            :country-code="selectedCountry.code" 
                                            size="w-5 h-3"
                                        />
                                    </div>
                                    <InputError :message="form.errors.country_id" />
                                </div>
                                
                                <!-- Preferencias F1 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="driver" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Favorite Driver
                                        </label>
                                        <select 
                                            id="driver" 
                                            v-model="form.favorite_driver_id" 
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                                        >
                                            <option value="">Select your favorite driver</option>
                                            <option 
                                                v-for="driver in drivers" 
                                                :key="driver.driver_id" 
                                                :value="driver.driver_id"
                                            >
                                                {{ driver.first_name }} {{ driver.last_name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.favorite_driver_id" />
                                    </div>
                                    
                                    <div>
                                        <label for="constructor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                            Favorite Team
                                        </label>
                                        <select 
                                            id="constructor" 
                                            v-model="form.favorite_constructor_id" 
                                            class="mt-1 block w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-gray-900 dark:text-gray-100"
                                        >
                                            <option value="">Select your favorite team</option>
                                            <option 
                                                v-for="constructor in constructors" 
                                                :key="constructor.constructor_id" 
                                                :value="constructor.constructor_id"
                                            >
                                                {{ constructor.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.favorite_constructor_id" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Botones de acción -->
                        <div class="flex items-center justify-end mt-6">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-f1-red text-white rounded-md hover:bg-red-700 focus:outline-none transition"
                                :disabled="form.processing"
                            >
                                Save Changes
                            </button>
                        </div>
                    </form>

                    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Change Password</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Ensure your account is using a long, random password to stay secure.
                        </p>
                        <div class="mt-4">
                            <Link :href="route('password.edit')" 
                                class="px-4 py-2 bg-gray-800 dark:bg-gray-600 text-white rounded-md hover:bg-gray-700">
                                Change Password
                            </Link>
                        </div>
                    </div>

                    <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
                        <h2 class="text-xl font-semibold text-red-600 dark:text-red-400">Delete Account</h2>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Permanently delete your account and all associated resources.
                        </p>
                        
                        <div class="mt-6 space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-800/20 dark:bg-red-900/10">
                            <div class="space-y-1 text-red-600 dark:text-red-300">
                                <p class="font-medium">Warning</p>
                                <p class="text-sm">Proceed with caution, this action cannot be undone.</p>
                            </div>
                            <button 
                                @click="confirmDeleteAccount" 
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none transition"
                            >
                                Delete Account
                            </button>
                        </div>
                    </div>

                    <!-- Modal de confirmación -->
                    <DeleteUser ref="deleteUserRef" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.f1-header {
    position: relative;
    padding-bottom: 0.5rem;
}

.f1-header::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    height: 3px;
    width: 60px;
    background: linear-gradient(90deg, #e10600 0%, #ff0000 100%);
}

.f1-button {
    background-color: #e10600;
    transition: all 0.2s ease;
}

.f1-button:hover {
    background-color: #b30500;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>
