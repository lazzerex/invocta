<?php

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_team');
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('invitations', 'email')
                    ->where('tenant_id', $this->user()->tenant_id)
                    ->whereNull('accepted_at'),
                Rule::unique('users', 'email')
                    ->where('tenant_id', $this->user()->tenant_id),
            ],
            'role' => ['required', 'string', Rule::in(Role::values())],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email has already been invited or is already a team member.',
        ];
    }
}
