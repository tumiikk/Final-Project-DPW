<?php

namespace App\Core;

/**
 * Validasi input (pengganti Validator/FormRequest Laravel).
 *
 * Mendukung aturan yang dipakai project ini dengan perilaku yang sama seperti Laravel:
 * required, required_if, accepted, nullable, string, array, boolean, integer, numeric,
 * max, min, between, in, regex, date_format, timezone, required_array_keys,
 * serta aturan kustom berupa fungsi: fn (string $atribut, mixed $nilai, callable $gagal).
 *
 * Nama field bertingkat memakai titik dan wildcard, contoh "data.items.*.photo.path".
 * Pesan error bawaan memakai bahasa Inggris seperti Laravel (pesan kustom berbahasa Indonesia
 * dikirim lewat parameter $messages, contoh ['attendance.required' => 'Pilih konfirmasi kehadiran.']).
 */
final class Validator
{
    private const IMPLICIT = ['required', 'required_if', 'accepted'];

    /** @var array<string, array> aturan setelah wildcard dijabarkan */
    private array $rules = [];

    /** @var array<string, bool> field hasil penjabaran wildcard (namanya ditampilkan apa adanya di pesan) */
    private array $fromWildcard = [];

    /** @var array<string, string[]> */
    private array $errors = [];

    private bool $ran = false;

    public function __construct(private array $data, array $rules, private array $messages = [])
    {
        $this->rules = $this->expandRules($rules);
    }

    /** Validasi lalu kembalikan data tervalidasi, atau lempar ValidationException bila gagal. */
    public static function validate(array $data, array $rules, array $messages = []): array
    {
        $v = new self($data, $rules, $messages);
        if ($v->fails()) {
            throw new ValidationException($v->errors());
        }

        return $v->validated();
    }

    public function fails(): bool
    {
        $this->run();

        return $this->errors !== [];
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        $this->run();

        return $this->errors;
    }

    /** Menambah error manual (dipakai pemeriksaan lanjutan setelah aturan dasar lolos). */
    public function addError(string $attribute, string $message): void
    {
        $this->errors[$attribute][] = $message;
    }

    /**
     * Hanya data yang tercantum di aturan yang dikembalikan (field lain dibuang),
     * sama seperti $request->validated() Laravel. Ini mencegah mass assignment.
     */
    public function validated(): array
    {
        $result = [];

        foreach ($this->rules as $key => $rules) {
            [$exists, $value] = $this->get($key);

            // Array yang punya aturan untuk anak-anaknya tidak disalin utuh: anaknya disalin satu per satu.
            if (in_array('array', $rules, true) && $exists && $value !== null && $this->hasChildRules($key)) {
                continue;
            }
            if ($exists) {
                data_set($result, $key, $value);
            }
        }

        return $this->sortLists($result);
    }

    // ------------------------------------------------------------------
    // Proses validasi
    // ------------------------------------------------------------------

    private function run(): void
    {
        if ($this->ran) {
            return;
        }
        $this->ran = true;

        foreach ($this->rules as $attribute => $rules) {
            [$exists, $value] = $this->get($attribute);
            $nullable = in_array('nullable', $rules, true);

            foreach ($rules as $rule) {
                if ($rule === 'nullable') {
                    continue;
                }

                $name = is_string($rule) ? explode(':', $rule, 2)[0] : 'custom';
                $implicit = in_array($name, self::IMPLICIT, true);

                // Aturan biasa dilewati bila field tidak dikirim, berupa teks kosong, atau null & nullable
                if (! $implicit && (! $exists || (is_string($value) && trim($value) === '') || ($nullable && $value === null))) {
                    continue;
                }

                $message = $this->check($attribute, $value, $rule, $rules);
                if ($message !== null) {
                    $this->errors[$attribute][] = $message;
                    break; // cukup satu pesan per field
                }
            }
        }
    }

