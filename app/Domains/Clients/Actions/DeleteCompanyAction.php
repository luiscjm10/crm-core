<?php

namespace App\Domains\Clients\Actions;

use App\Domains\Clients\Company;

class DeleteCompanyAction
{
    public function execute(Company $company): void
    {
        $company->delete();
    }
}
