<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class Navigation
{
    /** @return list<array{title: string, items: list<array<string, mixed>>}> */
    public static function for(?User $user): array
    {
        $groups = [];

        foreach (config('admin.nav', []) as $group) {
            $items = array_values(array_filter(
                array_map(fn (array $item) => self::resolve($item, $user), $group['items'] ?? []),
            ));

            if ($items !== []) {
                $groups[] = ['title' => $group['title'], 'items' => $items];
            }
        }

        return $groups;
    }

    private static function resolve(array $item, ?User $user): ?array
    {
        if (isset($item['when']) && ! config($item['when'])) {
            return null;
        }

        if (isset($item['can']) && ! $user?->can($item['can'])) {
            return null;
        }

        if (isset($item['children'])) {
            $children = array_values(array_filter(
                array_map(fn (array $child) => self::resolve($child, $user), $item['children']),
            ));

            return $children === [] ? null : [
                'label' => $item['label'],
                'icon' => $item['icon'] ?? null,
                'badge' => $item['badge'] ?? null,
                'href' => null,
                'active' => collect($children)->contains('active', true),
                'children' => $children,
            ];
        }

        $route = $item['route'] ?? null;

        if (! $route || ! Route::has($route)) {
            return null;
        }

        return [
            'label' => $item['label'],
            'icon' => $item['icon'] ?? null,
            'badge' => $item['badge'] ?? null,
            'href' => route($route),
            'active' => request()->routeIs(...Arr::wrap($item['match'] ?? [$route, $route . '.*'])),
            'children' => [],
        ];
    }
}