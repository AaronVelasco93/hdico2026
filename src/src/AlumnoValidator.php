<?php
declare(strict_types=1);

namespace App;

final class AlumnoValidator
{
    /**
     * Valida y normaliza los datos del formulario.
     *
     * @param array<string, mixed> $input
     * @return array{data: array<string, string>, errors: array<string, string>}
     */
    public static function validate(array $input): array
    {
        // Limpiamos espacios para trabajar con datos consistentes.
        $data = [
            'primer_apellido' => trim((string) ($input['primer_apellido'] ?? '')),
            'segundo_apellido' => trim((string) ($input['segundo_apellido'] ?? '')),
            'nombres' => trim((string) ($input['nombres'] ?? '')),
            'no_cuenta' => preg_replace('/\D+/', '', (string) ($input['no_cuenta'] ?? '')) ?? '',
            'correo' => strtolower(trim((string) ($input['correo'] ?? ''))),
            'contacto' => preg_replace('/\D+/', '', (string) ($input['contacto'] ?? '')) ?? '',
        ];

        $errors = [];

        // Reglas de texto principal.
        if ($data['primer_apellido'] === '') {
            $errors['primer_apellido'] = 'El apellido paterno es obligatorio.';
        } elseif (self::length($data['primer_apellido']) > 255) {
            $errors['primer_apellido'] = 'El apellido paterno no debe exceder 255 caracteres.';
        }

        if (self::length($data['segundo_apellido']) > 255) {
            $errors['segundo_apellido'] = 'El apellido materno no debe exceder 255 caracteres.';
        }

        if ($data['nombres'] === '') {
            $errors['nombres'] = 'El nombre es obligatorio.';
        } elseif (self::length($data['nombres']) > 255) {
            $errors['nombres'] = 'El nombre no debe exceder 255 caracteres.';
        }

        // Reglas numericas.
        if (!preg_match('/^\d{8,9}$/', $data['no_cuenta'])) {
            $errors['no_cuenta'] = 'El numero de cuenta debe tener entre 8 y 9 digitos.';
        }

        if (!preg_match('/^\d{10}$/', $data['contacto'])) {
            $errors['contacto'] = 'El contacto debe tener exactamente 10 digitos.';
        }

        // Reglas de correo.
        if ($data['correo'] === '') {
            $errors['correo'] = 'El correo es obligatorio.';
        } elseif (!filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors['correo'] = 'El correo no tiene un formato valido.';
        } elseif (self::length($data['correo']) > 255) {
            $errors['correo'] = 'El correo no debe exceder 255 caracteres.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    /**
     * Calcula longitud segura usando mbstring cuando este disponible.
     */
    private static function length(string $value): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($value);
        }

        return strlen($value);
    }
}
