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

type PermissionOption = { id: number; name: string };
type RoleItem = { id: number; name: string; permissions: string[] };

const props = defineProps<{
    roles: RoleItem[];
    permissions: PermissionOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Seguridad / Roles', href: '/seguridad/roles' },
];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    name: '',
    permissions: [] as number[],
});

function startEdit(role: RoleItem) {
    editingId.value = role.id;
    form.name = role.name;
    form.permissions = props.permissions
        .filter((permission) => role.permissions.includes(permission.name))
        .map((permission) => permission.id);
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

function submit() {
    if (editingId.value) {
        form.put(`/seguridad/roles/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/seguridad/roles', { onSuccess: resetForm });
}

function togglePermission(permissionId: number, checked: boolean) {
    form.permissions = checked
        ? [...form.permissions, permissionId]
        : form.permissions.filter((id) => id !== permissionId);
}

function destroyRole(id: number) {
    router.delete(`/seguridad/roles/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Roles" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Roles"
                    description="Administra perfiles de acceso y sus permisos."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo rol</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar rol' : 'Nuevo rol' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="role-name">Nombre</Label>
                                <Input id="role-name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>

                            <div class="grid max-h-80 gap-3 overflow-y-auto">
                                <Label>Permisos</Label>
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.id"
                                    class="flex items-center gap-2 text-sm"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="form.permissions.includes(permission.id)"
                                        @change="togglePermission(permission.id, ($event.target as HTMLInputElement).checked)"
                                    />
                                    <span>{{ permission.name }}</span>
                                </label>
                                <InputError :message="form.errors.permissions" />
                            </div>

                            <div class="flex justify-end gap-2">
                                <Button variant="outline" @click="resetForm">
                                    Cancelar
                                </Button>
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
                    <CardTitle>Listado de roles</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Rol</th>
                                <th class="pb-3">Permisos</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="role in roles" :key="role.id" class="border-t">
                                <td class="py-3 font-medium">{{ role.name }}</td>
                                <td class="py-3">{{ role.permissions.join(', ') || 'Sin permisos' }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(role)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyRole(role.id)">
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
