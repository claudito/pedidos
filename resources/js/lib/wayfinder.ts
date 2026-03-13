import type { RouteDefinition } from '@/wayfinder';

type FormMethod = 'get' | 'post';

type FormBinding = {
    action: string;
    method: FormMethod;
};

export function toForm(route: RouteDefinition<'get' | 'post' | 'put' | 'patch' | 'delete' | 'head'>): FormBinding {
    if (route.method === 'get') {
        return {
            action: route.url,
            method: 'get',
        };
    }

    if (route.method === 'post') {
        return {
            action: route.url,
            method: 'post',
        };
    }

    const separator = route.url.includes('?') ? '&' : '?';

    return {
        action: `${route.url}${separator}_method=${route.method.toUpperCase()}`,
        method: 'post',
    };
}
