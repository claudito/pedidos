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

type PermissionItem = { id: number; name: string; roles: string[] };

defineProps<{ permissions: PermissionItem[] }>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Seguridad / Permisos', href: '/seguridad/permisos' },
];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    name: '',
});

function startEdit(permission: PermissionItem) {
    editingId.value = permission.id;
    form.name = permission.name;
    dialogOpen.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    dialogOpen.value = false;
}

function startCreate() {
    editingId.value = null;
    form.reset();
    dialogOpen.value = true;
}

function submit() {
    if (editingId.value) {
        form.put(`/seguridad/permisos/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/seguridad/permisos', { onSuccess: resetForm });
}

function destroyPermission(id: number) {
    router.delete(`/seguridad/permisos/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Permisos" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Permisos"
                    description="Define los accesos que podran asignarse a cada rol."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo permiso</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar permiso' : 'Nuevo permiso' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="permission-name">Nombre</Label>
                                <Input id="permission-name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
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
                    <CardTitle>Listado de permisos</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Permiso</th>
                                <th class="pb-3">Roles</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="permission in permissions" :key="permission.id" class="border-t">
                                <td class="py-3 font-medium">{{ permission.name }}</td>
                                <td class="py-3">{{ permission.roles.join(', ') || 'Sin roles' }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(permission)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyPermission(permission.id)">
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
