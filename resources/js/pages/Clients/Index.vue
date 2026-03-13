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

type ClientItem = {
    id: number;
    code: string;
    name: string;
    document_number: string | null;
    email: string | null;
    phone: string | null;
    address: string | null;
};

defineProps<{ clients: ClientItem[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Clientes', href: '/clientes' }];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    code: '',
    name: '',
    document_number: '',
    email: '',
    phone: '',
    address: '',
});

function startEdit(client: ClientItem) {
    editingId.value = client.id;
    form.code = client.code;
    form.name = client.name;
    form.document_number = client.document_number ?? '';
    form.email = client.email ?? '';
    form.phone = client.phone ?? '';
    form.address = client.address ?? '';
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
        form.put(`/clientes/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/clientes', { onSuccess: resetForm });
}

function destroyClient(id: number) {
    router.delete(`/clientes/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Clientes" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Clientes"
                    description="Administra la cartera de clientes usada por los pedidos."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo cliente</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar cliente' : 'Nuevo cliente' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="client-code">Codigo</Label>
                                <Input id="client-code" v-model="form.code" />
                                <InputError :message="form.errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="client-name">Nombre</Label>
                                <Input id="client-name" v-model="form.name" />
                                <InputError :message="form.errors.name" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="client-document">Documento</Label>
                                <Input id="client-document" v-model="form.document_number" />
                                <InputError :message="form.errors.document_number" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="client-email">Correo</Label>
                                <Input id="client-email" v-model="form.email" type="email" />
                                <InputError :message="form.errors.email" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="client-phone">Telefono</Label>
                                <Input id="client-phone" v-model="form.phone" />
                                <InputError :message="form.errors.phone" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="client-address">Direccion</Label>
                                <Input id="client-address" v-model="form.address" />
                                <InputError :message="form.errors.address" />
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
                    <CardTitle>Listado de clientes</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Codigo</th>
                                <th class="pb-3">Nombre</th>
                                <th class="pb-3">Contacto</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="client in clients" :key="client.id" class="border-t">
                                <td class="py-3 font-medium">{{ client.code }}</td>
                                <td class="py-3">{{ client.name }}</td>
                                <td class="py-3">{{ client.phone || client.email || '-' }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(client)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyClient(client.id)">
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
