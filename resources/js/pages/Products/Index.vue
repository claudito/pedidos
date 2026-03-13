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

type ProductItem = {
    id: number;
    code: string;
    description: string;
    quantity: number;
    price: string;
};

defineProps<{ products: ProductItem[] }>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Productos', href: '/productos' }];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    code: '',
    description: '',
    quantity: 0,
    price: 0,
});

function startEdit(product: ProductItem) {
    editingId.value = product.id;
    form.code = product.code;
    form.description = product.description;
    form.quantity = product.quantity;
    form.price = Number(product.price);
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
        form.put(`/productos/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.post('/productos', { onSuccess: resetForm });
}

function destroyProduct(id: number) {
    router.delete(`/productos/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Productos" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Productos"
                    description="Controla catalogo, stock disponible y precio unitario."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo producto</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar producto' : 'Nuevo producto' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="product-code">Codigo</Label>
                                <Input id="product-code" v-model="form.code" />
                                <InputError :message="form.errors.code" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-description">Descripcion</Label>
                                <Input id="product-description" v-model="form.description" />
                                <InputError :message="form.errors.description" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-quantity">Cantidad</Label>
                                <Input id="product-quantity" v-model="form.quantity" type="number" min="0" />
                                <InputError :message="form.errors.quantity" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="product-price">Precio</Label>
                                <Input id="product-price" v-model="form.price" type="number" min="0" step="0.01" />
                                <InputError :message="form.errors.price" />
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
                    <CardTitle>Listado de productos</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Codigo</th>
                                <th class="pb-3">Descripcion</th>
                                <th class="pb-3">Cantidad</th>
                                <th class="pb-3">Precio</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="product in products" :key="product.id" class="border-t">
                                <td class="py-3 font-medium">{{ product.code }}</td>
                                <td class="py-3">{{ product.description }}</td>
                                <td class="py-3">{{ product.quantity }}</td>
                                <td class="py-3">S/ {{ product.price }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(product)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyProduct(product.id)">
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
