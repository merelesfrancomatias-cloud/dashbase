<?php
namespace App;

/**
 * Validator — Validación centralizada de datos de entrada.
 *
 * Uso en un Controller:
 *   $v = new Validator($data);
 *   $v->required('nombre')
 *     ->required('precio_venta')
 *     ->numeric('precio_venta', min: 0)
 *     ->numeric('stock', min: 0)
 *     ->maxLength('nombre', 255)
 *     ->email('email')
 *     ->date('fecha_gasto');
 *
 *   if ($v->fails()) {
 *       Response::error($v->firstError(), 400);
 *   }
 */
class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    // -------------------------------------------------------
    // Reglas
    // -------------------------------------------------------

    public function required(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || $this->data[$field] === '' || $this->data[$field] === null) {
            $this->errors[] = "El campo '$label' es requerido.";
        }
        return $this;
    }

    public function numeric(string $field, float $min = null, float $max = null, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            return $this;  // no validar si vacío (usar required() para eso)
        }
        if (!is_numeric($this->data[$field])) {
            $this->errors[] = "El campo '$label' debe ser numérico.";
            return $this;
        }
        $val = (float)$this->data[$field];
        if ($min !== null && $val < $min) {
            $this->errors[] = "El campo '$label' debe ser mayor o igual a $min.";
        }
        if ($max !== null && $val > $max) {
            $this->errors[] = "El campo '$label' debe ser menor o igual a $max.";
        }
        return $this;
    }

    public function integer(string $field, int $min = null, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!isset($this->data[$field]) || $this->data[$field] === '') {
            return $this;
        }
        if (!ctype_digit((string)$this->data[$field])) {
            $this->errors[] = "El campo '$label' debe ser un número entero.";
            return $this;
        }
        if ($min !== null && (int)$this->data[$field] < $min) {
            $this->errors[] = "El campo '$label' debe ser mayor o igual a $min.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): self
    {
        $label = $label ?: $field;
        if (isset($this->data[$field]) && mb_strlen((string)$this->data[$field]) > $max) {
            $this->errors[] = "El campo '$label' no puede superar $max caracteres.";
        }
        return $this;
    }

    public function email(string $field, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "El campo '$label' debe ser un email válido.";
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d', string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field])) {
            $d = \DateTime::createFromFormat($format, $this->data[$field]);
            if (!$d || $d->format($format) !== $this->data[$field]) {
                $this->errors[] = "El campo '$label' debe ser una fecha válida ($format).";
            }
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $label = ''): self
    {
        $label = $label ?: $field;
        if (!empty($this->data[$field]) && !in_array($this->data[$field], $allowed, true)) {
            $list = implode(', ', $allowed);
            $this->errors[] = "El campo '$label' debe ser uno de: $list.";
        }
        return $this;
    }

    // -------------------------------------------------------
    // Resultado
    // -------------------------------------------------------

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    /** Primer error (para Response::error) */
    public function firstError(): string
    {
        return $this->errors[0] ?? 'Datos inválidos.';
    }

    /** Todos los errores */
    public function errors(): array
    {
        return $this->errors;
    }
}
