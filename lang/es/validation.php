<?php

/*
|--------------------------------------------------------------------------
| Validation Language Lines (es)
|--------------------------------------------------------------------------
|
| Only the rules used by this application are translated; any other key
| falls back to lang/en/validation.php.
|
*/

return [
    'after' => 'El campo :attribute debe ser una fecha posterior a :date.',
    'after_or_equal' => 'El campo :attribute debe ser una fecha igual o posterior a :date.',
    'array' => 'El campo :attribute debe ser una lista.',
    'before_or_equal' => 'El campo :attribute debe ser una fecha igual o anterior a :date.',
    'boolean' => 'El campo :attribute debe ser verdadero o falso.',
    'date' => 'El campo :attribute no es una fecha válida.',
    'date_format' => 'El campo :attribute no tiene el formato :format.',
    'email' => 'El campo :attribute debe ser un email válido.',
    'enum' => 'El :attribute seleccionado no es válido.',
    'exists' => 'El :attribute seleccionado no es válido.',
    'image' => 'El campo :attribute debe ser una imagen.',
    'in' => 'El :attribute seleccionado no es válido.',
    'integer' => 'El campo :attribute debe ser un número entero.',
    'max' => [
        'file' => 'El archivo :attribute no puede superar los :max kilobytes.',
        'numeric' => 'El campo :attribute no puede ser mayor a :max.',
        'string' => 'El campo :attribute no puede tener más de :max caracteres.',
    ],
    'mimes' => 'El campo :attribute debe ser un archivo de tipo: :values.',
    'min' => [
        'numeric' => 'El campo :attribute debe ser al menos :min.',
        'string' => 'El campo :attribute debe tener al menos :min caracteres.',
    ],
    'numeric' => 'El campo :attribute debe ser un número.',
    'regex' => 'El formato del campo :attribute no es válido.',
    'required' => 'El campo :attribute es obligatorio.',
    'required_with' => 'El campo :attribute es obligatorio cuando :values está presente.',
    'string' => 'El campo :attribute debe ser texto.',
    'unique' => 'Ya existe un registro con ese :attribute.',

    'attributes' => [
        'email' => 'email',
        'password' => 'contraseña',
    ],
];
