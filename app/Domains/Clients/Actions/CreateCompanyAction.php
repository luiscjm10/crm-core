<?php

namespace App\Domains\Clients\Actions;

use App\Domains\Clients\Company;
use Illuminate\Support\Str;

class CreateCompanyAction
{
    public function execute(array $data): Company
    {
        $data['name'] = Str::upper($data['name']);

        if (!empty($data['legal_representative'])) {
            $data['legal_representative'] = Str::title(Str::lower($data['legal_representative']));
        }

        return Company::create($data);
    }
}
