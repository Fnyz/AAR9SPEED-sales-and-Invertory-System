<?php
function validate(array $rules, array $data): void
{
    foreach ($rules as $field => $constraints) {
        $value = trim($data[$field] ?? '');

        foreach ($constraints as $rule => $param) {
            switch ($rule) {
                case 'required':
                    if ($param && $value === '') {
                        json_validation_error(ucfirst($field) . ' is required.');
                    }
                    break;
                case 'max':
                    if (strlen($value) > $param) {
                        json_validation_error(ucfirst($field) . " must be under $param characters.");
                    }
                    break;
                case 'min':
                    if (is_numeric($value) && (float)$value < $param) {
                        json_validation_error(ucfirst($field) . " must be at least $param.");
                    }
                    break;
                case 'positive':
                    if ($param && (!is_numeric($value) || (float)$value <= 0)) {
                        json_validation_error(ucfirst($field) . ' must be a positive number.');
                    }
                    break;
                case 'email':
                    if ($param && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        json_validation_error('Invalid email address.');
                    }
                    break;
                case 'integer':
                    if ($param && $value !== '' && filter_var($value, FILTER_VALIDATE_INT) === false) {
                        json_validation_error(ucfirst($field) . ' must be a whole number.');
                    }
                    break;
            }
        }
    }
}

function json_validation_error(string $message): void
{
    http_response_code(422);
    echo json_encode(['error' => $message]);
    exit;
}
