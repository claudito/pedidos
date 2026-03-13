<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type ClientOption = { id: number; name: string };
type ProductOption = { id: number; description: string; price: string; quantity: number };
type OrderItem = {
    product_id: number;
    product_name: string;
    quantity: number;
    unit_price: string;
    subtotal: string;
};
type OrderRecord = {
    id: number;
    code: string;
    client_id: number;
    client_name: string;
    order_date: string;
    warehouse_preparation_date: string | null;
    dispatch_date: string | null;
    delivery_date: string | null;
    status: string;
    notes: string | null;
    total: string;
    items: OrderItem[];
};

const props = defineProps<{
    orders: OrderRecord[];
    clients: ClientOption[];
    products: ProductOption[];
    next_order_code: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Pedidos', href: '/pedidos' }];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);

const form = useForm({
    client_id: '',
    order_date: '',
    delivery_date: '',
    notes: '',
    items: [{ product_id: '', quantity: 1 }],
});

const total = computed(() =>
    form.items.reduce((sum, item) => {
        const product = props.products.find((entry) => entry.id === Number(item.product_id));
        return sum + (product ? Number(product.price) * Number(item.quantity) : 0);
    }, 0),
);

function addItem() {
    form.items.push({ product_id: '', quantity: 1 });
}

function removeItem(index: number) {
    form.items.splice(index, 1);
}

function startEdit(order: OrderRecord) {
    editingId.value = order.id;
    form.client_id = String(order.client_id);
    form.order_date = order.order_date;
    form.delivery_date = order.delivery_date ?? '';
    form.notes = order.notes ?? '';
    form.items = order.items.map((item) => ({
        product_id: String(item.product_id),
        quantity: item.quantity,
    }));
    dialogOpen.value = true;
}

function startCreate() {
    editingId.value = null;
    form.reset();
    form.items = [{ product_id: '', quantity: 1 }];
    dialogOpen.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.items = [{ product_id: '', quantity: 1 }];
    dialogOpen.value = false;
}

function submit() {
    const payload = {
        ...form.data(),
        client_id: Number(form.client_id),
        items: form.items.map((item) => ({
            product_id: Number(item.product_id),
            quantity: Number(item.quantity),
        })),
    };

    if (editingId.value) {
        form.transform(() => payload).put(`/pedidos/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.transform(() => payload).post('/pedidos', { onSuccess: resetForm });
}

function destroyOrder(id: number) {
    router.delete(`/pedidos/${id}`);
}

function priceFor(productId: string) {
    return props.products.find((product) => product.id === Number(productId));
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Pedidos" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Pedidos"
                    description="Registra pedidos con cliente, fechas operativas y detalle de productos."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate">Nuevo pedido</Button>
                    </DialogTrigger>
                    <DialogScrollContent class="sm:max-w-3xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar pedido' : 'Nuevo pedido' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="grid gap-2">
                                    <Label>Codigo</Label>
                                    <Input :model-value="editingId ? props.orders.find((order) => order.id === editingId)?.code ?? '' : props.next_order_code" disabled />
                                    <p class="text-xs text-muted-foreground">
                                        {{ editingId ? 'El correlativo del pedido no se puede editar.' : 'El sistema asigna el correlativo automaticamente desde la tabla de series.' }}
                                    </p>
                                </div>
                                <div class="grid gap-2">
                                    <Label for="order-client">Cliente</Label>
                                    <select
                                        id="order-client"
                                        v-model="form.client_id"
                                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    >
                                        <option value="">Seleccione</option>
                                        <option v-for="client in clients" :key="client.id" :value="String(client.id)">
                                            {{ client.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.client_id" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="order-date">Fecha de pedido</Label>
                                    <Input id="order-date" v-model="form.order_date" type="date" />
                                    <InputError :message="form.errors.order_date" />
                                </div>
                                <div class="grid gap-2">
                                    <Label for="order-delivery">Fecha de entrega</Label>
                                    <Input id="order-delivery" v-model="form.delivery_date" type="date" />
                                    <InputError :message="form.errors.delivery_date" />
                                </div>
                                <div class="grid gap-2 md:col-span-2">
                                    <p class="rounded-lg border border-dashed px-3 py-2 text-xs text-muted-foreground">
                                        El estado del pedido, la fecha de preparacion y la fecha de despacho se actualizan desde Seguimientos.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-2">
                                <Label for="order-notes">Notas</Label>
                                <textarea
                                    id="order-notes"
                                    v-model="form.notes"
                                    class="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                />
                            </div>

                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <Label>Detalle del pedido</Label>
                                    <Button type="button" variant="outline" size="sm" @click="addItem">
                                        Agregar item
                                    </Button>
                                </div>

                                <div
                                    v-for="(item, index) in form.items"
                                    :key="index"
                                    class="grid gap-3 rounded-lg border p-3 md:grid-cols-[minmax(0,1fr)_120px_120px_auto]"
                                >
                                    <div class="grid gap-2">
                                        <Label>Producto</Label>
                                        <select
                                            v-model="item.product_id"
                                            class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        >
                                            <option value="">Seleccione</option>
                                            <option
                                                v-for="product in products"
                                                :key="product.id"
                                                :value="String(product.id)"
                                            >
                                                {{ product.description }} (stock: {{ product.quantity }})
                                            </option>
                                        </select>
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Cantidad</Label>
                                        <Input v-model="item.quantity" type="number" min="1" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label>Precio</Label>
                                        <Input :model-value="priceFor(item.product_id)?.price ?? '0.00'" disabled />
                                    </div>
                                    <div class="flex items-end">
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            :disabled="form.items.length === 1"
                                            @click="removeItem(index)"
                                        >
                                            Quitar
                                        </Button>
                                    </div>
                                </div>

                                <InputError :message="form.errors.items" />
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="text-sm text-muted-foreground">
                                    Total estimado: <span class="font-semibold text-foreground">S/ {{ total.toFixed(2) }}</span>
                                </div>
                                <div class="flex gap-2">
                                    <Button variant="outline" @click="resetForm">Cancelar</Button>
                                    <Button :disabled="form.processing" @click="submit">
                                        {{ editingId ? 'Actualizar' : 'Guardar' }}
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </DialogScrollContent>
                </Dialog>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de pedidos</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-for="order in orders"
                        :key="order.id"
                        class="rounded-xl border p-4"
                    >
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="space-y-1">
                                <h3 class="font-semibold">{{ order.code }} - {{ order.client_name }}</h3>
                                <p class="text-sm text-muted-foreground">
                                    Pedido: {{ order.order_date }} | Estado: {{ order.status }} | Total: S/ {{ order.total }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Prep.: {{ order.warehouse_preparation_date || '-' }} | Despacho: {{ order.dispatch_date || '-' }} | Entrega: {{ order.delivery_date || '-' }}
                                </p>
                            </div>

                            <div class="flex gap-2">
                                <Button variant="outline" size="sm" @click="startEdit(order)">
                                    Editar
                                </Button>
                                <Button variant="destructive" size="sm" @click="destroyOrder(order.id)">
                                    Eliminar
                                </Button>
                            </div>
                        </div>

                        <div class="mt-3 text-sm text-muted-foreground">
                            {{ order.items.map((item) => `${item.product_name} x${item.quantity}`).join(', ') }}
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