    /** Memeriksa satu aturan. Mengembalikan pesan error, atau null bila lolos. */
    private function check(string $attr, mixed $value, mixed $rule, array $all): ?string
    {
        // Aturan kustom berupa fungsi
        if (is_callable($rule) && ! is_string($rule)) {
            $failed = null;
            $rule($attr, $value, function (string $msg) use (&$failed) { $failed = $msg; });

            return $failed;
        }

        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);
        $params = ($name === 'regex' || $param === null) ? [$param] : explode(',', $param);
        $numeric = in_array('numeric', $all, true) || in_array('integer', $all, true);

        $ok = match ($name) {
            'required' => filled($value),
            'required_if' => (string) data_get($this->data, $params[0]) !== (string) $params[1] || filled($value),
            'accepted' => in_array($value, ['yes', 'on', '1', 1, true, 'true'], true),
            'string' => is_string($value),
            'array' => is_array($value),
            'boolean' => in_array($value, [true, false, 0, 1, '0', '1'], true),
            'integer' => ! is_bool($value) && filter_var($value, FILTER_VALIDATE_INT) !== false,
            'numeric' => is_numeric($value),
            'max' => $this->size($value, $numeric) <= (float) $params[0],
            'min' => $this->size($value, $numeric) >= (float) $params[0],
            'between' => ($s = $this->size($value, $numeric)) >= (float) $params[0] && $s <= (float) $params[1],
            'in' => is_scalar($value) && in_array((string) $value, $params, true),
            'regex' => is_string($value) || is_numeric($value) ? preg_match($params[0], (string) $value) === 1 : false,
            'date_format' => is_string($value) && ($d = \DateTime::createFromFormat('!'.$params[0], $value)) && $d->format($params[0]) === $value,
            'timezone' => is_string($value) && in_array($value, \DateTimeZone::listIdentifiers(\DateTimeZone::ALL), true),
            'required_array_keys' => is_array($value) && count(array_diff($params, array_keys($value))) === 0,
            default => throw new \InvalidArgumentException("Aturan validasi tidak dikenal: $name"),
        };

