<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogScrollContent,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

type OrderOption = { id: number; code: string };
type StatusOption = { id: number; code: string; name: string };
type TrackingItem = {
    id: number;
    order_id: number;
    order_code: string;
    order_status_id: number | null;
    status: string;
    description: string | null;
    tracked_at: string;
    tracked_at_label: string;
};
type TrackingOrder = {
    order_id: number;
    order_code: string;
    client_name: string | null;
    current_status: string;
    current_description: string | null;
    last_tracked_at: string | null;
    tracking_count: number;
    history: TrackingItem[];
};

const props = defineProps<{
    tracking_orders: TrackingOrder[];
    orders: OrderOption[];
    statuses: StatusOption[];
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Seguimientos', href: '/seguimientos' }];

const editingId = ref<number | null>(null);
const dialogOpen = ref(false);
const historyOpen = ref(false);
const selectedOrder = ref<TrackingOrder | null>(null);

const form = useForm({
    order_id: '',
    order_status_id: '',
    description: '',
    tracked_at: '',
});

const selectedOrderCode = computed(() => {
    if (editingId.value && selectedOrder.value) {
        return selectedOrder.value.order_code;
    }

    return props.orders.find((order) => order.id === Number(form.order_id))?.code ?? '-';
});

function openHistory(order: TrackingOrder) {
    selectedOrder.value = order;
    historyOpen.value = true;
}

function closeHistory() {
    selectedOrder.value = null;
    historyOpen.value = false;
}

function startEdit(tracking: TrackingItem) {
    historyOpen.value = false;
    selectedOrder.value = null;
    editingId.value = tracking.id;
    form.order_id = String(tracking.order_id);
    form.order_status_id = tracking.order_status_id ? String(tracking.order_status_id) : '';
    form.description = tracking.description ?? '';
    form.tracked_at = tracking.tracked_at;
    dialogOpen.value = true;
}

function startCreate(orderId?: number) {
    historyOpen.value = false;
    selectedOrder.value = null;
    editingId.value = null;
    form.reset();
    form.order_id = orderId ? String(orderId) : '';
    dialogOpen.value = true;
}

function resetForm() {
    editingId.value = null;
    form.reset();
    dialogOpen.value = false;
}

function submit() {
    const payload = {
        ...form.data(),
        order_id: Number(form.order_id),
        order_status_id: Number(form.order_status_id),
    };

    if (editingId.value) {
        form.transform(() => payload).put(`/seguimientos/${editingId.value}`, { onSuccess: resetForm });
        return;
    }

    form.transform(() => payload).post('/seguimientos', { onSuccess: resetForm });
}

function destroyTracking(id: number) {
    router.delete(`/seguimientos/${id}`);
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Seguimientos" />

        <div class="space-y-6 p-4">
            <div class="flex items-start justify-between gap-4">
                <Heading
                    title="Seguimientos"
                    description="Administra el historial de estados de cada pedido."
                />

                <Dialog v-model:open="dialogOpen">
                    <DialogTrigger as-child>
                        <Button @click="startCreate()">Nuevo seguimiento</Button>
                    </DialogTrigger>
                    <DialogContent class="sm:max-w-xl">
                        <DialogHeader>
                            <DialogTitle>
                                {{ editingId ? 'Editar seguimiento' : 'Nuevo seguimiento' }}
                            </DialogTitle>
                        </DialogHeader>

                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="tracking-order">Pedido</Label>
                                <select
                                    id="tracking-order"
                                    v-model="form.order_id"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="">Seleccione</option>
                                    <option v-for="order in orders" :key="order.id" :value="String(order.id)">
                                        {{ order.code }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.order_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="tracking-status">Estado</Label>
                                <select
                                    id="tracking-status"
                                    v-model="form.order_status_id"
                                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                                >
                                    <option value="">Seleccione</option>
                                    <option v-for="status in statuses" :key="status.id" :value="String(status.id)">
                                        {{ status.name }}
                                    </option>
                                </select>
                                <p class="text-xs text-muted-foreground">
                                    Los estados de preparacion y despacho actualizan automaticamente las fechas del pedido.
                                </p>
                                <InputError :message="form.errors.order_status_id" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="tracking-date">Fecha y hora</Label>
                                <Input id="tracking-date" v-model="form.tracked_at" type="datetime-local" />
                                <InputError :message="form.errors.tracked_at" />
                            </div>
                            <div class="grid gap-2">
                                <Label for="tracking-description">Descripcion</Label>
                                <textarea
                                    id="tracking-description"
                                    v-model="form.description"
                                    class="min-h-24 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                />
                                <InputError :message="form.errors.description" />
                            </div>

                            <div class="rounded-lg border border-dashed px-3 py-2 text-xs text-muted-foreground">
                                Pedido seleccionado: {{ selectedOrderCode }}
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
                    <CardTitle>Listado de pedidos con seguimiento</CardTitle>
                </CardHeader>
                <CardContent class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-muted-foreground">
                            <tr>
                                <th class="pb-3">Pedido</th>
                                <th class="pb-3">Cliente</th>
                                <th class="pb-3">Estado actual</th>
                                <th class="pb-3">Ultimo movimiento</th>
                                <th class="pb-3">Eventos</th>
                                <th class="pb-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="order in tracking_orders" :key="order.order_id" class="border-t align-top">
                                <td class="py-3 font-medium">{{ order.order_code }}</td>
                                <td class="py-3">{{ order.client_name || '-' }}</td>
                                <td class="py-3">
                                    <div class="font-medium">{{ order.current_status }}</div>
                                    <div class="text-xs text-muted-foreground">
                                        {{ order.current_description || '-' }}
                                    </div>
                                </td>
                                <td class="py-3">{{ order.last_tracked_at || '-' }}</td>
                                <td class="py-3">{{ order.tracking_count }}</td>
                                <td class="py-3">
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" size="sm" @click="openHistory(order)">
                                            Ver historial
                                        </Button>
                                        <Button size="sm" @click="startCreate(order.order_id)">
                                            Agregar
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </CardContent>
            </Card>

            <Dialog :open="historyOpen" @update:open="(value) => !value && closeHistory()">
                <DialogScrollContent class="sm:max-w-3xl">
                    <DialogHeader>
                        <DialogTitle>
                            Historial de seguimiento {{ selectedOrder?.order_code ? `- ${selectedOrder.order_code}` : '' }}
                        </DialogTitle>
                    </DialogHeader>

                    <div class="space-y-4">
                        <div v-if="selectedOrder" class="rounded-lg border bg-muted/30 p-4 text-sm">
                            <div class="flex flex-wrap gap-x-6 gap-y-2">
                                <span><strong>Cliente:</strong> {{ selectedOrder.client_name || '-' }}</span>
                                <span><strong>Estado actual:</strong> {{ selectedOrder.current_status }}</span>
                                <span><strong>Eventos:</strong> {{ selectedOrder.tracking_count }}</span>
                            </div>
                            <p class="mt-2 text-muted-foreground">
                                {{ selectedOrder.current_description || '-' }}
                            </p>
                        </div>

                        <div v-if="selectedOrder?.history.length" class="space-y-3">
                            <div
                                v-for="tracking in selectedOrder.history"
                                :key="tracking.id"
                                class="rounded-xl border p-4"
                            >
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="space-y-1">
                                        <h3 class="font-semibold">{{ tracking.status }}</h3>
                                        <p class="text-sm text-muted-foreground">{{ tracking.tracked_at_label }}</p>
                                        <p class="text-sm text-muted-foreground">{{ tracking.description || '-' }}</p>
                                    </div>

                                    <div class="flex gap-2">
                                        <Button variant="outline" size="sm" @click="startEdit(tracking)">
                                            Editar
                                        </Button>
                                        <Button variant="destructive" size="sm" @click="destroyTracking(tracking.id)">
                                            Eliminar
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <p v-else class="text-sm text-muted-foreground">
                            Este pedido aun no tiene historial de seguimiento.
                        </p>

                        <div class="flex justify-end gap-2">
                            <Button variant="outline" @click="closeHistory">Cerrar</Button>
                            <Button v-if="selectedOrder" @click="startCreate(selectedOrder.order_id)">
                                Nuevo evento
                            </Button>
                        </div>
                    </div>
                </DialogScrollContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
