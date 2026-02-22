<?php

namespace App\Http\Requests\Balance;

use App\DTOs\BalanceOperationDTO;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReserveWithdrawRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required','integer'],
            'currency' => ['required','string','max:20'],
            'amount' => ['required','numeric','min:0.00000001'],
            'external_id' => ['required','string','max:255'],
        ];
    }

    public function toDTO(): BalanceOperationDTO
    {
        return new BalanceOperationDTO(
            user_id: $this->validated('user_id'),
            currency: $this->validated('currency'),
            amount: (string) $this->validated('amount'),
            external_id: $this->validated('external_id'),
        );
    }
}