        return $ok ? null : $this->message($attr, $name, $params, $value, $numeric);
    }

    /** Ukuran nilai: angka (bila aturan numeric/integer), jumlah item (array), atau panjang teks. */
    private function size(mixed $value, bool $numeric): float
    {
        if ($numeric && is_numeric($value)) {
            return (float) $value;
        }
        if (is_array($value)) {
            return count($value);
        }

        return mb_strlen((string) $value);
    }

    /** Menyusun pesan error (pesan kustom lebih dulu, lalu pesan bawaan gaya Laravel). */
    private function message(string $attr, string $rule, array $params, mixed $value, bool $numeric): string
    {
        $plain = preg_replace('/\.\d+(?=\.|$)/', '.*', $attr);
        foreach (["$attr.$rule", "$plain.$rule"] as $k) {
            if (isset($this->messages[$k])) {
                return $this->messages[$k];
            }
        }

        $type = ($numeric && is_numeric($value)) ? 'numeric' : (is_array($value) ? 'array' : 'string');
        $templates = [
            'required' => 'The :attribute field is required.',
            'required_if' => 'The :attribute field is required when :other is :value.',
            'accepted' => 'The :attribute field must be accepted.',
            'string' => 'The :attribute field must be a string.',
            'array' => 'The :attribute field must be an array.',
            'boolean' => 'The :attribute field must be true or false.',
            'integer' => 'The :attribute field must be an integer.',
            'numeric' => 'The :attribute field must be a number.',
            'max' => ['numeric' => 'The :attribute field must not be greater than :max.', 'array' => 'The :attribute field must not have more than :max items.', 'string' => 'The :attribute field must not be greater than :max characters.'],
            'min' => ['numeric' => 'The :attribute field must be at least :min.', 'array' => 'The :attribute field must have at least :min items.', 'string' => 'The :attribute field must be at least :min characters.'],
            'between' => ['numeric' => 'The :attribute field must be between :min and :max.', 'array' => 'The :attribute field must have between :min and :max items.', 'string' => 'The :attribute field must be between :min and :max characters.'],
            'in' => 'The selected :attribute is invalid.',
            'regex' => 'The :attribute field format is invalid.',
            'date_format' => 'The :attribute field must match the format :format.',
            'timezone' => 'The :attribute field must be a valid timezone.',
            'required_array_keys' => 'The :attribute field must contain entries for: :values.',
        ];

        $tpl = $templates[$rule];
        $tpl = is_array($tpl) ? $tpl[$type] : $tpl;
        // Sama seperti Laravel: field biasa "full_name" ditulis "full name", field hasil wildcard ditulis apa adanya
        $display = fn (string $a) => isset($this->fromWildcard[$a]) ? $a : str_replace('_', ' ', $a);

        return strtr($tpl, [
            ':attribute' => $display($attr),
            ':max' => $rule === 'between' ? ($params[1] ?? '') : ($params[0] ?? ''),
            ':min' => $params[0] ?? '',
            ':format' => $params[0] ?? '',
            ':values' => implode(', ', $params),
            ':other' => $display($params[0] ?? ''),
            ':value' => $params[1] ?? '',
        ]);
    }

    // ------------------------------------------------------------------
    // Utilitas data & wildcard
    // ------------------------------------------------------------------

    /** @return array{0: bool, 1: mixed} [ada/tidaknya key, nilainya] */
    private function get(string $key): array
    {
        $node = $this->data;
        foreach (explode('.', $key) as $seg) {
            if (! is_array($node) || ! array_key_exists($seg, $node)) {
                return [false, null];
            }
            $node = $node[$seg];
        }

        return [true, $node];
    }

    /**
     * Menjabarkan aturan wildcard sesuai data yang dikirim, contoh "items.*.year" menjadi
     * "items.0.year", "items.1.year", dst. Aturan tanpa wildcard tetap di urutan semula,
     * hasil penjabaran ditambahkan di belakang (sama seperti Laravel).
     */
    private function expandRules(array $rules): array
    {
        $out = [];
        $wild = [];
        foreach ($rules as $key => $r) {
            if (str_contains($key, '*')) {
                $wild[$key] = $r;
            } else {
                $out[$key] = $r;
            }
        }

        foreach ($wild as $pattern => $r) {
            foreach ($this->expandKey(explode('.', $pattern), $this->data, []) as $concrete) {
                $out[$concrete] = isset($out[$concrete]) ? array_merge($out[$concrete], $r) : $r;
                $this->fromWildcard[$concrete] = true;
            }
        }

        return $out;
    }

    /** Mencari semua path konkret untuk pola wildcard berdasarkan isi data. */
    private function expandKey(array $segments, mixed $node, array $prefix): array
    {
        if ($segments === []) {
            return [implode('.', $prefix)];
        }

        $seg = array_shift($segments);
        if ($seg === '*') {
            if (! is_array($node)) {
                return [];
            }
            $found = [];
            foreach ($node as $k => $child) {
                array_push($found, ...$this->expandKey($segments, $child, [...$prefix, (string) $k]));
            }

            return $found;
        }

        $child = is_array($node) && array_key_exists($seg, $node) ? $node[$seg] : null;

        return $this->expandKey($segments, $child, [...$prefix, $seg]);
    }

    private function hasChildRules(string $key): bool
    {
        foreach (array_keys($this->rules) as $k) {
            if (str_starts_with($k, $key.'.')) {
                return true;
            }
        }

        return false;
    }

    /** Array berkunci angka diurutkan kembali agar tetap menjadi daftar (list) yang benar. */
    private function sortLists(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = $this->sortLists($v);
            }
        }
        if ($data !== [] && count(array_filter(array_keys($data), 'is_int')) === count($data)) {
            ksort($data);
        }

        return $data;
    }
}
