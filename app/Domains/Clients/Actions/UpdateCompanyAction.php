<?php

namespace App\Domains\Clients\Actions;

use App\Domains\Clients\Company;
use Illuminate\Support\Str;

class UpdateCompanyAction
{
    public function execute(Company $company, array $data): Company
    {
        $data['name'] = Str::upper($data['name']);

        if (!empty($data['legal_representative'])) {
            $data['legal_representative'] = Str::title(Str::lower($data['legal_representative']));
        }

        $company->update($data);

        return $company->fresh();
    }
}
