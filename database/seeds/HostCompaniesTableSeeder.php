<?php

use FreshinUp\FreshBusForms\Models\Company\Company;
use FreshinUp\FreshBusForms\Models\Company\CompanyType;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;


class HostCompaniesTableSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $companyType = CompanyType::where('key_id', 'customer')->get()->first();
        for ($i = 0; $i < 15; $i++) {
            $company = Company::updateOrCreate(
                [
                    'name' => $faker->company
                ]
            );

            $company->company_types()->sync([$companyType->id]);
        }
    }
}
