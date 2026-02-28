<?php

namespace App\Enums;

enum Permission: string
{
    case ViewClients = 'view_clients';
    case CreateClients = 'create_clients';
    case EditClients = 'edit_clients';
    case DeleteClients = 'delete_clients';

    case ViewInvoices = 'view_invoices';
    case CreateInvoices = 'create_invoices';
    case EditInvoices = 'edit_invoices';
    case DeleteInvoices = 'delete_invoices';
    case SendInvoices = 'send_invoices';

    case ViewTeam = 'view_team';
    case ManageTeam = 'manage_team';

    case ManageBilling = 'manage_billing';

    case ManageSettings = 'manage_settings';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function adminPermissions(): array
    {
        return self::values();
    }

    public static function managerPermissions(): array
    {
        return [
            self::ViewClients->value,
            self::CreateClients->value,
            self::EditClients->value,
            self::DeleteClients->value,
            self::ViewInvoices->value,
            self::CreateInvoices->value,
            self::EditInvoices->value,
            self::DeleteInvoices->value,
            self::SendInvoices->value,
            self::ViewTeam->value,
        ];
    }

    public static function staffPermissions(): array
    {
        return [
            self::ViewClients->value,
            self::ViewInvoices->value,
            self::CreateInvoices->value,
            self::ViewTeam->value,
        ];
    }
}
