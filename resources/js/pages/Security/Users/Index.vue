<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type RoleOption = { id: number; name: string };
type UserItem = { id: number; name: string; email: string; roles: string[] };

const props = defineProps<{
    users: UserItem[];
    roles: RoleOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Seguridad / Usuarios', href: '/seguridad/usuarios' },
];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    roles: [] as number[],
});

function startEdit(user: UserItem) {
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.roles = props.roles
        .filter((role) => user.roles.includes(role.name))
        .map((role) => role.id);
    dialogOpen.value = true;
}

function startCreate() {
    editingId.value = null;
    form.reset();
    dialogOpen.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    dialogOpen.value = false;
}

function toggleRole(roleId: number, checked: boolean) {
    form.roles = checked
        ? [...form.roles, roleId]
        : form.roles.filter((id) => id !== roleId);
}

function submit() {
    if (editingId.value) {
        form.put(`/seguridad/usuarios/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/seguridad/usuarios', { onSuccess: resetForm });
}

function destroyUser(id: number) {
    router.delete(`/seguridad/usuarios/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Usuarios" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Usuarios"
                    description="Crea usuarios del sistema y asignales roles operativos."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo usuario</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar usuario' : 'Nuevo usuario' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="user-name">Nombre</Label>
                                <Input id="user-name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="user-email">Correo</Label>
                                <Input id="user-email" v-model="form.email" type="email" />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="user-password">
                                    {{ editingId ? 'Nueva clave (opcional)' : 'Clave' }}
                                </Label>
                                <Input id="user-password" v-model="form.password" type="password" />
                                <InputError :message="form.errors.password" />
                            </div>

                            <div class="grid max-h-80 gap-3 overflow-y-auto">
                                <Label>Roles</Label>
                                <label
                                    v-for="role in roles"
                                    :key="role.id"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="form.roles.includes(role.id)"
                                        @change="toggleRole(role.id, ($event.target as HTMLInputElement).checked)"
                                    />
                                    <span>{{ role.name }}</span>
                                </label>
                                <InputError :message="form.errors.roles" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <Button variant="outline" @click="resetForm">Cancelar</Button>
                                <Button :disabled="form.processing" @click="submit">
                                    {{ editingId ? 'Actualizar' : 'Guardar' }}
                                </Button>
                            </div>
                        </div>
                    </DialogContent>
                </Dialog>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de usuarios</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Nombre</th>
                                <th class="pb-3">Correo</th>
                                <th class="pb-3">Roles</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users" :key="user.id" class="border-t">
                                <td class="py-3 font-medium">{{ user.name }}</td>
                                <td class="py-3">{{ user.email }}</td>
                                <td class="py-3">{{ user.roles.join(', ') || 'Sin roles' }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(user)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyUser(user.id)">
                                            Eliminar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
