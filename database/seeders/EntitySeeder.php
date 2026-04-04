<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Entity;
use App\Models\Item;
use App\Models\PaymentMethod;
use App\Models\Tax;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class EntitySeeder extends Seeder
{
    private const ROLE_PERMISSIONS = [
        'entity_admin' => [
            'customers.view',
            'customers.manage',
            'taxes.view',
            'taxes.manage',
            'items.view',
            'items.manage',
            'payment-methods.view',
            'payment-methods.manage',
            'invoices.view',
            'invoices.manage',
            'invoices.send',
            'invoices.void',
            'payments.view',
            'payments.manage',
            'payments.confirm',
            'credit-notes.view',
            'credit-notes.manage',
            'recurring.view',
            'recurring.manage',
            'reports.view',
            'notifications.view',
            'audit.view',
        ],
        'finance_manager' => [
            'customers.view',
            'customers.manage',
            'taxes.view',
            'taxes.manage',
            'items.view',
            'items.manage',
            'payment-methods.view',
            'payment-methods.manage',
            'invoices.view',
            'invoices.manage',
            'invoices.send',
            'invoices.void',
            'payments.view',
            'payments.manage',
            'payments.confirm',
            'credit-notes.view',
            'credit-notes.manage',
            'recurring.view',
            'recurring.manage',
            'reports.view',
            'notifications.view',
            'audit.view',
        ],
        'cashier' => [
            'customers.view',
            'taxes.view',
            'items.view',
            'payment-methods.view',
            'invoices.view',
            'payments.view',
            'payments.manage',
            'payments.confirm',
            'reports.view',
            'notifications.view',
        ],
    ];

    /**
     * Run the entity bootstrap and reference data.
     */
    public function run(): void
    {
        foreach ($this->entities() as $definition) {
            $entity = Entity::query()->updateOrCreate(
                ['code' => $definition['code']],
                $definition,
            );

            $this->seedRoles($entity);
            $this->seedReferenceData($entity);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function entities(): array
    {
        return [
            [
                'name' => 'Kasira Nusantara HQ',
                'code' => 'KASIRA-HQ',
                'legal_name' => 'PT Kasira Nusantara',
                'tax_id' => '01.234.567.8-999.000',
                'address' => 'Jl. Jenderal Sudirman No. 18',
                'city' => 'Jakarta Selatan',
                'province' => 'DKI Jakarta',
                'postal_code' => '12190',
                'country' => 'ID',
                'phone' => '+62 21 555 0101',
                'email' => 'hq@kasira.test',
                'currency' => 'IDR',
                'invoice_prefix' => 'KSRHQ',
                'default_payment_terms' => 30,
                'default_tax_rate' => '11.00',
                'reminder_days' => [7, 3, 1],
                'is_active' => true,
                'metadata' => [
                    'demo' => true,
                    'segment' => 'headquarters',
                ],
            ],
            [
                'name' => 'Kasira Surabaya Branch',
                'code' => 'KASIRA-BR1',
                'legal_name' => 'PT Kasira Surabaya',
                'tax_id' => '02.345.678.9-888.000',
                'address' => 'Jl. Basuki Rahmat No. 22',
                'city' => 'Surabaya',
                'province' => 'Jawa Timur',
                'postal_code' => '60261',
                'country' => 'ID',
                'phone' => '+62 31 555 0202',
                'email' => 'surabaya@kasira.test',
                'currency' => 'IDR',
                'invoice_prefix' => 'KSRSBY',
                'default_payment_terms' => 14,
                'default_tax_rate' => '11.00',
                'reminder_days' => [5, 2, 1],
                'is_active' => true,
                'metadata' => [
                    'demo' => true,
                    'segment' => 'branch',
                ],
            ],
        ];
    }

    private function seedRoles(Entity $entity): void
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($entity->getKey());
        }

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::query()->updateOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                    'entity_id' => $entity->getKey(),
                ],
                [],
            );

            $role->syncPermissions($permissions);
        }

        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId(null);
        }
    }

    private function seedReferenceData(Entity $entity): void
    {
        $vat = Tax::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'name' => 'VAT 11%',
            ],
            [
                'code' => 'VAT11',
                'type' => 'exclusive',
                'rate' => '11.00',
                'is_default' => true,
                'is_active' => true,
            ],
        );

        $serviceTax = Tax::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'name' => 'Service Tax 2.5%',
            ],
            [
                'code' => 'SERV25',
                'type' => 'exclusive',
                'rate' => '2.50',
                'is_default' => false,
                'is_active' => true,
            ],
        );

        Item::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'sku' => 'SUBSCRIPTION-STARTER',
            ],
            [
                'name' => 'Starter Subscription',
                'description' => 'Monthly starter billing package for demo invoices.',
                'unit' => 'month',
                'default_price' => '250000.00',
                'is_taxable' => true,
                'tax_id' => $vat->getKey(),
                'is_active' => true,
            ],
        );

        Item::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'sku' => 'IMPLEMENTATION-SVC',
            ],
            [
                'name' => 'Implementation Service',
                'description' => 'One-time onboarding and setup service.',
                'unit' => 'service',
                'default_price' => '1500000.00',
                'is_taxable' => true,
                'tax_id' => $vat->getKey(),
                'is_active' => true,
            ],
        );

        Item::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'sku' => 'SUPPORT-HOURS',
            ],
            [
                'name' => 'Support Hours',
                'description' => 'Flexible professional services block.',
                'unit' => 'hour',
                'default_price' => '175000.00',
                'is_taxable' => true,
                'tax_id' => $serviceTax->getKey(),
                'is_active' => true,
            ],
        );

        Customer::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'customer_number' => $entity->code.'-CUST-001',
            ],
            [
                'name' => 'Astra Retail Group',
                'email' => 'finance@astraretail.test',
                'phone' => '+62 811 1000 1001',
                'tax_id' => '03.456.789.0-777.000',
                'billing_address' => 'Jl. MH Thamrin No. 9, Jakarta Pusat',
                'shipping_address' => 'Gudang Astra Retail, Jakarta',
                'credit_balance' => '0.00',
                'is_active' => true,
            ],
        );

        Customer::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'customer_number' => $entity->code.'-CUST-002',
            ],
            [
                'name' => 'Bumi Logistik Nusantara',
                'email' => 'ap@bumilogistik.test',
                'phone' => '+62 811 1000 1002',
                'tax_id' => '04.567.890.1-666.000',
                'billing_address' => 'Jl. Pahlawan No. 88, Surabaya',
                'shipping_address' => 'Warehouse Bumi Logistik, Surabaya',
                'credit_balance' => '0.00',
                'is_active' => true,
            ],
        );

        PaymentMethod::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'name' => 'Bank Transfer - BCA',
            ],
            [
                'type' => 'bank_transfer',
                'account_name' => $entity->legal_name ?? $entity->name,
                'account_number' => '1234567890',
                'provider' => 'BCA',
                'instructions' => 'Transfer to BCA account and upload the proof of payment.',
                'is_active' => true,
            ],
        );

        PaymentMethod::query()->updateOrCreate(
            [
                'entity_id' => $entity->getKey(),
                'name' => 'QRIS',
            ],
            [
                'type' => 'qris',
                'account_name' => $entity->legal_name ?? $entity->name,
                'account_number' => null,
                'provider' => 'Midtrans',
                'instructions' => 'Scan the QR code from the invoice or payment link.',
                'is_active' => true,
            ],
        );
    }
}
