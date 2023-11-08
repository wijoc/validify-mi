<?php

namespace Validitify\Rules;

use Validitify\Rule;

class FileRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        return true;
    }

    public function getErrorMessage($field, $parameters): string
    {
        // return "File type not allowed.";
        return "Bestandstype niet toegestaan.";
    }
}
