<?php

namespace App\Support;

use App\Models\User;
use App\Search\SearchSource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Mesin pencarian global. Tidak tahu apa pun tentang model tertentu: semuanya datang dari
 * kelas-kelas SearchSource yang terdaftar di config('admin.search').
 */
final class GlobalSearch
{
    public const MIN_LENGTH = 2;

    /** Batas jumlah kata agar masukan pengguna tidak melipatgandakan klausa query. */
    public const MAX_WORDS = 5;

    /**
     * @return list<array{title: string, items: list<array{title: string, url: string, details: list<array{label: string, value: string}>}>}>
     */
    public static function for(User $actor, string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < self::MIN_LENGTH) {
            return [];
        }

        $words = array_slice(preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, self::MAX_WORDS);

        $groups = [];

        foreach (config('admin.search', []) as $class) {
            /** @var SearchSource $source */
            $source = app($class);

            if (! $source->canSearch($actor)) {
                continue;
            }

            $records = self::match($source->query($actor), $source->attributes(), $words)
                ->limit($source->limit())
                ->get();

            if ($records->isEmpty()) {
                continue;
            }

            $groups[] = [
                'sort' => $source->sort(),
                'title' => $source->label(),
                'items' => $records->map(fn ($record) => [
                    'title' => $source->title($record),
                    'url' => $source->url($record),
                    'details' => collect($source->details($record))
                        ->filter(fn ($value) => filled($value))
                        ->map(fn ($value, $label) => ['label' => (string) $label, 'value' => (string) $value])
                        ->values()
                        ->all(),
                ])->all(),
            ];
        }

        usort($groups, fn (array $a, array $b) => [$a['sort'], $a['title']] <=> [$b['sort'], $b['title']]);

        return array_map(fn (array $g) => ['title' => $g['title'], 'items' => $g['items']], $groups);
    }

    /**
     * Setiap kata harus cocok di setidaknya satu kolom (AND antar kata, OR antar kolom).
     * Nama kolom berasal dari kelas sumber, bukan dari masukan pengguna.
     *
     * @param  list<string>  $attributes
     * @param  list<string>  $words
     */
    private static function match(Builder $query, array $attributes, array $words): Builder
    {
        foreach ($words as $word) {
            // % dan _ yang diketik pengguna dianggap huruf biasa.
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($word)).'%';

            $query->where(function (Builder $q) use ($attributes, $pattern) {
                foreach ($attributes as $attribute) {
                    if (str_contains($attribute, '.')) {
                        $relation = Str::beforeLast($attribute, '.');
                        $column = Str::afterLast($attribute, '.');

                        $q->orWhereHas($relation, fn (Builder $r) => self::like($r, $column, $pattern, false));
                    } else {
                        self::like($q, $attribute, $pattern, true);
                    }
                }
            });
        }

        return $query;
    }

    private static function like(Builder $query, string $column, string $pattern, bool $or): void
    {
        $sql = 'lower('.$query->getQuery()->getGrammar()->wrap($query->getModel()->qualifyColumn($column)).") like ? escape '!'";

        $or ? $query->orWhereRaw($sql, [$pattern]) : $query->whereRaw($sql, [$pattern]);
    }
}
